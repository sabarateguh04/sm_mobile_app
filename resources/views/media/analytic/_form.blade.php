@php
    $inputBase = 'w-full bg-slate-50 border border-slate-200 rounded-lg px-3 py-2 text-[13px] text-slate-800 placeholder:text-slate-400 focus:border-primary focus:bg-white focus:ring-0 outline-none transition-all';
    $selectBase = $inputBase . ' appearance-none pr-8';
    $labelClass = 'block text-[10px] font-bold text-slate-500 uppercase tracking-wide mb-0.5';
    $errClass = 'text-[11px] text-red-600 font-medium mt-0.5 block';
    $invalidBorder = ' border-red-300 bg-red-50';
    $isCreate = empty($data);
    $sentimenOptions = $sentimenOptions ?? ['Positif', 'Negatif', 'Netral'];
@endphp

<div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
    <div>
        <label for="tanggal" class="{{ $labelClass }}">Tanggal <span class="text-red-500">*</span></label>
        <x-sbo-date-input :value="optional($data ?? null)->tanggal ?? ($isCreate ? date('Y-m-d') : null)" :show-label="false" :default-today="$isCreate" required
            class="{{ $inputBase }}{{ $errors->has('tanggal') ? $invalidBorder : '' }}" />
    </div>
    <div>
        <label for="vol_percakapan" class="{{ $labelClass }}">Vol Percakapan <span class="text-red-500">*</span></label>
        <input type="number" name="vol_percakapan" id="vol_percakapan" min="0" required
            class="{{ $inputBase }}{{ $errors->has('vol_percakapan') ? $invalidBorder : '' }}"
            value="{{ old('vol_percakapan', optional($data ?? null)->vol_percakapan ?? ($isCreate ? '' : 0)) }}"
            placeholder="0">
        @error('vol_percakapan')<span class="{{ $errClass }}">{{ $message }}</span>@enderror
    </div>
    <div class="sm:col-span-2">
        <label for="issue_topic" class="{{ $labelClass }}">Issue / Topic</label>
        <textarea name="issue_topic" id="issue_topic" rows="2"
            class="{{ $inputBase }} resize-y min-h-[64px]{{ $errors->has('issue_topic') ? $invalidBorder : '' }}"
            placeholder="Topik / isu percakapan media sosial">{{ old('issue_topic', optional($data ?? null)->issue_topic) }}</textarea>
        @error('issue_topic')<span class="{{ $errClass }}">{{ $message }}</span>@enderror
    </div>
    <div>
        <label for="sentimen" class="{{ $labelClass }}">Sentimen</label>
        <div class="relative">
            <select name="sentimen" id="sentimen" class="{{ $selectBase }}{{ $errors->has('sentimen') ? $invalidBorder : '' }}">
                <option value="">— Pilih —</option>
                @foreach($sentimenOptions as $opt)
                    <option value="{{ $opt }}" {{ old('sentimen', optional($data ?? null)->sentimen) === $opt ? 'selected' : '' }}>{{ $opt }}</option>
                @endforeach
            </select>
            <span class="absolute right-2.5 top-1/2 -translate-y-1/2 material-symbols-outlined text-slate-400 text-[16px] pointer-events-none">expand_more</span>
        </div>
        @error('sentimen')<span class="{{ $errClass }}">{{ $message }}</span>@enderror
    </div>
    <div>
        <label for="potensi" class="{{ $labelClass }}">Potensi</label>
        <input type="text" name="potensi" id="potensi"
            class="{{ $inputBase }}{{ $errors->has('potensi') ? $invalidBorder : '' }}"
            value="{{ old('potensi', optional($data ?? null)->potensi) }}" placeholder="e.g. Pergerakan Massa">
        @error('potensi')<span class="{{ $errClass }}">{{ $message }}</span>@enderror
    </div>
</div>
