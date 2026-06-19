<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class MigrateSmLamaData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'migrate:smlama';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Migrasi data dari database SM-LAMA ke database SM-BARU (SM-LITE)';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info("Memulai proses migrasi data di database lokal...");

        try {
            // Test Koneksi DB
            $this->info("Mengetes koneksi ke Database...");
            DB::connection()->getPdo();
            $this->info("Koneksi Database berhasil!");
        } catch (\Exception $e) {
            $this->error("Gagal terhubung ke Database. Pastikan konfigurasi di .env sudah benar.");
            $this->error($e->getMessage());
            return;
        }

        // ==========================================
        // CONTOH PROSES MIGRASI UNTUK MASTER DATA
        // ==========================================
        // $this->info("Migrasi Data Master Polda...");
        // // Membaca dari tabel lama (misal: 'tabel_lama_polda') di DB yang sama
        // $poldaLama = DB::table('tabel_lama_polda')->get();
        // foreach ($poldaLama as $data) {
        //     // Memasukkan ke tabel baru (misal: 'sm_md_mstr_polda') di DB yang sama
        //     DB::table('sm_md_mstr_polda')->updateOrInsert(
        //         ['id' => $data->id], // Kondisi pencarian/unik
        //         [
        //             'nama_polda' => $data->nama,
        //             // mapping kolom lainnya...
        //         ]
        //     );
        // }

        // ==========================================
        // CONTOH PROSES MIGRASI UNTUK DATA COLLECTING (JALAN)
        // ==========================================
        // $this->info("Migrasi Data Jalan...");
        // // Membaca dari tabel lama 't_datajalan'
        // $jalanLama = DB::table('t_datajalan')->get();
        // foreach ($jalanLama as $data) {
        //     // Memasukkan ke tabel baru 'sm_dc_tbl_data_jalan'
        //     DB::table('sm_dc_tbl_data_jalan')->updateOrInsert(
        //         ['id_datajalan' => $data->id_datajalan], // key unik
        //         [
        //             'namajalan' => $data->namajalan,
        //             'panjangjalan' => $data->panjangjalan,
        //             // mapping kolom lainnya...
        //         ]
        //     );
        // }

        $this->info("Proses migrasi selesai! Silakan sesuaikan nama tabel lama, baru, dan mapping kolomnya.");
    }
}
