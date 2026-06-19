<?php

namespace App\Traits;

use App\Helpers\HierarchicalHelper;
use App\Support\TerritoryLevelForUsm;
use Illuminate\Database\Eloquent\Builder;

trait HasHierarchicalFilter
{
    /**
     * @param  Builder  $query
     * @param  string|null  $level  {@see TerritoryLevelForUsm} (+ alias lama `daerah` / `wilayah`)
     */
    public function scopeApplyHierarchicalFilter($query, $level = null)
    {
        return HierarchicalHelper::applyFilter($query, $level);
    }

    /**
     * @return 'nasional'|'provinsi'|'kab_kota'|null
     */
    public static function getUserLevel()
    {
        return HierarchicalHelper::getUserLevel();
    }

    public static function canAccessLevel($requiredLevel): bool
    {
        return HierarchicalHelper::canAccess((string) $requiredLevel);
    }
}
