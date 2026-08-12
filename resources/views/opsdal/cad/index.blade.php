@extends('layouts.app')
@section('title', $pageTitle)
@section('page-title', $pageTitle)

@section('content')
<div class="max-w-[1400px] mx-auto w-full space-y-4">

    @if(!empty($schemaWarning))
        <div class="alert alert-warning border-0 rounded-xl shadow-sm mb-0">
            <i class="fa fa-triangle-exclamation me-2"></i>{{ $schemaWarning }}
        </div>
    @endif

    <!-- Hero / Title -->
    <section class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
        <div>
            <h2 class="text-[22px] font-headline-lg font-extrabold text-on-surface tracking-tight leading-tight m-0">{{ $pageTitle }}</h2>
            <p class="text-[11px] text-slate-400 mt-0.5 m-0">Komando Pengendalian Tugas &amp; Penugasan Petugas Lapangan secara Real-time</p>
        </div>
        <div class="flex items-center gap-2">
            <button type="button" onclick="openTestNotifModal()" class="flex items-center justify-center gap-2 bg-slate-100 text-slate-700 hover:bg-slate-200 px-5 py-2.5 rounded-xl font-bold text-label-md transition-all border border-slate-200 cursor-pointer">
                <span class="material-symbols-outlined text-[20px]" style="font-variation-settings: 'FILL' 1;">notifications_active</span>
                <span class="hidden sm:inline">Uji Notifikasi</span>
            </button>
            <button type="button" onclick="openCreateModal()" class="flex items-center justify-center gap-2 bg-primary text-on-primary px-5 py-2.5 rounded-xl font-bold text-label-md shadow-lg shadow-primary/25 hover:shadow-xl hover:-translate-y-0.5 active:translate-y-0 transition-all border-0 cursor-pointer">
                <span class="material-symbols-outlined text-[20px]" style="font-variation-settings: 'FILL' 1;">add_alert</span>
                <span>Buat Tugas Baru</span>
            </button>
        </div>
    </section>

    <!-- Stats -->
    <section class="grid grid-cols-2 lg:grid-cols-4 gap-3">
        @foreach([
            ['icon' => 'assignment', 'bg' => 'bg-primary/10', 'color' => 'text-primary', 'label' => 'Total Tugas', 'val' => $stats['total'], 'num_class' => 'text-on-surface'],
            ['icon' => 'notification_important', 'bg' => 'bg-blue-100', 'color' => 'text-blue-600', 'label' => 'Tugas Baru (NEW)', 'val' => $stats['new'], 'num_class' => 'text-blue-600'],
            ['icon' => 'pending_actions', 'bg' => 'bg-amber-100', 'color' => 'text-amber-600', 'label' => 'Sedang Ditangani', 'val' => $stats['process'], 'num_class' => 'text-amber-600'],
            ['icon' => 'task_alt', 'bg' => 'bg-emerald-100', 'color' => 'text-emerald-600', 'label' => 'Selesai / Ditutup', 'val' => $stats['completed'], 'num_class' => 'text-emerald-600'],
        ] as $s)
        <div class="stat-card bg-white rounded-xl border border-slate-200/60 shadow-sm px-4 py-3 flex items-center gap-3">
            <div class="w-8 h-8 {{ $s['bg'] }} {{ $s['color'] }} rounded-lg flex items-center justify-center shrink-0">
                <span class="material-symbols-outlined text-[18px]" style="font-variation-settings: 'FILL' 1;">{{ $s['icon'] }}</span>
            </div>
            <div>
                <p class="text-[10px] text-slate-400 font-semibold leading-none mb-1">{{ $s['label'] }}</p>
                <p class="text-[20px] font-extrabold {{ $s['num_class'] }} leading-none m-0">{{ number_format($s['val'], 0, ',', '.') }}</p>
            </div>
        </div>
        @endforeach
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
    @if(session('error'))
        <div class="bg-rose-50 text-rose-800 border border-rose-200 rounded-xl p-4 flex justify-between items-center shadow-sm">
            <div class="flex items-center gap-3">
                <span class="material-symbols-outlined text-rose-500" style="font-variation-settings: 'FILL' 1;">error</span>
                <span class="font-medium text-sm">{{ session('error') }}</span>
            </div>
            <button type="button" class="text-rose-800/50 hover:text-rose-800 bg-transparent border-0 cursor-pointer" onclick="this.parentElement.remove()">
                <span class="material-symbols-outlined">close</span>
            </button>
        </div>
    @endif

    <!-- Filters -->
    <section class="bg-white rounded-xl border border-slate-200/60 p-4 shadow-sm">
        <form action="{{ route('opsdal.cad.index') }}" method="GET" class="flex flex-col sm:flex-row flex-wrap gap-3 items-end m-0">
            <div class="w-full sm:w-72">
                <label class="block text-[10px] font-bold text-slate-400 uppercase mb-1.5">Cari Tugas</label>
                <div class="relative">
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Judul, alamat, pelapor..." class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 text-xs focus:bg-white focus:border-primary outline-none transition-all"/>
                </div>
            </div>
            <div class="w-full sm:w-48">
                <label class="block text-[10px] font-bold text-slate-400 uppercase mb-1.5">Status</label>
                <select name="status" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 text-xs focus:bg-white focus:border-primary outline-none transition-all">
                    <option value="">Semua Status</option>
                    @foreach(['NEW' => 'Tugas Baru', 'DITERIMA' => 'Diterima', 'MENUJU' => 'Menuju TKP', 'TIBA' => 'Tiba di TKP', 'SELESAI' => 'Selesai', 'CLOSED' => 'Ditutup'] as $k => $v)
                        <option value="{{ $k }}" {{ request('status') == $k ? 'selected' : '' }}>{{ $v }}</option>
                    @endforeach
                </select>
            </div>
            <div class="w-full sm:w-48">
                <label class="block text-[10px] font-bold text-slate-400 uppercase mb-1.5">Skala Prioritas</label>
                <select name="priority" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 text-xs focus:bg-white focus:border-primary outline-none transition-all">
                    <option value="">Semua Prioritas</option>
                    @foreach(['HIGH' => 'Tinggi / High', 'MEDIUM' => 'Sedang / Medium', 'LOW' => 'Rendah / Low'] as $k => $v)
                        <option value="{{ $k }}" {{ request('priority') == $k ? 'selected' : '' }}>{{ $v }}</option>
                    @endforeach
                </select>
            </div>
            <div class="flex gap-2">
                <button type="submit" class="flex-1 bg-primary text-on-primary py-2 px-4 rounded-xl font-bold text-xs hover:bg-primary-dark transition-colors border-0 cursor-pointer">Filter</button>
                <a href="{{ route('opsdal.cad.index') }}" class="flex items-center justify-center bg-slate-100 text-slate-600 hover:bg-slate-200 px-3 py-2 rounded-xl text-decoration-none transition-colors"><span class="material-symbols-outlined text-[18px]">restart_alt</span></a>
            </div>
        </form>
    </section>

    <!-- Table -->
    <section class="bg-white rounded-xl border border-slate-200/60 overflow-hidden shadow-sm">
        <div class="px-4 py-3 border-b border-slate-100 flex items-center justify-between">
            <h3 class="text-[11px] font-bold uppercase tracking-wider text-slate-700 m-0">Daftar Penugasan CAD / Dispatch</h3>
        </div>

        @if($tasks->count())
            <div class="overflow-x-auto custom-scrollbar">
                <table class="w-full text-left min-w-[980px]">
                    <thead>
                        <tr class="bg-slate-50/50">
                            <th class="px-4 py-2 text-[10px] font-bold text-slate-400 uppercase tracking-widest border-b border-slate-100 w-10">#</th>
                            <th class="px-3 py-2 text-[10px] font-bold text-slate-400 uppercase tracking-widest border-b border-slate-100 w-[20%]">Judul Kejadian</th>
                            <th class="px-3 py-2 text-[10px] font-bold text-slate-400 uppercase tracking-widest border-b border-slate-100">Prioritas</th>
                            <th class="px-3 py-2 text-[10px] font-bold text-slate-400 uppercase tracking-widest border-b border-slate-100 w-[25%]">Lokasi / Alamat</th>
                            <th class="px-3 py-2 text-[10px] font-bold text-slate-400 uppercase tracking-widest border-b border-slate-100">Petugas Ditugaskan</th>
                            <th class="px-3 py-2 text-[10px] font-bold text-slate-400 uppercase tracking-widest border-b border-slate-100">Status</th>
                            <th class="px-3 py-2 text-[10px] font-bold text-slate-400 uppercase tracking-widest border-b border-slate-100">Waktu Kejadian</th>
                            <th class="px-3 py-2 text-[10px] font-bold text-slate-400 uppercase tracking-widest border-b border-slate-100 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach($tasks as $task)
                            <tr class="hover:bg-slate-50 group">
                                <td class="px-4 py-3.5 text-label-sm text-slate-400">{{ $loop->iteration + ($tasks->currentPage() - 1) * $tasks->perPage() }}</td>
                                <td class="px-3 py-3.5">
                                    <p class="text-[12px] font-bold text-slate-800 leading-tight m-0">{{ $task->title }}</p>
                                    <p class="text-[10px] text-slate-400 m-0 mt-0.5">Pelapor: <span class="font-medium text-slate-600">{{ $task->pelapor }}</span></p>
                                </td>
                                <td class="px-3 py-3.5">
                                    @if($task->priority === 'HIGH')
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[9px] font-bold bg-rose-50 text-rose-700 border border-rose-200/60">TINGGI</span>
                                    @elseif($task->priority === 'MEDIUM')
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[9px] font-bold bg-amber-50 text-amber-700 border border-amber-200/60">SEDANG</span>
                                    @else
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[9px] font-bold bg-sky-50 text-sky-700 border border-sky-200/60">RENDAH</span>
                                    @endif
                                </td>
                                <td class="px-3 py-3.5">
                                    <p class="text-[11px] text-slate-700 leading-normal m-0 line-clamp-2" title="{{ $task->address }}">{{ $task->address }}</p>
                                    @if($task->latitude && $task->longitude)
                                        <a href="https://www.google.com/maps/search/?api=1&query={{ $task->latitude }},{{ $task->longitude }}" target="_blank" class="inline-flex items-center gap-0.5 text-[9px] text-primary font-semibold hover:underline mt-1 text-decoration-none">
                                            <span class="material-symbols-outlined text-[11px]">location_on</span> Lihat Peta
                                        </a>
                                    @endif
                                </td>
                                <td class="px-3 py-3.5">
                                    <div class="flex -space-x-2 overflow-hidden hover:space-x-1 transition-all">
                                        @forelse($task->assignments as $assign)
                                            @php
                                                $avatar = !empty($assign->fotoprof)
                                                    ? (str_starts_with($assign->fotoprof, 'http') ? $assign->fotoprof : asset('storage/'.$assign->fotoprof))
                                                    : 'https://ui-avatars.com/api/?name='.urlencode($assign->nama).'&background=0041c8&color=fff&size=40';
                                            @endphp
                                            <img class="inline-block h-6 w-6 rounded-full ring-2 ring-white object-cover cursor-pointer" src="{{ $avatar }}" title="{{ $assign->nama }} ({{ $assign->assignment_status }})" alt=""/>
                                        @empty
                                            <span class="text-[10px] text-rose-500 font-medium">Belum ada petugas</span>
                                        @endforelse
                                    </div>
                                </td>
                                <td class="px-3 py-3.5">
                                    @if($task->status === 'NEW')
                                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-blue-50 text-blue-700 border border-blue-200/60">BARU</span>
                                    @elseif(in_array($task->status, ['DITERIMA', 'MENUJU', 'TIBA']))
                                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-amber-50 text-amber-700 border border-amber-200/60 shadow-sm animate-pulse">
                                            <span class="w-1 h-1 rounded-full bg-amber-500"></span>
                                            {{ $task->status }}
                                        </span>
                                    @elseif($task->status === 'SELESAI')
                                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200/60">SELESAI</span>
                                    @else
                                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-slate-100 text-slate-500 border border-slate-200/60">CLOSED</span>
                                    @endif
                                </td>
                                <td class="px-3 py-3.5">
                                    <span class="text-[11px] text-slate-600 block local-time" data-utc="{{ \Carbon\Carbon::parse($task->created_at)->timezone('UTC')->toIso8601String() }}">{{ \Carbon\Carbon::parse($task->created_at)->timezone('Asia/Jakarta')->format('d/m/Y H:i') }} WIB</span>
                                    <span class="text-[9px] text-slate-400">{{ \Carbon\Carbon::parse($task->created_at)->timezone('Asia/Jakarta')->diffForHumans() }}</span>
                                </td>
                                <td class="px-3 py-3.5 text-right">
                                    <div class="flex items-center justify-end gap-1 opacity-100 lg:opacity-0 group-hover:opacity-100 transition-opacity">
                                        <a href="{{ route('opsdal.cad.show', $task->id) }}" class="w-8 h-8 flex items-center justify-center text-primary hover:bg-primary/10 rounded-lg transition-colors text-decoration-none" title="Detail Tugas &amp; Progress">
                                            <span class="material-symbols-outlined text-[20px]">visibility</span>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="px-4 py-2.5 bg-slate-50/50 border-t border-slate-100 flex flex-col sm:flex-row items-center justify-between gap-3">
                <p class="text-[10px] text-slate-500 font-medium m-0">
                    Menampilkan <span class="font-bold text-slate-700">{{ $tasks->firstItem() ?? 0 }}–{{ $tasks->lastItem() ?? 0 }}</span>
                    dari {{ number_format($tasks->total(), 0, ',', '.') }} tugas.
                </p>
                <div class="pagination-container">
                    {{ $tasks->appends(request()->query())->links() }}
                </div>
            </div>
        @else
            <div class="py-12 text-center">
                <div class="w-12 h-12 bg-slate-100 rounded-full flex items-center justify-center mx-auto mb-3 text-slate-400">
                    <span class="material-symbols-outlined text-[26px]">notification_important</span>
                </div>
                <p class="text-label-md text-slate-500 font-medium max-w-md mx-auto m-0">Belum ada data tugas CAD yang sesuai dengan kriteria filter Anda.</p>
            </div>
        @endif
    </section>

    @if(isset($officerReports))
    <!-- Laporan Aktivitas Petugas (Eksekutif) -->
    <section class="bg-white rounded-xl border border-slate-200/60 overflow-hidden shadow-sm mt-4">
        <div class="px-4 py-3 border-b border-slate-100 flex items-center justify-between">
            <h3 class="text-[11px] font-bold uppercase tracking-wider text-slate-700 m-0">Laporan Aktivitas & Kehadiran Petugas</h3>
        </div>

        @php
            $user = auth()->user();
            $isNasional = empty($user->daerah) || \App\Helpers\HierarchicalHelper::viewerBypassesTerritoryScope();
            $isProvinsi = !empty($user->daerah) && empty($user->wilayah);
        @endphp

        <div class="p-4 bg-slate-50/50 border-b border-slate-100">
            <form method="GET" action="{{ route('opsdal.cad.index') }}" class="flex flex-col sm:flex-row flex-wrap gap-3 items-end m-0">
                <div class="w-full sm:w-48">
                    <label class="block text-[10px] font-bold text-slate-400 uppercase mb-1.5">Tanggal Laporan</label>
                    <input type="date" name="report_date" value="{{ request('report_date', now()->toDateString()) }}" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 text-xs focus:bg-white focus:border-primary outline-none transition-all">
                </div>
                
                @if($isNasional)
                <div class="w-full sm:w-56">
                    <label class="block text-[10px] font-bold text-slate-400 uppercase mb-1.5">Polda</label>
                    <input type="text" name="polda" value="{{ request('polda') }}" placeholder="Cari nama polda..." class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 text-xs focus:bg-white focus:border-primary outline-none transition-all">
                </div>
                @endif

                @if($isNasional || $isProvinsi)
                <div class="w-full sm:w-56">
                    <label class="block text-[10px] font-bold text-slate-400 uppercase mb-1.5">Polres</label>
                    <input type="text" name="polres" value="{{ request('polres') }}" placeholder="Cari nama polres..." class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 text-xs focus:bg-white focus:border-primary outline-none transition-all">
                </div>
                @endif

                <div class="flex gap-2">
                    <button type="submit" class="flex-1 bg-primary text-on-primary py-2 px-4 rounded-xl font-bold text-xs hover:bg-primary-dark transition-colors border-0 cursor-pointer">Filter</button>
                    @if(request('report_date') || request('polda') || request('polres'))
                    <a href="{{ route('opsdal.cad.index') }}" class="flex items-center justify-center bg-slate-100 text-slate-600 hover:bg-slate-200 px-3 py-2 rounded-xl text-decoration-none transition-colors"><span class="material-symbols-outlined text-[18px]">restart_alt</span></a>
                    @endif
                </div>
            </form>
        </div>

        <div class="overflow-x-auto custom-scrollbar">
            <table class="w-full text-left min-w-[700px]">
                <thead>
                    <tr class="bg-slate-100/50">
                        <th class="px-4 py-2 text-[10px] font-bold text-slate-500 uppercase tracking-widest border-b border-slate-200 w-10">#</th>
                        @if($isNasional)
                            <th class="px-3 py-2 text-[10px] font-bold text-slate-500 uppercase tracking-widest border-b border-slate-200">Polda</th>
                        @endif
                        @if($isNasional || $isProvinsi)
                            <th class="px-3 py-2 text-[10px] font-bold text-slate-500 uppercase tracking-widest border-b border-slate-200">Polres</th>
                        @endif
                        <th class="px-3 py-2 text-[10px] font-bold text-slate-500 uppercase tracking-widest border-b border-slate-200">Nama Petugas</th>
                        <th class="px-3 py-2 text-[10px] font-bold text-slate-500 uppercase tracking-widest border-b border-slate-200">Jam Enable (Online)</th>
                        <th class="px-3 py-2 text-[10px] font-bold text-slate-500 uppercase tracking-widest border-b border-slate-200">Jam Disable (Offline)</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($officerReports as $rep)
                        <tr class="hover:bg-slate-50">
                            <td class="px-4 py-3.5 text-label-sm text-slate-400">{{ $loop->iteration + ($officerReports->currentPage() - 1) * $officerReports->perPage() }}</td>
                            @if($isNasional)
                                <td class="px-3 py-3.5 text-[12px] font-semibold text-slate-700">{{ $rep->polda ?? '-' }}</td>
                            @endif
                            @if($isNasional || $isProvinsi)
                                <td class="px-3 py-3.5 text-[12px] font-semibold text-slate-700">{{ $rep->polres ?? '-' }}</td>
                            @endif
                            <td class="px-3 py-3.5 text-[12px] font-bold text-slate-800">{{ $rep->nama }}</td>
                            <td class="px-3 py-3.5">
                                @if($rep->jam_enable)
                                    <span class="inline-flex items-center gap-1 text-[11px] text-emerald-700 font-bold bg-emerald-50 px-2 py-0.5 rounded-full border border-emerald-200">
                                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span> {{ $rep->jam_enable }}
                                    </span>
                                @else
                                    <span class="text-slate-300">-</span>
                                @endif
                            </td>
                            <td class="px-3 py-3.5">
                                @if($rep->jam_disable)
                                    <span class="inline-flex items-center gap-1 text-[11px] text-rose-700 font-bold bg-rose-50 px-2 py-0.5 rounded-full border border-rose-200">
                                        <span class="w-1.5 h-1.5 rounded-full bg-rose-500"></span> {{ $rep->jam_disable }}
                                    </span>
                                @else
                                    <span class="text-slate-300">-</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ 3 + ($isNasional ? 1 : 0) + (($isNasional || $isProvinsi) ? 1 : 0) }}" class="px-4 py-8 text-center">
                                <div class="w-10 h-10 bg-slate-100 rounded-full flex items-center justify-center mx-auto mb-2 text-slate-400">
                                    <span class="material-symbols-outlined text-[20px]">person_off</span>
                                </div>
                                <p class="text-xs text-slate-500 font-medium m-0">Tidak ada data aktivitas petugas yang ditemukan.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        <div class="px-4 py-2.5 bg-slate-50/50 border-t border-slate-100 flex flex-col sm:flex-row items-center justify-between gap-3">
            <p class="text-[10px] text-slate-500 font-medium m-0">
                Menampilkan <span class="font-bold text-slate-700">{{ $officerReports->firstItem() ?? 0 }}–{{ $officerReports->lastItem() ?? 0 }}</span>
                dari {{ number_format($officerReports->total(), 0, ',', '.') }} petugas.
            </p>
            <div class="pagination-container">
                {{ $officerReports->appends(request()->query())->links() }}
            </div>
        </div>
    </section>
    @endif

</div>

<!-- Create Task Modal -->
<div id="createTaskModal" class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4 z-[9999] hidden">
    <div class="bg-white rounded-2xl border border-slate-100 shadow-2xl w-full max-w-[600px] overflow-hidden flex flex-col animate-in fade-in zoom-in duration-200">
        <div class="px-5 py-4 border-b border-slate-100 flex items-center justify-between bg-slate-50">
            <h4 class="text-sm font-extrabold text-slate-800 uppercase m-0 flex items-center gap-1.5">
                <span class="material-symbols-outlined text-primary text-[20px]">add_alert</span>
                Buat Tugas &amp; Dispatch Petugas
            </h4>
            <button type="button" onclick="closeCreateModal()" class="w-7 h-7 flex items-center justify-center text-slate-400 hover:text-slate-600 bg-transparent border-0 cursor-pointer"><span class="material-symbols-outlined text-[20px]">close</span></button>
        </div>
        <form action="{{ route('opsdal.cad.store') }}" method="POST" class="m-0 p-5 space-y-4 overflow-y-auto max-h-[75vh]">
            @csrf
            
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div class="col-span-2">
                    <label class="block text-[10px] font-bold text-slate-500 uppercase mb-1">Judul Kejadian / Tugas <span class="text-rose-500">*</span></label>
                    <input type="text" name="title" required placeholder="Contoh: Lakalantas Tol KM 12, Macet di Simpang..." class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 text-xs focus:bg-white focus:border-primary outline-none transition-all"/>
                </div>
                
                <div class="col-span-2">
                    <label class="block text-[10px] font-bold text-slate-500 uppercase mb-1">Alamat / Lokasi TKP <span class="text-rose-500">*</span></label>
                    <input type="text" name="address" required placeholder="Alamat lengkap kejadian..." class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 text-xs focus:bg-white focus:border-primary outline-none transition-all"/>
                </div>

            <div class="col-span-2 grid grid-cols-1 sm:grid-cols-[1fr_1fr_auto] gap-2 items-end">
                <div>
                    <label for="modal_lat" class="block text-[10px] font-bold text-slate-500 uppercase mb-1">Latitude <span class="text-rose-500">*</span></label>
                    <input type="text" name="latitude" id="modal_lat" required placeholder="-6.200000" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 text-xs focus:bg-white focus:border-primary outline-none transition-all"/>
                </div>
                <div>
                    <label for="modal_lng" class="block text-[10px] font-bold text-slate-500 uppercase mb-1">Longitude <span class="text-rose-500">*</span></label>
                    <input type="text" name="longitude" id="modal_lng" required placeholder="106.800000" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 text-xs focus:bg-white focus:border-primary outline-none transition-all"/>
                </div>
                <div>
                    <button type="button" id="btn_gps_cad_form"
                        class="w-full flex items-center justify-center gap-1.5 px-3 py-2 bg-amber-50 hover:bg-amber-100 text-amber-800 border border-amber-200 font-bold rounded-lg text-[12px] transition-all border-solid whitespace-nowrap"
                        data-sbo-gps-lat="modal_lat"
                        data-sbo-gps-lng="modal_lng"
                        data-sbo-gps-status="gps_status_cad_form">
                        <span class="material-symbols-outlined text-[16px]">my_location</span>
                        <span>GPS</span>
                    </button>
                </div>
                <span class="sr-only" id="gps_status_cad_form"></span>
            </div>

            <div>
                <label class="block text-[10px] font-bold text-slate-500 uppercase mb-1">Skala Prioritas <span class="text-rose-500">*</span></label>
                <select name="priority" required class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3 py-2.5 text-xs focus:bg-white focus:border-primary outline-none transition-all">
                    <option value="MEDIUM">SEDANG / MEDIUM</option>
                    <option value="HIGH">TINGGI / HIGH</option>
                    <option value="LOW">RENDAH / LOW</option>
                </select>
            </div>
            <div>
                <label class="block text-[10px] font-bold text-slate-500 uppercase mb-1">Nama Pelapor / Sumber</label>
                <input type="text" name="pelapor" placeholder="Contoh: Command Center, Warga..." class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 text-xs focus:bg-white focus:border-primary outline-none transition-all"/>
            </div>

            <div class="col-span-2">
                <label class="block text-[10px] font-bold text-slate-500 uppercase mb-1">Keterangan / Detail Laporan</label>
                <textarea name="keterangan" rows="3" placeholder="Jelaskan detail kejadian untuk memandu petugas di lapangan..." class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 text-xs focus:bg-white focus:border-primary outline-none transition-all resize-none"></textarea>
            </div>

            <div class="col-span-2">
                <label class="block text-[10px] font-bold text-slate-500 uppercase mb-1">Tugaskan Ke Petugas Online <span class="text-rose-500">* (Bisa pilih lebih dari satu)</span></label>
                <div class="border border-slate-200 rounded-xl p-3 bg-slate-50 max-h-[150px] overflow-y-auto space-y-2">
                    @forelse($onlineOfficers as $officer)
                        <label class="flex items-center gap-2 cursor-pointer py-0.5">
                            <input type="checkbox" name="assign_to[]" value="{{ $officer->id_usm }}" class="rounded text-primary border-slate-300 focus:ring-primary"/>
                            <span class="text-xs font-semibold text-slate-700">{{ $officer->nama }}</span>
                            <span class="text-[9px] px-1.5 py-0.25 rounded-full font-bold {{ $officer->tracking_status === 'BERTUGAS' ? 'bg-rose-50 text-rose-700 border border-rose-200' : 'bg-emerald-50 text-emerald-700 border border-emerald-200' }}">{{ $officer->tracking_status }}</span>
                        </label>
                    @empty
                        <div class="text-center py-4 text-xs text-slate-400">Tidak ada petugas online saat ini.</div>
                    @endforelse
                </div>
            </div>
        </div>

        <div class="pt-4 border-t border-slate-100 flex items-center justify-end gap-2">
            <button type="button" onclick="closeCreateModal()" class="bg-white hover:bg-slate-50 text-slate-500 font-bold text-xs py-2.5 px-4 rounded-xl border border-slate-200 transition-colors cursor-pointer">Batal</button>
            <button type="submit" class="bg-primary text-on-primary font-bold text-xs py-2.5 px-5 rounded-xl hover:bg-primary-dark transition-colors border-0 cursor-pointer">Kirim &amp; Tugaskan</button>
        </div>
    </form>
</div>
</div>

<!-- Test Notif Modal -->
<div id="testNotifModal" class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4 z-[9999] hidden">
    <div class="bg-white rounded-2xl border border-slate-100 shadow-2xl w-full max-w-[500px] overflow-hidden flex flex-col animate-in fade-in zoom-in duration-200">
        <div class="px-5 py-4 border-b border-slate-100 flex items-center justify-between bg-slate-50">
            <h4 class="text-sm font-extrabold text-slate-800 uppercase m-0 flex items-center gap-1.5">
                <span class="material-symbols-outlined text-primary text-[20px]">notifications_active</span>
                Uji Notifikasi FCM
            </h4>
            <button type="button" onclick="closeTestNotifModal()" class="w-7 h-7 flex items-center justify-center text-slate-400 hover:text-slate-600 bg-transparent border-0 cursor-pointer"><span class="material-symbols-outlined text-[20px]">close</span></button>
        </div>
        <form action="{{ route('opsdal.cad.test-fcm') }}" method="POST" class="m-0 p-5 space-y-4 overflow-y-auto max-h-[75vh]">
            @csrf
            
            <div>
                <label class="block text-[10px] font-bold text-slate-500 uppercase mb-1">Pesan Uji Coba <span class="text-rose-500">*</span></label>
                <input type="text" name="test_message" required value="Ini adalah notifikasi uji coba dari opsdal CAD" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 text-xs focus:bg-white focus:border-primary outline-none transition-all"/>
            </div>

            <div>
                <label class="block text-[10px] font-bold text-slate-500 uppercase mb-1">Pilih Petugas (Bisa Lebih Dari Satu) <span class="text-rose-500">*</span></label>
                <div class="border border-slate-200 rounded-xl p-3 bg-slate-50 max-h-[200px] overflow-y-auto space-y-2">
                    @forelse($onlineOfficers as $officer)
                        <label class="flex items-center gap-2 cursor-pointer py-0.5">
                            <input type="checkbox" name="test_assign_to[]" value="{{ $officer->id_usm }}" class="rounded text-primary border-slate-300 focus:ring-primary"/>
                            <span class="text-xs font-semibold text-slate-700">{{ $officer->nama }}</span>
                            <span class="text-[9px] px-1.5 py-0.25 rounded-full font-bold {{ $officer->tracking_status === 'BERTUGAS' ? 'bg-rose-50 text-rose-700 border border-rose-200' : 'bg-emerald-50 text-emerald-700 border border-emerald-200' }}">{{ $officer->tracking_status }}</span>
                        </label>
                    @empty
                        <div class="text-center py-4 text-xs text-slate-400">Tidak ada petugas online saat ini.</div>
                    @endforelse
                </div>
            </div>

            <div class="pt-4 border-t border-slate-100 flex items-center justify-end gap-2">
                <button type="button" onclick="closeTestNotifModal()" class="bg-white hover:bg-slate-50 text-slate-500 font-bold text-xs py-2.5 px-4 rounded-xl border border-slate-200 transition-colors cursor-pointer">Batal</button>
                <button type="submit" class="bg-primary text-on-primary font-bold text-xs py-2.5 px-5 rounded-xl hover:bg-primary-dark transition-colors border-0 cursor-pointer">Kirim Notifikasi</button>
            </div>
        </form>
    </div>
</div>

<script>
function openCreateModal() {
    document.getElementById('createTaskModal').classList.remove('hidden');
    // Trigger map invalidation and size refresh for Leaflet inside modal
    setTimeout(function() {
        window.dispatchEvent(new Event('resize'));
    }, 250);

    // Try to get dispatcher location if available
    if (navigator.geolocation) {
        navigator.geolocation.getCurrentPosition(function(position) {
            var latEl = document.getElementById('modal_lat');
            var lngEl = document.getElementById('modal_lng');
            if (latEl && lngEl) {
                latEl.value = position.coords.latitude.toFixed(6);
                lngEl.value = position.coords.longitude.toFixed(6);
                latEl.dispatchEvent(new Event('change', { bubbles: true }));
                lngEl.dispatchEvent(new Event('change', { bubbles: true }));
            }
        });
    }
}
function closeCreateModal() {
    document.getElementById('createTaskModal').classList.add('hidden');
}
function openTestNotifModal() {
    document.getElementById('testNotifModal').classList.remove('hidden');
}
function closeTestNotifModal() {
    document.getElementById('testNotifModal').classList.add('hidden');
}
</script>

<script>
document.addEventListener("DOMContentLoaded", function() {
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
