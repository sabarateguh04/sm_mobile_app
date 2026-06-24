<?php

/**
 * Pemetaan key referensi operasional → tabel smd_mstr_* di DB kedua.
 *
 * @see App\Support\SmMasterData
 * @see App\Support\ReferensiOptions
 */

return [
    'giat' => ['table' => 'smd_mstr_jenis_kegiatan', 'column' => 'jenis_kegiatan'],
    'jeniscontigency' => ['table' => 'smd_mstr_jenis_bencana', 'column' => 'jenis_bencana'],
    'faktor' => ['table' => 'smd_mstr_kerawanan', 'column' => 'kerawanan'],
    'peristiwa' => ['table' => 'smd_mstr_kejadian', 'column' => 'kejadian'],
    'durasi' => ['table' => 'smd_mstr_panjang_antrian', 'column' => 'panjang_antrian'],
    'situasilalin' => ['table' => 'smd_mstr_situasi_lalin', 'column' => 'situasi_lalin'],
    'situasicuaca' => ['table' => 'smd_mstr_kondisi_cuaca', 'column' => 'kondisi_cuaca'],
    'saluran' => ['table' => 'smd_mstr_saluran_informasi', 'column' => 'saluran'],
    'katlayanan' => ['table' => 'smd_mstr_kategori', 'column' => 'kategori'],
    'prasjal' => ['table' => 'smd_mstr_prasjal', 'column' => 'prasjal'],
    'penyebab' => ['table' => 'smd_mstr_penyebab_kemacetan', 'column' => 'penyebab_kemacetan'],
    'jenisjalan' => ['table' => 'smd_mstr_fungsi_jalan', 'column' => 'fungsi_jalan'],
    'fasilitas' => ['table' => 'smd_mstr_fasilitas', 'column' => 'fasilitas'],
    'katwisata' => ['table' => 'smd_mstr_jenis_wisata', 'column' => 'jenis_wisata'],
    'katperangkat' => ['table' => 'smd_mstr_kategori_device', 'column' => 'kategori_device'],
];
