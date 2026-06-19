<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Standardize role names and mapping.
 * 
 * Sragamkan nama role:
 * a. SuperAdmin / SA
 * b. AdminPolda
 * c. AdminPolres
 * d. Operator
 * e. Executive3
 * f. Executive2
 * g. Executive1
 */
return new class extends Migration
{
    /** @var array<int, array{role_id:int, role_name:string}> */
    protected array $newRoles = [
        ['role_id' => 1, 'role_name' => 'SuperAdmin / SA'],
        ['role_id' => 2, 'role_name' => 'AdminPolda'],
        ['role_id' => 3, 'role_name' => 'AdminPolres'],
        ['role_id' => 4, 'role_name' => 'Operator'],
        ['role_id' => 5, 'role_name' => 'Executive3'],
        ['role_id' => 6, 'role_name' => 'Executive2'],
        ['role_id' => 7, 'role_name' => 'Executive1'],
    ];

    public function up(): void
    {
        // 1. Shift IDs in `usm`
        if (Schema::hasTable('usm') && Schema::hasColumn('usm', 'user_role_id')) {
            DB::statement("
                UPDATE usm SET user_role_id = CASE
                    WHEN user_role_id = 1 THEN 101
                    WHEN user_role_id = 2 AND (wilayah IS NULL OR wilayah = '') THEN 102
                    WHEN user_role_id = 2 AND (wilayah IS NOT NULL AND wilayah != '') THEN 103
                    WHEN user_role_id = 3 THEN 104
                    WHEN user_role_id = 6 THEN 105
                    WHEN user_role_id = 5 THEN 106
                    WHEN user_role_id = 4 THEN 107
                    WHEN user_role_id = 7 THEN 107
                    ELSE user_role_id
                END
                WHERE user_role_id IS NOT NULL AND user_role_id < 100
            ");

            DB::statement("
                UPDATE usm SET user_role_id = user_role_id - 100
                WHERE user_role_id > 100
            ");
        }

        // 2. Shift IDs in `role_permissions`
        if (Schema::hasTable('role_permissions') && Schema::hasColumn('role_permissions', 'role_id')) {
            // Because old ID 2 splits into new 2 and 3, we first duplicate ID 2 to ID 3.
            $maxPermissionId = DB::table('role_permissions')->max('permission_id') ?? 0;
            $role2Permissions = DB::table('role_permissions')->where('role_id', 2)->get();
            $inserts = [];
            foreach ($role2Permissions as $p) {
                $maxPermissionId++;
                $inserts[] = [
                    'permission_id' => $maxPermissionId,
                    'role_id'       => 103,
                    'page_name'     => $p->page_name,
                    'action_name'   => $p->action_name,
                ];
            }
            if (!empty($inserts)) {
                DB::table('role_permissions')->insert($inserts);
            }

            DB::statement("
                UPDATE role_permissions SET role_id = CASE
                    WHEN role_id = 1 THEN 101
                    WHEN role_id = 2 THEN 102
                    WHEN role_id = 3 THEN 104
                    WHEN role_id = 6 THEN 105
                    WHEN role_id = 5 THEN 106
                    WHEN role_id = 4 THEN 107
                    WHEN role_id = 7 THEN 107
                    ELSE role_id
                END
                WHERE role_id < 100
            ");

            // Delete duplicates (if old 7 and old 4 merge to 107, there might be duplicate page_name + action_name)
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

            DB::statement("
                UPDATE role_permissions SET role_id = role_id - 100
                WHERE role_id > 100
            ");
        }

        // 3. Rebuild `roles` table
        if (Schema::hasTable('roles')) {
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
    }

    public function down(): void
    {
        //
    }
};
