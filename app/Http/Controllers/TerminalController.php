<?php

namespace App\Http\Controllers;

use App\Helpers\HierarchicalHelper;
use App\Models\DataTerminal;
use App\Traits\ExportsIndexQuery;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

/**
 * CRUD untuk tabel `dataterminal` (database sbo).
 * Filter wilayah/daerah dilakukan via JOIN ke `usm` lewat kolom `piket`.
 */
class TerminalController extends BaseController
{
    use ExportsIndexQuery;

    public function index(Request $request)
    {
        $query = DB::table('data_terminal')
            ->leftJoin('usm', 'data_terminal.piket', '=', 'usm.id_usm')
            ->select(
                'data_terminal.*',
                'usm.nama as usm_nama',
                'usm.daerah as usm_daerah',
                'usm.wilayah as usm_wilayah'
            );

        HierarchicalHelper::applyTerritoryScopeForTable($query, 'data_terminal');

        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(function ($q) use ($s) {
                $q->where('data_terminal.namaterminal', 'like', "%{$s}%")
                    ->orWhere('data_terminal.jenisterminal', 'like', "%{$s}%")
                    ->orWhere('data_terminal.jalan', 'like', "%{$s}%")
                    ->orWhere('data_terminal.cp', 'like', "%{$s}%");
            });
        }

        $data = $query->orderBy('data_terminal.tanggal', 'desc')->paginate(20)->withQueryString();
        $jenisTerminal = DB::table('t_jenisterminal')->pluck('namejenisterminal');
        return view('terminal.index', compact('data', 'jenisTerminal'));
    }

    public function export(Request $request, string $format = 'excel')
    {
        return $this->exportIndexQuery(
            $request,
            $format,
            fn (Request $req) => $this->buildIndexQuery($req),
            'data_terminal',
            'Data Terminal',
            'data_terminal.id_terminal'
        );
    }

    protected function buildIndexQuery(Request $request)
    {
        $query = DB::table('data_terminal')
            ->leftJoin('usm', 'data_terminal.piket', '=', 'usm.id_usm')
            ->select(
                'data_terminal.*',
                'usm.nama as usm_nama',
                'usm.daerah as usm_daerah',
                'usm.wilayah as usm_wilayah'
            );

        HierarchicalHelper::applyTerritoryScopeForTable($query, 'data_terminal');

        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(function ($q) use ($s) {
                $q->where('data_terminal.namaterminal', 'like', "%{$s}%")
                    ->orWhere('data_terminal.jenisterminal', 'like', "%{$s}%")
                    ->orWhere('data_terminal.jalan', 'like', "%{$s}%")
                    ->orWhere('data_terminal.cp', 'like', "%{$s}%");
            });
        }

        return $query->orderBy('data_terminal.tanggal', 'desc');
    }

    public function create()
    {
        \App\Support\RoleMutationPolicy::assertCanCreate(\Illuminate\Support\Facades\Auth::user());
        $jenisTerminal = DB::table('t_jenisterminal')->pluck('namejenisterminal');
        $fasilitasOptions = $this->fasilitasCheckboxOptions();

        return view('terminal.create', [
            'pageTitle' => 'Tambah Terminal',
            'jenisTerminal' => $jenisTerminal,
            'fasilitasOptions' => $fasilitasOptions,
        ]);
    }

    public function store(Request $request)
    {
        \App\Support\RoleMutationPolicy::assertCanCreate(\Illuminate\Support\Facades\Auth::user());
        $validated = $this->validateInput($request);
        unset($validated['lat'], $validated['lng'], $validated['foto_lokasi']);
        $coord = $this->koordinatStringFromRequest($request);
        $validated['koordinat'] = $coord !== null ? $coord : '';
        $validated['piket'] = (string) Auth::id();

        if ($request->hasFile('foto_lokasi')) {
            $validated['fotoobject'] = $request->file('foto_lokasi')->store('terminal_foto', 'public');
        }

        DataTerminal::create($validated);
        return redirect()->route('terminal.index')->with('success', 'Terminal berhasil ditambah.');
    }

    public function show($id)
    {
        $data = DB::table('data_terminal')
            ->leftJoin('usm', 'data_terminal.piket', '=', 'usm.id_usm')
            ->select('data_terminal.*', 'usm.nama as usm_nama', 'usm.daerah as usm_daerah', 'usm.wilayah as usm_wilayah')
            ->where('data_terminal.id_terminal', $id)->first();
        abort_unless($data, 404, 'Data tidak ditemukan');

        return view('terminal.show', array_merge(compact('data'), $this->operationalRecordViewFlags($data, 'data_terminal')));
    }

    public function edit($id)
    {
        $data = DataTerminal::findOrFail($id);
        $this->assertOperatorOwnsOperationalRow($data, 'data_terminal');
        $jenisTerminal = DB::table('t_jenisterminal')->pluck('namejenisterminal');
        $fasilitasOptions = $this->fasilitasCheckboxOptions();

        return view('terminal.edit', [
            'pageTitle' => 'Edit Terminal',
            'data' => $data,
            'jenisTerminal' => $jenisTerminal,
            'fasilitasOptions' => $fasilitasOptions,
        ]);
    }

    public function update(Request $request, $id)
    {
        $data = DataTerminal::findOrFail($id);
        $this->assertOperatorOwnsOperationalRow($data, 'data_terminal');
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
            $validated['fotoobject'] = $request->file('foto_lokasi')->store('terminal_foto', 'public');
        }

        $data->update($validated);
        return redirect()->route('terminal.index')->with('success', 'Terminal berhasil diubah.');
    }

    public function destroy($id)
    {
        $data = DataTerminal::findOrFail($id);
        $this->assertOperatorOwnsOperationalRow($data, 'data_terminal');
        $this->deleteStoredFoto($data->fotoobject);
        $data->delete();
        return redirect()->route('terminal.index')->with('success', 'Terminal berhasil dihapus.');
    }

    /**
     * Daftar fasilitas standar (checkbox), disimpan di DB sebagai string dipisah koma.
     *
     * @return list<string>
     */
    protected function fasilitasCheckboxOptions(): array
    {
        return [
            'Faskes / P3K',
            'Jalur evakuasi',
            'Ruang ibu menyusui / laktasi',
            'Toilet / MCK',
            'Tempat ibadah',
            'Parkir difabel',
            'Ruang tunggu khusus',
            'Wi-Fi publik',
            'Charger / stop kontak',
        ];
    }

    private function validateInput(Request $request): array
    {
        $fasOpts = $this->fasilitasCheckboxOptions();

        $validated = $request->validate([
            'jenisterminal' => 'required|string|max:100',
            'namaterminal' => 'required|string|max:100',
            'cp' => 'nullable|string|max:100',
            'namecp' => 'nullable|string|max:100',
            'fasilitas' => ['nullable', 'array'],
            'fasilitas.*' => ['string', 'max:120', Rule::in($fasOpts)],
            'jalan' => 'nullable|string|max:100',
            'lat' => ['nullable', 'numeric', 'between:-90,90'],
            'lng' => ['nullable', 'numeric', 'between:-180,180'],
            'foto_lokasi' => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif,webp', 'max:8192'],
            'tanggal' => 'required|date',
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

    /** Gabung lat/lng jadi kolom koordinat, atau null jika keduanya kosong (update: jangan timpa nilai lama). */
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
