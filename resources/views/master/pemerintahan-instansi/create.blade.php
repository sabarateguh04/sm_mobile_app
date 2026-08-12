@extends('layouts.app')

@section('title', 'Tambah Instansi')
@section('page-title', 'Tambah Instansi')

@section('content')
<div class="max-w-lg mx-auto w-full">
    <div class="bg-white rounded-xl border border-slate-200/60 shadow-sm overflow-hidden">
        <div class="px-4 py-3 border-b border-slate-100 bg-primary/5">
            <h3 class="text-[11px] font-bold uppercase tracking-wider text-primary m-0 flex items-center gap-2">
                <span class="material-symbols-outlined text-[18px]">account_balance</span>
                Tambah Instansi Pemerintahan
            </h3>
        </div>
        <div class="p-4">
            @if($errors->any())
                <div class="rounded-xl px-3 py-2 mb-3 border border-rose-200 bg-rose-50 text-rose-800 text-sm">
                    <ul class="mb-0 ps-3">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
                </div>
            @endif



            <form action="{{ route('master.pemerintahan-instansi.store') }}" method="POST" class="space-y-4" id="formInstansi">
                @csrf

                {{-- 1. INSTANSI --}}
                <div>
                    <label class="block text-[11px] font-bold text-slate-600 uppercase tracking-wide mb-1.5">
                        <span class="inline-flex items-center gap-1">
                            Nama Instansi <span class="text-error">*</span>
                        </span>
                    </label>
                    <input type="text" name="nama_unit" id="field_instansi"
                           class="w-full px-3 py-2.5 rounded-xl border border-slate-200 bg-slate-50 focus:border-primary focus:bg-white focus:ring-0 outline-none text-[13px] font-bold uppercase @error('nama_unit') border-rose-400 @enderror"
                           style="text-transform: uppercase;"
                           value="{{ old('nama_unit') }}" required maxlength="255" autocomplete="off"
                           placeholder="CONTOH: BPBD KOTA JAKARTA" list="instansi_datalist">
                    <datalist id="instansi_datalist">
                        @foreach($instansiList as $inst)
                            <option value="{{ strtoupper($inst) }}">
                        @endforeach
                    </datalist>
                    <p class="text-[10px] text-slate-400 mt-1 mb-0">Nama instansi pemerintahan (kementerian, lembaga, dinas, dll).</p>
                    @error('nama_unit') <p class="text-[11px] text-rose-500 mt-1 mb-0">{{ $message }}</p> @enderror
                </div>



                <div class="flex justify-between gap-2 pt-2">
                    <a href="{{ route('master.pemerintahan-instansi.index') }}"
                       class="px-4 py-2 rounded-xl border border-slate-200 text-slate-600 text-[12px] font-bold hover:bg-slate-50 text-decoration-none">
                        Batal
                    </a>
                    <button type="submit"
                            class="px-5 py-2 rounded-xl bg-primary text-on-primary text-[12px] font-bold border-0 shadow-sm">
                        Simpan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>


@endsection
