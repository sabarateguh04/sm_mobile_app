<?php

namespace Database\Seeders;

use App\Models\Usm;
use App\Support\LegacyUsmDbInput;
use App\Support\MasterKlHierarchyWriter;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;

/**
 * Master organisasi 4 tingkat (selaras lembar contoh) + personil operator contoh.
 *
 * Polri → Polda Metro Jaya → Ditlantas Metro Jaya → Subdit Gakkum
 * Polri → Polda Metro Jaya → Polrestro Bekasi → Satlantas Polrestro Bekasi
 * Polri → Korlantas → PJR Induk Bitung → Unit 1 PJR Induk Bitung
 *
 * Jalankan: php artisan db:seed --class=OrganisasiHierarkiPersonilSeeder
 */
class OrganisasiHierarkiPersonilSeeder extends Seeder
{
    /** @var array<string, array<string, array<string, list<string>>>> */
    private array $hierarki = [
        'Polri' => [
            'Korlantas' => [
                'PJR Induk Bitung' => [
                    'Unit 1 PJR Induk Bitung',
                ],
            ],
            'Polda Metro Jaya' => [
                'Ditlantas Metro Jaya' => [
                    'Subdit Gakkum',
                ],
                'Polrestro Bekasi' => [
                    'Satlantas Polrestro Bekasi',
                ],
            ],
            'Polda Jabar' => [
                'Polres Cimahi' => [
                    'Satlantas Polres Cimahi',
                ],
            ],
        ],
        'Kemendagri' => [
            'Pemprop Jabar' => [
                'Pemkab Cimahi' => [
                    'UPT Dishub Cimahi',
                ],
            ],
            'Pemprop DKI' => [
                'Trans Jakarta' => [
                    'Moda Trans Jakarta',
                ],
            ],
        ],
        'Kemenhub' => [],
        'Kemenpu' => [
            'BPJT' => [
                'Bidang Investasi Jalan Tol' => [
                    'Seksi Operasional Tol',
                ],
            ],
        ],
    ];

    public function run(): void
    {
        if (! Schema::hasTable('usm') || ! Schema::hasTable('roles')) {
            $this->command?->warn('OrganisasiHierarkiPersonilSeeder: tabel usm/roles tidak ada — dilewati.');

            return;
        }

        $now = now();
        $empatTingkat = Schema::hasTable('master_kl_unit_kerja');

        if (Schema::hasTable('master_kl_pusat')) {
            $this->seedMasterOrganisasi($now, $empatTingkat);
        }

        $operatorRoleId = $this->resolveRoleId((int) config('role_menu_matrix.operator_matrix_role_id', 3));
        $adminRoleId = $this->resolveRoleId(2);
        if ($operatorRoleId === null) {
            $this->command?->error('OrganisasiHierarkiPersonilSeeder: role operator tidak ditemukan.');

            return;
        }

        $dki = $this->resolveWilayah('DKI Jakarta', 'Kota Administrasi Jakarta Pusat');
        $passwordHash = Hash::make(RoleDemoAccountsSeeder::DEMO_PASSWORD);

        $created = 0;

        $kepDitlantas = $empatTingkat
            ? [
                'induk_pemerintahan' => 'Polri',
                'pemerintahan_daerah' => 'Polda Metro Jaya',
                'organisasi_daerah' => 'Ditlantas Metro Jaya',
                'unit_kerja' => 'Subdit Gakkum',
            ]
            : [
                'induk_pemerintahan' => 'Polri',
                'pemerintahan_daerah' => 'Polda Metro Jaya',
                'unit_kerja' => 'Ditlantas Metro Jaya',
            ];

        $kepPolrestro = $empatTingkat
            ? [
                'induk_pemerintahan' => 'Polri',
                'pemerintahan_daerah' => 'Polda Metro Jaya',
                'organisasi_daerah' => 'Polrestro Bekasi',
                'unit_kerja' => 'Satlantas Polrestro Bekasi',
            ]
            : [
                'induk_pemerintahan' => 'Polri',
                'pemerintahan_daerah' => 'Polda Metro Jaya',
                'unit_kerja' => 'Polrestro Bekasi',
            ];

        $kepPjr = $empatTingkat
            ? [
                'induk_pemerintahan' => 'Polri',
                'pemerintahan_daerah' => 'Korlantas',
                'organisasi_daerah' => 'PJR Induk Bitung',
                'unit_kerja' => 'Unit 1 PJR Induk Bitung',
            ]
            : [
                'induk_pemerintahan' => 'Polri',
                'pemerintahan_daerah' => 'Korlantas',
                'unit_kerja' => 'PJR Induk Bitung',
            ];

        if ($adminRoleId !== null) {
            $created += $this->upsertAkun($passwordHash, $adminRoleId, array_merge([
                'username' => 'Dirlantas_Metro_Jaya',
                'email' => 'dirlantas_metro_jaya@copilotproduction.local',
                'nama' => 'Dirlantas Polda Metro Jaya',
                'daerah' => $dki['daerah'],
                'wilayah' => $dki['wilayah'],
                'kategori_user' => 'organik',
            ], $kepDitlantas));
        }

        $created += $this->upsertAkun($passwordHash, $operatorRoleId, array_merge([
            'username' => 'op_ditlantas_metro',
            'email' => 'op_ditlantas_metro@copilotproduction.local',
            'nama' => 'Operator Subdit Gakkum Ditlantas Metro',
            'daerah' => $dki['daerah'],
            'wilayah' => $dki['wilayah'],
            'kategori_user' => 'organik',
        ], $kepDitlantas));

        $created += $this->upsertAkun($passwordHash, $operatorRoleId, array_merge([
            'username' => 'op_polrestro_bekasi',
            'email' => 'op_polrestro_bekasi@copilotproduction.local',
            'nama' => 'Operator Satlantas Polrestro Bekasi',
            'daerah' => $dki['daerah'],
            'wilayah' => 'Kota Bekasi',
            'kategori_user' => 'organik',
        ], $kepPolrestro));

        $created += $this->upsertAkun($passwordHash, $operatorRoleId, array_merge([
            'username' => 'op_pjr_bitung',
            'email' => 'op_pjr_bitung@copilotproduction.local',
            'nama' => 'Operator Unit 1 PJR Induk Bitung',
            'daerah' => 'Sulawesi Utara',
            'wilayah' => 'Kota Bitung',
            'kategori_user' => 'organik',
        ], $kepPjr));

        $created += $this->backfillKepegawaianOperatorLegacy($operatorRoleId, [
            'md_op_3101' => $kepDitlantas,
        ]);

        $pwd = RoleDemoAccountsSeeder::DEMO_PASSWORD;
        $this->command?->info("OrganisasiHierarkiPersonilSeeder: {$created} akun master/personil disimpan atau diperbarui.");
        $this->command?->info("  Admin: Dirlantas_Metro_Jaya — Operator: op_ditlantas_metro, op_polrestro_bekasi, op_pjr_bitung");
        $this->command?->info("  Password: {$pwd}");
    }

    private function seedMasterOrganisasi(\DateTimeInterface $now, bool $empatTingkat): void
    {
        foreach ($this->hierarki as $induk => $orgPusatMap) {
            $pusatId = MasterKlHierarchyWriter::ensurePusat($induk, $now);

            foreach ($orgPusatMap as $namaOrgPusat => $orgDaerahMap) {
                $daerahId = MasterKlHierarchyWriter::ensureDaerah($pusatId, $namaOrgPusat, $now);

                foreach ($orgDaerahMap as $namaOrgDaerah => $unitKerjaList) {
                    $resortId = MasterKlHierarchyWriter::ensureResort($daerahId, $namaOrgDaerah, $now);

                    if ($empatTingkat) {
                        foreach ($unitKerjaList as $namaUnitKerja) {
                            MasterKlHierarchyWriter::ensureUnitKerja($resortId, $namaUnitKerja, $now);
                        }
                    }
                }
            }
        }
    }

    /**
     * @param  array<string, array<string, string>>  $map
     */
    private function backfillKepegawaianOperatorLegacy(int $operatorRoleId, array $map): int
    {
        $updated = 0;

        foreach ($map as $username => $kep) {
            $user = Usm::query()
                ->where('username', $username)
                ->where('user_role_id', $operatorRoleId)
                ->first();

            if (! $user) {
                continue;
            }

            $user->fill(LegacyUsmDbInput::normalizeOptionalTextFields($kep));
            $user->save();
            $updated++;
        }

        return $updated;
    }

    /**
     * @return array{daerah: string, wilayah: string}
     */
    private function resolveWilayah(string $provinsiLike, string $defaultWilayah): array
    {
        $daerah = $provinsiLike;

        if (Schema::hasTable('master_provinsi')) {
            $prov = DB::table('master_provinsi')
                ->whereRaw('LOWER(nama) LIKE ?', ['%'.mb_strtolower($provinsiLike).'%'])
                ->orderBy('nama')
                ->value('nama');
            if ($prov) {
                $daerah = (string) $prov;
            }
        }

        $wilayah = $defaultWilayah;

        if (Schema::hasTable('master_kabupaten_kota') && Schema::hasTable('master_provinsi')) {
            $provCol = Schema::hasColumn('master_kabupaten_kota', 'master_provinsi_id')
                ? 'master_provinsi_id'
                : (Schema::hasColumn('master_kabupaten_kota', 'provinsi_id') ? 'provinsi_id' : null);

            if ($provCol !== null) {
                $kab = DB::table('master_kabupaten_kota as k')
                    ->join('master_provinsi as p', 'p.id', '=', 'k.'.$provCol)
                    ->whereRaw('LOWER(p.nama) LIKE ?', ['%'.mb_strtolower($provinsiLike).'%'])
                    ->whereRaw('LOWER(k.nama) LIKE ?', ['%'.mb_strtolower($defaultWilayah).'%'])
                    ->value('k.nama');
                if ($kab) {
                    $wilayah = (string) $kab;
                }
            }
        }

        return ['daerah' => $daerah, 'wilayah' => $wilayah];
    }

    private function resolveRoleId(int $matrixRoleId): ?int
    {
        if (! Schema::hasTable('roles')) {
            return null;
        }

        $pk = Schema::hasColumn('roles', 'role_id') ? 'role_id' : 'id';

        if (! DB::table('roles')->where($pk, $matrixRoleId)->exists()) {
            return null;
        }

        return (int) DB::table('roles')->where($pk, $matrixRoleId)->value($pk);
    }

    /**
     * @param  array<string, mixed>  $fields
     */
    private function upsertAkun(string $passwordHash, int $roleId, array $fields): int
    {
        $row = array_merge([
            'password' => $passwordHash,
            'account_status' => 'active',
            'user_role_id' => $roleId,
            'notelp' => '',
            'fotoprof' => '',
            'nrp_nip_nik' => '',
        ], $fields);

        if (Schema::hasColumn('usm', 'email_status')) {
            $row['email_status'] = '';
        }

        $row = LegacyUsmDbInput::normalizeOptionalTextFields($row);

        Usm::query()->updateOrCreate(
            ['username' => $fields['username']],
            $row
        );

        return 1;
    }
}
