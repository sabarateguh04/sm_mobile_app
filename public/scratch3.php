<?php
require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$response = $kernel->handle(
    $request = Illuminate\Http\Request::capture()
);

use Illuminate\Support\Facades\DB;

try {
    $res = DB::connection('satupeta')->table('sm_dacol_fasilitas_umum')
        ->select('polda', 'polres')
        ->where('polda', 'like', '%jawa tengah%')
        ->distinct()
        ->get();
    echo json_encode($res, JSON_PRETTY_PRINT);
} catch (\Throwable $e) {
    echo $e->getMessage();
}
