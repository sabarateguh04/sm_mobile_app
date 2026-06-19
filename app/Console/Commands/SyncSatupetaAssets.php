<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

class SyncSatupetaAssets extends Command
{
    protected $signature = 'satupeta:sync-assets';

    protected $description = 'Salin aset SatuPeta dari satupeta_new/my ke public/satupeta_assets';

    public function handle(): int
    {
        $source = realpath(base_path('satupeta_new/my'));
        $dest = public_path('satupeta_assets');

        if (! $source || ! is_dir($source)) {
            $this->error('Folder sumber tidak ditemukan: satupeta_new/my');

            return self::FAILURE;
        }

        if (! is_dir($dest) && ! mkdir($dest, 0755, true) && ! is_dir($dest)) {
            $this->error("Gagal membuat folder: {$dest}");

            return self::FAILURE;
        }

        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($source, RecursiveDirectoryIterator::SKIP_DOTS),
            RecursiveIteratorIterator::SELF_FIRST
        );

        $count = 0;
        foreach ($iterator as $item) {
            $target = $dest . DIRECTORY_SEPARATOR . $iterator->getSubPathName();
            if ($item->isDir()) {
                if (! is_dir($target)) {
                    mkdir($target, 0755, true);
                }
            } else {
                copy($item->getPathname(), $target);
                $count++;
            }
        }

        $this->info("Berhasil menyalin {$count} file ke public/satupeta_assets");

        $critical = [
            'css/satupeta/custom_new.css',
            'vendor/bootstrap/js/jquery3.5.1.min.js',
            'js_local/satupeta/satupeta_baru.js',
            'satupeta/stakeholder.png',
        ];

        $missing = [];
        foreach ($critical as $relative) {
            if (! is_file($dest . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $relative))) {
                $missing[] = $relative;
            }
        }

        if ($missing !== []) {
            $this->newLine();
            $this->error('File penting belum ada setelah sync:');
            foreach ($missing as $path) {
                $this->line("  - {$path}");
            }
            $this->newLine();
            $this->warn('Pastikan folder sumber lengkap: satupeta_new/my/ (harus ada vendor/, satupeta/, js_local/)');
            $this->warn('Atau upload seluruh public/satupeta_assets/ dari mesin lokal (~603 file).');

            return self::FAILURE;
        }

        $this->line('Contoh URL: ' . asset('satupeta_assets/css/satupeta/custom_new.css'));

        return self::SUCCESS;
    }
}
