<?php

namespace App\Http\Controllers\Satupeta;

use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redirect;

class PemetaanNewController extends BaseSatupetaController
{
    public function index(): View
    {
        return view('satupeta.satupeta_baru', $this->petaViewData());
    }

    public function getKategoriKml(Request $request): JsonResponse
    {
        $query = DB::connection('satupeta')->table('kml_kategori');

        if ($request->filled('polda_id')) {
            $query->where('polda', $request->input('polda_id'));
        }
        if ($request->filled('polres_id')) {
            $query->orWhere('polres', $request->input('polres_id'));
        }

        $data = $query->get()->map(fn ($v) => [
            'id' => $v->rowid,
            'value' => $v->value,
        ])->values();

        return response()->json($data);
    }

    public function getKml(Request $request): JsonResponse
    {
        $query = DB::connection('satupeta')->table('maps_kml');

        if ($request->filled('kategori_kml_id')) {
            $query->where('kategori_kml_id', $request->input('kategori_kml_id'));
        }
        if ($request->filled('polda_id')) {
            $query->where('polda', $request->input('polda_id'));
        }
        if ($request->filled('polres_id')) {
            $query->orWhere('polres', $request->input('polres_id'));
        }

        $data = $query->get()->map(fn ($v) => [
            'id' => $v->rowid,
            'nama' => $v->nama,
            'file' => satupeta_asset('kml/' . $v->file),
            'polda' => $v->polda,
            'polres' => $v->polres,
            'kategori_kml_id' => $v->kategori_kml_id,
            'ctddate' => tgl_indo($v->ctddate),
        ])->values();

        return response()->json($data);
    }

    public function dtRengiat(Request $request): RedirectResponse|Response
    {
        $bearer = $request->input('bearer');
        $filter = $request->input('filter', '');
        $token = $this->refreshToken((string) $bearer);

        if (! ($token->status ?? false)) {
            return Redirect::to((string) config('satupeta.url_bo'));
        }

        $response = \Illuminate\Support\Facades\Http::withHeaders([
            'Content-Type' => 'application/json',
            'token' => $this->apiToken(),
            'secondbearer' => $token->data->second_bearer ?? '',
        ])->withOptions(['verify' => false])
            ->get($this->apiBaseUrl() . 'data_collecting/rengiat?' . $filter);

        return response($response->body(), 200)->header('Content-Type', 'application/json');
    }

    public function getGeoJsonCctv(Request $request): JsonResponse
    {
        $table = \App\Http\Controllers\DataCctvController::readTableForUser();
        $pk = \App\Http\Controllers\DataCctvController::primaryKeyForTable($table);
        
        $cctvs = DB::table($table . ' as d')
            ->leftJoin('usm', 'd.piket', '=', 'usm.id_usm')
            ->select('d.' . $pk . ' as id', 'd.koordinat', 'd.fungsicctv as status', 'usm.wilayah as satwil')
            ->whereNotNull('d.koordinat')
            ->where('d.koordinat', '!=', '')
            ->get();

        $features = [];
        foreach ($cctvs as $cctv) {
            $coords = explode(',', $cctv->koordinat);
            if (count($coords) === 2) {
                $lat = (float) trim($coords[0]);
                $lng = (float) trim($coords[1]);
                
                $features[] = [
                    'type' => 'Feature',
                    'geometry' => [
                        'type' => 'Point',
                        'coordinates' => [$lng, $lat],
                    ],
                    'properties' => [
                        'id' => $cctv->id,
                        'status' => $cctv->status ?? 'Aktif',
                        'satwil' => $cctv->satwil ?? '',
                    ]
                ];
            }
        }

        return response()->json([
            'type' => 'FeatureCollection',
            'features' => $features,
        ]);
    }

    public function getDetailCctv(int $id): JsonResponse
    {
        $table = \App\Http\Controllers\DataCctvController::readTableForUser();
        $pk = \App\Http\Controllers\DataCctvController::primaryKeyForTable($table);

        $cctv = DB::table($table)->where($pk, $id)->first();

        if (!$cctv) {
            return response()->json(['error' => 'Data tidak ditemukan'], 404);
        }

        return response()->json([
            'id' => $cctv->{$pk},
            'brand' => $cctv->brand ?? '-',
            'owner' => $cctv->owner ?? '-',
            'lokasi' => $cctv->lokasi ?? '-',
            'ipaddres' => $cctv->ipaddres ?? '-',
            'fungsicctv' => $cctv->fungsicctv ?? '-',
            'tanggal' => $cctv->tanggal ?? '-',
        ]);
    }

    public function rToken(Request $request, string $bearer = '', string $out = ''): mixed
    {
        return $this->refreshToken($bearer, $out === 'Y');
    }
}
