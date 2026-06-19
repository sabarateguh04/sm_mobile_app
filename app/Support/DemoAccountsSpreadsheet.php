<?php

namespace App\Support;

use Database\Seeders\MasterTerritoryDemoAccountsSeeder;
use Database\Seeders\RoleDemoAccountsSeeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Mengisi baris spreadsheet akun demo dengan logika teritori sama seperti {@see RoleDemoAccountsSeeder}.
 */
class DemoAccountsSpreadsheet extends RoleDemoAccountsSeeder
{
    /**
     * Password teks sama dengan konstanta seeder (tidak ada hash).
     *
     * @return array<int, array<string, int|string|null>>
     */
    public function spreadsheetRows(): array
    {
        if (! Schema::hasTable('roles')) {
            return [];
        }

        $jawaBaratNama = $this->resolveJawaBaratDaerahName();
        $fallback = $this->fallbackDaerahWilayahBundle();

        $jbWilayahList = $jawaBaratNama ? $this->wilayahNamesForDaerah($jawaBaratNama) : [];

        $ntbNama = $this->resolveNtbDaerahName();
        $ntbWilayahList = $ntbNama ? $this->wilayahNamesForDaerah($ntbNama) : [];

        $eksOther = $this->resolveOtherDaerahForEks(array_filter([
            $jawaBaratNama,
            $ntbNama,
        ], fn ($n) => $n !== null && $n !== ''));

        $meta = $this->rolesPrimaryKey();
        $pk = $meta['pk'];

        $rows = [];

        foreach ($this->demoAccountRows() as $template) {
            $roleId = (int) $template['role_id'];
            $role = DB::table('roles')->where($pk, $roleId)->first();
            if (! $role) {
                continue;
            }

            $territoryProfile = (string) ($template['territory'] ?? 'minimal');
            $roleName = isset($role->role_name) ? (string) $role->role_name : '';

            $payload = [
                'username' => (string) $template['username'],
                'email' => (string) $template['email'],
                'password' => '',
                'nama' => (string) $template['nama'],
                'account_status' => 'active',
                'user_role_id' => (int) $role->{$meta['valueProp']},
                'notelp' => '',
                'fotoprof' => '',
                'daerah' => '',
                'wilayah' => '',
            ];

            $payload = $this->applyTerritoryProfile(
                $payload,
                $territoryProfile,
                (string) ($jawaBaratNama ?? ''),
                $jbWilayahList,
                (string) ($ntbNama ?? ''),
                $ntbWilayahList,
                $fallback,
                $eksOther
            );

            $rows[] = [
                'role_id' => $roleId,
                'nama_role' => $roleName,
                'username' => $template['username'],
                'password' => self::DEMO_PASSWORD,
                'email' => $template['email'],
                'nama_lengkap' => $template['nama'],
                'daerah' => $payload['daerah'] ?? '',
                'wilayah' => $payload['wilayah'] ?? '',
                'kode_territori' => $territoryProfile,
            ];
        }

        return $rows;
    }

    /**
     * Semua baris: akun demo lama (Jabar/NTB) + daftar akun master provinsi/kab (preview, konsisten dengan {@see MasterTerritoryDemoAccountsSeeder}).
     *
     * @return array<int, array<string, int|string|null>>
     */
    public function spreadsheetRowsCombined(): array
    {
        $legacy = $this->spreadsheetRows();
        $master = (new MasterTerritoryDemoAccountsSeeder)->previewSpreadsheetRows();
        $merged = array_merge($legacy, $master);

        usort($merged, function (array $a, array $b): int {
            return strcmp((string) ($a['username'] ?? ''), (string) ($b['username'] ?? ''));
        });

        return $merged;
    }
}
