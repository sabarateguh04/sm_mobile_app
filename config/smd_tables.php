<?php

/**
 * Registry tabel DB kedua (`mysql_second` / test.sql).
 *
 * Master  — smd_mstr_* (referensi dropdown & browse).
 * Transaksi — smd_tbl_* (data operasional SM yang ditampilkan di program).
 */

return [

    'master' => [
        'smd_mstr_bs_ts' => ['label' => 'Black Spot / Trouble Spot', 'column' => 'bs_ts'],
        'smd_mstr_cara_bertindak' => ['label' => 'Cara Bertindak', 'column' => 'cara_bertindak'],
        'smd_mstr_cctv_category' => ['label' => 'Kategori CCTV', 'column' => 'category'],
        'smd_mstr_dasar_kegiatan' => ['label' => 'Dasar Kegiatan', 'column' => 'dasar_giat'],
        'smd_mstr_fasilitas' => ['label' => 'Fasilitas', 'column' => 'fasilitas'],
        'smd_mstr_fungsi_jalan' => ['label' => 'Fungsi Jalan', 'column' => 'fungsi_jalan'],
        'smd_mstr_jenis_bencana' => ['label' => 'Jenis Bencana', 'column' => 'jenis_bencana'],
        'smd_mstr_jenis_berita' => ['label' => 'Jenis Berita', 'column' => 'jenis_berita'],
        'smd_mstr_jenis_event' => ['label' => 'Jenis Event', 'column' => 'jenis_event'],
        'smd_mstr_jenis_galery' => ['label' => 'Jenis Galeri', 'column' => 'jenis_galery'],
        'smd_mstr_jenis_kegiatan' => ['label' => 'Jenis Kegiatan', 'column' => 'jenis_kegiatan'],
        'smd_mstr_jenis_kendaraan' => ['label' => 'Jenis Kendaraan', 'column' => 'jenis_kendaraan'],
        'smd_mstr_jenis_laka' => ['label' => 'Jenis Laka', 'column' => 'jenis_laka'],
        'smd_mstr_jenis_layanan' => ['label' => 'Jenis Layanan', 'column' => 'jenis_layanan'],
        'smd_mstr_jenis_pengemudi' => ['label' => 'Jenis Pengemudi', 'column' => 'jenis_pengemudi'],
        'smd_mstr_jenis_project' => ['label' => 'Jenis Project', 'column' => 'jenis_project'],
        'smd_mstr_jenis_service' => ['label' => 'Jenis Service', 'column' => 'jenis_service'],
        'smd_mstr_jenis_wisata' => ['label' => 'Jenis Wisata', 'column' => 'jenis_wisata'],
        'smd_mstr_kategori' => ['label' => 'Kategori', 'column' => 'kategori'],
        'smd_mstr_kategori_aplikasi' => ['label' => 'Kategori Aplikasi', 'column' => 'kategori_aplikasi'],
        'smd_mstr_kategori_device' => ['label' => 'Kategori Device', 'column' => 'kategori_device'],
        'smd_mstr_kategori_kegiatan' => ['label' => 'Kategori Kegiatan', 'column' => 'kategori_kegiatan'],
        'smd_mstr_kategori_pelapor' => ['label' => 'Kategori Pelapor', 'column' => 'kategori_pelapor'],
        'smd_mstr_kebutuhan_peralatan' => ['label' => 'Kebutuhan Peralatan', 'column' => 'kebutuhan_peralatan'],
        'smd_mstr_kejadian' => ['label' => 'Kejadian', 'column' => 'kejadian'],
        'smd_mstr_kejadian_opsdal' => ['label' => 'Kejadian Opsdal', 'column' => 'kejadian_opsdal'],
        'smd_mstr_kejadian_termonitor' => ['label' => 'Kejadian Termonitor', 'column' => 'kejadian_termonitor'],
        'smd_mstr_kelompok_sasaran' => ['label' => 'Kelompok Sasaran', 'column' => 'kelompok_sasaran'],
        'smd_mstr_kerawanan' => ['label' => 'Kerawanan', 'column' => 'kerawanan'],
        'smd_mstr_kerusakan_object' => ['label' => 'Kerusakan Object', 'column' => 'kerusakan_object'],
        'smd_mstr_kondisi_cuaca' => ['label' => 'Kondisi Cuaca', 'column' => 'kondisi_cuaca'],
        'smd_mstr_kuat_personil' => ['label' => 'Kuat Personil', 'column' => 'kuat_personil'],
        'smd_mstr_layanan_darurat' => ['label' => 'Layanan Darurat', 'column' => 'layanan_darurat'],
        'smd_mstr_manage_route' => ['label' => 'Manage Route', 'column' => 'manage_route'],
        'smd_mstr_media' => ['label' => 'Media', 'column' => 'media'],
        'smd_mstr_object_kawal' => ['label' => 'Object Kawal', 'column' => 'object_kawal'],
        'smd_mstr_panjang_antrian' => ['label' => 'Panjang Antrian', 'column' => 'panjang_antrian'],
        'smd_mstr_pelanggaran' => ['label' => 'Pelanggaran', 'column' => 'pelanggaran'],
        'smd_mstr_penyebab_kemacetan' => ['label' => 'Penyebab Kemacetan', 'column' => 'penyebab_kemacetan'],
        'smd_mstr_petugas' => ['label' => 'Petugas', 'column' => 'nama_petugas'],
        'smd_mstr_pola_kejadian' => ['label' => 'Pola Kejadian', 'column' => 'pola_kejadian'],
        'smd_mstr_potensi_gangguan' => ['label' => 'Potensi Gangguan', 'column' => 'potensi_gangguan'],
        'smd_mstr_prasjal' => ['label' => 'Prasarana Jalan', 'column' => 'prasjal'],
        'smd_mstr_program_kerja' => ['label' => 'Program Kerja', 'column' => 'program_kerja'],
        'smd_mstr_saluran_informasi' => ['label' => 'Saluran Informasi', 'column' => 'saluran'],
        'smd_mstr_sasaran_kegiatan' => ['label' => 'Sasaran Kegiatan', 'column' => 'sasaran_giat'],
        'smd_mstr_situasi_lalin' => ['label' => 'Situasi Lalin', 'column' => 'situasi_lalin'],
        'smd_mstr_smart_ops' => ['label' => 'Smart Ops', 'column' => 'smart_ops'],
        'smd_mstr_status_device' => ['label' => 'Status Device', 'column' => 'status_device'],
        'smd_mstr_status_jalan' => ['label' => 'Status Jalan', 'column' => 'status_jalan'],
        'smd_mstr_status_pesan' => ['label' => 'Status Pesan', 'column' => 'status_pesan'],
        'smd_mstr_teguran' => ['label' => 'Teguran', 'column' => 'teguran'],
    ],

    'dacol' => [
        'sm_dacol_black_trouble_spot' => [
            'label'       => 'Black & Trouble Spot',
            'pk'          => 'id',
            'date_column' => 'created_at',
            'search'      => ['namalokasi', 'polda', 'polres', 'bs_ts', 'status'],
        ],
        'sm_dacol_fasilitas_umum' => [
            'label'       => 'Fasilitas Umum',
            'pk'          => 'id',
            'date_column' => 'created_at',
            'search'      => ['nama', 'polda', 'polres', 'fasilitas', 'kategori_fasilitas'],
        ],
        'sm_dacol_integrasi_data' => [
            'label'       => 'Integrasi Data',
            'pk'          => 'id',
            'date_column' => 'tanggal',
            'search'      => ['jenis_data', 'polda', 'polres', 'kategori', 'sub_kategori'],
        ],
        'sm_dacol_objek_vital' => [
            'label'       => 'Objek Vital',
            'pk'          => 'id',
            'date_column' => 'created_at',
            'search'      => ['nama', 'polda', 'polres', 'jenis_objek_vital', 'status'],
        ],
        'sm_dacol_penduduk' => [
            'label'       => 'Data Penduduk',
            'pk'          => 'id',
            'date_column' => 'created_at',
            'search'      => ['polda', 'polres', 'tahun'],
        ],
        'sm_dacol_pengemudi' => [
            'label'       => 'Data Pengemudi',
            'pk'          => 'id',
            'date_column' => 'created_at',
            'search'      => ['nama', 'nik', 'polda', 'polres', 'jenis_pengemudi'],
        ],
        'sm_dacol_prasjal' => [
            'label'       => 'Prasarana Jalan',
            'pk'          => 'id',
            'date_column' => 'created_at',
            'search'      => ['nama', 'polda', 'polres', 'prasjal', 'status'],
        ],
        'sm_dacol_rengiat' => [
            'label'       => 'Rencana Kegiatan',
            'pk'          => 'id',
            'date_column' => 'tanggal',
            'search'      => ['polda', 'polres', 'jenis_kegiatan', 'kategori'],
        ],
        'sm_dacol_wilayah_rawan' => [
            'label'       => 'Wilayah Rawan',
            'pk'          => 'id',
            'date_column' => 'created_at',
            'search'      => ['namalokasi', 'polda', 'polres', 'kerawanan', 'status'],
        ],
    ],

    'transaksi' => [
        'smd_tbl_data_irsms' => [
            'label' => 'Data IRSMS (Laka Lantas)',
            'date_column' => 'tanggal_kejadian',
            'search' => ['no_laka', 'polda', 'polres', 'tingkat_kecelakaan', 'nama_jalan'],
            'display' => ['no_laka', 'polda', 'polres', 'tanggal_kejadian', 'tingkat_kecelakaan', 'jumlah_md', 'jumlah_lb', 'jumlah_lr'],
        ],
        'smd_tbl_data_detail_irsms' => [
            'label' => 'Detail IRSMS',
            'date_column' => 'ctddate',
            'search' => ['no_laka', 'nama', 'nik', 'jenis_kendaraan'],
            'display' => ['no_laka', 'nama', 'nik', 'jenis_korban', 'jenis_kendaraan', 'usia', 'ctddate'],
        ],
        'smd_tbl_data_integrasi' => [
            'label' => 'Data Integrasi',
            'date_column' => 'tanggal',
            'search' => ['jenis_data', 'polda', 'polres', 'kategori', 'sub_kategori'],
            'display' => ['tanggal', 'polda', 'polres', 'jenis_data', 'kategori', 'sub_kategori', 'jumlah'],
        ],
        'smd_tbl_data_traffic_counting' => [
            'label' => 'Traffic Counting',
            'date_column' => 'tanggal',
            'search' => ['polda', 'polres', 'nama_cctv', 'jenis_cctv', 'category'],
            'display' => ['tanggal', 'jam', 'polda', 'polres', 'nama_cctv', 'motor', 'minibus', 'bus', 'truck', 'total'],
        ],
        'smd_tbl_data_traffic_counting_history' => [
            'label' => 'Traffic Counting History',
            'date_column' => 'tanggal',
            'search' => ['polda', 'polres', 'nama_cctv'],
            'display' => ['tanggal', 'jam', 'polda', 'polres', 'nama_cctv', 'total'],
        ],
    ],

];
