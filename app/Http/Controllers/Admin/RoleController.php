<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RoleController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // sbo: roles(role_id, role_name) + hitung jumlah user & permission per role.
        $roles = DB::table('roles as r')
            ->leftJoin('usm', 'usm.user_role_id', '=', 'r.role_id')
            ->leftJoin('role_permissions as rp', 'rp.role_id', '=', 'r.role_id')
            ->select(
                'r.role_id',
                'r.role_name',
                DB::raw('COUNT(DISTINCT usm.id_usm) as user_count'),
                DB::raw('COUNT(DISTINCT rp.permission_id) as permission_count')
            )
            ->groupBy('r.role_id', 'r.role_name')
            ->orderBy('r.role_name')
            ->get();

        return view('admin.roles.index', [
            'pageTitle' => 'Roles',
            'roles' => $roles,
            'roleDescriptions' => config('role_menu_matrix.role_descriptions', []),
        ]);
    }
}
