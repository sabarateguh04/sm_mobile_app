<?php

namespace Database\Seeders;

use App\Models\Usm;
use App\Support\LegacyUsmDbInput;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

/**
 * Akun demo per teritori dari **master_provinsi** & **master_kabupaten_kota** saja
 * (nilai `usm.daerah` / `usm.wilayah` mengikuti kolom `nama` di kedua tabel itu — tidak memakai tabel legacy `wilayah`).
 *
 * Password (seragam): sama dengan {@see RoleDemoAccountsSeeder::DEMO_PASSWORD}.
 *
 * Lingkup baris kab/kota:
 * - env `MASTER_DEMO_TERRITORY_SCOPE=per_province` (default): satu kab/kot pertama (urut `nama`) per provinsi.
 * - env `MASTER_DEMO_TERRITORY_SCOPE=all_kabkot`: setiap baris `master_kabupaten_kota` (satu akun per pasangan provinsi–wilayah per role).
 *
 * Role per kab (env `MASTER_DEMO_MATRIX_ROLES`, default `2,3,5,6`): admin (2), oprator (3), eks daerah (5), eks resort (6).
 * Dari Artisan dapat dioverride: `php artisan demo:territory-accounts-seed all_kabkot --roles=2,3`.
 *
 * Akun nasional (tanpa provinsi/kab di profil): superadmin (1), eks nas (4) — dibuat sekali.
 */
class MasterTerritoryDemoAccountsSeeder extends Seeder
{
    /** @var 'per_province'|'all_kabkot'|null — jika diisi, mengabaikan env untuk run ini */
    public ?string $territoryScopeOverride = null;

    /** @var array<int>|null — subset matrix role untuk kab/kot; jika diisi mengabaikan env `MASTER_DEMO_MATRIX_ROLES` */
    public ?array $matrixRoleIdsOverride = null;

    public function run(): void
    {
        if (! Schema::hasTable('roles') || ! Schema::hasTable('usm')) {
            $this->command?->warn('MasterTerritoryDemoAccountsSeeder: tabel roles atau usm tidak ada — dilewati.');

            return;
        }

        if (! Schema::hasTable('master_provinsi') || ! Schema::hasTable('master_kabupaten_kota')) {
            $this->command?->warn('MasterTerritoryDemoAccountsSeeder: master_provinsi / master_kabupaten_kota tidak ada — jalankan IndonesiaMasterDataSeeder terlebih dahulu.');

            return;
        }

        $meta = $this->rolesPrimaryKey();
        $pk = $meta['pk'];
        $prop = $meta['valueProp'];
        $passwordPlain = RoleDemoAccountsSeeder::DEMO_PASSWORD;
        $password = Hash::make($passwordPlain);

        $scope = $this->resolvedScope();

        $kabRows = $this->resolveKabupatenKotaRows($scope);
        if ($kabRows === []) {
            $this->command?->warn('MasterTerritoryDemoAccountsSeeder: tidak ada baris kab/kot — periksa data master.');

            return;
        }

        $this->command?->info('MasterTerritoryDemoAccountsSeeder: mode «'.$scope.'», '.count($kabRows).' pasangan provinsi–kab/kot.');

        $created = 0;

        $nationalRows = [
            [
                'matrix_role' => 1,
                'username' => 'mdemo_superadmin',
                'email' => 'mdemo.superadmin@demo.local',
                'nama' => 'Demo superadmin (master teritori)',
            ],
            [
                'matrix_role' => 4,
                'username' => 'mdemo_eks_nas',
                'email' => 'mdemo.eks.nas@demo.local',
                'nama' => 'Demo eks nas (master teritori)',
            ],
        ];

        foreach ($nationalRows as $nr) {
            $rid = (int) $nr['matrix_role'];
            $role = DB::table('roles')->where($pk, $rid)->first();
            if (! $role) {
                $this->command?->warn("Role matrix {$rid} tidak ada — lewat {$nr['username']}.");

                continue;
            }
            $fk = (int) $role->{$prop};
            $payload = $this->basePayload($password, $fk);
            $payload['username'] = $nr['username'];
            $payload['email'] = $nr['email'];
            $payload['nama'] = $nr['nama'];
            $payload['daerah'] = '';
            $payload['wilayah'] = '';
            $payload = LegacyUsmDbInput::normalizeOptionalTextFields($payload);
            Usm::query()->updateOrCreate(['username' => $nr['username']], $payload);
            $created++;
        }

        $created += $this->seedUsmRowsForKabupatenBatch($kabRows, $password, $pk, $prop);

        $this->command?->info("MasterTerritoryDemoAccountsSeeder: {$created} akun disimpan/diperbarui. Password: {$passwordPlain}");
    }

    /**
     * @param  array<int, object{nama_provinsi: string, nama_kabupaten_kota: string, kode_kabupaten_kota: string}>  $kabRows
     */
    protected function seedUsmRowsForKabupatenBatch(array $kabRows, string $passwordHash, string $pk, string $prop): int
    {
        $created = 0;

        foreach ($kabRows as $row) {
            $namaProv = (string) $row->nama_provinsi;
            $namaKab = (string) $row->nama_kabupaten_kota;
            $kodeKab = (string) $row->kode_kabupaten_kota;
            $slugKab = $this->usernameSuffixFromKode($kodeKab);

            $matrixRoles = $this->matrixRolesForSeed();

            foreach ($matrixRoles as $matrixId => $info) {
                $role = DB::table('roles')->where($pk, $matrixId)->first();
                if (! $role) {
                    $this->command?->warn("Role matrix {$matrixId} tidak ada — lewat {$info['prefix']}_{$slugKab}.");

                    continue;
                }
                $fk = (int) $role->{$prop};
                $username = $info['prefix'].'_'.$slugKab;
                $mailTag = match ($matrixId) {
                    2 => 'admin',
                    3 => 'oprator',
                    5 => 'eks-daerah',
                    6 => 'eks-resort',
                    default => 'r'.$matrixId,
                };
                $email = 'mdemo.'.$mailTag.'.'.$slugKab.'@demo.local';
                if (strlen($email) > 250) {
                    $email = 'mdemo.'.$matrixId.'.'.substr($slugKab, 0, 40).'@demo.local';
                }

                $payload = $this->basePayload($passwordHash, $fk);
                $payload['username'] = $username;
                $payload['email'] = $email;
                $payload['nama'] = 'Demo '.$info['label'].' — '.$namaKab.', '.$namaProv;
                $payload['daerah'] = $namaProv;
                $payload['wilayah'] = $namaKab;
                $payload = LegacyUsmDbInput::normalizeOptionalTextFields($payload);

                Usm::query()->updateOrCreate(['username' => $username], $payload);
                $created++;
            }
        }

        return $created;
    }

    /**
     * Baris untuk dokumentasi / export Excel (sama dengan akun yang di-seed, tanpa menulis ke `usm`).
     *
     * @return array<int, array<string, int|string|null>>
     */
    public function previewSpreadsheetRows(): array
    {
        if (! Schema::hasTable('roles') || ! Schema::hasTable('master_provinsi') || ! Schema::hasTable('master_kabupaten_kota')) {
            return [];
        }

        $meta = $this->rolesPrimaryKey();
        $pk = $meta['pk'];

        $scope = $this->resolvedScope();

        $kabRows = $this->resolveKabupatenKotaRows($scope);
        $rows = [];

        $nationalTemplates = [
            [
                'matrix_role' => 1,
                'username' => 'mdemo_superadmin',
                'email' => 'mdemo.superadmin@demo.local',
                'nama' => 'Demo superadmin (master teritori)',
            ],
            [
                'matrix_role' => 4,
                'username' => 'mdemo_eks_nas',
                'email' => 'mdemo.eks.nas@demo.local',
                'nama' => 'Demo eks nas (master teritori)',
            ],
        ];

        foreach ($nationalTemplates as $nr) {
            $rid = (int) $nr['matrix_role'];
            $role = DB::table('roles')->where($pk, $rid)->first();
            if (! $role) {
                continue;
            }
            $roleName = (string) ($role->role_name ?? '');
            $rows[] = [
                'role_id' => $rid,
                'nama_role' => $roleName,
                'username' => $nr['username'],
                'password' => RoleDemoAccountsSeeder::DEMO_PASSWORD,
                'email' => $nr['email'],
                'nama_lengkap' => $nr['nama'],
                'daerah' => '',
                'wilayah' => '',
                'kode_territori' => 'nasional',
            ];
        }

        foreach ($kabRows as $row) {
            $namaProv = (string) $row->nama_provinsi;
            $namaKab = (string) $row->nama_kabupaten_kota;
            $kodeKab = (string) $row->kode_kabupaten_kota;
            $slugKab = $this->usernameSuffixFromKode($kodeKab);

            $matrixRoles = $this->matrixRolesForSeed();

            foreach ($matrixRoles as $matrixId => $info) {
                $role = DB::table('roles')->where($pk, $matrixId)->first();
                if (! $role) {
                    continue;
                }
                $roleName = (string) ($role->role_name ?? '');
                $username = $info['prefix'].'_'.$slugKab;
                $mailTag = match ($matrixId) {
                    2 => 'admin',
                    3 => 'oprator',
                    5 => 'eks-daerah',
                    6 => 'eks-resort',
                    default => 'r'.$matrixId,
                };
                $email = 'mdemo.'.$mailTag.'.'.$slugKab.'@demo.local';
                if (strlen($email) > 250) {
                    $email = 'mdemo.'.$matrixId.'.'.substr($slugKab, 0, 40).'@demo.local';
                }

                $rows[] = [
                    'role_id' => $matrixId,
                    'nama_role' => $roleName,
                    'username' => $username,
                    'password' => RoleDemoAccountsSeeder::DEMO_PASSWORD,
                    'email' => $email,
                    'nama_lengkap' => 'Demo '.$info['label'].' — '.$namaKab.', '.$namaProv,
                    'daerah' => $namaProv,
                    'wilayah' => $namaKab,
                    'kode_territori' => 'master_provinsi_kabkot',
                ];
            }
        }

        return $rows;
    }

    protected function resolvedScope(): string
    {
        if ($this->territoryScopeOverride !== null && $this->territoryScopeOverride !== '') {
            $scope = strtolower(trim($this->territoryScopeOverride));
        } else {
            $scope = strtolower((string) env('MASTER_DEMO_TERRITORY_SCOPE', 'per_province'));
        }

        if (! in_array($scope, ['per_province', 'all_kabkot'], true)) {
            $scope = 'per_province';
        }

        return $scope;
    }

    /**
     * Definisi penuh role matrix untuk akun per kab/kot (urutan kunci dipertahankan).
     *
     * @return array<int, array{prefix: string, label: string}>
     */
    protected function matrixRoleDefinitions(): array
    {
        return [
            2 => ['prefix' => 'md_ad', 'label' => 'admin'],
            3 => ['prefix' => 'md_op', 'label' => 'oprator'],
            5 => ['prefix' => 'md_ed', 'label' => 'eks daerah'],
            6 => ['prefix' => 'md_er', 'label' => 'eks resort'],
        ];
    }

    /**
     * Subset role yang di-seed per pasangan provinsi–kab (override Artisan atau env `MASTER_DEMO_MATRIX_ROLES`).
     *
     * @return array<int, array{prefix: string, label: string}>
     */
    protected function matrixRolesForSeed(): array
    {
        $all = $this->matrixRoleDefinitions();

        if (is_array($this->matrixRoleIdsOverride) && $this->matrixRoleIdsOverride !== []) {
            $allow = array_flip(array_map('intval', $this->matrixRoleIdsOverride));

            return array_intersect_key($all, $allow);
        }

        $raw = (string) env('MASTER_DEMO_MATRIX_ROLES', '2,3,5,6');
        $ids = array_values(array_filter(
            array_map('intval', explode(',', $raw)),
            static fn (int $x): bool => $x > 0
        ));
        if ($ids === []) {
            return $all;
        }

        return array_intersect_key($all, array_flip($ids));
    }

    /**
     * @return array<int, object{nama_provinsi: string, nama_kabupaten_kota: string, kode_kabupaten_kota: string}>
     */
    protected function resolveKabupatenKotaRows(string $scope): array
    {
        if ($scope === 'all_kabkot') {
            return DB::table('master_kabupaten_kota as k')
                ->join('master_provinsi as p', 'p.id', '=', 'k.master_provinsi_id')
                ->orderBy('p.kode_provinsi')
                ->orderBy('k.nama')
                ->get(['p.nama as nama_provinsi', 'k.nama as nama_kabupaten_kota', 'k.kode_kabupaten_kota'])
                ->map(fn ($r) => (object) [
                    'nama_provinsi' => (string) $r->nama_provinsi,
                    'nama_kabupaten_kota' => (string) $r->nama_kabupaten_kota,
                    'kode_kabupaten_kota' => (string) $r->kode_kabupaten_kota,
                ])
                ->all();
        }

        $out = [];
        $provIds = DB::table('master_provinsi')->orderBy('kode_provinsi')->pluck('id');

        foreach ($provIds as $pid) {
            $kab = DB::table('master_kabupaten_kota')
                ->where('master_provinsi_id', $pid)
                ->orderBy('nama')
                ->first(['nama', 'kode_kabupaten_kota']);

            if ($kab === null) {
                continue;
            }

            $namaProv = (string) DB::table('master_provinsi')->where('id', $pid)->value('nama');

            $out[] = (object) [
                'nama_provinsi' => $namaProv,
                'nama_kabupaten_kota' => (string) $kab->nama,
                'kode_kabupaten_kota' => (string) $kab->kode_kabupaten_kota,
            ];
        }

        return $out;
    }

    /**
     * @return array{pk: string, valueProp: string}
     */
    protected function rolesPrimaryKey(): array
    {
        if (! Schema::hasTable('roles')) {
            return ['pk' => 'id', 'valueProp' => 'id'];
        }
        if (Schema::hasColumn('roles', 'role_id')) {
            return ['pk' => 'role_id', 'valueProp' => 'role_id'];
        }

        return ['pk' => 'id', 'valueProp' => 'id'];
    }

    protected function usernameSuffixFromKode(string $kode): string
    {
        $s = preg_replace('/[^a-zA-Z0-9]+/', '_', trim($kode));
        $s = trim((string) $s, '_');
        if ($s === '') {
            $s = (string) Str::random(8);
        }

        return strtolower(substr($s, 0, 48));
    }

    /**
     * @return array<string, mixed>
     */
    protected function basePayload(string $passwordHash, int $userRoleFk): array
    {
        $row = [
            'password' => $passwordHash,
            'account_status' => 'active',
            'user_role_id' => $userRoleFk,
            'notelp' => '',
            'fotoprof' => '',
        ];

        if (Schema::hasColumn('usm', 'email_status')) {
            $row['email_status'] = '';
        }

        return $row;
    }
}
