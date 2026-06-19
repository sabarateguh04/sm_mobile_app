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

<div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
    <div>
        <label for="tanggal" class="{{ $labelClass }}">Tanggal <span class="text-red-500">*</span></label>
        <x-sbo-date-input :value="optional($data ?? null)->tanggal ?? ($isCreate ? date('Y-m-d') : null)" :show-label="false" :default-today="$isCreate" required
            class="{{ $inputBase }}{{ $errors->has('tanggal') ? $invalidBorder : '' }}" />
    </div>

    <div>
        <label for="kategori_data" class="{{ $labelClass }}">Kategori Data</label>
        <div class="relative">
            <select name="kategori_data" id="kategori_data" class="{{ $selectBase }}{{ $errors->has('kategori_data') ? $invalidBorder : '' }}">
                <option value="">— Pilih —</option>
                @foreach($kategoriList ?? [] as $kat)
                    <option value="{{ $kat }}" {{ old('kategori_data', optional($data ?? null)->kategori_data) === $kat ? 'selected' : '' }}>{{ $kat }}</option>
                @endforeach
            </select>
            <span class="absolute right-2.5 top-1/2 -translate-y-1/2 material-symbols-outlined text-slate-400 text-[16px] pointer-events-none">expand_more</span>
        </div>
        @error('kategori_data')<span class="{{ $errClass }}">{{ $message }}</span>@enderror
    </div>

    <div>
        <label for="tujuan" class="{{ $labelClass }}">Tujuan</label>
        <input type="text" name="tujuan" id="tujuan"
            class="{{ $inputBase }}{{ $errors->has('tujuan') ? $invalidBorder : '' }}"
            value="{{ old('tujuan', optional($data ?? null)->tujuan) }}" placeholder="Tujuan permohonan">
        @error('tujuan')<span class="{{ $errClass }}">{{ $message }}</span>@enderror
    </div>

    <div>
        <label for="pemohon" class="{{ $labelClass }}">Pemohon</label>
        <input type="text" name="pemohon" id="pemohon"
            class="{{ $inputBase }}{{ $errors->has('pemohon') ? $invalidBorder : '' }}"
            value="{{ old('pemohon', optional($data ?? null)->pemohon) }}" placeholder="Nama pemohon">
        @error('pemohon')<span class="{{ $errClass }}">{{ $message }}</span>@enderror
    </div>

    <div class="sm:col-span-2">
        <label for="telp_pemohon" class="{{ $labelClass }}">Telp Pemohon</label>
        <input type="text" name="telp_pemohon" id="telp_pemohon"
            class="{{ $inputBase }}{{ $errors->has('telp_pemohon') ? $invalidBorder : '' }}"
            value="{{ old('telp_pemohon', optional($data ?? null)->telp_pemohon) }}" placeholder="Nomor telepon">
        @error('telp_pemohon')<span class="{{ $errClass }}">{{ $message }}</span>@enderror
    </div>

    <div class="sm:col-span-2">
        <label for="keterangan" class="{{ $labelClass }}">Keterangan</label>
        <textarea name="keterangan" id="keterangan" rows="2"
            class="{{ $inputBase }} resize-y min-h-[64px]{{ $errors->has('keterangan') ? $invalidBorder : '' }}"
            placeholder="Catatan">{{ old('keterangan', optional($data ?? null)->keterangan) }}</textarea>
        @error('keterangan')<span class="{{ $errClass }}">{{ $message }}</span>@enderror
    </div>

    <div class="sm:col-span-2 grid grid-cols-1 sm:grid-cols-[1fr_1fr_auto] gap-2 items-end">
        <div>
            <label for="laydata_lat" class="{{ $labelClass }}">Latitude</label>
            <input type="number" step="any" name="lat" id="laydata_lat"
                class="{{ $inputBase }}{{ $errors->has('lat') ? $invalidBorder : '' }}"
                value="{{ $latVal }}" placeholder="-7.xxx" autocomplete="off">
        </div>
        <div>
            <label for="laydata_lng" class="{{ $labelClass }}">Longitude</label>
            <input type="number" step="any" name="lng" id="laydata_lng"
                class="{{ $inputBase }}{{ $errors->has('lng') ? $invalidBorder : '' }}"
                value="{{ $lngVal }}" placeholder="110.xxx" autocomplete="off">
        </div>
        <div>
            <button type="button"
                class="w-full flex items-center justify-center gap-1.5 px-3 py-2 bg-amber-50 hover:bg-amber-100 text-amber-800 border border-amber-200 font-bold rounded-lg text-[12px] transition-all border-solid whitespace-nowrap"
                data-sbo-gps-lat="laydata_lat"
                data-sbo-gps-lng="laydata_lng"
                data-sbo-gps-status="gps_status_laydata">
                <span class="material-symbols-outlined text-[16px]">my_location</span>
                <span>GPS</span>
            </button>
        </div>
        <span class="sr-only" id="gps_status_laydata"></span>
        <input type="hidden" name="koordinat" id="laydata_koordinat" value="{{ old('koordinat', optional($data ?? null)->koordinat) }}">
        @error('lat')<span class="{{ $errClass }} sm:col-span-2">{{ $message }}</span>@enderror
        @error('lng')<span class="{{ $errClass }} sm:col-span-2">{{ $message }}</span>@enderror
    </div>

    <div class="sm:col-span-2">
        <label for="laydata_foto" class="{{ $labelClass }}">Foto</label>
        @if(!empty($data->foto ?? null))
            <img src="{{ $data->foto }}" alt="Foto saat ini" class="mb-2 rounded-lg border border-slate-200 max-h-[120px] object-cover">
        @endif
        <label for="laydata_foto"
            class="flex items-center justify-center gap-2 w-full rounded-lg border border-dashed border-slate-200 bg-slate-50/80 hover:border-primary/40 hover:bg-primary/5 px-3 py-3 cursor-pointer transition-all{{ $errors->has('foto_file') ? ' border-red-300 bg-red-50/50' : '' }}">
            <span class="material-symbols-outlined text-[22px] text-primary/70">add_a_photo</span>
            <span class="text-[12px] font-semibold text-slate-600">Ambil / unggah foto</span>
        </label>
        <input type="file" name="foto_file" id="laydata_foto" class="visually-hidden"
            accept="image/*" capture="environment"
            data-sbo-media-preview
            data-sbo-foto-gps
            data-sbo-foto-gps-lat="laydata_lat"
            data-sbo-foto-gps-lng="laydata_lng"
            data-sbo-foto-gps-coord-status="gps_status_laydata"
            data-sbo-preview-img="#laydata_foto_preview"
            data-sbo-preview-wrap="#laydata_foto_preview_wrap"
            data-sbo-max-mb="8">
        @error('foto_file')<span class="{{ $errClass }}">{{ $message }}</span>@enderror

        <div class="mt-2 d-none rounded-lg border border-slate-200 bg-slate-50 p-2" id="laydata_foto_preview_wrap">
            <img id="laydata_foto_preview" src="" alt="Pratinjau" class="rounded-md border border-slate-200 max-h-[180px] w-full object-contain d-none">
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    var koord = document.getElementById('laydata_koordinat');
    var form = koord ? koord.closest('form') : null;
    if (!form) return;
    form.addEventListener('submit', function () {
        var lat = document.getElementById('laydata_lat');
        var lng = document.getElementById('laydata_lng');
        if (lat && lng && koord && lat.value && lng.value) {
            koord.value = lat.value + ',' + lng.value;
        }
    });
});
</script>
@endpush
