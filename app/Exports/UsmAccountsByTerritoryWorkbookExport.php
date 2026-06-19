<?php

namespace App\Exports;

use App\Models\Usm;
use App\Support\TerritoryLevelForUsm;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

/**
 * Buku kerja Excel: lembar Nasional, Daerah, dan Wilayah (kosong bila tidak ada akun).
 */
class UsmAccountsByTerritoryWorkbookExport implements WithMultipleSheets
{
    /**
     * @param  Collection<int, Usm>  $users
     */
    public function __construct(
        protected Collection $users,
    ) {}

    public function sheets(): array
    {
        $grouped = [
            TerritoryLevelForUsm::NASIONAL => collect(),
            TerritoryLevelForUsm::DAERAH => collect(),
            TerritoryLevelForUsm::WILAYAH => collect(),
        ];

        foreach ($this->users as $user) {
            $level = TerritoryLevelForUsm::resolve($user);
            $grouped[$level]->push($user);
        }

        $titles = [
            TerritoryLevelForUsm::NASIONAL => 'Nasional',
            TerritoryLevelForUsm::DAERAH => 'Daerah',
            TerritoryLevelForUsm::WILAYAH => 'Wilayah',
        ];

        $sheets = [];
        foreach ($titles as $level => $title) {
            /** @var Collection<int, Usm> $rows */
            $rows = $grouped[$level]->sortBy('username')->values();
            $sheets[] = new UsmTerritorySheetExport($rows, $title);
        }

        return $sheets;
    }
}
