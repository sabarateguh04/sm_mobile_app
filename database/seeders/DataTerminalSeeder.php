<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Seeder dummy untuk tabel `dataterminal` (database sbo).
 * Berisi data terminal/stasiun/bandara/pelabuhan di kota besar Indonesia,
 * koordinatnya valid agar bisa dipakai sebagai pin di peta.
 */
class DataTerminalSeeder extends Seeder
{
    public function run(): void
    {
        if (!DB::getSchemaBuilder()->hasTable('dataterminal')) {
            $this->command->warn('Tabel `dataterminal` tidak ditemukan.');
            return;
        }

        $piketId = (string) (DB::table('usm')->value('id_usm') ?? 1);
        $tanggal = now()->format('Y-m-d');

        $terminals = [
            ['Terminal BUS AKAP', 'Terminal Pulo Gebang', 'Jl. Pulo Gebang, Jakarta Timur', '021-12345', 'Pengelola Jakarta', 'Faskes,Jalur Evakuasi,Ruang Ibu Menyusui', '-6.184,106.952'],
            ['Terminal BUS AKAP', 'Terminal Kampung Rambutan', 'Jl. TB Simatupang, Jakarta Timur', '021-67890', 'Pengelola Kp Rambutan', 'Faskes,Jalur Evakuasi', '-6.318,106.866'],
            ['Terminal BUS AKAP', 'Terminal Kalideres', 'Jl. Daan Mogot, Jakarta Barat', '021-11122', 'Pengelola Kalideres', 'Faskes,Lahan Parkir', '-6.148,106.713'],
            ['Terminal BUS AKAP', 'Terminal Arjosari', 'Jl. Raden Intan, Malang', '0341-11111', 'Pengelola Arjosari', 'Faskes,Tempat Bermain Anak', '-7.929,112.659'],
            ['Terminal BUS AKAP', 'Terminal Tirtonadi', 'Jl. Ahmad Yani, Surakarta', '0271-22222', 'Pengelola Tirtonadi', 'Faskes,Jaringan Internet', '-7.546,110.812'],
            ['Terminal BUS AKAP', 'Terminal Purabaya (Bungurasih)', 'Jl. Letjen Sutoyo, Sidoarjo', '031-33333', 'Pengelola Bungurasih', 'Faskes,Lahan Parkir,Jaringan Internet', '-7.353,112.730'],
            ['Terminal BUS AKAP', 'Terminal Leuwipanjang', 'Jl. Soekarno Hatta, Bandung', '022-44444', 'Pengelola Leuwipanjang', 'Faskes,Tempat Bermain Anak,Lahan Parkir', '-6.937,107.582'],
            ['Terminal BUS AKAP', 'Terminal Cicaheum', 'Jl. AH Nasution, Bandung', '022-55566', 'Pengelola Cicaheum', 'Faskes,Lahan Parkir', '-6.906,107.658'],
            ['Terminal BUS AKAP', 'Terminal Giwangan', 'Jl. Imogiri Timur, Yogyakarta', '0274-44455', 'Pengelola Giwangan', 'Faskes,Ruang Ibu Menyusui', '-7.836,110.380'],
            ['Terminal BUS AKAP', 'Terminal Tipar', 'Jl. Raya Tipar, Cilacap', '0282-77788', 'Pengelola Tipar', 'Faskes,Lahan Parkir', '-7.726,109.014'],
            ['Stasiun Kereta', 'Stasiun Gambir', 'Jl. Medan Merdeka Timur, Jakarta Pusat', '021-55555', 'Petugas Stasiun', 'Faskes,Ruang Ibu Menyusui,Jaringan Internet', '-6.177,106.831'],
            ['Stasiun Kereta', 'Stasiun Pasar Senen', 'Jl. Stasiun Senen, Jakarta Pusat', '021-66666', 'Petugas Senen', 'Faskes,Lahan Parkir', '-6.176,106.842'],
            ['Stasiun Kereta', 'Stasiun Tugu Yogyakarta', 'Jl. Margo Utomo, Yogyakarta', '0274-77777', 'Petugas Tugu', 'Faskes,Ruang Ibu Menyusui', '-7.789,110.363'],
            ['Stasiun Kereta', 'Stasiun Balapan', 'Jl. Wolter Monginsidi, Surakarta', '0271-88888', 'Petugas Balapan', 'Faskes,Jaringan Internet', '-7.553,110.821'],
            ['Stasiun Kereta', 'Stasiun Gubeng', 'Jl. Stasiun Gubeng, Surabaya', '031-99999', 'Petugas Gubeng', 'Faskes,Lahan Parkir', '-7.265,112.752'],
            ['Stasiun Kereta', 'Stasiun Malang', 'Jl. Trunojoyo, Malang', '0341-12321', 'Petugas Malang', 'Faskes,Ruang Ibu Menyusui', '-7.978,112.638'],
            ['Stasiun Kereta', 'Stasiun Bandung', 'Jl. Stasiun Timur, Bandung', '022-33344', 'Petugas Bandung', 'Faskes,Jaringan Internet', '-6.915,107.602'],
            ['Stasiun Kereta', 'Stasiun Cirebon', 'Jl. Siliwangi, Cirebon', '0231-22244', 'Petugas Cirebon', 'Faskes,Lahan Parkir', '-6.706,108.557'],
            ['Bandar Udara', 'Bandara Soekarno-Hatta', 'Tangerang, Banten', '021-10101', 'Petugas Bandara', 'Faskes,Ruang Ibu Menyusui,Jaringan Internet,Lahan Parkir', '-6.125,106.656'],
            ['Bandar Udara', 'Bandara Halim Perdanakusuma', 'Jakarta Timur', '021-20202', 'Petugas Halim', 'Faskes,Lahan Parkir', '-6.266,106.890'],
            ['Bandar Udara', 'Bandara Adisutjipto', 'Yogyakarta', '0274-30303', 'Petugas Adisutjipto', 'Faskes,Ruang Ibu Menyusui', '-7.788,110.431'],
            ['Bandar Udara', 'Bandara YIA Kulon Progo', 'Kulon Progo, Yogyakarta', '0274-30404', 'Petugas YIA', 'Faskes,Jaringan Internet', '-7.901,110.057'],
            ['Bandar Udara', 'Bandara Juanda', 'Sidoarjo', '031-40404', 'Petugas Juanda', 'Faskes,Lahan Parkir,Jaringan Internet', '-7.380,112.787'],
            ['Bandar Udara', 'Bandara Husein Sastranegara', 'Bandung', '022-50505', 'Petugas Husein', 'Faskes,Ruang Ibu Menyusui', '-6.901,107.575'],
            ['Bandar Udara', 'Bandara Abdul Rahman Saleh', 'Malang', '0341-60606', 'Petugas ARS', 'Faskes,Lahan Parkir', '-7.927,112.711'],
            ['Bandar Udara', 'Bandara Adi Sumarmo', 'Surakarta', '0271-71717', 'Petugas Adi Sumarmo', 'Faskes,Jaringan Internet', '-7.516,110.751'],
            ['Pelabuhan ASDP', 'Pelabuhan Merak', 'Cilegon, Banten', '0254-70707', 'Petugas Merak', 'Faskes,Jalur Evakuasi', '-5.929,106.001'],
            ['Pelabuhan ASDP', 'Pelabuhan Bakauheni', 'Lampung Selatan', '0721-80808', 'Petugas Bakauheni', 'Faskes,Lahan Parkir', '-5.870,105.752'],
            ['Pelabuhan ASDP', 'Pelabuhan Tanjung Perak', 'Surabaya', '031-90909', 'Petugas Tj Perak', 'Faskes,Jaringan Internet', '-7.205,112.736'],
            ['Pelabuhan ASDP', 'Pelabuhan Tanjung Priok', 'Jakarta Utara', '021-12222', 'Petugas Tj Priok', 'Faskes,Lahan Parkir', '-6.105,106.876'],
            ['Pelabuhan ASDP', 'Pelabuhan Gilimanuk', 'Jembrana, Bali', '0365-12121', 'Petugas Gilimanuk', 'Faskes,Jalur Evakuasi', '-8.158,114.438'],
            ['Pelabuhan ASDP', 'Pelabuhan Ketapang', 'Banyuwangi', '0333-13131', 'Petugas Ketapang', 'Faskes,Lahan Parkir', '-8.140,114.422'],
        ];

        $rows = [];
        foreach ($terminals as [$jenis, $nama, $jalan, $cp, $namecp, $fasilitas, $koord]) {
            $rows[] = [
                'piket' => $piketId,
                'jenisterminal' => $jenis,
                'namaterminal' => $nama,
                'cp' => $cp,
                'fasilitas' => $fasilitas,
                'jalan' => $jalan,
                'namecp' => $namecp,
                'koordinat' => $koord,
                'fotoobject' => '',
                'tanggal' => $tanggal,
            ];
        }
        DB::table('dataterminal')->insert($rows);
        $this->command->info('Data terminal dummy: ' . count($rows) . ' baris ditambah.');
    }
}
