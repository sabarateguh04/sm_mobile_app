<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Skema legacy sbo memakai `org.nameorg` varchar(50); nama kementerian lengkap tidak muat.
 */
return new class extends Migration
{
    public function up(): void
    {
        $driver = Schema::getConnection()->getDriverName();

        if ($driver !== 'mysql' || ! Schema::hasTable('org')) {
            return;
        }

        DB::statement('ALTER TABLE `org` MODIFY `nameorg` VARCHAR(255) NOT NULL');

        if (Schema::hasTable('suborg')) {
            DB::statement('ALTER TABLE `suborg` MODIFY `namesuborg` VARCHAR(255) NULL');
        }

        if (Schema::hasColumn('org', 'singkatan')) {
            DB::statement('ALTER TABLE `org` MODIFY `singkatan` VARCHAR(64) NULL');
        }
    }

    public function down(): void
    {
        $driver = Schema::getConnection()->getDriverName();

        if ($driver !== 'mysql' || ! Schema::hasTable('org')) {
            return;
        }

        DB::statement('ALTER TABLE `org` MODIFY `nameorg` VARCHAR(50) NOT NULL');

        if (Schema::hasTable('suborg')) {
            DB::statement('ALTER TABLE `suborg` MODIFY `namesuborg` VARCHAR(50) NULL');
        }
    }
};
