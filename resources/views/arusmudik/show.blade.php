@extends('layouts.app')
@section('title', 'Detail Arus Mudik & Balik')
@section('page-title', 'Detail Arus Mudik & Balik')

@section('content')
<div class="max-w-[900px] mx-auto w-full space-y-4">
    <div class="flex items-center gap-3">
        <a href="{{ route('arusmudik.index') }}" class="flex items-center gap-1.5 text-slate-500 hover:text-primary text-decoration-none text-label-md font-semibold transition-colors">
            <span class="material-symbols-outlined text-[18px]">arrow_back</span><span>Kembali</span>
        </a>
        <span class="text-slate-300">/</span>
        <span class="text-[13px] font-bold text-on-surface">Detail Arus Mudik &amp; Balik</span>
    </div>
    <div class="bg-white rounded-2xl border border-slate-200/60 shadow-sm overflow-hidden">
        <div class="px-5 py-3.5 border-b border-slate-100 bg-slate-50/30 flex items-center gap-2">
            <span class="material-symbols-outlined text-primary text-[16px]" style="font-variation-settings: 'FILL' 1;">directions_car</span>
            <h3 class="text-[13px] font-bold text-slate-800 m-0">Detail Arus Mudik &amp; Balik</h3>
        </div>
        <div class="p-5">
            <dl class="grid grid-cols-1 md:grid-cols-2 gap-x-8 gap-y-3 mb-0">
                <div><dt class="text-[11px] font-bold text-slate-500 uppercase tracking-wide mb-0.5">Tanggal</dt><dd class="text-[13px] text-slate-800 font-medium m-0">{{ isset($data->tanggal) ? \Carbon\Carbon::parse($data->tanggal)->format('d/m/Y') : '-' }}</dd></div>
                <div><dt class="text-[11px] font-bold text-slate-500 uppercase tracking-wide mb-0.5">Polda</dt>
                    <dd class="m-0">@if(!empty($data->daerah))<span class="inline-flex items-center px-2.5 py-1 rounded-lg bg-amber-100 text-amber-700 text-[11px] font-bold">{{ $data->daerah }}</span>@else<span class="text-slate-400 text-[13px]">-</span>@endif</dd>
                </div>
                <div><dt class="text-[11px] font-bold text-slate-500 uppercase tracking-wide mb-0.5">Polres</dt>
                    <dd class="m-0">@if(!empty($data->wilayah))<span class="inline-flex items-center px-2.5 py-1 rounded-lg bg-slate-100 text-slate-700 text-[11px] font-bold">{{ $data->wilayah }}</span>@else<span class="text-slate-400 text-[13px]">-</span>@endif</dd>
                </div>
                <div><dt class="text-[11px] font-bold text-slate-500 uppercase tracking-wide mb-0.5">Piket</dt><dd class="text-[13px] text-slate-800 font-medium m-0">{{ $data->usm_nama ?? $data->piket ?? '-' }}</dd></div>
                <div><dt class="text-[11px] font-bold text-slate-500 uppercase tracking-wide mb-0.5">Profile Posko</dt><dd class="text-[13px] text-slate-800 font-medium m-0">{{ $data->id_profileposko ?? '-' }}</dd></div>
                <div><dt class="text-[11px] font-bold text-slate-500 uppercase tracking-wide mb-0.5">Status Arus</dt>
                    <dd class="m-0">@if(!empty($data->statarus))<span class="inline-flex items-center px-2.5 py-1 rounded-lg bg-blue-100 text-blue-700 text-[11px] font-bold">{{ $data->statarus }}</span>@else<span class="text-slate-400 text-[13px]">-</span>@endif</dd>
                </div>
            </dl>
            <div class="flex items-center justify-between pt-4 mt-4 border-t border-slate-100 flex-wrap gap-2">
                <a href="{{ route('arusmudik.index') }}" class="flex items-center gap-2 px-4 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-600 font-bold rounded-xl text-label-md transition-all text-decoration-none">
                    <span class="material-symbols-outlined text-[18px]">arrow_back</span><span>Kembali</span>
                </a>
                <div class="flex items-center gap-2">
                    @include('_partials.show_export_dropdown', ['exportRouteName' => 'arusmudik.export', 'rowId' => $data->id_arusmudik])
                    <a href="{{ route('arusmudik.edit', $data->id_arusmudik) }}" class="flex items-center gap-2 px-4 py-2.5 bg-amber-50 hover:bg-amber-100 text-amber-700 border border-amber-200 font-bold rounded-xl text-label-md transition-all text-decoration-none">
                        <span class="material-symbols-outlined text-[18px]">edit</span><span>Edit</span>
                    </a>
                    <form action="{{ route('arusmudik.destroy', $data->id_arusmudik) }}" method="POST" class="d-inline" onsubmit="return confirm('Yakin hapus data ini?');">
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
