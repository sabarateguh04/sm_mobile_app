<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Smart Management') | Korlantas Admin</title>

    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&family=Manrope:wght@600;700;800&display=swap" rel="stylesheet"/>
    <!-- Material Symbols -->
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet"/>

    <!-- Load Bootstrap temporarily for backwards compatibility with unmigrated pages -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css"/>

    <!-- Tailwind CSS (via CDN) -->
    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
    <script id="tailwind-config">
        tailwind.config = {
          darkMode: "class",
          theme: {
            extend: {
              "colors": {
                      "surface": "#f8fafc",
                      "surface-container-low": "#ffffff",
                      "on-tertiary-fixed-variant": "#653e00",
                      "surface-bright": "#ffffff",
                      "surface-dim": "#f1f5f9",
                      "surface-container": "#f1f5f9",
                      "primary-container": "#0041c8",
                      "on-primary": "#ffffff",
                      "on-secondary-fixed": "#002109",
                      "primary-fixed": "#dce1ff",
                      "inverse-primary": "#b6c4ff",
                      "surface-tint": "#004dea",
                      "secondary-fixed": "#6bff8f",
                      "on-tertiary-container": "#ffe3c6",
                      "outline": "#64748b",
                      "on-secondary-container": "#166534",
                      "on-error-container": "#991b1b",
                      "tertiary": "#714600",
                      "on-tertiary-fixed": "#2a1700",
                      "inverse-on-surface": "#f8fafc",
                      "secondary": "#15803d",
                      "on-secondary": "#ffffff",
                      "on-error": "#ffffff",
                      "tertiary-container": "#925c00",
                      "on-background": "#0f172a",
                      "on-primary-fixed-variant": "#0039b3",
                      "on-surface-variant": "#475569",
                      "error": "#dc2626",
                      "on-primary-container": "#ffffff",
                      "primary": "#0041c8",
                      "inverse-surface": "#1e293b",
                      "surface-variant": "#e2e8f0",
                      "on-surface": "#0f172a",
                      "error-container": "#fef2f2",
                      "secondary-container": "#f0fdf4",
                      "surface-container-high": "#f1f5f9",
                      "primary-fixed-dim": "#b6c4ff",
                      "outline-variant": "#cbd5e1",
                      "surface-container-highest": "#e2e8f0",
                      "tertiary-fixed-dim": "#ffb95f",
                      "on-tertiary": "#ffffff",
                      "secondary-fixed-dim": "#4ae176",
                      "background": "#f8fafc",
                      "on-secondary-fixed-variant": "#005321",
                      "on-primary-fixed": "#001551",
                      "surface-container-lowest": "#ffffff",
                      "tertiary-fixed": "#ffddb8"
              },
              "borderRadius": {
                      "DEFAULT": "0.375rem",
                      "lg": "0.5rem",
                      "xl": "0.75rem",
                      "2xl": "1rem",
                      "full": "9999px"
              },
              "spacing": {
                      "stack-sm": "8px",
                      "margin-mobile": "12px",
                      "base": "4px",
                      "gutter": "12px",
                      "container-max": "1280px",
                      "stack-md": "16px",
                      "margin-desktop": "24px",
                      "stack-lg": "24px"
              }
            }
          }
        }
    </script>
    <style type="text/tailwindcss">
        @layer utilities {
            .text-label-sm { font-size: 11px; line-height: 1.1; font-weight: 700; font-family: "Plus Jakarta Sans"; }
            .text-headline-md { font-size: 18px; line-height: 1.2; font-weight: 600; font-family: "Manrope"; }
            .text-body-md { font-size: 13px; line-height: 1.4; font-weight: 400; font-family: "Plus Jakarta Sans"; }
            .text-display-lg-mobile { font-size: 28px; line-height: 1.1; letter-spacing: -0.01em; font-weight: 800; font-family: "Manrope"; }
            .text-body-lg { font-size: 15px; line-height: 1.4; font-weight: 400; font-family: "Plus Jakarta Sans"; }
            .text-display-lg { font-size: 28px; line-height: 1.1; letter-spacing: -0.01em; font-weight: 800; font-family: "Manrope"; }
            .text-label-md { font-size: 12px; line-height: 1.2; letter-spacing: 0.01em; font-weight: 600; font-family: "Plus Jakarta Sans"; }
            .text-headline-lg { font-size: 22px; line-height: 1.1; font-weight: 700; font-family: "Manrope"; }
            .text-headline-lg-mobile { font-size: 20px; line-height: 1.1; font-weight: 700; font-family: "Manrope"; }
            
            .custom-scrollbar::-webkit-scrollbar { width: 4px; height: 4px; }
            .custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
            .custom-scrollbar::-webkit-scrollbar-thumb { background: theme('colors.outline-variant'); border-radius: 4px; }
            .active-tab { box-shadow: 0 4px 12px rgba(0, 65, 200, 0.15); }
            .stat-card { transition: transform 0.2s ease, box-shadow 0.2s ease; }
            .stat-card:hover { transform: translateY(-2px); box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.05); }
            .sidebar-collapsed { transform: translateX(-100%); }
            .main-expanded { margin-left: 0 !important; }
        }
    </style>

    <style>
        body { background-color: #f8fafc; font-family: 'Plus Jakarta Sans', sans-serif; }
        .material-symbols-outlined { font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 20; vertical-align: middle; }
        tr { transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1); }
        
        @media (max-width: 1024px) {
            .sidebar-collapsed { transform: translateX(-100%); }
            .main-expanded { margin-left: 0 !important; }
        }

        /* Global theme bridge (Bootstrap pages → Korlantas theme) */
        :root { --sbo-primary: #0041c8; }

        /* Buttons */
        .btn { border-radius: 0.75rem; font-weight: 700; font-size: 12px; }
        .btn-sm { border-radius: 0.6rem; font-size: 11px; }
        .btn:focus { box-shadow: 0 0 0 0.2rem rgba(0, 65, 200, 0.18) !important; }
        .btn-primary { background-color: var(--sbo-primary); border-color: var(--sbo-primary); }
        .btn-primary:hover { background-color: #0036a8; border-color: #0036a8; }
        .btn-outline-primary { color: var(--sbo-primary); border-color: rgba(0, 65, 200, 0.35); }
        .btn-outline-primary:hover { background-color: var(--sbo-primary); border-color: var(--sbo-primary); color: #fff; }
        /* Neutralize success/info/warning buttons to theme palette */
        .btn-success { background-color: var(--sbo-primary); border-color: var(--sbo-primary); color: #fff; }
        .btn-success:hover, .btn-success:active { background-color: #0036a8; border-color: #0036a8; color: #fff; }
        .btn-outline-success { color: var(--sbo-primary); border-color: rgba(0,65,200,0.35); }
        .btn-outline-success:hover { background-color: var(--sbo-primary); border-color: var(--sbo-primary); color: #fff; }
        .btn-info { background-color: #0ea5e9; border-color: #0ea5e9; color: #fff; border-radius: 0.6rem; }
        .btn-info:hover { background-color: #0284c7; border-color: #0284c7; color: #fff; }
        .btn-warning { background-color: #f59e0b; border-color: #f59e0b; color: #fff; border-radius: 0.6rem; }
        .btn-warning:hover { background-color: #d97706; border-color: #d97706; color: #fff; }
        .btn-danger { background-color: #ef4444; border-color: #ef4444; color: #fff; border-radius: 0.6rem; }
        .btn-danger:hover { background-color: #dc2626; border-color: #dc2626; color: #fff; }
        .btn-light { border-radius: 0.6rem; }
        .btn-secondary { border-radius: 0.75rem; }

        /* Cards */
        .card { background-color: #ffffff; border: 1px solid rgba(226, 232, 240, 0.75); border-radius: 1rem; box-shadow: 0 1px 2px rgba(15, 23, 42, 0.06); }
        .card-header { background: #f8fafc !important; border-bottom: 1px solid rgba(226, 232, 240, 0.75) !important; border-top-left-radius: 1rem !important; border-top-right-radius: 1rem !important; color: #0f172a !important; padding: 0.75rem 1rem !important; }
        .card-header h5, .card-header h4, .card-header h6 { color: #0f172a !important; font-size: 13px; font-weight: 700; margin-bottom: 0; }
        .card-header .badge { font-size: 10px; }
        .card-footer { background: transparent; border-top: 1px solid rgba(226, 232, 240, 0.75); }
        /* Neutralize colored badge backgrounds in headers */
        .card-header .badge.bg-light { background-color: rgba(0,65,200,0.08) !important; color: #0041c8 !important; }

        /* Tables */
        .table { margin-bottom: 0; }
        .table thead th { font-size: 10px; text-transform: uppercase; letter-spacing: 0.12em; color: #94a3b8; border-bottom-color: rgba(226, 232, 240, 0.75) !important; }
        .table td, .table th { vertical-align: middle; border-top-color: rgba(226, 232, 240, 0.65) !important; }
        .table-hover > tbody > tr:hover { background-color: rgba(248, 250, 252, 0.9); }
        .table-responsive { border-radius: 1rem; }

        /* Alerts */
        .alert { border-radius: 1rem; border-width: 1px; }
        .alert-info { background: rgba(0, 65, 200, 0.06); border-color: rgba(0, 65, 200, 0.18); color: #0f172a; }
        .alert-success { background: rgba(21, 128, 61, 0.08); border-color: rgba(21, 128, 61, 0.2); color: #166534; }
        .alert-warning { background: rgba(245, 158, 11, 0.10); border-color: rgba(245, 158, 11, 0.25); color: #7c2d12; }
        .alert-danger { background: rgba(220, 38, 38, 0.08); border-color: rgba(220, 38, 38, 0.2); color: #991b1b; }

        /* Forms */
        .form-control, .form-select { border-radius: 0.75rem; border-color: rgba(226, 232, 240, 0.95); background-color: #f8fafc; }
        .form-control:focus, .form-select:focus { border-color: var(--sbo-primary); box-shadow: none; background-color: #ffffff; }
        .input-group .input-group-text { border-radius: 0.75rem; }

        /* Dropdown */
        .dropdown-menu { border-radius: 0.9rem; border: 0; box-shadow: 0 16px 30px -20px rgba(15, 23, 42, 0.35); }

        /* Bootstrap Form Controls — Korlantas theme */
        .form-control, .form-select {
            border-radius: 0.75rem !important; font-size: 12px; border-color: #e2e8f0;
            background-color: #f8fafc; color: #0f172a; padding: 0.55rem 0.875rem;
            transition: border-color .15s, background-color .15s, box-shadow .15s;
        }
        .form-control:focus, .form-select:focus {
            border-color: #0041c8 !important; background-color: #fff;
            box-shadow: 0 0 0 3px rgba(0,65,200,0.1) !important; outline: none;
        }
        .form-control.is-invalid, .form-select.is-invalid { border-color: #ef4444; }
        .form-control-sm, .form-select-sm { border-radius: 0.6rem !important; font-size: 11px; }
        .form-label { font-size: 11px; font-weight: 700; color: #374151; margin-bottom: 0.3rem; }
        .form-text, .text-muted.form-text { font-size: 10px; }
        .invalid-feedback { font-size: 10px; }
        .input-group .form-control { border-radius: 0 !important; }
        .input-group .form-control:last-child { border-radius: 0 0.75rem 0.75rem 0 !important; }
        .input-group .input-group-text { border-radius: 0.75rem 0 0 0.75rem !important; background: #f8fafc; border-color: #e2e8f0; font-size: 12px; }
        .input-group .btn:last-child { border-radius: 0 0.75rem 0.75rem 0 !important; }
        /* Bootstrap Alerts */
        .alert { border-radius: 0.9rem; font-size: 12px; border: 0; }
        .alert-success { background: #f0fdf4; color: #166534; }
        .alert-info { background: rgba(0,65,200,0.06); color: #1e40af; }
        .alert-warning { background: #fffbeb; color: #92400e; }
        .alert-danger { background: #fef2f2; color: #991b1b; }
        /* Tables */
        .table { font-size: 12px; }
        .table th { font-weight: 700; color: #475569; font-size: 11px; }
        .table td { color: #1e293b; }
        .table-bordered { border-color: #e2e8f0; border-radius: 0.75rem; overflow: hidden; }
        .table-bordered td, .table-bordered th { border-color: #e2e8f0; }
        .table-hover>tbody>tr:hover>td { background-color: rgba(0,65,200,0.03); }

        /* Compact table filters (admin listing) */
        .sbo-compact-filters,
        .personil-manager-filters,
        .patrol-cctv-filters { overflow: hidden; --pmf-value: #475569; --pmf-placeholder: #94a3b8; }
        .sbo-compact-filters .pmf-row,
        .personil-manager-filters .pmf-row,
        .patrol-cctv-filters .pmf-row {
            display: flex !important; flex-direction: row !important; flex-wrap: nowrap !important;
            align-items: stretch; gap: 0.5rem; width: 100%; overflow: hidden;
        }
        .sbo-compact-filters .pmf-row > *,
        .personil-manager-filters .pmf-row > *,
        .patrol-cctv-filters .pmf-row > * { min-width: 0; }
        .sbo-compact-filters .pmf-search,
        .personil-manager-filters .pmf-search,
        .patrol-cctv-filters .pmf-search { flex: 1 1 0; }
        .sbo-compact-filters .pmf-territory,
        .personil-manager-filters .pmf-territory,
        .patrol-cctv-filters .pmf-territory { flex: 0 1 88px; max-width: 110px; }
        .sbo-compact-filters .pmf-extra,
        .personil-manager-filters .pmf-extra,
        .patrol-cctv-filters .pmf-extra { flex: 0 1 100px; max-width: 130px; }
        .sbo-compact-filters .pmf-date,
        .personil-manager-filters .pmf-date,
        .patrol-cctv-filters .pmf-date { flex: 0 1 128px; max-width: 150px; }
        .sbo-compact-filters .pmf-actions,
        .personil-manager-filters .pmf-actions,
        .patrol-cctv-filters .pmf-actions { flex: 0 0 auto; flex-shrink: 0; display: flex; align-items: stretch; }
        .sbo-compact-filters .select2-container,
        .personil-manager-filters .select2-container,
        .patrol-cctv-filters .select2-container { display: none !important; }
        .sbo-compact-filters select[name="daerah"],
        .sbo-compact-filters select[name="wilayah"],
        .sbo-compact-filters select.pmf-native-select,
        .personil-manager-filters select[name="daerah"],
        .personil-manager-filters select[name="wilayah"],
        .personil-manager-filters select.pmf-native-select,
        .patrol-cctv-filters select[name="daerah"],
        .patrol-cctv-filters select[name="wilayah"],
        .patrol-cctv-filters select.pmf-native-select,
        .patrol-cctv-filters select[name="peristiwa"] {
            display: block !important; position: relative !important; width: 100% !important;
            height: 32px !important; min-height: 32px !important; opacity: 1 !important;
            clip: auto !important; margin: 0 !important; pointer-events: auto !important;
        }
        .sbo-compact-filters .pmf-control,
        .sbo-compact-filters .pmf-btn,
        .personil-manager-filters .pmf-control,
        .personil-manager-filters .pmf-btn,
        .patrol-cctv-filters .pmf-control,
        .patrol-cctv-filters .pmf-btn { height: 32px; min-height: 32px; }
        .sbo-compact-filters .pmf-field-text,
        .personil-manager-filters .pmf-field-text,
        .patrol-cctv-filters .pmf-field-text {
            font-family: inherit; font-size: 11px; font-weight: 500; line-height: normal; color: var(--pmf-value);
        }
        .sbo-compact-filters input.pmf-field-text,
        .personil-manager-filters input.pmf-field-text,
        .patrol-cctv-filters input.pmf-field-text { line-height: 32px; }
        .sbo-compact-filters .pmf-field-text::placeholder,
        .personil-manager-filters .pmf-field-text::placeholder,
        .patrol-cctv-filters .pmf-field-text::placeholder {
            color: var(--pmf-placeholder); font-size: 11px; font-weight: 500; opacity: 1;
        }
        .sbo-compact-filters select.pmf-native-select,
        .personil-manager-filters select.pmf-native-select,
        .patrol-cctv-filters select.pmf-native-select {
            appearance: none; -webkit-appearance: none; cursor: pointer;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 24 24' fill='none' stroke='%2394a3b8' stroke-width='2.5' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpath d='M6 9l6 6 6-6'/%3E%3C/svg%3E");
            background-repeat: no-repeat; background-position: right 8px center; background-size: 12px;
            text-overflow: ellipsis;
        }
        .sbo-compact-filters select.pmf-native-select.pmf-empty,
        .personil-manager-filters select.pmf-native-select.pmf-empty,
        .patrol-cctv-filters select.pmf-native-select.pmf-empty { color: var(--pmf-placeholder); }
        .sbo-compact-filters select.pmf-native-select:focus,
        .personil-manager-filters select.pmf-native-select:focus,
        .patrol-cctv-filters select.pmf-native-select:focus { color: var(--pmf-value); }
        .sbo-compact-filters select.pmf-native-select:focus.pmf-empty,
        .personil-manager-filters select.pmf-native-select:focus.pmf-empty,
        .patrol-cctv-filters select.pmf-native-select:focus.pmf-empty { color: var(--pmf-placeholder); }
        .flatpickr-calendar { border-radius: 1rem; box-shadow: 0 20px 40px -12px rgba(15,23,42,0.2); border: 1px solid #e2e8f0; font-family: 'Plus Jakarta Sans', sans-serif; }
        .flatpickr-day.selected, .flatpickr-day.startRange, .flatpickr-day.endRange { background: #0041c8 !important; border-color: #0041c8 !important; }
        .flatpickr-day.inRange { background: rgba(0,65,200,0.1) !important; border-color: transparent !important; box-shadow: none !important; }
        .flatpickr-day:hover { background: rgba(0,65,200,0.08); }
        .flatpickr-months .flatpickr-month { background: #0041c8; border-radius: 1rem 1rem 0 0; }
        .flatpickr-current-month, .flatpickr-monthDropdown-months { color: #fff; }
        .flatpickr-prev-month, .flatpickr-next-month { color: #fff !important; fill: #fff !important; }

        /* Global Pagination (Tailwind-styled Bootstrap pagination) */
        .pagination-container .pagination { margin: 0; display: flex; gap: 0.375rem; }
        .pagination-container .page-item .page-link {
            border-radius: 0.5rem !important; border: 1px solid #e2e8f0; color: #64748b;
            font-size: 11px; font-weight: 700; width: 32px; height: 32px;
            display: flex; align-items: center; justify-content: center; padding: 0; margin: 0; background: #ffffff;
        }
        .pagination-container .page-item.active .page-link { background-color: #0041c8; border-color: #0041c8; color: #ffffff; box-shadow: 0 4px 6px -1px rgba(0,65,200,0.2); }
        .pagination-container .page-item.disabled .page-link { opacity: 0.5; background: #f8fafc; cursor: not-allowed; }
        /* Nav tabs → Korlantas style */
        .nav-tabs { border-bottom: 1px solid #e2e8f0; }
        .nav-tabs .nav-link { font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.06em; color: #94a3b8; border: none; border-bottom: 2px solid transparent; padding: 0.6rem 1rem; }
        .nav-tabs .nav-link.active { color: #0041c8; border-bottom-color: #0041c8; background: transparent; }
        .nav-tabs .nav-link:hover:not(.active) { color: #475569; background: transparent; }
        .card-header-tabs { margin-bottom: -1px; }

        /* Top bar — selalu di atas konten saat scroll */
        #top-bar-wrapper {
            position: fixed;
            top: 0;
            right: 0;
            left: 0;
            z-index: 40;
            background: rgba(255, 255, 255, 0.97);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            box-shadow: 0 1px 3px rgba(15, 23, 42, 0.06);
            overflow: visible;
        }
        /* Top bar full lebar; konten di-indent selaras sidebar (w-56) + padding konten */
        .top-bar-content-indent {
            padding-left: 0.75rem;
            padding-right: 0.75rem;
        }
        @media (min-width: 1024px) {
            .top-bar-content-indent {
                padding-left: calc(14rem + 1.5rem);
                padding-right: 1.5rem;
            }
        }
        #top-bar-spacer { width: 100%; flex-shrink: 0; }
        /* Mega nav dropdown — panel di-render ke body via JS */
        .topbar-mega-btn.is-open { background: rgba(0, 65, 200, 0.1); color: #0041c8; }
        .topbar-mega-panel { animation: topbarMegaIn 0.12s ease-out; }
        .topbar-mega-panel:not(.hidden) { display: block; }
        @keyframes topbarMegaIn {
            from { opacity: 0; transform: translateY(-4px); }
            to   { opacity: 1; transform: translateY(0); }
        }
        @media (min-width: 1024px) {
            #topbar-mega-scroll { justify-content: flex-start; }
        }
        #sidebar .collapse { visibility: visible; }
        #sidebar a.text-decoration-none:hover { text-decoration: none !important; }
        #sidebar .select2-container { width: 100% !important; }
        #sidebar .sidebar-menu-trigger,
        #sidebar .sidebar-menu a,
        #sidebar .sidebar-menu > span {
            min-width: 0;
        }
        #sidebar .sidebar-menu-label {
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            min-width: 0;
        }
        .select2-container--bootstrap-5 .select2-selection {
            min-height: 42px; border-radius: 0.75rem !important;
            border-color: #e2e8f0 !important; background: #ffffff !important;
        }
        .select2-container--bootstrap-5.select2-container--disabled .select2-selection {
            background: #f8fafc !important;
        }
        .select2-container--bootstrap-5.select2-container--focus .select2-selection {
            border-color: #0041c8 !important; background: #fff !important; box-shadow: none !important;
        }

    </style>
    @stack('styles')
</head>
<body class="text-on-surface antialiased overflow-x-hidden bg-[#f8fafc] {{ is_array($bc = \App\Support\RoleMutationPolicy::bodyClasses(auth()->user())) ? implode(' ', $bc) : $bc }}">

    <!-- Mobile Overlay -->
    <div class="fixed inset-0 bg-slate-900/40 backdrop-blur-sm z-[45] hidden lg:hidden" id="sidebar-overlay" onclick="toggleSidebar()"></div>

    @php
        // Prioritaskan Kepegawaian & Organisasi (pemerintahan_daerah & organisasi_daerah) untuk semua user (Admin, Eksekutif, Operator)
        $headerDaerah = trim((string) (auth()->user()->pemerintahan_daerah ?? ''));
        $headerWilayah = trim((string) (auth()->user()->organisasi_daerah ?? ''));
        
        $isUsingPemerintahan = ($headerDaerah !== '');
        $isUsingOrganisasi = ($headerWilayah !== '');

        if ($headerDaerah === '') {
            $headerDaerah = trim((string) (auth()->user()->daerah_nama ?? auth()->user()->daerah ?? ''));
        }
        if ($headerWilayah === '') {
            $headerWilayah = trim((string) (auth()->user()->wilayah_nama ?? auth()->user()->wilayah ?? ''));
        }

        $headerPolda = $headerDaerah !== '' ? strtoupper($headerDaerah) : '';
        
        if ($headerWilayah !== '') {
            if ($isUsingOrganisasi) {
                $headerPolres = strtoupper($headerWilayah);
            } else {
                $headerPolres = strtoupper(trim(str_ireplace(['KABUPATEN ', 'KOTA ', 'KAB. '], '', $headerWilayah)));
            }
        } else {
            $headerPolres = '';
        }

        $labelDaerah = $isUsingPemerintahan ? 'Organisasi:' : 'Polda:';
        $labelWilayah = $isUsingOrganisasi ? 'Sub Organisasi:' : 'Polres:';

        $headerNama = trim((string) (auth()->user()->nama ?? '')) !== '' ? auth()->user()->nama : 'Admin Utama';
        $headerFoto = trim((string) (auth()->user()->fotoprof ?? ''));
        $headerAvatarUrl = $headerFoto !== ''
            ? (str_starts_with($headerFoto, 'http') ? $headerFoto : asset('storage/'.ltrim($headerFoto, '/')))
            : 'https://ui-avatars.com/api/?name='.urlencode($headerNama).'&background=0041c8&color=fff';
    @endphp

    <!-- Side Navigation Shell -->
    <aside class="fixed left-0 top-0 h-full max-h-[100dvh] w-[18rem] lg:w-56 bg-white dark:bg-inverse-surface flex flex-col border-r border-slate-200/60 z-50 transition-transform duration-300 sidebar-collapsed lg:translate-x-0 shadow-[4px_0_24px_-12px_rgba(15,23,42,0.08)]" id="sidebar">
        <div class="shrink-0 px-3 pt-5 pb-4 relative">
            <button type="button" class="lg:hidden absolute top-3 right-2 z-10 text-slate-400 hover:text-primary transition-colors border-0 bg-transparent p-1" onclick="toggleSidebar()" aria-label="Tutup menu">
                <span class="material-symbols-outlined">close</span>
            </button>
            <div class="flex flex-col items-center gap-3 min-w-0 w-full pt-0.5">
                <img
                    src="{{ $headerAvatarUrl }}"
                    alt="{{ $headerNama }}"
                    class="w-full aspect-square rounded-xl object-cover shrink-0 border-2 border-slate-200 shadow-md"
                />
                <div class="min-w-0 w-full text-center px-0.5">
                    <h1 class="text-[15px] font-bold text-primary leading-snug tracking-tight m-0 line-clamp-2">{{ $headerNama }}</h1>
                </div>
            </div>
        </div>

        <nav class="flex-1 min-h-0 overflow-y-auto overflow-x-hidden custom-scrollbar px-2 pb-5">
            <x-menu />
        </nav>
    </aside>

    <!-- Main Content Canvas -->
    <main class="lg:ml-56 min-h-screen transition-all duration-300 bg-[#f8fafc] flex flex-col" id="main-content">
        {{-- Fixed top bar: wilayah (atas) + menu cepat + profil --}}
        <div id="top-bar-wrapper">
            @if($headerPolda !== '' || $headerPolres !== '' || (! ($isExec ?? false) && \App\Helpers\MenuHelper::matrixRoleIdForUser(auth()->user()) === 8))
            <div class="top-bar-content-indent py-2 border-b border-slate-100 flex items-center justify-start min-h-0">
                <p class="text-[15px] lg:text-[18px] font-bold m-0 truncate whitespace-nowrap uppercase tracking-wide flex items-center gap-4 lg:gap-6">
                    @if($headerPolda !== '')
                        <span class="shrink-0">
                            <span class="text-slate-500">{{ $labelDaerah }}</span>
                            <span class="text-[#D8A808]">{{ $headerPolda }}</span>
                        </span>
                    @endif
                    @if(! ($isExec ?? false) && \App\Helpers\MenuHelper::matrixRoleIdForUser(auth()->user()) === 8)
                        <span class="shrink-0">
                            <span class="text-slate-500">Ditlantas</span>
                        </span>
                    @endif
                    @if($headerPolres !== '')
                        <span class="shrink-0">
                            <span class="text-slate-500">{{ $labelWilayah }}</span>
                            <span class="text-[#D8A808]">{{ $headerPolres }}</span>
                        </span>
                    @endif
                </p>
            </div>
            @endif
            <header class="top-bar-content-indent border-b border-slate-200/60 flex justify-between items-center w-full h-14 gap-3">
                <div class="flex items-center gap-2 min-w-0 flex-shrink-0">
                    <button class="lg:hidden text-slate-500 p-1 hover:bg-slate-100 rounded-lg transition-colors border-0 bg-transparent shrink-0" onclick="toggleSidebar()">
                        <span class="material-symbols-outlined text-[22px]">menu</span>
                    </button>
                </div>

                @if(\App\Helpers\MenuHelper::showsTopbarMegaNavForLayout(auth()->user()))
                <div class="flex items-center gap-1.5 min-w-0 flex-1 justify-center overflow-visible px-1">
                    <x-superadmin-header-nav :embedded="true" />
                </div>
                @else
                <div class="flex-1 min-w-0" aria-hidden="true"></div>
                @endif

                <div class="flex items-center shrink-0 gap-2">
                    <button type="button" onclick="toggleAIPanel()" class="flex items-center justify-center p-2 text-[#8b5cf6] hover:bg-[#8b5cf6]/10 transition-all rounded-xl border border-[#8b5cf6]/30 bg-[#8b5cf6]/5 mr-2" title="Alesha Assistant" aria-label="Buka AI">
                        <span class="material-symbols-outlined text-[22px]">smart_toy</span>
                    </button>
                    <a href="{{ route('download.apk') }}" class="flex items-center justify-center p-2 text-primary hover:bg-primary-container/20 transition-all rounded-xl border border-primary/20 bg-primary/5 text-decoration-none mr-2" title="Download Aplikasi Mobile (APK)" aria-label="Download APK">
                        <span class="material-symbols-outlined text-[20px] mr-1">android</span>
                        <span class="text-[12px] font-bold">Download APK</span>
                    </a>
                    <a href="{{ route('profile.show') }}" class="flex items-center gap-2 p-1 pl-2 pr-1 rounded-xl hover:bg-slate-100 transition-colors text-decoration-none" title="Profil">
                        <div class="text-right leading-tight min-w-0 max-w-[160px] sm:max-w-[220px] lg:max-w-[min(480px,48vw)]">
                            <p class="text-[11px] font-bold text-on-surface m-0 truncate">{{ $headerNama }}</p>
                            @if($headerPolda === '' && $headerPolres === '')
                                <p class="text-[11px] font-bold uppercase text-slate-400 mt-0.5 tracking-wider m-0 truncate">{{ auth()->user()->role_label }}</p>
                            @endif
                        </div>
                        <img alt="{{ $headerNama }}" class="w-8 h-8 rounded-lg shadow-sm border border-slate-200 object-cover shrink-0" src="{{ $headerAvatarUrl }}"/>
                    </a>
                    <form method="POST" action="{{ route('logout') }}" class="m-0 shrink-0">
                        @csrf
                        <button type="submit" class="flex items-center justify-center p-2 text-error hover:bg-error-container/40 transition-all rounded-xl border-0 bg-transparent" title="Keluar" aria-label="Keluar">
                            <span class="material-symbols-outlined text-[22px]">logout</span>
                        </button>
                    </form>
                </div>
            </header>
        </div>

        <div id="top-bar-spacer" aria-hidden="true"></div>

        <div class="p-3 lg:p-6 space-y-4 min-h-[calc(100vh-4rem)] flex-1">
            @yield('content')
        </div>
    </main>

    <script>
        function syncTopBarSpacer() {
            const wrapper = document.getElementById('top-bar-wrapper');
            const spacer = document.getElementById('top-bar-spacer');
            if (wrapper && spacer) {
                spacer.style.height = wrapper.offsetHeight + 'px';
            }
        }
        document.addEventListener('DOMContentLoaded', syncTopBarSpacer);
        window.addEventListener('resize', syncTopBarSpacer);
    </script>

    <script>
        function toggleSidebar() {
            const sidebar = document.getElementById('sidebar');
            const overlay = document.getElementById('sidebar-overlay');
            
            sidebar.classList.toggle('sidebar-collapsed');
            overlay.classList.toggle('hidden');
            
            if (!sidebar.classList.contains('sidebar-collapsed')) {
                document.body.style.overflow = 'hidden';
            } else {
                document.body.style.overflow = '';
            }
        }
    </script>
    
    <script src="https://cdn.jsdelivr.net/npm/jquery@3.6.0/dist/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script src="{{ asset('js/sbo-date-input.js') }}"></script>
    <script src="{{ asset('js/sbo-compact-filters.js') }}"></script>
    <script src="{{ asset('js/sbo-gps-autofill.js') }}?v={{ time() }}" defer></script>
    <script src="{{ asset('js/sbo-form-map.js') }}?v={{ time() }}" defer></script>
    <script src="{{ asset('js/sbo-autocomplete.js') }}?v={{ time() }}" defer></script>
    <script src="{{ asset('js/form-media-preview.js') }}" defer></script>
    <script src="{{ asset('js/foto-gps-watermark.js') }}?v={{ time() }}" defer></script>
    <script>
        $(document).ready(function() {
            var $targets = $('select.select2').filter(function () {
                return !$(this).closest('.sbo-compact-filters, .personil-manager-filters').length;
            });
            if ($targets.length) {
                $targets.select2({
                    theme: 'bootstrap-5',
                    width: '100%'
                });
            }
        });
    </script>
    <!-- AI Assistant Panel (Modal) -->
    <div id="ai-panel-overlay" class="fixed inset-0 bg-slate-900/40 backdrop-blur-sm z-[60] hidden transition-opacity duration-300" onclick="toggleAIPanel()"></div>
    <div id="ai-panel" class="fixed top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[95vw] md:w-[800px] h-[85vh] bg-white z-[70] shadow-2xl rounded-2xl flex flex-col border border-slate-200 overflow-hidden transition-all duration-300 scale-95 opacity-0 pointer-events-none">
        <div class="p-3 border-b border-slate-100 flex justify-between items-center bg-gradient-to-r from-indigo-50 to-purple-50">
            <div class="flex items-center gap-2">
                <span class="material-symbols-outlined text-indigo-600">smart_toy</span>
                <h3 class="m-0 font-bold text-[15px] text-indigo-900">Alesha</h3>
            </div>
            <button class="text-slate-400 hover:text-slate-700 transition-colors bg-transparent border-0 p-1 rounded-lg hover:bg-slate-200/50 flex items-center justify-center" onclick="toggleAIPanel()">
                <span class="material-symbols-outlined">close</span>
            </button>
        </div>
        <div class="flex-1 min-h-0 bg-slate-50 relative">
            <!-- Loading indicator -->
            <div class="absolute inset-0 flex items-center justify-center z-0">
                <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-indigo-600"></div>
            </div>
            <iframe src="http://localhost:5173" class="w-full h-full border-0 relative z-10" title="Alesha"></iframe>
        </div>
    </div>

    <script>
        function toggleAIPanel() {
            const panel = document.getElementById('ai-panel');
            const overlay = document.getElementById('ai-panel-overlay');
            
            if (panel.classList.contains('opacity-0')) {
                // Show
                panel.classList.remove('scale-95', 'opacity-0', 'pointer-events-none');
                panel.classList.add('scale-100', 'opacity-100', 'pointer-events-auto');
                overlay.classList.remove('hidden');
                document.body.style.overflow = 'hidden';
            } else {
                // Hide
                panel.classList.add('scale-95', 'opacity-0', 'pointer-events-none');
                panel.classList.remove('scale-100', 'opacity-100', 'pointer-events-auto');
                overlay.classList.add('hidden');
                document.body.style.overflow = '';
            }
        }
    </script>

    @stack('scripts')
</body>
</html>
