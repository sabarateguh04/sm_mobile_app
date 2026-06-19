<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('trafficstuck') || Schema::hasColumn('trafficstuck', 'sb_grp')) {
            return;
        }

        Schema::table('trafficstuck', function (Blueprint $table) {
            $table->string('sb_grp', 32)->nullable()->after('id_trafficstuck');
            $table->index('sb_grp');
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('trafficstuck') || ! Schema::hasColumn('trafficstuck', 'sb_grp')) {
            return;
        }

        Schema::table('trafficstuck', function (Blueprint $table) {
            $table->dropIndex(['sb_grp']);
            $table->dropColumn('sb_grp');
        });
    }
};
