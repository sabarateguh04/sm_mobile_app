@extends('layouts.app')
@section('title', $pageTitle)
@section('page-title', $pageTitle)

@section('content')
@php
    $s = strtolower($data->sentimen ?? '');
    $sc = $s === 'positif' ? 'text-green-600 bg-green-50 border-green-200' : ($s === 'negatif' ? 'text-red-600 bg-red-50 border-red-200' : 'text-slate-500 bg-slate-100 border-slate-200');
    $canMutate = ($canMutateRecord ?? false) && !($sboViewOnly ?? false);
@endphp
<div class="w-full max-w-3xl mx-auto space-y-3">
    <div class="flex items-center gap-2 flex-wrap">
        <a href="{{ route('media.analytic.index') }}" class="flex items-center gap-1 text-slate-500 hover:text-primary text-decoration-none text-[12px] font-semibold transition-colors">
            <span class="material-symbols-outlined text-[16px]">arrow_back</span><span>Kembali</span>
        </a>
        <span class="text-slate-300">/</span>
        <span class="text-[12px] font-bold text-on-surface">Detail #{{ $data->id_mediaanalytic }}</span>
    </div>

    <div class="bg-white rounded-xl border border-slate-200/60 shadow-sm overflow-hidden">
        <div class="px-4 py-2.5 border-b border-slate-100 bg-slate-50/40 flex items-center justify-between gap-2">
            <div class="flex items-center gap-2">
                <span class="material-symbols-outlined text-primary text-[15px]" style="font-variation-settings: 'FILL' 1;">bar_chart</span>
                <h3 class="text-[12px] font-bold text-slate-800 m-0">{{ $pageTitle }}</h3>
            </div>
            @if($data->sentimen)
                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold border {{ $sc }}">{{ $data->sentimen }}</span>
            @endif
        </div>
        <div class="p-3 sm:p-4">
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-2 mb-4">
                <div class="rounded-lg border border-slate-200/80 bg-slate-50/50 px-3 py-2">
                    <p class="text-[9px] font-bold text-slate-400 uppercase tracking-wide m-0 mb-0.5">Tanggal</p>
                    <p class="text-[12px] font-bold text-slate-800 m-0">{{ $data->tanggal ? \Carbon\Carbon::parse($data->tanggal)->format('d M Y') : '—' }}</p>
                </div>
                <div class="rounded-lg border border-slate-200/80 bg-slate-50/50 px-3 py-2">
                    <p class="text-[9px] font-bold text-slate-400 uppercase tracking-wide m-0 mb-0.5">Vol. Percakapan</p>
                    <p class="text-[14px] font-bold text-primary m-0">{{ number_format((int) ($data->vol_percakapan ?? 0), 0, ',', '.') }}</p>
                </div>
                <div class="rounded-lg border border-slate-200/80 bg-slate-50/50 px-3 py-2">
                    <p class="text-[9px] font-bold text-slate-400 uppercase tracking-wide m-0 mb-0.5">Sentimen</p>
                    <p class="m-0">
                        @if($data->sentimen)
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold border {{ $sc }}">{{ $data->sentimen }}</span>
                        @else
                            <span class="text-[12px] text-slate-400">—</span>
                        @endif
                    </p>
                </div>
                <div class="rounded-lg border border-slate-200/80 bg-slate-50/50 px-3 py-2">
                    <p class="text-[9px] font-bold text-slate-400 uppercase tracking-wide m-0 mb-0.5">Potensi</p>
                    <p class="text-[12px] font-semibold text-slate-800 m-0">{{ $data->potensi ?: '—' }}</p>
                </div>
            </div>

            <div class="rounded-lg border border-slate-200/80 bg-white px-3 py-2.5">
                <p class="text-[9px] font-bold text-slate-400 uppercase tracking-wide m-0 mb-1">Issue / Topic</p>
                <p class="text-[13px] font-medium text-slate-800 m-0 leading-relaxed">{{ $data->issue_topic ?: '—' }}</p>
            </div>

            <div class="flex items-center justify-between gap-2 pt-3 mt-3 border-t border-slate-100">
                <a href="{{ route('media.analytic.index') }}" class="flex items-center gap-1.5 px-3 py-2 bg-slate-100 hover:bg-slate-200 text-slate-600 font-bold rounded-lg text-[12px] transition-all text-decoration-none">
                    <span class="material-symbols-outlined text-[16px]">arrow_back</span><span>Kembali</span>
                </a>
                <div class="flex items-center gap-2">
                    @include('_partials.show_export_dropdown', ['exportRouteName' => 'media.analytic.export', 'rowId' => $data->id_mediaanalytic])
                @if($canMutate)
                        <a href="{{ route('media.analytic.edit', $data->id_mediaanalytic) }}" class="flex items-center gap-1.5 px-3 py-2 bg-amber-50 hover:bg-amber-100 text-amber-700 border border-amber-200 font-bold rounded-lg text-[12px] transition-all text-decoration-none">
                            <span class="material-symbols-outlined text-[16px]">edit</span><span>Edit</span>
                        </a>
                        <form action="{{ route('media.analytic.destroy', $data->id_mediaanalytic) }}" method="POST" class="m-0" onsubmit="return confirm('Yakin hapus data ini?')">
                            @csrf @method('DELETE')
                            <button type="submit" class="flex items-center gap-1.5 px-3 py-2 bg-red-50 hover:bg-red-100 text-red-700 border border-red-200 font-bold rounded-lg text-[12px] transition-all border-solid">
                                <span class="material-symbols-outlined text-[16px]">delete</span><span>Hapus</span>
                            </button>
                        </form>
                    </div>
                @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
