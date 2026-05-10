# Pembangunan Backend REST API Kunjungan AO (PHP Native)

Dokumen ini adalah panduan detail langkah demi langkah (blueprint) untuk mengembangkan backend API menggunakan PHP Native. Panduan ini dirancang agar mudah dieksekusi oleh programmer junior atau AI Assistant.

## 1. Arsitektur & Struktur Direktori (`api/`)

Aplikasi menggunakan arsitektur MVC sederhana berbasis REST API:
- `api/config/database.php` : Konfigurasi koneksi database (PDO).
- `api/index.php` : Sebagai Front Controller / Router utama. Semua request ke `/api/` akan masuk ke sini dan diarahkan berdasarkan parameter `action`.
- `api/controllers/` : Berisi class controller (misal: `AuthController`, `KunjunganController`) untuk menangani logika bisnis.
- `api/models/` : Berisi class model untuk query ke tabel database.
- `api/helpers/` : Fungsi-fungsi bantuan (seperti `ResponseHelper::json()`, fungsi upload foto base64).
- `api/.env` : File konfigurasi kredensial (database, secret key).

## 2. Pengelolaan Base URL (Local vs Production)

Aplikasi harus dirancang agar *plug-and-play* baik di local maupun server.
Di frontend root `index.php`, variabel `BASE_APP` sudah diatur secara dinamis:
```php
define('BASE_APP',
    (isset($_SERVER['HTTPS']) ? 'https' : 'http') . '://' .
    $_SERVER['HTTP_HOST'] .
    (strpos($_SERVER['HTTP_HOST'], 'localhost') !== false ? '/kunjungan-ao' : '')
);
```
**Tugas Backend:**
Pastikan saat mengunggah file (seperti foto kunjungan), path yang disimpan ke database adalah path relatif atau absolute path yang digenerate menggunakan skema dinamis agar gambar tidak *broken* saat dipindah dari localhost ke `visitin-ao.bkkjateng.co.id`.

## 3. Desain Database & Fitur Auto-Create (Migration)

Buat satu endpoint khusus, misalnya `GET /api/?action=init_db`, yang bertugas menjalankan query `CREATE TABLE IF NOT EXISTS` untuk membuat struktur awal database. 
Tabel inti yang dibutuhkan:
1. `users` : id, nik, password, nama_lengkap, role, kode_cabang.
2. `nasabah` : no_rekening, nama_debitur, baki_debet, kolektibilitas, hari_menunggak, total_tunggakan, tgl_jatuh_tempo, alamat, latitude, longitude.
3. `kunjungan` : id, no_rekening, nik_ao, tgl_kunjungan, kode_tindakan, jenis_tindakan, lokasi_tindakan, orang_ditemui, nominal_janji, tgl_janji, keterangan, latitude, longitude, alamat_gps, foto_url.

## 4. Daftar Endpoint API yang Harus Dibuat

Berikut adalah list endpoint yang harus diimplementasikan oleh programmer/AI yang mengerjakan:

### A. Autentikasi
- **Endpoint**: `POST /api/?action=login`
  - **Body**: `nik`, `password`
  - **Output**: Token JWT / Session Key, dan data user (nama, role).

### B. Dashboard & Mapping
- **Endpoint**: `GET /api/?action=get_dashboard`
  - **Query**: `nik_ao`, `bulan`
  - **Output**: Ringkasan jumlah baki debet, status pencapaian, dan coverage.
- **Endpoint**: `GET /api/?action=get_mapping`
  - **Query**: `nik_ao`, filter tambahan (kecamatan, bucket).
  - **Output**: List nasabah yang harus dikunjungi bulan ini berdasarkan status tunggakan.

### C. Input Kunjungan (Core Feature)
- **Endpoint**: `POST /api/?action=create_kunjungan`
  - **Body**: JSON yang berisi data form `kunjungan-create.php` (`kode_tindakan`, `lokasi_tindakan`, `keterangan`, `latitude`, `longitude`, `foto_base64`).
  - **Tugas Khusus**: Decode `foto_base64`, simpan sebagai file `.jpg` ke dalam folder `uploads/kunjungan/`, dan simpan path file tersebut ke database.

### D. Riwayat (History)
- **Endpoint**: `GET /api/?action=get_history`
  - **Query**: `nik_ao`, `start_date`, `end_date`.
  - **Output**: List riwayat kunjungan yang sudah dilakukan oleh AO.
- **Endpoint**: `GET /api/?action=get_detail_kunjungan&id={id}`
  - **Output**: Data detail kunjungan beserta link foto kunjungan untuk ditampilkan di halaman detail.

## 5. Standar Output Response API

Setiap response API WAJIB menggunakan format JSON standar berikut:
```json
{
  "status": "success",  // atau "error"
  "message": "Data kunjungan berhasil disimpan.",
  "data": { ... } // Opsional, hanya jika ada data yang dikembalikan
}
```
Set header `Content-Type: application/json` pada setiap response di file `api/index.php`.

## 6. Urutan Eksekusi (Roadmap)

1. **Step 1**: Setup koneksi database di `api/config/database.php`.
2. **Step 2**: Buat endpoint `init_db` untuk mem-build tabel otomatis.
3. **Step 3**: Buat fitur Login API & autentikasi dummy.
4. **Step 4**: Buat endpoint `get_mapping` & `get_nominatif` (bisa gunakan dummy data di awal, lalu integrasikan ke database).
5. **Step 5**: Buat fitur `create_kunjungan` beserta fungsi upload base64 gambar.
6. **Step 6**: Buat fitur `get_history`.
7. **Step 7**: Integrasikan (fetch) API tersebut di halaman frontend (menggunakan Vanilla JavaScript `fetch` atau jQuery AJAX).
