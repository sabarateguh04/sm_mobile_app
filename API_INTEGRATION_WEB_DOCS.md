# Dokumentasi API Integrasi Web (Data Sharing)

API ini disediakan bagi platform **Web Pihak Ketiga** atau instansi lain yang ingin menarik (fetch) data operasional dari Smart Backoffice secara *real-time* ke dalam sistem mereka.

Data dikembalikan dalam format standar `JSON` dengan dukungan paginasi dan filter rentang waktu (tanggal).

---

## 0. Autentikasi (JWT Token Secret)

Karena data operasional bersifat sensitif, semua endpoint integrasi **wajib** menyertakan Token JWT rahasia (*secret token*) di dalam HTTP Header.
Web pihak ketiga harus mendapatkan Token Bearer terlebih dahulu melalui rute `/api/auth/login` sebelum memanggil rute integrasi ini.

- **Header yang wajib disertakan:**
  - `Accept: application/json`
  - `Authorization: Bearer <ACCESS_TOKEN_ANDA>`

---

## 1. Endpoint Utama: Ambil Data per Modul

Endpoint ini bersifat dinamis. Anda hanya perlu mengganti parameter `{module}` di URL untuk mendapatkan data dari modul operasional yang berbeda.

- **URL Dasar:** `https://sm.djalu.co.id/api/integration/{module}`
- **Method:** `GET`
- **Tipe Konten:** `application/json`

### Daftar `{module}` yang didukung:
1. `turjagwali` : Laporan giat Turjagwali (Patroli).
2. `cctv` : Laporan pemantauan Patroli CCTV.
3. `layanan_masyarakat` : Laporan interaksi dan aduan layanan masyarakat.
4. `contigency` : Data kejadian luar biasa / Contigency.
5. `trafficstuck` : Data perlambatan arus lalu lintas (Traffic Stuck).

---

## 2. Parameter URL (Query Parameters)

Anda dapat menambahkan parameter tambahan pada URL untuk menyaring data yang ditarik. Semua parameter bersifat **opsional**.

| Parameter | Tipe | Default | Deskripsi |
| :--- | :--- | :--- | :--- |
| `start_date` | Date (YYYY-MM-DD) | `null` | Batas awal tanggal data (contoh: `2026-07-01`). |
| `end_date` | Date (YYYY-MM-DD) | `null` | Batas akhir tanggal data (contoh: `2026-07-31`). |
| `limit` | Integer | `50` | Jumlah maksimal data yang dikembalikan per halaman (Maksimal yang disarankan: 100). |
| `page` | Integer | `1` | Nomor halaman data (berguna jika total data lebih dari `limit`). |

**Contoh URL Request lengkap:**
```text
GET https://sm.djalu.co.id/api/integration/turjagwali?start_date=2026-07-01&end_date=2026-07-31&limit=25&page=1
```

---

## 3. Format Response (JSON)

Semua permintaan akan mengembalikan struktur JSON yang sama (dengan isi `data` yang berbeda tergantung modul).

### Contoh Response Berhasil (HTTP 200 OK):

```json
{
    "status": "success",
    "module": "turjagwali",
    "data": [
        {
            "id_turjagwali": 1250,
            "tanggal": "2026-07-05 08:30:00",
            "piket": 5,
            "katgiat": "Patroli Pagi",
            "lokasi": "Jl. Merdeka Barat",
            "peristiwa": "Aman Terkendali",
            "ketcb": "Melaksanakan pengaturan lalu lintas, situasi lancar.",
            "koordinat": "-6.175392,106.827153",
            "created_at": "2026-07-05 08:35:12"
        },
        {
            "id_turjagwali": 1251,
            "tanggal": "2026-07-05 09:15:00",
            "piket": 12,
            "katgiat": "Pengawalan",
            "lokasi": "Bundaran HI",
            "peristiwa": "Lalu Lintas Padat",
            "ketcb": "Kepadatan volume kendaraan dari arah Sudirman.",
            "koordinat": "-6.195000,106.823000",
            "created_at": "2026-07-05 09:20:00"
        }
    ],
    "pagination": {
        "total": 350,
        "per_page": 25,
        "current_page": 1,
        "last_page": 14
    }
}
```

### Contoh Response Gagal (HTTP 404 Not Found):

Terjadi apabila Anda memasukkan nama modul yang salah (misal: `/api/integration/laporan-salah`).

```json
{
    "status": "error",
    "message": "Modul tidak ditemukan atau tidak diizinkan."
}
```

---

## 4. Panduan Implementasi (Bagi Developer Pihak Ketiga)

### Contoh Script Penarikan Data (JavaScript / Fetch API):
Jika Web Pihak Ketiga dibangun dengan JavaScript/React/Vue, Anda dapat menarik data seperti berikut:

```javascript
// Fungsi untuk mengambil data CCTV minggu ini
async function getCCTVData() {
    const url = 'https://sm.djalu.co.id/api/integration/cctv?limit=10';
    
    try {
        const response = await fetch(url, {
            method: 'GET',
            headers: {
                'Accept': 'application/json',
                'Authorization': 'Bearer ISI_TOKEN_JWT_ANDA_DISINI'
            }
        });
        
        const result = await response.json();
        
        if (result.status === 'success') {
            console.log("Total Data:", result.pagination.total);
            console.log("Data Pertama:", result.data[0]);
        }
    } catch (error) {
        console.error("Gagal menarik data:", error);
    }
}

getCCTVData();
```

### Contoh Script Penarikan Data (PHP / cURL):
Bagi web berbasis PHP (seperti Laravel, CodeIgniter, atau PHP Native):

```php
<?php
$url = "https://sm.djalu.co.id/api/integration/turjagwali?limit=10";

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Accept: application/json',
    'Authorization: Bearer ISI_TOKEN_JWT_ANDA_DISINI'
]);

$response = curl_exec($ch);
curl_close($ch);

$data = json_decode($response, true);

if ($data && $data['status'] == 'success') {
    foreach ($data['data'] as $row) {
        echo "Tanggal: " . $row['tanggal'] . " | Lokasi: " . $row['lokasi'] . "<br>";
    }
}
?>
```
