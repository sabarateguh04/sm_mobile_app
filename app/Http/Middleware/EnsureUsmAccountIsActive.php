<?php

namespace App\Http\Middleware;

use App\Models\Usm;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * Pengguna USM dengan {@see Usm::$account_status} pending / blocked tidak boleh mengakses area aplikasi setelah login.
 * Registrasi mandiri mengisi status pending sampai administrator mengaktifkan akun.
 */
class EnsureUsmAccountIsActive
{
    /**
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        if (! $user instanceof Usm) {
            return $next($request);
        }

        $status = strtolower(trim((string) ($user->account_status ?? '')));

        if ($status === 'pending') {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()
                ->route('login')
                ->with('account_pending', true);
        }

        $blocked = in_array($status, ['blocked', 'inactive', 'suspend', 'suspended', 'banned'], true);
        if ($blocked) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()
                ->route('login')
                ->with('account_blocked', true);
        }

        return $next($request);
    }
}
