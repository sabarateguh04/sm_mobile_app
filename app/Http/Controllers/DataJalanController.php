<?php

namespace App\Http\Controllers;

use App\Helpers\HierarchicalHelper;
use App\Models\DataJalan;
use App\Support\TerritoryIndexFilters;
use App\Traits\Exportable;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

/**
 * CRUD + report untuk tabel `t_datajalan` (database sbo / produksi).
 */
class DataJalanController extends BaseController
{
    use Exportable;

    protected function reportPageTitle(): string
    {
        return 'Data Jalan';
    }

    protected function baseQuery()
    {
        return DB::table('t_datajalan')
            ->leftJoin('usm', 't_datajalan.piket', '=', 'usm.id_usm')
            ->select(
                't_datajalan.*',
                'usm.nama as usm_nama',
                DB::raw('usm.daerah as daerah_nama'),
                DB::raw('usm.wilayah as wilayah_nama')
            );
    }

    protected function applyReportFilters($query, Request $request): void
    {
        if ($request->filled('tanggal_dari')) {
            $query->where('t_datajalan.tanggal', '>=', $request->tanggal_dari);
        }
        if ($request->filled('tanggal_sampai')) {
            $query->where('t_datajalan.tanggal', '<=', $request->tanggal_sampai);
        }
        if ($request->filled('jenisjalan')) {
            $query->where('t_datajalan.jenisjalan', $request->jenisjalan);
        }
        if ($request->filled('lintasan')) {
            $query->where('t_datajalan.lintasan', $request->lintasan);
        }
        if ($request->filled('prasjal')) {
            $query->where('t_datajalan.prasjal', 'like', '%'.$request->prasjal.'%');
        }
        if ($request->filled('panjang_min')) {
            $query->where('t_datajalan.panjangjalan', '>=', (int) $request->panjang_min);
        }
        if ($request->filled('panjang_max')) {
            $query->where('t_datajalan.panjangjalan', '<=', (int) $request->panjang_max);
        }

        TerritoryIndexFilters::applyUsmFilters($query, $request);
    }

    /**
     * @return list<string>
     */
    protected function prasjalOptionsForFilter(): array
    {
        if (Schema::hasTable('t_prasjal')) {
            return DB::table('t_prasjal')->orderBy('nameprasjal')->pluck('nameprasjal')->filter()->values()->all();
        }

        return DB::table('t_datajalan')
            ->whereNotNull('prasjal')
            ->where('prasjal', '!=', '')
            ->pluck('prasjal')
            ->flatMap(fn ($v) => array_map('trim', explode(',', (string) $v)))
            ->filter()
            ->unique()
            ->sort()
            ->values()
            ->all();
    }

    public function index(Request $request)
    {
        $tab = $request->get('tab', 'rinci');

        $query = $this->baseQuery();
        HierarchicalHelper::applyTerritoryScopeForTable($query, 't_datajalan');
        $this->applyReportFilters($query, $request);

        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(function ($q) use ($s) {
                $q->where('t_datajalan.namajalan', 'like', "%{$s}%")
                    ->orWhere('t_datajalan.jenisjalan', 'like', "%{$s}%")
                    ->orWhere('t_datajalan.lintasan', 'like', "%{$s}%")
                    ->orWhere('t_datajalan.prasjal', 'like', "%{$s}%")
                    ->orWhere('usm.nama', 'like', "%{$s}%")
                    ->orWhere('usm.daerah', 'like', "%{$s}%")
                    ->orWhere('usm.wilayah', 'like', "%{$s}%");
            });
        }

        $data = $query->orderBy('t_datajalan.tanggal', 'desc')->paginate(20)->withQueryString();

        $tabulasi = collect();
        if ($tab === 'tabulasi') {
            $tabQ = DB::table('t_datajalan')
                ->leftJoin('usm', 't_datajalan.piket', '=', 'usm.id_usm');
            HierarchicalHelper::applyTerritoryScopeForTable($tabQ, 't_datajalan');
            $this->applyReportFilters($tabQ, $request);

            $tabulasi = $tabQ
                ->selectRaw('COALESCE(NULLIF(usm.daerah, ""), "-") as daerah_nama')
                ->selectRaw('COALESCE(NULLIF(t_datajalan.jenisjalan, ""), "-") as jenis_jalan')
                ->selectRaw('COUNT(*) as jumlah')
                ->selectRaw('SUM(t_datajalan.panjangjalan) as total_panjang')
                ->groupBy('daerah_nama', 'jenis_jalan')
                ->orderBy('daerah_nama')
                ->orderBy('jenis_jalan')
                ->get();
        }

        $jenisJalanList = Schema::hasTable('t_jenisjalan')
            ? DB::table('t_jenisjalan')->orderBy('namajenis')->pluck('namajenis')
            : DB::table('t_datajalan')->whereNotNull('jenisjalan')->distinct()->orderBy('jenisjalan')->pluck('jenisjalan');

        return view('data_jalan.index', array_merge([
            'pageTitle' => $this->reportPageTitle(),
            'data' => $data,
            'tabulasi' => $tabulasi,
            'tab' => $tab,
            'jenisJalanList' => $jenisJalanList,
            'prasjalList' => $this->prasjalOptionsForFilter(),
        ], TerritoryIndexFilters::viewBundle($request)));
    }

    public function export(Request $request, string $format = 'excel')
    {
        $query = $this->baseQuery();
        HierarchicalHelper::applyTerritoryScopeForTable($query, 't_datajalan');
        $this->applyReportFilters($query, $request);

        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(function ($q) use ($s) {
                $q->where('t_datajalan.namajalan', 'like', "%{$s}%")
                    ->orWhere('t_datajalan.jenisjalan', 'like', "%{$s}%")
                    ->orWhere('t_datajalan.lintasan', 'like', "%{$s}%");
            });
        }

        if ($request->filled('id')) {
            $query->where('t_datajalan.id_datajalan', $request->id);
        }

        $rows = $query->orderBy('t_datajalan.tanggal', 'desc')->get();
        $isSingle = $request->filled('id') && $rows->count() === 1;
        $filename = $request->get('filename', $isSingle
            ? 't_datajalan_'.$request->id.'_'.date('Y-m-d')
            : 't_datajalan_'.date('Y-m-d'));
        $title = $isSingle ? 'Detail '.$this->reportPageTitle() : $this->reportPageTitle();

        return match (strtolower($format)) {
            'pdf' => $this->exportToPdf($rows, 'exports.pdf.default', $filename, $title),
            'csv' => $this->exportToCsv($rows, $filename),
            default => $this->exportToExcel($rows, $filename, $title),
        };
    }

    public function create()
    {
        \App\Support\RoleMutationPolicy::assertCanCreate(\Illuminate\Support\Facades\Auth::user());
        $jenisJalan = Schema::hasTable('t_jenisjalan')
            ? DB::table('t_jenisjalan')->orderBy('namajenis')->pluck('namajenis')
            : DB::table('t_datajalan')->whereNotNull('jenisjalan')->distinct()->orderBy('jenisjalan')->pluck('jenisjalan');

        return view('data_jalan.create', [
            'pageTitle' => 'Tambah Data Jalan',
            'jenisJalan' => $jenisJalan,
            'prasjalOptions' => $this->prasjalCheckboxOptions(),
            'lintasanOptions' => $this->lintasanOptions(),
        ]);
    }

    public function store(Request $request)
    {
        \App\Support\RoleMutationPolicy::assertCanCreate(\Illuminate\Support\Facades\Auth::user());
        $validated = $this->validateInput($request);
        unset($validated['foto_lokasi'], $validated['lat'], $validated['lng']);
        $coord = $this->koordinatStringFromRequest($request);
        $validated['koordinat'] = $coord !== null ? $coord : '';
        $validated['piket'] = (string) Auth::id();

        if ($request->hasFile('foto_lokasi')) {
            $validated['fotoobject'] = $request->file('foto_lokasi')->store('datajalan_foto', 'public');
        } else {
            $validated['fotoobject'] = '';
        }

        DataJalan::create($validated);

        return redirect()->route('t_datajalan.index')->with('success', 'Data jalan berhasil ditambah.');
    }

    public function show($id)
    {
        $data = $this->baseQuery()->where('t_datajalan.id_datajalan', $id)->first();
        abort_unless($data, 404, 'Data tidak ditemukan');

        return view('data_jalan.show', array_merge([
            'pageTitle' => 'Detail Data Jalan',
            'data' => $data,
        ], $this->operationalRecordViewFlags($data, 't_datajalan')));
    }

    public function edit($id)
    {
        $data = DataJalan::findOrFail($id);
        $this->assertOperatorOwnsOperationalRow($data, 't_datajalan');
        $jenisJalan = Schema::hasTable('t_jenisjalan')
            ? DB::table('t_jenisjalan')->orderBy('namajenis')->pluck('namajenis')
            : DB::table('t_datajalan')->whereNotNull('jenisjalan')->distinct()->orderBy('jenisjalan')->pluck('jenisjalan');

        return view('data_jalan.edit', [
            'pageTitle' => 'Edit Data Jalan',
            'data' => $data,
            'jenisJalan' => $jenisJalan,
            'prasjalOptions' => $this->prasjalCheckboxOptions(),
            'lintasanOptions' => $this->lintasanOptions(),
        ]);
    }

    public function update(Request $request, $id)
    {
        $data = DataJalan::findOrFail($id);
        $this->assertOperatorOwnsOperationalRow($data, 't_datajalan');
        $validated = $this->validateInput($request);
        unset($validated['foto_lokasi'], $validated['lat'], $validated['lng']);
        $coord = $this->koordinatStringFromRequest($request);
        if ($coord !== null) {
            $validated['koordinat'] = $coord;
        } else {
            unset($validated['koordinat']);
        }

        if ($request->hasFile('foto_lokasi')) {
            $this->deleteStoredFoto($data->fotoobject);
            $validated['fotoobject'] = $request->file('foto_lokasi')->store('datajalan_foto', 'public');
        }

        $data->update($validated);

        return redirect()->route('t_datajalan.index')->with('success', 'Data jalan berhasil diubah.');
    }

    public function destroy($id)
    {
        $data = DataJalan::findOrFail($id);
        $this->assertOperatorOwnsOperationalRow($data, 't_datajalan');
        $this->deleteStoredFoto($data->fotoobject);
        $data->delete();

        return redirect()->route('t_datajalan.index')->with('success', 'Data jalan berhasil dihapus.');
    }

    /** @return list<string> */
    protected function lintasanOptions(): array
    {
        return ['Dalam Kota', 'Antar Kota'];
    }

    /** @return list<string> */
    protected function prasjalCheckboxOptions(): array
    {
        if (! Schema::hasTable('t_prasjal')) {
            return [
                'Marka Jalan', 'Rambu Lalu Lintas', 'APILL',
                'Penerangan Jalan', 'Pengendali & Pengaman Pengguna Jalan',
            ];
        }

        return DB::table('t_prasjal')->pluck('nameprasjal')->filter()->values()->all();
    }

    private function validateInput(Request $request): array
    {
        $prasOpts = $this->prasjalCheckboxOptions();
        $lintasanOpts = $this->lintasanOptions();

        $validated = $request->validate([
            'tanggal' => 'required|date',
            'jenisjalan' => 'required|string|max:100',
            'namajalan' => 'required|string|max:100',
            'panjangjalan' => 'required|integer|min:0|max:99999',
            'lintasan' => ['required', 'string', 'max:100', Rule::in($lintasanOpts)],
            'prasjal' => ['nullable', 'array'],
            'prasjal.*' => ['string', 'max:120', Rule::in($prasOpts)],
            'lat' => ['nullable', 'numeric', 'between:-90,90'],
            'lng' => ['nullable', 'numeric', 'between:-180,180'],
            'foto_lokasi' => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif,webp', 'max:8192'],
        ]);

        $parts = isset($validated['prasjal']) && is_array($validated['prasjal'])
            ? $validated['prasjal']
            : [];
        $prasStr = implode(',', array_unique(array_filter(array_map('trim', $parts))));
        if (strlen($prasStr) > 100) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'prasjal' => ['Gabungan prasarana jalan terlalu panjang (maks. 100 karakter). Kurangi pilihan.'],
            ]);
        }
        $validated['prasjal'] = $prasStr !== '' ? $prasStr : '';

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
        foreach (array_filter(array_map('trim', explode(',', $path))) as $part) {
            if (! preg_match('#^https?://#i', $part)) {
                Storage::disk('public')->delete($part);
            }
        }
    }
}
