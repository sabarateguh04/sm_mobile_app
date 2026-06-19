<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Usm;
use App\Support\LegacyUsmDbInput;
use App\Support\NumericCaptcha;
use App\Support\UsmMasterReferensi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\ValidationException;

class RegisterController extends Controller
{
    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function sanitizeKepegawaianFields(array $data): array
    {
        foreach (['nrp_nip_nik', 'induk_pemerintahan', 'pemerintahan_daerah', 'organisasi_daerah', 'unit_kerja'] as $key) {
            if (! array_key_exists($key, $data)) {
                continue;
            }
            $data[$key] = LegacyUsmDbInput::stringOrEmpty($data[$key] ?? '');
        }
        if (array_key_exists('kategori_user', $data)) {
            $k = $data['kategori_user'];
            $data['kategori_user'] = ($k === null || $k === '') ? null : $k;
        }

        return $data;
    }

    public function showRegistrationForm(Request $request)
    {
        if (! config('registration.enabled', true)) {
            abort(404);
        }

        if (Auth::check()) {
            return redirect()->route('home');
        }

        $defaultRoleId = (int) config('registration.default_role_id', 4);
        $defaultRole = DB::table('roles')->where('role_id', $defaultRoleId)->first();

        $territori = UsmMasterReferensi::wilayahDaerahResortBundle();

        $preselect = UsmMasterReferensi::resolveTerritorialQuerySelection(
            $request->query('id_daerah'),
            $request->query('id_wilayah'),
            $request->query('daerah'),
            $request->query('wilayah'),
        );

        $registerUserStub = null;
        if ($preselect['daerah'] !== '' || $preselect['wilayah'] !== '') {
            $registerUserStub = (object) $preselect;
        }

        return view('auth.register', array_merge([
            'defaultRole' => $defaultRole,
            'defaultRoleId' => $defaultRoleId,
            'roles' => collect(),
            'wilayahPakaiMasterKlDaerah' => false,
            'registerUserStub' => $registerUserStub,
        ], $territori, UsmMasterReferensi::registrationKepegawaianFormBundle()));
    }

    public function register(Request $request)
    {
        if (! config('registration.enabled', true)) {
            abort(404);
        }

        $status = (string) config('registration.default_account_status', 'pending');
        if (! in_array($status, ['active', 'pending', 'blocked'], true)) {
            $status = 'pending';
        }

        $defaultRoleId = (int) config('registration.default_role_id', 4);

        $pakaiKl = UsmMasterReferensi::masterKlTigaTingkatAktif();

        if (! $request->filled('daerah')) {
            $request->merge(['daerah' => '', 'wilayah' => '']);
        }

        $rules = array_merge(
            UsmMasterReferensi::daerahWilayahResortValidationRules(),
            UsmMasterReferensi::registrationKepegawaianValidationRules(),
            [
                'username' => ['required', 'string', 'max:255', 'regex:/^\S+$/', Rule::unique('usm', 'username')],
                'email' => ['required', 'string', 'email', 'max:255', Rule::unique('usm', 'email')],
                'nama' => ['required', 'string', 'max:255'],
                'password' => ['required', 'string', 'confirmed', Password::min(6)],
                'notelp' => ['nullable', 'string', 'max:255'],
                'fotoprof' => ['nullable', 'image', 'max:2048'],
                'captcha' => [
                    'required',
                    'regex:/^[0-9]{1,4}$/',
                    function (string $attribute, mixed $value, \Closure $fail): void {
                        $expected = session(NumericCaptcha::SESSION_REGISTER);
                        if ($expected === null || ! is_numeric($expected)) {
                            $fail('Captcha kedaluwarsa. Silakan muat ulang halaman lalu coba lagi.');

                            return;
                        }
                        if ((int) $value !== (int) $expected) {
                            $fail('Jawaban verifikasi angka salah.');
                        }
                    },
                ],
            ]
        );

        $validated = $request->validate($rules, [
            'username.regex' => 'Username tidak boleh mengandung spasi.',
            'captcha.required' => 'Harap jawab pertanyaan verifikasi angka.',
            'captcha.regex' => 'Jawaban verifikasi hanya boleh angka (maks. 4 digit).',
        ], ['daerah' => 'provinsi', 'wilayah' => 'wilayah']);

        $request->session()->forget(NumericCaptcha::SESSION_REGISTER);

        $roleId = $defaultRoleId;
        if (! DB::table('roles')->where('role_id', $roleId)->exists()) {
            throw ValidationException::withMessages([
                'username' => ['Konfigurasi registrasi belum valid: role default (ID '.$defaultRoleId.') tidak ada di tabel roles. Hubungi administrator.'],
            ]);
        }

        unset($validated['password_confirmation'], $validated['captcha']);

        if ($pakaiKl) {
            UsmMasterReferensi::validasiKepegawaianMasterKl(
                trim((string) ($validated['induk_pemerintahan'] ?? '')),
                trim((string) ($validated['pemerintahan_daerah'] ?? '')),
                trim((string) ($validated['organisasi_daerah'] ?? '')),
                trim((string) ($validated['unit_kerja'] ?? ''))
            );
        } else {
            UsmMasterReferensi::validasiUnitKerjaTerhadapInduk(
                trim((string) ($validated['induk_pemerintahan'] ?? '')),
                trim((string) ($validated['unit_kerja'] ?? ''))
            );
        }

        $validated = $this->sanitizeKepegawaianFields($validated);

        $explicitTerritory = trim((string) ($validated['daerah'] ?? '')) !== ''
            || trim((string) ($validated['wilayah'] ?? '')) !== '';

        if ($explicitTerritory) {
            $daerah = trim((string) ($validated['daerah'] ?? ''));
            $wilayah = trim((string) ($validated['wilayah'] ?? ''));
        } else {
            $daerah = '';
            $wilayah = '';
        }

        $validated['daerah'] = $daerah;
        $validated['wilayah'] = $wilayah;

        UsmMasterReferensi::assertTerritorialStaffRoleHasWilayahKerja(
            $roleId,
            $validated['daerah'] !== '' ? $validated['daerah'] : null,
            $validated['wilayah'] !== '' ? $validated['wilayah'] : null,
            false
        );

        UsmMasterReferensi::assertTerritorialStaffSelfRegistrationHasKabupaten(
            $roleId,
            $validated['daerah'] !== '' ? $validated['daerah'] : null,
            $validated['wilayah'] !== '' ? $validated['wilayah'] : null,
            false
        );

        $fotoprofPath = null;
        if ($request->hasFile('fotoprof')) {
            $fotoprofPath = $request->file('fotoprof')->store('profiles', 'public');
        }

        $validated['password'] = Hash::make($validated['password']);
        $validated['account_status'] = $status;
        $validated['user_role_id'] = $roleId;

        $validated = LegacyUsmDbInput::normalizeOptionalTextFields($validated);

        /** @var array<string, mixed> $row */
        $row = collect($validated)->only([
            'username', 'email', 'password', 'nama', 'daerah', 'wilayah',
            'notelp', 'account_status', 'user_role_id',
            'nrp_nip_nik', 'induk_pemerintahan', 'pemerintahan_daerah', 'organisasi_daerah', 'unit_kerja', 'kategori_user',
        ])->all();

        $row['fotoprof'] = $fotoprofPath ?? '';

        if (Schema::hasColumn('usm', 'email_status')) {
            $row['email_status'] = '';
        }

        DB::transaction(function () use ($row): void {
            Usm::create($row);
        });

        $message = ($status === 'pending')
            ? 'Pendaftaran berhasil.'
            : 'Akun berhasil dibuat. Silakan login dengan username dan password Anda.';

        return redirect()
            ->route('login')
            ->with('registered', $message)
            ->with('registration_needs_activation', $status === 'pending');
    }
}
