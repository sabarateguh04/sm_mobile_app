<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Support\NumericCaptcha;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class NumericCaptchaRefreshController extends Controller
{
    public function refreshMath(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'scope' => 'required|string|in:login,register',
        ]);

        $key = $validated['scope'] === 'login'
            ? NumericCaptcha::SESSION_LOGIN
            : NumericCaptcha::SESSION_REGISTER;

        $pair = NumericCaptcha::refresh($key);

        return response()->json([
            'left' => $pair['a'],
            'right' => $pair['b'],
        ]);
    }
}
