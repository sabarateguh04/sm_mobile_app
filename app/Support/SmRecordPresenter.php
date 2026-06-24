<?php

namespace App\Support;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Helper tampilan record DB kedua (kolom, detail, format nilai).
 */
final class SmRecordPresenter
{
    private const HIDDEN_COLUMNS = [
        'pk_ids', 'rowid', 'source_server', 'last_updated', 'updated_at',
        'ctdby', 'ctddate', 'ctdupd', 'ctdtime', 'is_delete', 'is_valid',
    ];

    /**
     * @return list<string>
     */
    public static function columns(string $connection, string $table, bool $includeHidden = false): array
    {
        if (! Schema::connection($connection)->hasTable($table)) {
            return [];
        }

        $db = (string) DB::connection($connection)->getDatabaseName();
        $rows = DB::connection($connection)->select(
            'SELECT COLUMN_NAME FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = ? AND TABLE_NAME = ? ORDER BY ORDINAL_POSITION',
            [$db, $table]
        );

        $cols = array_map(static fn ($r) => (string) $r->COLUMN_NAME, $rows);

        if ($includeHidden) {
            return $cols;
        }

        return array_values(array_filter($cols, static fn ($c) => ! in_array($c, self::HIDDEN_COLUMNS, true)));
    }

    public static function find(string $connection, string $table, int|string $id): ?object
    {
        if (! Schema::connection($connection)->hasTable($table)) {
            return null;
        }

        return DB::connection($connection)->table($table)->where('pk_ids', $id)->first();
    }

    public static function label(string $column): string
    {
        return ucwords(str_replace('_', ' ', $column));
    }

    public static function formatValue(mixed $value, string $column): string
    {
        if ($value === null || $value === '') {
            return '—';
        }

        if (is_numeric($value) && (str_contains($column, 'jumlah') || $column === 'total' || str_contains($column, 'kerugian'))) {
            return number_format((float) $value, 0, ',', '.');
        }

        if (in_array($column, ['koordinat_gps_lintang', 'koordinat_gps_bujur', 'lat', 'lng', 'long'], true) && is_numeric($value)) {
            return (string) $value;
        }

        return (string) $value;
    }

    /**
     * @return array{lat: ?float, lng: ?float}
     */
    public static function coordinates(object $row): array
    {
        $lat = $row->koordinat_gps_lintang ?? $row->lat ?? null;
        $lng = $row->koordinat_gps_bujur ?? $row->lng ?? $row->long ?? null;

        return [
            'lat' => SmTerritoryData::normalizeCoordinate(is_scalar($lat) ? (string) $lat : null),
            'lng' => SmTerritoryData::normalizeCoordinate(is_scalar($lng) ? (string) $lng : null),
        ];
    }

    /**
     * @return list<array{label: string, value: string}>
     */
    public static function fieldRows(string $connection, string $table, object $row): array
    {
        $fields = [];
        foreach (self::columns($connection, $table, false) as $col) {
            $fields[] = [
                'label' => self::label($col),
                'value' => self::formatValue($row->{$col} ?? null, $col),
            ];
        }

        return $fields;
    }
}
