<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Master hierarkis: provinsi → kab/kota; pusat bertingkat (parent_id).
 * Kolom penghubung di `daerah` / `wilayah` menyelaraskan dengan legacy USM & TerritoryMaster.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('master_provinsi', function (Blueprint $table) {
            $table->id();
            $table->string('kode_provinsi', 8)->unique();
            $table->string('nama', 128);
            $table->timestamps();
        });

        Schema::create('master_kabupaten_kota', function (Blueprint $table) {
            $table->id();
            $table->foreignId('master_provinsi_id')->constrained('master_provinsi')->cascadeOnDelete();
            $table->string('kode_kabupaten_kota', 16)->unique();
            $table->string('nama', 128);
            $table->string('jenis', 16);
            $table->timestamps();
        });

        Schema::create('master_pusat', function (Blueprint $table) {
            $table->id();
            $table->foreignId('parent_id')->nullable()->constrained('master_pusat')->cascadeOnDelete();
            $table->string('singkatan', 64)->nullable();
            $table->string('nama', 255);
            $table->unsignedSmallInteger('urutan')->nullable();
            $table->timestamps();
            $table->index(['parent_id', 'nama']);
        });

        if (Schema::hasTable('daerah') && ! Schema::hasColumn('daerah', 'master_provinsi_id')) {
            Schema::table('daerah', function (Blueprint $table) {
                $col = Schema::hasColumn('daerah', 'kode_provinsi') ? 'kode_provinsi' : 'namadaerah';
                $table->foreignId('master_provinsi_id')->nullable()->after($col)
                    ->constrained('master_provinsi')->nullOnDelete();
            });
        }

        if (Schema::hasTable('wilayah') && ! Schema::hasColumn('wilayah', 'master_kabupaten_kota_id')) {
            Schema::table('wilayah', function (Blueprint $table) {
                $col = Schema::hasColumn('wilayah', 'kode_kabupaten_kota') ? 'kode_kabupaten_kota' : 'namawilayah';
                $table->foreignId('master_kabupaten_kota_id')->nullable()->after($col)
                    ->constrained('master_kabupaten_kota')->nullOnDelete();
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('wilayah') && Schema::hasColumn('wilayah', 'master_kabupaten_kota_id')) {
            Schema::table('wilayah', function (Blueprint $table) {
                $table->dropForeign(['master_kabupaten_kota_id']);
                $table->dropColumn('master_kabupaten_kota_id');
            });
        }

        if (Schema::hasTable('daerah') && Schema::hasColumn('daerah', 'master_provinsi_id')) {
            Schema::table('daerah', function (Blueprint $table) {
                $table->dropForeign(['master_provinsi_id']);
                $table->dropColumn('master_provinsi_id');
            });
        }

        Schema::dropIfExists('master_pusat');
        Schema::dropIfExists('master_kabupaten_kota');
        Schema::dropIfExists('master_provinsi');
    }
};
