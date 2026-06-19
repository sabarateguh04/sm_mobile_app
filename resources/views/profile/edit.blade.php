@extends('layouts.app')
@section('title', $pageTitle)
@section('page-title', $pageTitle)

@section('content')
@php
    $avatarUrl = ! empty($user->fotoprof)
        ? (str_starts_with($user->fotoprof, 'http') ? $user->fotoprof : asset('storage/'.$user->fotoprof))
        : null;
@endphp

<div class="max-w-4xl mx-auto w-full space-y-4 user-form-page sbo-compact-select-native">

{{-- Header --}}
<section class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
    <div>
        <h2 class="text-[22px] font-extrabold text-on-surface tracking-tight leading-tight m-0">{{ $pageTitle }}</h2>
        <p class="text-[11px] text-slate-400 mt-0.5 m-0">Perbarui data diri, wilayah kerja, dan password. Username serta role tidak dapat diubah di sini.</p>
    </div>
    <a href="{{ route('profile.show') }}" class="flex items-center gap-1.5 px-3 py-2 bg-slate-100 hover:bg-slate-200 text-slate-600 font-bold rounded-lg text-[12px] transition-all text-decoration-none shrink-0">
        <span class="material-symbols-outlined text-[16px]">visibility</span>
        <span>Lihat profil</span>
    </a>
</section>

{{-- Ringkasan akun --}}
<section class="bg-white rounded-xl border border-slate-200/60 shadow-sm p-4 flex items-center gap-4">
    @if($avatarUrl)
        <img src="{{ $avatarUrl }}" alt="Foto profil" class="w-16 h-16 rounded-full object-cover border-2 border-slate-200 shadow-sm shrink-0">
    @else
        <div class="w-16 h-16 rounded-full bg-slate-100 border border-slate-200 flex items-center justify-center shrink-0">
            <span class="material-symbols-outlined text-slate-400 text-[32px]">person</span>
        </div>
    @endif
    <div class="min-w-0 flex-1">
        <p class="text-[15px] font-extrabold text-on-surface m-0 truncate">{{ $user->nama ?: $user->username }}</p>
        <p class="text-[11px] text-slate-500 m-0 truncate">{{ $user->username }} · {{ $user->email }}</p>
        @if($user->role)
            <span class="inline-flex items-center mt-1.5 px-2 py-0.5 rounded-full text-[10px] font-bold bg-primary/8 text-primary border border-primary/15">
                {{ $user->role->role_name }}
            </span>
        @endif
    </div>
</section>

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

@if($errors->any())
    <div class="rounded-lg px-3 py-2 border border-red-200 bg-red-50 text-red-800">
        <p class="text-[12px] font-bold m-0 mb-1">Periksa formulir:</p>
        <ul class="mb-0 list-disc list-inside space-y-0.5">
            @foreach($errors->all() as $err)<li class="text-[12px]">{{ $err }}</li>@endforeach
        </ul>
    </div>
@endif

<form action="{{ route('profile.update') }}" method="POST" enctype="multipart/form-data">
    @csrf
    @method('PUT')
    @include('admin.users._form', [
        'user' => $user,
        'myProfileMode' => true,
        'roles' => collect(),
        'daerahList' => $daerahList ?? collect(),
        'wilayahByDaerahJson' => $wilayahByDaerahJson ?? [],
        'wilayahPakaiMasterTerritori' => $wilayahPakaiMasterTerritori ?? false,
        'wilayahPakaiMasterKlDaerah' => $wilayahPakaiMasterKlDaerah ?? false,
        'userFormPakaiMasterKl' => $userFormPakaiMasterKl ?? false,
        'indukPemerintahanUseSelect' => $indukPemerintahanUseSelect ?? false,
        'orgsInduk' => $orgsInduk ?? collect(),
        'unitKerjaUseSelect' => $unitKerjaUseSelect ?? false,
        'suborgsUnitKerja' => $suborgsUnitKerja ?? collect(),
        'pemerintahanDaerahUseSelect' => $pemerintahanDaerahUseSelect ?? false,
        'pemerintahanDaerahResorts' => $pemerintahanDaerahResorts ?? collect(),
        'filterKepegawaianIndukPemerintahanDaerah' => $filterKepegawaianIndukPemerintahanDaerah ?? false,
        'organisasiDaerahList' => $organisasiDaerahList ?? collect(),
        'organisasiDaerahUseSelect' => $organisasiDaerahUseSelect ?? false,
        'filterKepegawaianOrganisasiDaerahUnit' => $filterKepegawaianOrganisasiDaerahUnit ?? false,
        'kepegawaianKlTigaTingkat' => $kepegawaianKlTigaTingkat ?? false,
        'kepegawaianKlEmpatTingkat' => $kepegawaianKlEmpatTingkat ?? false,
        'kepegawaianHierarchyJson' => $kepegawaianHierarchyJson ?? [],
    ])

    <div class="form-actions-sticky flex items-center justify-between gap-2 pt-3 mt-1 border-t border-slate-100">
        <a href="{{ route('profile.show') }}" class="flex items-center gap-1.5 px-3 py-2 bg-slate-100 hover:bg-slate-200 text-slate-600 font-bold rounded-lg text-[12px] transition-all text-decoration-none">
            <span class="material-symbols-outlined text-[16px]">arrow_back</span>
            <span>Kembali</span>
        </a>
        <button type="submit" class="flex items-center gap-1.5 px-4 py-2 bg-primary hover:bg-primary/90 text-white font-bold rounded-lg text-[12px] transition-all shadow-sm border-0">
            <span class="material-symbols-outlined text-[16px]">save</span>
            <span>Simpan profil</span>
        </button>
    </div>
</form>

</div>
@endsection
