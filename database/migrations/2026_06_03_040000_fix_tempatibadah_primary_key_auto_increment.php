<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Pastikan kolom id_tempatibadah bersifat AUTO_INCREMENT + PRIMARY KEY.
 *
 * Tabel tempatibadah dari dump SBO kadang tidak memiliki AUTO_INCREMENT, sehingga INSERT gagal dengan:
 *   "Field 'id_tempatibadah' doesn't have a default value"
 */
return new class extends Migration
{
    private const TABLE = 'tempatibadah';

    private const PK = 'id_tempatibadah';

    public function up(): void
    {
        if (! Schema::hasTable(self::TABLE) || ! Schema::hasColumn(self::TABLE, self::PK)) {
            return;
        }

        $col = collect(DB::select('SHOW COLUMNS FROM `'.self::TABLE.'` WHERE Field = ?', [self::PK]))->first();
        if ($col && str_contains(strtolower((string) ($col->Extra ?? '')), 'auto_increment')) {
            return;
        }

        $colType = strtoupper((string) ($col->Type ?? 'INT(11)'));
        $pkExists = collect(DB::select('SHOW KEYS FROM `'.self::TABLE.'` WHERE Key_name = ?', ['PRIMARY']))->isNotEmpty();

        if ($pkExists) {
            DB::statement('ALTER TABLE `'.self::TABLE.'` MODIFY COLUMN `'.self::PK."` {$colType} NOT NULL AUTO_INCREMENT");
        } else {
            DB::statement('ALTER TABLE `'.self::TABLE.'` MODIFY COLUMN `'.self::PK."` {$colType} NOT NULL AUTO_INCREMENT, ADD PRIMARY KEY (`".self::PK.'`)');
        }

        $next = max(1, ((int) DB::table(self::TABLE)->max(self::PK)) + 1);
        DB::statement('ALTER TABLE `'.self::TABLE."` AUTO_INCREMENT = {$next}");
    }

    public function down(): void
    {
        // Tidak di-rollback: menghapus AUTO_INCREMENT dari production bisa merusak data
    }
};
