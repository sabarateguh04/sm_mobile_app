@extends('layouts.app')

@section('title', 'Master Polda')
@section('page-title', 'Master Polda (KL Daerah)')

@section('content')
@php
    $filterActive = request()->filled('search');
@endphp
<div class="max-w-[1200px] mx-auto w-full space-y-4">

<section class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
    <div>
        <h2 class="text-[22px] font-headline-lg font-extrabold text-on-surface tracking-tight leading-tight m-0 uppercase">Master Polda</h2>
        <p class="text-[11px] text-slate-400 mt-0.5 m-0">
            Config SOTK — KL Daerah (induk Sub Org)
            <a href="{{ route('master.kl-resort.index') }}" class="text-primary font-semibold ms-1 text-decoration-none">· Sub Org</a>
            <a href="{{ route('master.pusat.index') }}" class="text-primary font-semibold ms-1 text-decoration-none">· Organisasi</a>
        </p>
    </div>
    @if(Auth::user() && \App\Helpers\MenuHelper::isSuperAdminForLayout(Auth::user()))
    <a href="{{ route('master.kl-daerah.create') }}"
       class="flex items-center justify-center gap-2 bg-primary text-on-primary px-5 py-2.5 rounded-xl font-bold text-label-md shadow-lg shadow-primary/25 hover:shadow-xl hover:-translate-y-0.5 transition-all text-decoration-none shrink-0">
        <span class="material-symbols-outlined text-[20px]" style="font-variation-settings: 'FILL' 1;">add</span>
        <span>Tambah Polda</span>
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

<form method="GET" action="{{ route('master.kl-daerah.index') }}" class="sbo-compact-filters bg-white rounded-lg p-2 border border-slate-200/60 shadow-sm">
    <div class="pmf-row">
        <div class="pmf-search relative flex-1 min-w-[200px]">
            <span class="absolute left-2 top-1/2 -translate-y-1/2 material-symbols-outlined text-primary text-[16px] pointer-events-none">search</span>
            <input name="search" value="{{ request('search') }}"
                   class="pmf-control pmf-field-text w-full h-full pl-8 pr-2.5 bg-slate-50 border border-slate-200 focus:border-primary focus:bg-white focus:ring-0 rounded-lg outline-none transition-all uppercase"
                   style="text-transform: uppercase;"
                   placeholder="Cari Polda / KL Pusat..." type="text" autocomplete="off">
        </div>
        <div class="pmf-actions flex items-center gap-1.5">
            <button type="submit" class="pmf-btn px-2 bg-primary hover:bg-primary/90 text-white font-semibold rounded-lg text-[11px] border-0 inline-flex items-center justify-center" title="Cari">
                <span class="material-symbols-outlined text-[16px]">search</span>
            </button>
            @if($filterActive)
                <a href="{{ route('master.kl-daerah.index') }}" class="pmf-btn px-2 bg-slate-100 hover:bg-slate-200 text-slate-600 rounded-lg inline-flex items-center justify-center text-decoration-none" title="Reset">
                    <span class="material-symbols-outlined text-[16px]">cached</span>
                </a>
            @endif
        </div>
    </div>
</form>

<section class="bg-white rounded-xl border border-slate-200/60 overflow-hidden shadow-sm">
    <div class="px-4 py-3 border-b border-slate-100 flex items-center justify-between gap-2">
        <h3 class="text-[11px] font-bold uppercase tracking-wider text-slate-700 m-0">Daftar Polda (KL Daerah)</h3>
        <span class="text-[10px] font-bold text-slate-500 bg-slate-100 px-2 py-0.5 rounded-full">{{ number_format($rows->total(), 0, ',', '.') }} data</span>
    </div>

    @if($rows->count())
        <div class="overflow-x-auto custom-scrollbar">
            <table class="w-full text-left min-w-[700px]">
                <thead>
                    <tr class="bg-slate-50/50">
                        <th class="px-4 py-2 text-[10px] font-bold text-slate-400 uppercase tracking-widest border-b border-slate-100 w-20">ID</th>
                        <th class="px-3 py-2 text-[10px] font-bold text-slate-400 uppercase tracking-widest border-b border-slate-100">KL Pusat</th>
                        <th class="px-3 py-2 text-[10px] font-bold text-slate-400 uppercase tracking-widest border-b border-slate-100">Nama Polda</th>
                        <th class="px-3 py-2 text-[10px] font-bold text-slate-400 uppercase tracking-widest border-b border-slate-100 text-right w-40">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach($rows as $r)
                        <tr class="hover:bg-slate-50 group">
                            <td class="px-4 py-2.5 text-[11px] text-slate-400 font-medium">{{ $r->id }}</td>
                            <td class="px-3 py-2.5">
                                <p class="text-[11px] text-slate-500 m-0 uppercase">{{ strtoupper($r->pusat_nama) }}</p>
                            </td>
                            <td class="px-3 py-2.5">
                                <p class="text-[13px] font-bold text-slate-800 m-0 uppercase tracking-wide">{{ strtoupper($r->nama) }}</p>
                            </td>
                            <td class="px-3 py-2.5 text-right">
                                <div class="flex items-center justify-end gap-1 opacity-100 lg:opacity-0 group-hover:opacity-100 transition-opacity">
                                    <a href="{{ route('master.pusat.index', ['search' => $r->nama]) }}"
                                       class="w-8 h-8 flex items-center justify-center text-emerald-600 hover:bg-emerald-50 rounded-lg transition-colors text-decoration-none"
                                       title="Hubungkan ke Organisasi">
                                        <span class="material-symbols-outlined text-[20px]">link</span>
                                    </a>
                                    <a href="{{ route('master.kl-resort.index', ['parent_id' => $r->id]) }}"
                                       class="w-8 h-8 flex items-center justify-center text-primary hover:bg-primary/10 rounded-lg transition-colors text-decoration-none"
                                       title="Lihat Sub Org">
                                        <span class="material-symbols-outlined text-[20px]">account_tree</span>
                                    </a>
                                    @if(Auth::user() && \App\Helpers\MenuHelper::isSuperAdminForLayout(Auth::user()))
                                        <a href="{{ route('master.kl-daerah.edit', $r->id) }}"
                                           class="w-8 h-8 flex items-center justify-center text-slate-500 hover:bg-slate-100 rounded-lg transition-colors text-decoration-none"
                                           title="Edit">
                                            <span class="material-symbols-outlined text-[20px]">edit</span>
                                        </a>
                                        <form action="{{ route('master.kl-daerah.destroy', $r->id) }}" method="POST" class="inline m-0"
                                              onsubmit="return confirm('Hapus Polda {{ strtoupper($r->nama) }}? Sub Org di bawahnya ikut terhapus.');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="w-8 h-8 flex items-center justify-center text-slate-400 hover:text-error hover:bg-error-container/20 rounded-lg transition-colors border-0 bg-transparent p-0" title="Hapus">
                                                <span class="material-symbols-outlined text-[20px]">delete</span>
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="px-4 py-2.5 bg-slate-50/50 border-t border-slate-100 flex flex-col sm:flex-row items-center justify-between gap-3">
            <p class="text-[10px] text-slate-500 font-medium m-0">
                <span class="font-bold text-slate-700">{{ $rows->firstItem() ?? 0 }}–{{ $rows->lastItem() ?? 0 }}</span>
                dari {{ number_format($rows->total(), 0, ',', '.') }} polda
            </p>
            <div class="pagination-container">
                {{ $rows->withQueryString()->onEachSide(1)->links() }}
            </div>
        </div>
    @else
        <div class="py-12 text-center px-4">
            <div class="w-12 h-12 bg-slate-100 rounded-full flex items-center justify-center mx-auto mb-3 text-slate-400">
                <span class="material-symbols-outlined text-[26px]">map</span>
            </div>
            <p class="text-label-md text-slate-500 font-medium m-0 uppercase">
                @if($filterActive)
                    Tidak ada data untuk pencarian ini.
                @else
                    Belum ada data polda.
                @endif
            </p>
        </div>
    @endif
</section>

</div>
@endsection
