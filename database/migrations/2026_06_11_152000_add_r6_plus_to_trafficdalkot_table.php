<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('trafficdalkot') || Schema::hasColumn('trafficdalkot', 'r6_plus')) {
            return;
        }

        Schema::table('trafficdalkot', function (Blueprint $table) {
            $table->unsignedInteger('r6_plus')->default(0)->after('r6');
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('trafficdalkot') || ! Schema::hasColumn('trafficdalkot', 'r6_plus')) {
            return;
        }

        Schema::table('trafficdalkot', function (Blueprint $table) {
            $table->dropColumn('r6_plus');
        });
    }
};
