<?php

namespace App\Helpers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class DataJalanHelper
{
    /**
     * Otomatis menyimpan nama jalan/lokasi ke t_datajalan jika belum ada
     * 
     * @param Request $request
     */
    public static function autoSyncFromRequest(Request $request): void
    {
        // Cari field terkait lokasi jalan dari request (mengakomodir form yang berbeda)
        $namaJalan = $request->input('lokasi') ?: $request->input('jalan') ?: $request->input('namajalan');
        
        if (empty($namaJalan) || $namaJalan === '-' || strlen($namaJalan) < 3) {
            return;
        }
        
        $namaJalan = trim($namaJalan);
        $piket = (string) Auth::id();
        if (empty($piket)) {
            return;
        }

        // Ambil koordinat jika ada
        $koordinat = '';
        $lat = $request->input('lat');
        $lng = $request->input('lng');
        if ($lat && $lng) {
            $koordinat = rtrim(rtrim(number_format((float)$lat, 8, '.', ''), '0'), '.') . ',' . 
                         rtrim(rtrim(number_format((float)$lng, 8, '.', ''), '0'), '.');
        }

        // Cek apakah tabel t_datajalan tersedia
        if (!Schema::hasTable('t_datajalan')) {
            return;
        }

        // Cek apakah nama jalan sudah ada untuk wilayah piket ini
        $exists = DB::table('t_datajalan')
            ->where('namajalan', $namaJalan)
            ->where('piket', $piket)
            ->exists();

        $data = [
            'tanggal' => date('Y-m-d'),
            'piket' => $piket,
            'jenisjalan' => '-',
            'namajalan' => $namaJalan,
            'panjangjalan' => 0,
            'lintasan' => 'Antar Kota',
            'prasjal' => '',
            'koordinat' => $koordinat,
        ];

        if (Schema::hasColumn('t_datajalan', 'created_at')) {
            $data['created_at'] = now();
        }
        if (Schema::hasColumn('t_datajalan', 'updated_at')) {
            $data['updated_at'] = now();
        }

        if (!$exists) {
            DB::table('t_datajalan')->insert($data);
        }
    }
}
