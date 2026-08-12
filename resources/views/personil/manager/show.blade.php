@extends('layouts.app')
@section('title', $pageTitle)
@section('page-title', $pageTitle)

@section('content')
@php
    $isOperatorViewer = $sboOperatorOwnership ?? \App\Support\RoleMutationPolicy::isOperatorRole(auth()->user());
    $status = strtolower((string) ($data->account_status ?? ''));
    $statusUi = match ($status) {
        'active' => ['label' => 'Aktif', 'wrap' => 'bg-secondary-container text-secondary border-secondary/10'],
        'pending' => ['label' => 'Pending', 'wrap' => 'bg-amber-50 text-amber-700 border-amber-200/60'],
        'blocked' => ['label' => 'Diblokir', 'wrap' => 'bg-rose-50 text-rose-700 border-rose-200/60'],
        default => ['label' => $data->account_status ?? '-', 'wrap' => 'bg-slate-100 text-slate-600 border-slate-200'],
    };
    $avatarUrl = !empty($data->fotoprof)
        ? (str_starts_with($data->fotoprof, 'http') ? $data->fotoprof : asset('storage/'.$data->fotoprof))
        : 'https://ui-avatars.com/api/?name='.urlencode($data->nama ?? $data->username ?? 'OP').'&background=0041c8&color=fff&size=256';
    $organisasiLabel = $data->induk_pemerintahan
        ?: ($data->pemerintahan_daerah ?: ($data->organisasi_daerah ?: '-'));
    $subOrgLabel = $data->unit_kerja ?: '-';
@endphp

<div class="max-w-[900px] mx-auto w-full space-y-4">
    <div class="flex items-center gap-3 flex-wrap">
        <a href="{{ route('personil.manager.index') }}" class="flex items-center gap-1.5 text-slate-500 hover:text-primary text-decoration-none text-label-md font-semibold transition-colors">
            <span class="material-symbols-outlined text-[18px]">arrow_back</span><span>Kembali</span>
        </a>
        <span class="text-slate-300">/</span>
        <span class="text-[13px] font-bold text-on-surface">{{ $data->nama ?? 'Detail Operator' }}</span>
    </div>

    <div class="bg-white rounded-2xl border border-slate-200/60 shadow-sm overflow-hidden">
        <div class="px-5 py-4 border-b border-slate-100 bg-slate-50/30">
            <div class="flex flex-col sm:flex-row sm:items-center gap-4">
                <img src="{{ $avatarUrl }}" alt="{{ $data->nama ?? 'Operator' }}"
                     class="w-20 h-20 rounded-2xl object-cover border border-slate-200 shadow-sm shrink-0"/>
                <div class="flex-1 min-w-0">
                    <div class="flex flex-wrap items-center gap-2 mb-1">
                        <h2 class="text-[18px] font-extrabold text-slate-800 m-0 truncate">{{ $data->nama ?? '-' }}</h2>
                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold border {{ $statusUi['wrap'] }}">{{ $statusUi['label'] }}</span>
                        @if(($data->kategori_user ?? '') === 'organik')
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200/60">Organik</span>
                        @elseif(($data->kategori_user ?? '') === 'non_organik')
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-50 text-amber-700 border border-amber-200/60">Non-Organik</span>
                        @endif
                    </div>
                    <p class="text-[12px] text-slate-500 m-0">
                        <code class="bg-slate-100 px-1.5 py-0.5 rounded text-[11px]">{{ $data->username ?? '-' }}</code>
                        @if(!empty($data->role_name))
                            <span class="mx-1 text-slate-300">·</span>
                            <span class="font-semibold text-primary">{{ $data->role_name }}</span>
                        @endif
                    </p>
                </div>
            </div>
        </div>

        <div class="p-5">
            <dl class="grid grid-cols-[140px_1fr] md:grid-cols-[160px_1fr] gap-x-4 gap-y-3 mb-0 items-center">
                <dt class="text-[11px] font-bold text-slate-500 uppercase tracking-wide">Polda</dt>
                <dd class="text-[13px] font-bold m-0" style="color:#D8A808;">{{ strtoupper($data->daerah ?? '—') }}</dd>
                
                <dt class="text-[11px] font-bold text-slate-500 uppercase tracking-wide">Polres</dt>
                <dd class="text-[13px] text-slate-800 font-medium m-0">{{ strtoupper(trim(str_ireplace(['KABUPATEN ', 'KOTA ', 'KAB. '], '', $data->wilayah ?? '—'))) }}</dd>

                <dt class="text-[11px] font-bold text-slate-500 uppercase tracking-wide">Organisasi</dt>
                <dd class="text-[13px] text-slate-800 font-medium m-0">{{ $organisasiLabel }}</dd>
                
                <dt class="text-[11px] font-bold text-slate-500 uppercase tracking-wide">Sub Org</dt>
                <dd class="text-[13px] text-slate-800 font-medium m-0">{{ $subOrgLabel }}</dd>
                
                <dt class="text-[11px] font-bold text-slate-500 uppercase tracking-wide">Telepon</dt>
                <dd class="text-[13px] text-slate-800 font-medium m-0">{{ $data->notelp ?: '—' }}</dd>
                
                <dt class="text-[11px] font-bold text-slate-500 uppercase tracking-wide">Email</dt>
                <dd class="text-[13px] text-slate-800 font-medium m-0 break-all">{{ $data->email ?? '—' }}</dd>

                @if(!$isOperatorViewer)
                    <dt class="text-[11px] font-bold text-slate-500 uppercase tracking-wide">NRP / NIP / NIK</dt>
                    <dd class="text-[13px] text-slate-800 font-medium m-0">{{ $data->nrp_nip_nik ?? '—' }}</dd>
                    
                    <dt class="text-[11px] font-bold text-slate-500 uppercase tracking-wide self-start mt-0.5">Kategori</dt>
                    <dd class="text-[13px] text-slate-800 font-medium m-0">
                        @if(($data->kategori_user ?? '') === 'organik')
                            Organik
                        @elseif(($data->kategori_user ?? '') === 'non_organik')
                            Non-Organik
                        @else
                            —
                        @endif
                    </dd>
                @endif

                @if(!$isOperatorViewer || !empty($data->induk_pemerintahan))
                    <dt class="text-[11px] font-bold text-slate-500 uppercase tracking-wide">Instansi</dt>
                    <dd class="text-[13px] text-slate-800 font-medium m-0">{{ $data->induk_pemerintahan ?: '—' }}</dd>
                @endif
                
                @if(!$isOperatorViewer && !empty($data->pemerintahan_daerah))
                    <dt class="text-[11px] font-bold text-slate-500 uppercase tracking-wide">Organisasi</dt>
                    <dd class="text-[13px] text-slate-800 font-medium m-0">{{ $data->pemerintahan_daerah }}</dd>
                @endif
                
                @if(!$isOperatorViewer && !empty($data->organisasi_daerah))
                    <dt class="text-[11px] font-bold text-slate-500 uppercase tracking-wide">Sub organisasi</dt>
                    <dd class="text-[13px] text-slate-800 font-medium m-0">{{ $data->organisasi_daerah }}</dd>
                @endif
            </dl>

            <div class="flex items-center justify-between pt-5 mt-5 border-t border-slate-100 flex-wrap gap-2">
                <a href="{{ route('personil.manager.index') }}" class="flex items-center gap-2 px-4 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-600 font-bold rounded-xl text-label-md transition-all text-decoration-none">
                    <span class="material-symbols-outlined text-[18px]">arrow_back</span><span>Kembali</span>
                </a>
                <div class="flex items-center gap-2 flex-wrap">
                    @include('_partials.show_export_dropdown', ['exportRouteName' => 'personil.manager.export', 'rowId' => $data->id_usm])
                    @if(!empty($adminUsersEditUrl) && !in_array(\App\Helpers\MenuHelper::matrixRoleIdForUser(auth()->user()), [2, 3, 4]))
                        <a href="{{ $adminUsersEditUrl }}" class="flex items-center gap-2 px-4 py-2.5 bg-primary/10 hover:bg-primary/15 text-primary border border-primary/20 font-bold rounded-xl text-label-md transition-all text-decoration-none">
                            <span class="material-symbols-outlined text-[18px]">manage_accounts</span><span>Edit di Manajemen User</span>
                        </a>
                    @endif
                    @canMutatePersonilRow($data)
                        <a href="{{ route('personil.manager.edit', $data->id_usm) }}" class="flex items-center gap-2 px-4 py-2.5 bg-amber-50 hover:bg-amber-100 text-amber-700 border border-amber-200 font-bold rounded-xl text-label-md transition-all text-decoration-none">
                            <span class="material-symbols-outlined text-[18px]">edit</span><span>Edit</span>
                        </a>
                        <form action="{{ route('personil.manager.destroy', $data->id_usm) }}" method="POST" class="inline m-0" onsubmit="return confirm(@json('Yakin hapus operator '.($data->nama ?: $data->username).'?'));">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="flex items-center gap-2 px-4 py-2.5 bg-red-50 hover:bg-red-100 text-red-700 border border-red-200 font-bold rounded-xl text-label-md transition-all">
                                <span class="material-symbols-outlined text-[18px]">delete</span><span>Hapus</span>
                            </button>
                        </form>
                    @endcanMutatePersonilRow
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
