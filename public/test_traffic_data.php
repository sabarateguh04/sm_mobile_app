<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;

try {
    $connection = 'mysql_second';
    $tableName = 'smd_tbl_data_traffic_counting';
    
    echo "Testing connection: $connection\n";
    echo "Testing table: $tableName\n";
    
    $data = DB::connection($connection)->table($tableName)->limit(5)->get();
    
    echo "Successfully fetched " . count($data) . " rows.\n";
    foreach ($data as $row) {
        print_r($row);
    }
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
