<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Buat tabel referensi `t_katlayanan` (Kategori Layanan Masyarakat).
 * Guard: skip jika tabel sudah ada (legacy SBO database).
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('t_katlayanan')) {
            return;
        }

        Schema::create('t_katlayanan', function (Blueprint $table) {
            $table->increments('id_katlayanan');
            $table->string('namekatlayanan', 100);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('t_katlayanan');
    }
};
