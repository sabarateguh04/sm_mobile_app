<?php

namespace App\Support;

use App\Helpers\HierarchicalHelper;
use App\Helpers\MenuHelper;
use App\Models\Usm;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;

/**
 * Scope query daftar pengguna (Personil Manager / admin/users) — dipakai index, ekspor, dan statistik dashboard.
 */
final class UserListingScope
{
    /** @var list<int> Role legacy DB sebelum migrasi 7 tier → diperlakukan sebagai admin. */
    private const LEGACY_ADMIN_USER_ROLE_IDS = [5, 8, 9, 16];

    public static function actorIsAdminTier(?Usm $actor): bool
    {
        if (! $actor instanceof Usm) {
            return false;
        }

        $matrixId = MenuHelper::matrixRoleIdForUser($actor);
        if ($matrixId === 2 || $matrixId === 3) {
            return true;
        }

        return in_array((int) ($actor->user_role_id ?? 0), self::LEGACY_ADMIN_USER_ROLE_IDS, true);
    }

    public static function actorIsOperatorTier(?Usm $actor): bool
    {
        if (! $actor instanceof Usm) {
            return false;
        }

        $operatorTier = (int) config('role_menu_matrix.operator_matrix_role_id', 3);
        if (MenuHelper::matrixRoleIdForUser($actor) === $operatorTier) {
            return true;
        }

        $legacyOperatorIds = config('role_menu_matrix.legacy_operator_user_role_ids', [11]);

        return in_array((int) ($actor->user_role_id ?? 0), $legacyOperatorIds, true);
    }

    /**
     * Scope dasar daftar user (teritori, role) — selaras Personil Manager.
     *
     * @param  Builder<\App\Models\Usm>  $query
     */
    public static function applyBaseScopes(Builder $query, ?UserListingScopeContext $ctx = null): void
    {
        if ($ctx !== null) {
            $ctx->listingTerritoryBlocked = false;
            $ctx->listingTerritoryHint = null;
        }

        self::applyExecutiveTerritorialProfileGateForListing($query, $ctx);
        self::applyTerritorialStaffMustHaveWilayahKerjaForListing($query, $ctx);
        self::applyHideSuperadminForNonSuperadminViewer($query);
        self::applyExecutiveOperatorsOnlyScope($query);
        self::applyUserListingTerritoryScope($query, $ctx);
    }

    /**
     * @param  Builder<\App\Models\Usm>  $query
     */
    private static function applyHideSuperadminForNonSuperadminViewer(Builder $query): void
    {
        $actor = Auth::user();
        if (! $actor instanceof Usm) {
            return;
        }
        if (MenuHelper::matrixRoleIdForUser($actor) === 1) {
            return;
        }

        $tiers = config('role_menu_matrix.super_admin_roles', [1]);
        $query->whereDoesntHave('role', function ($q) use ($tiers): void {
            $q->whereIn('role_id', $tiers);
        });
    }

    /**
     * @param  Builder<\App\Models\Usm>  $query
     */
    private static function applyTerritorialStaffMustHaveWilayahKerjaForListing(Builder $query, ?UserListingScopeContext $ctx): void
    {
        $actor = Auth::user();
        if (! $actor instanceof Usm) {
            return;
        }

        $mid = MenuHelper::matrixRoleIdForUser($actor);
        $staffTiers = config('role_menu_matrix.territorial_staff_roles', [2, 3]);
        $isStaff = in_array($mid, $staffTiers, true)
            || self::actorIsAdminTier($actor)
            || self::actorIsOperatorTier($actor);
        if (! $isStaff) {
            return;
        }

        $d = trim((string) ($actor->daerah ?? ''));

        if ($d === '') {
            if (self::actorIsOperatorTier($actor)) {
                if ($ctx !== null) {
                    $ctx->listingTerritoryBlocked = true;
                    $ctx->listingTerritoryHint = 'Operator wajib mengisi provinsi wilayah kerja di profil organisasi sebelum dapat melihat daftar pengguna.';
                }
                $query->whereRaw('1 = 0');
            } elseif (self::actorIsAdminTier($actor) && $ctx !== null && ! KepegawaianListingScope::actorHasScope($actor)) {
                $ctx->listingTerritoryHint = 'Administrator pusat — menampilkan seluruh pengguna.';
            } elseif ($ctx !== null) {
                $ctx->listingTerritoryHint = 'Provinsi wilayah kerja belum diisi di profil Anda — menampilkan seluruh pengguna. Lengkapi profil organisasi agar daftar terbatas ke provinsi kerja.';
            }

            return;
        }

        if (self::actorIsAdminTier($actor) && $ctx !== null && ! KepegawaianListingScope::actorHasScope($actor)) {
            $w = trim((string) ($actor->wilayah ?? ''));
            if ($w !== '') {
                $ctx->listingTerritoryHint = 'Administrator wilayah — menampilkan pengguna di: '.$w.' ('.$d.').';
            } else {
                $ctx->listingTerritoryHint = 'Administrator provinsi — menampilkan pengguna di provinsi: '.$d.'.';
            }
        }
    }

    /**
     * Administrator: kepegawaian (Polda/Polres) > geografis (provinsi/kab) > pusat (semua).
     *
     * @param  Builder<\App\Models\Usm>|QueryBuilder  $query
     */
    public static function applyAdminListingScope(
        Builder|QueryBuilder $query,
        Usm $actor,
        ?UserListingScopeContext $ctx = null,
        string $table = 'usm'
    ): void {
        if (KepegawaianListingScope::actorHasScope($actor)) {
            KepegawaianListingScope::applyScopeLegacyTigaTingkat($query, $table, $actor, true);
            if ($ctx !== null) {
                $ctx->listingTerritoryHint = 'Administrator — '
                    .KepegawaianListingScope::buildHint($actor, true);
            }

            return;
        }

        $daerah = trim((string) ($actor->daerah ?? ''));
        $wilayah = trim((string) ($actor->wilayah ?? ''));

        if ($daerah === '') {
            if ($ctx !== null) {
                $ctx->listingTerritoryHint = 'Administrator pusat — menampilkan seluruh pengguna.';
            }

            return;
        }

        if ($wilayah !== '') {
            self::applyColumnEqualsNormalized($query, $table.'.daerah', $daerah);
            self::applyColumnEqualsNormalized($query, $table.'.wilayah', $wilayah);
            if ($ctx !== null) {
                $ctx->listingTerritoryHint = 'Administrator wilayah — menampilkan pengguna di: '.$wilayah.' ('.$daerah.').';
            }

            return;
        }

        $query->where(function ($q) use ($table, $daerah) {
            self::applyColumnEqualsNormalized($q, $table.'.daerah', $daerah);
            $q->orWhere(function ($q2) use ($table) {
                $q2->where('account_status', 'pending');
                if (Schema::hasColumn($table, 'daerah')) {
                    $q2->where(function ($q3) use ($table) {
                        $q3->whereNull($table.'.daerah')->orWhere($table.'.daerah', '');
                    });
                }
            });
        });

        if ($ctx !== null) {
            $ctx->listingTerritoryHint = 'Administrator provinsi — menampilkan pengguna di provinsi: '.$daerah.'.';
        }
    }

    /**
     * @param  Builder<\App\Models\Usm>|QueryBuilder  $query
     */
    private static function applyColumnEqualsNormalized($query, string $qualifiedColumn, string $value): void
    {
        $query->whereRaw(
            'LOWER(TRIM(COALESCE('.$qualifiedColumn.', ""))) = ?',
            [mb_strtolower(trim($value))]
        );
    }

    public static function actorCanAccessUserRecordForAdmin(Usm $actor, Usm $target): bool
    {
        if (KepegawaianListingScope::actorHasScope($actor)) {
            return KepegawaianListingScope::targetInActorScope($actor, $target);
        }

        $wilayah = trim((string) ($actor->wilayah ?? ''));
        $daerah = trim((string) ($actor->daerah ?? ''));

        if ($wilayah !== '') {
            return strcasecmp(trim((string) ($target->daerah ?? '')), $daerah) === 0
                && strcasecmp(trim((string) ($target->wilayah ?? '')), $wilayah) === 0;
        }

        if ($daerah !== '') {
            return strcasecmp(trim((string) ($target->daerah ?? '')), $daerah) === 0;
        }

        return true;
    }

    /**
     * @param  Builder<\App\Models\Usm>  $query
     */
    private static function applyUserListingTerritoryScope(Builder $query, ?UserListingScopeContext $ctx): void
    {
        $actor = Auth::user();
        if (! $actor instanceof Usm) {
            return;
        }

        if (MenuHelper::matrixRoleIdForUser($actor) === 1) {
            return;
        }

        $mid = MenuHelper::matrixRoleIdForUser($actor);
        $execRoles = config('role_menu_matrix.executive_matrix_roles', [4, 5, 6]);

        if (in_array($mid, $execRoles, true)) {
            self::applyExecutiveListingTerritoryScope($query, $actor, $mid, $ctx);

            return;
        }

        if (self::actorIsAdminTier($actor)) {
            self::applyAdminListingScope($query, $actor, $ctx);

            return;
        }

        if (self::actorIsOperatorTier($actor)) {
            if (KepegawaianListingScope::actorHasScope($actor)) {
                KepegawaianListingScope::applyScopeLegacyTigaTingkat($query, 'usm', $actor, true);
                if ($ctx !== null) {
                    $ctx->listingTerritoryHint = KepegawaianListingScope::buildHint($actor, true);
                }

                return;
            }

            HierarchicalHelper::applyFilter($query);

            return;
        }

        HierarchicalHelper::applyFilter($query);
    }

    /**
     * @param  Builder<\App\Models\Usm>  $query
     */
    private static function applyExecutiveTerritorialProfileGateForListing(Builder $query, ?UserListingScopeContext $ctx): void
    {
        $actor = Auth::user();
        if (! $actor instanceof Usm) {
            return;
        }

        $mid = MenuHelper::matrixRoleIdForUser($actor);
        $provincialExecutive = (int) config('role_menu_matrix.executive_provinsi_matrix_role_id', 6);
        $resortExecutive = (int) config('role_menu_matrix.executive_kab_kota_matrix_role_id', 5);

        if ($mid === $provincialExecutive && trim((string) ($actor->daerah ?? '')) === '') {
            if ($ctx !== null) {
                $ctx->listingTerritoryBlocked = true;
                $ctx->listingTerritoryHint = 'Executive provinsi wajib mengisi provinsi wilayah kerja di profil organisasi sebelum dapat melihat daftar operator.';
            }
            $query->whereRaw('1 = 0');

            return;
        }

        if ($mid === $resortExecutive) {
            if (trim((string) ($actor->daerah ?? '')) === '' || trim((string) ($actor->wilayah ?? '')) === '') {
                if ($ctx !== null) {
                    $ctx->listingTerritoryBlocked = true;
                    $ctx->listingTerritoryHint = 'Executive kab/kota wajib mengisi provinsi dan wilayah kerja di profil organisasi sebelum dapat melihat daftar operator.';
                }
                $query->whereRaw('1 = 0');
            }
        }
    }

    /**
     * @param  Builder<\App\Models\Usm>  $query
     */
    private static function applyExecutiveListingTerritoryScope(Builder $query, Usm $actor, int $matrixRoleId, ?UserListingScopeContext $ctx): void
    {
        if ($matrixRoleId === 7) {
            $daerah = trim((string) ($actor->daerah ?? ''));
            if ($daerah !== '') {
                self::applyColumnEqualsNormalized($query, 'usm.daerah', $daerah);
                if ($ctx !== null) {
                    $ctx->listingTerritoryHint = 'Menampilkan operator di provinsi: '.$daerah.'.';
                }
            }

            return;
        }

        if ($matrixRoleId === (int) config('role_menu_matrix.executive_provinsi_matrix_role_id', 6)) {
            $daerah = trim((string) ($actor->daerah ?? ''));
            $wilayah = trim((string) ($actor->wilayah ?? ''));

            if ($daerah !== '' && $wilayah !== '') {
                self::applyColumnEqualsNormalized($query, 'usm.daerah', $daerah);
                self::applyColumnEqualsNormalized($query, 'usm.wilayah', $wilayah);
                if ($ctx !== null) {
                    $ctx->listingTerritoryHint = 'Menampilkan operator di wilayah: '.$wilayah.' ('.$daerah.').';
                }
            }

            return;
        }

        if ($matrixRoleId === (int) config('role_menu_matrix.executive_kab_kota_matrix_role_id', 5)) {
            $daerah = trim((string) ($actor->daerah ?? ''));
            $wilayah = trim((string) ($actor->wilayah ?? ''));

            if ($daerah !== '' && $wilayah !== '') {
                self::applyColumnEqualsNormalized($query, 'usm.daerah', $daerah);
                self::applyColumnEqualsNormalized($query, 'usm.wilayah', $wilayah);
                if ($ctx !== null) {
                    $ctx->listingTerritoryHint = 'Menampilkan operator di wilayah: '.$wilayah.' ('.$daerah.').';
                }

                return;
            }

            HierarchicalHelper::applyFilter($query, TerritoryLevelForUsm::KAB_KOTA);
        }
    }

    /**
     * @param  Builder<\App\Models\Usm>  $query
     */
    private static function applyExecutiveOperatorsOnlyScope(Builder $query): void
    {
        $actor = Auth::user();
        if (! $actor instanceof Usm) {
            return;
        }

        if (MenuHelper::isExecutiveNationalUser($actor)) {
            return;
        }

        $mid = MenuHelper::matrixRoleIdForUser($actor);
        $execRoles = config('role_menu_matrix.executive_matrix_roles', [4, 5, 6]);
        if (! in_array($mid, $execRoles, true)) {
            return;
        }

        $query->whereIn('user_role_id', MenuHelper::personilBoOperatorUserRoleIds());
    }

    /**
     * Apakah actor boleh lihat/ubah record user (selaras assertActorCanAccessUserRecord di UserController).
     */
    public static function actorCanAccessUserRecord(Usm $actor, Usm $target): bool
    {
        if (MenuHelper::matrixRoleIdForUser($actor) === 1) {
            return true;
        }

        $targetTier = MenuHelper::matrixRoleIdForUser($target);
        if (in_array($targetTier, config('role_menu_matrix.super_admin_roles', [1]), true)) {
            return false;
        }

        $actorTier = MenuHelper::matrixRoleIdForUser($actor);
        $execRoles = config('role_menu_matrix.executive_matrix_roles', [4, 5, 6]);
        if (
            in_array($actorTier, $execRoles, true)
            && ! MenuHelper::isExecutiveNationalUser($actor)
            && ! MenuHelper::isPersonilBoOperatorUser($target)
        ) {
            return false;
        }

        if (in_array($actorTier, $execRoles, true)) {
            return self::executiveTerritoryAllowsUserRecord($actor, $target, $actorTier);
        }

        if (self::actorIsAdminTier($actor)) {
            return self::actorCanAccessUserRecordForAdmin($actor, $target);
        }

        if (KepegawaianListingScope::actorHasScope($actor)) {
            return KepegawaianListingScope::targetInActorScope($actor, $target);
        }

        if (trim((string) ($actor->wilayah ?? '')) !== '') {
            return strcasecmp(trim((string) ($target->daerah ?? '')), trim((string) ($actor->daerah ?? ''))) === 0
                && strcasecmp(trim((string) ($target->wilayah ?? '')), trim((string) ($actor->wilayah ?? ''))) === 0;
        }

        if (trim((string) ($actor->daerah ?? '')) !== '') {
            return strcasecmp(trim((string) ($target->daerah ?? '')), trim((string) $actor->daerah)) === 0;
        }

        return true;
    }

    private static function executiveTerritoryAllowsUserRecord(Usm $actor, Usm $target, int $matrixRoleId): bool
    {
        if ($matrixRoleId === 7) {
            $daerah = trim((string) ($actor->daerah ?? ''));
            if ($daerah === '') {
                return true;
            }

            return strcasecmp(trim((string) ($target->daerah ?? '')), $daerah) === 0;
        }

        if ($matrixRoleId === (int) config('role_menu_matrix.executive_provinsi_matrix_role_id', 6)) {
            $daerah = trim((string) ($actor->daerah ?? ''));
            $wilayah = trim((string) ($actor->wilayah ?? ''));

            if ($daerah !== '' && strcasecmp(trim((string) ($target->daerah ?? '')), $daerah) !== 0) {
                return false;
            }

            if ($wilayah !== '' && strcasecmp(trim((string) ($target->wilayah ?? '')), $wilayah) !== 0) {
                return false;
            }

            return true;
        }

        if ($matrixRoleId === (int) config('role_menu_matrix.executive_kab_kota_matrix_role_id', 5)) {
            $daerah = trim((string) ($actor->daerah ?? ''));
            $wilayah = trim((string) ($actor->wilayah ?? ''));

            if ($daerah !== '' && strcasecmp(trim((string) ($target->daerah ?? '')), $daerah) !== 0) {
                return false;
            }

            if ($wilayah !== '' && strcasecmp(trim((string) ($target->wilayah ?? '')), $wilayah) !== 0) {
                return false;
            }
        }

        return true;
    }
}
