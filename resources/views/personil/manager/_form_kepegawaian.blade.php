@php
    $row = $data ?? null;
    $orgsIndukForKepegawaian = $orgsInduk ?? collect();
    $suborgsUnitKerjaCol = $suborgsUnitKerja ?? collect();
    $organisasiDaerahListCol = $organisasiDaerahList ?? collect();
    $indukPemerintahanUseSelect = $indukPemerintahanUseSelect ?? false;
    $unitKerjaUseSelect = $unitKerjaUseSelect ?? false;
    $pemerintahanDaerahUseSelect = $pemerintahanDaerahUseSelect ?? false;
    $organisasiDaerahUseSelect = $organisasiDaerahUseSelect ?? false;
    $pemerintahanDaerahResorts = $pemerintahanDaerahResorts ?? collect();
    $filterKepegawaianIndukPemerintahanDaerah = $filterKepegawaianIndukPemerintahanDaerah ?? false;
    $filterKepegawaianOrganisasiDaerahUnit = $filterKepegawaianOrganisasiDaerahUnit ?? false;
    $filterKepegawaianIndukUnit = ($indukPemerintahanUseSelect && $unitKerjaUseSelect && ! ($kepegawaianKlTigaTingkat ?? false));
    $kepegawaianKlTigaTingkat = $kepegawaianKlTigaTingkat ?? false;
    $kepegawaianKlEmpatTingkat = $kepegawaianKlEmpatTingkat ?? false;
    $canPickKepegawaian = $canPickKepegawaian ?? true;
    $locks = $kepegawaianFieldLocks ?? [
        'induk_pemerintahan' => false,
        'pemerintahan_daerah' => false,
        'organisasi_daerah' => false,
        'unit_kerja' => false,
    ];
    $valInduk = old('induk_pemerintahan', $row->induk_pemerintahan ?? $defaultIndukPemerintahan ?? '');
    $valPemda = old('pemerintahan_daerah', $row->pemerintahan_daerah ?? $defaultPemerintahanDaerah ?? '');
    $valOrgDaerah = old('organisasi_daerah', $row->organisasi_daerah ?? $defaultOrganisasiDaerah ?? '');
    $valUnit = old('unit_kerja', $row->unit_kerja ?? $defaultUnitKerja ?? '');

    if ($kepegawaianKlEmpatTingkat ?? false) {
        $kepegawaianSubtitleHtml = 'Alur hierarki 4 tingkat: <strong>Induk organisasi</strong> → <strong>Organisasi pusat</strong> → <strong>Organisasi daerah</strong> → <strong>Unit kerja</strong>';
    } elseif ($kepegawaianKlTigaTingkat ?? false) {
        $kepegawaianSubtitleHtml = 'Alur hierarki 3 tingkat: <strong>Induk organisasi</strong> → <strong>Organisasi pusat</strong> → <strong>Unit kerja</strong>';
    } else {
        $kepegawaianSubtitleHtml = 'Induk organisasi, organisasi pusat, organisasi daerah, dan unit kerja pegawai';
    }
@endphp

<x-admin.user-form-section
    icon="fa-landmark"
    title="Kepegawaian dan organisasi"
    variant="success"
    :subtitle-html="$kepegawaianSubtitleHtml"
>
<div class="row form-row-compact">
@if($canPickKepegawaian)
    <div class="col-6 col-lg-3">
        <label class="form-label" for="pm_induk_pemerintahan">
            @if(($kepegawaianKlTigaTingkat ?? false) || ($kepegawaianKlEmpatTingkat ?? false))<span class="pm-step-badge pm-step-badge--1">1</span>@endif
            Induk organisasi
        </label>
        @if($locks['induk_pemerintahan'] ?? false)
            <input type="text" class="form-control bg-light" value="{{ $valInduk }}" readonly>
            <input type="hidden" name="induk_pemerintahan" id="pm_induk_pemerintahan" value="{{ $valInduk }}">
        @elseif($indukPemerintahanUseSelect)
            <select name="induk_pemerintahan" id="pm_induk_pemerintahan" class="form-select @error('induk_pemerintahan') is-invalid @enderror">
                <option value="">— Pilih induk organisasi —</option>
                @foreach($orgsIndukForKepegawaian as $o)
                    <option value="{{ $o->nameorg }}" @selected($valInduk === $o->nameorg)>{{ $o->nameorg }}</option>
                @endforeach
            </select>
        @else
            <input type="text" name="induk_pemerintahan" id="pm_induk_pemerintahan" class="form-control @error('induk_pemerintahan') is-invalid @enderror" value="{{ $valInduk }}" maxlength="255">
        @endif
        @error('induk_pemerintahan')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-6 col-lg-3">
        <label class="form-label" for="pm_pemerintahan_daerah">
            @if(($kepegawaianKlTigaTingkat ?? false) || ($kepegawaianKlEmpatTingkat ?? false))<span class="pm-step-badge pm-step-badge--2">2</span>@endif
            Organisasi pusat
        </label>
        @if($locks['pemerintahan_daerah'] ?? false)
            <input type="text" class="form-control bg-light" value="{{ $valPemda }}" readonly>
            <input type="hidden" name="pemerintahan_daerah" id="pm_pemerintahan_daerah" value="{{ $valPemda }}">
        @elseif($pemerintahanDaerahUseSelect)
            <select name="pemerintahan_daerah" id="pm_pemerintahan_daerah" class="form-select @error('pemerintahan_daerah') is-invalid @enderror">
                <option value="">— Pilih organisasi pusat —</option>
                @foreach($pemerintahanDaerahResorts as $pr)
                    <option value="{{ $pr->nama }}" data-induk-org="{{ $pr->nameorg }}" @selected($valPemda === $pr->nama)>{{ $pr->nama }}</option>
                @endforeach
            </select>
        @else
            <input type="text" name="pemerintahan_daerah" id="pm_pemerintahan_daerah" class="form-control @error('pemerintahan_daerah') is-invalid @enderror" value="{{ $valPemda }}" maxlength="255">
        @endif
        @error('pemerintahan_daerah')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    @if($kepegawaianKlEmpatTingkat ?? false)
        <div class="col-6 col-lg-3">
            <label class="form-label" for="pm_organisasi_daerah">
                <span class="pm-step-badge pm-step-badge--3">3</span>
                Organisasi daerah
            </label>
            @if($locks['organisasi_daerah'] ?? false)
                <input type="text" class="form-control bg-light" value="{{ $valOrgDaerah }}" readonly>
                <input type="hidden" name="organisasi_daerah" id="pm_organisasi_daerah" value="{{ $valOrgDaerah }}">
            @elseif($organisasiDaerahUseSelect)
                <select name="organisasi_daerah" id="pm_organisasi_daerah" class="form-select @error('organisasi_daerah') is-invalid @enderror">
                    <option value="">— Pilih organisasi daerah —</option>
                    @foreach($organisasiDaerahListCol as $od)
                        <option value="{{ $od->nama }}"
                            data-induk-org="{{ $od->induk_pusat ?? '' }}"
                            data-org-pusat="{{ $od->org_pusat ?? '' }}"
                            @selected($valOrgDaerah === $od->nama)>{{ $od->nama }}</option>
                    @endforeach
                </select>
            @else
                <input type="text" name="organisasi_daerah" id="pm_organisasi_daerah" class="form-control @error('organisasi_daerah') is-invalid @enderror" value="{{ $valOrgDaerah }}" maxlength="255">
            @endif
            @error('organisasi_daerah')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
    @endif
    <div class="col-6 col-lg-3">
        <label class="form-label" for="pm_unit_kerja">
            @if($kepegawaianKlEmpatTingkat ?? false)
                <span class="pm-step-badge pm-step-badge--4">4</span>
            @elseif($kepegawaianKlTigaTingkat ?? false)
                <span class="pm-step-badge pm-step-badge--4">3</span>
            @endif
            Unit kerja
        </label>
        @if($locks['unit_kerja'] ?? false)
            <input type="text" class="form-control bg-light" value="{{ $valUnit }}" readonly>
            <input type="hidden" name="unit_kerja" id="pm_unit_kerja" value="{{ $valUnit }}">
        @elseif($unitKerjaUseSelect)
            <select name="unit_kerja" id="pm_unit_kerja" class="form-select @error('unit_kerja') is-invalid @enderror">
                <option value="">— Pilih unit kerja —</option>
                @foreach($suborgsUnitKerjaCol as $s)
                    <option value="{{ $s->namesuborg }}"
                        data-induk-org="{{ $kepegawaianKlTigaTingkat ? ($s->induk_pusat ?? '') : $s->nameorg }}"
                        data-org-pusat="{{ $kepegawaianKlEmpatTingkat ? ($s->org_pusat ?? '') : ($kepegawaianKlTigaTingkat ? ($s->nameorg ?? '') : '') }}"
                        data-org-daerah="{{ $kepegawaianKlEmpatTingkat ? ($s->org_daerah ?? '') : ($kepegawaianKlTigaTingkat ? ($s->nameorg ?? '') : '') }}"
                        @selected($valUnit === $s->namesuborg)>{{ $s->namesuborg }}</option>
                @endforeach
            </select>
        @else
            <input type="text" name="unit_kerja" id="pm_unit_kerja" class="form-control @error('unit_kerja') is-invalid @enderror" value="{{ $valUnit }}" maxlength="255" placeholder="Nama unit kerja / satuan kerja">
        @endif
        @error('unit_kerja')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    @if($isCreatePersonilForm ?? false)
        <div class="col-12">
            <p class="form-hint m-0 flex items-center gap-1">
                <span class="material-symbols-outlined text-[14px] text-slate-400">info</span>
                Pilih bertahap dari induk organisasi hingga unit kerja.
            </p>
        </div>
    @elseif(($locks['induk_pemerintahan'] ?? false) || ($locks['pemerintahan_daerah'] ?? false) || ($locks['organisasi_daerah'] ?? false))
        <div class="col-12">
            <p class="form-hint m-0 flex items-center gap-1">
                <span class="material-symbols-outlined text-[14px] text-slate-400">info</span>
                Struktur kepegawaian mengikuti profil kerja Anda.
            </p>
        </div>
    @endif
@else
    <div class="col-6 col-lg-3">
        <label class="form-label">Induk organisasi</label>
        <input type="text" class="form-control bg-light" value="{{ $valInduk }}" readonly>
        <input type="hidden" name="induk_pemerintahan" value="{{ $valInduk }}">
    </div>
    <div class="col-6 col-lg-3">
        <label class="form-label">Organisasi pusat</label>
        <input type="text" class="form-control bg-light" value="{{ $valPemda }}" readonly>
        <input type="hidden" name="pemerintahan_daerah" value="{{ $valPemda }}">
    </div>
    @if($kepegawaianKlEmpatTingkat ?? false)
        <div class="col-6 col-lg-3">
            <label class="form-label">Organisasi daerah</label>
            <input type="text" class="form-control bg-light" value="{{ $valOrgDaerah }}" readonly>
            <input type="hidden" name="organisasi_daerah" value="{{ $valOrgDaerah }}">
        </div>
    @endif
    <div class="col-6 col-lg-3">
        <label class="form-label">Unit kerja</label>
        <input type="text" class="form-control bg-light" value="{{ $valUnit }}" readonly>
        <input type="hidden" name="unit_kerja" value="{{ $valUnit }}">
    </div>
    <div class="col-12">
        <p class="form-hint m-0 flex items-center gap-1">
            <span class="material-symbols-outlined text-[14px] text-slate-400">info</span>
            Operator baru otomatis masuk struktur kepegawaian kerja Anda.
        </p>
    </div>
@endif
</div>
</x-admin.user-form-section>

@push('scripts')
@include('personil.manager._form_kepegawaian_scripts')
@endpush
