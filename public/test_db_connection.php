<?php

try {
    $host = '127.0.0.1';
    $db   = 'sql_103_114_110_66';
    $user = 'sql_103_114_110_66';
    $pass = 'sql_103_114_110_66';
    $port = 3306;
    $charset = 'utf8mb4';

    $dsn = "mysql:host=$host;dbname=$db;port=$port;charset=$charset";
    $options = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ];
    
    $pdo = new PDO($dsn, $user, $pass, $options);
    echo "Koneksi Database Berhasil!";
} catch (\PDOException $e) {
    echo "Koneksi Database Gagal: " . $e->getMessage();
}
