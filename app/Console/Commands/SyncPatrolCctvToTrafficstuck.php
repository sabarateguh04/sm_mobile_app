<?php

namespace App\Console\Commands;

use App\Http\Controllers\CctvController;
use App\Support\PatrolCctvOpsdalSync;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class SyncPatrolCctvToTrafficstuck extends Command
{
    protected $signature = 'patrol:sync-opsdal {--limit=0 : Batas baris (0 = semua)}';

    protected $description = 'Salin laporan patrolicctv ke modul Opsdal (perlambatan → trafficstuck, lainnya → contigency)';

    public function handle(): int
    {
        $table = CctvController::resolveCctvTableName();
        $pk = CctvController::resolveCctvPrimaryKey($table);

        if (! \Illuminate\Support\Facades\Schema::hasTable($table)) {
            $this->error('Tabel patroli tidak tersedia.');

            return self::FAILURE;
        }

        $q = DB::table($table)->orderBy($pk);
        $limit = (int) $this->option('limit');
        if ($limit > 0) {
            $q->limit($limit);
        }

        $rows = $q->get();
        $perlambatan = 0;
        $kontijensi = 0;
        $gagal = 0;

        foreach ($rows as $row) {
            $result = PatrolCctvOpsdalSync::syncFromPatrol((int) $row->{$pk}, (array) $row, $table);
            if (! ($result['ok'] ?? false)) {
                $gagal++;

                continue;
            }
            if (($result['target'] ?? '') === 'trafficstuck') {
                $perlambatan++;
            } else {
                $kontijensi++;
            }
        }

        $this->info("Selesai: {$perlambatan} → Kodal Perlambatan, {$kontijensi} → Kodal Kontijensi, {$gagal} gagal.");

        return $gagal > 0 ? self::FAILURE : self::SUCCESS;
    }
}
