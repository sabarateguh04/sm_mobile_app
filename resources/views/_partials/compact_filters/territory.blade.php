@if(($userLevel ?? 'nasional') === 'nasional' || \App\Helpers\HierarchicalHelper::viewerBypassesTerritoryScope())
    <div class="pmf-territory">
        <select name="daerah"
                class="pmf-control pmf-field-text pmf-native-select w-full h-full bg-slate-50 border border-slate-200 rounded-lg pl-2.5 pr-7 focus:border-primary focus:ring-0 focus:bg-white outline-none transition-all {{ request('daerah') ? '' : 'pmf-empty' }}"
                onchange="var w=this.form.querySelector('[name=wilayah]'); if(w){w.value='';} this.form.submit();">
            <option value="">Polda</option>
            @foreach($daerahList ?? [] as $d)
                <option value="{{ $d }}" @selected(request('daerah') === $d)>{{ strtoupper($d) }}</option>
            @endforeach
        </select>
    </div>
@endif

@if(in_array($userLevel ?? 'nasional', ['nasional', 'provinsi'], true) || \App\Helpers\HierarchicalHelper::viewerBypassesTerritoryScope())
    <div class="pmf-territory">
        <select name="wilayah"
                class="pmf-control pmf-field-text pmf-native-select w-full h-full bg-slate-50 border border-slate-200 rounded-lg pl-2.5 pr-7 focus:border-primary focus:ring-0 focus:bg-white outline-none transition-all {{ request('wilayah') ? '' : 'pmf-empty' }}"
                onchange="this.form.submit();">
            <option value="">Polres</option>
            @foreach($wilayahList ?? [] as $w)
                <option value="{{ $w }}" @selected(request('wilayah') === $w)>{{ strtoupper(trim(str_ireplace(['KABUPATEN ', 'KOTA ', 'KAB. '], '', $w))) }}</option>
            @endforeach
        </select>
    </div>
@endif
