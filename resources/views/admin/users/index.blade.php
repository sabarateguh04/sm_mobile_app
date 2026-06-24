@extends('layouts.app')

@php
    $pageTitle = !empty($executiveUserManagementReadOnly)
        ? 'Personil BackOffice'
        : (\App\Helpers\MenuHelper::isSuperAdminForLayout(auth()->user()) ? 'Personil Manager' : 'User Management');
    $stats = $userStats ?? ['total' => $users->total(), 'active' => 0, 'pending' => 0, 'blocked' => 0];
    $subtitle = !empty($executiveUserManagementReadOnly)
        ? (!empty($isExecutive2)
            ? 'Executive provinsi/Polda — hanya lihat operator di provinsi kerja Anda.'
            : 'Daftar operator dalam wilayah kerja Anda (hanya lihat).')
        : 'Kelola akun, role, dan verifikasi pendaftaran.';
    $rolesList = $rolesForFilter ?? \Illuminate\Support\Facades\DB::table('roles')->orderBy('role_name')->get();
    $filterActive = request()->hasAny([
        'daerah', 'wilayah', 'user_role_id', 'account_status', 'search',
        'kategori_user', 'induk_pemerintahan',
    ]);
@endphp

@section('title', $pageTitle)
@section('page-title', $pageTitle)

@section('content')
<div class="max-w-[1400px] mx-auto w-full space-y-4">

<section class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
    <div>
        <h2 class="text-[22px] font-headline-lg font-extrabold text-on-surface tracking-tight leading-tight m-0">{{ $pageTitle }}</h2>
        <p class="text-[11px] text-slate-400 mt-0.5 m-0">{{ $subtitle }}</p>
    </div>
    @if(empty($executiveUserManagementReadOnly))
    <div class="flex flex-wrap items-center gap-2 shrink-0">
        @if(($pendingVerificationCount ?? 0) > 0)
            <a href="{{ route('admin.users.index', array_merge(request()->except('page'), ['account_status' => 'pending'])) }}"
               class="flex items-center gap-1.5 bg-amber-50 text-amber-800 border border-amber-200/80 px-4 py-2 rounded-xl font-bold text-[11px] hover:bg-amber-100 transition-colors text-decoration-none">
                <span class="material-symbols-outlined text-[18px]">hourglass_top</span>
                {{ $pendingVerificationCount }} menunggu verifikasi
            </a>
        @endif
        <a href="{{ route('admin.users.create') }}"
           class="flex items-center justify-center gap-2 bg-primary text-on-primary px-5 py-2.5 rounded-xl font-bold text-label-md shadow-lg shadow-primary/25 hover:shadow-xl hover:-translate-y-0.5 active:translate-y-0 transition-all text-decoration-none">
            <span class="material-symbols-outlined text-[20px]" style="font-variation-settings: 'FILL' 1;">person_add</span>
            <span>Tambah User</span>
        </a>
    </div>
    @endif
</section>

<section class="grid grid-cols-2 lg:grid-cols-4 gap-3">
    @foreach([
        ['icon' => 'groups', 'bg' => 'bg-primary/10', 'color' => 'text-primary', 'label' => 'Total', 'val' => $stats['total'], 'num_class' => 'text-on-surface'],
        ['icon' => 'verified_user', 'bg' => 'bg-secondary/10', 'color' => 'text-secondary', 'label' => 'Aktif', 'val' => $stats['active'], 'num_class' => 'text-on-surface'],
        ['icon' => 'hourglass_top', 'bg' => 'bg-amber-100', 'color' => 'text-amber-600', 'label' => 'Pending', 'val' => $stats['pending'], 'num_class' => 'text-on-surface'],
        ['icon' => 'block', 'bg' => 'bg-rose-100', 'color' => 'text-rose-600', 'label' => 'Diblokir', 'val' => $stats['blocked'], 'num_class' => $stats['blocked'] > 0 ? 'text-rose-600' : 'text-on-surface'],
    ] as $s)
    <div class="stat-card bg-white rounded-xl border border-slate-200/60 shadow-sm px-4 py-3 flex items-center gap-3">
        <div class="w-8 h-8 {{ $s['bg'] }} {{ $s['color'] }} rounded-lg flex items-center justify-center shrink-0">
            <span class="material-symbols-outlined text-[18px]" style="font-variation-settings: 'FILL' 1;">{{ $s['icon'] }}</span>
        </div>
        <div>
            <p class="text-[10px] text-slate-400 font-semibold leading-none mb-1">{{ $s['label'] }}</p>
            <p class="text-[20px] font-extrabold {{ $s['num_class'] }} leading-none m-0">{{ number_format($s['val'], 0, ',', '.') }}</p>
        </div>
    </div>
    @endforeach
</section>

@if(session('success'))
    <div class="bg-secondary-container text-on-secondary-container border border-secondary/20 rounded-2xl p-4 flex justify-between items-center shadow-sm">
        <div class="flex items-center gap-3">
            <span class="material-symbols-outlined" style="font-variation-settings: 'FILL' 1;">check_circle</span>
            <span class="font-medium text-sm">{{ session('success') }}</span>
        </div>
        <button type="button" class="text-on-secondary-container/50 hover:text-on-secondary-container bg-transparent border-0" onclick="this.parentElement.remove()">
            <span class="material-symbols-outlined">close</span>
        </button>
    </div>
@endif
@if(session('warning'))
    <div class="rounded-xl px-4 py-2.5 flex items-center gap-2 border border-amber-200 bg-amber-50 text-amber-800 text-label-md font-medium">
        <span class="material-symbols-outlined text-[18px] text-amber-500 shrink-0">warning</span>
        {{ session('warning') }}
    </div>
@endif

@if(!empty($listingTerritoryBlocked))
    <div class="rounded-xl px-4 py-2.5 flex items-center gap-2 border border-amber-200 bg-amber-50 text-amber-800 text-label-md font-medium">
        <span class="material-symbols-outlined text-[18px] text-amber-500 shrink-0">warning</span>
        <span>
            {{ $listingTerritoryHint ?? 'Lengkapi Polda wilayah kerja di profil organisasi Anda untuk melihat daftar pengguna.' }}
            <a href="{{ route('profile.show') }}" class="font-bold text-amber-900 underline ms-1">Buka profil</a>
        </span>
    </div>
@elseif(!empty($listingTerritoryHint))
    <div class="rounded-xl px-4 py-2.5 flex items-center gap-2 border border-sky-200 bg-sky-50 text-sky-800 text-label-md font-medium">
        <span class="material-symbols-outlined text-[18px] text-sky-500 shrink-0">info</span>
        <span>
            {{ $listingTerritoryHint }}
            @if(str_contains($listingTerritoryHint, 'belum diisi'))
                <a href="{{ route('profile.edit') }}" class="font-bold text-sky-900 underline ms-1">Lengkapi profil</a>
            @endif
        </span>
    </div>
@endif

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
                    <select name="wilayah" class="pmf-control pmf-field-text pmf-native-select w-full h-full bg-slate-50 border border-slate-200 rounded-lg pl-2.5 pr-7 focus:border-primary focus:ring-0 focus:bg-white outline-none transition-all {{ request('wilayah') ? '' : 'pmf-empty' }}" onchange="this.form.submit()">
                        <option value="">Polres</option>
                        @foreach($wilayahList ?? [] as $w)
                            <option value="{{ $w }}" @selected(request('wilayah') === $w)>{{ strtoupper(trim(str_ireplace(['KABUPATEN ', 'KOTA ', 'KAB. '], '', $w))) }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="pmf-extra">
                    <select name="kategori_user" class="pmf-control pmf-field-text pmf-native-select w-full h-full bg-slate-50 border border-slate-200 rounded-lg pl-2.5 pr-7 focus:border-primary focus:ring-0 focus:bg-white outline-none transition-all {{ request('kategori_user') ? '' : 'pmf-empty' }}" onchange="this.form.submit()">
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
                <select name="user_role_id" class="pmf-control pmf-field-text pmf-native-select w-full h-full bg-slate-50 border border-slate-200 rounded-lg pl-2.5 pr-7 focus:border-primary focus:ring-0 focus:bg-white outline-none transition-all {{ request('user_role_id') ? '' : 'pmf-empty' }}" onchange="this.form.submit()">
                    <option value="">Role</option>
                    @foreach($rolesList as $r)
                        <option value="{{ $r->role_id }}" @selected(request('user_role_id') == $r->role_id)>{{ $r->role_name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="pmf-extra">
                <select name="account_status" class="pmf-control pmf-field-text pmf-native-select w-full h-full bg-slate-50 border border-slate-200 rounded-lg pl-2.5 pr-7 focus:border-primary focus:ring-0 focus:bg-white outline-none transition-all {{ request('account_status') ? '' : 'pmf-empty' }}" onchange="this.form.submit()">
                    <option value="">Status</option>
                    <option value="active" @selected(request('account_status') === 'active')>Aktif</option>
                    <option value="pending" @selected(request('account_status') === 'pending')>Pending</option>
                    <option value="blocked" @selected(request('account_status') === 'blocked')>Diblokir</option>
                </select>
            </div>
        @endif
        @include('_partials.compact_filters.search', ['placeholder' => 'Cari nama, username, email...'])
        @include('_partials.compact_filters.actions', ['resetUrl' => route('admin.users.index'), 'active' => $filterActive])
    </div>
</form>

<section class="bg-white rounded-xl border border-slate-200/60 overflow-hidden shadow-sm">
    <div class="px-4 py-3 border-b border-slate-100 flex flex-wrap items-center justify-between gap-2">
        <h3 class="text-[11px] font-bold uppercase tracking-wider text-slate-700 m-0">Daftar pengguna</h3>
        @include('_partials.export_dropdown', [
            'routeName' => 'admin.users.export',
            'query' => request()->query(),
            'variant' => 'hero',
        ])
    </div>

    @if($users->count())
        <div class="overflow-x-auto custom-scrollbar">
            <table class="w-full text-left min-w-[900px]">
                <thead>
                    <tr class="bg-slate-50/50">
                        <th class="px-4 py-2 text-[10px] font-bold text-slate-400 uppercase tracking-widest border-b border-slate-100 w-10">#</th>
                        @if(!empty($executiveUserManagementReadOnly))
                            @if(!empty($isExecutive1))
@if(\App\Support\TerritoryIndexFilters::canFilterByDaerah())
                                <th class="px-3 py-2 text-[10px] font-bold text-slate-400 uppercase tracking-widest border-b border-slate-100">Polda</th>
@endif
                                <th class="px-3 py-2 text-[10px] font-bold text-slate-400 uppercase tracking-widest border-b border-slate-100">Polres</th>
                                <th class="px-3 py-2 text-[10px] font-bold text-slate-400 uppercase tracking-widest border-b border-slate-100">Nama</th>
                                <th class="px-3 py-2 text-[10px] font-bold text-slate-400 uppercase tracking-widest border-b border-slate-100">Organisasi</th>
                                <th class="px-3 py-2 text-[10px] font-bold text-slate-400 uppercase tracking-widest border-b border-slate-100">Kategori</th>
                            @else
                                <th class="px-3 py-2 text-[10px] font-bold text-slate-400 uppercase tracking-widest border-b border-slate-100">Polres</th>
                                <th class="px-3 py-2 text-[10px] font-bold text-slate-400 uppercase tracking-widest border-b border-slate-100">Nama</th>
                                <th class="px-3 py-2 text-[10px] font-bold text-slate-400 uppercase tracking-widest border-b border-slate-100">Kategori</th>
                                <th class="px-3 py-2 text-[10px] font-bold text-slate-400 uppercase tracking-widest border-b border-slate-100">Organisasi</th>
                            @endif
                        @else
@if(\App\Support\TerritoryIndexFilters::canFilterByDaerah())
                            <th class="px-3 py-2 text-[10px] font-bold text-slate-400 uppercase tracking-widest border-b border-slate-100">Polda</th>
@endif
                            <th class="px-3 py-2 text-[10px] font-bold text-slate-400 uppercase tracking-widest border-b border-slate-100">Polres</th>
                            <th class="px-3 py-2 text-[10px] font-bold text-slate-400 uppercase tracking-widest border-b border-slate-100">Nama</th>
                            <th class="px-3 py-2 text-[10px] font-bold text-slate-400 uppercase tracking-widest border-b border-slate-100">Username</th>
                            <th class="px-3 py-2 text-[10px] font-bold text-slate-400 uppercase tracking-widest border-b border-slate-100">Role</th>
                            <th class="px-3 py-2 text-[10px] font-bold text-slate-400 uppercase tracking-widest border-b border-slate-100">Status</th>
                        @endif
                        <th class="px-3 py-2 text-[10px] font-bold text-slate-400 uppercase tracking-widest border-b border-slate-100 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach($users as $u)
                        @php $st = strtolower($u->account_status ?? ''); @endphp
                        <tr class="hover:bg-slate-50 group">
                            <td class="px-4 py-2.5 text-label-sm text-slate-400">
                                {{ $loop->iteration + ($users->currentPage() - 1) * $users->perPage() }}
                            </td>
                            @if(!empty($executiveUserManagementReadOnly))
                                @if(!empty($isExecutive1))
                                    <td class="px-3 py-2.5 text-[11px] font-bold text-[#D8A808] whitespace-nowrap">{{ $u->daerah ?: '—' }}</td>
                                    <td class="px-3 py-2.5 text-[11px] font-bold text-[#D8A808] whitespace-nowrap">{{ $u->wilayah ? trim(str_ireplace(['KABUPATEN ', 'KOTA ', 'KAB. '], '', $u->wilayah)) : '—' }}</td>
                                    <td class="px-3 py-2.5">
                                        <p class="text-[12px] font-bold text-slate-800 m-0">{{ $u->nama ?: '—' }}</p>
                                    </td>
                                    <td class="px-3 py-2.5 text-[11px] text-slate-600">{{ $u->induk_pemerintahan ?: '—' }}</td>
                                    <td class="px-3 py-2.5">
                                        @if(($u->kategori_user ?? '') === 'organik')
                                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200/60">Organik</span>
                                        @elseif(($u->kategori_user ?? '') === 'non_organik')
                                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-50 text-amber-700 border border-amber-200/60">Non-Organik</span>
                                        @else
                                            <span class="text-[11px] text-slate-400">—</span>
                                        @endif
                                    </td>
                                @else
                                    <td class="px-3 py-2.5 text-[11px] font-bold text-[#D8A808] whitespace-nowrap">{{ $u->wilayah ? trim(str_ireplace(['KABUPATEN ', 'KOTA ', 'KAB. '], '', $u->wilayah)) : '—' }}</td>
                                    <td class="px-3 py-2.5">
                                        <p class="text-[12px] font-bold text-slate-800 m-0">{{ $u->nama ?: '—' }}</p>
                                    </td>
                                    <td class="px-3 py-2.5">
                                        @if(($u->kategori_user ?? '') === 'organik')
                                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200/60">Organik</span>
                                        @elseif(($u->kategori_user ?? '') === 'non_organik')
                                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-50 text-amber-700 border border-amber-200/60">Non-Organik</span>
                                        @else
                                            <span class="text-[11px] text-slate-400">—</span>
                                        @endif
                                    </td>
                                    <td class="px-3 py-2.5 text-[11px] text-slate-600">{{ $u->induk_pemerintahan ?: '—' }}</td>
                                @endif
                            @else
                                <td class="px-3 py-2.5 text-[11px] font-bold text-[#D8A808] whitespace-nowrap">
                                    {{ $u->daerah ?: '—' }}
                                    @if($u->daerah && ! ($u->daerah_in_master ?? true))
                                        <span class="inline-flex items-center px-1 py-0 rounded text-[9px] font-bold bg-amber-100 text-amber-800 ms-0.5" title="Tidak cocok master Polda">!</span>
                                    @endif
                                </td>
                                <td class="px-3 py-2.5 text-[11px] font-bold text-[#D8A808] whitespace-nowrap">
                                    {{ $u->wilayah ? trim(str_ireplace(['KABUPATEN ', 'KOTA ', 'KAB. '], '', $u->wilayah)) : '—' }}
                                    @if($u->wilayah && ! ($u->wilayah_in_master ?? true))
                                        <span class="inline-flex items-center px-1 py-0 rounded text-[9px] font-bold bg-amber-100 text-amber-800 ms-0.5" title="Tidak cocok master Polres">!</span>
                                    @endif
                                </td>
                                <td class="px-3 py-2.5">
                                    <p class="text-[12px] font-bold text-slate-800 m-0">{{ $u->nama ?: '—' }}</p>
                                </td>
                                <td class="px-3 py-2.5">
                                    <code class="text-[10px] bg-slate-100 px-1.5 py-0.5 rounded text-slate-600">{{ $u->username }}</code>
                                </td>
                                <td class="px-3 py-2.5">
                                    <div class="dropdown">
                                        <button type="button" class="inline-flex items-center gap-0.5 border-0 bg-transparent p-0 shadow-none dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
                                            @if($u->role)
                                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-primary/10 text-primary border border-primary/20" title="{{ $roleDescriptions[$u->user_role_id] ?? '' }}">{{ $u->role->role_name }}</span>
                                            @elseif($u->user_role_id)
                                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-50 text-amber-800 border border-amber-200/60" title="Role ID tidak ada di master">ID {{ $u->user_role_id }}</span>
                                            @else
                                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-slate-100 text-slate-500">—</span>
                                            @endif
                                        </button>
                                        <ul class="dropdown-menu shadow-lg border-0 rounded-xl py-1" style="max-height: 250px; overflow-y: auto;">
                                            @foreach($rolesList as $r)
                                                <li>
                                                    <form method="POST" action="{{ route('admin.users.edit-field', $u->id_usm) }}" onsubmit="return confirm(@json('Ubah role menjadi '.$r->role_name.'?'));">
                                                        @csrf
                                                        <input type="hidden" name="name" value="user_role_id">
                                                        <input type="hidden" name="value" value="{{ $r->role_id }}">
                                                        <button type="submit" class="dropdown-item text-[11px] py-1.5 {{ $u->user_role_id == $r->role_id ? 'active' : '' }}">{{ $r->role_name }}</button>
                                                    </form>
                                                </li>
                                            @endforeach
                                        </ul>
                                    </div>
                                </td>
                                <td class="px-3 py-2.5">
                                    <div class="dropdown">
                                        <button type="button" class="inline-flex items-center gap-0.5 border-0 bg-transparent p-0 shadow-none dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
                                            @if($st === 'active')
                                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200/60">Aktif</span>
                                            @elseif($st === 'pending')
                                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-50 text-amber-700 border border-amber-200/60">Pending</span>
                                            @elseif($st === 'blocked')
                                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-rose-50 text-rose-700 border border-rose-200/60">Diblokir</span>
                                            @else
                                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-slate-100 text-slate-500">{{ $u->account_status ?: '—' }}</span>
                                            @endif
                                        </button>
                                        <ul class="dropdown-menu shadow-lg border-0 rounded-xl py-1 min-w-auto">
                                            @foreach(['active' => 'Aktif', 'pending' => 'Pending', 'blocked' => 'Diblokir'] as $val => $label)
                                                <li>
                                                    <form method="POST" action="{{ route('admin.users.edit-field', $u->id_usm) }}" onsubmit="return confirm(@json('Ubah status menjadi '.$label.'?'));">
                                                        @csrf
                                                        <input type="hidden" name="name" value="account_status">
                                                        <input type="hidden" name="value" value="{{ $val }}">
                                                        <button type="submit" class="dropdown-item text-[11px] py-1.5 {{ $st === $val ? 'active' : '' }}">{{ $label }}</button>
                                                    </form>
                                                </li>
                                            @endforeach
                                        </ul>
                                    </div>
                                </td>
                            @endif
                            <td class="px-3 py-2.5 text-right">
                                <div class="flex items-center justify-end gap-1 opacity-100 lg:opacity-0 group-hover:opacity-100 transition-opacity">
                                    <a href="{{ route('admin.users.show', $u->id_usm) }}" class="w-8 h-8 flex items-center justify-center text-primary hover:bg-primary/10 rounded-lg transition-colors text-decoration-none" title="Detail">
                                        <span class="material-symbols-outlined text-[20px]">visibility</span>
                                    </a>
                                    @if(empty($executiveUserManagementReadOnly))
                                        @if($st === 'pending')
                                            <form method="POST" action="{{ route('admin.users.verify', $u->id_usm) }}" class="inline m-0" onsubmit="return confirm(@json('Verifikasi dan aktifkan akun '.($u->nama ?: $u->username).'?'));">
                                                @csrf
                                                <button type="submit" class="w-8 h-8 flex items-center justify-center text-emerald-600 hover:bg-emerald-50 rounded-lg transition-colors border-0 bg-transparent p-0" title="Verifikasi">
                                                    <span class="material-symbols-outlined text-[20px]">verified</span>
                                                </button>
                                            </form>
                                        @endif
                                        <a href="{{ route('admin.users.edit', $u->id_usm) }}" class="w-8 h-8 flex items-center justify-center text-slate-400 hover:text-slate-600 hover:bg-slate-100 rounded-lg transition-colors text-decoration-none" title="Edit">
                                            <span class="material-symbols-outlined text-[20px]">edit</span>
                                        </a>
                                        @if(\App\Helpers\MenuHelper::isPersonilBoOperatorUser($u))
                                            <a href="{{ route('personil.manager.edit', $u->id_usm) }}" class="w-8 h-8 flex items-center justify-center text-primary hover:bg-primary/10 rounded-lg transition-colors text-decoration-none" title="Kuat Personil BO">
                                                <span class="material-symbols-outlined text-[20px]">groups</span>
                                            </a>
                                        @endif
                                        <form action="{{ route('admin.users.destroy', $u->id_usm) }}" method="POST" class="inline m-0" onsubmit="return confirm(@json('Yakin hapus user '.($u->nama ?: $u->username).'?'));">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="w-8 h-8 flex items-center justify-center text-slate-400 hover:text-error hover:bg-error-container/20 rounded-lg transition-colors border-0 bg-transparent p-0" title="Hapus">
                                                <span class="material-symbols-outlined text-[20px]">delete</span>
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="px-4 py-2.5 bg-slate-50/50 border-t border-slate-100 flex flex-col sm:flex-row items-center justify-between gap-3">
            <p class="text-[10px] text-slate-500 font-medium m-0">
                <span class="font-bold text-slate-700">{{ $users->firstItem() ?? 0 }}–{{ $users->lastItem() ?? 0 }}</span>
                dari {{ number_format($users->total(), 0, ',', '.') }} data
            </p>
            <div class="pagination-container">
                {{ $users->withQueryString()->onEachSide(1)->links() }}
            </div>
        </div>
    @else
        <div class="py-12 text-center px-4">
            <div class="w-12 h-12 bg-slate-100 rounded-full flex items-center justify-center mx-auto mb-3 text-slate-400">
                <span class="material-symbols-outlined text-[26px]">group_off</span>
            </div>
            <p class="text-label-md text-slate-500 font-medium max-w-md mx-auto m-0">
                @if(!empty($listingTerritoryBlocked))
                    Tidak ada data — lengkapi Polda di <a href="{{ route('profile.edit') }}" class="text-primary font-bold">profil organisasi</a> terlebih dahulu.
                @elseif(request()->filled('search') || request()->filled('wilayah') || request()->filled('daerah') || request()->filled('account_status'))
                    Tidak ada user untuk filter yang dipilih.
                @else
                    Tidak ada data user dalam cakupan wilayah Anda.
                @endif
            </p>
        </div>
    @endif
</section>

</div>
@endsection
