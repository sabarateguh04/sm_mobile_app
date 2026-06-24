@extends('layouts.app')
@section('title', 'Live CCTV — ' . ($camera->nama_cctv ?? 'Kamera'))
@section('page-title', 'Live CCTV')

@section('content')
<div class="max-w-[1100px] mx-auto w-full space-y-4">

<div class="flex items-center gap-3">
    <a href="{{ route('cctv-live.index') }}" class="flex items-center gap-1.5 text-slate-500 hover:text-primary text-decoration-none text-label-md font-semibold transition-colors">
        <span class="material-symbols-outlined text-[18px]">arrow_back</span><span>Daftar Kamera</span>
    </a>
    <span class="text-slate-300">/</span>
    <span class="text-[13px] font-bold text-on-surface">{{ $camera->nama_cctv ?: 'Kamera' }}</span>
</div>

<div class="bg-white rounded-2xl border border-slate-200/60 shadow-sm overflow-hidden">
    <div class="px-5 py-3.5 border-b border-slate-100 bg-slate-50/30 flex items-center justify-between gap-2 flex-wrap">
        <div class="flex items-center gap-3">
            <span class="material-symbols-outlined text-primary text-[18px]" style="font-variation-settings:'FILL' 1;">videocam</span>
            <h3 class="text-[13px] font-bold text-slate-800 m-0">{{ $camera->nama_cctv ?: 'Kamera' }}</h3>
            @php
                $statusLower = strtolower(trim((string)($camera->status ?? '')));
                $isOnline = in_array($statusLower, ['aktif', 'online', 'hidup', 'normal', '1']) || empty($statusLower);
            @endphp
            @if($isOnline)
                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full bg-emerald-50 text-emerald-700 border border-emerald-200/80 text-[10px] font-bold">
                    <span class="w-1 h-1 rounded-full bg-emerald-500 animate-pulse"></span>
                    <span>Aktif</span>
                </span>
            @else
                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full bg-rose-50 text-rose-700 border border-rose-200/80 text-[10px] font-bold">
                    <span class="w-1 h-1 rounded-full bg-rose-500"></span>
                    <span>Mati</span>
                </span>
            @endif
        </div>
        @if(!empty($camera->channel_name))
            <span class="inline-flex items-center px-2.5 py-1 rounded-lg bg-slate-100 text-slate-600 text-[11px] font-bold">{{ $camera->channel_name }}</span>
        @endif
    </div>

    <div class="p-4">
        @if(!$streamConfigured)
            <div class="rounded-xl bg-amber-50 border border-amber-200 px-4 py-3 text-amber-900 text-[12px] mb-4">
                Gateway stream belum dikonfigurasi (<code>URL_STREAM</code>).
            </div>
        @endif

        @if(isset($isLocalIp) && $isLocalIp)
            <div class="rounded-xl bg-rose-50 border border-rose-200 px-4 py-3 text-rose-900 text-[12px] mb-4 flex items-start gap-2">
                <span class="material-symbols-outlined text-rose-600 text-[18px]">warning</span>
                <div>
                    <strong>Peringatan: Kamera Tidak Dapat Diakses dari Internet</strong>
                    <p class="m-0 mt-1">Kamera ini menggunakan IP Lokal (Private IP). Server viewer publik tidak dapat menjangkau kamera ini. Pastikan Anda berada dalam jaringan yang sama atau gunakan VPN untuk mengaksesnya.</p>
                </div>
            </div>
        @endif

        <div class="relative bg-slate-900 rounded-xl overflow-hidden aspect-video flex items-center justify-center">
            <iframe id="cctvLiveFrame"
                    class="w-full h-full border-0 hidden"
                    allow="autoplay; fullscreen"
                    title="Live CCTV"></iframe>
            <div id="cctvLivePlaceholder" class="text-center px-4">
                <span class="material-symbols-outlined text-slate-500 text-[48px] animate-pulse">hourglass_top</span>
                <p id="cctvLiveStatus" class="text-slate-400 text-[12px] mt-2 m-0">Menyiapkan stream…</p>
            </div>
        </div>

        <dl class="grid grid-cols-1 sm:grid-cols-2 gap-3 mt-4 mb-0">
            <div>
                <dt class="text-[10px] font-bold text-slate-400 uppercase tracking-wide mb-0.5">Status Kamera</dt>
                <dd class="text-[12px] text-slate-700 m-0 font-semibold flex items-center gap-1.5">
                    @if($isOnline)
                        <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                        <span class="text-emerald-700">Aktif (Online)</span>
                    @else
                        <span class="w-2 h-2 rounded-full bg-rose-500"></span>
                        <span class="text-rose-600">Mati (Offline)</span>
                    @endif
                </dd>
            </div>
            @if(!empty($camera->channel_id))
                <div>
                    <dt class="text-[10px] font-bold text-slate-400 uppercase tracking-wide mb-0.5">Channel ID</dt>
                    <dd class="text-[12px] text-slate-700 m-0 font-mono">{{ $camera->channel_id }}</dd>
                </div>
            @endif
            @if(!empty($camera->kordinat))
                @php
                    $coords = explode(',', $camera->kordinat);
                    $lat = isset($coords[0]) ? trim($coords[0]) : '';
                    $lng = isset($coords[1]) ? trim($coords[1]) : '';
                @endphp
                <div>
                    <dt class="text-[10px] font-bold text-slate-400 uppercase tracking-wide mb-0.5">Koordinat</dt>
                    <dd class="text-[12px] text-slate-700 m-0">
                        @if($lat !== '' && $lng !== '')
                            <div class="flex items-center gap-2 text-[11px] mt-0.5">
                                <a href="{{ route('map.index', ['lat' => $lat, 'lng' => $lng, 'name' => $camera->nama_cctv]) }}" target="_blank" class="inline-flex items-center gap-0.5 text-primary hover:text-primary-dark font-semibold text-decoration-none">
                                    <span class="material-symbols-outlined text-[14px]">map</span>
                                    <span>Peta</span>
                                </a>
                                <span class="text-slate-300">|</span>
                                <a href="https://www.google.com/maps/search/?api=1&query={{ urlencode($camera->kordinat) }}" target="_blank" class="inline-flex items-center gap-0.5 text-emerald-600 hover:text-emerald-700 font-semibold text-decoration-none">
                                    <span class="material-symbols-outlined text-[14px]">explore</span>
                                    <span>Google Maps</span>
                                </a>
                                <span class="text-slate-300">|</span>
                                <span class="text-slate-500 font-mono text-[10px]">{{ $camera->kordinat }}</span>
                            </div>
                        @else
                            {{ $camera->kordinat }}
                        @endif
                    </dd>
                </div>
            @endif
        </dl>

        <!-- Debugging Panel -->
        @php
            $rtspUrl = $camera->rtsp_cctv ?? '';
            $isPrivateIp = false;
            $ipMatch = [];
            if (preg_match('/(?:[0-9]{1,3}\.){3}[0-9]{1,3}/', $rtspUrl, $ipMatch)) {
                $ip = $ipMatch[0];
                $isPrivateIp = !filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE);
            }
            $viewerHost = config('satupeta.stream_viewer_host', 'sp.kodal.id');
        @endphp

        <div class="mt-6 border border-slate-200 rounded-xl overflow-hidden bg-slate-50">
            <div class="px-4 py-2.5 bg-slate-200/50 border-b border-slate-200 flex items-center justify-between cursor-pointer" onclick="document.getElementById('debugPanel').classList.toggle('hidden')">
                <div class="flex items-center gap-2">
                    <span class="material-symbols-outlined text-slate-500 text-[18px]">bug_report</span>
                    <h4 class="text-[12px] font-bold text-slate-700 m-0">Diagnostic & Debugging Info</h4>
                </div>
                <span class="material-symbols-outlined text-slate-400 text-[16px]">expand_more</span>
            </div>
            <div id="debugPanel" class="p-4 text-[12px] space-y-3">
                <div>
                    <strong class="block text-slate-700 mb-1">1. Original RTSP URL (Dari Database):</strong>
                    <code class="block bg-slate-800 text-green-400 p-2 rounded-lg break-all">{{ $rtspUrl ?: 'Tidak ada URL RTSP' }}</code>
                </div>
                
                @if($isPrivateIp)
                <div class="bg-amber-100 border border-amber-200 text-amber-900 px-3 py-2 rounded-lg flex gap-2 shadow-sm">
                    <span class="material-symbols-outlined text-amber-600 text-[18px] mt-0.5">warning</span>
                    <div>
                        <strong class="text-[13px] block mb-0.5">Private / Local IP Terdeteksi!</strong>
                        CCTV ini menggunakan IP Lokal (<code>{{ $ip }}</code>). <br>
                        Server viewer (<code>{{ $viewerHost }}</code>) yang berada di internet publik <b>TIDAK BISA</b> menjangkau IP lokal ini, kecuali server viewer tersebut di-host di dalam satu jaringan VPN/LAN yang sama dengan CCTV. Inilah penyebab stream gagal dimuat!
                    </div>
                </div>
                @endif
                
                <div>
                    <strong class="block text-slate-700 mb-1">2. Viewer Player URL (Iframe src):</strong>
                    <code class="block bg-slate-100 text-slate-800 border border-slate-200 p-2 rounded-lg break-all" id="debugViewerUrl">Menunggu URL dari API...</code>
                </div>

                <div>
                    <strong class="block text-slate-700 mb-1">3. Status Koneksi Iframe:</strong>
                    <div id="debugIframeStatus" class="text-slate-600 italic">Memeriksa iframe...</div>
                </div>
                
                <div class="pt-2">
                    <button type="button" onclick="window.open(document.getElementById('cctvLiveFrame').src, '_blank')" class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-slate-200 hover:bg-slate-300 text-slate-700 rounded-lg font-bold transition-colors">
                        <span class="material-symbols-outlined text-[14px]">open_in_new</span> Coba Buka Stream di Tab Baru
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

</div>
@endsection

@push('scripts')
<script src="{{ asset('js/cctv-live-stream.js') }}"></script>
<script>
(function () {
    @if($streamConfigured)
    var player = null;
    CctvLiveStream.startGatewayStream(
        @json(route('cctv-live.start', $camera->id)),
        @json(route('cctv-live.stop', $camera->id))
    ).then(function (handle) {
        player = handle;
        var ph = document.getElementById('cctvLivePlaceholder');
        if (ph) ph.style.display = 'none';

        // Debug Panel Updates
        var debugUrl = document.getElementById('debugViewerUrl');
        var frame = document.getElementById('cctvLiveFrame');
        if (debugUrl && frame && frame.src) {
            debugUrl.textContent = frame.src;
        }

        var debugStatus = document.getElementById('debugIframeStatus');
        if (debugStatus && frame) {
            debugStatus.innerHTML = '<span class="text-emerald-600 font-bold">Iframe Src berhasil diset!</span> (Mencoba memuat player...)';
            frame.onload = function() {
                debugStatus.innerHTML = '<span class="text-emerald-600 font-bold">Iframe selesai dimuat.</span> (Menunggu video dimainkan...)';
            };
        }

    }).catch(function (err) {
        var debugStatus = document.getElementById('debugIframeStatus');
        if (debugStatus) {
            debugStatus.innerHTML = '<span class="text-rose-600 font-bold">Gagal mendapatkan viewer URL dari API:</span> ' + err;
        }
    });
    @else
    var status = document.getElementById('cctvLiveStatus');
    if (status) {
        status.textContent = 'Stream tidak tersedia — konfigurasi server diperlukan.';
        status.classList.add('text-red-600');
    }
    @endif
})();
</script>
@endpush
