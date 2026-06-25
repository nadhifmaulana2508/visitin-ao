# API Documentation - Visitin AO (E-Prospek)

## Base URL

| Environment | Base URL |
|-------------|----------|
| **Local** | `http://localhost/kunjungan-ao/api/` |
| **Server** | `https://visitin-ao.bkkjateng.co.id/api/` |

Semua endpoint menggunakan format: `{BASE_URL}?action={action_name}`

---

## Authentication

Login menggunakan dummy user (development mode). Setelah login, cookie `sso_token` akan di-set.

Untuk Postman: setelah login, cookie otomatis tersimpan. Atau copy token dari response dan gunakan header:
```
Authorization: Bearer {token}
```

---

## 1. AUTH

### 1.1 Login

```
POST /api/?action=login
Content-Type: application/json
```

**Body:**
```json
{
  "id_peg": "102-119",
  "password": "123456",
  "app": "visitin_ao"
}
```

**Akun Demo:**

| id_peg | Role | Akses |
|--------|------|-------|
| `102-119` | Developer | Full access |
| `201-001` | AO Kredit | Pipeline kredit |
| `201-002` | AO Dana | Pipeline dana |
| `201-003` | AO Remedial | Mapping & penagihan |
| `201-004` | Superuser | Delegasi & monitoring |
| `201-005` | Staff (Teller) | Input prospek |
| `201-006` | Staff (CS) | Input prospek |

**Response 200:**
```json
{
  "status": 200,
  "message": "OK",
  "data": {
    "token": "eyJhbGc...",
    "kode": "000",
    "employee_id": "102-119",
    "full_name": "SYAIFUN NADHIF MAULANA, S. Kom",
    "email": "syaifunnadhif@gmail.com",
    "telp": "088228659668",
    "branch_name": "Kantor Pusat",
    "unit_kerja": "Divisi Operasional",
    "job_position": "Staf Sistem dan Jaringan TI",
    "level": "Staf",
    "group_jabatan": "Staf",
    "role": "developer",
    "permissions": ["DEV","SUPERUSER_PROSPEK","AO_KREDIT","AO_DANA","AO_REMEDIAL_FE","AO_REMEDIAL_BE"]
  }
}
```

### 1.2 Whoami

```
GET /api/?action=whoami
Authorization: Bearer {token}
```

### 1.3 Logout

```
POST /api/?action=logout
```

---

## 2. PROSPEK - CRUD

### 2.1 Create Prospect

```
POST /api/?action=prospect_create
Content-Type: application/json
Authorization: Bearer {token}
```

**Body:**
```json
{
  "prospect_type": "KREDIT",
  "customer_name": "Bapak Ahmad",
  "phone_number": "081234567890",
  "identity_number": "3374012345670001",
  "jenis_usaha": "Perdagangan",
  "rekomendasi_produk": "Kredit",
  "keterangan_usaha": "Pemilik toko kelontong di pasar",
  "kode_kantor": "001",
  "provinsi": "JAWA TENGAH",
  "kab_kota": "KOTA SEMARANG",
  "kecamatan": "Semarang Tengah",
  "address": "Jl. Pandanaran No. 45",
  "latitude": -6.9830000,
  "longitude": 110.4190000,
  "geo_address": "Jl. Pandanaran, Pendrikan Kidul, Semarang",
  "foto_base64": null,
  "description": "Butuh modal untuk ekspansi toko"
}
```

**prospect_type valid:** `KREDIT` | `TABUNGAN` | `DEPOSITO` | `PEMBELI_ASET` | `DEBITUR_EXISTING`

**jenis_usaha valid:** `Pertanian` | `Perikanan` | `Peternakan` | `Perdagangan` | `Jasa` | `Industri Rumahan` | `Karyawan` | `Wiraswasta` | `Lainnya`

**rekomendasi_produk valid:** `Tabungan` | `Deposito` | `Kredit` | `Aset`

**Response 201:**
```json
{
  "status": 201,
  "message": "Prospek berhasil disimpan",
  "data": {
    "id": 1,
    "delegation_status": "SUDAH_DIDELEGASIKAN"
  }
}
```

> **Note:** Jika user adalah AO yang sesuai jenis prospek, `delegation_status` = `SUDAH_DIDELEGASIKAN` (auto-assign). Jika non-AO, = `BELUM_DIDELEGASIKAN`.

---

### 2.2 List Prospects (with filters)

```
GET /api/?action=prospect_list&{params}
Authorization: Bearer {token}
```

**Query Parameters:**

| Param | Type | Default | Keterangan |
|-------|------|---------|------------|
| `search` | string | - | Cari nama/HP |
| `prospect_type` | string | all | KREDIT/TABUNGAN/DEPOSITO/PEMBELI_ASET/DEBITUR_EXISTING |
| `status` | string | all | OPEN/FOLLOW_UP/SLA/REJECT/CLOSING |
| `source` | string | all | `ao` / `non_ao` / `all` |
| `delegation` | string | all | BELUM_DIDELEGASIKAN / SUDAH_DIDELEGASIKAN |
| `kode_kantor` | string | - | Filter cabang spesifik (001-028) |
| `korwil` | string | - | semarang/solo/banyumas/pekalongan |
| `date_from` | date | - | YYYY-MM-DD (tanggal input dari) |
| `date_to` | date | - | YYYY-MM-DD (tanggal input sampai) |
| `closing_from` | date | - | YYYY-MM-DD (closing dari) |
| `closing_to` | date | - | YYYY-MM-DD (closing sampai) |
| `page` | int | 1 | Halaman |
| `limit` | int | 25 | Per halaman (max 100) |

**Contoh:**
```
GET /api/?action=prospect_list&status=OPEN&source=non_ao&kode_kantor=001&page=1&limit=10
```

**Response 200:**
```json
{
  "status": 200,
  "message": "OK",
  "data": {
    "items": [...],
    "pagination": {
      "page": 1,
      "limit": 25,
      "total": 8,
      "total_pages": 1
    }
  }
}
```

---

### 2.3 Detail Prospect

```
GET /api/?action=prospect_detail&id={id}
Authorization: Bearer {token}
```

**Response:** Includes `follow_ups[]`, `histories[]`, `sla_logs[]`, `sla_duration_days`

---

## 3. PROSPEK - ACTIONS

### 3.1 Delegate Prospect (Superuser only)

```
POST /api/?action=prospect_delegate
Content-Type: application/json
Authorization: Bearer {token}
```

**Body:**
```json
{
  "prospect_id": 2,
  "assigned_to": "201-001"
}
```

**Validasi:** Hanya prospek `BELUM_DIDELEGASIKAN` yang bisa didelegasikan.

---

### 3.2 Follow Up

```
POST /api/?action=prospect_follow_up
Content-Type: application/json
Authorization: Bearer {token}
```

**Body:**
```json
{
  "prospect_id": 1,
  "follow_up_date": "2026-06-20",
  "method": "TELEPON",
  "result": "Nasabah tertarik, minta brosur produk",
  "next_plan": "Kunjungan minggu depan"
}
```

**method valid:** `TELEPON` | `WHATSAPP` | `KUNJUNGAN` | `BERTEMU_DI_KANTOR` | `LAINNYA`

> **Auto:** Jika status masih OPEN, otomatis berubah ke FOLLOW_UP setelah follow up pertama.

---

### 3.3 Change Status (to SLA)

```
POST /api/?action=prospect_change_status
Content-Type: application/json
Authorization: Bearer {token}
```

**Body:**
```json
{
  "prospect_id": 1,
  "new_status": "SLA"
}
```

**new_status valid:** `FOLLOW_UP` | `SLA`

> **SLA:** Hanya untuk `KREDIT` dan `DEBITUR_EXISTING`. Otomatis masuk pipeline kredit + create SLA log stage pertama.

---

### 3.4 Closing

```
POST /api/?action=prospect_close
Content-Type: application/json
Authorization: Bearer {token}
```

**Body (Kredit):**
```json
{
  "prospect_id": 1,
  "closing_account_number": "00123456789",
  "closing_realization_amount": 150000000,
  "closing_tenor": 36,
  "closing_note": "Pencairan berhasil"
}
```

**Validasi:** Kredit WAJIB `closing_account_number` dan `closing_realization_amount`.

---

### 3.5 Reject

```
POST /api/?action=prospect_reject
Content-Type: application/json
Authorization: Bearer {token}
```

**Body:**
```json
{
  "prospect_id": 7,
  "reject_reason": "Data tidak memenuhi syarat",
  "reject_note": "BI Checking buruk"
}
```

---

### 3.6 Add SLA Stage

```
POST /api/?action=prospect_sla_log
Content-Type: application/json
Authorization: Bearer {token}
```

**Body:**
```json
{
  "prospect_id": 1,
  "stage": "SURVEI_JAMINAN",
  "note": "Survei lokasi jaminan di Pendrikan"
}
```

**stage valid:** `VERIFIKASI_DATA` | `SURVEI_JAMINAN` | `ANALISA_KREDIT` | `KOMITE_KREDIT` | `PERSETUJUAN` | `AKAD_KREDIT` | `PENCAIRAN`

> **Auto:** Stage sebelumnya otomatis di-close dan dihitung durasinya.

---

### 3.7 SLA Pipeline

```
GET /api/?action=prospect_sla_pipeline
Authorization: Bearer {token}
```

Returns semua prospek status SLA (pipeline kredit) dengan `sla_days` dan `current_stage`.

---

## 4. REPORT

### 4.1 Prospect Report (Summary)

```
GET /api/?action=prospect_report&{params}
Authorization: Bearer {token}
```

**Params:**
| Param | Default | Keterangan |
|-------|---------|------------|
| `korwil` | all | Filter korwil |
| `kode_kantor` | all | Filter cabang |
| `source` | all | ao / non_ao |
| `closing_from` | Tgl 1 bulan lalu | Range closing dari |
| `closing_to` | Tgl akhir bulan lalu | Range closing sampai |
| `harian_date` | Hari ini | Tanggal untuk report harian |

**Response:**
```json
{
  "status": 200,
  "message": "OK",
  "data": {
    "summary": {
      "total_prospek": 8,
      "total_open": 2,
      "total_follow_up": 2,
      "total_sla": 1,
      "total_closing": 1,
      "total_reject": 1,
      "total_from_ao": 4,
      "total_from_non_ao": 4,
      "total_pending_delegasi": 2,
      "total_realisasi": 200000000
    },
    "closing_period": {
      "from": "2026-05-01",
      "to": "2026-05-31",
      "jumlah": 3,
      "nominal": 450000000
    },
    "harian": {
      "date": "2026-06-22",
      "jumlah_input": 1
    },
    "per_type": [
      {"prospect_type": "KREDIT", "jumlah": 4, "realisasi": 150000000},
      {"prospect_type": "DEPOSITO", "jumlah": 1, "realisasi": 200000000}
    ]
  }
}
```

---

## 5. MASTER DATA

### 5.1 Kode Kantor (Cabang)

```
GET /api/?action=master_kode_kantor
```

No auth required. Returns daftar cabang + korwil grouping.

### 5.2 Pegawai AO (untuk delegasi)

```
GET /api/?action=master_pegawai_ao&group_jabatan=AO Kredit&kode_kantor=001
Authorization: Bearer {token}
```

---

## 6. UPLOAD

### 6.1 Upload Foto (Base64)

```
POST /api/?action=upload_foto
Content-Type: application/json
Authorization: Bearer {token}
```

**Body:**
```json
{
  "foto_base64": "data:image/jpeg;base64,/9j/4AAQ...",
  "prefix": "prospek"
}
```

**Response:**
```json
{
  "status": 200,
  "message": "Foto berhasil diupload",
  "data": {
    "path": "uploads/prospek/prospek_20260622_143000_abc123.jpg",
    "filename": "prospek_20260622_143000_abc123.jpg"
  }
}
```

---

## Postman Collection Setup

### Import Environment Variables:

**Local:**
```json
{
  "variable": [
    {"key": "base_url", "value": "http://localhost/kunjungan-ao/api"},
    {"key": "token", "value": ""}
  ]
}
```

**Server:**
```json
{
  "variable": [
    {"key": "base_url", "value": "https://visitin-ao.bkkjateng.co.id/api"},
    {"key": "token", "value": ""}
  ]
}
```

### Auto-set token setelah login (Postman Tests script):
```javascript
if (pm.response.code === 200) {
    var body = pm.response.json();
    if (body.data && body.data.token) {
        pm.environment.set("token", body.data.token);
    }
}
```

### Gunakan token di semua request:
```
Authorization: Bearer {{token}}
```

---

## Error Responses

```json
{"status": 400, "message": "Pesan error validasi", "data": null}
{"status": 401, "message": "Unauthorized", "data": null}
{"status": 404, "message": "Not found", "data": null}
{"status": 405, "message": "Method harus POST", "data": null}
{"status": 500, "message": "Server error", "data": null}
```
