<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Dump sbo umumnya tidak punya tabel `pnpangkutan` (modul dari migrasi apps);
     * tanpa tabel ini rute /pnpangkutan melempar SQLSTATE[42S02].
     */
    public function up(): void
    {
        if (Schema::hasTable('pnpangkutan')) {
            return;
        }

        Schema::create('pnpangkutan', function (Blueprint $table) {
            $table->id('id_pnpangkutan');
            $table->unsignedBigInteger('id_profileposko')->nullable();
            $table->date('tanggal');
            $table->unsignedBigInteger('piket')->nullable()->comment('ID user yang piket');
            $table->string('jenis_angkutan')->nullable();
            $table->string('jumlah')->nullable()->comment('Jumlah/volume');
            $table->text('keterangan')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        // Jangan drop: bisa berisi data produksi.
    }
};
