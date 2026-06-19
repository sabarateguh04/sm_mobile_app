<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('auditarus')) {
            return;
        }

        Schema::table('auditarus', function (Blueprint $table) {
            if (! Schema::hasColumn('auditarus', 'fotolokasi')) {
                $table->string('fotolokasi', 255)->nullable()->after('catatan');
            }
            if (! Schema::hasColumn('auditarus', 'koordinat')) {
                $table->string('koordinat', 100)->nullable()->after('fotolokasi');
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('auditarus')) {
            return;
        }

        Schema::table('auditarus', function (Blueprint $table) {
            $table->dropColumn(['fotolokasi', 'koordinat']);
        });
    }
};
