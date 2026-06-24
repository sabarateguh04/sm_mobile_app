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
            <div class="space-y-6">
                <div>
                    <dl class="space-y-3 mb-0">
                        @if(!empty($data->daerah) || !empty($data->wilayah) || ($readOnly ?? false))
                            <div class="flex flex-col sm:flex-row sm:items-center py-2 border-b border-slate-100/80">
                                <dt class="w-full sm:w-1/3 text-[11px] font-bold text-slate-700 uppercase tracking-wide">Polda</dt>
                                <dd class="w-full sm:w-2/3 text-[13px] font-bold m-0 sm:pl-4" style="color:#D8A808;">{{ $data->daerah ? strtoupper($data->daerah) : '—' }}</dd>
                            </div>
                            <div class="flex flex-col sm:flex-row sm:items-center py-2 border-b border-slate-100/80">
                                <dt class="w-full sm:w-1/3 text-[11px] font-bold text-slate-700 uppercase tracking-wide">Polres</dt>
                                <dd class="w-full sm:w-2/3 text-[13px] text-slate-800 font-semibold m-0 sm:pl-4">{{ $data->wilayah ? strtoupper(trim(str_ireplace(['KABUPATEN ', 'KOTA ', 'KAB. '], '', $data->wilayah))) : '—' }}</dd>
                            </div>
                        @endif
                        <div class="flex flex-col sm:flex-row sm:items-center py-2 border-b border-slate-100/80">
                            <dt class="w-full sm:w-1/3 text-[11px] font-bold text-slate-700 uppercase tracking-wide">Tanggal</dt>
                            <dd class="w-full sm:w-2/3 text-[13px] text-slate-800 font-semibold m-0 sm:pl-4">{{ $data->tanggal ? \Carbon\Carbon::parse($data->tanggal)->format('d/m/Y') : '—' }}</dd>
                        </div>
                        <div class="flex flex-col sm:flex-row sm:items-center py-2 border-b border-slate-100/80">
                            <dt class="w-full sm:w-1/3 text-[11px] font-bold text-slate-700 uppercase tracking-wide">Saluran</dt>
                            <dd class="w-full sm:w-2/3 text-[13px] text-slate-800 font-semibold m-0 sm:pl-4">{{ $data->saluran ?: '—' }}</dd>
                        </div>
                        <div class="flex flex-col sm:flex-row sm:items-center py-2 border-b border-slate-100/80">
                            <dt class="w-full sm:w-1/3 text-[11px] font-bold text-slate-700 uppercase tracking-wide">Kategori</dt>
                            <dd class="w-full sm:w-2/3 text-[13px] text-slate-800 font-semibold m-0 sm:pl-4">{{ $data->kategori ?: '—' }}</dd>
                        </div>
                        <div class="flex flex-col sm:flex-row sm:items-center py-2 border-b border-slate-100/80">
                            <dt class="w-full sm:w-1/3 text-[11px] font-bold text-slate-700 uppercase tracking-wide">Narasumber</dt>
                            <dd class="w-full sm:w-2/3 text-[13px] text-slate-800 font-semibold m-0 sm:pl-4">
                                {{ $data->narsum ?: '—' }}
                                @if($data->telp_narsum)
                                    <span class="inline-block bg-slate-100 px-2 py-0.5 rounded text-[11px] text-slate-600 font-semibold ml-2">{{ $data->telp_narsum }}</span>
                                @endif
                            </dd>
                        </div>
                        <div class="flex flex-col sm:flex-row sm:items-center py-2 border-b border-slate-100/80">
                            <dt class="w-full sm:w-1/3 text-[11px] font-bold text-slate-700 uppercase tracking-wide">Tema</dt>
                            <dd class="w-full sm:w-2/3 text-[13px] text-slate-800 font-semibold m-0 sm:pl-4">{{ $data->tema ?: '—' }}</dd>
                        </div>
                        <div class="flex flex-col sm:flex-row sm:items-start py-2 border-b border-slate-100/80">
                            <dt class="w-full sm:w-1/3 text-[11px] font-bold text-slate-700 uppercase tracking-wide pt-0.5">Keterangan</dt>
                            <dd class="w-full sm:w-2/3 text-[13px] text-slate-800 font-medium m-0 sm:pl-4 whitespace-pre-line">{{ $data->keterangan ?: '—' }}</dd>
                        </div>
                        @if(!empty($data->koordinat))
                            <div class="flex flex-col sm:flex-row sm:items-center py-2">
                                <dt class="w-full sm:w-1/3 text-[11px] font-bold text-slate-700 uppercase tracking-wide">Koordinat GPS</dt>
                                <dd class="w-full sm:w-2/3 m-0 sm:pl-4 flex items-center gap-2 flex-wrap">
                                    <code class="bg-slate-100 px-2 py-0.5 rounded text-[12px] select-all font-semibold">{{ $data->koordinat }}</code>
                                    @if($kOk)
                                        <a href="https://www.google.com/maps?q={{ $kLat }},{{ $kLng }}" target="_blank" rel="noopener noreferrer"
                                           class="flex items-center gap-1 text-primary hover:underline text-decoration-none text-[12px] font-bold">
                                            <span class="material-symbols-outlined text-[14px]">map</span>Google Maps
                                        </a>
                                    @endif
                                </dd>
                            </div>
                        @endif
                    </dl>
                </div>

                @if($fotoUrl)
                    <div class="pt-4 border-t border-slate-100/80">
                        <p class="text-[11px] font-bold text-slate-700 uppercase tracking-wide mb-2">Foto Interaksi</p>
                        <div class="max-w-[400px]">
                            <a href="{{ $fotoUrl }}" target="_blank" rel="noopener noreferrer" class="block rounded-xl overflow-hidden border border-slate-200 hover:opacity-95 transition-opacity">
                                <img src="{{ $fotoUrl }}" alt="Foto interaksi" class="w-full rounded-xl" style="max-height:300px;object-fit:cover;" referrerpolicy="no-referrer">
                            </a>
                        </div>
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
