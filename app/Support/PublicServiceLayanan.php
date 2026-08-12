<?php

namespace App\Support;

use App\Helpers\HierarchicalHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Mode operasional vs laporan untuk modul Public Service (layanan masyarakat, data, posyan).
 */
class PublicServiceLayanan
{
    public const SB_GRP = 'public_service';

    public const SESSION_KEY = 'layanan_public_service_sb_grp';

    public static function captureSbGrpFromRequest(Request $request): void
    {
        if (! self::isLayananRoute($request)) {
            return;
        }

        if (self::isOperationalKabKotaStaff()) {
            session()->forget(self::SESSION_KEY);

            return;
        }

        if ($request->filled('sb_grp')) {
            session([self::SESSION_KEY => (string) $request->query('sb_grp')]);
        }
    }

    public static function currentSbGrp(): ?string
    {
        $value = session(self::SESSION_KEY);

        return is_string($value) && $value !== '' ? $value : null;
    }

    public static function isReportMode(Request $request): bool
    {
        if (HierarchicalHelper::viewerBypassesTerritoryScope()) {
            return self::requestHasReportSbGrp($request);
        }

        $user = Auth::user();
        if ($user && RoleMutationPolicy::isViewOnlyRole($user) && self::isLayananRoute($request)) {
            return true;
        }

        if (self::isOperationalKabKotaStaff()) {
            return false;
        }

        $level = HierarchicalHelper::getUserLevel();

        if (in_array($level, [TerritoryLevelForUsm::NASIONAL, TerritoryLevelForUsm::PROVINSI], true)) {
            return true;
        }

        return self::requestHasReportSbGrp($request);
    }

    /**
     * Staff kab/kota yang boleh input data (operator/admin) — bukan mode laporan executive.
     */
    private static function isOperationalKabKotaStaff(): bool
    {
        $user = Auth::user();
        if (! $user || RoleMutationPolicy::isViewOnlyRole($user)) {
            return false;
        }

        return HierarchicalHelper::getUserLevel() === TerritoryLevelForUsm::KAB_KOTA;
    }

    public static function assertWritable(Request $request): void
    {
        if (self::isReportMode($request)) {
            abort(403, 'Mode laporan — tidak dapat mengubah data.');
        }
    }

    /**
     * @return array<string, mixed>
     */
    public static function preserveIndexQuery(Request $request): array
    {
        $params = array_filter(
            $request->only(['search', 'tanggal_dari', 'tanggal_sampai', 'kategori_data', 'kategori', 'daerah', 'wilayah']),
            static fn ($value) => $value !== null && $value !== ''
        );

        if (self::isReportMode($request)) {
            $params['sb_grp'] = self::SB_GRP;
        }

        return $params;
    }

    public static function reportPageTitle(string $operationalTitle): string
    {
        if ($operationalTitle === 'Layanan Masyarakat (Interaksi)' || $operationalTitle === 'Layanan Masyarakat') {
            return 'Report Layanan Masyarakat';
        }
        return 'Report '.$operationalTitle;
    }

    private static function requestHasReportSbGrp(Request $request): bool
    {
        return $request->query('sb_grp') === self::SB_GRP
            || self::currentSbGrp() === self::SB_GRP;
    }

    private static function isLayananRoute(Request $request): bool
    {
        $name = $request->route()?->getName() ?? '';

        return str_starts_with($name, 'layanan.masyarakat.')
            || str_starts_with($name, 'layanan.data.')
            || str_starts_with($name, 'layanan.public_service.');
    }
}
