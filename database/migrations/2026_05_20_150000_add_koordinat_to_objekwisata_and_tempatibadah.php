<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Lokasi GIS untuk Objek Wisata & Tempat Ibadah — format "lat,lng" sama seperti dataterminal.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('t_tempatwisata') && ! Schema::hasColumn('t_tempatwisata', 'koordinat')) {
            Schema::table('t_tempatwisata', function (Blueprint $table) {
                $table->string('koordinat', 100)->nullable()->after('lokasi');
            });
        }

        if (Schema::hasTable('tempatibadah') && ! Schema::hasColumn('tempatibadah', 'koordinat')) {
            Schema::table('tempatibadah', function (Blueprint $table) {
                $table->string('koordinat', 100)->nullable()->after('lokasi');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('t_tempatwisata') && Schema::hasColumn('t_tempatwisata', 'koordinat')) {
            Schema::table('t_tempatwisata', function (Blueprint $table) {
                $table->dropColumn('koordinat');
            });
        }

        if (Schema::hasTable('tempatibadah') && Schema::hasColumn('tempatibadah', 'koordinat')) {
            Schema::table('tempatibadah', function (Blueprint $table) {
                $table->dropColumn('koordinat');
            });
        }
    }
};
