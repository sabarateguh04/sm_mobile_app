@extends('layouts.app')

@section('title', 'Sub Org — ' . strtoupper($org->nameorg))
@section('page-title', 'Sub Org (KL)')

@section('content')
<div class="max-w-[1200px] mx-auto w-full space-y-4">

<section class="flex flex-col sm:flex-row sm:items-start justify-between gap-3">
    <div>
        <a href="{{ route('master.pusat.index') }}"
           class="inline-flex items-center gap-1 text-[11px] font-bold text-primary text-decoration-none mb-2 uppercase">
            <span class="material-symbols-outlined text-[16px]">arrow_back</span>
            Kembali ke Organisasi
        </a>
        <h2 class="text-[22px] font-headline-lg font-extrabold text-on-surface tracking-tight leading-tight m-0 uppercase">Sub Organisasi</h2>
        <p class="text-[13px] font-bold text-slate-800 mt-1 mb-0 uppercase">{{ strtoupper($org->nameorg) }}</p>
        @if(!empty($org->singkatan))
            <p class="text-[10px] text-slate-400 m-0 mt-0.5 uppercase">{{ strtoupper($org->singkatan) }}</p>
        @endif
        <p class="text-[11px] text-slate-400 mt-1 m-0">
            <span class="font-bold text-primary">{{ number_format($suborgTotal, 0, ',', '.') }}</span>
            sub org
            @if($usesKlResort ?? false)
                <span class="text-slate-400">· data <strong class="text-slate-600">Master KL Resort</strong></span>
            @elseif(($matchedVia ?? '') === 'legacy_suborg')
                <span class="text-slate-400">· data <strong class="text-slate-600">legacy suborg</strong></span>
            @endif
        </p>
    </div>
    @if(($usesKlResort ?? false) && Auth::user() && \App\Helpers\MenuHelper::isSuperAdminForLayout(Auth::user()))
    <a href="{{ route('master.kl-resort.create', $createParentId ? ['parent_id' => $createParentId] : []) }}"
       class="flex items-center justify-center gap-2 bg-primary text-on-primary px-5 py-2.5 rounded-xl font-bold text-label-md shadow-lg shadow-primary/25 hover:shadow-xl hover:-translate-y-0.5 transition-all text-decoration-none shrink-0">
        <span class="material-symbols-outlined text-[20px]" style="font-variation-settings: 'FILL' 1;">add</span>
        <span>Tambah Sub Org</span>
    </a>
    @endif
</section>

@if(session('warning'))
    <div class="rounded-xl px-4 py-2.5 flex items-center gap-2 border border-amber-200 bg-amber-50 text-amber-800 text-label-md font-medium">
        <span class="material-symbols-outlined text-[18px] text-amber-500 shrink-0">warning</span>
        {{ session('warning') }}
    </div>
@endif

@if(empty($klDaerahIds) && ($usesKlResort ?? false) === false && ($suborgTotal ?? 0) === 0)
    <div class="rounded-xl px-4 py-2.5 flex items-center gap-2 border border-amber-200 bg-amber-50 text-amber-800 text-label-md font-medium">
        <span class="material-symbols-outlined text-[18px] text-amber-500 shrink-0">info</span>
        Belum ada sub org. Samakan nama organisasi <strong>{{ strtoupper($org->nameorg) }}</strong> dengan
        <a href="{{ route('master.kl-daerah.index') }}" class="text-primary font-bold text-decoration-none">KL Daerah</a>
        atau tambah di
        <a href="{{ route('master.kl-resort.index') }}" class="text-primary font-bold text-decoration-none">Master KL Resort</a>.
    </div>
@elseif(empty($klDaerahIds) && ($usesKlResort ?? false) === false && ($suborgTotal ?? 0) > 0)
    <div class="rounded-xl px-4 py-2.5 flex items-center gap-2 border border-sky-200 bg-sky-50 text-sky-800 text-label-md font-medium">
        <span class="material-symbols-outlined text-[18px] text-sky-500 shrink-0">info</span>
        Data dari tabel legacy. Untuk sinkron KL, buat entri dengan nama yang sama di
        <a href="{{ route('master.kl-daerah.index') }}" class="text-primary font-bold text-decoration-none">KL Daerah</a>.
    </div>
@endif

<form method="GET" action="{{ route('master.pusat.sub', $org->id_org) }}" class="sbo-compact-filters bg-white rounded-lg p-2 border border-slate-200/60 shadow-sm">
    <div class="pmf-row">
        <div class="pmf-search relative flex-1 min-w-[200px]">
            <span class="absolute left-2 top-1/2 -translate-y-1/2 material-symbols-outlined text-primary text-[16px] pointer-events-none">search</span>
            <input name="search" value="{{ request('search') }}"
                   class="pmf-control pmf-field-text w-full h-full pl-8 pr-2.5 bg-slate-50 border border-slate-200 focus:border-primary focus:bg-white focus:ring-0 rounded-lg outline-none transition-all uppercase"
                   style="text-transform: uppercase;"
                   placeholder="Cari nama sub org..." type="text" autocomplete="off">
        </div>
        <div class="pmf-actions flex items-center gap-1.5">
            <button type="submit" class="pmf-btn px-2 bg-primary hover:bg-primary/90 text-white font-semibold rounded-lg text-[11px] border-0 inline-flex items-center justify-center" title="Cari">
                <span class="material-symbols-outlined text-[16px]">search</span>
            </button>
            @if(request()->filled('search'))
                <a href="{{ route('master.pusat.sub', $org->id_org) }}" class="pmf-btn px-2 bg-slate-100 hover:bg-slate-200 text-slate-600 rounded-lg inline-flex items-center justify-center text-decoration-none" title="Reset">
                    <span class="material-symbols-outlined text-[16px]">cached</span>
                </a>
            @endif
        </div>
    </div>
</form>

<section class="bg-white rounded-xl border border-slate-200/60 overflow-hidden shadow-sm">
    <div class="px-4 py-3 border-b border-slate-100 flex flex-wrap items-center justify-between gap-2">
        <h3 class="text-[11px] font-bold uppercase tracking-wider text-slate-700 m-0">Daftar Sub Org</h3>
        <div class="flex items-center gap-2">
            <span class="text-[10px] font-bold text-slate-500 bg-slate-100 px-2 py-0.5 rounded-full">{{ number_format($suborg->total(), 0, ',', '.') }} data</span>
            @if($usesKlResort ?? false)
                <a href="{{ route('master.kl-resort.index', count($klDaerahIds ?? []) === 1 ? ['parent_id' => $klDaerahIds[0]] : []) }}"
                   class="text-[10px] font-bold text-primary text-decoration-none">Buka di Master KL</a>
            @endif
        </div>
    </div>

    @if($suborg->count())
        <div class="overflow-x-auto custom-scrollbar">
            <table class="w-full text-left min-w-[560px]">
                <thead>
                    <tr class="bg-slate-50/50">
                        <th class="px-4 py-2 text-[10px] font-bold text-slate-400 uppercase tracking-widest border-b border-slate-100 w-14">#</th>
                        <th class="px-3 py-2 text-[10px] font-bold text-slate-400 uppercase tracking-widest border-b border-slate-100">Nama Sub Org</th>
                        @if($usesKlResort ?? false)
                            <th class="px-3 py-2 text-[10px] font-bold text-slate-400 uppercase tracking-widest border-b border-slate-100 w-36">Induk KL</th>
                        @endif
                        <th class="px-3 py-2 text-[10px] font-bold text-slate-400 uppercase tracking-widest border-b border-slate-100 text-right w-28">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach($suborg as $s)
                        <tr class="hover:bg-slate-50 group">
                            <td class="px-4 py-2.5 text-[11px] text-slate-400 font-medium">
                                {{ $loop->iteration + ($suborg->currentPage() - 1) * $suborg->perPage() }}
                            </td>
                            <td class="px-3 py-2.5">
                                <p class="text-[13px] font-bold text-slate-800 m-0 uppercase tracking-wide">{{ strtoupper($s->namesuborg) }}</p>
                            </td>
                            @if($usesKlResort ?? false)
                                <td class="px-3 py-2.5">
                                    @if(!empty($s->pusat_nama))
                                        <p class="text-[10px] text-slate-400 m-0 uppercase">{{ strtoupper($s->pusat_nama) }}</p>
                                    @endif
                                    <p class="text-[11px] font-bold text-[#D8A808] m-0 uppercase">{{ strtoupper($s->daerah_nama ?? $org->nameorg) }}</p>
                                </td>
                            @endif
                            <td class="px-3 py-2.5 text-right">
                                @if(($usesKlResort ?? false) && !empty($s->id) && Auth::user() && \App\Helpers\MenuHelper::isSuperAdminForLayout(Auth::user()))
                                    <div class="flex items-center justify-end gap-1 opacity-100 lg:opacity-0 group-hover:opacity-100 transition-opacity">
                                        <a href="{{ route('master.kl-resort.edit', $s->id) }}"
                                           class="w-8 h-8 flex items-center justify-center text-slate-500 hover:bg-slate-100 rounded-lg transition-colors text-decoration-none"
                                           title="Edit">
                                            <span class="material-symbols-outlined text-[20px]">edit</span>
                                        </a>
                                        <a href="{{ route('master.unit-kerja.index', ['parent_id' => $s->id]) }}"
                                           class="w-8 h-8 flex items-center justify-center text-primary hover:bg-primary/10 rounded-lg transition-colors text-decoration-none"
                                           title="Lihat unit kerja">
                                            <span class="material-symbols-outlined text-[20px]">account_tree</span>
                                        </a>
                                        <form action="{{ route('master.kl-resort.destroy', $s->id) }}" method="POST" class="inline m-0"
                                              onsubmit="return confirm('Hapus sub org {{ strtoupper($s->namesuborg) }}?');">
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
                <span class="font-bold text-slate-700">{{ $suborg->firstItem() ?? 0 }}–{{ $suborg->lastItem() ?? 0 }}</span>
                dari {{ number_format($suborg->total(), 0, ',', '.') }} sub org
            </p>
            <div class="pagination-container">
                {{ $suborg->withQueryString()->onEachSide(1)->links() }}
            </div>
        </div>
    @else
        <div class="py-12 text-center px-4">
            <div class="w-12 h-12 bg-slate-100 rounded-full flex items-center justify-center mx-auto mb-3 text-slate-400">
                <span class="material-symbols-outlined text-[26px]">account_tree</span>
            </div>
            <p class="text-label-md text-slate-500 font-medium m-0 uppercase">
                @if(request()->filled('search'))
                    Tidak ada sub org untuk pencarian ini.
                @else
                    Belum ada sub org untuk organisasi ini.
                @endif
            </p>
            @if(($usesKlResort ?? false) && Auth::user() && \App\Helpers\MenuHelper::isSuperAdminForLayout(Auth::user()))
                <a href="{{ route('master.kl-resort.create', $createParentId ? ['parent_id' => $createParentId] : []) }}"
                   class="inline-flex items-center gap-1 mt-3 text-[11px] font-bold text-primary text-decoration-none uppercase">
                    <span class="material-symbols-outlined text-[16px]">add</span>
                    Tambah di Master KL Resort
                </a>
            @endif
        </div>
    @endif
</section>

</div>
@endsection
