<?php

namespace App\Http\Controllers;

use App\Helpers\HierarchicalHelper;
use App\Models\TempatIbadah;
use App\Support\AlatKeamananOptions;
use App\Traits\ExportsIndexQuery;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class TempatIbadahController extends BaseController
{
    use ExportsIndexQuery;

    private const KATEGORI = ['Masjid', 'Gereja', 'Pura', 'Vihara', 'Klenteng', 'Mushola', 'Lainnya'];

    public function index(Request $request)
    {
        $query = DB::table('tempatibadah as t')
            ->leftJoin('usm', 'usm.id_usm', '=', 't.piket')
            ->select('t.*', 'usm.nama as petugas_nama', 'usm.wilayah as petugas_wilayah', 'usm.daerah as petugas_daerah');

        // Territory filter - konsisten dengan OperasionalController
        HierarchicalHelper::applyTerritoryScopeForTable($query, 'tempatibadah', 't');
        \App\Support\TerritoryIndexFilters::applyUsmFilters($query, $request);

        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(function ($q) use ($s) {
                $q->where('t.namatempatibadah', 'like', "%{$s}%")
                  ->orWhere('t.kattempat', 'like', "%{$s}%")
                  ->orWhere('t.lokasi', 'like', "%{$s}%");
            });
        }

        $data = $query->orderByDesc('t.tanggal')->paginate($request->get('per_page', 20))->withQueryString();

        return view('tempatibadah.index', array_merge([
            'pageTitle' => 'Tempat Ibadah',
            'data' => $data,
            'kategori' => $this->kategoriOptions(),
            'sboViewOnly' => \App\Support\RoleMutationPolicy::deniesAllMutations(\Illuminate\Support\Facades\Auth::user()),
        ], \App\Support\TerritoryIndexFilters::viewBundle($request)));
    }

    public function export(Request $request, string $format = 'excel')
    {
        return $this->exportIndexQuery(
            $request,
            $format,
            fn (Request $req) => $this->buildIndexQuery($req),
            'tempatibadah',
            'Tempat Ibadah',
            't.id_tempatibadah'
        );
    }

    protected function buildIndexQuery(Request $request)
    {
        $query = DB::table('tempatibadah as t')
            ->leftJoin('usm', 'usm.id_usm', '=', 't.piket')
            ->select('t.*', 'usm.nama as petugas_nama', 'usm.wilayah as petugas_wilayah', 'usm.daerah as petugas_daerah');

        // Territory filter - konsisten dengan OperasionalController
        HierarchicalHelper::applyTerritoryScopeForTable($query, 'tempatibadah', 't');
        \App\Support\TerritoryIndexFilters::applyUsmFilters($query, $request);

        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(function ($q) use ($s) {
                $q->where('t.namatempatibadah', 'like', "%{$s}%")
                  ->orWhere('t.kattempat', 'like', "%{$s}%")
                  ->orWhere('t.lokasi', 'like', "%{$s}%");
            });
        }

        return $query->orderByDesc('t.tanggal');
    }

    public function create()
    {
        \App\Support\RoleMutationPolicy::assertCanCreate(\Illuminate\Support\Facades\Auth::user());
        return view('tempatibadah.create', [
            'pageTitle' => 'Tambah Tempat Ibadah',
            'kategori' => $this->kategoriOptions(),
            'alatKeamananOptions' => AlatKeamananOptions::all(),
        ]);
    }

    public function store(Request $request)
    {
        \App\Support\RoleMutationPolicy::assertCanCreate(\Illuminate\Support\Facades\Auth::user());
        $validated = $this->validateInput($request);
        $validated['piket'] = (string) Auth::id();
        unset($validated['lat'], $validated['lng'], $validated['foto_lokasi']);
        $coord = $this->koordinatStringFromRequest($request);
        $validated['koordinat'] = $coord !== null ? $coord : '';

        if ($request->hasFile('foto_lokasi')) {
            $validated['fotoobject'] = $request->file('foto_lokasi')->store('tempatibadah_foto', 'public');
        }

        TempatIbadah::create($validated);

        return redirect()->route('tempatibadah.index')->with('success', 'Tempat ibadah berhasil ditambahkan.');
    }

    public function show($id)
    {
        $data = DB::table('tempatibadah as t')
            ->leftJoin('usm', 'usm.id_usm', '=', 't.piket')
            ->select('t.*', 'usm.nama as petugas_nama', 'usm.wilayah as petugas_wilayah', 'usm.daerah as petugas_daerah')
            ->where('t.id_tempatibadah', $id)
            ->first();

        abort_unless($data, 404, 'Data tidak ditemukan');

        return view('tempatibadah.show', compact('data'));
    }

    public function edit($id)
    {
        $data = TempatIbadah::findOrFail($id);
        $this->assertOperatorOwnsOperationalRow($data, 'tempatibadah');

        return view('tempatibadah.edit', [
            'pageTitle' => 'Edit Tempat Ibadah',
            'data' => $data,
            'kategori' => $this->kategoriOptions(),
            'alatKeamananOptions' => AlatKeamananOptions::all(),
        ]);
    }

    public function update(Request $request, $id)
    {
        $data = TempatIbadah::findOrFail($id);
        $this->assertOperatorOwnsOperationalRow($data, 'tempatibadah');
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
            $validated['fotoobject'] = $request->file('foto_lokasi')->store('tempatibadah_foto', 'public');
        }

        $data->update($validated);

        return redirect()->route('tempatibadah.index')->with('success', 'Tempat ibadah berhasil diperbarui.');
    }

    public function destroy($id)
    {
        $data = TempatIbadah::findOrFail($id);
        $this->assertOperatorOwnsOperationalRow($data, 'tempatibadah');
        $this->deleteStoredFoto($data->fotoobject);
        $data->delete();

        return redirect()->route('tempatibadah.index')->with('success', 'Tempat ibadah berhasil dihapus.');
    }

    /**
     * @return list<string>
     */
    protected function kategoriOptions(): array
    {
        if (Schema::hasTable('t_kattempatibadah')) {
            return DB::table('t_kattempatibadah')->orderBy('namekategori')->pluck('namekategori')->filter()->values()->all();
        }

        return self::KATEGORI;
    }

    private function validateInput(Request $request): array
    {
        $alatOpts = AlatKeamananOptions::all();

        $validated = $request->validate([
            'tanggal'           => 'required|date',
            'kattempat'         => 'required|string|max:100',
            'namatempatibadah'  => 'required|string|max:100',
            'lokasi'            => 'required|string|max:100',
            'lat'               => ['nullable', 'numeric', 'between:-90,90'],
            'lng'               => ['nullable', 'numeric', 'between:-180,180'],
            'kappengunjung'     => 'nullable|integer|min:0',
            'kapparkir'         => 'nullable|integer|min:0',
            'alatkeamanan'      => ['nullable', 'array'],
            'alatkeamanan.*'    => ['string', 'max:120', Rule::in($alatOpts)],
            'namecp'            => 'nullable|string|max:255',
            'cp'                => 'nullable|string|max:100',
            'foto_lokasi'       => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif,webp', 'max:8192'],
        ]);

        $parts = isset($validated['alatkeamanan']) && is_array($validated['alatkeamanan'])
            ? $validated['alatkeamanan']
            : [];
        $alatStr = implode(', ', array_unique(array_filter(array_map('trim', $parts))));
        if (strlen($alatStr) > 255) {
            throw ValidationException::withMessages([
                'alatkeamanan' => ['Gabungan alat keamanan terlalu panjang. Kurangi pilihan.'],
            ]);
        }
        $validated['alatkeamanan'] = $alatStr !== '' ? $alatStr : null;

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
