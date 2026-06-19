<?php

namespace App\Support;

/**
 * Opsi standar alat keamanan (checkbox) untuk Objek Wisata & Tempat Ibadah.
 */
final class AlatKeamananOptions
{
    /**
     * @return list<string>
     */
    public static function all(): array
    {
        return [
            'Kamera Surveillance',
            'Sistem Access Control',
            'Detektor Kebakaran',
            'Perlengkapan Tanggap Darurat',
            'Panic Button',
            'Route Evakuasi',
            'Alat Pemadam Kebakaran',
        ];
    }
}
