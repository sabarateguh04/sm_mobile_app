@extends('layouts.app')
@section('title', $pageTitle ?? 'Report Traffic Dalkot')
@section('page-title', $pageTitle ?? 'Report Traffic Dalkot')

@section('content')
<div class="max-w-[1400px] mx-auto w-full space-y-4">

<section class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
    <div>
        <h2 class="text-[22px] font-extrabold text-on-surface tracking-tight leading-tight m-0">{{ $pageTitle ?? 'Report Traffic Dalkot' }}</h2>
        <p class="text-[11px] text-slate-400 mt-0.5 m-0">Data volume lalu lintas dalkot</p>
    </div>
    <div class="flex items-center gap-2 shrink-0">
        <a href="{{ route('traffics.create') }}" class="flex items-center justify-center gap-2 bg-primary text-on-primary px-4 py-2.5 rounded-xl font-bold text-label-md shadow-lg shadow-primary/25 hover:shadow-xl hover:-translate-y-0.5 transition-all text-decoration-none">
            <span class="material-symbols-outlined text-[18px]" style="font-variation-settings: 'FILL' 1;">add</span>
            <span>Tambah Data</span>
        </a>
        <div class="dropdown">
            <button type="button" class="flex items-center gap-1.5 px-3 py-2.5 bg-white border border-slate-200 hover:bg-slate-50 text-slate-600 font-bold rounded-xl text-label-md h-[42px]" data-bs-toggle="dropdown">
                <span class="material-symbols-outlined text-[18px]">download</span><span>Export</span>
            </button>
            <ul class="dropdown-menu dropdown-menu-end">
                <li><a class="dropdown-item text-label-md py-2" href="{{ route('traffics.export', 'excel') }}?{{ http_build_query(request()->except('page')) }}">Excel</a></li>
                <li><a class="dropdown-item text-label-md py-2" href="{{ route('traffics.export', 'csv') }}?{{ http_build_query(request()->except('page')) }}">CSV</a></li>
                <li><a class="dropdown-item text-label-md py-2" href="{{ route('traffics.export', 'pdf') }}?{{ http_build_query(request()->except('page')) }}">PDF</a></li>
            </ul>
        </div>
    </div>
</section>

@if(session('success'))
    <div class="rounded-xl px-4 py-2.5 flex items-center justify-between gap-3 border border-secondary/20 bg-secondary-container text-on-secondary-container shadow-sm">
        <div class="flex items-center gap-2"><span class="material-symbols-outlined text-[18px]" style="font-variation-settings: 'FILL' 1;">check_circle</span><span class="text-label-md font-medium">{{ session('success') }}</span></div>
        <button type="button" class="border-0 bg-transparent p-0" onclick="this.parentElement.remove()"><span class="material-symbols-outlined text-[18px]">close</span></button>
    </div>
@endif

<form action="{{ route('traffics.index') }}" method="GET" class="sbo-compact-filters bg-white rounded-lg p-2 border border-slate-200/60 shadow-sm relative z-10">
    <div class="pmf-row">
        @include('_partials.compact_filters.search', ['placeholder' => 'Cari gerbang, arah, jalan...'])
        @include('_partials.compact_filters.territory')
        @include('_partials.compact_filters.select', ['name' => 'jenisjalan', 'placeholder' => 'Jenis Jalan', 'options' => $jenisJalanList, 'autoSubmit' => false])
        @include('_partials.compact_filters.actions', [
            'resetUrl' => route('traffics.index'),
            'active' => request()->hasAny(['search','daerah','wilayah','jenisjalan']),
        ])
    </div>
</form>

<section class="bg-white rounded-xl border border-slate-200/60 overflow-hidden shadow-sm">
    <div class="px-4 py-3 border-b border-slate-100 flex items-center gap-1.5">
        <span class="material-symbols-outlined text-[16px] text-primary" style="font-variation-settings: 'FILL' 1;">traffic</span>
        <h3 class="text-[11px] font-bold uppercase tracking-wider text-slate-700 m-0">Daftar Traffic Dalkot
            @if(($data->total() ?? 0) > 0)<span class="text-[10px] font-semibold text-slate-400 normal-case ml-1">{{ number_format($data->total(), 0, ',', '.') }} record</span>@endif
        </h3>
    </div>
    @if($data->count() > 0)
        <div class="overflow-x-auto custom-scrollbar">
            <table class="w-full text-left min-w-[1000px]">
                <thead><tr class="bg-slate-50/50">
                    <th class="px-4 py-2 text-[10px] font-bold text-slate-400 uppercase tracking-widest border-b border-slate-100 w-10">#</th>
                    <th class="px-3 py-2 text-[10px] font-bold text-slate-400 uppercase tracking-widest border-b border-slate-100">Tanggal</th>
                    <th class="px-3 py-2 text-[10px] font-bold text-slate-400 uppercase tracking-widest border-b border-slate-100">Piket</th>
                    <th class="px-3 py-2 text-[10px] font-bold text-slate-400 uppercase tracking-widest border-b border-slate-100">Gerbang</th>
                    <th class="px-3 py-2 text-[10px] font-bold text-slate-400 uppercase tracking-widest border-b border-slate-100">Waktu</th>
                    <th class="px-3 py-2 text-[10px] font-bold text-slate-400 uppercase tracking-widest border-b border-slate-100">Arah Arus</th>
                    <th class="px-3 py-2 text-[10px] font-bold text-slate-400 uppercase tracking-widest border-b border-slate-100">Jenis jalan</th>
                    <th class="px-3 py-2 text-[10px] font-bold text-slate-400 uppercase tracking-widest border-b border-slate-100 text-center">R2</th>
                    <th class="px-3 py-2 text-[10px] font-bold text-slate-400 uppercase tracking-widest border-b border-slate-100 text-center">R4</th>
                    <th class="px-3 py-2 text-[10px] font-bold text-slate-400 uppercase tracking-widest border-b border-slate-100 text-center">R6+</th>
                    <th class="px-3 py-2 text-[10px] font-bold text-slate-400 uppercase tracking-widest border-b border-slate-100 text-center">Jumlah</th>
                    <th class="px-3 py-2 text-[10px] font-bold text-slate-400 uppercase tracking-widest border-b border-slate-100 text-right">Aksi</th>
                </tr></thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach($data as $item)
                        <tr class="hover:bg-slate-50 group">
                            <td class="px-4 py-2.5 text-label-sm text-slate-400">{{ $loop->iteration + ($data->currentPage() - 1) * $data->perPage() }}</td>
                            <td class="px-3 py-2.5 text-[11px] text-slate-700">{{ $item->tanggal ? \Carbon\Carbon::parse($item->tanggal)->locale('id')->translatedFormat('d F Y') : '-' }}</td>
                            <td class="px-3 py-2.5 text-[11px] text-slate-600">{{ $item->usm_nama ?? '-' }}</td>
                            <td class="px-3 py-2.5">
                                <span class="text-[11px] font-semibold text-slate-800 block">{{ $item->gerbangkota ?? '-' }}</span>
                            </td>
                            <td class="px-3 py-2.5 text-[11px] text-slate-500">{{ $item->waktu ? substr((string)$item->waktu, 0, 5) : '-' }}</td>
                            <td class="px-3 py-2.5 text-[11px] text-slate-600">{{ $item->araharus ?? '-' }}</td>
                            <td class="px-3 py-2.5">
                                <span class="text-[11px] text-slate-700 block">{{ $item->jenisjalan ?? '-' }}</span>
                                <span class="text-[10px] text-slate-400">{{ $item->namajalan ?? '' }}</span>
                            </td>
                            <td class="px-3 py-2.5 text-center text-[11px] text-slate-600">{{ $item->r2 ?? 0 }}</td>
                            <td class="px-3 py-2.5 text-center text-[11px] text-slate-600">{{ $item->r4 ?? 0 }}</td>
                            <td class="px-3 py-2.5 text-center text-[11px] text-slate-600">{{ $item->r6_plus ?? $item->r6 ?? 0 }}</td>
                            <td class="px-3 py-2.5 text-center text-[12px] font-bold text-slate-800">{{ $item->jumlah ?? (($item->r2??0)+($item->r4??0)+($item->r6??0)+($item->r6_plus??0)) }}</td>
                            <td class="px-3 py-2.5 text-right">
                                <div class="flex items-center justify-end gap-1 opacity-100 lg:opacity-0 group-hover:opacity-100 transition-opacity">
                                    <a href="{{ route('traffics.show', $item->id_trafficdalkot) }}" class="w-8 h-8 flex items-center justify-center text-primary hover:bg-primary/10 rounded-lg transition-colors text-decoration-none"><span class="material-symbols-outlined text-[18px]">visibility</span></a>
                                    <a href="{{ route('traffics.edit', $item->id_trafficdalkot) }}" class="w-8 h-8 flex items-center justify-center text-slate-400 hover:text-slate-700 hover:bg-slate-100 rounded-lg transition-colors text-decoration-none"><span class="material-symbols-outlined text-[18px]">edit</span></a>
                                    <form action="{{ route('traffics.destroy', $item->id_trafficdalkot) }}" method="POST" class="inline m-0" onsubmit="return confirm('Yakin hapus?');">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="w-8 h-8 flex items-center justify-center text-slate-400 hover:text-red-600 hover:bg-red-50 rounded-lg transition-colors border-0 bg-transparent p-0"><span class="material-symbols-outlined text-[18px]">delete</span></button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="px-4 py-2.5 bg-slate-50/50 border-t border-slate-100 flex flex-col sm:flex-row items-center justify-between gap-3">
            <p class="text-[10px] text-slate-500 font-medium m-0"><span class="font-bold text-slate-700">{{ $data->firstItem() ?? 0 }}–{{ $data->lastItem() ?? 0 }}</span> dari {{ number_format($data->total(), 0, ',', '.') }}</p>
            <div class="pagination-container">{{ $data->withQueryString()->links() }}</div>
        </div>
    @else
        <div class="py-10 text-center">
            <div class="w-12 h-12 bg-slate-100 rounded-full flex items-center justify-center mx-auto mb-3"><span class="material-symbols-outlined text-[26px] text-slate-400">folder_off</span></div>
            <p class="text-label-md text-slate-500 m-0">Tidak ada data.</p>
        </div>
    @endif
</section>
</div>
@endsection
