<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Tambah kolom parent agar dropdown Kategori & Tema bisa di-cascade per Saluran.
 *
 * t_katlayanan.saluran  → nilai saluran induk (nullable = tampil di semua saluran)
 * t_temalayanan.kategori → nilai kategori induk (nullable = tampil di semua kategori)
 */
return new class extends Migration
{
    public function up(): void
    {
        // Tambah kolom `saluran` ke t_katlayanan (jika tabel & kolom belum ada)
        if (Schema::hasTable('t_katlayanan') && !Schema::hasColumn('t_katlayanan', 'saluran')) {
            Schema::table('t_katlayanan', function (Blueprint $table) {
                $table->string('saluran', 100)->nullable()->after('namekatlayanan')
                    ->comment('Saluran induk — null = tampil di semua saluran');
            });
        }

        // Tambah kolom `kategori` ke t_temalayanan (jika tabel & kolom belum ada)
        if (Schema::hasTable('t_temalayanan') && !Schema::hasColumn('t_temalayanan', 'kategori')) {
            Schema::table('t_temalayanan', function (Blueprint $table) {
                $table->string('kategori', 100)->nullable()->after('nametema')
                    ->comment('Kategori induk — null = tampil di semua kategori');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('t_katlayanan') && Schema::hasColumn('t_katlayanan', 'saluran')) {
            Schema::table('t_katlayanan', function (Blueprint $table) {
                $table->dropColumn('saluran');
            });
        }

        if (Schema::hasTable('t_temalayanan') && Schema::hasColumn('t_temalayanan', 'kategori')) {
            Schema::table('t_temalayanan', function (Blueprint $table) {
                $table->dropColumn('kategori');
            });
        }
    }
};
