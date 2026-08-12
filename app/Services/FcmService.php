<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class FcmService
{
    /**
     * Send Push Notification using Firebase Cloud Messaging
     * 
     * @param int|array $userIds User ID(s) to send notification to
     * @param string $title Notification Title
     * @param string $body Notification Body
     * @param array $data Additional Data payload
     * @return bool
     */
    public static function send($userIds, $title, $body, $data = [])
    {
        if (!is_array($userIds)) {
            $userIds = [$userIds];
        }

        // Get FCM Tokens for the users
        $tokens = DB::table('petugas_tracking')
            ->whereIn('user_id', $userIds)
            ->whereNotNull('fcm_token')
            ->where('fcm_token', '!=', '')
            ->pluck('fcm_token')
            ->toArray();

        if (empty($tokens)) {
            Log::info('FCM Send: No tokens found for users', ['users' => $userIds]);
            return false;
        }

        // Path ke file Firebase Service Account JSON (wajib untuk FCM v1 API)
        $credentialsFilePath = app_path('firebase-service-account.json');
        
        if (!file_exists($credentialsFilePath)) {
            Log::error('FCM Send Error: File credentials Firebase tidak ditemukan di ' . $credentialsFilePath);
            return false;
        }

        try {
            // Generate OAuth2 token menggunakan Google API Client
            $client = new \Google\Client();
            $client->setAuthConfig($credentialsFilePath);
            $client->addScope('https://www.googleapis.com/auth/firebase.messaging');
            $client->fetchAccessTokenWithAssertion();
            
            $accessToken = $client->getAccessToken();
            if (!isset($accessToken['access_token'])) {
                Log::error('FCM Send Error: Gagal mendapatkan access token.');
                return false;
            }
            
            $bearerToken = $accessToken['access_token'];
            
            // Baca Project ID dari JSON file
            $credentials = json_decode(file_get_contents($credentialsFilePath), true);
            $projectId = $credentials['project_id'] ?? 'sm-mobile-djalu';

            $successCount = 0;

            foreach ($tokens as $token) {
                // Pastikan semua value di dalam 'data' adalah string (Syarat mutlak HTTP v1 API)
                $stringData = [];
                foreach ($data as $key => $val) {
                    $stringData[$key] = (string)$val;
                }

                // Format payload baru (HTTP v1 API)
                $payload = [
                    'message' => [
                        'token' => $token,
                        'notification' => [
                            'title' => $title,
                            'body' => $body,
                        ],
                        'data' => $stringData,
                        'android' => [
                            'notification' => [
                                'sound' => 'default'
                            ]
                        ]
                    ]
                ];

                $response = Http::withHeaders([
                    'Authorization' => 'Bearer ' . $bearerToken,
                    'Content-Type' => 'application/json',
                ])->post("https://fcm.googleapis.com/v1/projects/{$projectId}/messages:send", $payload);

                if ($response->successful()) {
                    $successCount++;
                } else {
                    Log::error('FCM Send Error: ' . $response->body());
                }
            }

            return $successCount > 0;
            
        } catch (\Exception $e) {
            Log::error('FCM Exception: ' . $e->getMessage());
            return false;
        }
    }
}
