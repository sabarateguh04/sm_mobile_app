<?php

/**
 * Registrasi mandiri (tabel `usm`).
 */
return [

    'enabled' => (bool) env('REGISTRATION_ENABLED', true),

    /** Harus ada di tabel `roles` (kolom `role_id` skema sbo). Default: 4 Operator. */
    'default_role_id' => (int) env('REGISTRATION_DEFAULT_ROLE_ID', 4),

    /**
     * Status awal akun setelah registrasi mandiri.
     * - pending (disarankan): tidak bisa login sampai administrator mengubah menjadi active di User Management.
     * - active: langsung bisa login (tanpa verifikasi administrator).
     * blocked: tidak dipakai untuk registrasi baru.
     */
    'default_account_status' => env('REGISTRATION_ACCOUNT_STATUS', 'pending'),

    /**
     * Setelah registrasi, kolom posyan.piket diisi id_usm pengguna baru
     * agar akun terhubung ke posko dan filter wilayah/daerah konsisten.
     */
    'assign_registered_user_as_posyan_piket' => (bool) env('REGISTRATION_ASSIGN_POSYAN_PIKET', true),

];
