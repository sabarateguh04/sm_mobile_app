@extends('layouts.app')
@section('title', $pageTitle ?? 'Objek Wisata')
@section('page-title', $pageTitle ?? 'Objek Wisata')

@section('content')
@php
    $pageTitle = $pageTitle ?? 'Objek Wisata';
    $hasFilters = request()->filled('search');
@endphp
<div class="max-w-[1400px] mx-auto w-full space-y-4">

<section class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
    <div>
        <h2 class="text-[22px] font-extrabold text-on-surface tracking-tight leading-tight m-0">{{ $pageTitle }}</h2>
        <p class="text-[11px] text-slate-400 mt-0.5 m-0">Data inventarisasi objek dan destinasi wisata</p>
    </div>
    <div class="flex items-center gap-2 shrink-0">
    @unless(\App\Helpers\MenuHelper::isExecutiveForLayout(auth()->user()))
        <a href="{{ route('objekwisata.create') }}" class="flex items-center justify-center gap-2 bg-primary text-on-primary px-4 py-2.5 rounded-xl font-bold text-label-md shadow-lg shadow-primary/25 hover:shadow-xl hover:-translate-y-0.5 transition-all text-decoration-none shrink-0">
            <span class="material-symbols-outlined text-[18px]" style="font-variation-settings: 'FILL' 1;">add</span>
            <span>Tambah Data</span>
        </a>
    @endunless
    @include('_partials.export_dropdown', ['routeName' => 'objekwisata.export', 'variant' => 'hero'])
    </div>
</section>

@if(session('success'))
    <div class="rounded-xl px-4 py-2.5 flex items-center justify-between gap-3 border border-secondary/20 bg-secondary-container text-on-secondary-container shadow-sm">
        <div class="flex items-center gap-2"><span class="material-symbols-outlined text-[18px]" style="font-variation-settings: 'FILL' 1;">check_circle</span><span class="text-label-md font-medium">{{ session('success') }}</span></div>
        <button type="button" class="border-0 bg-transparent p-0" onclick="this.parentElement.remove()"><span class="material-symbols-outlined text-[18px]">close</span></button>
    </div>
@endif

@if(($tab ?? '') !== 'dacol_objek_vital')
<form action="{{ route('objekwisata.index') }}" method="GET" class="sbo-compact-filters bg-white rounded-lg p-2 border border-slate-200/60 shadow-sm relative z-10">
    @php $isOp = \App\Helpers\MenuHelper::isOperatorForLayout(auth()->user()); @endphp
    <div class="pmf-row">
        @include('_partials.compact_filters.territory')
        @include('_partials.compact_filters.select', ['name' => 'kategori', 'placeholder' => 'Kategori', 'options' => $kategori ?? []])
        @include('_partials.compact_filters.search', ['placeholder' => 'Cari nama tempat, lokasi...'])
        @include('_partials.compact_filters.actions', [
            'resetUrl' => route('objekwisata.index'),
            'active' => request()->hasAny(['search', 'kategori']),
        ])
    </div>
</form>
@endif

<section class="bg-white rounded-xl border border-slate-200/60 overflow-hidden shadow-sm">
    <div class="px-4 py-3 border-b border-slate-100 flex flex-col sm:flex-row sm:items-center justify-between gap-2">
        <div class="flex items-center gap-1.5">
            <span class="material-symbols-outlined text-[16px] text-primary" style="font-variation-settings: 'FILL' 1;">landscape</span>
            <h3 class="text-[11px] font-bold uppercase tracking-wider text-slate-700 m-0">Daftar Objek Wisata
                @if(($tab ?? 'rinci') === 'rinci' && ($data->total() ?? 0) > 0)<span class="text-[10px] font-semibold text-slate-400 normal-case ml-1">{{ number_format($data->total(), 0, ',', '.') }} record</span>@endif
            </h3>
        </div>
        <div class="flex gap-4 border-l border-slate-200 pl-4">
            <a href="{{ route('objekwisata.index', array_merge(request()->except('tab','page'), ['tab' => 'dacol_objek_vital'])) }}"
               class="text-[11px] font-bold uppercase tracking-wider text-decoration-none {{ ($tab ?? '') === 'dacol_objek_vital' ? 'text-primary' : 'text-slate-400 hover:text-slate-600' }}">Objek Wisata & Vital (Dacol)</a>
        </div>
    </div>
    @if(($tab ?? '') === 'dacol_objek_vital')
        @include('sm.dacol._table')
    @elseif($data->count() > 0)
        <div class="overflow-x-auto custom-scrollbar">
            <table class="w-full text-left min-w-[1020px]">
                <thead><tr class="bg-slate-50/50">
                    <th class="px-4 py-2 text-[10px] font-bold text-slate-400 uppercase tracking-widest border-b border-slate-100 w-10">#</th>
                    @if($isOp)
                        <th class="px-3 py-2 text-[10px] font-bold text-slate-400 uppercase tracking-widest border-b border-slate-100">Kategori</th>
                        <th class="px-3 py-2 text-[10px] font-bold text-slate-400 uppercase tracking-widest border-b border-slate-100">Nama tempat</th>
                        <th class="px-3 py-2 text-[10px] font-bold text-slate-400 uppercase tracking-widest border-b border-slate-100">Lokasi</th>
                        <th class="px-3 py-2 text-[10px] font-bold text-slate-400 uppercase tracking-widest border-b border-slate-100 text-right">Kap pengunjung</th>
                        <th class="px-3 py-2 text-[10px] font-bold text-slate-400 uppercase tracking-widest border-b border-slate-100 text-right">Kap Parkir</th>
                        <th class="px-3 py-2 text-[10px] font-bold text-slate-400 uppercase tracking-widest border-b border-slate-100">Contact</th>
                    @else
                        @if(\App\Support\TerritoryIndexFilters::canFilterByDaerah())
                            <th class="px-3 py-2 text-[10px] font-bold text-slate-400 uppercase tracking-widest border-b border-slate-100">Polda</th>
                        @endif
                        <th class="px-3 py-2 text-[10px] font-bold text-slate-400 uppercase tracking-widest border-b border-slate-100">Polres</th>
                        <th class="px-3 py-2 text-[10px] font-bold text-slate-400 uppercase tracking-widest border-b border-slate-100">Kategori</th>
                        <th class="px-3 py-2 text-[10px] font-bold text-slate-400 uppercase tracking-widest border-b border-slate-100">Nama Object</th>
                        <th class="px-3 py-2 text-[10px] font-bold text-slate-400 uppercase tracking-widest border-b border-slate-100">Lokasi</th>
                        <th class="px-3 py-2 text-[10px] font-bold text-slate-400 uppercase tracking-widest border-b border-slate-100 text-right">Kapasitas</th>
                        <th class="px-3 py-2 text-[10px] font-bold text-slate-400 uppercase tracking-widest border-b border-slate-100 text-right">Kap parkir</th>
                    @endif
                    <th class="px-3 py-2 text-[10px] font-bold text-slate-400 uppercase tracking-widest border-b border-slate-100 text-right">Aksi</th>
                </tr></thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach($data as $r)
                        @php
                            $rowId = data_get($r, 'id_tempatwisata') ?? data_get($r, 'id_objekwisata');
                            $canMutate = !($sboViewOnly ?? false) && \App\Support\RoleMutationPolicy::canMutateOperationalRow(auth()->user(), $r, 'objek_wisata');
                        @endphp
                        <tr class="hover:bg-slate-50 group">
                            <td class="px-4 py-2.5 text-label-sm text-slate-400">{{ $loop->iteration + ($data->currentPage() - 1) * $data->perPage() }}</td>
                            @if($isOp)
                                <td class="px-3 py-2.5">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-primary/8 text-primary border border-primary/15">{{ $r->kattempatwisata ?: '—' }}</span>
                                </td>
                                <td class="px-3 py-2.5">
                                    <a href="{{ route('objekwisata.show', $rowId) }}"
                                       class="text-[11px] font-semibold text-primary hover:underline text-decoration-none block">{{ \Str::limit($r->nametempat ?: 'Detail', 28) }}</a>
                                </td>
                                <td class="px-3 py-2.5 text-[11px] text-slate-600">{{ \Str::limit($r->lokasi ?: '—', 26) }}</td>
                                <td class="px-3 py-2.5 text-[11px] text-slate-600 text-right">{{ number_format($r->kappengunjung ?? 0, 0, ',', '.') }}</td>
                                <td class="px-3 py-2.5 text-[11px] text-slate-600 text-right">{{ number_format($r->kapparkir ?? 0, 0, ',', '.') }}</td>
                                <td class="px-3 py-2.5">
                                    <div class="text-[11px] font-medium text-slate-700">{{ $r->namecp ?: '—' }}</div>
                                    @if($r->cp ?? null)<div class="text-[10px] text-slate-400">{{ $r->cp }}</div>@endif
                                </td>
                            @else
                                @if(\App\Support\TerritoryIndexFilters::canFilterByDaerah())
                                    <td class="px-3 py-2.5">
                                        <span class="text-[11px] font-bold" style="color:#D8A808;">{{ strtoupper($r->petugas_daerah ?? '—') }}</span>
                                    </td>
                                @endif
                                <td class="px-3 py-2.5">
                                    <span class="text-[11px] font-semibold text-slate-600 uppercase">{{ $r->petugas_wilayah ? strtoupper(trim(str_ireplace(['KABUPATEN ', 'KOTA ', 'KAB. '], '', $r->petugas_wilayah))) : '—' }}</span>
                                </td>
                                <td class="px-3 py-2.5">
                                    @if($r->kattempatwisata)<span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-primary/8 text-primary border border-primary/15">{{ $r->kattempatwisata }}</span>@else <span class="text-slate-400">—</span>@endif
                                </td>
                                <td class="px-3 py-2.5">
                                    <a href="{{ route('objekwisata.show', $rowId) }}" class="text-[11px] font-semibold text-primary hover:underline text-decoration-none block">{{ \Str::limit($r->nametempat ?: 'Detail', 28) }}</a>
                                </td>
                                <td class="px-3 py-2.5 text-[11px] text-slate-500">{{ \Str::limit($r->lokasi ?: '-', 26) }}</td>
                                <td class="px-3 py-2.5 text-[11px] text-slate-600 text-right">{{ number_format($r->kappengunjung ?: 0, 0, ',', '.') }}</td>
                                <td class="px-3 py-2.5 text-[11px] text-slate-600 text-right">{{ number_format($r->kapparkir ?: 0, 0, ',', '.') }}</td>
                            @endif
                            <td class="px-3 py-2.5 text-right">
                                <div class="flex items-center justify-end gap-1 opacity-100 lg:opacity-0 group-hover:opacity-100 transition-opacity">
                                    <a href="{{ route('objekwisata.show', $rowId) }}" class="w-8 h-8 flex items-center justify-center text-primary hover:bg-primary/10 rounded-lg transition-colors text-decoration-none" title="Detail">
                                        <span class="material-symbols-outlined text-[18px]">visibility</span>
                                    </a>
                                    @if($canMutate)
                                        <a href="{{ route('objekwisata.edit', $rowId) }}" class="w-8 h-8 flex items-center justify-center text-slate-400 hover:text-slate-700 hover:bg-slate-100 rounded-lg transition-colors text-decoration-none" title="Edit">
                                            <span class="material-symbols-outlined text-[18px]">edit</span>
                                        </a>
                                        <form action="{{ route('objekwisata.destroy', $rowId) }}" method="POST" class="inline m-0" onsubmit="return confirm('Yakin hapus data ini?')">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="w-8 h-8 flex items-center justify-center text-slate-400 hover:text-red-600 hover:bg-red-50 rounded-lg transition-colors border-0 bg-transparent p-0" title="Hapus">
                                                <span class="material-symbols-outlined text-[18px]">delete</span>
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
            <div class="pagination-container">{{ $data->links() }}</div>
        </div>
    @else
        <div class="py-10 text-center">
            <div class="w-12 h-12 bg-slate-100 rounded-full flex items-center justify-center mx-auto mb-3"><span class="material-symbols-outlined text-[26px] text-slate-400">folder_off</span></div>
            <p class="text-label-md text-slate-500 font-medium m-0">
                {{ $hasFilters ? 'Tidak ada data untuk filter yang dipilih.' : 'Belum ada data objek wisata.' }}
            </p>
            @if($hasFilters)
                <a href="{{ route('objekwisata.index') }}" class="inline-flex items-center gap-1.5 mt-3 px-3 py-2 bg-slate-100 hover:bg-slate-200 text-slate-600 font-bold rounded-xl text-label-md transition-all text-decoration-none">
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
