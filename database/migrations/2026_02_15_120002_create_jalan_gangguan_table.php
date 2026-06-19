<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('jalan_gangguan', function (Blueprint $table) {
            $table->id();
            $table->string('nama_ruas');
            $table->string('lokasi')->nullable();
            $table->decimal('latitude', 10, 8)->nullable();
            $table->decimal('longitude', 11, 8)->nullable();
            $table->string('status')->nullable()->comment('macet, longsor, perbaikan, dll');
            $table->text('keterangan')->nullable();
            $table->string('wilayah')->nullable();
            $table->string('daerah')->nullable();
            $table->timestamps();
            $table->index(['wilayah', 'daerah']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('jalan_gangguan');
    }
};
