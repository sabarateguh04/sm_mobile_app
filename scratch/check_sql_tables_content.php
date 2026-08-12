<?php
$handle = fopen('test_.sql', 'r');
if (!$handle) {
    echo "Could not open test_.sql\n";
    exit;
}

$tables = [];
while (($line = fgets($handle)) !== false) {
    if (preg_match('/INSERT INTO `?([^`\s()]+)`?/i', $line, $matches)) {
        $table = $matches[1];
        $tables[$table] = ($tables[$table] ?? 0) + 1;
    }
}
fclose($handle);

echo "Tables with inserts in test_.sql:\n";
print_r($tables);
