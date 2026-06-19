<?php

namespace App\Exports;

use App\Models\Usm;
use App\Support\UsmPasswordPlainHint;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;

/**
 * Satu lembar Excel: username, password (jika diketahui), dan teritori.
 */
class UsmTerritorySheetExport implements FromCollection, WithHeadings, WithTitle
{
    /**
     * @param  Collection<int, Usm>  $users
     */
    public function __construct(
        protected Collection $users,
        protected string $sheetTitle,
    ) {}

    public function title(): string
    {
        return $this->sheetTitle;
    }

    /**
     * @return array<int, string>
     */
    public function headings(): array
    {
        return [
            'No.',
            'Username',
            'Password',
            'Nama lengkap',
            'Nama role',
            'Provinsi (daerah)',
            'Wilayah (kab/kota)',
            'Email',
            'Status akun',
        ];
    }

    public function collection(): Collection
    {
        return $this->users->values()->map(function (Usm $u, int $idx) {
            $roleName = '';
            if ($u->relationLoaded('role') && $u->role !== null) {
                $roleName = (string) ($u->role->role_name ?? '');
            }

            return [
                $idx + 1,
                $u->username,
                UsmPasswordPlainHint::forUsername((string) $u->username),
                $u->nama,
                $roleName,
                $u->daerah ?? '',
                $u->wilayah ?? '',
                $u->email,
                $u->account_status ?? '',
            ];
        });
    }
}
