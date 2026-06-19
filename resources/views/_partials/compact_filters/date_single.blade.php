@props(['name' => 'tanggal', 'value' => null])

@php $value = $value ?? request($name); @endphp

<div class="pmf-date">
    <input type="date" name="{{ $name }}" value="{{ $value }}"
           class="pmf-control pmf-field-text w-full h-full bg-slate-50 border border-slate-200 rounded-lg px-2 focus:border-primary focus:ring-0 focus:bg-white outline-none transition-all"/>
</div>
