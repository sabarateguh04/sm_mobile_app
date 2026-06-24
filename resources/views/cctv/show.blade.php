@extends('layouts.app')
@section('title', $pageTitle ?? 'Detail Patroli CCTV')
@section('page-title', $pageTitle ?? 'Detail Patroli CCTV')

@section('content')
@php
    $tanggal = \App\Http\Controllers\CctvController::patrolValue($data, 'tanggal', $patrolCols);
    $lokasi = \App\Http\Controllers\CctvController::patrolValue($data, 'lokasi', $patrolCols);
    $lalin = \App\Http\Controllers\CctvController::patrolValue($data, 'situasilalin', $patrolCols);
    $cuaca = \App\Http\Controllers\CctvController::patrolValue($data, 'situasicuaca', $patrolCols);
    $peristiwa = \App\Http\Controllers\CctvController::patrolValue($data, 'peristiwa', $patrolCols);
    $ketcb = \App\Http\Controllers\CctvController::patrolValue($data, 'ketcb', $patrolCols);
    $fotoRaw = \App\Http\Controllers\CctvController::patrolValue($data, 'foto', $patrolCols);
    $fotoUrls = collect(array_filter(array_map('trim', explode(',', (string) $fotoRaw))))
        ->map(fn ($u) => \App\Http\Controllers\CctvController::fotoPublicUrl($u))
        ->filter()
        ->values();
    $hasFoto = $fotoUrls->isNotEmpty();

    $poldaNama = trim((string) ($data->daerah_nama ?? $data->daerah ?? ''));
    $polresNama = trim((string) ($data->wilayah_nama ?? $data->wilayah ?? ''));
    $judul = $lokasi ?: ('Patroli CCTV #'.$data->{$primaryKey});

    $kLat = $kLng = null;
    $kOk = false;
    if (! empty($data->koordinat)) {
        $kParts = preg_split('/\s*,\s*/', (string) $data->koordinat, 2);
        $kLat = $kParts[0] ?? '';
        $kLng = $kParts[1] ?? '';
        $kOk = $kLng !== '' && is_numeric($kLat) && is_numeric($kLng);
    }

    $rowId = $data->{$primaryKey};
    $routePrefix = request()->routeIs('cctv.*') ? 'cctv' : 'patrolicctv';
    $exportRouteName = $routePrefix.'.export';
@endphp
<div class="max-w-[900px] mx-auto w-full space-y-4">
    <div class="flex items-center gap-3 flex-wrap">
        <a href="{{ route($routePrefix.'.index') }}" class="flex items-center gap-1.5 text-slate-500 hover:text-primary text-decoration-none text-label-md font-semibold transition-colors">
            <span class="material-symbols-outlined text-[18px]">arrow_back</span><span>Kembali</span>
        </a>
        <span class="text-slate-300">/</span>
        <span class="text-[13px] font-bold text-on-surface">{{ \Str::limit($judul, 48) }}</span>
    </div>

    <div class="bg-white rounded-2xl border border-slate-200/60 shadow-sm overflow-hidden">
        <div class="px-5 py-3.5 border-b border-slate-100 bg-slate-50/30 flex items-center justify-between gap-2 flex-wrap">
            <div class="flex items-center gap-2 min-w-0">
                <span class="material-symbols-outlined text-primary text-[16px] shrink-0" style="font-variation-settings: 'FILL' 1;">videocam</span>
                <h3 class="text-[13px] font-bold text-slate-800 m-0 truncate">{{ $judul }}</h3>
            </div>
            @if($peristiwa && ($patrolCols['peristiwa'] ?? null))
                <span class="inline-flex items-center px-2.5 py-1 rounded-lg bg-slate-800 text-white text-[11px] font-bold shrink-0">{{ $peristiwa }}</span>
            @elseif($lalin && ($patrolCols['situasilalin'] ?? null))
                <span class="inline-flex items-center px-2.5 py-1 rounded-lg bg-primary/10 text-primary text-[11px] font-bold shrink-0">{{ $lalin }}</span>
            @endif
        </div>

        <div class="p-5">
            <div class="flex flex-col gap-6">
                <div>
                    <dl class="grid grid-cols-[140px_1fr] md:grid-cols-[160px_1fr] gap-x-4 gap-y-3 mb-0 items-center">
                        @if($poldaNama !== '' || $polresNama !== '')
                            <dt class="text-[11px] font-bold text-slate-500 uppercase tracking-wide">Polda</dt>
                            <dd class="text-[13px] font-bold m-0" style="color:#D8A808;">{{ $poldaNama !== '' ? strtoupper($poldaNama) : '—' }}</dd>

                            <dt class="text-[11px] font-bold text-slate-500 uppercase tracking-wide">Polres</dt>
                            <dd class="text-[13px] text-slate-800 font-medium m-0">{{ $polresNama !== '' ? strtoupper(trim(str_ireplace(['KABUPATEN ', 'KOTA ', 'KAB. '], '', $polresNama))) : '—' }}</dd>
                        @endif

                        <dt class="text-[11px] font-bold text-slate-500 uppercase tracking-wide">Tanggal</dt>
                        <dd class="text-[13px] text-slate-800 font-medium m-0">{{ $tanggal ? \Carbon\Carbon::parse($tanggal)->format('d/m/Y') : '—' }}</dd>

                        <dt class="text-[11px] font-bold text-slate-500 uppercase tracking-wide">Petugas</dt>
                        <dd class="text-[13px] text-slate-800 font-medium m-0">{{ $data->petugas_nama ?? '—' }}</dd>

                        <dt class="text-[11px] font-bold text-slate-500 uppercase tracking-wide self-start mt-0.5">Lokasi</dt>
                        <dd class="text-[13px] text-slate-800 font-semibold m-0 leading-relaxed">{{ $lokasi ?: '—' }}</dd>

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

                        @if($patrolCols['situasilalin'] ?? null)
                            <dt class="text-[11px] font-bold text-slate-500 uppercase tracking-wide">Situasi Lalin</dt>
                            <dd class="text-[13px] text-slate-800 font-medium m-0">{{ $lalin ?: '—' }}</dd>
                        @endif

                        @if($patrolCols['situasicuaca'] ?? null)
                            <dt class="text-[11px] font-bold text-slate-500 uppercase tracking-wide">Situasi Cuaca</dt>
                            <dd class="text-[13px] text-slate-800 font-medium m-0">{{ $cuaca ?: '—' }}</dd>
                        @endif

                        @if($patrolCols['peristiwa'] ?? null)
                            <dt class="text-[11px] font-bold text-slate-500 uppercase tracking-wide self-start mt-0.5">Peristiwa</dt>
                            <dd class="text-[13px] text-slate-800 font-medium m-0">{{ $peristiwa ?: '—' }}</dd>
                        @endif

                        @if($patrolCols['ketcb'] ?? null)
                            <dt class="text-[11px] font-bold text-slate-500 uppercase tracking-wide self-start mt-0.5">CB / Ket CB</dt>
                            <dd class="text-[13px] text-slate-800 font-medium m-0 whitespace-pre-line">{{ $ketcb ?: '—' }}</dd>
                        @endif
                    </dl>
                </div>

                @if($hasFoto)
                    <div class="pt-4 border-t border-slate-100">
                        <p class="text-[11px] font-bold text-slate-500 uppercase tracking-wide mb-2 flex items-center gap-1.5">
                            <span class="material-symbols-outlined text-[14px] text-primary">photo_camera</span>
                            Capture
                            @if($kOk)
                                <span class="inline-flex items-center gap-0.5 text-emerald-600 normal-case font-bold text-[10px]">
                                    <span class="material-symbols-outlined text-[12px]">location_on</span>GPS
                                </span>
                            @endif
                        </p>
                        <div class="flex flex-wrap gap-4">
                            @foreach($fotoUrls as $fotoUrl)
                                <a href="{{ $fotoUrl }}" target="_blank" rel="noopener noreferrer" class="block rounded-xl overflow-hidden border border-slate-200 hover:opacity-95 transition-opacity max-w-[250px]">
                                    <img src="{{ $fotoUrl }}" alt="Capture patroli" class="w-full rounded-xl" style="max-height:200px;object-fit:cover;" referrerpolicy="no-referrer">
                                </a>
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>

            <div class="flex items-center justify-between pt-4 mt-4 border-t border-slate-100 flex-wrap gap-2">
                <a href="{{ route($routePrefix.'.index') }}" class="flex items-center gap-2 px-4 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-600 font-bold rounded-xl text-label-md transition-all text-decoration-none">
                    <span class="material-symbols-outlined text-[18px]">arrow_back</span><span>Kembali</span>
                </a>
                <div class="flex items-center gap-2 flex-wrap">
                    @include('_partials.export_dropdown', [
                        'routeName' => $exportRouteName,
                        'query' => ['id' => $rowId],
                        'variant' => 'hero',
                    ])
                    @unless(\App\Helpers\MenuHelper::isExecutiveForLayout(auth()->user()))
                        <a href="{{ route($routePrefix.'.edit', $rowId) }}" class="flex items-center gap-2 px-4 py-2.5 bg-amber-50 hover:bg-amber-100 text-amber-700 border border-amber-200 font-bold rounded-xl text-label-md transition-all text-decoration-none">
                            <span class="material-symbols-outlined text-[18px]">edit</span><span>Edit</span>
                        </a>
                        <form action="{{ route($routePrefix.'.destroy', $rowId) }}" method="POST" class="d-inline m-0" onsubmit="return confirm('Yakin hapus laporan patroli ini?');">
                            @csrf @method('DELETE')
                            <button type="submit" class="flex items-center gap-2 px-4 py-2.5 bg-red-50 hover:bg-red-100 text-red-700 border border-red-200 font-bold rounded-xl text-label-md transition-all border-0">
                                <span class="material-symbols-outlined text-[18px]">delete</span><span>Hapus</span>
                            </button>
                        </form>
                    @endunless
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
