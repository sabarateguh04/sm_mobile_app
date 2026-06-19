<?php

namespace App\Http\Middleware;

use App\Helpers\MenuHelper;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureSuperAdmin
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        if (! $user || MenuHelper::matrixRoleIdForUser($user) !== 1) {
            abort(403, 'Hanya superadmin yang dapat mengakses halaman ini.');
        }

        return $next($request);
    }
}
