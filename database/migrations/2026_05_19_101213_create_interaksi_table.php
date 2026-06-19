<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('interaksi', function (Blueprint $table) {
            $table->increments('id_interaksi');
            $table->date('tanggal');
            $table->string('saluran', 100)->nullable();   // e.g. Call Center 110
            $table->string('kategori', 100)->nullable();  // e.g. Konsultasi
            $table->string('tema', 200)->nullable();
            $table->string('narsum', 200)->nullable();
            $table->string('telp_narsum', 50)->nullable();
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
        Schema::dropIfExists('interaksi');
    }
};
