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
    $formSuffix = $isCreate ? 'create' : 'edit';
    $fileInputId = 'datacctv_foto_'.$formSuffix;
    $previewId = 'datacctv_foto_preview_'.$formSuffix;
    $previewWrapId = 'datacctv_foto_preview_wrap_'.$formSuffix;
    $gpsStatusId = 'gps_status_datacctv_'.$formSuffix;
    $latId = 'datacctv_lat_'.$formSuffix;
    $lngId = 'datacctv_lng_'.$formSuffix;
@endphp

<div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
    <div>
        <label for="tanggal" class="{{ $labelClass }}">Tanggal <span class="text-red-500">*</span></label>
        <x-sbo-date-input :value="optional($data ?? null)->tanggal ?? ($isCreate ? date('Y-m-d') : null)" :show-label="false" :default-today="$isCreate" required
            class="{{ $inputBase }}{{ $errors->has('tanggal') ? $invalidBorder : '' }}" />
    </div>
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
        <label for="ipaddres" class="{{ $labelClass }}">IP Address <span class="text-red-500">*</span></label>
        <input type="text" name="ipaddres" id="ipaddres" required
            class="{{ $inputBase }}{{ $errors->has('ipaddres') ? $invalidBorder : '' }}"
            value="{{ old('ipaddres', optional($data ?? null)->ipaddres) }}" placeholder="Contoh: 10.0.0.1">
        @error('ipaddres')<span class="{{ $errClass }}">{{ $message }}</span>@enderror
    </div>
    <div>
        <label for="brand" class="{{ $labelClass }}">Brand <span class="text-red-500">*</span></label>
        <input type="text" name="brand" id="brand" required
            class="{{ $inputBase }}{{ $errors->has('brand') ? $invalidBorder : '' }}"
            value="{{ old('brand', optional($data ?? null)->brand) }}" placeholder="Contoh: Hikvision">
        @error('brand')<span class="{{ $errClass }}">{{ $message }}</span>@enderror
    </div>

    <x-sbo-form-divider icon="info" title="Detail CCTV" class="!pt-0 !border-t-0" />
    <div class="sm:col-span-2">
        <label for="lokasi" class="{{ $labelClass }}">Lokasi</label>
        <input type="text" name="lokasi" id="lokasi"
            class="{{ $inputBase }}{{ $errors->has('lokasi') ? $invalidBorder : '' }}"
            value="{{ old('lokasi', optional($data ?? null)->lokasi) }}" placeholder="Contoh: Simpang A.Yani"
            data-sbo-jalan-ac>
        @error('lokasi')<span class="{{ $errClass }}">{{ $message }}</span>@enderror
    </div>
    <div class="sm:col-span-2">
        <label for="fungsicctv" class="{{ $labelClass }}">Fungsi CCTV</label>
        <div class="relative">
            <select name="fungsicctv" id="fungsicctv" class="{{ $selectBase }}{{ $errors->has('fungsicctv') ? $invalidBorder : '' }}">
                <option value="">— Pilih —</option>
                @foreach($fungsiList ?? [] as $f)
                    <option value="{{ $f }}" {{ old('fungsicctv', optional($data ?? null)->fungsicctv) === $f ? 'selected' : '' }}>{{ $f }}</option>
                @endforeach
            </select>
            <span class="absolute right-2.5 top-1/2 -translate-y-1/2 material-symbols-outlined text-slate-400 text-[16px] pointer-events-none">expand_more</span>
        </div>
        @error('fungsicctv')<span class="{{ $errClass }}">{{ $message }}</span>@enderror
    </div>

    <x-sbo-form-divider icon="location_on" title="Koordinat GPS" />
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
        <p class="text-[11px] text-slate-400 mt-0 mb-0 sm:col-span-2" id="{{ $gpsStatusId }}" aria-live="polite"></p>
        @error('lat')<span class="{{ $errClass }} sm:col-span-2">{{ $message }}</span>@enderror
        @error('lng')<span class="{{ $errClass }} sm:col-span-2">{{ $message }}</span>@enderror
    </div>

    <x-sbo-form-divider icon="add_a_photo" title="Foto Fisik CCTV" />
    <div class="sm:col-span-2">
        <label for="{{ $fileInputId }}" class="{{ $labelClass }}">
            Foto Fisik CCTV
            @unless($isCreate)
                <span class="normal-case font-normal text-slate-400">(opsional)</span>
            @endunless
        </label>
        @if(!$isCreate)
            @php
                $fotoUrl = null;
                $fotoPath = $data->fotofisik ?? $data->fotosit ?? null;
                if (!empty($fotoPath)) {
                    $first = trim(explode(',', (string) $fotoPath)[0]);
                    $fotoUrl = preg_match('#^https?://#i', $first)
                        ? $first
                        : (str_starts_with($first, '/storage/') ? $first : \Illuminate\Support\Facades\Storage::disk('public')->url($first));
                }
            @endphp
            @if($fotoUrl)
                <a href="{{ $fotoUrl }}" target="_blank" rel="noopener noreferrer" class="block mb-2">
                    <img src="{{ $fotoUrl }}" alt="Foto CCTV" class="rounded-lg border border-slate-200 max-h-[120px] object-cover">
                </a>
            @endif
        @endif
        <label for="{{ $fileInputId }}"
            class="flex items-center justify-center gap-2 w-full rounded-lg border border-dashed border-slate-200 bg-slate-50/80 hover:border-primary/40 hover:bg-primary/5 px-3 py-3 cursor-pointer transition-all{{ $errors->has('foto_fisik') ? ' border-red-300 bg-red-50/50' : '' }}">
            <span class="material-symbols-outlined text-[22px] text-primary/70">add_a_photo</span>
            <span class="text-[12px] font-semibold text-slate-600">Ambil / unggah foto</span>
        </label>
        <input type="file" name="foto_fisik" id="{{ $fileInputId }}" class="visually-hidden"
            accept="image/jpeg,image/png,image/jpg,image/gif,image/webp" capture="environment"
            data-sbo-media-preview
            data-sbo-foto-gps
            data-sbo-foto-gps-lat="{{ $latId }}"
            data-sbo-foto-gps-lng="{{ $lngId }}"
            data-sbo-foto-gps-coord-status="{{ $gpsStatusId }}"
            data-sbo-preview-img="#{{ $previewId }}"
            data-sbo-preview-wrap="#{{ $previewWrapId }}"
            data-sbo-max-mb="8">
        <p class="text-[11px] text-slate-400 mt-1 mb-0">JPG, PNG, WebP, atau GIF — maks. 8 MB. Koordinat GPS dapat di-watermark pada foto.</p>
        @error('foto_fisik')<span class="{{ $errClass }}">{{ $message }}</span>@enderror
        <div class="mt-2 d-none rounded-lg border border-slate-200 bg-slate-50 p-2" id="{{ $previewWrapId }}">
            <p class="text-[10px] font-bold text-slate-500 uppercase tracking-wide mb-1.5 m-0">Pratinjau</p>
            <img id="{{ $previewId }}" src="" alt="Pratinjau foto CCTV" class="rounded-md border border-slate-200 max-h-[220px] w-full object-contain d-none">
        </div>
    </div>
</div>
