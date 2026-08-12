<?php

namespace App\Http\Controllers;

use App\Helpers\ArusmudikAggregationHelper;
use App\Helpers\HierarchicalHelper;
use App\Support\RoleMutationPolicy;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
 * ArusmudikController - Level Operasional
 * 
 * Controller untuk input data arus mudik & balik di level operasional (posko).
 * Full CRUD: Create, Read, Update, Delete
 */
class ArusmudikController extends OperasionalController
{
    protected $tableName = 'arusmudik';
    protected $primaryKey = 'id_arusmudik';
    protected $viewFolder = 'arusmudik';
    
    protected $fillable = [
        'id_profileposko',
        'tanggal',
        'piket',
        'statarus',
    ];
    
    protected $searchFields = [
        'id_arusmudik',
        'id_profileposko',
        'tanggal',
        'piket',
        'statarus',
    ];
    
    protected $defaultOrderBy = 'tanggal';
    protected $defaultOrderType = 'desc';

    /**
     * Apply filters and joins for index
     */
    protected function applyFilters($query, Request $request)
    {
        // Join dengan usm untuk nama piket dan profilposko untuk wilayah
        $query->leftJoin('usm', 'arusmudik.piket', '=', 'usm.id_usm')
              ->leftJoin('profilposko', 'arusmudik.id_profileposko', '=', 'profilposko.id_profilposko')
              ->select(
                  'arusmudik.*',
                  'usm.nama as usm_nama',
                  'profilposko.daerah',
                  'profilposko.wilayah'
              );
        
        return $query;
    }

    /**
     * Simpan data operasional lalu sinkronkan tabel hierarki.
     */
    public function store(Request $request)
    {
        RoleMutationPolicy::assertCanCreate(Auth::user());

        $validated = $request->validate($this->getValidationRules('store'));
        $data = $this->prepareDataForStore($validated, $request);

        $id = DB::table($this->tableName)->insertGetId($data);
        ArusmudikAggregationHelper::syncByArusmudikId((int) $id);

        return redirect()->route($this->getRoutePrefix() . '.index')
            ->with('success', 'Data berhasil ditambahkan.');
    }

    /**
     * Perbarui data operasional lalu sinkronkan tabel hierarki.
     */
    public function update(Request $request, $id)
    {
        $existing = $this->findOperationalRowOrFail($id);
        RoleMutationPolicy::assertCanEditOrDelete(Auth::user(), $existing, $this->tableName);

        $validated = $request->validate($this->getValidationRules('update'));
        $data = $this->prepareDataForUpdate($validated, $request);

        DB::table($this->tableName)
            ->where($this->primaryKey, $id)
            ->update($data);

        ArusmudikAggregationHelper::syncByArusmudikId((int) $id);

        return redirect()->route($this->getRoutePrefix() . '.index')
            ->with('success', 'Data berhasil diupdate.');
    }

    /**
     * Hapus data operasional beserta relasi ringkasan hierarki.
     */
    public function destroy($id)
    {
        $existing = $this->findOperationalRowOrFail($id);
        RoleMutationPolicy::assertCanEditOrDelete(Auth::user(), $existing, $this->tableName);

        DB::table($this->tableName)->where($this->primaryKey, $id)->delete();
        ArusmudikAggregationHelper::deleteByArusmudikId((int) $id);

        return redirect()->route($this->getRoutePrefix() . '.index')
            ->with('success', 'Data berhasil dihapus.');
    }

    /**
     * Override index to add join
     */
    public function index(Request $request)
    {
        $query = DB::table($this->tableName);

        // Apply joins
        $query = $this->applyFilters($query, $request);

        HierarchicalHelper::applyTerritoryScopeForTable($query, $this->tableName);

        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $searchFields = !empty($this->searchFields) ? $this->searchFields : $this->fillable;
            
            $query->where(function($q) use ($search, $searchFields) {
                foreach ($searchFields as $field) {
                    if ($field === 'piket') {
                        $q->orWhere('usm.nama', 'like', "%{$search}%");
                    } else {
                        $q->orWhere("arusmudik.{$field}", 'like', "%{$search}%");
                    }
                }
            });
        }

        // Sorting
        $orderBy = $request->get('orderby', $this->defaultOrderBy);
        $orderType = $request->get('ordertype', $this->defaultOrderType);
        $query->orderBy("arusmudik.{$orderBy}", $orderType);

        // Pagination
        $perPage = $request->get('per_page', $this->perPage);
        $data = $query->paginate($perPage);

        return view($this->viewFolder . '.index', [
            'pageTitle' => $this->getPageTitle(),
            'data' => $data,
            'tableName' => $this->tableName,
            'primaryKey' => $this->primaryKey,
        ]);
    }

    /**
     * Override show to add join dengan profilposko untuk daerah dan wilayah
     */
    public function show($id)
    {
        $data = DB::table($this->tableName)
            ->leftJoin('usm', 'arusmudik.piket', '=', 'usm.id_usm')
            ->leftJoin('profilposko', 'arusmudik.id_profileposko', '=', 'profilposko.id_profilposko')
            ->select(
                'arusmudik.*',
                'usm.nama as usm_nama',
                'profilposko.daerah',
                'profilposko.wilayah'
            )
            ->where("arusmudik.{$this->primaryKey}", $id)
            ->first();

        if (!$data) {
            abort(404, 'Data tidak ditemukan');
        }

        return view($this->viewFolder . '.show', [
            'pageTitle' => 'Detail ' . $this->getPageTitle(),
            'data' => $data,
            'tableName' => $this->tableName,
            'primaryKey' => $this->primaryKey,
        ]);
    }

    /**
     * Update single field via AJAX (editField)
     */
    public function editField(Request $request, $id)
    {
        $field = $request->input('field');
        $value = $request->input('value');

        if (!in_array($field, $this->fillable)) {
            return response()->json(['success' => false, 'message' => 'Field tidak diizinkan'], 400);
        }

        DB::table($this->tableName)
            ->where($this->primaryKey, $id)
            ->update([
                $field => $value,
                'updated_at' => now()
            ]);

        ArusmudikAggregationHelper::syncByArusmudikId((int) $id);

        return response()->json(['success' => true, 'message' => 'Field berhasil diupdate']);
    }

    /**
     * Get validation rules
     */
    protected function getValidationRules($action = 'store')
    {
        return [
            'id_profileposko' => 'required|exists:profilposko,id_profilposko',
            'tanggal' => 'required|date',
            'statarus' => 'nullable|string|max:255',
        ];
    }

    /**
     * Get page title
     */
    protected function getPageTitle()
    {
        return 'Arus Mudik & Balik';
    }
}
