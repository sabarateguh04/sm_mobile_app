<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pnpangkutan', function (Blueprint $table) {
            if (! Schema::hasColumn('pnpangkutan', 'fotolokasi')) {
                $table->string('fotolokasi', 255)->nullable()->after('keterangan');
            }
            if (! Schema::hasColumn('pnpangkutan', 'koordinat')) {
                $table->string('koordinat', 100)->nullable()->after('fotolokasi');
            }
        });
    }

    public function down(): void
    {
        Schema::table('pnpangkutan', function (Blueprint $table) {
            $table->dropColumn(['fotolokasi', 'koordinat']);
        });
    }
};
