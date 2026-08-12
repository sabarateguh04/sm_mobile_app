<?php

namespace App\Support;

use App\Helpers\HierarchicalHelper;
use App\Helpers\MenuHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

final class TerritoryIndexFilters
{
    /**
     * Tingkat teritori efektif untuk filter UI & query (Executive1 = nasional, Executive2 = provinsi).
     *
     * @return TerritoryLevelForUsm::NASIONAL|TerritoryLevelForUsm::PROVINSI|TerritoryLevelForUsm::KAB_KOTA|null
     */
    private static function effectiveLevel(): ?string
    {
        $user = Auth::user();

        return $user ? MenuHelper::effectiveTerritoryLevelForUser($user) : null;
    }

    public static function canFilterByDaerah(): bool
    {
        $user = Auth::user();
        if (! $user) {
            return false;
        }

        // Executive2 (provinsi/Polda): data sudah dibatasi ke polda kerja — tanpa filter UI
        if (MenuHelper::isExecutiveProvinsiUser($user)) {
            return false;
        }

        // Executive1 (pusat/nasional): tampilkan filter Polda
        if (MenuHelper::isExecutiveNationalUser($user)) {
            return true;
        }

        return static::effectiveLevel() === TerritoryLevelForUsm::NASIONAL;
    }

    public static function canFilterByWilayah(): bool
    {
        return Auth::check();
    }

    public static function applyUsmFilters($query, Request $request): void
    {
        $user = Auth::user();
        if (! $user) {
            return;
        }

        $level = static::effectiveLevel();

        // 1. Apply daerah (Polda) filter
        if ($level === TerritoryLevelForUsm::PROVINSI) {
            // Force Polda/Executive2 level users to their own daerah (no UI filter needed)
            $query->whereRaw('LOWER(usm.daerah) = ?', [strtolower((string) $user->daerah)]);
        } elseif ($level === TerritoryLevelForUsm::NASIONAL && $request->filled('daerah')) {
            // NASIONAL can filter by daerah (has UI filter)
            $query->whereRaw('LOWER(usm.daerah) = ?', [strtolower((string) $request->daerah)]);
        }

        // 2. Apply wilayah (Polres) filter
        if (static::canFilterByWilayah()) {
            if ($level === TerritoryLevelForUsm::KAB_KOTA) {
                // Force Kab/Kota level users to their own wilayah
                $query->whereRaw('LOWER(usm.wilayah) = ?', [strtolower((string) $user->wilayah)]);
            } elseif ($level === TerritoryLevelForUsm::PROVINSI) {
                if ($request->filled('wilayah')) {
                    // Check if the requested wilayah is under the user's Polda to prevent manipulation
                    $allowedWilayahs = static::wilayahOptions($request)->map(fn($w) => strtolower(trim($w)))->toArray();
                    $reqWilayah = strtolower(trim((string) $request->wilayah));
                    if (in_array($reqWilayah, $allowedWilayahs, true)) {
                        $query->whereRaw('LOWER(usm.wilayah) = ?', [$reqWilayah]);
                    } else {
                        // Parameter manipulation detected, force empty results
                        $query->whereRaw('1 = 0');
                    }
                }
            } elseif ($level === TerritoryLevelForUsm::NASIONAL && $request->filled('wilayah')) {
                $query->whereRaw('LOWER(usm.wilayah) = ?', [strtolower((string) $request->wilayah)]);
            }
        }
    }

    public static function applyColumnFilters($query, Request $request, string $table, ?string $connection = null): void
    {
        $user = Auth::user();
        if (! $user) {
            return;
        }

        $level = static::effectiveLevel();
        $schema = $connection ? Schema::connection($connection) : Schema::connection(config('database.default'));

        $isSatupeta = $connection === 'satupeta' || str_starts_with($table, 'sm_dacol_');
        
        $daerahCol = $isSatupeta 
            ? ($schema->hasColumn($table, 'polda') ? 'polda' : ($schema->hasColumn($table, 'daerah') ? 'daerah' : null))
            : ($schema->hasColumn($table, 'daerah') ? 'daerah' : ($schema->hasColumn($table, 'polda') ? 'polda' : null));
            
        $wilayahCol = $isSatupeta 
            ? ($schema->hasColumn($table, 'polres') ? 'polres' : ($schema->hasColumn($table, 'wilayah') ? 'wilayah' : null))
            : ($schema->hasColumn($table, 'wilayah') ? 'wilayah' : ($schema->hasColumn($table, 'polres') ? 'polres' : null));

        $cleanWilayah = function (string $name): string {
            $name = strtolower(trim($name));
            $search = [
                'polrestabes metro', 'polresta metro', 'polres metro', 'polres/metro',
                'polrestabes', 'polresta', 'polres', 'polda',
                'kabupaten', 'kab.', 'kota administrasi', 'kota adm.', 'kota', 'provinsi', 'prov.'
            ];
            return trim(str_replace($search, '', $name));
        };

        // 1. Apply daerah (Polda) filter
        if ($daerahCol) {
            if ($level === TerritoryLevelForUsm::PROVINSI) {
                // Force Polda/Executive2 level users to their own daerah (no UI filter needed)
                $query->whereRaw('LOWER('.$table.'.'.$daerahCol.') LIKE ?', ['%' . $cleanWilayah((string) $user->daerah) . '%']);
            } elseif ($level === TerritoryLevelForUsm::NASIONAL && $request->filled('daerah')) {
                // NASIONAL can filter by daerah (has UI filter)
                $query->whereRaw('LOWER('.$table.'.'.$daerahCol.') LIKE ?', ['%' . $cleanWilayah((string) $request->daerah) . '%']);
            }
        }

        // 2. Apply wilayah (Polres) filter
        if (static::canFilterByWilayah() && $wilayahCol) {
            if ($level === TerritoryLevelForUsm::KAB_KOTA) {
                // Force Kab/Kota level users to their own wilayah
                $query->whereRaw('LOWER('.$table.'.'.$wilayahCol.') LIKE ?', ['%' . $cleanWilayah((string) $user->wilayah) . '%']);
            } elseif ($level === TerritoryLevelForUsm::PROVINSI) {
                if ($request->filled('wilayah')) {
                    // Check if the requested wilayah is under the user's Polda to prevent manipulation
                    $allowedWilayahs = static::wilayahOptions($request)->map(fn($w) => strtolower(trim($w)))->toArray();
                    $reqWilayah = strtolower(trim((string) $request->wilayah));
                    if (in_array($reqWilayah, $allowedWilayahs, true)) {
                        $query->whereRaw('LOWER('.$table.'.'.$wilayahCol.') LIKE ?', ['%' . $cleanWilayah((string) $request->wilayah) . '%']);
                    } else {
                        // Parameter manipulation detected, force empty results
                        $query->whereRaw('1 = 0');
                    }
                }
            } elseif ($level === TerritoryLevelForUsm::NASIONAL && $request->filled('wilayah')) {
                $query->whereRaw('LOWER('.$table.'.'.$wilayahCol.') LIKE ?', ['%' . $cleanWilayah((string) $request->wilayah) . '%']);
            }
        }
    }

    /**
     * @return Collection<int, string>
     */
    public static function daerahOptions(): Collection
    {
        $user = Auth::user();
        if (! $user) {
            return collect();
        }

        $level = static::effectiveLevel();
        if ($level === TerritoryLevelForUsm::PROVINSI || $level === TerritoryLevelForUsm::KAB_KOTA) {
            return collect([$user->daerah])->filter();
        }

        if (SmTerritoryData::isAvailable()) {
            return SmTerritoryData::poldaOptions();
        }

        if (Schema::hasTable('master_provinsi')) {
            return DB::table('master_provinsi')->orderBy('nama')->pluck('nama');
        }

        return DB::table('usm')
            ->whereNotNull('daerah')
            ->where('daerah', '!=', '')
            ->distinct()
            ->orderBy('daerah')
            ->pluck('daerah');
    }

    /**
     * @return Collection<int, string>
     */
    public static function wilayahOptions(Request $request): Collection
    {
        $user = Auth::user();
        if (! $user) {
            return collect();
        }

        $level = static::effectiveLevel();
        if ($level === TerritoryLevelForUsm::KAB_KOTA) {
            return collect([$user->wilayah])->filter();
        }

        $polda = null;
        if ($level === TerritoryLevelForUsm::PROVINSI) {
            $polda = (string) $user->daerah;
        } elseif ($request->filled('daerah') && static::canFilterByDaerah()) {
            $polda = (string) $request->daerah;
        }

        if (SmTerritoryData::isAvailable()) {
            return SmTerritoryData::polresOptions($polda);
        }

        $q = DB::table('usm')->whereNotNull('wilayah')->where('wilayah', '!=', '');

        if ($polda !== null && $polda !== '') {
            $q->whereRaw('LOWER(daerah) = ?', [strtolower($polda)]);
        }

        return $q->distinct()->orderBy('wilayah')->pluck('wilayah');
    }

    /**
     * @return array{daerahList: Collection<int, string>, wilayahList: Collection<int, string>, userLevel: string}
     */
    public static function viewBundle(Request $request): array
    {
        return [
            'daerahList' => static::daerahOptions(),
            'wilayahList' => static::wilayahOptions($request),
            'userLevel' => static::effectiveLevel() ?? HierarchicalHelper::getUserLevel(),
        ];
    }
}
