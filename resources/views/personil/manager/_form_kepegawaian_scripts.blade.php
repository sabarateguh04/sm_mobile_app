@php
    $valInduk = old('induk_pemerintahan', ($data ?? null)->induk_pemerintahan ?? ($defaultIndukPemerintahan ?? ''));
    $valPemda = old('pemerintahan_daerah', ($data ?? null)->pemerintahan_daerah ?? ($defaultPemerintahanDaerah ?? ''));
    $valOrgDaerah = old('organisasi_daerah', ($data ?? null)->organisasi_daerah ?? ($defaultOrganisasiDaerah ?? ''));
    $valUnit = old('unit_kerja', ($data ?? null)->unit_kerja ?? ($defaultUnitKerja ?? ''));
@endphp
@include('shared._kepegawaian_kl_cascade_scripts', [
    'kepegawaianKlTigaTingkat' => $kepegawaianKlTigaTingkat ?? false,
    'kepegawaianKlEmpatTingkat' => $kepegawaianKlEmpatTingkat ?? false,
    'kepegawaianHierarchyJson' => $kepegawaianHierarchyJson ?? [],
    'indukFieldId' => 'pm_induk_pemerintahan',
    'pemdaFieldId' => 'pm_pemerintahan_daerah',
    'orgDaerahFieldId' => 'pm_organisasi_daerah',
    'unitFieldId' => 'pm_unit_kerja',
    'selectedPemda' => $valPemda,
    'selectedOrgDaerah' => $valOrgDaerah,
    'selectedUnit' => $valUnit,
])
