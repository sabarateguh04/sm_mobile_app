<?php
$host = '127.0.0.1';
$db   = 'sql_103_114_110_66';
$user = 'sql_103_114_110_66';
$pass = 'sql_103_114_110_66';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $stmt = $pdo->query("SHOW TABLES");
    $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    $results = [];
    foreach ($tables as $table) {
        $stmt = $pdo->query("SHOW COLUMNS FROM `$table`");
        $columns = $stmt->fetchAll(PDO::FETCH_COLUMN);
        
        $where = [];
        foreach ($columns as $col) {
            $where[] = "`$col` LIKE '%surakarta%'";
        }
        
        if (count($where) > 0) {
            $sql = "SELECT * FROM `$table` WHERE " . implode(' OR ', $where) . " LIMIT 10";
            try {
                $stmt2 = $pdo->query($sql);
                $rows = $stmt2->fetchAll(PDO::FETCH_ASSOC);
                if (count($rows) > 0) {
                    $results[$table] = $rows;
                }
            } catch (Exception $e) {
                // Ignore type conversion errors etc
            }
        }
    }
    
    echo "Found 'surakarta' in tables:\n";
    foreach ($results as $table => $rows) {
        echo "- $table (" . count($rows) . " rows)\n";
        echo json_encode($rows, JSON_PRETTY_PRINT) . "\n\n";
    }
    
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
