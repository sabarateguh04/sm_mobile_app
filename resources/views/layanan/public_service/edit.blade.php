@extends('layouts.app')
@section('title', $pageTitle)
@section('page-title', $pageTitle)

@section('content')
@php
    $__fp = $data->fotoposyan ?? '';
    $__fpImg = $__fp !== '' && (str_starts_with($__fp, 'http') || str_starts_with($__fp, '/storage'));
@endphp
<div class="card border-0 shadow-sm">
    <div class="card-header bg-info text-white"><h5 class="mb-0">Edit Pos Pelayanan</h5></div>
    <div class="card-body">
        <form action="{{ route('layanan.public_service.update', $data->id_posyan) }}" method="POST" enctype="multipart/form-data">@csrf @method('PUT')
            <div class="row g-3">
                <div class="col-md-4"><x-sbo-date-input :value="$data->tanggal" /></div>
                <div class="col-md-4"><label class="form-label">Tahun Ops <span class="text-danger">*</span></label><input type="text" name="tahunops" class="form-control" value="{{ old('tahunops', $data->tahunops) }}" required></div>
                <div class="col-md-4"><label class="form-label">Jenis Ops</label><input type="text" name="jenisops" class="form-control" value="{{ old('jenisops', $data->jenisops) }}"></div>
                <div class="col-md-6"><label class="form-label">Name Ops</label><input type="text" name="nameops" class="form-control" value="{{ old('nameops', $data->nameops) }}"></div>
                <div class="col-md-6"><label class="form-label">Sasaran Ops <span class="text-danger">*</span></label><input type="text" name="sasaranops" class="form-control" value="{{ old('sasaranops', $data->sasaranops) }}" required></div>
                <div class="col-md-6"><label class="form-label">Nama Posyan <span class="text-danger">*</span></label><input type="text" name="namaposyan" class="form-control" value="{{ old('namaposyan', $data->namaposyan) }}" required></div>
                <div class="col-md-6"><label class="form-label">Lokasi Posyan <span class="text-danger">*</span></label><input type="text" name="lokasiposyan" class="form-control" value="{{ old('lokasiposyan', $data->lokasiposyan) }}" required></div>
                <div class="col-md-6"><label class="form-label">Koordinat</label><input type="text" name="koordinat" class="form-control" value="{{ old('koordinat', $data->koordinat) }}"></div>
                <div class="col-12"><label class="form-label">Sarpras Posyan <span class="text-danger">*</span></label><input type="text" name="sarprasposyan" class="form-control" value="{{ old('sarprasposyan', $data->sarprasposyan) }}" required></div>
                <div class="col-12">
                    @if($__fpImg)
                        <div class="mb-2">
                            <span class="small text-muted d-block mb-1">Foto saat ini</span>
                            <a href="{{ str_starts_with($__fp, 'http') ? $__fp : url($__fp) }}" target="_blank" rel="noopener noreferrer">
                                <img src="{{ str_starts_with($__fp, 'http') ? $__fp : url($__fp) }}" alt="Foto posyan" class="img-thumbnail" style="max-height:160px">
                            </a>
                        </div>
                    @endif
                    <label class="form-label">Ganti foto posyan <span class="text-muted fw-normal">(opsional)</span></label>
                    <input type="file" name="fotoposyan_file" id="fotoposyan_file_posyan_edit" class="form-control @error('fotoposyan_file') is-invalid @enderror" accept="image/*" capture="environment"
                        data-sbo-media-preview data-sbo-foto-gps
                        data-sbo-preview-img="#fotoposyan_preview_posyan_edit"
                        data-sbo-preview-wrap="#fotoposyan_preview_wrap_posyan_edit"
                        data-sbo-max-mb="8">
                    <small class="text-muted d-block mt-1">Unggah foto baru untuk mengganti. Biarkan kosong untuk mempertahankan yang ada.</small>
                    @error('fotoposyan_file')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                    <div class="mt-2 d-none" id="fotoposyan_preview_wrap_posyan_edit">
                        <span class="small text-muted d-block mb-1">Pratinjau foto baru</span>
                        <img id="fotoposyan_preview_posyan_edit" src="" alt="Pratinjau" class="img-thumbnail d-none" style="max-height:200px">
                    </div>
                </div>
            </div>
            <div class="mt-4 d-flex justify-content-between">
                <a href="{{ route('layanan.public_service.index') }}" class="btn btn-secondary">Kembali</a>
                <button type="submit" class="btn btn-primary">Update</button>
            </div>
        </form>
    </div>
</div>
@endsection
