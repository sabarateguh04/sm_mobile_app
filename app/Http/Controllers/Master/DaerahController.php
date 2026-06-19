<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Support\TerritoryMaster;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class DaerahController extends Controller
{
    protected function normalizeDaerahName(string $name): string
    {
        return mb_strtoupper(trim($name), 'UTF-8');
    }

    protected function nextManualKodeProvinsi(): string
    {
        $maxId = (int) DB::table('master_provinsi')->max('id');

        return 'MAN-'.str_pad((string) ($maxId + 1), 4, '0', STR_PAD_LEFT);
    }

    /**
     * @return object{id: int|string, nama: string, kode_provinsi: ?string}|null
     */
    protected function findDaerahRow($id): ?object
    {
        if (Schema::hasTable('daerah')) {
            $row = DB::table('daerah')->where('id_daerah', $id)->first();
            if ($row !== null) {
                return (object) [
                    'id' => $row->id_daerah,
                    'nama' => (string) $row->namadaerah,
                    'kode_provinsi' => $row->kode_provinsi ?? null,
                    'lat' => $row->lat ?? null,
                    'lng' => $row->lng ?? null,
                ];
            }
        }

        if (Schema::hasTable('master_provinsi')) {
            $row = DB::table('master_provinsi')->where('id', $id)->first();
            if ($row !== null) {
                return (object) [
                    'id' => $row->id,
                    'nama' => (string) $row->nama,
                    'kode_provinsi' => $row->kode_provinsi ?? null,
                    'lat' => $row->lat ?? null,
                    'lng' => $row->lng ?? null,
                ];
            }
        }

        return null;
    }

    public function index(Request $request)
    {
        $perPage = max(1, (int) $request->get('per_page', 10));

        if (Schema::hasTable('daerah')) {
            $q = DB::table('daerah')->orderBy('namadaerah');

            if ($request->filled('search')) {
                $s = $request->search;
                $q->where(function ($query) use ($s) {
                    $query->where('namadaerah', 'like', "%{$s}%")
                        ->orWhere('id_daerah', 'like', "%{$s}%");
                    if (Schema::hasColumn('daerah', 'kode_provinsi')) {
                        $query->orWhere('kode_provinsi', 'like', "%{$s}%");
                    }
                });
            }

            $daerah = $q->paginate($perPage)->withQueryString();
        } elseif (Schema::hasTable('master_provinsi')) {
            $q = DB::table('master_provinsi')
                ->select([
                    'id as id_daerah',
                    'nama as namadaerah',
                    'kode_provinsi',
                    'lat',
                    'lng',
                ])
                ->orderBy('nama');

            if ($request->filled('search')) {
                $s = $request->search;
                $q->where(function ($query) use ($s) {
                    $query->where('nama', 'like', "%{$s}%")
                        ->orWhere('id', 'like', "%{$s}%")
                        ->orWhere('kode_provinsi', 'like', "%{$s}%");
                });
            }

            $daerah = $q->paginate($perPage)->withQueryString();
        } else {
            $daerah = (new LengthAwarePaginator([], 0, $perPage, 1, [
                'path' => $request->url(),
                'pageName' => 'page',
            ]))->appends($request->query());
        }

        return view('master.daerah.index', compact('daerah'));
    }

    public function create()
    {
        return view('master.daerah.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama' => ['required', 'string', 'max:255'],
            'lat' => ['nullable', 'string', 'max:50'],
            'lng' => ['nullable', 'string', 'max:50'],
        ]);

        $nama = $this->normalizeDaerahName($validated['nama']);

        if (Schema::hasTable('daerah')) {
            $insert = [
                'namadaerah' => $nama,
                'lat' => $validated['lat'] ?? null,
                'lng' => $validated['lng'] ?? null,
            ];
            if (Schema::hasColumn('daerah', 'kode_provinsi')) {
                $insert['kode_provinsi'] = null;
            }
            DB::table('daerah')->insert($insert);
        } elseif (Schema::hasTable('master_provinsi')) {
            $insert = [
                'id' => TerritoryMaster::nextTableId('master_provinsi'),
                'nama' => $nama,
                'lat' => $validated['lat'] ?? null,
                'lng' => $validated['lng'] ?? null,
                'created_at' => now(),
                'updated_at' => now(),
            ];
            if (Schema::hasColumn('master_provinsi', 'kode_provinsi')) {
                $insert['kode_provinsi'] = $this->nextManualKodeProvinsi();
            }
            DB::table('master_provinsi')->insert($insert);
        }

        return redirect()->route('master.daerah.index')->with('success', 'Data Polda berhasil ditambahkan.');
    }

    public function edit($id)
    {
        $row = $this->findDaerahRow($id);
        if ($row === null) {
            abort(404);
        }

        return view('master.daerah.edit', compact('row'));
    }

    public function update(Request $request, $id)
    {
        if ($this->findDaerahRow($id) === null) {
            abort(404);
        }

        $validated = $request->validate([
            'nama' => ['required', 'string', 'max:255'],
            'lat' => ['nullable', 'string', 'max:50'],
            'lng' => ['nullable', 'string', 'max:50'],
        ]);

        $nama = $this->normalizeDaerahName($validated['nama']);

        if (Schema::hasTable('daerah')) {
            DB::table('daerah')->where('id_daerah', $id)->update([
                'namadaerah' => $nama,
                'lat' => $validated['lat'] ?? null,
                'lng' => $validated['lng'] ?? null,
            ]);
        } elseif (Schema::hasTable('master_provinsi')) {
            DB::table('master_provinsi')->where('id', $id)->update([
                'nama' => $nama,
                'lat' => $validated['lat'] ?? null,
                'lng' => $validated['lng'] ?? null,
                'updated_at' => now(),
            ]);
        }

        return redirect()->route('master.daerah.index')->with('success', 'Data Polda berhasil diperbarui.');
    }

    public function destroy($id)
    {
        if (Schema::hasTable('daerah')) {
            DB::table('daerah')->where('id_daerah', $id)->delete();
        } elseif (Schema::hasTable('master_provinsi')) {
            DB::table('master_provinsi')->where('id', $id)->delete();
        }

        return redirect()->route('master.daerah.index')->with('success', 'Data Provinsi berhasil dihapus.');
    }
}
