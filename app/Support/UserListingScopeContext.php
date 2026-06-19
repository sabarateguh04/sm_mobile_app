<?php

namespace App\Support;

/**
 * Status UI opsional saat menerapkan scope daftar Personil Manager (index).
 */
final class UserListingScopeContext
{
    public bool $listingTerritoryBlocked = false;

    public ?string $listingTerritoryHint = null;
}
