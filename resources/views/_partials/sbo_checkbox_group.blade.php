@php
    $label = $label ?? 'Pilihan';
    $fieldName = $fieldName ?? 'options';
    $options = $options ?? [];
    $selected = $selected ?? [];
    $idPrefix = $idPrefix ?? 'sbo_cb';
    $hint = $hint ?? 'Pilih satu atau lebih.';
    $labelClass = $labelClass ?? 'block text-[10px] font-bold text-slate-500 uppercase tracking-wide mb-0.5';
    $errClass = $errClass ?? 'text-[11px] text-red-600 font-medium mt-0.5 block';
@endphp

<div class="sm:col-span-2">
    <span class="{{ $labelClass }}">{{ $label }}</span>
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 mt-1">
        @foreach($options as $opt)
            <label for="{{ $idPrefix }}_{{ $loop->index }}"
                class="flex items-start gap-2 rounded-lg border border-slate-200 bg-slate-50/80 px-3 py-2.5 cursor-pointer hover:border-primary/30 hover:bg-primary/5 transition-all min-h-[2.75rem]">
                <input type="checkbox" name="{{ $fieldName }}[]" id="{{ $idPrefix }}_{{ $loop->index }}" value="{{ $opt }}"
                    class="mt-0.5 rounded border-slate-300 text-primary focus:ring-primary/30 shrink-0"
                    @checked(in_array($opt, $selected, true))>
                <span class="text-[12px] font-medium text-slate-700 leading-snug">{{ $opt }}</span>
            </label>
        @endforeach
    </div>
    <p class="text-[11px] text-slate-400 mt-1.5 mb-0">{{ $hint }}</p>
    @error($fieldName)<span class="{{ $errClass }}">{{ $message }}</span>@enderror
    @error($fieldName.'.*')<span class="{{ $errClass }}">{{ $message }}</span>@enderror
</div>
