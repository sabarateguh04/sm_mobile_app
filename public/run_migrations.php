<?php
header('Content-Type: text/plain');

require_once __DIR__.'/../vendor/autoload.php';

$app = require __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\Artisan;

try {
    echo "Running migrations...\n";
    $exitCode = Artisan::call('migrate', ['--force' => true]);
    echo "Migration exit code: " . $exitCode . "\n";
    echo Artisan::output() . "\n";

    echo "Running seeders...\n";
    $exitCode = Artisan::call('db:seed', ['--force' => true]);
    echo "Seeding exit code: " . $exitCode . "\n";
    echo Artisan::output() . "\n";
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
