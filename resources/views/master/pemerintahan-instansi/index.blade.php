@extends('layouts.app')

@section('title', 'Master Data Pemerintahan — Instansi')
@section('page-title', 'Master Data Pemerintahan (Instansi & Pemda)')

@section('content')
@if(!empty($tableMissing))
<div class="alert alert-warning py-2 small mb-3">
    <i class="fa fa-database"></i> Tabel belum ada — jalankan migrasi: <code>php artisan migrate</code>, lalu isi awal: <code>php artisan db:seed --class=MasterPemerintahanUnitKerjaSeeder</code>.
</div>
@else
<div class="alert alert-info py-2 small mb-3">
    <i class="fa fa-info-circle"></i> Data dibaca dari tabel <code>master_pemerintahan_instansi</code>. Baris awal diisi oleh seeder <code>MasterPemerintahanUnitKerjaSeeder</code>.
</div>
@endif
<div class="card shadow-sm">
    <div class="card-header bg-teal text-white d-flex justify-content-between align-items-center flex-wrap gap-2" style="background-color:#0f766e !important;">
        <h6 class="mb-0"><i class="fa fa-landmark"></i> Daftar instansi/pemda pemerintahan</h6>
        <span class="badge bg-light text-dark">{{ $rows->total() }} baris</span>
    </div>
    <div class="card-body">
        <form method="GET" class="mb-3">
            <div class="input-group" style="max-width:440px;">
                <input type="text" name="search" class="form-control" placeholder="Cari jenis / nama / lokasi..." value="{{ request('search') }}">
                <button type="submit" class="btn btn-outline-success"><i class="fa fa-search"></i></button>
                @if(request()->filled('search'))
                    <a href="{{ route('master.pemerintahan-instansi.index') }}" class="btn btn-outline-secondary"><i class="fa fa-times"></i></a>
                @endif
            </div>
        </form>
        <div class="table-responsive">
            <table class="table table-striped table-hover align-middle">
                <thead class="table-light">
                    <tr>
                        <th>Jenis</th>
                        <th>Nama lengkap unit</th>
                        <th>Singkatan</th>
                        <th>Lokasi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($rows as $r)
                        <tr>
                            <td><span class="badge bg-info text-dark">{{ $r->jenis }}</span></td>
                            <td><strong>{{ $r->nama_unit }}</strong></td>
                            <td class="font-monospace">{{ $r->singkatan }}</td>
                            <td>{{ $r->lokasi }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="text-center text-muted py-4">Tidak ada data yang cocok</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @include('_partials.simple_pagination', ['paginator' => $rows, 'unit' => 'baris'])
    </div>
</div>
@endsection
