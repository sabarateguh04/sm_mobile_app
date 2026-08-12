<?php
$handle = fopen('test_.sql', 'r');
if (!$handle) {
    echo "Could not open test_.sql\n";
    exit;
}

$in_create = false;
$create_text = '';

while (($line = fgets($handle)) !== false) {
    if (preg_match('/CREATE TABLE `?smpc_tbl_data_cctv`?/i', $line)) {
        $in_create = true;
        $create_text = $line;
        continue;
    }
    
    if ($in_create) {
        $create_text .= $line;
        if (str_contains($line, ';') || str_contains($line, 'ENGINE=')) {
            $in_create = false;
            echo "--- CREATE TABLE smpc_tbl_data_cctv ---\n";
            echo $create_text . "\n";
            break;
        }
    }
}
fclose($handle);
