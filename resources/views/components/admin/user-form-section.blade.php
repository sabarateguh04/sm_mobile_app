@props([
    'icon' => 'fa-circle',
    'title' => '',
    'subtitle' => null,
    'subtitleHtml' => null,
    'variant' => 'primary',
])

@php
    $materialIcon = match ($icon) {
        'fa-map-marker-alt' => 'location_on',
        'fa-user' => 'person',
        'fa-key' => 'key',
        'fa-address-card' => 'badge',
        'fa-landmark' => 'account_balance',
        'fa-shield-alt', 'fa-shield' => 'shield',
        default => 'folder',
    };
    $headerClass = match ($variant) {
        'success' => 'bg-secondary/10 border-secondary/20',
        'info' => 'bg-sky-50 border-sky-100',
        'secondary' => 'bg-slate-50 border-slate-100',
        'warning' => 'bg-amber-50 border-amber-100',
        'purple' => 'bg-violet-50 border-violet-100',
        default => 'bg-primary/5 border-primary/15',
    };
    $iconClass = match ($variant) {
        'success' => 'text-secondary',
        'info' => 'text-sky-600',
        'secondary' => 'text-slate-500',
        'warning' => 'text-amber-600',
        'purple' => 'text-violet-600',
        default => 'text-primary',
    };
    $hasSubtitle = filled($subtitle) || filled($subtitleHtml);
@endphp

<div {{ $attributes->merge(['class' => 'mb-2 user-form-section rounded-lg border border-slate-200/60 overflow-visible']) }}>
    <div class="px-3 py-1.5 border-b {{ $headerClass }} flex {{ $hasSubtitle ? 'items-start' : 'items-center' }} gap-1.5">
        <span class="material-symbols-outlined {{ $iconClass }} text-[16px] shrink-0 {{ $hasSubtitle ? 'mt-0.5' : '' }}" style="font-variation-settings: 'FILL' 1;">{{ $materialIcon }}</span>
        <div class="min-w-0 flex-1">
            <h3 class="text-[12px] font-bold text-slate-800 uppercase tracking-wide m-0 leading-tight">{{ $title }}</h3>
            @if($subtitleHtml)
                <p class="text-[11px] text-slate-500 m-0 mt-0.5 normal-case font-normal leading-snug">{!! $subtitleHtml !!}</p>
            @elseif($subtitle)
                <p class="text-[11px] text-slate-500 m-0 mt-0.5 normal-case font-normal leading-snug">{{ $subtitle }}</p>
            @endif
        </div>
    </div>
    <div class="px-3 py-2">
        {{ $slot }}
    </div>
</div>

@once
    @push('styles')
    <style>
        .user-form-page {
            display: flex;
            flex-direction: column;
            gap: 0;
        }
        .user-form-page .user-form-section .row.form-row-compact,
        .user-form-page .user-form-section .row.g-1,
        .user-form-page .user-form-section .row.g-2 {
            --bs-gutter-x: 0.5rem;
            --bs-gutter-y: 0.25rem;
            margin-bottom: 0;
        }
        .user-form-page .user-form-section [class*="col-"] {
            margin-bottom: 0;
        }
        .user-form-section .form-label {
            font-size: 0.7rem;
            font-weight: 600;
            margin-bottom: 0.1rem;
            color: #475569;
            line-height: 1.2;
        }
        .user-form-section .form-control,
        .user-form-section .form-select {
            font-size: 0.78rem;
            padding: 0.28rem 0.45rem;
            min-height: 30px;
            border-radius: 0.375rem;
            border-color: #e2e8f0;
        }
        .user-form-section .form-control:focus,
        .user-form-section .form-select:focus {
            border-color: #0041c8;
            box-shadow: 0 0 0 2px rgba(0, 65, 200, 0.1);
        }
        .user-form-section .form-select:not([multiple]) {
            max-height: none;
        }
        .user-form-page .sbo-compact-select-native select {
            cursor: pointer;
        }
        .user-form-section .invalid-feedback,
        .user-form-section .form-hint {
            font-size: 0.68rem;
            margin-top: 0.1rem;
            color: #64748b;
        }
        .user-form-section .pm-step-badge {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-width: 1rem;
            height: 1rem;
            padding: 0 0.2rem;
            border-radius: 0.25rem;
            font-size: 0.6rem;
            font-weight: 700;
            line-height: 1;
            margin-right: 0.2rem;
            vertical-align: middle;
            color: #fff;
        }
        .user-form-section .pm-step-badge--1 { background: #15803d; }
        .user-form-section .pm-step-badge--2 { background: #0284c7; }
        .user-form-section .pm-step-badge--3 { background: #d97706; }
        .user-form-section .pm-step-badge--4 { background: #0041c8; }
        .user-form-page .select2-container--bootstrap-5 {
            width: 100% !important;
            margin: 0 !important;
        }
        .user-form-page .select2-container--bootstrap-5 .select2-selection {
            min-height: 30px !important;
            font-size: 0.78rem;
            padding: 0.1rem 0.4rem;
            border-color: #e2e8f0 !important;
            border-radius: 0.375rem !important;
        }
        .user-form-page .select2-container--bootstrap-5 .select2-selection--single .select2-selection__rendered {
            line-height: 26px;
            padding-left: 0;
            color: #0f172a;
        }
        .user-form-page .select2-container--bootstrap-5 .select2-selection--single .select2-selection__placeholder {
            color: #94a3b8;
        }
        .user-form-page .select2-container--bootstrap-5.select2-container--focus .select2-selection,
        .user-form-page .select2-container--bootstrap-5.select2-container--open .select2-selection {
            border-color: #0041c8 !important;
            box-shadow: 0 0 0 2px rgba(0, 65, 200, 0.1) !important;
        }
        .user-form-page .form-actions-sticky {
            position: sticky;
            bottom: 0;
            z-index: 10;
            background: linear-gradient(180deg, rgba(255,255,255,0) 0%, #fff 24%);
            padding-top: 0.75rem;
            margin-top: 0.25rem;
        }
    </style>
    @endpush
@endonce
