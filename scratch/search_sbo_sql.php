<?php
$contents = file_get_contents('sbo.sql');
if (preg_match_all('/CREATE TABLE `([^`]+)`/', $contents, $matches)) {
    echo "Tables in sbo.sql:\n";
    foreach ($matches[1] as $m) {
        echo "- $m\n";
    }
} else {
    echo "No CREATE TABLE found in sbo.sql\n";
}
