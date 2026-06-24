<?php

namespace App\Support;

use Illuminate\Database\Connection;
use Illuminate\Database\Schema\Builder as SchemaBuilder;
use Illuminate\Support\Facades\DB;

/**
 * Pemetaan 2 database aplikasi:
 *
 * - Operasional (`mysql`): modul lapangan, usm, auth, marker peta operasional.
 * - Wilayah (`mysql_second`): master referensi `smd_mstr_*`, data `smd_tbl_*`.
 *   Polda/Polres diambil dari DB operasional (`daerah`/`wilayah` atau `master_provinsi`/`master_kabupaten_kota`).
 */
final class DatabaseConnections
{
    public static function operational(): string
    {
        return (string) config('database.operational_connection', config('database.default', 'mysql'));
    }

    public static function territory(): string
    {
        return (string) config('database.territory_connection', 'mysql_second');
    }

    public static function operationalDb(): Connection
    {
        return DB::connection(self::operational());
    }

    public static function territoryDb(): Connection
    {
        return DB::connection(self::territory());
    }

    public static function operationalSchema(): SchemaBuilder
    {
        return self::operationalDb()->getSchemaBuilder();
    }

    public static function territorySchema(): SchemaBuilder
    {
        return self::territoryDb()->getSchemaBuilder();
    }

    /**
     * @return array{operational: array{connection: string, database: string, ok: bool, error: ?string}, territory: array{connection: string, database: string, ok: bool, error: ?string, polda: ?int, polres: ?int, tables: ?array{wilayah: int, master: int, transaksi: int, lainnya: int, total: int}}}
     */
    public static function healthCheck(): array
    {
        $result = [
            'operational' => [
                'connection' => self::operational(),
                'database' => '',
                'ok' => false,
                'error' => null,
                'polda' => null,
                'polres' => null,
            ],
            'territory' => [
                'connection' => self::territory(),
                'database' => '',
                'ok' => false,
                'error' => null,
                'tables' => null,
            ],
        ];

        try {
            $op = self::operationalDb();
            $result['operational']['database'] = (string) $op->getDatabaseName();
            $op->select('SELECT 1');
            $result['operational']['ok'] = true;
            if (SmTerritoryData::isAvailable()) {
                if (SmTerritoryData::usesLegacyTables()) {
                    $result['operational']['polda'] = (int) $op->table('daerah')->count();
                    $result['operational']['polres'] = (int) $op->table('wilayah')->count();
                } else {
                    $result['operational']['polda'] = (int) $op->table('master_provinsi')->count();
                    $result['operational']['polres'] = (int) $op->table('master_kabupaten_kota')->count();
                }
            }
        } catch (\Throwable $e) {
            $result['operational']['error'] = $e->getMessage();
        }

        try {
            $ter = self::territoryDb();
            $result['territory']['database'] = (string) $ter->getDatabaseName();
            $ter->select('SELECT 1');
            $result['territory']['tables'] = SmSecondDatabaseReport::tableCounts(self::territory());
            $result['territory']['ok'] = true;
        } catch (\Throwable $e) {
            $result['territory']['error'] = $e->getMessage();
        }

        return $result;
    }
}
