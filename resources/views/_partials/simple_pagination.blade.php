@push('styles')
<style>
    .pagination-wrapper .pagination { margin: 0; gap: 4px; }
    .pagination-wrapper .pagination .page-link {
        border-radius: 8px; border: 1px solid #e2e8f0; color: #1e293b;
        padding: 6px 12px; font-size: 13px; font-weight: 500; min-width: 36px; text-align: center;
    }
    .pagination-wrapper .pagination .page-link:hover { background: #f1f5f9; border-color: #cbd5e1; color: #667eea; }
    .pagination-wrapper .pagination .page-item.active .page-link {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        border-color: transparent; color: #fff; box-shadow: 0 2px 6px rgba(102,126,234,0.35);
    }
    .pagination-wrapper .pagination .page-item.disabled .page-link {
        background: #f8fafc; color: #94a3b8; border-color: #e2e8f0;
    }
</style>
@endpush

<div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mt-3 gap-2 pagination-wrapper">
    <div class="text-muted small">
        Menampilkan
        <strong>{{ $paginator->firstItem() ?? 0 }}</strong>–<strong>{{ $paginator->lastItem() ?? 0 }}</strong>
        dari <strong>{{ $paginator->total() }}</strong> {{ $unit ?? 'data' }}
    </div>
    <nav>
        {{ $paginator->withQueryString()->onEachSide(1)->links() }}
    </nav>
</div>
