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
    $fileInputId = $isCreate ? 'fotogiat_file_pnp_create' : 'fotogiat_file_pnp_edit';
    $previewId = $isCreate ? 'pnp_foto_preview_create' : 'pnp_foto_preview_edit';
    $previewWrapId = $isCreate ? 'pnp_foto_preview_wrap_create' : 'pnp_foto_preview_wrap_edit';
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
                @foreach($jenisTerminalList ?? [] as $jenis)
                    <option value="{{ $jenis }}" {{ old('jenisterminal', optional($data ?? null)->jenisterminal) === $jenis ? 'selected' : '' }}>{{ $jenis }}</option>
                @endforeach
            </select>
            <span class="absolute right-2.5 top-1/2 -translate-y-1/2 material-symbols-outlined text-slate-400 text-[16px] pointer-events-none">expand_more</span>
        </div>
        @error('jenisterminal')<span class="{{ $errClass }}">{{ $message }}</span>@enderror
    </div>
    <div>
        <label for="namaterminal" class="{{ $labelClass }}">Nama Terminal <span class="text-red-500">*</span></label>
        <input type="text" name="namaterminal" id="namaterminal" required
            class="{{ $inputBase }}{{ $errors->has('namaterminal') ? $invalidBorder : '' }}"
            value="{{ old('namaterminal', optional($data ?? null)->namaterminal) }}" placeholder="Nama terminal / stasiun">
        @error('namaterminal')<span class="{{ $errClass }}">{{ $message }}</span>@enderror
    </div>
    <div>
        <label for="koresponden" class="{{ $labelClass }}">Koresponden <span class="text-red-500">*</span></label>
        <input type="text" name="koresponden" id="koresponden" required
            class="{{ $inputBase }}{{ $errors->has('koresponden') ? $invalidBorder : '' }}"
            value="{{ old('koresponden', optional($data ?? null)->koresponden) }}" placeholder="Nama koresponden">
        @error('koresponden')<span class="{{ $errClass }}">{{ $message }}</span>@enderror
    </div>
    <div class="sm:col-span-2 grid grid-cols-2 sm:grid-cols-4 gap-2">
        @foreach([
            'kedatanganpnp' => 'Kedatangan PNP',
            'keberangkatanpnp' => 'Keberangkatan PNP',
            'kedatanganangk' => 'Kedatangan Angk.',
            'keberangkatanangk' => 'Keberangkatan Angk.',
        ] as $field => $lbl)
        <div>
            <label for="{{ $field }}" class="{{ $labelClass }}">{{ $lbl }} <span class="text-red-500">*</span></label>
            <input type="number" min="0" name="{{ $field }}" id="{{ $field }}" required
                class="{{ $inputBase }}{{ $errors->has($field) ? $invalidBorder : '' }}"
                value="{{ old($field, optional($data ?? null)->{$field} ?? 0) }}">
            @error($field)<span class="{{ $errClass }}">{{ $message }}</span>@enderror
        </div>
        @endforeach
    </div>
    <div class="sm:col-span-2 grid grid-cols-1 sm:grid-cols-[1fr_1fr_auto] gap-2 items-end">
        <div>
            <label for="pnp_lat" class="{{ $labelClass }}">Latitude</label>
            <input type="number" step="any" name="lat" id="pnp_lat"
                class="{{ $inputBase }}{{ $errors->has('lat') ? $invalidBorder : '' }}"
                value="{{ $latVal }}" placeholder="-7.xxx" autocomplete="off">
        </div>
        <div>
            <label for="pnp_lng" class="{{ $labelClass }}">Longitude</label>
            <input type="number" step="any" name="lng" id="pnp_lng"
                class="{{ $inputBase }}{{ $errors->has('lng') ? $invalidBorder : '' }}"
                value="{{ $lngVal }}" placeholder="110.xxx" autocomplete="off">
        </div>
        <div>
            <button type="button"
                class="w-full flex items-center justify-center gap-1.5 px-3 py-2 bg-amber-50 hover:bg-amber-100 text-amber-800 border border-amber-200 font-bold rounded-lg text-[12px] transition-all border-solid whitespace-nowrap"
                data-sbo-gps-lat="pnp_lat"
                data-sbo-gps-lng="pnp_lng"
                data-sbo-gps-status="gps_status_pnp">
                <span class="material-symbols-outlined text-[16px]">my_location</span>
                <span>GPS</span>
            </button>
        </div>
        <span class="sr-only" id="gps_status_pnp"></span>
        <input type="hidden" name="koordinat" id="pnp_koordinat" value="{{ old('koordinat', optional($data ?? null)->koordinat ?? '') }}">
    </div>
    <div class="sm:col-span-2">
        <label for="{{ $fileInputId }}" class="{{ $labelClass }}">
            Foto Giat
            @if($isCreate)
                <span class="text-red-500">*</span>
            @else
                <span class="normal-case font-normal text-slate-400">(opsional)</span>
            @endif
        </label>
        @if(!empty(optional($data ?? null)->fotogiat))
            @php
                $__foto = trim(explode(',', (string) $data->fotogiat, 2)[0]);
                $__fotoUrl = str_starts_with($__foto, 'http') ? $__foto : asset(ltrim($__foto, '/'));
            @endphp
            <a href="{{ $__fotoUrl }}" target="_blank" rel="noopener noreferrer" class="block mb-2">
                <img src="{{ $__fotoUrl }}" alt="Foto giat" class="rounded-lg border border-slate-200 max-h-[120px] object-cover">
            </a>
        @endif
        <label for="{{ $fileInputId }}"
            class="flex items-center justify-center gap-2 w-full rounded-lg border border-dashed border-slate-200 bg-slate-50/80 hover:border-primary/40 hover:bg-primary/5 px-3 py-3 cursor-pointer transition-all{{ $errors->has('fotogiat_file') ? ' border-red-300 bg-red-50/50' : '' }}">
            <span class="material-symbols-outlined text-[22px] text-primary/70">add_a_photo</span>
            <span class="text-[12px] font-semibold text-slate-600">Ambil / unggah foto</span>
        </label>
        <input type="file" name="fotogiat_file" id="{{ $fileInputId }}" class="visually-hidden"
            accept="image/jpeg,image/png,image/jpg,image/gif,image/webp" capture="environment"
            data-sbo-media-preview
            data-sbo-foto-gps
            data-sbo-foto-gps-lat="pnp_lat"
            data-sbo-foto-gps-lng="pnp_lng"
            data-sbo-foto-gps-coord-status="gps_status_pnp"
            data-sbo-preview-img="#{{ $previewId }}"
            data-sbo-preview-wrap="#{{ $previewWrapId }}"
            data-sbo-max-mb="8">
        @error('fotogiat_file')<span class="{{ $errClass }}">{{ $message }}</span>@enderror
        <div class="mt-2 d-none rounded-lg border border-slate-200 bg-slate-50 p-2" id="{{ $previewWrapId }}">
            <img id="{{ $previewId }}" src="" alt="Pratinjau" class="rounded-md border border-slate-200 max-h-[180px] w-full object-contain d-none">
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    var formEl = document.querySelector('form[action*="pnpangkutan"]');
    var latEl = document.getElementById('pnp_lat');
    var lngEl = document.getElementById('pnp_lng');
    var koordEl = document.getElementById('pnp_koordinat');
    if (formEl && latEl && lngEl && koordEl) {
        formEl.addEventListener('submit', function () {
            if (latEl.value.trim() && lngEl.value.trim()) {
                koordEl.value = latEl.value.trim() + ',' + lngEl.value.trim();
            }
        });
    }
});
</script>
@endpush
