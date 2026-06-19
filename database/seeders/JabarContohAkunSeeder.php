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
 * Akun contoh Jawa Barat (selaras lembar profil):
 * - Dirlantas_Jabar — Polri → Polda Jabar → Pimpinan (+ Ditlantas di master)
 * - Gubernur_Jabar — Kemendagri → Pemprop → Pimpinan
 *
 * Prasyarat: {@see MasterKlHierarchySeeder}, tabel `roles`, `usm`.
 * Jalankan: php artisan db:seed --class=JabarContohAkunSeeder
 *
 * Password: sama dengan {@see RoleDemoAccountsSeeder::DEMO_PASSWORD}
 */
class JabarContohAkunSeeder extends Seeder
{
    public function run(): void
    {
        if (! Schema::hasTable('usm') || ! Schema::hasTable('roles')) {
            $this->command?->warn('JabarContohAkunSeeder: tabel usm/roles tidak ada — dilewati.');

            return;
        }

        $now = now();
        if (Schema::hasTable('master_kl_pusat')) {
            $this->seedKlMaster($now);
        }

        $roleId = $this->resolveRoleId(2);
        if ($roleId === null) {
            $roleId = $this->resolveRoleId(3);
        }
        if ($roleId === null) {
            $this->command?->error('JabarContohAkunSeeder: role matrix 2 atau 3 tidak ditemukan di tabel roles.');

            return;
        }

        $wilayahBundle = $this->resolveProvinsiWilayahJabar();

        $passwordHash = Hash::make(RoleDemoAccountsSeeder::DEMO_PASSWORD);
        $created = 0;

        $created += $this->upsertAkun($passwordHash, $roleId, [
            'username' => 'Dirlantas_Jabar',
            'email' => 'dirlantas_jabar@copilotproduction.local',
            'nama' => 'Dirlantas Polda Jabar',
            'induk_pemerintahan' => 'Polri',
            'pemerintahan_daerah' => 'Polda Jabar',
            'unit_kerja' => 'Pimpinan',
            'daerah' => $wilayahBundle['daerah'],
            'wilayah' => $wilayahBundle['wilayah'],
            'kategori_user' => 'organik',
        ]);

        $created += $this->upsertAkun($passwordHash, $roleId, [
            'username' => 'Gubernur_Jabar',
            'email' => 'gubernur_jabar@copilotproduction.local',
            'nama' => 'Gubernur Jawa Barat',
            'induk_pemerintahan' => 'Kemendagri',
            'pemerintahan_daerah' => 'Pemprop',
            'unit_kerja' => 'Pimpinan',
            'daerah' => $wilayahBundle['daerah'],
            'wilayah' => $wilayahBundle['wilayah'],
            'kategori_user' => 'organik',
        ]);

        $pwd = RoleDemoAccountsSeeder::DEMO_PASSWORD;
        $this->command?->info("JabarContohAkunSeeder: {$created} akun disimpan/diperbarui.");
        $this->command?->info("  Login: Dirlantas_Jabar / Gubernur_Jabar — password: {$pwd}");
        $this->command?->line('  Provinsi (daerah): '.$wilayahBundle['daerah'].($wilayahBundle['master_label'] ? ' (master: '.$wilayahBundle['master_label'].')' : ''));
        $this->command?->line('  Wilayah: '.$wilayahBundle['wilayah']);
    }

    private function seedKlMaster(\DateTimeInterface $now): void
    {
        $polriId = MasterKlHierarchyWriter::ensurePusat('Polri', $now);
        $poldaId = MasterKlHierarchyWriter::ensureDaerah($polriId, 'Polda Jabar', $now);
        MasterKlHierarchyWriter::ensureResort($poldaId, 'Ditlantas Polda Jabar', $now);
        MasterKlHierarchyWriter::ensureResort($poldaId, 'Pimpinan', $now);

        $kemendagriId = MasterKlHierarchyWriter::ensurePusat('Kemendagri', $now);
        $pempropId = MasterKlHierarchyWriter::ensureDaerah($kemendagriId, 'Pemprop', $now);
        MasterKlHierarchyWriter::ensureResort($pempropId, 'Pimpinan', $now);
    }

    /**
     * @return array{daerah: string, wilayah: string, master_label: ?string}
     */
    private function resolveProvinsiWilayahJabar(): array
    {
        $wilayah = 'Wil Jabar';

        if (Schema::hasTable('master_provinsi')) {
            $prov = DB::table('master_provinsi')
                ->whereRaw('LOWER(nama) LIKE ?', ['%jawa barat%'])
                ->orderBy('nama')
                ->value('nama');
            if ($prov !== null && trim((string) $prov) !== '') {
                return ['daerah' => (string) $prov, 'wilayah' => $wilayah, 'master_label' => (string) $prov];
            }
        }

        if (Schema::hasTable('daerah')) {
            $legacy = DB::table('daerah')
                ->whereRaw('LOWER(namadaerah) LIKE ?', ['%jawa barat%'])
                ->orderBy('namadaerah')
                ->value('namadaerah');
            if ($legacy !== null && trim((string) $legacy) !== '') {
                return ['daerah' => (string) $legacy, 'wilayah' => $wilayah, 'master_label' => (string) $legacy];
            }
        }

        return ['daerah' => 'Jabar', 'wilayah' => $wilayah, 'master_label' => null];
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
     * @param  array{username: string, email: string, nama: string, induk_pemerintahan: string, pemerintahan_daerah: string, unit_kerja: string, daerah: string, wilayah: string, kategori_user: string}  $fields
     */
    private function upsertAkun(string $passwordHash, int $roleId, array $fields): int
    {
        $row = array_merge([
            'password' => $passwordHash,
            'account_status' => 'active',
            'user_role_id' => $roleId,
            'notelp' => '',
            'fotoprof' => '',
            'nrp_nip_nik' => null,
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
