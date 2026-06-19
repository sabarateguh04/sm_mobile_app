<?php

namespace App\Http\Controllers;

use App\Helpers\HierarchicalHelper;
use App\Support\ReferensiOptions;
use App\Support\RoleMutationPolicy;
use App\Support\TerritoryIndexFilters;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
/**
 * Media Publishing — publikasi konten (tabel mediapublishing).
 */
class MediaPublishingController extends LegacySboOperasionalController
{
    protected $tableName = 'mediapublishing';

    protected $primaryKey = 'id_mediapublishing';

    protected $viewFolder = 'media.manage';

    protected $fillable = [
        'piket', 'tanggal', 'daerah', 'wilayah', 'kategori_konten', 'tema',
        'metode', 'media_publishing', 'links', 'fotodoc',
    ];

    protected $searchFields = [
        'kategori_konten', 'tema', 'metode', 'media_publishing', 'links', 'daerah', 'wilayah',
    ];

    protected $defaultOrderBy = 'tanggal';

    protected $defaultOrderType = 'desc';

    public function __construct()
    {
        parent::__construct();

        $this->middleware(function (Request $request, $next) {
            $this->ensureTableExists();

            return $next($request);
        });
    }

    protected function ensureTableExists(): void
    {
        if (Schema::hasTable($this->tableName)) {
            return;
        }

        Schema::create($this->tableName, function (Blueprint $table) {
            $table->increments('id_mediapublishing');
            $table->unsignedInteger('piket')->nullable();
            $table->date('tanggal');
            $table->string('daerah', 100)->nullable();
            $table->string('wilayah', 100)->nullable();
            $table->string('kategori_konten', 100)->nullable();
            $table->string('tema', 255)->nullable();
            $table->string('metode', 100)->nullable();
            $table->string('media_publishing', 100)->nullable();
            $table->string('links', 500)->nullable();
            $table->string('fotodoc', 500)->nullable();
        });
    }

    protected function applyFilters($query, Request $request)
    {
        $query->leftJoin('usm', $this->tableName.'.piket', '=', 'usm.id_usm')
            ->select($this->tableName.'.*', 'usm.nama as usm_nama');

        if ($request->filled('tanggal_dari')) {
            $query->where($this->tableName.'.tanggal', '>=', $request->tanggal_dari);
        }
        if ($request->filled('tanggal_sampai')) {
            $query->where($this->tableName.'.tanggal', '<=', $request->tanggal_sampai);
        }
        if ($request->filled('kategori_konten')) {
            $query->where($this->tableName.'.kategori_konten', $request->kategori_konten);
        }
        if ($request->filled('media_publishing')) {
            $query->where($this->tableName.'.media_publishing', $request->media_publishing);
        }

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
                    $q->orWhere($this->tableName.'.'.$field, 'like', "%{$s}%");
                }
                $q->orWhere('usm.nama', 'like', "%{$s}%");
            });
        }

        TerritoryIndexFilters::applyColumnFilters($query, $request, $this->tableName);

        $query->orderBy(
            $this->tableName.'.'.$request->get('orderby', $this->defaultOrderBy),
            $request->get('ordertype', $this->defaultOrderType)
        );

        $data = $query->paginate($request->get('per_page', $this->perPage))
            ->appends($request->only(['search', 'tanggal_dari', 'tanggal_sampai', 'daerah', 'wilayah', 'kategori_konten', 'media_publishing']));

        return view($this->viewFolder.'.index', array_merge([
            'pageTitle' => 'List Publishing',
            'data' => $data,
            'tableName' => $this->tableName,
            'primaryKey' => $this->primaryKey,
            'sboViewOnly' => RoleMutationPolicy::deniesAllMutations(Auth::user()),
        ], $this->selectOptions(), TerritoryIndexFilters::viewBundle($request)));
    }

    public function create()
    {
        return view($this->viewFolder.'.create', array_merge(
            $this->formViewData('Tambah Media Publishing'),
            $this->selectOptions()
        ));
    }

    public function store(Request $request)
    {
        $validated = $request->validate($this->getValidationRules('store'));

        if ($request->hasFile('fotodoc_file')) {
            $path = $request->file('fotodoc_file')->store('mediapublishing', 'public');
            $validated['fotodoc'] = mb_substr('/storage/'.$path, 0, 500);
        }

        $data = $this->prepareDataForStore($validated, $request);
        $this->applyTerritoryOnStore($data);

        DB::table($this->tableName)->insertGetId($data, $this->primaryKey);

        return redirect()->route($this->getRoutePrefix().'.index')
            ->with('success', 'Data berhasil ditambahkan.');
    }

    public function show($id)
    {
        $data = DB::table($this->tableName)
            ->leftJoin('usm', $this->tableName.'.piket', '=', 'usm.id_usm')
            ->select($this->tableName.'.*', 'usm.nama as usm_nama')
            ->where($this->tableName.'.'.$this->primaryKey, $id)
            ->first();

        if (! $data) {
            abort(404);
        }

        return view($this->viewFolder.'.show', array_merge([
            'pageTitle' => 'Detail Media Publishing',
            'data' => $data,
            'primaryKey' => $this->primaryKey,
            'tableName' => $this->tableName,
        ], $this->operationalRecordViewFlags($data), [
            'sboViewOnly' => RoleMutationPolicy::deniesAllMutations(Auth::user()),
        ]));
    }

    public function edit($id)
    {
        $data = DB::table($this->tableName)->where($this->primaryKey, $id)->first();
        if (! $data) {
            abort(404);
        }

        $this->assertOperatorOwnsOperationalRow($data);

        return view($this->viewFolder.'.edit', array_merge(
            $this->formViewData('Edit Media Publishing', $data),
            $this->selectOptions()
        ));
    }

    public function update(Request $request, string|int $id)
    {
        $row = DB::table($this->tableName)->where($this->primaryKey, $id)->first();
        if (! $row) {
            abort(404, 'Data tidak ditemukan');
        }

        $this->assertOperatorOwnsOperationalRow($row);

        $validated = $request->validate($this->getValidationRules('update'));

        if ($request->hasFile('fotodoc_file')) {
            $path = $request->file('fotodoc_file')->store('mediapublishing', 'public');
            $validated['fotodoc'] = mb_substr('/storage/'.$path, 0, 500);
        } else {
            $validated['fotodoc'] = (string) ($row->fotodoc ?? '');
        }

        $data = $this->prepareDataForUpdate($validated, $request);

        DB::table($this->tableName)->where($this->primaryKey, $id)->update($data);

        return redirect()->route($this->getRoutePrefix().'.index')
            ->with('success', 'Data berhasil diupdate.');
    }

    public function export(Request $request, $format = 'excel')
    {
        $query = DB::table($this->tableName);
        $query = $this->applyFilters($query, $request);
        HierarchicalHelper::applyTerritoryScopeForTable($query, $this->tableName);

        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(function ($q) use ($s) {
                foreach ($this->searchFields as $field) {
                    $q->orWhere($this->tableName.'.'.$field, 'like', "%{$s}%");
                }
            });
        }

        TerritoryIndexFilters::applyColumnFilters($query, $request, $this->tableName);

        return parent::export($request, $format);
    }

    protected function getValidationRules($action = 'store'): array
    {
        $fotoRule = $action === 'store'
            ? 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:8192'
            : 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:8192';

        return [
            'tanggal' => 'required|date',
            'kategori_konten' => 'nullable|string|max:100',
            'tema' => 'nullable|string|max:255',
            'metode' => 'nullable|string|max:100',
            'media_publishing' => 'nullable|string|max:100',
            'links' => 'nullable|string|max:500',
            'fotodoc_file' => $fotoRule,
        ];
    }

    /**
     * @param  array<string, mixed>  $data
     */
    protected function applyTerritoryOnStore(array &$data): void
    {
        $user = Auth::user();
        if (! $user) {
            return;
        }

        if (empty($data['daerah']) && ! empty($user->daerah)) {
            $data['daerah'] = (string) $user->daerah;
        }
        if (empty($data['wilayah']) && ! empty($user->wilayah)) {
            $data['wilayah'] = (string) $user->wilayah;
        }
    }

    /**
     * @return array<string, mixed>
     */
    protected function formViewData(string $pageTitle, ?object $data = null): array
    {
        $user = Auth::user();

        return [
            'pageTitle' => $pageTitle,
            'data' => $data,
            'primaryKey' => $this->primaryKey,
            'territory' => [
                'provinsi' => $data->daerah ?? $user->daerah ?? '',
                'kab_kota' => $data->wilayah ?? $user->wilayah ?? '',
            ],
        ];
    }

    /**
     * @return array<string, list<string>>
     */
    protected function selectOptions(): array
    {
        return [
            'kategoriOptions' => ReferensiOptions::list('kategorikonten', ['Publikasi', 'Informasi', 'Edukasi', 'Sosialisasi', 'Lainnya'], $this->tableName, 'kategori_konten'),
            'metodeOptions' => ReferensiOptions::list('metodemedia', ['Info Graphic', 'Video', 'Artikel', 'Press Release', 'Podcast', 'Lainnya'], $this->tableName, 'metode'),
            'mediaPublishingOptions' => ReferensiOptions::list('mediapublishing', ['Media Offline', 'Media Online', 'Media Sosial', 'Website', 'Lainnya'], $this->tableName, 'media_publishing'),
        ];
    }

    protected function getPageTitle(): string
    {
        return 'Media Publishing';
    }

    protected function getRoutePrefix(): string
    {
        return 'media.manage';
    }
}
