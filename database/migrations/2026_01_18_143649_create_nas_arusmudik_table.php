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
        Schema::dropIfExists('nas_arusmudik');
        Schema::create('nas_arusmudik', function (Blueprint $table) {
            $table->id('id_nas_arusmudik');
            $table->unsignedBigInteger('id_arusmudik')->comment('FK ke arusmudik');
            $table->string('daerah')->nullable()->comment('Daerah');
            $table->string('wilayah')->nullable()->comment('Wilayah');
            $table->unsignedBigInteger('id_profileposko')->nullable()->comment('Profile Posko');
            $table->unsignedBigInteger('arusmudik_id_profileposko')->nullable()->comment('ID Profile Posko dari tabel arusmudik');
            $table->string('statarus')->nullable()->comment('Status Arus dari arusmudik');
            $table->date('tanggal')->nullable()->comment('Tanggal dari arusmudik');
            $table->unsignedBigInteger('piket')->nullable()->comment('ID Piket dari arusmudik');
            $table->string('usm_nama')->nullable()->comment('Nama Piket dari usm');
            $table->timestamps();
            
            // Foreign keys
            $table->foreign('id_arusmudik')->references('id_arusmudik')->on('arusmudik')->onDelete('cascade');
            $table->foreign('id_profileposko')->references('id_profilposko')->on('profilposko')->nullOnDelete();
            $table->foreign('piket')->references('id_usm')->on('usm')->nullOnDelete();
            
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
        Schema::dropIfExists('nas_arusmudik');
    }
};
