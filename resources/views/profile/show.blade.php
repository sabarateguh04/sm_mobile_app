@extends('layouts.app')
@section('title', $pageTitle)
@section('page-title', $pageTitle)

@section('content')
@php
    $avatarUrl = ! empty($user->fotoprof)
        ? (str_starts_with($user->fotoprof, 'http') ? $user->fotoprof : asset('storage/'.$user->fotoprof))
        : 'https://ui-avatars.com/api/?name='.urlencode($user->nama ?? $user->username ?? 'U').'&background=0041c8&color=fff&size=256';
    $klForm = $userFormPakaiMasterKl ?? false;
    $organisasiLabel = $user->induk_pemerintahan
        ?: ($user->pemerintahan_daerah ?: ($user->organisasi_daerah ?: '—'));
@endphp

<div class="max-w-4xl mx-auto w-full space-y-4">

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

<div class="bg-white rounded-2xl border border-slate-200/60 shadow-sm overflow-hidden">
    <div class="px-5 py-4 border-b border-slate-100 bg-slate-50/30">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div class="flex flex-col sm:flex-row sm:items-center gap-4 min-w-0">
                <img src="{{ $avatarUrl }}" alt="{{ $user->nama ?? 'Profil' }}"
                     class="w-20 h-20 rounded-2xl object-cover border border-slate-200 shadow-sm shrink-0"/>
                <div class="flex-1 min-w-0">
                    <h2 class="text-[20px] font-extrabold text-on-surface tracking-tight leading-tight m-0 truncate">{{ $user->nama ?: $user->username }}</h2>
                    <p class="text-[12px] text-slate-500 m-0 mt-0.5 truncate">{{ $user->username }} · {{ $user->email }}</p>
                    @if($user->role)
                        <span class="inline-flex items-center mt-1.5 px-2 py-0.5 rounded-full text-[10px] font-bold bg-primary/8 text-primary border border-primary/15">
                            {{ $user->role->role_name }}
                        </span>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <div class="p-5 space-y-6">
        <div>
            <h3 class="text-[11px] font-bold text-slate-400 uppercase tracking-widest m-0 mb-3">Wilayah Kerja</h3>
            <dl class="grid grid-cols-1 sm:grid-cols-2 gap-x-6 gap-y-4 mb-0">
                <div>
                    <dt class="text-[11px] font-bold text-slate-500 uppercase tracking-wide mb-0.5">Polda</dt>
                    <dd class="text-[13px] font-medium m-0" style="color:#D8A808;">{{ strtoupper($user->daerah ?: '—') }}</dd>
                </div>
                <div>
                    <dt class="text-[11px] font-bold text-slate-500 uppercase tracking-wide mb-0.5">Polres</dt>
                    <dd class="text-[13px] font-medium m-0" style="color:#D8A808;">{{ strtoupper(trim(str_ireplace(['KABUPATEN ', 'KOTA ', 'KAB. '], '', $user->wilayah ?: '—'))) }}</dd>
                </div>
            </dl>
        </div>

        <div>
            <h3 class="text-[11px] font-bold text-slate-400 uppercase tracking-widest m-0 mb-3">Identitas</h3>
            <dl class="grid grid-cols-1 sm:grid-cols-2 gap-x-6 gap-y-4 mb-0">
                <div>
                    <dt class="text-[11px] font-bold text-slate-500 uppercase tracking-wide mb-0.5">Nama</dt>
                    <dd class="text-[13px] text-slate-800 font-medium m-0">{{ $user->nama ?: '—' }}</dd>
                </div>
                <div>
                    <dt class="text-[11px] font-bold text-slate-500 uppercase tracking-wide mb-0.5">Username</dt>
                    <dd class="text-[13px] text-slate-800 font-medium m-0">{{ $user->username }}</dd>
                </div>
                <div>
                    <dt class="text-[11px] font-bold text-slate-500 uppercase tracking-wide mb-0.5">Email</dt>
                    <dd class="text-[13px] text-slate-800 font-medium m-0 break-all">{{ $user->email ?: '—' }}</dd>
                </div>
                <div>
                    <dt class="text-[11px] font-bold text-slate-500 uppercase tracking-wide mb-0.5">No. Telepon</dt>
                    <dd class="text-[13px] text-slate-800 font-medium m-0">{{ $user->notelp ?: '—' }}</dd>
                </div>
                <div>
                    <dt class="text-[11px] font-bold text-slate-500 uppercase tracking-wide mb-0.5">NRP / NIP / NIK</dt>
                    <dd class="text-[13px] text-slate-800 font-medium m-0">{{ $user->nrp_nip_nik ?: '—' }}</dd>
                </div>
                <div>
                    <dt class="text-[11px] font-bold text-slate-500 uppercase tracking-wide mb-0.5">Kategori</dt>
                    <dd class="text-[13px] text-slate-800 font-medium m-0">
                        @if($user->kategori_user === 'organik')
                            Organik
                        @elseif($user->kategori_user === 'non_organik')
                            Non-Organik
                        @else
                            —
                        @endif
                    </dd>
                </div>
            </dl>
        </div>

        <div>
            <h3 class="text-[11px] font-bold text-slate-400 uppercase tracking-widest m-0 mb-3">Organisasi</h3>
            <dl class="grid grid-cols-1 sm:grid-cols-2 gap-x-6 gap-y-4 mb-0">
                <div>
                    <dt class="text-[11px] font-bold text-slate-500 uppercase tracking-wide mb-0.5">Instansi</dt>
                    <dd class="text-[13px] text-slate-800 font-medium m-0">{{ $user->induk_pemerintahan ?: '—' }}</dd>
                </div>
                @if($klForm || !empty($user->pemerintahan_daerah))
                    <div>
                        <dt class="text-[11px] font-bold text-slate-500 uppercase tracking-wide mb-0.5">Organisasi</dt>
                        <dd class="text-[13px] text-slate-800 font-medium m-0">{{ $user->pemerintahan_daerah ?: '—' }}</dd>
                    </div>
                @endif
                @if(($kepegawaianKlEmpatTingkat ?? false) || !empty($user->organisasi_daerah))
                    <div>
                        <dt class="text-[11px] font-bold text-slate-500 uppercase tracking-wide mb-0.5">Sub organisasi</dt>
                        <dd class="text-[13px] text-slate-800 font-medium m-0">{{ $user->organisasi_daerah ?: '—' }}</dd>
                    </div>
                @endif
                <div>
                    <dt class="text-[11px] font-bold text-slate-500 uppercase tracking-wide mb-0.5">Unit Kerja</dt>
                    <dd class="text-[13px] text-slate-800 font-medium m-0">{{ $user->unit_kerja ?: '—' }}</dd>
                </div>
                @unless($klForm)
                    <div class="sm:col-span-2">
                        <dt class="text-[11px] font-bold text-slate-500 uppercase tracking-wide mb-0.5">Ringkasan</dt>
                        <dd class="text-[13px] text-slate-800 font-medium m-0">{{ $organisasiLabel }}</dd>
                    </div>
                @endunless
            </dl>
        </div>

        <div class="flex items-center justify-between pt-4 border-t border-slate-100 flex-wrap gap-2">
            <a href="{{ route('home') }}" class="flex items-center gap-2 px-4 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-600 font-bold rounded-xl text-[12px] transition-all text-decoration-none">
                <span class="material-symbols-outlined text-[18px]">arrow_back</span>
                <span>Kembali</span>
            </a>
            <a href="{{ route('profile.edit') }}" class="flex items-center gap-2 px-4 py-2.5 bg-primary hover:bg-primary/90 text-white font-bold rounded-xl text-[12px] transition-all text-decoration-none shadow-sm">
                <span class="material-symbols-outlined text-[18px]">edit</span>
                <span>Edit Profil</span>
            </a>
        </div>
    </div>
</div>

</div>
@endsection
