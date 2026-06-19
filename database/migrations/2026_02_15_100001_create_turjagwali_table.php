<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('turjagwali', function (Blueprint $table) {
            $table->id('id_turjagwali');
            $table->unsignedBigInteger('id_profileposko')->nullable();
            $table->date('tanggal');
            $table->unsignedBigInteger('piket')->nullable()->comment('ID user yang piket');
            $table->string('status')->nullable()->comment('Status kegiatan');
            $table->text('keterangan')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('turjagwali');
    }
};
