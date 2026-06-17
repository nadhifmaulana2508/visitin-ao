# Visitin AO

**Sistem Kunjungan & Pengelolaan Nasabah untuk Account Officer (AO)**

Aplikasi berbasis web mobile-first yang digunakan oleh Account Officer (AO) bank untuk mengelola kunjungan nasabah, mapping debitur, pencatatan aktivitas penagihan, dan monitoring pipeline kredit.

> **Live URL**: `visitin-ao.bkkjateng.co.id`  
> **SSO Domain**: `apisso.bkkjateng.co.id`

---

## Daftar Isi

- [Arsitektur](#arsitektur)
- [Tech Stack](#tech-stack)
- [Struktur Direktori](#struktur-direktori)
- [Fitur yang Sudah Dibangun](#fitur-yang-sudah-dibangun)
- [Fitur dalam Pengembangan](#fitur-dalam-pengembangan)
- [Instalasi & Setup](#instalasi--setup)
- [Konfigurasi Environment](#konfigurasi-environment)
- [API Endpoints](#api-endpoints)
- [Routing](#routing)
- [Autentikasi (SSO)](#autentikasi-sso)
- [Role Pengguna](#role-pengguna)
- [Halaman Aplikasi](#halaman-aplikasi)
- [Konvensi Pengembangan](#konvensi-pengembangan)
- [Roadmap](#roadmap)

---

## Arsitektur

Aplikasi menggunakan arsitektur **MVC sederhana (PHP Native)** dengan pemisahan antara frontend (pages) dan backend (REST API):

```
┌──────────────────────────────────────────────────┐
│                    Browser                         │
│              (Mobile-first PWA-like)              │
└───────────────────────┬──────────────────────────┘
                        │
            ┌───────────┴───────────┐
            │                       │
     ┌──────▼──────┐       ┌───────▼───────┐
     │  index.php  │       │  api/index.php │
     │  (Router    │       │  (API Router)  │
     │   Halaman)  │       │               │
     └──────┬──────┘       └───────┬───────┘
            │                       │
   ┌────────┴────────┐    ┌────────┴────────┐
   │  pages/*.php    │    │  controllers/   │
   │  views/*.php    │    │  middlewares/   │
   │                 │    │  helpers/       │
   └─────────────────┘    │  config/        │
                          └────────┬────────┘
                                   │
                          ┌────────▼────────┐
                          │   MySQL (PDO)   │
                          │  + API SIMPEG   │
                          └─────────────────┘
```

**Prinsip Utama:**
- **Front Controller Pattern** — Semua request masuk ke `index.php` (halaman) atau `api/index.php` (API).
- **Clean URL** — Menggunakan `.htaccess` RewriteRule untuk URL tanpa ekstensi `.php`.
- **SSO Cookie Bridge** — Token dari SIMPEG disimpan sebagai cookie `sso_token` dan di-bridge ke PHP session.
- **Mobile-First UI** — Layout max-width 480px dengan bottom navigation ala native app.

---

## Tech Stack

| Layer | Teknologi |
|-------|-----------|
| **Backend** | PHP 8.x (Native, tanpa framework) |
| **Database** | MySQL via PDO |
| **Frontend** | HTML5, CSS3 (Custom + Bootstrap 5.3.2) |
| **JavaScript** | Vanilla JS + jQuery (select2) |
| **Icons** | FontAwesome 6.4.2 |
| **Maps** | Leaflet.js 1.9.4 + OpenStreetMap Nominatim |
| **Auth** | JWT via SSO SIMPEG (cookie-based) |
| **Server** | Apache (mod_rewrite) / Nginx |

---

## Struktur Direktori

```
visitin-ao/
├── index.php                  # Front controller utama (router halaman)
├── .htaccess                  # Rewrite rules untuk clean URL
├── .gitignore
├── README.md                  # Dokumentasi ini
├── issue.md                   # Issue & roadmap pengembangan fitur baru
│
├── api/                       # Backend REST API
│   ├── index.php              # API router (switch-case ?action=xxx)
│   ├── .env.example           # Template konfigurasi environment
│   ├── config/
│   │   ├── database.php       # Koneksi database (PDO)
│   │   └── env.php            # Loader .env tanpa Composer
│   ├── controllers/
│   │   └── AuthController.php # Login, Whoami, Logout (proxy SIMPEG)
│   ├── middlewares/
│   │   └── AuthMiddleware.php # Ekstrak token dari header/cookie
│   └── helpers/
│       ├── response.php       # sendResponse(), readJsonBody()
│       ├── http.php           # httpRequest() cURL wrapper
│       └── cookie.php         # setAuthCookie(), clearAuthCookie()
│
├── pages/                     # Halaman-halaman frontend
│   ├── login.php              # Halaman login (SSO fetch)
│   ├── reset.php              # Aktivasi / Reset password
│   ├── home.php               # Dashboard utama (role-based menu)
│   ├── mapping.php            # Mapping debitur awal bulan
│   ├── nominatif.php          # Data nominatif kredit
│   ├── history.php            # Riwayat aktivitas kunjungan
│   ├── profile.php            # Profil & pengaturan akun
│   ├── janji-bayar.php        # Daftar janji bayar (PTP)
│   ├── hapus-buku.php         # Data debitur hapus buku (PH)
│   ├── kunjungan-create.php   # Form input kunjungan (kosong/WIP)
│   ├── kunjungan-create-kal.php # Form kunjungan + kalkulator simulasi
│   ├── kunjungan-detail.php   # Detail bukti kunjungan
│   ├── kunjungan-history-debitur.php # Timeline riwayat per debitur
│   └── kalkulator-simulasi.php # Widget simulasi penurunan DPD
│
├── views/                     # Komponen UI reusable
│   ├── header.php             # DOCTYPE, CSS global, mobile wrapper
│   ├── navbar.php             # Bottom navigation bar
│   └── script.php             # Penutup wrapper + Bootstrap JS
│
└── docs/                      # Dokumentasi teknis
    └── issues/
        └── 001-auth-login.md  # Dokumentasi implementasi auth
```

---

## Fitur yang Sudah Dibangun

### 1. Autentikasi SSO (Selesai)
- Login via proxy ke API SIMPEG (`/auth/login`)
- Validasi token via `/auth/whoami`
- Cookie SSO (`sso_token`) untuk single sign-on lintas aplikasi internal
- Session bridge (cookie -> PHP session)
- Logout (clear cookie)

### 2. Frontend Pages (UI Selesai, Belum Terintegrasi API)
- **Home** — Dashboard role-based (remedial, kredit, dana)
- **Mapping** — Daftar debitur mapping awal bulan + ringkasan performa
- **Nominatif** — Data nominatif kredit dengan filter multi-level
- **History** — Riwayat kunjungan + raport kinerja AO
- **Profile** — ID Card pegawai, ganti password, sinkronisasi SIMPEG
- **Janji Bayar** — Follow-up nasabah PTP (belum/sudah bayar)
- **Hapus Buku** — Data debitur PH (Penghapusan Buku)
- **Kunjungan Create** — Form input kunjungan (GPS, kamera, smart form)
- **Kunjungan Detail** — Bukti kunjungan dengan watermark foto
- **Kalkulator Simulasi** — Simulasi penurunan DPD (Days Past Due)
- **Login & Reset** — Form login + form aktivasi/reset password

### 3. API Endpoints (Partial)
- `POST /api/?action=login` — Autentikasi via SIMPEG
- `GET /api/?action=whoami` — Validasi token + ambil profil
- `POST /api/?action=logout` — Hapus cookie
- `GET /api/?action=get_mapping` — Dummy response (placeholder)
- `POST /api/?action=create_kunjungan` — Dummy response (placeholder)

---

## Fitur dalam Pengembangan

Lihat file [`issue.md`](./issue.md) untuk detail lengkap:

1. **Modul Prospek** — Input, delegasi, dan follow-up prospek bisnis (kredit, tabungan, deposito, pembeli aset, debitur existing)
2. **Delegasi AO** — Superuser mendelegasikan prospek ke AO yang tepat berdasarkan jenis prospek
3. **SLA Kredit** — Pencatatan pipeline kredit dengan tracking waktu proses
4. **Mapping Debitur** — Pembagian debitur ke AO berdasarkan hari menunggak setiap awal bulan
5. **Monitoring & Laporan** — Dashboard superuser untuk monitoring progres

---

## Instalasi & Setup

### Prasyarat
- PHP 8.0+ dengan ekstensi: `pdo_mysql`, `curl`, `json`, `mbstring`
- MySQL 5.7+ / MariaDB 10.3+
- Apache (mod_rewrite) atau Nginx
- Akses ke API SIMPEG (untuk autentikasi)

### Langkah Instalasi

```bash
# 1. Clone repository
git clone https://github.com/nadhifmaulana2508/visitin-ao.git

# 2. Masuk ke direktori project
cd visitin-ao

# 3. Salin konfigurasi environment
cp api/.env.example api/.env

# 4. Edit file .env sesuai konfigurasi lokal
nano api/.env

# 5. Buat database MySQL
mysql -u root -p -e "CREATE DATABASE db_kunjungan;"

# 6. (Opsional) Jalankan dengan PHP built-in server untuk development
php -S localhost:8080

# 7. Atau letakkan di folder htdocs/www Apache
# Pastikan folder project bernama 'kunjungan-ao' di localhost
# Akses: http://localhost/kunjungan-ao
```

### Setup Apache Virtual Host (Production)

```apache
<VirtualHost *:80>
    ServerName visitin-ao.bkkjateng.co.id
    DocumentRoot /var/www/visitin-ao
    
    <Directory /var/www/visitin-ao>
        AllowOverride All
        Require all granted
    </Directory>
</VirtualHost>
```

---

## Konfigurasi Environment

File: `api/.env` (dibuat dari `api/.env.example`)

```env
# API SSO SIMPEG
SIMPEG_BASE_URL=https://apisso.bkkjateng.co.id
APP_NAME=visitin-ao

# Cookie SSO (lintas aplikasi internal)
COOKIE_NAME=sso_token
COOKIE_DOMAIN=.bkkjateng.co.id    # Kosongkan untuk localhost
COOKIE_SECURE=true                  # false untuk localhost
COOKIE_SAMESITE=Lax
COOKIE_PATH=/
```

**Catatan untuk development lokal:**
- Set `COOKIE_DOMAIN=` (kosong)
- Set `COOKIE_SECURE=false`

---

## API Endpoints

Base URL: `/api/?action={action_name}`

| Method | Action | Auth | Deskripsi |
|--------|--------|------|-----------|
| POST | `login` | Public | Login via SIMPEG SSO |
| GET | `whoami` | Bearer/Cookie | Ambil profil user |
| POST | `logout` | - | Clear cookie SSO |
| GET | `get_mapping` | Bearer/Cookie | Daftar mapping debitur |
| POST | `create_kunjungan` | Bearer/Cookie | Simpan data kunjungan |

### Format Response Standar

```json
{
  "status": 200,
  "message": "Pesan deskriptif",
  "data": { ... }
}
```

---

## Routing

### Halaman (Frontend)
Router di `index.php` menggunakan clean URL:

```
https://visitin-ao.bkkjateng.co.id/{page}
https://visitin-ao.bkkjateng.co.id/{page}/{param}
```

Contoh:
- `/home` → `pages/home.php`
- `/mapping` → `pages/mapping.php`
- `/kunjungan-detail/123` → `pages/kunjungan-detail.php` (param=123)

### API (Backend)
Router di `api/index.php` menggunakan query parameter:

```
/api/?action=login         → AuthController::login()
/api/?action=whoami        → AuthController::whoami()
/api/?action=get_mapping   → (placeholder)
```

### Halaman Publik (Tanpa Login)
- `/login`
- `/reset`

Semua halaman lain memerlukan cookie `sso_token` valid.

---

## Autentikasi (SSO)

Aplikasi menggunakan **Single Sign-On** via API SIMPEG:

1. User submit `id_peg` + `password` di form login
2. Frontend fetch ke `/api/?action=login`
3. Backend proxy ke SIMPEG `/auth/login`
4. Jika valid, SIMPEG return JWT token
5. Backend set cookie `sso_token` (HttpOnly, domain `.bkkjateng.co.id`)
6. Cookie dipakai bersama oleh aplikasi internal lain (monbis, report-dpk, dll)

**Prioritas sumber token:**
1. Header `Authorization: Bearer <token>` (utama)
2. Cookie `sso_token` (fallback)

---

## Role Pengguna

Aplikasi mendukung multiple role dengan menu berbeda:

| Role | Fungsi | Menu Utama |
|------|--------|------------|
| **AO Remedial** | Penagihan debitur bermasalah | Mapping, Nominatif, Janji Bayar, Hapus Buku |
| **AO Kredit** | Prospek & pengelolaan kredit aktif | Prospek, Mapping Existing, Potensi Top-Up |
| **AO Dana** | Pengelolaan tabungan & deposito | Kelola Dana, Jadwal Menabung |
| **Superuser** | Delegasi prospek & mapping debitur | Semua menu + Delegasi + Mapping |
| **Developer** | Akses penuh untuk testing | Seluruh fitur |

---

## Halaman Aplikasi

| Halaman | File | Deskripsi |
|---------|------|-----------|
| Login | `pages/login.php` | Form login SSO dengan validasi async |
| Reset | `pages/reset.php` | Aktivasi akun / reset password |
| Home | `pages/home.php` | Dashboard dengan menu role-based |
| Mapping | `pages/mapping.php` | Daftar debitur + coverage + ringkasan |
| Nominatif | `pages/nominatif.php` | Data kredit seluruh cabang |
| History | `pages/history.php` | Riwayat kunjungan + raport kinerja |
| Profile | `pages/profile.php` | Info pegawai + pengaturan |
| Janji Bayar | `pages/janji-bayar.php` | Follow-up PTP |
| Hapus Buku | `pages/hapus-buku.php` | Data debitur PH |
| Kunjungan Create | `pages/kunjungan-create-kal.php` | Form kunjungan lengkap |
| Kunjungan Detail | `pages/kunjungan-detail.php` | Detail bukti kunjungan |
| History Debitur | `pages/kunjungan-history-debitur.php` | Timeline per nasabah |

---

## Konvensi Pengembangan

### Penamaan File
- Halaman: `pages/{nama-fitur}.php` (kebab-case)
- Controller: `api/controllers/{NamaController}.php` (PascalCase)
- Helper: `api/helpers/{nama}.php` (lowercase)

### CSS
- Menggunakan CSS Variables global di `views/header.php`
- Setiap halaman memiliki `<style>` scoped di awal file
- Warna utama: `--color-primary` (#0A1931), `--color-accent` (#FF7B54)

### JavaScript
- Vanilla JS untuk interaksi halaman
- jQuery hanya untuk Select2 (dependent dropdown)
- Fetch API untuk komunikasi dengan backend

### Base URL Dinamis
```php
define('BASE_APP',
    (isset($_SERVER['HTTPS']) ? 'https' : 'http') . '://' .
    $_SERVER['HTTP_HOST'] .
    (strpos($_SERVER['HTTP_HOST'], 'localhost') !== false ? '/kunjungan-ao' : '')
);
```

---

## Roadmap

- [x] Arsitektur dasar (routing, front controller, API router)
- [x] Autentikasi SSO via SIMPEG
- [x] UI halaman utama (Home, Mapping, History, Profile)
- [x] UI form kunjungan (GPS, kamera, smart form)
- [x] UI nominatif, janji bayar, hapus buku
- [ ] Integrasi API `get_mapping` dengan database
- [ ] Integrasi API `create_kunjungan` (upload foto base64)
- [ ] Modul Prospek (kredit, tabungan, deposito, pembeli aset, debitur existing)
- [ ] Delegasi AO oleh Superuser
- [ ] SLA Kredit (pipeline + tracking waktu)
- [ ] Mapping Debitur awal bulan (validasi kategori)
- [ ] Pipeline debitur per AO
- [ ] Monitoring & Laporan
- [ ] Push notification / reminder janji bayar
- [ ] Responsif cross-device

---

## Lisensi

Internal - BKK Jateng. Tidak untuk distribusi publik.

---

## Kontak

- **Repository**: [github.com/nadhifmaulana2508/visitin-ao](https://github.com/nadhifmaulana2508/visitin-ao)
- **IT Department** - BKK Jawa Tengah
