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



        $instansis = DB::table('master_pemerintahan_instansi')->orderBy('nama_unit')->pluck('nama_unit', 'id');
        $orgs = DB::table('org')->orderBy('nameorg')->pluck('nameorg', 'id_org');

        $q = DB::table('master_kl_resort as r')
            ->select('r.id', 'r.nama', 'r.instansi_id', 'r.org_id')
            ->orderBy('r.nama');

        if ($request->filled('search')) {
            $s = $request->search;
            $q->where(function ($qq) use ($s) {
                $qq->where('r.nama', 'like', "%{$s}%");
            });
        }



        $perPage = max(1, (int) $request->get('per_page', 10));
        $rows = $q->paginate($perPage)->withQueryString();

        return view('master.kl_resort.index', compact('rows', 'instansis', 'orgs'));
    }

    public function create(Request $request)
    {
        if (! Schema::hasTable('master_kl_resort')) {
            abort(404);
        }
        $instansis = DB::table('master_pemerintahan_instansi')->orderBy('nama_unit')->get();
        $orgs = DB::table('org')->orderBy('nameorg')->get();

        return view('master.kl_resort.create', compact('instansis', 'orgs'));
    }

    public function store(Request $request)
    {
        if (! Schema::hasTable('master_kl_resort')) {
            abort(404);
        }

        $validated = $request->validate([
            'nama' => ['required', 'string', 'max:255'],
            'instansi_id' => ['required', 'integer'],
            'org_id' => ['required', 'integer'],
        ]);

        DB::table('master_kl_resort')->insert([
            'nama' => $this->normalizeName($validated['nama']),
            'parent_id' => null,
            'instansi_id' => $validated['instansi_id'],
            'org_id' => $validated['org_id'],
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $redirect = route('master.kl-resort.index');


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

        $instansis = DB::table('master_pemerintahan_instansi')->orderBy('nama_unit')->get();
        $orgs = DB::table('org')->orderBy('nameorg')->get();

        return view('master.kl_resort.edit', compact('row', 'instansis', 'orgs'));
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
            'instansi_id' => ['required', 'integer'],
            'org_id' => ['required', 'integer'],
        ]);

        DB::table('master_kl_resort')->where('id', $id)->update([
            'nama' => $this->normalizeName($validated['nama']),
            'instansi_id' => $validated['instansi_id'],
            'org_id' => $validated['org_id'],
            'updated_at' => now(),
        ]);

        return redirect()->route('master.kl-resort.index')
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
