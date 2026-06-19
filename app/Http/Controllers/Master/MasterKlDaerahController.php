<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class MasterKlDaerahController extends Controller
{
    public function index(Request $request)
    {
        if (! Schema::hasTable('master_kl_daerah')) {
            abort(404);
        }

        $q = DB::table('master_kl_daerah as d')
            ->join('master_kl_pusat as p', 'p.id', '=', 'd.parent_id')
            ->select('d.*', 'p.nama as pusat_nama')
            ->orderBy('p.nama')
            ->orderBy('d.nama');

        if ($request->filled('search')) {
            $s = $request->search;
            $q->where(function ($qq) use ($s) {
                $qq->where('d.nama', 'like', "%{$s}%")
                    ->orWhere('p.nama', 'like', "%{$s}%");
            });
        }

        $perPage = max(1, (int) $request->get('per_page', 10));
        $rows = $q->paginate($perPage)->withQueryString();

        return view('master.kl_daerah.index', compact('rows'));
    }

    public function create()
    {
        if (! Schema::hasTable('master_kl_daerah')) {
            abort(404);
        }
        $pusats = DB::table('master_kl_pusat')->orderBy('nama')->get();

        return view('master.kl_daerah.create', compact('pusats'));
    }

    public function store(Request $request)
    {
        if (! Schema::hasTable('master_kl_daerah')) {
            abort(404);
        }

        $validated = $request->validate([
            'nama' => ['required', 'string', 'max:255'],
            'parent_id' => ['required', 'integer', 'exists:master_kl_pusat,id'],
        ]);

        DB::table('master_kl_daerah')->insert([
            'nama' => $validated['nama'],
            'parent_id' => $validated['parent_id'],
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return redirect()->route('master.kl-daerah.index')->with('success', 'Data KL Daerah berhasil ditambahkan.');
    }

    public function edit(string $id)
    {
        if (! Schema::hasTable('master_kl_daerah')) {
            abort(404);
        }

        $row = DB::table('master_kl_daerah')->where('id', $id)->first();
        if ($row === null) {
            abort(404);
        }

        $pusats = DB::table('master_kl_pusat')->orderBy('nama')->get();

        return view('master.kl_daerah.edit', compact('row', 'pusats'));
    }

    public function update(Request $request, string $id)
    {
        if (! Schema::hasTable('master_kl_daerah')) {
            abort(404);
        }

        if (! DB::table('master_kl_daerah')->where('id', $id)->exists()) {
            abort(404);
        }

        $validated = $request->validate([
            'nama' => ['required', 'string', 'max:255'],
            'parent_id' => ['required', 'integer', 'exists:master_kl_pusat,id'],
        ]);

        DB::table('master_kl_daerah')->where('id', $id)->update([
            'nama' => $validated['nama'],
            'parent_id' => $validated['parent_id'],
            'updated_at' => now(),
        ]);

        return redirect()->route('master.kl-daerah.index')->with('success', 'Data KL Daerah diperbarui.');
    }

    public function destroy(string $id)
    {
        if (! Schema::hasTable('master_kl_daerah')) {
            abort(404);
        }
        DB::table('master_kl_daerah')->where('id', $id)->delete();

        return redirect()->route('master.kl-daerah.index')->with('success', 'Data KL Daerah dihapus.');
    }
}
