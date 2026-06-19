<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Mengganti skema lama (kode, singkatan, dll.) menjadi id + parent_id + nama
 * untuk install yang sudah menjalankan versi awal 2026_05_14_100000.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('master_kl_pusat')) {
            return;
        }

        if (! Schema::hasColumn('master_kl_pusat', 'kode')) {
            return;
        }

        Schema::dropIfExists('master_kl_resort');
        Schema::dropIfExists('master_kl_daerah');
        Schema::dropIfExists('master_kl_pusat');

        Schema::create('master_kl_pusat', function (Blueprint $table) {
            $table->id();
            $table->foreignId('parent_id')->nullable()->constrained('master_kl_pusat')->cascadeOnDelete();
            $table->string('nama', 255);
            $table->timestamps();
            $table->index(['parent_id', 'nama']);
        });

        Schema::create('master_kl_daerah', function (Blueprint $table) {
            $table->id();
            $table->foreignId('parent_id')->constrained('master_kl_pusat')->cascadeOnDelete();
            $table->string('nama', 255);
            $table->timestamps();
            $table->index(['parent_id', 'nama']);
        });

        Schema::create('master_kl_resort', function (Blueprint $table) {
            $table->id();
            $table->foreignId('parent_id')->constrained('master_kl_daerah')->cascadeOnDelete();
            $table->string('nama', 255);
            $table->timestamps();
            $table->index(['parent_id', 'nama']);
        });
    }

    public function down(): void
    {
        // Skema lama tidak dipulihkan (data hilang bila up() dijalankan).
    }
};
