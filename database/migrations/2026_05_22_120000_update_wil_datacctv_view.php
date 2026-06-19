<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Perbarui view wil_datacctv: kolom fotofisik + hanya baris inventaris (bukan patroli) bila base = patrolicctv.
 */
return new class extends Migration
{
    public function up(): void
    {
        $base = $this->inventarisBaseTable();
        if ($base === null || ! Schema::hasTable('usm')) {
            return;
        }

        $idCol = Schema::hasColumn($base, 'id_datacctv') ? 'id_datacctv' : 'id_patrolicctv';

        $this->dropTableOrView('wil_datacctv');
        DB::statement($this->wilViewSql($base, $idCol));
    }

    public function down(): void
    {
        // View akan dibuat ulang oleh migrasi sebelumnya bila di-rollback berurutan.
    }

    private function inventarisBaseTable(): ?string
    {
        if (Schema::hasTable('datacctv')) {
            return 'datacctv';
        }

        if (Schema::hasTable('patrolicctv')
            && (Schema::hasColumn('patrolicctv', 'owner') || Schema::hasColumn('patrolicctv', 'brand'))) {
            return 'patrolicctv';
        }

        return null;
    }

    private function wilViewSql(string $base, string $idCol): string
    {
        $koordinat = Schema::hasColumn($base, 'koordinat') ? "d.`koordinat` AS `koordinat`," : '';
        $fotofisik = Schema::hasColumn($base, 'fotofisik') ? "d.`fotofisik` AS `fotofisik`," : '';
        $inventarisOnly = $base === 'patrolicctv'
            ? "WHERE d.`owner` IS NOT NULL AND d.`owner` != '' AND d.`owner` != '-'"
            : '';

        return <<<SQL
CREATE VIEW `wil_datacctv` AS
SELECT
    d.`{$idCol}` AS `id_datacctv`,
    d.`piket` AS `piket`,
    d.`owner` AS `owner`,
    d.`lokasi` AS `lokasi`,
    d.`ipaddres` AS `ipaddres`,
    d.`brand` AS `brand`,
    d.`fungsicctv` AS `fungsicctv`,
    d.`tanggal` AS `tanggal`,
    {$koordinat}
    {$fotofisik}
    u.`wilayah` AS `wilayah`
FROM (`{$base}` d JOIN `usm` u ON (d.`piket` = u.`id_usm`))
{$inventarisOnly}
SQL;
    }

    private function dropTableOrView(string $name): void
    {
        $rows = DB::select(
            'SELECT TABLE_TYPE FROM information_schema.TABLES WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ?',
            [$name]
        );

        if (empty($rows)) {
            return;
        }

        $type = $rows[0]->TABLE_TYPE ?? ($rows[0]->table_type ?? null);

        if ($type === 'VIEW') {
            DB::statement("DROP VIEW IF EXISTS `{$name}`");
        } else {
            DB::statement("DROP TABLE IF EXISTS `{$name}`");
        }
    }
};
