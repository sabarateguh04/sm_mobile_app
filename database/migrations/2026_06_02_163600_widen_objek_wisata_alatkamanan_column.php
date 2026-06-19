<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('objek_wisata') || ! Schema::hasColumn('objek_wisata', 'alatkamanan')) {
            return;
        }

        $driver = Schema::getConnection()->getDriverName();

        if ($driver === 'mysql') {
            DB::statement('ALTER TABLE objek_wisata MODIFY alatkamanan VARCHAR(255) NULL');
        }
    }

    public function down(): void
    {
        if (! Schema::hasTable('objek_wisata') || ! Schema::hasColumn('objek_wisata', 'alatkamanan')) {
            return;
        }

        $driver = Schema::getConnection()->getDriverName();

        if ($driver === 'mysql') {
            DB::statement('ALTER TABLE objek_wisata MODIFY alatkamanan VARCHAR(100) NULL');
        }
    }
};
