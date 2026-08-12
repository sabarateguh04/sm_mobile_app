<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\Auth;
use App\Models\Usm;
use App\Support\CctvLiveRegistry;

// 1. Mock Surakarta operator (daerah = JAWA TENGAH, wilayah = SURAKARTA)
$user = new Usm();
$user->id_usm = 9999;
$user->nama = 'Operator Surakarta';
$user->daerah = 'JAWA TENGAH';
$user->wilayah = 'SURAKARTA';
$user->user_role_id = 3;
Auth::login($user);

echo "=== CCTV SCOPING TEST ===\n";
echo "User Daerah: " . $user->daerah . "\n";
echo "User Wilayah: " . $user->wilayah . "\n";

try {
    if (!CctvLiveRegistry::isAvailable()) {
        echo "CctvLiveRegistry is not available!\n";
        exit;
    }
    
    $cameras = CctvLiveRegistry::listCameras();
    echo "Total cameras returned: " . $cameras->count() . "\n";
    
    if ($cameras->count() > 0) {
        echo "Sample cameras:\n";
        foreach ($cameras->take(5) as $c) {
            echo "- ID: {$c->id}, Name: {$c->nama_cctv}, Polda ID: {$c->da_id}, Polres ID: " . ($c->polres_id ?? 'N/A') . ", Status: {$c->status}, LatLng: {$c->kordinat}\n";
        }
    } else {
        echo "No cameras found for Surakarta operator!\n";
    }
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
