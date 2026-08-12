<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;

$conn = 'satupeta';
$table = 'smpc_tbl_data_cctv';

// Let's get unique polda_id values
$poldas = DB::connection($conn)->table($table)
    ->select('polda_id', DB::raw('count(*) as total'))
    ->groupBy('polda_id')
    ->get();

echo "Unique polda_id in $table:\n";
print_r($poldas->toArray());

// Let's query rows where name contains 'surakarta' or 'solo' or 'jateng'
$cctvs = DB::connection($conn)->table($table)
    ->where('nama_cctv', 'LIKE', '%surakarta%')
    ->orWhere('nama_cctv', 'LIKE', '%solo%')
    ->orWhere('nama_cctv', 'LIKE', '%jateng%')
    ->orWhere('nama_cctv', 'LIKE', '%jawa tengah%')
    ->get();

echo "\nCCTVs matching Surakarta/Solo/Jateng: " . $cctvs->count() . "\n";
foreach ($cctvs as $c) {
    echo "- ID: {$c->pk_ids}, Name: {$c->nama_cctv}, Polda ID: {$c->polda_id}, Polres ID: {$c->polres_id}, Status: {$c->status}, Lat: {$c->lat}, Lng: {$c->lng}\n";
}
