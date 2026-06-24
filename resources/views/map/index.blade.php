@extends('layouts.map')

@section('title', 'Peta Monitoring')

@push('styles')
<style>
    /* Styling khusus Leaflet Marker Clusters dengan Ikon Kategori & Badge Jumlah */
    .marker-cluster-custom {
        background: transparent !important;
        border: none !important;
    }
    .custom-cluster-icon {
        position: relative;
        width: 42px;
        height: 42px;
        display: flex;
        align-items: center;
        justify-content: center;
        background: transparent !important;
        border: none !important;
        box-shadow: none !important;
        transition: all 0.2s ease;
    }
    .custom-cluster-icon:hover {
        transform: scale(1.15);
    }
    .custom-cluster-icon img {
        width: 32px;
        height: 32px;
        object-fit: contain;
        filter: drop-shadow(0 3px 6px rgba(0,0,0,0.6));
    }
    .custom-cluster-icon .cluster-badge {
        position: absolute;
        top: -6px;
        right: -6px;
        background: #ef4444;
        color: #fff;
        font-size: 10px;
        font-weight: 800;
        padding: 2px 6px;
        border-radius: 10px;
        border: 1px solid rgba(255, 255, 255, 0.4);
        box-shadow: 0 2px 6px rgba(0, 0, 0, 0.4);
        white-space: nowrap;
    }

    .map-legend {
        position: fixed !important;
        bottom: 25px !important;
        left: 50% !important;
        transform: translateX(-50%) !important;
        max-width: 95vw !important;
        width: max-content !important;
        background: rgba(15, 23, 42, 0.85) !important;
        backdrop-filter: blur(8px);
        border: 1px solid rgba(255, 255, 255, 0.2) !important;
        border-radius: 16px !important;
        padding: 16px 14px 10px 14px !important;
        z-index: 1000;
        box-shadow: 0 10px 25px rgba(0,0,0,0.5);
        transition: all 0.3s ease !important;
    }
    .map-legend.collapsed-card {
        padding: 0 14px 0 14px !important;
        background: transparent !important;
        border-color: transparent !important;
        box-shadow: none !important;
        pointer-events: none;
    }
    
    .map-legend-border-title {
        position: absolute;
        top: -14px;
        left: 50%;
        transform: translateX(-50%);
        background: rgba(15, 23, 42, 0.95);
        border: 1px solid rgba(255, 255, 255, 0.2);
        border-radius: 12px;
        padding: 4px 14px;
        color: #fff;
        font-size: 11px;
        font-weight: 700;
        cursor: pointer;
        display: flex;
        align-items: center;
        gap: 6px;
        box-shadow: 0 4px 12px rgba(0,0,0,0.4);
        white-space: nowrap;
        transition: all 0.2s ease;
        pointer-events: auto;
    }
    .map-legend-border-title:hover {
        background: rgba(30, 41, 59, 1);
        transform: translate(-50%, -2px);
    }
    
    .map-legend-title, .map-legend-divider {
        display: none !important;
    }
    
    #mapLegendContent {
        display: flex !important;
        flex-wrap: wrap;
        gap: 8px;
        justify-content: center;
        transition: opacity 0.2s ease;
        opacity: 1;
    }
    #mapLegendContent.collapsed {
        display: none !important;
        opacity: 0;
    }
    .legend-group {
        display: contents;
    }
    .map-legend-row {
        display: flex !important;
        align-items: center;
        justify-content: center;
        width: 42px;
        height: 42px;
        margin: 0 !important;
        padding: 0 !important;
        border-radius: 10px;
        background: rgba(255, 255, 255, 0.05);
        border: 1px solid rgba(255, 255, 255, 0.08);
        cursor: pointer;
        position: relative;
        transition: all 0.2s ease;
    }
    .map-legend-row:hover {
        background: rgba(255, 255, 255, 0.15);
        border-color: rgba(255, 255, 255, 0.3);
        transform: translateY(-3px);
    }
    .map-legend-row.inactive {
        opacity: 0.4;
        background: transparent;
        border-color: transparent;
        transform: none;
    }
    .map-legend-row img {
        width: 24px !important;
        height: 24px !important;
        object-fit: contain;
        transition: all 0.2s;
    }
    .map-legend-row.inactive img {
        filter: grayscale(100%) opacity(0.6);
    }
    .map-legend-row span:not(.map-legend-badge) {
        display: none !important; /* Hide label text */
    }
    
    /* Custom Tooltip */
    .map-legend-row::after {
        content: attr(data-title);
        position: absolute;
        bottom: 100%;
        left: 50%;
        transform: translateX(-50%) translateY(10px);
        background: rgba(15, 23, 42, 0.95);
        color: #fff;
        padding: 6px 12px;
        border-radius: 6px;
        font-size: 11px;
        font-weight: 600;
        white-space: nowrap;
        pointer-events: none;
        opacity: 0;
        visibility: hidden;
        transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
        border: 1px solid rgba(255, 255, 255, 0.1);
        z-index: 1000;
        box-shadow: 0 4px 12px rgba(0,0,0,0.5);
    }
    .map-legend-row:hover::after {
        opacity: 1;
        visibility: visible;
        transform: translateX(-50%) translateY(-8px);
    }

    /* Badge untuk item dinamis (Contigency/Trafficstuck) */
    .map-legend-badge {
        position: absolute;
        top: -6px;
        right: -6px;
        background: #ef4444;
        color: #fff;
        font-size: 10px;
        font-weight: 700;
        padding: 1px 4px;
        border-radius: 10px;
        border: 2px solid #0f172a;
        min-width: 14px;
        text-align: center;
        line-height: 1.2;
        z-index: 2;
    }
    /* Edit Mode button */
    #editModeBtn {
        position: fixed;
        bottom: 80px;
        left: 16px;
        z-index: 1000;
        background: rgba(20, 30, 50, 0.92);
        border: 2px solid #4ade80;
        color: #4ade80;
        padding: 8px 14px;
        border-radius: 10px;
        font-size: 12px;
        font-weight: 700;
        cursor: pointer;
        display: flex;
        align-items: center;
        gap: 6px;
        transition: all 0.2s;
        backdrop-filter: blur(6px);
    }
    #editModeBtn:hover { background: rgba(74, 222, 128, 0.2); }
    #editModeBtn.active { background: rgba(74, 222, 128, 0.25); border-color: #86efac; color: #86efac; }
    /* Toast konfirmasi simpan koordinat */
    #saveKoordToast {
        position: fixed;
        bottom: 130px;
        left: 50%;
        transform: translateX(-50%);
        z-index: 2000;
        background: rgba(10, 20, 40, 0.97);
        border: 1px solid #4ade80;
        color: #fff;
        padding: 12px 18px;
        border-radius: 12px;
        font-size: 13px;
        min-width: 280px;
        max-width: 380px;
        display: none;
        flex-direction: column;
        gap: 8px;
        backdrop-filter: blur(8px);
        box-shadow: 0 8px 32px rgba(0,0,0,0.5);
    }
    #saveKoordToast .toast-title { font-weight: 700; font-size: 12px; color: #4ade80; }
    #saveKoordToast .toast-body { font-size: 11px; color: #cbd5e1; line-height: 1.4; }
    #saveKoordToast .toast-actions { display: flex; gap: 8px; margin-top: 4px; }
    #saveKoordToast .btn-save {
        flex: 1; padding: 7px 0; border-radius: 8px; border: none;
        background: #4ade80; color: #052e16; font-weight: 700; font-size: 12px; cursor: pointer;
    }
    #saveKoordToast .btn-cancel {
        flex: 1; padding: 7px 0; border-radius: 8px; border: 1px solid #475569;
        background: transparent; color: #94a3b8; font-weight: 700; font-size: 12px; cursor: pointer;
    }
    /* Drag indicator badge di atas marker saat edit mode */
    .map-pin-draggable .map-pin-circle { outline: 2px dashed #4ade80 !important; outline-offset: 3px; cursor: grab !important; }
    .edit-mode-hint {
        position: fixed; top: 80px; left: 50%; transform: translateX(-50%);
        z-index: 999; background: rgba(74,222,128,0.9); color: #052e16;
        padding: 6px 16px; border-radius: 20px; font-size: 12px; font-weight: 700;
        pointer-events: none; display: none;
    }
</style>

@endpush

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

    {{-- Hint bar edit mode --}}
    <div class="edit-mode-hint" id="editModeHint">✏️ Edit Mode Aktif — Seret marker untuk pindahkan koordinat</div>

    <div class="layer-switch">
        <div id="btn-street" class="ls-opt" onclick="setMapType('street')">GELAP</div>
        <div id="btn-hybrid" class="ls-opt" onclick="setMapType('hybrid')">HYBRID</div>
        <div id="btn-clean" class="ls-opt" onclick="setMapType('clean')">CLEAN</div>
    </div>

    <div class="map-legend collapsed-card" id="mapLegend">
        <div class="map-legend-border-title" onclick="toggleLegend()">
            <span title="Jenis marker"><i class="fas fa-list-ul"></i> Legenda Marker</span>
            <i class="fas fa-chevron-up" id="legendIcon"></i>
        </div>
        <div id="mapLegendContent" class="collapsed">

            {{-- ── Terminal & Transport (statis) ── --}}
            <div class="legend-group">
                <div class="map-legend-row" data-layer="terminal" data-title="Terminal bus"><img src="{{ asset('satupeta_assets/satupeta/Icon_terminal.png') }}"></div>
                <div class="map-legend-row" data-layer="stasiun" data-title="Stasiun kereta"><img src="{{ asset('satupeta_assets/satupeta/location.png') }}"></div>
                <div class="map-legend-row" data-layer="bandara" data-title="Bandar udara"><img src="{{ asset('satupeta_assets/satupeta/location.png') }}"></div>
                <div class="map-legend-row" data-layer="pelabuhan" data-title="Pelabuhan"><img src="{{ asset('satupeta_assets/satupeta/location.png') }}"></div>
            </div>

            {{-- ── Operasional Umum (statis) ── --}}
            <div class="legend-group">
                <div class="map-legend-row" data-layer="posko" data-title="Posko"><img src="{{ asset('satupeta_assets/satupeta/posko.png') }}"></div>
                <div class="map-legend-row" data-layer="gangguan" data-title="Gangguan jalan"><img src="{{ asset('satupeta_assets/satupeta/ambanggangguan.png') }}"></div>
                <div class="map-legend-row" data-layer="tj" data-title="Turjagwali"><img src="{{ asset('satupeta_assets/satupeta/polisi.png') }}"></div>
                <div class="map-legend-row" data-layer="aa" data-title="Audit Arus"><img src="{{ asset('satupeta_assets/satupeta/trafficcounting.png') }}"></div>
                <div class="map-legend-row" data-layer="pa" data-title="PNP Angkutan"><img src="{{ asset('satupeta_assets/satupeta/bus.png') }}"></div>
            </div>

            {{-- ── Fasilitas Umum (statis) ── --}}
            <div class="legend-group">
                <div class="map-legend-row" data-layer="wisata" data-title="Objek Wisata"><img src="{{ asset('satupeta_assets/satupeta/objek-vital.png') }}"></div>
                <div class="map-legend-row" data-layer="ibadah" data-title="Tempat Ibadah"><img src="{{ asset('satupeta_assets/satupeta/fasum.png') }}"></div>
                <div class="map-legend-row" data-layer="faskes" data-title="Data Faskes"><img src="{{ asset('satupeta_assets/satupeta/hospital.png') }}"></div>
                <div class="map-legend-row" data-layer="cctv" data-title="CCTV Live"><img src="{{ asset('satupeta_assets/satupeta/cctv.png') }}"></div>
            </div>

            {{-- ── Contigency: dinamis dari data collecting ── --}}
            <div id="legend-con-items" class="legend-group">
                {{-- diisi oleh JS --}}
            </div>

            {{-- ── TrafficStuck: dinamis dari data collecting ── --}}
            <div id="legend-ts-items" class="legend-group">
                {{-- diisi oleh JS --}}
            </div>
        </div>
    </div>

    <script>
        function toggleLegend() {
            var content = document.getElementById('mapLegendContent');
            var card = document.getElementById('mapLegend');
            var icon = document.getElementById('legendIcon');
            if (content.classList.contains('collapsed')) {
                content.classList.remove('collapsed');
                card.classList.remove('collapsed-card');
                icon.classList.remove('fa-chevron-up');
                icon.classList.add('fa-chevron-down');
            } else {
                content.classList.add('collapsed');
                card.classList.add('collapsed-card');
                icon.classList.remove('fa-chevron-down');
                icon.classList.add('fa-chevron-up');
            }
        }
    </script>

    <div id="map"></div>

    {{-- Toast konfirmasi simpan koordinat (dipakai saat Edit Mode aktif) --}}
    <div id="saveKoordToast">
        <div class="toast-title"><i class="fas fa-map-pin" style="margin-right:5px;"></i> Pindahkan Koordinat?</div>
        <div class="toast-body">Seret marker ke posisi baru, lalu klik Simpan.</div>
        <div class="toast-actions">
            <button class="btn-save"><i class="fas fa-check"></i> Simpan</button>
            <button class="btn-cancel"><i class="fas fa-times"></i> Batal</button>
        </div>
    </div>

    @push('scripts')
    <script>
        (function () {
            var userDaerah      = @json($userDaerah);
            var userWilayah     = @json($userWilayah);
            var bypassTerritory = @json($bypassTerritory);
            var userLevel       = @json($userLevel ?? 'nasional');
            var currentUserId   = @json($currentUserId ?? null);
            var isSuperAdmin    = @json($isSuperAdmin ?? false);
            var isExecutiveNational = @json($isExecutiveNational ?? false);
            var isExecutiveProvinsi = @json($isExecutiveProvinsi ?? false);
            var lockMapViewport = isExecutiveNational || (isExecutiveProvinsi && !!(userDaerah || userWilayah));
            var csrfToken       = '{{ csrf_token() }}';
            var updateKoordUrl  = '{{ route('map.update-koordinat') }}';

            var defaultCenter = [ -7.6812744, 110.5036695 ];
            var defaultZoom = 16;
            var hasUserCenter = false;
            var hasTerritoryScope = !bypassTerritory && !!(userDaerah || userWilayah);

            @if(isset($userCenter) && $userCenter)
                defaultCenter = [{{ $userCenter[0] }}, {{ $userCenter[1] }}];
                defaultZoom = {{ $userZoom ?? 14 }};
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

            // Executive1/2: jangan pakai posisi cache browser — viewport mengikuti cakupan role
            var cachedView = lockMapViewport ? null : localStorage.getItem('nms_map_view');
            if (cachedView && (bypassTerritory || (!userDaerah && !userWilayah))) {
                try {
                    var p = JSON.parse(cachedView);
                    map.setView(p.center, p.zoom);
                } catch (e) {}
            }
            map.on('moveend', function() {
                var c = map.getCenter();
                if (c && c.lat !== undefined && c.lng !== undefined) {
                    if (!lockMapViewport) {
                        localStorage.setItem('nms_map_view', JSON.stringify({
                            center: [c.lat, c.lng],
                            zoom: map.getZoom()
                        }));
                    }
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

            function createClusterGroup(iconFile) {
                return L.markerClusterGroup({
                    showCoverageOnHover: false,
                    maxClusterRadius: 55,
                    spiderfyOnMaxZoom: true,
                    iconCreateFunction: function(cluster) {
                        var childCount = cluster.getChildCount();
                        var imageUrl = "{{ asset('satupeta_assets/satupeta') }}/" + iconFile;
                        return L.divIcon({
                            html: '<div class="custom-cluster-icon">' +
                                    '<img src="' + imageUrl + '">' +
                                    '<span class="cluster-badge">' + childCount + '</span>' +
                                  '</div>',
                            className: 'marker-cluster-custom',
                            iconSize: [40, 40],
                            iconAnchor: [20, 20]
                        });
                    }
                });
            }

            var layers = {
                'terminal': createClusterGroup('Icon_terminal.png').addTo(map),
                'stasiun': createClusterGroup('location.png').addTo(map),
                'bandara': createClusterGroup('location.png').addTo(map),
                'pelabuhan': createClusterGroup('location.png').addTo(map),
                'posko': createClusterGroup('posko.png').addTo(map),
                'gangguan': createClusterGroup('ambanggangguan.png').addTo(map),
                'con': createClusterGroup('emergency.png').addTo(map),
                'ts': createClusterGroup('rawan-macet.png').addTo(map),
                'tj': createClusterGroup('polisi.png').addTo(map),
                'aa': createClusterGroup('trafficcounting.png').addTo(map),
                'pa': createClusterGroup('bus.png').addTo(map),
                'wisata': createClusterGroup('objek-vital.png').addTo(map),
                'ibadah': createClusterGroup('fasum.png').addTo(map),
                'faskes': createClusterGroup('hospital.png').addTo(map),
                'cctv': createClusterGroup('cctv.png').addTo(map)
            };

            // Registry semua marker yang dapat di-drag (untuk edit koordinat)
            var draggableMarkers = [];
            var editModeActive = false;

            function addMarker(obj, iconFile, label, layerKey) {
                var lat = obj.coordinates[0], lng = obj.coordinates[1];
                var imageUrl = "{{ asset('satupeta_assets/satupeta') }}/" + iconFile;
                var iconHtml =
                    '<div class="map-pin-root" style="width:' + PIN_WRAP_W + 'px">' +
                        '<div class="map-pin-circle" style="width:40px;height:40px;display:flex;justify-content:center;align-items:center;background:transparent;border:none;box-shadow:none;">' +
                           '<img src="' + imageUrl + '" style="width:100%; height:100%; object-fit:contain; filter: drop-shadow(0px 3px 5px rgba(0,0,0,0.5));">' +
                        '</div>' +
                        '<div class="map-label">' + escHtml(label || obj.name || '') + '</div>' +
                    '</div>';

                // Tentukan apakah marker ini bisa di-drag oleh user ini
                var canDrag = currentUserId && (
                    isSuperAdmin ||
                    (obj.piket && String(obj.piket) === String(currentUserId))
                );

                var marker = L.marker([lat, lng], {
                    draggable: false, // drag diaktifkan hanya saat editMode
                    icon: L.divIcon({
                        html: iconHtml,
                        className: 'map-pin-divicon',
                        iconSize: [PIN_WRAP_W, PIN_WRAP_H],
                        iconAnchor: [PIN_ANCHOR_X, PIN_ANCHOR_Y]
                    })
                });

                var tipHtml;
                if (layerKey === 'cctv') {
                    var idOnly = String(obj.id).replace('cctv_', '');
                    var startUrl = "/cctv-live/" + idOnly + "/start";
                    var stopUrl = "/cctv-live/" + idOnly + "/stop";

                    var statusLower = (obj.status || '').toLowerCase().trim();
                    var isOnline = ['aktif', 'online', 'hidup', 'normal', '1'].indexOf(statusLower) !== -1 || !obj.status;
                    var statusBadge = isOnline 
                        ? '<span style="display:inline-flex; align-items:center; gap:4px; padding:2px 6px; border-radius:4px; background:rgba(16,185,129,0.2); color:#10b981; font-size:10px; font-weight:bold;"><span style="width:6px; height:6px; border-radius:50%; background:#10b981;"></span> Aktif</span>'
                        : '<span style="display:inline-flex; align-items:center; gap:4px; padding:2px 6px; border-radius:4px; background:rgba(239,68,68,0.2); color:#ef4444; font-size:10px; font-weight:bold;"><span style="width:6px; height:6px; border-radius:50%; background:#ef4444;"></span> Mati</span>';

                    tipHtml = 
                        '<div class="cctv-popup-container" data-id="' + idOnly + '" data-start-url="' + startUrl + '" data-stop-url="' + stopUrl + '" style="width:280px; font-size:12px; line-height:1.4; color:#fff;">' +
                            '<div style="font-weight:bold; font-size:13px; color:#fff; margin-bottom:8px; display:flex; align-items:center; justify-content:space-between; width:100%;">' +
                                '<span style="display:flex; align-items:center; gap:6px;"><i class="fas fa-video" style="color:#ef4444;"></i> ' + escHtml(obj.name || 'CCTV Live') + '</span>' +
                                statusBadge +
                            '</div>' +
                            '<div class="cctv-stream-wrapper" style="position:relative; width:100%; aspect-ratio:16/9; background:#000; border-radius:8px; overflow:hidden; border:1px solid #444; margin-bottom:8px; display:flex; align-items:center; justify-content:center;">' +
                                '<iframe class="cctv-popup-iframe" style="width:100%; height:100%; border:none; display:none;" allow="autoplay; fullscreen"></iframe>' +
                                '<div class="cctv-popup-placeholder" style="text-align:center; padding:10px;">' +
                                    '<i class="fas fa-spinner fa-spin cctv-popup-spinner" style="font-size:24px; color:#ef4444; margin-bottom:6px;"></i>' +
                                    '<div class="cctv-popup-status" style="font-size:11px; color:#aaa;">Menyiapkan stream…</div>' +
                                '</div>' +
                            '</div>' +
                            '<div style="display:flex; gap:6px; margin-top:8px;">' +
                                '<a href="' + obj.stream_url + '" target="_blank" class="btn btn-sm" style="background:#ef4444; color:#fff; padding:4px 8px; font-size:11px; text-decoration:none; border-radius:4px; font-weight:bold; flex:1; text-align:center;"><i class="fas fa-expand"></i> Layar Penuh</a>' +
                                '<a href="https://www.google.com/maps/dir/?api=1&destination=' + lat + ',' + lng + '" target="_blank" class="btn btn-sm" style="background:#2563eb; color:#fff; padding:4px 8px; font-size:11px; text-decoration:none; border-radius:4px; text-align:center; flex:1;"><i class="fas fa-directions"></i> Rute</a>' +
                            '</div>' +
                        '</div>';
                } else {
                    tipHtml = '<div style="font-size:13px; line-height:1.5; min-width:160px;">';

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

                    if (obj.stream_url) {
                        tipHtml += '<div style="margin-top:8px; display:flex; gap:6px;">' +
                                   '<a href="' + obj.stream_url + '" target="_blank" class="btn btn-sm" style="background:#ef4444; color:#fff; padding:4px 8px; font-size:11px; text-decoration:none; border-radius:4px; display:inline-block; font-weight:bold;"><i class="fas fa-video"></i> Tonton Live</a>' +
                                   '<a href="https://www.google.com/maps/dir/?api=1&destination=' + lat + ',' + lng + '" target="_blank" class="btn btn-sm" style="background:#2563eb; color:#fff; padding:4px 8px; font-size:11px; text-decoration:none; border-radius:4px; display:inline-block;"><i class="fas fa-directions"></i> Rute</a>' +
                                   '</div></div>';
                    } else {
                        tipHtml += '<div style="margin-top:8px;">' +
                                   '<a href="https://www.google.com/maps/dir/?api=1&destination=' + lat + ',' + lng + '" target="_blank" class="btn btn-sm" style="background:#2563eb; color:#fff; padding:4px 8px; font-size:11px; text-decoration:none; border-radius:4px; display:inline-block;"><i class="fas fa-directions"></i> Buka Rute Peta</a>' +
                                   '</div></div>';
                    }
                }

                marker.bindPopup(tipHtml, { minWidth: layerKey === 'cctv' ? 300 : 150 });

                var targetLayer = layers[layerKey] || layers['posko'];
                targetLayer.addLayer(marker);

                // Simpan referensi jika marker ini bisa di-drag
                if (canDrag && obj.dbTable && obj.dbId) {
                    draggableMarkers.push({
                        marker: marker,
                        table: obj.dbTable,
                        id: obj.dbId,
                        name: obj.name || label || ''
                    });
                }

                return [lat, lng];
            }

            /** Bedakan stasiun, terminal bus, bandara, pelabuhan dari kolom jenis (sama dengan t_jenisterminal.namejenisterminal) */
            function getTerminalPinFile(jenis) {
                var j = String(jenis || '').toLowerCase();
                if (j.indexOf('stasiun') !== -1 || j.indexOf('kereta') !== -1) {
                    return { file: 'location.png', short: 'Stasiun' };
                }
                if (j.indexOf('bandar') !== -1 || j.indexOf('udara') !== -1) {
                    return { file: 'location.png', short: 'Bandara' };
                }
                if (j.indexOf('pelabuhan') !== -1 || j.indexOf('asdp') !== -1) {
                    return { file: 'location.png', short: 'Pelabuhan' };
                }
                return { file: 'Icon_terminal.png', short: 'Terminal' };
            }

            var poskoData       = @json($markers);
            var terminalsData   = @json($terminals);
            var jalanGangguanData = @json($jalanGangguan);
            var contigencyData  = @json($contigency);
            var trafficstuckData = @json($trafficstuck);
            var turjagwaliData  = @json($turjagwali);
            var auditarusData   = @json($auditarus);
            var pnpangkutanData = @json($pnpangkutan);
            var objekwisataData = @json($objekwisata ?? []);
            var tempatibadahData = @json($tempatibadah ?? []);
            var datafaskesData = @json($datafaskes ?? []);
            var cctvData = @json($cctv ?? []);

            var allBounds = [];
            // Inject dbTable & dbId ke setiap obj agar bisa di-update
            function tag(arr, tbl) { return arr.map(function(o) { var r=Object.assign({},o); r.dbTable=tbl; r.dbId=o.id?(o.id+'').replace(/^[^_]+_/,'')*1:null; return r; }); }
            poskoData       = tag(poskoData,       'pos_pelayanan');
            terminalsData   = tag(terminalsData,   'data_terminal');
            jalanGangguanData = tag(jalanGangguanData, 'data_gangguan');
            contigencyData  = tag(contigencyData,  'contigency');
            trafficstuckData = tag(trafficstuckData, 'trafficstuck');
            turjagwaliData  = tag(turjagwaliData,  'turjagwali');
            auditarusData   = tag(auditarusData,   'auditarus');
            pnpangkutanData = tag(pnpangkutanData, 'pnp_angkutan');
            objekwisataData = tag(objekwisataData, 'objek_wisata');
            tempatibadahData = tag(tempatibadahData, 'tempatibadah');
            datafaskesData  = tag(datafaskesData,  'data_faskes');
            cctvData        = tag(cctvData,        'smpc_tbl_data_cctv');

            poskoData.forEach(function(obj) {
                var c = addMarker(obj, 'posko.png', obj.name || 'Posko', 'posko');
                if (c) allBounds.push(c);
            });
            terminalsData.forEach(function(obj) {
                var st = getTerminalPinFile(obj.jenis);
                var lbl = (obj.name || '') + (obj.jenis ? ' · ' + st.short : '');
                var layerKey = 'terminal';
                var stShortLower = st.short.toLowerCase();
                if (stShortLower.includes('stasiun')) layerKey = 'stasiun';
                else if (stShortLower.includes('bandara')) layerKey = 'bandara';
                else if (stShortLower.includes('pelabuhan')) layerKey = 'pelabuhan';

                var c = addMarker(obj, st.file, lbl, layerKey);
                if (c) allBounds.push(c);
            });
            jalanGangguanData.forEach(function(obj) {
                var c = addMarker(obj, 'ambanggangguan.png', obj.name || 'Jalan Gangguan', 'gangguan');
                if (c) allBounds.push(c);
            });
            objekwisataData.forEach(function(obj) {
                var c = addMarker(obj, 'objek-vital.png', obj.name || 'Objek Wisata', 'wisata');
                if (c) allBounds.push(c);
            });
            tempatibadahData.forEach(function(obj) {
                var c = addMarker(obj, 'fasum.png', obj.name || 'Tempat Ibadah', 'ibadah');
                if (c) allBounds.push(c);
            });
            datafaskesData.forEach(function(obj) {
                var c = addMarker(obj, 'hospital.png', obj.name || 'Data Faskes', 'faskes');
                if (c) allBounds.push(c);
            });
            cctvData.forEach(function(obj) {
                var c = addMarker(obj, 'cctv.png', obj.name || 'CCTV Live', 'cctv');
                if (c) allBounds.push(c);
            });
            function getContigencyIcon(jenis) {
                var j = String(jenis || '').toLowerCase();
                if (j.includes('banjir')) return 'rawan-banjir.png';
                if (j.includes('laka') || j.includes('kecelakaan')) return 'rawan-laka.png';
                if (j.includes('longsor')) return 'rawan-longsor.png';
                if (j.includes('pohon') || j.includes('tumbang')) return 'rawan-pohontumbang.png';
                if (j.includes('pidana') || j.includes('kriminal') || j.includes('kejahatan')) return 'rawan-tindakpidana.png';
                return 'emergency.png';
            }

            function getTrafficStuckIcon(faktor) {
                var f = String(faktor || '').toLowerCase();
                if (f.includes('laka') || f.includes('kecelakaan')) return 'rawan-laka.png';
                if (f.includes('banjir')) return 'rawan-banjir.png';
                if (f.includes('pohon') || f.includes('tumbang')) return 'rawan-pohontumbang.png';
                return 'rawan-macet.png';
            }

            contigencyData.forEach(function(obj) {
                var jenis = (obj.name && obj.name.trim()) ? obj.name.trim() : 'Tidak Dikategorikan';
                var layerKey = 'con_' + jenis.replace(/\W+/g, '_').toLowerCase();
                if (!layers[layerKey]) {
                    layers[layerKey] = createClusterGroup(getContigencyIcon(jenis)).addTo(map);
                }
                var c = addMarker(obj, getContigencyIcon(jenis), jenis, layerKey);
                if (c) allBounds.push(c);
            });
            trafficstuckData.forEach(function(obj) {
                var faktor = (obj.name && obj.name.trim()) ? obj.name.trim() : 'Tidak Dikategorikan';
                var layerKey = 'ts_' + faktor.replace(/\W+/g, '_').toLowerCase();
                if (!layers[layerKey]) {
                    layers[layerKey] = createClusterGroup(getTrafficStuckIcon(faktor)).addTo(map);
                }
                var c = addMarker(obj, getTrafficStuckIcon(faktor), faktor, layerKey);
                if (c) allBounds.push(c);
            });
            turjagwaliData.forEach(function(obj) {
                var c = addMarker(obj, 'polisi.png', obj.name || 'Turjagwali', 'tj');
                if (c) allBounds.push(c);
            });
            auditarusData.forEach(function(obj) {
                var c = addMarker(obj, 'trafficcounting.png', obj.name || 'Audit Arus', 'aa');
                if (c) allBounds.push(c);
            });
            pnpangkutanData.forEach(function(obj) {
                var c = addMarker(obj, 'bus.png', obj.name || 'PNP Angkutan', 'pa');
                if (c) allBounds.push(c);
            });

            // ─── LEGEND DINAMIS: data dari table collecting ──────────────────────
            (function buildDynamicLegend() {
                var iconBase = "{{ asset('satupeta_assets/satupeta') }}/";

                // ─ Helper: render legend item row ─
                function makeLegendRow(layerKey, label, iconFile, badge) {
                    var el = document.createElement('div');
                    el.className = 'map-legend-row';
                    el.setAttribute('data-layer', layerKey);
                    
                    var tooltipText = escHtml(label) + (badge ? ' (' + badge + ')' : '');
                    el.setAttribute('data-title', tooltipText);
                    
                    var html = '<img src="' + iconBase + iconFile + '">';
                    if (badge) {
                        html += '<span class="map-legend-badge">' + badge + '</span>';
                    }
                    el.innerHTML = html;

                    return el;
                }

                // ─ CONTIGENCY: kelompokkan berdasarkan jeniscontigency (field `name`) ─
                (function() {
                    var container = document.getElementById('legend-con-items');
                    var countEl   = document.getElementById('legend-con-count');
                    if (!container) return;

                    var counts = {};
                    contigencyData.forEach(function(obj) {
                        var jenis = (obj.name && obj.name.trim()) ? obj.name.trim() : '(Tidak Dikategorikan)';
                        counts[jenis] = (counts[jenis] || 0) + 1;
                    });

                    container.innerHTML = '';
                    var total = contigencyData.length;
                    if (countEl) countEl.textContent = total ? '(' + total + ' titik)' : '';

                    if (total === 0) return; // Hide cleanly if empty

                    // Urutkan descending count
                    var entries = Object.entries(counts).sort(function(a, b){ return b[1] - a[1]; });
                    entries.forEach(function(entry) {
                        var jenis = entry[0];
                        var layerKey = 'con_' + jenis.replace(/\W+/g, '_').toLowerCase();
                        container.appendChild(makeLegendRow(layerKey, jenis, getContigencyIcon(jenis), entry[1]));
                    });
                })();

                // ─ TRAFFICSTUCK: kelompokkan berdasarkan faktor (field `name`) ─
                (function() {
                    var container = document.getElementById('legend-ts-items');
                    var countEl   = document.getElementById('legend-ts-count');
                    if (!container) return;

                    var counts = {};
                    trafficstuckData.forEach(function(obj) {
                        var faktor = (obj.name && obj.name.trim()) ? obj.name.trim() : '(Tidak Dikategorikan)';
                        counts[faktor] = (counts[faktor] || 0) + 1;
                    });

                    container.innerHTML = '';
                    var total = trafficstuckData.length;
                    if (countEl) countEl.textContent = total ? '(' + total + ' titik)' : '';

                    if (total === 0) return; // Hide cleanly if empty

                    // Urutkan descending count
                    var entries = Object.entries(counts).sort(function(a, b){ return b[1] - a[1]; });
                    entries.forEach(function(entry) {
                        var faktor = entry[0];
                        var layerKey = 'ts_' + faktor.replace(/\W+/g, '_').toLowerCase();
                        container.appendChild(makeLegendRow(layerKey, faktor, getTrafficStuckIcon(faktor), entry[1]));
                    });
                })();
            })();
            // ─── END LEGEND DINAMIS ──────────────────────────────────────────────


            // ─── EDIT MODE: Drag & Save Koordinat ───────────────────────────────
            var pendingDrag = null;
            var toast = document.getElementById('saveKoordToast');

            function showToast(info, newLat, newLng, originalLatLng) {
                pendingDrag = { info: info, lat: newLat, lng: newLng, originalLatLng: originalLatLng };
                var body = document.querySelector('#saveKoordToast .toast-body');
                if (body) body.textContent = 'Marker "' + info.name + '" akan dipindah ke:\n' +
                    newLat.toFixed(6) + ', ' + newLng.toFixed(6);
                toast.style.display = 'flex';
            }
            function hideToast() { toast.style.display = 'none'; pendingDrag = null; }

            function doSave() {
                if (!pendingDrag) return;
                var d = pendingDrag;
                hideToast();
                fetch(updateKoordUrl, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken
                    },
                    body: JSON.stringify({ table: d.info.table, id: d.info.id, lat: d.lat, lng: d.lng })
                })
                .then(function(r) { return r.json(); })
                .then(function(res) {
                    if (res.success) {
                        // Tampilkan notif kecil
                        var n = document.createElement('div');
                        n.style.cssText = 'position:fixed;bottom:60px;left:50%;transform:translateX(-50%);z-index:3000;background:#4ade80;color:#052e16;padding:8px 18px;border-radius:20px;font-size:12px;font-weight:700;pointer-events:none;';
                        n.textContent = '✓ Koordinat disimpan!';
                        document.body.appendChild(n);
                        setTimeout(function() { n.remove(); }, 2500);
                    } else {
                        // Kembalikan marker ke posisi semula
                        d.info.marker.setLatLng(d.originalLatLng);
                        alert('Gagal: ' + (res.message || 'Error'));
                    }
                })
                .catch(function() {
                    d.info.marker.setLatLng(d.originalLatLng);
                    alert('Gagal menyimpan koordinat. Cek koneksi.');
                });
            }

            function doCancel() {
                if (pendingDrag) { pendingDrag.info.marker.setLatLng(pendingDrag.originalLatLng); }
                hideToast();
            }

            var btnSave   = document.querySelector('#saveKoordToast .btn-save');
            var btnCancel = document.querySelector('#saveKoordToast .btn-cancel');
            if (btnSave)   btnSave.addEventListener('click', doSave);
            if (btnCancel) btnCancel.addEventListener('click', doCancel);

            function toggleEditMode() {
                editModeActive = !editModeActive;
                var btn  = document.getElementById('editModeBtn');
                var hint = document.getElementById('editModeHint');
                if (btn)  btn.classList.toggle('active', editModeActive);
                if (hint) hint.style.display = editModeActive ? 'block' : 'none';
                if (btn)  btn.querySelector('span').textContent = editModeActive ? ' Selesai Edit' : ' Edit Koordinat';

                draggableMarkers.forEach(function(info) {
                    if (editModeActive) {
                        info.marker.options.draggable = true;
                        info.marker.dragging && info.marker.dragging.enable();
                        var el = info.marker.getElement();
                        if (el) el.classList.add('map-pin-draggable');

                        info.marker.off('dragend').on('dragend', function(e) {
                            var newLL = info.marker.getLatLng();
                            var origLL = e.oldLatLng || newLL; // fallback
                            showToast(info, newLL.lat, newLL.lng, origLL);
                        });
                    } else {
                        info.marker.dragging && info.marker.dragging.disable();
                        var el = info.marker.getElement();
                        if (el) el.classList.remove('map-pin-draggable');
                        info.marker.off('dragend');
                        hideToast();
                    }
                });
            }

            // Tampilkan tombol Edit Mode hanya jika ada marker yang bisa di-drag
            if (currentUserId && draggableMarkers.length > 0) {
                var editBtn = document.createElement('button');
                editBtn.id = 'editModeBtn';
                editBtn.innerHTML = '<i class="fas fa-arrows-alt"></i><span> Edit Koordinat</span>';
                editBtn.addEventListener('click', toggleEditMode);
                document.body.appendChild(editBtn);
            }

            // Bind click events on legend rows for toggling layers using event delegation
            document.addEventListener('click', function(e) {
                var row = e.target.closest('.map-legend-row');
                if (!row) return;
                var layerKey = row.getAttribute('data-layer');
                if (!layerKey) return;
                var layer = layers[layerKey];
                if (layer) {
                    if (row.classList.contains('inactive')) {
                        row.classList.remove('inactive');
                        map.addLayer(layer);
                    } else {
                        row.classList.add('inactive');
                        map.removeLayer(layer);
                    }
                }
            });

            if (allBounds.length > 0 && !cachedView && !hasUserCenter && !hasTerritoryScope) {
                map.fitBounds(allBounds, { padding: [40, 40], maxZoom: 17 });
            }

            // Event handlers untuk memutar dan menghentikan live stream CCTV langsung di dalam popup peta
            map.on('popupopen', function(e) {
                var popup = e.popup;
                var container = popup.getElement().querySelector('.cctv-popup-container');
                if (container) {
                    var cameraId = container.getAttribute('data-id');
                    var startUrl = container.getAttribute('data-start-url');
                    var stopUrl = container.getAttribute('data-stop-url');
                    
                    var iframe = container.querySelector('.cctv-popup-iframe');
                    var status = container.querySelector('.cctv-popup-status');
                    var placeholder = container.querySelector('.cctv-popup-placeholder');
                    var csrfMeta = document.querySelector('meta[name="csrf-token"]');
                    var csrfToken = csrfMeta ? csrfMeta.getAttribute('content') : '';

                    var state = { streamName: '' };
                    
                    async function startStream() {
                        if (status) status.textContent = 'Menghubungkan ke kamera…';
                        try {
                            var response = await fetch(startUrl, {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'Accept': 'application/json',
                                    'X-CSRF-TOKEN': csrfToken
                                }
                            });
                            var result = await response.json().catch(function() { return {}; });
                            if (!response.ok || !result.success || !result.viewer_url) {
                                throw new Error(result.message || 'Gagal memulai stream.');
                            }
                            state.streamName = result.stream_name || '';
                            
                            if (iframe) {
                                iframe.src = result.viewer_url;
                                iframe.style.display = 'block';
                            }
                            if (placeholder) placeholder.style.display = 'none';
                        } catch (err) {
                            if (status) {
                                status.textContent = err.message || 'Gagal memuat stream.';
                                status.style.color = '#ef4444';
                            }
                            var spinner = container.querySelector('.cctv-popup-spinner');
                            if (spinner) spinner.style.display = 'none';
                        }
                    }
                    
                    startStream();
                    popup._cctvState = state;
                    popup._cctvStopUrl = stopUrl;
                }
            });

            map.on('popupclose', function(e) {
                var popup = e.popup;
                if (popup._cctvState && popup._cctvState.streamName) {
                    var stopUrl = popup._cctvStopUrl;
                    var streamName = popup._cctvState.streamName;
                    var csrfMeta = document.querySelector('meta[name="csrf-token"]');
                    var csrfToken = csrfMeta ? csrfMeta.getAttribute('content') : '';

                    fetch(stopUrl, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': csrfToken
                        },
                        body: JSON.stringify({ stream_name: streamName })
                    }).catch(function() {});
                    popup._cctvState.streamName = '';
                }
            });

            function applyTerritoryZoom(minZoom) {
                if (map.getZoom() < minZoom) {
                    map.setZoom(minZoom);
                }
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
                        return str.replace(/^(polda|polrestabes\s+metro|polresta\s+metro|polres\s+metro|polrestabes|polresta|polres|kabupaten|kab\.|kota\s+adm\.|kota\s+administrasi|kota|provinsi|prov\.)\s+/i, '')
                                  .replace(/\s+(polda|polrestabes\s+metro|polresta\s+metro|polres\s+metro|polrestabes|polresta|polres|kabupaten|kab\.|kota\s+adm\.|kota\s+administrasi|kota|provinsi|prov\.)$/i, '')
                                  .trim();
                    }

                    var gCleaned = clean(gClean);
                    var uCleaned = clean(uClean);

                    // Cek apakah ada indikasi "kota" (termasuk polresta/polrestabes/metro)
                    var gIsKota = gClean.includes('kota') || gClean.includes('polresta') || gClean.includes('polrestabes') || gClean.includes('metro');
                    var uIsKota = uClean.includes('kota') || uClean.includes('polresta') || uClean.includes('polrestabes') || uClean.includes('metro');

                    // Daftar wilayah yang memiliki Kota dan Kabupaten dengan nama dasar yang sama
                    var ambiguousNames = [
                        'bandung', 'bekasi', 'bogor', 'cirebon', 'sukabumi', 'tasikmalaya',
                        'tegal', 'pekalongan', 'semarang', 'magelang', 'madiun', 'malang',
                        'mojokerto', 'pasuruan', 'probolinggo', 'blitar', 'kediri',
                        'kupang', 'gorontalo', 'jayapura', 'sorong'
                    ];

                    // Hanya lakukan strict check (harus sama-sama Kota atau sama-sama Kabupaten) 
                    // JIKA nama wilayahnya ambigu (ada Kota dan Kabupaten-nya).
                    if (ambiguousNames.includes(gCleaned)) {
                        if (gIsKota !== uIsKota) {
                            if (gCleaned === uCleaned) {
                                return false;
                            }
                        }
                    }

                    return gCleaned === uCleaned;
                }

                // Tentukan GeoJSON berdasarkan userLevel (bukan hanya ada/tidaknya userWilayah)
                // kab_kota → selalu tampilkan batas kabupaten/kota
                // provinsi  → tampilkan batas kabupaten di dalam provinsi
                // nasional  → tampilkan batas provinsi seluruh Indonesia
                if (!bypassTerritory) {
                    if (userLevel === 'kab_kota' || userLevel === 'provinsi' || userWilayah || userDaerah) {
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

                                // Level kab_kota: filter ke satu kabupaten berdasarkan userWilayah
                                if (userLevel === 'kab_kota') {
                                    if (userWilayah) {
                                        var wadmkk = feature.properties.WADMKK || '';
                                        return matchTerritory(wadmkk, userWilayah);
                                    }
                                    // Fallback: jika wilayah tidak terisi, tampilkan semua kab di provinsi user
                                    if (userDaerah) {
                                        var wadmpr = feature.properties.WADMPR || '';
                                        return matchTerritory(wadmpr, userDaerah);
                                    }
                                    return true;
                                }

                                // Level provinsi: tampilkan semua kab/kota dalam provinsi user
                                if (userLevel === 'provinsi') {
                                    if (userDaerah) {
                                        var wadmpr2 = feature.properties.WADMPR || '';
                                        return matchTerritory(wadmpr2, userDaerah);
                                    }
                                    return true;
                                }

                                // Nasional: tampilkan semua (GeoJSON sudah provinsi)
                                if (!userDaerah && !userWilayah) return true;

                                if (userWilayah) {
                                    var wadmkk2 = feature.properties.WADMKK || '';
                                    return matchTerritory(wadmkk2, userWilayah);
                                } else if (userDaerah) {
                                    var wadmpr3 = feature.properties.WADMPR || '';
                                    return matchTerritory(wadmpr3, userDaerah);
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
                                var p = feature.properties || {};
                                var tooltipEl = document.createElement('div');
                                tooltipEl.style.cssText = 'font-size:12px; line-height:1.6; white-space:nowrap;';

                                if (isKabupaten) {
                                    // Nama kabupaten/kota dari GeoJSON (sudah administratif, bukan nama polres)
                                    var wadmkk = p.WADMKK || '';
                                    var wadmkc = p.WADMKC || ''; // Kecamatan
                                    var wadmkd = p.WADMKD || ''; // Desa/Kelurahan
                                    var tipadm = p.TIPADM;       // 1=Kabupaten, 2=Kota

                                    // Tambahkan prefix "Kab." atau "Kota" jika belum ada
                                    var kabLabel = wadmkk;
                                    if (kabLabel) {
                                        var lower = kabLabel.toLowerCase();
                                        if (lower.indexOf('kota') === -1 && lower.indexOf('kab') === -1) {
                                            kabLabel = (tipadm === 2 ? 'Kota ' : 'Kab. ') + kabLabel;
                                        }
                                    }

                                    // Susun baris tooltip dari bawah ke atas: Kab → Kec → Desa
                                    var lines = [];
                                    if (kabLabel) lines.push('<strong>' + kabLabel + '</strong>');
                                    if (wadmkc) lines.push('Kec. ' + wadmkc);
                                    if (wadmkd) lines.push(wadmkd);
                                    tooltipEl.innerHTML = lines.join('<br>');

                                } else {
                                    // Level Provinsi
                                    var prov = p.PROVINSI || p.Propinsi || p.PROPINSI || p.name || p.NAME_1 || '';
                                    if (prov && prov.toLowerCase().indexOf('provinsi') === -1) {
                                        prov = 'Provinsi ' + prov;
                                    }
                                    tooltipEl.innerHTML = '<strong>' + prov + '</strong>';
                                }

                                if (tooltipEl.innerHTML) {
                                    layer.bindTooltip(tooltipEl, {
                                        sticky: true,
                                        direction: 'auto',
                                        className: 'hud-style'
                                    });
                                }
                            }
                        }).addTo(map);

                        // Executive1: pastikan center/fit seluruh Indonesia (38 provinsi)
                        if (isExecutiveNational) {
                            var nationalBounds = geoJsonLayer.getBounds();
                            if (nationalBounds.isValid()) {
                                map.fitBounds(nationalBounds, { padding: [28, 28], maxZoom: 6 });
                            } else if (hasUserCenter) {
                                map.setView(defaultCenter, defaultZoom);
                            }
                        }

                        // Executive2: center & zoom ke provinsi/Polda kerja
                        if (isExecutiveProvinsi && userDaerah) {
                            var provinceBounds = geoJsonLayer.getBounds();
                            if (provinceBounds.isValid()) {
                                map.fitBounds(provinceBounds, { padding: [22, 22], maxZoom: 11 });
                                applyTerritoryZoom(9);
                            } else if (hasUserCenter) {
                                map.setView(defaultCenter, defaultZoom);
                                applyTerritoryZoom(9);
                            }
                        }

                        // Fit bounds ke wilayah berdasarkan userLevel
                        if (!bypassTerritory && !isExecutiveProvinsi && (userLevel === 'kab_kota' || userLevel === 'provinsi' || userDaerah || userWilayah)) {
                            var bounds = geoJsonLayer.getBounds();
                            if (bounds.isValid()) {
                                var fitOpts = { padding: [18, 18] };
                                if (userLevel === 'kab_kota' || userWilayah) {
                                    fitOpts.maxZoom = 13; // Zoom ke level kabupaten
                                } else if (userLevel === 'provinsi' || userDaerah) {
                                    fitOpts.maxZoom = 10; // Zoom ke level provinsi
                                }
                                map.fitBounds(bounds, fitOpts);
                                if (userLevel === 'kab_kota' || userWilayah) {
                                    applyTerritoryZoom(10);
                                } else if (userLevel === 'provinsi' || userDaerah) {
                                    applyTerritoryZoom(8);
                                }
                            } else if (hasUserCenter) {
                                map.setView(defaultCenter, defaultZoom);
                            }
                        }
                    })
                    .catch(function(err) {
                        console.log('GeoJSON warning:', err.message);
                    });
            })();

            // Check for query parameters lat and lng
            (function() {
                var urlParams = new URLSearchParams(window.location.search);
                var latParam = urlParams.get('lat');
                var lngParam = urlParams.get('lng');
                if (latParam && lngParam) {
                    var lat = parseFloat(latParam);
                    var lng = parseFloat(lngParam);
                    if (!isNaN(lat) && !isNaN(lng)) {
                        setTimeout(function() {
                            map.setView([lat, lng], 17);
                            var popupContent = '<b>Titik Lokasi Terpilih</b><br>Lat: ' + lat + '<br>Lng: ' + lng;
                            var nameParam = urlParams.get('name');
                            if (nameParam) {
                                popupContent = '<b>' + escHtml(nameParam) + '</b><br>' + popupContent;
                            }
                            L.popup()
                                .setLatLng([lat, lng])
                                .setContent(popupContent)
                                .openOn(map);
                        }, 800);
                    }
                }
            })();

        })();
    </script>
    @endpush
@endsection
