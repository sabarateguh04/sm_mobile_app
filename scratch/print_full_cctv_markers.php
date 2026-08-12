<?php
$lines = file('app/Http/Controllers/MapController.php');
$start = 0;
foreach ($lines as $i => $line) {
    if (strpos($line, 'function getCctvMarkers') !== false) {
        $start = $i;
        break;
    }
}

if ($start > 0) {
    echo "Starting at line " . ($start + 1) . "\n";
    for ($i = $start; $i < $start + 100; $i++) {
        if (isset($lines[$i])) {
            echo ($i + 1) . ": " . $lines[$i];
        }
    }
} else {
    echo "Not found!\n";
}
