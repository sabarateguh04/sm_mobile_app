@extends('layouts.app')
@section('title', 'Edit Peristiwa Menonjol')
@section('page-title', 'Edit Peristiwa Menonjol')

@section('content')
<div class="max-w-[900px] mx-auto w-full space-y-4">
    <div class="flex items-center gap-3">
        <a href="{{ route('ops_wanjol.index') }}" class="flex items-center gap-1.5 text-slate-500 hover:text-primary text-decoration-none text-label-md font-semibold transition-colors">
            <span class="material-symbols-outlined text-[18px]">arrow_back</span><span>Kembali</span>
        </a>
        <span class="text-slate-300">/</span>
        <span class="text-[13px] font-bold text-on-surface">Edit Peristiwa Menonjol</span>
    </div>
    @if($errors->any())
        <div class="rounded-xl px-4 py-3 border border-red-200 bg-red-50 text-red-800">
            <ul class="mb-0 list-disc list-inside space-y-1">@foreach($errors->all() as $err)<li class="text-label-md">{{ $err }}</li>@endforeach</ul>
        </div>
    @endif
    <div class="bg-white rounded-2xl border border-slate-200/60 shadow-sm overflow-hidden">
        <div class="px-5 py-3.5 border-b border-slate-100 bg-slate-50/30 flex items-center gap-2">
            <span class="material-symbols-outlined text-primary text-[16px]" style="font-variation-settings: 'FILL' 1;">edit</span>
            <h3 class="text-[13px] font-bold text-slate-800 m-0">Edit Peristiwa Menonjol</h3>
        </div>
        <div class="p-5">
            <form action="{{ route('ops_wanjol.update', $data->id_wanjol) }}" method="POST">
                @csrf @method('PUT')
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Profile Posko</label>
                        <select class="form-select @error('id_profileposko') is-invalid @enderror" name="id_profileposko">
                            <option value="">Pilih Profile Posko (opsional)</option>
                            @foreach(\Illuminate\Support\Facades\DB::table('profilposko')->get() as $posko)
                                <option value="{{ $posko->id_profilposko }}" {{ old('id_profileposko', $data->id_profileposko) == $posko->id_profilposko ? 'selected' : '' }}>
                                    {{ $posko->namaposko ?? $posko->id_profilposko }}
                                </option>
                            @endforeach
                        </select>
                        @error('id_profileposko')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-6 mb-3">
                        <x-sbo-date-input id="tanggal" :value="$data->tanggal" required />
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Lokasi</label>
                        <input type="text" class="form-control @error('lokasi') is-invalid @enderror" name="lokasi" value="{{ old('lokasi', $data->lokasi) }}" placeholder="Lokasi peristiwa">
                        @error('lokasi')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Status</label>
                        <input type="text" class="form-control @error('status') is-invalid @enderror" name="status" value="{{ old('status', $data->status) }}" placeholder="Status peristiwa">
                        @error('status')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label">Uraian Peristiwa</label>
                    <textarea class="form-control @error('uraian') is-invalid @enderror" name="uraian" rows="4">{{ old('uraian', $data->uraian) }}</textarea>
                    @error('uraian')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="flex items-center justify-between pt-4 mt-4 border-t border-slate-100">
                    <a href="{{ route('ops_wanjol.index') }}" class="flex items-center gap-2 px-4 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-600 font-bold rounded-xl text-label-md transition-all text-decoration-none">
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
