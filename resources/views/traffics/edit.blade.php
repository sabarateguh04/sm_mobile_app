@extends('layouts.app')
@section('title', $pageTitle)
@section('page-title', $pageTitle)

@section('content')
@php
    $backUrl = route('report.traffic-analytic.index');
@endphp
<div class="max-w-[960px] mx-auto w-full space-y-4">
    <section class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
        <div>
            <div class="flex items-center gap-3 flex-wrap">
                <a href="{{ $backUrl }}" class="flex items-center gap-1.5 text-slate-500 hover:text-primary text-decoration-none text-label-md font-semibold transition-colors">
                    <span class="material-symbols-outlined text-[18px]">arrow_back</span><span>Kembali</span>
                </a>
                <span class="text-slate-300">/</span>
                <span class="text-[13px] font-bold text-on-surface">{{ $pageTitle }}</span>
            </div>
        </div>
    </section>

    @if($errors->any())
        <div class="rounded-xl px-4 py-3 border border-red-200 bg-red-50 text-red-800">
            <ul class="mb-0 list-disc list-inside space-y-1">
                @foreach($errors->all() as $err)
                    <li class="text-label-md">{{ $err }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="bg-white rounded-2xl border border-slate-200/60 shadow-sm overflow-hidden">
        <div class="px-5 py-3.5 border-b border-slate-100 bg-slate-50/30 flex items-center gap-2">
            <span class="material-symbols-outlined text-primary text-[16px]" style="font-variation-settings: 'FILL' 1;">edit</span>
            <h3 class="text-[13px] font-bold text-slate-800 m-0">{{ $pageTitle }}</h3>
        </div>
        <div class="p-5">
            <form action="{{ route('traffics.update', $data->id_trafficdalkot) }}" method="POST" enctype="multipart/form-data">
                @csrf @method('PUT')
                @include('traffics._form')
                <div class="flex items-center justify-between gap-2 pt-4 mt-4 border-t border-slate-100 flex-wrap">
                    <a href="{{ $backUrl }}" class="flex items-center gap-2 px-4 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-600 font-bold rounded-xl text-label-md transition-all text-decoration-none">
                        <span class="material-symbols-outlined text-[18px]">close</span><span>Batal</span>
                    </a>
                    <button type="submit" class="flex items-center gap-2 px-5 py-2.5 bg-primary hover:bg-primary/90 text-white font-bold rounded-xl text-label-md transition-all shadow-sm border-0">
                        <span class="material-symbols-outlined text-[18px]" style="font-variation-settings: 'FILL' 1;">save</span><span>Simpan Perubahan</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
