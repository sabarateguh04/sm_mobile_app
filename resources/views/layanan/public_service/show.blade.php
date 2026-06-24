@extends('layouts.app')
@section('title', $pageTitle)
@section('page-title', $pageTitle)

@section('content')
@php
    $__fp = $data->fotoposyan ?? '';
    $__showImg = $__fp !== '' && (str_starts_with($__fp, 'http') || str_starts_with($__fp, '/storage'));
@endphp
<div class="max-w-[900px] mx-auto w-full space-y-4">
    <div class="flex items-center gap-3">
        <a href="{{ route('layanan.public_service.index', ($readOnly ?? false) ? ['sb_grp' => 'public_service'] : []) }}" class="flex items-center gap-1.5 text-slate-500 hover:text-primary text-decoration-none text-label-md font-semibold transition-colors">
            <span class="material-symbols-outlined text-[18px]">arrow_back</span><span>Kembali</span>
        </a>
        <span class="text-slate-300">/</span>
        <span class="text-[13px] font-bold text-on-surface">Detail Pos Pelayanan</span>
    </div>
    <div class="bg-white rounded-2xl border border-slate-200/60 shadow-sm overflow-hidden">
        <div class="px-5 py-3.5 border-b border-slate-100 bg-slate-50/30 flex items-center gap-2">
            <span class="material-symbols-outlined text-primary text-[16px]" style="font-variation-settings: 'FILL' 1;">medical_services</span>
            <h3 class="text-[13px] font-bold text-slate-800 m-0">Detail Pos Pelayanan</h3>
        </div>
        <div class="p-5">
            <dl class="grid grid-cols-[140px_1fr] md:grid-cols-[160px_1fr] gap-x-4 gap-y-3 mb-0 items-center">
                <dt class="text-[11px] font-bold text-slate-500 uppercase tracking-wide">Tanggal</dt>
                <dd class="text-[13px] text-slate-800 font-medium m-0">{{ $data->tanggal ? \Carbon\Carbon::parse($data->tanggal)->locale('id')->translatedFormat('d F Y') : '-' }}</dd>
                
                <dt class="text-[11px] font-bold text-slate-500 uppercase tracking-wide">Tahun / Jenis Ops</dt>
                <dd class="text-[13px] text-slate-800 font-medium m-0">{{ $data->tahunops ?? '-' }} / {{ $data->jenisops ?? '-' }}</dd>
                
                <dt class="text-[11px] font-bold text-slate-500 uppercase tracking-wide">Nama Ops</dt>
                <dd class="text-[13px] text-slate-800 font-medium m-0">{{ $data->nameops ?? '-' }}</dd>
                
                <dt class="text-[11px] font-bold text-slate-500 uppercase tracking-wide self-start mt-0.5">Sasaran</dt>
                <dd class="text-[13px] text-slate-800 font-medium m-0">{{ $data->sasaranops ?? '-' }}</dd>
                
                <dt class="text-[11px] font-bold text-slate-500 uppercase tracking-wide">Nama Posyan</dt>
                <dd class="text-[13px] text-slate-800 font-medium m-0">{{ $data->namaposyan ?? '-' }}</dd>
                
                <dt class="text-[11px] font-bold text-slate-500 uppercase tracking-wide self-start mt-0.5">Lokasi</dt>
                <dd class="text-[13px] text-slate-800 font-medium m-0">{{ $data->lokasiposyan ?? '-' }}</dd>
                
                <dt class="text-[11px] font-bold text-slate-500 uppercase tracking-wide">Koordinat</dt>
                <dd class="text-[13px] text-slate-800 font-medium m-0"><code class="bg-slate-100 px-2 py-0.5 rounded text-[12px]">{{ $data->koordinat ?? '-' }}</code></dd>
                
                <dt class="text-[11px] font-bold text-slate-500 uppercase tracking-wide self-start mt-0.5">Sarpras</dt>
                <dd class="text-[13px] text-slate-800 font-medium m-0">{{ $data->sarprasposyan ?? '-' }}</dd>
                
                <dt class="text-[11px] font-bold text-slate-500 uppercase tracking-wide">Piket</dt>
                <dd class="text-[13px] text-slate-800 font-medium m-0">{{ $data->usm_nama ?? $data->piket ?? '-' }}</dd>
            </dl>
            
            @if($__showImg)
                <div class="mt-4 pt-4 border-t border-slate-100">
                    <p class="text-[11px] font-bold text-slate-500 uppercase tracking-wide mb-2 m-0">Foto Posyan</p>
                    <div class="mt-2">
                        <a href="{{ str_starts_with($__fp, 'http') ? $__fp : url($__fp) }}" target="_blank" rel="noopener noreferrer" class="block">
                            <img src="{{ str_starts_with($__fp, 'http') ? $__fp : url($__fp) }}" alt="Foto posyan" class="rounded-xl border border-slate-200 object-cover" style="max-height:200px;">
                        </a>
                    </div>
                </div>
            @endif
            
            <div class="flex items-center justify-between pt-4 mt-4 border-t border-slate-100 flex-wrap gap-2">
                <a href="{{ route('layanan.public_service.index', ($readOnly ?? false) ? ['sb_grp' => 'public_service'] : []) }}" class="flex items-center gap-2 px-4 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-600 font-bold rounded-xl text-label-md transition-all text-decoration-none">
                    <span class="material-symbols-outlined text-[18px]">arrow_back</span><span>Kembali</span>
                </a>
                <div class="flex items-center gap-2 flex-wrap">
                    @include('_partials.show_export_dropdown', [
                        'exportRouteName' => 'layanan.public_service.export',
                        'rowId' => $data->id_posyan,
                        'exportQuery' => ($readOnly ?? false) ? ['sb_grp' => 'public_service'] : [],
                    ])
                    @unless($readOnly ?? false)
                        <a href="{{ route('layanan.public_service.edit', $data->id_posyan) }}" class="flex items-center gap-2 px-4 py-2.5 bg-amber-50 hover:bg-amber-100 text-amber-700 border border-amber-200 font-bold rounded-xl text-label-md transition-all text-decoration-none">
                            <span class="material-symbols-outlined text-[18px]">edit</span><span>Edit</span>
                        </a>
                        <form action="{{ route('layanan.public_service.destroy', $data->id_posyan) }}" method="POST" class="d-inline m-0" onsubmit="return confirm('Yakin hapus data ini?');">
                            @csrf @method('DELETE')
                            <button type="submit" class="flex items-center gap-2 px-4 py-2.5 bg-red-50 hover:bg-red-100 text-red-700 border border-red-200 font-bold rounded-xl text-label-md transition-all border-0">
                                <span class="material-symbols-outlined text-[18px]">delete</span><span>Hapus</span>
                            </button>
                        </form>
                    @endunless
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
