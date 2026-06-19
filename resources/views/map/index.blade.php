@extends('layouts.map')

@section('title', 'Peta Monitoring')

@section('content')
    @if(!request()->has('embed'))
    <div class="map-toolbar" id="mapToolbar">
        @auth
            <a href="{{ route('home') }}" class="btn-map-home" title="Ke Dashboard"><i class="fas fa-tachometer-alt"></i> Dashboard</a>
            <form action="{{ route('logout') }}" method="POST" class="d-inline mb-0" style="margin:0;">
                @csrf
                <button type="submit" class="btn-map btn-map-logout" style="background: rgba(20,0,0,0.9); border: 2px solid #dc3545; color: #dc3545;" title="Logout">
                    <i class="fas fa-sign-out-alt"></i> Logout
                </button>
            </form>
        @else
            <a href="{{ route('login') }}" class="btn-map-home" title="Login"><i class="fas fa-sign-in-alt"></i> Login Backoffice</a>
        @endauth
    </div>
    @endif

    <div class="layer-switch">
        <div id="btn-street" class="ls-opt" onclick="setMapType('street')">GELAP</div>
        <div id="btn-hybrid" class="ls-opt" onclick="setMapType('hybrid')">HYBRID</div>
        <div id="btn-clean" class="ls-opt" onclick="setMapType('clean')">CLEAN</div>
    </div>

    <div class="map-legend" id="mapLegend">
        <div class="map-legend-header" onclick="toggleLegend()" style="cursor: pointer; display: flex; align-items: center; justify-content: space-between; font-weight: 700; color: #fff; font-size: 11px; letter-spacing: 0.02em;">
            <span title="Jenis marker"><i class="fas fa-list-ul" style="margin-right: 6px;"></i> Legenda Marker</span>
            <i class="fas fa-chevron-down" id="legendIcon"></i>
        </div>
        <div id="mapLegendContent" style="display: block; margin-top: 10px;">
            <div class="map-legend-title">Terminal &amp; transport</div>
            <div class="map-legend-row"><span class="map-legend-dot" style="background:#16a34a;border-color:#bbf7d0"><i class="fas fa-bus"></i></span> Terminal bus</div>
            <div class="map-legend-row"><span class="map-legend-dot" style="background:#7c3aed;border-color:#ddd6fe"><i class="fas fa-train"></i></span> Stasiun kereta</div>
            <div class="map-legend-row"><span class="map-legend-dot" style="background:#0891b2;border-color:#a5f3fc"><i class="fas fa-plane"></i></span> Bandar udara</div>
            <div class="map-legend-row"><span class="map-legend-dot" style="background:#0369a1;border-color:#7dd3fc"><i class="fas fa-ship"></i></span> Pelabuhan</div>
            <div class="map-legend-divider"></div>
            <div class="map-legend-row"><span class="map-legend-dot" style="background:#2563eb;border-color:#bfdbfe"><i class="fas fa-map-marker-alt"></i></span> Posko</div>
            <div class="map-legend-row"><span class="map-legend-dot" style="background:#ea580c;border-color:#fed7aa"><i class="fas fa-road"></i></span> Gangguan jalan</div>
            <div class="map-legend-row"><span class="map-legend-dot" style="background:#dc2626;border-color:#fecaca"><i class="fas fa-exclamation-triangle"></i></span> Contigency</div>
            <div class="map-legend-row"><span class="map-legend-dot" style="background:#f59e0b;border-color:#fde68a"><i class="fas fa-car-crash"></i></span> Traffic Stuck</div>
        </div>
    </div>

    <script>
        function toggleLegend() {
            var content = document.getElementById('mapLegendContent');
            var icon = document.getElementById('legendIcon');
            if (content.style.display === 'none') {
                content.style.display = 'block';
                icon.classList.remove('fa-chevron-up');
                icon.classList.add('fa-chevron-down');
            } else {
                content.style.display = 'none';
                icon.classList.remove('fa-chevron-down');
                icon.classList.add('fa-chevron-up');
            }
        }
    </script>

    <div id="map"></div>

    @push('scripts')
    <script>
        (function () {
            var userDaerah = @json($userDaerah);
            var userWilayah = @json($userWilayah);
            var bypassTerritory = @json($bypassTerritory);
            var userLevel = @json($userLevel ?? 'nasional');

            var defaultCenter = [ -7.6812744, 110.5036695 ];
            var defaultZoom = 18;
            var hasUserCenter = false;

            @if(isset($userCenter) && $userCenter)
                defaultCenter = [{{ $userCenter[0] }}, {{ $userCenter[1] }}];
                defaultZoom = {{ $userZoom ?? 12 }};
                hasUserCenter = true;
            @endif

            var map = L.map('map', { zoomControl: false, preferCanvas: true }).setView(defaultCenter, defaultZoom);

            var googleHybrid = L.tileLayer('https://{s}.google.com/vt/lyrs=y&x={x}&y={y}&z={z}', {
                maxZoom: 22, maxNativeZoom: 20, subdomains: ['mt0','mt1','mt2','mt3'], attribution: 'Google'
            });
            // CLEAN = Peta 2D Standard (Google Roadmap)
            var mapClean2D = L.tileLayer('https://{s}.google.com/vt/lyrs=m&x={x}&y={y}&z={z}', {
                maxZoom: 22, maxNativeZoom: 20, subdomains: ['mt0','mt1','mt2','mt3'], attribution: 'Google'
            });
            var googleStreets = L.tileLayer('https://{s}.basemaps.cartocdn.com/dark_nolabels/{z}/{x}/{y}{r}.png', {
                attribution: 'CartoDB', subdomains: 'abcd', maxZoom: 20, maxNativeZoom: 20
            });

            var storedMapType = localStorage.getItem('nms_map_type') || 'clean';
            function setMapType(type) {
                localStorage.setItem('nms_map_type', type);
                document.querySelectorAll('.ls-opt').forEach(function(el){ el.classList.remove('active'); });
                if (map.hasLayer(googleHybrid)) map.removeLayer(googleHybrid);
                if (map.hasLayer(mapClean2D)) map.removeLayer(mapClean2D);
                if (map.hasLayer(googleStreets)) map.removeLayer(googleStreets);
                if (type === 'hybrid') { googleHybrid.addTo(map); document.getElementById('btn-hybrid').classList.add('active'); }
                else if (type === 'street') { googleStreets.addTo(map); document.getElementById('btn-street').classList.add('active'); }
                else { mapClean2D.addTo(map); document.getElementById('btn-clean').classList.add('active'); }
            }
            window.setMapType = setMapType;
            setMapType(storedMapType);

            // Hanya gunakan cachedView jika user adalah pusat/nasional (bypassTerritory atau tidak ada daerah/wilayah terikat)
            var cachedView = localStorage.getItem('nms_map_view');
            if (cachedView && (bypassTerritory || (!userDaerah && !userWilayah))) {
                try {
                    var p = JSON.parse(cachedView);
                    map.setView(p.center, p.zoom);
                } catch (e) {}
            }
            map.on('moveend', function() {
                var c = map.getCenter();
                if (c && c.lat !== undefined && c.lng !== undefined) {
                    localStorage.setItem('nms_map_view', JSON.stringify({
                        center: [c.lat, c.lng],
                        zoom: map.getZoom()
                    }));
                }
            });

            L.control.zoom({ position: 'bottomright' }).addTo(map);

            function escHtml(s) {
                return String(s == null ? '' : s).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/"/g, '&quot;');
            }

            /** Tinggi area pin (ikon + jarak + label); anchor = tengah ikon supaya titik koordinat tetap di pusat lingkaran */
            var PIN_WRAP_W = 220;
            var PIN_WRAP_H = 110;
            var PIN_ANCHOR_X = PIN_WRAP_W / 2;
            var PIN_ANCHOR_Y = 19;

            /** @param {string} borderColor - ring luar (kontras antar jenis) */
            function addMarker(obj, iconClass, bgColor, label, borderColor) {
                borderColor = borderColor || '#fff';
                var lat = obj.coordinates[0], lng = obj.coordinates[1];
                var circleStyle = 'width:38px;height:38px;background:' + bgColor + ';border:3px solid ' + borderColor + ';border-radius:50%;display:flex;justify-content:center;align-items:center;color:#fff;box-shadow:0 3px 10px rgba(0,0,0,0.5)';
                var iconHtml =
                    '<div class="map-pin-root" style="width:' + PIN_WRAP_W + 'px">' +
                        '<div class="map-pin-circle" style="' + circleStyle + '"><i class="fas ' + iconClass + '" style="font-size:17px"></i></div>' +
                        '<div class="map-label">' + escHtml(label || obj.name || '') + '</div>' +
                    '</div>';
                var marker = L.marker([lat, lng], {
                    icon: L.divIcon({
                        html: iconHtml,
                        className: 'map-pin-divicon',
                        iconSize: [PIN_WRAP_W, PIN_WRAP_H],
                        iconAnchor: [PIN_ANCHOR_X, PIN_ANCHOR_Y]
                    })
                });
                var tipHtml = '<div style="font-size:13px; line-height:1.5; min-width:160px;">';
                
                if (obj.foto_giat) {
                    var fg = obj.foto_giat.startsWith('/') || obj.foto_giat.startsWith('http') ? obj.foto_giat : '/storage/' + obj.foto_giat;
                    tipHtml += '<div style="margin-bottom:8px; text-align:center;"><img src="' + escHtml(fg) + '" style="max-width:100%; max-height:120px; border-radius:4px; object-fit:cover; border:1px solid #ccc; box-shadow:0 1px 3px rgba(0,0,0,0.2);"></div>';
                }

                tipHtml += '<strong>' + escHtml(obj.name || '') + '</strong><br>';
                
                if (obj.jenis) tipHtml += '<span style="opacity:.9">' + escHtml(obj.jenis) + '</span><br>';
                if (obj.lokasi) tipHtml += escHtml(obj.lokasi) + '<br>';
                if (obj.status) tipHtml += 'Status: ' + escHtml(obj.status) + '<br>';
                
                if (obj.petugas) {
                    tipHtml += '<div style="display:flex; align-items:center; margin-top:6px; padding-top:6px; border-top:1px solid #ccc;">';
                    if (obj.fotoprof) {
                        var fp = obj.fotoprof.startsWith('/') || obj.fotoprof.startsWith('http') ? obj.fotoprof : '/storage/' + obj.fotoprof;
                        tipHtml += '<img src="' + escHtml(fp) + '" style="width:24px; height:24px; border-radius:50%; margin-right:6px; object-fit:cover;"> ';
                    } else {
                        tipHtml += '<i class="fas fa-user-circle" style="color:#555; font-size:24px; margin-right:6px;"></i> ';
                    }
                    tipHtml += '<strong>' + escHtml(obj.petugas) + '</strong></div>';
                }
                
                tipHtml += '<div style="margin-top:8px;">' +
                           '<a href="https://www.google.com/maps/dir/?api=1&destination=' + lat + ',' + lng + '" target="_blank" class="btn btn-sm" style="background:#2563eb; color:#fff; padding:4px 8px; font-size:11px; text-decoration:none; border-radius:4px; display:inline-block;"><i class="fas fa-directions"></i> Buka Rute Peta</a>' +
                           '</div></div>';

                marker.bindPopup(tipHtml, { minWidth: 150 });
                marker.bindTooltip(escHtml(obj.name || ''), { direction: 'top', offset: [0, -32] });
                
                layerGroup.addLayer(marker);
                return [lat, lng];
            }

            /** Bedakan stasiun, terminal bus, bandara, pelabuhan dari kolom jenis (sama dengan t_jenisterminal.namejenisterminal) */
            function getTerminalPinStyle(jenis) {
                var j = String(jenis || '').toLowerCase();
                if (j.indexOf('stasiun') !== -1 || j.indexOf('kereta') !== -1) {
                    return { icon: 'fa-train', color: '#7c3aed', border: '#ddd6fe', short: 'Stasiun' };
                }
                if (j.indexOf('bandar') !== -1 || j.indexOf('udara') !== -1) {
                    return { icon: 'fa-plane', color: '#0891b2', border: '#a5f3fc', short: 'Bandara' };
                }
                if (j.indexOf('pelabuhan') !== -1 || j.indexOf('asdp') !== -1) {
                    return { icon: 'fa-ship', color: '#0369a1', border: '#7dd3fc', short: 'Pelabuhan' };
                }
                return { icon: 'fa-bus', color: '#16a34a', border: '#bbf7d0', short: 'Terminal' };
            }

            var layerGroup = L.layerGroup().addTo(map);
            var poskoData = @json($markers);
            var terminalsData = @json($terminals);
            var jalanGangguanData = @json($jalanGangguan);
            var contigencyData = @json($contigency);
            var trafficstuckData = @json($trafficstuck);
            var allBounds = [];

            poskoData.forEach(function(obj) {
                var c = addMarker(obj, 'fa-map-marker-alt', '#2563eb', obj.name || 'Posko', '#bfdbfe');
                if (c) allBounds.push(c);
            });
            terminalsData.forEach(function(obj) {
                var st = getTerminalPinStyle(obj.jenis);
                var lbl = (obj.name || '') + (obj.jenis ? ' · ' + st.short : '');
                var c = addMarker(obj, st.icon, st.color, lbl, st.border);
                if (c) allBounds.push(c);
            });
            jalanGangguanData.forEach(function(obj) {
                var c = addMarker(obj, 'fa-road', '#ea580c', obj.name || 'Jalan Gangguan', '#fed7aa');
                if (c) allBounds.push(c);
            });
            contigencyData.forEach(function(obj) {
                var c = addMarker(obj, 'fa-exclamation-triangle', '#dc2626', obj.name || 'Contigency', '#fecaca');
                if (c) allBounds.push(c);
            });
            trafficstuckData.forEach(function(obj) {
                var c = addMarker(obj, 'fa-car-crash', '#f59e0b', obj.name || 'Traffic Stuck', '#fde68a');
                if (c) allBounds.push(c);
            });

            if (allBounds.length > 0 && !cachedView && !hasUserCenter) {
                map.fitBounds(allBounds, { padding: [50, 50], maxZoom: 18 });
            }

            // Load GeoJSON Wilayah berdasarkan cakupan User
            (function() {
                var targetGeoJsonUrl = '/geojson/Provinsi/38%20Provinsi%20Indonesia%20-%20Provinsi.json';
                var isKabupaten = false;

                function matchTerritory(geoName, userName) {
                    if (!geoName || !userName) return false;

                    var gClean = geoName.toLowerCase();
                    var uClean = userName.toLowerCase();

                    // Bersihkan prefix/suffix Kepolisian & Administratif umum
                    function clean(str) {
                        return str.replace(/^(polda|polresta|polres\s+metro|polres|kabupaten|kab\.|kota|provinsi|prov\.)\s+/i, '')
                                  .replace(/\s+(polda|polresta|polres\s+metro|polres|kabupaten|kab\.|kota|provinsi|prov\.)$/i, '')
                                  .trim();
                    }

                    var gCleaned = clean(gClean);
                    var uCleaned = clean(uClean);

                    // Cek apakah salah satunya adalah "kota" atau "polresta" (untuk membedakan Kota Bandung vs Kabupaten Bandung)
                    var gIsKota = gClean.includes('kota') || gClean.includes('polresta');
                    var uIsKota = uClean.includes('kota') || uClean.includes('polresta');

                    if (gIsKota !== uIsKota) {
                        if (gCleaned === uCleaned) {
                            return false;
                        }
                    }

                    return gCleaned === uCleaned;
                }

                if (!bypassTerritory) {
                    if (userLevel === 'kab_kota' || userLevel === 'provinsi') {
                        targetGeoJsonUrl = '/geojson/Kabupaten/38%20Provinsi%20Indonesia%20-%20Kabupaten.json';
                        isKabupaten = true;
                    }
                }

                fetch(targetGeoJsonUrl)
                    .then(function(response) {
                        if (response.ok) return response.json();
                        throw new Error('GeoJSON file not found at ' + targetGeoJsonUrl);
                    })
                    .then(function(data) {
                        var geoJsonLayer = L.geoJSON(data, {
                            filter: function(feature) {
                                if (bypassTerritory) return true;
                                if (!userDaerah && !userWilayah) return true; // Nasional

                                if (userLevel === 'kab_kota') {
                                    var wadmkk = feature.properties.WADMKK || '';
                                    return matchTerritory(wadmkk, userWilayah);
                                } else if (userLevel === 'provinsi') {
                                    var wadmpr = feature.properties.WADMPR || '';
                                    return matchTerritory(wadmpr, userDaerah);
                                }
                                return true;
                            },
                            style: function(feature) {
                                return {
                                    color: "#38bdf8", // Warna garis batas (border)
                                    weight: 1.5,
                                    opacity: 0.3, // Membuat garis batas lebih transparan
                                    fillColor: "#0ea5e9", // Warna isi
                                    fillOpacity: 0.01, // Membuat area dalam poligon sangat transparan
                                    dashArray: '4' // Garis putus-putus
                                };
                            },
                            onEachFeature: function(feature, layer) {
                                var name = '';
                                if (isKabupaten) {
                                    name = feature.properties.WADMKK || '';
                                } else {
                                    name = feature.properties.PROVINSI || feature.properties.Propinsi || feature.properties.PROPINSI || feature.properties.name || feature.properties.NAME_1 || '';
                                }

                                if (name) {
                                    layer.bindTooltip(name, {
                                        sticky: true,
                                        direction: 'auto',
                                        className: 'hud-style'
                                    });
                                }
                            }
                        }).addTo(map);

                        // Fit bounds ke wilayah khusus jika user terbatasi wilayah
                        if (!bypassTerritory && (userDaerah || userWilayah)) {
                            var bounds = geoJsonLayer.getBounds();
                            if (bounds.isValid()) {
                                map.fitBounds(bounds, { padding: [30, 30] });
                            }
                        }
                    })
                    .catch(function(err) {
                        console.log('GeoJSON warning:', err.message);
                    });
            })();

        })();
    </script>
    @endpush
@endsection
