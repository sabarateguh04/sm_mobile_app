<?php

namespace App\Support;

use App\Models\Usm;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

/**
 * Satu sumber untuk form/registrasi USM:
 * - provinsi / kab-kota (administrator & oprator): prioritas `master_territori` (hierarki id/parent_id/level/kode/nama),
 *   lalu `master_provinsi` & `master_kabupaten_kota`, lalu legacy `daerah`/`wilayah`;
 * - registrasi mandiri: jika `master_kl_daerah` berisi data, form memakai daerah KL + resort (`{@see registrationWilayahDaerahMasterBundle}`).
 * - kepegawaian: admin memakai `org` / `suborg`; bila master KL terisi memakai 4 tingkat
 *   `master_kl_pusat` (induk) → `master_kl_daerah` (organisasi pusat) → `master_kl_resort` (organisasi daerah)
 *   → `master_kl_unit_kerja` (unit kerja).
 */
class UsmMasterReferensi
{
    /** @return list<string> */
    public static function indukOrganisasiEquivalentNames(string $induk): array
    {
        $induk = trim($induk);
        $names = [$induk];

        $aliases = [
            'polri' => ['Mabes Polri'],
            'mabes polri' => ['Polri'],
        ];

        $key = mb_strtolower($induk);
        foreach ($aliases[$key] ?? [] as $alias) {
            $names[] = $alias;
        }

        return array_values(array_unique(array_filter($names)));
    }

    public static function indukOrganisasiNamesMatch(string $left, string $right): bool
    {
        $left = trim($left);
        $right = trim($right);
        if ($left === '' || $right === '') {
            return $left === $right;
        }

        if (strcasecmp($left, $right) === 0) {
            return true;
        }

        $leftSet = array_map('mb_strtolower', static::indukOrganisasiEquivalentNames($left));
        $rightSet = array_map('mb_strtolower', static::indukOrganisasiEquivalentNames($right));

        return count(array_intersect($leftSet, $rightSet)) > 0;
    }

    /** @return list<int> */
    public static function masterKlPusatIdsForInduk(?string $indukPusat): array
    {
        $indukPusat = trim((string) $indukPusat);
        if ($indukPusat === '' || ! Schema::hasTable('master_kl_pusat')) {
            return [];
        }

        $names = static::indukOrganisasiEquivalentNames($indukPusat);

        return DB::table('master_kl_pusat')
            ->where(function ($query) use ($names): void {
                foreach ($names as $name) {
                    $query->orWhereRaw('LOWER(TRIM(nama)) = ?', [mb_strtolower(trim($name))]);
                }
            })
            ->pluck('id')
            ->map(static fn ($id): int => (int) $id)
            ->values()
            ->all();
    }

    /** @return list<int> */
    public static function masterKlDaerahIdsForOrgAndInduk(?string $indukPusat, ?string $orgDaerah): array
    {
        $orgDaerah = trim((string) $orgDaerah);
        if ($orgDaerah === '' || ! Schema::hasTable('master_kl_daerah')) {
            return [];
        }

        $query = DB::table('master_kl_daerah')
            ->whereRaw('LOWER(TRIM(nama)) = ?', [mb_strtolower($orgDaerah)]);

        $pusatIds = static::masterKlPusatIdsForInduk($indukPusat);
        if (trim((string) $indukPusat) !== '') {
            $query->whereIn('parent_id', $pusatIds !== [] ? $pusatIds : [-1]);
        }

        return $query->pluck('id')
            ->map(static fn ($id): int => (int) $id)
            ->values()
            ->all();
    }

    /** @return list<int> */
    public static function masterKlResortIdsForHierarchy(?string $indukPusat, ?string $orgPusat, ?string $organisasiDaerah): array
    {
        $organisasiDaerah = trim((string) $organisasiDaerah);
        $orgPusat = trim((string) $orgPusat);
        if ($organisasiDaerah === '' || $orgPusat === '' || ! Schema::hasTable('master_kl_resort')) {
            return [];
        }

        $daerahIds = static::masterKlDaerahIdsForOrgAndInduk($indukPusat, $orgPusat);
        if ($daerahIds === []) {
            return [];
        }

        return DB::table('master_kl_resort')
            ->whereRaw('LOWER(TRIM(nama)) = ?', [mb_strtolower($organisasiDaerah)])
            ->whereIn('parent_id', $daerahIds)
            ->pluck('id')
            ->map(static fn ($id): int => (int) $id)
            ->values()
            ->all();
    }

    public static function masterKlResortExistsForHierarchy(?string $indukPusat, ?string $orgPusat, ?string $organisasiDaerah): bool
    {
        return static::masterKlResortIdsForHierarchy($indukPusat, $orgPusat, $organisasiDaerah) !== [];
    }

    public static function masterKlUnitKerjaExistsForHierarchy(
        ?string $indukPusat,
        ?string $orgPusat,
        ?string $organisasiDaerah,
        ?string $unitKerja
    ): bool {
        $unitKerja = trim((string) $unitKerja);
        if ($unitKerja === '' || ! static::masterKlEmpatTingkatAktif()) {
            return false;
        }

        $resortIds = static::masterKlResortIdsForHierarchy($indukPusat, $orgPusat, $organisasiDaerah);
        if ($resortIds === []) {
            return false;
        }

        return DB::table('master_kl_unit_kerja')
            ->whereRaw('LOWER(TRIM(nama)) = ?', [mb_strtolower($unitKerja)])
            ->whereIn('parent_id', $resortIds)
            ->exists();
    }

    /** Hierarki nasional → provinsi → kab/kota (kode wilayah terpadu). */
    public static function wilayahPakaiMasterTerritori(): bool
    {
        if (! Schema::hasTable('master_territori')) {
            return false;
        }

        return ! (Schema::hasTable('daerah') && Schema::hasTable('wilayah'))
            && DB::table('master_territori')->where('level', 'provinsi')->exists();
    }

    public static function wilayahPakaiTabelMaster(): bool
    {
        return Schema::hasTable('master_provinsi')
            && Schema::hasTable('master_kabupaten_kota')
            && ! (Schema::hasTable('daerah') && Schema::hasTable('wilayah'))
            && DB::table('master_provinsi')->exists();
    }

    /** Halaman daftar: pilihan dari `master_kl_daerah` / `master_kl_resort` jika master KL sudah terisi. */
    public static function registrationPakaiMasterKlDaerah(): bool
    {
        if (! Schema::hasTable('master_kl_daerah') || ! Schema::hasTable('master_kl_resort')) {
            return false;
        }

        return DB::table('master_kl_daerah')->exists();
    }

    /**
     * Hierarki master KL untuk cascading form (parent_id).
     * 4 tingkat: induk → organisasi pusat → organisasi daerah → unit kerja.
     * 3 tingkat (legacy): induk → organisasi pusat → unit kerja (resort).
     *
     * @return array<string, array<string, array<string, list<string>>|list<string>>>
     */
    public static function kepegawaianMasterKlHierarchyJson(): array
    {
        if (! static::masterKlTigaTingkatAktif()) {
            return [];
        }

        $empatTingkat = static::masterKlEmpatTingkatAktif();
        $hierarchy = [];

        foreach (static::masterKlPusatIndukCollection() as $pusat) {
            $canonicalKey = static::canonicalIndukOrganisasiKey((string) $pusat->nama);
            if (! isset($hierarchy[$canonicalKey])) {
                $hierarchy[$canonicalKey] = [];
            }

            $orgPusatRows = DB::table('master_kl_daerah')
                ->where('parent_id', $pusat->id)
                ->orderBy('nama')
                ->get(['id', 'nama']);

            foreach ($orgPusatRows as $orgPusat) {
                $orgPusatNama = (string) $orgPusat->nama;

                if (! $empatTingkat) {
                    $units = [];
                    if (Schema::hasTable('master_kl_resort')) {
                        $units = DB::table('master_kl_resort')
                            ->where('parent_id', $orgPusat->id)
                            ->orderBy('nama')
                            ->pluck('nama')
                            ->map(static fn ($nama): string => (string) $nama)
                            ->values()
                            ->all();
                    }
                    $existing = $hierarchy[$canonicalKey][$orgPusatNama] ?? [];
                    $hierarchy[$canonicalKey][$orgPusatNama] = array_values(array_unique(array_merge(
                        is_array($existing) ? $existing : [],
                        $units
                    )));

                    continue;
                }

                if (! isset($hierarchy[$canonicalKey][$orgPusatNama]) || ! is_array($hierarchy[$canonicalKey][$orgPusatNama])) {
                    $hierarchy[$canonicalKey][$orgPusatNama] = [];
                }

                $resortRows = DB::table('master_kl_resort')
                    ->where('parent_id', $orgPusat->id)
                    ->orderBy('nama')
                    ->get(['id', 'nama']);

                foreach ($resortRows as $resort) {
                    $resortNama = (string) $resort->nama;
                    $unitNames = DB::table('master_kl_unit_kerja')
                        ->where('parent_id', $resort->id)
                        ->orderBy('nama')
                        ->pluck('nama')
                        ->map(static fn ($nama): string => (string) $nama)
                        ->values()
                        ->all();

                    $existingUnits = $hierarchy[$canonicalKey][$orgPusatNama][$resortNama] ?? [];
                    $hierarchy[$canonicalKey][$orgPusatNama][$resortNama] = array_values(array_unique(array_merge($existingUnits, $unitNames)));
                }
            }
        }

        return $hierarchy;
    }

    /** Kunci induk untuk hierarki/form (gabung alias, mis. Polri ≡ Mabes Polri). */
    public static function canonicalIndukOrganisasiKey(string $induk): string
    {
        $induk = trim($induk);
        if ($induk === '') {
            return '';
        }

        if (static::indukOrganisasiNamesMatch($induk, 'Polri')) {
            return 'Polri';
        }

        return $induk;
    }

    /**
     * Form kepegawaian master KL (minimal pusat → daerah → resort).
     */
    public static function masterKlTigaTingkatAktif(): bool
    {
        if (! static::registrationPakaiMasterKlDaerah()) {
            return false;
        }

        return Schema::hasTable('master_kl_pusat');
    }

    /** Hierarki kepegawaian 4 tingkat (tabel `master_kl_unit_kerja` tersedia). */
    public static function masterKlEmpatTingkatAktif(): bool
    {
        return static::masterKlTigaTingkatAktif()
            && Schema::hasTable('master_kl_unit_kerja');
    }

    /**
     * @return \Illuminate\Support\Collection<int, object{id:int, nama:string}>
     */
    protected static function masterKlPusatIndukCollection(): Collection
    {
        if (! Schema::hasTable('master_kl_pusat')) {
            return collect();
        }

        $roots = DB::table('master_kl_pusat')
            ->whereNull('parent_id')
            ->orderBy('nama')
            ->get(['id', 'nama']);

        if ($roots->isNotEmpty()) {
            return $roots;
        }

        $parentIds = DB::table('master_kl_daerah')->distinct()->pluck('parent_id')->filter()->all();
        if ($parentIds === []) {
            return collect();
        }

        return DB::table('master_kl_pusat')
            ->whereIn('id', $parentIds)
            ->orderBy('nama')
            ->get(['id', 'nama']);
    }

    /**
     * Bundle wilayah untuk `/register`: master KL daerah/resort jika tersedia, selain itu sama dengan {@see wilayahDaerahMasterBundle}.
     *
     * @return array{
     *     daerahList: \Illuminate\Support\Collection<int, object{id_daerah:int|string|null, namadaerah:string, kode?:string|null}>,
     *     wilayahByDaerahJson: array<string, array<int, string|array{nama:string,kode?:string}>>,
     *     daerahNameSet: array<string, int>,
     *     wilayahPairMap: array<string, string>,
     *     wilayahPakaiMasterTerritori: bool,
     *     wilayahPakaiMasterKlDaerah: bool
     * }
     */
    public static function registrationWilayahDaerahMasterBundle(): array
    {
        if (! static::registrationPakaiMasterKlDaerah()) {
            $out = static::wilayahDaerahMasterBundle();
            $out['wilayahPakaiMasterKlDaerah'] = false;

            return $out;
        }

        $daerahRows = DB::table('master_kl_daerah')->orderBy('nama')->get(['id', 'nama']);

        $daerahList = $daerahRows->map(fn ($r) => (object) [
            'id_daerah' => $r->id,
            'namadaerah' => $r->nama,
            'kode' => null,
        ]);

        $wilayahByDaerahJson = [];
        $wilayahPairMap = [];

        foreach ($daerahRows as $d) {
            $resorts = DB::table('master_kl_resort')
                ->where('parent_id', $d->id)
                ->orderBy('nama')
                ->pluck('nama')
                ->all();

            $wilayahByDaerahJson[$d->nama] = array_values($resorts);

            foreach ($resorts as $rname) {
                $wilayahPairMap[$d->nama.'|'.$rname] = $rname;
            }
        }

        $daerahNameSet = $daerahList->pluck('namadaerah')->flip()->all();

        return [
            'daerahList' => $daerahList,
            'wilayahByDaerahJson' => $wilayahByDaerahJson,
            'daerahNameSet' => $daerahNameSet,
            'wilayahPairMap' => $wilayahPairMap,
            'wilayahPakaiMasterTerritori' => false,
            'wilayahPakaiMasterKlDaerah' => true,
        ];
    }

    /** Aturan validasi `daerah` / `wilayah` untuk POST registrasi (sinkron dengan bundle registrasi). */
    public static function registrationDaerahWilayahValidationRules(): array
    {
        if (! static::registrationPakaiMasterKlDaerah()) {
            return static::daerahWilayahValidationRules();
        }

        return [
            'daerah' => ['nullable', 'string', 'max:255', Rule::exists('master_kl_daerah', 'nama')],
            'wilayah' => [
                'nullable',
                'string',
                'max:255',
                Rule::exists('master_kl_resort', 'nama')->where(function ($query): void {
                    $namaDaerah = Request::input('daerah');
                    if ($namaDaerah === null || $namaDaerah === '') {
                        $query->whereRaw('1 = 0');

                        return;
                    }
                    $ids = DB::table('master_kl_daerah')->where('nama', $namaDaerah)->pluck('id')->all();
                    $query->whereIn('parent_id', $ids !== [] ? $ids : [-1]);
                }),
            ],
        ];
    }

    /**
     * @return array{
     *     daerahList: Collection<int, object{id_daerah:int|string|null, namadaerah:string, kode?:string}>,
     *     wilayahByDaerahJson: array<string, array<int, string|array{nama:string,kode:string}>>,
     *     daerahNameSet: array<string, int>,
     *     wilayahPairMap: array<string, string>,
     *     wilayahPakaiMasterTerritori: bool
     * }
     */
    public static function wilayahDaerahMasterBundle(): array
    {
        if (static::wilayahPakaiMasterTerritori()) {
            $out = static::wilayahDaerahBundleDariMasterTerritori();
        } elseif (static::wilayahPakaiTabelMaster()) {
            $out = static::wilayahDaerahBundleDariMaster();
        } else {
            $out = static::wilayahDaerahBundleLegacy();
        }
        $out['wilayahPakaiMasterTerritori'] = static::wilayahPakaiMasterTerritori();

        return $out;
    }

    /**
     * Bundle provinsi/polres selaras dengan halaman `/master/resort` (prioritas legacy → master_provinsi/kabkot).
     *
     * @return array{
     *     daerahList: Collection<int, object{id_daerah:int|string|null, namadaerah:string, kode?:string}>,
     *     wilayahByDaerahJson: array<string, array<int, string|array{nama:string,kode?:string}>>,
     *     daerahNameSet: array<string, int>,
     *     wilayahPairMap: array<string, string>,
     *     wilayahPakaiMasterTerritori: bool
     * }
     */
    public static function wilayahDaerahResortBundle(): array
    {
        $useLegacy = Schema::hasTable('daerah') && Schema::hasTable('wilayah');
        $useMaster = Schema::hasTable('master_provinsi') && Schema::hasTable('master_kabupaten_kota');

        if ($useLegacy) {
            $out = static::wilayahDaerahBundleLegacy();
        } elseif ($useMaster) {
            $out = static::wilayahDaerahBundleDariMaster();
        } else {
            $out = [
                'daerahList' => collect(),
                'wilayahByDaerahJson' => [],
                'daerahNameSet' => [],
                'wilayahPairMap' => [],
            ];
        }

        $out['wilayahPakaiMasterTerritori'] = false;

        return $out;
    }

    /**
     * Seleksi Polda/Polres dari query URL (parameter `id_daerah` / `id_wilayah` seperti di `/master/resort`).
     *
     * @return array{daerah: string, wilayah: string}
     */
    public static function resolveTerritorialQuerySelection(
        mixed $idDaerah = null,
        mixed $idWilayah = null,
        mixed $daerahNama = null,
        mixed $wilayahNama = null,
    ): array {
        $daerah = trim((string) ($daerahNama ?? ''));
        $wilayah = trim((string) ($wilayahNama ?? ''));

        $useLegacy = Schema::hasTable('daerah') && Schema::hasTable('wilayah');
        $useMaster = Schema::hasTable('master_provinsi') && Schema::hasTable('master_kabupaten_kota');

        if ($idWilayah !== null && $idWilayah !== '') {
            if ($useMaster && ! $useLegacy) {
                $row = DB::table('master_kabupaten_kota as w')
                    ->join('master_provinsi as d', 'd.id', '=', 'w.master_provinsi_id')
                    ->where('w.id', $idWilayah)
                    ->first(['w.nama as namawilayah', 'd.nama as namadaerah']);
                if ($row !== null) {
                    $wilayah = (string) $row->namawilayah;
                    if ($daerah === '') {
                        $daerah = (string) $row->namadaerah;
                    }
                }
            } elseif ($useLegacy) {
                $row = DB::table('wilayah')->where('id_wilayah', $idWilayah)->first(['namawilayah', 'id_daerah']);
                if ($row !== null) {
                    $wilayah = (string) $row->namawilayah;
                    if ($daerah === '' && $row->id_daerah !== null && $row->id_daerah !== '') {
                        if (TerritoryMaster::wilayahIdColumnIsNumericDaerahFk()) {
                            $nama = DB::table('daerah')->where('id_daerah', $row->id_daerah)->value('namadaerah');
                            $daerah = (string) ($nama ?? '');
                        } else {
                            $daerah = (string) $row->id_daerah;
                        }
                    }
                }
            }
        }

        if ($idDaerah !== null && $idDaerah !== '' && $daerah === '') {
            if ($useMaster && ! $useLegacy) {
                $nama = DB::table('master_provinsi')->where('id', $idDaerah)->value('nama');
                $daerah = (string) ($nama ?? '');
            } elseif ($useLegacy) {
                $nama = DB::table('daerah')->where('id_daerah', $idDaerah)->value('namadaerah');
                $daerah = (string) ($nama ?? '');
            }
        }

        return ['daerah' => $daerah, 'wilayah' => $wilayah];
    }

    /**
     * Aturan validasi `daerah` / `wilayah` selaras dengan {@see wilayahDaerahResortBundle} dan `/master/resort`.
     *
     * @return array<string, mixed>
     */
    public static function daerahWilayahResortValidationRules(): array
    {
        $useLegacy = Schema::hasTable('daerah') && Schema::hasTable('wilayah');
        $useMaster = Schema::hasTable('master_provinsi') && Schema::hasTable('master_kabupaten_kota');

        if ($useLegacy) {
            return [
                'daerah' => ['nullable', 'string', 'max:255', Rule::exists('daerah', 'namadaerah')],
                'wilayah' => [
                    'nullable',
                    'string',
                    'max:255',
                    Rule::exists('wilayah', 'namawilayah')->where(function ($q) {
                        return $q->where('id_daerah', Request::input('daerah'));
                    }),
                ],
            ];
        }

        if ($useMaster) {
            return [
                'daerah' => ['nullable', 'string', 'max:255', Rule::exists('master_provinsi', 'nama')],
                'wilayah' => [
                    'nullable',
                    'string',
                    'max:255',
                    Rule::exists('master_kabupaten_kota', 'nama')->where(function ($query): void {
                        $namaProv = Request::input('daerah');
                        if ($namaProv === null || $namaProv === '') {
                            $query->whereRaw('1 = 0');

                            return;
                        }
                        $ids = DB::table('master_provinsi')->where('nama', $namaProv)->pluck('id')->all();
                        $query->whereIn('master_provinsi_id', $ids !== [] ? $ids : [-1]);
                    }),
                ],
            ];
        }

        return [
            'daerah' => ['nullable', 'string', 'max:255'],
            'wilayah' => ['nullable', 'string', 'max:255'],
        ];
    }

    /**
     * Bundle dari `master_territori`: level nasional → provinsi → kabupaten (semua kab/kot dari master).
     *
     * @return array{
     *     daerahList: \Illuminate\Support\Collection<int, object{id_daerah:int|string|null, namadaerah:string, kode?:string}>,
     *     wilayahByDaerahJson: array<string, array<int, string|array{nama:string,kode:string}>>,
     *     daerahNameSet: array<string, int>,
     *     wilayahPairMap: array<string, string>
     * }
     */
    protected static function wilayahDaerahBundleDariMasterTerritori(): array
    {
        $root = DB::table('master_territori')->where('level', 'nasional')->orderBy('id')->first();

        if ($root === null) {
            return static::wilayahDaerahBundleDariMaster();
        }

        $provinsi = DB::table('master_territori')
            ->where('parent_id', $root->id)
            ->where('level', 'provinsi')
            ->orderBy('nama')
            ->get(['id', 'kode', 'nama']);

        $daerahList = $provinsi->map(fn ($p) => (object) [
            'id_daerah' => $p->id,
            'namadaerah' => $p->nama,
            'kode' => $p->kode,
        ]);

        $wilayahByDaerahJson = [];
        $wilayahPairMap = [];

        foreach ($provinsi as $p) {
            $kabRows = DB::table('master_territori')
                ->where('parent_id', $p->id)
                ->whereIn('level', ['kabupaten', 'kota'])
                ->orderBy('nama')
                ->get(['nama', 'kode']);

            $wilayahByDaerahJson[$p->nama] = $kabRows->map(fn ($k) => [
                'nama' => $k->nama,
                'kode' => $k->kode,
            ])->values()->all();

            foreach ($kabRows as $k) {
                $wilayahPairMap[$p->nama.'|'.$k->nama] = $k->nama;
            }
        }

        $daerahNameSet = $daerahList->pluck('namadaerah')->flip()->all();

        return compact('daerahList', 'wilayahByDaerahJson', 'daerahNameSet', 'wilayahPairMap');
    }

    /**
     * @return array{
     *     daerahList: Collection<int, object{id_daerah:int|string|null, namadaerah:string}>,
     *     wilayahByDaerahJson: array<string, array<int, string>>,
     *     daerahNameSet: array<string, int>,
     *     wilayahPairMap: array<string, string>
     * }
     */
    protected static function wilayahDaerahBundleDariMaster(): array
    {
        $provinsi = DB::table('master_provinsi')->orderBy('nama')->get(['id', 'nama']);

        $daerahList = $provinsi->map(fn ($p) => (object) [
            'id_daerah' => $p->id,
            'namadaerah' => $p->nama,
        ]);

        $wilayahByDaerahJson = [];
        $wilayahPairMap = [];

        foreach ($provinsi as $p) {
            $names = DB::table('master_kabupaten_kota')
                ->where('master_provinsi_id', $p->id)
                ->orderBy('nama')
                ->pluck('nama')
                ->all();

            $wilayahByDaerahJson[$p->nama] = array_values($names);

            foreach ($names as $w) {
                $wilayahPairMap[$p->nama.'|'.$w] = $w;
            }
        }

        $daerahNameSet = $daerahList->pluck('namadaerah')->flip()->all();

        return compact('daerahList', 'wilayahByDaerahJson', 'daerahNameSet', 'wilayahPairMap');
    }

    /**
     * @return array{
     *     daerahList: Collection<int, object{id_daerah:int|string|null, namadaerah:string}>,
     *     wilayahByDaerahJson: array<string, array<int, string>>,
     *     daerahNameSet: array<string, int>,
     *     wilayahPairMap: array<string, string>
     * }
     */
    protected static function wilayahDaerahBundleLegacy(): array
    {
        if (! Schema::hasTable('daerah')) {
            return [
                'daerahList' => collect(),
                'wilayahByDaerahJson' => [],
                'daerahNameSet' => [],
                'wilayahPairMap' => [],
            ];
        }

        $daerahList = DB::table('daerah')->orderBy('namadaerah')->get(['id_daerah', 'namadaerah']);
        $daerahNameSet = $daerahList->pluck('namadaerah')->flip()->all();

        [$wilayahPairMap, $wilayahByDaerahJson] = TerritoryMaster::wilayahPairMapAndWilayahGroupedByNamadaerah();

        return compact('daerahList', 'wilayahByDaerahJson', 'daerahNameSet', 'wilayahPairMap');
    }

    /**
     * Master induk/unit kerja kepegawaian: tabel `org` / `suborg`.
     *
     * @return array{orgs: Collection<int, object>, suborgs: Collection<int, object>, hasMaster: bool}
     */
    public static function organisasiFormBundle(?Usm $user = null): array
    {
        if (! Schema::hasTable('org') || ! Schema::hasTable('suborg')) {
            return ['orgs' => collect(), 'suborgs' => collect(), 'hasMaster' => false];
        }

        $orgs = DB::table('org')->orderBy('nameorg')->get();
        $suborgs = DB::table('suborg as s')
            ->join('org as o', 'o.id_org', '=', 's.id_org')
            ->select('s.namesuborg', 'o.nameorg')
            ->orderBy('o.nameorg')
            ->orderBy('s.namesuborg')
            ->get();

        return [
            'orgs' => $orgs,
            'suborgs' => $suborgs,
            'hasMaster' => $orgs->isNotEmpty(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public static function daerahWilayahValidationRules(): array
    {
        if (static::wilayahPakaiMasterTerritori()) {
            return [
                'daerah' => ['nullable', 'string', 'max:255', Rule::exists('master_territori', 'nama')->where('level', 'provinsi')],
                'wilayah' => [
                    'nullable',
                    'string',
                    'max:255',
                    Rule::exists('master_territori', 'nama')->where(function ($query): void {
                        $namaProv = Request::input('daerah');
                        if ($namaProv === null || $namaProv === '') {
                            $query->whereRaw('1 = 0');

                            return;
                        }
                        $parentIds = DB::table('master_territori')
                            ->where('level', 'provinsi')
                            ->where('nama', $namaProv)
                            ->pluck('id')
                            ->all();
                        $query
                            ->whereIn('parent_id', $parentIds !== [] ? $parentIds : [-1])
                            ->whereIn('level', ['kabupaten', 'kota']);
                    }),
                ],
            ];
        }

        if (static::wilayahPakaiTabelMaster()) {
            return [
                'daerah' => ['nullable', 'string', 'max:255', Rule::exists('master_provinsi', 'nama')],
                'wilayah' => [
                    'nullable',
                    'string',
                    'max:255',
                    Rule::exists('master_kabupaten_kota', 'nama')->where(function ($query): void {
                        $namaProv = Request::input('daerah');
                        if ($namaProv === null || $namaProv === '') {
                            $query->whereRaw('1 = 0');

                            return;
                        }
                        $ids = DB::table('master_provinsi')->where('nama', $namaProv)->pluck('id')->all();
                        $query->whereIn('master_provinsi_id', $ids !== [] ? $ids : [-1]);
                    }),
                ],
            ];
        }

        return [
            'daerah' => ['nullable', 'string', 'max:255', Rule::exists('daerah', 'namadaerah')],
            'wilayah' => [
                'nullable',
                'string',
                'max:255',
                Rule::exists('wilayah', 'namawilayah')->where(function ($q) {
                    return $q->where('id_daerah', Request::input('daerah'));
                }),
            ],
        ];
    }

    /**
     * Admin & oprator wajib punya provinsi (`daerah`). Kab/kota (`wilayah`) opsional:
     * kosong = cakupan tingkat provinsi (melihat seluruh kab/kota di provinsi itu);
     * terisi = cakupan satu kab/kota (admin wilayah).
     *
     * @param  int|null  $matrixRoleId  Nilai `roles.role_id` (tier matriks 1–7).
     * @param  bool  $labelMasterKl  true saat form memakai master KL daerah/resort (bukan provinsi/kab-kota).
     */
    public static function assertTerritorialStaffRoleHasWilayahKerja(?int $matrixRoleId, ?string $daerah, ?string $wilayah, bool $labelMasterKl = false): void
    {
        $tiers = config('role_menu_matrix.territorial_staff_roles');
        if (! is_array($tiers)) {
            $tiers = [2, 3];
        }
        $mid = (int) ($matrixRoleId ?? 0);
        if (! in_array($mid, $tiers, true)) {
            return;
        }

        $d = trim((string) ($daerah ?? ''));
        if ($d !== '') {
            return;
        }

        $msg = $labelMasterKl
            ? 'Untuk role administrator atau oprator, daerah (KL) kerja wajib diisi. Isi resort bila cakupan Anda satu satuan (boleh dikosongkan untuk cakupan seluruh daerah KL).'
            : 'Untuk role administrator atau oprator, provinsi kerja wajib diisi. Isi kab/kota bila cakupan Anda satu wilayah (boleh dikosongkan untuk cakupan seluruh provinsi).';

        throw ValidationException::withMessages([
            'daerah' => [$msg],
        ]);
    }

    /**
     * Registrasi mandiri: untuk role teritorial, provinsi **dan** kab/kota wajib diisi agar akun pending
     * tercatat di Daftar User administrator tingkat wilayah (filter teritori sama dengan pengguna).
     *
     * Berbeda dari profil admin internal yang boleh hanya provinsi ({@see assertTerritorialStaffRoleHasWilayahKerja}).
     *
     * @param  bool  $labelMasterKl  true jika pasangan field mengikuti master KL daerah/resort.
     */
    public static function assertTerritorialStaffSelfRegistrationHasKabupaten(?int $matrixRoleId, ?string $daerah, ?string $wilayah, bool $labelMasterKl = false): void
    {
        $tiers = config('role_menu_matrix.territorial_staff_roles');
        if (! is_array($tiers)) {
            $tiers = [2, 3];
        }
        $mid = (int) ($matrixRoleId ?? 0);
        if (! in_array($mid, $tiers, true)) {
            return;
        }

        $d = trim((string) ($daerah ?? ''));
        $w = trim((string) ($wilayah ?? ''));

        if ($d !== '' && $w !== '') {
            return;
        }

        if ($labelMasterKl) {
            throw ValidationException::withMessages([
                'daerah' => ['Pilih daerah (KL) dan resort. Data ini memastikan administrator di wilayah Anda melihat akun Anda di menu Daftar User untuk diverifikasi (status Pending).'],
                'wilayah' => ['Pilih resort — wajib untuk registrasi mandiri agar akun masuk daftar verifikasi wilayah kerja.'],
            ]);
        }

        throw ValidationException::withMessages([
            'daerah' => ['Pilih provinsi dan kab/kota. Data ini memastikan administrator di wilayah Anda melihat akun Anda di menu Daftar User untuk diverifikasi (status Pending).'],
            'wilayah' => ['Pilih kab/kota — wajib untuk registrasi mandiri agar akun masuk daftar verifikasi wilayah kerja.'],
        ]);
    }

    public static function indukPemerintahanSelectAktif(): bool
    {
        return Schema::hasTable('org') && DB::table('org')->exists();
    }

    public static function unitKerjaSelectAktif(): bool
    {
        return Schema::hasTable('org')
            && Schema::hasTable('suborg')
            && DB::table('org')->exists()
            && DB::table('suborg')->exists();
    }

    public static function validasiUnitKerjaTerhadapInduk(?string $induk, ?string $unitKerja): void
    {
        if (! static::unitKerjaSelectAktif()) {
            return;
        }

        $unitKerja = trim((string) $unitKerja);
        $induk = trim((string) $induk);
        if ($unitKerja === '') {
            return;
        }

        if ($induk === '') {
            throw ValidationException::withMessages([
                'unit_kerja' => ['Pilih induk organisasi terlebih dahulu.'],
            ]);
        }

        $pairOk = DB::table('suborg as s')
            ->join('org as o', 'o.id_org', '=', 's.id_org')
            ->where('o.nameorg', $induk)
            ->where('s.namesuborg', $unitKerja)
            ->exists();

        if (! $pairOk) {
            throw ValidationException::withMessages([
                'unit_kerja' => ['Unit kerja tidak sesuai dengan induk organisasi yang dipilih.'],
            ]);
        }
    }

    /** Validasi organisasi pusat (`master_kl_daerah`) terhadap induk (`master_kl_pusat`). */
    public static function validasiOrganisasiDaerahTerhadapIndukKl(?string $indukPusat, ?string $orgPusat): void
    {
        if (! static::masterKlTigaTingkatAktif()) {
            return;
        }

        $orgPusat = trim((string) $orgPusat);
        $indukPusat = trim((string) $indukPusat);
        if ($orgPusat === '') {
            return;
        }

        if ($indukPusat === '') {
            throw ValidationException::withMessages([
                'pemerintahan_daerah' => ['Pilih induk organisasi terlebih dahulu.'],
            ]);
        }

        $daerahIds = static::masterKlDaerahIdsForOrgAndInduk($indukPusat, $orgPusat);
        if ($daerahIds === []) {
            throw ValidationException::withMessages([
                'pemerintahan_daerah' => ['Organisasi pusat tidak sesuai dengan induk organisasi yang dipilih.'],
            ]);
        }
    }

    /** Validasi organisasi daerah (`master_kl_resort`) terhadap organisasi pusat dan induk. */
    public static function validasiOrganisasiDaerahResortTerhadapPusatKl(
        ?string $indukPusat,
        ?string $orgPusat,
        ?string $organisasiDaerah
    ): void {
        if (! static::masterKlEmpatTingkatAktif()) {
            return;
        }

        $organisasiDaerah = trim((string) $organisasiDaerah);
        $orgPusat = trim((string) $orgPusat);
        $indukPusat = trim((string) $indukPusat);
        if ($organisasiDaerah === '') {
            return;
        }

        if ($orgPusat === '') {
            throw ValidationException::withMessages([
                'organisasi_daerah' => ['Pilih organisasi pusat terlebih dahulu.'],
            ]);
        }

        if (! static::masterKlResortExistsForHierarchy($indukPusat, $orgPusat, $organisasiDaerah)) {
            throw ValidationException::withMessages([
                'organisasi_daerah' => ['Organisasi daerah tidak sesuai dengan organisasi pusat yang dipilih.'],
            ]);
        }
    }

    /** Validasi unit kerja terhadap hierarki master KL (3 atau 4 tingkat). */
    public static function validasiUnitKerjaTerhadapOrganisasiDaerahKl(
        ?string $indukPusat,
        ?string $orgPusat,
        ?string $organisasiDaerahOrUnit,
        ?string $unitKerja = null
    ): void {
        if (! static::masterKlTigaTingkatAktif()) {
            return;
        }

        if (static::masterKlEmpatTingkatAktif()) {
            $unitKerja = trim((string) $unitKerja);
            $organisasiDaerah = trim((string) $organisasiDaerahOrUnit);
            $orgPusat = trim((string) $orgPusat);
            if ($unitKerja === '') {
                return;
            }

            if ($organisasiDaerah === '') {
                throw ValidationException::withMessages([
                    'unit_kerja' => ['Pilih organisasi daerah terlebih dahulu.'],
                ]);
            }

            if (! static::masterKlUnitKerjaExistsForHierarchy($indukPusat, $orgPusat, $organisasiDaerah, $unitKerja)) {
                throw ValidationException::withMessages([
                    'unit_kerja' => ['Unit kerja tidak sesuai dengan organisasi daerah yang dipilih.'],
                ]);
            }

            return;
        }

        $unitKerja = trim((string) $organisasiDaerahOrUnit);
        $orgPusat = trim((string) $orgPusat);
        $indukPusat = trim((string) $indukPusat);
        if ($unitKerja === '') {
            return;
        }

        if ($orgPusat === '') {
            throw ValidationException::withMessages([
                'unit_kerja' => ['Pilih organisasi pusat terlebih dahulu.'],
            ]);
        }

        if (! static::masterKlResortExistsForHierarchy($indukPusat, $orgPusat, $unitKerja)) {
            throw ValidationException::withMessages([
                'unit_kerja' => ['Unit kerja tidak sesuai dengan organisasi pusat yang dipilih.'],
            ]);
        }
    }

    /** Validasi berantai kepegawaian master KL (pusat → daerah → resort → unit kerja). */
    public static function validasiKepegawaianMasterKl(
        ?string $indukPusat,
        ?string $orgPusat,
        ?string $organisasiDaerah = '',
        ?string $unitKerja = ''
    ): void {
        if (! static::masterKlTigaTingkatAktif()) {
            return;
        }

        static::validasiOrganisasiDaerahTerhadapIndukKl($indukPusat, $orgPusat);

        if (static::masterKlEmpatTingkatAktif()) {
            static::validasiOrganisasiDaerahResortTerhadapPusatKl($indukPusat, $orgPusat, $organisasiDaerah);
            static::validasiUnitKerjaTerhadapOrganisasiDaerahKl($indukPusat, $orgPusat, $organisasiDaerah, $unitKerja);
        } else {
            static::validasiUnitKerjaTerhadapOrganisasiDaerahKl($indukPusat, $orgPusat, $unitKerja);
        }
    }

    /** @deprecated Gunakan {@see validasiOrganisasiDaerahTerhadapIndukKl} */
    public static function validasiPemerintahanDaerahResortTerhadapIndukKl(?string $induk, ?string $pemerintahanDaerah): void
    {
        static::validasiOrganisasiDaerahTerhadapIndukKl($induk, $pemerintahanDaerah);
    }

    /**
     * @return array<string, mixed>
     */
    public static function kepegawaianValidationRules(): array
    {
        $indukRules = ['nullable', 'string', 'max:255'];
        if (static::indukPemerintahanSelectAktif()) {
            $indukRules[] = Rule::exists('org', 'nameorg');
        }

        return [
            'nrp_nip_nik' => ['nullable', 'string', 'max:191'],
            'induk_pemerintahan' => $indukRules,
            'pemerintahan_daerah' => ['nullable', 'string', 'max:255'],
            'organisasi_daerah' => ['nullable', 'string', 'max:255'],
            'unit_kerja' => ['nullable', 'string', 'max:255'],
            'kategori_user' => ['nullable', 'in:organik,non_organik'],
        ];
    }

    /** Aturan kepegawaian untuk POST `/register` bila memakai master KL. */
    public static function registrationKepegawaianValidationRules(): array
    {
        if (! static::masterKlTigaTingkatAktif()) {
            return static::kepegawaianValidationRules();
        }

        $empatTingkat = static::masterKlEmpatTingkatAktif();

        $rules = [
            'nrp_nip_nik' => ['nullable', 'string', 'max:191'],
            'induk_pemerintahan' => [
                'nullable',
                'string',
                'max:255',
                function (string $attribute, mixed $value, \Closure $fail): void {
                    $value = trim((string) $value);
                    if ($value === '') {
                        return;
                    }
                    if (static::masterKlPusatIdsForInduk($value) === []) {
                        $fail('Induk organisasi tidak valid.');
                    }
                },
            ],
            'pemerintahan_daerah' => [
                'nullable',
                'string',
                'max:255',
                Rule::exists('master_kl_daerah', 'nama')->where(function ($query): void {
                    $induk = trim((string) Request::input('induk_pemerintahan', ''));
                    if ($induk === '') {
                        $query->whereRaw('1 = 0');

                        return;
                    }
                    $ids = static::masterKlDaerahIdsForOrgAndInduk($induk, (string) Request::input('pemerintahan_daerah', ''));
                    $query->whereIn('id', $ids !== [] ? $ids : [-1]);
                }),
            ],
            'kategori_user' => ['nullable', 'in:organik,non_organik'],
        ];

        if ($empatTingkat) {
            $rules['organisasi_daerah'] = [
                'nullable',
                'string',
                'max:255',
                Rule::exists('master_kl_resort', 'nama')->where(function ($query): void {
                    $orgPusat = trim((string) Request::input('pemerintahan_daerah', ''));
                    if ($orgPusat === '') {
                        $query->whereRaw('1 = 0');

                        return;
                    }
                    $ids = static::masterKlDaerahIdsForOrgAndInduk(
                        (string) Request::input('induk_pemerintahan', ''),
                        $orgPusat
                    );
                    $query->whereIn('parent_id', $ids !== [] ? $ids : [-1]);
                }),
            ];
            $rules['unit_kerja'] = [
                'nullable',
                'string',
                'max:255',
                Rule::exists('master_kl_unit_kerja', 'nama')->where(function ($query): void {
                    $organisasiDaerah = trim((string) Request::input('organisasi_daerah', ''));
                    if ($organisasiDaerah === '') {
                        $query->whereRaw('1 = 0');

                        return;
                    }
                    $resortIds = static::masterKlResortIdsForHierarchy(
                        (string) Request::input('induk_pemerintahan', ''),
                        (string) Request::input('pemerintahan_daerah', ''),
                        $organisasiDaerah
                    );
                    $query->whereIn('parent_id', $resortIds !== [] ? $resortIds : [-1]);
                }),
            ];
        } else {
            $rules['unit_kerja'] = [
                'nullable',
                'string',
                'max:255',
                Rule::exists('master_kl_resort', 'nama')->where(function ($query): void {
                    $orgPusat = trim((string) Request::input('pemerintahan_daerah', ''));
                    if ($orgPusat === '') {
                        $query->whereRaw('1 = 0');

                        return;
                    }
                    $ids = static::masterKlDaerahIdsForOrgAndInduk(
                        (string) Request::input('induk_pemerintahan', ''),
                        $orgPusat
                    );
                    $query->whereIn('parent_id', $ids !== [] ? $ids : [-1]);
                }),
            ];
        }

        return $rules;
    }

    /**
     * Form kepegawaian master KL: pusat → daerah → resort → unit kerja (4 tingkat bila tabel unit kerja ada).
     *
     * @return array<string, mixed>
     */
    public static function registrationKepegawaianFormBundle(?Usm $user = null): array
    {
        if (! static::masterKlTigaTingkatAktif()) {
            return array_merge(static::kepegawaianFormBundle($user), [
                'pemerintahanDaerahUseSelect' => false,
                'pemerintahanDaerahResorts' => collect(),
                'organisasiDaerahUseSelect' => false,
                'organisasiDaerahList' => collect(),
                'filterKepegawaianIndukPemerintahanDaerah' => false,
                'filterKepegawaianOrganisasiDaerahUnit' => false,
                'filterKepegawaianOrganisasiPusatDaerah' => false,
                'kepegawaianKlTigaTingkat' => false,
                'kepegawaianKlEmpatTingkat' => false,
            ]);
        }

        $empatTingkat = static::masterKlEmpatTingkatAktif();
        $pusatRows = static::masterKlPusatIndukCollection();
        $orgsInduk = $pusatRows->map(fn ($r) => (object) [
            'id_org' => (int) $r->id,
            'nameorg' => (string) $r->nama,
        ]);

        $orgPusatList = DB::table('master_kl_daerah as d')
            ->join('master_kl_pusat as p', 'p.id', '=', 'd.parent_id')
            ->orderBy('p.nama')
            ->orderBy('d.nama')
            ->get(['d.id', 'd.nama as nama', 'p.nama as nameorg']);

        $organisasiDaerahList = collect();
        $unitKerjaList = collect();

        if ($empatTingkat && Schema::hasTable('master_kl_resort')) {
            $organisasiDaerahList = DB::table('master_kl_resort as r')
                ->join('master_kl_daerah as d', 'd.id', '=', 'r.parent_id')
                ->join('master_kl_pusat as p', 'p.id', '=', 'd.parent_id')
                ->orderBy('p.nama')
                ->orderBy('d.nama')
                ->orderBy('r.nama')
                ->get(['r.nama as nama', 'd.nama as org_pusat', 'p.nama as induk_pusat']);
        }

        if ($empatTingkat && Schema::hasTable('master_kl_unit_kerja')) {
            $unitKerjaList = DB::table('master_kl_unit_kerja as u')
                ->join('master_kl_resort as r', 'r.id', '=', 'u.parent_id')
                ->join('master_kl_daerah as d', 'd.id', '=', 'r.parent_id')
                ->join('master_kl_pusat as p', 'p.id', '=', 'd.parent_id')
                ->orderBy('p.nama')
                ->orderBy('d.nama')
                ->orderBy('r.nama')
                ->orderBy('u.nama')
                ->get([
                    'u.nama as namesuborg',
                    'r.nama as org_daerah',
                    'd.nama as org_pusat',
                    'p.nama as induk_pusat',
                ]);
        } elseif (Schema::hasTable('master_kl_resort')) {
            $unitKerjaList = DB::table('master_kl_resort as r')
                ->join('master_kl_daerah as d', 'd.id', '=', 'r.parent_id')
                ->join('master_kl_pusat as p', 'p.id', '=', 'd.parent_id')
                ->orderBy('p.nama')
                ->orderBy('d.nama')
                ->orderBy('r.nama')
                ->get(['r.nama as namesuborg', 'd.nama as nameorg', 'p.nama as induk_pusat']);
        }

        if ($user && trim((string) ($user->induk_pemerintahan ?? '')) !== ''
            && $orgsInduk->isNotEmpty()
            && ! $orgsInduk->pluck('nameorg')->contains($user->induk_pemerintahan)) {
            $orgsInduk = $orgsInduk->prepend((object) ['id_org' => null, 'nameorg' => $user->induk_pemerintahan]);
        }

        if ($user && trim((string) ($user->pemerintahan_daerah ?? '')) !== ''
            && $orgPusatList->isNotEmpty()) {
            $pair = $orgPusatList->first(fn ($row) => $row->nama === $user->pemerintahan_daerah
                && static::indukOrganisasiNamesMatch((string) ($user->induk_pemerintahan ?? ''), (string) ($row->nameorg ?? '')));
            if ($pair === null && trim((string) ($user->induk_pemerintahan ?? '')) !== '') {
                $orgPusatList = $orgPusatList->prepend((object) [
                    'id' => 0,
                    'nama' => $user->pemerintahan_daerah,
                    'nameorg' => $user->induk_pemerintahan,
                ]);
            }
        }

        if ($empatTingkat && $user && trim((string) ($user->organisasi_daerah ?? '')) !== ''
            && trim((string) ($user->pemerintahan_daerah ?? '')) !== ''
            && $organisasiDaerahList->isNotEmpty()) {
            $pairOrgDaerah = $organisasiDaerahList->first(fn ($row) => $row->nama === $user->organisasi_daerah
                && strcasecmp((string) ($row->org_pusat ?? ''), (string) ($user->pemerintahan_daerah ?? '')) === 0
                && static::indukOrganisasiNamesMatch((string) ($user->induk_pemerintahan ?? ''), (string) ($row->induk_pusat ?? '')));
            if ($pairOrgDaerah === null) {
                $organisasiDaerahList = $organisasiDaerahList->prepend((object) [
                    'nama' => $user->organisasi_daerah,
                    'org_pusat' => $user->pemerintahan_daerah,
                    'induk_pusat' => $user->induk_pemerintahan,
                ]);
            }
        }

        if ($user && trim((string) ($user->unit_kerja ?? '')) !== '' && $unitKerjaList->isNotEmpty()) {
            if ($empatTingkat) {
                $pairUnit = $unitKerjaList->first(fn ($row) => $row->namesuborg === $user->unit_kerja
                    && strcasecmp((string) ($row->org_daerah ?? ''), (string) ($user->organisasi_daerah ?? '')) === 0
                    && strcasecmp((string) ($row->org_pusat ?? ''), (string) ($user->pemerintahan_daerah ?? '')) === 0);
                if ($pairUnit === null) {
                    $unitKerjaList = $unitKerjaList->prepend((object) [
                        'namesuborg' => $user->unit_kerja,
                        'org_daerah' => $user->organisasi_daerah,
                        'org_pusat' => $user->pemerintahan_daerah,
                        'induk_pusat' => $user->induk_pemerintahan,
                    ]);
                }
            } else {
                $pairUnit = $unitKerjaList->first(fn ($row) => $row->namesuborg === $user->unit_kerja
                    && $row->nameorg === $user->pemerintahan_daerah);
                if ($pairUnit === null) {
                    $unitKerjaList = $unitKerjaList->prepend((object) [
                        'namesuborg' => $user->unit_kerja,
                        'nameorg' => $user->pemerintahan_daerah,
                        'induk_pusat' => $user->induk_pemerintahan,
                    ]);
                }
            }
        }

        $orgPusatSelect = $orgPusatList->isNotEmpty();
        $organisasiDaerahSelect = $organisasiDaerahList->isNotEmpty();
        $unitSelect = $unitKerjaList->isNotEmpty();

        return [
            'indukPemerintahanUseSelect' => $orgsInduk->isNotEmpty(),
            'orgsInduk' => $orgsInduk,
            'unitKerjaUseSelect' => $unitSelect,
            'suborgsUnitKerja' => $unitKerjaList,
            'pemerintahanDaerahUseSelect' => $orgPusatSelect,
            'pemerintahanDaerahResorts' => $orgPusatList,
            'organisasiDaerahUseSelect' => $organisasiDaerahSelect,
            'organisasiDaerahList' => $organisasiDaerahList,
            'filterKepegawaianIndukPemerintahanDaerah' => $orgsInduk->isNotEmpty() && $orgPusatSelect,
            'filterKepegawaianOrganisasiPusatDaerah' => $orgPusatSelect && $organisasiDaerahSelect,
            'filterKepegawaianOrganisasiDaerahUnit' => ($empatTingkat ? $organisasiDaerahSelect : $orgPusatSelect) && $unitSelect,
            'kepegawaianKlTigaTingkat' => true,
            'kepegawaianKlEmpatTingkat' => $empatTingkat,
            'kepegawaianHierarchyJson' => static::kepegawaianMasterKlHierarchyJson(),
        ];
    }

    /**
     * @return array{
     *     indukPemerintahanUseSelect: bool,
     *     orgsInduk: \Illuminate\Support\Collection,
     *     unitKerjaUseSelect: bool,
     *     suborgsUnitKerja: \Illuminate\Support\Collection,
     * }
     */
    public static function kepegawaianFormBundle(?Usm $user = null): array
    {
        $orgBundle = static::organisasiFormBundle($user);
        $orgsInduk = $orgBundle['orgs'];
        $suborgsUnit = $orgBundle['suborgs'];

        if ($user && trim((string) ($user->induk_pemerintahan ?? '')) !== ''
            && $orgsInduk->isNotEmpty()
            && ! $orgsInduk->pluck('nameorg')->contains($user->induk_pemerintahan)) {
            $orgsInduk = $orgsInduk->prepend((object) ['id_org' => null, 'nameorg' => $user->induk_pemerintahan]);
        }

        if ($user
            && trim((string) ($user->unit_kerja ?? '')) !== ''
            && trim((string) ($user->induk_pemerintahan ?? '')) !== ''
            && $suborgsUnit->isNotEmpty()) {
            $pair = $suborgsUnit->first(fn ($s) => $s->namesuborg === $user->unit_kerja && $s->nameorg === $user->induk_pemerintahan);
            if ($pair === null) {
                $suborgsUnit = $suborgsUnit->prepend((object) [
                    'namesuborg' => $user->unit_kerja,
                    'nameorg' => $user->induk_pemerintahan,
                ]);
            }
        }

        return [
            'indukPemerintahanUseSelect' => static::indukPemerintahanSelectAktif(),
            'orgsInduk' => $orgsInduk,
            'unitKerjaUseSelect' => static::unitKerjaSelectAktif(),
            'suborgsUnitKerja' => $suborgsUnit,
        ];
    }
}
