<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

Schema::table('org', function (Blueprint $table) {
    if (!Schema::hasColumn('org', 'instansi_id')) {
        $table->unsignedBigInteger('instansi_id')->nullable()->after('id_org');
    }
});
echo "Done.";
