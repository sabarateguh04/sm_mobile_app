<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Usm;
use App\Services\UsmPasswordVerifier;
use App\Support\NumericCaptcha;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class LoginController extends Controller
{
    /**
     * Show login form
     */
    public function showLoginForm()
    {
        if (Auth::check()) {
            return redirect()->route('home');
        }
        return view('auth.login');
    }

    /**
     * Handle login request
     */
    public function login(Request $request)
    {
        $request->validate([
            'username' => 'required|string',
            'password' => 'required|string',
            'rememberme' => 'nullable|boolean',
            'captcha' => [
                'required',
                'regex:/^[0-9]{1,4}$/',
                function (string $attribute, mixed $value, \Closure $fail): void {
                    $expected = session(NumericCaptcha::SESSION_LOGIN);
                    if ($expected === null || ! is_numeric($expected)) {
                        $fail('Captcha kedaluwarsa. Silakan muat ulang halaman lalu coba lagi.');

                        return;
                    }
                    if ((int) $value !== (int) $expected) {
                        $fail('Jawaban verifikasi angka salah.');
                    }
                },
            ],
        ], [
            'captcha.required' => 'Harap jawab pertanyaan verifikasi angka.',
            'captcha.regex' => 'Jawaban verifikasi hanya boleh angka (maks. 4 digit).',
        ]);

        $request->session()->forget(NumericCaptcha::SESSION_LOGIN);

        $username = trim((string) $request->username);
        $password = (string) $request->password;
        $remember = $request->boolean('rememberme');

        // Find user by username atau email (prioritas username unik).
        $user = Usm::where('username', $username)->first()
            ?? Usm::where('email', $username)->first();

        if (! $user || ! UsmPasswordVerifier::verify($user, $password)) {
            return redirect()->route('login')
                ->with('login_failed', true)
                ->withInput($request->only('username'));
        }

        UsmPasswordVerifier::upgradeHashIfNeeded($user, $password);
        $user->refresh();

        $status = strtolower(trim((string) ($user->account_status ?? '')));

        if ($status === 'pending') {
            return redirect()->route('login')
                ->with('login_pending', true)
                ->with('login_pending_username', $username);
        }

        // Hanya blokir status eksplisit; null/kosong = legacy dianggap boleh login.
        $blocked = in_array($status, ['blocked', 'inactive', 'suspend', 'suspended', 'banned'], true);

        if ($blocked) {
            throw ValidationException::withMessages([
                'username' => ['Akun dinonaktifkan. Hubungi administrator.'],
            ]);
        }

        Auth::login($user, $remember);

        return redirect()->intended(route('home'));
    }

    /**
     * Confirm logout before POST (helps users who open /logout in the browser).
     */
    public function showLogoutConfirm()
    {
        return view('auth.logout-confirm');
    }

    /**
     * Handle logout
     */
    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
