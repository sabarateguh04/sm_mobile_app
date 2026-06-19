<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('interaksi') || ! Schema::hasColumn('interaksi', 'piket')) {
            return;
        }

        Schema::table('interaksi', function (Blueprint $table) {
            $table->dropColumn('piket');
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('interaksi') || Schema::hasColumn('interaksi', 'piket')) {
            return;
        }

        Schema::table('interaksi', function (Blueprint $table) {
            $table->unsignedInteger('piket')->nullable()->after('tanggal');
        });
    }
};
