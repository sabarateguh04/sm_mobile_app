<?php

namespace App\Support;

use App\Models\Usm;

/**
 * Level geografis profil USM (selaras {@see \App\Helpers\HierarchicalHelper::getUserLevel()}).
 *
 * Kolom DB tetap `daerah` = provinsi, `wilayah` = kab/kota.
 */
class TerritoryLevelForUsm
{
    public const NASIONAL = 'nasional';

    public const PROVINSI = 'provinsi';

    public const KAB_KOTA = 'kab_kota';

    /** @deprecated Gunakan {@see PROVINSI} */
    public const DAERAH = self::PROVINSI;

    /** @deprecated Gunakan {@see KAB_KOTA} */
    public const WILAYAH = self::KAB_KOTA;

    public static function normalize(?string $level): ?string
    {
        if ($level === null || $level === '') {
            return $level;
        }

        return match ($level) {
            'daerah', self::PROVINSI => self::PROVINSI,
            'wilayah', self::KAB_KOTA => self::KAB_KOTA,
            'nasional', self::NASIONAL => self::NASIONAL,
            default => $level,
        };
    }

    /**
     * @return self::NASIONAL|self::PROVINSI|self::KAB_KOTA
     */
    public static function resolve(Usm $user): string
    {
        $matrixId = \App\Helpers\MenuHelper::matrixRoleIdForUser($user);

        // SuperAdmin (1) and Executive1 (7) are ALWAYS Nasional level
        if (in_array($matrixId, [1, 7], true)) {
            return self::NASIONAL;
        }

        // Polda (2) and Executive2 (6) are ALWAYS Provinsi level
        if (in_array($matrixId, [2, 6], true)) {
            return self::PROVINSI;
        }

        // Polres (3), Operator (4), and Executive3 (5) are ALWAYS Kab/Kota level
        if (in_array($matrixId, [3, 4, 5])) {
            return self::KAB_KOTA;
        }

        $kabKota = trim((string) ($user->wilayah ?? ''));
        $provinsi = trim((string) ($user->daerah ?? ''));

        if ($kabKota !== '' && !str_starts_with(strtolower($kabKota), 'wil ')) {
            return self::KAB_KOTA;
        }

        if ($provinsi !== '') {
            return self::PROVINSI;
        }

        return self::NASIONAL;
    }

    public static function label(?string $level): string
    {
        return match (self::normalize($level)) {
            self::PROVINSI => 'Provinsi',
            self::KAB_KOTA => 'Kab/Kota',
            self::NASIONAL => 'Pusat',
            default => '—',
        };
    }
}
