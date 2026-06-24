<?php
/**
 * Script untuk mencari tabel CCTV di database test
 */

require __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "<h2>Mencari Tabel CCTV di Database</h2>\n";
echo "<hr>\n";

try {
    // Cari di database satupeta (test)
    echo "<h3>Database: test (koneksi 'satupeta')</h3>\n";
    $tables = DB::connection('satupeta')->select('SHOW TABLES');
    
    echo "<h4>Tabel yang mengandung kata 'cctv' (case-insensitive):</h4>\n";
    $cctvTables = [];
    foreach ($tables as $table) {
        $tableName = current((array) $table);
        if (stripos($tableName, 'cctv') !== false) {
            $cctvTables[] = $tableName;
            echo "✓ <strong>{$tableName}</strong><br>\n";
        }
    }
    
    if (empty($cctvTables)) {
        echo "✗ Tidak ada tabel dengan kata 'cctv'<br>\n";
    }
    
    // Cari tabel yang mirip dengan 'data' atau 'monitoring'
    echo "<h4>Tabel yang mengandung kata 'data' atau 'monitoring':</h4>\n";
    $dataTables = [];
    foreach ($tables as $table) {
        $tableName = current((array) $table);
        if (stripos($tableName, 'data') !== false || stripos($tableName, 'monitoring') !== false) {
            $dataTables[] = $tableName;
            echo "- {$tableName}<br>\n";
        }
    }
    
    // Cari dengan prefix smpc_
    echo "<h4>Tabel dengan prefix 'smpc_':</h4>\n";
    $smpcTables = [];
    foreach ($tables as $table) {
        $tableName = current((array) $table);
        if (stripos($tableName, 'smpc_') === 0) {
            $smpcTables[] = $tableName;
            echo "- {$tableName}<br>\n";
        }
    }
    
    if (empty($smpcTables)) {
        echo "✗ Tidak ada tabel dengan prefix 'smpc_'<br>\n";
    }
    
    // List semua tabel
    echo "<hr>\n";
    echo "<h3>Daftar SEMUA Tabel di Database 'test'</h3>\n";
    echo "<ol>\n";
    foreach ($tables as $table) {
        $tableName = current((array) $table);
        echo "<li>{$tableName}</li>\n";
    }
    echo "</ol>\n";
    
    // Cek di database utama (sql_103_114_110_66)
    echo "<hr>\n";
    echo "<h3>Cek Database Utama (mysql)</h3>\n";
    $dbName = DB::connection('mysql')->getDatabaseName();
    echo "Database: <strong>{$dbName}</strong><br>\n";
    
    $mainTables = DB::connection('mysql')->select('SHOW TABLES');
    $mainCctvTables = [];
    foreach ($mainTables as $table) {
        $tableName = current((array) $table);
        if (stripos($tableName, 'cctv') !== false || stripos($tableName, 'smpc_') === 0) {
            $mainCctvTables[] = $tableName;
        }
    }
    
    if (!empty($mainCctvTables)) {
        echo "<h4>Tabel CCTV/SMPC di database utama:</h4>\n";
        foreach ($mainCctvTables as $tableName) {
            echo "- {$tableName}<br>\n";
            
            // Cek jumlah data
            $count = DB::connection('mysql')->table($tableName)->count();
            echo "  → Total: {$count} records<br>\n";
        }
    } else {
        echo "✗ Tidak ada tabel CCTV/SMPC di database utama<br>\n";
    }
    
} catch (\Exception $e) {
    echo "<div style='color: red; padding: 10px; border: 1px solid red; background-color: #ffe6e6;'>\n";
    echo "<strong>ERROR:</strong><br>\n";
    echo htmlspecialchars($e->getMessage()) . "<br>\n";
    echo "</div>\n";
}

echo "<hr>\n";
echo "<p><strong>Kesimpulan:</strong> Tolong konfirmasi nama tabel CCTV yang sebenarnya ada di database.</p>\n";
echo "<p><a href='find_cctv_tables.php'>Refresh</a> | <a href='/'>Home</a></p>\n";
