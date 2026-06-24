/**
 * SBO GPS Auto-fill
 * Mengisi field lat/lng via tombol GPS diklik.
 * Auto-trigger saat form OPSDAL dibuka (sb_grp=opsdal / sm_lv=opsdal) jika koordinat masih kosong.
 *
 * Tombol GPS:
 *   data-sbo-gps-lat="<id input latitude>"
 *   data-sbo-gps-lng="<id input longitude>"
 *   data-sbo-gps-status="<id elemen status>" (opsional)
 */
(function () {
    'use strict';

    var STATUS_BASE = 'text-[11px] font-medium mt-1 block sm:col-span-2';

    function setStatus(statusEl, message, tone) {
        if (!statusEl) return;
        statusEl.textContent = message;
        statusEl.classList.remove('sr-only');
        statusEl.className = STATUS_BASE;
        if (tone === 'success') {
            statusEl.classList.add('text-emerald-600');
        } else if (tone === 'error') {
            statusEl.classList.add('text-red-600');
        } else if (tone === 'warn') {
            statusEl.classList.add('text-amber-600');
        } else {
            statusEl.classList.add('text-slate-500');
        }
    }

    function formatCoord(n) {
        var num = Number(n);
        if (!isFinite(num)) return '';
        var s = num.toFixed(8).replace(/\.?0+$/, '');
        return s === '-0' ? '0' : s;
    }

    function doGPS(latEl, lngEl, statusEl, btn) {
        if (!navigator.geolocation) {
            setStatus(statusEl, 'Peramban tidak mendukung geolokasi.', 'error');
            return;
        }

        if (!window.isSecureContext) {
            setStatus(statusEl, 'GPS memerlukan HTTPS. Buka situs lewat koneksi aman (https://).', 'error');
            return;
        }

        setStatus(statusEl, 'Mengambil lokasi GPS… izinkan akses lokasi jika diminta.', 'muted');
        if (btn) btn.disabled = true;

        navigator.geolocation.getCurrentPosition(
            function (pos) {
                latEl.value = formatCoord(pos.coords.latitude);
                lngEl.value = formatCoord(pos.coords.longitude);
                ['input', 'change'].forEach(function (type) {
                    latEl.dispatchEvent(new Event(type, { bubbles: true }));
                    lngEl.dispatchEvent(new Event(type, { bubbles: true }));
                });
                setStatus(statusEl, 'Koordinat berhasil diisi dari GPS.', 'success');
                if (btn) btn.disabled = false;
            },
            function (err) {
                var hint = 'Isi manual atau klik tombol GPS lagi.';
                var msg = 'GPS gagal: izin ditolak atau tidak tersedia.';
                if (err && err.code === 1) {
                    msg = 'GPS gagal: izin lokasi ditolak.';
                } else if (err && err.code === 2) {
                    msg = 'GPS gagal: posisi tidak tersedia.';
                } else if (err && err.code === 3) {
                    msg = 'GPS gagal: waktu habis, coba lagi.';
                } else if (err && err.message) {
                    msg = 'GPS gagal: ' + err.message;
                }
                setStatus(statusEl, msg + ' ' + hint, 'warn');
                if (btn) btn.disabled = false;
            },
            { enableHighAccuracy: true, timeout: 25000, maximumAge: 0 }
        );
    }

    function isOpsdalForm(el) {
        if (!el) return false;
        var form = el.closest ? el.closest('form') : null;
        if (form && form.querySelector(
            'input[name="sb_grp"][value="opsdal"], input[name="sm_lv"][value="opsdal"]'
        )) {
            return true;
        }
        try {
            var params = new URLSearchParams(window.location.search);
            return params.get('sb_grp') === 'opsdal' || params.get('sm_lv') === 'opsdal';
        } catch (e) {
            return false;
        }
    }

    function init() {
        document.querySelectorAll('[data-sbo-gps-lat]').forEach(function (btn) {
            var latId = btn.getAttribute('data-sbo-gps-lat');
            var lngId = btn.getAttribute('data-sbo-gps-lng');
            var statusId = btn.getAttribute('data-sbo-gps-status');

            var latEl = latId ? document.getElementById(latId) : null;
            var lngEl = lngId ? document.getElementById(lngId) : null;
            var statusEl = statusId ? document.getElementById(statusId) : null;

            if (!latEl || !lngEl) return;

            btn.addEventListener('click', function (e) {
                e.preventDefault();
                doGPS(latEl, lngEl, statusEl, btn);
            });

            // Dinonaktifkan: pengisian otomatis GPS saat form dibuka dinonaktifkan
            // karena koordinat akan diisi oleh user dari Flutter.
            /*
            if (isOpsdalForm(btn) &&
                !String(latEl.value || '').trim() &&
                !String(lngEl.value || '').trim()) {
                doGPS(latEl, lngEl, statusEl, btn);
            }
            */
        });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
})();
