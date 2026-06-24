<?php
/**
 * Script untuk clear config cache dari browser
 * Akses via: https://103.114.110.67/clear_config.php
 */

// Clear config cache
exec('cd .. && php artisan config:clear 2>&1', $output1, $return1);

// Clear cache
exec('cd .. && php artisan cache:clear 2>&1', $output2, $return2);

// Optimize config (cache lagi dengan config baru)
exec('cd .. && php artisan config:cache 2>&1', $output3, $return3);

?>
<!DOCTYPE html>
<html>
<head>
    <title>Clear Config Cache</title>
    <style>
        body { font-family: monospace; padding: 20px; background: #f5f5f5; }
        .result { background: white; padding: 15px; margin: 10px 0; border-left: 4px solid #4CAF50; }
        .error { border-color: #f44336; }
        h2 { color: #333; }
        pre { background: #f9f9f9; padding: 10px; overflow-x: auto; }
    </style>
</head>
<body>
    <h1>🔄 Clear Config Cache</h1>
    
    <div class="result <?= $return1 !== 0 ? 'error' : '' ?>">
        <h2>1. Config Clear (status: <?= $return1 === 0 ? '✅ Success' : '❌ Failed' ?>)</h2>
        <pre><?= implode("\n", $output1) ?: 'No output' ?></pre>
    </div>

    <div class="result <?= $return2 !== 0 ? 'error' : '' ?>">
        <h2>2. Cache Clear (status: <?= $return2 === 0 ? '✅ Success' : '❌ Failed' ?>)</h2>
        <pre><?= implode("\n", $output2) ?: 'No output' ?></pre>
    </div>

    <div class="result <?= $return3 !== 0 ? 'error' : '' ?>">
        <h2>3. Config Cache (status: <?= $return3 === 0 ? '✅ Success' : '❌ Failed' ?>)</h2>
        <pre><?= implode("\n", $output3) ?: 'No output' ?></pre>
    </div>

    <hr>
    <p><strong>✅ Config telah di-clear dan di-cache ulang!</strong></p>
    <p>DB_TERRITORY_CONNECTION sekarang menggunakan: <strong>mysql</strong> (bukan mysql_second)</p>
    <p><a href="/">← Kembali ke Home</a></p>
</body>
</html>
