/**
 * SBO Autocomplete Helpers
 *
 * 1. sboJalanAutocomplete(inputEl, options)
 *    — Autocomplete untuk field Nama Jalan / Lokasi dari master data t_datajalan,
 *      difilter berdasarkan wilayah (Polres) user yang login.
 *
 *    Atribut HTML:
 *      data-sbo-jalan-ac  — pasang ke <input type="text"> untuk aktifkan autocomplete jalan
 *
 * 2. sboPetugasAutocomplete(containerEl, options)
 *    — Multi-select autocomplete untuk Petugas Lapangan dari tabel usm,
 *      difilter berdasarkan wilayah (Polres) user yang login.
 *      Maks. 5 orang. Nilai disimpan sebagai input hidden name="petugaslap[]".
 *
 *    Atribut HTML:
 *      data-sbo-petugas-ac  — pasang ke <div> container untuk aktifkan multi-select petugas
 *      data-sbo-petugas-name="petugaslap"   — nama field (default: petugaslap)
 *      data-sbo-petugas-max="5"             — maks. pilihan (default: 5)
 *      data-sbo-petugas-value="Budi,Sari"   — nilai awal (dipisah koma)
 */
(function () {
    'use strict';

    /* =========================================================
     * Helpers
     * ========================================================= */

    function debounce(fn, delay) {
        var t;
        return function () {
            clearTimeout(t);
            var args = arguments;
            var ctx = this;
            t = setTimeout(function () { fn.apply(ctx, args); }, delay);
        };
    }

    function escHtml(str) {
        return String(str)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;');
    }

    /* =========================================================
     * 1. Jalan Autocomplete
     * ========================================================= */

    function buildJalanDropdown() {
        var d = document.createElement('ul');
        d.className = 'sbo-ac-dropdown';
        d.setAttribute('role', 'listbox');
        d.style.cssText = [
            'position:absolute', 'z-index:9999', 'background:#fff',
            'border:1px solid #e2e8f0', 'border-radius:0.5rem',
            'box-shadow:0 4px 16px rgba(0,0,0,.12)',
            'max-height:220px', 'overflow-y:auto', 'margin:2px 0 0',
            'padding:4px 0', 'list-style:none', 'min-width:100%',
            'width:100%', 'display:none'
        ].join(';');
        return d;
    }

    function sboJalanAutocomplete(inputEl) {
        inputEl.setAttribute('autocomplete', 'off');
        var wrapper = inputEl.parentElement;
        if (!wrapper) return;
        if (wrapper.style.position === '') wrapper.style.position = 'relative';

        var dropdown = buildJalanDropdown();
        wrapper.appendChild(dropdown);

        var lastQ = '';
        var activeIdx = -1;
        var items = [];

        function setItems(results) {
            items = results;
            activeIdx = -1;
            dropdown.innerHTML = '';
            if (!results.length) {
                var empty = document.createElement('li');
                empty.style.cssText = 'padding:6px 12px;font-size:12px;color:#94a3b8;font-style:italic;';
                empty.textContent = 'Tidak ada data jalan ditemukan.';
                dropdown.appendChild(empty);
                dropdown.style.display = 'block';
                return;
            }
            results.forEach(function (name, i) {
                var li = document.createElement('li');
                li.setAttribute('role', 'option');
                li.setAttribute('data-idx', i);
                li.style.cssText = 'padding:6px 12px;font-size:12px;cursor:pointer;color:#334155;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;';
                li.textContent = name;
                li.addEventListener('mouseenter', function () { setActive(i); });
                li.addEventListener('mousedown', function (e) { e.preventDefault(); selectItem(i); });
                dropdown.appendChild(li);
            });
            dropdown.style.display = 'block';
        }

        function setActive(idx) {
            var lis = dropdown.querySelectorAll('li');
            lis.forEach(function (li, i) {
                li.style.background = i === idx ? '#f1f5f9' : '';
                li.style.color = i === idx ? 'var(--color-primary,#2563eb)' : '#334155';
                li.style.fontWeight = i === idx ? '700' : '';
            });
            activeIdx = idx;
        }

        function selectItem(idx) {
            if (items[idx] !== undefined) {
                inputEl.value = items[idx];
                dropdown.style.display = 'none';
                inputEl.dispatchEvent(new Event('change', { bubbles: true }));
            }
        }

        var fetch = debounce(function (q) {
            if (q === lastQ && dropdown.style.display === 'block') return;
            lastQ = q;
            var url = '/api/autocomplete/jalan?q=' + encodeURIComponent(q);
            window.fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
                .then(function (r) { return r.json(); })
                .then(function (data) { setItems(Array.isArray(data) ? data : []); })
                .catch(function () { setItems([]); });
        }, 220);

        inputEl.addEventListener('input', function () {
            var q = inputEl.value.trim();
            fetch(q);
        });

        inputEl.addEventListener('focus', function () {
            var q = inputEl.value.trim();
            fetch(q);
        });

        inputEl.addEventListener('keydown', function (e) {
            var lis = dropdown.querySelectorAll('li');
            if (!lis.length) return;
            if (e.key === 'ArrowDown') {
                e.preventDefault();
                setActive(Math.min(activeIdx + 1, lis.length - 1));
            } else if (e.key === 'ArrowUp') {
                e.preventDefault();
                setActive(Math.max(activeIdx - 1, 0));
            } else if (e.key === 'Enter' && activeIdx >= 0) {
                e.preventDefault();
                selectItem(activeIdx);
            } else if (e.key === 'Escape') {
                dropdown.style.display = 'none';
            }
        });

        document.addEventListener('click', function (e) {
            if (!wrapper.contains(e.target)) dropdown.style.display = 'none';
        });
    }

    /* =========================================================
     * 2. Petugas Multi-Select Autocomplete
     * ========================================================= */

    var PETUGAS_STYLES = [
        '.sbo-petugas-ac { position:relative; }',
        '.sbo-petugas-tags { display:flex; flex-wrap:wrap; gap:4px; min-height:38px;',
        '  border:1px solid #e2e8f0; border-radius:0.5rem; padding:4px 8px;',
        '  background:#f8fafc; cursor:text; align-items:center;',
        '  transition:border-color .15s,background .15s; }',
        '.sbo-petugas-tags:focus-within { border-color:var(--color-primary,#2563eb); background:#fff; }',
        '.sbo-petugas-tag { display:inline-flex; align-items:center; gap:4px;',
        '  background:var(--color-primary,#2563eb); color:#fff; border-radius:999px;',
        '  padding:2px 8px 2px 10px; font-size:11px; font-weight:700; white-space:nowrap; }',
        '.sbo-petugas-tag-rm { background:none; border:0; color:#fff; cursor:pointer;',
        '  font-size:14px; line-height:1; padding:0 0 0 2px; opacity:.75; }',
        '.sbo-petugas-tag-rm:hover { opacity:1; }',
        '.sbo-petugas-input { border:0; outline:none; background:transparent;',
        '  font-size:12px; min-width:120px; flex:1; padding:2px 0; color:#1e293b; }',
        '.sbo-petugas-hint { font-size:10px; color:#94a3b8; margin-top:2px; }',
        '.sbo-petugas-dropdown { position:absolute; z-index:9999; background:#fff;',
        '  border:1px solid #e2e8f0; border-radius:0.5rem;',
        '  box-shadow:0 4px 16px rgba(0,0,0,.12); max-height:220px; overflow-y:auto;',
        '  margin:2px 0 0; padding:4px 0; list-style:none; width:100%; display:none; }',
        '.sbo-petugas-opt { padding:7px 12px; font-size:12px; cursor:pointer; color:#334155; }',
        '.sbo-petugas-opt:hover,.sbo-petugas-opt.active { background:#f1f5f9;',
        '  color:var(--color-primary,#2563eb); font-weight:700; }',
        '.sbo-petugas-opt small { display:block; font-size:10px; color:#94a3b8; font-weight:400; }',
        '.sbo-petugas-empty { padding:8px 12px; font-size:12px; color:#94a3b8; font-style:italic; }',
    ].join('\n');

    function injectPetugasStyles() {
        if (document.getElementById('sbo-petugas-ac-style')) return;
        var s = document.createElement('style');
        s.id = 'sbo-petugas-ac-style';
        s.textContent = PETUGAS_STYLES;
        document.head.appendChild(s);
    }

    function sboPetugasAutocomplete(containerEl) {
        injectPetugasStyles();

        var fieldName = containerEl.getAttribute('data-sbo-petugas-name') || 'petugaslap';
        var maxPick = parseInt(containerEl.getAttribute('data-sbo-petugas-max') || '5', 10);
        var initVal = (containerEl.getAttribute('data-sbo-petugas-value') || '').trim();

        // Selected: array of {id, nama}
        var selected = [];

        /* ---- DOM ---- */
        containerEl.classList.add('sbo-petugas-ac');
        containerEl.innerHTML = '';

        var tagsWrap = document.createElement('div');
        tagsWrap.className = 'sbo-petugas-tags';

        var acInput = document.createElement('input');
        acInput.type = 'text';
        acInput.className = 'sbo-petugas-input';
        acInput.placeholder = 'Cari nama petugas…';
        acInput.autocomplete = 'off';

        var hint = document.createElement('p');
        hint.className = 'sbo-petugas-hint';

        var dropdown = document.createElement('ul');
        dropdown.className = 'sbo-petugas-dropdown';

        containerEl.appendChild(tagsWrap);
        tagsWrap.appendChild(acInput);
        containerEl.appendChild(dropdown);
        containerEl.appendChild(hint);

        function updateHint() {
            hint.textContent = selected.length >= maxPick
                ? 'Maks. ' + maxPick + ' petugas. Hapus untuk mengganti.'
                : 'Pilih maks. ' + maxPick + ' petugas lapangan.';
        }

        function renderTags() {
            // Remove old tags (keep input)
            Array.from(tagsWrap.querySelectorAll('.sbo-petugas-tag')).forEach(function (el) { el.remove(); });
            // Remove old hidden inputs
            Array.from(containerEl.querySelectorAll('input[type=hidden]')).forEach(function (el) { el.remove(); });

            selected.forEach(function (person) {
                // Hidden input
                var hidden = document.createElement('input');
                hidden.type = 'hidden';
                hidden.name = fieldName + '[]';
                hidden.value = person.nama;
                containerEl.appendChild(hidden);

                // Tag chip
                var tag = document.createElement('span');
                tag.className = 'sbo-petugas-tag';
                tag.textContent = person.nama;

                var rm = document.createElement('button');
                rm.type = 'button';
                rm.className = 'sbo-petugas-tag-rm';
                rm.innerHTML = '&times;';
                rm.title = 'Hapus';
                rm.addEventListener('click', function () {
                    selected = selected.filter(function (p) { return p.id !== person.id; });
                    renderTags();
                    updateHint();
                });

                tag.appendChild(rm);
                tagsWrap.insertBefore(tag, acInput);
            });

            acInput.disabled = selected.length >= maxPick;
            if (acInput.disabled) {
                acInput.placeholder = 'Maks. ' + maxPick + ' petugas';
            } else {
                acInput.placeholder = selected.length === 0 ? 'Cari nama petugas…' : 'Tambah petugas lain…';
            }
        }

        function setDropdownItems(results) {
            dropdown.innerHTML = '';
            if (!results.length) {
                var empty = document.createElement('li');
                empty.className = 'sbo-petugas-empty';
                empty.textContent = 'Tidak ada petugas ditemukan.';
                dropdown.appendChild(empty);
                dropdown.style.display = 'block';
                return;
            }
            results.forEach(function (person) {
                var alreadySelected = selected.some(function (p) { return p.id === person.id; });
                var li = document.createElement('li');
                li.className = 'sbo-petugas-opt' + (alreadySelected ? ' active' : '');
                li.innerHTML = escHtml(person.nama) + (person.nrp ? '<small>' + escHtml(person.nrp) + '</small>' : '');
                li.addEventListener('mousedown', function (e) {
                    e.preventDefault();
                    if (alreadySelected) return;
                    if (selected.length >= maxPick) return;
                    selected.push({ id: person.id, nama: person.nama });
                    renderTags();
                    updateHint();
                    acInput.value = '';
                    dropdown.style.display = 'none';
                    acInput.focus();
                });
                dropdown.appendChild(li);
            });
            dropdown.style.display = 'block';
        }

        var lastFetch = debounce(function (q) {
            var url = '/api/autocomplete/petugas?q=' + encodeURIComponent(q);
            window.fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
                .then(function (r) { return r.json(); })
                .then(function (data) { setDropdownItems(Array.isArray(data) ? data : []); })
                .catch(function () { dropdown.style.display = 'none'; });
        }, 220);

        acInput.addEventListener('input', function () {
            var q = acInput.value.trim();
            lastFetch(q);
        });

        acInput.addEventListener('focus', function () {
            var q = acInput.value.trim();
            lastFetch(q);
        });

        acInput.addEventListener('keydown', function (e) {
            if (e.key === 'Escape') dropdown.style.display = 'none';
            if (e.key === 'Backspace' && acInput.value === '' && selected.length > 0) {
                selected.pop();
                renderTags();
                updateHint();
            } else if ((e.key === 'Enter' || e.key === ',') && acInput.value.trim() !== '') {
                e.preventDefault();
                var q = acInput.value.trim().replace(/,/g, '');
                if (q !== '' && selected.length < maxPick && !selected.some(function (p) { return p.nama.toLowerCase() === q.toLowerCase(); })) {
                    selected.push({ id: 'manual_' + Date.now(), nama: q });
                    renderTags();
                    updateHint();
                    acInput.value = '';
                    dropdown.style.display = 'none';
                    acInput.focus();
                }
            }
        });

        document.addEventListener('click', function (e) {
            if (!containerEl.contains(e.target)) dropdown.style.display = 'none';
        });

        tagsWrap.addEventListener('click', function () { acInput.focus(); });

        // Init with existing values (comma-separated names from old text field)
        if (initVal) {
            initVal.split(',').map(function (v) { return v.trim(); }).filter(Boolean).slice(0, maxPick).forEach(function (nama) {
                selected.push({ id: 'legacy_' + nama, nama: nama });
            });
        }

        renderTags();
        updateHint();
    }

    /* =========================================================
     * Auto-init on DOMContentLoaded
     * ========================================================= */

    function init() {
        // Jalan autocomplete
        document.querySelectorAll('[data-sbo-jalan-ac]').forEach(function (el) {
            sboJalanAutocomplete(el);
        });
        // Petugas multi-select autocomplete
        document.querySelectorAll('[data-sbo-petugas-ac]').forEach(function (el) {
            sboPetugasAutocomplete(el);
        });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }

    // Expose for manual init (e.g. after AJAX modal load)
    window.sboJalanAutocomplete = sboJalanAutocomplete;
    window.sboPetugasAutocomplete = sboPetugasAutocomplete;
})();
