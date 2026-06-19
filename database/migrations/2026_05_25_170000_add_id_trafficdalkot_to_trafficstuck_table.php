<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Tautan Traffic Dalkot → Traffic Analytic (trafficstuck sb_grp=patrol_online).
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('trafficstuck') || Schema::hasColumn('trafficstuck', 'id_trafficdalkot')) {
            return;
        }

        Schema::table('trafficstuck', function (Blueprint $table) {
            $after = Schema::hasColumn('trafficstuck', 'id_patrolicctv') ? 'id_patrolicctv' : 'id_trafficstuck';
            $table->unsignedInteger('id_trafficdalkot')->nullable()->after($after);
            $table->unique('id_trafficdalkot');
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('trafficstuck') || ! Schema::hasColumn('trafficstuck', 'id_trafficdalkot')) {
            return;
        }

        Schema::table('trafficstuck', function (Blueprint $table) {
            $table->dropUnique(['id_trafficdalkot']);
            $table->dropColumn('id_trafficdalkot');
        });
    }
};
