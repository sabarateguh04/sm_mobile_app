@extends('layouts.app')
@section('title', 'Terminal')
@section('page-title', 'Data Terminal')

@section('content')
<div class="max-w-[1400px] mx-auto w-full space-y-4">

<section class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
    <div>
        <h2 class="text-[22px] font-extrabold text-on-surface tracking-tight leading-tight m-0">Data Terminal</h2>
        <p class="text-[11px] text-slate-400 mt-0.5 m-0">Inventarisasi terminal dan fasilitas transportasi</p>
    </div>
    <div class="flex items-center gap-2 shrink-0">
        <a href="{{ route('terminal.create') }}" class="flex items-center justify-center gap-2 bg-primary text-on-primary px-4 py-2.5 rounded-xl font-bold text-label-md shadow-lg shadow-primary/25 hover:shadow-xl hover:-translate-y-0.5 transition-all text-decoration-none">
            <span class="material-symbols-outlined text-[18px]" style="font-variation-settings: 'FILL' 1;">add</span>
            <span>Tambah Terminal</span>
        </a>
        @if(Route::has('map.index'))
            <a href="{{ route('map.index') }}" class="flex items-center gap-1.5 px-3 py-2.5 bg-white border border-slate-200 hover:bg-slate-50 text-slate-600 font-bold rounded-xl text-label-md h-[42px] text-decoration-none">
                <span class="material-symbols-outlined text-[18px]">map</span><span>Peta</span>
            </a>
        @endif
        @include('_partials.export_dropdown', ['routeName' => 'terminal.export', 'variant' => 'hero'])
    </div>
</section>

@if(session('success'))
    <div class="rounded-xl px-4 py-2.5 flex items-center justify-between gap-3 border border-secondary/20 bg-secondary-container text-on-secondary-container shadow-sm">
        <div class="flex items-center gap-2"><span class="material-symbols-outlined text-[18px]" style="font-variation-settings: 'FILL' 1;">check_circle</span><span class="text-label-md font-medium">{{ session('success') }}</span></div>
        <button type="button" class="border-0 bg-transparent p-0" onclick="this.parentElement.remove()"><span class="material-symbols-outlined text-[18px]">close</span></button>
    </div>
@endif

<form action="{{ route('terminal.index') }}" method="GET" class="sbo-compact-filters bg-white rounded-lg p-2 border border-slate-200/60 shadow-sm relative z-10">
    <div class="pmf-row">
        @include('_partials.compact_filters.territory')
        @include('_partials.compact_filters.select', [
            'name' => 'jenisterminal',
            'placeholder' => 'Jenis Terminal',
            'options' => $jenisTerminal ?? [],
        ])
        @include('_partials.compact_filters.search', ['placeholder' => 'Cari nama, jalan...'])
        @include('_partials.compact_filters.actions', [
            'resetUrl' => route('terminal.index'),
            'active' => request()->hasAny(['daerah', 'wilayah', 'jenisterminal', 'search']),
        ])
    </div>
</form>

<section class="bg-white rounded-xl border border-slate-200/60 overflow-hidden shadow-sm">
    <div class="px-4 py-3 border-b border-slate-100 flex items-center gap-1.5">
        <span class="material-symbols-outlined text-[16px] text-primary" style="font-variation-settings: 'FILL' 1;">directions_bus</span>
        <h3 class="text-[11px] font-bold uppercase tracking-wider text-slate-700 m-0">Daftar Terminal
            @if(($data->total() ?? 0) > 0)<span class="text-[10px] font-semibold text-slate-400 normal-case ml-1">{{ number_format($data->total(), 0, ',', '.') }} record</span>@endif
        </h3>
    </div>
    @if($data->count() > 0)
        <div class="overflow-x-auto custom-scrollbar">
            <table class="w-full text-left min-w-[860px]">
                <thead><tr class="bg-slate-50/50">
                    <th class="px-4 py-2 text-[10px] font-bold text-slate-400 uppercase tracking-widest border-b border-slate-100 w-10">#</th>
                    <th class="px-3 py-2 text-[10px] font-bold text-slate-400 uppercase tracking-widest border-b border-slate-100">Tanggal</th>
                    <th class="px-3 py-2 text-[10px] font-bold text-slate-400 uppercase tracking-widest border-b border-slate-100">Jenis</th>
                    <th class="px-3 py-2 text-[10px] font-bold text-slate-400 uppercase tracking-widest border-b border-slate-100">Nama Terminal</th>
                    <th class="px-3 py-2 text-[10px] font-bold text-slate-400 uppercase tracking-widest border-b border-slate-100">Jalan</th>
                    <th class="px-3 py-2 text-[10px] font-bold text-slate-400 uppercase tracking-widest border-b border-slate-100">CP</th>
                    <th class="px-3 py-2 text-[10px] font-bold text-slate-400 uppercase tracking-widest border-b border-slate-100">Koordinat</th>
                    <th class="px-3 py-2 text-[10px] font-bold text-slate-400 uppercase tracking-widest border-b border-slate-100">Wilayah/Daerah</th>
                    <th class="px-3 py-2 text-[10px] font-bold text-slate-400 uppercase tracking-widest border-b border-slate-100 text-right">Aksi</th>
                </tr></thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach($data as $item)
                        <tr class="hover:bg-slate-50 group">
                            <td class="px-4 py-2.5 text-label-sm text-slate-400">{{ $loop->iteration + ($data->currentPage() - 1) * $data->perPage() }}</td>
                            <td class="px-3 py-2.5 text-[11px] text-slate-700">{{ $item->tanggal ? \Carbon\Carbon::parse($item->tanggal)->locale('id')->translatedFormat('d F Y') : '-' }}</td>
                            <td class="px-3 py-2.5">
                                @if($item->jenisterminal)<span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-primary/8 text-primary border border-primary/15">{{ $item->jenisterminal }}</span>@else <span class="text-slate-400">-</span>@endif
                            </td>
                            <td class="px-3 py-2.5 text-[12px] font-bold text-slate-800">{{ $item->namaterminal ?? '-' }}</td>
                            <td class="px-3 py-2.5 text-[11px] text-slate-600">{{ \Str::limit($item->jalan ?? '-', 24) }}</td>
                            <td class="px-3 py-2.5">
                                <div class="text-[11px] font-medium text-slate-700">{{ $item->namecp ?? '-' }}</div>
                                @if($item->cp ?? null)<div class="text-[10px] text-slate-400">{{ $item->cp }}</div>@endif
                            </td>
                            <td class="px-3 py-2.5">
                                @if($item->koordinat)
                                    <a href="https://www.google.com/maps/search/?api=1&query={{ $item->koordinat }}" target="_blank" rel="noopener" class="flex items-center gap-1 text-[10px] font-mono text-green-600 hover:underline text-decoration-none">
                                        <span class="material-symbols-outlined text-[14px]">location_on</span>{{ $item->koordinat }}
                                    </a>
                                @else <span class="text-slate-400">-</span>@endif
                            </td>
                            <td class="px-3 py-2.5 text-[11px] text-slate-600">{{ $item->usm_wilayah ? strtoupper(trim(str_ireplace(['KABUPATEN ', 'KOTA ', 'KAB. '], '', $item->usm_wilayah))) : '-' }} / {{ $item->usm_daerah ?? '-' }}</td>
                            <td class="px-3 py-2.5 text-right">
                                <div class="flex items-center justify-end gap-1 opacity-100 lg:opacity-0 group-hover:opacity-100 transition-opacity">
                                    <a href="{{ route('terminal.show', $item->id_terminal) }}" class="w-8 h-8 flex items-center justify-center text-primary hover:bg-primary/10 rounded-lg transition-colors text-decoration-none"><span class="material-symbols-outlined text-[18px]">visibility</span></a>
                                    <a href="{{ route('terminal.edit', $item->id_terminal) }}" class="w-8 h-8 flex items-center justify-center text-slate-400 hover:text-slate-700 hover:bg-slate-100 rounded-lg transition-colors text-decoration-none"><span class="material-symbols-outlined text-[18px]">edit</span></a>
                                    <form action="{{ route('terminal.destroy', $item->id_terminal) }}" method="POST" class="inline m-0" onsubmit="return confirm('Yakin hapus?');">
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
            <div class="pagination-container">{{ $data->links() }}</div>
        </div>
    @else
        <div class="py-10 text-center">
            <div class="w-12 h-12 bg-slate-100 rounded-full flex items-center justify-center mx-auto mb-3"><span class="material-symbols-outlined text-[26px] text-slate-400">folder_off</span></div>
            <p class="text-label-md text-slate-500 m-0">Belum ada data terminal.</p>
        </div>
    @endif
</section>
</div>
@endsection
