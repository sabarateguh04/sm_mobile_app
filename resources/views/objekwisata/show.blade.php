@extends('layouts.app')
@section('title', 'Detail Objek Wisata')
@section('page-title', 'Detail Objek Wisata')

@section('content')
@php
    $fotoUrl = null;
    if (!empty($data->fotoobject)) {
        $fotoUrl = preg_match('#^https?://#i', $data->fotoobject)
            ? $data->fotoobject
            : \Illuminate\Support\Facades\Storage::disk('public')->url($data->fotoobject);
    }
@endphp
<div class="max-w-[900px] mx-auto w-full space-y-4">
    <div class="flex items-center gap-3">
        <a href="{{ route('objekwisata.index') }}" class="flex items-center gap-1.5 text-slate-500 hover:text-primary text-decoration-none text-label-md font-semibold transition-colors">
            <span class="material-symbols-outlined text-[18px]">arrow_back</span><span>Kembali</span>
        </a>
        <span class="text-slate-300">/</span>
        <span class="text-[13px] font-bold text-on-surface">{{ $data->nametempat }}</span>
    </div>
    <div class="bg-white rounded-2xl border border-slate-200/60 shadow-sm overflow-hidden">
        <div class="px-5 py-3.5 border-b border-slate-100 bg-slate-50/30 flex items-center justify-between gap-2">
            <div class="flex items-center gap-2">
                <span class="material-symbols-outlined text-primary text-[16px]" style="font-variation-settings: 'FILL' 1;">landscape</span>
                <h3 class="text-[13px] font-bold text-slate-800 m-0">{{ $data->nametempat }}</h3>
            </div>
            @if(!empty($data->kattempatwisata))
                <span class="inline-flex items-center px-2.5 py-1 rounded-lg bg-primary/10 text-primary text-[11px] font-bold">{{ $data->kattempatwisata }}</span>
            @endif
        </div>
        <div class="p-5">
            <div class="flex flex-col gap-6">
                <div>
                    <dl class="grid grid-cols-[140px_1fr] md:grid-cols-[160px_1fr] gap-x-4 gap-y-3 mb-0 items-center">
                        <dt class="text-[11px] font-bold text-slate-500 uppercase tracking-wide">Tanggal</dt>
                        <dd class="text-[13px] text-slate-800 font-medium m-0">{{ $data->tanggal ? \Carbon\Carbon::parse($data->tanggal)->locale('id')->translatedFormat('d F Y') : '-' }}</dd>
                        
                        <dt class="text-[11px] font-bold text-slate-500 uppercase tracking-wide">Kategori Wisata</dt>
                        <dd class="text-[13px] text-slate-800 font-medium m-0">{{ $data->kattempatwisata ?: '-' }}</dd>
                        
                        <dt class="text-[11px] font-bold text-slate-500 uppercase tracking-wide self-start mt-0.5">Nama Tempat</dt>
                        <dd class="text-[13px] text-slate-800 font-semibold m-0">{{ $data->nametempat ?: '-' }}</dd>
                        
                        <dt class="text-[11px] font-bold text-slate-500 uppercase tracking-wide self-start mt-0.5">Lokasi / Alamat</dt>
                        <dd class="text-[13px] text-slate-800 font-medium m-0">{{ $data->lokasi ?: '-' }}</dd>
                        
                        <dt class="text-[11px] font-bold text-slate-500 uppercase tracking-wide">Kapasitas Pengunjung</dt>
                        <dd class="text-[13px] text-slate-800 font-medium m-0">{{ number_format($data->kappengunjung ?: 0) }} orang</dd>
                        
                        <dt class="text-[11px] font-bold text-slate-500 uppercase tracking-wide">Kapasitas Parkir</dt>
                        <dd class="text-[13px] text-slate-800 font-medium m-0">{{ number_format($data->kapparkir ?: 0) }} kendaraan</dd>
                        
                        <dt class="text-[11px] font-bold text-slate-500 uppercase tracking-wide">Contact Person</dt>
                        <dd class="text-[13px] text-slate-800 font-medium m-0">{{ $data->namecp ?: '-' }}@if($data->cp) <span class="text-slate-400">({{ $data->cp }})</span>@endif</dd>
                        
                        <dt class="text-[11px] font-bold text-slate-500 uppercase tracking-wide">Alat Keamanan</dt>
                        <dd class="text-[13px] text-slate-800 font-medium m-0">{{ $data->alatkamanan ?: '-' }}</dd>
                        
                        @if(!empty($data->koordinat))
                        <dt class="text-[11px] font-bold text-slate-500 uppercase tracking-wide self-start mt-1">Koordinat GPS</dt>
                        <dd class="m-0 flex items-center gap-2 flex-wrap">
                            <code class="bg-slate-100 px-2 py-0.5 rounded text-[12px]">{{ $data->koordinat }}</code>
                            <a href="https://www.google.com/maps/search/?api=1&query={{ $data->koordinat }}" target="_blank" rel="noopener noreferrer" class="flex items-center gap-1 text-primary hover:underline text-decoration-none text-[12px]">
                                <span class="material-symbols-outlined text-[14px]">map</span>Google Maps
                            </a>
                        </dd>
                        @endif
                        
                        <dt class="text-[11px] font-bold text-slate-500 uppercase tracking-wide">Petugas</dt>
                        <dd class="text-[13px] text-slate-800 font-medium m-0">{{ $data->petugas_nama ?: '—' }}</dd>
                        
                        <dt class="text-[11px] font-bold text-slate-500 uppercase tracking-wide">Polda</dt>
                        <dd class="text-[13px] font-bold m-0" style="color:#D8A808;">{{ $data->petugas_daerah ? strtoupper($data->petugas_daerah) : '—' }}</dd>
                        
                        <dt class="text-[11px] font-bold text-slate-500 uppercase tracking-wide">Polres</dt>
                        <dd class="text-[13px] text-slate-800 font-medium m-0">{{ $data->petugas_wilayah ? strtoupper($data->petugas_wilayah) : '—' }}</dd>
                    </dl>
                </div>
                @if($fotoUrl)
                <div class="pt-4 border-t border-slate-100">
                    <p class="text-[11px] font-bold text-slate-500 uppercase tracking-wide mb-2 m-0">Foto Objek Wisata</p>
                    <a href="{{ $fotoUrl }}" target="_blank" rel="noopener noreferrer" class="block mt-2">
                        <img src="{{ $fotoUrl }}" alt="Foto {{ $data->nametempat }}" class="rounded-xl border border-slate-200 w-full max-w-[400px]" style="max-height:240px;object-fit:cover;">
                    </a>
                </div>
                @endif
            </div>
            <div class="flex items-center justify-between pt-4 mt-4 border-t border-slate-100 flex-wrap gap-2">
                <a href="{{ route('objekwisata.index') }}" class="flex items-center gap-2 px-4 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-600 font-bold rounded-xl text-label-md transition-all text-decoration-none">
                    <span class="material-symbols-outlined text-[18px]">arrow_back</span><span>Kembali</span>
                </a>
                <div class="flex items-center gap-2">
                    @include('_partials.show_export_dropdown', ['exportRouteName' => 'objekwisata.export', 'rowId' => $data->id_tempatwisata])
                @if($canMutateRecord ?? false)
                    <a href="{{ route('objekwisata.edit', $data->id_tempatwisata) }}" class="flex items-center gap-2 px-4 py-2.5 bg-amber-50 hover:bg-amber-100 text-amber-700 border border-amber-200 font-bold rounded-xl text-label-md transition-all text-decoration-none">
                        <span class="material-symbols-outlined text-[18px]">edit</span><span>Edit</span>
                    </a>
                    <form action="{{ route('objekwisata.destroy', $data->id_tempatwisata) }}" method="POST" class="d-inline" onsubmit="return confirm('Yakin hapus objek wisata ini?');">
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
