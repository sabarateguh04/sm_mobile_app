<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

Schema::table('master_kl_resort', function (Blueprint $table) {
    if (!Schema::hasColumn('master_kl_resort', 'instansi_id')) {
        $table->unsignedBigInteger('instansi_id')->nullable()->after('parent_id');
    }
    if (!Schema::hasColumn('master_kl_resort', 'org_id')) {
        $table->unsignedBigInteger('org_id')->nullable()->after('instansi_id');
    }
});
echo "Done.";
