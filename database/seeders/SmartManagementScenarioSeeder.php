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
 * Skenario uji Smart Management (Jawa Barat & Jawa Timur):
 * - Operator per grup kab/kota sesui matrix jumlah.
 * - Executive 3 (role 5): 2 akun per grup, profil daerah+wilayah = grup.
 * - Executive 2 (role 6): 4 akun per provinsi, profil hanya provinsi (wilayah kosong) — hanya lihat.
 * - Executive 1 (role 7): 5 akun nasional (daerah & wilayah kosong) — hanya lihat.
 *
 * Prasyarat: {@see IndonesiaMasterDataSeeder}, tabel `roles` (matriks 1–7), `usm`.
 * Jalankan: `php artisan db:seed --class=SmartManagementScenarioSeeder`
 *
 * Ekspor ke Excel (semua akun `usm` atau hanya `sm_*`): `php artisan export:usm-accounts-excel` / `--smartmgmt`
 *
 * Password sama dengan {@see RoleDemoAccountsSeeder::DEMO_PASSWORD}.
 */
class SmartManagementScenarioSeeder extends Seeder
{
    /** @var list<array{tokens: list<string>, prefer_kota?: bool, prefer_kabupaten?: bool, operators: int, exec3: int}> */
    protected array $jabarGroups = [
        ['tokens' => ['Bandung'], 'prefer_kota' => true, 'operators' => 4, 'exec3' => 2],
        ['tokens' => ['Bogor'], 'prefer_kabupaten' => true, 'operators' => 3, 'exec3' => 2],
        ['tokens' => ['Ciamis'], 'prefer_kabupaten' => true, 'operators' => 4, 'exec3' => 2],
        ['tokens' => ['Cianjur'], 'prefer_kabupaten' => true, 'operators' => 5, 'exec3' => 2],
        ['tokens' => ['Garut'], 'prefer_kabupaten' => true, 'operators' => 3, 'exec3' => 2],
        ['tokens' => ['Indramayu'], 'prefer_kabupaten' => true, 'operators' => 2, 'exec3' => 2],
    ];

    /** @var list<array{tokens: list<string>, prefer_kota?: bool, prefer_kabupaten?: bool, operators: int, exec3: int}> */
    protected array $jatimGroups = [
        ['tokens' => ['Surabaya'], 'prefer_kota' => true, 'operators' => 4, 'exec3' => 2],
        ['tokens' => ['Gresik', 'Gersik'], 'prefer_kabupaten' => true, 'operators' => 4, 'exec3' => 2],
    ];

    public function run(): void
    {
        if (! Schema::hasTable('roles') || ! Schema::hasTable('usm')) {
            $this->command?->warn('SmartManagementScenarioSeeder: tabel roles atau usm tidak ada — dilewati.');

            return;
        }

        if (! Schema::hasTable('master_provinsi') || ! Schema::hasTable('master_kabupaten_kota')) {
            $this->command?->warn('SmartManagementScenarioSeeder: jalankan IndonesiaMasterDataSeeder terlebih dahulu (master provinsi / kab-kot).');

            return;
        }

        $meta = $this->rolesPrimaryKey();
        $pk = $meta['pk'];
        $prop = $meta['valueProp'];

        foreach ([4, 5, 6, 7] as $rid) {
            if (! DB::table('roles')->where($pk, $rid)->exists()) {
                $this->command?->error("SmartManagementScenarioSeeder: role matrix {$rid} tidak ada di tabel roles.");

                return;
            }
        }

        $jabar = DB::table('master_provinsi')->where('nama', 'like', '%Jawa Barat%')->orderBy('nama')->first();
        $jatim = DB::table('master_provinsi')->where('nama', 'like', '%Jawa Timur%')->orderBy('nama')->first();

        if ($jabar === null || $jatim === null) {
            $this->command?->error('SmartManagementScenarioSeeder: provinsi Jawa Barat atau Jawa Timur tidak ditemukan di master_provinsi.');

            return;
        }

        $passwordPlain = RoleDemoAccountsSeeder::DEMO_PASSWORD;
        $passwordHash = Hash::make($passwordPlain);

        $roleFk = static fn (int $matrixId): int => (int) DB::table('roles')->where($pk, $matrixId)->value($prop);

        $created = 0;

        foreach ($this->jabarGroups as $group) {
            $kab = $this->resolveKabRow((int) $jabar->id, $group);
            if ($kab === null) {
                $this->command?->warn('Grup Jabar '.implode('|', $group['tokens']).': kab/kot tidak ditemukan — dilewati.');

                continue;
            }
            $created += $this->seedGroupAccounts(
                $passwordHash,
                $roleFk,
                (string) $jabar->nama,
                $kab,
                'jabar',
                $group['operators'],
                $group['exec3']
            );
        }

        foreach ($this->jatimGroups as $group) {
            $kab = $this->resolveKabRow((int) $jatim->id, $group);
            if ($kab === null) {
                $this->command?->warn('Grup Jatim '.implode('|', $group['tokens']).': kab/kot tidak ditemukan — dilewati.');

                continue;
            }
            $created += $this->seedGroupAccounts(
                $passwordHash,
                $roleFk,
                (string) $jatim->nama,
                $kab,
                'jatim',
                $group['operators'],
                $group['exec3']
            );
        }

        $namaProvJabar = (string) $jabar->nama;
        $namaProvJatim = (string) $jatim->nama;

        for ($i = 1; $i <= 4; $i++) {
            $u = 'sm_ex2_jabar_'.$i;
            $created += $this->upsertUsm($passwordHash, $roleFk(6), [
                'username' => $u,
                'email' => $u.'@smartmgmt.demo.local',
                'nama' => 'SmartMgmt Executive 2 Jawa Barat '.$i,
                'daerah' => $namaProvJabar,
                'wilayah' => '',
            ]);
        }

        for ($i = 1; $i <= 4; $i++) {
            $u = 'sm_ex2_jatim_'.$i;
            $created += $this->upsertUsm($passwordHash, $roleFk(6), [
                'username' => $u,
                'email' => $u.'@smartmgmt.demo.local',
                'nama' => 'SmartMgmt Executive 2 Jawa Timur '.$i,
                'daerah' => $namaProvJatim,
                'wilayah' => '',
            ]);
        }

        for ($i = 1; $i <= 5; $i++) {
            $u = 'sm_ex1_nas_'.$i;
            $created += $this->upsertUsm($passwordHash, $roleFk(7), [
                'username' => $u,
                'email' => $u.'@smartmgmt.demo.local',
                'nama' => 'SmartMgmt Executive 1 Nasional '.$i,
                'daerah' => '',
                'wilayah' => '',
            ]);
        }

        $this->command?->info("SmartManagementScenarioSeeder: {$created} akun disimpan/diperbarui. Password: {$passwordPlain}");
    }

    /**
     * @param  array{tokens: list<string>, prefer_kota?: bool, prefer_kabupaten?: bool, operators: int, exec3: int}  $group
     */
    protected function resolveKabRow(int $masterProvinsiId, array $group): ?object
    {
        foreach ($group['tokens'] as $token) {
            $rows = DB::table('master_kabupaten_kota')
                ->where('master_provinsi_id', $masterProvinsiId)
                ->whereRaw('LOWER(nama) LIKE ?', ['%'.mb_strtolower($token).'%'])
                ->orderBy('nama')
                ->get();

            if ($rows->isEmpty()) {
                continue;
            }

            if (! empty($group['prefer_kota'])) {
                $hit = $rows->first(fn ($r) => str_starts_with((string) $r->nama, 'Kota '));
                if ($hit !== null) {
                    return $hit;
                }
            }

            if (! empty($group['prefer_kabupaten'])) {
                $hit = $rows->first(fn ($r) => str_starts_with((string) $r->nama, 'Kabupaten '));
                if ($hit !== null) {
                    return $hit;
                }
            }

            return $rows->first();
        }

        return null;
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

    /**
     * @param  object{nama: string, kode_kabupaten_kota?: string|null}  $kabRow
     */
    protected function seedGroupAccounts(
        string $passwordHash,
        callable $roleFk,
        string $namaProvinsi,
        object $kabRow,
        string $regionSlug,
        int $operatorCount,
        int $exec3Count
    ): int {
        $namaKab = (string) $kabRow->nama;
        $slug = $this->slugFromKab($namaKab, $kabRow);

        $n = 0;
        $opFk = $roleFk(4);
        $ex3Fk = $roleFk(5);

        for ($i = 1; $i <= $operatorCount; $i++) {
            $suffix = str_pad((string) $i, 2, '0', STR_PAD_LEFT);
            $u = 'sm_op_'.$regionSlug.'_'.$slug.'_'.$suffix;
            $n += $this->upsertUsm($passwordHash, $opFk, [
                'username' => $u,
                'email' => $u.'@smartmgmt.demo.local',
                'nama' => 'SmartMgmt Operator '.$namaKab.' '.$i,
                'daerah' => $namaProvinsi,
                'wilayah' => $namaKab,
            ]);
        }

        for ($i = 1; $i <= $exec3Count; $i++) {
            $suffix = str_pad((string) $i, 2, '0', STR_PAD_LEFT);
            $u = 'sm_ex3_'.$regionSlug.'_'.$slug.'_'.$suffix;
            $n += $this->upsertUsm($passwordHash, $ex3Fk, [
                'username' => $u,
                'email' => $u.'@smartmgmt.demo.local',
                'nama' => 'SmartMgmt Executive 3 '.$namaKab.' '.$i,
                'daerah' => $namaProvinsi,
                'wilayah' => $namaKab,
            ]);
        }

        return $n;
    }

    protected function slugFromKab(string $namaKab, object $kabRow): string
    {
        $kode = isset($kabRow->kode_kabupaten_kota) ? trim((string) $kabRow->kode_kabupaten_kota) : '';
        if ($kode !== '') {
            $s = preg_replace('/[^a-zA-Z0-9]+/', '_', $kode);
            $s = strtolower(trim((string) $s, '_'));

            return $s !== '' ? substr($s, 0, 40) : $this->slugFromNamaKab($namaKab);
        }

        return $this->slugFromNamaKab($namaKab);
    }

    protected function slugFromNamaKab(string $namaKab): string
    {
        $s = preg_replace('/[^a-zA-Z0-9]+/', '_', $namaKab);
        $s = strtolower(trim((string) $s, '_'));

        return $s !== '' ? substr($s, 0, 40) : strtolower(Str::random(8));
    }

    /**
     * @param  array{username: string, email: string, nama: string, daerah: string, wilayah: string}  $fields
     */
    protected function upsertUsm(string $passwordHash, int $userRoleFk, array $fields): int
    {
        $row = array_merge($this->basePayload($passwordHash, $userRoleFk), $fields);
        $row = LegacyUsmDbInput::normalizeOptionalTextFields($row);

        Usm::query()->updateOrCreate(['username' => $fields['username']], $row);

        return 1;
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
