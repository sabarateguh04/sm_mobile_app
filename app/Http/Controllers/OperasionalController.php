<?php

namespace App\Http\Controllers;

use App\Helpers\HierarchicalHelper;
use App\Support\RoleMutationPolicy;
use App\Support\TerritoryLevelForUsm;
use App\Traits\Exportable;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\View;

/**
 * Base Controller untuk Level Operasional
 * 
 * Level Operasional adalah level terendah dimana data langsung diinput oleh personil di lapangan.
 * Controller ini menyediakan CRUD lengkap (Create, Read, Update, Delete) untuk modul operasional.
 * 
 * Pattern: [Modul]Controller extends OperasionalController
 * Contoh: ArusmudikController, ProfilposkoController, TurjagwaliController
 */
abstract class OperasionalController extends BaseController
{
    use Exportable;

    /**
     * Nama tabel di database
     * Harus di-override di child class
     *
     * @var string
     */
    protected $tableName;

    /**
     * Primary key field
     * Default: id_[tableName]
     *
     * @var string
     */
    protected $primaryKey;

    /**
     * Nama view folder
     * Default: lowercase dari tableName tanpa prefix
     *
     * @var string
     */
    protected $viewFolder;

    /**
     * Fields yang bisa diisi (fillable)
     * Harus di-override di child class
     */
    protected $fillable = [];

    /**
     * Fields yang ditampilkan di index
     */
    protected $indexFields = [];

    /**
     * Fields yang digunakan untuk search
     */
    protected $searchFields = [];

    /**
     * Default order by
     */
    protected $defaultOrderBy = 'id';
    protected $defaultOrderType = 'desc';

    /**
     * Per page untuk pagination
     */
    protected $perPage = 20;

    /**
     * Constructor
     */
    public function __construct()
    {
        // Set primary key jika belum di-set
        if (!$this->primaryKey && $this->tableName) {
            $this->primaryKey = 'id_' . str_replace('_', '', $this->tableName);
        }

        // Set view folder jika belum di-set
        if (!$this->viewFolder && $this->tableName) {
            $this->viewFolder = strtolower($this->tableName);
        }

        $this->middleware(function (Request $request, $next) {
            $response = $next($request);

            if ($request->isMethod('POST') || $request->isMethod('PUT') || $request->isMethod('PATCH')) {
                if ($response->isSuccessful() || $response->isRedirection()) {
                    if ($this->tableName !== 't_datajalan') {
                        \App\Helpers\DataJalanHelper::autoSyncFromRequest($request);
                    }
                }
            }

            return $response;
        });
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = DB::table($this->tableName);

        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $searchFields = !empty($this->searchFields) ? $this->searchFields : $this->fillable;
            
            $query->where(function($q) use ($search, $searchFields) {
                foreach ($searchFields as $field) {
                    $q->orWhere($field, 'like', "%{$search}%");
                }
            });
        }

        // Additional filters (dapat di-override)
        $query = $this->applyFilters($query, $request);

        HierarchicalHelper::applyTerritoryScopeForTable($query, $this->tableName);

        // Sorting
        $orderBy = $request->get('orderby', $this->defaultOrderBy);
        $orderType = $request->get('ordertype', $this->defaultOrderType);
        $query->orderBy($orderBy, $orderType);

        // Pagination
        $perPage = $request->get('per_page', $this->perPage);
        $data = $query->paginate($perPage);

        return view($this->viewFolder . '.index', [
            'pageTitle' => $this->getPageTitle(),
            'data' => $data,
            'tableName' => $this->tableName,
            'primaryKey' => $this->primaryKey,
            'sboOperatorOwnership' => RoleMutationPolicy::isOperatorRole(Auth::user()),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        RoleMutationPolicy::assertCanCreate(Auth::user());

        return view($this->viewFolder . '.create', [
            'pageTitle' => 'Tambah ' . $this->getPageTitle(),
            'tableName' => $this->tableName,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        RoleMutationPolicy::assertCanCreate(Auth::user());

        $validated = $request->validate($this->getValidationRules('store'));

        // Auto set fields
        $data = $this->prepareDataForStore($validated, $request);

        $id = DB::table($this->tableName)->insertGetId($data);

        return redirect()->route($this->getRoutePrefix() . '.index')
            ->with('success', 'Data berhasil ditambahkan.');
    }

    /**
     * Display the specified resource.
     */
    public function show(string|int $id)
    {
        $data = $this->findOperationalRowOrFail($id);

        return view($this->viewFolder . '.show', array_merge([
            'pageTitle' => 'Detail ' . $this->getPageTitle(),
            'data' => $data,
            'tableName' => $this->tableName,
            'primaryKey' => $this->primaryKey,
        ], $this->operationalRecordViewFlags($data)));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string|int $id)
    {
        $data = $this->findOperationalRowOrFail($id);
        RoleMutationPolicy::assertCanEditOrDelete(Auth::user(), $data, $this->tableName);

        return view($this->viewFolder . '.edit', [
            'pageTitle' => 'Edit ' . $this->getPageTitle(),
            'data' => $data,
            'tableName' => $this->tableName,
            'primaryKey' => $this->primaryKey,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string|int $id)
    {
        $existing = $this->findOperationalRowOrFail($id);
        RoleMutationPolicy::assertCanEditOrDelete(Auth::user(), $existing, $this->tableName);

        $validated = $request->validate($this->getValidationRules('update'));

        // Auto set fields
        $data = $this->prepareDataForUpdate($validated, $request);

        DB::table($this->tableName)
            ->where($this->primaryKey, $id)
            ->update($data);

        return redirect()->route($this->getRoutePrefix() . '.index')
            ->with('success', 'Data berhasil diupdate.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string|int $id)
    {
        $existing = $this->findOperationalRowOrFail($id);
        RoleMutationPolicy::assertCanEditOrDelete(Auth::user(), $existing, $this->tableName);

        DB::table($this->tableName)->where($this->primaryKey, $id)->delete();

        return redirect()->route($this->getRoutePrefix() . '.index')
            ->with('success', 'Data berhasil dihapus.');
    }

    /**
     * Export data
     */
    public function export(Request $request, $format = 'excel')
    {
        $query = DB::table($this->tableName);

        // Apply filters
        $query = $this->applyFilters($query, $request);

        HierarchicalHelper::applyTerritoryScopeForTable($query, $this->tableName);

        if ($request->filled('id') && $this->primaryKey) {
            $query->where($this->tableName.'.'.$this->primaryKey, $request->id);
        }

        $data = $query->get();
        $isSingle = $request->filled('id') && $data->count() === 1;
        $filename = $request->get('filename', $isSingle
            ? $this->tableName.'_'.$request->id.'_'.date('Y-m-d')
            : $this->tableName.'_'.date('Y-m-d'));
        $title = $request->get('title', $isSingle
            ? 'Detail '.$this->getPageTitle()
            : $this->getPageTitle().' Report');

        switch (strtolower($format)) {
            case 'pdf':
                $view = $request->get('view', 'exports.pdf.' . $this->viewFolder);
                if (!View::exists($view)) {
                    $view = 'exports.pdf.default';
                }
                return $this->exportToPdf($data, $view, $filename, $title);
                
            case 'excel':
                return $this->exportToExcel($data, $filename, $title);
                
            case 'csv':
                return $this->exportToCsv($data, $filename);
                
            case 'word':
                $view = $request->get('view', 'exports.word.' . $this->viewFolder);
                if (!View::exists($view)) {
                    $view = 'exports.word.default';
                }
                return $this->exportToWord($data, $view, $filename, $title);
                
            case 'json':
                return $this->exportToJson($data, $filename);
                
            default:
                abort(400, 'Invalid export format');
        }
    }

    /**
     * Apply additional filters (dapat di-override)
     *
     * @param \Illuminate\Database\Query\Builder $query
     * @return \Illuminate\Database\Query\Builder
     */
    protected function applyFilters($query, Request $request)
    {
        // Override di child class untuk custom filters
        return $query;
    }

    /**
     * Prepare data before store (dapat di-override)
     */
    protected function prepareDataForStore(array $validated, Request $request)
    {
        $data = [];
        foreach ($this->fillable as $field) {
            if (isset($validated[$field])) {
                $data[$field] = $validated[$field];
            }
        }

        // Auto set timestamps
        $data['created_at'] = now();
        $data['updated_at'] = now();

        // Auto set piket/user — selalu isi pemilik baris agar operator bisa ubah data sendiri
        $data = $this->stampRecordOwnerPiket($data);

        return $data;
    }

    /**
     * Set kolom `piket` ke akun yang login bila kolom ada di tabel.
     *
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function stampRecordOwnerPiket(array $data): array
    {
        if (! $this->tableName || ! Schema::hasColumn($this->tableName, 'piket')) {
            return $data;
        }

        if (! isset($data['piket']) || $data['piket'] === '' || $data['piket'] === null) {
            $data['piket'] = \App\Support\RoleMutationPolicy::resolveActorPiketForStore(Auth::user());
        }

        return $data;
    }

    /**
     * Prepare data before update (dapat di-override)
     */
    protected function prepareDataForUpdate(array $validated, Request $request)
    {
        $data = [];
        foreach ($this->fillable as $field) {
            if (isset($validated[$field])) {
                $data[$field] = $validated[$field];
            }
        }

        // Auto set updated_at
        $data['updated_at'] = now();

        return $data;
    }

    /**
     * Insert baris tabel legacy — kompatibel jika PK belum AUTO_INCREMENT.
     */
    protected function insertLegacyRow(array $data): int
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

    /**
     * Get validation rules (harus di-override)
     */
    protected function getValidationRules($action = 'store')
    {
        return [];
    }

    /**
     * Get page title (dapat di-override)
     */
    protected function getPageTitle()
    {
        return ucwords(str_replace('_', ' ', $this->tableName));
    }

    /**
     * Get route prefix (dapat di-override)
     */
    protected function getRoutePrefix()
    {
        return $this->viewFolder;
    }

    /**
     * Ambil satu baris operasional (setelah filter wilayah bila perlu).
     */
    protected function findOperationalRowOrFail(string|int $id): object
    {
        $query = DB::table($this->tableName)->where($this->primaryKey, $id);
        HierarchicalHelper::applyTerritoryScopeForTable($query, $this->tableName);
        $row = $query->first();

        if (! $row) {
            abort(404, 'Data tidak ditemukan');
        }

        return $row;
    }

    protected function canFilterByDaerah(): bool
    {
        return \App\Support\TerritoryIndexFilters::canFilterByDaerah();
    }

    protected function canFilterByWilayah(): bool
    {
        return \App\Support\TerritoryIndexFilters::canFilterByWilayah();
    }

    protected function applyDirectDaerahWilayahFilters($query, Request $request): void
    {
        if (! Schema::hasColumn($this->tableName, 'daerah') && ! Schema::hasColumn($this->tableName, 'wilayah')) {
            return;
        }

        $table = $this->tableName;

        if ($request->filled('daerah') && $this->canFilterByDaerah()) {
            $query->whereRaw('LOWER('.$table.'.daerah) = ?', [strtolower((string) $request->daerah)]);
        }

        if ($request->filled('wilayah') && $this->canFilterByWilayah()) {
            $query->whereRaw('LOWER('.$table.'.wilayah) = ?', [strtolower((string) $request->wilayah)]);
        }
    }

    /**
     * @return \Illuminate\Support\Collection<int, string>
     */
    protected function daerahOptionsFromTable(?string $table = null)
    {
        $table = $table ?? $this->tableName;

        if (! Schema::hasColumn($table, 'daerah')) {
            return collect();
        }

        return DB::table($table)
            ->whereNotNull('daerah')
            ->where('daerah', '!=', '')
            ->distinct()
            ->orderBy('daerah')
            ->pluck('daerah');
    }

    /**
     * @return \Illuminate\Support\Collection<int, string>
     */
    protected function wilayahOptionsFromTable(Request $request, ?string $table = null)
    {
        $table = $table ?? $this->tableName;

        if (! Schema::hasColumn($table, 'wilayah')) {
            return collect();
        }

        $q = DB::table($table)->whereNotNull('wilayah')->where('wilayah', '!=', '');

        if ($request->filled('daerah')) {
            $q->whereRaw('LOWER(daerah) = ?', [strtolower((string) $request->daerah)]);
        } elseif ($user = Auth::user()) {
            if (! empty($user->daerah) && empty($user->wilayah) && $this->canFilterByWilayah()) {
                $q->whereRaw('LOWER(daerah) = ?', [strtolower((string) $user->daerah)]);
            }
        }

        return $q->distinct()->orderBy('wilayah')->pluck('wilayah');
    }
}
