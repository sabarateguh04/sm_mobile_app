<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;

// Query all records in objek_wisata
$all = DB::table('objek_wisata')->get();
echo "Total objek_wisata: " . $all->count() . "\n";

// Query records containing surakarta in any fields or linked to surakarta piket or surakarta territory
$surakarta_rows = DB::table('objek_wisata')
    ->where('nametempat', 'LIKE', '%surakarta%')
    ->orWhere('lokasi', 'LIKE', '%surakarta%')
    ->orWhere('daerah', 'LIKE', '%surakarta%')
    ->orWhere('wilayah', 'LIKE', '%surakarta%')
    ->get();

echo "Objek Wisata matching 'surakarta' in fields: " . $surakarta_rows->count() . "\n";
foreach ($surakarta_rows as $row) {
    echo "- ID: {$row->id_tempatwisata}, Name: {$row->nametempat}, Koordinat: " . ($row->koordinat ?? 'NULL') . ", Wilayah: " . ($row->wilayah ?? 'NULL') . ", Daerah: " . ($row->daerah ?? 'NULL') . ", Piket: " . ($row->piket ?? 'NULL') . "\n";
}

// Let's also check if there are users with wilayah like 'surakarta'
$users = DB::table('usm')->where('wilayah', 'LIKE', '%surakarta%')->get();
echo "\nUsers in Surakarta:\n";
foreach ($users as $u) {
    echo "- ID: {$u->id_usm}, Name: {$u->nama}, Username: {$u->username}, Wilayah: {$u->wilayah}, Daerah: {$u->daerah}\n";
    // Check if this user has created any objek_wisata
    $user_ow = DB::table('objek_wisata')->where('piket', $u->id_usm)->get();
    echo "  Created {$user_ow->count()} objek_wisata records\n";
    foreach ($user_ow as $row) {
        echo "    * ID: {$row->id_tempatwisata}, Name: {$row->nametempat}, Koordinat: " . ($row->koordinat ?? 'NULL') . ", Wilayah: " . ($row->wilayah ?? 'NULL') . ", Daerah: " . ($row->daerah ?? 'NULL') . "\n";
    }
}
