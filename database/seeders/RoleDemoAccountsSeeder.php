<?php

namespace Database\Seeders;

use App\Models\Usm;
use App\Support\TerritoryMaster;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;

/**
 * Akun demo: Jawa Barat + NTB — admin daerah (tanpa wilayah) vs admin resort + oprator per wilayah.
 * Wilayah diisi lewat {@see TerritoryMaster}: mendukung FK numerik atau legacy (wilayah.id_daerah = namadaerah).
 *
 * Password semua akun demo (seragam): DemoRole2026!
 *
 * Akun NTB — semua wilayah diambil dari master `wilayah` (id_daerah = daerah NTB):
 * - admin_ntb_daerah: wilayah pertama urut `namawilayah` (level wilayah, selaras master).
 * - oprator_ntb: wilayah kedua (fallback wilayah pertama).
 * - admin_ntb_resort: satu baris dari tabel `wilayah` (join `daerah` via id_daerah); prioritas namawilayah mengandung "resort".
 *
 * Bila master kosong atau tidak memenuhi kebutuhan demo, seeder menambahkan contoh minimal:
 * - Jawa Barat (provinsi + ≥2 wilayah) untuk akun Jabar;
 * - NTB (≥2 wilayah + satu nama mengandung “resort”);
 * - satu provinsi lain (wilayah tidak kosong) untuk `demo_eks_daerah`.
 */
class RoleDemoAccountsSeeder extends Seeder
{
    public const DEMO_PASSWORD = 'DemoRole2026!';

    /**
     * @return array{pk: string, valueProp: string}
     */
    protected function rolesPrimaryKey(): array
    {
        if (! Schema::hasTable('roles')) {
            return ['pk' => 'id', 'valueProp' => 'id'];
        }
        if (Schema::hasColumn('roles', 'role_id')) {
            return ['pk' => 'role_id', 'valueProp' => 'role_id'];
        }

        return ['pk' => 'id', 'valueProp' => 'id'];
    }

    /**
     * Cari nama baris `daerah` yang cocok dengan Jawa Barat (bebas kapital).
     */
    protected function resolveJawaBaratDaerahName(): ?string
    {
        if (! Schema::hasTable('daerah')) {
            return null;
        }

        $exact = DB::table('daerah')
            ->whereRaw('LOWER(TRIM(namadaerah)) = ?', ['jawa barat'])
            ->value('namadaerah');

        if ($exact) {
            return $exact;
        }

        return DB::table('daerah')
            ->whereRaw('LOWER(namadaerah) LIKE ?', ['%jawa barat%'])
            ->orderBy('namadaerah')
            ->value('namadaerah');
    }

    /** Cari nama baris `daerah` untuk NTB (`namadaerah` di master bisa beda kapital/spasi). */
    protected function resolveNtbDaerahName(): ?string
    {
        if (! Schema::hasTable('daerah')) {
            return null;
        }

        foreach (['nusa tenggara barat', 'ntb'] as $needle) {
            $exact = DB::table('daerah')
                ->whereRaw('LOWER(TRIM(namadaerah)) = ?', [$needle])
                ->value('namadaerah');
            if ($exact) {
                return $exact;
            }
        }

        $like = DB::table('daerah')
            ->where(function ($q) {
                $q->whereRaw('LOWER(namadaerah) LIKE ?', ['%nusa%tenggara%barat%'])
                    ->orWhereRaw('LOWER(TRIM(namadaerah)) = ?', ['ntb'])
                    ->orWhereRaw('LOWER(namadaerah) LIKE ?', ['%(ntb)%'])
                    ->orWhere(function ($qq) {
                        $qq->whereRaw('LOWER(namadaerah) LIKE ?', ['%tenggara barat%'])
                            ->whereRaw('LOWER(namadaerah) NOT LIKE ?', ['%timur%']);
                    });
            })
            ->orderBy('namadaerah')
            ->value('namadaerah');

        if ($like) {
            return $like;
        }

        foreach (DB::table('daerah')->orderBy('namadaerah')->cursor() as $row) {
            $lower = mb_strtolower((string) ($row->namadaerah ?? ''), 'UTF-8');

            if (str_contains($lower, 'nusa tenggara barat')) {
                return $row->namadaerah;
            }
            if (str_contains($lower, 'ntb')) {
                return $row->namadaerah;
            }
            if (str_contains($lower, 'tenggara barat') && ! str_contains($lower, 'timur')) {
                return $row->namadaerah;
            }
        }

        return null;
    }

    /**
     * Sisip satu baris `daerah` bila tidak ada nama setara (perbandingan case-insensitive).
     */
    protected function insertDaerahCanonicalIfAbsent(string $canonicalNamadaerah, string $logPrefix): bool
    {
        if (! Schema::hasTable('daerah')) {
            return false;
        }

        $already = DB::table('daerah')
            ->whereRaw('LOWER(TRIM(namadaerah)) = ?', [mb_strtolower($canonicalNamadaerah, 'UTF-8')])
            ->exists();

        if ($already) {
            return false;
        }

        $row = ['namadaerah' => $canonicalNamadaerah];
        $now = now();
        if (Schema::hasColumn('daerah', 'created_at')) {
            $row['created_at'] = $now;
        }
        if (Schema::hasColumn('daerah', 'updated_at')) {
            $row['updated_at'] = $now;
        }
        DB::table('daerah')->insert($row);
        $this->command?->info($logPrefix.' ('.$canonicalNamadaerah.')');

        return true;
    }

    /**
     * Sisipkan baris wilayah yang belum ada untuk satu provinsi bila wilayah kurang atau (opsional) belum ada kata "resort".
     *
     * @param  array<int, string>  $wilayahNamawilayahCandidates
     */
    protected function insertMissingWilayahForProvinsi(
        string $namadaerahResolved,
        array $wilayahNamawilayahCandidates,
        int $minWilayahCount,
        bool $requireNamawilayahContainsResort,
        string $logBatchLabel
    ): void {
        if (! Schema::hasTable('wilayah') || ! Schema::hasTable('daerah')) {
            return;
        }

        $daerahRow = DB::table('daerah')->where('namadaerah', $namadaerahResolved)->first();

        if ($daerahRow === null) {
            return;
        }

        $wilayahList = TerritoryMaster::wilayahNamawilayahListForNamadaerah($namadaerahResolved);
        $hasResort = TerritoryMaster::wilayahQueryForNamadaerah($namadaerahResolved)
            ->whereRaw('LOWER(w.namawilayah) LIKE ?', ['%resort%'])
            ->exists();

        if (count($wilayahList) >= $minWilayahCount
            && (! $requireNamawilayahContainsResort || $hasResort)) {
            return;
        }

        $numericFk = TerritoryMaster::wilayahIdColumnIsNumericDaerahFk();
        $fkValue = $numericFk ? (int) $daerahRow->id_daerah : $namadaerahResolved;

        $inserted = 0;
        $now = now();

        foreach ($wilayahNamawilayahCandidates as $namawilayah) {
            $exists = TerritoryMaster::wilayahQueryForNamadaerah($namadaerahResolved)
                ->where('w.namawilayah', $namawilayah)
                ->exists();

            if ($exists) {
                continue;
            }

            $row = ['namawilayah' => $namawilayah, 'id_daerah' => $fkValue];
            if (Schema::hasColumn('wilayah', 'created_at')) {
                $row['created_at'] = $now;
            }
            if (Schema::hasColumn('wilayah', 'updated_at')) {
                $row['updated_at'] = $now;
            }

            DB::table('wilayah')->insert($row);
            $inserted++;
        }

        if ($inserted > 0) {
            $this->command?->info("Master `wilayah` {$logBatchLabel}: ditambah {$inserted} baris (provinsi «{$namadaerahResolved}»).");
        }
    }

    /**
     * Pastikan Jawa Barat ada dan punya minimal dua wilayah untuk admin resort (indeks kedua) + oprator.
     */
    protected function ensureJawaBaratMasterWilayahMinimal(): void
    {
        if (! Schema::hasTable('daerah') || ! Schema::hasTable('wilayah')) {
            return;
        }

        if ($this->resolveJawaBaratDaerahName() === null) {
            $this->insertDaerahCanonicalIfAbsent(
                'Jawa Barat',
                'Master `daerah`: ditambah provinsi demo Jawa Barat'
            );
        }

        $jb = $this->resolveJawaBaratDaerahName();
        if ($jb === null) {
            return;
        }

        $this->insertMissingWilayahForProvinsi(
            $jb,
            [
                'WILAYAH HUKUM BANDUNG',
                'WILAYAH HUKUM BOGOR',
            ],
            2,
            false,
            'Jabar'
        );
    }

    /**
     * Pastikan master NTB layak dipakai demo: baris provinsi di `daerah` + minimal dua wilayah dan satu nama mengandung "resort".
     */
    protected function ensureNtbMasterWilayahMinimal(): void
    {
        if (! Schema::hasTable('daerah') || ! Schema::hasTable('wilayah')) {
            return;
        }

        if ($this->resolveNtbDaerahName() === null) {
            $this->insertDaerahCanonicalIfAbsent(
                'NUSA TENGGARA BARAT',
                'Master `daerah`: ditambah provinsi demo NTB'
            );
        }

        $ntb = $this->resolveNtbDaerahName();
        if ($ntb === null) {
            return;
        }

        $this->insertMissingWilayahForProvinsi(
            $ntb,
            [
                'WILAYAH HUKUM BIMA',
                'WILAYAH HUKUM MATARAM',
                'WILAYAH HUKUM RESORT NTB',
            ],
            2,
            true,
            'NTB'
        );
    }

    /**
     * Satu provinsi di luar Jabar & NTB dengan minimal satu wilayah (akun demo_eks_daerah).
     *
     * @param  array<int, string|null>  $excludeNamadaerah
     */
    protected function ensureEksOtherDaerahWilayahIfNeeded(array $excludeNamadaerah): void
    {
        if (! Schema::hasTable('daerah') || ! Schema::hasTable('wilayah')) {
            return;
        }

        $exclude = array_values(array_filter($excludeNamadaerah, fn ($n) => $n !== null && $n !== ''));

        $firstOther = function () use ($exclude): ?string {
            $q = DB::table('daerah')->orderBy('namadaerah');
            if ($exclude !== []) {
                $q->whereNotIn('namadaerah', $exclude);
            }

            $v = $q->value('namadaerah');

            return $v !== null ? (string) $v : null;
        };

        $nama = $firstOther();

        if ($nama !== null) {
            $list = TerritoryMaster::wilayahNamawilayahListForNamadaerah($nama);
            if ($list !== []) {
                return;
            }

            $this->insertMissingWilayahForProvinsi(
                $nama,
                [
                    'WILAYAH HUKUM EKS DAERAH A',
                    'WILAYAH HUKUM EKS DAERAH B',
                ],
                1,
                false,
                'eks-daerah'
            );

            return;
        }

        $canonical = 'SUMATERA SELATAN';
        $this->insertDaerahCanonicalIfAbsent(
            $canonical,
            'Master `daerah`: ditambah provinsi untuk demo eks-daerah'
        );

        $nama = $firstOther();
        if ($nama === null) {
            return;
        }

        $this->insertMissingWilayahForProvinsi(
            $nama,
            [
                'WILAYAH HUKUM PALEMBANG',
                'WILAYAH HUKUM BATURAJA',
            ],
            1,
            false,
            'eks-daerah'
        );
    }

    /**
     * @return array<int, string> wilayah di urutan master, kosong jika tidak ada
     */
    protected function wilayahNamesForDaerah(?string $namadaerah): array
    {
        if (! $namadaerah) {
            return [];
        }

        return TerritoryMaster::wilayahNamawilayahListForNamadaerah($namadaerah);
    }

    /**
     * admin_ntb_resort: baris dari master `wilayah` + join `daerah`.
     *
     * @return array{daerah: string, wilayah: string}
     */
    protected function territoryFromWilayahTableForNtbResort(?string $ntbNamadaerah): array
    {
        if (! $ntbNamadaerah) {
            return ['daerah' => '', 'wilayah' => ''];
        }

        $base = TerritoryMaster::wilayahQueryForNamadaerah($ntbNamadaerah);

        $resortRow = (clone $base)
            ->whereRaw('LOWER(w.namawilayah) LIKE ?', ['%resort%'])
            ->orderBy('w.namawilayah')
            ->first(['w.namawilayah', 'd.namadaerah as daerah_nama']);

        if ($resortRow !== null) {
            return ['daerah' => (string) $resortRow->daerah_nama, 'wilayah' => (string) $resortRow->namawilayah];
        }

        $lastRow = (clone $base)
            ->orderByDesc('w.namawilayah')
            ->first(['w.namawilayah', 'd.namadaerah as daerah_nama']);

        if ($lastRow !== null) {
            return ['daerah' => (string) $lastRow->daerah_nama, 'wilayah' => (string) $lastRow->namawilayah];
        }

        return ['daerah' => $ntbNamadaerah, 'wilayah' => ''];
    }

    /**
     * Fallback: satu daerah + list wilayah (untuk DB tanpa “Jawa Barat”).
     *
     * @return array{daerah: ?string, wilayah_list: array<int, string>}
     */
    protected function fallbackDaerahWilayahBundle(): array
    {
        $daerah = Schema::hasTable('daerah')
            ? DB::table('daerah')->orderBy('namadaerah')->value('namadaerah')
            : null;

        return [
            'daerah' => $daerah,
            'wilayah_list' => $daerah ? $this->wilayahNamesForDaerah($daerah) : [],
        ];
    }

    /**
     * Daerah lain (bukan Jabar / NTB bila tersedia) untuk akun eks daerah demo.
     *
     * @param  array<int, string|null>  $excludeNames
     */
    protected function resolveOtherDaerahForEks(array $excludeNames): array
    {
        if (! Schema::hasTable('daerah')) {
            return ['daerah' => null, 'wilayah_list' => []];
        }

        $exclude = array_values(array_filter($excludeNames, fn ($n) => $n !== null && $n !== ''));

        $q = DB::table('daerah')->orderBy('namadaerah');
        if ($exclude !== []) {
            $q->whereNotIn('namadaerah', $exclude);
        }

        $nama = $q->value('namadaerah');

        if ($nama === null) {
            return $this->fallbackDaerahWilayahBundle();
        }

        return [
            'daerah' => $nama,
            'wilayah_list' => $this->wilayahNamesForDaerah($nama),
        ];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    protected function demoAccountRows(): array
    {
        return [
            [
                'role_id' => 1,
                'username' => 'demo_superadmin',
                'email' => 'demo.superadmin@demo.local',
                'nama' => 'Demo superadmin',
                'territory' => 'nasional',
            ],
            [
                'role_id' => 2,
                'username' => 'admin_jabar_daerah',
                'email' => 'admin.jabar.daerah@demo.local',
                'nama' => 'Admin daerah Jawa Barat',
                'territory' => 'admin_daerah_jabar',
            ],
            [
                'role_id' => 3,
                'username' => 'admin_jabar_resort',
                'email' => 'admin.jabar.resort@demo.local',
                'nama' => 'Admin resort Jawa Barat',
                'territory' => 'admin_resort_jabar',
            ],
            [
                'role_id' => 4,
                'username' => 'oprator_jabar',
                'email' => 'oprator.jabar@demo.local',
                'nama' => 'Oprator (wilayah Jawa Barat)',
                'territory' => 'oprator_jabar',
            ],
            [
                'role_id' => 2,
                'username' => 'admin_ntb_daerah',
                'email' => 'admin.ntb.daerah@demo.local',
                'nama' => 'Admin wilayah NTB (wilayah pertama, sync master)',
                'territory' => 'admin_daerah_ntb',
            ],
            [
                'role_id' => 3,
                'username' => 'admin_ntb_resort',
                'email' => 'admin.ntb.resort@demo.local',
                'nama' => 'Admin resort NTB',
                'territory' => 'admin_resort_ntb',
            ],
            [
                'role_id' => 4,
                'username' => 'oprator_ntb',
                'email' => 'oprator.ntb@demo.local',
                'nama' => 'Oprator (wilayah NTB)',
                'territory' => 'oprator_ntb',
            ],
            [
                'role_id' => 7,
                'username' => 'demo_eks_nas',
                'email' => 'demo.eks.nas@demo.local',
                'nama' => 'Demo eks nas',
                'territory' => 'nasional',
            ],
            [
                'role_id' => 6,
                'username' => 'demo_eks_daerah',
                'email' => 'demo.eks.daerah@demo.local',
                'nama' => 'Demo eks daerah',
                'territory' => 'eks_daerah_other',
            ],
            [
                'role_id' => 5,
                'username' => 'demo_eks_resort',
                'email' => 'demo.eks.resort@demo.local',
                'nama' => 'Demo eks resort',
                'territory' => 'eks_resort_linked',
            ],
            [
                'role_id' => 7,
                'username' => 'demo_eks_external',
                'email' => 'demo.eks.external@demo.local',
                'nama' => 'Demo eks external',
                'territory' => 'minimal',
            ],
        ];
    }

    /**
     * @param  array<string, mixed>  $payload
     * @param  array{daerah: ?string, wilayah_list: array<int, string>}  $eksOther
     * @return array<string, mixed>
     */
    protected function applyTerritoryProfile(
        array $payload,
        string $territoryProfile,
        string $jawaBaratNama,
        array $jbWilayahList,
        string $ntbNama,
        array $ntbWilayahList,
        array $fallback,
        array $eksOther
    ): array {
        $fallbackDaerah = $fallback['daerah'] ?? '';
        $fallbackWils = $fallback['wilayah_list'] ?? [];

        $effectiveJabar = $jawaBaratNama ?: $fallbackDaerah;
        $wilsJabar = $jawaBaratNama ? $jbWilayahList : $fallbackWils;

        $effectiveNtb = $ntbNama ?: '';
        $wilsNtb = $ntbNama ? $ntbWilayahList : [];

        $eksD = $eksOther['daerah'] ?? '';
        $eksWils = $eksOther['wilayah_list'] ?? [];

        switch ($territoryProfile) {
            case 'admin_daerah_jabar':
                $payload['daerah'] = $effectiveJabar ?: '';
                $payload['wilayah'] = '';
                break;
            case 'admin_resort_jabar':
                $payload['daerah'] = $effectiveJabar ?: '';
                $payload['wilayah'] = $wilsJabar[1] ?? $wilsJabar[0] ?? '';
                break;
            case 'oprator_jabar':
                $payload['daerah'] = $effectiveJabar ?: '';
                $payload['wilayah'] = $wilsJabar[0] ?? '';
                break;
            case 'admin_daerah_ntb':
                // Nama akun tetap *_daerah; profil aplikasi mengikuti level wilayah (+ daerah dari master NTB).
                $payload['daerah'] = $effectiveNtb ?: '';
                $payload['wilayah'] = $wilsNtb[0] ?? '';
                break;
            case 'oprator_ntb':
                $payload['daerah'] = $effectiveNtb ?: '';
                $payload['wilayah'] = $wilsNtb[1] ?? $wilsNtb[0] ?? '';
                break;
            case 'admin_resort_ntb':
                $w = $this->territoryFromWilayahTableForNtbResort($effectiveNtb !== '' ? $effectiveNtb : null);
                $payload['daerah'] = $w['daerah'];
                $payload['wilayah'] = $w['wilayah'];
                break;
            case 'eks_daerah_other':
                if ($eksD !== '') {
                    $payload['daerah'] = $eksD;
                    $payload['wilayah'] = $eksWils[0] ?? '';
                }
                break;
            case 'eks_resort_linked':
                $payload['daerah'] = $effectiveJabar ?: '';
                $payload['wilayah'] = $wilsJabar[0] ?? '';
                break;
            case 'nasional':
            case 'minimal':
            default:
                break;
        }

        return $payload;
    }

    public function run(): void
    {
        if (! Schema::hasTable('roles') || ! Schema::hasTable('usm')) {
            $this->command?->warn('Tabel roles atau usm tidak ada — RoleDemoAccountsSeeder dilewati.');

            return;
        }

        $this->ensureJawaBaratMasterWilayahMinimal();
        $this->ensureNtbMasterWilayahMinimal();

        $jawaBaratNama = $this->resolveJawaBaratDaerahName();
        $fallback = $this->fallbackDaerahWilayahBundle();

        if (! $jawaBaratNama) {
            $this->command?->warn('Master `daerah` tidak memuat “Jawa Barat” — pakai daerah pertama sebagai contoh admin/oprator.');
        }

        $jbWilayahList = $jawaBaratNama ? $this->wilayahNamesForDaerah($jawaBaratNama) : [];

        $ntbNama = $this->resolveNtbDaerahName();
        $ntbWilayahList = $ntbNama ? $this->wilayahNamesForDaerah($ntbNama) : [];

        if (! $ntbNama) {
            $this->command?->warn('Master `daerah` tidak memuat “NTB” / “Nusa Tenggara Barat” — akun admin_ntb_* / oprator_ntb dapat kosong wilayah/daerah.');
        } elseif ($ntbWilayahList === []) {
            $this->command?->warn('NTB ada di master daerah tetapi belum ada baris wilayah dengan id_daerah yang sama — isi wilayah di master untuk menghubungkan resort NTB.');
        }

        $this->ensureEksOtherDaerahWilayahIfNeeded([
            $jawaBaratNama,
            $ntbNama,
        ]);

        $eksOther = $this->resolveOtherDaerahForEks(array_filter([
            $jawaBaratNama,
            $ntbNama,
        ], fn ($n) => $n !== null && $n !== ''));

        $meta = $this->rolesPrimaryKey();
        $pk = $meta['pk'];
        $prop = $meta['valueProp'];
        $password = Hash::make(self::DEMO_PASSWORD);

        $created = 0;
        $skipped = 0;

        foreach ($this->demoAccountRows() as $row) {
            $roleId = (int) $row['role_id'];
            $role = DB::table('roles')->where($pk, $roleId)->first();
            if (! $role) {
                $this->command?->warn("Role ID {$roleId} tidak ada — lewat akun {$row['username']}.");
                $skipped++;

                continue;
            }

            $rolePkValue = $role->{$prop};
            $territoryProfile = (string) ($row['territory'] ?? 'minimal');

            $payload = [
                'username' => $row['username'],
                'email' => $row['email'],
                'password' => $password,
                'nama' => $row['nama'],
                'account_status' => 'active',
                'user_role_id' => $rolePkValue,
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

            Usm::query()->updateOrCreate(
                ['username' => $row['username']],
                $payload
            );
            $created++;
        }

        $this->command?->info("Akun demo: {$created} dibuat/diperbarui, {$skipped} dilewatkan (role tidak ada).");
        $this->command?->info('Jabar: admin_jabar_daerah vs admin_jabar_resort + oprator_jabar — wilayah dari relasi wilayah.id_daerah → daerah.');
        $this->command?->info('NTB: admin_ntb_resort = baris tabel `wilayah` (join daerah), prioritas nama mengandung "resort", else wilayah terakhir urut nama.');
        $this->command?->info('Password semua akun demo: '.self::DEMO_PASSWORD);
    }
}
