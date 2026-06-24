@extends('layouts.app')
@section('title', $pageTitle)
@section('page-title', $pageTitle)

@section('content')
@php
    $sbGrp = $sbGrp ?? [];
    $isPatrolOnline = ($sbGrp['sb_grp'] ?? request('sb_grp') ?? '') === 'patrol_online';
    $canMutate = ($canMutateRecord ?? false) && !($sboViewOnly ?? false);
    $editUrl = ($isPatrolOnline && ! empty($data->id_trafficdalkot))
        ? route('traffics.edit', ['id' => $data->id_trafficdalkot])
        : route('trafficstuck.edit', array_merge(['trafficstuck' => $data->id_trafficstuck], $sbGrp));
    $destroyUrl = ($isPatrolOnline && ! empty($data->id_trafficdalkot))
        ? route('traffics.destroy', ['id' => $data->id_trafficdalkot])
        : route('trafficstuck.destroy', array_merge(['trafficstuck' => $data->id_trafficstuck], $sbGrp));
    $authUser = auth()->user();
    $sameDaerah = $authUser && !empty($authUser->daerah) && !empty($data->daerah_nama) && strcasecmp((string) $authUser->daerah, (string) $data->daerah_nama) === 0;
    $sameWilayah = $authUser && !empty($authUser->wilayah) && !empty($data->wilayah_nama) && strcasecmp((string) $authUser->wilayah, (string) $data->wilayah_nama) === 0;
    $canConfirmOpsdal = !($sboViewOnly ?? false) && $sameDaerah && (empty($authUser->wilayah) || $sameWilayah);
    $isPendingOpsdal = ($data->status_konfirmasi_opsdal ?? null) === 'menunggu';
@endphp
<div class="max-w-[900px] mx-auto w-full space-y-4">
    <div class="flex items-center gap-3">
        <a href="{{ route('trafficstuck.index', $sbGrp) }}" class="flex items-center gap-1.5 text-slate-500 hover:text-primary text-decoration-none text-label-md font-semibold transition-colors">
            <span class="material-symbols-outlined text-[18px]">arrow_back</span><span>Kembali</span>
        </a>
        <span class="text-slate-300">/</span>
        <span class="text-[13px] font-bold text-on-surface">{{ $pageTitle }}</span>
    </div>
    <div class="bg-white rounded-2xl border border-slate-200/60 shadow-sm overflow-hidden">
        <div class="px-5 py-3.5 border-b border-slate-100 bg-slate-50/30 flex items-center justify-between gap-2">
            <div class="flex items-center gap-2">
                <span class="material-symbols-outlined text-primary text-[16px]" style="font-variation-settings: 'FILL' 1;">traffic</span>
                <h3 class="text-[13px] font-bold text-slate-800 m-0">{{ $pageTitle }}</h3>
            </div>
            @include('trafficstuck._status_badges', ['item' => $data, 'expanded' => true])
        </div>
        <div class="p-5">
            <dl class="grid grid-cols-[140px_1fr] md:grid-cols-[160px_1fr] gap-x-4 gap-y-3 mb-0 items-center">
                <dt class="text-[11px] font-bold text-slate-500 uppercase tracking-wide">Tanggal</dt>
                <dd class="text-[13px] text-slate-800 font-medium m-0">{{ $data->tanggal ? \Carbon\Carbon::parse($data->tanggal)->locale('id')->translatedFormat('d F Y') : '-' }}</dd>
                
                <dt class="text-[11px] font-bold text-slate-500 uppercase tracking-wide">Jam Peristiwa</dt>
                <dd class="text-[13px] text-slate-800 font-medium m-0">{{ $data->jamperistiwa ? \Carbon\Carbon::parse($data->jamperistiwa)->format('H:i') . ' WIB' : '-' }}</dd>
                
                <dt class="text-[11px] font-bold text-slate-500 uppercase tracking-wide self-start mt-0.5">Lokasi</dt>
                <dd class="text-[13px] text-slate-800 font-medium m-0">{{ $data->lokasi ?? '-' }}</dd>
                
                @if(!empty($data->koordinat))
                @php
                    $kParts = preg_split('/\s*,\s*/', (string) $data->koordinat, 2);
                    $kLat = $kParts[0] ?? '';
                    $kLng = $kParts[1] ?? '';
                    $kOk = $kLng !== '' && is_numeric($kLat) && is_numeric($kLng);
                @endphp
                <dt class="text-[11px] font-bold text-slate-500 uppercase tracking-wide self-start mt-1">Koordinat GPS</dt>
                <dd class="text-[13px] text-slate-800 font-medium m-0 flex items-center gap-2 flex-wrap">
                    <code class="bg-slate-100 px-2 py-0.5 rounded text-[12px]">{{ $data->koordinat }}</code>
                    @if($kOk)
                        <a href="https://www.google.com/maps?q={{ $kLat }},{{ $kLng }}" target="_blank" rel="noopener noreferrer" class="flex items-center gap-1 text-primary hover:underline text-decoration-none text-[12px]">
                            <span class="material-symbols-outlined text-[14px]">map</span>Google Maps
                        </a>
                    @endif
                </dd>
                @endif
                
                <dt class="text-[11px] font-bold text-slate-500 uppercase tracking-wide">Faktor</dt>
                <dd class="text-[13px] text-slate-800 font-medium m-0">{{ $data->faktor ?? '-' }}</dd>
                
                <dt class="text-[11px] font-bold text-slate-500 uppercase tracking-wide">Durasi Perlambatan</dt>
                <dd class="text-[13px] text-slate-800 font-medium m-0">{{ $data->durasi ?? '-' }}</dd>
                
                <dt class="text-[11px] font-bold text-slate-500 uppercase tracking-wide self-start mt-0.5">Penyebab</dt>
                <dd class="text-[13px] text-slate-800 font-medium m-0">{{ $data->penyebab ?? '-' }}</dd>
                
                <dt class="text-[11px] font-bold text-slate-500 uppercase tracking-wide">Tim Pengurai</dt>
                <dd class="text-[13px] text-slate-800 font-medium m-0">{{ $data->timurai ?? '-' }}</dd>
                
                <dt class="text-[11px] font-bold text-slate-500 uppercase tracking-wide self-start mt-0.5">Keterangan CB</dt>
                <dd class="text-[13px] text-slate-800 font-medium m-0 whitespace-pre-wrap">{!! nl2br(e($data->ketcb ?? '-')) !!}</dd>
                
                <dt class="text-[11px] font-bold text-slate-500 uppercase tracking-wide">Piket</dt>
                <dd class="text-[13px] text-slate-800 font-medium m-0">{{ $data->usm_nama ?? '-' }}</dd>
                
                <dt class="text-[11px] font-bold text-slate-500 uppercase tracking-wide">Polda</dt>
                <dd class="text-[13px] font-bold m-0" style="color:#D8A808;">{{ strtoupper($data->daerah_nama ?? '-') }}</dd>
                
                <dt class="text-[11px] font-bold text-slate-500 uppercase tracking-wide">Polres</dt>
                <dd class="text-[13px] text-slate-800 font-medium m-0">{{ !empty($data->wilayah_nama) ? strtoupper(trim(str_ireplace(['KABUPATEN ', 'KOTA ', 'KAB. '], '', $data->wilayah_nama))) : '-' }}</dd>
            </dl>
            @if(!empty($data->fotosituasi))
                <div class="mt-4 pt-4 border-t border-slate-100">
                    <p class="text-[11px] font-bold text-slate-500 uppercase tracking-wide mb-2 m-0">Foto Situasi</p>
                    <div class="flex flex-wrap gap-2 mt-2">
                        @foreach(explode(',', (string) $data->fotosituasi) as $foto)
                            @php $f = trim($foto); if ($f === '') continue; $url = str_starts_with($f, 'http') ? $f : asset(ltrim($f, '/')); @endphp
                            <a href="{{ $url }}" target="_blank" rel="noopener noreferrer" class="block">
                                <img src="{{ $url }}" alt="Foto situasi" class="rounded-xl border border-slate-200 object-cover" style="width:160px;height:120px;">
                            </a>
                        @endforeach
                    </div>
                </div>
            @endif
            <div class="flex items-center justify-between pt-4 mt-4 border-t border-slate-100 flex-wrap gap-2">
                <a href="{{ route('trafficstuck.index', $sbGrp) }}" class="flex items-center gap-2 px-4 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-600 font-bold rounded-xl text-label-md transition-all text-decoration-none">
                    <span class="material-symbols-outlined text-[18px]">arrow_back</span><span>Kembali</span>
                </a>
                <div class="flex items-center gap-2">
                    @php
                        $trafficExportRoute = ($isPatrolOnline && ! empty($data->id_trafficdalkot)) ? 'traffics.export' : 'trafficstuck.export';
                        $trafficExportId = ($isPatrolOnline && ! empty($data->id_trafficdalkot)) ? $data->id_trafficdalkot : $data->id_trafficstuck;
                        $trafficExportParams = ($isPatrolOnline && ! empty($data->id_trafficdalkot)) ? [] : $sbGrp;
                    @endphp
                    @include('_partials.show_export_dropdown', [
                        'exportRouteName' => $trafficExportRoute,
                        'rowId' => $trafficExportId,
                        'exportRouteParams' => $trafficExportParams,
                    ])
                    @if($canConfirmOpsdal && $isPendingOpsdal)
                        <form action="{{ route('trafficstuck.confirm', array_merge(['trafficstuck' => $data->id_trafficstuck], $sbGrp)) }}" method="POST" class="d-inline">
                            @csrf
                            @include('trafficstuck._sb_grp_hidden', ['sbGrp' => $sbGrp])
                            <button type="submit" class="flex items-center gap-2 px-4 py-2.5 bg-emerald-50 hover:bg-emerald-100 text-emerald-700 border border-emerald-200 font-bold rounded-xl text-label-md transition-all border-0">
                                <span class="material-symbols-outlined text-[18px]">check_circle</span><span>Konfirmasi OPSDAL</span>
                            </button>
                        </form>
                        <form action="{{ route('trafficstuck.reject', array_merge(['trafficstuck' => $data->id_trafficstuck], $sbGrp)) }}" method="POST" class="d-inline">
                            @csrf
                            @include('trafficstuck._sb_grp_hidden', ['sbGrp' => $sbGrp])
                            <input type="hidden" name="opsdal_confirmation_note" value="Ditolak dari halaman detail OPSDAL">
                            <button type="submit" class="flex items-center gap-2 px-4 py-2.5 bg-red-50 hover:bg-red-100 text-red-700 border border-red-200 font-bold rounded-xl text-label-md transition-all border-0">
                                <span class="material-symbols-outlined text-[18px]">cancel</span><span>Tolak OPSDAL</span>
                            </button>
                        </form>
                    @endif
                    @if($canMutate)
                        <a href="{{ $editUrl }}" class="flex items-center gap-2 px-4 py-2.5 bg-amber-50 hover:bg-amber-100 text-amber-700 border border-amber-200 font-bold rounded-xl text-label-md transition-all text-decoration-none">
                            <span class="material-symbols-outlined text-[18px]">edit</span><span>Edit</span>
                        </a>
                        <form action="{{ $destroyUrl }}" method="POST" class="d-inline" onsubmit="return confirm('Yakin hapus data ini?');">
                            @csrf @method('DELETE')
                            @if(! ($isPatrolOnline && ! empty($data->id_trafficdalkot)))
                                @include('trafficstuck._sb_grp_hidden', ['sbGrp' => $sbGrp])
                            @endif
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
