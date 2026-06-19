<?php

namespace App\Helpers;

use Illuminate\Support\Facades\DB;

class ArusmudikAggregationHelper
{
    public static function syncByArusmudikId(int $idArusmudik): void
    {
        $row = DB::table('arusmudik')
            ->leftJoin('profilposko', 'arusmudik.id_profileposko', '=', 'profilposko.id_profilposko')
            ->leftJoin('usm', 'arusmudik.piket', '=', 'usm.id_usm')
            ->select(
                'arusmudik.id_arusmudik',
                'arusmudik.id_profileposko',
                'arusmudik.statarus',
                'arusmudik.tanggal',
                'arusmudik.piket',
                'profilposko.daerah',
                'profilposko.wilayah',
                'profilposko.katops',
                'profilposko.jenisops',
                'profilposko.namaposko',
                'usm.nama as usm_nama'
            )
            ->where('arusmudik.id_arusmudik', $idArusmudik)
            ->first();

        if (! $row) {
            self::deleteByArusmudikId($idArusmudik);
            return;
        }

        $now = now();

        DB::table('wil_arusmudik')->updateOrInsert(
            ['id_arusmudik' => $idArusmudik],
            [
                'wilayah' => $row->wilayah,
                'id_profileposko' => $row->id_profileposko,
                'arusmudik_id_profileposko' => $row->id_profileposko,
                'tanggal' => $row->tanggal,
                'piket' => $row->piket,
                'usm_nama' => $row->usm_nama,
                'updated_at' => $now,
                'created_at' => $now,
            ]
        );

        DB::table('da_arusmudik')->updateOrInsert(
            ['id_arusmudik' => $idArusmudik],
            [
                'daerah' => $row->daerah,
                'wilayah' => $row->wilayah,
                'katops' => $row->katops,
                'jenisops' => $row->jenisops,
                'tanggal' => $row->tanggal,
                'namaposko' => $row->namaposko,
                'arusmudik_id_profileposko' => $row->id_profileposko,
                'updated_at' => $now,
                'created_at' => $now,
            ]
        );

        DB::table('nas_arusmudik')->updateOrInsert(
            ['id_arusmudik' => $idArusmudik],
            [
                'daerah' => $row->daerah,
                'wilayah' => $row->wilayah,
                'id_profileposko' => $row->id_profileposko,
                'arusmudik_id_profileposko' => $row->id_profileposko,
                'statarus' => $row->statarus,
                'tanggal' => $row->tanggal,
                'piket' => $row->piket,
                'usm_nama' => $row->usm_nama,
                'updated_at' => $now,
                'created_at' => $now,
            ]
        );
    }

    public static function deleteByArusmudikId(int $idArusmudik): void
    {
        DB::table('wil_arusmudik')->where('id_arusmudik', $idArusmudik)->delete();
        DB::table('da_arusmudik')->where('id_arusmudik', $idArusmudik)->delete();
        DB::table('nas_arusmudik')->where('id_arusmudik', $idArusmudik)->delete();
    }
}

