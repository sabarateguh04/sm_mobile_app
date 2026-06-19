@props([
    'icon' => 'info',
    'title' => '',
])

<div {{ $attributes->merge(['class' => 'sm:col-span-2 flex items-center gap-2 pt-2 border-t border-slate-100']) }}>
    <span class="material-symbols-outlined text-slate-400 text-[16px] shrink-0" style="font-variation-settings: 'FILL' 1;">{{ $icon }}</span>
    <span class="text-[10px] font-bold text-slate-500 uppercase tracking-wider">{{ $title }}</span>
</div>
