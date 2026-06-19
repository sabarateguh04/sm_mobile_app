<?php

namespace App\Http\Controllers\Satupeta;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

class ApiIndicarController extends BaseSatupetaController
{
    private string $email = 'iot.korlantas@indicar.id';

    private string $password = 'IndiCarok2023';

    public function getToken(): JsonResponse
    {
        $cached = $this->cachedToken();

        if ($cached !== null) {
            return response()->json([
                'token' => null,
                'indicarToken' => $cached,
            ]);
        }

        $token = $this->fetchApiToken()['token'] ?? null;

        if ($token) {
            $this->storeToken($token);
        }

        return response()->json([
            'token' => null,
            'indicarToken' => $token,
        ]);
    }

    private function cachedToken(): ?string
    {
        try {
            $row = DB::connection('satupeta')->table('indicar_key')->orderByDesc('id')->first();

            if ($row !== null && ! empty($row->exp_date) && Carbon::now()->lt(Carbon::parse($row->exp_date))) {
                return $row->indicarToken ?? $row->token ?? null;
            }
        } catch (\Throwable $e) {
            report($e);
        }

        return null;
    }

    private function storeToken(string $token): void
    {
        try {
            DB::connection('satupeta')->table('indicar_key')->insert([
                'token' => null,
                'indicarToken' => $token,
                'exp_date' => Carbon::now()->addDay()->format('Y-m-d H:i:s'),
            ]);
        } catch (\Throwable $e) {
            report($e);
        }
    }

    private function fetchApiToken(): array
    {
        try {
            $response = Http::timeout(15)->post('https://indicar.id/platform/public/index.php/sysapi/users/login', [
                'username' => $this->email,
                'password' => $this->password,
            ]);

            return $response->json() ?? [];
        } catch (\Throwable $e) {
            report($e);

            return [];
        }
    }
}
