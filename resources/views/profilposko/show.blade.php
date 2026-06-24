@extends('layouts.app')

@section('title', 'Detail Profile Posko')
@section('page-title', 'Detail Profile Posko')

@section('content')
<div class="max-w-[900px] mx-auto w-full space-y-4">
    <div class="flex items-center gap-3">
        <a href="{{ route('profilposko.index') }}" class="flex items-center gap-1.5 text-slate-500 hover:text-primary text-decoration-none text-label-md font-semibold transition-colors">
            <span class="material-symbols-outlined text-[18px]">arrow_back</span><span>Kembali</span>
        </a>
        <span class="text-slate-300">/</span>
        <span class="text-[13px] font-bold text-on-surface">{{ $data->namaposko ?: 'Detail Profile Posko' }}</span>
    </div>
    <div class="bg-white rounded-2xl border border-slate-200/60 shadow-sm overflow-hidden">
        <div class="px-5 py-3.5 border-b border-slate-100 bg-slate-50/30 flex items-center justify-between gap-2">
            <div class="flex items-center gap-2">
                <span class="material-symbols-outlined text-primary text-[16px]" style="font-variation-settings: 'FILL' 1;">home_work</span>
                <h3 class="text-[13px] font-bold text-slate-800 m-0">{{ $data->namaposko ?: 'Detail Profile Posko' }}</h3>
            </div>
            @if(!empty($data->jenisops))
                <span class="inline-flex items-center px-2.5 py-1 rounded-lg bg-primary/10 text-primary text-[11px] font-bold">{{ $data->jenisops }}</span>
            @endif
        </div>
        <div class="p-5">
            <dl class="grid grid-cols-[140px_1fr] md:grid-cols-[160px_1fr] gap-x-4 gap-y-3 mb-0 items-center">
                <dt class="text-[11px] font-bold text-slate-500 uppercase tracking-wide">Nama Posko</dt>
                <dd class="text-[13px] text-slate-800 font-semibold m-0">{{ $data->namaposko ?? '-' }}</dd>
                
                <dt class="text-[11px] font-bold text-slate-500 uppercase tracking-wide">Kategori Ops</dt>
                <dd class="text-[13px] text-slate-800 font-medium m-0">{{ $data->katops ?? '-' }}</dd>
                
                <dt class="text-[11px] font-bold text-slate-500 uppercase tracking-wide">Jenis Ops</dt>
                <dd class="text-[13px] text-slate-800 font-medium m-0">{{ $data->jenisops ?? '-' }}</dd>
                
                <dt class="text-[11px] font-bold text-slate-500 uppercase tracking-wide">Tahun Ops</dt>
                <dd class="text-[13px] text-slate-800 font-medium m-0">{{ $data->tahunops ?? '-' }}</dd>
                
                <dt class="text-[11px] font-bold text-slate-500 uppercase tracking-wide">Polres</dt>
                <dd class="text-[13px] text-slate-800 font-medium m-0">{{ $data->wilayah ?? '-' }}</dd>
                
                <dt class="text-[11px] font-bold text-slate-500 uppercase tracking-wide">Polda</dt>
                <dd class="text-[13px] text-slate-800 font-medium m-0">{{ $data->daerah ?? '-' }}</dd>
                
                <dt class="text-[11px] font-bold text-slate-500 uppercase tracking-wide">Piket</dt>
                <dd class="text-[13px] text-slate-800 font-medium m-0">{{ $data->usm_nama ?? '-' }}</dd>
                
                <dt class="text-[11px] font-bold text-slate-500 uppercase tracking-wide">Tema Ops</dt>
                <dd class="text-[13px] text-slate-800 font-medium m-0">{{ $data->temaops ?? '-' }}</dd>
                
                <dt class="text-[11px] font-bold text-slate-500 uppercase tracking-wide self-start mt-0.5">Lokasi Posko</dt>
                <dd class="text-[13px] text-slate-800 font-medium m-0">{!! nl2br(e($data->lokasiposko ?? '-')) !!}</dd>
                
                <dt class="text-[11px] font-bold text-slate-500 uppercase tracking-wide self-start mt-0.5">Alamat</dt>
                <dd class="text-[13px] text-slate-800 font-medium m-0">{{ $data->alamat ?? '-' }}</dd>
                
                <dt class="text-[11px] font-bold text-slate-500 uppercase tracking-wide self-start mt-0.5">Sarana Prasarana</dt>
                <dd class="text-[13px] text-slate-800 font-medium m-0">{!! nl2br(e($data->sarpras ?? '-')) !!}</dd>
                
                <dt class="text-[11px] font-bold text-slate-500 uppercase tracking-wide self-start mt-0.5">Keterangan</dt>
                <dd class="text-[13px] text-slate-800 font-medium m-0">{!! nl2br(e($data->keterangan ?? '-')) !!}</dd>
            </dl>
            <div class="flex items-center justify-between pt-4 mt-4 border-t border-slate-100 flex-wrap gap-2">
                <a href="{{ route('profilposko.index') }}" class="flex items-center gap-2 px-4 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-600 font-bold rounded-xl text-label-md transition-all text-decoration-none">
                    <span class="material-symbols-outlined text-[18px]">arrow_back</span><span>Kembali</span>
                </a>
                <div class="flex items-center gap-2">
                    @include('_partials.show_export_dropdown', ['exportRouteName' => 'profilposko.export', 'rowId' => $data->id_profilposko])
                    <a href="{{ route('profilposko.edit', $data->id_profilposko) }}" class="flex items-center gap-2 px-4 py-2.5 bg-amber-50 hover:bg-amber-100 text-amber-700 border border-amber-200 font-bold rounded-xl text-label-md transition-all text-decoration-none">
                        <span class="material-symbols-outlined text-[18px]">edit</span><span>Edit</span>
                    </a>
                    <form action="{{ route('profilposko.destroy', $data->id_profilposko) }}" method="POST" class="d-inline" onsubmit="return confirm('Yakin hapus?');">
                        @csrf @method('DELETE')
                        <button type="submit" class="flex items-center gap-2 px-4 py-2.5 bg-red-50 hover:bg-red-100 text-red-700 border border-red-200 font-bold rounded-xl text-label-md transition-all border-0">
                            <span class="material-symbols-outlined text-[18px]">delete</span><span>Hapus</span>
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
