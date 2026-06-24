<?php

namespace App\Services;

use Illuminate\Contracts\Auth\Guard;
use Illuminate\Contracts\Auth\UserProvider;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Http\Request;

class SimpleJwtGuard implements Guard
{
    protected $provider;
    protected $request;
    protected $user;

    public function __construct(UserProvider $provider, Request $request)
    {
        $this->provider = $provider;
        $this->request = $request;
    }

    public function check()
    {
        return !is_null($this->user());
    }

    public function guest()
    {
        return !$this->check();
    }

    public function user()
    {
        if (!is_null($this->user)) {
            return $this->user;
        }

        $token = $this->request->bearerToken();
        if (!$token) {
            return null;
        }

        $payload = SimpleJwtService::decode($token);
        if (!$payload || !isset($payload['sub'])) {
            return null;
        }

        $user = $this->provider->retrieveById($payload['sub']);
        if ($user) {
            $this->user = $user;
        }

        return $this->user;
    }

    public function id()
    {
        if ($user = $this->user()) {
            return $user->getAuthIdentifier();
        }
    }

    public function validate(array $credentials = [])
    {
        if (empty($credentials['username']) || empty($credentials['password'])) {
            return false;
        }

        $user = $this->provider->retrieveByCredentials($credentials);
        return $user && $this->provider->validateCredentials($user, $credentials);
    }

    public function hasUser()
    {
        return !is_null($this->user);
    }

    public function setUser(Authenticatable $user)
    {
        $this->user = $user;
    }

    public function login(Authenticatable $user)
    {
        $this->setUser($user);
        
        $payload = [
            'sub' => $user->getAuthIdentifier(),
            'username' => $user->username ?? null,
            'nama' => $user->nama ?? null,
            'daerah' => $user->daerah ?? null,
            'wilayah' => $user->wilayah ?? null,
            'role_id' => $user->user_role_id ?? null,
            'iat' => time(),
            'exp' => time() + (3600 * 24 * 30), // 30 days
        ];

        return SimpleJwtService::encode($payload);
    }

    public function logout()
    {
        $this->user = null;
    }

    public function refresh()
    {
        $token = $this->request->bearerToken();
        if (!$token) {
            throw new \Exception('Token not provided');
        }

        $payload = SimpleJwtService::decode($token);
        if (!$payload || !isset($payload['sub'])) {
            throw new \Exception('Invalid token');
        }

        $user = $this->provider->retrieveById($payload['sub']);
        if (!$user) {
            throw new \Exception('User not found');
        }

        return $this->login($user);
    }
}
