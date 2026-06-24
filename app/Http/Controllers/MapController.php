<?php

namespace App\Http\Controllers;

use App\Helpers\HierarchicalHelper;
use App\Helpers\MenuHelper;
use App\Support\DatabaseConnections;
use App\Support\SmTerritoryData;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
 * Peta Monitoring.
 * Mengambil data operasional dari DB utama (`mysql`),
 * koordinat wilayah dari DB kedua (`mysql_second` / test.sql).
 */
class MapController extends Controller
{
    /** Viewport default peta nasional (Executive1 / seluruh Indonesia). */
    private const INDONESIA_MAP_CENTER = [-2.548926, 118.014863];

    private const INDONESIA_MAP_ZOOM = 5;

    /** Zoom viewport provinsi (Executive2 / Polda). */
    private const PROVINCE_MAP_ZOOM = 9;

    public function index()
    {
        $userCenter = null;
        $userZoom = 14;
        $user = Auth::user();
        $userDaerah = '';
        $userWilayah = '';
        $isExecutiveNational = $user !== null && MenuHelper::isExecutiveNationalUser($user);
        $isExecutiveProvinsi = $user !== null && MenuHelper::isExecutiveProvinsiUser($user);

        if ($isExecutiveNational) {
            // Executive1 (pusat): seluruh Indonesia — abaikan profil daerah/wilayah untuk viewport & batas peta
            $userCenter = self::INDONESIA_MAP_CENTER;
            $userZoom = self::INDONESIA_MAP_ZOOM;
        } elseif ($user !== null) {
            $userDaerah = trim((string) ($user->daerah ?? ''));
            $userWilayah = trim((string) ($user->wilayah ?? ''));

            if (! empty($userWilayah)) {
                $wilayahRow = SmTerritoryData::findPolresByName($userWilayah, $userDaerah ?: null);

                if ($wilayahRow) {
                    if (! empty($wilayahRow->polres)) {
                        $userWilayah = $wilayahRow->polres;
                    }
                    [$lat, $lng] = SmTerritoryData::parseCoordinate($wilayahRow->lat ?? null, $wilayahRow->long ?? null);
                    if ($lat !== null && $lng !== null) {
                        $userCenter = [$lat, $lng];
                        $userZoom = 15;
                    }
                } elseif (DatabaseConnections::operationalSchema()->hasTable('master_kabupaten_kota')) {
                    $cleaned = $this->cleanTerritoryName($userWilayah);
                    $query = DatabaseConnections::operationalDb()->table('master_kabupaten_kota')->where('nama', 'LIKE', '%'.$cleaned.'%');

                    if (str_contains(strtolower($userWilayah), 'kota') || str_contains(strtolower($userWilayah), 'polresta') || str_contains(strtolower($userWilayah), 'polrestabes') || str_contains(strtolower($userWilayah), 'metro') || str_contains(strtolower($userWilayah), 'jakarta')) {
                        $query->orderByRaw("CASE WHEN nama LIKE '%Kota%' THEN 0 ELSE 1 END");
                    } else {
                        $query->orderByRaw("CASE WHEN nama NOT LIKE '%Kota%' THEN 0 ELSE 1 END");
                    }

                    $wilayahRow = $query->first();
                    if ($wilayahRow) {
                        if (! empty($wilayahRow->nama)) {
                            $userWilayah = $wilayahRow->nama;
                        }
                        if (DatabaseConnections::operationalSchema()->hasColumn('master_kabupaten_kota', 'lat') && $wilayahRow->lat && $wilayahRow->lng) {
                            $userCenter = [(float) $wilayahRow->lat, (float) $wilayahRow->lng];
                            $userZoom = 15;
                        }
                    }
                }
            }

            if (! empty($userDaerah)) {
                $daerahRow = SmTerritoryData::findPoldaByName($userDaerah);

                if ($daerahRow) {
                    if (! empty($daerahRow->polda)) {
                        $userDaerah = $daerahRow->polda;
                    }
                    if (! $userCenter) {
                        [$lat, $lng] = SmTerritoryData::parseCoordinate($daerahRow->lat ?? null, $daerahRow->lng ?? null);
                        if ($lat !== null && $lng !== null) {
                            $userCenter = [$lat, $lng];
                            $userZoom = 11;
                        }
                    }
                } elseif (DatabaseConnections::operationalSchema()->hasTable('master_provinsi')) {
                    $cleaned = $this->cleanTerritoryName($userDaerah);
                    $daerahRow = DatabaseConnections::operationalDb()->table('master_provinsi')->where('nama', 'LIKE', '%'.$cleaned.'%')->first();
                    if ($daerahRow) {
                        if (! empty($daerahRow->nama)) {
                            $userDaerah = $daerahRow->nama;
                        }
                        if (! $userCenter && DatabaseConnections::operationalSchema()->hasColumn('master_provinsi', 'lat') && $daerahRow->lat && $daerahRow->lng) {
                            $userCenter = [(float) $daerahRow->lat, (float) $daerahRow->lng];
                            $userZoom = 11;
                        }
                    }
                }
            }
        }

        if ($isExecutiveProvinsi && $userDaerah !== '' && $userWilayah === '') {
            // Executive2: pastikan viewport mengarah ke provinsi kerja dengan zoom provinsi
            if ($userCenter === null) {
                [$userCenter, $userZoom] = $this->resolveProvinceMapViewport($userDaerah);
            } else {
                $userZoom = max($userZoom, self::PROVINCE_MAP_ZOOM);
            }
        }

        $bypassTerritory = $user === null || HierarchicalHelper::viewerBypassesTerritoryScope();

        return view('map.index', [
            'markers' => $this->getPoskoMarkers(),
            'terminals' => $this->getTerminalMarkers(),
            'jalanGangguan' => $this->getJalanGangguanMarkers(),
            'contigency' => $this->getContigencyMarkers(),
            'trafficstuck' => $this->getTrafficstuckMarkers(),
            'turjagwali' => $this->getTurjagwaliMarkers(),
            'auditarus' => $this->getAuditarusMarkers(),
            'pnpangkutan' => $this->getPnpangkutanMarkers(),
            'objekwisata' => $this->getObjekWisataMarkers(),
            'tempatibadah' => $this->getTempatIbadahMarkers(),
            'datafaskes' => $this->getDataFaskesMarkers(),
            'cctv' => $this->getCctvMarkers(),
            'userCenter' => $userCenter,
            'userZoom' => $userZoom,
            'userDaerah' => $userDaerah,
            'userWilayah' => $userWilayah,
            'bypassTerritory' => $bypassTerritory,
            'userLevel' => $user !== null
                ? (MenuHelper::effectiveTerritoryLevelForUser($user) ?? HierarchicalHelper::getUserLevel())
                : 'nasional',
            'currentUserId' => $user?->id_usm ?? null,
            'isSuperAdmin' => $user !== null && MenuHelper::isSuperAdminForLayout($user),
            'isExecutiveNational' => $isExecutiveNational,
            'isExecutiveProvinsi' => $isExecutiveProvinsi,
        ]);
    }

    /**
     * @return array{0: ?array{0: float, 1: float}, 1: int}
     */
    private function resolveProvinceMapViewport(string $daerah): array
    {
        $daerahRow = SmTerritoryData::findPoldaByName($daerah);

        if ($daerahRow) {
            [$lat, $lng] = SmTerritoryData::parseCoordinate($daerahRow->lat ?? null, $daerahRow->lng ?? null);
            if ($lat !== null && $lng !== null) {
                return [[$lat, $lng], self::PROVINCE_MAP_ZOOM];
            }
        }

        if (DatabaseConnections::operationalSchema()->hasTable('master_provinsi')) {
            $cleaned = $this->cleanTerritoryName($daerah);
            $daerahRow = DatabaseConnections::operationalDb()
                ->table('master_provinsi')
                ->where('nama', 'LIKE', '%'.$cleaned.'%')
                ->first();

            if ($daerahRow
                && DatabaseConnections::operationalSchema()->hasColumn('master_provinsi', 'lat')
                && $daerahRow->lat
                && $daerahRow->lng) {
                return [[(float) $daerahRow->lat, (float) $daerahRow->lng], self::PROVINCE_MAP_ZOOM];
            }
        }

        return [null, self::PROVINCE_MAP_ZOOM];
    }

    /**
     * AJAX: Update koordinat marker oleh operator yang bersangkutan.
     *
     * Payload: { table, id, lat, lng }
     * Otorisasi: hanya marker milik user sendiri (kolom piket = id_usm), kecuali SuperAdmin.
     */
    public function updateKoordinat(Request $request): JsonResponse
    {
        $user = Auth::user();
        if (! $user) {
            return response()->json(['success' => false, 'message' => 'Unauthenticated.'], 401);
        }

        $validated = $request->validate([
            'table' => ['required', 'string', 'in:pos_pelayanan,data_terminal,data_gangguan,contigency,trafficstuck,turjagwali,auditarus,pnp_angkutan,objek_wisata,tempatibadah,data_faskes,smpc_tbl_data_cctv'],
            'id'    => ['required', 'integer', 'min:1'],
            'lat'   => ['required', 'numeric', 'between:-90,90'],
            'lng'   => ['required', 'numeric', 'between:-180,180'],
        ]);

        $table = $validated['table'];
        $id    = (int) $validated['id'];
        $lat   = $validated['lat'];
        $lng   = $validated['lng'];

        $isSuperAdmin = MenuHelper::isSuperAdminForLayout($user);

        if ($table === 'smpc_tbl_data_cctv') {
            if (!$isSuperAdmin) {
                return response()->json(['success' => false, 'message' => 'Hanya SuperAdmin yang dapat memindahkan koordinat CCTV.'], 403);
            }

            $dbSatupeta = DB::connection('satupeta');
            $row = $dbSatupeta->table('smpc_tbl_data_cctv')->where('pk_ids', $id)->first();
            if (!$row) {
                return response()->json(['success' => false, 'message' => 'Data CCTV tidak ditemukan.'], 404);
            }

            $dbSatupeta->table('smpc_tbl_data_cctv')->where('pk_ids', $id)->update([
                'lat' => round($lat, 7),
                'lng' => round($lng, 7),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Koordinat CCTV berhasil diperbarui.'
            ]);
        }

        // Primary key per tabel
        $pkMap = [
            'pos_pelayanan'   => 'id_posyan',
            'data_terminal'   => 'id_terminal',
            'data_gangguan'   => 'id_datagangguan',
            'contigency'      => 'id_contigency',
            'trafficstuck'    => 'id_trafficstuck',
            'turjagwali'      => 'id_turjagwali',
            'auditarus'       => 'id_auditarus',
            'pnp_angkutan'    => 'id_datapnp',
            'objek_wisata'    => 'id_tempatwisata',
            'tempatibadah'    => 'id_tempatibadah',
            'data_faskes'     => 'id_faskes',
        ];
        $pk = $pkMap[$table] ?? null;
        $opSchema = DatabaseConnections::operationalSchema();
        $opDb = DatabaseConnections::operationalDb();

        if (! $pk || ! $opSchema->hasTable($table) || ! $opSchema->hasColumn($table, 'koordinat')) {
            return response()->json(['success' => false, 'message' => 'Tabel atau kolom koordinat tidak ditemukan.'], 422);
        }

        $row = $opDb->table($table)->where($pk, $id)->first();
        if (! $row) {
            return response()->json(['success' => false, 'message' => 'Data tidak ditemukan.'], 404);
        }

        // Otorisasi: SuperAdmin bisa ubah semua; selain itu hanya milik sendiri (piket = id_usm)
        if (! $isSuperAdmin) {
            $rowPiket = $row->piket ?? null;
            if ((string) $rowPiket !== (string) $user->id_usm) {
                return response()->json(['success' => false, 'message' => 'Anda hanya dapat memindahkan marker milik Anda sendiri.'], 403);
            }
        }

        $opDb->table($table)->where($pk, $id)->update([
            'koordinat' => round($lat, 7) . ',' . round($lng, 7),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Koordinat berhasil diperbarui.',
            'koordinat' => round($lat, 7) . ',' . round($lng, 7),
        ]);
    }

    /**
     * Pin Posko (posyan).
     */
    private function getPoskoMarkers(): array
    {
        if (!DatabaseConnections::operationalSchema()->hasTable('pos_pelayanan')) return [];
        if (!DatabaseConnections::operationalSchema()->hasColumn('pos_pelayanan', 'koordinat')) return [];

        $query = $this->scopedQuery('pos_pelayanan', 'id_posyan')
            ->select('pos_pelayanan.id_posyan as id', 'pos_pelayanan.namaposyan as nama',
                'pos_pelayanan.lokasiposyan as lokasi', 'pos_pelayanan.koordinat')
            ->whereNotNull('pos_pelayanan.koordinat')
            ->where('pos_pelayanan.koordinat', '!=', '');
            
        if (DatabaseConnections::operationalSchema()->hasColumn('pos_pelayanan', 'piket')) {
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
        if (!DatabaseConnections::operationalSchema()->hasTable('data_terminal')) return [];

        $query = $this->scopedQuery('data_terminal', 'id_terminal')
            ->select('data_terminal.id_terminal as id', 'data_terminal.namaterminal as nama',
                'data_terminal.jalan as lokasi', 'data_terminal.koordinat',
                'data_terminal.jenisterminal as jenis')
            ->whereNotNull('data_terminal.koordinat')
            ->where('data_terminal.koordinat', '!=', '');
            
        if (DatabaseConnections::operationalSchema()->hasColumn('data_terminal', 'piket')) {
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
        if (!DatabaseConnections::operationalSchema()->hasTable('data_gangguan')) return [];
        if (!DatabaseConnections::operationalSchema()->hasColumn('data_gangguan', 'koordinat')) return [];

        $query = $this->scopedQuery('data_gangguan', 'id_datagangguan')
            ->select('data_gangguan.id_datagangguan as id', 'data_gangguan.namajalan as nama',
                'data_gangguan.fungsijalan as lokasi', 'data_gangguan.koordinat',
                'data_gangguan.levelgangguan as status')
            ->whereNotNull('data_gangguan.koordinat')
            ->where('data_gangguan.koordinat', '!=', '');
            
        if (DatabaseConnections::operationalSchema()->hasColumn('data_gangguan', 'piket')) {
            $query->leftJoin('usm', 'data_gangguan.piket', '=', 'usm.id_usm')
                  ->addSelect('usm.nama as petugas', 'usm.fotoprof as fotoprof');
        }

        $rows = $query->get();
        return $this->mapToMarkers($rows, 'jalan_', null, 'status');
    }

    private function getContigencyMarkers(): array
    {
        if (!DatabaseConnections::operationalSchema()->hasTable('contigency')) return [];
        if (!DatabaseConnections::operationalSchema()->hasColumn('contigency', 'koordinat')) return [];

        $query = $this->scopedQuery('contigency', 'id_contigency')
            ->select('contigency.id_contigency as id', 'contigency.jeniscontigency as nama',
                'contigency.ketcb as lokasi', 'contigency.koordinat', 'contigency.fotosit as foto_giat')
            ->whereNotNull('contigency.koordinat')
            ->where('contigency.koordinat', '!=', '');
            
        if (DatabaseConnections::operationalSchema()->hasColumn('contigency', 'piket')) {
            $query->leftJoin('usm', 'contigency.piket', '=', 'usm.id_usm')
                  ->addSelect('usm.nama as petugas', 'usm.fotoprof as fotoprof');
        }

        $rows = $query->get();
        return $this->mapToMarkers($rows, 'con_');
    }

    private function getTrafficstuckMarkers(): array
    {
        if (!DatabaseConnections::operationalSchema()->hasTable('trafficstuck')) return [];
        if (!DatabaseConnections::operationalSchema()->hasColumn('trafficstuck', 'koordinat')) return [];

        $query = $this->scopedQuery('trafficstuck', 'id_trafficstuck')
            ->select('trafficstuck.id_trafficstuck as id', 'trafficstuck.faktor as nama',
                'trafficstuck.ketcb as lokasi', 'trafficstuck.koordinat', 'trafficstuck.fotosituasi as foto_giat')
            ->whereNotNull('trafficstuck.koordinat')
            ->where('trafficstuck.koordinat', '!=', '');
            
        if (DatabaseConnections::operationalSchema()->hasColumn('trafficstuck', 'piket')) {
            $query->leftJoin('usm', 'trafficstuck.piket', '=', 'usm.id_usm')
                  ->addSelect('usm.nama as petugas', 'usm.fotoprof as fotoprof');
        }

        $rows = $query->get();
        return $this->mapToMarkers($rows, 'ts_');
    }

    /**
     * Pin Turjagwali.
     */
    private function getTurjagwaliMarkers(): array
    {
        if (!DatabaseConnections::operationalSchema()->hasTable('turjagwali')) return [];
        if (!DatabaseConnections::operationalSchema()->hasColumn('turjagwali', 'koordinat')) return [];

        $query = $this->scopedQuery('turjagwali', 'id_turjagwali')
            ->select('turjagwali.id_turjagwali as id', 'turjagwali.katgiat as nama',
                'turjagwali.lokasi as lokasi', 'turjagwali.koordinat', 'turjagwali.fotogiat as foto_giat')
            ->whereNotNull('turjagwali.koordinat')
            ->where('turjagwali.koordinat', '!=', '');
            
        if (DatabaseConnections::operationalSchema()->hasColumn('turjagwali', 'piket')) {
            $query->leftJoin('usm', 'turjagwali.piket', '=', 'usm.id_usm')
                  ->addSelect('usm.nama as petugas', 'usm.fotoprof as fotoprof');
        }

        $rows = $query->get();
        return $this->mapToMarkers($rows, 'tj_');
    }

    /**
     * Pin Audit Arus.
     */
    private function getAuditarusMarkers(): array
    {
        if (!DatabaseConnections::operationalSchema()->hasTable('auditarus')) return [];
        if (!DatabaseConnections::operationalSchema()->hasColumn('auditarus', 'koordinat')) return [];

        $query = $this->scopedQuery('auditarus', 'id_auditarus')
            ->select('auditarus.id_auditarus as id', 'auditarus.hasil_audit as nama',
                'auditarus.catatan as lokasi', 'auditarus.koordinat', 'auditarus.fotolokasi as foto_giat')
            ->whereNotNull('auditarus.koordinat')
            ->where('auditarus.koordinat', '!=', '');
            
        if (DatabaseConnections::operationalSchema()->hasColumn('auditarus', 'piket')) {
            $query->leftJoin('usm', 'auditarus.piket', '=', 'usm.id_usm')
                  ->addSelect('usm.nama as petugas', 'usm.fotoprof as fotoprof');
        }

        $rows = $query->get();
        return $this->mapToMarkers($rows, 'aa_');
    }

    private function getObjekWisataMarkers(): array
    {
        if (!DatabaseConnections::operationalSchema()->hasTable('objek_wisata')) return [];
        if (!DatabaseConnections::operationalSchema()->hasColumn('objek_wisata', 'koordinat')) return [];

        $query = $this->scopedQuery('objek_wisata', 'id_tempatwisata')
            ->select('objek_wisata.id_tempatwisata as id', 'objek_wisata.nametempat as nama',
                'objek_wisata.lokasi as lokasi', 'objek_wisata.koordinat', 'objek_wisata.fotoobject as foto_giat')
            ->whereNotNull('objek_wisata.koordinat')
            ->where('objek_wisata.koordinat', '!=', '');
            
        if (DatabaseConnections::operationalSchema()->hasColumn('objek_wisata', 'piket')) {
            $query->leftJoin('usm', 'objek_wisata.piket', '=', 'usm.id_usm')
                  ->addSelect('usm.nama as petugas', 'usm.fotoprof as fotoprof');
        }

        $rows = $query->get();
        return $this->mapToMarkers($rows, 'wisata_');
    }

    private function getTempatIbadahMarkers(): array
    {
        if (!DatabaseConnections::operationalSchema()->hasTable('tempatibadah')) return [];
        if (!DatabaseConnections::operationalSchema()->hasColumn('tempatibadah', 'koordinat')) return [];

        $query = $this->scopedQuery('tempatibadah', 'id_tempatibadah')
            ->select('tempatibadah.id_tempatibadah as id', 'tempatibadah.namatempatibadah as nama',
                'tempatibadah.lokasi as lokasi', 'tempatibadah.koordinat', 'tempatibadah.fotoobject as foto_giat')
            ->whereNotNull('tempatibadah.koordinat')
            ->where('tempatibadah.koordinat', '!=', '');
            
        if (DatabaseConnections::operationalSchema()->hasColumn('tempatibadah', 'piket')) {
            $query->leftJoin('usm', 'tempatibadah.piket', '=', 'usm.id_usm')
                  ->addSelect('usm.nama as petugas', 'usm.fotoprof as fotoprof');
        }

        $rows = $query->get();
        return $this->mapToMarkers($rows, 'ibadah_');
    }

    private function getDataFaskesMarkers(): array
    {
        if (!DatabaseConnections::operationalSchema()->hasTable('data_faskes')) return [];
        if (!DatabaseConnections::operationalSchema()->hasColumn('data_faskes', 'koordinat')) return [];

        $query = $this->scopedQuery('data_faskes', 'id_faskes')
            ->select('data_faskes.id_faskes as id', 'data_faskes.namafaskes as nama',
                'data_faskes.lokasi as lokasi', 'data_faskes.koordinat', 'data_faskes.fotoobject as foto_giat')
            ->whereNotNull('data_faskes.koordinat')
            ->where('data_faskes.koordinat', '!=', '');
            
        if (DatabaseConnections::operationalSchema()->hasColumn('data_faskes', 'piket')) {
            $query->leftJoin('usm', 'data_faskes.piket', '=', 'usm.id_usm')
                  ->addSelect('usm.nama as petugas', 'usm.fotoprof as fotoprof');
        }

        $rows = $query->get();
        return $this->mapToMarkers($rows, 'faskes_');
    }

    /**
     * Pin PNP Angkutan.
     */
    private function getPnpangkutanMarkers(): array
    {
        if (!DatabaseConnections::operationalSchema()->hasTable('pnp_angkutan')) return [];
        if (!DatabaseConnections::operationalSchema()->hasColumn('pnp_angkutan', 'koordinat')) return [];

        $query = $this->scopedQuery('pnp_angkutan', 'id_datapnp')
            ->select('pnp_angkutan.id_datapnp as id', 'pnp_angkutan.namaterminal as nama',
                'pnp_angkutan.jenisterminal as lokasi', 'pnp_angkutan.koordinat', 'pnp_angkutan.fotogiat as foto_giat')
            ->whereNotNull('pnp_angkutan.koordinat')
            ->where('pnp_angkutan.koordinat', '!=', '');
            
        if (DatabaseConnections::operationalSchema()->hasColumn('pnp_angkutan', 'piket')) {
            $query->leftJoin('usm', 'pnp_angkutan.piket', '=', 'usm.id_usm')
                  ->addSelect('usm.nama as petugas', 'usm.fotoprof as fotoprof');
        }

        $rows = $query->get();
        return $this->mapToMarkers($rows, 'pa_');
    }

    /**
     * Bangun query untuk marker — filter wilayah/daerah user lewat helper terpusat.
     */
    private function scopedQuery(string $table, string $pk)
    {
        $query = DatabaseConnections::operationalDb()->table($table);
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
            'polrestabes metro', 'polresta metro', 'polres metro', 'polres/metro',
            'polrestabes', 'polresta', 'polres', 'polda',
            'kabupaten', 'kab.', 'kota administrasi', 'kota adm.', 'kota', 'provinsi', 'prov.'
        ];
        $name = str_replace($search, '', $name);
        return trim($name);
    }

    /**
     * Ambil data kamera CCTV live sebagai markers.
     */
    private function getCctvMarkers(): array
    {
        if (!\App\Support\CctvLiveRegistry::isAvailable()) {
            return [];
        }

        try {
            $cameras = \App\Support\CctvLiveRegistry::listCameras();
            $out = [];
            foreach ($cameras as $cam) {
                $latLng = $this->parseKoordinat($cam->kordinat);
                if (!$latLng) continue;

                $out[] = [
                    'id' => 'cctv_' . $cam->id,
                    'name' => $cam->nama_cctv ?? '',
                    'coordinates' => $latLng,
                    'channel_id' => $cam->channel_id ?? '',
                    'rtsp_url' => $cam->rtsp_cctv ?? '',
                    'stream_url' => route('cctv-live.show', $cam->id),
                    'status' => $cam->status ?? 'aktif',
                ];
            }
            return $out;
        } catch (\Throwable) {
            return [];
        }
    }
}
