@extends('layouts.app')
@section('title', $pageTitle)
@section('page-title', $pageTitle)

@section('content')
<div class="card border-0 shadow-sm">
    <div class="card-header bg-info text-white"><h5 class="mb-0">Tambah Pos Pelayanan</h5></div>
    <div class="card-body">
        <form action="{{ route('layanan.public_service.store') }}" method="POST" enctype="multipart/form-data">@csrf
            <div class="row g-3">
                <div class="col-md-4"><x-sbo-date-input /></div>
                <div class="col-md-4"><label class="form-label">Tahun Ops <span class="text-danger">*</span></label><input type="text" name="tahunops" class="form-control @error('tahunops') is-invalid @enderror" value="{{ old('tahunops') }}" required>@error('tahunops')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
                <div class="col-md-4"><label class="form-label">Jenis Ops</label><input type="text" name="jenisops" class="form-control" value="{{ old('jenisops') }}"></div>
                <div class="col-md-6"><label class="form-label">Name Ops</label><input type="text" name="nameops" class="form-control" value="{{ old('nameops') }}"></div>
                <div class="col-md-6"><label class="form-label">Sasaran Ops <span class="text-danger">*</span></label><input type="text" name="sasaranops" class="form-control @error('sasaranops') is-invalid @enderror" value="{{ old('sasaranops') }}" required>@error('sasaranops')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
                <div class="col-md-6"><label class="form-label">Nama Posyan <span class="text-danger">*</span></label><input type="text" name="namaposyan" class="form-control @error('namaposyan') is-invalid @enderror" value="{{ old('namaposyan') }}" required>@error('namaposyan')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
                <div class="col-md-6"><label class="form-label">Lokasi Posyan <span class="text-danger">*</span></label><input type="text" name="lokasiposyan" class="form-control @error('lokasiposyan') is-invalid @enderror" value="{{ old('lokasiposyan') }}" required>@error('lokasiposyan')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
                <div class="col-md-6"><label class="form-label">Koordinat</label><input type="text" name="koordinat" class="form-control" value="{{ old('koordinat') }}" placeholder="lat,lng"></div>
                <div class="col-12"><label class="form-label">Sarpras Posyan <span class="text-danger">*</span></label><input type="text" name="sarprasposyan" class="form-control @error('sarprasposyan') is-invalid @enderror" value="{{ old('sarprasposyan') }}" required>@error('sarprasposyan')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
                <div class="col-12">
                    <label class="form-label">Foto posyan <span class="text-danger">*</span></label>
                    <input type="file" name="fotoposyan_file" id="fotoposyan_file_posyan_create" class="form-control @error('fotoposyan_file') is-invalid @enderror" accept="image/*" capture="environment" required
                        data-sbo-media-preview data-sbo-foto-gps
                        data-sbo-preview-img="#fotoposyan_preview_posyan_create"
                        data-sbo-preview-wrap="#fotoposyan_preview_wrap_posyan_create"
                        data-sbo-max-mb="8">
                    <small class="text-muted d-block mt-1">Unggah dari galeri atau ambil foto (kamera di HP).</small>
                    @error('fotoposyan_file')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                    <div class="mt-2 d-none" id="fotoposyan_preview_wrap_posyan_create">
                        <span class="small text-muted d-block mb-1">Pratinjau</span>
                        <img id="fotoposyan_preview_posyan_create" src="" alt="Pratinjau foto" class="img-thumbnail d-none" style="max-height:200px">
                    </div>
                </div>
            </div>
            <div class="mt-4 d-flex justify-content-between">
                <a href="{{ route('layanan.public_service.index') }}" class="btn btn-secondary">Kembali</a>
                <button type="submit" class="btn btn-primary">Simpan</button>
            </div>
        </form>
    </div>
</div>
@endsection
