(function () {
    'use strict';

    function parseDmY(value) {
        var m = String(value || '').trim().match(/^(\d{1,2})\/(\d{1,2})\/(\d{2,4})$/);
        if (!m) {
            return null;
        }

        var d = parseInt(m[1], 10);
        var mo = parseInt(m[2], 10);
        var y = parseInt(m[3], 10);

        if (m[3].length === 2) {
            y += y >= 70 ? 1900 : 2000;
        }

        if (mo < 1 || mo > 12 || d < 1 || d > 31) {
            return null;
        }

        return { y: y, mo: mo, d: d };
    }

    function toIso(parsed) {
        return parsed.y + '-' + String(parsed.mo).padStart(2, '0') + '-' + String(parsed.d).padStart(2, '0');
    }

    function toIsoFromDate(date) {
        return date.getFullYear() + '-'
            + String(date.getMonth() + 1).padStart(2, '0') + '-'
            + String(date.getDate()).padStart(2, '0');
    }

    function normalizeDisplayValue(el) {
        var v = String(el.value || '').trim();
        var iso = v.match(/^(\d{4})-(\d{2})-(\d{2})/);

        if (iso) {
            el.value = iso[3] + '/' + iso[2] + '/' + iso[1].slice(-2);
        }
    }

    function syncRangeHidden(wrap, selectedDates) {
        if (!wrap) {
            return;
        }

        var dariInput = wrap.querySelector('[data-sbo-range-dari]');
        var sampaiInput = wrap.querySelector('[data-sbo-range-sampai]');

        if (!dariInput || !sampaiInput) {
            return;
        }

        if (!selectedDates || !selectedDates.length) {
            dariInput.value = '';
            sampaiInput.value = '';
            return;
        }

        dariInput.value = toIsoFromDate(selectedDates[0]);
        sampaiInput.value = toIsoFromDate(selectedDates[selectedDates.length - 1]);
    }

    function initDateRangeInputs(root) {
        if (typeof flatpickr === 'undefined') {
            return;
        }

        (root || document).querySelectorAll('[data-sbo-date-range]').forEach(function (el) {
            if (el._sboRangeFp) {
                return;
            }

            var wrap = el.closest('[data-sbo-date-range-wrap]');
            var dariInput = wrap ? wrap.querySelector('[data-sbo-range-dari]') : null;
            var sampaiInput = wrap ? wrap.querySelector('[data-sbo-range-sampai]') : null;
            var initialDates = [];

            if (dariInput && dariInput.value) {
                initialDates.push(dariInput.value);
            }
            if (sampaiInput && sampaiInput.value && sampaiInput.value !== (dariInput ? dariInput.value : '')) {
                initialDates.push(sampaiInput.value);
            }

            el._sboRangeFp = flatpickr(el, {
                mode: 'range',
                dateFormat: 'd/m/y',
                disableMobile: true,
                locale: {
                    rangeSeparator: ' — ',
                },
                defaultDate: initialDates.length ? initialDates : null,
                onChange: function (selectedDates) {
                    syncRangeHidden(wrap, selectedDates);
                },
                onClose: function (selectedDates) {
                    syncRangeHidden(wrap, selectedDates);
                },
            });
        });
    }

    function initDateInputs(root) {
        if (typeof flatpickr === 'undefined') {
            return;
        }

        (root || document).querySelectorAll('[data-sbo-date-input]').forEach(function (el) {
            if (el._sboFlatpickr) {
                return;
            }

            normalizeDisplayValue(el);

            el._sboFlatpickr = flatpickr(el, {
                dateFormat: 'd/m/y',
                allowInput: true,
                disableMobile: true,
                defaultDate: el.value.trim() || null,
            });
        });
    }

    function bindForms(root) {
        (root || document).querySelectorAll('form').forEach(function (form) {
            if (form._sboDateBound) {
                return;
            }

            form._sboDateBound = true;

            form.addEventListener('submit', function () {
                form.querySelectorAll('[data-sbo-date-range]').forEach(function (el) {
                    if (el._sboRangeFp) {
                        syncRangeHidden(
                            el.closest('[data-sbo-date-range-wrap]'),
                            el._sboRangeFp.selectedDates
                        );
                    }
                });

                form.querySelectorAll('[data-sbo-date-input]').forEach(function (input) {
                    var parsed = parseDmY(input.value);
                    if (parsed) {
                        input.value = toIso(parsed);
                    }
                });
            });
        });
    }

    function boot() {
        initDateRangeInputs(document);
        initDateInputs(document);
        bindForms(document);
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', boot);
    } else {
        boot();
    }

    document.addEventListener('sbo-layout', boot);
})();
