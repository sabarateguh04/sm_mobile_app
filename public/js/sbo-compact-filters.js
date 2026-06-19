(function () {
    'use strict';

    function syncNativeSelect(sel) {
        sel.classList.toggle('pmf-empty', !sel.value);
    }

    function initNativeSelects(root) {
        var form = root.closest('form') || root;
        if (!form || !form.matches('.sbo-compact-filters, .personil-manager-filters, .patrol-cctv-filters')) {
            return;
        }
        if (window.jQuery) {
            jQuery(form).find('select').each(function () {
                var $s = jQuery(this);
                if ($s.data('select2')) {
                    $s.select2('destroy');
                }
            });
            jQuery(form).find('.select2-container').remove();
        }
        form.querySelectorAll('select.pmf-native-select').forEach(function (sel) {
            syncNativeSelect(sel);
            if (!sel._sboPmfBound) {
                sel._sboPmfBound = true;
                sel.addEventListener('change', function () {
                    syncNativeSelect(sel);
                });
            }
        });
    }

    function ymdToDmy(ymd) {
        if (!ymd) {
            return '';
        }
        var p = String(ymd).split('-');
        return p[2] + '/' + p[1] + '/' + p[0];
    }

    function initDateRange(wrap) {
        if (wrap._sboDateFp || typeof flatpickr === 'undefined') {
            return;
        }

        var displayId = wrap.getAttribute('data-sbo-date-display');
        var fromId = wrap.getAttribute('data-sbo-date-from-input');
        var toId = wrap.getAttribute('data-sbo-date-to-input');
        var display = displayId ? document.getElementById(displayId) : null;
        var fromInput = fromId ? document.getElementById(fromId) : null;
        var toInput = toId ? document.getElementById(toId) : null;
        var clearBtn = wrap.querySelector('.sbo-compact-date-clear');

        if (!display || !fromInput || !toInput) {
            return;
        }

        var fromVal = fromInput.value || '';
        var toVal = toInput.value || '';

        function updateDisplay(from, to) {
            if (from && to) {
                display.value = ymdToDmy(from) + ' — ' + ymdToDmy(to);
            } else if (from) {
                display.value = ymdToDmy(from) + ' — …';
            } else {
                display.value = '';
            }
        }

        updateDisplay(fromVal, toVal);

        var fp = flatpickr(display, {
            mode: 'range',
            dateFormat: 'Y-m-d',
            defaultDate: (fromVal && toVal) ? [fromVal, toVal] : (fromVal ? [fromVal] : []),
            locale: {
                rangeSeparator: ' — ',
                firstDayOfWeek: 1,
            },
            onReady: function () {
                updateDisplay(fromVal, toVal);
            },
            onClose: function (selectedDates) {
                if (selectedDates.length === 2) {
                    var fmt = function (d) {
                        return d.getFullYear() + '-' +
                            String(d.getMonth() + 1).padStart(2, '0') + '-' +
                            String(d.getDate()).padStart(2, '0');
                    };
                    var dmy = function (d) {
                        return String(d.getDate()).padStart(2, '0') + '/' +
                            String(d.getMonth() + 1).padStart(2, '0') + '/' +
                            d.getFullYear();
                    };
                    fromInput.value = fmt(selectedDates[0]);
                    toInput.value = fmt(selectedDates[1]);
                    display.value = dmy(selectedDates[0]) + ' — ' + dmy(selectedDates[1]);
                } else if (selectedDates.length === 1) {
                    var fmtS = function (d) {
                        return d.getFullYear() + '-' +
                            String(d.getMonth() + 1).padStart(2, '0') + '-' +
                            String(d.getDate()).padStart(2, '0');
                    };
                    var dmyS = function (d) {
                        return String(d.getDate()).padStart(2, '0') + '/' +
                            String(d.getMonth() + 1).padStart(2, '0') + '/' +
                            d.getFullYear();
                    };
                    fromInput.value = fmtS(selectedDates[0]);
                    toInput.value = '';
                    display.value = dmyS(selectedDates[0]) + ' — …';
                } else {
                    fromInput.value = '';
                    toInput.value = '';
                    display.value = '';
                }
            },
        });

        wrap._sboDateFp = fp;

        wrap.addEventListener('click', function (e) {
            if (clearBtn && clearBtn.contains(e.target)) {
                return;
            }
            fp.open();
        });

        if (clearBtn) {
            clearBtn.addEventListener('click', function (e) {
                e.stopPropagation();
                fp.clear();
                fromInput.value = '';
                toInput.value = '';
                display.value = '';
            });
        }
    }

    function wireLegacyDateRange(form, wrap) {
        if (wrap._sboDateFp || wrap.classList.contains('sbo-compact-date-range')) {
            return;
        }

        var display = wrap.querySelector('[id$="-date-range-display"], #date-range-display');
        var fromInput = wrap.querySelector('[id$="-date-from"], #tanggal_from_input, [name="tanggal_from"]');
        var toInput = wrap.querySelector('[id$="-date-to"], #tanggal_to_input, [name="tanggal_to"]');

        if (!display || !fromInput || !toInput) {
            return;
        }

        wrap.classList.add('sbo-compact-date-range');
        if (!wrap.getAttribute('data-sbo-date-display')) {
            wrap.setAttribute('data-sbo-date-display', display.id);
        }
        if (!wrap.getAttribute('data-sbo-date-from-input')) {
            wrap.setAttribute('data-sbo-date-from-input', fromInput.id || '');
        }
        if (!wrap.getAttribute('data-sbo-date-to-input')) {
            wrap.setAttribute('data-sbo-date-to-input', toInput.id || '');
        }

        initDateRange(wrap);
    }

    function boot(root) {
        (root || document).querySelectorAll('.sbo-compact-filters, .personil-manager-filters, .patrol-cctv-filters').forEach(function (form) {
            initNativeSelects(form);
            form.querySelectorAll('.sbo-compact-date-range').forEach(initDateRange);
            form.querySelectorAll('.pmf-date .pmf-control[id*="date-range"]').forEach(function (wrap) {
                wireLegacyDateRange(form, wrap);
            });
        });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', function () {
            boot(document);
        });
    } else {
        boot(document);
    }

    document.addEventListener('sbo-layout', function () {
        boot(document);
    });
})();
