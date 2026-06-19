<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('dataperangkat') || ! Schema::hasTable('usm') || ! Schema::hasColumn('dataperangkat', 'piket')) {
            return;
        }

        $rows = DB::table('dataperangkat')
            ->whereNotNull('piket')
            ->where('piket', '!=', '')
            ->get(['id_perangkat', 'piket']);

        foreach ($rows as $row) {
            $piket = trim((string) $row->piket);
            if ($piket === '' || ctype_digit($piket)) {
                if ($piket !== '' && DB::table('usm')->where('id_usm', (int) $piket)->exists()) {
                    continue;
                }
                if ($piket === '') {
                    continue;
                }
            }

            $idUsm = DB::table('usm')->where('username', $piket)->value('id_usm');
            if ($idUsm === null) {
                $idUsm = DB::table('usm')->where('email', $piket)->value('id_usm');
            }

            if ($idUsm !== null && (int) $idUsm > 0) {
                DB::table('dataperangkat')
                    ->where('id_perangkat', $row->id_perangkat)
                    ->update(['piket' => (string) $idUsm]);
            }
        }
    }

    public function down(): void
    {
        // Normalisasi data — tidak di-rollback.
    }
};
