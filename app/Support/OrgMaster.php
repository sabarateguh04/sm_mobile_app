<?php

namespace App\Support;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Organisasi induk (`org`) dan sub-organisasi:
 * - Utama: `master_kl_daerah` / `master_kl_pusat` → `master_kl_resort`.
 * - Fallback: tabel legacy `suborg` bila tidak ada pasangan KL.
 */
class OrgMaster
{
    /** @var ?bool */
    protected static ?bool $numericSuborgOrgFk = null;

    /**
     * Kelompok nama yang diperlakukan setara saat mapping ke KL Daerah.
     *
     * @return list<list<string>>
     */
    protected static function klDaerahLabelAliasGroups(): array
    {
        return [
            ['ADMINISTRASI DAN OPERASIONAL', 'ADMINISTRASI & OPERASIONAL', 'KL DAERAH'],
        ];
    }

    protected static function normalizeLabel(string $label): string
    {
        $label = preg_replace('/\s+/u', ' ', trim($label)) ?? trim($label);

        return mb_strtoupper($label, 'UTF-8');
    }

    protected static function canonicalLabelKey(string $label): string
    {
        $label = mb_strtolower(static::normalizeLabel($label), 'UTF-8');
        $label = str_replace('&', ' dan ', $label);
        $label = preg_replace('/[^a-z0-9\s]+/u', ' ', $label) ?? $label;
        $label = preg_replace('/\s+/u', ' ', trim($label)) ?? trim($label);

        return $label;
    }

    /**
     * @return list<string>
     */
    protected static function equivalentKlDaerahLabels(string $label): array
    {
        $normalized = static::normalizeLabel($label);
        if ($normalized === '') {
            return [];
        }

        $labels = [$normalized];
        $canonical = static::canonicalLabelKey($normalized);
        if ($canonical === '') {
            return $labels;
        }

        foreach (static::klDaerahLabelAliasGroups() as $group) {
            $groupCanonicals = array_values(array_filter(array_map(
                static fn (string $item): string => static::canonicalLabelKey($item),
                $group
            )));

            $related = false;
            foreach ($groupCanonicals as $groupCanonical) {
                if ($groupCanonical === $canonical
                    || str_contains($canonical, $groupCanonical)
                    || str_contains($groupCanonical, $canonical)) {
                    $related = true;
                    break;
                }
            }

            if (! $related) {
                continue;
            }

            foreach ($group as $alias) {
                $aliasNormalized = static::normalizeLabel($alias);
                if ($aliasNormalized !== '') {
                    $labels[] = $aliasNormalized;
                }
            }
        }

        return array_values(array_unique($labels));
    }

    /**
     * Label prioritas agar satu kelompok memakai nama canonical yang sama.
     *
     * @return list<string>
     */
    protected static function preferredKlDaerahLabelsFor(string $label): array
    {
        $normalized = static::normalizeLabel($label);
        $canonical = static::canonicalLabelKey($normalized);
        if ($canonical === '') {
            return [];
        }

        foreach (static::klDaerahLabelAliasGroups() as $group) {
            $groupCanonicals = array_values(array_filter(array_map(
                static fn (string $item): string => static::canonicalLabelKey($item),
                $group
            )));

            $related = false;
            foreach ($groupCanonicals as $groupCanonical) {
                if ($groupCanonical === $canonical
                    || str_contains($canonical, $groupCanonical)
                    || str_contains($groupCanonical, $canonical)) {
                    $related = true;
                    break;
                }
            }

            if (! $related) {
                continue;
            }

            // Pakai label canonical pertama dalam group sebagai titik temu tunggal.
            return [static::normalizeLabel($group[0])];
        }

        return [];
    }

    public static function usesKlResortAsSuborgSource(): bool
    {
        return Schema::hasTable('master_kl_resort')
            && Schema::hasTable('master_kl_daerah');
    }

    /**
     * @param  object{nameorg?: string, singkatan?: ?string}|null  $org
     * @return array{daerah_ids: list<int>, pusat_ids: list<int>, matched_via: ?string}
     */
    public static function resolveKlScopeForOrg(?object $org): array
    {
        $empty = ['daerah_ids' => [], 'pusat_ids' => [], 'matched_via' => null];

        if ($org === null || ! static::usesKlResortAsSuborgSource()) {
            return $empty;
        }

        $nameorg = trim((string) ($org->nameorg ?? ''));
        $singkatan = trim((string) ($org->singkatan ?? ''));
        $baseLabels = array_values(array_unique(array_filter([$nameorg, $singkatan])));
        $labels = [];
        foreach ($baseLabels as $baseLabel) {
            $labels = array_merge($labels, static::equivalentKlDaerahLabels($baseLabel));
        }
        $labels = array_values(array_unique(array_filter($labels)));

        if ($labels === []) {
            return $empty;
        }

        $daerahIds = [];
        $preferredLabels = [];
        foreach ($baseLabels as $baseLabel) {
            $preferredLabels = array_merge($preferredLabels, static::preferredKlDaerahLabelsFor($baseLabel));
        }
        $preferredLabels = array_values(array_unique(array_filter($preferredLabels)));

        foreach ($preferredLabels as $preferredLabel) {
            $found = DB::table('master_kl_daerah')
                ->whereRaw('LOWER(TRIM(nama)) = ?', [mb_strtolower($preferredLabel, 'UTF-8')])
                ->pluck('id')
                ->all();
            $daerahIds = array_merge($daerahIds, $found);
        }

        if ($daerahIds !== []) {
            return [
                'daerah_ids' => static::intIds($daerahIds),
                'pusat_ids' => [],
                'matched_via' => 'kl_daerah_group_canonical',
            ];
        }

        foreach ($labels as $label) {
            $found = DB::table('master_kl_daerah')
                ->whereRaw('LOWER(TRIM(nama)) = ?', [mb_strtolower($label)])
                ->pluck('id')
                ->all();
            $daerahIds = array_merge($daerahIds, $found);
        }

        if ($daerahIds !== []) {
            return [
                'daerah_ids' => static::intIds($daerahIds),
                'pusat_ids' => [],
                'matched_via' => 'kl_daerah_nama',
            ];
        }

        if (Schema::hasTable('master_kl_pusat')) {
            $pusatIds = [];
            foreach ($labels as $label) {
                $found = DB::table('master_kl_pusat')
                    ->whereRaw('LOWER(TRIM(nama)) = ?', [mb_strtolower($label)])
                    ->pluck('id')
                    ->all();
                $pusatIds = array_merge($pusatIds, $found);
            }
            $pusatIds = static::intIds($pusatIds);

            if ($pusatIds !== []) {
                $daerahIds = DB::table('master_kl_daerah')
                    ->whereIn('parent_id', $pusatIds)
                    ->pluck('id')
                    ->all();

                if ($daerahIds !== []) {
                    return [
                        'daerah_ids' => static::intIds($daerahIds),
                        'pusat_ids' => $pusatIds,
                        'matched_via' => 'kl_pusat_induk',
                    ];
                }
            }
        }

        foreach ($labels as $label) {
            $labelLower = mb_strtolower($label, 'UTF-8');
            $found = DB::table('master_kl_daerah')
                ->where(function ($q) use ($labelLower): void {
                    $q->whereRaw('LOWER(TRIM(nama)) like ?', ['%'.$labelLower.'%'])
                        ->orWhereRaw('? like CONCAT("%", LOWER(TRIM(nama)), "%")', [$labelLower]);
                })
                ->pluck('id')
                ->all();
            $daerahIds = array_merge($daerahIds, $found);
        }

        if ($daerahIds !== []) {
            return [
                'daerah_ids' => static::intIds($daerahIds),
                'pusat_ids' => [],
                'matched_via' => 'kl_daerah_like',
            ];
        }

        return $empty;
    }

    /**
     * @return list<int>
     */
    public static function klDaerahIdsForOrgName(?string $nameorg, ?object $org = null): array
    {
        if ($org !== null) {
            return static::resolveKlScopeForOrg($org)['daerah_ids'];
        }

        $nameorg = trim((string) $nameorg);
        if ($nameorg === '') {
            return [];
        }

        return static::resolveKlScopeForOrg((object) ['nameorg' => $nameorg])['daerah_ids'];
    }

    /**
     * @param  list<int|string>  $ids
     * @return list<int>
     */
    protected static function intIds(array $ids): array
    {
        return array_values(array_unique(array_map(static fn ($id) => (int) $id, $ids)));
    }

    public static function suborgIdOrgColumnIsNumericFk(): bool
    {
        if (static::$numericSuborgOrgFk !== null) {
            return static::$numericSuborgOrgFk;
        }

        if (! Schema::hasTable('suborg')) {
            static::$numericSuborgOrgFk = false;

            return static::$numericSuborgOrgFk;
        }

        try {
            $type = strtolower((string) Schema::getColumnType('suborg', 'id_org'));
        } catch (\Throwable) {
            static::$numericSuborgOrgFk = false;

            return static::$numericSuborgOrgFk;
        }

        static::$numericSuborgOrgFk = in_array($type, [
            'bigint', 'biginteger', 'integer', 'int', 'mediumint', 'smallint', 'tinyint',
        ], true);

        return static::$numericSuborgOrgFk;
    }

    public static function suborgIdOrgValueForOrgPrimaryKey($orgPrimaryKey): mixed
    {
        if (static::suborgIdOrgColumnIsNumericFk()) {
            return $orgPrimaryKey;
        }

        if (! Schema::hasTable('org')) {
            return $orgPrimaryKey;
        }

        $name = DB::table('org')->where('id_org', $orgPrimaryKey)->value('nameorg');

        return $name ?? $orgPrimaryKey;
    }

    public static function klResortQueryForOrg(?object $org): \Illuminate\Database\Query\Builder
    {
        $daerahIds = static::resolveKlScopeForOrg($org)['daerah_ids'];
        if ($daerahIds === []) {
            return DB::table('master_kl_resort as r')->whereRaw('1 = 0');
        }

        $q = DB::table('master_kl_resort as r')
            ->join('master_kl_daerah as d', 'd.id', '=', 'r.parent_id')
            ->whereIn('r.parent_id', $daerahIds)
            ->select(
                'r.id',
                'r.nama as namesuborg',
                'r.parent_id',
                'd.nama as daerah_nama'
            );

        if (Schema::hasTable('master_kl_pusat')) {
            $q->leftJoin('master_kl_pusat as p', 'p.id', '=', 'd.parent_id')
                ->addSelect('p.nama as pusat_nama');
        }

        return $q;
    }

    public static function legacySuborgQueryForOrgPrimaryKey($orgPrimaryKey): \Illuminate\Database\Query\Builder
    {
        if (! Schema::hasTable('suborg')) {
            return DB::table('suborg as s')->whereRaw('1 = 0');
        }

        return static::suborgQueryWithOrg()
            ->where('s.id_org', static::suborgIdOrgValueForOrgPrimaryKey($orgPrimaryKey));
    }

    public static function suborgQueryWithOrg(): \Illuminate\Database\Query\Builder
    {
        if (! Schema::hasTable('suborg')) {
            return DB::table('suborg as s')->whereRaw('1 = 0');
        }

        if (static::suborgIdOrgColumnIsNumericFk()) {
            return DB::table('suborg as s')
                ->leftJoin('org as o', 'o.id_org', '=', 's.id_org')
                ->select('s.id_suborg as id', 's.namesuborg', 's.id_org', 'o.nameorg');
        }

        return DB::table('suborg as s')
            ->leftJoin('org as o', 'o.nameorg', '=', 's.id_org')
            ->select('s.id_suborg as id', 's.namesuborg', 's.id_org', 'o.nameorg', 'o.id_org as org_id_numeric');
    }

    public static function countKlResortForOrg(?object $org): int
    {
        $daerahIds = static::resolveKlScopeForOrg($org)['daerah_ids'];
        if ($daerahIds === []) {
            return 0;
        }

        return (int) DB::table('master_kl_resort')->whereIn('parent_id', $daerahIds)->count();
    }

    public static function countLegacySuborgForOrgPrimaryKey($orgPrimaryKey): int
    {
        if (! Schema::hasTable('suborg')) {
            return 0;
        }

        return (int) DB::table('suborg')
            ->where('id_org', static::suborgIdOrgValueForOrgPrimaryKey($orgPrimaryKey))
            ->count();
    }

    public static function countSuborgForOrgPrimaryKey($orgPrimaryKey): int
    {
        if (! Schema::hasTable('org')) {
            return 0;
        }

        $org = DB::table('org')->where('id_org', $orgPrimaryKey)->first();
        $orgObj = $org ? (object) (array) $org : null;

        $kl = static::countKlResortForOrg($orgObj);
        if ($kl > 0) {
            return $kl;
        }

        return static::countLegacySuborgForOrgPrimaryKey($orgPrimaryKey);
    }

    /**
     * @param  iterable<int, object{id_org: int|string, nameorg?: string, singkatan?: ?string}>  $orgRows
     * @return array<int|string, int>
     */
    public static function suborgCountsForOrgRows(iterable $orgRows): array
    {
        $rows = collect($orgRows);
        if ($rows->isEmpty()) {
            return [];
        }

        $result = [];
        foreach ($rows as $o) {
            $result[$o->id_org] = 0;
        }

        if (static::usesKlResortAsSuborgSource()) {
            foreach ($rows as $o) {
                $result[$o->id_org] = static::countKlResortForOrg($o);
            }

            $needsLegacy = $rows->filter(fn ($o) => ($result[$o->id_org] ?? 0) === 0);
            if ($needsLegacy->isNotEmpty() && Schema::hasTable('suborg')) {
                if (static::suborgIdOrgColumnIsNumericFk()) {
                    $ids = $needsLegacy->pluck('id_org')->unique()->values()->all();
                    $legacyCounts = DB::table('suborg')
                        ->whereIn('id_org', $ids)
                        ->groupBy('id_org')
                        ->selectRaw('id_org, COUNT(*) as cnt')
                        ->pluck('cnt', 'id_org')
                        ->map(fn ($c) => (int) $c)
                        ->all();
                    foreach ($legacyCounts as $orgId => $cnt) {
                        $result[$orgId] = $cnt;
                    }
                } else {
                    foreach ($needsLegacy as $o) {
                        $fk = static::suborgIdOrgValueForOrgPrimaryKey($o->id_org);
                        $result[$o->id_org] = (int) DB::table('suborg')->where('id_org', $fk)->count();
                    }
                }
            }

            return $result;
        }

        if (! Schema::hasTable('suborg')) {
            return $result;
        }

        foreach ($rows as $o) {
            $result[$o->id_org] = static::countLegacySuborgForOrgPrimaryKey($o->id_org);
        }

        return $result;
    }

    /**
     * @return array{items: Collection, uses_kl: bool, matched_via: ?string, daerah_ids: list<int>}
     */
    public static function paginatedSuborgListForOrg(?object $org, int $perPage, ?string $search = null): array
    {
        $scope = static::resolveKlScopeForOrg($org);
        $daerahIds = $scope['daerah_ids'];

        if ($daerahIds !== []) {
            $q = static::klResortQueryForOrg($org);
            if ($search !== null && $search !== '') {
                $q->where('r.nama', 'like', '%'.$search.'%');
            }

            $paginator = $q->orderBy('r.nama')->paginate($perPage);

            if ($paginator->total() > 0 || ! Schema::hasTable('suborg')) {
                return [
                    'items' => $paginator,
                    'uses_kl' => true,
                    'matched_via' => $scope['matched_via'],
                    'daerah_ids' => $daerahIds,
                ];
            }
        }

        if ($org === null || ! Schema::hasTable('suborg')) {
            $empty = DB::table('suborg as s')->whereRaw('1 = 0');

            return [
                'items' => $empty->paginate($perPage),
                'uses_kl' => false,
                'matched_via' => null,
                'daerah_ids' => [],
            ];
        }

        $q = static::legacySuborgQueryForOrgPrimaryKey($org->id_org);
        if ($search !== null && $search !== '') {
            $q->where('s.namesuborg', 'like', '%'.$search.'%');
        }

        $paginator = $q->orderBy('s.namesuborg')->paginate($perPage);

        return [
            'items' => $paginator,
            'uses_kl' => false,
            'matched_via' => 'legacy_suborg',
            'daerah_ids' => [],
        ];
    }
}
