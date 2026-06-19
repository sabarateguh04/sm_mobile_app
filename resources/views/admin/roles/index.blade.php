@extends('layouts.app')

@section('title', 'Roles')
@section('page-title', 'Roles')

@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="card shadow-sm">
            <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                <h5 class="mb-0"><i class="fa fa-shield"></i> Roles Management</h5>
                <span class="badge bg-light text-primary">Total: {{ count($roles) }} role</span>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped table-hover align-middle">
                        <thead class="table-light">
                            <tr>
                                <th style="width:80px;">ID</th>
                                <th style="width:220px;">Role</th>
                                <th>Privilege</th>
                                <th class="text-center" style="width:160px;">Jumlah User</th>
                                <th class="text-center" style="width:180px;">Jumlah Permission</th>
                                <th class="text-end" style="width:180px;">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php $roleDescriptions = $roleDescriptions ?? config('role_menu_matrix.role_descriptions', []); @endphp
                            @forelse($roles as $role)
                                <tr>
                                    <td><span class="badge bg-secondary">{{ $role->role_id }}</span></td>
                                    <td><strong>{{ $role->role_name }}</strong></td>
                                    <td class="small text-muted">{{ $roleDescriptions[$role->role_id] ?? '—' }}</td>
                                    <td class="text-center">
                                        <span class="badge bg-info">{{ $role->user_count ?? 0 }}</span>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge bg-success">{{ $role->permission_count ?? 0 }}</span>
                                    </td>
                                    <td class="text-end">
                                        <a href="{{ route('admin.role_permissions.index', ['role_id' => $role->role_id]) }}" class="btn btn-sm btn-outline-primary" title="Lihat Permissions">
                                            <i class="fa fa-key"></i> Permissions
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center text-muted py-4">Tidak ada data roles</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
