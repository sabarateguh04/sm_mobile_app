<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class MasterKlResortController extends Controller
{
    protected function normalizeName(string $name): string
    {
        return mb_strtoupper(trim($name), 'UTF-8');
    }

    public function index(Request $request)
    {
        if (! Schema::hasTable('master_kl_resort')) {
            abort(404);
        }

        $daerahs = Schema::hasTable('master_kl_daerah')
            ? DB::table('master_kl_daerah')->orderBy('nama')->get(['id', 'nama'])
            : collect();

        $q = DB::table('master_kl_resort as r')
            ->join('master_kl_daerah as d', 'd.id', '=', 'r.parent_id')
            ->select('r.id', 'r.nama', 'r.parent_id', 'd.nama as daerah_nama')
            ->orderBy('d.nama')
            ->orderBy('r.nama');

        if ($request->filled('search')) {
            $s = $request->search;
            $q->where(function ($qq) use ($s) {
                $qq->where('r.nama', 'like', "%{$s}%")
                    ->orWhere('d.nama', 'like', "%{$s}%");
            });
        }

        if ($request->filled('parent_id')) {
            $q->where('r.parent_id', $request->parent_id);
        }

        $perPage = max(1, (int) $request->get('per_page', 10));
        $rows = $q->paginate($perPage)->withQueryString();

        return view('master.kl_resort.index', compact('rows', 'daerahs'));
    }

    public function create(Request $request)
    {
        if (! Schema::hasTable('master_kl_resort')) {
            abort(404);
        }
        $daerahs = DB::table('master_kl_daerah')->orderBy('nama')->get();
        $preselectedParent = $request->query('parent_id');

        return view('master.kl_resort.create', compact('daerahs', 'preselectedParent'));
    }

    public function store(Request $request)
    {
        if (! Schema::hasTable('master_kl_resort')) {
            abort(404);
        }

        $validated = $request->validate([
            'nama' => ['required', 'string', 'max:255'],
            'parent_id' => ['required', 'integer', 'exists:master_kl_daerah,id'],
        ]);

        DB::table('master_kl_resort')->insert([
            'nama' => $this->normalizeName($validated['nama']),
            'parent_id' => $validated['parent_id'],
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $redirect = route('master.kl-resort.index');
        if ($request->filled('parent_id')) {
            $redirect .= '?'.http_build_query(['parent_id' => $request->parent_id]);
        }

        return redirect()->to($redirect)->with('success', 'Data sub organisasi berhasil ditambahkan.');
    }

    public function edit(string $id)
    {
        if (! Schema::hasTable('master_kl_resort')) {
            abort(404);
        }

        $row = DB::table('master_kl_resort')->where('id', $id)->first();
        if ($row === null) {
            abort(404);
        }

        $daerahs = DB::table('master_kl_daerah')->orderBy('nama')->get();

        return view('master.kl_resort.edit', compact('row', 'daerahs'));
    }

    public function update(Request $request, string $id)
    {
        if (! Schema::hasTable('master_kl_resort')) {
            abort(404);
        }

        if (! DB::table('master_kl_resort')->where('id', $id)->exists()) {
            abort(404);
        }

        $validated = $request->validate([
            'nama' => ['required', 'string', 'max:255'],
            'parent_id' => ['required', 'integer', 'exists:master_kl_daerah,id'],
        ]);

        DB::table('master_kl_resort')->where('id', $id)->update([
            'nama' => $this->normalizeName($validated['nama']),
            'parent_id' => $validated['parent_id'],
            'updated_at' => now(),
        ]);

        return redirect()->route('master.kl-resort.index', ['parent_id' => $validated['parent_id']])
            ->with('success', 'Data sub organisasi berhasil diperbarui.');
    }

    public function destroy(string $id)
    {
        if (! Schema::hasTable('master_kl_resort')) {
            abort(404);
        }
        DB::table('master_kl_resort')->where('id', $id)->delete();

        return redirect()->route('master.kl-resort.index')->with('success', 'Data sub organisasi berhasil dihapus.');
    }
}
