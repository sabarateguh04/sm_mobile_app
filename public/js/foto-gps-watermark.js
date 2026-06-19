/**
 * Watermark GPS otomatis — alamat detail 3 baris + koordinat + timestamp.
 * Aktifkan dengan menambahkan  data-sbo-foto-gps  pada <input type="file">.
 *
 * Tampilan watermark (banner bawah foto, full-width):
 * ┌──────────────────────────────────────────────────────────────┐
 * │ 📍 No. 12, Jl. Sudirman, Dusun Sari, Lingk. RW 05           │
 * │    Kel. Senayan, Kec. Kebayoran Baru                         │
 * │    Jakarta Selatan, DKI Jakarta  12190                       │
 * │ 🕒 19/05/2026 10:28:45  ·  GPS: -6.227123, 106.803456        │
 * └──────────────────────────────────────────────────────────────┘
 */
(function (global) {
    'use strict';

    // ─── Helpers ────────────────────────────────────────────────────────────

    function p2(n) { return String(n).padStart(2, '0'); }

    function nowString(d) {
        return p2(d.getDate()) + '/' + p2(d.getMonth() + 1) + '/' + d.getFullYear() +
            '  ' + p2(d.getHours()) + ':' + p2(d.getMinutes()) + ':' + p2(d.getSeconds());
    }

    // Potong teks jika terlalu panjang (mirip Str::limit Laravel)
    function limit(str, max) {
        if (!str) return '';
        return str.length > max ? str.slice(0, max - 1) + '\u2026' : str;
    }

    // ─── Parsing alamat Nominatim → 3 baris detail ──────────────────────────

    /**
     * @param {object} a  — data.address dari Nominatim
     * @returns {{ baris1: string, baris2: string, baris3: string }}
     */
    function parseAlamat(a) {
        // Baris 1: detail terkecil (nomor, jalan, dusun/kampung, RT/RW/lingkungan)
        var baris1Parts = [
            a.house_number ? 'No.\u00A0' + a.house_number : null,
            a.road,
            a.hamlet,          // dusun / kampung
            a.neighbourhood,   // lingkungan / RW
        ].filter(Boolean);
        var baris1 = limit(baris1Parts.join(', '), 65);

        // Baris 2: desa/kelurahan, kecamatan
        var kel = a.village || a.suburb || a.quarter || '';
        var kec = a.city_district || a.county || '';
        var baris2 = '';
        if (kel && kec) {
            baris2 = limit('Kel.\u00A0' + kel + ',  Kec.\u00A0' + kec, 65);
        } else if (kel) {
            baris2 = limit('Kel.\u00A0' + kel, 65);
        } else if (kec) {
            baris2 = limit('Kec.\u00A0' + kec, 65);
        }

        // Baris 3: kota, provinsi, kodepos
        var baris3Parts = [
            a.city || a.town || a.municipality || a.county,
            a.state,
            a.postcode,
        ].filter(Boolean);
        var baris3 = limit(baris3Parts.join(',  '), 65);

        return { baris1: baris1, baris2: baris2, baris3: baris3 };
    }

    // ─── Reverse geocoding ──────────────────────────────────────────────────

    function reverseGeocode(lat, lon) {
        return fetch(
            'https://nominatim.openstreetmap.org/reverse?' +
            'lat=' + lat + '&lon=' + lon +
            '&format=json&accept-language=id&zoom=18&addressdetails=1',
            { headers: { Accept: 'application/json', 'Accept-Language': 'id' } }
        ).then(function (r) {
            if (!r.ok) throw new Error('Nominatim ' + r.status);
            return r.json();
        }).then(function (d) {
            return parseAlamat(d.address || {});
        });
    }

    // ─── GPS ────────────────────────────────────────────────────────────────

    function getGPS() {
        return new Promise(function (resolve, reject) {
            if (!navigator.geolocation) { reject(new Error('no-geo')); return; }
            navigator.geolocation.getCurrentPosition(
                function (p) { resolve({ lat: p.coords.latitude, lon: p.coords.longitude }); },
                function (e) { reject(e); },
                { enableHighAccuracy: true, timeout: 20000, maximumAge: 0 }
            );
        });
    }

    // ─── Watermark renderer ─────────────────────────────────────────────────

    /**
     * Gambar banner watermark di bagian bawah foto (full-width).
     *
     * @param {HTMLImageElement} img
     * @param {object}  info     — { baris1, baris2, baris3, coords, now }
     * @returns {HTMLCanvasElement}
     */
    function drawWatermark(img, info) {
        var W = img.naturalWidth || img.width || 1280;
        var H = img.naturalHeight || img.height || 960;

        var canvas = document.createElement('canvas');
        canvas.width = W;
        canvas.height = H;
        var ctx = canvas.getContext('2d');
        ctx.drawImage(img, 0, 0, W, H);

        // ── Ukuran font proporsional terhadap lebar foto ────────────────────
        var fs = Math.max(14, Math.round(W / 55));   // font utama
        var fsGps = Math.max(11, Math.round(W / 72));  // font GPS/waktu
        var pad = Math.round(fs * 0.75);
        var lh = Math.round(fs * 1.65);              // line-height
        var lhGps = Math.round(fsGps * 1.5);

        // ── Hitung berapa baris yang terisi ────────────────────────────────
        var addrLines = [info.baris1, info.baris2, info.baris3].filter(Boolean);
        var gpsLine = '\uD83D\uDD52 ' + nowString(info.now) +
            '    \u00B7    GPS: ' + info.coords.lat.toFixed(6) + ',\u00A0' + info.coords.lon.toFixed(6);

        // Tinggi total banner
        var bannerH = pad +
            addrLines.length * lh +
            (addrLines.length > 0 ? Math.round(fs * 0.4) : 0) +
            lhGps +
            pad;

        var bannerY = H - bannerH;

        // ── Background semi-transparan (gradient gelap ke atas) ────────────
        var grad = ctx.createLinearGradient(0, bannerY - Math.round(bannerH * 0.25), 0, H);
        grad.addColorStop(0, 'rgba(0,0,0,0)');
        grad.addColorStop(0.18, 'rgba(0,0,0,0.55)');
        grad.addColorStop(1, 'rgba(0,0,0,0.75)');
        ctx.fillStyle = grad;
        ctx.fillRect(0, bannerY - Math.round(bannerH * 0.25), W, bannerH + Math.round(bannerH * 0.25));

        // ── Teks alamat (3 baris) ──────────────────────────────────────────
        ctx.textBaseline = 'top';
        ctx.shadowColor = 'rgba(0,0,0,0.9)';
        ctx.shadowBlur = 3;
        ctx.shadowOffsetX = 1;
        ctx.shadowOffsetY = 1;

        var marginLeft = Math.round(pad * 1.2);
        var curY = bannerY + pad;

        addrLines.forEach(function (line, i) {
            if (i === 0) {
                // Baris 1: ikon pin + teks, sedikit lebih besar/tebal
                ctx.font = 'bold ' + fs + 'px Arial, sans-serif';
                ctx.fillStyle = '#FFFFFF';
                ctx.fillText('\uD83D\uDCCD\u00A0' + line, marginLeft, curY);
            } else {
                // Baris 2 & 3: indent, sedikit lebih redup
                ctx.font = (Math.max(12, fs - 1)) + 'px Arial, sans-serif';
                ctx.fillStyle = '#E8E8E8';
                // indent supaya rata dengan teks baris 1 (setelah ikon pin)
                var indent = Math.round(fs * 1.55);
                ctx.fillText(line, marginLeft + indent, curY);
            }
            curY += lh;
        });

        // Garis pemisah tipis
        if (addrLines.length > 0) {
            curY += Math.round(fs * 0.3);
            ctx.shadowBlur = 0;
            ctx.strokeStyle = 'rgba(255,255,255,0.2)';
            ctx.lineWidth = 1;
            ctx.beginPath();
            ctx.moveTo(marginLeft, curY);
            ctx.lineTo(W - marginLeft, curY);
            ctx.stroke();
            curY += Math.round(fs * 0.3);
        }

        // ── Baris GPS + waktu ──────────────────────────────────────────────
        ctx.font = fsGps + 'px Arial, sans-serif';
        ctx.fillStyle = '#CCCCCC';
        ctx.shadowColor = 'rgba(0,0,0,0.9)';
        ctx.shadowBlur = 2;
        ctx.fillText(gpsLine, marginLeft, curY);

        return canvas;
    }

    // ─── Canvas → File ──────────────────────────────────────────────────────

    function canvasToFile(canvas, orig) {
        return new Promise(function (resolve) {
            var isPng = orig && orig.type === 'image/png';
            var mime = isPng ? 'image/png' : 'image/jpeg';
            var ext = isPng ? 'png' : 'jpg';
            var base = orig ? orig.name.replace(/\.[^.]+$/, '') : 'foto';
            canvas.toBlob(function (blob) {
                resolve(new File([blob], base + '_gps.' + ext, { type: mime, lastModified: Date.now() }));
            }, mime, 0.93);
        });
    }

    // ─── Ganti file di input ─────────────────────────────────────────────────

    function replaceFile(input, file) {
        try {
            var dt = new DataTransfer();
            dt.items.add(file);
            input.files = dt.files;
        } catch (e) { /* browser lama tidak support DataTransfer */ }
    }

    // ─── Status element ─────────────────────────────────────────────────────

    function getStatus(input) {
        var id = (input.id || ('sbogps_' + Math.random().toString(36).slice(2))) + '__wm_status';
        var el = document.getElementById(id);
        if (!el) {
            el = document.createElement('small');
            el.id = id;
            input.insertAdjacentElement('afterend', el);
        }
        return el;
    }

    function setStatus(el, msg, cls) {
        if (!el) return;
        el.textContent = msg;
        el.className = 'small d-block mt-1 fw-semibold text-' + (cls || 'muted');
    }

    /** Isi field lat/lng form dari GPS foto (data-sbo-foto-gps-lat / data-sbo-foto-gps-lng pada input file). */
    function fillCoordFields(input, coords) {
        if (!coords || !input) return;
        var latId = input.getAttribute('data-sbo-foto-gps-lat');
        var lngId = input.getAttribute('data-sbo-foto-gps-lng');
        var coordStatusId = input.getAttribute('data-sbo-foto-gps-coord-status');

        function setCoord(el, val) {
            if (!el) return;
            el.value = val;
            try { el.dispatchEvent(new Event('input', { bubbles: true })); } catch (e) { }
            try { el.dispatchEvent(new Event('change', { bubbles: true })); } catch (e) { }
        }

        if (latId) setCoord(document.getElementById(latId), coords.lat);
        if (lngId) setCoord(document.getElementById(lngId), coords.lon);

        if (coordStatusId) {
            var coordEl = document.getElementById(coordStatusId);
            if (coordEl) {
                coordEl.textContent = '\u2705 Koordinat GPS diisi dari foto: ' +
                    coords.lat.toFixed(6) + ', ' + coords.lon.toFixed(6);
                coordEl.className = 'text-success small d-block mt-1';
            }
        }
    }

    // ─── Proses utama ────────────────────────────────────────────────────────

    function processFile(input, file) {
        var latId = input.getAttribute('data-sbo-foto-gps-lat');
        var lngId = input.getAttribute('data-sbo-foto-gps-lng');
        var hasManualFields = false;
        if (latId && document.getElementById(latId)) {
            hasManualFields = true;
        }

        // SBO-REQ: Semua Fietur Upload FOTO yg otomatis load GPS Device di desble dulu, kecuali yang memang sudah disediakan kolom entry GPS Manual.
        if (!hasManualFields) {
            return;
        }

        var now = new Date();
        var statusEl = getStatus(input);
        var form = input.closest('form');
        var submitBtn = form ? form.querySelector('[type="submit"]') : null;

        if (submitBtn) submitBtn.disabled = true;
        setStatus(statusEl, '\u23F3 Mengambil lokasi GPS\u2026', 'muted');

        getGPS()
            .then(function (coords) {
                fillCoordFields(input, coords);
                setStatus(statusEl, '\uD83D\uDDFA\uFE0F Mendapatkan nama alamat\u2026', 'muted');
                return reverseGeocode(coords.lat, coords.lon)
                    .then(function (alamat) { return { coords: coords, alamat: alamat }; })
                    .catch(function () { return { coords: coords, alamat: { baris1: '', baris2: '', baris3: '' } }; });
            })
            .catch(function () {
                return { coords: null, alamat: null };
            })
            .then(function (info) {
                if (!info.coords) {
                    // SBO-REQ: Jika GPS tidak tersedia/diaktifkan, jangan rubah file asli dan jangan berikan watermark (mark) apapun.
                    if (submitBtn) submitBtn.disabled = false;

                    var isPerlambatan = input.id === 'fotosituasi_file' || input.getAttribute('data-sbo-foto-gps-lat') === 'trafficstuck_lat';
                    if (isPerlambatan) {
                        setStatus(statusEl, '', 'muted');
                        statusEl.style.display = 'none';
                    } else {
                        setStatus(statusEl, '\u26A0\uFE0F Foto diupload tanpa GPS \u2014 izinkan lokasi atau isi koordinat manual.', 'warning');
                    }
                    return;
                }

                setStatus(statusEl, '\u270F\uFE0F Membuat watermark\u2026', 'muted');

                var img = new Image();
                var burl = URL.createObjectURL(file);

                img.onload = function () {
                    URL.revokeObjectURL(burl);

                    var canvas = drawWatermark(img, {
                        baris1: (info.alamat && info.alamat.baris1) || '',
                        baris2: (info.alamat && info.alamat.baris2) || '',
                        baris3: (info.alamat && info.alamat.baris3) || '',
                        coords: info.coords,
                        now: now,
                    });

                    canvasToFile(canvas, file).then(function (newFile) {
                        replaceFile(input, newFile);

                        // Re-trigger 'change' agar preview module refresh dengan file watermark
                        input._sboWatermarkReplace = true;
                        try { input.dispatchEvent(new Event('change', { bubbles: true })); } catch (e) { }
                        input._sboWatermarkReplace = false;

                        if (submitBtn) submitBtn.disabled = false;

                        // Tampilkan baris ringkas di status
                        var lokStr = '';
                        if (info.coords && info.alamat) {
                            lokStr = info.alamat.baris1 ||
                                (info.coords.lat.toFixed(5) + ', ' + info.coords.lon.toFixed(5));
                        }
                        var wmMsg = '\u2705 Watermark GPS ditambahkan' + (lokStr ? ': ' + lokStr : '') + '.';
                        setStatus(statusEl, wmMsg, 'success');
                    });
                };

                img.onerror = function () {
                    URL.revokeObjectURL(burl);
                    if (submitBtn) submitBtn.disabled = false;
                    setStatus(statusEl, '\u26A0\uFE0F Gagal memproses gambar \u2014 foto diupload tanpa watermark.', 'warning');
                };

                img.src = burl;
            });
    }

    // ─── Bind ───────────────────────────────────────────────────────────────

    function bindInput(input) {
        if (!input || input.type !== 'file') return;
        if (input.dataset.sboGpsWmBound === '1') return;
        if (!input.hasAttribute('data-sbo-foto-gps')) return;
        input.dataset.sboGpsWmBound = '1';

        input.addEventListener('change', function () {
            if (input._sboWatermarkReplace) return;
            var file = input.files && input.files[0];
            if (!file) return;
            if (file.type.indexOf('image/') !== 0) return;
            processFile(input, file);
        });
    }

    function init(scope) {
        // SBO-REQ: watermark gps nonaktifkan dulu semuanya
        return;

        var root = (scope && scope.querySelectorAll) ? scope : document;
        root.querySelectorAll('input[type="file"][data-sbo-foto-gps]').forEach(bindInput);
    }

    global.SBOFotoGpsWatermark = { init: init };

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', function () { init(document); });
    } else {
        init(document);
    }

})(typeof window !== 'undefined' ? window : this);
