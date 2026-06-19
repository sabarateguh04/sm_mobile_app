<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('t_katfaskes')) {
            Schema::create('t_katfaskes', function (Blueprint $table) {
                $table->increments('id_katfaskes');
                $table->string('namekatfaskes', 100);
            });

            $defaults = ['RSUD', 'RS Swasta', 'Puskesmas', 'Klinik', 'Klinik Utama', 'BHC / Pos Kesehatan'];
            foreach ($defaults as $name) {
                DB::table('t_katfaskes')->insert(['namekatfaskes' => $name]);
            }
        }

        if (Schema::hasTable('data_faskes')) {
            return;
        }

        Schema::create('data_faskes', function (Blueprint $table) {
            $table->increments('id_faskes');
            $table->date('tanggal');
            $table->string('katfaskes', 100)->nullable();
            $table->string('namafaskes', 200);
            $table->string('lokasi', 255)->nullable();
            $table->string('koordinat', 100)->nullable();
            $table->string('fasilitas', 500)->nullable();
            $table->string('namecp', 200)->nullable();
            $table->string('cp', 100)->nullable();
            $table->string('telpambulance', 50)->nullable();
            $table->string('telpigd', 50)->nullable();
            $table->string('fotoobject', 255)->nullable();
            $table->string('piket', 50)->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('data_faskes');
        Schema::dropIfExists('t_katfaskes');
    }
};
