<?php

namespace App\Http\Controllers;

use App\Helpers\HierarchicalHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class AuditarusController extends OperasionalController
{
    protected $tableName = 'auditarus';
    protected $primaryKey = 'id_auditarus';
    protected $viewFolder = 'auditarus';

    protected $fillable = ['id_profileposko', 'tanggal', 'piket', 'hasil_audit', 'catatan'];
    protected $searchFields = ['hasil_audit', 'catatan'];
    protected $defaultOrderBy = 'tanggal';
    protected $defaultOrderType = 'desc';

    protected function applyFilters($query, Request $request)
    {
        $query->leftJoin('usm', 'auditarus.piket', '=', 'usm.id_usm')
            ->leftJoin('profilposko', 'auditarus.id_profileposko', '=', 'profilposko.id_profilposko')
            ->select('auditarus.*', 'usm.nama as usm_nama', 'profilposko.namaposko as posko_nama');
        return $query;
    }

    public function index(Request $request)
    {
        $query = DB::table($this->tableName);
        $query = $this->applyFilters($query, $request);
        HierarchicalHelper::applyTerritoryScopeForTable($query, $this->tableName);
        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(function ($q) use ($s) {
                $q->orWhere('auditarus.hasil_audit', 'like', "%{$s}%")->orWhere('auditarus.catatan', 'like', "%{$s}%")
                    ->orWhere('usm.nama', 'like', "%{$s}%")->orWhere('profilposko.namaposko', 'like', "%{$s}%");
            });
        }
        $query->orderBy('auditarus.' . $request->get('orderby', $this->defaultOrderBy), $request->get('ordertype', $this->defaultOrderType));
        $data = $query->paginate($request->get('per_page', $this->perPage));
        return view($this->viewFolder . '.index', ['pageTitle' => $this->getPageTitle(), 'data' => $data, 'tableName' => $this->tableName, 'primaryKey' => $this->primaryKey]);
    }

    public function show($id)
    {
        $data = DB::table($this->tableName)
            ->leftJoin('usm', 'auditarus.piket', '=', 'usm.id_usm')
            ->leftJoin('profilposko', 'auditarus.id_profileposko', '=', 'profilposko.id_profilposko')
            ->select('auditarus.*', 'usm.nama as usm_nama', 'profilposko.namaposko as posko_nama')
            ->where('auditarus.' . $this->primaryKey, $id)->first();
        if (!$data) abort(404, 'Data tidak ditemukan');
        return view($this->viewFolder . '.show', ['pageTitle' => 'Detail ' . $this->getPageTitle(), 'data' => $data, 'tableName' => $this->tableName, 'primaryKey' => $this->primaryKey]);
    }

    protected function getValidationRules($action = 'store')
    {
        return ['id_profileposko' => 'nullable|exists:profilposko,id_profilposko', 'tanggal' => 'required|date', 'hasil_audit' => 'nullable|string', 'catatan' => 'nullable|string'];
    }

    protected function prepareDataForStore(array $validated, Request $request)
    {
        $data = [];
        foreach ($this->fillable as $f) { if (isset($validated[$f])) $data[$f] = $validated[$f]; }
        $data['created_at'] = $data['updated_at'] = now();
        $data['piket'] = $data['piket'] ?? Auth::id();
        return $data;
    }

    protected function getPageTitle() { return 'Audit Arus'; }
    protected function getRoutePrefix() { return 'auditarus'; }
}
