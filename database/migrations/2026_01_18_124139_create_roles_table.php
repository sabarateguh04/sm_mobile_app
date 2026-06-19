<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Add role_name column to existing roles table (created by permission package)
        Schema::table('roles', function (Blueprint $table) {
            if (!Schema::hasColumn('roles', 'role_name')) {
                $table->string('role_name')->nullable()->after('name');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('roles', function (Blueprint $table) {
            if (Schema::hasColumn('roles', 'role_name')) {
                $table->dropColumn('role_name');
            }
        });
    }
};
