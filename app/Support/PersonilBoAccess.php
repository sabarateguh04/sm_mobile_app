<?php

namespace App\Support;

use App\Http\Controllers\PersonilPosyanController;
use App\Models\Usm;
use Illuminate\Foundation\Auth\User as AuthenticatedUser;

/**
 * Akses mutasi operator Kuat Personil BO — selaras scope daftar ({@see PersonilPosyanController::applyPersonilBoTerritoryScope}).
 */
final class PersonilBoAccess
{
    public static function operatorInListingScope(AuthenticatedUser $actor, object $row): bool
    {
        if (! $actor instanceof Usm) {
            return false;
        }

        $id = (int) ($row->id_usm ?? 0);
        if ($id <= 0) {
            return false;
        }

        /** @var PersonilPosyanController $controller */
        $controller = app(PersonilPosyanController::class);

        return $controller->operatorIsInListingScope($id);
    }
}
