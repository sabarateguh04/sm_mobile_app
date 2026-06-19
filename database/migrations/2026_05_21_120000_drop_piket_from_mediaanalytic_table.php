<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('mediaanalytic') || ! Schema::hasColumn('mediaanalytic', 'piket')) {
            return;
        }

        Schema::table('mediaanalytic', function (Blueprint $table) {
            $table->dropColumn('piket');
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('mediaanalytic') || Schema::hasColumn('mediaanalytic', 'piket')) {
            return;
        }

        Schema::table('mediaanalytic', function (Blueprint $table) {
            $table->string('piket', 255)->nullable()->after('tanggal');
        });
    }
};
