<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Satu tabel hierarki wilayah: nasional → provinsi → kabupaten/kota (parent_id + level + kode).
 * Diisi dari `master_provinsi` / `master_kabupaten_kota` lewat seeder (sinkron).
 * Dipakai sebagai sumber select provinsi/kab-kota untuk role teritorial (administrator, oprator).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('master_territori', function (Blueprint $table) {
            $table->id();
            $table->foreignId('parent_id')->nullable()->constrained('master_territori')->cascadeOnDelete();
            $table->string('level', 32);
            $table->string('kode', 16)->unique();
            $table->string('nama', 191);
            $table->timestamps();

            $table->index(['parent_id', 'level']);
            $table->index('level');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('master_territori');
    }
};
