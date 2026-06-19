<?php

/**
 * Sidebar admin wilayah (AdminPolda matrix 2, AdminPolres matrix 3).
 * Struktur & urutan selaras {@see config/executive_sidebar_menu.php}, ditambah Kuat Personil BO + Data Collecting.
 * Setiap daun wajib `territories` untuk filter Pusat × Provinsi × Kab/Kota (@see MenuHelper::filterMenuByTerritory).
 *
 * @see config/role_menu_matrix.php
 */

return [
    'items' => [
        ['path' => 'home', 'label' => 'Home', 'icon' => 'fas fa-home', 'territories' => ['nasional', 'provinsi', 'kab_kota']],
        ['path' => 'personil/manager', 'label' => 'Kuat Personil BO', 'icon' => 'fas fa-users', 'territories' => ['nasional', 'provinsi', 'kab_kota']],

        [
            'path' => 'group_patrol_online',
            'label' => 'Patroli Online',
            'icon' => 'fas fa-video',
            'submenu' => [
                ['path' => 'patrolicctv', 'label' => 'Monitoring CCTV', 'icon' => 'fas fa-camera', 'territories' => ['nasional', 'provinsi', 'kab_kota']],
                ['path' => 'report/traffic-analytic', 'index_route' => 'report.traffic-analytic.index', 'matrix_path' => 'trafficstuck', 'label' => 'Report Traffic Analytic', 'icon' => 'fas fa-chart-line', 'territories' => ['nasional', 'provinsi', 'kab_kota']],
            ],
        ],
        [
            'path' => 'group_giat_rutin',
            'label' => 'Giat Rutin',
            'icon' => 'fas fa-calendar-check',
            'submenu' => [
                ['path' => 'turjagwali', 'query' => ['sm_lv' => 'giat_daerah'], 'label' => 'Turjagwali', 'icon' => 'fas fa-calendar-day', 'territories' => ['nasional', 'provinsi', 'kab_kota']],
                ['path' => 'turjagwali', 'query' => ['sm_lv' => 'giat_nasional'], 'label' => 'Kodal Turjagwali', 'icon' => 'fas fa-taxi', 'territories' => ['nasional']],
                ['path' => 'pnpangkutan', 'label' => 'Report PNP Angkutan', 'icon' => 'fas fa-train', 'territories' => ['nasional', 'provinsi', 'kab_kota']],
            ],
        ],
        [
            'path' => 'group_opsdal',
            'label' => 'Opsdal',
            'icon' => 'fas fa-headphones',
            'submenu' => [
                ['path' => 'turjagwali', 'query' => ['sm_lv' => 'opsdal'], 'label' => 'Kodal Turjagwali', 'icon' => 'fas fa-map-marker-alt', 'territories' => ['nasional', 'provinsi', 'kab_kota']],
                ['path' => 'contigency', 'query' => ['sb_grp' => 'opsdal'], 'label' => 'Kodal Contigency', 'icon' => 'fas fa-ambulance', 'territories' => ['nasional', 'provinsi', 'kab_kota']],
                ['path' => 'trafficstuck', 'query' => ['sb_grp' => 'opsdal'], 'label' => 'Kodal Perlambatan', 'icon' => 'fas fa-gauge', 'territories' => ['nasional', 'provinsi', 'kab_kota']],
            ],
        ],
        [
            'path' => 'group_quick_respons',
            'label' => 'Quick Respons',
            'icon' => 'fas fa-bolt',
            'submenu' => [
                ['path' => 'contigency', 'query' => ['sb_grp' => 'quick_respons'], 'label' => 'QR & Kodal — Kontijensi', 'icon' => 'fas fa-ambulance', 'territories' => ['nasional', 'provinsi', 'kab_kota']],
                ['path' => 'trafficstuck', 'query' => ['sb_grp' => 'quick_respons'], 'label' => 'QR & Kodal — Perlambatan', 'icon' => 'fas fa-gauge', 'territories' => ['nasional', 'provinsi', 'kab_kota']],
            ],
        ],
        [
            'path' => 'group_public_service',
            'label' => 'Public Service',
            'icon' => 'fas fa-hands-helping',
            'submenu' => [
                ['path' => 'layanan/masyarakat', 'label' => 'Layanan Masy', 'icon' => 'fas fa-people-carry', 'territories' => ['kab_kota']],
                ['path' => 'layanan/masyarakat', 'query' => ['sb_grp' => 'public_service'], 'label' => 'Report Layanan Masy', 'icon' => 'fas fa-chart-bar', 'territories' => ['nasional', 'provinsi']],
            ],
        ],
        [
            'path' => 'group_media',
            'label' => 'Media Management',
            'icon' => 'fas fa-newspaper',
            'submenu' => [
                ['path' => 'media/manage', 'label' => 'Media Publishing', 'icon' => 'fas fa-cloud-upload-alt', 'territories' => ['nasional', 'provinsi', 'kab_kota']],
                ['path' => 'media/analytic', 'label' => 'Media Analytic', 'icon' => 'fas fa-cogs', 'index_route' => 'media.analytic.index', 'territories' => ['nasional', 'provinsi', 'kab_kota']],
            ],
        ],
        [
            'path' => 'group_data_collecting',
            'label' => 'Data Collecting',
            'icon' => 'fas fa-database',
            'submenu' => [
                ['path' => 'terminal', 'label' => 'Data Terminal', 'icon' => 'fas fa-bus', 'territories' => ['nasional', 'provinsi', 'kab_kota']],
                ['path' => 'objekwisata', 'label' => 'Object Wisata', 'icon' => 'fas fa-camera-retro', 'territories' => ['nasional', 'provinsi', 'kab_kota']],
                ['path' => 'tempatibadah', 'label' => 'Tempat Ibadah', 'icon' => 'fas fa-place-of-worship', 'territories' => ['nasional', 'provinsi', 'kab_kota']],
                ['path' => 'faskes', 'label' => 'Data Faskes', 'icon' => 'fas fa-hospital', 'territories' => ['nasional', 'provinsi', 'kab_kota']],
                ['path' => 't_datajalan', 'label' => 'Data Jalan', 'icon' => 'fas fa-road', 'territories' => ['nasional', 'provinsi', 'kab_kota']],
                ['path' => 'jalan_gangguan', 'label' => 'Data Gangguan', 'icon' => 'fas fa-exclamation-triangle', 'territories' => ['nasional', 'provinsi', 'kab_kota']],
                ['path' => 'pnpangkutan', 'label' => 'Pnp Angkutan', 'icon' => 'fas fa-train', 'territories' => ['nasional', 'provinsi', 'kab_kota']],
                ['path' => 'kunjunganwisata', 'label' => 'Pengunjung Wisata', 'icon' => 'fas fa-users', 'territories' => ['nasional', 'provinsi', 'kab_kota']],
            ],
        ],

        ['path' => 'admin/users', 'label' => 'Personil Manager', 'icon' => 'fas fa-user-tie', 'territories' => ['nasional', 'provinsi', 'kab_kota']],
    ],
];
