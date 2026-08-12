<?php
// Read test_.sql line-by-line to avoid out-of-memory errors
$handle = fopen('test_.sql', 'r');
if (!$handle) {
    echo "Could not open test_.sql\n";
    exit;
}

echo "Searching test_.sql...\n";
$lines_count = 0;
$inserts_found = [];

while (($line = fgets($handle)) !== false) {
    $lines_count++;
    if (preg_match('/INSERT INTO `?(t_tempatwisata|objek_wisata|sm_dacol_objek_vital)`?/i', $line, $matches)) {
        $table = $matches[1];
        $inserts_found[$table] = ($inserts_found[$table] ?? 0) + 1;
        
        // If it matches Surakarta, print it
        if (stripos($line, 'surakarta') !== false) {
            echo "Line $lines_count (Table: $table) contains 'Surakarta':\n";
            echo substr($line, 0, 500) . "...\n\n";
        }
    }
}
fclose($handle);

echo "Search finished. Checked $lines_count lines.\n";
echo "Inserts found per table:\n";
foreach ($inserts_found as $table => $count) {
    echo "- $table: $count inserts\n";
}
