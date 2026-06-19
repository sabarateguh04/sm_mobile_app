<?php

namespace App\Http\Controllers;

use App\Helpers\HierarchicalHelper;
use App\Support\PatrolCctvOpsdalSync;
use App\Support\ReferensiOptions;
use App\Support\TerritoryIndexFilters;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\View;

/**
 * Monitoring CCTV / Patroli — tabel `patrolicctv` (laporan patrol: situasi lalin, cuaca, peristiwa).
 */
class CctvController extends LegacySboOperasionalController
{
    protected $viewFolder = 'cctv';

    protected $tableName = 'patrolicctv';

    protected $fillable = [
        'piket', 'tanggal', 'lokasi', 'situasilalin', 'situasicuaca', 'peristiwa', 'ketcb', 'fotosit',
    ];

    protected $searchFields = ['lokasi', 'situasilalin', 'situasicuaca', 'peristiwa', 'ketcb', 'koordinat'];

    protected $defaultOrderBy = 'tanggal';

    protected $defaultOrderType = 'desc';

    public function __construct()
    {
        $this->tableName = self::resolveCctvTableName();
        $this->primaryKey = self::resolveCctvPrimaryKey($this->tableName);
        parent::__construct();
    }

    public static function resolveCctvTableName(): string
    {
        if (Schema::hasTable('patrolicctv')) {
            return 'patrolicctv';
        }

        return 'datacctv';
    }

    public static function resolveCctvPrimaryKey(string $table): string
    {
        if (Schema::hasColumn($table, 'id_patrolicctv')) {
            return 'id_patrolicctv';
        }

        return 'id_datacctv';
    }

    public static function usesPatrolSchema(): bool
    {
        $table = self::resolveCctvTableName();
        $cols = self::resolvePatrolColumns($table);

        return $cols['situasilalin'] !== null
            || $cols['peristiwa'] !== null
            || $cols['ketcb'] !== null;
    }

    /**
     * Pemetaan field form patrol → nama kolom aktual di DB (production bisa beda penamaan).
     *
     * @return array<string, string|null>
     */
    public static function resolvePatrolColumns(?string $table = null): array
    {
        $table = $table ?? self::resolveCctvTableName();
        $pick = static function (string ...$names) use ($table): ?string {
            foreach ($names as $name) {
                if (Schema::hasColumn($table, $name)) {
                    return $name;
                }
            }

            return null;
        };

        return [
            'situasilalin' => $pick('situasilalin', 'situasi_lalin', 'statarus'),
            'situasicuaca' => $pick('situasicuaca', 'situasi_cuaca', 'cuaca'),
            'peristiwa' => $pick('peristiwa'),
            'ketcb' => $pick('ketcb', 'ket_cb'),
            'foto' => $pick('fotosit', 'fotofisik', 'capture', 'foto'),
            'lokasi' => $pick('lokasi'),
            'tanggal' => $pick('tanggal'),
            'owner' => $pick('owner'),
        ];
    }

    /**
     * @param  object|array<string, mixed>  $row
     */
    public static function patrolValue(object $row, string $logical, ?array $cols = null): ?string
    {
        $cols = $cols ?? self::resolvePatrolColumns();
        $col = $cols[$logical] ?? null;
        if ($col === null) {
            return null;
        }

        return isset($row->{$col}) ? (string) $row->{$col} : null;
    }

    /**
     * URL tampilan untuk foto patrol (`fotosit` atau legacy `fotofisik`).
     */
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

        return asset(ltrim($first, '/'));
    }

    /**
     * @param \Illuminate\Database\Query\Builder $query
     * @return \Illuminate\Database\Query\Builder
     */
    protected function applyFilters($query, Request $request)
    {
        return $query
            ->leftJoin('usm', $this->tableName.'.piket', '=', 'usm.id_usm')
            ->select($this->tableName.'.*', 'usm.nama as petugas_nama');
    }

    public function index(Request $request)
    {
        $patrolCols = self::resolvePatrolColumns($this->tableName);

        $query = DB::table($this->tableName);
        $query = $this->applyFilters($query, $request);
        HierarchicalHelper::applyTerritoryScopeForTable($query, $this->tableName);
        TerritoryIndexFilters::applyUsmFilters($query, $request);

        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(function ($q) use ($s, $patrolCols) {
                foreach (array_filter(array_unique(array_merge(
                    $this->searchFields,
                    array_values($patrolCols)
                ))) as $field) {
                    $q->orWhere($this->tableName.'.'.$field, 'like', "%{$s}%");
                }
                $q->orWhere('usm.nama', 'like', "%{$s}%");
            });
        }

        if ($request->filled('tanggal_dari') && $patrolCols['tanggal']) {
            $query->where($this->tableName.'.'.$patrolCols['tanggal'], '>=', $request->tanggal_dari);
        }
        if ($request->filled('tanggal_sampai') && $patrolCols['tanggal']) {
            $query->where($this->tableName.'.'.$patrolCols['tanggal'], '<=', $request->tanggal_sampai);
        }
        if ($request->filled('peristiwa') && $patrolCols['peristiwa']) {
            $query->where($this->tableName.'.'.$patrolCols['peristiwa'], $request->peristiwa);
        }

        $tanggalCol = $patrolCols['tanggal'] ?? $this->defaultOrderBy;
        $query->orderBy($this->tableName.'.'.$tanggalCol, $request->get('ordertype', $this->defaultOrderType));
        $query->orderBy($this->tableName.'.'.$this->primaryKey, 'desc');

        $data = $query->paginate($request->get('per_page', 20))
            ->appends($this->preserveIndexQuery($request));

        return view($this->viewFolder.'.index', array_merge([
            'pageTitle' => $this->getPageTitle(),
            'data' => $data,
            'primaryKey' => $this->primaryKey,
            'patrolCols' => $patrolCols,
            'peristiwaList' => $this->peristiwaOptions(),
        ], TerritoryIndexFilters::viewBundle($request)));
    }

    /**
     * @return array<string, mixed>
     */
    protected function preserveIndexQuery(Request $request): array
    {
        return array_filter(
            $request->only(['search', 'tanggal_dari', 'tanggal_sampai', 'peristiwa', 'daerah', 'wilayah']),
            fn ($v) => $v !== '' && $v !== null
        );
    }

    public function create()
    {
        return view($this->viewFolder.'.create', $this->formViewData('Tambah '.$this->getPageTitle()));
    }

    public function show(string|int $id)
    {
        $data = DB::table($this->tableName)
            ->leftJoin('usm', $this->tableName.'.piket', '=', 'usm.id_usm')
            ->select(
                $this->tableName.'.*',
                'usm.nama as petugas_nama',
                'usm.daerah as daerah_nama',
                'usm.wilayah as wilayah_nama',
            )
            ->where($this->tableName.'.'.$this->primaryKey, $id)->first();
        if (! $data) {
            abort(404);
        }

        return view($this->viewFolder.'.show', [
            'pageTitle' => 'Detail '.$this->getPageTitle(),
            'data' => $data,
            'primaryKey' => $this->primaryKey,
            'patrolCols' => self::resolvePatrolColumns($this->tableName),
        ]);
    }

    public function edit(string|int $id)
    {
        $data = DB::table($this->tableName)->where($this->primaryKey, $id)->first();
        if (! $data) {
            abort(404);
        }

        $this->assertOperatorOwnsOperationalRow($data);

        return view($this->viewFolder.'.edit', $this->formViewData('Edit '.$this->getPageTitle(), $data));
    }

    public function store(Request $request)
    {
        $rules = $this->getValidationRules('store');
        $this->applyFotoGpsValidationRules($rules, $request);
        $validated = $request->validate($rules, $this->fotoGpsValidationMessages());
        $data = $this->prepareDataForStore($validated, $request);

        $patrolId = $this->insertPatrolCctvRow($data);
        $sync = PatrolCctvOpsdalSync::syncFromPatrol($patrolId, $data, $this->tableName);

        $redirect = redirect()->route($this->getRoutePrefix().'.index')
            ->with('success', 'Data berhasil ditambahkan.');

        if (! $sync['ok']) {
            $redirect->with('warning', 'Laporan patroli tersimpan, tetapi gagal disalin ke modul Opsdal: '
                .($sync['message'] ?? 'periksa migrasi id_patrolicctv / sb_grp.'));
        } elseif (($sync['target'] ?? '') === 'contigency') {
            $redirect->with('info', 'Laporan disalin ke Kodal Kontijensi (Opsdal).');
        } elseif (($sync['target'] ?? '') === 'trafficstuck') {
            $redirect->with('info', 'Laporan disalin ke Kodal Perlambatan (Opsdal).');
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
        $this->applyFotoGpsValidationRules($rules, $request);
        $validated = $request->validate($rules, $this->fotoGpsValidationMessages());
        $data = $this->prepareDataForUpdate($validated, $request);

        DB::table($this->tableName)->where($this->primaryKey, $id)->update($data);
        $sync = PatrolCctvOpsdalSync::syncFromPatrol((int) $id, $data, $this->tableName);

        $redirect = redirect()->route($this->getRoutePrefix().'.index')
            ->with('success', 'Data berhasil diupdate.');

        if (! $sync['ok']) {
            $redirect->with('warning', 'Patroli diupdate, tetapi sinkron modul Opsdal gagal: '
                .($sync['message'] ?? 'cek log aplikasi.'));
        }

        return $redirect;
    }

    public function destroy(string|int $id)
    {
        $row = DB::table($this->tableName)->where($this->primaryKey, $id)->first();
        if ($row) {
            $foto = $row->fotosit ?? $row->fotofisik ?? null;
            if (! empty($foto)) {
                $this->deleteStoredFoto($foto);
            }
        }

        PatrolCctvOpsdalSync::deleteAllLinked((int) $id);

        return parent::destroy($id);
    }

    protected function getValidationRules($action = 'store')
    {
        return [
            'tanggal' => 'required|date',
            'lokasi' => 'nullable|string|max:100',
            'situasilalin' => 'nullable|string|max:100',
            'situasicuaca' => 'nullable|string|max:100',
            'peristiwa' => 'nullable|string|max:100',
            'owner' => 'nullable|string|max:100',
            'ketcb' => 'nullable|string|max:2000',
            'lat' => ['nullable', 'numeric', 'between:-90,90'],
            'lng' => ['nullable', 'numeric', 'between:-180,180'],
            'capture_file' => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif,webp', 'max:8192'],
        ];
    }

    /**
     * Capture wajib disertai koordinat GPS (watermark / field lat-lng).
     *
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
            'lat.required' => 'Koordinat GPS wajib saat mengunggah capture. Izinkan akses lokasi atau klik "Ambil lokasi GPS".',
            'lng.required' => 'Koordinat GPS wajib saat mengunggah capture.',
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

    protected function prepareDataForStore(array $validated, Request $request)
    {
        return $this->mergePatrolPayload($request, $validated, null, null);
    }

    protected function prepareDataForUpdate(array $validated, Request $request)
    {
        $id = $request->route('id');
        $row = $id ? DB::table($this->tableName)->where($this->primaryKey, $id)->first() : null;
        $existingFoto = $row
            ? ($row->fotosit ?? $row->fotofisik ?? null)
            : null;
        $existingKoordinat = $row->koordinat ?? null;

        return $this->mergePatrolPayload($request, $validated, $existingFoto, $existingKoordinat);
    }

    /**
     * @return array<string, mixed>
     */
    private function mergePatrolPayload(Request $request, array $validated, ?string $existingFoto, ?string $existingKoordinat): array
    {
        $data = ['piket' => (string) (Auth::id() ?? '')];
        $patrolCols = self::resolvePatrolColumns($this->tableName);

        $logicalFields = [
            'tanggal' => 'tanggal',
            'lokasi' => 'lokasi',
            'situasilalin' => 'situasilalin',
            'situasicuaca' => 'situasicuaca',
            'peristiwa' => 'peristiwa',
            'owner' => 'owner',
            'ketcb' => 'ketcb',
        ];

        foreach ($logicalFields as $logical => $requestKey) {
            $dbCol = $patrolCols[$logical] ?? null;
            if ($dbCol && array_key_exists($requestKey, $validated)) {
                $data[$dbCol] = $validated[$requestKey];
            }
        }

        $fotoCol = $patrolCols['foto'] ?? 'fotosit';
        $foto = $existingFoto ?? '';

        if ($request->hasFile('capture_file')) {
            if ($existingFoto) {
                $this->deleteStoredFoto($existingFoto);
            }
            $stored = $request->file('capture_file')->store('patrol_cctv_capture', 'public');
            $foto = '/storage/'.$stored;
        }

        $data[$fotoCol] = mb_substr((string) $foto, 0, 255);

        if (Schema::hasColumn($this->tableName, 'koordinat')) {
            $coord = $this->koordinatFromRequest($request);
            if ($coord !== []) {
                $data['koordinat'] = $coord['koordinat'];
            } elseif ($existingKoordinat !== null && $existingKoordinat !== '') {
                $data['koordinat'] = $existingKoordinat;
            }
        }

        // Kolom legacy inventaris CCTV (dari rename datacctv).
        if (($patrolCols['owner'] ?? null) && array_key_exists('owner', $validated)) {
            $ownerVal = trim((string) $validated['owner']);
            $data[$patrolCols['owner']] = $ownerVal !== '' ? $ownerVal : '-';
        }
        foreach (['ipaddres' => '-', 'brand' => '-', 'fungsicctv' => null] as $legacy => $default) {
            if (Schema::hasColumn($this->tableName, $legacy) && ! array_key_exists($legacy, $data)) {
                $data[$legacy] = $default;
            }
        }
        if (Schema::hasColumn($this->tableName, 'owner') && ! array_key_exists('owner', $data)) {
            $data['owner'] = '-';
        }
        if (Schema::hasColumn($this->tableName, 'fotofisik') && ! array_key_exists('fotofisik', $data)) {
            $data['fotofisik'] = $data[$fotoCol] ?? '';
        }

        return $data;
    }

    protected function insertPatrolCctvRow(array $data): int
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

    private function deleteStoredFoto(?string $paths): void
    {
        if (empty($paths)) {
            return;
        }
        foreach (explode(',', $paths) as $raw) {
            $p = trim($raw);
            if ($p === '' || preg_match('#^https?://#i', $p)) {
                continue;
            }
            $p = preg_replace('#^/storage/#', '', $p);
            Storage::disk('public')->delete($p);
        }
    }

    public function export(Request $request, $format = 'excel')
    {
        $query = DB::table($this->tableName.' as t')
            ->leftJoin('usm', 'usm.id_usm', '=', 't.piket')
            ->select('t.*', 'usm.nama as petugas_nama');

        HierarchicalHelper::applyTerritoryScopeForTable($query, $this->tableName, 't');

        $patrolCols = self::resolvePatrolColumns($this->tableName);
        if ($request->filled('tanggal_dari') && $patrolCols['tanggal']) {
            $query->where('t.'.$patrolCols['tanggal'], '>=', $request->tanggal_dari);
        }
        if ($request->filled('tanggal_sampai') && $patrolCols['tanggal']) {
            $query->where('t.'.$patrolCols['tanggal'], '<=', $request->tanggal_sampai);
        }
        if ($request->filled('peristiwa') && $patrolCols['peristiwa']) {
            $query->where('t.'.$patrolCols['peristiwa'], $request->peristiwa);
        }

        TerritoryIndexFilters::applyUsmFilters($query, $request);

        if ($request->filled('id')) {
            $query->where('t.'.$this->primaryKey, $request->id);
        }

        $data = $query->orderByDesc('t.tanggal')->orderByDesc('t.'.$this->primaryKey)->get();
        $isSingle = $request->filled('id') && $data->count() === 1;
        $filename = $request->get('filename', $isSingle
            ? $this->tableName.'_'.$request->id.'_'.date('Y-m-d')
            : $this->tableName.'_'.date('Y-m-d'));
        $title = $request->get('title', $isSingle ? 'Detail '.$this->getPageTitle() : $this->getPageTitle().' Report');

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

    protected function getPageTitle()
    {
        return 'Monitoring CCTV (Patroli)';
    }

    protected function getRoutePrefix(): string
    {
        $name = request()->route()?->getName() ?? '';

        return str_starts_with($name, 'cctv.') ? 'cctv' : 'patrolicctv';
    }

    /**
     * @return array<string, mixed>
     */
    private function formViewData(string $pageTitle, ?object $data = null): array
    {
        return [
            'pageTitle' => $pageTitle,
            'data' => $data,
            'tableName' => $this->tableName,
            'primaryKey' => $this->primaryKey,
            'patrolCols' => $cols = self::resolvePatrolColumns($this->tableName),
            'situasiLalinList' => $this->situasiLalinOptions($cols),
            'situasiCuacaList' => $this->situasiCuacaOptions($cols),
            'peristiwaList' => $this->peristiwaOptions(),
            'ownerList' => ReferensiOptions::list('ownercctv', ['Korlantas', 'Polda', 'Polres', 'Instansi Lain']),
        ];
    }

    /**
     * @param  array<string, string|null>|null  $patrolCols
     * @return array<int, string>
     */
    private function situasiLalinOptions(?array $patrolCols = null): array
    {
        $patrolCols = $patrolCols ?? self::resolvePatrolColumns($this->tableName);
        $col = $patrolCols['situasilalin'] ?? null;

        return ReferensiOptions::list(
            'situasilalin',
            ['Lancar', 'Padat', 'Macet', 'Terhenti', 'Mudik', 'Arus Balik'],
            $col ? $this->tableName : null,
            $col
        );
    }

    /**
     * @param  array<string, string|null>|null  $patrolCols
     * @return array<int, string>
     */
    private function situasiCuacaOptions(?array $patrolCols = null): array
    {
        $patrolCols = $patrolCols ?? self::resolvePatrolColumns($this->tableName);
        $col = $patrolCols['situasicuaca'] ?? null;

        return ReferensiOptions::list(
            'situasicuaca',
            ['Cerah', 'Berawan', 'Hujan Ringan', 'Hujan Lebat', 'Badai', 'Kabut'],
            $col ? $this->tableName : null,
            $col
        );
    }

    /**
     * @return array<int, string>
     */
    private function peristiwaOptions(): array
    {
        return ReferensiOptions::list('peristiwa');
    }
}
