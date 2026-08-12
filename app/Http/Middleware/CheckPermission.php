<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;
use App\Helpers\MenuHelper;
use App\Support\RoleMutationPolicy;

class CheckPermission
{
    /**
     * Pages to exclude from access validation check
     */
    private $excludePages = ['', 'index', 'home', 'account', 'info', 'masterdetail', 'map'];

    /**
     * Map Laravel controller-name -> page_name yang tersimpan di tabel role_permissions (sbo).
     * Tambahkan entry baru di sini kalau ada controller yg namanya beda dgn page_name DB.
     */
    private $pageAliases = [
        'user' => 'usm',
        'terminal' => 'data_terminal',
        'datajalan' => 't_datajalan',
        'jalangangguan' => 'data_gangguan',
        'role' => 'roles',
        'rolepermission' => 'role_permissions',
        'cctv' => 'patrolicctv',
        'traffics' => 'trafficdalkot',
        'posyanpublic' => 'posyan',
        'interaksilayanan' => 'interaksi',
        'opsdalcad' => 'opsdal/cad',
    ];

    /**
     * Map action method -> action_name di DB
     */
    private $actionAliases = [
        'index' => 'list',
        'show' => 'view',
        'create' => 'add',
        'store' => 'add',
        'update' => 'edit',
        'destroy' => 'delete',
        'editField' => 'editfield',
        'verify' => 'edit',
        'exportExcel' => 'list',
        'export' => 'list',
    ];

    /**
     * Handle an incoming request.
     * Check permission format: controller/action
     * Example: usm/view, usm/add, usm/edit, usm/delete
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string $permission = null): Response
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $user = Auth::user();

        // If user has no role, deny access
        if (!$user->user_role_id) {
            abort(403, 'User does not have any role.');
        }

        // If permission not provided, auto-detect from route
        if (!$permission) {
            $route = $request->route();
            $controller = class_basename($route->getController());
            $action = $route->getActionMethod();

            $controllerName = strtolower(str_replace('Controller', '', $controller));
            $page = $this->pageAliases[$controllerName] ?? $controllerName;
            $action = $this->actionAliases[$action] ?? strtolower($action);

            $permission = "{$page}/{$action}";
        }

        // Check if page is excluded
        $page = explode('/', $permission)[0];
        if (in_array($page, $this->excludePages)) {
            return $next($request);
        }

        $matrixId = MenuHelper::matrixRoleIdForUser($user);
        // Super Admin (matriks): bypass pemeriksaan role_permissions untuk route terproteksi
        $superAdminRoles = config('role_menu_matrix.super_admin_roles', [1]);
        if (!is_array($superAdminRoles)) {
            $superAdminRoles = [1];
        }
        if (in_array($matrixId, $superAdminRoles, true)) {
            return $next($request);
        }

        $actionPart = strtolower(explode('/', $permission)[1] ?? '');
        if (RoleMutationPolicy::deniesAllMutations($user)) {
            if (in_array($actionPart, ['add', 'edit', 'editfield', 'delete', 'import_data'], true)) {
                abort(403, 'Akun executive/eksternal hanya dapat melihat data sesuai wilayah kerja Anda.');
            }
        }

        if ($this->matrixGrantsUsmAccess($user, $permission, $actionPart)) {
            return $next($request);
        }

        // Check permission in role_permissions table (matrix role_id + FK user_role_id)
        $roleIds = array_values(array_unique(array_filter([
            (int) ($user->user_role_id ?? 0),
            $matrixId,
        ], fn (int $id): bool => $id > 0)));

        $hasPermission = DB::table('role_permissions')
            ->whereIn('role_id', $roleIds)
            ->whereRaw('LOWER(CONCAT(page_name, \'/\', action_name)) = ?', [strtolower($permission)])
            ->exists();

        if (
            ! $hasPermission
            && str_starts_with(strtolower($permission), 'usm/')
            && $actionPart === 'list'
        ) {
            $hasPermission = DB::table('role_permissions')
                ->whereIn('role_id', $roleIds)
                ->whereRaw('LOWER(CONCAT(page_name, \'/\', action_name)) = ?', ['usm/view'])
                ->exists();
        }

        if (!$hasPermission) {
            abort(403, 'Unauthorized action. Permission required: ' . $permission);
        }

        return $next($request);
    }

    /**
     * Modul USM (admin/users): role matriks sidebar + executive pusat (cakupan nasional, hanya lihat).
     */
    private function matrixGrantsUsmAccess($user, string $permission, string $actionPart): bool
    {
        $permLower = strtolower($permission);
        if (! str_starts_with($permLower, 'usm/')) {
            return false;
        }

        $matrixId = MenuHelper::matrixRoleIdForUser($user);
        if ($matrixId <= 0) {
            return false;
        }

        $menuAccess = config('role_menu_matrix.menu_access', []);
        $allowedUserMgmt = is_array($menuAccess['admin/users'] ?? null) ? $menuAccess['admin/users'] : [];

        if (in_array($matrixId, $allowedUserMgmt, true)) {
            return true;
        }

        return MenuHelper::matrixAllowsPathForUser($user, 'admin/users')
            && in_array($actionPart, ['list', 'view', 'add', 'edit', 'editfield', 'delete'], true);
    }
}
