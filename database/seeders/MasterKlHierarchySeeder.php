<?php

namespace Database\Seeders;

use App\Support\MasterKlHierarchyWriter;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Schema;

/**
 * Hierarki master KL 4 tingkat:
 * master_kl_pusat (induk) → master_kl_daerah (organisasi pusat) → master_kl_resort (organisasi daerah)
 * → master_kl_unit_kerja (unit kerja).
 *
 * Jalankan: php artisan db:seed --class=MasterKlHierarchySeeder
 */
class MasterKlHierarchySeeder extends Seeder
{
    protected array $hierarkiContoh = [
        'POLRI' => [
            'KORLANTAS' => [
                'Kakorlantas' => [],
                'Wakakorlantas' => [],
                'Bagren' => [],
                'Bagbinopsnal' => [],
                'Bag SDM' => [],
                'Bag Keuangan' => [],
                'Ditregident' => [],
                'Ditgakkum' => [],
                'Ditkamsel' => [],
                'Ditwal dan PJR' => [],
                'NTMC Polri' => [],
                'Ditlantas Polda' => [
                    'Dirlantas',
                    'Bagbinops',
                    'Subdit Regident',
                    'Subdit Gakkum',
                    'Subdit Kamsel',
                    'Subdit Gakkum/PJR'
                ],
                'Satlantas Polres/Polresta/Polrestabes' => [
                    'Kasat Lantas',
                    'Unit Regident',
                    'Unit Gakkum',
                    'Unit Turjawali',
                    'Unit Kamsel',
                    'SPKT/SIM/Samsat (sesuai organisasi)'
                ]
            ]
        ],

        'Kemendagri' => [
            'Pemprov Jawa Barat' => [
                'Pemkab Cimahi' => [
                    'UPT Dishub Cimahi',
                ],
            ],
            'Pemprop Jabar' => [
                'Pemkab Cimahi' => [
                    'UPT Dishub Cimahi',
                ],
            ],
            'Pemprop DKI' => [
                'Trans Jakarta' => [
                    'Moda Trans Jakarta',
                ],
            ],
            'Pemprop' => [
                'Pimpinan' => [],
            ],
        ],
        'Kemenhub' => [],
        'Kemenpu' => [
            'BPJT' => [
                'Bidang Investasi Jalan Tol' => [
                    'Seksi Operasional Tol',
                ],
            ],
        ],
    ];

    public function run(): void
    {
        if (! Schema::hasTable('master_kl_pusat')
            || ! Schema::hasTable('master_kl_daerah')
            || ! Schema::hasTable('master_kl_resort')) {
            return;
        }

        $now = now();
        $empatTingkat = Schema::hasTable('master_kl_unit_kerja');

        foreach ($this->hierarkiContoh as $namaInduk => $orgPusatMap) {
            $pusatId = MasterKlHierarchyWriter::ensurePusat($namaInduk, $now);

            foreach ($orgPusatMap as $namaOrgPusat => $orgDaerahMap) {
                $daerahId = MasterKlHierarchyWriter::ensureDaerah($pusatId, $namaOrgPusat, $now);

                foreach ($orgDaerahMap as $namaOrgDaerah => $unitKerjaList) {
                    $resortId = MasterKlHierarchyWriter::ensureResort($daerahId, $namaOrgDaerah, $now);

                    if ($empatTingkat) {
                        foreach ($unitKerjaList as $namaUnitKerja) {
                            MasterKlHierarchyWriter::ensureUnitKerja($resortId, $namaUnitKerja, $now);
                        }
                    }
                }
            }
        }
    }
}
