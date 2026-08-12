@php
    $inputBase = 'w-full bg-slate-50 border border-slate-200 rounded-lg px-3 py-2 text-[13px] text-slate-800 placeholder:text-slate-400 focus:border-primary focus:bg-white focus:ring-0 outline-none transition-all';
    $selectBase = $inputBase . ' appearance-none pr-8';
    $labelClass = 'block text-[10px] font-bold text-slate-500 uppercase tracking-wide mb-0.5';
    $errClass = 'text-[11px] text-red-600 font-medium mt-0.5 block';
    $invalidBorder = ' border-red-300 bg-red-50';
    $isCreate = empty($data);
    $formSuffix = $isCreate ? 'create' : 'edit';
    $fileInputId = 'dataperangkat_foto_'.$formSuffix;
    $previewId = 'dataperangkat_foto_preview_'.$formSuffix;
    $previewWrapId = 'dataperangkat_foto_preview_wrap_'.$formSuffix;
@endphp

<div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
    <div>
        <label for="tanggal" class="{{ $labelClass }}">Tanggal <span class="text-red-500">*</span></label>
        <x-sbo-date-input :value="optional($data ?? null)->tanggal ?? ($isCreate ? date('Y-m-d') : null)" :show-label="false" :default-today="$isCreate" required
            class="{{ $inputBase }}{{ $errors->has('tanggal') ? $invalidBorder : '' }}" />
    </div>
    <div>
        <label for="katperangkat" class="{{ $labelClass }}">Kategori Perangkat <span class="text-red-500">*</span></label>
        <div class="relative">
            <select name="katperangkat" id="katperangkat" required class="{{ $selectBase }}{{ $errors->has('katperangkat') ? $invalidBorder : '' }}">
                <option value="">— Pilih —</option>
                @foreach($kategori ?? [] as $k)
                    <option value="{{ $k }}" {{ old('katperangkat', optional($data ?? null)->katperangkat) === $k ? 'selected' : '' }}>{{ $k }}</option>
                @endforeach
            </select>
            <span class="absolute right-2.5 top-1/2 -translate-y-1/2 material-symbols-outlined text-slate-400 text-[16px] pointer-events-none">expand_more</span>
        </div>
        @error('katperangkat')<span class="{{ $errClass }}">{{ $message }}</span>@enderror
    </div>

    <x-sbo-form-divider icon="memory" title="Detail Perangkat" class="!pt-0 !border-t-0" />
    <div class="sm:col-span-2">
        <label for="nameperangkat" class="{{ $labelClass }}">Nama Perangkat <span class="text-red-500">*</span></label>
        <input type="text" name="nameperangkat" id="nameperangkat" required
            class="{{ $inputBase }}{{ $errors->has('nameperangkat') ? $invalidBorder : '' }}"
            value="{{ old('nameperangkat', optional($data ?? null)->nameperangkat) }}" placeholder="Contoh: PC Operator">
        @error('nameperangkat')<span class="{{ $errClass }}">{{ $message }}</span>@enderror
    </div>
    <div class="sm:col-span-2">
        <label for="lokasi" class="{{ $labelClass }}">Lokasi <span class="text-red-500">*</span></label>
        <input type="text" name="lokasi" id="lokasi" required
            class="{{ $inputBase }}{{ $errors->has('lokasi') ? $invalidBorder : '' }}"
            value="{{ old('lokasi', optional($data ?? null)->lokasi) }}" placeholder="Contoh: Ruang Backoffice">
        @error('lokasi')<span class="{{ $errClass }}">{{ $message }}</span>@enderror
    </div>

    <x-sbo-form-divider icon="inventory_2" title="Inventaris" />
    <div>
        <label for="owner" class="{{ $labelClass }}">Owner <span class="text-red-500">*</span></label>
        @if(($owners ?? collect())->isNotEmpty())
            <div class="relative">
                <select name="owner" id="owner" required class="{{ $selectBase }}{{ $errors->has('owner') ? $invalidBorder : '' }}">
                    <option value="">— Pilih —</option>
                    @foreach($owners as $o)
                        <option value="{{ $o }}" {{ old('owner', optional($data ?? null)->owner) === $o ? 'selected' : '' }}>{{ $o }}</option>
                    @endforeach
                </select>
                <span class="absolute right-2.5 top-1/2 -translate-y-1/2 material-symbols-outlined text-slate-400 text-[16px] pointer-events-none">expand_more</span>
            </div>
        @else
            <input type="text" name="owner" id="owner" required
                class="{{ $inputBase }}{{ $errors->has('owner') ? $invalidBorder : '' }}"
                value="{{ old('owner', optional($data ?? null)->owner) }}" placeholder="Contoh: Korlantas">
        @endif
        @error('owner')<span class="{{ $errClass }}">{{ $message }}</span>@enderror
    </div>
    <div>
        <label for="qtty" class="{{ $labelClass }}">Jumlah (Qty) <span class="text-red-500">*</span></label>
        <input type="text" name="qtty" id="qtty" required inputmode="numeric"
            class="{{ $inputBase }}{{ $errors->has('qtty') ? $invalidBorder : '' }}"
            value="{{ old('qtty', optional($data ?? null)->qtty ?? ($isCreate ? '1' : null)) }}" placeholder="Contoh: 5">
        @error('qtty')<span class="{{ $errClass }}">{{ $message }}</span>@enderror
    </div>
    <div class="sm:col-span-2">
        <label for="supportfor" class="{{ $labelClass }}">Dukungan Untuk</label>
        <input type="text" name="supportfor" id="supportfor"
            class="{{ $inputBase }}{{ $errors->has('supportfor') ? $invalidBorder : '' }}"
            value="{{ old('supportfor', optional($data ?? null)->supportfor) }}" placeholder="Contoh: Monitoring, Call Center">
        @error('supportfor')<span class="{{ $errClass }}">{{ $message }}</span>@enderror
    </div>

    <x-sbo-form-divider icon="add_a_photo" title="Foto Perangkat" />
    <div class="sm:col-span-2">
        <label for="{{ $fileInputId }}" class="{{ $labelClass }}">
            Foto Perangkat
            @unless($isCreate)
                <span class="normal-case font-normal text-slate-400">(opsional)</span>
            @endunless
        </label>
        @if(!$isCreate)
            @php
                $fotoUrl = null;
                if (!empty($data->fotoperangkat)) {
                    $first = trim(explode(',', (string) $data->fotoperangkat)[0]);
                    if (preg_match('#^https?://#i', $first)) {
                        $fotoUrl = $first;
                    } else {
                        $cleaned = ltrim($first, '/');
                        $fotoUrl = str_starts_with($cleaned, 'storage/') ? asset($cleaned) : asset('storage/' . $cleaned);
                    }
                }
            @endphp
            @if($fotoUrl)
                <a href="{{ $fotoUrl }}" target="_blank" rel="noopener noreferrer" class="block mb-2">
                    <img src="{{ $fotoUrl }}" alt="Foto perangkat" class="rounded-lg border border-slate-200 max-h-[120px] object-cover">
                </a>
            @endif
        @endif
        <label for="{{ $fileInputId }}"
            class="flex items-center justify-center gap-2 w-full rounded-lg border border-dashed border-slate-200 bg-slate-50/80 hover:border-primary/40 hover:bg-primary/5 px-3 py-3 cursor-pointer transition-all{{ $errors->has('foto_perangkat') ? ' border-red-300 bg-red-50/50' : '' }}">
            <span class="material-symbols-outlined text-[22px] text-primary/70">add_a_photo</span>
            <span class="text-[12px] font-semibold text-slate-600">Ambil / unggah foto</span>
        </label>
        <input type="file" name="foto_perangkat" id="{{ $fileInputId }}" class="visually-hidden"
            accept="image/jpeg,image/png,image/jpg,image/gif,image/webp" capture="environment"
            data-sbo-media-preview
            data-sbo-preview-img="#{{ $previewId }}"
            data-sbo-preview-wrap="#{{ $previewWrapId }}"
            data-sbo-max-mb="8">
        <p class="text-[11px] text-slate-400 mt-1 mb-0">JPG, PNG, WebP, atau GIF — maks. 8 MB.</p>
        @error('foto_perangkat')<span class="{{ $errClass }}">{{ $message }}</span>@enderror
        <div class="mt-2 d-none rounded-lg border border-slate-200 bg-slate-50 p-2" id="{{ $previewWrapId }}">
            <p class="text-[10px] font-bold text-slate-500 uppercase tracking-wide mb-1.5 m-0">Pratinjau</p>
            <img id="{{ $previewId }}" src="" alt="Pratinjau foto perangkat" class="rounded-md border border-slate-200 max-h-[220px] w-full object-contain d-none">
        </div>
    </div>
</div>
