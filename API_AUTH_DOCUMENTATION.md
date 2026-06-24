# API Documentation: Authentication

## Base URL
`https://domain-anda.com/api/`

## Endpoints

### 1. Register
`POST /auth/register`

**Description**: Mendaftarkan user baru.

**Request Body (JSON)**:
- `username` (required, string, unique)
- `email` (required, string, email, unique)
- `nama` (required, string)
- `password` (required, string, confirmed)
- `password_confirmation` (required, string)
- `notelp` (optional, string)
- [Tambahan field sesuai validasi `UsmMasterReferensi`]

**Response**:
- `201 Created`: Pendaftaran berhasil.
- `403 Forbidden`: Registrasi dinonaktifkan.
- `422 Unprocessable Entity`: Validasi gagal.

---

### 2. Login
`POST /auth/login`

**Description**: Login user dan mendapatkan JWT.

**Request Body (JSON)**:
- `username` (required, string)
- `password` (required, string)

**Response**:
- `200 OK`:
  ```json
  {
    "status": "success",
    "data": {
      "access_token": "...",
      "token_type": "bearer",
      "expires_in": 3600
    }
  }
  ```
- `401 Unauthorized`: Username atau password salah.
- `403 Forbidden`: Akun pending atau dinonaktifkan.

---

### 3. Me (Profile)
`GET /auth/me`

**Description**: Mendapatkan data user yang sedang login.
**Header**: `Authorization: Bearer <token>`

---

### 4. Logout
`POST /auth/logout`

**Description**: Logout user (invalidate token).
**Header**: `Authorization: Bearer <token>`

---

### 5. Refresh
`POST /auth/refresh`

**Description**: Refresh token yang sudah expired.
**Header**: `Authorization: Bearer <token>`


Authorization: Bearer eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9...



{
  "status": "success",
  "count": 42,
  "data": [
    {
      "id_usm": 186,
      "username": "superadmin",
      "nama": "Super Administrator",
      "daerah": "",
      "wilayah": "",
      "role_name": "SuperAdmin / SA",
      "jwt_token": "eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJzdWIiOjE4NiwidXNlcm5hbWUiOiJzdXBlcmFkbWluIiwibmFtYSI6IlN1cGVyIEFkbWluaXN0cmF0b3IiLCJkYWVyYWgiOiIiLCJ3aWxheWFoIjoiIiwicm9sZV9pZCI6MSwiaWF0IjoxNzgyMjA2MDI0LCJleHAiOjE3ODQ3OTgwMjR9.nLzYRYhAAJCRkhS-2IVGh1orxBV8xEix56wgThrGiag"
    },
    {
      "id_usm": 187,
      "username": "admin",
      "nama": "Administrator",
      "daerah": "Banten",
      "wilayah": "",
      "role_name": "AdminPolda",
      "jwt_token": "eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJzdWIiOjE4NywidXNlcm5hbWUiOiJhZG1pbiIsIm5hbWEiOiJBZG1pbmlzdHJhdG9yIiwiZGFlcmFoIjoiQmFudGVuIiwid2lsYXlhaCI6IiIsInJvbGVfaWQiOjIsImlhdCI6MTc4MjIwNjAyNCwiZXhwIjoxNzg0Nzk4MDI0fQ.3KtDMtrSVCzEvk6TiB04ScDtvb9W0PoTYCbxUKK8Eic"
    },
    {
      "id_usm": 282,
      "username": "wasdalpus",
      "nama": "Dayat77",
      "daerah": "METRO JAYA",
      "wilayah": "Kota Jakarta Pusat",
      "role_name": "Executive1",
      "jwt_token": "eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJzdWIiOjI4MiwidXNlcm5hbWUiOiJ3YXNkYWxwdXMiLCJuYW1hIjoiRGF5YXQ3NyIsImRhZXJhaCI6Ik1FVFJPIEpBWUEiLCJ3aWxheWFoIjoiS290YSBKYWthcnRhIFB1c2F0Iiwicm9sZV9pZCI6NywiaWF0IjoxNzgyMjA2MDI0LCJleHAiOjE3ODQ3OTgwMjR9.XmFyYpzE2v1LwgtVh7vyqEk_VAmBrj_CCb4lG7H0Miw"
    },
    {
      "id_usm": 284,
      "username": "lebak1",
      "nama": "TMC Banten",
      "daerah": "Banten",
      "wilayah": "Kabupaten Lebak",
      "role_name": "Operator",
      "jwt_token": "eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJzdWIiOjI4NCwidXNlcm5hbWUiOiJsZWJhazEiLCJuYW1hIjoiVE1DIEJhbnRlbiIsImRhZXJhaCI6IkJhbnRlbiIsIndpbGF5YWgiOiJLYWJ1cGF0ZW4gTGViYWsiLCJyb2xlX2lkIjo0LCJpYXQiOjE3ODIyMDYwMjQsImV4cCI6MTc4NDc5ODAyNH0.1YYdXVHYq-l-701aTCc7oggdordz7xlHYcEqVAipa_0"
    },
    {
      "id_usm": 285,
      "username": "lebak2",
      "nama": "Akp Lebak 2",
      "daerah": "Banten",
      "wilayah": "",
      "role_name": "Operator",
      "jwt_token": "eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJzdWIiOjI4NSwidXNlcm5hbWUiOiJsZWJhazIiLCJuYW1hIjoiQWtwIExlYmFrIDIiLCJkYWVyYWgiOiJCYW50ZW4iLCJ3aWxheWFoIjoiIiwicm9sZV9pZCI6NCwiaWF0IjoxNzgyMjA2MDI0LCJleHAiOjE3ODQ3OTgwMjR9.K0ayl2jzVJtjubbgbm7nuVXwUlN0jdRKOi1q64f5Bk4"
    },
    {
      "id_usm": 286,
      "username": "wasdalbanten",
      "nama": "Pak Wasdal",
      "daerah": "Banten",
      "wilayah": "Kabupaten Lebak",
      "role_name": "Executive3",
      "jwt_token": "eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJzdWIiOjI4NiwidXNlcm5hbWUiOiJ3YXNkYWxiYW50ZW4iLCJuYW1hIjoiUGFrIFdhc2RhbCIsImRhZXJhaCI6IkJhbnRlbiIsIndpbGF5YWgiOiJLYWJ1cGF0ZW4gTGViYWsiLCJyb2xlX2lkIjo1LCJpYXQiOjE3ODIyMDYwMjQsImV4cCI6MTc4NDc5ODAyNH0.aqLDeGEvz62oXR6BSdi_lAZYFsXE64ZajngKcGtXZO8"
    },
    {
      "id_usm": 287,
      "username": "tangerang1",
      "nama": "TMC Tangerang",
      "daerah": "Banten",
      "wilayah": "",
      "role_name": "Operator",
      "jwt_token": "eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJzdWIiOjI4NywidXNlcm5hbWUiOiJ0YW5nZXJhbmcxIiwibmFtYSI6IlRNQyBUYW5nZXJhbmciLCJkYWVyYWgiOiJCYW50ZW4iLCJ3aWxheWFoIjoiIiwicm9sZV9pZCI6NCwiaWF0IjoxNzgyMjA2MDI0LCJleHAiOjE3ODQ3OTgwMjR9.E451koOzEYssClhjQZdHvnH9h6tydU1DeFvDkONR-68"
    },
    {
      "id_usm": 288,
      "username": "badung1",
      "nama": "TMC Badung",
      "daerah": "Bali",
      "wilayah": "Kabupaten Badung",
      "role_name": "Operator",
      "jwt_token": "eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJzdWIiOjI4OCwidXNlcm5hbWUiOiJiYWR1bmcxIiwibmFtYSI6IlRNQyBCYWR1bmciLCJkYWVyYWgiOiJCYWxpIiwid2lsYXlhaCI6IkthYnVwYXRlbiBCYWR1bmciLCJyb2xlX2lkIjo0LCJpYXQiOjE3ODIyMDYwMjQsImV4cCI6MTc4NDc5ODAyNH0.zuH5iEchiXXg8dZMP9i-Y4qJDB81buMRd1N0hyKWVsk"
    },
    {
      "id_usm": 289,
      "username": "wasdalbali",
      "nama": "Pak Wasdal Bali",
      "daerah": "Bali",
      "wilayah": "Kota Denpasar",
      "role_name": "Executive2",
      "jwt_token": "eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJzdWIiOjI4OSwidXNlcm5hbWUiOiJ3YXNkYWxiYWxpIiwibmFtYSI6IlBhayBXYXNkYWwgQmFsaSIsImRhZXJhaCI6IkJhbGkiLCJ3aWxheWFoIjoiS290YSBEZW5wYXNhciIsInJvbGVfaWQiOjYsImlhdCI6MTc4MjIwNjAyNCwiZXhwIjoxNzg0Nzk4MDI0fQ.U5EBWZd-1vnXlYMKyXtSjcChh1JJaQlQS6WPNNwxrxM"
    },
    {
      "id_usm": 290,
      "username": "gianyar",
      "nama": "TMC Gianyar",
      "daerah": "Bali",
      "wilayah": "Kabupaten Gianyar",
      "role_name": "Operator",
      "jwt_token": "eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJzdWIiOjI5MCwidXNlcm5hbWUiOiJnaWFueWFyIiwibmFtYSI6IlRNQyBHaWFueWFyIiwiZGFlcmFoIjoiQmFsaSIsIndpbGF5YWgiOiJLYWJ1cGF0ZW4gR2lhbnlhciIsInJvbGVfaWQiOjQsImlhdCI6MTc4MjIwNjAyNCwiZXhwIjoxNzg0Nzk4MDI0fQ.-F1jCLHSG0BCqe4ojpaLhPp5fyYJQnFYLHFLRZSb6Zg"
    },
    {
      "id_usm": 291,
      "username": "test",
      "nama": "Test",
      "daerah": "Banten",
      "wilayah": "Kota Cilegon",
      "role_name": "Operator",
      "jwt_token": "eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJzdWIiOjI5MSwidXNlcm5hbWUiOiJ0ZXN0IiwibmFtYSI6IlRlc3QiLCJkYWVyYWgiOiJCYW50ZW4iLCJ3aWxheWFoIjoiS290YSBDaWxlZ29uIiwicm9sZV9pZCI6NCwiaWF0IjoxNzgyMjA2MDI0LCJleHAiOjE3ODQ3OTgwMjR9.rXxfMfdNN_Lnl-4E3i11u8KDdb8iwspnIamHLpX_OL4"
    },
    {
      "id_usm": 292,
      "username": "dps1",
      "nama": "Tmc ditlantas",
      "daerah": "Bali",
      "wilayah": "Kota Denpasar",
      "role_name": "Operator",
      "jwt_token": "eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJzdWIiOjI5MiwidXNlcm5hbWUiOiJkcHMxIiwibmFtYSI6IlRtYyBkaXRsYW50YXMiLCJkYWVyYWgiOiJCYWxpIiwid2lsYXlhaCI6IktvdGEgRGVucGFzYXIiLCJyb2xlX2lkIjo0LCJpYXQiOjE3ODIyMDYwMjQsImV4cCI6MTc4NDc5ODAyNH0.yI9BvHpd6z2nGVXqsKFxBXC7diG5QBrUEy-Ripaaa90"
    },
    {
      "id_usm": 293,
      "username": "exe1_banten",
      "nama": "exe1_banten",
      "daerah": "Banten",
      "wilayah": "",
      "role_name": "Executive2",
      "jwt_token": "eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJzdWIiOjI5MywidXNlcm5hbWUiOiJleGUxX2JhbnRlbiIsIm5hbWEiOiJleGUxX2JhbnRlbiIsImRhZXJhaCI6IkJhbnRlbiIsIndpbGF5YWgiOiIiLCJyb2xlX2lkIjo2LCJpYXQiOjE3ODIyMDYwMjQsImV4cCI6MTc4NDc5ODAyNH0.WJkWtAnfC4zFCWO292iuHtf4bHkrSZQAiaCNBv8Hgtc"
    },
    {
      "id_usm": 352,
      "username": "Reza",
      "nama": "Muhammad Reza Pahlevi",
      "daerah": "METRO JAYA",
      "wilayah": "Kota Jakarta Pusat",
      "role_name": "SuperAdmin / SA",
      "jwt_token": "eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJzdWIiOjM1MiwidXNlcm5hbWUiOiJSZXphIiwibmFtYSI6Ik11aGFtbWFkIFJlemEgUGFobGV2aSIsImRhZXJhaCI6Ik1FVFJPIEpBWUEiLCJ3aWxheWFoIjoiS290YSBKYWthcnRhIFB1c2F0Iiwicm9sZV9pZCI6MSwiaWF0IjoxNzgyMjA2MDI0LCJleHAiOjE3ODQ3OTgwMjR9.Kl08x_u3nJaKFTmIVOU3RUCIbvqH7eoC5ZNXS1fHbaY"
    },
    {
      "id_usm": 353,
      "username": "Vincent",
      "nama": "Alvin Vincent Suot",
      "daerah": "Jawa Tengah",
      "wilayah": "Kota Surakarta",
      "role_name": "Operator",
      "jwt_token": "eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJzdWIiOjM1MywidXNlcm5hbWUiOiJWaW5jZW50IiwibmFtYSI6IkFsdmluIFZpbmNlbnQgU3VvdCIsImRhZXJhaCI6Ikphd2EgVGVuZ2FoIiwid2lsYXlhaCI6IktvdGEgU3VyYWthcnRhIiwicm9sZV9pZCI6NCwiaWF0IjoxNzgyMjA2MDI0LCJleHAiOjE3ODQ3OTgwMjR9.M-HCUE2RkOpk-2VEaRshIE48G8tFHRqTgLxB1W52gd4"
    },
    {
      "id_usm": 354,
      "username": "firdaus",
      "nama": "Firdaus Aulia",
      "daerah": "Bali",
      "wilayah": "Kota Denpasar",
      "role_name": "Operator",
      "jwt_token": "eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJzdWIiOjM1NCwidXNlcm5hbWUiOiJmaXJkYXVzIiwibmFtYSI6IkZpcmRhdXMgQXVsaWEiLCJkYWVyYWgiOiJCYWxpIiwid2lsYXlhaCI6IktvdGEgRGVucGFzYXIiLCJyb2xlX2lkIjo0LCJpYXQiOjE3ODIyMDYwMjQsImV4cCI6MTc4NDc5ODAyNH0.7Ls3bV6PWGX7Ial38gfqwWHFY7mO4TcMeOdTfcOenkQ"
    },
    {
      "id_usm": 355,
      "username": "chandra",
      "nama": "Chandra Widodo",
      "daerah": "Jawa Tengah",
      "wilayah": "Kota Semarang",
      "role_name": "Operator",
      "jwt_token": "eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJzdWIiOjM1NSwidXNlcm5hbWUiOiJjaGFuZHJhIiwibmFtYSI6IkNoYW5kcmEgV2lkb2RvIiwiZGFlcmFoIjoiSmF3YSBUZW5nYWgiLCJ3aWxheWFoIjoiS290YSBTZW1hcmFuZyIsInJvbGVfaWQiOjQsImlhdCI6MTc4MjIwNjAyNCwiZXhwIjoxNzg0Nzk4MDI0fQ.ygRHjACS6CEamzjLTZzacmiwUUxysTeaP9EvfRN8Ua0"
    },
    {
      "id_usm": 356,
      "username": "RezaPahlevi",
      "nama": "Reza Pahlevi",
      "daerah": "METRO JAYA",
      "wilayah": "Kota Jakarta Pusat",
      "role_name": "Operator",
      "jwt_token": "eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJzdWIiOjM1NiwidXNlcm5hbWUiOiJSZXphUGFobGV2aSIsIm5hbWEiOiJSZXphIFBhaGxldmkiLCJkYWVyYWgiOiJNRVRSTyBKQVlBIiwid2lsYXlhaCI6IktvdGEgSmFrYXJ0YSBQdXNhdCIsInJvbGVfaWQiOjQsImlhdCI6MTc4MjIwNjAyNCwiZXhwIjoxNzg0Nzk4MDI0fQ.H-vxGjoEnReG1JkFqKm-9vCcDankHEnjwDybPZSOk-A"
    },
    {
      "id_usm": 357,
      "username": "alfiantoramadhan",
      "nama": "Alfianto Ramadhan",
      "daerah": "Sumatera Utara",
      "wilayah": "Kota Medan",
      "role_name": "Operator",
      "jwt_token": "eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJzdWIiOjM1NywidXNlcm5hbWUiOiJhbGZpYW50b3JhbWFkaGFuIiwibmFtYSI6IkFsZmlhbnRvIFJhbWFkaGFuIiwiZGFlcmFoIjoiU3VtYXRlcmEgVXRhcmEiLCJ3aWxheWFoIjoiS290YSBNZWRhbiIsInJvbGVfaWQiOjQsImlhdCI6MTc4MjIwNjAyNCwiZXhwIjoxNzg0Nzk4MDI0fQ.67tLNCefZ0iwn1CTmehWo3hLZ4tFYaAsLZ-KSyq5q44"
    },
    {
      "id_usm": 358,
      "username": "KapoldaJateng",
      "nama": "Pak Kapolda Jateng",
      "daerah": "Jawa Tengah",
      "wilayah": "Kota Semarang",
      "role_name": "Executive2",
      "jwt_token": "eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJzdWIiOjM1OCwidXNlcm5hbWUiOiJLYXBvbGRhSmF0ZW5nIiwibmFtYSI6IlBhayBLYXBvbGRhIEphdGVuZyIsImRhZXJhaCI6Ikphd2EgVGVuZ2FoIiwid2lsYXlhaCI6IktvdGEgU2VtYXJhbmciLCJyb2xlX2lkIjo2LCJpYXQiOjE3ODIyMDYwMjQsImV4cCI6MTc4NDc5ODAyNH0.7Pip24Gn7wpZFO6XII5TOtfp4M9r3Ut6-D0C59L2u7k"
    },
    {
      "id_usm": 359,
      "username": "tmcpmj1",
      "nama": "Op TMC",
      "daerah": "METRO JAYA",
      "wilayah": "Kota Jakarta Pusat",
      "role_name": "Operator",
      "jwt_token": "eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJzdWIiOjM1OSwidXNlcm5hbWUiOiJ0bWNwbWoxIiwibmFtYSI6Ik9wIFRNQyIsImRhZXJhaCI6Ik1FVFJPIEpBWUEiLCJ3aWxheWFoIjoiS290YSBKYWthcnRhIFB1c2F0Iiwicm9sZV9pZCI6NCwiaWF0IjoxNzgyMjA2MDI0LCJleHAiOjE3ODQ3OTgwMjR9.ssb-pB5tidGgnWnQDQdP47M5vQ1NP8CE2dsOraUvqgk"
    },
    {
      "id_usm": 361,
      "username": "tmcpmj",
      "nama": "TMC PMJ 1",
      "daerah": "METRO JAYA",
      "wilayah": "Kota Jakarta Utara",
      "role_name": "Operator",
      "jwt_token": "eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJzdWIiOjM2MSwidXNlcm5hbWUiOiJ0bWNwbWoiLCJuYW1hIjoiVE1DIFBNSiAxIiwiZGFlcmFoIjoiTUVUUk8gSkFZQSIsIndpbGF5YWgiOiJLb3RhIEpha2FydGEgVXRhcmEiLCJyb2xlX2lkIjo0LCJpYXQiOjE3ODIyMDYwMjQsImV4cCI6MTc4NDc5ODAyNH0.uhhTAv6A4muJwnHfgMpIDU6eNf-7FBUkPEOpxLyuqsQ"
    },
    {
      "id_usm": 362,
      "username": "firman",
      "nama": "Firman Ferdi",
      "daerah": "Jawa Tengah",
      "wilayah": "Kota Semarang",
      "role_name": "Operator",
      "jwt_token": "eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJzdWIiOjM2MiwidXNlcm5hbWUiOiJmaXJtYW4iLCJuYW1hIjoiRmlybWFuIEZlcmRpIiwiZGFlcmFoIjoiSmF3YSBUZW5nYWgiLCJ3aWxheWFoIjoiS290YSBTZW1hcmFuZyIsInJvbGVfaWQiOjQsImlhdCI6MTc4MjIwNjAyNCwiZXhwIjoxNzg0Nzk4MDI0fQ.MHMs-kie_qlKGjUTgDxj7xQZuhap2sAtJ40yt2kLmCE"
    },
    {
      "id_usm": 363,
      "username": "2051",
      "nama": "Rizal",
      "daerah": "Jawa Tengah",
      "wilayah": "Kota Surakarta",
      "role_name": "Operator",
      "jwt_token": "eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJzdWIiOjM2MywidXNlcm5hbWUiOiIyMDUxIiwibmFtYSI6IlJpemFsIiwiZGFlcmFoIjoiSmF3YSBUZW5nYWgiLCJ3aWxheWFoIjoiS290YSBTdXJha2FydGEiLCJyb2xlX2lkIjo0LCJpYXQiOjE3ODIyMDYwMjQsImV4cCI6MTc4NDc5ODAyNH0.82k-hKww7ewgNM2K7akXfu2p5zhU47zzhha5Fs1ONoE"
    },
    {
      "id_usm": 364,
      "username": "mromialf",
      "nama": "M Romi Alfianto",
      "daerah": "Bali",
      "wilayah": "Kota Denpasar",
      "role_name": "Operator",
      "jwt_token": "eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJzdWIiOjM2NCwidXNlcm5hbWUiOiJtcm9taWFsZiIsIm5hbWEiOiJNIFJvbWkgQWxmaWFudG8iLCJkYWVyYWgiOiJCYWxpIiwid2lsYXlhaCI6IktvdGEgRGVucGFzYXIiLCJyb2xlX2lkIjo0LCJpYXQiOjE3ODIyMDYwMjQsImV4cCI6MTc4NDc5ODAyNH0.1E1WlXx-RE1zbtiNmx2ZKeLPCTjDe6YZLmuyxEftUyU"
    },
    {
      "id_usm": 365,
      "username": "elingjogja123",
      "nama": "Febrianto",
      "daerah": "DI Yogyakarta",
      "wilayah": "DITLANTAS",
      "role_name": "Operator",
      "jwt_token": "eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJzdWIiOjM2NSwidXNlcm5hbWUiOiJlbGluZ2pvZ2phMTIzIiwibmFtYSI6IkZlYnJpYW50byIsImRhZXJhaCI6IkRJIFlvZ3lha2FydGEiLCJ3aWxheWFoIjoiRElUTEFOVEFTIiwicm9sZV9pZCI6NCwiaWF0IjoxNzgyMjA2MDI0LCJleHAiOjE3ODQ3OTgwMjR9.sIg5uo86kgK7t4LM92WyBTbUlTZc-qJ1Idm9GE_FwdE"
    },
    {
      "id_usm": 366,
      "username": "1012",
      "nama": "Iin",
      "daerah": "Jawa Tengah",
      "wilayah": "Kota Surakarta",
      "role_name": "Operator",
      "jwt_token": "eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJzdWIiOjM2NiwidXNlcm5hbWUiOiIxMDEyIiwibmFtYSI6IklpbiIsImRhZXJhaCI6Ikphd2EgVGVuZ2FoIiwid2lsYXlhaCI6IktvdGEgU3VyYWthcnRhIiwicm9sZV9pZCI6NCwiaWF0IjoxNzgyMjA2MDI0LCJleHAiOjE3ODQ3OTgwMjR9.DekKekhpbSj0Xu326PeeCoRo14yYO9X3ogUT45zDlzQ"
    },
    {
      "id_usm": 367,
      "username": "friza01",
      "nama": "friza",
      "daerah": "Sumatera Utara",
      "wilayah": "Kota Medan",
      "role_name": "Operator",
      "jwt_token": "eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJzdWIiOjM2NywidXNlcm5hbWUiOiJmcml6YTAxIiwibmFtYSI6ImZyaXphIiwiZGFlcmFoIjoiU3VtYXRlcmEgVXRhcmEiLCJ3aWxheWFoIjoiS290YSBNZWRhbiIsInJvbGVfaWQiOjQsImlhdCI6MTc4MjIwNjAyNCwiZXhwIjoxNzg0Nzk4MDI0fQ.0eHHWNH7wA3MCbpKCzSh5IhehuUYJK3BW9l4Y_8DCMw"
    },
    {
      "id_usm": 368,
      "username": "willy.hartika19@gmail.com",
      "nama": "Wily",
      "daerah": "Sumatera Utara",
      "wilayah": "Kota Medan",
      "role_name": "Operator",
      "jwt_token": "eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJzdWIiOjM2OCwidXNlcm5hbWUiOiJ3aWxseS5oYXJ0aWthMTlAZ21haWwuY29tIiwibmFtYSI6IldpbHkiLCJkYWVyYWgiOiJTdW1hdGVyYSBVdGFyYSIsIndpbGF5YWgiOiJLb3RhIE1lZGFuIiwicm9sZV9pZCI6NCwiaWF0IjoxNzgyMjA2MDI0LCJleHAiOjE3ODQ3OTgwMjR9.B5eg5MgGLUoK9CG8VuZPeb50uxUs4NVxK45t4dAvsMU"
    },
    {
      "id_usm": 369,
      "username": "azrelprla",
      "nama": "Azrel Prila",
      "daerah": "DI Yogyakarta",
      "wilayah": "DITLANTAS",
      "role_name": "Operator",
      "jwt_token": "eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJzdWIiOjM2OSwidXNlcm5hbWUiOiJhenJlbHBybGEiLCJuYW1hIjoiQXpyZWwgUHJpbGEiLCJkYWVyYWgiOiJESSBZb2d5YWthcnRhIiwid2lsYXlhaCI6IkRJVExBTlRBUyIsInJvbGVfaWQiOjQsImlhdCI6MTc4MjIwNjAyNCwiZXhwIjoxNzg0Nzk4MDI0fQ.1FsCj1bmRYuNA4jRb4V2dLYEjAlfdKQ8KeEm_cES3iE"
    },
    {
      "id_usm": 370,
      "username": "sahdhan",
      "nama": "SAHDHAN MISBAKHUL ANAM",
      "daerah": "DI Yogyakarta",
      "wilayah": "DITLANTAS",
      "role_name": "Operator",
      "jwt_token": "eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJzdWIiOjM3MCwidXNlcm5hbWUiOiJzYWhkaGFuIiwibmFtYSI6IlNBSERIQU4gTUlTQkFLSFVMIEFOQU0iLCJkYWVyYWgiOiJESSBZb2d5YWthcnRhIiwid2lsYXlhaCI6IkRJVExBTlRBUyIsInJvbGVfaWQiOjQsImlhdCI6MTc4MjIwNjAyNCwiZXhwIjoxNzg0Nzk4MDI0fQ.qQ6VUKby6E2c3MDxQS62qD9eBfzxUWiB8r08rHh8cIA"
    },
    {
      "id_usm": 371,
      "username": "aqshalfauzan10",
      "nama": "Aqshal Alukalfauzan",
      "daerah": "Jawa Barat",
      "wilayah": "Kota Bandung",
      "role_name": "Operator",
      "jwt_token": "eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJzdWIiOjM3MSwidXNlcm5hbWUiOiJhcXNoYWxmYXV6YW4xMCIsIm5hbWEiOiJBcXNoYWwgQWx1a2FsZmF1emFuIiwiZGFlcmFoIjoiSmF3YSBCYXJhdCIsIndpbGF5YWgiOiJLb3RhIEJhbmR1bmciLCJyb2xlX2lkIjo0LCJpYXQiOjE3ODIyMDYwMjQsImV4cCI6MTc4NDc5ODAyNH0.HEeYZ-rjY9_T6rv8weoEBxygRKATWb85Vo8miA0XWe0"
    },
    {
      "id_usm": 372,
      "username": "Rosiana",
      "nama": "Rosiana Sinaga",
      "daerah": "Jawa Barat",
      "wilayah": "Kabupaten Bandung",
      "role_name": "Operator",
      "jwt_token": "eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJzdWIiOjM3MiwidXNlcm5hbWUiOiJSb3NpYW5hIiwibmFtYSI6IlJvc2lhbmEgU2luYWdhIiwiZGFlcmFoIjoiSmF3YSBCYXJhdCIsIndpbGF5YWgiOiJLYWJ1cGF0ZW4gQmFuZHVuZyIsInJvbGVfaWQiOjQsImlhdCI6MTc4MjIwNjAyNCwiZXhwIjoxNzg0Nzk4MDI0fQ.YLpQ_t6lLlR1r3vTq5_w_Lw-3uhLNaPlwwNZwwG8XlQ"
    },
    {
      "id_usm": 373,
      "username": "Fadwa42",
      "nama": "M. Fadwa Mufriz",
      "daerah": "Jawa Barat",
      "wilayah": "Kabupaten Bandung",
      "role_name": "Operator",
      "jwt_token": "eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJzdWIiOjM3MywidXNlcm5hbWUiOiJGYWR3YTQyIiwibmFtYSI6Ik0uIEZhZHdhIE11ZnJpeiIsImRhZXJhaCI6Ikphd2EgQmFyYXQiLCJ3aWxheWFoIjoiS2FidXBhdGVuIEJhbmR1bmciLCJyb2xlX2lkIjo0LCJpYXQiOjE3ODIyMDYwMjQsImV4cCI6MTc4NDc5ODAyNH0.K2ikIWAPI0RqzW4FFU0EkUgU_OaKCxyM3n3zQal_a6Y"
    },
    {
      "id_usm": 374,
      "username": "Vincents",
      "nama": "Vincent",
      "daerah": "Jawa Tengah",
      "wilayah": "Kota Surakarta",
      "role_name": "Operator",
      "jwt_token": "eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJzdWIiOjM3NCwidXNlcm5hbWUiOiJWaW5jZW50cyIsIm5hbWEiOiJWaW5jZW50IiwiZGFlcmFoIjoiSmF3YSBUZW5nYWgiLCJ3aWxheWFoIjoiS290YSBTdXJha2FydGEiLCJyb2xlX2lkIjo0LCJpYXQiOjE3ODIyMDYwMjQsImV4cCI6MTc4NDc5ODAyNH0.kzUSEozgg8OjFjldLolWi0BPFHoO9udn1SD6hupvVKo"
    },
    {
      "id_usm": 375,
      "username": "ahadiprayuda",
      "nama": "yuda",
      "daerah": "Sumatera Utara",
      "wilayah": "Kota Medan",
      "role_name": "Operator",
      "jwt_token": "eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJzdWIiOjM3NSwidXNlcm5hbWUiOiJhaGFkaXByYXl1ZGEiLCJuYW1hIjoieXVkYSIsImRhZXJhaCI6IlN1bWF0ZXJhIFV0YXJhIiwid2lsYXlhaCI6IktvdGEgTWVkYW4iLCJyb2xlX2lkIjo0LCJpYXQiOjE3ODIyMDYwMjQsImV4cCI6MTc4NDc5ODAyNH0.hrpxxiSFIJPJo4azrpFs7M_qvCXlYOoIlc00x6H6ZOQ"
    },
    {
      "id_usm": 376,
      "username": "yayat123",
      "nama": "yayat",
      "daerah": "Banten",
      "wilayah": "Kabupaten Pandeglang",
      "role_name": "Operator",
      "jwt_token": "eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJzdWIiOjM3NiwidXNlcm5hbWUiOiJ5YXlhdDEyMyIsIm5hbWEiOiJ5YXlhdCIsImRhZXJhaCI6IkJhbnRlbiIsIndpbGF5YWgiOiJLYWJ1cGF0ZW4gUGFuZGVnbGFuZyIsInJvbGVfaWQiOjQsImlhdCI6MTc4MjIwNjAyNCwiZXhwIjoxNzg0Nzk4MDI0fQ.uvxOoktZW6vDfPQEUsyZQM3fgbKzCG3c_4J0XN4uPqI"
    },
    {
      "id_usm": 377,
      "username": "fuadi",
      "nama": "Moh Izzul Fuadi",
      "daerah": "DI Yogyakarta",
      "wilayah": "DITLANTAS",
      "role_name": "Operator",
      "jwt_token": "eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJzdWIiOjM3NywidXNlcm5hbWUiOiJmdWFkaSIsIm5hbWEiOiJNb2ggSXp6dWwgRnVhZGkiLCJkYWVyYWgiOiJESSBZb2d5YWthcnRhIiwid2lsYXlhaCI6IkRJVExBTlRBUyIsInJvbGVfaWQiOjQsImlhdCI6MTc4MjIwNjAyNCwiZXhwIjoxNzg0Nzk4MDI0fQ.grS3KgAMwwVdCgYTMXJIMBxYGtyRzTVKqtqd-6y_CmQ"
    },
    {
      "id_usm": 378,
      "username": "baden",
      "nama": "baden",
      "daerah": "Jawa Barat",
      "wilayah": "Kabupaten Bogor",
      "role_name": "Operator",
      "jwt_token": "eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJzdWIiOjM3OCwidXNlcm5hbWUiOiJiYWRlbiIsIm5hbWEiOiJiYWRlbiIsImRhZXJhaCI6Ikphd2EgQmFyYXQiLCJ3aWxheWFoIjoiS2FidXBhdGVuIEJvZ29yIiwicm9sZV9pZCI6NCwiaWF0IjoxNzgyMjA2MDI0LCJleHAiOjE3ODQ3OTgwMjR9.5F5tZOMYzaudCIbolajBJ2SGYcPLYMR78kMIWLIZA-U"
    },
    {
      "id_usm": 379,
      "username": "tmcnganjuk",
      "nama": "TMC Nganjuk",
      "daerah": "Jawa Timur",
      "wilayah": "Kabupaten Nganjuk",
      "role_name": "Operator",
      "jwt_token": "eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJzdWIiOjM3OSwidXNlcm5hbWUiOiJ0bWNuZ2FuanVrIiwibmFtYSI6IlRNQyBOZ2FuanVrIiwiZGFlcmFoIjoiSmF3YSBUaW11ciIsIndpbGF5YWgiOiJLYWJ1cGF0ZW4gTmdhbmp1ayIsInJvbGVfaWQiOjQsImlhdCI6MTc4MjIwNjAyNCwiZXhwIjoxNzg0Nzk4MDI0fQ.Bwi4GPQTRSTCRq6KWhd5Fo6AAqen9Lv8d4Z5PdX_s6g"
    },
    {
      "id_usm": 380,
      "username": "wasdaljateng",
      "nama": "Akp Heri",
      "daerah": "Jawa Tengah",
      "wilayah": "Kota Semarang",
      "role_name": "Executive2",
      "jwt_token": "eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJzdWIiOjM4MCwidXNlcm5hbWUiOiJ3YXNkYWxqYXRlbmciLCJuYW1hIjoiQWtwIEhlcmkiLCJkYWVyYWgiOiJKYXdhIFRlbmdhaCIsIndpbGF5YWgiOiJLb3RhIFNlbWFyYW5nIiwicm9sZV9pZCI6NiwiaWF0IjoxNzgyMjA2MDI0LCJleHAiOjE3ODQ3OTgwMjR9.tZEdkvFRDLCNps63AN9f_MuK3cCwoZdApaUMktkjESY"
    },
    {
      "id_usm": 381,
      "username": "tmcpati",
      "nama": "TMC Pati",
      "daerah": "Jawa Tengah",
      "wilayah": "Kabupaten Pati",
      "role_name": "Operator",
      "jwt_token": "eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJzdWIiOjM4MSwidXNlcm5hbWUiOiJ0bWNwYXRpIiwibmFtYSI6IlRNQyBQYXRpIiwiZGFlcmFoIjoiSmF3YSBUZW5nYWgiLCJ3aWxheWFoIjoiS2FidXBhdGVuIFBhdGkiLCJyb2xlX2lkIjo0LCJpYXQiOjE3ODIyMDYwMjQsImV4cCI6MTc4NDc5ODAyNH0.RpFJGB5Jx8_ZigN7fTaE7dj9dkC1512s6IUgdbIqOvw"
    }
  ]
}


