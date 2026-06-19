<?php

namespace App\Exports;

use App\Models\Usm;
use App\Support\UsmPasswordPlainHint;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

/**
 * Ekspor semua (atau subset) pengguna dari tabel {@see Usm} ke spreadsheet.
 */
class UsmAccountsExport implements FromCollection, WithHeadings
{
    /**
     * @param  Collection<int, Usm>  $users
     */
    public function __construct(
        protected Collection $users,
        protected bool $includeGlobalUserId = true,
    ) {}

    /**
     * @return array<int, string>
     */
    public function headings(): array
    {
        $headings = ['No.'];

        if ($this->includeGlobalUserId) {
            $headings[] = 'ID USM';
        }

        return array_merge($headings, [
            'Role ID',
            'Nama Role',
            'Username',
            'Password (catatan)',
            'Email',
            'Nama lengkap',
            'NRP / NIP / NIK',
            'Induk organisasi',
            'Organisasi daerah',
            'Unit kerja',
            'Provinsi (daerah)',
            'Wilayah (kab/kota)',
            'Kategori',
            'Status akun',
        ]);
    }

    public function collection(): Collection
    {
        return $this->users->values()->map(function (Usm $u, int $idx) {
            $roleName = '';
            if ($u->relationLoaded('role') && $u->role !== null) {
                $roleName = (string) ($u->role->role_name ?? '');
            }

            $row = [$idx + 1];

            if ($this->includeGlobalUserId) {
                $row[] = $u->id_usm;
            }

            return array_merge($row, [
                $u->user_role_id ?? '',
                $roleName,
                $u->username,
                UsmPasswordPlainHint::forUsername((string) $u->username),
                $u->email,
                $u->nama,
                $u->nrp_nip_nik ?? '',
                $u->induk_pemerintahan ?? '',
                $u->pemerintahan_daerah ?? '',
                $u->unit_kerja ?? '',
                $u->daerah ?? '',
                $u->wilayah ?? '',
                $u->kategori_user ?? '',
                $u->account_status ?? '',
            ]);
        });
    }

    /** @deprecated Gunakan {@see UsmPasswordPlainHint::forUsername()} */
    public static function passwordPlainHint(string $username): string
    {
        return UsmPasswordPlainHint::forUsername($username);
    }
}
