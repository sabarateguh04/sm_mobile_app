<?php

namespace App\Http\Controllers;

use App\Helpers\HierarchicalHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * Layanan Masy — tabel pengunjungposyan (sbo).
 */
class PengunjungPosyanController extends LegacySboOperasionalController
{
    protected $tableName = 'pengunjungposyan';

    protected $primaryKey = 'id_pengunjungposyan';

    protected $viewFolder = 'layanan.masyarakat';

    protected $fillable = ['tanggal', 'piket', 'namaposyan', 'fotogiat', 'statusarus', 'katpengunjung', 'jmlhorang', 'kbmr2', 'kbmr4', 'keterangan', 'cppengunjung'];

    protected $searchFields = ['namaposyan', 'statusarus', 'katpengunjung', 'keterangan', 'cppengunjung'];

    protected $defaultOrderBy = 'tanggal';

    protected $defaultOrderType = 'desc';

    protected function applyFilters($query, Request $request)
    {
        $query->leftJoin('usm', 'pengunjungposyan.piket', '=', 'usm.id_usm')
            ->select('pengunjungposyan.*', 'usm.nama as usm_nama');

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
                foreach ($this->searchFields as $field) {
                    $q->orWhere('pengunjungposyan.'.$field, 'like', "%{$s}%");
                }
                $q->orWhere('usm.nama', 'like', "%{$s}%");
            });
        }
        $query->orderBy('pengunjungposyan.'.$request->get('orderby', $this->defaultOrderBy), $request->get('ordertype', $this->defaultOrderType));
        $data = $query->paginate($request->get('per_page', $this->perPage));

        return view($this->viewFolder.'.index', [
            'pageTitle' => $this->getPageTitle(),
            'data' => $data,
            'tableName' => $this->tableName,
            'primaryKey' => $this->primaryKey,
        ]);
    }

    public function show($id)
    {
        $data = DB::table($this->tableName)
            ->leftJoin('usm', 'pengunjungposyan.piket', '=', 'usm.id_usm')
            ->select('pengunjungposyan.*', 'usm.nama as usm_nama')
            ->where($this->tableName.'.'.$this->primaryKey, $id)->first();
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

    protected function getValidationRules($action = 'store')
    {
        return [
            'tanggal' => 'required|date',
            'piket' => 'nullable|string|max:200',
            'namaposyan' => 'required|string|max:200',
            'fotogiat' => 'nullable|string|max:255',
            'statusarus' => 'required|string|max:100',
            'katpengunjung' => 'required|string|max:100',
            'jmlhorang' => 'nullable|integer',
            'kbmr2' => 'required|integer',
            'kbmr4' => 'nullable|integer',
            'keterangan' => 'nullable|string|max:200',
            'cppengunjung' => 'nullable|string|max:100',
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

        $path = $request->file('fotogiat_file')->store('pengunjungposyan_giat', 'public');
        $validated['fotogiat'] = mb_substr('/storage/'.$path, 0, 255);

        $data = $this->prepareDataForStore($validated, $request);
        DB::table($this->tableName)->insertGetId($data);

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
            $path = $request->file('fotogiat_file')->store('pengunjungposyan_giat', 'public');
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
        return 'Layanan Masyarakat (Pengunjung Posyan)';
    }

    protected function getRoutePrefix()
    {
        return 'layanan.masyarakat';
    }
}
