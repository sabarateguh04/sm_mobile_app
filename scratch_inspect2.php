<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;

$conn = 'satupeta';
$table = 'sm_dacol_black_trouble_spot';

$poldas = DB::connection($conn)->table($table)->distinct()->pluck('polda');
$polres = DB::connection($conn)->table($table)->distinct()->pluck('polres');

echo "Polda values:\n";
print_r($poldas->toArray());

echo "\nPolres values:\n";
print_r($polres->toArray());

$surakartaCount = DB::connection($conn)->table($table)->where('polres', 'like', '%surakarta%')->count();
echo "\nCount like '%surakarta%': $surakartaCount\n";

$soloCount = DB::connection($conn)->table($table)->where('polres', 'like', '%solo%')->count();
echo "Count like '%solo%': $soloCount\n";

$sourceServers = DB::connection($conn)->table($table)->distinct()->pluck('source_server');
echo "\nsource_server values:\n";
print_r($sourceServers->toArray());
