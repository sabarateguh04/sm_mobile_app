@extends('layouts.app')
@section('title', $pageTitle ?? 'Data Koleksi')
@section('page-title', $pageTitle ?? 'Data Koleksi')

@section('content')
@php
    $hasFilters = request()->hasAny(['search', 'tanggal_dari', 'tanggal_sampai', 'daerah', 'wilayah', 'source_server']);
@endphp
<div class="max-w-[1400px] mx-auto w-full space-y-4">

<section>
    <h2 class="text-[22px] font-extrabold text-on-surface tracking-tight leading-tight m-0">
        {{ $meta['label'] ?? $pageTitle ?? 'Data Koleksi' }}
    </h2>
    <p class="text-[11px] text-slate-400 mt-0.5 mb-0">
        Menampilkan 50 data terbaru dari tabel <code class="bg-slate-100 px-1 rounded text-[10px]">{{ $active ?? '—' }}</code>
    </p>
</section>

@if(!$available)
    <div class="rounded-xl bg-slate-50 border border-slate-200 px-4 py-3 text-slate-600 text-[12px]">
        Tabel <code>sm_dacol_*</code> belum tersedia di koneksi database kedua.
    </div>
@else
<div class="space-y-4">
    {{-- Pilihan Tabel Dacol (Button di atas pencarian) --}}
    <div class="flex flex-wrap gap-2 items-center">
        @foreach($tables as $table => $info)
            <a href="{{ route('sm.dacol.index', ['table' => $table]) }}"
               class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full border text-[11px] font-semibold text-decoration-none transition-colors {{ $active === $table ? 'bg-primary border-primary text-white shadow-sm' : 'bg-white border-slate-200/80 text-slate-600 hover:border-slate-300 hover:bg-slate-50' }}">
                <span>{{ $info['label'] }}</span>
                <span class="px-1.5 py-0.5 rounded-full text-[9px] font-bold tracking-tight {{ $active === $table ? 'bg-white/20 text-white' : 'bg-slate-100 text-slate-500' }}">
                    {{ number_format($info['count']) }}
                </span>
            </a>
        @endforeach
    </div>

    {{-- Konten utama --}}
    <div class="space-y-3">
        @if($active && $meta)
        @include('sm.dacol._table')
        @endif
    </div>
</div>
@endif
</div>
@endsection
