<?php

namespace App\Http\Controllers\Admin;

use App\Helpers\MenuHelper;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class RolePermissionController extends Controller
{
    /**
     * Matriks izin per modul (page) — toggle per role × action.
     */
    public function index(Request $request)
    {
        if (! Schema::hasTable('role_permissions') || ! Schema::hasTable('roles')) {
            abort(503, 'Tabel role_permissions atau roles tidak tersedia.');
        }

        $roles = DB::table('roles')
            ->orderBy('role_id')
            ->get(['role_id', 'role_name']);

        $allPerms = DB::table('role_permissions as rp')
            ->select('rp.permission_id', 'rp.role_id', 'rp.page_name', 'rp.action_name')
            ->get();

        /** @var array<string, array<string, array<int, int>>> $matrix page → action → role_id → permission_id */
        $matrix = [];
        foreach ($allPerms as $row) {
            $matrix[$row->page_name][$row->action_name][(int) $row->role_id] = (int) $row->permission_id;
        }

        $pages = collect(array_keys($matrix))->sort()->values();

        $actionSet = [];
        foreach ($matrix as $actions) {
            foreach (array_keys($actions) as $a) {
                $actionSet[$a] = true;
            }
        }
        $orderedActions = array_keys($actionSet);
        usort($orderedActions, function (string $a, string $b): int {
            $order = ['list' => 0, 'view' => 1, 'add' => 2, 'edit' => 3, 'editfield' => 4, 'delete' => 5, 'import_data' => 6, 'export_data' => 7];

            return ($order[$a] ?? 100).$a <=> ($order[$b] ?? 100).$b;
        });

        $sortFn = static function (string $a, string $b): int {
            $order = ['list' => 0, 'view' => 1, 'add' => 2, 'edit' => 3, 'editfield' => 4, 'delete' => 5, 'import_data' => 6, 'export_data' => 7];

            return ($order[$a] ?? 100).$a <=> ($order[$b] ?? 100).$b;
        };

        /** @var array<string, array<int, string>> $actionsPerPage */
        $actionsPerPage = [];
        foreach ($pages as $page) {
            $union = [];
            foreach ($orderedActions as $a) {
                $union[$a] = true;
            }
            foreach (array_keys($matrix[$page] ?? []) as $a) {
                $union[$a] = true;
            }
            $keys = array_keys($union);
            usort($keys, $sortFn);
            $actionsPerPage[$page] = $keys;
        }

        /** @var array<string, array{label: string, pages: array<int, string>}> $moduleBuckets */
        $moduleBuckets = [];
        foreach ($pages as $page) {
            [$mid, $label] = \App\Support\PermissionModuleRegistry::moduleForPage($page);
            if (! isset($moduleBuckets[$mid])) {
                $moduleBuckets[$mid] = ['label' => $label, 'pages' => []];
            }
            $moduleBuckets[$mid]['pages'][] = $page;
        }

        $orderMap = \App\Support\PermissionModuleRegistry::moduleOrder();
        uksort($moduleBuckets, function (string $a, string $b) use ($orderMap): int {
            return ($orderMap[$a] ?? 50) <=> ($orderMap[$b] ?? 50);
        });

        foreach ($moduleBuckets as &$bucket) {
            sort($bucket['pages'], SORT_STRING);
        }
        unset($bucket);

        $actor = Auth::user();
        $canEdit = $actor && in_array(
            MenuHelper::matrixRoleIdForUser($actor),
            config('role_menu_matrix.super_admin_roles', [1]),
            true
        );

        return view('admin.role-permissions.index', [
            'pageTitle' => 'Role & Permissions',
            'roles' => $roles,
            'matrix' => $matrix,
            'actionsPerPage' => $actionsPerPage,
            'moduleBuckets' => $moduleBuckets,
            'canEdit' => $canEdit,
            'totalPermissions' => $allPerms->count(),
        ]);
    }

    /**
     * Aktifkan / nonaktifkan satu sel matriks (JSON).
     */
    public function toggle(Request $request)
    {
        $user = Auth::user();
        if (! $user || ! in_array(
            MenuHelper::matrixRoleIdForUser($user),
            config('role_menu_matrix.super_admin_roles', [1]),
            true
        )) {
            abort(403, 'Hanya superadmin yang dapat mengubah matriks izin.');
        }

        $validated = $request->validate([
            'role_id' => 'required|integer',
            'page_name' => 'required|string|max:255',
            'action_name' => 'required|string|max:255',
            'enabled' => 'required|boolean',
        ]);

        $roleId = (int) $validated['role_id'];
        $pk = Schema::hasColumn('roles', 'role_id') ? 'role_id' : 'id';
        if (! DB::table('roles')->where($pk, $roleId)->exists()) {
            return response()->json(['ok' => false, 'message' => 'Role tidak ditemukan.'], 422);
        }

        $page = $validated['page_name'];
        $action = $validated['action_name'];
        $enabled = $validated['enabled'];

        $q = DB::table('role_permissions')
            ->where('role_id', $roleId)
            ->where('page_name', $page)
            ->where('action_name', $action);

        if ($enabled) {
            if (! $q->exists()) {
                $now = now();
                $insert = [
                    'role_id' => $roleId,
                    'page_name' => $page,
                    'action_name' => $action,
                ];
                if (Schema::hasColumn('role_permissions', 'created_at')) {
                    $insert['created_at'] = $now;
                }
                if (Schema::hasColumn('role_permissions', 'updated_at')) {
                    $insert['updated_at'] = $now;
                }
                try {
                    DB::table('role_permissions')->insert($insert);
                } catch (\Throwable $e) {
                    return response()->json(['ok' => false, 'message' => 'Gagal menyimpan: '.$e->getMessage()], 422);
                }
            }
        } else {
            $q->delete();
        }

        return response()->json(['ok' => true]);
    }
}
