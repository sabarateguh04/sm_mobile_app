# 📊 DOKUMENTASI PEMETAAN TABEL DAN MODUL (SMART BACK OFFICE)

Dokumentasi ini menjelaskan pemetaan tabel database terhadap seluruh modul aplikasi secara detail, terbagi dalam 11 kategori sistem/modul fungsional.

---

## 🏗️ 1. Arsitektur Hierarki Wilayah Kerja (Territory Scoping)

Sistem Smart Back Office menggunakan mekanisme penyaringan data otomatis berdasarkan wilayah administrasi kepolisian / kewilayahan user yang sedang login (`usm`). Hierarki 4 level yang diimplementasikan adalah:

```
NASIONAL (Level 4 - No Filter) - Mengakses seluruh data se-Indonesia.
    ↓
DAERAH / POLDA (Level 3 - Filter by daerah) - Mengakses data di lingkup satu provinsi.
    ↓
WILAYAH / POLRES (Level 2 - Filter by wilayah) - Mengakses data di lingkup satu kabupaten/kota.
    ↓
OPERASIONAL / POLSEK (Level 1 - Full CRUD) - Tingkat input data operasional di lapangan.
```

Penyaringan dilakukan secara otomatis melalui helper kewilayahan `HierarchicalHelper::applyTerritoryScopeForTable()` menggunakan kolom `daerah` dan `wilayah` pada tabel, atau melalui relasi petugas piket (`piket` -> `usm.id_usm`), maupun melalui relasi posko (`id_profileposko` -> `profilposko`).

---

## 📂 2. Daftar Pemetaan Tabel per Modul Fungsional

### **A. Modul Manajemen User & Hak Akses (User Management & Auth)**
| Modul di Aplikasi | Halaman / Controller | Tabel Database | Eloquent Model / Query | Deskripsi & Kolom Utama |
| :--- | :--- | :--- | :--- | :--- |
| **Manajemen User** | `/admin/users`<br>`UserController` | `usm` | `App\Models\Usm` | Menyimpan data personil/user sistem.<br>Kolom utama: `id_usm`, `username`, `password`, `email`, `nama`, `daerah`, `wilayah`, `user_role_id`, `account_status`, `nrp_nip_nik`, `organisasi_daerah`. |
| **Manajemen Role** | `/admin/roles`<br>`RoleController` | `roles` | `App\Models\Role` | Menyimpan nama dan hierarki peran pengguna.<br>Kolom utama: `id`, `name`, `display_name`, `guard_name`. |
| **Hak Akses & Izin** | `/admin/role_permissions`<br>`RolePermissionController` | `role_permissions`<br>`permissions` | `App\Models\RolePermission` | Menyimpan izin akses per modul/menu untuk setiap role (Matriks Akses).<br>Kolom utama: `role_id`, `permission_id`. |
| **Token Akses API** | API Endpoint / Sanctum | `personal_access_tokens` | Sanctum standard | Token autentikasi untuk integrasi mobile app Flutter.<br>Kolom utama: `tokenable_id`, `tokenable_type`, `name`, `token`, `abilities`. |

---

### **B. Modul Operasi Kepolisian (Ops Kepolisian)**
| Modul di Aplikasi | Halaman / Controller | Tabel Database | Eloquent Model / Query | Deskripsi & Kolom Utama |
| :--- | :--- | :--- | :--- | :--- |
| **Profil Posko** | `/profilposko`<br>`ProfilposkoController` | `profilposko` | `DB::table('profilposko')` | Konfigurasi posko pengamanan kepolisian.<br>Kolom utama: `id_profilposko`, `namaposko`, `lokasiposko`, `katops`, `jenisops`, `tahunops`, `temaops`, `wilayah`, `daerah`, `piket` (FK ke `usm`). |
| **Arus Mudik & Balik (Operasional)** | `/arusmudik`<br>`ArusmudikController` | `arusmudik` | `DB::table('arusmudik')` | Pencatatan status arus lalu lintas mudik/balik.<br>Kolom utama: `id_arusmudik`, `tanggal`, `statarus`, `id_profileposko` (FK ke `profilposko`), `piket` (FK ke `usm`). |
| **Arus Mudik (Wilayah)** | `/wil/arusmudik`<br>`WilArusmudikController` | `wil_arusmudik` | `DB::table('wil_arusmudik')` | Aggregation table untuk pelaporan di level Polres.<br>Kolom utama: `id_wil_arusmudik`, `id_arusmudik` (FK), `wilayah`, `id_profileposko`. |
| **Arus Mudik (Daerah)** | `/da/arusmudik`<br>`DaArusmudikController` | `da_arusmudik` | `DB::table('da_arusmudik')` | Aggregation table untuk pelaporan di level Polda.<br>Kolom utama: `id_da_arusmudik`, `id_arusmudik` (FK), `daerah`, `wilayah`, `namaposko`, `katops`, `jenisops`. |
| **Arus Mudik (Nasional)** | `/nas/arusmudik`<br>`NasArusmudikController` | `nas_arusmudik` | `DB::table('nas_arusmudik')` | Aggregation table tingkat Korlantas Polri (Nasional).<br>Kolom utama: `id_nas_arusmudik`, `id_arusmudik` (FK), `daerah`, `wilayah`, `statarus`, `tanggal`, `usm_nama`. |
| **Peristiwa Menonjol** | `/ops_wanjol`<br>`OpsWanjolController` | `ops_wanjol` | `DB::table('ops_wanjol')` | Pencatatan kejadian/gangguan menonjol selama operasi.<br>Kolom utama: `id_wanjol`, `tanggal`, `piket`, `kategori`, `peristiwa`, `lokasi`, `keterangan`. |

> [!NOTE]
> Perubahan data pada tabel operasional `arusmudik` secara otomatis memicu sinkronisasi ke tabel aggregasi (`wil_arusmudik`, `da_arusmudik`, dan `nas_arusmudik`) melalui `ArusmudikAggregationHelper::syncByArusmudikId()`.

---

### **C. Modul Kegiatan Rutin (Giat Rutin)**
| Modul di Aplikasi | Halaman / Controller | Tabel Database | Eloquent Model / Query | Deskripsi & Kolom Utama |
| :--- | :--- | :--- | :--- | :--- |
| **Patroli Turjagwali** | `/turjagwali`<br>`TurjagwaliController` | `turjagwali` | `DB::table('turjagwali')` | Laporan kegiatan Pengaturan, Penjagaan, Pengawalan, dan Patroli.<br>Kolom utama: `id_turjagwali`, `tanggal`, `katgiat`, `lokasi`, `peristiwa`, `ketcb` (cara bertindak), `fotogiat`, `piket` (FK ke `usm`), `koordinat`. |
| **Audit Arus Lalu Lintas** | `/auditarus`<br>`AuditarusController` | `auditarus` | `DB::table('auditarus')` | Evaluasi volume arus kendaraan pada titik-titik tertentu.<br>Kolom utama: `id_auditarus`, `tanggal`, `piket`, `lokasi`, `volume_in`, `volume_out`, `keterangan`, `fotosit`. |
| **Penumpang & Angkutan** | `/pnpangkutan`<br>`PnpangkutanController` | `pnp_angkutan` | `DB::table('pnp_angkutan')` | Laporan mobilitas armada bus/kendaraan umum di terminal/stasiun.<br>Kolom utama: `id_pnpangkutan`, `tanggal`, `piket`, `namaterminal`, `penumpang_datang`, `penumpang_berangkat`, `angkut_datang`, `angkut_berangkat`, `fotosit`. |
| **Arus Dalkot (Dalam Kota)** | `/traffics`<br>`TrafficsController` | `trafficdalkot` | `DB::table('trafficdalkot')` | Pemantauan volume kendaraan lalu lintas dalam kota.<br>Kolom utama: `id_trafficdalkot`, `tanggal`, `lokasi`, `volume_r2`, `volume_r4`, `volume_r6`, `volume_r6_plus`, `piket`, `koordinat`. |

---

### **D. Modul Respon Cepat & Operasional Pengendalian (Quick Response & Opsdal)**
| Modul di Aplikasi | Halaman / Controller | Tabel Database | Eloquent Model / Query | Deskripsi & Kolom Utama |
| :--- | :--- | :--- | :--- | :--- |
| **Kejadian Kontigensi** | `/contigency`<br>`ContigencyController` | `contigency` | `DB::table('contigency')` | Penanganan bencana alam, kecelakaan massal, sabotase, dll.<br>Kolom utama: `id_contigency`, `tanggal`, `lokasi`, `jeniscontigency`, `faktor`, `penyebab`, `korbanmd` (meninggal dunia), `korbanlb` (luka berat), `korbanlr` (luka ringan), `kermat` (kerugian materiil), `fotosit`, `ketcb`, `piket`, `koordinat`, `sb_grp` (opsdal/operasional). |
| **Kemacetan / Perlambatan** | `/trafficstuck`<br>`TrafficstuckController` | `trafficstuck` | `DB::table('trafficstuck')` | Laporan titik kemacetan parah dan langkah penguraian.<br>Kolom utama: `id_trafficstuck`, `tanggal`, `lokasi`, `faktor`, `penyebab`, `durasi`, `timurai`, `fotosituasi`, `piket`, `koordinat`, `sb_grp`, `operator_edited`, `id_patrolicctv` (FK). |

---

### **E. Modul Pelayanan Masyarakat (Layanan / Public Service)**
| Modul di Aplikasi | Halaman / Controller | Tabel Database | Eloquent Model / Query | Deskripsi & Kolom Utama |
| :--- | :--- | :--- | :--- | :--- |
| **Public Service (Posyan)** | `/layanan/public-service`<br>`PosyanPublicController` | `pos_pelayanan` | `App\Models\Posyan` | Manajemen titik Pos Pelayanan (Posyan) / Pos Pengamanan (Pospam).<br>Kolom utama: `id_posyan`, `namaposyan`, `lokasi`, `fasilitas`, `koordinat`, `piket` (FK ke `usm`). |
| **Interaksi Masyarakat** | `/layanan/masyarakat`<br>`InteraksiLayananController` | `layanan_masyarakat` | `DB::table('layanan_masyarakat')` | Laporan aduan atau interaksi langsung dengan masyarakat.<br>Kolom utama: `id_interaksi`, `tanggal`, `kategori`, `tema`, `keterangan`, `id_usm` (FK ke `usm`), `foto`, `koordinat`. |
| **Layanan Data** | `/layanan/data`<br>`LayananDataController` | `layanan_data` | `DB::table('layanan_data')` | Pencatatan permohonan/akses data dari publik.<br>Kolom utama: `id_layanan_data`, `tanggal`, `namapemohon`, `keperluan`, `status`, `id_usm`, `foto`, `koordinat`. |

---

### **F. Modul Media & Informasi Publik (Media)**
| Modul di Aplikasi | Halaman / Controller | Tabel Database | Eloquent Model / Query | Deskripsi & Kolom Utama |
| :--- | :--- | :--- | :--- | :--- |
| **Publikasi Media (Manage)** | `/media/manage`<br>`MediaPublishingController` | `mediapublishing` | `DB::table('mediapublishing')` | Pencatatan rilis berita/edukasi kepolisian di media sosial/cetak.<br>Kolom utama: `id_mediapublishing`, `tanggal`, `link_media`, `kategori_media`, `isi_berita`, `piket` (FK ke `usm`). |
| **Analisis Media** | `/media/analytic`<br>`MediaAnalyticController` | `mediaanalytic` | `DB::table('mediaanalytic')` | Analisis sentimen berita (positif/netral/negatif).<br>Kolom utama: `id_mediaanalytic`, `tanggal`, `isu_utama`, `sentimen` (Positif, Netral, Negatif), `media_nama`, `keterangan`, `id_usm` (FK ke `usm`). |

---

### **G. Modul Personil & Kunjungan Posko (Personil)**
| Modul di Aplikasi | Halaman / Controller | Tabel Database | Eloquent Model / Query | Deskripsi & Kolom Utama |
| :--- | :--- | :--- | :--- | :--- |
| **Personil Posyan** | `/personil/manager`<br>`PersonilPosyanController` | `wil_usm` (View/Table)<br>`apelposyan`<br>`pengunjungposyan` | `DB::table('wil_usm')` | Pencatatan absensi apel posko dan kunjungan tamu/pejabat ke posko.<br>Kolom utama (`apelposyan`): `id_apelposyan`, `namaposyan`, `tanggal`, `piket`, `fotogiat`, `koordinat`.<br>Kolom utama (`pengunjungposyan`): `id_pengunjungposyan`, `namaposyan`, `tanggal`, `namapengunjung`, `instansi`, `keterangan`, `koordinat`. |

---

### **H. Modul Inventaris & Data Collecting (Data Collecting)**
| Modul di Aplikasi | Halaman / Controller | Tabel Database | Eloquent Model / Query | Deskripsi & Kolom Utama |
| :--- | :--- | :--- | :--- | :--- |
| **Terminal Transportasi** | `/terminal`<br>`TerminalController` | `data_terminal` | `App\Models\DataTerminal` | Inventarisasi terminal bus, stasiun, bandara, atau pelabuhan.<br>Kolom utama: `id_terminal`, `namaterminal`, `jenisterminal`, `jalan`, `cp` (narahubung), `fasilitas`, `koordinat`, `piket`, `tanggal`. |
| **Data Jalan (Master Fisik)** | `/t_datajalan`<br>`DataJalanController` | `t_datajalan` | `App\Models\DataJalan` | Pencatatan data fisik ruas jalan (arteri/tol) dan panjangnya.<br>Kolom utama: `id_datajalan`, `namajalan`, `jenisjalan`, `panjangjalan`, `lintasan`, `fotoobject`, `koordinat`, `piket`. |
| **Gangguan Jalan (Blackspot)** | `/jalan_gangguan`<br>`JalanGangguanController` | `data_gangguan` | `App\Models\DataGangguan` | Lokasi rawan kecelakaan, rawan macet, atau gangguan fisik jalan.<br>Kolom utama: `id_datagangguan`, `tanggal`, `namajalan`, `fungsijalan`, `kerawanan`, `faktor`, `penyebab`, `levelgangguan`, `teamsurvayor`, `fotolokasi`, `piket`. |

---

### **I. Modul TIK & Pemantauan CCTV (TIK & Patroli Online)**
| Modul di Aplikasi | Halaman / Controller | Tabel Database | Eloquent Model / Query | Deskripsi & Kolom Utama |
| :--- | :--- | :--- | :--- | :--- |
| **Infrastruktur CCTV** | `/data-cctv`<br>`DataCctvController` | `datacctv` (Table)<br>`wil_datacctv` (View)<br>`nas_datacctv` (View) | `App\Models\DataCctv` / `DB::table(...)` | Inventarisasi perangkat CCTV (milik Korlantas, Ditlantas, Dishub, BPJT, dll).<br>Kolom utama: `id_datacctv`, `owner`, `lokasi`, `ipaddres`, `brand`, `fungsicctv`, `tanggal`, `koordinat`, `fotofisik`, `piket`. |
| **Patroli Online (Monitoring)** | `/patrolicctv`<br>`CctvController` | `patrolicctv` | `DB::table('patrolicctv')` | Pemantauan lalu lintas berkala via streaming CCTV.<br>Kolom utama: `id_patrolicctv`, `tanggal`, `peristiwa`, `ketcb`, `fotosit`, `koordinat`, `piket`. |
| **Streaming CCTV Live** | `/cctv-live`<br>`CctvLiveController` | `datacctv` | Integrasi streaming RTSP/HLS | Mengakses URL stream live CCTV dari inventaris data cctv. |
| **Data Perangkat IT** | `/dataperangkat`<br>`DataPerangkatController` | `dataperangkat` | `App\Models\DataPerangkat` | Inventaris perangkat IT pendukung backoffice (PC, UPS, Server).<br>Kolom utama: `id_perangkat`, `nameperangkat`, `katperangkat`, `lokasi`, `owner`, `qtty`, `supportfor`, `piket` (FK ke `usm`), `tanggal`. |
| **IT Support Ticket** | `/itsupport`<br>`ItSupportController` | `itsupport` | `App\Models\ItSupport` | Pelaporan kerusakan dan penanganan infrastruktur IT.<br>Kolom utama: `id_itsupport`, `namedevice`, `katdevice`, `owner`, `tingkatgangguan`, `ketsingkat`, `statusticket` (Open, Progres, Closed), `piket`, `tanggal`. |

---

### **J. Modul CAD Dispatch & Tracking (Kodal & Petugas)**
| Modul di Aplikasi | Halaman / Controller | Tabel Database | Eloquent Model / Query | Deskripsi & Kolom Utama |
| :--- | :--- | :--- | :--- | :--- |
| **CAD (Computer-Aided Dispatch)** | `/opsdal/cad`<br>`OpsdalCadController` | `cad_tasks`<br>`cad_assignments` | `DB::table(...)` | Pembagian dan penugasan kejadian darurat (dispatch) ke petugas di lapangan.<br>Kolom utama (`cad_tasks`): `id_cad`, `judul_tugas`, `kategori`, `deskripsi`, `status` (Open, Assigned, In_Progress, Closed), `koordinat_peristiwa`. |
| **Tracking Lokasi Petugas** | Map & API<br>`MapController` | `petugas_tracking` | `DB::table('petugas_tracking')` | Pencatatan titik koordinat GPS real-time petugas lapangan dari mobile app.<br>Kolom utama: `id`, `id_usm` (FK ke `usm`), `latitude`, `longitude`, `updated_at`. |

---

### **K. Modul Wisata & Ibadah**
| Modul di Aplikasi | Halaman / Controller | Tabel Database | Eloquent Model / Query | Deskripsi & Kolom Utama |
| :--- | :--- | :--- | :--- | :--- |
| **Objek Wisata** | `/objekwisata`<br>`ObjekWisataController` | `objek_wisata` | `App\Models\ObjekWisata` | Inventaris tempat wisata (pantai, budaya, alam, dll).<br>Kolom utama: `id_tempatwisata`, `nametempat`, `kattempatwisata`, `lokasi`, `kappengunjung`, `kapparkir`, `cp`, `alatkamanan`, `fotoobject`, `koordinat`, `piket`, `tanggal`. |
| **Kunjungan Wisata** | `/kunjunganwisata`<br>`KunjunganWisataController` | `kunjunganwisata` | `App\Models\KunjunganWisata` | Laporan statistik harian jumlah pengunjung objek wisata.<br>Kolom utama: `id_kunjunganwisata`, `namawisata`, `katwisata`, `parkirr2`, `parkirr4`, `domestic`, `mancanegara`, `jmlhpengunjung`, `piket`, `tanggal`. |
| **Tempat Ibadah** | `/tempatibadah`<br>`TempatIbadahController` | `tempatibadah` | `App\Models\TempatIbadah` | Inventaris masjid, gereja, pura, dll di sepanjang rute pengamanan.<br>Kolom utama: `id_tempatibadah`, `namatempat`, `kattempatibadah`, `jalan`, `cp`, `kappengunjung`, `kapparkir`, `fotoobject`, `koordinat`, `piket`, `tanggal`. |
| **Fasilitas Kesehatan** | `/faskes`<br>`FaskesController` | `data_faskes` | `App\Models\DataFaskes` | Inventaris rumah sakit, puskesmas, posko kesehatan di rute pengamanan.<br>Kolom utama: `id_faskes`, `nama_faskes`, `jenis_faskes` (Rumah Sakit, Puskesmas, Pos Kesehatan), `alamat`, `no_telp`, `dokter_ready`, `ambulan_ready`, `koordinat`, `piket`, `tanggal`. |

---

## 🗃️ 3. Tabel Referensi & Data Induk (Master Data)

Tabel master digunakan untuk mengisi dropdown menu secara dinamis dan memetakan data wilayah geospasial:

1. **Referensi Kewilayahan Pemerintahan**:
   - `master_provinsi` / `daerah`: Menyimpan nama-nama Provinsi di Indonesia.
   - `master_kabupaten_kota` / `wilayah`: Menyimpan nama Kabupaten/Kota yang terhubung dengan Provinsi.
   - `master_territori`: Master koordinat geospasial (Latitude, Longitude) wilayah.
2. **Referensi Organisasi / Satuan Kerja**:
   - `org`: Master data nama instansi/organisasi kepolisian pusat/instansi luar.
   - `suborg`: Sub-organisasi/satuan kerja bagian bawah dari organisasi induk.
   - `master_kl_pusat` / `master_kl_daerah` / `master_kl_resort` / `master_kl_unit_kerja`: Hierarki kementerian/lembaga (Mabes Polri, Polda, Polres, Polsek/Unit Kerja).
3. **Dropdown Pilihan Menu (Kategori & Jenis)**:
   - `t_jenisterminal`: Pilihan jenis terminal (Terminal Bus AKAP, Stasiun, Bandara, Pelabuhan).
   - `t_ownercctv`: Instansi pemilik CCTV (Korlantas, Ditlantas, Dishub, Jasa Marga, BPJT, dll).
   - `t_katlayanan` / `t_temalayanan`: Referensi kategori interaksi pelayanan masyarakat.
   - `t_faktor` / `t_penyebab`: Referensi faktor (Kendaraan, Jalan, Alam, Pengendara) dan penyebab kemacetan/kontigensi.
