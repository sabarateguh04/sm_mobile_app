<?php

namespace App\Http\Controllers;

use App\Helpers\HierarchicalHelper;
use App\Models\ObjekWisata;
use App\Support\AlatKeamananOptions;
use App\Traits\ExportsIndexQuery;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class ObjekWisataController extends BaseController
{
    use ExportsIndexQuery;

    public function index(Request $request)
    {
        $query = DB::table('objek_wisata as t')
            ->leftJoin('usm', 'usm.id_usm', '=', 't.piket')
            ->select(
                't.*',
                DB::raw('t.id_tempatwisata as id_objekwisata'),
                'usm.nama as petugas_nama',
                'usm.wilayah as petugas_wilayah',
                'usm.daerah as petugas_daerah'
            );

        $this->applyHierarchy($query);

        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(function ($q) use ($s) {
                $q->where('t.nametempat', 'like', "%{$s}%")
                  ->orWhere('t.kattempatwisata', 'like', "%{$s}%")
                  ->orWhere('t.lokasi', 'like', "%{$s}%")
                  ->orWhere('t.cp', 'like', "%{$s}%");
            });
        }

        $data = $query->orderByDesc('t.tanggal')->paginate($request->get('per_page', 20))->withQueryString();

        return view('objekwisata.index', [
            'pageTitle' => 'Objek Wisata',
            'data' => $data,
            'kategori' => DB::table('t_katwisata')->orderBy('namekatwisata')->get(),
            'sboViewOnly' => \App\Support\RoleMutationPolicy::deniesAllMutations(\Illuminate\Support\Facades\Auth::user()),
        ]);
    }

    public function export(Request $request, string $format = 'excel')
    {
        return $this->exportIndexQuery(
            $request,
            $format,
            fn (Request $req) => $this->buildIndexQuery($req),
            'objek_wisata',
            'Objek Wisata',
            't.id_tempatwisata'
        );
    }

    protected function buildIndexQuery(Request $request)
    {
        $query = DB::table('objek_wisata as t')
            ->leftJoin('usm', 'usm.id_usm', '=', 't.piket')
            ->select(
                't.*',
                DB::raw('t.id_tempatwisata as id_objekwisata'),
                'usm.nama as petugas_nama',
                'usm.wilayah as petugas_wilayah',
                'usm.daerah as petugas_daerah'
            );

        $this->applyHierarchy($query);

        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(function ($q) use ($s) {
                $q->where('t.nametempat', 'like', "%{$s}%")
                  ->orWhere('t.kattempatwisata', 'like', "%{$s}%")
                  ->orWhere('t.lokasi', 'like', "%{$s}%")
                  ->orWhere('t.cp', 'like', "%{$s}%");
            });
        }

        return $query->orderByDesc('t.tanggal');
    }

    public function create()
    {
        \App\Support\RoleMutationPolicy::assertCanCreate(\Illuminate\Support\Facades\Auth::user());
        return view('objekwisata.create', [
            'pageTitle' => 'Tambah Objek Wisata',
            'kategori' => DB::table('t_katwisata')->orderBy('namekatwisata')->pluck('namekatwisata'),
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
            $validated['fotoobject'] = $request->file('foto_lokasi')->store('objekwisata_foto', 'public');
        }

        ObjekWisata::create($validated);

        return redirect()->route('objekwisata.index')->with('success', 'Objek wisata berhasil ditambahkan.');
    }

    public function show($id)
    {
        $data = DB::table('objek_wisata as t')
            ->leftJoin('usm', 'usm.id_usm', '=', 't.piket')
            ->select('t.*', 'usm.nama as petugas_nama', 'usm.wilayah as petugas_wilayah', 'usm.daerah as petugas_daerah')
            ->where('t.id_tempatwisata', $id)
            ->first();

        abort_unless($data, 404, 'Data tidak ditemukan');

        return view('objekwisata.show', array_merge(compact('data'), $this->operationalRecordViewFlags($data, 'objek_wisata'), [
            'sboViewOnly' => \App\Support\RoleMutationPolicy::deniesAllMutations(\Illuminate\Support\Facades\Auth::user()),
        ]));
    }

    public function edit($id)
    {
        $data = ObjekWisata::findOrFail($id);
        $this->assertOperatorOwnsOperationalRow($data, 'objek_wisata');

        return view('objekwisata.edit', [
            'pageTitle' => 'Edit Objek Wisata',
            'data' => $data,
            'kategori' => DB::table('t_katwisata')->orderBy('namekatwisata')->pluck('namekatwisata'),
            'alatKeamananOptions' => AlatKeamananOptions::all(),
        ]);
    }

    public function update(Request $request, $id)
    {
        $data = ObjekWisata::findOrFail($id);
        $this->assertOperatorOwnsOperationalRow($data, 'objek_wisata');
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
            $validated['fotoobject'] = $request->file('foto_lokasi')->store('objekwisata_foto', 'public');
        }

        $data->update($validated);

        return redirect()->route('objekwisata.index')->with('success', 'Objek wisata berhasil diperbarui.');
    }

    public function destroy($id)
    {
        $data = ObjekWisata::findOrFail($id);
        $this->assertOperatorOwnsOperationalRow($data, 'objek_wisata');
        $this->deleteStoredFoto($data->fotoobject);
        $data->delete();

        return redirect()->route('objekwisata.index')->with('success', 'Objek wisata berhasil dihapus.');
    }

    private function validateInput(Request $request): array
    {
        $alatOpts = AlatKeamananOptions::all();

        $validated = $request->validate([
            'tanggal'        => 'required|date',
            'kattempatwisata'=> 'required|string|max:100',
            'nametempat'     => 'required|string|max:100',
            'lokasi'         => 'required|string|max:100',
            'lat'            => ['nullable', 'numeric', 'between:-90,90'],
            'lng'            => ['nullable', 'numeric', 'between:-180,180'],
            'kappengunjung'  => 'required|integer|min:0',
            'kapparkir'      => 'required|integer|min:0',
            'namecp'         => 'nullable|string|max:100',
            'cp'             => 'nullable|string|max:100',
            'alatkamanan'    => ['nullable', 'array'],
            'alatkamanan.*'  => ['string', 'max:120', Rule::in($alatOpts)],
            'foto_lokasi'    => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif,webp', 'max:8192'],
        ]);

        $parts = isset($validated['alatkamanan']) && is_array($validated['alatkamanan'])
            ? $validated['alatkamanan']
            : [];
        $alatStr = implode(', ', array_unique(array_filter(array_map('trim', $parts))));
        if (strlen($alatStr) > 255) {
            throw ValidationException::withMessages([
                'alatkamanan' => ['Gabungan alat keamanan terlalu panjang. Kurangi pilihan.'],
            ]);
        }
        $validated['alatkamanan'] = $alatStr !== '' ? $alatStr : null;

        foreach (['namecp', 'cp'] as $key) {
            if (! array_key_exists($key, $validated) || $validated[$key] === null) {
                $validated[$key] = '';
            }
        }

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

    private function applyHierarchy($query): void
    {
        $user = Auth::user();
        if (! $user || HierarchicalHelper::viewerBypassesTerritoryScope()) {
            return;
        }

        HierarchicalHelper::applyTerritoryScopeForTable($query, 'objek_wisata', 't');
    }
}
