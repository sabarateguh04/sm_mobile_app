<?php

require __DIR__ . '/vendor/autoload.php';
$app = require __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$tables = ['turjagwali', 'auditarus', 'contigency', 'pnpangkutan', 'profilposko', 'trafficstuck'];
foreach ($tables as $t) {
    if (! Schema::hasTable($t)) {
        echo "$t missing\n";
        continue;
    }
    $cols = Schema::getColumnListing($t);
    echo "$t (" . count($cols) . "): " . implode(', ', $cols) . "\n\n";
}
