<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pengunjungposyan', function (Blueprint $table) {
            if (! Schema::hasColumn('pengunjungposyan', 'koordinat')) {
                $table->string('koordinat', 100)->nullable()->after('fotogiat');
            }
        });
    }

    public function down(): void
    {
        Schema::table('pengunjungposyan', function (Blueprint $table) {
            $table->dropColumn('koordinat');
        });
    }
};
