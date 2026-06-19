@extends('layouts.app')

@section('title', 'Master Data Unit Kerja')
@section('page-title', 'Master Data Unit Kerja')

@section('content')
@if(!empty($tableMissing))
<div class="alert alert-warning py-2 small mb-3">
    <i class="fa fa-database"></i> Tabel belum ada — jalankan migrasi: <code>php artisan migrate</code>, lalu isi awal: <code>php artisan db:seed --class=MasterPemerintahanUnitKerjaSeeder</code>.
</div>
@else
<div class="alert alert-info py-2 small mb-3">
    <i class="fa fa-info-circle"></i> Data dibaca dari tabel <code>master_unit_kerja</code>. Baris awal diisi oleh seeder <code>MasterPemerintahanUnitKerjaSeeder</code>.
</div>
@endif
<div class="card shadow-sm">
    <div class="card-header bg-secondary text-white d-flex justify-content-between align-items-center flex-wrap gap-2">
        <h6 class="mb-0"><i class="fa fa-sitemap"></i> Rekaman Unit Kerja</h6>
        <span class="badge bg-light text-secondary">{{ $rows->total() }} baris</span>
    </div>
    <div class="card-body">
        <form method="GET" class="mb-3">
            <div class="input-group" style="max-width:440px;">
                <input type="text" name="search" class="form-control" placeholder="Cari kode / nama unit / penanggung jawab..." value="{{ request('search') }}">
                <button type="submit" class="btn btn-outline-secondary"><i class="fa fa-search"></i></button>
                @if(request()->filled('search'))
                    <a href="{{ route('master.unit-kerja.index') }}" class="btn btn-outline-danger border-secondary"><i class="fa fa-times"></i></a>
                @endif
            </div>
        </form>
        <div class="table-responsive">
            <table class="table table-striped table-hover align-middle">
                <thead class="table-light">
                    <tr>
                        <th>Kode Unit Kerja</th>
                        <th>Nama Unit Kerja</th>
                        <th>Menempati instansi (placeholder)</th>
                        <th>Penanggung jawab</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($rows as $r)
                        <tr>
                            <td class="font-monospace small">{{ $r->kode }}</td>
                            <td><strong>{{ $r->nama_unit_kerja }}</strong></td>
                            <td class="text-muted">{{ $r->instansi_placeholder }}</td>
                            <td>{{ $r->penanggung_jawab }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="text-center text-muted py-4">Tidak ada data yang cocok</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @include('_partials.simple_pagination', ['paginator' => $rows, 'unit' => 'unit kerja'])
    </div>
</div>
@endsection
