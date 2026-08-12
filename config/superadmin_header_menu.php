<?php

/**
 * Menu horizontal/topbar khusus SuperAdmin (legacy SBO).
 * Grup: IT Asset Management Nas, Data Collecting Nas, Config SOTK, Set Puldown.
 *
 * @see App\View\Components\SuperadminHeaderNav
 */
return [
    'groups' => [
//        [
//            'label' => 'IT Asset Management Nas',
//            'items' => [
//                ['label' => 'Data CCTV Nasional', 'route' => 'datacctv.index', 'matrix_path' => 'data-cctv'],
//            ],
//        ],
//        [
//            'label' => 'Data Collecting Nas',
//            'items' => [
//                ['label' => 'Data Terminal', 'route' => 'terminal.index', 'matrix_path' => 'terminal'],
//                ['label' => 'Object Wisata', 'route' => 'objekwisata.index', 'matrix_path' => 'objekwisata'],
//                ['label' => 'Tempat Ibadah', 'route' => 'tempatibadah.index', 'matrix_path' => 'tempatibadah'],
//                ['label' => 'Data Faskes', 'route' => 'faskes.index', 'matrix_path' => 'faskes'],
//                ['label' => 'Data Jalan', 'route' => 't_datajalan.index', 'matrix_path' => 't_datajalan'],
//                ['label' => 'Data Gangguan', 'route' => 'jalan_gangguan.index', 'matrix_path' => 'jalan_gangguan'],
//                ['label' => 'Pnp Angkutan', 'route' => 'pnpangkutan.index', 'matrix_path' => 'pnpangkutan'],
//                ['label' => 'Pengunjung Wisata', 'route' => 'kunjunganwisata.index', 'matrix_path' => 'kunjunganwisata'],
//                ['label' => 'Master Jenis Jalan', 'route' => 'master.referensi.index', 'query' => ['ref' => 'jenisjalan']],
//                ['label' => 'Master Prasarana Jalan', 'route' => 'master.referensi.index', 'query' => ['ref' => 'prasjal']],
//            ],
//        ],
        [
            'label' => 'Config SOTK',
            'items' => [
                ['label' => 'Polda', 'route' => 'master.daerah.index'],
                ['label' => 'Polres / Polrestabes', 'route' => 'master.resort.index', 'matrix_path' => 'master/resort'],
                ['label' => 'Instansi', 'route' => 'master.pemerintahan-instansi.index'],
                ['label' => 'Organisasi', 'route' => 'master.pusat.index', 'level' => 1],
                ['label' => 'Sub Org', 'route' => 'master.kl-resort.index', 'level' => 2],
                ['label' => 'Unit Kerja', 'route' => 'master.unit-kerja.index', 'level' => 3],
            ],
        ],
        [
            'label' => 'Set Puldown',
            'items' => [
                ['label' => 'Tabel Giat', 'route' => 'master.referensi.index', 'query' => ['ref' => 'giat']],
                ['label' => 'Tabel Peristiwa', 'route' => 'master.referensi.index', 'query' => ['ref' => 'peristiwa']],
                ['label' => 'Tabel Faktor', 'route' => 'master.referensi.index', 'query' => ['ref' => 'faktor']],
                ['label' => 'Tabel Penyebab', 'route' => 'master.referensi.index', 'query' => ['ref' => 'penyebab']],
            ],
        ],
    ],
];
