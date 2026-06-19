@extends('layouts.app')

@section('title', 'Master KL Pusat')
@section('page-title', 'Master KL Pusat')

@section('content')
<div class="row g-3">
    <div class="col-12">
        <div class="card shadow-sm">
            <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center flex-wrap gap-2">
                <h6 class="mb-0"><i class="fa fa-sitemap"></i> Hierarki KL Pusat</h6>
                <div class="d-flex gap-2">
                    <span class="badge bg-light text-primary">{{ $rows->total() }} baris</span>
                    <a href="{{ route('master.kl-pusat.create') }}" class="btn btn-sm btn-light"><i class="fa fa-plus"></i> Tambah</a>
                </div>
            </div>
            <div class="card-body">
                @if(session('success'))
                    <div class="alert alert-success py-2">{{ session('success') }}</div>
                @endif
                <form method="GET" class="mb-3">
                    <div class="input-group" style="max-width:520px;">
                        <input type="text" name="search" class="form-control" placeholder="Cari nama..." value="{{ request('search') }}">
                        <button class="btn btn-outline-primary" type="submit"><i class="fa fa-search"></i></button>
                    </div>
                </form>
                <div class="table-responsive">
                    <table class="table table-striped table-hover align-middle">
                        <thead class="table-light">
                            <tr>
                                <th style="width:90px;">ID</th>
                                <th>Induk</th>
                                <th>Nama</th>
                                <th style="width:200px;" class="text-end">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($rows as $r)
                                <tr>
                                    <td class="text-muted small">{{ $r->id }}</td>
                                    <td class="small">{{ $r->parent_nama ?: '—' }}</td>
                                    <td><strong>{{ $r->nama }}</strong></td>
                                    <td class="text-end">
                                        <a href="{{ route('master.kl-pusat.edit', $r->id) }}" class="btn btn-sm btn-outline-primary"><i class="fa fa-edit"></i></a>
                                        <form action="{{ route('master.kl-pusat.destroy', $r->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Hapus baris ini?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-danger"><i class="fa fa-trash"></i></button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="4" class="text-center text-muted py-4">Belum ada data</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                @include('_partials.simple_pagination', ['paginator' => $rows, 'unit' => 'baris'])
            </div>
        </div>
    </div>
</div>
@endsection
