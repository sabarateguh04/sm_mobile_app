<?php



namespace App\Http\Controllers;



use App\Helpers\HierarchicalHelper;

use App\Models\KunjunganWisata;

use App\Support\TerritoryIndexFilters;

use App\Traits\ExportsIndexQuery;

use Illuminate\Http\Request;

use Illuminate\Support\Facades\Auth;

use Illuminate\Support\Facades\DB;

use Illuminate\Support\Facades\Schema;

use Illuminate\Support\Facades\Storage;



class KunjunganWisataController extends BaseController

{

    use ExportsIndexQuery;

    protected function applyIndexFilters($query, Request $request): void

    {

        if ($request->filled('tanggal_dari')) {

            $query->whereDate('k.tanggal', '>=', $request->tanggal_dari);

        }

        if ($request->filled('tanggal_sampai')) {

            $query->whereDate('k.tanggal', '<=', $request->tanggal_sampai);

        }

        if ($request->filled('katwisata')) {

            $query->where('k.katwisata', $request->katwisata);

        }

        if ($request->filled('namawisata')) {

            $query->where('k.namawisata', $request->namawisata);

        }



        TerritoryIndexFilters::applyUsmFilters($query, $request);

    }



    public function index(Request $request)

    {

        $query = DB::table('kunjunganwisata as k')

            ->leftJoin('usm', 'usm.id_usm', '=', 'k.piket')

            ->select('k.*', 'usm.nama as petugas_nama', 'usm.wilayah as petugas_wilayah', 'usm.daerah as petugas_daerah');



        HierarchicalHelper::applyTerritoryScopeForTable($query, 'kunjunganwisata', 'k');

        $this->applyIndexFilters($query, $request);



        if ($request->filled('search')) {

            $s = $request->search;

            $query->where(function ($q) use ($s) {

                $q->where('k.namawisata', 'like', "%{$s}%")

                    ->orWhere('k.katwisata', 'like', "%{$s}%")

                    ->orWhere('k.koresponden', 'like', "%{$s}%")

                    ->orWhere('usm.nama', 'like', "%{$s}%");

            });

        }



        $data = $query->orderByDesc('k.tanggal')->paginate($request->get('per_page', 20))->withQueryString();



        $kategori = Schema::hasTable('t_katwisata')

            ? DB::table('t_katwisata')->orderBy('namekatwisata')->pluck('namekatwisata')

            : DB::table('kunjunganwisata')->whereNotNull('katwisata')->distinct()->orderBy('katwisata')->pluck('katwisata');



        $namaWisataList = DB::table('kunjunganwisata')->whereNotNull('namawisata')->where('namawisata', '!=', '')->distinct()->orderBy('namawisata')->pluck('namawisata');



        return view('kunjunganwisata.index', array_merge([

            'pageTitle' => 'Kunjungan Wisata Harian',

            'data' => $data,

            'kategori' => $kategori,

            'namaWisataList' => $namaWisataList,

        ], TerritoryIndexFilters::viewBundle($request)));

    }

    public function export(Request $request, string $format = 'excel')
    {
        return $this->exportIndexQuery(
            $request,
            $format,
            fn (Request $req) => $this->buildIndexQuery($req),
            'kunjunganwisata',
            'Kunjungan Wisata Harian',
            'k.id_kunjunganwisata'
        );
    }

    protected function buildIndexQuery(Request $request)
    {
        $query = DB::table('kunjunganwisata as k')
            ->leftJoin('usm', 'usm.id_usm', '=', 'k.piket')
            ->select('k.*', 'usm.nama as petugas_nama', 'usm.wilayah as petugas_wilayah', 'usm.daerah as petugas_daerah');

        HierarchicalHelper::applyTerritoryScopeForTable($query, 'kunjunganwisata', 'k');
        $this->applyIndexFilters($query, $request);

        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(function ($q) use ($s) {
                $q->where('k.namawisata', 'like', "%{$s}%")
                    ->orWhere('k.katwisata', 'like', "%{$s}%")
                    ->orWhere('k.koresponden', 'like', "%{$s}%")
                    ->orWhere('usm.nama', 'like', "%{$s}%");
            });
        }

        return $query->orderByDesc('k.tanggal');
    }



    public function create()

    {

        \App\Support\RoleMutationPolicy::assertCanCreate(\Illuminate\Support\Facades\Auth::user());



        return view('kunjunganwisata.create', [

            'pageTitle' => 'Tambah Kunjungan Wisata',

            'kategori' => Schema::hasTable('t_katwisata')

                ? DB::table('t_katwisata')->orderBy('namekatwisata')->pluck('namekatwisata')

                : collect(),

        ]);

    }



    public function store(Request $request)

    {

        \App\Support\RoleMutationPolicy::assertCanCreate(\Illuminate\Support\Facades\Auth::user());

        $validated = $this->validateInput($request);

        $validated['piket'] = (string) Auth::id();



        if ($request->hasFile('fotogiat')) {

            $validated['fotogiat'] = $request->file('fotogiat')->store('kunjunganwisata_foto', 'public');

        }



        KunjunganWisata::create($validated);



        return redirect()->route('kunjunganwisata.index')->with('success', 'Data kunjungan wisata berhasil ditambahkan.');

    }



    public function show($id)

    {

        $data = DB::table('kunjunganwisata as k')

            ->leftJoin('usm', 'usm.id_usm', '=', 'k.piket')

            ->select('k.*', 'usm.nama as petugas_nama', 'usm.wilayah as petugas_wilayah', 'usm.daerah as petugas_daerah')

            ->where('k.id_kunjunganwisata', $id)

            ->first();



        abort_unless($data, 404, 'Data tidak ditemukan');



        $totalKbm = (int) ($data->jmlhkbm ?? 0);

        if ($totalKbm === 0) {

            $totalKbm = (int) ($data->parkirr2 ?? 0) + (int) ($data->parkirr4 ?? 0);

        }



        return view('kunjunganwisata.show', array_merge([

            'pageTitle' => 'Detail Kunjungan Wisata',

            'data' => $data,

            'fotoUrl' => KunjunganWisata::resolveFotoUrl($data->fotogiat ?? null),

            'totalKbm' => $totalKbm,

        ], $this->operationalRecordViewFlags($data, 'kunjunganwisata')));

    }



    public function edit($id)

    {

        $data = KunjunganWisata::findOrFail($id);

        $this->assertOperatorOwnsOperationalRow($data, 'kunjunganwisata');



        return view('kunjunganwisata.edit', [

            'pageTitle' => 'Edit Kunjungan Wisata',

            'data' => $data,

            'kategori' => Schema::hasTable('t_katwisata')

                ? DB::table('t_katwisata')->orderBy('namekatwisata')->pluck('namekatwisata')

                : collect(),

        ]);

    }



    public function update(Request $request, $id)

    {

        $data = KunjunganWisata::findOrFail($id);

        $this->assertOperatorOwnsOperationalRow($data, 'kunjunganwisata');

        $validated = $this->validateInput($request);



        if ($request->hasFile('fotogiat')) {

            $this->deleteStoredFoto($data->fotogiat);

            $validated['fotogiat'] = $request->file('fotogiat')->store('kunjunganwisata_foto', 'public');

        }



        $data->update($validated);



        return redirect()->route('kunjunganwisata.index')->with('success', 'Data kunjungan wisata berhasil diperbarui.');

    }



    public function destroy($id)

    {

        $data = KunjunganWisata::findOrFail($id);

        $this->assertOperatorOwnsOperationalRow($data, 'kunjunganwisata');

        $this->deleteStoredFoto($data->fotogiat);

        $data->delete();



        return redirect()->route('kunjunganwisata.index')->with('success', 'Data kunjungan wisata berhasil dihapus.');

    }



    private function validateInput(Request $request): array

    {

        return $request->validate([

            'tanggal' => 'required|date',

            'katwisata' => 'required|string|max:100',

            'namawisata' => 'required|string|max:100',

            'koresponden' => 'nullable|string|max:100',

            'parkirr2' => 'required|integer|min:0',

            'parkirr4' => 'required|integer|min:0',

            'domestic' => 'required|integer|min:0',

            'mancanegara' => 'required|integer|min:0',

            'jmlhpengunjung' => 'required|integer|min:0',

            'jmlhkbm' => 'nullable|integer|min:0',

            'fotogiat' => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif,webp', 'max:8192'],

        ]);

    }



    private function deleteStoredFoto(?string $path): void

    {

        if (empty($path) || preg_match('#^https?://#i', $path)) {

            return;

        }

        Storage::disk('public')->delete($path);

    }

}


