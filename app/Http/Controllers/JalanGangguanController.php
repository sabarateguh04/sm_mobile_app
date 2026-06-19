<?php



namespace App\Http\Controllers;



use App\Helpers\HierarchicalHelper;

use App\Models\DataGangguan;

use App\Support\TerritoryIndexFilters;

use App\Traits\ExportsIndexQuery;

use Illuminate\Http\Request;

use Illuminate\Support\Facades\Auth;

use Illuminate\Support\Facades\DB;

use Illuminate\Support\Facades\Schema;

use Illuminate\Support\Facades\Storage;



/**

 * CRUD untuk tabel `data_gangguan` (database sbo).

 * Filter wilayah/daerah dilakukan via JOIN ke `usm` lewat kolom `piket`.

 */

class JalanGangguanController extends BaseController

{

    use ExportsIndexQuery;

    protected function applyIndexFilters($query, Request $request): void

    {

        if ($request->filled('tanggal_dari')) {

            $query->where('data_gangguan.tanggal', '>=', $request->tanggal_dari);

        }

        if ($request->filled('tanggal_sampai')) {

            $query->where('data_gangguan.tanggal', '<=', $request->tanggal_sampai);

        }

        if ($request->filled('fungsijalan')) {

            $query->where('data_gangguan.fungsijalan', $request->fungsijalan);

        }

        if ($request->filled('levelgangguan')) {

            $query->where('data_gangguan.levelgangguan', $request->levelgangguan);

        }

        if ($request->filled('faktor')) {

            $query->where('data_gangguan.faktor', $request->faktor);

        }



        TerritoryIndexFilters::applyUsmFilters($query, $request);

    }



    public function index(Request $request)

    {

        $query = DB::table('data_gangguan')

            ->leftJoin('usm', 'data_gangguan.piket', '=', 'usm.id_usm')

            ->select(

                'data_gangguan.*',

                'usm.nama as usm_nama',

                'usm.daerah as usm_daerah',

                'usm.wilayah as usm_wilayah'

            );



        HierarchicalHelper::applyTerritoryScopeForTable($query, 'data_gangguan');

        $this->applyIndexFilters($query, $request);



        if ($request->filled('search')) {

            $s = $request->search;

            $query->where(function ($q) use ($s) {

                $q->where('data_gangguan.namajalan', 'like', "%{$s}%")

                    ->orWhere('data_gangguan.fungsijalan', 'like', "%{$s}%")

                    ->orWhere('data_gangguan.kerawanan', 'like', "%{$s}%")

                    ->orWhere('data_gangguan.penyebab', 'like', "%{$s}%")

                    ->orWhere('usm.nama', 'like', "%{$s}%");

            });

        }



        $data = $query->orderBy('data_gangguan.tanggal', 'desc')->paginate(20)->withQueryString();



        $jenisJalanList = Schema::hasTable('t_jenisjalan')

            ? DB::table('t_jenisjalan')->orderBy('namajenis')->pluck('namajenis')

            : DB::table('data_gangguan')->whereNotNull('fungsijalan')->distinct()->orderBy('fungsijalan')->pluck('fungsijalan');



        $levelGangguanList = Schema::hasTable('levelgangguan')

            ? DB::table('levelgangguan')->orderBy('namelevelgangguan')->pluck('namelevelgangguan')

            : DB::table('data_gangguan')->whereNotNull('levelgangguan')->distinct()->orderBy('levelgangguan')->pluck('levelgangguan');



        $faktorList = Schema::hasTable('t_faktor')

            ? DB::table('t_faktor')->orderBy('namefaktor')->pluck('namefaktor')

            : DB::table('data_gangguan')->whereNotNull('faktor')->distinct()->orderBy('faktor')->pluck('faktor');



        return view('jalan_gangguan.index', array_merge([

            'pageTitle' => 'Data Gangguan Jalan',

            'data' => $data,

            'jenisJalanList' => $jenisJalanList,

            'levelGangguanList' => $levelGangguanList,

            'faktorList' => $faktorList,

        ], TerritoryIndexFilters::viewBundle($request)));

    }

    public function export(Request $request, string $format = 'excel')
    {
        return $this->exportIndexQuery(
            $request,
            $format,
            fn (Request $req) => $this->buildIndexQuery($req),
            'data_gangguan',
            'Data Gangguan Jalan',
            'data_gangguan.id_datagangguan'
        );
    }

    protected function buildIndexQuery(Request $request)
    {
        $query = DB::table('data_gangguan')
            ->leftJoin('usm', 'data_gangguan.piket', '=', 'usm.id_usm')
            ->select(
                'data_gangguan.*',
                'usm.nama as usm_nama',
                'usm.daerah as usm_daerah',
                'usm.wilayah as usm_wilayah'
            );

        HierarchicalHelper::applyTerritoryScopeForTable($query, 'data_gangguan');
        $this->applyIndexFilters($query, $request);

        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(function ($q) use ($s) {
                $q->where('data_gangguan.namajalan', 'like', "%{$s}%")
                    ->orWhere('data_gangguan.fungsijalan', 'like', "%{$s}%")
                    ->orWhere('data_gangguan.kerawanan', 'like', "%{$s}%")
                    ->orWhere('data_gangguan.penyebab', 'like', "%{$s}%")
                    ->orWhere('usm.nama', 'like', "%{$s}%");
            });
        }

        return $query->orderBy('data_gangguan.tanggal', 'desc');
    }



    public function create()

    {

        \App\Support\RoleMutationPolicy::assertCanCreate(\Illuminate\Support\Facades\Auth::user());

        $jenisJalan = Schema::hasTable('t_jenisjalan')

            ? DB::table('t_jenisjalan')->orderBy('namajenis')->pluck('namajenis')

            : collect();

        $levelGangguan = Schema::hasTable('levelgangguan')

            ? DB::table('levelgangguan')->orderBy('namelevelgangguan')->pluck('namelevelgangguan')

            : collect();

        $faktor = Schema::hasTable('t_faktor')

            ? DB::table('t_faktor')->orderBy('namefaktor')->pluck('namefaktor')

            : collect();



        return view('jalan_gangguan.create', [

            'pageTitle' => 'Tambah Data Gangguan',

            'jenisJalan' => $jenisJalan,

            'levelGangguan' => $levelGangguan,

            'faktor' => $faktor,

        ]);

    }



    public function store(Request $request)

    {

        \App\Support\RoleMutationPolicy::assertCanCreate(\Illuminate\Support\Facades\Auth::user());

        $validated = $this->validateInput($request);

        $validated['piket'] = (string) Auth::id();

        $payload = $this->fillGangguanPayload($request, $validated, null);

        DataGangguan::create($payload);



        return redirect()->route('jalan_gangguan.index')->with('success', 'Data gangguan berhasil ditambah.');

    }



    public function show($id)

    {

        $data = DB::table('data_gangguan')

            ->leftJoin('usm', 'data_gangguan.piket', '=', 'usm.id_usm')

            ->select('data_gangguan.*', 'usm.nama as usm_nama', 'usm.daerah as usm_daerah', 'usm.wilayah as usm_wilayah')

            ->where('data_gangguan.id_datagangguan', $id)->first();

        abort_unless($data, 404, 'Data tidak ditemukan');



        return view('jalan_gangguan.show', array_merge([

            'pageTitle' => 'Detail Data Gangguan',

            'data' => $data,

        ], $this->operationalRecordViewFlags($data, 'data_gangguan')));

    }



    public function edit($id)

    {

        $data = DataGangguan::findOrFail($id);

        $this->assertOperatorOwnsOperationalRow($data, 'data_gangguan');

        $jenisJalan = Schema::hasTable('t_jenisjalan')

            ? DB::table('t_jenisjalan')->orderBy('namajenis')->pluck('namajenis')

            : collect();

        $levelGangguan = Schema::hasTable('levelgangguan')

            ? DB::table('levelgangguan')->orderBy('namelevelgangguan')->pluck('namelevelgangguan')

            : collect();

        $faktor = Schema::hasTable('t_faktor')

            ? DB::table('t_faktor')->orderBy('namefaktor')->pluck('namefaktor')

            : collect();



        return view('jalan_gangguan.edit', [

            'pageTitle' => 'Edit Data Gangguan',

            'data' => $data,

            'jenisJalan' => $jenisJalan,

            'levelGangguan' => $levelGangguan,

            'faktor' => $faktor,

        ]);

    }



    public function update(Request $request, $id)

    {

        $data = DataGangguan::findOrFail($id);

        $this->assertOperatorOwnsOperationalRow($data, 'data_gangguan');

        $validated = $this->validateInput($request);

        if ($request->hasFile('foto_lokasi')) {

            $this->deleteStoredGangguanFoto($data->fotolokasi);

        }

        $payload = $this->fillGangguanPayload($request, $validated, $data->fotolokasi);

        $data->update($payload);



        return redirect()->route('jalan_gangguan.index')->with('success', 'Data gangguan berhasil diubah.');

    }



    public function destroy($id)

    {

        $data = DataGangguan::findOrFail($id);

        $this->assertOperatorOwnsOperationalRow($data, 'data_gangguan');

        $this->deleteStoredGangguanFoto($data->fotolokasi);

        $data->delete();



        return redirect()->route('jalan_gangguan.index')->with('success', 'Data gangguan berhasil dihapus.');

    }



    /**

     * @param  string|null  $existingFotoPath  path di disk public (bukan URL) untuk pertahankan jika tidak upload baru

     * @return array<string, mixed>

     */

    private function fillGangguanPayload(Request $request, array $validated, ?string $existingFotoPath): array

    {

        $out = $validated;

        unset($out['lat'], $out['lng'], $out['foto_lokasi']);

        // Normalise teamsurvayor: array → comma-separated string
        if (isset($out['teamsurvayor']) && is_array($out['teamsurvayor'])) {
            $out['teamsurvayor'] = implode(', ', array_filter(array_map('trim', $out['teamsurvayor'])));
        }



        $lat = $request->input('lat');

        $lng = $request->input('lng');

        if ($lat !== null && $lat !== '' && $lng !== null && $lng !== '') {

            $out['koordinat'] = $this->formatCoord((float) $lat).','.$this->formatCoord((float) $lng);

        } else {

            $out['koordinat'] = null;

        }



        if ($request->hasFile('foto_lokasi')) {

            $out['fotolokasi'] = $request->file('foto_lokasi')->store('gangguan_foto', 'public');

        } else {

            $out['fotolokasi'] = $existingFotoPath;

        }



        return $out;

    }



    private function formatCoord(float $n): string

    {

        $s = number_format($n, 8, '.', '');

        $s = rtrim(rtrim($s, '0'), '.');



        return $s === '-0' ? '0' : $s;

    }



    private function deleteStoredGangguanFoto(?string $path): void

    {

        if (empty($path) || preg_match('#^https?://#i', $path)) {

            return;

        }

        Storage::disk('public')->delete($path);

    }



    private function validateInput(Request $request): array

    {

        return $request->validate([

            'tanggal'       => 'required|date',

            'fungsijalan'   => 'required|string|max:100',

            'namajalan'     => 'required|string|max:100',

            'kerawanan'     => 'nullable|string|max:100',

            'faktor'        => 'nullable|string|max:100',

            'penyebab'      => 'nullable|string|max:100',

            'levelgangguan' => 'nullable|string|max:100',

            // teamsurvayor: array (multi-select, maks 5) atau string
            'teamsurvayor'  => 'nullable',

            'teamsurvayor.*'=> 'nullable|string|max:100',

            'lat'           => ['nullable', 'numeric', 'between:-90,90'],
            'lng'           => ['nullable', 'numeric', 'between:-180,180'],

            'foto_lokasi'   => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif,webp', 'max:8192'],

        ], [

            'lat.required_with' => 'Isi latitude dan longitude keduanya, atau kosongkan keduanya.',

            'lng.required_with' => 'Isi latitude dan longitude keduanya, atau kosongkan keduanya.',

        ]);

    }

}


