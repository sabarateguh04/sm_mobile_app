<?php

namespace App\Support;

use App\Http\Controllers\CctvController;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

/**
 * Salin laporan patroli CCTV ke tabel trafficstuck (Kodal Perlambatan / Opsdal).
 */
class PatrolCctvTrafficstuckSync
{
    public const TABLE = 'trafficstuck';

    public const PRIMARY_KEY = 'id_trafficstuck';

    public const LINK_COLUMN = 'id_patrolicctv';

    public const SB_GRP_OPSDAL = 'opsdal';

    public static function enabled(): bool
    {
        if (! Schema::hasTable(self::TABLE)) {
            return false;
        }

        $patrolTable = CctvController::resolveCctvTableName();

        return Schema::hasTable($patrolTable)
            && Schema::hasColumn($patrolTable, CctvController::resolveCctvPrimaryKey($patrolTable));
    }

    /**
     * @return array{ok:bool, message?:string}
     */
    public static function upsertFromPatrol(int $patrolId, array $patrolPayload, ?string $patrolTable = null): array
    {
        if (! self::enabled() || $patrolId <= 0) {
            return ['ok' => false, 'message' => 'Sinkron patroli tidak aktif (tabel tidak siap).'];
        }

        try {
            $row = self::mapPatrolPayloadToTrafficstuck($patrolPayload, $patrolTable);
            if ($row === []) {
                return ['ok' => false, 'message' => 'Payload patroli kosong.'];
            }

            if (self::hasLinkColumn()) {
                $existingId = DB::table(self::TABLE)
                    ->where(self::LINK_COLUMN, $patrolId)
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

                $row[self::LINK_COLUMN] = $patrolId;
            }

            self::insertRow($row);

            return ['ok' => true];
        } catch (\Throwable $e) {
            Log::warning('PatrolCctvTrafficstuckSync gagal', [
                'patrol_id' => $patrolId,
                'error' => $e->getMessage(),
            ]);

            return ['ok' => false, 'message' => $e->getMessage()];
        }
    }

    public static function deleteLinked(int $patrolId): void
    {
        if (! Schema::hasTable(self::TABLE) || $patrolId <= 0 || ! self::hasLinkColumn()) {
            return;
        }

        try {
            DB::table(self::TABLE)->where(self::LINK_COLUMN, $patrolId)->delete();
        } catch (\Throwable $e) {
            Log::warning('PatrolCctvTrafficstuckSync delete gagal', [
                'patrol_id' => $patrolId,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * @param  array<string, mixed>  $patrolPayload
     * @return array<string, mixed>
     */
    public static function mapPatrolPayloadToTrafficstuck(array $patrolPayload, ?string $patrolTable = null): array
    {
        $cols = CctvController::resolvePatrolColumns($patrolTable);

        $tanggal = self::patrolValue($patrolPayload, $cols, 'tanggal') ?? now()->toDateString();
        $lokasi = self::patrolValue($patrolPayload, $cols, 'lokasi') ?? '';
        $situasiLalin = self::patrolValue($patrolPayload, $cols, 'situasilalin') ?? '';
        $situasiCuaca = self::patrolValue($patrolPayload, $cols, 'situasicuaca') ?? '';
        $peristiwa = self::patrolValue($patrolPayload, $cols, 'peristiwa') ?? '';
        $ketcb = self::patrolValue($patrolPayload, $cols, 'ketcb') ?? '';

        $ketParts = array_filter([
            $situasiCuaca !== '' ? 'Cuaca: '.$situasiCuaca : null,
            $ketcb !== '' ? $ketcb : null,
            $situasiLalin !== '' ? 'Lalin: '.$situasiLalin : null,
        ]);
        $ketcbMerged = $ketParts !== [] ? implode(' | ', $ketParts) : '';

        $fotoCol = $cols['foto'] ?? 'fotosit';
        $foto = isset($patrolPayload[$fotoCol]) ? (string) $patrolPayload[$fotoCol] : '';

        $piket = isset($patrolPayload['piket']) ? (string) $patrolPayload['piket'] : '';

        $mapped = [
            'piket' => $piket,
            'tanggal' => $tanggal,
            'lokasi' => mb_substr($lokasi !== '' ? $lokasi : '-', 0, 100),
            'faktor' => mb_substr($situasiLalin !== '' ? $situasiLalin : '-', 0, 100),
            'penyebab' => mb_substr($peristiwa !== '' ? $peristiwa : '-', 0, 100),
            'durasi' => '-',
            'timurai' => '-',
            'ketcb' => mb_substr($ketcbMerged, 0, 100),
            'jamperistiwa' => now()->format('H:i:s'),
            'fotosituasi' => mb_substr($foto, 0, 255),
        ];

        if (Schema::hasColumn(self::TABLE, 'koordinat') && isset($patrolPayload['koordinat'])) {
            $mapped['koordinat'] = mb_substr((string) $patrolPayload['koordinat'], 0, 100);
        }

        if (Schema::hasColumn(self::TABLE, 'sb_grp')) {
            $mapped['sb_grp'] = self::SB_GRP_OPSDAL;
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
     * @param  array<string, string|null>  $cols
     * @param  array<string, mixed>  $payload
     */
    private static function patrolValue(array $payload, array $cols, string $logical): ?string
    {
        $dbCol = $cols[$logical] ?? null;
        if ($dbCol === null || ! array_key_exists($dbCol, $payload)) {
            return null;
        }

        $value = $payload[$dbCol];
        if ($value === null || $value === '') {
            return null;
        }

        return (string) $value;
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
