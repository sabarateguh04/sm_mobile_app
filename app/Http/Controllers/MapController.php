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

    /**
     * Bounding box longgar wilayah Indonesia — dipakai untuk membuang koordinat sampah
     * (mis. "0,0"/null-island, titik desimal salah ketik, atau salah input di luar negeri)
     * supaya peta tidak ikut fitBounds ke titik liar dan tampilan jadi berantakan/zoom dunia.
     */
    private const INDONESIA_BBOX = ['lat_min' => -11.5, 'lat_max' => 6.5, 'lng_min' => 94.0, 'lng_max' => 141.5];

    /**
     * Validasi koordinat masuk akal untuk peta operasional (wilayah Indonesia).
     */
    private function isValidIndonesiaCoordinate(?float $lat, ?float $lng): bool
    {
        if ($lat === null || $lng === null) {
            return false;
        }
        if ($lat === 0.0 && $lng === 0.0) {
            return false;
        }

        return $lat >= self::INDONESIA_BBOX['lat_min'] && $lat <= self::INDONESIA_BBOX['lat_max']
            && $lng >= self::INDONESIA_BBOX['lng_min'] && $lng <= self::INDONESIA_BBOX['lng_max'];
    }

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
            'opsdal' => $this->getOpsdalMarkers(),
            'cctv' => $this->getCctvMarkers(),
            'layananData' => $this->getLayananDataMarkers(),
            'dacolBlackspot' => $this->getDacolMarkers('sm_dacol_black_trouble_spot', 'dacol_bs'),
            'dacolObjekVital' => $this->getDacolMarkers('sm_dacol_objek_vital', 'dacol_ov'),
            'dacolFasum' => $this->getDacolMarkers('sm_dacol_fasilitas_umum', 'dacol_fu'),
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
            'table' => ['required', 'string', 'in:pos_pelayanan,data_terminal,data_gangguan,contigency,trafficstuck,turjagwali,auditarus,pnp_angkutan,objek_wisata,tempatibadah,data_faskes,smpc_tbl_data_cctv,layanan_data,sm_dacol_objek_vital,sm_dacol_black_trouble_spot,sm_dacol_fasilitas_umum'],
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

        if ($table === 'sm_dacol_objek_vital') {
            if (!$isSuperAdmin) {
                return response()->json(['success' => false, 'message' => 'Hanya SuperAdmin yang dapat memindahkan objek vital.'], 403);
            }

            $dbSatupeta = DB::connection('satupeta');
            $row = $dbSatupeta->table('sm_dacol_objek_vital')->where('rowid', $id)->first();
            if (!$row) {
                return response()->json(['success' => false, 'message' => 'Data objek vital tidak ditemukan.'], 404);
            }

            $dbSatupeta->table('sm_dacol_objek_vital')->where('rowid', $id)->update([
                'lat' => round($lat, 7),
                'lng' => round($lng, 7),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Koordinat objek vital berhasil diperbarui.'
            ]);
        }

        if ($table === 'sm_dacol_black_trouble_spot') {
            if (!$isSuperAdmin) {
                return response()->json(['success' => false, 'message' => 'Hanya SuperAdmin yang dapat memindahkan black / trouble spot.'], 403);
            }

            $dbSatupeta = DB::connection('satupeta');
            $row = $dbSatupeta->table('sm_dacol_black_trouble_spot')->where('id', $id)->first();
            if (!$row) {
                return response()->json(['success' => false, 'message' => 'Data black/trouble spot tidak ditemukan.'], 404);
            }

            $dbSatupeta->table('sm_dacol_black_trouble_spot')->where('id', $id)->update([
                'lat' => round($lat, 7),
                'lng' => round($lng, 7),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Koordinat black/trouble spot berhasil diperbarui.'
            ]);
        }

        if ($table === 'sm_dacol_fasilitas_umum') {
            if (!$isSuperAdmin) {
                return response()->json(['success' => false, 'message' => 'Hanya SuperAdmin yang dapat memindahkan fasilitas umum.'], 403);
            }

            $dbSatupeta = DB::connection('satupeta');
            $row = $dbSatupeta->table('sm_dacol_fasilitas_umum')->where('id', $id)->first();
            if (!$row) {
                return response()->json(['success' => false, 'message' => 'Data fasilitas umum tidak ditemukan.'], 404);
            }

            $dbSatupeta->table('sm_dacol_fasilitas_umum')->where('id', $id)->update([
                'lat' => round($lat, 7),
                'lng' => round($lng, 7),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Koordinat fasilitas umum berhasil diperbarui.'
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
            'layanan_data'    => 'id_layanan_data',
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
        return $this->safeModuleMarkers(
            ['pos_pelayanan', 'posyan'],
            ['id_posyan', 'id'],
            ['namaposyan', 'nama', 'namaposko'],
            ['lokasiposyan', 'lokasi', 'alamat'],
            'posko_',
            ['fotoposyan', 'foto', 'fotogiat']
        );
    }

    /**
     * Pin Terminal (dataterminal).
     */
    private function getTerminalMarkers(): array
    {
        return $this->safeModuleMarkers(
            ['data_terminal', 'dataterminal'],
            ['id_terminal', 'id'],
            ['namaterminal', 'nama'],
            ['jalan', 'lokasi', 'alamat'],
            'terminal_',
            [],
            ['jenisterminal', 'jenis']
        );
    }

    /**
     * Pin Jalan Gangguan (datagangguan).
     */
    private function getJalanGangguanMarkers(): array
    {
        return $this->safeModuleMarkers(
            ['data_gangguan', 'datagangguan'],
            ['id_datagangguan', 'id'],
            ['namajalan', 'nama'],
            ['fungsijalan', 'lokasi'],
            'jalan_',
            [],
            null,
            ['levelgangguan', 'status']
        );
    }

    private function getContigencyMarkers(): array
    {
        return $this->safeModuleMarkers(
            ['contigency'],
            ['id_contigency', 'id'],
            ['jeniscontigency', 'jenis_peristiwa', 'jenis', 'nama'],
            ['ketcb', 'lokasi', 'uraian'],
            'con_',
            ['fotosit', 'foto', 'fotogiat']
        );
    }

    private function getTrafficstuckMarkers(): array
    {
        return $this->safeModuleMarkers(
            ['trafficstuck'],
            ['id_trafficstuck', 'id'],
            ['faktor', 'penyebab', 'nama'],
            ['ketcb', 'lokasi', 'uraian'],
            'ts_',
            ['fotosituasi', 'fotosit', 'foto']
        );
    }

    /**
     * Pin Turjagwali.
     */
    private function getTurjagwaliMarkers(): array
    {
        return $this->safeModuleMarkers(
            ['turjagwali'],
            ['id_turjagwali', 'id'],
            ['katgiat', 'peristiwa', 'lokasi', 'status', 'keterangan'],
            ['lokasi', 'keterangan', 'status'],
            'tj_',
            ['fotogiat', 'fotolokasi', 'foto']
        );
    }

    /**
     * Pin Audit Arus.
     */
    private function getAuditarusMarkers(): array
    {
        return $this->safeModuleMarkers(
            ['auditarus'],
            ['id_auditarus', 'id'],
            ['hasil_audit', 'lokasi', 'nama'],
            ['catatan', 'lokasi', 'keterangan'],
            'aa_',
            ['fotolokasi', 'fotosit', 'foto']
        );
    }

    private function getObjekWisataMarkers(): array
    {
        return $this->safeModuleMarkers(
            ['objek_wisata', 'objekwisata'],
            ['id_tempatwisata', 'id'],
            ['nametempat', 'nama', 'namaobjek'],
            ['lokasi', 'alamat'],
            'wisata_',
            ['fotoobject', 'foto', 'fotogiat']
        );
    }

    private function getTempatIbadahMarkers(): array
    {
        return $this->safeModuleMarkers(
            ['tempatibadah'],
            ['id_tempatibadah', 'id'],
            ['namatempatibadah', 'nama'],
            ['lokasi', 'alamat'],
            'ibadah_',
            ['fotoobject', 'foto']
        );
    }

    private function getDataFaskesMarkers(): array
    {
        return $this->safeModuleMarkers(
            ['data_faskes', 'datafaskes'],
            ['id_faskes', 'id'],
            ['namafaskes', 'nama'],
            ['lokasi', 'alamat'],
            'faskes_',
            ['fotoobject', 'foto']
        );
    }

    /**
     * Pin PNP Angkutan.
     */
    private function getPnpangkutanMarkers(): array
    {
        return $this->safeModuleMarkers(
            ['pnp_angkutan', 'pnpangkutan', 'datapnp'],
            ['id_datapnp', 'id_pnpangkutan', 'id'],
            ['namaterminal', 'jenis_angkutan', 'nama'],
            ['lokasi', 'jenisterminal', 'keterangan'],
            'pa_',
            ['fotosit', 'fotogiat', 'foto']
        );
    }

    /**
     * Ambil marker modul dengan deteksi tabel/kolom dinamis + tangkap error skema.
     *
     * @param  list<string>  $tableCandidates
     * @param  list<string>  $pkCandidates
     * @param  list<string>  $namaCandidates
     * @param  list<string>  $lokasiCandidates
     * @param  list<string>  $fotoCandidates
     * @param  list<string>|null  $jenisCandidates
     * @param  list<string>|null  $statusCandidates
     */
    private function safeModuleMarkers(
        array $tableCandidates,
        array $pkCandidates,
        array $namaCandidates,
        array $lokasiCandidates,
        string $prefix,
        array $fotoCandidates = [],
        ?array $jenisCandidates = null,
        ?array $statusCandidates = null
    ): array {
        try {
            $schema = DatabaseConnections::operationalSchema();
            $table = $this->firstExistingTable($tableCandidates);
            if ($table === null) {
                return [];
            }

            $coordCol = $this->firstExistingColumn($table, ['koordinat', 'kordinat', 'coordinate']);
            if ($coordCol === null) {
                return [];
            }

            $pk = $this->firstExistingColumn($table, $pkCandidates);
            $namaCol = $this->firstExistingColumn($table, $namaCandidates);
            $lokasiCol = $this->firstExistingColumn($table, $lokasiCandidates);
            if ($pk === null || $namaCol === null) {
                return [];
            }

            $query = $this->scopedQuery($table, $pk)
                ->select([
                    $table.'.'.$pk.' as id',
                    $table.'.'.$namaCol.' as nama',
                    $table.'.'.$coordCol.' as koordinat',
                ])
                ->whereNotNull($table.'.'.$coordCol)
                ->where($table.'.'.$coordCol, '!=', '');

            if ($lokasiCol !== null) {
                $query->addSelect($table.'.'.$lokasiCol.' as lokasi');
            } else {
                $query->selectRaw("'' as lokasi");
            }

            $fotoCol = $fotoCandidates !== [] ? $this->firstExistingColumn($table, $fotoCandidates) : null;
            if ($fotoCol !== null) {
                $query->addSelect($table.'.'.$fotoCol.' as foto_giat');
            }

            $jenisKey = null;
            if ($jenisCandidates) {
                $jenisCol = $this->firstExistingColumn($table, $jenisCandidates);
                if ($jenisCol !== null) {
                    $query->addSelect($table.'.'.$jenisCol.' as jenis');
                    $jenisKey = 'jenis';
                }
            }

            $statusKey = null;
            if ($statusCandidates) {
                $statusCol = $this->firstExistingColumn($table, $statusCandidates);
                if ($statusCol !== null) {
                    $query->addSelect($table.'.'.$statusCol.' as status');
                    $statusKey = 'status';
                }
            }

            $piketCol = $schema->hasColumn($table, 'piket') ? 'piket' : ($schema->hasColumn($table, 'id_usm') ? 'id_usm' : null);
            if ($piketCol !== null) {
                // Alias beda agar tidak bentrok dengan subquery territory scope ke `usm`
                $query->leftJoin('usm as map_petugas', $table.'.'.$piketCol, '=', 'map_petugas.id_usm')
                    ->addSelect('map_petugas.nama as petugas', 'map_petugas.fotoprof as fotoprof', $table.'.'.$piketCol.' as piket');
            }

            return $this->mapToMarkers($query->get(), $prefix, $jenisKey, $statusKey);
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning('MapController markers failed: '.$prefix.' '.$e->getMessage());

            return [];
        }
    }

    /**
     * @param  list<string>  $candidates
     */
    private function firstExistingTable(array $candidates): ?string
    {
        $schema = DatabaseConnections::operationalSchema();
        foreach ($candidates as $table) {
            if ($schema->hasTable($table)) {
                return $table;
            }
        }

        return null;
    }

    /**
     * @param  list<string>  $candidates
     */
    private function firstExistingColumn(string $table, array $candidates): ?string
    {
        $schema = DatabaseConnections::operationalSchema();
        foreach ($candidates as $column) {
            if ($schema->hasColumn($table, $column)) {
                return $column;
            }
        }

        return null;
    }

    private function getLayananDataMarkers(): array
    {
        return $this->safeModuleMarkers(
            ['layanan_data'],
            ['id_layanan_data', 'id'],
            ['kategori_data', 'pemohon', 'tujuan'],
            ['tujuan', 'keterangan'],
            'layanan_data_',
            ['foto'],
            ['kategori_data']
        );
    }

    /**
     * Pin data dari tabel Dacol (DB satupeta) secara generik.
     */
    private function getDacolMarkers(string $tableName, string $prefix): array
    {
        try {
            $db = DB::connection('satupeta');
            if (!\Illuminate\Support\Facades\Schema::connection('satupeta')->hasTable($tableName)) {
                return [];
            }

            // Gunakan orderBy rowid jika ada, atau fallback tanpa orderBy jika error
            $query = $db->table($tableName);
            \App\Support\TerritoryIndexFilters::applyColumnFilters($query, request(), $tableName, 'satupeta');
            
            $query->limit(300);
            if (\Illuminate\Support\Facades\Schema::connection('satupeta')->hasColumn($tableName, 'rowid')) {
                $query->orderBy('rowid', 'desc');
            }
            $rows = $query->get();
            
            $out = [];
            foreach ($rows as $r) {
                // Deteksi kolom koordinat (lat/lng, latitude/longitude, koordinat)
                $lat = $r->lat ?? $r->latitude ?? null;
                $lng = $r->lng ?? $r->longitude ?? null;
                if (!$lat || !$lng) {
                    if (isset($r->koordinat)) {
                        $parts = explode(',', $r->koordinat);
                        if (count($parts) === 2) {
                            $lat = trim($parts[0]);
                            $lng = trim($parts[1]);
                        }
                    }
                }

                if (!is_numeric($lat) || !is_numeric($lng)) continue;
                $lat = (float) $lat;
                $lng = (float) $lng;
                if (!$this->isValidIndonesiaCoordinate($lat, $lng)) continue;

                // Deteksi nama, jenis, lokasi
                $name = $r->nama_jalan ?? $r->nama_objek ?? $r->nama_fasilitas ?? $r->nama ?? ucwords(str_replace('_', ' ', str_replace('sm_dacol_', '', $tableName)));
                $jenis = $r->kategori ?? $r->jenis ?? $r->jenis_objek ?? '';
                $lokasi = $r->keterangan ?? $r->alamat ?? $r->lokasi ?? '';

                $out[] = [
                    'id' => $prefix . '_' . ($r->rowid ?? $r->id ?? uniqid()),
                    'name' => $name,
                    'coordinates' => [$lat, $lng],
                    'lokasi' => $lokasi,
                    'jenis' => $jenis,
                    'status' => $r->status ?? '',
                ];
            }
            return $out;
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error("MapController Dacol Error [$tableName]: " . $e->getMessage());
            return [];
        }
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
            if (property_exists($r, 'piket') && !empty($r->piket)) $marker['piket'] = $r->piket;
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
        $lat = (float) $parts[0];
        $lng = (float) $parts[1];
        if (!$this->isValidIndonesiaCoordinate($lat, $lng)) return null;

        return [$lat, $lng];
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

    /**
     * AJAX: Ambil lokasi realtime petugas role oprator (untuk Leaflet).
     */
    public function getPetugasMarkers(): JsonResponse
    {
        if (!DatabaseConnections::operationalSchema()->hasTable('petugas_tracking')) {
            return response()->json([]);
        }

        $operatorRoleIds = MenuHelper::personilBoOperatorUserRoleIds();

        $query = DB::table('usm as u')
            ->join('petugas_tracking as t', 'u.id_usm', '=', 't.user_id')
            ->select(
                'u.id_usm as id',
                'u.nama',
                'u.username',
                'u.fotoprof',
                'u.notelp',
                'u.daerah',
                'u.wilayah',
                't.lat',
                't.lng',
                't.status',
                't.last_updated'
            )
            ->whereIn('u.user_role_id', $operatorRoleIds)
            ->whereIn('t.status', ['ONLINE', 'BERTUGAS'])
            ->whereNotNull('t.lat')
            ->whereNotNull('t.lng')
            ->where('t.lat', '!=', 0)
            ->where('t.lng', '!=', 0);

        // Territory scope: LIKE (selaras CAD) agar nama daerah/wilayah tidak harus exact match
        $user = Auth::user();
        if ($user && !HierarchicalHelper::viewerBypassesTerritoryScope()) {
            $userDaerah = trim((string) ($user->daerah ?? ''));
            $userWilayah = trim((string) ($user->wilayah ?? ''));

            if ($userDaerah !== '') {
                $query->where('u.daerah', 'LIKE', '%'.$userDaerah.'%');
                if ($userWilayah !== '') {
                    $query->where('u.wilayah', 'LIKE', '%'.$userWilayah.'%');
                }
            }
        }

        $officers = $query->get()->map(function ($item) {
            $url_image = null;
            if ($item->fotoprof) {
                if (filter_var($item->fotoprof, FILTER_VALIDATE_URL)) {
                    $url_image = $item->fotoprof;
                } else {
                    $url_image = asset('storage/' . $item->fotoprof);
                }
            } else {
                $url_image = null;
            }

            $taskLat = null;
            $taskLng = null;
            $taskTitle = null;
            
            if ($item->status === 'BERTUGAS') {
                $assignment = DB::table('tbl_cad_assignment as a')
                    ->join('tbl_cad_task as t', 'a.task_id', '=', 't.id')
                    ->where('a.user_id', $item->id)
                    ->whereIn('a.status', ['ASSIGNED', 'DITERIMA', 'MENUJU', 'TIBA'])
                    ->select('t.latitude', 't.longitude', 't.title')
                    ->orderBy('a.id', 'desc')
                    ->first();
                if ($assignment) {
                    $taskLat = (float) $assignment->latitude;
                    $taskLng = (float) $assignment->longitude;
                    $taskTitle = $assignment->title;
                }
            }

            return [
                'id' => 'petugas_' . $item->id,
                'name' => $item->nama,
                'username' => $item->username,
                'status' => $item->status,
                'coordinates' => [(float) $item->lat, (float) $item->lng],
                'fotoprof' => $url_image,
                'notelp' => $item->notelp,
                'daerah' => $item->daerah,
                'wilayah' => $item->wilayah,
                'last_updated' => $item->last_updated,
                'task_lat' => $taskLat,
                'task_lng' => $taskLng,
                'task_title' => $taskTitle
            ];
        })->filter(function ($item) {
            return $this->isValidIndonesiaCoordinate($item['coordinates'][0] ?? null, $item['coordinates'][1] ?? null);
        })->values();

        return response()->json($officers);
    }

    /**
     * Endpoint diagnostik untuk mencari tahu mengapa marker operator tidak muncul.
     */
    public function debugOperatorMap(Request $request): JsonResponse
    {
        try {
            $hasTable = DatabaseConnections::operationalSchema()->hasTable('petugas_tracking');
            $operatorRoleIds = MenuHelper::personilBoOperatorUserRoleIds();
            
            // Cek apakah tabel usm ada
            $hasUsmTable = DatabaseConnections::operationalSchema()->hasTable('usm');

            // Hitung operator di tabel usm
            $usmOperatorsCount = 0;
            if ($hasUsmTable) {
                $usmOperatorsCount = DB::table('usm')
                    ->whereIn('user_role_id', $operatorRoleIds)
                    ->count();
            }

            // Hitung baris di petugas_tracking
            $trackingRowsCount = 0;
            if ($hasTable) {
                $trackingRowsCount = DB::table('petugas_tracking')->count();
            }

            // Wilayah user saat ini
            $currentUser = Auth::user();
            $userDaerah = $currentUser ? trim((string) ($currentUser->daerah ?? '')) : '';
            $userWilayah = $currentUser ? trim((string) ($currentUser->wilayah ?? '')) : '';
            $bypassesScope = $currentUser ? HierarchicalHelper::viewerBypassesTerritoryScope() : true;

            // Gabungkan usm dan petugas_tracking untuk dianalisis satu per satu
            $allOperatorsWithTracking = [];
            if ($hasUsmTable) {
                $opQuery = DB::table('usm as u');
                if ($hasTable) {
                    $opQuery->leftJoin('petugas_tracking as t', 'u.id_usm', '=', 't.user_id');
                }
                
                $opQuery->select(
                    'u.id_usm as id',
                    'u.nama',
                    'u.username',
                    'u.user_role_id',
                    'u.daerah',
                    'u.wilayah'
                );

                if ($hasTable) {
                    $opQuery->addSelect(
                        't.lat',
                        't.lng',
                        't.status as tracking_status',
                        't.last_updated'
                    );
                }

                $opQuery->whereIn('u.user_role_id', $operatorRoleIds);
                
                $allRows = $opQuery->get();

                foreach ($allRows as $item) {
                    $hasTracking = isset($item->tracking_status);
                    $statusOk = $hasTracking && in_array(strtoupper($item->tracking_status), ['ONLINE', 'BERTUGAS']);
                    $coordsOk = $hasTracking && isset($item->lat) && isset($item->lng) && (float)$item->lat !== 0.0 && (float)$item->lng !== 0.0;
                    
                    // Filter wilayah
                    $territoryDaerahMatch = true;
                    $territoryWilayahMatch = true;
                    if ($currentUser && !$bypassesScope) {
                        if ($userDaerah !== '') {
                            $territoryDaerahMatch = str_contains(strtolower($item->daerah ?? ''), strtolower($userDaerah));
                            if ($userWilayah !== '') {
                                $territoryWilayahMatch = str_contains(strtolower($item->wilayah ?? ''), strtolower($userWilayah));
                            }
                        }
                    }

                    $reasons = [];
                    if (!$hasTracking) {
                        $reasons[] = 'Tidak ada baris tracking di tabel petugas_tracking';
                    } else {
                        if (!$statusOk) {
                            $reasons[] = 'Status tracking adalah "' . ($item->tracking_status ?: 'kosong') . '" (harus ONLINE/BERTUGAS)';
                        }
                        if (!$coordsOk) {
                            $reasons[] = 'Koordinat tidak valid atau bernilai nol: ' . ($item->lat ?? 'null') . ',' . ($item->lng ?? 'null');
                        }
                    }

                    if (!$territoryDaerahMatch) {
                        $reasons[] = 'Terfilter oleh scope daerah (User: "' . $userDaerah . '" vs Operator: "' . ($item->daerah ?? '') . '")';
                    }
                    if (!$territoryWilayahMatch) {
                        $reasons[] = 'Terfilter oleh scope wilayah (User: "' . $userWilayah . '" vs Operator: "' . ($item->wilayah ?? '') . '")';
                    }

                    $allOperatorsWithTracking[] = [
                        'id' => $item->id,
                        'nama' => $item->nama,
                        'username' => $item->username,
                        'user_role_id' => $item->user_role_id,
                        'daerah' => $item->daerah,
                        'wilayah' => $item->wilayah,
                        'lat' => $hasTracking ? (float)$item->lat : null,
                        'lng' => $hasTracking ? (float)$item->lng : null,
                        'tracking_status' => $hasTracking ? $item->tracking_status : null,
                        'last_updated' => $hasTracking ? $item->last_updated : null,
                        'is_visible' => $statusOk && $coordsOk && $territoryDaerahMatch && $territoryWilayahMatch,
                        'reasons' => $reasons,
                    ];
                }
            }

            // Diagnostik kontijensi yang disinkronkan dari layanan masyarakat
            $hasContigencyTable = DatabaseConnections::operationalSchema()->hasTable('contigency');
            $contingencyDiagnostics = [];
            
            if ($hasContigencyTable) {
                $hasSourceModule = DatabaseConnections::operationalSchema()->hasColumn('contigency', 'source_module');
                $hasStatusKonfirmasi = DatabaseConnections::operationalSchema()->hasColumn('contigency', 'status_konfirmasi_opsdal');
                
                $conQuery = DB::table('contigency as c')
                    ->leftJoin('usm as u', 'c.piket', '=', 'u.id_usm');
                
                if ($hasSourceModule) {
                    $conQuery->where('c.source_module', 'layanan_masyarakat');
                } else {
                    $conQuery->where('c.ketcb', 'LIKE', '%Layanan Masyarakat%');
                }
                
                $conRows = $conQuery->select([
                    'c.id_contigency as id',
                    'c.jeniscontigency as jenis',
                    'c.lokasi',
                    'c.koordinat',
                    'c.ketcb',
                    'u.daerah as operator_daerah',
                    'u.wilayah as operator_wilayah',
                ])
                ->when($hasStatusKonfirmasi, function($q) {
                    $q->addSelect('c.status_konfirmasi_opsdal');
                })
                ->get();
                
                $totalCon = count($conRows);
                $missingCoordsCount = 0;
                $pendingConfirmCount = 0;
                $filteredByScopeCount = 0;
                $visibleConCount = 0;
                
                $sampleCon = [];
                foreach ($conRows as $row) {
                    $coords = trim((string)($row->koordinat ?? ''));
                    $hasCoords = $coords !== '';
                    
                    // Parse koordinat
                    $latLng = null;
                    if ($hasCoords) {
                        $parts = array_map('trim', explode(',', $coords));
                        if (count($parts) === 2 && is_numeric($parts[0]) && is_numeric($parts[1])) {
                            $latLng = [(float)$parts[0], (float)$parts[1]];
                        }
                    }
                    
                    // Filter wilayah
                    $territoryDaerahMatch = true;
                    $territoryWilayahMatch = true;
                    if ($currentUser && !$bypassesScope) {
                        if ($userDaerah !== '') {
                            $territoryDaerahMatch = str_contains(strtolower($row->operator_daerah ?? ''), strtolower($userDaerah));
                            if ($userWilayah !== '') {
                                $territoryWilayahMatch = str_contains(strtolower($row->operator_wilayah ?? ''), strtolower($userWilayah));
                            }
                        }
                    }
                    
                    $reasons = [];
                    if (!$hasCoords) {
                        $reasons[] = 'Koordinat kosong/null';
                        $missingCoordsCount++;
                    } elseif (!$latLng) {
                        $reasons[] = 'Format koordinat tidak valid: "' . $coords . '"';
                        $missingCoordsCount++;
                    }
                    
                    if ($hasStatusKonfirmasi && isset($row->status_konfirmasi_opsdal) && strtolower($row->status_konfirmasi_opsdal) === 'menunggu') {
                        $reasons[] = 'Status konfirmasi masih "menunggu" (belum disetujui)';
                        $pendingConfirmCount++;
                    }
                    
                    if (!$territoryDaerahMatch) {
                        $reasons[] = 'Terfilter oleh scope daerah (User: "' . $userDaerah . '" vs Operator Pembuat: "' . ($row->operator_daerah ?? '') . '")';
                        $filteredByScopeCount++;
                    }
                    if (!$territoryWilayahMatch) {
                        $reasons[] = 'Terfilter oleh scope wilayah (User: "' . $userWilayah . '" vs Operator Pembuat: "' . ($row->operator_wilayah ?? '') . '")';
                        $filteredByScopeCount++;
                    }
                    
                    $isVisible = $hasCoords && $latLng && (!$hasStatusKonfirmasi || (isset($row->status_konfirmasi_opsdal) && strtolower($row->status_konfirmasi_opsdal) !== 'menunggu')) && $territoryDaerahMatch && $territoryWilayahMatch;
                    
                    if ($isVisible) {
                        $visibleConCount++;
                    }
                    
                    $sampleCon[] = [
                        'id' => $row->id,
                        'jenis' => $row->jenis,
                        'lokasi' => $row->lokasi,
                        'koordinat' => $coords,
                        'status_konfirmasi' => $row->status_konfirmasi_opsdal ?? null,
                        'is_visible' => $isVisible,
                        'reasons' => $reasons,
                    ];
                }
                
                $contingencyDiagnostics = [
                    'total_synced_records' => $totalCon,
                    'visible_on_map' => $visibleConCount,
                    'missing_coordinates' => $missingCoordsCount,
                    'pending_confirmation' => $pendingConfirmCount,
                    'filtered_by_territory_scope' => $filteredByScopeCount,
                    'records' => $sampleCon,
                ];
            }

            return response()->json([
                'success' => true,
                'diagnostics' => [
                    'petugas_tracking_table_exists' => $hasTable,
                    'usm_table_exists' => $hasUsmTable,
                    'operator_role_ids' => $operatorRoleIds,
                    'total_usm_operators' => $usmOperatorsCount,
                    'total_tracking_rows' => $trackingRowsCount,
                    'current_user' => [
                        'id' => $currentUser ? $currentUser->id_usm : null,
                        'username' => $currentUser ? $currentUser->username : null,
                        'daerah' => $userDaerah,
                        'wilayah' => $userWilayah,
                        'bypasses_scope' => $bypassesScope,
                    ],
                    'operators_diagnostics' => $allOperatorsWithTracking,
                    'contingency_diagnostics' => $contingencyDiagnostics,
                ]
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ], 500);
        }
    }

    /**
     * Ambil marker Opsdal CAD
     */
    private function getOpsdalMarkers(): array
    {
        if (!DatabaseConnections::operationalSchema()->hasTable('tbl_cad_task')) {
            return [];
        }

        $query = DB::table('tbl_cad_task as t')
            ->leftJoin('usm as creator', 't.created_by', '=', 'creator.id_usm')
            ->select('t.id', 't.title as nama', 't.address as lokasi', 't.latitude', 't.longitude', 't.status', 't.priority', 't.created_by as piket')
            ->whereNotNull('t.latitude')
            ->whereNotNull('t.longitude')
            ->where('t.latitude', '!=', 0)
            ->where('t.longitude', '!=', 0)
            ->where('t.status', '!=', 'CLOSED') // exclude closed tasks maybe? Or not.
            ;

        // Territory scope
        $user = Auth::user();
        if ($user && !HierarchicalHelper::viewerBypassesTerritoryScope()) {
            $userDaerah = trim((string) ($user->daerah ?? ''));
            $userWilayah = trim((string) ($user->wilayah ?? ''));

            if ($userDaerah !== '') {
                $query->where(function ($q) use ($userDaerah, $userWilayah) {
                    $q->where('creator.daerah', 'LIKE', '%' . $userDaerah . '%');
                    if ($userWilayah !== '') {
                        $q->where('creator.wilayah', 'LIKE', '%' . $userWilayah . '%');
                    }
                    
                    $q->orWhereExists(function ($sub) use ($userDaerah, $userWilayah) {
                        $sub->select(DB::raw(1))
                            ->from('tbl_cad_assignment as a')
                            ->join('usm as officer', 'a.user_id', '=', 'officer.id_usm')
                            ->whereColumn('a.task_id', 't.id')
                            ->where('officer.daerah', 'LIKE', '%' . $userDaerah . '%');
                        if ($userWilayah !== '') {
                            $sub->where('officer.wilayah', 'LIKE', '%' . $userWilayah . '%');
                        }
                    });
                });
            }
        }

        $rows = $query->get();
        $out = [];
        foreach ($rows as $r) {
            $lat = (float) $r->latitude;
            $lng = (float) $r->longitude;
            if (!$this->isValidIndonesiaCoordinate($lat, $lng)) continue;

            $out[] = [
                'id' => 'opsdal_' . $r->id,
                'name' => $r->nama ?? 'Opsdal Task',
                'coordinates' => [$lat, $lng],
                'lokasi' => $r->lokasi ?? '',
                'status' => $r->status,
                'jenis' => 'Prioritas: ' . $r->priority,
                'piket' => $r->piket
            ];
        }
        return $out;
    }
}
