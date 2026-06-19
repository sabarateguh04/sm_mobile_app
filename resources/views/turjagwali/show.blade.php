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
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-2 mb-4">
                <div class="rounded-lg border border-slate-200/80 bg-slate-50/50 px-3 py-2">
                    <p class="text-[9px] font-bold text-slate-400 uppercase tracking-wide m-0 mb-0.5">Tanggal</p>
                    <p class="text-[12px] font-bold text-slate-800 m-0">{{ $data->tanggal ? \Carbon\Carbon::parse($data->tanggal)->format('d M Y') : '—' }}</p>
                </div>
                <div class="rounded-lg border border-slate-200/80 bg-slate-50/50 px-3 py-2">
                    <p class="text-[9px] font-bold text-slate-400 uppercase tracking-wide m-0 mb-0.5">Piket</p>
                    <p class="text-[12px] font-semibold text-slate-800 m-0">{{ $data->usm_nama ?? '—' }}</p>
                </div>
                <div class="rounded-lg border border-slate-200/80 bg-slate-50/50 px-3 py-2">
                    <p class="text-[9px] font-bold text-slate-400 uppercase tracking-wide m-0 mb-0.5">Polda</p>
                    <p class="text-[12px] font-bold m-0" style="color:#D8A808;">{{ strtoupper($data->daerah_nama ?? '—') }}</p>
                </div>
                <div class="rounded-lg border border-slate-200/80 bg-slate-50/50 px-3 py-2">
                    <p class="text-[9px] font-bold text-slate-400 uppercase tracking-wide m-0 mb-0.5">Polres</p>
                    <p class="text-[12px] font-semibold text-slate-800 m-0">{{ !empty($data->wilayah_nama) ? strtoupper(trim(str_ireplace(['KABUPATEN ', 'KOTA ', 'KAB. '], '', $data->wilayah_nama))) : '—' }}</p>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 mb-4">
                <div class="rounded-lg border border-slate-200/80 bg-white px-3 py-2.5">
                    <p class="text-[9px] font-bold text-slate-400 uppercase tracking-wide m-0 mb-1">Lokasi</p>
                    <p class="text-[13px] font-medium text-slate-800 m-0">{{ $data->lokasi ?: '—' }}</p>
                </div>
                <div class="rounded-lg border border-slate-200/80 bg-white px-3 py-2.5">
                    <p class="text-[9px] font-bold text-slate-400 uppercase tracking-wide m-0 mb-1">Peristiwa</p>
                    <p class="text-[13px] font-medium text-slate-800 m-0">{{ $data->peristiwa ?: '—' }}</p>
                </div>
            </div>

            <div class="grid grid-cols-2 sm:grid-cols-3 gap-2 mb-4">
                <div class="rounded-lg border border-slate-200/80 bg-slate-50/50 px-3 py-2">
                    <p class="text-[9px] font-bold text-slate-400 uppercase tracking-wide m-0 mb-0.5">Petugas Lap.</p>
                    <p class="text-[12px] font-semibold text-slate-800 m-0">{{ $data->petugaslap ?: '—' }}</p>
                </div>
                <div class="rounded-lg border border-slate-200/80 bg-slate-50/50 px-3 py-2">
                    <p class="text-[9px] font-bold text-slate-400 uppercase tracking-wide m-0 mb-0.5">Faktor</p>
                    <p class="text-[12px] text-slate-700 m-0">{{ $data->faktor ?: '—' }}</p>
                </div>
                <div class="rounded-lg border border-slate-200/80 bg-slate-50/50 px-3 py-2 sm:col-span-1 col-span-2">
                    <p class="text-[9px] font-bold text-slate-400 uppercase tracking-wide m-0 mb-0.5">Penyebab</p>
                    <p class="text-[12px] text-slate-700 m-0">{{ $data->penyebab ?: '—' }}</p>
                </div>
            </div>

            @if(!empty($data->ketcb))
                <div class="rounded-lg border border-slate-200/80 bg-white px-3 py-2.5 mb-4">
                    <p class="text-[9px] font-bold text-slate-400 uppercase tracking-wide m-0 mb-1">Keterangan CB</p>
                    <p class="text-[12px] text-slate-700 m-0 leading-relaxed whitespace-pre-line">{{ $data->ketcb }}</p>
                </div>
            @endif

            @if(!empty($data->koordinat))
                <div class="rounded-lg border border-slate-200/80 bg-slate-50/50 px-3 py-2.5 mb-4">
                    <p class="text-[9px] font-bold text-slate-400 uppercase tracking-wide m-0 mb-1">Koordinat GPS</p>
                    <div class="flex items-center gap-2 flex-wrap">
                        <code class="bg-white border border-slate-200 px-2 py-0.5 rounded text-[11px] text-slate-700">{{ $data->koordinat }}</code>
                        @if($kOk)
                            <a href="https://www.google.com/maps?q={{ $kLat }},{{ $kLng }}" target="_blank" rel="noopener noreferrer"
                               class="inline-flex items-center gap-1 text-primary hover:underline text-decoration-none text-[11px] font-semibold">
                                <span class="material-symbols-outlined text-[14px]">map</span> Maps
                            </a>
                        @endif
                    </div>
                </div>
            @endif

            @if(!empty($data->fotogiat))
                <div class="rounded-lg border border-slate-200/80 bg-slate-50/50 px-3 py-2.5 mb-4">
                    <p class="text-[9px] font-bold text-slate-400 uppercase tracking-wide m-0 mb-2">Foto Giat</p>
                    <div class="flex flex-wrap gap-2">
                        @foreach(explode(',', (string) $data->fotogiat) as $foto)
                            @php $f = trim($foto); if ($f === '') continue; $url = str_starts_with($f, 'http') ? $f : asset(ltrim($f, '/')); @endphp
                            <a href="{{ $url }}" target="_blank" rel="noopener noreferrer">
                                <img src="{{ $url }}" alt="Foto giat" class="rounded-lg border border-slate-200 max-h-[120px] object-cover">
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
