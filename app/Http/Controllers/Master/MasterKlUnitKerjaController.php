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

        $instansis = DB::table('master_pemerintahan_instansi')->orderBy('nama_unit')->pluck('nama_unit', 'id');
        $orgs = DB::table('org')->orderBy('nameorg')->pluck('nameorg', 'id_org');

        $resorts = DB::table('master_kl_resort as r')
            ->orderBy('r.nama')
            ->get(['r.id', 'r.nama as resort_nama', 'r.instansi_id', 'r.org_id']);

        $q = DB::table('master_kl_unit_kerja as u')
            ->join('master_kl_resort as r', 'r.id', '=', 'u.parent_id')
            ->leftJoin('master_pemerintahan_instansi as i', 'i.id', '=', 'r.instansi_id')
            ->leftJoin('org as o', 'o.id_org', '=', 'r.org_id')
            ->select(
                'u.id',
                'u.nama',
                'u.parent_id',
                'r.nama as resort_nama',
                'i.nama_unit as instansi_nama',
                'o.nameorg as org_nama'
            )
            ->orderBy('r.nama')
            ->orderBy('u.nama');

        if ($request->filled('search')) {
            $s = $request->search;
            $q->where(function ($qq) use ($s): void {
                $qq->where('u.nama', 'like', "%{$s}%")
                    ->orWhere('r.nama', 'like', "%{$s}%")
                    ->orWhere('i.nama_unit', 'like', "%{$s}%")
                    ->orWhere('o.nameorg', 'like', "%{$s}%");
            });
        }

        if ($request->filled('parent_id')) {
            $q->where('u.parent_id', $request->parent_id);
        }

        $perPage = max(1, (int) $request->get('per_page', 10));
        $rows = $q->paginate($perPage)->withQueryString();

        return view('master.kl_unit_kerja.index', compact('rows', 'resorts', 'instansis', 'orgs'));
    }

    public function create(Request $request)
    {
        if (! Schema::hasTable('master_kl_unit_kerja')) {
            abort(404);
        }

        $instansis = DB::table('master_pemerintahan_instansi')->orderBy('nama_unit')->get();
        $orgs = DB::table('org')->orderBy('nameorg')->get();

        $resorts = DB::table('master_kl_resort as r')
            ->orderBy('r.nama')
            ->get(['r.id', 'r.nama as resort_nama', 'r.instansi_id', 'r.org_id']);
        $preselectedParent = $request->query('parent_id');

        return view('master.kl_unit_kerja.create', compact('instansis', 'orgs', 'resorts', 'preselectedParent'));
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

        $instansis = DB::table('master_pemerintahan_instansi')->orderBy('nama_unit')->get();
        $orgs = DB::table('org')->orderBy('nameorg')->get();

        $resorts = DB::table('master_kl_resort as r')
            ->orderBy('r.nama')
            ->get(['r.id', 'r.nama as resort_nama', 'r.instansi_id', 'r.org_id']);

        return view('master.kl_unit_kerja.edit', compact('row', 'instansis', 'orgs', 'resorts'));
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
