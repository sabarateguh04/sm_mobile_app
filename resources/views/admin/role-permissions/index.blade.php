@extends('layouts.app')

@section('title', 'Role Permissions')
@section('page-title', 'Matriks izin per modul')

@push('styles')
<style>
    .rp-shell {
        max-width: 100%;
    }
    .rp-toolbar {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        justify-content: space-between;
        gap: 1rem;
        margin-bottom: 1.25rem;
        padding: 1rem 1.15rem;
        background: linear-gradient(180deg, #f8fafc 0%, #f1f5f9 100%);
        border: 1px solid #e2e8f0;
        border-radius: 0.75rem;
    }
    .rp-toolbar .input-group {
        max-width: 22rem;
        min-width: 12rem;
    }
    .rp-toolbar .input-group-text {
        background: #fff;
        border-color: #e2e8f0;
        color: #64748b;
    }
    .rp-toolbar .form-control {
        border-color: #e2e8f0;
        font-size: 0.925rem;
    }
    .rp-legend {
        font-size: 0.8125rem;
        color: #64748b;
        max-width: 42rem;
        line-height: 1.5;
    }
    .rp-legend code {
        font-size: 0.78rem;
        background: #f1f5f9;
        padding: 0.1rem 0.35rem;
        border-radius: 0.25rem;
        color: #475569;
    }
    .rp-card-main {
        border: none;
        border-radius: 0.85rem;
        overflow: hidden;
        box-shadow: 0 4px 6px -1px rgba(15, 23, 42, 0.06), 0 10px 24px -12px rgba(102, 126, 234, 0.2);
    }
    .rp-card-main .card-header {
        border-bottom: none;
        padding: 1.1rem 1.35rem;
    }
    .rp-card-main .card-body {
        padding: 1.25rem 1.35rem 1.5rem;
        background: #fafbfc;
    }
    .rp-stat {
        display: inline-flex;
        align-items: center;
        gap: 0.35rem;
        padding: 0.4rem 0.75rem;
        border-radius: 2rem;
        font-size: 0.8125rem;
        font-weight: 600;
        background: rgba(255, 255, 255, 0.2);
    }
    .rp-accordion {
        --rp-acc-radius: 0.7rem;
    }
    .rp-accordion .accordion-item {
        border: 1px solid #e2e8f0;
        border-radius: var(--rp-acc-radius) !important;
        margin-bottom: 0.65rem;
        overflow: hidden;
        background: #fff;
        box-shadow: 0 1px 3px rgba(15, 23, 42, 0.04);
    }
    .rp-accordion .accordion-button {
        padding: 0.85rem 1.1rem;
        font-size: 0.95rem;
        font-weight: 600;
        color: #1e293b;
        background: #fff;
        box-shadow: none;
    }
    .rp-accordion .accordion-button:not(.collapsed) {
        background: linear-gradient(135deg, rgba(102, 126, 234, 0.1) 0%, rgba(118, 75, 162, 0.08) 100%);
        color: #3730a3;
    }
    .rp-accordion .accordion-button:focus {
        box-shadow: none;
        border-color: rgba(102, 126, 234, 0.35);
    }
    .rp-accordion .accordion-button::after {
        filter: opacity(0.55);
    }
    .rp-accordion .accordion-body {
        padding: 1rem 1.1rem 1.15rem;
        background: #f8fafc;
        border-top: 1px solid #eef2f7;
    }
    .rp-mod-icon {
        width: 2rem;
        height: 2rem;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 0.5rem;
        background: linear-gradient(135deg, rgba(102, 126, 234, 0.15) 0%, rgba(118, 75, 162, 0.12) 100%);
        color: #5b4fce;
        font-size: 0.9rem;
    }
    .rp-page-card {
        border: 1px solid #e2e8f0;
        border-radius: 0.65rem;
        overflow: hidden;
        background: #fff;
        transition: box-shadow 0.15s ease;
    }
    .rp-page-card:hover {
        box-shadow: 0 4px 14px rgba(15, 23, 42, 0.06);
    }
    .rp-page-card .card-header {
        background: #fff;
        border-bottom: 1px dashed #e2e8f0;
        padding: 0.65rem 0.9rem;
    }
    .rp-page-card .card-header code {
        font-size: 0.85rem;
        background: #f1f5f9;
        padding: 0.2rem 0.45rem;
        border-radius: 0.35rem;
        color: #0f172a;
    }
    .rp-matrix-wrap {
        overflow-x: auto;
        -webkit-overflow-scrolling: touch;
        border-radius: 0 0 0.5rem 0.5rem;
    }
    .rp-matrix {
        position: relative;
    }
    .rp-matrix table {
        font-size: 0.78rem;
        min-width: 640px;
        margin-bottom: 0;
        border-collapse: separate;
        border-spacing: 0;
    }
    .rp-matrix thead th {
        position: sticky;
        top: 0;
        z-index: 2;
        background: #f1f5f9 !important;
        font-weight: 600;
        color: #475569;
        border-bottom: 2px solid #e2e8f0 !important;
        padding: 0.55rem 0.4rem;
        vertical-align: middle;
        white-space: nowrap;
    }
    .rp-matrix thead th:first-child {
        position: sticky;
        left: 0;
        z-index: 3;
        min-width: 108px;
        max-width: 140px;
        background: #eef2ff !important;
        border-right: 2px solid #e2e8f0;
        text-align: left;
        padding-left: 0.75rem;
    }
    .rp-matrix tbody td:first-child {
        position: sticky;
        left: 0;
        z-index: 1;
        background: #fafbff;
        border-right: 2px solid #e2e8f0;
        padding-left: 0.75rem;
    }
    .rp-matrix tbody tr:hover td {
        background-color: rgba(102, 126, 234, 0.04);
    }
    .rp-matrix tbody tr:hover td:first-child {
        background: #f0f4ff !important;
    }
    .rp-matrix td {
        border-color: #eef2f7 !important;
        padding: 0.45rem 0.35rem;
        vertical-align: middle;
    }
    .rp-matrix thead th.role-col {
        max-width: 4.75rem;
        min-width: 3.25rem;
        line-height: 1.2;
    }
    .rp-matrix thead th.role-col .rp-rn {
        display: block;
        font-size: 0.68rem;
        font-weight: 700;
        color: #334155;
        max-height: 2.4em;
        overflow: hidden;
        text-overflow: ellipsis;
    }
    .rp-matrix thead th.role-col .rp-rid {
        font-size: 0.62rem;
        font-weight: 500;
        color: #94a3b8;
    }
    .rp-matrix .form-check-input {
        width: 2.1rem;
        height: 1.1rem;
        margin: 0;
        cursor: pointer;
        border-color: #cbd5e1;
    }
    .rp-matrix .form-check-input:checked {
        background-color: #667eea;
        border-color: #667eea;
    }
    .rp-matrix .form-check-input:focus {
        box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
    }
    .rp-matrix .badge {
        font-size: 0.7rem;
        font-weight: 600;
        padding: 0.28em 0.5em;
        letter-spacing: 0.02em;
    }
    .rp-empty-page {
        padding: 1.5rem;
        text-align: center;
        color: #94a3b8;
        font-size: 0.875rem;
        background: #fafbfc;
    }
    @media (max-width: 576px) {
        .rp-toolbar {
            flex-direction: column;
            align-items: stretch;
        }
        .rp-toolbar .input-group {
            max-width: none;
        }
    }
</style>
@endpush

@section('content')
<div class="row rp-shell">
    <div class="col-12">
        <div class="card rp-card-main shadow-sm">
            <div class="card-header bg-primary text-white d-flex flex-wrap justify-content-between align-items-start gap-3">
                <div>
                    <h5 class="mb-1 fw-semibold"><i class="fa fa-shield-halved me-2 opacity-90"></i>Hak akses per modul</h5>
                    <p class="mb-0 small opacity-90">Matriks <strong>aksi × role</strong> — aktifkan izin per sel (superadmin).</p>
                </div>
                <div class="d-flex flex-wrap gap-2 align-items-center">
                    <span class="rp-stat text-white">
                        <i class="fa fa-database opacity-75"></i> {{ $totalPermissions }} izin
                    </span>
                    @if($canEdit)
                        <span class="badge bg-success rounded-pill px-3 py-2">Mode edit aktif</span>
                    @else
                        <span class="badge bg-light text-dark rounded-pill px-3 py-2">Hanya baca</span>
                    @endif
                </div>
            </div>
            <div class="card-body">
                <div class="rp-toolbar">
                    <p class="rp-legend mb-0">
                        Grup modul dari <code>page_name</code>. Middleware memakai <code>page_name/action_name</code> (mis. <code>usm/list</code>).
                    </p>
                    <div class="d-flex flex-wrap gap-2 align-items-center">
                        <div class="input-group input-group-sm">
                            <span class="input-group-text"><i class="fa fa-search"></i></span>
                            <input type="search" class="form-control" id="rpFilterModul" placeholder="Cari modul atau halaman…" autocomplete="off" aria-label="Filter">
                        </div>
                        <div class="btn-group btn-group-sm" role="group" aria-label="Accordion">
                            <button type="button" class="btn btn-outline-secondary" id="rpExpandAll" title="Buka semua modul">
                                <i class="fa fa-folder-open me-1"></i>Buka semua
                            </button>
                            <button type="button" class="btn btn-outline-secondary" id="rpCollapseAll" title="Tutup semua">
                                <i class="fa fa-folder me-1"></i>Tutup semua
                            </button>
                        </div>
                    </div>
                </div>

                @if(count($moduleBuckets) === 0)
                    <div class="alert alert-warning border-0 shadow-sm mb-0 rounded-3">
                        <i class="fa fa-triangle-exclamation me-2"></i>
                        Belum ada data di <code>role_permissions</code>. Jalankan seed atau minta superadmin mengisi matriks.
                    </div>
                @else
                    <div class="accordion rp-accordion" id="accordionModul">
                        @foreach($moduleBuckets as $moduleId => $bucket)
                            @php
                                $accId = 'mod-'.preg_replace('/[^a-z0-9]+/i', '-', $moduleId);
                            @endphp
                            <div class="accordion-item rp-module-block"
                                 data-rp-label="{{ strtolower($bucket['label']) }}"
                                 data-rp-module-id="{{ $moduleId }}">
                                <h2 class="accordion-header" id="h-{{ $accId }}">
                                    <button class="accordion-button collapsed" type="button"
                                            data-bs-toggle="collapse" data-bs-target="#c-{{ $accId }}"
                                            aria-expanded="false" aria-controls="c-{{ $accId }}">
                                        <span class="rp-mod-icon me-2"><i class="fa fa-layer-group"></i></span>
                                        <span class="flex-grow-1 text-start">{{ $bucket['label'] }}</span>
                                        <span class="badge bg-secondary rounded-pill ms-2">{{ count($bucket['pages']) }} hal.</span>
                                    </button>
                                </h2>
                                <div id="c-{{ $accId }}" class="accordion-collapse collapse"
                                     aria-labelledby="h-{{ $accId }}">
                                    <div class="accordion-body">
                                        @foreach($bucket['pages'] as $page)
                                            <div class="card rp-page-card mb-3 rp-page-block"
                                                 data-rp-label="{{ strtolower($page.' '.$bucket['label']) }}">
                                                <div class="card-header py-2 px-3">
                                                    <code>{{ $page }}</code>
                                                    <span class="text-muted small ms-1">halaman / controller</span>
                                                </div>
                                                @php
                                                    $pageRows = $actionsPerPage[$page] ?? [];
                                                    $visibleRows = collect($pageRows)->filter(function ($action) use ($canEdit, $matrix, $page) {
                                                        return $canEdit || isset($matrix[$page][$action]);
                                                    });
                                                @endphp
                                                @if($visibleRows->isEmpty())
                                                    <div class="rp-empty-page mb-0 rounded-bottom">
                                                        Tidak ada aksi untuk halaman ini.
                                                    </div>
                                                @else
                                                    <div class="card-body p-0">
                                                        <div class="rp-matrix-wrap">
                                                            <div class="rp-matrix">
                                                                <table class="table mb-0">
                                                                    <thead>
                                                                        <tr>
                                                                            <th scope="col">Aksi</th>
                                                                            @foreach($roles as $r)
                                                                                <th class="text-center role-col" scope="col" title="{{ $r->role_name }} (ID {{ $r->role_id }})">
                                                                                    <span class="rp-rn">{{ $r->role_name }}</span>
                                                                                    <span class="rp-rid d-block">#{{ $r->role_id }}</span>
                                                                                </th>
                                                                            @endforeach
                                                                        </tr>
                                                                    </thead>
                                                                    <tbody>
                                                                        @foreach($visibleRows as $action)
                                                                            <tr>
                                                                                <td>
                                                                                    @php
                                                                                        $color = match(strtolower($action)) {
                                                                                            'list', 'view' => 'info',
                                                                                            'add' => 'success',
                                                                                            'edit', 'editfield' => 'warning',
                                                                                            'delete' => 'danger',
                                                                                            'import_data', 'export_data' => 'secondary',
                                                                                            default => 'dark',
                                                                                        };
                                                                                    @endphp
                                                                                    <span class="badge bg-{{ $color }}">{{ $action }}</span>
                                                                                </td>
                                                                                @foreach($roles as $r)
                                                                                    @php
                                                                                        $on = isset($matrix[$page][$action][$r->role_id]);
                                                                                    @endphp
                                                                                    <td class="text-center">
                                                                                        @if($canEdit)
                                                                                            <div class="form-check form-switch d-flex justify-content-center my-1">
                                                                                                <input
                                                                                                    type="checkbox"
                                                                                                    class="form-check-input rp-toggle"
                                                                                                    role="switch"
                                                                                                    data-role-id="{{ $r->role_id }}"
                                                                                                    data-page-name="{{ $page }}"
                                                                                                    data-action-name="{{ $action }}"
                                                                                                    @checked($on)
                                                                                                    aria-label="{{ $page }} {{ $action }} role {{ $r->role_id }}"
                                                                                                >
                                                                                            </div>
                                                                                        @else
                                                                                            @if($on)
                                                                                                <span class="text-success" title="Diizinkan"><i class="fa fa-circle-check"></i></span>
                                                                                            @else
                                                                                                <span class="text-muted user-select-none">·</span>
                                                                                            @endif
                                                                                        @endif
                                                                                    </td>
                                                                                @endforeach
                                                                            </tr>
                                                                        @endforeach
                                                                    </tbody>
                                                                </table>
                                                            </div>
                                                        </div>
                                                    </div>
                                                @endif
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@push('scripts')
<script>
(function () {
    var inp = document.getElementById('rpFilterModul');
    if (inp) {
        inp.addEventListener('input', function () {
            var q = (inp.value || '').toLowerCase().trim();
            document.querySelectorAll('.rp-module-block').forEach(function (mod) {
                var modLab = (mod.getAttribute('data-rp-label') || '');
                var showMod = false;
                mod.querySelectorAll('.rp-page-block').forEach(function (page) {
                    var lab = (page.getAttribute('data-rp-label') || '');
                    var hit = !q || lab.indexOf(q) !== -1 || modLab.indexOf(q) !== -1;
                    page.style.display = hit ? '' : 'none';
                    if (hit) showMod = true;
                });
                mod.style.display = showMod ? '' : 'none';
            });
        });
    }
    var acc = document.getElementById('accordionModul');
    document.getElementById('rpExpandAll')?.addEventListener('click', function () {
        if (!acc || typeof bootstrap === 'undefined') return;
        acc.querySelectorAll('.accordion-collapse').forEach(function (el) {
            try {
                bootstrap.Collapse.getOrCreateInstance(el, { toggle: false }).show();
            } catch (e) {}
        });
    });
    document.getElementById('rpCollapseAll')?.addEventListener('click', function () {
        if (!acc || typeof bootstrap === 'undefined') return;
        acc.querySelectorAll('.accordion-collapse.show').forEach(function (el) {
            try {
                bootstrap.Collapse.getOrCreateInstance(el).hide();
            } catch (e) {}
        });
    });
})();

@if($canEdit)
(function () {
    var token = document.querySelector('meta[name="csrf-token"]');
    token = token ? token.getAttribute('content') : '';

    function postToggle(el, enabled) {
        var payload = {
            role_id: parseInt(el.getAttribute('data-role-id'), 10),
            page_name: el.getAttribute('data-page-name'),
            action_name: el.getAttribute('data-action-name'),
            enabled: enabled
        };
        el.disabled = true;
        fetch(@json(route('admin.role_permissions.toggle')), {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': token,
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify(payload)
        })
            .then(function (r) { return r.json().then(function (j) { return { ok: r.ok, json: j }; }); })
            .then(function (res) {
                if (!res.ok || !res.json || !res.json.ok) {
                    el.checked = !enabled;
                    alert((res.json && res.json.message) ? res.json.message : 'Gagal menyimpan.');
                }
            })
            .catch(function () {
                el.checked = !enabled;
                alert('Kesalahan jaringan.');
            })
            .finally(function () {
                el.disabled = false;
            });
    }

    document.querySelectorAll('.rp-toggle').forEach(function (el) {
        el.addEventListener('change', function () {
            postToggle(el, el.checked);
        });
    });
})();
@endif
</script>
@endpush
@endsection
