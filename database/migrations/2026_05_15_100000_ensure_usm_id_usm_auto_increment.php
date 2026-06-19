<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Repair: legacy / imported `usm` tables sometimes define `id_usm` as NOT NULL without
     * AUTO_INCREMENT, which breaks inserts (SQLSTATE 1364: Field 'id_usm' doesn't have a default value).
     */
    public function up(): void
    {
        if (! Schema::hasTable('usm') || ! Schema::hasColumn('usm', 'id_usm')) {
            return;
        }

        if (Schema::getConnection()->getDriverName() !== 'mysql') {
            return;
        }

        /** @var object{Type: string, Null: string, Key: string, Extra: string}|null $col */
        $col = DB::selectOne("SHOW COLUMNS FROM `usm` WHERE Field = 'id_usm'");

        if ($col === null) {
            return;
        }

        if (str_contains(strtolower((string) $col->Extra), 'auto_increment')) {
            return;
        }

        $type = trim((string) $col->Type);
        if ($type === '') {
            return;
        }

        $key = strtoupper((string) $col->Key);
        if ($key !== 'PRI') {
            $pk = DB::select("SHOW KEYS FROM `usm` WHERE Key_name = 'PRIMARY'");
            if ($pk === []) {
                DB::statement('ALTER TABLE `usm` ADD PRIMARY KEY (`id_usm`)');
            }
        }

        DB::statement('ALTER TABLE `usm` MODIFY `id_usm` '.$type.' NOT NULL AUTO_INCREMENT');
    }

    public function down(): void
    {
        // Not reversible: prior column definition (AUTO_INCREMENT or not) is unknown.
    }
};
