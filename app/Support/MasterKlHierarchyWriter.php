<?php

namespace App\Support;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Insert aman ke master_kl_* bila kolom `id` di DB tidak AUTO_INCREMENT (skema legacy / impor manual).
 */
final class MasterKlHierarchyWriter
{
    public static function ensurePusat(string $nama, ?\DateTimeInterface $now = null): int
    {
        $now ??= now();

        $id = DB::table('master_kl_pusat')
            ->whereNull('parent_id')
            ->where('nama', $nama)
            ->value('id');

        if ($id !== null) {
            return (int) $id;
        }

        return static::insertRow('master_kl_pusat', [
            'parent_id' => null,
            'nama' => $nama,
            'created_at' => $now,
            'updated_at' => $now,
        ]);
    }

    public static function ensureDaerah(int $pusatId, string $nama, ?\DateTimeInterface $now = null): int
    {
        $now ??= now();

        $id = DB::table('master_kl_daerah')
            ->where('parent_id', $pusatId)
            ->where('nama', $nama)
            ->value('id');

        if ($id !== null) {
            return (int) $id;
        }

        return static::insertRow('master_kl_daerah', [
            'parent_id' => $pusatId,
            'nama' => $nama,
            'created_at' => $now,
            'updated_at' => $now,
        ]);
    }

    public static function ensureResort(int $daerahId, string $nama, ?\DateTimeInterface $now = null): int
    {
        $now ??= now();

        $id = DB::table('master_kl_resort')
            ->where('parent_id', $daerahId)
            ->where('nama', $nama)
            ->value('id');

        if ($id !== null) {
            return (int) $id;
        }

        return static::insertRow('master_kl_resort', [
            'parent_id' => $daerahId,
            'nama' => $nama,
            'created_at' => $now,
            'updated_at' => $now,
        ]);
    }

    public static function ensureUnitKerja(int $resortId, string $nama, ?\DateTimeInterface $now = null): void
    {
        if (! Schema::hasTable('master_kl_unit_kerja')) {
            return;
        }

        $now ??= now();

        $exists = DB::table('master_kl_unit_kerja')
            ->where('parent_id', $resortId)
            ->where('nama', $nama)
            ->exists();

        if ($exists) {
            return;
        }

        static::insertRow('master_kl_unit_kerja', [
            'parent_id' => $resortId,
            'nama' => $nama,
            'created_at' => $now,
            'updated_at' => $now,
        ]);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private static function insertRow(string $table, array $data): int
    {
        if (static::idColumnUsesAutoIncrement($table)) {
            return (int) DB::table($table)->insertGetId($data);
        }

        $id = static::nextId($table);
        $data['id'] = $id;
        DB::table($table)->insert($data);

        return $id;
    }

    private static function nextId(string $table): int
    {
        $max = DB::table($table)->max('id');

        return $max !== null ? (int) $max + 1 : 1;
    }

    private static function idColumnUsesAutoIncrement(string $table): bool
    {
        if (! Schema::hasTable($table) || ! Schema::hasColumn($table, 'id')) {
            return true;
        }

        try {
            $row = DB::selectOne(
                'SELECT EXTRA FROM information_schema.COLUMNS
                 WHERE TABLE_SCHEMA = DATABASE()
                   AND TABLE_NAME = ?
                   AND COLUMN_NAME = ?',
                [$table, 'id']
            );
        } catch (\Throwable) {
            return true;
        }

        if ($row === null) {
            return true;
        }

        return stripos((string) ($row->EXTRA ?? ''), 'auto_increment') !== false;
    }
}
