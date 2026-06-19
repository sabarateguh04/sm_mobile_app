@extends('layouts.app')

@section('title', 'Tambah User')
@section('page-title', 'Tambah User')

@section('content')
<div class="user-form-page">
    <nav aria-label="breadcrumb" class="mb-3">
        <ol class="breadcrumb mb-0 small">
            <li class="breadcrumb-item"><a href="{{ route('admin.users.index') }}">Manajemen User</a></li>
            <li class="breadcrumb-item active" aria-current="page">Tambah user baru</li>
        </ol>
    </nav>

    <div class="row justify-content-center">
        <div class="col-xl-10">
            @if($errors->any())
                <div class="alert alert-danger border-0 shadow-sm mb-4">
                    <strong><i class="fa fa-exclamation-circle me-1"></i> Periksa form:</strong>
                    <ul class="mb-0 mt-2 small">
                        @foreach($errors->all() as $err)<li>{{ $err }}</li>@endforeach
                    </ul>
                </div>
            @endif

            <form action="{{ route('admin.users.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                @include('admin.users._form', ['user' => null])

                <div class="form-actions-sticky d-flex flex-wrap justify-content-between gap-2 border-top pt-3">
                    <a href="{{ route('admin.users.index') }}" class="btn btn-outline-secondary">
                        <i class="fa fa-arrow-left me-1"></i> Batal
                    </a>
                    <button type="submit" class="btn btn-primary px-4">
                        <i class="fa fa-save me-1"></i> Simpan user
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
