<?php

namespace App\Http\Controllers\Satupeta;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;

abstract class BaseSatupetaController extends Controller
{
    protected function petaUrl(string $query = ''): string
    {
        $path = trim((string) config('satupeta.peta_path', 'PemetaanNew'), '/');
        $url = url($path);

        return $query !== '' ? $url . '?' . ltrim($query, '?&') : $url;
    }

    protected function redirectToPeta(string $query = ''): \Illuminate\Http\RedirectResponse
    {
        $intended = session('satupeta.intended');
        session()->forget('satupeta.intended');

        if ($intended) {
            return redirect()->to($intended);
        }

        return redirect()->to($this->petaUrl($query));
    }
    protected function apiToken(): string
    {
        return (string) config('satupeta.api_token');
    }

    protected function apiBaseUrl(): string
    {
        return rtrim((string) config('satupeta.url_api'), '/') . '/';
    }

    protected function refreshToken(string $bearer = '', bool $echo = false)
    {
        $response = Http::withHeaders([
            'token' => $this->apiToken(),
            'Content-Type' => 'application/json',
        ])->withOptions(['verify' => false])
            ->post($this->apiBaseUrl() . 'login_v2/refresh_token', [
                'bearer' => $bearer,
            ]);

        $body = $response->body();

        if ($echo) {
            return response($body, 200)->header('Content-Type', 'application/json');
        }

        return json_decode($body);
    }

    protected function satupetaUserContext(Request $request): array
    {
        $user = Auth::user();

        return [
            'id_unique' => session('id_unique', $user?->username ?? ''),
            'token_bearer' => session('token_bearer', ''),
            'user_lat' => (float) (session('lat') ?: config('satupeta.lat_center')),
            'user_lng' => (float) (session('lng') ?: config('satupeta.lng_center')),
            'polda_id' => session('polda', $user?->daerah ?? ''),
            'polres_id' => session('polres', $user?->wilayah ?? ''),
            'provinsi' => session('provinsi', ''),
            'kota' => session('kota', ''),
        ];
    }

    protected function usesOpenStreetMap(): bool
    {
        if (config('satupeta.map_provider') === 'google' && config('satupeta.google_maps_key')) {
            return false;
        }

        return config('satupeta.map_provider', 'openstreetmap') === 'openstreetmap';
    }

    protected function petaViewData(): array
    {
        $ctx = $this->satupetaUserContext(request());

        $useOsm = $this->usesOpenStreetMap();
        $jsTop = ['satupeta/heat_new.js'];

        return array_merge($ctx, [
            'title' => 'SatuPeta',
            'use_osm_map' => $useOsm,
            'js_array_top' => $jsTop,
            'js_local' => 'satupeta/satupeta_baru.js',
            'js_array_bot' => [
                'satupeta/global.js',
                'satupeta/vip_baru.js',
                'satupeta/rawan_baru.js',
                'satupeta/kml.js',
                'satupeta/cctv_baru.js',
                'satupeta/analytic.js',
                'satupeta/car_2.js',
                'satupeta/polygon.js',
                'satupeta/petugas.js',
                'satupeta/fasum.js',
                'satupeta/pengaduan.js',
                'satupeta/kamseltibcar.js',
                'satupeta/management_route.js',
            ],
            'lokasi' => [],
            'css_extra' => ['css/satupeta/custom_peta.css'],
        ]);
    }
}
