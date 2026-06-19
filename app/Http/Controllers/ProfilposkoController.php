<?php

namespace App\Http\Controllers;

use App\Helpers\HierarchicalHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
 * ProfilposkoController - Profile Posko (Level Operasional)
 * Full CRUD + Export. Tabel profilposko sudah ada.
 */
class ProfilposkoController extends OperasionalController
{
    protected $tableName = 'profilposko';
    protected $primaryKey = 'id_profilposko';
    protected $viewFolder = 'profilposko';

    protected $fillable = [
        'piket',
        'katops',
        'jenisops',
        'tahunops',
        'temaops',
        'namaposko',
        'lokasiposko',
        'sarpras',
        'fotoposko',
        'alamat',
        'wilayah',
        'daerah',
        'keterangan',
    ];

    protected $searchFields = [
        'namaposko',
        'katops',
        'wilayah',
        'daerah',
        'alamat',
    ];

    protected $defaultOrderBy = 'namaposko';
    protected $defaultOrderType = 'asc';

    protected function applyFilters($query, Request $request)
    {
        $query->leftJoin('usm', 'profilposko.piket', '=', 'usm.id_usm')
            ->select('profilposko.*', 'usm.nama as usm_nama');
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
                foreach ($this->searchFields as $field) {
                    $q->orWhere('profilposko.' . $field, 'like', "%{$search}%");
                }
                $q->orWhere('usm.nama', 'like', "%{$search}%");
            });
        }

        $orderBy = $request->get('orderby', $this->defaultOrderBy);
        $orderType = $request->get('ordertype', $this->defaultOrderType);
        $query->orderBy("profilposko.{$orderBy}", $orderType);

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
            ->leftJoin('usm', 'profilposko.piket', '=', 'usm.id_usm')
            ->select('profilposko.*', 'usm.nama as usm_nama')
            ->where('profilposko.' . $this->primaryKey, $id)
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

    protected function getValidationRules($action = 'store')
    {
        return [
            'namaposko' => 'nullable|string|max:255',
            'katops' => 'nullable|string|max:100',
            'jenisops' => 'nullable|string|max:100',
            'tahunops' => 'nullable|string|max:20',
            'temaops' => 'nullable|string|max:255',
            'lokasiposko' => 'nullable|string',
            'sarpras' => 'nullable|string',
            'alamat' => 'nullable|string|max:255',
            'wilayah' => 'nullable|string|max:100',
            'daerah' => 'nullable|string|max:100',
            'keterangan' => 'nullable|string',
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
        $data['created_at'] = now();
        $data['updated_at'] = now();
        $data['piket'] = $data['piket'] ?? Auth::id();
        return $data;
    }

    protected function getPageTitle()
    {
        return 'Profile Posko';
    }

    protected function getRoutePrefix()
    {
        return 'profilposko';
    }
}
