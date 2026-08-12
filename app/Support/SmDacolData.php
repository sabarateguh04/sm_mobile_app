<?php

namespace App\Support;

use App\Helpers\HierarchicalHelper;
use Illuminate\Database\Query\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Data koleksi dari DB kedua: tabel `sm_dacol_*`.
 * Menampilkan 50 data terbaru per tabel secara paginasi.
 */
final class SmDacolData
{
    /** Prefix yang valid untuk tabel dacol. */
    private const PREFIX = 'sm_dacol_';

    public static function isAvailable(): bool
    {
        return Schema::connection(self::connection())->hasTable('sm_dacol_rengiat')
            || Schema::connection(self::connection())->hasTable('sm_dacol_integrasi_data')
            || Schema::connection(self::connection())->hasTable('sm_dacol_black_trouble_spot');
    }

    public static function connection(): string
    {
        // Tabel sm_dacol_* ada di database 'test' via koneksi 'satupeta'
        // (env: SATUPETA_DB_DATABASE=test, SATUPETA_DB_HOST, SATUPETA_DB_USERNAME)
        return 'satupeta';
    }

    /**
     * @return array<string, array{label: string, count: int}>
     */
    public static function tableRegistry(): array
    {
        $registry = config('smd_tables.dacol', []);
        $result   = [];

        foreach ($registry as $table => $meta) {
            $result[$table] = [
                'label' => (string) ($meta['label'] ?? $table),
                'count' => self::hasTable($table)
                    ? (int) DB::connection(self::connection())->table($table)->count()
                    : 0,
            ];
        }

        return $result;
    }

    public static function hasTable(string $table): bool
    {
        return str_starts_with($table, self::PREFIX)
            && Schema::connection(self::connection())->hasTable($table);
    }

    /**
     * Ambil data terbaru dengan filter opsional, termasuk source_server.
     * Default: 50 data terbaru (ORDER BY date_column DESC).
     *
     * @return list<string>  Daftar source_server unik yang tersedia di tabel.
     */
    public static function availableSources(string $table): array
    {
        if (! self::hasTable($table)) {
            return [];
        }

        $conn = self::connection();
        if (! Schema::connection($conn)->hasColumn($table, 'source_server')) {
            return [];
        }

        return DB::connection($conn)
            ->table($table)
            ->whereNotNull('source_server')
            ->where('source_server', '!=', '')
            ->orderBy('source_server')
            ->distinct()
            ->pluck('source_server')
            ->map(fn ($v) => (string) $v)
            ->all();
    }

    /**
     * Ambil 50 data terbaru (ORDER BY date_column DESC, LIMIT 50).
     */
    public static function paginated(string $table, Request $request, int $perPage = 50)
    {
        if (! self::hasTable($table)) {
            return new \Illuminate\Pagination\LengthAwarePaginator([], 0, $perPage);
        }

        $meta  = config("smd_tables.dacol.{$table}", []);
        $query = DB::connection(self::connection())->table($table);

        self::applyTerritoryScope($query, $table);
        self::applyRequestFilters($query, $table, $request, $meta);

        $orderCol = self::resolveOrderColumn($table, $meta);

        return $query->orderByDesc($orderCol)->paginate($perPage)->withQueryString();
    }

    private static function resolveOrderColumn(string $table, array $meta): string
    {
        $conn    = self::connection();
        $dateCol = (string) ($meta['date_column'] ?? '');
        $pk      = (string) ($meta['pk'] ?? '');

        if ($dateCol !== '' && Schema::connection($conn)->hasColumn($table, $dateCol)) {
            return $dateCol;
        }

        if ($pk !== '' && Schema::connection($conn)->hasColumn($table, $pk)) {
            return $pk;
        }

        // Auto-detect: cari kolom yang umum atau gunakan kolom pertama
        $cols = Schema::connection($conn)->getColumnListing($table);
        if (in_array('pk_ids', $cols, true)) return 'pk_ids';
        if (in_array('last_updated', $cols, true)) return 'last_updated';
        if (in_array('tanggal', $cols, true)) return 'tanggal';
        
        return $cols[0] ?? 'id';
    }

    /**
     * @param array<string, mixed> $meta
     */
    private static function applyRequestFilters(Builder $query, string $table, Request $request, array $meta): void
    {
        if ($request->filled('search')) {
            $search = (string) $request->search;
            $cols   = $meta['search'] ?? [];

            if ($cols !== []) {
                $query->where(function ($q) use ($search, $cols, $table) {
                    foreach ($cols as $col) {
                        if (Schema::connection(SmDacolData::connection())->hasColumn($table, $col)) {
                            $q->orWhere($col, 'like', '%'.$search.'%');
                        }
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
        // Filter source_server (SM-SOLO, SM-JOGJA, dst)
        if ($request->filled('source_server') && Schema::connection($conn)->hasColumn($table, 'source_server')) {
            $query->where('source_server', $request->source_server);
        }

        // Apply territory (daerah/wilayah) filters if the columns exist in Dacol tables
        TerritoryIndexFilters::applyColumnFilters($query, $request, $table, $conn);
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
        if (! self::hasTable($table)) {
            return [];
        }

        $meta     = config("smd_tables.dacol.{$table}", []);
        $existing = Schema::connection(self::connection())->getColumnListing($table);

        // Bila display terdaftar di config, filter hanya kolom yang benar-benar ada
        if (! empty($meta['display'])) {
            $filtered = array_values(array_filter(
                (array) $meta['display'],
                fn (string $col) => in_array($col, $existing, true)
            ));

            // Jika ada yang cocok, gunakan; jika tidak ada sama sekali, fallback ke semua kolom
            if ($filtered !== []) {
                return $filtered;
            }
        }

        // Fallback: tampilkan semua kolom aktual (max 12 kolom pertama agar tidak terlalu lebar)
        return array_slice($existing, 0, 12);
    }

    public static function find(int|string $id, string $table): ?object
    {
        if (! self::hasTable($table)) {
            return null;
        }

        $pk = config("smd_tables.dacol.{$table}.pk", 'id');
        return SmRecordPresenter::find(self::connection(), $table, $id, $pk);
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
