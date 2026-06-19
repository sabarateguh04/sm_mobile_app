@extends('layouts.app')
@section('title', $pageTitle)
@section('page-title', $pageTitle)

@section('content')
@php
    $isOperatorViewer = $sboOperatorOwnership ?? \App\Support\RoleMutationPolicy::isOperatorRole(auth()->user());
    $stats = $personilStats ?? ['total' => $data->total(), 'active' => 0, 'pending' => 0, 'blocked' => 0];
    $activePct = $stats['total'] > 0 ? round(($stats['active'] / $stats['total']) * 100) : 0;
    $subtitle = $listingTerritoryHint
        ?? (!empty($actorIndukOrganisasi) ? 'Induk organisasi: '.$actorIndukOrganisasi : 'Operator per Polda / Polres — Smart Management');
@endphp

<div class="max-w-[1400px] mx-auto w-full space-y-4">

<!-- Hero -->
<section class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
    <div>
        <h2 class="text-[22px] font-headline-lg font-extrabold text-on-surface tracking-tight leading-tight m-0">{{ $pageTitle }}</h2>
        <p class="text-[11px] text-slate-400 mt-0.5 m-0">{{ $subtitle }}</p>
    </div>
    @if(!\App\Support\RoleMutationPolicy::isOperatorRole(auth()->user()))
    <a href="{{ route('personil.manager.create') }}" class="flex items-center justify-center gap-2 bg-primary text-on-primary px-5 py-2.5 rounded-xl font-bold text-label-md shadow-lg shadow-primary/25 hover:shadow-xl hover:-translate-y-0.5 active:translate-y-0 transition-all text-decoration-none shrink-0">
        <span class="material-symbols-outlined text-[20px]" style="font-variation-settings: 'FILL' 1;">person_add</span>
        <span>Tambah Operator</span>
    </a>
    @endif
</section>


<!-- Stats -->
<section class="grid grid-cols-2 lg:grid-cols-4 gap-3">
    @foreach([
        ['icon'=>'groups',        'bg'=>'bg-primary/10',  'color'=>'text-primary',   'label'=>'Total',    'val'=>$stats['total'],   'num_class'=>'text-on-surface'],
        ['icon'=>'verified_user', 'bg'=>'bg-secondary/10','color'=>'text-secondary', 'label'=>'Aktif',    'val'=>$stats['active'],  'num_class'=>'text-on-surface'],
        ['icon'=>'hourglass_top', 'bg'=>'bg-amber-100',   'color'=>'text-amber-600', 'label'=>'Pending',  'val'=>$stats['pending'], 'num_class'=>'text-on-surface'],
        ['icon'=>'block',         'bg'=>'bg-rose-100',    'color'=>'text-rose-600',  'label'=>'Diblokir', 'val'=>$stats['blocked'], 'num_class'=>$stats['blocked']>0?'text-rose-600':'text-on-surface'],
    ] as $s)
    <div class="stat-card bg-white rounded-xl border border-slate-200/60 shadow-sm px-4 py-3 flex items-center gap-3">
        <div class="w-8 h-8 {{ $s['bg'] }} {{ $s['color'] }} rounded-lg flex items-center justify-center shrink-0">
            <span class="material-symbols-outlined text-[18px]" style="font-variation-settings: 'FILL' 1;">{{ $s['icon'] }}</span>
        </div>
        <div>
            <p class="text-[10px] text-slate-400 font-semibold leading-none mb-1">{{ $s['label'] }}</p>
            <p class="text-[20px] font-extrabold {{ $s['num_class'] }} leading-none m-0">{{ number_format($s['val'], 0, ',', '.') }}</p>
        </div>
    </div>
    @endforeach
</section>

@if(session('success'))
    <div class="bg-secondary-container text-on-secondary-container border border-secondary/20 rounded-2xl p-4 flex justify-between items-center shadow-sm">
        <div class="flex items-center gap-3">
            <span class="material-symbols-outlined" style="font-variation-settings: 'FILL' 1;">check_circle</span>
            <span class="font-medium text-sm">{{ session('success') }}</span>
        </div>
        <button type="button" class="text-on-secondary-container/50 hover:text-on-secondary-container bg-transparent border-0" onclick="this.parentElement.remove()">
            <span class="material-symbols-outlined">close</span>
        </button>
    </div>
@endif

@if(!empty($listingTerritoryBlocked))
    <div class="rounded-xl px-4 py-2.5 flex items-center gap-2 border border-amber-200 bg-amber-50 text-amber-800 text-label-md font-medium">
        <span class="material-symbols-outlined text-[18px] text-amber-500 shrink-0">warning</span>
        {{ $listingTerritoryHint }}
    </div>
@endif

@include('personil.manager._filters')

<!-- Table -->
<section class="bg-white rounded-xl border border-slate-200/60 overflow-hidden shadow-sm">
    <div class="px-4 py-3 border-b border-slate-100 flex flex-wrap items-center justify-between gap-2">
        <div>
            <h3 class="text-[11px] font-bold uppercase tracking-wider text-slate-700 m-0">Daftar Operator BO</h3>
        </div>
        <div class="flex gap-2">
            @include('_partials.export_dropdown', ['routeName' => 'personil.manager.export', 'query' => request()->query()])
        </div>
    </div>

    @if($data->count())
        <div class="overflow-x-auto custom-scrollbar">
            <table class="w-full text-left min-w-[980px]">
                <thead>
                    <tr class="bg-slate-50/50">
                        <th class="px-4 py-2 text-[10px] font-bold text-slate-400 uppercase tracking-widest border-b border-slate-100 w-10">#</th>
                        <th class="px-3 py-2 text-[10px] font-bold text-slate-400 uppercase tracking-widest border-b border-slate-100 w-14">Foto</th>
                        <th class="px-3 py-2 text-[10px] font-bold text-slate-400 uppercase tracking-widest border-b border-slate-100">Nama</th>
                        <th class="px-3 py-2 text-[10px] font-bold text-slate-400 uppercase tracking-widest border-b border-slate-100">Kategori</th>
                        <th class="px-3 py-2 text-[10px] font-bold text-slate-400 uppercase tracking-widest border-b border-slate-100">Organisasi</th>
                        <th class="px-3 py-2 text-[10px] font-bold text-slate-400 uppercase tracking-widest border-b border-slate-100">Sub Org</th>
                        <th class="px-3 py-2 text-[10px] font-bold text-slate-400 uppercase tracking-widest border-b border-slate-100">Telp</th>
                        <th class="px-3 py-2 text-[10px] font-bold text-slate-400 uppercase tracking-widest border-b border-slate-100 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach($data as $item)
                        @php
                            $avatarUrl = !empty($item->fotoprof)
                                ? (str_starts_with($item->fotoprof, 'http') ? $item->fotoprof : asset('storage/'.$item->fotoprof))
                                : 'https://ui-avatars.com/api/?name='.urlencode($item->nama ?? $item->username ?? 'OP').'&background=0041c8&color=fff&size=80';
                            $organisasiLabel = $item->induk_pemerintahan
                                ?: ($item->pemerintahan_daerah ?: ($item->organisasi_daerah ?: '-'));
                            $subOrgLabel = $item->unit_kerja ?: '-';
                        @endphp
                        <tr class="hover:bg-slate-50 group">
                            <td class="px-4 py-2.5 text-label-sm text-slate-400">{{ $loop->iteration + ($data->currentPage() - 1) * $data->perPage() }}</td>
                            <td class="px-3 py-2.5">
                                <img alt="" class="w-9 h-9 rounded-lg object-cover border border-slate-200" src="{{ $avatarUrl }}"/>
                            </td>
                            <td class="px-3 py-2.5">
                                <p class="text-[12px] font-bold text-slate-800 leading-tight m-0 truncate max-w-[200px]">{{ $item->nama ?? '-' }}</p>
                                @if(!empty($item->username))
                                    <p class="text-[10px] text-slate-400 m-0 truncate"><code class="bg-slate-100 px-1 rounded">{{ $item->username }}</code></p>
                                @endif
                            </td>
                            <td class="px-3 py-2.5">
                                @if(($item->kategori_user ?? '') === 'organik')
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200/60">Organik</span>
                                @elseif(($item->kategori_user ?? '') === 'non_organik')
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-50 text-amber-700 border border-amber-200/60">Non-Organik</span>
                                @else
                                    <span class="text-[11px] text-slate-400">-</span>
                                @endif
                            </td>
                            <td class="px-3 py-2.5">
                                <span class="text-[11px] text-slate-700 font-medium truncate block max-w-[180px]">{{ $organisasiLabel }}</span>
                            </td>
                            <td class="px-3 py-2.5">
                                <span class="text-[11px] text-slate-600 truncate block max-w-[180px]">{{ $subOrgLabel }}</span>
                            </td>
                            <td class="px-3 py-2.5">
                                <span class="text-[11px] text-slate-700 font-medium">{{ $item->notelp ?: '-' }}</span>
                            </td>
                            <td class="px-3 py-2.5 text-right">
                                <div class="flex items-center justify-end gap-1 opacity-100 lg:opacity-0 group-hover:opacity-100 transition-opacity">
                                    <a href="{{ route('personil.manager.show', $item->id_usm) }}" class="w-8 h-8 flex items-center justify-center text-primary hover:bg-primary/10 rounded-lg transition-colors text-decoration-none" title="Detail">
                                        <span class="material-symbols-outlined text-[20px]">visibility</span>
                                    </a>
                                    @canMutatePersonilRow($item)
                                        <a href="{{ route('personil.manager.edit', $item->id_usm) }}" class="w-8 h-8 flex items-center justify-center text-slate-400 hover:text-slate-600 hover:bg-slate-100 rounded-lg transition-colors text-decoration-none" title="Edit">
                                            <span class="material-symbols-outlined text-[20px]">edit</span>
                                        </a>
                                        <form action="{{ route('personil.manager.destroy', $item->id_usm) }}" method="POST" class="inline m-0" onsubmit="return confirm(@json('Yakin hapus operator '.($item->nama ?: $item->username).'?'));">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="w-8 h-8 flex items-center justify-center text-slate-400 hover:text-error hover:bg-error-container/20 rounded-lg transition-colors border-0 bg-transparent p-0" title="Hapus">
                                                <span class="material-symbols-outlined text-[20px]">delete</span>
                                            </button>
                                        </form>
                                    @else
                                        @if(!empty($canEditViaAdminUsers) && ($adminUserEditUrls[$item->id_usm] ?? null))
                                            <a href="{{ $adminUserEditUrls[$item->id_usm] }}" class="w-8 h-8 flex items-center justify-center text-primary hover:bg-primary/10 rounded-lg transition-colors text-decoration-none" title="Edit (Manajemen User)">
                                                <span class="material-symbols-outlined text-[20px]">manage_accounts</span>
                                            </a>
                                        @endif
                                    @endcanMutatePersonilRow
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
                <span class="material-symbols-outlined text-[26px]">group_off</span>
            </div>
            <p class="text-label-md text-slate-500 font-medium max-w-md mx-auto">
                @if(!empty($listingTerritoryBlocked))
                    Daftar personil tidak dapat ditampilkan. Lengkapi induk organisasi atau Polda wilayah kerja di profil organisasi Anda.
                @else
                    Belum ada operator
                    @if(!empty($actorIndukOrganisasi))
                        di induk organisasi <strong class="text-slate-700">{{ $actorIndukOrganisasi }}</strong>
                    @elseif(($userLevel ?? null) === 'kab_kota')
                        di Polres kerja Anda.
                    @elseif(($userLevel ?? null) === 'provinsi')
                        di Polda kerja Anda.
                    @else
                        yang terdaftar.
                    @endif
                @endif
            </p>
        </div>
    @endif
</section>

</div>
@endsection
