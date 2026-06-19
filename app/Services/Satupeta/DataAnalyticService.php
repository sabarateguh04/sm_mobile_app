<?php

namespace App\Services\Satupeta;

use Illuminate\Support\Facades\DB;

class DataAnalyticService
{
    public function totalKendaraan(string $channelId, string $filter = 'hari'): array
    {
        $row = DB::connection('satupeta')
            ->table('analytic_kend')
            ->where('channel_id', $channelId)
            ->where('ctddate', date('Y-m-d'))
            ->selectRaw('count(*) as jml')
            ->first();

        return ['jml' => $row->jml ?? 0];
    }

    public function getTrafficCategory(array $arr): array
    {
        $query = DB::connection('satupeta')->table('analytic_kend');

        if (! empty($arr['channel_id'])) {
            $query->where('channel_id', $arr['channel_id']);
        }
        if (! empty($arr['ctddate'])) {
            $query->where('ctddate', $arr['ctddate']);
        }

        return $query->get()->toArray();
    }

    public function getTrafficCat(int $cctvId): array
    {
        return DB::connection('satupeta')
            ->table('traffic_kend')
            ->where('cctv_id', $cctvId)
            ->where('dates', date('Y-m-d'))
            ->get()
            ->toArray();
    }
}
