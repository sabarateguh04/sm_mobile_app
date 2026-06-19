<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ReferensiController extends Controller
{
    /**
     * @return array<string, array{0: string, 1: string, 2: string, 3: string}>
     */
    private function references(): array
    {
        return config('referensi_tables', []);
    }

    public function index(Request $request)
    {
        $active = $request->get('ref', 'jenisops');
        $references = $this->references();
        if (! isset($references[$active])) {
            $active = array_key_first($references);
        }

        $activeDef = $references[$active];
        $table = $activeDef[0];
        $idCol = $activeDef[1];
        $nameCol = $activeDef[2];
        $label = $activeDef[3];
        $parentCol = $activeDef[5] ?? null;
        $parentRefKey = $activeDef[6] ?? null;
        $parentLabel = match ($parentCol) {
            'saluran' => 'Saluran',
            'kategori' => 'Kategori',
            'id_faktor' => 'Faktor',
            default => $parentCol ? ucwords(str_replace('_', ' ', $parentCol)) : null,
        };

        $rows = collect();
        if (Schema::hasTable($table)) {
            $q = DB::table($table);
            if ($request->filled('search')) {
                $q->where($nameCol, 'like', '%' . $request->search . '%');
            }
            $perPage = max(1, (int) $request->get('per_page', 10));
            $rows = $q->orderBy($nameCol)->paginate($perPage)->withQueryString();
        }

        // Ambil opsi parent jika ada
        $parentOptions = [];
        if ($parentRefKey && isset($references[$parentRefKey])) {
            $pTable = $references[$parentRefKey][0];
            $pNameCol = $references[$parentRefKey][2];
            if (Schema::hasTable($pTable)) {
                $parentOptions = DB::table($pTable)
                    ->whereNotNull($pNameCol)
                    ->where($pNameCol, '!=', '')
                    ->orderBy($pNameCol)
                    ->pluck($pNameCol, $pNameCol)
                    ->toArray();
            }
        }

        // Buat list tab + count per referensi, dikelompokkan per group
        $groups = [];
        foreach ($this->references() as $key => $def) {
            [$tab, $id, $name, $lab] = $def;
            $group = $def[4] ?? 'Lainnya';
            $count = Schema::hasTable($tab) ? DB::table($tab)->count() : 0;
            $groups[$group][] = [
                'key'   => $key,
                'tab'   => $tab,
                'id'    => $id,
                'name'  => $name,
                'lab'   => $lab,
                'count' => $count,
            ];
        }

        return view('master.referensi.index', [
            'pageTitle'     => 'Master Referensi',
            'groups'        => $groups,
            'active'        => $active,
            'label'         => $label,
            'rows'          => $rows,
            'idCol'         => $idCol,
            'nameCol'       => $nameCol,
            'parentCol'     => $parentCol,
            'parentLabel'   => $parentLabel,
            'parentOptions' => $parentOptions,
            'parentRequired' => $parentCol ? $this->columnIsNotNullable($table, $parentCol) : false,
        ]);
    }

    public function store(Request $request)
    {
        $active = $request->input('ref', 'faktor');
        $references = $this->references();
        if (! isset($references[$active])) {
            return redirect()->back()->with('error', 'Referensi tidak valid.');
        }

        $activeDef = $references[$active];
        $table = $activeDef[0];
        $idCol = $activeDef[1];
        $nameCol = $activeDef[2];
        $label = $activeDef[3];
        $parentCol = $activeDef[5] ?? null;

        $rules = [
            'name' => 'required|string|max:255',
        ];
        if ($parentCol) {
            $rules['parent_val'] = $this->columnIsNotNullable($table, $parentCol)
                ? 'required|string|max:255'
                : 'nullable|string|max:255';
        }
        $validated = $request->validate($rules);

        if (!Schema::hasTable($table)) {
            Schema::create($table, function (\Illuminate\Database\Schema\Blueprint $t) use ($idCol, $nameCol, $parentCol) {
                $t->integer($idCol)->primary();
                $t->string($nameCol, 255)->nullable();
                if ($parentCol) {
                    $t->string($parentCol, 100)->nullable();
                }
                $t->timestamps();
            });
        }

        if (Schema::hasTable($table)) {
            $maxId = DB::table($table)->max($idCol);
            $newId = is_numeric($maxId) ? $maxId + 1 : 1;
            
            $data = [
                $idCol => $newId,
                $nameCol => $validated['name']
            ];
            if ($parentCol) {
                $data[$parentCol] = $validated['parent_val'] ?? null;
            }
            DB::table($table)->insert($data);
        }

        return redirect()->route('master.referensi.index', ['ref' => $active])->with('success', "Data {$label} berhasil ditambahkan.");
    }

    public function update(Request $request, $id)
    {
        $active = $request->input('ref', 'faktor');
        $references = $this->references();
        if (! isset($references[$active])) {
            return redirect()->back()->with('error', 'Referensi tidak valid.');
        }

        $activeDef = $references[$active];
        $table = $activeDef[0];
        $idCol = $activeDef[1];
        $nameCol = $activeDef[2];
        $label = $activeDef[3];
        $parentCol = $activeDef[5] ?? null;

        $rules = [
            'name' => 'required|string|max:255',
        ];
        if ($parentCol) {
            $rules['parent_val'] = $this->columnIsNotNullable($table, $parentCol)
                ? 'required|string|max:255'
                : 'nullable|string|max:255';
        }
        $validated = $request->validate($rules);

        if (Schema::hasTable($table)) {
            $data = [
                $nameCol => $validated['name']
            ];
            if ($parentCol) {
                $data[$parentCol] = $validated['parent_val'] ?? null;
            }
            DB::table($table)->where($idCol, $id)->update($data);
        }

        return redirect()->route('master.referensi.index', ['ref' => $active])->with('success', "Data {$label} berhasil diperbarui.");
    }

    public function destroy(Request $request, $id)
    {
        $active = $request->input('ref', 'faktor');
        $references = $this->references();
        if (! isset($references[$active])) {
            return redirect()->back()->with('error', 'Referensi tidak valid.');
        }

        [$table, $idCol, $nameCol, $label] = $references[$active];

        if (Schema::hasTable($table)) {
            DB::table($table)->where($idCol, $id)->delete();
        }

        return redirect()->route('master.referensi.index', ['ref' => $active])->with('success', "Data {$label} berhasil dihapus.");
    }

    private function columnIsNotNullable(string $table, string $column): bool
    {
        if (! Schema::hasTable($table) || ! Schema::hasColumn($table, $column)) {
            return false;
        }

        $meta = DB::selectOne(
            'SELECT IS_NULLABLE
             FROM information_schema.COLUMNS
             WHERE TABLE_SCHEMA = DATABASE()
               AND TABLE_NAME = ?
               AND COLUMN_NAME = ?',
            [$table, $column]
        );

        return $meta !== null && strtoupper((string) ($meta->IS_NULLABLE ?? '')) === 'NO';
    }
}
