<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class IntegrationController extends Controller
{
    /**
     * Mengambil data dari modul operasional untuk dibagikan ke Web Pihak Ketiga.
     * Mendukung paginasi dan filter tanggal.
     */
    public function getModuleData(Request $request, $module)
    {
        // Daftar modul yang diizinkan untuk di-share
        $allowedModules = [
            'turjagwali' => 'turjagwali',
            'cctv' => 'patrolicctv',
            'layanan_masyarakat' => 'layanan_masyarakat',
            'contigency' => 'contigency',
            'trafficstuck' => 'trafficstuck',
            'wilayah' => 'wilayah',
            'daerah' => 'daerah'
        ];

        if (!array_key_exists($module, $allowedModules)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Modul tidak ditemukan atau tidak diizinkan.'
            ], 404);
        }

        $tableName = $allowedModules[$module];
        $query = DB::table($tableName);

        $hasTanggal = \Illuminate\Support\Facades\Schema::hasColumn($tableName, 'tanggal');

        // Filter berdasarkan tanggal (Opsional) jika tabel memiliki kolom tanggal
        if ($hasTanggal) {
            if ($request->filled('start_date')) {
                $query->whereDate('tanggal', '>=', $request->start_date);
            }
            if ($request->filled('end_date')) {
                $query->whereDate('tanggal', '<=', $request->end_date);
            }
            $query->orderBy('tanggal', 'desc');
        }

        // Limit dan Paginasi
        $limit = $request->get('limit', 50); // Default 50 data per request
        $data = $query->paginate($limit);

        return response()->json([
            'status' => 'success',
            'module' => $module,
            'data' => $data->items(),
            'pagination' => [
                'total' => $data->total(),
                'per_page' => $data->perPage(),
                'current_page' => $data->currentPage(),
                'last_page' => $data->lastPage()
            ]
        ]);
    }
}
