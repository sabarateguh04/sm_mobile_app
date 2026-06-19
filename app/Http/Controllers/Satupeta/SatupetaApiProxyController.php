<?php

namespace App\Http\Controllers\Satupeta;

use App\Services\Satupeta\SatupetaLocalDataService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Symfony\Component\HttpFoundation\Response;

class SatupetaApiProxyController extends BaseSatupetaController
{
    public function __construct(private SatupetaLocalDataService $localData) {}

    public function proxy(Request $request, string $path = ''): Response
    {
        $path = ltrim($path, '/');

        if (config('satupeta.use_local_api', true)) {
            $local = $this->localData->responseFor($request, $path);
            if ($local !== null) {
                return $local;
            }
        }

        $fallback = $this->localFallbackResponse($request, $path);

        $baseUrl = rtrim((string) config('satupeta.url_api'), '/');

        if ($baseUrl === '') {
            if ($fallback) {
                return $fallback;
            }

            return response()->json([
                'status' => false,
                'msg' => 'URL_BE belum dikonfigurasi di .env',
            ], 503);
        }

        $url = $baseUrl . ($path !== '' ? '/' . $path : '');

        $query = $request->getQueryString();
        if ($query) {
            $url .= '?' . $query;
        }

        $headers = [
            'Accept' => 'application/json',
            'token' => $this->apiToken(),
        ];

        $contentType = $request->header('Content-Type');
        if ($contentType) {
            $headers['Content-Type'] = $contentType;
        } else {
            $headers['Content-Type'] = 'application/json';
        }

        $secondBearer = $this->resolveSecondBearer($request);
        if ($secondBearer !== '') {
            $headers['secondbearer'] = $secondBearer;
        }

        $clientToken = $request->header('token');
        if ($clientToken && $clientToken !== $this->apiToken()) {
            $headers['token'] = $clientToken;
        }

        $http = Http::withHeaders($headers)->withOptions(['verify' => false])->timeout(60);

        try {
            $response = match (strtoupper($request->method())) {
                'POST' => $http->withBody($request->getContent(), $headers['Content-Type'])->post($url),
                'PUT' => $http->withBody($request->getContent(), $headers['Content-Type'])->put($url),
                'PATCH' => $http->withBody($request->getContent(), $headers['Content-Type'])->patch($url),
                'DELETE' => $http->delete($url),
                default => $http->get($url),
            };

            if ($response->successful() || ! $fallback) {
                return response($response->body(), $response->status())
                    ->header('Content-Type', $response->header('Content-Type') ?? 'application/json');
            }
        } catch (\Throwable $e) {
            if (! $fallback) {
                throw $e;
            }
        }

        return $fallback;
    }

    private function localFallbackResponse(Request $request, string $path): ?Response
    {
        $path = trim($path, '/');
        $center = $this->centerCoordinates();

        if ($path === 'provinsiPolda') {
            return response()
                ->json([
                    'status' => true,
                    'msg' => 'fallback local Laravel',
                    'data' => [[
                        'prov_id' => 1,
                        'prov_name' => session('provinsi', 'Provinsi Demo'),
                        'polda_id' => (string) session('polda', '1'),
                        'polda' => session('polda_nama', 'POLDA DEMO'),
                        'polygon' => [$this->squarePolygon($center['lat'], $center['lng'], 0.25)],
                    ]],
                ])
                ->header('X-Satupeta-Fallback', 'local');
        }

        if ($path === 'kotaPolres') {
            return response()
                ->json([
                    'status' => true,
                    'msg' => 'fallback local Laravel',
                    'data' => [[
                        'city_id' => 1,
                        'city_name' => session('kota', 'Kota Demo'),
                        'polda_id' => (string) session('polda', '1'),
                        'polda' => session('polda_nama', 'POLDA DEMO'),
                        'polres_id' => (string) session('polres', '1'),
                        'polres' => session('polres_nama', 'POLRES DEMO'),
                        'prov_id' => 1,
                        'polygon' => [$this->squarePolygon($center['lat'], $center['lng'], 0.15)],
                    ]],
                ])
                ->header('X-Satupeta-Fallback', 'local');
        }

        if ($path === 'sm_master_data/kerawanan') {
            return response()
                ->json([
                    'status' => true,
                    'msg' => 'fallback local Laravel',
                    'data' => [[
                        'rowid' => 1,
                        'kategori' => 'Rawan Laka',
                        'kerawanan' => 'Rawan Laka',
                        'icon' => null,
                        'url_icon' => './my/satupeta/rawan-laka.png',
                    ]],
                ])
                ->header('X-Satupeta-Fallback', 'local');
        }

        if ($path === 'data_collecting/black_trouble_spot') {
            $status = (string) $request->query('status', 'black spot');

            return response()
                ->json([
                    'status' => true,
                    'msg' => 'fallback local Laravel',
                    'data' => [
                        $this->mockTroubleSpotRow(1, $center['lat'], $center['lng'], $status),
                        $this->mockTroubleSpotRow(2, $center['lat'] + 0.01, $center['lng'] + 0.01, $status),
                        $this->mockTroubleSpotRow(3, $center['lat'] - 0.01, $center['lng'] - 0.01, $status),
                    ],
                ])
                ->header('X-Satupeta-Fallback', 'local');
        }

        return null;
    }

    private function centerCoordinates(): array
    {
        return [
            'lat' => (float) (session('lat') ?: config('satupeta.lat_center')),
            'lng' => (float) (session('lng') ?: config('satupeta.lng_center')),
        ];
    }

    private function squarePolygon(float $lat, float $lng, float $d): array
    {
        return [
            [$lng - $d, $lat - $d],
            [$lng + $d, $lat - $d],
            [$lng + $d, $lat + $d],
            [$lng - $d, $lat + $d],
            [$lng - $d, $lat - $d],
        ];
    }

    private function mockTroubleSpotRow(int $id, float $lat, float $lng, string $status): array
    {
        return [
            'rowid' => $id,
            'id' => $id,
            'lat' => $lat,
            'lng' => $lng,
            'status' => $status,
            'status_b_t_s' => 'Aktif',
            'nama_jalan_s' => 'Jalan Demo ' . $id,
            'namajalan' => 'Jalan Demo ' . $id,
            'lokasi' => 'Lokasi Demo ' . $id,
        ];
    }

    private function resolveSecondBearer(Request $request): string
    {
        $fromRequest = (string) ($request->header('secondbearer') ?: $request->header('second_bearer') ?: '');
        if ($fromRequest !== '') {
            return $fromRequest;
        }

        $fromConfig = (string) config('satupeta.s_bearer');
        if ($fromConfig !== '') {
            return $fromConfig;
        }

        $bearer = (string) session('token_bearer', '');
        if ($bearer === '') {
            return '';
        }

        $token = $this->refreshToken($bearer);

        if ($token->status ?? false) {
            return (string) ($token->data->second_bearer ?? '');
        }

        return '';
    }
}
