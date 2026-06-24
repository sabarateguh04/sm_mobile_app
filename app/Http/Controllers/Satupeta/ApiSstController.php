<?php

namespace App\Http\Controllers\Satupeta;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

class ApiSstController extends BaseSatupetaController
{
    private ?string $token = null;

    private function baseUrl(): string
    {
        return rtrim((string) config('satupeta.sst_url'), '/') . '/';
    }

    private function username(): string
    {
        return (string) config('satupeta.sst_username', 'admin');
    }

    private function password(): string
    {
        return (string) config('satupeta.sst_password', 'admin12345');
    }

    private function curl(string $path, array $body, array $headers = []): ?string
    {
        $response = Http::withHeaders($headers)
            ->timeout(30)
            ->post($this->baseUrl() . $path, $body);

        return $response->successful() ? $response->body() : null;
    }

    private function getToken(): ?string
    {
        $login = $this->curl('login', [
            'username' => $this->username(),
            'password' => $this->password(),
        ], ['Content-Type' => 'application/json']);

        if (! $login) {
            return null;
        }

        $decoded = json_decode($login);
        $this->token = $decoded->token ?? null;

        return $this->token;
    }

    private function authHeaders(): array
    {
        return [
            'Content-Type' => 'application/json',
            'Authorization' => 'Bearer ' . ($this->token ?? ''),
        ];
    }

    public function getAnalitik(Request $request): JsonResponse
    {
        $korelasi = $request->query('korelasi');
        $this->getToken();

        $body = $this->curl('listanalitik', ['page' => 1], $this->authHeaders());
        $data = json_decode((string) $body, true);
        $result = [];

        foreach ($data['data'] ?? [] as $item) {
            if (($item['camid'] ?? null) == $korelasi) {
                $result = $item;
                break;
            }
        }

        return response()->json($result);
    }

    public function getCamera(): JsonResponse|string
    {
        $this->getToken();
        $body = $this->curl('listcamera', ['page' => 1], $this->authHeaders());

        return response($body ?? '[]', 200)->header('Content-Type', 'application/json');
    }

    public function updCctv(): string
    {
        $this->getToken();
        $body = $this->curl('listcamera', ['page' => 1], $this->authHeaders());
        $decoded = json_decode((string) $body);
        $output = '';

        foreach ($decoded->data ?? [] as $camera) {
            $exists = DB::connection('satupeta')->table('smpc_tbl_data_cctv')
                ->where('rowid', $camera->id)
                ->where('source_server', 'sst')
                ->exists();

            if (! $exists) {
                $lat = null;
                $lng = null;
                if (!empty($camera->camgps)) {
                    $gps = explode(',', $camera->camgps);
                    if (count($gps) === 2) {
                        $lat = trim($gps[0]);
                        $lng = trim($gps[1]);
                    }
                }

                DB::connection('satupeta')->table('smpc_tbl_data_cctv')->insert([
                    'nama_cctv' => $camera->camname,
                    'lat' => $lat,
                    'lng' => $lng,
                    'rowid' => $camera->id,
                    'rtsp_cctv' => $camera->urlraw,
                    'source_server' => 'sst',
                    'is_active' => 1,
                ]);
                $output .= '1</br>';
            }

            $output .= 'cctv ' . $camera->camname . ' sudah di eksekusi <br>';
        }

        return $output;
    }

    public function getDashanalitikByCam(Request $request): JsonResponse
    {
        $korelasi = $request->query('korelasi');
        $this->getToken();

        $body = $this->curl('listanalitikdash', [
            'camname' => $korelasi,
            'page' => 1,
            'tanggal' => now('Asia/Jakarta')->format('d/m/Y'),
        ], $this->authHeaders());

        $data = json_decode((string) $body, true);
        $result = [];

        foreach ($data['data'] ?? [] as $item) {
            if (($item['camname'] ?? null) == $korelasi) {
                $result = $item;
                break;
            }
        }

        return response()->json($result);
    }
}
