<?php

namespace App\Support;

use App\Http\Controllers\CctvController;

/**
 * Arahkan laporan patroli CCTV ke modul Opsdal yang sesuai.
 */
final class PatrolCctvOpsdalSync
{
    /**
     * @param  array<string, mixed>  $patrolPayload
     * @return array{ok:bool, target?:string, message?:string}
     */
    public static function syncFromPatrol(int $patrolId, array $patrolPayload, ?string $patrolTable = null): array
    {
        if ($patrolId <= 0) {
            return ['ok' => false, 'message' => 'ID patroli tidak valid.'];
        }

        $cols = CctvController::resolvePatrolColumns($patrolTable);
        $peristiwa = self::patrolValue($patrolPayload, $cols, 'peristiwa');
        $situasiLalin = self::patrolValue($patrolPayload, $cols, 'situasilalin');
        $target = PatrolCctvOpsdalClassifier::targetModule($peristiwa, $situasiLalin);

        if ($target === 'trafficstuck') {
            PatrolCctvContigencySync::deleteLinked($patrolId);

            $result = PatrolCctvTrafficstuckSync::upsertFromPatrol($patrolId, $patrolPayload, $patrolTable);
            $result['target'] = 'trafficstuck';

            return $result;
        }

        PatrolCctvTrafficstuckSync::deleteLinked($patrolId);

        $result = PatrolCctvContigencySync::upsertFromPatrol($patrolId, $patrolPayload, $patrolTable);
        $result['target'] = 'contigency';

        return $result;
    }

    public static function deleteAllLinked(int $patrolId): void
    {
        PatrolCctvTrafficstuckSync::deleteLinked($patrolId);
        PatrolCctvContigencySync::deleteLinked($patrolId);
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
}
