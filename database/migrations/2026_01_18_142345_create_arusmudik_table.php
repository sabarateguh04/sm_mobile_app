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
        Schema::dropIfExists('arusmudik');
        Schema::create('arusmudik', function (Blueprint $table) {
            $table->id('id_arusmudik');
            $table->unsignedBigInteger('id_profileposko')->nullable();
            $table->date('tanggal');
            $table->unsignedBigInteger('piket')->nullable()->comment('ID user yang piket');
            $table->string('statarus')->nullable()->comment('Status arus');
            $table->timestamps();
            
            // Foreign keys - akan ditambahkan setelah tabel profilposko dibuat
            // $table->foreign('id_profileposko')->references('id_profilposko')->on('profilposko')->nullOnDelete();
            // $table->foreign('piket')->references('id_usm')->on('usm')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('arusmudik');
    }
};
