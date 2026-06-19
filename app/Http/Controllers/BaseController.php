<?php

namespace App\Http\Controllers;

use App\Helpers\HierarchicalHelper;
use App\Support\RoleMutationPolicy;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as LaravelBaseController;
use Illuminate\Support\Facades\Auth;

abstract class BaseController extends LaravelBaseController
{
    use AuthorizesRequests, ValidatesRequests;

    /**
     * Apply hierarchical filter to query
     */
    protected function applyHierarchicalFilter($query, $level = null)
    {
        return HierarchicalHelper::applyFilter($query, $level);
    }

    /**
     * Get current user level
     */
    protected function getUserLevel()
    {
        return HierarchicalHelper::getUserLevel();
    }

    /**
     * Check if user can access specific level
     */
    protected function canAccessLevel($requiredLevel)
    {
        return HierarchicalHelper::canAccess($requiredLevel);
    }

    /** Cek kepemilikan baris operasional (`piket`) sesuai mode role. */
    protected function assertOperatorOwnsOperationalRow(object $row, ?string $table = null): void
    {
        $tableName = $table;
        if ($tableName === null && property_exists($this, 'tableName') && is_string($this->tableName)) {
            $tableName = $this->tableName;
        }

        RoleMutationPolicy::repairOrphanOperationalPiketForOperator($row, $tableName);
        RoleMutationPolicy::assertCanEditOrDelete(Auth::user(), $row, $tableName);
    }

    protected function canMutateOperationalRecord(object $row, ?string $table = null): bool
    {
        $tableName = $table;
        if ($tableName === null && property_exists($this, 'tableName') && is_string($this->tableName)) {
            $tableName = $this->tableName;
        }

        return RoleMutationPolicy::canMutateOperationalRow(Auth::user(), $row, $tableName);
    }

    /**
     * @return array{canMutateRecord: bool, tableName: string|null}
     */
    protected function operationalRecordViewFlags(object $row, ?string $table = null): array
    {
        $tableName = $table;
        if ($tableName === null && property_exists($this, 'tableName') && is_string($this->tableName)) {
            $tableName = $this->tableName;
        }

        return [
            'canMutateRecord' => $this->canMutateOperationalRecord($row, $tableName),
            'tableName' => $tableName,
        ];
    }
}
