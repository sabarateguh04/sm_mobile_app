<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="generator" content="SatuPeta Laravel">
    <title>{{ $title ?? 'SatuPeta' }}</title>
    <link rel="icon" href="{{ satupeta_asset('logo/korlantas.png') }}" type="image/x-icon"/>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">

    <link href="{{ satupeta_asset('css/satupeta/custom_new.css') }}" rel="stylesheet" />
    <link href="{{ satupeta_asset('vendor/p-scroll/perfect-scrollbar.css') }}" rel="stylesheet" />
    <link href="{{ satupeta_asset('vendor/iconfonts/icons.css') }}" rel="stylesheet" />
    <link href="{{ satupeta_asset('vendor/iconfonts/font-awesome/font-awesome.min.css') }}" rel="stylesheet">
    <link href="{{ satupeta_asset('vendor/iconfonts/plugin.css') }}" rel="stylesheet" />
    <link href="{{ satupeta_asset('vendor/select2/select2.min.css') }}" rel="stylesheet" />
    <link href="{{ satupeta_asset('css/apexcharts.css') }}" rel="stylesheet" />
    <link rel="stylesheet" href="{{ satupeta_asset('vendor/jquery-fancybox/jquery.fancybox.min.css') }}">
    <link href="{{ satupeta_asset('css/custom.css') }}" rel="stylesheet" />
    <link rel="stylesheet" href="{{ satupeta_asset('vendor/owl/owl.carousel.min.css') }}">
    <link rel="stylesheet" href="{{ satupeta_asset('vendor/owl/owl.theme.default.min.css') }}">
    <link rel="stylesheet" href="{{ satupeta_asset('vendor/bootstrap/css/bootstrap5.2.min.css') }}">
    <link rel="stylesheet" href="{{ satupeta_asset('vendor/datatables/datatables.min.css') }}">
    <link href="https://vjs.zencdn.net/8.6.1/video-js.css" rel="stylesheet" />
    <link href="{{ satupeta_asset('vendor/daterangepicker/daterangepicker.css') }}" rel="stylesheet" />

    @foreach ($css_extra ?? [] as $css)
        <link href="{{ satupeta_asset($css) }}" rel="stylesheet" />
    @endforeach

    @if (!empty($use_osm_map))
        <link href="{{ satupeta_asset('vendor/leaflet/leaflet.css') }}" rel="stylesheet" />
        <link href="{{ satupeta_asset('vendor/leaflet/MarkerCluster.css') }}" rel="stylesheet" />
        <link href="{{ satupeta_asset('vendor/leaflet/MarkerCluster.Default.css') }}" rel="stylesheet" />
    @endif

    <style>
        #custom-content {
            position: fixed;
            inset: 0;
            z-index: 2;
            pointer-events: none;
        }
        #custom-content > * {
            pointer-events: auto;
        }
        #mapid {
            z-index: 0;
            position: fixed !important;
            inset: 0 !important;
            width: 100vw !important;
            height: 100vh !important;
            min-height: 100vh !important;
            background: #101828;
        }
    </style>

    <script src="{{ satupeta_asset('vendor/bootstrap/js/jquery3.5.1.min.js') }}"></script>
    <script src="{{ satupeta_asset('vendor/bootstrap/js/popper.min.js') }}"></script>
    <script src="{{ satupeta_asset('vendor/bootstrap/js/bootstrap5.2.min.js') }}"></script>

    @stack('styles')
</head>
<body class="satupeta-body">
    @yield('content')

    <script src="{{ satupeta_asset('vendor/owl/owl.carousel.min.js') }}"></script>
    <script src="{{ satupeta_asset('js/popover.js') }}"></script>
    <script src="{{ satupeta_asset('vendor/select2/select2.min.js') }}"></script>
    <script src="{{ satupeta_asset('js/apexcharts.js') }}"></script>
    <script src="{{ satupeta_asset('vendor/sweet-alert/sweetalert.min.js') }}"></script>
    <script src="{{ satupeta_asset('vendor/datatables/datatables.min.js') }}"></script>
    <script src="https://vjs.zencdn.net/8.6.1/video.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/hls.js@latest"></script>

    @foreach ($js_array_top ?? [] as $js)
        <script src="{{ satupeta_asset('js_local/' . $js) }}"></script>
    @endforeach

    @if (!empty($use_osm_map))
        <script>window.SATUPETA_MAP_PROVIDER = 'openstreetmap';</script>
        <script src="{{ satupeta_asset('vendor/leaflet/leaflet.js') }}"></script>
        <script src="{{ satupeta_asset('vendor/leaflet/leaflet.markercluster.js') }}"></script>
        <script src="{{ satupeta_asset('satupeta/js/heatmap.js') }}"></script>
        <script src="{{ satupeta_asset('satupeta/js/leaflet-heatmap.js') }}"></script>
        <script src="{{ satupeta_asset('js_local/satupeta/map_osm.js') }}"></script>
    @else
        <script>window.SATUPETA_MAP_PROVIDER = 'google';</script>
    @endif

    @if (!empty($js_local))
        <script>
            window.SATUPETA_LAT = {{ json_encode($user_lat ?? (float) config('satupeta.lat_center')) }};
            window.SATUPETA_LNG = {{ json_encode($user_lng ?? (float) config('satupeta.lng_center')) }};
        </script>
        <script src="{{ satupeta_asset('js_local/' . $js_local) }}"></script>
    @endif

    <script>
        @php
            $satupetaApiUrl = (config('satupeta.use_api_proxy') && config('satupeta.url_api'))
                ? rtrim(url('api/satupeta'), '/') . '/'
                : rtrim((string) config('satupeta.url_api'), '/') . '/';
        @endphp
        var global_url_api = @json($satupetaApiUrl);
        var SATUPETA_API_TOKEN = @json(config('satupeta.api_token'));
        var global_url_ci = @json(rtrim(url('/'), '/') . '/');
        var global_url_stream = @json(rtrim((string) config('satupeta.url_stream'), '/') . '/');
        var global_url_stream_go = @json(config('satupeta.url_stream_go'));
        var global_url_tomtom = @json(config('satupeta.url_tomtom'));
        var global_key_tomtom = @json(config('satupeta.key_tomtom'));
        var global_bbox_tomtom = @json(config('satupeta.bbox_tomtom'));
        var global_sbearer = @json(config('satupeta.s_bearer'));
        var global_bounds = @json(config('satupeta.bounds'));
        var global_minzoom = @json(config('satupeta.min_zoom'));
        var global_maxzoom = @json(config('satupeta.max_zoom'));

        @if (empty(config('satupeta.url_api')))
        console.warn('URL_BE belum di-set di .env — layer data memakai DB lokal / endpoint Laravel saja.');
        @endif

        $('#form_filter').submit(function (e) {
            e.preventDefault();
            if (typeof prom_get_vip === 'function') {
                prom_get_vip().then(function () {
                    if (typeof list_vip === 'function') list_vip();
                });
            }
        });
    </script>

    @foreach ($js_array_bot ?? [] as $js)
        <script src="{{ satupeta_asset('js_local/' . $js) }}"></script>
    @endforeach

    <script src="{{ satupeta_asset('js_local/stream_whep.js') }}"></script>
    <script src="{{ satupeta_asset('js_local/satupeta/custom.js') }}"></script>
    <script src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css" />

    @stack('scripts')
</body>
</html>
