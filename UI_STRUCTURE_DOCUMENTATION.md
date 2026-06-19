# Dokumentasi Struktur Tampilan (UI) - Smart Back Office

## 📋 Daftar Isi
1. [Struktur Layout](#struktur-layout)
2. [Komponen UI](#komponen-ui)
3. [Pola Desain](#pola-desain)
4. [Hierarki Tampilan](#hierarki-tampilan)
5. [Fitur UI](#fitur-ui)
6. [Teknologi yang Digunakan](#teknologi-yang-digunakan)

---

## 🏗️ Struktur Layout

### 1. Layout Utama (`layouts/app.blade.php`)

**Struktur:**
```
┌─────────────────────────────────────────┐
│  Sidebar (Fixed Left)                   │
│  - Header: "Smart Back Office"          │
│  - Menu Navigation (x-menu component)    │
│                                         │
│  Main Content Area (Margin Left: 250px) │
│  ┌───────────────────────────────────┐ │
│  │  Topbar                            │ │
│  │  - Page Title                     │ │
│  │  - User Info + Logout Button      │ │
│  └───────────────────────────────────┘ │
│  ┌───────────────────────────────────┐ │
│  │  Flash Messages                   │ │
│  │  - Success/Error Alerts          │ │
│  └───────────────────────────────────┘ │
│  ┌───────────────────────────────────┐ │
│  │  Content Wrapper                 │ │
│  │  - @yield('content')             │ │
│  └───────────────────────────────────┘ │
└─────────────────────────────────────────┘
```

**Fitur Layout:**
- **Sidebar**: Fixed position, width 250px, background `#2c3e50`
- **Main Content**: Margin-left 250px, padding 20px
- **Topbar**: White background, shadow, flex layout
- **Responsive**: Sidebar hidden di mobile (< 768px)

**Warna:**
- Sidebar: `#2c3e50` (dark blue-gray)
- Active menu: `#3498db` (blue)
- Background: `#f5f5f5` (light gray)
- Content: `#ffffff` (white)

---

## 🧩 Komponen UI

### 1. Menu Component (`components/menu.blade.php`)

**Fitur:**
- Nested menu support (menu dengan submenu)
- Active state detection
- Icon support (Font Awesome)
- Collapsible submenu dengan Bootstrap collapse
- Dynamic URL generation via `MenuHelper`

**Struktur Menu:**
```php
Menu Item
├── Icon (optional)
├── Label
├── Path/URL
└── Submenu (optional)
    ├── Submenu Item 1
    ├── Submenu Item 2
    └── ...
```

**Active State:**
- Menu item aktif: background `#3498db`, text putih
- Submenu aktif: expanded dengan class `show`
- Deteksi aktif menggunakan `MenuHelper::isActive()`

---

### 2. Card Component (Bootstrap Card)

**Pola Standar:**
```html
<div class="card">
    <div class="card-header bg-[color] text-white">
        <h5 class="mb-0"><i class="fas fa-icon"></i> Title</h5>
    </div>
    <div class="card-body">
        <!-- Content -->
    </div>
</div>
```

**Warna Header Card:**
- `bg-primary` - Biru (untuk form create/edit)
- `bg-warning` - Kuning (untuk form edit)
- `bg-info` - Cyan (untuk data listing)
- `bg-success` - Hijau (untuk success states)
- `bg-danger` - Merah (untuk error/critical)
- `bg-secondary` - Abu-abu (untuk secondary info)

---

## 🎨 Pola Desain

### 1. Dashboard (`home.blade.php`)

**Struktur:**
1. **Welcome Card** - Card biru dengan greeting
2. **User Statistics Cards** (4 cards):
   - Total Users (info)
   - Active Users (success)
   - Pending Users (warning)
   - Blocked Users (danger)
3. **Operational Module Statistics** (7 cards):
   - Arus Mudik & Balik (gradient purple)
   - Profil Posko (gradient cyan)
   - Turjagwali (gradient green)
   - Audit Arus (gradient yellow)
   - PnP Angkutan (gradient red)
   - QR Contigency (gradient gray)
   - QR Perlambatan (gradient dark)
4. **Chart Visualizations** (4 charts):
   - Bar Chart - Total Data per Modul
   - Line Chart - Trend Data Bulan Ini
   - Pie Chart - Distribusi Data Modul
   - Doughnut Chart - Data Hari Ini
5. **System Information** - User info + Quick Actions

**Card Statistik Pattern:**
```html
<div class="card text-white mb-4 shadow-sm" 
     style="background: linear-gradient(135deg, #color1 0%, #color2 100%);">
    <div class="card-body">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h6 class="card-title mb-1">Title</h6>
                <h3 class="mb-0">{{ value }}</h3>
                <small class="text-white-50">Additional info</small>
            </div>
            <div>
                <i class="fas fa-icon fa-3x opacity-50"></i>
            </div>
        </div>
    </div>
    <div class="card-footer bg-transparent border-0">
        <a href="..." class="text-white text-decoration-none">
            Lihat Detail <i class="fas fa-arrow-right"></i>
        </a>
    </div>
</div>
```

---

### 2. CRUD Pages Pattern

#### Index Page (`index.blade.php`)

**Struktur:**
```
┌─────────────────────────────────────────┐
│  Action Bar                             │
│  - Tambah Data Button (left)            │
│  - Search Form + Export Dropdown (right) │
└─────────────────────────────────────────┘
┌─────────────────────────────────────────┐
│  Card with Table                         │
│  ┌───────────────────────────────────┐   │
│  │  Card Header (colored)           │   │
│  └───────────────────────────────────┘   │
│  ┌───────────────────────────────────┐   │
│  │  Table Responsive                  │   │
│  │  - Striped rows                    │   │
│  │  - Hover effect                   │   │
│  │  - Action buttons (View/Edit/Del) │   │
│  └───────────────────────────────────┘   │
│  ┌───────────────────────────────────┐   │
│  │  Pagination Info + Links          │   │
│  └───────────────────────────────────┘   │
└─────────────────────────────────────────┘
```

**Action Buttons Pattern:**
```html
<div class="btn-group btn-group-sm">
    <a href="..." class="btn btn-info" title="Detail">
        <i class="fas fa-eye"></i>
    </a>
    <a href="..." class="btn btn-warning" title="Edit">
        <i class="fas fa-edit"></i>
    </a>
    <form action="..." method="POST" class="d-inline" 
          onsubmit="return confirm('...');">
        @csrf
        @method('DELETE')
        <button type="submit" class="btn btn-danger" title="Hapus">
            <i class="fas fa-trash"></i>
        </button>
    </form>
</div>
```

#### Create/Edit Page (`create.blade.php`, `edit.blade.php`)

**Struktur:**
```
┌─────────────────────────────────────────┐
│  Card                                    │
│  ┌───────────────────────────────────┐   │
│  │  Card Header (bg-primary/warning)  │   │
│  └───────────────────────────────────┘   │
│  ┌───────────────────────────────────┐   │
│  │  Form                              │   │
│  │  - Row with columns (col-md-6)    │   │
│  │  - Form groups with labels        │   │
│  │  - Validation errors display       │   │
│  │  - Required field indicator (*)    │   │
│  └───────────────────────────────────┘   │
│  ┌───────────────────────────────────┐   │
│  │  Form Actions (Kembali + Simpan)  │   │
│  └───────────────────────────────────┘   │
└─────────────────────────────────────────┘
```

**Form Field Pattern:**
```html
<div class="mb-3">
    <label for="field" class="form-label">
        Label <span class="text-danger">*</span>
    </label>
    <input type="..." 
           class="form-control @error('field') is-invalid @enderror" 
           id="field" 
           name="field" 
           value="{{ old('field', $data->field ?? '') }}"
           required>
    @error('field')
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror
</div>
```

#### Show/Detail Page (`show.blade.php`)

**Struktur:**
```
┌─────────────────────────────────────────┐
│  Card                                    │
│  ┌───────────────────────────────────┐   │
│  │  Card Header (bg-info)             │   │
│  └───────────────────────────────────┘   │
│  ┌───────────────────────────────────┐   │
│  │  Row (2 columns)                  │   │
│  │  ┌─────────────┬─────────────┐   │   │
│  │  │  Table 1     │  Table 2    │   │   │
│  │  │  (Left)      │  (Right)    │   │   │
│  │  └─────────────┴─────────────┘   │   │
│  └───────────────────────────────────┘   │
│  ┌───────────────────────────────────┐   │
│  │  Action Buttons                    │   │
│  │  (Kembali + Edit + Hapus)         │   │
│  └───────────────────────────────────┘   │
└─────────────────────────────────────────┘
```

**Detail Table Pattern:**
```html
<table class="table table-bordered">
    <tr>
        <th width="40%">Field Label</th>
        <td>
            @if($data->field)
                <span class="badge bg-[color]">{{ $data->field }}</span>
            @else
                <span class="text-muted">-</span>
            @endif
        </td>
    </tr>
</table>
```

---

### 3. Hierarchical Level Pages

#### Wilayah Level (`wil/arusmudik/index.blade.php`)
- **Badge**: `bg-info` - "Level: Wilayah"
- **Card Header**: `bg-info` - "Laporan Posko : Arus Mudik & Balik (Wilayah)"
- **Read-only**: Tidak ada tombol "Tambah Data"
- **Export**: Tersedia (PDF, Excel, CSV, Word, JSON)

#### Daerah Level (`da/arusmudik/index.blade.php`)
- **Badge**: `bg-warning` - "Level: Daerah"
- **Card Header**: `bg-warning` - "Report Ops : Arus Mudik & Balik (Daerah)"
- **Read-only**: Tidak ada tombol "Tambah Data"
- **Export**: Tersedia

#### Nasional Level (`nas/arusmudik/index.blade.php`)
- **Badge**: `bg-danger` - "Level: Nasional"
- **Card Header**: `bg-danger` - "Report Ops : Arus Mudik & Balik (Nasional)"
- **Read-only**: Tidak ada tombol "Tambah Data"
- **Export**: Tersedia

---

### 4. Login Page (`auth/login.blade.php`)

**Desain:**
- Full-page centered layout
- Gradient background: `linear-gradient(135deg, #667eea 0%, #764ba2 100%)`
- White card container dengan shadow
- Rounded corners (10px)
- Form dengan icon di label
- "Ingat saya" checkbox
- Full-width login button dengan gradient

**Struktur:**
```
┌─────────────────────────────┐
│  Login Container (centered) │
│  ┌───────────────────────┐  │
│  │  Header               │  │
│  │  - Title + Icon      │  │
│  │  - Subtitle          │  │
│  └───────────────────────┘  │
│  ┌───────────────────────┐  │
│  │  Error Messages       │  │
│  └───────────────────────┘  │
│  ┌───────────────────────┐  │
│  │  Form                 │  │
│  │  - Username/Email     │  │
│  │  - Password           │  │
│  │  - Remember Me       │  │
│  │  - Login Button       │  │
│  └───────────────────────┘  │
└─────────────────────────────┘
```

---

## 📊 Hierarki Tampilan

### Level 1: Operasional (Full CRUD)
- **Path**: `/arusmudik`, `/profilposko`, dll
- **Fitur**: Create, Read, Update, Delete
- **Action Bar**: Tombol "Tambah Data" + Search + Export
- **Table**: Action buttons (View, Edit, Delete)

### Level 2-4: Hierarchical (Read-Only)
- **Level 2 (Wilayah)**: Path `/wil/...`
- **Level 3 (Daerah)**: Path `/da/...`
- **Level 4 (Nasional)**: Path `/nas/...`
- **Fitur**: Read-only, Search, Export
- **Action Bar**: Badge level + Search + Export (NO "Tambah Data")
- **Table**: NO action buttons (hanya data display)

---

## ✨ Fitur UI

### 1. Search & Filter
- **Input Search**: Form dengan placeholder "Cari..."
- **Search Button**: Icon search, outline secondary
- **Reset Button**: Muncul jika ada search query, icon times, outline danger
- **Location**: Top right di action bar

### 2. Export Functionality
- **Dropdown Button**: Success color, icon download
- **Format Options**:
  - PDF
  - Excel
  - CSV
  - Word
  - JSON

### 3. Pagination
- **Info Display**: "Menampilkan X sampai Y dari Z data"
- **Links**: Bootstrap pagination component
- **Location**: Bottom of table, right-aligned

### 4. Flash Messages
- **Success**: Green alert dengan icon check-circle
- **Error**: Red alert dengan icon exclamation-circle
- **Validation Errors**: List semua error dengan icon
- **Dismissible**: Button close untuk dismiss
- **Location**: Top of content wrapper

### 5. Badge System
- **Status Badges**:
  - `bg-success` - Active/Active status
  - `bg-warning` - Pending/Warning
  - `bg-danger` - Blocked/Error
  - `bg-info` - Info/Status arus
  - `bg-secondary` - Secondary info
- **Level Badges**:
  - `bg-info` - Wilayah
  - `bg-warning` - Daerah
  - `bg-danger` - Nasional

### 6. Date Formatting
- **Format**: `d/m/Y` untuk tanggal
- **Format**: `d/m/Y H:i` untuk datetime
- **Format**: `d/m/Y H:i:s` untuk full datetime
- **Library**: Carbon (via Laravel)

### 7. Chart Visualizations (Chart.js)
- **Bar Chart**: Total data per modul
- **Line Chart**: Trend data bulan ini
- **Pie Chart**: Distribusi data modul
- **Doughnut Chart**: Data hari ini
- **Features**: Responsive, interactive tooltips, custom colors

---

## 🛠️ Teknologi yang Digunakan

### CSS Framework
- **Bootstrap 5.3.0**: Grid system, components, utilities
- **Custom CSS**: Inline styles di layout untuk sidebar, topbar

### Icons
- **Font Awesome 6.4.0**: Icons untuk menu, buttons, cards

### JavaScript Libraries
- **Bootstrap JS Bundle**: Dropdown, collapse, alerts
- **jQuery 3.7.0**: DOM manipulation (jika diperlukan)
- **Chart.js 4.4.0**: Data visualization charts

### Font
- **Font Family**: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif

---

## 🎯 Pola Warna Konsisten

### Primary Colors
- **Primary Blue**: `#3498db` (active menu, primary buttons)
- **Info Cyan**: `#17a2b8` (info cards, info badges)
- **Success Green**: `#28a745` (success states, active users)
- **Warning Yellow**: `#ffc107` (warning states, pending)
- **Danger Red**: `#dc3545` (error states, blocked users)

### Gradient Colors (Dashboard Cards)
- **Arus Mudik**: `#667eea` → `#764ba2` (purple gradient)
- **Profil Posko**: `#17a2b8` → `#138496` (cyan gradient)
- **Turjagwali**: `#28a745` → `#20c997` (green gradient)
- **Audit Arus**: `#ffc107` → `#ff9800` (yellow gradient)
- **PnP Angkutan**: `#dc3545` → `#c82333` (red gradient)
- **QR Contigency**: `#6c757d` → `#5a6268` (gray gradient)
- **QR Perlambatan**: `#343a40` → `#23272b` (dark gradient)

### Background Colors
- **Body**: `#f5f5f5` (light gray)
- **Sidebar**: `#2c3e50` (dark blue-gray)
- **Content**: `#ffffff` (white)
- **Card**: `#ffffff` dengan shadow

---

## 📱 Responsive Design

### Breakpoints
- **Mobile**: < 768px
  - Sidebar hidden (transform translateX(-100%))
  - Main content full width (margin-left: 0)
  - Sidebar dapat ditoggle dengan class `show`

### Responsive Elements
- **Tables**: Wrapped dengan `table-responsive`
- **Cards**: Stack vertically di mobile
- **Charts**: Maintain aspect ratio dengan `maintainAspectRatio: false`

---

## 🔍 Best Practices yang Diterapkan

1. **Consistent Card Pattern**: Semua halaman menggunakan card dengan header berwarna
2. **Icon Usage**: Setiap section memiliki icon yang relevan
3. **Color Coding**: Warna konsisten untuk status, level, dan action
4. **Form Validation**: Error display dengan Bootstrap invalid feedback
5. **Loading States**: Pagination info untuk UX yang lebih baik
6. **Empty States**: Alert info jika tidak ada data
7. **Confirmation Dialogs**: JavaScript confirm untuk delete actions
8. **Accessibility**: Proper labels, titles, dan ARIA attributes (jika diperlukan)

---

## 📝 Catatan Implementasi

### Menu Filtering
- Menu difilter berdasarkan level user (wilayah, daerah, nasional)
- Implementasi di `MenuHelper::filterMenuByHierarchy()`
- Menu hanya muncul jika sesuai dengan level user

### Dynamic Content
- Statistik dashboard difilter berdasarkan level user
- Data di hierarchical pages difilter otomatis
- Export juga terfilter sesuai level user

### Flash Messages
- Success: Setelah create/update/delete berhasil
- Error: Setelah operasi gagal atau validation error
- Auto-dismissible dengan Bootstrap alert component

---

**Dokumentasi ini dibuat untuk membantu memahami struktur dan pola tampilan di seluruh aplikasi Smart Back Office.**
