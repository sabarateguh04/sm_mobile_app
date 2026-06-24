<?php

namespace App\Support;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Opsi dropdown dari tabel master referensi (config/referensi_tables.php).
 */
class ReferensiOptions
{
    /**
     * @param  list<string>  $defaults
     * @return list<string>
     */
    public static function list(string $refKey, array $defaults = [], ?string $distinctTable = null, ?string $distinctColumn = null): array
    {
        $fromSmd = SmMasterData::isAvailable() ? SmMasterData::optionsForReferensi($refKey) : [];
        $fromMaster = self::fromMasterTable($refKey);
        $merged = array_values(array_unique(array_merge($defaults, $fromSmd, $fromMaster)));

        if ($distinctTable !== null && $distinctColumn !== null && Schema::hasTable($distinctTable) && Schema::hasColumn($distinctTable, $distinctColumn)) {
            $fromData = DB::table($distinctTable)
                ->whereNotNull($distinctColumn)
                ->where($distinctColumn, '!=', '')
                ->distinct()
                ->orderBy($distinctColumn)
                ->pluck($distinctColumn)
                ->all();

            $merged = array_values(array_unique(array_merge($merged, $fromData)));
        }

        return $merged;
    }

    /**
     * @param  list<string>  $defaults
     */
    public static function collection(string $refKey, array $defaults = [], ?string $distinctTable = null, ?string $distinctColumn = null): Collection
    {
        return collect(self::list($refKey, $defaults, $distinctTable, $distinctColumn));
    }

    /**
     * @return list<string>
     */
    public static function fromMasterTable(string $refKey): array
    {
        $defs = config("referensi_tables.{$refKey}");
        if (! is_array($defs) || count($defs) < 3) {
            return [];
        }

        [$table, , $nameCol] = $defs;

        if (! Schema::hasTable($table) || ! Schema::hasColumn($table, $nameCol)) {
            return [];
        }

        return DB::table($table)
            ->whereNotNull($nameCol)
            ->where($nameCol, '!=', '')
            ->orderBy($nameCol)
            ->pluck($nameCol)
            ->map(fn ($v) => (string) $v)
            ->all();
    }

    public static function label(string $refKey): string
    {
        $defs = config("referensi_tables.{$refKey}");

        return is_array($defs) && isset($defs[3]) ? (string) $defs[3] : $refKey;
    }

    /**
     * @return array{0: string, 1: string, 2: string, 3: string}|null
     */
    public static function definition(string $refKey): ?array
    {
        $defs = config("referensi_tables.{$refKey}");

        if (! is_array($defs) || count($defs) < 4) {
            return null;
        }

        return [$defs[0], $defs[1], $defs[2], $defs[3]];
    }
}
