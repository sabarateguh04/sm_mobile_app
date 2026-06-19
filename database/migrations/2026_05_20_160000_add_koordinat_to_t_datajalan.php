<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Lokasi GIS untuk Data Jalan — format "lat,lng" sama seperti dataterminal.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('t_datajalan') && ! Schema::hasColumn('t_datajalan', 'koordinat')) {
            Schema::table('t_datajalan', function (Blueprint $table) {
                $table->string('koordinat', 100)->nullable()->after('prasjal');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('t_datajalan') && Schema::hasColumn('t_datajalan', 'koordinat')) {
            Schema::table('t_datajalan', function (Blueprint $table) {
                $table->dropColumn('koordinat');
            });
        }
    }
};
