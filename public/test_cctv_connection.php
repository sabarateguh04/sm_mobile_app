<?php
/**
 * Script test koneksi database SatuPeta CCTV
 * Database: test
 * Table: smpc_tbl_data_cctv
 */

require __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "<h2>Test Koneksi Database SatuPeta CCTV</h2>\n";
echo "<hr>\n";

try {
    // Test koneksi ke database 'satupeta'
    echo "<h3>1. Test Koneksi Database 'satupeta'</h3>\n";
    $connection = DB::connection('satupeta');
    $dbName = $connection->getDatabaseName();
    echo "✓ Koneksi berhasil ke database: <strong>{$dbName}</strong><br>\n";
    
    // Cek tabel smpc_tbl_data_cctv
    echo "<h3>2. Cek Tabel smpc_tbl_data_cctv</h3>\n";
    $tableExists = DB::connection('satupeta')
        ->select("SHOW TABLES LIKE 'smpc_tbl_data_cctv'");
    
    if (empty($tableExists)) {
        echo "✗ Tabel 'smpc_tbl_data_cctv' TIDAK DITEMUKAN<br>\n";
        echo "<br>Daftar tabel yang tersedia:<br>\n";
        $tables = DB::connection('satupeta')->select('SHOW TABLES');
        echo "<pre>";
        print_r($tables);
        echo "</pre>";
    } else {
        echo "✓ Tabel 'smpc_tbl_data_cctv' ditemukan<br>\n";
        
        // Cek struktur tabel
        echo "<h3>3. Struktur Tabel smpc_tbl_data_cctv</h3>\n";
        $columns = DB::connection('satupeta')->select('DESCRIBE smpc_tbl_data_cctv');
        echo "<pre>";
        print_r($columns);
        echo "</pre>";
        
        // Hitung jumlah data
        echo "<h3>4. Jumlah Data</h3>\n";
        $count = DB::connection('satupeta')
            ->table('smpc_tbl_data_cctv')
            ->count();
        echo "Total record: <strong>{$count}</strong><br>\n";
        
        // Ambil 5 data pertama
        if ($count > 0) {
            echo "<h3>5. Sample Data (5 record pertama)</h3>\n";
            $samples = DB::connection('satupeta')
                ->table('smpc_tbl_data_cctv')
                ->limit(5)
                ->get();
            
            echo "<table border='1' cellpadding='5' cellspacing='0' style='border-collapse: collapse;'>\n";
            
            // Header
            if ($samples->count() > 0) {
                $firstRow = (array) $samples->first();
                echo "<tr style='background-color: #f0f0f0;'>";
                foreach (array_keys($firstRow) as $column) {
                    echo "<th>{$column}</th>";
                }
                echo "</tr>\n";
                
                // Data rows
                foreach ($samples as $row) {
                    echo "<tr>";
                    foreach ((array) $row as $value) {
                        $displayValue = $value === null ? '<em>NULL</em>' : htmlspecialchars(substr((string)$value, 0, 100));
                        echo "<td>{$displayValue}</td>";
                    }
                    echo "</tr>\n";
                }
            }
            echo "</table>\n";
        }
    }
    
    // Test koneksi alternatif mysql_second (DB_SECOND_*)
    echo "<hr>\n";
    echo "<h3>6. Test Koneksi Alternatif (mysql_second)</h3>\n";
    $connection2 = DB::connection('mysql_second');
    $dbName2 = $connection2->getDatabaseName();
    echo "✓ Koneksi berhasil ke database: <strong>{$dbName2}</strong><br>\n";
    
    $tableExists2 = DB::connection('mysql_second')
        ->select("SHOW TABLES LIKE 'smpc_tbl_data_cctv'");
    
    if (!empty($tableExists2)) {
        echo "✓ Tabel 'smpc_tbl_data_cctv' juga ditemukan di mysql_second<br>\n";
        $count2 = DB::connection('mysql_second')
            ->table('smpc_tbl_data_cctv')
            ->count();
        echo "Total record di mysql_second: <strong>{$count2}</strong><br>\n";
    } else {
        echo "✗ Tabel 'smpc_tbl_data_cctv' tidak ditemukan di mysql_second<br>\n";
    }
    
} catch (\Exception $e) {
    echo "<div style='color: red; padding: 10px; border: 1px solid red; background-color: #ffe6e6;'>\n";
    echo "<strong>ERROR:</strong><br>\n";
    echo htmlspecialchars($e->getMessage()) . "<br>\n";
    echo "<br><strong>Stack Trace:</strong><br>\n";
    echo "<pre>" . htmlspecialchars($e->getTraceAsString()) . "</pre>\n";
    echo "</div>\n";
}

echo "<hr>\n";
echo "<h3>Konfigurasi Environment (.env)</h3>\n";
echo "<pre>";
echo "DB_SECOND_DATABASE=" . env('DB_SECOND_DATABASE') . "\n";
echo "DB_SECOND_USERNAME=" . env('DB_SECOND_USERNAME') . "\n";
echo "DB_SECOND_PASSWORD=" . (env('DB_SECOND_PASSWORD') ? '***' : '(empty)') . "\n";
echo "\n";
echo "SATUPETA_DB_HOST=" . env('SATUPETA_DB_HOST') . "\n";
echo "SATUPETA_DB_PORT=" . env('SATUPETA_DB_PORT') . "\n";
echo "SATUPETA_DB_DATABASE=" . env('SATUPETA_DB_DATABASE') . "\n";
echo "SATUPETA_DB_USERNAME=" . env('SATUPETA_DB_USERNAME') . "\n";
echo "SATUPETA_DB_PASSWORD=" . (env('SATUPETA_DB_PASSWORD') ? '***' : '(empty)') . "\n";
echo "</pre>";

echo "<p><a href='test_cctv_connection.php'>Refresh</a> | <a href='/'>Home</a></p>\n";
