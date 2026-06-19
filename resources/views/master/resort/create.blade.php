@extends('layouts.app')

@section('title', 'Tambah Master Polres')
@section('page-title', 'Tambah Master Polres')

@section('content')
<div class="max-w-lg mx-auto w-full">
    <div class="bg-white rounded-xl border border-slate-200/60 shadow-sm overflow-hidden">
        <div class="px-4 py-3 border-b border-slate-100 bg-primary/5">
            <h3 class="text-[11px] font-bold uppercase tracking-wider text-primary m-0 flex items-center gap-2">
                <span class="material-symbols-outlined text-[18px]">add</span>
                Tambah Polres
            </h3>
        </div>
        <div class="p-4">
            @if($errors->any())
                <div class="rounded-xl px-3 py-2 mb-3 border border-rose-200 bg-rose-50 text-rose-800 text-sm">
                    <ul class="mb-0 ps-3">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
                </div>
            @endif
            <form action="{{ route('master.resort.store') }}" method="POST" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-[11px] font-bold text-slate-600 uppercase tracking-wide mb-1.5">Polda <span class="text-error">*</span></label>
                    <input type="hidden" id="daerah-id-value" name="id_daerah" value="{{ old('id_daerah', $preselectedDaerah ?? '') }}" required>
                    <div class="relative">
                    <input type="text" id="daerah-id-select"
                           class="w-full px-3 py-2.5 rounded-xl border border-slate-200 bg-slate-50 focus:border-primary focus:bg-white focus:ring-0 outline-none text-[12px] font-semibold uppercase @error('id_daerah') border-error @enderror"
                           placeholder="Ketik dan pilih Polda..."
                           autocomplete="off">
                    <div id="daerah-id-options"
                         class="absolute left-0 right-0 z-20 mt-1 rounded-xl border border-slate-200 bg-white shadow-lg max-h-56 overflow-auto hidden"></div>
                    </div>
                    <p id="daerah-id-search-note" class="text-[10px] text-slate-400 mt-1 mb-0">Ketik nama Polda lalu pilih dari daftar.</p>
                    @error('id_daerah') <p class="text-[11px] text-error mt-1 mb-0">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-[11px] font-bold text-slate-600 uppercase tracking-wide mb-1.5">Nama Polres <span class="text-error">*</span></label>
                    <input type="text" name="nama"
                           class="w-full px-3 py-2.5 rounded-xl border border-slate-200 bg-slate-50 focus:border-primary focus:bg-white focus:ring-0 outline-none text-[13px] font-bold uppercase @error('nama') border-error @enderror"
                           style="text-transform: uppercase;"
                           value="{{ old('nama') }}" required maxlength="255" autocomplete="off"
                           placeholder="CONTOH: BADUNG">
                    <p class="text-[10px] text-slate-400 mt-1 mb-0">Disimpan huruf KAPITAL.</p>
                    @error('nama') <p class="text-[11px] text-error mt-1 mb-0">{{ $message }}</p> @enderror
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-[11px] font-bold text-slate-600 uppercase tracking-wide mb-1.5">Latitude</label>
                        <input type="text" name="lat"
                               class="w-full px-3 py-2.5 rounded-xl border border-slate-200 bg-slate-50 focus:border-primary focus:bg-white focus:ring-0 outline-none text-[13px] @error('lat') border-error @enderror"
                               value="{{ old('lat') }}" maxlength="50" autocomplete="off" placeholder="Misal: -6.200000">
                        @error('lat') <p class="text-[11px] text-error mt-1 mb-0">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-[11px] font-bold text-slate-600 uppercase tracking-wide mb-1.5">Longitude</label>
                        <input type="text" name="lng"
                               class="w-full px-3 py-2.5 rounded-xl border border-slate-200 bg-slate-50 focus:border-primary focus:bg-white focus:ring-0 outline-none text-[13px] @error('lng') border-error @enderror"
                               value="{{ old('lng') }}" maxlength="50" autocomplete="off" placeholder="Misal: 106.816666">
                        @error('lng') <p class="text-[11px] text-error mt-1 mb-0">{{ $message }}</p> @enderror
                    </div>
                </div>
                <div class="flex justify-between gap-2 pt-2">
                    <a href="{{ route('master.resort.index', request()->only('id_daerah')) }}" class="px-4 py-2 rounded-xl border border-slate-200 text-slate-600 text-[12px] font-bold hover:bg-slate-50 text-decoration-none">Batal</a>
                    <button type="submit" class="px-5 py-2 rounded-xl bg-primary text-on-primary text-[12px] font-bold border-0 shadow-sm">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const displayInput = document.getElementById('daerah-id-select');
    const hiddenInput = document.getElementById('daerah-id-value');
    const dropdown = document.getElementById('daerah-id-options');
    const note = document.getElementById('daerah-id-search-note');
    if (!displayInput || !hiddenInput || !dropdown) return;

    const options = [
        @foreach($daerahs as $d)
        { id: "{{ $d->id_daerah }}", text: "{{ strtoupper($d->namadaerah) }}" },
        @endforeach
    ];
    const textToId = new Map(options.map(function (item) { return [item.text.toLowerCase(), item.id]; }));
    const idToText = new Map(options.map(function (item) { return [item.id, item.text]; }));

    const selectedId = String(hiddenInput.value || '');
    if (selectedId && idToText.has(selectedId)) displayInput.value = idToText.get(selectedId);

    const renderOptions = function (keyword) {
        const kw = (keyword || '').toLowerCase().trim();
        const filtered = options.filter(function (item) {
            return kw === '' || item.text.toLowerCase().includes(kw);
        }).slice(0, 100);

        dropdown.innerHTML = '';
        if (filtered.length === 0) {
            dropdown.classList.add('hidden');
            if (note) note.textContent = 'Tidak ada data yang cocok.';
            return;
        }

        filtered.forEach(function (item) {
            const btn = document.createElement('button');
            btn.type = 'button';
            btn.className = 'w-full text-left px-3 py-2 text-[11px] font-semibold uppercase hover:bg-slate-50 border-0 bg-transparent';
            btn.textContent = item.text;
            btn.addEventListener('click', function () {
                displayInput.value = item.text;
                hiddenInput.value = item.id;
                dropdown.classList.add('hidden');
                if (note) note.textContent = 'Polda dipilih.';
            });
            dropdown.appendChild(btn);
        });

        dropdown.classList.remove('hidden');
        if (note) note.textContent = 'Menampilkan ' + filtered.length + ' pilihan.';
    };

    const syncValue = function () {
        const id = textToId.get((displayInput.value || '').toLowerCase().trim()) || '';
        hiddenInput.value = id;
    };

    displayInput.addEventListener('focus', function () { renderOptions(displayInput.value); });
    displayInput.addEventListener('input', function () { syncValue(); renderOptions(displayInput.value); });
    document.addEventListener('click', function (e) {
        if (!displayInput.contains(e.target) && !dropdown.contains(e.target)) dropdown.classList.add('hidden');
    });

    const form = displayInput.closest('form');
    if (form) {
        form.addEventListener('submit', function (e) {
            syncValue();
            if (!hiddenInput.value) {
                e.preventDefault();
                if (note) note.textContent = 'Pilih salah satu Polda dari daftar.';
                displayInput.focus();
            }
        });
    }
});
</script>
@endsection
