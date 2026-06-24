<?php

namespace App\Http\Controllers;

use App\Helpers\HierarchicalHelper;
use App\Support\RoleMutationPolicy;
use App\Support\TerritoryIndexFilters;
use App\Support\ReferensiOptions;
use App\Support\TrafficDalkotAnalyticSync;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class TrafficsController extends OperasionalController
{
    public function __construct()
    {
        parent::__construct();
        $this->middleware(function ($request, $next) {
            if ($request->routeIs('traffics.*')) {
                session()->forget('trafficstuck_sb_grp');

                // Daftar Dalkot ditampilkan di Traffic Analytic (trafficstuck); form CRUD tetap di /traffics → tabel trafficdalkot.
                if ($request->isMethod('GET') && $request->routeIs('traffics.index')) {
                    return redirect()->route('report.traffic-analytic.index', $request->query());
                }
            }

            return $next($request);
        });
    }

    protected $tableName = 'trafficdalkot';

    protected $primaryKey = 'id_trafficdalkot';

    protected $viewFolder = 'traffics';

    protected $fillable = [
        'tanggal', 'piket', 'waktu', 'gerbangkota', 'araharus', 'jenisjalan',
        'r2', 'r4', 'r6', 'r6_plus', 'ketcb', 'fotosit', 'namajalan', 'jumlah', 'koordinat',
    ];

    protected $searchFields = [
        'gerbangkota', 'araharus', 'jenisjalan', 'namajalan', 'ketcb',
    ];

    protected $defaultOrderBy = 'tanggal';

    protected $defaultOrderType = 'desc';

    protected function applyFilters($query, Request $request)
    {
        $query->leftJoin('usm', 'trafficdalkot.piket', '=', 'usm.id_usm')
            ->select(
                'trafficdalkot.*',
                'usm.nama as usm_nama',
                DB::raw('usm.daerah as daerah_nama'),
                DB::raw('usm.wilayah as wilayah_nama')
            );

        return $query;
    }

    protected function applyReportFilters($query, Request $request): void
    {
        if ($request->filled('tanggal_dari')) {
            $query->where('trafficdalkot.tanggal', '>=', $request->tanggal_dari);
        }
        if ($request->filled('tanggal_sampai')) {
            $query->where('trafficdalkot.tanggal', '<=', $request->tanggal_sampai);
        }
        if ($request->filled('jenisjalan')) {
            $query->where('trafficdalkot.jenisjalan', $request->jenisjalan);
        }
        if ($request->filled('gerbangkota')) {
            $query->where('trafficdalkot.gerbangkota', $request->gerbangkota);
        }
        if ($request->filled('araharus')) {
            $query->where('trafficdalkot.araharus', $request->araharus);
        }

        TerritoryIndexFilters::applyUsmFilters($query, $request);
    }

    public function index(Request $request)
    {
        $query = DB::table($this->tableName);
        $query = $this->applyFilters($query, $request);
        HierarchicalHelper::applyTerritoryScopeForTable($query, $this->tableName);
        $this->applyReportFilters($query, $request);

        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(function ($q) use ($s) {
                foreach ($this->searchFields as $field) {
                    $q->orWhere('trafficdalkot.'.$field, 'like', "%{$s}%");
                }
                $q->orWhere('usm.nama', 'like', "%{$s}%");
            });
        }

        $query->orderBy(
            'trafficdalkot.'.$request->get('orderby', $this->defaultOrderBy),
            $request->get('ordertype', $this->defaultOrderType)
        );

        $data = $query->paginate($request->get('per_page', $this->perPage));

        $jenisJalanList = $this->jenisJalanOptions();

        return view($this->viewFolder.'.index', array_merge([
            'pageTitle' => $this->getPageTitle(),
            'data' => $data,
            'tableName' => $this->tableName,
            'primaryKey' => $this->primaryKey,
            'jenisJalanList' => $jenisJalanList,
            'gerbangKotaList' => $this->gerbangKotaOptions(),
            'arahArusList' => $this->arahArusOptions(),
        ], TerritoryIndexFilters::viewBundle($request)));
    }

    public function show(string|int $id)
    {
        $data = DB::table($this->tableName)
            ->leftJoin('usm', 'trafficdalkot.piket', '=', 'usm.id_usm')
            ->select(
                'trafficdalkot.*',
                'usm.nama as usm_nama',
                DB::raw('usm.daerah as daerah_nama'),
                DB::raw('usm.wilayah as wilayah_nama')
            )
            ->where('trafficdalkot.'.$this->primaryKey, $id)->first();

        if (! $data) {
            abort(404, 'Data tidak ditemukan');
        }

        $this->assertOperatorOwnsOperationalRow($data);

        return view($this->viewFolder.'.show', [
            'pageTitle' => 'Detail Report Traffic Analytic',
            'data' => $data,
            'tableName' => $this->tableName,
            'primaryKey' => $this->primaryKey,
            'backUrl' => route('report.traffic-analytic.index'),
            'canMutate' => ! RoleMutationPolicy::deniesAllMutations(Auth::user())
                && RoleMutationPolicy::canMutateOperationalRow(Auth::user(), $data, $this->tableName),
        ]);
    }

    public function create()
    {
        return view($this->viewFolder.'.create', [
            'pageTitle' => 'Tambah '.$this->getPageTitle(),
            'jenisJalanList' => $this->jenisJalanOptions(),
            'gerbangKotaList' => $this->gerbangKotaOptions(),
            'arahArusList' => $this->arahArusOptions(),
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
            'pageTitle' => 'Edit '.$this->getPageTitle(),
            'data' => $data,
            'jenisJalanList' => $this->jenisJalanOptions(),
            'gerbangKotaList' => $this->gerbangKotaOptions(),
            'arahArusList' => $this->arahArusOptions(),
        ]);
    }

    public function store(Request $request)
    {
        $rules = $this->getValidationRules('store');
        $rules['fotosit_file'] = 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:8192';
        $validated = $request->validate($rules);

        $data = $this->prepareDataForStore($validated, $request);

        if ($request->hasFile('fotosit_file')) {
            $path = $request->file('fotosit_file')->store('trafficdalkot_situasi', 'public');
            $data['fotosit'] = mb_substr('/storage/'.$path, 0, 255);
        }

        $dalkotId = $this->insertTrafficdalkotRow($data);
        $sync = TrafficDalkotAnalyticSync::upsertFromDalkot($dalkotId, $data);

        $redirect = redirect()->route('report.traffic-analytic.index')
            ->with('success', 'Data Traffic Dalkot berhasil ditambahkan.');

        if (! ($sync['ok'] ?? false)) {
            $redirect->with('warning', 'Data Dalkot tersimpan, tetapi gagal disalin ke Traffic Analytic: '
                .($sync['message'] ?? 'periksa migrasi id_trafficdalkot.'));
        }

        return $redirect;
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
        $validated = $request->validate($rules);

        $data = $this->prepareDataForUpdate($validated, $request);

        if ($request->hasFile('fotosit_file')) {
            $path = $request->file('fotosit_file')->store('trafficdalkot_situasi', 'public');
            $data['fotosit'] = mb_substr('/storage/'.$path, 0, 255);
        } else {
            $data['fotosit'] = $row->fotosit;
        }

        DB::table($this->tableName)->where($this->primaryKey, $id)->update($data);
        $sync = TrafficDalkotAnalyticSync::upsertFromDalkot((int) $id, array_merge((array) $row, $data));

        $redirect = redirect()->route('report.traffic-analytic.index')
            ->with('success', 'Data Traffic Dalkot berhasil diupdate.');

        if (! ($sync['ok'] ?? false)) {
            $redirect->with('warning', 'Data Dalkot diupdate, tetapi sinkron Traffic Analytic gagal.');
        }

        return $redirect;
    }

    public function destroy(string|int $id)
    {
        $existing = DB::table($this->tableName)->where($this->primaryKey, $id)->first();
        if (! $existing) {
            abort(404, 'Data tidak ditemukan');
        }

        $this->assertOperatorOwnsOperationalRow($existing);

        TrafficDalkotAnalyticSync::deleteLinked((int) $id);
        DB::table($this->tableName)->where($this->primaryKey, $id)->delete();

        return redirect()->route('report.traffic-analytic.index')
            ->with('success', 'Data Traffic Dalkot berhasil dihapus.');
    }

    public function export(Request $request, $format = 'excel')
    {
        $query = DB::table($this->tableName);
        $query = $this->applyFilters($query, $request);
        HierarchicalHelper::applyTerritoryScopeForTable($query, $this->tableName);
        $this->applyReportFilters($query, $request);

        if ($request->filled('id')) {
            $query->where('trafficdalkot.'.$this->primaryKey, $request->id);
        }

        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(function ($q) use ($s) {
                foreach ($this->searchFields as $field) {
                    $q->orWhere('trafficdalkot.'.$field, 'like', "%{$s}%");
                }
                $q->orWhere('usm.nama', 'like', "%{$s}%");
            });
        }

        $data = $query->get();
        $isSingle = $request->filled('id') && $data->count() === 1;
        $filename = $request->get('filename', $isSingle
            ? 'traffic_analytic_'.$request->id.'_'.date('Y-m-d')
            : 'traffic_analytic_'.date('Y-m-d'));
        $title = $isSingle ? 'Detail Report Traffic Analytic' : 'Report Traffic Analytic';

        return match (strtolower($format)) {
            'pdf' => $this->exportToPdf($data, 'exports.pdf.default', $filename, $title),
            'csv' => $this->exportToCsv($data, $filename),
            default => $this->exportToExcel($data, $filename, $title),
        };
    }

    protected function insertTrafficdalkotRow(array $data): int
    {
        try {
            return (int) DB::table($this->tableName)->insertGetId($data, $this->primaryKey);
        } catch (QueryException $e) {
            if (! str_contains($e->getMessage(), "doesn't have a default value")) {
                throw $e;
            }
        }

        $nextId = (int) DB::table($this->tableName)->max($this->primaryKey) + 1;
        $data[$this->primaryKey] = $nextId;
        DB::table($this->tableName)->insert($data);

        return $nextId;
    }

    protected function getValidationRules($action = 'store')
    {
        return [
            'tanggal' => 'required|date',
            'waktu' => 'nullable|string|max:20',
            'gerbangkota' => 'required|string|max:100',
            'araharus' => 'required|string|max:100',
            'jenisjalan' => 'required|string|max:100',
            'namajalan' => 'required|string|max:100',
            'r2' => 'required|integer|min:0',
            'r4' => 'required|integer|min:0',
            'r6' => 'required|integer|min:0',
            'r6_plus' => 'nullable|integer|min:0',
            'ketcb' => 'nullable|string|max:255',
            'lat' => ['nullable', 'numeric', 'between:-90,90'],
            'lng' => ['nullable', 'numeric', 'between:-180,180'],
        ];
    }

    protected function prepareDataForStore(array $validated, Request $request)
    {
        $data = [];
        foreach ($this->fillable as $f) {
            if (array_key_exists($f, $validated)) {
                $data[$f] = $validated[$f];
            }
        }

        $data['piket'] = $data['piket'] ?? Auth::id();
        $data['waktu'] = $this->normalizeWaktu($data['waktu'] ?? null);
        $data['r2'] = (int) ($data['r2'] ?? 0);
        $data['r4'] = (int) ($data['r4'] ?? 0);
        $data['r6'] = (int) ($data['r6'] ?? 0);
        $data['r6_plus'] = (int) ($data['r6_plus'] ?? 0);
        $data['jumlah'] = $data['r2'] + $data['r4'] + $data['r6'] + $data['r6_plus'];
        $data['fotosit'] = $data['fotosit'] ?? '';
        $data['ketcb'] = $data['ketcb'] ?? '';

        if (! Schema::hasColumn($this->tableName, 'r6_plus')) {
            unset($data['r6_plus']);
        }

        if (Schema::hasColumn($this->tableName, 'koordinat')) {
            $data = array_merge($data, $this->koordinatFromRequest($request));
        }

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

        if (isset($data['waktu'])) {
            $data['waktu'] = $this->normalizeWaktu($data['waktu']);
        }

        if (isset($data['r2']) || isset($data['r4']) || isset($data['r6']) || isset($data['r6_plus'])) {
            $r2 = (int) ($data['r2'] ?? $request->input('r2', 0));
            $r4 = (int) ($data['r4'] ?? $request->input('r4', 0));
            $r6 = (int) ($data['r6'] ?? $request->input('r6', 0));
            $r6Plus = (int) ($data['r6_plus'] ?? $request->input('r6_plus', 0));
            $data['jumlah'] = $r2 + $r4 + $r6 + $r6Plus;
        }

        if (! Schema::hasColumn($this->tableName, 'r6_plus')) {
            unset($data['r6_plus']);
        }

        if (Schema::hasColumn($this->tableName, 'koordinat')) {
            foreach ($this->koordinatFromRequest($request) as $col => $val) {
                $data[$col] = $val;
            }
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

    private function normalizeWaktu(?string $waktu): string
    {
        if ($waktu === null || $waktu === '') {
            return now()->format('H:i:s');
        }

        if (preg_match('/^\d{1,2}:\d{2}$/', $waktu)) {
            return $waktu.':00';
        }

        return $waktu;
    }

    /**
     * @return \Illuminate\Support\Collection<int, string>
     */
    protected function jenisJalanOptions()
    {
        if (Schema::hasTable('t_jenisjalan')) {
            return DB::table('t_jenisjalan')->orderBy('namajenis')->pluck('namajenis');
        }

        return collect(['TOL', 'Arteri', 'Nasional', 'Provinsi']);
    }

    /**
     * @return list<string>
     */
    protected function gerbangKotaOptions(): array
    {
        return ReferensiOptions::list(
            'gerbangkota',
            ['UTARA', 'SELATAN', 'TIMUR', 'BARAT'],
            $this->tableName,
            'gerbangkota'
        );
    }

    /**
     * @return list<string>
     */
    protected function arahArusOptions(): array
    {
        return ReferensiOptions::list(
            'araharus',
            ['Masuk', 'Keluar'],
            $this->tableName,
            'araharus'
        );
    }

    public static function fotoPublicUrl(?string $path): ?string
    {
        if ($path === null || trim($path) === '') {
            return null;
        }

        $first = trim(explode(',', $path, 2)[0]);
        if ($first === '') {
            return null;
        }

        if (preg_match('#^https?://#i', $first)) {
            return $first;
        }

        if (str_starts_with($first, '/storage/') || str_starts_with($first, 'storage/')) {
            return asset(ltrim($first, '/'));
        }

        /** @var \Illuminate\Filesystem\FilesystemAdapter $disk */
        $disk = \Illuminate\Support\Facades\Storage::disk('public');

        return $disk->url(ltrim($first, '/'));
    }

    protected function getPageTitle()
    {
        return 'Traffic Dalkot';
    }

    protected function getRoutePrefix()
    {
        return 'traffics';
    }
}
