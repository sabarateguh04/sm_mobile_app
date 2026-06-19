<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Skema patroli CCTV (laporan patrol) — selaras form legacy SBO / production.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('patrolicctv')) {
            Schema::create('patrolicctv', function (Blueprint $table) {
                $table->increments('id_patrolicctv');
                $table->string('piket', 100);
                $table->date('tanggal')->nullable();
                $table->string('lokasi', 100)->nullable();
                $table->string('situasilalin', 100)->nullable();
                $table->string('situasicuaca', 100)->nullable();
                $table->string('peristiwa', 100)->nullable();
                $table->text('ketcb')->nullable();
                $table->string('fotosit', 255)->default('');
            });

            return;
        }

        Schema::table('patrolicctv', function (Blueprint $table) {
            if (! Schema::hasColumn('patrolicctv', 'situasilalin')) {
                $table->string('situasilalin', 100)->nullable()->after('lokasi');
            }
            if (! Schema::hasColumn('patrolicctv', 'situasicuaca')) {
                $table->string('situasicuaca', 100)->nullable()->after('situasilalin');
            }
            if (! Schema::hasColumn('patrolicctv', 'peristiwa')) {
                $table->string('peristiwa', 100)->nullable()->after('situasicuaca');
            }
            if (! Schema::hasColumn('patrolicctv', 'ketcb')) {
                $table->text('ketcb')->nullable()->after('peristiwa');
            }
            if (! Schema::hasColumn('patrolicctv', 'fotosit')) {
                $table->string('fotosit', 255)->nullable()->default('')->after('ketcb');
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('patrolicctv')) {
            return;
        }

        Schema::table('patrolicctv', function (Blueprint $table) {
            foreach (['situasilalin', 'situasicuaca', 'peristiwa', 'ketcb', 'fotosit'] as $col) {
                if (Schema::hasColumn('patrolicctv', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};
