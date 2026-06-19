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
    /**
     * @var array<string, array<string, array<string, list<string>>>>
     *     induk => [organisasi pusat => [organisasi daerah => [unit kerja, ...]]]
     */
    protected array $hierarkiContoh = [
        'Polri' => [
            'Korlantas' => [
                'PJR Induk Bitung' => [
                    'Unit 1 PJR Induk Bitung',
                ],
            ],
            'Polda Metro Jaya' => [
                'Ditlantas Metro Jaya' => [
                    'Subdit Gakkum',
                    'Subdit LLAJR',
                ],
                'Polrestro Bekasi' => [
                    'Satlantas Polrestro Bekasi',
                ],
            ],
            'Polda Jawa Barat' => [
                'Polres Cimahi' => [
                    'Satlantas Polres Cimahi',
                ],
            ],
            'Polda Jabar' => [
                'Ditlantas Polda Jabar' => [
                    'Subdit Regident',
                ],
                'Pimpinan' => [],
            ],
            'Polda NTB' => [
                'Polres Lombok' => ['Satlantas Polres Lombok'],
                'Polres Lombok Tengah' => ['Satlantas Polres Lombok Tengah'],
                'Polres Lombok Barat' => ['Satlantas Polres Lombok Barat'],
                'Polres Mataram' => ['Satlantas Polres Mataram'],
            ],
            'Polda Bali' => [
                'Polres Denpasar' => ['Satlantas Polres Denpasar'],
                'Polres Badung' => ['Satlantas Polres Badung'],
            ],
            'Polda Jawa Timur' => [
                'Polres Surabaya Kota' => ['Satlantas Polres Surabaya Kota'],
                'Polres Malang Kota' => ['Satlantas Polres Malang Kota'],
            ],
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
