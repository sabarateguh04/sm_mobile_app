@extends('layouts.app')

@section('title', 'Ubah KL Pusat')
@section('page-title', 'Ubah KL Pusat')

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="card shadow-sm">
            <div class="card-header bg-primary text-white"><h6 class="mb-0"><i class="fa fa-edit"></i> ID {{ $row->id }}</h6></div>
            <div class="card-body">
                @if($errors->any())
                    <div class="alert alert-danger"><ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div>
                @endif
                <form method="POST" action="{{ route('master.kl-pusat.update', $row->id) }}">
                    @csrf
                    @method('PUT')
                    <div class="mb-3">
                        <label class="form-label">Nama <span class="text-danger">*</span></label>
                        <input type="text" name="nama" class="form-control" value="{{ old('nama', $row->nama) }}" required maxlength="255">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Induk (opsional)</label>
                        <select name="parent_id" class="form-select">
                            <option value="">— Akar —</option>
                            @foreach($parents as $p)
                                <option value="{{ $p->id }}" @selected(old('parent_id', $row->parent_id) == $p->id)>{{ $p->nama }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="d-flex justify-content-between">
                        <a href="{{ route('master.kl-pusat.index') }}" class="btn btn-outline-secondary">Kembali</a>
                        <button type="submit" class="btn btn-primary">Perbarui</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
