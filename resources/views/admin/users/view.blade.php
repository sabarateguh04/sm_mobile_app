@extends('layouts.app')

@section('title', 'Detail User')
@section('page-title', 'Detail User')

@section('content')
<div class="row">
    <div class="col-md-10 offset-md-1">
        <div class="card shadow-sm">
            <div class="card-header bg-info text-white d-flex justify-content-between align-items-center">
                <h5 class="mb-0"><i class="fa fa-user"></i> Detail User</h5>
                <div>
                    @if(empty($executiveUserManagementReadOnly))
                        <a href="{{ route('admin.users.edit', $user->id_usm) }}" class="btn btn-light btn-sm"><i class="fa fa-edit"></i> Edit</a>
                    @endif
                    <a href="{{ route('admin.users.index') }}" class="btn btn-light btn-sm"><i class="fa fa-arrow-left"></i> Kembali</a>
                </div>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-3 text-center mb-3">
                        @if($user->fotoprof && \Illuminate\Support\Facades\Storage::disk('public')->exists($user->fotoprof))
                            <img src="{{ asset('storage/'.$user->fotoprof) }}" alt="avatar" class="rounded-circle shadow" style="width:160px;height:160px;object-fit:cover;">
                        @else
                            <div class="rounded-circle bg-secondary text-white d-inline-flex align-items-center justify-content-center shadow" style="width:160px;height:160px;font-size:60px;">
                                {{ strtoupper(substr($user->nama ?? $user->username ?? '?', 0, 1)) }}
                            </div>
                        @endif
                        <div class="mt-2">
                            @php $st = strtolower((string) ($user->account_status ?? '')); @endphp
                            @if($st === 'active')
                                <span class="badge bg-success">Active</span>
                            @elseif($st === 'pending')
                                <span class="badge bg-warning text-dark">Pending</span>
                            @elseif($st === 'blocked')
                                <span class="badge bg-danger">Blocked</span>
                            @else
                                <span class="badge bg-secondary">{{ $user->account_status ?: '-' }}</span>
                            @endif
                        </div>
                    </div>
                    <div class="col-md-9">
                        <table class="table table-borderless">
                            <tbody>
                                <tr><th>Nama</th><td>{{ $user->nama ?: '-' }}</td></tr>
                                <tr><th>NRP / NIP / NIK</th><td>{{ $user->nrp_nip_nik ?: '—' }}</td></tr>
                                <tr><th>Kategori user</th>
                                    <td>
                                        @if($user->kategori_user === 'organik')
                                            <span class="badge bg-primary">Organik</span>
                                        @elseif($user->kategori_user === 'non_organik')
                                            <span class="badge bg-secondary">Non-organik</span>
                                        @else
                                            <span class="text-muted">—</span>
                                        @endif
                                    </td>
                                </tr>
                                <tr><th>Instansi</th><td>{{ $user->induk_pemerintahan ?: '—' }}</td></tr>
                                <tr><th>Organisasi</th><td>{{ $user->pemerintahan_daerah ?: '—' }}</td></tr>
                                @if(!empty($user->organisasi_daerah))
                                    <tr><th>Sub organisasi</th><td>{{ $user->organisasi_daerah }}</td></tr>
                                @endif
                                <tr><th>Unit kerja</th><td>{{ $user->unit_kerja ?: '—' }}</td></tr>
                                <tr><th>Username</th><td>{{ $user->username }}</td></tr>
                                <tr><th>Email</th><td>{{ $user->email }}</td></tr>
                                <tr><th>Role</th>
                                    <td>
                                        @if($role)
                                            <span class="badge bg-primary fs-6">{{ $role->role_name }}</span>
                                            <span class="text-muted small ms-1">(ID {{ $role->role_id }})</span>
                                        @else
                                            <span class="badge bg-secondary">Tidak ada di master</span>
                                            @if($user->user_role_id)
                                                <span class="text-warning small d-block mt-1"><code>user_role_id = {{ $user->user_role_id }}</code> — periksa tabel <code>roles</code>.</span>
                                            @else
                                                <span class="text-muted small d-block mt-1">Role belum dipilih untuk user ini.</span>
                                            @endif
                                        @endif
                                        @if($role && ($roleHelp[$role->role_id] ?? null))
                                            <p class="small text-muted mb-0 mt-2">{{ $roleHelp[$role->role_id] }}</p>
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    @if(\App\Support\TerritoryIndexFilters::canFilterByDaerah())
                                        <th>Polda</th>
                                    @endif
                                    <td>
                                        {{ $user->daerah ?: '—' }}
                                        @if($user->daerah && ! $user->daerah_in_master)
                                            <span class="badge bg-warning text-dark ms-1">Tidak ada di master Polda</span>
                                        @elseif($user->daerah && $user->daerah_in_master)
                                            <span class="badge bg-success bg-opacity-75 ms-1">Sesuai master</span>
                                        @endif
                                    </td>
                                </tr>
                                <tr><th>Polres</th>
                                    <td>
                                        {{ $user->wilayah ?: '—' }}
                                        @if($user->wilayah && ! $user->wilayah_in_master)
                                            <span class="badge bg-warning text-dark ms-1">Tidak cocok master Polres</span>
                                        @elseif($user->wilayah && $user->wilayah_in_master)
                                            <span class="badge bg-success bg-opacity-75 ms-1">Sesuai master</span>
                                        @endif
                                    </td>
                                </tr>
                                <tr><th>No. Telp</th><td>{{ $user->notelp !== null && $user->notelp !== '' ? $user->notelp : '-' }}</td></tr>
                                <tr><th>Email Status</th><td>{{ data_get($user, 'email_status') ?: '-' }}</td></tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
