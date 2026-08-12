@if(\App\Support\TerritoryIndexFilters::canFilterByDaerah())
    @php
        $isLockedPolda = ($userLevel ?? \App\Helpers\HierarchicalHelper::getUserLevel()) === \App\Support\TerritoryLevelForUsm::PROVINSI;
        $daerahCount = count($daerahList ?? []);
    @endphp
    <div class="pmf-territory">
        <select name="daerah"
                class="pmf-control pmf-field-text pmf-native-select w-full h-full bg-slate-50 border border-slate-200 rounded-lg pl-2.5 pr-7 focus:border-primary focus:ring-0 focus:bg-white outline-none transition-all {{ request('daerah') || $daerahCount === 1 ? '' : 'pmf-empty' }} {{ $isLockedPolda ? 'opacity-75 cursor-not-allowed' : '' }}"
                onchange="var w=this.form.querySelector('[name=wilayah]'); if(w){w.value='';} this.form.submit();"
                @if($isLockedPolda) disabled @endif>
            <option value="">Polda</option>
            @foreach($daerahList ?? [] as $d)
                <option value="{{ $d }}" @selected(request('daerah') === $d || $daerahCount === 1)>{{ strtoupper($d) }}</option>
            @endforeach
        </select>
        @if($isLockedPolda && $daerahCount === 1)
            <input type="hidden" name="daerah" value="{{ ($daerahList ?? [])[0] ?? '' }}">
        @endif
    </div>
@endif

@if(\App\Support\TerritoryIndexFilters::canFilterByWilayah() && auth()->check() && !\App\Helpers\MenuHelper::isOperatorForLayout(auth()->user()))
    @php
        $wilayahCount = count($wilayahList ?? []);
        $isLockedWilayah = $wilayahCount === 1;
    @endphp
    <div class="pmf-territory">
        <select name="wilayah"
                class="pmf-control pmf-field-text pmf-native-select w-full h-full bg-slate-50 border border-slate-200 rounded-lg pl-2.5 pr-7 focus:border-primary focus:ring-0 focus:bg-white outline-none transition-all {{ request('wilayah') || $wilayahCount === 1 ? '' : 'pmf-empty' }} {{ $isLockedWilayah ? 'opacity-75 cursor-not-allowed' : '' }}"
                onchange="this.form.submit();"
                @if($isLockedWilayah) disabled @endif>
            <option value="">Polres</option>
            @foreach($wilayahList ?? [] as $w)
                <option value="{{ $w }}" @selected(request('wilayah') === $w || $wilayahCount === 1)>{{ strtoupper(trim(str_ireplace(['KABUPATEN ', 'KOTA ', 'KAB. '], '', $w))) }}</option>
            @endforeach
        </select>
        @if($isLockedWilayah)
            <input type="hidden" name="wilayah" value="{{ ($wilayahList ?? [])[0] ?? '' }}">
        @endif
    </div>
@endif
