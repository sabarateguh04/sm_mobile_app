<?php

use App\Exports\DemoAccountsExport;
use App\Exports\UsmAccountsByTerritoryWorkbookExport;
use App\Exports\UsmAccountsExport;
use App\Support\AnalyticStagingToTrafficstuckSync;
use App\Support\LayananToOpsdalSync;
use App\Support\PatrolCctvOpsdalSync;
use App\Support\SmLiteSyncRuntime;
use App\Support\SmLiteTrafficVolumeClassifier;
use App\Support\TrafficDalkotAnalyticSync;
use App\Support\TerritoryLevelForUsm;
use App\Models\Usm;
use App\Support\DemoAccountsSpreadsheet;
use Database\Seeders\MasterTerritoryDemoAccountsSeeder;
use Database\Seeders\RoleDemoAccountsSeeder;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Facades\Excel;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('export:demo-accounts-excel {--filename=Akun_Demo_SmartBackoffice.xlsx}', function () {
    if (! Schema::hasTable('roles')) {
        $this->error('Tabel `roles` tidak ada — tidak bisa mengekspor.');

        return 1;
    }

    $filename = (string) $this->option('filename');
    if ($filename === '' || preg_match('/[^\w\.\-]/', basename($filename))) {
        $this->error('Nama file tidak valid. Gunakan huruf, angka, titik, underscore, dan tanda hubung saja.');

        return 1;
    }

    $relative = 'exports/'.$filename;
    Storage::disk('local')->makeDirectory('exports');

    $generator = new DemoAccountsSpreadsheet;
    $rows = Collection::make($generator->spreadsheetRowsCombined());

    if ($rows->isEmpty()) {
        $this->error('Tidak ada baris yang diekspor (periksa definisi role di database).');

        return 1;
    }

    Excel::store(new DemoAccountsExport($rows), $relative, 'local');

    $full = storage_path('app/'.$relative);
    $this->info('File Excel akun demo dibuat: '.$full);
    $this->comment('Baris: '.$rows->count().' | Password demo (kolom): '.RoleDemoAccountsSeeder::DEMO_PASSWORD);

    return 0;
})->purpose('Ekspor spreadsheet akun demo (RoleDemoAccountsSeeder + akun mdemo_* dari DB) ke storage/app/exports');

Artisan::command('export:usm-accounts-excel {--filename=Semua_Akun_USM.xlsx} {--smartmgmt : Hanya akun skenario Smart Management (username diawali sm_)}', function () {
    if (! Schema::hasTable('usm')) {
        $this->error('Tabel `usm` tidak ada — tidak bisa mengekspor.');

        return 1;
    }

    $filename = (string) $this->option('filename');
    if ($filename === '' || preg_match('/[^\w\.\-]/', basename($filename))) {
        $this->error('Nama file tidak valid. Gunakan huruf, angka, titik, underscore, dan tanda hubung saja.');

        return 1;
    }

    $relative = 'exports/'.$filename;
    Storage::disk('local')->makeDirectory('exports');

    $query = Usm::query()->with('role')->orderBy('username');
    if ($this->option('smartmgmt')) {
        $query->where('username', 'like', 'sm_%');
    }

    $users = $query->get();

    if ($users->isEmpty()) {
        $this->warn('Tidak ada baris pengguna yang cocok dengan filter.');

        return 0;
    }

    Excel::store(new UsmAccountsExport($users), $relative, 'local');

    $full = storage_path('app/'.$relative);
    $this->info('File Excel dibuat: '.$full);
    $this->comment('Baris: '.$users->count().' | Kolom password (plain) diisi untuk username sm_* dan mdemo_* → '.RoleDemoAccountsSeeder::DEMO_PASSWORD);

    return 0;
})->purpose('Ekspor semua akun dari tabel usm ke storage/app/exports (opsi --smartmgmt untuk sm_* saja)');

Artisan::command('export:accounts-by-territory-excel {--filename=Akun_per_Nasional_Daerah_Wilayah.xlsx} {--smartmgmt : Hanya username sm_*}', function () {
    if (! Schema::hasTable('usm')) {
        $this->error('Tabel `usm` tidak ada — tidak bisa mengekspor.');

        return 1;
    }

    $filename = (string) $this->option('filename');
    if ($filename === '' || preg_match('/[^\w\.\-]/', basename($filename))) {
        $this->error('Nama file tidak valid. Gunakan huruf, angka, titik, underscore, dan tanda hubung saja.');

        return 1;
    }

    $relative = 'exports/'.$filename;
    Storage::disk('local')->makeDirectory('exports');

    $query = Usm::query()->with('role')->orderBy('username');
    if ($this->option('smartmgmt')) {
        $query->where('username', 'like', 'sm_%');
    }

    $users = $query->get();

    Excel::store(new UsmAccountsByTerritoryWorkbookExport($users), $relative, 'local');

    $counts = [
        TerritoryLevelForUsm::NASIONAL => 0,
        TerritoryLevelForUsm::DAERAH => 0,
        TerritoryLevelForUsm::WILAYAH => 0,
    ];
    foreach ($users as $user) {
        $counts[TerritoryLevelForUsm::resolve($user)]++;
    }

    $full = storage_path('app/'.$relative);
    $this->info('File Excel dibuat: '.$full);
    $this->comment(sprintf(
        'Lembar: Nasional %d | Daerah %d | Wilayah %d (total %d) — kolom password hanya diisi jika diketahui dari seeder',
        $counts[TerritoryLevelForUsm::NASIONAL],
        $counts[TerritoryLevelForUsm::DAERAH],
        $counts[TerritoryLevelForUsm::WILAYAH],
        $users->count()
    ));

    return 0;
})->purpose('Ekspor akun USM ke satu Excel (3 lembar: Nasional, Daerah, Wilayah)');

Artisan::command('demo:territory-accounts-seed {scope? : per_province atau all_kabkot (default dari .env)} {--roles= : ID matrix per kab, contoh 2,3}', function (?string $scope) {
    $seeder = new MasterTerritoryDemoAccountsSeeder;
    if ($scope !== null && trim($scope) !== '') {
        $seeder->territoryScopeOverride = strtolower(trim($scope));
    }
    $rolesOpt = $this->option('roles');
    if (is_string($rolesOpt) && trim($rolesOpt) !== '') {
        $seeder->matrixRoleIdsOverride = array_values(array_filter(
            array_map('intval', explode(',', $rolesOpt)),
            static fn (int $x): bool => $x > 0
        ));
    }
    $seeder->run();

    return 0;
})->purpose('Seed akun demo dari master provinsi/kab (lingkup & role matrix dapat di-override)');

Artisan::command('demo:export-accounts-by-role {--dir=demo_per_role : Subfolder di storage/app/exports}', function () {
    if (! Schema::hasTable('roles')) {
        $this->error('Tabel `roles` tidak ada.');

        return 1;
    }

    $dir = (string) $this->option('dir');
    $dir = trim(str_replace(['..', '\\'], ['', '/'], $dir), '/');
    if ($dir === '' || preg_match('/[^a-zA-Z0-9_\-\/]/', $dir)) {
        $this->error('Nama folder tidak valid.');

        return 1;
    }

    Storage::disk('local')->makeDirectory('exports/'.$dir);

    $generator = new DemoAccountsSpreadsheet;
    $combined = $generator->spreadsheetRowsCombined();
    if ($combined === []) {
        $this->error('Tidak ada baris untuk diekspor.');

        return 1;
    }

    $byRole = [];
    foreach ($combined as $row) {
        $rid = (int) ($row['role_id'] ?? 0);
        if ($rid < 1) {
            continue;
        }
        if (! isset($byRole[$rid])) {
            $byRole[$rid] = [];
        }
        $byRole[$rid][] = $row;
    }
    ksort($byRole);

    $pk = Schema::hasColumn('roles', 'role_id') ? 'role_id' : 'id';

    foreach ($byRole as $rid => $rows) {
        $role = DB::table('roles')->where($pk, $rid)->first();
        $name = $role ? (string) ($role->role_name ?? '') : '';
        $slug = Str::slug($name, '_') ?: ('role_'.$rid);
        $slug = substr($slug, 0, 48);
        $fname = sprintf('role_%02d_%s.xlsx', $rid, $slug);
        Excel::store(new DemoAccountsExport(Collection::make($rows)), 'exports/'.$dir.'/'.$fname, 'local');
        $this->line('  '.$fname.' — '.count($rows).' baris');
    }

    $this->newLine();
    $this->info('Disimpan di: '.storage_path('app/exports/'.$dir));
    $this->comment('Password kolom (demo): '.RoleDemoAccountsSeeder::DEMO_PASSWORD);

    return 0;
})->purpose('Satu file Excel per role_id (legacy + master teritori)');

Artisan::command('demo:rebuild-territory-demo {--scope=all_kabkot : per_province atau all_kabkot} {--roles= : Matrix role per kab (koma); kosong = pakai env MASTER_DEMO_MATRIX_ROLES} {--export-dir=demo_per_role}', function () {
    $seeder = new MasterTerritoryDemoAccountsSeeder;
    $seeder->territoryScopeOverride = strtolower(trim((string) $this->option('scope')));

    $rolesOpt = $this->option('roles');
    if (is_string($rolesOpt) && trim($rolesOpt) !== '') {
        $seeder->matrixRoleIdsOverride = array_values(array_filter(
            array_map('intval', explode(',', $rolesOpt)),
            static fn (int $x): bool => $x > 0
        ));
    } else {
        $seeder->matrixRoleIdsOverride = null;
    }

    $seeder->run();

    $exitCode = $this->call('demo:export-accounts-by-role', [
        '--dir' => (string) $this->option('export-dir'),
    ]);

    return is_int($exitCode) ? $exitCode : 0;
})->purpose('Buat ulang akun demo master teritori + ekspor Excel per role');

Artisan::command('db:repair-migrations-auto-increment', function () {
    if (Schema::getConnection()->getDriverName() !== 'mysql') {
        $this->error('Perintah ini hanya untuk koneksi MySQL.');

        return 1;
    }

    if (! Schema::hasTable('migrations') || ! Schema::hasColumn('migrations', 'id')) {
        $this->error('Tabel `migrations` atau kolom `id` tidak ada. Jalankan `php artisan migrate:install` jika perlu.');

        return 1;
    }

    /** @var object{Type: string, Key: string, Extra: string}|null $col */
    $col = DB::selectOne("SHOW COLUMNS FROM `migrations` WHERE Field = 'id'");

    if ($col === null) {
        $this->error('Tidak bisa membaca definisi kolom `migrations.id`.');

        return 1;
    }

    if (str_contains(strtolower((string) $col->Extra), 'auto_increment')) {
        $this->info('Kolom `migrations.id` sudah AUTO_INCREMENT — tidak ada perbaikan yang dijalankan.');

        return 0;
    }

    $type = trim((string) $col->Type);
    if ($type === '') {
        $this->error('Tipe kolom `migrations.id` tidak dikenali.');

        return 1;
    }

    // Same constraint as Laravel's DatabaseMigrationRepository::createRepository() (increments + PK).
    if (strtoupper((string) $col->Key) !== 'PRI') {
        $pk = DB::select("SHOW KEYS FROM `migrations` WHERE Key_name = 'PRIMARY'");
        if ($pk === []) {
            DB::statement('ALTER TABLE `migrations` ADD PRIMARY KEY (`id`)');
        }
    }

    DB::statement('ALTER TABLE `migrations` MODIFY `id` '.$type.' NOT NULL AUTO_INCREMENT');

    $this->info('Berhasil memperbaiki `migrations.id` menjadi AUTO_INCREMENT. Silakan jalankan `php artisan migrate` lagi.');

    return 0;
})->purpose('Perbaiki kolom migrations.id tanpa AUTO_INCREMENT (mengatasi SQLSTATE 1364 saat migrate)');

Artisan::command('usm:set-password {username : Username di tabel usm} {password : Password baru}', function (string $username, string $password) {
    $user = Usm::query()->where('username', $username)->first();
    if (! $user) {
        $this->error('User tidak ditemukan: '.$username);

        return 1;
    }

    $user->password = Hash::make($password);
    $user->account_status = 'Active';
    $user->save();

    $this->info('Password untuk '.$username.' sudah diatur dan akun di-set Active.');

    return 0;
})->purpose('Atur ulang password user USM (mis. setelah import sbo.sql)');

Artisan::command('traffic:sync-dalkot-analytic {--limit=0 : Batas baris (0 = semua)}', function () {
    if (! TrafficDalkotAnalyticSync::enabled()) {
        $this->error('Tabel trafficdalkot atau trafficstuck tidak tersedia.');

        return 1;
    }

    $q = DB::table('trafficdalkot')->orderBy('id_trafficdalkot');
    $limit = (int) $this->option('limit');
    if ($limit > 0) {
        $q->limit($limit);
    }

    $ok = 0;
    $fail = 0;

    foreach ($q->get() as $row) {
        $result = TrafficDalkotAnalyticSync::upsertFromDalkot((int) $row->id_trafficdalkot, (array) $row);
        if ($result['ok'] ?? false) {
            $ok++;
        } else {
            $fail++;
        }
    }

    $this->info("Selesai: {$ok} disalin ke Traffic Analytic, {$fail} gagal.");

    return $fail > 0 ? 1 : 0;
})->purpose('Salin trafficdalkot ke Traffic Analytic (trafficstuck patrol_online)');

Artisan::command('sm-lite:discovery-audit {--output= : Simpan hasil JSON ke storage/app/<path>} {--json : Tampilkan ringkasan dalam format JSON}', function () {
    $startedAt = now();
    $tables = [
        'trafficdalkot',
        'patrolicctv',
        'trafficstuck',
        'contigency',
        'mediaanalytic',
        'layanan_masyarakat',
        'usm',
    ];

    $summary = [
        'generated_at' => $startedAt->toDateTimeString(),
        'connection' => DB::connection()->getName(),
        'database' => DB::connection()->getDatabaseName(),
        'tables' => [],
        'quality_checks' => [
            'null_critical' => [],
            'duplicates' => [],
            'orphans' => [],
            'invalid_values' => [],
        ],
    ];

    foreach ($tables as $table) {
        if (! Schema::hasTable($table)) {
            $summary['tables'][$table] = [
                'exists' => false,
                'row_count' => null,
                'columns' => [],
            ];
            continue;
        }

        $summary['tables'][$table] = [
            'exists' => true,
            'row_count' => (int) DB::table($table)->count(),
            'columns' => Schema::getColumnListing($table),
        ];
    }

    $nullChecks = [
        'trafficdalkot' => ['id_trafficdalkot', 'tanggal'],
        'patrolicctv' => ['id_patrolicctv', 'tanggal'],
        'trafficstuck' => ['id_trafficstuck', 'tanggal', 'lokasi'],
        'contigency' => ['id_contigency', 'tanggal', 'lokasi'],
        'mediaanalytic' => ['id_mediaanalytic', 'tanggal'],
        'layanan_masyarakat' => ['id_interaksi', 'tanggal', 'kategori'],
    ];
    foreach ($nullChecks as $table => $columns) {
        if (! ($summary['tables'][$table]['exists'] ?? false)) {
            continue;
        }
        foreach ($columns as $column) {
            if (! Schema::hasColumn($table, $column)) {
                continue;
            }
            $summary['quality_checks']['null_critical'][] = [
                'table' => $table,
                'column' => $column,
                'null_count' => (int) DB::table($table)
                    ->whereNull($column)
                    ->count(),
            ];
        }
    }

    if (($summary['tables']['trafficstuck']['exists'] ?? false) && Schema::hasColumn('trafficstuck', 'id_trafficdalkot')) {
        $summary['quality_checks']['duplicates'][] = [
            'table' => 'trafficstuck',
            'column' => 'id_trafficdalkot',
            'duplicate_keys' => (int) DB::table('trafficstuck')
                ->whereNotNull('id_trafficdalkot')
                ->groupBy('id_trafficdalkot')
                ->havingRaw('COUNT(*) > 1')
                ->count(),
        ];
    }
    if (($summary['tables']['trafficstuck']['exists'] ?? false) && Schema::hasColumn('trafficstuck', 'id_patrolicctv')) {
        $summary['quality_checks']['duplicates'][] = [
            'table' => 'trafficstuck',
            'column' => 'id_patrolicctv',
            'duplicate_keys' => (int) DB::table('trafficstuck')
                ->whereNotNull('id_patrolicctv')
                ->groupBy('id_patrolicctv')
                ->havingRaw('COUNT(*) > 1')
                ->count(),
        ];
    }
    if (($summary['tables']['contigency']['exists'] ?? false) && Schema::hasColumn('contigency', 'id_patrolicctv')) {
        $summary['quality_checks']['duplicates'][] = [
            'table' => 'contigency',
            'column' => 'id_patrolicctv',
            'duplicate_keys' => (int) DB::table('contigency')
                ->whereNotNull('id_patrolicctv')
                ->groupBy('id_patrolicctv')
                ->havingRaw('COUNT(*) > 1')
                ->count(),
        ];
    }

    if (($summary['tables']['trafficstuck']['exists'] ?? false)
        && ($summary['tables']['trafficdalkot']['exists'] ?? false)
        && Schema::hasColumn('trafficstuck', 'id_trafficdalkot')
    ) {
        $summary['quality_checks']['orphans'][] = [
            'relation' => 'trafficstuck.id_trafficdalkot -> trafficdalkot.id_trafficdalkot',
            'orphan_count' => (int) DB::table('trafficstuck as t')
                ->leftJoin('trafficdalkot as d', 'd.id_trafficdalkot', '=', 't.id_trafficdalkot')
                ->whereNotNull('t.id_trafficdalkot')
                ->whereNull('d.id_trafficdalkot')
                ->count(),
        ];
    }
    if (($summary['tables']['trafficstuck']['exists'] ?? false)
        && ($summary['tables']['patrolicctv']['exists'] ?? false)
        && Schema::hasColumn('trafficstuck', 'id_patrolicctv')
    ) {
        $summary['quality_checks']['orphans'][] = [
            'relation' => 'trafficstuck.id_patrolicctv -> patrolicctv.id_patrolicctv',
            'orphan_count' => (int) DB::table('trafficstuck as t')
                ->leftJoin('patrolicctv as p', 'p.id_patrolicctv', '=', 't.id_patrolicctv')
                ->whereNotNull('t.id_patrolicctv')
                ->whereNull('p.id_patrolicctv')
                ->count(),
        ];
    }
    if (($summary['tables']['contigency']['exists'] ?? false)
        && ($summary['tables']['patrolicctv']['exists'] ?? false)
        && Schema::hasColumn('contigency', 'id_patrolicctv')
    ) {
        $summary['quality_checks']['orphans'][] = [
            'relation' => 'contigency.id_patrolicctv -> patrolicctv.id_patrolicctv',
            'orphan_count' => (int) DB::table('contigency as c')
                ->leftJoin('patrolicctv as p', 'p.id_patrolicctv', '=', 'c.id_patrolicctv')
                ->whereNotNull('c.id_patrolicctv')
                ->whereNull('p.id_patrolicctv')
                ->count(),
        ];
    }
    if (($summary['tables']['trafficstuck']['exists'] ?? false)
        && ($summary['tables']['usm']['exists'] ?? false)
        && Schema::hasColumn('trafficstuck', 'piket')
    ) {
        $summary['quality_checks']['orphans'][] = [
            'relation' => 'trafficstuck.piket -> usm.id_usm',
            'orphan_count' => (int) DB::table('trafficstuck as t')
                ->leftJoin('usm as u', 'u.id_usm', '=', 't.piket')
                ->whereNotNull('t.piket')
                ->whereNull('u.id_usm')
                ->count(),
        ];
    }
    if (($summary['tables']['contigency']['exists'] ?? false)
        && ($summary['tables']['usm']['exists'] ?? false)
        && Schema::hasColumn('contigency', 'piket')
    ) {
        $summary['quality_checks']['orphans'][] = [
            'relation' => 'contigency.piket -> usm.id_usm',
            'orphan_count' => (int) DB::table('contigency as c')
                ->leftJoin('usm as u', 'u.id_usm', '=', 'c.piket')
                ->whereNotNull('c.piket')
                ->whereNull('u.id_usm')
                ->count(),
        ];
    }

    foreach (['trafficstuck', 'contigency'] as $table) {
        if (! ($summary['tables'][$table]['exists'] ?? false) || ! Schema::hasColumn($table, 'sb_grp')) {
            continue;
        }
        $invalidCount = (int) DB::table($table)
            ->whereNotNull('sb_grp')
            ->whereNotIn('sb_grp', ['opsdal', 'patrol_online'])
            ->count();
        $summary['quality_checks']['invalid_values'][] = [
            'table' => $table,
            'column' => 'sb_grp',
            'invalid_count' => $invalidCount,
            'allowed' => ['opsdal', 'patrol_online'],
        ];
    }

    $missingTables = collect($summary['tables'])
        ->filter(fn (array $table): bool => $table['exists'] === false)
        ->keys()
        ->values()
        ->all();

    if ($this->option('json')) {
        $this->line(json_encode($summary, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    } else {
        $this->info('SM-Lite discovery audit selesai.');
        $this->line('Database: '.($summary['database'] ?? '-').' | Connection: '.$summary['connection']);
        foreach ($summary['tables'] as $table => $meta) {
            if (! $meta['exists']) {
                $this->warn("- {$table}: table tidak ditemukan");
                continue;
            }
            $this->line("- {$table}: {$meta['row_count']} baris, ".count($meta['columns']).' kolom');
        }
        if ($missingTables !== []) {
            $this->newLine();
            $this->warn('Tabel belum tersedia: '.implode(', ', $missingTables));
        }
    }

    $output = (string) ($this->option('output') ?? '');
    if ($output !== '') {
        $relative = ltrim(str_replace('\\', '/', $output), '/');
        Storage::disk('local')->put($relative, json_encode($summary, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        $this->info('Audit disimpan ke: '.storage_path('app/'.$relative));
    }

    return 0;
})->purpose('Audit discovery data quality legacy + mapping readiness untuk migrasi SM-Lite');

Artisan::command('sm-lite:staging-transform {--limit=1000 : Batas row per run} {--dry-run : Simulasi tanpa commit}', function () {
    if (! Schema::hasTable('trafficdalkot')) {
        $this->error('Tabel trafficdalkot belum tersedia.');

        return 1;
    }
    if (! Schema::hasTable('traffic_counting_staging')) {
        $this->error('Tabel traffic_counting_staging belum tersedia. Jalankan migrate terlebih dahulu.');

        return 1;
    }

    $limit = max(1, (int) $this->option('limit'));
    $dryRun = (bool) $this->option('dry-run');
    $cursorKey = 'legacy_to_staging_traffic';
    $watermark = SmLiteSyncRuntime::watermark($cursorKey);
    $lastId = (int) ($watermark['last_id'] ?? 0);

    $batchKey = SmLiteSyncRuntime::startBatch('legacy_to_staging', 'traffic_counting', [
        'limit' => $limit,
        'dry_run' => $dryRun,
        'watermark_before' => $lastId,
    ]);

    $rows = DB::table('trafficdalkot')
        ->where('id_trafficdalkot', '>', $lastId)
        ->orderBy('id_trafficdalkot')
        ->limit($limit)
        ->get();

    if ($rows->isEmpty()) {
        SmLiteSyncRuntime::finishBatch($batchKey, 'success', 0, 0, 0);
        $this->info('Tidak ada data baru untuk staging transform.');

        return 0;
    }

    $ok = 0;
    $fail = 0;
    $maxId = $lastId;

    if ($dryRun) {
        DB::beginTransaction();
    }

    try {
        foreach ($rows as $row) {
            $sid = (int) $row->id_trafficdalkot;
            $maxId = max($maxId, $sid);

            try {
                $volume = SmLiteTrafficVolumeClassifier::classify($row->r2 ?? 0, $row->r4 ?? 0, $row->r6 ?? 0, $row->jumlah ?? null);

                $payload = [
                    'source_system' => 'dcc',
                    'source_id' => $sid,
                    'tanggal' => $row->tanggal ?? null,
                    'waktu' => $row->waktu ?? null,
                    'lokasi' => mb_substr((string) ($row->namajalan ?? ''), 0, 150),
                    'gerbangkota' => mb_substr((string) ($row->gerbangkota ?? ''), 0, 100),
                    'araharus' => mb_substr((string) ($row->araharus ?? ''), 0, 100),
                    'jenisjalan' => mb_substr((string) ($row->jenisjalan ?? ''), 0, 100),
                    'r2' => $volume['r2'],
                    'r4' => $volume['r4'],
                    'r6' => $volume['r6'],
                    'r6_plus' => $volume['r6_plus'],
                    'jumlah' => $volume['total'],
                    'volume_class' => $volume['volume_class'],
                    'koordinat' => isset($row->koordinat) ? mb_substr((string) $row->koordinat, 0, 100) : null,
                    'fotosit' => isset($row->fotosit) ? mb_substr((string) $row->fotosit, 0, 255) : null,
                    'ketcb' => $row->ketcb ?? null,
                    'status' => 'ready',
                    'batch_key' => $batchKey,
                    'updated_at' => now(),
                ];

                $exists = DB::table('traffic_counting_staging')
                    ->where('source_system', 'dcc')
                    ->where('source_id', $sid)
                    ->exists();

                if ($exists) {
                    DB::table('traffic_counting_staging')
                        ->where('source_system', 'dcc')
                        ->where('source_id', $sid)
                        ->update($payload);
                } else {
                    $payload['created_at'] = now();
                    DB::table('traffic_counting_staging')->insert($payload);
                }

                $ok++;
            } catch (\Throwable $e) {
                $fail++;
                SmLiteSyncRuntime::deadLetter(
                    $batchKey,
                    'legacy_to_staging',
                    'traffic_counting',
                    'trafficdalkot',
                    (string) $sid,
                    (array) $row,
                    $e->getMessage()
                );
            }
        }

        SmLiteSyncRuntime::setWatermark($cursorKey, $maxId, now()->toDateTimeString());
        SmLiteSyncRuntime::finishBatch($batchKey, $fail > 0 ? 'partial' : 'success', $rows->count(), $ok, $fail);

        if ($dryRun) {
            DB::rollBack();
            $this->warn('DRY-RUN aktif: perubahan dibatalkan (rollback).');
        }
    } catch (\Throwable $e) {
        if ($dryRun) {
            DB::rollBack();
        }
        SmLiteSyncRuntime::finishBatch($batchKey, 'failed', $rows->count(), $ok, $fail, 0, $e->getMessage());
        $this->error('Gagal transform staging: '.$e->getMessage());

        return 1;
    }

    $this->info("Staging transform selesai. total={$rows->count()} ok={$ok} fail={$fail} watermark={$maxId}");

    return $fail > 0 ? 1 : 0;
})->purpose('Transformasi & pembersihan data traffic legacy ke tabel staging');

Artisan::command('sm-lite:migrate-one-time {--limit=0 : Batas data per sumber (0=semua)} {--dry-run : Simulasi tanpa commit}', function () {
    $dryRun = (bool) $this->option('dry-run');
    $limit = (int) $this->option('limit');
    $batchKey = SmLiteSyncRuntime::startBatch('legacy_to_smlite', 'one_time_migration', [
        'limit' => $limit,
        'dry_run' => $dryRun,
    ]);

    $sources = [
        'trafficdalkot' => Schema::hasTable('trafficdalkot') && TrafficDalkotAnalyticSync::enabled(),
        'patrolicctv' => Schema::hasTable('patrolicctv') && Schema::hasTable('trafficstuck') && Schema::hasTable('contigency'),
        'layanan_masyarakat' => Schema::hasTable('layanan_masyarakat'),
    ];
    if (! in_array(true, $sources, true)) {
        $this->error('Tidak ada sumber migrasi yang siap (cek tabel legacy/target).');

        return 1;
    }

    $ok = 0;
    $fail = 0;
    $total = 0;

    if ($dryRun) {
        DB::beginTransaction();
    }

    try {
        if ($sources['trafficdalkot']) {
            $q = DB::table('trafficdalkot')->orderBy('id_trafficdalkot');
            if ($limit > 0) {
                $q->limit($limit);
            }
            foreach ($q->get() as $row) {
                $total++;
                $result = TrafficDalkotAnalyticSync::upsertFromDalkot((int) $row->id_trafficdalkot, (array) $row);
                if ($result['ok'] ?? false) {
                    $ok++;
                } else {
                    $fail++;
                    SmLiteSyncRuntime::deadLetter(
                        $batchKey,
                        'legacy_to_smlite',
                        'one_time_trafficdalkot',
                        'trafficdalkot',
                        (string) $row->id_trafficdalkot,
                        (array) $row,
                        (string) ($result['message'] ?? 'upsert gagal')
                    );
                }
            }
        }

        if ($sources['patrolicctv']) {
            $q = DB::table('patrolicctv')->orderBy('id_patrolicctv');
            if ($limit > 0) {
                $q->limit($limit);
            }
            foreach ($q->get() as $row) {
                $total++;
                $result = PatrolCctvOpsdalSync::syncFromPatrol((int) $row->id_patrolicctv, (array) $row, 'patrolicctv');
                if ($result['ok'] ?? false) {
                    $ok++;
                } else {
                    $fail++;
                    SmLiteSyncRuntime::deadLetter(
                        $batchKey,
                        'legacy_to_smlite',
                        'one_time_patrolicctv',
                        'patrolicctv',
                        (string) $row->id_patrolicctv,
                        (array) $row,
                        (string) ($result['message'] ?? 'sinkron patroli gagal')
                    );
                }
            }
        }

        if ($sources['layanan_masyarakat']) {
            $q = DB::table('layanan_masyarakat')->orderBy('id_interaksi');
            if ($limit > 0) {
                $q->limit($limit);
            }
            foreach ($q->get() as $row) {
                $total++;
                try {
                    LayananToOpsdalSync::syncFromLayananId((int) $row->id_interaksi);
                    $ok++;
                } catch (\Throwable $e) {
                    $fail++;
                    SmLiteSyncRuntime::deadLetter(
                        $batchKey,
                        'legacy_to_smlite',
                        'one_time_layanan',
                        'layanan_masyarakat',
                        (string) $row->id_interaksi,
                        (array) $row,
                        $e->getMessage()
                    );
                }
            }
        }

        SmLiteSyncRuntime::finishBatch($batchKey, $fail > 0 ? 'partial' : 'success', $total, $ok, $fail);

        if ($dryRun) {
            DB::rollBack();
            $this->warn('DRY-RUN aktif: semua perubahan migrasi dibatalkan.');
        }
    } catch (\Throwable $e) {
        if ($dryRun) {
            DB::rollBack();
        }
        SmLiteSyncRuntime::finishBatch($batchKey, 'failed', $total, $ok, $fail, 0, $e->getMessage());
        $this->error('Migrasi one-time gagal: '.$e->getMessage());

        return 1;
    }

    $this->info("One-time migration selesai. total={$total} ok={$ok} fail={$fail}");

    return $fail > 0 ? 1 : 0;
})->purpose('Eksekusi migrasi one-time SM lama ke SM-Lite (opsi dry-run)');

Artisan::command('sm-lite:sync-analytic-smlite {--limit=500 : Batas row per run} {--dry-run : Simulasi tanpa commit}', function () {
    if (! Schema::hasTable('traffic_counting_staging')) {
        $this->error('Tabel traffic_counting_staging belum tersedia.');

        return 1;
    }
    if (! AnalyticStagingToTrafficstuckSync::enabled()) {
        $this->error('Tabel target sync belum siap (trafficstuck/staging).');

        return 1;
    }

    $limit = max(1, (int) $this->option('limit'));
    $dryRun = (bool) $this->option('dry-run');
    $cursorKey = 'analytic_to_smlite_traffic';
    $watermark = SmLiteSyncRuntime::watermark($cursorKey);
    $lastId = (int) ($watermark['last_id'] ?? 0);

    $batchKey = SmLiteSyncRuntime::startBatch('analytic_to_smlite', 'traffic_counting', [
        'limit' => $limit,
        'dry_run' => $dryRun,
        'watermark_before' => $lastId,
    ]);

    $rows = DB::table('traffic_counting_staging')
        ->where('id', '>', $lastId)
        ->where('status', 'ready')
        ->orderBy('id')
        ->limit($limit)
        ->get();

    if ($rows->isEmpty()) {
        SmLiteSyncRuntime::finishBatch($batchKey, 'success', 0, 0, 0);
        $this->info('Tidak ada data Analytic baru untuk disinkronkan.');

        return 0;
    }

    $ok = 0;
    $fail = 0;
    $maxId = $lastId;

    if ($dryRun) {
        DB::beginTransaction();
    }

    try {
        foreach ($rows as $row) {
            $rid = (int) $row->id;
            $maxId = max($maxId, $rid);

            $result = AnalyticStagingToTrafficstuckSync::upsertFromStaging($rid, (array) $row);
            if ($result['ok'] ?? false) {
                $ok++;
                DB::table('traffic_counting_staging')->where('id', $rid)->update([
                    'status' => 'synced',
                    'synced_at' => now(),
                    'batch_key' => $batchKey,
                    'updated_at' => now(),
                ]);
            } else {
                $fail++;
                SmLiteSyncRuntime::deadLetter(
                    $batchKey,
                    'analytic_to_smlite',
                    'traffic_counting',
                    'traffic_counting_staging',
                    (string) $rid,
                    (array) $row,
                    (string) ($result['message'] ?? 'sync staging gagal')
                );
            }
        }

        SmLiteSyncRuntime::setWatermark($cursorKey, $maxId, now()->toDateTimeString());
        SmLiteSyncRuntime::finishBatch($batchKey, $fail > 0 ? 'partial' : 'success', $rows->count(), $ok, $fail);

        if ($dryRun) {
            DB::rollBack();
            $this->warn('DRY-RUN aktif: perubahan sync dibatalkan.');
        }
    } catch (\Throwable $e) {
        if ($dryRun) {
            DB::rollBack();
        }
        SmLiteSyncRuntime::finishBatch($batchKey, 'failed', $rows->count(), $ok, $fail, 0, $e->getMessage());
        $this->error('Sinkron Analytic -> SM-Lite gagal: '.$e->getMessage());

        return 1;
    }

    $this->info("Sync Analytic -> SM-Lite selesai. total={$rows->count()} ok={$ok} fail={$fail} watermark={$maxId}");

    return $fail > 0 ? 1 : 0;
})->purpose('Cron sinkron berkala Analytic staging ke SM-Lite (idempotent + watermark)');

Artisan::command('sm-lite:sync-smlite-dwh {--limit=500 : Batas row per run} {--dry-run : Simulasi tanpa commit}', function () {
    if (! Schema::hasTable('trafficstuck') || ! Schema::hasTable('dwh_traffic_sampling')) {
        $this->error('Tabel sumber/target DWH belum tersedia.');

        return 1;
    }

    $limit = max(1, (int) $this->option('limit'));
    $dryRun = (bool) $this->option('dry-run');
    $cursorKey = 'smlite_to_dwh_traffic';
    $watermark = SmLiteSyncRuntime::watermark($cursorKey);
    $lastId = (int) ($watermark['last_id'] ?? 0);
    $batchKey = SmLiteSyncRuntime::startBatch('smlite_to_dwh', 'traffic_sampling', [
        'limit' => $limit,
        'dry_run' => $dryRun,
        'watermark_before' => $lastId,
    ]);

    $rows = DB::table('trafficstuck')
        ->where('id_trafficstuck', '>', $lastId)
        ->orderBy('id_trafficstuck')
        ->limit($limit)
        ->get();

    if ($rows->isEmpty()) {
        SmLiteSyncRuntime::finishBatch($batchKey, 'success', 0, 0, 0);
        $this->info('Tidak ada data SM-Lite baru untuk dikirim ke DWH.');

        return 0;
    }

    $ok = 0;
    $fail = 0;
    $maxId = $lastId;
    $parseVolume = static function (?string $ketcb): array {
        $text = (string) $ketcb;
        $r2 = $r4 = $r6 = $r6plus = 0;

        if (preg_match('/R2\/R4\/R6\/R6\+:\s*(\d+)\/(\d+)\/(\d+)\/(\d+)/i', $text, $m)) {
            return [
                'r2' => (int) $m[1],
                'r4' => (int) $m[2],
                'r6' => (int) $m[3],
                'r6_plus' => (int) $m[4],
            ];
        }
        if (preg_match('/R2\/R4\/R6:\s*(\d+)\/(\d+)\/(\d+)/i', $text, $m)) {
            return [
                'r2' => (int) $m[1],
                'r4' => (int) $m[2],
                'r6' => (int) $m[3],
                'r6_plus' => 0,
            ];
        }

        return ['r2' => $r2, 'r4' => $r4, 'r6' => $r6, 'r6_plus' => $r6plus];
    };

    if ($dryRun) {
        DB::beginTransaction();
    }

    try {
        foreach ($rows as $row) {
            $rid = (int) $row->id_trafficstuck;
            $maxId = max($maxId, $rid);

            try {
                $vol = $parseVolume(isset($row->ketcb) ? (string) $row->ketcb : '');
                $klass = SmLiteTrafficVolumeClassifier::classify($vol['r2'], $vol['r4'], $vol['r6'] + $vol['r6_plus']);

                $payload = [
                    'source_record_id' => $rid,
                    'event_date' => $row->tanggal ?? null,
                    'event_time' => $row->jamperistiwa ?? null,
                    'lokasi' => mb_substr((string) ($row->lokasi ?? ''), 0, 150),
                    'sb_grp' => isset($row->sb_grp) ? (string) $row->sb_grp : null,
                    'faktor' => isset($row->faktor) ? mb_substr((string) $row->faktor, 0, 100) : null,
                    'penyebab' => isset($row->penyebab) ? mb_substr((string) $row->penyebab, 0, 100) : null,
                    'volume_class' => $klass['volume_class'],
                    'volume_r2' => $vol['r2'],
                    'volume_r4' => $vol['r4'],
                    'volume_r6' => $vol['r6'],
                    'volume_r6_plus' => $vol['r6_plus'],
                    'total_volume' => $vol['r2'] + $vol['r4'] + $vol['r6'] + $vol['r6_plus'],
                    'batch_key' => $batchKey,
                    'sync_timestamp' => now(),
                    'updated_at' => now(),
                ];

                $exists = DB::table('dwh_traffic_sampling')
                    ->where('source_record_id', $rid)
                    ->exists();

                if ($exists) {
                    DB::table('dwh_traffic_sampling')
                        ->where('source_record_id', $rid)
                        ->update($payload);
                } else {
                    $payload['created_at'] = now();
                    DB::table('dwh_traffic_sampling')->insert($payload);
                }

                $ok++;
            } catch (\Throwable $e) {
                $fail++;
                SmLiteSyncRuntime::deadLetter(
                    $batchKey,
                    'smlite_to_dwh',
                    'traffic_sampling',
                    'trafficstuck',
                    (string) $rid,
                    (array) $row,
                    $e->getMessage()
                );
            }
        }

        SmLiteSyncRuntime::setWatermark($cursorKey, $maxId, now()->toDateTimeString());
        SmLiteSyncRuntime::finishBatch($batchKey, $fail > 0 ? 'partial' : 'success', $rows->count(), $ok, $fail);

        if ($dryRun) {
            DB::rollBack();
            $this->warn('DRY-RUN aktif: perubahan export DWH dibatalkan.');
        }
    } catch (\Throwable $e) {
        if ($dryRun) {
            DB::rollBack();
        }
        SmLiteSyncRuntime::finishBatch($batchKey, 'failed', $rows->count(), $ok, $fail, 0, $e->getMessage());
        $this->error('Sync SM-Lite -> DWH gagal: '.$e->getMessage());

        return 1;
    }

    $this->info("Sync SM-Lite -> DWH selesai. total={$rows->count()} ok={$ok} fail={$fail} watermark={$maxId}");

    return $fail > 0 ? 1 : 0;
})->purpose('Cron export traffic SM-Lite ke tabel DWH dengan retry/dead-letter');

Artisan::command('sm-lite:reconcile {--sample=25 : Jumlah sampel hash} {--json : Output JSON}', function () {
    $sample = max(1, (int) $this->option('sample'));
    $report = [
        'generated_at' => now()->toDateTimeString(),
        'counts' => [],
        'samples' => [],
    ];

    if (Schema::hasTable('trafficdalkot') && Schema::hasTable('trafficstuck')) {
        $srcCount = (int) DB::table('trafficdalkot')->count();
        $dstCount = Schema::hasColumn('trafficstuck', 'id_trafficdalkot')
            ? (int) DB::table('trafficstuck')->whereNotNull('id_trafficdalkot')->count()
            : 0;
        $report['counts'][] = [
            'pair' => 'trafficdalkot -> trafficstuck',
            'source_count' => $srcCount,
            'target_count' => $dstCount,
            'delta' => $srcCount - $dstCount,
        ];

        if (Schema::hasColumn('trafficstuck', 'id_trafficdalkot')) {
            $links = DB::table('trafficstuck')
                ->whereNotNull('id_trafficdalkot')
                ->inRandomOrder()
                ->limit($sample)
                ->pluck('id_trafficdalkot')
                ->all();

            foreach ($links as $id) {
                $s = DB::table('trafficdalkot')->where('id_trafficdalkot', $id)->first();
                $t = DB::table('trafficstuck')->where('id_trafficdalkot', $id)->first();
                if (! $s || ! $t) {
                    continue;
                }
                $srcSig = md5(strtolower(trim((string) ($s->tanggal ?? ''))).'|'.strtolower(trim((string) ($s->namajalan ?? ''))));
                $dstSig = md5(strtolower(trim((string) ($t->tanggal ?? ''))).'|'.strtolower(trim((string) ($t->lokasi ?? ''))));
                $report['samples'][] = [
                    'pair' => 'trafficdalkot -> trafficstuck',
                    'source_id' => (int) $id,
                    'hash_match' => $srcSig === $dstSig,
                ];
            }
        }
    }

    if (Schema::hasTable('patrolicctv') && Schema::hasTable('trafficstuck') && Schema::hasTable('contigency')) {
        $src = (int) DB::table('patrolicctv')->count();
        $dst1 = Schema::hasColumn('trafficstuck', 'id_patrolicctv')
            ? (int) DB::table('trafficstuck')->whereNotNull('id_patrolicctv')->count()
            : 0;
        $dst2 = Schema::hasColumn('contigency', 'id_patrolicctv')
            ? (int) DB::table('contigency')->whereNotNull('id_patrolicctv')->count()
            : 0;
        $report['counts'][] = [
            'pair' => 'patrolicctv -> trafficstuck/contigency',
            'source_count' => $src,
            'target_count' => $dst1 + $dst2,
            'delta' => $src - ($dst1 + $dst2),
        ];
    }

    if ($this->option('json')) {
        $this->line(json_encode($report, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    } else {
        $this->info('Rekonsiliasi SM-Lite:');
        foreach ($report['counts'] as $row) {
            $this->line("- {$row['pair']} | source={$row['source_count']} target={$row['target_count']} delta={$row['delta']}");
        }
        $sampleMismatches = collect($report['samples'])->where('hash_match', false)->count();
        $this->line("- sample_checked=".count($report['samples'])." mismatch={$sampleMismatches}");
    }

    return 0;
})->purpose('Validasi & rekonsiliasi row-count + hash sample pasca migrasi/sync');

Artisan::command('sm-lite:cutover {module : Nama modul (traffic,opsdal,layanan,all)} {state : freeze|unfreeze|activate|deactivate} {--note= : Catatan}', function (string $module, string $state) {
    if (! Schema::hasTable('sm_cutover_controls')) {
        $this->error('Tabel sm_cutover_controls belum tersedia. Jalankan migrate.');

        return 1;
    }

    $state = strtolower(trim($state));
    if (! in_array($state, ['freeze', 'unfreeze', 'activate', 'deactivate'], true)) {
        $this->error('State tidak valid. Gunakan: freeze|unfreeze|activate|deactivate');

        return 1;
    }

    $modules = $module === 'all' ? ['traffic', 'opsdal', 'layanan'] : [strtolower(trim($module))];
    $updatedBy = (string) (config('app.name', 'system').':artisan');
    $note = mb_substr((string) $this->option('note'), 0, 255);

    foreach ($modules as $m) {
        $current = DB::table('sm_cutover_controls')->where('module', $m)->first();
        $legacyFrozen = (bool) ($current->legacy_write_frozen ?? false);
        $smlitePrimary = (bool) ($current->smlite_primary ?? false);

        if ($state === 'freeze') {
            $legacyFrozen = true;
        } elseif ($state === 'unfreeze') {
            $legacyFrozen = false;
        } elseif ($state === 'activate') {
            $smlitePrimary = true;
        } elseif ($state === 'deactivate') {
            $smlitePrimary = false;
        }

        $payload = [
            'module' => $m,
            'legacy_write_frozen' => $legacyFrozen,
            'smlite_primary' => $smlitePrimary,
            'effective_at' => now(),
            'updated_by' => $updatedBy,
            'notes' => $note !== '' ? $note : null,
            'updated_at' => now(),
        ];

        if ($current) {
            DB::table('sm_cutover_controls')->where('module', $m)->update($payload);
        } else {
            DB::table('sm_cutover_controls')->insert($payload);
        }
    }

    $this->info('Cutover state berhasil diperbarui.');

    return 0;
})->purpose('Kontrol freeze legacy dan aktivasi SM-Lite per modul');

Artisan::command('sm-lite:hypercare-status {--hours=24 : Window jam laporan}', function () {
    if (! Schema::hasTable('sm_sync_batches') || ! Schema::hasTable('sm_sync_dead_letters')) {
        $this->error('Tabel monitoring sync belum tersedia.');

        return 1;
    }

    $hours = max(1, (int) $this->option('hours'));
    $from = now()->subHours($hours);

    $batches = DB::table('sm_sync_batches')
        ->where('created_at', '>=', $from)
        ->orderByDesc('id')
        ->get();

    $dead = DB::table('sm_sync_dead_letters')
        ->where('created_at', '>=', $from)
        ->select('direction', 'module', DB::raw('COUNT(*) as total'))
        ->groupBy('direction', 'module')
        ->get();

    $this->info("Hypercare status {$hours} jam terakhir:");
    $this->line('- total batch: '.$batches->count());

    $statusCounts = $batches->groupBy('status')->map->count()->all();
    foreach ($statusCounts as $status => $count) {
        $this->line("  - status {$status}: {$count}");
    }

    if ($dead->isEmpty()) {
        $this->line('- dead-letter: 0');
    } else {
        $this->line('- dead-letter:');
        foreach ($dead as $row) {
            $this->line("  - {$row->direction} / {$row->module}: {$row->total}");
        }
    }

    return 0;
})->purpose('Monitoring hypercare: ringkasan batch sync, fail, dan dead-letter');
