# Dokumentasi API Smart Backoffice untuk Flutter

Dokumentasi ini berisi daftar *endpoint* API yang dapat digunakan oleh aplikasi Flutter untuk berinteraksi dengan sistem Smart Backoffice, khususnya untuk kebutuhan *USM Operator* dan pengelolaan data Turjagwali.

---

## 1. Autentikasi (Login)

Sistem menyediakan dua jenis *endpoint* login. Disarankan menggunakan **JWT Token Login** agar aplikasi dapat mengakses API lain yang membutuhkan otorisasi di masa mendatang.

### A. Login dengan JWT Token (Direkomendasikan)
Menghasilkan *Bearer Token* (JWT) yang memiliki masa berlaku dan lebih aman.

- **URL:** `/api/auth/login`
- **Method:** `POST`
- **Headers:**
  - `Accept: application/json`
  - `Content-Type: application/json`

**Body Request:**
```json
{
    "username": "email_atau_username",
    "password": "password_user"
}
```

**Response Berhasil (200 OK):**
```json
{
    "status": "success",
    "data": {
        "access_token": "eyJ0eXAiOiJKV1QiLCJhbGciOi...",
        "token_type": "bearer",
        "expires_in": 2592000
    }
}
```

**Response Gagal (401 Unauthorized):**
```json
{
    "status": "error",
    "message": "Username atau password salah."
}
```

---

### B. Login Sederhana (Petugas Tracking)
Digunakan jika Anda hanya membutuhkan `userId` tanpa sistem token Bearer.

- **URL:** `/api/login`
- **Method:** `POST`
- **Headers:**
  - `Accept: application/json`
  - `Content-Type: application/json`

**Body Request:**
```json
{
    "username": "email_atau_username",
    "password": "password_user"
}
```

**Response Berhasil (200 OK):**
```json
{
    "userId": 5,
    "username": "operator_jaksel",
    "nama": "Budi Santoso"
}
```

---

## 2. Modul Turjagwali

API ini digunakan untuk mengambil data laporan Turjagwali, dengan dukungan pencarian, paginasi, dan filter spesifik untuk operator.

### Ambil Data Turjagwali
- **URL:** `/api/turjagwali`
- **Method:** `GET`
- **Headers:**
  - `Accept: application/json`
  - `Authorization: Bearer <token_anda>` *(Opsional, jika rute ini nantinya diproteksi)*

**Query Parameters (Opsional):**
| Parameter | Tipe | Default | Deskripsi |
| :--- | :--- | :--- | :--- |
| `operator_id` | Integer | `null` | Filter data berdasarkan ID USM Operator (merujuk pada kolom `piket`). |
| `limit` | Integer | `10` | Menentukan jumlah data yang dikembalikan per halaman. |
| `search` | String | `null` | Kata kunci pencarian (mencari ke kolom lokasi, peristiwa, nama operator, dll). |
| `page` | Integer | `1` | Navigasi halaman untuk paginasi. |

**Contoh URL Endpoint:**
```text
GET /api/turjagwali?operator_id=5&limit=15&page=1
```

**Response Berhasil (200 OK):**
```json
{
    "status": "success",
    "data": [
        {
            "id_turjagwali": 1,
            "tanggal": "2026-06-24",
            "piket": 5,
            "katgiat": "Patroli Rutin",
            "lokasi": "Jalan Sudirman",
            "peristiwa": "Aman Terkendali",
            "ketcb": "Dilakukan himbauan kepada masyarakat",
            "fotogiat": "/storage/turjagwali_giat/foto.jpg",
            "fotolokasi": null,
            "koordinat": "-6.200000,106.816666",
            "petugaslap": "Bripka Andi",
            "faktor": "Manusia",
            "penyebab": "Kelalaian",
            "usm_nama": "Budi Santoso",
            "daerah_nama": "Polda Metro Jaya",
            "wilayah_nama": "Polres Jakarta Selatan"
        }
    ],
    "pagination": {
        "total": 50,
        "per_page": 15,
        "current_page": 1,
        "last_page": 4
    }
}
```
