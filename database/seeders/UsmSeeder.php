<?php

namespace Database\Seeders;

use App\Models\Usm;
use App\Support\LegacyUsmDbInput;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;

/**
 * User inti untuk lokal: superadmin, admin, test, serta contoh level wilayah/daerah/nasional.
 * `user_role_id` = nilai matriks {@see \App\Models\Usm} / `roles.role_id` (1 = superadmin, 2 = admin, …).
 */
class UsmSeeder extends Seeder
{
    public function run(): void
    {
        if (! Schema::hasTable('usm') || ! Schema::hasTable('roles')) {
            $this->command?->warn('UsmSeeder: tabel usm atau roles tidak ada.');

            return;
        }

        $passwordHash = Hash::make(RoleDemoAccountsSeeder::DEMO_PASSWORD);

        $roleAdmin = 2;
        $roleSuper = 1;

        if (Schema::hasColumn('roles', 'role_id')) {
            if (! DB::table('roles')->where('role_id', $roleSuper)->exists()) {
                $this->command?->warn('UsmSeeder: role_id 1 (superadmin) tidak ada di `roles` — jalankan migrasi role terlebih dahulu.');

                return;
            }
        }

        $base = [
            'notelp' => '',
            'fotoprof' => '',
        ];
        if (Schema::hasColumn('usm', 'email_status')) {
            $base['email_status'] = '';
        }

        $mk = function (array $extra) use ($base): array {
            return LegacyUsmDbInput::normalizeOptionalTextFields(array_merge($base, $extra));
        };

        Usm::firstOrCreate(
            ['username' => 'superadmin'],
            $mk([
                'email' => 'superadmin@smartbackoffice.com',
                'password' => $passwordHash,
                'nama' => 'Super Administrator',
                'account_status' => 'active',
                'user_role_id' => $roleSuper,
                'daerah' => '',
                'wilayah' => '',
            ])
        );

        Usm::firstOrCreate(
            ['username' => 'admin'],
            $mk([
                'email' => 'admin@smartbackoffice.com',
                'password' => Hash::make('admin123'),
                'nama' => 'Administrator',
                'account_status' => 'active',
                'user_role_id' => $roleAdmin,
                'daerah' => '',
                'wilayah' => '',
            ])
        );

        Usm::firstOrCreate(
            ['username' => 'test'],
            $mk([
                'email' => 'test@smartbackoffice.com',
                'password' => Hash::make('test123'),
                'nama' => 'Test User',
                'account_status' => 'active',
                'user_role_id' => $roleAdmin,
                'daerah' => '',
                'wilayah' => '',
            ])
        );

        Usm::firstOrCreate(
            ['username' => 'wilayah'],
            $mk([
                'email' => 'wilayah@smartbackoffice.com',
                'password' => Hash::make('wilayah123'),
                'nama' => 'User Wilayah',
                'account_status' => 'active',
                'user_role_id' => $roleAdmin,
                'wilayah' => 'WILAYAH_01',
                'daerah' => '',
            ])
        );

        Usm::firstOrCreate(
            ['username' => 'daerah'],
            $mk([
                'email' => 'daerah@smartbackoffice.com',
                'password' => Hash::make('daerah123'),
                'nama' => 'User Daerah',
                'account_status' => 'active',
                'user_role_id' => $roleAdmin,
                'daerah' => 'DAERAH_01',
                'wilayah' => '',
            ])
        );

        Usm::firstOrCreate(
            ['username' => 'nasional'],
            $mk([
                'email' => 'nasional@smartbackoffice.com',
                'password' => Hash::make('nasional123'),
                'nama' => 'User Nasional',
                'account_status' => 'active',
                'user_role_id' => $roleAdmin,
                'daerah' => '',
                'wilayah' => '',
            ])
        );

        $this->command?->info('UsmSeeder: user inti disimpan (firstOrCreate).');
        $this->command?->info('  superadmin / demo: username=superadmin — password='.RoleDemoAccountsSeeder::DEMO_PASSWORD);
        $this->command?->info('  admin: username=admin — password=admin123');
        $this->command?->info('  test:  username=test  — password=test123');
        $this->command?->info('  Lihat juga: demo_superadmin, mdemo_superadmin ('.RoleDemoAccountsSeeder::DEMO_PASSWORD.') dari seeder demo.');
    }
}
