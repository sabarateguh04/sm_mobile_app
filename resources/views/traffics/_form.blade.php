@php
    $inputBase = 'w-full bg-slate-50 border border-slate-200 rounded-lg px-3 py-2 text-[13px] text-slate-800 placeholder:text-slate-400 focus:border-primary focus:bg-white focus:ring-0 outline-none transition-all';
    $selectBase = $inputBase . ' appearance-none pr-8';
    $labelClass = 'block text-[10px] font-bold text-slate-500 uppercase tracking-wide mb-0.5';
    $errClass = 'text-[11px] text-red-600 font-medium mt-0.5 block';
    $invalidBorder = ' border-red-300 bg-red-50';
    $isCreate = empty($data);
    $hasGpsCol = \Illuminate\Support\Facades\Schema::hasColumn('trafficdalkot', 'koordinat');
    $latVal = old('lat');
    $lngVal = old('lng');
    if ($latVal === null && $lngVal === null && ! empty(optional($data ?? null)->koordinat ?? '') && str_contains((string) optional($data ?? null)->koordinat, ',')) {
        [$latVal, $lngVal] = array_map('trim', explode(',', (string) optional($data ?? null)->koordinat, 2));
    }
    $waktuVal = old('waktu');
    if ($waktuVal === null) {
        $waktuVal = optional($data ?? null)->waktu
            ? substr((string) optional($data ?? null)->waktu, 0, 5)
            : \Carbon\Carbon::now('Asia/Jakarta')->format('H:i');
    }
    $formSuffix = $isCreate ? 'create' : 'edit';
@endphp

<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3">
    <div>
        <label for="tanggal" class="{{ $labelClass }}">Tanggal <span class="text-red-500">*</span></label>
        <x-sbo-date-input :value="optional($data ?? null)->tanggal ?? ($isCreate ? date('Y-m-d') : null)" :show-label="false" :default-today="$isCreate" required
            class="{{ $inputBase }}{{ $errors->has('tanggal') ? $invalidBorder : '' }}" />
    </div>

    <div>
        <label for="waktu" class="{{ $labelClass }}">Waktu</label>
        <input type="time" name="waktu" id="waktu"
            class="{{ $inputBase }}{{ $errors->has('waktu') ? $invalidBorder : '' }}"
            value="{{ $waktuVal }}">
        @error('waktu')<span class="{{ $errClass }}">{{ $message }}</span>@enderror
    </div>

    <div>
        <label for="jenisjalan" class="{{ $labelClass }}">Jenis Jalan <span class="text-red-500">*</span></label>
        <div class="relative">
            <select name="jenisjalan" id="jenisjalan" required class="{{ $selectBase }}{{ $errors->has('jenisjalan') ? $invalidBorder : '' }}">
                <option value="">— Pilih —</option>
                @foreach($jenisJalanList as $j)
                    <option value="{{ $j }}" {{ old('jenisjalan', optional($data ?? null)->jenisjalan ?? '') == $j ? 'selected' : '' }}>{{ $j }}</option>
                @endforeach
            </select>
            <span class="absolute right-2.5 top-1/2 -translate-y-1/2 material-symbols-outlined text-slate-400 text-[16px] pointer-events-none">expand_more</span>
        </div>
        @error('jenisjalan')<span class="{{ $errClass }}">{{ $message }}</span>@enderror
    </div>

    <x-sbo-form-divider icon="route" title="Lokasi & arah" class="sm:col-span-2 lg:col-span-3 !pt-0 !border-t-0" />

    <div>
        <label for="gerbangkota" class="{{ $labelClass }}">Gerbang Kota <span class="text-red-500">*</span></label>
        <div class="relative">
            <select name="gerbangkota" id="gerbangkota" required class="{{ $selectBase }}{{ $errors->has('gerbangkota') ? $invalidBorder : '' }}">
                <option value="">— Pilih —</option>
                @foreach($gerbangKotaList ?? ['UTARA', 'SELATAN', 'TIMUR', 'BARAT'] as $g)
                    <option value="{{ $g }}" {{ old('gerbangkota', optional($data ?? null)->gerbangkota ?? '') == $g ? 'selected' : '' }}>{{ $g }}</option>
                @endforeach
            </select>
            <span class="absolute right-2.5 top-1/2 -translate-y-1/2 material-symbols-outlined text-slate-400 text-[16px] pointer-events-none">expand_more</span>
        </div>
        @error('gerbangkota')<span class="{{ $errClass }}">{{ $message }}</span>@enderror
    </div>

    <div>
        <label for="araharus" class="{{ $labelClass }}">Arah Arus <span class="text-red-500">*</span></label>
        <div class="relative">
            <select name="araharus" id="araharus" required class="{{ $selectBase }}{{ $errors->has('araharus') ? $invalidBorder : '' }}">
                <option value="">— Pilih —</option>
                @foreach($arahArusList ?? ['Masuk', 'Keluar'] as $a)
                    <option value="{{ $a }}" {{ old('araharus', optional($data ?? null)->araharus ?? '') == $a ? 'selected' : '' }}>{{ $a }}</option>
                @endforeach
            </select>
            <span class="absolute right-2.5 top-1/2 -translate-y-1/2 material-symbols-outlined text-slate-400 text-[16px] pointer-events-none">expand_more</span>
        </div>
        @error('araharus')<span class="{{ $errClass }}">{{ $message }}</span>@enderror
    </div>

    <div class="sm:col-span-2 lg:col-span-1">
        <label for="namajalan" class="{{ $labelClass }}">Nama Jalan <span class="text-red-500">*</span></label>
        <input type="text" name="namajalan" id="namajalan" required maxlength="100"
            class="{{ $inputBase }}{{ $errors->has('namajalan') ? $invalidBorder : '' }}"
            value="{{ old('namajalan', optional($data ?? null)->namajalan ?? '') }}" placeholder="Nama ruas / gerbang"
            data-sbo-jalan-ac>
        @error('namajalan')<span class="{{ $errClass }}">{{ $message }}</span>@enderror
    </div>

    <x-sbo-form-divider icon="directions_car" title="Volume kendaraan" class="sm:col-span-2 lg:col-span-3" />

    <div>
        <label for="traffics_r2" class="{{ $labelClass }}">R2 <span class="text-red-500">*</span></label>
        <input type="number" min="0" name="r2" id="traffics_r2" required
            class="{{ $inputBase }}{{ $errors->has('r2') ? $invalidBorder : '' }}"
            value="{{ old('r2', optional($data ?? null)->r2 ?? 0) }}">
        @error('r2')<span class="{{ $errClass }}">{{ $message }}</span>@enderror
    </div>

    <div>
        <label for="traffics_r4" class="{{ $labelClass }}">R4 <span class="text-red-500">*</span></label>
        <input type="number" min="0" name="r4" id="traffics_r4" required
            class="{{ $inputBase }}{{ $errors->has('r4') ? $invalidBorder : '' }}"
            value="{{ old('r4', optional($data ?? null)->r4 ?? 0) }}">
        @error('r4')<span class="{{ $errClass }}">{{ $message }}</span>@enderror
    </div>

    <div>
        <label for="traffics_r6" class="{{ $labelClass }}">R6 <span class="text-red-500">*</span></label>
        <input type="number" min="0" name="r6" id="traffics_r6" required
            class="{{ $inputBase }}{{ $errors->has('r6') ? $invalidBorder : '' }}"
            value="{{ old('r6', optional($data ?? null)->r6 ?? 0) }}">
        @error('r6')<span class="{{ $errClass }}">{{ $message }}</span>@enderror
    </div>

    <div>
        <label for="traffics_r6_plus" class="{{ $labelClass }}">R6+</label>
        <input type="number" min="0" name="r6_plus" id="traffics_r6_plus"
            class="{{ $inputBase }}{{ $errors->has('r6_plus') ? $invalidBorder : '' }}"
            value="{{ old('r6_plus', optional($data ?? null)->r6_plus ?? 0) }}">
        @error('r6_plus')<span class="{{ $errClass }}">{{ $message }}</span>@enderror
    </div>

    <div class="sm:col-span-2 lg:col-span-3">
        <label for="traffics_jumlah_preview" class="{{ $labelClass }}">Jumlah (otomatis)</label>
        <input type="text" id="traffics_jumlah_preview" readonly
            class="{{ $inputBase }} bg-slate-100 text-slate-600 cursor-default"
            value="{{ old('jumlah', optional($data ?? null)->jumlah ?? 0) }}">
        <p class="text-[10px] text-slate-400 mt-0.5 m-0">R2 + R4 + R6 + R6+</p>
    </div>

    <div class="sm:col-span-2 lg:col-span-3">
        <label for="ketcb" class="{{ $labelClass }}">Keterangan CB</label>
        <textarea name="ketcb" id="ketcb" rows="2"
            class="{{ $inputBase }} resize-y min-h-[64px]{{ $errors->has('ketcb') ? $invalidBorder : '' }}"
            placeholder="Keterangan kondisi lalu lintas">{{ old('ketcb', optional($data ?? null)->ketcb ?? '') }}</textarea>
        @error('ketcb')<span class="{{ $errClass }}">{{ $message }}</span>@enderror
    </div>

    @if($hasGpsCol)
        <x-sbo-form-divider icon="my_location" title="Koordinat GPS" class="sm:col-span-2 lg:col-span-3" />
        <div class="sm:col-span-2 lg:col-span-3 grid grid-cols-1 sm:grid-cols-3 gap-3">
            <div>
                <label for="traffics_lat" class="{{ $labelClass }}">Latitude</label>
                <input type="number" step="any" name="lat" id="traffics_lat"
                    class="{{ $inputBase }}{{ $errors->has('lat') ? $invalidBorder : '' }}"
                    value="{{ $latVal }}" placeholder="-7.xxx" autocomplete="off">
            </div>
            <div>
                <label for="traffics_lng" class="{{ $labelClass }}">Longitude</label>
                <input type="number" step="any" name="lng" id="traffics_lng"
                    class="{{ $inputBase }}{{ $errors->has('lng') ? $invalidBorder : '' }}"
                    value="{{ $lngVal }}" placeholder="110.xxx" autocomplete="off">
            </div>
            <div class="flex items-end">
                <button type="button" id="btn_gps_traffics_form"
                    class="w-full flex items-center justify-center gap-1.5 px-3 py-2 bg-amber-50 hover:bg-amber-100 text-amber-800 border border-amber-200 font-bold rounded-lg text-[12px] transition-all border-solid whitespace-nowrap"
                    data-sbo-gps-lat="traffics_lat"
                    data-sbo-gps-lng="traffics_lng"
                    data-sbo-gps-status="gps_status_traffics_form">
                    <span class="material-symbols-outlined text-[16px]">my_location</span>
                    <span>Ambil GPS</span>
                </button>
            </div>
            <span class="sr-only" id="gps_status_traffics_form"></span>
            @error('lat')<span class="{{ $errClass }} sm:col-span-3">{{ $message }}</span>@enderror
            @error('lng')<span class="{{ $errClass }} sm:col-span-3">{{ $message }}</span>@enderror
        </div>
    @endif

    <div class="sm:col-span-2 lg:col-span-3">
        <label for="fotosit_file" class="{{ $labelClass }}">Foto Situasi</label>
        @if(! empty(optional($data ?? null)->fotosit ?? ''))
            @php
                $__foto = trim(explode(',', (string) optional($data ?? null)->fotosit, 2)[0]);
                $__fotoUrl = preg_match('#^https?://#i', $__foto) ? $__foto : asset(ltrim($__foto, '/'));
            @endphp
            <a href="{{ $__fotoUrl }}" target="_blank" rel="noopener noreferrer" class="block mb-2">
                <img src="{{ $__fotoUrl }}" alt="Foto situasi" class="rounded-lg border border-slate-200 max-h-[120px] object-cover">
            </a>
        @endif
        <label for="fotosit_file_{{ $formSuffix }}"
            class="flex items-center justify-center gap-2 w-full rounded-lg border border-dashed border-slate-200 bg-slate-50/80 hover:border-primary/40 hover:bg-primary/5 px-3 py-3 cursor-pointer transition-all{{ $errors->has('fotosit_file') ? ' border-red-300 bg-red-50/50' : '' }}">
            <span class="material-symbols-outlined text-[22px] text-primary/70">add_a_photo</span>
            <span class="text-[12px] font-semibold text-slate-600">Ambil / unggah foto</span>
        </label>
        <input type="file" name="fotosit_file" id="fotosit_file_{{ $formSuffix }}" class="visually-hidden"
            accept="image/jpeg,image/png,image/jpg,image/gif,image/webp" capture="environment"
            data-sbo-media-preview
            @if($hasGpsCol) data-sbo-foto-gps data-sbo-foto-gps-lat="traffics_lat" data-sbo-foto-gps-lng="traffics_lng" data-sbo-foto-gps-coord-status="gps_status_traffics_form" @endif
            data-sbo-preview-img="#traffics_foto_preview_{{ $formSuffix }}"
            data-sbo-preview-wrap="#traffics_foto_preview_wrap_{{ $formSuffix }}"
            data-sbo-max-mb="8">
        @error('fotosit_file')<span class="{{ $errClass }}">{{ $message }}</span>@enderror

        <div class="mt-2 d-none rounded-lg border border-slate-200 bg-slate-50 p-2" id="traffics_foto_preview_wrap_{{ $formSuffix }}">
            <img id="traffics_foto_preview_{{ $formSuffix }}" src="" alt="Pratinjau" class="rounded-md border border-slate-200 max-h-[180px] w-full object-contain d-none">
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    function updateJumlah() {
        var r2 = parseInt(document.getElementById('traffics_r2')?.value || '0', 10) || 0;
        var r4 = parseInt(document.getElementById('traffics_r4')?.value || '0', 10) || 0;
        var r6 = parseInt(document.getElementById('traffics_r6')?.value || '0', 10) || 0;
        var r6Plus = parseInt(document.getElementById('traffics_r6_plus')?.value || '0', 10) || 0;
        var el = document.getElementById('traffics_jumlah_preview');
        if (el) {
            el.value = r2 + r4 + r6 + r6Plus;
        }
    }
    ['traffics_r2', 'traffics_r4', 'traffics_r6', 'traffics_r6_plus'].forEach(function (id) {
        document.getElementById(id)?.addEventListener('input', updateJumlah);
    });
    updateJumlah();
});
</script>
@endpush
