@extends('layouts.app')
@section('title', $pageTitle)
@section('page-title', $pageTitle)

@section('content')
@php
    $sbGrp = $sbGrp ?? [];
    $fotoUrls = [];
    if (! empty($data->fotosit)) {
        foreach (explode(',', (string) $data->fotosit) as $foto) {
            $f = trim($foto);
            if ($f === '') continue;
            $fotoUrls[] = str_starts_with($f, 'http') ? $f : asset(ltrim($f, '/'));
        }
    }
    $hasFoto = $fotoUrls !== [];

    $kLat = $kLng = null;
    $kOk = false;
    if (! empty($data->koordinat)) {
        $kParts = preg_split('/\s*,\s*/', (string) $data->koordinat, 2);
        $kLat = $kParts[0] ?? '';
        $kLng = $kParts[1] ?? '';
        $kOk = $kLng !== '' && is_numeric($kLat) && is_numeric($kLng);
    }

    $tanggalFmt = $data->tanggal ? \Carbon\Carbon::parse($data->tanggal)->format('d/m/Y') : '-';
    $jamFmt = '-';
    if (! empty($data->jamperistiwa)) {
        try { $jamFmt = \Carbon\Carbon::parse($data->jamperistiwa)->format('H:i'); }
        catch (\Throwable) { $jamFmt = (string) $data->jamperistiwa; }
    }

    $korbanMd = (int) ($data->korbanmd ?? 0);
    $korbanLb = (int) ($data->korbanlb ?? 0);
    $korbanLr = (int) ($data->korbanlr ?? 0);
    $kermat   = (int) ($data->kermat ?? 0);
    $korbanTotal = $korbanMd + $korbanLb + $korbanLr;
    $canMutate = ($canMutateRecord ?? false) && !($sboViewOnly ?? false);
    $authUser = auth()->user();
    $sameDaerah = $authUser && !empty($authUser->daerah) && !empty($data->daerah_nama) && strcasecmp((string) $authUser->daerah, (string) $data->daerah_nama) === 0;
    $sameWilayah = $authUser && !empty($authUser->wilayah) && !empty($data->wilayah_nama) && strcasecmp((string) $authUser->wilayah, (string) $data->wilayah_nama) === 0;
    $canConfirmOpsdal = !($sboViewOnly ?? false) && $sameDaerah && (empty($authUser->wilayah) || $sameWilayah);
    $isPendingOpsdal = ($data->status_konfirmasi_opsdal ?? null) === 'menunggu';

    ob_start();
@endphp
@include('contigency._sb_grp_hidden', ['sbGrp' => $sbGrp])
@php $destroyExtraHtml = ob_get_clean(); @endphp

<div class="max-w-[960px] mx-auto w-full space-y-4">
    {{-- Breadcrumb --}}
    <div class="flex items-center gap-3">
        <a href="{{ route('contigency.index', $sbGrp) }}" class="flex items-center gap-1.5 text-slate-500 hover:text-primary text-decoration-none text-label-md font-semibold transition-colors">
            <span class="material-symbols-outlined text-[18px]">arrow_back</span><span>Kembali</span>
        </a>
        <span class="text-slate-300">/</span>
        <span class="text-[13px] font-bold text-on-surface">{{ $pageTitle }}</span>
    </div>

    {{-- Main content card --}}
    <div class="bg-white rounded-2xl border border-slate-200/60 shadow-sm overflow-hidden">
        <div class="px-5 py-3.5 border-b border-slate-100 bg-slate-50/30 flex items-center justify-between gap-2">
            <div class="flex items-center gap-2">
                <span class="material-symbols-outlined text-primary text-[16px]" style="font-variation-settings: 'FILL' 1;">local_hospital</span>
                <h3 class="text-[13px] font-bold text-slate-800 m-0">{{ $pageTitle }}</h3>
            </div>
            @if(! empty($data->jeniscontigency))
                <span class="inline-flex items-center px-2.5 py-1 rounded-lg bg-slate-800 text-white text-[11px] font-bold">{{ $data->jeniscontigency }}</span>
            @endif
        </div>
        <div class="p-5">
            <div class="flex flex-col gap-6">
                <div>
                    <dl class="grid grid-cols-[140px_1fr] md:grid-cols-[160px_1fr] gap-x-4 gap-y-3 mb-0 items-center">


                        
                        <dt class="text-[11px] font-bold text-slate-500 uppercase tracking-wide">Polda</dt>
                        <dd class="text-[13px] font-bold m-0" style="color:#D8A808;">{{ strtoupper($data->daerah_nama ?? '—') }}</dd>
                        
                        <dt class="text-[11px] font-bold text-slate-500 uppercase tracking-wide">Polres</dt>
                        <dd class="text-[13px] text-slate-800 font-medium m-0">{{ !empty($data->wilayah_nama) ? strtoupper(trim(str_ireplace(['KABUPATEN ', 'KOTA ', 'KAB. '], '', $data->wilayah_nama))) : '—' }}</dd>
                        
                        <dt class="text-[11px] font-bold text-slate-500 uppercase tracking-wide">Tanggal</dt>
                        <dd class="text-[13px] text-slate-800 font-medium m-0">{{ $tanggalFmt }} @if($jamFmt !== '-')<span class="text-[11px] text-slate-400 ml-1">{{ $jamFmt }} WIB</span>@endif</dd>
                        
                        <dt class="text-[11px] font-bold text-slate-500 uppercase tracking-wide">Piket (Input)</dt>
                        <dd class="text-[13px] text-slate-800 font-medium m-0">{{ $data->usm_nama ?? '—' }}</dd>
                        
                        <dt class="text-[11px] font-bold text-slate-500 uppercase tracking-wide">Petugas TPTKP</dt>
                        <dd class="text-[13px] text-slate-800 font-medium m-0">{{ $data->petugastptkp ?: '—' }}</dd>
                        
                        <dt class="text-[11px] font-bold text-slate-500 uppercase tracking-wide self-start mt-0.5">Status Opsdal</dt>
                        <dd class="m-0">@include('trafficstuck._status_badges', ['item' => $data])</dd>
                        
                        <dt class="text-[11px] font-bold text-slate-500 uppercase tracking-wide self-start mt-0.5">Lokasi Kejadian</dt>
                        <dd class="text-[13px] text-slate-800 font-medium m-0 break-all">{{ $data->lokasi ?: '—' }}</dd>
                        
                        @if(! empty($data->koordinat))
                        <dt class="text-[11px] font-bold text-slate-500 uppercase tracking-wide self-start mt-1">Koordinat GPS</dt>
                        <dd class="m-0 flex items-center gap-2 flex-wrap">
                            <code class="bg-slate-100 px-2 py-0.5 rounded text-[12px]">{{ $data->koordinat }}</code>
                            @if($kOk)<a href="https://www.google.com/maps?q={{ $kLat }},{{ $kLng }}" target="_blank" rel="noopener noreferrer" class="flex items-center gap-1 text-primary hover:underline text-decoration-none text-[12px]"><span class="material-symbols-outlined text-[14px]">map</span>Google Maps</a>@endif
                        </dd>
                        @endif
                        
                        <dt class="text-[11px] font-bold text-slate-500 uppercase tracking-wide">Jenis Contigency</dt>
                        <dd class="text-[13px] text-slate-800 font-medium m-0">{{ $data->jeniscontigency ?: '—' }}</dd>
                        
                        <dt class="text-[11px] font-bold text-slate-500 uppercase tracking-wide">Faktor</dt>
                        <dd class="text-[13px] text-slate-800 font-medium m-0">{{ $data->faktor ?: '—' }}</dd>
                        
                        <dt class="text-[11px] font-bold text-slate-500 uppercase tracking-wide self-start mt-0.5">Penyebab</dt>
                        <dd class="text-[13px] text-slate-800 font-medium m-0">{{ $data->penyebab ?: '—' }}</dd>
                        
                        <dt class="text-[11px] font-bold text-slate-500 uppercase tracking-wide self-start mt-1">Korban &amp; Kerusakan</dt>
                        <dd class="m-0 flex flex-wrap gap-2">
                            <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-xl {{ $korbanMd > 0 ? 'bg-red-100 text-red-700' : 'bg-slate-100 text-slate-500' }} text-[12px] font-bold">
                                MD: <span class="text-[14px]">{{ $korbanMd }}</span>
                            </span>
                            <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-xl {{ $korbanLb > 0 ? 'bg-amber-100 text-amber-700' : 'bg-slate-100 text-slate-500' }} text-[12px] font-bold">
                                LB: <span class="text-[14px]">{{ $korbanLb }}</span>
                            </span>
                            <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-xl {{ $korbanLr > 0 ? 'bg-sky-100 text-sky-700' : 'bg-slate-100 text-slate-500' }} text-[12px] font-bold">
                                LR: <span class="text-[14px]">{{ $korbanLr }}</span>
                            </span>
                            <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-xl {{ $kermat > 0 ? 'bg-purple-100 text-purple-700' : 'bg-slate-100 text-slate-500' }} text-[12px] font-bold">
                                Kermat: <span class="text-[14px]">{{ $kermat }}</span>
                            </span>
                        </dd>
                        
                        @if(! empty(trim((string) ($data->ketcb ?? ''))))
                        <dt class="text-[11px] font-bold text-slate-500 uppercase tracking-wide self-start mt-0.5">Keterangan CB</dt>
                        <dd class="text-[13px] text-slate-800 font-medium m-0 whitespace-pre-wrap bg-slate-50 rounded-xl px-4 py-3 break-all">{!! nl2br(e($data->ketcb)) !!}</dd>
                        @endif
                    </dl>
                </div>

                {{-- Foto gallery --}}
                @if($hasFoto)
                <div>
                    <div class="rounded-2xl overflow-hidden border border-slate-200 shadow-sm contigency-photo-card">
                        <div class="px-3 py-2 bg-slate-800 text-white flex items-center justify-between gap-2">
                            <span class="text-[12px] font-bold flex items-center gap-1.5">
                                <span class="material-symbols-outlined text-[14px]">photo_camera</span>Foto Situasi
                                @if(count($fotoUrls) > 1)<span class="opacity-60 font-normal">({{ count($fotoUrls) }})</span>@endif
                            </span>
                            @if($kOk)
                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md bg-emerald-500 text-white text-[10px] font-bold"><span class="material-symbols-outlined text-[12px]">location_on</span>GPS</span>
                            @else
                                <span class="inline-flex items-center px-2 py-0.5 rounded-md bg-amber-400 text-amber-900 text-[10px] font-bold">Tanpa GPS</span>
                            @endif
                        </div>

                        <div class="contigency-photo-main position-relative" role="button" tabindex="0" data-bs-toggle="modal" data-bs-target="#contigencyPhotoModal" aria-label="Perbesar foto situasi">
                            <img id="contigencyPhotoMain" src="{{ $fotoUrls[0] }}" alt="Foto situasi" class="contigency-photo-main-img w-100">
                            <div class="contigency-photo-overlay">
                                <span class="contigency-photo-zoom">
                                    <span class="material-symbols-outlined text-[28px]">zoom_in</span>
                                    <span class="text-[11px] d-block mt-1">Perbesar</span>
                                </span>
                            </div>
                        </div>

                        @if(count($fotoUrls) > 1)
                        <div class="contigency-photo-thumbs d-flex gap-2 p-2 bg-light border-top">
                            @foreach($fotoUrls as $idx => $url)
                                <button type="button" class="contigency-photo-thumb {{ $idx === 0 ? 'is-active' : '' }}" data-index="{{ $idx }}" data-src="{{ $url }}" aria-label="Foto {{ $idx + 1 }}">
                                    <img src="{{ $url }}" alt="Thumb {{ $idx + 1 }}" loading="lazy">
                                </button>
                            @endforeach
                        </div>
                        @endif

                        @if($kOk)
                        <div class="px-3 py-2 border-top bg-white">
                            <a href="https://www.google.com/maps?q={{ $kLat }},{{ $kLng }}" target="_blank" rel="noopener noreferrer" class="flex items-center gap-1.5 justify-center px-3 py-2 bg-emerald-50 hover:bg-emerald-100 text-emerald-700 border border-emerald-200 font-bold rounded-xl text-[12px] transition-all text-decoration-none">
                                <span class="material-symbols-outlined text-[16px]">map</span>Lihat di Google Maps
                            </a>
                        </div>
                        @endif
                    </div>
                </div>
                @endif
            </div>

            {{-- Actions --}}
            <div class="flex items-center justify-between pt-4 mt-4 border-t border-slate-100 flex-wrap gap-2">
                <a href="{{ route('contigency.index', $sbGrp) }}" class="flex items-center gap-2 px-4 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-600 font-bold rounded-xl text-label-md transition-all text-decoration-none">
                    <span class="material-symbols-outlined text-[18px]">arrow_back</span><span>Kembali</span>
                </a>
                <div class="flex items-center gap-2">
                    @include('_partials.show_export_dropdown', [
                        'exportRouteName' => 'contigency.export',
                        'rowId' => $data->id_contigency,
                        'exportRouteParams' => $sbGrp,
                    ])
                    @if($canConfirmOpsdal && $isPendingOpsdal)
                        <form action="{{ route('contigency.confirm', array_merge(['contigency' => $data->id_contigency], $sbGrp)) }}" method="POST" class="d-inline">
                            @csrf
                            @include('contigency._sb_grp_hidden', ['sbGrp' => $sbGrp])
                            <button type="submit" class="flex items-center gap-2 px-4 py-2.5 bg-emerald-50 hover:bg-emerald-100 text-emerald-700 border border-emerald-200 font-bold rounded-xl text-label-md transition-all border-0">
                                <span class="material-symbols-outlined text-[18px]">check_circle</span><span>Konfirmasi OPSDAL</span>
                            </button>
                        </form>
                        <form action="{{ route('contigency.reject', array_merge(['contigency' => $data->id_contigency], $sbGrp)) }}" method="POST" class="d-inline">
                            @csrf
                            @include('contigency._sb_grp_hidden', ['sbGrp' => $sbGrp])
                            <input type="hidden" name="opsdal_confirmation_note" value="Ditolak dari halaman detail OPSDAL">
                            <button type="submit" class="flex items-center gap-2 px-4 py-2.5 bg-red-50 hover:bg-red-100 text-red-700 border border-red-200 font-bold rounded-xl text-label-md transition-all border-0">
                                <span class="material-symbols-outlined text-[18px]">cancel</span><span>Tolak OPSDAL</span>
                            </button>
                        </form>
                    @endif
                    @if($canMutate)
                        <a href="{{ route('contigency.edit', array_merge(['contigency' => $data->id_contigency], $sbGrp)) }}" class="flex items-center gap-2 px-4 py-2.5 bg-amber-50 hover:bg-amber-100 text-amber-700 border border-amber-200 font-bold rounded-xl text-label-md transition-all text-decoration-none">
                            <span class="material-symbols-outlined text-[18px]">edit</span><span>Edit</span>
                        </a>
                        <form action="{{ route('contigency.destroy', array_merge(['contigency' => $data->id_contigency], $sbGrp)) }}" method="POST" class="d-inline" onsubmit="return confirm('Yakin hapus data contigency ini?');">
                            @csrf @method('DELETE')
                            {!! $destroyExtraHtml !!}
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

{{-- Modal photo --}}
@if($hasFoto)
<div class="modal fade" id="contigencyPhotoModal" tabindex="-1" aria-labelledby="contigencyPhotoModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-xl modal-fullscreen-lg-down">
        <div class="modal-content bg-dark border-0">
            <div class="modal-header border-secondary border-opacity-25 py-2">
                <h6 class="modal-title text-white" id="contigencyPhotoModalLabel">
                    <span class="material-symbols-outlined text-[16px] align-middle me-1">photo_camera</span>Foto Situasi
                    <span id="contigencyPhotoCounter" class="opacity-75 fw-normal ms-1"></span>
                </h6>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Tutup"></button>
            </div>
            <div class="modal-body p-0 position-relative d-flex align-items-center justify-content-center contigency-photo-modal-body">
                @if(count($fotoUrls) > 1)
                    <button type="button" class="contigency-photo-nav contigency-photo-nav-prev" aria-label="Foto sebelumnya"><span class="material-symbols-outlined">chevron_left</span></button>
                @endif
                <img id="contigencyPhotoModalImg" src="{{ $fotoUrls[0] }}" alt="Foto situasi" class="contigency-photo-modal-img">
                @if(count($fotoUrls) > 1)
                    <button type="button" class="contigency-photo-nav contigency-photo-nav-next" aria-label="Foto berikutnya"><span class="material-symbols-outlined">chevron_right</span></button>
                @endif
            </div>
            @if(count($fotoUrls) > 1)
            <div class="modal-footer border-secondary border-opacity-25 py-2 justify-content-center gap-2 flex-nowrap overflow-auto">
                @foreach($fotoUrls as $idx => $url)
                    <button type="button" class="contigency-photo-modal-thumb {{ $idx === 0 ? 'is-active' : '' }}" data-index="{{ $idx }}" data-src="{{ $url }}">
                        <img src="{{ $url }}" alt="Thumb {{ $idx + 1 }}" loading="lazy">
                    </button>
                @endforeach
            </div>
            @endif
        </div>
    </div>
</div>

@push('styles')
<style>
.contigency-photo-card { position: sticky; top: 1rem; }
.contigency-photo-main { cursor: zoom-in; overflow: hidden; background: #1a1a1a; }
.contigency-photo-main-img { display: block; height: min(300px, 45vw); object-fit: cover; transition: transform .35s ease; }
.contigency-photo-main:hover .contigency-photo-main-img { transform: scale(1.03); }
.contigency-photo-overlay { position: absolute; inset: 0; display: flex; align-items: center; justify-content: center; background: rgba(0,0,0,.35); opacity: 0; transition: opacity .25s ease; }
.contigency-photo-main:hover .contigency-photo-overlay,
.contigency-photo-main:focus-visible .contigency-photo-overlay { opacity: 1; }
.contigency-photo-zoom { color: #fff; text-align: center; text-shadow: 0 1px 4px rgba(0,0,0,.5); }
.contigency-photo-thumb, .contigency-photo-modal-thumb {
    flex: 0 0 auto; padding: 0; border: 2px solid transparent; border-radius: .5rem; overflow: hidden;
    background: #fff; width: 60px; height: 45px; cursor: pointer; transition: border-color .2s, box-shadow .2s;
}
.contigency-photo-modal-thumb { width: 72px; height: 54px; }
.contigency-photo-thumb img, .contigency-photo-modal-thumb img { width: 100%; height: 100%; object-fit: cover; display: block; }
.contigency-photo-thumb.is-active, .contigency-photo-modal-thumb.is-active { border-color: #0041c8; box-shadow: 0 0 0 2px rgba(0,65,200,.25); }
.contigency-photo-modal-body { min-height: 50vh; background: #111; }
.contigency-photo-modal-img { max-width: 100%; max-height: min(78vh, 900px); object-fit: contain; user-select: none; }
.contigency-photo-nav {
    position: absolute; top: 50%; transform: translateY(-50%); z-index: 2;
    width: 2.75rem; height: 2.75rem; border: none; border-radius: 50%;
    background: rgba(255,255,255,.15); color: #fff;
    display: flex; align-items: center; justify-content: center; transition: background .2s;
}
.contigency-photo-nav:hover { background: rgba(255,255,255,.3); }
.contigency-photo-nav-prev { left: .75rem; }
.contigency-photo-nav-next { right: .75rem; }
</style>
@endpush

@push('scripts')
<script>
(function () {
    var urls = @json($fotoUrls);
    if (!urls.length) return;
    var idx = 0;
    var mainImg = document.getElementById('contigencyPhotoMain');
    var modalImg = document.getElementById('contigencyPhotoModalImg');
    var counter = document.getElementById('contigencyPhotoCounter');
    var thumbs = document.querySelectorAll('.contigency-photo-thumb');
    var modalThumbs = document.querySelectorAll('.contigency-photo-modal-thumb');
    var prevBtn = document.querySelector('.contigency-photo-nav-prev');
    var nextBtn = document.querySelector('.contigency-photo-nav-next');

    function setActive(i) {
        idx = (i + urls.length) % urls.length;
        if (mainImg) mainImg.src = urls[idx];
        if (modalImg) modalImg.src = urls[idx];
        if (counter) counter.textContent = urls.length > 1 ? '(' + (idx + 1) + ' / ' + urls.length + ')' : '';
        thumbs.forEach(function (el) { el.classList.toggle('is-active', parseInt(el.dataset.index, 10) === idx); });
        modalThumbs.forEach(function (el) { el.classList.toggle('is-active', parseInt(el.dataset.index, 10) === idx); });
    }
    function bindThumb(el) {
        el.addEventListener('click', function (e) { e.stopPropagation(); setActive(parseInt(el.dataset.index, 10)); });
    }
    thumbs.forEach(bindThumb);
    modalThumbs.forEach(bindThumb);
    if (prevBtn) prevBtn.addEventListener('click', function () { setActive(idx - 1); });
    if (nextBtn) nextBtn.addEventListener('click', function () { setActive(idx + 1); });
    document.getElementById('contigencyPhotoModal')?.addEventListener('keydown', function (e) {
        if (e.key === 'ArrowLeft') setActive(idx - 1);
        if (e.key === 'ArrowRight') setActive(idx + 1);
    });
    setActive(0);
})();
</script>
@endpush
@endif
@endsection
