<?php
$handle = fopen('test_.sql', 'r');
if (!$handle) {
    echo "Could not open test_.sql\n";
    exit;
}

while (($line = fgets($handle)) !== false) {
    if (preg_match('/INSERT INTO `?sm_dacol_objek_vital`?/i', $line)) {
        echo "Found insert:\n";
        echo substr($line, 0, 1000) . "...\n\n";
    }
}
fclose($handle);
