<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Kolom remember_token untuk fitur "Remember me" Laravel Auth.
     */
    public function up(): void
    {
        if (! Schema::hasTable('usm')) {
            return;
        }

        Schema::table('usm', function (Blueprint $table) {
            if (! Schema::hasColumn('usm', 'remember_token')) {
                $table->rememberToken();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (! Schema::hasTable('usm') || ! Schema::hasColumn('usm', 'remember_token')) {
            return;
        }

        Schema::table('usm', function (Blueprint $table) {
            $table->dropRememberToken();
        });
    }
};
