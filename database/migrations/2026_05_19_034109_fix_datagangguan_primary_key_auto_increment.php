<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Pastikan kolom id_datagangguan bersifat AUTO_INCREMENT + PRIMARY KEY.
 *
 * Tabel datagangguan berasal dari database SBO lama yang kadang tidak memiliki
 * AUTO_INCREMENT pada primary key-nya, sehingga INSERT gagal dengan error:
 *   "Field 'id_datagangguan' doesn't have a default value"
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('datagangguan')) {
            return;
        }

        // Cek apakah AUTO_INCREMENT sudah ada; hindari eksekusi ALTER jika tidak perlu.
        $col = collect(DB::select('SHOW COLUMNS FROM `datagangguan` WHERE Field = ?', ['id_datagangguan']))->first();

        if (! $col) {
            // Kolom belum ada sama sekali — tambahkan sebagai primary key auto increment
            DB::statement('ALTER TABLE `datagangguan` ADD COLUMN `id_datagangguan` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY FIRST');
            return;
        }

        // Cek apakah sudah AUTO_INCREMENT
        $isAutoInc = str_contains(strtolower((string) ($col->Extra ?? '')), 'auto_increment');
        if ($isAutoInc) {
            return; // sudah benar, tidak perlu apa-apa
        }

        // Tentukan tipe kolom yang ada agar ALTER menggunakan tipe yang sama
        $colType = strtoupper((string) ($col->Type ?? 'INT'));

        // Tambahkan / perbaiki PRIMARY KEY + AUTO_INCREMENT
        // Cek dulu apakah sudah ada PRIMARY KEY
        $pkExists = collect(DB::select('SHOW KEYS FROM `datagangguan` WHERE Key_name = ?', ['PRIMARY']))->isNotEmpty();

        if ($pkExists) {
            // Kolom sudah jadi PK, cukup ubah ke AUTO_INCREMENT
            DB::statement("ALTER TABLE `datagangguan` MODIFY COLUMN `id_datagangguan` {$colType} NOT NULL AUTO_INCREMENT");
        } else {
            // Tidak ada PK sama sekali — set kolom sekaligus jadi PK AUTO_INCREMENT
            DB::statement("ALTER TABLE `datagangguan` MODIFY COLUMN `id_datagangguan` {$colType} NOT NULL AUTO_INCREMENT, ADD PRIMARY KEY (`id_datagangguan`)");
        }
    }

    public function down(): void
    {
        // Tidak di-rollback: menghapus AUTO_INCREMENT dari production bisa merusak data
    }
};
