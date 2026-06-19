@extends('layouts.auth')

@section('title', 'Login')
@section('form_heading', 'Masuk ke akun Anda')

@section('alerts')
    @if(session('registered') && !session('registration_needs_activation'))
        <div class="auth-alert auth-alert-success">
            <span class="material-symbols-outlined" style="font-variation-settings:'FILL' 1;">check_circle</span>
            {{ session('registered') }}
        </div>
    @endif

    @if(session('account_pending'))
        <div class="auth-alert auth-alert-warning">
            <span class="material-symbols-outlined" style="font-variation-settings:'FILL' 1;">hourglass_top</span>
            Sesi dihentikan: akun Anda belum diaktifkan. Tunggu verifikasi administrator lalu login kembali.
        </div>
    @endif

    @if(session('account_blocked'))
        <div class="auth-alert auth-alert-danger">
            <span class="material-symbols-outlined" style="font-variation-settings:'FILL' 1;">block</span>
            Akun tidak aktif. Hubungi administrator.
        </div>
    @endif

    @if($errors->has('captcha'))
        <div class="auth-alert auth-alert-danger">
            <span class="material-symbols-outlined" style="font-variation-settings:'FILL' 1;">error</span>
            <div>{{ $errors->first('captcha') }}</div>
        </div>
    @endif
@endsection

@section('content')
    <form method="POST" action="{{ route('login.post') }}" novalidate>
        @csrf

        <div class="auth-field">
            <label for="username">Username atau Email</label>
            <div class="auth-field-input-wrap">
                <input
                    type="text"
                    class="form-control @error('username') is-invalid @enderror"
                    id="username"
                    name="username"
                    value="{{ old('username') }}"
                    placeholder="Masukkan username atau email"
                    required
                    autofocus
                >
                <span class="material-symbols-outlined auth-field-check" style="font-variation-settings:'FILL' 1;">check_circle</span>
            </div>
            @error('username')
                <div class="invalid-feedback d-block">{{ $message }}</div>
            @enderror
        </div>

        <div class="auth-field">
            <label for="password">Password</label>
            <div class="auth-field-input-wrap">
                <input
                    type="password"
                    class="form-control @error('password') is-invalid @enderror"
                    id="password"
                    name="password"
                    placeholder="Masukkan password"
                    required
                >
                <span class="material-symbols-outlined auth-field-check" style="font-variation-settings:'FILL' 1;">check_circle</span>
            </div>
            @error('password')
                <div class="invalid-feedback d-block">{{ $message }}</div>
            @enderror
        </div>

        <label class="auth-remember">
            <input type="checkbox" id="rememberme" name="rememberme" value="1">
            Ingat saya
        </label>

        <div class="auth-field">
            <span class="auth-captcha-label">Verifikasi penjumlahan</span>
            <div
                class="captcha-math-group"
                data-captcha-scope="login"
                role="group"
                aria-label="Captcha penjumlahan"
            >
                <div class="captcha-math-inline">
                    <span id="staff_captcha_math_label">{{ $captchaLeft }} + {{ $captchaRight }} = ?</span>
                </div>
                <div class="auth-field-input-wrap flex-grow-1" style="min-width: 70px; max-width: 90px;">
                    <input
                        type="text"
                        name="captcha"
                        id="staff_captcha"
                        required
                        maxlength="4"
                        autocomplete="off"
                        inputmode="numeric"
                        pattern="[0-9]*"
                        class="captcha-math-input @error('captcha') is-invalid @enderror"
                        placeholder="= ?"
                        value="{{ old('captcha') }}"
                    >
                </div>
                <button
                    type="button"
                    onclick="refreshPortalMathCaptcha(this)"
                    title="Ambil soal baru"
                    class="captcha-math-reload"
                >
                    <span class="material-symbols-outlined">refresh</span>
                    Muat ulang
                </button>
            </div>
            @error('captcha')
                <div class="invalid-feedback d-block">{{ $message }}</div>
            @enderror
        </div>

        <div class="auth-actions">
            <button type="submit" class="auth-btn auth-btn-primary">
                <span class="material-symbols-outlined" style="font-size:16px;">login</span>
                Masuk
            </button>
            @if(config('registration.enabled', true))
                <a href="{{ route('register') }}" class="auth-btn auth-btn-outline">
                    <span class="material-symbols-outlined" style="font-size:16px;">person_add</span>
                    Daftar
                </a>
            @endif
        </div>
    </form>
@endsection

@section('modals')
    {{-- Login gagal --}}
    @if(session('login_failed'))
        <div class="modal fade" id="modalLoginFailed" tabindex="-1" aria-labelledby="modalLoginFailedLabel" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content border-0 rounded-4 overflow-hidden shadow-lg">
                    <div class="modal-header border-0 pb-0 pt-4 px-4" style="background:linear-gradient(135deg,#ef4444,#dc2626);">
                        <div class="d-flex align-items-center gap-3 w-100">
                            <div class="rounded-circle bg-white bg-opacity-25 d-flex align-items-center justify-content-center" style="width:48px;height:48px;flex-shrink:0;">
                                <span class="material-symbols-outlined text-white" style="font-size:24px;font-variation-settings:'FILL' 1;">gpp_bad</span>
                            </div>
                            <div>
                                <h5 class="modal-title fw-bold text-white mb-0" id="modalLoginFailedLabel">Login Gagal</h5>
                                <p class="text-white opacity-75 mb-0" style="font-size:12px;">Kredensial tidak cocok</p>
                            </div>
                        </div>
                    </div>
                    <div class="modal-body px-4 pt-4 pb-3">
                        <div class="d-flex align-items-start gap-2 p-3 rounded-3 mb-3" style="background:#fef2f2;">
                            <span class="material-symbols-outlined text-danger mt-1" style="font-size:18px;font-variation-settings:'FILL' 1;">error</span>
                            <div>
                                <div class="fw-semibold text-dark mb-1" style="font-size:13px;">Username atau password salah</div>
                                <div class="text-muted" style="font-size:12px;">Periksa kembali username/email dan password yang Anda masukkan.</div>
                            </div>
                        </div>
                        <ul class="list-unstyled mb-0" style="font-size:12px;color:#374151;">
                            <li class="mb-2 d-flex align-items-center gap-2"><span class="material-symbols-outlined text-slate-400" style="font-size:15px;">keyboard</span>Pastikan tidak ada typo pada username/email</li>
                            <li class="mb-2 d-flex align-items-center gap-2"><span class="material-symbols-outlined text-slate-400" style="font-size:15px;">lock</span>Password bersifat case-sensitive</li>
                            <li class="d-flex align-items-center gap-2"><span class="material-symbols-outlined text-slate-400" style="font-size:15px;">person_off</span>Jika lupa password, hubungi administrator</li>
                        </ul>
                    </div>
                    <div class="modal-footer border-0 px-4 pb-4">
                        <button type="button" class="btn btn-danger w-100 fw-bold rounded-3 d-flex align-items-center justify-content-center gap-2" data-bs-dismiss="modal">
                            <span class="material-symbols-outlined" style="font-size:16px;">refresh</span>Coba Lagi
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    {{-- Registrasi berhasil, menunggu aktivasi --}}
    @if(session('registered') && session('registration_needs_activation'))
        <div class="modal fade" id="modalAfterRegisterPending" tabindex="-1" aria-labelledby="modalAfterRegisterPendingLabel" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content border-0 rounded-4 overflow-hidden shadow-lg">
                    <div class="modal-header border-0 pb-0 pt-4 px-4" style="background:linear-gradient(135deg,#22c55e,#16a34a);">
                        <div class="d-flex align-items-center gap-3 w-100">
                            <div class="rounded-circle bg-white bg-opacity-25 d-flex align-items-center justify-content-center" style="width:48px;height:48px;flex-shrink:0;">
                                <span class="material-symbols-outlined text-white" style="font-size:24px;font-variation-settings:'FILL' 1;">send</span>
                            </div>
                            <div>
                                <h5 class="modal-title fw-bold text-white mb-0" id="modalAfterRegisterPendingLabel">Pendaftaran Berhasil!</h5>
                                <p class="text-white opacity-75 mb-0" style="font-size:12px;">Terima kasih telah mendaftar</p>
                            </div>
                        </div>
                    </div>
                    <div class="modal-body px-4 pt-4 pb-3">
                        <div class="d-flex align-items-start gap-2 p-3 rounded-3 mb-3" style="background:#fffbeb;">
                            <span class="material-symbols-outlined text-warning mt-1" style="font-size:18px;font-variation-settings:'FILL' 1;">hourglass_top</span>
                            <div>
                                <div class="fw-semibold text-dark mb-1" style="font-size:13px;">Menunggu verifikasi admin</div>
                                <div class="text-muted" style="font-size:12px;">Akun Anda sedang dalam proses peninjauan. Anda dapat login setelah akun diaktifkan.</div>
                            </div>
                        </div>
                        <ul class="list-unstyled mt-3 mb-0" style="font-size:12px;color:#374151;">
                            <li class="mb-2 d-flex align-items-center gap-2"><span class="material-symbols-outlined text-success" style="font-size:15px;font-variation-settings:'FILL' 1;">check_circle</span>Data registrasi Anda telah tersimpan</li>
                            <li class="mb-2 d-flex align-items-center gap-2"><span class="material-symbols-outlined text-warning" style="font-size:15px;font-variation-settings:'FILL' 1;">schedule</span>Tunggu konfirmasi dari administrator</li>
                            <li class="d-flex align-items-center gap-2"><span class="material-symbols-outlined text-info" style="font-size:15px;font-variation-settings:'FILL' 1;">mail</span>Anda akan dihubungi jika diperlukan</li>
                        </ul>
                    </div>
                    <div class="modal-footer border-0 px-4 pb-4">
                        <button type="button" class="btn btn-success w-100 fw-bold rounded-3 d-flex align-items-center justify-content-center gap-2" data-bs-dismiss="modal">
                            <span class="material-symbols-outlined" style="font-size:16px;font-variation-settings:'FILL' 1;">thumb_up</span>Mengerti, Saya Akan Menunggu
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    {{-- Login ditolak — akun pending --}}
    @if(session('login_pending'))
        <div class="modal fade" id="modalLoginPending" tabindex="-1" aria-labelledby="modalLoginPendingLabel" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content border-0 rounded-4 overflow-hidden shadow-lg">
                    <div class="modal-header border-0 pb-0 pt-4 px-4" style="background:linear-gradient(135deg,#f59e0b,#d97706);">
                        <div class="d-flex align-items-center gap-3 w-100">
                            <div class="rounded-circle bg-white bg-opacity-25 d-flex align-items-center justify-content-center" style="width:48px;height:48px;flex-shrink:0;">
                                <span class="material-symbols-outlined text-white" style="font-size:24px;font-variation-settings:'FILL' 1;">hourglass_top</span>
                            </div>
                            <div>
                                <h5 class="modal-title fw-bold text-white mb-0" id="modalLoginPendingLabel">Akun Dalam Proses Verifikasi</h5>
                                <p class="text-white opacity-75 mb-0" style="font-size:12px;">Login belum dapat dilakukan</p>
                            </div>
                        </div>
                    </div>
                    <div class="modal-body px-4 pt-4 pb-3">
                        <div class="d-flex align-items-start gap-2 p-3 rounded-3 mb-3" style="background:#fffbeb;">
                            <span class="material-symbols-outlined text-warning mt-1" style="font-size:18px;font-variation-settings:'FILL' 1;">info</span>
                            <div>
                                <div class="fw-semibold text-dark mb-1" style="font-size:13px;">Data masih proses verifikasi</div>
                                <div class="text-muted" style="font-size:12px;">Akun <strong>{{ session('login_pending_username') }}</strong> sudah terdaftar, namun masih menunggu persetujuan administrator.</div>
                            </div>
                        </div>
                        <ul class="list-unstyled mb-0" style="font-size:12px;color:#374151;">
                            <li class="mb-2 d-flex align-items-center gap-2"><span class="material-symbols-outlined text-warning" style="font-size:15px;font-variation-settings:'FILL' 1;">manage_accounts</span>Status akun: <span class="badge bg-warning text-dark ms-1" style="font-size:10px;">Menunggu Verifikasi</span></li>
                            <li class="mb-2 d-flex align-items-center gap-2"><span class="material-symbols-outlined text-primary" style="font-size:15px;font-variation-settings:'FILL' 1;">shield</span>Harap tunggu hingga administrator mengaktifkan akun</li>
                            <li class="d-flex align-items-center gap-2"><span class="material-symbols-outlined text-success" style="font-size:15px;font-variation-settings:'FILL' 1;">support_agent</span>Hubungi admin jika butuh bantuan lebih lanjut</li>
                        </ul>
                    </div>
                    <div class="modal-footer border-0 px-4 pb-4">
                        <button type="button" class="btn btn-warning w-100 fw-bold rounded-3 text-dark d-flex align-items-center justify-content-center gap-2" data-bs-dismiss="modal">
                            <span class="material-symbols-outlined" style="font-size:16px;">arrow_back</span>Kembali ke Halaman Login
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            var failedEl = document.getElementById('modalLoginFailed');
            if (failedEl && typeof bootstrap !== 'undefined' && bootstrap.Modal) {
                bootstrap.Modal.getOrCreateInstance(failedEl).show();
            }
            var pendingRegisterEl = document.getElementById('modalAfterRegisterPending');
            if (pendingRegisterEl && typeof bootstrap !== 'undefined' && bootstrap.Modal) {
                bootstrap.Modal.getOrCreateInstance(pendingRegisterEl).show();
            }
            var pendingLoginEl = document.getElementById('modalLoginPending');
            if (pendingLoginEl && typeof bootstrap !== 'undefined' && bootstrap.Modal) {
                bootstrap.Modal.getOrCreateInstance(pendingLoginEl).show();
            }
        });
    </script>
@endpush
