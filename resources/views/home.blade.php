@extends('layouts.app')

@section('title', 'Dashboard')
@section('page-title', 'Dashboard')

@section('content')
@php
    $showCountingRecords = $showCountingRecords ?? (bool) config('dashboard.show_counting_records', false);
@endphp

<div class="space-y-3">

{{-- ══ MAP SEBARAN (EMBED) ══ --}}
<div>
    <div class="flex items-center gap-1.5 mb-2">
        <div class="w-6 h-6 rounded-md bg-indigo-100 flex items-center justify-center">
            <span class="material-symbols-outlined text-indigo-500 text-[14px]" style="font-variation-settings:'FILL' 1;">map</span>
        </div>
        <h6 class="m-0 font-bold text-[12px] text-slate-700">Peta Opsdal</h6>
    </div>
    <div class="bg-white rounded-xl border border-slate-200/60 shadow-sm overflow-hidden relative" style="height: 450px; z-index: 10;">
        <iframe src="{{ route('map.index') }}?embed=1" width="100%" height="100%" style="border:none;" title="Map Operasional"></iframe>
    </div>
</div>

{{-- ══ USER STATS (admin only) ══ --}}
@if($showCountingRecords && isset($userStats) && $userStats !== null)
<div>
    <div class="flex items-center gap-1.5 mb-2">
        <div class="w-6 h-6 rounded-md bg-sky-100 flex items-center justify-center">
            <span class="material-symbols-outlined text-sky-500 text-[14px]" style="font-variation-settings:'FILL' 1;">group</span>
        </div>
        <h6 class="m-0 font-bold text-[12px] text-slate-700">Statistik Pengguna</h6>
    </div>
    <div class="grid grid-cols-2 md:grid-cols-4 gap-2">
        @php
        $uCards = [
            ['label'=>'Total Users','val'=>$userStats['total']??0,'icon'=>'group','grad'=>'from-sky-500 to-sky-600','route'=>'admin.users.index'],
            ['label'=>'Aktif','val'=>$userStats['active']??0,'icon'=>'verified_user','grad'=>'from-emerald-500 to-emerald-600','route'=>'admin.users.index'],
            ['label'=>'Menunggu','val'=>$userStats['pending']??0,'icon'=>'hourglass_top','grad'=>'from-amber-400 to-amber-500','route'=>'admin.users.index'],
            ['label'=>'Diblokir','val'=>$userStats['blocked']??0,'icon'=>'block','grad'=>'from-red-500 to-red-600','route'=>'admin.users.index'],
        ];
        @endphp
        @foreach($uCards as $uc)
        <div class="bg-gradient-to-br {{ $uc['grad'] }} rounded-xl shadow-sm overflow-hidden">
            <div class="px-3 py-2.5 flex items-start justify-between gap-2">
                <div>
                    <p class="text-white/70 text-[9px] font-bold uppercase tracking-wide mb-0.5">{{ $uc['label'] }}</p>
                    <p class="text-white text-[22px] font-black m-0 leading-none">{{ $uc['val'] }}</p>
                </div>
                <div class="w-8 h-8 rounded-lg bg-white/20 flex items-center justify-center flex-shrink-0">
                    <span class="material-symbols-outlined text-white text-[17px]" style="font-variation-settings:'FILL' 1;">{{ $uc['icon'] }}</span>
                </div>
            </div>
            <div class="px-3 pb-2">
                <a href="{{ route($uc['route']) }}" class="flex items-center gap-1 text-white/80 hover:text-white text-[10px] font-semibold text-decoration-none transition-colors">
                    Lihat Detail <span class="material-symbols-outlined text-[12px]">arrow_forward</span>
                </a>
            </div>
        </div>
        @endforeach
    </div>
</div>
@endif

{{-- ══ MODULE STATS ══ --}}
@if($showCountingRecords && isset($stats) && count($stats ?? []))
<div>
    <div class="flex items-center gap-1.5 mb-2">
        <div class="w-6 h-6 rounded-md bg-primary/10 flex items-center justify-center">
            <span class="material-symbols-outlined text-primary text-[14px]" style="font-variation-settings:'FILL' 1;">grid_view</span>
        </div>
        <h6 class="m-0 font-bold text-[12px] text-slate-700">Statistik Modul Operasional</h6>
    </div>
    <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-2">
        @php
        $modules = [
            ['key'=>'arusmudik','label'=>'Arus Mudik & Balik','icon'=>'directions_car','color'=>'#667eea','route'=>'arusmudik.index','routeParams'=>[]],
            ['key'=>'profilposko','label'=>'Profil Posko','icon'=>'home_work','color'=>'#0ea5e9','route'=>'profilposko.index','routeParams'=>[]],
            ['key'=>'turjagwali','label'=>'Turjagwali','icon'=>'shield','color'=>'#22c55e','route'=>'turjagwali.index','routeParams'=>[]],
            ['key'=>'auditarus','label'=>'Audit Arus','icon'=>'fact_check','color'=>'#f59e0b','route'=>'auditarus.index','routeParams'=>[]],
            ['key'=>'pnpangkutan','label'=>'PnP Angkutan','icon'=>'directions_bus','color'=>'#ef4444','route'=>'pnpangkutan.index','routeParams'=>[]],
            ['key'=>'contigency','label'=>'QR Contigency','icon'=>'emergency','color'=>'#64748b','route'=>'contigency.index','routeParams'=>['sb_grp'=>'quick_respons']],
            ['key'=>'trafficstuck','label'=>'QR Perlambatan','icon'=>'traffic','color'=>'#334155','route'=>'trafficstuck.index','routeParams'=>['sb_grp'=>'quick_respons']],
        ];
        @endphp
        @foreach($modules as $mod)
        @if(isset($stats[$mod['key']]))
        <div class="bg-white rounded-xl border border-slate-200/60 shadow-sm overflow-hidden">
            <div class="px-3 py-2 flex items-start justify-between gap-2" style="background:{{ $mod['color'] }}18;">
                <div class="min-w-0">
                    <p class="text-[9px] font-bold uppercase tracking-wide mb-0.5 leading-tight" style="color:{{ $mod['color'] }};">{{ $mod['label'] }}</p>
                    <p class="text-[22px] font-black m-0 leading-none text-slate-800">{{ number_format($stats[$mod['key']]['total'] ?? 0) }}</p>
                </div>
                <div class="w-7 h-7 rounded-lg flex items-center justify-center flex-shrink-0" style="background:{{ $mod['color'] }}22;">
                    <span class="material-symbols-outlined text-[16px]" style="font-variation-settings:'FILL' 1;color:{{ $mod['color'] }};">{{ $mod['icon'] }}</span>
                </div>
            </div>
            <div class="px-3 py-1.5 border-t border-slate-100 flex items-center justify-between gap-1">
                <div class="flex items-center gap-2">
                    @if(isset($stats[$mod['key']]['today']))
                    <span class="flex items-center gap-1 text-[10px] text-slate-500">
                        <span class="material-symbols-outlined text-[12px]">today</span>{{ $stats[$mod['key']]['today'] }}
                    </span>
                    @endif
                    @if(isset($stats[$mod['key']]['this_month']))
                    <span class="flex items-center gap-1 text-[10px] text-slate-500">
                        <span class="material-symbols-outlined text-[12px]">calendar_month</span>{{ $stats[$mod['key']]['this_month'] }}
                    </span>
                    @endif
                    @if(isset($stats[$mod['key']]['active']))
                    <span class="flex items-center gap-1 text-[10px] text-emerald-600">
                        <span class="material-symbols-outlined text-[12px]">check_circle</span>{{ $stats[$mod['key']]['active'] }}
                    </span>
                    @endif
                </div>
                <a href="{{ route($mod['route'], $mod['routeParams']) }}" class="text-[11px] font-bold text-decoration-none flex items-center gap-0.5" style="color:{{ $mod['color'] }};">
                    Detail <span class="material-symbols-outlined text-[13px]">arrow_forward</span>
                </a>
            </div>
        </div>
        @endif
        @endforeach
    </div>
</div>
@endif

{{--
    ══ 4. DASHBOARD OPERASIONAL CHARTS (HIDDEN - dipindahkan ke dashboard per-rule) ══
    Chart "Trend Faktor Penyebab Peristiwa" dan "Trend Durasi Perlambatan" disembunyikan sementara.
    Akan dibuatkan dashboard terpisah di masing-masing modul (trafficstuck, contigency, turjagwali, dll).
--}}
{{-- HIDDEN: Chart Trend Penyebab & Perlambatan
<div>
    <div class="grid grid-cols-1 md:grid-cols-2 gap-2">
        <div class="bg-white rounded-xl border border-slate-200/60 shadow-sm overflow-hidden">
            <div class="px-3 py-2 border-b border-slate-100 bg-slate-50/40 flex items-center gap-2">
                <span class="material-symbols-outlined text-primary text-[14px]" style="font-variation-settings:'FILL' 1;">bar_chart</span>
                <div>
                    <h6 class="text-[11px] font-bold text-slate-800 m-0">Trend Faktor Penyebab Peristiwa</h6>
                    <p class="text-[9px] text-slate-400 m-0">Akumulasi data faktor penyebab</p>
                </div>
            </div>
            <div class="p-2.5"><div style="height:160px;position:relative;"><canvas id="chartKebersihanPosyan"></canvas></div></div>
        </div>
        <div class="bg-white rounded-xl border border-slate-200/60 shadow-sm overflow-hidden">
            <div class="px-3 py-2 border-b border-slate-100 bg-slate-50/40 flex items-center gap-2">
                <span class="material-symbols-outlined text-primary text-[14px]" style="font-variation-settings:'FILL' 1;">speed</span>
                <div>
                    <h6 class="text-[11px] font-bold text-slate-800 m-0">Trend Durasi Perlambatan</h6>
                    <p class="text-[9px] text-slate-400 m-0">Akumulasi peristiwa perlambatan Nasional</p>
                </div>
            </div>
            <div class="p-2.5"><div style="height:160px;position:relative;"><canvas id="chartSarprasPosyan"></canvas></div></div>
        </div>
    </div>
</div>
END HIDDEN --}}

{{-- ══ 5. REPORT NEWS: QR CONTIGENCY ══ --}}
{{-- HIDDEN: Report News QR Contigency
<div>
    <div class="flex items-center justify-end gap-2 mb-2 flex-wrap">
        <a href="{{ route('contigency.index', ['sb_grp' => 'quick_respons']) }}" class="flex items-center gap-1 px-2.5 py-1.5 bg-red-50 hover:bg-red-100 text-red-600 border border-red-200 font-bold rounded-lg text-[10px] transition-all text-decoration-none">
            <span class="material-symbols-outlined text-[12px]">open_in_new</span>Lihat Semua
        </a>
    </div>
    <div class="grid grid-cols-2 md:grid-cols-4 gap-2">
        @forelse(collect($contigencyNews ?? [])->take(4) as $news)
            <a href="{{ route('contigency.show', ['contigency' => $news->id_contigency, 'sb_grp' => 'quick_respons']) }}" class="text-decoration-none group">
                <div class="bg-white rounded-xl border border-slate-200/60 shadow-sm overflow-hidden h-full group-hover:-translate-y-0.5 transition-transform duration-200">
                    <div class="bg-slate-100 overflow-hidden" style="height:96px;">
                        <img src="{{ $news->foto_url }}" alt="{{ $news->jeniscontigency ?? 'Contigency' }}" loading="lazy"
                             class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300"
                             onerror="this.closest('.overflow-hidden').innerHTML='<div class=\'flex items-center justify-center h-full\'><span class=\'material-symbols-outlined text-slate-300 text-[28px]\'>image_not_supported</span></div>'">
                    </div>
                    <div class="p-2">
                        <div class="text-[11px] font-bold text-slate-800 truncate mb-1">{{ $news->jeniscontigency ?? 'Contigency' }}</div>
                        <div class="space-y-0.5">
                            <div class="flex items-center gap-1 text-[10px] text-slate-500 truncate">
                                <span class="material-symbols-outlined text-red-400 flex-shrink-0" style="font-size:11px;font-variation-settings:'FILL' 1;">location_on</span>
                                <span class="truncate">{{ $news->lokasi ?? '-' }}</span>
                            </div>
                            <div class="flex items-center gap-1 text-[10px] text-slate-400">
                                <span class="material-symbols-outlined flex-shrink-0" style="font-size:11px;">calendar_today</span>
                                {{ $news->tanggal ? \Carbon\Carbon::parse($news->tanggal)->format('d/m/Y') : '-' }}
                            </div>
                            @if(!empty($news->wilayah))
                                <span class="inline-flex items-center px-1.5 py-0.5 bg-primary/8 text-primary rounded text-[9px] font-bold">{{ $news->wilayah }}</span>
                            @endif
                        </div>
                    </div>
                </div>
            </a>
        @empty
            <div class="col-span-4 md:col-span-4">
                <div class="bg-white rounded-xl border border-slate-200/60 shadow-sm px-4 py-4 flex items-center gap-2 text-slate-400">
                    <span class="material-symbols-outlined text-[22px]">info</span>
                    <span class="text-[12px]">Belum ada laporan contigency dengan foto.</span>
                </div>
            </div>
        @endforelse
    </div>
</div>
END HIDDEN --}}

{{-- ══ STATISTIK LAINNYA (counting record + chart modul) ══ --}}
@if($showCountingRecords)
<div class="flex justify-center pt-0.5">
    <button class="flex items-center gap-1.5 px-4 py-2 bg-white hover:bg-slate-50 text-slate-600 border border-slate-200 font-bold rounded-xl text-[11px] shadow-sm transition-all"
            type="button" id="toggleMoreStatsBtn"
            aria-expanded="false" aria-controls="moreStatisticsCollapse">
        <span class="material-symbols-outlined text-[16px]" id="expandStatsIcon">expand_more</span>
        Statistik Lainnya
    </button>
</div>

<div id="moreStatisticsCollapse" class="hidden" aria-hidden="true">
    <div class="space-y-2.5 pt-1">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-2">
            <div class="bg-white rounded-xl border border-slate-200/60 shadow-sm overflow-hidden">
                <div class="px-3 py-2 border-b border-slate-100 bg-slate-50/40 flex items-center gap-2">
                    <span class="material-symbols-outlined text-primary text-[14px]" style="font-variation-settings:'FILL' 1;">directions_car</span>
                    <div>
                        <h6 class="text-[11px] font-bold text-slate-800 m-0">Counting Traffic Arus Mudik : KETUPAT 2026</h6>
                        <p class="text-[9px] text-slate-400 m-0">Traffic berdasarkan jenis ranmor</p>
                    </div>
                </div>
                <div class="p-2.5"><div style="height:170px;position:relative;"><canvas id="chartTrafficArusMudik"></canvas></div></div>
            </div>
            <div class="bg-white rounded-xl border border-slate-200/60 shadow-sm overflow-hidden">
                <div class="px-3 py-2 border-b border-slate-100 bg-slate-50/40 flex items-center gap-2">
                    <span class="material-symbols-outlined text-primary text-[14px]" style="font-variation-settings:'FILL' 1;">group</span>
                    <div>
                        <h6 class="text-[11px] font-bold text-slate-800 m-0">Kedatangan &amp; Keberangkatan Penumpang</h6>
                        <p class="text-[9px] text-slate-400 m-0">Data penumpang per tanggal</p>
                    </div>
                </div>
                <div class="p-2.5"><div style="height:170px;position:relative;"><canvas id="chartPenumpang"></canvas></div></div>
            </div>
        </div>

        <div class="flex items-center gap-1.5">
            <div class="w-6 h-6 rounded-md bg-primary/10 flex items-center justify-center">
                <span class="material-symbols-outlined text-primary text-[14px]" style="font-variation-settings:'FILL' 1;">query_stats</span>
            </div>
            <h6 class="m-0 font-bold text-[12px] text-slate-700">Visualisasi Statistik Modul</h6>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-2">
            <div class="bg-white rounded-xl border border-slate-200/60 shadow-sm overflow-hidden">
                <div class="px-3 py-2 flex items-center gap-2" style="background:linear-gradient(135deg,#667eea,#764ba2);">
                    <span class="material-symbols-outlined text-white text-[14px]" style="font-variation-settings:'FILL' 1;">bar_chart</span>
                    <h6 class="text-[11px] font-bold text-white m-0">Total Data per Modul</h6>
                </div>
                <div class="p-2.5"><div style="height:160px;position:relative;"><canvas id="totalChart"></canvas></div></div>
            </div>
            <div class="bg-white rounded-xl border border-slate-200/60 shadow-sm overflow-hidden">
                <div class="px-3 py-2 flex items-center gap-2" style="background:linear-gradient(135deg,#22c55e,#16a34a);">
                    <span class="material-symbols-outlined text-white text-[14px]" style="font-variation-settings:'FILL' 1;">show_chart</span>
                    <h6 class="text-[11px] font-bold text-white m-0">Trend Data Bulan Ini</h6>
                </div>
                <div class="p-2.5"><div style="height:160px;position:relative;"><canvas id="trendChart"></canvas></div></div>
            </div>
            <div class="bg-white rounded-xl border border-slate-200/60 shadow-sm overflow-hidden">
                <div class="px-3 py-2 flex items-center gap-2" style="background:linear-gradient(135deg,#0041c8,#3b6ef5);">
                    <span class="material-symbols-outlined text-white text-[14px]" style="font-variation-settings:'FILL' 1;">pie_chart</span>
                    <h6 class="text-[11px] font-bold text-white m-0">Distribusi Data Modul</h6>
                </div>
                <div class="p-2.5"><div style="height:160px;position:relative;"><canvas id="pieChart"></canvas></div></div>
            </div>
            <div class="bg-white rounded-xl border border-slate-200/60 shadow-sm overflow-hidden">
                <div class="px-3 py-2 flex items-center gap-2" style="background:linear-gradient(135deg,#f59e0b,#d97706);">
                    <span class="material-symbols-outlined text-white text-[14px]" style="font-variation-settings:'FILL' 1;">today</span>
                    <h6 class="text-[11px] font-bold text-white m-0">Data Hari Ini</h6>
                </div>
                <div class="p-2.5"><div style="height:160px;position:relative;"><canvas id="todayChart"></canvas></div></div>
            </div>
        </div>
    </div>
</div>
@endif

</div>

{{-- Chart.js --}}
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
const chartData = @json($chartData);
const chartKebersihan = @json($chartKebersihan ?? ['labels'=>[],'values'=>[],'colors'=>[]]);
const chartSarpras = @json($chartSarpras ?? ['labels'=>[],'values'=>[],'colors'=>[]]);
const chartTrafficArusMudik = @json($chartTrafficArusMudik ?? ['labels'=>[],'datasets'=>[]]);
const chartPenumpang = @json($chartPenumpang ?? ['labels'=>[],'kedatangan'=>[],'keberangkatan'=>[]]);

Chart.defaults.font.family = "'Segoe UI', system-ui, sans-serif";
const scaleBase = {
    y: { beginAtZero: true, ticks: { stepSize: 1, font: { size: 10 } }, grid: { color: 'rgba(0,0,0,.04)' }, border: { display: false } },
    x: { grid: { display: false }, ticks: { font: { size: 10 } }, border: { display: false } }
};

function makeBar(elId, labels, data, colors) {
    const el = document.getElementById(elId);
    if (!el) return null;
    return new Chart(el, {
        type: 'bar',
        data: { labels, datasets: [{ data, backgroundColor: colors, borderColor: colors, borderWidth: 0, borderRadius: 6, borderSkipped: false }] },
        options: {
            responsive: true, maintainAspectRatio: false,
            plugins: { legend: { display: false }, tooltip: { callbacks: { label: ctx => 'Total: ' + ctx.parsed.y } } },
            scales: scaleBase
        }
    });
}

makeBar('chartKebersihanPosyan', chartKebersihan.labels, chartKebersihan.values, chartKebersihan.colors);
makeBar('chartSarprasPosyan', chartSarpras.labels, chartSarpras.values, chartSarpras.colors);

let moreStatsChartsReady = false;
const moreStatsCharts = [];

function initMoreStatsCharts() {
    if (moreStatsChartsReady) return;
    moreStatsChartsReady = true;

    if (document.getElementById('chartTrafficArusMudik')) {
        const ds = chartTrafficArusMudik.datasets?.length ? chartTrafficArusMudik.datasets : [];
        moreStatsCharts.push(new Chart(document.getElementById('chartTrafficArusMudik'), {
            type: 'bar',
            data: { labels: chartTrafficArusMudik.labels || [], datasets: ds },
            options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { position: 'top', labels: { boxWidth: 10, padding: 6, font: { size: 10 } } } }, scales: scaleBase }
        }));
    }
    if (document.getElementById('chartPenumpang') && chartPenumpang.labels?.length) {
        moreStatsCharts.push(new Chart(document.getElementById('chartPenumpang'), {
            type: 'line',
            data: { labels: chartPenumpang.labels, datasets: [
                { label: 'Kedatangan', data: chartPenumpang.kedatangan, borderColor: '#0041c8', backgroundColor: 'rgba(0,65,200,.08)', fill: true, tension: 0.35, pointRadius: 3, pointBackgroundColor: '#0041c8' },
                { label: 'Keberangkatan', data: chartPenumpang.keberangkatan, borderColor: '#22c55e', backgroundColor: 'rgba(34,197,94,.08)', fill: true, tension: 0.35, pointRadius: 3, pointBackgroundColor: '#22c55e' }
            ]},
            options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { position: 'top', labels: { boxWidth: 10, padding: 6, font: { size: 10 } } } }, scales: scaleBase }
        }));
    }

    const totalChart = makeBar('totalChart', chartData.labels, chartData.totals, chartData.colors);
    if (totalChart) moreStatsCharts.push(totalChart);

    if (document.getElementById('trendChart')) {
        moreStatsCharts.push(new Chart(document.getElementById('trendChart'), {
            type: 'line',
            data: { labels: chartData.labels, datasets: [{ label: 'Bulan Ini', data: chartData.this_month, borderColor: '#22c55e', backgroundColor: 'rgba(34,197,94,.1)', borderWidth: 2, fill: true, tension: 0.4, pointRadius: 3 }] },
            options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { position: 'top', labels: { boxWidth: 10, padding: 4, font: { size: 10 } } }, tooltip: { callbacks: { label: ctx => 'Bulan Ini: ' + ctx.parsed.y } } }, scales: { ...scaleBase, x: { ...scaleBase.x, ticks: { font: { size: 9 }, maxRotation: 40 } } } }
        }));
    }
    if (document.getElementById('pieChart')) {
        moreStatsCharts.push(new Chart(document.getElementById('pieChart'), {
            type: 'pie',
            data: { labels: chartData.labels, datasets: [{ data: chartData.totals, backgroundColor: chartData.colors, borderColor: '#fff', borderWidth: 2 }] },
            options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { position: 'right', labels: { boxWidth: 10, padding: 6, font: { size: 10 } } }, tooltip: { callbacks: { label: ctx => { const t = ctx.dataset.data.reduce((a,b)=>a+b,0); return ctx.label+': '+ctx.parsed+' ('+(t>0?((ctx.parsed/t)*100).toFixed(1):0)+'%)'; } } } } }
        }));
    }
    if (document.getElementById('todayChart')) {
        moreStatsCharts.push(new Chart(document.getElementById('todayChart'), {
            type: 'doughnut',
            data: { labels: chartData.labels, datasets: [{ data: chartData.today, backgroundColor: chartData.colors, borderColor: '#fff', borderWidth: 2 }] },
            options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { position: 'right', labels: { boxWidth: 10, padding: 6, font: { size: 10 } } }, tooltip: { callbacks: { label: ctx => { const t = ctx.dataset.data.reduce((a,b)=>a+b,0); return ctx.label+': '+ctx.parsed+' ('+(t>0?((ctx.parsed/t)*100).toFixed(1):0)+'%)'; } } } } }
        }));
    }
}

const toggleBtn = document.getElementById('toggleMoreStatsBtn');
const collapseEl = document.getElementById('moreStatisticsCollapse');
const iconEl = document.getElementById('expandStatsIcon');
if (toggleBtn && collapseEl) {
    toggleBtn.addEventListener('click', () => {
        const willShow = collapseEl.classList.contains('hidden');
        collapseEl.classList.toggle('hidden');
        collapseEl.setAttribute('aria-hidden', willShow ? 'false' : 'true');
        toggleBtn.setAttribute('aria-expanded', willShow ? 'true' : 'false');
        if (iconEl) iconEl.textContent = willShow ? 'expand_less' : 'expand_more';
        if (willShow) {
            initMoreStatsCharts();
            requestAnimationFrame(() => moreStatsCharts.forEach(c => c.resize()));
        }
    });
}
</script>
@endsection
