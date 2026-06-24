@extends('layouts.app')
@php $pageTitle = $pageTitle ?? 'Kunjungan Wisata Harian'; @endphp
@section('title', $pageTitle)
@section('page-title', $pageTitle)

@section('content')
@php
    $hasFilters = request()->hasAny(['search', 'daerah', 'wilayah', 'tanggal_dari', 'tanggal_sampai', 'katwisata']);
    $primaryKey = 'id_kunjunganwisata';
@endphp
<div class="max-w-[1400px] mx-auto w-full space-y-4">

<section class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
    <div>
        <h2 class="text-[22px] font-extrabold text-on-surface tracking-tight leading-tight m-0">{{ $pageTitle }}</h2>
        <p class="text-[11px] text-slate-400 mt-0.5 m-0">Data harian pengunjung dan parkir objek wisata</p>
    </div>
    <div class="flex items-center gap-2 shrink-0">
    <a href="{{ route('kunjunganwisata.create') }}" class="flex items-center justify-center gap-2 bg-primary text-on-primary px-4 py-2.5 rounded-xl font-bold text-label-md shadow-lg shadow-primary/25 hover:shadow-xl hover:-translate-y-0.5 transition-all text-decoration-none shrink-0">
        <span class="material-symbols-outlined text-[18px]" style="font-variation-settings: 'FILL' 1;">add</span>
        <span>Tambah Data</span>
    </a>
    @include('_partials.export_dropdown', ['routeName' => 'kunjunganwisata.export', 'variant' => 'hero'])
    </div>
</section>

@if(session('success'))
    <div class="rounded-xl px-4 py-2.5 flex items-center justify-between gap-3 border border-secondary/20 bg-secondary-container text-on-secondary-container shadow-sm">
        <div class="flex items-center gap-2"><span class="material-symbols-outlined text-[18px]" style="font-variation-settings: 'FILL' 1;">check_circle</span><span class="text-label-md font-medium">{{ session('success') }}</span></div>
        <button type="button" class="border-0 bg-transparent p-0" onclick="this.parentElement.remove()"><span class="material-symbols-outlined text-[18px]">close</span></button>
    </div>
@endif

<form action="{{ route('kunjunganwisata.index') }}" method="GET" class="sbo-compact-filters bg-white rounded-lg p-2 border border-slate-200/60 shadow-sm relative z-10">
    <div class="pmf-row">
        @include('_partials.compact_filters.search', ['placeholder' => 'Cari nama wisata, kategori...'])
        @include('_partials.compact_filters.territory')
        @include('_partials.compact_filters.date_range', ['prefix' => 'kunjungan'])
        @include('_partials.compact_filters.select', ['name' => 'katwisata', 'placeholder' => 'Kategori Wisata', 'options' => $kategori ?? [], 'autoSubmit' => false])
        @include('_partials.compact_filters.actions', [
            'resetUrl' => route('kunjunganwisata.index'),
            'active' => $hasFilters,
        ])
    </div>
</form>

<section class="bg-white rounded-xl border border-slate-200/60 overflow-hidden shadow-sm">
    <div class="px-4 py-3 border-b border-slate-100 flex items-center gap-1.5">
        <span class="material-symbols-outlined text-[16px] text-primary" style="font-variation-settings: 'FILL' 1;">camera_outdoor</span>
        <h3 class="text-[11px] font-bold uppercase tracking-wider text-slate-700 m-0">Data Kunjungan Wisata
            @if(($data->total() ?? 0) > 0)<span class="text-[10px] font-semibold text-slate-400 normal-case ml-1">{{ number_format($data->total(), 0, ',', '.') }} record</span>@endif
        </h3>
    </div>
    @if($data->count() > 0)
        <div class="overflow-x-auto custom-scrollbar">
            <table class="w-full text-left min-w-[1100px]">
                <thead><tr class="bg-slate-50/50">
                    <th class="px-4 py-2 text-[10px] font-bold text-slate-400 uppercase tracking-widest border-b border-slate-100 w-10">#</th>
@if(\App\Support\TerritoryIndexFilters::canFilterByDaerah())
                    <th class="px-3 py-2 text-[10px] font-bold text-slate-400 uppercase tracking-widest border-b border-slate-100">Polda</th>
@endif
                    <th class="px-3 py-2 text-[10px] font-bold text-slate-400 uppercase tracking-widest border-b border-slate-100">Polres</th>
                    <th class="px-3 py-2 text-[10px] font-bold text-slate-400 uppercase tracking-widest border-b border-slate-100">Tanggal</th>
                    <th class="px-3 py-2 text-[10px] font-bold text-slate-400 uppercase tracking-widest border-b border-slate-100">Nama Wisata</th>
                    <th class="px-3 py-2 text-[10px] font-bold text-slate-400 uppercase tracking-widest border-b border-slate-100 text-right">Parkir R2</th>
                    <th class="px-3 py-2 text-[10px] font-bold text-slate-400 uppercase tracking-widest border-b border-slate-100 text-right">Parkir R4</th>
                    <th class="px-3 py-2 text-[10px] font-bold text-slate-400 uppercase tracking-widest border-b border-slate-100 text-right">Total KBM</th>
                    <th class="px-3 py-2 text-[10px] font-bold text-slate-400 uppercase tracking-widest border-b border-slate-100 text-right">Domestik</th>
                    <th class="px-3 py-2 text-[10px] font-bold text-slate-400 uppercase tracking-widest border-b border-slate-100 text-right">Mancanegara</th>
                    <th class="px-3 py-2 text-[10px] font-bold text-slate-400 uppercase tracking-widest border-b border-slate-100 text-right">Total</th>
                    <th class="px-3 py-2 text-[10px] font-bold text-slate-400 uppercase tracking-widest border-b border-slate-100">Petugas</th>
                    <th class="px-3 py-2 text-[10px] font-bold text-slate-400 uppercase tracking-widest border-b border-slate-100 text-right">Aksi</th>
                </tr></thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach($data as $r)
                        @php
                            $rowId = $r->{$primaryKey};
                            $canMutate = \App\Support\RoleMutationPolicy::canMutateOperationalRow(auth()->user(), $r, 'kunjunganwisata');
                        @endphp
                        <tr class="hover:bg-slate-50 group">
                            <td class="px-4 py-2.5 text-label-sm text-slate-400">{{ $loop->iteration + ($data->currentPage() - 1) * $data->perPage() }}</td>
                            <td class="px-3 py-2.5">
                                <span class="text-[11px] font-bold" style="color:#D8A808;">{{ strtoupper($r->petugas_daerah ?? '—') }}</span>
                            </td>
                            <td class="px-3 py-2.5">
                                <span class="text-[11px] font-semibold text-slate-600 uppercase">{{ $r->petugas_wilayah ? strtoupper(trim(str_ireplace(['KABUPATEN ', 'KOTA ', 'KAB. '], '', $r->petugas_wilayah))) : '—' }}</span>
                            </td>
                            <td class="px-3 py-2.5 text-[12px] font-semibold text-slate-700">{{ $r->tanggal ? \Carbon\Carbon::parse($r->tanggal)->locale('id')->translatedFormat('d F Y') : '—' }}</td>
                            <td class="px-3 py-2.5">
                                <a href="{{ route('kunjunganwisata.show', $rowId) }}" class="text-[11px] font-semibold text-primary hover:underline text-decoration-none block">{{ \Str::limit($r->namawisata ?: 'Detail', 28) }}</a>
                                @if($r->katwisata)<span class="inline-flex items-center mt-0.5 px-2 py-0.5 rounded-full text-[9px] font-bold bg-primary/8 text-primary border border-primary/15">{{ $r->katwisata }}</span>@endif
                            </td>
                            <td class="px-3 py-2.5 text-[11px] text-slate-600 text-right">{{ number_format($r->parkirr2 ?: 0) }}</td>
                            <td class="px-3 py-2.5 text-[11px] text-slate-600 text-right">{{ number_format($r->parkirr4 ?: 0) }}</td>
                            <td class="px-3 py-2.5 text-[11px] font-semibold text-slate-800 text-right">{{ number_format($r->jmlhkbm ?: 0) }}</td>
                            <td class="px-3 py-2.5 text-[11px] text-slate-600 text-right">{{ number_format($r->domestic ?: 0) }}</td>
                            <td class="px-3 py-2.5 text-[11px] text-slate-600 text-right">{{ number_format($r->mancanegara ?: 0) }}</td>
                            <td class="px-3 py-2.5 text-right"><span class="text-[12px] font-bold text-primary">{{ number_format($r->jmlhpengunjung ?: 0) }}</span></td>
                            <td class="px-3 py-2.5 text-[11px] text-slate-600">{{ $r->petugas_nama ?: '-' }}</td>
                            <td class="px-3 py-2.5 text-right">
                                <div class="flex items-center justify-end gap-1 opacity-100 lg:opacity-0 group-hover:opacity-100 transition-opacity">
                                    <a href="{{ route('kunjunganwisata.show', $rowId) }}" class="w-8 h-8 flex items-center justify-center text-primary hover:bg-primary/10 rounded-lg transition-colors text-decoration-none" title="Detail"><span class="material-symbols-outlined text-[18px]">visibility</span></a>
                                    @if(!empty($r->piket ?? $r->id_usm))
                                        <a href="{{ route('personil.manager.show', $r->piket ?? $r->id_usm) }}" class="w-8 h-8 flex items-center justify-center text-indigo-500 hover:bg-indigo-50 rounded-lg transition-colors text-decoration-none" title="Profil Operator"><span class="material-symbols-outlined text-[18px]">account_circle</span></a>
                                    @endif
                                    @if($canMutate)
                                        <a href="{{ route('kunjunganwisata.edit', $rowId) }}" class="w-8 h-8 flex items-center justify-center text-slate-400 hover:text-slate-700 hover:bg-slate-100 rounded-lg transition-colors text-decoration-none" title="Edit"><span class="material-symbols-outlined text-[18px]">edit</span></a>
                                        <form action="{{ route('kunjunganwisata.destroy', $rowId) }}" method="POST" class="inline m-0" onsubmit="return confirm('Yakin hapus data ini?');">
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
        </div>
        <div class="px-4 py-2.5 bg-slate-50/50 border-t border-slate-100 flex flex-col sm:flex-row items-center justify-between gap-3">
            <p class="text-[10px] text-slate-500 font-medium m-0"><span class="font-bold text-slate-700">{{ $data->firstItem() ?? 0 }}–{{ $data->lastItem() ?? 0 }}</span> dari {{ number_format($data->total(), 0, ',', '.') }}</p>
            <div class="pagination-container">{{ $data->appends(request()->query())->links() }}</div>
        </div>
    @else
        <div class="py-10 text-center">
            <div class="w-12 h-12 bg-slate-100 rounded-full flex items-center justify-center mx-auto mb-3"><span class="material-symbols-outlined text-[26px] text-slate-400">folder_off</span></div>
            <p class="text-label-md text-slate-500 m-0">{{ $hasFilters ? 'Tidak ada data untuk filter yang dipilih.' : 'Belum ada data kunjungan wisata.' }}</p>
            @if($hasFilters)
                <a href="{{ route('kunjunganwisata.index') }}" class="inline-flex items-center gap-1.5 mt-3 px-3 py-2 bg-slate-100 hover:bg-slate-200 text-slate-600 font-bold rounded-xl text-label-md transition-all text-decoration-none">
                    <span class="material-symbols-outlined text-[16px]">cached</span> Reset
                </a>
            @endif
        </div>
    @endif
</section>
</div>
@endsection
