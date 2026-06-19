@php
    $inputBase = 'w-full bg-slate-50 border border-slate-200 rounded-lg px-3 py-2 text-[13px] text-slate-800 placeholder:text-slate-400 focus:border-primary focus:bg-white focus:ring-0 outline-none transition-all';
    $selectBase = $inputBase . ' appearance-none pr-8';
    $labelClass = 'block text-[10px] font-bold text-slate-500 uppercase tracking-wide mb-0.5';
    $errClass = 'text-[11px] text-red-600 font-medium mt-0.5 block';
    $invalidBorder = ' border-red-300 bg-red-50';
    $isCreate = empty($data);
    $fileInputId = $isCreate ? 'itsupport_foto_create' : 'itsupport_foto_edit';
    $previewId = $isCreate ? 'itsupport_foto_preview_create' : 'itsupport_foto_preview_edit';
    $previewWrapId = $isCreate ? 'itsupport_foto_preview_wrap_create' : 'itsupport_foto_preview_wrap_edit';
    $existingFotoUrl = null;
    if (!$isCreate && !empty($data->fotokondisi ?? '')) {
        $existingFotoUrl = preg_match('#^https?://#i', $data->fotokondisi)
            ? $data->fotokondisi
            : \Illuminate\Support\Facades\Storage::disk('public')->url($data->fotokondisi);
    }
@endphp

<div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
    <div>
        <label for="tanggal" class="{{ $labelClass }}">Tanggal <span class="text-red-500">*</span></label>
        <x-sbo-date-input :value="optional($data ?? null)->tanggal ?? ($isCreate ? date('Y-m-d') : null)" :show-label="false" :default-today="$isCreate" required
            class="{{ $inputBase }}{{ $errors->has('tanggal') ? $invalidBorder : '' }}" />
    </div>
    <div>
        <label for="katdevice" class="{{ $labelClass }}">Kategori Device <span class="text-red-500">*</span></label>
        <div class="relative">
            <select name="katdevice" id="katdevice" required class="{{ $selectBase }}{{ $errors->has('katdevice') ? $invalidBorder : '' }}">
                <option value="">— Pilih —</option>
                @foreach($katOptions ?? [] as $k)
                    <option value="{{ $k }}" {{ old('katdevice', optional($data ?? null)->katdevice) === $k ? 'selected' : '' }}>{{ $k }}</option>
                @endforeach
            </select>
            <span class="absolute right-2.5 top-1/2 -translate-y-1/2 material-symbols-outlined text-slate-400 text-[16px] pointer-events-none">expand_more</span>
        </div>
        @error('katdevice')<span class="{{ $errClass }}">{{ $message }}</span>@enderror
    </div>
    <div>
        <label for="namedevice" class="{{ $labelClass }}">Nama Device</label>
        <input type="text" name="namedevice" id="namedevice" maxlength="100"
            class="{{ $inputBase }}{{ $errors->has('namedevice') ? $invalidBorder : '' }}"
            value="{{ old('namedevice', optional($data ?? null)->namedevice) }}" placeholder="Contoh: PC-SBG-001">
        @error('namedevice')<span class="{{ $errClass }}">{{ $message }}</span>@enderror
    </div>
    <div>
        <label for="owner" class="{{ $labelClass }}">Owner / Pemilik <span class="text-red-500">*</span></label>
        <input type="text" name="owner" id="owner" required maxlength="100"
            class="{{ $inputBase }}{{ $errors->has('owner') ? $invalidBorder : '' }}"
            value="{{ old('owner', optional($data ?? null)->owner) }}">
        @error('owner')<span class="{{ $errClass }}">{{ $message }}</span>@enderror
    </div>
    <div class="sm:col-span-2">
        <label for="lokasidevice" class="{{ $labelClass }}">Lokasi Device <span class="text-red-500">*</span></label>
        <input type="text" name="lokasidevice" id="lokasidevice" required maxlength="100"
            class="{{ $inputBase }}{{ $errors->has('lokasidevice') ? $invalidBorder : '' }}"
            value="{{ old('lokasidevice', optional($data ?? null)->lokasidevice) }}" placeholder="Ruangan / lantai / gedung">
        @error('lokasidevice')<span class="{{ $errClass }}">{{ $message }}</span>@enderror
    </div>
    <div>
        <label for="tingkatgangguan" class="{{ $labelClass }}">Tingkat Gangguan <span class="text-red-500">*</span></label>
        <div class="relative">
            <select name="tingkatgangguan" id="tingkatgangguan" required class="{{ $selectBase }}{{ $errors->has('tingkatgangguan') ? $invalidBorder : '' }}">
                <option value="">— Pilih —</option>
                @foreach($tingkatOptions ?? [] as $t)
                    <option value="{{ $t }}" {{ old('tingkatgangguan', optional($data ?? null)->tingkatgangguan) === $t ? 'selected' : '' }}>{{ $t }}</option>
                @endforeach
            </select>
            <span class="absolute right-2.5 top-1/2 -translate-y-1/2 material-symbols-outlined text-slate-400 text-[16px] pointer-events-none">expand_more</span>
        </div>
        @error('tingkatgangguan')<span class="{{ $errClass }}">{{ $message }}</span>@enderror
    </div>
    <div>
        <label for="statusticket" class="{{ $labelClass }}">Status Tiket <span class="text-red-500">*</span></label>
        <div class="relative">
            <select name="statusticket" id="statusticket" required class="{{ $selectBase }}{{ $errors->has('statusticket') ? $invalidBorder : '' }}">
                <option value="">— Pilih —</option>
                @foreach($statusOptions ?? [] as $s)
                    <option value="{{ $s }}" {{ old('statusticket', optional($data ?? null)->statusticket) === $s ? 'selected' : '' }}>{{ $s }}</option>
                @endforeach
            </select>
            <span class="absolute right-2.5 top-1/2 -translate-y-1/2 material-symbols-outlined text-slate-400 text-[16px] pointer-events-none">expand_more</span>
        </div>
        @error('statusticket')<span class="{{ $errClass }}">{{ $message }}</span>@enderror
    </div>
    <div class="sm:col-span-2">
        <label for="ketsingkat" class="{{ $labelClass }}">Keterangan Singkat</label>
        <textarea name="ketsingkat" id="ketsingkat" rows="3"
            class="{{ $inputBase }}{{ $errors->has('ketsingkat') ? $invalidBorder : '' }}"
            placeholder="Deskripsi kerusakan / gangguan...">{{ old('ketsingkat', optional($data ?? null)->ketsingkat) }}</textarea>
        @error('ketsingkat')<span class="{{ $errClass }}">{{ $message }}</span>@enderror
    </div>
    <div class="sm:col-span-2">
        <label for="{{ $fileInputId }}" class="{{ $labelClass }}">
            Foto Kondisi
            @unless($isCreate)
                <span class="normal-case font-normal text-slate-400">(opsional)</span>
            @endunless
        </label>
        @if($existingFotoUrl)
            <a href="{{ $existingFotoUrl }}" target="_blank" rel="noopener noreferrer" class="block mb-2">
                <img src="{{ $existingFotoUrl }}" alt="Foto kondisi" class="rounded-lg border border-slate-200 max-h-[120px] object-cover">
            </a>
        @endif
        <label for="{{ $fileInputId }}"
            class="flex items-center justify-center gap-2 w-full rounded-lg border border-dashed border-slate-200 bg-slate-50/80 hover:border-primary/40 hover:bg-primary/5 px-3 py-3 cursor-pointer transition-all{{ $errors->has('foto_kondisi') ? ' border-red-300 bg-red-50/50' : '' }}">
            <span class="material-symbols-outlined text-[22px] text-primary/70">add_a_photo</span>
            <span class="text-[12px] font-semibold text-slate-600">Ambil / unggah foto</span>
        </label>
        <input type="file" name="foto_kondisi" id="{{ $fileInputId }}" class="visually-hidden"
            accept="image/jpeg,image/png,image/jpg,image/gif,image/webp" capture="environment"
            data-sbo-media-preview
            data-sbo-preview-img="#{{ $previewId }}"
            data-sbo-preview-wrap="#{{ $previewWrapId }}"
            data-sbo-max-mb="8">
        @error('foto_kondisi')<span class="{{ $errClass }}">{{ $message }}</span>@enderror
        <div class="mt-2 d-none rounded-lg border border-slate-200 bg-slate-50 p-2" id="{{ $previewWrapId }}">
            <img id="{{ $previewId }}" src="" alt="Pratinjau" class="rounded-md border border-slate-200 max-h-[180px] w-full object-contain d-none">
        </div>
    </div>
</div>
