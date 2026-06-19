@php
    $isEdit = $isEdit ?? false;
    $row = $data ?? null;
    $statusVal = strtolower(old('account_status', $row->account_status ?? 'active'));
@endphp
<x-admin.user-form-section icon="fa-map-marker-alt" title="Wilayah penugasan" variant="info">
<div class="row form-row-compact">
    @if($canPickTerritory ?? false)
        <div class="col-12 col-sm-6">
            <label class="form-label" for="personilDaerah">Provinsi/Polda <span class="text-danger">*</span></label>
            @if(($daerahList ?? collect())->isNotEmpty())
                <select name="daerah" id="personilDaerah" class="form-select select2 @error('daerah') is-invalid @enderror" required data-placeholder="— Pilih Provinsi/Polda —">
                    <option value="">— Pilih Provinsi/Polda —</option>
                    @foreach($daerahList as $d)
                        <option value="{{ $d->namadaerah }}" {{ old('daerah', $row->daerah ?? $defaultDaerah ?? '') === $d->namadaerah ? 'selected' : '' }}>{{ strtoupper($d->namadaerah) }}</option>
                    @endforeach
                </select>
            @else
                <input type="text" name="daerah" id="personilDaerah" class="form-control @error('daerah') is-invalid @enderror" value="{{ old('daerah', $row->daerah ?? $defaultDaerah ?? '') }}" required>
            @endif
            @error('daerah')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
        <div class="col-12 col-sm-6">
            <label class="form-label" for="personilWilayah">Kab/Polres <span class="text-muted fw-normal">(Polrestabes)</span></label>
            @if(($daerahList ?? collect())->isNotEmpty())
                <select name="wilayah" id="personilWilayah" class="form-select select2 @error('wilayah') is-invalid @enderror" data-placeholder="— Pilih Provinsi/Polda dulu —">
                    <option value="">— Pilih Provinsi/Polda dulu —</option>
                </select>
            @else
                <input type="text" name="wilayah" id="personilWilayah" class="form-control @error('wilayah') is-invalid @enderror" value="{{ old('wilayah', $row->wilayah ?? $defaultWilayah ?? '') }}">
            @endif
            @error('wilayah')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
        @if(($daerahList ?? collect())->isNotEmpty())
            @push('scripts')
<script>
    if (typeof jQuery !== 'undefined') {
        $(document).ready(function() {
            if ($.fn.select2) {
                $('#personilDaerah, #personilWilayah').select2({
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
                @include('personil.manager._form_wilayah_scripts')
            @endpush
        @endif
    @else
        <div class="col-12 col-sm-6">
            <label class="form-label">Provinsi/Polda</label>
            <input type="text" class="form-control bg-light" value="{{ old('daerah', $row->daerah ?? $defaultDaerah ?? '') }}" readonly>
            <input type="hidden" name="daerah" value="{{ old('daerah', $row->daerah ?? $defaultDaerah ?? '') }}">
        </div>
        <div class="col-12 col-sm-6">
            <label class="form-label">Kab/Polres <span class="text-muted fw-normal">(Polrestabes)</span></label>
            <input type="text" class="form-control bg-light" value="{{ old('wilayah', $row->wilayah ?? $defaultWilayah ?? '') }}" readonly>
            <input type="hidden" name="wilayah" value="{{ old('wilayah', $row->wilayah ?? $defaultWilayah ?? '') }}">
        </div>
        <div class="col-12">
            <p class="form-hint m-0 flex items-center gap-1">
                <span class="material-symbols-outlined text-[14px] text-slate-400">info</span>
                Operator baru otomatis masuk wilayah kerja Anda.
            </p>
        </div>
    @endif
</div>
</x-admin.user-form-section>

<x-admin.user-form-section icon="fa-user" title="Identitas" variant="primary">
<div class="row form-row-compact">
    <div class="col-12 col-sm-6">
        <label class="form-label">Nama <span class="text-danger">*</span></label>
        <input type="text" name="nama" class="form-control @error('nama') is-invalid @enderror" value="{{ old('nama', $row->nama ?? '') }}" required>
        @error('nama')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-12 col-sm-6">
        <label class="form-label">Username <span class="text-danger">*</span></label>
        <input type="text" name="username" class="form-control @error('username') is-invalid @enderror" value="{{ old('username', $row->username ?? '') }}" required autocomplete="off">
        @error('username')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
</div>
</x-admin.user-form-section>

@include('personil.manager._form_kepegawaian')

<x-admin.user-form-section icon="fa-key" title="Akun dan akses" variant="warning">
<div class="row form-row-compact">
    <div class="col-12 col-sm-6 col-lg-4">
        <label class="form-label">Status Akun <span class="text-danger">*</span></label>
        <select name="account_status" class="form-select @error('account_status') is-invalid @enderror" required>
            @foreach(['active' => 'Active', 'pending' => 'Pending', 'blocked' => 'Blocked'] as $val => $label)
                <option value="{{ $val }}" {{ $statusVal === $val ? 'selected' : '' }}>{{ $label }}</option>
            @endforeach
        </select>
        @error('account_status')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-12 col-sm-6 col-lg-4">
        <label class="form-label">Password @unless($isEdit)<span class="text-danger">*</span>@else<span class="text-muted fw-normal">(opsional)</span>@endunless</label>
        <input type="password" name="password" class="form-control @error('password') is-invalid @enderror" {{ $isEdit ? '' : 'required' }} autocomplete="new-password">
        @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-12 col-sm-6 col-lg-4">
        <label class="form-label">Konfirmasi Password @unless($isEdit)<span class="text-danger">*</span>@endunless</label>
        <input type="password" name="password_confirmation" class="form-control" {{ $isEdit ? '' : 'required' }} autocomplete="new-password">
    </div>
</div>
</x-admin.user-form-section>

<x-admin.user-form-section icon="fa-address-card" title="Kontak dan foto profil" variant="purple">
<div class="row form-row-compact">
    <div class="col-12 col-sm-6 col-lg-4">
        <label class="form-label">Email <span class="text-danger">*</span></label>
        <input type="email" name="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email', $row->email ?? '') }}" required>
        @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-12 col-sm-6 col-lg-4">
        <label class="form-label">No. Telp</label>
        <input type="text" name="notelp" class="form-control @error('notelp') is-invalid @enderror" value="{{ old('notelp', $row->notelp ?? '') }}">
        @error('notelp')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-12 col-sm-6 col-lg-4">
        <label class="form-label">Foto Profil</label>
        <input type="file" name="fotoprof" class="form-control @error('fotoprof') is-invalid @enderror" accept="image/*">
        @error('fotoprof')<div class="invalid-feedback">{{ $message }}</div>@enderror
        @if($isEdit && !empty($row->fotoprof))
            @php $foto = $row->fotoprof; $fotoUrl = str_starts_with($foto, 'http') ? $foto : url($foto); @endphp
            <img src="{{ $fotoUrl }}" alt="Foto saat ini" class="img-thumbnail mt-1" style="max-height:56px">
        @endif
    </div>
</div>
</x-admin.user-form-section>
