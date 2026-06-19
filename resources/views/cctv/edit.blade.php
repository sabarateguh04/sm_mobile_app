@extends('layouts.app')
@section('title', $pageTitle)
@section('page-title', $pageTitle)

@section('content')
<div class="w-full max-w-3xl mx-auto space-y-3">
    <div class="flex items-center gap-2 flex-wrap">
        <a href="{{ route('patrolicctv.index') }}" class="flex items-center gap-1 text-slate-500 hover:text-primary text-decoration-none text-[12px] font-semibold transition-colors">
            <span class="material-symbols-outlined text-[16px]">arrow_back</span><span>Kembali</span>
        </a>
        <span class="text-slate-300">/</span>
        <span class="text-[12px] font-bold text-on-surface">Edit #{{ $data->{$primaryKey} }}</span>
    </div>

    @if($errors->any())
        <div class="rounded-lg px-3 py-2 border border-red-200 bg-red-50 text-red-800">
            <ul class="mb-0 list-disc list-inside space-y-0.5">@foreach($errors->all() as $err)<li class="text-[12px]">{{ $err }}</li>@endforeach</ul>
        </div>
    @endif

    <div class="bg-white rounded-xl border border-slate-200/60 shadow-sm overflow-hidden">
        <div class="px-4 py-2.5 border-b border-slate-100 bg-slate-50/40 flex items-center gap-2">
            <span class="material-symbols-outlined text-amber-500 text-[15px]" style="font-variation-settings: 'FILL' 1;">edit</span>
            <h3 class="text-[12px] font-bold text-slate-800 m-0">{{ $pageTitle }} #{{ $data->{$primaryKey} }}</h3>
        </div>
        <div class="p-3 sm:p-4">
            <form action="{{ route('patrolicctv.update', $data->{$primaryKey}) }}" method="POST" enctype="multipart/form-data">@csrf @method('PUT')
                @include('cctv._form', ['patrolCols' => $patrolCols ?? []])
                <div class="flex items-center justify-between gap-2 pt-3 mt-3 border-t border-slate-100">
                    <a href="{{ route('patrolicctv.index') }}" class="flex items-center gap-1.5 px-3 py-2 bg-slate-100 hover:bg-slate-200 text-slate-600 font-bold rounded-lg text-[12px] transition-all text-decoration-none">
                        <span class="material-symbols-outlined text-[16px]">arrow_back</span><span>Batal</span>
                    </a>
                    <button type="submit" class="flex items-center gap-1.5 px-4 py-2 bg-primary hover:bg-primary/90 text-white font-bold rounded-lg text-[12px] transition-all shadow-sm border-0">
                        <span class="material-symbols-outlined text-[16px]">save</span><span>Perbarui</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
