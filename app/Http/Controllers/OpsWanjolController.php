<?php

namespace App\Http\Controllers;

use App\Helpers\HierarchicalHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
 * OpsWanjolController - Peristiwa Menonjol (Level Operasional)
 * Full CRUD: Create, Read, Update, Delete + Export
 */
class OpsWanjolController extends OperasionalController
{
    protected $tableName = 'ops_wanjol';
    protected $primaryKey = 'id_wanjol';
    protected $viewFolder = 'ops_wanjol';

    protected $fillable = [
        'id_profileposko',
        'tanggal',
        'lokasi',
        'uraian',
        'piket',
        'status',
    ];

    protected $searchFields = [
        'lokasi',
        'uraian',
        'status',
    ];

    protected $defaultOrderBy = 'tanggal';
    protected $defaultOrderType = 'desc';

    protected function applyFilters($query, Request $request)
    {
        $query->leftJoin('usm', 'ops_wanjol.piket', '=', 'usm.id_usm')
            ->leftJoin('profilposko', 'ops_wanjol.id_profileposko', '=', 'profilposko.id_profilposko')
            ->select(
                'ops_wanjol.*',
                'usm.nama as usm_nama',
                'profilposko.namaposko as posko_nama',
                'profilposko.daerah',
                'profilposko.wilayah'
            );

        return $query;
    }

    public function index(Request $request)
    {
        $query = DB::table($this->tableName);
        $query = $this->applyFilters($query, $request);

        HierarchicalHelper::applyTerritoryScopeForTable($query, $this->tableName);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->orWhere('ops_wanjol.lokasi', 'like', "%{$search}%")
                    ->orWhere('ops_wanjol.uraian', 'like', "%{$search}%")
                    ->orWhere('ops_wanjol.status', 'like', "%{$search}%")
                    ->orWhere('usm.nama', 'like', "%{$search}%")
                    ->orWhere('profilposko.namaposko', 'like', "%{$search}%");
            });
        }

        $orderBy = $request->get('orderby', $this->defaultOrderBy);
        $orderType = $request->get('ordertype', $this->defaultOrderType);
        $query->orderBy("ops_wanjol.{$orderBy}", $orderType);

        $perPage = $request->get('per_page', $this->perPage);
        $data = $query->paginate($perPage);

        return view($this->viewFolder . '.index', [
            'pageTitle' => $this->getPageTitle(),
            'data' => $data,
            'tableName' => $this->tableName,
            'primaryKey' => $this->primaryKey,
        ]);
    }

    public function show($id)
    {
        $data = DB::table($this->tableName)
            ->leftJoin('usm', 'ops_wanjol.piket', '=', 'usm.id_usm')
            ->leftJoin('profilposko', 'ops_wanjol.id_profileposko', '=', 'profilposko.id_profilposko')
            ->select(
                'ops_wanjol.*',
                'usm.nama as usm_nama',
                'profilposko.namaposko as posko_nama',
                'profilposko.daerah',
                'profilposko.wilayah'
            )
            ->where('ops_wanjol.' . $this->primaryKey, $id)
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
                'updated_at' => now(),
            ]);

        return response()->json(['success' => true, 'message' => 'Field berhasil diupdate']);
    }

    protected function getValidationRules($action = 'store')
    {
        return [
            'id_profileposko' => 'nullable|exists:profilposko,id_profilposko',
            'tanggal' => 'required|date',
            'lokasi' => 'nullable|string|max:255',
            'uraian' => 'nullable|string',
            'status' => 'nullable|string|max:100',
        ];
    }

    protected function prepareDataForStore(array $validated, Request $request)
    {
        $data = [];
        foreach ($this->fillable as $field) {
            if (isset($validated[$field])) {
                $data[$field] = $validated[$field];
            }
        }
        $data['created_at'] = now();
        $data['updated_at'] = now();
        if (in_array('piket', $this->fillable)) {
            $data['piket'] = $data['piket'] ?? Auth::id();
        }
        return $data;
    }

    protected function getPageTitle()
    {
        return 'Peristiwa Menonjol';
    }

    protected function getRoutePrefix()
    {
        return 'ops_wanjol';
    }
}
