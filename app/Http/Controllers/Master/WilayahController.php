<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Support\TerritoryMaster;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class WilayahController extends Controller
{
    protected function usesMasterKabkotOnly(): bool
    {
        return Schema::hasTable('master_provinsi')
            && Schema::hasTable('master_kabupaten_kota')
            && ! (Schema::hasTable('daerah') && Schema::hasTable('wilayah'));
    }

    protected function normalizeWilayahName(string $name): string
    {
        return TerritoryMaster::stripKabupatenKotaFromName(mb_strtoupper(trim($name), 'UTF-8'));
    }

    protected function nextManualKodeKabupatenKota(int $masterProvinsiId): string
    {
        $maxId = (int) DB::table('master_kabupaten_kota')->max('id');

        return 'MAN-'.$masterProvinsiId.'-'.str_pad((string) ($maxId + 1), 5, '0', STR_PAD_LEFT);
    }

    protected function nextMasterKabupatenKotaId(): int
    {
        return ((int) DB::table('master_kabupaten_kota')->max('id')) + 1;
    }

    /**
     * @return object{id_wilayah: int|string, namawilayah: string, id_daerah: int|string, namadaerah: string, jenis_wilayah: ?string}|null
     */
    protected function findWilayahRow($id): ?object
    {
        if ($this->usesMasterKabkotOnly()) {
            $row = DB::table('master_kabupaten_kota as w')
                ->join('master_provinsi as d', 'd.id', '=', 'w.master_provinsi_id')
                ->where('w.id', $id)
                ->first(['w.id as id_wilayah', 'w.nama as namawilayah', 'd.id as id_daerah', 'd.nama as namadaerah', 'w.jenis as jenis_wilayah', 'w.lat', 'w.lng']);

            return $row ? (object) (array) $row : null;
        }

        if (Schema::hasTable('wilayah') && Schema::hasTable('daerah')) {
            $select = ['w.id_wilayah', 'w.namawilayah', 'd.namadaerah', 'd.id_daerah as id_daerah', 'w.lat', 'w.lng'];
            if (Schema::hasColumn('wilayah', 'jenis_wilayah')) {
                $select[] = 'w.jenis_wilayah';
            }

            if (TerritoryMaster::wilayahIdColumnIsNumericDaerahFk()) {
                $row = DB::table('wilayah as w')
                    ->join('daerah as d', 'd.id_daerah', '=', 'w.id_daerah')
                    ->where('w.id_wilayah', $id)
                    ->select($select)
                    ->first();
            } else {
                $row = DB::table('wilayah as w')
                    ->leftJoin('daerah as d', 'd.namadaerah', '=', 'w.id_daerah')
                    ->where('w.id_wilayah', $id)
                    ->select($select)
                    ->first();
            }

            return $row ? (object) (array) $row : null;
        }

        return null;
    }

    /**
     * @return list<object{id_daerah: int|string, namadaerah: string}>
     */
    protected function daerahOptionsForForms(): array
    {
        if ($this->usesMasterKabkotOnly()) {
            return DB::table('master_provinsi')
                ->select(['id as id_daerah', 'nama as namadaerah'])
                ->orderBy('nama')
                ->get()
                ->all();
        }

        if (Schema::hasTable('daerah')) {
            return DB::table('daerah')->orderBy('namadaerah')->get()->all();
        }

        return [];
    }

    protected function resolveLegacyWilayahIdDaerahValue($idDaerahFromForm): mixed
    {
        if (! Schema::hasTable('daerah')) {
            return $idDaerahFromForm;
        }

        if (TerritoryMaster::wilayahIdColumnIsNumericDaerahFk()) {
            return $idDaerahFromForm;
        }

        $row = DB::table('daerah')->where('id_daerah', $idDaerahFromForm)->value('namadaerah');

        return $row ?? $idDaerahFromForm;
    }

    public function index(Request $request)
    {
        $perPage = max(1, (int) $request->get('per_page', 10));

        $useLegacy = Schema::hasTable('daerah') && Schema::hasTable('wilayah');
        $useMaster = Schema::hasTable('master_provinsi') && Schema::hasTable('master_kabupaten_kota');

        if ($useLegacy) {
            return $this->indexLegacy($request, $perPage);
        }

        if ($useMaster) {
            return $this->indexFromMasterKabkot($request, $perPage);
        }

        $daerah = new Collection([]);
        $resort = (new LengthAwarePaginator([], 0, $perPage, 1, [
            'path' => $request->url(),
            'pageName' => 'page',
        ]))->appends($request->query());

        return view('master.resort.index', compact('daerah', 'resort'));
    }

    private function indexLegacy(Request $request, int $perPage)
    {
        $actor = \Illuminate\Support\Facades\Auth::user();
        $isBypassed = \App\Helpers\HierarchicalHelper::viewerBypassesTerritoryScope();
        $userDaerah = $actor->daerah ?? '';
        $userWilayah = $actor->wilayah ?? '';

        $daerahQuery = DB::table('daerah')->orderBy('namadaerah');
        if (!$isBypassed && !empty($userDaerah)) {
            $daerahQuery->where('namadaerah', $userDaerah);
        }
        $daerah = $daerahQuery->get();

        $wilayahQ = $this->wilayahBaseQuery();

        if (!$isBypassed) {
            if (!empty($userDaerah)) {
                $wilayahQ->where('d.namadaerah', $userDaerah);
            }
            if (!empty($userWilayah)) {
                $wilayahQ->where('w.namawilayah', $userWilayah);
            }
        }

        if ($request->filled('search')) {
            $s = $request->search;
            $wilayahQ->where(function ($q) use ($s) {
                $q->where('w.namawilayah', 'like', "%{$s}%")
                    ->orWhere('d.namadaerah', 'like', "%{$s}%")
                    ->orWhere('w.id_daerah', 'like', "%{$s}%");
                if (Schema::hasColumn('wilayah', 'kode_kabupaten_kota')) {
                    $q->orWhere('w.kode_kabupaten_kota', 'like', "%{$s}%");
                }
            });
        }

        if ($request->filled('id_daerah')) {
            if ($request->id_daerah === 'unassigned') {
                $wilayahQ->whereNull('w.id_daerah')->orWhere('w.id_daerah', '')->orWhere('w.id_daerah', '0');
            } else {
                $daerahRow = DB::table('daerah')->where('id_daerah', $request->id_daerah)->first(['id_daerah', 'namadaerah']);
                if ($daerahRow !== null) {
                    if (TerritoryMaster::wilayahIdColumnIsNumericDaerahFk()) {
                        $wilayahQ->where('w.id_daerah', $daerahRow->id_daerah);
                    } else {
                        $wilayahQ->where('w.id_daerah', $daerahRow->namadaerah);
                    }
                }
            }
        }

        $resort = $wilayahQ->orderBy('d.namadaerah')->orderBy('w.namawilayah')
            ->paginate($perPage)->withQueryString();

        return view('master.resort.index', compact('daerah', 'resort'));
    }

    private function indexFromMasterKabkot(Request $request, int $perPage)
    {
        $actor = \Illuminate\Support\Facades\Auth::user();
        $isBypassed = \App\Helpers\HierarchicalHelper::viewerBypassesTerritoryScope();
        $userDaerah = $actor->daerah ?? '';
        $userWilayah = $actor->wilayah ?? '';

        $daerahQuery = DB::table('master_provinsi')
            ->select(['id as id_daerah', 'nama as namadaerah'])
            ->orderBy('nama');
            
        if (!$isBypassed && !empty($userDaerah)) {
            $daerahQuery->where('nama', $userDaerah);
        }
        
        $daerah = $daerahQuery->get();

        $wilayahQ = $this->wilayahBaseQueryMasterKabkot();

        if (!$isBypassed) {
            if (!empty($userDaerah)) {
                $wilayahQ->where('d.nama', $userDaerah);
            }
            if (!empty($userWilayah)) {
                $wilayahQ->where('w.nama', $userWilayah);
            }
        }

        if ($request->filled('search')) {
            $s = $request->search;
            $wilayahQ->where(function ($q) use ($s) {
                $q->where('w.nama', 'like', "%{$s}%")
                    ->orWhere('d.nama', 'like', "%{$s}%")
                    ->orWhere('w.id', 'like', "%{$s}%")
                    ->orWhere('d.id', 'like', "%{$s}%")
                    ->orWhere('w.kode_kabupaten_kota', 'like', "%{$s}%");
            });
        }

        if ($request->filled('id_daerah')) {
            if ($request->id_daerah === 'unassigned') {
                $wilayahQ->whereNull('d.id')->orWhere('d.id', 0)->orWhereNull('w.master_provinsi_id');
            } else {
                $wilayahQ->where('d.id', $request->id_daerah);
            }
        }

        $resort = $wilayahQ->orderBy('d.nama')->orderBy('w.nama')
            ->paginate($perPage)->withQueryString();

        return view('master.resort.index', compact('daerah', 'resort'));
    }

    private function wilayahBaseQuery(): \Illuminate\Database\Query\Builder
    {
        $select = ['w.id_wilayah', 'w.namawilayah', 'w.id_daerah', 'd.namadaerah', 'w.lat', 'w.lng'];
        if (Schema::hasColumn('wilayah', 'kode_kabupaten_kota')) {
            $select[] = 'w.kode_kabupaten_kota';
        }
        if (Schema::hasColumn('wilayah', 'jenis_wilayah')) {
            $select[] = 'w.jenis_wilayah';
        }

        if (TerritoryMaster::wilayahIdColumnIsNumericDaerahFk()) {
            return DB::table('wilayah as w')
                ->join('daerah as d', 'd.id_daerah', '=', 'w.id_daerah')
                ->select($select);
        }

        return DB::table('wilayah as w')
            ->leftJoin('daerah as d', 'd.namadaerah', '=', 'w.id_daerah')
            ->select($select);
    }

    /** Sumber: `master_kabupaten_kota` + `master_provinsi` (tanpa tabel legacy `daerah` / `wilayah`). */
    private function wilayahBaseQueryMasterKabkot(): \Illuminate\Database\Query\Builder
    {
        return DB::table('master_kabupaten_kota as w')
            ->join('master_provinsi as d', 'd.id', '=', 'w.master_provinsi_id')
            ->select([
                'w.id as id_wilayah',
                'w.nama as namawilayah',
                'd.id as id_daerah',
                'd.nama as namadaerah',
                'w.kode_kabupaten_kota as kode_kabupaten_kota',
                'w.jenis as jenis_wilayah',
                'w.lat as lat',
                'w.lng as lng',
            ]);
    }

    public function create(Request $request)
    {
        $daerahs = $this->daerahOptionsForForms();
        $preselectedDaerah = $request->query('id_daerah');

        return view('master.resort.create', compact('daerahs', 'preselectedDaerah'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'id_daerah' => ['required'],
            'nama' => ['required', 'string', 'max:255'],
            'jenis_wilayah' => ['nullable', 'string', 'in:Kabupaten,Kota'],
            'lat' => ['nullable', 'string', 'max:50'],
            'lng' => ['nullable', 'string', 'max:50'],
        ]);

        $nama = $this->normalizeWilayahName($validated['nama']);
        $jenis = $validated['jenis_wilayah'] ?? 'Kabupaten';

        if ($this->usesMasterKabkotOnly()) {
            $provinsiId = (int) $validated['id_daerah'];
            DB::table('master_kabupaten_kota')->insert([
                'id' => $this->nextMasterKabupatenKotaId(),
                'master_provinsi_id' => $provinsiId,
                'kode_kabupaten_kota' => $this->nextManualKodeKabupatenKota($provinsiId),
                'nama' => $nama,
                'jenis' => $jenis,
                'lat' => $validated['lat'] ?? null,
                'lng' => $validated['lng'] ?? null,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        } elseif (Schema::hasTable('wilayah')) {
            $insertData = [
                'id_daerah' => $this->resolveLegacyWilayahIdDaerahValue($validated['id_daerah']),
                'namawilayah' => $nama,
                'lat' => $validated['lat'] ?? null,
                'lng' => $validated['lng'] ?? null,
            ];
            if (Schema::hasColumn('wilayah', 'jenis_wilayah')) {
                $insertData['jenis_wilayah'] = $jenis;
            }
            DB::table('wilayah')->insert($insertData);
        }

        $redirect = route('master.resort.index');
        if ($request->filled('id_daerah')) {
            $redirect .= '?'.http_build_query(['id_daerah' => $request->id_daerah]);
        }

        return redirect()->to($redirect)->with('success', 'Data Polres berhasil ditambahkan.');
    }

    public function edit($id)
    {
        $row = $this->findWilayahRow($id);
        if ($row === null) {
            abort(404);
        }

        $daerahs = $this->daerahOptionsForForms();

        return view('master.resort.edit', compact('row', 'daerahs'));
    }

    public function update(Request $request, $id)
    {
        if ($this->findWilayahRow($id) === null) {
            abort(404);
        }

        $validated = $request->validate([
            'id_daerah' => ['required'],
            'nama' => ['required', 'string', 'max:255'],
            'jenis_wilayah' => ['nullable', 'string', 'in:Kabupaten,Kota'],
            'lat' => ['nullable', 'string', 'max:50'],
            'lng' => ['nullable', 'string', 'max:50'],
        ]);

        $nama = $this->normalizeWilayahName($validated['nama']);
        $jenis = $validated['jenis_wilayah'] ?? 'Kabupaten';

        if ($this->usesMasterKabkotOnly()) {
            DB::table('master_kabupaten_kota')->where('id', $id)->update([
                'master_provinsi_id' => $validated['id_daerah'],
                'nama' => $nama,
                'jenis' => $jenis,
                'lat' => $validated['lat'] ?? null,
                'lng' => $validated['lng'] ?? null,
                'updated_at' => now(),
            ]);
        } elseif (Schema::hasTable('wilayah')) {
            $update = [
                'id_daerah' => $this->resolveLegacyWilayahIdDaerahValue($validated['id_daerah']),
                'namawilayah' => $nama,
                'lat' => $validated['lat'] ?? null,
                'lng' => $validated['lng'] ?? null,
            ];
            if (Schema::hasColumn('wilayah', 'jenis_wilayah')) {
                $update['jenis_wilayah'] = $jenis;
            }
            DB::table('wilayah')->where('id_wilayah', $id)->update($update);
        }

        return redirect()->route('master.resort.index', ['id_daerah' => $validated['id_daerah']])
            ->with('success', 'Data Polres berhasil diperbarui.');
    }

    public function destroy($id)
    {
        if ($this->findWilayahRow($id) === null) {
            abort(404);
        }

        if ($this->usesMasterKabkotOnly()) {
            DB::table('master_kabupaten_kota')->where('id', $id)->delete();
        } elseif (Schema::hasTable('wilayah')) {
            DB::table('wilayah')->where('id_wilayah', $id)->delete();
        }

        return redirect()->route('master.resort.index')->with('success', 'Data Polres berhasil dihapus.');
    }
}
