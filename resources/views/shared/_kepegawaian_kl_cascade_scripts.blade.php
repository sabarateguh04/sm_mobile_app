@php
    $kepegawaianKlTigaTingkat = $kepegawaianKlTigaTingkat ?? false;
    $kepegawaianKlEmpatTingkat = $kepegawaianKlEmpatTingkat ?? false;
    $kepegawaianHierarchyJson = $kepegawaianHierarchyJson ?? [];
    $indukFieldId = $indukFieldId ?? 'pm_induk_pemerintahan';
    $pemdaFieldId = $pemdaFieldId ?? 'pm_pemerintahan_daerah';
    $orgDaerahFieldId = $orgDaerahFieldId ?? 'pm_organisasi_daerah';
    $unitFieldId = $unitFieldId ?? 'pm_unit_kerja';
    $selectedPemda = $selectedPemda ?? '';
    $selectedOrgDaerah = $selectedOrgDaerah ?? '';
    $selectedUnit = $selectedUnit ?? '';
@endphp
@if($kepegawaianKlTigaTingkat && ! empty($kepegawaianHierarchyJson))
<script>
(function () {
    function sboIndukOrganisasiMatch(selected, candidate) {
        if (!selected || !candidate) {
            return !selected && !candidate;
        }
        if (String(selected).toLowerCase() === String(candidate).toLowerCase()) {
            return true;
        }
        var aliases = { 'polri': ['mabes polri'], 'mabes polri': ['polri'] };
        var key = String(selected).toLowerCase();
        var set = [key];
        (aliases[key] || []).forEach(function (a) { set.push(a); });
        return set.indexOf(String(candidate).toLowerCase()) !== -1;
    }
    window.__sboIndukOrganisasiMatch = sboIndukOrganisasiMatch;

    var empatTingkat = @json((bool) $kepegawaianKlEmpatTingkat);
    var hierarchy = @json($kepegawaianHierarchyJson);
    var indukEl = document.getElementById(@json($indukFieldId));
    var pemdaEl = document.getElementById(@json($pemdaFieldId));
    var orgDaerahEl = document.getElementById(@json($orgDaerahFieldId));
    var unitEl = document.getElementById(@json($unitFieldId));
    if (!indukEl || !pemdaEl || !unitEl) {
        return;
    }

    var selectedPemda = @json($selectedPemda);
    var selectedOrgDaerah = @json($selectedOrgDaerah);
    var selectedUnit = @json($selectedUnit);

    function resolveIndukKey(val) {
        if (!val) {
            return '';
        }
        if (hierarchy[val]) {
            return val;
        }
        for (var k in hierarchy) {
            if (sboIndukOrganisasiMatch(val, k)) {
                return k;
            }
        }
        return val;
    }

    function refillSelect(selectEl, placeholder, names, selected, allowLegacy) {
        if (!selectEl || selectEl.tagName !== 'SELECT') {
            return selected;
        }
        selectEl.innerHTML = '';
        var opt0 = document.createElement('option');
        opt0.value = '';
        opt0.textContent = placeholder;
        selectEl.appendChild(opt0);
        (names || []).forEach(function (name) {
            var o = document.createElement('option');
            o.value = name;
            o.textContent = name;
            selectEl.appendChild(o);
        });
        if (selected) {
            selectEl.value = selected;
            if (selectEl.value !== selected && allowLegacy) {
                var legacy = document.createElement('option');
                legacy.value = selected;
                legacy.textContent = selected + ' (tidak di master)';
                selectEl.appendChild(legacy);
                selectEl.value = selected;
            }
        }
        return selectEl.value;
    }

    function orgPusatNamesForInduk(indukVal) {
        var key = resolveIndukKey(indukVal);
        var map = hierarchy[key] || {};
        return Object.keys(map).sort();
    }

    function organisasiDaerahNamesFor(indukVal, orgPusatVal) {
        var key = resolveIndukKey(indukVal);
        var map = hierarchy[key] || {};
        var child = map[orgPusatVal];
        if (!child) {
            return [];
        }
        if (empatTingkat) {
            return Object.keys(child).sort();
        }
        return Array.isArray(child) ? child.slice().sort() : [];
    }

    function unitNamesFor(indukVal, orgPusatVal, orgDaerahVal) {
        if (!empatTingkat) {
            return organisasiDaerahNamesFor(indukVal, orgPusatVal);
        }
        var key = resolveIndukKey(indukVal);
        var map = hierarchy[key] || {};
        var resortMap = map[orgPusatVal] || {};
        return resortMap[orgDaerahVal] || [];
    }

    function refillOrgPusat(resetSelection) {
        if (pemdaEl.tagName !== 'SELECT') {
            refillOrgDaerah(resetSelection);
            return;
        }
        var induk = indukEl.value || '';
        var pemda = resetSelection ? '' : selectedPemda;
        selectedPemda = pemda;
        var names = orgPusatNamesForInduk(induk);
        if (!pemda && names.length === 1 && names[0] === '-') {
            pemda = '-';
            selectedPemda = '-';
        }
        refillSelect(
            pemdaEl,
            induk ? '— Pilih organisasi pusat —' : '— Pilih induk organisasi dulu —',
            names,
            pemda,
            true
        );
        refillOrgDaerah(resetSelection && !pemda);
    }

    function refillOrgDaerah(resetSelection) {
        if (!empatTingkat || !orgDaerahEl) {
            refillUnit(resetSelection);
            return;
        }
        if (orgDaerahEl.tagName !== 'SELECT') {
            refillUnit(resetSelection);
            return;
        }
        var induk = indukEl.value || '';
        var orgPusat = pemdaEl.value || '';
        var orgDaerah = resetSelection ? '' : selectedOrgDaerah;
        selectedOrgDaerah = orgDaerah;
        var names = organisasiDaerahNamesFor(induk, orgPusat);
        if (!orgDaerah && names.length === 1 && names[0] === '-') {
            orgDaerah = '-';
            selectedOrgDaerah = '-';
        }
        refillSelect(
            orgDaerahEl,
            orgPusat ? '— Pilih organisasi daerah —' : '— Pilih organisasi pusat dulu —',
            names,
            orgDaerah,
            true
        );
        refillUnit(resetSelection && !orgDaerah);
    }

    function refillUnit(resetSelection) {
        if (unitEl.tagName !== 'SELECT') {
            return;
        }
        var induk = indukEl.value || '';
        var orgPusat = pemdaEl.value || '';
        var orgDaerah = empatTingkat && orgDaerahEl ? (orgDaerahEl.value || '') : '';
        var unit = resetSelection ? '' : selectedUnit;
        selectedUnit = unit;
        var placeholder = empatTingkat
            ? (orgDaerah ? '— Pilih unit kerja —' : '— Pilih organisasi daerah dulu —')
            : (orgPusat ? '— Pilih unit kerja —' : '— Pilih organisasi pusat dulu —');
        refillSelect(
            unitEl,
            placeholder,
            unitNamesFor(induk, orgPusat, orgDaerah),
            unit,
            true
        );
    }

    if (indukEl.tagName === 'SELECT') {
        indukEl.addEventListener('change', function () {
            selectedPemda = '';
            selectedOrgDaerah = '';
            selectedUnit = '';
            refillOrgPusat(true);
        });
    }
    if (pemdaEl.tagName === 'SELECT') {
        pemdaEl.addEventListener('change', function () {
            selectedOrgDaerah = '';
            selectedUnit = '';
            refillOrgDaerah(true);
        });
    }
    if (empatTingkat && orgDaerahEl && orgDaerahEl.tagName === 'SELECT') {
        orgDaerahEl.addEventListener('change', function () {
            selectedUnit = '';
            refillUnit(true);
        });
    }

    refillOrgPusat(false);
})();
</script>
@endif
