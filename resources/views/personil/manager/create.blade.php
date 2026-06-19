@extends('layouts.app')
@section('title', $pageTitle)
@section('page-title', $pageTitle)

@section('content')
@php
    $wilayahKerja = trim(($defaultDaerah ?? '').(! empty($defaultWilayah ?? '') ? ' / '.$defaultWilayah : ''));
    $subtitle = $wilayahKerja !== ''
        ? 'Wilayah kerja: '.$wilayahKerja
        : 'Form pendaftaran operator baru — Smart Management';
@endphp

<div class="max-w-[1400px] mx-auto w-full space-y-3">

    {{-- Hero (selaras index Kuat Personil BO) --}}
    <section class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
        <div>
            <a href="{{ route('personil.manager.index') }}" class="inline-flex items-center gap-1 text-slate-500 hover:text-primary text-decoration-none text-[11px] font-semibold mb-1 transition-colors">
                <span class="material-symbols-outlined text-[16px]">arrow_back</span>
                <span>Kembali ke daftar</span>
            </a>
            <h2 class="text-[22px] font-headline-lg font-extrabold text-on-surface tracking-tight leading-tight m-0">{{ $pageTitle }}</h2>
            <p class="text-[11px] text-slate-400 mt-0.5 m-0">{{ $subtitle }}</p>
        </div>
    </section>

    @if($errors->any())
        <div class="rounded-xl px-4 py-3 border border-red-200 bg-red-50 text-red-800">
            <div class="flex items-center gap-2 mb-2">
                <span class="material-symbols-outlined text-[18px] shrink-0">error</span>
                <span class="text-label-md font-bold">Periksa form berikut:</span>
            </div>
            <ul class="mb-0 list-disc list-inside space-y-1">
                @foreach($errors->all() as $err)<li class="text-label-md">{{ $err }}</li>@endforeach
            </ul>
        </div>
    @endif

    <div class="bg-white rounded-2xl border border-slate-200/60 shadow-sm overflow-hidden">
        <div class="px-4 py-2.5 border-b border-slate-100 bg-slate-50/30 flex items-center gap-2">
            <span class="material-symbols-outlined text-primary text-[18px]" style="font-variation-settings: 'FILL' 1;">person_add</span>
            <h3 class="text-[13px] font-bold text-slate-800 m-0">Data Operator Baru</h3>
        </div>
        <div class="px-4 py-3">
            <form action="{{ route('personil.manager.store') }}" method="POST" enctype="multipart/form-data" class="user-form-page personil-operator-form">
                @csrf
                @include('personil.manager._form', ['isEdit' => false])
                <div class="flex flex-col-reverse sm:flex-row sm:items-center sm:justify-between gap-2 pt-3 mt-1 border-t border-slate-100">
                    <a href="{{ route('personil.manager.index') }}" class="flex items-center justify-center gap-2 px-4 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-600 font-bold rounded-xl text-label-md transition-all text-decoration-none">
                        <span class="material-symbols-outlined text-[18px]">close</span>
                        <span>Batal</span>
                    </a>
                    <button type="submit" class="flex items-center justify-center gap-2 px-5 py-2.5 bg-primary hover:bg-primary/90 text-on-primary font-bold rounded-xl text-label-md transition-all shadow-lg shadow-primary/25 border-0">
                        <span class="material-symbols-outlined text-[18px]" style="font-variation-settings: 'FILL' 1;">save</span>
                        <span>Simpan Operator</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
