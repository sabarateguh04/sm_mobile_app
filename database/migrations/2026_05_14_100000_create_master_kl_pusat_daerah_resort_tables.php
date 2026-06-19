<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Master Kantor Imigrasi: KL Pusat (bertingkat parent_id) → KL Daerah → KL Resort.
 * Kolom: id, parent_id (induk), nama.
 */
return new class extends Migration
{
    public function up(): void
    {
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
        Schema::dropIfExists('master_kl_resort');
        Schema::dropIfExists('master_kl_daerah');
        Schema::dropIfExists('master_kl_pusat');
    }
};
