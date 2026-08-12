<?php
$handle = fopen('test_.sql', 'r');
if (!$handle) {
    echo "Could not open test_.sql\n";
    exit;
}

$in_insert = false;
$insert_text = '';

while (($line = fgets($handle)) !== false) {
    if (preg_match('/INSERT INTO `?sm_dacol_objek_vital`?/i', $line)) {
        $in_insert = true;
        $insert_text = $line;
        continue;
    }
    
    if ($in_insert) {
        $insert_text .= $line;
        if (str_contains($line, ';')) {
            $in_insert = false;
            echo "--- INSERT STATEMENT ---\n";
            echo substr($insert_text, 0, 1500) . "...\n";
            // Count rows in values
            preg_match_all('/\(([^)]+)\)/', $insert_text, $rows);
            echo "Contains " . count($rows[0]) . " rows.\n\n";
            
            // Print any row containing Surakarta
            foreach ($rows[0] as $r) {
                if (stripos($r, 'surakarta') !== false || stripos($r, 'jawa tengah') !== false || stripos($r, 'solo') !== false) {
                    echo "Matching Row: " . substr($r, 0, 300) . "...\n";
                }
            }
        }
    }
}
fclose($handle);
