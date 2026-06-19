<?php

namespace App\Http\Controllers\Satupeta;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ApiIntanController extends BaseSatupetaController
{
    private function validateToken(Request $request): ?JsonResponse
    {
        $token = $request->header('token');

        if (! $token) {
            return $this->jsonResponse(true, 'Invalid token', null);
        }

        $dbToken = DB::connection('satupeta_intan')
            ->table('token')
            ->where('nama', 'Aplikasi Petugas')
            ->value('token');

        $allowed = array_filter([
            (string) config('satupeta.api_token'),
            $dbToken,
        ]);

        if (! in_array($token, $allowed, true)) {
            return $this->jsonResponse(true, 'Invalid token', null);
        }

        return null;
    }

    private function jsonResponse(bool $error, string $msg, mixed $values): JsonResponse
    {
        return response()->json([
            'error' => $error,
            'msg' => $msg,
            'values' => $values,
        ]);
    }

    public function getRengiatVipById(Request $request): JsonResponse
    {
        if ($response = $this->validateToken($request)) {
            return $response;
        }

        $id = $request->input('id');
        if ($id === null || $id === '') {
            return $this->jsonResponse(true, 'ID wajib diisi', null);
        }

        $rows = DB::connection('satupeta')->select(
            'SELECT rengiatid, dtm, dinas, subdinas, unit, tanggal, obyek, obyeklain, pejabat,
                    CONCAT(dari, \' \', darinama) asal, CONCAT(ke, \' \', kenama) tujuan,
                    wasdal, anggota1, anggota2, anggota3
             FROM tmc_rengiat_vip WHERE rowid = ?',
            [$id]
        );

        return $this->jsonResponse(false, 'Sukses', $rows);
    }

    public function getRengiatVipDetail(Request $request): JsonResponse
    {
        if ($response = $this->validateToken($request)) {
            return $response;
        }

        $rengiatid = $request->input('rengiatid');
        if ($rengiatid === null || $rengiatid === '') {
            return $this->jsonResponse(true, 'Ren Giat ID wajib diisi', null);
        }

        $rows = DB::connection('satupeta')->select(
            'SELECT rowid, nama lokasi, gangguan identifikasi_gangguan, ejarak estimasi_jarak,
                    ewaktu estimasi_waktu, transit, lat, lng
             FROM tmc_rengiat_vip_route WHERE rengiatid = ?',
            [$rengiatid]
        );

        return $this->jsonResponse(false, 'Sukses', $rows);
    }
}
