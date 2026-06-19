@include('_partials.sbo_checkbox_group', [
    'label' => $label ?? 'Alat Keamanan',
    'fieldName' => $fieldName ?? 'alatkeamanan',
    'options' => $options ?? [],
    'selected' => $selected ?? [],
    'idPrefix' => $idPrefix ?? 'alat_keamanan',
    'hint' => $hint ?? 'Pilih satu atau lebih sesuai ketersediaan.',
    'labelClass' => $labelClass ?? 'block text-[10px] font-bold text-slate-500 uppercase tracking-wide mb-0.5',
    'errClass' => $errClass ?? 'text-[11px] text-red-600 font-medium mt-0.5 block',
])
