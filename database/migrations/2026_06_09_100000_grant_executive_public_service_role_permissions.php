<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Report Public Service — izin view-only untuk role executive (5 kab/kota, 6 provinsi, 7 pusat).
 *
 * Halaman legacy SBO: posyan (Pos Pelayanan), interaksi (Layanan Masy), layanan_data (Layanan Data).
 */
return new class extends Migration
{
    /** @var list<int> */
    private array $executiveRoleIds = [5, 6, 7];

    /**
     * @var list<array{page: string, actions: list<string>}>
     */
    private array $reportPages = [
        ['page' => 'posyan', 'actions' => ['list', 'view', 'export_data']],
        ['page' => 'interaksi', 'actions' => ['list', 'view', 'export_data']],
        ['page' => 'layanan_data', 'actions' => ['list', 'view', 'export_data']],
    ];

    public function up(): void
    {
        if (! Schema::hasTable('role_permissions')) {
            return;
        }

        $hasPermissionId = Schema::hasColumn('role_permissions', 'permission_id');
        $hasTimestamps = Schema::hasColumn('role_permissions', 'created_at');
        $nextPermissionId = $hasPermissionId ? ((int) (DB::table('role_permissions')->max('permission_id') ?? 0)) : 0;
        $now = now();

        foreach ($this->executiveRoleIds as $roleId) {
            foreach ($this->reportPages as $entry) {
                foreach ($entry['actions'] as $action) {
                    $exists = DB::table('role_permissions')
                        ->where('role_id', $roleId)
                        ->where('page_name', $entry['page'])
                        ->where('action_name', $action)
                        ->exists();

                    if ($exists) {
                        continue;
                    }

                    $insert = [
                        'role_id' => $roleId,
                        'page_name' => $entry['page'],
                        'action_name' => $action,
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
    }

    public function down(): void
    {
        if (! Schema::hasTable('role_permissions')) {
            return;
        }

        foreach ($this->executiveRoleIds as $roleId) {
            foreach ($this->reportPages as $entry) {
                DB::table('role_permissions')
                    ->where('role_id', $roleId)
                    ->where('page_name', $entry['page'])
                    ->whereIn('action_name', $entry['actions'])
                    ->delete();
            }
        }
    }
};
