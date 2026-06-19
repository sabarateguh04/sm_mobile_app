<?php
/**
 * Konversi view CodeIgniter satupeta_baru.php ke Blade Laravel.
 */
$source = realpath(__DIR__ . '/../satupeta_new/application/views/page/satupeta/satupeta_baru.php');
$dest = __DIR__ . '/../resources/views/satupeta/satupeta_baru.blade.php';

if (!$source || !is_readable($source)) {
    fwrite(STDERR, "Source view not found: {$source}\n");
    exit(1);
}

$content = file_get_contents($source);

// base_url my/ → asset satupeta_assets/
$replacements = [
    '<?= base_url() ?>my/' => "{{ asset('satupeta_assets') }}/",
    '<?= base_url(); ?>my/' => "{{ asset('satupeta_assets') }}/",
    "<?= base_url('my/" => "{{ asset('satupeta_assets/",
    '<?php echo base_url(' => '{{-- converted --}}',
];

foreach ($replacements as $from => $to) {
    $content = str_replace($from, $to, $content);
}

// Session userdata → blade variables
$sessionMap = [
    "<?php echo \$this->session->userdata('id_unique') ?>" => '{{ $id_unique }}',
    "<?php echo \$this->session->userdata('token_bearer')?>" => '{{ $token_bearer }}',
    "<?php echo \$this->session->userdata('lat') != '' ? \$this->session->userdata('lat') : '-1.3292304' ?>" => '{{ $user_lat }}',
    "<?php echo \$this->session->userdata('lng') != '' ? \$this->session->userdata('lng') : '117.6913772' ?>" => '{{ $user_lng }}',
    "<?php echo \$this->session->userdata('polda') != '' ? \$this->session->userdata('polda') : '' ?>" => '{{ $polda_id }}',
    "<?php echo \$this->session->userdata('polres') != '' ? \$this->session->userdata('polres') : '' ?>" => '{{ $polres_id }}',
    "<?php echo \$this->session->userdata('provinsi') != '' ? \$this->session->userdata('provinsi') : ''?>" => '{{ $provinsi }}',
    "<?php echo \$this->session->userdata('kota') != '' ? \$this->session->userdata('kota') : ''?>" => '{{ $kota }}',
    "<?php echo \$this->session->userdata('id_unique') ?>" => '{{ $id_unique }}',
    "<?php echo \$this->session->userdata('token_bearer')?>" => '{{ $token_bearer }}',
];

foreach ($sessionMap as $from => $to) {
    $content = str_replace($from, $to, $content);
}

// Fix remaining base_url patterns
$content = preg_replace(
    '/\<\?= base_url\(\)\s*\?\>my\//',
    "{{ asset('satupeta_assets') }}/",
    $content
);
$content = preg_replace(
    '/\<\?= base_url\(\'my\/([^\']+)\'\)\s*\?\>/',
    "{{ asset('satupeta_assets/$1') }}",
    $content
);

// Google Maps API key from env
$content = preg_replace(
    '/key=AIzaSy[A-Za-z0-9_-]+/',
    'key={{ config(\'satupeta.google_maps_key\') }}',
    $content
);

// Remove PHP foreach lokasi (empty in CI)
$content = preg_replace(
    '/\<\?php foreach \(\$lokasi as \$v\) \{ \?\>.*?\<\?php \} \?\>/s',
    '{{-- lokasi kategori diisi via JS --}}',
    $content
);

// Auth logout link
$content = str_replace(
    "<?php echo base_url('Auth/logout')?>",
    "{{ url('Auth/logout') }}",
    $content
);

// Fix sisa penutup PHP CI: ') ?>" → ') }}" (Blade)
$content = str_replace("') ?>\"", "') }}\"", $content);

$blade = "@extends('layouts.satupeta')\n\n@section('content')\n" . $content . "\n@endsection\n";

if (!is_dir(dirname($dest))) {
    mkdir(dirname($dest), 0755, true);
}

file_put_contents($dest, $blade);
echo "Converted to: {$dest}\n";
