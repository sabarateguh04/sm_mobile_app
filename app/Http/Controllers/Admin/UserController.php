<?php

namespace App\Http\Controllers\Admin;

use App\Exports\UsmAccountsExport;
use App\Http\Controllers\Controller;
use App\Models\Usm;
use App\Traits\Exportable;
use App\Helpers\HierarchicalHelper;
use App\Helpers\MenuHelper;
use App\Support\LegacyUsmDbInput;
use App\Support\RoleMutationPolicy;
use App\Support\UserListingScope;
use App\Support\UserListingScopeContext;
use App\Support\UsmMasterReferensi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Maatwebsite\Excel\Facades\Excel;

class UserController extends Controller
{
    use Exportable;

    protected function viewerShowsGlobalUserId(): bool
    {
        return false;
    }

    /**
     * @return array{0: string, 1: string}
     */
    protected function defaultListingOrder(): array
    {
        return ['id_usm', 'desc'];
    }

    protected function actorIsAdminTier(?Usm $actor): bool
    {
        return UserListingScope::actorIsAdminTier($actor);
    }

    protected function actorIsOperatorTier(?Usm $actor): bool
    {
        return UserListingScope::actorIsOperatorTier($actor);
    }

    protected ?UserListingScopeContext $listingScope = null;

    protected function listingScopeContext(): UserListingScopeContext
    {
        return $this->listingScope ??= new UserListingScopeContext();
    }
    /**
     * Hanya superadmin (matriks role 1) yang boleh menetapkan / mengubah akun menjadi superadmin.
     *
     * @param  int|null  $newMatrixRoleId  `roles.role_id` yang akan disimpan (tier 1–7).
     */
    protected function assertActorMayAssignMatrixRole(?Usm $actor, ?int $newMatrixRoleId): void
    {
        if ($newMatrixRoleId === null) {
            return;
        }

        $tier = (int) $newMatrixRoleId;
        $superTiers = config('role_menu_matrix.super_admin_roles', [1]);
        if (! in_array($tier, $superTiers, true)) {
            return;
        }

        if (! $actor || MenuHelper::matrixRoleIdForUser($actor) !== 1) {
            throw ValidationException::withMessages([
                'user_role_id' => ['Hanya superadmin yang dapat menetapkan role superadmin.'],
            ]);
        }
    }

    /**
     * Master wilayah kerja: selaras dengan `/master/resort` (legacy `daerah`/`wilayah` atau `master_provinsi` / `master_kabupaten_kota`).
     *
     * @return array{daerahList: \Illuminate\Support\Collection, wilayahByDaerahJson: array<string, array<int, string|array<string, string>>>, daerahNameSet: array<string, int>, wilayahPairMap: array<string, string>, wilayahPakaiMasterTerritori: bool}
     */
    protected function wilayahDaerahMasterBundle(): array
    {
        return UsmMasterReferensi::wilayahDaerahResortBundle();
    }

    /**
     * @param  \App\Models\Usm  $user
     * @return \App\Models\Usm
     */
    protected function attachWilayahDaerahLabels($user, array $daerahNameSet, array $wilayahPairMap)
    {
        $pair = ($user->daerah && $user->wilayah) ? ($user->daerah.'|'.$user->wilayah) : null;
        $user->daerah_in_master = (bool) ($user->daerah && isset($daerahNameSet[$user->daerah]));
        $user->wilayah_in_master = (bool) ($pair && isset($wilayahPairMap[$pair]));

        return $user;
    }

    /**
     * Database sbo lawas sering punya kolom varchar NOT NULL tanpa default (mis. notelp);
     * ConvertEmptyStringsToNull mengubah "" jadi null sehingga UPDATE gagal.
     *
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function normalizeLegacyUsmOptionalStrings(array $data): array
    {
        return LegacyUsmDbInput::normalizeForPersist($data);
    }

    /**
     * Master KL terisi: form user admin memakai induk/resort dari `master_kl_daerah` / `master_kl_resort`
     * (sama dengan registrasi mandiri dan halaman profil).
     */
    protected function userFormPakaiMasterKl(?Usm $actor): bool
    {
        return UsmMasterReferensi::masterKlTigaTingkatAktif();
    }

    protected function daerahWilayahValidationRules(): array
    {
        return UsmMasterReferensi::daerahWilayahResortValidationRules();
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function sanitizeKepegawaianFields(array $data): array
    {
        foreach (['nrp_nip_nik', 'induk_pemerintahan', 'pemerintahan_daerah', 'organisasi_daerah', 'unit_kerja'] as $key) {
            if (! array_key_exists($key, $data)) {
                continue;
            }
            $data[$key] = LegacyUsmDbInput::stringOrEmpty($data[$key] ?? '');
        }
        if (array_key_exists('kategori_user', $data)) {
            $k = $data['kategori_user'];
            $data['kategori_user'] = ($k === null || $k === '') ? null : $k;
        }

        return $data;
    }

    /**
     * Pengguna non–superadmin dengan provinsi (`daerah`) / wilayah di profil hanya boleh mengelola data
     * pengguna dalam teritori yang sama (admin & oprator mengikuti aturan ini).
     */
    protected function assertActorCanManageTerritoryValues(?Usm $actor, ?string $daerah, ?string $wilayah): void
    {
        if (! $actor) {
            abort(403);
        }
        if (MenuHelper::matrixRoleIdForUser($actor) === 1) {
            return;
        }

        if (! empty($actor->wilayah)) {
            if (($daerah ?? '') !== ($actor->daerah ?? '') || ($wilayah ?? '') !== ($actor->wilayah ?? '')) {
                throw ValidationException::withMessages([
                    'daerah' => ['Provinsi dan wilayah harus sesuai wilayah kerja Anda.'],
                    'wilayah' => ['Provinsi dan wilayah harus sesuai wilayah kerja Anda.'],
                ]);
            }

            return;
        }

        if (! empty($actor->daerah)) {
            if (($daerah ?? '') !== ($actor->daerah ?? '')) {
                throw ValidationException::withMessages([
                    'daerah' => ['Provinsi harus sesuai wilayah kerja Anda.'],
                ]);
            }

            return;
        }
    }

    /**
     * Non-superadmin tidak boleh memindahkan akun ke wilayah baru jika akun sudah punya wilayah kerja awal.
     */
    protected function assertLockedTargetTerritoryOnUpdate(?Usm $actor, Usm $target, ?string $daerah, ?string $wilayah): void
    {
        if (! $actor) {
            abort(403);
        }
        if (MenuHelper::matrixRoleIdForUser($actor) === 1) {
            return;
        }

        $targetDaerah = trim((string) ($target->daerah ?? ''));
        $targetWilayah = trim((string) ($target->wilayah ?? ''));
        if ($targetDaerah === '' && $targetWilayah === '') {
            return;
        }

        $nextDaerah = trim((string) ($daerah ?? ''));
        $nextWilayah = trim((string) ($wilayah ?? ''));
        if (strcasecmp($targetDaerah, $nextDaerah) !== 0 || strcasecmp($targetWilayah, $nextWilayah) !== 0) {
            throw ValidationException::withMessages([
                'daerah' => ['Wilayah kerja user mengikuti wilayah awal saat pendaftaran dan tidak dapat diubah.'],
                'wilayah' => ['Wilayah kerja user mengikuti wilayah awal saat pendaftaran dan tidak dapat diubah.'],
            ]);
        }
    }

    /**
     * Akses lihat/ubah/hapus record user: sama dengan scope teritori manager.
     */
    protected function assertActorCanAccessUserRecord(Usm $actor, Usm $target): void
    {
        if (MenuHelper::matrixRoleIdForUser($actor) === 1) {
            return;
        }

        $targetTier = MenuHelper::matrixRoleIdForUser($target);
        if (in_array($targetTier, config('role_menu_matrix.super_admin_roles', [1]), true)) {
            abort(403, 'Akses ditolak: pengguna superadmin tidak dapat dilihat atau diubah dari akun Anda.');
        }

        $actorTier = MenuHelper::matrixRoleIdForUser($actor);
        $execRoles = config('role_menu_matrix.executive_matrix_roles', [4, 5, 6]);
        if (
            in_array($actorTier, $execRoles, true)
            && ! MenuHelper::isExecutiveNationalUser($actor)
            && ! MenuHelper::isPersonilBoOperatorUser($target)
        ) {
            abort(403, 'Akses ditolak: akun executive hanya dapat melihat data operator.');
        }

        if (in_array($actorTier, $execRoles, true)) {
            $this->assertExecutiveTerritoryAccessForUserRecord($actor, $target, $actorTier);

            return;
        }

        if (! empty($actor->wilayah)) {
            if (strcasecmp(trim((string) ($target->daerah ?? '')), trim((string) ($actor->daerah ?? ''))) !== 0
                || strcasecmp(trim((string) ($target->wilayah ?? '')), trim((string) ($actor->wilayah ?? ''))) !== 0) {
                abort(403, 'Akses ditolak: pengguna di luar wilayah kerja Anda.');
            }

            return;
        }

        if (! empty($actor->daerah)) {
            if (strcasecmp(trim((string) ($target->daerah ?? '')), trim((string) ($actor->daerah ?? ''))) !== 0) {
                abort(403, 'Akses ditolak: pengguna di luar provinsi kerja Anda.');
            }

            return;
        }
    }

    /**
     * @return array<string, mixed>
     */
    protected function kepegawaianValidationRules(): array
    {
        return UsmMasterReferensi::kepegawaianValidationRules();
    }

    protected function assertExecutiveTerritoryAccessForUserRecord(Usm $actor, Usm $target, int $matrixRoleId): void
    {
        if ($matrixRoleId === 7) {
            return;
        }

        $provExecutiveRole = (int) config('role_menu_matrix.executive_provinsi_matrix_role_id', 6);
        $kabExecutiveRole = (int) config('role_menu_matrix.executive_kab_kota_matrix_role_id', 5);

        if ($matrixRoleId === $provExecutiveRole) {
            $daerah = trim((string) ($actor->daerah ?? ''));
            if ($daerah !== '' && strcasecmp(trim((string) ($target->daerah ?? '')), $daerah) !== 0) {
                abort(403, 'Akses ditolak: pengguna di luar provinsi kerja Anda.');
            }

            return;
        }

        if ($matrixRoleId === $kabExecutiveRole) {
            $daerah = trim((string) ($actor->daerah ?? ''));
            $wilayah = trim((string) ($actor->wilayah ?? ''));

            if ($daerah !== '' && strcasecmp(trim((string) ($target->daerah ?? '')), $daerah) !== 0) {
                abort(403, 'Akses ditolak: pengguna di luar provinsi kerja Anda.');
            }

            if ($wilayah !== '' && strcasecmp(trim((string) ($target->wilayah ?? '')), $wilayah) !== 0) {
                abort(403, 'Akses ditolak: pengguna di luar wilayah kerja Anda.');
            }
        }
    }

    /** User Management untuk executive/eksternal: hanya list & detail, tanpa ubah/hapus/verifikasi. */
    protected function assertExecutiveMayMutateUserRecords(?Usm $actor): void
    {
        if (! $actor instanceof Usm) {
            return;
        }

        if (\App\Support\RoleMutationPolicy::deniesAllMutations($actor)) {
            abort(403, 'Akun executive/eksternal hanya dapat melihat daftar dan detail operator.');
        }
    }

    /**
     * Pastikan operator hanya dapat mengubah akun miliknya sendiri.
     */
    protected function assertOperatorCanMutateUserRecord(Usm $actor, Usm $target): void
    {
        if (\App\Support\RoleMutationPolicy::isOperatorRole($actor) && (int) $target->id_usm !== (int) $actor->id_usm) {
            abort(403, 'Operator hanya dapat mengubah akun milik sendiri.');
        }
    }

    /**
     * Scope dasar daftar user (teritori, role) — tanpa filter request.
     *
     * @param  \Illuminate\Database\Eloquent\Builder<\App\Models\Usm>  $query
     */
    protected function applyUserListingBaseScopes($query): void
    {
        UserListingScope::applyBaseScopes($query, $this->listingScopeContext());
    }

    /**
     * @return array{wilayahList: \Illuminate\Support\Collection<int, string>, unitKerjaList: \Illuminate\Support\Collection<int, string>}
     */
    protected function userListingFilterOptions(Request $request): array
    {
        $actor = \Illuminate\Support\Facades\Auth::user();
        $effectiveDaerah = $request->filled('daerah') ? $request->daerah : ($actor->daerah ?? '');

        $hasFilters = $request->filled('search') || $request->filled('kategori_user') || $request->filled('induk_pemerintahan');

        if ($hasFilters) {
            $requestWithoutWilayah = $request->duplicate();
            $requestWithoutWilayah->query->remove('wilayah');
            $wilayahList = $this->userListingQuery($requestWithoutWilayah)
                ->whereNotNull('wilayah')
                ->where('wilayah', '!=', '')
                ->distinct()
                ->orderBy('wilayah')
                ->pluck('wilayah');
        } else {
            if ($effectiveDaerah) {
                $master = $this->wilayahDaerahMasterBundle();
                $foundDaerah = collect(array_keys($master['wilayahByDaerahJson']))->first(fn($d) => strcasecmp($d, $effectiveDaerah) === 0);
                $wilayahList = $foundDaerah ? collect($master['wilayahByDaerahJson'][$foundDaerah]) : collect();
            } else {
                $wilayahList = collect();
            }
        }

        $requestWithoutUnitKerja = $request->duplicate();
        $requestWithoutUnitKerja->query->remove('unit_kerja');
        $unitQuery = $this->userListingQuery($requestWithoutUnitKerja);

        return [
            'wilayahList' => $wilayahList,
            'unitKerjaList' => $unitQuery
                ->whereNotNull('unit_kerja')
                ->where('unit_kerja', '!=', '')
                ->distinct()
                ->orderBy('unit_kerja')
                ->pluck('unit_kerja'),
        ];
    }

    /**
     * Query dasar daftar user (filter hierarki, pencarian, wilayah, unit kerja) — dipakai index & ekspor Excel.
     *
     * @return \Illuminate\Database\Eloquent\Builder<\App\Models\Usm>
     */
    protected function userListingQuery(Request $request)
    {
        $query = Usm::query();

        $this->applyUserListingBaseScopes($query);

        \App\Support\TerritoryIndexFilters::applyColumnFilters($query, $request, 'usm');

        if ($request->filled('unit_kerja')) {
            $query->where('unit_kerja', $request->unit_kerja);
        }

        if ($request->filled('kategori_user')) {
            $query->where('kategori_user', $request->kategori_user);
        }

        if ($request->filled('induk_pemerintahan')) {
            $query->where('induk_pemerintahan', 'like', '%' . $request->induk_pemerintahan . '%');
        }

        if ($request->filled('account_status')) {
            $st = strtolower((string) $request->get('account_status'));
            if (in_array($st, ['active', 'pending', 'blocked'], true)) {
                $query->where('account_status', $st);
            }
        }

        if ($request->filled('user_role_id')) {
            $query->where('user_role_id', $request->user_role_id);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('id_usm', 'like', "%{$search}%")
                    ->orWhere('daerah', 'like', "%{$search}%")
                    ->orWhere('wilayah', 'like', "%{$search}%")
                    ->orWhere('nama', 'like', "%{$search}%")
                    ->orWhere('username', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('notelp', 'like', "%{$search}%")
                    ->orWhere('nrp_nip_nik', 'like', "%{$search}%")
                    ->orWhere('induk_pemerintahan', 'like', "%{$search}%")
                    ->orWhere('pemerintahan_daerah', 'like', "%{$search}%")
                    ->orWhere('unit_kerja', 'like', "%{$search}%")
                    ->orWhere('kategori_user', 'like', "%{$search}%")
                    ->orWhereHas('role', function ($rq) use ($search) {
                        $rq->where('role_name', 'like', "%{$search}%");
                    });
                if (is_numeric($search)) {
                    $q->orWhere('user_role_id', (int) $search);
                }
            });
        }

        return $query;
    }

    /**
     * Ekspor ke Excel — baris yang sama dengan daftar (filter & hak akses teritori tetap berlaku).
     * Password tidak diekspor (hanya catatan / hint demo); lihat {@see UsmAccountsExport}.
     */
    public function exportExcel(Request $request)
    {
        $query = $this->userListingQuery($request);
        [$defaultOrderBy, $defaultOrderType] = $this->defaultListingOrder();
        $orderBy = $request->get('orderby', $defaultOrderBy);
        $orderType = $request->get('ordertype', $defaultOrderType);
        $query->orderBy($orderBy, $orderType);

        $users = $query->with('role')->get();
        $filename = 'daftar-akun-usm_'.date('Y-m-d_His').'.xlsx';

        return Excel::download(
            new UsmAccountsExport($users, $this->viewerShowsGlobalUserId()),
            $filename
        );
    }

    /**
     * Export user listing ke format standar (excel, pdf, csv).
     */
    public function export(Request $request, string $format = 'excel')
    {
        $normalized = strtolower($format);
        if (in_array($normalized, ['excel', 'xlsx'], true)) {
            return $this->exportExcel($request);
        }

        $query = $this->userListingQuery($request);
        [$defaultOrderBy, $defaultOrderType] = $this->defaultListingOrder();
        $orderBy = $request->get('orderby', $defaultOrderBy);
        $orderType = $request->get('ordertype', $defaultOrderType);
        $query->orderBy($orderBy, $orderType);

        $rows = $query->with('role')->get()->map(function ($user) {
            return [
                'id_usm' => $user->id_usm,
                'nama' => $user->nama,
                'username' => $user->username,
                'email' => $user->email,
                'notelp' => $user->notelp,
                'daerah' => $user->daerah,
                'wilayah' => $user->wilayah,
                'role' => optional($user->role)->role_name,
                'account_status' => $user->account_status,
                'kategori_user' => $user->kategori_user,
                'induk_pemerintahan' => $user->induk_pemerintahan,
                'pemerintahan_daerah' => $user->pemerintahan_daerah,
                'organisasi_daerah' => $user->organisasi_daerah,
                'unit_kerja' => $user->unit_kerja,
                'created_at' => $user->created_at,
            ];
        })->values();

        $filename = 'daftar-akun-usm_'.date('Y-m-d_His');
        $title = 'Daftar Akun USM';

        return match ($normalized) {
            'pdf' => $this->exportToPdf($rows, 'exports.pdf.default', $filename, $title),
            'csv' => $this->exportToCsv($rows, $filename),
            default => abort(400, 'Invalid export format'),
        };
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = $this->userListingQuery($request);

        $statusCounts = (clone $query)
            ->reorder()
            ->select(DB::raw('LOWER(account_status) as status, COUNT(*) as aggregate'))
            ->groupByRaw('LOWER(account_status)')
            ->pluck('aggregate', 'status');

        $userStats = [
            'total' => (int) $statusCounts->sum(),
            'active' => (int) ($statusCounts['active'] ?? 0),
            'pending' => (int) ($statusCounts['pending'] ?? 0),
            'blocked' => (int) ($statusCounts['blocked'] ?? 0),
        ];

        [$defaultOrderBy, $defaultOrderType] = $this->defaultListingOrder();
        $orderBy = $request->get('orderby', $defaultOrderBy);
        $orderType = $request->get('ordertype', $defaultOrderType);
        $query->orderBy($orderBy, $orderType);

        // Pagination
        $perPage = $request->get('per_page', 20);
        $users = $query->with('role')->paginate($perPage);

        $master = $this->wilayahDaerahMasterBundle();
        $users->setCollection(
            $users->getCollection()->map(function ($u) use ($master) {
                return $this->attachWilayahDaerahLabels($u, $master['daerahNameSet'], $master['wilayahPairMap']);
            })
        );

        $roleDescriptions = config('role_menu_matrix.role_descriptions', []);

        $pendingVerificationCount = $this->userListingQuery($request)->where('account_status', 'pending')->count();

        $actor = Auth::user();
        $executiveUserManagementReadOnly = $actor instanceof Usm
            && RoleMutationPolicy::isViewOnlyRole($actor);
        $isExecutive1 = ($actor instanceof Usm && MenuHelper::isExecutiveNationalUser($actor));
        $isExecutive2 = ($actor instanceof Usm && MenuHelper::isExecutiveProvinsiUser($actor));

        $filterOptions = $this->userListingFilterOptions($request);
        $territoryBundle = \App\Support\TerritoryIndexFilters::viewBundle($request);

        $listingTerritoryHint = $this->listingScopeContext()->listingTerritoryHint;
        if ($request->filled('wilayah')) {
            $listingTerritoryHint = 'Menampilkan hasil filter operator di wilayah: ' . strtoupper($request->wilayah);
        } elseif ($request->filled('daerah')) {
            $listingTerritoryHint = 'Menampilkan hasil filter operator di provinsi: ' . strtoupper($request->daerah);
        }

        $rolesForFilter = DB::table('roles')->orderBy('role_name')->get();

        return view('admin.users.index', array_merge(compact(
            'users',
            'roleDescriptions',
            'pendingVerificationCount',
            'executiveUserManagementReadOnly',
            'isExecutive1',
            'isExecutive2',
            'userStats',
            'rolesForFilter'
        ), [
            'listingTerritoryBlocked' => $this->listingScopeContext()->listingTerritoryBlocked,
            'listingTerritoryHint' => $listingTerritoryHint,
            'unitKerjaList' => $filterOptions['unitKerjaList'],
        ], $territoryBundle));
    }

    /**
     * Aktifkan akun yang masih pending (registrasi / menunggu verifikasi).
     */
    public function verify(string $id): \Illuminate\Http\RedirectResponse
    {
        $target = Usm::with('role')->findOrFail($id);
        $actor = Auth::user();
        if (! $actor instanceof Usm) {
            abort(403);
        }

        $this->assertExecutiveMayMutateUserRecords($actor);
        $this->assertActorCanAccessUserRecord($actor, $target);
        $this->assertOperatorCanMutateUserRecord($actor, $target);

        if (strtolower((string) ($target->account_status ?? '')) !== 'pending') {
            return redirect()
                ->back()
                ->with('warning', 'Akun tidak dalam status menunggu verifikasi.');
        }

        $target->account_status = 'active';
        $target->save();

        return redirect()
            ->back()
            ->with('success', 'Akun '.($target->nama ?: $target->username).' telah diverifikasi dan dapat login.');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $actor = Auth::user();
        $this->assertExecutiveMayMutateUserRecords($actor instanceof Usm ? $actor : null);

        if ($actor instanceof Usm && \App\Support\RoleMutationPolicy::isOperatorRole($actor)) {
            abort(403, 'Operator tidak diizinkan membuat akun baru.');
        }

        $roles = DB::table('roles')->orderBy('role_name')->get();
        $actor = Auth::user();
        if ($actor instanceof Usm && MenuHelper::matrixRoleIdForUser($actor) !== 1) {
            $roles = $roles->filter(fn ($r) => (int) ($r->role_id ?? 0) !== 1)->values();
        }
        $klForm = $this->userFormPakaiMasterKl($actor instanceof Usm ? $actor : null);
        $territoriMaster = $this->wilayahDaerahMasterBundle();
        $kepegawaian = $klForm
            ? UsmMasterReferensi::registrationKepegawaianFormBundle()
            : UsmMasterReferensi::kepegawaianFormBundle();

        return view('admin.users.create', array_merge([
            'roles' => $roles,
            'daerahList' => $territoriMaster['daerahList'],
            'wilayahByDaerahJson' => $territoriMaster['wilayahByDaerahJson'],
            'wilayahPakaiMasterTerritori' => $territoriMaster['wilayahPakaiMasterTerritori'] ?? false,
            'wilayahPakaiMasterKlDaerah' => UsmMasterReferensi::registrationPakaiMasterKlDaerah(),
            'userFormPakaiMasterKl' => $klForm,
        ], $kepegawaian));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $actor = Auth::user();
        $this->assertExecutiveMayMutateUserRecords($actor instanceof Usm ? $actor : null);

        if ($actor instanceof Usm && \App\Support\RoleMutationPolicy::isOperatorRole($actor)) {
            abort(403, 'Operator tidak diizinkan membuat akun baru.');
        }

        $klForm = $this->userFormPakaiMasterKl($actor instanceof Usm ? $actor : null);

        if (! $request->filled('daerah')) {
            $request->merge(['daerah' => '', 'wilayah' => '']);
        }

        $validated = $request->validate(array_merge(
            $this->daerahWilayahValidationRules(),
            $klForm ? UsmMasterReferensi::registrationKepegawaianValidationRules() : $this->kepegawaianValidationRules(),
            [
                'username' => 'required|string|max:255|unique:usm,username',
                'password' => 'required|string|min:6|confirmed',
                'nama' => 'required|string|max:255',
                'email' => 'required|email|unique:usm,email',
                'fotoprof' => 'nullable|image|max:2048',
                'notelp' => 'nullable|string|max:255',
                'user_role_id' => 'nullable|integer|exists:roles,role_id',
            ]
        ), [], ['daerah' => 'provinsi', 'wilayah' => 'kab/kota']);

        if ($klForm) {
            UsmMasterReferensi::validasiKepegawaianMasterKl(
                trim((string) ($validated['induk_pemerintahan'] ?? '')),
                trim((string) ($validated['pemerintahan_daerah'] ?? '')),
                trim((string) ($validated['organisasi_daerah'] ?? '')),
                trim((string) ($validated['unit_kerja'] ?? ''))
            );
        } else {
            UsmMasterReferensi::validasiUnitKerjaTerhadapInduk(
                trim((string) ($validated['induk_pemerintahan'] ?? '')),
                trim((string) ($validated['unit_kerja'] ?? ''))
            );
        }

        $validated = $this->sanitizeKepegawaianFields($validated);
        $validated = $this->normalizeLegacyUsmOptionalStrings($validated);

        $this->assertActorCanManageTerritoryValues($actor instanceof Usm ? $actor : null, $validated['daerah'] ?? null, $validated['wilayah'] ?? null);

        $this->assertActorMayAssignMatrixRole($actor instanceof Usm ? $actor : null, isset($validated['user_role_id']) ? (int) $validated['user_role_id'] : null);
        UsmMasterReferensi::assertTerritorialStaffRoleHasWilayahKerja(
            isset($validated['user_role_id']) ? (int) $validated['user_role_id'] : null,
            $validated['daerah'] ?? null,
            $validated['wilayah'] ?? null,
            false
        );

        // Handle file upload
        if ($request->hasFile('fotoprof')) {
            $validated['fotoprof'] = $request->file('fotoprof')->store('profiles', 'public');
        } else {
            $validated['fotoprof'] = '';
        }

        // Hash password — user baru harus langsung bisa login (bukan pending).
        $validated['password'] = Hash::make($validated['password']);
        $validated['account_status'] = 'active';

        Usm::create($validated);

        return redirect()->route('admin.users.index')
            ->with('success', 'User created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $user = Usm::findOrFail($id);
        $actor = Auth::user();
        if ($actor instanceof Usm) {
            $this->assertActorCanAccessUserRecord($actor, $user);
        }
        $role = DB::table('roles')->where('role_id', $user->user_role_id)->first();
        $master = $this->wilayahDaerahMasterBundle();
        $user = $this->attachWilayahDaerahLabels($user, $master['daerahNameSet'], $master['wilayahPairMap']);
        $roleHelp = config('role_menu_matrix.role_descriptions', []);

        $executiveUserManagementReadOnly = $actor instanceof Usm
            && RoleMutationPolicy::isViewOnlyRole($actor);

        return view('admin.users.view', compact('user', 'role', 'roleHelp', 'executiveUserManagementReadOnly'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $this->assertExecutiveMayMutateUserRecords(Auth::user() instanceof Usm ? Auth::user() : null);

        $user = Usm::findOrFail($id);
        $actor = Auth::user();
        if ($actor instanceof Usm) {
            $this->assertActorCanAccessUserRecord($actor, $user);
            $this->assertOperatorCanMutateUserRecord($actor, $user);
        }
        $roles = DB::table('roles')->orderBy('role_name')->get();
        if ($actor instanceof Usm && MenuHelper::matrixRoleIdForUser($actor) !== 1) {
            $roles = $roles->filter(fn ($r) => (int) ($r->role_id ?? 0) !== 1)->values();
        }
        $klForm = $this->userFormPakaiMasterKl($actor instanceof Usm ? $actor : null);
        $territoriMaster = $this->wilayahDaerahMasterBundle();
        $kepegawaian = $klForm
            ? UsmMasterReferensi::registrationKepegawaianFormBundle($user)
            : UsmMasterReferensi::kepegawaianFormBundle($user);

        return view('admin.users.edit', array_merge([
            'user' => $user,
            'roles' => $roles,
            'daerahList' => $territoriMaster['daerahList'],
            'wilayahByDaerahJson' => $territoriMaster['wilayahByDaerahJson'],
            'wilayahPakaiMasterTerritori' => $territoriMaster['wilayahPakaiMasterTerritori'] ?? false,
            'wilayahPakaiMasterKlDaerah' => UsmMasterReferensi::registrationPakaiMasterKlDaerah(),
            'userFormPakaiMasterKl' => $klForm,
            'personilManagerEditUrl' => MenuHelper::isPersonilBoOperatorUser($user)
                ? route('personil.manager.edit', $user->id_usm)
                : null,
        ], $kepegawaian));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $this->assertExecutiveMayMutateUserRecords(Auth::user() instanceof Usm ? Auth::user() : null);

        $user = Usm::findOrFail($id);
        $actor = Auth::user();
        if ($actor instanceof Usm) {
            $this->assertActorCanAccessUserRecord($actor, $user);
            $this->assertOperatorCanMutateUserRecord($actor, $user);
        }

        $klForm = $this->userFormPakaiMasterKl($actor instanceof Usm ? $actor : null);

        if (! $request->filled('daerah')) {
            $request->merge(['daerah' => '', 'wilayah' => '']);
        }

        $validated = $request->validate(array_merge(
            $this->daerahWilayahValidationRules(),
            $klForm ? UsmMasterReferensi::registrationKepegawaianValidationRules() : $this->kepegawaianValidationRules(),
            [
                'username' => ['required', 'string', 'max:255', Rule::unique('usm')->ignore($id, 'id_usm')],
                'nama' => 'required|string|max:255',
                'fotoprof' => 'nullable|image|max:2048',
                'account_status' => 'required|in:active,pending,blocked',
                'user_role_id' => 'nullable|integer|exists:roles,role_id',
                'notelp' => 'nullable|string|max:255',
                'password' => 'nullable|string|min:6|confirmed',
            ]
        ), [], ['daerah' => 'provinsi', 'wilayah' => 'kab/kota']);

        if ($klForm) {
            UsmMasterReferensi::validasiKepegawaianMasterKl(
                trim((string) ($validated['induk_pemerintahan'] ?? '')),
                trim((string) ($validated['pemerintahan_daerah'] ?? '')),
                trim((string) ($validated['organisasi_daerah'] ?? '')),
                trim((string) ($validated['unit_kerja'] ?? ''))
            );
        } else {
            UsmMasterReferensi::validasiUnitKerjaTerhadapInduk(
                trim((string) ($validated['induk_pemerintahan'] ?? '')),
                trim((string) ($validated['unit_kerja'] ?? ''))
            );
        }

        $validated = $this->sanitizeKepegawaianFields($validated);
        // Handle file upload
        if ($request->hasFile('fotoprof')) {
            // Delete old file
            if ($user->fotoprof) {
                Storage::disk('public')->delete($user->fotoprof);
            }
            $validated['fotoprof'] = $request->file('fotoprof')->store('profiles', 'public');
        }

        if (! empty($validated['password'])) {
            $validated['password'] = Hash::make($validated['password']);
        } else {
            unset($validated['password']);
        }

        $validated = $this->normalizeLegacyUsmOptionalStrings($validated);

        if ($actor instanceof Usm) {
            $this->assertLockedTargetTerritoryOnUpdate(
                $actor,
                $user,
                $validated['daerah'] ?? null,
                $validated['wilayah'] ?? null
            );
            $this->assertActorCanManageTerritoryValues($actor, $validated['daerah'] ?? null, $validated['wilayah'] ?? null);
        }

        $this->assertActorMayAssignMatrixRole($actor instanceof Usm ? $actor : null, isset($validated['user_role_id']) ? (int) $validated['user_role_id'] : null);
        UsmMasterReferensi::assertTerritorialStaffRoleHasWilayahKerja(
            isset($validated['user_role_id']) ? (int) $validated['user_role_id'] : null,
            $validated['daerah'] ?? null,
            $validated['wilayah'] ?? null,
            false
        );

        $user->update($validated);

        return redirect()->route('admin.users.index')
            ->with('success', 'User updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $this->assertExecutiveMayMutateUserRecords(Auth::user() instanceof Usm ? Auth::user() : null);

        $user = Usm::findOrFail($id);
        $actor = Auth::user();
        if ($actor instanceof Usm) {
            $this->assertActorCanAccessUserRecord($actor, $user);
            $this->assertOperatorCanMutateUserRecord($actor, $user);
        }

        // Delete profile photo
        if ($user->fotoprof) {
            Storage::disk('public')->delete($user->fotoprof);
        }

        $user->delete();

        return redirect()->route('admin.users.index')
            ->with('success', 'User deleted successfully.');
    }

    /**
     * Update single field (for inline editing)
     */
    public function editField(Request $request, string $id)
    {
        $this->assertExecutiveMayMutateUserRecords(Auth::user() instanceof Usm ? Auth::user() : null);

        $user = Usm::findOrFail($id);
        $actor = Auth::user();
        if ($actor instanceof Usm) {
            $this->assertActorCanAccessUserRecord($actor, $user);
            $this->assertOperatorCanMutateUserRecord($actor, $user);
        }

        $field = $request->input('name');
        $value = $request->input('value');

        // Validate field
        $allowedFields = [
            'daerah', 'wilayah', 'username', 'nama', 'account_status', 'user_role_id', 'notelp',
            'nrp_nip_nik', 'induk_pemerintahan', 'pemerintahan_daerah', 'organisasi_daerah', 'unit_kerja', 'kategori_user',
        ];
        
        if (!in_array($field, $allowedFields)) {
            if ($request->wantsJson() || $request->ajax()) return response()->json(['error' => 'Invalid field'], 400);
            return redirect()->back()->with('error', 'Invalid field');
        }

        // Check for duplicate username
        if ($field === 'username') {
            $exists = Usm::where('username', $value)
                ->where('id_usm', '!=', $id)
                ->exists();
            if ($exists) {
                if ($request->wantsJson() || $request->ajax()) return response()->json(['error' => 'Username already exists'], 400);
                return redirect()->back()->with('error', 'Username already exists');
            }
        }

        $value = LegacyUsmDbInput::normalizeInlineFieldValue($field, $value);

        if ($field === 'kategori_user') {
            $value = ($value === null || $value === '') ? null : $value;
            if ($value !== null && ! in_array($value, ['organik', 'non_organik'], true)) {
                if ($request->wantsJson() || $request->ajax()) return response()->json(['error' => 'Invalid value'], 400);
                return redirect()->back()->with('error', 'Invalid value');
            }
        }

        $actorKlForm = $actor instanceof Usm && $this->userFormPakaiMasterKl($actor);
        if ($actorKlForm && UsmMasterReferensi::masterKlTigaTingkatAktif()) {
            if ($field === 'induk_pemerintahan' && $value !== null && $value !== '' && ! DB::table('master_kl_pusat')->where('nama', $value)->exists()) {
                if ($request->wantsJson() || $request->ajax()) return response()->json(['error' => 'Induk organisasi tidak ada di master KL pusat.'], 422);
                return redirect()->back()->with('error', 'Induk organisasi tidak ada di master KL pusat.');
            }
            if ($field === 'pemerintahan_daerah' && $value !== null && $value !== '') {
                try {
                    UsmMasterReferensi::validasiOrganisasiDaerahTerhadapIndukKl(
                        trim((string) ($user->induk_pemerintahan ?? '')),
                        trim((string) $value)
                    );
                } catch (ValidationException $ex) {
                    $msg = collect($ex->errors())->flatten()->first() ?? 'Organisasi daerah tidak valid untuk induk.';
                    if ($request->wantsJson() || $request->ajax()) return response()->json(['error' => $msg], 422);
                    return redirect()->back()->with('error', $msg);
                }
            }
            if ($field === 'unit_kerja' && $value !== null && $value !== '') {
                try {
                    UsmMasterReferensi::validasiUnitKerjaTerhadapOrganisasiDaerahKl(
                        trim((string) ($user->induk_pemerintahan ?? '')),
                        trim((string) ($user->pemerintahan_daerah ?? '')),
                        trim((string) $value)
                    );
                } catch (ValidationException $ex) {
                    $msg = collect($ex->errors())->flatten()->first() ?? 'Unit kerja tidak valid.';
                    if ($request->wantsJson() || $request->ajax()) return response()->json(['error' => $msg], 422);
                    return redirect()->back()->with('error', $msg);
                }
            }
        }

        if (in_array($field, ['daerah', 'wilayah'], true) && $actor instanceof Usm) {
            $nextDaerah = $field === 'daerah' ? LegacyUsmDbInput::stringOrEmpty($value) : LegacyUsmDbInput::stringOrEmpty($user->daerah);
            $nextWilayah = $field === 'wilayah' ? LegacyUsmDbInput::stringOrEmpty($value) : LegacyUsmDbInput::stringOrEmpty($user->wilayah);
            if ($field === 'daerah' && LegacyUsmDbInput::stringOrEmpty($value) === '') {
                $nextWilayah = '';
            }
            try {
                $snap = $request->only(['daerah', 'wilayah']);
                try {
                    $request->merge([
                        'daerah' => $nextDaerah,
                        'wilayah' => $nextWilayah,
                    ]);
                    Validator::make(
                        $request->only(['daerah', 'wilayah']),
                        $this->daerahWilayahValidationRules()
                    )->validate();
                } finally {
                    $request->merge($snap);
                }

                $this->assertActorCanManageTerritoryValues(
                    $actor,
                    $nextDaerah !== '' ? $nextDaerah : null,
                    $nextWilayah !== '' ? $nextWilayah : null
                );
                $this->assertLockedTargetTerritoryOnUpdate(
                    $actor,
                    $user,
                    $nextDaerah !== '' ? $nextDaerah : null,
                    $nextWilayah !== '' ? $nextWilayah : null
                );
                UsmMasterReferensi::assertTerritorialStaffRoleHasWilayahKerja(
                    isset($user->user_role_id) ? (int) $user->user_role_id : null,
                    $nextDaerah !== '' ? $nextDaerah : null,
                    $nextWilayah !== '' ? $nextWilayah : null,
                    false
                );
            } catch (ValidationException $ex) {
                $msg = collect($ex->errors())->flatten()->first() ?? 'Wilayah kerja tidak sesuai.';
                if ($request->wantsJson() || $request->ajax()) return response()->json(['error' => $msg], 422);
                return redirect()->back()->with('error', $msg);
            }
        }

        if ($field === 'user_role_id' && $actor instanceof Usm) {
            $rid = ($value === null || $value === '') ? null : (int) $value;
            try {
                $this->assertActorMayAssignMatrixRole($actor, $rid);
                UsmMasterReferensi::assertTerritorialStaffRoleHasWilayahKerja(
                    $rid,
                    $user->daerah,
                    $user->wilayah,
                    false
                );
            } catch (ValidationException $ex) {
                $msg = collect($ex->errors())->flatten()->first() ?? 'Role tidak valid.';
                if ($request->wantsJson() || $request->ajax()) return response()->json(['error' => $msg], 422);
                return redirect()->back()->with('error', $msg);
            }
        }

        if ($field === 'daerah' && $value === '') {
            $user->update(['daerah' => '', 'wilayah' => '']);
        } else {
            $user->update([$field => $value]);
        }

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'num_rows' => 1,
                'rec_id' => $id,
            ]);
        }

        return redirect()->back()->with('success', 'Data berhasil diperbarui.');
    }
}
