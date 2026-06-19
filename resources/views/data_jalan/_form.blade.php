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
    $prasjalOptions = $prasjalOptions ?? [];
    if (is_array(old('prasjal'))) {
        $prasjalSelected = old('prasjal');
    } elseif (!$isCreate) {
        $prasjalSelected = collect(preg_split('/\s*,\s*/', (string) old('prasjal', $data->prasjal ?? ''), -1, PREG_SPLIT_NO_EMPTY))
            ->map(fn ($s) => trim((string) $s))
            ->filter()
            ->values()
            ->all();
    } else {
        $prasjalSelected = [];
    }
    $fileInputId = $isCreate ? 'datajalan_foto_create' : 'datajalan_foto_edit';
    $previewId = $isCreate ? 'datajalan_foto_preview_create' : 'datajalan_foto_preview_edit';
    $previewWrapId = $isCreate ? 'datajalan_foto_preview_wrap_create' : 'datajalan_foto_preview_wrap_edit';
    $gpsStatusId = $isCreate ? 'gps_status_datajalan_create' : 'gps_status_datajalan_edit';
    $latId = $isCreate ? 'datajalan_lat_create' : 'datajalan_lat_edit';
    $lngId = $isCreate ? 'datajalan_lng_create' : 'datajalan_lng_edit';
    $prasIdPrefix = $isCreate ? 'datajalan_pras_create' : 'datajalan_pras_edit';
    $existingFotoUrl = (!$isCreate && is_object($data ?? null)) ? ($data->fotoObjectUrl() ?? null) : null;
@endphp

<div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
    <div>
        <label for="tanggal" class="{{ $labelClass }}">Tanggal <span class="text-red-500">*</span></label>
        <x-sbo-date-input :value="optional($data ?? null)->tanggal ?? ($isCreate ? date('Y-m-d') : null)" :show-label="false" :default-today="$isCreate" required
            class="{{ $inputBase }}{{ $errors->has('tanggal') ? $invalidBorder : '' }}" />
    </div>
    <div>
        <label for="jenisjalan" class="{{ $labelClass }}">Jenis Jalan <span class="text-red-500">*</span></label>
        <div class="relative">
            <select name="jenisjalan" id="jenisjalan" required class="{{ $selectBase }}{{ $errors->has('jenisjalan') ? $invalidBorder : '' }}">
                <option value="">— Pilih —</option>
                @foreach($jenisJalan ?? [] as $j)
                    <option value="{{ $j }}" {{ old('jenisjalan', optional($data ?? null)->jenisjalan) === $j ? 'selected' : '' }}>{{ $j }}</option>
                @endforeach
            </select>
            <span class="absolute right-2.5 top-1/2 -translate-y-1/2 material-symbols-outlined text-slate-400 text-[16px] pointer-events-none">expand_more</span>
        </div>
        @error('jenisjalan')<span class="{{ $errClass }}">{{ $message }}</span>@enderror
    </div>
    <div class="sm:col-span-2">
        <label for="namajalan" class="{{ $labelClass }}">Nama Jalan <span class="text-red-500">*</span></label>
        <input type="text" name="namajalan" id="namajalan" required maxlength="100"
            class="{{ $inputBase }}{{ $errors->has('namajalan') ? $invalidBorder : '' }}"
            value="{{ old('namajalan', optional($data ?? null)->namajalan) }}" placeholder="Nama ruas jalan">
        @error('namajalan')<span class="{{ $errClass }}">{{ $message }}</span>@enderror
    </div>
    <div>
        <label for="panjangjalan" class="{{ $labelClass }}">Panjang Jalan (km) <span class="text-red-500">*</span></label>
        <input type="number" name="panjangjalan" id="panjangjalan" required min="0" max="99999"
            class="{{ $inputBase }}{{ $errors->has('panjangjalan') ? $invalidBorder : '' }}"
            value="{{ old('panjangjalan', optional($data ?? null)->panjangjalan ?? 0) }}">
        @error('panjangjalan')<span class="{{ $errClass }}">{{ $message }}</span>@enderror
    </div>
    <div>
        <label for="lintasan" class="{{ $labelClass }}">Lintasan <span class="text-red-500">*</span></label>
        <div class="relative">
            <select name="lintasan" id="lintasan" required class="{{ $selectBase }}{{ $errors->has('lintasan') ? $invalidBorder : '' }}">
                <option value="">— Pilih —</option>
                @foreach($lintasanOptions ?? [] as $l)
                    <option value="{{ $l }}" {{ old('lintasan', optional($data ?? null)->lintasan) === $l ? 'selected' : '' }}>{{ $l }}</option>
                @endforeach
            </select>
            <span class="absolute right-2.5 top-1/2 -translate-y-1/2 material-symbols-outlined text-slate-400 text-[16px] pointer-events-none">expand_more</span>
        </div>
        @error('lintasan')<span class="{{ $errClass }}">{{ $message }}</span>@enderror
    </div>
    <div class="sm:col-span-2">
        <span class="{{ $labelClass }}">Prasarana Jalan</span>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-2 mt-1">
            @foreach($prasjalOptions as $p)
                <label for="{{ $prasIdPrefix }}_{{ $loop->index }}"
                    class="flex items-start gap-2 rounded-lg border border-slate-200 bg-slate-50/80 px-3 py-2 cursor-pointer hover:border-primary/30 hover:bg-primary/5 transition-all">
                    <input type="checkbox" name="prasjal[]" id="{{ $prasIdPrefix }}_{{ $loop->index }}" value="{{ $p }}"
                        class="mt-0.5 rounded border-slate-300 text-primary focus:ring-primary/30 shrink-0"
                        @checked(in_array($p, $prasjalSelected, true))>
                    <span class="text-[12px] font-medium text-slate-700 leading-snug">{{ $p }}</span>
                </label>
            @endforeach
        </div>
        <p class="text-[11px] text-slate-400 mt-1 mb-0">Pilih prasarana yang tersedia di ruas jalan.</p>
        @error('prasjal')<span class="{{ $errClass }}">{{ $message }}</span>@enderror
        @error('prasjal.*')<span class="{{ $errClass }}">{{ $message }}</span>@enderror
    </div>
    <div class="sm:col-span-2 grid grid-cols-1 sm:grid-cols-[1fr_1fr_auto] gap-2 items-end">
        <div>
            <label for="{{ $latId }}" class="{{ $labelClass }}">Latitude</label>
            <input type="number" step="any" name="lat" id="{{ $latId }}"
                class="{{ $inputBase }}{{ $errors->has('lat') ? $invalidBorder : '' }}"
                value="{{ $latVal }}" placeholder="-7.xxx" autocomplete="off">
        </div>
        <div>
            <label for="{{ $lngId }}" class="{{ $labelClass }}">Longitude</label>
            <input type="number" step="any" name="lng" id="{{ $lngId }}"
                class="{{ $inputBase }}{{ $errors->has('lng') ? $invalidBorder : '' }}"
                value="{{ $lngVal }}" placeholder="110.xxx" autocomplete="off">
        </div>
        <div>
            <button type="button"
                class="w-full flex items-center justify-center gap-1.5 px-3 py-2 bg-amber-50 hover:bg-amber-100 text-amber-800 border border-amber-200 font-bold rounded-lg text-[12px] transition-all border-solid whitespace-nowrap"
                data-sbo-gps-lat="{{ $latId }}"
                data-sbo-gps-lng="{{ $lngId }}"
                data-sbo-gps-status="{{ $gpsStatusId }}">
                <span class="material-symbols-outlined text-[16px]">my_location</span>
                <span>GPS</span>
            </button>
        </div>
        <span class="sr-only" id="{{ $gpsStatusId }}"></span>
        @error('lat')<span class="{{ $errClass }} sm:col-span-2">{{ $message }}</span>@enderror
        @error('lng')<span class="{{ $errClass }} sm:col-span-2">{{ $message }}</span>@enderror
    </div>
    <div class="sm:col-span-2">
        <label for="{{ $fileInputId }}" class="{{ $labelClass }}">
            Foto Lokasi
            @unless($isCreate)
                <span class="normal-case font-normal text-slate-400">(opsional)</span>
            @endunless
        </label>
        @if($existingFotoUrl)
            <a href="{{ $existingFotoUrl }}" target="_blank" rel="noopener noreferrer" class="block mb-2">
                <img src="{{ $existingFotoUrl }}" alt="Foto jalan" class="rounded-lg border border-slate-200 max-h-[120px] object-cover">
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
            data-sbo-foto-gps
            data-sbo-foto-gps-lat="{{ $latId }}"
            data-sbo-foto-gps-lng="{{ $lngId }}"
            data-sbo-foto-gps-coord-status="{{ $gpsStatusId }}"
            data-sbo-preview-img="#{{ $previewId }}"
            data-sbo-preview-wrap="#{{ $previewWrapId }}"
            data-sbo-max-mb="8">
        @error('foto_lokasi')<span class="{{ $errClass }}">{{ $message }}</span>@enderror
        <div class="mt-2 d-none rounded-lg border border-slate-200 bg-slate-50 p-2" id="{{ $previewWrapId }}">
            <img id="{{ $previewId }}" src="" alt="Pratinjau" class="rounded-md border border-slate-200 max-h-[180px] w-full object-contain d-none">
        </div>
    </div>
</div>
