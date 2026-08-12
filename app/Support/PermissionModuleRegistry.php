<?php

namespace App\Support;

/**
 * Mengelompokkan `page_name` pada role_permissions ke label modul untuk UI.
 */
final class PermissionModuleRegistry
{
    /**
     * @return array{0: string, 1: string} [module_id, label]
     */
    public static function moduleForPage(string $page): array
    {
        $p = strtolower(trim($page));

        if (in_array($p, ['usm', 'roles'], true) || str_contains($p, 'role_permission')) {
            return ['admin', 'Admin & Sistem'];
        }

        if (str_contains($p, 'master')
            || str_contains($p, 'referensi')
            || str_contains($p, 'pemerintahan')
            || str_contains($p, 'pusat')
            || $p === 'data_terminal'
            || $p === 'datawilayah'
            || str_contains($p, 'resort')
            || str_contains($p, 'kab')
            || str_contains($p, 'instansi')) {
            return ['master', 'Master Data'];
        }

        if (str_contains($p, 'arus')
            || str_contains($p, 'traffic')
            || str_contains($p, 'mudik')
            || str_contains($p, 'audit')
            || str_contains($p, 'contigen')
            || str_contains($p, 'turjag')
            || str_contains($p, 'gangguan')
            || str_contains($p, 'opsdal')
            || str_contains($p, 'data_gangguan')) {
            return ['lalin', 'Lalu Lintas & Arus'];
        }

        if (str_contains($p, 'cctv')
            || str_contains($p, 'layanan')
            || str_contains($p, 'media')
            || str_contains($p, 'personil')
            || str_contains($p, 'publik')
            || str_contains($p, 'masyarakat')
            || str_contains($p, 'itsupport')) {
            return ['layanan', 'Layanan & IT'];
        }

        if (str_contains($p, 'terminal') || str_contains($p, 'pnp') || str_contains($p, 'penumpang')) {
            return ['terminal', 'Terminal & Penumpang'];
        }

        if (str_contains($p, 'wisata')
            || str_contains($p, 'kunjung')
            || str_contains($p, 'ibadah')
            || str_contains($p, 'objek')
            || str_contains($p, 'faskes')) {
            return ['wisata', 'Wisata & Ibadah'];
        }

        if (preg_match('#^(wil|da|nas)/#', $p) || str_contains($p, 'wilayah') && str_contains($p, 'arus')) {
            return ['tier', 'Operasional Wilayah'];
        }

        if (in_array($p, ['home', 'map', 'account'], true)) {
            return ['umum', 'Umum'];
        }

        return ['lainnya', 'Lainnya'];
    }

    /**
     * Urutan tampilan modul di accordion.
     *
     * @return array<string, int>
     */
    public static function moduleOrder(): array
    {
        return [
            'umum' => 0,
            'admin' => 1,
            'master' => 2,
            'lalin' => 3,
            'layanan' => 4,
            'terminal' => 5,
            'wisata' => 6,
            'tier' => 7,
            'lainnya' => 99,
        ];
    }
}
