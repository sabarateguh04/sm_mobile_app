<?php

namespace App\Support;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Master Polda/Polres dari database operasional (utama):
 * `daerah` + `wilayah`, atau `master_provinsi` + `master_kabupaten_kota`.
 */
final class SmTerritoryData
{
    public static function connection(): string
    {
        return DatabaseConnections::operational();
    }

    public static function usesLegacyTables(): bool
    {
        $conn = self::connection();

        return Schema::connection($conn)->hasTable('daerah')
            && Schema::connection($conn)->hasTable('wilayah');
    }

    public static function usesMasterTables(): bool
    {
        $conn = self::connection();

        return Schema::connection($conn)->hasTable('master_provinsi')
            && Schema::connection($conn)->hasTable('master_kabupaten_kota');
    }

    public static function isAvailable(): bool
    {
        return self::usesLegacyTables() || self::usesMasterTables();
    }

    /**
     * @return Collection<int, string>
     */
    public static function poldaOptions(): Collection
    {
        if (! self::isAvailable()) {
            return collect();
        }

        $conn = self::connection();

        if (self::usesLegacyTables()) {
            return DB::connection($conn)
                ->table('daerah')
                ->orderBy('namadaerah')
                ->pluck('namadaerah');
        }

        return DB::connection($conn)
            ->table('master_provinsi')
            ->orderBy('nama')
            ->pluck('nama');
    }

    /**
     * @return Collection<int, string>
     */
    public static function polresOptions(?string $poldaName = null): Collection
    {
        if (! self::isAvailable()) {
            return collect();
        }

        $conn = self::connection();

        if (self::usesLegacyTables()) {
            if ($poldaName !== null && $poldaName !== '') {
                return collect(TerritoryMaster::wilayahNamawilayahListForNamadaerah($poldaName));
            }

            return DB::connection($conn)
                ->table('wilayah')
                ->orderBy('namawilayah')
                ->pluck('namawilayah');
        }

        $query = DB::connection($conn)
            ->table('master_kabupaten_kota as w')
            ->join('master_provinsi as d', 'd.id', '=', 'w.master_provinsi_id')
            ->orderBy('w.nama');

        if ($poldaName !== null && $poldaName !== '') {
            $query->whereRaw('LOWER(d.nama) = ?', [mb_strtolower($poldaName)]);
        }

        return $query->pluck('w.nama');
    }

    public static function findPoldaByName(string $name): ?object
    {
        if (! self::isAvailable()) {
            return null;
        }

        $cleaned = self::cleanTerritoryName($name);
        $conn = self::connection();

        if (self::usesLegacyTables()) {
            $row = DB::connection($conn)
                ->table('daerah')
                ->where('namadaerah', 'LIKE', '%'.$cleaned.'%')
                ->orderBy('namadaerah')
                ->first();

            if ($row === null) {
                return null;
            }

            return (object) [
                'polda' => (string) $row->namadaerah,
                'lat' => $row->lat ?? null,
                'lng' => $row->lng ?? null,
            ];
        }

        $row = DB::connection($conn)
            ->table('master_provinsi')
            ->where('nama', 'LIKE', '%'.$cleaned.'%')
            ->orderBy('nama')
            ->first();

        if ($row === null) {
            return null;
        }

        return (object) [
            'polda' => (string) $row->nama,
            'lat' => $row->lat ?? null,
            'lng' => $row->lng ?? null,
        ];
    }

    public static function findPolresByName(string $name, ?string $poldaName = null): ?object
    {
        if (! self::isAvailable()) {
            return null;
        }

        $cleaned = self::cleanTerritoryName($name);
        $conn = self::connection();

        if (self::usesLegacyTables()) {
            $query = DB::connection($conn)
                ->table('wilayah as w')
                ->select('w.*', DB::raw('d.namadaerah as polda'))
                ->where('w.namawilayah', 'LIKE', '%'.$cleaned.'%');

            if (TerritoryMaster::wilayahIdColumnIsNumericDaerahFk()) {
                $query->join('daerah as d', 'd.id_daerah', '=', 'w.id_daerah');
            } else {
                $query->join('daerah as d', 'd.namadaerah', '=', 'w.id_daerah');
            }

            if ($poldaName !== null && $poldaName !== '') {
                $query->whereRaw('LOWER(d.namadaerah) = ?', [mb_strtolower($poldaName)]);
            }

            self::applyPolresNameOrdering($query, 'w.namawilayah', $name);

            $row = $query->first();
            if ($row === null) {
                return null;
            }

            return (object) [
                'polres' => (string) $row->namawilayah,
                'polda' => (string) ($row->polda ?? ''),
                'lat' => $row->lat ?? null,
                'long' => $row->lng ?? $row->long ?? null,
            ];
        }

        $query = DB::connection($conn)
            ->table('master_kabupaten_kota as w')
            ->select('w.*', 'd.nama as polda')
            ->join('master_provinsi as d', 'd.id', '=', 'w.master_provinsi_id')
            ->where('w.nama', 'LIKE', '%'.$cleaned.'%');

        if ($poldaName !== null && $poldaName !== '') {
            $query->whereRaw('LOWER(d.nama) = ?', [mb_strtolower($poldaName)]);
        }

        self::applyPolresNameOrdering($query, 'w.nama', $name);

        $row = $query->first();
        if ($row === null) {
            return null;
        }

        return (object) [
            'polres' => (string) $row->nama,
            'polda' => (string) ($row->polda ?? ''),
            'lat' => $row->lat ?? null,
            'long' => $row->lng ?? $row->long ?? null,
        ];
    }

    /**
     * @return array{0: ?float, 1: ?float}
     */
    public static function parseCoordinate(?string $lat, ?string $lng): array
    {
        $latVal = self::normalizeCoordinate($lat);
        $lngVal = self::normalizeCoordinate($lng);

        return [$latVal, $lngVal];
    }

    public static function normalizeCoordinate(?string $value): ?float
    {
        if ($value === null) {
            return null;
        }

        $value = trim($value);
        if ($value === '' || strtoupper($value) === 'NULL' || ! is_numeric($value)) {
            return null;
        }

        return (float) $value;
    }

    public static function cleanTerritoryName(string $name): string
    {
        $name = mb_strtolower($name);
        $search = [
            'polrestabes metro', 'polresta metro', 'polres metro', 'polres/metro',
            'polrestabes', 'polresta', 'polres', 'polda',
            'kabupaten', 'kab.', 'kota administrasi', 'kota adm.', 'kota', 'provinsi', 'prov.',
        ];
        $name = str_replace($search, '', $name);

        return trim($name);
    }

    private static function applyPolresNameOrdering(\Illuminate\Database\Query\Builder $query, string $column, string $name): void
    {
        $lower = mb_strtolower($name);
        if (str_contains($lower, 'kota') || str_contains($lower, 'polresta') || str_contains($lower, 'polrestabes') || str_contains($lower, 'metro') || str_contains($lower, 'jakarta')) {
            $query->orderByRaw("CASE WHEN {$column} LIKE '%Kota%' OR {$column} LIKE '%POLRESTA%' OR {$column} LIKE '%METRO%' THEN 0 ELSE 1 END");
        } else {
            $query->orderByRaw("CASE WHEN {$column} NOT LIKE '%Kota%' AND {$column} NOT LIKE '%POLRESTA%' AND {$column} NOT LIKE '%METRO%' THEN 0 ELSE 1 END");
        }
    }
}
