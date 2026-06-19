<?php

namespace App\Http\Middleware;

use App\Helpers\MenuHelper;
use App\Support\RoleMutationPolicy;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * Blokir HTTP mutasi untuk role view-only & modul personil operator.
 * Pemeriksaan kepemilikan baris (`piket`) dilakukan di controller.
 */
class EnforceRoleMutationPolicy
{
    /** @var list<string> */
    private const SKIP_ROUTE_PREFIXES = [
        'profile.',
        'logout',
        'logout.confirm',
        'auth.',
        'register',
        'register.',
    ];

    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::user();
        if (! $user) {
            return $next($request);
        }

        if ($this->shouldSkip($request)) {
            return $next($request);
        }

        $action = (string) ($request->route()?->getActionMethod() ?? '');

        if ($action === '') {
            return $next($request);
        }

        RoleMutationPolicy::assertRouteMutationAllowed(
            $user,
            $action,
            (string) ($request->route()?->getName() ?? '')
        );

        return $next($request);
    }

    private function shouldSkip(Request $request): bool
    {
        if (MenuHelper::isSuperAdminForLayout(Auth::user())) {
            return true;
        }

        $routeName = (string) ($request->route()?->getName() ?? '');

        foreach (self::SKIP_ROUTE_PREFIXES as $prefix) {
            if ($routeName === $prefix || str_starts_with($routeName, $prefix)) {
                return true;
            }
        }

        if ($request->is('profil-saya/*')) {
            return true;
        }

        if ($request->isMethodSafe()) {
            $action = strtolower((string) ($request->route()?->getActionMethod() ?? ''));

            return ! in_array($action, ['create', 'edit'], true);
        }

        return false;
    }
}
