<?php
$handle = fopen('test_.sql', 'r');
if (!$handle) {
    echo "Could not open test_.sql\n";
    exit;
}

$in_insert = false;
$insert_text = '';
$count = 0;

while (($line = fgets($handle)) !== false) {
    if (preg_match('/INSERT INTO `?smpc_tbl_data_cctv`?/i', $line)) {
        $in_insert = true;
        $insert_text = $line;
        continue;
    }
    
    if ($in_insert) {
        $insert_text .= $line;
        if (str_contains($line, ';')) {
            $in_insert = false;
            // Parse values
            preg_match_all('/\(([^)]+)\)/', $insert_text, $rows);
            echo "Insert statement contains " . count($rows[0]) . " rows.\n";
            
            // Print any row containing Surakarta or Solo or Jawa Tengah
            foreach ($rows[0] as $r) {
                if (stripos($r, 'surakarta') !== false || stripos($r, 'solo') !== false || stripos($r, 'jawa tengah') !== false || stripos($r, 'jateng') !== false) {
                    $count++;
                    echo "Matching CCTV row: " . substr($r, 0, 300) . "...\n";
                }
            }
        }
    }
}
fclose($handle);

echo "Total matching CCTV rows found in test_.sql: $count\n";
