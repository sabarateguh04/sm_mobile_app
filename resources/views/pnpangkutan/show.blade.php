@extends('layouts.app')
@section('title', $pageTitle)
@section('page-title', $pageTitle)

@section('content')
@php
    $fotoUrls = [];
    if (! empty($data->fotogiat)) {
        foreach (explode(',', (string) $data->fotogiat) as $foto) {
            $f = trim($foto);
            if ($f === '') continue;
            $fotoUrls[] = str_starts_with($f, 'http') ? $f : asset(ltrim($f, '/'));
        }
    }
    $hasFoto = $fotoUrls !== [];

    $kLat = $kLng = null;
    $kOk = false;
    if (! empty($data->koordinat)) {
        $kParts = preg_split('/\s*,\s*/', (string) $data->koordinat, 2);
        $kLat = $kParts[0] ?? '';
        $kLng = $kParts[1] ?? '';
        $kOk = $kLng !== '' && is_numeric($kLat) && is_numeric($kLng);
    }

    $tanggalFmt = $data->tanggal ? \Carbon\Carbon::parse($data->tanggal)->locale('id')->translatedFormat('d F Y') : '—';
    $canMutate = !($sboViewOnly ?? false) && \App\Support\RoleMutationPolicy::canMutateOperationalRow(auth()->user(), $data, $tableName ?? 'pnp_angkutan');
@endphp

<div class="max-w-[960px] mx-auto w-full space-y-4">
    <div class="flex items-center gap-2 flex-wrap">
        <a href="{{ route('pnpangkutan.index') }}" class="flex items-center gap-1 text-slate-500 hover:text-primary text-decoration-none text-[12px] font-semibold transition-colors">
            <span class="material-symbols-outlined text-[16px]">arrow_back</span><span>Kembali</span>
        </a>
        <span class="text-slate-300">/</span>
        <span class="text-[12px] font-bold text-on-surface">{{ $pageTitle }}</span>
    </div>

    <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
        <div class="bg-white rounded-xl border border-slate-200/60 shadow-sm px-4 py-3">
            <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wide mb-1">Tanggal</p>
            <p class="text-[13px] font-bold text-slate-800 mb-0">{{ $tanggalFmt }}</p>
        </div>
        <div class="bg-white rounded-xl border border-slate-200/60 shadow-sm px-4 py-3">
            <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wide mb-1">Terminal</p>
            <p class="text-[13px] font-bold text-slate-800 mb-0 line-clamp-2">{{ $data->namaterminal ?: '—' }}</p>
            @if($data->jenisterminal)
                <p class="text-[11px] text-slate-400 mt-0.5 mb-0">{{ $data->jenisterminal }}</p>
            @endif
        </div>
        <div class="bg-white rounded-xl border border-slate-200/60 shadow-sm px-4 py-3">
            <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wide mb-1">PNP</p>
            <p class="text-[13px] font-bold text-emerald-700 mb-0">{{ number_format($data->kedatanganpnp ?? 0, 0, ',', '.') }} <span class="text-slate-400 font-normal">↓</span></p>
            <p class="text-[13px] font-bold text-rose-700 mb-0">{{ number_format($data->keberangkatanpnp ?? 0, 0, ',', '.') }} <span class="text-slate-400 font-normal">↑</span></p>
        </div>
        <div class="bg-white rounded-xl border border-slate-200/60 shadow-sm px-4 py-3">
            <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wide mb-1">Angkutan</p>
            <p class="text-[13px] font-bold text-blue-700 mb-0">{{ number_format($data->kedatanganangk ?? 0, 0, ',', '.') }} <span class="text-slate-400 font-normal">↓</span></p>
            <p class="text-[13px] font-bold text-amber-700 mb-0">{{ number_format($data->keberangkatanangk ?? 0, 0, ',', '.') }} <span class="text-slate-400 font-normal">↑</span></p>
        </div>
    </div>

    <div class="bg-white rounded-xl border border-slate-200/60 shadow-sm overflow-hidden">
        <div class="px-4 py-2.5 border-b border-slate-100 bg-slate-50/40 flex items-center gap-2">
            <span class="material-symbols-outlined text-primary text-[15px]" style="font-variation-settings: 'FILL' 1;">directions_bus</span>
            <h3 class="text-[12px] font-bold text-slate-800 m-0">{{ $pageTitle }}</h3>
        </div>
        <div class="p-3 sm:p-4">
            <div class="flex flex-col gap-4">
                <div>
                    <dl class="grid grid-cols-[140px_1fr] md:grid-cols-[160px_1fr] gap-x-4 gap-y-3 mb-0 items-center">
                        <dt class="text-[11px] font-bold text-slate-500 uppercase tracking-wide">Piket</dt>
                        <dd class="text-[13px] text-slate-800 font-medium m-0">{{ $data->usm_nama ?? '—' }}</dd>
                        
                        <dt class="text-[11px] font-bold text-slate-500 uppercase tracking-wide">Koresponden</dt>
                        <dd class="text-[13px] text-slate-800 font-medium m-0">{{ $data->koresponden ?? '—' }}</dd>
                        
                        <dt class="text-[11px] font-bold text-slate-500 uppercase tracking-wide">Kedatangan PNP</dt>
                        <dd class="text-[18px] font-bold text-emerald-700 m-0">{{ number_format($data->kedatanganpnp ?? 0, 0, ',', '.') }}</dd>
                        
                        <dt class="text-[11px] font-bold text-slate-500 uppercase tracking-wide">Keberangkatan PNP</dt>
                        <dd class="text-[18px] font-bold text-rose-700 m-0">{{ number_format($data->keberangkatanpnp ?? 0, 0, ',', '.') }}</dd>
                        
                        <dt class="text-[11px] font-bold text-slate-500 uppercase tracking-wide">Kedatangan Angkutan</dt>
                        <dd class="text-[13px] text-slate-800 font-medium m-0">{{ number_format($data->kedatanganangk ?? 0, 0, ',', '.') }}</dd>
                        
                        <dt class="text-[11px] font-bold text-slate-500 uppercase tracking-wide">Keberangkatan Angkutan</dt>
                        <dd class="text-[13px] text-slate-800 font-medium m-0">{{ number_format($data->keberangkatanangk ?? 0, 0, ',', '.') }}</dd>
                        
                        @if(!empty($data->koordinat))
                        <dt class="text-[11px] font-bold text-slate-500 uppercase tracking-wide self-start mt-1">Koordinat GPS</dt>
                        <dd class="m-0 flex items-center gap-2 flex-wrap">
                            <code class="bg-slate-100 px-2 py-0.5 rounded text-[12px]">{{ $data->koordinat }}</code>
                            @if($kOk)
                                <a href="https://maps.google.com/?q={{ $kLat }},{{ $kLng }}" target="_blank" rel="noopener noreferrer" class="flex items-center gap-1 text-primary hover:underline text-decoration-none text-[12px]">
                                    <span class="material-symbols-outlined text-[14px]">map</span>Google Maps
                                </a>
                            @endif
                        </dd>
                        @endif
                    </dl>
                </div>
                @if($hasFoto)
                <div class="pt-4 border-t border-slate-100">
                    <p class="text-[11px] font-bold text-slate-500 uppercase tracking-wide mb-2 m-0">Foto Giat</p>
                    <div class="flex flex-wrap gap-2 mt-2">
                        @foreach($fotoUrls as $fotoUrl)
                            <a href="{{ $fotoUrl }}" target="_blank" rel="noopener noreferrer" class="block">
                                <img src="{{ $fotoUrl }}" alt="Foto giat" class="rounded-xl border border-slate-200 object-cover" style="width:160px;height:120px;">
                            </a>
                        @endforeach
                    </div>
                </div>
                @endif
            </div>
            <div class="flex items-center justify-between pt-3 mt-3 border-t border-slate-100 flex-wrap gap-2">
                <a href="{{ route('pnpangkutan.index') }}" class="flex items-center gap-1.5 px-3 py-2 bg-slate-100 hover:bg-slate-200 text-slate-600 font-bold rounded-lg text-[12px] transition-all text-decoration-none">
                    <span class="material-symbols-outlined text-[16px]">arrow_back</span><span>Kembali</span>
                </a>
                <div class="flex items-center gap-2">
                    @include('_partials.show_export_dropdown', ['exportRouteName' => 'pnpangkutan.export', 'rowId' => $data->{$primaryKey}])
                @if($canMutate)
                    <a href="{{ route('pnpangkutan.edit', $data->{$primaryKey}) }}" class="flex items-center gap-1.5 px-3 py-2 bg-amber-50 hover:bg-amber-100 text-amber-700 border border-amber-200 font-bold rounded-lg text-[12px] transition-all text-decoration-none">
                        <span class="material-symbols-outlined text-[16px]">edit</span><span>Edit</span>
                    </a>
                    <form action="{{ route('pnpangkutan.destroy', $data->{$primaryKey}) }}" method="POST" class="inline m-0" onsubmit="return confirm('Yakin hapus data ini?')">
                        @csrf @method('DELETE')
                        <button type="submit" class="flex items-center gap-1.5 px-3 py-2 bg-red-50 hover:bg-red-100 text-red-700 border border-red-200 font-bold rounded-lg text-[12px] transition-all border-solid">
                            <span class="material-symbols-outlined text-[16px]">delete</span><span>Hapus</span>
                        </button>
                    </form>
                @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
