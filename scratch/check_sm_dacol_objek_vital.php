<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

$conn = 'satupeta';
$table = 'sm_dacol_objek_vital';

if (!Schema::connection($conn)->hasTable($table)) {
    echo "Table $table does not exist on connection $conn!\n";
    exit;
}

$all = DB::connection($conn)->table($table)->get();
echo "Total rows in $table: " . $all->count() . "\n";

$surakarta_rows = DB::connection($conn)->table($table)
    ->where('polres', 'LIKE', '%surakarta%')
    ->orWhere('polda', 'LIKE', '%surakarta%')
    ->orWhere('nama_objek', 'LIKE', '%surakarta%')
    ->get();

echo "Rows matching 'surakarta': " . $surakarta_rows->count() . "\n";
foreach ($surakarta_rows as $row) {
    $array = (array)$row;
    echo "- Name: " . ($row->nama_objek ?? 'N/A') . ", Polres: " . ($row->polres ?? 'N/A') . ", Polda: " . ($row->polda ?? 'N/A') . ", Lat: " . ($row->lat ?? 'N/A') . ", Lng: " . ($row->lng ?? 'N/A') . "\n";
}
