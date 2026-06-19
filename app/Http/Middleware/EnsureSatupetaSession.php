<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureSatupetaSession
{
    public function handle(Request $request, Closure $next): Response
    {
        if (session('kode_user_master')) {
            return $next($request);
        }

        if (config('app.env') !== 'production' && filter_var(config('satupeta.dev_bypass_auth'), FILTER_VALIDATE_BOOLEAN)) {
            session([
                'kode_user_master' => 'dev-local',
                'nama' => 'Developer Lokal',
                'polda' => env('POLDA_ID', '1'),
                'polres' => env('POLRES_ID', '1'),
                'lat' => config('satupeta.lat_center'),
                'lng' => config('satupeta.lng_center'),
                'id_unique' => 'dev-local',
            ]);

            return $next($request);
        }

        $filter = $this->buildFilter($request);
        $unique = $request->query('unique');

        if ($unique !== null && $unique !== '') {
            return redirect('Peta_embed?unique=' . $unique);
        }

        $k = $request->query('k');
        $u = $request->query('u');
        $b = $request->query('b', '');

        if ($k !== null && $k !== '') {
            return redirect('Auth/login_sso/' . $k . '/' . $b . '?' . ltrim($filter, '&'));
        }

        if ($u !== null && $u !== '') {
            return redirect('Auth/proses_login_sso/' . $u . '/' . $b . '?' . ltrim($filter, '&'));
        }

        session(['satupeta.intended' => $request->fullUrl()]);

        return redirect()->route('satupeta.auth.login');
    }

    private function buildFilter(Request $request): string
    {
        $filter = '';
        $params = ['display', 'btn_out', 'lokasi', 'center', 'car', 'officer', 'giat', 'giat_m', 'ops', 'ctg'];

        foreach ($params as $param) {
            $value = $request->query($param);
            if ($value !== null && $value !== '') {
                $filter .= '&' . $param . '=' . $value;
            }
        }

        return $filter;
    }
}
