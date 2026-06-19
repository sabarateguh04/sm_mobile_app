<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class DemoAccountsExport implements FromCollection, WithHeadings
{
    /**
     * @param  array<int, array<string, int|string|null>>  $rows
     */
    public function __construct(
        protected Collection $rows,
    ) {}

    /**
     * @return array<int, array<int|string|null|int|string>>
     */
    public function headings(): array
    {
        return ['No.', 'Role ID', 'Nama Role', 'Username', 'Password (demo)', 'Email', 'Nama Lengkap', 'Provinsi', 'Wilayah', 'Kode profil teritori'];
    }

    public function collection(): Collection
    {
        return $this->rows->values()->map(function (array $r, int $idx) {
            return [
                $idx + 1,
                $r['role_id'] ?? '',
                $r['nama_role'] ?? '',
                $r['username'] ?? '',
                $r['password'] ?? '',
                $r['email'] ?? '',
                $r['nama_lengkap'] ?? '',
                $r['daerah'] ?? '',
                $r['wilayah'] ?? '',
                $r['kode_territori'] ?? '',
            ];
        });
    }
}
