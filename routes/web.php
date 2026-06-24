<?php

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\NumericCaptchaRefreshController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\RoleController;
use App\Http\Controllers\Admin\RolePermissionController;
use App\Http\Controllers\TurjagwaliController;
use App\Http\Controllers\AuditarusController;
use App\Http\Controllers\PnpangkutanController;
use App\Http\Controllers\ContigencyController;
use App\Http\Controllers\TrafficstuckController;
use App\Http\Controllers\TrafficsController;
use App\Http\Controllers\ProfilposkoController;
use App\Http\Controllers\ArusmudikController;
use App\Http\Controllers\OpsWanjolController;
use App\Http\Controllers\MapController;
use App\Http\Controllers\TerminalController;
use App\Http\Controllers\JalanGangguanController;
use App\Http\Controllers\DataJalanController;
use App\Http\Controllers\ObjekWisataController;
use App\Http\Controllers\KunjunganWisataController;
use App\Http\Controllers\TempatIbadahController;
use App\Http\Controllers\FaskesController;
use App\Http\Controllers\CctvController;
use App\Http\Controllers\ItSupportController;
use App\Http\Controllers\DataPerangkatController;
use App\Http\Controllers\DataCctvController;
use App\Http\Controllers\Master\WilayahController as MasterWilayahController;
use App\Http\Controllers\Master\DaerahController as MasterDaerahController;
use App\Http\Controllers\Master\OrganisasiController as MasterOrganisasiController;
use App\Http\Controllers\Master\ReferensiController as MasterReferensiController;
use App\Http\Controllers\Master\PemerintahanWilayahController as MasterPemerintahanWilayahController;
use App\Http\Controllers\Master\PemerintahanInstansiController as MasterPemerintahanInstansiController;
use App\Http\Controllers\Master\MasterKlUnitKerjaController;
use App\Http\Controllers\Master\MasterKlPusatController;
use App\Http\Controllers\Master\MasterKlDaerahController;
use App\Http\Controllers\Master\MasterKlResortController;
use App\Http\Controllers\Wil\WilArusmudikController;
use App\Http\Controllers\Da\DaArusmudikController;
use App\Http\Controllers\Nas\NasArusmudikController;
use App\Http\Controllers\PosyanPublicController;
use App\Http\Controllers\PengunjungPosyanController;
use App\Http\Controllers\InteraksiLayananController;
use App\Http\Controllers\DatapnpLayananController;
use App\Http\Controllers\LayananDataController;
use App\Http\Controllers\MediaPublishingController;
use App\Http\Controllers\MediaAnalyticController;
use App\Http\Controllers\PersonilPosyanController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Sm\SmDataController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

// ============================================
// PUBLIC ROUTES (No Authentication Required)
// ============================================

// Login Routes
Route::get('/', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('/login', [LoginController::class, 'login'])->name('login.post');
Route::post('/auth/captcha/refresh-math', [NumericCaptchaRefreshController::class, 'refreshMath'])
    ->name('auth.captcha.refresh-math');

Route::middleware('guest')->group(function () {
    Route::get('/register', [RegisterController::class, 'showRegistrationForm'])->name('register');
    Route::post('/register', [RegisterController::class, 'register'])->name('register.post');
});

// Peta Monitoring (dari index.html - Leaflet) - Public before login
Route::get('/map', [MapController::class, 'index'])->name('map.index');
Route::post('/map/update-koordinat', [MapController::class, 'updateKoordinat'])->name('map.update-koordinat')->middleware('auth');

Route::get('/debug-db-structure', function () {
    $health = \App\Support\DatabaseConnections::healthCheck();
    $lines = ["STATUS 2 DATABASE APLIKASI\n"];

    foreach (['operational', 'territory'] as $key) {
        $db = $health[$key];
        $label = $key === 'operational' ? 'DB UTAMA (operasional)' : 'DB KEDUA (wilayah + master SM)';
        $lines[] = '=========================================';
        $lines[] = $label;
        $lines[] = "Koneksi : {$db['connection']}";
        $lines[] = "Database: {$db['database']}";
        $lines[] = 'Status  : '.($db['ok'] ? 'OK' : 'GAGAL');
        if ($db['error']) {
            $lines[] = "Error   : {$db['error']}";
        }
        if ($key === 'operational' && $db['ok']) {
            if (isset($db['polda'])) {
                $lines[] = 'Polda   : '.($db['polda'] ?? 0);
                $lines[] = 'Polres  : '.($db['polres'] ?? 0);
            }
        }
        if ($key === 'territory' && $db['ok'] && ! empty($db['tables'])) {
            $t = $db['tables'];
            $lines[] = "Tabel   : {$t['total']} total (wilayah {$t['wilayah']}, smd_mstr {$t['master']}, smd_tbl {$t['transaksi']}, lainnya {$t['lainnya']})";
        }
        $lines[] = '';
    }

    if ($health['territory']['ok']) {
        $lines[] = \App\Support\SmSecondDatabaseReport::render(\App\Support\DatabaseConnections::territory());
    }

    $output = implode("\n", $lines);
    file_put_contents(public_path('db_structure.txt'), $output);

    return response($output, 200, ['Content-Type' => 'text/plain; charset=UTF-8']);
});


// ============================================
// AUTHENTICATED ROUTES (Require Authentication)
// ============================================
Route::middleware(['auth', 'usm.active', 'role.mutation'])->group(function () {
    
    // Logout: GET shows confirm (bookmark/address bar); POST performs sign-out (CSRF).
    Route::get('/logout', [LoginController::class, 'showLogoutConfirm'])->name('logout.confirm');
    Route::post('/logout', [LoginController::class, 'logout'])->name('logout');
    
    // Dashboard/Home Route
    Route::get('/home', [HomeController::class, 'index'])->name('home');

    // Data SM — DB kedua (smd_mstr_* master + smd_tbl_* transaksi)
    Route::prefix('sm')->name('sm.')->group(function () {
        Route::get('master', [SmDataController::class, 'masterIndex'])->name('master.index');
        Route::get('master/{table}/{id}', [SmDataController::class, 'masterShow'])->name('master.show');
        Route::get('transaksi', [SmDataController::class, 'transaksiIndex'])->name('transaksi.index');
        Route::get('transaksi/{table}/{id}', [SmDataController::class, 'transaksiShow'])->name('transaksi.show');
    });

    // Profil akun sendiri (organisasi / sub org — setiap user)
    Route::get('/profil-saya/organisasi', [ProfileController::class, 'show'])->name('profile.show');
    Route::get('/profil-saya/organisasi/edit', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/profil-saya/organisasi', [ProfileController::class, 'update'])->name('profile.update');

    // SatuPeta — route utama ada di routes/satupeta.php (PemetaanNew, Pemetaan, Api_*)

    // Public Service & modul sbo (posyan, pengunjung, datapnp, apel, personil)
    Route::prefix('layanan')->name('layanan.')->group(function () {
        Route::get('public-service/export/{format}', [PosyanPublicController::class, 'export'])->name('public_service.export');
        Route::resource('public-service', PosyanPublicController::class)->names('public_service')->parameters(['public-service' => 'id']);

        Route::get('masyarakat/export/{format}', [InteraksiLayananController::class, 'export'])->name('masyarakat.export');
        Route::resource('masyarakat', InteraksiLayananController::class)->names('masyarakat')->parameters(['masyarakat' => 'id']);

        Route::get('data/export/{format}', [LayananDataController::class, 'export'])->name('data.export');
        Route::resource('data', LayananDataController::class)->names('data')->parameters(['data' => 'id']);
    });

    Route::prefix('media')->name('media.')->group(function () {
        Route::get('manage/export/{format}', [MediaPublishingController::class, 'export'])->name('manage.export');
        Route::resource('manage', MediaPublishingController::class)->names('manage')->parameters(['manage' => 'id']);

        Route::get('analytic/export/{format}', [MediaAnalyticController::class, 'export'])->name('analytic.export');
        Route::resource('analytic', MediaAnalyticController::class)->names('analytic')->parameters(['analytic' => 'id']);
    });

    Route::redirect('/mediaanalytic', '/media/analytic', 301);

    Route::prefix('personil')->name('personil.')->group(function () {
        Route::get('manager/export/{format}', [PersonilPosyanController::class, 'export'])->name('manager.export');
        Route::resource('manager', PersonilPosyanController::class)->names('manager')->parameters(['manager' => 'id']);
    });

    // Data Collecting — terminal, jalan, gangguan (gangguan tampil di peta)
    Route::resource('terminal', TerminalController::class);
    Route::get('terminal/export/{format}', [TerminalController::class, 'export'])->name('terminal.export');
    Route::redirect('data_jalan', '/t_datajalan', 301);
    Route::get('t_datajalan/export/{format}', [DataJalanController::class, 'export'])->name('t_datajalan.export');
    Route::resource('t_datajalan', DataJalanController::class)->parameters(['t_datajalan' => 'id']);
    Route::get('jalan_gangguan/export/{format}', [JalanGangguanController::class, 'export'])->name('jalan_gangguan.export');
    Route::resource('jalan_gangguan', JalanGangguanController::class);

    Route::get('/clear-cache', function() {
        \Illuminate\Support\Facades\Artisan::call('optimize:clear');
        return "Cache cleared!";
    });

    Route::get('/setup-db-latlng', function() {
        $tables = ['daerah', 'wilayah', 'master_provinsi', 'master_kabupaten_kota'];
        $log = [];

        foreach ($tables as $table) {
            if (\Illuminate\Support\Facades\Schema::hasTable($table)) {
                \Illuminate\Support\Facades\Schema::table($table, function (\Illuminate\Database\Schema\Blueprint $t) use ($table, &$log) {
                    if (!\Illuminate\Support\Facades\Schema::hasColumn($table, 'lat')) {
                        $t->string('lat', 50)->nullable();
                        $log[] = "Added 'lat' to {$table}";
                    }
                    if (!\Illuminate\Support\Facades\Schema::hasColumn($table, 'lng')) {
                        $t->string('lng', 50)->nullable();
                        $log[] = "Added 'lng' to {$table}";
                    }
                });
            }
        }

        if (empty($log)) {
            return "Tidak ada kolom yang perlu ditambahkan. Semua tabel sudah memiliki kolom lat dan lng, atau tabel tidak ditemukan.";
        }
        
        return "Berhasil memperbarui struktur database:<br>- " . implode("<br>- ", $log);
    });

    Route::get('/import-latlng', function() {
        $path = base_path('test.sql');
        if (!file_exists($path)) {
            return "File test.sql tidak ditemukan di {$path}";
        }

        $contents = file_get_contents($path);

        $hasDaerah = \Illuminate\Support\Facades\Schema::hasTable('daerah');
        $hasProvinsi = \Illuminate\Support\Facades\Schema::hasTable('master_provinsi');

        $poldaCount = 0;
        if (preg_match_all("/\(\d+,\s*'[^']*',\s*'([^']*)',\s*[^,]+,\s*'[^']*',\s*'([^']*)',\s*'([^']*)'/", $contents, $matches, PREG_SET_ORDER)) {
            foreach($matches as $m) {
                $poldaName = mb_strtoupper(trim($m[1]));
                $lat = $m[2] !== 'NULL' ? $m[2] : null;
                $lng = $m[3] !== 'NULL' ? $m[3] : null;

                if ($lat && $lng) {
                    if ($hasDaerah) \Illuminate\Support\Facades\DB::table('daerah')->where('namadaerah', $poldaName)->update(['lat' => $lat, 'lng' => $lng]);
                    if ($hasProvinsi) \Illuminate\Support\Facades\DB::table('master_provinsi')->where('nama', $poldaName)->update(['lat' => $lat, 'lng' => $lng]);
                    $poldaCount++;
                }
            }
        }

        $hasWilayah = \Illuminate\Support\Facades\Schema::hasTable('wilayah');
        $hasKabkot = \Illuminate\Support\Facades\Schema::hasTable('master_kabupaten_kota');

        $lines = explode("\n", $contents);
        $polresCount = 0;
        foreach($lines as $line) {
            if (preg_match("/\(\d+,\s*'[^']*',\s*'([^']*)',\s*'[^']*',\s*\d+,\s*\d+,\s*'([^']*)',\s*'([^']*)'/", $line, $m)) {
                $polresName = mb_strtoupper(trim($m[1]));
                // Hilangkan KABUPATEN / KOTA
                $polresNameClean = trim(str_replace(['KABUPATEN ', 'KOTA '], '', $polresName));
                
                $lat = $m[2] !== 'NULL' ? $m[2] : null;
                $lng = $m[3] !== 'NULL' ? $m[3] : null;

                if ($lat && $lng) {
                    if ($hasWilayah) \Illuminate\Support\Facades\DB::table('wilayah')->where('namawilayah', $polresNameClean)->update(['lat' => $lat, 'lng' => $lng]);
                    if ($hasKabkot) \Illuminate\Support\Facades\DB::table('master_kabupaten_kota')->where('nama', $polresNameClean)->update(['lat' => $lat, 'lng' => $lng]);
                    $polresCount++;
                }
            }
        }
        
        return "Berhasil mengimpor! <br>Polda yang diperbarui: {$poldaCount} <br>Polres yang diperbarui: {$polresCount}";
    });

    // ============================================
    // AUTOCOMPLETE API (JSON endpoints)
    // ============================================
    Route::get('api/autocomplete/jalan', function (\Illuminate\Http\Request $request) {
        $user = Auth::user();
        $q = trim((string) $request->get('q', ''));
        $query = \Illuminate\Support\Facades\DB::table('t_datajalan')
            ->join('usm', 't_datajalan.piket', '=', 'usm.id_usm')
            ->select('t_datajalan.namajalan');
        // Scope to user's polsek (organisasi_daerah) or polres (wilayah)
        $query->where(function($q) use ($user) {
            if (!empty($user->organisasi_daerah)) {
                $q->where('usm.organisasi_daerah', $user->organisasi_daerah)
                  ->orWhere(function($sq) use ($user) {
                      $sq->where('usm.wilayah', $user->wilayah)
                         ->where(function($sq2) {
                             $sq2->whereNull('usm.organisasi_daerah')
                                 ->orWhere('usm.organisasi_daerah', '');
                         });
                  });
            } elseif (!empty($user->wilayah)) {
                $q->where('usm.wilayah', $user->wilayah);
            } elseif (!empty($user->daerah)) {
                $q->where('usm.daerah', $user->daerah);
            }
        });
        if ($q !== '') {
            $query->where('t_datajalan.namajalan', 'like', "%{$q}%");
        }
        $results = $query->distinct()->orderBy('t_datajalan.namajalan')->limit(20)->pluck('t_datajalan.namajalan');
        return response()->json($results);
    })->name('api.autocomplete.jalan');

    Route::get('api/autocomplete/petugas', function (\Illuminate\Http\Request $request) {
        $user = Auth::user();
        $q = trim((string) $request->get('q', ''));
        $query = \Illuminate\Support\Facades\DB::table('usm')
            ->select('id_usm', 'nama', 'nrp_nip_nik')
            ->where('account_status', 'active');
        // Scope to user's polsek, polres, or polda
        if (!empty($user->organisasi_daerah)) {
            $query->where('organisasi_daerah', $user->organisasi_daerah);
        } elseif (!empty($user->wilayah)) {
            $query->where('wilayah', $user->wilayah);
        } elseif (!empty($user->daerah)) {
            $query->where('daerah', $user->daerah);
        }
        if ($q !== '') {
            $query->where(function($qb) use ($q) {
                $qb->where('nama', 'like', "%{$q}%")
                   ->orWhere('nrp_nip_nik', 'like', "%{$q}%");
            });
        }
        $results = $query->orderBy('nama')->limit(20)->get()->map(fn($u) => [
            'id'   => $u->id_usm,
            'nama' => $u->nama,
            'nrp'  => $u->nrp_nip_nik,
        ]);
        return response()->json($results);
    })->name('api.autocomplete.petugas');
    // ============================================
    // OPSDAL NOTIFICATION API
    // ============================================
    Route::get('api/opsdal-notifications', function (\Illuminate\Http\Request $request) {
        $lastLayananId = (int) $request->get('last_layanan_id', 0);
        $lastCctvId = (int) $request->get('last_cctv_id', 0);
        
        $user = Auth::user();
        $userDaerah = $user->daerah ?? '';
        $userWilayah = $user->wilayah ?? '';
        $userLevel = \App\Support\TerritoryLevelForUsm::resolve($user);

        $applyUserTerritoryScope = static function ($query) use ($userDaerah, $userWilayah) {
            // Aturan level: wilayah terisi => user wilayah; jika kosong lalu daerah terisi => user daerah; selain itu nasional.
            if (! empty($userWilayah)) {
                return $query->where('wilayah', $userWilayah);
            }

            if (! empty($userDaerah)) {
                return $query->where('daerah', $userDaerah);
            }

            return $query;
        };

        // 1. Cek layanan masyarakat (Public Service)
        $layananQ = \Illuminate\Support\Facades\DB::table('layanan_masyarakat')
            ->select('id_interaksi as id', 'tanggal', 'kategori as type', 'tema', 'keterangan', 'created_at')
            ->where(function($q) {
                $q->where('kategori', 'like', '%Kecelakaan%')
                  ->orWhere('tema', 'like', '%Kecelakaan%')
                  ->orWhere('kategori', 'like', '%Perlambatan%')
                  ->orWhere('tema', 'like', '%Perlambatan%')
                  ->orWhere('kategori', 'like', '%Kemacetan%')
                  ->orWhere('tema', 'like', '%Kemacetan%')
                  ->orWhere('kategori', 'like', '%Macet%')
                  ->orWhere('tema', 'like', '%Macet%')
                  ->orWhere('kategori', 'like', '%Kebakaran%')
                  ->orWhere('tema', 'like', '%Kebakaran%')
                  ->orWhere('kategori', 'like', '%Bencana%')
                  ->orWhere('tema', 'like', '%Bencana%')
                  ->orWhere('kategori', 'like', '%Aduan%');
            });
        $applyUserTerritoryScope($layananQ);
        
        if ($lastLayananId > 0) {
            $layananQ->where('id_interaksi', '>', $lastLayananId);
        } else {
            $layananQ->whereDate('tanggal', '>=', Carbon::today()->toDateString());
        }
        
        $layanan = $layananQ->get()->map(fn($item) => [
            'id' => $item->id,
            'source' => 'layanan_masyarakat',
            'label' => 'Public Service: ' . ($item->type ?: ($item->tema ?: '-')),
            'desc' => \Illuminate\Support\Str::limit($item->keterangan, 50),
            'url' => route('layanan.masyarakat.show', $item->id),
            'time' => $item->created_at ?? $item->tanggal
        ]);

        // 2. Cek Patroli CCTV
        $cctvTable = \App\Http\Controllers\CctvController::resolveCctvTableName();
        $cctvPk = \App\Http\Controllers\CctvController::resolveCctvPrimaryKey($cctvTable);
        $patrolCols = \App\Http\Controllers\CctvController::resolvePatrolColumns($cctvTable);
        
        $peristiwaCol = $patrolCols['peristiwa'] ?? 'peristiwa';
        $ketcbCol = $patrolCols['ketcb'] ?? 'ketcb';
        $tanggalCol = $patrolCols['tanggal'] ?? 'tanggal';
        
        $cctvQ = \Illuminate\Support\Facades\DB::table($cctvTable)
            ->select("$cctvTable.$cctvPk as id", "$cctvTable.$tanggalCol as tanggal", "$cctvTable.$peristiwaCol as type", "$cctvTable.$ketcbCol as keterangan")
            ->where(function($q) use ($cctvTable, $peristiwaCol) {
                $q->where("$cctvTable.$peristiwaCol", 'like', '%Kecelakaan%')
                  ->orWhere("$cctvTable.$peristiwaCol", 'like', '%Perlambatan%');
            });

        $cctvQ->leftJoin('usm', $cctvTable.'.piket', '=', 'usm.id_usm');
        if (! empty($userWilayah)) {
            $cctvQ->where('usm.wilayah', $userWilayah);
        } elseif (! empty($userDaerah)) {
            $cctvQ->where('usm.daerah', $userDaerah);
        }

        if ($lastCctvId > 0) {
            $cctvQ->where($cctvTable.'.'.$cctvPk, '>', $lastCctvId);
        } else {
            $cctvQ->whereDate($cctvTable.'.'.$tanggalCol, '>=', Carbon::today()->toDateString());
        }
        
        $cctv = $cctvQ->get()->map(fn($item) => [
            'id' => $item->id,
            'source' => 'patrolicctv',
            'label' => 'CCTV: ' . $item->type,
            'desc' => \Illuminate\Support\Str::limit($item->keterangan, 50),
            'url' => route('patrolicctv.show', $item->id),
            'time' => $item->tanggal
        ]);

        // 3. Cek Kodal Turjagwali
        $lastTurjagwaliId = (int) $request->get('last_turjagwali_id', 0);
        $turQ = \Illuminate\Support\Facades\DB::table('turjagwali')
            ->select('turjagwali.id_turjagwali as id', 'turjagwali.tanggal', 'turjagwali.katgiat as type', 'turjagwali.ketcb as keterangan', 'turjagwali.created_at');
        $turQ->leftJoin('usm', 'turjagwali.piket', '=', 'usm.id_usm');
        if (! empty($userWilayah)) {
            $turQ->where('usm.wilayah', $userWilayah);
        } elseif (! empty($userDaerah)) {
            $turQ->where('usm.daerah', $userDaerah);
        }
        
        if ($lastTurjagwaliId > 0) {
            $turQ->where('turjagwali.id_turjagwali', '>', $lastTurjagwaliId);
        } else {
            $turQ->whereDate('turjagwali.tanggal', '>=', Carbon::today()->toDateString());
        }
        
        $turjagwali = $turQ->get()->map(fn($item) => [
            'id' => $item->id,
            'source' => 'turjagwali',
            'label' => 'Turjagwali: ' . $item->type,
            'desc' => \Illuminate\Support\Str::limit($item->keterangan, 50),
            'url' => route('turjagwali.show', $item->id),
            'time' => $item->created_at ?? $item->tanggal
        ]);

        // 4. Cek Kodal Contigency
        $lastContigencyId = (int) $request->get('last_contigency_id', 0);
        $conQ = \Illuminate\Support\Facades\DB::table('contigency')
            ->select('contigency.id_contigency as id', 'contigency.tanggal', 'contigency.jeniscontigency as type', 'contigency.ketcb as keterangan', 'contigency.created_at', 'contigency.source_module');
        $conQ->leftJoin('usm', 'contigency.piket', '=', 'usm.id_usm');
        if (! empty($userWilayah)) {
            $conQ->where('usm.wilayah', $userWilayah);
        } elseif (! empty($userDaerah)) {
            $conQ->where('usm.daerah', $userDaerah);
        }
        
        if (\Illuminate\Support\Facades\Schema::hasColumn('contigency', 'sb_grp')) {
            $conQ->where(function($q) {
                if (\Illuminate\Support\Facades\Schema::hasColumn('contigency', 'id_patrolicctv')) {
                    $q->whereNotNull('contigency.id_patrolicctv')->orWhere('contigency.sb_grp', 'opsdal');
                } else {
                    $q->where('contigency.sb_grp', 'opsdal');
                }
            });
        }
        if ($lastContigencyId > 0) {
            $conQ->where('contigency.id_contigency', '>', $lastContigencyId);
        } else {
            $conQ->whereDate('contigency.tanggal', '>=', Carbon::today()->toDateString());
        }
        
        $contigency = $conQ->get()->map(function ($item) {
            $fromLayanan = ($item->source_module ?? '') === 'layanan_masyarakat';

            return [
                'id' => $item->id,
                'source' => 'contigency',
                'label' => ($fromLayanan ? 'Contigency (Dari Layanan): ' : 'Contigency: ') . $item->type,
                'desc' => \Illuminate\Support\Str::limit($item->keterangan, 50),
                'url' => route('contigency.show', ['contigency' => $item->id, 'sb_grp' => 'opsdal']),
                'time' => $item->created_at ?? $item->tanggal,
            ];
        });

        // 5. Cek Kodal Perlambatan (Trafficstuck)
        $lastTrafficstuckId = (int) $request->get('last_trafficstuck_id', 0);
        $tsQ = \Illuminate\Support\Facades\DB::table('trafficstuck')
            ->select('trafficstuck.id_trafficstuck as id', 'trafficstuck.tanggal', 'trafficstuck.faktor as type', 'trafficstuck.ketcb as keterangan', 'trafficstuck.created_at', 'trafficstuck.source_module');
        $tsQ->leftJoin('usm', 'trafficstuck.piket', '=', 'usm.id_usm');
        if (! empty($userWilayah)) {
            $tsQ->where('usm.wilayah', $userWilayah);
        } elseif (! empty($userDaerah)) {
            $tsQ->where('usm.daerah', $userDaerah);
        }
        
        if (\Illuminate\Support\Facades\Schema::hasColumn('trafficstuck', 'sb_grp')) {
            $tsQ->where(function($q) {
                if (\Illuminate\Support\Facades\Schema::hasColumn('trafficstuck', 'id_patrolicctv')) {
                    $q->whereNotNull('trafficstuck.id_patrolicctv')->orWhere('trafficstuck.sb_grp', 'opsdal');
                } else {
                    $q->where('trafficstuck.sb_grp', 'opsdal');
                }
            });
        }
        if ($lastTrafficstuckId > 0) {
            $tsQ->where('trafficstuck.id_trafficstuck', '>', $lastTrafficstuckId);
        } else {
            $tsQ->whereDate('trafficstuck.tanggal', '>=', Carbon::today()->toDateString());
        }
        
        $trafficstuck = $tsQ->get()->map(function ($item) {
            $fromLayanan = ($item->source_module ?? '') === 'layanan_masyarakat';

            return [
                'id' => $item->id,
                'source' => 'trafficstuck',
                'label' => ($fromLayanan ? 'Perlambatan (Dari Layanan): ' : 'Perlambatan: ') . $item->type,
                'desc' => \Illuminate\Support\Str::limit($item->keterangan, 50),
                'url' => route('trafficstuck.show', ['trafficstuck' => $item->id, 'sb_grp' => 'opsdal']),
                'time' => $item->created_at ?? $item->tanggal,
            ];
        });

        $items = $layanan->merge($cctv)->merge($turjagwali)->merge($contigency)->merge($trafficstuck)->sortByDesc('time')->values();
        
        return response()->json([
            'items' => $items,
            'max_layanan_id' => $layanan->max('id') ?? $lastLayananId,
            'max_cctv_id' => $cctv->max('id') ?? $lastCctvId,
            'max_turjagwali_id' => $turjagwali->max('id') ?? $lastTurjagwaliId,
            'max_contigency_id' => $contigency->max('id') ?? $lastContigencyId,
            'max_trafficstuck_id' => $trafficstuck->max('id') ?? $lastTrafficstuckId,
            'has_new' => $items->count() > 0,
            'user_level' => $userLevel,
            'scope' => [
                'daerah' => $userDaerah ?: null,
                'wilayah' => $userWilayah ?: null,
            ],
        ]);
    })->name('api.opsdal-notifications');

    // ============================================
    // GIAT RUTIN ROUTES (Full CRUD + Export)
    // ============================================
    Route::get('turjagwali/export/{format}', [TurjagwaliController::class, 'export'])->name('turjagwali.export');
    Route::resource('turjagwali', TurjagwaliController::class);
    Route::get('auditarus/export/{format}', [AuditarusController::class, 'export'])->name('auditarus.export');
    Route::resource('auditarus', AuditarusController::class);
    Route::get('pnpangkutan/export/{format}', [PnpangkutanController::class, 'export'])->name('pnpangkutan.export');
    Route::resource('pnpangkutan', PnpangkutanController::class);
    Route::get('traffics/export/{format}', [TrafficsController::class, 'export'])->name('traffics.export');
    Route::resource('traffics', TrafficsController::class)->parameters(['traffics' => 'id']);
    Route::get('report/traffic-analytic', [TrafficstuckController::class, 'reportTrafficAnalytic'])
        ->name('report.traffic-analytic.index');
    Route::redirect('trafficdalkot', '/report/traffic-analytic', 301);
    Route::redirect('trafficstuck/giat-rutin', '/report/traffic-analytic', 301);
    
    // ============================================
    // QUICK RESPONS ROUTES (Full CRUD + Export)
    // ============================================
    Route::get('contigency/export/{format}', [ContigencyController::class, 'export'])->name('contigency.export');
    Route::resource('contigency', ContigencyController::class);
    Route::post('contigency/{contigency}/confirm', [ContigencyController::class, 'confirmOpsdal'])->name('contigency.confirm');
    Route::post('contigency/{contigency}/reject', [ContigencyController::class, 'rejectOpsdal'])->name('contigency.reject');
    Route::get('trafficstuck/export/{format}', [TrafficstuckController::class, 'export'])->name('trafficstuck.export');
    Route::resource('trafficstuck', TrafficstuckController::class);
    Route::post('trafficstuck/{trafficstuck}/confirm', [TrafficstuckController::class, 'confirmOpsdal'])->name('trafficstuck.confirm');
    Route::post('trafficstuck/{trafficstuck}/reject', [TrafficstuckController::class, 'rejectOpsdal'])->name('trafficstuck.reject');
    
    // ============================================
    // OPS KEPOLISIAN ROUTES (Level Operasional)
    // ============================================
    Route::get('profilposko/export/{format}', [ProfilposkoController::class, 'export'])->name('profilposko.export');
    Route::resource('profilposko', ProfilposkoController::class);
    
    // Arus Mudik & Balik - Full CRUD
    Route::resource('arusmudik', ArusmudikController::class);
    Route::post('arusmudik/{id}/edit-field', [ArusmudikController::class, 'editField'])->name('arusmudik.edit-field');
    Route::get('arusmudik/export/{format}', [ArusmudikController::class, 'export'])->name('arusmudik.export');
    
    // Peristiwa Menonjol (Ops Wanjol) - Full CRUD
    Route::get('ops_wanjol/export/{format}', [OpsWanjolController::class, 'export'])->name('ops_wanjol.export');
    Route::post('ops_wanjol/{id}/edit-field', [OpsWanjolController::class, 'editField'])->name('ops_wanjol.edit-field');
    Route::resource('ops_wanjol', OpsWanjolController::class);
    
    // ============================================
    // LEVEL WILAYAH ROUTES (Read-Only)
    // ============================================
    Route::prefix('wil')->name('wil.')->group(function () {
        Route::get('arusmudik', [WilArusmudikController::class, 'index'])->name('arusmudik.index');
        Route::get('arusmudik/export/{format}', [WilArusmudikController::class, 'export'])->name('arusmudik.export');
    });
    
    // ============================================
    // LEVEL DAERAH ROUTES (Read-Only)
    // ============================================
    Route::prefix('da')->name('da.')->group(function () {
        Route::get('arusmudik', [DaArusmudikController::class, 'index'])->name('arusmudik.index');
        Route::get('arusmudik/export/{format}', [DaArusmudikController::class, 'export'])->name('arusmudik.export');
    });
    
    // ============================================
    // LEVEL NASIONAL ROUTES (Read-Only)
    // ============================================
    Route::prefix('nas')->name('nas.')->group(function () {
        Route::get('arusmudik', [NasArusmudikController::class, 'index'])->name('arusmudik.index');
        Route::get('arusmudik/export/{format}', [NasArusmudikController::class, 'export'])->name('arusmudik.export');
    });
    
    // ============================================
    // WISATA & IBADAH
    // ============================================
    Route::get('objekwisata/export/{format}', [ObjekWisataController::class, 'export'])->name('objekwisata.export');
    Route::resource('objekwisata', ObjekWisataController::class);
    Route::get('kunjunganwisata/export/{format}', [KunjunganWisataController::class, 'export'])->name('kunjunganwisata.export');
    Route::resource('kunjunganwisata', KunjunganWisataController::class);
    Route::get('tempatibadah/export/{format}', [TempatIbadahController::class, 'export'])->name('tempatibadah.export');
    Route::resource('tempatibadah', TempatIbadahController::class);
    Route::get('faskes/export/{format}', [FaskesController::class, 'export'])->name('faskes.export');
    Route::resource('faskes', FaskesController::class);

    // ============================================
    // TIK — Data CCTV inventaris (view wil_datacctv / nas_datacctv)
    // URL utama data-cctv (hindari cache 301 legacy datacctv → patrolicctv di browser)
    // ============================================
    Route::get('data-cctv/export/{format}', [DataCctvController::class, 'export'])->name('datacctv.export');
    Route::resource('data-cctv', DataCctvController::class)
        ->names('datacctv')
        ->parameters(['data-cctv' => 'id']);
    Route::redirect('datacctv', '/data-cctv', 301);

    // ============================================
    // PATROLI ONLINE — Monitoring CCTV (tabel patrolicctv)
    // ============================================
    $registerPatrolCctvRoutes = function (string $uriPrefix, string $namePrefix): void {
        Route::get("{$uriPrefix}/export/{format}", [CctvController::class, 'export'])
            ->name("{$namePrefix}.export");
        Route::get($uriPrefix, [CctvController::class, 'index'])->name("{$namePrefix}.index");
        Route::get("{$uriPrefix}/create", [CctvController::class, 'create'])->name("{$namePrefix}.create");
        Route::post($uriPrefix, [CctvController::class, 'store'])->name("{$namePrefix}.store");
        Route::get("{$uriPrefix}/{id}", [CctvController::class, 'show'])
            ->name("{$namePrefix}.show")
            ->whereNumber('id');
        Route::get("{$uriPrefix}/{id}/edit", [CctvController::class, 'edit'])
            ->name("{$namePrefix}.edit")
            ->whereNumber('id');
        Route::put("{$uriPrefix}/{id}", [CctvController::class, 'update'])
            ->name("{$namePrefix}.update")
            ->whereNumber('id');
        Route::patch("{$uriPrefix}/{id}", [CctvController::class, 'update'])
            ->whereNumber('id');
        Route::delete("{$uriPrefix}/{id}", [CctvController::class, 'destroy'])
            ->name("{$namePrefix}.destroy")
            ->whereNumber('id');
    };

    $registerPatrolCctvRoutes('patrolicctv', 'patrolicctv');
    $registerPatrolCctvRoutes('cctv', 'cctv');

    // Live CCTV — stream RTSP via gateway SatuPeta
    Route::prefix('cctv-live')->name('cctv-live.')->group(function () {
        Route::get('/', [\App\Http\Controllers\CctvLiveController::class, 'index'])->name('index');
        Route::get('/debug-connection', [\App\Http\Controllers\CctvLiveController::class, 'debugConnection'])->name('debug');
        Route::get('/{id}', [\App\Http\Controllers\CctvLiveController::class, 'show'])->name('show')->whereNumber('id');
        Route::post('/{id}/start', [\App\Http\Controllers\CctvLiveController::class, 'startStream'])->name('start')->whereNumber('id');
        Route::post('/{id}/stop', [\App\Http\Controllers\CctvLiveController::class, 'stopStream'])->name('stop')->whereNumber('id');
    });

    Route::get('dataperangkat/export/{format}', [DataPerangkatController::class, 'export'])->name('dataperangkat.export');
    Route::resource('dataperangkat', DataPerangkatController::class)->parameters(['dataperangkat' => 'id']);
    Route::get('itsupport/export/{format}', [ItSupportController::class, 'export'])->name('itsupport.export');
    Route::resource('itsupport', ItSupportController::class);

    // ============================================
    // MASTER DATA (Pusat, Daerah, Resort, Referensi)
    // ============================================
    Route::prefix('master')->name('master.')->group(function () {
        Route::get('wilayah', function (Request $request): RedirectResponse {
            return redirect()->route('master.resort.index', $request->query());
        })->name('wilayah.redirect');
        Route::get('organisasi', function (Request $request): RedirectResponse {
            return redirect()->route('master.pusat.index', $request->query());
        })->name('organisasi.redirect');

        Route::middleware('superadmin')->group(function () {
            Route::resource('kl-pusat', MasterKlPusatController::class)->parameters(['kl-pusat' => 'id'])->except(['show']);
            Route::resource('kl-daerah', MasterKlDaerahController::class)->parameters(['kl-daerah' => 'id'])->except(['show']);
            Route::resource('kl-resort', MasterKlResortController::class)->parameters(['kl-resort' => 'id'])->except(['show']);
            Route::resource('kl-unit-kerja', MasterKlUnitKerjaController::class)->parameters(['kl-unit-kerja' => 'id'])->except(['show', 'index']);
            
            // Tambahan rute CREATE, EDIT, UPDATE, DESTROY untuk master tabel inti
            Route::resource('pusat', MasterOrganisasiController::class)->parameters(['pusat' => 'id'])->except(['index', 'show']);
            Route::resource('daerah', MasterDaerahController::class)->parameters(['daerah' => 'id'])->except(['index', 'show']);
            Route::resource('resort', MasterWilayahController::class)->parameters(['resort' => 'id'])->except(['index', 'show']);
            
            Route::post('referensi', [MasterReferensiController::class, 'store'])->name('referensi.store');
            Route::put('referensi/{id}', [MasterReferensiController::class, 'update'])->name('referensi.update');
            Route::delete('referensi/{id}', [MasterReferensiController::class, 'destroy'])->name('referensi.destroy');
        });

        Route::get('pusat', [MasterOrganisasiController::class, 'index'])->name('pusat.index');
        Route::get('pusat/{id}/sub-organisasi', [MasterOrganisasiController::class, 'suborganisasi'])->name('pusat.sub');
        Route::get('daerah', [MasterDaerahController::class, 'index'])->name('daerah.index');
        Route::get('kab-kota', function (Request $request): RedirectResponse {
            return redirect()->route('master.resort.index', $request->query());
        })->name('kab-kota.index');
        Route::get('resort', [MasterWilayahController::class, 'index'])->name('resort.index');
        Route::get('referensi', [MasterReferensiController::class, 'index'])->name('referensi.index');

        Route::get('pemerintahan-wilayah', [MasterPemerintahanWilayahController::class, 'index'])->name('pemerintahan-wilayah.index');
        Route::get('pemerintahan-instansi', [MasterPemerintahanInstansiController::class, 'index'])->name('pemerintahan-instansi.index');
        Route::get('unit-kerja', [MasterKlUnitKerjaController::class, 'index'])->name('unit-kerja.index');
    });

    // ============================================
    // ADMIN ROUTES (Require Permission Middleware)
    // ============================================
    Route::prefix('admin')->name('admin.')->middleware('permission')->group(function () {
        
        // Application Settings
        Route::get('settings/logo', [\App\Http\Controllers\Admin\AppSettingController::class, 'logo'])->name('settings.logo');
        Route::post('settings/logo', [\App\Http\Controllers\Admin\AppSettingController::class, 'updateLogo'])->name('settings.logo.update');

        // User Management Routes
        Route::get('users/export/{format}', [UserController::class, 'export'])->name('users.export');
        Route::get('users/export-excel', [UserController::class, 'exportExcel'])->name('users.export-excel');
        Route::post('users/{id}/verify', [UserController::class, 'verify'])->name('users.verify');
        Route::resource('users', UserController::class);
        Route::post('users/{id}/edit-field', [UserController::class, 'editField'])->name('users.edit-field');
        
        // Roles Routes
        Route::get('/roles', [RoleController::class, 'index'])->name('roles.index');
        
        // Role Permissions Routes
        Route::get('/role_permissions', [RolePermissionController::class, 'index'])->name('role_permissions.index');
        Route::post('/role_permissions/toggle', [RolePermissionController::class, 'toggle'])->name('role_permissions.toggle');
        
    });
    
});
