<?php
define('LARAVEL_START', microtime(true));
require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$rows = \Illuminate\Support\Facades\DB::table('contigency')
    ->where('source_module', 'layanan_masyarakat')
    ->get();

echo "Contigency records synced from layanan_masyarakat:\n";
echo "Total count: " . count($rows) . "\n";
foreach ($rows as $i => $row) {
    echo "[$i] ID: " . $row->id_contigency . ", Koordinat: " . ($row->koordinat ?: 'NULL/EMPTY') . ", Status Konfirmasi: " . ($row->status_konfirmasi_opsdal ?? 'N/A') . ", Piket (User ID): " . $row->piket . "\n";
}
