<?php

namespace App\Http\Controllers\Satupeta;

use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ApiDahuaController extends BaseSatupetaController
{
    private function satupetaSmQuery(string $sql, array $bindings = []): array
    {
        try {
            return DB::connection('satupeta_sm_integration')->select($sql, $bindings);
        } catch (QueryException $e) {
            report($e);
            throw $e;
        }
    }

    public function trafficFlow2()
    {
        $selisih = 15;
        $waktu = date('H:i:s');
        $waktuMin = date('H:i:s', time() - 60 * $selisih);

        try {
            $rows = $this->satupetaSmQuery(
                'SELECT rowid, nama_cctv, kordinat, channel_name, count(*) as flow, ctddate as tgl, ctdtime as jam FROM tbl_traffic_counting WHERE ctddate = ? AND ctdtime >= ? AND ctdtime <= ? GROUP BY channel_name',
                [date('Y-m-d'), $waktuMin, $waktu]
            );
        } catch (QueryException $e) {
            return response()->json([
                'status' => false,
                'msg' => 'Koneksi database satupeta_sm_integration gagal. Cek SATUPETA_SM_DB_* di .env.',
                'error' => $e->getCode(),
                'data' => [],
            ], 503);
        }

        return response()->json([
            'filter' => [
                'dari' => date('Y-m-d') . ' ' . $waktuMin,
                'sampai' => date('Y-m-d H:i:s'),
                'selisih' => $selisih . ' Menit',
            ],
            'data' => $rows,
        ]);
    }

    public function trafficFlowCctv(Request $request)
    {
        $selisih = 15;
        $waktu = date('H:i:s');
        $waktuMin = date('H:i:s', time() - 60 * $selisih);
        $channel = $request->input('channel_name');

        try {
            $rows = $this->satupetaSmQuery(
                'SELECT rowid, nama_cctv, kordinat, channel_name, sum(flow) as flow, ctddate, ctdtime FROM tbl_traffic_counting WHERE channel_name = ? AND ctddate = ? AND ctdtime >= ? AND ctdtime <= ?',
                [$channel, date('Y-m-d'), $waktuMin, $waktu]
            );
        } catch (QueryException $e) {
            return response()->json([
                'status' => false,
                'msg' => 'Koneksi database satupeta_sm_integration gagal. Cek SATUPETA_SM_DB_* di .env.',
                'error' => $e->getCode(),
                'data' => [],
            ], 503);
        }

        return response()->json([
            'filter' => [
                'dari' => date('Y-m-d') . ' ' . $waktuMin,
                'sampai' => date('Y-m-d H:i:s'),
                'selisih' => $selisih . ' Menit',
            ],
            'data' => $rows,
        ]);
    }

    public function trafficCategoryCctv(Request $request)
    {
        $selisih = 15;
        $waktu = date('H:i:s');
        $waktuMin = date('H:i:s', time() - 60 * $selisih);
        $channel = $request->input('channel_name');

        try {
            $summary = $this->satupetaSmQuery(
                'SELECT sum(flow) as total, ctddate as tgl, ctdtime as jam FROM tbl_traffic_counting WHERE channel_name = ? AND ctddate = ? AND ctdtime >= ? AND ctdtime <= ?',
                [$channel, date('Y-m-d'), $waktuMin, $waktu]
            );

            $table = $this->satupetaSmQuery(
                'SELECT sum(jml) as jml, tgl, carTypeAStr, channelName, nama_cctv FROM tbl_traffic_category WHERE channelName = ? AND tgl = ? AND jam >= ? AND jam <= ? GROUP BY carTypeAStr',
                [$channel, date('Y-m-d'), $waktuMin, $waktu]
            );
        } catch (QueryException $e) {
            return response()->json([
                'status' => false,
                'msg' => 'Koneksi database satupeta_sm_integration gagal. Cek SATUPETA_SM_DB_* di .env.',
                'error' => $e->getCode(),
                'data' => [],
                'table' => [],
            ], 503);
        }

        return response()->json([
            'filter' => [
                'dari' => date('Y-m-d') . ' ' . $waktuMin,
                'sampai' => date('Y-m-d H:i:s'),
                'selisih' => $selisih . ' Menit',
            ],
            'data' => $summary,
            'table' => $table,
        ]);
    }

    public function averageSpeedCctv(Request $request)
    {
        $selisih = 15;
        $waktu = date('H:i:s');
        $waktuMin = date('H:i:s', time() - 60 * $selisih);
        $channel = $request->input('channel_name');

        try {
            $rows = $this->satupetaSmQuery(
                'SELECT * FROM tbl_traffic_average WHERE channel_name = ? AND tgl = ? AND jam >= ? AND jam <= ?',
                [$channel, date('Y-m-d'), $waktuMin, $waktu]
            );
        } catch (QueryException $e) {
            return response()->json([
                'status' => false,
                'msg' => 'Koneksi database satupeta_sm_integration gagal. Cek SATUPETA_SM_DB_* di .env.',
                'error' => $e->getCode(),
                'data' => [],
            ], 503);
        }

        return response()->json([
            'filter' => [
                'dari' => date('Y-m-d') . ' ' . $waktuMin,
                'sampai' => date('Y-m-d H:i:s'),
                'selisih' => $selisih . ' Menit',
            ],
            'data' => $rows,
        ]);
    }

    public function totalPelanggaran(Request $request)
    {
        $channelId = $request->input('channel_id');

        $rows = DB::connection('satupeta')
            ->select('SELECT c.nama_cctv, pk.channel_id, pk.ctddate, count(pk.id) as total FROM cctv as c LEFT JOIN etle.pelang_kend as pk on c.channel_code = pk.channel_id WHERE pk.ctddate = ? AND c.channel_code = ? ORDER BY pk.ctddate DESC', [
                date('Y-m-d'), $channelId,
            ]);

        return response()->json(['data' => $rows]);
    }
}
