<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Master data Interaksi Public Service — Kategori → Tema (cascade dropdown operator).
 *
 * Kategori: ADUAN, Pemberian Informasi, Permintaan Informasi
 * Tema: child dari kategori (kolom t_temalayanan.kategori)
 */
return new class extends Migration
{
    /** @var list<string> */
    private array $kategoriNames = [
        'ADUAN',
        'Pemberian Informasi',
        'Permintaan Informasi',
    ];

    /**
     * @var array<string, list<string>>
     */
    private array $temaByKategori = [
        'ADUAN' => [
            'Jalan Rusak',
            'Pohon Tumbang',
            'Traffic Light Mati',
            'Lampu Jalan Mati',
            'Drainase Tersumbat',
            'Marka Jalan',
            'Lainnya',
        ],
        'Pemberian Informasi' => [
            'Kecelakaan',
            'Pelanggaran',
            'Kerusuhan',
            'Tindak Pidana',
            'Perlambatan',
        ],
        'Permintaan Informasi' => [
            'Administratif',
            'Hukum',
            'Peraturan',
            'Pengumuman',
            'Prosedur',
            'Lainnya',
        ],
    ];

    public function up(): void
    {
        if (Schema::hasTable('t_katlayanan')) {
            $this->syncKategori();
        }

        if (Schema::hasTable('t_temalayanan')) {
            $this->syncTema();
        }
    }

    public function down(): void
    {
        // Data master — tidak di-rollback agar entry Interaksi yang sudah ada tetap konsisten.
    }

    private function syncKategori(): void
    {
        $existing = DB::table('t_katlayanan')
            ->pluck('id_katlayanan', 'namekatlayanan')
            ->all();

        $nextId = ((int) DB::table('t_katlayanan')->max('id_katlayanan')) + 1;

        foreach ($this->kategoriNames as $name) {
            if (isset($existing[$name])) {
                continue;
            }

            $row = [
                'id_katlayanan' => $nextId++,
                'namekatlayanan' => $name,
            ];

            if (Schema::hasColumn('t_katlayanan', 'saluran')) {
                $row['saluran'] = null;
            }

            DB::table('t_katlayanan')->insert($row);
        }
    }

    private function syncTema(): void
    {
        $hasKategoriCol = Schema::hasColumn('t_temalayanan', 'kategori');

        $existingPairs = DB::table('t_temalayanan')
            ->get(['id_temalayanan', 'nametema', 'kategori'])
            ->map(fn ($row) => ($hasKategoriCol ? ($row->kategori ?? '') : '').'|'.($row->nametema ?? ''))
            ->flip()
            ->all();

        $nextId = ((int) DB::table('t_temalayanan')->max('id_temalayanan')) + 1;

        foreach ($this->temaByKategori as $kategori => $temas) {
            foreach ($temas as $tema) {
                $key = $kategori.'|'.$tema;
                if (isset($existingPairs[$key])) {
                    continue;
                }

                $row = [
                    'id_temalayanan' => $nextId++,
                    'nametema' => $tema,
                ];

                if ($hasKategoriCol) {
                    $row['kategori'] = $kategori;
                }

                if (Schema::hasColumn('t_temalayanan', 'created_at')) {
                    $row['created_at'] = now();
                    $row['updated_at'] = now();
                }

                DB::table('t_temalayanan')->insert($row);
                $existingPairs[$key] = true;
            }
        }
    }
};
