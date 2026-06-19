<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class MasterPemerintahanUnitKerjaSeeder extends Seeder
{
    public function run(): void
    {
        if (! Schema::hasTable('master_pemerintahan_wilayah')) {
            return;
        }

        $now = now();

        $wilayah = [
            ['tingkat' => 'Provinsi', 'kode_wilayah' => '31', 'nama' => 'DKI Jakarta', 'wilayah_induk' => '—'],
            ['tingkat' => 'Kabupaten', 'kode_wilayah' => '3201', 'nama' => 'Kabupaten Bogor', 'wilayah_induk' => 'Jawa Barat'],
            ['tingkat' => 'Kota', 'kode_wilayah' => '3273', 'nama' => 'Kota Bandung', 'wilayah_induk' => 'Jawa Barat'],
            ['tingkat' => 'Kota', 'kode_wilayah' => '3578', 'nama' => 'Kota Surabaya', 'wilayah_induk' => 'Jawa Timur'],
            ['tingkat' => 'Kecamatan', 'kode_wilayah' => '3201010', 'nama' => 'Kecamatan Bogor Selatan', 'wilayah_induk' => 'Kab. Bogor'],
            ['tingkat' => 'Kelurahan', 'kode_wilayah' => '3201010005', 'nama' => 'Kelurahan Ciseureuh', 'wilayah_induk' => 'Kec. Bogor Selatan'],
            ['tingkat' => 'Provinsi', 'kode_wilayah' => '32', 'nama' => 'Jawa Barat', 'wilayah_induk' => '—'],
            ['tingkat' => 'Kabupaten', 'kode_wilayah' => '3510', 'nama' => 'Kabupaten Mojokerto', 'wilayah_induk' => 'Jawa Timur'],
            ['tingkat' => 'Kelurahan', 'kode_wilayah' => '3578020007', 'nama' => 'Kelurahan Gubeng', 'wilayah_induk' => 'Kec. Gubeng'],
            ['tingkat' => 'Kelurahan', 'kode_wilayah' => '3578020100', 'nama' => 'Kelurahan Tegalsari', 'wilayah_induk' => 'Kec. Tegalsari'],
            ['tingkat' => 'Kecamatan', 'kode_wilayah' => '3273010', 'nama' => 'Kecamatan Sumur Bandung', 'wilayah_induk' => 'Kota Bandung'],
            ['tingkat' => 'Kota', 'kode_wilayah' => '3171', 'nama' => 'Kota Jakarta Pusat', 'wilayah_induk' => 'DKI Jakarta'],
        ];

        foreach ($wilayah as &$row) {
            $row['created_at'] = $now;
            $row['updated_at'] = $now;
        }
        unset($row);

        DB::table('master_pemerintahan_wilayah')->upsert(
            $wilayah,
            ['kode_wilayah'],
            ['tingkat', 'nama', 'wilayah_induk', 'updated_at']
        );

        $instansi = [
            ['jenis' => 'Kementerian', 'singkatan' => 'Kemendagri', 'nama_unit' => 'Kementerian Dalam Negeri', 'lokasi' => 'Jakarta'],
            ['jenis' => 'Kementerian', 'singkatan' => 'Polri', 'nama_unit' => 'Kepolisian Negara RI', 'lokasi' => 'Jakarta'],
            ['jenis' => 'BKPD Pemda Provinsi', 'singkatan' => 'BKPSDM', 'nama_unit' => 'Badan Kepegawaian Pengembangan SDM Jawa Barat', 'lokasi' => 'Prov. Jawa Barat'],
            ['jenis' => 'Dinas Pemda Kota', 'singkatan' => 'Dispora', 'nama_unit' => 'Dinas Kepemudaan & Olahraga Kota Bandung', 'lokasi' => 'Kota Bandung'],
            ['jenis' => 'Dinas Pemda Kab.', 'singkatan' => 'Dispendukcapil', 'nama_unit' => 'Dinas Kependudukan & Pencatatan Sipil Kab. Bogor', 'lokasi' => 'Kab. Bogor'],
            ['jenis' => 'Lembaga Negeri', 'singkatan' => 'BNN', 'nama_unit' => 'Badan Narkotika Nasional RI', 'lokasi' => 'Jakarta'],
            ['jenis' => 'BKPD Kota', 'singkatan' => 'Bapperida', 'nama_unit' => 'Badan Perencanaan Pemda Kota Surabaya', 'lokasi' => 'Kota Surabaya'],
            ['jenis' => 'Satuan Kerja Daerah', 'singkatan' => 'Satpol PP', 'nama_unit' => 'Satuan Polisi Pamong Praja Surabaya', 'lokasi' => 'Kota Surabaya'],
            ['jenis' => 'Rum Sakit Daerah', 'singkatan' => 'RSD Graha', 'nama_unit' => 'RSUD Graha Pemda Kota Surabaya (dummy)', 'lokasi' => 'Kota Surabaya'],
        ];

        foreach ($instansi as &$row) {
            $row['created_at'] = $now;
            $row['updated_at'] = $now;
        }
        unset($row);

        DB::table('master_pemerintahan_instansi')->upsert(
            $instansi,
            ['singkatan', 'nama_unit'],
            ['jenis', 'lokasi', 'updated_at']
        );

        $units = [
            ['kode_unit_kerja' => 'UK-ADM-001', 'nama_unit_kerja' => 'Bagian Tata Usaha & Umum', 'instansi_placeholder' => 'Dinas XYZ Kota Bandung (dummy)', 'penanggung_jawab' => 'Ka. TU'],
            ['kode_unit_kerja' => 'UK-KES-013', 'nama_unit_kerja' => 'Bidang Keselamatan & Ketertiban', 'instansi_placeholder' => 'BKPD Kota Surabaya (dummy)', 'penanggung_jawab' => 'Kabid'],
            ['kode_unit_kerja' => 'UK-OPS-042', 'nama_unit_kerja' => 'Subbag Pengendali Operasional Lapangan', 'instansi_placeholder' => 'Dispendukcapil Kab. Bogor (dummy)', 'penanggung_jawab' => 'Koordinator Subbag'],
            ['kode_unit_kerja' => 'UK-ITP-022', 'nama_unit_kerja' => 'Tim Penyelia Infrastruktur Teknologi & Data', 'instansi_placeholder' => 'Badan Pusat Statistics (dummy)', 'penanggung_jawab' => 'Pjs. Pengelola Teknologi'],
            ['kode_unit_kerja' => 'UK-PRM-055', 'nama_unit_kerja' => 'Desk Humas dan Media Publik Internal', 'instansi_placeholder' => 'Kemendagri (dummy)', 'penanggung_jawab' => 'Desk Officer'],
            ['kode_unit_kerja' => 'UK-LOG-088', 'nama_unit_kerja' => 'Urusan Logistik & Anggaran', 'instansi_placeholder' => 'Dinas Pemuda & Olahraga (dummy)', 'penanggung_jawab' => 'Pemeriksa Materil'],
            ['kode_unit_kerja' => 'UK-LAY-033', 'nama_unit_kerja' => 'Satuan tugas satu pintu layanan publik harian', 'instansi_placeholder' => 'Kelurahan Ciseureuh (dummy)', 'penanggung_jawab' => 'Koordinator loket'],
            ['kode_unit_kerja' => 'UK-MTR-067', 'nama_unit_kerja' => 'Kelompok Jabatan Pengadministratif & Arsip', 'instansi_placeholder' => 'BKPSDM Jawa Barat (dummy)', 'penanggung_jawab' => 'Pengolah Arsip'],
        ];

        foreach ($units as &$row) {
            $row['created_at'] = $now;
            $row['updated_at'] = $now;
        }
        unset($row);

        DB::table('master_unit_kerja')->upsert(
            $units,
            ['kode_unit_kerja'],
            ['nama_unit_kerja', 'instansi_placeholder', 'penanggung_jawab', 'updated_at']
        );
    }
}
