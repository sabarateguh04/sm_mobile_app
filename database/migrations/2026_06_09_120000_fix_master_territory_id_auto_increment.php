<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Perbaiki kolom `id` master_provinsi / master_kabupaten_kota / master_territori
 * agar PRIMARY KEY + AUTO_INCREMENT.
 *
 * Skema impor dump phpMyAdmin sering punya `id` NOT NULL tanpa AUTO_INCREMENT,
 * sehingga INSERT gagal dengan:
 *   "Field 'id' doesn't have a default value"
 */
return new class extends Migration
{
    public function up(): void
    {
        foreach (['master_provinsi', 'master_kabupaten_kota', 'master_territori', 'master_pusat'] as $table) {
            $this->fixTableIdAutoIncrement($table);
        }
    }

    public function down(): void
    {
        // Tidak mengembalikan skema non–auto-increment.
    }

    private function fixTableIdAutoIncrement(string $table): void
    {
        if (! Schema::hasTable($table) || ! Schema::hasColumn($table, 'id')) {
            return;
        }

        $meta = DB::selectOne(
            'SELECT COLUMN_TYPE, COLUMN_KEY, EXTRA, IS_NULLABLE
             FROM information_schema.COLUMNS
             WHERE TABLE_SCHEMA = DATABASE()
               AND TABLE_NAME = ?
               AND COLUMN_NAME = ?',
            [$table, 'id']
        );

        if ($meta === null) {
            return;
        }

        if (stripos((string) ($meta->EXTRA ?? ''), 'auto_increment') !== false) {
            return;
        }

        $type = $this->sqlIdType((string) ($meta->COLUMN_TYPE ?? ''));

        $this->normalizeIdValues($table);

        $primaryCols = $this->primaryKeyColumnNames($table);
        $idIsPrimary = $primaryCols === ['id'];

        DB::statement('SET FOREIGN_KEY_CHECKS=0');

        try {
            if (! $idIsPrimary) {
                if ($primaryCols !== []) {
                    DB::statement("ALTER TABLE `{$table}` DROP PRIMARY KEY");
                }

                DB::statement("ALTER TABLE `{$table}` ADD PRIMARY KEY (`id`)");
            }

            DB::statement("ALTER TABLE `{$table}` MODIFY `id` {$type} NOT NULL AUTO_INCREMENT");

            $next = max(1, ((int) DB::table($table)->max('id')) + 1);
            DB::statement("ALTER TABLE `{$table}` AUTO_INCREMENT = {$next}");
        } finally {
            DB::statement('SET FOREIGN_KEY_CHECKS=1');
        }
    }

    /**
     * Isi id kosong/0 dan perbaiki duplikat sebelum PRIMARY KEY.
     */
    private function normalizeIdValues(string $table): void
    {
        $nextId = (int) (DB::table($table)->max('id') ?? 0);
        $nameCol = Schema::hasColumn($table, 'nama') ? 'nama' : null;
        $hasParent = Schema::hasColumn($table, 'parent_id');

        if ($nameCol === null) {
            return;
        }

        $badRows = DB::table($table)
            ->where(function ($q) {
                $q->whereNull('id')->orWhere('id', 0);
            })
            ->orderBy($nameCol)
            ->get($hasParent ? ['parent_id', $nameCol] : [$nameCol]);

        foreach ($badRows as $row) {
            $nextId++;
            $q = DB::table($table)->where($nameCol, $row->{$nameCol});
            if ($hasParent) {
                if ($row->parent_id === null) {
                    $q->whereNull('parent_id');
                } else {
                    $q->where('parent_id', $row->parent_id);
                }
            }
            $q->where(function ($inner) {
                $inner->whereNull('id')->orWhere('id', 0);
            })->limit(1)->update(['id' => $nextId]);
        }

        $dupes = DB::table($table)
            ->select('id')
            ->whereNotNull('id')
            ->where('id', '!=', 0)
            ->groupBy('id')
            ->havingRaw('COUNT(*) > 1')
            ->pluck('id');

        foreach ($dupes as $dupId) {
            $rows = DB::table($table)
                ->where('id', $dupId)
                ->orderBy($nameCol)
                ->get($hasParent ? ['parent_id', $nameCol] : [$nameCol]);

            foreach ($rows->skip(1) as $row) {
                $nextId++;
                $q = DB::table($table)->where('id', $dupId)->where($nameCol, $row->{$nameCol});
                if ($hasParent) {
                    if ($row->parent_id === null) {
                        $q->whereNull('parent_id');
                    } else {
                        $q->where('parent_id', $row->parent_id);
                    }
                }
                $q->limit(1)->update(['id' => $nextId]);
            }
        }
    }

    /**
     * @return list<string>
     */
    private function primaryKeyColumnNames(string $table): array
    {
        $indexes = DB::select("SHOW INDEX FROM `{$table}` WHERE Key_name = 'PRIMARY'");

        if ($indexes === []) {
            return [];
        }

        usort($indexes, fn ($a, $b) => (int) $a->Seq_in_index <=> (int) $b->Seq_in_index);

        return array_values(array_map(fn ($row) => (string) $row->Column_name, $indexes));
    }

    private function sqlIdType(string $columnType): string
    {
        $columnType = strtolower($columnType);

        if (str_contains($columnType, 'bigint')) {
            return 'BIGINT UNSIGNED';
        }

        if (str_contains($columnType, 'int')) {
            return 'INT UNSIGNED';
        }

        return 'BIGINT UNSIGNED';
    }
};
