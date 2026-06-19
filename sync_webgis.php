<?php

$source_dir = __DIR__ . '/project/project';
$frontend_dir = $source_dir . '/frontend/svc-sm-map-webgis';
$backend_dir = $source_dir . '/backend/svc-sm-scheduler-webgis';

if (!is_dir($frontend_dir)) {
    die("Error: Direktori frontend tidak ditemukan di $frontend_dir\n");
}

echo "Memulai sinkronisasi WebGIS...\n";

// 1. Copy index.html
$html_content = file_get_contents($frontend_dir . '/index.html');

// Fix ALL asset and data paths using str_replace for reliability
$prefixes = ['\'', '"', '`'];
foreach ($prefixes as $p) {
    // Assets
    $html_content = str_replace($p . 'assets/', $p . '{{ asset(\'satupeta_webgis\') }}/assets/', $html_content);
    $html_content = str_replace($p . './assets/', $p . '{{ asset(\'satupeta_webgis\') }}/assets/', $html_content);
    
    // Data
    $html_content = str_replace($p . 'data/', $p . '{{ asset(\'satupeta_webgis\') }}/data/', $html_content);
    $html_content = str_replace($p . './data/', $p . '{{ asset(\'satupeta_webgis\') }}/data/', $html_content);
}

// geojson direct files
$html_content = preg_replace('/([\'"\`])(?!\{\{)(\.\/)?([a-zA-Z0-9_\-\/]+\.geojson)\1/', '$1{{ asset(\'satupeta_webgis\') }}/$4$1', $html_content);

// Re-inject Laravel Blade Variables so the backend connection doesn't break
$laravel_vars = <<<EOD
<!-- Injected Laravel Auth & Variables -->
<script>
    window.id_unique = '{{ \$id_unique ?? '' }}';
    window.tber = '{{ \$token_bearer ?? '' }}';
    window.user_lat = '{{ \$user_lat ?? '' }}';
    window.user_lng = '{{ \$user_lng ?? '' }}';
    window.polda_id = '{{ \$polda_id ?? '' }}';
    window.polres_id = '{{ \$polres_id ?? '' }}';
</script>
EOD;

$html_content = str_replace('</body>', $laravel_vars . "\n</body>", $html_content);

// Restore original blade if we had backed it up
$old_blade = __DIR__ . '/resources/views/satupeta/satupeta_baru.blade.php';

// Write new blade
file_put_contents($old_blade, $html_content);
echo ">> Update satupeta_baru.blade.php dari index.html selesai.\n";

// 2. Helper function to copy folders recursively
function recurse_copy($src, $dst) { 
    $dir = opendir($src); 
    @mkdir($dst, 0777, true); 
    while(false !== ( $file = readdir($dir)) ) { 
        if (( $file != '.' ) && ( $file != '..' )) { 
            if ( is_dir($src . '/' . $file) ) { 
                recurse_copy($src . '/' . $file, $dst . '/' . $file); 
            } 
            else { 
                copy($src . '/' . $file, $dst . '/' . $file); 
            } 
        } 
    } 
    closedir($dir); 
}

// 3. Copy frontend assets and data
echo ">> Menyalin assets dan data ke public/satupeta_webgis...\n";
recurse_copy($frontend_dir . '/assets', __DIR__ . '/public/satupeta_webgis/assets');
if (is_dir($frontend_dir . '/data')) {
    recurse_copy($frontend_dir . '/data', __DIR__ . '/public/satupeta_webgis/data');
}

// Copy geojson files
foreach (glob($frontend_dir . '/*.geojson') as $file) {
    copy($file, __DIR__ . '/public/satupeta_webgis/' . basename($file));
}

// 4. Copy backend python scripts to scripts/scheduler-webgis
if (is_dir($backend_dir)) {
    echo ">> Menyalin script backend python ke scripts/scheduler-webgis...\n";
    recurse_copy($backend_dir, __DIR__ . '/scripts/scheduler-webgis');
}

echo "\nPerbaikan Sinkronisasi WebGIS Map berhasil diselesaikan!\n";
echo "Silakan akses halaman peta (misalnya /PemetaanNew) di browser untuk melihat perubahan.\n";
