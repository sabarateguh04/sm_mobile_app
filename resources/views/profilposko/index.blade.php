@extends('layouts.app')

@section('title', 'Profile Posko')
@section('page-title', 'Profile Posko')

@section('content')
<div class="row mb-4">
    <div class="col-md-12">
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3">
            <div>
                <a href="{{ route('profilposko.create') }}" class="btn btn-primary shadow-sm">
                    <i class="fas fa-plus me-2"></i> Tambah Data
                </a>
            </div>
            <div class="d-flex flex-wrap gap-2 w-100 w-md-auto">
                <form action="{{ route('profilposko.index') }}" method="GET" class="d-flex gap-2 flex-grow-1" style="max-width: 400px;">
                    <div class="input-group">
                        <span class="input-group-text bg-white border-end-0"><i class="fas fa-search text-muted"></i></span>
                        <input type="text" name="search" class="form-control border-start-0" placeholder="Cari..." value="{{ request('search') }}" style="border-radius: 0 10px 10px 0;">
                    </div>
                    @if(request('search'))
                        <a href="{{ route('profilposko.index') }}" class="btn btn-outline-danger"><i class="fas fa-times"></i></a>
                    @endif
                </form>
                <div class="btn-group shadow-sm">
                    <button type="button" class="btn btn-success dropdown-toggle" data-bs-toggle="dropdown" style="border-radius: 10px;">
                        <i class="fas fa-download me-2"></i> Export
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li><a class="dropdown-item" href="{{ route('profilposko.export', 'pdf') }}"><i class="fas fa-file-pdf text-danger me-2"></i> PDF</a></li>
                        <li><a class="dropdown-item" href="{{ route('profilposko.export', 'excel') }}"><i class="fas fa-file-excel text-success me-2"></i> Excel</a></li>
                        <li><a class="dropdown-item" href="{{ route('profilposko.export', 'csv') }}"><i class="fas fa-file-csv text-info me-2"></i> CSV</a></li>
                        <li><a class="dropdown-item" href="{{ route('profilposko.export', 'word') }}"><i class="fas fa-file-word text-primary me-2"></i> Word</a></li>
                        <li><a class="dropdown-item" href="{{ route('profilposko.export', 'json') }}"><i class="fas fa-file-code text-warning me-2"></i> JSON</a></li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show">
        <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

<div class="row">
    <div class="col-md-12">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-transparent border-bottom">
                <h5 class="mb-0"><i class="fa fa-building text-info me-2"></i> Daftar Profile Posko</h5>
            </div>
            <div class="card-body">
                @if($data->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Nama Posko</th>
                                    <th>Kategori Ops</th>
                                    <th>Polres</th>
@if(\App\Support\TerritoryIndexFilters::canFilterByDaerah())
                                    <th>Polda</th>
@endif
                                    <th>Piket</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($data as $item)
                                    <tr>
                                        <td>{{ $loop->iteration + ($data->currentPage() - 1) * $data->perPage() }}</td>
                                        <td>{{ $item->namaposko ?? '-' }}</td>
                                        <td>{{ $item->katops ?? '-' }}</td>
                                        <td>{{ $item->wilayah ?? '-' }}</td>
                                        <td>{{ $item->daerah ?? '-' }}</td>
                                        <td>{{ $item->usm_nama ?? '-' }}</td>
                                        <td>
                                            <div class="btn-group btn-group-sm">
                                                <a href="{{ route('profilposko.show', $item->id_profilposko) }}" class="btn btn-info" title="Detail"><i class="fas fa-eye"></i></a>
                                                <a href="{{ route('profilposko.edit', $item->id_profilposko) }}" class="btn btn-warning" title="Edit"><i class="fas fa-edit"></i></a>
                                                <form action="{{ route('profilposko.destroy', $item->id_profilposko) }}" method="POST" class="d-inline" onsubmit="return confirm('Yakin hapus?');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-danger" title="Hapus"><i class="fas fa-trash"></i></button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <div class="d-flex justify-content-between align-items-center mt-3">
                        <p class="text-muted mb-0">Menampilkan {{ $data->firstItem() }} - {{ $data->lastItem() }} dari {{ $data->total() }}</p>
                        {{ $data->links() }}
                    </div>
                @else
                    <div class="alert alert-info"><i class="fas fa-info-circle"></i> Tidak ada data.</div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
