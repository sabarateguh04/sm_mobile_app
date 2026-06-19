<?php

namespace App\Support;

use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

final class AnalyticStagingToTrafficstuckSync
{
    public const TARGET_TABLE = 'trafficstuck';

    public const TARGET_PK = 'id_trafficstuck';

    public const LINK_COLUMN = 'id_analytic_staging';

    /**
     * @param  array<string, mixed>  $payload
     * @return array{ok:bool,message?:string}
     */
    public static function upsertFromStaging(int $stagingId, array $payload): array
    {
        if ($stagingId <= 0 || ! self::enabled()) {
            return ['ok' => false, 'message' => 'Sinkron staging Analytic tidak aktif.'];
        }

        try {
            $mapped = self::mapPayload($payload);
            if ($mapped === []) {
                return ['ok' => false, 'message' => 'Payload staging kosong.'];
            }

            if (Schema::hasColumn(self::TARGET_TABLE, self::LINK_COLUMN)) {
                $existingId = DB::table(self::TARGET_TABLE)
                    ->where(self::LINK_COLUMN, $stagingId)
                    ->value(self::TARGET_PK);

                if ($existingId) {
                    unset($mapped['created_at']);
                    if (Schema::hasColumn(self::TARGET_TABLE, 'updated_at')) {
                        $mapped['updated_at'] = now();
                    }

                    DB::table(self::TARGET_TABLE)
                        ->where(self::TARGET_PK, $existingId)
                        ->update($mapped);

                    return ['ok' => true];
                }

                $mapped[self::LINK_COLUMN] = $stagingId;
            }

            self::insertRow($mapped);

            return ['ok' => true];
        } catch (\Throwable $e) {
            Log::warning('AnalyticStagingToTrafficstuckSync gagal', [
                'staging_id' => $stagingId,
                'error' => $e->getMessage(),
            ]);

            return ['ok' => false, 'message' => $e->getMessage()];
        }
    }

    public static function enabled(): bool
    {
        return Schema::hasTable('traffic_counting_staging')
            && Schema::hasTable(self::TARGET_TABLE);
    }

    /**
     * @param  array<string, mixed>  $payload
     * @return array<string, mixed>
     */
    private static function mapPayload(array $payload): array
    {
        $tanggal = isset($payload['tanggal']) ? (string) $payload['tanggal'] : now()->toDateString();
        $lokasi = trim((string) ($payload['lokasi'] ?? '-'));
        $faktor = trim((string) ($payload['jenisjalan'] ?? 'Traffic Counting'));
        $arah = trim((string) ($payload['araharus'] ?? ''));
        $ketcb = trim((string) ($payload['ketcb'] ?? ''));
        $klass = trim((string) ($payload['volume_class'] ?? 'R2'));

        $r2 = (int) ($payload['r2'] ?? 0);
        $r4 = (int) ($payload['r4'] ?? 0);
        $r6 = (int) ($payload['r6'] ?? 0);
        $r6Plus = (int) ($payload['r6_plus'] ?? 0);
        $total = (int) ($payload['jumlah'] ?? ($r2 + $r4 + $r6 + $r6Plus));

        $time = (string) ($payload['waktu'] ?? now()->format('H:i:s'));
        if (preg_match('/^\d{1,2}:\d{2}$/', $time)) {
            $time .= ':00';
        }

        $mergedKetcb = array_filter([
            $ketcb !== '' ? $ketcb : null,
            $arah !== '' ? 'Arah: '.$arah : null,
            "Kelas: {$klass}",
            "R2/R4/R6/R6+: {$r2}/{$r4}/{$r6}/{$r6Plus}",
            'Total: '.$total,
        ]);

        $mapped = [
            'tanggal' => $tanggal,
            'piket' => (string) ($payload['piket'] ?? ''),
            'lokasi' => mb_substr($lokasi === '' ? '-' : $lokasi, 0, 100),
            'faktor' => mb_substr($faktor === '' ? 'Traffic Counting' : $faktor, 0, 100),
            'penyebab' => mb_substr($arah === '' ? 'Analytic Traffic Feed' : $arah, 0, 100),
            'durasi' => '-',
            'timurai' => '-',
            'ketcb' => mb_substr(implode(' | ', $mergedKetcb), 0, 100),
            'jamperistiwa' => $time,
            'sb_grp' => 'patrol_online',
        ];

        if (Schema::hasColumn(self::TARGET_TABLE, 'koordinat') && ! empty($payload['koordinat'])) {
            $mapped['koordinat'] = mb_substr((string) $payload['koordinat'], 0, 100);
        }
        if (Schema::hasColumn(self::TARGET_TABLE, 'fotosituasi') && ! empty($payload['fotosit'])) {
            $mapped['fotosituasi'] = mb_substr((string) $payload['fotosit'], 0, 255);
        }
        if (Schema::hasColumn(self::TARGET_TABLE, 'created_at')) {
            $mapped['created_at'] = now();
        }
        if (Schema::hasColumn(self::TARGET_TABLE, 'updated_at')) {
            $mapped['updated_at'] = now();
        }

        $out = [];
        foreach ($mapped as $key => $value) {
            if (Schema::hasColumn(self::TARGET_TABLE, $key)) {
                $out[$key] = $value;
            }
        }

        return $out;
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private static function insertRow(array $data): int
    {
        try {
            return (int) DB::table(self::TARGET_TABLE)->insertGetId($data, self::TARGET_PK);
        } catch (QueryException $e) {
            if (! str_contains($e->getMessage(), "doesn't have a default value")) {
                throw $e;
            }
        }

        $nextId = (int) DB::table(self::TARGET_TABLE)->max(self::TARGET_PK) + 1;
        $data[self::TARGET_PK] = $nextId;
        DB::table(self::TARGET_TABLE)->insert($data);

        return $nextId;
    }
}
