@extends('layouts.app')
@section('title', $pageTitle)
@section('page-title', $pageTitle)

@section('content')
@php
    $hasFilters = request()->hasAny(['search','tanggal_dari','tanggal_sampai','peristiwa','daerah','wilayah']);
@endphp
<div class="max-w-[1400px] mx-auto w-full space-y-4">

<section class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
    <div>
        <h2 class="text-[22px] font-extrabold text-on-surface tracking-tight leading-tight m-0">{{ $pageTitle }}</h2>
        <p class="text-[11px] text-slate-400 mt-0.5 m-0">Laporan patroli situasi lalu lintas dari monitoring CCTV</p>
    </div>
    <div class="flex flex-wrap items-center gap-2 shrink-0">
        <a href="{{ route('cctv-live.index') }}" class="flex items-center justify-center gap-2 bg-slate-800 text-white px-4 py-2.5 rounded-xl font-bold text-label-md shadow-sm hover:bg-slate-700 transition-all text-decoration-none">
            <span class="material-symbols-outlined text-[18px]" style="font-variation-settings: 'FILL' 1;">live_tv</span>
            <span>Live CCTV</span>
        </a>
        @unless(\App\Helpers\MenuHelper::isExecutiveForLayout(auth()->user()))
        <a href="{{ route('patrolicctv.create') }}" class="flex items-center justify-center gap-2 bg-primary text-on-primary px-4 py-2.5 rounded-xl font-bold text-label-md shadow-lg shadow-primary/25 hover:shadow-xl hover:-translate-y-0.5 transition-all text-decoration-none shrink-0">
            <span class="material-symbols-outlined text-[18px]" style="font-variation-settings: 'FILL' 1;">add</span>
            <span>Tambah Laporan</span>
        </a>
        @endunless
    </div>
</section>

@if(session('success'))
    <div class="rounded-xl px-4 py-2.5 flex items-center justify-between gap-3 border border-secondary/20 bg-secondary-container text-on-secondary-container shadow-sm">
        <div class="flex items-center gap-2">
            <span class="material-symbols-outlined text-[18px]" style="font-variation-settings: 'FILL' 1;">check_circle</span>
            <span class="text-label-md font-medium">{{ session('success') }}</span>
        </div>
        <button type="button" class="border-0 bg-transparent p-0 text-on-secondary-container/50 hover:text-on-secondary-container" onclick="this.parentElement.remove()">
            <span class="material-symbols-outlined text-[18px]">close</span>
        </button>
    </div>
@endif
@if(session('info'))
    <div class="rounded-xl px-4 py-2.5 flex items-center justify-between gap-3 border border-primary/15 bg-primary/5 text-primary shadow-sm">
        <div class="flex items-center gap-2">
            <span class="material-symbols-outlined text-[18px]">info</span>
            <span class="text-label-md font-medium">{{ session('info') }}</span>
        </div>
        <button type="button" class="border-0 bg-transparent p-0 text-primary/50 hover:text-primary" onclick="this.parentElement.remove()">
            <span class="material-symbols-outlined text-[18px]">close</span>
        </button>
    </div>
@endif
@if(session('warning'))
    <div class="rounded-xl px-4 py-2.5 flex items-center justify-between gap-3 border border-amber-200 bg-amber-50 text-amber-800 shadow-sm">
        <div class="flex items-center gap-2">
            <span class="material-symbols-outlined text-[18px]">warning</span>
            <span class="text-label-md font-medium">{{ session('warning') }}</span>
        </div>
        <button type="button" class="border-0 bg-transparent p-0 text-amber-500 hover:text-amber-700" onclick="this.parentElement.remove()">
            <span class="material-symbols-outlined text-[18px]">close</span>
        </button>
    </div>
@endif

@include('cctv._filters')

<section class="bg-white rounded-xl border border-slate-200/60 overflow-hidden shadow-sm">
    <div class="px-4 py-3 border-b border-slate-100 flex flex-wrap items-center justify-between gap-2">
        <div class="flex items-center gap-1.5">
            <span class="material-symbols-outlined text-[16px] text-primary" style="font-variation-settings: 'FILL' 1;">videocam</span>
            <h3 class="text-[11px] font-bold uppercase tracking-wider text-slate-700 m-0">Daftar Patroli CCTV
                @if($data->total() > 0)
                    <span class="text-[10px] font-semibold text-slate-400 normal-case ml-1">{{ number_format($data->total(), 0, ',', '.') }} laporan</span>
                @endif
            </h3>
        </div>
        @if($data->count())
            <div class="dropdown">
                <button type="button" class="w-9 h-9 flex items-center justify-center hover:bg-slate-50 border border-slate-100 rounded-xl text-slate-500 transition-colors shadow-sm bg-white border-0" data-bs-toggle="dropdown" title="Export">
                    <span class="material-symbols-outlined text-[18px]">download</span>
                </button>
                <ul class="dropdown-menu dropdown-menu-end shadow-lg border-0 rounded-xl">
                    <li><a class="dropdown-item text-label-md py-2" href="{{ route('patrolicctv.export', 'pdf') }}?{{ http_build_query(request()->only(['search','tanggal_dari','tanggal_sampai','peristiwa','daerah','wilayah'])) }}">PDF</a></li>
                    <li><a class="dropdown-item text-label-md py-2" href="{{ route('patrolicctv.export', 'excel') }}?{{ http_build_query(request()->only(['search','tanggal_dari','tanggal_sampai','peristiwa','daerah','wilayah'])) }}">Excel</a></li>
                    <li><a class="dropdown-item text-label-md py-2" href="{{ route('patrolicctv.export', 'csv') }}?{{ http_build_query(request()->only(['search','tanggal_dari','tanggal_sampai','peristiwa','daerah','wilayah'])) }}">CSV</a></li>
                </ul>
            </div>
        @endif
    </div>

    @if($data->count())
        <div class="overflow-x-auto custom-scrollbar">
            <table class="w-full text-left min-w-[1100px]">
                <thead>
                    <tr class="bg-slate-50/50">
                        <th class="px-4 py-2 text-[10px] font-bold text-slate-400 uppercase tracking-widest border-b border-slate-100 w-10">#</th>
                        <th class="px-3 py-2 text-[10px] font-bold text-slate-400 uppercase tracking-widest border-b border-slate-100">Capture</th>
                        <th class="px-3 py-2 text-[10px] font-bold text-slate-400 uppercase tracking-widest border-b border-slate-100">Tanggal</th>
                        <th class="px-3 py-2 text-[10px] font-bold text-slate-400 uppercase tracking-widest border-b border-slate-100">Lokasi</th>
                        @if($patrolCols['situasilalin'] ?? null)
                            <th class="px-3 py-2 text-[10px] font-bold text-slate-400 uppercase tracking-widest border-b border-slate-100">Situasi Lalin</th>
                        @endif
                        @if($patrolCols['situasicuaca'] ?? null)
                            <th class="px-3 py-2 text-[10px] font-bold text-slate-400 uppercase tracking-widest border-b border-slate-100">Situasi Cuaca</th>
                        @endif
                        @if($patrolCols['peristiwa'] ?? null)
                            <th class="px-3 py-2 text-[10px] font-bold text-slate-400 uppercase tracking-widest border-b border-slate-100">Peristiwa</th>
                        @endif
                        @if($patrolCols['ketcb'] ?? null)
                            <th class="px-3 py-2 text-[10px] font-bold text-slate-400 uppercase tracking-widest border-b border-slate-100">CB / Ket CB</th>
                        @endif
                        <th class="px-3 py-2 text-[10px] font-bold text-slate-400 uppercase tracking-widest border-b border-slate-100">Petugas</th>
                        <th class="px-3 py-2 text-[10px] font-bold text-slate-400 uppercase tracking-widest border-b border-slate-100 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach($data as $item)
                        @php
                            $foto = \App\Http\Controllers\CctvController::patrolValue($item, 'foto', $patrolCols);
                            $thumb = \App\Http\Controllers\CctvController::fotoPublicUrl($foto);
                            $tanggal = \App\Http\Controllers\CctvController::patrolValue($item, 'tanggal', $patrolCols);
                            $lokasi = \App\Http\Controllers\CctvController::patrolValue($item, 'lokasi', $patrolCols);
                            $lalin = \App\Http\Controllers\CctvController::patrolValue($item, 'situasilalin', $patrolCols);
                            $cuaca = \App\Http\Controllers\CctvController::patrolValue($item, 'situasicuaca', $patrolCols);
                            $peristiwa = \App\Http\Controllers\CctvController::patrolValue($item, 'peristiwa', $patrolCols);
                            $ketcb = \App\Http\Controllers\CctvController::patrolValue($item, 'ketcb', $patrolCols);
                        @endphp
                        <tr class="hover:bg-slate-50 group">
                            <td class="px-4 py-2.5 text-label-sm text-slate-400">{{ $loop->iteration + ($data->currentPage() - 1) * $data->perPage() }}</td>
                            <td class="px-3 py-2.5">
                                @if($thumb)
                                    <a href="{{ route('patrolicctv.show', $item->{$primaryKey}) }}" class="block shrink-0">
                                        <img src="{{ $thumb }}" alt="" class="w-11 h-11 rounded-lg object-cover border border-slate-200 group-hover:scale-105 transition-transform" referrerpolicy="no-referrer">
                                    </a>
                                @else
                                    <div class="w-11 h-11 rounded-lg bg-slate-100 flex items-center justify-center text-slate-300">
                                        <span class="material-symbols-outlined text-[20px]">image_not_supported</span>
                                    </div>
                                @endif
                            </td>
                            <td class="px-3 py-2.5">
                                <span class="text-[11px] font-bold text-slate-800">
                                    {{ $tanggal ? \Carbon\Carbon::parse($tanggal)->locale('id')->translatedFormat('d F Y') : '—' }}
                                </span>
                            </td>
                            <td class="px-3 py-2.5 text-[11px] text-slate-700 max-w-[200px]">
                                <span class="line-clamp-2">{{ $lokasi ?: '—' }}</span>
                            </td>
                            @if($patrolCols['situasilalin'] ?? null)
                                <td class="px-3 py-2.5">
                                    @if($lalin)
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-primary/8 text-primary border border-primary/15">{{ $lalin }}</span>
                                    @else
                                        <span class="text-slate-400">—</span>
                                    @endif
                                </td>
                            @endif
                            @if($patrolCols['situasicuaca'] ?? null)
                                <td class="px-3 py-2.5 text-[11px] text-slate-600">{{ $cuaca ?: '—' }}</td>
                            @endif
                            @if($patrolCols['peristiwa'] ?? null)
                                <td class="px-3 py-2.5 text-[11px] text-slate-700">{{ $peristiwa ?: '—' }}</td>
                            @endif
                            @if($patrolCols['ketcb'] ?? null)
                                <td class="px-3 py-2.5 text-[11px] text-slate-600 max-w-[160px]">
                                    <span class="line-clamp-2">{{ $ketcb ? \Str::limit($ketcb, 35) : '—' }}</span>
                                </td>
                            @endif
                            <td class="px-3 py-2.5">
                                <span class="text-[11px] font-medium text-slate-700">{{ $item->petugas_nama ?? '—' }}</span>
                            </td>
                            <td class="px-3 py-2.5 text-right">
                                <div class="flex items-center justify-end gap-1 opacity-100 lg:opacity-0 group-hover:opacity-100 transition-opacity">
                                    <a href="{{ route('patrolicctv.show', $item->{$primaryKey}) }}" class="w-8 h-8 flex items-center justify-center text-primary hover:bg-primary/10 rounded-lg transition-colors text-decoration-none" title="Detail">
                                        <span class="material-symbols-outlined text-[18px]">visibility</span>
                                    </a>
                                    @if(!empty($item->piket ?? $item->id_usm ?? $id))
                                        <a href="{{ route('personil.manager.show', $item->piket ?? $item->id_usm ?? $id) }}" class="w-8 h-8 flex items-center justify-center text-indigo-500 hover:bg-indigo-50 rounded-lg transition-colors text-decoration-none" title="Profil Operator"><span class="material-symbols-outlined text-[18px]">account_circle</span></a>
                                    @endif
                                    @unless(\App\Helpers\MenuHelper::isExecutiveForLayout(auth()->user()))
                                    <a href="{{ route('patrolicctv.edit', $item->{$primaryKey}) }}" class="w-8 h-8 flex items-center justify-center text-slate-400 hover:text-slate-700 hover:bg-slate-100 rounded-lg transition-colors text-decoration-none" title="Edit">
                                        <span class="material-symbols-outlined text-[18px]">edit</span>
                                    </a>
                                    <form action="{{ route('patrolicctv.destroy', $item->{$primaryKey}) }}" method="POST" class="inline m-0" onsubmit="return confirm('Yakin hapus data ini?');">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="w-8 h-8 flex items-center justify-center text-slate-400 hover:text-red-600 hover:bg-red-50 rounded-lg transition-colors border-0 bg-transparent p-0" title="Hapus">
                                            <span class="material-symbols-outlined text-[18px]">delete</span>
                                        </button>
                                    </form>
                                    @endunless
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
            <div class="pagination-container">{{ $data->appends(request()->query())->links() }}</div>
        </div>
    @else
        <div class="py-10 text-center">
            <div class="w-12 h-12 bg-slate-100 rounded-full flex items-center justify-center mx-auto mb-3">
                <span class="material-symbols-outlined text-[26px] text-slate-400">videocam_off</span>
            </div>
            <p class="text-label-md text-slate-500 font-medium max-w-md mx-auto m-0">
                @if($hasFilters)
                    Tidak ada data untuk filter yang dipilih.
                @else
                    Belum ada laporan patroli CCTV. Klik <strong class="text-slate-700">Tambah Laporan</strong> untuk memulai.
                @endif
            </p>
        </div>
    @endif
</section>

</div>
@endsection
