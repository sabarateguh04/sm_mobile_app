<?php

/** Unduh provinces + regencies (emsifa/api-wilayah-indonesia) ke database/data/indonesia_territories.json */

$base = 'https://raw.githubusercontent.com/emsifa/api-wilayah-indonesia/master/static/api/';
$provinces = json_decode(file_get_contents($base.'provinces.json'), true);
if (! is_array($provinces)) {
    fwrite(STDERR, "Gagal unduh provinces.json\n");
    exit(1);
}

$out = [];
foreach ($provinces as $prov) {
    $pid = $prov['id'];
    $url = $base.'regencies/'.$pid.'.json';
    $raw = @file_get_contents($url);
    $regs = json_decode($raw ?: '[]', true);
    $out[] = [
        'province' => $prov,
        'regencies' => is_array($regs) ? $regs : [],
    ];
}

$target = dirname(__DIR__).'/data/indonesia_territories.json';
if (! is_dir(dirname($target))) {
    mkdir(dirname($target), 0755, true);
}

file_put_contents($target, json_encode($out, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));

$nReg = array_sum(array_map(static fn ($x) => count($x['regencies']), $out));
echo 'OK: '.count($out)." provinsi, {$nReg} kab/kota -> {$target}\n";
