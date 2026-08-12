<form action="{{ route('personil.manager.index') }}" method="GET" class="sbo-compact-filters bg-white rounded-lg p-2 border border-slate-200/60 shadow-sm relative z-10">
    <div class="pmf-row">
        @include('_partials.compact_filters.territory')
        @if(\App\Helpers\MenuHelper::isSuperAdminForLayout(auth()->user()))
            @include('_partials.compact_filters.date_range', ['prefix' => 'personil-mgr', 'fromName' => 'tanggal_from', 'toName' => 'tanggal_to'])
        @endif
        @if(Schema::hasColumn('usm', 'kategori_user'))
            <div class="pmf-extra">
                <select name="kategori_user"
                        class="pmf-control pmf-field-text pmf-native-select w-full h-full bg-slate-50 border border-slate-200 rounded-lg pl-2.5 pr-7 focus:border-primary focus:ring-0 focus:bg-white outline-none transition-all {{ request('kategori_user') ? '' : 'pmf-empty' }}"
                        onchange="this.form.submit()">
                    <option value="">Kategori</option>
                    <option value="organik" @selected(request('kategori_user') === 'organik')>Organik</option>
                    <option value="non_organik" @selected(request('kategori_user') === 'non_organik')>Non-Organik</option>
                </select>
            </div>
        @endif
        @if(Schema::hasColumn($tableName ?? 'usm', 'induk_pemerintahan')
            || Schema::hasColumn($tableName ?? 'usm', 'pemerintahan_daerah')
            || Schema::hasColumn($tableName ?? 'usm', 'organisasi_daerah'))
            @include('_partials.compact_filters.select', [
                'name' => 'induk_pemerintahan',
                'placeholder' => 'Organisasi',
                'options' => $organisasiFilterOptions ?? $indukOrganisasiFilterOptions ?? [],
            ])
        @endif
        @if(Schema::hasColumn($tableName ?? 'usm', 'unit_kerja'))
            @include('_partials.compact_filters.select', [
                'name' => 'unit_kerja',
                'placeholder' => 'Sub Org',
                'options' => $subOrgFilterOptions ?? [],
            ])
        @endif
        @include('_partials.compact_filters.search', ['placeholder' => 'Cari nama, username...'])
        @include('_partials.compact_filters.actions', [
            'resetUrl' => route('personil.manager.index'),
            'active' => request()->hasAny(['daerah','wilayah','tanggal_from','tanggal_to','search','kategori_user','induk_pemerintahan','unit_kerja']),
        ])
    </div>
</form>
