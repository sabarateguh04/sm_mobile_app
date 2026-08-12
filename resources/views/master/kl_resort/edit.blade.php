@extends('layouts.app')

@section('title', 'Ubah Master Sub Org')
@section('page-title', 'Ubah Master Sub Org')

@section('content')
<div class="max-w-lg mx-auto w-full">
    <div class="bg-white rounded-xl border border-slate-200/60 shadow-sm overflow-hidden">
        <div class="px-4 py-3 border-b border-slate-100 bg-secondary/5">
            <h3 class="text-[11px] font-bold uppercase tracking-wider text-secondary m-0 flex items-center gap-2">
                <span class="material-symbols-outlined text-[18px]">edit</span>
                Ubah Sub Org
            </h3>
        </div>
        <div class="p-4">
            @if($errors->any())
                <div class="rounded-xl px-3 py-2 mb-3 border border-rose-200 bg-rose-50 text-rose-800 text-sm">
                    <ul class="mb-0 ps-3">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
                </div>
            @endif
            <form action="{{ route('master.kl-resort.update', $row->id) }}" method="POST" class="space-y-4">
                @csrf
                @method('PUT')
                <div>
                    <label class="block text-[11px] font-bold text-slate-600 uppercase tracking-wide mb-1.5">Instansi <span class="text-error">*</span></label>
                    <select name="instansi_id" id="instansi_id" class="w-full px-3 py-2.5 rounded-xl border border-slate-200 bg-slate-50 focus:border-primary focus:bg-white focus:ring-0 outline-none text-[13px] font-bold" required>
                        <option value="">— Pilih Instansi —</option>
                        @foreach($instansis as $inst)
                            <option value="{{ $inst->id }}" @selected(old('instansi_id', $row->instansi_id) == $inst->id)>{{ $inst->nama_unit }}</option>
                        @endforeach
                    </select>
                    @error('instansi_id') <p class="text-[11px] text-error mt-1 mb-0">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-[11px] font-bold text-slate-600 uppercase tracking-wide mb-1.5">Organisasi <span class="text-error">*</span></label>
                    <select name="org_id" id="org_id" class="w-full px-3 py-2.5 rounded-xl border border-slate-200 bg-slate-50 focus:border-primary focus:bg-white focus:ring-0 outline-none text-[13px] font-bold" required>
                        <option value="">— Pilih Organisasi —</option>
                        @foreach($orgs as $org)
                            <option value="{{ $org->id_org }}" data-instansi="{{ $org->instansi_id }}" @selected(old('org_id', $row->org_id) == $org->id_org)>{{ $org->nameorg }}</option>
                        @endforeach
                    </select>
                    @error('org_id') <p class="text-[11px] text-error mt-1 mb-0">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-[11px] font-bold text-slate-600 uppercase tracking-wide mb-1.5">Nama Sub Organisasi <span class="text-error">*</span></label>
                    <input type="text" name="nama"
                           class="w-full px-3 py-2.5 rounded-xl border border-slate-200 bg-slate-50 focus:border-primary focus:bg-white focus:ring-0 outline-none text-[13px] font-bold uppercase @error('nama') border-error @enderror"
                           style="text-transform: uppercase;"
                           value="{{ old('nama', $row->nama) }}" required maxlength="255" autocomplete="off">
                    <p class="text-[10px] text-slate-400 mt-1 mb-0">Disimpan huruf KAPITAL.</p>
                    @error('nama') <p class="text-[11px] text-error mt-1 mb-0">{{ $message }}</p> @enderror
                </div>
                <div class="flex justify-between gap-2 pt-2">
                    <a href="{{ route('master.kl-resort.index') }}" class="px-4 py-2 rounded-xl border border-slate-200 text-slate-600 text-[12px] font-bold hover:bg-slate-50 text-decoration-none">Kembali</a>
                    <button type="submit" class="px-5 py-2 rounded-xl bg-secondary text-on-secondary text-[12px] font-bold border-0">Simpan Perubahan</button>
                </div>
            </form>
        </div>
    </div>
</div>
<script>
document.addEventListener('DOMContentLoaded', function () {
    // JS for Instansi -> Organisasi cascading
    const instansiSelect = document.getElementById('instansi_id');
    const orgSelect = document.getElementById('org_id');
    if (instansiSelect && orgSelect) {
        const filterOrgs = () => {
            const selectedInstansi = instansiSelect.value;
            let firstVisible = null;
            let hasVisible = false;
            Array.from(orgSelect.options).forEach(opt => {
                if (opt.value === '') return;
                if (opt.dataset.instansi === selectedInstansi) {
                    opt.style.display = '';
                    hasVisible = true;
                    if (!firstVisible) firstVisible = opt.value;
                } else {
                    opt.style.display = 'none';
                    if (orgSelect.value === opt.value) orgSelect.value = '';
                }
            });
            if (!orgSelect.value && hasVisible && selectedInstansi) {
                // optional: orgSelect.value = firstVisible;
            }
        };
        instansiSelect.addEventListener('change', filterOrgs);
        filterOrgs();
    }

});
</script>
@endsection
