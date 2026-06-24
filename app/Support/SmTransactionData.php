<?php

namespace App\Support;

use App\Helpers\HierarchicalHelper;
use Illuminate\Database\Query\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Data transaksi dari DB kedua: tabel `smd_tbl_*` (dump test.sql).
 */
final class SmTransactionData
{
    public static function isAvailable(): bool
    {
        $conn = DatabaseConnections::territory();

        return Schema::connection($conn)->hasTable('smd_tbl_data_integrasi');
    }

    public static function connection(): string
    {
        return DatabaseConnections::territory();
    }

    /**
     * @return array<string, array{label: string, count: int}>
     */
    public static function tableRegistry(): array
    {
        $registry = config('smd_tables.transaksi', []);
        $result = [];

        foreach ($registry as $table => $meta) {
            $result[$table] = [
                'label' => (string) ($meta['label'] ?? $table),
                'count' => self::hasTable($table) ? (int) DB::connection(self::connection())->table($table)->count() : 0,
            ];
        }

        return $result;
    }

    public static function hasTable(string $table): bool
    {
        return str_starts_with($table, 'smd_tbl_')
            && Schema::connection(self::connection())->hasTable($table);
    }

    public static function paginated(string $table, Request $request, int $perPage = 20)
    {
        if (! self::hasTable($table)) {
            return new \Illuminate\Pagination\LengthAwarePaginator([], 0, $perPage);
        }

        $meta = config("smd_tables.transaksi.{$table}", []);
        $query = DB::connection(self::connection())->table($table);

        self::applyTerritoryScope($query, $table);
        self::applyRequestFilters($query, $table, $request, $meta);

        $orderCol = (string) ($meta['date_column'] ?? 'pk_ids');

        return $query->orderByDesc($orderCol)->paginate($perPage)->withQueryString();
    }

    /**
     * @param  array<string, mixed>  $meta
     */
    private static function applyRequestFilters(Builder $query, string $table, Request $request, array $meta): void
    {
        if ($request->filled('search')) {
            $search = (string) $request->search;
            $cols = $meta['search'] ?? [];
            if ($cols !== []) {
                $query->where(function ($q) use ($search, $cols) {
                    foreach ($cols as $col) {
                        $q->orWhere($col, 'like', '%'.$search.'%');
                    }
                });
            }
        }

        $dateCol = $meta['date_column'] ?? null;
        if ($dateCol && $request->filled('tanggal_dari')) {
            $query->whereDate($dateCol, '>=', $request->tanggal_dari);
        }
        if ($dateCol && $request->filled('tanggal_sampai')) {
            $query->whereDate($dateCol, '<=', $request->tanggal_sampai);
        }

        $conn = self::connection();
        if ($request->filled('daerah') && Schema::connection($conn)->hasColumn($table, 'polda')) {
            $query->where('polda', 'like', '%'.$request->daerah.'%');
        }

        if ($request->filled('wilayah') && Schema::connection($conn)->hasColumn($table, 'polres')) {
            $query->where('polres', 'like', '%'.$request->wilayah.'%');
        }
    }

    private static function applyTerritoryScope(Builder $query, string $table): void
    {
        if (HierarchicalHelper::viewerBypassesTerritoryScope()) {
            return;
        }

        $user = Auth::user();
        if (! $user) {
            return;
        }

        $conn = self::connection();
        $hasPolda = Schema::connection($conn)->hasColumn($table, 'polda');
        $hasPolres = Schema::connection($conn)->hasColumn($table, 'polres');

        if (! $hasPolda && ! $hasPolres) {
            return;
        }

        if (! empty($user->daerah) && ! empty($user->wilayah)) {
            if ($hasPolda) {
                $query->where('polda', 'like', '%'.$user->daerah.'%');
            }
            if ($hasPolres) {
                $query->where('polres', 'like', '%'.$user->wilayah.'%');
            }

            return;
        }

        if (! empty($user->wilayah) && $hasPolres) {
            $query->where('polres', 'like', '%'.$user->wilayah.'%');

            return;
        }

        if (! empty($user->daerah) && $hasPolda) {
            $query->where('polda', 'like', '%'.$user->daerah.'%');
        }
    }

    /**
     * @return list<string>
     */
    public static function displayColumns(string $table): array
    {
        $meta = config("smd_tables.transaksi.{$table}", []);

        return $meta['display'] ?? ['polda', 'polres'];
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
