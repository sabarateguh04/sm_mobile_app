<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);

echo "<pre>";
$status = $kernel->call('cctv:import-db2');
echo "Exit Code: " . $status . "\n";
echo "Output:\n";
echo $kernel->output();
echo "</pre>";
