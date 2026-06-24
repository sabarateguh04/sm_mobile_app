@extends('layouts.app')
@section('title', $pageTitle)
@section('page-title', $pageTitle)

@section('content')
@php
    $hasFoto = ! empty($data->foto);
    $fotoUrl = $hasFoto
        ? (str_starts_with($data->foto, 'http') ? $data->foto : asset(ltrim($data->foto, '/')))
        : null;

    $kLat = $kLng = null;
    $kOk = false;
    if (! empty($data->koordinat)) {
        $kParts = preg_split('/\s*,\s*/', (string) $data->koordinat, 2);
        $kLat = $kParts[0] ?? '';
        $kLng = $kParts[1] ?? '';
        $kOk = $kLng !== '' && is_numeric($kLat) && is_numeric($kLng);
    }

    $canMutate = ! ($readOnly ?? false) && ! ($sboViewOnly ?? false)
        && ($canMutateRecord ?? \App\Support\RoleMutationPolicy::canMutateOperationalRow(auth()->user(), $data, $tableName ?? 'layanan_data'));
    $isOwnRow = \App\Support\RoleMutationPolicy::isRowOwnedByActor(auth()->user(), $data, $tableName ?? 'layanan_data');
    $indexRoute = ($readOnly ?? false)
        ? route('layanan.data.index', ['sb_grp' => 'public_service'])
        : route('layanan.data.index');
    $judul = $data->tujuan ?: ($data->kategori_data ?: 'Layanan Data');
@endphp
<div class="max-w-[900px] mx-auto w-full space-y-4">
    <div class="flex items-center gap-3 flex-wrap">
        <a href="{{ $indexRoute }}" class="flex items-center gap-1.5 text-slate-500 hover:text-primary text-decoration-none text-label-md font-semibold transition-colors">
            <span class="material-symbols-outlined text-[18px]">arrow_back</span><span>Kembali</span>
        </a>
        <span class="text-slate-300">/</span>
        <span class="text-[13px] font-bold text-on-surface">{{ \Str::limit($judul, 48) }}</span>
        @if($isOwnRow && !($readOnly ?? false))
            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-secondary/10 text-secondary border border-secondary/20">Data Anda</span>
        @endif
    </div>

    <div class="bg-white rounded-2xl border border-slate-200/60 shadow-sm overflow-hidden">
        <div class="px-5 py-3.5 border-b border-slate-100 bg-slate-50/30 flex items-center justify-between gap-2 flex-wrap">
            <div class="flex items-center gap-2 min-w-0">
                <span class="material-symbols-outlined text-primary text-[16px] shrink-0" style="font-variation-settings: 'FILL' 1;">database</span>
                <h3 class="text-[13px] font-bold text-slate-800 m-0 truncate">{{ $judul }}</h3>
            </div>
            @if($data->kategori_data)
                <span class="inline-flex items-center px-2.5 py-1 rounded-lg bg-primary/10 text-primary text-[11px] font-bold shrink-0">{{ $data->kategori_data }}</span>
            @endif
        </div>

        <div class="p-5">
            <div class="flex flex-col gap-6">
                <div>
                    <dl class="grid grid-cols-[140px_1fr] md:grid-cols-[160px_1fr] gap-x-4 gap-y-3 mb-0 items-center">
                        @if(!empty($data->daerah) || !empty($data->wilayah) || ($readOnly ?? false))
                            <dt class="text-[11px] font-bold text-slate-500 uppercase tracking-wide">Polda</dt>
                            <dd class="text-[13px] font-bold m-0" style="color:#D8A808;">{{ $data->daerah ? strtoupper($data->daerah) : '—' }}</dd>
                            
                            <dt class="text-[11px] font-bold text-slate-500 uppercase tracking-wide">Polres</dt>
                            <dd class="text-[13px] text-slate-800 font-medium m-0">{{ $data->wilayah ? strtoupper(trim(str_ireplace(['KABUPATEN ', 'KOTA ', 'KAB. '], '', $data->wilayah))) : '—' }}</dd>
                        @endif
                        
                        <dt class="text-[11px] font-bold text-slate-500 uppercase tracking-wide">Tanggal</dt>
                        <dd class="text-[13px] text-slate-800 font-medium m-0">{{ $data->tanggal ? \Carbon\Carbon::parse($data->tanggal)->locale('id')->translatedFormat('d F Y') : '—' }}</dd>
                        
                        <dt class="text-[11px] font-bold text-slate-500 uppercase tracking-wide self-start mt-0.5">Pemohon</dt>
                        <dd class="text-[13px] text-slate-800 font-medium m-0">
                            {{ $data->pemohon ?: '—' }}
                            @if($data->telp_pemohon)
                                <span class="block text-[11px] text-slate-400 font-normal mt-0.5">{{ $data->telp_pemohon }}</span>
                            @endif
                        </dd>
                        
                        <dt class="text-[11px] font-bold text-slate-500 uppercase tracking-wide self-start mt-0.5">Tujuan</dt>
                        <dd class="text-[13px] text-slate-800 font-semibold m-0">{{ $data->tujuan ?: '—' }}</dd>
                        
                        <dt class="text-[11px] font-bold text-slate-500 uppercase tracking-wide">Kategori</dt>
                        <dd class="text-[13px] text-slate-800 font-medium m-0">{{ $data->kategori_data ?: '—' }}</dd>
                        
                        <dt class="text-[11px] font-bold text-slate-500 uppercase tracking-wide self-start mt-0.5">Keterangan</dt>
                        <dd class="text-[13px] text-slate-800 font-medium m-0 whitespace-pre-line">{{ $data->keterangan ?: '—' }}</dd>
                        
                        @if(!empty($data->koordinat))
                            <dt class="text-[11px] font-bold text-slate-500 uppercase tracking-wide self-start mt-1">Koordinat GPS</dt>
                            <dd class="m-0 flex items-center gap-2 flex-wrap">
                                <code class="bg-slate-100 px-2 py-0.5 rounded text-[12px] select-all">{{ $data->koordinat }}</code>
                                @if($kOk)
                                    <a href="https://www.google.com/maps?q={{ $kLat }},{{ $kLng }}" target="_blank" rel="noopener noreferrer"
                                       class="flex items-center gap-1 text-primary hover:underline text-decoration-none text-[12px]">
                                        <span class="material-symbols-outlined text-[14px]">map</span>Google Maps
                                    </a>
                                @endif
                            </dd>
                        @endif
                    </dl>
                </div>

                @if($fotoUrl)
                    <div class="pt-4 border-t border-slate-100">
                        <p class="text-[11px] font-bold text-slate-500 uppercase tracking-wide mb-2 m-0">Foto</p>
                        <div class="rounded-xl overflow-hidden border border-slate-200 shadow-sm cursor-zoom-in relative group mt-2 max-w-[400px]"
                             data-bs-toggle="modal" data-bs-target="#layananDataPhotoModal" role="button" tabindex="0">
                            <img src="{{ $fotoUrl }}" alt="Foto layanan data" class="w-full transition-transform duration-300 group-hover:scale-[1.02]"
                                 style="max-height:240px;object-fit:cover;" referrerpolicy="no-referrer">
                            <div class="absolute inset-0 bg-black/30 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center">
                                <span class="material-symbols-outlined text-white text-[32px]">zoom_in</span>
                            </div>
                        </div>
                    </div>
                @endif
            </div>

            <div class="flex items-center justify-between pt-4 mt-4 border-t border-slate-100 flex-wrap gap-2">
                <a href="{{ $indexRoute }}" class="flex items-center gap-2 px-4 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-600 font-bold rounded-xl text-label-md transition-all text-decoration-none">
                    <span class="material-symbols-outlined text-[18px]">arrow_back</span><span>Kembali</span>
                </a>
                <div class="flex items-center gap-2 flex-wrap">
                    @include('_partials.show_export_dropdown', [
                        'exportRouteName' => 'layanan.data.export',
                        'rowId' => $data->id_layanan_data,
                        'exportQuery' => ($readOnly ?? false) ? ['sb_grp' => 'public_service'] : [],
                    ])
                    @if($canMutate)
                        <a href="{{ route('layanan.data.edit', $data->id_layanan_data) }}"
                           class="flex items-center gap-2 px-4 py-2.5 bg-amber-50 hover:bg-amber-100 text-amber-700 border border-amber-200 font-bold rounded-xl text-label-md transition-all text-decoration-none">
                            <span class="material-symbols-outlined text-[18px]">edit</span><span>Edit</span>
                        </a>
                        <form action="{{ route('layanan.data.destroy', $data->id_layanan_data) }}" method="POST" class="d-inline m-0"
                              onsubmit="return confirm('Yakin hapus data layanan ini?')">
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

@if($fotoUrl)
    <div class="modal fade" id="layananDataPhotoModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg modal-fullscreen-sm-down">
            <div class="modal-content bg-dark border-0" style="border-radius:1rem;">
                <div class="modal-header border-secondary border-opacity-25 py-2">
                    <h6 class="modal-title text-white text-[13px] font-bold m-0">
                        <span class="material-symbols-outlined text-[16px] align-middle me-1">photo_camera</span>Foto Layanan Data
                    </h6>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Tutup"></button>
                </div>
                <div class="modal-body p-2 d-flex align-items-center justify-content-center">
                    <img src="{{ $fotoUrl }}" alt="Foto layanan data" class="img-fluid rounded" style="max-height:80vh;object-fit:contain;" referrerpolicy="no-referrer">
                </div>
            </div>
        </div>
    </div>
@endif
@endsection
