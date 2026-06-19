<?php

namespace App\Support;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Menyelaraskan akses master `wilayah` / `daerah` untuk dua bentuk umum DB.
 * Secara domain, baris `daerah` adalah **provinsi** (nama disimpan di `namadaerah`; kolom profil `usm.daerah` mengacu ke sana).
 *
 * - **FK numerik:** `wilayah.id_daerah` → `daerah.id_daerah` (migrasi Laravel).
 * - **Legacy sbo:** `wilayah.id_daerah` berisi STRING sama dengan `daerah.namadaerah` ({@see \App\Http\Controllers\Master\WilayahController}).
 */
class TerritoryMaster
{
    /** @var ?bool */
    protected static ?bool $numericWilayahFk = null;

    public static function wilayahIdColumnIsNumericDaerahFk(): bool
    {
        if (static::$numericWilayahFk !== null) {
            return static::$numericWilayahFk;
        }

        if (! Schema::hasTable('wilayah')) {
            static::$numericWilayahFk = false;

            return static::$numericWilayahFk;
        }

        try {
            $type = strtolower((string) Schema::getColumnType('wilayah', 'id_daerah'));
        } catch (\Throwable) {
            static::$numericWilayahFk = false;

            return static::$numericWilayahFk;
        }

        static::$numericWilayahFk = in_array($type, [
            'bigint', 'biginteger', 'integer', 'int', 'mediumint', 'smallint', 'tinyint',
        ], true);

        return static::$numericWilayahFk;
    }

    /**
     * @return array<int, string>
     */
    public static function wilayahNamawilayahListForNamadaerah(string $namadaerah): array
    {
        return static::wilayahNamawilayahListForNamadaerahQuery($namadaerah)
            ->orderBy('w.namawilayah')
            ->pluck('w.namawilayah')
            ->all();
    }

    /** Query wilayah + daerah dalam satu provinsi (`daerah.namadaerah`). */
    public static function wilayahQueryForNamadaerah(string $namadaerah): \Illuminate\Database\Query\Builder
    {
        if ($namadaerah === '' || ! Schema::hasTable('wilayah')) {
            return DB::table('wilayah as w')->whereRaw('1 = 0');
        }

        return static::wilayahNamawilayahListForNamadaerahQuery($namadaerah);
    }

    private static function wilayahNamawilayahListForNamadaerahQuery(string $namadaerah): \Illuminate\Database\Query\Builder
    {
        if (! Schema::hasTable('wilayah') || ! Schema::hasTable('daerah')) {
            return DB::table('wilayah as w')->whereRaw('1 = 0');
        }

        if (static::wilayahIdColumnIsNumericDaerahFk()) {
            return DB::table('wilayah as w')
                ->join('daerah as d', 'd.id_daerah', '=', 'w.id_daerah')
                ->where('d.namadaerah', $namadaerah);
        }

        return DB::table('wilayah as w')
            ->join('daerah as d', 'd.namadaerah', '=', 'w.id_daerah')
            ->where('d.namadaerah', $namadaerah);
    }

    /**
     * Pasangan cocok `$userDaerah|$userWilayah` ke master + map untuk dropdown (kunci nama provinsi).
     *
     * @return array{array<string, string>, array<string, array<int, string>>}
     */
    public static function wilayahPairMapAndWilayahGroupedByNamadaerah(): array
    {
        $wilayahPairMap = [];
        $wilayahByDaerahNama = [];

        if (! Schema::hasTable('wilayah')) {
            return [$wilayahPairMap, $wilayahByDaerahNama];
        }

        $rows = DB::table('wilayah')->orderBy('namawilayah')->get(['namawilayah', 'id_daerah']);

        $namaByNumericId = [];
        if (Schema::hasTable('daerah')) {
            $namaByNumericId = DB::table('daerah')->pluck('namadaerah', 'id_daerah')->all();
        }

        foreach ($rows as $row) {
            $prov = $row->id_daerah !== null ? (string) $row->id_daerah : null;
            
            // If the column holds a numeric ID (even if it's a VARCHAR), map it back to namadaerah
            if ($prov !== null && is_numeric($prov) && isset($namaByNumericId[$prov])) {
                $prov = $namaByNumericId[$prov];
            } elseif ($prov !== null && is_numeric($prov) && isset($namaByNumericId[(int) $prov])) {
                $prov = $namaByNumericId[(int) $prov];
            }

            if ($prov === null || $prov === '') {
                continue;
            }

            $wilayahPairMap[$prov.'|'.$row->namawilayah] = $row->namawilayah;

            if (! isset($wilayahByDaerahNama[$prov])) {
                $wilayahByDaerahNama[$prov] = [];
            }
            $wilayahByDaerahNama[$prov][] = $row->namawilayah;
        }

        foreach ($wilayahByDaerahNama as &$list) {
            $list = array_values(array_unique($list));
            sort($list);
        }
        unset($list);

        return [$wilayahPairMap, $wilayahByDaerahNama];
    }

    /**
     * Hilangkan awalan/akhiran Kabupaten/Kota dari nama wilayah (tampilan Polres).
     */
    public static function stripKabupatenKotaFromName(?string $name): string
    {
        if ($name === null || trim($name) === '') {
            return '';
        }

        $n = mb_strtoupper(trim($name), 'UTF-8');
        $n = preg_replace('/^(KABUPATEN|KAB\.?|KOTA|KOT\.?)\s+/u', '', $n) ?? $n;
        $n = preg_replace('/\s+(KABUPATEN|KAB\.?|KOTA|KOT\.?)$/u', '', $n) ?? $n;

        return trim($n);
    }

    /** ID berikutnya untuk tabel master tanpa AUTO_INCREMENT (skema impor dump). */
    public static function nextTableId(string $table, string $column = 'id'): int
    {
        return ((int) DB::table($table)->max($column)) + 1;
    }
}
