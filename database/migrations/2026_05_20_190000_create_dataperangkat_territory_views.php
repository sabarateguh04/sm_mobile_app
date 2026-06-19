<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('dataperangkat') || ! Schema::hasTable('usm')) {
            return;
        }

        $this->dropTableOrView('nas_datadevice');
        DB::statement(<<<'SQL'
CREATE VIEW `nas_datadevice` AS
SELECT
    d.id_perangkat,
    d.piket,
    d.tanggal,
    d.katperangkat,
    d.nameperangkat,
    d.lokasi,
    d.owner,
    d.qtty,
    d.supportfor,
    d.fotoperangkat,
    u.daerah,
    u.wilayah
FROM dataperangkat d
INNER JOIN usm u ON d.piket = u.id_usm
SQL);

        $this->dropTableOrView('wil_dataperangkat');
        DB::statement(<<<'SQL'
CREATE VIEW `wil_dataperangkat` AS
SELECT
    d.id_perangkat,
    d.piket,
    d.tanggal,
    d.katperangkat,
    d.nameperangkat,
    d.lokasi,
    d.owner,
    d.qtty,
    d.supportfor,
    d.fotoperangkat,
    u.wilayah
FROM dataperangkat d
INNER JOIN usm u ON d.piket = u.id_usm
SQL);
    }

    public function down(): void
    {
        $this->dropTableOrView('wil_dataperangkat');
        $this->dropTableOrView('nas_datadevice');
    }

    /**
     * Hapus objek (view atau tabel stand-in phpMyAdmin) secara aman.
     * Gunakan raw query berparameter agar DATABASE() dievaluasi sebagai fungsi MySQL.
     */
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
