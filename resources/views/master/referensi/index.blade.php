@extends('layouts.app')
@php
    $pageTitle = $pageTitle ?? 'Master Referensi';
    $isSuperAdmin = Auth::user() && \App\Helpers\MenuHelper::isSuperAdminForLayout(Auth::user());
    $rowTotal = $rows instanceof \Illuminate\Pagination\AbstractPaginator ? $rows->total() : count($rows);
    $hasSearch = request()->filled('search');
@endphp
@section('title', $pageTitle)
@section('page-title', $pageTitle)

@section('content')
<div class="max-w-[1400px] mx-auto w-full space-y-4">

<section class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
    <div>
        <h2 class="text-[22px] font-extrabold text-on-surface tracking-tight leading-tight m-0">{{ $pageTitle }}</h2>
        <p class="text-[11px] text-slate-400 mt-0.5 m-0">Kelola data referensi master untuk modul operasional</p>
    </div>
</section>

@if(session('success'))
    <div class="rounded-xl px-4 py-2.5 flex items-center justify-between gap-3 border border-secondary/20 bg-secondary-container text-on-secondary-container shadow-sm">
        <div class="flex items-center gap-2"><span class="material-symbols-outlined text-[18px]" style="font-variation-settings: 'FILL' 1;">check_circle</span><span class="text-label-md font-medium">{{ session('success') }}</span></div>
        <button type="button" class="border-0 bg-transparent p-0" onclick="this.parentElement.remove()"><span class="material-symbols-outlined text-[18px]">close</span></button>
    </div>
@endif
@if(session('error'))
    <div class="rounded-xl px-4 py-2.5 flex items-center justify-between gap-3 border border-red-200 bg-red-50 text-red-800 shadow-sm">
        <div class="flex items-center gap-2"><span class="material-symbols-outlined text-[18px]">error</span><span class="text-label-md font-medium">{{ session('error') }}</span></div>
        <button type="button" class="border-0 bg-transparent p-0" onclick="this.parentElement.remove()"><span class="material-symbols-outlined text-[18px]">close</span></button>
    </div>
@endif

<div class="grid grid-cols-1 lg:grid-cols-[260px_1fr] gap-4 items-start">
    <aside class="bg-white rounded-xl border border-slate-200/60 shadow-sm overflow-hidden">
        <div class="px-4 py-3 border-b border-slate-100 flex items-center gap-1.5">
            <span class="material-symbols-outlined text-[16px] text-primary" style="font-variation-settings: 'FILL' 1;">list</span>
            <h3 class="text-[11px] font-bold uppercase tracking-wider text-slate-700 m-0">Kategori</h3>
        </div>
        <ul class="max-h-[620px] overflow-y-auto custom-scrollbar m-0 p-0 list-none">
            @foreach($groups as $groupName => $items)
                {{-- Group Header --}}
                <li class="px-4 pt-3 pb-1 flex items-center gap-1.5 sticky top-0 bg-white z-10 border-b border-slate-100">
                    <span class="material-symbols-outlined text-[13px] text-slate-400">folder</span>
                    <span class="text-[9px] font-black uppercase tracking-widest text-slate-400">{{ $groupName }}</span>
                </li>
                {{-- Items in group --}}
                @foreach($items as $t)
                    <li>
                        <a href="{{ route('master.referensi.index', ['ref' => $t['key']]) }}"
                           class="flex items-center justify-between gap-2 px-4 py-2 text-decoration-none transition-colors {{ $active === $t['key'] ? 'bg-primary text-white' : 'text-slate-700 hover:bg-slate-50' }}">
                            <span class="text-[12px] font-semibold leading-snug pl-3">{{ $t['lab'] }}</span>
                            <span class="inline-flex items-center justify-center min-w-[1.5rem] px-1.5 py-0.5 rounded-md text-[10px] font-bold {{ $active === $t['key'] ? 'bg-white/20 text-white' : 'bg-slate-100 text-slate-500' }}">{{ $t['count'] }}</span>
                        </a>
                    </li>
                @endforeach
            @endforeach
        </ul>
    </aside>


    <section class="bg-white rounded-xl border border-slate-200/60 overflow-hidden shadow-sm min-w-0">
        <div class="px-4 py-3 border-b border-slate-100 flex flex-col sm:flex-row sm:items-center justify-between gap-2">
            <div class="flex items-center gap-1.5 min-w-0">
                <span class="material-symbols-outlined text-[16px] text-primary shrink-0" style="font-variation-settings: 'FILL' 1;">menu_book</span>
                <h3 class="text-[11px] font-bold uppercase tracking-wider text-slate-700 m-0 truncate">{{ $label }}</h3>
                <span class="text-[10px] font-semibold text-slate-400 normal-case">{{ number_format($rowTotal, 0, ',', '.') }} item</span>
            </div>
            @if($isSuperAdmin && empty($smdSource))
                <button type="button"
                    class="flex items-center justify-center gap-1.5 px-3 py-2 bg-primary hover:bg-primary/90 text-white font-bold rounded-lg text-[12px] transition-all shadow-sm border-0 shrink-0"
                    data-bs-toggle="modal" data-bs-target="#createModal">
                    <span class="material-symbols-outlined text-[16px]">add</span>
                    <span>Tambah Data</span>
                </button>
            @endif
        </div>

        <div class="p-3 sm:p-4 border-b border-slate-100">
            <form action="{{ route('master.referensi.index') }}" method="GET" class="sbo-compact-filters">
                <input type="hidden" name="ref" value="{{ $active }}">
                <div class="pmf-row">
                    @include('_partials.compact_filters.search', ['placeholder' => 'Cari nama...'])
                    @include('_partials.compact_filters.actions', [
                        'resetUrl' => route('master.referensi.index', ['ref' => $active]),
                        'active' => $hasSearch,
                    ])
                </div>
            </form>
        </div>

        @if($rows->count() > 0)
            <div class="overflow-x-auto custom-scrollbar">
                <table class="w-full text-left min-w-[480px]">
                    <thead><tr class="bg-slate-50/50">
                        <th class="px-4 py-2 text-[10px] font-bold text-slate-400 uppercase tracking-widest border-b border-slate-100 w-20">ID</th>
                        <th class="px-3 py-2 text-[10px] font-bold text-slate-400 uppercase tracking-widest border-b border-slate-100">Nama</th>
                        @if(!empty($parentCol))
                            <th class="px-3 py-2 text-[10px] font-bold text-slate-400 uppercase tracking-widest border-b border-slate-100">{{ $parentLabel ?? 'Parent' }}</th>
                        @endif
                        @if($isSuperAdmin && empty($smdSource))
                            <th class="px-3 py-2 text-[10px] font-bold text-slate-400 uppercase tracking-widest border-b border-slate-100 text-right w-28">Aksi</th>
                        @endif
                    </tr></thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach($rows as $r)
                            @php
                                $rowId = data_get($r, $idCol);
                                $rowName = data_get($r, $nameCol);
                                $rowParent = $parentCol ? data_get($r, $parentCol) : null;
                            @endphp
                            <tr class="hover:bg-slate-50 group">
                                <td class="px-4 py-2.5 text-[11px] text-slate-400 font-medium">{{ $rowId ?? '—' }}</td>
                                <td class="px-3 py-2.5 text-[12px] font-semibold text-slate-800">{{ $rowName ?? '—' }}</td>
                                @if(!empty($parentCol))
                                    <td class="px-3 py-2.5 text-[11px] text-slate-500 font-medium">
                                        @if(!empty($rowParent))
                                            <span class="inline-flex items-center px-2 py-0.5 rounded-md bg-slate-100 border border-slate-200">{{ $rowParent }}</span>
                                        @else
                                            —
                                        @endif
                                    </td>
                                @endif
                                @if($isSuperAdmin && empty($smdSource))
                                    <td class="px-3 py-2.5 text-right">
                                        <div class="flex items-center justify-end gap-1 opacity-100 lg:opacity-0 group-hover:opacity-100 transition-opacity">
                                            <button type="button"
                                                class="w-8 h-8 flex items-center justify-center text-slate-400 hover:text-slate-700 hover:bg-slate-100 rounded-lg transition-colors border-0 bg-transparent p-0"
                                                data-bs-toggle="modal" data-bs-target="#editModal{{ $rowId }}" title="Edit">
                                                <span class="material-symbols-outlined text-[18px]">edit</span>
                                            </button>
                                            <form action="{{ route('master.referensi.destroy', $rowId) }}" method="POST" class="inline m-0" onsubmit="return confirm('Yakin ingin menghapus data ini?');">
                                                @csrf @method('DELETE')
                                                <input type="hidden" name="ref" value="{{ $active }}">
                                                <button type="submit" class="w-8 h-8 flex items-center justify-center text-slate-400 hover:text-red-600 hover:bg-red-50 rounded-lg transition-colors border-0 bg-transparent p-0" title="Hapus">
                                                    <span class="material-symbols-outlined text-[18px]">delete</span>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                @endif
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @if($rows instanceof \Illuminate\Pagination\AbstractPaginator)
                <div class="px-4 py-2.5 bg-slate-50/50 border-t border-slate-100 flex flex-col sm:flex-row items-center justify-between gap-3">
                    <p class="text-[10px] text-slate-500 font-medium m-0"><span class="font-bold text-slate-700">{{ $rows->firstItem() ?? 0 }}–{{ $rows->lastItem() ?? 0 }}</span> dari {{ number_format($rows->total(), 0, ',', '.') }}</p>
                    <div class="pagination-container">{{ $rows->links() }}</div>
                </div>
            @endif
        @else
            <div class="py-10 text-center">
                <div class="w-12 h-12 bg-slate-100 rounded-full flex items-center justify-center mx-auto mb-3"><span class="material-symbols-outlined text-[26px] text-slate-400">folder_off</span></div>
                <p class="text-label-md text-slate-500 font-medium m-0">
                    {{ $hasSearch ? 'Tidak ada data untuk pencarian ini.' : 'Belum ada data ' . strtolower($label) . '.' }}
                </p>
                @if($hasSearch)
                    <a href="{{ route('master.referensi.index', ['ref' => $active]) }}" class="inline-flex items-center gap-1.5 mt-3 px-3 py-2 bg-slate-100 hover:bg-slate-200 text-slate-600 font-bold rounded-xl text-label-md transition-all text-decoration-none">
                        <span class="material-symbols-outlined text-[16px]">cached</span> Reset
                    </a>
                @elseif($isSuperAdmin && empty($smdSource))
                    <button type="button"
                        class="inline-flex items-center gap-1.5 mt-3 px-3 py-2 bg-primary text-white font-bold rounded-xl text-label-md transition-all border-0"
                        data-bs-toggle="modal" data-bs-target="#createModal">
                        <span class="material-symbols-outlined text-[16px]">add</span> Tambah sekarang
                    </button>
                @endif
            </div>
        @endif
    </section>
</div>
</div>

@if($isSuperAdmin && empty($smdSource))
    <div class="modal fade" id="createModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <form action="{{ route('master.referensi.store') }}" method="POST">
                @csrf
                <input type="hidden" name="ref" value="{{ $active }}">
                <div class="modal-content border-0 rounded-xl shadow-lg overflow-hidden">
                    <div class="modal-header border-b border-slate-100 px-4 py-3">
                        <h5 class="modal-title text-[14px] font-bold text-slate-800 m-0">Tambah {{ $label }}</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body px-4 py-3 space-y-3">
                        @if(!empty($parentCol))
                            <div>
                                <label class="block text-[10px] font-bold text-slate-500 uppercase tracking-wide mb-1">{{ $parentLabel ?? 'Parent' }}</label>
                                <select name="parent_val" @if(!empty($parentRequired)) required @endif class="w-full bg-slate-50 border border-slate-200 rounded-lg px-3 py-2 text-[13px] text-slate-800 focus:border-primary focus:bg-white focus:ring-0 outline-none appearance-none">
                                    <option value="">{{ !empty($parentRequired) ? '— Pilih ' . ($parentLabel ?? 'parent') . ' —' : '— Tidak ada parent —' }}</option>
                                    @foreach($parentOptions ?? [] as $val => $labelOpt)
                                        <option value="{{ $val }}">{{ $labelOpt }}</option>
                                    @endforeach
                                </select>
                            </div>
                        @endif
                        <div>
                            <label class="block text-[10px] font-bold text-slate-500 uppercase tracking-wide mb-1">Nama <span class="text-red-500">*</span></label>
                            <input type="text" name="name" required
                                class="w-full bg-slate-50 border border-slate-200 rounded-lg px-3 py-2 text-[13px] text-slate-800 focus:border-primary focus:bg-white focus:ring-0 outline-none">
                        </div>
                    </div>
                    <div class="modal-footer border-t border-slate-100 px-4 py-3 gap-2">
                        <button type="button" class="px-3 py-2 bg-slate-100 hover:bg-slate-200 text-slate-600 font-bold rounded-lg text-[12px] border-0" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="px-4 py-2 bg-primary hover:bg-primary/90 text-white font-bold rounded-lg text-[12px] border-0">Simpan</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    @foreach($rows as $r)
        @php
            $rowId = data_get($r, $idCol);
            $rowName = data_get($r, $nameCol);
            $rowParent = $parentCol ? data_get($r, $parentCol) : null;
        @endphp
        <div class="modal fade" id="editModal{{ $rowId }}" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <form action="{{ route('master.referensi.update', $rowId) }}" method="POST">
                    @csrf @method('PUT')
                    <input type="hidden" name="ref" value="{{ $active }}">
                    <div class="modal-content border-0 rounded-xl shadow-lg overflow-hidden">
                        <div class="modal-header border-b border-slate-100 px-4 py-3">
                            <h5 class="modal-title text-[14px] font-bold text-slate-800 m-0">Edit {{ $label }}</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body px-4 py-3 space-y-3">
                            @if(!empty($parentCol))
                                <div>
                                    <label class="block text-[10px] font-bold text-slate-500 uppercase tracking-wide mb-1">{{ $parentLabel ?? 'Parent' }}</label>
                                    <select name="parent_val" @if(!empty($parentRequired)) required @endif class="w-full bg-slate-50 border border-slate-200 rounded-lg px-3 py-2 text-[13px] text-slate-800 focus:border-primary focus:bg-white focus:ring-0 outline-none appearance-none">
                                        <option value="">{{ !empty($parentRequired) ? '— Pilih ' . ($parentLabel ?? 'parent') . ' —' : '— Tidak ada parent —' }}</option>
                                        @foreach($parentOptions ?? [] as $val => $labelOpt)
                                            <option value="{{ $val }}" {{ ($rowParent ?? '') == $val ? 'selected' : '' }}>{{ $labelOpt }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            @endif
                            <div>
                                <label class="block text-[10px] font-bold text-slate-500 uppercase tracking-wide mb-1">Nama <span class="text-red-500">*</span></label>
                                <input type="text" name="name" value="{{ $rowName }}" required
                                    class="w-full bg-slate-50 border border-slate-200 rounded-lg px-3 py-2 text-[13px] text-slate-800 focus:border-primary focus:bg-white focus:ring-0 outline-none">
                            </div>
                        </div>
                        <div class="modal-footer border-t border-slate-100 px-4 py-3 gap-2">
                            <button type="button" class="px-3 py-2 bg-slate-100 hover:bg-slate-200 text-slate-600 font-bold rounded-lg text-[12px] border-0" data-bs-dismiss="modal">Batal</button>
                            <button type="submit" class="px-4 py-2 bg-primary hover:bg-primary/90 text-white font-bold rounded-lg text-[12px] border-0">Simpan</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    @endforeach
@endif
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
