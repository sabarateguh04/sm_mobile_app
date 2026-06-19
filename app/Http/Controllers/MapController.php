<?php

namespace App\Http\Controllers;

use App\Helpers\HierarchicalHelper;
use Illuminate\Support\Facades\DB;

/**
 * Peta Monitoring.
 * Mengambil data Posyan (posko), Terminal, dan Data Gangguan dari database `sbo`,
 * lalu parse kolom `koordinat` (format "lat,lng") jadi marker di peta.
 *
 * Filter wilayah/daerah user (kolom wilayah/daerah baris, petugas `piket`, atau profil posko terkait).
 */
class MapController extends Controller
{
    public function index()
    {
        $userCenter = null;
        $userZoom = 12; // Default zoom level for user center

        if (auth()->check()) {
            $user = auth()->user();
            $userDaerah = $user->daerah ?? '';
            $userWilayah = $user->wilayah ?? '';

            if (!empty($userWilayah) && \Illuminate\Support\Facades\Schema::hasTable('master_kabupaten_kota')) {
                $cleaned = $this->cleanTerritoryName($userWilayah);
                $query = DB::table('master_kabupaten_kota')->where('nama', 'LIKE', '%' . $cleaned . '%');
                
                // Prioritaskan "Kota" jika input memiliki kata "kota" atau "polresta"
                if (str_contains(strtolower($userWilayah), 'kota') || str_contains(strtolower($userWilayah), 'polresta')) {
                    $query->orderByRaw("CASE WHEN nama LIKE '%Kota%' THEN 0 ELSE 1 END");
                } else {
                    $query->orderByRaw("CASE WHEN nama NOT LIKE '%Kota%' THEN 0 ELSE 1 END");
                }
                
                $wilayahRow = $query->first();
                if ($wilayahRow && $wilayahRow->lat && $wilayahRow->lng) {
                    $userCenter = [(float)$wilayahRow->lat, (float)$wilayahRow->lng];
                    $userZoom = 13; // Zoom lebih dekat untuk Polres/Kabupaten
                }
            }

            if (!$userCenter && !empty($userDaerah) && \Illuminate\Support\Facades\Schema::hasTable('master_provinsi')) {
                $cleaned = $this->cleanTerritoryName($userDaerah);
                $daerahRow = DB::table('master_provinsi')->where('nama', 'LIKE', '%' . $cleaned . '%')->first();
                if ($daerahRow && $daerahRow->lat && $daerahRow->lng) {
                    $userCenter = [(float)$daerahRow->lat, (float)$daerahRow->lng];
                    $userZoom = 9; // Zoom lebih luas untuk Polda/Provinsi
                }
            }
        }

        $bypassTerritory = true;
        if (auth()->check()) {
            $bypassTerritory = HierarchicalHelper::viewerBypassesTerritoryScope();
        }

        return view('map.index', [
            'markers' => $this->getPoskoMarkers(),
            'terminals' => $this->getTerminalMarkers(),
            'jalanGangguan' => $this->getJalanGangguanMarkers(),
            'contigency' => $this->getContigencyMarkers(),
            'trafficstuck' => $this->getTrafficstuckMarkers(),
            'userCenter' => $userCenter,
            'userZoom' => $userZoom,
            'userDaerah' => auth()->check() ? (auth()->user()->daerah ?? '') : '',
            'userWilayah' => auth()->check() ? (auth()->user()->wilayah ?? '') : '',
            'bypassTerritory' => $bypassTerritory,
            'userLevel' => auth()->check() ? HierarchicalHelper::getUserLevel() : 'nasional',
        ]);
    }

    /**
     * Pin Posko (posyan).
     */
    private function getPoskoMarkers(): array
    {
        if (!DB::getSchemaBuilder()->hasTable('pos_pelayanan')) return [];
        if (!DB::getSchemaBuilder()->hasColumn('pos_pelayanan', 'koordinat')) return [];

        $query = $this->scopedQuery('pos_pelayanan', 'id_posyan')
            ->select('pos_pelayanan.id_posyan as id', 'pos_pelayanan.namaposyan as nama',
                'pos_pelayanan.lokasiposyan as lokasi', 'pos_pelayanan.koordinat')
            ->whereNotNull('pos_pelayanan.koordinat')
            ->where('pos_pelayanan.koordinat', '!=', '');
            
        if (DB::getSchemaBuilder()->hasColumn('pos_pelayanan', 'piket')) {
            $query->leftJoin('usm', 'pos_pelayanan.piket', '=', 'usm.id_usm')
                  ->addSelect('usm.nama as petugas', 'usm.fotoprof as fotoprof');
        }

        $rows = $query->get();
        return $this->mapToMarkers($rows, 'posko_');
    }

    /**
     * Pin Terminal (dataterminal) — kolom `koordinat` SUDAH ADA di sbo.
     */
    private function getTerminalMarkers(): array
    {
        if (!DB::getSchemaBuilder()->hasTable('data_terminal')) return [];

        $query = $this->scopedQuery('data_terminal', 'id_terminal')
            ->select('data_terminal.id_terminal as id', 'data_terminal.namaterminal as nama',
                'data_terminal.jalan as lokasi', 'data_terminal.koordinat',
                'data_terminal.jenisterminal as jenis')
            ->whereNotNull('data_terminal.koordinat')
            ->where('data_terminal.koordinat', '!=', '');
            
        if (DB::getSchemaBuilder()->hasColumn('data_terminal', 'piket')) {
            $query->leftJoin('usm', 'data_terminal.piket', '=', 'usm.id_usm')
                  ->addSelect('usm.nama as petugas', 'usm.fotoprof as fotoprof');
        }

        $rows = $query->get();
        return $this->mapToMarkers($rows, 'terminal_', 'jenis');
    }

    /**
     * Pin Jalan Gangguan (datagangguan).
     * Kolom `koordinat` ditambah lewat migration agar bisa di-plot di peta.
     */
    private function getJalanGangguanMarkers(): array
    {
        if (!DB::getSchemaBuilder()->hasTable('data_gangguan')) return [];
        if (!DB::getSchemaBuilder()->hasColumn('data_gangguan', 'koordinat')) return [];

        $query = $this->scopedQuery('data_gangguan', 'id_datagangguan')
            ->select('data_gangguan.id_datagangguan as id', 'data_gangguan.namajalan as nama',
                'data_gangguan.fungsijalan as lokasi', 'data_gangguan.koordinat',
                'data_gangguan.levelgangguan as status')
            ->whereNotNull('data_gangguan.koordinat')
            ->where('data_gangguan.koordinat', '!=', '');
            
        if (DB::getSchemaBuilder()->hasColumn('data_gangguan', 'piket')) {
            $query->leftJoin('usm', 'data_gangguan.piket', '=', 'usm.id_usm')
                  ->addSelect('usm.nama as petugas', 'usm.fotoprof as fotoprof');
        }

        $rows = $query->get();
        return $this->mapToMarkers($rows, 'jalan_', null, 'status');
    }

    private function getContigencyMarkers(): array
    {
        if (!DB::getSchemaBuilder()->hasTable('contigency')) return [];
        if (!DB::getSchemaBuilder()->hasColumn('contigency', 'koordinat')) return [];

        $query = $this->scopedQuery('contigency', 'id_contigency')
            ->select('contigency.id_contigency as id', 'contigency.jeniscontigency as nama',
                'contigency.ketcb as lokasi', 'contigency.koordinat', 'contigency.fotosit as foto_giat')
            ->whereNotNull('contigency.koordinat')
            ->where('contigency.koordinat', '!=', '');
            
        if (DB::getSchemaBuilder()->hasColumn('contigency', 'piket')) {
            $query->leftJoin('usm', 'contigency.piket', '=', 'usm.id_usm')
                  ->addSelect('usm.nama as petugas', 'usm.fotoprof as fotoprof');
        }

        $rows = $query->get();
        return $this->mapToMarkers($rows, 'con_');
    }

    private function getTrafficstuckMarkers(): array
    {
        if (!DB::getSchemaBuilder()->hasTable('trafficstuck')) return [];
        if (!DB::getSchemaBuilder()->hasColumn('trafficstuck', 'koordinat')) return [];

        $query = $this->scopedQuery('trafficstuck', 'id_trafficstuck')
            ->select('trafficstuck.id_trafficstuck as id', 'trafficstuck.faktor as nama',
                'trafficstuck.ketcb as lokasi', 'trafficstuck.koordinat', 'trafficstuck.fotosituasi as foto_giat')
            ->whereNotNull('trafficstuck.koordinat')
            ->where('trafficstuck.koordinat', '!=', '');
            
        if (DB::getSchemaBuilder()->hasColumn('trafficstuck', 'piket')) {
            $query->leftJoin('usm', 'trafficstuck.piket', '=', 'usm.id_usm')
                  ->addSelect('usm.nama as petugas', 'usm.fotoprof as fotoprof');
        }

        $rows = $query->get();
        return $this->mapToMarkers($rows, 'ts_');
    }

    /**
     * Bangun query untuk marker — filter wilayah/daerah user lewat helper terpusat.
     */
    private function scopedQuery(string $table, string $pk)
    {
        $query = DB::table($table);
        HierarchicalHelper::applyTerritoryScopeForTable($query, $table);

        return $query;
    }

    /**
     * Konversi rows ke struktur marker untuk Leaflet.
     */
    private function mapToMarkers($rows, string $prefix, ?string $jenisKey = null, ?string $statusKey = null): array
    {
        $out = [];
        foreach ($rows as $r) {
            $latLng = $this->parseKoordinat($r->koordinat);
            if (!$latLng) continue;
            $marker = [
                'id' => $prefix . $r->id,
                'name' => $r->nama ?? '',
                'coordinates' => $latLng,
                'lokasi' => $r->lokasi ?? '',
            ];
            if ($jenisKey && property_exists($r, $jenisKey)) $marker['jenis'] = $r->{$jenisKey};
            if ($statusKey && property_exists($r, $statusKey)) $marker['status'] = $r->{$statusKey};
            if (property_exists($r, 'petugas') && !empty($r->petugas)) $marker['petugas'] = $r->petugas;
            if (property_exists($r, 'fotoprof') && !empty($r->fotoprof)) $marker['fotoprof'] = $r->fotoprof;
            if (property_exists($r, 'foto_giat') && !empty($r->foto_giat)) $marker['foto_giat'] = $r->foto_giat;
            $out[] = $marker;
        }
        return $out;
    }

    /**
     * Parse string "lat,lng" → [lat, lng].
     */
    private function parseKoordinat(?string $val): ?array
    {
        if (empty($val)) return null;
        $parts = array_map('trim', explode(',', $val));
        if (count($parts) !== 2) return null;
        if (!is_numeric($parts[0]) || !is_numeric($parts[1])) return null;
        return [(float) $parts[0], (float) $parts[1]];
    }

    /**
     * Membersihkan nama daerah/wilayah dari prefix/suffix institusi atau administratif.
     */
    private function cleanTerritoryName(string $name): string
    {
        $name = strtolower($name);
        $search = [
            'polda', 'polresta', 'polres metro', 'polres/metro', 'polres',
            'kabupaten', 'kab.', 'kota', 'provinsi', 'prov.'
        ];
        $name = str_replace($search, '', $name);
        return trim($name);
    }
}
