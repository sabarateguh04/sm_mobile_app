<?php

namespace App\Helpers;

use App\Support\SmTerritoryData;
use App\Support\TerritoryLevelForUsm;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class HierarchicalHelper
{
    /**
     * Super Admin (matriks `role_menu_matrix.super_admin_roles`) dan executive pusat (matrix 7)
     * melihat semua provinsi/kab-kota. User dengan kab/kota (`wilayah`) atau provinsi (`daerah`)
     * terisi dibatasi ke cakupan masing-masing.
     */
    public static function viewerBypassesTerritoryScope(): bool
    {
        $user = Auth::user();
        if (! $user) {
            return false;
        }

        $matrixId = MenuHelper::matrixRoleIdForUser($user);

        return in_array($matrixId, config('role_menu_matrix.super_admin_roles', [1]), true)
            || MenuHelper::isExecutiveNationalUser($user);
    }

    /**
     * Tingkat cakupan profil user: {@see TerritoryLevelForUsm::NASIONAL}, {@see TerritoryLevelForUsm::PROVINSI}, atau {@see TerritoryLevelForUsm::KAB_KOTA}.
     *
     * @return 'nasional'|'provinsi'|'kab_kota'|null
     */
    public static function getUserLevel()
    {
        $user = Auth::user();

        if (! $user) {
            return null;
        }

        return TerritoryLevelForUsm::resolve($user);
    }

    /**
     * Apply hierarchical filter to query (kolom DB `daerah` = provinsi, `wilayah` = kab/kota).
     *
     * @param \Illuminate\Database\Query\Builder|\Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Query\Builder|\Illuminate\Database\Eloquent\Builder
     */
    public static function applyFilter($query, ?string $level = null)
    {
        $user = Auth::user();

        if (! $user) {
            return $query;
        }

        if (self::viewerBypassesTerritoryScope()) {
            return $query;
        }

        if (! $level) {
            $level = self::getUserLevel();
        }

        $level = TerritoryLevelForUsm::normalize($level);

        switch ($level) {
            case TerritoryLevelForUsm::KAB_KOTA:
                if (! empty($user->wilayah)) {
                    self::whereTerritoryColumnLike($query, 'wilayah', (string) $user->wilayah);
                } elseif (! empty($user->daerah)) {
                    self::whereTerritoryColumnLike($query, 'daerah', (string) $user->daerah);
                }
                break;

            case TerritoryLevelForUsm::PROVINSI:
                if (! empty($user->daerah)) {
                    self::whereTerritoryColumnLike($query, 'daerah', (string) $user->daerah);
                }
                break;

            case TerritoryLevelForUsm::NASIONAL:
                break;
        }

        return $query;
    }

    /**
     * Check if user can access specific territory level (menerima slug lama `daerah` / `wilayah`).
     */
    public static function canAccess(string $requiredLevel): bool
    {
        $userLevel = self::getUserLevel();
        $requiredLevel = TerritoryLevelForUsm::normalize($requiredLevel) ?? $requiredLevel;

        if (! $userLevel) {
            return false;
        }

        if ($userLevel === TerritoryLevelForUsm::NASIONAL) {
            return true;
        }

        if ($userLevel === TerritoryLevelForUsm::PROVINSI && in_array($requiredLevel, [TerritoryLevelForUsm::PROVINSI, TerritoryLevelForUsm::KAB_KOTA], true)) {
            return true;
        }

        if ($userLevel === TerritoryLevelForUsm::KAB_KOTA && $requiredLevel === TerritoryLevelForUsm::KAB_KOTA) {
            return true;
        }

        return false;
    }

    /** Nilai provinsi dari profil (`usm.daerah`). */
    public static function getUserProvinsi()
    {
        return Auth::user()->daerah ?? null;
    }

    /** Nilai kab/kota dari profil (`usm.wilayah`). */
    public static function getUserKabKota()
    {
        return Auth::user()->wilayah ?? null;
    }

    /** @deprecated Gunakan {@see getUserProvinsi} */
    public static function getUserDaerah()
    {
        return self::getUserProvinsi();
    }

    /** @deprecated Gunakan {@see getUserKabKota} */
    public static function getUserWilayah()
    {
        return self::getUserKabKota();
    }

    /**
     * Filter via JOIN ke `usm` lewat kolom `piket`.
     * Skema sbo: data (mis. dataterminal, datagangguan, posyan) tidak punya kolom wilayah/daerah,
     * jadi filter via JOIN ke `usm`.
     *
     * Catatan: $query harus berupa query builder DB::table('xxx'); JOIN dilakukan di sini.
     *
     * @return Builder
     */
    public static function applyFilterViaPiket(Builder $query, string $table): Builder
    {
        $user = Auth::user();
        if (! $user) {
            return $query;
        }

        if (self::viewerBypassesTerritoryScope()) {
            return $query;
        }

        $query->leftJoin('usm', $table . '.piket', '=', 'usm.id_usm');

        $level = self::getUserLevel();

        if ($level === TerritoryLevelForUsm::KAB_KOTA && ! empty($user->wilayah)) {
            self::whereTerritoryColumnLike($query, 'usm.wilayah', (string) $user->wilayah);
        } elseif ($level === TerritoryLevelForUsm::PROVINSI && ! empty($user->daerah)) {
            self::whereTerritoryColumnLike($query, 'usm.daerah', (string) $user->daerah);
        }

        return $query;
    }

    /**
     * Batasi query operasional ke wilayah/daerah user yang login.
     * Dipakai untuk index CRUD, peta, dashboard — tanpa JOIN tambahan (pakai whereExists).
     *
     * Super Admin: tidak dibatasi (lihat `viewerBypassesTerritoryScope`).
     * Wilayah: kolom `wilayah` pada tabel, atau petugas (`piket`→`usm`), atau `profilposko`.
     * Daerah: kolom `daerah` serupa.
     * User nasional (tanpa wilayah & daerah): tidak dibatasi — melihat data seluruh Indonesia.
     */
    public static function applyTerritoryScopeForTable(Builder $query, string $table, ?string $qualifiedPrefix = null): void
    {
        $user = Auth::user();
        if (! $user) {
            return;
        }

        if (self::viewerBypassesTerritoryScope()) {
            return;
        }

        $connName = $query->getConnection()->getName();
        if (! Schema::connection($connName)->hasTable($table) && ! Schema::connection($connName)->hasView($table)) {
            return;
        }

        $prefix = $qualifiedPrefix ?? $table;

        $level = self::getUserLevel();

        // Kab/kota (oprator, Polres, Executive3): filter utama by wilayah.
        // Jangan wajibkan match daerah — nama Polda di profil vs data sering beda ejaan
        // ("JAWA BARAT" vs "POLDA JABAR") sehingga marker modul kosong di peta.
        if ($level === TerritoryLevelForUsm::KAB_KOTA) {
            if (! empty($user->wilayah)) {
                self::applyWilayahTerritoryScope($query, $table, $user->wilayah, $prefix);

                return;
            }
            if (! empty($user->daerah)) {
                self::applyDaerahTerritoryScope($query, $table, $user->daerah, $prefix);
            }
        } elseif ($level === TerritoryLevelForUsm::PROVINSI) {
            if (! empty($user->daerah)) {
                self::applyDaerahTerritoryScope($query, $table, $user->daerah, $prefix);
            }
        }
    }

    /**
     * Filter baris dengan pasangan provinsi + kab/kota (admin wilayah / satu satuan).
     * Matching fleksibel (LIKE + nama dibersihkan) agar "POLRESTA BANDUNG" cocok dengan "KOTA BANDUNG".
     */
    private static function applyDaerahDanWilayahTerritoryScope(Builder $query, string $table, string $daerah, string $wilayah, string $prefix): void
    {
        $query->where(function ($q) use ($table, $daerah, $wilayah, $prefix) {
            $any = false;

            $connName = $q->getConnection()->getName();
            if (Schema::connection($connName)->hasColumn($table, 'daerah') && Schema::connection($connName)->hasColumn($table, 'wilayah')) {
                self::whereTerritoryColumnLike($q, $prefix.'.daerah', $daerah);
                self::whereTerritoryColumnLike($q, $prefix.'.wilayah', $wilayah);
                $any = true;
            } elseif (Schema::connection($connName)->hasColumn($table, 'wilayah')) {
                self::whereTerritoryColumnLike($q, $prefix.'.wilayah', $wilayah);
                $any = true;
            }

            if (Schema::connection($connName)->hasColumn($table, 'piket')) {
                $method = $any ? 'orWhereExists' : 'whereExists';
                $q->{$method}(function ($sub) use ($table, $daerah, $wilayah, $prefix) {
                    $sub->select(DB::raw(1))
                        ->from('usm')
                        ->whereColumn('usm.id_usm', $prefix.'.piket');
                    self::whereTerritoryColumnLike($sub, 'usm.daerah', $daerah);
                    self::whereTerritoryColumnLike($sub, 'usm.wilayah', $wilayah);
                });
                $any = true;
            } elseif (Schema::connection($connName)->hasColumn($table, 'id_usm')) {
                $method = $any ? 'orWhereExists' : 'whereExists';
                $q->{$method}(function ($sub) use ($table, $daerah, $wilayah, $prefix) {
                    $sub->select(DB::raw(1))
                        ->from('usm')
                        ->whereColumn('usm.id_usm', $prefix.'.id_usm');
                    self::whereTerritoryColumnLike($sub, 'usm.daerah', $daerah);
                    self::whereTerritoryColumnLike($sub, 'usm.wilayah', $wilayah);
                });
                $any = true;
            }

            if (Schema::connection($connName)->hasColumn($table, 'id_profileposko')) {
                $method = $any ? 'orWhereExists' : 'whereExists';
                $q->{$method}(function ($sub) use ($table, $daerah, $wilayah, $prefix) {
                    $sub->select(DB::raw(1))
                        ->from('profilposko')
                        ->whereColumn('profilposko.id_profilposko', $prefix.'.id_profileposko');
                    self::whereTerritoryColumnLike($sub, 'profilposko.daerah', $daerah);
                    self::whereTerritoryColumnLike($sub, 'profilposko.wilayah', $wilayah);
                });
                $any = true;
            }

            if (! $any) {
                $q->whereRaw('1 = 0');
            }
        });
    }

    private static function applyWilayahTerritoryScope(Builder $query, string $table, string $wilayah, string $prefix): void
    {
        $query->where(function ($q) use ($table, $wilayah, $prefix) {
            $any = false;

            $connName = $q->getConnection()->getName();
            if (Schema::connection($connName)->hasColumn($table, 'wilayah')) {
                self::whereTerritoryColumnLike($q, $prefix.'.wilayah', $wilayah);
                $any = true;
            }

            if (Schema::connection($connName)->hasColumn($table, 'piket')) {
                $method = $any ? 'orWhereExists' : 'whereExists';
                $q->{$method}(function ($sub) use ($table, $wilayah, $prefix) {
                    $sub->select(DB::raw(1))
                        ->from('usm')
                        ->whereColumn('usm.id_usm', $prefix.'.piket');
                    self::whereTerritoryColumnLike($sub, 'usm.wilayah', $wilayah);
                });
                $any = true;
            } elseif (Schema::connection($connName)->hasColumn($table, 'id_usm')) {
                $method = $any ? 'orWhereExists' : 'whereExists';
                $q->{$method}(function ($sub) use ($table, $wilayah, $prefix) {
                    $sub->select(DB::raw(1))
                        ->from('usm')
                        ->whereColumn('usm.id_usm', $prefix.'.id_usm');
                    self::whereTerritoryColumnLike($sub, 'usm.wilayah', $wilayah);
                });
                $any = true;
            }

            if (Schema::connection($connName)->hasColumn($table, 'id_profileposko')) {
                $method = $any ? 'orWhereExists' : 'whereExists';
                $q->{$method}(function ($sub) use ($table, $wilayah, $prefix) {
                    $sub->select(DB::raw(1))
                        ->from('profilposko')
                        ->whereColumn('profilposko.id_profilposko', $prefix.'.id_profileposko');
                    self::whereTerritoryColumnLike($sub, 'profilposko.wilayah', $wilayah);
                });
                $any = true;
            }

            if (! $any) {
                $q->whereRaw('1 = 0');
            }
        });
    }

    private static function applyDaerahTerritoryScope(Builder $query, string $table, string $daerah, string $prefix): void
    {
        $query->where(function ($q) use ($table, $daerah, $prefix) {
            $any = false;

            $connName = $q->getConnection()->getName();
            if (Schema::connection($connName)->hasColumn($table, 'daerah')) {
                self::whereTerritoryColumnLike($q, $prefix.'.daerah', $daerah);
                $any = true;
            }

            if (Schema::connection($connName)->hasColumn($table, 'piket')) {
                $method = $any ? 'orWhereExists' : 'whereExists';
                $q->{$method}(function ($sub) use ($table, $daerah, $prefix) {
                    $sub->select(DB::raw(1))
                        ->from('usm')
                        ->whereColumn('usm.id_usm', $prefix.'.piket');
                    self::whereTerritoryColumnLike($sub, 'usm.daerah', $daerah);
                });
                $any = true;
            } elseif (Schema::connection($connName)->hasColumn($table, 'id_usm')) {
                $method = $any ? 'orWhereExists' : 'whereExists';
                $q->{$method}(function ($sub) use ($table, $daerah, $prefix) {
                    $sub->select(DB::raw(1))
                        ->from('usm')
                        ->whereColumn('usm.id_usm', $prefix.'.id_usm');
                    self::whereTerritoryColumnLike($sub, 'usm.daerah', $daerah);
                });
                $any = true;
            }

            if (Schema::connection($connName)->hasColumn($table, 'id_profileposko')) {
                $method = $any ? 'orWhereExists' : 'whereExists';
                $q->{$method}(function ($sub) use ($table, $daerah, $prefix) {
                    $sub->select(DB::raw(1))
                        ->from('profilposko')
                        ->whereColumn('profilposko.id_profilposko', $prefix.'.id_profileposko');
                    self::whereTerritoryColumnLike($sub, 'profilposko.daerah', $daerah);
                });
                $any = true;
            }

            if (! $any) {
                $q->whereRaw('1 = 0');
            }
        });
    }

    /**
     * Cocokkan kolom wilayah/daerah secara longgar (buang prefix POLRES/KOTA/dll + LIKE).
     *
     * @param  \Illuminate\Database\Query\Builder|\Illuminate\Database\Eloquent\Builder  $query
     */
    private static function whereTerritoryColumnLike($query, string $column, string $value): void
    {
        $needle = self::territoryMatchNeedle($value);
        if ($needle === '') {
            return;
        }

        $query->whereRaw('LOWER('.$column.') LIKE ?', ['%'.$needle.'%']);
    }

    /**
     * Normalisasi nama territory untuk pencocokan (selaras SmTerritoryData / TerritoryIndexFilters).
     */
    private static function territoryMatchNeedle(string $name): string
    {
        return SmTerritoryData::cleanTerritoryName($name);
    }
}
