<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('datapnp') || ! Schema::hasColumn('datapnp', 'id_datapnp')) {
            return;
        }

        $idColumn = $this->columnInfo('id_datapnp');
        if ($idColumn && str_contains((string) ($idColumn->Extra ?? ''), 'auto_increment')) {
            return;
        }

        $this->stripAutoIncrementFromOtherColumns();

        if (! $this->isPrimaryKeyColumn('id_datapnp')) {
            if ($this->hasPrimaryKey()) {
                DB::statement('ALTER TABLE `datapnp` DROP PRIMARY KEY');
            }

            DB::statement('ALTER TABLE `datapnp` MODIFY `id_datapnp` INT(11) NOT NULL');
            DB::statement('ALTER TABLE `datapnp` ADD PRIMARY KEY (`id_datapnp`)');
        }

        $next = max(1, ((int) DB::table('datapnp')->max('id_datapnp')) + 1);
        DB::statement("ALTER TABLE `datapnp` MODIFY `id_datapnp` INT(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT={$next}");
    }

    public function down(): void
    {
        if (! Schema::hasTable('datapnp') || ! Schema::hasColumn('datapnp', 'id_datapnp')) {
            return;
        }

        $idColumn = $this->columnInfo('id_datapnp');
        if (! $idColumn || ! str_contains((string) ($idColumn->Extra ?? ''), 'auto_increment')) {
            return;
        }

        DB::statement('ALTER TABLE `datapnp` MODIFY `id_datapnp` INT(11) NOT NULL');
    }

    private function columnInfo(string $field): ?object
    {
        return collect(DB::select('SHOW COLUMNS FROM `datapnp` WHERE Field = ?', [$field]))->first();
    }

    /** @return list<object> */
    private function primaryKeyColumns(): array
    {
        return DB::select("SHOW INDEX FROM `datapnp` WHERE Key_name = 'PRIMARY'");
    }

    private function hasPrimaryKey(): bool
    {
        return $this->primaryKeyColumns() !== [];
    }

    private function isPrimaryKeyColumn(string $field): bool
    {
        return collect($this->primaryKeyColumns())->contains(
            fn ($idx) => ($idx->Column_name ?? '') === $field
        );
    }

    private function stripAutoIncrementFromOtherColumns(): void
    {
        foreach (DB::select('SHOW COLUMNS FROM `datapnp`') as $col) {
            if (($col->Field ?? '') === 'id_datapnp') {
                continue;
            }
            if (! str_contains((string) ($col->Extra ?? ''), 'auto_increment')) {
                continue;
            }

            $null = ($col->Null ?? '') === 'YES' ? 'NULL' : 'NOT NULL';
            $default = $this->defaultClause($col);
            DB::statement("ALTER TABLE `datapnp` MODIFY `{$col->Field}` {$col->Type} {$null}{$default}");
        }
    }

    private function defaultClause(object $col): string
    {
        if (($col->Default ?? null) === null) {
            return ($col->Null ?? '') === 'YES' ? ' DEFAULT NULL' : '';
        }

        if (is_numeric($col->Default)) {
            return ' DEFAULT '.$col->Default;
        }

        return " DEFAULT '".str_replace("'", "''", (string) $col->Default)."'";
    }
};
