@props([
    'invalid' => false,
    'compact' => false,
])

@php
    $selectClass = $compact
        ? 'w-full bg-slate-50 border border-slate-200 rounded-lg px-2 py-1.5 text-[11px] text-slate-800 focus:border-primary focus:bg-white focus:ring-0 outline-none transition-all appearance-none pr-7 min-h-[30px]'
        : 'w-full bg-slate-50 border border-slate-200 rounded-lg px-3 py-2 text-[12px] text-slate-800 focus:border-primary focus:bg-white focus:ring-0 outline-none transition-all appearance-none pr-8 min-h-[34px]';
    if ($invalid) {
        $selectClass .= ' border-red-300 bg-red-50';
    }
    $chevronClass = $compact ? 'text-[13px] right-2' : 'text-[16px] right-2.5';
@endphp

<div class="relative w-full">
    <select {{ $attributes->merge(['class' => $selectClass]) }}>
        {{ $slot }}
    </select>
    <span class="absolute top-1/2 -translate-y-1/2 material-symbols-outlined text-slate-400 pointer-events-none {{ $chevronClass }}">expand_more</span>
</div>
