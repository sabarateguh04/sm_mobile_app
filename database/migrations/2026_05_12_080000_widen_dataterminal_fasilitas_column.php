<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('dataterminal')) {
            return;
        }

        $driver = Schema::getConnection()->getDriverName();

        if ($driver === 'mysql') {
            DB::statement('ALTER TABLE dataterminal MODIFY fasilitas VARCHAR(500) NULL');
        }
    }

    public function down(): void
    {
        if (! Schema::hasTable('dataterminal')) {
            return;
        }

        $driver = Schema::getConnection()->getDriverName();

        if ($driver === 'mysql') {
            DB::statement('ALTER TABLE dataterminal MODIFY fasilitas VARCHAR(100) NULL');
        }
    }
};
