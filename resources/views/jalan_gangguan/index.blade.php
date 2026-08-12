@extends('layouts.app')
@php $pageTitle = $pageTitle ?? 'Data Gangguan Jalan'; @endphp
@section('title', $pageTitle)
@section('page-title', $pageTitle)

@section('content')
@php
    $hasFilters = request()->hasAny(['search', 'daerah', 'wilayah', 'tanggal_dari', 'tanggal_sampai', 'fungsijalan', 'levelgangguan', 'faktor']);
    $primaryKey = 'id_datagangguan';
@endphp
<div class="max-w-[1400px] mx-auto w-full space-y-4">

<section class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
    <div>
        <h2 class="text-[22px] font-extrabold text-on-surface tracking-tight leading-tight m-0">{{ $pageTitle }}</h2>
        <p class="text-[11px] text-slate-400 mt-0.5 m-0">Data gangguan dan kerawanan pada ruas jalan</p>
    </div>
    <div class="flex items-center gap-2 shrink-0">
        @unless(\App\Helpers\MenuHelper::isExecutiveForLayout(auth()->user()))
        <a href="{{ route('jalan_gangguan.create') }}" class="flex items-center justify-center gap-2 bg-primary text-on-primary px-4 py-2.5 rounded-xl font-bold text-label-md shadow-lg shadow-primary/25 hover:shadow-xl hover:-translate-y-0.5 transition-all text-decoration-none">
            <span class="material-symbols-outlined text-[18px]" style="font-variation-settings: 'FILL' 1;">add</span>
            <span>Tambah Gangguan</span>
        </a>
        @endunless
        @include('_partials.export_dropdown', ['routeName' => 'jalan_gangguan.export', 'variant' => 'hero'])
        @if(Route::has('map.index'))
            <a href="{{ route('map.index') }}" class="flex items-center gap-1.5 px-3 py-2.5 bg-white border border-slate-200 hover:bg-slate-50 text-slate-600 font-bold rounded-xl text-label-md h-[42px] text-decoration-none">
                <span class="material-symbols-outlined text-[18px]">map</span><span>Peta</span>
            </a>
        @endif
    </div>
</section>

@if(session('success'))
    <div class="rounded-xl px-4 py-2.5 flex items-center justify-between gap-3 border border-secondary/20 bg-secondary-container text-on-secondary-container shadow-sm">
        <div class="flex items-center gap-2"><span class="material-symbols-outlined text-[18px]" style="font-variation-settings: 'FILL' 1;">check_circle</span><span class="text-label-md font-medium">{{ session('success') }}</span></div>
        <button type="button" class="border-0 bg-transparent p-0" onclick="this.parentElement.remove()"><span class="material-symbols-outlined text-[18px]">close</span></button>
    </div>
@endif

@if(!str_starts_with($tab ?? '', 'dacol_'))
<form action="{{ route('jalan_gangguan.index') }}" method="GET" class="sbo-compact-filters bg-white rounded-lg p-2 border border-slate-200/60 shadow-sm relative z-10">
    <input type="hidden" name="tab" value="{{ $tab ?? 'rinci' }}">
    @php $isOp = \App\Helpers\MenuHelper::isOperatorForLayout(auth()->user()); @endphp
    <div class="pmf-row">
        @include('_partials.compact_filters.territory')
        @include('_partials.compact_filters.select', ['name' => 'fungsijalan', 'placeholder' => 'Fungsi Jalan...', 'options' => collect($jenisJalanList ?? [])->mapWithKeys(fn($v) => [$v => $v])->all(), 'autoSubmit' => false])
        @include('_partials.compact_filters.select', ['name' => 'kerawanan', 'placeholder' => 'Pilih Kerawanan...', 'options' => collect($kerawananList ?? [])->mapWithKeys(fn($v) => [$v => $v])->all(), 'autoSubmit' => false])
        @include('_partials.compact_filters.select', ['name' => 'faktor', 'placeholder' => 'Pilih Faktor...', 'options' => collect($faktorList ?? [])->mapWithKeys(fn($v) => [$v => $v])->all(), 'autoSubmit' => false])
        @include('_partials.compact_filters.search', ['placeholder' => 'Pencarian...'])
        @include('_partials.compact_filters.actions', [
            'resetUrl' => route('jalan_gangguan.index'),
            'active' => request()->hasAny(['search', 'fungsijalan', 'kerawanan', 'faktor', 'levelgangguan']),
        ])
    </div>
</form>
@endif

<section class="bg-white rounded-xl border border-slate-200/60 overflow-hidden shadow-sm">
    <div class="px-4 py-3 border-b border-slate-100 flex flex-col sm:flex-row sm:items-center justify-between gap-2">
        <div class="flex items-center gap-1.5">
            <span class="material-symbols-outlined text-[16px] text-amber-500" style="font-variation-settings: 'FILL' 1;">report</span>
            <h3 class="text-[11px] font-bold uppercase tracking-wider text-slate-700 m-0">Daftar Data Gangguan
                @if(($tab ?? 'rinci') === 'rinci' && ($data->total() ?? 0) > 0)<span class="text-[10px] font-semibold text-slate-400 normal-case ml-1">{{ number_format($data->total(), 0, ',', '.') }} record</span>@endif
            </h3>
        </div>
        <div class="flex gap-4 border-l border-slate-200 pl-4">
            <a href="{{ route('jalan_gangguan.index', array_merge(request()->except('tab','page'), ['tab' => 'dacol_trouble_spot'])) }}"
               class="text-[11px] font-bold uppercase tracking-wider text-decoration-none {{ ($tab ?? '') === 'dacol_trouble_spot' ? 'text-primary' : 'text-slate-400 hover:text-slate-600' }}">Black & Trouble Spot (Dacol)</a>
            <a href="{{ route('jalan_gangguan.index', array_merge(request()->except('tab','page'), ['tab' => 'dacol_wilayah_rawan'])) }}"
               class="text-[11px] font-bold uppercase tracking-wider text-decoration-none {{ ($tab ?? '') === 'dacol_wilayah_rawan' ? 'text-primary' : 'text-slate-400 hover:text-slate-600' }}">Wilayah Rawan (Dacol)</a>
        </div>
    </div>
    
    @if(str_starts_with($tab ?? '', 'dacol_'))
        @include('sm.dacol._table')
    @elseif($data->count() > 0)
        <div class="overflow-x-auto custom-scrollbar">
            <table class="w-full text-left min-w-[860px]">
                <thead><tr class="bg-slate-50/50">
                    <th class="px-4 py-2 text-[10px] font-bold text-slate-400 uppercase tracking-widest border-b border-slate-100 w-10">#</th>
                    @if($isOp)
                        <th class="px-3 py-2 text-[10px] font-bold text-slate-400 uppercase tracking-widest border-b border-slate-100">Fungsi Jalan</th>
                        <th class="px-3 py-2 text-[10px] font-bold text-slate-400 uppercase tracking-widest border-b border-slate-100">Nama Jalan</th>
                        <th class="px-3 py-2 text-[10px] font-bold text-slate-400 uppercase tracking-widest border-b border-slate-100">Kerawanan</th>
                        <th class="px-3 py-2 text-[10px] font-bold text-slate-400 uppercase tracking-widest border-b border-slate-100">Faktor</th>
                        <th class="px-3 py-2 text-[10px] font-bold text-slate-400 uppercase tracking-widest border-b border-slate-100">Penyebab</th>
                        <th class="px-3 py-2 text-[10px] font-bold text-slate-400 uppercase tracking-widest border-b border-slate-100">Level gangguan</th>
                    @else
                        @if(\App\Support\TerritoryIndexFilters::canFilterByDaerah())
                            <th class="px-3 py-2 text-[10px] font-bold text-slate-400 uppercase tracking-widest border-b border-slate-100">Polda</th>
                        @endif
                        <th class="px-3 py-2 text-[10px] font-bold text-slate-400 uppercase tracking-widest border-b border-slate-100">Polres</th>
                        <th class="px-3 py-2 text-[10px] font-bold text-slate-400 uppercase tracking-widest border-b border-slate-100">Nama Jalan</th>
                        <th class="px-3 py-2 text-[10px] font-bold text-slate-400 uppercase tracking-widest border-b border-slate-100">Kerawanan</th>
                        <th class="px-3 py-2 text-[10px] font-bold text-slate-400 uppercase tracking-widest border-b border-slate-100">Faktor</th>
                    @endif
                    <th class="px-3 py-2 text-[10px] font-bold text-slate-400 uppercase tracking-widest border-b border-slate-100 text-right">Aksi</th>
                </tr></thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach($data as $item)
                        @php
                            $rowId = $item->{$primaryKey};
                            $canMutate = \App\Support\RoleMutationPolicy::canMutateOperationalRow(auth()->user(), $item, 'data_gangguan');
                        @endphp
                        <tr class="hover:bg-slate-50 group">
                            <td class="px-4 py-2.5 text-label-sm text-slate-400">{{ $loop->iteration + ($data->currentPage() - 1) * $data->perPage() }}</td>
                            @if($isOp)
                                <td class="px-3 py-2.5">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-primary/8 text-primary border border-primary/15">{{ $item->fungsijalan ?: '—' }}</span>
                                </td>
                                <td class="px-3 py-2.5">
                                    <a href="{{ route('jalan_gangguan.show', $rowId) }}" class="text-[11px] font-semibold text-primary hover:underline text-decoration-none block">{{ \Str::limit($item->namajalan ?: 'Detail', 32) }}</a>
                                </td>
                                <td class="px-3 py-2.5 text-[11px] text-slate-600">{{ $item->kerawanan ?: '—' }}</td>
                                <td class="px-3 py-2.5 text-[11px] text-slate-600">{{ $item->faktor ?: '—' }}</td>
                                <td class="px-3 py-2.5 text-[11px] text-slate-600">{{ \Str::limit($item->penyebab ?? '—', 24) }}</td>
                                <td class="px-3 py-2.5">
                                    @if($item->levelgangguan)
                                        <span class="inline-flex items-center mt-0.5 px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-50 text-amber-700 border border-amber-200">{{ $item->levelgangguan }}</span>
                                    @else
                                        <span class="text-slate-400">—</span>
                                    @endif
                                </td>
                            @else
                                @if(\App\Support\TerritoryIndexFilters::canFilterByDaerah())
                                    <td class="px-3 py-2.5">
                                        <span class="text-[11px] font-bold" style="color:#D8A808;">{{ strtoupper($item->usm_daerah ?? '—') }}</span>
                                    </td>
                                @endif
                                <td class="px-3 py-2.5">
                                    <span class="text-[11px] font-semibold text-slate-600 uppercase">{{ $item->usm_wilayah ? strtoupper(trim(str_ireplace(['KABUPATEN ', 'KOTA ', 'KAB. '], '', $item->usm_wilayah))) : '—' }}</span>
                                </td>
                                <td class="px-3 py-2.5">
                                    <a href="{{ route('jalan_gangguan.show', $rowId) }}" class="text-[11px] font-semibold text-primary hover:underline text-decoration-none block">{{ \Str::limit($item->namajalan ?: 'Detail', 32) }}</a>
                                </td>
                                <td class="px-3 py-2.5 text-[11px] text-slate-600">{{ $item->kerawanan ?: '—' }}</td>
                                <td class="px-3 py-2.5 text-[11px] text-slate-600">{{ $item->faktor ?: '—' }}</td>
                            @endif
                            <td class="px-3 py-2.5 text-right">
                                <div class="flex items-center justify-end gap-1 opacity-100 lg:opacity-0 group-hover:opacity-100 transition-opacity">
                                    <a href="{{ route('jalan_gangguan.show', $rowId) }}" class="w-8 h-8 flex items-center justify-center text-primary hover:bg-primary/10 rounded-lg transition-colors text-decoration-none" title="Detail"><span class="material-symbols-outlined text-[18px]">visibility</span></a>
                                    @if(!empty($item->piket ?? $item->id_usm))
                                        <a href="{{ route('personil.manager.show', $item->piket ?? $item->id_usm) }}" class="w-8 h-8 flex items-center justify-center text-indigo-500 hover:bg-indigo-50 rounded-lg transition-colors text-decoration-none" title="Profil Operator"><span class="material-symbols-outlined text-[18px]">account_circle</span></a>
                                    @endif
                                    @if($canMutate)
                                        <a href="{{ route('jalan_gangguan.edit', $rowId) }}" class="w-8 h-8 flex items-center justify-center text-slate-400 hover:text-slate-700 hover:bg-slate-100 rounded-lg transition-colors text-decoration-none" title="Edit"><span class="material-symbols-outlined text-[18px]">edit</span></a>
                                        <form action="{{ route('jalan_gangguan.destroy', $rowId) }}" method="POST" class="inline m-0" onsubmit="return confirm('Yakin hapus data gangguan ini?');">
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
            <p class="text-label-md text-slate-500 m-0">{{ $hasFilters ? 'Tidak ada data untuk filter yang dipilih.' : 'Belum ada data gangguan.' }}</p>
            @if($hasFilters)
                <a href="{{ route('jalan_gangguan.index') }}" class="inline-flex items-center gap-1.5 mt-3 px-3 py-2 bg-slate-100 hover:bg-slate-200 text-slate-600 font-bold rounded-xl text-label-md transition-all text-decoration-none">
                    <span class="material-symbols-outlined text-[16px]">cached</span> Reset
                </a>
            @endif
        </div>
    @endif
</section>
</div>
@endsection
