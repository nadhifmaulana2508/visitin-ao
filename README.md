# Visitin AO / Kunjungan AO

Prototype aplikasi kunjungan Account Officer berbasis PHP native. Repo ini saat ini berisi UI mobile-first untuk mapping nasabah, input kunjungan, history, profile, dan modul auth awal yang melakukan proxy login ke SIMPEG SSO.

Dokumen ini dibuat sebagai pegangan build ulang: apa yang sudah ada, apa yang belum selesai, urutan progres yang disarankan, dan standar implementasi agar programmer pemula atau AI assistant berikutnya bisa langsung melanjutkan tanpa menebak-nebak.

## Ringkasan Kondisi Saat Ini

Status project: prototype UI + auth bridge awal, belum menjadi aplikasi backend penuh.

Yang sudah ada:

- Routing halaman utama melalui `index.php`.
- Template global di `views/header.php`, `views/navbar.php`, dan `views/script.php`.
- UI mobile untuk `home`, `mapping`, `nominatif`, `history`, `profile`, `janji-bayar`, `hapus-buku`, detail kunjungan, dan history per debitur.
- Auth API awal: `login`, `whoami`, dan `logout` di `api/index.php` lewat `AuthController`.
- Helper API awal: env loader, response JSON, HTTP cURL wrapper, cookie SSO, dan auth middleware.
- Draft form kunjungan lengkap di `pages/kunjungan-create-kal.php` dengan GPS, kamera, upload foto, VA, WhatsApp, smart form PTP/RKS, dan potensi top-up.
- Dokumen issue auth di `docs/issues/001-auth-login.md`.

Yang belum selesai:

- `pages/kunjungan-create.php` masih kosong, padahal banyak tombol mengarah ke halaman ini.
- Endpoint bisnis masih dummy atau belum ada: dashboard, mapping, nominatif, create kunjungan, history, detail, janji bayar, hapus buku.
- Belum ada folder `api/models/`.
- Belum ada adapter/query API untuk membaca database existing.
- `api/.env.example` masih kosong.
- Konfigurasi database masih hardcoded di `api/config/database.php`.
- Folder `uploads/kunjungan/` belum ada.
- Hampir semua halaman frontend masih memakai data hardcoded.
- Logout di profile masih link ke `/login`, belum memanggil API logout.
- Halaman reset password/aktivasi masih form statis.
- Belum ada validasi backend untuk input kunjungan, upload foto, ukuran file, MIME type, dan relasi data.
- Belum ada proteksi authorization per role/cabang/AO untuk data nasabah.
- Belum ada test atau smoke test terdokumentasi.

## Stack

- PHP native.
- Apache/XAMPP.
- MySQL/MariaDB via PDO.
- Bootstrap 5.3 dari CDN.
- Font Awesome dari CDN.
- jQuery dan Select2 pada halaman tertentu.
- Leaflet pada draft form kunjungan.
- SIMPEG SSO sebagai sumber autentikasi.

Belum ada Composer, framework PHP, bundler frontend, atau dependency manager di repo ini.

## Struktur Repo

```text
.
|-- index.php                    # Router halaman utama
|-- .htaccess                    # Rewrite semua request halaman ke index.php
|-- README.md                    # Dokumen kerja project
|-- issue.md                     # Catatan kebutuhan lama
|-- notped.txt                   # Catatan konfigurasi SSO lokal/produksi
|-- api/
|   |-- index.php                # Front controller API berbasis ?action=
|   |-- .env                     # Config lokal, jangan commit kredensial
|   |-- .env.example             # Masih kosong, perlu dilengkapi
|   |-- config/
|   |   |-- database.php         # Koneksi PDO, masih hardcoded
|   |   `-- env.php              # Loader .env ringan
|   |-- controllers/
|   |   `-- AuthController.php   # Login/whoami/logout via SIMPEG
|   |-- helpers/
|   |   |-- cookie.php
|   |   |-- http.php
|   |   `-- response.php
|   `-- middlewares/
|       `-- AuthMiddleware.php
|-- pages/
|   |-- login.php
|   |-- home.php
|   |-- mapping.php
|   |-- nominatif.php
|   |-- history.php
|   |-- profile.php
|   |-- janji-bayar.php
|   |-- hapus-buku.php
|   |-- kunjungan-create.php       # Kosong
|   |-- kunjungan-create-kal.php   # Draft form kunjungan lengkap
|   |-- kunjungan-detail.php
|   `-- kunjungan-history-debitur.php
`-- views/
    |-- header.php
    |-- navbar.php
    `-- script.php
```

## Cara Menjalankan Lokal

1. Letakkan repo di:

```text
C:\xampp\htdocs\kunjungan-ao
```

2. Jalankan Apache dan MySQL dari XAMPP.

3. Buka aplikasi:

```text
http://localhost/kunjungan-ao
```

4. Buka API:

```text
http://localhost/kunjungan-ao/api/?action=whoami
```

5. Siapkan database lokal, misalnya:

```sql
CREATE DATABASE db_kunjungan CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

6. Lengkapi `api/.env.example`, lalu buat `api/.env` dari contoh tersebut.

Contoh isi yang disarankan:

```env
APP_NAME=visitin-ao
APP_ENV=local

DB_HOST=localhost
DB_NAME=db_kunjungan
DB_USER=root
DB_PASS=
DB_CHARSET=utf8mb4

SIMPEG_BASE_URL=http://localhost/rest_api_sso

COOKIE_NAME=sso_token
COOKIE_DOMAIN=
COOKIE_PATH=/
COOKIE_SECURE=false
COOKIE_SAMESITE=Lax
```

Untuk production, `SIMPEG_BASE_URL` diarahkan ke:

```text
https://apisso.bkkjateng.co.id
```

Catatan: `api/config/database.php` belum membaca `.env`, jadi step ini perlu diimplementasikan pada fase backend.

## Routing

### Routing Halaman

Routing utama ada di `index.php`.

Contoh:

```text
/login                     -> pages/login.php
/home                      -> pages/home.php
/mapping                   -> pages/mapping.php
/kunjungan-detail/123      -> pages/kunjungan-detail.php dengan $_GET['id']=123
```

Halaman selain `login` dan `reset` wajib punya `$_SESSION['user_data']`. Saat cookie `sso_token` tersedia, `index.php` membuat session bridge sederhana:

```php
$_SESSION['user_data'] = ['token' => $_COOKIE['sso_token']];
```

### Routing API

API memakai pola:

```text
/api/?action=nama_action
```

Action yang sudah ada:

| Method | Action | Status |
|---|---|---|
| POST | `login` | Sudah ada, proxy ke SIMPEG |
| GET | `whoami` | Sudah ada, proxy ke SIMPEG |
| POST | `logout` | Sudah ada, clear cookie |
| GET | `get_mapping` | Masih dummy |
| POST | `create_kunjungan` | Masih dummy, belum simpan DB/foto |

## Standar Response API

Saat ini helper `sendResponse()` mengirim format:

```json
{
  "status": 200,
  "message": "Login berhasil",
  "data": {}
}
```

Gunakan format ini secara konsisten untuk semua endpoint baru. Hindari endpoint yang kadang memakai `status: "success"` dan kadang `status: 200`, karena frontend akan sulit dibuat stabil.

Rekomendasi final:

```json
{
  "status": 200,
  "message": "Data berhasil disimpan",
  "data": {}
}
```

## Modul Yang Sudah Dipelajari

### Auth

File penting:

- `api/controllers/AuthController.php`
- `api/middlewares/AuthMiddleware.php`
- `api/helpers/cookie.php`
- `api/helpers/http.php`
- `api/config/env.php`
- `pages/login.php`

Alur login:

1. User submit `id_peg` dan `password` dari `pages/login.php`.
2. Frontend memanggil `POST /api/?action=login`.
3. Backend meneruskan data ke `SIMPEG_BASE_URL/auth/login`.
4. Jika SIMPEG mengembalikan token, backend menyimpan token ke cookie `sso_token`.
5. User diarahkan ke `/home`.

Catatan yang harus diperbaiki:

- `api/.env.example` kosong.
- Perlu fallback otomatis local/production untuk `SIMPEG_BASE_URL`.
- Perlu pengujian login terhadap SSO lokal dan production.
- Frontend halaman lain belum memanggil `whoami`, sehingga nama, role, cabang masih dummy.
- Logout di `profile.php` belum memanggil `POST /api/?action=logout`.

### Home

File: `pages/home.php`

Status:

- UI menu utama sudah ada.
- Role, nama user, dan cabang masih hardcoded:

```php
$role = 'remedial';
$nama_user = 'Budi Santoso';
$cabang = 'Cabang Utama';
```

Perlu:

- Ambil data dari `whoami` atau session user lengkap.
- Tampilkan menu berdasarkan role sebenarnya.
- Pastikan route menu yang belum ada tidak membuat user masuk 404.

### Mapping

File: `pages/mapping.php`

Status:

- UI list mapping, filter, ringkasan, badge bucket, coverage sudah ada.
- Data masih hardcoded.
- Filter belum bekerja.
- Tombol "Mulai Kunjungan" mengarah ke `kunjungan-create`, tetapi file target kosong.

Perlu:

- Endpoint `get_mapping`.
- Endpoint `get_dashboard` atau `get_mapping_summary`.
- Filter query: bulan, AO, cabang, kecamatan, desa, status bayar, bucket, kolektibilitas, minimal tunggakan.
- Pagination.
- Integrasi tombol create dengan `no_rekening` atau `mapping_id`.

### Kunjungan Create

File utama saat ini kosong:

- `pages/kunjungan-create.php`

Draft lengkap ada di:

- `pages/kunjungan-create-kal.php`

Fitur yang sudah ada di draft:

- Data debitur readonly.
- Kode tindakan.
- Jenis tindakan.
- Lokasi tindakan.
- Orang ditemui.
- Nominal dan tanggal janji bayar.
- Keterangan.
- GPS dan reverse geocoding.
- Kamera WebRTC.
- Upload foto.
- Hidden input `foto_base64`.
- VA dan tombol WhatsApp.
- Potensi top-up.

Perlu:

- Pindahkan/rapikan draft ke `kunjungan-create.php`.
- Buat parameter URL, misalnya `/kunjungan-create/{no_rekening}`.
- Ambil data debitur dari API, bukan hardcoded.
- Submit via `fetch` ke `POST /api/?action=create_kunjungan`.
- Simpan foto base64 ke `uploads/kunjungan/`.
- Simpan path foto relatif ke database.
- Validasi required field berdasarkan kode tindakan.
- Jika jenis tindakan bukan kunjungan, aturan GPS/foto perlu didefinisikan.

### History

File:

- `pages/history.php`
- `pages/kunjungan-detail.php`
- `pages/kunjungan-history-debitur.php`

Status:

- UI list, raport, detail, watermark foto, timeline debitur sudah ada.
- Semua data masih dummy.

Perlu:

- Endpoint `get_history`.
- Endpoint `get_detail_kunjungan`.
- Endpoint `get_history_debitur`.
- Filter tanggal dan kode tindakan.
- Detail harus memuat foto asli, koordinat, alamat GPS, AO, waktu, dan catatan.
- Raport AO harus dihitung dari data kunjungan nyata.

### Nominatif

File: `pages/nominatif.php`

Status:

- UI list dan filter sudah ada.
- Data masih hardcoded.

Perlu:

- Endpoint `get_nominatif`.
- Filter cabang, kantor kas, kolektibilitas, kecamatan, desa, bucket, minimal tunggakan.
- Role pusat dapat melihat semua cabang.
- Role cabang hanya melihat cabangnya.
- Role AO hanya melihat data yang ditugaskan ke AO tersebut.

### Janji Bayar

File: `pages/janji-bayar.php`

Status:

- UI tab belum bayar/sudah bayar ada.
- Data masih hardcoded.

Perlu:

- Endpoint `get_janji_bayar`.
- Status janji: belum jatuh tempo, jatuh tempo hari ini, lewat jatuh tempo, sudah bayar, batal.
- Relasi ke kunjungan PTP/PET/PPK/LNS.

### Hapus Buku

File: `pages/hapus-buku.php`

Status:

- UI list, filter, Select2, dan dependent dropdown dummy wilayah sudah ada.
- Data masih hardcoded.

Perlu:

- Endpoint `get_hapus_buku`.
- Data saldo PH/HB dari database atau sumber eksternal.
- Filter wilayah dari API, bukan array JS hardcoded.
- Role dan cabang wajib dibatasi.

### Profile

File: `pages/profile.php`

Status:

- UI informasi akun, upload preview foto, sync SIMPEG, ganti password, logout ada.
- Data masih hardcoded.
- Upload hanya preview, belum simpan.
- Sync dan ganti password belum jalan.
- Logout hanya link ke login.

Perlu:

- Ambil data dari `whoami`.
- Tombol logout memanggil API logout.
- Endpoint upload foto profil jika memang dibutuhkan.
- Endpoint sync SIMPEG jika data user akan disimpan lokal.
- Ganti password sebaiknya diarahkan ke SIMPEG, bukan dibuat lokal, kecuali ada requirement berbeda.

### Reset Password

File: `pages/reset.php`

Status:

- UI form ada.
- Belum terhubung ke API.

Perlu:

- Pastikan flow reset/aktivasi berasal dari SIMPEG.
- Buat endpoint proxy hanya jika SIMPEG menyediakan endpoint resmi.
- Jangan menyimpan password lokal tanpa requirement dan hashing yang benar.

## Kontrak Data Dari Database Existing

Project ini tidak perlu membuat tabel baru dari aplikasi. Database utama sudah tersedia di sisi sistem existing. Tugas backend nanti adalah membuat API adapter yang membaca data dari database tersebut, lalu mengubah hasil query menjadi response yang mudah dipakai frontend.

Field minimal yang perlu disiapkan oleh API:

### User / AO

- `id_peg`
- `nama_lengkap`
- `role`
- `kode_cabang`
- `nama_cabang`
- `kode_kantor`
- `nama_kantor`
- `no_hp`

### Nasabah / Nominatif

- `no_rekening`
- `nama_debitur`
- `no_hp`
- `alamat`
- `kecamatan`
- `desa`
- `latitude`
- `longitude`
- `baki_debet`
- `tunggakan_pokok`
- `tunggakan_bunga`
- `total_tunggakan`
- `kolektibilitas`
- `hari_menunggak`
- `tgl_jatuh_tempo`
- `kode_cabang`
- `kode_kantor`
- `nik_ao`

### Mapping / Pipeline

- `no_rekening`
- `nama_debitur`
- `status_bayar`
- `bucket_awal`
- `bucket_target`
- `bucket_actual`
- `bucket_status` seperti `memburuk`, `stay`, atau `perbaikan`
- `last_visit_at`
- `last_visit_code`
- `coverage_status`

### Kunjungan

- `id`
- `no_rekening`
- `nik_ao`
- `tgl_kunjungan`
- `kode_tindakan`
- `jenis_tindakan`
- `lokasi_tindakan`
- `orang_ditemui`
- `nominal_janji`
- `tgl_janji`
- `keterangan`
- `latitude`
- `longitude`
- `alamat_gps`
- `foto_url`

Catatan penting:

- Jangan membuat endpoint migration atau auto-create table untuk production.
- Kalau butuh data dummy untuk demo UI, gunakan response dummy sementara di controller, bukan membuat struktur database baru.
- Setelah akses database existing tersedia, mapping field asli ke kontrak response di atas.

## Endpoint Yang Perlu Dibangun

### Auth

Sudah ada, tetapi perlu dirapikan:

```text
POST /api/?action=login
GET  /api/?action=whoami
POST /api/?action=logout
```

### Dashboard

```text
GET /api/?action=get_dashboard&bulan=2026-06
```

Output minimal:

- total NOA mapping.
- total baki debet.
- coverage kunjungan.
- frekuensi kunjungan.
- contacted vs not contacted.
- status bayar.
- bucket memburuk/stay/perbaikan.
- nominal collect.

### Mapping

```text
GET /api/?action=get_mapping&bulan=2026-06&page=1&limit=10
```

Query opsional:

- `q`
- `kecamatan`
- `desa`
- `status_bayar`
- `bucket`
- `kolektibilitas`
- `min_tunggakan`

### Nominatif

```text
GET /api/?action=get_nominatif&page=1&limit=10
```

### Detail Nasabah

```text
GET /api/?action=get_nasabah&no_rekening=1029384756
```

Dipakai oleh form create kunjungan.

### Create Kunjungan

```text
POST /api/?action=create_kunjungan
```

Body:

```json
{
  "no_rekening": "1029384756",
  "kode_tindakan": "PTP",
  "jenis_tindakan": "Kunjungan",
  "lokasi_tindakan": "Rumah",
  "orang_ditemui": "Debitur",
  "nominal_janji": 2500000,
  "tgl_janji": "2026-06-15",
  "keterangan": "Debitur berjanji bayar.",
  "latitude": -6.9932,
  "longitude": 110.4215,
  "alamat_gps": "Alamat dari GPS",
  "foto_base64": "data:image/jpeg;base64,..."
}
```

Validasi minimal:

- `no_rekening` wajib ada dan terdaftar.
- `kode_tindakan` wajib valid.
- `jenis_tindakan` wajib valid.
- `keterangan` wajib.
- Untuk `jenis_tindakan=Kunjungan`, GPS dan foto wajib.
- Untuk `kode_tindakan` PTP/PET/PPK/LNS, nominal dan tanggal janji/realisasi perlu aturan jelas.
- Foto hanya JPEG/PNG, ukuran dibatasi.

### History

```text
GET /api/?action=get_history&start_date=2026-06-01&end_date=2026-06-30
GET /api/?action=get_detail_kunjungan&id=1
GET /api/?action=get_history_debitur&no_rekening=1029384756
```

### Janji Bayar

```text
GET /api/?action=get_janji_bayar&status=belum
```

### Hapus Buku

```text
GET /api/?action=get_hapus_buku&page=1&limit=10
```

### Wilayah

```text
GET /api/?action=get_kecamatan
GET /api/?action=get_desa&kecamatan=Kaliori
```

## Prioritas Build Ulang

### Fase 0: Rapikan fondasi

- Isi `api/.env.example`.
- Ubah `api/config/database.php` agar membaca `.env`.
- Buat folder `api/models/`.
- Buat folder `uploads/kunjungan/`.
- Buat helper upload foto.
- Samakan format response API.
- Tambahkan handler `OPTIONS` untuk API jika dibutuhkan.

Hasil akhir fase 0:

- Aplikasi bisa jalan lokal.
- API bisa konek database.
- Config local dan production jelas.

### Fase 1: Auth dan session user

- Validasi login dengan SSO lokal dan production.
- Simpan data user hasil `whoami` ke session atau cache lokal.
- Buat helper `currentUser()`.
- Update `home.php` dan `profile.php` agar memakai data user asli.
- Logout profile memanggil `POST /api/?action=logout`.

Hasil akhir fase 1:

- User login, masuk home, melihat nama/role/cabang asli, dan bisa logout.

### Fase 2: Database dan data nasabah

- Buat model/adapter `Nasabah` yang membaca database existing.
- Buat response dummy sementara hanya jika akses database existing belum tersedia.
- Buat endpoint `get_nasabah`, `get_mapping`, dan `get_nominatif`.
- Integrasikan `mapping.php` dan `nominatif.php` ke API.

Hasil akhir fase 2:

- Mapping dan nominatif tampil dari database, bukan hardcoded.

### Fase 3: Create kunjungan

- Jadikan `kunjungan-create-kal.php` sebagai dasar `kunjungan-create.php`.
- Buka halaman dengan parameter `no_rekening`.
- Ambil data debitur via API.
- Submit kunjungan via `fetch`.
- Decode dan simpan foto base64.
- Simpan data kunjungan ke database.
- Tampilkan sukses/gagal yang jelas.

Hasil akhir fase 3:

- AO bisa membuat kunjungan nyata lengkap dengan GPS dan foto.

### Fase 4: History dan detail

- Buat model `Kunjungan`.
- Buat endpoint `get_history`, `get_detail_kunjungan`, `get_history_debitur`.
- Integrasikan `history.php`, `kunjungan-detail.php`, dan `kunjungan-history-debitur.php`.
- Foto detail mengambil file upload asli.
- Link Google Maps memakai koordinat asli.

Hasil akhir fase 4:

- Semua kunjungan yang dibuat bisa dilihat kembali.

### Fase 5: Dashboard, raport, janji bayar

- Buat agregasi dashboard.
- Buat agregasi raport AO.
- Buat endpoint dan UI janji bayar.
- Tentukan aturan PTP yang dianggap sudah bayar/ingkar.

Hasil akhir fase 5:

- Manajemen bisa membaca coverage, performa, dan follow-up janji bayar.

### Fase 6: Hapus buku dan data eksternal

- Tentukan sumber data HB/PH.
- Buat table atau adapter API.
- Integrasikan filter wilayah.
- Terapkan batas akses role/cabang.

Hasil akhir fase 6:

- Data hapus buku bisa dikunjungi dan dimonitor.

### Fase 7: Responsif laptop dan polishing

- Saat ini UI dibatasi `max-width: 480px` di `.mobile-wrapper`.
- Untuk presentasi cabang via laptop, perlu mode responsive:
  - mobile tetap nyaman.
  - tablet/laptop menampilkan layout lebih lebar.
  - bottom nav bisa menjadi sidebar/topbar pada desktop.
- Periksa semua halaman agar teks tidak overflow.
- Uji di lebar 360px, 480px, 768px, 1024px, dan 1366px.

Hasil akhir fase 7:

- Aplikasi layak demo di HP dan laptop.

## Checklist Detail Implementasi

### Backend

- [ ] Isi `api/.env.example`.
- [ ] Refactor `Database` agar membaca env.
- [ ] Tambah `api/models/DatabaseModel.php` atau base model sederhana.
- [ ] Tambah `NasabahController`.
- [ ] Tambah `KunjunganController`.
- [ ] Tambah `DashboardController`.
- [ ] Tambah `WilayahController`.
- [ ] Tambah endpoint `get_dashboard`.
- [ ] Tambah endpoint `get_mapping`.
- [ ] Tambah endpoint `get_nominatif`.
- [ ] Tambah endpoint `get_nasabah`.
- [ ] Tambah endpoint `create_kunjungan`.
- [ ] Tambah endpoint `get_history`.
- [ ] Tambah endpoint `get_detail_kunjungan`.
- [ ] Tambah endpoint `get_history_debitur`.
- [ ] Tambah endpoint `get_janji_bayar`.
- [ ] Tambah endpoint `get_hapus_buku`.
- [ ] Tambah validasi request method di semua action.
- [ ] Tambah validasi input.
- [ ] Tambah authorization berdasarkan token/user.
- [ ] Tambah upload helper untuk foto.
- [ ] Tambah response error yang konsisten.

### Frontend

- [ ] `home.php` memakai data user asli.
- [ ] `profile.php` memakai data user asli.
- [ ] Logout profile memakai API.
- [ ] `mapping.php` fetch data API.
- [ ] Filter mapping bekerja.
- [ ] Pagination mapping bekerja.
- [ ] `nominatif.php` fetch data API.
- [ ] `kunjungan-create.php` diisi dari draft form.
- [ ] Form create kunjungan submit via API.
- [ ] `history.php` fetch data API.
- [ ] `kunjungan-detail.php` fetch detail API.
- [ ] `kunjungan-history-debitur.php` fetch timeline API.
- [ ] `janji-bayar.php` fetch data API.
- [ ] `hapus-buku.php` fetch data API.
- [ ] Wilayah kecamatan/desa fetch API.
- [ ] Semua halaman diuji di mobile dan laptop.

### Keamanan

- [ ] Jangan commit `api/.env`.
- [ ] Cookie production harus `Secure=true`.
- [ ] Cookie production pakai domain yang benar.
- [ ] Semua endpoint data wajib cek auth.
- [ ] Query database wajib prepared statement.
- [ ] Upload foto wajib validasi MIME dan ukuran.
- [ ] Nama file upload wajib dibuat server-side, bukan dari user.
- [ ] Jangan simpan password lokal kecuali ada requirement resmi.
- [ ] Batasi akses data berdasarkan role, cabang, kantor, dan AO.

## Rekomendasi Pola Kode

Gunakan controller tipis dan model untuk query.

Contoh alur:

```text
api/index.php
  -> KunjunganController::create()
    -> AuthMiddleware::require()
    -> validate input
    -> UploadHelper::saveBase64Image()
    -> KunjunganModel::insert()
    -> sendResponse()
```

Contoh struktur model:

```text
api/models/
|-- Nasabah.php
|-- Kunjungan.php
|-- Dashboard.php
`-- Wilayah.php
```

## Catatan Penting Untuk Build Berikutnya

- Jangan mulai dari membuat banyak UI baru. UI sudah banyak, yang paling kurang adalah data dan backend.
- Prioritas pertama setelah README ini adalah membuat fondasi API dan database.
- `kunjungan-create-kal.php` jangan dibuang. Itu adalah kandidat terbaik untuk mengisi `kunjungan-create.php`.
- Pastikan setiap tombol yang ada punya route dan data nyata.
- Untuk demo cepat, boleh pakai response dummy di controller, tapi jangan membuat struktur database baru dari aplikasi.
- Jika SSO local belum siap, buat mode `AUTH_FAKE=true` khusus local agar frontend tetap bisa dikembangkan. Jangan aktifkan mode ini di production.

## Definition of Done MVP

Project dianggap MVP ketika:

- User bisa login lewat SSO.
- User melihat home dengan data dirinya.
- User melihat mapping dari database.
- User membuka detail debitur.
- User membuat kunjungan dengan kode tindakan, GPS, catatan, dan foto.
- Data kunjungan tersimpan ke database.
- Foto tersimpan ke `uploads/kunjungan/`.
- User melihat history dan detail kunjungan yang baru dibuat.
- User bisa logout.
- Aplikasi nyaman dibuka di HP dan laptop.
