/**
 * OpenStreetMap (Leaflet) + shim google.maps agar layer SatuPeta tetap jalan tanpa Google API key.
 */
(function () {
    const BASEMAPS = {
        default: {
            url: 'https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png',
            options: { maxZoom: 19, attribution: '&copy; OpenStreetMap' },
        },
        silver: {
            url: 'https://{s}.basemaps.cartocdn.com/light_all/{z}/{x}/{y}{r}.png',
            options: { maxZoom: 20, attribution: '&copy; CARTO' },
        },
        night: {
            url: 'https://{s}.basemaps.cartocdn.com/dark_all/{z}/{x}/{y}{r}.png',
            options: { maxZoom: 20, attribution: '&copy; CARTO' },
        },
        retro: {
            url: 'https://{s}.basemaps.cartocdn.com/rastertiles/voyager/{z}/{x}/{y}{r}.png',
            options: { maxZoom: 20, attribution: '&copy; CARTO' },
        },
    };

    let activeBasemapKey = 'night';
    let activeTileLayer = null;
    let heatmapOverlay = null;

    function leafletMapFrom(mapObj) {
        if (!mapObj) return null;
        return mapObj.__leaflet || mapObj;
    }

    function latLngFrom(position) {
        if (!position) return null;
        if (typeof position.lat === 'function') {
            return [position.lat(), position.lng()];
        }
        return [parseFloat(position.lat), parseFloat(position.lng)];
    }

    function applyBasemap(key, leafletMap) {
        const cfg = BASEMAPS[key] || BASEMAPS.night;
        if (activeTileLayer) {
            leafletMap.removeLayer(activeTileLayer);
        }
        activeTileLayer = L.tileLayer(cfg.url, cfg.options).addTo(leafletMap);
        activeBasemapKey = key;
    }

    window.setOsmBasemap = function (key) {
        const lm = window.mymap || (window.map && window.map.__leaflet);
        if (lm) applyBasemap(key || 'night', lm);
    };

    window.MarkerClusterer = function (mapObj, markers, options) {
        const lm = leafletMapFrom(mapObj);
        const group = L.markerClusterGroup({
            showCoverageOnHover: false,
            maxClusterRadius: 50,
        });

        (markers || []).forEach(function (marker) {
            if (marker && marker.__leaflet) {
                group.addLayer(marker.__leaflet);
            }
        });

        if (lm) {
            lm.addLayer(group);
        }

        return {
            __leaflet: group,
            clearMarkers: function () {
                group.clearLayers();
                if (lm) {
                    lm.removeLayer(group);
                }
            },
        };
    };

    function parseKmlCoordinates(text) {
        const rings = [];
        const parts = text.trim().split(/\s+/);
        const ring = [];
        parts.forEach(function (part) {
            const bits = part.split(',');
            if (bits.length >= 2) {
                ring.push([parseFloat(bits[1]), parseFloat(bits[0])]);
            }
        });
        if (ring.length) rings.push(ring);
        return rings;
    }

    function loadKmlToMap(url, mapObj) {
        const lm = leafletMapFrom(mapObj);
        if (!lm || !url) return Promise.resolve(null);

        return fetch(url)
            .then(function (res) { return res.text(); })
            .then(function (xml) {
                const doc = new DOMParser().parseFromString(xml, 'text/xml');
                const layers = [];
                doc.querySelectorAll('Polygon coordinates, LineString coordinates').forEach(function (node) {
                    const rings = parseKmlCoordinates(node.textContent || '');
                    rings.forEach(function (ring) {
                        const layer = L.polyline(ring, { color: '#e74c3c', weight: 3, opacity: 0.85 });
                        layer.addTo(lm);
                        layers.push(layer);
                    });
                });
                doc.querySelectorAll('Point coordinates').forEach(function (node) {
                    const rings = parseKmlCoordinates(node.textContent || '');
                    if (rings[0] && rings[0][0]) {
                        const layer = L.marker(rings[0][0]);
                        layer.addTo(lm);
                        layers.push(layer);
                    }
                });
                return layers;
            })
            .catch(function (err) {
                console.warn('KML gagal dimuat:', url, err);
                return [];
            });
    }

    function ensureGoogleShim() {
        window.google = window.google || {};
        const gm = google.maps = google.maps || {};

        gm.Size = function (w, h) {
            return { width: w, height: h };
        };

        gm.Animation = { DROP: 'drop' };
        gm.TravelMode = { DRIVING: 'DRIVING' };
        gm.DirectionsStatus = { OK: 'OK', ZERO_RESULTS: 'ZERO_RESULTS' };

        gm.LatLng = function (lat, lng) {
            const la = parseFloat(lat);
            const ln = parseFloat(lng);
            return {
                lat: function () { return la; },
                lng: function () { return ln; },
            };
        };

        function toGoogleLikeEvent(e) {
            if (!e) return e;
            if (e.latlng) {
                return {
                    latLng: gm.LatLng(e.latlng.lat, e.latlng.lng),
                    domEvent: e.originalEvent || null,
                };
            }
            return e;
        }

        gm.event = {
            addListener: function (target, eventName, handler) {
                if (!target) return;
                if (target.__leaflet) {
                    const ev = eventName === 'click' ? 'click' : eventName;
                    target.__leaflet.on(ev, function (e) {
                        handler.call(target, toGoogleLikeEvent(e));
                    });
                    return;
                }
                if (typeof target.addListener === 'function') {
                    target.addListener(eventName, handler);
                }
            },
        };

        gm.Marker = function (opts) {
            opts = opts || {};
            const ll = latLngFrom(opts.position || {});
            const options = {};

            if (opts.icon && opts.icon.url) {
                const size = opts.icon.scaledSize || { width: 32, height: 32 };
                options.icon = L.icon({
                    iconUrl: opts.icon.url,
                    iconSize: [size.width, size.height],
                    iconAnchor: [size.width / 2, size.height / 2],
                });
            }

            const leafletMarker = L.marker(ll, options);
            if (opts.map) {
                leafletMarker.addTo(leafletMapFrom(opts.map));
            }

            return {
                __leaflet: leafletMarker,
                setMap: function (m) {
                    if (!m) leafletMarker.remove();
                    else leafletMarker.addTo(leafletMapFrom(m));
                },
                getPosition: function () {
                    const p = leafletMarker.getLatLng();
                    return gm.LatLng(p.lat, p.lng);
                },
                addListener: function (eventName, handler) {
                    leafletMarker.on(eventName, handler);
                },
            };
        };

        gm.Circle = function (opts) {
            opts = opts || {};
            const center = latLngFrom(opts.center || {});
            const circle = L.circle(center, {
                radius: opts.radius || 100,
                color: opts.strokeColor || '#3388ff',
                weight: opts.strokeWeight || 1,
                fillColor: opts.fillColor || '#3388ff',
                fillOpacity: opts.fillOpacity != null ? opts.fillOpacity : 0.2,
            });

            if (opts.map) {
                circle.addTo(leafletMapFrom(opts.map));
            }

            return {
                __leaflet: circle,
                setMap: function (m) {
                    if (!m) circle.remove();
                    else circle.addTo(leafletMapFrom(m));
                },
            };
        };

        gm.Polyline = function (opts) {
            opts = opts || {};
            const path = (opts.path || []).map(function (p) {
                return latLngFrom(p);
            });
            const line = L.polyline(path, {
                color: opts.strokeColor || '#3388ff',
                weight: opts.strokeWeight || 3,
                opacity: opts.strokeOpacity != null ? opts.strokeOpacity : 1,
            });

            if (opts.map) {
                line.addTo(leafletMapFrom(opts.map));
            }

            return {
                __leaflet: line,
                setMap: function (m) {
                    if (!m) line.remove();
                    else line.addTo(leafletMapFrom(m));
                },
            };
        };

        gm.Polygon = function (opts) {
            opts = opts || {};
            let latlngs = [];

            if (Array.isArray(opts.paths)) {
                if (opts.paths.length && opts.paths[0] && typeof opts.paths[0].lat !== 'undefined') {
                    latlngs = opts.paths.map(function (p) { return latLngFrom(p); });
                } else if (Array.isArray(opts.paths[0])) {
                    latlngs = opts.paths.map(function (ring) {
                        return ring.map(function (p) { return latLngFrom(p); });
                    });
                }
            }

            const poly = L.polygon(latlngs, {
                color: opts.strokeColor || '#FF0000',
                weight: opts.strokeWeight || 1,
                opacity: opts.strokeOpacity != null ? opts.strokeOpacity : 0.8,
                fillColor: opts.fillColor || '#FF0000',
                fillOpacity: opts.fillOpacity != null ? opts.fillOpacity : 0.15,
            });

            if (opts.map) {
                poly.addTo(leafletMapFrom(opts.map));
            }

            const adapter = {
                __leaflet: poly,
                name: opts.name,
                filters: opts.filters,
                setMap: function (m) {
                    if (!m) poly.remove();
                    else poly.addTo(leafletMapFrom(m));
                },
                addListener: function (eventName, handler) {
                    poly.on(eventName, function (e) {
                        handler.call(adapter, toGoogleLikeEvent(e));
                    });
                },
            };

            return adapter;
        };

        gm.InfoWindow = function () {
            let html = '';
            let openMap = null;
            let popup = null;

            return {
                setContent: function (content) {
                    html = content;
                },
                setPosition: function (position) {
                    if (!position) return;
                    if (typeof position.lat === 'function') {
                        popup = L.popup().setLatLng([position.lat(), position.lng()]);
                    } else if (typeof position.lat !== 'undefined' && typeof position.lng !== 'undefined') {
                        popup = L.popup().setLatLng([position.lat, position.lng]);
                    }
                },
                open: function (mapObj, anchor) {
                    const lm = leafletMapFrom(mapObj);
                    openMap = mapObj;
                    let latLng;
                    if (anchor && anchor.__leaflet) {
                        latLng = anchor.__leaflet.getLatLng();
                    } else if (anchor && anchor.getPosition) {
                        const p = anchor.getPosition();
                        latLng = L.latLng(p.lat(), p.lng());
                    } else if (popup && popup.getLatLng()) {
                        latLng = popup.getLatLng();
                    }
                    if (!latLng || !lm) return;
                    if (!popup) {
                        popup = L.popup();
                    }
                    popup.setLatLng(latLng).setContent(html).openOn(lm);
                },
                close: function () {
                    if (popup) popup.remove();
                    popup = null;
                    openMap = null;
                },
                getMap: function () {
                    return openMap;
                },
            };
        };

        gm.KmlLayer = function (opts) {
            const layers = [];
            const mapRef = opts.map || null;

            loadKmlToMap(opts.url, mapRef).then(function (loaded) {
                layers.push.apply(layers, loaded || []);
            });

            return {
                __layers: layers,
                setMap: function (m) {
                    if (!m) {
                        layers.forEach(function (l) { l.remove(); });
                        layers.length = 0;
                    }
                },
            };
        };

        gm.TrafficLayer = function () {
            return { setMap: function () {} };
        };

        gm.OverlayView = function () {
            return {
                setMap: function () {},
                getPanes: function () {
                    return { overlayMouseTarget: document.getElementById('mapid') };
                },
                getProjection: function () {
                    return {
                        fromLatLngToDivPixel: function () {
                            return { x: 0, y: 0 };
                        },
                    };
                },
            };
        };

        gm.DirectionsService = function () {
            return {
                route: function (request, callback) {
                    const pts = [];

                    function pushPoint(pos) {
                        if (!pos) return;
                        const p = typeof pos.lat === 'function' ? pos : gm.LatLng(pos.lat, pos.lng);
                        pts.push(p.lng() + ',' + p.lat());
                    }

                    pushPoint(request.origin);
                    (request.waypoints || []).forEach(function (wp) {
                        pushPoint(wp.location);
                    });
                    pushPoint(request.destination);

                    if (pts.length < 2) {
                        callback(null, gm.DirectionsStatus.ZERO_RESULTS);
                        return;
                    }

                    fetch('https://router.project-osrm.org/route/v1/driving/' + pts.join(';') + '?overview=full&geometries=geojson')
                        .then(function (r) { return r.json(); })
                        .then(function (data) {
                            if (data.code !== 'Ok' || !data.routes || !data.routes[0]) {
                                callback(null, gm.DirectionsStatus.ZERO_RESULTS);
                                return;
                            }
                            const coords = data.routes[0].geometry.coordinates.map(function (c) {
                                return gm.LatLng(c[1], c[0]);
                            });
                            callback({ routes: [{ overview_path: coords }] }, gm.DirectionsStatus.OK);
                        })
                        .catch(function () {
                            callback(null, gm.DirectionsStatus.ZERO_RESULTS);
                        });
                },
            };
        };

        gm.DirectionsRenderer = function (opts) {
            opts = opts || {};
            let routeLine = null;
            let boundMap = null;
            let panelEl = null;

            return {
                setMap: function (m) {
                    boundMap = m;
                    if (!m && routeLine) {
                        routeLine.remove();
                        routeLine = null;
                    }
                },
                setPanel: function (el) {
                    panelEl = el;
                },
                setDirections: function (result) {
                    if (!result || !result.routes || !result.routes[0]) return;
                    const lm = leafletMapFrom(boundMap);
                    if (!lm) return;

                    if (routeLine) routeLine.remove();

                    const path = result.routes[0].overview_path.map(function (p) {
                        return [p.lat(), p.lng()];
                    });

                    routeLine = L.polyline(path, {
                        color: (opts.polylineOptions && opts.polylineOptions.strokeColor) || 'red',
                        weight: (opts.polylineOptions && opts.polylineOptions.strokeWeight) || 6,
                        opacity: 0.9,
                    }).addTo(lm);

                    if (panelEl) {
                        panelEl.innerHTML = '<div class="p-2 small">Rute berhasil dimuat (OSRM).</div>';
                    }
                },
            };
        };

        gm.visualization = gm.visualization || {};
        gm.visualization.HeatmapLayer = function (opts) {
            const cfg = {
                radius: (opts && opts.radius) || 25,
                maxOpacity: 0.8,
                scaleRadius: true,
                useLocalExtrema: true,
                latField: 'lat',
                lngField: 'lng',
                valueField: 'weight',
            };

            return {
                setMap: function (m) {
                    const lm = leafletMapFrom(m);
                    if (!lm) {
                        if (heatmapOverlay && lm) {
                            lm.removeLayer(heatmapOverlay);
                        }
                        heatmapOverlay = null;
                        return;
                    }

                    if (typeof HeatmapOverlay === 'undefined') {
                        console.warn('HeatmapOverlay belum dimuat.');
                        return;
                    }

                    if (!heatmapOverlay) {
                        heatmapOverlay = new HeatmapOverlay(cfg);
                        heatmapOverlay.addTo(lm);
                    }

                    const points = (opts && opts.data ? opts.data : []).map(function (item) {
                        const loc = item.location || item;
                        const lat = typeof loc.lat === 'function' ? loc.lat() : loc.lat;
                        const lng = typeof loc.lng === 'function' ? loc.lng() : loc.lng;
                        return { lat: parseFloat(lat), lng: parseFloat(lng), weight: item.weight || 1 };
                    });

                    heatmapOverlay.setData({ data: points, max: Math.max.apply(null, points.map(function (p) { return p.weight; }).concat([1])) });
                },
                setData: function (data) {
                    this.setMap(window.map);
                },
            };
        };
    }

    window.initOsmMap = function () {
        ensureGoogleShim();

        const zoom = (user_lat == -1.3292304 && user_lng == 117.6913772) ? 5 : 10;
        if (window.mymap && typeof window.mymap.remove === 'function') {
            try { window.mymap.remove(); } catch (e) {}
            window.mymap = null;
        }

        const leafletMap = L.map('mapid', {
            zoomControl: true,
            attributionControl: true,
        }).setView([user_lat, user_lng], zoom);

        applyBasemap(activeBasemapKey, leafletMap);

        const mapAdapter = {
            __leaflet: leafletMap,
            styles: [],
            setZoom: function (z) {
                leafletMap.setZoom(z);
            },
            setCenter: function (c) {
                leafletMap.setView([c.lat, c.lng], leafletMap.getZoom());
            },
            setOptions: function (opts) {
                if (!opts) return;
                if (opts.center) {
                    this.setCenter(opts.center);
                }
                if (opts.zoom) {
                    this.setZoom(opts.zoom);
                }
            },
        };

        window.map = mapAdapter;
        window.mymap = leafletMap;

        const basemapSelect = document.getElementById('basemap');
        if (basemapSelect && basemapSelect.value) {
            applyBasemap(basemapSelect.value, leafletMap);
        }

        // Marker center untuk memastikan peta terlihat saat mode lokal/demo.
        L.circleMarker([user_lat, user_lng], {
            radius: 4,
            color: '#00d4ff',
            fillColor: '#00d4ff',
            fillOpacity: 0.8,
        }).addTo(leafletMap);

        console.info('SatuPeta: peta OpenStreetMap (Leaflet) aktif.');
    };

    // Hard fallback: jika initMap tidak terpanggil oleh script lain, paksa render OSM.
    window.addEventListener('load', function () {
        if (window.SATUPETA_MAP_PROVIDER !== 'openstreetmap') return;
        if (!document.getElementById('mapid')) return;
        if (window.mymap) return;
        if (typeof window.initOsmMap === 'function') {
            window.initOsmMap();
        }
    });
})();
