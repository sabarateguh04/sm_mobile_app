<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class MasterKlUnitKerjaController extends Controller
{
    protected function normalizeName(string $name): string
    {
        return mb_strtoupper(trim($name), 'UTF-8');
    }

    public function index(Request $request)
    {
        if (! Schema::hasTable('master_kl_unit_kerja')) {
            abort(404);
        }

        $resorts = DB::table('master_kl_resort as r')
            ->join('master_kl_daerah as d', 'd.id', '=', 'r.parent_id')
            ->orderBy('d.nama')
            ->orderBy('r.nama')
            ->get(['r.id', 'r.nama as resort_nama', 'd.nama as daerah_nama']);

        $q = DB::table('master_kl_unit_kerja as u')
            ->join('master_kl_resort as r', 'r.id', '=', 'u.parent_id')
            ->join('master_kl_daerah as d', 'd.id', '=', 'r.parent_id')
            ->select(
                'u.id',
                'u.nama',
                'u.parent_id',
                'r.nama as resort_nama',
                'd.nama as daerah_nama'
            )
            ->orderBy('d.nama')
            ->orderBy('r.nama')
            ->orderBy('u.nama');

        if ($request->filled('search')) {
            $s = $request->search;
            $q->where(function ($qq) use ($s): void {
                $qq->where('u.nama', 'like', "%{$s}%")
                    ->orWhere('r.nama', 'like', "%{$s}%")
                    ->orWhere('d.nama', 'like', "%{$s}%");
            });
        }

        if ($request->filled('parent_id')) {
            $q->where('u.parent_id', $request->parent_id);
        }

        $perPage = max(1, (int) $request->get('per_page', 10));
        $rows = $q->paginate($perPage)->withQueryString();

        return view('master.kl_unit_kerja.index', compact('rows', 'resorts'));
    }

    public function create(Request $request)
    {
        if (! Schema::hasTable('master_kl_unit_kerja')) {
            abort(404);
        }

        $resorts = DB::table('master_kl_resort as r')
            ->join('master_kl_daerah as d', 'd.id', '=', 'r.parent_id')
            ->orderBy('d.nama')
            ->orderBy('r.nama')
            ->get(['r.id', 'r.nama as resort_nama', 'd.nama as daerah_nama']);
        $preselectedParent = $request->query('parent_id');

        return view('master.kl_unit_kerja.create', compact('resorts', 'preselectedParent'));
    }

    public function store(Request $request)
    {
        if (! Schema::hasTable('master_kl_unit_kerja')) {
            abort(404);
        }

        $validated = $request->validate([
            'nama' => ['required', 'string', 'max:255'],
            'parent_id' => ['required', 'integer', 'exists:master_kl_resort,id'],
        ]);

        DB::table('master_kl_unit_kerja')->insert([
            'nama' => $this->normalizeName($validated['nama']),
            'parent_id' => $validated['parent_id'],
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return redirect()
            ->route('master.unit-kerja.index', ['parent_id' => $validated['parent_id']])
            ->with('success', 'Data unit kerja berhasil ditambahkan.');
    }

    public function edit(string $id)
    {
        if (! Schema::hasTable('master_kl_unit_kerja')) {
            abort(404);
        }

        $row = DB::table('master_kl_unit_kerja')->where('id', $id)->first();
        if ($row === null) {
            abort(404);
        }

        $resorts = DB::table('master_kl_resort as r')
            ->join('master_kl_daerah as d', 'd.id', '=', 'r.parent_id')
            ->orderBy('d.nama')
            ->orderBy('r.nama')
            ->get(['r.id', 'r.nama as resort_nama', 'd.nama as daerah_nama']);

        return view('master.kl_unit_kerja.edit', compact('row', 'resorts'));
    }

    public function update(Request $request, string $id)
    {
        if (! Schema::hasTable('master_kl_unit_kerja')) {
            abort(404);
        }

        if (! DB::table('master_kl_unit_kerja')->where('id', $id)->exists()) {
            abort(404);
        }

        $validated = $request->validate([
            'nama' => ['required', 'string', 'max:255'],
            'parent_id' => ['required', 'integer', 'exists:master_kl_resort,id'],
        ]);

        DB::table('master_kl_unit_kerja')->where('id', $id)->update([
            'nama' => $this->normalizeName($validated['nama']),
            'parent_id' => $validated['parent_id'],
            'updated_at' => now(),
        ]);

        return redirect()
            ->route('master.unit-kerja.index', ['parent_id' => $validated['parent_id']])
            ->with('success', 'Data unit kerja berhasil diperbarui.');
    }

    public function destroy(string $id)
    {
        if (! Schema::hasTable('master_kl_unit_kerja')) {
            abort(404);
        }

        DB::table('master_kl_unit_kerja')->where('id', $id)->delete();

        return redirect()->route('master.unit-kerja.index')->with('success', 'Data unit kerja berhasil dihapus.');
    }
}
