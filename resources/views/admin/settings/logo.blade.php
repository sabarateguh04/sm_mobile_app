@extends('layouts.app')

@section('title', 'Pengaturan Logo Aplikasi')
@section('header_title', 'Pengaturan Aplikasi')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-6">
        <div class="card shadow-sm border-0 rounded-4 mt-4">
            <div class="card-header bg-white border-0 pt-4 pb-0 px-4">
                <h5 class="fw-bold text-dark mb-0"><i class="fas fa-image text-primary me-2"></i> Ganti Logo Aplikasi</h5>
            </div>
            <div class="card-body p-4">
                @if(session('success'))
                    <div class="alert alert-success alert-dismissible fade show rounded-3" role="alert">
                        <i class="fas fa-check-circle me-1"></i> {{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif

                <form action="{{ route('admin.settings.logo.update') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    
                    <div class="mb-4 text-center">
                        <label class="form-label d-block text-muted small fw-semibold">Logo Saat Ini</label>
                        <div class="p-3 bg-light rounded-3 d-inline-block border" style="width: 150px; height: 150px;">
                            @if(file_exists(public_path('images/logo-korlantas.png')))
                                <img src="{{ asset('images/logo-korlantas.png') }}?v={{ time() }}" alt="Logo Aplikasi" class="img-fluid" style="max-height: 100%; object-fit: contain;">
                            @else
                                <i class="fas fa-image text-muted" style="font-size: 3rem; line-height: 120px;"></i>
                            @endif
                        </div>
                    </div>

                    <div class="mb-4">
                        <label for="logo" class="form-label fw-semibold">Upload Logo Baru</label>
                        <input class="form-control @error('logo') is-invalid @enderror" type="file" id="logo" name="logo" accept="image/png, image/jpeg, image/svg+xml">
                        <div class="form-text text-muted">Format: PNG, JPG, JPEG, SVG. Maks: 2MB. Logo idealnya berlatar transparan (PNG/SVG).</div>
                        @error('logo')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="d-grid">
                        <button type="submit" class="btn btn-primary fw-semibold rounded-3 py-2">
                            <i class="fas fa-save me-2"></i> Simpan Logo Baru
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
