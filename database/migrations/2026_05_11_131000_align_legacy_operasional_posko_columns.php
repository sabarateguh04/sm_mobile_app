<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tabel operasional lawas (sbo) sering belum punya id_profileposko sementara controller join ke profilposko.
     */
    public function up(): void
    {
        foreach (['contigency', 'wanjol', 'auditarus', 'pnpangkutan'] as $table) {
            if (! Schema::hasTable($table)) {
                continue;
            }
            Schema::table($table, function (Blueprint $blueprint) use ($table) {
                if (! Schema::hasColumn($table, 'id_profileposko')) {
                    $blueprint->unsignedBigInteger('id_profileposko')->nullable();
                }
                if (! Schema::hasColumn($table, 'created_at')) {
                    $blueprint->timestamp('created_at')->nullable();
                }
                if (! Schema::hasColumn($table, 'updated_at')) {
                    $blueprint->timestamp('updated_at')->nullable();
                }
            });
        }
    }

    public function down(): void
    {
        //
    }
};
