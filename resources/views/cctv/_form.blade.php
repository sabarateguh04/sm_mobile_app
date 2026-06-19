@php
    $patrolCols = $patrolCols ?? \App\Http\Controllers\CctvController::resolvePatrolColumns();
    $__latForm = old('lat');
    $__lngForm = old('lng');
    if ($__latForm === null && $__lngForm === null && !empty($data->koordinat ?? '')) {
        [$__latForm, $__lngForm] = array_map('trim', explode(',', (string) $data->koordinat, 2));
    }
    $inputBase = 'w-full bg-slate-50 border border-slate-200 rounded-lg px-3 py-2 text-[13px] text-slate-800 placeholder:text-slate-400 focus:border-primary focus:bg-white focus:ring-0 outline-none transition-all';
    $selectBase = $inputBase . ' appearance-none pr-8';
    $labelClass = 'block text-[10px] font-bold text-slate-500 uppercase tracking-wide mb-0.5';
    $errClass = 'text-[11px] text-red-600 font-medium mt-0.5 block';
    $invalidBorder = ' border-red-300 bg-red-50';
    $hasGpsCol = \Illuminate\Support\Facades\Schema::hasColumn('patrolicctv', 'koordinat');
    $isCreate = empty($data);
@endphp

<div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
    <div>
        <label for="tanggal" class="{{ $labelClass }}">Tanggal <span class="text-red-500">*</span></label>
        <x-sbo-date-input :value="optional($data ?? null)->tanggal" :show-label="false" :default-today="$isCreate" required
            class="{{ $inputBase }}{{ $errors->has('tanggal') ? $invalidBorder : '' }}" />
    </div>

    @if($patrolCols['lokasi'] ?? null)
    <div>
        <label for="lokasi" class="{{ $labelClass }}">Lokasi <span class="text-red-500">*</span></label>
        <input type="text" name="lokasi" id="lokasi" required
            class="{{ $inputBase }}{{ $errors->has('lokasi') ? $invalidBorder : '' }}"
            value="{{ old('lokasi', optional($data ?? null)->lokasi) }}" placeholder="Lokasi / ruas jalan"
            data-sbo-jalan-ac>
        @error('lokasi')<span class="{{ $errClass }}">{{ $message }}</span>@enderror
    </div>
    @endif

    @if($patrolCols['situasilalin'] ?? null)
    <div>
        <label for="situasilalin" class="{{ $labelClass }}">Situasi Lalin</label>
        <div class="relative">
            <select name="situasilalin" id="situasilalin" class="{{ $selectBase }}{{ $errors->has('situasilalin') ? $invalidBorder : '' }}">
                <option value="">— Pilih —</option>
                @foreach(($situasiLalinList ?? []) as $opt)
                    <option value="{{ $opt }}" {{ old('situasilalin', optional($data ?? null)->situasilalin) === $opt ? 'selected' : '' }}>{{ $opt }}</option>
                @endforeach
            </select>
            <span class="absolute right-2.5 top-1/2 -translate-y-1/2 material-symbols-outlined text-slate-400 text-[16px] pointer-events-none">expand_more</span>
        </div>
        @error('situasilalin')<span class="{{ $errClass }}">{{ $message }}</span>@enderror
    </div>
    @endif

    @if($patrolCols['situasicuaca'] ?? null)
    <div>
        <label for="situasicuaca" class="{{ $labelClass }}">Situasi Cuaca</label>
        <div class="relative">
            <select name="situasicuaca" id="situasicuaca" class="{{ $selectBase }}{{ $errors->has('situasicuaca') ? $invalidBorder : '' }}">
                <option value="">— Pilih —</option>
                @foreach(($situasiCuacaList ?? []) as $opt)
                    <option value="{{ $opt }}" {{ old('situasicuaca', optional($data ?? null)->situasicuaca) === $opt ? 'selected' : '' }}>{{ $opt }}</option>
                @endforeach
            </select>
            <span class="absolute right-2.5 top-1/2 -translate-y-1/2 material-symbols-outlined text-slate-400 text-[16px] pointer-events-none">expand_more</span>
        </div>
        @error('situasicuaca')<span class="{{ $errClass }}">{{ $message }}</span>@enderror
    </div>
    @endif

    @if($patrolCols['peristiwa'] ?? null)
    <div>
        <label for="peristiwa" class="{{ $labelClass }}">Peristiwa</label>
        <div class="relative">
            <select name="peristiwa" id="peristiwa" class="{{ $selectBase }}{{ $errors->has('peristiwa') ? $invalidBorder : '' }}">
                <option value="">— Pilih —</option>
                @foreach(($peristiwaList ?? []) as $opt)
                    <option value="{{ $opt }}" {{ old('peristiwa', optional($data ?? null)->peristiwa) === $opt ? 'selected' : '' }}>{{ $opt }}</option>
                @endforeach
            </select>
            <span class="absolute right-2.5 top-1/2 -translate-y-1/2 material-symbols-outlined text-slate-400 text-[16px] pointer-events-none">expand_more</span>
        </div>
        @error('peristiwa')<span class="{{ $errClass }}">{{ $message }}</span>@enderror
    </div>
    @endif

    @if($patrolCols['ketcb'] ?? null)
    <div class="sm:col-span-2">
        <label for="ketcb" class="{{ $labelClass }}">CB / Ket CB</label>
        <textarea name="ketcb" id="ketcb" rows="2"
            class="{{ $inputBase }} resize-y min-h-[64px]{{ $errors->has('ketcb') ? $invalidBorder : '' }}"
            placeholder="Keterangan CB">{{ old('ketcb', optional($data ?? null)->ketcb) }}</textarea>
        @error('ketcb')<span class="{{ $errClass }}">{{ $message }}</span>@enderror
    </div>
    @endif

    @if($hasGpsCol)
    <div class="sm:col-span-2 grid grid-cols-1 sm:grid-cols-[1fr_1fr_auto] gap-2 items-end">
        <div>
            <label for="patrolicctv_lat" class="{{ $labelClass }}">Latitude</label>
            <input type="number" step="any" name="lat" id="patrolicctv_lat"
                class="{{ $inputBase }}{{ $errors->has('lat') ? $invalidBorder : '' }}"
                value="{{ $__latForm }}" placeholder="-6.xxx">
        </div>
        <div>
            <label for="patrolicctv_lng" class="{{ $labelClass }}">Longitude</label>
            <input type="number" step="any" name="lng" id="patrolicctv_lng"
                class="{{ $inputBase }}{{ $errors->has('lng') ? $invalidBorder : '' }}"
                value="{{ $__lngForm }}" placeholder="106.xxx">
        </div>
        <div>
            <button type="button" id="btn_gps_patrolicctv_form"
                class="w-full flex items-center justify-center gap-1.5 px-3 py-2 bg-amber-50 hover:bg-amber-100 text-amber-800 border border-amber-200 font-bold rounded-lg text-[12px] transition-all border-solid whitespace-nowrap"
                data-sbo-gps-lat="patrolicctv_lat"
                data-sbo-gps-lng="patrolicctv_lng"
                data-sbo-gps-status="gps_status_patrolicctv_form">
                <span class="material-symbols-outlined text-[16px]">my_location</span>
                <span>GPS</span>
            </button>
        </div>
        <span class="sr-only" id="gps_status_patrolicctv_form"></span>
        @error('lat')<span class="{{ $errClass }} sm:col-span-2">{{ $message }}</span>@enderror
        @error('lng')<span class="{{ $errClass }} sm:col-span-2">{{ $message }}</span>@enderror
    </div>
    @endif

    @if($patrolCols['foto'] ?? null)
    <div class="sm:col-span-2">
        <label for="patrolicctv_capture" class="{{ $labelClass }}">Capture</label>
        <label for="patrolicctv_capture"
            class="flex items-center justify-center gap-2 w-full rounded-lg border border-dashed border-slate-200 bg-slate-50/80 hover:border-primary/40 hover:bg-primary/5 px-3 py-3 cursor-pointer transition-all{{ $errors->has('capture_file') ? ' border-red-300 bg-red-50/50' : '' }}">
            <span class="material-symbols-outlined text-[22px] text-primary/70">add_a_photo</span>
            <span class="text-[12px] font-semibold text-slate-600">Ambil / unggah foto</span>
        </label>
        <input type="file" name="capture_file" id="patrolicctv_capture" class="visually-hidden"
            accept="image/jpeg,image/png,image/jpg,image/gif,image/webp" capture="environment"
            data-sbo-media-preview
            @if($hasGpsCol)
            data-sbo-foto-gps
            data-sbo-foto-gps-lat="patrolicctv_lat"
            data-sbo-foto-gps-lng="patrolicctv_lng"
            data-sbo-foto-gps-coord-status="gps_status_patrolicctv_form"
            @endif
            data-sbo-preview-img="#patrolicctv_capture_preview"
            data-sbo-preview-wrap="#patrolicctv_capture_preview_wrap"
            data-sbo-max-mb="8">
        @error('capture_file')<span class="{{ $errClass }}">{{ $message }}</span>@enderror

        <div class="mt-2 d-none rounded-lg border border-slate-200 bg-slate-50 p-2" id="patrolicctv_capture_preview_wrap">
            <img id="patrolicctv_capture_preview" src="" alt="Pratinjau" class="rounded-md border border-slate-200 max-h-[180px] w-full object-contain d-none">
        </div>

        @php
            $existingFoto = optional($data ?? null)->fotosit ?? optional($data ?? null)->fotofisik ?? '';
        @endphp
        @if(!empty($existingFoto))
            @php $thumb = \App\Http\Controllers\CctvController::fotoPublicUrl($existingFoto); @endphp
            @if($thumb)
                <img src="{{ $thumb }}" alt="" class="mt-2 w-full max-h-[160px] object-cover rounded-lg border border-slate-200" referrerpolicy="no-referrer">
            @endif
        @endif
    </div>
    @endif
</div>

@if($hasGpsCol)
@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
});
</script>
@endpush
@endif
