<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Tabel master dropdown operator + seed nilai default (selaras hardcoded lama).
 */
return new class extends Migration
{
    /** @var array<string, array{0: string, 1: string, 2: list<string>}> */
    private array $tables = [
        't_saluran' => ['id_saluran', 'namesaluran', ['Call Center 110', 'WhatsApp', 'Media Sosial', 'Kunjungan Langsung']],
        't_temalayanan' => ['id_temalayanan', 'nametema', ['Informasi Lalu Lintas', 'Pengaduan', 'Koordinasi Kegiatan', 'Lainnya']],
        't_durasi' => ['id_durasi', 'namedurasi', ['2-3 Jam', '4-5 Jam', '6-7 Jam', '8-9 Jam']],
        't_situasilalin' => ['id_situasilalin', 'namesituasilalin', ['Lancar', 'Padat', 'Macet', 'Terhenti', 'Mudik', 'Arus Balik']],
        't_situasicuaca' => ['id_situasicuaca', 'namesituasicuaca', ['Cerah', 'Berawan', 'Hujan Ringan', 'Hujan Lebat', 'Badai', 'Kabut']],
        't_gerbangkota' => ['id_gerbangkota', 'namegerbang', ['UTARA', 'SELATAN', 'TIMUR', 'BARAT']],
        't_araharus' => ['id_araharus', 'namearah', ['Masuk', 'Keluar']],
        't_kategorikonten' => ['id_kategorikonten', 'namekategori', ['Publikasi', 'Informasi', 'Edukasi', 'Sosialisasi', 'Lainnya']],
        't_metodemedia' => ['id_metodemedia', 'namemetode', ['Info Graphic', 'Video', 'Artikel', 'Press Release', 'Podcast', 'Lainnya']],
        't_mediapublishing' => ['id_mediapublishing', 'namemedia', ['Media Offline', 'Media Online', 'Media Sosial', 'Website', 'Lainnya']],
        't_sentimen' => ['id_sentimen', 'namesentimen', ['Positif', 'Negatif', 'Netral']],
    ];

    public function up(): void
    {
        foreach ($this->tables as $table => [$idCol, $nameCol, $seed]) {
            if (! Schema::hasTable($table)) {
                Schema::create($table, function (Blueprint $t) use ($idCol, $nameCol) {
                    $t->unsignedInteger($idCol)->primary();
                    $t->string($nameCol, 255)->nullable();
                    $t->timestamps();
                });
            }

            if (DB::table($table)->count() === 0) {
                $id = 1;
                foreach ($seed as $name) {
                    DB::table($table)->insert([
                        $idCol => $id++,
                        $nameCol => $name,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }
        }

        if (Schema::hasTable('t_ownercctv') && DB::table('t_ownercctv')->count() === 0) {
            foreach (['Korlantas', 'Polda', 'Polres', 'Instansi Lain'] as $i => $name) {
                DB::table('t_ownercctv')->insert([
                    'id_ownercctv' => $i + 1,
                    'nameowner' => $name,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }

    public function down(): void
    {
        foreach (array_keys($this->tables) as $table) {
            Schema::dropIfExists($table);
        }
    }
};
