<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Repair: profilposko can be missing when migrations are already recorded
     * (e.g. DB restore, manual schema drift).
     */
    public function up(): void
    {
        if (Schema::hasTable('profilposko')) {
            return;
        }

        $idUsmRow = DB::selectOne("SHOW COLUMNS FROM `usm` WHERE Field = 'id_usm'");
        $useBigint = $idUsmRow && str_contains(strtolower((string) $idUsmRow->Type), 'bigint');

        Schema::create('profilposko', function (Blueprint $table) use ($useBigint) {
            $table->id('id_profilposko');
            if ($useBigint) {
                $table->unsignedBigInteger('piket')->nullable()->comment('ID user yang piket');
            } else {
                $table->integer('piket')->nullable()->comment('ID user yang piket');
            }
            $table->string('katops')->nullable()->comment('Kategori Ops');
            $table->string('jenisops')->nullable()->comment('Jenis Ops');
            $table->string('tahunops')->nullable()->comment('Tahun Ops');
            $table->string('temaops')->nullable()->comment('Tema Ops');
            $table->string('namaposko')->nullable()->comment('Nama Posko');
            $table->text('lokasiposko')->nullable()->comment('Lokasi Posko');
            $table->text('sarpras')->nullable()->comment('Sarana Prasarana');
            $table->string('fotoposko')->nullable()->comment('Foto Posko');
            $table->string('alamat')->nullable()->comment('Alamat');
            $table->string('wilayah')->nullable()->comment('Wilayah');
            $table->string('daerah')->nullable()->comment('Daerah');
            $table->text('keterangan')->nullable()->comment('Keterangan');
            $table->timestamps();

            $table->foreign('piket')->references('id_usm')->on('usm')->nullOnDelete();

            $table->index('wilayah');
            $table->index('daerah');
            $table->index('piket');
        });
    }

    public function down(): void
    {
    }
};
