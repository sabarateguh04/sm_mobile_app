<?php

/**
 * Sidebar SuperAdmin — grup & label selaras operator (Public Service, Patroli Online, Opsdal, Media Management).
 * Master referensi modul operator ada di sidebar; tabel inti (Giat/Peristiwa/Faktor/Penyebab) di header Set Puldown.
 *
 * @see App\Helpers\MenuHelper::getSuperadminSidebarItems()
 * @see config/operator_sidebar_menu.php
 */

return [
    'items' => [
        ['path' => 'home', 'label' => 'Home', 'icon' => 'fas fa-home'],
        ['path' => 'admin/users', 'label' => 'Personil Manager', 'icon' => 'fas fa-user-tie'],

        [
            'path' => 'group_config_sotk',
            'label' => 'Config SOTK',
            'icon' => 'fas fa-cog',
            'submenu' => [
                ['path' => 'master/daerah', 'label' => 'PROV/POLDA', 'icon' => 'fas fa-map'],
                ['path' => 'master/resort', 'label' => 'POLRES / POLRESTABES', 'icon' => 'fas fa-map-marked-alt'],
                ['path' => 'master/pusat', 'label' => 'Organisasi', 'icon' => 'fas fa-sitemap'],
                ['path' => 'master/kl-resort', 'label' => 'Sub Org', 'icon' => 'fas fa-network-wired'],
            ],
        ],

        [
            'path' => 'group_public_service',
            'label' => 'Public Service',
            'icon' => 'fas fa-clipboard',
            'submenu' => [
                ['path' => 'master/referensi', 'query' => ['ref' => 'katlayanan'], 'label' => 'Kategori Layanan Masy', 'icon' => 'fas fa-list-alt'],
                ['path' => 'master/referensi', 'query' => ['ref' => 'temalayanan'], 'label' => 'Tema Layanan Masy', 'icon' => 'fas fa-list-alt'],
                ['path' => 'master/referensi', 'query' => ['ref' => 'saluran'], 'label' => 'Saluran Layanan Masy', 'icon' => 'fas fa-list-alt'],
                ['path' => 'master/referensi', 'query' => ['ref' => 'katlayanandata'], 'label' => 'Kategori Layanan Data', 'icon' => 'fas fa-list-alt'],
            ],
        ],

        [
            'path' => 'group_patrol_online',
            'label' => 'Patroli Online',
            'icon' => 'fas fa-video',
            'submenu' => [
                ['path' => 'cctv-live', 'label' => 'Live CCTV', 'icon' => 'fas fa-tv'],
                ['path' => 'master/referensi', 'query' => ['ref' => 'situasilalin'], 'label' => 'Situasi Lalin', 'icon' => 'fas fa-list'],
                ['path' => 'master/referensi', 'query' => ['ref' => 'situasicuaca'], 'label' => 'Situasi Cuaca', 'icon' => 'fas fa-list'],
                ['path' => 'master/referensi', 'query' => ['ref' => 'gerbangkota'], 'label' => 'Gerbang Kota', 'icon' => 'fas fa-list'],
                ['path' => 'master/referensi', 'query' => ['ref' => 'araharus'], 'label' => 'Arah Arus', 'icon' => 'fas fa-list'],
                ['path' => 'master/referensi', 'query' => ['ref' => 'ownercctv'], 'label' => 'Owner CCTV', 'icon' => 'fas fa-list'],
            ],
        ],

        [
            'path' => 'group_opsdal',
            'label' => 'Opsdal',
            'icon' => 'fas fa-headphones',
            'submenu' => [
                ['path' => 'master/referensi', 'query' => ['ref' => 'jeniscontigency'], 'label' => 'Jenis Contigency', 'icon' => 'fas fa-list'],
                ['path' => 'master/referensi', 'query' => ['ref' => 'durasi'], 'label' => 'Durasi Perlambatan', 'icon' => 'fas fa-list'],
            ],
        ],

        [
            'path' => 'group_media',
            'label' => 'Media Management',
            'icon' => 'fas fa-newspaper',
            'submenu' => [
                ['path' => 'master/referensi', 'query' => ['ref' => 'kategorikonten'], 'label' => 'Kategori Konten Media', 'icon' => 'fas fa-list'],
                ['path' => 'master/referensi', 'query' => ['ref' => 'metodemedia'], 'label' => 'Metode Media', 'icon' => 'fas fa-list'],
                ['path' => 'master/referensi', 'query' => ['ref' => 'mediapublishing'], 'label' => 'Channel Publishing', 'icon' => 'fas fa-list'],
                ['path' => 'master/referensi', 'query' => ['ref' => 'sentimen'], 'label' => 'Sentimen Analytic', 'icon' => 'fas fa-list'],
            ],
        ],

        [
            'path' => 'group_data_collecting',
            'label' => 'Data Collecting',
            'icon' => 'fas fa-database',
            'submenu' => [
                ['path' => 'master/referensi', 'query' => ['ref' => 'katperangkat'], 'label' => 'Kategori Perangkat', 'icon' => 'fas fa-list'],
                ['path' => 'master/referensi', 'query' => ['ref' => 'katwisata'], 'label' => 'Kategori Wisata', 'icon' => 'fas fa-list'],
                ['path' => 'master/referensi', 'query' => ['ref' => 'kattempatibadah'], 'label' => 'Kategori Tempat Ibadah', 'icon' => 'fas fa-list'],
                ['path' => 'master/referensi', 'query' => ['ref' => 'katfaskes'], 'label' => 'Kategori Faskes', 'icon' => 'fas fa-list'],
                ['path' => 'master/referensi', 'query' => ['ref' => 'prasjal'], 'label' => 'Prasarana Jalan', 'icon' => 'fas fa-list'],
                ['path' => 'master/referensi', 'query' => ['ref' => 'jenisterminal'], 'label' => 'Jenis Terminal', 'icon' => 'fas fa-list'],
                ['path' => 'master/referensi', 'query' => ['ref' => 'jenisjalan'], 'label' => 'Jenis Jalan', 'icon' => 'fas fa-list'],
            ],
        ],

        [
            'path' => 'group_akses',
            'label' => 'Akses & Pengaturan',
            'icon' => 'fas fa-shield-alt',
            'submenu' => [
                ['path' => 'roles', 'label' => 'Roles', 'icon' => 'fas fa-user-lock'],
                ['path' => 'role_permissions', 'label' => 'Role Permissions', 'icon' => 'fas fa-key'],
                ['path' => 'admin/settings/logo', 'label' => 'Pengaturan Logo', 'icon' => 'fas fa-image'],
            ],
        ],
    ],
];
