@extends('layouts.app')
@section('title', $pageTitle)
@section('page-title', $pageTitle)

@section('content')
@php
    $hasFilters = request()->hasAny(['tanggal_dari','tanggal_sampai','kategori_data','daerah','wilayah','search']);
    $indexResetUrl = ($readOnly ?? false) ? route('layanan.data.index', ['sb_grp' => 'public_service']) : route('layanan.data.index');
@endphp
<div class="max-w-[1400px] mx-auto w-full space-y-4">

{{-- Hero --}}
<section class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
    <div>
        <h2 class="text-[22px] font-extrabold text-on-surface tracking-tight leading-tight m-0">{{ $pageTitle }}</h2>
        <p class="text-[11px] text-slate-400 mt-0.5 m-0">Data layanan dan permohonan informasi</p>
    </div>
    @unless(($readOnly ?? false) || ($sboViewOnly ?? false))
        <a href="{{ route('layanan.data.create') }}" class="flex items-center justify-center gap-2 bg-primary text-on-primary px-4 py-2.5 rounded-xl font-bold text-label-md shadow-lg shadow-primary/25 hover:shadow-xl hover:-translate-y-0.5 transition-all text-decoration-none shrink-0">
            <span class="material-symbols-outlined text-[18px]" style="font-variation-settings: 'FILL' 1;">add</span>
            <span>Tambah Data</span>
        </a>
    @endunless
</section>

{{-- Success flash --}}
@if(session('success'))
    <div class="rounded-xl px-4 py-2.5 flex items-center justify-between gap-3 border border-secondary/20 bg-secondary-container text-on-secondary-container shadow-sm">
        <div class="flex items-center gap-2">
            <span class="material-symbols-outlined text-[18px]" style="font-variation-settings: 'FILL' 1;">check_circle</span>
            <span class="text-label-md font-medium">{{ session('success') }}</span>
        </div>
        <button type="button" class="border-0 bg-transparent text-on-secondary-container/50 hover:text-on-secondary-container p-0" onclick="this.parentElement.remove()">
            <span class="material-symbols-outlined text-[18px]">close</span>
        </button>
    </div>
@endif

<form action="{{ route('layanan.data.index') }}" method="GET" id="filterForm" class="sbo-compact-filters bg-white rounded-lg p-2 border border-slate-200/60 shadow-sm relative z-10">
    @if($readOnly ?? false)
        <input type="hidden" name="sb_grp" value="public_service">
    @endif
    <div class="pmf-row">
        @include('_partials.compact_filters.date_range', ['prefix' => 'layanan-data'])
        @if($readOnly ?? false)
            @include('_partials.compact_filters.territory')
        @endif
        @include('_partials.compact_filters.select', ['name' => 'kategori_data', 'placeholder' => 'Kategori', 'options' => $kategoriList])
        @include('_partials.compact_filters.search', ['placeholder' => 'Cari pemohon, kategori, tujuan...'])
        @include('_partials.compact_filters.actions', [
            'resetUrl' => route('layanan.data.index', ($readOnly ?? false) ? ['sb_grp' => 'public_service'] : []),
            'active' => $hasFilters,
        ])
    </div>
</form>

{{-- Table --}}
<section class="bg-white rounded-xl border border-slate-200/60 overflow-hidden shadow-sm">
    <div class="px-4 py-3 border-b border-slate-100 flex items-center justify-between gap-2">
        <h3 class="text-[11px] font-bold uppercase tracking-wider text-slate-700 m-0 flex items-center gap-1.5">
            <span class="material-symbols-outlined text-[16px] text-primary" style="font-variation-settings: 'FILL' 1;">table_chart</span>
            Layanan Data
            @if(($data->total() ?? 0) > 0)
                <span class="text-[10px] font-semibold text-slate-400 normal-case">{{ number_format($data->total(), 0, ',', '.') }} record</span>
            @endif
        </h3>
        <div class="dropdown">
            <button type="button" class="w-8 h-8 flex items-center justify-center hover:bg-slate-50 border border-slate-100 rounded-lg text-slate-500 transition-colors bg-white" data-bs-toggle="dropdown" title="Export">
                <span class="material-symbols-outlined text-[17px]">download</span>
            </button>
            <ul class="dropdown-menu dropdown-menu-end">
                <li><a class="dropdown-item text-label-md py-2" href="{{ route('layanan.data.export', 'excel') }}?{{ http_build_query(request()->query()) }}">Excel</a></li>
                <li><a class="dropdown-item text-label-md py-2" href="{{ route('layanan.data.export', 'csv') }}?{{ http_build_query(request()->query()) }}">CSV</a></li>
                <li><a class="dropdown-item text-label-md py-2" href="{{ route('layanan.data.export', 'pdf') }}?{{ http_build_query(request()->query()) }}">PDF</a></li>
            </ul>
        </div>
    </div>

    @if($data->count())
        <div class="overflow-x-auto custom-scrollbar">
            <table class="w-full text-left min-w-[820px]">
                <thead>
                    <tr class="bg-slate-50/50">
                        <th class="px-4 py-2 text-[10px] font-bold text-slate-400 uppercase tracking-widest border-b border-slate-100 w-10">#</th>
                        <th class="px-3 py-2 text-[10px] font-bold text-slate-400 uppercase tracking-widest border-b border-slate-100 w-14">Foto</th>
                        <th class="px-3 py-2 text-[10px] font-bold text-slate-400 uppercase tracking-widest border-b border-slate-100">Tanggal</th>
                        <th class="px-3 py-2 text-[10px] font-bold text-slate-400 uppercase tracking-widest border-b border-slate-100">Kategori</th>
                        <th class="px-3 py-2 text-[10px] font-bold text-slate-400 uppercase tracking-widest border-b border-slate-100">Tujuan</th>
                        <th class="px-3 py-2 text-[10px] font-bold text-slate-400 uppercase tracking-widest border-b border-slate-100">Pemohon</th>
                        <th class="px-3 py-2 text-[10px] font-bold text-slate-400 uppercase tracking-widest border-b border-slate-100">Telp</th>
                        @if($readOnly ?? false)
                            <th class="px-3 py-2 text-[10px] font-bold text-slate-400 uppercase tracking-widest border-b border-slate-100">Wilayah</th>
                        @endif
                        <th class="px-3 py-2 text-[10px] font-bold text-slate-400 uppercase tracking-widest border-b border-slate-100 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach($data as $item)
                        @php
                            $showParams = array_filter(array_merge(['id' => $item->id_layanan_data], ($readOnly ?? false) ? ['sb_grp' => 'public_service'] : []));
                            $showUrl = route('layanan.data.show', $showParams);
                            $fotoThumb = \App\Http\Controllers\LayananDataController::fotoPublicUrl($item->foto ?? null);
                            $canMutate = !($readOnly ?? false) && !($sboViewOnly ?? false) && \App\Support\RoleMutationPolicy::canMutateOperationalRow(auth()->user(), $item, $tableName ?? 'layanan_data');
                        @endphp
                        <tr class="hover:bg-slate-50 group">
                            <td class="px-4 py-2.5 text-label-sm text-slate-400">{{ $loop->iteration + ($data->currentPage() - 1) * $data->perPage() }}</td>
                            <td class="px-3 py-2.5">
                                @if($fotoThumb)
                                    <a href="{{ $showUrl }}" class="block shrink-0" title="Lihat foto">
                                        <img src="{{ $fotoThumb }}" alt="Foto layanan data" class="w-11 h-11 rounded-lg object-cover border border-slate-200 group-hover:scale-105 transition-transform" referrerpolicy="no-referrer">
                                    </a>
                                @else
                                    <div class="w-11 h-11 rounded-lg bg-slate-100 flex items-center justify-center text-slate-300" title="Tanpa foto">
                                        <span class="material-symbols-outlined text-[20px]">image_not_supported</span>
                                    </div>
                                @endif
                            </td>
                            <td class="px-3 py-2.5 text-[12px] font-semibold text-slate-700">{{ $item->tanggal ? \Carbon\Carbon::parse($item->tanggal)->format('d M Y') : '-' }}</td>
                            <td class="px-3 py-2.5">
                                @if($item->kategori_data)
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-primary/8 text-primary border border-primary/15">{{ $item->kategori_data }}</span>
                                @else
                                    <span class="text-slate-400">—</span>
                                @endif
                            </td>
                            <td class="px-3 py-2.5">
                                <a href="{{ $showUrl }}"
                                   class="text-[11px] font-semibold text-primary hover:underline text-decoration-none">{{ \Str::limit($item->tujuan ?: 'Detail', 32) }}</a>
                            </td>
                            <td class="px-3 py-2.5 text-[11px] font-semibold text-slate-700">{{ $item->pemohon ?? '—' }}</td>
                            <td class="px-3 py-2.5 text-[11px] text-slate-500">{{ $item->telp_pemohon ?? '—' }}</td>
                            @if($readOnly ?? false)
                                <td class="px-3 py-2.5">
                                    <span class="text-[11px] font-bold" style="color:#D8A808;">{{ strtoupper($item->daerah ?? '-') }}</span>
                                    <span class="block text-[10px] text-slate-400 uppercase">{{ $item->wilayah ? strtoupper(trim(str_ireplace(['KABUPATEN ', 'KOTA ', 'KAB. '], '', $item->wilayah))) : '' }}</span>
                                </td>
                            @endif
                            <td class="px-3 py-2.5 text-right">
                                <div class="flex items-center justify-end gap-1 opacity-100 lg:opacity-0 group-hover:opacity-100 transition-opacity">
                                    <a href="{{ $showUrl }}"
                                       class="w-8 h-8 flex items-center justify-center text-primary hover:bg-primary/10 rounded-lg transition-colors text-decoration-none" title="Detail">
                                        <span class="material-symbols-outlined text-[18px]">visibility</span>
                                    </a>
                                    @if($canMutate)
                                        <a href="{{ route('layanan.data.edit', $item->id_layanan_data) }}"
                                           class="w-8 h-8 flex items-center justify-center text-slate-400 hover:text-slate-700 hover:bg-slate-100 rounded-lg transition-colors text-decoration-none" title="Edit">
                                            <span class="material-symbols-outlined text-[18px]">edit</span>
                                        </a>
                                        <form action="{{ route('layanan.data.destroy', $item->id_layanan_data) }}" method="POST" class="inline m-0"
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
            <p class="text-[10px] text-slate-500 font-medium m-0">
                <span class="font-bold text-slate-700">{{ $data->firstItem() ?? 0 }}–{{ $data->lastItem() ?? 0 }}</span>
                dari {{ number_format($data->total(), 0, ',', '.') }}
            </p>
            <div class="pagination-container">
                {{ $data->appends(request()->query())->links() }}
            </div>
        </div>
    @else
        <div class="py-10 text-center">
            <div class="w-12 h-12 bg-slate-100 rounded-full flex items-center justify-center mx-auto mb-3 text-slate-400">
                <span class="material-symbols-outlined text-[26px]">folder_off</span>
            </div>
            <p class="text-label-md text-slate-500 font-medium m-0">
                {{ $hasFilters ? 'Tidak ada data untuk filter yang dipilih.' : 'Belum ada data layanan.' }}
            </p>
            @if($hasFilters)
                <a href="{{ $indexResetUrl }}" class="inline-flex items-center gap-1.5 mt-3 px-3 py-2 bg-slate-100 hover:bg-slate-200 text-slate-600 font-bold rounded-xl text-label-md transition-all text-decoration-none">
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
