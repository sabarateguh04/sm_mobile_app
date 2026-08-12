@extends('layouts.app')
@section('title', 'Data Perangkat')
@section('page-title', 'Data Perangkat')

@section('content')
@php
    $pageTitle = 'Data Perangkat';
    $hasFilters = request()->hasAny(['search', 'katperangkat', 'owner', 'tanggal_dari', 'tanggal_sampai', 'daerah', 'wilayah']);
    $writeTable = $writeTable ?? 'dataperangkat';
    $canCreate = !($sboViewOnly ?? false) && !empty($writeTable) && empty($tableMissing);
@endphp
<div class="max-w-[1400px] mx-auto w-full space-y-4">

<section class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
    <div>
        <h2 class="text-[22px] font-extrabold text-on-surface tracking-tight leading-tight m-0">{{ $pageTitle }}</h2>
        <p class="text-[11px] text-slate-400 mt-0.5 m-0">Inventarisasi perangkat dan teknologi pendukung operasi</p>
    </div>
    <div class="flex items-center gap-2 shrink-0">
    @if($canCreate)
        <a href="{{ route('dataperangkat.create') }}" class="flex items-center justify-center gap-2 bg-primary text-on-primary px-4 py-2.5 rounded-xl font-bold text-label-md shadow-lg shadow-primary/25 hover:shadow-xl hover:-translate-y-0.5 transition-all text-decoration-none shrink-0">
            <span class="material-symbols-outlined text-[18px]" style="font-variation-settings: 'FILL' 1;">add</span>
            <span>Tambah Data</span>
        </a>
    @endif
    @include('_partials.export_dropdown', ['routeName' => 'dataperangkat.export', 'variant' => 'hero'])
    </div>
</section>

@if(session('success'))
    <div class="rounded-xl px-4 py-2.5 flex items-center justify-between gap-3 border border-secondary/20 bg-secondary-container text-on-secondary-container shadow-sm">
        <div class="flex items-center gap-2">
            <span class="material-symbols-outlined text-[18px]" style="font-variation-settings: 'FILL' 1;">check_circle</span>
            <span class="text-label-md font-medium">{{ session('success') }}</span>
        </div>
        <button type="button" class="border-0 bg-transparent p-0" onclick="this.parentElement.remove()">
            <span class="material-symbols-outlined text-[18px]">close</span>
        </button>
    </div>
@endif

@if(!empty($tableMissing))
    <div class="rounded-xl bg-amber-50 border border-amber-200 px-4 py-3 text-amber-800 text-[12px]">
        View/tabel <code class="text-[11px]">{{ $sourceTable ?? 'dataperangkat' }}</code> belum tersedia di database. Form tambah data dinonaktifkan sampai tabel inventaris siap.
    </div>
@endif

<form action="{{ route('dataperangkat.index') }}" method="GET" id="dataperangkatFilterForm" class="sbo-compact-filters bg-white rounded-lg p-2 border border-slate-200/60 shadow-sm relative z-10">
    @php $isOp = \App\Helpers\MenuHelper::isOperatorForLayout(auth()->user()); @endphp
    <div class="pmf-row">
        @include('_partials.compact_filters.territory')
        @include('_partials.compact_filters.select', ['name' => 'katperangkat', 'placeholder' => 'Kategori', 'options' => $kategori ?? []])
        @include('_partials.compact_filters.select', ['name' => 'owner', 'placeholder' => 'Select a value', 'options' => $owners ?? []])
        @include('_partials.compact_filters.search', ['placeholder' => 'Nama Perangkat...'])
        @include('_partials.compact_filters.actions', [
            'resetUrl' => route('dataperangkat.index'),
            'active' => $hasFilters,
        ])
    </div>
</form>

<section class="bg-white rounded-xl border border-slate-200/60 overflow-hidden shadow-sm">
    <div class="px-4 py-3 border-b border-slate-100 flex items-center gap-1.5">
        <span class="material-symbols-outlined text-[16px] text-primary" style="font-variation-settings: 'FILL' 1;">memory</span>
        <h3 class="text-[11px] font-bold uppercase tracking-wider text-slate-700 m-0">Daftar Data Perangkat
            @if($data && $data->total() > 0)
                <span class="text-[10px] font-semibold text-slate-400 normal-case ml-1">{{ number_format($data->total(), 0, ',', '.') }} record</span>
            @endif
        </h3>
    </div>

    @if(!empty($tableMissing))
        <div class="py-10 text-center px-4">
            <div class="w-12 h-12 bg-slate-100 rounded-full flex items-center justify-center mx-auto mb-3">
                <span class="material-symbols-outlined text-[26px] text-slate-400">database_off</span>
            </div>
            <p class="text-label-md text-slate-500 font-medium m-0">Data perangkat belum dapat ditampilkan.</p>
        </div>
    @elseif($data && $data->count() > 0)
        <div class="overflow-x-auto custom-scrollbar">
            <table class="w-full text-left min-w-[1060px]">
                <thead>
                    <tr class="bg-slate-50/50">
                        @php $isOp = \App\Helpers\MenuHelper::isOperatorForLayout(auth()->user()); @endphp
                        <th class="px-4 py-2 text-[10px] font-bold text-slate-400 uppercase tracking-widest border-b border-slate-100 w-10">#</th>
                        @if($isOp)
                            <th class="px-3 py-2 text-[10px] font-bold text-slate-400 uppercase tracking-widest border-b border-slate-100">Name perangkat</th>
                            <th class="px-3 py-2 text-[10px] font-bold text-slate-400 uppercase tracking-widest border-b border-slate-100">Lokasi</th>
                            <th class="px-3 py-2 text-[10px] font-bold text-slate-400 uppercase tracking-widest border-b border-slate-100 text-center">Qtty</th>
                            <th class="px-3 py-2 text-[10px] font-bold text-slate-400 uppercase tracking-widest border-b border-slate-100">Owner</th>
                            <th class="px-3 py-2 text-[10px] font-bold text-slate-400 uppercase tracking-widest border-b border-slate-100">Fungsi</th>
                        @else
@if(\App\Support\TerritoryIndexFilters::canFilterByDaerah())
                        <th class="px-3 py-2 text-[10px] font-bold text-slate-400 uppercase tracking-widest border-b border-slate-100">Polda</th>
@endif
                        <th class="px-3 py-2 text-[10px] font-bold text-slate-400 uppercase tracking-widest border-b border-slate-100">Polres</th>
                        <th class="px-3 py-2 text-[10px] font-bold text-slate-400 uppercase tracking-widest border-b border-slate-100">Kategori</th>
                        <th class="px-3 py-2 text-[10px] font-bold text-slate-400 uppercase tracking-widest border-b border-slate-100">Name perangkat</th>
                        <th class="px-3 py-2 text-[10px] font-bold text-slate-400 uppercase tracking-widest border-b border-slate-100">Lokasi</th>
                        <th class="px-3 py-2 text-[10px] font-bold text-slate-400 uppercase tracking-widest border-b border-slate-100 text-center">Qtty</th>
                        <th class="px-3 py-2 text-[10px] font-bold text-slate-400 uppercase tracking-widest border-b border-slate-100">Owner</th>
                        <th class="px-3 py-2 text-[10px] font-bold text-slate-400 uppercase tracking-widest border-b border-slate-100">Fungsi</th>
                        @endif
                        <th class="px-3 py-2 text-[10px] font-bold text-slate-400 uppercase tracking-widest border-b border-slate-100 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach($data as $row)
                        @php
                            $canMutate = !($sboViewOnly ?? false) && \App\Support\RoleMutationPolicy::canMutateDataperangkatRow(auth()->user(), $row);
                        @endphp
                        <tr class="hover:bg-slate-50 group">
                            <td class="px-4 py-2.5 text-label-sm text-slate-400">{{ $loop->iteration + ($data->currentPage() - 1) * $data->perPage() }}</td>
                            @if($isOp)
                                <td class="px-3 py-2.5">
                                    <a href="{{ route('dataperangkat.show', $row->id_perangkat) }}" class="text-[11px] font-semibold text-primary hover:underline text-decoration-none block">
                                        {{ \Str::limit($row->nameperangkat ?: 'Detail', 28) }}
                                    </a>
                                </td>
                                <td class="px-3 py-2.5 text-[11px] text-slate-600">{{ \Str::limit($row->lokasi ?? '—', 26) }}</td>
                                <td class="px-3 py-2.5 text-center text-[11px] font-semibold text-slate-700">{{ $row->qtty ?? '—' }}</td>
                                <td class="px-3 py-2.5 text-[11px] text-slate-600">{{ $row->owner ?? '—' }}</td>
                                <td class="px-3 py-2.5 text-[11px] text-slate-600">{{ \Str::limit($row->supportfor ?? '—', 32) }}</td>
                            @else
@if(\App\Support\TerritoryIndexFilters::canFilterByDaerah())
                            <td class="px-3 py-2.5">
                                <span class="text-[11px] font-bold" style="color:#D8A808;">{{ strtoupper($row->usm_daerah ?? '—') }}</span>
                            </td>
@endif
                            <td class="px-3 py-2.5">
                                <span class="text-[11px] font-semibold text-slate-600 uppercase">{{ $row->usm_wilayah ? strtoupper(trim(str_ireplace(['KABUPATEN ', 'KOTA ', 'KAB. '], '', $row->usm_wilayah))) : '—' }}</span>
                            </td>
                            <td class="px-3 py-2.5">
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-primary/8 text-primary border border-primary/15">{{ $row->katperangkat ?? '—' }}</span>
                            </td>
                            <td class="px-3 py-2.5">
                                <a href="{{ route('dataperangkat.show', $row->id_perangkat) }}" class="text-[11px] font-semibold text-primary hover:underline text-decoration-none block">
                                    {{ \Str::limit($row->nameperangkat ?: 'Detail', 28) }}
                                </a>
                            </td>
                            <td class="px-3 py-2.5 text-[11px] text-slate-600">{{ \Str::limit($row->lokasi ?? '—', 26) }}</td>
                            <td class="px-3 py-2.5 text-center text-[11px] font-semibold text-slate-700">{{ $row->qtty ?? '—' }}</td>
                            <td class="px-3 py-2.5 text-[11px] text-slate-600">{{ $row->owner ?? '—' }}</td>
                            <td class="px-3 py-2.5 text-[11px] text-slate-600">{{ \Str::limit($row->supportfor ?? '—', 32) }}</td>
                            @endif
                            <td class="px-3 py-2.5 text-right">
                                <div class="flex items-center justify-end gap-1 opacity-100 lg:opacity-0 group-hover:opacity-100 transition-opacity">
                                    <a href="{{ route('dataperangkat.show', $row->id_perangkat) }}" class="w-8 h-8 flex items-center justify-center text-primary hover:bg-primary/10 rounded-lg transition-colors text-decoration-none" title="Detail">
                                        <span class="material-symbols-outlined text-[18px]">visibility</span>
                                    </a>
                                    @if($canMutate)
                                        <a href="{{ route('dataperangkat.edit', $row->id_perangkat) }}" class="w-8 h-8 flex items-center justify-center text-slate-400 hover:text-slate-700 hover:bg-slate-100 rounded-lg transition-colors text-decoration-none" title="Edit">
                                            <span class="material-symbols-outlined text-[18px]">edit</span>
                                        </a>
                                        <form action="{{ route('dataperangkat.destroy', $row->id_perangkat) }}" method="POST" class="inline m-0" onsubmit="return confirm('Yakin hapus data perangkat ini?')">
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
            <p class="text-[10px] text-slate-500 font-medium m-0">
                <span class="font-bold text-slate-700">{{ $data->firstItem() ?? 0 }}–{{ $data->lastItem() ?? 0 }}</span>
                dari {{ number_format($data->total(), 0, ',', '.') }}
            </p>
            <div class="pagination-container">{{ $data->links() }}</div>
        </div>
    @else
        <div class="py-10 text-center">
            <div class="w-12 h-12 bg-slate-100 rounded-full flex items-center justify-center mx-auto mb-3">
                <span class="material-symbols-outlined text-[26px] text-slate-400">folder_off</span>
            </div>
            <p class="text-label-md text-slate-500 font-medium m-0">
                {{ $hasFilters ? 'Tidak ada data untuk filter yang dipilih.' : 'Belum ada data perangkat.' }}
            </p>
            @if($hasFilters)
                <a href="{{ route('dataperangkat.index') }}" class="inline-flex items-center gap-1.5 mt-3 px-3 py-2 bg-slate-100 hover:bg-slate-200 text-slate-600 font-bold rounded-xl text-label-md transition-all text-decoration-none">
                    <span class="material-symbols-outlined text-[16px]">cached</span> Reset
                </a>
            @elseif($canCreate)
                <a href="{{ route('dataperangkat.create') }}" class="inline-flex items-center gap-1.5 mt-3 px-3 py-2 bg-primary text-white font-bold rounded-xl text-label-md transition-all text-decoration-none">
                    <span class="material-symbols-outlined text-[16px]">add</span> Tambah sekarang
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
