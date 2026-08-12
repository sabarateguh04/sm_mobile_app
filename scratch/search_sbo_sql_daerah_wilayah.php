<?php
$sql = file_get_contents('sbo.sql');

if (preg_match_all('/INSERT INTO `daerah`[\s\S]+?;/', $sql, $matches)) {
    echo "=== daerah INSERTS ===\n";
    foreach ($matches[0] as $m) {
        $lines = explode("\n", $m);
        foreach ($lines as $l) {
            if (stripos($l, 'jawa tengah') !== false || stripos($l, 'jateng') !== false) {
                echo "Line: " . substr($l, 0, 300) . "\n";
            }
        }
    }
}

if (preg_match_all('/INSERT INTO `wilayah`[\s\S]+?;/', $sql, $matches)) {
    echo "\n=== wilayah INSERTS ===\n";
    foreach ($matches[0] as $m) {
        $lines = explode("\n", $m);
        foreach ($lines as $l) {
            if (stripos($l, 'surakarta') !== false || stripos($l, 'solo') !== false) {
                echo "Line: " . substr($l, 0, 300) . "\n";
            }
        }
    }
}
