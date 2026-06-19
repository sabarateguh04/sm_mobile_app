<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Lokasi GIS untuk QR Perlambatan — format "lat,lng" sama seperti datagangguan / CCTV.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('trafficstuck')) {
            return;
        }

        Schema::table('trafficstuck', function (Blueprint $table) {
            if (! Schema::hasColumn('trafficstuck', 'koordinat')) {
                $table->string('koordinat', 100)->nullable();
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('trafficstuck') || ! Schema::hasColumn('trafficstuck', 'koordinat')) {
            return;
        }

        Schema::table('trafficstuck', function (Blueprint $table) {
            $table->dropColumn('koordinat');
        });
    }
};
