<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

$table = 'smpc_tbl_data_cctv';
$conn = 'satupeta';

if (!Schema::connection($conn)->hasTable($table)) {
    echo "Table $table does not exist on connection $conn!\n";
    exit;
}

$columns = Schema::connection($conn)->getColumnListing($table);
echo "Columns of $table:\n";
print_r($columns);

$total = DB::connection($conn)->table($table)->count();
echo "Total rows: $total\n";

$all = DB::connection($conn)->table($table)->limit(10)->get();
echo "Sample rows:\n";
foreach ($all as $row) {
    echo "- ID: {$row->pk_ids}, Name: {$row->nama_cctv}, Lat: {$row->lat}, Lng: {$row->lng}, Polda: " . ($row->polda_id ?? 'N/A') . ", Polres: " . ($row->polres_id ?? 'N/A') . "\n";
}
