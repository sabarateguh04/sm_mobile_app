<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Tabel trafficdalkot dari dump SBO lama: id_trafficdalkot NOT NULL tanpa AUTO_INCREMENT.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('trafficdalkot')) {
            return;
        }

        $col = collect(DB::select('SHOW COLUMNS FROM `trafficdalkot` WHERE Field = ?', ['id_trafficdalkot']))->first();

        if (! $col) {
            DB::statement('ALTER TABLE `trafficdalkot` ADD COLUMN `id_trafficdalkot` INT NOT NULL AUTO_INCREMENT PRIMARY KEY FIRST');

            return;
        }

        if (str_contains(strtolower((string) ($col->Extra ?? '')), 'auto_increment')) {
            return;
        }

        $colType = strtoupper((string) ($col->Type ?? 'INT'));
        $pkExists = collect(DB::select('SHOW KEYS FROM `trafficdalkot` WHERE Key_name = ?', ['PRIMARY']))->isNotEmpty();

        $maxId = (int) (DB::table('trafficdalkot')->max('id_trafficdalkot') ?? 0);
        $next = max(1, $maxId + 1);

        if ($pkExists) {
            DB::statement("ALTER TABLE `trafficdalkot` MODIFY COLUMN `id_trafficdalkot` {$colType} NOT NULL AUTO_INCREMENT");
        } else {
            DB::statement("ALTER TABLE `trafficdalkot` MODIFY COLUMN `id_trafficdalkot` {$colType} NOT NULL AUTO_INCREMENT, ADD PRIMARY KEY (`id_trafficdalkot`)");
        }

        DB::statement("ALTER TABLE `trafficdalkot` AUTO_INCREMENT = {$next}");
    }

    public function down(): void
    {
        //
    }
};
