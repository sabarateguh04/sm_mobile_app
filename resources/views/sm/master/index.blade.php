@extends('layouts.app')
@section('title', $pageTitle ?? 'Master Referensi')
@section('page-title', $pageTitle ?? 'Master Referensi')

@section('content')
@php
    $hasSearch = request()->filled('search');
    $visibleCols = array_slice($listColumns ?? [], 0, 5);
@endphp
<div class="max-w-[1400px] mx-auto w-full space-y-4">

<section>
    <h2 class="text-[22px] font-extrabold text-on-surface tracking-tight leading-tight m-0">{{ $active && $meta ? $meta['label'] : ($pageTitle ?? 'Master Referensi') }}</h2>
</section>

@if(!$available)
    <div class="rounded-xl bg-slate-50 border border-slate-200 px-4 py-3 text-slate-600 text-[12px]">Data belum tersedia.</div>
@else
<div class="grid grid-cols-1 lg:grid-cols-[260px_1fr] gap-4 items-start">
    <aside class="bg-white rounded-xl border border-slate-200/60 shadow-sm overflow-hidden">
        <ul class="max-h-[640px] overflow-y-auto custom-scrollbar m-0 p-0 list-none">
            @foreach($tables as $table => $info)
                <li>
                    <a href="{{ route('sm.master.index', ['table' => $table]) }}"
                       class="flex items-center justify-between gap-2 px-4 py-2 text-decoration-none transition-colors {{ $active === $table ? 'bg-primary text-white' : 'text-slate-700 hover:bg-slate-50' }}">
                        <span class="text-[11px] font-semibold leading-snug">{{ $info['label'] }}</span>
                        <span class="text-[10px] font-bold {{ $active === $table ? 'text-white/80' : 'text-slate-400' }}">{{ number_format($info['count']) }}</span>
                    </a>
                </li>
            @endforeach
        </ul>
    </aside>

    <div class="space-y-3">
        @if($active && $meta)
        <form action="{{ route('sm.master.index') }}" method="GET" class="sbo-compact-filters bg-white rounded-lg p-2 border border-slate-200/60 shadow-sm">
            <input type="hidden" name="table" value="{{ $active }}">
            <div class="pmf-row">
                @include('_partials.compact_filters.search', ['placeholder' => 'Cari...'])
                @include('_partials.compact_filters.actions', [
                    'resetUrl' => route('sm.master.index', ['table' => $active]),
                    'active' => $hasSearch,
                ])
            </div>
        </form>

        <section class="bg-white rounded-xl border border-slate-200/60 overflow-hidden shadow-sm">
            @if($rows && $rows->count() > 0)
                <div class="overflow-x-auto custom-scrollbar">
                    <table class="w-full text-left min-w-[600px]">
                        <thead>
                            <tr class="bg-slate-50/50">
                                <th class="px-4 py-2 text-[10px] font-bold text-slate-400 uppercase tracking-widest border-b border-slate-100 w-10">#</th>
                                @foreach($visibleCols as $col)
                                    <th class="px-3 py-2 text-[10px] font-bold text-slate-400 uppercase tracking-widest border-b border-slate-100">{{ str_replace('_', ' ', $col) }}</th>
                                @endforeach
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @foreach($rows as $i => $row)
                                <tr class="hover:bg-slate-50/50">
                                    <td class="px-4 py-2 text-[11px] text-slate-400">{{ $rows->firstItem() + $i }}</td>
                                    @foreach($visibleCols as $col)
                                        <td class="px-3 py-2 text-[12px] text-slate-800">{{ $row->{$col} ?? '—' }}</td>
                                    @endforeach
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @if($rows->hasPages())
                    <div class="px-4 py-3 border-t border-slate-100">{{ $rows->links() }}</div>
                @endif
            @else
                <div class="py-10 text-center px-4">
                    <p class="text-label-md text-slate-500 font-medium m-0">Tidak ada data.</p>
                </div>
            @endif
        </section>
        @endif
    </div>
</div>
@endif
</div>
@endsection
