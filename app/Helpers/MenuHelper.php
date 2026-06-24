<?php

namespace App\Helpers;

use App\Support\TerritoryLevelForUsm;
use Illuminate\Foundation\Auth\User as AuthenticatedUser;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Schema;

class MenuHelper
{
    /**
     * Label grup submenu yang hanya di topbar/header, bukan sidebar (semua role).
     *
     * @see config/operator_header_menu.php
     * @see config/superadmin_header_menu.php
     */
    private const SIDEBAR_EXCLUDED_GROUP_LABELS = [
        'IT Asset Management',
    ];

    /** Kunci `ref` master/referensi — selaras ReferensiController */
    private const REFERENSI_REF_KEYS = [
        'faktor', 'jenisterminal', 'jenisjalan', 'levelgangguan', 'jenisops', 'sarpras',
        'peristiwa', 'giat', 'penyebab', 'kattempatibadah', 'katwisata', 'katperangkat',
        'ownercctv', 'fasilitas', 'jeniscontigency', 'prasjal', 'katlayanan', 'katlayanandata', 'katfaskes',
        'saluran', 'temalayanan', 'durasi', 'situasilalin', 'situasicuaca', 'gerbangkota', 'araharus',
        'kategorikonten', 'metodemedia', 'mediapublishing', 'sentimen',
    ];

    /**
     * Path submenu sidebar → nama route Laravel *.index (satu sumber kebenaran untuk URL & active state).
     *
     * @return array<string, string>
     */
    protected static function pathToIndexRouteMap(): array
    {
        return [
            'home' => 'home',
            'map' => 'map.index',
            'layanan/public-service' => 'layanan.public_service.index',
            'layanan/masyarakat' => 'layanan.masyarakat.index',
            'layanan/data' => 'layanan.data.index',
            'media/manage' => 'media.manage.index',
            'media/analytic' => 'media.analytic.index',
            'personil/manager' => 'personil.manager.index',
            'terminal' => 'terminal.index',
            't_datajalan' => 't_datajalan.index',
            'jalan_gangguan' => 'jalan_gangguan.index',
            'admin/users' => 'admin.users.index',
            'roles' => 'admin.roles.index',
            'role_permissions' => 'admin.role_permissions.index',
            'turjagwali' => 'turjagwali.index',
            'auditarus' => 'auditarus.index',
            'pnpangkutan' => 'pnpangkutan.index',
            'contigency' => 'contigency.index',
            'trafficstuck' => 'trafficstuck.index',
            'report/traffic-analytic' => 'report.traffic-analytic.index',
            'traffics' => 'traffics.index',
            'trafficdalkot' => 'report.traffic-analytic.index',
            'profilposko' => 'profilposko.index',
            'arusmudik' => 'arusmudik.index',
            'ops_wanjol' => 'ops_wanjol.index',
            'wil/arusmudik' => 'wil.arusmudik.index',
            'da/arusmudik' => 'da.arusmudik.index',
            'nas/arusmudik' => 'nas.arusmudik.index',
            'objekwisata' => 'objekwisata.index',
            'kunjunganwisata' => 'kunjunganwisata.index',
            'tempatibadah' => 'tempatibadah.index',
            'faskes' => 'faskes.index',
            'patrolicctv' => 'patrolicctv.index',
            'cctv' => 'patrolicctv.index',
            'data-cctv' => 'datacctv.index',
            'datacctv' => 'datacctv.index',
            'dataperangkat' => 'dataperangkat.index',
            'itsupport' => 'itsupport.index',
            'master/daerah' => 'master.daerah.index',
            'master/pusat' => 'master.pusat.index',
            'master/kab-kota' => 'master.resort.index',
            'master/resort' => 'master.resort.index',
            'master/kl-pusat' => 'master.kl-pusat.index',
            'master/kl-daerah' => 'master.kl-daerah.index',
            'master/kl-resort' => 'master.kl-resort.index',
            'master/referensi' => 'master.referensi.index',
            'master/unit-kerja' => 'master.unit-kerja.index',
        ];
    }

    /**
     * Semua path sidebar yang memakai nama route *.index sama (cek {@see pathToIndexRouteMap}).
     *
     * @return list<string>
     */
    public static function menuPathsFromIndexRouteName(string $indexRouteName): array
    {
        $out = [];
        foreach (self::pathToIndexRouteMap() as $path => $name) {
            if ($name === $indexRouteName) {
                $out[] = $path;
            }
        }

        return $out;
    }

    /**
     * Path menu (config sidebar/header) → nama route Laravel *.index.
     */
    public static function indexRouteNameForMenuPath(string $path): ?string
    {
        $path = trim($path, '/');
        if ($path === '') {
            return null;
        }

        $map = self::pathToIndexRouteMap();

        return $map[$path] ?? null;
    }

    /**
     * Item navbar horizontal (config/topbar shortcuts): kombinasi matriks + teritori sama sidebar,
     * plus opsional kolom permission (format page_name/action_name seperti menu sidebar {@see filterMenuByPermission}).
     *
     * Keys entry: route?, path?, query?, matrix_path? (kalau beberapa path share route yang sama),
     * permission?
     *
     * @param  array<string, mixed>  $entry
     */
    public static function allowsHeaderMenuEntry(?AuthenticatedUser $user, array $entry): bool
    {
        if (! $user) {
            return false;
        }

        $bypassGate = self::isSuperAdminForLayout($user);
        $routeName = (string) ($entry['route'] ?? '');
        $hasPath = isset($entry['path']) && is_string($entry['path']) && trim($entry['path']) !== '';

        if ($hasPath && $routeName === '') {
            if ($bypassGate) {
                return self::headerMenuEntryPassesOptionalPermission($entry, $user, true);
            }

            $path = trim($entry['path']);
            $query = isset($entry['query']) && is_array($entry['query']) ? $entry['query'] : [];

            if (! self::matrixAllowsPathForUser($user, $path, $query)) {
                return false;
            }

            return self::headerMenuEntryPassesOptionalPermission($entry, $user, false);
        }

        if ($routeName === '' || ! Route::has($routeName)) {
            return false;
        }

        if ($bypassGate) {
            return self::headerMenuEntryPassesOptionalPermission($entry, $user, true);
        }

        $path = self::resolveHeaderEntryMenuPath($entry);
        if ($path === null) {
            return false;
        }

        $query = isset($entry['query']) && is_array($entry['query']) ? $entry['query'] : [];

        if (! self::matrixAllowsPathForUser($user, $path, $query)) {
            return false;
        }

        return self::headerMenuEntryPassesOptionalPermission($entry, $user, false);
    }

    /**
     * Path matriks/sidebar untuk satu baris config navbar (tanpa bypass superadmin-layout).
     *
     * @param  array<string, mixed>  $entry
     */
    protected static function resolveHeaderEntryMenuPath(array $entry): ?string
    {
        if (isset($entry['matrix_path']) && is_string($entry['matrix_path']) && $entry['matrix_path'] !== '') {
            return trim($entry['matrix_path']);
        }

        if (isset($entry['path']) && is_string($entry['path']) && trim($entry['path']) !== '') {
            return trim($entry['path']);
        }

        $routeName = (string) ($entry['route'] ?? '');
        if ($routeName === '') {
            return null;
        }

        $candidates = self::menuPathsFromIndexRouteName($routeName);
        if (count($candidates) !== 1) {
            return null;
        }

        return $candidates[0];
    }

    /**
     * Fingerprint konsisten untuk bandingkan daun sidebar vs shortcut navbar (path + query canonik).
     */
    protected static function sidebarNavbarDedupeFingerprint(string $path, array $query): string
    {
        return $path.'|'.self::canonicalNavbarQuerySignature($query);
    }

    /**
     * Signature query stabil (urutan key terurut).
     */
    protected static function canonicalNavbarQuerySignature(array $query): string
    {
        if ($query === []) {
            return '';
        }
        ksort($query);

        return http_build_query($query);
    }

    /** @var array<string, array<string, true>> fingerprint key => true, cache per pengguna login */
    private static array $navbarShortcutFingerprintMaps = [];

    /**
     * Modul CRUD Data Collecting — tetap di sidebar walaupun ada shortcut navbar yang sama.
     *
     * @return list<string>
     */
    protected static function sidebarDedupeExcludedPaths(): array
    {
        return [
            'terminal',
            'objekwisata',
            'tempatibadah',
            'faskes',
            't_datajalan',
            'jalan_gangguan',
            'pnpangkutan',
            'kunjunganwisata',
        ];
    }

    protected static function isSidebarDedupeExcludedPath(string $path): bool
    {
        return in_array($path, self::sidebarDedupeExcludedPaths(), true);
    }

    /**
     * Shortcut navbar yang boleh tampil bagi user ini → fingerprint path|query (untuk hilangkan duplikat di sidebar).
     *
     * @return array<string, true>
     */
    protected static function visibleNavbarShortcutFingerprintMap(?AuthenticatedUser $user): array
    {
        $cacheKey = $user ? (string) $user->getAuthIdentifier() : '_';

        if (isset(self::$navbarShortcutFingerprintMaps[$cacheKey])) {
            return self::$navbarShortcutFingerprintMaps[$cacheKey];
        }

        $fingerprints = [];

        if (! $user || ! self::isSuperAdminForLayout($user)) {
            self::$navbarShortcutFingerprintMaps[$cacheKey] = $fingerprints;

            return $fingerprints;
        }

        foreach (self::configArrayStrict('superadmin_header_menu.groups') as $g) {
            foreach ($g['items'] ?? [] as $it) {
                if (! is_array($it) || ! self::allowsHeaderMenuEntry($user, $it)) {
                    continue;
                }
                $path = self::resolveHeaderEntryMenuPath($it);
                if ($path === null || $path === '') {
                    continue;
                }
                if (self::isSidebarDedupeExcludedPath($path)) {
                    continue;
                }
                $query = isset($it['query']) && is_array($it['query']) ? $it['query'] : [];
                $fingerprints[self::sidebarNavbarDedupeFingerprint($path, $query)] = true;
            }
        }

        self::$navbarShortcutFingerprintMaps[$cacheKey] = $fingerprints;

        return $fingerprints;
    }

    /**
     * Sembunyikan daun sidebar yang sama dengan shortcut navbar (path + query).
     *
     * @param  array<int, array<string, mixed>>  $menu
     * @param  array<string, true>  $fingerprints
     * @return array<int, array<string, mixed>>
     */
    protected static function filterSidebarItemsHiddenWhenInNavbarRecursive(array $menu, array $fingerprints): array
    {
        if ($fingerprints === []) {
            return $menu;
        }

        $out = [];

        foreach ($menu as $item) {
            if (isset($item['submenu']) && is_array($item['submenu'])) {
                $sub = self::filterSidebarItemsHiddenWhenInNavbarRecursive($item['submenu'], $fingerprints);
                if ($sub === []) {
                    continue;
                }
                $item['submenu'] = $sub;
                $out[] = $item;

                continue;
            }

            if (! empty($item['disabled'])) {
                $out[] = $item;

                continue;
            }

            $path = isset($item['path']) ? (string) $item['path'] : '';
            if ($path === '' || str_starts_with($path, 'group_')) {
                $out[] = $item;

                continue;
            }

            $query = isset($item['query']) && is_array($item['query']) ? $item['query'] : [];
            if (isset($fingerprints[self::sidebarNavbarDedupeFingerprint($path, $query)])) {
                continue;
            }

            $out[] = $item;
        }

        return $out;
    }

    /**
     * Sembunyikan item sidebar yang sudah ada di navbar bagi user sekarang (@see visibleNavbarShortcutFingerprintMap).
     *
     * @param  array<int, array<string, mixed>>  $menu
     * @return array<int, array<string, mixed>>
     */
    protected static function filterSidebarItemsHiddenWhenShownInNavbar(array $menu): array
    {
        $user = Auth::user();

        return self::filterSidebarItemsHiddenWhenInNavbarRecursive(
            $menu,
            self::visibleNavbarShortcutFingerprintMap($user instanceof AuthenticatedUser ? $user : null)
        );
    }

    /**
     * @param  array<string, mixed>  $entry
     */
    protected static function headerMenuEntryPassesOptionalPermission(array $entry, AuthenticatedUser $user, bool $superAdminIgnoreDbPermission): bool
    {
        if ($superAdminIgnoreDbPermission) {
            return true;
        }

        if (! isset($entry['permission']) || ! is_string($entry['permission']) || $entry['permission'] === '') {
            return true;
        }

        if (! $user->user_role_id) {
            return false;
        }

        return DB::table('role_permissions')
            ->where('role_id', $user->user_role_id)
            ->whereRaw('LOWER(CONCAT(page_name, \'/\', action_name)) = ?', [strtolower($entry['permission'])])
            ->exists();
    }

    /**
     * Path menu → prefix nama route untuk halaman selain index (resource Laravel).
     *
     * @return array<string, string>
     */
    protected static function pathToRoutePrefixMap(): array
    {
        return [
            'layanan/public-service' => 'layanan.public_service',
            'layanan/masyarakat' => 'layanan.masyarakat',
            'layanan/data' => 'layanan.data',
            'media/manage' => 'media.manage',
            'media/analytic' => 'media.analytic',
            'personil/manager' => 'personil.manager',
        ];
    }

    /**
     * Nilai konfigurasi sebagai array (Intelephense: hindari inferensi mixed|null pada config()).
     *
     * @return array<mixed>
     */
    private static function configArrayStrict(string $key, array $default = []): array
    {
        $value = config($key, $default);

        return is_array($value) ? $value : $default;
    }

    /**
     * ID role untuk matriks sidebar (1–7, sama dengan keys di config/role_menu_matrix.php).
     *
     * Pada basis data sbo, `usm.user_role_id` biasanya = `roles.role_id`.
     * Pada skema Spatie murni, `user_role_id` bisa = `roles.id` — dilacak ke `roles.role_id` bila kolom itu ada.
     */
    public static function matrixRoleIdForUser(?AuthenticatedUser $user): int
    {
        $raw = (int) ($user?->user_role_id ?? 0);
        if ($raw === 0) {
            return 0;
        }

        $operatorId = (int) config('role_menu_matrix.operator_matrix_role_id', 4);
        $legacyOperatorIds = self::configArrayStrict('role_menu_matrix.legacy_operator_user_role_ids', [11]);
        if (in_array($raw, $legacyOperatorIds, true)) {
            return $operatorId;
        }

        if (! Schema::hasTable('roles')) {
            return $raw;
        }

        if (Schema::hasColumn('roles', 'role_id')) {
            if (Schema::hasColumn('roles', 'id')) {
                $matrixFromPk = DB::table('roles')->where('id', $raw)->value('role_id');
                if ($matrixFromPk !== null) {
                    return (int) $matrixFromPk;
                }
            }

            if (DB::table('roles')->where('role_id', $raw)->exists()) {
                return $raw;
            }

            $matrixId = DB::table('roles')->where('id', $raw)->value('role_id');
            if ($matrixId !== null) {
                return (int) $matrixId;
            }
        }

        return $raw;
    }

    /**
     * Menu horizontal topbar: super admin, operator, semua level executive.
     * Executive 1/2 (pusat/provinsi) menggunakan config/executive_header_menu.php.
     * Executive 3 (kab/kota) menggunakan config/operator_header_menu.php.
     */
    public static function showsTopbarMegaNavForLayout(?AuthenticatedUser $user): bool
    {
        return self::isSuperAdminForLayout($user)
            || self::isOperatorForLayout($user)
            || self::isExecutiveForLayout($user);
    }

    /**
     * Tampilkan menu horizontal legacy di layout (topbar): matrix super admin ATAU nama role mengandung "super admin".
     * Dipakai ketika user_role_id tidak memetakan ke role_id=1 tetapi akun tetap super administrator.
     */
    public static function isSuperAdminForLayout(?AuthenticatedUser $user): bool
    {
        if (! $user) {
            return false;
        }

        $matrixIds = self::configArrayStrict('role_menu_matrix.super_admin_roles', [1]);
        if (in_array(self::matrixRoleIdForUser($user), $matrixIds, true)) {
            return true;
        }

        $user->loadMissing('role');
        $role = $user->role;
        if ($role && isset($role->role_id) && in_array((int) $role->role_id, $matrixIds, true)) {
            return true;
        }

        $name = strtolower(trim((string) ($role?->role_name ?? $role?->name ?? '')));

        return $name !== '' && (bool) preg_match('/super\s*admin/i', $name);
    }

    /**
     * Sidebar/menu khusus operator: matrix role_id 4, legacy user_role_id 11, atau nama role mengandung operator/posyan.
     */
    public static function isOperatorForLayout(?AuthenticatedUser $user): bool
    {
        if (! $user) {
            return false;
        }

        if (self::isSuperAdminForLayout($user)) {
            return false;
        }

        $operatorId = (int) config('role_menu_matrix.operator_matrix_role_id', 4);

        if (self::matrixRoleIdForUser($user) === $operatorId) {
            return true;
        }

        $legacyOperatorIds = self::configArrayStrict('role_menu_matrix.legacy_operator_user_role_ids', [11]);
        if (in_array((int) ($user->user_role_id ?? 0), $legacyOperatorIds, true)) {
            return true;
        }

        $user->loadMissing('role');
        $role = $user->role;
        if ($role && isset($role->role_id) && (int) $role->role_id === $operatorId) {
            return true;
        }

        $name = strtolower(trim((string) ($role?->role_name ?? $role?->name ?? '')));
        if ($name === '' && method_exists($user, 'getRoleLabelAttribute')) {
            $name = strtolower(trim((string) ($user->role_label ?? '')));
        }

        return $name !== '' && (bool) preg_match('/\b(oprator|operator|posyan)\b/i', $name);
    }

    /**
     * Satu pintu sidebar layout: superadmin / operator / executive / admin (config/menu.php).
     *
     * @return array<int, array<string, mixed>>
     */
    public static function resolveSidebarMenuForUser(?AuthenticatedUser $user): array
    {
        if (! $user) {
            return [];
        }

        if (self::isSuperAdminForLayout($user)) {
            return self::getSuperadminSidebarItems();
        }

        if (self::isOperatorForLayout($user)) {
            return self::getOperatorSidebarItems();
        }

        if (self::isExecutiveForLayout($user)) {
            if (self::isExecutiveKabKotaUser($user)) {
                // Executive 3 (matrix 5): sidebar & topbar sama operator, hanya lihat.
                return self::getOperatorSidebarItems();
            }

            // Executive 2/1: sidebar sama AdminPolda/pusat (config/menu.php), hanya lihat.
            return self::getAdminSidebarItems();
        }

        return self::getAdminSidebarItems();
    }

    /**
     * Sidebar/menu khusus executive: matrix role 7 (pusat), 6 (provinsi/Polda), 5 (kab/kota/Polres).
     */
    public static function isExecutiveForLayout(?AuthenticatedUser $user): bool
    {
        if (! $user) {
            return false;
        }

        $execRoles = self::configArrayStrict('role_menu_matrix.executive_matrix_roles', [5, 6, 7]);

        return in_array(self::matrixRoleIdForUser($user), $execRoles, true);
    }

    /**
     * Executive pusat (matrix 7): cakupan nasional, hanya lihat.
     */
    public static function isExecutiveNationalUser(?AuthenticatedUser $user): bool
    {
        if (! $user) {
            return false;
        }

        $nationalRoleId = (int) config('role_menu_matrix.executive_national_matrix_role_id', 7);

        return self::matrixRoleIdForUser($user) === $nationalRoleId;
    }

    /**
     * Executive 2 (matrix 6): setara AdminPolda/provinsi — hanya lihat data di polda kerja.
     */
    public static function isExecutiveProvinsiUser(?AuthenticatedUser $user): bool
    {
        if (! $user) {
            return false;
        }

        $provRoleId = (int) config('role_menu_matrix.executive_provinsi_matrix_role_id', 6);

        return self::matrixRoleIdForUser($user) === $provRoleId;
    }

    /**
     * Executive 3 (matrix 5): setara AdminPolres/kab-kota — hanya lihat data di polres kerja.
     */
    public static function isExecutiveKabKotaUser(?AuthenticatedUser $user): bool
    {
        if (! $user) {
            return false;
        }

        $kabRoleId = (int) config('role_menu_matrix.executive_kab_kota_matrix_role_id', 5);

        return self::matrixRoleIdForUser($user) === $kabRoleId;
    }

    /**
     * Tingkat geografis efektif untuk menu & gate URL — executive pusat selalu nasional.
     *
     * @return 'nasional'|'provinsi'|'kab_kota'|null
     */
    public static function effectiveTerritoryLevelForUser(?AuthenticatedUser $user): ?string
    {
        if (self::isExecutiveNationalUser($user)) {
            return \App\Support\TerritoryLevelForUsm::NASIONAL;
        }

        return HierarchicalHelper::getUserLevel();
    }

    /**
     * `user_role_id` yang dianggap operator Kuat Personil BO (matriks 3 + legacy, mis. 11).
     *
     * @return list<int>
     */
    public static function personilBoOperatorUserRoleIds(): array
    {
        $ids = [(int) config('role_menu_matrix.operator_matrix_role_id', 4)];

        foreach (self::configArrayStrict('role_menu_matrix.legacy_operator_user_role_ids', [11]) as $legacyId) {
            $ids[] = (int) $legacyId;
        }

        return array_values(array_unique($ids));
    }

    public static function isPersonilBoOperatorUser(?AuthenticatedUser $user): bool
    {
        if (! $user) {
            return false;
        }

        return in_array((int) ($user->user_role_id ?? 0), self::personilBoOperatorUserRoleIds(), true);
    }

    /**
     * Admin wilayah: matrix role_id 2 (Polda) atau 3 (Polres).
     */
    public static function isAdminTerritorialForLayout(?AuthenticatedUser $user): bool
    {
        if (! $user) {
            return false;
        }

        return in_array(self::matrixRoleIdForUser($user), [2, 3], true);
    }

    /** @deprecated Gunakan {@see isOperatorForLayout} */
    public static function isOperatorMatrixRole(?AuthenticatedUser $user): bool
    {
        return self::isOperatorForLayout($user);
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public static function getOperatorSidebarItems(): array
    {
        $menu = self::configArrayStrict('operator_sidebar_menu.items', []);

        // Menu oprator sudah dikurasi; jangan filter matriks/navbar dedupe (CCTV dll hilang dari sidebar).
        $menu = self::filterMenuByTerritory($menu);
        $menu = self::filterMenuByPermission($menu);

        return self::pruneEmptySubmenuGroups(self::excludeSidebarOnlyGroups($menu));
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public static function getExecutiveSidebarItems(): array
    {
        $menu = self::configArrayStrict('executive_sidebar_menu.items', []);

        $menu = self::filterMenuByRoleMatrix($menu);
        $menu = self::filterMenuByTerritory($menu);
        $menu = self::filterMenuByPermission($menu);

        return self::pruneEmptySubmenuGroups(self::excludeSidebarOnlyGroups($menu));
    }

    /**
     * Buang grup yang hanya boleh muncul di header/topbar, bukan sidebar.
     *
     * @param  array<int, array<string, mixed>>  $menu
     * @return array<int, array<string, mixed>>
     */
    protected static function excludeSidebarOnlyGroups(array $menu): array
    {
        $excluded = array_flip(self::SIDEBAR_EXCLUDED_GROUP_LABELS);
        $out = [];

        foreach ($menu as $item) {
            $label = (string) ($item['label'] ?? '');
            if (isset($excluded[$label])) {
                continue;
            }
            if (! empty($item['submenu']) && is_array($item['submenu'])) {
                $item['submenu'] = self::excludeSidebarOnlyGroups($item['submenu']);
            }
            $out[] = $item;
        }

        return $out;
    }

    /**
     * Sidebar admin wilayah & executive (view-only): {@see config/menu.php}.
     * Executive: label Personil Manager → Personil BackOffice; Map/Kuat Personil BO difilter matriks.
     *
     * @return array<int, array<string, mixed>>
     */
    public static function getAdminSidebarItems(): array
    {
        $user = Auth::user();
        $menu = self::configArrayStrict('menu.items', []);

        if ($user && self::isExecutiveForLayout($user)) {
            foreach ($menu as &$item) {
                if (($item['path'] ?? '') === 'admin/users') {
                    $item['label'] = 'Personil BackOffice';
                }
            }
            unset($item);
        }

        // 1) Filter berdasarkan matriks akses role (config/role_menu_matrix.php)
        $menu = self::filterMenuByRoleMatrix($menu);

        // 2) Filter kolom geografis Pusat / Provinsi / Kab-Kota (config/menu.php → territories)
        $menu = self::filterMenuByTerritory($menu);

        // 3) Filter tambahan berdasarkan tabel role_permissions (jika menu punya 'permission' key)
        $menu = self::filterMenuByPermission($menu);

        // 4) Sembunyikan daun yang duplikat dengan shortcut navbar untuk user ini
        $menu = self::filterSidebarItemsHiddenWhenShownInNavbar($menu);

        return self::pruneEmptySubmenuGroups(self::excludeSidebarOnlyGroups($menu));
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public static function getMenuItems(): array
    {
        $user = Auth::user();

        return $user ? self::resolveSidebarMenuForUser($user) : [];
    }

    /**
     * Filter menu berdasarkan matriks role × menu (config/role_menu_matrix.php).
     */
    protected static function filterMenuByRoleMatrix(array $menu): array
    {
        $user = Auth::user();
        if (! $user) {
            return [];
        }

        $roleId = self::matrixRoleIdForUser($user);
        $superAdmins = self::configArrayStrict('role_menu_matrix.super_admin_roles', [1]);

        // SA / role super admin: tampilkan semua menu (skip matrix).
        if (in_array($roleId, $superAdmins, true)) {
            return $menu;
        }

        $access = self::configArrayStrict('role_menu_matrix.menu_access', []);

        $filtered = [];
        foreach ($menu as $item) {
            $path = $item['path'] ?? '';

            // Recursively filter submenu dulu agar parent ikut hilang kalau kosong.
            if (isset($item['submenu']) && is_array($item['submenu'])) {
                $item['submenu'] = self::filterMenuByRoleMatrix($item['submenu']);
            }

            if (self::roleCanSeeMenu($path, $roleId, $access, $item)) {
                $filtered[] = $item;
            }
        }

        return $filtered;
    }

    /**
     * Cek apakah role boleh melihat menu path tertentu sesuai matriks.
     * - Path tidak terdaftar di matriks → default hide (kecuali punya submenu yg visible).
     * - 'all' → semua role yg login lolos.
     * - array role_id → cek apakah role user ada di list.
     */
    protected static function roleCanSeeMenu(string $path, int $roleId, array $access, array $item): bool
    {
        // Parent menu: tampilkan hanya jika masih ada submenu setelah filter rekursif.
        if (! empty($item['submenu']) && is_array($item['submenu'])) {
            return $item['submenu'] !== [];
        }

        if (! isset($access[$path])) {
            return false; // path tidak ada di matriks → hide secara default
        }

        $rule = $access[$path];

        if ($rule === 'all') {
            return true;
        }
        if (is_array($rule)) {
            return in_array($roleId, $rule, true);
        }

        return false;
    }

    /**
     * Proteksi akses URL: matriks role + aturan geografis seperti daftar sidebar (config/menu.php).
     * Query request (mis. sm_lv untuk turjagwali, ref untuk referensi) mempengaruhi kecocokan dengan daun menu.
     */
    public static function matrixAllowsPathForUser(?AuthenticatedUser $user, string $path, array $requestQuery = []): bool
    {
        if (! $user) {
            return false;
        }

        $roleId = self::matrixRoleIdForUser($user);
        $superAdmins = self::configArrayStrict('role_menu_matrix.super_admin_roles', [1]);

        if (in_array($roleId, $superAdmins, true)) {
            return true;
        }

        $access = self::configArrayStrict('role_menu_matrix.menu_access', []);

        if (! isset($access[$path])) {
            return false;
        }

        $rule = $access[$path];

        if ($rule !== 'all' && (! is_array($rule) || ! in_array($roleId, $rule, true))) {
            return false;
        }

        if ($path === 'turjagwali'
            && (self::isOperatorForLayout($user) || self::isExecutiveKabKotaUser($user))
            && ! self::operatorTurjagwaliQueryAllowed($requestQuery)) {
            return false;
        }

        return self::territoryAllowsPathForSidebarMatrix($user, $path, $requestQuery);
    }

    /**
     * Oprator: turjagwali hanya lewat Opsdal (sm_lv=opsdal), bukan Giat Rutin (giat_daerah/giat_nasional).
     *
     * @param  array<string, mixed>  $requestQuery
     */
    protected static function operatorTurjagwaliQueryAllowed(array $requestQuery): bool
    {
        $smLv = isset($requestQuery['sm_lv']) ? (string) $requestQuery['sm_lv'] : '';
        if ($smLv === '' && Request::filled('sm_lv')) {
            $smLv = (string) Request::query('sm_lv');
        }
        if ($smLv === '' && Session::has('turjagwali_sm_lv')) {
            $smLv = (string) Session::get('turjagwali_sm_lv');
        }

        return $smLv === 'opsdal';
    }

    /** @var list<array{path:string, query:array<string, mixed>, territories:array<int, string>}>|null */
    private static ?array $flattenedSidebarLeavesWithTerritories = null;

    /**
     * Daun sidebar (path + query + territories) untuk sinkron akses geografis dengan matriks.
     *
     * @return list<array{path:string, query:array<string, mixed>, territories:array<int, string>}>
     */
    private static function flattenedSidebarLeavesWithTerritories(): array
    {
        if (self::$flattenedSidebarLeavesWithTerritories !== null) {
            return self::$flattenedSidebarLeavesWithTerritories;
        }

        /** @var list<array{path:string, query:array<string, mixed>, territories:array<int, string>}> $out */
        $out = [];
        foreach ([
            self::configArrayStrict('menu.items', []),
            self::configArrayStrict('operator_sidebar_menu.items', []),
            self::configArrayStrict('executive_sidebar_menu.items', []),
        ] as $items) {
            foreach ($items as $root) {
                if (is_array($root)) {
                    self::collectMenuLeavesWithTerritories($root, $out);
                }
            }
        }
        self::$flattenedSidebarLeavesWithTerritories = $out;

        return self::$flattenedSidebarLeavesWithTerritories;
    }

    /**
     * Kumpulkan hanya leaf yang punya `territories` (matriks Pusat × Provinsi × Kab/Kota).
     *
     * @param  array<string, mixed>  $node
     * @param  list<array{path:string, query:array<string, mixed>, territories:array<int, string>}>  $accum
     */
    private static function collectMenuLeavesWithTerritories(array $node, array &$accum): void
    {
        $submenu = $node['submenu'] ?? null;
        if (is_array($submenu)) {
            foreach ($submenu as $child) {
                if (is_array($child)) {
                    self::collectMenuLeavesWithTerritories($child, $accum);
                }
            }

            return;
        }

        if (! isset($node['path']) || ! is_string($node['path']) || ! isset($node['territories']) || ! is_array($node['territories'])) {
            return;
        }

        $q = isset($node['query']) && is_array($node['query']) ? $node['query'] : [];
        $terrs = [];
        foreach ($node['territories'] as $t) {
            if (is_string($t)) {
                $terrs[] = $t;
            }
        }

        $accum[] = [
            'path' => $node['path'],
            'query' => $q,
            'territories' => $terrs,
        ];
    }

    private static function requestQueryMatchesSidebarLeafQuery(array $requestQuery, array $leafQuery): bool
    {
        foreach ($leafQuery as $k => $expected) {
            $k = (string) $k;
            if (! array_key_exists($k, $requestQuery)) {
                return false;
            }
            if ((string) $requestQuery[$k] !== (string) $expected) {
                return false;
            }
        }

        return true;
    }

    /**
     * Cocokkah path + subset query permintaan dengan satu baris matriks sidebar & level geografis pengguna?
     *
     * @param  array<string, mixed>  $requestQuery
     */
    public static function territoryAllowsPathForSidebarMatrix(?AuthenticatedUser $user, string $path, array $requestQuery = []): bool
    {
        if (! $user) {
            return false;
        }

        $level = MenuHelper::effectiveTerritoryLevelForUser($user);
        $levelNormalized = TerritoryLevelForUsm::normalize($level) ?? TerritoryLevelForUsm::NASIONAL;

        $matchedLeaves = [];
        foreach (self::flattenedSidebarLeavesWithTerritories() as $leaf) {
            if ($leaf['path'] !== $path) {
                continue;
            }

            if (! self::requestQueryMatchesSidebarLeafQuery($requestQuery, $leaf['query'])) {
                continue;
            }

            $matchedLeaves[] = $leaf;
        }

        if ($matchedLeaves === []) {
            return true;
        }

        foreach ($matchedLeaves as $leaf) {
            foreach ($leaf['territories'] as $territory) {
                if ($levelNormalized === TerritoryLevelForUsm::normalize($territory)) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Filter menu untuk kolom Pusat / Provinsi / Kab-Kota profil pengguna (@see config/menu.php `territories`).
     *
     * @param  array<int, array<string, mixed>>  $menu
     * @return array<int, array<string, mixed>>
     */
    protected static function filterMenuByTerritory(array $menu): array
    {
        $user = Auth::user();

        if (! $user) {
            return [];
        }

        $roleId = self::matrixRoleIdForUser($user);
        if (in_array($roleId, self::configArrayStrict('role_menu_matrix.super_admin_roles', [1]), true)) {
            return $menu;
        }

        $level = MenuHelper::effectiveTerritoryLevelForUser($user);
        $levelNormalized = TerritoryLevelForUsm::normalize($level) ?? TerritoryLevelForUsm::NASIONAL;

        $filtered = [];
        foreach ($menu as $item) {
            $filteredItem = $item;

            if (isset($item['submenu']) && is_array($item['submenu'])) {
                $filteredItem['submenu'] = self::filterMenuByTerritory($item['submenu']);

                if (empty($filteredItem['submenu'])) {
                    continue;
                }
                $filtered[] = $filteredItem;

                continue;
            }

            if (! isset($item['territories']) || ! is_array($item['territories'])) {
                continue;
            }

            $allowed = false;
            foreach ($item['territories'] as $territory) {
                if ($levelNormalized === TerritoryLevelForUsm::normalize($territory)) {
                    $allowed = true;
                    break;
                }
            }

            if (! $allowed) {
                continue;
            }

            $filtered[] = $filteredItem;
        }

        return $filtered;
    }

    /**
     * Buang grup submenu yang kosong setelah filter teritori/permission.
     *
     * @param  array<int, array<string, mixed>>  $menu
     * @return array<int, array<string, mixed>>
     */
    protected static function pruneEmptySubmenuGroups(array $menu): array
    {
        $out = [];

        foreach ($menu as $item) {
            if (isset($item['submenu']) && is_array($item['submenu'])) {
                $sub = self::pruneEmptySubmenuGroups($item['submenu']);
                if ($sub === []) {
                    continue;
                }
                $item['submenu'] = $sub;
            }

            $out[] = $item;
        }

        return $out;
    }

    /**
     * Filter menu items based on user permissions.
     *
     * @param  array<int, array<string, mixed>>  $menu
     * @return array<int, array<string, mixed>>
     */
    protected static function filterMenuByPermission(array $menu): array
    {
        $filtered = [];

        foreach ($menu as $item) {
            $filteredItem = $item;

            // Recursively filter submenu first
            if (isset($item['submenu']) && is_array($item['submenu'])) {
                $filteredItem['submenu'] = self::filterMenuByPermission($item['submenu']);

                // Parent dengan submenu: tampilkan hanya kalau ada submenu yg lolos
                if (empty($filteredItem['submenu'])) {
                    continue;
                }
                $filtered[] = $filteredItem;

                continue;
            }

            // Leaf menu: cek permission
            if (self::canAccessMenu($item)) {
                $filtered[] = $filteredItem;
            }
        }

        return $filtered;
    }

    /**
     * Check if user can access menu item.
     *
     * @param  array<string, mixed>  $item
     */
    protected static function canAccessMenu(array $item): bool
    {
        if (! isset($item['path'])) {
            return false;
        }

        $user = Auth::user();

        // If no user, don't show menu
        if (! $user) {
            return false;
        }

        // Home menu is always accessible for logged in users
        if ($item['path'] === 'home') {
            return true;
        }

        // If menu has explicit permission check
        if (isset($item['permission'])) {
            if (! $user->user_role_id) {
                return false;
            }
            $permission = $item['permission'];

            return DB::table('role_permissions')
                ->where('role_id', $user->user_role_id)
                ->whereRaw("LOWER(CONCAT(page_name, '/', action_name)) = ?", [strtolower($permission)])
                ->exists();
        }

        // If no permission specified, allow if user is logged in
        // This allows menus without permission requirement to be visible
        return true;
    }

    /**
     * Convert menu path to permission format.
     */
    protected static function pathToPermission(string $path): string
    {
        // Convert path to controller/action format
        // Example: 'usm' -> 'usm/list'
        return strtolower($path).'/list';
    }

    /**
     * Modul trafficstuck (kodal/analytic) vs traffics/trafficdalkot — jangan saling aktif di sidebar.
     */
    protected static function menuPathConflictsWithCurrentRoute(string $path, ?string $currentRoute): bool
    {
        if (! $currentRoute) {
            return false;
        }

        $onTraffics = str_starts_with($currentRoute, 'traffics.');
        $onStuck = str_starts_with($currentRoute, 'trafficstuck.');
        $menuTraffics = in_array($path, ['traffics', 'trafficdalkot'], true);
        $menuStuck = $path === 'trafficstuck';

        return ($onTraffics && $menuStuck) || ($onStuck && $menuTraffics);
    }

    /**
     * Nama route *.index untuk item menu (index_route eksplisit > pathToIndexRouteMap).
     */
    protected static function indexRouteNameForMenuItem(array $item): ?string
    {
        if (! empty($item['index_route']) && is_string($item['index_route']) && Route::has($item['index_route'])) {
            return $item['index_route'];
        }

        $path = (string) ($item['path'] ?? '');
        if ($path === '') {
            return null;
        }

        $routeMap = self::pathToIndexRouteMap();

        return isset($routeMap[$path]) && Route::has($routeMap[$path]) ? $routeMap[$path] : null;
    }

    /**
     * Check if current route matches menu path.
     */
    public static function isActive(string $path): bool
    {
        $currentRoute = Route::currentRouteName();
        $currentPath = Request::path();

        if (self::menuPathConflictsWithCurrentRoute($path, $currentRoute)) {
            return false;
        }

        $routeMap = self::pathToIndexRouteMap();

        if (isset($routeMap[$path]) && $currentRoute === $routeMap[$path]) {
            return true;
        }

        $resourcePrefixes = self::pathToRoutePrefixMap();
        if ($currentRoute && isset($resourcePrefixes[$path]) && str_starts_with($currentRoute, $resourcePrefixes[$path].'.')) {
            return true;
        }

        if (in_array($path, ['patrolicctv', 'cctv'], true) && $currentRoute && str_starts_with($currentRoute, 'patrolicctv.')) {
            return true;
        }

        // Check if current route name matches path directly
        if ($currentRoute === $path) {
            return true;
        }

        // Check if path matches current URL path
        if ($currentPath === $path || str_starts_with($currentPath, $path.'/')) {
            return true;
        }

        // Check if path is prefix of current route name (for resource routes)
        $dotPath = str_replace('/', '.', $path);
        if ($currentRoute && (str_starts_with($currentRoute, $dotPath . '.') || $currentRoute === $dotPath)) {
            return true;
        }

        return false;
    }

    /**
     * Get menu item URL.
     */
    public static function getUrl(string $path): string
    {
        $routeMap = self::pathToIndexRouteMap();

        if (isset($routeMap[$path])) {
            $routeName = $routeMap[$path];
            if (Route::has($routeName)) {
                return route($routeName);
            }
        }

        // Check if path itself is a route name
        if (Route::has($path)) {
            return route($path);
        }

        // Otherwise, use as URL path
        return url($path);
    }

    /**
     * Item sidebar khusus superadmin (config/superadmin_sidebar_menu.php):
     * daun yang sama dengan navbar (@see visibleNavbarShortcutFingerprintMap) tidak ditampil lagi.
     *
     * @return array<int, array<string, mixed>>
     */
    public static function getSuperadminSidebarItems(): array
    {
        $menu = (array) config('superadmin_sidebar_menu.items', []);

        $menu = self::filterSidebarItemsHiddenWhenShownInNavbar($menu);

        return self::pruneEmptySubmenuGroups(self::excludeSidebarOnlyGroups($menu));
    }

    /**
     * Font Awesome (config menu) → Material Symbols untuk sidebar Korlantas.
     */
    public static function sidebarMaterialIcon(?string $icon): string
    {
        if ($icon === null || trim($icon) === '') {
            return 'article';
        }

        $key = preg_replace('/\s+/', ' ', trim(strtolower($icon))) ?? '';

        $map = [
            'fas fa-home' => 'home',
            'fas fa-chart-area' => 'map',
            'fas fa-map' => 'map',
            'fas fa-users' => 'groups',
            'fas fa-user-tie' => 'manage_accounts',
            'fas fa-user' => 'person',
            'fas fa-user-shield' => 'admin_panel_settings',
            'fas fa-video' => 'videocam',
            'fas fa-camera' => 'videocam',
            'fas fa-chart-line' => 'analytics',
            'fas fa-headphones' => 'headset_mic',
            'fas fa-map-marker-alt' => 'location_on',
            'fas fa-ambulance' => 'emergency',
            'fas fa-gauge' => 'speed',
            'fas fa-calendar-check' => 'event_available',
            'fas fa-calendar-day' => 'today',
            'fas fa-taxi' => 'local_taxi',
            'fas fa-train' => 'train',
            'fas fa-bolt' => 'bolt',
            'fas fa-hands-helping' => 'volunteer_activism',
            'fas fa-people-carry' => 'groups',
            'fas fa-table' => 'table_chart',
            'fas fa-building' => 'domain',
            'fas fa-chart-bar' => 'bar_chart',
            'fas fa-database' => 'database',
            'fas fa-cog' => 'settings',
            'fas fa-cogs' => 'settings',
            'fas fa-shield-alt' => 'shield',
            'fas fa-road' => 'add_road',
            'fas fa-car' => 'directions_car',
            'fas fa-list' => 'list',
            'fas fa-folder' => 'folder',
            'fas fa-clipboard' => 'assignment',
            'fab fa-whatsapp' => 'chat',
            'fas fa-newspaper' => 'newspaper',
            'fas fa-cloud-upload-alt' => 'cloud_upload',
            'fas fa-traffic' => 'traffic',
            'fas fa-cloud' => 'cloud',
            'fas fa-sign-in-alt' => 'door_front',
            'fas fa-exchange-alt' => 'swap_horiz',
            'fas fa-clock' => 'schedule',
            'fas fa-phone' => 'call',
            'fas fa-photo-video' => 'perm_media',
            'fas fa-bus' => 'directions_bus',
            'fas fa-hospital' => 'local_hospital',
            'fas fa-camera-retro' => 'photo_camera',
            'fas fa-place-of-worship' => 'mosque',
            'fas fa-server' => 'dns',
        ];

        return $map[$key] ?? 'folder';
    }

    /**
     * URL untuk satu item menu (path + opsional query, mis. master/referensi?ref=…).
     */
    public static function getMenuItemUrl(array $item): string
    {
        if (! empty($item['disabled'])) {
            return '#';
        }

        $path = $item['path'] ?? '';
        $query = $item['query'] ?? [];
        if ($path === '') {
            return '#';
        }

        $routeName = self::indexRouteNameForMenuItem($item);
        if ($routeName !== null) {
            $url = route($routeName);

            return $query === [] ? $url : $url.'?'.http_build_query($query);
        }

        if (Route::has($path)) {
            $url = route($path);

            return $query === [] ? $url : $url.'?'.http_build_query($query);
        }

        return $query === [] ? url($path) : url($path).'?'.http_build_query($query);
    }

    /**
     * Item sidebar aktif? Parent dengan submenu aktif jika salah satu anak aktif.
     */
    public static function isNavItemActive(array $item): bool
    {
        if (! empty($item['submenu']) && is_array($item['submenu'])) {
            foreach ($item['submenu'] as $sub) {
                if (self::isNavItemActive($sub)) {
                    return true;
                }
            }

            return false;
        }

        return self::isLeafMenuItemActive($item);
    }

    /**
     * @internal
     */
    protected static function isReportTrafficAnalyticMenuLeaf(array $item): bool
    {
        $path = (string) ($item['path'] ?? '');
        $indexRoute = (string) ($item['index_route'] ?? '');

        return $path === 'report/traffic-analytic'
            || $indexRoute === 'report.traffic-analytic.index';
    }

    protected static function isOnReportTrafficAnalyticModule(?string $currentRoute): bool
    {
        if (! $currentRoute) {
            return false;
        }

        if ($currentRoute === 'report.traffic-analytic.index') {
            return true;
        }

        if (str_starts_with($currentRoute, 'traffics.')) {
            return true;
        }

        if (! str_starts_with($currentRoute, 'trafficstuck.')) {
            return false;
        }

        $grp = (string) Request::query('sb_grp', session('trafficstuck_sb_grp', ''));

        return $grp === 'patrol_online';
    }

    protected static function isLeafMenuItemActive(array $item): bool
    {
        if (! empty($item['disabled'])) {
            return false;
        }

        $path = $item['path'] ?? '';
        if ($path === '') {
            return false;
        }

        $query = $item['query'] ?? [];
        $current = Route::currentRouteName();
        if (! $current) {
            return false;
        }

        if (self::isReportTrafficAnalyticMenuLeaf($item)) {
            return self::isOnReportTrafficAnalyticModule($current);
        }

        if (self::menuPathConflictsWithCurrentRoute($path, $current)) {
            return false;
        }

        $routeName = self::indexRouteNameForMenuItem($item);

        if (! $routeName) {
            return self::isActive($path);
        }

        if ($query !== []) {
            if (isset($query['sm_lv']) && $routeName === 'turjagwali.index') {
                if (! str_starts_with($current, 'turjagwali.')) {
                    return false;
                }
                $ctx = (string) Request::query('sm_lv', session('turjagwali_sm_lv', ''));

                return (string) $query['sm_lv'] === $ctx;
            }

            if ($routeName === 'master.referensi.index') {
                return $current === $routeName && self::queryMatchesReferensiMenuItem($routeName, $query);
            }

            return self::sidebarIndexRouteMatchesBranch($current, $routeName)
                && self::queryMatchesSidebarLeafAgainstRequest($query);
        }

        if (! str_contains($routeName, '.') || ! str_ends_with($routeName, '.index')) {
            return $current === $routeName;
        }

        $prefix = substr($routeName, 0, -strlen('.index'));

        return $current === $routeName || str_starts_with($current, $prefix.'.');
    }

    /**
     * Apakah nama rute saat ini termasuk cabang resource untuk *.index (mis. trafficstuck.edit).
     */
    protected static function sidebarIndexRouteMatchesBranch(string $current, string $indexRouteName): bool
    {
        if (! str_contains($indexRouteName, '.') || ! str_ends_with($indexRouteName, '.index')) {
            return $current === $indexRouteName;
        }

        $prefix = substr($indexRouteName, 0, -strlen('.index'));

        return $current === $indexRouteName || str_starts_with($current, $prefix.'.');
    }

    /**
     * Cocokkan subset query daun menu dengan request. Untuk sb_grp (satu path trafficstuck di beberapa grup sidebar)
     * gunakan session jika tidak ada di URL — diset middleware saat buka dari menu bertanda sb_grp.
     *
     * @param  array<string, mixed>  $leafQuery
     */
    protected static function queryMatchesSidebarLeafAgainstRequest(array $leafQuery): bool
    {
        foreach ($leafQuery as $key => $expected) {
            $keyStr = (string) $key;
            $actual = Request::query($keyStr);
            if ($keyStr === 'sb_grp' && ($actual === null || $actual === '')) {
                $routeName = Route::currentRouteName() ?? '';
                if (str_starts_with($routeName, 'contigency.')) {
                    $actual = session('contigency_sb_grp');
                } elseif (str_starts_with($routeName, 'layanan.masyarakat.')
                    || str_starts_with($routeName, 'layanan.data.')
                    || str_starts_with($routeName, 'layanan.public_service.')) {
                    $actual = session('layanan_public_service_sb_grp');
                } elseif (str_starts_with($routeName, 'traffics.')) {
                    $actual = null;
                } else {
                    $actual = session('trafficstuck_sb_grp');
                }
            }
            if ((string) $actual !== (string) $expected) {
                return false;
            }
        }

        return true;
    }

    /**
     * Cocokkan query URL untuk tab referensi (default ref=faktor bila kosong/invalid).
     */
    protected static function queryMatchesReferensiMenuItem(string $routeName, array $query): bool
    {
        foreach ($query as $key => $value) {
            $actual = Request::query($key);
            if ($key === 'ref') {
                if ($actual === null || $actual === '') {
                    $actual = 'faktor';
                } elseif (! in_array((string) $actual, self::REFERENSI_REF_KEYS, true)) {
                    $actual = 'faktor';
                }
            }
            if ((string) $actual !== (string) $value) {
                return false;
            }
        }

        return true;
    }
}
