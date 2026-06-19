<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
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
        Schema::dropIfExists('pnpangkutan');
    }
};
