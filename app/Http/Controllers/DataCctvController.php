<?php

namespace App\Http\Controllers;

use App\Helpers\HierarchicalHelper;
use App\Support\TerritoryIndexFilters;
use App\Traits\ExportsIndexQuery;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

/**
 * Data CCTV (inventaris TIK) — baca dari view `wil_datacctv`; tulis ke `datacctv` / inventaris `patrolicctv`.
 */
class DataCctvController extends BaseController
{
    use ExportsIndexQuery;

    public static function baseTableForWrite(): ?string
    {
        if (Schema::hasTable('datacctv')) {
            return 'datacctv';
        }

        if (Schema::hasTable('patrolicctv')
            && (Schema::hasColumn('patrolicctv', 'owner') || Schema::hasColumn('patrolicctv', 'brand'))) {
            return 'patrolicctv';
        }

        return null;
    }

    public static function readTableForUser(): string
    {
        if (Schema::hasView('wil_datacctv')) {
            return 'wil_datacctv';
        }

        if (Schema::hasView('nas_datacctv')) {
            return 'nas_datacctv';
        }

        return self::baseTableForWrite() ?? 'wil_datacctv';
    }

    public static function primaryKeyForTable(?string $table = null): string
    {
        $table = $table ?? self::baseTableForWrite() ?? 'datacctv';

        return CctvController::resolveCctvPrimaryKey($table);
    }

    public function index(Request $request): View
    {
        $table = self::readTableForUser();

        if (! Schema::hasTable($table) && ! Schema::hasView($table)) {
            return view('datacctv.index', [
                'data' => null,
                'tableMissing' => true,
                'sourceTable' => $table,
                'writeTable' => self::baseTableForWrite(),
                'owners' => collect(),
                'brands' => collect(),
                'fungsiList' => collect(),
                'lokasiList' => collect(),
                'sboViewOnly' => \App\Support\RoleMutationPolicy::deniesAllMutations(Auth::user()),
            ]);
        }

        $query = DB::table($table.' as d')
            ->leftJoin('usm', 'd.piket', '=', 'usm.id_usm')
            ->select(
                'd.*',
                'usm.nama as usm_nama',
                'usm.daerah as usm_daerah',
                'usm.wilayah as usm_wilayah'
            );

        if (CctvController::usesPatrolSchema()) {
            $query->where(function ($q) {
                $q->where('d.owner', '!=', '-')
                    ->whereNotNull('d.owner')
                    ->where('d.owner', '!=', '');
            });
        }

        HierarchicalHelper::applyTerritoryScopeForTable($query, $table, 'd');
        $this->applyIndexFilters($query, $request, 'd');

        $data = $query->orderByDesc('d.tanggal')
            ->orderByDesc('d.id_datacctv')
            ->paginate(20)
            ->withQueryString();

        return view('datacctv.index', array_merge([
            'data' => $data,
            'tableMissing' => false,
            'sourceTable' => $table,
            'writeTable' => self::baseTableForWrite() ?? 'datacctv',
            'owners' => $this->ownerOptions(),
            'brands' => $this->brandOptions(),
            'fungsiList' => $this->fungsiOptions(),
            'lokasiList' => $this->lokasiOptions(),
            'sboViewOnly' => \App\Support\RoleMutationPolicy::deniesAllMutations(Auth::user()),
        ], TerritoryIndexFilters::viewBundle($request)));
    }

    public function export(Request $request, string $format = 'excel')
    {
        return $this->exportIndexQuery(
            $request,
            $format,
            fn (Request $req) => $this->buildIndexQuery($req),
            'datacctv',
            'Data CCTV',
            'd.'.DataCctvController::primaryKeyForTable(self::readTableForUser())
        );
    }

    protected function buildIndexQuery(Request $request)
    {
        $table = self::readTableForUser();

        $query = DB::table($table.' as d')
            ->leftJoin('usm', 'd.piket', '=', 'usm.id_usm')
            ->select(
                'd.*',
                'usm.nama as usm_nama',
                'usm.daerah as usm_daerah',
                'usm.wilayah as usm_wilayah'
            );

        if (CctvController::usesPatrolSchema()) {
            $query->where(function ($q) {
                $q->where('d.owner', '!=', '-')
                    ->whereNotNull('d.owner')
                    ->where('d.owner', '!=', '');
            });
        }

        HierarchicalHelper::applyTerritoryScopeForTable($query, $table, 'd');
        $this->applyIndexFilters($query, $request, 'd');

        return $query->orderByDesc('d.tanggal')->orderByDesc('d.id_datacctv');
    }

    public function create(): View
    {
        \App\Support\RoleMutationPolicy::assertCanCreate(\Illuminate\Support\Facades\Auth::user());
        abort_unless(self::baseTableForWrite(), 503, 'Tabel inventaris CCTV belum tersedia.');

        return view('datacctv.create', [
            'owners' => $this->ownerOptions(),
            'fungsiList' => $this->fungsiOptions(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        \App\Support\RoleMutationPolicy::assertCanCreate(\Illuminate\Support\Facades\Auth::user());
        $table = self::baseTableForWrite();
        abort_unless($table, 503, 'Tabel inventaris CCTV belum tersedia.');

        $validated = $this->validateInput($request);
        $payload = $this->prepareInventarisPayload($validated, $request, $table);

        $pk = self::primaryKeyForTable($table);
        $payload[$pk] = (int) DB::table($table)->max($pk) + 1;
        DB::table($table)->insert($payload);

        return redirect()->route('datacctv.index')->with('success', 'Data CCTV berhasil ditambahkan.');
    }

    public function show(int $id): View
    {
        $data = $this->findWithPetugas($id);
        abort_unless($data, 404, 'Data tidak ditemukan.');

        return view('datacctv.show', [
            'data' => $data,
            'writeTable' => self::baseTableForWrite() ?? 'datacctv',
            'sboViewOnly' => \App\Support\RoleMutationPolicy::deniesAllMutations(Auth::user()),
        ]);
    }

    public function edit(int $id): View
    {
        abort_unless(self::baseTableForWrite(), 503, 'Tabel inventaris CCTV belum tersedia.');

        $data = $this->findReadableRow($id);
        abort_unless($data, 404, 'Data tidak ditemukan.');

        $this->assertOperatorOwnsOperationalRow($data, self::baseTableForWrite() ?: 'datacctv');

        return view('datacctv.edit', [
            'data' => $data,
            'owners' => $this->ownerOptions(),
            'fungsiList' => $this->fungsiOptions(),
        ]);
    }

    public function update(Request $request, int $id): RedirectResponse
    {
        $table = self::baseTableForWrite();
        abort_unless($table, 503, 'Tabel inventaris CCTV belum tersedia.');

        $existing = $this->findWritableRow($id);
        abort_unless($existing, 404, 'Data tidak ditemukan.');

        $this->assertOperatorOwnsOperationalRow($existing, $table);

        $validated = $this->validateInput($request, true);
        $payload = $this->prepareInventarisPayload($validated, $request, $table, $existing);

        $pk = self::primaryKeyForTable($table);
        DB::table($table)->where($pk, $id)->update($payload);

        return redirect()->route('datacctv.index')->with('success', 'Data CCTV berhasil diperbarui.');
    }

    public function destroy(int $id): RedirectResponse
    {
        $table = self::baseTableForWrite();
        abort_unless($table, 503, 'Tabel inventaris CCTV belum tersedia.');

        $existing = $this->findWritableRow($id);
        abort_unless($existing, 404, 'Data tidak ditemukan.');

        $this->assertOperatorOwnsOperationalRow($existing, $table);

        $fotoCol = $this->fotoColumn($table);
        if ($fotoCol && ! empty($existing->{$fotoCol})) {
            $this->deleteStoredFoto($existing->{$fotoCol});
        }

        $pk = self::primaryKeyForTable($table);
        DB::table($table)->where($pk, $id)->delete();

        return redirect()->route('datacctv.index')->with('success', 'Data CCTV berhasil dihapus.');
    }

    /**
     * @return \Illuminate\Support\Collection<int, string>
     */
    private function ownerOptions()
    {
        if (Schema::hasTable('t_ownercctv')) {
            return DB::table('t_ownercctv')->orderBy('nameowner')->pluck('nameowner');
        }

        $table = self::readTableForUser();
        if (! Schema::hasTable($table) && ! Schema::hasView($table)) {
            return collect();
        }

        $query = DB::table($table)
            ->whereNotNull('owner')
            ->where('owner', '!=', '')
            ->where('owner', '!=', '-');

        if ($table === 'wil_datacctv' || $table === 'nas_datacctv') {
            HierarchicalHelper::applyTerritoryScopeForTable($query, $table);
        }

        return $query->distinct()->orderBy('owner')->pluck('owner');
    }

    /**
     * @return \Illuminate\Support\Collection<int, string>
     */
    private function brandOptions()
    {
        $table = self::baseTableForWrite() ?? self::readTableForUser();
        if (! Schema::hasTable($table) && ! Schema::hasView($table)) {
            return collect();
        }

        $query = DB::table($table)
            ->whereNotNull('brand')
            ->where('brand', '!=', '')
            ->where('brand', '!=', '-');

        if ($table === 'wil_datacctv' || $table === 'nas_datacctv') {
            HierarchicalHelper::applyTerritoryScopeForTable($query, $table);
        }

        return $query->distinct()->orderBy('brand')->pluck('brand');
    }

    /**
     * @return \Illuminate\Support\Collection<int, string>
     */
    private function fungsiOptions()
    {
        $defaults = collect(['Monitoring', 'Traffic Analytic', 'Deteksi Kecepatan']);
        $table = self::readTableForUser();
        if (! Schema::hasTable($table) && ! Schema::hasView($table)) {
            return $defaults;
        }

        $query = DB::table($table)
            ->whereNotNull('fungsicctv')
            ->where('fungsicctv', '!=', '');

        if ($table === 'wil_datacctv' || $table === 'nas_datacctv') {
            HierarchicalHelper::applyTerritoryScopeForTable($query, $table);
        }

        $fromDb = $query->distinct()->orderBy('fungsicctv')->pluck('fungsicctv');

        return $defaults->merge($fromDb)->unique()->values();
    }

    /**
     * @return \Illuminate\Support\Collection<int, string>
     */
    private function lokasiOptions()
    {
        $table = self::readTableForUser();
        if (! Schema::hasTable($table) && ! Schema::hasView($table)) {
            return collect();
        }

        $query = DB::table($table)
            ->whereNotNull('lokasi')
            ->where('lokasi', '!=', '');

        if ($table === 'wil_datacctv' || $table === 'nas_datacctv') {
            HierarchicalHelper::applyTerritoryScopeForTable($query, $table);
        }

        return $query->distinct()->orderBy('lokasi')->pluck('lokasi');
    }

    private function validateInput(Request $request, bool $isUpdate = false): array
    {
        // SBO-REQ: Make latitude and longitude optional, even when uploading foto_fisik.
        $rules = [
            'tanggal' => 'required|date',
            'owner' => 'required|string|max:100',
            'lokasi' => 'nullable|string|max:100',
            'ipaddres' => 'required|string|max:100',
            'brand' => 'required|string|max:100',
            'fungsicctv' => 'nullable|string|max:100',
            'lat' => ['nullable', 'numeric', 'between:-90,90'],
            'lng' => ['nullable', 'numeric', 'between:-180,180'],
            'foto_fisik' => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif,webp', 'max:8192'],
        ];

        return $request->validate($rules, [
            'lat.required' => 'Koordinat GPS wajib saat mengunggah foto. Izinkan akses lokasi atau klik "Ambil lokasi GPS".',
            'lng.required' => 'Koordinat GPS wajib saat mengunggah foto.',
        ]);
    }

    /**
     * @param  array<string, mixed>  $validated
     * @return array<string, mixed>
     */
    private function prepareInventarisPayload(array $validated, Request $request, string $table, ?object $existing = null): array
    {
        $data = [
            'piket' => $existing ? $existing->piket : (string) Auth::id(),
            'owner' => $validated['owner'],
            'lokasi' => $validated['lokasi'] ?? '',
            'ipaddres' => $validated['ipaddres'],
            'brand' => $validated['brand'],
            'fungsicctv' => $validated['fungsicctv'] ?? null,
            'tanggal' => $validated['tanggal'],
        ];

        $coord = $this->koordinatStringFromRequest($request);
        if ($coord !== null) {
            $data['koordinat'] = $coord;
        } elseif ($existing && ! empty($existing->koordinat)) {
            $data['koordinat'] = $existing->koordinat;
        } elseif (Schema::hasColumn($table, 'koordinat')) {
            $data['koordinat'] = '';
        }

        $fotoCol = $this->fotoColumn($table);
        if ($fotoCol) {
            $existingFoto = $existing ? ($existing->{$fotoCol} ?? '') : '';
            if ($request->hasFile('foto_fisik')) {
                $this->deleteStoredFoto($existingFoto);
                $stored = $request->file('foto_fisik')->store('datacctv_foto', 'public');
                $data[$fotoCol] = '/storage/'.$stored;
            } elseif ($existing) {
                $data[$fotoCol] = $existingFoto;
            } else {
                $data[$fotoCol] = '';
            }
        }

        return $data;
    }

    private function fotoColumn(string $table): ?string
    {
        if (Schema::hasColumn($table, 'fotofisik')) {
            return 'fotofisik';
        }
        if (Schema::hasColumn($table, 'fotosit')) {
            return 'fotosit';
        }

        return null;
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

    private function applyIndexFilters($query, Request $request, string $prefix): void
    {
        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(function ($q) use ($s, $prefix) {
                $q->where($prefix.'.owner', 'like', "%{$s}%")
                    ->orWhere($prefix.'.lokasi', 'like', "%{$s}%")
                    ->orWhere($prefix.'.ipaddres', 'like', "%{$s}%")
                    ->orWhere($prefix.'.brand', 'like', "%{$s}%")
                    ->orWhere($prefix.'.fungsicctv', 'like', "%{$s}%")
                    ->orWhere('usm.nama', 'like', "%{$s}%");
            });
        }

        if ($request->filled('owner')) {
            $query->where($prefix.'.owner', $request->owner);
        }

        if ($request->filled('lokasi')) {
            $query->where($prefix.'.lokasi', $request->lokasi);
        }

        if ($request->filled('brand')) {
            $query->where($prefix.'.brand', $request->brand);
        }

        if ($request->filled('fungsicctv')) {
            $query->where($prefix.'.fungsicctv', $request->fungsicctv);
        }

        if ($request->filled('tanggal_dari')) {
            $query->whereDate($prefix.'.tanggal', '>=', $request->tanggal_dari);
        }

        if ($request->filled('tanggal_sampai')) {
            $query->whereDate($prefix.'.tanggal', '<=', $request->tanggal_sampai);
        }

        TerritoryIndexFilters::applyUsmFilters($query, $request);
    }

    private function findWithPetugas(int $id): ?object
    {
        return $this->findReadableRow($id);
    }

    private function findReadableRow(int $id): ?object
    {
        $table = self::readTableForUser();
        if (! Schema::hasTable($table) && ! Schema::hasView($table)) {
            return null;
        }

        $query = DB::table($table.' as d')
            ->leftJoin('usm', 'd.piket', '=', 'usm.id_usm')
            ->select(
                'd.*',
                'usm.nama as usm_nama',
                'usm.daerah as usm_daerah',
                'usm.wilayah as usm_wilayah'
            )
            ->where('d.id_datacctv', $id);

        if (CctvController::usesPatrolSchema() && $table !== 'wil_datacctv' && $table !== 'nas_datacctv') {
            $query->where(function ($q) {
                $q->where('d.owner', '!=', '-')
                    ->whereNotNull('d.owner')
                    ->where('d.owner', '!=', '');
            });
        }

        HierarchicalHelper::applyTerritoryScopeForTable($query, $table, 'd');

        return $this->enrichReadableRow($query->first());
    }

    /**
     * View `wil_datacctv` tidak selalu menyertakan kolom foto — ambil dari tabel dasar bila perlu.
     */
    private function enrichReadableRow(?object $row): ?object
    {
        if (! $row) {
            return null;
        }

        $writeTable = self::baseTableForWrite();
        if (! $writeTable) {
            return $row;
        }

        $fotoCol = $this->fotoColumn($writeTable);
        if (! $fotoCol || ! empty($row->{$fotoCol})) {
            return $row;
        }

        $pk = self::primaryKeyForTable($writeTable);
        $id = $row->id_datacctv ?? null;
        if (! $id) {
            return $row;
        }

        $foto = DB::table($writeTable)->where($pk, $id)->value($fotoCol);
        if ($foto !== null) {
            $row->{$fotoCol} = $foto;
        }

        return $row;
    }

    private function findWritableRow(int $id): ?object
    {
        $table = self::baseTableForWrite();
        if (! $table) {
            return null;
        }

        $pk = self::primaryKeyForTable($table);
        $query = DB::table($table.' as d')
            ->leftJoin('usm', 'd.piket', '=', 'usm.id_usm')
            ->select('d.*', 'usm.nama as usm_nama', 'usm.daerah as usm_daerah', 'usm.wilayah as usm_wilayah')
            ->where('d.'.$pk, $id);

        HierarchicalHelper::applyTerritoryScopeForTable($query, $table, 'd');

        return $query->first();
    }

    private function deleteStoredFoto(?string $path): void
    {
        if (empty($path)) {
            return;
        }

        foreach (explode(',', $path) as $raw) {
            $p = trim($raw);
            if ($p === '' || preg_match('#^https?://#i', $p)) {
                continue;
            }
            $p = preg_replace('#^/storage/#', '', $p);
            Storage::disk('public')->delete($p);
        }
    }
}
