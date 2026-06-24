@extends('layouts.app')
@php $pageTitle = $pageTitle ?? 'Detail Kunjungan Wisata'; @endphp
@section('title', $pageTitle)
@section('page-title', $pageTitle)

@section('content')
@php
    $fotoUrl = $fotoUrl ?? \App\Models\KunjunganWisata::resolveFotoUrl($data->fotogiat ?? null);
    $totalKbm = $totalKbm ?? ((int) ($data->jmlhkbm ?? 0) ?: (int) ($data->parkirr2 ?? 0) + (int) ($data->parkirr4 ?? 0));
    $canMutate = $canMutateRecord ?? false;
@endphp
<div class="max-w-[960px] mx-auto w-full space-y-4">
    <div class="flex items-center gap-3">
        <a href="{{ route('kunjunganwisata.index') }}" class="flex items-center gap-1.5 text-slate-500 hover:text-primary text-decoration-none text-label-md font-semibold transition-colors">
            <span class="material-symbols-outlined text-[18px]">arrow_back</span><span>Kembali</span>
        </a>
        <span class="text-slate-300">/</span>
        <span class="text-[13px] font-bold text-on-surface">{{ $data->namawisata ?: 'Detail Kunjungan Wisata' }}</span>
    </div>

    <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
        <div class="bg-white rounded-xl border border-slate-200/60 shadow-sm px-4 py-3">
            <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wide mb-1">Total Pengunjung</p>
            <p class="text-[18px] font-extrabold text-primary mb-0">{{ number_format($data->jmlhpengunjung ?? 0, 0, ',', '.') }}</p>
        </div>
        <div class="bg-white rounded-xl border border-slate-200/60 shadow-sm px-4 py-3">
            <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wide mb-1">Domestik</p>
            <p class="text-[18px] font-extrabold text-emerald-700 mb-0">{{ number_format($data->domestic ?? 0, 0, ',', '.') }}</p>
        </div>
        <div class="bg-white rounded-xl border border-slate-200/60 shadow-sm px-4 py-3">
            <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wide mb-1">Mancanegara</p>
            <p class="text-[18px] font-extrabold text-blue-700 mb-0">{{ number_format($data->mancanegara ?? 0, 0, ',', '.') }}</p>
        </div>
        <div class="bg-white rounded-xl border border-slate-200/60 shadow-sm px-4 py-3">
            <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wide mb-1">Total KBM</p>
            <p class="text-[18px] font-extrabold text-slate-800 mb-0">{{ number_format($totalKbm, 0, ',', '.') }}</p>
        </div>
    </div>

    <div class="bg-white rounded-2xl border border-slate-200/60 shadow-sm overflow-hidden">
        <div class="px-5 py-3.5 border-b border-slate-100 bg-slate-50/30 flex items-center justify-between gap-2">
            <div class="flex items-center gap-2">
                <span class="material-symbols-outlined text-primary text-[16px]" style="font-variation-settings: 'FILL' 1;">camera_outdoor</span>
                <h3 class="text-[13px] font-bold text-slate-800 m-0">{{ $data->namawisata ?: 'Detail Kunjungan Wisata' }}</h3>
            </div>
            @if(!empty($data->katwisata))
                <span class="inline-flex items-center px-2.5 py-1 rounded-lg bg-primary/10 text-primary text-[11px] font-bold">{{ $data->katwisata }}</span>
            @endif
        </div>
        <div class="p-5">
            <div class="flex flex-col gap-6">
                <div>
                    <dl class="grid grid-cols-[140px_1fr] md:grid-cols-[160px_1fr] gap-x-4 gap-y-3 mb-0 items-center">
                        <dt class="text-[11px] font-bold text-slate-500 uppercase tracking-wide">Tanggal</dt>
                        <dd class="text-[13px] text-slate-800 font-medium m-0">{{ $data->tanggal ? \Carbon\Carbon::parse($data->tanggal)->locale('id')->translatedFormat('d F Y') : '-' }}</dd>
                        
                        <dt class="text-[11px] font-bold text-slate-500 uppercase tracking-wide">Kategori Wisata</dt>
                        <dd class="text-[13px] text-slate-800 font-medium m-0">{{ $data->katwisata ?? '-' }}</dd>
                        
                        <dt class="text-[11px] font-bold text-slate-500 uppercase tracking-wide self-start mt-0.5">Nama Objek Wisata</dt>
                        <dd class="text-[13px] text-slate-800 font-semibold m-0">{{ $data->namawisata ?? '-' }}</dd>
                        
                        <dt class="text-[11px] font-bold text-slate-500 uppercase tracking-wide self-start mt-0.5">Koresponden</dt>
                        <dd class="text-[13px] text-slate-800 font-medium m-0">{{ $data->koresponden ?? '-' }}</dd>
                        
                        <dt class="text-[11px] font-bold text-slate-500 uppercase tracking-wide">Parkir R2</dt>
                        <dd class="text-[13px] text-slate-800 font-medium m-0">{{ number_format($data->parkirr2 ?? 0, 0, ',', '.') }} unit</dd>
                        
                        <dt class="text-[11px] font-bold text-slate-500 uppercase tracking-wide">Parkir R4</dt>
                        <dd class="text-[13px] text-slate-800 font-medium m-0">{{ number_format($data->parkirr4 ?? 0, 0, ',', '.') }} unit</dd>
                        
                        <dt class="text-[11px] font-bold text-slate-500 uppercase tracking-wide">Petugas</dt>
                        <dd class="text-[13px] text-slate-800 font-medium m-0">{{ $data->petugas_nama ?? '-' }}</dd>
                        
                        <dt class="text-[11px] font-bold text-slate-500 uppercase tracking-wide">Wilayah / Daerah</dt>
                        <dd class="text-[13px] text-slate-800 font-medium m-0">{{ $data->petugas_wilayah ?? '-' }} / {{ $data->petugas_daerah ?? '-' }}</dd>
                    </dl>
                </div>
                @if($fotoUrl)
                <div class="pt-4 border-t border-slate-100">
                    <p class="text-[11px] font-bold text-slate-500 uppercase tracking-wide mb-2 m-0">Foto Giat</p>
                    <a href="{{ $fotoUrl }}" target="_blank" rel="noopener noreferrer" class="block mt-2">
                        <img src="{{ $fotoUrl }}" alt="Foto giat" class="rounded-xl border border-slate-200 w-full max-w-[400px]" style="max-height:240px;object-fit:cover;">
                    </a>
                </div>
                @endif
            </div>
            <div class="flex items-center justify-between pt-4 mt-4 border-t border-slate-100 flex-wrap gap-2">
                <a href="{{ route('kunjunganwisata.index') }}" class="flex items-center gap-2 px-4 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-600 font-bold rounded-xl text-label-md transition-all text-decoration-none">
                    <span class="material-symbols-outlined text-[18px]">arrow_back</span><span>Kembali</span>
                </a>
                <div class="flex items-center gap-2">
                    @include('_partials.show_export_dropdown', ['exportRouteName' => 'kunjunganwisata.export', 'rowId' => $data->id_kunjunganwisata])
                @if($canMutate)
                    <a href="{{ route('kunjunganwisata.edit', $data->id_kunjunganwisata) }}" class="flex items-center gap-2 px-4 py-2.5 bg-amber-50 hover:bg-amber-100 text-amber-700 border border-amber-200 font-bold rounded-xl text-label-md transition-all text-decoration-none">
                        <span class="material-symbols-outlined text-[18px]">edit</span><span>Edit</span>
                    </a>
                    <form action="{{ route('kunjunganwisata.destroy', $data->id_kunjunganwisata) }}" method="POST" class="d-inline" onsubmit="return confirm('Yakin hapus data kunjungan wisata ini?');">
                        @csrf @method('DELETE')
                        <button type="submit" class="flex items-center gap-2 px-4 py-2.5 bg-red-50 hover:bg-red-100 text-red-700 border border-red-200 font-bold rounded-xl text-label-md transition-all border-0">
                            <span class="material-symbols-outlined text-[18px]">delete</span><span>Hapus</span>
                        </button>
                    </form>
                @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
