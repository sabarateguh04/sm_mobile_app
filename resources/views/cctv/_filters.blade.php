<form action="{{ route('patrolicctv.index') }}" method="GET" id="patrolCctvFilterForm" class="sbo-compact-filters bg-white rounded-lg p-2 border border-slate-200/60 shadow-sm relative z-10">
    <div class="pmf-row">
        @include('_partials.compact_filters.territory')
        @include('_partials.compact_filters.date_range', ['prefix' => 'patrol-cctv'])
        @if($patrolCols['peristiwa'] ?? null)
            @include('_partials.compact_filters.select', ['name' => 'peristiwa', 'placeholder' => 'Peristiwa', 'options' => $peristiwaList ?? []])
        @endif
        @include('_partials.compact_filters.search', ['placeholder' => 'Cari lokasi, peristiwa...'])
        @include('_partials.compact_filters.actions', [
            'resetUrl' => route('patrolicctv.index'),
            'active' => request()->hasAny(['search','tanggal_dari','tanggal_sampai','peristiwa','daerah','wilayah']),
        ])
    </div>
</form>
