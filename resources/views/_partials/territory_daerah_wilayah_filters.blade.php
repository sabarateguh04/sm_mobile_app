@php
    $filterStyle = $filterStyle ?? 'form';
    $daerahChangeSubmit = $daerahChangeSubmit ?? true;
    $wilayahChangeSubmit = $wilayahChangeSubmit ?? true;
@endphp

@if(\App\Support\TerritoryIndexFilters::canFilterByDaerah())
    <div class="@if($filterStyle === 'tailwind-compact') relative pmf-territory shrink-0 @elseif($filterStyle === 'tailwind') relative pmf-territory min-w-[140px] flex-1 @elseif($filterStyle === 'sidebar') mb-4 @elseif($filterStyle === 'row') col @else mb-3 @endif">
        @if($filterStyle === 'tailwind-compact')
            <select name="daerah" class="pmf-control pmf-field-text pmf-native-select w-full bg-slate-50 border border-slate-200 rounded-lg pl-2.5 pr-7 focus:border-primary focus:ring-0 focus:bg-white outline-none transition-all" data-empty-label="Polda"
        @elseif($filterStyle === 'tailwind')
            <label class="absolute -top-2 left-3 text-[9px] tracking-widest px-1 bg-white font-extrabold text-slate-500 uppercase leading-none z-10">Polda</label>
            <select name="daerah" class="pmf-control w-full appearance-none bg-slate-50 border border-slate-200 rounded-xl px-4 py-2.5 pr-8 text-label-md focus:border-primary focus:ring-0 focus:bg-white outline-none transition-all select2" data-placeholder="— Semua Polda —"
        @elseif($filterStyle === 'sidebar')
            <label class="text-uppercase fw-bold mb-2 d-block" style="font-size:11px;letter-spacing:.6px;color:#64748b;">
                <i class="fas fa-map me-1"></i> Polda
            </label>
            <select name="daerah" class="form-select form-select-sm select2" data-placeholder="— Semua Polda —"
        @else
            <label class="form-label small fw-semibold text-muted text-uppercase">Polda</label>
            <select name="daerah" class="form-select form-select-sm select2" data-placeholder="— Semua Polda —"
        @endif
            @if($daerahChangeSubmit) onchange="var w=this.form.querySelector('[name=wilayah]'); if(w){w.value='';} this.form.submit();" @endif>
            @if($filterStyle === 'tailwind-compact')<option value="">Polda</option>@else<option value="">— Semua Polda —</option>@endif
            @foreach($daerahList ?? [] as $d)
                <option value="{{ $d }}" {{ request('daerah') === $d ? 'selected' : '' }}>{{ strtoupper($d) }}</option>
            @endforeach
        </select>
    </div>
@endif

@if(\App\Support\TerritoryIndexFilters::canFilterByWilayah() && auth()->check() && !\App\Helpers\MenuHelper::isOperatorForLayout(auth()->user()))
    <div class="@if($filterStyle === 'tailwind-compact') relative pmf-territory shrink-0 @elseif($filterStyle === 'tailwind') relative pmf-territory min-w-[140px] flex-1 @elseif($filterStyle === 'sidebar') mb-4 @elseif($filterStyle === 'row') col @else mb-3 @endif">
        @if($filterStyle === 'tailwind-compact')
            <select name="wilayah" class="pmf-control pmf-field-text pmf-native-select w-full bg-slate-50 border border-slate-200 rounded-lg pl-2.5 pr-7 focus:border-primary focus:ring-0 focus:bg-white outline-none transition-all" data-empty-label="Polres"
        @elseif($filterStyle === 'tailwind')
            <label class="absolute -top-2 left-3 text-[9px] tracking-widest px-1 bg-white font-extrabold text-slate-500 uppercase leading-none z-10">Polres</label>
            <select name="wilayah" class="pmf-control w-full appearance-none bg-slate-50 border border-slate-200 rounded-xl px-4 py-2.5 pr-8 text-label-md focus:border-primary focus:ring-0 focus:bg-white outline-none transition-all select2" data-placeholder="— Semua Polres —"
        @elseif($filterStyle === 'sidebar')
            <label class="text-uppercase fw-bold mb-2 d-block" style="font-size:11px;letter-spacing:.6px;color:#64748b;">
                <i class="fas fa-map-marker-alt me-1"></i> Polres
            </label>
            <select name="wilayah" class="form-select form-select-sm select2" data-placeholder="— Semua Polres —"
        @else
            <label class="form-label small fw-semibold text-muted text-uppercase">Polres</label>
            <select name="wilayah" class="form-select form-select-sm select2" data-placeholder="— Semua Polres —"
        @endif
            @if($wilayahChangeSubmit) onchange="this.form.submit();" @endif>
            @if($filterStyle === 'tailwind-compact')<option value="">Polres</option>@else<option value="">— Semua Polres —</option>@endif
            @foreach($wilayahList ?? [] as $w)
                <option value="{{ $w }}" {{ request('wilayah') === $w ? 'selected' : '' }}>{{ strtoupper(trim(str_ireplace(['KABUPATEN ', 'KOTA ', 'KAB. '], '', $w))) }}</option>
            @endforeach
        </select>
        @if(!in_array($filterStyle, ['tailwind', 'tailwind-compact'], true) && request('daerah'))
            <small class="text-muted d-block mt-1">Polres di Polda: {{ strtoupper(request('daerah')) }}</small>
        @endif
    </div>
@endif
