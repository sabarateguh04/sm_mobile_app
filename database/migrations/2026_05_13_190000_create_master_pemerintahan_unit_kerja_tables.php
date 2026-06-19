<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('master_pemerintahan_wilayah', function (Blueprint $table) {
            $table->id();
            $table->string('tingkat', 64);
            $table->string('kode_wilayah', 32)->unique();
            $table->string('nama');
            $table->string('wilayah_induk')->nullable();
            $table->timestamps();
        });

        Schema::create('master_pemerintahan_instansi', function (Blueprint $table) {
            $table->id();
            $table->string('jenis', 128);
            $table->string('singkatan', 64);
            $table->string('nama_unit');
            $table->string('lokasi', 128)->nullable();
            $table->timestamps();
            $table->unique(['singkatan', 'nama_unit']);
        });

        Schema::create('master_unit_kerja', function (Blueprint $table) {
            $table->id();
            $table->string('kode_unit_kerja', 64)->unique();
            $table->string('nama_unit_kerja');
            $table->string('instansi_placeholder');
            $table->string('penanggung_jawab', 128)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('master_unit_kerja');
        Schema::dropIfExists('master_pemerintahan_instansi');
        Schema::dropIfExists('master_pemerintahan_wilayah');
    }
};
