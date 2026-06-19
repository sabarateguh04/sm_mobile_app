@props([
    'name' => 'tanggal',
    'id' => null,
    'value' => null,
    'required' => false,
    'label' => 'Tanggal',
    'defaultToday' => false,
    'showLabel' => true,
])

@php
    use Carbon\Carbon;

    $inputId = $id ?? $name;
    $raw = old($name, $value);

    if (($raw === null || $raw === '') && $defaultToday && ! old($name)) {
        $raw = \Carbon\Carbon::now('Asia/Jakarta')->format('Y-m-d');
    }

    $displayValue = '';

    if ($raw !== null && $raw !== '') {
        if ($raw instanceof \DateTimeInterface) {
            $displayValue = $raw->format('d/m/y');
        } elseif (preg_match('/^\d{4}-\d{2}-\d{2}/', (string) $raw)) {
            $displayValue = Carbon::parse($raw)->format('d/m/y');
        } elseif (preg_match('#^\d{1,2}/\d{1,2}/\d{2,4}#', (string) $raw)) {
            $displayValue = (string) $raw;
        } else {
            try {
                $displayValue = Carbon::parse($raw)->format('d/m/y');
            } catch (\Exception $e) {
                $displayValue = '';
            }
        }
    }

    $fallbackClass = $showLabel ? 'form-control' : '';
    if ($errors->has($name)) {
        $fallbackClass .= $showLabel ? ' is-invalid' : '';
    }
@endphp

@if($showLabel)
<label for="{{ $inputId }}" class="form-label">{{ $label }} @if($required)<span class="text-danger">*</span>@endif</label>
@endif
<input
    type="text"
    name="{{ $name }}"
    id="{{ $inputId }}"
    value="{{ $displayValue }}"
    placeholder="dd/mm/yy"
    autocomplete="off"
    inputmode="numeric"
    data-sbo-date-input
    @if($required) required @endif
    {{ $attributes->merge(['class' => trim($fallbackClass)]) }}
>
@error($name)
    @if($showLabel)
        <div class="invalid-feedback">{{ $message }}</div>
    @else
        <span class="text-[11px] text-red-600 font-medium mt-0.5 block">{{ $message }}</span>
    @endif
@enderror
