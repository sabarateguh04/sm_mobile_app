<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Sesuaikan dengan database `sbo`:
     * - Drop tabel `terminals` & `jalan_gangguan` baru (tidak dipakai, ganti ke `dataterminal` & `datagangguan` sbo).
     * - Tambah kolom `koordinat varchar(100)` ke `datagangguan` dan `posyan` (untuk pin di peta).
     */
    public function up(): void
    {
        Schema::dropIfExists('terminals');
        Schema::dropIfExists('jalan_gangguan');

        if (Schema::hasTable('datagangguan') && !Schema::hasColumn('datagangguan', 'koordinat')) {
            Schema::table('datagangguan', function (Blueprint $table) {
                $table->string('koordinat', 100)->nullable()->after('teamsurvayor');
            });
        }

        if (Schema::hasTable('posyan') && !Schema::hasColumn('posyan', 'koordinat')) {
            Schema::table('posyan', function (Blueprint $table) {
                $table->string('koordinat', 100)->nullable()->after('lokasiposyan');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('datagangguan') && Schema::hasColumn('datagangguan', 'koordinat')) {
            Schema::table('datagangguan', function (Blueprint $table) {
                $table->dropColumn('koordinat');
            });
        }

        if (Schema::hasTable('posyan') && Schema::hasColumn('posyan', 'koordinat')) {
            Schema::table('posyan', function (Blueprint $table) {
                $table->dropColumn('koordinat');
            });
        }
    }
};
