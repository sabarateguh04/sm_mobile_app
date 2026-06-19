<?php

namespace App\Http\Controllers;

use App\Helpers\HierarchicalHelper;
use App\Helpers\MenuHelper;
use App\Models\DataPerangkat;
use App\Support\RoleMutationPolicy;
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
 * Data Perangkat (inventaris TIK) — CRUD pada tabel `dataperangkat`;
 * index operator membaca view `wil_dataperangkat` bila tersedia.
 */
class DataPerangkatController extends BaseController
{
    use ExportsIndexQuery;

    public static function readTableForUser(): string
    {
        $user = Auth::user();
        if ($user && MenuHelper::isOperatorForLayout($user) && Schema::hasView('wil_dataperangkat')) {
            return 'wil_dataperangkat';
        }

        if (Schema::hasView('nas_datadevice')) {
            return 'nas_datadevice';
        }

        if (Schema::hasTable('dataperangkat')) {
            return 'dataperangkat';
        }

        return 'wil_dataperangkat';
    }

    public function index(Request $request): View
    {
        $table = self::readTableForUser();
        if (! Schema::hasTable($table) && ! Schema::hasView($table)) {
            return view('dataperangkat.index', [
                'data' => null,
                'tableMissing' => true,
                'sourceTable' => $table,
                'writeTable' => Schema::hasTable('dataperangkat') ? 'dataperangkat' : null,
                'kategori' => collect(),
                'owners' => collect(),
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

        HierarchicalHelper::applyTerritoryScopeForTable($query, $table, 'd');
        $this->applyIndexFilters($query, $request, 'd');

        $data = $query->orderByDesc('d.tanggal')
            ->orderByDesc('d.id_perangkat')
            ->paginate(20)
            ->withQueryString();

        return view('dataperangkat.index', array_merge([
            'data' => $data,
            'tableMissing' => false,
            'sourceTable' => $table,
            'writeTable' => Schema::hasTable('dataperangkat') ? 'dataperangkat' : null,
            'kategori' => $this->kategoriOptions(),
            'owners' => $this->ownerOptions(),
            'sboViewOnly' => \App\Support\RoleMutationPolicy::deniesAllMutations(Auth::user()),
        ], TerritoryIndexFilters::viewBundle($request)));
    }

    public function export(Request $request, string $format = 'excel')
    {
        return $this->exportIndexQuery(
            $request,
            $format,
            fn (Request $req) => $this->buildIndexQuery($req),
            'dataperangkat',
            'Data Perangkat',
            'd.id_perangkat'
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

        HierarchicalHelper::applyTerritoryScopeForTable($query, $table, 'd');
        $this->applyIndexFilters($query, $request, 'd');

        return $query->orderByDesc('d.tanggal')->orderByDesc('d.id_perangkat');
    }

    public function create(): View
    {
        \App\Support\RoleMutationPolicy::assertCanCreate(\Illuminate\Support\Facades\Auth::user());
        abort_unless(Schema::hasTable('dataperangkat'), 503, 'Tabel dataperangkat belum tersedia.');

        return view('dataperangkat.create', [
            'kategori' => $this->kategoriOptions(),
            'owners' => $this->ownerOptions(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        \App\Support\RoleMutationPolicy::assertCanCreate(\Illuminate\Support\Facades\Auth::user());
        abort_unless(Schema::hasTable('dataperangkat'), 503, 'Tabel dataperangkat belum tersedia.');

        $validated = $this->validateInput($request);
        $validated['piket'] = RoleMutationPolicy::resolveActorPiketForStore(Auth::user());
        unset($validated['foto_perangkat']);

        if ($request->hasFile('foto_perangkat')) {
            $validated['fotoperangkat'] = $request->file('foto_perangkat')->store('dataperangkat_foto', 'public');
        } else {
            $validated['fotoperangkat'] = '';
        }

        $row = DataPerangkat::create($validated);
        $actorPiket = RoleMutationPolicy::resolveActorPiketForStore(Auth::user());
        if ((string) $row->piket !== $actorPiket) {
            $row->forceFill(['piket' => $actorPiket])->save();
        }

        return redirect()->route('dataperangkat.index')->with('success', 'Data perangkat berhasil ditambahkan.');
    }

    public function show(int $id): View
    {
        $data = $this->findWithPetugas($id);
        abort_unless($data, 404, 'Data tidak ditemukan.');

        return view('dataperangkat.show', [
            'data' => $data,
            'writeTable' => 'dataperangkat',
            'sboViewOnly' => \App\Support\RoleMutationPolicy::deniesAllMutations(Auth::user()),
        ]);
    }

    public function edit(int $id): View
    {
        abort_unless(Schema::hasTable('dataperangkat'), 503, 'Tabel dataperangkat belum tersedia.');

        $data = DataPerangkat::findOrFail($id);
        RoleMutationPolicy::assertDataperangkatMutationAllowed(Auth::user(), $data);

        return view('dataperangkat.edit', [
            'data' => $data,
            'kategori' => $this->kategoriOptions(),
            'owners' => $this->ownerOptions(),
        ]);
    }

    public function update(Request $request, int $id): RedirectResponse
    {
        abort_unless(Schema::hasTable('dataperangkat'), 503, 'Tabel dataperangkat belum tersedia.');

        $data = DataPerangkat::findOrFail($id);
        RoleMutationPolicy::assertDataperangkatMutationAllowed(Auth::user(), $data);
        $validated = $this->validateInput($request);
        unset($validated['foto_perangkat']);

        if ($request->hasFile('foto_perangkat')) {
            $this->deleteStoredFoto($data->fotoperangkat);
            $validated['fotoperangkat'] = $request->file('foto_perangkat')->store('dataperangkat_foto', 'public');
        }

        $data->update($validated);

        return redirect()->route('dataperangkat.index')->with('success', 'Data perangkat berhasil diperbarui.');
    }

    public function destroy(int $id): RedirectResponse
    {
        abort_unless(Schema::hasTable('dataperangkat'), 503, 'Tabel dataperangkat belum tersedia.');

        $data = DataPerangkat::findOrFail($id);
        RoleMutationPolicy::assertDataperangkatMutationAllowed(Auth::user(), $data);
        $this->deleteStoredFoto($data->fotoperangkat);
        $data->delete();

        return redirect()->route('dataperangkat.index')->with('success', 'Data perangkat berhasil dihapus.');
    }

    /**
     * @return \Illuminate\Support\Collection<int, string>
     */
    private function kategoriOptions()
    {
        if (Schema::hasTable('t_katperangkat')) {
            return DB::table('t_katperangkat')->orderBy('namekatperangkat')->pluck('namekatperangkat');
        }

        if (! Schema::hasTable('dataperangkat')) {
            return collect();
        }

        return DB::table('dataperangkat')
            ->whereNotNull('katperangkat')
            ->where('katperangkat', '!=', '')
            ->distinct()
            ->orderBy('katperangkat')
            ->pluck('katperangkat');
    }

    /**
     * @return \Illuminate\Support\Collection<int, string>
     */
    private function ownerOptions()
    {
        if (! Schema::hasTable('dataperangkat')) {
            return collect();
        }

        return DB::table('dataperangkat')
            ->whereNotNull('owner')
            ->where('owner', '!=', '')
            ->distinct()
            ->orderBy('owner')
            ->pluck('owner');
    }

    private function validateInput(Request $request): array
    {
        return $request->validate([
            'tanggal' => 'required|date',
            'katperangkat' => 'required|string|max:100',
            'nameperangkat' => 'required|string|max:100',
            'lokasi' => 'required|string|max:100',
            'owner' => 'required|string|max:100',
            'qtty' => 'required|string|max:100',
            'supportfor' => 'nullable|string|max:100',
            'foto_perangkat' => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif,webp', 'max:8192'],
        ]);
    }

    private function applyIndexFilters($query, Request $request, string $prefix): void
    {
        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(function ($q) use ($s, $prefix) {
                $q->where($prefix.'.katperangkat', 'like', "%{$s}%")
                    ->orWhere($prefix.'.nameperangkat', 'like', "%{$s}%")
                    ->orWhere($prefix.'.lokasi', 'like', "%{$s}%")
                    ->orWhere($prefix.'.owner', 'like', "%{$s}%")
                    ->orWhere($prefix.'.supportfor', 'like', "%{$s}%")
                    ->orWhere('usm.nama', 'like', "%{$s}%");
            });
        }

        if ($request->filled('katperangkat')) {
            $query->where($prefix.'.katperangkat', $request->katperangkat);
        }

        if ($request->filled('owner')) {
            $query->where($prefix.'.owner', $request->owner);
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
        if (! Schema::hasTable('dataperangkat')) {
            return null;
        }

        $query = DB::table('dataperangkat as d')
            ->leftJoin('usm', 'd.piket', '=', 'usm.id_usm')
            ->select(
                'd.*',
                'usm.nama as usm_nama',
                'usm.daerah as usm_daerah',
                'usm.wilayah as usm_wilayah'
            )
            ->where('d.id_perangkat', $id);

        HierarchicalHelper::applyTerritoryScopeForTable($query, 'dataperangkat', 'd');

        return $query->first();
    }

    private function deleteStoredFoto(?string $path): void
    {
        if (empty($path) || preg_match('#^https?://#i', $path)) {
            return;
        }

        $first = trim(explode(',', $path)[0]);
        if ($first !== '' && ! preg_match('#^https?://#i', $first)) {
            Storage::disk('public')->delete($first);
        }
    }
}
