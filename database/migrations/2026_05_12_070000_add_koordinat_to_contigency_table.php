<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('contigency')) {
            return;
        }
        Schema::table('contigency', function (Blueprint $table) {
            if (! Schema::hasColumn('contigency', 'koordinat')) {
                $table->string('koordinat', 100)->nullable()->after('lokasi');
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('contigency')) {
            return;
        }
        Schema::table('contigency', function (Blueprint $table) {
            if (Schema::hasColumn('contigency', 'koordinat')) {
                $table->dropColumn('koordinat');
            }
        });
    }
};
