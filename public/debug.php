<?php
require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$response = $kernel->handle(
    $request = Illuminate\Http\Request::capture()
);
$row = \Illuminate\Support\Facades\DB::connection('satupeta')->table('sm_dacol_prasjal')->first();
echo implode(',', array_keys((array)$row));
