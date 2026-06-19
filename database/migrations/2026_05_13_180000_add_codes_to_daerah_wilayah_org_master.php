<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('daerah') && ! Schema::hasColumn('daerah', 'kode_provinsi')) {
            Schema::table('daerah', function (Blueprint $table) {
                $table->string('kode_provinsi', 8)->nullable()->unique()->after('namadaerah');
            });
        }

        if (Schema::hasTable('wilayah') && ! Schema::hasColumn('wilayah', 'kode_kabupaten_kota')) {
            Schema::table('wilayah', function (Blueprint $table) {
                $table->string('kode_kabupaten_kota', 16)->nullable()->unique()->after('namawilayah');
                $table->string('jenis_wilayah', 16)->nullable()->after('kode_kabupaten_kota');
            });
        }

        if (Schema::hasTable('org') && ! Schema::hasColumn('org', 'singkatan')) {
            Schema::table('org', function (Blueprint $table) {
                $table->string('singkatan', 64)->nullable()->after('nameorg');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('wilayah')) {
            Schema::table('wilayah', function (Blueprint $table) {
                if (Schema::hasColumn('wilayah', 'jenis_wilayah')) {
                    $table->dropColumn('jenis_wilayah');
                }
                if (Schema::hasColumn('wilayah', 'kode_kabupaten_kota')) {
                    $table->dropColumn('kode_kabupaten_kota');
                }
            });
        }

        if (Schema::hasTable('daerah')) {
            Schema::table('daerah', function (Blueprint $table) {
                if (Schema::hasColumn('daerah', 'kode_provinsi')) {
                    $table->dropColumn('kode_provinsi');
                }
            });
        }

        if (Schema::hasTable('org')) {
            Schema::table('org', function (Blueprint $table) {
                if (Schema::hasColumn('org', 'singkatan')) {
                    $table->dropColumn('singkatan');
                }
            });
        }
    }
};
