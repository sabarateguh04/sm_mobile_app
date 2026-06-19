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
        Schema::create('wanjol', function (Blueprint $table) {
            $table->id('id_wanjol');
            $table->unsignedBigInteger('id_profileposko')->nullable();
            $table->date('tanggal');
            $table->string('lokasi')->nullable()->comment('Lokasi peristiwa');
            $table->text('uraian')->nullable()->comment('Uraian peristiwa menonjol');
            $table->unsignedBigInteger('piket')->nullable()->comment('ID user yang piket');
            $table->string('status')->nullable()->comment('Status peristiwa');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('wanjol');
    }
};
