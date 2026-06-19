<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tabel `trafficstuck` di dump sbo lawas beda skema dengan migrasi apps;
     * tambah kolom yang dipakai aplikasi dan longgarkan kolom legacy agar form baru bisa menyimpan.
     */
    public function up(): void
    {
        if (! Schema::hasTable('trafficstuck')) {
            return;
        }

        Schema::table('trafficstuck', function (Blueprint $table) {
            if (! Schema::hasColumn('trafficstuck', 'id_profileposko')) {
                $table->unsignedBigInteger('id_profileposko')->nullable();
            }
            if (! Schema::hasColumn('trafficstuck', 'uraian')) {
                $table->text('uraian')->nullable();
            }
            if (! Schema::hasColumn('trafficstuck', 'status')) {
                $table->string('status', 100)->nullable();
            }
            if (! Schema::hasColumn('trafficstuck', 'created_at')) {
                $table->timestamp('created_at')->nullable();
            }
            if (! Schema::hasColumn('trafficstuck', 'updated_at')) {
                $table->timestamp('updated_at')->nullable();
            }
        });

        try {
            DB::statement('ALTER TABLE `trafficstuck` MODIFY `faktor` VARCHAR(100) NULL');
        } catch (\Throwable) {
        }
        try {
            DB::statement('ALTER TABLE `trafficstuck` MODIFY `durasi` VARCHAR(100) NULL');
        } catch (\Throwable) {
        }
        try {
            DB::statement('ALTER TABLE `trafficstuck` MODIFY `timurai` VARCHAR(100) NULL');
        } catch (\Throwable) {
        }
        try {
            DB::statement('ALTER TABLE `trafficstuck` MODIFY `jamperistiwa` TIME NULL');
        } catch (\Throwable) {
        }
    }

    public function down(): void
    {
    }
};
