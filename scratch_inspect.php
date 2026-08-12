<?php
// Debug: cek kolom & sample data sm_dacol_* via koneksi satupeta
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

$conn  = 'satupeta';
$table = 'sm_dacol_black_trouble_spot';

echo "=== KONEKSI: {$conn} ===\n";
try {
    $db = DB::connection($conn)->getDatabaseName();
    echo "Database : {$db}\n";
} catch (\Throwable $e) {
    echo "GAGAL KONEK: ".$e->getMessage()."\n";
    exit(1);
}

echo "\n=== hasTable({$table}) ===\n";
echo Schema::connection($conn)->hasTable($table) ? "YES\n" : "NO\n";

echo "\n=== KOLOM AKTUAL ===\n";
$cols = Schema::connection($conn)->getColumnListing($table);
print_r($cols);

echo "\n=== TOTAL ROWS ===\n";
$total = DB::connection($conn)->table($table)->count();
echo "Total: {$total}\n";

echo "\n=== 3 SAMPLE ROWS (no filter) ===\n";
$rows = DB::connection($conn)->table($table)->limit(3)->get();
foreach ($rows as $i => $row) {
    echo "--- Row ".($i+1)." ---\n";
    foreach ((array)$row as $k => $v) {
        echo "  {$k}: {$v}\n";
    }
}

echo "\n=== CEK KOLOM polda / polres ===\n";
$hasPolda  = Schema::connection($conn)->hasColumn($table, 'polda');
$hasPolres = Schema::connection($conn)->hasColumn($table, 'polres');
echo "polda  : ".($hasPolda  ? 'ADA' : 'TIDAK ADA')."\n";
echo "polres : ".($hasPolres ? 'ADA' : 'TIDAK ADA')."\n";

if ($hasPolda) {
    $poldas = DB::connection($conn)->table($table)->distinct()->pluck('polda')->take(5);
    echo "Contoh nilai polda: ".implode(', ', $poldas->toArray())."\n";
}
