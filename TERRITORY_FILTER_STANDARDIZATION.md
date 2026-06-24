# Territory Filter Standardization Report

**Date:** 2026-06-22  
**Status:** ✅ SELESAI untuk controllers sederhana, ⚠️ PENDING untuk controllers kompleks

## 🎯 Tujuan
Menstandardisasi implementasi territory filter di semua controllers agar konsisten dengan `OperasionalController` yang sudah benar.

## 📋 Standard Pattern (dari OperasionalController)

### ✅ Pattern yang BENAR:
```php
public function index(Request $request)
{
    $query = DB::table('table_name as t')
        ->leftJoin('usm', 'usm.id_usm', '=', 't.piket')
        ->select('t.*', 'usm.nama', 'usm.daerah', 'usm.wilayah');

    // Territory filter - TANPA role check manual
    HierarchicalHelper::applyTerritoryScopeForTable($query, 'table_name', 't');
    
    // USM filters (daerah/wilayah)
    \App\Support\TerritoryIndexFilters::applyUsmFilters($query, $request);
    
    // ... other filters ...
    
    return view('module.index', array_merge([
        'data' => $data,
        // ... other data ...
    ], \App\Support\TerritoryIndexFilters::viewBundle($request)));
}

protected function buildIndexQuery(Request $request)
{
    $query = DB::table('table_name as t')
        ->leftJoin('usm', 'usm.id_usm', '=', 't.piket')
        ->select('t.*', 'usm.nama', 'usm.daerah', 'usm.wilayah');

    // Territory filter - SAME as index()
    HierarchicalHelper::applyTerritoryScopeForTable($query, 'table_name', 't');
    \App\Support\TerritoryIndexFilters::applyUsmFilters($query, $request);
    
    // ... other filters ...
    
    return $query->orderBy(...);
}
```

### ❌ Pattern yang SALAH:
```php
// SALAH #1: Manual role check
$user = Auth::user();
if ($user && (int) $user->user_role_id !== 1) {
    HierarchicalHelper::applyTerritoryScopeForTable($query, 'table', 't');
}

// SALAH #2: Custom applyHierarchy method dengan check manual
private function applyHierarchy($query): void
{
    $user = Auth::user();
    if (! $user || HierarchicalHelper::viewerBypassesTerritoryScope()) {
        return;
    }
    HierarchicalHelper::applyTerritoryScopeForTable($query, 'table', 't');
}

// SALAH #3: Tidak ada TerritoryIndexFilters::applyUsmFilters()
// - User tidak bisa filter by daerah/wilayah

// SALAH #4: Tidak ada TerritoryIndexFilters::viewBundle()
// - View tidak dapat data filter daerah/wilayah
```

## 🔧 Controllers yang Sudah Diperbaiki

### ✅ 1. ItSupportController
**Masalah:**
- Manual role check: `if ($user && (int) $user->user_role_id !== 1)`
- Tidak ada `TerritoryIndexFilters::applyUsmFilters()`
- Tidak ada `TerritoryIndexFilters::viewBundle()`

**Perbaikan:**
- Hapus role check manual
- Tambahkan `TerritoryIndexFilters::applyUsmFilters($query, $request)`
- Tambahkan `TerritoryIndexFilters::viewBundle($request)` ke view
- Update `index()` dan `buildIndexQuery()`

### ✅ 2. FaskesController
**Masalah:**
- Manual role check: `if ($user && (int) $user->user_role_id !== 1)`
- Tidak ada `TerritoryIndexFilters::applyUsmFilters()`
- Tidak ada `TerritoryIndexFilters::viewBundle()`

**Perbaikan:**
- Hapus role check manual
- Tambahkan `TerritoryIndexFilters::applyUsmFilters($query, $request)`
- Tambahkan `TerritoryIndexFilters::viewBundle($request)` ke view
- Update `index()` dan `buildIndexQuery()`

### ✅ 3. TerminalController
**Masalah:**
- Tidak ada `TerritoryIndexFilters::applyUsmFilters()`
- Tidak ada `TerritoryIndexFilters::viewBundle()`

**Perbaikan:**
- Tambahkan `TerritoryIndexFilters::applyUsmFilters($query, $request)`
- Tambahkan `TerritoryIndexFilters::viewBundle($request)` ke view
- Update `index()` dan `buildIndexQuery()`

### ✅ 4. TempatIbadahController
**Masalah:**
- Manual role check: `if ($user && (int) $user->user_role_id !== 1)`
- Tidak ada `TerritoryIndexFilters::applyUsmFilters()`
- Tidak ada `TerritoryIndexFilters::viewBundle()`

**Perbaikan:**
- Hapus role check manual
- Tambahkan `TerritoryIndexFilters::applyUsmFilters($query, $request)`
- Tambahkan `TerritoryIndexFilters::viewBundle($request)` ke view
- Update `index()` dan `buildIndexQuery()`

### ✅ 5. ObjekWisataController
**Masalah:**
- Custom method `applyHierarchy()` dengan manual check
- Tidak ada `TerritoryIndexFilters::applyUsmFilters()`
- Tidak ada `TerritoryIndexFilters::viewBundle()`

**Perbaikan:**
- Hapus method `applyHierarchy()`
- Ganti dengan direct call `HierarchicalHelper::applyTerritoryScopeForTable()`
- Tambahkan `TerritoryIndexFilters::applyUsmFilters($query, $request)`
- Tambahkan `TerritoryIndexFilters::viewBundle($request)` ke view
- Update `index()` dan `buildIndexQuery()`

## ✅ Controllers yang SUDAH BENAR (Tidak Perlu Diperbaiki)

### ✅ 6. JalanGangguanController
**Status:** SUDAH BENAR ✅
- Sudah menggunakan `HierarchicalHelper::applyTerritoryScopeForTable()` tanpa role check
- Sudah ada `TerritoryIndexFilters::applyUsmFilters()`
- Sudah ada `TerritoryIndexFilters::viewBundle()`
- File formatnya aneh (banyak line break) tapi implementasinya correct

### ✅ 7. KunjunganWisataController
**Status:** SUDAH BENAR ✅
- Sudah menggunakan `HierarchicalHelper::applyTerritoryScopeForTable()` tanpa role check
- Sudah ada `TerritoryIndexFilters::applyUsmFilters()`
- Sudah ada `TerritoryIndexFilters::viewBundle()`
- File formatnya aneh (banyak line break) tapi implementasinya correct

## ⚠️ Controllers Kompleks (Perlu Penanganan Khusus)

### ⚠️ 8. DataPerangkatController
**Kompleksitas:**
- Ada logic `readTableForUser()` yang memilih antara `wil_dataperangkat`, `nas_datadevice`, atau `dataperangkat`
- Menggunakan view aggregation untuk operator role
- Sudah ada custom `applyIndexFilters()` method
- Sudah ada `TerritoryIndexFilters::applyUsmFilters()` di `applyIndexFilters()`
- Sudah ada `TerritoryIndexFilters::viewBundle()` di view

**Status:** SUDAH ADA territory filter tapi perlu verifikasi konsistensi dengan pattern baru

**Rekomendasi:**
- Tetap pertahankan logic `readTableForUser()` karena spesifik ke kebutuhan bisnis
- Pastikan `HierarchicalHelper::applyTerritoryScopeForTable()` dipanggil tanpa role check
- Verifikasi `TerritoryIndexFilters::applyUsmFilters()` sudah ada di semua query path

### ⚠️ 9. DataCctvController
**Kompleksitas:**
- Ada logic `readTableForUser()` dan `baseTableForWrite()`
- Memilih antara `wil_datacctv`, `nas_datacctv`, `datacctv`, atau `patrolicctv`
- Ada special logic untuk `CctvController::usesPatrolSchema()`
- Multiple table handling yang kompleks
- Sudah ada `TerritoryIndexFilters::applyUsmFilters()` di `applyIndexFilters()`
- Sudah ada `TerritoryIndexFilters::viewBundle()` di view

**Status:** SUDAH ADA territory filter tapi perlu verifikasi konsistensi dengan pattern baru

**Rekomendasi:**
- Tetap pertahankan logic view aggregation karena spesifik ke kebutuhan bisnis
- Pastikan `HierarchicalHelper::applyTerritoryScopeForTable()` dipanggil tanpa role check
- Verifikasi `TerritoryIndexFilters::applyUsmFilters()` sudah ada di semua query path

## 📊 Statistik Perbaikan

### Controllers Diperbaiki: 5
1. ItSupportController ✅
2. FaskesController ✅
3. TerminalController ✅
4. TempatIbadahController ✅
5. ObjekWisataController ✅

### Controllers Sudah Benar: 2
1. JalanGangguanController ✅
2. KunjunganWisataController ✅

### Controllers Kompleks (Perlu Verifikasi): 2
1. DataPerangkatController ⚠️
2. DataCctvController ⚠️

### Total Controllers Reviewed: 9

## 🎉 Dampak Perubahan

### Fitur Baru yang Tersedia:
1. **Filter by Daerah** - User bisa filter data berdasarkan daerah
2. **Filter by Wilayah** - User bisa filter data berdasarkan wilayah
3. **Konsistensi UI** - Semua modul sekarang punya filter yang sama
4. **Export yang Benar** - Export Excel/PDF juga mengikuti territory filter

### Bug yang Diperbaiki:
1. **Role Check Manual** - Tidak konsisten dan error-prone
2. **Missing Territory Filter** - Beberapa controller tidak apply territory scope di export
3. **Missing Filter UI** - User tidak bisa filter by daerah/wilayah

## 🔍 Cara Verifikasi

### Test Case untuk Setiap Controller:
1. **Login sebagai Superadmin** → Harus lihat SEMUA data
2. **Login sebagai Operator Daerah** → Harus lihat data DAERAH saja
3. **Login sebagai Operator Wilayah** → Harus lihat data WILAYAH saja
4. **Filter by Daerah** → Harus filter data sesuai daerah
5. **Filter by Wilayah** → Harus filter data sesuai wilayah
6. **Export Excel** → Harus export data yang ter-filter
7. **Export PDF** → Harus export data yang ter-filter

### Command untuk Test:
```bash
# Run aplikasi
php artisan serve

# Test dengan different user roles:
# - Superadmin: lihat semua
# - Daerah Operator: lihat daerah saja
# - Wilayah Operator: lihat wilayah saja
```

## 📝 Notes

- Semua perubahan BACKWARD COMPATIBLE
- Tidak ada breaking changes ke database
- Tidak perlu migration
- View template tetap sama (hanya tambah filter controls)
- Permission system tetap sama

## 🚀 Next Steps

1. ✅ Perbaiki controllers sederhana (SELESAI)
2. ⚠️ Verifikasi dan perbaiki DataPerangkatController (PENDING)
3. ⚠️ Verifikasi dan perbaiki DataCctvController (PENDING)
4. ✅ Buat dokumentasi (SELESAI - file ini)
5. ⏳ Testing menyeluruh di staging environment
6. ⏳ Deploy ke production

---

**Author:** AI Assistant  
**Date:** 2026-06-22  
**Version:** 1.0
