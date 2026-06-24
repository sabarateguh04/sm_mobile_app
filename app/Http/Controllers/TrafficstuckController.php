<?php

namespace App\Http\Controllers;

use App\Helpers\HierarchicalHelper;
use App\Support\ReferensiOptions;
use App\Support\RoleMutationPolicy;
use App\Support\TerritoryIndexFilters;
use Illuminate\Database\Query\Builder;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Session;

class TrafficstuckController extends OperasionalController
{
    protected $tableName = 'trafficstuck';

    protected $primaryKey = 'id_trafficstuck';

    protected $viewFolder = 'trafficstuck';

    protected $fillable = [
        'tanggal', 'piket', 'lokasi', 'koordinat', 'faktor', 'penyebab',
        'durasi', 'timurai', 'fotosituasi', 'ketcb', 'jamperistiwa',
    ];

    protected $searchFields = ['lokasi', 'faktor', 'penyebab', 'durasi', 'timurai', 'ketcb'];

    protected $defaultOrderBy = 'tanggal';

    protected $defaultOrderType = 'desc';

    public function __construct()
    {
        parent::__construct();
        $this->middleware(function (Request $request, $next) {
            if (! $request->routeIs('trafficstuck.*')) {
                return $next($request);
            }

            $sbGrp = (string) ($request->query('sb_grp') ?: $request->input('sb_grp') ?: Session::get('trafficstuck_sb_grp', ''));

            // Legacy SBO: Traffic Dalkot (giat rutin) pindah ke /traffics + tabel trafficdalkot.
            if ($sbGrp === 'giat_rutin') {
                $query = $request->except(['sb_grp']);
                $target = route('traffics.index', $query);

                return redirect()->to($target, 301);
            }

            // Patrol Online: input ke tabel trafficdalkot (/traffics), tampilan laporan di trafficstuck.
            if ($sbGrp === 'patrol_online') {
                if ($request->routeIs('trafficstuck.create')) {
                    return redirect()->route('traffics.create');
                }

                if ($request->routeIs('trafficstuck.edit')) {
                    $id = $request->route('trafficstuck');
                    if ($id !== null && Schema::hasColumn($this->tableName, 'id_trafficdalkot')) {
                        $dalkotId = DB::table($this->tableName)
                            ->where($this->primaryKey, $id)
                            ->value('id_trafficdalkot');
                        if ($dalkotId) {
                            return redirect()->route('traffics.edit', ['id' => $dalkotId]);
                        }
                    }
                }
            }

            if ($request->query('sb_grp') || $request->input('sb_grp')) {
                session(['trafficstuck_sb_grp' => (string) ($request->query('sb_grp') ?: $request->input('sb_grp'))]);
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
        if ($grp === [] && Session::has('trafficstuck_sb_grp')) {
            $grp = ['sb_grp' => Session::get('trafficstuck_sb_grp')];
        }

        return $grp;
    }

    protected function currentSbGrp(Request $request): string
    {
        return (string) ($request->query('sb_grp') ?: $request->input('sb_grp') ?: Session::get('trafficstuck_sb_grp', ''));
    }

    protected function reportPageTitle(Request $request): string
    {
        return match ($this->currentSbGrp($request)) {
            'quick_respons' => 'Report Kodal : QR Perlambatan',
            'patrol_online' => 'Report Traffic Analytic',
            'opsdal' => 'Report Kodal Perlambatan',
            default => 'Report Kodal : QR Perlambatan',
        };
    }

    protected function detailPageTitle(Request $request): string
    {
        return $this->currentSbGrp($request) === 'quick_respons'
            ? 'Detail QR Perlambatan'
            : 'Detail Kodal Perlambatan';
    }

    protected function formPageTitle(string $action = 'create'): string
    {
        $isQr = $this->currentSbGrp(app(Request::class)) === 'quick_respons';

        return match ($action) {
            'create' => $isQr ? 'Tambah QR Perlambatan' : 'Tambah Kodal Perlambatan',
            'edit' => $isQr ? 'Edit QR Perlambatan' : 'Edit Kodal Perlambatan',
            default => $isQr ? 'QR Perlambatan' : 'Kodal Perlambatan',
        };
    }

    protected function applyFilters($query, Request $request)
    {
        $query->leftJoin('usm', 'trafficstuck.piket', '=', 'usm.id_usm');

        $select = [
            'trafficstuck.*',
            'usm.nama as usm_nama',
            DB::raw('usm.daerah as daerah_nama'),
            DB::raw('usm.wilayah as wilayah_nama'),
        ];

        if (Schema::hasColumn($this->tableName, 'operator_edited_by')) {
            $query->leftJoin('usm as editor_usm', 'trafficstuck.operator_edited_by', '=', 'editor_usm.id_usm');
            $select[] = 'editor_usm.nama as operator_editor_nama';
        }

        $query->select($select);

        return $query;
    }

    protected function applyReportFilters(Builder $query, Request $request): void
    {
        if ($request->filled('tanggal_dari')) {
            $query->where('trafficstuck.tanggal', '>=', $request->tanggal_dari);
        }
        if ($request->filled('tanggal_sampai')) {
            $query->where('trafficstuck.tanggal', '<=', $request->tanggal_sampai);
        }
        if ($request->filled('faktor')) {
            $query->where('trafficstuck.faktor', $request->faktor);
        }
        if ($request->filled('status_konfirmasi_opsdal')) {
            $query->where('trafficstuck.status_konfirmasi_opsdal', $request->status_konfirmasi_opsdal);
        }
        if ($request->filled('lokasi')) {
            $query->where('trafficstuck.lokasi', $request->lokasi);
        }

        TerritoryIndexFilters::applyUsmFilters($query, $request);
        $this->applySbGrpScope($query, $request);
    }

    /**
     * Pisahkan daftar per menu sidebar (Opsdal / Patroli / QR).
     */
    protected function applySbGrpScope(Builder $query, Request $request): void
    {
        $grp = $this->currentSbGrp($request);
        if ($grp === '' || $grp === 'giat_rutin') {
            return;
        }

        $table = $this->tableName;
        $hasPatrolLink = Schema::hasColumn($table, 'id_patrolicctv');
        $hasDalkotLink = Schema::hasColumn($table, 'id_trafficdalkot');
        $hasSbGrp = Schema::hasColumn($table, 'sb_grp');

        if (! $hasPatrolLink && ! $hasDalkotLink && ! $hasSbGrp) {
            return;
        }

        if ($grp === 'opsdal') {
            $query->where(function ($q) use ($table, $hasPatrolLink, $hasSbGrp) {
                if ($hasPatrolLink) {
                    $q->whereNotNull($table.'.id_patrolicctv');
                }
                if ($hasSbGrp) {
                    $hasPatrolLink ? $q->orWhere($table.'.sb_grp', 'opsdal') : $q->where($table.'.sb_grp', 'opsdal');
                }
            });

            return;
        }

        if ($grp === 'patrol_online') {
            $query->where(function ($q) use ($table, $hasPatrolLink, $hasDalkotLink, $hasSbGrp) {
                $started = false;
                if ($hasPatrolLink) {
                    $q->whereNotNull($table.'.id_patrolicctv');
                    $started = true;
                }
                if ($hasDalkotLink) {
                    $started ? $q->orWhereNotNull($table.'.id_trafficdalkot') : $q->whereNotNull($table.'.id_trafficdalkot');
                    $started = true;
                }
                if ($hasSbGrp) {
                    $started ? $q->orWhere($table.'.sb_grp', 'patrol_online') : $q->where($table.'.sb_grp', 'patrol_online');
                }
            });

            return;
        }

        if ($grp === 'quick_respons') {
            $query->where(function ($q) use ($table, $hasPatrolLink, $hasDalkotLink, $hasSbGrp) {
                if ($hasPatrolLink) {
                    $q->whereNull($table.'.id_patrolicctv');
                }
                if ($hasDalkotLink) {
                    $hasPatrolLink
                        ? $q->whereNull($table.'.id_trafficdalkot')
                        : $q->whereNull($table.'.id_trafficdalkot');
                }
                if ($hasSbGrp) {
                    $method = ($hasPatrolLink || $hasDalkotLink) ? 'where' : 'where';
                    $q->{$method}(function ($inner) use ($table) {
                        $inner->whereNull($table.'.sb_grp')
                            ->orWhere($table.'.sb_grp', '')
                            ->orWhere($table.'.sb_grp', 'quick_respons');
                    });
                }
            });
        }
    }

    /**
     * Report Traffic Analytic — daftar trafficdalkot (bukan trafficstuck / kemacetan).
     */
    public function reportTrafficAnalytic(Request $request)
    {
        abort_unless(Schema::hasTable('trafficdalkot'), 503, 'Tabel trafficdalkot belum tersedia.');

        session(['trafficstuck_sb_grp' => 'patrol_online']);
        $request->merge(['sb_grp' => 'patrol_online']);

        return $this->indexTrafficDalkot($request, 'report.traffic-analytic.index');
    }

    public function index(Request $request)
    {
        if ($this->currentSbGrp($request) === 'patrol_online') {
            return redirect()->route('report.traffic-analytic.index', $request->query());
        }

        if (! $request->filled('sb_grp') && ! Session::has('trafficstuck_sb_grp')) {
            return redirect()->route('trafficstuck.index', ['sb_grp' => 'quick_respons']);
        }

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
                    $q->orWhere('trafficstuck.'.$field, 'like', "%{$s}%");
                }
                $q->orWhere('usm.nama', 'like', "%{$s}%")
                    ->orWhere('usm.daerah', 'like', "%{$s}%")
                    ->orWhere('usm.wilayah', 'like', "%{$s}%");
            });
        }

        $query->orderBy(
            'trafficstuck.'.$request->get('orderby', $this->defaultOrderBy),
            $request->get('ordertype', $this->defaultOrderType)
        );
        if (Schema::hasColumn($this->tableName, 'status_konfirmasi_opsdal')) {
            $query->orderByRaw("CASE WHEN trafficstuck.status_konfirmasi_opsdal = 'menunggu' THEN 0 ELSE 1 END");
        }

        $data = $query->paginate($request->get('per_page', $this->perPage))->withQueryString();

        $tabulasi = collect();
        if ($tab === 'tabulasi') {
            $tabQ = DB::table($this->tableName)
                ->leftJoin('usm', 'trafficstuck.piket', '=', 'usm.id_usm');
            HierarchicalHelper::applyTerritoryScopeForTable($tabQ, $this->tableName);
            $this->applyReportFilters($tabQ, $request);

            $tabulasi = $tabQ
                ->selectRaw('COALESCE(NULLIF(usm.daerah, ""), "-") as daerah_nama')
                ->selectRaw('COALESCE(NULLIF(trafficstuck.faktor, ""), "-") as faktor_nama')
                ->selectRaw('COUNT(*) as jumlah')
                ->groupBy('daerah_nama', 'faktor_nama')
                ->orderBy('daerah_nama')
                ->orderBy('faktor_nama')
                ->get();
        }

        $lokasiList = DB::table($this->tableName)
            ->whereNotNull('lokasi')
            ->where('lokasi', '!=', '')
            ->distinct()
            ->orderBy('lokasi')
            ->pluck('lokasi');

        return view($this->viewFolder.'.index', array_merge([
            'pageTitle' => $this->reportPageTitle($request),
            'data' => $data,
            'tabulasi' => $tabulasi,
            'tab' => $tab,
            'faktorList' => $this->lookupList('t_faktor', 'namefaktor'),
            'lokasiList' => $lokasiList,
            'sbGrp' => $sbGrp,
            'tableName' => $this->tableName,
            'primaryKey' => $this->primaryKey,
            'sboViewOnly' => RoleMutationPolicy::deniesAllMutations(Auth::user()),
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

    /**
     * @return \Illuminate\Support\Collection<int, string>
     */
    /**
     * @return \Illuminate\Support\Collection<int, string>
     */
    protected function penyebabOptionsForFilter()
    {
        if (Schema::hasTable('t_penyebab')) {
            return DB::table('t_penyebab')->orderBy('namepenyebab')->pluck('namepenyebab');
        }

        return DB::table($this->tableName)->whereNotNull('penyebab')->where('penyebab', '!=', '')->distinct()->orderBy('penyebab')->pluck('penyebab');
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

    protected function durasiOptionsForFilter()
    {
        return ReferensiOptions::collection(
            'durasi',
            ['2-3 Jam', '4-5 Jam', '6-7 Jam', '8-9 Jam'],
            $this->tableName,
            'durasi'
        );
    }

    public function show(string|int $id)
    {
        $showQuery = DB::table($this->tableName)
            ->leftJoin('usm', 'trafficstuck.piket', '=', 'usm.id_usm');

        $showSelect = [
            'trafficstuck.*',
            'usm.nama as usm_nama',
            DB::raw('usm.daerah as daerah_nama'),
            DB::raw('usm.wilayah as wilayah_nama'),
        ];

        if (Schema::hasColumn($this->tableName, 'operator_edited_by')) {
            $showQuery->leftJoin('usm as editor_usm', 'trafficstuck.operator_edited_by', '=', 'editor_usm.id_usm');
            $showSelect[] = 'editor_usm.nama as operator_editor_nama';
        }

        $data = $showQuery->select($showSelect)
            ->where('trafficstuck.'.$this->primaryKey, $id)->first();

        if (! $data) {
            abort(404, 'Data tidak ditemukan');
        }

        $req = app(Request::class);

        return view($this->viewFolder.'.show', array_merge([
            'pageTitle' => $this->detailPageTitle($req),
            'data' => $data,
            'sbGrp' => $this->sbGrpFromRequest($req),
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
            ->leftJoin('usm', 'trafficstuck.piket', '=', 'usm.id_usm')
            ->select('trafficstuck.*', 'usm.daerah as daerah_nama', 'usm.wilayah as wilayah_nama')
            ->where('trafficstuck.'.$this->primaryKey, $id)
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
            ->leftJoin('usm', 'trafficstuck.piket', '=', 'usm.id_usm')
            ->select('trafficstuck.*', 'usm.daerah as daerah_nama', 'usm.wilayah as wilayah_nama')
            ->where('trafficstuck.'.$this->primaryKey, $id)
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

    public function create()
    {
        if ($this->currentSbGrp(app(Request::class)) === 'patrol_online') {
            return redirect()->route('traffics.create');
        }

        return view($this->viewFolder.'.create', [
            'pageTitle' => $this->formPageTitle('create'),
            'faktorList' => $this->lookupList('t_faktor', 'namefaktor'),
            'penyebabList' => $this->penyebabOptionsForForm(),
            'durasiList' => $this->durasiOptionsForFilter(),
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
            'faktorList' => $this->lookupList('t_faktor', 'namefaktor'),
            'penyebabList' => $this->penyebabOptionsForForm(),
            'durasiList' => $this->durasiOptionsForFilter(),
            'sbGrp' => $this->sbGrpFromRequest(app(Request::class)),
        ]);
    }

    protected function getValidationRules($action = 'store')
    {
        return [
            'tanggal'      => 'required|date',
            'lokasi'       => 'nullable|string|max:255',
            'faktor'       => 'nullable|string|max:100',
            'penyebab'     => 'nullable|string|max:100',
            'durasi'       => 'nullable|string|max:100',
            // timurai: array (multi-select, maks 5) atau string
            'timurai'      => 'nullable',
            'timurai.*'    => 'nullable|string|max:100',
            'ketcb'        => 'nullable|string',
            'jamperistiwa' => 'nullable|string|max:20',
            'lat'          => ['nullable', 'numeric', 'between:-90,90'],
            'lng'          => ['nullable', 'numeric', 'between:-180,180'],
        ];
    }

    /**
     * Foto situasi wajib disertai koordinat GPS (dari watermark / field lat-lng).
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
            'lat.required' => 'Koordinat GPS wajib saat mengunggah foto situasi. Izinkan akses lokasi atau klik "Ambil lokasi GPS".',
            'lng.required' => 'Koordinat GPS wajib saat mengunggah foto situasi.',
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
        if ($this->currentSbGrp($request) === 'patrol_online') {
            return redirect()->route('traffics.create')
                ->with('warning', 'Data Traffic Analytic (patrol online) diinput melalui form Traffic Dalkot — tabel trafficdalkot.');
        }

        $rules = $this->getValidationRules('store');
        $rules['fotosituasi_file'] = 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:8192';
        $this->applyFotoGpsValidationRules($rules, $request);
        $validated = $request->validate($rules, $this->fotoGpsValidationMessages());

        $data = $this->prepareDataForStore($validated, $request);

        if ($request->hasFile('fotosituasi_file')) {
            $path = $request->file('fotosituasi_file')->store('trafficstuck_situasi', 'public');
            $data['fotosituasi'] = mb_substr('/storage/'.$path, 0, 255);
        }

        $this->insertTrafficstuckRow($data);

        return redirect()->route($this->getRoutePrefix().'.index', $this->sbGrpFromRequest($request))
            ->with('success', 'Data berhasil ditambahkan.');
    }

    /**
     * Insert baris trafficstuck — kompatibel tabel legacy tanpa AUTO_INCREMENT.
     */
    protected function insertTrafficstuckRow(array $data): int
    {
        $this->normalizeLegacyTrafficstuckFields($data);

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

    public function update(Request $request, string|int $id)
    {
        $row = DB::table($this->tableName)->where($this->primaryKey, $id)->first();
        if (! $row) {
            abort(404, 'Data tidak ditemukan');
        }

        $this->assertOperatorOwnsOperationalRow($row);

        $rules = $this->getValidationRules('update');
        $rules['fotosituasi_file'] = 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:8192';
        $this->applyFotoGpsValidationRules($rules, $request);
        $validated = $request->validate($rules, $this->fotoGpsValidationMessages());

        $data = $this->prepareDataForUpdate($validated, $request);

        if ($request->hasFile('fotosituasi_file')) {
            $path = $request->file('fotosituasi_file')->store('trafficstuck_situasi', 'public');
            $data['fotosituasi'] = mb_substr('/storage/'.$path, 0, 255);
        } else {
            $data['fotosituasi'] = $row->fotosituasi;
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

    /**
     * Patrol Online / Traffic Analytic — daftar dari tabel trafficdalkot (bukan trafficstuck).
     */
    protected function indexTrafficDalkot(Request $request, string $indexRouteName = 'report.traffic-analytic.index')
    {
        session(['trafficstuck_sb_grp' => 'patrol_online']);
        $sbGrp = ['sb_grp' => 'patrol_online'];

        $query = DB::table('trafficdalkot')
            ->leftJoin('usm', 'trafficdalkot.piket', '=', 'usm.id_usm')
            ->select(
                'trafficdalkot.*',
                'usm.nama as usm_nama',
                DB::raw('usm.daerah as daerah_nama'),
                DB::raw('usm.wilayah as wilayah_nama')
            );

        HierarchicalHelper::applyTerritoryScopeForTable($query, 'trafficdalkot');

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

        if ($request->filled('search')) {
            $s = $request->search;
            $searchFields = ['gerbangkota', 'araharus', 'jenisjalan', 'namajalan', 'ketcb'];
            $query->where(function ($q) use ($s, $searchFields) {
                foreach ($searchFields as $field) {
                    $q->orWhere('trafficdalkot.'.$field, 'like', "%{$s}%");
                }
                $q->orWhere('usm.nama', 'like', "%{$s}%");
            });
        }

        $query->orderBy(
            'trafficdalkot.'.$request->get('orderby', 'tanggal'),
            $request->get('ordertype', 'desc')
        );

        $data = $query->paginate($request->get('per_page', $this->perPage))->withQueryString();

        $jenisJalanList = collect();
        if (Schema::hasTable('t_jenisjalan')) {
            $jenisJalanList = DB::table('t_jenisjalan')->orderBy('namajenis')->pluck('namajenis');
        } else {
            $jenisJalanList = DB::table('trafficdalkot')->whereNotNull('jenisjalan')->where('jenisjalan', '!=', '')
                ->distinct()->orderBy('jenisjalan')->pluck('jenisjalan');
        }

        $gerbangKotaList = \App\Support\ReferensiOptions::list(
            'gerbangkota',
            ['UTARA', 'SELATAN', 'TIMUR', 'BARAT'],
            'trafficdalkot',
            'gerbangkota'
        );

        $arahArusList = \App\Support\ReferensiOptions::list(
            'araharus',
            ['Masuk', 'Keluar'],
            'trafficdalkot',
            'araharus'
        );

        return view($this->viewFolder.'.index', array_merge([
            'pageTitle' => 'Report Traffic Analytic',
            'data' => $data,
            'tabulasi' => collect(),
            'tab' => 'rinci',
            'listSource' => 'trafficdalkot',
            'indexRouteName' => $indexRouteName,
            'jenisJalanList' => $jenisJalanList,
            'gerbangKotaList' => $gerbangKotaList,
            'arahArusList' => $arahArusList,
            'sbGrp' => $sbGrp,
            'tableName' => 'trafficdalkot',
            'primaryKey' => 'id_trafficdalkot',
            'sboViewOnly' => RoleMutationPolicy::deniesAllMutations(Auth::user()),
        ], TerritoryIndexFilters::viewBundle($request)));
    }

    public function export(Request $request, $format = 'excel')
    {
        if ($this->currentSbGrp($request) === 'patrol_online') {
            return app(TrafficsController::class)->export($request, $format);
        }

        $query = DB::table($this->tableName);
        $query = $this->applyFilters($query, $request);
        HierarchicalHelper::applyTerritoryScopeForTable($query, $this->tableName);
        $this->applyReportFilters($query, $request);

        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(function ($q) use ($s) {
                foreach ($this->searchFields as $field) {
                    $q->orWhere('trafficstuck.'.$field, 'like', "%{$s}%");
                }
                $q->orWhere('usm.nama', 'like', "%{$s}%");
            });
        }

        if ($request->filled('id') && $this->primaryKey) {
            $query->where('trafficstuck.'.$this->primaryKey, $request->id);
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

    protected function prepareDataForStore(array $validated, Request $request)
    {
        $data = [];
        foreach ($this->fillable as $f) {
            if (array_key_exists($f, $validated)) {
                $data[$f] = $validated[$f];
            }
        }
        if (Schema::hasColumn($this->tableName, 'created_at')) {
            $data['created_at'] = now();
        }
        if (Schema::hasColumn($this->tableName, 'updated_at')) {
            $data['updated_at'] = now();
        }
        $data['piket'] = $data['piket'] ?? Auth::id();

        if (! array_key_exists('jamperistiwa', $data) || $data['jamperistiwa'] === null || $data['jamperistiwa'] === '') {
            $data['jamperistiwa'] = now()->format('H:i:s');
        }

        if (Schema::hasColumn($this->tableName, 'koordinat')) {
            $data = array_merge($data, $this->koordinatFromRequest($request));
        }

        $this->applySbGrpToManualRow($data, $request);
        $this->normalizeLegacyTrafficstuckFields($data);

        return $data;
    }

    protected function prepareDataForUpdate(array $validated, Request $request)
    {
        $data = parent::prepareDataForUpdate($validated, $request);

        if (Schema::hasColumn($this->tableName, 'koordinat')) {
            foreach ($this->koordinatFromRequest($request) as $col => $val) {
                $data[$col] = $val;
            }
        }

        $this->applySbGrpToManualRow($data, $request);
        $this->normalizeLegacyTrafficstuckFields($data);

        return $data;
    }

    /**
     * Kolom NOT NULL di dump SBO lama — form boleh kosong (middleware ubah "" jadi null).
     *
     * @param  array<string, mixed>  $data
     */
    protected function normalizeLegacyTrafficstuckFields(array &$data): void
    {
        if (Schema::hasColumn($this->tableName, 'lokasi')) {
            if (! array_key_exists('lokasi', $data) || $data['lokasi'] === null || $data['lokasi'] === '') {
                $data['lokasi'] = '-';
            }
        }

        if (Schema::hasColumn($this->tableName, 'faktor')) {
            if (! array_key_exists('faktor', $data) || $data['faktor'] === null || $data['faktor'] === '') {
                $data['faktor'] = '-';
            }
        }

        if (Schema::hasColumn($this->tableName, 'penyebab')) {
            if (! array_key_exists('penyebab', $data) || $data['penyebab'] === null || $data['penyebab'] === '') {
                $data['penyebab'] = '-';
            }
        }

        // Normalise timurai: array → comma-separated string
        if (isset($data['timurai']) && is_array($data['timurai'])) {
            $data['timurai'] = implode(', ', array_filter(array_map('trim', $data['timurai'])));
        }
    }

    /**
     * Input manual dari halaman trafficstuck (bukan salinan patroli).
     *
     * @param  array<string, mixed>  $data
     */
    protected function applySbGrpToManualRow(array &$data, Request $request): void
    {
        if (! Schema::hasColumn($this->tableName, 'sb_grp')) {
            return;
        }

        $grp = $this->currentSbGrp($request);
        if ($grp !== '' && $grp !== 'giat_rutin') {
            $data['sb_grp'] = $grp;
        }
    }

    /**
     * Tandai baris yang disunting lewat form Kodal Perlambatan (per operator).
     *
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
        return 'Kodal Perlambatan';
    }

    protected function getRoutePrefix()
    {
        return 'trafficstuck';
    }
}
