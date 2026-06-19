<?php
$sqlPath = __DIR__ . '/../test.sql';

if (!file_exists($sqlPath)) {
    die("File test.sql tidak ditemukan di $sqlPath");
}

$contents = file_get_contents($sqlPath);

echo "<h2>Query Update Manual (Latitude & Longitude)</h2>";
echo "<p>Silakan copy semua query di bawah ini dan paste ke phpMyAdmin / DBeaver Anda:</p>";

echo "<h3>1. Query Update Polda (Tabel: master_provinsi)</h3>";
echo "<textarea style='width:100%; height:200px; font-family:monospace; padding:10px; border-radius:8px;'>";
if (preg_match_all("/\(\d+,\s*'[^']*',\s*'([^']*)',\s*[^,]+,\s*'[^']*',\s*'([^']*)',\s*'([^']*)'/", $contents, $matches, PREG_SET_ORDER)) {
    foreach($matches as $m) {
        $poldaName = mb_strtoupper(trim($m[1]));
        // Hapus kata 'POLDA ' di awal
        $poldaNameClean = trim(preg_replace('/^POLDA\s+/i', '', $poldaName));
        $poldaNameClean = addslashes($poldaNameClean);

        $lat = $m[2] !== 'NULL' ? $m[2] : null;
        $lng = $m[3] !== 'NULL' ? $m[3] : null;

        if ($lat && $lng) {
            echo "UPDATE `master_provinsi` SET `lat` = '{$lat}', `lng` = '{$lng}' WHERE `nama` = '{$poldaNameClean}';\n";
        }
    }
}
echo "</textarea>";

echo "<h3>2. Query Update Polres (Tabel: master_kabupaten_kota)</h3>";
echo "<textarea style='width:100%; height:300px; font-family:monospace; padding:10px; border-radius:8px;'>";
$lines = explode("\n", $contents);
foreach($lines as $line) {
    if (preg_match("/\(\d+,\s*'[^']*',\s*'([^']*)',\s*'[^']*',\s*\d+,\s*\d+,\s*'([^']*)',\s*'([^']*)'/", $line, $m)) {
        $polresName = mb_strtoupper(trim($m[1]));
        
        // Hapus prefix umum Polres
        $polresNameClean = preg_replace('/^(POLRES\s+METRO|POLRESTABES|POLRESTA|POLRES)\s+/i', '', $polresName);
        $polresNameClean = trim(str_replace(['KABUPATEN ', 'KOTA '], '', $polresNameClean));
        $polresNameClean = addslashes($polresNameClean);

        $lat = $m[2] !== 'NULL' ? $m[2] : null;
        $lng = $m[3] !== 'NULL' ? $m[3] : null;

        if ($lat && $lng) {
            echo "UPDATE `master_kabupaten_kota` SET `lat` = '{$lat}', `lng` = '{$lng}' WHERE `nama` = '{$polresNameClean}';\n";
        }
    }
}
echo "</textarea>";

