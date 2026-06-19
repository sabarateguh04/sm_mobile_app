<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Autentikasi') — Smart Management Lalu Lintas</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200" />
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet" />
    <script src="https://cdn.tailwindcss.com?plugins=forms"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: '#0041c8',
                        'on-primary': '#ffffff',
                    }
                }
            }
        };
    </script>
    <style>
    /* ─── Korlantas Auth Theme ─── */
    :root {
        --auth-primary: #0041c8;
        --auth-primary-dark: #002fa0;
        --auth-primary-light: #3b6ef5;
        --auth-text-dark: #0f172a;
        --auth-text-muted: #64748b;
        --auth-border: #e2e8f0;
        --auth-font: 'Segoe UI', system-ui, -apple-system, BlinkMacSystemFont, sans-serif;
    }
    *, *::before, *::after { box-sizing: border-box; }
    body {
        margin: 0; min-height: 100vh; font-family: var(--auth-font);
        background: #eef2fb;
        display: flex; align-items: center; justify-content: center;
        padding: clamp(0.5rem, 3vw, 2rem);
        -webkit-font-smoothing: antialiased;
    }
    .auth-card {
        display: flex; width: 100%; max-width: 960px;
        background: #fff; border-radius: 1.5rem;
        box-shadow: 0 4px 6px -1px rgba(0,65,200,.06), 0 20px 48px -8px rgba(0,65,200,.22);
        overflow: hidden; position: relative;
    }
    .auth-card--wide { max-width: 1100px; }

    /* ── Brand panel ── */
    .auth-brand {
        position: relative; flex: 0 0 42%; min-width: 0;
        background: linear-gradient(155deg, var(--auth-primary-dark) 0%, var(--auth-primary) 45%, var(--auth-primary-light) 100%);
        color: #fff;
        display: flex; flex-direction: column; align-items: center; justify-content: center;
        padding: clamp(1.5rem, 4vw, 3rem) clamp(1rem, 3vw, 2rem);
        text-align: center; z-index: 1; gap: 1.5rem;
    }
    .auth-brand::after {
        content: '';
        position: absolute; inset: 0;
        background: url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%23ffffff' fill-opacity='0.04'%3E%3Ccircle cx='30' cy='30' r='20'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E");
        pointer-events: none; z-index: 0;
    }
    .auth-brand > * { position: relative; z-index: 1; }
    .auth-brand-welcome {
        font-size: clamp(14px, 1.6vw, 17px); font-weight: 600; opacity: .85; letter-spacing: .06em;
        text-transform: uppercase; margin: 0;
    }
    .auth-brand-logo-wrap { display: flex; flex-direction: column; align-items: center; gap: .75rem; }
    .auth-brand-logo-circle {
        width: clamp(140px, 18vw, 180px); height: clamp(140px, 18vw, 180px);
        display: flex; align-items: center; justify-content: center;
    }
    .auth-brand-logo-circle img { width: 100%; height: 100%; object-fit: contain; }
    .auth-brand-logo-circle svg { width: 100%; height: 100%; }
    .auth-brand-name {
        font-size: clamp(1.05rem, 2.2vw, 1.35rem); font-weight: 800;
        letter-spacing: .04em; margin: 0; line-height: 1.2;
        text-transform: uppercase; white-space: nowrap;
    }
    .auth-brand-sub {
        font-size: clamp(1.05rem, 2.2vw, 1.35rem); font-weight: 800;
        letter-spacing: .04em; margin: 0; line-height: 1.2;
        text-transform: uppercase; white-space: nowrap; opacity: .9;
    }
    .auth-brand-badges {
        display: flex; flex-wrap: wrap; gap: .5rem; justify-content: center;
    }
    .auth-brand-badge {
        display: inline-flex; align-items: center; gap: .3rem;
        background: rgba(255,255,255,.15); border: 1px solid rgba(255,255,255,.2);
        border-radius: 999px; padding: .3rem .8rem;
        font-size: 11px; font-weight: 600; opacity: .9;
        backdrop-filter: blur(4px);
    }
    .auth-brand-badge .material-symbols-outlined { font-size: 14px; }
    /* Wavy edge desktop */
    .auth-brand-wave {
        position: absolute; top: 0; right: -1px; bottom: 0; width: 60px;
        pointer-events: none; z-index: 2;
    }
    .auth-brand-wave svg { height: 100%; width: 100%; display: block; }
    .auth-brand-wave-mobile { display: none; }

    /* ── Form panel ── */
    .auth-form-panel {
        flex: 1 1 58%; min-width: 0;
        display: flex; flex-direction: column; justify-content: center;
        padding: clamp(1.5rem, 4vw, 3rem) clamp(1.5rem, 4vw, 3rem);
        overflow-y: auto; background: #fff;
    }
    .auth-form-panel::-webkit-scrollbar { width: 5px; }
    .auth-form-panel::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 3px; }
    .auth-form-heading {
        font-size: clamp(1.15rem, 2.5vw, 1.3rem); font-weight: 800;
        color: var(--auth-text-dark); margin: 0 0 1.25rem;
        letter-spacing: -.02em;
    }

    /* Alerts */
    .auth-alert {
        border-radius: .75rem; padding: .7rem 1rem;
        font-size: 12px; margin-bottom: .75rem; border: none;
        display: flex; align-items: flex-start; gap: .5rem;
    }
    .auth-alert .material-symbols-outlined { font-size: 16px; flex-shrink: 0; margin-top: .05rem; }
    .auth-alert-success { background: #f0fdf4; color: #166534; }
    .auth-alert-warning { background: #fffbeb; color: #92400e; }
    .auth-alert-danger { background: #fef2f2; color: #991b1b; }

    /* Fields */
    .auth-field { margin-bottom: 1.1rem; }
    .auth-field label, .auth-captcha-label {
        display: block; font-size: 11px; font-weight: 700;
        color: #374151; margin-bottom: .35rem; letter-spacing: .01em; text-transform: uppercase;
    }
    .auth-field-input-wrap { position: relative; display: flex; align-items: center; }
    .auth-field-input-wrap .form-control,
    .auth-field-input-wrap .captcha-math-input {
        flex: 1; border: 1.5px solid var(--auth-border);
        border-radius: .75rem; background: #f8fafc;
        padding: .6rem .875rem; font-size: 13px; color: var(--auth-text-dark);
        box-shadow: none !important; min-height: 2.5rem;
        transition: border-color .15s, background .15s, box-shadow .15s;
    }
    .auth-field-input-wrap .form-control::placeholder,
    .auth-field-input-wrap .captcha-math-input::placeholder { color: #94a3b8; }
    .auth-field-input-wrap .form-control:focus,
    .auth-field-input-wrap .captcha-math-input:focus {
        border-color: var(--auth-primary) !important;
        background: #fff; box-shadow: 0 0 0 3px rgba(0,65,200,.1) !important; outline: none;
    }
    .auth-field-input-wrap .form-control.is-invalid,
    .auth-field-input-wrap .captcha-math-input.is-invalid { border-color: #ef4444 !important; }
    .auth-field-check {
        position: absolute; right: .75rem; color: var(--auth-primary);
        font-size: 16px; opacity: 0; transition: opacity .2s ease; pointer-events: none;
    }
    .auth-field-check.material-symbols-outlined { font-size: 18px; }
    .auth-field-input-wrap.is-filled .auth-field-check { opacity: 1; }
    .auth-field .invalid-feedback { font-size: 11px; margin-top: .3rem; }

    /* Remember */
    .auth-remember {
        display: flex; align-items: center; gap: .5rem;
        margin-bottom: 1.1rem; font-size: 12px; color: var(--auth-text-muted);
        cursor: pointer;
    }
    .auth-remember input { accent-color: var(--auth-primary); cursor: pointer; }

    /* Actions */
    .auth-actions { display: flex; gap: .75rem; flex-wrap: wrap; margin-top: .75rem; }
    .auth-btn {
        flex: 1 1 auto; min-width: 100px;
        padding: .7rem 1.5rem; border-radius: 999px;
        font-size: 13px; font-weight: 700; text-align: center; text-decoration: none;
        border: 2px solid transparent; cursor: pointer;
        transition: transform .15s, box-shadow .15s, background .15s;
        display: inline-flex; align-items: center; justify-content: center; gap: .4rem;
    }
    .auth-btn-primary {
        background: var(--auth-primary); color: #fff; border-color: transparent;
        box-shadow: 0 4px 12px rgba(0,65,200,.25);
    }
    .auth-btn-primary:hover:not(:disabled) {
        color: #fff; transform: translateY(-1px);
        box-shadow: 0 8px 20px rgba(0,65,200,.35);
        background: var(--auth-primary-dark);
    }
    .auth-btn-outline {
        background: #fff; color: var(--auth-text-dark); border-color: var(--auth-border);
    }
    .auth-btn-outline:hover { border-color: var(--auth-primary); color: var(--auth-primary); text-decoration: none; }
    .auth-btn:disabled { opacity: .55; cursor: not-allowed; }

    /* Captcha */
    .captcha-math-group {
        display: flex; gap: .5rem; align-items: stretch; width: 100%; flex-wrap: nowrap;
    }
    .captcha-math-inline {
        display: flex; align-items: center; flex-shrink: 0;
        padding: .5rem .875rem; background: #eef2fb;
        border: 1.5px solid var(--auth-border); border-radius: .75rem;
        font-size: 13px; font-weight: 700; color: var(--auth-text-dark);
        white-space: nowrap; min-height: 2.5rem;
    }
    .captcha-math-input { flex: 1 1 80px; min-width: 0; max-width: 80px; }
    .captcha-math-reload {
        flex-shrink: 0; padding: .5rem .75rem;
        border: 1.5px solid var(--auth-border); border-radius: .75rem;
        background: #f8fafc; font-size: 11px; font-weight: 700;
        color: var(--auth-primary); cursor: pointer; min-height: 2.5rem;
        transition: background .15s, border-color .15s;
        display: flex; align-items: center; gap: .25rem;
    }
    .captcha-math-reload:hover { background: #eef2fb; border-color: var(--auth-primary); }
    .captcha-math-reload .material-symbols-outlined { font-size: 14px; }

    /* Register — selaras user-form-page compact */
    .auth-register-form.user-form-page { display: flex; flex-direction: column; gap: 0; }
    .auth-register-form .user-form-section { margin-bottom: .375rem; }
    .auth-register-form .user-form-section:last-child { margin-bottom: 0; }
    .auth-register-actions { margin-top: .75rem; padding-top: .75rem; border-top: 1px solid #e2e8f0; }

    .uf-register-mobile-row .user-form-section > div:first-child { padding: 0.2rem 0.5rem; }
    .uf-register-mobile-row .user-form-section > div:first-child h3 { font-size: 10px; }
    .uf-register-mobile-row .user-form-section > div:last-child { padding: 0.35rem 0.5rem; }
    .uf-register-mobile-row .uf-form-row { --bs-gutter-x: 0.3rem; --bs-gutter-y: 0.2rem; }
    .uf-register-mobile-row .user-form-section select,
    .uf-register-mobile-row .user-form-section input[type="text"],
    .uf-register-mobile-row .user-form-section input[type="email"],
    .uf-register-mobile-row .user-form-section input[type="password"],
    .uf-register-mobile-row .user-form-section input[type="tel"] {
        font-size: 11px !important;
        padding: 0.28rem 0.4rem !important;
        min-height: 30px !important;
        border-radius: 0.375rem !important;
    }
    .uf-register-mobile-row .user-form-section select {
        padding-right: 1.35rem !important;
        text-overflow: ellipsis;
    }
    .uf-register-mobile-row .relative .material-symbols-outlined {
        font-size: 13px !important;
        right: 0.3rem !important;
    }
    .uf-register-mobile-row .user-form-section label {
        font-size: 9px !important;
        letter-spacing: 0.03em;
        margin-bottom: 0.1rem !important;
    }

    /* Register: 2 kolom sejajar di mobile */
    @media (max-width: 767.98px) {
        .uf-register-mobile-row .uf-form-row {
            --bs-gutter-x: 0.25rem;
            flex-wrap: nowrap;
        }
        .uf-register-mobile-row .uf-form-row > [class*="col-"] {
            flex: 0 0 50%;
            max-width: 50%;
            min-width: 0;
        }
        .uf-register-mobile-row .user-form-section select,
        .uf-register-mobile-row .user-form-section input[type="text"],
        .uf-register-mobile-row .user-form-section input[type="email"],
        .uf-register-mobile-row .user-form-section input[type="password"],
        .uf-register-mobile-row .user-form-section input[type="tel"] {
            font-size: 10px !important;
            padding: 0.25rem 0.35rem !important;
            min-height: 28px !important;
        }
        .uf-register-mobile-row .user-form-section select {
            padding-right: 1.25rem !important;
        }
        .uf-register-mobile-row .relative .material-symbols-outlined {
            font-size: 12px !important;
            right: 0.25rem !important;
        }
        .uf-register-mobile-row .user-form-section label {
            font-size: 8px !important;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
    }

    /* Legacy register overrides (non-compact fallback) */
    .auth-register-form:not(.user-form-page) .form-section-title {
        color: var(--auth-text-muted) !important; font-size: 10px !important;
        letter-spacing: .08em; border-bottom-color: #e2e8f0 !important;
        margin-top: 1.25rem !important; text-transform: uppercase;
    }
    .auth-register-form .form-section-title:first-of-type { margin-top: 0 !important; }
    .auth-register-form .form-label { font-size: 11px; font-weight: 700; color: var(--auth-text-dark); }
    .auth-register-form .form-control,
    .auth-register-form .form-select {
        border-radius: .75rem !important; font-size: 12px; border-color: #e2e8f0;
        background: #f8fafc; color: #0f172a;
    }
    .auth-register-form:not(.user-form-page) .form-control:focus,
    .auth-register-form:not(.user-form-page) .form-select:focus {
        border-color: var(--auth-primary) !important;
        box-shadow: 0 0 0 3px rgba(0,65,200,.1) !important;
    }
    .auth-register-actions--legacy { margin-top: 1.25rem; padding-top: 1.25rem; border-top: 1px solid #e2e8f0; }

    /* Mobile */
    @media (max-width: 767.98px) {
        body { padding: 0; align-items: stretch; }
        .auth-card {
            flex-direction: column; max-width: none;
            min-height: 100vh; border-radius: 0; box-shadow: none;
        }
        .auth-brand {
            flex: 0 0 auto; flex-direction: row; align-items: center;
            justify-content: flex-start; gap: .75rem;
            padding: .85rem 1.25rem 1.1rem; min-height: 0;
        }
        .auth-brand-logo-circle { width: 64px; height: 64px; }
        .auth-brand-welcome { display: none; }
        .auth-brand-name { font-size: 1.05rem; letter-spacing: .01em; margin: 0; text-align: left; }
        .auth-brand-sub, .auth-brand-badges, .auth-brand-wave { display: none; }
        .auth-brand-logo-wrap { flex-direction: row; align-items: center; gap: .6rem; padding: 0; margin: 0; }
        .auth-brand-wave-mobile {
            display: block; position: absolute; bottom: -1px; left: 0; right: 0;
            width: 100%; height: 20px; pointer-events: none;
        }
        .auth-brand-wave-mobile svg { width: 100%; height: 100%; display: block; }
        .auth-form-panel { flex: 1 1 auto; padding: 1.25rem 1rem 1.5rem; justify-content: flex-start; }
    }
    @media (min-width: 768px) {
        .auth-brand { padding-right: 72px !important; }
    }
    @media (max-width: 767.98px) {
        .auth-brand { padding-bottom: 24px !important; }
    }
    </style>
    @stack('styles')
</head>
<body>
    <div class="auth-card @yield('auth_card_class')" role="main">
        <aside class="auth-brand" aria-label="Selamat datang">
            <p class="auth-brand-welcome">Selamat datang di</p>

            <div class="auth-brand-logo-wrap">
                <div class="auth-brand-logo-circle" aria-hidden="true">
                    @if(file_exists(public_path('images/logo-korlantas.png')))
                        <img src="{{ asset('images/logo-korlantas.png') }}" alt="Logo Korlantas">
                    @else
                        <svg viewBox="0 0 100 100" xmlns="http://www.w3.org/2000/svg" style="width:100%;height:100%;">
                            <circle cx="50" cy="50" r="46" fill="none" stroke="rgba(255,255,255,.6)" stroke-width="3"/>
                            <circle cx="50" cy="50" r="38" fill="rgba(255,255,255,.15)"/>
                            <rect x="38" y="22" width="24" height="56" rx="4" fill="white" opacity=".9"/>
                            <circle cx="50" cy="33" r="7" fill="#ef4444"/>
                            <circle cx="50" cy="50" r="7" fill="#f59e0b"/>
                            <circle cx="50" cy="67" r="7" fill="#22c55e"/>
                            <path d="M20 82 Q50 72 80 82" stroke="rgba(255,255,255,.7)" stroke-width="2.5" fill="none"/>
                        </svg>
                    @endif
                </div>
                <h1 class="auth-brand-name">Smart Management</h1>
                <p class="auth-brand-sub">Road Safety</p>
            </div>

            {{-- Desktop wave --}}
            <div class="auth-brand-wave d-none d-md-block" aria-hidden="true">
                <svg viewBox="0 0 72 600" preserveAspectRatio="none" xmlns="http://www.w3.org/2000/svg">
                    <path fill="#ffffff" d="M0,0 C18,80 8,160 24,240 C40,320 12,400 28,480 C44,540 36,580 72,600 L72,0 Z"/>
                </svg>
            </div>
            {{-- Mobile bottom wave --}}
            <div class="auth-brand-wave-mobile d-md-none" aria-hidden="true">
                <svg viewBox="0 0 400 24" preserveAspectRatio="none" xmlns="http://www.w3.org/2000/svg">
                    <path fill="#ffffff" d="M0,12 C50,0 100,24 150,12 C200,0 250,20 300,10 C350,2 380,18 400,8 L400,24 L0,24 Z"/>
                </svg>
            </div>
        </aside>

        <section class="auth-form-panel">
            <h2 class="auth-form-heading">@yield('form_heading')</h2>

            @yield('alerts')
            @yield('content')
        </section>
    </div>

    @yield('modals')

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            document.querySelectorAll('.auth-field-input-wrap').forEach(function (wrap) {
                var input = wrap.querySelector('input, select, textarea');
                if (!input) return;
                function sync() {
                    wrap.classList.toggle('is-filled', input.value && String(input.value).trim() !== '');
                }
                input.addEventListener('input', sync);
                input.addEventListener('change', sync);
                sync();
            });
        });

        function refreshPortalMathCaptcha(btn) {
            var root = btn && btn.closest ? btn.closest('[data-captcha-scope]') : document.querySelector('[data-captcha-scope]');
            if (!root) return;
            var scope = root.getAttribute('data-captcha-scope');
            var label = root.querySelector('.captcha-math-inline span');
            var input = root.querySelector('input[name="captcha"]');
            fetch(@json(route('auth.captcha.refresh-math')), {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify({ scope: scope })
            })
            .then(function (r) { if (!r.ok) throw new Error('refresh failed'); return r.json(); })
            .then(function (data) {
                if (label && data.left != null && data.right != null) {
                    label.textContent = data.left + ' + ' + data.right + ' = ?';
                }
                if (input) {
                    input.value = '';
                    input.classList.remove('is-invalid');
                    var wrap = input.closest('.auth-field-input-wrap');
                    if (wrap) wrap.classList.remove('is-filled');
                }
            })
            .catch(function () {});
        }
    </script>
    @stack('scripts')
</body>
</html>
