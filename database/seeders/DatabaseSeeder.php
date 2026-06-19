<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Skenario QA Smart Management (operator + executive Jabar/Jatim):
        // php artisan db:seed --class=SmartManagementScenarioSeeder

        $this->call([
            IndonesiaMasterDataSeeder::class,
            MasterKlHierarchySeeder::class,
            PoldaPolresExcelSeeder::class,
            DemoUsersSeeder::class,
            DummyDataSeeder::class,
            MasterPemerintahanUnitKerjaSeeder::class,
        ]);
    }
}
