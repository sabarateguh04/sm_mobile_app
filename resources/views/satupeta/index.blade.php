@extends('layouts.app')

@section('title', 'Peta Monitoring (SatuPeta)')

@push('styles')
    <!-- CSS Satupeta -->
    <link href="{{ asset('satupeta_assets/css/satupeta/custom_peta.css') }}" rel="stylesheet" />
    <link href="{{ asset('satupeta_assets/css/satupeta/custom_car.css') }}" rel="stylesheet" />
    <style>
        /* Agar peta full screen di layout SBO */
        #main-content {
            padding: 0 !important;
        }
        #map {
            width: 100%;
            height: calc(100vh - 56px); /* Kurangi tinggi topbar SBO */
            margin: 0;
            padding: 0;
            position: relative;
        }
        .satupeta-menus {
            position: absolute;
            z-index: 1000;
            top: 20px;
            left: 50%;
            transform: translateX(-50%);
            background: rgba(255, 255, 255, 0.8);
            padding: 10px;
            border-radius: 50px;
            display: flex;
            gap: 10px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }
        .satupeta-menus img {
            height: 40px;
            cursor: pointer;
            transition: transform 0.2s;
        }
        .satupeta-menus img:hover {
            transform: scale(1.1);
        }
    </style>
@endpush

@section('content')
    <!-- Filter Hirarki Hidden untuk dibaca oleh JS -->
    <input type="hidden" id="polda_id" value="{{ $polda_id }}">
    <input type="hidden" id="polres_id" value="{{ $polres_id }}">
    <input type="hidden" id="daerah" value="{{ $daerah }}">
    <input type="hidden" id="wilayah" value="{{ $wilayah }}">
    <input type="hidden" id="global_url_ci" value="{{ url('satupeta-api') }}/">
    <input type="hidden" id="base_url" value="{{ url('/') }}/">

    <!-- Audio Player Alert -->
    <audio id="musicplayer">
        <source src="{{ asset('satupeta_assets/audio/audio.mp3') }}" />
    </audio>

    <!-- Kontainer Peta -->
    <div id="map"></div>

    <!-- Menu Tombol (Adaptasi dari Satupeta Legacy) -->
    <div class="satupeta-menus">
        <a onclick="get_cstm_peta()"><img title="MAP" src="{{ asset('satupeta_assets/logo/menu_mapping.png') }}" alt="MAP"></a>
        <a onclick="get_list_kml()"><img title="PRAOPS" src="{{ asset('satupeta_assets/logo/menu_praops.png') }}" alt="PRAOPS"></a>
        <a onclick="get_list_mgmt_route()"><img title="MANAGEMENT ROUTE" src="{{ asset('satupeta_assets/logo/menu_route.png') }}" alt="ROUTE"></a>
        <a onclick="get_list_petugas()"><img title="PETUGAS" src="{{ asset('satupeta_assets/logo/petugas.svg') }}" alt="PETUGAS"></a>
        <a onclick="get_list_indicar()"><img title="KENDARAAN" src="{{ asset('satupeta_assets/logo/menu_kendaraan.png') }}" alt="KENDARAAN"></a>
        <a onclick="get_list_cctv()"><img title="CCTV" src="{{ asset('satupeta_assets/logo/menu_cctv.png') }}" alt="CCTV"></a>
        <a onclick="get_list_vip()"><img title="VIP" src="{{ asset('satupeta_assets/logo/menu_vip.png') }}" alt="VIP"></a>
        <a onclick="show_titik('posko')"><img title="POSKO" src="{{ asset('satupeta_assets/logo/menu_posko.png') }}" alt="POSKO"></a>
        <a onclick="show_titik('black_spot')"><img title="BLACK SPOT" src="{{ asset('satupeta_assets/logo/menu_blackspot.png') }}" alt="BLACK SPOT"></a>
        <a onclick="show_titik('trouble_spot')"><img title="TROUBLE SPOT" src="{{ asset('satupeta_assets/logo/menu_troublespot.png') }}" alt="TROUBLE SPOT"></a>
    </div>

    <!-- Sidebar Detail Kiri (Disembunyikan default) -->
    <div class="position-absolute d-none" style="z-index:1000; top:100px; left:20px; width:300px; background:white; padding:15px; border-radius:10px; box-shadow:0 4px 6px rgba(0,0,0,0.1);" id="cstm-content">
        <!-- Akan diisi oleh JS -->
    </div>
@endsection

@push('scripts')
    <!-- Google Maps JS API -->
    <script src="https://maps.googleapis.com/maps/api/js?key={{ env('API_KEY_GOOGLE') }}&libraries=places,drawing,geometry&v=weekly"></script>
    
    <!-- Script Peta Utama -->
    <script>
        var map;
        var markers = [];
        
        function initMap() {
            map = new google.maps.Map(document.getElementById("map"), {
                center: { lat: {{ $lat }}, lng: {{ $lng }} },
                zoom: {{ env('ZOOM_PETA', 12) }},
                mapTypeId: "roadmap",
                disableDefaultUI: true,
                zoomControl: true,
            });
        }

        $(document).ready(function() {
            initMap();
        });

        // Dummy functions (Akan ditimpa oleh file satupeta_assets/js/peta.js nanti)
        function get_cstm_peta() { alert('Menu Mapping (Akan terhubung ke API Laravel)'); }
        function get_list_cctv() { alert('CCTV (Akan terhubung ke API Laravel)'); }
        function show_titik(jenis) { alert('Menampilkan ' + jenis + ' (Akan terhubung ke API Laravel)'); }
        function get_list_indicar() { alert('Menampilkan Kendaraan'); }
        function get_list_kml() { alert('Menampilkan KML'); }
        function get_list_mgmt_route() { alert('Menampilkan Manajemen Route'); }
        function get_list_petugas() { alert('Menampilkan Petugas'); }
        function get_list_vip() { alert('Menampilkan VIP'); }
    </script>
    
    <!-- Sertakan file JS asli dari satupeta (pastikan sudah dicopy) -->
    <!-- <script src="{{ asset('satupeta_assets/js/peta.js') }}"></script> -->
    <!-- <script src="{{ asset('satupeta_assets/js/cctv.js') }}"></script> -->
@endpush
