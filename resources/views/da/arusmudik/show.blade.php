@extends('layouts.app')

@section('title', 'Detail - Daerah Arus Mudik & Balik')
@section('page-title', 'Detail - Daerah Arus Mudik & Balik')

@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header bg-warning text-white">
                <h5 class="mb-0"><i class="fas fa-eye"></i> Detail Report Ops : Arus Mudik & Balik (Daerah)</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <table class="table table-bordered">
                            <tr>
                                <th width="40%">ID</th>
                                <td>{{ $data->id_da_arusmudik ?? $data->id ?? '-' }}</td>
                            </tr>
                            <tr>

                                <th>Tanggal</th>
                                <td>{{ isset($data->tanggal) ? \Carbon\Carbon::parse($data->tanggal)->format('d/m/Y') : '-' }}</td>
                            </tr>
@if(\App\Support\TerritoryIndexFilters::canFilterByDaerah())
                                <th>Polda</th>
@endif
                                <td><span class="badge bg-warning">{{ $data->daerah ?? '-' }}</span></td>
                            </tr>
                            <tr>
                                <th>Polres</th>
                                <td><span class="badge bg-secondary">{{ $data->wilayah ?? '-' }}</span></td>
                            </tr>




                            <tr>
                                <th>Status Arus</th>
                                <td>
                                    @if(isset($data->statarus) && $data->statarus)
                                        <span class="badge bg-info">{{ $data->statarus }}</span>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                            </tr>
                        </table>
                    </div>
                    <div class="col-md-6">
                        <table class="table table-bordered">
                            <tr>
                                <th width="40%">Created At</th>
                                <td>{{ isset($data->created_at) ? \Carbon\Carbon::parse($data->created_at)->format('d/m/Y H:i:s') : '-' }}</td>
                            </tr>
                            <tr>
                                <th>Updated At</th>
                                <td>{{ isset($data->updated_at) ? \Carbon\Carbon::parse($data->updated_at)->format('d/m/Y H:i:s') : '-' }}</td>
                            </tr>
                        </table>
                    </div>
                </div>
                
                <div class="mt-3 d-flex flex-wrap gap-2 align-items-center">
                    <a href="{{ route('da.arusmudik.index') }}" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Kembali
                    </a>
                    @include('_partials.show_export_dropdown', [
                        'exportRouteName' => 'da.arusmudik.export',
                        'rowId' => $data->id_da_arusmudik ?? $data->id,
                    ])
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
