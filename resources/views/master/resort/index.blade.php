@extends('layouts.app')

@section('title', 'Master Data Polres')
@section('page-title', 'Master Data Polres')

@section('content')
@php
    $filterActive = request()->hasAny(['search', 'id_daerah']);
@endphp
<div class="max-w-[1200px] mx-auto w-full space-y-4">

<section class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
    <div>
        <h2 class="text-[22px] font-headline-lg font-extrabold text-on-surface tracking-tight leading-tight m-0 uppercase">Master Polres</h2>
        <p class="text-[11px] text-slate-400 mt-0.5 m-0">
            Config SOTK — Polres / Polrestabes
            <a href="{{ route('master.daerah.index') }}" class="text-primary font-semibold ms-1 text-decoration-none">· Polda</a>
        </p>
    </div>
    @if(Auth::user() && \App\Helpers\MenuHelper::isSuperAdminForLayout(Auth::user()))
    <a href="{{ route('master.resort.create', request()->only('id_daerah')) }}"
       class="flex items-center justify-center gap-2 bg-primary text-on-primary px-5 py-2.5 rounded-xl font-bold text-label-md shadow-lg shadow-primary/25 hover:shadow-xl hover:-translate-y-0.5 transition-all text-decoration-none shrink-0">
        <span class="material-symbols-outlined text-[20px]" style="font-variation-settings: 'FILL' 1;">add</span>
        <span>Tambah Polres</span>
    </a>
    @endif
</section>

@if(session('success'))
    <div class="bg-secondary-container text-on-secondary-container border border-secondary/20 rounded-2xl p-4 flex justify-between items-center shadow-sm">
        <div class="flex items-center gap-3">
            <span class="material-symbols-outlined" style="font-variation-settings: 'FILL' 1;">check_circle</span>
            <span class="font-medium text-sm">{{ session('success') }}</span>
        </div>
        <button type="button" class="text-on-secondary-container/50 hover:text-on-secondary-container bg-transparent border-0" onclick="this.parentElement.remove()">
            <span class="material-symbols-outlined">close</span>
        </button>
    </div>
@endif

<form method="GET" action="{{ route('master.resort.index') }}" class="sbo-compact-filters bg-white rounded-lg p-2 border border-slate-200/60 shadow-sm">
    <div class="pmf-row">
        <div class="pmf-territory">
            <select name="id_daerah"
                    class="pmf-control pmf-field-text pmf-native-select w-full h-full bg-slate-50 border border-slate-200 rounded-lg pl-2.5 pr-7 focus:border-primary focus:ring-0 focus:bg-white outline-none transition-all {{ request('id_daerah') ? '' : 'pmf-empty' }}"
                    onchange="this.form.submit()">
                <option value="">Semua Polda</option>
                @foreach($daerah as $d)
                    <option value="{{ $d->id_daerah }}" @selected(request('id_daerah') == $d->id_daerah)>{{ strtoupper($d->namadaerah) }}</option>
                @endforeach
            </select>
        </div>
        <div class="pmf-search relative flex-1 min-w-[180px]">
            <span class="absolute left-2 top-1/2 -translate-y-1/2 material-symbols-outlined text-primary text-[16px] pointer-events-none">search</span>
            <input name="search" value="{{ request('search') }}"
                   class="pmf-control pmf-field-text w-full h-full pl-8 pr-2.5 bg-slate-50 border border-slate-200 focus:border-primary focus:bg-white focus:ring-0 rounded-lg outline-none transition-all uppercase"
                   style="text-transform: uppercase;"
                   placeholder="Cari nama polres..." type="text" autocomplete="off">
        </div>
        <div class="pmf-actions flex items-center gap-1.5">
            <button type="submit" class="pmf-btn px-2 bg-primary hover:bg-primary/90 text-white font-semibold rounded-lg text-[11px] border-0 inline-flex items-center justify-center" title="Cari">
                <span class="material-symbols-outlined text-[16px]">search</span>
            </button>
            @if($filterActive)
                <a href="{{ route('master.resort.index') }}" class="pmf-btn px-2 bg-slate-100 hover:bg-slate-200 text-slate-600 rounded-lg inline-flex items-center justify-center text-decoration-none" title="Reset">
                    <span class="material-symbols-outlined text-[16px]">cached</span>
                </a>
            @endif
        </div>
    </div>
</form>

<section class="bg-white rounded-xl border border-slate-200/60 overflow-hidden shadow-sm">
    <div class="px-4 py-3 border-b border-slate-100 flex items-center justify-between gap-2">
        <h3 class="text-[11px] font-bold uppercase tracking-wider text-slate-700 m-0">Daftar Polres / Polrestabes</h3>
        <span class="text-[10px] font-bold text-slate-500 bg-slate-100 px-2 py-0.5 rounded-full">{{ number_format($resort->total(), 0, ',', '.') }} data</span>
    </div>

    @if($resort->count())
        <div class="overflow-x-auto custom-scrollbar">
            <table class="w-full text-left min-w-[640px]">
                <thead>
                    <tr class="bg-slate-50/50">
                        <th class="px-4 py-2 text-[10px] font-bold text-slate-400 uppercase tracking-widest border-b border-slate-100 w-14">#</th>
                        <th class="px-3 py-2 text-[10px] font-bold text-slate-400 uppercase tracking-widest border-b border-slate-100">Nama Polres</th>
@if(\App\Support\TerritoryIndexFilters::canFilterByDaerah())
                        <th class="px-3 py-2 text-[10px] font-bold text-slate-400 uppercase tracking-widest border-b border-slate-100 w-40">Polda</th>
@endif
                        <th class="px-3 py-2 text-[10px] font-bold text-slate-400 uppercase tracking-widest border-b border-slate-100">Koordinat (Lat/Lng)</th>
                        <th class="px-3 py-2 text-[10px] font-bold text-slate-400 uppercase tracking-widest border-b border-slate-100 text-right w-28">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach($resort as $w)
                        <tr class="hover:bg-slate-50 group">
                            <td class="px-4 py-2.5 text-[11px] text-slate-400 font-medium">
                                {{ $loop->iteration + ($resort->currentPage() - 1) * $resort->perPage() }}
                            </td>
                            <td class="px-3 py-2.5">
                                <p class="text-[13px] font-bold text-slate-800 m-0 uppercase tracking-wide">{{ \App\Support\TerritoryMaster::stripKabupatenKotaFromName($w->namawilayah) }}</p>
                            </td>
                            <td class="px-3 py-2.5">
                                <span class="text-[11px] font-bold text-[#D8A808] uppercase">{{ $w->namadaerah ? strtoupper($w->namadaerah) : '—' }}</span>
                            </td>
                            <td class="px-3 py-2.5">
                                @if(isset($w->lat) && isset($w->lng) && $w->lat && $w->lng)
                                    <span class="text-[11px] font-mono text-slate-600 bg-slate-100 px-2 py-0.5 rounded">{{ $w->lat }}, {{ $w->lng }}</span>
                                @else
                                    <span class="text-[11px] text-slate-400 italic">—</span>
                                @endif
                            </td>
                            <td class="px-3 py-2.5 text-right">
                                @if(Auth::user() && \App\Helpers\MenuHelper::isSuperAdminForLayout(Auth::user()))
                                <div class="flex items-center justify-end gap-1 opacity-100 lg:opacity-0 group-hover:opacity-100 transition-opacity">
                                    <a href="{{ route('master.resort.edit', $w->id_wilayah) }}"
                                       class="w-8 h-8 flex items-center justify-center text-slate-500 hover:bg-slate-100 rounded-lg transition-colors text-decoration-none"
                                       title="Edit">
                                        <span class="material-symbols-outlined text-[20px]">edit</span>
                                    </a>
                                    <form action="{{ route('master.resort.destroy', $w->id_wilayah) }}" method="POST" class="inline m-0"
                                          onsubmit="return confirm(@json('Hapus Polres ' . \App\Support\TerritoryMaster::stripKabupatenKotaFromName($w->namawilayah) . '?'));">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="w-8 h-8 flex items-center justify-center text-slate-400 hover:text-error hover:bg-error-container/20 rounded-lg transition-colors border-0 bg-transparent p-0" title="Hapus">
                                            <span class="material-symbols-outlined text-[20px]">delete</span>
                                        </button>
                                    </form>
                                </div>
                                @else
                                    <span class="text-[10px] text-slate-400">—</span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="px-4 py-2.5 bg-slate-50/50 border-t border-slate-100 flex flex-col sm:flex-row items-center justify-between gap-3">
            <p class="text-[10px] text-slate-500 font-medium m-0">
                <span class="font-bold text-slate-700">{{ $resort->firstItem() ?? 0 }}–{{ $resort->lastItem() ?? 0 }}</span>
                dari {{ number_format($resort->total(), 0, ',', '.') }} Polres
            </p>
            <div class="pagination-container">
                {{ $resort->withQueryString()->onEachSide(1)->links() }}
            </div>
        </div>
    @else
        <div class="py-12 text-center px-4">
            <div class="w-12 h-12 bg-slate-100 rounded-full flex items-center justify-center mx-auto mb-3 text-slate-400">
                <span class="material-symbols-outlined text-[26px]">location_on</span>
            </div>
            <p class="text-label-md text-slate-500 font-medium m-0 uppercase">
                @if(request()->filled('search') || request()->filled('id_daerah'))
                    Tidak ada Polres untuk filter ini.
                @else
                    Belum ada data Polres.
                @endif
            </p>
        </div>
    @endif
</section>

</div>
@endsection
