<?php

namespace App\Support;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

final class SmLiteSyncRuntime
{
    public static function startBatch(string $direction, string $module, array $meta = []): string
    {
        $batchKey = now()->format('YmdHis').'-'.Str::lower(Str::random(8));

        if (self::hasTable('sm_sync_batches')) {
            DB::table('sm_sync_batches')->insert([
                'batch_key' => $batchKey,
                'direction' => $direction,
                'module' => $module,
                'status' => 'running',
                'total_rows' => 0,
                'success_rows' => 0,
                'failed_rows' => 0,
                'retried_rows' => 0,
                'started_at' => now(),
                'meta' => json_encode($meta, JSON_UNESCAPED_UNICODE),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        return $batchKey;
    }

    public static function finishBatch(string $batchKey, string $status, int $total, int $ok, int $failed, int $retried = 0, ?string $error = null): void
    {
        if (! self::hasTable('sm_sync_batches')) {
            return;
        }

        DB::table('sm_sync_batches')
            ->where('batch_key', $batchKey)
            ->update([
                'status' => $status,
                'total_rows' => $total,
                'success_rows' => $ok,
                'failed_rows' => $failed,
                'retried_rows' => $retried,
                'error_message' => $error,
                'finished_at' => now(),
                'updated_at' => now(),
            ]);
    }

    /**
     * @return array{last_id:int,last_timestamp:?string}
     */
    public static function watermark(string $syncKey): array
    {
        if (! self::hasTable('sm_sync_watermarks')) {
            return ['last_id' => 0, 'last_timestamp' => null];
        }

        $row = DB::table('sm_sync_watermarks')
            ->where('sync_key', $syncKey)
            ->first();

        if (! $row) {
            return ['last_id' => 0, 'last_timestamp' => null];
        }

        return [
            'last_id' => (int) ($row->last_id ?? 0),
            'last_timestamp' => isset($row->last_timestamp) ? (string) $row->last_timestamp : null,
        ];
    }

    public static function setWatermark(string $syncKey, int $lastId, ?string $lastTimestamp = null): void
    {
        if (! self::hasTable('sm_sync_watermarks')) {
            return;
        }

        $exists = DB::table('sm_sync_watermarks')
            ->where('sync_key', $syncKey)
            ->exists();

        $payload = [
            'sync_key' => $syncKey,
            'last_id' => $lastId,
            'last_timestamp' => $lastTimestamp,
            'updated_at' => now(),
        ];

        if ($exists) {
            DB::table('sm_sync_watermarks')
                ->where('sync_key', $syncKey)
                ->update($payload);

            return;
        }

        DB::table('sm_sync_watermarks')->insert($payload);
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    public static function deadLetter(string $batchKey, string $direction, string $module, string $sourceTable, string $sourcePk, array $payload, string $error): void
    {
        if (! self::hasTable('sm_sync_dead_letters')) {
            return;
        }

        DB::table('sm_sync_dead_letters')->insert([
            'batch_key' => $batchKey,
            'direction' => $direction,
            'module' => $module,
            'source_table' => $sourceTable,
            'source_pk' => $sourcePk,
            'payload' => json_encode($payload, JSON_UNESCAPED_UNICODE),
            'error_message' => mb_substr($error, 0, 2000),
            'failed_at' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    private static function hasTable(string $table): bool
    {
        try {
            return \Illuminate\Support\Facades\Schema::hasTable($table);
        } catch (\Throwable) {
            return false;
        }
    }
}
