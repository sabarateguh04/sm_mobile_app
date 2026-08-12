<?php

namespace App\Http\Controllers;

use App\Helpers\HierarchicalHelper;
use App\Support\TerritoryIndexFilters;
use App\Helpers\MenuHelper;
use App\Support\RoleMutationPolicy;
use Illuminate\Database\Query\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Session;

class TurjagwaliController extends OperasionalController
{
    public function __construct()
    {
        parent::__construct();
        $this->middleware(function (Request $request, $next) {
            if (! $request->routeIs('turjagwali.*')) {
                return $next($request);
            }
            $q = [];
            if ($request->filled('sm_lv')) {
                $q['sm_lv'] = (string) $request->query('sm_lv');
            }
            abort_unless(MenuHelper::matrixAllowsPathForUser($request->user(), 'turjagwali', $q), 403);

            return $next($request);
        });
        $this->middleware(function (Request $request, $next) {
            if ($request->routeIs('turjagwali.*') && $request->filled('sm_lv')) {
                session(['turjagwali_sm_lv' => $request->query('sm_lv')]);
            }

            return $next($request);
        });
    }

    protected $tableName = 'turjagwali';

    protected $primaryKey = 'id_turjagwali';

    protected $viewFolder = 'turjagwali';

    protected $fillable = [
        'tanggal', 'piket', 'katgiat', 'lokasi', 'peristiwa',
        'ketcb', 'fotogiat', 'fotolokasi', 'koordinat', 'petugaslap', 'faktor', 'penyebab',
    ];

    protected $searchFields = ['katgiat', 'lokasi', 'peristiwa', 'petugaslap', 'faktor', 'penyebab', 'ketcb'];

    protected $defaultOrderBy = 'tanggal';

    protected $defaultOrderType = 'desc';

    protected function resolveHttpRequest(): Request
    {
        $request = request();

        return $request instanceof Request ? $request : app(Request::class);
    }

    /**
     * @return array<string, string>
     */
    protected function turjagwaliSidebarQuery(?Request $request = null): array
    {
        $request = $request ?? $this->resolveHttpRequest();
        $q = array_filter((array) $request->only('sm_lv'));
        if ($q === [] && Session::has('turjagwali_sm_lv')) {
            $q = ['sm_lv' => Session::get('turjagwali_sm_lv')];
        }

        return $q;
    }

    /**
     * @param  \Illuminate\Database\Query\Builder  $query
     * @return \Illuminate\Database\Query\Builder
     */
    protected function applyFilters($query, Request $request)
    {
        $query->leftJoin('usm', 'turjagwali.piket', '=', 'usm.id_usm')
            ->select(
                'turjagwali.*',
                'usm.nama as usm_nama',
                DB::raw('usm.daerah as daerah_nama'),
                DB::raw('usm.wilayah as wilayah_nama')
            );

        return $query;
    }

    /**
     * @param  Builder  $query
     */
    protected function applyReportFilters(Builder $query, Request $request): void
    {
        if ($request->filled('tanggal_dari')) {
            $query->where('turjagwali.tanggal', '>=', $request->tanggal_dari);
        }
        if ($request->filled('tanggal_sampai')) {
            $query->where('turjagwali.tanggal', '<=', $request->tanggal_sampai);
        }
        if ($request->filled('katgiat')) {
            $query->where('turjagwali.katgiat', $request->katgiat);
        }
        if ($request->filled('peristiwa')) {
            $query->where('turjagwali.peristiwa', $request->peristiwa);
        }
        if ($request->filled('faktor')) {
            $query->where('turjagwali.faktor', $request->faktor);
        }

        TerritoryIndexFilters::applyUsmFilters($query, $request);
    }

    public function index(Request $request)
    {
        $smLv = $this->turjagwaliSidebarQuery($request);
        $tab = $request->get('tab', 'dacol_rengiat');

        if ($tab === 'dacol_rengiat') {
            $table = 'sm_dacol_rengiat';
            $dacolRows = \App\Support\SmDacolData::paginated($table, $request, 20);
            return view($this->viewFolder.'.index', array_merge([
                'pageTitle' => 'Report Kodal Turjagwali',
                'tab' => $tab,
                'data' => collect(), // dummy
                'tabulasi' => collect(),
                'giatList' => collect(),
                'peristiwaList' => collect(),
                'faktorList' => collect(),
                'smLv' => $smLv,
                'tableName' => $this->tableName,
                'primaryKey' => $this->primaryKey,
                'sboViewOnly' => true,
                'hideAddButton' => true,
                
                'active' => $table,
                'meta' => config("smd_tables.dacol.{$table}", []),
                'rows' => $dacolRows,
                'displayColumns' => \App\Support\SmDacolData::displayColumns($table),
                'availableSources' => \App\Support\SmDacolData::availableSources($table),
                'filterUrl' => route('turjagwali.index', $smLv),
                'filterHiddenInputs' => '<input type="hidden" name="tab" value="'.$tab.'">',
                'filterResetUrl' => route('turjagwali.index', array_merge($smLv, ['tab' => $tab])),
            ], TerritoryIndexFilters::viewBundle($request)));
        }

        $query = DB::table($this->tableName);
        $query = $this->applyFilters($query, $request);
        HierarchicalHelper::applyTerritoryScopeForTable($query, $this->tableName);
        $this->applyReportFilters($query, $request);

        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(function ($q) use ($s) {
                foreach ($this->searchFields as $field) {
                    $q->orWhere('turjagwali.'.$field, 'like', "%{$s}%");
                }
                $q->orWhere('usm.nama', 'like', "%{$s}%")
                    ->orWhere('usm.daerah', 'like', "%{$s}%")
                    ->orWhere('usm.wilayah', 'like', "%{$s}%");
            });
        }

        $query->orderBy(
            'turjagwali.'.$request->get('orderby', $this->defaultOrderBy),
            $request->get('ordertype', $this->defaultOrderType)
        );

        $data = $query->paginate($request->get('per_page', $this->perPage))->withQueryString();

        $tabulasi = collect();
        if ($tab === 'tabulasi') {
            $tabQ = DB::table($this->tableName)
                ->leftJoin('usm', 'turjagwali.piket', '=', 'usm.id_usm');
            HierarchicalHelper::applyTerritoryScopeForTable($tabQ, $this->tableName);
            $this->applyReportFilters($tabQ, $request);

            $tabulasi = $tabQ
                ->selectRaw('COALESCE(NULLIF(usm.daerah, ""), "-") as daerah_nama')
                ->selectRaw('COALESCE(NULLIF(turjagwali.katgiat, ""), "-") as kat_giat')
                ->selectRaw('COUNT(*) as jumlah')
                ->groupBy('daerah_nama', 'kat_giat')
                ->orderBy('daerah_nama')
                ->orderBy('kat_giat')
                ->get();
        }

        return view($this->viewFolder.'.index', array_merge([
            'pageTitle' => 'Report Kodal Turjagwali',
            'data' => $data,
            'tabulasi' => $tabulasi,
            'tab' => $tab,
            'giatList' => $this->lookupList('t_giat', 'namegiat'),
            'peristiwaList' => $this->lookupList('t_peristiwa', 'nameperistiwa'),
            'faktorList' => $this->lookupList('t_faktor', 'namefaktor'),
            'smLv' => $smLv,
            'tableName' => $this->tableName,
            'primaryKey' => $this->primaryKey,
            'sboViewOnly' => RoleMutationPolicy::deniesAllMutations(Auth::user()),
            'hideAddButton' => \App\Helpers\MenuHelper::isAdminTerritorialForLayout(Auth::user()),
        ], TerritoryIndexFilters::viewBundle($request)));
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

    public function show(string|int $id)
    {
        $data = DB::table($this->tableName)
            ->leftJoin('usm', 'turjagwali.piket', '=', 'usm.id_usm')
            ->select(
                'turjagwali.*',
                'usm.nama as usm_nama',
                DB::raw('usm.daerah as daerah_nama'),
                DB::raw('usm.wilayah as wilayah_nama')
            )
            ->where('turjagwali.'.$this->primaryKey, $id)->first();

        if (! $data) {
            abort(404, 'Data tidak ditemukan');
        }

        return view($this->viewFolder.'.show', array_merge([
            'pageTitle' => 'Detail Kodal Turjagwali',
            'data' => $data,
            'smLv' => $this->turjagwaliSidebarQuery(),
            'tableName' => $this->tableName,
            'primaryKey' => $this->primaryKey,
        ], $this->operationalRecordViewFlags($data), [
            'sboViewOnly' => RoleMutationPolicy::deniesAllMutations(Auth::user()),
        ]));
    }

    public function create()
    {
        RoleMutationPolicy::assertCanCreate(Auth::user());
        if (\App\Helpers\MenuHelper::isAdminTerritorialForLayout(Auth::user())) {
            abort(403, 'Role Admin Polda dan Polres tidak diperkenankan menambah data Turjagwali.');
        }

        return view($this->viewFolder.'.create', [
            'pageTitle' => 'Tambah Kodal Turjagwali',
            'giatList' => $this->lookupList('t_giat', 'namegiat'),
            'peristiwaList' => $this->lookupList('t_peristiwa', 'nameperistiwa'),
            'faktorList' => $this->lookupList('t_faktor', 'namefaktor'),
            'penyebabList' => $this->penyebabOptionsForForm(),
            'smLv' => $this->turjagwaliSidebarQuery(),
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
            'pageTitle' => 'Edit Kodal Turjagwali',
            'data' => $data,
            'giatList' => $this->lookupList('t_giat', 'namegiat'),
            'peristiwaList' => $this->lookupList('t_peristiwa', 'nameperistiwa'),
            'faktorList' => $this->lookupList('t_faktor', 'namefaktor'),
            'penyebabList' => $this->penyebabOptionsForForm(),
            'smLv' => $this->turjagwaliSidebarQuery(),
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

    protected function getValidationRules($action = 'store')
    {
        return [
            'tanggal'    => 'required|date',
            'katgiat'    => 'required|string|max:100',
            'lokasi'     => 'required|string|max:255',
            'peristiwa'  => 'required|string|max:100',
            // petugaslap: bisa string (legacy) atau array (multi-select, max 5)
            'petugaslap'   => 'nullable',
            'petugaslap.*' => 'nullable|string|max:100',
            'faktor'     => 'required|string|max:100',
            'penyebab'   => 'required|string|max:100',
            'ketcb'      => 'nullable|string',
            'lat'        => ['nullable', 'numeric', 'between:-90,90'],
            'lng'        => ['nullable', 'numeric', 'between:-180,180'],
            'sm_lv'      => 'nullable|string|max:32',
        ];
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
            'lat.required' => 'Koordinat GPS wajib saat mengunggah foto giat. Izinkan akses lokasi atau klik "Ambil lokasi GPS".',
            'lng.required' => 'Koordinat GPS wajib saat mengunggah foto giat.',
        ];
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

    public function store(Request $request)
    {
        RoleMutationPolicy::assertCanCreate(Auth::user());
        if (\App\Helpers\MenuHelper::isAdminTerritorialForLayout(Auth::user())) {
            abort(403, 'Role Admin Polda dan Polres tidak diperkenankan menambah data Turjagwali.');
        }

        $rules = $this->getValidationRules('store');
        $rules['fotogiat_file'] = 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:8192';
        $this->applyFotoGpsValidationRules($rules, $request);
        $validated = $request->validate($rules, $this->fotoGpsValidationMessages());

        $data = $this->prepareDataForStore($validated, $request);

        if ($request->hasFile('fotogiat_file')) {
            $path = $request->file('fotogiat_file')->store('turjagwali_giat', 'public');
            $data['fotogiat'] = mb_substr('/storage/'.$path, 0, 255);
        }

        $this->insertLegacyRow($data);

        return redirect()->route($this->getRoutePrefix().'.index', $this->turjagwaliSidebarQuery($request))
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
        $rules['fotogiat_file'] = 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:8192';
        $this->applyFotoGpsValidationRules($rules, $request);
        $validated = $request->validate($rules, $this->fotoGpsValidationMessages());

        $data = $this->prepareDataForUpdate($validated, $request);

        if ($request->hasFile('fotogiat_file')) {
            $path = $request->file('fotogiat_file')->store('turjagwali_giat', 'public');
            $data['fotogiat'] = mb_substr('/storage/'.$path, 0, 255);
        } else {
            $data['fotogiat'] = $row->fotogiat;
        }

        DB::table($this->tableName)->where($this->primaryKey, $id)->update($data);

        return redirect()->route($this->getRoutePrefix().'.index', $this->turjagwaliSidebarQuery($request))
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

        return redirect()->route($this->getRoutePrefix().'.index', $this->turjagwaliSidebarQuery())
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
                    $q->orWhere('turjagwali.'.$field, 'like', "%{$s}%");
                }
                $q->orWhere('usm.nama', 'like', "%{$s}%");
            });
        }

        if ($request->filled('id') && $this->primaryKey) {
            $query->where('turjagwali.'.$this->primaryKey, $request->id);
        }

        $data = $query->get();
        $isSingle = $request->filled('id') && $data->count() === 1;
        $filename = $request->get('filename', $isSingle
            ? $this->tableName.'_'.$request->id.'_'.date('Y-m-d')
            : $this->tableName.'_'.date('Y-m-d'));
        $title = $isSingle ? 'Detail Report Kodal Turjagwali' : 'Report Kodal Turjagwali';

        return match (strtolower($format)) {
            'pdf' => $this->exportToPdf($data, 'exports.pdf.default', $filename, $title),
            'csv' => $this->exportToCsv($data, $filename),
            default => $this->exportToExcel($data, $filename, $title),
        };
    }

    protected function prepareDataForStore(array $validated, Request $request)
    {
        $data = [];
        foreach ($this->fillable as $f) {
            if (array_key_exists($f, $validated)) {
                $data[$f] = $validated[$f];
            }
        }
        // Normalise petugaslap: array → comma-separated string
        if (isset($data['petugaslap']) && is_array($data['petugaslap'])) {
            $data['petugaslap'] = implode(', ', array_filter(array_map('trim', $data['petugaslap'])));
        }
        if (Schema::hasColumn($this->tableName, 'koordinat')) {
            $data = array_merge($data, $this->koordinatFromRequest($request));
        }
        if (Schema::hasColumn($this->tableName, 'created_at')) {
            $data['created_at'] = now();
        }
        if (Schema::hasColumn($this->tableName, 'updated_at')) {
            $data['updated_at'] = now();
        }
        $data['piket'] = $data['piket'] ?? Auth::id();

        return $data;
    }

    protected function prepareDataForUpdate(array $validated, Request $request)
    {
        $data = [];
        foreach ($this->fillable as $f) {
            if (array_key_exists($f, $validated)) {
                $data[$f] = $validated[$f];
            }
        }
        // Normalise petugaslap: array → comma-separated string
        if (isset($data['petugaslap']) && is_array($data['petugaslap'])) {
            $data['petugaslap'] = implode(', ', array_filter(array_map('trim', $data['petugaslap'])));
        }
        if (Schema::hasColumn($this->tableName, 'koordinat')) {
            foreach ($this->koordinatFromRequest($request) as $col => $val) {
                $data[$col] = $val;
            }
        }
        if (Schema::hasColumn($this->tableName, 'updated_at')) {
            $data['updated_at'] = now();
        }

        return $data;
    }

    protected function getPageTitle()
    {
        return 'Kodal Turjagwali';
    }

    protected function getRoutePrefix()
    {
        return 'turjagwali';
    }
}
