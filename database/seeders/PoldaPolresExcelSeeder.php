<?php

namespace Database\Seeders;

use App\Support\MasterKlHierarchyWriter;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use PhpOffice\PhpSpreadsheet\IOFactory;

/**
 * Impor daftar Polda / Polres dari berkas {@see base_path('polda polres.xlsx')}.
 * Kolom A: nama wilayah polda (mis. ACEH, METRO JAYA); kolom B: nama Polres (mis. SUBULUSSALAM).
 *
 * Jalankan: php artisan db:seed --class=PoldaPolresExcelSeeder
 */
class PoldaPolresExcelSeeder extends Seeder
{
    public function run(): void
    {
        if (! Schema::hasTable('master_kl_pusat') || ! Schema::hasTable('master_kl_daerah') || ! Schema::hasTable('master_kl_resort')) {
            return;
        }

        $path = base_path('polda polres.xlsx');
        if (! is_file($path)) {
            if ($this->command) {
                $this->command->warn('Lewati PoldaPolresExcelSeeder: tidak ada berkas '.$path);
            }

            return;
        }

        $now = now();
        $pusatId = MasterKlHierarchyWriter::ensurePusat('Mabes Polri', $now);

        $sheet = IOFactory::load($path)->getActiveSheet();
        $highRow = (int) $sheet->getHighestRow();
        $insertedDaerah = 0;
        $insertedResort = 0;

        for ($r = 2; $r <= $highRow; $r++) {
            $rawPolda = $sheet->getCell('A'.$r)->getValue();
            $rawPolres = $sheet->getCell('B'.$r)->getValue();
            if ($rawPolda === null || trim((string) $rawPolda) === '') {
                continue;
            }
            if ($rawPolres === null || trim((string) $rawPolres) === '') {
                continue;
            }

            $poldaNama = 'Polda '.$this->toTitle((string) $rawPolda);
            $polresNama = 'Polres '.$this->toTitle((string) $rawPolres);

            $beforeDaerah = DB::table('master_kl_daerah')->where('parent_id', $pusatId)->where('nama', $poldaNama)->exists();
            $daerahId = MasterKlHierarchyWriter::ensureDaerah($pusatId, $poldaNama, $now);
            if (! $beforeDaerah) {
                $insertedDaerah++;
            }

            $beforeResort = DB::table('master_kl_resort')->where('parent_id', $daerahId)->where('nama', $polresNama)->exists();
            MasterKlHierarchyWriter::ensureResort($daerahId, $polresNama, $now);
            if (! $beforeResort) {
                $insertedResort++;
            }
        }

        if ($this->command) {
            $this->command->info("PoldaPolresExcelSeeder: +{$insertedDaerah} daerah (KL), +{$insertedResort} resort.");
        }
    }

    protected function toTitle(string $value): string
    {
        $value = trim($value);
        if ($value === '') {
            return $value;
        }

        return mb_convert_case(mb_strtolower($value, 'UTF-8'), MB_CASE_TITLE, 'UTF-8');
    }
}
