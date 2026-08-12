@php
    $hasFilters = request()->hasAny(['search', 'tanggal_dari', 'tanggal_sampai', 'daerah', 'wilayah', 'source_server']);
    
    // Sembunyikan kolom polda/polres untuk level operator (karena datanya statis milik mereka sendiri)
    if (auth()->check() && \App\Helpers\MenuHelper::isOperatorForLayout(auth()->user())) {
        $displayColumns = array_values(array_filter($displayColumns, function($col) {
            return !in_array(strtolower($col), ['polda', 'polres', 'daerah', 'wilayah']);
        }));
    }
@endphp

<form action="{{ $filterUrl ?? route('sm.dacol.index') }}" method="GET"
      class="sbo-compact-filters bg-white rounded-lg p-2 border border-slate-200/60 shadow-sm">
    @if(isset($filterHiddenInputs))
        {!! $filterHiddenInputs !!}
    @else
        <input type="hidden" name="table" value="{{ $active }}">
    @endif
    <div class="pmf-row">
        @include('_partials.compact_filters.search', ['placeholder' => 'Cari...'])
        @include('_partials.compact_filters.territory')

        {{-- Filter Source Server (SM-SOLO, SM-JOGJA, dll) --}}
        @if(!empty($availableSources) && count($availableSources) > 1)
        <div class="pmf-item">
            <select name="source_server" class="pmf-select">
                <option value="">— Semua Sumber —</option>
                @foreach($availableSources as $src)
                    <option value="{{ $src }}" {{ request('source_server') === $src ? 'selected' : '' }}>
                        {{ $src }}
                    </option>
                @endforeach
            </select>
        </div>
        @elseif(!empty($availableSources) && count($availableSources) === 1)
        {{-- Hanya 1 sumber — tampilkan sebagai badge info saja --}}
        <div class="pmf-item">
            <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-md bg-slate-100 text-[10px] font-bold text-slate-500">
                <span class="material-symbols-outlined text-[13px]">dns</span>
                {{ $availableSources[0] }}
            </span>
        </div>
        @endif

        @include('_partials.compact_filters.date_range', ['prefix' => 'sm_dacol'])
        @include('_partials.compact_filters.actions', [
            'resetUrl' => $filterResetUrl ?? route('sm.dacol.index', ['table' => $active]),
            'active'   => $hasFilters,
        ])
    </div>
</form>

<section class="bg-white rounded-xl border border-slate-200/60 overflow-hidden shadow-sm mt-3">
    @if($rows && $rows->count() > 0)
        <div class="overflow-x-auto custom-scrollbar">
            <table class="w-full text-left" style="min-width: {{ max(640, count($displayColumns) * 130) }}px">
                <thead>
                    <tr class="bg-slate-50/50">
                        <th class="px-4 py-2 text-[10px] font-bold text-slate-400 uppercase tracking-widest border-b border-slate-100 w-10">#</th>
                        @foreach($displayColumns as $col)
                            <th class="px-3 py-2 text-[10px] font-bold text-slate-400 uppercase tracking-widest border-b border-slate-100">
                                {{ str_replace('_', ' ', $col) }}
                            </th>
                        @endforeach
                        <th class="px-3 py-2 text-[10px] font-bold text-slate-400 uppercase tracking-widest border-b border-slate-100 w-16">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach($rows as $i => $row)
                        <tr class="hover:bg-slate-50/50">
                            <td class="px-4 py-2 text-[11px] text-slate-400">{{ $rows->firstItem() + $i }}</td>
                            @foreach($displayColumns as $col)
                                <td class="px-3 py-2 text-[11px] text-slate-700 max-w-[200px] truncate" title="{{ $row->{$col} ?? '' }}">
                                    @php $val = $row->{$col} ?? null; @endphp
                                    @if(is_numeric($val) && preg_match('/jumlah|total|penduduk|laki|perempuan/i', $col))
                                        {{ number_format((float) $val, 0, ',', '.') }}
                                    @else
                                        {{ $val !== null && $val !== '' ? $val : '—' }}
                                    @endif
                                </td>
                            @endforeach
                            <td class="px-3 py-2">
                                @php
                                    $pk = $meta['pk'] ?? 'pk_ids';
                                    $actualPk = null;
                                    $rowObj = (object) $row;
                                    if (isset($rowObj->{$pk})) {
                                        $actualPk = $pk;
                                    } elseif (isset($rowObj->pk_ids)) {
                                        $actualPk = 'pk_ids';
                                    } elseif (isset($rowObj->rowid)) {
                                        $actualPk = 'rowid';
                                    } elseif (isset($rowObj->id)) {
                                        $actualPk = 'id';
                                    }
                                @endphp
                                @if($actualPk)
                                    <a href="{{ route('sm.dacol.show', [$active, $rowObj->{$actualPk}]) }}"
                                       class="text-[10px] font-semibold text-primary hover:underline">Detail</a>
                                @else
                                    <span class="text-red-500 text-[10px]">PK Not Found (Keys: {{ implode(',', array_keys((array)$row)) }})</span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @if($rows->hasPages())
            <div class="px-4 py-3 border-t border-slate-100">{{ $rows->links() }}</div>
        @endif
    @else
        <div class="py-10 text-center px-4">
            <p class="text-label-md text-slate-500 font-medium m-0">Tidak ada data.</p>
        </div>
    @endif
</section>
