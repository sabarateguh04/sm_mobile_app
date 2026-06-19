<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Kolom kepegawaian tingkat 3 (organisasi daerah / master_kl_resort).
 * unit_kerja di usm menjadi tingkat 4 (master_kl_unit_kerja) bila tabel tersebut ada.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('usm')) {
            return;
        }

        if (! Schema::hasColumn('usm', 'organisasi_daerah')) {
            Schema::table('usm', function (Blueprint $table) {
                $table->string('organisasi_daerah', 255)->nullable()->after('pemerintahan_daerah');
            });
        }

        if (! Schema::hasTable('master_kl_resort') || ! Schema::hasColumn('usm', 'organisasi_daerah')) {
            return;
        }

        $resortNames = DB::table('master_kl_resort')
            ->pluck('nama')
            ->map(static fn ($nama): string => mb_strtolower(trim((string) $nama)))
            ->flip()
            ->all();

        DB::table('usm')
            ->whereNotNull('unit_kerja')
            ->where('unit_kerja', '!=', '')
            ->where(function ($q) {
                $q->whereNull('organisasi_daerah')->orWhere('organisasi_daerah', '');
            })
            ->orderBy('id_usm')
            ->chunk(200, function ($rows) use ($resortNames) {
                foreach ($rows as $row) {
                    $unit = trim((string) ($row->unit_kerja ?? ''));
                    if ($unit === '') {
                        continue;
                    }

                    $key = mb_strtolower($unit);
                    if (! isset($resortNames[$key])) {
                        continue;
                    }

                    DB::table('usm')
                        ->where('id_usm', $row->id_usm)
                        ->update([
                            'organisasi_daerah' => $unit,
                            'unit_kerja' => '',
                        ]);
                }
            });
    }

    public function down(): void
    {
        if (! Schema::hasTable('usm') || ! Schema::hasColumn('usm', 'organisasi_daerah')) {
            return;
        }

        Schema::table('usm', function (Blueprint $table) {
            $table->dropColumn('organisasi_daerah');
        });
    }
};
