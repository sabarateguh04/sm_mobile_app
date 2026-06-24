@extends('layouts.app')

@section('title', 'Daerah - Arus Mudik & Balik')
@section('page-title', 'Daerah - Arus Mudik & Balik')

@section('content')
<div class="row mb-3">
    <div class="col-md-12">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <span class="badge bg-warning">Level: Daerah</span>
            </div>
            <div class="d-flex gap-2">
                <form action="{{ route('da.arusmudik.index') }}" method="GET" class="d-flex gap-2">
                    <input type="text" name="search" class="form-control" placeholder="Cari..." value="{{ request('search') }}">
                    <button type="submit" class="btn btn-outline-secondary">
                        <i class="fas fa-search"></i> Cari
                    </button>
                    @if(request('search'))
                        <a href="{{ route('da.arusmudik.index') }}" class="btn btn-outline-danger">
                            <i class="fas fa-times"></i> Reset
                        </a>
                    @endif
                </form>
                <div class="btn-group">
                    <button type="button" class="btn btn-success dropdown-toggle" data-bs-toggle="dropdown">
                        <i class="fas fa-download"></i> Export
                    </button>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item" href="{{ route('da.arusmudik.export', 'pdf') }}">PDF</a></li>
                        <li><a class="dropdown-item" href="{{ route('da.arusmudik.export', 'excel') }}">Excel</a></li>
                        <li><a class="dropdown-item" href="{{ route('da.arusmudik.export', 'csv') }}">CSV</a></li>
                        <li><a class="dropdown-item" href="{{ route('da.arusmudik.export', 'word') }}">Word</a></li>
                        <li><a class="dropdown-item" href="{{ route('da.arusmudik.export', 'json') }}">JSON</a></li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header bg-warning text-white">
                <h5 class="mb-0"><i class="fa fa-route"></i> Report Ops : Arus Mudik & Balik (Daerah)</h5>
            </div>
            <div class="card-body">
                @if($data->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>ID</th>
@if(\App\Support\TerritoryIndexFilters::canFilterByDaerah())
                                    <th>Polda</th>
@endif
                                    <th>Polres</th>
                                    <th>Kat Ops</th>
                                    <th>Jenis Ops</th>
                                    <th>Tanggal</th>
                                    <th>Nama Posko</th>
                                    <th>ID Profile Posko (Arusmudik)</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($data as $item)
                                    <tr>
                                        <td>{{ $loop->iteration + ($data->currentPage() - 1) * $data->perPage() }}</td>
                                        <td>{{ $item->id_arusmudik ?? '-' }}</td>
                                        <td><span class="badge bg-warning">{{ $item->daerah ?? '-' }}</span></td>
                                        <td><span class="badge bg-secondary">{{ $item->wilayah ?? '-' }}</span></td>
                                        <td>{{ $item->katops ?? '-' }}</td>
                                        <td>{{ $item->jenisops ?? '-' }}</td>
                                        <td>{{ isset($item->tanggal) ? \Carbon\Carbon::parse($item->tanggal)->locale('id')->translatedFormat('d F Y') : '-' }}</td>
                                        <td>{{ $item->namaposko ?? '-' }}</td>
                                        <td>{{ $item->arusmudik_id_profileposko ?? '-' }}</td>
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
                        <i class="fas fa-info-circle"></i> Tidak ada data ditemukan untuk daerah Anda.
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
