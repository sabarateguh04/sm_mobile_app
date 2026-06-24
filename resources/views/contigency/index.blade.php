@extends('layouts.app')
@section('title', $pageTitle)
@section('page-title', $pageTitle)

@section('content')
@php
    $sbGrp = $sbGrp ?? [];
    $hasFilters = request()->hasAny(['tanggal_dari','tanggal_sampai','daerah','wilayah','jenis_contigency','faktor','search']);
@endphp
<div class="max-w-[1400px] mx-auto w-full space-y-4">

<section class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
    <div>
        <h2 class="text-[22px] font-extrabold text-on-surface tracking-tight leading-tight m-0">{{ $pageTitle }}</h2>
        <p class="text-[11px] text-slate-400 mt-0.5 m-0">Data rencana kontijensi lalu lintas</p>
    </div>
    @unless($sboViewOnly ?? false)
        <a href="{{ route('contigency.create', $sbGrp) }}" class="flex items-center justify-center gap-2 bg-primary text-on-primary px-4 py-2.5 rounded-xl font-bold text-label-md shadow-lg shadow-primary/25 hover:shadow-xl hover:-translate-y-0.5 transition-all text-decoration-none shrink-0">
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
@if(session('info'))
    <div class="rounded-xl px-4 py-2.5 flex items-center gap-3 border border-primary/20 bg-primary/5 text-slate-700 shadow-sm">
        <span class="material-symbols-outlined text-[18px] text-primary" style="font-variation-settings: 'FILL' 1;">info</span>
        <span class="text-label-md">{{ session('info') }}</span>
    </div>
@endif

<form action="{{ route('contigency.index') }}" method="GET" id="contigencyFilterForm" class="sbo-compact-filters bg-white rounded-lg p-2 border border-slate-200/60 shadow-sm relative z-10">
    @foreach($sbGrp as $k => $v)<input type="hidden" name="{{ $k }}" value="{{ $v }}">@endforeach
    <div class="pmf-row">
        @include('_partials.compact_filters.territory')
        @include('_partials.compact_filters.date_range', ['prefix' => 'contigency'])
        @include('_partials.compact_filters.select', ['name' => 'jenis_contigency', 'placeholder' => 'Jenis', 'options' => $jenisList ?? [], 'autoSubmit' => false])
        @include('_partials.compact_filters.select', ['name' => 'faktor', 'placeholder' => 'Faktor', 'options' => $faktorList ?? [], 'autoSubmit' => false])
        @include('_partials.compact_filters.search', ['placeholder' => 'Cari lokasi, jenis...'])
        @include('_partials.compact_filters.actions', [
            'resetUrl' => route('contigency.index', $sbGrp),
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
                <li><a class="dropdown-item text-label-md py-2" href="{{ route('contigency.export', array_merge(['format' => 'excel'], $sbGrp)) }}?{{ http_build_query(request()->except('page')) }}">Excel</a></li>
                <li><a class="dropdown-item text-label-md py-2" href="{{ route('contigency.export', array_merge(['format' => 'csv'], $sbGrp)) }}?{{ http_build_query(request()->except('page')) }}">CSV</a></li>
                <li><a class="dropdown-item text-label-md py-2" href="{{ route('contigency.export', array_merge(['format' => 'pdf'], $sbGrp)) }}?{{ http_build_query(request()->except('page')) }}">PDF</a></li>
            </ul>
        </div>
    </div>

    @if($data->count() > 0)
            <div class="overflow-x-auto custom-scrollbar">
                <table class="w-full text-left min-w-[900px]">
                    <thead><tr class="bg-slate-50/50">
                        <th class="px-4 py-2 text-[10px] font-bold text-slate-400 uppercase tracking-widest border-b border-slate-100 w-10">#</th>
                        <th class="px-3 py-2 text-[10px] font-bold text-slate-400 uppercase tracking-widest border-b border-slate-100">Wilayah</th>
                        <th class="px-3 py-2 text-[10px] font-bold text-slate-400 uppercase tracking-widest border-b border-slate-100">Tanggal</th>
                        <th class="px-3 py-2 text-[10px] font-bold text-slate-400 uppercase tracking-widest border-b border-slate-100">Lokasi</th>
                        <th class="px-3 py-2 text-[10px] font-bold text-slate-400 uppercase tracking-widest border-b border-slate-100">Status</th>
                        <th class="px-3 py-2 text-[10px] font-bold text-slate-400 uppercase tracking-widest border-b border-slate-100">Jenis</th>
                        <th class="px-3 py-2 text-[10px] font-bold text-slate-400 uppercase tracking-widest border-b border-slate-100">Petugas TPTKP</th>
                        <th class="px-3 py-2 text-[10px] font-bold text-slate-400 uppercase tracking-widest border-b border-slate-100 text-right">Aksi</th>
                    </tr></thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach($data as $item)
                            @php
                                $canMutate = !($sboViewOnly ?? false) && \App\Support\RoleMutationPolicy::canMutateOperationalRow(auth()->user(), $item, $tableName ?? 'contigency');
                                $authUser = auth()->user();
                                $sameDaerah = $authUser && !empty($authUser->daerah) && !empty($item->daerah_nama) && strcasecmp((string) $authUser->daerah, (string) $item->daerah_nama) === 0;
                                $sameWilayah = $authUser && !empty($authUser->wilayah) && !empty($item->wilayah_nama) && strcasecmp((string) $authUser->wilayah, (string) $item->wilayah_nama) === 0;
                                $canConfirmOpsdal = !($sboViewOnly ?? false) && $sameDaerah && (empty($authUser->wilayah) || $sameWilayah);
                                $isPendingOpsdal = ($item->status_konfirmasi_opsdal ?? null) === 'menunggu';
                            @endphp
                            <tr class="hover:bg-slate-50 group">
                                <td class="px-4 py-2.5 text-label-sm text-slate-400">{{ $loop->iteration + ($data->currentPage() - 1) * $data->perPage() }}</td>
                                <td class="px-3 py-2.5">
                                    @if(\App\Support\TerritoryIndexFilters::canFilterByDaerah())
                                        <span class="text-[10px] font-bold block" style="color:#D8A808;">{{ strtoupper($item->daerah_nama ?? '—') }}</span>
                                    @endif
                                    <span class="text-[9px] text-slate-400 uppercase">{{ $item->wilayah_nama ? strtoupper(trim(str_ireplace(['KABUPATEN ', 'KOTA ', 'KAB. '], '', $item->wilayah_nama))) : '' }}</span>
                                </td>
                                <td class="px-3 py-2.5 text-[12px] font-semibold text-slate-700">{{ $item->tanggal ? \Carbon\Carbon::parse($item->tanggal)->locale('id')->translatedFormat('d F Y') : '—' }}</td>
                                <td class="px-3 py-2.5">
                                    <a href="{{ route('contigency.show', array_merge(['contigency' => $item->id_contigency], $sbGrp)) }}"
                                       class="text-[11px] font-semibold text-primary hover:underline text-decoration-none">{{ \Str::limit($item->lokasi ?: 'Detail', 28) }}</a>
                                </td>
                                <td class="px-3 py-2.5">@include('trafficstuck._status_badges', ['item' => $item])</td>
                                <td class="px-3 py-2.5">
                                    @if($item->jeniscontigency)
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-slate-800 text-white">{{ $item->jeniscontigency }}</span>
                                    @else
                                        <span class="text-slate-400">—</span>
                                    @endif
                                </td>
                                <td class="px-3 py-2.5 text-[11px] text-slate-600">{{ $item->petugastptkp ?? ($item->usm_nama ?? '—') }}</td>
                                <td class="px-3 py-2.5 text-right">
                                    <div class="flex items-center justify-end gap-1 opacity-100 lg:opacity-0 group-hover:opacity-100 transition-opacity">
                                        <a href="{{ route('contigency.show', array_merge(['contigency' => $item->id_contigency], $sbGrp)) }}"
                                           class="w-8 h-8 flex items-center justify-center text-primary hover:bg-primary/10 rounded-lg transition-colors text-decoration-none" title="Detail">
                                            <span class="material-symbols-outlined text-[18px]">visibility</span>
                                        </a>
                                        @if(!empty($item->piket ?? $item->id_usm))
                                            <a href="{{ route('personil.manager.show', $item->piket ?? $item->id_usm) }}" class="w-8 h-8 flex items-center justify-center text-indigo-500 hover:bg-indigo-50 rounded-lg transition-colors text-decoration-none" title="Profil Operator"><span class="material-symbols-outlined text-[18px]">account_circle</span></a>
                                        @endif
                                        @if($canMutate)
                                            <a href="{{ route('contigency.edit', array_merge(['contigency' => $item->id_contigency], $sbGrp)) }}"
                                               class="w-8 h-8 flex items-center justify-center text-slate-400 hover:text-slate-700 hover:bg-slate-100 rounded-lg transition-colors text-decoration-none" title="Edit">
                                                <span class="material-symbols-outlined text-[18px]">edit</span>
                                            </a>
                                            <form action="{{ route('contigency.destroy', array_merge(['contigency' => $item->id_contigency], $sbGrp)) }}" method="POST" class="inline m-0"
                                                  onsubmit="return confirm('Yakin hapus data ini?')">
                                                @csrf @method('DELETE')
                                                @include('contigency._sb_grp_hidden', ['sbGrp' => $sbGrp])
                                                <button type="submit" class="w-8 h-8 flex items-center justify-center text-slate-400 hover:text-red-600 hover:bg-red-50 rounded-lg transition-colors border-0 bg-transparent p-0" title="Hapus">
                                                    <span class="material-symbols-outlined text-[18px]">delete</span>
                                                </button>
                                            </form>
                                        @endif
                                        @if($canConfirmOpsdal && $isPendingOpsdal)
                                            <form action="{{ route('contigency.confirm', array_merge(['contigency' => $item->id_contigency], $sbGrp)) }}" method="POST" class="inline m-0">
                                                @csrf
                                                @include('contigency._sb_grp_hidden', ['sbGrp' => $sbGrp])
                                                <button type="submit" class="w-8 h-8 flex items-center justify-center text-emerald-600 hover:text-emerald-700 hover:bg-emerald-50 rounded-lg transition-colors border-0 bg-transparent p-0" title="Konfirmasi OPSDAL">
                                                    <span class="material-symbols-outlined text-[18px]">check</span>
                                                </button>
                                            </form>
                                            <form action="{{ route('contigency.reject', array_merge(['contigency' => $item->id_contigency], $sbGrp)) }}" method="POST" class="inline m-0">
                                                @csrf
                                                @include('contigency._sb_grp_hidden', ['sbGrp' => $sbGrp])
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
            </div>
            <div class="px-4 py-2.5 bg-slate-50/50 border-t border-slate-100 flex flex-col sm:flex-row items-center justify-between gap-3">
                <p class="text-[10px] text-slate-500 font-medium m-0"><span class="font-bold text-slate-700">{{ $data->firstItem() ?? 0 }}–{{ $data->lastItem() ?? 0 }}</span> dari {{ number_format($data->total(), 0, ',', '.') }}</p>
                <div class="pagination-container">{{ $data->appends(request()->query())->links() }}</div>
            </div>
        @else
            <div class="py-10 text-center">
                <div class="w-12 h-12 bg-slate-100 rounded-full flex items-center justify-center mx-auto mb-3"><span class="material-symbols-outlined text-[26px] text-slate-400">folder_off</span></div>
                <p class="text-label-md text-slate-500 font-medium m-0">
                    {{ $hasFilters ? 'Tidak ada data untuk filter yang dipilih.' : 'Belum ada data contigency.' }}
                </p>
                @if($hasFilters)
                    <a href="{{ route('contigency.index', $sbGrp) }}" class="inline-flex items-center gap-1.5 mt-3 px-3 py-2 bg-slate-100 hover:bg-slate-200 text-slate-600 font-bold rounded-xl text-label-md transition-all text-decoration-none">
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
