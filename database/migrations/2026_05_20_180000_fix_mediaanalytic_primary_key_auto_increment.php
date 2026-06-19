<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Tabel mediaanalytic dari dump SBO lama: id_mediaanalytic NOT NULL tanpa AUTO_INCREMENT.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('mediaanalytic')) {
            return;
        }

        $col = collect(DB::select('SHOW COLUMNS FROM `mediaanalytic` WHERE Field = ?', ['id_mediaanalytic']))->first();

        if (! $col) {
            DB::statement('ALTER TABLE `mediaanalytic` ADD COLUMN `id_mediaanalytic` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY FIRST');

            return;
        }

        if (str_contains(strtolower((string) ($col->Extra ?? '')), 'auto_increment')) {
            return;
        }

        $colType = strtoupper((string) ($col->Type ?? 'INT UNSIGNED'));
        $pkExists = collect(DB::select('SHOW KEYS FROM `mediaanalytic` WHERE Key_name = ?', ['PRIMARY']))->isNotEmpty();

        $maxId = (int) (DB::table('mediaanalytic')->max('id_mediaanalytic') ?? 0);
        $next = max(1, $maxId + 1);

        if ($pkExists) {
            DB::statement("ALTER TABLE `mediaanalytic` MODIFY COLUMN `id_mediaanalytic` {$colType} NOT NULL AUTO_INCREMENT");
        } else {
            DB::statement("ALTER TABLE `mediaanalytic` MODIFY COLUMN `id_mediaanalytic` {$colType} NOT NULL AUTO_INCREMENT, ADD PRIMARY KEY (`id_mediaanalytic`)");
        }

        DB::statement("ALTER TABLE `mediaanalytic` AUTO_INCREMENT = {$next}");
    }

    public function down(): void
    {
        //
    }
};
