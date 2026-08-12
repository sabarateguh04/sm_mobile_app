/**
 * SBO Form Map Picker
 * Automatically adds a Leaflet map and search bar to any forms containing latitude/longitude inputs.
 */
(function () {
    'use strict';

    function loadLeaflet(callback) {
        if (window.L) {
            callback();
            return;
        }

        // Load Leaflet CSS
        var link = document.createElement('link');
        link.rel = 'stylesheet';
        link.href = 'https://unpkg.com/leaflet@1.9.4/dist/leaflet.css';
        link.crossOrigin = '';
        document.head.appendChild(link);

        // Load Leaflet JS
        var script = document.createElement('script');
        script.src = 'https://unpkg.com/leaflet@1.9.4/dist/leaflet.js';
        script.crossOrigin = '';
        script.onload = function () {
            if (window.L) {
                delete L.Icon.Default.prototype._getIconUrl;
                L.Icon.Default.mergeOptions({
                    iconRetinaUrl: 'https://unpkg.com/leaflet@1.9.4/dist/images/marker-icon-2x.png',
                    iconUrl: 'https://unpkg.com/leaflet@1.9.4/dist/images/marker-icon.png',
                    shadowUrl: 'https://unpkg.com/leaflet@1.9.4/dist/images/marker-shadow.png',
                });
            }
            callback();
        };
        document.head.appendChild(script);
    }

    function formatCoord(n) {
        var num = Number(n);
        if (!isFinite(num)) return '';
        var s = num.toFixed(8).replace(/\.?0+$/, '');
        return s === '-0' ? '0' : s;
    }

    function searchLocation(query, map, marker, latEl, lngEl) {
        if (!query) return;

        var url = 'https://nominatim.openstreetmap.org/search?format=json&limit=1&q=' + encodeURIComponent(query);

        fetch(url)
            .then(function (r) { return r.json(); })
            .then(function (data) {
                if (data && data.length > 0) {
                    var lat = parseFloat(data[0].lat);
                    var lon = parseFloat(data[0].lon);

                    latEl.value = formatCoord(lat);
                    lngEl.value = formatCoord(lon);

                    ['input', 'change'].forEach(function (type) {
                        latEl.dispatchEvent(new Event(type, { bubbles: true }));
                        lngEl.dispatchEvent(new Event(type, { bubbles: true }));
                    });

                    var newLatLng = new L.LatLng(lat, lon);
                    marker.setLatLng(newLatLng);
                    map.setView(newLatLng, 15);
                } else {
                    alert('Lokasi tidak ditemukan.');
                }
            })
            .catch(function (err) {
                console.error('Error searching location:', err);
                alert('Gagal melakukan pencarian lokasi.');
            });
    }

    function initMapForButton(btn) {
        var latId = btn.getAttribute('data-sbo-gps-lat');
        var lngId = btn.getAttribute('data-sbo-gps-lng');

        var latEl = latId ? document.getElementById(latId) : null;
        var lngEl = lngId ? document.getElementById(lngId) : null;

        if (!latEl || !lngEl) return;

        // Find the grid container that contains the lat/lng/gps button
        var container = btn.closest('.grid') || btn.parentElement.parentElement;
        if (!container) return;

        // Check if map is already initialized here
        if (container.nextElementSibling && container.nextElementSibling.classList.contains('sbo-form-map-wrapper')) {
            return;
        }

        // Create Map Wrapper
        var wrapper = document.createElement('div');
        wrapper.className = 'sm:col-span-2 space-y-2 mt-3 sbo-form-map-wrapper';
        wrapper.innerHTML =
            '<label class="block text-[10px] font-bold text-slate-500 uppercase tracking-wide mb-0.5">Pencarian Lokasi & Map Picker</label>' +
            '<div class="relative">' +
            '<input type="text" placeholder="Cari lokasi atau alamat (cth: Simpang A. Yani Bandung)..." class="w-full bg-slate-50 border border-slate-200 rounded-lg pl-3 pr-10 py-2 text-[13px] text-slate-800 placeholder:text-slate-400 focus:border-primary focus:bg-white focus:ring-0 outline-none transition-all sbo-map-search-input">' +
            '<button type="button" class="absolute right-2.5 top-1/2 -translate-y-1/2 text-slate-400 hover:text-primary transition-colors border-0 bg-transparent flex items-center justify-center sbo-map-search-btn">' +
            '<span class="material-symbols-outlined text-[18px]">search</span>' +
            '</button>' +
            '</div>' +
            '<div class="sbo-form-map" style="height: 280px; width: 100%; border-radius: 8px; border: 1px solid #e2e8f0; z-index: 10; position: relative;"></div>' +
            '<p class="text-[10px] text-slate-400 m-0">Seret/drag penanda (marker) merah di peta untuk menyesuaikan titik koordinat secara presisi.</p>';

        container.parentNode.insertBefore(wrapper, container.nextSibling);

        var mapEl = wrapper.querySelector('.sbo-form-map');
        var searchInput = wrapper.querySelector('.sbo-map-search-input');
        var searchBtn = wrapper.querySelector('.sbo-map-search-btn');

        // Determine Initial Coordinates
        var initLat = parseFloat(latEl.value);
        var initLng = parseFloat(lngEl.value);
        var hasValidCoords = !isNaN(initLat) && !isNaN(initLng);

        var defaultCenter = hasValidCoords ? [initLat, initLng] : [-2.548926, 118.0148634]; // Indonesia center
        var defaultZoom = hasValidCoords ? 15 : 5;

        // Initialize Map
        var map = L.map(mapEl, {
            center: defaultCenter,
            zoom: defaultZoom,
            zoomControl: true
        });

        L.tileLayer('https://{s}.google.com/vt/lyrs=m&x={x}&y={y}&z={z}', {
            maxZoom: 22,
            subdomains: ['mt0', 'mt1', 'mt2', 'mt3'],
            attribution: 'Google'
        }).addTo(map);

        // Add Draggable Marker
        var markerLatLng = hasValidCoords ? [initLat, initLng] : [0, 0];
        var marker = L.marker(markerLatLng, {
            draggable: true
        });

        if (hasValidCoords) {
            marker.addTo(map);
        }

        // Sync Marker -> Inputs
        marker.on('dragend', function () {
            var position = marker.getLatLng();
            latEl.value = formatCoord(position.lat);
            lngEl.value = formatCoord(position.lng);

            ['input', 'change'].forEach(function (type) {
                latEl.dispatchEvent(new Event(type, { bubbles: true }));
                lngEl.dispatchEvent(new Event(type, { bubbles: true }));
            });
        });

        // Sync Inputs -> Marker & Map
        function syncInputsToMap() {
            var lat = parseFloat(latEl.value);
            var lng = parseFloat(lngEl.value);
            if (!isNaN(lat) && !isNaN(lng)) {
                var newLatLng = new L.LatLng(lat, lng);
                marker.setLatLng(newLatLng);
                if (!map.hasLayer(marker)) {
                    marker.addTo(map);
                }
                map.setView(newLatLng, 15);
            } else {
                if (map.hasLayer(marker)) {
                    map.removeLayer(marker);
                }
            }
        }

        latEl.addEventListener('input', syncInputsToMap);
        lngEl.addEventListener('input', syncInputsToMap);
        latEl.addEventListener('change', syncInputsToMap);
        lngEl.addEventListener('change', syncInputsToMap);

        // Search Handlers
        searchBtn.addEventListener('click', function () {
            searchLocation(searchInput.value, map, marker, latEl, lngEl);
        });

        searchInput.addEventListener('keydown', function (e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                searchLocation(searchInput.value, map, marker, latEl, lngEl);
            }
        });
    }

    function init() {
        var gpsButtons = document.querySelectorAll('[data-sbo-gps-lat]');
        if (gpsButtons.length > 0) {
            loadLeaflet(function () {
                gpsButtons.forEach(initMapForButton);
            });
        }
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
})();
