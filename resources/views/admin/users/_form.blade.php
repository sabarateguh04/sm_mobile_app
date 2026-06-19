@php
    $isEdit = isset($user) && $user;
    $registerMode = $registerMode ?? false;
    $myProfileMode = $myProfileMode ?? false;
    $wilayahPakaiMasterTerritori = $wilayahPakaiMasterTerritori ?? false;
    $wilayahPakaiMasterKlDaerah = $wilayahPakaiMasterKlDaerah ?? false;
    $userFormPakaiMasterKl = $userFormPakaiMasterKl ?? false;
    $klWilayahUi = ($registerMode || $userFormPakaiMasterKl) && $wilayahPakaiMasterKlDaerah;
    $daerahList = $daerahList ?? collect();
    $wilayahByDaerahJson = $wilayahByDaerahJson ?? [];
    $roleDescriptions = config('role_menu_matrix.role_descriptions', []);
    $orgsIndukForKepegawaian = $orgsInduk ?? collect();
    $suborgsUnitKerjaCol = $suborgsUnitKerja ?? collect();
    $indukPemerintahanUseSelect = $indukPemerintahanUseSelect ?? false;
    $unitKerjaUseSelect = $unitKerjaUseSelect ?? false;
    $pemerintahanDaerahUseSelect = $pemerintahanDaerahUseSelect ?? false;
    $pemerintahanDaerahResorts = isset($pemerintahanDaerahResorts) ? $pemerintahanDaerahResorts : collect();
    $organisasiDaerahListCol = $organisasiDaerahList ?? collect();
    $organisasiDaerahUseSelect = $organisasiDaerahUseSelect ?? false;
    $filterKepegawaianIndukPemerintahanDaerah = $filterKepegawaianIndukPemerintahanDaerah ?? false;
    $filterKepegawaianOrganisasiDaerahUnit = $filterKepegawaianOrganisasiDaerahUnit ?? false;
    $kepegawaianKlTigaTingkat = $kepegawaianKlTigaTingkat ?? false;
    $kepegawaianKlEmpatTingkat = $kepegawaianKlEmpatTingkat ?? false;
    $ufCompactMode = $myProfileMode || $registerMode;
    $ufRegisterMobileRow = $registerMode && ! $myProfileMode;
    $ufLabelClass = $ufCompactMode ? 'block text-[10px] font-bold text-slate-500 uppercase tracking-wide mb-0.5' : 'form-label';
    $ufInputClass = $ufCompactMode
        ? ($ufRegisterMobileRow
            ? 'w-full bg-slate-50 border border-slate-200 rounded-lg px-2 py-1.5 text-[11px] text-slate-800 placeholder:text-slate-400 focus:border-primary focus:bg-white focus:ring-0 outline-none transition-all min-h-[30px]'
            : 'w-full bg-slate-50 border border-slate-200 rounded-lg px-3 py-2 text-[12px] text-slate-800 placeholder:text-slate-400 focus:border-primary focus:bg-white focus:ring-0 outline-none transition-all min-h-[34px]')
        : 'form-control';
    $ufReadonlyClass = $ufCompactMode
        ? 'w-full bg-slate-100 border border-slate-200 rounded-lg px-3 py-2 text-[12px] text-slate-600 min-h-[34px]'
        : 'form-control bg-light';
    $ufErrClass = $ufCompactMode ? 'text-[11px] text-red-600 font-medium mt-0.5 block' : 'invalid-feedback';
    $ufPairCol = $ufRegisterMobileRow ? 'col-6' : 'col-md-6';
    $ufIdentCol = $ufRegisterMobileRow ? 'col-6' : 'col-12 col-sm-6 col-md-3';
    $ufHalfCol = $ufRegisterMobileRow ? 'col-6' : 'col-md-6';
    $ufKepegCol = $myProfileMode ? 'col-12 col-sm-6 col-md-3' : 'col-md-6 col-xl-3';
    $ufEmptyDaerah = $ufRegisterMobileRow ? '— Pilih —' : '— Tidak dipilih —';
    $ufEmptyWilayah = $ufRegisterMobileRow ? '— Pilih —' : '— Pilih Polda dulu —';
@endphp

<x-admin.user-form-section icon="fa-map-marker-alt" title="Provinsi dan Kabupaten/Kota" variant="info">
<div class="row g-1 uf-form-row">
    <div class="{{ $ufPairCol }} min-w-0">
        <label class="{{ $ufLabelClass }}" for="userDaerah">Provinsi/Polda</label>
        @if($ufCompactMode)
            <x-sbo-compact-select name="daerah" id="userDaerah" :invalid="$errors->has('daerah')" :compact="$ufRegisterMobileRow" data-placeholder="{{ $ufEmptyDaerah }}">
                <option value="">{{ $ufEmptyDaerah }}</option>
                @foreach($daerahList as $d)
                    <option value="{{ $d->namadaerah }}" {{ old('daerah', optional($user)->daerah ?? '') === $d->namadaerah ? 'selected' : '' }}>{{ strtoupper($d->namadaerah) }}</option>
                @endforeach
            </x-sbo-compact-select>
        @else
            <select name="daerah" id="userDaerah" class="form-select select2 @error('daerah') is-invalid @enderror" data-placeholder="— Tidak dipilih —">
                <option value="">— Tidak dipilih —</option>
                @foreach($daerahList as $d)
                    <option value="{{ $d->namadaerah }}" {{ old('daerah', optional($user)->daerah ?? '') === $d->namadaerah ? 'selected' : '' }}>{{ strtoupper($d->namadaerah) }}</option>
                @endforeach
            </select>
        @endif
        @error('daerah')<div class="{{ $ufErrClass }}">{{ $message }}</div>@enderror
    </div>
    <div class="{{ $ufPairCol }} min-w-0">
        <label class="{{ $ufLabelClass }}" for="userWilayah">
            Kab/Polres
            @unless($ufRegisterMobileRow)
                <span class="text-muted fw-normal">(Polrestabes)</span>
            @endunless
        </label>
        @if($ufCompactMode)
            <x-sbo-compact-select name="wilayah" id="userWilayah" :invalid="$errors->has('wilayah')" :compact="$ufRegisterMobileRow" data-placeholder="{{ $ufEmptyWilayah }}">
                <option value="">{{ $ufEmptyWilayah }}</option>
            </x-sbo-compact-select>
        @else
            <select name="wilayah" id="userWilayah" class="form-select select2 @error('wilayah') is-invalid @enderror" data-placeholder="— Pilih Provinsi/Polda dulu —">
                <option value="">— Pilih Provinsi/Polda dulu —</option>
            </select>
        @endif
        @error('wilayah')<div class="{{ $ufErrClass }}">{{ $message }}</div>@enderror
    </div>
</div>
</x-admin.user-form-section>

<x-admin.user-form-section icon="fa-user" title="Identitas" variant="primary">
<div class="row g-1 uf-form-row">
    <div class="{{ $ufIdentCol }} min-w-0">
        <label class="{{ $ufLabelClass }}">Nama <span class="text-red-500">*</span></label>
        <input type="text" name="nama" class="{{ $ufCompactMode ? $ufInputClass : 'form-control' }} @error('nama') is-invalid @enderror" value="{{ old('nama', optional($user)->nama ?? '') }}" required placeholder="{{ $ufRegisterMobileRow ? 'Nama' : 'Nama lengkap' }}">
        @error('nama')<div class="{{ $ufErrClass }}">{{ $message }}</div>@enderror
    </div>
    <div class="{{ $ufIdentCol }} min-w-0">
        <label class="{{ $ufLabelClass }}">
            Username
            @unless($myProfileMode)
                <span class="text-red-500">*</span>
            @endunless
        </label>
        @if($myProfileMode)
            <input type="text" class="{{ $ufReadonlyClass }}" value="{{ old('username', $user->username) }}" disabled readonly autocomplete="username" aria-describedby="profile_username_hint">
        @else
            <input type="text" name="username" class="{{ $ufCompactMode ? $ufInputClass : 'form-control' }} @error('username') is-invalid @enderror" value="{{ old('username', optional($user)->username ?? '') }}" required placeholder="{{ $ufRegisterMobileRow ? 'Username' : 'Username login' }}" autocomplete="username">
            @error('username')<div class="{{ $ufErrClass }}">{{ $message }}</div>@enderror
        @endif
    </div>
    @if(!$registerMode)
    <div class="col-12 col-sm-6 col-md-3">
        <label class="form-label">NRP / NIP / NIK</label>
        <input type="text" name="nrp_nip_nik" class="form-control @error('nrp_nip_nik') is-invalid @enderror" value="{{ old('nrp_nip_nik', optional($user)->nrp_nip_nik ?? '') }}" maxlength="191" autocomplete="off" placeholder="Identitas pegawai">
        @error('nrp_nip_nik')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-12 col-sm-6 col-md-3">
        <label class="{{ $ufLabelClass }}">Kategori user</label>
        @if($myProfileMode)
            <x-sbo-compact-select name="kategori_user" :invalid="$errors->has('kategori_user')">
                <option value="">— Pilih —</option>
                <option value="organik" {{ old('kategori_user', optional($user)->kategori_user ?? '') === 'organik' ? 'selected' : '' }}>Organik</option>
                <option value="non_organik" {{ old('kategori_user', optional($user)->kategori_user ?? '') === 'non_organik' ? 'selected' : '' }}>Non-organik</option>
            </x-sbo-compact-select>
        @else
            <select name="kategori_user" class="form-select @error('kategori_user') is-invalid @enderror">
                <option value="">— Pilih kategori —</option>
                <option value="organik" {{ old('kategori_user', optional($user)->kategori_user ?? '') === 'organik' ? 'selected' : '' }}>Organik</option>
                <option value="non_organik" {{ old('kategori_user', optional($user)->kategori_user ?? '') === 'non_organik' ? 'selected' : '' }}>Non-organik</option>
            </select>
        @endif
        @error('kategori_user')<div class="{{ $myProfileMode ? $ufErrClass : 'invalid-feedback' }}">{{ $message }}</div>@enderror
    </div>
    @endif
</div>
</x-admin.user-form-section>

<x-admin.user-form-section icon="fa-key" title="Akun dan akses" variant="warning">
<div class="row g-1 uf-form-row">
    @if($myProfileMode)
        <div class="col-12 col-sm-6 col-md-3">
            <label class="{{ $ufLabelClass }}">Role</label>
            @if($user->role)
                <div class="{{ $ufReadonlyClass }}">{{ $user->role->role_name ?? '—' }}</div>
            @else
                <div class="{{ $ufReadonlyClass }} text-muted">—</div>
            @endif
        </div>
        <div class="col-12 col-sm-6 col-md-3">
            <label class="{{ $ufLabelClass }}" for="prof_password_current">Password saat ini</label>
            <input type="password" name="password_current" id="prof_password_current" class="{{ $ufInputClass }} @error('password_current') is-invalid @enderror" autocomplete="current-password" placeholder="Jika ganti password">
            @error('password_current')<div class="{{ $ufErrClass }}">{{ $message }}</div>@enderror
        </div>
        <div class="col-12 col-sm-6 col-md-3">
            <label class="{{ $ufLabelClass }}">Password baru <span class="text-slate-400 font-normal normal-case">(opsional)</span></label>
            <input type="password" name="password" class="{{ $ufInputClass }} @error('password') is-invalid @enderror" autocomplete="new-password" placeholder="Kosongkan jika tidak diubah">
            @error('password')<div class="{{ $ufErrClass }}">{{ $message }}</div>@enderror
        </div>
        <div class="col-12 col-sm-6 col-md-3">
            <label class="{{ $ufLabelClass }}">Konfirmasi password</label>
            <input type="password" name="password_confirmation" class="{{ $ufInputClass }}" autocomplete="new-password" placeholder="Ulangi password baru">
        </div>
    @elseif(!$isEdit)
        @if($registerMode)
            <div class="{{ $ufHalfCol }} min-w-0">
                <label class="{{ $ufLabelClass }}">Password <span class="text-red-500">*</span></label>
                <input type="password" name="password" class="{{ $ufInputClass }} @error('password') is-invalid @enderror" required autocomplete="new-password" placeholder="{{ $ufRegisterMobileRow ? 'Password' : 'Min. 6 karakter' }}">
                @error('password')<div class="{{ $ufErrClass }}">{{ $message }}</div>@enderror
            </div>
            <div class="{{ $ufHalfCol }} min-w-0">
                <label class="{{ $ufLabelClass }}">Konfirmasi <span class="text-red-500">*</span></label>
                <input type="password" name="password_confirmation" class="{{ $ufInputClass }}" required autocomplete="new-password" placeholder="{{ $ufRegisterMobileRow ? 'Ulangi' : 'Ulangi password' }}">
            </div>
        @else
            <div class="col-md-6">
                <label class="form-label">Role</label>
                <select name="user_role_id" class="form-select @error('user_role_id') is-invalid @enderror">
                    <option value="">— Pilih role —</option>
                    @foreach($roles as $r)
                        @php
                            $roleHint = trim(($roleDescriptions[$r->role_id] ?? '') . ' (ID ' . $r->role_id . ')');
                        @endphp
                        <option value="{{ $r->role_id }}" title="{{ $roleHint }}" {{ old('user_role_id', optional($user)->user_role_id ?? '') == $r->role_id ? 'selected' : '' }}>
                            {{ $r->role_name }}
                        </option>
                    @endforeach
                </select>
                @error('user_role_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                <p class="form-hint mb-0">Arahkan kursor ke opsi role untuk melihat deskripsi akses.</p>
            </div>
            <div class="col-md-6 d-none d-md-block" aria-hidden="true"></div>
            <div class="col-md-6">
                <label class="form-label">Password <span class="text-danger">*</span></label>
                <input type="password" name="password" class="form-control @error('password') is-invalid @enderror" required autocomplete="new-password">
                @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-6">
                <label class="form-label">Konfirmasi password <span class="text-danger">*</span></label>
                <input type="password" name="password_confirmation" class="form-control" required autocomplete="new-password">
            </div>
        @endif
    @else
        <div class="col-md-6">
            <label class="form-label">Role</label>
            <select name="user_role_id" class="form-select @error('user_role_id') is-invalid @enderror">
                <option value="">— Pilih role —</option>
                @foreach($roles as $r)
                    @php
                        $roleHint = trim(($roleDescriptions[$r->role_id] ?? '') . ' (ID ' . $r->role_id . ')');
                    @endphp
                    <option value="{{ $r->role_id }}" title="{{ $roleHint }}" {{ old('user_role_id', optional($user)->user_role_id ?? '') == $r->role_id ? 'selected' : '' }}>
                        {{ $r->role_name }}
                    </option>
                @endforeach
            </select>
            @error('user_role_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
            <p class="form-hint mb-0">Arahkan kursor ke opsi role untuk melihat deskripsi akses.</p>
        </div>
        <div class="col-md-6">
            <label class="form-label">Status akun <span class="text-danger">*</span></label>
            <select name="account_status" class="form-select @error('account_status') is-invalid @enderror" required>
                @foreach(['active','pending','blocked'] as $s)
                    <option value="{{ $s }}" {{ old('account_status', optional($user)->account_status ?? '') === $s ? 'selected' : '' }}>{{ ucfirst($s) }}</option>
                @endforeach
            </select>
            @error('account_status')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
        <div class="col-md-6">
            <label class="form-label">Password baru <span class="text-muted fw-normal">(opsional)</span></label>
            <input type="password" name="password" class="form-control @error('password') is-invalid @enderror" autocomplete="new-password" placeholder="Kosongkan jika tidak diubah">
            @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
        <div class="col-md-6">
            <label class="form-label">Konfirmasi password baru</label>
            <input type="password" name="password_confirmation" class="form-control" autocomplete="new-password">
        </div>
    @endif
</div>
</x-admin.user-form-section>

@if(!$registerMode)
<x-admin.user-form-section icon="fa-landmark" title="{{ $myProfileMode ? 'Kepegawaian' : 'Kepegawaian dan organisasi' }}" variant="success">
<div class="row g-1">
    <div class="{{ $ufKepegCol }}">
        <label class="{{ $ufLabelClass }}" for="user_induk_pemerintahan">Induk organisasi</label>
        @if($indukPemerintahanUseSelect)
            @if($myProfileMode)
                <x-sbo-compact-select name="induk_pemerintahan" id="user_induk_pemerintahan" :invalid="$errors->has('induk_pemerintahan')">
                    <option value="">— Pilih —</option>
                    @foreach($orgsIndukForKepegawaian as $o)
                        <option value="{{ $o->nameorg }}" @selected(old('induk_pemerintahan', optional($user)->induk_pemerintahan ?? '') === $o->nameorg)>{{ $o->nameorg }}</option>
                    @endforeach
                </x-sbo-compact-select>
            @else
                <select name="induk_pemerintahan" id="user_induk_pemerintahan" class="form-select @error('induk_pemerintahan') is-invalid @enderror">
                    <option value="">— Pilih induk organisasi —</option>
                    @foreach($orgsIndukForKepegawaian as $o)
                        <option value="{{ $o->nameorg }}" @selected(old('induk_pemerintahan', optional($user)->induk_pemerintahan ?? '') === $o->nameorg)>{{ $o->nameorg }}</option>
                    @endforeach
                </select>
            @endif
        @else
            <input type="text" name="induk_pemerintahan" id="user_induk_pemerintahan" class="{{ $myProfileMode ? $ufInputClass : 'form-control' }} @error('induk_pemerintahan') is-invalid @enderror" value="{{ old('induk_pemerintahan', optional($user)->induk_pemerintahan ?? '') }}" maxlength="255" placeholder="Contoh: Kementerian / instansi induk">
        @endif
        @error('induk_pemerintahan')<div class="{{ $ufErrClass }}">{{ $message }}</div>@enderror
    </div>
    <div class="{{ $ufKepegCol }}">
        <label class="{{ $ufLabelClass }}" for="user_pemerintahan_daerah">Organisasi pusat</label>
        @if($pemerintahanDaerahUseSelect)
            @if($myProfileMode)
                <x-sbo-compact-select name="pemerintahan_daerah" id="user_pemerintahan_daerah" :invalid="$errors->has('pemerintahan_daerah')">
                    <option value="">— Pilih —</option>
                    @foreach($pemerintahanDaerahResorts as $pr)
                        <option value="{{ $pr->nama }}" data-induk-org="{{ $pr->nameorg }}" @selected(old('pemerintahan_daerah', optional($user)->pemerintahan_daerah ?? '') === $pr->nama)>{{ $pr->nama }}</option>
                    @endforeach
                </x-sbo-compact-select>
            @else
                <select name="pemerintahan_daerah" id="user_pemerintahan_daerah" class="form-select @error('pemerintahan_daerah') is-invalid @enderror">
                    <option value="">— Pilih organisasi pusat —</option>
                    @foreach($pemerintahanDaerahResorts as $pr)
                        <option value="{{ $pr->nama }}" data-induk-org="{{ $pr->nameorg }}" @selected(old('pemerintahan_daerah', optional($user)->pemerintahan_daerah ?? '') === $pr->nama)>{{ $pr->nama }}</option>
                    @endforeach
                </select>
            @endif
        @else
            <input type="text" name="pemerintahan_daerah" id="user_pemerintahan_daerah" class="{{ $myProfileMode ? $ufInputClass : 'form-control' }} @error('pemerintahan_daerah') is-invalid @enderror" value="{{ old('pemerintahan_daerah', optional($user)->pemerintahan_daerah ?? '') }}" maxlength="255" placeholder="Contoh: Polda / Pemprov">
        @endif
        @error('pemerintahan_daerah')<div class="{{ $ufErrClass }}">{{ $message }}</div>@enderror
    </div>
    @if($kepegawaianKlEmpatTingkat ?? false)
        <div class="{{ $ufKepegCol }}">
            <label class="{{ $ufLabelClass }}" for="user_organisasi_daerah">Organisasi daerah</label>
            @if($organisasiDaerahUseSelect)
                @if($myProfileMode)
                    <x-sbo-compact-select name="organisasi_daerah" id="user_organisasi_daerah" :invalid="$errors->has('organisasi_daerah')">
                        <option value="">— Pilih —</option>
                        @foreach($organisasiDaerahListCol as $od)
                            <option value="{{ $od->nama }}"
                                data-induk-org="{{ $od->induk_pusat ?? '' }}"
                                data-org-pusat="{{ $od->org_pusat ?? '' }}"
                                @selected(old('organisasi_daerah', optional($user)->organisasi_daerah ?? '') === $od->nama)>{{ $od->nama }}</option>
                        @endforeach
                    </x-sbo-compact-select>
                @else
                    <select name="organisasi_daerah" id="user_organisasi_daerah" class="form-select @error('organisasi_daerah') is-invalid @enderror">
                        <option value="">— Pilih organisasi daerah —</option>
                        @foreach($organisasiDaerahListCol as $od)
                            <option value="{{ $od->nama }}"
                                data-induk-org="{{ $od->induk_pusat ?? '' }}"
                                data-org-pusat="{{ $od->org_pusat ?? '' }}"
                                @selected(old('organisasi_daerah', optional($user)->organisasi_daerah ?? '') === $od->nama)>{{ $od->nama }}</option>
                        @endforeach
                    </select>
                @endif
            @else
                <input type="text" name="organisasi_daerah" id="user_organisasi_daerah" class="{{ $myProfileMode ? $ufInputClass : 'form-control' }} @error('organisasi_daerah') is-invalid @enderror" value="{{ old('organisasi_daerah', optional($user)->organisasi_daerah ?? '') }}" maxlength="255">
            @endif
            @error('organisasi_daerah')<div class="{{ $ufErrClass }}">{{ $message }}</div>@enderror
        </div>
    @endif
    <div class="{{ $ufKepegCol }}">
        <label class="{{ $ufLabelClass }}" for="user_unit_kerja">Unit kerja</label>
        @if($unitKerjaUseSelect)
            @if($myProfileMode)
                <x-sbo-compact-select name="unit_kerja" id="user_unit_kerja" :invalid="$errors->has('unit_kerja')">
                    <option value="">— Pilih —</option>
                    @foreach($suborgsUnitKerjaCol as $s)
                        <option value="{{ $s->namesuborg }}"
                            data-induk-org="{{ $kepegawaianKlTigaTingkat ? ($s->induk_pusat ?? '') : $s->nameorg }}"
                            data-org-pusat="{{ $kepegawaianKlEmpatTingkat ? ($s->org_pusat ?? '') : ($kepegawaianKlTigaTingkat ? ($s->nameorg ?? '') : '') }}"
                            data-org-daerah="{{ $kepegawaianKlEmpatTingkat ? ($s->org_daerah ?? '') : ($kepegawaianKlTigaTingkat ? ($s->nameorg ?? '') : '') }}"
                            @selected(old('unit_kerja', optional($user)->unit_kerja ?? '') === $s->namesuborg)>{{ $s->namesuborg }}</option>
                    @endforeach
                </x-sbo-compact-select>
            @else
                <select name="unit_kerja" id="user_unit_kerja" class="form-select @error('unit_kerja') is-invalid @enderror">
                    <option value="">— Pilih unit kerja —</option>
                    @foreach($suborgsUnitKerjaCol as $s)
                        <option value="{{ $s->namesuborg }}"
                            data-induk-org="{{ $kepegawaianKlTigaTingkat ? ($s->induk_pusat ?? '') : $s->nameorg }}"
                            data-org-pusat="{{ $kepegawaianKlEmpatTingkat ? ($s->org_pusat ?? '') : ($kepegawaianKlTigaTingkat ? ($s->nameorg ?? '') : '') }}"
                            data-org-daerah="{{ $kepegawaianKlEmpatTingkat ? ($s->org_daerah ?? '') : ($kepegawaianKlTigaTingkat ? ($s->nameorg ?? '') : '') }}"
                            @selected(old('unit_kerja', optional($user)->unit_kerja ?? '') === $s->namesuborg)>{{ $s->namesuborg }}</option>
                    @endforeach
                </select>
            @endif
        @else
            <input type="text" name="unit_kerja" id="user_unit_kerja" class="{{ $myProfileMode ? $ufInputClass : 'form-control' }} @error('unit_kerja') is-invalid @enderror" value="{{ old('unit_kerja', optional($user)->unit_kerja ?? '') }}" maxlength="255" placeholder="Nama unit kerja / satuan kerja">
        @endif
        @error('unit_kerja')<div class="{{ $ufErrClass }}">{{ $message }}</div>@enderror
    </div>
</div>
</x-admin.user-form-section>
@endif



<x-admin.user-form-section icon="fa-address-card" title="{{ $registerMode ? 'Kontak' : 'Kontak dan foto profil' }}" variant="purple">
<div class="row g-1 uf-form-row">
    <div class="{{ $ufHalfCol }} min-w-0">
        <label class="{{ $ufLabelClass }}">Email <span class="text-red-500">*</span></label>
        @if($isEdit && !$myProfileMode)
            <input type="email" class="{{ $ufReadonlyClass }}" value="{{ $user->email }}" disabled readonly>
        @else
            <input type="email" name="email" class="{{ $ufCompactMode ? $ufInputClass : 'form-control' }} @error('email') is-invalid @enderror" value="{{ old('email', optional($user)->email ?? '') }}" required autocomplete="email" placeholder="{{ $ufRegisterMobileRow ? 'Email' : 'email@contoh.com' }}">
            @error('email')<div class="{{ $ufErrClass }}">{{ $message }}</div>@enderror
        @endif
    </div>
    <div class="{{ $ufHalfCol }} min-w-0">
        <label class="{{ $ufLabelClass }}" for="user_notelp">Telepon</label>
        <input type="text" name="notelp" id="user_notelp" class="{{ $ufCompactMode ? $ufInputClass : 'form-control' }}" value="{{ old('notelp', optional($user)->notelp ?? '') }}" inputmode="tel" autocomplete="tel" placeholder="{{ $ufRegisterMobileRow ? '08xx' : '08xxxxxxxxxx' }}">
    </div>
    @if(!$registerMode)
    <div class="{{ $myProfileMode ? 'col-12' : 'col-md-6' }}">
        <label class="form-label" for="user_fotoprof">Foto profil</label>
        @if($myProfileMode)
            <label for="user_fotoprof"
                class="flex items-center justify-center gap-2 w-full rounded-lg border border-dashed border-slate-200 bg-slate-50/80 hover:border-primary/40 hover:bg-primary/5 px-3 py-3 cursor-pointer transition-all{{ $errors->has('fotoprof') ? ' border-red-300 bg-red-50/50' : '' }}">
                <span class="material-symbols-outlined text-[22px] text-primary/70">add_a_photo</span>
                <span class="text-[12px] font-semibold text-slate-600">Unggah foto profil baru</span>
            </label>
            <input type="file" name="fotoprof" id="user_fotoprof" accept="image/*" class="visually-hidden @error('fotoprof') is-invalid @enderror"
                data-sbo-media-preview
                data-sbo-preview-img="#user_fotoprof_preview"
                data-sbo-preview-wrap="#user_fotoprof_preview_wrap"
                data-sbo-max-mb="2">
            @error('fotoprof')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
            <div class="mt-2 d-none rounded-lg border border-slate-200 bg-slate-50 p-2" id="user_fotoprof_preview_wrap">
                <img id="user_fotoprof_preview" src="" alt="Pratinjau foto profil" class="rounded-md border border-slate-200 max-h-[120px] w-auto d-none">
            </div>
            @if(optional($user)->fotoprof)
                <p class="text-[10px] text-slate-400 mt-1.5 mb-0">Foto saat ini akan diganti jika Anda mengunggah berkas baru.</p>
            @endif
        @else
        <input type="file" name="fotoprof" id="user_fotoprof" accept="image/*" class="form-control @error('fotoprof') is-invalid @enderror"
            data-sbo-media-preview
            data-sbo-preview-img="#user_fotoprof_preview"
            data-sbo-preview-wrap="#user_fotoprof_preview_wrap"
            data-sbo-max-mb="2">
        @error('fotoprof')<div class="invalid-feedback">{{ $message }}</div>@enderror
        <div class="mt-2 d-none" id="user_fotoprof_preview_wrap">
            <span class="small text-muted d-block mb-1">Pratinjau</span>
            <img id="user_fotoprof_preview" src="" alt="Pratinjau foto profil" class="img-thumbnail d-none" style="max-height:160px;width:auto;">
        </div>
        @if(($isEdit || $myProfileMode) && optional($user)->fotoprof)
            <small class="text-muted d-block mt-1">Berkas saat ini: <code>{{ $user->fotoprof }}</code></small>
        @endif
        @endif
    </div>
    @endif
</div>
</x-admin.user-form-section>

@push('scripts')
@if(!$ufCompactMode)
<script>
    if (typeof jQuery !== 'undefined') {
        $(document).ready(function() {
            if ($.fn.select2) {
                $('#userDaerah, #userWilayah').select2({
                    theme: 'bootstrap-5',
                    width: '100%',
                    placeholder: function() {
                        return $(this).data('placeholder') || '— Pilih —';
                    }
                });
            }
        });
    }
</script>
@endif
@include('admin.users._form_dynamic_scripts', [
    'filterKepegawaianIndukUnit' => ($indukPemerintahanUseSelect && $unitKerjaUseSelect && ! $kepegawaianKlTigaTingkat),
    'registerMode' => $registerMode,
    'wilayahPakaiMasterKlDaerah' => $wilayahPakaiMasterKlDaerah,
    'userFormPakaiMasterKl' => $userFormPakaiMasterKl,
    'filterKepegawaianIndukPemerintahanDaerah' => $filterKepegawaianIndukPemerintahanDaerah,
    'filterKepegawaianOrganisasiDaerahUnit' => $filterKepegawaianOrganisasiDaerahUnit,
    'kepegawaianKlTigaTingkat' => $kepegawaianKlTigaTingkat,
    'kepegawaianKlEmpatTingkat' => $kepegawaianKlEmpatTingkat ?? false,
    'kepegawaianHierarchyJson' => $kepegawaianHierarchyJson ?? [],
    'selectedPemda' => old('pemerintahan_daerah', optional($user)->pemerintahan_daerah ?? ''),
    'selectedOrgDaerah' => old('organisasi_daerah', optional($user)->organisasi_daerah ?? ''),
    'selectedUnit' => old('unit_kerja', optional($user)->unit_kerja ?? ''),
])
@endpush
