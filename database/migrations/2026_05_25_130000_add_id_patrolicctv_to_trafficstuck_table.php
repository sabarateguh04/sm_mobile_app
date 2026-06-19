<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Tautan laporan patroli CCTV → kodal perlambatan (trafficstuck / Opsdal).
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('trafficstuck') || Schema::hasColumn('trafficstuck', 'id_patrolicctv')) {
            return;
        }

        Schema::table('trafficstuck', function (Blueprint $table) {
            $table->unsignedInteger('id_patrolicctv')->nullable()->after('id_trafficstuck');
            $table->unique('id_patrolicctv');
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('trafficstuck') || ! Schema::hasColumn('trafficstuck', 'id_patrolicctv')) {
            return;
        }

        Schema::table('trafficstuck', function (Blueprint $table) {
            $table->dropUnique(['id_patrolicctv']);
            $table->dropColumn('id_patrolicctv');
        });
    }
};
