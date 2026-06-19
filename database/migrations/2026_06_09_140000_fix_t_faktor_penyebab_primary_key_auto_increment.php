<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Pastikan PK referensi `t_faktor` / `t_penyebab` bersifat AUTO_INCREMENT.
 */
return new class extends Migration
{
    public function up(): void
    {
        $this->fixPkAutoIncrement('t_faktor', 'id_faktor');
        $this->fixPkAutoIncrement('t_penyebab', 'id_penyebab');
    }

    public function down(): void
    {
        // Tidak di-rollback.
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
