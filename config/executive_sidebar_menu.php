<?php

/**
 * Sidebar khusus untuk Executive 2/1 (Provinsi / Pusat).
 * Hanya menampilkan modul spesifik: Home, Kuat Personil, Public Service, Patroli Online, Opsdal, Management Media.
 *
 * @see App\Helpers\MenuHelper::getExecutiveSidebarItems()
 */

return [
    'items' => [
        ['path' => 'home', 'label' => 'Home', 'icon' => 'fas fa-home', 'territories' => ['nasional', 'provinsi', 'kab_kota']],
        ['path' => 'personil/manager', 'label' => 'Kuat Personil', 'icon' => 'fas fa-users', 'territories' => ['nasional', 'provinsi', 'kab_kota']],

        [
            'path' => 'group_public_service',
            'label' => 'Public Service',
            'icon' => 'fas fa-hands-helping',
            'submenu' => [
                ['path' => 'layanan/masyarakat', 'label' => 'Layanan Masy', 'icon' => 'fas fa-people-carry', 'territories' => ['kab_kota']],
                ['path' => 'layanan/masyarakat', 'query' => ['sb_grp' => 'public_service'], 'label' => 'Report Layanan Masyarakat', 'icon' => 'fas fa-chart-bar', 'territories' => ['nasional', 'provinsi']],
                ['path' => 'layanan/data', 'label' => 'Layanan Data', 'icon' => 'fas fa-table', 'territories' => ['nasional', 'provinsi', 'kab_kota']],
            ],
        ],
        [
            'path' => 'group_patrol_online',
            'label' => 'Patroli Online',
            'icon' => 'fas fa-video',
            'submenu' => [
                ['path' => 'patrolicctv', 'label' => 'Monitoring CCTV', 'icon' => 'fas fa-camera', 'territories' => ['nasional', 'provinsi', 'kab_kota']],
                ['path' => 'cctv-live', 'label' => 'Live CCTV', 'icon' => 'fas fa-tv', 'territories' => ['nasional', 'provinsi', 'kab_kota']],
                ['path' => 'report/traffic-analytic', 'index_route' => 'report.traffic-analytic.index', 'matrix_path' => 'trafficstuck', 'label' => 'Report Traffic Analytic', 'icon' => 'fas fa-chart-line', 'territories' => ['nasional', 'provinsi', 'kab_kota']],
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
                ['path' => 'opsdal/cad', 'index_route' => 'opsdal.cad.index', 'label' => 'Report CAD Dispatch', 'icon' => 'fas fa-headset', 'territories' => ['nasional', 'provinsi', 'kab_kota']],
            ],
        ],
        [
            'path' => 'group_media',
            'label' => 'Management Media',
            'icon' => 'fas fa-newspaper',
            'submenu' => [
                ['path' => 'media/manage', 'label' => 'Media Publishing', 'icon' => 'fas fa-cloud-upload-alt', 'territories' => ['nasional', 'provinsi', 'kab_kota']],
                ['path' => 'media/analytic', 'label' => 'Media Analytic', 'icon' => 'fas fa-cogs', 'index_route' => 'media.analytic.index', 'territories' => ['nasional', 'provinsi', 'kab_kota']],
            ],
        ],
    ],
];
