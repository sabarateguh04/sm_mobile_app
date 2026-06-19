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
    $fileInputId = $isCreate ? 'gangguan_foto_create' : 'gangguan_foto_edit';
    $previewId = $isCreate ? 'gangguan_foto_preview_create' : 'gangguan_foto_preview_edit';
    $previewWrapId = $isCreate ? 'gangguan_foto_preview_wrap_create' : 'gangguan_foto_preview_wrap_edit';
    $gpsStatusId = $isCreate ? 'gps_status_gangguan_create' : 'gps_status_gangguan_edit';
    $latId = $isCreate ? 'gangguan_lat_create' : 'gangguan_lat_edit';
    $lngId = $isCreate ? 'gangguan_lng_create' : 'gangguan_lng_edit';
@endphp

<div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
    <div>
        <label for="tanggal" class="{{ $labelClass }}">Tanggal <span class="text-red-500">*</span></label>
        <x-sbo-date-input :value="optional($data ?? null)->tanggal ?? ($isCreate ? date('Y-m-d') : null)" :show-label="false" :default-today="$isCreate" required
            class="{{ $inputBase }}{{ $errors->has('tanggal') ? $invalidBorder : '' }}" />
    </div>
    <div>
        <label for="fungsijalan" class="{{ $labelClass }}">Fungsi Jalan <span class="text-red-500">*</span></label>
        <div class="relative">
            <select name="fungsijalan" id="fungsijalan" required class="{{ $selectBase }}{{ $errors->has('fungsijalan') ? $invalidBorder : '' }}">
                <option value="">— Pilih —</option>
                @foreach($jenisJalan ?? [] as $j)
                    <option value="{{ $j }}" {{ old('fungsijalan', optional($data ?? null)->fungsijalan) === $j ? 'selected' : '' }}>{{ $j }}</option>
                @endforeach
            </select>
            <span class="absolute right-2.5 top-1/2 -translate-y-1/2 material-symbols-outlined text-slate-400 text-[16px] pointer-events-none">expand_more</span>
        </div>
        @error('fungsijalan')<span class="{{ $errClass }}">{{ $message }}</span>@enderror
    </div>
    <div class="sm:col-span-2">
        <label for="namajalan" class="{{ $labelClass }}">Nama Jalan <span class="text-red-500">*</span></label>
        <input type="text" name="namajalan" id="namajalan" required
            class="{{ $inputBase }}{{ $errors->has('namajalan') ? $invalidBorder : '' }}"
            value="{{ old('namajalan', optional($data ?? null)->namajalan) }}" placeholder="Nama ruas jalan"
            data-sbo-jalan-ac>
        @error('namajalan')<span class="{{ $errClass }}">{{ $message }}</span>@enderror
    </div>
    <div>
        <label for="kerawanan" class="{{ $labelClass }}">Kerawanan</label>
        <input type="text" name="kerawanan" id="kerawanan"
            class="{{ $inputBase }}{{ $errors->has('kerawanan') ? $invalidBorder : '' }}"
            value="{{ old('kerawanan', optional($data ?? null)->kerawanan) }}" placeholder="Black Spot / Rawan Tindak Pidana">
        @error('kerawanan')<span class="{{ $errClass }}">{{ $message }}</span>@enderror
    </div>
    <div>
        <label for="levelgangguan" class="{{ $labelClass }}">Level Gangguan</label>
        <div class="relative">
            <select name="levelgangguan" id="levelgangguan" class="{{ $selectBase }}{{ $errors->has('levelgangguan') ? $invalidBorder : '' }}">
                <option value="">— Pilih —</option>
                @foreach($levelGangguan ?? [] as $l)
                    <option value="{{ $l }}" {{ old('levelgangguan', optional($data ?? null)->levelgangguan) === $l ? 'selected' : '' }}>{{ $l }}</option>
                @endforeach
            </select>
            <span class="absolute right-2.5 top-1/2 -translate-y-1/2 material-symbols-outlined text-slate-400 text-[16px] pointer-events-none">expand_more</span>
        </div>
        @error('levelgangguan')<span class="{{ $errClass }}">{{ $message }}</span>@enderror
    </div>
    <div>
        <label for="faktor" class="{{ $labelClass }}">Faktor</label>
        <div class="relative">
            <select name="faktor" id="faktor" class="{{ $selectBase }}{{ $errors->has('faktor') ? $invalidBorder : '' }}">
                <option value="">— Pilih —</option>
                @foreach($faktor ?? [] as $f)
                    <option value="{{ $f }}" {{ old('faktor', optional($data ?? null)->faktor) === $f ? 'selected' : '' }}>{{ $f }}</option>
                @endforeach
            </select>
            <span class="absolute right-2.5 top-1/2 -translate-y-1/2 material-symbols-outlined text-slate-400 text-[16px] pointer-events-none">expand_more</span>
        </div>
        @error('faktor')<span class="{{ $errClass }}">{{ $message }}</span>@enderror
    </div>
    <div>
        <label for="penyebab" class="{{ $labelClass }}">Penyebab</label>
        <input type="text" name="penyebab" id="penyebab"
            class="{{ $inputBase }}{{ $errors->has('penyebab') ? $invalidBorder : '' }}"
            value="{{ old('penyebab', optional($data ?? null)->penyebab) }}">
        @error('penyebab')<span class="{{ $errClass }}">{{ $message }}</span>@enderror
    </div>
    <div class="sm:col-span-2">
        <label class="{{ $labelClass }}">Team Survayor / Petugas</label>
        @php
            $survayorVal = old('teamsurvayor', optional($data ?? null)->teamsurvayor ?? '');
            if (is_array($survayorVal)) {
                $survayorVal = implode(',', $survayorVal);
            }
        @endphp
        <div
            data-sbo-petugas-ac
            data-sbo-petugas-name="teamsurvayor"
            data-sbo-petugas-max="5"
            data-sbo-petugas-value="{{ $survayorVal }}"
        ></div>
        @error('teamsurvayor')<span class="{{ $errClass }}">{{ $message }}</span>@enderror
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
        @if(!$isCreate && $data->fotoLokasiUrl())
            <a href="{{ $data->fotoLokasiUrl() }}" target="_blank" rel="noopener noreferrer" class="block mb-2">
                <img src="{{ $data->fotoLokasiUrl() }}" alt="Foto lokasi" class="rounded-lg border border-slate-200 max-h-[120px] object-cover">
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
