@php
    $inputBase = 'w-full bg-slate-50 border border-slate-200 rounded-lg px-3 py-2 text-[13px] text-slate-800 placeholder:text-slate-400 focus:border-primary focus:bg-white focus:ring-0 outline-none transition-all';
    $selectBase = $inputBase . ' appearance-none pr-8';
    $labelClass = 'block text-[10px] font-bold text-slate-500 uppercase tracking-wide mb-0.5';
    $errClass = 'text-[11px] text-red-600 font-medium mt-0.5 block';
    $invalidBorder = ' border-red-300 bg-red-50';
    $isCreate = empty($data);
    $fileInputId = $isCreate ? 'kunjungan_foto_create' : 'kunjungan_foto_edit';
    $previewId = $isCreate ? 'kunjungan_foto_preview_create' : 'kunjungan_foto_preview_edit';
    $previewWrapId = $isCreate ? 'kunjungan_foto_preview_wrap_create' : 'kunjungan_foto_preview_wrap_edit';
@endphp

<div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
    <div>
        <label for="tanggal" class="{{ $labelClass }}">Tanggal <span class="text-red-500">*</span></label>
        <x-sbo-date-input :value="optional($data ?? null)->tanggal ?? ($isCreate ? date('Y-m-d') : null)" :show-label="false" :default-today="$isCreate" required
            class="{{ $inputBase }}{{ $errors->has('tanggal') ? $invalidBorder : '' }}" />
    </div>
    <div>
        <label for="katwisata" class="{{ $labelClass }}">Kategori Wisata <span class="text-red-500">*</span></label>
        <div class="relative">
            <select name="katwisata" id="katwisata" required class="{{ $selectBase }}{{ $errors->has('katwisata') ? $invalidBorder : '' }}">
                <option value="">— Pilih —</option>
                @foreach($kategori ?? [] as $k)
                    <option value="{{ $k }}" {{ old('katwisata', optional($data ?? null)->katwisata) === $k ? 'selected' : '' }}>{{ $k }}</option>
                @endforeach
            </select>
            <span class="absolute right-2.5 top-1/2 -translate-y-1/2 material-symbols-outlined text-slate-400 text-[16px] pointer-events-none">expand_more</span>
        </div>
        @error('katwisata')<span class="{{ $errClass }}">{{ $message }}</span>@enderror
    </div>
    <div class="sm:col-span-2">
        <label for="namawisata" class="{{ $labelClass }}">Nama Objek Wisata <span class="text-red-500">*</span></label>
        <input type="text" name="namawisata" id="namawisata" required
            class="{{ $inputBase }}{{ $errors->has('namawisata') ? $invalidBorder : '' }}"
            value="{{ old('namawisata', optional($data ?? null)->namawisata) }}" placeholder="Nama tempat wisata">
        @error('namawisata')<span class="{{ $errClass }}">{{ $message }}</span>@enderror
    </div>
    <div class="sm:col-span-2">
        <label for="koresponden" class="{{ $labelClass }}">Koresponden</label>
        <input type="text" name="koresponden" id="koresponden"
            class="{{ $inputBase }}{{ $errors->has('koresponden') ? $invalidBorder : '' }}"
            value="{{ old('koresponden', optional($data ?? null)->koresponden) }}" placeholder="Nama narahubung">
        @error('koresponden')<span class="{{ $errClass }}">{{ $message }}</span>@enderror
    </div>
    <div>
        <label for="parkirr2" class="{{ $labelClass }}">Parkir R2 <span class="text-red-500">*</span></label>
        <input type="number" name="parkirr2" id="parkirr2" min="0" required
            class="{{ $inputBase }}{{ $errors->has('parkirr2') ? $invalidBorder : '' }}"
            value="{{ old('parkirr2', optional($data ?? null)->parkirr2 ?? 0) }}">
        @error('parkirr2')<span class="{{ $errClass }}">{{ $message }}</span>@enderror
    </div>
    <div>
        <label for="parkirr4" class="{{ $labelClass }}">Parkir R4 <span class="text-red-500">*</span></label>
        <input type="number" name="parkirr4" id="parkirr4" min="0" required
            class="{{ $inputBase }}{{ $errors->has('parkirr4') ? $invalidBorder : '' }}"
            value="{{ old('parkirr4', optional($data ?? null)->parkirr4 ?? 0) }}">
        @error('parkirr4')<span class="{{ $errClass }}">{{ $message }}</span>@enderror
    </div>
    <div>
        <label for="jmlhkbm" class="{{ $labelClass }}">Total KBM</label>
        <input type="number" name="jmlhkbm" id="jmlhkbm" min="0"
            class="{{ $inputBase }}{{ $errors->has('jmlhkbm') ? $invalidBorder : '' }}"
            value="{{ old('jmlhkbm', optional($data ?? null)->jmlhkbm ?? 0) }}">
        @error('jmlhkbm')<span class="{{ $errClass }}">{{ $message }}</span>@enderror
    </div>
    <div>
        <label for="domestic" class="{{ $labelClass }}">Pengunjung Domestik <span class="text-red-500">*</span></label>
        <input type="number" name="domestic" id="domestic" min="0" required
            class="{{ $inputBase }}{{ $errors->has('domestic') ? $invalidBorder : '' }}"
            value="{{ old('domestic', optional($data ?? null)->domestic ?? 0) }}">
        @error('domestic')<span class="{{ $errClass }}">{{ $message }}</span>@enderror
    </div>
    <div>
        <label for="mancanegara" class="{{ $labelClass }}">Pengunjung Mancanegara <span class="text-red-500">*</span></label>
        <input type="number" name="mancanegara" id="mancanegara" min="0" required
            class="{{ $inputBase }}{{ $errors->has('mancanegara') ? $invalidBorder : '' }}"
            value="{{ old('mancanegara', optional($data ?? null)->mancanegara ?? 0) }}">
        @error('mancanegara')<span class="{{ $errClass }}">{{ $message }}</span>@enderror
    </div>
    <div>
        <label for="jmlhpengunjung" class="{{ $labelClass }}">Total Pengunjung <span class="text-red-500">*</span></label>
        <input type="number" name="jmlhpengunjung" id="jmlhpengunjung" min="0" required
            class="{{ $inputBase }}{{ $errors->has('jmlhpengunjung') ? $invalidBorder : '' }}"
            value="{{ old('jmlhpengunjung', optional($data ?? null)->jmlhpengunjung ?? 0) }}">
        @error('jmlhpengunjung')<span class="{{ $errClass }}">{{ $message }}</span>@enderror
    </div>
    <div class="sm:col-span-2">
        <label for="{{ $fileInputId }}" class="{{ $labelClass }}">
            Foto Giat
            @unless($isCreate)
                <span class="normal-case font-normal text-slate-400">(opsional)</span>
            @endunless
        </label>
        @if(!$isCreate && $data->fotoUrl())
            <a href="{{ $data->fotoUrl() }}" target="_blank" rel="noopener noreferrer" class="block mb-2">
                <img src="{{ $data->fotoUrl() }}" alt="Foto giat" class="rounded-lg border border-slate-200 max-h-[120px] object-cover">
            </a>
        @endif
        <label for="{{ $fileInputId }}"
            class="flex items-center justify-center gap-2 w-full rounded-lg border border-dashed border-slate-200 bg-slate-50/80 hover:border-primary/40 hover:bg-primary/5 px-3 py-3 cursor-pointer transition-all{{ $errors->has('fotogiat') ? ' border-red-300 bg-red-50/50' : '' }}">
            <span class="material-symbols-outlined text-[22px] text-primary/70">add_a_photo</span>
            <span class="text-[12px] font-semibold text-slate-600">Ambil / unggah foto</span>
        </label>
        <input type="file" name="fotogiat" id="{{ $fileInputId }}" class="visually-hidden"
            accept="image/jpeg,image/png,image/jpg,image/gif,image/webp" capture="environment"
            data-sbo-media-preview
            data-sbo-preview-img="#{{ $previewId }}"
            data-sbo-preview-wrap="#{{ $previewWrapId }}"
            data-sbo-max-mb="8">
        @error('fotogiat')<span class="{{ $errClass }}">{{ $message }}</span>@enderror
        <div class="mt-2 d-none rounded-lg border border-slate-200 bg-slate-50 p-2" id="{{ $previewWrapId }}">
            <img id="{{ $previewId }}" src="" alt="Pratinjau" class="rounded-md border border-slate-200 max-h-[180px] w-full object-contain d-none">
        </div>
    </div>
</div>
