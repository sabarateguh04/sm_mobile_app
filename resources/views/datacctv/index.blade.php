@extends('layouts.app')
@section('title', 'Data CCTV')
@section('page-title', 'Data CCTV')

@section('content')
@php
    $pageTitle = 'Data CCTV';
    $hasFilters = request()->hasAny(['search', 'owner', 'brand', 'fungsicctv', 'tanggal_dari', 'tanggal_sampai', 'daerah', 'wilayah']);
    $writeTable = $writeTable ?? 'datacctv';
    $canCreate = !($sboViewOnly ?? false) && !empty($writeTable) && empty($tableMissing);
@endphp
<div class="max-w-[1400px] mx-auto w-full space-y-4">

<section class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
    <div>
        <h2 class="text-[22px] font-extrabold text-on-surface tracking-tight leading-tight m-0">{{ $pageTitle }}</h2>
        <p class="text-[11px] text-slate-400 mt-0.5 m-0">Inventarisasi kamera CCTV pengamanan lalu lintas</p>
    </div>
    <div class="flex items-center gap-2 shrink-0">
    @if($canCreate)
        <a href="{{ route('datacctv.create') }}" class="flex items-center justify-center gap-2 bg-primary text-on-primary px-4 py-2.5 rounded-xl font-bold text-label-md shadow-lg shadow-primary/25 hover:shadow-xl hover:-translate-y-0.5 transition-all text-decoration-none shrink-0">
            <span class="material-symbols-outlined text-[18px]" style="font-variation-settings: 'FILL' 1;">add</span>
            <span>Tambah Data</span>
        </a>
    @endif
    @include('_partials.export_dropdown', ['routeName' => 'datacctv.export', 'variant' => 'hero'])
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
        View/tabel <code class="text-[11px]">{{ $sourceTable ?? 'wil_datacctv' }}</code> belum tersedia di database. Form tambah data dinonaktifkan sampai tabel inventaris siap.
    </div>
@endif

<form action="{{ route('datacctv.index') }}" method="GET" id="datacctvFilterForm" class="sbo-compact-filters bg-white rounded-lg p-2 border border-slate-200/60 shadow-sm relative z-10">
    <div class="pmf-row">
        @include('_partials.compact_filters.search', ['placeholder' => 'Cari owner, lokasi, IP, brand...'])
        @include('_partials.compact_filters.territory')
        @include('_partials.compact_filters.select', ['name' => 'owner', 'placeholder' => 'Owner', 'options' => $owners ?? []])
        @include('_partials.compact_filters.select', ['name' => 'brand', 'placeholder' => 'Brand', 'options' => $brands ?? []])
        @include('_partials.compact_filters.select', ['name' => 'fungsicctv', 'placeholder' => 'Fungsi CCTV', 'options' => $fungsiList ?? []])
        @include('_partials.compact_filters.date_range', ['prefix' => 'datacctv'])
        @include('_partials.compact_filters.actions', [
            'resetUrl' => route('datacctv.index'),
            'active' => $hasFilters,
        ])
    </div>
</form>

<section class="bg-white rounded-xl border border-slate-200/60 overflow-hidden shadow-sm">
    <div class="px-4 py-3 border-b border-slate-100 flex items-center gap-1.5">
        <span class="material-symbols-outlined text-[16px] text-primary" style="font-variation-settings: 'FILL' 1;">videocam</span>
        <h3 class="text-[11px] font-bold uppercase tracking-wider text-slate-700 m-0">Daftar Data CCTV
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
            <p class="text-label-md text-slate-500 font-medium m-0">Data CCTV belum dapat ditampilkan.</p>
        </div>
    @elseif($data && $data->count() > 0)
        <div class="overflow-x-auto custom-scrollbar">
            <table class="w-full text-left min-w-[1080px]">
                <thead>
                    <tr class="bg-slate-50/50">
                        <th class="px-4 py-2 text-[10px] font-bold text-slate-400 uppercase tracking-widest border-b border-slate-100 w-10">#</th>
@if(\App\Support\TerritoryIndexFilters::canFilterByDaerah())
                        <th class="px-3 py-2 text-[10px] font-bold text-slate-400 uppercase tracking-widest border-b border-slate-100">Polda</th>
@endif
                        <th class="px-3 py-2 text-[10px] font-bold text-slate-400 uppercase tracking-widest border-b border-slate-100">Polres</th>
                        <th class="px-3 py-2 text-[10px] font-bold text-slate-400 uppercase tracking-widest border-b border-slate-100">Tanggal</th>
                        <th class="px-3 py-2 text-[10px] font-bold text-slate-400 uppercase tracking-widest border-b border-slate-100">Lokasi / Owner</th>
                        <th class="px-3 py-2 text-[10px] font-bold text-slate-400 uppercase tracking-widest border-b border-slate-100">IP Address</th>
                        <th class="px-3 py-2 text-[10px] font-bold text-slate-400 uppercase tracking-widest border-b border-slate-100">Brand</th>
                        <th class="px-3 py-2 text-[10px] font-bold text-slate-400 uppercase tracking-widest border-b border-slate-100 text-center">GPS</th>
                        <th class="px-3 py-2 text-[10px] font-bold text-slate-400 uppercase tracking-widest border-b border-slate-100 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach($data as $row)
                        @php
                            $recordId = $row->id_datacctv ?? $row->id_patrolicctv ?? null;
                            $canMutate = $recordId && !($sboViewOnly ?? false) && \App\Support\RoleMutationPolicy::canMutateOperationalRow(auth()->user(), $row, $writeTable);
                        @endphp
                        <tr class="hover:bg-slate-50 group">
                            <td class="px-4 py-2.5 text-label-sm text-slate-400">{{ $loop->iteration + ($data->currentPage() - 1) * $data->perPage() }}</td>
                            <td class="px-3 py-2.5">
                                <span class="text-[11px] font-bold" style="color:#D8A808;">{{ strtoupper($row->usm_daerah ?? '—') }}</span>
                            </td>
                            <td class="px-3 py-2.5">
                                <span class="text-[11px] font-semibold text-slate-600 uppercase">{{ $row->usm_wilayah ? strtoupper(trim(str_ireplace(['KABUPATEN ', 'KOTA ', 'KAB. '], '', $row->usm_wilayah))) : '—' }}</span>
                            </td>
                            <td class="px-3 py-2.5 text-[12px] font-semibold text-slate-700">
                                {{ $row->tanggal ? \Carbon\Carbon::parse($row->tanggal)->locale('id')->translatedFormat('d F Y') : '—' }}
                            </td>
                            <td class="px-3 py-2.5">
                                @if($recordId)
                                    <a href="{{ route('datacctv.show', $recordId) }}" class="text-[11px] font-semibold text-primary hover:underline text-decoration-none block">
                                        {{ \Str::limit($row->lokasi ?: 'Detail CCTV', 28) }}
                                    </a>
                                @else
                                    <span class="text-[11px] text-slate-700">{{ \Str::limit($row->lokasi ?: '—', 28) }}</span>
                                @endif
                                @if($row->owner)
                                    <span class="inline-flex items-center mt-0.5 px-2 py-0.5 rounded-full text-[10px] font-bold bg-slate-100 text-slate-600 border border-slate-200">{{ $row->owner }}</span>
                                @endif
                                @if($row->fungsicctv)
                                    <span class="inline-flex items-center mt-0.5 px-2 py-0.5 rounded-full text-[10px] font-bold bg-primary/8 text-primary border border-primary/15">{{ $row->fungsicctv }}</span>
                                @endif
                            </td>
                            <td class="px-3 py-2.5">
                                <code class="text-[10px] bg-slate-100 px-1.5 py-0.5 rounded text-slate-700">{{ $row->ipaddres ?? '—' }}</code>
                            </td>
                            <td class="px-3 py-2.5 text-[11px] text-slate-600">{{ $row->brand ?? '—' }}</td>
                            <td class="px-3 py-2.5 text-center">
                                @if(!empty($row->koordinat))
                                    <a href="https://www.google.com/maps/search/?api=1&query={{ $row->koordinat }}" target="_blank" rel="noopener noreferrer"
                                       class="inline-flex items-center justify-center w-8 h-8 text-green-600 hover:bg-green-50 rounded-lg transition-colors text-decoration-none mx-auto"
                                       title="{{ $row->koordinat }}">
                                        <span class="material-symbols-outlined text-[18px]">location_on</span>
                                    </a>
                                @else
                                    <span class="text-slate-300">—</span>
                                @endif
                            </td>
                            <td class="px-3 py-2.5 text-right">
                                @if($recordId)
                                    <div class="flex items-center justify-end gap-1 opacity-100 lg:opacity-0 group-hover:opacity-100 transition-opacity">
                                        <a href="{{ route('datacctv.show', $recordId) }}" class="w-8 h-8 flex items-center justify-center text-primary hover:bg-primary/10 rounded-lg transition-colors text-decoration-none" title="Detail">
                                            <span class="material-symbols-outlined text-[18px]">visibility</span>
                                        </a>
                                        @if(!empty($row->piket ?? $row->id_usm))
                                            <a href="{{ route('personil.manager.show', $row->piket ?? $row->id_usm) }}" class="w-8 h-8 flex items-center justify-center text-indigo-500 hover:bg-indigo-50 rounded-lg transition-colors text-decoration-none" title="Profil Operator"><span class="material-symbols-outlined text-[18px]">account_circle</span></a>
                                        @endif
                                        @if($canMutate)
                                            <a href="{{ route('datacctv.edit', $recordId) }}" class="w-8 h-8 flex items-center justify-center text-slate-400 hover:text-slate-700 hover:bg-slate-100 rounded-lg transition-colors text-decoration-none" title="Edit">
                                                <span class="material-symbols-outlined text-[18px]">edit</span>
                                            </a>
                                            <form action="{{ route('datacctv.destroy', $recordId) }}" method="POST" class="inline m-0" onsubmit="return confirm('Yakin hapus data CCTV ini?')">
                                                @csrf @method('DELETE')
                                                <button type="submit" class="w-8 h-8 flex items-center justify-center text-slate-400 hover:text-red-600 hover:bg-red-50 rounded-lg transition-colors border-0 bg-transparent p-0" title="Hapus">
                                                    <span class="material-symbols-outlined text-[18px]">delete</span>
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                @endif
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
                {{ $hasFilters ? 'Tidak ada data untuk filter yang dipilih.' : 'Belum ada data CCTV.' }}
            </p>
            @if($hasFilters)
                <a href="{{ route('datacctv.index') }}" class="inline-flex items-center gap-1.5 mt-3 px-3 py-2 bg-slate-100 hover:bg-slate-200 text-slate-600 font-bold rounded-xl text-label-md transition-all text-decoration-none">
                    <span class="material-symbols-outlined text-[16px]">cached</span> Reset
                </a>
            @elseif($canCreate)
                <a href="{{ route('datacctv.create') }}" class="inline-flex items-center gap-1.5 mt-3 px-3 py-2 bg-primary text-white font-bold rounded-xl text-label-md transition-all text-decoration-none">
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
