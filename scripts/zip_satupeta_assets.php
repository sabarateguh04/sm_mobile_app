<?php

$source = realpath(__DIR__ . '/../public/satupeta_assets');
$zipPath = realpath(__DIR__ . '/..') . DIRECTORY_SEPARATOR . 'satupeta_assets_deploy.zip';

if (! $source || ! is_dir($source)) {
    fwrite(STDERR, "Folder tidak ditemukan: public/satupeta_assets\n");
    exit(1);
}

$zip = new ZipArchive();
if ($zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
    fwrite(STDERR, "Gagal membuat zip: {$zipPath}\n");
    exit(1);
}

$iterator = new RecursiveIteratorIterator(
    new RecursiveDirectoryIterator($source, FilesystemIterator::SKIP_DOTS)
);

$count = 0;
foreach ($iterator as $file) {
    if (! $file->isFile()) {
        continue;
    }
    $relative = substr($file->getPathname(), strlen($source) + 1);
    $zip->addFile($file->getPathname(), str_replace('\\', '/', $relative));
    $count++;
}

$zip->close();

echo "OK: {$count} file -> {$zipPath} (" . round(filesize($zipPath) / 1024 / 1024, 1) . " MB)\n";
