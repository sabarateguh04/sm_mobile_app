<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckLevel
{
    /**
     * Handle an incoming request.
     * Auto filter data berdasarkan level user (wilayah/daerah/nasional)
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string $level): Response
    {
        if (!auth()->check()) {
            return redirect()->route('login');
        }

        $user = auth()->user();

        // Set level filter untuk digunakan di controller
        $request->attributes->set('user_level', $level);
        $request->attributes->set('user_daerah', $user->daerah);
        $request->attributes->set('user_wilayah', $user->wilayah);

        return $next($request);
    }
}
