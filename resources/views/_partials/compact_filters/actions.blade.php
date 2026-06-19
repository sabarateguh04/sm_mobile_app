@props(['resetUrl', 'active' => null])

@php
    $active = $active ?? false;
@endphp

<div class="pmf-actions flex items-center gap-1.5">
    <button type="submit" class="pmf-btn px-2 bg-primary hover:bg-primary/90 text-white font-semibold rounded-lg text-[11px] transition-all shadow-sm border-0 inline-flex items-center justify-center" title="Filter">
        <span class="material-symbols-outlined text-[16px]">filter_list</span>
    </button>
    @if($active)
        <a href="{{ $resetUrl }}" class="pmf-btn px-2 bg-slate-100 hover:bg-slate-200 text-slate-600 rounded-lg inline-flex items-center justify-center text-decoration-none" title="Reset filter">
            <span class="material-symbols-outlined text-[16px]">cached</span>
        </a>
    @endif
</div>
