<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Tabel trafficstuck dari dump SBO lama: id_trafficstuck NOT NULL tanpa AUTO_INCREMENT.
 * Menyebabkan: Field 'id_trafficstuck' doesn't have a default value
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('trafficstuck')) {
            return;
        }

        $col = collect(DB::select('SHOW COLUMNS FROM `trafficstuck` WHERE Field = ?', ['id_trafficstuck']))->first();

        if (! $col) {
            DB::statement('ALTER TABLE `trafficstuck` ADD COLUMN `id_trafficstuck` INT NOT NULL AUTO_INCREMENT PRIMARY KEY FIRST');

            return;
        }

        if (str_contains(strtolower((string) ($col->Extra ?? '')), 'auto_increment')) {
            return;
        }

        $colType = strtoupper((string) ($col->Type ?? 'INT'));
        $pkExists = collect(DB::select('SHOW KEYS FROM `trafficstuck` WHERE Key_name = ?', ['PRIMARY']))->isNotEmpty();

        $maxId = (int) (DB::table('trafficstuck')->max('id_trafficstuck') ?? 0);
        $next = max(1, $maxId + 1);

        if ($pkExists) {
            DB::statement("ALTER TABLE `trafficstuck` MODIFY COLUMN `id_trafficstuck` {$colType} NOT NULL AUTO_INCREMENT");
        } else {
            DB::statement("ALTER TABLE `trafficstuck` MODIFY COLUMN `id_trafficstuck` {$colType} NOT NULL AUTO_INCREMENT, ADD PRIMARY KEY (`id_trafficstuck`)");
        }

        DB::statement("ALTER TABLE `trafficstuck` AUTO_INCREMENT = {$next}");
    }

    public function down(): void
    {
        // Tidak di-rollback pada production.
    }
};
