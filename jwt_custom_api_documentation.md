# Dokumentasi Autentikasi API JWT Kustom

Dokumentasi ini menjelaskan implementasi autentikasi **Stateless JWT (JSON Web Token)** kustom pada aplikasi **Smart Backoffice**. Sistem ini dirancang mandiri tanpa dependensi library eksternal guna mempermudah portabilitas dan testing pada server Laragon/Windows.

---

## 1. Arsitektur & Cara Kerja

Sistem ini didukung oleh tiga komponen utama:
* **`SimpleJwtService.php`**: Mengenkode (HS256) dan mendekode token JWT. Menggunakan `APP_KEY` Laravel Anda sebagai signature key untuk memvalidasi keaslian token.
* **`SimpleJwtGuard.php`**: Custom Guard Driver yang didaftarkan ke Laravel Auth Manager. Guard ini mengekstrak Bearer token dari request header `Authorization: Bearer <token>`, mendekode payload, dan mencari user terkait secara otomatis.
* **`Usm.php`**: Model user yang direpresentasikan untuk setiap akun di Smart Backoffice.

---

## 2. Struktur Payload JWT Token

Setiap token yang dihasilkan mengandung klaim data pengguna berikut:
```json
{
  "sub": 12,                  // ID User (id_usm)
  "username": "polres_kudus", // Username akun
  "nama": "Polres Kudus",     // Nama akun
  "daerah": "polda_jateng",   // Tingkat Polda (daerah)
  "wilayah": "KABUPATEN KUDUS",// Tingkat Polres (wilayah)
  "role_id": 4,               // ID Role
  "iat": 1719114000,          // Waktu token dibuat
  "exp": 1721706000           // Waktu kedaluwarsa token (30 hari)
}
```

---

## 3. Daftar Endpoint API

### A. List Semua Token & Wilayah (Untuk Development / Postman)
Digunakan untuk melihat seluruh akun aktif beserta daerah, wilayah, dan token JWT siap pakai. Sangat berguna untuk langsung disalin ke Postman tanpa perlu login manual.

* **URL:** `/api/auth/all-tokens`
* **Method:** `GET`
* **Headers:** `Accept: application/json`
* **Response Contoh:**
```json
{
  "status": "success",
  "count": 15,
  "data": [
    {
      "id_usm": 12,
      "username": "polres_kudus",
      "nama": "Polres Kudus",
      "daerah": "polda_jateng",
      "wilayah": "KABUPATEN KUDUS",
      "role_name": "Operator Polres",
      "jwt_token": "eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJzdWIiOjEyLCJ1c2Vy..."
    }
  ]
}
```

---

### B. Login API
Digunakan oleh aplikasi mobile/client untuk mendapatkan token autentikasi.

* **URL:** `/api/auth/login`
* **Method:** `POST`
* **Headers:** 
  * `Content-Type: application/json`
  * `Accept: application/json`
* **Body:**
```json
{
  "username": "polres_kudus",
  "password": "password123"
}
```
* **Response Sukses (200):**
```json
{
  "status": "success",
  "data": {
    "access_token": "eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJzdWIiOjEyLCJ1c2Vy...",
    "token_type": "bearer",
    "expires_in": 2592000
  }
}
```

---

### C. Profil Saya (Protected Endpoint)
Mendapatkan profil lengkap akun pemilik token yang sedang login.

* **URL:** `/api/auth/me`
* **Method:** `GET`
* **Headers:**
  * `Authorization: Bearer <jwt_token>`
  * `Accept: application/json`
* **Response Contoh:**
```json
{
  "status": "success",
  "data": {
    "id_usm": 12,
    "username": "polres_kudus",
    "email": "kudus@polri.go.id",
    "nama": "Polres Kudus",
    "daerah": "polda_jateng",
    "wilayah": "KABUPATEN KUDUS",
    "user_role_id": 4,
    "account_status": "active"
  }
}
```

---

## 4. Cara Pengujian di Postman

1. **Ambil Token Cepat:**
   * Kirim request `GET` ke `{{base_url}}/api/auth/all-tokens`.
   * Pilih user yang ingin Anda simulasikan (misal user Polres Kudus).
   * Salin value `jwt_token`.
2. **Kirim Request Terproteksi:**
   * Di request Postman baru (misal `/api/auth/me`), masuk ke tab **Authorization**.
   * Pilih tipe **Bearer Token** pada dropdown Type.
   * Tempelkan `jwt_token` yang tadi Anda salin ke input box **Token**.
   * Kirim request Anda.
