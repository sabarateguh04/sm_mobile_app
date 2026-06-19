<?php

/**
 * Menu horizontal/topbar khusus executive (matrix role_id 5 kab/kota, 6 provinsi, 7 pusat) — view-only.
 * Selaras {@see config/operator_header_menu.php}.
 *
 * @see App\View\Components\SuperadminHeaderNav
 * @see config/role_menu_matrix.php (menu_access untuk gate URL)
 */

return [
    'excluded_groups' => [],

    'groups' => [
        [
            'label' => 'IT Asset Management',
            'items' => [
                ['label' => 'Data CCTV', 'route' => 'datacctv.index', 'matrix_path' => 'data-cctv'],
                ['label' => 'Data Perangkat', 'route' => 'dataperangkat.index', 'matrix_path' => 'dataperangkat'],
                ['label' => 'Report Trouble', 'route' => 'itsupport.index', 'matrix_path' => 'itsupport'],
            ],
        ],
        [
            'label' => 'Data Collecting',
            'items' => [
                ['label' => 'Data Terminal', 'route' => 'terminal.index', 'matrix_path' => 'terminal'],
                ['label' => 'Object Wisata', 'route' => 'objekwisata.index', 'matrix_path' => 'objekwisata'],
                ['label' => 'Tempat Ibadah', 'route' => 'tempatibadah.index', 'matrix_path' => 'tempatibadah'],
                ['label' => 'Data Faskes', 'route' => 'faskes.index', 'matrix_path' => 'faskes'],
                ['label' => 'Data Jalan', 'route' => 't_datajalan.index', 'matrix_path' => 't_datajalan'],
                ['label' => 'Data Gangguan', 'route' => 'jalan_gangguan.index', 'matrix_path' => 'jalan_gangguan'],
                ['label' => 'Pnp Angkutan', 'route' => 'pnpangkutan.index', 'matrix_path' => 'pnpangkutan'],
                ['label' => 'Pengunjung Wisata', 'route' => 'kunjunganwisata.index', 'matrix_path' => 'kunjunganwisata'],
            ],
        ],
    ],
];
