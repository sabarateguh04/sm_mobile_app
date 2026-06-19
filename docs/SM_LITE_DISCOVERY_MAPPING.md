# Discovery Mapping Legacy -> SM-Lite

Dokumen ini merangkum hasil discovery untuk to-do `discovery-mapping`: audit kualitas data legacy dan mapping kolom + rule transform yang dipakai sinkronisasi saat ini.

## 1) Ruang Lingkup Discovery

- Sumber legacy: `trafficdalkot`, `patrolicctv` (fallback `datacctv`), `layanan_masyarakat`.
- Target SM-Lite saat ini: `trafficstuck`, `contigency`, `mediaanalytic`.
- Referensi implementasi: `TrafficDalkotAnalyticSync`, `PatrolCctvTrafficstuckSync`, `PatrolCctvContigencySync`, `LayananToOpsdalSync`, dan command di `routes/console.php`.

## 2) Baseline Audit Environment Saat Discovery

Audit cepat telah dijalankan di environment kerja ini, dan seluruh tabel operasional belum tersedia (belum import data/migrate penuh):

- `trafficdalkot`: tidak ada
- `patrolicctv`: tidak ada
- `trafficstuck`: tidak ada
- `contigency`: tidak ada
- `mediaanalytic`: tidak ada
- `layanan_masyarakat`: tidak ada

Implikasi: audit isi data (null, duplikat, orphan, invalid value) belum bisa divalidasi langsung di environment ini, sehingga disiapkan command audit siap pakai untuk environment staging/prod yang berdata.

## 3) Command Audit Discovery (Siap Eksekusi)

Gunakan command berikut di environment yang memiliki data:

```bash
php artisan sm-lite:discovery-audit
php artisan sm-lite:discovery-audit --json
php artisan sm-lite:discovery-audit --output=reports/sm-lite-discovery-audit.json
```

Command ini mengecek:

- Ketersediaan tabel + `row_count` + daftar kolom.
- Null pada kolom kritis per tabel.
- Duplikasi key link (`id_trafficdalkot`, `id_patrolicctv`).
- Orphan relation (link ke tabel sumber, serta `piket -> usm.id_usm`).
- Nilai tidak valid pada `sb_grp` (allowed: `opsdal`, `patrol_online`).

## 4) Mapping Kolom dan Rule Transform

### 4.1 Legacy `trafficdalkot` -> SM-Lite `trafficstuck` (`sb_grp=patrol_online`)

| Legacy (`trafficdalkot`) | Target (`trafficstuck`) | Rule transform |
|---|---|---|
| `id_trafficdalkot` | `id_trafficdalkot` | Link unik untuk idempotent upsert. |
| `piket` | `piket` | Cast string; default kosong bila tidak ada. |
| `tanggal` | `tanggal` | Default `today()` bila kosong. |
| `namajalan` + `gerbangkota` | `lokasi` | Prioritas `namajalan`; fallback `gerbangkota`; gabung jika keduanya ada. |
| `jenisjalan` | `faktor` | Truncate max 100; default `-`. |
| `araharus` | `penyebab` | Truncate max 100; default `Traffic Dalkot`. |
| `ketcb` + metadata volume | `ketcb` | Gabung teks: ketcb, gerbang, arah, `R2/R4/R6`, total jumlah. |
| `waktu` | `jamperistiwa` | Normalisasi `HH:MM` -> `HH:MM:SS`; default waktu saat ini. |
| `fotosit` | `fotosituasi` | Truncate max 255. |
| `koordinat` | `koordinat` | Dipetakan hanya jika kolom target tersedia. |
| (konstan) | `sb_grp` | Selalu `patrol_online` jika kolom tersedia. |

### 4.2 Legacy `patrolicctv` -> SM-Lite `trafficstuck` (`sb_grp=opsdal`)

| Legacy (`patrolicctv`) | Target (`trafficstuck`) | Rule transform |
|---|---|---|
| `id_patrolicctv` | `id_patrolicctv` | Link unik untuk upsert per patroli. |
| `piket` | `piket` | Dipertahankan dari payload patroli. |
| `tanggal` | `tanggal` | Default `today()` jika kosong. |
| `lokasi` | `lokasi` | Fallback `-`; truncate 100. |
| `situasilalin` | `faktor` | Fallback `-`; truncate 100. |
| `peristiwa` | `penyebab` | Fallback `-`; truncate 100. |
| `situasicuaca` + `ketcb` + `situasilalin` | `ketcb` | Digabung dengan separator `|`. |
| `fotosit` / alias foto legacy | `fotosituasi` | Truncate 255. |
| `koordinat` | `koordinat` | Dipetakan jika ada kolom target. |
| (konstan) | `sb_grp` | Selalu `opsdal`. |

### 4.3 Legacy `patrolicctv` -> SM-Lite `contigency` (`sb_grp=opsdal`)

| Legacy (`patrolicctv`) | Target (`contigency`) | Rule transform |
|---|---|---|
| `id_patrolicctv` | `id_patrolicctv` | Link unik untuk upsert per patroli. |
| `piket` | `piket` | Dipertahankan dari payload patroli. |
| `tanggal` | `tanggal` | Default `today()` bila kosong. |
| `lokasi` | `lokasi` | Fallback `-`; truncate 100. |
| `peristiwa` | `jeniscontigency` | Fallback `Laporan Patroli CCTV`. |
| `situasicuaca` / `situasilalin` | `faktor` | Prioritas cuaca, fallback situasi lalin. |
| `peristiwa` | `penyebab` | Fallback `-`; truncate 100. |
| (default) | `korbanmd/lb/lr`, `kermat`, `petugastptkp` | Diinisialisasi `0/0/0`, `0`, `-`. |
| `fotosit` / alias foto legacy | `fotosit` | Truncate 255. |
| `situasicuaca` + `ketcb` + `situasilalin` | `ketcb` | Digabung dengan separator `|`. |
| (konstan) | `sb_grp` | Selalu `opsdal`. |

### 4.4 Legacy `layanan_masyarakat` -> SM-Lite `trafficstuck/contigency`

| Legacy (`layanan_masyarakat`) | Target | Rule transform |
|---|---|---|
| `id_interaksi` | `source_id` | Simpan lineage (`source_module=layanan_masyarakat`). |
| `tanggal` | `tanggal` | Default `today()` bila kosong. |
| `id_usm` | `piket` | Fallback actor login jika kosong. |
| `lokasi/tema/keterangan` | `lokasi` | Prioritas lokasi -> tema -> keterangan -> default teks. |
| `koordinat` | `koordinat` | Dipertahankan jika ada. |
| `kategori` + teks aduan | `faktor/jeniscontigency` | Klasifikasi aduan traffic vs kontijensi berdasarkan keyword. |
| `foto` | `fotosituasi` / `fotosit` | Dipotong max 255. |
| (konstan) | `status_konfirmasi_opsdal` | Set `menunggu`. |
| (konstan) | `sb_grp` | Set `opsdal`. |

## 5) Aturan Kualitas Data Wajib sebelum Cutover

- Tidak ada duplikasi pada link unik sinkron: `trafficstuck.id_trafficdalkot`, `trafficstuck.id_patrolicctv`, `contigency.id_patrolicctv`.
- Tidak ada orphan untuk link ke tabel sumber (`trafficdalkot`, `patrolicctv`) dan user (`usm.id_usm`).
- `sb_grp` hanya berisi nilai standar: `opsdal` atau `patrol_online`.
- Kolom tanggal/lokasi pada target memiliki tingkat null rendah (ideal 0 untuk record baru hasil sinkronisasi).

## 6) Catatan Lanjutan Discovery

- Standarisasi volume ke kelas `R2, R4, R6, R6+` belum diterapkan penuh di sinkronisasi saat ini; saat ini volume masih ditulis sebagai teks agregat di `ketcb`.
- Untuk finalisasi migrasi one-time, tetap perlu menjalankan command audit di staging/prod yang sudah memuat dump legacy agar mendapatkan angka real (null/duplikat/orphan) per tabel.
