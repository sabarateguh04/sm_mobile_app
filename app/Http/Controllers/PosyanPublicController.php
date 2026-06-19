<?php

namespace App\Http\Controllers;

use App\Helpers\HierarchicalHelper;
use App\Support\PublicServiceLayanan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * Public Service — tabel pos_pelayanan (legacy: posyan).
 */
class PosyanPublicController extends LegacySboOperasionalController
{
    public function __construct()
    {
        parent::__construct();
        $this->middleware(function (Request $request, $next) {
            PublicServiceLayanan::captureSbGrpFromRequest($request);

            return $next($request);
        });
    }
    protected $tableName = 'pos_pelayanan';

    protected $primaryKey = 'id_posyan';

    protected $viewFolder = 'layanan.public_service';

    protected $fillable = ['tanggal', 'piket', 'jenisops', 'nameops', 'sasaranops', 'namaposyan', 'lokasiposyan', 'koordinat', 'sarprasposyan', 'fotoposyan', 'tahunops'];

    protected $searchFields = ['namaposyan', 'lokasiposyan', 'sasaranops', 'nameops', 'jenisops', 'tahunops'];

    protected $defaultOrderBy = 'tanggal';

    protected $defaultOrderType = 'desc';

    protected function applyFilters($query, Request $request)
    {
        $query->leftJoin('usm', 'pos_pelayanan.piket', '=', 'usm.id_usm')
            ->select('pos_pelayanan.*', 'usm.nama as usm_nama', 'usm.daerah as usm_daerah', 'usm.wilayah as usm_wilayah');

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
                    $q->orWhere('pos_pelayanan.'.$field, 'like', "%{$s}%");
                }
                $q->orWhere('usm.nama', 'like', "%{$s}%");
            });
        }
        $query->orderBy('pos_pelayanan.'.$request->get('orderby', $this->defaultOrderBy), $request->get('ordertype', $this->defaultOrderType));
        $data = $query->paginate($request->get('per_page', $this->perPage))
            ->appends(PublicServiceLayanan::preserveIndexQuery($request));

        $readOnly = PublicServiceLayanan::isReportMode($request);

        return view($this->viewFolder.'.index', [
            'pageTitle' => $readOnly ? PublicServiceLayanan::reportPageTitle($this->getPageTitle()) : $this->getPageTitle(),
            'data' => $data,
            'readOnly' => $readOnly,
            'tableName' => $this->tableName,
            'primaryKey' => $this->primaryKey,
        ]);
    }

    public function create()
    {
        PublicServiceLayanan::assertWritable(request());

        return view($this->viewFolder.'.create', [
            'pageTitle' => 'Tambah '.$this->getPageTitle(),
        ]);
    }

    public function show($id)
    {
        $data = DB::table($this->tableName)
            ->leftJoin('usm', 'pos_pelayanan.piket', '=', 'usm.id_usm')
            ->select('pos_pelayanan.*', 'usm.nama as usm_nama', 'usm.daerah as usm_daerah', 'usm.wilayah as usm_wilayah')
            ->where('pos_pelayanan.'.$this->primaryKey, $id)->first();
        if (! $data) {
            abort(404, 'Data tidak ditemukan');
        }

        return view($this->viewFolder.'.show', [
            'pageTitle' => 'Detail '.$this->getPageTitle(),
            'data' => $data,
            'readOnly' => PublicServiceLayanan::isReportMode(request()),
            'tableName' => $this->tableName,
            'primaryKey' => $this->primaryKey,
        ]);
    }

    public function edit(string|int $id)
    {
        PublicServiceLayanan::assertWritable(request());

        $data = DB::table($this->tableName)->where($this->primaryKey, $id)->first();
        if (! $data) {
            abort(404, 'Data tidak ditemukan');
        }

        return view($this->viewFolder.'.edit', [
            'pageTitle' => 'Edit '.$this->getPageTitle(),
            'data' => $data,
        ]);
    }

    protected function getValidationRules($action = 'store')
    {
        return [
            'tanggal' => 'nullable|date',
            'piket' => 'nullable|string|max:100',
            'jenisops' => 'nullable|string|max:100',
            'nameops' => 'nullable|string|max:100',
            'sasaranops' => 'required|string|max:100',
            'namaposyan' => 'required|string|max:100',
            'lokasiposyan' => 'required|string|max:100',
            'koordinat' => 'nullable|string|max:100',
            'sarprasposyan' => 'required|string|max:255',
            'fotoposyan' => 'nullable|string|max:255',
            'tahunops' => 'required|string|max:100',
        ];
    }

    protected function fotoFileValidationRule(bool $required): string
    {
        $rule = 'image|mimes:jpeg,png,jpg,gif,webp|max:8192';

        return $required ? 'required|'.$rule : 'nullable|'.$rule;
    }

    public function store(Request $request)
    {
        PublicServiceLayanan::assertWritable($request);
        $rules = $this->getValidationRules('store');
        unset($rules['fotoposyan']);
        $rules['fotoposyan_file'] = $this->fotoFileValidationRule(true);

        $validated = $request->validate($rules);

        $path = $request->file('fotoposyan_file')->store('posyan_giat', 'public');
        $validated['fotoposyan'] = mb_substr('/storage/'.$path, 0, 255);

        $data = $this->prepareDataForStore($validated, $request);
        DB::table($this->tableName)->insertGetId($data);

        return redirect()->route($this->getRoutePrefix().'.index')
            ->with('success', 'Data berhasil ditambahkan.');
    }

    public function update(Request $request, string|int $id)
    {
        PublicServiceLayanan::assertWritable($request);
        $row = DB::table($this->tableName)->where($this->primaryKey, $id)->first();
        if (! $row) {
            abort(404, 'Data tidak ditemukan');
        }

        $this->assertOperatorOwnsOperationalRow($row);

        $rules = $this->getValidationRules('update');
        unset($rules['fotoposyan']);
        $rules['fotoposyan_file'] = $this->fotoFileValidationRule(false);

        $validated = $request->validate($rules);

        if ($request->hasFile('fotoposyan_file')) {
            $path = $request->file('fotoposyan_file')->store('posyan_giat', 'public');
            $validated['fotoposyan'] = mb_substr('/storage/'.$path, 0, 255);
        } else {
            $validated['fotoposyan'] = (string) ($row->fotoposyan ?? '');
        }
        if (trim((string) $validated['fotoposyan']) === '') {
            throw ValidationException::withMessages([
                'fotoposyan_file' => ['Foto posyan wajib ada. Unggah foto terlebih dahulu.'],
            ]);
        }

        $data = $this->prepareDataForUpdate($validated, $request);
        DB::table($this->tableName)->where($this->primaryKey, $id)->update($data);

        return redirect()->route($this->getRoutePrefix().'.index')
            ->with('success', 'Data berhasil diupdate.');
    }

    public function destroy(string|int $id)
    {
        PublicServiceLayanan::assertWritable(request());

        return parent::destroy($id);
    }

    protected function getPageTitle()
    {
        return 'Public Service (Pos Pelayanan)';
    }

    protected function getRoutePrefix()
    {
        return 'layanan.public_service';
    }
}
