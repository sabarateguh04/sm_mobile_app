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
        Schema::dropIfExists('profilposko');
        Schema::create('profilposko', function (Blueprint $table) {
            $table->id('id_profilposko');
            $table->unsignedBigInteger('piket')->nullable()->comment('ID user yang piket');
            $table->string('katops')->nullable()->comment('Kategori Ops');
            $table->string('jenisops')->nullable()->comment('Jenis Ops');
            $table->string('tahunops')->nullable()->comment('Tahun Ops');
            $table->string('temaops')->nullable()->comment('Tema Ops');
            $table->string('namaposko')->nullable()->comment('Nama Posko');
            $table->text('lokasiposko')->nullable()->comment('Lokasi Posko');
            $table->text('sarpras')->nullable()->comment('Sarana Prasarana');
            $table->string('fotoposko')->nullable()->comment('Foto Posko');
            $table->string('alamat')->nullable()->comment('Alamat');
            $table->string('wilayah')->nullable()->comment('Wilayah');
            $table->string('daerah')->nullable()->comment('Daerah');
            $table->text('keterangan')->nullable()->comment('Keterangan');
            $table->timestamps();
            
            // Foreign keys
            $table->foreign('piket')->references('id_usm')->on('usm')->nullOnDelete();
            
            // Index untuk performa
            $table->index('wilayah');
            $table->index('daerah');
            $table->index('piket');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('profilposko');
    }
};
