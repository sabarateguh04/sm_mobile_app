<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('usm')) {
            return;
        }

        $columns = [
            'id_usm', 'username', 'password', 'email', 'fotoprof',
            'account_status', 'user_role_id', 'nama', 'daerah', 'wilayah',
            'org', 'suborg', 'notelp',
        ];

        $select = [];
        foreach ($columns as $col) {
            if (Schema::hasColumn('usm', $col)) {
                $select[] = "`u`.`{$col}` AS `{$col}`";
            }
        }

        if ($select === []) {
            return;
        }

        // Production kadang punya `wil_usm` sebagai TABLE (bukan view) — DROP VIEW saja tidak cukup.
        DB::statement('DROP VIEW IF EXISTS `wil_usm`');
        DB::statement('DROP TABLE IF EXISTS `wil_usm`');
        DB::statement('CREATE VIEW `wil_usm` AS SELECT '.implode(', ', $select).' FROM `usm` AS `u`');
    }

    public function down(): void
    {
        DB::statement('DROP VIEW IF EXISTS `wil_usm`');
        DB::statement('DROP TABLE IF EXISTS `wil_usm`');
    }
};
