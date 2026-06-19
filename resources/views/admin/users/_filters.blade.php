@php
    $filterActive = request()->hasAny([
        'daerah', 'wilayah', 'user_role_id', 'account_status', 'search',
        'kategori_user', 'induk_pemerintahan',
    ]);
@endphp

<form method="GET" action="{{ route('admin.users.index') }}" class="sbo-compact-filters bg-white rounded-lg p-2 border border-slate-200/60 shadow-sm relative z-10">
    <div class="pmf-row">
        @if(!empty($executiveUserManagementReadOnly))
            @if(!empty($isExecutive1))
                @include('_partials.compact_filters.territory')
                <div class="pmf-extra">
                    <input type="text" name="induk_pemerintahan" value="{{ request('induk_pemerintahan') }}"
                           class="pmf-control pmf-field-text w-full h-full bg-slate-50 border border-slate-200 rounded-lg px-2.5 focus:border-primary focus:bg-white focus:ring-0 outline-none transition-all"
                           placeholder="Organisasi" onchange="this.form.submit()">
                </div>
            @else
                <div class="pmf-territory">
                    <select name="wilayah"
                            class="pmf-control pmf-field-text pmf-native-select w-full h-full bg-slate-50 border border-slate-200 rounded-lg pl-2.5 pr-7 focus:border-primary focus:ring-0 focus:bg-white outline-none transition-all {{ request('wilayah') ? '' : 'pmf-empty' }}"
                            onchange="this.form.submit()">
                        <option value="">Polres</option>
                        @foreach($wilayahList ?? [] as $w)
                            <option value="{{ $w }}" @selected(request('wilayah') === $w)>{{ strtoupper(trim(str_ireplace(['KABUPATEN ', 'KOTA ', 'KAB. '], '', $w))) }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="pmf-extra">
                    <select name="kategori_user"
                            class="pmf-control pmf-field-text pmf-native-select w-full h-full bg-slate-50 border border-slate-200 rounded-lg pl-2.5 pr-7 focus:border-primary focus:ring-0 focus:bg-white outline-none transition-all {{ request('kategori_user') ? '' : 'pmf-empty' }}"
                            onchange="this.form.submit()">
                        <option value="">Kategori</option>
                        <option value="organik" @selected(request('kategori_user') === 'organik')>Organik</option>
                        <option value="non_organik" @selected(request('kategori_user') === 'non_organik')>Non-Organik</option>
                    </select>
                </div>
                <div class="pmf-extra">
                    <input type="text" name="induk_pemerintahan" value="{{ request('induk_pemerintahan') }}"
                           class="pmf-control pmf-field-text w-full h-full bg-slate-50 border border-slate-200 rounded-lg px-2.5 focus:border-primary focus:bg-white focus:ring-0 outline-none transition-all"
                           placeholder="Organisasi" onchange="this.form.submit()">
                </div>
            @endif
        @else
            @include('_partials.compact_filters.territory')
            <div class="pmf-extra">
                <select name="user_role_id"
                        class="pmf-control pmf-field-text pmf-native-select w-full h-full bg-slate-50 border border-slate-200 rounded-lg pl-2.5 pr-7 focus:border-primary focus:ring-0 focus:bg-white outline-none transition-all {{ request('user_role_id') ? '' : 'pmf-empty' }}"
                        onchange="this.form.submit()">
                    <option value="">Role</option>
                    @foreach($rolesForFilter ?? [] as $r)
                        <option value="{{ $r->role_id }}" @selected(request('user_role_id') == $r->role_id)>{{ $r->role_name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="pmf-extra">
                <select name="account_status"
                        class="pmf-control pmf-field-text pmf-native-select w-full h-full bg-slate-50 border border-slate-200 rounded-lg pl-2.5 pr-7 focus:border-primary focus:ring-0 focus:bg-white outline-none transition-all {{ request('account_status') ? '' : 'pmf-empty' }}"
                        onchange="this.form.submit()">
                    <option value="">Status</option>
                    <option value="active" @selected(request('account_status') === 'active')>Aktif</option>
                    <option value="pending" @selected(request('account_status') === 'pending')>Pending</option>
                    <option value="blocked" @selected(request('account_status') === 'blocked')>Diblokir</option>
                </select>
            </div>
        @endif

        @include('_partials.compact_filters.search', ['placeholder' => 'Cari nama, username, email...'])

        @include('_partials.compact_filters.actions', [
            'resetUrl' => route('admin.users.index'),
            'active' => $filterActive,
        ])
    </div>
</form>
