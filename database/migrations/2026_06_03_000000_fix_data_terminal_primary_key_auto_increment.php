<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Pastikan kolom id_terminal bersifat AUTO_INCREMENT + PRIMARY KEY.
 *
 * Tabel data_terminal (legacy: dataterminal) dari dump SBO kadang tidak memiliki
 * AUTO_INCREMENT, sehingga INSERT gagal dengan:
 *   "Field 'id_terminal' doesn't have a default value"
 */
return new class extends Migration
{
    private const PK = 'id_terminal';

    public function up(): void
    {
        $table = $this->resolveTableName();
        if ($table === null || ! Schema::hasColumn($table, self::PK)) {
            return;
        }

        $col = collect(DB::select("SHOW COLUMNS FROM `{$table}` WHERE Field = ?", [self::PK]))->first();
        if ($col && str_contains(strtolower((string) ($col->Extra ?? '')), 'auto_increment')) {
            return;
        }

        $colType = strtoupper((string) ($col->Type ?? 'INT(11)'));
        $pkExists = collect(DB::select("SHOW KEYS FROM `{$table}` WHERE Key_name = 'PRIMARY'"))->isNotEmpty();

        if ($pkExists) {
            DB::statement("ALTER TABLE `{$table}` MODIFY COLUMN `".self::PK."` {$colType} NOT NULL AUTO_INCREMENT");
        } else {
            DB::statement("ALTER TABLE `{$table}` MODIFY COLUMN `".self::PK."` {$colType} NOT NULL AUTO_INCREMENT, ADD PRIMARY KEY (`".self::PK."`)");
        }

        $next = max(1, ((int) DB::table($table)->max(self::PK)) + 1);
        DB::statement("ALTER TABLE `{$table}` AUTO_INCREMENT = {$next}");
    }

    public function down(): void
    {
        // Tidak di-rollback: menghapus AUTO_INCREMENT dari production bisa merusak data
    }

    private function resolveTableName(): ?string
    {
        if (Schema::hasTable('data_terminal')) {
            return 'data_terminal';
        }

        if (Schema::hasTable('dataterminal')) {
            return 'dataterminal';
        }

        return null;
    }
};
