<?php

require __DIR__ . '/vendor/autoload.php';
$app = require __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo 'default=' . config('database.default') . PHP_EOL;
echo 'operational=' . config('database.operational_connection', '(unset)') . PHP_EOL;
echo 'db=' . DB::connection()->getDatabaseName() . PHP_EOL;

$tables = DB::select('SHOW TABLES');
$key = array_key_first((array) $tables[0]);
$names = array_map(fn ($r) => $r->$key, $tables);

$needles = ['pos', 'terminal', 'turjag', 'contig', 'traffic', 'gangguan', 'wisata', 'ibadah', 'faskes', 'auditarus', 'angkutan'];
foreach ($needles as $n) {
    $hit = array_values(array_filter($names, fn ($x) => stripos($x, $n) !== false));
    if ($hit) {
        echo $n . ' => ' . implode(', ', $hit) . PHP_EOL;
    }
}

$mapTables = [
    'pos_pelayanan', 'data_terminal', 'data_gangguan', 'contigency', 'trafficstuck',
    'turjagwali', 'auditarus', 'pnp_angkutan', 'objek_wisata', 'tempatibadah', 'data_faskes',
];

$schema = App\Support\DatabaseConnections::operationalSchema();
$db = App\Support\DatabaseConnections::operationalDb();

foreach ($mapTables as $t) {
    $exists = $schema->hasTable($t) ? 'Y' : 'N';
    if ($exists === 'N') {
        echo "[$t] missing\n";
        continue;
    }
    $cols = ['koordinat', 'daerah', 'wilayah', 'piket'];
    $flags = [];
    foreach ($cols as $c) {
        $flags[] = $c . '=' . ($schema->hasColumn($t, $c) ? 'Y' : 'N');
    }
    $total = $db->table($t)->count();
    $withK = $schema->hasColumn($t, 'koordinat')
        ? $db->table($t)->whereNotNull('koordinat')->where('koordinat', '!=', '')->count()
        : -1;
    echo "[$t] total=$total withK=$withK " . implode(' ', $flags) . PHP_EOL;
}
