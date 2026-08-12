<?php

namespace App\Support;

use App\Helpers\MenuHelper;
use App\Models\Usm;
use Illuminate\Foundation\Auth\User as AuthenticatedUser;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;

/**
 * Kebijakan mutasi data operasional per tier role (matriks 1–7).
 *
 * Mode per role (config `role_mutation_modes`):
 * - full      (1): semua data
 * - territory (2): CRUD di wilayah kerja (filter geografis terpisah)
 * - own       (3): ubah/hapus baris `piket`/`id_usm` = akun sendiri
 * - view      (5–7): executive — hanya lihat (Executive2 = provinsi/Polda, setara AdminPolda tanpa CRUD)
 *
 * Modul Kuat Personil BO: operator hanya lihat personil lain; ubah/hapus hanya akun sendiri.
 */
final class RoleMutationPolicy
{
  public const MODE_FULL = 'full';

  public const MODE_TERRITORY = 'territory';

  public const MODE_OWN = 'own';

  public const MODE_VIEW = 'view';

  /**
   * @return list<int>
   */
  public static function viewOnlyMatrixRoles(): array
  {
    $roles = config('role_menu_matrix.view_only_matrix_roles');

    return is_array($roles) ? array_map('intval', $roles) : [4, 5, 6, 7];
  }

  public static function mutationMode(?AuthenticatedUser $user): string
  {
    if (! $user) {
      return self::MODE_VIEW;
    }

    if (MenuHelper::isSuperAdminForLayout($user)) {
      return self::MODE_FULL;
    }

    if (MenuHelper::isExecutiveForLayout($user)) {
      return self::MODE_VIEW;
    }

    $mid = MenuHelper::matrixRoleIdForUser($user);
    $modes = config('role_menu_matrix.role_mutation_modes', []);

    if (is_array($modes) && isset($modes[$mid])) {
      return (string) $modes[$mid];
    }

    if (self::isOperatorRole($user)) {
      return self::MODE_OWN;
    }

    if (in_array($mid, self::viewOnlyMatrixRoles(), true)) {
      return self::MODE_VIEW;
    }

    return self::MODE_TERRITORY;
  }

  public static function isViewOnlyRole(?AuthenticatedUser $user): bool
  {
    return self::mutationMode($user) === self::MODE_VIEW;
  }

  public static function isOperatorRole(?AuthenticatedUser $user): bool
  {
    return MenuHelper::isOperatorForLayout($user);
  }

  public static function deniesAllMutations(?AuthenticatedUser $user): bool
  {
    return self::mutationMode($user) === self::MODE_VIEW;
  }

  public static function deniesCreate(?AuthenticatedUser $user): bool
  {
    return self::deniesAllMutations($user);
  }

  /**
   * ID akun login (USM) — gunakan kolom `id_usm` model bila ada.
   */
  public static function resolveActorUserId(?AuthenticatedUser $user): int
  {
    if (! $user) {
      return 0;
    }

    $candidates = [];
    if (isset($user->id_usm) && $user->id_usm !== '' && $user->id_usm !== null) {
      $candidates[] = (int) $user->id_usm;
    }

    $authKey = $user->getAuthIdentifierName();
    if ($authKey && $authKey !== 'id_usm' && isset($user->{$authKey})) {
      $candidates[] = (int) $user->{$authKey};
    }

    $candidates[] = (int) ($user->getAuthIdentifier() ?? 0);

    if (method_exists($user, 'getKey')) {
      $key = $user->getKey();
      if ($key !== null && $key !== '' && (int) $key > 0) {
        $candidates[] = (int) $key;
      }
    }

    foreach ($candidates as $id) {
      if ($id > 0) {
        return $id;
      }
    }

    return 0;
  }

  /**
   * Nilai kolom `piket` untuk insert — selalu `id_usm` akun login.
   */
  public static function resolveActorPiketForStore(?AuthenticatedUser $user): string
  {
    $actorId = self::resolveActorUserId($user);
    if ($actorId <= 0) {
      abort(403, 'Akun tidak valid — tidak dapat mencatat pemilik data.');
    }

    return (string) $actorId;
  }

  /**
   * Apakah baris secara eksplisit dimiliki akun ini (`id_usm` / `piket` cocok)?
   */
  public static function isRowOwnedByActor(?AuthenticatedUser $user, object $row, ?string $table = null): bool
  {
    if (! $user) {
      return false;
    }

    $actorId = self::resolveActorUserId($user);
    $ownerId = self::resolveRecordOwnerUserId($row, $table);

    return $actorId > 0 && $ownerId !== null && $ownerId > 0 && (int) $ownerId === (int) $actorId;
  }

  /**
   * ID pemilik baris dari kolom `id_usm` (layanan_masyarakat) atau `piket` / `id_usm` (modul lain).
   */
  public static function resolveRecordOwnerUserId(object $row, ?string $table = null): ?int
  {
    $isInteraksi = $table === 'layanan_masyarakat' || isset($row->id_interaksi);

    if ($isInteraksi) {
      if (isset($row->id_usm) && $row->id_usm !== '' && $row->id_usm !== null && (int) $row->id_usm > 0) {
        return (int) $row->id_usm;
      }

      return null;
    }

    if (isset($row->piket) && $row->piket !== '' && $row->piket !== null) {
      $piket = trim((string) $row->piket);
      if ($piket !== '') {
        if (is_numeric($piket) && (int) $piket > 0) {
          return (int) $piket;
        }

        // Legacy SBO: `piket` kadang menyimpan username / email, bukan id_usm.
        $ownerId = Usm::query()->where('username', $piket)->value('id_usm');
        if ($ownerId !== null && (int) $ownerId > 0) {
          return (int) $ownerId;
        }

        $ownerId = Usm::query()->where('email', $piket)->value('id_usm');
        if ($ownerId !== null && (int) $ownerId > 0) {
          return (int) $ownerId;
        }
      }
    }

    if (isset($row->id_usm) && $row->id_usm !== '' && $row->id_usm !== null && (int) $row->id_usm > 0) {
      return (int) $row->id_usm;
    }

    return null;
  }

    /**
     * Apakah user boleh ubah/hapus baris operasional ini?
     */
    public static function canMutateOperationalRow(?AuthenticatedUser $user, object $row, ?string $table = null): bool
    {
        if (! $user) {
            return false;
        }

        $table = self::resolveOperationalTableName($row, $table);

        if ($table !== null && self::tableRequiresAccountOwnership($table)) {
            return self::canMutateAccountOwnedRow($user, $row, $table);
        }

        return match (self::mutationMode($user)) {
            self::MODE_FULL => true,
            self::MODE_TERRITORY => self::territoryStaffCanMutateRow($user, $row, $table),
            self::MODE_VIEW => false,
            self::MODE_OWN => self::operatorOwnsRow($user, $row, $table),
            default => false,
        };
    }

    /**
     * Deteksi tabel operasional dari baris (mis. id_interaksi → layanan_masyarakat).
     */
    private static function resolveOperationalTableName(object $row, ?string $table): ?string
    {
        if ($table !== null && $table !== '') {
            return $table;
        }

        if (isset($row->id_interaksi)) {
            return 'layanan_masyarakat';
        }

        return null;
    }

    /**
     * Tabel `account_owned_operational_tables`: hanya pemilik akun (`id_usm`) yang boleh ubah/hapus.
     */
    private static function canMutateAccountOwnedRow(AuthenticatedUser $user, object $row, string $table): bool
    {
        if (self::deniesAllMutations($user)) {
            return false;
        }

        if (MenuHelper::isSuperAdminForLayout($user) || self::mutationMode($user) === self::MODE_FULL) {
            return true;
        }

        return self::accountOwnsRow($user, $row, $table);
    }

    /**
     * @deprecated Fallback wilayah dinonaktifkan — wajib id_usm = akun login.
     * @return list<string>
     */
    private static function accountOwnedLegacyTerritoryFallbackTables(): array
    {
        $tables = config('role_menu_matrix.account_owned_legacy_territory_fallback', []);

        return is_array($tables) ? array_map('strval', $tables) : [];
    }

    private static function accountOwnsRow(AuthenticatedUser $user, object $row, ?string $table = null): bool
    {
        $table = self::resolveOperationalTableName($row, $table);
        $actorId = self::resolveActorUserId($user);
        if ($actorId <= 0) {
            return false;
        }

        $ownerId = self::resolveRecordOwnerUserId($row, $table);
        if ($ownerId === null || $ownerId <= 0) {
            return false;
        }

        return (int) $ownerId === (int) $actorId;
    }

    /**
     * @return list<string>
     */
    private static function accountOwnedOperationalTables(): array
    {
        $tables = config('role_menu_matrix.account_owned_operational_tables', ['layanan_masyarakat']);

        return is_array($tables) ? array_map('strval', $tables) : ['layanan_masyarakat'];
    }

    private static function tableRequiresAccountOwnership(string $table): bool
    {
        return in_array($table, self::accountOwnedOperationalTables(), true);
    }

    /**
     * Admin wilayah: hanya baris dalam provinsi/kab-kota kerja masing-masing.
     */
    private static function territoryStaffCanMutateRow(AuthenticatedUser $user, object $row, ?string $table = null): bool
    {
        if (MenuHelper::isSuperAdminForLayout($user)) {
            return true;
        }

        $userDaerah = trim((string) ($user->daerah ?? ''));
        $userWilayah = trim((string) ($user->wilayah ?? ''));

        if ($userDaerah === '' && $userWilayah === '') {
            return true;
        }

        $rowDaerah = trim((string) ($row->daerah ?? $row->usm_daerah ?? ''));
        $rowWilayah = trim((string) ($row->wilayah ?? $row->usm_wilayah ?? ''));

        if ($rowDaerah === '') {
            $ownerId = self::resolveRecordOwnerUserId($row, $table);
            if ($ownerId !== null && $ownerId > 0) {
                $ownerUsm = \App\Models\Usm::find($ownerId);
                if ($ownerUsm) {
                    $rowDaerah = trim((string) ($ownerUsm->daerah ?? ''));
                    $rowWilayah = trim((string) ($ownerUsm->wilayah ?? ''));
                }
            }
        }

        if ($userWilayah !== '') {
            return $rowDaerah !== '' && $rowWilayah !== ''
                && strcasecmp($rowDaerah, $userDaerah) === 0
                && strcasecmp($rowWilayah, $userWilayah) === 0;
        }

        if ($userDaerah !== '') {
            return $rowDaerah !== '' && strcasecmp($rowDaerah, $userDaerah) === 0;
        }

        return true;
    }

  /**
   * Cocokkan akun login dengan kolom `piket` / `id_usm` baris (termasuk legacy string).
   */
  public static function actorMatchesOperationalOwner(?AuthenticatedUser $user, object $row, ?string $table = null): bool
  {
    if (! $user) {
      return false;
    }

    $ownerId = self::resolveRecordOwnerUserId($row, $table);
    $actorId = self::resolveActorUserId($user);

    if ($actorId > 0 && $ownerId !== null && $ownerId > 0 && (int) $ownerId === (int) $actorId) {
      return true;
    }

    if (! isset($row->piket)) {
      return false;
    }

    $piket = trim((string) $row->piket);
    if ($piket === '') {
      return false;
    }

    $candidates = array_unique(array_filter([
      (string) $actorId,
      (string) ($user->id_usm ?? ''),
      (string) ($user->getAuthIdentifier() ?? ''),
      trim((string) ($user->username ?? '')),
      trim((string) ($user->email ?? '')),
    ], static fn (string $v): bool => $v !== ''));

    foreach ($candidates as $candidate) {
      if (strcasecmp($candidate, $piket) === 0) {
        return true;
      }
    }

    return false;
  }

  /**
   * Perbaiki baris operasional tanpa pemilik valid (`piket` kosong / orphan) untuk operator MODE_OWN.
   */
  public static function repairOrphanOperationalPiketForOperator(object $row, ?string $table = null): void
  {
    /** @var AuthenticatedUser|null $user */
    $user = Auth::user();
    if (! $user instanceof AuthenticatedUser || self::mutationMode($user) !== self::MODE_OWN) {
      return;
    }

    if (! $row instanceof \Illuminate\Database\Eloquent\Model) {
      return;
    }

    $tableName = $table ?? $row->getTable();
    if (! Schema::hasColumn($tableName, 'piket')) {
      return;
    }

    if (self::actorMatchesOperationalOwner($user, $row, $tableName)) {
      return;
    }

    $piket = trim((string) ($row->piket ?? ''));
    $actorId = self::resolveActorUserId($user);
    if ($actorId <= 0) {
      return;
    }

    $actorPiket = (string) $actorId;
    $needsRepair = false;

    if ($piket === '' || $piket === '0') {
      $needsRepair = true;
    } elseif (is_numeric($piket) && ! Usm::query()->where('id_usm', (int) $piket)->exists()) {
      $needsRepair = true;
    } elseif (! is_numeric($piket)
        && ! Usm::query()->where('username', $piket)->exists()
        && ! Usm::query()->where('email', $piket)->exists()) {
      $needsRepair = true;
    } elseif ($tableName === 'dataperangkat' && is_numeric($piket)) {
      $recordId = (int) ($row->id_perangkat ?? $row->getKey() ?? 0);
      if ($recordId > 0 && (int) $piket === $recordId && (int) $piket !== $actorId) {
        $needsRepair = true;
      }
    }

    if ($needsRepair) {
      $row->forceFill(['piket' => $actorPiket])->save();
    }
  }

  /**
   * Operator/admin mutasi baris dataperangkat — perbaiki `piket` legacy sebelum cek kepemilikan.
   */
  public static function assertDataperangkatMutationAllowed(?AuthenticatedUser $user, object $row, bool $repair = true): void
  {
    if (! $user) {
      abort(403, 'Anda hanya dapat mengubah atau menghapus data yang Anda input sendiri, bukan data operator lain.');
    }

    if (self::deniesAllMutations($user)) {
      self::assertCanEditOrDelete($user, $row, 'dataperangkat');

      return;
    }

    $mode = self::mutationMode($user);
    if ($mode === self::MODE_FULL || $mode === self::MODE_TERRITORY) {
      self::assertCanEditOrDelete($user, $row, 'dataperangkat');

      return;
    }

    if ($mode !== self::MODE_OWN) {
      self::assertCanEditOrDelete($user, $row, 'dataperangkat');

      return;
    }

    if ($row instanceof \Illuminate\Database\Eloquent\Model) {
      $row->refresh();
      if ($repair) {
        self::repairOrphanOperationalPiketForOperator($row, 'dataperangkat');
        $row->refresh();
      }
    }

    if (self::canMutateDataperangkatRow($user, $row)) {
      return;
    }

    abort(403, 'Anda hanya dapat mengubah atau menghapus data yang Anda input sendiri, bukan data operator lain.');
  }

  /**
   * Apakah baris dataperangkat boleh di-edit/hapus oleh user ini (tanpa menulis ke DB).
   */
  public static function canMutateDataperangkatRow(?AuthenticatedUser $user, object $row): bool
  {
    if (! $user) {
      return false;
    }

    if (self::deniesAllMutations($user)) {
      return false;
    }

    $mode = self::mutationMode($user);
    if ($mode === self::MODE_FULL || $mode === self::MODE_TERRITORY) {
      return self::canMutateOperationalRow($user, $row, 'dataperangkat');
    }

    if ($mode !== self::MODE_OWN) {
      return false;
    }

    if (self::actorMatchesOperationalOwner($user, $row, 'dataperangkat')) {
      return true;
    }

    $recordId = (int) ($row->id_perangkat ?? 0);
    $piket = trim((string) ($row->piket ?? ''));
    $actorId = self::resolveActorUserId($user);

    if ($actorId <= 0) {
      return false;
    }

    if ($piket === '' || $piket === '0') {
      return true;
    }

    if ($recordId > 0 && is_numeric($piket) && (int) $piket === $recordId && (int) $piket !== $actorId) {
      return true;
    }

    if (is_numeric($piket) && ! Usm::query()->where('id_usm', (int) $piket)->exists()) {
      return true;
    }

    if (! is_numeric($piket)
        && ! Usm::query()->where('username', $piket)->exists()
        && ! Usm::query()->where('email', $piket)->exists()) {
      return true;
    }

    return false;
  }

  private static function operatorOwnsRow(AuthenticatedUser $user, object $row, ?string $table = null): bool
  {
    return self::actorMatchesOperationalOwner($user, $row, $table);
  }

  /**
   * Kuat Personil BO — operator hanya ubah/hapus akun sendiri; admin/wilayah kelola personil di scope.
   */
  public static function canMutatePersonilRow(?AuthenticatedUser $user, object $row): bool
  {
    if (! $user || self::deniesAllMutations($user)) {
      return false;
    }

    $rowId = (int) ($row->id_usm ?? 0);
    $actorId = (int) $user->getAuthIdentifier();
    $isOwnRow = $rowId > 0 && $rowId === $actorId;

    if (self::mutationMode($user) === self::MODE_OWN) {
      return $isOwnRow;
    }

    if (self::mutationMode($user) === self::MODE_TERRITORY) {
      if (self::territoryStaffCanMutateRow($user, $row)) {
        return true;
      }

      if ($user instanceof Usm && UserListingScope::actorIsAdminTier($user)) {
        return PersonilBoAccess::operatorInListingScope($user, $row);
      }

      return false;
    }

    return true;
  }

  public static function assertOperatorPersonilModuleReadOnly(?AuthenticatedUser $user): void
  {
    if ($user && self::mutationMode($user) === self::MODE_OWN) {
      abort(403, 'Operator hanya dapat melihat personil operator lain di wilayah kerja, tidak dapat menambah personil baru.');
    }
  }

  /**
   * @return list<string>
   */
  public static function bodyClasses(?AuthenticatedUser $user): array
  {
    $classes = [];
    if (self::deniesAllMutations($user)) {
      $classes[] = 'sbo-view-only';
    }

    return $classes;
  }

  public static function assertCanCreate(?AuthenticatedUser $user): void
  {
    if (self::deniesCreate($user)) {
      abort(403, 'Akun Anda hanya dapat melihat data sesuai wilayah kerja, tidak dapat menambah data.');
    }
  }

  public static function assertCanEditOrDelete(?AuthenticatedUser $user, ?object $record = null, ?string $table = null): void
  {
    if (self::deniesAllMutations($user)) {
      abort(403, 'Akun executive/eksternal hanya dapat melihat data sesuai wilayah kerja Anda.');
    }

    if ($record === null) {
      return;
    }

    if (! self::canMutateOperationalRow($user, $record, $table)) {
      $mode = self::mutationMode($user);
      if ($table !== null && self::tableRequiresAccountOwnership($table)) {
        abort(403, 'Anda hanya dapat mengubah atau menghapus data yang Anda input sendiri.');
      }
      if ($mode === self::MODE_OWN) {
        abort(403, 'Anda hanya dapat mengubah atau menghapus data yang Anda input sendiri, bukan data operator lain.');
      }

      if ($mode === self::MODE_TERRITORY) {
        abort(403, 'Anda hanya dapat mengubah atau menghapus data di wilayah kerja Anda.');
      }

      abort(403, 'Anda tidak memiliki izin untuk mengubah atau menghapus data ini.');
    }
  }

  public static function assertOperatorCanMutatePersonilRow(?AuthenticatedUser $user, object $row): void
  {
    if (! self::canMutatePersonilRow($user, $row)) {
      if ($user && self::mutationMode($user) === self::MODE_OWN) {
        abort(403, 'Operator hanya dapat melihat personil operator lain di wilayah kerja, tidak dapat mengubah atau menghapus data personil tersebut.');
      }

      abort(403, 'Anda tidak dapat mengubah atau menghapus personil operator lain dari halaman ini.');
    }
  }

  /**
   * @param  string  $actionMethod  Nama method controller (create, store, edit, update, destroy, editField, verify, …)
   */
  public static function assertRouteMutationAllowed(?AuthenticatedUser $user, string $actionMethod, ?string $routeName = null): void
  {
    if (! $user || MenuHelper::isSuperAdminForLayout($user)) {
      return;
    }

    $action = strtolower(trim($actionMethod));
    $routeName = (string) ($routeName ?? '');

    if (self::mutationMode($user) === self::MODE_OWN && str_starts_with($routeName, 'personil.manager.')) {
      if (in_array($action, ['create', 'store'], true)) {
        self::assertOperatorPersonilModuleReadOnly($user);
      }
    }

    if (in_array($action, ['create', 'store'], true)) {
      self::assertCanCreate($user);
    }

    if (in_array($action, ['edit', 'update', 'destroy', 'editfield', 'verify'], true)) {
      self::assertCanEditOrDelete($user);
    }
  }
}
