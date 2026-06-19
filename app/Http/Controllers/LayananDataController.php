<?php

namespace App\Http\Controllers;

use App\Helpers\HierarchicalHelper;
use App\Support\ReferensiOptions;
use App\Support\PublicServiceLayanan;
use App\Support\RoleMutationPolicy;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

/**
 * Layanan Data — tabel layanan_data (permohonan data publik).
 */
class LayananDataController extends OperasionalController
{
    public function __construct()
    {
        parent::__construct();
        $this->middleware(function (Request $request, $next) {
            PublicServiceLayanan::captureSbGrpFromRequest($request);

            return $next($request);
        });
    }
    protected $tableName = 'layanan_data';

    protected $primaryKey = 'id_layanan_data';

    protected $viewFolder = 'layanan.data';

    protected $fillable = ['tanggal', 'kategori_data', 'tujuan', 'pemohon', 'telp_pemohon', 'keterangan', 'foto', 'koordinat'];

    protected $searchFields = ['kategori_data', 'tujuan', 'pemohon', 'telp_pemohon', 'keterangan'];

    protected $defaultOrderBy = 'tanggal';

    protected $defaultOrderType = 'desc';

    protected function applyFilters($query, Request $request)
    {
        $query->select('layanan_data.*');

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
                    $q->orWhere('layanan_data.'.$field, 'like', "%{$s}%");
                }
            });
        }

        // Filter range tanggal
        if ($request->filled('tanggal_dari')) {
            $query->whereDate('layanan_data.tanggal', '>=', $request->tanggal_dari);
        }
        if ($request->filled('tanggal_sampai')) {
            $query->whereDate('layanan_data.tanggal', '<=', $request->tanggal_sampai);
        }

        // Filter kategori
        if ($request->filled('kategori_data')) {
            $query->where('layanan_data.kategori_data', $request->kategori_data);
        }

        if (PublicServiceLayanan::isReportMode($request)) {
            $this->applyDirectDaerahWilayahFilters($query, $request);
        }

        $query->orderBy('layanan_data.'.$request->get('orderby', $this->defaultOrderBy), $request->get('ordertype', $this->defaultOrderType));
        $data = $query->paginate($request->get('per_page', $this->perPage))
            ->appends(PublicServiceLayanan::preserveIndexQuery($request));

        $readOnly = PublicServiceLayanan::isReportMode($request);
        $kategoriList = $this->getKategoriList();

        return view($this->viewFolder.'.index', [
            'pageTitle' => $readOnly ? PublicServiceLayanan::reportPageTitle($this->getPageTitle()) : $this->getPageTitle(),
            'data' => $data,
            'kategoriList' => $kategoriList,
            'readOnly' => $readOnly,
            'tableName' => $this->tableName,
            'primaryKey' => $this->primaryKey,
            'sboViewOnly' => RoleMutationPolicy::deniesAllMutations(Auth::user()),
            'daerahList' => $readOnly ? $this->daerahOptionsFromTable() : collect(),
            'wilayahList' => $readOnly ? $this->wilayahOptionsFromTable($request) : collect(),
            'userLevel' => HierarchicalHelper::getUserLevel(),
        ]);
    }

    public function create()
    {
        PublicServiceLayanan::assertWritable(request());
        $kategoriList = $this->getKategoriList();

        return view($this->viewFolder.'.create', [
            'pageTitle' => 'Tambah '.$this->getPageTitle(),
            'kategoriList' => $kategoriList,
        ]);
    }

    public function show($id)
    {
        $data = DB::table($this->tableName)
            ->where($this->primaryKey, $id)->first();

        if (! $data) {
            abort(404, 'Data tidak ditemukan');
        }

        return view($this->viewFolder.'.show', array_merge([
            'pageTitle' => 'Detail '.$this->getPageTitle(),
            'data' => $data,
            'readOnly' => PublicServiceLayanan::isReportMode(request()),
            'tableName' => $this->tableName,
            'primaryKey' => $this->primaryKey,
        ], $this->operationalRecordViewFlags($data), [
            'sboViewOnly' => RoleMutationPolicy::deniesAllMutations(Auth::user()),
        ]));
    }

    public function edit($id)
    {
        PublicServiceLayanan::assertWritable(request());
        $data = DB::table($this->tableName)->where($this->primaryKey, $id)->first();

        if (! $data) {
            abort(404, 'Data tidak ditemukan');
        }

        $kategoriList = $this->getKategoriList();

        return view($this->viewFolder.'.edit', [
            'pageTitle' => 'Edit '.$this->getPageTitle(),
            'data' => $data,
            'kategoriList' => $kategoriList,
        ]);
    }

    protected function getKategoriList()
    {
        return ReferensiOptions::collection(
            'katlayanandata',
            [],
            $this->tableName,
            'kategori_data'
        );
    }

    protected function getValidationRules($action = 'store')
    {
        return [
            'tanggal'       => 'required|date',
            'kategori_data' => 'nullable|string|max:100',
            'tujuan'        => 'nullable|string|max:200',
            'pemohon'       => 'nullable|string|max:200',
            'telp_pemohon'  => 'nullable|string|max:50',
            'keterangan'    => 'nullable|string',
            'koordinat'     => 'nullable|string|max:100',
        ];
    }

    public function store(Request $request)
    {
        PublicServiceLayanan::assertWritable($request);
        $rules = $this->getValidationRules('store');
        $rules['foto_file'] = 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:8192';
        $validated = $request->validate($rules);

        $data = $this->prepareDataForStore($validated, $request);

        if ($request->hasFile('foto_file')) {
            $path = $request->file('foto_file')->store('layanan_data_foto', 'public');
            $data['foto'] = mb_substr('/storage/'.$path, 0, 255);
        }

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
        $rules['foto_file'] = 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:8192';
        $validated = $request->validate($rules);

        $data = $this->prepareDataForUpdate($validated, $request);

        if ($request->hasFile('foto_file')) {
            $path = $request->file('foto_file')->store('layanan_data_foto', 'public');
            $data['foto'] = mb_substr('/storage/'.$path, 0, 255);
        } else {
            $data['foto'] = $row->foto;
        }

        DB::table($this->tableName)->where($this->primaryKey, $id)->update($data);

        return redirect()->route($this->getRoutePrefix().'.index')
            ->with('success', 'Data berhasil diupdate.');
    }

    public function destroy(string|int $id)
    {
        PublicServiceLayanan::assertWritable(request());

        return parent::destroy($id);
    }

    protected function prepareDataForStore(array $validated, Request $request)
    {
        $user = Auth::user();
        $data = [];
        foreach ($this->fillable as $f) {
            if (array_key_exists($f, $validated)) {
                $data[$f] = $validated[$f];
            }
        }
        $data['daerah']     = $user->daerah ?? null;
        $data['wilayah']    = $user->wilayah ?? null;
        $data['created_at'] = now();
        $data['updated_at'] = now();

        return $data;
    }

    protected function prepareDataForUpdate(array $validated, Request $request)
    {
        $data = [];
        foreach ($this->fillable as $f) {
            if (array_key_exists($f, $validated)) {
                $data[$f] = $validated[$f];
            }
        }
        $data['updated_at'] = now();

        return $data;
    }

    public static function fotoPublicUrl(?string $path): ?string
    {
        if ($path === null || trim($path) === '') {
            return null;
        }

        $v = trim($path);
        if (preg_match('#^https?://#i', $v)) {
            return $v;
        }

        if (str_starts_with($v, '/storage/') || str_starts_with($v, 'storage/')) {
            return asset(ltrim($v, '/'));
        }

        /** @var \Illuminate\Filesystem\FilesystemAdapter $disk */
        $disk = Storage::disk('public');

        return $disk->url(ltrim($v, '/'));
    }

    protected function getPageTitle()
    {
        return 'Layanan Data';
    }

    protected function getRoutePrefix()
    {
        return 'layanan.data';
    }
}
