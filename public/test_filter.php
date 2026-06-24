<?php
require __DIR__.'/../bootstrap/app.php';

$app = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

// Let's mock a user with role ID 6 (Executive 2)
// Let's find a user with user_role_id = 6
$user = \DB::table('usm')->where('user_role_id', 6)->first();

if (!$user) {
    // If no role 6 user, let's find one or create a dummy object
    $user = (object)[
        'id_usm' => 9999,
        'username' => 'mock_executive_2',
        'nama' => 'Mock Executive 2',
        'daerah' => 'JAWA BARAT',
        'wilayah' => null,
        'user_role_id' => 6
    ];
}

// Log in the user
auth()->loginUsingId($user->id_usm);

// Let's print out what is returned by canFilterByDaerah, canFilterByWilayah, etc.
header('Content-Type: application/json');
echo json_encode([
    'user' => [
        'username' => $user->username,
        'daerah' => $user->daerah,
        'wilayah' => $user->wilayah,
        'user_role_id' => $user->user_role_id,
    ],
    'canFilterByDaerah' => \App\Support\TerritoryIndexFilters::canFilterByDaerah(),
    'canFilterByWilayah' => \App\Support\TerritoryIndexFilters::canFilterByWilayah(),
    'daerahOptions' => \App\Support\TerritoryIndexFilters::daerahOptions()->toArray(),
    'wilayahOptions' => \App\Support\TerritoryIndexFilters::wilayahOptions(request())->toArray(),
], JSON_PRETTY_PRINT);
