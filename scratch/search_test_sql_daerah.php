<?php
$handle = fopen('test_.sql', 'r');
if (!$handle) {
    echo "Could not open test_.sql\n";
    exit;
}

echo "=== SEARCHING FOR DAERAH JATENG IN test_.sql ===\n";
while (($line = fgets($handle)) !== false) {
    if (preg_match('/INSERT INTO `?daerah`?/i', $line)) {
        preg_match_all('/\(([^)]+)\)/', $line, $rows);
        foreach ($rows[0] as $r) {
            if (stripos($r, 'jawa tengah') !== false || stripos($r, 'jateng') !== false) {
                echo "Daerah row: $r\n";
            }
        }
    }
    
    if (preg_match('/INSERT INTO `?wilayah`?/i', $line)) {
        preg_match_all('/\(([^)]+)\)/', $line, $rows);
        foreach ($rows[0] as $r) {
            if (stripos($r, 'surakarta') !== false || stripos($r, 'solo') !== false) {
                echo "Wilayah row: $r\n";
            }
        }
    }
}
fclose($handle);
