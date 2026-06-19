<?php

/**
 * Matriks Akses Role × Menu Path (sidebar + gate URL {@see MenuHelper::matrixAllowsPathForUser}).
 *
 * Kolom wilayah geografis pengguna (Pusat / Provinsi / Kab-Kota profil USM)
 * tidak di sini — lihat keys `territories` pada `config/menu.php` dan {@see MenuHelper::filterMenuByTerritory}.
 *
 * role_id 1–7:
 *   1 SuperAdmin / SA   2 AdminPolda   3 AdminPolres   4 Operator
 *   5 Executive3   6 Executive2   7 Executive1
 */

return [

    'super_admin_roles' => [1],

    /**
     * Mode mutasi data operasional per role (matriks `role_id`):
     * - full      : superadmin — semua data
     * - territory : admin — CRUD data di wilayah kerja
     * - own       : operator — tambah baru; ubah/hapus hanya baris `piket` = akun sendiri
     * - view      : executive & eksternal — hanya lihat
     */
    'role_mutation_modes' => [
        1 => 'full',
        2 => 'territory',
        3 => 'territory',
        4 => 'own',
        5 => 'view',
        6 => 'view',
        7 => 'view',
    ],

    /**
     * Executive (7 pusat, 6 provinsi, 5 kab/kota):
     * hanya list/view/export — mutasi diblokir {@see \App\Support\RoleMutationPolicy}.
     */
    'view_only_matrix_roles' => [5, 6, 7],

    /**
     * Operator (4): ubah/hapus hanya baris dengan `piket` = akun sendiri; modul personil = lihat saja.
     */
    'operator_read_only_in_territory' => true,

    /** Role matrix ID admin (2, 3) & oprator (4): wajib provinsi di profil; kab/kota opsional. */
    'territorial_staff_roles' => [2, 3, 4],

    /**
     * Executive (Smart Management): User Management scoped teritori (@see UserController).
     */
    'executive_matrix_roles' => [5, 6, 7],

    /** Executive pusat: cakupan nasional seperti admin pusat, hanya lihat (@see MenuHelper::isExecutiveNationalUser). */
    'executive_national_matrix_role_id' => 7,

    /** Executive provinsi (Polda): setara cakupan AdminPolda, hanya lihat (@see MenuHelper::isExecutiveProvinsiUser). */
    'executive_provinsi_matrix_role_id' => 6,

    /** Executive kab/kota (Polres): setara cakupan AdminPolres, hanya lihat (@see MenuHelper::isExecutiveKabKotaUser). */
    'executive_kab_kota_matrix_role_id' => 5,

    /**
     * Modul operasional tanpa `piket`: ubah/hapus hanya baris dengan `id_usm` = akun login
     * (mis. layanan masyarakat / tabel interaksi).
     */
    'account_owned_operational_tables' => ['layanan_masyarakat'],

    /**
     * Layanan masyarakat (interaksi): hanya pemilik baris (`id_usm`) — tanpa fallback wilayah.
     */
    'account_owned_legacy_territory_fallback' => [],

    'operator_matrix_role_id' => 4,

    /** user_role_id legacy sebelum migrasi 7 tier (11 = Posyan/operator) → tetap dianggap oprator. */
    'legacy_operator_user_role_ids' => [11],

    /*
     * menu_access[path]:
     *   'all'  = seluruh role terautentikasi memenuhi tingkat pertama (belum tentu lolos kontrol teritori).
     *   array  = matrix role_id yg boleh; path tidak ada = sembunyikan & tolak akses langsung URL.
     */
    'menu_access' => [
        /* Dashboard */
        'home' => 'all',

        /* Patrol & monitoring */
        'map'  => [1, 2, 3, 4, 5, 6, 7],
        'patrolicctv' => [1, 2, 3, 4, 5, 6, 7],
        /** Data CCTV inventaris TIK (operator: view wil_datacctv) */
        'data-cctv' => [1, 2, 3, 4, 5, 6, 7],
        /** Alias URL legacy /datacctv (sama halaman, tanpa redirect) */
        'datacctv' => [1, 2, 3, 4, 5, 6, 7],
        'dataperangkat' => [1, 2, 3, 4, 5, 6, 7],
        /** @deprecated Alias URL /cctv (redirect 301) */
        'cctv' => [1, 2, 3, 4, 5, 6, 7],

        /* Personil — operator & Executive 3 (view-only): Kuat Personil BO */
        'personil/manager' => [1, 2, 3, 4, 5],
        'admin/users' => [1, 2, 3, 5, 6, 7],

        /* Giat rutin & Quick Respons + Opsdal (turjagwali memakai query sm_lv → filter teritori pada menu path) */
        'turjagwali'   => [1, 2, 3, 4, 5, 6, 7],
        'contigency'   => [1, 2, 3, 4, 5, 6, 7],
        'trafficstuck' => [1, 2, 3, 4, 5, 6, 7],
        'report/traffic-analytic' => [1, 2, 3, 4, 5, 6, 7],
        'traffics' => [1, 2, 3, 4, 5, 6, 7],
        /** Alias path legacy (page_name DB: trafficdalkot) */
        'trafficdalkot' => [1, 2, 3, 4, 5, 6, 7],

        /* Data */
        'layanan/data'   => [4, 5, 6, 7],
        't_datajalan'    => [1, 2, 3, 4, 5, 6, 7],
        'jalan_gangguan' => [1, 2, 3, 4, 5, 6, 7],

        /* TIK — oprator: header IT Asset Management (Data Perangkat, CCTV, Trouble Report) */
        'itsupport'      => [1, 2, 3, 4, 5, 6, 7],
        'master/referensi' => [1, 2, 3, 4],

        /* Config — setara baris ✓ (SA): hanya matrix superadmin mengubah Roles/permissions */
        'roles'             => [1],
        'role_permissions' => [1],

        /* ------- Modul legacy / tautan langsung di luar sidebar matriks gambar ------- */
        'layanan/public-service' => [5, 6, 7],
        'layanan/masyarakat'     => [2, 3, 4, 5, 6, 7],
        'media/manage'           => [1, 2, 3, 4, 5, 6, 7],
        'media/analytic'         => [4, 5, 6, 7],

        'arusmudik'       => [1, 2, 3, 5, 6, 7],
        'wil/arusmudik'   => [1, 2, 3, 5, 6, 7],
        'da/arusmudik'    => [1, 2, 3, 5, 6, 7],
        'nas/arusmudik'   => [1, 2, 3, 5, 6, 7],

        'auditarus'   => [1, 2, 3, 5, 6, 7],
        'profilposko' => [1, 2, 3, 5, 6, 7],
        'ops_wanjol'  => [1, 2, 3, 5, 6, 7],

        'terminal'       => [1, 2, 3, 4, 5, 6, 7],
        'pnpangkutan'    => [1, 2, 3, 4, 5, 6, 7],
        'objekwisata'    => [1, 2, 3, 4, 5, 6, 7],
        'kunjunganwisata'=> [1, 2, 3, 4, 5, 6, 7],
        'tempatibadah'   => [1, 2, 3, 4, 5, 6, 7],
        'faskes'         => [1, 2, 3, 4, 5, 6, 7],

        'master/pusat'       => [1, 2, 3],
        'master/daerah'      => [1, 2, 3],
        'master/resort'      => [1, 2, 3],
        'master/kl-pusat'    => [1],
        'master/kl-daerah'   => [1],
        'master/kl-resort'   => [1],
        'master/kab-kota'    => [1, 2, 3],
        'master/unit-kerja'  => [1],
    ],

    'role_descriptions' => [
        1 => 'SuperAdmin / SA - Akses penuh konfigurasi & semua jalur sidebar.',
        2 => 'AdminPolda - Administrator CRUD data di lingkup polda/provinsi.',
        3 => 'AdminPolres - Administrator CRUD data di lingkup polres/kab-kota.',
        4 => 'Operator - Tambah data baru; ubah/hapus hanya data sendiri.',
        5 => 'Executive3 - Executive kab/kota (setara operator/Polres), sidebar & data sama operator, hanya lihat.',
        6 => 'Executive2 - Executive provinsi/Polda (setara AdminPolda), hanya melihat data sesuai polda/provinsi kerja.',
        7 => 'Executive1 - Executive pusat, hanya melihat data sesuai cakupan nasional.',
    ],
];
