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
    .custom-cluster-icon i {
        font-size: 24px;
        color: #fff;
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

    /* ── 3D Marker Pin: sphere + specular highlight + pointed tail ── */
    .pin3d-box {
        position: relative;
        display: flex;
        flex-direction: column;
        align-items: center;
        transition: transform 0.25s cubic-bezier(0.34, 1.56, 0.64, 1), filter 0.25s ease;
        filter: drop-shadow(0 5px 10px rgba(0,0,0,0.5));
    }
    .pin3d-box:hover {
        transform: translateY(-5px) scale(1.12);
        filter: drop-shadow(0 10px 22px rgba(0,0,0,0.72));
    }
    .pin3d-sphere {
        position: relative;
        width: 32px;
        height: 32px;
        border-radius: 50%;
        border: 2px solid rgba(255,255,255,0.5);
        display: flex;
        align-items: center;
        justify-content: center;
        overflow: visible;
        box-shadow:
            inset 0 -4px 8px rgba(0,0,0,0.32),
            inset 0 3px 5px rgba(255,255,255,0.22),
            0 4px 12px rgba(0,0,0,0.5);
        z-index: 2;
    }
    /* Specular highlight — simulates overhead light */
    .pin3d-sphere::before {
        content: '';
        position: absolute;
        top: 3px;
        left: 5px;
        width: 11px;
        height: 7px;
        background: rgba(255,255,255,0.52);
        border-radius: 50%;
        filter: blur(2px);
        z-index: 3;
        pointer-events: none;
    }
    /* Pointed tail */
    .pin3d-sphere::after {
        content: '';
        position: absolute;
        bottom: -7px;
        left: 50%;
        transform: translateX(-50%);
        width: 0;
        height: 0;
        border-left: 7px solid transparent;
        border-right: 7px solid transparent;
        border-top: 9px solid var(--pin-color, #EA4335);
        filter: brightness(0.65);
        z-index: 1;
    }
    /* Marker icon inside sphere */
    .pin3d-icon-font {
        position: relative;
        font-size: 16px;
        color: #fff;
        z-index: 4;
        text-shadow: 0 1px 3px rgba(0,0,0,0.6);
    }
    .pin3d-icon-img {
        position: relative;
        z-index: 4;
        filter: drop-shadow(0 1px 2px rgba(0,0,0,0.5)) brightness(1.08);
    }
    /* Ground shadow ellipse below pin */
    .pin3d-ground-shadow {
        width: 12px;
        height: 3px;
        background: radial-gradient(ellipse, rgba(0,0,0,0.45) 0%, transparent 75%);
        border-radius: 50%;
        margin-top: 4px;
    }

    /* ── Premium Dark Glassmorphism Popup ── */
    .leaflet-popup-content-wrapper {
        background: rgba(8, 14, 30, 0.93) !important;
        backdrop-filter: blur(20px) saturate(1.5) !important;
        -webkit-backdrop-filter: blur(20px) saturate(1.5) !important;
        border: 1px solid rgba(255,255,255,0.1) !important;
        border-radius: 16px !important;
        box-shadow:
            0 24px 64px rgba(0,0,0,0.75),
            0 0 0 1px rgba(255,255,255,0.04) inset !important;
        padding: 0 !important;
        animation: popup-slide-in 0.22s cubic-bezier(0.34, 1.56, 0.64, 1) both;
    }
    @keyframes popup-slide-in {
        from { opacity: 0; transform: scale(0.88) translateY(10px); }
        to   { opacity: 1; transform: scale(1) translateY(0); }
    }
    .leaflet-popup-content {
        margin: 0 !important;
        color: #f1f5f9 !important;
    }
    .leaflet-popup-tip {
        background: rgba(8, 14, 30, 0.93) !important;
        box-shadow: none !important;
    }
    .leaflet-popup-close-button {
        color: rgba(255,255,255,0.45) !important;
        font-size: 16px !important;
        top: 6px !important;
        right: 8px !important;
        width: 22px !important;
        height: 22px !important;
        line-height: 22px !important;
        text-align: center !important;
        border-radius: 50% !important;
        padding: 0 !important;
        transition: all 0.2s !important;
        background: rgba(255,255,255,0.06) !important;
    }
    .leaflet-popup-close-button:hover {
        color: #fff !important;
        background: rgba(255,255,255,0.15) !important;
    }
    /* Premium popup inner wrapper — left connection line */
    .popup-premium {
        padding: 14px 16px 14px 24px;
        font-size: 13px;
        line-height: 1.55;
        color: #e2e8f0;
        position: relative;
    }
    
    /* -- Leader Line / Garis dari popup ke marker -- */
    .leaflet-popup-tip-container {
        width: 40px !important;
        height: 40px !important;
        margin-left: -20px !important;
        bottom: -40px !important;
        overflow: visible !important;
    }
    .leaflet-popup-tip {
        background: transparent !important;
        border: none !important;
        box-shadow: none !important;
        width: 100% !important;
        height: 100% !important;
        margin: 0 !important;
        padding: 0 !important;
        transform: none !important;
    }
    .leaflet-popup-tip::after {
        content: '';
        position: absolute;
        top: 0;
        left: 50%;
        width: 2px;
        height: 70px;
        background: linear-gradient(to bottom, #38bdf8 0%, rgba(56,189,248,0.1) 100%);
        box-shadow: 0 0 10px #38bdf8;
        transform: rotate(-45deg);
        transform-origin: top left;
        pointer-events: none;
    }

    /* Vertical jalur koneksi (connection track) di dalam popup */
    .popup-premium::before {
        content: '';
        position: absolute;
        left: 8px;
        top: 12px;
        bottom: 12px;
        width: 3px;
        background:
            radial-gradient(circle 8px at 1.5px 0%,   rgba(56,189,248,1)   0%, transparent 100%),
            radial-gradient(circle 8px at 1.5px 100%, rgba(139,92,246,1)   0%, transparent 100%),
            linear-gradient(to bottom,
                rgba(56,189,248,0.95)  0%,
                rgba(99,102,241,0.9)  50%,
                rgba(139,92,246,0.95) 100%
            );
        border-radius: 2px;
        box-shadow: 0 0 10px rgba(56,189,248,0.35), 0 0 22px rgba(99,102,241,0.15);
    }
    /* Node titik atas — pulsing cyan glow */
    .popup-premium::after {
        content: '';
        position: absolute;
        left: 3.5px;
        top: 9px;
        width: 9px;
        height: 9px;
        border-radius: 50%;
        background: radial-gradient(circle, #bae6fd 0%, #38bdf8 55%, #0284c7 100%);
        box-shadow: 0 0 8px rgba(56,189,248,1), 0 0 18px rgba(56,189,248,0.55);
        animation: popup-node-pulse 2.4s ease-in-out infinite;
        z-index: 5;
    }
    @keyframes popup-node-pulse {
        0%, 100% {
            box-shadow: 0 0 6px rgba(56,189,248,0.9), 0 0 14px rgba(56,189,248,0.45);
            transform: scale(1);
        }
        50% {
            box-shadow: 0 0 12px rgba(56,189,248,1), 0 0 26px rgba(56,189,248,0.65);
            transform: scale(1.18);
        }
    }
    .popup-premium strong {
        color: #f8fafc;
    }
    .popup-premium a.btn {
        display: inline-flex !important;
        align-items: center !important;
        gap: 5px !important;
        border-radius: 8px !important;
        font-weight: 700 !important;
        font-size: 11px !important;
        padding: 5px 10px !important;
        text-decoration: none !important;
        transition: filter 0.2s, transform 0.2s !important;
        box-shadow: 0 2px 8px rgba(0,0,0,0.35) !important;
    }
    .popup-premium a.btn:hover {
        filter: brightness(1.25) !important;
        transform: translateY(-1px) !important;
    }
    .popup-premium img[style*="border-radius:4px"] {
        border: 1px solid rgba(255,255,255,0.1);
        box-shadow: 0 2px 8px rgba(0,0,0,0.4);
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
    .map-legend-row i {
        font-size: 20px !important;
        color: #fff;
        transition: all 0.2s;
    }
    .map-legend-row.inactive i {
        opacity: 0.4;
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

@if(request()->query('debug') == '1')
<style>
    /* Premium Debug Panel Styles */
    .debug-panel {
        position: fixed;
        top: 80px;
        right: 20px;
        width: 380px;
        max-height: 80vh;
        z-index: 2000;
        background: rgba(15, 23, 42, 0.9);
        backdrop-filter: blur(12px);
        border: 1px solid rgba(255, 255, 255, 0.15);
        border-radius: 16px;
        color: #f8fafc;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.5);
        font-family: inherit;
        display: flex;
        flex-direction: column;
        overflow: hidden;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    }
    .debug-panel.collapsed {
        width: 220px;
        max-height: 48px;
    }
    .debug-panel-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 12px 16px;
        background: rgba(30, 41, 59, 0.7);
        border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        cursor: pointer;
        user-select: none;
    }
    .debug-panel-title {
        font-size: 13px;
        font-weight: 700;
        display: flex;
        align-items: center;
        gap: 8px;
        color: #f43f5e;
    }
    .debug-panel-toggle-btn {
        background: none;
        border: none;
        color: #94a3b8;
        cursor: pointer;
        font-size: 12px;
        padding: 4px;
        transition: color 0.2s;
    }
    .debug-panel-toggle-btn:hover {
        color: #f1f5f9;
    }
    .debug-panel-body {
        padding: 16px;
        overflow-y: auto;
        font-size: 11px;
        line-height: 1.5;
        flex: 1;
        display: flex;
        flex-direction: column;
        gap: 12px;
    }
    .debug-panel-summary {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 8px;
    }
    .debug-summary-item {
        background: rgba(255, 255, 255, 0.05);
        border: 1px solid rgba(255, 255, 255, 0.05);
        padding: 8px 10px;
        border-radius: 8px;
    }
    .debug-summary-label {
        color: #94a3b8;
        font-size: 9px;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        margin-bottom: 4px;
    }
    .debug-summary-value {
        font-size: 13px;
        font-weight: 700;
        color: #f8fafc;
    }
    .debug-summary-value.success {
        color: #10b981;
    }
    .debug-summary-value.danger {
        color: #ef4444;
    }
    .debug-section-title {
        font-weight: 700;
        font-size: 10px;
        text-transform: uppercase;
        color: #38bdf8;
        border-bottom: 1px solid rgba(56, 189, 248, 0.2);
        padding-bottom: 4px;
        margin: 0;
    }
    .debug-user-info {
        background: rgba(56, 189, 248, 0.05);
        border: 1px solid rgba(56, 189, 248, 0.1);
        border-radius: 8px;
        padding: 8px 12px;
    }
    .debug-operator-list {
        display: flex;
        flex-direction: column;
        gap: 8px;
        max-height: 200px;
        overflow-y: auto;
        padding-right: 4px;
    }
    .debug-operator-item {
        background: rgba(15, 23, 42, 0.6);
        border: 1px solid rgba(255, 255, 255, 0.06);
        border-left-width: 4px;
        padding: 8px;
        border-radius: 6px;
        position: relative;
    }
    .debug-operator-item.visible {
        border-left-color: #10b981;
    }
    .debug-operator-item.hidden {
        border-left-color: #ef4444;
    }
    .debug-operator-header {
        display: flex;
        justify-content: space-between;
        font-weight: 700;
        margin-bottom: 4px;
    }
    .debug-operator-name {
        color: #f1f5f9;
        font-size: 11px;
    }
    .debug-operator-status {
        font-size: 9px;
        font-weight: 700;
        padding: 1px 6px;
        border-radius: 4px;
        color: #fff;
    }
    .debug-operator-status.online { background: #10b981; }
    .debug-operator-status.bertugas { background: #f59e0b; }
    .debug-operator-status.offline { background: #64748b; }
    .debug-operator-details {
        font-size: 9px;
        color: #cbd5e1;
        margin-top: 4px;
    }
    .debug-operator-reasons {
        margin-top: 6px;
        background: rgba(239, 68, 68, 0.1);
        border: 1px dashed rgba(239, 68, 68, 0.25);
        color: #fca5a5;
        padding: 4px 6px;
        border-radius: 4px;
        font-size: 9px;
    }
    .debug-panel-actions {
        display: flex;
        gap: 8px;
    }
    .btn-debug-action {
        flex: 1;
        background: rgba(56, 189, 248, 0.2);
        border: 1px solid rgba(56, 189, 248, 0.4);
        color: #38bdf8;
        padding: 8px 12px;
        border-radius: 8px;
        cursor: pointer;
        font-weight: 700;
        font-size: 10px;
        text-align: center;
        text-decoration: none;
        transition: all 0.2s;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 6px;
    }
    .btn-debug-action:hover {
        background: rgba(56, 189, 248, 0.35);
        color: #fff;
    }
</style>
@endif

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

    @if(request()->query('debug') == '1')
        <div class="debug-panel" id="debugPanel">
            <div class="debug-panel-header" onclick="toggleDebugPanel()">
                <div class="debug-panel-title">
                    <i class="fas fa-bug"></i> Map Operator Debugger
                </div>
                <button class="debug-panel-toggle-btn" id="debugToggleIcon">
                    <i class="fas fa-chevron-right"></i>
                </button>
            </div>
            <div class="debug-panel-body" id="debugPanelBody">
                <div class="debug-panel-summary">
                    <div class="debug-summary-item">
                        <div class="debug-summary-label">Table Exist</div>
                        <div class="debug-summary-value" id="dbgTableStatus">-</div>
                    </div>
                    <div class="debug-summary-item">
                        <div class="debug-summary-label">Active Markers</div>
                        <div class="debug-summary-value success" id="dbgActiveMarkers">-</div>
                    </div>
                </div>

                <div class="debug-section-title">User Scope</div>
                <div class="debug-user-info" id="dbgUserInfo">
                    Loading user data...
                </div>

                <div class="debug-section-title">Operators Status (USM & Tracking)</div>
                <div class="debug-operator-list" id="dbgOperatorList">
                    Loading operators diagnostics...
                </div>

                <div class="debug-section-title">Contingency (Layanan Masy)</div>
                <div class="debug-operator-list" id="dbgContingencyList" style="max-height: 150px;">
                    Loading contingency diagnostics...
                </div>

                <div class="debug-panel-actions">
                    <button class="btn-debug-action" onclick="fetchMapDiagnostics()">
                        <i class="fas fa-sync-alt"></i> Refresh
                    </button>
                    <a href="{{ route('map.debug') }}" target="_blank" class="btn-debug-action">
                        <i class="fas fa-external-link-alt"></i> Raw JSON
                    </a>
                </div>
            </div>
        </div>
    @endif

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
                <div class="map-legend-row" data-layer="terminal" data-title="Terminal bus"><i class="fas fa-bus-alt"></i></div>
                <div class="map-legend-row" data-layer="stasiun" data-title="Stasiun kereta"><i class="fas fa-train"></i></div>
                <div class="map-legend-row" data-layer="bandara" data-title="Bandar udara"><i class="fas fa-plane"></i></div>
                <div class="map-legend-row" data-layer="pelabuhan" data-title="Pelabuhan"><i class="fas fa-ship"></i></div>
            </div>

            {{-- ── Operasional Umum (statis) ── --}}
            <div class="legend-group">
                <div class="map-legend-row" data-layer="posko" data-title="Posko"><i class="fas fa-building-shield"></i></div>
                <div class="map-legend-row" data-layer="gangguan" data-title="Gangguan jalan"><i class="fas fa-triangle-exclamation fa-shake" style="--fa-animation-duration: 2s;"></i></div>
                <div class="map-legend-row" data-layer="tj" data-title="Turjagwali"><i class="fas fa-person-military-pointing"></i></div>
                <div class="map-legend-row" data-layer="aa" data-title="Audit Arus"><i class="fas fa-chart-line"></i></div>
                <div class="map-legend-row" data-layer="pa" data-title="PNP Angkutan"><i class="fas fa-van-shuttle"></i></div>
            </div>

            {{-- ── Fasilitas Umum (statis) ── --}}
            <div class="legend-group">
                <div class="map-legend-row" data-layer="wisata" data-title="Objek Wisata"><i class="fas fa-map-location-dot"></i></div>
                <div class="map-legend-row" data-layer="ibadah" data-title="Tempat Ibadah"><i class="fas fa-place-of-worship"></i></div>
                <div class="map-legend-row" data-layer="faskes" data-title="Data Faskes"><i class="fas fa-hospital"></i></div>
                <div class="map-legend-row" data-layer="cctv" data-title="CCTV Live"><i class="fas fa-video fa-beat"></i></div>
            </div>

            {{-- ── Operasional Khusus ── --}}
            <div class="legend-group">
                <div class="map-legend-row" data-layer="con" data-title="Contigency (Kejadian Darurat)"><i class="fas fa-truck-medical fa-fade"></i></div>
                <div class="map-legend-row" data-layer="ts" data-title="Traffic Stuck (Kemacetan)"><i class="fas fa-car-burst fa-shake" style="--fa-animation-duration: 2s;"></i></div>
                <div class="map-legend-row" data-layer="petugas" data-title="Petugas Operator (Online)"><i class="fas fa-user-shield fa-bounce" style="--fa-animation-duration: 2s;"></i></div>
                <div class="map-legend-row" data-layer="layanan_data" data-title="Layanan Data"><i class="fas fa-users-viewfinder"></i></div>
                <div class="map-legend-row" data-layer="opsdal" data-title="Opsdal"><i class="fas fa-bullseye fa-beat"></i></div>
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
            var petugasUrl      = '{{ route('map.petugas') }}';

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
            // CLEAN = Basemap premium (CartoDB Voyager) - tampilan bersih & elegan, warna selaras dengan pin ala Google
            var mapClean2D = L.tileLayer('https://{s}.basemaps.cartocdn.com/rastertiles/voyager/{z}/{x}/{y}{r}.png', {
                attribution: 'CartoDB', subdomains: 'abcd', maxZoom: 20, maxNativeZoom: 20
            });
            // GELAP = Basemap premium gelap (CartoDB Dark Matter, dengan label jalan/wilayah)
            var googleStreets = L.tileLayer('https://{s}.basemaps.cartocdn.com/dark_all/{z}/{x}/{y}{r}.png', {
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

            /**
             * Jaga-jaga di sisi klien: buang koordinat sampah (null-island, salah ketik desimal,
             * di luar rentang lat/lng valid) supaya tidak ikut fitBounds dan merusak tampilan peta
             * (mis. peta ter-zoom ke seluruh dunia gara-gara satu titik nyasar).
             */
            function isValidCoordinate(lat, lng) {
                return typeof lat === 'number' && typeof lng === 'number' &&
                    isFinite(lat) && isFinite(lng) &&
                    !(lat === 0 && lng === 0) &&
                    Math.abs(lat) <= 90 && Math.abs(lng) <= 180;
            }

            /** Tinggi area pin (ikon + jarak + label); anchor = ujung bawah pin (ala Google Maps) supaya titik koordinat tepat di ujung pin */
            var PIN_WRAP_W = 150;
            var PIN_WRAP_H = 96;
            var PIN_ANCHOR_X = PIN_WRAP_W / 2;
            var PIN_SIZE = 32;
            var PIN_ANCHOR_Y = PIN_SIZE;

            function createClusterGroup(iconClass) {
                return L.markerClusterGroup({
                    showCoverageOnHover: false,
                    maxClusterRadius: 55,
                    spiderfyOnMaxZoom: true,
                    iconCreateFunction: function(cluster) {
                        var childCount = cluster.getChildCount();
                        return L.divIcon({
                            html: '<div class="custom-cluster-icon">' +
                                    '<i class="' + iconClass + '"></i>' +
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
                'terminal': createClusterGroup('fas fa-bus-alt').addTo(map),
                'stasiun': createClusterGroup('fas fa-train').addTo(map),
                'bandara': createClusterGroup('fas fa-plane').addTo(map),
                'pelabuhan': createClusterGroup('fas fa-ship').addTo(map),
                'posko': createClusterGroup('fas fa-building-shield').addTo(map),
                'gangguan': createClusterGroup('fas fa-triangle-exclamation fa-shake').addTo(map),
                'con': createClusterGroup('fas fa-truck-medical fa-fade').addTo(map),
                'ts': createClusterGroup('fas fa-car-burst fa-shake').addTo(map),
                'tj': createClusterGroup('fas fa-person-military-pointing').addTo(map),
                'aa': createClusterGroup('fas fa-chart-line').addTo(map),
                'pa': createClusterGroup('fas fa-van-shuttle').addTo(map),
                'wisata': createClusterGroup('fas fa-map-location-dot').addTo(map),
                'ibadah': createClusterGroup('fas fa-place-of-worship').addTo(map),
                'faskes': createClusterGroup('fas fa-hospital').addTo(map),
                'opsdal': createClusterGroup('fas fa-bullseye fa-beat').addTo(map),
                'cctv': createClusterGroup('fas fa-video fa-beat').addTo(map),
                'petugas': createClusterGroup('fas fa-user-shield fa-bounce').addTo(map),
                'layanan_data': createClusterGroup('fas fa-users-viewfinder').addTo(map)
            };

            // Registry semua marker yang dapat di-drag (untuk edit koordinat)
            var draggableMarkers = [];
            var editModeActive = false;

            /** Warna 3D pin per kategori layer */
            var _pinColorMap = {
                'cctv':         '#ef4444',
                'con':          '#f97316',
                'ts':           '#f97316',
                'opsdal':       '#f97316',
                'gangguan':     '#f97316',
                'petugas':      '#22c55e',
                'posko':        '#3b82f6',
                'terminal':     '#8b5cf6',
                'stasiun':      '#8b5cf6',
                'bandara':      '#8b5cf6',
                'pelabuhan':    '#8b5cf6',
                'tj':           '#06b6d4',
                'aa':           '#f59e0b',
                'pa':           '#10b981',
                'wisata':       '#ec4899',
                'ibadah':       '#6366f1',
                'faskes':       '#14b8a6',
                'layanan_data': '#84cc16'
            };

            function addMarker(obj, iconClassOrFile, label, layerKey) {
                var lat = obj.coordinates[0], lng = obj.coordinates[1];
                if (!isValidCoordinate(lat, lng)) return null;
                var pinColor = _pinColorMap[layerKey] || '#EA4335';
                
                var isImageFile = iconClassOrFile.includes('.gif') || iconClassOrFile.includes('.png') || iconClassOrFile.includes('.svg');
                var innerElement = isImageFile 
                    ? '<img class="pin3d-icon-img" src="{{ asset("satupeta_assets/satupeta") }}/' + iconClassOrFile + '" style="width:20px;height:20px;object-fit:contain;">'
                    : '<i class="' + iconClassOrFile + ' pin3d-icon-font"></i>';

                var iconHtml =
                    '<div class="map-pin-root" style="width:' + PIN_WRAP_W + 'px">' +
                        '<div class="pin3d-box map-pin-circle" style="--pin-color:' + pinColor + '">' +
                            '<div class="pin3d-sphere" style="background: radial-gradient(circle at 35% 28%, rgba(255,255,255,0.38) 0%, transparent 55%), ' + pinColor + ';">' +
                                innerElement +
                            '</div>' +
                            '<div class="pin3d-ground-shadow"></div>' +
                        '</div>' +
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
                        iconAnchor: [PIN_ANCHOR_X, PIN_ANCHOR_Y],
                        popupAnchor: [-49, -49]
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
                        '<div class="popup-premium cctv-popup-container" data-id="' + idOnly + '" data-start-url="' + startUrl + '" data-stop-url="' + stopUrl + '" style="width:280px;">' +
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
                    tipHtml = '<div class="popup-premium" style="min-width:160px;">';

                    if (obj.foto_giat) {
                        var fg = obj.foto_giat.startsWith('/') || obj.foto_giat.startsWith('http') ? obj.foto_giat : '/storage/' + obj.foto_giat;
                        tipHtml += '<div style="margin-bottom:8px; text-align:center;"><img src="' + escHtml(fg) + '" style="max-width:100%; max-height:120px; border-radius:4px; object-fit:cover; border:1px solid #ccc; box-shadow:0 1px 3px rgba(0,0,0,0.2);"></div>';
                    }

                    var title = obj.name || '';
                    var subtitle = obj.lokasi || '';
                    
                    // Jika obj.name adalah alamat (seperti Jl. atau Jalan), tukar dengan obj.lokasi (nama tempat)
                    if (subtitle && (title.toLowerCase().startsWith('jl.') || title.toLowerCase().startsWith('jalan '))) {
                        var temp = title;
                        title = subtitle;
                        subtitle = temp;
                    }

                    tipHtml += '<strong style="font-size:14px; color:#fff; display:block; margin-bottom:2px;">' + escHtml(title) + '</strong>';

                    if (obj.jenis) tipHtml += '<span style="opacity:.9; font-size:11px; display:inline-block; margin-bottom:2px;">' + escHtml(obj.jenis) + '</span><br>';
                    if (subtitle) tipHtml += '<span style="font-size:11px; opacity:.8;">' + escHtml(subtitle) + '</span><br>';
                    if (obj.status) tipHtml += '<span style="font-size:11px;">Status: ' + escHtml(obj.status) + '</span><br>';

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
                    return { file: 'fas fa-train', short: 'Stasiun' };
                }
                if (j.indexOf('bandar') !== -1 || j.indexOf('udara') !== -1) {
                    return { file: 'fas fa-plane', short: 'Bandara' };
                }
                if (j.indexOf('pelabuhan') !== -1 || j.indexOf('asdp') !== -1) {
                    return { file: 'fas fa-ship', short: 'Pelabuhan' };
                }
                return { file: 'fas fa-bus-alt', short: 'Terminal' };
            }

            /** Black Spot / Trouble Spot / Traffic Stuck */
            function getTrafficStuckPinFile(obj) {
                return 'fas fa-car-burst fa-shake';
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
            var opsdalData = @json($opsdal ?? []);
            var cctvData = @json($cctv ?? []);
            var layananDataData = @json($layananData ?? []);
            var dacolObjekVitalData = @json($dacolObjekVital ?? []);
            var dacolBlackspotData = @json($dacolBlackspot ?? []);
            var dacolFasumData = @json($dacolFasum ?? []);

            var allBounds = [];
            // Inject dbTable & dbId ke setiap obj agar bisa di-update
            function tag(arr, tbl) { return arr.map(function(o) { var r=Object.assign({},o); r.dbTable=tbl; r.dbId=o.id?(o.id+'').replace(/^[^_]+_/,'')*1:null; return r; }); }
            poskoData       = tag(poskoData,       'pos_pelayanan');
            terminalsData   = tag(terminalsData,   'data_terminal');
            jalanGangguanData = tag(jalanGangguanData, 'data_gangguan');
            contigencyData  = tag(contigencyData,  'contigency');
            trafficstuckData = tag(trafficstuckData, 'trafficstuck');
            dacolBlackspotData = tag(dacolBlackspotData, 'sm_dacol_black_trouble_spot');
            trafficstuckData = trafficstuckData.concat(dacolBlackspotData);
            turjagwaliData  = tag(turjagwaliData,  'turjagwali');
            auditarusData   = tag(auditarusData,   'auditarus');
            pnpangkutanData = tag(pnpangkutanData, 'pnp_angkutan');
            objekwisataData = tag(objekwisataData, 'objek_wisata');
            dacolObjekVitalData = tag(dacolObjekVitalData, 'sm_dacol_objek_vital');
            objekwisataData = objekwisataData.concat(dacolObjekVitalData);
            tempatibadahData = tag(tempatibadahData, 'tempatibadah');
            dacolFasumData = tag(dacolFasumData, 'sm_dacol_fasilitas_umum');
            tempatibadahData = tempatibadahData.concat(dacolFasumData);
            datafaskesData  = tag(datafaskesData,  'data_faskes');
            opsdalData      = tag(opsdalData,      'tbl_cad_task');
            cctvData        = tag(cctvData,        'smpc_tbl_data_cctv');
            layananDataData = tag(layananDataData, 'layanan_data');

            poskoData.forEach(function(obj) {
                var c = addMarker(obj, 'fas fa-building-shield', obj.name || 'Posko', 'posko');
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
                var c = addMarker(obj, 'fas fa-triangle-exclamation fa-shake', obj.name || 'Jalan Gangguan', 'gangguan');
                if (c) allBounds.push(c);
            });
            objekwisataData.forEach(function(obj) {
                var c = addMarker(obj, 'fas fa-map-location-dot', obj.name || 'Objek Wisata', 'wisata');
                if (c) allBounds.push(c);
            });
            tempatibadahData.forEach(function(obj) {
                var c = addMarker(obj, 'fas fa-place-of-worship', obj.name || 'Tempat Ibadah', 'ibadah');
                if (c) allBounds.push(c);
            });
            datafaskesData.forEach(function(obj) {
                var c = addMarker(obj, 'fas fa-hospital', obj.name || 'Data Faskes', 'faskes');
                if (c) allBounds.push(c);
            });
            cctvData.forEach(function(obj) {
                var c = addMarker(obj, 'fas fa-video fa-beat', obj.name || 'CCTV Live', 'cctv');
                if (c) allBounds.push(c);
            });
            layananDataData.forEach(function(obj) {
                var c = addMarker(obj, 'fas fa-users-viewfinder', obj.name || 'Layanan Data', 'layanan_data');
                if (c) allBounds.push(c);
            });
            contigencyData.forEach(function(obj) {
                var jenis = (obj.name && obj.name.trim()) ? obj.name.trim() : 'Kejadian Darurat';
                var c = addMarker(obj, 'fas fa-truck-medical fa-fade', jenis, 'con');
                if (c) allBounds.push(c);
            });
            trafficstuckData.forEach(function(obj) {
                var faktor = (obj.name && obj.name.trim()) ? obj.name.trim() : 'Perlambatan';
                var c = addMarker(obj, getTrafficStuckPinFile(obj), faktor, 'ts');
                if (c) allBounds.push(c);
            });
            turjagwaliData.forEach(function(obj) {
                var c = addMarker(obj, 'fas fa-person-military-pointing', obj.name || 'Turjagwali', 'tj');
                if (c) allBounds.push(c);
            });
            auditarusData.forEach(function(obj) {
                var c = addMarker(obj, 'fas fa-chart-line', obj.name || 'Audit Arus', 'aa');
                if (c) allBounds.push(c);
            });
            pnpangkutanData.forEach(function(obj) {
                var c = addMarker(obj, 'fas fa-van-shuttle', obj.name || 'PNP Angkutan', 'pa');
                if (c) allBounds.push(c);
            });
            opsdalData.forEach(function(obj) {
                var nama = (obj.name && obj.name.trim()) ? obj.name.trim() : 'Tugas Opsdal';
                var c = addMarker(obj, 'fas fa-bullseye fa-beat', nama, 'opsdal');
                if (c) allBounds.push(c);
            });

            // ─── PETUGAS OPERATOR (realtime GPS dari /map/petugas) ───────────────
            var petugasRouteLines = L.layerGroup().addTo(map);

            function addPetugasMarker(obj) {
                var lat = obj.coordinates[0], lng = obj.coordinates[1];
                if (!isValidCoordinate(lat, lng)) return null;
                var isBertugas = String(obj.status || '').toUpperCase() === 'BERTUGAS';
                var statusColor = isBertugas ? '#f59e0b' : '#22c55e';
                var statusLabel = isBertugas ? 'BERTUGAS' : 'ONLINE';
                var photoUrl = obj.fotoprof || '';
                var pinInner = photoUrl
                    ? '<img src="' + escHtml(photoUrl) + '" style="width:36px;height:36px;border-radius:50%;object-fit:cover;border:2px solid ' + statusColor + ';box-shadow:0 2px 6px rgba(0,0,0,.45);">'
                    : '<i class="fas fa-user-shield fa-bounce" style="font-size:20px;color:' + statusColor + ';filter:drop-shadow(0 2px 4px rgba(0,0,0,.5));"></i>';

                var iconHtml =
                    '<div class="map-pin-root" style="width:' + PIN_WRAP_W + 'px">' +
                        '<div class="pin3d-box map-pin-circle" style="--pin-color:' + statusColor + '">' +
                            '<div style="position:relative;width:40px;height:40px;display:flex;justify-content:center;align-items:center;border-radius:50%;' +
                                'box-shadow:0 4px 14px rgba(0,0,0,0.5),0 0 0 3px ' + statusColor + '44,inset 0 2px 4px rgba(255,255,255,0.18);">' +
                               pinInner +
                               '<span style="position:absolute;bottom:0;right:0;width:11px;height:11px;border-radius:50%;' +
                                   'background:' + statusColor + ';border:2px solid #fff;box-shadow:0 1px 4px rgba(0,0,0,0.5);"></span>' +
                            '</div>' +
                        '</div>' +
                    '</div>';

                var marker = L.marker([lat, lng], {
                    icon: L.divIcon({
                        html: iconHtml,
                        className: 'map-pin-divicon',
                        iconSize: [PIN_WRAP_W, PIN_WRAP_H],
                        iconAnchor: [PIN_ANCHOR_X, PIN_ANCHOR_Y],
                        popupAnchor: [-49, -49]
                    })
                });

                var tipHtml = '<div class="popup-premium" style="min-width:180px;">';
                if (photoUrl) {
                    tipHtml += '<div style="text-align:center;margin-bottom:8px;"><img src="' + escHtml(photoUrl) + '" style="width:56px;height:56px;border-radius:50%;object-fit:cover;border:2px solid ' + statusColor + ';"></div>';
                }
                tipHtml += '<strong>' + escHtml(obj.name || '') + '</strong><br>';
                tipHtml += '<span style="display:inline-block;margin-top:4px;padding:2px 8px;border-radius:999px;background:' + statusColor + ';color:#fff;font-size:10px;font-weight:700;">' + statusLabel + '</span><br>';
                if (obj.daerah || obj.wilayah) {
                    tipHtml += '<span style="opacity:.85;font-size:11px;">' + escHtml([obj.wilayah, obj.daerah].filter(Boolean).join(' · ')) + '</span><br>';
                }
                if (obj.notelp) tipHtml += 'Telp: ' + escHtml(obj.notelp) + '<br>';
                if (obj.task_title) tipHtml += '<div style="margin-top:6px;padding-top:6px;border-top:1px solid #ccc;font-size:11px;">Tugas: <strong>' + escHtml(obj.task_title) + '</strong></div>';
                tipHtml += '<div style="margin-top:8px;"><a href="https://www.google.com/maps/dir/?api=1&destination=' + lat + ',' + lng + '" target="_blank" style="color:#2563eb;font-weight:600;text-decoration:none;font-size:11px;"><i class="fas fa-directions"></i> Rute</a></div>';
                tipHtml += '</div>';

                marker.bindPopup(tipHtml, { minWidth: 180 });
                layers['petugas'].addLayer(marker);

                // Garis menuju TKP jika sedang bertugas
                if (isBertugas && obj.task_lat && obj.task_lng) {
                    L.polyline([[lat, lng], [obj.task_lat, obj.task_lng]], {
                        color: '#f59e0b',
                        weight: 2,
                        dashArray: '6 6',
                        opacity: 0.75
                    }).addTo(petugasRouteLines);
                }

                return [lat, lng];
            }

            function refreshPetugasMarkers() {
                fetch(petugasUrl, { headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }, credentials: 'same-origin' })
                    .then(function(r) { return r.ok ? r.json() : []; })
                    .then(function(list) {
                        if (!Array.isArray(list)) list = [];
                        layers['petugas'].clearLayers();
                        petugasRouteLines.clearLayers();
                        list.forEach(function(obj) {
                            if (!obj.coordinates || obj.coordinates.length < 2) return;
                            addPetugasMarker(obj);
                        });
                    })
                    .catch(function() { /* diamkan: endpoint/tracking mungkin belum siap */ });
            }

            refreshPetugasMarkers();
            setInterval(refreshPetugasMarkers, 20000);

            // Debug Panel Functions
            @if(request()->query('debug') == '1')
            window.toggleDebugPanel = function() {
                var panel = document.getElementById('debugPanel');
                var icon = document.getElementById('debugToggleIcon').querySelector('i');
                if (panel.classList.contains('collapsed')) {
                    panel.classList.remove('collapsed');
                    icon.classList.remove('fa-chevron-left');
                    icon.classList.add('fa-chevron-right');
                } else {
                    panel.classList.add('collapsed');
                    icon.classList.remove('fa-chevron-right');
                    icon.classList.add('fa-chevron-left');
                }
            };

            window.fetchMapDiagnostics = function() {
                var listContainer = document.getElementById('dbgOperatorList');
                var conContainer = document.getElementById('dbgContingencyList');
                var tableStatus = document.getElementById('dbgTableStatus');
                var activeMarkers = document.getElementById('dbgActiveMarkers');
                var userInfo = document.getElementById('dbgUserInfo');

                listContainer.innerHTML = '<div style="text-align:center;padding:12px;opacity:0.7;"><i class="fas fa-spinner fa-spin"></i> Loading...</div>';
                conContainer.innerHTML = '<div style="text-align:center;padding:12px;opacity:0.7;"><i class="fas fa-spinner fa-spin"></i> Loading...</div>';

                fetch('{{ route('map.debug') }}', {
                    headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
                })
                .then(function(res) { return res.json(); })
                .then(function(data) {
                    if (!data.success) {
                        listContainer.innerHTML = '<div class="text-danger">Error: ' + escHtml(data.error || 'Unknown error') + '</div>';
                        conContainer.innerHTML = '<div class="text-danger">Error</div>';
                        return;
                    }

                    var diag = data.diagnostics;
                    
                    // Update summary
                    tableStatus.textContent = diag.petugas_tracking_table_exists ? 'YES' : 'NO';
                    tableStatus.className = 'debug-summary-value ' + (diag.petugas_tracking_table_exists ? 'success' : 'danger');

                    // User Info
                    var usr = diag.current_user;
                    if (usr.id) {
                        userInfo.innerHTML = 
                            '<strong>User:</strong> ' + escHtml(usr.username) + ' (ID: ' + escHtml(usr.id) + ')<br>' +
                            '<strong>Scope:</strong> ' + escHtml([usr.wilayah, usr.daerah].filter(Boolean).join(' · ') || 'None') + '<br>' +
                            '<strong>Bypass Scope:</strong> ' + (usr.bypasses_scope ? '<span class="text-success">YES</span>' : '<span class="text-warning">NO</span>');
                    } else {
                        userInfo.innerHTML = '<em>Not Logged In (Public)</em>';
                    }

                    // Operator List
                    var listHtml = '';
                    var visibleCount = 0;
                    if (Array.isArray(diag.operators_diagnostics) && diag.operators_diagnostics.length > 0) {
                        diag.operators_diagnostics.forEach(function(op) {
                            if (op.is_visible) visibleCount++;

                            var itemClass = op.is_visible ? 'visible' : 'hidden';
                            var statusClass = String(op.tracking_status || '').toLowerCase() || 'offline';
                            var statusLabel = String(op.tracking_status || 'OFFLINE').toUpperCase();
                            var details = escHtml([op.wilayah, op.daerah].filter(Boolean).join(' · '));
                            
                            var coordStr = op.lat && op.lng ? op.lat.toFixed(5) + ', ' + op.lng.toFixed(5) : 'No Coordinates';
                            
                            listHtml += 
                                '<div class="debug-operator-item ' + itemClass + '">' +
                                    '<div class="debug-operator-header">' +
                                        '<span class="debug-operator-name">' + escHtml(op.nama) + ' (@' + escHtml(op.username) + ')</span>' +
                                        '<span class="debug-operator-status ' + statusClass + '">' + statusLabel + '</span>' +
                                    '</div>' +
                                    '<div class="debug-operator-details">' +
                                        '<strong>Role ID:</strong> ' + op.user_role_id + ' | <strong>Coords:</strong> ' + coordStr + '<br>' +
                                        '<strong>Region:</strong> ' + (details || '<em>Empty</em>') +
                                    '</div>';

                            if (op.reasons && op.reasons.length > 0) {
                                listHtml += '<div class="debug-operator-reasons">⚠️ ' + op.reasons.join('<br>⚠️ ') + '</div>';
                            }

                            listHtml += '</div>';
                        });
                    } else {
                        listHtml = '<div style="text-align:center;padding:12px;opacity:0.7;">No operators found for Role IDs: ' + diag.operator_role_ids.join(', ') + '</div>';
                    }

                    listContainer.innerHTML = listHtml;
                    activeMarkers.textContent = visibleCount + ' / ' + (diag.operators_diagnostics ? diag.operators_diagnostics.length : 0);

                    // Contingency List
                    var conHtml = '';
                    var conDiag = diag.contingency_diagnostics;
                    if (conDiag && Array.isArray(conDiag.records)) {
                        if (conDiag.records.length > 0) {
                            conDiag.records.forEach(function(con) {
                                var itemClass = con.is_visible ? 'visible' : 'hidden';
                                var statusClass = con.status_konfirmasi === 'menunggu' ? 'bertugas' : 'online';
                                var statusLabel = String(con.status_konfirmasi || 'CONFIRMED').toUpperCase();
                                
                                conHtml += 
                                    '<div class="debug-operator-item ' + itemClass + '">' +
                                        '<div class="debug-operator-header">' +
                                            '<span class="debug-operator-name">[' + con.id + '] ' + escHtml(con.jenis) + '</span>' +
                                            '<span class="debug-operator-status ' + statusClass + '">' + statusLabel + '</span>' +
                                        '</div>' +
                                        '<div class="debug-operator-details">' +
                                            '<strong>Location:</strong> ' + escHtml(con.lokasi || 'Empty') + '<br>' +
                                            '<strong>Coords:</strong> ' + escHtml(con.koordinat || 'No Coordinates') +
                                        '</div>';

                                if (con.reasons && con.reasons.length > 0) {
                                    conHtml += '<div class="debug-operator-reasons">⚠️ ' + con.reasons.join('<br>⚠️ ') + '</div>';
                                }

                                conHtml += '</div>';
                            });
                        } else {
                            conHtml = '<div style="text-align:center;padding:12px;opacity:0.7;">No contingency records synced from Layanan Masyarakat.</div>';
                        }
                    } else {
                        conHtml = '<div style="text-align:center;padding:12px;opacity:0.7;">Contingency diagnostics not available.</div>';
                    }
                    conContainer.innerHTML = conHtml;

                })
                .catch(function(err) {
                    listContainer.innerHTML = '<div style="color:#ef4444;">Failed to fetch diagnostics: ' + escHtml(err.message || err) + '</div>';
                    conContainer.innerHTML = '<div style="color:#ef4444;">Failed to load.</div>';
                });
            };

            // Run fetch on load
            fetchMapDiagnostics();
            @endif

            // ─── LEGEND DINAMIS DIHAPUS (DIGANTI LEGEND STATIS BERDASARKAN MODUL) ───


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
                        if (layerKey === 'petugas') map.addLayer(petugasRouteLines);
                    } else {
                        row.classList.add('inactive');
                        map.removeLayer(layer);
                        if (layerKey === 'petugas') map.removeLayer(petugasRouteLines);
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
