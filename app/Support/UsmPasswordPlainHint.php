<?php

namespace App\Support;

use Database\Seeders\RoleDemoAccountsSeeder;

/**
 * Password teks untuk ekspor / dokumentasi (hanya akun dengan kata sandi diketahui dari seeder).
 */
class UsmPasswordPlainHint
{
    /** @var array<string, string> */
    private const KNOWN_USERNAMES = [
        'superadmin' => RoleDemoAccountsSeeder::DEMO_PASSWORD,
        'admin' => 'admin123',
        'test' => 'test123',
        'wilayah' => 'wilayah123',
        'daerah' => 'daerah123',
        'nasional' => 'nasional123',
    ];

    public static function forUsername(string $username): string
    {
        if (isset(self::KNOWN_USERNAMES[$username])) {
            return self::KNOWN_USERNAMES[$username];
        }

        if (
            str_starts_with($username, 'sm_')
            || str_starts_with($username, 'mdemo_')
            || str_starts_with($username, 'demo_')
        ) {
            return RoleDemoAccountsSeeder::DEMO_PASSWORD;
        }

        return '';
    }
}
