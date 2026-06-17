# Visitin AO

**Sistem E-Prospek, Kunjungan & Pengelolaan Nasabah untuk Account Officer**

Aplikasi web responsif (mobile + tablet + desktop) untuk mengelola prospek bisnis, kunjungan nasabah, mapping debitur, dan monitoring pipeline kredit.

> **Live URL**: `visitin-ao.bkkjateng.co.id`  
> **Branch aktif**: `create-responsif`

---

## Status Pengembangan

| Modul | Status | Keterangan |
|-------|--------|------------|
| E-Prospek (FE+BE) | Selesai | Full CRUD, filter, report |
| Dummy Auth | Selesai | 7 akun per role |
| Responsive UI | Selesai | Mobile + Tablet + Desktop |
| Database Schema | Selesai | SQL migration ready |
| Kunjungan & Mapping | UI Selesai | Belum integrasi BE |
| SSO SIMPEG | Pending | Pakai dummy dulu |

---

## Quick Start

```bash
# 1. Clone
git clone https://github.com/nadhifmaulana2508/visitin-ao.git
cd visitin-ao

# 2. Setup environment
cp api/.env.example api/.env
# Edit api/.env sesuai kredensial DB lokal

# 3. Buat database
mysql -u root -p < database/migration_dpk.sql
mysql -u root -p < database/dummy_data_dpk.sql

# 4. Jalankan
php -S localhost:8080
# Akses: http://localhost:8080/kunjungan-ao
```

---

## Arsitektur

```
┌─────────────────────────────────────────────────┐
│              Browser (All Devices)                │
│        Mobile / Tablet / Desktop                 │
└────────────────────┬────────────────────────────┘
                     │
         ┌───────────┴───────────┐
         │                       │
  ┌──────▼──────┐       ┌───────▼───────┐
  │  index.php  │       │  api/index.php │
  │  (Page      │       │  (API Router)  │
  │   Router)   │       │               │
  └──────┬──────┘       └───────┬───────┘
         │                       │
  ┌──────┴──────┐       ┌───────┴───────┐
  │  pages/     │       │  routers/     │
  │  views/     │       │  controllers/ │
  └─────────────┘       └───────┬───────┘
                                │
                    ┌───────────┴───────────┐
                    │                       │
             ┌──────▼──────┐       ┌───────▼───────┐
             │   DB: dpk   │       │ DB: simpeg    │
             │  (Prospek,  │       │ (Pegawai,     │
             │  Kunjungan) │       │  Jabatan)     │
             └─────────────┘       └───────────────┘
```

---

## Database

### 2 Database (1 server, port sama)

| Database | Nama | Fungsi |
|----------|------|--------|
| **DPK** | `dpk` | Data prospek, kunjungan, mapping, kode_kantor |
| **SIMPEG** | `masq2971_simpeg_dummy` | Data pegawai aktif (read-only) |

### Tabel di DB `dpk`

| Tabel | Fungsi |
|-------|--------|
| `kode_kantor` | Master cabang 000-028 + korwil |
| `prospects` | Data prospek (kredit/tabungan/deposito/aset/existing) |
| `prospect_follow_ups` | Catatan follow up per prospek |
| `prospect_histories` | Audit trail perubahan status |
| `prospect_sla_logs` | Tracking durasi tiap tahap SLA kredit |

### Korwil Grouping

| Korwil | Range | Cabang |
|--------|-------|--------|
| Pusat | 000 | Kantor Pusat |
| Semarang | 001-007 | Utama, Rembang, Pati, Demak, Kendal, Salatiga, Kab.Semarang |
| Solo | 008-014 | Wonogiri, Surakarta, Karanganyar, Sukoharjo, Sragen, Boyolali, Magelang |
| Banyumas | 015-021 | Wonosobo, Purworejo, Kebumen, Banjarnegara, Purbalingga, Banyumas, Cilacap |
| Pekalongan | 022-028 | Kab.Tegal, Brebes, Kota Tegal, Pemalang, Kota Pekalongan, Kab.Pekalongan, Batang |

### Query Pegawai Aktif (dari SIMPEG)

```sql
SELECT k.kode_cabang AS kode_kantor, j.id_peg AS employee_id,
       p.nama AS full_name, p.nip AS nik, p.email, p.telp,
       k.nama_kantor AS branch_name, mj.nama_unit_kerja AS unit_kerja,
       mj.nama_jabatan AS job_position, mj.level, mj.group_jabatan
FROM tb_jabatan j
INNER JOIN tb_pegawai p ON j.id_peg = p.id_peg
INNER JOIN tb_master_jabatan mj ON CAST(j.kode_jabatan AS CHAR) = CAST(mj.kode_jabatan AS CHAR)
LEFT JOIN tb_kantor k ON j.unit_kerja = k.kode_kantor_detail
WHERE j.status_jab = 'Aktif';
```

---

## Environment (.env)

```env
# Database DPK (prospek & kunjungan)
DB_HOST=localhost
DB_USER=root
DB_PASS=
DB_NAME=dpk
DB_PORT=3306

# Database SIMPEG (pegawai)
SIMPEG_DB_HOST=localhost
SIMPEG_DB_USER=root
SIMPEG_DB_PASS=
SIMPEG_DB_NAME=masq2971_simpeg_dummy
SIMPEG_DB_PORT=3306

# Cookie
COOKIE_NAME=sso_token
COOKIE_DOMAIN=
COOKIE_SECURE=false
COOKIE_SAMESITE=Lax
COOKIE_PATH=/
```

---

## API Endpoints

### Auth
| Method | Action | Deskripsi |
|--------|--------|-----------|
| POST | `login` | Dummy login (7 akun) |
| GET | `whoami` | Profil user dari token |
| POST | `logout` | Clear cookie |

### Prospek (CRUD)
| Method | Action | Deskripsi |
|--------|--------|-----------|
| POST | `prospect_create` | Input prospek baru |
| GET | `prospect_list` | List + filter + pagination |
| GET | `prospect_detail` | Detail + follow_ups + histories + sla_logs |
| POST | `prospect_delegate` | Delegasi ke AO (superuser) |
| POST | `prospect_follow_up` | Input follow up |
| POST | `prospect_change_status` | Ubah ke FOLLOW_UP / SLA |
| POST | `prospect_close` | Closing (wajib rekening+nominal) |
| POST | `prospect_reject` | Reject (wajib alasan) |

### SLA Pipeline
| Method | Action | Deskripsi |
|--------|--------|-----------|
| POST | `prospect_sla_log` | Tambah tahap SLA |
| GET | `prospect_sla_pipeline` | List prospek status SLA |

### Report & Master
| Method | Action | Deskripsi |
|--------|--------|-----------|
| GET | `prospect_report` | Summary + closing period + harian + per type |
| GET | `master_kode_kantor` | List cabang + korwil |
| GET | `master_pegawai_ao` | List AO (untuk delegasi) |
| POST | `upload_foto` | Upload foto base64 |

### Filter Parameters (prospect_list)
| Param | Contoh | Keterangan |
|-------|--------|------------|
| `source` | ao / non_ao / all | Filter sumber input |
| `prospect_type` | KREDIT / TABUNGAN / ... | Jenis prospek |
| `status` | OPEN / SLA / CLOSING / ... | Status prospek |
| `korwil` | semarang / solo / ... | Filter korwil |
| `kode_kantor` | 001 / 002 / ... | Filter cabang spesifik |
| `date_from` | 2026-06-01 | Range tanggal input |
| `date_to` | 2026-06-30 | Range tanggal input |
| `closing_from` | 2026-05-01 | Range tanggal closing |
| `closing_to` | 2026-05-31 | Range tanggal closing |
| `search` | nama nasabah | Pencarian nama/HP |
| `page` | 1 | Halaman |
| `limit` | 25 | Per halaman |

---

## Akun Demo (Dummy Login)

| ID Pegawai | Password | Role | Akses |
|-----------|----------|------|-------|
| `102-119` | 123456 | Developer | Full access semua fitur |
| `201-001` | 123456 | AO Kredit | Pipeline kredit, prospek kredit |
| `201-002` | 123456 | AO Dana | Pipeline tabungan/deposito |
| `201-003` | 123456 | AO Remedial | Mapping, penagihan (FE+BE) |
| `201-004` | 123456 | Superuser | Delegasi, monitoring, report |
| `201-005` | 123456 | Staff (Teller) | Input prospek only |
| `201-006` | 123456 | Staff (CS) | Input prospek only |

---

## Akses Berdasarkan Role

| Role | Prospek | Report | Cabang |
|------|---------|--------|--------|
| Developer | Semua | Semua | Semua |
| Pusat (Div. Pemasaran, Operasional, Direksi, Komisaris) | Lihat semua | Semua | Konsolidasi 000-028 |
| Pusat (Div. Remedial) | Lihat semua | Hanya remedial | Konsolidasi |
| Superuser Cabang (PE/PS) | Cabangnya saja | Cabangnya | Cabangnya saja |
| AO Kredit | Yang di-assign + input sendiri | Pipeline-nya | Cabangnya |
| AO Dana | Yang di-assign + input sendiri | Pipeline-nya | Cabangnya |
| AO Remedial | Yang di-assign + input sendiri | Pipeline-nya | Cabangnya |
| Staff (Non-AO) | Yang dia input saja | Tidak ada | Cabangnya |

---

## Responsive Breakpoints

| Device | Width | Layout |
|--------|-------|--------|
| Mobile | < 768px | Full-width, 2 col grid, bottom nav sticky |
| Tablet | 768-1023px | 768px centered, 3-4 col grid |
| Desktop | 1024px+ | 1200px centered + shadow, 4-5 col grid |

---

## Struktur Direktori

```
visitin-ao/
├── index.php                    # Page router (clean URL)
├── .htaccess                    # Rewrite rules
├── api/
│   ├── .env.example             # Template config 2 database
│   ├── index.php                # API front controller
│   ├── config/
│   │   ├── database.php         # Dual DB connection (dpk + simpeg)
│   │   └── env.php              # .env loader
│   ├── controllers/
│   │   ├── AuthController.php   # Dummy login (7 akun)
│   │   └── ProspectController.php # Full CRUD + filter + report
│   ├── routers/
│   │   └── prospect.php         # Prospect action routing
│   ├── middlewares/
│   │   └── AuthMiddleware.php   # Token extractor
│   └── helpers/
│       ├── response.php         # JSON response helper
│       ├── http.php             # cURL wrapper
│       └── cookie.php           # Cookie management
├── database/
│   ├── migration_dpk.sql        # CREATE TABLE + kode_kantor data
│   ├── dummy_data_dpk.sql       # Sample prospek + histories
│   └── query_simpeg_pegawai_aktif.sql  # Reference query SIMPEG
├── pages/
│   ├── login.php                # Login + demo accounts
│   ├── home.php                 # Dashboard role-based
│   ├── input-prospek.php        # Form + foto + geotagging + cabang
│   ├── daftar-prospek.php       # List + filter + report view
│   ├── prospek-detail.php       # Detail + SLA pipeline + actions
│   ├── mapping.php              # Mapping debitur (UI)
│   ├── nominatif.php            # Data nominatif (UI)
│   ├── history.php              # Riwayat kunjungan (UI)
│   ├── profile.php              # Profil pegawai (UI)
│   └── ...
├── views/
│   ├── header.php               # Responsive CSS + layout
│   ├── navbar.php               # Bottom nav role-based
│   └── script.php               # Global JS utilities
└── uploads/                     # Foto prospek (gitignored)
```

---

## Alur E-Prospek

```
Input Prospek ──→ [AO?] ──Yes──→ Auto-delegasi ──→ Pipeline AO
      │                                                    │
      └──No──→ Menunggu Delegasi ──→ Superuser Delegasi ───┘
                                                           │
                                              ┌────────────┘
                                              ▼
                                    Follow Up (1..n kali)
                                              │
                              ┌────────────────┼────────────────┐
                              ▼                ▼                ▼
                         [Kredit?]         [Non-Kredit]      Reject
                              │                │
                              ▼                ▼
                            SLA            Closing
                         (Pipeline)           │
                              │               Done
                    ┌─────────┼─────────┐
                    ▼         ▼         ▼
               Verifikasi  Analisa   Komite
                    │         │         │
                    ▼         ▼         ▼
                Pencairan ──→ Closing (wajib rekening+nominal)
```

### SLA Pipeline (Kredit)
Saat prospek kredit masuk status SLA, otomatis menjadi pipeline AO Kredit dengan tracking:
- **Tahap**: Verifikasi → Survei → Analisa → Komite → Persetujuan → Akad → Pencairan
- **Durasi per tahap**: dihitung otomatis (hari)
- **Total durasi SLA**: dari masuk SLA sampai Closing/Reject

---

## Tech Stack

| Layer | Teknologi |
|-------|-----------|
| Backend | PHP 8.x Native (tanpa framework) |
| Database | MySQL via PDO (dual connection) |
| Frontend | Bootstrap 5.3.2 + Custom CSS |
| Maps | Leaflet.js 1.9.4 + OSM |
| Icons | FontAwesome 6.4.2 |
| Camera | WebRTC (getUserMedia) |
| Wilayah | emsifa API (provinsi→kab→kec→desa) |

---

## Lisensi

Internal - BKK Jawa Tengah. Tidak untuk distribusi publik.
