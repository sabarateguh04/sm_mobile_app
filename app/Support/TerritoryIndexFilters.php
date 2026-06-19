<?php

namespace App\Support;

use App\Helpers\HierarchicalHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

final class TerritoryIndexFilters
{
    public static function canFilterByDaerah(): bool
    {
        return HierarchicalHelper::viewerBypassesTerritoryScope()
            || (Auth::user() && empty(Auth::user()->wilayah));
    }

    public static function canFilterByWilayah(): bool
    {
        $user = Auth::user();
        if (! $user) {
            return false;
        }

        if (HierarchicalHelper::viewerBypassesTerritoryScope()) {
            return true;
        }

        $mid = \App\Helpers\MenuHelper::matrixRoleIdForUser($user);
        // Provincial admin (2) or Provincial executive (6) can filter by wilayah within their province
        if (in_array($mid, [2, 6], true)) {
            return true;
        }

        return empty($user->wilayah);
    }

    public static function applyUsmFilters($query, Request $request): void
    {
        if ($request->filled('daerah') && static::canFilterByDaerah()) {
            $query->whereRaw('LOWER(usm.daerah) = ?', [strtolower((string) $request->daerah)]);
        }

        if ($request->filled('wilayah') && static::canFilterByWilayah()) {
            $query->whereRaw('LOWER(usm.wilayah) = ?', [strtolower((string) $request->wilayah)]);
        }
    }

    public static function applyColumnFilters($query, Request $request, string $table): void
    {
        if ($request->filled('daerah') && Schema::hasColumn($table, 'daerah') && static::canFilterByDaerah()) {
            $query->whereRaw('LOWER('.$table.'.daerah) = ?', [strtolower((string) $request->daerah)]);
        }

        if ($request->filled('wilayah') && Schema::hasColumn($table, 'wilayah') && static::canFilterByWilayah()) {
            $query->whereRaw('LOWER('.$table.'.wilayah) = ?', [strtolower((string) $request->wilayah)]);
        }
    }

    /**
     * @return Collection<int, string>
     */
    public static function daerahOptions(): Collection
    {
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
        $q = DB::table('usm')->whereNotNull('wilayah')->where('wilayah', '!=', '');

        if ($request->filled('daerah') && static::canFilterByDaerah()) {
            $q->whereRaw('LOWER(daerah) = ?', [strtolower((string) $request->daerah)]);
        } elseif ($user = Auth::user()) {
            if (! HierarchicalHelper::viewerBypassesTerritoryScope()) {
                $mid = \App\Helpers\MenuHelper::matrixRoleIdForUser($user);
                $isProvincialExecutive = ($mid === 6);

                if (! empty($user->wilayah) && ! $isProvincialExecutive) {
                    $q->whereRaw('LOWER(wilayah) = ?', [strtolower((string) $user->wilayah)]);
                } elseif (! empty($user->daerah)) {
                    $q->whereRaw('LOWER(daerah) = ?', [strtolower((string) $user->daerah)]);
                }
            }
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
            'userLevel' => HierarchicalHelper::getUserLevel(),
        ];
    }
}
