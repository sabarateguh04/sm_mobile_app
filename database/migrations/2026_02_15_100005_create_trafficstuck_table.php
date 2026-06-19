<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('trafficstuck', function (Blueprint $table) {
            $table->id('id_trafficstuck');
            $table->unsignedBigInteger('id_profileposko')->nullable();
            $table->date('tanggal');
            $table->unsignedBigInteger('piket')->nullable()->comment('ID user yang piket');
            $table->string('lokasi')->nullable();
            $table->string('penyebab')->nullable()->comment('Penyebab perlambatan');
            $table->text('uraian')->nullable();
            $table->string('status')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('trafficstuck');
    }
};
