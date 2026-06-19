@extends('layouts.app')

@section('title', 'Edit Arus Mudik & Balik')
@section('page-title', 'Edit Arus Mudik & Balik')

@section('content')
<div class="max-w-[900px] mx-auto w-full space-y-4">
<div class="flex items-center gap-3">
    <a href="{{ route('arusmudik.index') }}" class="flex items-center gap-1.5 text-slate-500 hover:text-primary text-decoration-none text-label-md font-semibold transition-colors">
        <span class="material-symbols-outlined text-[18px]">arrow_back</span><span>Kembali</span>
    </a>
    <span class="text-slate-300">/</span>
    <span class="text-[13px] font-bold text-on-surface">Edit Arus Mudik &amp; Balik</span>
</div>
@if($errors->any())
    <div class="rounded-xl px-4 py-3 border border-red-200 bg-red-50 text-red-800">
        <ul class="mb-0 list-disc list-inside space-y-1">@foreach($errors->all() as $err)<li class="text-label-md">{{ $err }}</li>@endforeach</ul>
    </div>
@endif
<div class="bg-white rounded-2xl border border-slate-200/60 shadow-sm overflow-hidden">
    <div class="px-5 py-3.5 border-b border-slate-100 bg-slate-50/30 flex items-center gap-2">
        <span class="material-symbols-outlined text-primary text-[16px]" style="font-variation-settings: 'FILL' 1;">edit</span>
        <h3 class="text-[13px] font-bold text-slate-800 m-0">Edit Arus Mudik &amp; Balik</h3>
    </div>
    <div class="p-5">
        <form action="{{ route('arusmudik.update', $data->id_arusmudik) }}" method="POST">
            @csrf
                    @method('PUT')
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="id_profileposko" class="form-label">Profile Posko <span class="text-danger">*</span></label>
                                <select class="form-select @error('id_profileposko') is-invalid @enderror" id="id_profileposko" name="id_profileposko" required>
                                    <option value="">Pilih Profile Posko</option>
                                    @php
                                        $profilposko = \Illuminate\Support\Facades\DB::table('profilposko')->get();
                                    @endphp
                                    @foreach($profilposko as $posko)
                                        <option value="{{ $posko->id_profilposko }}" {{ old('id_profileposko', $data->id_profileposko) == $posko->id_profilposko ? 'selected' : '' }}>
                                            {{ $posko->nama_posko ?? $posko->id_profilposko }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('id_profileposko')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="mb-3">
                                <x-sbo-date-input id="tanggal" :value="$data->tanggal" required />
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-12">
                            <div class="mb-3">
                                <label for="statarus" class="form-label">Status Arus</label>
                                <textarea class="form-control @error('statarus') is-invalid @enderror" id="statarus" name="statarus" rows="3" placeholder="Masukkan status arus">{{ old('statarus', $data->statarus) }}</textarea>
                                @error('statarus')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                    
                    <div class="flex items-center justify-between pt-4 mt-4 border-t border-slate-100">
                        <a href="{{ route('arusmudik.index') }}" class="flex items-center gap-2 px-4 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-600 font-bold rounded-xl text-label-md transition-all text-decoration-none">
                            <span class="material-symbols-outlined text-[18px]">arrow_back</span><span>Kembali</span>
                        </a>
                        <button type="submit" class="flex items-center gap-2 px-5 py-2.5 bg-primary hover:bg-primary/90 text-white font-bold rounded-xl text-label-md transition-all shadow-sm border-0">
                            <span class="material-symbols-outlined text-[18px]">save</span><span>Update</span>
                        </button>
                    </div>
                </form>
    </div>
</div>
</div>
@endsection
