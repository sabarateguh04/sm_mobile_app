<?php

namespace App\Http\Controllers;

use App\Helpers\HierarchicalHelper;
use App\Models\DataFaskes;
use App\Support\FaskesFasilitasOptions;
use App\Traits\ExportsIndexQuery;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class FaskesController extends BaseController
{
    use ExportsIndexQuery;

    private const KATEGORI = ['RSUD', 'RS Swasta', 'Puskesmas', 'Klinik', 'Klinik Utama', 'BHC / Pos Kesehatan'];

    public function index(Request $request)
    {
        $query = DB::table('data_faskes as t')
            ->leftJoin('usm', 'usm.id_usm', '=', 't.piket')
            ->select('t.*', 'usm.nama as petugas_nama', 'usm.wilayah as petugas_wilayah', 'usm.daerah as petugas_daerah');

        // Territory filter - konsisten dengan OperasionalController
        HierarchicalHelper::applyTerritoryScopeForTable($query, 'data_faskes', 't');
        \App\Support\TerritoryIndexFilters::applyUsmFilters($query, $request);

        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(function ($q) use ($s) {
                $q->where('t.namafaskes', 'like', "%{$s}%")
                    ->orWhere('t.katfaskes', 'like', "%{$s}%")
                    ->orWhere('t.lokasi', 'like', "%{$s}%")
                    ->orWhere('t.namecp', 'like', "%{$s}%");
            });
        }

        $data = $query->orderByDesc('t.tanggal')->paginate($request->get('per_page', 20))->withQueryString();

        return view('faskes.index', array_merge([
            'pageTitle' => 'Data Faskes',
            'data' => $data,
            'sboViewOnly' => \App\Support\RoleMutationPolicy::deniesAllMutations(Auth::user()),
        ], \App\Support\TerritoryIndexFilters::viewBundle($request)));
    }

    public function export(Request $request, string $format = 'excel')
    {
        return $this->exportIndexQuery(
            $request,
            $format,
            fn (Request $req) => $this->buildIndexQuery($req),
            'data_faskes',
            'Data Faskes',
            't.id_faskes'
        );
    }

    protected function buildIndexQuery(Request $request)
    {
        $query = DB::table('data_faskes as t')
            ->leftJoin('usm', 'usm.id_usm', '=', 't.piket')
            ->select('t.*', 'usm.nama as petugas_nama', 'usm.wilayah as petugas_wilayah', 'usm.daerah as petugas_daerah');

        // Territory filter - konsisten dengan OperasionalController
        HierarchicalHelper::applyTerritoryScopeForTable($query, 'data_faskes', 't');
        \App\Support\TerritoryIndexFilters::applyUsmFilters($query, $request);

        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(function ($q) use ($s) {
                $q->where('t.namafaskes', 'like', "%{$s}%")
                    ->orWhere('t.katfaskes', 'like', "%{$s}%")
                    ->orWhere('t.lokasi', 'like', "%{$s}%")
                    ->orWhere('t.namecp', 'like', "%{$s}%");
            });
        }

        return $query->orderByDesc('t.tanggal');
    }

    public function create()
    {
        \App\Support\RoleMutationPolicy::assertCanCreate(Auth::user());

        return view('faskes.create', [
            'pageTitle' => 'Tambah Data Faskes',
            'kategori' => $this->kategoriOptions(),
            'fasilitasOptions' => FaskesFasilitasOptions::all(),
        ]);
    }

    public function store(Request $request)
    {
        \App\Support\RoleMutationPolicy::assertCanCreate(Auth::user());
        $validated = $this->validateInput($request);
        $validated['piket'] = (string) Auth::id();
        unset($validated['lat'], $validated['lng'], $validated['foto_lokasi']);
        $coord = $this->koordinatStringFromRequest($request);
        $validated['koordinat'] = $coord !== null ? $coord : '';

        if ($request->hasFile('foto_lokasi')) {
            $validated['fotoobject'] = $request->file('foto_lokasi')->store('faskes_foto', 'public');
        }

        DataFaskes::create($validated);

        return redirect()->route('faskes.index')->with('success', 'Data faskes berhasil ditambahkan.');
    }

    public function show($id)
    {
        $data = DB::table('data_faskes as t')
            ->leftJoin('usm', 'usm.id_usm', '=', 't.piket')
            ->select('t.*', 'usm.nama as petugas_nama', 'usm.wilayah as petugas_wilayah', 'usm.daerah as petugas_daerah')
            ->where('t.id_faskes', $id)
            ->first();

        abort_unless($data, 404, 'Data tidak ditemukan');

        return view('faskes.show', array_merge(compact('data'), $this->operationalRecordViewFlags($data, 'data_faskes')));
    }

    public function edit($id)
    {
        $data = DataFaskes::findOrFail($id);
        $this->assertOperatorOwnsOperationalRow($data, 'data_faskes');

        return view('faskes.edit', [
            'pageTitle' => 'Edit Data Faskes',
            'data' => $data,
            'kategori' => $this->kategoriOptions(),
            'fasilitasOptions' => FaskesFasilitasOptions::all(),
        ]);
    }

    public function update(Request $request, $id)
    {
        $data = DataFaskes::findOrFail($id);
        $this->assertOperatorOwnsOperationalRow($data, 'data_faskes');
        $validated = $this->validateInput($request);
        unset($validated['lat'], $validated['lng'], $validated['foto_lokasi']);
        $coord = $this->koordinatStringFromRequest($request);
        if ($coord !== null) {
            $validated['koordinat'] = $coord;
        } else {
            unset($validated['koordinat']);
        }

        if ($request->hasFile('foto_lokasi')) {
            $this->deleteStoredFoto($data->fotoobject);
            $validated['fotoobject'] = $request->file('foto_lokasi')->store('faskes_foto', 'public');
        }

        $data->update($validated);

        return redirect()->route('faskes.index')->with('success', 'Data faskes berhasil diperbarui.');
    }

    public function destroy($id)
    {
        $data = DataFaskes::findOrFail($id);
        $this->assertOperatorOwnsOperationalRow($data, 'data_faskes');
        $this->deleteStoredFoto($data->fotoobject);
        $data->delete();

        return redirect()->route('faskes.index')->with('success', 'Data faskes berhasil dihapus.');
    }

    /**
     * @return list<string>
     */
    protected function kategoriOptions(): array
    {
        if (Schema::hasTable('t_katfaskes')) {
            return DB::table('t_katfaskes')->orderBy('namekatfaskes')->pluck('namekatfaskes')->filter()->values()->all();
        }

        return self::KATEGORI;
    }

    private function validateInput(Request $request): array
    {
        $fasOpts = FaskesFasilitasOptions::all();

        $validated = $request->validate([
            'tanggal'        => 'required|date',
            'katfaskes'      => 'required|string|max:100',
            'namafaskes'     => 'required|string|max:200',
            'lokasi'         => 'required|string|max:255',
            'lat'            => ['nullable', 'numeric', 'between:-90,90'],
            'lng'            => ['nullable', 'numeric', 'between:-180,180'],
            'fasilitas'      => ['nullable', 'array'],
            'fasilitas.*'    => ['string', 'max:120', Rule::in($fasOpts)],
            'namecp'         => 'nullable|string|max:200',
            'cp'             => 'nullable|string|max:100',
            'telpambulance'  => 'nullable|string|max:50',
            'telpigd'        => 'nullable|string|max:50',
            'foto_lokasi'    => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif,webp', 'max:8192'],
        ]);

        $parts = isset($validated['fasilitas']) && is_array($validated['fasilitas'])
            ? $validated['fasilitas']
            : [];
        $fasStr = implode(', ', array_unique(array_filter(array_map('trim', $parts))));
        if (strlen($fasStr) > 500) {
            throw ValidationException::withMessages([
                'fasilitas' => ['Gabungan fasilitas terlalu panjang. Kurangi pilihan.'],
            ]);
        }
        $validated['fasilitas'] = $fasStr !== '' ? $fasStr : null;

        return $validated;
    }

    private function koordinatStringFromRequest(Request $request): ?string
    {
        $lat = $request->input('lat');
        $lng = $request->input('lng');
        if ($lat !== null && $lat !== '' && $lng !== null && $lng !== '') {
            return $this->formatCoord((float) $lat).','.$this->formatCoord((float) $lng);
        }

        return null;
    }

    private function formatCoord(float $n): string
    {
        $s = number_format($n, 8, '.', '');
        $s = rtrim(rtrim($s, '0'), '.');

        return $s === '-0' ? '0' : $s;
    }

    private function deleteStoredFoto(?string $path): void
    {
        if (empty($path) || preg_match('#^https?://#i', $path)) {
            return;
        }
        Storage::disk('public')->delete($path);
    }
}
