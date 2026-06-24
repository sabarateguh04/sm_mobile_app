<?php

namespace App\Http\Controllers;

use App\Helpers\HierarchicalHelper;
use App\Support\TerritoryIndexFilters;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;

/**
 * PNP Angkutan — tabel pnp_angkutan (legacy: datapnp).
 */
class PnpangkutanController extends LegacySboOperasionalController
{
    protected $tableName = 'pnp_angkutan';

    protected $primaryKey = 'id_datapnp';

    protected $viewFolder = 'pnpangkutan';

    protected $fillable = [
        'tanggal', 'piket', 'jenisterminal', 'namaterminal',
        'kedatanganpnp', 'keberangkatanpnp', 'kedatanganangk', 'keberangkatanangk',
        'koresponden', 'fotogiat', 'koordinat',
    ];

    protected $searchFields = ['jenisterminal', 'namaterminal', 'koresponden'];

    protected $defaultOrderBy = 'tanggal';

    protected $defaultOrderType = 'desc';

    protected function applyFilters($query, Request $request)
    {
        $query->leftJoin('usm', 'pnp_angkutan.piket', '=', 'usm.id_usm')
            ->select('pnp_angkutan.*', 'usm.nama as usm_nama', 'usm.daerah as daerah_nama', 'usm.wilayah as wilayah_nama');

        if ($request->filled('tanggal_dari')) {
            $query->whereDate('pnp_angkutan.tanggal', '>=', $request->tanggal_dari);
        }
        if ($request->filled('tanggal_sampai')) {
            $query->whereDate('pnp_angkutan.tanggal', '<=', $request->tanggal_sampai);
        }
        if ($request->filled('jenisterminal')) {
            $query->where('pnp_angkutan.jenisterminal', $request->jenisterminal);
        }

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
            'jenisTerminalList' => $this->jenisTerminalList(),
        ], TerritoryIndexFilters::viewBundle($request)));
    }

    public function create()
    {
        return view($this->viewFolder.'.create', [
            'pageTitle' => 'Tambah '.$this->getPageTitle(),
            'tableName' => $this->tableName,
            'jenisTerminalList' => $this->jenisTerminalList(),
        ]);
    }

    public function show($id)
    {
        $data = DB::table($this->tableName)
            ->leftJoin('usm', 'pnp_angkutan.piket', '=', 'usm.id_usm')
            ->select('pnp_angkutan.*', 'usm.nama as usm_nama')
            ->where('pnp_angkutan.'.$this->primaryKey, $id)
            ->first();

        if (! $data) {
            abort(404, 'Data tidak ditemukan');
        }

        return view($this->viewFolder.'.show', [
            'pageTitle' => 'Detail '.$this->getPageTitle(),
            'data' => $data,
            'tableName' => $this->tableName,
            'primaryKey' => $this->primaryKey,
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
            'pageTitle' => 'Edit '.$this->getPageTitle(),
            'data' => $data,
            'tableName' => $this->tableName,
            'primaryKey' => $this->primaryKey,
            'jenisTerminalList' => $this->jenisTerminalList(),
        ]);
    }

    protected function getValidationRules($action = 'store')
    {
        return [
            'tanggal' => 'required|date',
            'piket' => 'nullable|string|max:100',
            'jenisterminal' => 'required|string|max:100',
            'namaterminal' => 'required|string|max:100',
            'kedatanganpnp' => 'required|integer|min:0',
            'keberangkatanpnp' => 'required|integer|min:0',
            'kedatanganangk' => 'required|integer|min:0',
            'keberangkatanangk' => 'required|integer|min:0',
            'koresponden' => 'required|string|max:100',
            'fotogiat' => 'nullable|string|max:255',
            'koordinat' => 'nullable|string|max:100',
        ];
    }

    protected function fotoFileValidationRule(bool $required): string
    {
        $rule = 'image|mimes:jpeg,png,jpg,gif,webp|max:8192';

        return $required ? 'required|'.$rule : 'nullable|'.$rule;
    }

    protected function mergeKoordinat(array $validated, Request $request): array
    {
        $lat = $request->input('lat');
        $lng = $request->input('lng');
        if ($lat !== null && $lat !== '' && $lng !== null && $lng !== '') {
            $validated['koordinat'] = mb_substr((string) $lat.','.(string) $lng, 0, 100);
        }

        return $validated;
    }

    public function store(Request $request)
    {
        $rules = $this->getValidationRules('store');
        unset($rules['fotogiat']);
        $rules['fotogiat_file'] = $this->fotoFileValidationRule(true);

        $validated = $request->validate($rules);
        $validated = $this->mergeKoordinat($validated, $request);

        $path = $request->file('fotogiat_file')->store('datapnp_giat', 'public');
        $validated['fotogiat'] = mb_substr('/storage/'.$path, 0, 255);

        $data = $this->prepareDataForStore($validated, $request);
        $data = $this->filterDataForExistingColumns($data);
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
        $validated = $this->mergeKoordinat($validated, $request);

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
        $data = $this->filterDataForExistingColumns($data);
        DB::table($this->tableName)->where($this->primaryKey, $id)->update($data);

        return redirect()->route($this->getRoutePrefix().'.index')
            ->with('success', 'Data berhasil diupdate.');
    }

    protected function jenisTerminalList()
    {
        if (Schema::hasTable('t_jenisterminal')) {
            return DB::table('t_jenisterminal')->orderBy('namejenisterminal')->pluck('namejenisterminal');
        }

        return collect(['Terminal BUS AKAP', 'Stasiun Kereta', 'Bandar Udara', 'Pelabuhan ASDP']);
    }

    /**
     * Buang kolom yang belum ada di tabel (mis. koordinat sebelum migrasi dijalankan).
     */
    protected function filterDataForExistingColumns(array $data): array
    {
        foreach (array_keys($data) as $column) {
            if (! Schema::hasColumn($this->tableName, $column)) {
                unset($data[$column]);
            }
        }

        return $data;
    }

    protected function getPageTitle()
    {
        return 'PNP Angkutan';
    }

    protected function getRoutePrefix()
    {
        return 'pnpangkutan';
    }
}
