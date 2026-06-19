@props([
    'prefix' => 'sbo',
    'fromName' => 'tanggal_dari',
    'toName' => 'tanggal_sampai',
    'fromValue' => null,
    'toValue' => null,
])

@php
    $fromValue = $fromValue ?? request($fromName);
    $toValue = $toValue ?? request($toName);
    $displayId = $prefix . '-date-range-display';
    $fromId = $prefix . '-date-from';
    $toId = $prefix . '-date-to';
    $wrapperId = $prefix . '-date-range-wrapper';
    $clearId = $prefix . '-date-range-clear';
@endphp

<div class="pmf-date">
    <div class="pmf-control flex items-center gap-1.5 h-full bg-slate-50 border border-slate-200 rounded-lg px-2 transition-all focus-within:border-primary focus-within:bg-white cursor-pointer sbo-compact-date-range"
         id="{{ $wrapperId }}"
         data-sbo-date-from="{{ $fromName }}"
         data-sbo-date-to="{{ $toName }}"
         data-sbo-date-display="{{ $displayId }}"
         data-sbo-date-from-input="{{ $fromId }}"
         data-sbo-date-to-input="{{ $toId }}">
        <span class="material-symbols-outlined text-slate-400 text-[15px] shrink-0 pointer-events-none">calendar_month</span>
        <input type="text" id="{{ $displayId }}"
               class="pmf-field-text flex-1 min-w-0 bg-transparent border-0 outline-none cursor-pointer"
               placeholder="Tanggal" readonly/>
        <input type="hidden" name="{{ $fromName }}" id="{{ $fromId }}" value="{{ $fromValue }}"/>
        <input type="hidden" name="{{ $toName }}" id="{{ $toId }}" value="{{ $toValue }}"/>
        @if($fromValue || $toValue)
            <button type="button" class="text-slate-300 hover:text-slate-500 border-0 bg-transparent p-0 shrink-0 leading-none sbo-compact-date-clear" id="{{ $clearId }}" title="Hapus tanggal">
                <span class="material-symbols-outlined text-[14px]">close</span>
            </button>
        @endif
    </div>
</div>
