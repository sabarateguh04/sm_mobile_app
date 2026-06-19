<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Selaraskan kolom mediapublishing dengan form CRUD:
 * kategori_konten, tema, links (+ tanggal, metode, media_publishing, fotodoc, piket, daerah, wilayah).
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('mediapublishing')) {
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

            $this->seedSample();

            return;
        }

        $this->renameColumnIfExists('kategori', 'kategori_konten', 'VARCHAR(100) NULL');
        $this->renameColumnIfExists('tema_konten', 'tema', 'VARCHAR(255) NULL');
        $this->renameColumnIfExists('links_media', 'links', 'VARCHAR(500) NULL');

        if (! Schema::hasColumn('mediapublishing', 'kategori_konten')) {
            Schema::table('mediapublishing', function (Blueprint $table) {
                $table->string('kategori_konten', 100)->nullable()->after('wilayah');
            });
        }
        if (! Schema::hasColumn('mediapublishing', 'tema')) {
            Schema::table('mediapublishing', function (Blueprint $table) {
                $table->string('tema', 255)->nullable()->after('kategori_konten');
            });
        }
        if (! Schema::hasColumn('mediapublishing', 'links')) {
            Schema::table('mediapublishing', function (Blueprint $table) {
                $table->string('links', 500)->nullable()->after('media_publishing');
            });
        }

        $this->ensureAutoIncrementPrimaryKey();
    }

    public function down(): void
    {
        if (! Schema::hasTable('mediapublishing')) {
            return;
        }

        $this->renameColumnIfExists('kategori_konten', 'kategori', 'VARCHAR(100) NULL');
        $this->renameColumnIfExists('tema', 'tema_konten', 'VARCHAR(255) NULL');
        $this->renameColumnIfExists('links', 'links_media', 'VARCHAR(500) NULL');
    }

    private function renameColumnIfExists(string $from, string $to, string $sqlType): void
    {
        if (! Schema::hasColumn('mediapublishing', $from) || Schema::hasColumn('mediapublishing', $to)) {
            return;
        }

        DB::statement("ALTER TABLE `mediapublishing` CHANGE `{$from}` `{$to}` {$sqlType}");
    }

    private function ensureAutoIncrementPrimaryKey(): void
    {
        $col = collect(DB::select('SHOW COLUMNS FROM `mediapublishing` WHERE Field = ?', ['id_mediapublishing']))->first();
        if (! $col || stripos((string) ($col->Extra ?? ''), 'auto_increment') !== false) {
            return;
        }

        $maxId = (int) (DB::table('mediapublishing')->max('id_mediapublishing') ?? 0);
        $next = $maxId + 1;
        DB::statement('ALTER TABLE `mediapublishing` MODIFY COLUMN `id_mediapublishing` INT UNSIGNED NOT NULL AUTO_INCREMENT');
        DB::statement("ALTER TABLE `mediapublishing` AUTO_INCREMENT = {$next}");
    }

    private function seedSample(): void
    {
        if (DB::table('mediapublishing')->count() > 0) {
            return;
        }

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
};
