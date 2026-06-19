<?php

namespace App\Http\Controllers;

use App\Helpers\HierarchicalHelper;
use App\Support\RoleMutationPolicy;
use App\Support\TerritoryIndexFilters;
use Illuminate\Database\Query\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Session;

class ContigencyController extends OperasionalController
{
    protected $tableName = 'contigency';

    protected $primaryKey = 'id_contigency';

    protected $viewFolder = 'contigency';

    protected $fillable = [
        'tanggal', 'piket', 'lokasi', 'koordinat',
        'jeniscontigency', 'faktor', 'penyebab', 'korbanmd', 'korbanlb', 'korbanlr',
        'kermat', 'petugastptkp', 'fotosit', 'ketcb', 'jamperistiwa',
    ];

    protected $searchFields = ['jeniscontigency', 'lokasi', 'faktor', 'penyebab', 'petugastptkp', 'ketcb'];

    protected $defaultOrderBy = 'tanggal';

    protected $defaultOrderType = 'desc';

    public function __construct()
    {
        parent::__construct();

        $this->middleware(function (Request $request, $next) {
            if ($request->routeIs('contigency.*')) {
                $sbGrp = $request->query('sb_grp') ?: $request->input('sb_grp');
                if ($sbGrp) {
                    session(['contigency_sb_grp' => (string) $sbGrp]);
                }
            }

            return $next($request);
        });
    }

    /**
     * @return array<string, string>
     */
    protected function sbGrpFromRequest(Request $request): array
    {
        $grp = array_filter((array) $request->only('sb_grp'));
        if ($grp === [] && Session::has('contigency_sb_grp')) {
            $grp = ['sb_grp' => Session::get('contigency_sb_grp')];
        }

        return $grp;
    }

    protected function currentSbGrp(Request $request): string
    {
        return (string) ($request->query('sb_grp') ?: $request->input('sb_grp') ?: Session::get('contigency_sb_grp', ''));
    }

    protected function reportPageTitle(Request $request): string
    {
        return match ($this->currentSbGrp($request)) {
            'quick_respons' => 'Report Kodal : QR Contigency',
            'opsdal' => 'Report Kodal Kontijensi',
            default => 'Report Kodal Contigency',
        };
    }

    protected function detailPageTitle(Request $request): string
    {
        $grp = (string) ($request->query('sb_grp') ?: Session::get('contigency_sb_grp', ''));

        return match ($grp) {
            'quick_respons' => 'Detail QR Contigency',
            default => 'Detail Kodal Contigency',
        };
    }

    protected function formPageTitle(string $action = 'create'): string
    {
        $grp = (string) (Session::get('contigency_sb_grp', '') ?: request()->query('sb_grp', ''));
        $isQr = $grp === 'quick_respons';

        return match ($action) {
            'create' => $isQr ? 'Tambah QR Contigency' : 'Tambah Kodal Contigency',
            'edit' => $isQr ? 'Edit QR Contigency' : 'Edit Kodal Contigency',
            default => $isQr ? 'QR Contigency' : 'Kodal Contigency',
        };
    }

    /**
     * @return \Illuminate\Support\Collection<int, string>
     */
    protected function lookupList(string $table, string $column)
    {
        if (! Schema::hasTable($table)) {
            return collect();
        }

        return DB::table($table)->orderBy($column)->pluck($column);
    }

    protected function applyFilters($query, Request $request)
    {
        $query->leftJoin('usm', 'contigency.piket', '=', 'usm.id_usm');

        $select = [
            'contigency.*',
            'usm.nama as usm_nama',
            DB::raw('usm.daerah as daerah_nama'),
            DB::raw('usm.wilayah as wilayah_nama'),
        ];

        if (Schema::hasColumn($this->tableName, 'operator_edited_by')) {
            $query->leftJoin('usm as editor_usm', 'contigency.operator_edited_by', '=', 'editor_usm.id_usm');
            $select[] = 'editor_usm.nama as operator_editor_nama';
        }

        $query->select($select);

        return $query;
    }

    protected function applyReportFilters(Builder $query, Request $request): void
    {
        if ($request->filled('tanggal_dari')) {
            $query->where('contigency.tanggal', '>=', $request->tanggal_dari);
        }
        if ($request->filled('tanggal_sampai')) {
            $query->where('contigency.tanggal', '<=', $request->tanggal_sampai);
        }
        if ($request->filled('jenis_contigency')) {
            $query->where('contigency.jeniscontigency', $request->jenis_contigency);
        }
        if ($request->filled('faktor')) {
            $query->where('contigency.faktor', $request->faktor);
        }
        if ($request->filled('status_konfirmasi_opsdal')) {
            $query->where('contigency.status_konfirmasi_opsdal', $request->status_konfirmasi_opsdal);
        }

        TerritoryIndexFilters::applyUsmFilters($query, $request);
        $this->applySbGrpScope($query, $request);
    }

    /**
     * Pisahkan daftar per menu sidebar (Opsdal / QR).
     */
    protected function applySbGrpScope(Builder $query, Request $request): void
    {
        $grp = $this->currentSbGrp($request);
        if ($grp === '') {
            return;
        }

        $table = $this->tableName;
        $hasLink = Schema::hasColumn($table, 'id_patrolicctv');
        $hasSbGrp = Schema::hasColumn($table, 'sb_grp');

        if (! $hasLink && ! $hasSbGrp) {
            return;
        }

        if ($grp === 'opsdal') {
            $query->where(function ($q) use ($table, $hasLink, $hasSbGrp) {
                if ($hasLink) {
                    $q->whereNotNull($table.'.id_patrolicctv');
                }
                if ($hasSbGrp) {
                    $hasLink ? $q->orWhere($table.'.sb_grp', 'opsdal') : $q->where($table.'.sb_grp', 'opsdal');
                }
            });

            return;
        }

        if ($grp === 'quick_respons') {
            $query->where(function ($q) use ($table, $hasLink, $hasSbGrp) {
                if ($hasLink) {
                    $q->whereNull($table.'.id_patrolicctv');
                }
                if ($hasSbGrp) {
                    $q->where(function ($inner) use ($table) {
                        $inner->whereNull($table.'.sb_grp')
                            ->orWhere($table.'.sb_grp', '')
                            ->orWhere($table.'.sb_grp', 'quick_respons');
                    });
                }
            });
        }
    }

    public function index(Request $request)
    {
        $sbGrp = $this->sbGrpFromRequest($request);
        $tab = $request->get('tab', 'rinci');

        $query = DB::table($this->tableName);
        $query = $this->applyFilters($query, $request);
        HierarchicalHelper::applyTerritoryScopeForTable($query, $this->tableName);
        $this->applyReportFilters($query, $request);

        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(function ($q) use ($s) {
                foreach ($this->searchFields as $field) {
                    $q->orWhere('contigency.'.$field, 'like', "%{$s}%");
                }
                $q->orWhere('usm.nama', 'like', "%{$s}%")
                    ->orWhere('usm.daerah', 'like', "%{$s}%")
                    ->orWhere('usm.wilayah', 'like', "%{$s}%");
            });
        }

        $query->orderBy(
            'contigency.'.$request->get('orderby', $this->defaultOrderBy),
            $request->get('ordertype', $this->defaultOrderType)
        );
        if (Schema::hasColumn($this->tableName, 'status_konfirmasi_opsdal')) {
            $query->orderByRaw("CASE WHEN contigency.status_konfirmasi_opsdal = 'menunggu' THEN 0 ELSE 1 END");
        }

        $data = $query->paginate($request->get('per_page', $this->perPage))->withQueryString();

        $tabulasi = collect();
        if ($tab === 'tabulasi') {
            $tabQ = DB::table($this->tableName)
                ->leftJoin('usm', 'contigency.piket', '=', 'usm.id_usm');
            HierarchicalHelper::applyTerritoryScopeForTable($tabQ, $this->tableName);
            $this->applyReportFilters($tabQ, $request);

            $tabulasi = $tabQ
                ->selectRaw('COALESCE(NULLIF(usm.daerah, ""), "-") as daerah_nama')
                ->selectRaw('COALESCE(NULLIF(contigency.jeniscontigency, ""), "-") as jenis_contigency')
                ->selectRaw('COUNT(*) as jumlah')
                ->groupBy('daerah_nama', 'jenis_contigency')
                ->orderBy('daerah_nama')
                ->orderBy('jenis_contigency')
                ->get();
        }

        $jenisList = Schema::hasTable('t_jeniscontigency')
            ? DB::table('t_jeniscontigency')->orderBy('namecontigency')->pluck('namecontigency')
            : DB::table($this->tableName)->whereNotNull('jeniscontigency')->distinct()->orderBy('jeniscontigency')->pluck('jeniscontigency');

        return view($this->viewFolder.'.index', array_merge([
            'pageTitle' => $this->reportPageTitle($request),
            'data' => $data,
            'tabulasi' => $tabulasi,
            'tab' => $tab,
            'jenisList' => $jenisList,
            'faktorList' => $this->lookupList('t_faktor', 'namefaktor'),
            'sbGrp' => $sbGrp,
            'tableName' => $this->tableName,
            'primaryKey' => $this->primaryKey,
            'sboViewOnly' => RoleMutationPolicy::deniesAllMutations(Auth::user()),
        ], TerritoryIndexFilters::viewBundle($request)));
    }

    public function show(string|int $id)
    {
        $showQuery = DB::table($this->tableName)
            ->leftJoin('usm', 'contigency.piket', '=', 'usm.id_usm');

        $showSelect = [
            'contigency.*',
            'usm.nama as usm_nama',
            DB::raw('usm.daerah as daerah_nama'),
            DB::raw('usm.wilayah as wilayah_nama'),
        ];

        if (Schema::hasColumn($this->tableName, 'operator_edited_by')) {
            $showQuery->leftJoin('usm as editor_usm', 'contigency.operator_edited_by', '=', 'editor_usm.id_usm');
            $showSelect[] = 'editor_usm.nama as operator_editor_nama';
        }

        $data = $showQuery->select($showSelect)
            ->where('contigency.'.$this->primaryKey, $id)->first();

        if (! $data) {
            abort(404, 'Data tidak ditemukan');
        }

        $req = app(Request::class);

        return view($this->viewFolder.'.show', array_merge([
            'pageTitle' => $this->detailPageTitle($req),
            'data' => $data,
            'sbGrp' => $this->sbGrpFromRequest(app(Request::class)),
            'tableName' => $this->tableName,
            'primaryKey' => $this->primaryKey,
        ], $this->operationalRecordViewFlags($data), [
            'sboViewOnly' => RoleMutationPolicy::deniesAllMutations(Auth::user()),
        ]));
    }

    protected function canConfirmOpsdalRow(object $row): bool
    {
        $user = Auth::user();
        if (! $user) {
            return false;
        }
        if (RoleMutationPolicy::deniesAllMutations($user)) {
            return false;
        }
        if (\App\Helpers\MenuHelper::isSuperAdminForLayout($user)) {
            return true;
        }

        $actorDaerah = trim((string) ($user->daerah ?? ''));
        $actorWilayah = trim((string) ($user->wilayah ?? ''));
        $rowDaerah = trim((string) ($row->daerah_nama ?? ''));
        $rowWilayah = trim((string) ($row->wilayah_nama ?? ''));

        if ($actorDaerah === '' || $rowDaerah === '' || strcasecmp($actorDaerah, $rowDaerah) !== 0) {
            return false;
        }

        if ($actorWilayah !== '') {
            return $rowWilayah !== '' && strcasecmp($actorWilayah, $rowWilayah) === 0;
        }

        return true;
    }

    public function confirmOpsdal(Request $request, string|int $id)
    {
        $row = DB::table($this->tableName)
            ->leftJoin('usm', 'contigency.piket', '=', 'usm.id_usm')
            ->select('contigency.*', 'usm.daerah as daerah_nama', 'usm.wilayah as wilayah_nama')
            ->where('contigency.'.$this->primaryKey, $id)
            ->first();
        if (! $row) {
            abort(404, 'Data tidak ditemukan');
        }
        if (! $this->canConfirmOpsdalRow($row)) {
            abort(403, 'Anda tidak berhak konfirmasi data lintas wilayah.');
        }

        $updates = [
            'status_konfirmasi_opsdal' => 'terkonfirmasi',
            'opsdal_confirmed_by' => RoleMutationPolicy::resolveActorUserId(Auth::user()),
            'opsdal_confirmed_at' => now(),
            'updated_at' => now(),
        ];
        if (Schema::hasColumn($this->tableName, 'operator_edited_at')) {
            $updates['operator_edited_at'] = now();
        }
        if (Schema::hasColumn($this->tableName, 'operator_edited_by')) {
            $updates['operator_edited_by'] = (string) (Auth::id() ?? '');
        }

        DB::table($this->tableName)->where($this->primaryKey, $id)->update($updates);

        return redirect()->back()->with('success', 'Data berhasil dikonfirmasi OPSDAL.');
    }

    public function rejectOpsdal(Request $request, string|int $id)
    {
        $validated = $request->validate([
            'opsdal_confirmation_note' => ['required', 'string', 'max:1000'],
        ]);

        $row = DB::table($this->tableName)
            ->leftJoin('usm', 'contigency.piket', '=', 'usm.id_usm')
            ->select('contigency.*', 'usm.daerah as daerah_nama', 'usm.wilayah as wilayah_nama')
            ->where('contigency.'.$this->primaryKey, $id)
            ->first();
        if (! $row) {
            abort(404, 'Data tidak ditemukan');
        }
        if (! $this->canConfirmOpsdalRow($row)) {
            abort(403, 'Anda tidak berhak konfirmasi data lintas wilayah.');
        }

        DB::table($this->tableName)->where($this->primaryKey, $id)->update([
            'status_konfirmasi_opsdal' => 'ditolak',
            'opsdal_confirmed_by' => RoleMutationPolicy::resolveActorUserId(Auth::user()),
            'opsdal_confirmed_at' => now(),
            'opsdal_confirmation_note' => $validated['opsdal_confirmation_note'],
            'updated_at' => now(),
        ]);

        return redirect()->back()->with('success', 'Data ditolak oleh OPSDAL.');
    }

    protected function getValidationRules($action = 'store')
    {
        $rules = [
            'tanggal'        => 'required|date',
            'lokasi'         => 'nullable|string|max:255',
            'jeniscontigency'=> 'nullable|string|max:100',
            // petugastptkp: array (multi-select, maks 5) atau string
            'petugastptkp'   => 'nullable',
            'petugastptkp.*' => 'nullable|string|max:100',
            'faktor'         => 'nullable|string|max:100',
            'penyebab'       => 'nullable|string|max:100',
            'korbanmd'       => 'nullable|integer|min:0',
            'korbanlb'       => 'nullable|integer|min:0',
            'korbanlr'       => 'nullable|integer|min:0',
            'kermat'         => 'nullable|integer|min:0',
            'ketcb'          => 'nullable|string',
            'jamperistiwa'   => 'nullable|string|max:20',
            'lat'            => ['nullable', 'numeric', 'between:-90,90'],
            'lng'            => ['nullable', 'numeric', 'between:-180,180'],
        ];

        return $rules;
    }

    /**
     * @param  array<string, mixed>  $rules
     */
    protected function applyFotoGpsValidationRules(array &$rules, Request $request): void
    {
        // SBO-REQ: Pastikan bisa tanpa GPS, tidak wajib latitude & longitude meskipun ada file upload.
        $rules['lat'] = ['nullable', 'numeric', 'between:-90,90'];
        $rules['lng'] = ['nullable', 'numeric', 'between:-180,180'];
    }

    /**
     * @return array<string, string>
     */
    protected function fotoGpsValidationMessages(): array
    {
        return [
            'lat.required' => 'Koordinat GPS wajib saat mengunggah foto situasi. Izinkan akses lokasi atau klik "Ambil lokasi GPS".',
            'lng.required' => 'Koordinat GPS wajib saat mengunggah foto situasi.',
        ];
    }

    protected function prepareDataForStore(array $validated, Request $request)
    {
        $data = $this->mergeFillableFromRequest($validated, $request);
        $data = $this->applyLegacyDefaults($data);

        if (Schema::hasColumn($this->tableName, 'koordinat')) {
            $data = array_merge($data, $this->koordinatFromRequest($request));
        }
        if (Schema::hasColumn($this->tableName, 'created_at')) {
            $data['created_at'] = now();
        }
        if (Schema::hasColumn($this->tableName, 'updated_at')) {
            $data['updated_at'] = now();
        }
        $data['piket'] = (string) ($data['piket'] ?? Auth::id() ?? '');
        $this->applySbGrpToManualRow($data, $request);

        return $data;
    }

    protected function prepareDataForUpdate(array $validated, Request $request)
    {
        $data = $this->mergeFillableFromRequest($validated, $request);
        $data = $this->applyLegacyDefaults($data, false);

        if (Schema::hasColumn($this->tableName, 'koordinat')) {
            foreach ($this->koordinatFromRequest($request) as $col => $val) {
                $data[$col] = $val;
            }
        }
        if (Schema::hasColumn($this->tableName, 'updated_at')) {
            $data['updated_at'] = now();
        }
        $this->applySbGrpToManualRow($data, $request);

        return $data;
    }

    /**
     * @param  array<string, mixed>  $data
     */
    protected function applySbGrpToManualRow(array &$data, Request $request): void
    {
        if (! Schema::hasColumn($this->tableName, 'sb_grp')) {
            return;
        }

        $grp = $this->currentSbGrp($request);
        if ($grp !== '') {
            $data['sb_grp'] = $grp;
        }
    }

    /**
     * @param  array<string, mixed>  $data
     */
    protected function stampOperatorEdit(array &$data): void
    {
        if (! Schema::hasColumn($this->tableName, 'operator_edited_at')) {
            return;
        }

        $data['operator_edited_at'] = now();
        if (Schema::hasColumn($this->tableName, 'operator_edited_by')) {
            $data['operator_edited_by'] = (string) (Auth::id() ?? '');
        }
    }

    /**
     * @return array<string, mixed>
     */
    protected function mergeFillableFromRequest(array $validated, Request $request): array
    {
        $data = [];
        foreach ($this->fillable as $field) {
            if (array_key_exists($field, $validated)) {
                $data[$field] = $validated[$field];
            } elseif ($request->has($field)) {
                $data[$field] = $request->input($field);
            }
        }

        return $data;
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function applyLegacyDefaults(array $data, bool $applyJamDefault = true): array
    {
        foreach (['lokasi', 'jeniscontigency', 'faktor', 'penyebab', 'petugastptkp', 'ketcb'] as $field) {
            if (! array_key_exists($field, $data) || $data[$field] === null) {
                $data[$field] = '';
            }
        }

        // Normalise petugastptkp: array → comma-separated string
        if (isset($data['petugastptkp']) && is_array($data['petugastptkp'])) {
            $data['petugastptkp'] = implode(', ', array_filter(array_map('trim', $data['petugastptkp'])));
        }

        foreach (['korbanmd', 'korbanlb', 'korbanlr', 'kermat'] as $field) {
            $data[$field] = (int) ($data[$field] ?? 0);
        }

        if ($applyJamDefault && (! array_key_exists('jamperistiwa', $data) || $data['jamperistiwa'] === null || $data['jamperistiwa'] === '')) {
            $data['jamperistiwa'] = now()->format('H:i:s');
        }

        return $data;
    }

    /**
     * @return array<string, string>
     */
    protected function koordinatFromRequest(Request $request): array
    {
        $lat = $request->input('lat');
        $lng = $request->input('lng');
        if ($lat !== null && $lat !== '' && $lng !== null && $lng !== '') {
            return ['koordinat' => $this->formatCoord((float) $lat).','.$this->formatCoord((float) $lng)];
        }

        return [];
    }

    private function formatCoord(float $n): string
    {
        $s = number_format($n, 8, '.', '');
        $s = rtrim(rtrim($s, '0'), '.');

        return $s === '-0' ? '0' : $s;
    }

    public function create()
    {
        return view($this->viewFolder.'.create', [
            'pageTitle' => $this->formPageTitle('create'),
            'jenisList' => Schema::hasTable('t_jeniscontigency')
                ? DB::table('t_jeniscontigency')->orderBy('namecontigency')->get()
                : collect(),
            'faktorList' => $this->lookupList('t_faktor', 'namefaktor'),
            'penyebabList' => $this->penyebabOptionsForForm(),
            'sbGrp' => $this->sbGrpFromRequest(app(Request::class)),
        ]);
    }

    public function edit(string|int $id)
    {
        $data = DB::table($this->tableName)->where($this->primaryKey, $id)->first();
        if (! $data) {
            abort(404, 'Data tidak ditemukan');
        }

        $this->assertOperatorOwnsOperationalRow($data);

        return view($this->viewFolder.'.edit', [
            'pageTitle' => $this->formPageTitle('edit'),
            'data' => $data,
            'jenisList' => Schema::hasTable('t_jeniscontigency')
                ? DB::table('t_jeniscontigency')->orderBy('namecontigency')->get()
                : collect(),
            'faktorList' => $this->lookupList('t_faktor', 'namefaktor'),
            'penyebabList' => $this->penyebabOptionsForForm(),
            'sbGrp' => $this->sbGrpFromRequest(app(Request::class)),
        ]);
    }

    /**
     * @return \Illuminate\Support\Collection<int, object>
     */
    protected function penyebabOptionsForForm()
    {
        if (! Schema::hasTable('t_penyebab')) {
            return collect();
        }

        return DB::table('t_penyebab')->orderBy('namepenyebab')->get();
    }

    public function store(Request $request)
    {
        $rules = $this->getValidationRules('store');
        $rules['fotosit_file'] = 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:8192';
        $this->applyFotoGpsValidationRules($rules, $request);
        $validated = $request->validate($rules, $this->fotoGpsValidationMessages());
        $data = $this->prepareDataForStore($validated, $request);

        if ($request->hasFile('fotosit_file')) {
            $path = $request->file('fotosit_file')->store('contigency_situasi', 'public');
            $data['fotosit'] = mb_substr('/storage/'.$path, 0, 255);
        }

        $this->insertLegacyRow($data);

        return redirect()->route($this->getRoutePrefix().'.index', $this->sbGrpFromRequest($request))
            ->with('success', 'Data berhasil ditambahkan.');
    }

    public function update(Request $request, string|int $id)
    {
        $row = DB::table($this->tableName)->where($this->primaryKey, $id)->first();
        if (! $row) {
            abort(404, 'Data tidak ditemukan');
        }

        $this->assertOperatorOwnsOperationalRow($row);

        $rules = $this->getValidationRules('update');
        $rules['fotosit_file'] = 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:8192';
        $this->applyFotoGpsValidationRules($rules, $request);
        $validated = $request->validate($rules, $this->fotoGpsValidationMessages());
        $data = $this->prepareDataForUpdate($validated, $request);

        if (($data['jamperistiwa'] ?? '') === '') {
            $data['jamperistiwa'] = $row->jamperistiwa ?? now()->format('H:i:s');
        }

        if ($request->hasFile('fotosit_file')) {
            $path = $request->file('fotosit_file')->store('contigency_situasi', 'public');
            $data['fotosit'] = mb_substr('/storage/'.$path, 0, 255);
        } else {
            $data['fotosit'] = $row->fotosit;
        }

        $this->stampOperatorEdit($data);

        DB::table($this->tableName)->where($this->primaryKey, $id)->update($data);

        return redirect()->route($this->getRoutePrefix().'.index', $this->sbGrpFromRequest($request))
            ->with('success', 'Data berhasil diupdate.');
    }

    public function destroy(string|int $id)
    {
        $row = DB::table($this->tableName)->where($this->primaryKey, $id)->first();
        if (! $row) {
            abort(404, 'Data tidak ditemukan');
        }
        $this->assertOperatorOwnsOperationalRow($row);

        DB::table($this->tableName)->where($this->primaryKey, $id)->delete();

        return redirect()->route($this->getRoutePrefix().'.index', $this->sbGrpFromRequest(app(Request::class)))
            ->with('success', 'Data berhasil dihapus.');
    }

    public function export(Request $request, $format = 'excel')
    {
        $query = DB::table($this->tableName);
        $query = $this->applyFilters($query, $request);
        HierarchicalHelper::applyTerritoryScopeForTable($query, $this->tableName);
        $this->applyReportFilters($query, $request);

        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(function ($q) use ($s) {
                foreach ($this->searchFields as $field) {
                    $q->orWhere('contigency.'.$field, 'like', "%{$s}%");
                }
                $q->orWhere('usm.nama', 'like', "%{$s}%");
            });
        }

        if ($request->filled('id') && $this->primaryKey) {
            $query->where('contigency.'.$this->primaryKey, $request->id);
        }

        $data = $query->get();
        $isSingle = $request->filled('id') && $data->count() === 1;
        $filename = $request->get('filename', $isSingle
            ? $this->tableName.'_'.$request->id.'_'.date('Y-m-d')
            : $this->tableName.'_'.date('Y-m-d'));
        $title = $isSingle ? 'Detail '.$this->reportPageTitle($request) : $this->reportPageTitle($request);

        return match (strtolower($format)) {
            'pdf' => $this->exportToPdf($data, 'exports.pdf.default', $filename, $title),
            'csv' => $this->exportToCsv($data, $filename),
            default => $this->exportToExcel($data, $filename, $title),
        };
    }

    protected function getPageTitle()
    {
        return 'Kodal Contigency';
    }

    protected function getRoutePrefix()
    {
        return 'contigency';
    }
}
