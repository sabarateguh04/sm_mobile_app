<?php

namespace App\Http\Controllers;

use App\Traits\Exportable;
use App\Helpers\HierarchicalHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\View;

/**
 * Base Controller untuk Level Hierarki (Wilayah, Daerah, Nasional)
 * 
 * Controller ini digunakan untuk level aggregasi data:
 * - Level Wilayah: Filter berdasarkan wilayah user (read-only)
 * - Level Daerah: Filter berdasarkan daerah user (read-only)
 * - Level Nasional: Tanpa filter, semua data (read-only)
 * 
 * Pattern:
 * - Wilayah: Wil_[Modul]Controller extends HierarchicalController
 * - Daerah: Da_[Modul]Controller extends HierarchicalController
 * - Nasional: Nas_[Modul]Controller extends HierarchicalController
 */
abstract class HierarchicalController extends BaseController
{
    use Exportable;

    /**
     * Level hierarki: 'wilayah', 'daerah', atau 'nasional'
     * Harus di-set di child class
     */
    protected $hierarchyLevel;

    /**
     * Nama tabel operasional (untuk join)
     * Harus di-override di child class
     */
    protected $operasionalTable;

    /**
     * Nama tabel aggregasi (wil_*, da_*, nas_*)
     * Default: {level}_{operasionalTable}
     */
    protected $aggregationTable;

    /**
     * Nama view folder
     */
    protected $viewFolder;

    /**
     * Primary key field
     */
    protected $primaryKey;

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
        // Set aggregation table jika belum di-set
        if (!$this->aggregationTable && $this->hierarchyLevel && $this->operasionalTable) {
            $prefix = '';
            switch ($this->hierarchyLevel) {
                case 'wilayah':
                    $prefix = 'wil_';
                    break;
                case 'daerah':
                    $prefix = 'da_';
                    break;
                case 'nasional':
                    $prefix = 'nas_';
                    break;
            }
            $this->aggregationTable = $prefix . $this->operasionalTable;
        }

        // Set view folder jika belum di-set
        if (!$this->viewFolder && $this->aggregationTable) {
            $this->viewFolder = strtolower($this->aggregationTable);
        }

        // Set primary key jika belum di-set
        if (!$this->primaryKey && $this->aggregationTable) {
            $this->primaryKey = 'id_' . str_replace('_', '', $this->aggregationTable);
        }
    }

    /**
     * Display a listing of the resource (Read-Only).
     */
    public function index(Request $request)
    {
        $query = DB::table($this->aggregationTable);

        // Apply hierarchical filter berdasarkan level
        $query = $this->applyHierarchicalFilter($query);

        // Join dengan tabel operasional jika diperlukan
        $query = $this->applyJoins($query);

        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $searchFields = !empty($this->searchFields) ? $this->searchFields : $this->indexFields;
            
            $query->where(function($q) use ($search, $searchFields) {
                foreach ($searchFields as $field) {
                    $q->orWhere($this->aggregationTable . '.' . $field, 'like', "%{$search}%");
                }
            });
        }

        // Additional filters (dapat di-override)
        $query = $this->applyFilters($query, $request);

        // Select fields
        $selectFields = $this->getSelectFields();
        if (!empty($selectFields)) {
            $query->select($selectFields);
        }

        // Sorting
        $orderBy = $request->get('orderby', $this->defaultOrderBy);
        $orderType = $request->get('ordertype', $this->defaultOrderType);
        
        // Check if orderBy contains table prefix, if not add aggregation table prefix
        if (!str_contains($orderBy, '.')) {
            $orderBy = $this->aggregationTable . '.' . $orderBy;
        }
        
        $query->orderBy($orderBy, $orderType);

        // Pagination
        $perPage = $request->get('per_page', $this->perPage);
        $data = $query->paginate($perPage);

        return view($this->viewFolder . '.index', [
            'pageTitle' => $this->getPageTitle(),
            'data' => $data,
            'hierarchyLevel' => $this->hierarchyLevel,
            'aggregationTable' => $this->aggregationTable,
        ]);
    }

    /**
     * Display the specified resource (Read-Only).
     */
    public function show($id)
    {
        $query = DB::table($this->aggregationTable)
            ->where($this->aggregationTable . '.' . $this->primaryKey, $id);

        // Apply hierarchical filter
        $query = $this->applyHierarchicalFilter($query);

        // Apply joins
        $query = $this->applyJoins($query);

        $data = $query->first();

        if (!$data) {
            abort(404, 'Data tidak ditemukan');
        }

        return view($this->viewFolder . '.show', [
            'pageTitle' => 'Detail ' . $this->getPageTitle(),
            'data' => $data,
            'hierarchyLevel' => $this->hierarchyLevel,
        ]);
    }

    /**
     * Export data
     */
    public function export(Request $request, $format = 'excel')
    {
        $query = DB::table($this->aggregationTable);

        // Apply hierarchical filter
        $query = $this->applyHierarchicalFilter($query);

        // Apply joins
        $query = $this->applyJoins($query);

        // Apply filters
        $query = $this->applyFilters($query, $request);

        if ($request->filled('id') && ! empty($this->primaryKey)) {
            $query->where($this->aggregationTable.'.'.$this->primaryKey, $request->id);
        }

        // Select fields
        $selectFields = $this->getSelectFields();
        if (!empty($selectFields)) {
            $query->select($selectFields);
        }

        $data = $query->get();
        $isSingle = $request->filled('id') && $data->count() === 1;
        $filename = $request->get('filename', $isSingle
            ? $this->aggregationTable.'_'.$request->id.'_'.date('Y-m-d')
            : $this->aggregationTable.'_'.date('Y-m-d'));
        $title = $request->get('title', $isSingle
            ? 'Detail '.$this->getReportTitle()
            : $this->getReportTitle());

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
     * Apply hierarchical filter berdasarkan level user
     * 
     * @param \Illuminate\Database\Query\Builder $query
     * @param string|null $level
     * @return \Illuminate\Database\Query\Builder
     */
    protected function applyHierarchicalFilter($query, $level = null)
    {
        /** @var \App\Models\User|null $user */
        $user = Auth::user();
        
        if (!$user) {
            return $query;
        }

        // Use provided level or fallback to instance property
        $hierarchyLevel = $level ?? $this->hierarchyLevel;

        switch ($hierarchyLevel) {
            case 'wilayah':
                if (!empty($user->wilayah)) {
                    $query->where($this->aggregationTable . '.wilayah', $user->wilayah);
                }
                break;
                
            case 'daerah':
                if (!empty($user->daerah)) {
                    $query->where($this->aggregationTable . '.daerah', $user->daerah);
                }
                break;
                
            case 'nasional':
                // No filter - show all data
                break;
        }

        return $query;
    }

    /**
     * Apply joins dengan tabel operasional (dapat di-override)
     */
    protected function applyJoins($query)
    {
        // Override di child class untuk custom joins
        // Contoh:
        // $query->leftJoin('arusmudik', $this->aggregationTable . '.id_arusmudik', '=', 'arusmudik.id_arusmudik')
        //       ->leftJoin('usm', 'arusmudik.piket', '=', 'usm.id_usm');
        return $query;
    }

    /**
     * Get select fields (dapat di-override)
     */
    protected function getSelectFields()
    {
        // Default: select all dari aggregation table
        return [$this->aggregationTable . '.*'];
    }

    /**
     * Apply additional filters (dapat di-override)
     */
    protected function applyFilters($query, Request $request)
    {
        // Override di child class untuk custom filters
        return $query;
    }

    /**
     * Get page title (dapat di-override)
     */
    protected function getPageTitle()
    {
        $levelLabel = ucfirst($this->hierarchyLevel);
        $modulName = ucwords(str_replace('_', ' ', $this->operasionalTable));
        return $levelLabel . ' - ' . $modulName;
    }

    /**
     * Get report title (dapat di-override)
     */
    protected function getReportTitle()
    {
        $levelLabel = ucfirst($this->hierarchyLevel);
        $modulName = ucwords(str_replace('_', ' ', $this->operasionalTable));
        
        if ($this->hierarchyLevel === 'wilayah') {
            return 'Laporan Posko : ' . $modulName;
        } else {
            return 'Report Ops : ' . $modulName;
        }
    }
}
