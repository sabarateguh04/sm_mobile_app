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
    $fileInputId = $isCreate ? 'ibadah_foto_create' : 'ibadah_foto_edit';
    $previewId = $isCreate ? 'ibadah_foto_preview_create' : 'ibadah_foto_preview_edit';
    $previewWrapId = $isCreate ? 'ibadah_foto_preview_wrap_create' : 'ibadah_foto_preview_wrap_edit';
    $gpsStatusId = $isCreate ? 'gps_status_tempatibadah_create' : 'gps_status_tempatibadah_edit';
    $alatKeamananOptions = $alatKeamananOptions ?? [];
    if (is_array(old('alatkeamanan'))) {
        $alatKeamananSelected = old('alatkeamanan');
    } elseif (!$isCreate) {
        $alatKeamananSelected = collect(preg_split('/\s*,\s*/', (string) old('alatkeamanan', $data->alatkeamanan ?? ''), -1, PREG_SPLIT_NO_EMPTY))
            ->map(fn ($s) => trim((string) $s))
            ->filter()
            ->values()
            ->all();
    } else {
        $alatKeamananSelected = [];
    }
    $alatIdPrefix = $isCreate ? 'tempatibadah_alat_create' : 'tempatibadah_alat_edit';
@endphp

<div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
    {{-- Data utama --}}
    <div>
        <label for="tanggal" class="{{ $labelClass }}">Tanggal <span class="text-red-500">*</span></label>
        <x-sbo-date-input :value="optional($data ?? null)->tanggal ?? ($isCreate ? date('Y-m-d') : null)" :show-label="false" :default-today="$isCreate" required
            class="{{ $inputBase }}{{ $errors->has('tanggal') ? $invalidBorder : '' }}" />
    </div>
    <div>
        <label for="kattempat" class="{{ $labelClass }}">Kategori Tempat <span class="text-red-500">*</span></label>
        <div class="relative">
            <select name="kattempat" id="kattempat" required class="{{ $selectBase }}{{ $errors->has('kattempat') ? $invalidBorder : '' }}">
                <option value="">— Pilih —</option>
                @foreach($kategori ?? [] as $k)
                    <option value="{{ $k }}" {{ old('kattempat', optional($data ?? null)->kattempat) === $k ? 'selected' : '' }}>{{ $k }}</option>
                @endforeach
            </select>
            <span class="absolute right-2.5 top-1/2 -translate-y-1/2 material-symbols-outlined text-slate-400 text-[16px] pointer-events-none">expand_more</span>
        </div>
        @error('kattempat')<span class="{{ $errClass }}">{{ $message }}</span>@enderror
    </div>
    <div class="sm:col-span-2">
        <label for="namatempatibadah" class="{{ $labelClass }}">Nama Tempat Ibadah <span class="text-red-500">*</span></label>
        <input type="text" name="namatempatibadah" id="namatempatibadah" required
            class="{{ $inputBase }}{{ $errors->has('namatempatibadah') ? $invalidBorder : '' }}"
            value="{{ old('namatempatibadah', optional($data ?? null)->namatempatibadah) }}" placeholder="Contoh: Masjid Agung Al-Falah">
        @error('namatempatibadah')<span class="{{ $errClass }}">{{ $message }}</span>@enderror
    </div>

    {{-- Lokasi & GPS --}}
    <x-sbo-form-divider icon="location_on" title="Lokasi & Koordinat" class="!pt-0 !border-t-0" />
    <div class="sm:col-span-2">
        <label for="lokasi" class="{{ $labelClass }}">Lokasi / Alamat <span class="text-red-500">*</span></label>
        <input type="text" name="lokasi" id="lokasi" required
            class="{{ $inputBase }}{{ $errors->has('lokasi') ? $invalidBorder : '' }}"
            value="{{ old('lokasi', optional($data ?? null)->lokasi) }}" placeholder="Alamat lengkap tempat ibadah"
            data-sbo-jalan-ac>
        @error('lokasi')<span class="{{ $errClass }}">{{ $message }}</span>@enderror
    </div>
    <div class="sm:col-span-2 grid grid-cols-1 sm:grid-cols-[1fr_1fr_auto] gap-2 items-end">
        <div>
            <label for="tempatibadah_lat" class="{{ $labelClass }}">Latitude</label>
            <input type="number" step="any" name="lat" id="tempatibadah_lat"
                class="{{ $inputBase }}{{ $errors->has('lat') ? $invalidBorder : '' }}"
                value="{{ $latVal }}" placeholder="-7.xxx" autocomplete="off">
        </div>
        <div>
            <label for="tempatibadah_lng" class="{{ $labelClass }}">Longitude</label>
            <input type="number" step="any" name="lng" id="tempatibadah_lng"
                class="{{ $inputBase }}{{ $errors->has('lng') ? $invalidBorder : '' }}"
                value="{{ $lngVal }}" placeholder="110.xxx" autocomplete="off">
        </div>
        <div>
            <button type="button"
                class="w-full flex items-center justify-center gap-1.5 px-3 py-2 bg-amber-50 hover:bg-amber-100 text-amber-800 border border-amber-200 font-bold rounded-lg text-[12px] transition-all border-solid whitespace-nowrap"
                data-sbo-gps-lat="tempatibadah_lat"
                data-sbo-gps-lng="tempatibadah_lng"
                data-sbo-gps-status="{{ $gpsStatusId }}">
                <span class="material-symbols-outlined text-[16px]">my_location</span>
                <span>GPS</span>
            </button>
        </div>
        <span class="sr-only" id="{{ $gpsStatusId }}"></span>
        @error('lat')<span class="{{ $errClass }} sm:col-span-2">{{ $message }}</span>@enderror
        @error('lng')<span class="{{ $errClass }} sm:col-span-2">{{ $message }}</span>@enderror
    </div>

    {{-- Kapasitas & kontak --}}
    <x-sbo-form-divider icon="groups" title="Kapasitas & Kontak" />
    <div>
        <label for="kappengunjung" class="{{ $labelClass }}">Kapasitas Pengunjung</label>
        <input type="number" min="0" name="kappengunjung" id="kappengunjung"
            class="{{ $inputBase }}{{ $errors->has('kappengunjung') ? $invalidBorder : '' }}"
            value="{{ old('kappengunjung', optional($data ?? null)->kappengunjung ?? 0) }}" placeholder="0">
        @error('kappengunjung')<span class="{{ $errClass }}">{{ $message }}</span>@enderror
    </div>
    <div>
        <label for="kapparkir" class="{{ $labelClass }}">Kapasitas Parkir</label>
        <input type="number" min="0" name="kapparkir" id="kapparkir"
            class="{{ $inputBase }}{{ $errors->has('kapparkir') ? $invalidBorder : '' }}"
            value="{{ old('kapparkir', optional($data ?? null)->kapparkir ?? 0) }}" placeholder="0">
        @error('kapparkir')<span class="{{ $errClass }}">{{ $message }}</span>@enderror
    </div>
    <div>
        <label for="namecp" class="{{ $labelClass }}">Nama CP</label>
        <input type="text" name="namecp" id="namecp"
            class="{{ $inputBase }}{{ $errors->has('namecp') ? $invalidBorder : '' }}"
            value="{{ old('namecp', optional($data ?? null)->namecp) }}" placeholder="Pengelola / penanggung jawab">
        @error('namecp')<span class="{{ $errClass }}">{{ $message }}</span>@enderror
    </div>
    <div>
        <label for="cp" class="{{ $labelClass }}">No. HP / Telepon</label>
        <input type="text" name="cp" id="cp"
            class="{{ $inputBase }}{{ $errors->has('cp') ? $invalidBorder : '' }}"
            value="{{ old('cp', optional($data ?? null)->cp) }}" placeholder="08xx-xxxx-xxxx">
        @error('cp')<span class="{{ $errClass }}">{{ $message }}</span>@enderror
    </div>

    {{-- Keamanan --}}
    <x-sbo-form-divider icon="shield" title="Keamanan" />
    @include('_partials.sbo_checkbox_group', [
        'label' => 'Alat Keamanan',
        'fieldName' => 'alatkeamanan',
        'options' => $alatKeamananOptions,
        'selected' => $alatKeamananSelected,
        'idPrefix' => $alatIdPrefix,
        'hint' => 'Pilih satu atau lebih sesuai ketersediaan di tempat ibadah.',
        'labelClass' => $labelClass,
        'errClass' => $errClass,
    ])

    {{-- Foto --}}
    <x-sbo-form-divider icon="add_a_photo" title="Dokumentasi Foto" />
    <div class="sm:col-span-2">
        <label for="{{ $fileInputId }}" class="{{ $labelClass }}">
            Foto Tempat Ibadah
            @unless($isCreate)
                <span class="normal-case font-normal text-slate-400">(opsional)</span>
            @endunless
        </label>
        @if(!$isCreate && method_exists($data, 'fotoUrl') && $data->fotoUrl())
            <a href="{{ $data->fotoUrl() }}" target="_blank" rel="noopener noreferrer" class="block mb-2">
                <img src="{{ $data->fotoUrl() }}" alt="Foto tempat ibadah" class="rounded-lg border border-slate-200 max-h-[120px] object-cover">
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
