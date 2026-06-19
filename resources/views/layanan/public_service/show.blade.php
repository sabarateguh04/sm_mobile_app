@extends('layouts.app')
@section('title', $pageTitle)
@section('page-title', $pageTitle)

@section('content')
@php
    $__fp = $data->fotoposyan ?? '';
    $__showImg = $__fp !== '' && (str_starts_with($__fp, 'http') || str_starts_with($__fp, '/storage'));
@endphp
<div class="card border-0 shadow-sm">
    <div class="card-header bg-info text-white"><h5 class="mb-0">Detail Pos Pelayanan</h5></div>
    <div class="card-body">
        <table class="table table-bordered">
            <tr><th width="32%">Tanggal</th><td>{{ $data->tanggal ? \Carbon\Carbon::parse($data->tanggal)->format('d/m/Y') : '-' }}</td></tr>
            <tr><th>Tahun / Jenis Ops</th><td>{{ $data->tahunops ?? '-' }} / {{ $data->jenisops ?? '-' }}</td></tr>
            <tr><th>Name Ops</th><td>{{ $data->nameops ?? '-' }}</td></tr>
            <tr><th>Sasaran</th><td>{{ $data->sasaranops ?? '-' }}</td></tr>
            <tr><th>Nama Posyan</th><td>{{ $data->namaposyan ?? '-' }}</td></tr>
            <tr><th>Lokasi</th><td>{{ $data->lokasiposyan ?? '-' }}</td></tr>
            <tr><th>Koordinat</th><td>{{ $data->koordinat ?? '-' }}</td></tr>
            <tr><th>Sarpras</th><td>{{ $data->sarprasposyan ?? '-' }}</td></tr>
            <tr><th>Foto posyan</th><td>
                @if($__showImg)
                    <a href="{{ str_starts_with($__fp, 'http') ? $__fp : url($__fp) }}" target="_blank" rel="noopener noreferrer">
                        <img src="{{ str_starts_with($__fp, 'http') ? $__fp : url($__fp) }}" alt="Foto posyan" class="img-fluid rounded border" style="max-height:360px">
                    </a>
                @else
                    {{ $__fp !== '' ? $__fp : '-' }}
                @endif
            </td></tr>
            <tr><th>Piket</th><td>{{ $data->usm_nama ?? $data->piket ?? '-' }}</td></tr>
        </table>
        <div class="mt-3 d-flex flex-wrap gap-2 align-items-center">
            <a href="{{ route('layanan.public_service.index', ($readOnly ?? false) ? ['sb_grp' => 'public_service'] : []) }}" class="btn btn-secondary">Kembali</a>
            @include('_partials.show_export_dropdown', [
                'exportRouteName' => 'layanan.public_service.export',
                'rowId' => $data->id_posyan,
                'exportQuery' => ($readOnly ?? false) ? ['sb_grp' => 'public_service'] : [],
                'variant' => 'hero',
            ])
            @unless($readOnly ?? false)
                <a href="{{ route('layanan.public_service.edit', $data->id_posyan) }}" class="btn btn-warning">Edit</a>
                <form action="{{ route('layanan.public_service.destroy', $data->id_posyan) }}" method="POST" class="d-inline" onsubmit="return confirm('Yakin?');">@csrf @method('DELETE')<button type="submit" class="btn btn-danger">Hapus</button></form>
            @endunless
        </div>
    </div>
</div>
@endsection
