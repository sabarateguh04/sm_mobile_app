<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TurjagwaliController extends Controller
{
    /**
     * GET /api/turjagwali
     * Dapatkan daftar turjagwali untuk diakses di Flutter
     */
    public function index(Request $request)
    {
        $limit = $request->input('limit', 10);
        $search = $request->input('search');
        $operatorId = $request->input('operator_id'); // Filter khusus untuk USM Operator

        $query = DB::table('turjagwali')
            ->leftJoin('usm', 'turjagwali.piket', '=', 'usm.id_usm')
            ->select(
                'turjagwali.*',
                'usm.nama as usm_nama',
                DB::raw('usm.daerah as daerah_nama'),
                DB::raw('usm.wilayah as wilayah_nama')
            );

        // Jika ingin mengambil data khusus untuk USM Operator tertentu
        if ($operatorId) {
            $query->where('turjagwali.piket', $operatorId);
        }

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->orWhere('turjagwali.katgiat', 'like', "%{$search}%")
                  ->orWhere('turjagwali.lokasi', 'like', "%{$search}%")
                  ->orWhere('turjagwali.peristiwa', 'like', "%{$search}%")
                  ->orWhere('turjagwali.petugaslap', 'like', "%{$search}%")
                  ->orWhere('usm.nama', 'like', "%{$search}%");
            });
        }

        $query->orderBy('turjagwali.tanggal', 'desc');

        $data = $query->paginate($limit);

        return response()->json([
            'status' => 'success',
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
