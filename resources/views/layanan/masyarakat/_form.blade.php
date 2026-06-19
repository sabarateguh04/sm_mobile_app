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
@endphp

{{-- ── Seksi 1: Informasi Layanan ─────────────────────────────────────────── --}}
<div class="space-y-3">
    <div class="flex items-center gap-2">
        <span class="material-symbols-outlined text-[14px] text-primary" style="font-variation-settings:'FILL' 1;">campaign</span>
        <span class="text-[10px] font-black uppercase tracking-widest text-slate-500">Informasi Layanan</span>
        <div class="flex-1 h-px bg-slate-100"></div>
    </div>
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
        <div>
            <label for="tanggal" class="{{ $labelClass }}">Tanggal <span class="text-red-500">*</span></label>
            <x-sbo-date-input :value="optional($data ?? null)->tanggal ?? ($isCreate ? date('Y-m-d') : null)" :show-label="false" :default-today="$isCreate" required
                class="{{ $inputBase }}{{ $errors->has('tanggal') ? $invalidBorder : '' }}" />
        </div>

        <div>
            <label for="saluran" class="{{ $labelClass }}">Saluran</label>
            <div class="relative">
                <select name="saluran" id="saluran" class="{{ $selectBase }}{{ $errors->has('saluran') ? $invalidBorder : '' }}">
                    <option value="">— Pilih —</option>
                    @foreach($saluranList ?? [] as $sal)
                        <option value="{{ $sal }}" {{ old('saluran', optional($data ?? null)->saluran) === $sal ? 'selected' : '' }}>{{ $sal }}</option>
                    @endforeach
                </select>
                <span class="absolute right-2.5 top-1/2 -translate-y-1/2 material-symbols-outlined text-slate-400 text-[16px] pointer-events-none">expand_more</span>
            </div>
            @error('saluran')<span class="{{ $errClass }}">{{ $message }}</span>@enderror
        </div>

        <div id="kategori_wrap">
            <label for="kategori" class="{{ $labelClass }}">
                Kategori
                <span id="kategori_hint" class="normal-case font-normal text-slate-400 ml-1" style="font-size:9px;"></span>
            </label>
            <div class="relative">
                <select name="kategori" id="kategori"
                    class="{{ $selectBase }}{{ $errors->has('kategori') ? $invalidBorder : '' }}">
                    <option value="">— Pilih —</option>
                    @foreach($kategoriList ?? [] as $kat)
                        <option value="{{ $kat }}" {{ old('kategori', optional($data ?? null)->kategori) === $kat ? 'selected' : '' }}>{{ $kat }}</option>
                    @endforeach
                </select>
                <span class="absolute right-2.5 top-1/2 -translate-y-1/2 material-symbols-outlined text-slate-400 text-[16px] pointer-events-none">expand_more</span>
            </div>
            @error('kategori')<span class="{{ $errClass }}">{{ $message }}</span>@enderror
        </div>

        <div id="tema_wrap">
            <label for="tema" class="{{ $labelClass }}">
                Tema
                <span id="tema_hint" class="normal-case font-normal text-slate-400 ml-1" style="font-size:9px;">{{ old('kategori', optional($data ?? null)->kategori) ? '' : '— pilih kategori dulu' }}</span>
            </label>
            <div class="relative">
                <select name="tema" id="tema"
                    class="{{ $selectBase }}{{ $errors->has('tema') ? $invalidBorder : '' }} disabled:opacity-50 disabled:cursor-not-allowed"
                    {{ old('kategori', optional($data ?? null)->kategori) ? '' : 'disabled' }}>
                    <option value="">— Pilih —</option>
                    @foreach($temaList ?? [] as $temaOpt)
                        <option value="{{ $temaOpt }}" {{ old('tema', optional($data ?? null)->tema) === $temaOpt ? 'selected' : '' }}>{{ $temaOpt }}</option>
                    @endforeach
                </select>
                <span class="absolute right-2.5 top-1/2 -translate-y-1/2 material-symbols-outlined text-slate-400 text-[16px] pointer-events-none">expand_more</span>
            </div>
            @error('tema')<span class="{{ $errClass }}">{{ $message }}</span>@enderror
        </div>

        <div class="sm:col-span-2">
            <label for="keterangan" class="{{ $labelClass }}">Keterangan</label>
            <textarea name="keterangan" id="keterangan" rows="2"
                class="{{ $inputBase }} resize-y min-h-[56px]{{ $errors->has('keterangan') ? $invalidBorder : '' }}"
                placeholder="Catatan tambahan mengenai layanan ini...">{{ old('keterangan', optional($data ?? null)->keterangan) }}</textarea>
            @error('keterangan')<span class="{{ $errClass }}">{{ $message }}</span>@enderror
        </div>
    </div>
</div>

{{-- ── Seksi 2: Narasumber ─────────────────────────────────────────────────── --}}
<div class="space-y-3 pt-1">
    <div class="flex items-center gap-2">
        <span class="material-symbols-outlined text-[14px] text-primary" style="font-variation-settings:'FILL' 1;">person</span>
        <span class="text-[10px] font-black uppercase tracking-widest text-slate-500">Narasumber</span>
        <div class="flex-1 h-px bg-slate-100"></div>
    </div>
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
        <div>
            <label for="narsum" class="{{ $labelClass }}">Nama Narasumber</label>
            <input type="text" name="narsum" id="narsum"
                class="{{ $inputBase }}{{ $errors->has('narsum') ? $invalidBorder : '' }}"
                value="{{ old('narsum', optional($data ?? null)->narsum) }}" placeholder="Nama narasumber">
            @error('narsum')<span class="{{ $errClass }}">{{ $message }}</span>@enderror
        </div>

        <div>
            <label for="telp_narsum" class="{{ $labelClass }}">Nomor Telepon</label>
            <input type="text" name="telp_narsum" id="telp_narsum"
                class="{{ $inputBase }}{{ $errors->has('telp_narsum') ? $invalidBorder : '' }}"
                value="{{ old('telp_narsum', optional($data ?? null)->telp_narsum) }}" placeholder="Nomor telepon">
            @error('telp_narsum')<span class="{{ $errClass }}">{{ $message }}</span>@enderror
        </div>
    </div>
</div>

{{-- ── Seksi 3: Lokasi & Dokumentasi ──────────────────────────────────────── --}}
<div class="space-y-3 pt-1">
    <div class="flex items-center gap-2">
        <span class="material-symbols-outlined text-[14px] text-primary" style="font-variation-settings:'FILL' 1;">location_on</span>
        <span class="text-[10px] font-black uppercase tracking-widest text-slate-500">Lokasi & Dokumentasi</span>
        <div class="flex-1 h-px bg-slate-100"></div>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-[1fr_1fr_auto] gap-2 items-end">
        <div>
            <label for="interaksi_lat" class="{{ $labelClass }}">Latitude</label>
            <input type="number" step="any" name="lat" id="interaksi_lat"
                class="{{ $inputBase }}{{ $errors->has('lat') ? $invalidBorder : '' }}"
                value="{{ $latVal }}" placeholder="-7.xxx" autocomplete="off">
        </div>
        <div>
            <label for="interaksi_lng" class="{{ $labelClass }}">Longitude</label>
            <input type="number" step="any" name="lng" id="interaksi_lng"
                class="{{ $inputBase }}{{ $errors->has('lng') ? $invalidBorder : '' }}"
                value="{{ $lngVal }}" placeholder="110.xxx" autocomplete="off">
        </div>
        <div>
            <button type="button"
                class="w-full flex items-center justify-center gap-1.5 px-3 py-2 bg-amber-50 hover:bg-amber-100 text-amber-800 border border-amber-200 font-bold rounded-lg text-[12px] transition-all border-solid whitespace-nowrap"
                data-sbo-gps-lat="interaksi_lat"
                data-sbo-gps-lng="interaksi_lng"
                data-sbo-gps-status="gps_status_interaksi">
                <span class="material-symbols-outlined text-[16px]">my_location</span>
                <span>GPS</span>
            </button>
        </div>
        <span class="sr-only" id="gps_status_interaksi"></span>
        <input type="hidden" name="koordinat" id="interaksi_koordinat" value="{{ old('koordinat', optional($data ?? null)->koordinat) }}">
        @error('lat')<span class="{{ $errClass }} sm:col-span-3">{{ $message }}</span>@enderror
        @error('lng')<span class="{{ $errClass }} sm:col-span-3">{{ $message }}</span>@enderror
    </div>

    <div>
        <label class="{{ $labelClass }}">Foto Dokumentasi</label>
        @if(!empty($data->foto ?? null))
            <img src="{{ $data->foto }}" alt="Foto saat ini" class="mb-2 rounded-lg border border-slate-200 max-h-[120px] object-cover">
        @endif
        <label for="interaksi_foto"
            class="flex items-center justify-center gap-2 w-full rounded-lg border border-dashed border-slate-200 bg-slate-50/80 hover:border-primary/40 hover:bg-primary/5 px-3 py-3 cursor-pointer transition-all{{ $errors->has('foto_file') ? ' border-red-300 bg-red-50/50' : '' }}">
            <span class="material-symbols-outlined text-[22px] text-primary/70">add_a_photo</span>
            <span class="text-[12px] font-semibold text-slate-600">Ambil / unggah foto</span>
        </label>
        <input type="file" name="foto_file" id="interaksi_foto" class="visually-hidden"
            accept="image/*" capture="environment"
            data-sbo-media-preview
            data-sbo-foto-gps
            data-sbo-foto-gps-lat="interaksi_lat"
            data-sbo-foto-gps-lng="interaksi_lng"
            data-sbo-foto-gps-coord-status="gps_status_interaksi"
            data-sbo-preview-img="#interaksi_foto_preview"
            data-sbo-preview-wrap="#interaksi_foto_preview_wrap"
            data-sbo-max-mb="8">
        @error('foto_file')<span class="{{ $errClass }}">{{ $message }}</span>@enderror

        <div class="mt-2 d-none rounded-lg border border-slate-200 bg-slate-50 p-2" id="interaksi_foto_preview_wrap">
            <img id="interaksi_foto_preview" src="" alt="Pratinjau" class="rounded-md border border-slate-200 max-h-[180px] w-full object-contain d-none">
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    /* ── Koordinat: gabung lat+lng ke hidden field saat submit ── */
    var koord = document.getElementById('interaksi_koordinat');
    var form = koord ? koord.closest('form') : null;
    if (form) {
        form.addEventListener('submit', function () {
            var lat = document.getElementById('interaksi_lat');
            var lng = document.getElementById('interaksi_lng');
            if (lat && lng && koord && lat.value && lng.value) {
                koord.value = lat.value + ',' + lng.value;
            }
        });
    }

    /* ── Cascade: Kategori → Tema (saluran independen) ── */
    var selSaluran  = document.getElementById('saluran');
    var selKategori = document.getElementById('kategori');
    var selTema     = document.getElementById('tema');
    var hintKategori = document.getElementById('kategori_hint');
    var hintTema     = document.getElementById('tema_hint');
    var wrapKategori = document.getElementById('kategori_wrap');
    var wrapTema     = document.getElementById('tema_wrap');

    if (!selSaluran || !selKategori || !selTema) return;

    var cascadeMap = @json($cascadeMap ?? ['saluranKategori' => [], 'kategoriTema' => []]);
    
    // Simpan daftar opsi awal (clone)
    var allKategoriOptions = Array.from(selKategori.options).map(o => ({val: o.value, text: o.text}));
    var allTemaOptions = Array.from(selTema.options).map(o => ({val: o.value, text: o.text}));

    function setEnabled(sel, hint, enabled, hintText) {
        sel.disabled = !enabled;
        sel.classList.toggle('opacity-50', !enabled);
        sel.classList.toggle('cursor-not-allowed', !enabled);
        if (hint) hint.textContent = enabled ? '' : hintText;
    }

    function resetSelect(sel) {
        sel.value = '';
    }

    function filterOptions(select, allOptions, parentValue, mapDict) {
        var currentValue = select.value;
        select.innerHTML = '';
        select.appendChild(new Option('— Pilih —', ''));

        var allowedValues = mapDict[parentValue];
        // Jika allowedValues terdefinisi & ada isinya, berarti ada pemetaan sub-opsi
        var hasFilter = allowedValues !== undefined && allowedValues.length > 0;

        allOptions.forEach(function(opt) {
            if (opt.val === '') return;
            // Tampilkan jika cocok dengan filter, ATAU jika tidak ada filter (tampilkan semua)
            if (!hasFilter || allowedValues.includes(opt.val)) {
                select.appendChild(new Option(opt.text, opt.val));
            }
        });

        // Restore nilai jika masih valid di daftar baru
        var stillValid = Array.from(select.options).some(o => o.value === currentValue && currentValue !== '');
        select.value = stillValid ? currentValue : '';
    }

    selKategori.addEventListener('change', function () {
        var hasKategori = this.value.trim() !== '';
        if (!hasKategori) {
            resetSelect(selTema);
            setEnabled(selTema, hintTema, false, '— pilih kategori dulu');
        } else {
            filterOptions(selTema, allTemaOptions, this.value, cascadeMap.kategoriTema);
            setEnabled(selTema, hintTema, true, '');
        }
    });

    // Kategori terbuka sejak awal; tema mengikuti kategori
    setEnabled(selKategori, hintKategori, true, '');
    setEnabled(selTema, hintTema, selKategori.value.trim() !== '', '— pilih kategori dulu');

    if (selKategori.value) {
        filterOptions(selTema, allTemaOptions, selKategori.value, cascadeMap.kategoriTema);
        selTema.value = @json(old('tema', optional($data ?? null)->tema));
        setEnabled(selTema, hintTema, true, '');
    }
});
</script>
@endpush
