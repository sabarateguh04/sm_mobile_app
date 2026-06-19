<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

/**
 * Rename 7 priority tables to match menu/route names for easier tracking & maintenance.
 *
 * Old Name         → New Name           | Menu / Route
 * ──────────────────────────────────────────────────────
 * datapnp          → pnp_angkutan       | PNP Angkutan / pnpangkutan
 * interaksi        → layanan_masyarakat | Layanan Masy / layanan/masyarakat
 * t_tempatwisata   → objek_wisata       | Object Wisata / objekwisata
 * datagangguan     → data_gangguan      | Data Gangguan / jalan_gangguan
 * wanjol           → ops_wanjol         | Peristiwa Menonjol / ops_wanjol
 * dataterminal     → data_terminal      | Data Terminal / terminal
 * posyan           → pos_pelayanan      | Pos Pelayanan / layanan/public-service
 */
return new class extends Migration
{
    /**
     * @var array<string, string>  old => new
     */
    private array $renames = [
        'datapnp'        => 'pnp_angkutan',
        'interaksi'      => 'layanan_masyarakat',
        't_tempatwisata' => 'objek_wisata',
        'datagangguan'   => 'data_gangguan',
        'wanjol'         => 'ops_wanjol',
        'dataterminal'   => 'data_terminal',
        'posyan'         => 'pos_pelayanan',
    ];

    public function up(): void
    {
        foreach ($this->renames as $old => $new) {
            if (Schema::hasTable($old) && ! Schema::hasTable($new)) {
                Schema::rename($old, $new);
            }
        }
    }

    public function down(): void
    {
        foreach ($this->renames as $old => $new) {
            if (Schema::hasTable($new) && ! Schema::hasTable($old)) {
                Schema::rename($new, $old);
            }
        }
    }
};
