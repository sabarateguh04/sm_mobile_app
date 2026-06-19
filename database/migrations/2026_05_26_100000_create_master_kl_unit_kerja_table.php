<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Tingkat ke-4 hierarki master KL: unit kerja (Subdit, satlantas, UPT, dll.)
 * Induk: master_kl_resort (organisasi daerah).
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('master_kl_resort')) {
            return;
        }

        Schema::create('master_kl_unit_kerja', function (Blueprint $table) {
            $table->id();
            $table->foreignId('parent_id')->constrained('master_kl_resort')->cascadeOnDelete();
            $table->string('nama', 255);
            $table->timestamps();
            $table->index(['parent_id', 'nama']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('master_kl_unit_kerja');
    }
};
