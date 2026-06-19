@extends('layouts.app')

@section('title', 'Ubah Master Polda')
@section('page-title', 'Ubah Master Polda')

@section('content')
<div class="max-w-lg mx-auto w-full">
    <div class="bg-white rounded-xl border border-slate-200/60 shadow-sm overflow-hidden">
        <div class="px-4 py-3 border-b border-slate-100 bg-secondary/5">
            <h3 class="text-[11px] font-bold uppercase tracking-wider text-secondary m-0 flex items-center gap-2">
                <span class="material-symbols-outlined text-[18px]">edit</span>
                Ubah Polda
            </h3>
        </div>
        <div class="p-4">
            @if($errors->any())
                <div class="rounded-xl px-3 py-2 mb-3 border border-rose-200 bg-rose-50 text-rose-800 text-sm">
                    <ul class="mb-0 ps-3">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
                </div>
            @endif
            <form action="{{ route('master.daerah.update', $row->id) }}" method="POST" class="space-y-4">
                @csrf
                @method('PUT')
                <div>
                    <label class="block text-[11px] font-bold text-slate-600 uppercase tracking-wide mb-1.5">Nama Polda <span class="text-error">*</span></label>
                    <input type="text" name="nama"
                           class="w-full px-3 py-2.5 rounded-xl border border-slate-200 bg-slate-50 focus:border-primary focus:bg-white focus:ring-0 outline-none text-[13px] font-bold uppercase @error('nama') border-error @enderror"
                           style="text-transform: uppercase;"
                           value="{{ old('nama', $row->nama) }}" required maxlength="255" autocomplete="off">
                    <p class="text-[10px] text-slate-400 mt-1 mb-0">Disimpan huruf KAPITAL.</p>
                    @error('nama') <p class="text-[11px] text-error mt-1 mb-0">{{ $message }}</p> @enderror
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-[11px] font-bold text-slate-600 uppercase tracking-wide mb-1.5">Latitude</label>
                        <input type="text" name="lat"
                               class="w-full px-3 py-2.5 rounded-xl border border-slate-200 bg-slate-50 focus:border-primary focus:bg-white focus:ring-0 outline-none text-[13px] @error('lat') border-error @enderror"
                               value="{{ old('lat', $row->lat ?? '') }}" maxlength="50" autocomplete="off" placeholder="Misal: -6.200000">
                        @error('lat') <p class="text-[11px] text-error mt-1 mb-0">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-[11px] font-bold text-slate-600 uppercase tracking-wide mb-1.5">Longitude</label>
                        <input type="text" name="lng"
                               class="w-full px-3 py-2.5 rounded-xl border border-slate-200 bg-slate-50 focus:border-primary focus:bg-white focus:ring-0 outline-none text-[13px] @error('lng') border-error @enderror"
                               value="{{ old('lng', $row->lng ?? '') }}" maxlength="50" autocomplete="off" placeholder="Misal: 106.816666">
                        @error('lng') <p class="text-[11px] text-error mt-1 mb-0">{{ $message }}</p> @enderror
                    </div>
                </div>
                <div class="flex justify-between gap-2 pt-2">
                    <a href="{{ route('master.daerah.index') }}" class="px-4 py-2 rounded-xl border border-slate-200 text-slate-600 text-[12px] font-bold hover:bg-slate-50 text-decoration-none">Kembali</a>
                    <button type="submit" class="px-5 py-2 rounded-xl bg-secondary text-on-secondary text-[12px] font-bold border-0 shadow-sm">
                        Simpan Perubahan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
