<?php

namespace App\Support;

use App\Helpers\HierarchicalHelper;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Registry kamera live dari database SatuPeta (`cctv.rtsp_cctv`).
 */
final class CctvLiveRegistry
{
    private static ?string $resolvedTable = null;

    public static function tableName(): string
    {
        if (self::$resolvedTable !== null) {
            return self::$resolvedTable;
        }

        try {
            $schema = Schema::connection('satupeta');
            if ($schema->hasTable('smpc_tbl_data_cctv')) {
                return self::$resolvedTable = 'smpc_tbl_data_cctv';
            }
            if ($schema->hasTable('cctv')) {
                return self::$resolvedTable = 'cctv';
            }
        } catch (\Throwable) {
            // Fallback
        }

        return self::$resolvedTable = 'cctv';
    }

    public static function isAvailable(): bool
    {
        try {
            $schema = Schema::connection('satupeta');
            return $schema->hasTable('smpc_tbl_data_cctv') || $schema->hasTable('cctv');
        } catch (\Throwable) {
            return false;
        }
    }

    /**
     * @return Collection<int, object>
     */
    public static function listCameras(?string $search = null): Collection
    {
        if (! self::isAvailable()) {
            return collect();
        }

        $table = self::tableName();
        $query = DB::connection('satupeta')->table($table);

        if ($table === 'smpc_tbl_data_cctv') {
            $query->select(
                'pk_ids as id',
                'nama_cctv',
                'rtsp_cctv',
                'hls_cctv',
                DB::raw("CONCAT(COALESCE(lat, ''), ',', COALESCE(lng, '')) as kordinat"),
                'nama_cctv as channel_name',
                'channel_id',
                'rowid as trx_id',
                'polda_id as da_id',
                'status'
            );
        } else {
            $hasStatus = Schema::connection('satupeta')->hasColumn('cctv', 'status');
            if ($hasStatus) {
                $query->select('id', 'nama_cctv', 'rtsp_cctv', 'hls_cctv', 'kordinat', 'channel_name', 'channel_id', 'trx_id', 'da_id', 'status');
            } else {
                $query->select('id', 'nama_cctv', 'rtsp_cctv', 'hls_cctv', 'kordinat', 'channel_name', 'channel_id', 'trx_id', 'da_id', DB::raw("'aktif' as status"));
            }
        }

        $query->whereNotNull('rtsp_cctv')->where('rtsp_cctv', '!=', '');

        self::applyTerritoryScope($query);

        if ($search !== null && trim($search) !== '') {
            $q = '%'.trim($search).'%';
            $query->where(function ($w) use ($q, $table) {
                $w->where('nama_cctv', 'like', $q)
                    ->orWhere('channel_id', 'like', $q);
                if ($table !== 'smpc_tbl_data_cctv') {
                    $w->orWhere('channel_name', 'like', $q);
                }
            });
        }

        return $query->orderBy('nama_cctv')->get();
    }

    public static function findCamera(int $id): ?object
    {
        if (! self::isAvailable()) {
            return null;
        }

        $table = self::tableName();
        $query = DB::connection('satupeta')->table($table);

        if ($table === 'smpc_tbl_data_cctv') {
            $query->select(
                'pk_ids as id',
                'nama_cctv',
                'rtsp_cctv',
                'hls_cctv',
                DB::raw("CONCAT(COALESCE(lat, ''), ',', COALESCE(lng, '')) as kordinat"),
                'nama_cctv as channel_name',
                'channel_id',
                'rowid as trx_id',
                'polda_id as da_id',
                'status'
            )->where('pk_ids', $id);
        } else {
            $hasStatus = Schema::connection('satupeta')->hasColumn('cctv', 'status');
            if ($hasStatus) {
                $query->select('id', 'nama_cctv', 'rtsp_cctv', 'hls_cctv', 'kordinat', 'channel_name', 'channel_id', 'trx_id', 'da_id', 'status')
                    ->where('id', $id);
            } else {
                $query->select('id', 'nama_cctv', 'rtsp_cctv', 'hls_cctv', 'kordinat', 'channel_name', 'channel_id', 'trx_id', 'da_id', DB::raw("'aktif' as status"))
                    ->where('id', $id);
            }
        }

        self::applyTerritoryScope($query);

        $row = $query->first();
        if (! $row || empty($row->rtsp_cctv)) {
            return null;
        }

        return $row;
    }

    private static function getPoldaIdByName(string $name): ?string
    {
        $cleaned = mb_strtolower((string) preg_replace('/^(polda|provinsi|prov\.?)\s+/iu', '', $name));
        $cleaned = trim($cleaned);
        if ($cleaned === '') return null;

        // Hardcoded map for the main known Poldas in CCTV database
        $map = [
            'jawa tengah' => '14',
            'jateng' => '14',
            'jawa barat' => '15',
            'jabar' => '15',
            'bali' => '17',
        ];
        if (isset($map[$cleaned])) {
            return $map[$cleaned];
        }

        // Try legacy table first
        if (Schema::hasTable('daerah')) {
            $row = DB::table('daerah')->where('namadaerah', 'LIKE', '%' . $cleaned . '%')->first();
            if ($row) return (string) ($row->id_daerah ?? $row->id ?? '');
        }

        // Try master table
        if (Schema::hasTable('master_provinsi')) {
            $row = DB::table('master_provinsi')->where('nama', 'LIKE', '%' . $cleaned . '%')->first();
            if ($row) return (string) ($row->id ?? '');
        }

        // Dynamic lookup from table
        try {
            $table = self::tableName();
            $conn = 'satupeta';
            if (Schema::connection($conn)->hasTable($table)) {
                $row = DB::connection($conn)->table($table)
                    ->where('nama_cctv', 'LIKE', '%' . $cleaned . '%')
                    ->first();
                if ($row && !empty($row->polda_id)) {
                    return (string) $row->polda_id;
                }
            }
        } catch (\Throwable $e) {}

        return null;
    }

    private static function getPolresIdByName(string $name): ?string
    {
        $cleaned = mb_strtolower((string) preg_replace('/^(polrestabes metro|polresta metro|polres metro|polres\/metro|polrestabes|polresta|polres|kabupaten|kab\.|kota administrasi|kota adm\.|kota)\s+/iu', '', $name));
        $cleaned = trim($cleaned);
        if ($cleaned === '') return null;

        // Hardcoded map for the main known Polreses in CCTV database
        $map = [
            'surakarta' => '191',
            'solo' => '191',
            'denpasar' => '271',
            'badung' => '265',
        ];
        if (isset($map[$cleaned])) {
            return $map[$cleaned];
        }

        // Try legacy table first
        if (Schema::hasTable('wilayah')) {
            $row = DB::table('wilayah')->where('namawilayah', 'LIKE', '%' . $cleaned . '%')->first();
            if ($row) return (string) ($row->id_wilayah ?? $row->id ?? '');
        }

        // Try master table
        if (Schema::hasTable('master_kabupaten_kota')) {
            $row = DB::table('master_kabupaten_kota')->where('nama', 'LIKE', '%' . $cleaned . '%')->first();
            if ($row) return (string) ($row->id ?? '');
        }

        // Dynamic lookup from table
        try {
            $table = self::tableName();
            $conn = 'satupeta';
            if (Schema::connection($conn)->hasTable($table)) {
                $row = DB::connection($conn)->table($table)
                    ->where(function($q) use ($cleaned) {
                        $q->where('nama_cctv', 'LIKE', '%' . $cleaned . '%')
                          ->orWhere('kota', 'LIKE', '%' . $cleaned . '%')
                          ->orWhere('kecamatan', 'LIKE', '%' . $cleaned . '%');
                    })
                    ->first();
                if ($row && !empty($row->polres_id)) {
                    return (string) $row->polres_id;
                }
            }
        } catch (\Throwable $e) {}

        return null;
    }

    private static function applyTerritoryScope($query): void
    {
        if (HierarchicalHelper::viewerBypassesTerritoryScope()) {
            return;
        }

        $user = Auth::user();
        if (! $user) {
            $query->whereRaw('1 = 0');
            return;
        }

        $table = self::tableName();
        $schema = Schema::connection('satupeta');

        // Dynamic Scoping based on logged in user's daerah and wilayah
        $userDaerah = trim((string) ($user->daerah ?? ''));
        $userWilayah = trim((string) ($user->wilayah ?? ''));

        if ($userDaerah !== '') {
            $poldaId = self::getPoldaIdByName($userDaerah);
            if ($poldaId !== null && $poldaId !== '') {
                if ($table === 'smpc_tbl_data_cctv' && $schema->hasColumn($table, 'polda_id')) {
                    $query->where('polda_id', $poldaId);
                } elseif ($schema->hasColumn($table, 'da_id')) {
                    $query->where('da_id', $poldaId);
                }
            } else {
                // Name-based fallback if ID not found
                $cleanDaerah = mb_strtolower((string) preg_replace('/^(polda|provinsi|prov\.?)\s+/iu', '', $userDaerah));
                $likeDaerah = '%'.$cleanDaerah.'%';
                $query->where(function ($w) use ($likeDaerah, $table) {
                    $w->whereRaw('LOWER(nama_cctv) LIKE ?', [$likeDaerah]);
                    if ($table !== 'smpc_tbl_data_cctv') {
                        $w->orWhereRaw('LOWER(channel_name) LIKE ?', [$likeDaerah]);
                    }
                });
            }

            if ($userWilayah !== '') {
                $polresId = self::getPolresIdByName($userWilayah);
                if ($polresId !== null && $polresId !== '') {
                    if ($table === 'smpc_tbl_data_cctv' && $schema->hasColumn($table, 'polres_id')) {
                        $query->where('polres_id', $polresId);
                    }
                } else {
                    // Name-based fallback for Polres (using kecamatan or kota or nama_cctv)
                    $cleanWilayah = mb_strtolower((string) preg_replace('/^(polrestabes metro|polresta metro|polres metro|polres\/metro|polrestabes|polresta|polres|kabupaten|kab\.|kota administrasi|kota adm\.|kota)\s+/iu', '', $userWilayah));
                    $likeWilayah = '%'.$cleanWilayah.'%';
                    $query->where(function ($w) use ($likeWilayah, $table, $schema) {
                        $w->whereRaw('LOWER(nama_cctv) LIKE ?', [$likeWilayah]);
                        if ($table === 'smpc_tbl_data_cctv') {
                            if ($schema->hasColumn($table, 'kota')) {
                                $w->orWhereRaw('LOWER(kota) LIKE ?', [$likeWilayah]);
                            }
                            if ($schema->hasColumn($table, 'kecamatan')) {
                                $w->orWhereRaw('LOWER(kecamatan) LIKE ?', [$likeWilayah]);
                            }
                        }
                    });
                }
            }
            return;
        }

        // Default global config fallback if user has no daerah
        $configPoldaId = trim((string) config('satupeta.polda_id', ''));
        if ($configPoldaId !== '') {
            if ($table === 'smpc_tbl_data_cctv' && $schema->hasColumn($table, 'polda_id')) {
                $query->where('polda_id', $configPoldaId);
            } elseif ($schema->hasColumn($table, 'da_id')) {
                $query->where('da_id', $configPoldaId);
            }
        }
    }
}
