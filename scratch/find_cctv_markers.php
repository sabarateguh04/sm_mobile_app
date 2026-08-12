<?php
$content = file_get_contents('app/Http/Controllers/MapController.php');
if (preg_match('/function getCctvMarkers[\s\S]+?}/', $content, $matches)) {
    echo "Found getCctvMarkers:\n";
    echo $matches[0] . "\n";
} else {
    echo "getCctvMarkers not found!\n";
}
if (preg_match('/function cctv[\s\S]+?}/', $content, $matches)) {
    echo "Found function cctv:\n";
    echo $matches[0] . "\n";
}
