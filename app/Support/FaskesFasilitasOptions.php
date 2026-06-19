<?php

namespace App\Support;

/**
 * Opsi fasilitas standar (checkbox) untuk Data Faskes.
 */
final class FaskesFasilitasOptions
{
    /**
     * @return list<string>
     */
    public static function all(): array
    {
        return [
            'IGD',
            'Ambulan Gawat Darurat',
            'Ruang Operasi',
            'Ruang ICU/NICU/PICU',
            'Radiologi',
            'Laboratorium',
        ];
    }
}
