<?php

namespace App\Support;

/**
 * Penyesuaian input form dengan batasan kolom `usm` di dump sbo (NOT NULL tanpa default).
 */
class LegacyUsmDbInput
{
    /** Kolom varchar `usm` yang tidak boleh NULL (skema sbo / produksi). */
    public const NOT_NULL_STRING_COLUMNS = [
        'notelp',
        'daerah',
        'wilayah',
        'fotoprof',
        'nrp_nip_nik',
        'induk_pemerintahan',
        'pemerintahan_daerah',
        'organisasi_daerah',
        'unit_kerja',
        'email_status',
    ];

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    public static function normalizeOptionalTextFields(array $data): array
    {
        return static::normalizeForPersist($data);
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    public static function normalizeForPersist(array $data): array
    {
        foreach (static::NOT_NULL_STRING_COLUMNS as $key) {
            if (array_key_exists($key, $data) && $data[$key] === null) {
                $data[$key] = '';
            }
        }

        return $data;
    }

    /**
     * Nilai untuk simpan inline / form: kosong → string kosong (bukan null) bila kolom NOT NULL.
     */
    public static function stringOrEmpty(mixed $value): string
    {
        if ($value === null) {
            return '';
        }

        return trim((string) $value);
    }

    /**
     * Normalisasi satu field sebelum UPDATE `usm`.
     */
    public static function normalizeInlineFieldValue(string $field, mixed $value): mixed
    {
        if ($field === 'kategori_user') {
            if ($value === null || $value === '') {
                return null;
            }

            return $value;
        }

        if ($field === 'user_role_id') {
            return ($value === null || $value === '') ? null : (int) $value;
        }

        if (in_array($field, static::NOT_NULL_STRING_COLUMNS, true)) {
            return static::stringOrEmpty($value);
        }

        if (in_array($field, ['nrp_nip_nik', 'induk_pemerintahan', 'pemerintahan_daerah', 'organisasi_daerah', 'unit_kerja'], true)) {
            return static::stringOrEmpty($value);
        }

        return $value;
    }
}
