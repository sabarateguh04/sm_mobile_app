@extends('layouts.app')
@section('title', $pageTitle)
@section('page-title', $pageTitle)

@section('content')
<div class="max-w-[1400px] mx-auto w-full space-y-4">

    <!-- Header Navigation -->
    <section class="flex items-center gap-2">
        <a href="{{ route('opsdal.cad.index') }}" class="flex items-center justify-center w-9 h-9 rounded-xl bg-white border border-slate-200 text-slate-500 hover:text-slate-800 transition-colors text-decoration-none shadow-sm">
            <span class="material-symbols-outlined text-[20px]">arrow_back</span>
        </a>
        <div>
            <h2 class="text-[18px] font-extrabold text-slate-800 m-0">Detail Penugasan CAD #{{ $task->id }}</h2>
            <p class="text-[10px] text-slate-400 mt-0.5 m-0">Status Penanganan Laporan Tugas secara Real-time</p>
        </div>
    </section>

    <!-- Alert Messages -->
    @if(session('success'))
        <div class="bg-emerald-50 text-emerald-800 border border-emerald-200 rounded-xl p-4 flex justify-between items-center shadow-sm">
            <div class="flex items-center gap-3">
                <span class="material-symbols-outlined text-emerald-500" style="font-variation-settings: 'FILL' 1;">check_circle</span>
                <span class="font-medium text-sm">{{ session('success') }}</span>
            </div>
            <button type="button" class="text-emerald-800/50 hover:text-emerald-800 bg-transparent border-0 cursor-pointer" onclick="this.parentElement.remove()">
                <span class="material-symbols-outlined">close</span>
            </button>
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">

        <!-- Left Column: Details, Map, and Photos -->
        <div class="lg:col-span-2 space-y-4">

            <!-- Detail Laporan -->
            <div class="bg-white rounded-xl border border-slate-200/60 shadow-sm p-4 space-y-3">
                <div class="flex items-start justify-between gap-2 border-b border-slate-100 pb-3">
                    <div>
                        <span class="text-[9px] font-bold text-primary uppercase tracking-widest">Detail Kejadian</span>
                        <h3 class="text-[16px] font-extrabold text-slate-800 m-0 mt-0.5">{{ $task->title }}</h3>
                        <p class="text-[10px] text-slate-400 m-0 mt-1">Dilaporkan oleh: <span class="font-bold text-slate-700">{{ $task->pelapor }}</span> | Waktu: <span class="font-medium text-slate-600 local-time" data-utc="{{ \Carbon\Carbon::parse($task->created_at)->timezone('UTC')->toIso8601String() }}">{{ \Carbon\Carbon::parse($task->created_at)->timezone('Asia/Jakarta')->format('d F Y H:i') }} WIB</span></p>
                    </div>
                    <div class="flex flex-col items-end gap-1.5">
                        @if($task->status === 'NEW')
                            <span class="inline-flex items-center gap-1 px-3 py-1 rounded-full text-[10px] font-bold bg-blue-50 text-blue-700 border border-blue-200/60">STATUS: BARU</span>
                        @elseif(in_array($task->status, ['DITERIMA', 'MENUJU', 'TIBA']))
                            <span class="inline-flex items-center gap-1 px-3 py-1 rounded-full text-[10px] font-bold bg-amber-50 text-amber-700 border border-amber-200/60 shadow-sm animate-pulse">
                                <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span>
                                STATUS: {{ $task->status }}
                            </span>
                        @elseif($task->status === 'SELESAI')
                            <span class="inline-flex items-center gap-1 px-3 py-1 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200/60">STATUS: SELESAI</span>
                        @else
                            <span class="inline-flex items-center gap-1 px-3 py-1 rounded-full text-[10px] font-bold bg-slate-100 text-slate-500 border border-slate-200/60">STATUS: CLOSED</span>
                        @endif

                        @if($task->priority === 'HIGH')
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[9px] font-bold bg-rose-50 text-rose-700 border border-rose-200/60">PRIORITAS TINGGI</span>
                        @elseif($task->priority === 'MEDIUM')
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[9px] font-bold bg-amber-50 text-amber-700 border border-amber-200/60">PRIORITAS SEDANG</span>
                        @else
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[9px] font-bold bg-sky-50 text-sky-700 border border-sky-200/60">PRIORITAS RENDAH</span>
                        @endif
                    </div>
                </div>

                <div class="space-y-2">
                    <div>
                        <h5 class="text-[10px] font-bold text-slate-400 uppercase m-0">Alamat Kejadian</h5>
                        <p class="text-[12px] text-slate-700 font-medium m-0 mt-0.5">{{ $task->address }}</p>
                    </div>
                    @if(!empty($task->keterangan))
                        <div>
                            <h5 class="text-[10px] font-bold text-slate-400 uppercase m-0">Keterangan / Laporan Awal</h5>
                            <p class="text-[12px] text-slate-700 m-0 mt-0.5 bg-slate-50 border border-slate-100 p-2.5 rounded-xl leading-relaxed">{{ $task->keterangan }}</p>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Interactive Map -->
            <div class="bg-white rounded-xl border border-slate-200/60 shadow-sm overflow-hidden">
                <div class="px-4 py-3 border-b border-slate-100 flex items-center justify-between bg-slate-50/50">
                    <h4 class="text-[11px] font-bold uppercase tracking-wider text-slate-700 m-0 flex items-center gap-1">
                        <span class="material-symbols-outlined text-[16px] text-primary">map</span>
                        Peta Lokasi &amp; Posisi Petugas
                    </h4>
                </div>
                <div id="taskMap" class="w-full" style="height: 350px;"></div>
            </div>

            <!-- Bukti Foto Laporan Lapangan -->
            <div class="bg-white rounded-xl border border-slate-200/60 shadow-sm p-4">
                <h4 class="text-[11px] font-bold uppercase tracking-wider text-slate-700 m-0 mb-3 flex items-center gap-1">
                    <span class="material-symbols-outlined text-[16px] text-primary">photo_library</span>
                    Dokumentasi Bukti Lapangan ({{ $photos->count() }})
                </h4>
                @if($photos->count())
                    <div class="grid grid-cols-2 sm:grid-cols-3 gap-3">
                        @foreach($photos as $photo)
                            @php
                                $photoUrl = $photo->url;
                                if (!str_starts_with($photoUrl, 'http')) {
                                    if (!str_contains($photoUrl, '/')) {
                                        $photoUrl = asset('storage/cad_photos/' . $photoUrl);
                                    } else {
                                        $photoUrl = asset('storage/' . $photoUrl);
                                    }
                                }
                            @endphp
                            <a href="{{ $photoUrl }}" target="_blank" class="block rounded-xl overflow-hidden border border-slate-200 bg-slate-50 hover:opacity-90 transition-opacity" style="height: 140px;">
                                <img src="{{ $photoUrl }}" alt="Bukti CAD" class="w-full h-full object-cover"/>
                            </a>
                        @endforeach
                    </div>
                @else
                    <div class="text-center py-8 text-slate-400 border border-dashed border-slate-200 rounded-xl">
                        <span class="material-symbols-outlined text-[32px] mb-1.5 block">add_a_photo</span>
                        <span class="text-xs font-semibold">Belum ada dokumentasi foto yang diunggah oleh petugas.</span>
                    </div>
                @endif
            </div>

        </div>

        <!-- Right Column: Timeline & Dispatch Actions -->
        <div class="space-y-4">

            <!-- Progress Timeline -->
            <div class="bg-white rounded-xl border border-slate-200/60 shadow-sm p-4">
                <h4 class="text-[11px] font-bold uppercase tracking-wider text-slate-700 m-0 mb-4 flex items-center gap-1">
                    <span class="material-symbols-outlined text-[16px] text-primary">hourglass_empty</span>
                    Timeline Progress Penanganan
                </h4>
                <div class="relative pl-6 border-l-2 border-slate-100 space-y-5">
                    
                    <!-- Laporan Dibuat -->
                    <div class="relative">
                        <div class="absolute -left-[31px] top-0.5 w-4 h-4 rounded-full bg-blue-500 border-4 border-white shadow-sm flex items-center justify-center"></div>
                        <div>
                            <h5 class="text-[11px] font-bold text-slate-800 m-0">Tugas Baru Dibuat</h5>
                            <p class="text-[9px] text-slate-400 m-0 mt-0.5"><span class="local-time" data-utc="{{ \Carbon\Carbon::parse($task->created_at)->timezone('UTC')->toIso8601String() }}">{{ \Carbon\Carbon::parse($task->created_at)->timezone('Asia/Jakarta')->format('d/m/Y H:i') }} WIB</span> | oleh {{ $task->creator_name ?? 'Sistem' }}</p>
                        </div>
                    </div>

                    <!-- Log Aktivitas Petugas -->
                    @forelse($logs as $log)
                        @php
                            $color = 'bg-slate-300';
                            if ($log->status === 'DITERIMA') $color = 'bg-indigo-500';
                            elseif ($log->status === 'MENUJU') $color = 'bg-amber-500';
                            elseif ($log->status === 'TIBA') $color = 'bg-orange-500';
                            elseif ($log->status === 'SELESAI') $color = 'bg-emerald-500';
                        @endphp
                        <div class="relative">
                            <div class="absolute -left-[31px] top-0.5 w-4 h-4 rounded-full {{ $color }} border-4 border-white shadow-sm flex items-center justify-center"></div>
                            <div>
                                <h5 class="text-[11px] font-bold text-slate-800 m-0">
                                    Status: <span class="text-primary">{{ $log->status }}</span>
                                </h5>
                                <p class="text-[10px] text-slate-600 m-0 mt-0.5 font-medium">Petugas: {{ $log->user_name }}</p>
                                <p class="text-[9px] text-slate-400 m-0 mt-0.5 local-time" data-utc="{{ \Carbon\Carbon::parse($log->created_at)->timezone('UTC')->toIso8601String() }}">{{ \Carbon\Carbon::parse($log->created_at)->timezone('Asia/Jakarta')->format('d/m/Y H:i') }} WIB</p>
                            </div>
                        </div>
                    @empty
                        <div class="text-xs text-slate-400 font-medium py-1">Menunggu respon pertama dari petugas...</div>
                    @endforelse

                    <!-- Laporan Ditutup -->
                    @if($task->status === 'CLOSED' && $closeLog)
                        <div class="relative">
                            <div class="absolute -left-[31px] top-0.5 w-4 h-4 rounded-full bg-slate-800 border-4 border-white shadow-sm flex items-center justify-center"></div>
                            <div>
                                <h5 class="text-[11px] font-bold text-slate-800 m-0">Tugas Ditutup (CLOSED)</h5>
                                <p class="text-[9px] text-slate-400 m-0 mt-0.5"><span class="local-time" data-utc="{{ \Carbon\Carbon::parse($closeLog->created_at)->timezone('UTC')->toIso8601String() }}">{{ \Carbon\Carbon::parse($closeLog->created_at)->timezone('Asia/Jakarta')->format('d/m/Y H:i') }} WIB</span> | oleh {{ $closeLog->closed_by_name }}</p>
                                <p class="text-[10px] text-slate-600 mt-1.5 p-2 bg-slate-50 rounded-lg border border-slate-100 italic">"{{ $closeLog->catatan }}"</p>
                            </div>
                        </div>
                    @endif

                </div>
            </div>

            <!-- Petugas Ditugaskan -->
            <div class="bg-white rounded-xl border border-slate-200/60 shadow-sm p-4">
                <h4 class="text-[11px] font-bold uppercase tracking-wider text-slate-700 m-0 mb-3 flex items-center gap-1">
                    <span class="material-symbols-outlined text-[16px] text-primary">groups</span>
                    Tim Petugas Lapangan
                </h4>
                <div class="space-y-2.5">
                    @forelse($assignments as $assign)
                        @php
                            $avatar = !empty($assign->fotoprof)
                                ? (str_starts_with($assign->fotoprof, 'http') ? $assign->fotoprof : asset('storage/'.$assign->fotoprof))
                                : 'https://ui-avatars.com/api/?name='.urlencode($assign->nama).'&background=0041c8&color=fff&size=40';
                        @endphp
                        <div class="flex items-center justify-between p-2 rounded-xl border border-slate-100">
                            <div class="flex items-center gap-2">
                                <img src="{{ $avatar }}" alt="" class="w-8 h-8 rounded-lg object-cover border border-slate-200"/>
                                <div>
                                    <h5 class="text-xs font-bold text-slate-800 m-0">{{ $assign->nama }}</h5>
                                    <p class="text-[9px] text-slate-400 m-0">{{ $assign->notelp ?: '-' }}</p>
                                </div>
                            </div>
                            <span class="text-[9px] px-2 py-0.5 rounded-full font-bold {{ $assign->assignment_status === 'SELESAI' ? 'bg-emerald-50 text-emerald-700' : 'bg-amber-50 text-amber-700' }}">{{ $assign->assignment_status }}</span>
                        </div>
                    @empty
                        <div class="text-center py-4 text-xs text-rose-500 font-bold">Belum ada petugas yang ditugaskan.</div>
                    @endforelse
                </div>

                <!-- Assign More Officers Form -->
                @if($task->status !== 'CLOSED' && $task->status !== 'SELESAI')
                    <form action="{{ route('opsdal.cad.assign', $task->id) }}" method="POST" class="mt-4 pt-4 border-t border-slate-100 m-0 space-y-2">
                        @csrf
                        <label class="block text-[10px] font-bold text-slate-400 uppercase">Tambah Petugas Cadangan</label>
                        <select name="assign_to[]" multiple required class="w-full bg-slate-50 border border-slate-200 rounded-xl px-2 py-2 text-xs outline-none focus:bg-white focus:border-primary transition-all">
                            @foreach($onlineOfficers as $officer)
                                <option value="{{ $officer->id_usm }}">{{ $officer->nama }} ({{ $officer->tracking_status }})</option>
                            @endforeach
                        </select>
                        <button type="submit" class="w-full bg-primary text-on-primary font-bold text-xs py-2 rounded-xl hover:bg-primary-dark transition-colors border-0 cursor-pointer">Kirim Petugas Tambahan</button>
                    </form>
                @endif
            </div>

            <!-- Action Panel: Close Task -->
            @if($task->status !== 'CLOSED')
                <div class="bg-white rounded-xl border border-slate-200/60 shadow-sm p-4">
                    <h4 class="text-[11px] font-bold uppercase tracking-wider text-slate-700 m-0 mb-3 flex items-center gap-1">
                        <span class="material-symbols-outlined text-[16px] text-rose-600">close</span>
                        Tutup Tugas Dispatch
                    </h4>
                    <form action="{{ route('opsdal.cad.close', $task->id) }}" method="POST" class="m-0 space-y-3">
                        @csrf
                        <div>
                            <label class="block text-[10px] font-bold text-slate-400 uppercase mb-1">Catatan Dispatcher / Penutupan</label>
                            <textarea name="catatan" required rows="3" placeholder="Masukkan laporan akhir penutupan kasus..." class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 text-xs focus:bg-white focus:border-primary outline-none transition-all resize-none"></textarea>
                        </div>
                        <button type="submit" onclick="return confirm('Yakin ingin menutup tugas CAD ini? Semua petugas akan dikembalikan ke status ONLINE.')" class="w-full bg-rose-600 text-white font-bold text-xs py-2.5 rounded-xl hover:bg-rose-700 transition-colors border-0 cursor-pointer">Tutup &amp; Selesaikan Tugas</button>
                    </form>
                </div>
            @endif

        </div>

    </div>

</div>

<!-- Leaflet CSS & JS -->
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin=""/>
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>

<script>
document.addEventListener("DOMContentLoaded", function () {
    var incidentLat = {{ $task->latitude ?? 0 }};
    var incidentLng = {{ $task->longitude ?? 0 }};
    
    // Fallback if no coordinates
    if (incidentLat === 0 && incidentLng === 0) {
        incidentLat = -6.200000;
        incidentLng = 106.800000;
    }

    var map = L.map('taskMap').setView([incidentLat, incidentLng], 14);

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; OpenStreetMap contributors'
    }).addTo(map);

    // Custom Icon for Incident (Red alert pin)
    var alertIcon = L.icon({
        iconUrl: 'https://cdn-icons-png.flaticon.com/512/564/564619.png', // Fire / incident icon
        iconSize: [32, 32],
        iconAnchor: [16, 32],
        popupAnchor: [0, -32]
    });

    var incidentMarker = L.marker([incidentLat, incidentLng], { icon: alertIcon }).addTo(map)
        .bindPopup("<b>TKP: {{ $task->title }}</b><br/>{{ $task->address }}")
        .openPopup();

    var bounds = [ [incidentLat, incidentLng] ];

    // Draw Officer Locations
    @foreach($assignments as $assign)
        @if($assign->tracking_lat && $assign->tracking_lng)
            var officerLat = {{ $assign->tracking_lat }};
            var officerLng = {{ $assign->tracking_lng }};
            
            var officerIcon = L.divIcon({
                className: 'custom-officer-icon-container',
                html: '<div style="position:relative; width:36px; height:36px; border-radius:50%; border:3px solid {{ $assign->tracking_status === 'BERTUGAS' ? '#ef4444' : '#10b981' }}; background:white; overflow:hidden; box-shadow:0 2px 5px rgba(0,0,0,0.3);">' +
                      '<img src="{{ !empty($assign->fotoprof) ? (str_starts_with($assign->fotoprof, 'http') ? $assign->fotoprof : asset('storage/'.$assign->fotoprof)) : 'https://ui-avatars.com/api/?name='.urlencode($assign->nama).'&background=0041c8&color=fff&size=40' }}" style="width:100%; height:100%; object-cover:cover;" />' +
                      '</div>',
                iconSize: [36, 36],
                iconAnchor: [18, 18],
                popupAnchor: [0, -18]
            });

            var officerMarker = L.marker([officerLat, officerLng], { icon: officerIcon }).addTo(map)
                .bindPopup("<b>Petugas: {{ $assign->nama }}</b><br/>Status: {{ $assign->tracking_status }}<br/><a href='https://www.google.com/maps/dir/?api=1&origin=" + officerLat + "," + officerLng + "&destination=" + incidentLat + "," + incidentLng + "' target='_blank' style='color:#0041c8;font-weight:bold;text-decoration:none;'>Rute ke TKP</a>");

            bounds.push([officerLat, officerLng]);
        @endif
    @endforeach

    // Fit map to show both TKP and officers if officers have GPS
    if (bounds.length > 1) {
        map.fitBounds(bounds, { padding: [50, 50] });
    }

    // Dynamic Timezone Conversion
    document.querySelectorAll('.local-time').forEach(function(el) {
        var utcDate = new Date(el.getAttribute('data-utc'));
        if (!isNaN(utcDate.getTime())) {
            var formatted = utcDate.toLocaleString('id-ID', {
                day: '2-digit', month: '2-digit', year: 'numeric',
                hour: '2-digit', minute: '2-digit'
            }).replace(/\./g, ':');
            
            var tzFormatter = new Intl.DateTimeFormat('id-ID', { timeZoneName: 'short' });
            var tzParts = tzFormatter.formatToParts(utcDate);
            var tzName = tzParts.find(p => p.type === 'timeZoneName')?.value || 'WIB';
            
            el.textContent = formatted + ' ' + tzName;
        }
    });
});
</script>
@endsection
