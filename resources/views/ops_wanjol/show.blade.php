@extends('layouts.app')
@section('title', 'Detail Peristiwa Menonjol')
@section('page-title', 'Detail Peristiwa Menonjol')

@section('content')
<div class="max-w-[900px] mx-auto w-full space-y-4">
    <div class="flex items-center gap-3">
        <a href="{{ route('ops_wanjol.index') }}" class="flex items-center gap-1.5 text-slate-500 hover:text-primary text-decoration-none text-label-md font-semibold transition-colors">
            <span class="material-symbols-outlined text-[18px]">arrow_back</span><span>Kembali</span>
        </a>
        <span class="text-slate-300">/</span>
        <span class="text-[13px] font-bold text-on-surface">Detail Peristiwa Menonjol</span>
    </div>
    <div class="bg-white rounded-2xl border border-slate-200/60 shadow-sm overflow-hidden">
        <div class="px-5 py-3.5 border-b border-slate-100 bg-slate-50/30 flex items-center gap-2">
            <span class="material-symbols-outlined text-primary text-[16px]" style="font-variation-settings: 'FILL' 1;">report</span>
            <h3 class="text-[13px] font-bold text-slate-800 m-0">Detail Peristiwa Menonjol</h3>
        </div>
        <div class="p-5">
            <dl class="grid grid-cols-[140px_1fr] md:grid-cols-[160px_1fr] gap-x-4 gap-y-3 mb-0 items-center">
                <dt class="text-[11px] font-bold text-slate-500 uppercase tracking-wide">Tanggal</dt>
                <dd class="text-[13px] text-slate-800 font-medium m-0">{{ isset($data->tanggal) ? \Carbon\Carbon::parse($data->tanggal)->locale('id')->translatedFormat('d F Y') : '-' }}</dd>
                
                <dt class="text-[11px] font-bold text-slate-500 uppercase tracking-wide">Posko</dt>
                <dd class="text-[13px] text-slate-800 font-medium m-0">{{ $data->posko_nama ?? '-' }}</dd>
                
                <dt class="text-[11px] font-bold text-slate-500 uppercase tracking-wide self-start mt-0.5">Lokasi</dt>
                <dd class="text-[13px] text-slate-800 font-medium m-0">{{ $data->lokasi ?? '-' }}</dd>
                
                <dt class="text-[11px] font-bold text-slate-500 uppercase tracking-wide">Status</dt>
                <dd class="m-0">@if(!empty($data->status))<span class="inline-flex items-center px-2.5 py-1 rounded-lg bg-blue-100 text-blue-700 text-[11px] font-bold">{{ $data->status }}</span>@else<span class="text-[13px] text-slate-800 font-medium">-</span>@endif</dd>
                
                <dt class="text-[11px] font-bold text-slate-500 uppercase tracking-wide self-start mt-0.5">Uraian Peristiwa</dt>
                <dd class="text-[13px] text-slate-800 font-medium m-0 whitespace-pre-wrap">{!! nl2br(e($data->uraian ?? '-')) !!}</dd>
                
                <dt class="text-[11px] font-bold text-slate-500 uppercase tracking-wide">Piket</dt>
                <dd class="text-[13px] text-slate-800 font-medium m-0">{{ $data->usm_nama ?? '-' }}</dd>
                
                <dt class="text-[11px] font-bold text-slate-500 uppercase tracking-wide">Polda</dt>
                <dd class="m-0">@if(!empty($data->daerah))<span class="text-[13px] font-bold" style="color:#D8A808;">{{ strtoupper($data->daerah) }}</span>@else<span class="text-[13px] text-slate-800 font-medium">-</span>@endif</dd>
                
                <dt class="text-[11px] font-bold text-slate-500 uppercase tracking-wide">Polres</dt>
                <dd class="m-0">@if(!empty($data->wilayah))<span class="text-[13px] text-slate-800 font-medium">{{ strtoupper($data->wilayah) }}</span>@else<span class="text-[13px] text-slate-800 font-medium">-</span>@endif</dd>
            </dl>
            <div class="flex items-center justify-between pt-4 mt-4 border-t border-slate-100 flex-wrap gap-2">
                <a href="{{ route('ops_wanjol.index') }}" class="flex items-center gap-2 px-4 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-600 font-bold rounded-xl text-label-md transition-all text-decoration-none">
                    <span class="material-symbols-outlined text-[18px]">arrow_back</span><span>Kembali</span>
                </a>
                <div class="flex items-center gap-2">
                    @include('_partials.show_export_dropdown', ['exportRouteName' => 'ops_wanjol.export', 'rowId' => $data->id_wanjol])
                    <a href="{{ route('ops_wanjol.edit', $data->id_wanjol) }}" class="flex items-center gap-2 px-4 py-2.5 bg-amber-50 hover:bg-amber-100 text-amber-700 border border-amber-200 font-bold rounded-xl text-label-md transition-all text-decoration-none">
                        <span class="material-symbols-outlined text-[18px]">edit</span><span>Edit</span>
                    </a>
                    <form action="{{ route('ops_wanjol.destroy', $data->id_wanjol) }}" method="POST" class="d-inline" onsubmit="return confirm('Yakin hapus data ini?');">
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
