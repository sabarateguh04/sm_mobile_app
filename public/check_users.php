<?php
require_once __DIR__.'/../vendor/autoload.php';

$app = require __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

$users = DB::table('usm')->select('username', 'nama', 'user_role_id', 'daerah', 'wilayah')->get();
header('Content-Type: application/json');
echo json_encode($users, JSON_PRETTY_PRINT);
