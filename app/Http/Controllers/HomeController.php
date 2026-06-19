<?php

namespace App\Http\Controllers;

use App\Helpers\HierarchicalHelper;
use App\Helpers\MenuHelper;
use App\Models\Usm;
use App\Support\TerritoryIndexFilters;
use App\Support\UserListingScope;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class HomeController extends Controller
{
    /**
     * Show the application dashboard.
     */
    public function index()
    {
        $userLevel = HierarchicalHelper::getUserLevel();
        $showCountingRecords = (bool) config('dashboard.show_counting_records', false);

        $stats = [];
        $chartData = ['labels' => [], 'totals' => [], 'today' => [], 'this_month' => [], 'colors' => [], 'borderColors' => []];
        $chartTrafficArusMudik = ['labels' => [], 'datasets' => []];
        $chartPenumpang = ['labels' => [], 'kedatangan' => [], 'keberangkatan' => []];
        $userStats = null;

        if ($showCountingRecords) {
            $stats = [
                'arusmudik' => $this->countByTanggal('arusmudik', 'tanggal'),
                'profilposko' => $this->countSimple('pos_pelayanan', ['active_key' => 'piket']),
                'turjagwali' => $this->countByTanggal('turjagwali', 'tanggal'),
                'auditarus' => $this->countByTanggal('auditarus', 'tanggal'),
                'pnpangkutan' => $this->countByTanggal('pnp_angkutan', 'tanggal'),
                'contigency' => $this->countByTanggal('contigency', 'tanggal'),
                'trafficstuck' => $this->countByTanggal('trafficstuck', 'tanggal'),
                'dataterminal' => $this->countByTanggal('data_terminal', 'tanggal'),
                'datagangguan' => $this->countByTanggal('data_gangguan', 'tanggal'),
            ];

            $chartData = $this->getChartData($stats);
            $chartTrafficArusMudik = $this->getTrafficArusMudikBarData();
            $chartPenumpang = $this->getPenumpangChartData();

            if (MenuHelper::isSuperAdminForLayout(Auth::user())) {
                $userStats = $this->getUserDashboardStats();
            }
        }

        $chartKebersihan = $this->getContigencyFaktorChart();
        $chartSarpras = $this->getTrafficstuckDurasiChart();
        $contigencyNews = $this->getContigencyNewsReport();

        return view('home', array_merge([
            'stats' => $stats,
            'userLevel' => $userLevel,
            'showCountingRecords' => $showCountingRecords,
            'chartData' => $chartData,
            'chartKebersihan' => $chartKebersihan,
            'chartSarpras' => $chartSarpras,
            'chartTrafficArusMudik' => $chartTrafficArusMudik,
            'chartPenumpang' => $chartPenumpang,
            'contigencyNews' => $contigencyNews,
            'userStats' => $userStats,
        ], TerritoryIndexFilters::viewBundle(request())));
    }

    /**
     * Statistik user di kartu dashboard — scope sama dengan Personil Manager (admin/users).
     *
     * @return array{total: int, active: int, pending: int, blocked: int}
     */
    private function getUserDashboardStats(): array
    {
        $viewer = Auth::user();
        if (! $viewer) {
            return ['total' => 0, 'active' => 0, 'pending' => 0, 'blocked' => 0];
        }

        $query = Usm::query();
        UserListingScope::applyBaseScopes($query);

        return [
            'total' => (clone $query)->count(),
            'active' => (clone $query)->where('account_status', 'active')->count(),
            'pending' => (clone $query)->where('account_status', 'pending')->count(),
            'blocked' => (clone $query)->where('account_status', 'blocked')->count(),
        ];
    }

    /**
     * Apply filter wilayah/daerah untuk dashboard (tanpa JOIN — pakai scope exists).
     */
    private function applyPiketFilter($query, string $table): void
    {
        HierarchicalHelper::applyTerritoryScopeForTable($query, $table);
        TerritoryIndexFilters::applyUsmFilters($query, request());
    }

    /**
     * Hitung total/today/this_month untuk tabel berdasarkan kolom tanggal.
     * Auto-skip jika tabel/kolom tidak ada (kompatibel dgn sbo & smartbackoffice).
     */
    private function countByTanggal(string $table, string $dateColumn): array
    {
        $empty = ['total' => 0, 'today' => 0, 'this_month' => 0];
        if (!Schema::hasTable($table)) return $empty;

        $query = DB::table($table)->leftJoin('usm', $table . '.piket', '=', 'usm.id_usm');
        $this->applyPiketFilter($query, $table);
        $base = clone $query;

        $today = (clone $base);
        $month = (clone $base);
        if (Schema::hasColumn($table, $dateColumn)) {
            $today->whereDate($table . '.' . $dateColumn, today());
            $month->whereMonth($table . '.' . $dateColumn, now()->month)
                ->whereYear($table . '.' . $dateColumn, now()->year);
        }

        return [
            'total' => $base->count(),
            'today' => $today->count(),
            'this_month' => $month->count(),
        ];
    }

    /**
     * Hitung total + 'active' (baris dgn $active_key not null).
     */
    private function countSimple(string $table, array $opts = []): array
    {
        if (!Schema::hasTable($table)) return ['total' => 0, 'active' => 0];
        $query = DB::table($table)->leftJoin('usm', $table . '.piket', '=', 'usm.id_usm');
        $this->applyPiketFilter($query, $table);
        $base = clone $query;
        $active = clone $base;
        if (!empty($opts['active_key']) && Schema::hasColumn($table, $opts['active_key'])) {
            $active->whereNotNull($table . '.' . $opts['active_key']);
        }
        return [
            'total' => $base->count(),
            'active' => $active->count(),
        ];
    }

    private function getPoskoTotal(): int
    {
        if (!Schema::hasTable('pos_pelayanan')) return 0;
        $query = DB::table('pos_pelayanan')->leftJoin('usm', 'pos_pelayanan.piket', '=', 'usm.id_usm');
        $this->applyPiketFilter($query, 'pos_pelayanan');
        return $query->count();
    }

    private function getContigencyFaktorChart(): array
    {
        if (!Schema::hasTable('contigency')) {
            return ['labels' => [], 'values' => [], 'colors' => []];
        }
        $query = DB::table('contigency')->leftJoin('usm', 'contigency.piket', '=', 'usm.id_usm');
        $this->applyPiketFilter($query, 'contigency');
        $rows = $query->select('faktor', DB::raw('count(*) as total'))
                      ->whereNotNull('faktor')
                      ->where('faktor', '!=', '')
                      ->groupBy('faktor')
                      ->orderByDesc('total')
                      ->limit(6)
                      ->get();
        
        $labels = $rows->pluck('faktor')->all();
        $values = $rows->pluck('total')->map(fn($v) => (int)$v)->all();
        $colors = ['#ef4444', '#f97316', '#f59e0b', '#84cc16', '#22c55e', '#0ea5e9'];

        return ['labels' => $labels, 'values' => $values, 'colors' => array_slice($colors, 0, count($labels))];
    }

    private function getTrafficstuckDurasiChart(): array
    {
        if (!Schema::hasTable('trafficstuck')) {
            return ['labels' => [], 'values' => [], 'colors' => []];
        }
        $query = DB::table('trafficstuck')->leftJoin('usm', 'trafficstuck.piket', '=', 'usm.id_usm');
        $this->applyPiketFilter($query, 'trafficstuck');
        $rows = $query->select('durasi', DB::raw('count(*) as total'))
                      ->whereNotNull('durasi')
                      ->where('durasi', '!=', '')
                      ->groupBy('durasi')
                      ->orderByDesc('total')
                      ->limit(6)
                      ->get();
        
        $labels = $rows->pluck('durasi')->all();
        $values = $rows->pluck('total')->map(fn($v) => (int)$v)->all();
        $colors = ['#3b82f6', '#6366f1', '#8b5cf6', '#a855f7', '#d946ef', '#f43f5e'];

        return ['labels' => $labels, 'values' => $values, 'colors' => array_slice($colors, 0, count($labels))];
    }

    private function getChartData(array $stats): array
    {
        $modules = ['arusmudik', 'profilposko', 'turjagwali', 'auditarus', 'pnpangkutan', 'contigency', 'trafficstuck'];
        $labels = ['Arus Mudik', 'Profil Posko', 'Turjagwali', 'Audit Arus', 'PnP Angkutan', 'QR Contigency', 'QR Perlambatan'];
        $totals = array_map(fn ($m) => $stats[$m]['total'] ?? 0, $modules);
        $today = array_map(fn ($m) => $stats[$m]['today'] ?? ($stats[$m]['total'] ?? 0), $modules);
        $month = array_map(fn ($m) => $stats[$m]['this_month'] ?? ($stats[$m]['total'] ?? 0), $modules);
        return [
            'labels' => $labels,
            'totals' => $totals,
            'today' => $today,
            'this_month' => $month,
            'colors' => [
                'rgba(102, 126, 234, 0.8)', 'rgba(23, 162, 184, 0.8)', 'rgba(40, 167, 69, 0.8)',
                'rgba(255, 193, 7, 0.8)', 'rgba(220, 53, 69, 0.8)', 'rgba(108, 117, 125, 0.8)', 'rgba(52, 58, 64, 0.8)',
            ],
            'borderColors' => [
                'rgba(102, 126, 234, 1)', 'rgba(23, 162, 184, 1)', 'rgba(40, 167, 69, 1)',
                'rgba(255, 193, 7, 1)', 'rgba(220, 53, 69, 1)', 'rgba(108, 117, 125, 1)', 'rgba(52, 58, 64, 1)',
            ],
        ];
    }

    /**
     * Bar chart traffic per tanggal. Pakai kolom asli sbo (r2, r4, r6) kalau ada.
     */
    private function getTrafficArusMudikBarData(): array
    {
        $empty = ['labels' => [], 'datasets' => []];
        if (!Schema::hasTable('arusmudik') || !Schema::hasColumn('arusmudik', 'tanggal')) return $empty;

        $query = DB::table('arusmudik')->leftJoin('usm', 'arusmudik.piket', '=', 'usm.id_usm');
        $this->applyPiketFilter($query, 'arusmudik');

        $hasR = Schema::hasColumn('arusmudik', 'r2') && Schema::hasColumn('arusmudik', 'r4') && Schema::hasColumn('arusmudik', 'r6');
        if ($hasR) {
            $query->selectRaw('arusmudik.tanggal as tanggal, SUM(arusmudik.r2) as r2, SUM(arusmudik.r4) as r4, SUM(arusmudik.r6) as r6');
        } else {
            $query->selectRaw('arusmudik.tanggal as tanggal, COUNT(*) as total');
        }
        $rows = $query->groupBy('arusmudik.tanggal')->orderBy('arusmudik.tanggal')->get();

        $labels = $rows->map(fn ($r) => $r->tanggal)->values()->all();
        if ($hasR) {
            $r2 = $rows->map(fn ($r) => (int) $r->r2)->values()->all();
            $r4 = $rows->map(fn ($r) => (int) $r->r4)->values()->all();
            $r6 = $rows->map(fn ($r) => (int) $r->r6)->values()->all();
        } else {
            $totals = $rows->map(fn ($r) => (int) $r->total)->values()->all();
            $r2 = array_map(fn ($t) => (int) round($t * 0.1), $totals);
            $r4 = array_map(fn ($t) => (int) round($t * 0.5), $totals);
            $r6 = array_map(fn ($t, $i) => max(0, $totals[$i] - $r2[$i] - $r4[$i]), $totals, array_keys($totals));
        }

        return [
            'labels' => $labels,
            'datasets' => [
                ['label' => 'R2', 'data' => $r2, 'backgroundColor' => 'rgba(144, 238, 144, 0.8)', 'borderColor' => '#90EE90'],
                ['label' => 'R4', 'data' => $r4, 'backgroundColor' => 'rgba(199, 21, 133, 0.8)', 'borderColor' => '#C71585'],
                ['label' => 'R6 lebih', 'data' => $r6, 'backgroundColor' => 'rgba(255, 215, 0, 0.8)', 'borderColor' => '#FFD700'],
            ],
        ];
    }

    /**
     * Penumpang chart: kalau ada tabel `pnp_angkutan` (legacy: datapnp) pakai kolom kedatanganpnp/keberangkatanpnp,
     * fallback ke `pnpangkutan` (Laravel) dgn kolom `jumlah`.
     */
    private function getPenumpangChartData(): array
    {
        $empty = ['labels' => [], 'kedatangan' => [], 'keberangkatan' => []];

        if (Schema::hasTable('pnp_angkutan')) {
            $query = DB::table('pnp_angkutan')->leftJoin('usm', 'pnp_angkutan.piket', '=', 'usm.id_usm');
            $this->applyPiketFilter($query, 'pnp_angkutan');
            $rows = $query->selectRaw('pnp_angkutan.tanggal as tanggal, SUM(pnp_angkutan.kedatanganpnp) as kedatangan, SUM(pnp_angkutan.keberangkatanpnp) as keberangkatan')
                ->whereNotNull('pnp_angkutan.tanggal')
                ->groupBy('pnp_angkutan.tanggal')->orderBy('pnp_angkutan.tanggal')->get();
            return [
                'labels' => $rows->pluck('tanggal')->all(),
                'kedatangan' => $rows->pluck('kedatangan')->map(fn ($v) => (int) $v)->all(),
                'keberangkatan' => $rows->pluck('keberangkatan')->map(fn ($v) => (int) $v)->all(),
            ];
        }

        if (Schema::hasTable('pnpangkutan')) {
            $query = DB::table('pnpangkutan')->leftJoin('usm', 'pnpangkutan.piket', '=', 'usm.id_usm');
            $this->applyPiketFilter($query, 'pnpangkutan');
            $rows = $query->selectRaw('pnpangkutan.tanggal as tanggal, COALESCE(SUM(CAST(NULLIF(TRIM(pnpangkutan.jumlah), "") AS UNSIGNED)), 0) as total')
                ->groupBy('pnpangkutan.tanggal')->orderBy('pnpangkutan.tanggal')->get();
            $totals = $rows->pluck('total')->map(fn ($v) => (int) $v)->all();
            $kedatangan = array_map(fn ($t) => (int) round($t * 0.5), $totals);
            $keberangkatan = array_map(fn ($t, $i) => max(0, $totals[$i] - $kedatangan[$i]), $totals, array_keys($totals));
            return [
                'labels' => $rows->pluck('tanggal')->all(),
                'kedatangan' => $kedatangan,
                'keberangkatan' => $keberangkatan,
            ];
        }

        return $empty;
    }

    /**
     * Report News QR Contigency — laporan terbaru beserta foto situasi.
     *
     * @return \Illuminate\Support\Collection<int, object>
     */
    private function getContigencyNewsReport(int $limit = 12)
    {
        if (! Schema::hasTable('contigency')) {
            return collect();
        }

        $query = DB::table('contigency as c')
            ->leftJoin('usm', 'c.piket', '=', 'usm.id_usm')
            ->select(
                'c.id_contigency',
                'c.tanggal',
                'c.lokasi',
                'c.jeniscontigency',
                'c.fotosit',
                'c.koordinat',
                'c.jamperistiwa',
                'usm.wilayah as wilayah',
                'usm.daerah as daerah',
                'usm.nama as usm_nama'
            )
            ->whereNotNull('c.fotosit')
            ->where('c.fotosit', '!=', '')
            ->orderByDesc('c.id_contigency')
            ->limit($limit);

        HierarchicalHelper::applyTerritoryScopeForTable($query, 'contigency', 'c');
        TerritoryIndexFilters::applyUsmFilters($query, request());

        return $query->get()->map(function ($row) {
            $row->foto_url = $this->resolveContigencyFotoUrl($row->fotosit ?? null);

            return $row;
        })->filter(fn ($row) => ! empty($row->foto_url))->values();
    }

    private function resolveContigencyFotoUrl(?string $fotosit): ?string
    {
        if ($fotosit === null || trim($fotosit) === '') {
            return null;
        }

        $first = trim(explode(',', $fotosit, 2)[0]);
        if ($first === '') {
            return null;
        }

        if (preg_match('#^https?://#i', $first)) {
            return $first;
        }

        if (str_starts_with($first, '/storage/')) {
            return asset(ltrim($first, '/'));
        }

        return asset(ltrim($first, '/'));
    }
}
