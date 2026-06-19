@extends('layouts.app')
@section('title', $pageTitle)
@section('page-title', $pageTitle)

@section('content')
<div class="max-w-[1400px] mx-auto w-full space-y-4">

{{-- Hero --}}
<section class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
    <div>
        <h2 class="text-[22px] font-extrabold text-on-surface tracking-tight leading-tight m-0">{{ $pageTitle }}</h2>
        <p class="text-[11px] text-slate-400 mt-0.5 m-0">Pos Pelayanan (Posyan)</p>
    </div>
    <div class="flex items-center gap-2 shrink-0">
        @unless($readOnly ?? false)
            <a href="{{ route('layanan.public_service.create') }}" class="flex items-center justify-center gap-2 bg-primary text-on-primary px-4 py-2.5 rounded-xl font-bold text-label-md shadow-lg shadow-primary/25 hover:shadow-xl hover:-translate-y-0.5 transition-all text-decoration-none">
                <span class="material-symbols-outlined text-[18px]" style="font-variation-settings: 'FILL' 1;">add</span>
                <span>Tambah</span>
            </a>
        @endunless
        <div class="dropdown">
            <button type="button" class="flex items-center justify-center gap-1.5 px-3 py-2.5 bg-white border border-slate-200 hover:bg-slate-50 text-slate-600 font-bold rounded-xl text-label-md transition-all h-[42px]" data-bs-toggle="dropdown">
                <span class="material-symbols-outlined text-[18px]">download</span>
                <span>Export</span>
            </button>
            <ul class="dropdown-menu dropdown-menu-end">
                <li><a class="dropdown-item text-label-md py-2" href="{{ route('layanan.public_service.export', 'excel') }}?{{ http_build_query(\App\Support\PublicServiceLayanan::preserveIndexQuery(request())) }}">Excel</a></li>
                <li><a class="dropdown-item text-label-md py-2" href="{{ route('layanan.public_service.export', 'csv') }}?{{ http_build_query(\App\Support\PublicServiceLayanan::preserveIndexQuery(request())) }}">CSV</a></li>
                <li><a class="dropdown-item text-label-md py-2" href="{{ route('layanan.public_service.export', 'pdf') }}?{{ http_build_query(\App\Support\PublicServiceLayanan::preserveIndexQuery(request())) }}">PDF</a></li>
            </ul>
        </div>
    </div>
</section>

{{-- Success flash --}}
@if(session('success'))
    <div class="rounded-xl px-4 py-2.5 flex items-center justify-between gap-3 border border-secondary/20 bg-secondary-container text-on-secondary-container shadow-sm">
        <div class="flex items-center gap-2">
            <span class="material-symbols-outlined text-[18px]" style="font-variation-settings: 'FILL' 1;">check_circle</span>
            <span class="text-label-md font-medium">{{ session('success') }}</span>
        </div>
        <button type="button" class="border-0 bg-transparent text-on-secondary-container/50 p-0" onclick="this.parentElement.remove()">
            <span class="material-symbols-outlined text-[18px]">close</span>
        </button>
    </div>
@endif

<form action="{{ route('layanan.public_service.index') }}" method="GET" class="sbo-compact-filters bg-white rounded-lg p-2 border border-slate-200/60 shadow-sm relative z-10">
    @if($readOnly ?? false)
        <input type="hidden" name="sb_grp" value="public_service">
    @endif
    <div class="pmf-row">
        @include('_partials.compact_filters.search', ['placeholder' => 'Cari posyan, lokasi, petugas...'])
        @include('_partials.compact_filters.actions', [
            'resetUrl' => route('layanan.public_service.index', ($readOnly ?? false) ? ['sb_grp' => 'public_service'] : []),
            'active' => request('search'),
        ])
    </div>
</form>

{{-- Table --}}
<section class="bg-white rounded-xl border border-slate-200/60 overflow-hidden shadow-sm">
    <div class="px-4 py-3 border-b border-slate-100 flex items-center gap-1.5">
        <span class="material-symbols-outlined text-[16px] text-primary" style="font-variation-settings: 'FILL' 1;">store</span>
        <h3 class="text-[11px] font-bold uppercase tracking-wider text-slate-700 m-0">
            Pos Pelayanan (Posyan)
            @if(($data->total() ?? 0) > 0)
                <span class="text-[10px] font-semibold text-slate-400 normal-case ml-1">{{ number_format($data->total(), 0, ',', '.') }} record</span>
            @endif
        </h3>
    </div>

    @if($data->count())
        <div class="overflow-x-auto custom-scrollbar">
            <table class="w-full text-left min-w-[700px]">
                <thead>
                    <tr class="bg-slate-50/50">
                        <th class="px-4 py-2 text-[10px] font-bold text-slate-400 uppercase tracking-widest border-b border-slate-100 w-10">#</th>
                        <th class="px-3 py-2 text-[10px] font-bold text-slate-400 uppercase tracking-widest border-b border-slate-100">Tanggal</th>
                        <th class="px-3 py-2 text-[10px] font-bold text-slate-400 uppercase tracking-widest border-b border-slate-100">Nama Posyan</th>
                        <th class="px-3 py-2 text-[10px] font-bold text-slate-400 uppercase tracking-widest border-b border-slate-100">Lokasi</th>
                        <th class="px-3 py-2 text-[10px] font-bold text-slate-400 uppercase tracking-widest border-b border-slate-100">Ops</th>
                        <th class="px-3 py-2 text-[10px] font-bold text-slate-400 uppercase tracking-widest border-b border-slate-100">Piket</th>
                        @if($readOnly ?? false)
                            <th class="px-3 py-2 text-[10px] font-bold text-slate-400 uppercase tracking-widest border-b border-slate-100">Wilayah</th>
                        @endif
                        <th class="px-3 py-2 text-[10px] font-bold text-slate-400 uppercase tracking-widest border-b border-slate-100 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach($data as $item)
                        <tr class="hover:bg-slate-50 group">
                            <td class="px-4 py-2.5 text-label-sm text-slate-400">{{ $loop->iteration + ($data->currentPage() - 1) * $data->perPage() }}</td>
                            <td class="px-3 py-2.5 text-[12px] font-semibold text-slate-700">{{ $item->tanggal ? \Carbon\Carbon::parse($item->tanggal)->format('d/m/Y') : '-' }}</td>
                            <td class="px-3 py-2.5 text-[11px] font-semibold text-slate-800">{{ \Str::limit($item->namaposyan ?? '-', 28) }}</td>
                            <td class="px-3 py-2.5 text-[11px] text-slate-500">{{ \Str::limit($item->lokasiposyan ?? '-', 24) }}</td>
                            <td class="px-3 py-2.5 text-[11px] text-slate-500">{{ $item->jenisops ?? '-' }} / {{ $item->nameops ?? '-' }}</td>
                            <td class="px-3 py-2.5 text-[11px] text-slate-600">{{ $item->usm_nama ?? $item->piket ?? '-' }}</td>
                            @if($readOnly ?? false)
                                <td class="px-3 py-2.5">
                                    <span class="text-[11px] font-bold" style="color:#D8A808;">{{ strtoupper($item->usm_daerah ?? '-') }}</span>
                                    <span class="block text-[10px] text-slate-400 uppercase">{{ $item->usm_wilayah ? strtoupper(trim(str_ireplace(['KABUPATEN ', 'KOTA ', 'KAB. '], '', $item->usm_wilayah))) : '' }}</span>
                                </td>
                            @endif
                            <td class="px-3 py-2.5 text-right">
                                <div class="flex items-center justify-end gap-1 opacity-100 lg:opacity-0 group-hover:opacity-100 transition-opacity">
                                    <a href="{{ route('layanan.public_service.show', $item->id_posyan) }}"
                                       class="w-8 h-8 flex items-center justify-center text-primary hover:bg-primary/10 rounded-lg transition-colors text-decoration-none" title="Detail">
                                        <span class="material-symbols-outlined text-[18px]">visibility</span>
                                    </a>
                                    @unless($readOnly ?? false)
                                        <a href="{{ route('layanan.public_service.edit', $item->id_posyan) }}"
                                           class="w-8 h-8 flex items-center justify-center text-slate-400 hover:text-slate-700 hover:bg-slate-100 rounded-lg transition-colors text-decoration-none" title="Edit">
                                            <span class="material-symbols-outlined text-[18px]">edit</span>
                                        </a>
                                        <form action="{{ route('layanan.public_service.destroy', $item->id_posyan) }}" method="POST" class="inline m-0"
                                              onsubmit="return confirm('Yakin hapus?')">
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
                {{ number_format($data->total(), 0, ',', '.') }} record
            </p>
            <div class="pagination-container">
                {{ $data->links() }}
            </div>
        </div>
    @else
        <div class="py-10 text-center">
            <div class="w-12 h-12 bg-slate-100 rounded-full flex items-center justify-center mx-auto mb-3 text-slate-400">
                <span class="material-symbols-outlined text-[26px]">folder_off</span>
            </div>
            <p class="text-label-md text-slate-500 font-medium m-0">Tidak ada data posyan.</p>
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
