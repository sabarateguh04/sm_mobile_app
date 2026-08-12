<?php
$handle = fopen('test_.sql', 'r');
if (!$handle) {
    echo "Could not open test_.sql\n";
    exit;
}

$in_insert = false;
$insert_text = '';

while (($line = fgets($handle)) !== false) {
    if (preg_match('/INSERT INTO `?sm_dacol_fasilitas_umum`?/i', $line)) {
        $in_insert = true;
        $insert_text = $line;
        continue;
    }
    
    if ($in_insert) {
        $insert_text .= $line;
        if (str_contains($line, ';')) {
            $in_insert = false;
            echo "--- INSERT STATEMENT FOR sm_dacol_fasilitas_umum ---\n";
            preg_match_all('/\(([^)]+)\)/', $insert_text, $rows);
            echo "Contains " . count($rows[0]) . " rows.\n\n";
            
            // Print unique kategori_fasilitas
            $categories = [];
            foreach ($rows[0] as $r) {
                // Values are comma separated, kategori_fasilitas is the 7th field usually. Let's parse.
                $parts = str_getcsv($r, ',', "'");
                // Let's just print unique categories
                $cat = $parts[6] ?? 'UNKNOWN';
                $categories[$cat] = ($categories[$cat] ?? 0) + 1;
            }
            print_r($categories);
            break;
        }
    }
}
fclose($handle);
