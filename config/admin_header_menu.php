<?php

/**
 * Menu horizontal/topbar khusus admin wilayah (matrix role_id 2 Polda, 3 Polres).
 * IT Asset Management selaras executive/operator (inventaris TIK, bukan hanya patrol CCTV).
 *
 * @see App\View\Components\SuperadminHeaderNav
 * @see config/role_menu_matrix.php
 */

return [
    'excluded_superadmin_groups' => [
        'IT Asset Management',
    ],

    'groups' => [
        [
            'label' => 'IT Asset Management',
            'items' => [
                ['label' => 'Data CCTV', 'route' => 'datacctv.index', 'matrix_path' => 'data-cctv'],
                ['label' => 'Data Perangkat', 'route' => 'dataperangkat.index', 'matrix_path' => 'dataperangkat'],
                ['label' => 'TIK Trouble Report', 'route' => 'itsupport.index', 'matrix_path' => 'itsupport'],
            ],
        ],
    ],
];
