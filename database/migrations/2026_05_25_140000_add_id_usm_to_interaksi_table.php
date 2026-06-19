<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('interaksi') || Schema::hasColumn('interaksi', 'id_usm')) {
            return;
        }

        Schema::table('interaksi', function (Blueprint $table) {
            $table->unsignedInteger('id_usm')->nullable()->after('tanggal')->comment('Akun USM pemilik input');
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('interaksi') || ! Schema::hasColumn('interaksi', 'id_usm')) {
            return;
        }

        Schema::table('interaksi', function (Blueprint $table) {
            $table->dropColumn('id_usm');
        });
    }
};
