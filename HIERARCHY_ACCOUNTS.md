# 📋 AKUN HIERARKI - SMART BACK OFFICE

## 👥 DAFTAR AKUN YANG SUDAH DIBUAT

### **1. Admin (Level Operasional)**
- **Username:** `admin`
- **Password:** `admin123`
- **Email:** admin@smartbackoffice.com
- **Nama:** Administrator
- **Level:** Operasional (tidak ada wilayah/daerah)
- **Menu yang ditampilkan:**
  - Home
  - Giat Rutin (Operasional)
  - Quick Respons (Operasional)
  - Ops Kepolisian (Operasional)
  - User Management (jika ada permission)
  - Roles (jika ada permission)
  - Role Permissions (jika ada permission)

### **2. Test User (Level Operasional)**
- **Username:** `test`
- **Password:** `test123`
- **Email:** test@smartbackoffice.com
- **Nama:** Test User
- **Level:** Operasional (tidak ada wilayah/daerah)
- **Menu yang ditampilkan:**
  - Home
  - Giat Rutin (Operasional)
  - Quick Respons (Operasional)
  - Ops Kepolisian (Operasional)
  - User Management (jika ada permission)
  - Roles (jika ada permission)
  - Role Permissions (jika ada permission)

### **3. User Wilayah**
- **Username:** `wilayah`
- **Password:** `wilayah123`
- **Email:** wilayah@smartbackoffice.com
- **Nama:** User Wilayah
- **Level:** Wilayah
- **Wilayah:** WILAYAH_01
- **Daerah:** null
- **Menu yang ditampilkan:**
  - Home
  - Giat Rutin (Operasional)
  - Quick Respons (Operasional)
  - Ops Kepolisian (Operasional)
  - **Wilayah - Ops Kepolisian** (Menu khusus level wilayah)
  - User Management (jika ada permission)
  - Roles (jika ada permission)
  - Role Permissions (jika ada permission)

### **4. User Daerah**
- **Username:** `daerah`
- **Password:** `daerah123`
- **Email:** daerah@smartbackoffice.com
- **Nama:** User Daerah
- **Level:** Daerah
- **Daerah:** DAERAH_01
- **Wilayah:** null
- **Menu yang ditampilkan:**
  - Home
  - Giat Rutin (Operasional)
  - Quick Respons (Operasional)
  - Ops Kepolisian (Operasional)
  - **Wilayah - Ops Kepolisian** (Menu level wilayah)
  - **Daerah - Ops Kepolisian** (Menu khusus level daerah)
  - User Management (jika ada permission)
  - Roles (jika ada permission)
  - Role Permissions (jika ada permission)

### **5. User Nasional**
- **Username:** `nasional`
- **Password:** `nasional123`
- **Email:** nasional@smartbackoffice.com
- **Nama:** User Nasional
- **Level:** Nasional
- **Daerah:** null
- **Wilayah:** null
- **Menu yang ditampilkan:**
  - Home
  - Giat Rutin (Operasional)
  - Quick Respons (Operasional)
  - Ops Kepolisian (Operasional)
  - **Wilayah - Ops Kepolisian** (Menu level wilayah)
  - **Daerah - Ops Kepolisian** (Menu level daerah)
  - **Nasional - Ops Kepolisian** (Menu khusus level nasional)
  - User Management (jika ada permission)
  - Roles (jika ada permission)
  - Role Permissions (jika ada permission)

---

## 🎯 LOGIKA FILTER MENU BERDASARKAN HIERARKI

### **Level Operasional** (tidak ada wilayah/daerah)
- ✅ Menu operasional (tanpa prefix)
- ❌ Menu wilayah (wil/*)
- ❌ Menu daerah (da/*)
- ❌ Menu nasional (nas/*)

### **Level Wilayah** (ada wilayah, tidak ada daerah)
- ✅ Menu operasional (tanpa prefix)
- ✅ Menu wilayah (wil/*)
- ❌ Menu daerah (da/*)
- ❌ Menu nasional (nas/*)

### **Level Daerah** (ada daerah, tidak ada wilayah)
- ✅ Menu operasional (tanpa prefix)
- ✅ Menu wilayah (wil/*)
- ✅ Menu daerah (da/*)
- ❌ Menu nasional (nas/*)

### **Level Nasional** (tidak ada wilayah/daerah)
- ✅ Menu operasional (tanpa prefix)
- ✅ Menu wilayah (wil/*)
- ✅ Menu daerah (da/*)
- ✅ Menu nasional (nas/*)

---

## 🔐 CARA MENENTUKAN LEVEL USER

Level user ditentukan berdasarkan field `wilayah` dan `daerah` di tabel `usm`:

```php
// Jika user memiliki wilayah → Level Wilayah
if (!empty($user->wilayah)) {
    $level = 'wilayah';
}
// Jika user memiliki daerah (tapi tidak ada wilayah) → Level Daerah
elseif (!empty($user->daerah)) {
    $level = 'daerah';
}
// Jika tidak ada wilayah dan daerah → Level Nasional
else {
    $level = 'nasional';
}
```

---

## 📝 CARA MENAMBAHKAN USER BARU

### **Via Seeder (Recommended)**
```php
// Di database/seeders/UsmSeeder.php
Usm::firstOrCreate(
    ['username' => 'username_baru'],
    [
        'username' => 'username_baru',
        'email' => 'email@example.com',
        'password' => Hash::make('password123'),
        'nama' => 'Nama User',
        'account_status' => 'active',
        'user_role_id' => $adminRoleId,
        'wilayah' => 'WILAYAH_01', // Untuk level wilayah
        // atau
        'daerah' => 'DAERAH_01', // Untuk level daerah
        // atau
        // Tidak set wilayah dan daerah untuk level nasional
    ]
);
```

### **Via Admin Panel**
1. Login sebagai admin
2. Masuk ke User Management
3. Tambah user baru
4. Set field `wilayah` atau `daerah` sesuai kebutuhan

---

## ✅ VERIFIKASI

Untuk memverifikasi bahwa menu ditampilkan sesuai hierarki:

1. **Login sebagai user wilayah:**
   - Login dengan username: `wilayah`, password: `wilayah123`
   - Sidebar harus menampilkan: Menu operasional + Menu Wilayah
   - Sidebar TIDAK menampilkan: Menu Daerah, Menu Nasional

2. **Login sebagai user daerah:**
   - Login dengan username: `daerah`, password: `daerah123`
   - Sidebar harus menampilkan: Menu operasional + Menu Wilayah + Menu Daerah
   - Sidebar TIDAK menampilkan: Menu Nasional

3. **Login sebagai user nasional:**
   - Login dengan username: `nasional`, password: `nasional123`
   - Sidebar harus menampilkan: Semua menu (Operasional + Wilayah + Daerah + Nasional)

4. **Login sebagai admin/test (operasional):**
   - Login dengan username: `admin`, password: `admin123`
   - Sidebar harus menampilkan: Hanya menu operasional
   - Sidebar TIDAK menampilkan: Menu Wilayah, Daerah, Nasional

---

## 🔄 UPDATE SEEDER

Jika ingin mengupdate seeder, jalankan:

```bash
php artisan db:seed --class=UsmSeeder
```

Atau untuk refresh semua seeder:

```bash
php artisan migrate:fresh --seed
```

---

**Dokumen ini menjelaskan akun hierarki yang sudah dibuat dan bagaimana menu ditampilkan sesuai level user.**
