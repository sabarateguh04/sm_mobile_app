@extends('layouts.app')
@section('title', $pageTitle)
@section('page-title', $pageTitle)

@section('content')
@php
    $fotoUrl = \App\Http\Controllers\InteraksiLayananController::fotoPublicUrl($data->foto ?? null);
    $canMutate = ! ($readOnly ?? false) && ! ($sboViewOnly ?? false)
        && ($canMutateRecord ?? \App\Support\RoleMutationPolicy::canMutateOperationalRow(auth()->user(), $data, $tableName ?? 'layanan_masyarakat'));
    $isOwnRow = \App\Support\RoleMutationPolicy::isRowOwnedByActor(auth()->user(), $data, $tableName ?? 'layanan_masyarakat');
    $indexRoute = ($readOnly ?? false)
        ? route('layanan.masyarakat.index', ['sb_grp' => 'public_service'])
        : route('layanan.masyarakat.index');
    $judul = $data->tema ?: 'Interaksi Layanan Masyarakat';

    $kLat = $kLng = null;
    $kOk = false;
    if (! empty($data->koordinat)) {
        $kParts = preg_split('/\s*,\s*/', (string) $data->koordinat, 2);
        $kLat = $kParts[0] ?? '';
        $kLng = $kParts[1] ?? '';
        $kOk = $kLng !== '' && is_numeric($kLat) && is_numeric($kLng);
    }
@endphp
<div class="max-w-[900px] mx-auto w-full space-y-4">
    <div class="flex items-center gap-3 flex-wrap">
        <a href="{{ $indexRoute }}" class="flex items-center gap-1.5 text-slate-500 hover:text-primary text-decoration-none text-label-md font-semibold transition-colors">
            <span class="material-symbols-outlined text-[18px]">arrow_back</span><span>Kembali</span>
        </a>
        <span class="text-slate-300">/</span>
        <span class="text-[13px] font-bold text-on-surface">{{ \Str::limit($judul, 48) }}</span>
        @if($isOwnRow && !($readOnly ?? false))
            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-secondary/10 text-secondary border border-secondary/20">Data Anda</span>
        @endif
    </div>

    <div class="bg-white rounded-2xl border border-slate-200/60 shadow-sm overflow-hidden">
        <div class="px-5 py-3.5 border-b border-slate-100 bg-slate-50/30 flex items-center justify-between gap-2 flex-wrap">
            <div class="flex items-center gap-2 min-w-0">
                <span class="material-symbols-outlined text-primary text-[16px] shrink-0" style="font-variation-settings: 'FILL' 1;">volunteer_activism</span>
                <h3 class="text-[13px] font-bold text-slate-800 m-0 truncate">{{ $judul }}</h3>
            </div>
            @if($data->kategori)
                <span class="inline-flex items-center px-2.5 py-1 rounded-lg bg-primary/10 text-primary text-[11px] font-bold shrink-0">{{ $data->kategori }}</span>
            @endif
        </div>

        <div class="p-5">
            <div class="grid grid-cols-1 {{ $fotoUrl ? 'lg:grid-cols-3' : '' }} gap-6">
                <div class="{{ $fotoUrl ? 'lg:col-span-2' : '' }}">
                    <dl class="grid grid-cols-1 md:grid-cols-2 gap-x-6 gap-y-3 mb-0">
                        @if(!empty($data->daerah) || !empty($data->wilayah) || ($readOnly ?? false))
                            <div>
                                <dt class="text-[11px] font-bold text-slate-500 uppercase tracking-wide mb-0.5">Polda</dt>
                                <dd class="text-[13px] font-bold m-0" style="color:#D8A808;">{{ $data->daerah ? strtoupper($data->daerah) : '—' }}</dd>
                            </div>
                            <div>
                                <dt class="text-[11px] font-bold text-slate-500 uppercase tracking-wide mb-0.5">Polres</dt>
                                <dd class="text-[13px] text-slate-800 font-medium m-0">{{ $data->wilayah ? strtoupper(trim(str_ireplace(['KABUPATEN ', 'KOTA ', 'KAB. '], '', $data->wilayah))) : '—' }}</dd>
                            </div>
                        @endif
                        <div>
                            <dt class="text-[11px] font-bold text-slate-500 uppercase tracking-wide mb-0.5">Tanggal</dt>
                            <dd class="text-[13px] text-slate-800 font-medium m-0">{{ $data->tanggal ? \Carbon\Carbon::parse($data->tanggal)->format('d/m/Y') : '—' }}</dd>
                        </div>
                        <div>
                            <dt class="text-[11px] font-bold text-slate-500 uppercase tracking-wide mb-0.5">Saluran</dt>
                            <dd class="text-[13px] text-slate-800 font-medium m-0">{{ $data->saluran ?: '—' }}</dd>
                        </div>
                        <div>
                            <dt class="text-[11px] font-bold text-slate-500 uppercase tracking-wide mb-0.5">Kategori</dt>
                            <dd class="text-[13px] text-slate-800 font-medium m-0">{{ $data->kategori ?: '—' }}</dd>
                        </div>
                        <div>
                            <dt class="text-[11px] font-bold text-slate-500 uppercase tracking-wide mb-0.5">Narasumber</dt>
                            <dd class="text-[13px] text-slate-800 font-medium m-0">
                                {{ $data->narsum ?: '—' }}
                                @if($data->telp_narsum)
                                    <span class="block text-[11px] text-slate-400 font-normal mt-0.5">{{ $data->telp_narsum }}</span>
                                @endif
                            </dd>
                        </div>
                        <div class="md:col-span-2">
                            <dt class="text-[11px] font-bold text-slate-500 uppercase tracking-wide mb-0.5">Tema</dt>
                            <dd class="text-[13px] text-slate-800 font-semibold m-0">{{ $data->tema ?: '—' }}</dd>
                        </div>
                        <div class="md:col-span-2">
                            <dt class="text-[11px] font-bold text-slate-500 uppercase tracking-wide mb-0.5">Keterangan</dt>
                            <dd class="text-[13px] text-slate-800 font-medium m-0 whitespace-pre-line">{{ $data->keterangan ?: '—' }}</dd>
                        </div>
                        @if(!empty($data->koordinat))
                            <div class="md:col-span-2">
                                <dt class="text-[11px] font-bold text-slate-500 uppercase tracking-wide mb-0.5">Koordinat GPS</dt>
                                <dd class="m-0 flex items-center gap-2 flex-wrap">
                                    <code class="bg-slate-100 px-2 py-0.5 rounded text-[12px] select-all">{{ $data->koordinat }}</code>
                                    @if($kOk)
                                        <a href="https://www.google.com/maps?q={{ $kLat }},{{ $kLng }}" target="_blank" rel="noopener noreferrer"
                                           class="flex items-center gap-1 text-primary hover:underline text-decoration-none text-[12px]">
                                            <span class="material-symbols-outlined text-[14px]">map</span>Google Maps
                                        </a>
                                    @endif
                                </dd>
                            </div>
                        @endif
                    </dl>
                </div>

                @if($fotoUrl)
                    <div>
                        <p class="text-[11px] font-bold text-slate-500 uppercase tracking-wide mb-2">Foto Interaksi</p>
                        <a href="{{ $fotoUrl }}" target="_blank" rel="noopener noreferrer" class="block rounded-xl overflow-hidden border border-slate-200 hover:opacity-95 transition-opacity">
                            <img src="{{ $fotoUrl }}" alt="Foto interaksi" class="w-full rounded-xl" style="max-height:220px;object-fit:cover;" referrerpolicy="no-referrer">
                        </a>
                    </div>
                @endif
            </div>

            <div class="flex items-center justify-between pt-4 mt-4 border-t border-slate-100 flex-wrap gap-2">
                <a href="{{ $indexRoute }}" class="flex items-center gap-2 px-4 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-600 font-bold rounded-xl text-label-md transition-all text-decoration-none">
                    <span class="material-symbols-outlined text-[18px]">arrow_back</span><span>Kembali</span>
                </a>
                <div class="flex items-center gap-2 flex-wrap">
                    @include('_partials.show_export_dropdown', [
                        'exportRouteName' => 'layanan.masyarakat.export',
                        'rowId' => $data->id_interaksi,
                        'exportQuery' => ($readOnly ?? false) ? ['sb_grp' => 'public_service'] : [],
                    ])
                @if($canMutate)
                        <a href="{{ route('layanan.masyarakat.edit', $data->id_interaksi) }}"
                           class="flex items-center gap-2 px-4 py-2.5 bg-amber-50 hover:bg-amber-100 text-amber-700 border border-amber-200 font-bold rounded-xl text-label-md transition-all text-decoration-none">
                            <span class="material-symbols-outlined text-[18px]">edit</span><span>Edit</span>
                        </a>
                        <form action="{{ route('layanan.masyarakat.destroy', $data->id_interaksi) }}" method="POST" class="d-inline m-0"
                              onsubmit="return confirm('Yakin hapus data interaksi ini?')">
                            @csrf @method('DELETE')
                            <button type="submit" class="flex items-center gap-2 px-4 py-2.5 bg-red-50 hover:bg-red-100 text-red-700 border border-red-200 font-bold rounded-xl text-label-md transition-all border-0">
                                <span class="material-symbols-outlined text-[18px]">delete</span><span>Hapus</span>
                            </button>
                        </form>
                @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
