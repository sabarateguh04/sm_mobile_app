<?php
$sql = file_get_contents('sbo.sql');

// Find t_tempatwisata table creation and inserts
if (preg_match('/CREATE TABLE `t_tempatwisata`[\s\S]+?;/', $sql, $createMatch)) {
    echo "=== t_tempatwisata CREATE TABLE ===\n";
    echo $createMatch[0] . "\n\n";
}

// Find inserts for t_tempatwisata
if (preg_match_all('/INSERT INTO `t_tempatwisata`[\s\S]+?;/', $sql, $insertMatches)) {
    echo "=== t_tempatwisata INSERTS ===\n";
    foreach ($insertMatches[0] as $insert) {
        // Print lines containing 'Surakarta' or 'Ska' or matching regional keywords
        $lines = explode("\n", $insert);
        foreach ($lines as $line) {
            if (stripos($line, 'surakarta') !== false || stripos($line, 'jawa tengah') !== false || stripos($line, 'jateng') !== false) {
                echo "Line: " . substr($line, 0, 300) . "...\n";
            }
        }
    }
} else {
    echo "No inserts found for t_tempatwisata in sbo.sql\n";
}

// Let's also check if there are other tables like da_tempatwisata or wil_tempatwisata having Surakarta
$tables = ['da_tempatwisata', 'wil_tempatwisata', 'nas_objectwisata'];
foreach ($tables as $t) {
    if (preg_match_all('/INSERT INTO `'.$t.'`[\s\S]+?;/', $sql, $insertMatches)) {
        echo "\n=== $t INSERTS ===\n";
        foreach ($insertMatches[0] as $insert) {
            $lines = explode("\n", $insert);
            foreach ($lines as $line) {
                if (stripos($line, 'surakarta') !== false || stripos($line, 'jawa tengah') !== false || stripos($line, 'jateng') !== false) {
                    echo "Line: " . substr($line, 0, 300) . "...\n";
                }
            }
        }
    }
}
