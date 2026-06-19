@props([
    'name',
    'placeholder' => 'Pilih',
    'options' => [],
    'autoSubmit' => true,
    'widthClass' => 'pmf-extra',
])

<div class="{{ $widthClass }}">
    <select name="{{ $name }}"
            class="pmf-control pmf-field-text pmf-native-select w-full h-full bg-slate-50 border border-slate-200 rounded-lg pl-2.5 pr-7 focus:border-primary focus:ring-0 focus:bg-white outline-none transition-all {{ request($name) ? '' : 'pmf-empty' }}"
            @if($autoSubmit) onchange="this.form.submit()" @endif>
        <option value="">{{ $placeholder }}</option>
        @foreach($options as $opt)
            <option value="{{ $opt }}" @selected(request($name) == $opt)>{{ $opt }}</option>
        @endforeach
    </select>
</div>
