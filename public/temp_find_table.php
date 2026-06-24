<?php
// Script to extract all table names and columns from test.sql
$file = __DIR__ . '/../database/test.sql';
if (!file_exists($file)) {
    die("File not found: " . $file);
}

$handle = fopen($file, 'r');
if (!$handle) {
    die("Failed to open file");
}

$tables = [];
$currentTable = null;

while (($line = fgets($handle)) !== false) {
    if (preg_match("/CREATE TABLE\s+`?([a-zA-Z0-9_-]+)`?/i", $line, $matches)) {
        $currentTable = $matches[1];
        $tables[$currentTable] = [];
    } elseif ($currentTable) {
        if (strpos($line, ';') !== false && (strpos($line, 'ENGINE=') !== false || strpos($line, 'engine=') !== false)) {
            $currentTable = null;
        } else {
            // match columns
            if (preg_match("/^\s*`([a-zA-Z0-9_-]+)`\s+(.*),?$/", $line, $colMatches)) {
                $tables[$currentTable][] = $colMatches[1];
            }
        }
    }
}
fclose($handle);

header('Content-Type: text/plain');
echo "Found " . count($tables) . " tables in test.sql:\n\n";

foreach ($tables as $name => $cols) {
    echo "- " . $name . " (" . count($cols) . " columns)\n";
    if (in_array('koordinat', $cols) || in_array('lat', $cols) || in_array('lng', $cols) || in_array('latitude', $cols) || in_array('longitude', $cols)) {
        echo "  [HAS COORDINATES!]\n";
    }
}
