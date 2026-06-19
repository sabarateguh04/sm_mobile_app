<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Mengganti skema legacy (role_id tersebar / label lama) dengan 7 role bisnis baru.
 *
 * Pemetaan lama → baru:
 *   1 (SA)                         → 1 superadmin
 *   5, 8, 9, 16 (Admin/back office)→ 2 admin
 *   11 (Posyan / operator)         → 3 oprator
 *   2, 15 (Executive nasional)     → 4 eks nas
 *   3, 14 (Executive daerah)       → 5 eks daerah
 *   4 (Executive wilayah / resort)→ 6 eks resort
 *
 * Nilai lain di `user_role_id` atau `role_permissions.role_id`: → 7 eks external
 *
 * Hanya untuk tabel legacy `roles` dengan kolom PK `role_id`.
 */
return new class extends Migration
{
    /** @var array<int, array{role_id:int, role_name:string}> */
    protected array $newRoles = [
        ['role_id' => 1, 'role_name' => 'superadmin'],
        ['role_id' => 2, 'role_name' => 'admin'],
        ['role_id' => 3, 'role_name' => 'oprator'],
        ['role_id' => 4, 'role_name' => 'eks nas'],
        ['role_id' => 5, 'role_name' => 'eks daerah'],
        ['role_id' => 6, 'role_name' => 'eks resort'],
        ['role_id' => 7, 'role_name' => 'eks external'],
    ];

    public function up(): void
    {
        if (! Schema::hasTable('roles') || ! Schema::hasColumn('roles', 'role_id')) {
            return;
        }

        if (Schema::hasTable('usm') && Schema::hasColumn('usm', 'user_role_id')) {
            DB::statement("
                UPDATE usm SET user_role_id = CASE
                    WHEN user_role_id IN (1) THEN 1
                    WHEN user_role_id IN (5, 8, 9, 16) THEN 2
                    WHEN user_role_id IN (11) THEN 3
                    WHEN user_role_id IN (2, 15) THEN 4
                    WHEN user_role_id IN (3, 14) THEN 5
                    WHEN user_role_id IN (4) THEN 6
                    ELSE 7
                END
                WHERE user_role_id IS NOT NULL
            ");
        }

        if (Schema::hasTable('role_permissions') && Schema::hasColumn('role_permissions', 'role_id')) {
            DB::statement("
                UPDATE role_permissions SET role_id = CASE
                    WHEN role_id IN (1) THEN 1
                    WHEN role_id IN (5, 8, 9, 16) THEN 2
                    WHEN role_id IN (11) THEN 3
                    WHEN role_id IN (2, 15) THEN 4
                    WHEN role_id IN (3, 14) THEN 5
                    WHEN role_id IN (4) THEN 6
                    ELSE 7
                END
            ");

            if (Schema::hasColumn('role_permissions', 'permission_id')) {
                DB::statement('
                    DELETE rp1 FROM role_permissions rp1
                    INNER JOIN role_permissions rp2 ON
                        rp1.role_id = rp2.role_id
                        AND rp1.page_name = rp2.page_name
                        AND rp1.action_name = rp2.action_name
                        AND rp1.permission_id > rp2.permission_id
                ');
            }
        }

        Schema::disableForeignKeyConstraints();
        DB::table('roles')->delete();
        Schema::enableForeignKeyConstraints();

        $now = now();
        foreach ($this->newRoles as $row) {
            $insert = [
                'role_id' => $row['role_id'],
                'role_name' => $row['role_name'],
            ];
            if (Schema::hasColumn('roles', 'name')) {
                $insert['name'] = $row['role_name'];
            }
            if (Schema::hasColumn('roles', 'guard_name')) {
                $insert['guard_name'] = 'web';
            }
            if (Schema::hasColumn('roles', 'created_at')) {
                $insert['created_at'] = $now;
                $insert['updated_at'] = $now;
            }
            DB::table('roles')->insert($insert);
        }
    }

    public function down(): void
    {
        //
    }
};
