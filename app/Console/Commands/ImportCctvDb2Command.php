<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ImportCctvDb2Command extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cctv:import-db2';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Menggabungkan (import) data CCTV dari database satupeta (DB2) ke tabel datacctv utama (DB1).';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Membaca data dari database satupeta...');

        try {
            $cctvs = DB::connection('satupeta')->table('smpc_tbl_data_cctv')->get();
        } catch (\Exception $e) {
            $this->error('Gagal terhubung ke database satupeta atau tabel smpc_tbl_data_cctv tidak ditemukan.');
            return self::FAILURE;
        }

        if ($cctvs->isEmpty()) {
            $this->info('Tidak ada data CCTV di database satupeta.');
            return self::SUCCESS;
        }

        $tableWrite = \App\Http\Controllers\DataCctvController::baseTableForWrite();
        if (!$tableWrite) {
            $this->error('Tabel penulisan CCTV utama tidak ditemukan (datacctv / patrolicctv).');
            return self::FAILURE;
        }
        $pk = \App\Http\Controllers\DataCctvController::primaryKeyForTable($tableWrite);

        $insertedCount = 0;
        $skippedCount = 0;

        // Ambil admin superadmin atau user pertama untuk menjadi piket default jika tidak ada.
        $defaultPiket = DB::table('usm')->orderBy('id_usm')->value('id_usm') ?? 1;

        foreach ($cctvs as $cctv) {
            $koordinat = '';
            if (!empty($cctv->lat) && !empty($cctv->lng)) {
                $koordinat = trim($cctv->lat) . ',' . trim($cctv->lng);
            }

            // Cek apakah koordinat & nama_cctv sudah ada agar tidak duplikat
            $exists = DB::table($tableWrite)
                ->where('koordinat', $koordinat)
                ->where('lokasi', $cctv->nama_cctv)
                ->exists();

            if ($exists) {
                $skippedCount++;
                continue;
            }

            // Map data dari DB2 ke DB1
            $newId = (int) DB::table($tableWrite)->max($pk) + 1;
            
            // Tentukan owner berdasarkan instansi, atau channel_name jika mungkin (Korlantas / Dishub)
            $owner = 'Lainnya';
            if (!empty($cctv->instansi)) {
                $owner = ucfirst(trim($cctv->instansi));
            } else {
                $channelName = strtoupper((string) ($cctv->channel_id ?? ''));
                if (str_contains($channelName, 'KORLA') || str_contains($channelName, 'SMART')) {
                    $owner = 'Korlantas';
                } elseif (str_contains($channelName, 'DSHB')) {
                    $owner = 'Dishub';
                }
            }

            $payload = [
                $pk => $newId,
                'piket' => $defaultPiket,
                'owner' => $owner,
                'lokasi' => $cctv->nama_cctv ?? '-',
                'ipaddres' => $cctv->rtsp_cctv ?? '-',
                'brand' => 'Dahua', // Umumnya dari DB2
                'fungsicctv' => !empty($cctv->jenis_cctv) ? $cctv->jenis_cctv : 'Monitoring',
                'tanggal' => date('Y-m-d'),
                'koordinat' => $koordinat,
            ];

            // Masukkan fotofisik kosong jika ada kolomnya
            if (Schema::hasColumn($tableWrite, 'fotofisik')) {
                $payload['fotofisik'] = '';
            } elseif (Schema::hasColumn($tableWrite, 'fotosit')) {
                $payload['fotosit'] = '';
            }

            DB::table($tableWrite)->insert($payload);
            $insertedCount++;
        }

        $this->info("Import selesai! Berhasil insert: $insertedCount, Dilewati (duplikat): $skippedCount.");
        return self::SUCCESS;
    }
}
