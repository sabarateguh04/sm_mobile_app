<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $now = now();

        // 1. Insert Ditlantas role into `roles` table if not exists
        if (Schema::hasTable('roles')) {
            $roleExists = DB::table('roles')->where('role_id', 8)->exists();
            if (!$roleExists) {
                $insert = [
                    'role_id' => 8,
                    'role_name' => 'Ditlantas',
                ];
                if (Schema::hasColumn('roles', 'name')) {
                    $insert['name'] = 'Ditlantas';
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

        // 2. Duplicate permissions from AdminPolda (ID 2) to Ditlantas (ID 8)
        if (Schema::hasTable('role_permissions')) {
            $role2Permissions = DB::table('role_permissions')->where('role_id', 2)->get();
            $hasPermissionId = Schema::hasColumn('role_permissions', 'permission_id');
            $hasTimestamps = Schema::hasColumn('role_permissions', 'created_at');
            $nextPermissionId = $hasPermissionId ? ((int) (DB::table('role_permissions')->max('permission_id') ?? 0)) : 0;

            foreach ($role2Permissions as $p) {
                $exists = DB::table('role_permissions')
                    ->where('role_id', 8)
                    ->where('page_name', $p->page_name)
                    ->where('action_name', $p->action_name)
                    ->exists();

                if ($exists) {
                    continue;
                }

                $insert = [
                    'role_id' => 8,
                    'page_name' => $p->page_name,
                    'action_name' => $p->action_name,
                ];

                if ($hasPermissionId) {
                    $nextPermissionId++;
                    $insert['permission_id'] = $nextPermissionId;
                }

                if ($hasTimestamps) {
                    $insert['created_at'] = $now;
                    $insert['updated_at'] = $now;
                }

                DB::table('role_permissions')->insert($insert);
            }
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('role_permissions')) {
            DB::table('role_permissions')->where('role_id', 8)->delete();
        }

        if (Schema::hasTable('roles')) {
            DB::table('roles')->where('role_id', 8)->delete();
        }
    }
};
