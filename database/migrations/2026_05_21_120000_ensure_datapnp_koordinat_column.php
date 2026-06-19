<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('datapnp')) {
            return;
        }

        Schema::table('datapnp', function (Blueprint $table) {
            if (! Schema::hasColumn('datapnp', 'koordinat')) {
                $table->string('koordinat', 100)->nullable()->after('fotogiat');
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('datapnp') || ! Schema::hasColumn('datapnp', 'koordinat')) {
            return;
        }

        Schema::table('datapnp', function (Blueprint $table) {
            $table->dropColumn('koordinat');
        });
    }
};
