# 📊 IMPLEMENTASI SISTEM HIERARKI - SMART BACK OFFICE

## 🏗️ STRUKTUR HIERARKI YANG SUDAH DIIMPLEMENTASIKAN

Sistem Smart Back Office menggunakan struktur hierarki 4 level yang sudah diimplementasikan:

```
NASIONAL (Level Tertinggi) - Read Only, No Filter
    ↓
DAERAH (Level Menengah) - Read Only, Filter by Daerah
    ↓
WILAYAH (Level Bawah) - Read Only, Filter by Wilayah
    ↓
OPERASIONAL (Level Input Data) - Full CRUD
```

---

## 📁 STRUKTUR CONTROLLER

### **1. Base Controllers**

#### **OperasionalController** (`app/Http/Controllers/OperasionalController.php`)
- Base controller untuk level operasional
- Menyediakan CRUD lengkap: Create, Read, Update, Delete
- Support export (PDF, Excel, Word, CSV, JSON)
- Auto filter hierarki (opsional)
- Auto set timestamps dan piket

#### **HierarchicalController** (`app/Http/Controllers/HierarchicalController.php`)
- Base controller untuk level hierarki (Wilayah, Daerah, Nasional)
- Read-only: hanya index() dan show()
- Auto filter berdasarkan level user
- Support export dengan format report khusus

---

## 🎯 CARA MENGGUNAKAN

### **Level Operasional (Input Data)**

#### **Contoh: ArusmudikController**

```php
<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ArusmudikController extends OperasionalController
{
    protected $tableName = 'arusmudik';
    protected $primaryKey = 'id_arusmudik';
    protected $viewFolder = 'arusmudik';
    
    protected $fillable = [
        'id_profileposko',
        'tanggal',
        'piket',
        'statarus',
    ];
    
    protected $searchFields = [
        'tanggal',
        'statarus',
    ];
    
    protected $defaultOrderBy = 'tanggal';
    protected $defaultOrderType = 'desc';

    protected function getValidationRules($action = 'store')
    {
        return [
            'id_profileposko' => 'required|exists:profilposko,id_profilposko',
            'tanggal' => 'required|date',
            'statarus' => 'nullable|string|max:255',
        ];
    }

    protected function getPageTitle()
    {
        return 'Arus Mudik & Balik';
    }
}
```

#### **Fitur yang Tersedia:**
- ✅ `index()` - List data dengan search, filter, pagination
- ✅ `create()` - Form tambah data baru
- ✅ `store()` - Simpan data baru
- ✅ `show($id)` - Detail data
- ✅ `edit($id)` - Form edit data
- ✅ `update($id)` - Update data
- ✅ `destroy($id)` - Hapus data
- ✅ `export($format)` - Export data (pdf/excel/csv/word/json)

---

### **Level Wilayah (Read-Only dengan Filter Wilayah)**

#### **Contoh: WilArusmudikController**

```php
<?php

namespace App\Http\Controllers\Wil;

use App\Http\Controllers\HierarchicalController;
use Illuminate\Http\Request;

class WilArusmudikController extends HierarchicalController
{
    protected $hierarchyLevel = 'wilayah';
    protected $operasionalTable = 'arusmudik';
    protected $aggregationTable = 'wil_arusmudik';
    protected $viewFolder = 'wil.arusmudik';
    protected $primaryKey = 'id_wil_arusmudik';
    
    protected $indexFields = ['tanggal', 'statarus', 'wilayah'];
    protected $searchFields = ['tanggal', 'statarus', 'wilayah'];
    
    protected $defaultOrderBy = 'tanggal';
    protected $defaultOrderType = 'desc';

    protected function applyJoins($query)
    {
        $query->leftJoin('arusmudik', $this->aggregationTable . '.id_arusmudik', '=', 'arusmudik.id_arusmudik')
              ->leftJoin('usm', 'arusmudik.piket', '=', 'usm.id_usm')
              ->select(
                  $this->aggregationTable . '.*',
                  'arusmudik.tanggal',
                  'arusmudik.statarus',
                  'usm.nama as nama_piket'
              );
        
        return $query;
    }

    protected function getPageTitle()
    {
        return 'Wilayah - Arus Mudik & Balik';
    }
}
```

#### **Fitur yang Tersedia:**
- ✅ `index()` - List data dengan filter wilayah user
- ✅ `show($id)` - Detail data
- ✅ `export($format)` - Export report (pdf/excel/csv/word/json)

#### **Auto Filter:**
- Data otomatis di-filter berdasarkan `wilayah` dari user yang login
- User hanya melihat data wilayah mereka sendiri

---

### **Level Daerah (Read-Only dengan Filter Daerah)**

#### **Contoh: DaArusmudikController**

```php
<?php

namespace App\Http\Controllers\Da;

use App\Http\Controllers\HierarchicalController;
use Illuminate\Http\Request;

class DaArusmudikController extends HierarchicalController
{
    protected $hierarchyLevel = 'daerah';
    protected $operasionalTable = 'arusmudik';
    protected $aggregationTable = 'da_arusmudik';
    protected $viewFolder = 'da.arusmudik';
    protected $primaryKey = 'id_da_arusmudik';
    
    protected $indexFields = ['tanggal', 'statarus', 'daerah', 'wilayah'];
    protected $searchFields = ['tanggal', 'statarus', 'daerah', 'wilayah'];
    
    protected $defaultOrderBy = 'tanggal';
    protected $defaultOrderType = 'desc';

    protected function applyJoins($query)
    {
        $query->leftJoin('arusmudik', $this->aggregationTable . '.id_arusmudik', '=', 'arusmudik.id_arusmudik')
              ->select(
                  $this->aggregationTable . '.*',
                  'arusmudik.tanggal',
                  'arusmudik.statarus'
              );
        
        return $query;
    }

    protected function getPageTitle()
    {
        return 'Daerah - Arus Mudik & Balik';
    }
}
```

#### **Fitur yang Tersedia:**
- ✅ `index()` - List data dengan filter daerah user
- ✅ `show($id)` - Detail data
- ✅ `export($format)` - Export report (pdf/excel/csv/word/json)

#### **Auto Filter:**
- Data otomatis di-filter berdasarkan `daerah` dari user yang login
- User melihat data dari semua wilayah di dalam daerah mereka

---

### **Level Nasional (Read-Only tanpa Filter)**

#### **Contoh: NasArusmudikController**

```php
<?php

namespace App\Http\Controllers\Nas;

use App\Http\Controllers\HierarchicalController;
use Illuminate\Http\Request;

class NasArusmudikController extends HierarchicalController
{
    protected $hierarchyLevel = 'nasional';
    protected $operasionalTable = 'arusmudik';
    protected $aggregationTable = 'nas_arusmudik';
    protected $viewFolder = 'nas.arusmudik';
    protected $primaryKey = 'id_nas_arusmudik';
    
    protected $indexFields = ['tanggal', 'statarus', 'daerah', 'wilayah'];
    protected $searchFields = ['tanggal', 'statarus', 'daerah', 'wilayah'];
    
    protected $defaultOrderBy = 'tanggal';
    protected $defaultOrderType = 'desc';

    protected function applyJoins($query)
    {
        $query->leftJoin('arusmudik', $this->aggregationTable . '.id_arusmudik', '=', 'arusmudik.id_arusmudik')
              ->leftJoin('usm', 'arusmudik.piket', '=', 'usm.id_usm')
              ->select(
                  $this->aggregationTable . '.*',
                  'arusmudik.tanggal',
                  'arusmudik.statarus',
                  'usm.nama as nama_piket'
              );
        
        return $query;
    }

    protected function getPageTitle()
    {
        return 'Nasional - Arus Mudik & Balik';
    }
}
```

#### **Fitur yang Tersedia:**
- ✅ `index()` - List semua data tanpa filter
- ✅ `show($id)` - Detail data
- ✅ `export($format)` - Export report (pdf/excel/csv/word/json)

#### **Auto Filter:**
- Tidak ada filter - menampilkan semua data nasional

---

## 📋 CONTOH IMPLEMENTASI MODUL BARU

### **Step 1: Buat Controller Level Operasional**

```php
// app/Http/Controllers/TurjagwaliController.php
class TurjagwaliController extends OperasionalController
{
    protected $tableName = 'turjagwali';
    protected $primaryKey = 'id_turjagwali';
    protected $viewFolder = 'turjagwali';
    
    protected $fillable = ['tanggal', 'piket', 'keterangan'];
    protected $searchFields = ['tanggal', 'keterangan'];
    
    protected function getValidationRules($action = 'store')
    {
        return [
            'tanggal' => 'required|date',
            'keterangan' => 'nullable|string',
        ];
    }
}
```

### **Step 2: Buat Controller Level Wilayah**

```php
// app/Http/Controllers/Wil/WilTurjagwaliController.php
class WilTurjagwaliController extends HierarchicalController
{
    protected $hierarchyLevel = 'wilayah';
    protected $operasionalTable = 'turjagwali';
    protected $aggregationTable = 'wil_turjagwali';
    protected $viewFolder = 'wil.turjagwali';
    
    protected function applyJoins($query)
    {
        $query->leftJoin('turjagwali', $this->aggregationTable . '.id_turjagwali', '=', 'turjagwali.id_turjagwali');
        return $query;
    }
}
```

### **Step 3: Buat Controller Level Daerah**

```php
// app/Http/Controllers/Da/DaTurjagwaliController.php
class DaTurjagwaliController extends HierarchicalController
{
    protected $hierarchyLevel = 'daerah';
    protected $operasionalTable = 'turjagwali';
    protected $aggregationTable = 'da_turjagwali';
    protected $viewFolder = 'da.turjagwali';
    
    protected function applyJoins($query)
    {
        $query->leftJoin('turjagwali', $this->aggregationTable . '.id_turjagwali', '=', 'turjagwali.id_turjagwali');
        return $query;
    }
}
```

### **Step 4: Buat Controller Level Nasional**

```php
// app/Http/Controllers/Nas/NasTurjagwaliController.php
class NasTurjagwaliController extends HierarchicalController
{
    protected $hierarchyLevel = 'nasional';
    protected $operasionalTable = 'turjagwali';
    protected $aggregationTable = 'nas_turjagwali';
    protected $viewFolder = 'nas.turjagwali';
    
    protected function applyJoins($query)
    {
        $query->leftJoin('turjagwali', $this->aggregationTable . '.id_turjagwali', '=', 'turjagwali.id_turjagwali');
        return $query;
    }
}
```

### **Step 5: Tambahkan Route**

```php
// routes/web.php

// Level Operasional
Route::resource('turjagwali', TurjagwaliController::class);

// Level Wilayah
Route::prefix('wil')->name('wil.')->group(function () {
    Route::get('turjagwali', [WilTurjagwaliController::class, 'index'])->name('turjagwali.index');
    Route::get('turjagwali/{id}', [WilTurjagwaliController::class, 'show'])->name('turjagwali.show');
    Route::get('turjagwali/export/{format}', [WilTurjagwaliController::class, 'export'])->name('turjagwali.export');
});

// Level Daerah
Route::prefix('da')->name('da.')->group(function () {
    Route::get('turjagwali', [DaTurjagwaliController::class, 'index'])->name('turjagwali.index');
    Route::get('turjagwali/{id}', [DaTurjagwaliController::class, 'show'])->name('turjagwali.show');
    Route::get('turjagwali/export/{format}', [DaTurjagwaliController::class, 'export'])->name('turjagwali.export');
});

// Level Nasional
Route::prefix('nas')->name('nas.')->group(function () {
    Route::get('turjagwali', [NasTurjagwaliController::class, 'index'])->name('turjagwali.index');
    Route::get('turjagwali/{id}', [NasTurjagwaliController::class, 'show'])->name('turjagwali.show');
    Route::get('turjagwali/export/{format}', [NasTurjagwaliController::class, 'export'])->name('turjagwali.export');
});
```

---

## 🔄 ALUR DATA FLOW

### **Input Data (Bottom-Up)**
```
1. OPERASIONAL (Input)
   ↓ Data masuk ke tabel operasional (arusmudik, turjagwali, dll)
   ↓
2. WILAYAH (Aggregation)
   ↓ Data di-aggregate ke wil_arusmudik dengan filter wilayah
   ↓
3. DAERAH (Aggregation)
   ↓ Data di-aggregate ke da_arusmudik dengan filter daerah
   ↓
4. NASIONAL (Aggregation)
   ↓ Data di-aggregate ke nas_arusmudik tanpa filter
```

### **View Data (Top-Down)**
```
1. NASIONAL
   ↓ View semua data nasional
   ↓
2. DAERAH
   ↓ View data per daerah (filter by daerah)
   ↓
3. WILAYAH
   ↓ View data per wilayah (filter by wilayah)
   ↓
4. OPERASIONAL
   ↓ View & Edit data detail operasional
```

---

## 📊 SUMMARY

| Level | Prefix | Filter | CRUD | Use Case |
|-------|--------|--------|------|----------|
| **Operasional** | - | Tidak ada | Full CRUD | Input data di lapangan |
| **Wilayah** | `Wil_` | `wilayah = user.wilayah` | Read Only | Laporan per wilayah |
| **Daerah** | `Da_` | `daerah = user.daerah` | Read Only | Laporan per daerah |
| **Nasional** | `Nas_` | Tidak ada | Read Only | Laporan nasional |

---

## ✅ STATUS IMPLEMENTASI

- ✅ Base Controller: OperasionalController
- ✅ Base Controller: HierarchicalController
- ✅ Contoh: ArusmudikController (Operasional)
- ✅ Contoh: WilArusmudikController (Wilayah)
- ✅ Contoh: DaArusmudikController (Daerah)
- ✅ Contoh: NasArusmudikController (Nasional)
- ✅ Export System: PDF, Excel, Word, CSV, JSON
- ✅ Auto Filter Hierarki
- ✅ Search & Pagination
- ⏳ Menu Config (perlu update)
- ⏳ Route (perlu update untuk semua modul)

---

**Dokumen ini menjelaskan cara menggunakan struktur hierarki yang sudah diimplementasikan.**
