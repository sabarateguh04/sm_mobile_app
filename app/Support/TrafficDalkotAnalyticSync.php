<?php

namespace App\Support;

use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

/**
 * Salin laporan trafficdalkot ke Traffic Analytic (trafficstuck, sb_grp=patrol_online).
 */
class TrafficDalkotAnalyticSync
{
    public const TABLE = 'trafficstuck';

    public const PRIMARY_KEY = 'id_trafficstuck';

    public const LINK_COLUMN = 'id_trafficdalkot';

    public const SB_GRP_PATROL_ONLINE = 'patrol_online';

    public static function enabled(): bool
    {
        return Schema::hasTable(self::TABLE) && Schema::hasTable('trafficdalkot');
    }

    /**
     * @param  array<string, mixed>  $dalkotPayload
     * @return array{ok:bool, message?:string}
     */
    public static function upsertFromDalkot(int $dalkotId, array $dalkotPayload): array
    {
        if (! self::enabled() || $dalkotId <= 0) {
            return ['ok' => false, 'message' => 'Sinkron Traffic Analytic tidak aktif.'];
        }

        try {
            $row = self::mapDalkotToTrafficstuck($dalkotPayload);
            if ($row === []) {
                return ['ok' => false, 'message' => 'Payload dalkot kosong.'];
            }

            if (self::hasLinkColumn()) {
                $existingId = DB::table(self::TABLE)
                    ->where(self::LINK_COLUMN, $dalkotId)
                    ->value(self::PRIMARY_KEY);

                if ($existingId) {
                    unset($row['created_at']);
                    if (Schema::hasColumn(self::TABLE, 'updated_at')) {
                        $row['updated_at'] = now();
                    }

                    DB::table(self::TABLE)
                        ->where(self::PRIMARY_KEY, $existingId)
                        ->update($row);

                    return ['ok' => true];
                }

                $row[self::LINK_COLUMN] = $dalkotId;
            }

            self::insertRow($row);

            return ['ok' => true];
        } catch (\Throwable $e) {
            Log::warning('TrafficDalkotAnalyticSync gagal', [
                'dalkot_id' => $dalkotId,
                'error' => $e->getMessage(),
            ]);

            return ['ok' => false, 'message' => $e->getMessage()];
        }
    }

    public static function deleteLinked(int $dalkotId): void
    {
        if (! self::hasLinkColumn() || $dalkotId <= 0) {
            return;
        }

        try {
            DB::table(self::TABLE)->where(self::LINK_COLUMN, $dalkotId)->delete();
        } catch (\Throwable $e) {
            Log::warning('TrafficDalkotAnalyticSync delete gagal', [
                'dalkot_id' => $dalkotId,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * @param  array<string, mixed>  $payload
     * @return array<string, mixed>
     */
    public static function mapDalkotToTrafficstuck(array $payload): array
    {
        $tanggal = isset($payload['tanggal']) ? (string) $payload['tanggal'] : now()->toDateString();
        $piket = isset($payload['piket']) ? (string) $payload['piket'] : '';
        $namajalan = trim((string) ($payload['namajalan'] ?? ''));
        $gerbang = trim((string) ($payload['gerbangkota'] ?? ''));
        $arah = trim((string) ($payload['araharus'] ?? ''));
        $jenis = trim((string) ($payload['jenisjalan'] ?? ''));
        $ketcb = trim((string) ($payload['ketcb'] ?? ''));
        $r2 = (int) ($payload['r2'] ?? 0);
        $r4 = (int) ($payload['r4'] ?? 0);
        $r6 = (int) ($payload['r6'] ?? 0);
        $r6Plus = (int) ($payload['r6_plus'] ?? 0);
        $jumlah = (int) ($payload['jumlah'] ?? ($r2 + $r4 + $r6 + $r6Plus));

        $lokasi = $namajalan !== '' ? $namajalan : ($gerbang !== '' ? $gerbang : '-');
        if ($gerbang !== '' && $namajalan !== '' && ! str_contains(mb_strtolower($namajalan), mb_strtolower($gerbang))) {
            $lokasi = $namajalan.' ('.$gerbang.')';
        }

        $ketParts = array_filter([
            $ketcb !== '' ? $ketcb : null,
            $gerbang !== '' ? 'Gerbang: '.$gerbang : null,
            $arah !== '' ? 'Arah: '.$arah : null,
            "R2/R4/R6/R6+: {$r2}/{$r4}/{$r6}/{$r6Plus}",
            'Jumlah: '.$jumlah,
        ]);

        $waktu = (string) ($payload['waktu'] ?? now()->format('H:i:s'));
        if (preg_match('/^\d{1,2}:\d{2}$/', $waktu)) {
            $waktu .= ':00';
        }

        $mapped = [
            'piket' => $piket,
            'tanggal' => $tanggal,
            'lokasi' => mb_substr($lokasi, 0, 100),
            'faktor' => mb_substr($jenis !== '' ? $jenis : '-', 0, 100),
            'penyebab' => mb_substr($arah !== '' ? $arah : 'Traffic Dalkot', 0, 100),
            'durasi' => '-',
            'timurai' => '-',
            'ketcb' => mb_substr(implode(' | ', $ketParts), 0, 100),
            'jamperistiwa' => $waktu,
            'fotosituasi' => mb_substr((string) ($payload['fotosit'] ?? ''), 0, 255),
        ];

        if (Schema::hasColumn(self::TABLE, 'koordinat') && ! empty($payload['koordinat'])) {
            $mapped['koordinat'] = mb_substr((string) $payload['koordinat'], 0, 100);
        }

        if (Schema::hasColumn(self::TABLE, 'sb_grp')) {
            $mapped['sb_grp'] = self::SB_GRP_PATROL_ONLINE;
        }

        if (Schema::hasColumn(self::TABLE, 'created_at')) {
            $mapped['created_at'] = now();
        }
        if (Schema::hasColumn(self::TABLE, 'updated_at')) {
            $mapped['updated_at'] = now();
        }

        return self::onlyExistingColumns($mapped);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public static function insertRow(array $data): int
    {
        try {
            return (int) DB::table(self::TABLE)->insertGetId($data, self::PRIMARY_KEY);
        } catch (QueryException $e) {
            if (! str_contains($e->getMessage(), "doesn't have a default value")) {
                throw $e;
            }
        }

        $nextId = (int) DB::table(self::TABLE)->max(self::PRIMARY_KEY) + 1;
        $data[self::PRIMARY_KEY] = $nextId;
        DB::table(self::TABLE)->insert($data);

        return $nextId;
    }

    public static function hasLinkColumn(): bool
    {
        return Schema::hasColumn(self::TABLE, self::LINK_COLUMN);
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    private static function onlyExistingColumns(array $data): array
    {
        $out = [];
        foreach ($data as $key => $value) {
            if (Schema::hasColumn(self::TABLE, $key)) {
                $out[$key] = $value;
            }
        }

        return $out;
    }
}
