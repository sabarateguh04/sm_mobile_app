<?php

namespace App\Http\Controllers;

use App\Helpers\HierarchicalHelper;
use App\Support\ReferensiOptions;
use App\Support\PublicServiceLayanan;
use App\Support\LayananToOpsdalSync;
use App\Support\RoleMutationPolicy;
use App\Support\TerritoryIndexFilters;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;

/**
 * Layanan Masy — tabel layanan_masyarakat (legacy: interaksi).
 */
class InteraksiLayananController extends OperasionalController
{
    public function __construct()
    {
        parent::__construct();
        $this->middleware(function (Request $request, $next) {
            PublicServiceLayanan::captureSbGrpFromRequest($request);

            return $next($request);
        });
    }
    protected $tableName = 'layanan_masyarakat';
 
    protected $primaryKey = 'id_interaksi';

    protected $viewFolder = 'layanan.masyarakat';

    protected $fillable = ['tanggal', 'saluran', 'kategori', 'tema', 'narsum', 'telp_narsum', 'keterangan', 'foto', 'koordinat'];

    protected $searchFields = ['saluran', 'kategori', 'tema', 'narsum', 'keterangan'];

    protected $defaultOrderBy = 'tanggal';

    protected $defaultOrderType = 'desc';

    protected function applyFilters($query, Request $request)
    {
        $query->select('layanan_masyarakat.*', 'usm.nama as nama_petugas');
        if (\Illuminate\Support\Facades\Schema::hasColumn('layanan_masyarakat', 'id_usm')) {
            $query->leftJoin('usm', 'layanan_masyarakat.id_usm', '=', 'usm.id_usm');
        } elseif (\Illuminate\Support\Facades\Schema::hasColumn('layanan_masyarakat', 'piket')) {
            $query->leftJoin('usm', 'layanan_masyarakat.piket', '=', 'usm.id_usm');
        }

        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(function ($q) use ($s) {
                foreach ($this->searchFields as $field) {
                    $q->orWhere('layanan_masyarakat.'.$field, 'like', "%{$s}%");
                }
                $q->orWhere('usm.nama', 'like', "%{$s}%");
            });
        }

        if ($request->filled('tanggal_dari')) {
            $query->whereDate('layanan_masyarakat.tanggal', '>=', $request->tanggal_dari);
        }
        if ($request->filled('tanggal_sampai')) {
            $query->whereDate('layanan_masyarakat.tanggal', '<=', $request->tanggal_sampai);
        }
        if ($request->filled('kategori')) {
            $query->where('layanan_masyarakat.kategori', $request->kategori);
        }

        // Apply territory column filters for report mode
        if (PublicServiceLayanan::isReportMode($request)) {
            TerritoryIndexFilters::applyColumnFilters($query, $request, $this->tableName);
            TerritoryIndexFilters::applyUsmFilters($query, $request);
        }

        return $query;
    }

    public function index(Request $request)
    {
        $query = DB::table($this->tableName);
        $query = $this->applyFilters($query, $request);
        HierarchicalHelper::applyTerritoryScopeForTable($query, $this->tableName);

        $query->orderBy('layanan_masyarakat.'.$request->get('orderby', $this->defaultOrderBy), $request->get('ordertype', $this->defaultOrderType));
        $data = $query->paginate($request->get('per_page', $this->perPage))
            ->appends(PublicServiceLayanan::preserveIndexQuery($request));

        $readOnly = PublicServiceLayanan::isReportMode($request);
        $kategoriList = $this->getKategoriList();

        return view($this->viewFolder.'.index', array_merge([
            'pageTitle' => $readOnly ? PublicServiceLayanan::reportPageTitle($this->getPageTitle()) : $this->getPageTitle(),
            'data' => $data,
            'kategoriList' => $kategoriList,
            'readOnly' => $readOnly,
            'tableName' => $this->tableName,
            'primaryKey' => $this->primaryKey,
            'sboViewOnly' => RoleMutationPolicy::deniesAllMutations(Auth::user()),
        ], $readOnly ? TerritoryIndexFilters::viewBundle($request, $this->tableName) : []));
    }

    public function create()
    {
        PublicServiceLayanan::assertWritable(request());
        if (in_array(auth()->user()->user_role_id, [1, 2, 3, 8])) {
            abort(403, 'Admin tidak diizinkan menambah data.');
        }

        return view($this->viewFolder.'.create', [
            'pageTitle'    => 'Tambah '.$this->getPageTitle(),
            'saluranList'  => $this->getSaluranList(),
            'kategoriList' => $this->getKategoriList(),
            'temaList'     => $this->getTemaList(),
            'cascadeMap'   => $this->getCascadeMap(),
        ]);
    }

    public function show($id)
    {
        $data = $this->findOperationalRowOrFail($id);
        $readOnly = PublicServiceLayanan::isReportMode(request());
        $baseTitle = $readOnly
            ? PublicServiceLayanan::reportPageTitle($this->getPageTitle())
            : $this->getPageTitle();

        return view($this->viewFolder.'.show', array_merge([
            'pageTitle' => 'Detail '.$baseTitle,
            'data' => $data,
            'readOnly' => $readOnly,
            'tableName' => $this->tableName,
        ], $this->operationalRecordViewFlags($data), [
            'sboViewOnly' => RoleMutationPolicy::deniesAllMutations(Auth::user()),
        ]));
    }

    public function edit($id)
    {
        PublicServiceLayanan::assertWritable(request());
        $data = $this->findOperationalRowOrFail($id);
        RoleMutationPolicy::assertCanEditOrDelete(Auth::user(), $data, $this->tableName);

        return view($this->viewFolder.'.edit', [
            'pageTitle'    => 'Edit '.$this->getPageTitle(),
            'data'         => $data,
            'saluranList'  => $this->getSaluranList($data->saluran ?? null),
            'kategoriList' => $this->getKategoriList($data->kategori ?? null),
            'temaList'     => $this->getTemaList($data->tema ?? null),
            'cascadeMap'   => $this->getCascadeMap(),
        ]);
    }

    protected function getKategoriList(?string $current = null)
    {
        $list = ReferensiOptions::collection('katlayanan');

        return $this->mergeCurrentOption($list, $current);
    }

    protected function getSaluranList(?string $current = null)
    {
        $list = ReferensiOptions::collection(
            'saluran',
            ['Call Center 110'],
            $this->tableName,
            'saluran'
        );

        return $this->mergeCurrentOption($list, $current);
    }

    protected function getTemaList(?string $current = null)
    {
        $list = ReferensiOptions::collection(
            'temalayanan',
            [],
            $this->tableName,
            'tema'
        );

        return $this->mergeCurrentOption($list, $current);
    }

    /**
     * Build cascade map untuk client-side filtering:
     *   kategoriTema: { "ADUAN": ["..."], ... }
     *
     * Kategori independen dari saluran; tema mengikuti kategori (master temalayanan).
     *
     * @return array{saluranKategori: array<string,list<string>>, kategoriTema: array<string,list<string>>}
     */
    protected function getCascadeMap(): array
    {
        $kategoriTema = [];
        if (Schema::hasTable('t_temalayanan') && Schema::hasColumn('t_temalayanan', 'kategori')) {
            $rows = DB::table('t_temalayanan')
                ->whereNotNull('kategori')
                ->where('kategori', '!=', '')
                ->whereNotNull('nametema')
                ->where('nametema', '!=', '')
                ->orderBy('nametema')
                ->get(['kategori', 'nametema']);

            foreach ($rows as $row) {
                $kategoriTema[$row->kategori][] = $row->nametema;
            }
        }

        return [
            'saluranKategori' => [],
            'kategoriTema' => $kategoriTema,
        ];
    }

    protected function mergeCurrentOption($list, ?string $current)
    {
        $current = trim((string) $current);
        if ($current !== '' && ! $list->contains($current)) {
            $list = $list->prepend($current)->values();
        }

        return $list;
    }

    protected function getValidationRules($action = 'store')
    {
        return [
            'tanggal'     => 'required|date',
            'saluran'     => 'nullable|string|max:100',
            'kategori'    => 'nullable|string|max:100',
            'tema'        => 'nullable|string|max:200',
            'narsum'      => 'nullable|string|max:200',
            'telp_narsum' => 'nullable|string|max:50',
            'keterangan'  => 'nullable|string',
            'koordinat'   => 'nullable|string|max:100',
        ];
    }

    public function store(Request $request)
    {
        PublicServiceLayanan::assertWritable($request);
        if (in_array(auth()->user()->user_role_id, [1, 2, 3, 8])) {
            abort(403, 'Admin tidak diizinkan menambah data.');
        }
        $rules = $this->getValidationRules('store');
        $rules['foto_file'] = 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:8192';
        $validated = $request->validate($rules);

        $data = $this->prepareDataForStore($validated, $request);

        if ($request->hasFile('foto_file')) {
            $path = $request->file('foto_file')->store('interaksi_foto', 'public');
            $data['foto'] = mb_substr('/storage/'.$path, 0, 255);
        }

        $id = (int) DB::table($this->tableName)->insertGetId($data);
        LayananToOpsdalSync::syncFromLayananId($id);

        return redirect()->route($this->getRoutePrefix().'.index')
            ->with('success', 'Data berhasil ditambahkan.');
    }

    public function update(Request $request, string|int $id)
    {
        PublicServiceLayanan::assertWritable($request);
        $row = $this->findOperationalRowOrFail($id);
        RoleMutationPolicy::assertCanEditOrDelete(Auth::user(), $row, $this->tableName);

        $rules = $this->getValidationRules('update');
        $rules['foto_file'] = 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:8192';
        $validated = $request->validate($rules);

        $data = $this->prepareDataForUpdate($validated, $request);

        if ($request->hasFile('foto_file')) {
            $path = $request->file('foto_file')->store('interaksi_foto', 'public');
            $data['foto'] = mb_substr('/storage/'.$path, 0, 255);
        } else {
            $data['foto'] = $row->foto;
        }

        DB::table($this->tableName)->where($this->primaryKey, $id)->update($data);
        LayananToOpsdalSync::syncFromLayananId((int) $id);

        return redirect()->route($this->getRoutePrefix().'.index')
            ->with('success', 'Data berhasil diupdate.');
    }

    public function destroy(string|int $id)
    {
        PublicServiceLayanan::assertWritable(request());

        $existing = $this->findOperationalRowOrFail($id);
        RoleMutationPolicy::assertCanEditOrDelete(Auth::user(), $existing, $this->tableName);

        DB::table($this->tableName)->where($this->primaryKey, $id)->delete();

        return redirect()->route($this->getRoutePrefix().'.index')
            ->with('success', 'Data berhasil dihapus.');
    }

    public function export(Request $request, $format = 'excel')
    {
        $query = DB::table($this->tableName);
        $query = $this->applyFilters($query, $request);
        HierarchicalHelper::applyTerritoryScopeForTable($query, $this->tableName);

        if ($request->filled('id') && $this->primaryKey) {
            $query->where($this->tableName.'.'.$this->primaryKey, $request->id);
        }

        $query->orderBy(
            'layanan_masyarakat.'.$request->get('orderby', $this->defaultOrderBy),
            $request->get('ordertype', $this->defaultOrderType)
        );

        $data = $query->get();
        $isSingle = $request->filled('id') && $data->count() === 1;
        $filename = $request->get('filename', $isSingle
            ? 'layanan_masyarakat_'.$request->id.'_'.date('Y-m-d')
            : 'layanan_masyarakat_'.date('Y-m-d'));
        $baseTitle = PublicServiceLayanan::isReportMode($request)
            ? PublicServiceLayanan::reportPageTitle($this->getPageTitle())
            : $this->getPageTitle().' Report';
        $title = $isSingle ? 'Detail '.$baseTitle : $baseTitle;

        return match (strtolower((string) $format)) {
            'csv' => $this->exportToCsv($data, $filename),
            'pdf' => $this->exportToPdf($data, 'exports.pdf.default', $filename, $title),
            default => $this->exportToExcel($data, $filename, $title),
        };
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
        $data['daerah'] = $user->daerah ?? null;
        $data['wilayah'] = $user->wilayah ?? null;
        $data['created_at'] = now();
        $data['updated_at'] = now();

        if (Schema::hasColumn($this->tableName, 'id_usm')) {
            $actorId = RoleMutationPolicy::resolveActorUserId($user);
            if ($actorId <= 0) {
                abort(403, 'Akun tidak valid — tidak dapat mencatat pemilik data.');
            }
            $data['id_usm'] = $actorId;
        }

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
        return 'Layanan Masyarakat (Interaksi)';
    }

    protected function getRoutePrefix()
    {
        return 'layanan.masyarakat';
    }
}
