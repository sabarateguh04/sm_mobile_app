<?php
require __DIR__.'/../bootstrap/app.php';

$app = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$user = auth()->user();
header('Content-Type: application/json');
if ($user) {
    echo json_encode([
        'logged_in' => true,
        'username' => $user->username,
        'nama' => $user->nama,
        'daerah' => $user->daerah,
        'wilayah' => $user->wilayah,
        'user_role_id' => $user->user_role_id,
        'bypass' => \App\Helpers\HierarchicalHelper::viewerBypassesTerritoryScope(),
        'level' => \App\Helpers\HierarchicalHelper::getUserLevel(),
    ], JSON_PRETTY_PRINT);
} else {
    echo json_encode(['logged_in' => false], JSON_PRETTY_PRINT);
}
