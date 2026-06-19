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
    $jamVal = old('jamperistiwa');
    if ($jamVal === null) {
        $jamVal = optional($data ?? null)->jamperistiwa
            ? \Carbon\Carbon::parse($data->jamperistiwa)->format('H:i')
            : \Carbon\Carbon::now('Asia/Jakarta')->format('H:i');
    }
@endphp

<div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
    <div>
        <label for="tanggal" class="{{ $labelClass }}">Tanggal <span class="text-red-500">*</span></label>
        <x-sbo-date-input :value="optional($data ?? null)->tanggal ?? ($isCreate ? date('Y-m-d') : null)" :show-label="false" :default-today="$isCreate" required
            class="{{ $inputBase }}{{ $errors->has('tanggal') ? $invalidBorder : '' }}" />
    </div>
    <div>
        <label for="jamperistiwa" class="{{ $labelClass }}">Jam Peristiwa</label>
        <input type="time" name="jamperistiwa" id="jamperistiwa"
            class="{{ $inputBase }}{{ $errors->has('jamperistiwa') ? $invalidBorder : '' }}"
            value="{{ $jamVal }}">
        @error('jamperistiwa')<span class="{{ $errClass }}">{{ $message }}</span>@enderror
    </div>
    <div>
        <label for="jeniscontigency" class="{{ $labelClass }}">Jenis Contigency</label>
        <div class="relative">
            <select name="jeniscontigency" id="jeniscontigency" class="{{ $selectBase }}{{ $errors->has('jeniscontigency') ? $invalidBorder : '' }}">
                <option value="">— Pilih —</option>
                @foreach($jenisList ?? [] as $j)
                    @php $__jenis = is_object($j) ? $j->namecontigency : $j; @endphp
                    <option value="{{ $__jenis }}" {{ old('jeniscontigency', optional($data ?? null)->jeniscontigency) === $__jenis ? 'selected' : '' }}>{{ $__jenis }}</option>
                @endforeach
            </select>
            <span class="absolute right-2.5 top-1/2 -translate-y-1/2 material-symbols-outlined text-slate-400 text-[16px] pointer-events-none">expand_more</span>
        </div>
        @error('jeniscontigency')<span class="{{ $errClass }}">{{ $message }}</span>@enderror
    </div>
    <div>
        <label for="lokasi" class="{{ $labelClass }}">Lokasi</label>
        <input type="text" name="lokasi" id="lokasi"
            class="{{ $inputBase }}{{ $errors->has('lokasi') ? $invalidBorder : '' }}"
            value="{{ old('lokasi', optional($data ?? null)->lokasi) }}" placeholder="Lokasi kejadian"
            data-sbo-jalan-ac>
        @error('lokasi')<span class="{{ $errClass }}">{{ $message }}</span>@enderror
    </div>
    <div>
        <label class="{{ $labelClass }}">Petugas TPTKP</label>
        @php
            $tptkpVal = old('petugastptkp', optional($data ?? null)->petugastptkp ?? '');
            if (is_array($tptkpVal)) {
                $tptkpVal = implode(',', $tptkpVal);
            }
        @endphp
        <div
            data-sbo-petugas-ac
            data-sbo-petugas-name="petugastptkp"
            data-sbo-petugas-max="5"
            data-sbo-petugas-value="{{ $tptkpVal }}"
        ></div>
        @error('petugastptkp')<span class="{{ $errClass }}">{{ $message }}</span>@enderror
    </div>
    <div>
        <label for="faktor" class="{{ $labelClass }}">Faktor</label>
        <div class="relative">
            <select name="faktor" id="faktor" class="{{ $selectBase }}{{ $errors->has('faktor') ? $invalidBorder : '' }}">
                <option value="">— Pilih —</option>
                @foreach($faktorList ?? [] as $f)
                    <option value="{{ $f }}" {{ old('faktor', optional($data ?? null)->faktor) === $f ? 'selected' : '' }}>{{ $f }}</option>
                @endforeach
            </select>
            <span class="absolute right-2.5 top-1/2 -translate-y-1/2 material-symbols-outlined text-slate-400 text-[16px] pointer-events-none">expand_more</span>
        </div>
        @error('faktor')<span class="{{ $errClass }}">{{ $message }}</span>@enderror
    </div>
    <div>
        <label for="penyebab" class="{{ $labelClass }}">Penyebab</label>
        <div class="relative">
            <select name="penyebab" id="penyebab" class="{{ $selectBase }}{{ $errors->has('penyebab') ? $invalidBorder : '' }}">
                <option value="">— Pilih —</option>
                @foreach($penyebabList ?? [] as $p)
                    @php $__penyebab = is_object($p) ? $p->namepenyebab : $p; @endphp
                    <option value="{{ $__penyebab }}"
                            data-faktor="{{ is_object($p) ? ($p->id_faktor ?? '') : '' }}"
                            {{ old('penyebab', optional($data ?? null)->penyebab) === $__penyebab ? 'selected' : '' }}>{{ $__penyebab }}</option>
                @endforeach
            </select>
            <span class="absolute right-2.5 top-1/2 -translate-y-1/2 material-symbols-outlined text-slate-400 text-[16px] pointer-events-none">expand_more</span>
        </div>
        @error('penyebab')<span class="{{ $errClass }}">{{ $message }}</span>@enderror
    </div>
    <div class="sm:col-span-2 grid grid-cols-2 sm:grid-cols-4 gap-2">
        @foreach(['korbanmd' => 'Korban MD', 'korbanlb' => 'Korban LB', 'korbanlr' => 'Korban LR', 'kermat' => 'Kermat'] as $field => $lbl)
        <div>
            <label for="{{ $field }}" class="{{ $labelClass }}">{{ $lbl }}</label>
            <input type="number" min="0" name="{{ $field }}" id="{{ $field }}"
                class="{{ $inputBase }}{{ $errors->has($field) ? $invalidBorder : '' }}"
                value="{{ old($field, optional($data ?? null)->{$field} ?? 0) }}">
            @error($field)<span class="{{ $errClass }}">{{ $message }}</span>@enderror
        </div>
        @endforeach
    </div>
    <div class="sm:col-span-2 grid grid-cols-1 sm:grid-cols-[1fr_1fr_auto] gap-2 items-end">
        <div>
            <label for="contigency_lat" class="{{ $labelClass }}">Latitude</label>
            <input type="number" step="any" name="lat" id="contigency_lat"
                class="{{ $inputBase }}{{ $errors->has('lat') ? $invalidBorder : '' }}"
                value="{{ $latVal }}" placeholder="-7.xxx" autocomplete="off">
        </div>
        <div>
            <label for="contigency_lng" class="{{ $labelClass }}">Longitude</label>
            <input type="number" step="any" name="lng" id="contigency_lng"
                class="{{ $inputBase }}{{ $errors->has('lng') ? $invalidBorder : '' }}"
                value="{{ $lngVal }}" placeholder="110.xxx" autocomplete="off">
        </div>
        <div>
            <button type="button" id="btn_gps_contigency_form"
                class="w-full flex items-center justify-center gap-1.5 px-3 py-2 bg-amber-50 hover:bg-amber-100 text-amber-800 border border-amber-200 font-bold rounded-lg text-[12px] transition-all border-solid whitespace-nowrap"
                data-sbo-gps-lat="contigency_lat"
                data-sbo-gps-lng="contigency_lng"
                data-sbo-gps-status="gps_status_contigency_form">
                <span class="material-symbols-outlined text-[16px]">my_location</span>
                <span>GPS</span>
            </button>
        </div>
        <span class="sr-only" id="gps_status_contigency_form"></span>
        @error('lat')<span class="{{ $errClass }} sm:col-span-2">{{ $message }}</span>@enderror
        @error('lng')<span class="{{ $errClass }} sm:col-span-2">{{ $message }}</span>@enderror
    </div>
    <div class="sm:col-span-2">
        <label for="ketcb" class="{{ $labelClass }}">Keterangan CB</label>
        <textarea name="ketcb" id="ketcb" rows="2"
            class="{{ $inputBase }} resize-y min-h-[64px]{{ $errors->has('ketcb') ? $invalidBorder : '' }}"
            placeholder="Keterangan">{{ old('ketcb', optional($data ?? null)->ketcb) }}</textarea>
        @error('ketcb')<span class="{{ $errClass }}">{{ $message }}</span>@enderror
    </div>
    <div class="sm:col-span-2">
        <label for="fotosit_file" class="{{ $labelClass }}">Foto Situasi</label>
        @if(!empty(optional($data ?? null)->fotosit))
            @php
                $__foto = trim(explode(',', (string) $data->fotosit, 2)[0]);
                $__fotoUrl = str_starts_with($__foto, 'http') ? $__foto : asset(ltrim($__foto, '/'));
            @endphp
            <img src="{{ $__fotoUrl }}" alt="Foto situasi" class="mb-2 rounded-lg border border-slate-200 max-h-[120px] object-cover">
        @endif
        <label for="fotosit_file"
            class="flex items-center justify-center gap-2 w-full rounded-lg border border-dashed border-slate-200 bg-slate-50/80 hover:border-primary/40 hover:bg-primary/5 px-3 py-3 cursor-pointer transition-all{{ $errors->has('fotosit_file') ? ' border-red-300 bg-red-50/50' : '' }}">
            <span class="material-symbols-outlined text-[22px] text-primary/70">add_a_photo</span>
            <span class="text-[12px] font-semibold text-slate-600">Ambil / unggah foto</span>
        </label>
        <input type="file" name="fotosit_file" id="fotosit_file" class="visually-hidden"
            accept="image/jpeg,image/png,image/jpg,image/gif,image/webp" capture="environment"
            data-sbo-media-preview
            data-sbo-foto-gps
            data-sbo-foto-gps-lat="contigency_lat"
            data-sbo-foto-gps-lng="contigency_lng"
            data-sbo-foto-gps-coord-status="gps_status_contigency_form"
            data-sbo-preview-img="#contigency_foto_preview"
            data-sbo-preview-wrap="#contigency_foto_preview_wrap"
            data-sbo-max-mb="8">
        @error('fotosit_file')<span class="{{ $errClass }}">{{ $message }}</span>@enderror
        <div class="mt-2 d-none rounded-lg border border-slate-200 bg-slate-50 p-2" id="contigency_foto_preview_wrap">
            <img id="contigency_foto_preview" src="" alt="Pratinjau" class="rounded-md border border-slate-200 max-h-[180px] w-full object-contain d-none">
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    var faktorEl = document.getElementById('faktor');
    var penyebabEl = document.getElementById('penyebab');
    if (faktorEl && penyebabEl) {
        var allOptions = Array.from(penyebabEl.querySelectorAll('option[data-faktor]'));
        function filterPenyebab() {
            var faktor = faktorEl.value;
            var selected = penyebabEl.value;
            allOptions.forEach(function (opt) {
                var show = !faktor || opt.getAttribute('data-faktor') === faktor;
                opt.hidden = !show;
                opt.disabled = !show;
            });
            if (selected && penyebabEl.querySelector('option[value="' + CSS.escape(selected) + '"]:disabled')) {
                penyebabEl.value = '';
            }
        }
        faktorEl.addEventListener('change', filterPenyebab);
        filterPenyebab();
    }
});
</script>
@endpush
