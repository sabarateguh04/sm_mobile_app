@php
    $inputBase = 'w-full bg-slate-50 border border-slate-200 rounded-lg px-3 py-2 text-[13px] text-slate-800 placeholder:text-slate-400 focus:border-primary focus:bg-white focus:ring-0 outline-none transition-all';
    $selectBase = $inputBase . ' appearance-none pr-8';
    $labelClass = 'block text-[10px] font-bold text-slate-500 uppercase tracking-wide mb-0.5';
    $errClass = 'text-[11px] text-red-600 font-medium mt-0.5 block';
    $invalidBorder = ' border-red-300 bg-red-50';
    $isCreate = empty($data);
    $latVal = old('lat');
    $lngVal = old('lng');
    $lokasiVal = old('lokasi', optional($data ?? null)->lokasi ?? '');
    $routePoints = collect(explode('->', (string) $lokasiVal))
        ->map(fn ($point) => trim($point))
        ->filter(fn ($point) => $point !== '')
        ->values();
    $routeStart = $routePoints->get(0, '');
    $routeEnd = $routePoints->get(1, '');
    $routeStops = $routePoints->slice(2)->values()->all();
    if ($routeEnd === '' && $routeStart !== '') {
        $routeStart = '';
    }
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
        <label for="katgiat" class="{{ $labelClass }}">Kategori Giat <span class="text-red-500">*</span></label>
        <div class="relative">
            <select name="katgiat" id="katgiat" required class="{{ $selectBase }}{{ $errors->has('katgiat') ? $invalidBorder : '' }}">
                <option value="">— Pilih —</option>
                @foreach($giatList ?? [] as $giat)
                    <option value="{{ $giat }}" {{ old('katgiat', optional($data ?? null)->katgiat) === $giat ? 'selected' : '' }}>{{ $giat }}</option>
                @endforeach
            </select>
            <span class="absolute right-2.5 top-1/2 -translate-y-1/2 material-symbols-outlined text-slate-400 text-[16px] pointer-events-none">expand_more</span>
        </div>
        @error('katgiat')<span class="{{ $errClass }}">{{ $message }}</span>@enderror
    </div>
    <div class="sm:col-span-2">
        <label class="{{ $labelClass }}">Lokasi <span class="text-red-500">*</span></label>
        <input type="hidden" name="lokasi" id="lokasi" value="{{ $lokasiVal }}">
        <div id="turjagwali_route_builder" class="grid grid-cols-1 sm:grid-cols-2 gap-2 {{ $errors->has('lokasi') ? 'p-2 rounded-lg border border-red-200 bg-red-50/40' : '' }}">
            <div>
                <label for="lokasi_start" class="{{ $labelClass }}">Titik awal</label>
                <input type="text" id="lokasi_start"
                    class="{{ $inputBase }}"
                    value="{{ old('lokasi_start', $routeStart) }}" placeholder="Lokasi keberangkatan"
                    data-sbo-jalan-ac>
            </div>
            <div>
                <label for="lokasi_end" class="{{ $labelClass }}">Titik tujuan</label>
                <input type="text" id="lokasi_end"
                    class="{{ $inputBase }}"
                    value="{{ old('lokasi_end', $routeEnd) }}" placeholder="Lokasi tujuan"
                    data-sbo-jalan-ac>
            </div>
            <div class="sm:col-span-2 space-y-2" id="turjagwali_route_stops_wrapper">
                @foreach($routeStops as $idx => $stop)
                    <div class="turjagwali-route-stop-row flex items-center gap-2">
                        <input type="text"
                            class="{{ $inputBase }} turjagwali-route-stop"
                            placeholder="Titik lanjutan {{ $idx + 1 }}"
                            value="{{ old('lokasi_stop_'.$idx, $stop) }}"
                            data-sbo-jalan-ac>
                        <button type="button" class="turjagwali-remove-stop w-9 h-9 shrink-0 rounded-lg border border-slate-200 text-slate-500 hover:text-red-600 hover:border-red-200 bg-white">
                            <span class="material-symbols-outlined text-[18px]">close</span>
                        </button>
                    </div>
                @endforeach
            </div>
            <div class="sm:col-span-2 flex items-center justify-between gap-2">
                <button type="button" id="turjagwali_add_stop" class="inline-flex items-center gap-1.5 px-3 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-lg text-[12px] font-bold border-0">
                    <span class="material-symbols-outlined text-[16px]">add_road</span>
                    Tambah Titik
                </button>
                <span class="text-[10px] text-slate-400 font-semibold">Format otomatis: A -> B -> C</span>
            </div>
        </div>
        <div id="turjagwali_lokasi_manual_wrap" class="mt-2">
            <label for="turjagwali_lokasi_manual" class="{{ $labelClass }}">Lokasi manual</label>
            <input type="text" id="turjagwali_lokasi_manual"
                class="{{ $inputBase }}{{ $errors->has('lokasi') ? $invalidBorder : '' }}"
                value="{{ $lokasiVal }}" placeholder="Lokasi giat"
                data-sbo-jalan-ac>
            <span class="text-[10px] text-slate-400">Dipakai untuk kategori selain patroli/pengawalan.</span>
        </div>
        @error('lokasi')<span class="{{ $errClass }}">{{ $message }}</span>@enderror
    </div>
    <div>
        <label for="peristiwa" class="{{ $labelClass }}">Peristiwa <span class="text-red-500">*</span></label>
        <div class="relative">
            <select name="peristiwa" id="peristiwa" required class="{{ $selectBase }}{{ $errors->has('peristiwa') ? $invalidBorder : '' }}">
                <option value="">— Pilih —</option>
                @foreach($peristiwaList ?? [] as $p)
                    <option value="{{ $p }}" {{ old('peristiwa', optional($data ?? null)->peristiwa) === $p ? 'selected' : '' }}>{{ $p }}</option>
                @endforeach
            </select>
            <span class="absolute right-2.5 top-1/2 -translate-y-1/2 material-symbols-outlined text-slate-400 text-[16px] pointer-events-none">expand_more</span>
        </div>
        @error('peristiwa')<span class="{{ $errClass }}">{{ $message }}</span>@enderror
    </div>
    <div>
        <label class="{{ $labelClass }}">Petugas Lapangan <span class="text-red-500">*</span></label>
        @php
            $petugasLapVal = old('petugaslap', optional($data ?? null)->petugaslap ?? '');
            if (is_array($petugasLapVal)) {
                $petugasLapVal = implode(',', $petugasLapVal);
            }
        @endphp
        <div
            data-sbo-petugas-ac
            data-sbo-petugas-name="petugaslap"
            data-sbo-petugas-max="5"
            data-sbo-petugas-value="{{ $petugasLapVal }}"
        ></div>
        @error('petugaslap')<span class="{{ $errClass }}">{{ $message }}</span>@enderror
    </div>
    <div>
        <label for="faktor" class="{{ $labelClass }}">Faktor <span class="text-red-500">*</span></label>
        <div class="relative">
            <select name="faktor" id="faktor" required class="{{ $selectBase }}{{ $errors->has('faktor') ? $invalidBorder : '' }}">
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
        <label for="penyebab" class="{{ $labelClass }}">Penyebab <span class="text-red-500">*</span></label>
        <div class="relative">
            <select name="penyebab" id="penyebab" required class="{{ $selectBase }}{{ $errors->has('penyebab') ? $invalidBorder : '' }}">
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
    <div class="sm:col-span-2 grid grid-cols-1 sm:grid-cols-[1fr_1fr_auto] gap-2 items-end">
        <div>
            <label for="turjagwali_lat" class="{{ $labelClass }}">Latitude</label>
            <input type="number" step="any" name="lat" id="turjagwali_lat"
                class="{{ $inputBase }}{{ $errors->has('lat') ? $invalidBorder : '' }}"
                value="{{ $latVal }}" placeholder="-7.xxx" autocomplete="off">
        </div>
        <div>
            <label for="turjagwali_lng" class="{{ $labelClass }}">Longitude</label>
            <input type="number" step="any" name="lng" id="turjagwali_lng"
                class="{{ $inputBase }}{{ $errors->has('lng') ? $invalidBorder : '' }}"
                value="{{ $lngVal }}" placeholder="110.xxx" autocomplete="off">
        </div>
        <div>
            <button type="button" id="btn_gps_turjagwali_form"
                class="w-full flex items-center justify-center gap-1.5 px-3 py-2 bg-amber-50 hover:bg-amber-100 text-amber-800 border border-amber-200 font-bold rounded-lg text-[12px] transition-all border-solid whitespace-nowrap"
                data-sbo-gps-lat="turjagwali_lat"
                data-sbo-gps-lng="turjagwali_lng"
                data-sbo-gps-status="gps_status_turjagwali_form">
                <span class="material-symbols-outlined text-[16px]">my_location</span>
                <span>GPS</span>
            </button>
        </div>
        <span class="sr-only" id="gps_status_turjagwali_form"></span>
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
        <label for="fotogiat_file" class="{{ $labelClass }}">Foto Giat</label>
        @if(!empty(optional($data ?? null)->fotogiat))
            @php
                $__fotoGiat = trim(explode(',', (string) $data->fotogiat, 2)[0]);
                $__fotoGiatUrl = str_starts_with($__fotoGiat, 'http') ? $__fotoGiat : asset(ltrim($__fotoGiat, '/'));
            @endphp
            <img src="{{ $__fotoGiatUrl }}" alt="Foto giat" class="mb-2 rounded-lg border border-slate-200 max-h-[120px] object-cover">
        @endif
        <label for="fotogiat_file"
            class="flex items-center justify-center gap-2 w-full rounded-lg border border-dashed border-slate-200 bg-slate-50/80 hover:border-primary/40 hover:bg-primary/5 px-3 py-3 cursor-pointer transition-all{{ $errors->has('fotogiat_file') ? ' border-red-300 bg-red-50/50' : '' }}">
            <span class="material-symbols-outlined text-[22px] text-primary/70">add_a_photo</span>
            <span class="text-[12px] font-semibold text-slate-600">Ambil / unggah foto</span>
        </label>
        <input type="file" name="fotogiat_file" id="fotogiat_file" class="visually-hidden"
            accept="image/jpeg,image/png,image/jpg,image/gif,image/webp" capture="environment"
            data-sbo-media-preview
            data-sbo-foto-gps
            data-sbo-foto-gps-lat="turjagwali_lat"
            data-sbo-foto-gps-lng="turjagwali_lng"
            data-sbo-foto-gps-coord-status="gps_status_turjagwali_form"
            data-sbo-preview-img="#turjagwali_fotogiat_preview"
            data-sbo-preview-wrap="#turjagwali_fotogiat_preview_wrap"
            data-sbo-max-mb="8">
        @error('fotogiat_file')<span class="{{ $errClass }}">{{ $message }}</span>@enderror
        <div class="mt-2 d-none rounded-lg border border-slate-200 bg-slate-50 p-2" id="turjagwali_fotogiat_preview_wrap">
            <img id="turjagwali_fotogiat_preview" src="" alt="Pratinjau" class="rounded-md border border-slate-200 max-h-[180px] w-full object-contain d-none">
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    var faktorEl = document.getElementById('faktor');
    var penyebabEl = document.getElementById('penyebab');
    var katgiatEl = document.getElementById('katgiat');
    var lokasiHiddenEl = document.getElementById('lokasi');
    var lokasiManualWrap = document.getElementById('turjagwali_lokasi_manual_wrap');
    var lokasiManualEl = document.getElementById('turjagwali_lokasi_manual');
    var routeBuilder = document.getElementById('turjagwali_route_builder');
    var startEl = document.getElementById('lokasi_start');
    var endEl = document.getElementById('lokasi_end');
    var stopsWrapper = document.getElementById('turjagwali_route_stops_wrapper');
    var addStopBtn = document.getElementById('turjagwali_add_stop');

    var isRouteKatgiat = function () {
        if (!katgiatEl) return false;
        var val = (katgiatEl.value || '').toLowerCase();
        return val.includes('patroli') || val.includes('pengawalan');
    };

    var makeStopRow = function (value) {
        var row = document.createElement('div');
        row.className = 'turjagwali-route-stop-row flex items-center gap-2';
        row.innerHTML = '<input type="text" class="{{ $inputBase }} turjagwali-route-stop" placeholder="Titik lanjutan" data-sbo-jalan-ac>' +
            '<button type="button" class="turjagwali-remove-stop w-9 h-9 shrink-0 rounded-lg border border-slate-200 text-slate-500 hover:text-red-600 hover:border-red-200 bg-white">' +
            '<span class="material-symbols-outlined text-[18px]">close</span></button>';
        var input = row.querySelector('.turjagwali-route-stop');
        input.value = value || '';
        return row;
    };

    var routeValue = function () {
        var points = [];
        var first = (startEl?.value || '').trim();
        var second = (endEl?.value || '').trim();
        if (first !== '') points.push(first);
        if (second !== '') points.push(second);
        (stopsWrapper ? Array.from(stopsWrapper.querySelectorAll('.turjagwali-route-stop')) : []).forEach(function (el) {
            var v = (el.value || '').trim();
            if (v !== '') points.push(v);
        });
        return points.join(' -> ');
    };

    var syncLokasi = function () {
        if (!lokasiHiddenEl) return;
        if (isRouteKatgiat()) {
            lokasiHiddenEl.value = routeValue();
            if (lokasiManualEl) lokasiManualEl.value = lokasiHiddenEl.value;
        } else if (lokasiManualEl) {
            lokasiHiddenEl.value = (lokasiManualEl.value || '').trim();
        }
    };

    var toggleLokasiMode = function () {
        var routeMode = isRouteKatgiat();
        if (routeBuilder) routeBuilder.style.display = routeMode ? '' : 'none';
        if (lokasiManualWrap) lokasiManualWrap.style.display = routeMode ? 'none' : '';
        syncLokasi();
    };

    if (addStopBtn && stopsWrapper) {
        addStopBtn.addEventListener('click', function () {
            stopsWrapper.appendChild(makeStopRow(''));
        });
        stopsWrapper.addEventListener('click', function (event) {
            var btn = event.target.closest('.turjagwali-remove-stop');
            if (!btn) return;
            var row = btn.closest('.turjagwali-route-stop-row');
            if (row) row.remove();
            syncLokasi();
        });
        stopsWrapper.addEventListener('input', syncLokasi);
    }
    [startEl, endEl, lokasiManualEl, katgiatEl].forEach(function (el) {
        if (!el) return;
        el.addEventListener('input', syncLokasi);
        el.addEventListener('change', syncLokasi);
    });
    if (katgiatEl) {
        katgiatEl.addEventListener('change', toggleLokasiMode);
    }

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

    toggleLokasiMode();
});
</script>
@endpush
