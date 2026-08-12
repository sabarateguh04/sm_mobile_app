<?php
// public/test_sync.php
// Skrip ini mensimulasikan pengiriman data dari aplikasi Flutter ke backend Laravel.

$baseUrl = 'http://localhost:8000'; // Sesuaikan dengan URL lokal Anda
$userId = 1; // Ganti dengan ID user yang valid di database Anda

function sendRequest($url, $data) {
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    return ['code' => $httpCode, 'response' => $response];
}

echo "--- Memulai Pengujian Sinkronisasi ---\n";

// 1. Uji Update Lokasi
$locData = [
    'userId' => $userId,
    'lat' => -6.200000,
    'lng' => 106.816666,
    'ctddate' => date('Y-m-d'),
    'ctdtime' => date('H:i:s'),
    'status' => 'ONLINE'
];
echo "Mengirim update lokasi...\n";
$resLoc = sendRequest("$baseUrl/api/update-location", $locData);
echo "Response Code: " . $resLoc['code'] . "\n";
echo "Response Body: " . $resLoc['response'] . "\n\n";

// 2. Uji Update Profil
$profData = [
    'userId' => $userId,
    'lat' => -6.200000,
    'lng' => 106.816666,
    'status' => 'BERTUGAS',
    'last_updated' => date('Y-m-d H:i:s')
];
echo "Mengirim update profil...\n";
$resProf = sendRequest("$baseUrl/api/update-profile", $profData);
echo "Response Code: " . $resProf['code'] . "\n";
echo "Response Body: " . $resProf['response'] . "\n";

echo "--- Pengujian Selesai ---\n";
