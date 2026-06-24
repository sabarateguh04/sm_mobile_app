@extends('layouts.app')

@section('title', 'Wilayah - Arus Mudik & Balik')
@section('page-title', 'Wilayah - Arus Mudik & Balik')

@section('content')
<div class="row mb-3">
    <div class="col-md-12">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <span class="badge bg-info">Level: Wilayah</span>
            </div>
            <div class="d-flex gap-2">
                <form action="{{ route('wil.arusmudik.index') }}" method="GET" class="d-flex gap-2">
                    <input type="text" name="search" class="form-control" placeholder="Cari..." value="{{ request('search') }}">
                    <button type="submit" class="btn btn-outline-secondary">
                        <i class="fas fa-search"></i> Cari
                    </button>
                    @if(request('search'))
                        <a href="{{ route('wil.arusmudik.index') }}" class="btn btn-outline-danger">
                            <i class="fas fa-times"></i> Reset
                        </a>
                    @endif
                </form>
                <div class="btn-group">
                    <button type="button" class="btn btn-success dropdown-toggle" data-bs-toggle="dropdown">
                        <i class="fas fa-download"></i> Export
                    </button>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item" href="{{ route('wil.arusmudik.export', 'pdf') }}">PDF</a></li>
                        <li><a class="dropdown-item" href="{{ route('wil.arusmudik.export', 'excel') }}">Excel</a></li>
                        <li><a class="dropdown-item" href="{{ route('wil.arusmudik.export', 'csv') }}">CSV</a></li>
                        <li><a class="dropdown-item" href="{{ route('wil.arusmudik.export', 'word') }}">Word</a></li>
                        <li><a class="dropdown-item" href="{{ route('wil.arusmudik.export', 'json') }}">JSON</a></li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header bg-info text-white">
                <h5 class="mb-0"><i class="fa fa-route"></i> Laporan Posko : Arus Mudik & Balik (Wilayah)</h5>
            </div>
            <div class="card-body">
                @if($data->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>ID</th>
                                    <th>Polres</th>
                                    <th>Profile Posko</th>
                                    <th>ID Profile Posko (Arusmudik)</th>
                                    <th>Tanggal</th>
                                    <th>Piket</th>
                                    <th>Nama Piket</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($data as $item)
                                    <tr>
                                        <td>{{ $loop->iteration + ($data->currentPage() - 1) * $data->perPage() }}</td>
                                        <td>{{ $item->id_arusmudik ?? '-' }}</td>
                                        <td><span class="badge bg-secondary">{{ $item->wilayah ?? '-' }}</span></td>
                                        <td>{{ $item->id_profileposko ?? '-' }}</td>
                                        <td>{{ $item->arusmudik_id_profileposko ?? '-' }}</td>
                                        <td>{{ isset($item->tanggal) ? \Carbon\Carbon::parse($item->tanggal)->locale('id')->translatedFormat('d F Y') : '-' }}</td>
                                        <td>{{ $item->piket ?? '-' }}</td>
                                        <td>{{ $item->usm_nama ?? '-' }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    
                    <div class="d-flex justify-content-between align-items-center mt-3">
                        <div>
                            <p class="text-muted mb-0">
                                Menampilkan {{ $data->firstItem() }} sampai {{ $data->lastItem() }} dari {{ $data->total() }} data
                            </p>
                        </div>
                        <div>
                            {{ $data->links() }}
                        </div>
                    </div>
                @else
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i> Tidak ada data ditemukan untuk wilayah Anda.
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
