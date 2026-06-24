@extends('layouts.app')
@section('title', $pageTitle)
@section('page-title', $pageTitle)

@section('content')
@php
    $tab = $tab ?? 'rinci';
    $hasFilters = request()->hasAny(['search', 'daerah', 'wilayah', 'tanggal_dari', 'tanggal_sampai', 'jenisjalan', 'lintasan', 'prasjal']);
    $primaryKey = 'id_datajalan';
@endphp
<div class="max-w-[1400px] mx-auto w-full space-y-4">

<section class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
    <div>
        <h2 class="text-[22px] font-extrabold text-on-surface tracking-tight leading-tight m-0">{{ $pageTitle }}</h2>
        <p class="text-[11px] text-slate-400 mt-0.5 m-0">Inventarisasi ruas jalan dan prasarana lalu lintas</p>
    </div>
    @unless(\App\Helpers\MenuHelper::isExecutiveForLayout(auth()->user()))
    <a href="{{ route('t_datajalan.create') }}" class="flex items-center justify-center gap-2 bg-primary text-on-primary px-4 py-2.5 rounded-xl font-bold text-label-md shadow-lg shadow-primary/25 hover:shadow-xl hover:-translate-y-0.5 transition-all text-decoration-none shrink-0">
        <span class="material-symbols-outlined text-[18px]" style="font-variation-settings: 'FILL' 1;">add_road</span>
        <span>Tambah Data Jalan</span>
    </a>
    @endunless
</section>

@if(session('success'))
    <div class="rounded-xl px-4 py-2.5 flex items-center justify-between gap-3 border border-secondary/20 bg-secondary-container text-on-secondary-container shadow-sm">
        <div class="flex items-center gap-2"><span class="material-symbols-outlined text-[18px]" style="font-variation-settings: 'FILL' 1;">check_circle</span><span class="text-label-md font-medium">{{ session('success') }}</span></div>
        <button type="button" class="border-0 bg-transparent p-0" onclick="this.parentElement.remove()"><span class="material-symbols-outlined text-[18px]">close</span></button>
    </div>
@endif

<form action="{{ route('t_datajalan.index') }}" method="GET" class="sbo-compact-filters bg-white rounded-lg p-2 border border-slate-200/60 shadow-sm relative z-10">
    <input type="hidden" name="tab" value="{{ $tab }}">
    <div class="pmf-row">
        @include('_partials.compact_filters.search', ['placeholder' => 'Cari nama / jenis jalan...'])
        @include('_partials.compact_filters.territory')
        @include('_partials.compact_filters.date_range', ['prefix' => 'datajalan'])
        @include('_partials.compact_filters.select', ['name' => 'jenisjalan', 'placeholder' => 'Jenis Jalan', 'options' => $jenisJalanList ?? [], 'autoSubmit' => false])
        @include('_partials.compact_filters.actions', [
            'resetUrl' => route('t_datajalan.index'),
            'active' => $hasFilters,
        ])
    </div>
</form>

<section class="bg-white rounded-xl border border-slate-200/60 overflow-hidden shadow-sm">
    <div class="px-4 py-3 border-b border-slate-100 flex flex-col sm:flex-row sm:items-center justify-between gap-2">
        <div class="flex items-center gap-4">
            <h3 class="text-[11px] font-bold uppercase tracking-wider text-slate-700 m-0 flex items-center gap-1.5">
                <span class="material-symbols-outlined text-[16px] text-primary" style="font-variation-settings: 'FILL' 1;">add_road</span>
                @if($tab === 'tabulasi')
                    Tabulasi Data Jalan
                @else
                    Daftar Data Jalan
                    @if($tab === 'rinci' && ($data->total() ?? 0) > 0)
                        <span class="text-[10px] font-semibold text-slate-400 normal-case">{{ number_format($data->total(), 0, ',', '.') }} record</span>
                    @endif
                @endif
            </h3>
            <div class="flex gap-4 border-l border-slate-200 pl-4">
                <a href="{{ route('t_datajalan.index', array_merge(request()->except('tab','page'), ['tab' => 'rinci'])) }}"
                   class="text-[11px] font-bold uppercase tracking-wider text-decoration-none {{ $tab === 'rinci' ? 'text-primary' : 'text-slate-400 hover:text-slate-600' }}">Rinci</a>
                <a href="{{ route('t_datajalan.index', array_merge(request()->except('tab','page'), ['tab' => 'tabulasi'])) }}"
                   class="text-[11px] font-bold uppercase tracking-wider text-decoration-none {{ $tab === 'tabulasi' ? 'text-primary' : 'text-slate-400 hover:text-slate-600' }}">Tabulasi</a>
            </div>
        </div>
        @if($tab === 'rinci')
            <div class="dropdown">
                <button type="button" class="w-8 h-8 flex items-center justify-center hover:bg-slate-50 border border-slate-100 rounded-lg text-slate-500 transition-colors bg-white" data-bs-toggle="dropdown" title="Export">
                    <span class="material-symbols-outlined text-[17px]">download</span>
                </button>
                <ul class="dropdown-menu dropdown-menu-end">
                    <li><a class="dropdown-item text-label-md py-2" href="{{ route('t_datajalan.export', 'excel') }}?{{ http_build_query(request()->except('page')) }}">Excel</a></li>
                    <li><a class="dropdown-item text-label-md py-2" href="{{ route('t_datajalan.export', 'csv') }}?{{ http_build_query(request()->except('page')) }}">CSV</a></li>
                    <li><a class="dropdown-item text-label-md py-2" href="{{ route('t_datajalan.export', 'pdf') }}?{{ http_build_query(request()->except('page')) }}">PDF</a></li>
                </ul>
            </div>
        @endif
    </div>

    @if($tab === 'tabulasi')
        @if(($tabulasi ?? collect())->count() > 0)
            <div class="overflow-x-auto custom-scrollbar">
                <table class="w-full text-left min-w-[700px]">
                    <thead><tr class="bg-slate-50/50">
                        <th class="px-4 py-2 text-[10px] font-bold text-slate-400 uppercase tracking-widest border-b border-slate-100 w-10">#</th>
                        <th class="px-3 py-2 text-[10px] font-bold text-slate-400 uppercase tracking-widest border-b border-slate-100">Daerah</th>
                        <th class="px-3 py-2 text-[10px] font-bold text-slate-400 uppercase tracking-widest border-b border-slate-100">Jenis Jalan</th>
                        <th class="px-3 py-2 text-[10px] font-bold text-slate-400 uppercase tracking-widest border-b border-slate-100 text-right">Jumlah Ruas</th>
                        <th class="px-3 py-2 text-[10px] font-bold text-slate-400 uppercase tracking-widest border-b border-slate-100 text-right">Total Panjang (km)</th>
                    </tr></thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach($tabulasi as $row)
                            <tr class="hover:bg-slate-50">
                                <td class="px-4 py-2.5 text-label-sm text-slate-400">{{ $loop->iteration }}</td>
                                <td class="px-3 py-2.5 text-[11px] font-bold" style="color:#D8A808;">{{ strtoupper($row->daerah_nama ?? '—') }}</td>
                                <td class="px-3 py-2.5 text-[12px] text-slate-700">{{ $row->jenis_jalan ?? '—' }}</td>
                                <td class="px-3 py-2.5 text-[12px] font-bold text-slate-800 text-right">{{ number_format($row->jumlah, 0, ',', '.') }}</td>
                                <td class="px-3 py-2.5 text-[12px] text-slate-600 text-right">{{ number_format($row->total_panjang ?? 0, 2, ',', '.') }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div class="py-10 text-center">
                <div class="w-12 h-12 bg-slate-100 rounded-full flex items-center justify-center mx-auto mb-3"><span class="material-symbols-outlined text-[26px] text-slate-400">folder_off</span></div>
                <p class="text-label-md text-slate-500 m-0">{{ $hasFilters ? 'Tidak ada data tabulasi untuk filter yang dipilih.' : 'Belum ada data tabulasi.' }}</p>
            </div>
        @endif
    @elseif($data->count() > 0)
        <div class="overflow-x-auto custom-scrollbar">
            <table class="w-full text-left min-w-[1000px]">
                <thead><tr class="bg-slate-50/50">
                    <th class="px-4 py-2 text-[10px] font-bold text-slate-400 uppercase tracking-widest border-b border-slate-100 w-10">#</th>
@if(\App\Support\TerritoryIndexFilters::canFilterByDaerah())
                    <th class="px-3 py-2 text-[10px] font-bold text-slate-400 uppercase tracking-widest border-b border-slate-100">Polda</th>
@endif
                    <th class="px-3 py-2 text-[10px] font-bold text-slate-400 uppercase tracking-widest border-b border-slate-100">Polres</th>
                    <th class="px-3 py-2 text-[10px] font-bold text-slate-400 uppercase tracking-widest border-b border-slate-100">Tanggal</th>
                    <th class="px-3 py-2 text-[10px] font-bold text-slate-400 uppercase tracking-widest border-b border-slate-100">Nama Jalan</th>
                    <th class="px-3 py-2 text-[10px] font-bold text-slate-400 uppercase tracking-widest border-b border-slate-100">Jenis / Lintasan</th>
                    <th class="px-3 py-2 text-[10px] font-bold text-slate-400 uppercase tracking-widest border-b border-slate-100 text-center">Panjang</th>
                    <th class="px-3 py-2 text-[10px] font-bold text-slate-400 uppercase tracking-widest border-b border-slate-100">Koordinat</th>
                    <th class="px-3 py-2 text-[10px] font-bold text-slate-400 uppercase tracking-widest border-b border-slate-100 text-right">Aksi</th>
                </tr></thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach($data as $item)
                        @php
                            $rowId = $item->{$primaryKey};
                            $canMutate = \App\Support\RoleMutationPolicy::canMutateOperationalRow(auth()->user(), $item, 't_datajalan');
                        @endphp
                        <tr class="hover:bg-slate-50 group">
                            <td class="px-4 py-2.5 text-label-sm text-slate-400">{{ $loop->iteration + ($data->currentPage() - 1) * $data->perPage() }}</td>
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
                                <a href="{{ route('t_datajalan.show', $rowId) }}" class="text-[11px] font-semibold text-primary hover:underline text-decoration-none block">{{ \Str::limit($item->namajalan ?: 'Detail', 32) }}</a>
                            </td>
                            <td class="px-3 py-2.5">
                                @if($item->jenisjalan)
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-primary/8 text-primary border border-primary/15">{{ $item->jenisjalan }}</span>
                                @endif
                                @if($item->lintasan)
                                    <span class="text-[10px] text-slate-500 block mt-0.5">{{ $item->lintasan }}</span>
                                @endif
                            </td>
                            <td class="px-3 py-2.5 text-center">
                                <span class="inline-flex min-w-[2rem] justify-center px-1.5 py-0.5 rounded-md text-[11px] font-bold bg-slate-100 text-slate-700">{{ number_format($item->panjangjalan ?? 0, 0, ',', '.') }} km</span>
                            </td>
                            <td class="px-3 py-2.5">
                                @if($item->koordinat)
                                    <a href="https://www.google.com/maps/search/?api=1&query={{ $item->koordinat }}" target="_blank" rel="noopener" class="flex items-center gap-1 text-[10px] font-mono text-green-600 hover:underline text-decoration-none">
                                        <span class="material-symbols-outlined text-[14px]">location_on</span>{{ \Str::limit($item->koordinat, 18) }}
                                    </a>
                                @else
                                    <span class="text-slate-400">—</span>
                                @endif
                            </td>
                            <td class="px-3 py-2.5 text-right">
                                <div class="flex items-center justify-end gap-1 opacity-100 lg:opacity-0 group-hover:opacity-100 transition-opacity">
                                    <a href="{{ route('t_datajalan.show', $rowId) }}" class="w-8 h-8 flex items-center justify-center text-primary hover:bg-primary/10 rounded-lg transition-colors text-decoration-none" title="Detail">
                                        <span class="material-symbols-outlined text-[18px]">visibility</span>
                                    </a>
                                    @if(!empty($item->piket ?? $item->id_usm))
                                        <a href="{{ route('personil.manager.show', $item->piket ?? $item->id_usm) }}" class="w-8 h-8 flex items-center justify-center text-indigo-500 hover:bg-indigo-50 rounded-lg transition-colors text-decoration-none" title="Profil Operator"><span class="material-symbols-outlined text-[18px]">account_circle</span></a>
                                    @endif
                                    @if($canMutate)
                                        <a href="{{ route('t_datajalan.edit', $rowId) }}" class="w-8 h-8 flex items-center justify-center text-slate-400 hover:text-slate-700 hover:bg-slate-100 rounded-lg transition-colors text-decoration-none" title="Edit">
                                            <span class="material-symbols-outlined text-[18px]">edit</span>
                                        </a>
                                        <form action="{{ route('t_datajalan.destroy', $rowId) }}" method="POST" class="inline m-0" onsubmit="return confirm('Yakin hapus data jalan ini?');">
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
            <div class="pagination-container">{{ $data->appends(request()->query())->links() }}</div>
        </div>
    @else
        <div class="py-10 text-center">
            <div class="w-12 h-12 bg-slate-100 rounded-full flex items-center justify-center mx-auto mb-3"><span class="material-symbols-outlined text-[26px] text-slate-400">folder_off</span></div>
            <p class="text-label-md text-slate-500 m-0">{{ $hasFilters ? 'Tidak ada data untuk filter yang dipilih.' : 'Belum ada data jalan.' }}</p>
            @if($hasFilters)
                <a href="{{ route('t_datajalan.index') }}" class="inline-flex items-center gap-1.5 mt-3 px-3 py-2 bg-slate-100 hover:bg-slate-200 text-slate-600 font-bold rounded-xl text-label-md transition-all text-decoration-none">
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
    .pagination-container .page-item.active .page-link { background-color: var(--color-primary); border-color: var(--color-primary); color: #fff; }
    .pagination-container .page-item.disabled .page-link { opacity: 0.5; background: #f8fafc; }
</style>
@endpush
