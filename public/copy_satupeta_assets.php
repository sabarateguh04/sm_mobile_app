<?php
function xcopy($source, $dest) {
    if (!is_dir($dest)) {
        mkdir($dest, 0755, true);
    }
    foreach (
        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($source, \RecursiveDirectoryIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::SELF_FIRST) as $item
    ) {
        if ($item->isDir()) {
            $target = $dest . DIRECTORY_SEPARATOR . $iterator->getSubPathName();
            if (!is_dir($target)) {
                mkdir($target, 0755, true);
            }
        } else {
            copy($item, $dest . DIRECTORY_SEPARATOR . $iterator->getSubPathName());
        }
    }
}

$source = realpath(__DIR__ . '/../satupeta_new/my');
$dest = __DIR__ . '/satupeta_assets';

if ($source && is_dir($source)) {
    xcopy($source, $dest);
    echo "BERHASIL: Semua aset dan icon satupeta berhasil disalin.";
} else {
    echo "GAGAL: Folder sumber tidak ditemukan.";
}
