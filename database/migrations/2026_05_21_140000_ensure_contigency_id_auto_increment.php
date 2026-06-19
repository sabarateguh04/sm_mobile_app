<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private const TABLE = 'contigency';

    private const PK = 'id_contigency';

    public function up(): void
    {
        if (! Schema::hasTable(self::TABLE) || ! Schema::hasColumn(self::TABLE, self::PK)) {
            return;
        }

        $idColumn = $this->columnInfo(self::PK);
        if ($idColumn && str_contains((string) ($idColumn->Extra ?? ''), 'auto_increment')) {
            return;
        }

        $this->stripAutoIncrementFromOtherColumns();

        if (! $this->isPrimaryKeyColumn(self::PK)) {
            if ($this->hasPrimaryKey()) {
                DB::statement('ALTER TABLE `'.self::TABLE.'` DROP PRIMARY KEY');
            }

            DB::statement('ALTER TABLE `'.self::TABLE.'` MODIFY `'.self::PK.'` INT(11) NOT NULL');
            DB::statement('ALTER TABLE `'.self::TABLE.'` ADD PRIMARY KEY (`'.self::PK.'`)');
        }

        $next = max(1, ((int) DB::table(self::TABLE)->max(self::PK)) + 1);
        DB::statement('ALTER TABLE `'.self::TABLE.'` MODIFY `'.self::PK.'` INT(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT='.$next);
    }

    public function down(): void
    {
        if (! Schema::hasTable(self::TABLE) || ! Schema::hasColumn(self::TABLE, self::PK)) {
            return;
        }

        $idColumn = $this->columnInfo(self::PK);
        if (! $idColumn || ! str_contains((string) ($idColumn->Extra ?? ''), 'auto_increment')) {
            return;
        }

        DB::statement('ALTER TABLE `'.self::TABLE.'` MODIFY `'.self::PK.'` INT(11) NOT NULL');
    }

    private function columnInfo(string $field): ?object
    {
        return collect(DB::select('SHOW COLUMNS FROM `'.self::TABLE.'` WHERE Field = ?', [$field]))->first();
    }

    /** @return list<object> */
    private function primaryKeyColumns(): array
    {
        return DB::select("SHOW INDEX FROM `".self::TABLE."` WHERE Key_name = 'PRIMARY'");
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
        foreach (DB::select('SHOW COLUMNS FROM `'.self::TABLE.'`') as $col) {
            if (($col->Field ?? '') === self::PK) {
                continue;
            }
            if (! str_contains((string) ($col->Extra ?? ''), 'auto_increment')) {
                continue;
            }

            $null = ($col->Null ?? '') === 'YES' ? 'NULL' : 'NOT NULL';
            $default = $this->defaultClause($col);
            DB::statement('ALTER TABLE `'.self::TABLE."` MODIFY `{$col->Field}` {$col->Type} {$null}{$default}");
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
