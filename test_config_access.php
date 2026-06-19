<?php
require __DIR__.'/vendor/autoload.php';
$app = require __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$dot = config('role_menu_matrix.menu_access.admin/users');
$arr = config('role_menu_matrix.menu_access');
$key = is_array($arr) ? ($arr['admin/users'] ?? 'MISSING') : 'NO_ARR';

echo "dot notation: ";
var_export($dot);
echo "\narray key: ";
var_export($key);
echo "\n";
