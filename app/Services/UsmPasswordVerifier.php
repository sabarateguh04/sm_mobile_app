<?php

namespace App\Services;

use App\Models\Usm;
use Illuminate\Support\Facades\Hash;

/**
 * Verifikasi password tabel `usm`: bcrypt (Laravel) + legacy MD5 hex (sbo lama).
 */
class UsmPasswordVerifier
{
    public static function verify(Usm $user, string $plain): bool
    {
        $stored = (string) ($user->password ?? '');
        if ($stored === '') {
            return false;
        }

        if (self::isBcryptLike($stored)) {
            return Hash::check($plain, $stored);
        }

        if (strlen($stored) === 32 && ctype_xdigit($stored)) {
            return hash_equals(strtolower($stored), md5($plain));
        }

        return false;
    }

    /**
     * Setelah login sukses, upgrade hash legacy ke bcrypt.
     */
    public static function upgradeHashIfNeeded(Usm $user, string $plain): void
    {
        $stored = (string) ($user->password ?? '');
        if ($stored === '' || self::isBcryptLike($stored)) {
            return;
        }

        $user->forceFill(['password' => Hash::make($plain)])->saveQuietly();
    }

    private static function isBcryptLike(string $hash): bool
    {
        return str_starts_with($hash, '$2y$')
            || str_starts_with($hash, '$2a$')
            || str_starts_with($hash, '$2b$');
    }
}
