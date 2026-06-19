<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;

/**
 * Akun demo untuk **setiap** kab/kota di provinsi NTB (master `kode_provinsi` 52),
 * mengikuti pola {@see MasterTerritoryDemoAccountsSeeder}: admin, oprator, eks daerah, eks resort per wilayah.
 *
 * Dijalankan setelah {@see IndonesiaMasterDataSeeder}. Idempotent (`updateOrCreate` per username).
 * Password sama dengan {@see RoleDemoAccountsSeeder::DEMO_PASSWORD}.
 */
class NtbWilayahDemoAccountsSeeder extends MasterTerritoryDemoAccountsSeeder
{
    /** Kode BPS provinsi NTB */
    public const KODE_PROVINSI_NTB = '52';

    public function run(): void
    {
        if (! Schema::hasTable('roles') || ! Schema::hasTable('usm')) {
            $this->command?->warn('NtbWilayahDemoAccountsSeeder: tabel roles atau usm tidak ada — dilewati.');

            return;
        }

        if (! Schema::hasTable('master_provinsi') || ! Schema::hasTable('master_kabupaten_kota')) {
            $this->command?->warn('NtbWilayahDemoAccountsSeeder: master provinsi/kab-kota tidak ada — jalankan IndonesiaMasterDataSeeder.');

            return;
        }

        $prov = DB::table('master_provinsi')->where('kode_provinsi', self::KODE_PROVINSI_NTB)->first();

        if ($prov === null) {
            $prov = DB::table('master_provinsi')
                ->whereRaw('LOWER(nama) LIKE ?', ['%nusa tenggara barat%'])
                ->orderBy('nama')
                ->first();
        }

        if ($prov === null) {
            $this->command?->warn('NtbWilayahDemoAccountsSeeder: provinsi NTB tidak ditemukan di master_provinsi.');

            return;
        }

        $kabList = DB::table('master_kabupaten_kota')
            ->where('master_provinsi_id', $prov->id)
            ->orderBy('nama')
            ->get(['nama', 'kode_kabupaten_kota']);

        if ($kabList->isEmpty()) {
            $this->command?->warn('NtbWilayahDemoAccountsSeeder: tidak ada kab/kota untuk provinsi «'.($prov->nama ?? 'NTB').'».');

            return;
        }

        $namaProv = (string) $prov->nama;
        $kabRows = $kabList->map(fn ($k) => (object) [
            'nama_provinsi' => $namaProv,
            'nama_kabupaten_kota' => (string) $k->nama,
            'kode_kabupaten_kota' => (string) $k->kode_kabupaten_kota,
        ])->all();

        $meta = $this->rolesPrimaryKey();
        $pk = $meta['pk'];
        $prop = $meta['valueProp'];
        $passwordPlain = RoleDemoAccountsSeeder::DEMO_PASSWORD;
        $password = Hash::make($passwordPlain);

        $n = $this->seedUsmRowsForKabupatenBatch($kabRows, $password, $pk, $prop);

        $this->command?->info(sprintf(
            'NtbWilayahDemoAccountsSeeder: %d akun wilayah NTB (%d kab/kota × 4 role). Provinsi: «%s». Password: %s',
            $n,
            count($kabRows),
            $namaProv,
            $passwordPlain
        ));
    }
}
