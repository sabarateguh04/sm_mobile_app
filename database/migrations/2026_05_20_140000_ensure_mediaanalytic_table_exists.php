<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('mediaanalytic')) {
            Schema::create('mediaanalytic', function (Blueprint $table) {
                $table->increments('id_mediaanalytic');
                $table->date('tanggal');
                $table->string('issue_topic', 255)->nullable();
                $table->unsignedInteger('vol_percakapan')->default(0);
                $table->string('sentimen', 50)->nullable();
                $table->string('potensi', 150)->nullable();
            });
        }

        if (Schema::hasTable('mediaanalytic') && DB::table('mediaanalytic')->count() === 0) {
            DB::table('mediaanalytic')->insert([
                'tanggal' => '2026-05-17',
                'issue_topic' => null,
                'vol_percakapan' => 787,
                'sentimen' => 'Positif',
                'potensi' => 'Pergerakan Massa',
            ]);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('mediaanalytic');
    }
};
