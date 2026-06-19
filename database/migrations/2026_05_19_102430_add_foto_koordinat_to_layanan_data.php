<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('layanan_data')) {
            return;
        }

        Schema::table('layanan_data', function (Blueprint $table) {
            if (! Schema::hasColumn('layanan_data', 'foto')) {
                $table->string('foto', 255)->nullable()->after('keterangan');
            }
            if (! Schema::hasColumn('layanan_data', 'koordinat')) {
                $table->string('koordinat', 100)->nullable()->after('foto');
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('layanan_data')) {
            return;
        }

        Schema::table('layanan_data', function (Blueprint $table) {
            $table->dropColumn(['foto', 'koordinat']);
        });
    }
};
