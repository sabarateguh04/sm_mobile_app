<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

/**
 * Satu entri untuk menjalankan ulang seed akun demo + user inti (termasuk superadmin).
 *
 * Urutan: master teritori (mdemo_*) → NTB wilayah → Jabar/NTB legacy (demo_*) → admin/test/superadmin ({@see UsmSeeder}).
 */
class DemoUsersSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        $this->call([
            MasterTerritoryDemoAccountsSeeder::class,
            NtbWilayahDemoAccountsSeeder::class,
            RoleDemoAccountsSeeder::class,
            UsmSeeder::class,
        ]);
    }
}
