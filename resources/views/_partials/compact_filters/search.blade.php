@props(['placeholder' => 'Cari...'])

<div class="pmf-search relative">
    <span class="absolute left-2 top-1/2 -translate-y-1/2 material-symbols-outlined text-primary text-[16px] pointer-events-none">search</span>
    <input name="search" value="{{ request('search') }}"
           {{ $attributes->merge(['class' => 'pmf-control pmf-field-text w-full h-full pl-8 pr-2.5 bg-slate-50 border border-slate-200 focus:border-primary focus:bg-white focus:ring-0 rounded-lg outline-none transition-all']) }}
           placeholder="{{ $placeholder }}" type="text" autocomplete="off"/>
</div>
