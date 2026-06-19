<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('profilposko', function (Blueprint $table) {
            if (! Schema::hasColumn('profilposko', 'koordinat')) {
                $table->string('koordinat', 100)->nullable()->after('fotoposko');
            }
        });
    }

    public function down(): void
    {
        Schema::table('profilposko', function (Blueprint $table) {
            $table->dropColumn('koordinat');
        });
    }
};
