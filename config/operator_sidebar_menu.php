<?php

/**
 * Sidebar khusus role operator (matrix role_id 4) — selaras menu legacy SBO.
 *
 * @see App\Helpers\MenuHelper::getMenuItems()
 * @see config/role_menu_matrix.php (menu_access untuk gate URL)
 */

return [
    'items' => [
        ['path' => 'home', 'label' => 'Home', 'icon' => 'fas fa-home', 'territories' => ['nasional', 'provinsi', 'kab_kota']],
        ['path' => 'personil/manager', 'label' => 'Kuat Personil BO', 'icon' => 'fas fa-users', 'territories' => ['nasional', 'provinsi', 'kab_kota']],

        [
            'path' => 'group_public_service',
            'label' => 'Public Service',
            'icon' => 'fas fa-clipboard',
            'submenu' => [
                ['path' => 'layanan/masyarakat', 'label' => 'Layanan Masy', 'icon' => 'fab fa-whatsapp', 'territories' => ['nasional', 'provinsi', 'kab_kota']],
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
    ],
];
