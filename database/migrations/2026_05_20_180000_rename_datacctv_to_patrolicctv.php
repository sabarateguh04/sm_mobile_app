<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('patrolicctv') || ! Schema::hasTable('datacctv')) {
            return;
        }

        Schema::rename('datacctv', 'patrolicctv');

        $this->recreateDatacctvViews('patrolicctv');
    }

    public function down(): void
    {
        if (Schema::hasTable('datacctv') || ! Schema::hasTable('patrolicctv')) {
            return;
        }

        Schema::rename('patrolicctv', 'datacctv');

        $this->recreateDatacctvViews('datacctv');
    }

    private function recreateDatacctvViews(string $table): void
    {
        $this->dropTableOrView('nas_datacctv');
        $this->dropTableOrView('wil_datacctv');

        DB::statement(<<<SQL
CREATE VIEW `nas_datacctv` AS
SELECT
    d.`id_datacctv` AS `id_datacctv`,
    d.`piket` AS `piket`,
    d.`owner` AS `owner`,
    d.`lokasi` AS `lokasi`,
    d.`ipaddres` AS `ipaddres`,
    d.`brand` AS `brand`,
    d.`fungsicctv` AS `fungsicctv`,
    d.`tanggal` AS `tanggal`,
    d.`koordinat` AS `koordinat`,
    d.`fotofisik` AS `fotofisik`,
    u.`daerah` AS `daerah`,
    u.`wilayah` AS `wilayah`
FROM (`{$table}` d JOIN `usm` u ON (d.`piket` = u.`id_usm`))
SQL);

        DB::statement(<<<SQL
CREATE VIEW `wil_datacctv` AS
SELECT
    d.`id_datacctv` AS `id_datacctv`,
    d.`piket` AS `piket`,
    d.`owner` AS `owner`,
    d.`lokasi` AS `lokasi`,
    d.`ipaddres` AS `ipaddres`,
    d.`brand` AS `brand`,
    d.`fungsicctv` AS `fungsicctv`,
    d.`tanggal` AS `tanggal`,
    d.`koordinat` AS `koordinat`,
    u.`wilayah` AS `wilayah`
FROM (`{$table}` d JOIN `usm` u ON (d.`piket` = u.`id_usm`))
SQL);
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
