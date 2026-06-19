<?php

namespace App\Support;

use App\Models\Usm;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Support\Facades\Schema;

/**
 * Scope daftar user/personil menurut kedalaman profil kepegawaian:
 * - Polda (organisasi pusat): lihat seluruh user di bawah Polda tersebut.
 * - Polres / Ditlantas (organisasi daerah): hanya user di organisasi daerah yang sama.
 * - Unit kerja: hanya user di unit kerja yang sama.
 */
final class KepegawaianListingScope
{
    public static function actorHasScope(Usm $actor): bool
    {
        return trim((string) ($actor->induk_pemerintahan ?? '')) !== ''
            || trim((string) ($actor->pemerintahan_daerah ?? '')) !== ''
            || trim((string) ($actor->organisasi_daerah ?? '')) !== ''
            || trim((string) ($actor->unit_kerja ?? '')) !== '';
    }

    /**
     * @param  QueryBuilder|EloquentBuilder  $query
     */
    public static function applyScope(
        QueryBuilder|EloquentBuilder $query,
        string $table,
        Usm $actor,
        bool $includeLegacyRowsWithEmptyInduk = true
    ): void {
        if (! Schema::hasColumn($table, 'induk_pemerintahan')) {
            return;
        }

        $induk = trim((string) ($actor->induk_pemerintahan ?? ''));
        $orgPusat = trim((string) ($actor->pemerintahan_daerah ?? ''));
        $organisasiDaerah = trim((string) ($actor->organisasi_daerah ?? ''));
        $unitKerja = trim((string) ($actor->unit_kerja ?? ''));
        [$organisasiDaerah, $unitKerja] = static::resolveOrganisasiDaerahAndUnit(
            $induk,
            $orgPusat,
            $organisasiDaerah,
            $unitKerja
        );
        $hasOrgDaerahCol = Schema::hasColumn($table, 'organisasi_daerah');

        if ($induk === '' && $orgPusat === '' && $organisasiDaerah === '' && $unitKerja === '') {
            return;
        }

        $query->where(function ($outer) use (
            $table,
            $induk,
            $orgPusat,
            $organisasiDaerah,
            $unitKerja,
            $hasOrgDaerahCol,
            $includeLegacyRowsWithEmptyInduk
        ) {
            $outer->where(function ($kep) use (
                $table,
                $induk,
                $orgPusat,
                $organisasiDaerah,
                $unitKerja,
                $hasOrgDaerahCol
            ) {
                if ($unitKerja !== '') {
                    static::applyUnitKerjaScope($kep, $table, $induk, $orgPusat, $organisasiDaerah, $unitKerja, $hasOrgDaerahCol);

                    return;
                }

                if ($organisasiDaerah !== '' && $hasOrgDaerahCol) {
                    static::applyOrganisasiDaerahScope($kep, $table, $induk, $orgPusat, $organisasiDaerah);

                    return;
                }

                if ($orgPusat !== '') {
                    static::applyOrganisasiPusatScope($kep, $table, $induk, $orgPusat, $hasOrgDaerahCol);

                    return;
                }

                if ($induk !== '') {
                    static::applyIndukOnlyScope($kep, $table, $induk);
                }
            });

            if ($includeLegacyRowsWithEmptyInduk) {
                $outer->orWhere(function ($legacy) use (
                    $table,
                    $orgPusat,
                    $organisasiDaerah,
                    $unitKerja,
                    $hasOrgDaerahCol
                ) {
                    static::applyLegacyEmptyIndukScope(
                        $legacy,
                        $table,
                        $orgPusat,
                        $organisasiDaerah,
                        $unitKerja,
                        $hasOrgDaerahCol
                    );
                });
            }
        });
    }

    public static function buildHint(Usm $actor, bool $includesLegacyNote = false): string
    {
        $induk = trim((string) ($actor->induk_pemerintahan ?? ''));
        $orgPusat = trim((string) ($actor->pemerintahan_daerah ?? ''));
        $organisasiDaerah = trim((string) ($actor->organisasi_daerah ?? ''));
        $unitKerja = trim((string) ($actor->unit_kerja ?? ''));
        [$organisasiDaerah, $unitKerja] = static::resolveOrganisasiDaerahAndUnit(
            $induk,
            $orgPusat,
            $organisasiDaerah,
            $unitKerja
        );
        $suffix = $includesLegacyNote ? ' (termasuk baris legacy tanpa induk organisasi).' : '.';

        if ($unitKerja !== '') {
            return 'Menampilkan user di unit kerja: '.$unitKerja
                .($organisasiDaerah !== '' ? ' ('.$organisasiDaerah.')' : '')
                .$suffix;
        }

        if ($organisasiDaerah !== '') {
            return 'Menampilkan operator di organisasi daerah: '.$organisasiDaerah
                .($orgPusat !== '' ? ' — '.$orgPusat : '')
                .' (semua unit di bawahnya)'
                .$suffix;
        }

        if ($orgPusat !== '') {
            return 'Menampilkan operator di organisasi pusat (Polda) dan seluruh unit di bawahnya: '.$orgPusat.$suffix;
        }

        return 'Menampilkan user induk organisasi: '.$induk.$suffix;
    }

    /**
     * Apakah target user berada dalam cakupan kepegawaian actor (selaras {@see applyScope}).
     */
    public static function targetInActorScope(Usm $actor, Usm $target): bool
    {
        if (! static::actorHasScope($actor)) {
            return true;
        }

        $actorInduk = trim((string) ($actor->induk_pemerintahan ?? ''));
        $actorOrgPusat = trim((string) ($actor->pemerintahan_daerah ?? ''));
        $actorOrgDaerah = trim((string) ($actor->organisasi_daerah ?? ''));
        $actorUnit = trim((string) ($actor->unit_kerja ?? ''));
        [$actorOrgDaerah, $actorUnit] = static::resolveOrganisasiDaerahAndUnit(
            $actorInduk,
            $actorOrgPusat,
            $actorOrgDaerah,
            $actorUnit
        );

        $targetUnit = trim((string) ($target->unit_kerja ?? ''));
        $targetOrgDaerah = trim((string) ($target->organisasi_daerah ?? ''));
        $targetOrgPusat = trim((string) ($target->pemerintahan_daerah ?? ''));
        $targetInduk = trim((string) ($target->induk_pemerintahan ?? ''));
        [$targetOrgDaerah, $targetUnit] = static::resolveOrganisasiDaerahAndUnit(
            $targetInduk,
            $targetOrgPusat,
            $targetOrgDaerah,
            $targetUnit
        );

        if ($actorInduk !== '' && $targetInduk !== ''
            && ! UsmMasterReferensi::indukOrganisasiNamesMatch($actorInduk, $targetInduk)) {
            return false;
        }

        if ($actorUnit !== '') {
            return static::fieldMatches($targetUnit, $actorUnit)
                && ($actorOrgDaerah === '' || static::fieldMatches($targetOrgDaerah, $actorOrgDaerah))
                && ($actorOrgPusat === '' || static::fieldMatches($targetOrgPusat, $actorOrgPusat));
        }

        if ($actorOrgDaerah !== '') {
            return static::fieldMatches($targetOrgDaerah, $actorOrgDaerah)
                && ($actorOrgPusat === '' || static::fieldMatches($targetOrgPusat, $actorOrgPusat));
        }

        if ($actorOrgPusat !== '') {
            return static::fieldMatches($targetOrgPusat, $actorOrgPusat);
        }

        if ($actorInduk !== '') {
            return $targetInduk === ''
                || UsmMasterReferensi::indukOrganisasiNamesMatch($actorInduk, $targetInduk);
        }

        return true;
    }

    /**
     * Profil Polres kadang menyimpan nama resort di `unit_kerja` (data lama) — perlakukan sebagai organisasi daerah.
     *
     * @return array{0: string, 1: string} [organisasi_daerah, unit_kerja]
     */
    private static function resolveOrganisasiDaerahAndUnit(
        string $induk,
        string $orgPusat,
        string $organisasiDaerah,
        string $unitKerja
    ): array {
        if ($organisasiDaerah !== '' || $unitKerja === '') {
            return [$organisasiDaerah, $unitKerja];
        }

        if ($orgPusat === '') {
            return [$organisasiDaerah, $unitKerja];
        }

        if (UsmMasterReferensi::masterKlResortExistsForHierarchy($induk, $orgPusat, $unitKerja)) {
            return [$unitKerja, ''];
        }

        return [$organisasiDaerah, $unitKerja];
    }

    private static function fieldMatches(string $left, string $right): bool
    {
        if ($right === '') {
            return true;
        }

        return strcasecmp(trim($left), trim($right)) === 0;
    }

    /**
     * @param  QueryBuilder|EloquentBuilder  $query
     */
    private static function applyUnitKerjaScope(
        $query,
        string $table,
        string $induk,
        string $orgPusat,
        string $organisasiDaerah,
        string $unitKerja,
        bool $hasOrgDaerahCol
    ): void {
        static::applyColumnEquals($query, $table.'.unit_kerja', $unitKerja);

        if ($hasOrgDaerahCol && $organisasiDaerah !== '') {
            static::applyColumnEquals($query, $table.'.organisasi_daerah', $organisasiDaerah);
        }

        if ($orgPusat !== '') {
            static::applyColumnEquals($query, $table.'.pemerintahan_daerah', $orgPusat);
        }

        if ($induk !== '') {
            static::applyIndukColumn($query, $table.'.induk_pemerintahan', $induk);
        }
    }

    /**
     * Polres / Ditlantas: hanya user di organisasi daerah yang sama (bukan Polda lain / Polres lain).
     *
     * @param  QueryBuilder|EloquentBuilder  $query
     */
    private static function applyOrganisasiDaerahScope(
        $query,
        string $table,
        string $induk,
        string $orgPusat,
        string $organisasiDaerah
    ): void {
        static::applyColumnEquals($query, $table.'.organisasi_daerah', $organisasiDaerah);

        if ($orgPusat !== '') {
            static::applyColumnEquals($query, $table.'.pemerintahan_daerah', $orgPusat);
        }

        if ($induk !== '') {
            static::applyIndukColumn($query, $table.'.induk_pemerintahan', $induk);
        }
    }

    /**
     * Polda: user dengan organisasi pusat yang sama (semua organisasi daerah & unit di bawahnya).
     *
     * @param  QueryBuilder|EloquentBuilder  $query
     */
    private static function applyOrganisasiPusatScope(
        $query,
        string $table,
        string $induk,
        string $orgPusat,
        bool $hasOrgDaerahCol
    ): void {
        static::applyColumnEquals($query, $table.'.pemerintahan_daerah', $orgPusat);

        if ($induk !== '') {
            static::applyIndukColumn($query, $table.'.induk_pemerintahan', $induk);
        }

        // Mode 3 tingkat (tanpa kolom organisasi_daerah): unit_kerja = nama resort di bawah pusat ini.
        if (! $hasOrgDaerahCol) {
            return;
        }

        // User Polda murni (hanya isi pusat, organisasi_daerah kosong) tetap tampil bersama bawahannya.
    }

    /**
     * @param  QueryBuilder|EloquentBuilder  $query
     */
    private static function applyIndukOnlyScope($query, string $table, string $induk): void
    {
        static::applyIndukColumn($query, $table.'.induk_pemerintahan', $induk);
    }

    /**
     * Baris legacy: induk kosong tetapi organisasi terisi — tetap dalam cakupan yang sama.
     *
     * @param  QueryBuilder|EloquentBuilder  $query
     */
    private static function applyLegacyEmptyIndukScope(
        $query,
        string $table,
        string $orgPusat,
        string $organisasiDaerah,
        string $unitKerja,
        bool $hasOrgDaerahCol
    ): void {
        $query->where(function ($q) use ($table) {
            $q->where($table.'.induk_pemerintahan', '')->orWhereNull($table.'.induk_pemerintahan');
        });

        if ($unitKerja !== '') {
            static::applyColumnEquals($query, $table.'.unit_kerja', $unitKerja);
            if ($hasOrgDaerahCol && $organisasiDaerah !== '') {
                static::applyColumnEquals($query, $table.'.organisasi_daerah', $organisasiDaerah);
            }
            if ($orgPusat !== '') {
                static::applyColumnEquals($query, $table.'.pemerintahan_daerah', $orgPusat);
            }

            return;
        }

        if ($organisasiDaerah !== '' && $hasOrgDaerahCol) {
            static::applyColumnEquals($query, $table.'.organisasi_daerah', $organisasiDaerah);
            if ($orgPusat !== '') {
                static::applyColumnEquals($query, $table.'.pemerintahan_daerah', $orgPusat);
            }

            return;
        }

        if ($orgPusat !== '') {
            static::applyColumnEquals($query, $table.'.pemerintahan_daerah', $orgPusat);
        }
    }

    /**
     * @param  QueryBuilder|EloquentBuilder  $query
     */
    private static function applyColumnEquals($query, string $qualifiedColumn, string $value): void
    {
        $query->whereRaw(
            'LOWER(TRIM(COALESCE('.$qualifiedColumn.', ""))) = ?',
            [mb_strtolower(trim($value))]
        );
    }

    /**
     * @param  QueryBuilder|EloquentBuilder  $query
     */
    private static function applyIndukColumn($query, string $qualifiedColumn, string $induk): void
    {
        $names = UsmMasterReferensi::indukOrganisasiEquivalentNames($induk);
        $query->where(function ($q) use ($qualifiedColumn, $names) {
            foreach ($names as $name) {
                $q->orWhereRaw(
                    'LOWER(TRIM(COALESCE('.$qualifiedColumn.', ""))) = ?',
                    [mb_strtolower(trim($name))]
                );
            }
        });
    }

    /**
     * Mode 3 tingkat: organisasi daerah tersimpan di `unit_kerja`.
     *
     * @param  QueryBuilder|EloquentBuilder  $query
     */
    public static function applyScopeLegacyTigaTingkat(
        QueryBuilder|EloquentBuilder $query,
        string $table,
        Usm $actor,
        bool $includeLegacyRowsWithEmptyInduk = true
    ): void {
        if (Schema::hasColumn($table, 'organisasi_daerah')) {
            static::applyScope($query, $table, $actor, $includeLegacyRowsWithEmptyInduk);

            return;
        }

        $induk = trim((string) ($actor->induk_pemerintahan ?? ''));
        $orgPusat = trim((string) ($actor->pemerintahan_daerah ?? ''));
        $unitKerja = trim((string) ($actor->unit_kerja ?? ''));

        if ($orgPusat === '' && $unitKerja === '' && $induk === '') {
            return;
        }

        $query->where(function ($outer) use ($table, $induk, $orgPusat, $unitKerja, $includeLegacyRowsWithEmptyInduk) {
            $outer->where(function ($kep) use ($table, $induk, $orgPusat, $unitKerja) {
                if ($unitKerja !== '') {
                    static::applyColumnEquals($kep, $table.'.unit_kerja', $unitKerja);
                    if ($orgPusat !== '') {
                        static::applyColumnEquals($kep, $table.'.pemerintahan_daerah', $orgPusat);
                    }
                    if ($induk !== '') {
                        static::applyIndukColumn($kep, $table.'.induk_pemerintahan', $induk);
                    }

                    return;
                }

                if ($orgPusat !== '') {
                    static::applyColumnEquals($kep, $table.'.pemerintahan_daerah', $orgPusat);
                    if ($induk !== '') {
                        static::applyIndukColumn($kep, $table.'.induk_pemerintahan', $induk);
                    }
                }
            });

            if ($includeLegacyRowsWithEmptyInduk) {
                $outer->orWhere(function ($legacy) use ($table, $orgPusat, $unitKerja) {
                    $legacy->where(function ($q) use ($table) {
                        $q->where($table.'.induk_pemerintahan', '')->orWhereNull($table.'.induk_pemerintahan');
                    });
                    if ($unitKerja !== '') {
                        static::applyColumnEquals($legacy, $table.'.unit_kerja', $unitKerja);
                        if ($orgPusat !== '') {
                            static::applyColumnEquals($legacy, $table.'.pemerintahan_daerah', $orgPusat);
                        }
                    } elseif ($orgPusat !== '') {
                        static::applyColumnEquals($legacy, $table.'.pemerintahan_daerah', $orgPusat);
                    }
                });
            }
        });
    }
}
