# SM-Lite Operation Runbook

Runbook ini adalah SOP eksekusi plan migrasi SM lama ke SM-Lite + integrasi Analytic dan DWH.

## 1) Persiapan

1. Jalankan migrasi skema:
   - `php artisan migrate`
2. Validasi discovery:
   - `php artisan sm-lite:discovery-audit`
   - opsional simpan hasil: `php artisan sm-lite:discovery-audit --output=reports/discovery.json`

## 2) Staging Transform & Dry Run

1. Transform legacy -> staging (`traffic_counting_staging`):
   - `php artisan sm-lite:staging-transform --limit=1000`
2. Dry-run one-time migration:
   - `php artisan sm-lite:migrate-one-time --dry-run --limit=1000`

## 3) Go-Live One-Time Migration

1. Backup database terlebih dahulu.
2. Eksekusi migrasi:
   - `php artisan sm-lite:migrate-one-time`
3. Rekonsiliasi:
   - `php artisan sm-lite:reconcile --json`

## 4) Cron Berkala Analytic -> SM-Lite

Command:

- `php artisan sm-lite:sync-analytic-smlite --limit=500`

Contoh cron (setiap 5 menit):

```cron
*/5 * * * * cd /path/to/smartbackoffice-laravel && php artisan sm-lite:sync-analytic-smlite --limit=500 >> storage/logs/sm-lite-sync.log 2>&1
```

## 5) Cron Berkala SM-Lite -> DWH

Command:

- `php artisan sm-lite:sync-smlite-dwh --limit=500`

Contoh cron (setiap 5 menit):

```cron
*/5 * * * * cd /path/to/smartbackoffice-laravel && php artisan sm-lite:sync-smlite-dwh --limit=500 >> storage/logs/sm-lite-dwh.log 2>&1
```

## 6) Parallel Run & Reconcile

Selama 1-2 minggu:

- Jalankan rekonsiliasi periodik:
  - `php artisan sm-lite:reconcile`
- Pantau dead-letter dan batch fail:
  - `php artisan sm-lite:hypercare-status --hours=24`

## 7) Cutover & Hypercare

### Freeze legacy write per modul

- Freeze:
  - `php artisan sm-lite:cutover traffic freeze --note="cutover traffic phase 1"`
- Unfreeze (rollback):
  - `php artisan sm-lite:cutover traffic unfreeze --note="rollback traffic"`

### Aktivasi SM-Lite sebagai primary

- Activate:
  - `php artisan sm-lite:cutover traffic activate --note="smlite primary traffic"`
- Deactivate:
  - `php artisan sm-lite:cutover traffic deactivate --note="switch back"`

### Monitoring hypercare

- `php artisan sm-lite:hypercare-status --hours=24`

## 8) Kebijakan Retry & Dead-Letter

- Semua command sync mencatat kegagalan ke `sm_sync_dead_letters`.
- Cek dead-letter dan lakukan replay manual setelah penyebab diperbaiki.
- Watermark disimpan di `sm_sync_watermarks` untuk mencegah duplikasi.

## 9) Kriteria Sukses Minimum

- Delta row-count utama (`sm-lite:reconcile`) mendekati nol/within threshold tim.
- Dead-letter harian menurun dan tidak ada batch status `failed` berulang.
- Spot-check data kritikal lulus (tanggal, lokasi, klasifikasi volume, linkage sumber).
