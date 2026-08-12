<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;

try {
    $connection = 'satupeta';
    $tableName = 'smd_tbl_data_traffic_counting';
    
    $data = DB::connection($connection)->table($tableName)->orderBy('tanggal', 'desc')->orderBy('jam', 'desc')->limit(5)->get();
    
    $output = "";
    foreach ($data as $row) {
        $output .= "TGL: {$row->tanggal} JAM: {$row->jam} KATEGORI: {$row->category} ARAH: {$row->status_cctv} POLDA: {$row->polda} POLRES: {$row->polres}\n";
    }
    file_put_contents(__DIR__ . '/test_output.txt', $output);
    echo "Output written to test_output.txt";
} catch (\Exception $e) {
    file_put_contents(__DIR__ . '/test_output.txt', "Error: " . $e->getMessage());
    echo "Error written to test_output.txt";
}
