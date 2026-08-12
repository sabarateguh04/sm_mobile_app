<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Auth;
use App\Models\Usm;
use App\Helpers\HierarchicalHelper;
use App\Support\TerritoryIndexFilters;

// 1. Mock logged in user (Operator Wilayah Surakarta)
$userWilayah = new Usm();
$userWilayah->id_usm = 9999;
$userWilayah->nama = 'Operator Surakarta';
$userWilayah->username = 'op_surakarta';
$userWilayah->daerah = 'JAWA TENGAH';
$userWilayah->wilayah = 'SURAKARTA';
$userWilayah->user_role_id = 3; // Operator
Auth::login($userWilayah);

echo "=== MOCK USER (OPERATOR WILAYAH) ===\n";
echo "Level: " . HierarchicalHelper::getUserLevel() . "\n";
echo "Daerah: " . HierarchicalHelper::getUserProvinsi() . "\n";
echo "Wilayah: " . HierarchicalHelper::getUserKabKota() . "\n\n";

// Tables to inspect
$tablesToCheck = [
    // [table_name, connection, pk]
    ['t_tempatwisata', 'mysql', 'id_tempatwisata'],
    ['objek_wisata', 'mysql', 'id_tempatwisata'],
    ['sm_dacol_objek_vital', 'satupeta', 'rowid']
];

foreach ($tablesToCheck as [$table, $conn, $pk]) {
    echo "--- Checking Table: $table (Connection: $conn) ---\n";
    try {
        $schema = Schema::connection($conn);
        if (!$schema->hasTable($table)) {
            echo "Result: Table does not exist.\n\n";
            continue;
        }

        // Show columns
        $columns = $schema->getColumnListing($table);
        echo "Columns: " . implode(', ', $columns) . "\n";

        // Query all
        $total = DB::connection($conn)->table($table)->count();
        echo "Total records in DB: $total\n";

        // Query with coordinates
        $coordCol = null;
        foreach (['koordinat', 'lat', 'latitude'] as $c) {
            if ($schema->hasColumn($table, $c)) {
                $coordCol = $c;
                break;
            }
        }

        if ($coordCol) {
            $hasCoords = DB::connection($conn)->table($table)
                ->whereNotNull($coordCol)
                ->where($coordCol, '!=', '')
                ->where($coordCol, '!=', '0')
                ->count();
            echo "Records with valid coordinates ($coordCol): $hasCoords\n";
        } else {
            echo "No coordinate column found!\n";
        }

        // Query Surakarta (Polda JAWA TENGAH, Polres SURAKARTA)
        $poldaCol = $schema->hasColumn($table, 'polda') ? 'polda' : ($schema->hasColumn($table, 'daerah') ? 'daerah' : null);
        $polresCol = $schema->hasColumn($table, 'polres') ? 'polres' : ($schema->hasColumn($table, 'wilayah') ? 'wilayah' : null);

        $qSurakarta = DB::connection($conn)->table($table);
        if ($poldaCol) {
            $qSurakarta->where($poldaCol, 'LIKE', '%JAWA TENGAH%');
        }
        if ($polresCol) {
            $qSurakarta->where($polresCol, 'LIKE', '%SURAKARTA%');
        }
        $countSka = $qSurakarta->count();
        echo "Records matching JATENG & SURAKARTA columns: $countSka\n";

        // Let's print some sample rows for Surakarta if any
        if ($countSka > 0) {
            $samples = $qSurakarta->limit(3)->get();
            foreach ($samples as $idx => $s) {
                echo "  Sample " . ($idx+1) . ":\n";
                foreach ((array)$s as $k => $v) {
                    if (in_array($k, ['nametempat', 'nama_objek', 'lokasi', 'alamat', 'koordinat', 'lat', 'lng', 'piket', 'polres', 'polda', 'daerah', 'wilayah'])) {
                        echo "    $k: $v\n";
                    }
                }
            }
        }

        // Apply territory scope
        echo "Applying operator territory scope...\n";
        $qScoped = DB::connection($conn)->table($table);
        if ($conn === 'satupeta') {
            TerritoryIndexFilters::applyColumnFilters($qScoped, request(), $table, $conn);
        } else {
            HierarchicalHelper::applyTerritoryScopeForTable($qScoped, $table);
        }
        
        $sql = $qScoped->toSql();
        $bindings = $qScoped->getBindings();
        echo "SQL Query: $sql\n";
        echo "Bindings: " . json_encode($bindings) . "\n";
        echo "Scoped records count: " . $qScoped->count() . "\n";

    } catch (\Exception $e) {
        echo "Error checking table $table: " . $e->getMessage() . "\n";
    }
    echo "\n";
}

// 2. Mock logged in user (Operator Daerah Jawa Tengah)
$userDaerah = new Usm();
$userDaerah->id_usm = 9998;
$userDaerah->nama = 'Operator Jateng';
$userDaerah->username = 'op_jateng';
$userDaerah->daerah = 'JAWA TENGAH';
$userDaerah->wilayah = '';
$userDaerah->user_role_id = 3; // Operator
Auth::login($userDaerah);

echo "=== MOCK USER (OPERATOR DAERAH) ===\n";
echo "Level: " . HierarchicalHelper::getUserLevel() . "\n";
echo "Daerah: " . HierarchicalHelper::getUserProvinsi() . "\n";
echo "Wilayah: " . HierarchicalHelper::getUserKabKota() . "\n\n";

foreach ($tablesToCheck as [$table, $conn, $pk]) {
    echo "--- Checking Scoping for Table: $table (Connection: $conn) ---\n";
    try {
        $schema = Schema::connection($conn);
        if (!$schema->hasTable($table)) {
            continue;
        }
        
        $qScoped = DB::connection($conn)->table($table);
        if ($conn === 'satupeta') {
            TerritoryIndexFilters::applyColumnFilters($qScoped, request(), $table, $conn);
        } else {
            HierarchicalHelper::applyTerritoryScopeForTable($qScoped, $table);
        }
        
        $sql = $qScoped->toSql();
        echo "SQL Query: $sql\n";
        echo "Scoped records count: " . $qScoped->count() . "\n";
    } catch (\Exception $e) {
        echo "Error: " . $e->getMessage() . "\n";
    }
    echo "\n";
}
