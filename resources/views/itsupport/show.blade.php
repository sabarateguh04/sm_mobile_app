@extends('layouts.app')
@php $pageTitle = $pageTitle ?? 'Detail Tiket IT Support'; @endphp
@section('title', $pageTitle)
@section('page-title', $pageTitle)

@section('content')
@php
    $fotoUrl = null;
    if (!empty($data->fotokondisi)) {
        $fotoUrl = preg_match('#^https?://#i', $data->fotokondisi)
            ? $data->fotokondisi
            : \Illuminate\Support\Facades\Storage::disk('public')->url($data->fotokondisi);
    }
    $tg = strtolower($data->tingkatgangguan ?? '');
    $st = strtolower($data->statusticket ?? '');
    $canMutate = $canMutateRecord ?? \App\Support\RoleMutationPolicy::canMutateOperationalRow(auth()->user(), $data, 'itsupport');
@endphp
<div class="max-w-[900px] mx-auto w-full space-y-4">
    <div class="flex items-center gap-3">
        <a href="{{ route('itsupport.index') }}" class="flex items-center gap-1.5 text-slate-500 hover:text-primary text-decoration-none text-label-md font-semibold transition-colors">
            <span class="material-symbols-outlined text-[18px]">arrow_back</span><span>Kembali</span>
        </a>
        <span class="text-slate-300">/</span>
        <span class="text-[13px] font-bold text-on-surface">Tiket #{{ $data->id_itsupport }}</span>
    </div>
    <div class="bg-white rounded-2xl border border-slate-200/60 shadow-sm overflow-hidden">
        <div class="px-5 py-3.5 border-b border-slate-100 bg-slate-50/30 flex items-center justify-between gap-2">
            <div class="flex items-center gap-2">
                <span class="material-symbols-outlined text-primary text-[16px]" style="font-variation-settings: 'FILL' 1;">support_agent</span>
                <h3 class="text-[13px] font-bold text-slate-800 m-0">Tiket #{{ $data->id_itsupport }} — {{ $data->namedevice ?: 'Tanpa Nama Device' }}</h3>
            </div>
            @php
                $stColor = match(true) {
                    str_contains($st, 'close') || str_contains($st, 'selesai') => 'bg-emerald-100 text-emerald-700',
                    str_contains($st, 'progress') || str_contains($st, 'proses') => 'bg-blue-100 text-blue-700',
                    str_contains($st, 'open') || str_contains($st, 'pending') => 'bg-amber-100 text-amber-700',
                    default => 'bg-slate-100 text-slate-600',
                };
            @endphp
            <span class="inline-flex items-center px-2.5 py-1 rounded-lg {{ $stColor }} text-[11px] font-bold">{{ $data->statusticket ?: '-' }}</span>
        </div>
        <div class="p-5">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-x-8 gap-y-3 {{ $fotoUrl ? 'lg:grid-cols-3' : '' }}">
                <div class="{{ $fotoUrl ? 'lg:col-span-2' : '' }}">
                    <dl class="grid grid-cols-1 md:grid-cols-2 gap-x-6 gap-y-3 mb-0">
                        <div><dt class="text-[11px] font-bold text-slate-500 uppercase tracking-wide mb-0.5">Tanggal</dt><dd class="text-[13px] text-slate-800 font-medium m-0">{{ $data->tanggal ? \Carbon\Carbon::parse($data->tanggal)->format('d/m/Y') : '-' }}</dd></div>
                        <div><dt class="text-[11px] font-bold text-slate-500 uppercase tracking-wide mb-0.5">Kategori Device</dt>
                            <dd class="m-0"><span class="inline-flex items-center px-2.5 py-1 rounded-lg bg-slate-100 text-slate-700 text-[11px] font-bold">{{ $data->katdevice ?: '-' }}</span></dd>
                        </div>
                        <div><dt class="text-[11px] font-bold text-slate-500 uppercase tracking-wide mb-0.5">Nama Device</dt><dd class="text-[13px] text-slate-800 font-semibold m-0">{{ $data->namedevice ?: '-' }}</dd></div>
                        <div><dt class="text-[11px] font-bold text-slate-500 uppercase tracking-wide mb-0.5">Owner / Pemilik</dt><dd class="text-[13px] text-slate-800 font-medium m-0">{{ $data->owner ?: '-' }}</dd></div>
                        <div class="md:col-span-2"><dt class="text-[11px] font-bold text-slate-500 uppercase tracking-wide mb-0.5">Lokasi Device</dt><dd class="text-[13px] text-slate-800 font-medium m-0">{{ $data->lokasidevice ?: '-' }}</dd></div>
                        <div><dt class="text-[11px] font-bold text-slate-500 uppercase tracking-wide mb-0.5">Tingkat Gangguan</dt>
                            <dd class="m-0">
                                @php $tgColor = str_contains($tg, 'tinggi') ? 'bg-red-100 text-red-700' : (str_contains($tg, 'sedang') ? 'bg-amber-100 text-amber-700' : 'bg-emerald-100 text-emerald-700'); @endphp
                                <span class="inline-flex items-center px-2.5 py-1 rounded-lg {{ $tgColor }} text-[11px] font-bold">{{ $data->tingkatgangguan ?: '-' }}</span>
                            </dd>
                        </div>
                        <div><dt class="text-[11px] font-bold text-slate-500 uppercase tracking-wide mb-0.5">Petugas</dt><dd class="text-[13px] text-slate-800 font-medium m-0">{{ $data->petugas_nama ?: '—' }}</dd></div>
                        <div><dt class="text-[11px] font-bold text-slate-500 uppercase tracking-wide mb-0.5">Polda</dt><dd class="text-[13px] font-bold m-0" style="color:#D8A808;">{{ $data->petugas_daerah ? strtoupper($data->petugas_daerah) : '—' }}</dd></div>
                        <div><dt class="text-[11px] font-bold text-slate-500 uppercase tracking-wide mb-0.5">Polres</dt><dd class="text-[13px] text-slate-800 font-medium m-0">{{ $data->petugas_wilayah ? strtoupper($data->petugas_wilayah) : '—' }}</dd></div>
                        <div class="md:col-span-2"><dt class="text-[11px] font-bold text-slate-500 uppercase tracking-wide mb-0.5">Keterangan</dt><dd class="text-[13px] text-slate-800 font-medium m-0">{{ $data->ketsingkat ?: '-' }}</dd></div>
                    </dl>
                </div>
                @if($fotoUrl)
                <div>
                    <p class="text-[11px] font-bold text-slate-500 uppercase tracking-wide mb-2">Foto Kondisi</p>
                    <a href="{{ $fotoUrl }}" target="_blank" rel="noopener noreferrer">
                        <img src="{{ $fotoUrl }}" alt="Foto kondisi" class="rounded-xl border border-slate-200 w-full" style="max-height:220px;object-fit:cover;">
                    </a>
                </div>
                @endif
            </div>
            <div class="flex items-center justify-between pt-4 mt-4 border-t border-slate-100 flex-wrap gap-2">
                <a href="{{ route('itsupport.index') }}" class="flex items-center gap-2 px-4 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-600 font-bold rounded-xl text-label-md transition-all text-decoration-none">
                    <span class="material-symbols-outlined text-[18px]">arrow_back</span><span>Kembali</span>
                </a>
                <div class="flex items-center gap-2">
                    @include('_partials.show_export_dropdown', ['exportRouteName' => 'itsupport.export', 'rowId' => $data->id_itsupport])
                @if($canMutate)
                    <a href="{{ route('itsupport.edit', $data->id_itsupport) }}" class="flex items-center gap-2 px-4 py-2.5 bg-amber-50 hover:bg-amber-100 text-amber-700 border border-amber-200 font-bold rounded-xl text-label-md transition-all text-decoration-none">
                        <span class="material-symbols-outlined text-[18px]">edit</span><span>Edit</span>
                    </a>
                    <form action="{{ route('itsupport.destroy', $data->id_itsupport) }}" method="POST" class="d-inline m-0" onsubmit="return confirm('Yakin hapus tiket ini?');">
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
