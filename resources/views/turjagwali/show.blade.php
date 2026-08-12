@extends('layouts.app')
@section('title', $pageTitle)
@section('page-title', $pageTitle)

@section('content')
@php
    $smLv = $smLv ?? [];
    $canMutate = ($canMutateRecord ?? false) && !($sboViewOnly ?? false);
    $kLat = $kLng = null;
    $kOk = false;
    if (!empty($data->koordinat)) {
        $kParts = preg_split('/\s*,\s*/', (string) $data->koordinat, 2);
        $kLat = $kParts[0] ?? '';
        $kLng = $kParts[1] ?? '';
        $kOk = $kLng !== '' && is_numeric($kLat) && is_numeric($kLng);
    }
@endphp
<div class="w-full max-w-3xl mx-auto space-y-3">
    <div class="flex items-center gap-2 flex-wrap">
        <a href="{{ route('turjagwali.index', $smLv) }}" class="flex items-center gap-1 text-slate-500 hover:text-primary text-decoration-none text-[12px] font-semibold transition-colors">
            <span class="material-symbols-outlined text-[16px]">arrow_back</span><span>Kembali</span>
        </a>
        <span class="text-slate-300">/</span>
        <span class="text-[12px] font-bold text-on-surface">Detail #{{ $data->id_turjagwali }}</span>
    </div>

    <div class="bg-white rounded-xl border border-slate-200/60 shadow-sm overflow-hidden">
        <div class="px-4 py-2.5 border-b border-slate-100 bg-slate-50/40 flex items-center justify-between gap-2">
            <div class="flex items-center gap-2">
                <span class="material-symbols-outlined text-primary text-[15px]" style="font-variation-settings: 'FILL' 1;">security</span>
                <h3 class="text-[12px] font-bold text-slate-800 m-0">{{ $pageTitle }}</h3>
            </div>
            @if($data->katgiat)
                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-primary/8 text-primary border border-primary/15">{{ $data->katgiat }}</span>
            @endif
        </div>
        <div class="p-3 sm:p-4">
            <dl class="grid grid-cols-[140px_1fr] md:grid-cols-[160px_1fr] gap-x-4 gap-y-3 mb-0 items-center">
                
                
                
                <dt class="text-[11px] font-bold text-slate-500 uppercase tracking-wide self-start mt-0.5">Polda</dt>
                <dd class="text-[13px] font-bold m-0 mt-0.5" style="color:#D8A808;">{{ strtoupper($data->daerah_nama ?? '—') }}</dd>
                
                <dt class="text-[11px] font-bold text-slate-500 uppercase tracking-wide self-start mt-0.5">Polres</dt>
                <dd class="text-[13px] text-slate-800 font-medium m-0 mt-0.5">{{ !empty($data->wilayah_nama) ? strtoupper(trim(str_ireplace(['KABUPATEN ', 'KOTA ', 'KAB. '], '', $data->wilayah_nama))) : '—' }}</dd>
                
                <dt class="text-[11px] font-bold text-slate-500 uppercase tracking-wide">Tanggal</dt>
                <dd class="text-[13px] text-slate-800 font-medium m-0">{{ $data->tanggal ? \Carbon\Carbon::parse($data->tanggal)->locale('id')->translatedFormat('d F Y') : '—' }}</dd>
                
                <dt class="text-[11px] font-bold text-slate-500 uppercase tracking-wide self-start mt-0.5">Piket</dt>
                <dd class="text-[13px] text-slate-800 font-medium m-0 mt-0.5">{{ $data->usm_nama ?? '—' }}</dd>
                
                <dt class="text-[11px] font-bold text-slate-500 uppercase tracking-wide self-start mt-0.5">Lokasi</dt>
                <dd class="text-[13px] text-slate-800 font-medium m-0">{{ $data->lokasi ?: '—' }}</dd>
                
                <dt class="text-[11px] font-bold text-slate-500 uppercase tracking-wide self-start mt-0.5">Peristiwa</dt>
                <dd class="text-[13px] text-slate-800 font-medium m-0">{{ $data->peristiwa ?: '—' }}</dd>
                
                <dt class="text-[11px] font-bold text-slate-500 uppercase tracking-wide">Petugas Lap.</dt>
                <dd class="text-[13px] text-slate-800 font-medium m-0">{{ $data->petugaslap ?: '—' }}</dd>
                
                <dt class="text-[11px] font-bold text-slate-500 uppercase tracking-wide">Faktor</dt>
                <dd class="text-[13px] text-slate-800 font-medium m-0">{{ $data->faktor ?: '—' }}</dd>
                
                <dt class="text-[11px] font-bold text-slate-500 uppercase tracking-wide self-start mt-0.5">Penyebab</dt>
                <dd class="text-[13px] text-slate-800 font-medium m-0">{{ $data->penyebab ?: '—' }}</dd>
                
                @if(!empty($data->ketcb))
                <dt class="text-[11px] font-bold text-slate-500 uppercase tracking-wide self-start mt-0.5">Keterangan CB</dt>
                <dd class="text-[13px] text-slate-800 font-medium m-0 whitespace-pre-line">{{ $data->ketcb }}</dd>
                @endif
                
                @if(!empty($data->koordinat))
                <dt class="text-[11px] font-bold text-slate-500 uppercase tracking-wide self-start mt-1">Koordinat GPS</dt>
                <dd class="m-0 flex items-center gap-2 flex-wrap">
                    <code class="bg-slate-100 px-2 py-0.5 rounded text-[12px]">{{ $data->koordinat }}</code>
                    @if($kOk)
                        <a href="https://www.google.com/maps?q={{ $kLat }},{{ $kLng }}" target="_blank" rel="noopener noreferrer"
                           class="inline-flex items-center gap-1 text-primary hover:underline text-decoration-none text-[12px]">
                            <span class="material-symbols-outlined text-[14px]">map</span> Google Maps
                        </a>
                    @endif
                </dd>
                @endif
                
            </dl>

            @if(!empty($data->fotogiat))
                <div class="pt-4 mt-4 border-t border-slate-100">
                    <p class="text-[11px] font-bold text-slate-500 uppercase tracking-wide m-0 mb-2">Foto Giat</p>
                    <div class="flex flex-wrap gap-2 mt-2">
                        @foreach(explode(',', (string) $data->fotogiat) as $foto)
                            @php $f = trim($foto); if ($f === '') continue; $url = str_starts_with($f, 'http') ? $f : asset(ltrim($f, '/')); @endphp
                            <a href="{{ $url }}" target="_blank" rel="noopener noreferrer" class="block">
                                <img src="{{ $url }}" alt="Foto giat" class="rounded-xl border border-slate-200 object-cover" style="width:300px;height:400px;">
                            </a>
                        @endforeach
                    </div>
                </div>
            @endif

            <div class="flex items-center justify-between gap-2 pt-3 mt-3 border-t border-slate-100">
                <a href="{{ route('turjagwali.index', $smLv) }}" class="flex items-center gap-1.5 px-3 py-2 bg-slate-100 hover:bg-slate-200 text-slate-600 font-bold rounded-lg text-[12px] transition-all text-decoration-none">
                    <span class="material-symbols-outlined text-[16px]">arrow_back</span><span>Kembali</span>
                </a>
                <div class="flex items-center gap-2">
                    @include('_partials.show_export_dropdown', [
                        'exportRouteName' => 'turjagwali.export',
                        'rowId' => $data->id_turjagwali,
                        'exportRouteParams' => $smLv,
                    ])
                @if($canMutate)
                    <a href="{{ route('turjagwali.edit', array_merge(['turjagwali' => $data->id_turjagwali], $smLv)) }}" class="flex items-center gap-1.5 px-3 py-2 bg-amber-50 hover:bg-amber-100 text-amber-700 border border-amber-200 font-bold rounded-lg text-[12px] transition-all text-decoration-none">
                            <span class="material-symbols-outlined text-[16px]">edit</span><span>Edit</span>
                        </a>
                        <form action="{{ route('turjagwali.destroy', array_merge(['turjagwali' => $data->id_turjagwali], $smLv)) }}" method="POST" class="m-0" onsubmit="return confirm('Yakin hapus data ini?')">
                            @csrf @method('DELETE')
                            @include('turjagwali._sm_lv_hidden', ['smLv' => $smLv])
                            <button type="submit" class="flex items-center gap-1.5 px-3 py-2 bg-red-50 hover:bg-red-100 text-red-700 border border-red-200 font-bold rounded-lg text-[12px] transition-all border-solid">
                                <span class="material-symbols-outlined text-[16px]">delete</span><span>Hapus</span>
                            </button>
                        </form>
                @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
