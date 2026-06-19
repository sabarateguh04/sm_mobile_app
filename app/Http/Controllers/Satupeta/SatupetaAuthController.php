<?php

namespace App\Http\Controllers\Satupeta;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\View\View;

class SatupetaAuthController extends BaseSatupetaController
{
    public function index(): View
    {
        return view('satupeta.auth.login');
    }

    public function prosesLogin(Request $request): RedirectResponse
    {
        $user = \Illuminate\Support\Facades\DB::connection('satupeta')
            ->table('core_user as a')
            ->join('persons as p', 'p.nrp', '=', 'a.uid')
            ->join('polda as da', 'p.polda', '=', 'da.da_id')
            ->where('a.uid', $request->input('username'))
            ->where('a.upwd', md5((string) $request->input('password')))
            ->select('p.rowid', 'a.uid', 'a.upwd', 'p.nama', 'p.pangkat', 'p.polda', 'p.polres', 'da.lat', 'da.lng')
            ->first();

        if ($user) {
            $this->setSatupetaSession([
                'id' => $user->rowid,
                'nama' => $user->nama,
                'pangkat' => $user->pangkat,
                'polda' => $user->polda,
                'polres' => $user->polres,
                'lat' => $user->lat,
                'lng' => $user->lng,
                'kode_user_master' => (string) $user->rowid,
            ]);

            return $this->redirectToPeta();
        }

        return redirect()->route('satupeta.auth.login')->with('gagal', 'Username atau Password salah.');
    }

    public function prosesLoginApi(Request $request): RedirectResponse
    {
        $response = Http::withHeaders([
            'Content-Type' => 'application/json',
            'token' => $this->apiToken(),
            'Accept-Language' => '*',
        ])->withOptions(['verify' => false])
            ->post($this->apiBaseUrl() . 'login_v2', [
                'username' => $request->input('username'),
                'password' => $request->input('password'),
                'kode_app' => 'SM',
            ]);

        $data = $response->json();

        if (($data['status'] ?? false) === true) {
            $u = $data['data'];
            $this->setSatupetaSession([
                'kode_user_master' => $u['kode_user_master'],
                'nama' => $u['nama'],
                'polda' => $u['polda_id'],
                'polres' => $u['polres_id'],
                'provinsi' => $u['provinsi'] ?? '',
                'lat' => $u['lat'],
                'lng' => $u['lng'],
            ]);

            return $this->redirectToPeta();
        }

        return redirect()->route('satupeta.auth.login')->with('gagal', $data['msg'] ?? 'Login gagal');
    }

    public function prosesLoginSso(Request $request, string $kode = '', string $bearer = ''): RedirectResponse|View
    {
        if ($kode === '') {
            return redirect()->route('satupeta.auth.login');
        }

        return view('satupeta.auth.sso', [
            'url_ci' => rtrim(url('/'), '/') . '/',
            'kode_login' => $kode,
            'bearer' => $bearer,
            'filter' => ltrim($this->ssoFilterQuery($request), '&'),
        ]);
    }

    public function loginSso(Request $request, string $kode = '', string $bearer = '', string $urlN = ''): RedirectResponse
    {
        $filter = $this->ssoFilterQuery($request);
        $token = $this->refreshToken($bearer);

        if (! ($token->status ?? false)) {
            return redirect((string) config('satupeta.url_bo', '/'))
                ->with('gagal', $token->msg ?? 'Token gagal');
        }

        $response = Http::withHeaders([
            'Content-Type' => 'application/json',
            'token' => $this->apiToken(),
            'secondbearer' => $token->data->second_bearer ?? '',
            'Accept-Language' => '*',
        ])->withOptions(['verify' => false])
            ->post($this->apiBaseUrl() . 'login_sso', [
                'kode_login' => $kode,
            ]);

        $data = $response->json();

        if (($data['status'] ?? false) === true) {
            $u = $data['data'][0]['data_login'];
            $this->setSatupetaSession([
                'kode_user_master' => $u['kode_user_master'],
                'nama' => $u['nama'],
                'polda' => $u['polda_id'],
                'polres' => $u['polres_id'],
                'provinsi' => $u['provinsi'] ?? '',
                'kota' => $u['kota'] ?? '',
                'lat' => $u['lat'],
                'lng' => $u['lng'],
                'kode_login' => $u['kode_login'],
                'token_bearer' => $data['data'][0]['token_bearer'] ?? '',
                'id_unique' => $u['id_unique'] ?? '',
            ]);

            return $this->redirectToPeta(ltrim($filter, '&'));
        }

        return redirect((string) config('satupeta.url_bo', '/'))
            ->with('gagal', $data['msg'] ?? 'SSO gagal');
    }

    public function rToken(Request $request, string $bearer = '', string $out = '')
    {
        return $this->refreshToken($bearer, $out === 'Y');
    }

    public function logout(): RedirectResponse
    {
        $request = request();
        $request->session()->forget($this->sessionKeys());
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect((string) config('satupeta.url_bo', '/'));
    }

    public function logoutSso(string $code = '', string $bearer = ''): RedirectResponse
    {
        $token = $this->refreshToken($bearer);

        if ($token->status ?? false) {
            Http::withHeaders([
                'Content-Type' => 'application/json',
                'token' => $this->apiToken(),
                'secondbearer' => $token->data->second_bearer ?? '',
                'Accept-Language' => '*',
            ])->withOptions(['verify' => false])
                ->send('DELETE', $this->apiBaseUrl() . 'sso_check/' . $code);
        }

        request()->session()->forget($this->sessionKeys());
        request()->session()->invalidate();

        return redirect((string) config('satupeta.url_bo', '/'));
    }

    public function logoutSsoSm(string $code = ''): RedirectResponse
    {
        Http::withHeaders([
            'secondbearer' => (string) config('satupeta.s_bearer'),
        ])->withOptions(['verify' => false])
            ->send('DELETE', $this->apiBaseUrl() . 'sso_check/' . $code);

        request()->session()->forget($this->sessionKeys());
        request()->session()->invalidate();

        return redirect((string) config('satupeta.url_bo', '/'));
    }

    private function ssoFilterQuery(Request $request): string
    {
        $filter = '';
        $params = ['display', 'btn_out', 'lokasi', 'center', 'car', 'officer', 'giat', 'giat_m', 'ops', 'ctg'];

        foreach ($params as $param) {
            $value = $request->query($param);
            if ($value !== null && $value !== '') {
                $filter .= '&' . $param . '=' . $value;
            }
        }

        return $filter;
    }

    private function sessionKeys(): array
    {
        return [
            'id', 'nama', 'pangkat', 'polda', 'polres', 'provinsi', 'kota', 'lat', 'lng',
            'kode_user_master', 'kode_login', 'token_bearer', 'id_unique',
        ];
    }

    private function setSatupetaSession(array $data): void
    {
        session($data);
    }
}
