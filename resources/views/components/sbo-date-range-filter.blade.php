@props([
    'dari' => null,
    'sampai' => null,
    'label' => 'Range Tanggal',
    'showLabel' => true,
    'nameDari' => 'tanggal_dari',
    'nameSampai' => 'tanggal_sampai',
])

@php
    use Carbon\Carbon;

    $dariVal = $dari ?? request($nameDari);
    $sampaiVal = $sampai ?? request($nameSampai);

    $fmtDisplay = function ($v) {
        if ($v === null || $v === '') {
            return '';
        }
        try {
            return Carbon::parse($v)->format('d/m/y');
        } catch (\Exception $e) {
            return '';
        }
    };

    $fmtIso = function ($v) {
        if ($v === null || $v === '') {
            return '';
        }
        try {
            return Carbon::parse($v)->format('Y-m-d');
        } catch (\Exception $e) {
            return '';
        }
    };

    $dariDisplay = $fmtDisplay($dariVal);
    $sampaiDisplay = $fmtDisplay($sampaiVal);
    $displayRange = '';

    if ($dariDisplay && $sampaiDisplay) {
        $displayRange = $dariDisplay === $sampaiDisplay
            ? $dariDisplay
            : $dariDisplay . ' — ' . $sampaiDisplay;
    } elseif ($dariDisplay) {
        $displayRange = $dariDisplay;
    }

    $dariIso = $fmtIso($dariVal);
    $sampaiIso = $fmtIso($sampaiVal);
@endphp

<div data-sbo-date-range-wrap>
    @if($showLabel)
        <label class="form-label small fw-semibold text-muted text-uppercase">{{ $label }}</label>
    @endif
    <input
        type="text"
        data-sbo-date-range
        value="{{ $displayRange }}"
        placeholder="dd/mm/yy — dd/mm/yy"
        autocomplete="off"
        {{ $attributes->merge(['class' => 'form-control form-control-sm']) }}
    >
    <input type="hidden" name="{{ $nameDari }}" data-sbo-range-dari value="{{ $dariIso }}">
    <input type="hidden" name="{{ $nameSampai }}" data-sbo-range-sampai value="{{ $sampaiIso }}">
</div>
