<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rule;

class MasterKlPusatController extends Controller
{
    public function index(Request $request)
    {
        if (! Schema::hasTable('master_kl_pusat')) {
            abort(404);
        }

        $q = DB::table('master_kl_pusat as p')
            ->leftJoin('master_kl_pusat as par', 'par.id', '=', 'p.parent_id')
            ->select('p.*', 'par.nama as parent_nama')
            ->orderBy('p.nama');

        if ($request->filled('search')) {
            $s = $request->search;
            $q->where('p.nama', 'like', "%{$s}%");
        }

        $perPage = max(1, (int) $request->get('per_page', 10));
        $rows = $q->paginate($perPage)->withQueryString();

        return view('master.kl_pusat.index', compact('rows'));
    }

    public function create()
    {
        if (! Schema::hasTable('master_kl_pusat')) {
            abort(404);
        }
        $parents = DB::table('master_kl_pusat')->orderBy('nama')->get();

        return view('master.kl_pusat.create', compact('parents'));
    }

    public function store(Request $request)
    {
        if (! Schema::hasTable('master_kl_pusat')) {
            abort(404);
        }

        $validated = $request->validate([
            'nama' => ['required', 'string', 'max:255'],
            'parent_id' => ['nullable', 'integer', 'exists:master_kl_pusat,id'],
        ]);

        DB::table('master_kl_pusat')->insert([
            'nama' => $validated['nama'],
            'parent_id' => $validated['parent_id'] ?? null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return redirect()->route('master.kl-pusat.index')->with('success', 'Data KL Pusat berhasil ditambahkan.');
    }

    public function edit(string $id)
    {
        if (! Schema::hasTable('master_kl_pusat')) {
            abort(404);
        }

        $row = DB::table('master_kl_pusat')->where('id', $id)->first();
        if ($row === null) {
            abort(404);
        }

        $parents = DB::table('master_kl_pusat')->where('id', '!=', $id)->orderBy('nama')->get();

        return view('master.kl_pusat.edit', compact('row', 'parents'));
    }

    public function update(Request $request, string $id)
    {
        if (! Schema::hasTable('master_kl_pusat')) {
            abort(404);
        }

        $exists = DB::table('master_kl_pusat')->where('id', $id)->exists();
        if (! $exists) {
            abort(404);
        }

        $validated = $request->validate([
            'nama' => ['required', 'string', 'max:255'],
            'parent_id' => [
                'nullable',
                'integer',
                Rule::exists('master_kl_pusat', 'id')->where(fn ($q) => $q->where('id', '!=', $id)),
            ],
        ]);

        $pid = $validated['parent_id'] ?? null;
        if ($pid !== null && (int) $pid === (int) $id) {
            return back()->withErrors(['parent_id' => 'Induk tidak boleh diri sendiri.'])->withInput();
        }

        DB::table('master_kl_pusat')->where('id', $id)->update([
            'nama' => $validated['nama'],
            'parent_id' => $pid,
            'updated_at' => now(),
        ]);

        return redirect()->route('master.kl-pusat.index')->with('success', 'Data KL Pusat diperbarui.');
    }

    public function destroy(string $id)
    {
        if (! Schema::hasTable('master_kl_pusat')) {
            abort(404);
        }
        DB::table('master_kl_pusat')->where('id', $id)->delete();

        return redirect()->route('master.kl-pusat.index')->with('success', 'Data KL Pusat dihapus.');
    }
}
