<?php

if (! function_exists('tgl_indo')) {
    function tgl_indo(?string $tanggal): string
    {
        if (empty($tanggal)) {
            return '';
        }

        $bulan = [
            1 => 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
            'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember',
        ];

        $tgl = explode(' ', $tanggal);
        $pecahkan = explode('-', $tgl[0]);

        return ($pecahkan[2] ?? '') . ' ' . ($bulan[(int) ($pecahkan[1] ?? 0)] ?? '') . ' ' . ($pecahkan[0] ?? '');
    }
}

if (! function_exists('kordinat')) {
    function kordinat(?string $kordinat = ''): array
    {
        $k = explode(',', (string) $kordinat);

        return [(float) ($k[0] ?? 0), (float) ($k[1] ?? 0)];
    }
}

if (! function_exists('satupeta_asset')) {
    function satupeta_asset(string $path = ''): string
    {
        $base = '/' . trim((string) config('satupeta.assets_path', 'satupeta_assets'), '/');

        return $base . '/' . ltrim($path, '/');
    }
}
