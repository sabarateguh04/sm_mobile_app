@php
    $inputBase = 'w-full bg-slate-50 border border-slate-200 rounded-lg px-3 py-2 text-[13px] text-slate-800 placeholder:text-slate-400 focus:border-primary focus:bg-white focus:ring-0 outline-none transition-all';
    $selectBase = $inputBase . ' appearance-none pr-8';
    $labelClass = 'block text-[10px] font-bold text-slate-500 uppercase tracking-wide mb-0.5';
    $errClass = 'text-[11px] text-red-600 font-medium mt-0.5 block';
    $invalidBorder = ' border-red-300 bg-red-50';
    $isCreate = empty($data);
    $latVal = old('lat');
    $lngVal = old('lng');
    if ($latVal === null && $lngVal === null && !empty($data->koordinat ?? '') && str_contains((string) $data->koordinat, ',')) {
        [$latVal, $lngVal] = array_map('trim', explode(',', (string) $data->koordinat, 2));
    }
    $fileInputId = $isCreate ? 'faskes_foto_create' : 'faskes_foto_edit';
    $previewId = $isCreate ? 'faskes_foto_preview_create' : 'faskes_foto_preview_edit';
    $previewWrapId = $isCreate ? 'faskes_foto_preview_wrap_create' : 'faskes_foto_preview_wrap_edit';
    $gpsStatusId = $isCreate ? 'gps_status_faskes_create' : 'gps_status_faskes_edit';
    $fasilitasOptions = $fasilitasOptions ?? [];
    if (is_array(old('fasilitas'))) {
        $fasilitasSelected = old('fasilitas');
    } elseif (!$isCreate) {
        $fasilitasSelected = collect(preg_split('/\s*,\s*/', (string) old('fasilitas', $data->fasilitas ?? ''), -1, PREG_SPLIT_NO_EMPTY))
            ->map(fn ($s) => trim((string) $s))
            ->filter()
            ->values()
            ->all();
    } else {
        $fasilitasSelected = [];
    }
    $fasIdPrefix = $isCreate ? 'faskes_fas_create' : 'faskes_fas_edit';
@endphp

<div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
    <div>
        <label for="tanggal" class="{{ $labelClass }}">Tanggal <span class="text-red-500">*</span></label>
        <x-sbo-date-input :value="optional($data ?? null)->tanggal ?? ($isCreate ? date('Y-m-d') : null)" :show-label="false" :default-today="$isCreate" required
            class="{{ $inputBase }}{{ $errors->has('tanggal') ? $invalidBorder : '' }}" />
    </div>
    <div>
        <label for="katfaskes" class="{{ $labelClass }}">Kategori Faskes <span class="text-red-500">*</span></label>
        <div class="relative">
            <select name="katfaskes" id="katfaskes" required class="{{ $selectBase }}{{ $errors->has('katfaskes') ? $invalidBorder : '' }}">
                <option value="">— Pilih —</option>
                @foreach($kategori ?? [] as $k)
                    <option value="{{ $k }}" {{ old('katfaskes', optional($data ?? null)->katfaskes) === $k ? 'selected' : '' }}>{{ $k }}</option>
                @endforeach
            </select>
            <span class="absolute right-2.5 top-1/2 -translate-y-1/2 material-symbols-outlined text-slate-400 text-[16px] pointer-events-none">expand_more</span>
        </div>
        @error('katfaskes')<span class="{{ $errClass }}">{{ $message }}</span>@enderror
    </div>
    <div class="sm:col-span-2">
        <label for="namafaskes" class="{{ $labelClass }}">Nama Faskes <span class="text-red-500">*</span></label>
        <input type="text" name="namafaskes" id="namafaskes" required
            class="{{ $inputBase }}{{ $errors->has('namafaskes') ? $invalidBorder : '' }}"
            value="{{ old('namafaskes', optional($data ?? null)->namafaskes) }}" placeholder="Contoh: RSUD Dr. Soetomo">
        @error('namafaskes')<span class="{{ $errClass }}">{{ $message }}</span>@enderror
    </div>

    <x-sbo-form-divider icon="location_on" title="Lokasi & Koordinat" class="!pt-0 !border-t-0" />
    <div class="sm:col-span-2">
        <label for="lokasi" class="{{ $labelClass }}">Lokasi / Alamat <span class="text-red-500">*</span></label>
        <input type="text" name="lokasi" id="lokasi" required
            class="{{ $inputBase }}{{ $errors->has('lokasi') ? $invalidBorder : '' }}"
            value="{{ old('lokasi', optional($data ?? null)->lokasi) }}" placeholder="Alamat lengkap faskes"
            data-sbo-jalan-ac>
        @error('lokasi')<span class="{{ $errClass }}">{{ $message }}</span>@enderror
    </div>
    <div class="sm:col-span-2 grid grid-cols-1 sm:grid-cols-[1fr_1fr_auto] gap-2 items-end">
        <div>
            <label for="faskes_lat" class="{{ $labelClass }}">Latitude</label>
            <input type="number" step="any" name="lat" id="faskes_lat"
                class="{{ $inputBase }}{{ $errors->has('lat') ? $invalidBorder : '' }}"
                value="{{ $latVal }}" placeholder="-7.xxx" autocomplete="off">
        </div>
        <div>
            <label for="faskes_lng" class="{{ $labelClass }}">Longitude</label>
            <input type="number" step="any" name="lng" id="faskes_lng"
                class="{{ $inputBase }}{{ $errors->has('lng') ? $invalidBorder : '' }}"
                value="{{ $lngVal }}" placeholder="110.xxx" autocomplete="off">
        </div>
        <div>
            <button type="button"
                class="w-full flex items-center justify-center gap-1.5 px-3 py-2 bg-amber-50 hover:bg-amber-100 text-amber-800 border border-amber-200 font-bold rounded-lg text-[12px] transition-all border-solid whitespace-nowrap"
                data-sbo-gps-lat="faskes_lat"
                data-sbo-gps-lng="faskes_lng"
                data-sbo-gps-status="{{ $gpsStatusId }}">
                <span class="material-symbols-outlined text-[16px]">my_location</span>
                <span>GPS</span>
            </button>
        </div>
        <span class="sr-only" id="{{ $gpsStatusId }}"></span>
        @error('lat')<span class="{{ $errClass }} sm:col-span-2">{{ $message }}</span>@enderror
        @error('lng')<span class="{{ $errClass }} sm:col-span-2">{{ $message }}</span>@enderror
    </div>

    @include('_partials.sbo_checkbox_group', [
        'label' => 'Fasilitas',
        'fieldName' => 'fasilitas',
        'options' => $fasilitasOptions,
        'selected' => $fasilitasSelected,
        'idPrefix' => $fasIdPrefix,
        'hint' => 'Pilih fasilitas yang tersedia di faskes.',
        'labelClass' => $labelClass,
        'errClass' => $errClass,
    ])

    <x-sbo-form-divider icon="call" title="Kontak & Telepon" />
    <div>
        <label for="namecp" class="{{ $labelClass }}">Nama CP</label>
        <input type="text" name="namecp" id="namecp"
            class="{{ $inputBase }}{{ $errors->has('namecp') ? $invalidBorder : '' }}"
            value="{{ old('namecp', optional($data ?? null)->namecp) }}" placeholder="Nama contact person">
        @error('namecp')<span class="{{ $errClass }}">{{ $message }}</span>@enderror
    </div>
    <div>
        <label for="cp" class="{{ $labelClass }}">Telp CP</label>
        <input type="text" name="cp" id="cp"
            class="{{ $inputBase }}{{ $errors->has('cp') ? $invalidBorder : '' }}"
            value="{{ old('cp', optional($data ?? null)->cp) }}" placeholder="08xx-xxxx-xxxx">
        @error('cp')<span class="{{ $errClass }}">{{ $message }}</span>@enderror
    </div>
    <div>
        <label for="telpambulance" class="{{ $labelClass }}">Telp Ambulance</label>
        <input type="text" name="telpambulance" id="telpambulance"
            class="{{ $inputBase }}{{ $errors->has('telpambulance') ? $invalidBorder : '' }}"
            value="{{ old('telpambulance', optional($data ?? null)->telpambulance) }}" placeholder="Nomor ambulance">
        @error('telpambulance')<span class="{{ $errClass }}">{{ $message }}</span>@enderror
    </div>
    <div>
        <label for="telpigd" class="{{ $labelClass }}">Telp IGD</label>
        <input type="text" name="telpigd" id="telpigd"
            class="{{ $inputBase }}{{ $errors->has('telpigd') ? $invalidBorder : '' }}"
            value="{{ old('telpigd', optional($data ?? null)->telpigd) }}" placeholder="Nomor IGD">
        @error('telpigd')<span class="{{ $errClass }}">{{ $message }}</span>@enderror
    </div>

    <x-sbo-form-divider icon="add_a_photo" title="Dokumentasi Foto" />
    <div class="sm:col-span-2">
        <label for="{{ $fileInputId }}" class="{{ $labelClass }}">
            Foto Faskes
            @unless($isCreate)
                <span class="normal-case font-normal text-slate-400">(opsional)</span>
            @endunless
        </label>
        @if(!$isCreate && method_exists($data, 'fotoUrl') && $data->fotoUrl())
            <a href="{{ $data->fotoUrl() }}" target="_blank" rel="noopener noreferrer" class="block mb-2">
                <img src="{{ $data->fotoUrl() }}" alt="Foto faskes" class="rounded-lg border border-slate-200 max-h-[120px] object-cover">
            </a>
        @endif
        <label for="{{ $fileInputId }}"
            class="flex items-center justify-center gap-2 w-full rounded-lg border border-dashed border-slate-200 bg-slate-50/80 hover:border-primary/40 hover:bg-primary/5 px-3 py-3 cursor-pointer transition-all{{ $errors->has('foto_lokasi') ? ' border-red-300 bg-red-50/50' : '' }}">
            <span class="material-symbols-outlined text-[22px] text-primary/70">add_a_photo</span>
            <span class="text-[12px] font-semibold text-slate-600">Ambil / unggah foto</span>
        </label>
        <input type="file" name="foto_lokasi" id="{{ $fileInputId }}" class="visually-hidden"
            accept="image/jpeg,image/png,image/jpg,image/gif,image/webp" capture="environment"
            data-sbo-media-preview
            data-sbo-preview-img="#{{ $previewId }}"
            data-sbo-preview-wrap="#{{ $previewWrapId }}"
            data-sbo-max-mb="8">
        @error('foto_lokasi')<span class="{{ $errClass }}">{{ $message }}</span>@enderror
        <div class="mt-2 d-none rounded-lg border border-slate-200 bg-slate-50 p-2" id="{{ $previewWrapId }}">
            <img id="{{ $previewId }}" src="" alt="Pratinjau" class="rounded-md border border-slate-200 max-h-[180px] w-full object-contain d-none">
        </div>
    </div>
</div>
