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
        $poldaId = trim((string) config('satupeta.polda_id', ''));

        if ($poldaId !== '') {
            if ($table === 'smpc_tbl_data_cctv' && $schema->hasColumn($table, 'polda_id')) {
                $query->where('polda_id', $poldaId);
                return;
            } elseif ($schema->hasColumn($table, 'da_id')) {
                $query->where('da_id', $poldaId);
                return;
            }
        }

        $daerah = trim((string) ($user->daerah ?? ''));
        if ($daerah === '') {
            return;
        }

        $clean = mb_strtolower((string) preg_replace('/^(polda|provinsi|prov\.?)\s+/iu', '', $daerah));
        if ($clean === '') {
            return;
        }

        $like = '%'.$clean.'%';
        $query->where(function ($w) use ($like, $table) {
            $w->whereRaw('LOWER(nama_cctv) LIKE ?', [$like]);
            if ($table !== 'smpc_tbl_data_cctv') {
                $w->orWhereRaw('LOWER(channel_name) LIKE ?', [$like]);
            }
        });
    }
}
