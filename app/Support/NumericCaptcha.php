<?php

namespace App\Support;

use Illuminate\Support\Facades\Session;

final class NumericCaptcha
{
    public const SESSION_LOGIN = 'auth.numeric_captcha.login';

    public const SESSION_REGISTER = 'auth.numeric_captcha.register';

    /**
     * Simpan jawaban yang benar di session dan kembalikan bilangan untuk ditampilkan di form.
     *
     * @return array{a: int, b: int}
     */
    public static function refresh(string $sessionKey): array
    {
        $a = random_int(1, 12);
        $b = random_int(1, 12);
        Session::put($sessionKey, $a + $b);

        return ['a' => $a, 'b' => $b];
    }
}
