<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('layanan_data') || ! Schema::hasColumn('layanan_data', 'piket')) {
            return;
        }

        Schema::table('layanan_data', function (Blueprint $table) {
            $table->dropColumn('piket');
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('layanan_data') || Schema::hasColumn('layanan_data', 'piket')) {
            return;
        }

        Schema::table('layanan_data', function (Blueprint $table) {
            $table->unsignedInteger('piket')->nullable()->after('tanggal');
        });
    }
};
