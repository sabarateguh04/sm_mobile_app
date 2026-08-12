<?php
require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$response = $kernel->handle(
    $request = Illuminate\Http\Request::capture()
);

use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

$cols = Schema::connection('satupeta')->getColumnListing('sm_dacol_objek_vital');
echo json_encode($cols);

$data = DB::connection('satupeta')->table('sm_dacol_objek_vital')->limit(1)->get();
echo "\n\n";
echo json_encode($data);
