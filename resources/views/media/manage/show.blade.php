@extends('layouts.app')
@section('title', $pageTitle)
@section('page-title', $pageTitle)

@section('content')
@php
    $fotodoc = $data->fotodoc ?? '';
    $fotodocImg = $fotodoc !== '' && (str_starts_with($fotodoc, 'http') || str_starts_with($fotodoc, '/storage'));
    $fotodocSrc = str_starts_with($fotodoc, 'http') ? $fotodoc : url($fotodoc);
    $canMutate = ($canMutateRecord ?? false) && !($sboViewOnly ?? false);
@endphp
<div class="w-full max-w-3xl mx-auto space-y-3">
    <div class="flex items-center gap-2 flex-wrap">
        <a href="{{ route('media.manage.index') }}" class="flex items-center gap-1 text-slate-500 hover:text-primary text-decoration-none text-[12px] font-semibold transition-colors">
            <span class="material-symbols-outlined text-[16px]">arrow_back</span><span>Kembali</span>
        </a>
        <span class="text-slate-300">/</span>
        <span class="text-[12px] font-bold text-on-surface">Detail #{{ $data->id_mediapublishing }}</span>
    </div>

    <div class="bg-white rounded-xl border border-slate-200/60 shadow-sm overflow-hidden">
        <div class="px-4 py-2.5 border-b border-slate-100 bg-slate-50/40 flex items-center justify-between gap-2">
            <div class="flex items-center gap-2">
                <span class="material-symbols-outlined text-primary text-[15px]" style="font-variation-settings: 'FILL' 1;">newspaper</span>
                <h3 class="text-[12px] font-bold text-slate-800 m-0">{{ $pageTitle }}</h3>
            </div>
            @if($data->kategori_konten)
                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-primary/8 text-primary border border-primary/15">{{ $data->kategori_konten }}</span>
            @endif
        </div>
        <div class="p-3 sm:p-4">
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-2 mb-4">
                <div class="rounded-lg border border-slate-200/80 bg-slate-50/50 px-3 py-2">
                    <p class="text-[9px] font-bold text-slate-400 uppercase tracking-wide m-0 mb-0.5">Tanggal</p>
                    <p class="text-[12px] font-bold text-slate-800 m-0">{{ $data->tanggal ? \Carbon\Carbon::parse($data->tanggal)->format('d M Y') : '—' }}</p>
                </div>
                <div class="rounded-lg border border-slate-200/80 bg-slate-50/50 px-3 py-2">
                    <p class="text-[9px] font-bold text-slate-400 uppercase tracking-wide m-0 mb-0.5">Piket</p>
                    <p class="text-[12px] font-semibold text-slate-800 m-0">{{ $data->usm_nama ?? ($data->piket ?: '—') }}</p>
                </div>
                <div class="rounded-lg border border-slate-200/80 bg-slate-50/50 px-3 py-2">
                    <p class="text-[9px] font-bold text-slate-400 uppercase tracking-wide m-0 mb-0.5">Polda</p>
                    <p class="text-[12px] font-bold m-0" style="color:#D8A808;">{{ strtoupper($data->daerah ?: '—') }}</p>
                </div>
                <div class="rounded-lg border border-slate-200/80 bg-slate-50/50 px-3 py-2">
                    <p class="text-[9px] font-bold text-slate-400 uppercase tracking-wide m-0 mb-0.5">Polres</p>
                    <p class="text-[12px] font-semibold text-slate-800 m-0">{{ !empty($data->wilayah) ? strtoupper(trim(str_ireplace(['KABUPATEN ', 'KOTA ', 'KAB. '], '', $data->wilayah))) : '—' }}</p>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 mb-4">
                <div class="rounded-lg border border-slate-200/80 bg-white px-3 py-2.5">
                    <p class="text-[9px] font-bold text-slate-400 uppercase tracking-wide m-0 mb-1">Tema</p>
                    <p class="text-[13px] font-medium text-slate-800 m-0 leading-relaxed">{{ $data->tema ?: '—' }}</p>
                </div>
                <div class="rounded-lg border border-slate-200/80 bg-white px-3 py-2.5">
                    <p class="text-[9px] font-bold text-slate-400 uppercase tracking-wide m-0 mb-1">Metode / Media</p>
                    <p class="text-[12px] text-slate-700 m-0">{{ $data->metode ?: '—' }} · {{ $data->media_publishing ?: '—' }}</p>
                </div>
            </div>

            <div class="rounded-lg border border-slate-200/80 bg-white px-3 py-2.5 mb-4">
                <p class="text-[9px] font-bold text-slate-400 uppercase tracking-wide m-0 mb-1">Links</p>
                @if($data->links && filter_var($data->links, FILTER_VALIDATE_URL))
                    <a href="{{ $data->links }}" target="_blank" rel="noopener noreferrer" class="text-primary hover:underline text-[12px] break-all text-decoration-none">{{ $data->links }}</a>
                @else
                    <p class="text-[12px] text-slate-700 m-0">{{ $data->links ?: '—' }}</p>
                @endif
            </div>

            @if($fotodocImg)
                <div class="rounded-lg border border-slate-200/80 bg-slate-50/50 px-3 py-2.5 mb-4">
                    <p class="text-[9px] font-bold text-slate-400 uppercase tracking-wide m-0 mb-2">Foto Dokumentasi</p>
                    <a href="{{ $fotodocSrc }}" target="_blank" rel="noopener noreferrer">
                        <img src="{{ $fotodocSrc }}" alt="Foto Doc" class="rounded-lg border border-slate-200 max-h-[180px] w-full object-cover">
                    </a>
                </div>
            @endif

            <div class="flex items-center justify-between gap-2 pt-3 mt-3 border-t border-slate-100">
                <a href="{{ route('media.manage.index') }}" class="flex items-center gap-1.5 px-3 py-2 bg-slate-100 hover:bg-slate-200 text-slate-600 font-bold rounded-lg text-[12px] transition-all text-decoration-none">
                    <span class="material-symbols-outlined text-[16px]">arrow_back</span><span>Kembali</span>
                </a>
                <div class="flex items-center gap-2">
                    @include('_partials.show_export_dropdown', ['exportRouteName' => 'media.manage.export', 'rowId' => $data->id_mediapublishing])
                @if($canMutate)
                        <a href="{{ route('media.manage.edit', $data->id_mediapublishing) }}" class="flex items-center gap-1.5 px-3 py-2 bg-amber-50 hover:bg-amber-100 text-amber-700 border border-amber-200 font-bold rounded-lg text-[12px] transition-all text-decoration-none">
                            <span class="material-symbols-outlined text-[16px]">edit</span><span>Edit</span>
                        </a>
                        <form action="{{ route('media.manage.destroy', $data->id_mediapublishing) }}" method="POST" class="m-0" onsubmit="return confirm('Yakin hapus data ini?')">
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
