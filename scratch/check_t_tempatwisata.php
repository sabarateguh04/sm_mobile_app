<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;

$tableName = 't_tempatwisata';

$all = DB::table($tableName)->get();
echo "Total rows in $tableName: " . $all->count() . "\n";

$surakarta_rows = DB::table($tableName)
    ->where('nametempat', 'LIKE', '%surakarta%')
    ->orWhere('lokasi', 'LIKE', '%surakarta%')
    ->get();

echo "Rows matching 'surakarta': " . $surakarta_rows->count() . "\n";
foreach ($surakarta_rows as $row) {
    echo "- Name: " . ($row->nametempat ?? 'N/A') . ", Koordinat: " . ($row->koordinat ?? 'N/A') . ", Piket: " . ($row->piket ?? 'N/A') . "\n";
}

// Let's also check if there are users with wilayah like 'surakarta'
$users = DB::table('usm')->where('wilayah', 'LIKE', '%surakarta%')->get();
foreach ($users as $u) {
    // Check if this user has created any records in t_tempatwisata
    $user_ow = DB::table($tableName)->where('piket', $u->id_usm)->get();
    echo "User ID {$u->id_usm} ({$u->nama}, Wilayah: {$u->wilayah}) created {$user_ow->count()} records\n";
    foreach ($user_ow as $row) {
        echo "  * Name: {$row->nametempat}, Koordinat: " . ($row->koordinat ?? 'NULL') . "\n";
    }
}
