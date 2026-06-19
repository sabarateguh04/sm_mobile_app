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
        Schema::dropIfExists('da_arusmudik');
        Schema::create('da_arusmudik', function (Blueprint $table) {
            $table->id('id_da_arusmudik');
            $table->unsignedBigInteger('id_arusmudik')->comment('FK ke arusmudik');
            $table->string('daerah')->comment('Daerah untuk filter');
            $table->string('wilayah')->nullable()->comment('Wilayah');
            $table->string('katops')->nullable()->comment('Kategori Ops dari profilposko');
            $table->string('jenisops')->nullable()->comment('Jenis Ops dari profilposko');
            $table->date('tanggal')->nullable()->comment('Tanggal dari arusmudik');
            $table->string('namaposko')->nullable()->comment('Nama Posko dari profilposko');
            $table->unsignedBigInteger('arusmudik_id_profileposko')->nullable()->comment('ID Profile Posko dari tabel arusmudik');
            $table->timestamps();
            
            // Foreign keys
            $table->foreign('id_arusmudik')->references('id_arusmudik')->on('arusmudik')->onDelete('cascade');
            
            // Index untuk performa
            $table->index('daerah');
            $table->index('wilayah');
            $table->index('id_arusmudik');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('da_arusmudik');
    }
};
