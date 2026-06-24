@extends('layouts.app')
@section('title', $pageTitle)
@section('page-title', $pageTitle)

@section('content')
@php
    $sbGrp = $sbGrp ?? [];
    $indexRouteName = $indexRouteName ?? 'trafficstuck.index';
    $isAnalyticReport = $indexRouteName === 'report.traffic-analytic.index'
        || ($listSource ?? '') === 'trafficdalkot';
    $isDalkotList = $isAnalyticReport || ($listSource ?? '') === 'trafficdalkot';
    $isPatrolOnline = $isDalkotList
        || ($sbGrp['sb_grp'] ?? request('sb_grp') ?? session('trafficstuck_sb_grp') ?? '') === 'patrol_online';
    $hasFilters = $isDalkotList
        ? request()->hasAny(['tanggal_dari','tanggal_sampai','daerah','wilayah','jenisjalan','gerbangkota','araharus','search'])
        : request()->hasAny(['tanggal_dari','tanggal_sampai','daerah','wilayah','faktor','lokasi','search']);
    $createUrl = $isPatrolOnline ? route('traffics.create') : route('trafficstuck.create', $sbGrp);
@endphp
<div class="max-w-[1400px] mx-auto w-full space-y-4">

<section class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
    <div>
        <h2 class="text-[22px] font-extrabold text-on-surface tracking-tight leading-tight m-0">{{ $pageTitle }}</h2>
        <p class="text-[11px] text-slate-400 mt-0.5 m-0">{{ $isDalkotList ? 'Laporan volume lalu lintas patrol online (trafficdalkot)' : 'Data kemacetan dan gangguan lalu lintas (trafficstuck)' }}</p>
    </div>
    @unless($sboViewOnly ?? false)
        <a href="{{ $createUrl }}" class="flex items-center justify-center gap-2 bg-primary text-on-primary px-4 py-2.5 rounded-xl font-bold text-label-md shadow-lg shadow-primary/25 hover:shadow-xl hover:-translate-y-0.5 transition-all text-decoration-none shrink-0">
            <span class="material-symbols-outlined text-[18px]" style="font-variation-settings: 'FILL' 1;">add</span>
            <span>Tambah Data</span>
        </a>
    @endunless
</section>

@if(session('success'))
    <div class="rounded-xl px-4 py-2.5 flex items-center justify-between gap-3 border border-secondary/20 bg-secondary-container text-on-secondary-container shadow-sm">
        <div class="flex items-center gap-2"><span class="material-symbols-outlined text-[18px]" style="font-variation-settings: 'FILL' 1;">check_circle</span><span class="text-label-md font-medium">{{ session('success') }}</span></div>
        <button type="button" class="border-0 bg-transparent p-0" onclick="this.parentElement.remove()"><span class="material-symbols-outlined text-[18px]">close</span></button>
    </div>
@endif
@if(session('warning'))
    <div class="rounded-xl px-4 py-2.5 flex items-center justify-between gap-3 border border-amber-200 bg-amber-50 text-amber-800 shadow-sm">
        <div class="flex items-center gap-2"><span class="material-symbols-outlined text-[18px]" style="font-variation-settings: 'FILL' 1;">warning</span><span class="text-label-md font-medium">{{ session('warning') }}</span></div>
        <button type="button" class="border-0 bg-transparent p-0" onclick="this.parentElement.remove()"><span class="material-symbols-outlined text-[18px]">close</span></button>
    </div>
@endif

<form action="{{ route($indexRouteName) }}" method="GET" id="trafficstuckFilterForm" class="sbo-compact-filters bg-white rounded-lg p-2 border border-slate-200/60 shadow-sm relative z-10">
    @if($indexRouteName !== 'report.traffic-analytic.index')
        @foreach($sbGrp as $k => $v)<input type="hidden" name="{{ $k }}" value="{{ $v }}">@endforeach
    @endif
    <div class="pmf-row">
        @include('_partials.compact_filters.territory')
        @include('_partials.compact_filters.date_range', ['prefix' => 'trafficstuck'])
        @if($isDalkotList)
            @include('_partials.compact_filters.select', ['name' => 'jenisjalan', 'placeholder' => 'Jenis Jalan', 'options' => $jenisJalanList ?? [], 'autoSubmit' => false])
            @include('_partials.compact_filters.select', ['name' => 'gerbangkota', 'placeholder' => 'Gerbang', 'options' => $gerbangKotaList ?? [], 'autoSubmit' => false])
            @include('_partials.compact_filters.select', ['name' => 'araharus', 'placeholder' => 'Arah Arus', 'options' => $arahArusList ?? [], 'autoSubmit' => false])
        @else
            @include('_partials.compact_filters.select', ['name' => 'lokasi', 'placeholder' => 'Lokasi', 'options' => $lokasiList ?? [], 'autoSubmit' => false])
            @include('_partials.compact_filters.select', ['name' => 'faktor', 'placeholder' => 'Faktor', 'options' => $faktorList ?? [], 'autoSubmit' => false])
        @endif
        @include('_partials.compact_filters.search', ['placeholder' => $isDalkotList ? 'Cari gerbang, arah, jalan...' : 'Cari lokasi, faktor...'])
        @include('_partials.compact_filters.actions', [
            'resetUrl' => route($indexRouteName, $indexRouteName === 'report.traffic-analytic.index' ? [] : $sbGrp),
            'active' => $hasFilters,
        ])
    </div>
</form>

<section class="bg-white rounded-xl border border-slate-200/60 overflow-hidden shadow-sm">
    <div class="px-4 pt-3 border-b border-slate-100 bg-slate-50/30 flex items-center justify-end gap-3">
        <div class="dropdown pb-2">
            <button type="button" class="w-8 h-8 flex items-center justify-center hover:bg-slate-100 border border-slate-100 rounded-lg text-slate-500 bg-white" data-bs-toggle="dropdown" title="Export">
                <span class="material-symbols-outlined text-[17px]">download</span>
            </button>
            <ul class="dropdown-menu dropdown-menu-end">
                @if($isDalkotList)
                    <li><a class="dropdown-item text-label-md py-2" href="{{ route('traffics.export', 'excel') }}?{{ http_build_query(request()->except('page')) }}">Excel</a></li>
                    <li><a class="dropdown-item text-label-md py-2" href="{{ route('traffics.export', 'csv') }}?{{ http_build_query(request()->except('page')) }}">CSV</a></li>
                    <li><a class="dropdown-item text-label-md py-2" href="{{ route('traffics.export', 'pdf') }}?{{ http_build_query(request()->except('page')) }}">PDF</a></li>
                @else
                    <li><a class="dropdown-item text-label-md py-2" href="{{ route('trafficstuck.export', array_merge(['format' => 'excel'], $sbGrp)) }}?{{ http_build_query(request()->except('page')) }}">Excel</a></li>
                    <li><a class="dropdown-item text-label-md py-2" href="{{ route('trafficstuck.export', array_merge(['format' => 'csv'], $sbGrp)) }}?{{ http_build_query(request()->except('page')) }}">CSV</a></li>
                    <li><a class="dropdown-item text-label-md py-2" href="{{ route('trafficstuck.export', array_merge(['format' => 'pdf'], $sbGrp)) }}?{{ http_build_query(request()->except('page')) }}">PDF</a></li>
                @endif
            </ul>
        </div>
    </div>

    @if($data->count() > 0)
            <div class="overflow-x-auto custom-scrollbar">
                @if($isDalkotList)
                <table class="w-full text-left min-w-[1180px]">
                    <thead><tr class="bg-slate-50/50">
                        <th class="px-4 py-2 text-[10px] font-bold text-slate-400 uppercase tracking-widest border-b border-slate-100 w-10">#</th>
                        <th class="px-3 py-2 text-[10px] font-bold text-slate-400 uppercase tracking-widest border-b border-slate-100 w-14">Foto</th>
@if(\App\Support\TerritoryIndexFilters::canFilterByDaerah())
                            <th class="px-3 py-2 text-[10px] font-bold text-slate-400 uppercase tracking-widest border-b border-slate-100">Polda</th>
@endif
                        <th class="px-3 py-2 text-[10px] font-bold text-slate-400 uppercase tracking-widest border-b border-slate-100">Polres</th>
                        <th class="px-3 py-2 text-[10px] font-bold text-slate-400 uppercase tracking-widest border-b border-slate-100">Tanggal</th>
                        <th class="px-3 py-2 text-[10px] font-bold text-slate-400 uppercase tracking-widest border-b border-slate-100">Waktu</th>
                        <th class="px-3 py-2 text-[10px] font-bold text-slate-400 uppercase tracking-widest border-b border-slate-100">Gerbang / Arah</th>
                        <th class="px-3 py-2 text-[10px] font-bold text-slate-400 uppercase tracking-widest border-b border-slate-100">Jenis / Nama Jalan</th>
                        <th class="px-3 py-2 text-[10px] font-bold text-slate-400 uppercase tracking-widest border-b border-slate-100 text-center">R2</th>
                        <th class="px-3 py-2 text-[10px] font-bold text-slate-400 uppercase tracking-widest border-b border-slate-100 text-center">R4</th>
                        <th class="px-3 py-2 text-[10px] font-bold text-slate-400 uppercase tracking-widest border-b border-slate-100 text-center">R6</th>
                        <th class="px-3 py-2 text-[10px] font-bold text-slate-400 uppercase tracking-widest border-b border-slate-100 text-center">Total</th>
                        <th class="px-3 py-2 text-[10px] font-bold text-slate-400 uppercase tracking-widest border-b border-slate-100">Piket</th>
                        <th class="px-3 py-2 text-[10px] font-bold text-slate-400 uppercase tracking-widest border-b border-slate-100 text-right">Aksi</th>
                    </tr></thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach($data as $item)
                            @php
                                $canMutate = !($sboViewOnly ?? false) && \App\Support\RoleMutationPolicy::canMutateOperationalRow(auth()->user(), $item, 'trafficdalkot');
                                $rowId = $item->id_trafficdalkot;
                                $showUrl = route('traffics.show', $rowId);
                                $editUrl = route('traffics.edit', $rowId);
                                $destroyUrl = route('traffics.destroy', $rowId);
                                $fotoThumb = \App\Http\Controllers\TrafficsController::fotoPublicUrl($item->fotosit ?? null);
                                $totalVol = $item->jumlah ?? (($item->r2 ?? 0) + ($item->r4 ?? 0) + ($item->r6 ?? 0));
                            @endphp
                            <tr class="hover:bg-slate-50 group">
                                <td class="px-4 py-2.5 text-label-sm text-slate-400">{{ $loop->iteration + ($data->currentPage() - 1) * $data->perPage() }}</td>
                                <td class="px-3 py-2.5">
                                    @if($fotoThumb)
                                        <a href="{{ $showUrl }}" class="block shrink-0" title="Lihat foto situasi">
                                            <img src="{{ $fotoThumb }}" alt="Foto situasi" class="w-11 h-11 rounded-lg object-cover border border-slate-200 group-hover:scale-105 transition-transform" referrerpolicy="no-referrer">
                                        </a>
                                    @else
                                        <div class="w-11 h-11 rounded-lg bg-slate-100 flex items-center justify-center text-slate-300" title="Tanpa foto">
                                            <span class="material-symbols-outlined text-[20px]">image_not_supported</span>
                                        </div>
                                    @endif
                                </td>
@if(\App\Support\TerritoryIndexFilters::canFilterByDaerah())
                                    <td class="px-3 py-2.5"><span class="text-[11px] font-bold" style="color:#D8A808;">{{ strtoupper($item->daerah_nama ?? '—') }}</span></td>
@endif
                                <td class="px-3 py-2.5"><span class="text-[11px] font-semibold text-slate-600 uppercase">{{ $item->wilayah_nama ? strtoupper(trim(str_ireplace(['KABUPATEN ', 'KOTA ', 'KAB. '], '', $item->wilayah_nama))) : '—' }}</span></td>
                                <td class="px-3 py-2.5 text-[11px] text-slate-700">{{ $item->tanggal ? \Carbon\Carbon::parse($item->tanggal)->locale('id')->translatedFormat('d F Y') : '—' }}</td>
                                <td class="px-3 py-2.5 text-[11px] text-slate-500">{{ $item->waktu ? substr((string) $item->waktu, 0, 5) : '—' }}</td>
                                <td class="px-3 py-2.5">
                                    <a href="{{ $showUrl }}" class="text-[11px] font-semibold text-primary hover:underline text-decoration-none block">{{ $item->gerbangkota ?: '—' }}</a>
                                    <span class="text-[10px] text-slate-400">{{ $item->araharus ?? '' }}</span>
                                </td>
                                <td class="px-3 py-2.5">
                                    <span class="text-[11px] text-slate-700 block">{{ $item->jenisjalan ?? '—' }}</span>
                                    <span class="text-[10px] text-slate-400">{{ $item->namajalan ?? '' }}</span>
                                </td>
                                <td class="px-3 py-2.5 text-center text-[11px] text-slate-600">{{ $item->r2 ?? 0 }}</td>
                                <td class="px-3 py-2.5 text-center text-[11px] text-slate-600">{{ $item->r4 ?? 0 }}</td>
                                <td class="px-3 py-2.5 text-center text-[11px] text-slate-600">{{ $item->r6 ?? 0 }}</td>
                                <td class="px-3 py-2.5 text-center text-[12px] font-bold text-slate-800">{{ $totalVol }}</td>
                                <td class="px-3 py-2.5 text-[11px] text-slate-600">{{ $item->usm_nama ?? '—' }}</td>
                                <td class="px-3 py-2.5 text-right">
                                    <div class="flex items-center justify-end gap-1 opacity-100 lg:opacity-0 group-hover:opacity-100 transition-opacity">
                                        <a href="{{ $showUrl }}" class="w-8 h-8 flex items-center justify-center text-primary hover:bg-primary/10 rounded-lg transition-colors text-decoration-none" title="Detail"><span class="material-symbols-outlined text-[18px]">visibility</span></a>
                                        @if($canMutate)
                                            <a href="{{ $editUrl }}" class="w-8 h-8 flex items-center justify-center text-slate-400 hover:text-slate-700 hover:bg-slate-100 rounded-lg transition-colors text-decoration-none" title="Edit"><span class="material-symbols-outlined text-[18px]">edit</span></a>
                                            <form action="{{ $destroyUrl }}" method="POST" class="inline m-0" onsubmit="return confirm('Yakin hapus data ini?')">
                                                @csrf @method('DELETE')
                                                <button type="submit" class="w-8 h-8 flex items-center justify-center text-slate-400 hover:text-red-600 hover:bg-red-50 rounded-lg transition-colors border-0 bg-transparent p-0" title="Hapus"><span class="material-symbols-outlined text-[18px]">delete</span></button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
                @else
                <table class="w-full text-left min-w-[1040px]">
                    <thead><tr class="bg-slate-50/50">
                        <th class="px-4 py-2 text-[10px] font-bold text-slate-400 uppercase tracking-widest border-b border-slate-100 w-10">#</th>
                        <th class="px-3 py-2 text-[10px] font-bold text-slate-400 uppercase tracking-widest border-b border-slate-100 w-14">Foto</th>
@if(\App\Support\TerritoryIndexFilters::canFilterByDaerah())
                            <th class="px-3 py-2 text-[10px] font-bold text-slate-400 uppercase tracking-widest border-b border-slate-100">Polda</th>
@endif
                        <th class="px-3 py-2 text-[10px] font-bold text-slate-400 uppercase tracking-widest border-b border-slate-100">Polres</th>
                        <th class="px-3 py-2 text-[10px] font-bold text-slate-400 uppercase tracking-widest border-b border-slate-100">Tanggal</th>
                        <th class="px-3 py-2 text-[10px] font-bold text-slate-400 uppercase tracking-widest border-b border-slate-100">Lokasi</th>
                        <th class="px-3 py-2 text-[10px] font-bold text-slate-400 uppercase tracking-widest border-b border-slate-100">Status</th>
                        <th class="px-3 py-2 text-[10px] font-bold text-slate-400 uppercase tracking-widest border-b border-slate-100">Faktor</th>
                        <th class="px-3 py-2 text-[10px] font-bold text-slate-400 uppercase tracking-widest border-b border-slate-100">Penyebab</th>
                        <th class="px-3 py-2 text-[10px] font-bold text-slate-400 uppercase tracking-widest border-b border-slate-100">Durasi</th>
                        <th class="px-3 py-2 text-[10px] font-bold text-slate-400 uppercase tracking-widest border-b border-slate-100 text-right">Aksi</th>
                    </tr></thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach($data as $item)
                            @php
                                $canMutate = !($sboViewOnly ?? false) && \App\Support\RoleMutationPolicy::canMutateOperationalRow(auth()->user(), $item, $tableName ?? 'trafficstuck');
                                $showUrl = route('trafficstuck.show', array_merge(['trafficstuck' => $item->id_trafficstuck], $sbGrp));
                                $fotoThumb = \App\Http\Controllers\TrafficstuckController::fotoPublicUrl($item->fotosituasi ?? null);
                                $editUrl = route('trafficstuck.edit', array_merge(['trafficstuck' => $item->id_trafficstuck], $sbGrp));
                                $destroyUrl = route('trafficstuck.destroy', array_merge(['trafficstuck' => $item->id_trafficstuck], $sbGrp));
                                $destroyUsesSbGrpHidden = true;
                                $authUser = auth()->user();
                                $sameDaerah = $authUser && !empty($authUser->daerah) && !empty($item->daerah_nama) && strcasecmp((string) $authUser->daerah, (string) $item->daerah_nama) === 0;
                                $sameWilayah = $authUser && !empty($authUser->wilayah) && !empty($item->wilayah_nama) && strcasecmp((string) $authUser->wilayah, (string) $item->wilayah_nama) === 0;
                                $canConfirmOpsdal = !($sboViewOnly ?? false) && $sameDaerah && (empty($authUser->wilayah) || $sameWilayah);
                                $isPendingOpsdal = ($item->status_konfirmasi_opsdal ?? null) === 'menunggu';
                            @endphp
                            <tr class="hover:bg-slate-50 group">
                                <td class="px-4 py-2.5 text-label-sm text-slate-400">{{ $loop->iteration + ($data->currentPage() - 1) * $data->perPage() }}</td>
                                <td class="px-3 py-2.5">
                                    @if($fotoThumb)
                                        <a href="{{ $showUrl }}" class="block shrink-0" title="Lihat foto situasi">
                                            <img src="{{ $fotoThumb }}" alt="Foto situasi" class="w-11 h-11 rounded-lg object-cover border border-slate-200 group-hover:scale-105 transition-transform" referrerpolicy="no-referrer">
                                        </a>
                                    @else
                                        <div class="w-11 h-11 rounded-lg bg-slate-100 flex items-center justify-center text-slate-300" title="Tanpa foto">
                                            <span class="material-symbols-outlined text-[20px]">image_not_supported</span>
                                        </div>
                                    @endif
                                </td>
@if(\App\Support\TerritoryIndexFilters::canFilterByDaerah())
                                    <td class="px-3 py-2.5">
                                        <span class="text-[11px] font-bold" style="color:#D8A808;">{{ strtoupper($item->daerah_nama ?? '—') }}</span>
                                    </td>
@endif
                                <td class="px-3 py-2.5">
                                    <span class="text-[11px] font-semibold text-slate-600 uppercase">{{ $item->wilayah_nama ? strtoupper(trim(str_ireplace(['KABUPATEN ', 'KOTA ', 'KAB. '], '', $item->wilayah_nama))) : '—' }}</span>
                                </td>
                                <td class="px-3 py-2.5 text-[12px] font-semibold text-slate-700">{{ $item->tanggal ? \Carbon\Carbon::parse($item->tanggal)->locale('id')->translatedFormat('d F Y') : '—' }}</td>
                                <td class="px-3 py-2.5">
                                    <a href="{{ $showUrl }}"
                                       class="text-[11px] font-semibold text-primary hover:underline text-decoration-none">{{ \Str::limit($item->lokasi ?: 'Detail', 28) }}</a>
                                </td>
                                <td class="px-3 py-2.5">@include('trafficstuck._status_badges', ['item' => $item])</td>
                                <td class="px-3 py-2.5 text-[11px] text-slate-600 max-w-[100px] truncate" title="{{ $item->faktor ?: '—' }}">{{ \Str::limit($item->faktor ?: '—', 16) }}</td>
                                <td class="px-3 py-2.5 text-[11px] text-slate-600 max-w-[100px] truncate" title="{{ $item->penyebab ?: '—' }}">{{ \Str::limit($item->penyebab ?: '—', 16) }}</td>
                                <td class="px-3 py-2.5 text-[11px] text-slate-500">{{ $item->durasi ?: '—' }}</td>
                                <td class="px-3 py-2.5 text-right">
                                    <div class="flex items-center justify-end gap-1 opacity-100 lg:opacity-0 group-hover:opacity-100 transition-opacity">
                                        <a href="{{ $showUrl }}"
                                           class="w-8 h-8 flex items-center justify-center text-primary hover:bg-primary/10 rounded-lg transition-colors text-decoration-none" title="Detail">
                                            <span class="material-symbols-outlined text-[18px]">visibility</span>
                                        </a>
                                        @if($canMutate)
                                            <a href="{{ $editUrl }}"
                                               class="w-8 h-8 flex items-center justify-center text-slate-400 hover:text-slate-700 hover:bg-slate-100 rounded-lg transition-colors text-decoration-none" title="Edit">
                                                <span class="material-symbols-outlined text-[18px]">edit</span>
                                            </a>
                                            <form action="{{ $destroyUrl }}" method="POST" class="inline m-0"
                                                  onsubmit="return confirm('Yakin hapus data ini?')">
                                                @csrf @method('DELETE')
                                                @if($destroyUsesSbGrpHidden)
                                                    @include('trafficstuck._sb_grp_hidden', ['sbGrp' => $sbGrp])
                                                @endif
                                                <button type="submit" class="w-8 h-8 flex items-center justify-center text-slate-400 hover:text-red-600 hover:bg-red-50 rounded-lg transition-colors border-0 bg-transparent p-0" title="Hapus">
                                                    <span class="material-symbols-outlined text-[18px]">delete</span>
                                                </button>
                                            </form>
                                        @endif
                                        @if($canConfirmOpsdal && $isPendingOpsdal)
                                            <form action="{{ route('trafficstuck.confirm', array_merge(['trafficstuck' => $item->id_trafficstuck], $sbGrp)) }}" method="POST" class="inline m-0">
                                                @csrf
                                                @include('trafficstuck._sb_grp_hidden', ['sbGrp' => $sbGrp])
                                                <button type="submit" class="w-8 h-8 flex items-center justify-center text-emerald-600 hover:text-emerald-700 hover:bg-emerald-50 rounded-lg transition-colors border-0 bg-transparent p-0" title="Konfirmasi OPSDAL">
                                                    <span class="material-symbols-outlined text-[18px]">check</span>
                                                </button>
                                            </form>
                                            <form action="{{ route('trafficstuck.reject', array_merge(['trafficstuck' => $item->id_trafficstuck], $sbGrp)) }}" method="POST" class="inline m-0">
                                                @csrf
                                                @include('trafficstuck._sb_grp_hidden', ['sbGrp' => $sbGrp])
                                                <input type="hidden" name="opsdal_confirmation_note" value="Ditolak dari daftar OPSDAL">
                                                <button type="submit" class="w-8 h-8 flex items-center justify-center text-red-600 hover:text-red-700 hover:bg-red-50 rounded-lg transition-colors border-0 bg-transparent p-0" title="Tolak OPSDAL">
                                                    <span class="material-symbols-outlined text-[18px]">close</span>
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
                @endif
            </div>
            <div class="px-4 py-2.5 bg-slate-50/50 border-t border-slate-100 flex flex-col sm:flex-row items-center justify-between gap-3">
                <p class="text-[10px] text-slate-500 font-medium m-0"><span class="font-bold text-slate-700">{{ $data->firstItem() ?? 0 }}–{{ $data->lastItem() ?? 0 }}</span> dari {{ number_format($data->total(), 0, ',', '.') }}</p>
                <div class="pagination-container">{{ $data->appends(request()->query())->links() }}</div>
            </div>
        @else
            <div class="py-10 text-center">
                <div class="w-12 h-12 bg-slate-100 rounded-full flex items-center justify-center mx-auto mb-3"><span class="material-symbols-outlined text-[26px] text-slate-400">folder_off</span></div>
                <p class="text-label-md text-slate-500 font-medium m-0">
                    {{ $hasFilters ? 'Tidak ada data untuk filter yang dipilih.' : ($isDalkotList ? 'Belum ada data patrol online.' : 'Belum ada data trafficstuck.') }}
                </p>
                @if($hasFilters)
                    <a href="{{ route($indexRouteName, $indexRouteName === 'report.traffic-analytic.index' ? [] : $sbGrp) }}" class="inline-flex items-center gap-1.5 mt-3 px-3 py-2 bg-slate-100 hover:bg-slate-200 text-slate-600 font-bold rounded-xl text-label-md transition-all text-decoration-none">
                        <span class="material-symbols-outlined text-[16px]">cached</span> Reset
                    </a>
                @endif
            </div>
        @endif
</section>
</div>
@endsection

@push('styles')
<style>
    .pagination-container .pagination { margin: 0; display: flex; gap: 0.375rem; }
    .pagination-container .page-item .page-link {
        border-radius: 0.5rem !important; border: 1px solid #e2e8f0; color: #64748b;
        font-size: 11px; font-weight: 700; width: 32px; height: 32px;
        display: flex; align-items: center; justify-content: center; padding: 0; margin: 0; background: #fff;
    }
    .pagination-container .page-item.active .page-link { background-color: #0041c8; border-color: #0041c8; color: #fff; }
    .pagination-container .page-item.disabled .page-link { opacity: 0.5; background: #f8fafc; }
</style>
@endpush
