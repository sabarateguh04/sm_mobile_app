<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('layanan_data', function (Blueprint $table) {
            $table->increments('id_layanan_data');
            $table->date('tanggal');
            $table->string('kategori_data', 100)->nullable(); // e.g. Data Gangguan, Data Terminal
            $table->string('tujuan', 200)->nullable();
            $table->string('pemohon', 200)->nullable();
            $table->string('telp_pemohon', 50)->nullable();
            $table->text('keterangan')->nullable();
            // territory scoping
            $table->string('org', 100)->nullable();
            $table->string('suborg', 100)->nullable();
            $table->string('daerah', 100)->nullable();
            $table->string('wilayah', 100)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('layanan_data');
    }
};
