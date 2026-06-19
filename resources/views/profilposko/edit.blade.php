@extends('layouts.app')

@section('title', 'Edit Profile Posko')
@section('page-title', 'Edit Profile Posko')

@section('content')
<div class="max-w-[900px] mx-auto w-full space-y-4">
    <div class="flex items-center gap-3">
        <a href="{{ route('profilposko.index') }}" class="flex items-center gap-1.5 text-slate-500 hover:text-primary text-decoration-none text-label-md font-semibold transition-colors">
            <span class="material-symbols-outlined text-[18px]">arrow_back</span><span>Kembali</span>
        </a>
        <span class="text-slate-300">/</span>
        <span class="text-[13px] font-bold text-on-surface">Edit Profile Posko</span>
    </div>
    @if($errors->any())
        <div class="rounded-xl px-4 py-3 border border-red-200 bg-red-50 text-red-800">
            <ul class="mb-0 list-disc list-inside space-y-1">@foreach($errors->all() as $err)<li class="text-label-md">{{ $err }}</li>@endforeach</ul>
        </div>
    @endif
    <div class="bg-white rounded-2xl border border-slate-200/60 shadow-sm overflow-hidden">
        <div class="px-5 py-3.5 border-b border-slate-100 bg-slate-50/30 flex items-center gap-2">
            <span class="material-symbols-outlined text-amber-500 text-[16px]" style="font-variation-settings: 'FILL' 1;">edit</span>
            <h3 class="text-[13px] font-bold text-slate-800 m-0">Form Edit Profile Posko</h3>
        </div>
        <div class="p-5">
            <form action="{{ route('profilposko.update', $data->id_profilposko) }}" method="POST">
                @csrf @method('PUT')
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="namaposko" class="form-label">Nama Posko</label>
                        <input type="text" class="form-control @error('namaposko') is-invalid @enderror" id="namaposko" name="namaposko" value="{{ old('namaposko', $data->namaposko) }}">
                        @error('namaposko')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="katops" class="form-label">Kategori Ops</label>
                        <input type="text" class="form-control @error('katops') is-invalid @enderror" id="katops" name="katops" value="{{ old('katops', $data->katops) }}">
                        @error('katops')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="jenisops" class="form-label">Jenis Ops</label>
                        <input type="text" class="form-control @error('jenisops') is-invalid @enderror" id="jenisops" name="jenisops" value="{{ old('jenisops', $data->jenisops) }}">
                        @error('jenisops')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="tahunops" class="form-label">Tahun Ops</label>
                        <input type="text" class="form-control @error('tahunops') is-invalid @enderror" id="tahunops" name="tahunops" value="{{ old('tahunops', $data->tahunops) }}">
                        @error('tahunops')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>
                <div class="mb-3">
                    <label for="temaops" class="form-label">Tema Ops</label>
                    <input type="text" class="form-control @error('temaops') is-invalid @enderror" id="temaops" name="temaops" value="{{ old('temaops', $data->temaops) }}">
                    @error('temaops')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="mb-3">
                    <label for="lokasiposko" class="form-label">Lokasi Posko</label>
                    <textarea class="form-control @error('lokasiposko') is-invalid @enderror" id="lokasiposko" name="lokasiposko" rows="2">{{ old('lokasiposko', $data->lokasiposko) }}</textarea>
                    @error('lokasiposko')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="mb-3">
                    <label for="alamat" class="form-label">Alamat</label>
                    <input type="text" class="form-control @error('alamat') is-invalid @enderror" id="alamat" name="alamat" value="{{ old('alamat', $data->alamat) }}">
                    @error('alamat')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="wilayah" class="form-label">Wilayah</label>
                        <input type="text" class="form-control @error('wilayah') is-invalid @enderror" id="wilayah" name="wilayah" value="{{ old('wilayah', $data->wilayah) }}">
                        @error('wilayah')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="daerah" class="form-label">Daerah</label>
                        <input type="text" class="form-control @error('daerah') is-invalid @enderror" id="daerah" name="daerah" value="{{ old('daerah', $data->daerah) }}">
                        @error('daerah')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>
                <div class="mb-3">
                    <label for="sarpras" class="form-label">Sarana Prasarana</label>
                    <textarea class="form-control @error('sarpras') is-invalid @enderror" id="sarpras" name="sarpras" rows="2">{{ old('sarpras', $data->sarpras) }}</textarea>
                    @error('sarpras')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="mb-3">
                    <label for="keterangan" class="form-label">Keterangan</label>
                    <textarea class="form-control @error('keterangan') is-invalid @enderror" id="keterangan" name="keterangan" rows="2">{{ old('keterangan', $data->keterangan) }}</textarea>
                    @error('keterangan')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="flex items-center justify-between pt-4 mt-4 border-t border-slate-100">
                    <a href="{{ route('profilposko.index') }}" class="flex items-center gap-2 px-4 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-600 font-bold rounded-xl text-label-md transition-all text-decoration-none">
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
