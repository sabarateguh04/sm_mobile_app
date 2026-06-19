@extends('layouts.app')
@php $pageTitle = $pageTitle ?? 'Detail Data Jalan'; @endphp
@section('title', $pageTitle)
@section('page-title', $pageTitle)

@section('content')
@php
    $fotoUrls = \App\Models\DataJalan::publicUrlsForFoto($data->fotoobject ?? null);
    $prasjalChips = !empty($data->prasjal)
        ? array_filter(array_map('trim', preg_split('/\s*,\s*/', (string) $data->prasjal, -1, PREG_SPLIT_NO_EMPTY)))
        : [];
    $canMutate = $canMutateRecord ?? false;
@endphp
<div class="max-w-[900px] mx-auto w-full space-y-4">
    <div class="flex items-center gap-3">
        <a href="{{ route('t_datajalan.index') }}" class="flex items-center gap-1.5 text-slate-500 hover:text-primary text-decoration-none text-label-md font-semibold transition-colors">
            <span class="material-symbols-outlined text-[18px]">arrow_back</span><span>Kembali</span>
        </a>
        <span class="text-slate-300">/</span>
        <span class="text-[13px] font-bold text-on-surface">{{ $data->namajalan ?: 'Detail Data Jalan' }}</span>
    </div>
    <div class="bg-white rounded-2xl border border-slate-200/60 shadow-sm overflow-hidden">
        <div class="px-5 py-3.5 border-b border-slate-100 bg-slate-50/30 flex items-center justify-between gap-2">
            <div class="flex items-center gap-2">
                <span class="material-symbols-outlined text-primary text-[16px]" style="font-variation-settings: 'FILL' 1;">add_road</span>
                <h3 class="text-[13px] font-bold text-slate-800 m-0">{{ $data->namajalan ?: 'Detail Data Jalan' }}</h3>
            </div>
            @if(!empty($data->jenisjalan))
                <span class="inline-flex items-center px-2.5 py-1 rounded-lg bg-primary/10 text-primary text-[11px] font-bold">{{ $data->jenisjalan }}</span>
            @endif
        </div>
        <div class="p-5">
            <div class="grid grid-cols-1 {{ $fotoUrls ? 'lg:grid-cols-3' : '' }} gap-6">
                <div class="{{ $fotoUrls ? 'lg:col-span-2' : '' }}">
                    <dl class="grid grid-cols-1 md:grid-cols-2 gap-x-6 gap-y-3 mb-0">
                        <div><dt class="text-[11px] font-bold text-slate-500 uppercase tracking-wide mb-0.5">Tanggal</dt><dd class="text-[13px] text-slate-800 font-medium m-0">{{ $data->tanggal ? \Carbon\Carbon::parse($data->tanggal)->format('d/m/Y') : '-' }}</dd></div>
                        <div><dt class="text-[11px] font-bold text-slate-500 uppercase tracking-wide mb-0.5">Jenis Jalan</dt><dd class="text-[13px] text-slate-800 font-medium m-0">{{ $data->jenisjalan ?? '-' }}</dd></div>
                        <div class="md:col-span-2"><dt class="text-[11px] font-bold text-slate-500 uppercase tracking-wide mb-0.5">Nama Jalan</dt><dd class="text-[13px] text-slate-800 font-semibold m-0">{{ $data->namajalan ?? '-' }}</dd></div>
                        <div><dt class="text-[11px] font-bold text-slate-500 uppercase tracking-wide mb-0.5">Panjang Jalan</dt><dd class="text-[13px] text-slate-800 font-medium m-0">{{ number_format($data->panjangjalan ?? 0, 0, ',', '.') }} km</dd></div>
                        <div><dt class="text-[11px] font-bold text-slate-500 uppercase tracking-wide mb-0.5">Lintasan</dt><dd class="text-[13px] text-slate-800 font-medium m-0">{{ $data->lintasan ?? '-' }}</dd></div>
                        @if(!empty($prasjalChips))
                        <div class="md:col-span-2"><dt class="text-[11px] font-bold text-slate-500 uppercase tracking-wide mb-0.5">Prasarana Jalan</dt>
                            <dd class="m-0 flex flex-wrap gap-1.5 mt-1">
                                @foreach($prasjalChips as $chip)
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-lg bg-primary/10 text-primary text-[11px] font-bold">{{ $chip }}</span>
                                @endforeach
                            </dd>
                        </div>
                        @elseif(!empty($data->prasjal))
                        <div class="md:col-span-2"><dt class="text-[11px] font-bold text-slate-500 uppercase tracking-wide mb-0.5">Prasarana Jalan</dt><dd class="text-[13px] text-slate-800 font-medium m-0">{{ $data->prasjal }}</dd></div>
                        @endif
                        @if(!empty($data->koordinat))
                        <div class="md:col-span-2"><dt class="text-[11px] font-bold text-slate-500 uppercase tracking-wide mb-0.5">Koordinat GPS</dt>
                            <dd class="m-0 flex items-center gap-2 flex-wrap">
                                <code class="bg-slate-100 px-2 py-0.5 rounded text-[12px]">{{ $data->koordinat }}</code>
                                <a href="https://www.google.com/maps/search/?api=1&query={{ $data->koordinat }}" target="_blank" rel="noopener noreferrer" class="flex items-center gap-1 text-primary hover:underline text-decoration-none text-[12px]">
                                    <span class="material-symbols-outlined text-[14px]">map</span>Google Maps
                                </a>
                            </dd>
                        </div>
                        @endif
                        <div><dt class="text-[11px] font-bold text-slate-500 uppercase tracking-wide mb-0.5">Piket</dt><dd class="text-[13px] text-slate-800 font-medium m-0">{{ $data->usm_nama ?? '-' }}</dd></div>
                        <div><dt class="text-[11px] font-bold text-slate-500 uppercase tracking-wide mb-0.5">Wilayah / Daerah</dt><dd class="text-[13px] text-slate-800 font-medium m-0">{{ $data->wilayah_nama ?? '-' }} / {{ $data->daerah_nama ?? '-' }}</dd></div>
                    </dl>
                </div>
                @if($fotoUrls)
                <div>
                    <p class="text-[11px] font-bold text-slate-500 uppercase tracking-wide mb-2">Foto Lokasi</p>
                    <div class="flex flex-col gap-2">
                        @foreach($fotoUrls as $fotoShow)
                            <a href="{{ $fotoShow }}" target="_blank" rel="noopener noreferrer">
                                <img src="{{ $fotoShow }}" alt="Foto jalan" class="rounded-xl border border-slate-200 w-full" style="max-height:220px;object-fit:cover;">
                            </a>
                        @endforeach
                    </div>
                </div>
                @endif
            </div>
            <div class="flex items-center justify-between pt-4 mt-4 border-t border-slate-100 flex-wrap gap-2">
                <a href="{{ route('t_datajalan.index') }}" class="flex items-center gap-2 px-4 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-600 font-bold rounded-xl text-label-md transition-all text-decoration-none">
                    <span class="material-symbols-outlined text-[18px]">arrow_back</span><span>Kembali</span>
                </a>
                <div class="flex items-center gap-2">
                    @include('_partials.show_export_dropdown', ['exportRouteName' => 't_datajalan.export', 'rowId' => $data->id_datajalan])
                @if($canMutate)
                    <a href="{{ route('t_datajalan.edit', $data->id_datajalan) }}" class="flex items-center gap-2 px-4 py-2.5 bg-amber-50 hover:bg-amber-100 text-amber-700 border border-amber-200 font-bold rounded-xl text-label-md transition-all text-decoration-none">
                        <span class="material-symbols-outlined text-[18px]">edit</span><span>Edit</span>
                    </a>
                    <form action="{{ route('t_datajalan.destroy', $data->id_datajalan) }}" method="POST" class="d-inline" onsubmit="return confirm('Yakin hapus data jalan ini?');">
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
