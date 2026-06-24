@extends('layouts.app')
@section('title', $pageTitle ?? 'Detail')
@section('page-title', $pageTitle ?? 'Detail')

@section('content')
<div class="max-w-[900px] mx-auto w-full space-y-4">

<section>
    <a href="{{ route('sm.master.index', ['table' => $table]) }}" class="inline-flex items-center gap-1 text-[11px] font-semibold text-slate-400 hover:text-primary text-decoration-none mb-1">
        <span class="material-symbols-outlined text-[14px]">arrow_back</span> Kembali
    </a>
    <h2 class="text-[22px] font-extrabold text-on-surface tracking-tight leading-tight m-0">{{ $meta['label'] ?? 'Detail' }}</h2>
</section>

<section class="bg-white rounded-xl border border-slate-200/60 shadow-sm overflow-hidden p-4">
    <dl class="grid grid-cols-[140px_1fr] md:grid-cols-[160px_1fr] gap-x-4 gap-y-3 mb-0 items-center">
        @foreach($fields as $field)
            <dt class="text-[11px] font-bold text-slate-500 uppercase tracking-wide self-start mt-0.5">{{ $field['label'] }}</dt>
            <dd class="text-[13px] text-slate-800 font-medium m-0 break-words whitespace-pre-wrap">{{ $field['value'] }}</dd>
        @endforeach
    </dl>
</section>

@if(!empty($coordinates['lat']) && !empty($coordinates['lng']))
<section class="bg-white rounded-xl border border-slate-200/60 overflow-hidden shadow-sm p-4">
    <div id="smMasterMap" class="rounded-lg overflow-hidden border border-slate-200" style="height:280px;"></div>
</section>
@endif

</div>
@endsection

@if(!empty($coordinates['lat']) && !empty($coordinates['lng']))
@push('scripts')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"/>
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    var lat = {{ $coordinates['lat'] }};
    var lng = {{ $coordinates['lng'] }};
    var map = L.map('smMasterMap').setView([lat, lng], 15);
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { maxZoom: 19 }).addTo(map);
    L.marker([lat, lng]).addTo(map);
});
</script>
@endpush
@endif
