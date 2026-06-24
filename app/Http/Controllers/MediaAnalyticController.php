<?php

namespace App\Http\Controllers;

use App\Support\ReferensiOptions;
use App\Support\RoleMutationPolicy;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;

/**
 * Media Analytic — input manual operator (tabel mediaanalytic).
 */
class MediaAnalyticController extends LegacySboOperasionalController
{
    protected $tableName = 'mediaanalytic';

    protected $primaryKey = 'id_mediaanalytic';

    protected $viewFolder = 'media.analytic';

    protected $fillable = ['tanggal', 'issue_topic', 'vol_percakapan', 'sentimen', 'potensi'];

    protected $searchFields = ['issue_topic', 'sentimen', 'potensi'];

    protected $defaultOrderBy = 'tanggal';

    protected $defaultOrderType = 'desc';

    public function __construct()
    {
        parent::__construct();

        $this->middleware(function (Request $request, $next) {
            $this->ensureTableExists();

            return $next($request);
        });
    }

    protected function ensureTableExists(): void
    {
        if (Schema::hasTable($this->tableName)) {
            return;
        }

        Schema::create($this->tableName, function (Blueprint $table) {
            $table->increments('id_mediaanalytic');
            $table->date('tanggal');
            $table->string('issue_topic', 255)->nullable();
            $table->unsignedInteger('vol_percakapan')->default(0);
            $table->string('sentimen', 50)->nullable();
            $table->string('potensi', 150)->nullable();
        });
    }

    protected function applyFilters($query, Request $request)
    {
        if ($request->filled('tanggal_dari')) {
            $query->where($this->tableName.'.tanggal', '>=', $request->tanggal_dari);
        }
        if ($request->filled('tanggal_sampai')) {
            $query->where($this->tableName.'.tanggal', '<=', $request->tanggal_sampai);
        }
        if ($request->filled('sentimen')) {
            $query->where($this->tableName.'.sentimen', $request->sentimen);
        }
        if ($request->filled('potensi')) {
            $query->where($this->tableName.'.potensi', $request->potensi);
        }

        return $query;
    }

    public function index(Request $request)
    {
        $query = DB::table($this->tableName);
        $query = $this->applyFilters($query, $request);
        // mediaanalytic tidak punya kolom daerah/wilayah/piket — data tampil untuk semua role yang punya akses menu.

        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(function ($q) use ($s) {
                foreach ($this->searchFields as $field) {
                    $q->orWhere($this->tableName.'.'.$field, 'like', "%{$s}%");
                }
            });
        }

        $query->orderBy(
            $this->tableName.'.'.$request->get('orderby', $this->defaultOrderBy),
            $request->get('ordertype', $this->defaultOrderType)
        );

        $data = $query->paginate($request->get('per_page', $this->perPage))
            ->appends($request->only(['search', 'tanggal_dari', 'tanggal_sampai', 'sentimen', 'potensi']));

        $potensiOptions = DB::table($this->tableName)
            ->whereNotNull('potensi')
            ->where('potensi', '!=', '')
            ->distinct()
            ->orderBy('potensi')
            ->pluck('potensi');

        return view($this->viewFolder.'.index', [
            'pageTitle' => 'List Media Analytic',
            'data' => $data,
            'tableName' => $this->tableName,
            'primaryKey' => $this->primaryKey,
            'sentimenOptions' => $this->sentimenOptions(),
            'potensiOptions' => $potensiOptions,
            'sboViewOnly' => RoleMutationPolicy::deniesAllMutations(Auth::user()),
            'sboOperatorOwnership' => RoleMutationPolicy::mutationMode(Auth::user()) === RoleMutationPolicy::MODE_OWN,
        ]);
    }

    /**
     * mediaanalytic tanpa kolom teritori — jangan filter via applyTerritoryScopeForTable (menghasilkan 0 baris).
     */
    protected function findOperationalRowOrFail(string|int $id): object
    {
        $row = DB::table($this->tableName)->where($this->primaryKey, $id)->first();

        if (! $row) {
            abort(404, 'Data tidak ditemukan');
        }

        return $row;
    }

    public function create()
    {
        RoleMutationPolicy::assertCanCreate(Auth::user());

        return view($this->viewFolder.'.create', $this->formViewData('Tambah Media Analytic'));
    }

    public function store(Request $request)
    {
        RoleMutationPolicy::assertCanCreate(Auth::user());

        $validated = $request->validate($this->getValidationRules('store'));
        $data = $this->prepareDataForStore($validated, $request);

        DB::table($this->tableName)->insertGetId($data, $this->primaryKey);

        return redirect()->route($this->getRoutePrefix().'.index')
            ->with('success', 'Data berhasil ditambahkan.');
    }

    public function show($id)
    {
        $data = $this->findOperationalRowOrFail($id);

        return view($this->viewFolder.'.show', array_merge(
            $this->formViewData('Detail Media Analytic', $data),
            $this->operationalRecordViewFlags($data),
            ['sboViewOnly' => RoleMutationPolicy::deniesAllMutations(Auth::user())]
        ));
    }

    public function edit($id)
    {
        $data = $this->findOperationalRowOrFail($id);
        RoleMutationPolicy::assertCanEditOrDelete(Auth::user(), $data, $this->tableName);

        return view($this->viewFolder.'.edit', $this->formViewData('Edit Media Analytic', $data));
    }

    /**
     * @return array<string, mixed>
     */
    protected function formViewData(string $pageTitle, ?object $data = null): array
    {
        return [
            'pageTitle' => $pageTitle,
            'data' => $data,
            'sentimenOptions' => $this->sentimenOptions(),
        ];
    }

    protected function getValidationRules($action = 'store')
    {
        return [
            'tanggal' => 'required|date',
            'issue_topic' => 'nullable|string|max:255',
            'vol_percakapan' => 'required|integer|min:0',
            'sentimen' => 'nullable|string|max:50|in:Positif,Negatif,Netral',
            'potensi' => 'nullable|string|max:150',
        ];
    }

    protected function prepareDataForStore(array $validated, Request $request)
    {
        $data = [];
        foreach ($this->fillable as $field) {
            if (array_key_exists($field, $validated)) {
                $data[$field] = $validated[$field];
            }
        }
        $data['vol_percakapan'] = (int) ($data['vol_percakapan'] ?? 0);

        return $data;
    }

    protected function prepareDataForUpdate(array $validated, Request $request)
    {
        $data = parent::prepareDataForUpdate($validated, $request);
        if (array_key_exists('vol_percakapan', $data)) {
            $data['vol_percakapan'] = (int) $data['vol_percakapan'];
        }

        return $data;
    }

    protected function getPageTitle()
    {
        return 'Media Analytic';
    }

    protected function getRoutePrefix()
    {
        return 'media.analytic';
    }

    /**
     * @return list<string>
     */
    protected function sentimenOptions(): array
    {
        return ReferensiOptions::list(
            'sentimen',
            ['Positif', 'Negatif', 'Netral'],
            $this->tableName,
            'sentimen'
        );
    }
}
