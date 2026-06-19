<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Pastikan tabel t_datajalan ada dan id_datajalan = PRIMARY KEY + AUTO_INCREMENT.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('t_datajalan')) {
            Schema::create('t_datajalan', function (Blueprint $table) {
                $table->increments('id_datajalan');
                $table->string('jenisjalan', 100);
                $table->string('namajalan', 100);
                $table->integer('panjangjalan');
                $table->string('piket', 100)->nullable();
                $table->string('lintasan', 100);
                $table->string('fotoobject', 255)->nullable();
                $table->string('prasjal', 100);
                $table->date('tanggal');
            });

            return;
        }

        $col = collect(DB::select('SHOW COLUMNS FROM `t_datajalan` WHERE Field = ?', ['id_datajalan']))->first();
        if (! $col) {
            DB::statement('ALTER TABLE `t_datajalan` ADD COLUMN `id_datajalan` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY FIRST');

            return;
        }

        if (str_contains(strtolower((string) ($col->Extra ?? '')), 'auto_increment')) {
            return;
        }

        $colType = strtoupper((string) ($col->Type ?? 'INT'));
        $pkExists = collect(DB::select('SHOW KEYS FROM `t_datajalan` WHERE Key_name = ?', ['PRIMARY']))->isNotEmpty();

        if ($pkExists) {
            DB::statement("ALTER TABLE `t_datajalan` MODIFY COLUMN `id_datajalan` {$colType} NOT NULL AUTO_INCREMENT");
        } else {
            DB::statement("ALTER TABLE `t_datajalan` MODIFY COLUMN `id_datajalan` {$colType} NOT NULL AUTO_INCREMENT, ADD PRIMARY KEY (`id_datajalan`)");
        }
    }

    public function down(): void
    {
        // Tabel legacy SBO — tidak di-drop otomatis.
    }
};
