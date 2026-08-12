<?php
/**
 * Diagnostic Tool: Debugging Objek Wisata for Surakarta and Jawa Tengah
 * Place this in public/debug_gis_wisata.php and visit:
 * http://<your-host>/debug_gis_wisata.php
 */

define('LARAVEL_START', microtime(true));

require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Auth;
use App\Models\Usm;
use App\Helpers\HierarchicalHelper;
use App\Support\TerritoryIndexFilters;

header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Debug Objek Wisata - Surakarta</title>
    <style>
        body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif; line-height: 1.6; color: #333; max-width: 1000px; margin: 20px auto; padding: 0 20px; background: #f8fafc; }
        h1, h2, h3 { color: #0f172a; }
        .card { background: #fff; padding: 20px; border-radius: 12px; border: 1px solid #e2e8f0; margin-bottom: 20px; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1); }
        .status { padding: 4px 8px; border-radius: 4px; font-weight: bold; font-size: 12px; }
        .status.success { background: #dcfce7; color: #15803d; }
        .status.error { background: #fee2e2; color: #b91c1c; }
        .status.warning { background: #fef9c3; color: #a16207; }
        pre { background: #0f172a; color: #38bdf8; padding: 15px; border-radius: 8px; overflow-x: auto; font-family: monospace; font-size: 13px; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { padding: 10px; text-align: left; border-bottom: 1px solid #e2e8f0; font-size: 13px; }
        th { background: #f1f5f9; font-weight: bold; }
        .btn { display: inline-block; background: #2563eb; color: #fff; padding: 10px 20px; border-radius: 6px; text-decoration: none; font-weight: bold; border: none; cursor: pointer; }
        .btn-success { background: #16a34a; }
        .alert { padding: 15px; border-radius: 8px; margin-bottom: 15px; border-left: 4px solid; }
        .alert-info { background: #eff6ff; color: #1e40af; border-left-color: #3b82f6; }
        .alert-danger { background: #fef2f2; color: #991b1b; border-left-color: #ef4444; }
        .alert-warning { background: #fffbeb; color: #92400e; border-left-color: #f59e0b; }
    </style>
</head>
<body>
    <h1>GIS & Objek Wisata Diagnostic Tool</h1>
    <p>Halaman ini mendiagnosis mengapa data Objek Wisata untuk Surakarta tidak muncul di peta.</p>

    <?php
    // Action: Fix Database
    if (isset($_GET['action']) && $_GET['action'] === 'fix') {
        echo '<div class="card">';
        echo '<h2>Proses Perbaikan Data...</h2>';
        try {
            $db = DB::connection('satupeta');
            $affected = $db->table('sm_dacol_objek_vital')
                ->where('polda', 'LIKE', '%Jawa Tengah%')
                ->where(function($q) {
                    $q->where('nama_jalan', 'LIKE', '%Surakarta%')
                      ->orWhere('nama_jalan', 'LIKE', '%Solo%');
                })
                ->whereNull('polres')
                ->update(['polres' => 'KOTA SURAKARTA']);
            
            echo '<div class="alert alert-success"><b>Sukses!</b> Berhasil memperbarui ' . $affected . ' baris di sm_dacol_objek_vital. Kolom <code>polres</code> telah diisi dengan "KOTA SURAKARTA".</div>';
        } catch (\Exception $e) {
            echo '<div class="alert alert-danger"><b>Gagal memperbarui database:</b> ' . htmlspecialchars($e->getMessage()) . '</div>';
        }
        echo '<a href="debug_gis_wisata.php" class="btn">Kembali ke Diagnostik</a>';
        echo '</div>';
        exit;
    }
    ?>

    <div class="card">
        <h2>1. Informasi Database & Koneksi</h2>
        <table>
            <tr>
                <th>Koneksi</th>
                <th>Database Konfigurasi</th>
                <th>Status</th>
            </tr>
            <?php
            $connections = ['mysql' => 'DB Utama (Operational)', 'satupeta' => 'DB Satupeta (Dacol)'];
            foreach ($connections as $conn => $label) {
                try {
                    $dbName = DB::connection($conn)->getDatabaseName();
                    DB::connection($conn)->select('SELECT 1');
                    $status = '<span class="status success">Koneksi OK</span>';
                } catch (\Exception $e) {
                    $dbName = config("database.connections.{$conn}.database", 'Unknown');
                    $status = '<span class="status error">Error: ' . htmlspecialchars($e->getMessage()) . '</span>';
                }
                echo "<tr>
                    <td><b>{$conn}</b> ({$label})</td>
                    <td><code>{$dbName}</code></td>
                    <td>{$status}</td>
                </tr>";
            }
            ?>
        </table>
    </div>

    <div class="card">
        <h2>2. Pemeriksaan Tabel Objek Wisata (sm_dacol_objek_vital)</h2>
        <?php
        try {
            $db = DB::connection('satupeta');
            if (!Schema::connection('satupeta')->hasTable('sm_dacol_objek_vital')) {
                throw new \Exception("Tabel 'sm_dacol_objek_vital' tidak ditemukan di database satupeta.");
            }

            $total = $db->table('sm_dacol_objek_vital')->count();
            $hasCoords = $db->table('sm_dacol_objek_vital')
                ->whereNotNull('lat')->where('lat', '!=', '')
                ->whereNotNull('lng')->where('lng', '!=', '')
                ->count();
            
            echo "<ul>";
            echo "<li>Total Baris Data: <b>{$total}</b></li>";
            echo "<li>Data Memiliki Koordinat (lat/lng): <b>{$hasCoords}</b></li>";
            echo "</ul>";

            // Count NULL polres
            $nullPolres = $db->table('sm_dacol_objek_vital')
                ->where('polda', 'LIKE', '%Jawa Tengah%')
                ->where(function($q) {
                    $q->where('nama_jalan', 'LIKE', '%Surakarta%')
                      ->orWhere('nama_jalan', 'LIKE', '%Solo%');
                })
                ->whereNull('polres')
                ->count();

            if ($nullPolres > 0) {
                echo '<div class="alert alert-danger">';
                echo "<h4>⚠️ Ditemukan Masalah Data Teritori!</h4>";
                echo "Ada <b>{$nullPolres}</b> baris data di Jawa Tengah dengan alamat 'Surakarta' / 'Solo' yang kolom <b><code>polres</code>-nya NULL (kosong)</b>.";
                echo "<br><br><a href='debug_gis_wisata.php?action=fix' class='btn btn-success'>Klik di Sini untuk Memperbaiki Otomatis (Update polres ke KOTA SURAKARTA)</a>";
                echo '</div>';
            } else {
                echo '<div class="alert alert-info">Semua data Surakarta/Solo di <code>sm_dacol_objek_vital</code> memiliki kolom <code>polres</code> yang terisi.</div>';
            }

            // Tampilkan beberapa contoh baris data Surakarta
            $rows = $db->table('sm_dacol_objek_vital')
                ->where('polda', 'LIKE', '%Jawa Tengah%')
                ->where(function($q) {
                    $q->where('nama_jalan', 'LIKE', '%Surakarta%')
                      ->orWhere('nama_jalan', 'LIKE', '%Solo%');
                })
                ->limit(5)
                ->get();

            echo "<h3>Contoh Data Objek Wisata di Surakarta (Database):</h3>";
            if ($rows->isEmpty()) {
                echo "<p class='status error'>Tidak ada data Surakarta ditemukan.</p>";
            } else {
                echo "<table>";
                echo "<tr>
                    <th>RowID</th>
                    <th>Nama Objek / Fasilitas</th>
                    <th>Polda (daerah)</th>
                    <th>Polres (wilayah)</th>
                    <th>Koordinat</th>
                    <th>Alamat</th>
                </tr>";
                foreach ($rows as $r) {
                    $polresVal = $r->polres === null ? '<span class="status error">NULL</span>' : htmlspecialchars($r->polres);
                    echo "<tr>
                        <td>{$r->rowid}</td>
                        <td><b>" . htmlspecialchars($r->fasilitas ?? $r->nama_objek ?? '-') . "</b></td>
                        <td>" . htmlspecialchars($r->polda) . "</td>
                        <td>{$polresVal}</td>
                        <td><code>{$r->lat},{$r->lng}</code></td>
                        <td>" . htmlspecialchars($r->nama_jalan ?? '-') . "</td>
                    </tr>";
                }
                echo "</table>";
            }

        } catch (\Exception $e) {
            echo '<div class="alert alert-danger"><b>Error:</b> ' . htmlspecialchars($e->getMessage()) . '</div>';
        }
        ?>
    </div>

    <div class="card">
        <h2>3. Simulasi Hak Akses Operator</h2>
        
        <h3>A. Operator Wilayah (SURAKARTA)</h3>
        <?php
        try {
            $userWilayah = new Usm();
            $userWilayah->id_usm = 9999;
            $userWilayah->nama = 'Operator Surakarta';
            $userWilayah->daerah = 'JAWA TENGAH';
            $userWilayah->wilayah = 'SURAKARTA';
            $userWilayah->user_role_id = 3; 
            Auth::login($userWilayah);

            $qScoped = DB::connection('satupeta')->table('sm_dacol_objek_vital');
            TerritoryIndexFilters::applyColumnFilters($qScoped, request(), 'sm_dacol_objek_vital', 'satupeta');
            
            $sql = $qScoped->toSql();
            $bindings = $qScoped->getBindings();
            $count = $qScoped->count();

            echo "<ul>";
            echo "<li>User Daerah: <code>{$userWilayah->daerah}</code></li>";
            echo "<li>User Wilayah: <code>{$userWilayah->wilayah}</code></li>";
            echo "<li>Query yang Dihasilkan: <code>" . htmlspecialchars($sql) . "</code></li>";
            echo "<li>Bindings: <code>" . json_encode($bindings) . "</code></li>";
            echo "<li>Apakah Data Tampil? " . ($count > 0 ? '<span class="status success">YA (' . $count . ' data)</span>' : '<span class="status error">TIDAK (0 data)</span>') . "</li>";
            echo "</ul>";
        } catch (\Exception $e) {
            echo '<p class="status error">Error: ' . htmlspecialchars($e->getMessage()) . '</p>';
        }
        ?>

        <h3>B. Operator Daerah (JAWA TENGAH)</h3>
        <?php
        try {
            $userDaerah = new Usm();
            $userDaerah->id_usm = 9998;
            $userDaerah->nama = 'Operator Jateng';
            $userDaerah->daerah = 'JAWA TENGAH';
            $userDaerah->wilayah = '';
            $userDaerah->user_role_id = 3; 
            Auth::login($userDaerah);

            $qScoped = DB::connection('satupeta')->table('sm_dacol_objek_vital');
            TerritoryIndexFilters::applyColumnFilters($qScoped, request(), 'sm_dacol_objek_vital', 'satupeta');
            
            $sql = $qScoped->toSql();
            $bindings = $qScoped->getBindings();
            $count = $qScoped->count();

            echo "<ul>";
            echo "<li>User Daerah: <code>{$userDaerah->daerah}</code></li>";
            echo "<li>User Wilayah: <code>(Kosong / Seluruh Polres)</code></li>";
            echo "<li>Query yang Dihasilkan: <code>" . htmlspecialchars($sql) . "</code></li>";
            echo "<li>Bindings: <code>" . json_encode($bindings) . "</code></li>";
            echo "<li>Apakah Data Tampil? " . ($count > 0 ? '<span class="status success">YA (' . $count . ' data)</span>' : '<span class="status error">TIDAK (0 data)</span>') . "</li>";
            echo "</ul>";
        } catch (\Exception $e) {
            echo '<p class="status error">Error: ' . htmlspecialchars($e->getMessage()) . '</p>';
        }
        ?>
    </div>

    <div class="card">
        <h2>4. Kesimpulan & Rekomendasi Solusi</h2>
        <p><b>Mengapa Data Tidak Tampil di Map Operator Wilayah Surakarta?</b></p>
        <p>Data objek wisata vital tersimpan di dalam tabel <code>sm_dacol_objek_vital</code>. Ketika data tersebut dimasukkan (di-import), kolom <b><code>polres</code>-nya bernilai NULL (kosong)</b> sedangkan kolom <b><code>polda</code>-nya terisi "Polda Jawa Tengah"</b>.</p>
        <p>Akibatnya:</p>
        <ol>
            <li><b>Operator Wilayah (Surakarta)</b>: Mengalami pemfilteran strict <code>LOWER(polres) LIKE '%surakarta%'</code>. Karena kolom polres bernilai NULL, query menghasilkan 0 data.</li>
            <li><b>Operator Daerah (Jawa Tengah)</b>: Mengalami pemfilteran <code>LOWER(polda) LIKE '%jawa tengah%'</code>. Karena kolom polda bernilai "Polda Jawa Tengah", data tampil untuk operator daerah.</li>
        </ol>
        
        <p><b>Solusi:</b></p>
        <p>Gunakan tombol hijau di bagian Pemeriksaan Tabel di atas untuk mengupdate data, atau jalankan perintah SQL berikut di Database Client Anda:</p>
        <pre>UPDATE sm_dacol_objek_vital 
SET polres = 'KOTA SURAKARTA' 
WHERE polda LIKE '%Jawa Tengah%' 
  AND (nama_jalan LIKE '%Surakarta%' OR nama_jalan LIKE '%Solo%') 
  AND polres IS NULL;</pre>
    </div>
</body>
</html>
