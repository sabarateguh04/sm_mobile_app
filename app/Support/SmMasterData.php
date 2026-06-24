<?php

namespace App\Support;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Master referensi dari DB kedua: tabel `smd_mstr_*` (dump test.sql).
 */
final class SmMasterData
{
    public static function isAvailable(): bool
    {
        $conn = DatabaseConnections::territory();

        return Schema::connection($conn)->hasTable('smd_mstr_jenis_kegiatan');
    }

    public static function connection(): string
    {
        return DatabaseConnections::territory();
    }

    /**
     * @return array<string, array{label: string, column: string, count: int}>
     */
    public static function tableRegistry(): array
    {
        $registry = config('smd_tables.master', []);
        $result = [];

        foreach ($registry as $table => $meta) {
            $result[$table] = [
                'label' => (string) ($meta['label'] ?? $table),
                'column' => (string) ($meta['column'] ?? ''),
                'count' => self::hasTable($table) ? (int) DB::connection(self::connection())->table($table)->count() : 0,
            ];
        }

        return $result;
    }

    public static function hasTable(string $table): bool
    {
        return str_starts_with($table, 'smd_mstr_')
            && Schema::connection(self::connection())->hasTable($table);
    }

    /**
     * @return list<string>
     */
    public static function options(string $table, string $column): array
    {
        if (! self::hasTable($table) || ! Schema::connection(self::connection())->hasColumn($table, $column)) {
            return [];
        }

        return DB::connection(self::connection())
            ->table($table)
            ->whereNotNull($column)
            ->where($column, '!=', '')
            ->orderBy($column)
            ->pluck($column)
            ->map(fn ($v) => (string) $v)
            ->unique()
            ->values()
            ->all();
    }

    /**
     * @return list<string>
     */
    public static function optionsForReferensi(string $refKey): array
    {
        $map = config("smd_referensi_map.{$refKey}");
        if (! is_array($map)) {
            return [];
        }

        return self::options((string) $map['table'], (string) $map['column']);
    }

    public static function paginated(string $table, int $perPage = 20, ?string $search = null)
    {
        if (! self::hasTable($table)) {
            return new \Illuminate\Pagination\LengthAwarePaginator([], 0, $perPage);
        }

        $meta = config("smd_tables.master.{$table}", []);
        $labelColumn = (string) ($meta['column'] ?? '');

        $query = DB::connection(self::connection())->table($table)->orderBy('pk_ids');

        if ($search !== null && $search !== '' && $labelColumn !== '' && Schema::connection(self::connection())->hasColumn($table, $labelColumn)) {
            $query->where($labelColumn, 'like', '%'.$search.'%');
        }

        return $query->paginate($perPage)->withQueryString();
    }

    public static function find(int|string $id, string $table): ?object
    {
        if (! self::hasTable($table)) {
            return null;
        }

        return SmRecordPresenter::find(self::connection(), $table, $id);
    }

    /**
     * @return list<string>
     */
    public static function listColumns(string $table): array
    {
        if (! self::hasTable($table)) {
            return [];
        }

        return SmRecordPresenter::columns(self::connection(), $table);
    }
}
