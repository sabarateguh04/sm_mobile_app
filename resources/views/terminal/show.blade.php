@extends('layouts.app')
@section('title', 'Detail Terminal')
@section('page-title', 'Detail Terminal')

@section('content')
@php
    $fotoRaw = $data->fotoobject ?? null;
    $fotoUrl = null;
    if (!empty($fotoRaw)) {
        $fotoUrl = preg_match('#^https?://#i', $fotoRaw) ? $fotoRaw : \Illuminate\Support\Facades\Storage::disk('public')->url($fotoRaw);
    }
    $fasilitas = !empty($data->fasilitas) ? preg_split('/\s*,\s*/', (string) $data->fasilitas, -1, PREG_SPLIT_NO_EMPTY) : [];
@endphp
<div class="max-w-[900px] mx-auto w-full space-y-4">
    <div class="flex items-center gap-3">
        <a href="{{ route('terminal.index') }}" class="flex items-center gap-1.5 text-slate-500 hover:text-primary text-decoration-none text-label-md font-semibold transition-colors">
            <span class="material-symbols-outlined text-[18px]">arrow_back</span><span>Kembali</span>
        </a>
        <span class="text-slate-300">/</span>
        <span class="text-[13px] font-bold text-on-surface">{{ $data->namaterminal }}</span>
    </div>
    <div class="bg-white rounded-2xl border border-slate-200/60 shadow-sm overflow-hidden">
        <div class="px-5 py-3.5 border-b border-slate-100 bg-slate-50/30 flex items-center justify-between gap-2">
            <div class="flex items-center gap-2">
                <span class="material-symbols-outlined text-primary text-[16px]" style="font-variation-settings: 'FILL' 1;">directions_bus</span>
                <h3 class="text-[13px] font-bold text-slate-800 m-0">{{ $data->namaterminal }}</h3>
            </div>
            @if(!empty($data->jenisterminal))
                <span class="inline-flex items-center px-2.5 py-1 rounded-lg bg-primary/10 text-primary text-[11px] font-bold">{{ $data->jenisterminal }}</span>
            @endif
        </div>
        <div class="p-5">
            <div class="grid grid-cols-1 {{ $fotoUrl ? 'lg:grid-cols-3' : '' }} gap-6">
                <div class="{{ $fotoUrl ? 'lg:col-span-2' : '' }}">
                    <dl class="grid grid-cols-1 md:grid-cols-2 gap-x-6 gap-y-3 mb-0">
                        <div><dt class="text-[11px] font-bold text-slate-500 uppercase tracking-wide mb-0.5">Tanggal</dt><dd class="text-[13px] text-slate-800 font-medium m-0">{{ $data->tanggal ? \Carbon\Carbon::parse($data->tanggal)->format('d/m/Y') : '-' }}</dd></div>
                        <div><dt class="text-[11px] font-bold text-slate-500 uppercase tracking-wide mb-0.5">Jenis Terminal</dt><dd class="text-[13px] text-slate-800 font-medium m-0">{{ $data->jenisterminal ?? '-' }}</dd></div>
                        <div class="md:col-span-2"><dt class="text-[11px] font-bold text-slate-500 uppercase tracking-wide mb-0.5">Nama Terminal</dt><dd class="text-[13px] text-slate-800 font-semibold m-0">{{ $data->namaterminal ?? '-' }}</dd></div>
                        <div class="md:col-span-2"><dt class="text-[11px] font-bold text-slate-500 uppercase tracking-wide mb-0.5">Jalan</dt><dd class="text-[13px] text-slate-800 font-medium m-0">{{ $data->jalan ?? '-' }}</dd></div>
                        <div><dt class="text-[11px] font-bold text-slate-500 uppercase tracking-wide mb-0.5">Contact Person</dt><dd class="text-[13px] text-slate-800 font-medium m-0">{{ $data->namecp ?? '-' }}</dd></div>
                        <div><dt class="text-[11px] font-bold text-slate-500 uppercase tracking-wide mb-0.5">No. Telepon</dt><dd class="text-[13px] text-slate-800 font-medium m-0">{{ $data->cp ?? '-' }}</dd></div>
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
                        @if(!empty($fasilitas))
                        <div class="md:col-span-2"><dt class="text-[11px] font-bold text-slate-500 uppercase tracking-wide mb-0.5">Fasilitas</dt>
                            <dd class="m-0 flex flex-wrap gap-1.5 mt-1">
                                @foreach($fasilitas as $chip)
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-lg bg-primary/10 text-primary text-[11px] font-bold">{{ trim($chip) }}</span>
                                @endforeach
                            </dd>
                        </div>
                        @endif
                        <div><dt class="text-[11px] font-bold text-slate-500 uppercase tracking-wide mb-0.5">Piket</dt><dd class="text-[13px] text-slate-800 font-medium m-0">{{ $data->usm_nama ?? '-' }}</dd></div>
                        <div><dt class="text-[11px] font-bold text-slate-500 uppercase tracking-wide mb-0.5">Wilayah / Daerah</dt><dd class="text-[13px] text-slate-800 font-medium m-0">{{ $data->usm_wilayah ?? '-' }} / {{ $data->usm_daerah ?? '-' }}</dd></div>
                    </dl>
                </div>
                @if($fotoUrl)
                <div>
                    <p class="text-[11px] font-bold text-slate-500 uppercase tracking-wide mb-2">Foto Lokasi</p>
                    <a href="{{ $fotoUrl }}" target="_blank" rel="noopener noreferrer">
                        <img src="{{ $fotoUrl }}" alt="Foto Terminal" class="rounded-xl border border-slate-200 w-full" style="max-height:220px;object-fit:cover;">
                    </a>
                </div>
                @endif
            </div>
            <div class="flex items-center justify-between pt-4 mt-4 border-t border-slate-100 flex-wrap gap-2">
                <a href="{{ route('terminal.index') }}" class="flex items-center gap-2 px-4 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-600 font-bold rounded-xl text-label-md transition-all text-decoration-none">
                    <span class="material-symbols-outlined text-[18px]">arrow_back</span><span>Kembali</span>
                </a>
                <div class="flex items-center gap-2">
                    @include('_partials.show_export_dropdown', ['exportRouteName' => 'terminal.export', 'rowId' => $data->id_terminal])
                    <a href="{{ route('terminal.edit', $data->id_terminal) }}" class="flex items-center gap-2 px-4 py-2.5 bg-amber-50 hover:bg-amber-100 text-amber-700 border border-amber-200 font-bold rounded-xl text-label-md transition-all text-decoration-none">
                        <span class="material-symbols-outlined text-[18px]">edit</span><span>Edit</span>
                    </a>
                    <form action="{{ route('terminal.destroy', $data->id_terminal) }}" method="POST" class="d-inline" onsubmit="return confirm('Yakin hapus data terminal ini?');">
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
