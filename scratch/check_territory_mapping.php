<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;

$conn = 'mysql';

// Check daerah table
if (\Illuminate\Support\Facades\Schema::hasTable('daerah')) {
    $daerahs = DB::table('daerah')->get();
    echo "Daerah table count: " . $daerahs->count() . "\n";
    foreach ($daerahs as $d) {
        echo "- ID: {$d->id_daerah}, Name: {$d->namadaerah}\n";
    }
} else {
    echo "Daerah table not found!\n";
}

// Check wilayah table for Surakarta
if (\Illuminate\Support\Facades\Schema::hasTable('wilayah')) {
    $wilayahs = DB::table('wilayah')->where('namawilayah', 'LIKE', '%surakarta%')->get();
    echo "\nWilayah Surakarta:\n";
    foreach ($wilayahs as $w) {
        echo "- ID: {$w->id_wilayah}, Name: {$w->namawilayah}, Daerah ID: {$w->id_daerah}\n";
    }
} else {
    echo "Wilayah table not found!\n";
}
