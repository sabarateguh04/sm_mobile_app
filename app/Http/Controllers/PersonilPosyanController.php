<?php

namespace App\Http\Controllers;

use App\Helpers\HierarchicalHelper;
use App\Helpers\MenuHelper;
use App\Models\Usm;
use App\Support\KepegawaianListingScope;
use App\Support\LegacyUsmDbInput;
use App\Support\RoleMutationPolicy;
use App\Support\UserListingScope;
use App\Support\UsmMasterReferensi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\View;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

/**
 * Kuat Personil BO — CRUD operator per provinsi/kab-kota (sumber baca wil_usm, tulis usm).
 */
class PersonilPosyanController extends LegacySboOperasionalController
{
    /** @var list<int> Role legacy DB sebelum migrasi 7 tier → diperlakukan sebagai admin. */
    private const LEGACY_ADMIN_USER_ROLE_IDS = [5, 8, 9, 16];

    protected ?string $listingTerritoryHint = null;

    protected bool $listingTerritoryBlocked = false;

    protected $tableName = 'wil_usm';

    protected $primaryKey = 'id_usm';

    protected $viewFolder = 'personil.manager';

    protected $fillable = [
        'username', 'email', 'nama', 'notelp',
        'daerah', 'wilayah', 'account_status', 'fotoprof',
        'nrp_nip_nik', 'induk_pemerintahan', 'pemerintahan_daerah', 'organisasi_daerah', 'unit_kerja',
    ];

    protected $searchFields = [
        'username', 'nama', 'email', 'daerah', 'wilayah', 'account_status',
        'nrp_nip_nik', 'induk_pemerintahan', 'pemerintahan_daerah', 'organisasi_daerah', 'unit_kerja', 'kategori_user',
    ];

    protected $defaultOrderBy = 'nama';

    protected $defaultOrderType = 'asc';

    /** @return list<int> */
    protected function legacyOperatorRoleIds(): array
    {
        $value = config('role_menu_matrix.legacy_operator_user_role_ids', [11]);

        if (! is_array($value)) {
            return [11];
        }

        return array_values(array_map(static fn ($id): int => (int) $id, $value));
    }

    /** @return list<int> */
    protected function operatorRoleIds(): array
    {
        return MenuHelper::personilBoOperatorUserRoleIds();
    }

    protected function sourceTable(): string
    {
        // Tulis & baca dari `usm` — view legacy `wil_usm` sering rusak / tidak ter-update di production.
        return 'usm';
    }

    protected function baseQuery()
    {
        $table = $this->sourceTable();
        $this->tableName = $table;

        $select = [
            $table.'.id_usm',
            $table.'.username',
            $table.'.email',
            $table.'.fotoprof',
            $table.'.account_status',
            $table.'.user_role_id',
            $table.'.nama',
            $table.'.daerah',
            $table.'.wilayah',
            $table.'.notelp',
            'roles.role_name',
        ];

        foreach (['nrp_nip_nik', 'induk_pemerintahan', 'pemerintahan_daerah', 'organisasi_daerah', 'unit_kerja', 'kategori_user'] as $col) {
            if (Schema::hasColumn($table, $col)) {
                $select[] = $table.'.'.$col;
            }
        }

        return DB::table($table)
            ->leftJoin('roles', $table.'.user_role_id', '=', 'roles.role_id')
            ->whereIn($table.'.user_role_id', $this->operatorRoleIds())
            ->select($select);
    }

    protected function applyFilters($query, Request $request)
    {
        return $query;
    }

    protected function actorIsAdminTier(?Usm $actor): bool
    {
        if (! $actor instanceof Usm) {
            return false;
        }

        if (MenuHelper::matrixRoleIdForUser($actor) === 2) {
            return true;
        }

        return in_array((int) ($actor->user_role_id ?? 0), self::LEGACY_ADMIN_USER_ROLE_IDS, true);
    }

    protected function actorIsOperatorTier(?Usm $actor): bool
    {
        if (! $actor instanceof Usm) {
            return false;
        }

        $operatorTier = (int) config('role_menu_matrix.operator_matrix_role_id', 3);
        if (MenuHelper::matrixRoleIdForUser($actor) === $operatorTier) {
            return true;
        }

        return in_array((int) ($actor->user_role_id ?? 0), $this->legacyOperatorRoleIds(), true);
    }

    protected function actorHasKepegawaianScope(Usm $actor): bool
    {
        return trim((string) ($actor->induk_pemerintahan ?? '')) !== ''
            || trim((string) ($actor->pemerintahan_daerah ?? '')) !== ''
            || trim((string) ($actor->organisasi_daerah ?? '')) !== ''
            || trim((string) ($actor->unit_kerja ?? '')) !== '';
    }

    protected function actorHasGeographicScope(Usm $actor): bool
    {
        return trim((string) ($actor->daerah ?? '')) !== '';
    }

    /**
     * @param  \Illuminate\Database\Query\Builder  $query
     */
    protected function applyColumnEqualsNormalized($query, string $qualifiedColumn, string $value): void
    {
        $query->whereRaw(
            'LOWER(TRIM(COALESCE('.$qualifiedColumn.", ''))) = ?",
            [mb_strtolower(trim($value))]
        );
    }

    /** @return list<string> */
    protected function pemerintahanDaerahNamesUnderInduk(string $induk): array
    {
        $induk = trim($induk);
        if ($induk === '') {
            return [];
        }

        if (UsmMasterReferensi::masterKlTigaTingkatAktif()) {
            $names = [];
            foreach ($this->indukOrganisasiEquivalentNames($induk) as $indukName) {
                $rows = DB::table('master_kl_daerah as d')
                    ->join('master_kl_pusat as p', 'p.id', '=', 'd.parent_id')
                    ->whereRaw('LOWER(TRIM(p.nama)) = ?', [mb_strtolower($indukName)])
                    ->orderBy('d.nama')
                    ->pluck('d.nama');
                foreach ($rows as $name) {
                    $names[] = trim((string) $name);
                }
            }

            return array_values(array_unique(array_filter($names)));
        }

        if (UsmMasterReferensi::indukPemerintahanSelectAktif()) {
            $names = [];
            foreach ($this->indukOrganisasiEquivalentNames($induk) as $indukName) {
                $rows = DB::table('suborg as s')
                    ->join('org as o', 'o.id_org', '=', 's.id_org')
                    ->whereRaw('LOWER(TRIM(o.nameorg)) = ?', [mb_strtolower($indukName)])
                    ->distinct()
                    ->orderBy('s.namesuborg')
                    ->pluck('s.namesuborg');
                foreach ($rows as $name) {
                    $names[] = trim((string) $name);
                }
            }

            return array_values(array_unique(array_filter($names)));
        }

        return [];
    }

    /**
     * Operator yang belum diisi kepegawaiannya (legacy / belum sync profil).
     *
     * @param  \Illuminate\Database\Query\Builder  $query
     */
    protected function applyAllKepegawaianFieldsEmpty($query, string $table): void
    {
        foreach (['induk_pemerintahan', 'pemerintahan_daerah', 'organisasi_daerah', 'unit_kerja'] as $col) {
            if (! Schema::hasColumn($table, $col)) {
                return;
            }
        }

        foreach (['induk_pemerintahan', 'pemerintahan_daerah', 'organisasi_daerah', 'unit_kerja'] as $col) {
            $query->where(function ($q) use ($table, $col) {
                $q->where($table.'.'.$col, '')->orWhereNull($table.'.'.$col);
            });
        }
    }

    /**
     * @param  \Illuminate\Database\Query\Builder  $query
     */
    protected function applyGeographicScopeForActor($query, string $table, Usm $actor): void
    {
        $daerah = trim((string) ($actor->daerah ?? ''));
        $wilayah = trim((string) ($actor->wilayah ?? ''));

        if ($daerah === '') {
            return;
        }

        if ($wilayah !== '') {
            $this->applyColumnEqualsNormalized($query, $table.'.daerah', $daerah);
            $this->applyColumnEqualsNormalized($query, $table.'.wilayah', $wilayah);

            return;
        }

        $this->applyColumnEqualsNormalized($query, $table.'.daerah', $daerah);
    }

    /**
     * Scope daftar personil BO: induk organisasi (utama), lalu provinsi/kab-kota bila profil punya wilayah.
     *
     * @param  \Illuminate\Database\Query\Builder  $query
     */
    protected function applyPersonilBoTerritoryScope($query, string $table): void
    {
        $actor = Auth::user();
        if (! $actor instanceof Usm) {
            return;
        }

        if (HierarchicalHelper::viewerBypassesTerritoryScope()) {
            return;
        }

        $hasGeo = $this->actorHasGeographicScope($actor);
        $hasKepegawaian = $this->actorHasKepegawaianScope($actor);

        if ($this->actorIsAdminTier($actor)
            && ! $hasGeo
            && ! KepegawaianListingScope::actorHasScope($actor)) {
            $this->listingTerritoryHint = 'Administrator pusat — menampilkan seluruh operator.';

            return;
        }

        if (($this->actorIsOperatorTier($actor) || MenuHelper::isExecutiveKabKotaUser($actor)) && ! $hasGeo) {
            $this->listingTerritoryBlocked = true;
            $this->listingTerritoryHint = MenuHelper::isExecutiveKabKotaUser($actor)
                ? 'Executive kab/kota wajib mengisi provinsi dan wilayah kerja di profil organisasi agar daftar personil menampilkan operator di wilayah Anda.'
                : 'Operator wajib mengisi provinsi kerja (daerah) di profil organisasi agar daftar personil menampilkan operator di wilayah provinsi Anda.';
            $query->whereRaw('1 = 0');

            return;
        }

        $this->applyPersonilBoListingScope($query, $table, $actor);
    }

    /**
     * Daftar personil BO: filter provinsi (`daerah`) wajib bila terisi; kepegawaian menyempitkan ke Polda/Polres.
     *
     * @param  \Illuminate\Database\Query\Builder  $query
     */
    protected function applyPersonilBoListingScope($query, string $table, Usm $actor): void
    {
        $hasGeo = $this->actorHasGeographicScope($actor);
        $hasKepegawaian = KepegawaianListingScope::actorHasScope($actor);

        if ($hasGeo) {
            $query->where(function ($outer) use ($table, $actor, $hasKepegawaian) {
                $outer->where(function ($main) use ($table, $actor, $hasKepegawaian) {
                    $this->applyGeographicScopeForActor($main, $table, $actor);
                    if ($hasKepegawaian && Schema::hasColumn($table, 'induk_pemerintahan')) {
                        KepegawaianListingScope::applyScopeLegacyTigaTingkat($main, $table, $actor, false);
                    }
                });

                if ($hasKepegawaian && Schema::hasColumn($table, 'induk_pemerintahan')) {
                    $outer->orWhere(function ($legacy) use ($table, $actor) {
                        $this->applyAllKepegawaianFieldsEmpty($legacy, $table);
                        $this->applyGeographicScopeForActor($legacy, $table, $actor);
                    });
                }
            });

            $this->setPersonilBoListingHint($actor, $hasKepegawaian);

            return;
        }

        if ($hasKepegawaian && Schema::hasColumn($table, 'induk_pemerintahan')) {
            $query->where(function ($kep) use ($table, $actor) {
                KepegawaianListingScope::applyScopeLegacyTigaTingkat($kep, $table, $actor, true);
            });
            $this->listingTerritoryHint = ($this->actorIsAdminTier($actor) ? 'Administrator — ' : '')
                .KepegawaianListingScope::buildHint($actor, true);

            return;
        }

        $this->listingTerritoryHint = $this->actorIsAdminTier($actor)
            ? 'Administrator — menampilkan seluruh operator.'
            : null;
    }

    protected function setPersonilBoListingHint(Usm $actor, bool $hasKepegawaian): void
    {
        $daerah = trim((string) ($actor->daerah ?? ''));
        $wilayah = trim((string) ($actor->wilayah ?? ''));

        $provinsiHint = $wilayah !== ''
            ? 'Menampilkan operator di kab/kota '.$wilayah.' (provinsi '.$daerah.')'
            : 'Menampilkan operator di daerah provinsi: '.$daerah;

        if ($hasKepegawaian) {
            $prefix = $this->actorIsAdminTier($actor) ? 'Administrator — ' : '';
            $this->listingTerritoryHint = $prefix.$provinsiHint.' · '
                .KepegawaianListingScope::buildHint($actor, false);

            return;
        }

        $this->listingTerritoryHint = ($this->actorIsAdminTier($actor) ? 'Administrator — ' : '').$provinsiHint.'.';
    }

    /**
     * Filter permintaan daftar: kategori user & organisasi (induk_pemerintahan).
     *
     * @param  \Illuminate\Database\Query\Builder  $query
     */
    protected function applyPersonilBoRequestFilters($query, string $table, Request $request): void
    {
        if ($request->filled('kategori_user') && Schema::hasColumn($table, 'kategori_user')) {
            $kat = (string) $request->kategori_user;
            if (in_array($kat, ['organik', 'non_organik'], true)) {
                $query->where($table.'.kategori_user', $kat);
            }
        }

        if ($request->filled('induk_pemerintahan') && Schema::hasColumn($table, 'induk_pemerintahan')) {
            $org = trim((string) $request->induk_pemerintahan);
            if ($org !== '') {
                $query->where(function ($q) use ($table, $org) {
                    $needle = mb_strtolower($org);
                    foreach (['induk_pemerintahan', 'pemerintahan_daerah', 'organisasi_daerah'] as $col) {
                        if (Schema::hasColumn($table, $col)) {
                            $q->orWhereRaw(
                                'LOWER(TRIM(COALESCE('.$table.'.'.$col.", ''))) = ?",
                                [$needle]
                            );
                        }
                    }
                });
            }
        }
    }

    /** @return \Illuminate\Support\Collection<int, string> */
    protected function personilOrganisasiFilterOptions(string $table, Request $request): \Illuminate\Support\Collection
    {
        $columns = array_values(array_filter(
            ['induk_pemerintahan', 'pemerintahan_daerah', 'organisasi_daerah'],
            static fn (string $col): bool => Schema::hasColumn($table, $col)
        ));

        if ($columns === []) {
            return collect();
        }

        $query = $this->baseQuery();
        $this->applyPersonilBoTerritoryScope($query, $table);
        \App\Support\TerritoryIndexFilters::applyColumnFilters($query, $request, $table);

        $options = collect();
        foreach ($columns as $col) {
            $opts = (clone $query)
                ->reorder()
                ->select($table.'.'.$col)
                ->whereNotNull($table.'.'.$col)
                ->where($table.'.'.$col, '!=', '')
                ->distinct()
                ->orderBy($table.'.'.$col)
                ->pluck($col);
            $options = $options->merge($opts);
        }

        $options = $options
            ->map(static fn ($v) => trim((string) $v))
            ->filter(static fn (string $v): bool => $v !== '')
            ->unique()
            ->sort()
            ->values();

        if ($options->isNotEmpty()) {
            return $options;
        }

        if (Schema::hasTable('org')) {
            return DB::table('org')
                ->whereNotNull('nameorg')
                ->where('nameorg', '!=', '')
                ->distinct()
                ->orderBy('nameorg')
                ->pluck('nameorg');
        }

        return $options;
    }

    /**
     * Filter manual induk organisasi (superadmin / pusat).
     *
     * @param  \Illuminate\Database\Query\Builder  $query
     */
    protected function applyOptionalIndukOrganisasiRequestFilter($query, string $table): void
    {
        if (! Schema::hasColumn($table, 'induk_pemerintahan')) {
            return;
        }

        $induk = trim((string) request()->query('induk_pemerintahan', ''));
        if ($induk === '') {
            return;
        }

        $query->where(function ($q) use ($table, $induk) {
            $this->applyIndukOrganisasiMatch($q, $table, $induk, '', '');
        });

        $this->listingTerritoryHint = 'Filter induk organisasi: '.$induk.'.';
    }

    /** @return list<string> */
    protected function indukOrganisasiEquivalentNames(string $induk): array
    {
        return UsmMasterReferensi::indukOrganisasiEquivalentNames($induk);
    }

    /**
     * @param  \Illuminate\Database\Query\Builder  $query
     */
    protected function applyIndukColumnMatches($query, string $qualifiedColumn, string $induk): void
    {
        $names = $this->indukOrganisasiEquivalentNames($induk);
        $query->where(function ($q) use ($qualifiedColumn, $names) {
            foreach ($names as $name) {
                $q->orWhereRaw(
                    'LOWER(TRIM(COALESCE('.$qualifiedColumn.", ''))) = ?",
                    [mb_strtolower(trim($name))]
                );
            }
        });
    }

    /**
     * Cocokkan baris operator ke induk organisasi (termasuk baris legacy tanpa kolom induk terisi).
     *
     * @param  \Illuminate\Database\Query\Builder  $query
     */
    protected function applyIndukOrganisasiMatch(
        $query,
        string $table,
        string $induk,
        string $orgPusat = '',
        string $organisasiDaerah = '',
        string $unitKerja = ''
    ): void {
        $induk = trim($induk);
        $orgPusat = trim($orgPusat);
        $organisasiDaerah = trim($organisasiDaerah);
        $unitKerja = trim($unitKerja);

        if ($induk === '') {
            return;
        }

        $indukNames = $this->indukOrganisasiEquivalentNames($induk);
        $hasOrgDaerahCol = Schema::hasColumn($table, 'organisasi_daerah');

        $query->where(function ($q) use ($table, $induk, $indukNames, $orgPusat, $organisasiDaerah, $unitKerja, $hasOrgDaerahCol) {
            $this->applyIndukColumnMatches($q, $table.'.induk_pemerintahan', $induk);

            $orgNames = ($orgPusat === '' && $organisasiDaerah === '' && $unitKerja === '')
                ? array_merge(
                    ...array_map(fn (string $name) => $this->pemerintahanDaerahNamesUnderInduk($name), $indukNames)
                )
                : [];

            $orgNames = array_values(array_unique(array_filter($orgNames)));

            if ($unitKerja === '' && $organisasiDaerah === '' && $orgPusat === '' && $orgNames === []) {
                return;
            }

            $q->orWhere(function ($q2) use ($table, $orgPusat, $organisasiDaerah, $unitKerja, $orgNames, $hasOrgDaerahCol) {
                $q2->where(function ($q3) use ($table) {
                    $q3->where($table.'.induk_pemerintahan', '')->orWhereNull($table.'.induk_pemerintahan');
                });

                if ($unitKerja !== '') {
                    $this->applyColumnEqualsNormalized($q2, $table.'.unit_kerja', $unitKerja);
                    if ($hasOrgDaerahCol && $organisasiDaerah !== '') {
                        $this->applyColumnEqualsNormalized($q2, $table.'.organisasi_daerah', $organisasiDaerah);
                    }
                    if ($orgPusat !== '') {
                        $this->applyColumnEqualsNormalized($q2, $table.'.pemerintahan_daerah', $orgPusat);
                    }

                    return;
                }

                if ($organisasiDaerah !== '' && $hasOrgDaerahCol) {
                    $this->applyColumnEqualsNormalized($q2, $table.'.organisasi_daerah', $organisasiDaerah);
                    if ($orgPusat !== '') {
                        $this->applyColumnEqualsNormalized($q2, $table.'.pemerintahan_daerah', $orgPusat);
                    }

                    return;
                }

                if ($orgPusat !== '') {
                    $this->applyColumnEqualsNormalized($q2, $table.'.pemerintahan_daerah', $orgPusat);

                    return;
                }

                $q2->whereIn($table.'.pemerintahan_daerah', $orgNames);
            });
        });
    }

    public function index(Request $request)
    {
        $table = $this->sourceTable();
        $this->tableName = $table;
        $this->listingTerritoryHint = null;
        $this->listingTerritoryBlocked = false;

        $query = $this->baseQuery();
        $this->applyPersonilBoTerritoryScope($query, $table);

        \App\Support\TerritoryIndexFilters::applyColumnFilters($query, $request, $table);
        $this->applyPersonilBoRequestFilters($query, $table, $request);
        
        if ($request->filled('wilayah')) {
            $this->listingTerritoryHint = 'Menampilkan hasil filter operator di wilayah: ' . strtoupper($request->wilayah);
        } elseif ($request->filled('daerah')) {
            $this->listingTerritoryHint = 'Menampilkan hasil filter operator di provinsi: ' . strtoupper($request->daerah);
        }

        if ($request->filled('search')) {
            $s = $request->search;
            $searchFields = array_values(array_filter(
                $this->searchFields,
                fn (string $field): bool => Schema::hasColumn($table, $field)
            ));
            $query->where(function ($q) use ($s, $table, $searchFields) {
                foreach ($searchFields as $field) {
                    $q->orWhere($table.'.'.$field, 'like', "%{$s}%");
                }
                $q->orWhere('roles.role_name', 'like', "%{$s}%");
            });
        }

        // Filter rentang tanggal (opsional) berdasarkan kolom created_at usm bila tersedia.
        $dateFrom = $request->get('tanggal_from');
        $dateTo = $request->get('tanggal_to');
        if (($dateFrom || $dateTo) && Schema::hasColumn($table, 'created_at')) {
            $query->where(function ($q) use ($table, $dateFrom, $dateTo) {
                if ($dateFrom) {
                    $q->whereDate($table.'.created_at', '>=', $dateFrom);
                }
                if ($dateTo) {
                    $q->whereDate($table.'.created_at', '<=', $dateTo);
                }
            });
        }

        $query->orderBy(
            $table.'.'.$request->get('orderby', $this->defaultOrderBy),
            $request->get('ordertype', $this->defaultOrderType)
        );

        $statusCounts = (clone $query)
            ->reorder()
            ->select(DB::raw('LOWER('.$table.'.account_status) as status, COUNT(*) as aggregate'))
            ->groupByRaw('LOWER('.$table.'.account_status)')
            ->pluck('aggregate', 'status');

        $personilStats = [
            'total' => (int) $statusCounts->sum(),
            'active' => (int) ($statusCounts['active'] ?? 0),
            'pending' => (int) ($statusCounts['pending'] ?? 0),
            'blocked' => (int) ($statusCounts['blocked'] ?? 0),
        ];

        $data = $query->paginate($request->get('per_page', $this->perPage));

        $actor = Auth::user();
        $canFilterIndukOrganisasi = HierarchicalHelper::viewerBypassesTerritoryScope()
            && Schema::hasColumn($table, 'induk_pemerintahan');
        $organisasiFilterOptions = $this->personilOrganisasiFilterOptions($table, $request);

        $canEditViaAdminUsers = $actor instanceof Usm && ! RoleMutationPolicy::deniesAllMutations($actor);
        $adminUserEditUrls = [];
        if ($canEditViaAdminUsers && $actor instanceof Usm) {
            foreach ($data as $item) {
                if (RoleMutationPolicy::canMutatePersonilRow($actor, $item)) {
                    continue;
                }
                $target = Usm::find($item->id_usm);
                if ($target instanceof Usm && UserListingScope::actorCanAccessUserRecord($actor, $target)) {
                    $adminUserEditUrls[(int) $item->id_usm] = route('admin.users.edit', $item->id_usm);
                }
            }
        }

        return view($this->viewFolder.'.index', array_merge([
            'pageTitle' => $this->getPageTitle(),
            'personilStats' => $personilStats,
            'data' => $data,
            'tableName' => $table,
            'primaryKey' => $this->primaryKey,
            'sboOperatorOwnership' => RoleMutationPolicy::isOperatorRole(Auth::user()),
            'listingTerritoryHint' => $this->listingTerritoryHint,
            'listingTerritoryBlocked' => $this->listingTerritoryBlocked,
            'canFilterIndukOrganisasi' => $canFilterIndukOrganisasi,
            'organisasiFilterOptions' => $organisasiFilterOptions,
            'indukOrganisasiFilterOptions' => $organisasiFilterOptions,
            'actorIndukOrganisasi' => $actor instanceof Usm ? trim((string) ($actor->induk_pemerintahan ?? '')) : '',
            'canEditViaAdminUsers' => $canEditViaAdminUsers,
            'adminUserEditUrls' => $adminUserEditUrls,
        ], \App\Support\TerritoryIndexFilters::viewBundle($request)));
    }

    public function create()
    {
        RoleMutationPolicy::assertOperatorPersonilModuleReadOnly(Auth::user());

        return view($this->viewFolder.'.create', array_merge(
            $this->formSharedData(),
            ['pageTitle' => 'Tambah '.$this->getPageTitle()]
        ));
    }

    public function store(Request $request)
    {
        RoleMutationPolicy::assertOperatorPersonilModuleReadOnly(Auth::user());

        $validated = $this->validateOperatorPayload($request);
        $validated['user_role_id'] = (int) config('role_menu_matrix.operator_matrix_role_id', 3);
        $validated['password'] = Hash::make($validated['password']);
        unset($validated['password_confirmation']);

        if ($request->hasFile('fotoprof')) {
            $validated['fotoprof'] = $request->file('fotoprof')->store('profiles', 'public');
        } else {
            $validated['fotoprof'] = '';
        }

        $validated = LegacyUsmDbInput::normalizeOptionalTextFields($validated);

        $this->tableName = 'usm';
        $this->insertLegacyRow($validated);

        return redirect()->route($this->getRoutePrefix().'.index')
            ->with('success', 'Operator berhasil ditambahkan. Data dapat diubah juga dari menu Manajemen User.');
    }

    public function show(string|int $id)
    {
        $data = $this->findScopedOperator($id);

        return view($this->viewFolder.'.show', array_merge(
            $this->adminUserManagementContext(Usm::find($data->id_usm)),
            [
                'pageTitle' => 'Detail '.$this->getPageTitle(),
                'data' => $data,
                'tableName' => $this->sourceTable(),
                'primaryKey' => $this->primaryKey,
                'sboOperatorOwnership' => RoleMutationPolicy::isOperatorRole(Auth::user()),
            ]
        ));
    }

    public function edit(string|int $id)
    {
        $data = $this->findScopedOperator($id);
        RoleMutationPolicy::assertOperatorCanMutatePersonilRow(Auth::user(), $data);

        return view($this->viewFolder.'.edit', array_merge(
            $this->formSharedData(Usm::find($data->id_usm)),
            [
                'pageTitle' => 'Edit '.$this->getPageTitle(),
                'data' => $data,
            ]
        ));
    }

    public function update(Request $request, string|int $id)
    {
        $row = $this->findScopedOperator($id);
        RoleMutationPolicy::assertOperatorCanMutatePersonilRow(Auth::user(), $row);

        $validated = $this->validateOperatorPayload($request, (int) $row->id_usm);

        if ($request->filled('password')) {
            $validated['password'] = Hash::make($validated['password']);
        } else {
            unset($validated['password']);
        }
        unset($validated['password_confirmation']);

        if ($request->hasFile('fotoprof')) {
            $validated['fotoprof'] = $request->file('fotoprof')->store('profiles', 'public');
        }

        $validated['user_role_id'] = (int) config('role_menu_matrix.operator_matrix_role_id', 3);
        $validated = LegacyUsmDbInput::normalizeOptionalTextFields($validated);

        DB::table('usm')->where('id_usm', $row->id_usm)->update($validated);

        return redirect()->route($this->getRoutePrefix().'.index')
            ->with('success', 'Data operator berhasil diupdate.');
    }

    public function destroy(string|int $id)
    {
        $row = $this->findScopedOperator($id);
        RoleMutationPolicy::assertOperatorCanMutatePersonilRow(Auth::user(), $row);

        DB::table('usm')->where('id_usm', $row->id_usm)->delete();

        return redirect()->route($this->getRoutePrefix().'.index')
            ->with('success', 'Operator berhasil dihapus.');
    }

    public function export(Request $request, $format = 'excel')
    {
        $table = $this->sourceTable();
        $this->tableName = $table;

        $this->listingTerritoryHint = null;
        $this->listingTerritoryBlocked = false;

        $query = $this->baseQuery();
        $this->applyPersonilBoTerritoryScope($query, $table);

        \App\Support\TerritoryIndexFilters::applyColumnFilters($query, $request, $table);
        $this->applyPersonilBoRequestFilters($query, $table, $request);

        if ($request->filled('search')) {
            $s = $request->search;
            $searchFields = array_values(array_filter(
                $this->searchFields,
                fn (string $field): bool => Schema::hasColumn($table, $field)
            ));
            $query->where(function ($q) use ($s, $table, $searchFields) {
                foreach ($searchFields as $field) {
                    $q->orWhere($table.'.'.$field, 'like', "%{$s}%");
                }
                $q->orWhere('roles.role_name', 'like', "%{$s}%");
            });
        }

        if ($request->filled('id')) {
            $query->where($table.'.id_usm', $request->id);
        }

        $data = $query->orderBy($table.'.nama')->get();
        $isSingle = $request->filled('id') && $data->count() === 1;
        $filename = $request->get('filename', $isSingle
            ? 'kuat_personil_bo_'.$request->id.'_'.date('Y-m-d')
            : 'kuat_personil_bo_'.date('Y-m-d'));
        $title = $request->get('title', $isSingle
            ? 'Detail '.$this->getPageTitle()
            : $this->getPageTitle().' Report');

        switch (strtolower($format)) {
            case 'pdf':
                $view = $request->get('view', 'exports.pdf.'.$this->viewFolder);
                if (! View::exists($view)) {
                    $view = 'exports.pdf.default';
                }

                return $this->exportToPdf($data, $view, $filename, $title);
            case 'excel':
                return $this->exportToExcel($data, $filename, $title);
            case 'csv':
                return $this->exportToCsv($data, $filename);
            case 'word':
                $view = $request->get('view', 'exports.word.'.$this->viewFolder);
                if (! View::exists($view)) {
                    $view = 'exports.word.default';
                }

                return $this->exportToWord($data, $view, $filename, $title);
            case 'json':
                return $this->exportToJson($data, $filename);
            default:
                abort(400, 'Invalid export format');
        }
    }

    protected function findScopedOperator(int|string $id): object
    {
        $table = $this->sourceTable();
        $query = $this->baseQuery()->where($table.'.'.$this->primaryKey, $id);
        $this->applyPersonilBoTerritoryScope($query, $table);

        $data = $query->first();
        if (! $data) {
            abort(404, 'Data operator tidak ditemukan.');
        }

        return $data;
    }

    public function operatorIsInListingScope(int|string $id): bool
    {
        $table = $this->sourceTable();
        $query = $this->baseQuery()->where($table.'.'.$this->primaryKey, $id);
        $this->applyPersonilBoTerritoryScope($query, $table);

        return $query->exists();
    }

    protected function personilFormPakaiMasterKl(?Usm $actor): bool
    {
        return UsmMasterReferensi::masterKlTigaTingkatAktif();
    }

    /** @return array{induk_pemerintahan: bool, pemerintahan_daerah: bool, organisasi_daerah: bool, unit_kerja: bool} */
    protected function kepegawaianFieldLocks(?Usm $actor, bool $isCreate = false): array
    {
        $unlocked = [
            'induk_pemerintahan' => false,
            'pemerintahan_daerah' => false,
            'organisasi_daerah' => false,
            'unit_kerja' => false,
        ];

        if (HierarchicalHelper::viewerBypassesTerritoryScope() || ! $actor instanceof Usm) {
            return $unlocked;
        }

        if ($this->actorIsAdminTier($actor) || MenuHelper::matrixRoleIdForUser($actor) === 1) {
            return $unlocked;
        }

        $hasKepegawaian = trim((string) ($actor->induk_pemerintahan ?? '')) !== ''
            || trim((string) ($actor->pemerintahan_daerah ?? '')) !== ''
            || trim((string) ($actor->organisasi_daerah ?? '')) !== ''
            || trim((string) ($actor->unit_kerja ?? '')) !== '';

        if ($hasKepegawaian) {
            return [
                'induk_pemerintahan' => true,
                'pemerintahan_daerah' => true,
                'organisasi_daerah' => true,
                'unit_kerja' => true,
            ];
        }

        return $unlocked;
    }

    protected function actorCanPickKepegawaian(?Usm $actor): bool
    {
        $locks = $this->kepegawaianFieldLocks($actor);

        return ! ($locks['induk_pemerintahan'] && $locks['pemerintahan_daerah'] && $locks['organisasi_daerah'] && $locks['unit_kerja']);
    }

    /**
     * @param  array<string, mixed>  $bundle
     * @return array<string, mixed>
     */
    protected function scopeKepegawaianFormBundleForActor(array $bundle, ?Usm $actor, array $locks, bool $klForm): array
    {
        if (! $actor instanceof Usm) {
            return $bundle;
        }

        $induk = trim((string) ($actor->induk_pemerintahan ?? ''));
        $pemda = trim((string) ($actor->pemerintahan_daerah ?? ''));
        $orgDaerah = trim((string) ($actor->organisasi_daerah ?? ''));

        if ($locks['induk_pemerintahan'] && ! $locks['pemerintahan_daerah'] && $induk !== '') {
            $resorts = collect($bundle['pemerintahanDaerahResorts'] ?? []);
            if ($resorts->isNotEmpty()) {
                $bundle['pemerintahanDaerahResorts'] = $resorts->filter(
                    fn ($row) => UsmMasterReferensi::indukOrganisasiNamesMatch($induk, (string) ($row->nameorg ?? ''))
                )->values();
                $bundle['pemerintahanDaerahUseSelect'] = $bundle['pemerintahanDaerahResorts']->isNotEmpty();
                $bundle['filterKepegawaianIndukPemerintahanDaerah'] = false;
            }
        }

        if ($locks['pemerintahan_daerah'] && ! $locks['organisasi_daerah'] && $pemda !== '' && $klForm) {
            $orgDaerahRows = collect($bundle['organisasiDaerahList'] ?? []);
            if ($orgDaerahRows->isNotEmpty()) {
                $bundle['organisasiDaerahList'] = $orgDaerahRows->filter(function ($row) use ($pemda, $induk) {
                    if (strcasecmp(trim((string) ($row->org_pusat ?? '')), $pemda) !== 0) {
                        return false;
                    }

                    return $induk === ''
                        || UsmMasterReferensi::indukOrganisasiNamesMatch($induk, (string) ($row->induk_pusat ?? ''));
                })->values();
                $bundle['organisasiDaerahUseSelect'] = $bundle['organisasiDaerahList']->isNotEmpty();
                $bundle['filterKepegawaianOrganisasiPusatDaerah'] = false;
            }
        }

        if ($locks['organisasi_daerah'] && ! $locks['unit_kerja'] && $orgDaerah !== '' && $klForm) {
            $units = collect($bundle['suborgsUnitKerja'] ?? []);
            if ($units->isNotEmpty()) {
                $bundle['suborgsUnitKerja'] = $units->filter(function ($row) use ($orgDaerah, $pemda, $induk) {
                    if (strcasecmp(trim((string) ($row->org_daerah ?? $row->nameorg ?? '')), $orgDaerah) !== 0) {
                        return false;
                    }
                    if ($pemda !== '' && strcasecmp(trim((string) ($row->org_pusat ?? $row->nameorg ?? '')), $pemda) !== 0) {
                        return false;
                    }

                    return $induk === ''
                        || UsmMasterReferensi::indukOrganisasiNamesMatch($induk, (string) ($row->induk_pusat ?? ''));
                })->values();
                $bundle['unitKerjaUseSelect'] = $bundle['suborgsUnitKerja']->isNotEmpty();
                $bundle['filterKepegawaianOrganisasiDaerahUnit'] = false;
                $bundle['filterKepegawaianIndukUnit'] = false;
            }
        } elseif ($locks['pemerintahan_daerah'] && ! $locks['unit_kerja'] && $pemda !== '' && $klForm && ! ($bundle['kepegawaianKlEmpatTingkat'] ?? false)) {
            $units = collect($bundle['suborgsUnitKerja'] ?? []);
            if ($units->isNotEmpty()) {
                $bundle['suborgsUnitKerja'] = $units->filter(function ($row) use ($pemda, $induk) {
                    if (strcasecmp(trim((string) ($row->nameorg ?? '')), $pemda) !== 0) {
                        return false;
                    }

                    return $induk === ''
                        || UsmMasterReferensi::indukOrganisasiNamesMatch($induk, (string) ($row->induk_pusat ?? ''));
                })->values();
                $bundle['unitKerjaUseSelect'] = $bundle['suborgsUnitKerja']->isNotEmpty();
                $bundle['filterKepegawaianOrganisasiDaerahUnit'] = false;
                $bundle['filterKepegawaianIndukUnit'] = false;
            }
        }

        return $bundle;
    }

    /** @return array<string, mixed> */
    protected function formSharedData(?Usm $row = null): array
    {
        $actor = Auth::user();
        $canPickTerritory = HierarchicalHelper::viewerBypassesTerritoryScope()
            || (! $actor || (empty($actor->daerah) && empty($actor->wilayah)));
        $actorUsm = $actor instanceof Usm ? $actor : null;
        $isCreate = $row === null;
        $kepegawaianFieldLocks = $this->kepegawaianFieldLocks($actorUsm, $isCreate);
        $canPickKepegawaian = $this->actorCanPickKepegawaian($actorUsm);
        $klForm = $this->personilFormPakaiMasterKl($actorUsm);
        $kepegawaian = $klForm
            ? UsmMasterReferensi::registrationKepegawaianFormBundle($row)
            : UsmMasterReferensi::kepegawaianFormBundle($row);
        $kepegawaian = $this->scopeKepegawaianFormBundleForActor($kepegawaian, $actorUsm, $kepegawaianFieldLocks, $klForm);
        $territoriMaster = UsmMasterReferensi::wilayahDaerahMasterBundle();
        $daerahList = ($canPickTerritory && ($territoriMaster['daerahList'] ?? collect())->isNotEmpty())
            ? $territoriMaster['daerahList']
            : (Schema::hasTable('daerah')
                ? DB::table('daerah')->orderBy('namadaerah')->get()
                : collect());

        return array_merge(
            $this->adminUserManagementContext($row instanceof Usm ? $row : null),
            [
                'canPickTerritory' => $canPickTerritory,
                'canPickKepegawaian' => $canPickKepegawaian,
                'kepegawaianFieldLocks' => $kepegawaianFieldLocks,
                'isCreatePersonilForm' => $isCreate,
                'defaultDaerah' => $actor->daerah ?? '',
                'defaultWilayah' => $actor->wilayah ?? '',
                'defaultIndukPemerintahan' => $actor->induk_pemerintahan ?? '',
                'defaultPemerintahanDaerah' => $actor->pemerintahan_daerah ?? '',
                'defaultOrganisasiDaerah' => $actor->organisasi_daerah ?? '',
                'defaultUnitKerja' => $actor->unit_kerja ?? '',
                'daerahList' => $daerahList,
                'wilayahByDaerahJson' => $territoriMaster['wilayahByDaerahJson'] ?? [],
                'wilayahPakaiMasterTerritori' => $territoriMaster['wilayahPakaiMasterTerritori'] ?? false,
                'kepegawaianKlTigaTingkat' => $klForm,
            ],
            $kepegawaian
        );
    }

    /** @return array<string, mixed> */
    protected function adminUserManagementContext(?Usm $target = null): array
    {
        $actor = Auth::user();
        $canMutateAdminUsers = $actor instanceof Usm && ! RoleMutationPolicy::deniesAllMutations($actor);
        $canAccessTarget = $target instanceof Usm && $actor instanceof Usm
            && UserListingScope::actorCanAccessUserRecord($actor, $target);

        return [
            'canManageFromAdminUsers' => $canMutateAdminUsers && ($target === null || $canAccessTarget),
            'adminUsersEditUrl' => ($canMutateAdminUsers && $canAccessTarget && $target instanceof Usm)
                ? route('admin.users.edit', $target->id_usm)
                : null,
            'adminUsersIndexUrl' => route('admin.users.index'),
        ];
    }

    /** @return array<string, mixed> */
    protected function validateOperatorPayload(Request $request, ?int $ignoreId = null): array
    {
        $actor = Auth::user();
        $canPickTerritory = HierarchicalHelper::viewerBypassesTerritoryScope()
            || (! $actor || (empty($actor->daerah) && empty($actor->wilayah)));

        $rules = [
            'username' => [
                'required', 'string', 'max:255',
                Rule::unique('usm', 'username')->ignore($ignoreId, 'id_usm'),
            ],
            'email' => [
                'required', 'email', 'max:255',
                Rule::unique('usm', 'email')->ignore($ignoreId, 'id_usm'),
            ],
            'nama' => 'required|string|max:255',
            'notelp' => 'nullable|string|max:100',
            'account_status' => 'required|in:active,pending,blocked,Active,Pending,Blocked',
            'fotoprof' => 'nullable|image|max:2048',
        ];

        if ($ignoreId === null) {
            $rules['password'] = 'required|string|min:6|confirmed';
        } else {
            $rules['password'] = 'nullable|string|min:6|confirmed';
        }

        if ($canPickTerritory) {
            $wilayahRules = UsmMasterReferensi::daerahWilayahValidationRules();
            $rules['daerah'] = array_merge(['required'], array_values(array_filter(
                $wilayahRules['daerah'] ?? ['string', 'max:255'],
                static fn ($rule): bool => $rule !== 'nullable'
            )));
            $rules['wilayah'] = $wilayahRules['wilayah'] ?? ['nullable', 'string', 'max:255'];
        }

        $klForm = $this->personilFormPakaiMasterKl($actor instanceof Usm ? $actor : null);
        $rules = array_merge(
            $rules,
            $klForm
                ? UsmMasterReferensi::registrationKepegawaianValidationRules()
                : UsmMasterReferensi::kepegawaianValidationRules()
        );

        $validated = $request->validate($rules);

        if (! $canPickTerritory && $actor instanceof Usm) {
            $validated['daerah'] = (string) ($actor->daerah ?? '');
            $validated['wilayah'] = (string) ($actor->wilayah ?? '');
            $this->assertActorCanManageTerritory($actor, $validated['daerah'], $validated['wilayah']);
        } else {
            $validated['daerah'] = trim((string) ($validated['daerah'] ?? ''));
            $validated['wilayah'] = trim((string) ($validated['wilayah'] ?? ''));
        }

        $isCreate = $ignoreId === null;
        $kepegawaianFieldLocks = $this->kepegawaianFieldLocks($actor instanceof Usm ? $actor : null, $isCreate);
        if ($actor instanceof Usm) {
            foreach (['induk_pemerintahan', 'pemerintahan_daerah', 'organisasi_daerah', 'unit_kerja'] as $field) {
                if ($kepegawaianFieldLocks[$field] ?? false) {
                    $validated[$field] = (string) ($actor->{$field} ?? '');
                } else {
                    $validated[$field] = trim((string) ($validated[$field] ?? ''));
                }
            }
            $validated['nrp_nip_nik'] = trim((string) ($validated['nrp_nip_nik'] ?? ''));
            $adminFullKepegawaian = $this->actorIsAdminTier($actor)
                || MenuHelper::matrixRoleIdForUser($actor) === 1;
            if (! $adminFullKepegawaian) {
                $this->assertActorCanManageKepegawaian($actor, $validated);
            }
        } else {
            foreach (['induk_pemerintahan', 'pemerintahan_daerah', 'organisasi_daerah', 'unit_kerja', 'nrp_nip_nik'] as $key) {
                $validated[$key] = trim((string) ($validated[$key] ?? ''));
            }
        }

        if ($klForm) {
            UsmMasterReferensi::validasiKepegawaianMasterKl(
                $validated['induk_pemerintahan'] ?? '',
                $validated['pemerintahan_daerah'] ?? '',
                $validated['organisasi_daerah'] ?? '',
                $validated['unit_kerja'] ?? ''
            );
        } else {
            UsmMasterReferensi::validasiUnitKerjaTerhadapInduk(
                $validated['induk_pemerintahan'] ?? '',
                $validated['unit_kerja'] ?? ''
            );
        }

        $validated['account_status'] = strtolower((string) $validated['account_status']);

        return LegacyUsmDbInput::normalizeOptionalTextFields($validated);
    }

    /** @param  array<string, mixed>  $values */
    protected function assertActorCanManageKepegawaian(Usm $actor, array $values): void
    {
        if (MenuHelper::matrixRoleIdForUser($actor) === 1) {
            return;
        }

        if ($this->actorIsAdminTier($actor) && ! KepegawaianListingScope::actorHasScope($actor)) {
            return;
        }

        if (! KepegawaianListingScope::actorHasScope($actor)) {
            return;
        }

        $candidate = new Usm;
        foreach (['induk_pemerintahan', 'pemerintahan_daerah', 'organisasi_daerah', 'unit_kerja'] as $field) {
            $candidate->{$field} = trim((string) ($values[$field] ?? ''));
        }

        if (! KepegawaianListingScope::targetInActorScope($actor, $candidate)) {
            throw ValidationException::withMessages([
                'pemerintahan_daerah' => ['Personil hanya dapat dikelola dalam cakupan organisasi kerja Anda (Polda / Polres / unit).'],
            ]);
        }
    }

    protected function assertActorCanManageTerritory(Usm $actor, string $daerah, string $wilayah): void
    {
        if (MenuHelper::matrixRoleIdForUser($actor) === 1) {
            return;
        }

        if (! empty($actor->wilayah)) {
            if ($daerah !== (string) ($actor->daerah ?? '') || $wilayah !== (string) ($actor->wilayah ?? '')) {
                throw ValidationException::withMessages([
                    'wilayah' => ['Operator hanya dapat mengelola personil di kab/kota kerja Anda.'],
                ]);
            }

            return;
        }

        if (! empty($actor->daerah) && $daerah !== (string) $actor->daerah) {
            throw ValidationException::withMessages([
                'daerah' => ['Operator hanya dapat mengelola personil di provinsi kerja Anda.'],
            ]);
        }
    }

    protected function getPageTitle()
    {
        return 'Kuat Personil BO';
    }

    protected function getRoutePrefix()
    {
        return 'personil.manager';
    }
}
