<?php

require __DIR__ . '/vendor/autoload.php';
$app = require __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Support\DatabaseConnections;
use Illuminate\Support\Facades\Schema;

$checks = [
    'contigency' => ['jeniscontigency', 'ketcb', 'fotosit', 'jenis_peristiwa', 'lokasi', 'koordinat', 'piket'],
    'trafficstuck' => ['faktor', 'ketcb', 'fotosituasi', 'penyebab', 'lokasi', 'koordinat', 'piket'],
    'turjagwali' => ['koordinat', 'lokasi', 'katgiat', 'peristiwa', 'fotogiat', 'status', 'keterangan', 'piket', 'id_profileposko'],
    'auditarus' => ['koordinat', 'lokasi', 'hasil_audit', 'catatan', 'piket'],
];

foreach ($checks as $table => $cols) {
    if (! Schema::hasTable($table)) {
        echo "$table MISSING\n";
        continue;
    }
    echo "== $table ==\n";
    foreach ($cols as $c) {
        echo "  $c: " . (Schema::hasColumn($table, $c) ? 'Y' : 'N') . "\n";
    }
    $total = DB::table($table)->count();
    $withK = Schema::hasColumn($table, 'koordinat')
        ? DB::table($table)->whereNotNull('koordinat')->where('koordinat', '!=', '')->count()
        : 'n/a';
    echo "  rows=$total withKoordinat=$withK\n";
}

echo "\n== Try contigency select like MapController ==\n";
try {
    $rows = DB::table('contigency')
        ->select('contigency.id_contigency as id', 'contigency.jeniscontigency as nama',
            'contigency.ketcb as lokasi', 'contigency.koordinat')
        ->whereNotNull('contigency.koordinat')
        ->where('contigency.koordinat', '!=', '')
        ->limit(1)
        ->get();
    echo 'OK count=' . $rows->count() . "\n";
} catch (Throwable $e) {
    echo 'FAIL: ' . $e->getMessage() . "\n";
}

echo "\n== profilposko with coords via join? ==\n";
$pp = DB::table('profilposko')->whereNotNull('daerah')->limit(3)->get(['id_profilposko','namaposko','daerah','wilayah','lokasiposko']);
foreach ($pp as $r) {
    echo json_encode($r, JSON_UNESCAPED_UNICODE) . "\n";
}

echo "\n== sample contigency rows ==\n";
foreach (DB::table('contigency')->limit(3)->get() as $r) {
    echo json_encode($r, JSON_UNESCAPED_UNICODE) . "\n";
}
