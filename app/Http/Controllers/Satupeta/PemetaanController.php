<?php

namespace App\Http\Controllers\Satupeta;

use App\Services\Satupeta\DataAnalyticService;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Connection;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PemetaanController extends BaseSatupetaController
{
    public function __construct(private DataAnalyticService $analytics) {}

    public function index(): View
    {
        $data = $this->petaViewData();
        $data['page'] = 'satupeta_new';
        $data['js_array_top'] = ['satupeta/heat_new.js'];
        $data['js_local'] = 'satupeta/satupeta_new.js';
        $data['js_array_bot'] = [
            'satupeta/vip.js', 'satupeta/rawan_new.js', 'satupeta/rengiat_new.js',
            'satupeta/global.js', 'satupeta/kml.js', 'satupeta/cctv.js',
            'satupeta/analytic.js', 'satupeta/car_2.js', 'satupeta/polygon.js',
        ];

        return view('satupeta.satupeta_baru', $data);
    }

    public function getKategoriLokasi()
    {
        return response()->json([]);
    }

    public function getPoi(Request $request)
    {
        $query = DB::connection('satupeta_intan')->table('lokasi')
            ->select('id', 'kategori_static', 'nama_lokasi', 'lat', 'lng', 'deskripsi');

        if ($request->filled('kategori')) {
            $query->where('kategori_static', $request->input('kategori'));
        } else {
            $query->groupBy('kategori_static');
        }

        return response()->json($query->get());
    }

    public function getTitik(Request $request)
    {
        $s = $request->input('s');
        $from = $request->input('from');
        $to = $request->input('to');
        $poldaId = $request->input('polda_id');
        $polresId = $request->input('polres_id');
        $db = DB::connection('satupeta');

        if ($s === 'Ambang Gangguan') {
            return $this->jsonTitikGangguan($db, $from, $to, $poldaId, $polresId);
        }
        if ($s === 'Rawan') {
            return $this->jsonTitikRawan($db, $from, $to, $poldaId, $polresId);
        }
        if ($s === 'Kegiatan Masyarakat') {
            return $this->jsonTitikGiat($db, $from, $to, $poldaId, $polresId);
        }

        $query = $db->table('tmc_data_statusjalan');

        if (in_array($s, ['Black Spot', 'Trouble Spot'], true)) {
            $query->where('status', $s);
            if ($from) {
                $query->where('tgl', '>=', $from);
            }
            if ($to) {
                $query->where('tgl', '<=', $to);
            }
            if ($poldaId) {
                $query->where('polda', $poldaId);
            }
            if ($polresId) {
                $query->where('polres', $polresId);
            }
            $query->where('polda', '!=', '')->where('polres', '!=', '');
        }

        $data = $query->get()->map(fn ($v) => [
            'rowid' => $v->rowid,
            'dtm' => tgl_indo($v->tgl),
            'lat' => (float) $v->lat,
            'lng' => (float) $v->lng,
            'namajalan' => $v->jalan,
            'status' => $v->status,
            'polda_id' => $v->polda,
            'polres_id' => $v->polres,
        ]);

        return response()->json(['data' => $data, 'total' => $data->count()]);
    }

    private function jsonTitikGangguan(
        Connection $db,
        ?string $from,
        ?string $to,
        ?string $poldaId,
        ?string $polresId
    ): JsonResponse {
        $query = $db->table('tmc_data_gangguan')->where('status', 'Ambang Gangguan');
        if ($from) $query->where('tgl', '>=', $from);
        if ($to) $query->where('tgl', '<=', $to);
        if ($poldaId) $query->where('polda', $poldaId);
        if ($polresId) $query->where('polres', $polresId);
        $query->where('polda', '!=', '')->where('polres', '!=', '');

        $data = $query->get()->map(fn ($v) => [
            'rowid' => $v->rowid,
            'dtm' => tgl_indo($v->tgl),
            'lat' => (float) $v->lat,
            'lng' => (float) $v->lng,
            'namajalan' => $v->jalan,
            'status' => $v->status,
            'penyebab' => $v->penyebab,
            'penyebabd' => $v->penyebabd,
            'polda_id' => $v->polda,
            'polres_id' => $v->polres,
        ]);

        return response()->json(['data' => $data, 'total' => $data->count()]);
    }

    private function jsonTitikRawan(
        Connection $db,
        ?string $from,
        ?string $to,
        ?string $poldaId,
        ?string $polresId
    ): JsonResponse {
        $query = $db->table('tmc_data_rawan');
        if ($from) $query->where('tgl', '>=', $from);
        if ($to) $query->where('tgl', '<=', $to);
        if ($poldaId) $query->where('polda', $poldaId);
        if ($polresId) $query->where('polres', $polresId);
        $query->where('polda', '!=', '')->where('polres', '!=', '');

        $data = $query->get()->map(fn ($v) => [
            'rowid' => $v->rowid,
            'dtm' => tgl_indo($v->tgl),
            'lat' => (float) $v->lat,
            'lng' => (float) $v->lng,
            'namajalan' => $v->jalan,
            'status' => $v->status,
            'polda_id' => $v->polda,
            'polres_id' => $v->polres,
        ]);

        return response()->json($data);
    }

    private function jsonTitikGiat(
        Connection $db,
        ?string $from,
        ?string $to,
        ?string $poldaId,
        ?string $polresId
    ): JsonResponse {
        $query = $db->table('tmc_data_giatpublik');
        if ($from) $query->where('tgl', '>=', $from);
        if ($to) $query->where('tgl', '<=', $to);
        if ($poldaId) $query->where('polda', $poldaId);
        if ($polresId) $query->where('polres', $polresId);
        $query->where('polda', '!=', '')->where('polres', '!=', '');

        $data = $query->get()->map(fn ($v) => [
            'rowid' => $v->rowid,
            'dtm' => tgl_indo($v->tgl),
            'lat' => (float) $v->lat,
            'lng' => (float) $v->lng,
            'namajalan' => $v->jalan,
            'giat' => $v->giat,
            'tgldari' => $v->tgldari,
            'tglsampai' => $v->tglsampai,
            'jamdari' => $v->jamdari,
            'jamsampai' => $v->jamsampai,
            'polda_id' => $v->polda,
            'polres_id' => $v->polres,
        ]);

        return response()->json(['data' => $data, 'total' => $data->count()]);
    }

    public function getCctv(Request $request)
    {
        $a = $request->input('a');
        $dinas = $request->input('dinas');
        $poldaId = $request->input('polda_id');

        $query = DB::connection('satupeta')->table('smpc_tbl_data_cctv')
            ->select(
                'pk_ids as id',
                'nama_cctv',
                'rtsp_cctv',
                DB::raw("CONCAT(COALESCE(lat, ''), ',', COALESCE(lng, '')) as kordinat"),
                'nama_cctv as channel_name',
                'channel_id',
                'rowid as trx_id',
                'polda_id as da_id',
                'instansi'
            );

        if ($poldaId) {
            $query->where('polda_id', $poldaId);
        }

        $rows = $query->get();
        $filtered = match ($dinas) {
            'korlantas' => $rows->filter(fn ($v) => strtolower($v->instansi) === 'korlantas' || str_contains(strtoupper($v->nama_cctv), 'KORLA') || str_contains(strtoupper($v->nama_cctv), 'SMART')),
            'dishub' => $rows->filter(fn ($v) => strtolower($v->instansi) === 'dishub' || str_contains(strtoupper($v->nama_cctv), 'DSHB')),
            default => $rows,
        };

        $data = [];
        foreach ($filtered as $v) {
            $total = '';
            $kategori = [];
            $traffic = [];

            if ($a !== '') {
                $total = $this->analytics->totalKendaraan($v->channel_id, 'hari')['jml'] ?? '';
            }
            if ($a === 'traffic_category') {
                $kategori = $this->analytics->getTrafficCategory([
                    'channel_id' => $v->channel_id,
                    'ctddate' => date('Y-m-d'),
                    'filter' => 'today',
                ]);
                $traffic = $this->analytics->getTrafficCat($v->id);
            }

            $data[] = [
                'id' => $v->id,
                'nama' => $v->nama_cctv,
                'rtsp' => $v->rtsp_cctv,
                'total' => $total,
                'kategori' => $kategori,
                'kordinat' => kordinat($v->kordinat),
                'traffic_kategori' => $traffic,
                'trx_id' => $v->trx_id,
                'channel_name' => $v->channel_name,
                'channel_code' => $v->channel_id,
            ];
        }

        return response()->json($data);
    }

    public function getRawan(Request $request)
    {
        $query = DB::connection('satupeta')->table('tmc_data_rawan');

        if ($request->filled('filter')) {
            $query->where('status', $request->input('filter'));
        }
        if ($request->filled('from')) {
            $query->where('tgl', '>=', $request->input('from'));
        }
        if ($request->filled('to')) {
            $query->where('tgl', '<=', $request->input('to'));
        }
        if ($request->filled('polda_id')) {
            $query->where('polda', $request->input('polda_id'));
        }
        if ($request->filled('polres_id')) {
            $query->where('polres', $request->input('polres_id'));
        }

        $data = $query->get()->map(fn ($v) => [
            'rowid' => $v->rowid,
            'dtm' => tgl_indo($v->dtm),
            'lat' => (float) $v->lat,
            'lng' => (float) $v->lng,
            'namajalan' => $v->jalan,
            'status' => $v->status,
            'polda_id' => $v->polda,
            'polres_id' => $v->polres,
        ]);

        return response()->json($data);
    }

    public function getRengiat(Request $request)
    {
        $query = DB::connection('satupeta')->table('tmc_rengiat');

        if ($request->filled('filter')) {
            $query->where('jenis', $request->input('filter'));
        }
        if ($request->filled('from')) {
            $query->where('tgl', '>=', $request->input('from'));
        }
        if ($request->filled('to')) {
            $query->where('tgl', '<=', $request->input('to'));
        }
        if ($request->filled('polda_id')) {
            $query->where('polda', $request->input('polda_id'));
        }
        if ($request->filled('polres_id')) {
            $query->where('polres', $request->input('polres_id'));
        }

        $data = $query->get()->map(fn ($v) => [
            'rowid' => $v->rowid,
            'polda_id' => $v->polda,
            'polres_id' => $v->polres,
            'tgl' => tgl_indo($v->tgl),
            'lat' => (float) $v->lat,
            'lng' => (float) $v->lng,
            'jamdari' => $v->jamdari,
            'jamsampai' => $v->jamsampai,
            'tgldari' => $v->tgldari,
            'tglsampai' => $v->tglsampai,
            'sasaran' => $v->sasaran,
            'rincian' => $v->rincian,
            'jenis' => $v->jenis,
            'kuatpers' => $v->kuatpers,
            'lokasi' => $v->lokasi,
            'pic' => $v->pic,
            'target' => $v->target,
        ]);

        return response()->json($data);
    }

    public function getKategoriKml(Request $request)
    {
        return app(PemetaanNewController::class)->getKategoriKml($request);
    }

    public function getKml(Request $request)
    {
        return app(PemetaanNewController::class)->getKml($request);
    }

    public function getVip(Request $request)
    {
        $query = DB::connection('satupeta')->table('tmc_data_vip');
        if ($request->filled('rowid')) {
            $query->where('rowid', $request->input('rowid'));
        }

        $data = $query->get()->map(fn ($v) => [
            'id' => $v->rowid,
            'jalan' => $v->jalan,
            'route' => json_decode($v->route),
        ]);

        return response()->json($data);
    }

    public function heatLaka()
    {
        $rows = DB::connection('satupeta')
            ->select('SELECT detil, lat, lng, count(*) as jumlah FROM tmc_data_rawan WHERE status = "Rawan Laka" GROUP BY lat, lng');

        return response()->json($rows);
    }
}
