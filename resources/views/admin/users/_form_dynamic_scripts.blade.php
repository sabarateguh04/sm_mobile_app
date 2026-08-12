@php
    $formUser = isset($user) ? $user : null;
    $selectedWilayahInitial = old('wilayah', optional($formUser)->wilayah ?? '');
    $filterKepegawaianIndukUnit = $filterKepegawaianIndukUnit ?? false;
    $filterKepegawaianIndukPemerintahanDaerah = $filterKepegawaianIndukPemerintahanDaerah ?? false;
    $filterKepegawaianOrganisasiDaerahUnit = $filterKepegawaianOrganisasiDaerahUnit ?? false;
    $kepegawaianKlTigaTingkat = $kepegawaianKlTigaTingkat ?? false;
    $hintPilihIndukWilayah = '— Pilih Polda dulu —';
    $hintPilihAnakWilayah = '— Pilih Polres —';
@endphp
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
})();
(function () {
    var byDaerah = @json($wilayahByDaerahJson ?? []);
    var daerahEl = document.getElementById('userDaerah');
    var wilayahEl = document.getElementById('userWilayah');
    if (!daerahEl || !wilayahEl) return;
    var selectedWilayah = @json($selectedWilayahInitial);
    var hintPilihInduk = @json($hintPilihIndukWilayah);
    var hintPilihAnak = @json($hintPilihAnakWilayah);

    function clearError(el) {
        if (!el) return;
        var container = el.closest('.min-w-0') || el.parentNode;
        if (container) {
            var errorEls = container.querySelectorAll('.invalid-feedback, .text-red-600');
            errorEls.forEach(function (errorEl) {
                errorEl.style.display = 'none';
            });
        }
        el.classList.remove('border-red-300', 'bg-red-50', 'is-invalid');
        if (window.jQuery) {
            $(el).removeClass('border-red-300 bg-red-50 is-invalid');
            if ($(el).hasClass('select2-hidden-accessible')) {
                var select2Selection = $(el).next('.select2-container').find('.select2-selection');
                select2Selection.removeClass('is-invalid border-red-300 bg-red-50');
            }
        }
    }

    function refillWilayah(resetSelection) {
        var d = daerahEl.value;
        wilayahEl.innerHTML = '';
        var opt0 = document.createElement('option');
        opt0.value = '';
        opt0.textContent = d ? hintPilihAnak : hintPilihInduk;
        wilayahEl.appendChild(opt0);
        if (d) {
            var dLower = String(d).toLowerCase();
            var list = null;
            for (var key in byDaerah) {
                if (Object.prototype.hasOwnProperty.call(byDaerah, key) && String(key).toLowerCase() === dLower) {
                    list = byDaerah[key];
                    break;
                }
            }

            if (list) {
                list.forEach(function (item) {
                var name = typeof item === 'string' ? item : (item && item.nama ? item.nama : '');
                if (!name) {
                    return;
                }
                var o = document.createElement('option');
                o.value = name;
                var displayName = name.replace(/^(KAB\.\s*|KAB\s+|KABUPATEN\s+|KOTA\s+)/i, '');
                o.textContent = displayName.toUpperCase();
                wilayahEl.appendChild(o);
                });
            }
        }
        if (!resetSelection && selectedWilayah) {
            wilayahEl.value = selectedWilayah;
            if (wilayahEl.value !== selectedWilayah) {
                var o = document.createElement('option');
                o.value = selectedWilayah;
                o.textContent = selectedWilayah + ' (tidak di master)';
                wilayahEl.appendChild(o);
                wilayahEl.value = selectedWilayah;
            }
        }
        if (window.jQuery && $(wilayahEl).hasClass('select2-hidden-accessible')) {
            $(wilayahEl).trigger('change.select2');
        }
    }

    if (window.jQuery) {
        $(daerahEl).on('change', function () {
            selectedWilayah = '';
            refillWilayah(true);
            clearError(daerahEl);
            clearError(wilayahEl);
        });
        $(wilayahEl).on('change', function () {
            clearError(wilayahEl);
        });
    } else {
        daerahEl.addEventListener('change', function () {
            selectedWilayah = '';
            refillWilayah(true);
            clearError(daerahEl);
            clearError(wilayahEl);
        });
        wilayahEl.addEventListener('change', function () {
            clearError(wilayahEl);
        });
    }
    refillWilayah(false);
})();
(function () {
    var roleEl = document.querySelector('select[name="user_role_id"]');
    var wilayahEl = document.getElementById('userWilayah');
    if (!roleEl || !wilayahEl) return;

    function handleRoleChange() {
        var isExec2 = roleEl.value === '6';
        if (isExec2) {
            wilayahEl.value = '';
            wilayahEl.disabled = true;
            if (window.jQuery) {
                $(wilayahEl).prop('disabled', true);
                if ($(wilayahEl).hasClass('select2-hidden-accessible')) {
                    $(wilayahEl).trigger('change.select2');
                }
            }
        } else {
            wilayahEl.disabled = false;
            if (window.jQuery) {
                $(wilayahEl).prop('disabled', false);
                if ($(wilayahEl).hasClass('select2-hidden-accessible')) {
                    $(wilayahEl).trigger('change.select2');
                }
            }
        }
    }

    roleEl.addEventListener('change', handleRoleChange);
    // Run on init
    handleRoleChange();

    // Also watch for when daerah changes: if role is Executive 2, make sure wilayah remains cleared/disabled
    var daerahEl = document.getElementById('userDaerah');
    if (daerahEl) {
        var handler = function () {
            if (roleEl.value === '6') {
                wilayahEl.value = '';
                wilayahEl.disabled = true;
                if (window.jQuery && $(wilayahEl).hasClass('select2-hidden-accessible')) {
                    $(wilayahEl).prop('disabled', true).trigger('change.select2');
                }
            }
        };
        daerahEl.addEventListener('change', handler);
        if (window.jQuery) {
            $(daerahEl).on('change', handler);
        }
    }
})();
</script>
@php
    $useKlHierarchyCascade = ($kepegawaianKlTigaTingkat ?? false) && ! empty($kepegawaianHierarchyJson ?? []);
@endphp
@if($useKlHierarchyCascade)
@include('shared._kepegawaian_kl_cascade_scripts', [
    'kepegawaianKlTigaTingkat' => $kepegawaianKlTigaTingkat,
    'kepegawaianKlEmpatTingkat' => $kepegawaianKlEmpatTingkat ?? false,
    'kepegawaianHierarchyJson' => $kepegawaianHierarchyJson,
    'indukFieldId' => 'user_induk_pemerintahan',
    'pemdaFieldId' => 'user_pemerintahan_daerah',
    'orgDaerahFieldId' => 'user_organisasi_daerah',
    'unitFieldId' => 'user_unit_kerja',
    'selectedPemda' => $selectedPemda ?? '',
    'selectedOrgDaerah' => $selectedOrgDaerah ?? '',
    'selectedUnit' => $selectedUnit ?? '',
])
@else
<script>
@if($filterKepegawaianIndukPemerintahanDaerah)
(function () {
    var indukSel = document.getElementById('user_induk_pemerintahan');
    var pemdaSel = document.getElementById('user_pemerintahan_daerah');
    if (!indukSel || !pemdaSel || indukSel.tagName !== 'SELECT' || pemdaSel.tagName !== 'SELECT') return;

    function filterPemdaByInduk() {
        var indukName = indukSel.value;
        var hadSelection = false;
        pemdaSel.querySelectorAll('option[data-induk-org]').forEach(function (opt) {
            var show = !indukName || window.__sboIndukOrganisasiMatch(indukName, opt.getAttribute('data-induk-org') || '');
            opt.hidden = !show;
            if (!show && opt.selected) {
                opt.selected = false;
            }
            if (show && opt.selected) {
                hadSelection = true;
            }
        });
        if (!indukName) {
            pemdaSel.value = '';
            if (@json($kepegawaianKlTigaTingkat)) {
                var unitSel = document.getElementById('user_unit_kerja');
                if (unitSel && unitSel.tagName === 'SELECT') {
                    unitSel.value = '';
                }
            }
            return;
        }
        if (!hadSelection) {
            for (var i = 0; i < pemdaSel.options.length; i++) {
                var o = pemdaSel.options[i];
                if (o.hidden || !o.value) continue;
                if (window.__sboIndukOrganisasiMatch(indukName, o.getAttribute('data-induk-org') || '')) {
                    o.selected = true;
                    break;
                }
            }
        }
        if (@json($kepegawaianKlTigaTingkat) && typeof window.__filterUnitByOrgDaerah === 'function') {
            window.__filterUnitByOrgDaerah();
        }
    }

    indukSel.addEventListener('change', filterPemdaByInduk);
    filterPemdaByInduk();
})();
@endif
@if($filterKepegawaianOrganisasiDaerahUnit)
(function () {
    var pemdaSel = document.getElementById('user_pemerintahan_daerah');
    var unitSel = document.getElementById('user_unit_kerja');
    if (!pemdaSel || !unitSel || pemdaSel.tagName !== 'SELECT' || unitSel.tagName !== 'SELECT') return;

    function filterUnitByOrgDaerah() {
        var orgDaerah = pemdaSel.value;
        var indukName = document.getElementById('user_induk_pemerintahan')?.value || '';
        var hadSelection = false;
        unitSel.querySelectorAll('option[data-org-daerah]').forEach(function (opt) {
            var matchOrg = !orgDaerah || opt.getAttribute('data-org-daerah') === orgDaerah;
            var indukAttr = opt.getAttribute('data-induk-org') || '';
            var matchInduk = !indukName || indukAttr === '' || window.__sboIndukOrganisasiMatch(indukName, indukAttr);
            var show = matchOrg && matchInduk;
            opt.hidden = !show;
            if (!show && opt.selected) {
                opt.selected = false;
            }
            if (show && opt.selected) {
                hadSelection = true;
            }
        });
        if (!orgDaerah) {
            unitSel.value = '';
            return;
        }
        if (!hadSelection) {
            for (var i = 0; i < unitSel.options.length; i++) {
                var o = unitSel.options[i];
                if (o.hidden || !o.value) continue;
                if (o.getAttribute('data-org-daerah') === orgDaerah) {
                    o.selected = true;
                    break;
                }
            }
        }
    }

    window.__filterUnitByOrgDaerah = filterUnitByOrgDaerah;
    pemdaSel.addEventListener('change', filterUnitByOrgDaerah);
    filterUnitByOrgDaerah();
})();
@endif
@if($filterKepegawaianIndukUnit)
(function () {
    var indukSel = document.getElementById('user_induk_pemerintahan');
    var unitSel = document.getElementById('user_unit_kerja');
    if (!indukSel || !unitSel || indukSel.tagName !== 'SELECT' || unitSel.tagName !== 'SELECT') return;

    function filterUnitByInduk() {
        var indukName = indukSel.value;
        var hadSelection = false;
        unitSel.querySelectorAll('option[data-induk-org]').forEach(function (opt) {
            var show = !indukName || window.__sboIndukOrganisasiMatch(indukName, opt.getAttribute('data-induk-org') || '');
            opt.hidden = !show;
            if (!show && opt.selected) {
                opt.selected = false;
            }
            if (show && opt.selected) {
                hadSelection = true;
            }
        });
        if (!indukName) {
            unitSel.value = '';
            return;
        }
        if (!hadSelection) {
            for (var i = 0; i < unitSel.options.length; i++) {
                var o = unitSel.options[i];
                if (o.hidden || !o.value) continue;
                if (window.__sboIndukOrganisasiMatch(indukName, o.getAttribute('data-induk-org') || '')) {
                    o.selected = true;
                    break;
                }
            }
        }
    }

    indukSel.addEventListener('change', filterUnitByInduk);
    filterUnitByInduk();
})();
@endif
@endif
</script>
