<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;

$tables = DB::select('SHOW TABLES');
echo "Tables in default database:\n";
foreach ($tables as $table) {
    $array = (array)$table;
    echo "- " . reset($array) . "\n";
}
