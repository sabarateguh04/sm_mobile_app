<?php

namespace App\Console\Commands;

use App\Support\TrafficDalkotAnalyticSync;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class SyncTrafficDalkotToAnalytic extends Command
{
    protected $signature = 'traffic:sync-dalkot-analytic {--limit=0 : Batas baris (0 = semua)}';

    protected $description = 'Salin trafficdalkot ke Traffic Analytic (trafficstuck patrol_online)';

    public function handle(): int
    {
        if (! TrafficDalkotAnalyticSync::enabled()) {
            $this->error('Tabel trafficdalkot atau trafficstuck tidak tersedia.');

            return self::FAILURE;
        }

        $q = DB::table('trafficdalkot')->orderBy('id_trafficdalkot');
        $limit = (int) $this->option('limit');
        if ($limit > 0) {
            $q->limit($limit);
        }

        $ok = 0;
        $fail = 0;

        foreach ($q->get() as $row) {
            $result = TrafficDalkotAnalyticSync::upsertFromDalkot((int) $row->id_trafficdalkot, (array) $row);
            if ($result['ok'] ?? false) {
                $ok++;
            } else {
                $fail++;
            }
        }

        $this->info("Selesai: {$ok} disalin ke Traffic Analytic, {$fail} gagal.");

        return $fail > 0 ? self::FAILURE : self::SUCCESS;
    }
}
