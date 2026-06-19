<?php

namespace App\Http\Controllers;

use App\Models\Usm;
use App\Support\LegacyUsmDbInput;
use App\Support\UsmMasterReferensi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

/**
 * Profil akun login (Usm): data pendaftaran, foto, password — username & role tidak diubah di sini.
 */
class ProfileController extends Controller
{
    protected function profileUsesKlDaerahMaster(Usm $user): bool
    {
        return UsmMasterReferensi::masterKlTigaTingkatAktif();
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function normalizeLegacyUsmOptionalStrings(array $data): array
    {
        foreach (['notelp'] as $key) {
            if (array_key_exists($key, $data) && $data[$key] === null) {
                $data[$key] = '';
            }
        }

        return $data;
    }

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

    /**
     * @return array{user: Usm, klForm: bool, territori: array<string, mixed>, kepegawaian: array<string, mixed>}
     */
    protected function profilePageContext(): array
    {
        $user = Auth::user();
        if (! $user instanceof Usm) {
            abort(403);
        }

        $user->loadMissing('role');
        $klForm = $this->profileUsesKlDaerahMaster($user);
        $territori = UsmMasterReferensi::wilayahDaerahMasterBundle();
        $kepegawaian = $klForm
            ? UsmMasterReferensi::registrationKepegawaianFormBundle($user)
            : UsmMasterReferensi::kepegawaianFormBundle($user);

        return compact('user', 'klForm', 'territori', 'kepegawaian');
    }

    public function show()
    {
        ['user' => $user, 'klForm' => $klForm, 'territori' => $territori, 'kepegawaian' => $kepegawaian] = $this->profilePageContext();

        return view('profile.show', array_merge([
            'user' => $user,
            'pageTitle' => 'Profil',
            'userFormPakaiMasterKl' => $klForm,
            'kepegawaianKlTigaTingkat' => $kepegawaian['kepegawaianKlTigaTingkat'] ?? false,
            'kepegawaianKlEmpatTingkat' => $kepegawaian['kepegawaianKlEmpatTingkat'] ?? false,
        ], $territori));
    }

    public function edit()
    {
        ['user' => $user, 'klForm' => $klForm, 'territori' => $territori, 'kepegawaian' => $kepegawaian] = $this->profilePageContext();

        return view('profile.edit', array_merge([
            'user' => $user,
            'pageTitle' => 'Edit Profil',
            'myProfileMode' => true,
            'roles' => collect(),
            'wilayahPakaiMasterTerritori' => $territori['wilayahPakaiMasterTerritori'] ?? false,
            'wilayahPakaiMasterKlDaerah' => UsmMasterReferensi::registrationPakaiMasterKlDaerah(),
            'userFormPakaiMasterKl' => $klForm,
        ], $territori, $kepegawaian));
    }

    public function update(Request $request)
    {
        $user = Auth::user();
        if (! $user instanceof Usm) {
            abort(403);
        }

        $klForm = $this->profileUsesKlDaerahMaster($user);

        if (! $request->filled('daerah')) {
            $request->merge(['daerah' => '', 'wilayah' => '']);
        }

        $rules = array_merge(
            UsmMasterReferensi::daerahWilayahValidationRules(),
            $klForm ? UsmMasterReferensi::registrationKepegawaianValidationRules() : UsmMasterReferensi::kepegawaianValidationRules(),
            [
                'nama' => ['required', 'string', 'max:255'],
                'email' => ['required', 'string', 'email', 'max:255', Rule::unique('usm', 'email')->ignore($user->getKey(), 'id_usm')],
                'notelp' => 'nullable|string|max:255',
                'fotoprof' => ['nullable', 'image', 'max:2048'],
                'password' => ['nullable', 'string', 'min:6', 'confirmed'],
                'password_current' => ['nullable', 'string'],
            ]
        );

        $validated = $request->validate($rules, [
            'password.min' => 'Password baru minimal 6 karakter.',
        ], ['daerah' => 'provinsi', 'wilayah' => 'wilayah']);

        if (! empty(trim((string) ($validated['password'] ?? '')))) {
            $current = trim((string) ($request->input('password_current') ?? ''));
            if ($current === '') {
                throw ValidationException::withMessages([
                    'password_current' => ['Masukkan password saat ini untuk mengganti password.'],
                ]);
            }
            if (! Hash::check($current, $user->password)) {
                throw ValidationException::withMessages([
                    'password_current' => ['Password saat ini tidak sesuai.'],
                ]);
            }
        }

        unset($validated['password_current']);

        if ($klForm) {
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

        if ($request->hasFile('fotoprof')) {
            if ($user->fotoprof) {
                Storage::disk('public')->delete($user->fotoprof);
            }
            $validated['fotoprof'] = $request->file('fotoprof')->store('profiles', 'public');
        }

        if (! empty(trim((string) ($validated['password'] ?? '')))) {
            $validated['password'] = Hash::make($validated['password']);
        } else {
            unset($validated['password']);
        }

        unset($validated['password_confirmation']);

        $validated = $this->normalizeLegacyUsmOptionalStrings($validated);

        UsmMasterReferensi::assertTerritorialStaffRoleHasWilayahKerja(
            isset($user->user_role_id) ? (int) $user->user_role_id : null,
            $validated['daerah'] !== '' ? $validated['daerah'] : null,
            $validated['wilayah'] !== '' ? $validated['wilayah'] : null,
            false
        );

        /** @var array<string, mixed> $payload */
        $payload = LegacyUsmDbInput::normalizeOptionalTextFields(
            collect($validated)->except(['username', 'user_role_id', 'account_status'])->all()
        );

        foreach (array_keys($payload) as $k) {
            if (! Schema::hasColumn('usm', $k)) {
                unset($payload[$k]);
            }
        }

        $user->fill($payload);
        $user->save();

        return redirect()->route('profile.show')
            ->with('success', 'Profil berhasil disimpan.');
    }
}
