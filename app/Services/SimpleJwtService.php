<?php

namespace App\Services;

class SimpleJwtService
{
    private static function getSecret()
    {
        return env('JWT_SECRET') ?: (config('app.key') ?: 'base64:somefallbackkey');
    }

    private static function base64UrlEncode($data)
    {
        return str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($data));
    }

    private static function base64UrlDecode($data)
    {
        $remainder = strlen($data) % 4;
        if ($remainder) {
            $data .= str_repeat('=', 4 - $remainder);
        }
        return base64_decode(str_replace(['-', '_'], ['+', '/'], $data));
    }

    public static function encode(array $payload)
    {
        $header = json_encode(['alg' => 'HS256', 'typ' => 'JWT']);
        $headerEncoded = self::base64UrlEncode($header);
        
        $payloadEncoded = self::base64UrlEncode(json_encode($payload));
        
        $signature = hash_hmac('sha256', "$headerEncoded.$payloadEncoded", self::getSecret(), true);
        $signatureEncoded = self::base64UrlEncode($signature);
        
        return "$headerEncoded.$payloadEncoded.$signatureEncoded";
    }

    public static function decode(string $token)
    {
        $parts = explode('.', $token);
        if (count($parts) !== 3) {
            return null;
        }

        list($headerEncoded, $payloadEncoded, $signatureEncoded) = $parts;
        
        $signature = self::base64UrlDecode($signatureEncoded);
        $expectedSignature = hash_hmac('sha256', "$headerEncoded.$payloadEncoded", self::getSecret(), true);
        
        if (!hash_equals($signature, $expectedSignature)) {
            return null;
        }

        $payload = json_decode(self::base64UrlDecode($payloadEncoded), true);
        if (!is_array($payload)) {
            return null;
        }

        // Check expiration
        if (isset($payload['exp']) && $payload['exp'] < time()) {
            return null;
        }

        return $payload;
    }
}
