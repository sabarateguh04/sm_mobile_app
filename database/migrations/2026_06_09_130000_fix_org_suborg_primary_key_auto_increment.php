<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Pastikan `org.id_org` dan `suborg.id_suborg` bersifat PRIMARY KEY + AUTO_INCREMENT.
 *
 * Tabel legacy dari dump SBO sering tanpa AUTO_INCREMENT, sehingga INSERT gagal dengan:
 *   "Field 'id_org' doesn't have a default value"
 */
return new class extends Migration
{
    public function up(): void
    {
        $this->fixPkAutoIncrement('org', 'id_org');
        $this->fixPkAutoIncrement('suborg', 'id_suborg');
    }

    public function down(): void
    {
        // Tidak di-rollback: menghapus AUTO_INCREMENT dari production bisa merusak data.
    }

    private function fixPkAutoIncrement(string $table, string $pk): void
    {
        if (! Schema::hasTable($table) || ! Schema::hasColumn($table, $pk)) {
            return;
        }

        $meta = DB::selectOne(
            'SELECT COLUMN_TYPE, EXTRA
             FROM information_schema.COLUMNS
             WHERE TABLE_SCHEMA = DATABASE()
               AND TABLE_NAME = ?
               AND COLUMN_NAME = ?',
            [$table, $pk]
        );

        if ($meta === null) {
            return;
        }

        if (stripos((string) ($meta->EXTRA ?? ''), 'auto_increment') !== false) {
            return;
        }

        $type = $this->sqlIdType((string) ($meta->COLUMN_TYPE ?? ''));
        $pkExists = collect(DB::select("SHOW KEYS FROM `{$table}` WHERE Key_name = 'PRIMARY'"))->isNotEmpty();

        if ($pkExists) {
            DB::statement("ALTER TABLE `{$table}` MODIFY COLUMN `{$pk}` {$type} NOT NULL AUTO_INCREMENT");
        } else {
            DB::statement("ALTER TABLE `{$table}` MODIFY COLUMN `{$pk}` {$type} NOT NULL AUTO_INCREMENT, ADD PRIMARY KEY (`{$pk}`)");
        }

        $next = max(1, ((int) DB::table($table)->max($pk)) + 1);
        DB::statement("ALTER TABLE `{$table}` AUTO_INCREMENT = {$next}");
    }

    private function sqlIdType(string $columnType): string
    {
        $columnType = strtolower($columnType);

        if (str_contains($columnType, 'bigint')) {
            return 'BIGINT UNSIGNED';
        }

        if (str_contains($columnType, 'int')) {
            return 'INT(11)';
        }

        return 'INT(11)';
    }
};
