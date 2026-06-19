<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('trafficdalkot', function (Blueprint $table) {
            if (! Schema::hasColumn('trafficdalkot', 'koordinat')) {
                $table->string('koordinat', 100)->nullable()->after('fotosit');
            }
        });
    }

    public function down(): void
    {
        Schema::table('trafficdalkot', function (Blueprint $table) {
            $table->dropColumn('koordinat');
        });
    }
};
