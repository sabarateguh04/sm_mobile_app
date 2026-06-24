@extends('layouts.app')
@section('title', $pageTitle)
@section('page-title', $pageTitle)

@section('content')
@php
    $preserveQuery = array_filter(request()->only(['search','tanggal_dari','tanggal_sampai','daerah','wilayah','kategori_konten']), fn($v) => $v !== '' && $v !== null);
    $hasFilters = request()->hasAny(['tanggal_dari','tanggal_sampai','daerah','wilayah','kategori_konten','search']);
@endphp
<div class="max-w-[1400px] mx-auto w-full space-y-4">

<section class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
    <div>
        <h2 class="text-[22px] font-extrabold text-on-surface tracking-tight leading-tight m-0">{{ $pageTitle }}</h2>
        <p class="text-[11px] text-slate-400 mt-0.5 m-0">Manajemen konten dan publikasi media</p>
    </div>
    @unless($sboViewOnly ?? false)
        <a href="{{ route('media.manage.create') }}" class="flex items-center justify-center gap-2 bg-primary text-on-primary px-4 py-2.5 rounded-xl font-bold text-label-md shadow-lg shadow-primary/25 hover:shadow-xl hover:-translate-y-0.5 transition-all text-decoration-none shrink-0">
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

<form action="{{ route('media.manage.index') }}" method="GET" class="sbo-compact-filters bg-white rounded-lg p-2 border border-slate-200/60 shadow-sm relative z-10">
    <div class="pmf-row">
        @include('_partials.compact_filters.territory')
        @include('_partials.compact_filters.date_range', ['prefix' => 'media-manage'])
        @include('_partials.compact_filters.select', ['name' => 'kategori_konten', 'placeholder' => 'Kategori', 'options' => $kategoriOptions ?? []])
        @include('_partials.compact_filters.search', ['placeholder' => 'Cari tema, kategori...'])
        @include('_partials.compact_filters.actions', [
            'resetUrl' => route('media.manage.index'),
            'active' => $hasFilters,
        ])
    </div>
</form>

<section class="bg-white rounded-xl border border-slate-200/60 overflow-hidden shadow-sm">
    <div class="px-4 py-3 border-b border-slate-100 flex items-center justify-between gap-2">
        <h3 class="text-[11px] font-bold uppercase tracking-wider text-slate-700 m-0 flex items-center gap-1.5">
            <span class="material-symbols-outlined text-[16px] text-primary" style="font-variation-settings: 'FILL' 1;">newspaper</span>
            Media Publishing
            @if(($data->total() ?? 0) > 0)
                <span class="text-[10px] font-semibold text-slate-400 normal-case">{{ number_format($data->total(), 0, ',', '.') }} record</span>
            @endif
        </h3>
        <div class="dropdown">
            <button type="button" class="w-8 h-8 flex items-center justify-center hover:bg-slate-50 border border-slate-100 rounded-lg text-slate-500 transition-colors bg-white" data-bs-toggle="dropdown" title="Export">
                <span class="material-symbols-outlined text-[17px]">download</span>
            </button>
            <ul class="dropdown-menu dropdown-menu-end">
                <li><a class="dropdown-item text-label-md py-2" href="{{ route('media.manage.export', array_merge(['format' => 'excel'], $preserveQuery)) }}">Excel</a></li>
                <li><a class="dropdown-item text-label-md py-2" href="{{ route('media.manage.export', array_merge(['format' => 'csv'], $preserveQuery)) }}">CSV</a></li>
                <li><a class="dropdown-item text-label-md py-2" href="{{ route('media.manage.export', array_merge(['format' => 'pdf'], $preserveQuery)) }}">PDF</a></li>
            </ul>
        </div>
    </div>
    @if($data->count())
        <div class="overflow-x-auto custom-scrollbar">
            <table class="w-full text-left min-w-[900px]">
                <thead><tr class="bg-slate-50/50">
                    <th class="px-4 py-2 text-[10px] font-bold text-slate-400 uppercase tracking-widest border-b border-slate-100 w-10">#</th>
                    <th class="px-3 py-2 text-[10px] font-bold text-slate-400 uppercase tracking-widest border-b border-slate-100">Wilayah</th>
                    <th class="px-3 py-2 text-[10px] font-bold text-slate-400 uppercase tracking-widest border-b border-slate-100">Tanggal</th>
                    <th class="px-3 py-2 text-[10px] font-bold text-slate-400 uppercase tracking-widest border-b border-slate-100">Kategori</th>
                    <th class="px-3 py-2 text-[10px] font-bold text-slate-400 uppercase tracking-widest border-b border-slate-100">Tema</th>
                    <th class="px-3 py-2 text-[10px] font-bold text-slate-400 uppercase tracking-widest border-b border-slate-100">Media</th>
                    <th class="px-3 py-2 text-[10px] font-bold text-slate-400 uppercase tracking-widest border-b border-slate-100">Metode</th>
                    <th class="px-3 py-2 text-[10px] font-bold text-slate-400 uppercase tracking-widest border-b border-slate-100 text-right">Aksi</th>
                </tr></thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach($data as $item)
                        @php
                            $canMutate = !($sboViewOnly ?? false) && \App\Support\RoleMutationPolicy::canMutateOperationalRow(auth()->user(), $item, $tableName ?? 'mediapublishing');
                        @endphp
                        <tr class="hover:bg-slate-50 group">
                            <td class="px-4 py-2.5 text-label-sm text-slate-400">{{ $loop->iteration + ($data->currentPage() - 1) * $data->perPage() }}</td>
                            <td class="px-3 py-2.5">
@if(\App\Support\TerritoryIndexFilters::canFilterByDaerah())
                                <span class="text-[10px] font-bold block" style="color:#D8A808;">{{ strtoupper($item->daerah ?? '—') }}</span>
@endif
                                <span class="text-[9px] text-slate-400 uppercase">{{ $item->wilayah ? strtoupper(trim(str_ireplace(['KABUPATEN ', 'KOTA ', 'KAB. '], '', $item->wilayah))) : '' }}</span>
                            </td>
                            <td class="px-3 py-2.5 text-[12px] font-semibold text-slate-700">{{ $item->tanggal ? \Carbon\Carbon::parse($item->tanggal)->locale('id')->translatedFormat('d F Y') : '—' }}</td>
                            <td class="px-3 py-2.5">
                                @if($item->kategori_konten)
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-primary/8 text-primary border border-primary/15">{{ $item->kategori_konten }}</span>
                                @else
                                    <span class="text-slate-400">—</span>
                                @endif
                            </td>
                            <td class="px-3 py-2.5">
                                <a href="{{ route('media.manage.show', $item->id_mediapublishing) }}"
                                   class="text-[11px] font-semibold text-primary hover:underline text-decoration-none">{{ \Str::limit($item->tema ?: 'Detail', 32) }}</a>
                            </td>
                            <td class="px-3 py-2.5 text-[11px] text-slate-600">{{ $item->media_publishing ?: '—' }}</td>
                            <td class="px-3 py-2.5 text-[11px] text-slate-600">{{ $item->metode ?: '—' }}</td>
                            <td class="px-3 py-2.5 text-right">
                                <div class="flex items-center justify-end gap-1 opacity-100 lg:opacity-0 group-hover:opacity-100 transition-opacity">
                                    <a href="{{ route('media.manage.show', $item->id_mediapublishing) }}"
                                       class="w-8 h-8 flex items-center justify-center text-primary hover:bg-primary/10 rounded-lg transition-colors text-decoration-none" title="Detail">
                                        <span class="material-symbols-outlined text-[18px]">visibility</span>
                                    </a>
                                    @if($canMutate)
                                        <a href="{{ route('media.manage.edit', $item->id_mediapublishing) }}"
                                           class="w-8 h-8 flex items-center justify-center text-slate-400 hover:text-slate-700 hover:bg-slate-100 rounded-lg transition-colors text-decoration-none" title="Edit">
                                            <span class="material-symbols-outlined text-[18px]">edit</span>
                                        </a>
                                        <form action="{{ route('media.manage.destroy', $item->id_mediapublishing) }}" method="POST" class="inline m-0"
                                              onsubmit="return confirm('Yakin hapus data ini?')">
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
            <div class="pagination-container">{{ $data->appends($preserveQuery)->links() }}</div>
        </div>
    @else
        <div class="py-10 text-center">
            <div class="w-12 h-12 bg-slate-100 rounded-full flex items-center justify-center mx-auto mb-3"><span class="material-symbols-outlined text-[26px] text-slate-400">folder_off</span></div>
            <p class="text-label-md text-slate-500 font-medium m-0">
                {{ $hasFilters ? 'Tidak ada data untuk filter yang dipilih.' : 'Belum ada data media publishing.' }}
            </p>
            @if($hasFilters)
                <a href="{{ route('media.manage.index') }}" class="inline-flex items-center gap-1.5 mt-3 px-3 py-2 bg-slate-100 hover:bg-slate-200 text-slate-600 font-bold rounded-xl text-label-md transition-all text-decoration-none">
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
