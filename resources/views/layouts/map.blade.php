<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Peta Monitoring') - Smart Management</title>
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" crossorigin="">
    <link rel="stylesheet" href="https://unpkg.com/leaflet.markercluster@1.4.1/dist/MarkerCluster.css" />
    <link rel="stylesheet" href="https://unpkg.com/leaflet.markercluster@1.4.1/dist/MarkerCluster.Default.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * { box-sizing: border-box; }
        body { margin: 0; padding: 0; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background: #050505; overflow: hidden; }
        #map { height: 100vh; width: 100%; background: #111; z-index: 1; cursor: default !important; }
        .leaflet-container { cursor: default !important; }
        .leaflet-interactive { cursor: pointer !important; }

        /* Leaflet default bisa memotong label; biarkan teks di bawah ikon tampil penuh */
        .leaflet-marker-icon.map-pin-divicon {
            overflow: visible !important;
        }

        .map-toolbar {
            position: absolute; right: 15px; top: 15px; z-index: 1000;
            display: flex; flex-direction: column; gap: 6px; padding: 8px;
            background: rgba(15, 15, 20, 0.92); backdrop-filter: blur(8px);
            border: 1px solid #333; border-radius: 12px; box-shadow: 0 4px 20px rgba(0, 0, 0, 0.5);
        }
        .map-toolbar a, .map-toolbar .btn-map {
            height: 36px; padding: 0 16px; border-radius: 18px; display: flex; align-items: center; justify-content: center; gap: 8px;
            cursor: pointer; text-decoration: none; color: inherit; box-shadow: 0 2px 6px rgba(0, 0, 0, 0.4);
            transition: transform 0.2s; border: 2px solid; font-size: 12px; font-weight: bold;
        }
        .map-toolbar a:hover, .map-toolbar .btn-map:hover { transform: scale(1.05); }
        .btn-map-home { background: rgba(0, 20, 50, 0.9); border-color: #007bff; color: #007bff; }
        .btn-map-logout { background: rgba(20, 0, 0, 0.9); border-color: #dc3545; color: #dc3545; }

        .layer-switch {
            position: absolute; top: 15px; left: 50%; transform: translateX(-50%); z-index: 1000;
            background: rgba(15, 15, 20, 0.92); backdrop-filter: blur(8px); border: 1px solid #333;
            border-radius: 10px; display: flex; align-items: center; height: 36px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.5); font-size: 11px; font-weight: 600;
        }
        .ls-opt {
            padding: 0 14px; height: 100%; display: flex; align-items: center; cursor: pointer;
            color: #ccc; transition: 0.2s; border-right: 1px solid #444;
        }
        .ls-opt:last-child { border-right: none; }
        .ls-opt:hover { background: #333; color: #fff; }
        .ls-opt.active { background: #007bff; color: white; }

        /* Wrapper pin: ikon + teks (jarak diatur di sini, bukan position:absolute mepet) */
        .map-pin-root {
            position: relative;
            display: flex;
            flex-direction: column;
            align-items: center;
            width: max-content;
            max-width: 220px;
            pointer-events: none;
        }
        .map-pin-root .map-pin-circle {
            pointer-events: auto;
            flex-shrink: 0;
        }
        .map-label {
            margin-top: 7px;
            padding: 1px 6px 0;
            color: #fff;
            font-weight: 800;
            font-size: 11px;
            line-height: 1.35;
            text-shadow: 0 0 4px #000, 0 1px 2px rgba(0,0,0,0.9);
            text-align: center;
            white-space: normal;
            word-break: break-word;
            max-width: 220px;
            pointer-events: none;
            opacity: 0;
            transition: opacity 0.15s ease-in-out;
        }
        .leaflet-marker-icon:hover .map-label {
            opacity: 1;
        }
        .leaflet-tooltip.hud-style {
            background: rgba(20, 20, 20, 0.95); backdrop-filter: blur(4px); border: 1px solid #555;
            border-radius: 6px; padding: 8px 10px; color: #eee; font-size: 12px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.9);
        }

        .map-legend {
            position: absolute; left: 12px; bottom: 52px; z-index: 1000;
            background: rgba(15, 15, 20, 0.92); backdrop-filter: blur(8px);
            border: 1px solid #333; border-radius: 10px; padding: 10px 12px;
            font-size: 11px; color: #ddd; max-width: 200px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.5);
        }
        .map-legend-title { font-weight: 700; color: #fff; margin-bottom: 8px; font-size: 11px; letter-spacing: 0.02em; }
        .map-legend-row { display: flex; align-items: center; gap: 8px; margin-bottom: 6px; line-height: 1.2; }
        .map-legend-row:last-child { margin-bottom: 0; }
        .map-legend-dot {
            width: 28px; height: 28px; border-radius: 50%; border: 2px solid #fff;
            display: flex; align-items: center; justify-content: center; flex-shrink: 0;
            color: #fff; font-size: 12px; box-shadow: 0 2px 6px rgba(0,0,0,0.45);
        }
        .map-legend-divider { height: 1px; background: #444; margin: 8px 0; }
    </style>
    @stack('styles')
</head>
<body>
    @yield('content')

    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" crossorigin=""></script>
    <script src="https://unpkg.com/leaflet.markercluster@1.4.1/dist/leaflet.markercluster.js"></script>
    @stack('scripts')
</body>
</html>
