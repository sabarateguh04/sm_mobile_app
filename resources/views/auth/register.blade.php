@extends('layouts.auth')

@section('auth_card_class', 'auth-card--wide')

@section('title', 'Daftar')
@section('form_heading', 'Buat akun Anda')

@section('alerts')
    @if($errors->any())
        <div class="rounded-lg px-3 py-2 mb-3 border border-red-200 bg-red-50 text-red-800">
            <ul class="mb-0 list-disc list-inside space-y-0.5">
                @foreach($errors->all() as $error)
                    <li class="text-[12px]">{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    @if(!$defaultRole)
        <div class="rounded-lg px-3 py-2 mb-3 border border-amber-200 bg-amber-50 text-amber-900 text-[12px]">
            Role default (ID {{ $defaultRoleId }}) belum ditemukan di database. Hubungi administrator sebelum mendaftar.
        </div>
    @endif
@endsection

@section('content')
    <form method="POST" action="{{ route('register.post') }}" enctype="multipart/form-data" class="register-form">
        @csrf

        <div class="auth-register-form register-form-body user-form-page sbo-compact-select-native uf-register-mobile-row">
            @include('admin.users._form', [
                'user' => $registerUserStub ?? null,
                'registerMode' => true,
                'roles' => $roles,
                'daerahList' => $daerahList,
                'wilayahByDaerahJson' => $wilayahByDaerahJson,
                'wilayahPakaiMasterTerritori' => $wilayahPakaiMasterTerritori ?? false,
                'wilayahPakaiMasterKlDaerah' => $wilayahPakaiMasterKlDaerah ?? false,
            ])
        </div>

        <div class="auth-register-actions mt-3 pt-3 border-t border-slate-200">
            <div class="mb-3">
                <label class="block text-[10px] font-bold text-slate-500 uppercase tracking-wide mb-1" for="staff_captcha">
                    Verifikasi penjumlahan <span class="text-red-500">*</span>
                </label>
                <div class="captcha-math-group flex flex-wrap items-stretch gap-2" data-captcha-scope="register" role="group" aria-label="Captcha penjumlahan">
                    <div class="captcha-math-inline flex items-center shrink-0 px-3 py-2 bg-slate-50 border border-slate-200 rounded-lg text-[13px] font-bold text-slate-800 min-h-[34px]">
                        <span id="staff_captcha_math_label">{{ $captchaLeft }} + {{ $captchaRight }} = ?</span>
                    </div>
                    <input type="text" name="captcha" id="staff_captcha" required maxlength="4" autocomplete="off" inputmode="numeric" pattern="[0-9]*"
                        class="w-20 min-h-[34px] bg-slate-50 border border-slate-200 rounded-lg px-3 py-2 text-[13px] text-slate-800 focus:border-primary focus:bg-white focus:ring-0 outline-none transition-all @error('captcha') border-red-300 bg-red-50 @enderror"
                        placeholder="=?" value="{{ old('captcha') }}" aria-describedby="staff_captcha_math_label">
                    <button type="button" onclick="refreshPortalMathCaptcha(this)" title="Ambil soal penjumlahan baru"
                        class="inline-flex items-center gap-1 px-3 py-2 bg-slate-100 hover:bg-slate-200 border border-slate-200 rounded-lg text-[11px] font-bold text-primary transition-all min-h-[34px]">
                        <span class="material-symbols-outlined text-[16px]">refresh</span>
                        Muat ulang
                    </button>
                </div>
                @error('captcha')
                    <div class="text-[11px] text-red-600 font-medium mt-1">{{ $message }}</div>
                @enderror
            </div>

            <div class="flex flex-row items-center justify-between gap-2">
                <a href="{{ route('login') }}" class="inline-flex items-center justify-center gap-1.5 px-3 py-2 bg-slate-100 hover:bg-slate-200 text-slate-600 font-bold rounded-xl text-[12px] transition-all text-decoration-none flex-1 min-h-[38px]">
                    <span class="material-symbols-outlined text-[16px]">login</span>
                    Masuk
                </a>
                <button type="submit" class="inline-flex items-center justify-center gap-1.5 px-3 py-2 bg-primary hover:bg-primary/90 text-white font-bold rounded-xl text-[12px] transition-all shadow-sm border-0 flex-1 min-h-[38px] @if(!$defaultRole) opacity-50 cursor-not-allowed @endif" @if(!$defaultRole) disabled @endif>
                    <span class="material-symbols-outlined text-[16px]">person_add</span>
                    Daftar
                </button>
            </div>
        </div>
    </form>
@endsection

@push('scripts')
    @include('admin.users._form_dynamic_scripts', [
        'filterKepegawaianIndukUnit' => ($indukPemerintahanUseSelect ?? false) && ($unitKerjaUseSelect ?? false),
        'filterKepegawaianIndukPemerintahanDaerah' => $filterKepegawaianIndukPemerintahanDaerah ?? false,
        'registerMode' => true,
        'userFormPakaiMasterKl' => false,
        'wilayahPakaiMasterKlDaerah' => $wilayahPakaiMasterKlDaerah ?? false,
    ])
@endpush
