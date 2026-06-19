<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('mediapublishing')) {
            return;
        }

        Schema::create('mediapublishing', function (Blueprint $table) {
            $table->increments('id_mediapublishing');
            $table->unsignedInteger('piket')->nullable();
            $table->date('tanggal');
            $table->string('daerah', 100)->nullable()->comment('Provinsi');
            $table->string('wilayah', 100)->nullable()->comment('Kab/Kota');
            $table->string('kategori_konten', 100)->nullable();
            $table->string('tema', 255)->nullable();
            $table->string('metode', 100)->nullable();
            $table->string('media_publishing', 100)->nullable();
            $table->string('links', 500)->nullable();
            $table->string('fotodoc', 500)->nullable();
        });

        if (DB::table('mediapublishing')->count() === 0) {
            DB::table('mediapublishing')->insert([
                'tanggal' => '2026-05-20',
                'daerah' => 'JAWA TIMUR',
                'wilayah' => 'SURABAYA',
                'kategori_konten' => 'Publikasi',
                'tema' => '23232',
                'metode' => 'Info Graphic',
                'media_publishing' => 'Media Offline',
                'links' => '22',
                'fotodoc' => null,
            ]);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('mediapublishing');
    }
};
