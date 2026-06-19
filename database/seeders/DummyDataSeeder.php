<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class DummyDataSeeder extends Seeder
{
    /**
     * Seed dummy data untuk dashboard: profilposko, arusmudik, turjagwali,
     * auditarus, pnpangkutan, contigency, trafficstuck.
     * Jalankan setelah UsmSeeder (php artisan db:seed --class=UsmSeeder atau php artisan db:seed).
     */
    public function run(): void
    {
        $piketId = DB::table('usm')->value('id_usm');
        if (!$piketId) {
            $this->command->warn('Tabel usm kosong. Jalankan dulu: php artisan db:seed --class=UsmSeeder');
            return;
        }

        $wilayahs = ['WILAYAH_01', 'WILAYAH_02'];
        $daerahs = ['DAERAH_01', 'DAERAH_02'];
        $now = now();
        $profilIds = [];

        // 1. Profil Posko (agar stat & chart ada data)
        $poskoNames = [
            'Posko Utama Terminal A',
            'Posko Terminal B',
            'Posko Stasiun C',
            'Posko Pelabuhan D',
            'Posko Bandara E',
            'Posko Wilayah 2 A',
            'Posko Wilayah 2 B',
        ];
        foreach ($poskoNames as $i => $name) {
            $wilayah = $wilayahs[$i % 2];
            $daerah = $daerahs[$i % 2];
            DB::table('profilposko')->insert([
                'piket' => $piketId,
                'katops' => 'Mudik',
                'jenisops' => 'Posyan',
                'tahunops' => (string) $now->year,
                'temaops' => 'Mudik Aman',
                'namaposko' => $name,
                'lokasiposko' => "Lokasi {$name}",
                'sarpras' => 'Tenda, Kursi, Meja, P3K',
                'fotoposko' => null,
                'alamat' => "Alamat {$name}",
                'wilayah' => $wilayah,
                'daerah' => $daerah,
                'keterangan' => 'Posko dummy',
                'created_at' => $now,
                'updated_at' => $now,
            ]);
            $profilIds[] = (int) DB::getPdo()->lastInsertId();
        }
        $this->command->info('Profil posko dummy: ' . count($profilIds) . ' baris.');

        // 2. Arus Mudik (beberapa tanggal: hari ini, bulan ini, bulan lalu)
        $tanggalArus = [
            $now->copy()->subDays(2)->format('Y-m-d'),
            $now->copy()->subDay()->format('Y-m-d'),
            $now->format('Y-m-d'),
            $now->copy()->subDays(5)->format('Y-m-d'),
            $now->copy()->subDays(10)->format('Y-m-d'),
            $now->copy()->subDays(15)->format('Y-m-d'),
        ];
        foreach ($profilIds as $pid) {
            foreach ($tanggalArus as $tgl) {
                DB::table('arusmudik')->insert([
                    'id_profileposko' => $pid,
                    'tanggal' => $tgl,
                    'piket' => $piketId,
                    'statarus' => ['lancar', 'padat', 'macet'][array_rand(['lancar', 'padat', 'macet'])],
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }
        }
        $this->command->info('Arus mudik dummy: ' . (count($profilIds) * count($tanggalArus)) . ' baris.');

        // 3. Turjagwali
        if (DB::getSchemaBuilder()->hasTable('turjagwali')) {
            foreach ($profilIds as $pid) {
                foreach (array_slice($tanggalArus, 0, 4) as $tgl) {
                    DB::table('turjagwali')->insert([
                        'id_profileposko' => $pid,
                        'tanggal' => $tgl,
                        'piket' => $piketId,
                        'status' => 'selesai',
                        'keterangan' => 'Patroli rutin',
                        'created_at' => $now,
                        'updated_at' => $now,
                    ]);
                }
            }
            $this->command->info('Turjagwali dummy: dibuat.');
        }

        // 4. Auditarus
        if (DB::getSchemaBuilder()->hasTable('auditarus')) {
            foreach ($profilIds as $pid) {
                foreach (array_slice($tanggalArus, 0, 3) as $tgl) {
                    DB::table('auditarus')->insert([
                        'id_profileposko' => $pid,
                        'tanggal' => $tgl,
                        'piket' => $piketId,
                        'hasil_audit' => 'Kondisi arus sesuai standar',
                        'catatan' => 'Audit dummy',
                        'created_at' => $now,
                        'updated_at' => $now,
                    ]);
                }
            }
            $this->command->info('Auditarus dummy: dibuat.');
        }

        // 5. Pnpangkutan (jumlah penumpang)
        if (DB::getSchemaBuilder()->hasTable('pnpangkutan')) {
            $jenis = ['Bus', 'Kereta', 'Mobil Pribadi', 'Kapal'];
            foreach ($profilIds as $pid) {
                foreach ($tanggalArus as $tgl) {
                    DB::table('pnpangkutan')->insert([
                        'id_profileposko' => $pid,
                        'tanggal' => $tgl,
                        'piket' => $piketId,
                        'jenis_angkutan' => $jenis[array_rand($jenis)],
                        'jumlah' => (string) rand(50, 500),
                        'keterangan' => null,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ]);
                }
            }
            $this->command->info('Pnpangkutan dummy: dibuat.');
        }

        // 6. Contigency
        if (DB::getSchemaBuilder()->hasTable('contigency')) {
            foreach ($profilIds as $pid) {
                foreach (array_slice($tanggalArus, 0, 2) as $tgl) {
                    $row = [
                        'id_profileposko' => $pid,
                        'tanggal' => $tgl,
                        'piket' => $piketId,
                        'jenis_peristiwa' => 'Lalu lintas',
                        'lokasi' => 'Ruas tol km 10',
                        'uraian' => 'Insiden minor, sudah tertangani',
                        'status' => 'selesai',
                        'created_at' => $now,
                        'updated_at' => $now,
                    ];
                    if (Schema::hasColumn('contigency', 'koordinat')) {
                        $row['koordinat'] = '-6.2,106.816666'; // contoh Jakarta
                    }
                    DB::table('contigency')->insert($row);
                }
            }
            $this->command->info('Contigency dummy: dibuat.');
        }

        // 7. Trafficstuck
        if (DB::getSchemaBuilder()->hasTable('trafficstuck')) {
            foreach ($profilIds as $pid) {
                foreach (array_slice($tanggalArus, 0, 3) as $tgl) {
                    DB::table('trafficstuck')->insert([
                        'id_profileposko' => $pid,
                        'tanggal' => $tgl,
                        'piket' => $piketId,
                        'lokasi' => 'Pintu keluar tol',
                        'penyebab' => 'Volume kendaraan tinggi',
                        'uraian' => 'Perlambatan 15 menit',
                        'status' => 'teratasi',
                        'created_at' => $now,
                        'updated_at' => $now,
                    ]);
                }
            }
            $this->command->info('Trafficstuck dummy: dibuat.');
        }

        // 8. Terminal (pakai tabel `dataterminal` dari sbo) - kota besar Indonesia
        if (DB::getSchemaBuilder()->hasTable('dataterminal')) {
            $terminals = [
                ['Terminal BUS AKAP', 'Terminal Pulo Gebang', 'Jl. Pulo Gebang, Jakarta Timur', '021-12345', 'Pengelola Jakarta', 'Faskes,Jalur Evakuasi,Ruang Ibu Menyusui', '-6.184,106.952'],
                ['Terminal BUS AKAP', 'Terminal Kampung Rambutan', 'Jl. TB Simatupang, Jakarta Timur', '021-67890', 'Pengelola Kp Rambutan', 'Faskes,Jalur Evakuasi', '-6.318,106.866'],
                ['Terminal BUS AKAP', 'Terminal Arjosari', 'Jl. Raden Intan, Malang', '0341-11111', 'Pengelola Arjosari', 'Faskes,Tempat Bermain Anak', '-7.929,112.659'],
                ['Terminal BUS AKAP', 'Terminal Tirtonadi', 'Jl. Ahmad Yani, Surakarta', '0271-22222', 'Pengelola Tirtonadi', 'Faskes,Jaringan Internet', '-7.546,110.812'],
                ['Terminal BUS AKAP', 'Terminal Purabaya (Bungurasih)', 'Jl. Letjen Sutoyo, Sidoarjo', '031-33333', 'Pengelola Bungurasih', 'Faskes,Lahan Parkir,Jaringan Internet', '-7.353,112.730'],
                ['Terminal BUS AKAP', 'Terminal Leuwipanjang', 'Jl. Soekarno Hatta, Bandung', '022-44444', 'Pengelola Leuwipanjang', 'Faskes,Tempat Bermain Anak,Lahan Parkir', '-6.937,107.582'],
                ['Stasiun Kereta', 'Stasiun Gambir', 'Jl. Medan Merdeka Timur, Jakarta Pusat', '021-55555', 'Petugas Stasiun', 'Faskes,Ruang Ibu Menyusui,Jaringan Internet', '-6.177,106.831'],
                ['Stasiun Kereta', 'Stasiun Pasar Senen', 'Jl. Stasiun Senen, Jakarta Pusat', '021-66666', 'Petugas Senen', 'Faskes,Lahan Parkir', '-6.176,106.842'],
                ['Stasiun Kereta', 'Stasiun Tugu Yogyakarta', 'Jl. Margo Utomo, Yogyakarta', '0274-77777', 'Petugas Tugu', 'Faskes,Ruang Ibu Menyusui', '-7.789,110.363'],
                ['Stasiun Kereta', 'Stasiun Balapan', 'Jl. Wolter Monginsidi, Surakarta', '0271-88888', 'Petugas Balapan', 'Faskes,Jaringan Internet', '-7.553,110.821'],
                ['Stasiun Kereta', 'Stasiun Gubeng', 'Jl. Stasiun Gubeng, Surabaya', '031-99999', 'Petugas Gubeng', 'Faskes,Lahan Parkir', '-7.265,112.752'],
                ['Bandar Udara', 'Bandara Soekarno-Hatta', 'Tangerang, Banten', '021-10101', 'Petugas Bandara', 'Faskes,Ruang Ibu Menyusui,Jaringan Internet,Lahan Parkir', '-6.125,106.656'],
                ['Bandar Udara', 'Bandara Halim Perdanakusuma', 'Jakarta Timur', '021-20202', 'Petugas Halim', 'Faskes,Lahan Parkir', '-6.266,106.890'],
                ['Bandar Udara', 'Bandara Adisutjipto', 'Yogyakarta', '0274-30303', 'Petugas Adisutjipto', 'Faskes,Ruang Ibu Menyusui', '-7.788,110.431'],
                ['Bandar Udara', 'Bandara Juanda', 'Sidoarjo', '031-40404', 'Petugas Juanda', 'Faskes,Lahan Parkir,Jaringan Internet', '-7.380,112.787'],
                ['Bandar Udara', 'Bandara Husein Sastranegara', 'Bandung', '022-50505', 'Petugas Husein', 'Faskes,Ruang Ibu Menyusui', '-6.901,107.575'],
                ['Bandar Udara', 'Bandara Abdul Rahman Saleh', 'Malang', '0341-60606', 'Petugas ARS', 'Faskes,Lahan Parkir', '-7.927,112.711'],
                ['Pelabuhan ASDP', 'Pelabuhan Merak', 'Cilegon, Banten', '0254-70707', 'Petugas Merak', 'Faskes,Jalur Evakuasi', '-5.929,106.001'],
                ['Pelabuhan ASDP', 'Pelabuhan Bakauheni', 'Lampung Selatan', '0721-80808', 'Petugas Bakauheni', 'Faskes,Lahan Parkir', '-5.870,105.752'],
                ['Pelabuhan ASDP', 'Pelabuhan Tanjung Perak', 'Surabaya', '031-90909', 'Petugas Tj Perak', 'Faskes,Jaringan Internet', '-7.205,112.736'],
                ['Pelabuhan ASDP', 'Pelabuhan Gilimanuk', 'Jembrana, Bali', '0365-12121', 'Petugas Gilimanuk', 'Faskes,Jalur Evakuasi', '-8.158,114.438'],
                ['Pelabuhan ASDP', 'Pelabuhan Ketapang', 'Banyuwangi', '0333-13131', 'Petugas Ketapang', 'Faskes,Lahan Parkir', '-8.140,114.422'],
            ];

            $rows = [];
            foreach ($terminals as [$jenis, $nama, $jalan, $cp, $namecp, $fasilitas, $koord]) {
                $rows[] = [
                    'piket' => (string) $piketId,
                    'jenisterminal' => $jenis,
                    'namaterminal' => $nama,
                    'cp' => $cp,
                    'fasilitas' => $fasilitas,
                    'jalan' => $jalan,
                    'namecp' => $namecp,
                    'koordinat' => $koord,
                    'fotoobject' => '',
                    'tanggal' => $now->format('Y-m-d'),
                ];
            }
            DB::table('dataterminal')->insert($rows);
            $this->command->info('Dataterminal dummy: ' . count($rows) . ' baris.');
        }

        // 9. Data Gangguan (pakai tabel `datagangguan` dari sbo)
        if (DB::getSchemaBuilder()->hasTable('datagangguan')) {
            DB::table('datagangguan')->insert([
                ['tanggal' => $now->format('Y-m-d'), 'piket' => (string) $piketId, 'fungsijalan' => 'Tol', 'namajalan' => 'Tol Demo Km 10', 'kerawanan' => 'Black Spot', 'faktor' => 'Kendaraan', 'penyebab' => 'Mogok Mesin', 'levelgangguan' => 'Gangguan Nyata (GN)', 'teamsurvayor' => 'Tim Demo', 'fotolokasi' => '', 'koordinat' => '-7.684,110.500'],
                ['tanggal' => $now->format('Y-m-d'), 'piket' => (string) $piketId, 'fungsijalan' => 'Arteri', 'namajalan' => 'Jl. Demo Perbaikan', 'kerawanan' => 'Rawan Perlambatan', 'faktor' => 'Jalan', 'penyebab' => 'Perbaikan', 'levelgangguan' => 'Potensi Gangguan (PG)', 'teamsurvayor' => 'Tim Demo', 'fotolokasi' => '', 'koordinat' => '-7.676,110.512'],
            ]);
            $this->command->info('Datagangguan dummy: dibuat.');
        }

        $this->command->info('Data dummy selesai. Login dengan user wilayah (WILAYAH_01) atau daerah (DAERAH_01) untuk melihat filter.');
    }
}
