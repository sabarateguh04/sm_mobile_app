<?php

namespace App\Http\Controllers;

use App\Helpers\HierarchicalHelper;
use App\Support\TerritoryIndexFilters;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * Layanan Data — tabel pnp_angkutan (legacy: datapnp).
 */
class DatapnpLayananController extends LegacySboOperasionalController
{
    protected $tableName = 'pnp_angkutan';

    protected $primaryKey = 'id_datapnp';

    protected $viewFolder = 'layanan.data';

    protected $fillable = ['tanggal', 'piket', 'jenisterminal', 'namaterminal', 'kedatanganpnp', 'keberangkatanpnp', 'kedatanganangk', 'keberangkatanangk', 'koresponden', 'fotogiat'];

    protected $searchFields = ['jenisterminal', 'namaterminal', 'koresponden'];

    protected $defaultOrderBy = 'tanggal';

    protected $defaultOrderType = 'desc';

    protected function applyFilters($query, Request $request)
    {
        $query->leftJoin('usm', 'pnp_angkutan.piket', '=', 'usm.id_usm')
            ->select('pnp_angkutan.*', 'usm.nama as usm_nama', 'usm.daerah as daerah_nama', 'usm.wilayah as wilayah_nama');

        TerritoryIndexFilters::applyUsmFilters($query, $request);

        return $query;
    }

    public function index(Request $request)
    {
        $query = DB::table($this->tableName);
        $query = $this->applyFilters($query, $request);
        // Territory filter already applied in applyFilters() via TerritoryIndexFilters::applyUsmFilters()

        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(function ($q) use ($s) {
                foreach ($this->searchFields as $field) {
                    $q->orWhere('pnp_angkutan.'.$field, 'like', "%{$s}%");
                }
                $q->orWhere('usm.nama', 'like', "%{$s}%");
            });
        }

        $query->orderBy(
            'pnp_angkutan.'.$request->get('orderby', $this->defaultOrderBy),
            $request->get('ordertype', $this->defaultOrderType)
        );

        $data = $query->paginate($request->get('per_page', $this->perPage));

        return view($this->viewFolder.'.index', array_merge([
            'pageTitle' => $this->getPageTitle(),
            'data' => $data,
            'tableName' => $this->tableName,
            'primaryKey' => $this->primaryKey,
        ], TerritoryIndexFilters::viewBundle($request)));
    }

    public function show($id)
    {
        $data = DB::table($this->tableName)
            ->leftJoin('usm', 'pnp_angkutan.piket', '=', 'usm.id_usm')
            ->select('pnp_angkutan.*', 'usm.nama as usm_nama')
            ->where($this->tableName.'.'.$this->primaryKey, $id)->first();
        if (! $data) {
            abort(404, 'Tidak ditemukan');
        }

        return view($this->viewFolder.'.show', [
            'pageTitle' => 'Detail '.$this->getPageTitle(),
            'data' => $data,
            'tableName' => $this->tableName,
            'primaryKey' => $this->primaryKey,
        ]);
    }

    protected function getValidationRules($action = 'store')
    {
        return [
            'tanggal' => 'nullable|date',
            'piket' => 'nullable|string|max:100',
            'jenisterminal' => 'required|string|max:100',
            'namaterminal' => 'required|string|max:100',
            'kedatanganpnp' => 'required|integer',
            'keberangkatanpnp' => 'required|integer',
            'kedatanganangk' => 'required|integer',
            'keberangkatanangk' => 'required|integer',
            'koresponden' => 'required|string|max:100',
            'fotogiat' => 'nullable|string|max:255',
        ];
    }

    protected function fotoFileValidationRule(bool $required): string
    {
        $rule = 'image|mimes:jpeg,png,jpg,gif,webp|max:8192';

        return $required ? 'required|'.$rule : 'nullable|'.$rule;
    }

    public function store(Request $request)
    {
        $rules = $this->getValidationRules('store');
        unset($rules['fotogiat']);
        $rules['fotogiat_file'] = $this->fotoFileValidationRule(true);

        $validated = $request->validate($rules);

        $path = $request->file('fotogiat_file')->store('datapnp_giat', 'public');
        $validated['fotogiat'] = mb_substr('/storage/'.$path, 0, 255);

        $data = $this->prepareDataForStore($validated, $request);
        $this->insertLegacySboRow($data);

        return redirect()->route($this->getRoutePrefix().'.index')
            ->with('success', 'Data berhasil ditambahkan.');
    }

    public function update(Request $request, string|int $id)
    {
        $row = DB::table($this->tableName)->where($this->primaryKey, $id)->first();
        if (! $row) {
            abort(404, 'Data tidak ditemukan');
        }

        $this->assertOperatorOwnsOperationalRow($row);

        $rules = $this->getValidationRules('update');
        unset($rules['fotogiat']);
        $rules['fotogiat_file'] = $this->fotoFileValidationRule(false);

        $validated = $request->validate($rules);

        if ($request->hasFile('fotogiat_file')) {
            $path = $request->file('fotogiat_file')->store('datapnp_giat', 'public');
            $validated['fotogiat'] = mb_substr('/storage/'.$path, 0, 255);
        } else {
            $validated['fotogiat'] = (string) ($row->fotogiat ?? '');
        }
        if (trim((string) $validated['fotogiat']) === '') {
            throw ValidationException::withMessages([
                'fotogiat_file' => ['Foto giat wajib ada. Unggah foto terlebih dahulu.'],
            ]);
        }

        $data = $this->prepareDataForUpdate($validated, $request);
        DB::table($this->tableName)->where($this->primaryKey, $id)->update($data);

        return redirect()->route($this->getRoutePrefix().'.index')
            ->with('success', 'Data berhasil diupdate.');
    }

    protected function getPageTitle()
    {
        return 'Layanan Data (Penumpang)';
    }

    protected function getRoutePrefix()
    {
        return 'layanan.data';
    }
}
