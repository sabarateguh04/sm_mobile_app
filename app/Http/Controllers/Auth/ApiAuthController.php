<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Usm;
use App\Services\UsmPasswordVerifier;
use App\Support\UsmMasterReferensi;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rules\Password;

class ApiAuthController extends Controller
{
    /**
     * Handle user registration via API.
     */
    public function register(Request $request)
    {
        if (! config('registration.enabled', true)) {
            return response()->json(['status' => 'error', 'message' => 'Registrasi dinonaktifkan.'], 403);
        }

        $status = (string) config('registration.default_account_status', 'pending');
        $defaultRoleId = (int) config('registration.default_role_id', 4);

        $rules = array_merge(
            UsmMasterReferensi::daerahWilayahResortValidationRules(),
            UsmMasterReferensi::registrationKepegawaianValidationRules(),
            [
                'username' => ['required', 'string', 'max:255', 'regex:/^\S+$/', 'unique:usm,username'],
                'email' => ['required', 'string', 'email', 'max:255', 'unique:usm,email'],
                'nama' => ['required', 'string', 'max:255'],
                'password' => ['required', 'string', 'confirmed', Password::min(6)],
                'notelp' => ['nullable', 'string', 'max:255'],
            ]
        );

        $validated = $request->validate($rules);

        $validated['password'] = Hash::make($validated['password']);
        $validated['account_status'] = $status;
        $validated['user_role_id'] = $defaultRoleId;
        unset($validated['password_confirmation']);

        $user = DB::transaction(function () use ($validated) {
            return Usm::create($validated);
        });

        return response()->json([
            'status' => 'success',
            'message' => 'Pendaftaran berhasil.',
            'data' => $user
        ], 201);
    }

    /**
     * Handle user login via API and return a JWT.
     */
    public function login(Request $request)
    {
        $request->validate([
            'username' => 'required|string',
            'password' => 'required|string',
        ]);

        $username = trim((string) $request->username);
        $password = (string) $request->password;

        // Cari user berdasarkan username atau email
        $user = Usm::where('username', $username)->first()
            ?? Usm::where('email', $username)->first();

        // Verifikasi password menggunakan verifier aplikasi
        if (!$user || !UsmPasswordVerifier::verify($user, $password)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Username atau password salah.'
            ], 401);
        }

        // Cek status akun
        $status = strtolower(trim((string) ($user->account_status ?? '')));
        if ($status === 'pending') {
            return response()->json([
                'status' => 'error',
                'message' => 'Akun Anda sedang dalam proses verifikasi (pending).'
            ], 403);
        }

        $blocked = in_array($status, ['blocked', 'inactive', 'suspend', 'suspended', 'banned'], true);
        if ($blocked) {
            return response()->json([
                'status' => 'error',
                'message' => 'Akun dinonaktifkan. Hubungi administrator.'
            ], 403);
        }

        // Generate token untuk user ini
        $token = Auth::guard('api')->login($user);

        if (!$token) {
            return response()->json([
                'status' => 'error',
                'message' => 'Gagal membuat token autentikasi.'
            ], 500);
        }

        return $this->respondWithToken($token);
    }

    /**
     * Get the authenticated user profile.
     */
    public function me()
    {
        $user = Auth::guard('api')->user();
        
        return response()->json([
            'status' => 'success',
            'data' => $user
        ]);
    }

    /**
     * Log the user out (Invalidate the token).
     */
    public function logout()
    {
        Auth::guard('api')->logout();

        return response()->json([
            'status' => 'success',
            'message' => 'Berhasil logout.'
        ]);
    }

    /**
     * Refresh a token.
     */
    public function refresh()
    {
        try {
            $newToken = Auth::guard('api')->refresh();
            return $this->respondWithToken($newToken);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Token tidak valid atau tidak bisa di-refresh.'
            ], 401);
        }
    }

    /**
     * Generate JWT tokens for all active accounts with their regions.
     */
    public function allTokens()
    {
        $users = Usm::whereNotIn('account_status', ['blocked', 'inactive', 'suspend', 'suspended', 'banned'])
            ->get();

        $tokens = [];
        foreach ($users as $user) {
            $payload = [
                'sub' => $user->id_usm,
                'username' => $user->username,
                'nama' => $user->nama,
                'daerah' => $user->daerah,
                'wilayah' => $user->wilayah,
                'role_id' => $user->user_role_id,
                'iat' => time(),
                'exp' => time() + (3600 * 24 * 30), // 30 days
            ];

            $tokens[] = [
                'id_usm' => $user->id_usm,
                'username' => $user->username,
                'nama' => $user->nama,
                'daerah' => $user->daerah,
                'wilayah' => $user->wilayah,
                'role_name' => $user->role_label,
                'jwt_token' => \App\Services\SimpleJwtService::encode($payload)
            ];
        }

        return response()->json([
            'status' => 'success',
            'count' => count($tokens),
            'data' => $tokens
        ]);
    }

    /**
     * Get the token array structure.
     */
    protected function respondWithToken($token)
    {
        return response()->json([
            'status' => 'success',
            'data' => [
                'access_token' => $token,
                'token_type' => 'bearer',
                'expires_in' => 3600 * 24 * 30
            ]
        ]);
    }
}
