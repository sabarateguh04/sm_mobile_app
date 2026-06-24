@extends('layouts.app')
@section('title', 'Live CCTV')
@section('page-title', 'Live CCTV')

@section('content')
@php
    $hasSearch = request()->filled('search');
@endphp
<div class="max-w-[1400px] mx-auto w-full space-y-4">

<section class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
    <div>
        <h2 class="text-[22px] font-extrabold text-on-surface tracking-tight leading-tight m-0">Live CCTV</h2>
        <p class="text-[11px] text-slate-400 mt-0.5 m-0">Pemantauan kamera real-time dari registry SatuPeta (RTSP)</p>
    </div>
    <a href="{{ route('patrolicctv.index') }}" class="flex items-center gap-2 px-4 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-600 font-bold rounded-xl text-label-md transition-all text-decoration-none shrink-0">
        <span class="material-symbols-outlined text-[18px]">assignment</span>
        <span>Laporan Patroli</span>
    </a>
</section>

@if(!$available)
    <div class="rounded-xl bg-amber-50 border border-amber-200 px-4 py-3 text-amber-900 text-[12px]">
        Database SatuPeta (`cctv`) belum tersedia. Periksa koneksi <code>SATUPETA_DB_*</code> di <code>.env</code>.
    </div>
@elseif(!$streamConfigured)
    <div class="rounded-xl bg-amber-50 border border-amber-200 px-4 py-3 text-amber-900 text-[12px]">
        Gateway stream belum dikonfigurasi. Set <code>URL_STREAM</code> di <code>.env</code> (contoh: <code>https://satupeta.kodal.id:3000/</code>).
    </div>
@endif

<form method="GET" action="{{ route('cctv-live.index') }}" class="sbo-compact-filters bg-white rounded-lg p-2 border border-slate-200/60 shadow-sm">
    <div class="pmf-row">
        @include('_partials.compact_filters.search', ['placeholder' => 'Cari nama / channel CCTV...'])
        @include('_partials.compact_filters.actions', [
            'resetUrl' => route('cctv-live.index'),
            'active' => $hasSearch,
        ])
    </div>
</form>

<section class="bg-white rounded-xl border border-slate-200/60 shadow-sm overflow-hidden">
    @if($available && $cameras->count() > 0)
        <div class="overflow-x-auto custom-scrollbar">
            <table class="w-full text-left min-w-[700px]">
                <thead>
                    <tr class="bg-slate-50/50">
                        <th class="px-4 py-2 text-[10px] font-bold text-slate-400 uppercase tracking-widest border-b border-slate-100 w-10">#</th>
                        <th class="px-3 py-2 text-[10px] font-bold text-slate-400 uppercase tracking-widest border-b border-slate-100">Nama CCTV</th>
                        <th class="px-3 py-2 text-[10px] font-bold text-slate-400 uppercase tracking-widest border-b border-slate-100">Channel</th>
                        <th class="px-3 py-2 text-[10px] font-bold text-slate-400 uppercase tracking-widest border-b border-slate-100 w-28 text-center">Status</th>
                        <th class="px-3 py-2 text-[10px] font-bold text-slate-400 uppercase tracking-widest border-b border-slate-100 w-28 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach($cameras as $i => $cam)
                        <tr class="hover:bg-slate-50/50">
                            <td class="px-4 py-2.5 text-[11px] text-slate-400">{{ $i + 1 }}</td>
                            <td class="px-3 py-2.5">
                                <div class="text-[13px] font-semibold text-slate-800">{{ $cam->nama_cctv ?: '—' }}</div>
                                @if(!empty($cam->kordinat))
                                    @php
                                        $coords = explode(',', $cam->kordinat);
                                        $lat = isset($coords[0]) ? trim($coords[0]) : '';
                                        $lng = isset($coords[1]) ? trim($coords[1]) : '';
                                    @endphp
                                    @if($lat !== '' && $lng !== '')
                                        <div class="flex items-center gap-2 mt-1 text-[11px]">
                                            <a href="{{ route('map.index', ['lat' => $lat, 'lng' => $lng, 'name' => $cam->nama_cctv]) }}" target="_blank" class="inline-flex items-center gap-0.5 text-primary hover:text-primary-dark font-semibold text-decoration-none">
                                                <span class="material-symbols-outlined text-[14px]">map</span>
                                                <span>Peta</span>
                                            </a>
                                            <span class="text-slate-300">|</span>
                                            <a href="https://www.google.com/maps/search/?api=1&query={{ urlencode($cam->kordinat) }}" target="_blank" class="inline-flex items-center gap-0.5 text-emerald-600 hover:text-emerald-700 font-semibold text-decoration-none">
                                                <span class="material-symbols-outlined text-[14px]">explore</span>
                                                <span>Google Maps</span>
                                            </a>
                                            <span class="text-slate-300">|</span>
                                            <span class="text-slate-400 text-[10px] font-mono">{{ $cam->kordinat }}</span>
                                        </div>
                                    @else
                                        <div class="text-[10px] text-slate-400 mt-0.5">{{ $cam->kordinat }}</div>
                                    @endif
                                @endif
                            </td>
                            <td class="px-3 py-2.5 text-[12px] text-slate-600">
                                {{ $cam->channel_name ?: ($cam->channel_id ?: '—') }}
                            </td>
                            <td class="px-3 py-2.5 text-center">
                                @php
                                    $statusLower = strtolower(trim((string)($cam->status ?? '')));
                                    $isOnline = in_array($statusLower, ['aktif', 'online', 'hidup', 'normal', '1']) || empty($statusLower);
                                @endphp
                                @if($isOnline)
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-emerald-50 text-emerald-700 border border-emerald-200/80 text-[10px] font-bold">
                                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                                        <span>Aktif</span>
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-rose-50 text-rose-700 border border-rose-200/80 text-[10px] font-bold">
                                        <span class="w-1.5 h-1.5 rounded-full bg-rose-500"></span>
                                        <span>Mati</span>
                                    </span>
                                @endif
                            </td>
                            <td class="px-3 py-2.5 text-center">
                                <a href="{{ route('cctv-live.show', $cam->id) }}"
                                   class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-primary text-on-primary text-[11px] font-bold text-decoration-none hover:opacity-90 transition-opacity">
                                    <span class="material-symbols-outlined text-[16px]" style="font-variation-settings:'FILL' 1;">play_circle</span>
                                    Live
                                </a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @elseif($available)
        <div class="py-12 text-center px-4">
            <span class="material-symbols-outlined text-slate-300 text-[40px] mb-2">videocam_off</span>
            <p class="text-label-md text-slate-500 font-medium m-0">Tidak ada kamera RTSP dalam cakupan Anda.</p>
        </div>
    @endif
</section>

</div>
@endsection
