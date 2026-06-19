<?php

namespace App\Support;

/**
 * Patroli CCTV → Opsdal:
 * - Peristiwa "Perlambatan" (master t_peristiwa) → Kodal Perlambatan (trafficstuck)
 * - Selain itu (Kecelakaan, Bencana Alam, kosong, dll.) → Kodal Kontijensi (contigency)
 *
 * Situasi lalin (macet/padat) tidak dipakai untuk pemilihan modul — hanya disalin ke ketcb.
 */
final class PatrolCctvOpsdalClassifier
{
    /** Nama peristiwa di master (mis. t_peristiwa.nameperistiwa = "Perlambatan"). */
    private const PERLAMBATAN_PERISTIWA_MARKERS = [
        'perlambatan',
    ];

    /**
     * Hanya kolom peristiwa yang menentukan modul, bukan situasi lalin.
     */
    public static function isPerlambatan(?string $peristiwa, ?string $situasiLalin = null): bool
    {
        $peristiwaNorm = mb_strtolower(trim($peristiwa ?? ''));
        if ($peristiwaNorm === '') {
            return false;
        }

        foreach (self::PERLAMBATAN_PERISTIWA_MARKERS as $marker) {
            if (str_contains($peristiwaNorm, $marker)) {
                return true;
            }
        }

        return false;
    }

    public static function targetModule(?string $peristiwa, ?string $situasiLalin = null): string
    {
        return self::isPerlambatan($peristiwa, $situasiLalin) ? 'trafficstuck' : 'contigency';
    }
}
