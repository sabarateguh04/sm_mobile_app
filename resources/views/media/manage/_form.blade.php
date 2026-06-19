@php
    $inputBase = 'w-full bg-slate-50 border border-slate-200 rounded-lg px-3 py-2 text-[13px] text-slate-800 placeholder:text-slate-400 focus:border-primary focus:bg-white focus:ring-0 outline-none transition-all';
    $selectBase = $inputBase . ' appearance-none pr-8';
    $labelClass = 'block text-[10px] font-bold text-slate-500 uppercase tracking-wide mb-0.5';
    $errClass = 'text-[11px] text-red-600 font-medium mt-0.5 block';
    $invalidBorder = ' border-red-300 bg-red-50';
    $isCreate = empty($data);
    $fotodoc = !$isCreate ? ($data->fotodoc ?? '') : '';
    $fotodocImg = $fotodoc !== '' && (str_starts_with($fotodoc, 'http') || str_starts_with($fotodoc, '/storage'));
    $fileInputId = $isCreate ? 'fotodoc_file_manage_create' : 'fotodoc_file_manage_edit';
    $previewId = $isCreate ? 'fotodoc_preview_manage_create' : 'fotodoc_preview_manage_edit';
    $previewWrapId = $isCreate ? 'fotodoc_preview_wrap_manage_create' : 'fotodoc_preview_wrap_manage_edit';
@endphp

<div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
    <div>
        <label for="tanggal" class="{{ $labelClass }}">Tanggal <span class="text-red-500">*</span></label>
        <x-sbo-date-input :value="optional($data ?? null)->tanggal ?? ($isCreate ? date('Y-m-d') : null)" :show-label="false" :default-today="$isCreate" required
            class="{{ $inputBase }}{{ $errors->has('tanggal') ? $invalidBorder : '' }}" />
    </div>
    <div>
        <label for="kategori_konten" class="{{ $labelClass }}">Kategori Konten</label>
        <div class="relative">
            <select name="kategori_konten" id="kategori_konten" class="{{ $selectBase }}{{ $errors->has('kategori_konten') ? $invalidBorder : '' }}">
                <option value="">— Pilih —</option>
                @foreach($kategoriOptions ?? [] as $opt)
                    <option value="{{ $opt }}" {{ old('kategori_konten', optional($data ?? null)->kategori_konten) === $opt ? 'selected' : '' }}>{{ $opt }}</option>
                @endforeach
            </select>
            <span class="absolute right-2.5 top-1/2 -translate-y-1/2 material-symbols-outlined text-slate-400 text-[16px] pointer-events-none">expand_more</span>
        </div>
        @error('kategori_konten')<span class="{{ $errClass }}">{{ $message }}</span>@enderror
    </div>
    <div>
        <label for="tema" class="{{ $labelClass }}">Tema</label>
        <input type="text" name="tema" id="tema"
            class="{{ $inputBase }}{{ $errors->has('tema') ? $invalidBorder : '' }}"
            value="{{ old('tema', optional($data ?? null)->tema) }}" placeholder="Topik / tema konten">
        @error('tema')<span class="{{ $errClass }}">{{ $message }}</span>@enderror
    </div>
    <div>
        <label for="metode" class="{{ $labelClass }}">Metode</label>
        <div class="relative">
            <select name="metode" id="metode" class="{{ $selectBase }}{{ $errors->has('metode') ? $invalidBorder : '' }}">
                <option value="">— Pilih —</option>
                @foreach($metodeOptions ?? [] as $opt)
                    <option value="{{ $opt }}" {{ old('metode', optional($data ?? null)->metode) === $opt ? 'selected' : '' }}>{{ $opt }}</option>
                @endforeach
            </select>
            <span class="absolute right-2.5 top-1/2 -translate-y-1/2 material-symbols-outlined text-slate-400 text-[16px] pointer-events-none">expand_more</span>
        </div>
        @error('metode')<span class="{{ $errClass }}">{{ $message }}</span>@enderror
    </div>
    <div>
        <label for="media_publishing" class="{{ $labelClass }}">Media Publishing</label>
        <div class="relative">
            <select name="media_publishing" id="media_publishing" class="{{ $selectBase }}{{ $errors->has('media_publishing') ? $invalidBorder : '' }}">
                <option value="">— Pilih —</option>
                @foreach($mediaPublishingOptions ?? [] as $opt)
                    <option value="{{ $opt }}" {{ old('media_publishing', optional($data ?? null)->media_publishing) === $opt ? 'selected' : '' }}>{{ $opt }}</option>
                @endforeach
            </select>
            <span class="absolute right-2.5 top-1/2 -translate-y-1/2 material-symbols-outlined text-slate-400 text-[16px] pointer-events-none">expand_more</span>
        </div>
        @error('media_publishing')<span class="{{ $errClass }}">{{ $message }}</span>@enderror
    </div>
    <div>
        <label for="links" class="{{ $labelClass }}">Links</label>
        <input type="text" name="links" id="links"
            class="{{ $inputBase }}{{ $errors->has('links') ? $invalidBorder : '' }}"
            value="{{ old('links', optional($data ?? null)->links) }}" placeholder="URL atau referensi media">
        @error('links')<span class="{{ $errClass }}">{{ $message }}</span>@enderror
    </div>
    <div class="sm:col-span-2">
        <label for="{{ $fileInputId }}" class="{{ $labelClass }}">
            Foto Doc
            @unless($isCreate)
                <span class="normal-case font-normal text-slate-400">(opsional)</span>
            @endunless
        </label>
        @if($fotodocImg)
            <a href="{{ str_starts_with($fotodoc, 'http') ? $fotodoc : url($fotodoc) }}" target="_blank" rel="noopener noreferrer" class="block mb-2">
                <img src="{{ str_starts_with($fotodoc, 'http') ? $fotodoc : url($fotodoc) }}" alt="Foto Doc" class="rounded-lg border border-slate-200 max-h-[120px] object-cover">
            </a>
        @endif
        <label for="{{ $fileInputId }}"
            class="flex items-center justify-center gap-2 w-full rounded-lg border border-dashed border-slate-200 bg-slate-50/80 hover:border-primary/40 hover:bg-primary/5 px-3 py-3 cursor-pointer transition-all{{ $errors->has('fotodoc_file') ? ' border-red-300 bg-red-50/50' : '' }}">
            <span class="material-symbols-outlined text-[22px] text-primary/70">add_a_photo</span>
            <span class="text-[12px] font-semibold text-slate-600">Ambil / unggah foto</span>
        </label>
        <input type="file" name="fotodoc_file" id="{{ $fileInputId }}" class="visually-hidden"
            accept="image/jpeg,image/png,image/jpg,image/gif,image/webp" capture="environment"
            data-sbo-media-preview
            data-sbo-preview-img="#{{ $previewId }}"
            data-sbo-preview-wrap="#{{ $previewWrapId }}"
            data-sbo-max-mb="8">
        @error('fotodoc_file')<span class="{{ $errClass }}">{{ $message }}</span>@enderror
        <div class="mt-2 d-none rounded-lg border border-slate-200 bg-slate-50 p-2" id="{{ $previewWrapId }}">
            <img id="{{ $previewId }}" src="" alt="Pratinjau" class="rounded-md border border-slate-200 max-h-[180px] w-full object-contain d-none">
        </div>
    </div>
</div>
