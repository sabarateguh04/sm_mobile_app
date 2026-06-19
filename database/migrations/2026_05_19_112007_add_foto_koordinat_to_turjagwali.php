<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('turjagwali', function (Blueprint $table) {
            if (! Schema::hasColumn('turjagwali', 'fotolokasi')) {
                if (Schema::hasColumn('turjagwali', 'fotogiat')) {
                    $table->string('fotolokasi', 255)->nullable()->after('fotogiat');
                } else {
                    $table->string('fotolokasi', 255)->nullable();
                }
            }
            if (! Schema::hasColumn('turjagwali', 'koordinat')) {
                if (Schema::hasColumn('turjagwali', 'fotolokasi')) {
                    $table->string('koordinat', 100)->nullable()->after('fotolokasi');
                } else {
                    $table->string('koordinat', 100)->nullable();
                }
            }
        });
    }

    public function down(): void
    {
        Schema::table('turjagwali', function (Blueprint $table) {
            $table->dropColumn(['fotolokasi', 'koordinat']);
        });
    }
};
