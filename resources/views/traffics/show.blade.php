@extends('layouts.app')
@section('title', $pageTitle)
@section('page-title', $pageTitle)

@section('content')
@php
    $backUrl = $backUrl ?? route('report.traffic-analytic.index');
    $rowId = $data->id_trafficdalkot;
    $totalVol = (int) ($data->jumlah ?? (($data->r2 ?? 0) + ($data->r4 ?? 0) + ($data->r6 ?? 0) + ($data->r6_plus ?? 0)));
    $waktuLabel = $data->waktu ? substr((string) $data->waktu, 0, 5) : '—';
    $fotoUrls = collect(preg_split('/\s*,\s*/', (string) ($data->fotosit ?? ''), -1, PREG_SPLIT_NO_EMPTY))
        ->map(fn ($p) => \App\Http\Controllers\TrafficsController::fotoPublicUrl(trim((string) $p)))
        ->filter()
        ->values();
    $fotoUtama = $fotoUrls->first();
    $kParts = ! empty($data->koordinat) ? preg_split('/\s*,\s*/', (string) $data->koordinat, 2) : [];
    $kLat = trim($kParts[0] ?? '');
    $kLng = trim($kParts[1] ?? '');
    $kOk = $kLng !== '' && is_numeric($kLat) && is_numeric($kLng);
    $exportQuery = http_build_query(['id' => $rowId]);
@endphp
<div class="max-w-[960px] mx-auto w-full space-y-4">
    <div class="flex items-center gap-3 flex-wrap">
        <a href="{{ $backUrl }}" class="flex items-center gap-1.5 text-slate-500 hover:text-primary text-decoration-none text-label-md font-semibold transition-colors">
            <span class="material-symbols-outlined text-[18px]">arrow_back</span><span>Kembali</span>
        </a>
        <span class="text-slate-300">/</span>
        <span class="text-[13px] font-bold text-on-surface">{{ $data->gerbangkota ?: 'Detail' }}</span>
    </div>

    <div class="bg-white rounded-2xl border border-slate-200/60 shadow-sm overflow-hidden">
        <div class="px-5 py-3.5 border-b border-slate-100 bg-slate-50/30 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
            <div class="flex items-center gap-2 min-w-0">
                <span class="material-symbols-outlined text-primary text-[16px] shrink-0" style="font-variation-settings: 'FILL' 1;">analytics</span>
                <div class="min-w-0">
                    <h3 class="text-[13px] font-bold text-slate-800 m-0 truncate">{{ $data->gerbangkota ?: '—' }}</h3>
                    @if($data->araharus)
                        <p class="text-[11px] text-slate-500 m-0 mt-0.5">{{ $data->araharus }}</p>
                    @endif
                </div>
            </div>
            <span class="inline-flex items-center px-3 py-1.5 rounded-xl bg-primary/10 text-primary text-[13px] font-bold shrink-0">
                Total {{ number_format($totalVol, 0, ',', '.') }} kendaraan
            </span>
        </div>

        <div class="px-5 py-4 border-b border-slate-100 bg-slate-50/20">
            <p class="text-[10px] font-bold text-slate-500 uppercase tracking-wide mb-2 m-0">Volume kendaraan (sama seperti export)</p>
            <div class="grid grid-cols-5 gap-2">
                <div class="rounded-xl border border-slate-200 bg-white px-3 py-2.5 text-center">
                    <span class="block text-[10px] font-bold text-slate-400 uppercase">R2</span>
                    <span class="block text-[18px] font-extrabold text-primary leading-tight">{{ (int) ($data->r2 ?? 0) }}</span>
                </div>
                <div class="rounded-xl border border-slate-200 bg-white px-3 py-2.5 text-center">
                    <span class="block text-[10px] font-bold text-slate-400 uppercase">R4</span>
                    <span class="block text-[18px] font-extrabold text-primary leading-tight">{{ (int) ($data->r4 ?? 0) }}</span>
                </div>
                <div class="rounded-xl border border-slate-200 bg-white px-3 py-2.5 text-center">
                    <span class="block text-[10px] font-bold text-slate-400 uppercase">R6</span>
                    <span class="block text-[18px] font-extrabold text-primary leading-tight">{{ (int) ($data->r6 ?? 0) }}</span>
                </div>
                <div class="rounded-xl border border-slate-200 bg-white px-3 py-2.5 text-center">
                    <span class="block text-[10px] font-bold text-slate-400 uppercase">R6+</span>
                    <span class="block text-[18px] font-extrabold text-primary leading-tight">{{ (int) ($data->r6_plus ?? 0) }}</span>
                </div>
                <div class="rounded-xl border border-primary/20 bg-primary/5 px-3 py-2.5 text-center">
                    <span class="block text-[10px] font-bold text-primary uppercase">Jumlah</span>
                    <span class="block text-[18px] font-extrabold text-primary leading-tight">{{ number_format($totalVol, 0, ',', '.') }}</span>
                </div>
            </div>
        </div>

        <div class="p-5">
            <div class="grid grid-cols-1 {{ $fotoUtama ? 'lg:grid-cols-3' : '' }} gap-6">
                <div class="{{ $fotoUtama ? 'lg:col-span-2' : '' }}">
                    <dl class="grid grid-cols-1 md:grid-cols-2 gap-x-6 gap-y-3 mb-0">
                        <div>
                            <dt class="text-[11px] font-bold text-slate-500 uppercase tracking-wide mb-0.5">Polda</dt>
                            <dd class="text-[13px] font-bold m-0" style="color:#D8A808;">{{ $data->daerah_nama ? strtoupper($data->daerah_nama) : '—' }}</dd>
                        </div>
                        <div>
                            <dt class="text-[11px] font-bold text-slate-500 uppercase tracking-wide mb-0.5">Polres</dt>
                            <dd class="text-[13px] text-slate-800 font-medium m-0">{{ $data->wilayah_nama ? strtoupper(trim(str_ireplace(['KABUPATEN ', 'KOTA ', 'KAB. '], '', $data->wilayah_nama))) : '—' }}</dd>
                        </div>
                        <div>
                            <dt class="text-[11px] font-bold text-slate-500 uppercase tracking-wide mb-0.5">Tanggal</dt>
                            <dd class="text-[13px] text-slate-800 font-medium m-0">{{ $data->tanggal ? \Carbon\Carbon::parse($data->tanggal)->format('d/m/Y') : '—' }}</dd>
                        </div>
                        <div>
                            <dt class="text-[11px] font-bold text-slate-500 uppercase tracking-wide mb-0.5">Waktu</dt>
                            <dd class="text-[13px] text-slate-800 font-medium m-0">{{ $waktuLabel }}</dd>
                        </div>
                        <div>
                            <dt class="text-[11px] font-bold text-slate-500 uppercase tracking-wide mb-0.5">Gerbang Kota</dt>
                            <dd class="text-[13px] text-slate-800 font-semibold m-0">{{ $data->gerbangkota ?: '—' }}</dd>
                        </div>
                        <div>
                            <dt class="text-[11px] font-bold text-slate-500 uppercase tracking-wide mb-0.5">Arah Arus</dt>
                            <dd class="text-[13px] text-slate-800 font-medium m-0">{{ $data->araharus ?: '—' }}</dd>
                        </div>
                        <div>
                            <dt class="text-[11px] font-bold text-slate-500 uppercase tracking-wide mb-0.5">Jenis Jalan</dt>
                            <dd class="text-[13px] text-slate-800 font-medium m-0">{{ $data->jenisjalan ?: '—' }}</dd>
                        </div>
                        <div>
                            <dt class="text-[11px] font-bold text-slate-500 uppercase tracking-wide mb-0.5">Nama Jalan</dt>
                            <dd class="text-[13px] text-slate-800 font-medium m-0">{{ $data->namajalan ?: '—' }}</dd>
                        </div>
                        @if(!empty($data->koordinat))
                        <div class="md:col-span-2">
                            <dt class="text-[11px] font-bold text-slate-500 uppercase tracking-wide mb-0.5">Koordinat GPS</dt>
                            <dd class="m-0 flex items-center gap-2 flex-wrap">
                                <code class="bg-slate-100 px-2 py-0.5 rounded text-[12px]">{{ $data->koordinat }}</code>
                                @if($kOk)
                                    <a href="https://www.google.com/maps?q={{ $kLat }},{{ $kLng }}" target="_blank" rel="noopener noreferrer" class="flex items-center gap-1 text-primary hover:underline text-decoration-none text-[12px]">
                                        <span class="material-symbols-outlined text-[14px]">map</span>Google Maps
                                    </a>
                                @endif
                            </dd>
                        </div>
                        @endif
                        <div class="md:col-span-2">
                            <dt class="text-[11px] font-bold text-slate-500 uppercase tracking-wide mb-0.5">Keterangan CB</dt>
                            <dd class="text-[13px] text-slate-800 font-medium m-0 whitespace-pre-wrap">{{ $data->ketcb ?: '—' }}</dd>
                        </div>
                        <div>
                            <dt class="text-[11px] font-bold text-slate-500 uppercase tracking-wide mb-0.5">Piket</dt>
                            <dd class="text-[13px] text-slate-800 font-medium m-0">{{ $data->usm_nama ?: '—' }}</dd>
                        </div>
                        <div>
                            <dt class="text-[11px] font-bold text-slate-500 uppercase tracking-wide mb-0.5">ID Record</dt>
                            <dd class="text-[13px] text-slate-600 font-mono m-0">#{{ $rowId }}</dd>
                        </div>
                    </dl>
                </div>

                @if($fotoUtama)
                <div>
                    <p class="text-[11px] font-bold text-slate-500 uppercase tracking-wide mb-2 m-0">Foto Situasi</p>
                    <a href="{{ $fotoUtama }}" target="_blank" rel="noopener noreferrer" class="block mb-2">
                        <img src="{{ $fotoUtama }}" alt="Foto situasi" class="rounded-xl border border-slate-200 w-full" style="max-height:240px;object-fit:cover;" referrerpolicy="no-referrer">
                    </a>
                    @if($fotoUrls->count() > 1)
                        <div class="flex flex-wrap gap-2">
                            @foreach($fotoUrls->slice(1) as $extraUrl)
                                <a href="{{ $extraUrl }}" target="_blank" rel="noopener noreferrer">
                                    <img src="{{ $extraUrl }}" alt="Foto situasi" class="rounded-lg border border-slate-200 w-16 h-16 object-cover" referrerpolicy="no-referrer">
                                </a>
                            @endforeach
                        </div>
                    @endif
                </div>
                @endif
            </div>

            <div class="flex items-center justify-between pt-4 mt-4 border-t border-slate-100 flex-wrap gap-2">
                <a href="{{ $backUrl }}" class="flex items-center gap-2 px-4 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-600 font-bold rounded-xl text-label-md transition-all text-decoration-none">
                    <span class="material-symbols-outlined text-[18px]">arrow_back</span><span>Kembali</span>
                </a>
                <div class="flex items-center gap-2 flex-wrap">
                    <div class="dropdown">
                        <button type="button" class="flex items-center gap-2 px-4 py-2.5 bg-white border border-slate-200 hover:bg-slate-50 text-slate-700 font-bold rounded-xl text-label-md transition-all" data-bs-toggle="dropdown">
                            <span class="material-symbols-outlined text-[18px]">download</span><span>Export</span>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><a class="dropdown-item text-label-md py-2" href="{{ route('traffics.export', 'excel') }}?{{ $exportQuery }}">Excel</a></li>
                            <li><a class="dropdown-item text-label-md py-2" href="{{ route('traffics.export', 'csv') }}?{{ $exportQuery }}">CSV</a></li>
                            <li><a class="dropdown-item text-label-md py-2" href="{{ route('traffics.export', 'pdf') }}?{{ $exportQuery }}">PDF</a></li>
                        </ul>
                    </div>
                    @if($canMutate ?? false)
                        <a href="{{ route('traffics.edit', $rowId) }}" class="flex items-center gap-2 px-4 py-2.5 bg-amber-50 hover:bg-amber-100 text-amber-700 border border-amber-200 font-bold rounded-xl text-label-md transition-all text-decoration-none">
                            <span class="material-symbols-outlined text-[18px]">edit</span><span>Edit</span>
                        </a>
                        <form action="{{ route('traffics.destroy', $rowId) }}" method="POST" class="d-inline" onsubmit="return confirm('Yakin hapus data ini?');">
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
