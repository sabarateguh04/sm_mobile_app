<?php

namespace Database\Seeders;

use App\Support\TerritoryMaster;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

/**
 * Mengisi hierarki master: `master_provinsi` → `master_kabupaten_kota`, dan `master_pusat` (parent_id).
 * Menyinkronkan ke legacy `daerah` / `wilayah` / `org` / `suborg` agar validasi & TerritoryMaster tetap jalan.
 *
 * Data wilayah: {@see database/data/indonesia_territories.json}
 */
class IndonesiaMasterDataSeeder extends Seeder
{
    public function run(): void
    {
        $path = database_path('data/indonesia_territories.json');
        $payload = null;
        if (is_readable($path)) {
            $payload = json_decode((string) file_get_contents($path), true);
            if (! is_array($payload)) {
                $this->command?->error('Gagal parse JSON wilayah.');
                $payload = null;
            }
        } else {
            $this->command?->warn('Berkas wilayah tidak ada: '.$path.' — jalankan: php database/scripts/fetch-indonesia-territories.php');
        }

        $numericFk = TerritoryMaster::wilayahIdColumnIsNumericDaerahFk();
        $now = now();

        if (is_array($payload)) {
            if (Schema::hasTable('master_provinsi') && Schema::hasTable('master_kabupaten_kota')) {
                $this->seedMasterProvinsiKabupatenKotaFromPayload($payload, $now);
                $this->syncLegacyDaerahWilayahFromMasterTables($numericFk, $now);
            } elseif (Schema::hasTable('daerah') && Schema::hasTable('wilayah')) {
                $this->seedLegacyWilayahFromPayloadOnly($payload, $numericFk, $now);
            }
        }

        if (Schema::hasTable('master_pusat')) {
            $this->ensureMasterPusatTreeFromDefinitions($now);
        }

        $this->syncLegacyOrgSuborgFromMasterPusat($now);

        if (Schema::hasTable('master_territori')
            && Schema::hasTable('master_provinsi')
            && DB::table('master_provinsi')->exists()) {
            $this->syncMasterTerritoriFromProvKab($now);
        }
    }

    /**
     * @param  array<int, mixed>  $payload
     */
    protected function seedMasterProvinsiKabupatenKotaFromPayload(array $payload, \DateTimeInterface $now): void
    {
        DB::transaction(function () use ($payload, $now): void {
            foreach ($payload as $block) {
                if (! is_array($block) || ! isset($block['province'], $block['regencies'])) {
                    continue;
                }
                /** @var array{id:string,name:string} $prov */
                $prov = $block['province'];
                /** @var array<int, array{id:string,name:string}> $regs */
                $regs = is_array($block['regencies']) ? $block['regencies'] : [];

                $namaProvinsi = self::prettyProvinceName((string) $prov['name']);
                $kodeProv = trim((string) $prov['id']);

                $rowP = ['nama' => $namaProvinsi, 'updated_at' => $now];
                if (Schema::hasColumn('master_provinsi', 'created_at')) {
                    $rowP['created_at'] = $now;
                }

                $existingPid = DB::table('master_provinsi')->where('kode_provinsi', $kodeProv)->value('id');
                if ($existingPid !== null) {
                    DB::table('master_provinsi')->where('id', $existingPid)->update($rowP);
                    $provinsiId = (int) $existingPid;
                } else {
                    $provinsiId = (int) DB::table('master_provinsi')->insertGetId([
                        'kode_provinsi' => $kodeProv,
                        'nama' => $namaProvinsi,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ]);
                }

                foreach ($regs as $reg) {
                    $kodeKab = trim((string) ($reg['id'] ?? ''));
                    if ($kodeKab === '') {
                        continue;
                    }
                    $namaKabkot = self::prettyKabupatenKotaName((string) ($reg['name'] ?? ''));
                    $jenis = self::jenisWilayahFromName((string) ($reg['name'] ?? ''));

                    $rowK = [
                        'master_provinsi_id' => $provinsiId,
                        'nama' => $namaKabkot,
                        'jenis' => $jenis,
                        'updated_at' => $now,
                    ];

                    $kid = DB::table('master_kabupaten_kota')->where('kode_kabupaten_kota', $kodeKab)->value('id');
                    if ($kid !== null) {
                        DB::table('master_kabupaten_kota')->where('id', $kid)->update($rowK);

                        continue;
                    }

                    $rowK['kode_kabupaten_kota'] = $kodeKab;
                    $rowK['created_at'] = $now;
                    DB::table('master_kabupaten_kota')->insert($rowK);
                }
            }
        });

        $this->command?->info('Master provinsi & kab/kota (tabel hierarki): data disimpan.');
    }

    protected function syncLegacyDaerahWilayahFromMasterTables(bool $numericFk, \DateTimeInterface $now): void
    {
        if (! Schema::hasTable('daerah') || ! Schema::hasTable('wilayah')) {
            $this->command?->warn('Tabel daerah/wilayah tidak ada — sinkron legacy dari master provinsi/kab-kota dilewati.');

            return;
        }

        DB::transaction(function () use ($numericFk, $now): void {
            $mapKodeProvToIdDaerah = [];

            $provinsiRows = DB::table('master_provinsi')->orderBy('kode_provinsi')->get();

            foreach ($provinsiRows as $mp) {
                $idDaerah = $this->upsertLegacyDaerahRow(
                    $mp->nama,
                    $mp->kode_provinsi,
                    (int) $mp->id,
                    $now
                );
                $mapKodeProvToIdDaerah[(string) $mp->kode_provinsi] = $idDaerah;
            }

            $kabRows = DB::table('master_kabupaten_kota as k')
                ->join('master_provinsi as p', 'p.id', '=', 'k.master_provinsi_id')
                ->select('k.id as mk_id', 'k.kode_kabupaten_kota', 'k.nama as nama_kab', 'k.jenis', 'p.kode_provinsi')
                ->orderBy('p.kode_provinsi')
                ->orderBy('k.kode_kabupaten_kota')
                ->get();

            foreach ($kabRows as $kab) {
                $idDaerah = $mapKodeProvToIdDaerah[(string) $kab->kode_provinsi] ?? null;
                if ($idDaerah === null) {
                    continue;
                }

                $namaDaerahRow = DB::table('daerah')->where('id_daerah', $idDaerah)->value('namadaerah');
                $fkVal = $numericFk ? (int) $idDaerah : (string) ($namaDaerahRow ?? '');

                $already = false;
                if (Schema::hasColumn('wilayah', 'kode_kabupaten_kota')) {
                    $already = DB::table('wilayah')->where('kode_kabupaten_kota', $kab->kode_kabupaten_kota)->exists();
                }
                if (! $already) {
                    $already = DB::table('wilayah')
                        ->where('namawilayah', $kab->nama_kab)
                        ->where('id_daerah', $fkVal)
                        ->exists();
                }
                if ($already) {
                    $this->patchWilayahHierarchyIds($kab->kode_kabupaten_kota, $kab->nama_kab, $fkVal, (int) $kab->mk_id);

                    continue;
                }

                $wRow = [
                    'id_daerah' => $fkVal,
                    'namawilayah' => $kab->nama_kab,
                ];
                if (Schema::hasColumn('wilayah', 'kode_kabupaten_kota')) {
                    $wRow['kode_kabupaten_kota'] = $kab->kode_kabupaten_kota;
                }
                if (Schema::hasColumn('wilayah', 'jenis_wilayah')) {
                    $wRow['jenis_wilayah'] = $kab->jenis;
                }
                if (Schema::hasColumn('wilayah', 'master_kabupaten_kota_id')) {
                    $wRow['master_kabupaten_kota_id'] = (int) $kab->mk_id;
                }
                if (Schema::hasColumn('wilayah', 'updated_at')) {
                    $wRow['updated_at'] = $now;
                }
                if (Schema::hasColumn('wilayah', 'created_at')) {
                    $wRow['created_at'] = $now;
                }

                DB::table('wilayah')->insert($wRow);
            }
        });

        $this->command?->info('Legacy daerah/wilayah diselaraskan dari tabel master hierarki.');
    }

    /**
     * Bangun hierarki satu tabel (contoh: id 1 Indonesia → 32 Jawa Barat → 3216 Bekasi) untuk select admin/oprator.
     * Sumber: `master_provinsi` & `master_kabupaten_kota`.
     */
    protected function syncMasterTerritoriFromProvKab(\DateTimeInterface $now): void
    {
        if (! Schema::hasTable('master_territori') || ! Schema::hasTable('master_provinsi')) {
            return;
        }

        if (! DB::table('master_provinsi')->exists()) {
            return;
        }

        Schema::disableForeignKeyConstraints();
        try {
            DB::table('master_territori')->truncate();
        } finally {
            Schema::enableForeignKeyConstraints();
        }

        $rootId = (int) DB::table('master_territori')->insertGetId([
            'parent_id' => null,
            'level' => 'nasional',
            'kode' => 'ID',
            'nama' => 'Indonesia',
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        /** @var array<int, int> $mapProvinsiMasterIdToTerritoriId */
        $mapProvinsiMasterIdToTerritoriId = [];

        $provRows = DB::table('master_provinsi')->orderBy('kode_provinsi')->get();
        foreach ($provRows as $mp) {
            $tid = (int) DB::table('master_territori')->insertGetId([
                'parent_id' => $rootId,
                'level' => 'provinsi',
                'kode' => (string) $mp->kode_provinsi,
                'nama' => (string) $mp->nama,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
            $mapProvinsiMasterIdToTerritoriId[(int) $mp->id] = $tid;
        }

        if (! Schema::hasTable('master_kabupaten_kota')) {
            $this->command?->warn('master_territori: master_kabupaten_kota tidak ada — hanya baris nasional & provinsi.');

            $this->command?->info('master_territori disinkronkan (nasional → provinsi → kab/kota, kode wilayah).');

            return;
        }

            $kabRows = DB::table('master_kabupaten_kota')->orderBy('kode_kabupaten_kota')->get();
            foreach ($kabRows as $kab) {
                $pid = (int) $kab->master_provinsi_id;
                $parentTid = $mapProvinsiMasterIdToTerritoriId[$pid] ?? null;
                if ($parentTid === null) {
                    continue;
                }

                $levelKabkot = self::jenisWilayahFromName((string) $kab->nama);

                DB::table('master_territori')->insert([
                    'parent_id' => $parentTid,
                    'level' => $levelKabkot,
                    'kode' => (string) $kab->kode_kabupaten_kota,
                    'nama' => (string) $kab->nama,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }

        $this->command?->info('master_territori disinkronkan (nasional → provinsi → kab/kota, kode wilayah).');
    }

    protected function patchWilayahHierarchyIds(string $kodeKab, string $namaKab, mixed $fkVal, int $masterKabId): void
    {
        $q = DB::table('wilayah');
        if (Schema::hasColumn('wilayah', 'kode_kabupaten_kota')) {
            $q->where('kode_kabupaten_kota', $kodeKab);
        } else {
            $q->where('namawilayah', $namaKab)->where('id_daerah', $fkVal);
        }

        $patch = [];
        if (Schema::hasColumn('wilayah', 'master_kabupaten_kota_id')) {
            $patch['master_kabupaten_kota_id'] = $masterKabId;
        }
        if (Schema::hasColumn('wilayah', 'jenis_wilayah')) {
            $patch['jenis_wilayah'] = DB::table('master_kabupaten_kota')->where('id', $masterKabId)->value('jenis');
        }
        if ($patch !== []) {
            if (Schema::hasColumn('wilayah', 'updated_at')) {
                $patch['updated_at'] = now();
            }
            $q->update($patch);
        }
    }

    protected function upsertLegacyDaerahRow(string $namaProvinsi, string $kodeProv, int $masterProvinsiId, \DateTimeInterface $now): int
    {
        $idDaerah = null;
        if (Schema::hasColumn('daerah', 'kode_provinsi')) {
            $idDaerah = DB::table('daerah')->where('kode_provinsi', $kodeProv)->value('id_daerah');
        }
        if ($idDaerah === null) {
            $idDaerah = DB::table('daerah')
                ->whereRaw('LOWER(TRIM(namadaerah)) = ?', [mb_strtolower(trim($namaProvinsi), 'UTF-8')])
                ->value('id_daerah');
        }

        $daerahRow = ['namadaerah' => $namaProvinsi];
        if (Schema::hasColumn('daerah', 'kode_provinsi')) {
            $daerahRow['kode_provinsi'] = $kodeProv;
        }
        if (Schema::hasColumn('daerah', 'master_provinsi_id')) {
            $daerahRow['master_provinsi_id'] = $masterProvinsiId;
        }
        if (Schema::hasColumn('daerah', 'updated_at')) {
            $daerahRow['updated_at'] = $now;
        }

        if ($idDaerah !== null) {
            DB::table('daerah')->where('id_daerah', $idDaerah)->update($daerahRow);

            return (int) $idDaerah;
        }

        if (Schema::hasColumn('daerah', 'created_at')) {
            $daerahRow['created_at'] = $now;
        }

        return (int) DB::table('daerah')->insertGetId($daerahRow);
    }

    /**
     * Jalankan bila tabel master wilayah belum ada (basis data lama).
     *
     * @param  array<int, mixed>  $payload
     */
    protected function seedLegacyWilayahFromPayloadOnly(array $payload, bool $numericFk, \DateTimeInterface $now): void
    {
        DB::transaction(function () use ($payload, $numericFk, $now): void {
            foreach ($payload as $block) {
                if (! is_array($block) || ! isset($block['province'], $block['regencies'])) {
                    continue;
                }
                $prov = $block['province'];
                $regs = is_array($block['regencies']) ? $block['regencies'] : [];

                $namaProvinsi = self::prettyProvinceName((string) $prov['name']);
                $kodeProv = trim((string) $prov['id']);

                $idDaerah = DB::table('daerah')->where('kode_provinsi', $kodeProv)->value('id_daerah');
                if ($idDaerah === null) {
                    $idDaerah = DB::table('daerah')
                        ->whereRaw('LOWER(TRIM(namadaerah)) = ?', [mb_strtolower(trim($namaProvinsi), 'UTF-8')])
                        ->value('id_daerah');
                }

                $daerahRow = ['namadaerah' => $namaProvinsi];
                if (Schema::hasColumn('daerah', 'kode_provinsi')) {
                    $daerahRow['kode_provinsi'] = $kodeProv;
                }
                if (Schema::hasColumn('daerah', 'updated_at')) {
                    $daerahRow['updated_at'] = $now;
                }

                if ($idDaerah !== null) {
                    DB::table('daerah')->where('id_daerah', $idDaerah)->update($daerahRow);
                } else {
                    if (Schema::hasColumn('daerah', 'created_at')) {
                        $daerahRow['created_at'] = $now;
                    }
                    $idDaerah = DB::table('daerah')->insertGetId($daerahRow);
                }

                foreach ($regs as $reg) {
                    $kodeKab = trim((string) ($reg['id'] ?? ''));
                    if ($kodeKab === '') {
                        continue;
                    }

                    $namaKabkot = self::prettyKabupatenKotaName((string) ($reg['name'] ?? ''));
                    $jenis = self::jenisWilayahFromName((string) ($reg['name'] ?? ''));

                    $fkVal = $numericFk ? (int) $idDaerah : $namaProvinsi;

                    $already = false;
                    if (Schema::hasColumn('wilayah', 'kode_kabupaten_kota')) {
                        $already = DB::table('wilayah')->where('kode_kabupaten_kota', $kodeKab)->exists();
                    }
                    if (! $already) {
                        $already = DB::table('wilayah')
                            ->where('namawilayah', $namaKabkot)
                            ->where('id_daerah', $fkVal)
                            ->exists();
                    }
                    if ($already) {
                        continue;
                    }

                    $wRow = [
                        'id_daerah' => $fkVal,
                        'namawilayah' => $namaKabkot,
                    ];
                    if (Schema::hasColumn('wilayah', 'kode_kabupaten_kota')) {
                        $wRow['kode_kabupaten_kota'] = $kodeKab;
                    }
                    if (Schema::hasColumn('wilayah', 'jenis_wilayah')) {
                        $wRow['jenis_wilayah'] = $jenis;
                    }
                    if (Schema::hasColumn('wilayah', 'updated_at')) {
                        $wRow['updated_at'] = $now;
                    }
                    if (Schema::hasColumn('wilayah', 'created_at')) {
                        $wRow['created_at'] = $now;
                    }

                    DB::table('wilayah')->insert($wRow);
                }
            }
        });

        $this->command?->info('Master provinsi & kab/kota (legacy daerah/wilayah): data dari JSON disimpan.');
    }

    /**
     * @param  array<int, array{singkatan?:string|null, nama:string, urutan?:int|null, children?:array}>  $nodes
     */
    protected function upsertMasterPusatNodes(?int $parentId, array $nodes, \DateTimeInterface $now): void
    {
        foreach ($nodes as $node) {
            $nama = $node['nama'];
            $singkatan = $node['singkatan'] ?? null;
            $urutan = $node['urutan'] ?? null;

            $q = DB::table('master_pusat')->where('nama', $nama);
            if ($parentId === null) {
                $q->whereNull('parent_id');
            } else {
                $q->where('parent_id', $parentId);
            }

            $existingId = $q->value('id');

            $row = [
                'singkatan' => $singkatan,
                'nama' => $nama,
                'urutan' => $urutan,
                'updated_at' => $now,
            ];

            if ($existingId !== null) {
                DB::table('master_pusat')->where('id', $existingId)->update($row);
                $id = (int) $existingId;
            } else {
                $row['parent_id'] = $parentId;
                $row['created_at'] = $now;
                $id = (int) DB::table('master_pusat')->insertGetId($row);
            }

            $children = $node['children'] ?? [];
            if ($children !== []) {
                $this->upsertMasterPusatNodes($id, $children, $now);
            }
        }
    }

    protected function ensureMasterPusatTreeFromDefinitions(\DateTimeInterface $now): void
    {
        $defs = $this->pusatHierarchyDefinitions();
        $this->upsertMasterPusatNodes(null, $defs, $now);

        $this->command?->info('Master pusat (hierarki parent_id): pohon organisasi disimpan.');
    }

    /**
     * @return array<int, array{singkatan?:string|null, nama:string, urutan?:int|null, children?:array<int, mixed>}>
     */
    protected function pusatHierarchyDefinitions(): array
    {
        return [
            ['singkatan' => 'Setpres', 'nama' => 'Sekretariat Presiden', 'urutan' => 10, 'children' => []],
            ['singkatan' => 'Setkab', 'nama' => 'Sekretariat Kabinet', 'urutan' => 20, 'children' => []],
            ['singkatan' => 'Kemenko PMK', 'nama' => 'Kementerian Koordinator Bidang Pembangunan Manusia dan Kebudayaan', 'urutan' => 30, 'children' => []],
            ['singkatan' => 'Kemenko Perekonomian', 'nama' => 'Kementerian Koordinator Bidang Perekonomian', 'urutan' => 40, 'children' => []],
            ['singkatan' => 'Kemenko Marves', 'nama' => 'Kementerian Koordinator Bidang Kemaritiman dan Investasi', 'urutan' => 50, 'children' => []],
            ['singkatan' => 'Kemenko Polhukam', 'nama' => 'Kementerian Koordinator Bidang Politik dan Keamanan', 'urutan' => 60, 'children' => []],
            ['singkatan' => 'Kemenko PM', 'nama' => 'Kementerian Koordinator Bidang Pemberdayaan Masyarakat', 'urutan' => 70, 'children' => []],
            ['singkatan' => 'Kemenpora', 'nama' => 'Kementerian Pemuda dan Olahraga', 'urutan' => 80, 'children' => []],
            [
                'singkatan' => 'Kemenhub',
                'nama' => 'Kementerian Perhubungan',
                'urutan' => 90,
                'children' => [
                    ['nama' => 'Sekretariat Jenderal'],
                    ['nama' => 'Direktorat Jenderal Perhubungan Darat'],
                    ['nama' => 'Direktorat Jenderal Perhubungan Laut'],
                    ['nama' => 'Direktorat Jenderal Perhubungan Udara'],
                ],
            ],
            ['singkatan' => 'Kemenparekraf', 'nama' => 'Kementerian Pariwisata dan Ekonomi Kreatif', 'urutan' => 100, 'children' => []],
            [
                'singkatan' => 'Kementerian Dalam Negeri',
                'nama' => 'Kementerian Dalam Negeri',
                'urutan' => 110,
                'children' => [
                    ['nama' => 'Sekretariat Jenderal'],
                    ['nama' => 'Direktorat Jenderal Politik dan Pemerintahan Umum'],
                    ['nama' => 'Direktorat Jenderal Otonomi Daerah'],
                ],
            ],
            ['singkatan' => 'Polri', 'nama' => 'Kepolisian Negara Republik Indonesia', 'urutan' => 120, 'children' => []],
            [
                'singkatan' => 'BNPB',
                'nama' => 'Badan Nasional Penanggulangan Bencana',
                'urutan' => 130,
                'children' => [
                    ['nama' => 'Deputi Bidang Pencegahan'],
                    ['nama' => 'Deputi Bidang Kesiapsiagaan'],
                    ['nama' => 'Deputi Bidang Rehabilitasi dan Rekonstruksi'],
                ],
            ],
            ['singkatan' => 'BMKG', 'nama' => 'Badan Meteorologi, Klimatologi, dan Geofisika', 'urutan' => 140, 'children' => []],
            ['singkatan' => 'BPS', 'nama' => 'Badan Pusat Statistik', 'urutan' => 150, 'children' => []],
            ['singkatan' => 'BSN', 'nama' => 'Badan Standardisasi Nasional', 'urutan' => 160, 'children' => []],
            ['singkatan' => 'LAN', 'nama' => 'Lembaga Administrasi Negara', 'urutan' => 170, 'children' => []],
            ['singkatan' => 'LKPP', 'nama' => 'Lembaga Kebijakan Pengadaan Barang/Jasa Pemerintah', 'urutan' => 180, 'children' => []],
            ['singkatan' => 'BKN', 'nama' => 'Badan Kepegawaian Negara', 'urutan' => 190, 'children' => []],
            ['singkatan' => 'Kemenkes', 'nama' => 'Kementerian Kesehatan', 'urutan' => 200, 'children' => []],
            ['singkatan' => 'Kemendikbudristek', 'nama' => 'Kementerian Pendidikan, Kebudayaan, Riset, dan Teknologi', 'urutan' => 210, 'children' => []],
            ['singkatan' => 'Kemenlu', 'nama' => 'Kementerian Luar Negeri', 'urutan' => 220, 'children' => []],
            ['singkatan' => 'Kemenhan', 'nama' => 'Kementerian Pertahanan', 'urutan' => 230, 'children' => []],
            ['singkatan' => 'Kejaksaan Agung', 'nama' => 'Kejaksaan Republik Indonesia', 'urutan' => 240, 'children' => []],
            ['singkatan' => 'Mahkamah Agung', 'nama' => 'Mahkamah Agung Republik Indonesia', 'urutan' => 250, 'children' => []],
            ['singkatan' => 'Kemenkumham', 'nama' => 'Kementerian Hukum dan Hak Asasi Manusia', 'urutan' => 260, 'children' => []],
            ['singkatan' => 'Kemenkeu', 'nama' => 'Kementerian Keuangan', 'urutan' => 270, 'children' => []],
            ['singkatan' => 'Kemenperin', 'nama' => 'Kementerian Perindustrian', 'urutan' => 280, 'children' => []],
            ['singkatan' => 'KemenLH', 'nama' => 'Kementerian Lingkungan Hidup', 'urutan' => 290, 'children' => []],
            ['singkatan' => 'Kemenkominfo', 'nama' => 'Kementerian Komunikasi dan Digital', 'urutan' => 300, 'children' => []],
            ['singkatan' => 'Kemenkop UKM', 'nama' => 'Kementerian Koperasi dan Usaha Kecil dan Menengah', 'urutan' => 310, 'children' => []],
        ];
    }

    protected function syncLegacyOrgSuborgFromMasterPusat(\DateTimeInterface $now): void
    {
        if (! Schema::hasTable('master_pusat') || ! Schema::hasTable('org')) {
            return;
        }

        $roots = DB::table('master_pusat')->whereNull('parent_id')->orderBy('urutan')->orderBy('nama')->get();

        /** @var array<int, int> $mapMasterToOrg */
        $mapMasterToOrg = [];

        foreach ($roots as $r) {
            $existingOrgId = DB::table('org')->where('nameorg', $r->nama)->value('id_org');

            $row = ['nameorg' => $r->nama];
            if (Schema::hasColumn('org', 'singkatan') && $r->singkatan !== null && $r->singkatan !== '') {
                $row['singkatan'] = $r->singkatan;
            }
            if (Schema::hasColumn('org', 'updated_at')) {
                $row['updated_at'] = $now;
            }

            if ($existingOrgId !== null) {
                DB::table('org')->where('id_org', $existingOrgId)->update($row);
                $mapMasterToOrg[(int) $r->id] = (int) $existingOrgId;

                continue;
            }

            if (Schema::hasColumn('org', 'created_at')) {
                $row['created_at'] = $now;
            }
            $mapMasterToOrg[(int) $r->id] = (int) DB::table('org')->insertGetId($row);
        }

        if (! Schema::hasTable('suborg')) {
            $this->command?->info('Legacy org disinkronkan dari master_pusat (akar).');

            return;
        }

        $children = DB::table('master_pusat')->whereNotNull('parent_id')->orderBy('nama')->get();

        foreach ($children as $c) {
            $orgId = $mapMasterToOrg[(int) $c->parent_id] ?? null;
            if ($orgId === null) {
                continue;
            }

            $exists = DB::table('suborg')
                ->where('id_org', $orgId)
                ->where('namesuborg', $c->nama)
                ->exists();
            if ($exists) {
                continue;
            }

            $ins = [
                'id_org' => $orgId,
                'namesuborg' => $c->nama,
            ];
            if (Schema::hasColumn('suborg', 'updated_at')) {
                $ins['updated_at'] = $now;
            }
            if (Schema::hasColumn('suborg', 'created_at')) {
                $ins['created_at'] = $now;
            }
            DB::table('suborg')->insert($ins);
        }

        $this->command?->info('Legacy org/suborg disinkronkan dari hierarki master_pusat (tingkat 1–2).');
    }

    protected static function prettyProvinceName(string $upperName): string
    {
        $u = strtoupper(trim($upperName));

        return match ($u) {
            'DKI JAKARTA' => 'DKI Jakarta',
            'DI YOGYAKARTA' => 'DI Yogyakarta',
            default => Str::title(Str::lower($upperName)),
        };
    }

    protected static function prettyKabupatenKotaName(string $upperName): string
    {
        return Str::title(Str::lower(trim($upperName)));
    }

    protected static function jenisWilayahFromName(string $name): string
    {
        return str_starts_with(strtoupper(trim($name)), 'KOTA ') ? 'kota' : 'kabupaten';
    }
}
