<?php

namespace App\Support;

use Illuminate\Support\Facades\DB;

/**
 * Laporan struktur + sample data untuk DB kedua (wilayah + master/transaksi SM).
 */
final class SmSecondDatabaseReport
{
  private const SAMPLE_LIMIT = 19;

  /**
   * @return array{wilayah: int, master: int, transaksi: int, lainnya: int, total: int}
   */
  public static function tableCounts(string $connection): array
  {
    $db = (string) DB::connection($connection)->getDatabaseName();
    $rows = DB::connection($connection)->select(
      'SELECT TABLE_NAME FROM information_schema.TABLES WHERE TABLE_SCHEMA = ? ORDER BY TABLE_NAME',
      [$db]
    );

    $counts = ['wilayah' => 0, 'master' => 0, 'transaksi' => 0, 'lainnya' => 0, 'total' => 0];

    foreach ($rows as $row) {
      $name = (string) $row->TABLE_NAME;
      $counts['total']++;
      if (str_starts_with($name, 'sm_mstr_tbl_')) {
        $counts['wilayah']++;
      } elseif (str_starts_with($name, 'smd_mstr_')) {
        $counts['master']++;
      } elseif (str_starts_with($name, 'smd_tbl_')) {
        $counts['transaksi']++;
      } else {
        $counts['lainnya']++;
      }
    }

    return $counts;
  }

  /**
   * @return list<string>
   */
  public static function listTables(string $connection): array
  {
    $db = (string) DB::connection($connection)->getDatabaseName();

    return array_map(
      static fn ($row) => (string) $row->TABLE_NAME,
      DB::connection($connection)->select(
        'SELECT TABLE_NAME FROM information_schema.TABLES WHERE TABLE_SCHEMA = ? ORDER BY TABLE_NAME',
        [$db]
      )
    );
  }

  public static function render(string $connection, int $sampleLimit = self::SAMPLE_LIMIT): string
  {
    $db = DB::connection($connection);
    $database = (string) $db->getDatabaseName();
    $counts = self::tableCounts($connection);
    $tables = self::listTables($connection);

    $lines = [
      "DB KEDUA: {$database} (koneksi: {$connection})",
      "Ringkasan: {$counts['total']} tabel — wilayah {$counts['wilayah']}, master smd_mstr {$counts['master']}, transaksi smd_tbl {$counts['transaksi']}, lainnya {$counts['lainnya']}",
      '',
    ];

    if ($counts['master'] === 0 && $counts['transaksi'] === 0) {
      $lines[] = 'PERINGATAN: Tabel smd_mstr_* dan smd_tbl_* belum ada di database ini.';
      $lines[] = 'Import dump SQL lengkap (bukan hanya test.sql wilayah) ke database "'.$database.'".';
      $lines[] = '';
    }

    foreach ($tables as $table) {
      $lines[] = '=========================================';
      $lines[] = "TABLE: {$table}";
      $lines[] = '=========================================';

      $create = $db->selectOne('SHOW CREATE TABLE `'.$table.'`');
      $createSql = $create->{'Create Table'} ?? $create->{'Create View'} ?? '';
      $lines[] = $createSql.';';
      $lines[] = '';
      $lines[] = '--- SAMPLE DATA ---';

      $total = (int) $db->table($table)->count();
      if ($total === 0) {
        $lines[] = '(No sample data found in SQL dump)';
        $lines[] = '';
        continue;
      }

      $sample = $db->table($table)->limit($sampleLimit)->get();
      $columns = array_keys((array) $sample->first());

      foreach ($sample as $row) {
        $values = [];
        foreach ($columns as $col) {
          $values[] = self::sqlValue($row->{$col} ?? null);
        }
        $lines[] = 'INSERT INTO `'.$table.'` (`'.implode('`, `', $columns).'`) VALUES'
          .' ('.implode(', ', $values).'),';
      }

      if ($total > $sampleLimit) {
        $lines[] = '';
        $lines[] = '  ... (data truncated) ...';
      }

      $lines[] = '';
    }

    return implode("\n", $lines);
  }

  private static function sqlValue(mixed $value): string
  {
    if ($value === null) {
      return 'NULL';
    }

    if (is_bool($value)) {
      return $value ? '1' : '0';
    }

    if (is_int($value) || is_float($value)) {
      return (string) $value;
    }

    return "'".str_replace(["\\", "'"], ["\\\\", "\\'"], (string) $value)."'";
  }
}
