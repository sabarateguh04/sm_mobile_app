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
    $fileInputId = $isCreate ? 'terminal_foto_create' : 'terminal_foto_edit';
    $previewId = $isCreate ? 'terminal_foto_preview_create' : 'terminal_foto_preview_edit';
    $previewWrapId = $isCreate ? 'terminal_foto_preview_wrap_create' : 'terminal_foto_preview_wrap_edit';
    $gpsStatusId = $isCreate ? 'gps_status_terminal_create' : 'gps_status_terminal_edit';
    $fasIdPrefix = $isCreate ? 'terminal_fas_create' : 'terminal_fas_edit';
@endphp

<div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
    <div>
        <label for="tanggal" class="{{ $labelClass }}">Tanggal <span class="text-red-500">*</span></label>
        <x-sbo-date-input :value="optional($data ?? null)->tanggal ?? ($isCreate ? date('Y-m-d') : null)" :show-label="false" :default-today="$isCreate" required
            class="{{ $inputBase }}{{ $errors->has('tanggal') ? $invalidBorder : '' }}" />
    </div>
    <div>
        <label for="jenisterminal" class="{{ $labelClass }}">Jenis Terminal <span class="text-red-500">*</span></label>
        <div class="relative">
            <select name="jenisterminal" id="jenisterminal" required class="{{ $selectBase }}{{ $errors->has('jenisterminal') ? $invalidBorder : '' }}">
                <option value="">— Pilih —</option>
                @foreach($jenisTerminal ?? [] as $j)
                    <option value="{{ $j }}" {{ old('jenisterminal', optional($data ?? null)->jenisterminal) === $j ? 'selected' : '' }}>{{ $j }}</option>
                @endforeach
            </select>
            <span class="absolute right-2.5 top-1/2 -translate-y-1/2 material-symbols-outlined text-slate-400 text-[16px] pointer-events-none">expand_more</span>
        </div>
        @error('jenisterminal')<span class="{{ $errClass }}">{{ $message }}</span>@enderror
    </div>
    <div class="sm:col-span-2">
        <label for="namaterminal" class="{{ $labelClass }}">Nama Terminal <span class="text-red-500">*</span></label>
        <input type="text" name="namaterminal" id="namaterminal" required
            class="{{ $inputBase }}{{ $errors->has('namaterminal') ? $invalidBorder : '' }}"
            value="{{ old('namaterminal', optional($data ?? null)->namaterminal) }}" placeholder="Nama terminal / stasiun / bandara">
        @error('namaterminal')<span class="{{ $errClass }}">{{ $message }}</span>@enderror
    </div>
    <div>
        <label for="namecp" class="{{ $labelClass }}">Nama CP</label>
        <input type="text" name="namecp" id="namecp"
            class="{{ $inputBase }}{{ $errors->has('namecp') ? $invalidBorder : '' }}"
            value="{{ old('namecp', optional($data ?? null)->namecp) }}" placeholder="Contact person">
        @error('namecp')<span class="{{ $errClass }}">{{ $message }}</span>@enderror
    </div>
    <div>
        <label for="cp" class="{{ $labelClass }}">No. CP</label>
        <input type="text" name="cp" id="cp"
            class="{{ $inputBase }}{{ $errors->has('cp') ? $invalidBorder : '' }}"
            value="{{ old('cp', optional($data ?? null)->cp) }}" placeholder="Nomor telepon">
        @error('cp')<span class="{{ $errClass }}">{{ $message }}</span>@enderror
    </div>
    <div class="sm:col-span-2">
        <label for="jalan" class="{{ $labelClass }}">Jalan / Alamat</label>
        <input type="text" name="jalan" id="jalan"
            class="{{ $inputBase }}{{ $errors->has('jalan') ? $invalidBorder : '' }}"
            value="{{ old('jalan', optional($data ?? null)->jalan) }}" placeholder="Alamat lengkap terminal"
            data-sbo-jalan-ac>
        @error('jalan')<span class="{{ $errClass }}">{{ $message }}</span>@enderror
    </div>
    <div class="sm:col-span-2">
        <span class="{{ $labelClass }}">Fasilitas</span>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-2 mt-1">
            @foreach($fasilitasOptions as $f)
                <label for="{{ $fasIdPrefix }}_{{ $loop->index }}"
                    class="flex items-start gap-2 rounded-lg border border-slate-200 bg-slate-50/80 px-3 py-2 cursor-pointer hover:border-primary/30 hover:bg-primary/5 transition-all">
                    <input type="checkbox" name="fasilitas[]" id="{{ $fasIdPrefix }}_{{ $loop->index }}" value="{{ $f }}"
                        class="mt-0.5 rounded border-slate-300 text-primary focus:ring-primary/30 shrink-0"
                        @checked(in_array($f, $fasilitasSelected, true))>
                    <span class="text-[12px] font-medium text-slate-700 leading-snug">{{ $f }}</span>
                </label>
            @endforeach
        </div>
        <p class="text-[11px] text-slate-400 mt-1 mb-0">Pilih satu atau lebih sesuai ketersediaan di terminal.</p>
        @error('fasilitas')<span class="{{ $errClass }}">{{ $message }}</span>@enderror
        @error('fasilitas.*')<span class="{{ $errClass }}">{{ $message }}</span>@enderror
    </div>
    <div class="sm:col-span-2 grid grid-cols-1 sm:grid-cols-[1fr_1fr_auto] gap-2 items-end">
        <div>
            <label for="terminal_lat" class="{{ $labelClass }}">Latitude</label>
            <input type="number" step="any" name="lat" id="terminal_lat"
                class="{{ $inputBase }}{{ $errors->has('lat') ? $invalidBorder : '' }}"
                value="{{ $latVal }}" placeholder="-7.xxx" autocomplete="off">
        </div>
        <div>
            <label for="terminal_lng" class="{{ $labelClass }}">Longitude</label>
            <input type="number" step="any" name="lng" id="terminal_lng"
                class="{{ $inputBase }}{{ $errors->has('lng') ? $invalidBorder : '' }}"
                value="{{ $lngVal }}" placeholder="110.xxx" autocomplete="off">
        </div>
        <div>
            <button type="button"
                class="w-full flex items-center justify-center gap-1.5 px-3 py-2 bg-amber-50 hover:bg-amber-100 text-amber-800 border border-amber-200 font-bold rounded-lg text-[12px] transition-all border-solid whitespace-nowrap"
                data-sbo-gps-lat="terminal_lat"
                data-sbo-gps-lng="terminal_lng"
                data-sbo-gps-status="{{ $gpsStatusId }}">
                <span class="material-symbols-outlined text-[16px]">my_location</span>
                <span>GPS</span>
            </button>
        </div>
        <p class="text-[11px] text-slate-400 mt-0 mb-0 sm:col-span-2" id="{{ $gpsStatusId }}" aria-live="polite"></p>
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
        @if(!$isCreate && method_exists($data, 'fotoUrl') && $data->fotoUrl())
            <a href="{{ $data->fotoUrl() }}" target="_blank" rel="noopener noreferrer" class="block mb-2">
                <img src="{{ $data->fotoUrl() }}" alt="Foto terminal" class="rounded-lg border border-slate-200 max-h-[120px] object-cover">
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
            data-sbo-foto-gps-lat="terminal_lat"
            data-sbo-foto-gps-lng="terminal_lng"
            data-sbo-foto-gps-coord-status="{{ $gpsStatusId }}"
            data-sbo-preview-img="#{{ $previewId }}"
            data-sbo-preview-wrap="#{{ $previewWrapId }}"
            data-sbo-max-mb="8">
        @error('foto_lokasi')<span class="{{ $errClass }}">{{ $message }}</span>@enderror
        <div class="mt-2 d-none rounded-lg border border-slate-200 bg-slate-50 p-2" id="{{ $previewWrapId }}">
            <p class="text-[10px] font-bold text-slate-500 uppercase tracking-wide mb-1.5 m-0">Pratinjau</p>
            <img id="{{ $previewId }}" src="" alt="Pratinjau foto lokasi" class="rounded-md border border-slate-200 max-h-[220px] w-full object-contain d-none">
        </div>
    </div>
</div>
