# Progres Pengembangan Visitin AO

## 2026-06-24

### Fokus Saat Ini
- Modul prospek untuk semua karyawan.
- Alur AO vs non-AO:
  - AO Kredit input prospek kredit -> auto ke AO sendiri.
  - AO Remedial input prospek aset -> auto ke AO sendiri.
  - AO Dana input tabungan/deposito -> auto ke AO sendiri.
  - AO Kredit input tabungan atau non-AO input prospek -> masuk pending delegasi Kepala Bidang Pemasaran cabang tujuan.
- Pembatasan menu berdasarkan jabatan/cabang dengan tabel permission boolean per menu.
- Pemisahan database:
  - `dpk` untuk prospek/data kredit/corebank-like.
  - `simpeg` untuk data pegawai.

### Yang Sudah Dipelajari
- Repo PHP native tanpa framework.
- API utama ada di `api/index.php`, controller prospek di `api/controllers/ProspectController.php`.
- UI prospek ada di `pages/input-prospek.php`, `pages/daftar-prospek.php`, dan `pages/prospek-detail.php`.
- Kontrak lama belum sinkron: form dan SQL memakai `jenis_usaha/rekomendasi_produk/keterangan_usaha`, tetapi controller dan beberapa UI masih membaca `product_interest/estimated_amount`.
- File SQL saat ini baru berisi tabel `prospects`, belum lengkap untuk `kode_kantor`, follow up, history, SLA, dan menu access.

### Sudah Dikerjakan
- Sinkronisasi input prospek dengan skema baru:
  - Backend menerima `rekomendasi_produk` lalu memetakan otomatis ke `prospect_type`.
  - UI list/detail membaca `rekomendasi_produk` dan `jenis_usaha`, bukan kolom lama.
  - AO auto-assign hanya jika produk cocok dengan permission AO-nya.
  - Produk yang tidak cocok dengan AO penginput masuk pending delegasi Kepala Bidang Pemasaran cabang tujuan.
- Middleware API mengisi session dari Bearer token/cookie agar `created_by` tidak kosong saat request API langsung.
- Endpoint delegasi dibatasi untuk Developer/Superuser Prospek.
- Fix submit form prospek di localhost:
  - API sekarang menerima session halaman sebagai fallback auth.
  - Cookie auth otomatis mengabaikan domain production dan secure flag saat host localhost/127.0.0.1.
- Fix akses database lokal:
  - `.env` lokal diarahkan ke MySQL XAMPP (`localhost`, root tanpa password).
  - Koneksi database punya fallback localhost/127.0.0.1 untuk request lokal.
  - Error DB dibuat lebih informatif dan tidak langsung menampilkan credential lengkap di toast.
  - `kode_kantor` dibuat kompatibel dengan skema lama yang belum punya `is_active`.
  - Ditambahkan runner migration `database/run_migration_dpk.php`.
- Form input prospek diperbaiki:
  - Wilayah sekarang punya provinsi, kab/kota, kecamatan, dan desa/kelurahan.
  - Dropdown cabang dan wilayah bisa diketik/search lewat datalist.
  - Default provinsi diarahkan ke Jawa Tengah, tapi tetap bisa diganti.
  - Layout form dibatasi maksimal di desktop dan tetap full nyaman di mobile.
  - Warna form disamakan dengan tema dashboard (`var(--color-primary)`, `var(--color-secondary)`, `var(--color-accent)`).
- SQL DPK dilengkapi:
  - `kode_kantor`
  - `menu_access_by_jabatan`
  - `prospects`
  - `prospect_follow_ups`
  - `prospect_histories`
  - `prospect_sla_logs`
- Daftar tabel DPK dibuat di `database/TABEL_DPK_PROSPEK.md`.
- SQL SIMPEG dummy dihapus karena SIMPEG existing sudah punya data pegawai.
- README disesuaikan agar SIMPEG disebut sebagai database existing/read-only, bukan database dummy yang harus dibuat.
- `api/.env` diselaraskan untuk dua database: `dpk` dan `simpeg`.
- Flag akses menu global dibuat sejajar dengan kolom `menu_access_by_jabatan`, sementara masih berbasis role/permission dummy.

### Verifikasi
- `php -l` lolos untuk file PHP yang disentuh:
  - `api/controllers/ProspectController.php`
  - `api/controllers/AuthController.php`
  - `api/middlewares/AuthMiddleware.php`
  - `index.php`
  - `views/header.php`
  - `views/navbar.php`
  - `pages/home.php`
  - `pages/input-prospek.php`
  - `pages/daftar-prospek.php`
  - `pages/prospek-detail.php`
- Catatan: PHP lokal menampilkan warning `Module "openssl" is already loaded`, tapi sintaks kode tetap valid.
- Server lokal PHP berhasil start di `http://127.0.0.1:8087`.
- Halaman `/login` merespons HTTP 200.
- API `/api/?action=login` berhasil untuk dummy AO Kredit `201-001`.
- API `master_kode_kantor` masih HTTP 500 di environment ini karena koneksi/migration database belum tersedia.
- Simulasi login halaman lalu submit `prospect_create` sudah tidak kena 401.
- Migration DPK berhasil dijalankan via `php database/run_migration_dpk.php`.
- Submit `prospect_create` berhasil HTTP 201 setelah migration. Data test validasi sudah dihapus kembali.
- Browser in-app tidak tersedia di sesi ini (`iab` tidak aktif), jadi verifikasi visual responsif belum bisa dilakukan.

### Belum Dikerjakan
- Eksekusi migration DB dari terminal, karena command `mysql` belum tersedia di PATH shell ini.
- Test end-to-end semua role di browser.
- Integrasi SIMPEG asli.
- Menu selain prospek.

## 2026-06-25

### Sudah Dikerjakan
- Dropdown wilayah form prospek diganti menjadi searchable dropdown/combobox:
  - Cabang tujuan.
  - Provinsi.
  - Kab/Kota. 
  - Kecamatan.
  - Desa/Kelurahan.
- Dashboard home ditambah ringkasan prospek yang mengikuti scope user:
  - Superuser pusat: konsolidasi semua cabang.
  - Superuser cabang: semua prospek di cabangnya.
  - AO: prospek assigned ke dia + input sendiri.
  - Staff/karyawan: prospek input sendiri.
- Ringkasan angka di home kemudian dihapus lagi agar home fokus sebagai akses menu.
- Ringkasan statistik di daftar prospek sekarang mengambil `prospect_report`, bukan hanya menghitung item di halaman aktif.
- Filter kantor di daftar prospek dikunci untuk user non-pusat agar tetap sesuai kantor user.
- Scope report staff diperbaiki supaya staff hanya melihat prospek input sendiri.
- Delegasi diperketat:
  - Semua input prospek sekarang masuk `BELUM_DIDELEGASIKAN`.
  - Superuser memilih AO sesuai jenis produk dan cabang.
  - Kredit/Debitur Existing -> AO Kredit.
  - Tabungan/Deposito -> AO Dana.
  - Pembeli Aset -> AO Remedial.
  - API delegasi memvalidasi AO tujuan harus sesuai group jabatan dan cabang prospek.
- Query list/detail/SLA dibuat kompatibel dengan beda collation antara `prospects.kode_kantor` dan `kode_kantor.kode_kantor`.
- Detail prospek diberi guard scope role agar user tidak bisa membuka prospek di luar aksesnya via URL langsung.
- Default filter daftar prospek diperbaiki:
  - Closing periode default tetap bulan lalu (`closing_from` awal bulan lalu, `closing_to` akhir bulan lalu).
  - Data/list default menjadi data setelah `closing_to` sampai `harian_date`.
  - Contoh: `closing_to = 2026-05-31`, maka data berjalan `2026-06-01 <= data <= 2026-06-25`.
- Report `harian` diubah menjadi periode berjalan: `created_at > closing_to` dan `created_at <= harian_date`.
- Hak delegasi diperketat:
  - Developer bisa delegasi walaupun kode kantor `000`.
  - Superuser cabang bisa delegasi jika kode kantor bukan `000`.
  - Superuser pusat/kode kantor `000` tidak bisa delegasi.
  - Tombol delegasi di detail mengikuti aturan tersebut.

### Verifikasi
- `php -l` lolos untuk:
  - `api/controllers/ProspectController.php`
  - `pages/input-prospek.php`
  - `pages/home.php`
  - `pages/daftar-prospek.php`
- API `prospect_report` berhasil HTTP 200 untuk session superuser cabang.
- API `prospect_list` berhasil HTTP 200 setelah fix collation join.
- Test create prospek dari AO Kredit menghasilkan `BELUM_DIDELEGASIKAN`.
- Data test validasi sudah dihapus kembali.
- Halaman `/home` dan `/daftar-prospek` merespons HTTP 200.
- API `prospect_report` dengan `closing_to=2026-05-31` dan `harian_date=2026-06-25` mengembalikan `harian.from=2026-06-01`.
- API `prospect_list` dengan range default berjalan berhasil HTTP 200 dan mengambil data dari tabel `prospects`.
- Setelah ringkasan angka home dihapus, `pages/home.php` tetap lolos `php -l` dan `/home` HTTP 200.
- Filter daftar prospek disederhanakan menjadi 2 parameter:
  - `closing_date`, default akhir bulan kemarin.
  - `harian_date`, default tanggal hari ini.
- Query list prospek diperbaiki agar memakai periode berjalan: `created_at > closing_date` dan `created_at <= harian_date`.
- Duplikasi data daftar prospek diperbaiki dengan join `kode_kantor` yang dideduplikasi per `kode_kantor`, sehingga 4 data di tabel `prospects` tampil sebagai 4 item saja.
- API `prospect_list` dengan `closing_date=2026-05-31` dan `harian_date=2026-06-25` berhasil HTTP 200 dengan `total=4`.
- API `prospect_report` dengan parameter baru berhasil HTTP 200 dan mengembalikan:
  - `closing_period.from=2026-05-01`
  - `closing_period.to=2026-05-31`
  - `harian.from=2026-06-01`
  - `harian.date=2026-06-25`
- Halaman `/daftar-prospek` berhasil HTTP 200 setelah perubahan filter.
- Aturan auto-delegasi input prospek AO diperbarui:
  - AO yang input prospek sesuai group AO-nya dan kode kantor prospek sama dengan kode kantor user otomatis `SUDAH_DIDELEGASIKAN` ke dirinya sendiri.
  - Contoh: AO Kredit kode kantor `001` input prospek `KREDIT` untuk kantor `001` otomatis assigned ke AO tersebut.
  - Jika AO input prospek beda cabang, tetap `BELUM_DIDELEGASIKAN`.
  - Jika AO input produk yang bukan group AO-nya, tetap `BELUM_DIDELEGASIKAN` dan perlu delegasi superuser cabang.
- Test API create sementara sebagai AO Kredit `201-001`:
  - `KREDIT` kantor `001` -> `SUDAH_DIDELEGASIKAN`, `assigned_to=201-001`.
  - `KREDIT` kantor `002` -> `BELUM_DIDELEGASIKAN`.
  - `TABUNGAN` kantor `001` -> `BELUM_DIDELEGASIKAN`.
  - Data test sudah dihapus kembali dari tabel `prospects` dan `prospect_histories`.
- Desain dan implementasi awal pipeline/SLA kredit:
  - SLA hanya untuk `KREDIT` dan `DEBITUR_EXISTING`.
  - Pipeline kredit dibuat saat debitur konfirmasi berminat lanjut.
  - SLA baru mulai dihitung saat `Pemberkasan Lengkap`.
  - Tahapan pipeline kredit: `FORMULIR`, `PEMBERKASAN`, `SURVEY`, `ANALISA`, `KOMITE`, `CAIR`, lalu `SELESAI/BATAL`.
  - Dokumen default kredit dibuat otomatis: formulir, KTP suami/istri, SKU/surat usaha, rekening listrik/air/pajak/sertifikat/NPWP, STNK/BPKB, sertifikat/collateral.
- Tabel baru DPK ditambahkan:
  - `prospect_credit_pipelines`
  - `prospect_credit_pipeline_documents`
  - `prospect_credit_pipeline_stages`
- Kolom baru prospek ditambahkan:
  - `closing_asset_purchase_method` untuk closing pembeli aset (`LELANG`, `CESSIE`, `LAINNYA`).
- API baru ditambahkan:
  - `prospect_confirm_credit_interest`
  - `prospect_complete_credit_docs`
- Endpoint `prospect_sla_log` diarahkan ke tabel pipeline kredit baru, tetap kompatibel dengan log SLA lama.
- Closing prospek diperketat sesuai jenis:
  - Kredit wajib nomor rekening dan nominal pencairan, serta sudah masuk SLA/pipeline.
  - Tabungan/deposito wajib nomor rekening dan nominal setoran/deposito.
  - Pembeli aset wajib nama pembeli dan metode pembelian (`LELANG`, `CESSIE`, `LAINNYA`).
- Tampilan detail prospek diperbarui:
  - Tombol `Debitur Mau Lanjut` untuk membuat pipeline kredit.
  - Tombol `Pemberkasan Lengkap` untuk mulai hitung SLA.
  - Tahapan SLA mengikuti alur kredit baru.
  - Berkas kredit tampil sebagai checklist.
  - Form closing berubah sesuai produk prospek.
- Migration DPK berhasil dijalankan setelah penambahan tabel pipeline kredit.
- Test API pipeline kredit sementara sebagai AO Kredit `201-001`:
  - Create prospek kredit kantor `001` otomatis assigned ke `201-001`.
  - Konfirmasi minat berhasil membuat pipeline.
  - Pemberkasan lengkap berhasil mengubah status ke `SLA`.
  - Tambah stage `SURVEY` berhasil.
  - Detail mengembalikan pipeline dengan 6 dokumen dan 3 stage.
  - Data test sudah dihapus kembali dari tabel terkait.
- Halaman `/prospek-detail/6` berhasil HTTP 200 setelah perubahan.
- Perbaikan UI dan upload SLA Kredit:
  - Action tombol detail prospek dibuat grid ringkas agar proses SLA tidak terlalu panjang di layar.
  - Foto prospek tampil sebagai icon/chip `Foto Prospek`, lalu preview dibuka lewat modal.
  - Berkas kredit tampil sebagai checklist dengan tombol icon upload dan view.
  - `FORMULIR` menerima upload foto/scan.
  - Dokumen kredit selain formulir menerima upload PDF.
  - Foto upload dari browser dikompres otomatis sebelum dikirim.
  - Tahap SLA menyesuaikan form:
    - `SURVEY` wajib upload foto kunjungan/jaminan.
    - `ANALISA` wajib upload PDF hasil analisa.
    - Tahap lain cukup catatan.
  - Lampiran stage survey/analisa tampil sebagai icon, preview lewat modal.
- Tabel pipeline kredit diperluas:
  - `prospect_credit_pipeline_documents.file_type`
  - `prospect_credit_pipeline_stages.attachment_url`
  - `prospect_credit_pipeline_stages.attachment_type`
- API `prospect_credit_upload` ditambahkan untuk upload dokumen pipeline kredit.
- `Pemberkasan Lengkap` sekarang menolak mulai SLA jika masih ada dokumen wajib yang belum diupload.
- Test API upload SLA sementara:
  - Upload 1 foto formulir dan 5 PDF dokumen kredit berhasil HTTP 200.
  - `Pemberkasan Lengkap` berhasil setelah semua dokumen upload.
  - Stage `SURVEY` dengan foto berhasil dan attachment terbaca di detail.
  - Data dan file test sudah dihapus kembali.
- Verifikasi:
  - `php -l` lolos untuk controller, router, halaman detail, dan migration.
  - `/prospek-detail/6` HTTP 200.
  - API `prospect_detail&id=6` HTTP 200.
- Perbaikan lanjutan SLA Kredit:
  - Berkas kredit dipindah menjadi sub `Pemberkasan` di dalam card Pipeline SLA, bukan card panjang terpisah.
  - Card informasi nasabah, informasi proses, follow up, dan riwayat aktivitas dibuat buka-tutup agar halaman detail tidak terlalu panjang.
  - Syarat mulai SLA diperlonggar sesuai proses:
    - Wajib minimal `FORMULIR` dan `KTP_SUAMI_ISTRI`.
    - Berkas lain boleh menyusul dan tetap bisa diupload dari sub `Pemberkasan`.
  - Upload berkas susulan tidak lagi memenuhi timeline aktivitas satu per satu; hanya dokumen inti yang dicatat.
  - Tahap SLA yang dipakai: `PEMBERKASAN -> SURVEY -> ANALISA -> KOMITE`.
  - Tahap `CAIR` tidak lagi ditampilkan sebagai proses SLA; setelah komite lanjut closing jika sudah cair.
  - Proses SLA tidak bisa lompat tahap:
    - Dari pemberkasan hanya bisa ke `SURVEY`.
    - Dari survey hanya bisa ke `ANALISA`.
    - Dari analisa hanya bisa ke `KOMITE`.
  - Modal tahap SLA tidak lagi memakai dropdown bebas, melainkan otomatis menampilkan tahap berikutnya.
  - `SURVEY` tetap wajib upload foto.
  - `ANALISA` dan `KOMITE` boleh upload PDF, tapi opsional.
- Test API lanjutan:
  - Upload hanya `FORMULIR` + `KTP_SUAMI_ISTRI` lalu `Pemberkasan Lengkap` berhasil HTTP 200.
  - Coba lompat langsung ke `ANALISA` ditolak HTTP 400.
  - Lanjut `SURVEY` dengan foto berhasil HTTP 200.
  - Data dan file test sudah dihapus kembali.
- `/prospek-detail/6` tetap HTTP 200 setelah perbaikan card collapsible.
- Fix upload pemberkasan tidak muncul:
  - Penyebab: sub `Pemberkasan` berada di dalam `sla-section`, sementara `sla-section` sebelumnya baru tampil setelah status sudah `SLA`.
  - Perbaikan: Pipeline/Berkas Kredit sekarang tampil sejak `credit_pipeline` dibuat oleh tombol `Debitur Mau Lanjut`, walaupun status prospek masih `FOLLOW_UP`.
  - Label tahap sebelum SLA dimulai menjadi `Menunggu pemberkasan`.
  - Test API: setelah `Debitur Mau Lanjut`, detail prospek masih `FOLLOW_UP` tetapi sudah membawa `credit_pipeline` dengan 6 dokumen, sehingga upload pemberkasan bisa tampil.
- Perbaikan detail prospek dan akses SLA:
  - Warna hijau pada form/SLA summary, tombol SLA, icon checklist, dan tombol view diganti ke warna netral/biru agar konsisten.
  - Urutan detail diperbaiki:
    - Header prospek.
    - `Informasi Nasabah` default open.
    - `Pipeline SLA Kredit` default open jika pipeline sudah ada.
    - Informasi lain default tertutup.
  - Upload/re-upload dokumen pipeline:
    - Jika belum pernah upload, tetap boleh upload.
    - Setelah file diupload, masih bisa re-upload selama 7 hari.
    - Setelah lewat 7 hari dari upload, backend menolak re-upload.
  - Upload lampiran stage Analisa/Komite bisa susulan lewat icon upload di stage.
  - Kolom `attachment_uploaded_at` ditambahkan ke `prospect_credit_pipeline_stages` untuk menghitung lock 7 hari lampiran stage.
  - Update SLA dikunci hanya untuk AO pengelola prospek atau developer.
  - Superuser pusat/cabang hanya bisa lihat; API update SLA tetap ditolak.
- Test akses dan lock:
  - Re-upload dokumen setelah `completed_at` dimundurkan 8 hari ditolak HTTP 400.
  - Superuser cabang mencoba `prospect_confirm_credit_interest` ditolak HTTP 403.
  - `/prospek-detail/6` tetap HTTP 200.

### Belum Dikerjakan
- Verifikasi visual browser penuh untuk combobox wilayah di semua device.
- Verifikasi visual browser penuh untuk UI upload dan preview modal SLA Kredit, karena browser in-app tidak tersedia di sesi ini.
- Penyempurnaan UI upload file per dokumen pipeline kredit.
- Report khusus SLA kredit per stage dan overdue.
- Integrasi role/jabatan dari SSO SIMPEG asli.

## 2026-09-03

### Audit Repo Dan Kesepakatan Kerja

- Repo dipelajari ulang sebelum melanjutkan pembangunan.
- Branch aktif saat audit: `prospek` dengan remote `origin/prospek`.
- Working tree sudah memiliki perubahan yang belum di-commit pada:
  - `api/controllers/AoCreditPortfolioController.php`
  - `pages/home.php`
  - `pages/kelolaan-ao-kredit.php`
  - `pages/kunjungan-create.php`
- Perubahan yang sudah ada tersebut dianggap milik pekerjaan sebelumnya dan tidak di-revert.

### Kondisi Arsitektur Saat Ini

- Aplikasi tetap PHP native tanpa framework.
- `index.php` menangani routing halaman dan guard session.
- `api/index.php` menjadi front controller API.
- Router API sudah dipisah menjadi:
  - `api/routers/prospect.php` untuk modul prospek, pipeline, SLA, report, dan master data.
  - `api/routers/ao_credit_portfolio.php` untuk mapping/kelolaan portfolio AO kredit.
- Database memiliki dua koneksi:
  - DPK untuk prospek, pipeline, dan data operasional aplikasi.
  - SIMPEG existing/read-only untuk data pegawai, jabatan, unit kerja, dan kantor.
- Folder `database/` sudah berisi SQL dan runner migration DPK. Database SIMPEG tidak dibuat dari repo karena dianggap sudah tersedia.
- UI prospek dan pipeline kredit sudah terhubung ke API dalam jumlah cukup besar.
- Modul kunjungan lama masih memiliki halaman UI, tetapi endpoint bisnis kunjungan seperti mapping kunjungan, create kunjungan, history, dan detail kunjungan masih perlu ditinjau ulang sebelum diaktifkan penuh.

### Temuan Dokumentasi

- `issue.md` masih berisi blueprint awal REST API kunjungan dan belum menggambarkan seluruh perkembangan modul prospek terbaru.
- `README.md` memuat gambaran arsitektur dan status project, tetapi beberapa bagian perlu selalu diselaraskan dengan `progres.md` jika flow atau status modul berubah.
- `progres.md` menjadi jurnal kronologis utama untuk keputusan dan implementasi terbaru.

### Aturan Pencatatan Mulai Sekarang

- Setiap pekerjaan kode wajib menambahkan entri tanggal baru di file ini.
- Setiap entri minimal menjelaskan:
  - fokus pekerjaan;
  - flow lama dan flow baru jika ada perombakan;
  - file yang dibuat/diubah;
  - alasan keputusan teknis penting;
  - hasil verifikasi dan kendala;
  - pekerjaan lanjutan yang masih terbuka.
- Perubahan flow tidak boleh hanya dijelaskan di chat; ringkasannya harus masuk ke jurnal ini.
- Perubahan database harus dibedakan jelas antara:
  - membaca atau menyesuaikan API terhadap database existing; dan
  - perubahan schema/migration yang memang disetujui secara khusus.
- Sebelum mengubah kode, kondisi working tree dan perubahan pekerjaan sebelumnya harus diperiksa agar tidak tertimpa.
- Sebelum menutup satu pekerjaan, lakukan pengecekan syntax/API/UI yang relevan dan catat hasilnya di bagian `Verifikasi`.

### Prioritas Lanjutan Yang Disepakati

- Fokus pekerjaan berikutnya ditentukan dari issue terbaru, bukan otomatis mengikuti blueprint kunjungan lama.
- Modul prospek, pipeline SLA, dan mapping kelolaan AO kredit diperlakukan sebagai baseline aktif.
- Untuk modul operasional baru, API akan disiapkan agar mengambil data dari database existing; pembuatan tabel baru tidak dilakukan tanpa keputusan khusus.
- Setiap pekerjaan berikutnya akan dimulai dengan membaca `issue.md` dan entri terakhir `progres.md`, lalu ditutup dengan catatan progres baru.

### Belum Dikerjakan Dari Audit Ini

- Tidak ada perubahan source code pada audit ini.
- Belum dilakukan verifikasi ulang seluruh endpoint dan seluruh halaman karena pekerjaan ini hanya menetapkan baseline dan aturan dokumentasi.
- Status aktual beberapa fitur perlu diuji kembali saat issue implementasi berikutnya dipilih.

### Analisis Flow Daftar Prospek Dan Pipeline Kredit

- `pages/pipeline-kredit.php` saat ini bukan halaman dengan logika terpisah. File tersebut hanya mengaktifkan flag `force_pipeline_kredit_page`, lalu meng-include `pages/daftar-prospek.php`.
- Akibatnya, daftar prospek dan pipeline kredit masih berbagi template, filter, sumber data, pagination, dan sebagian besar JavaScript. Perbedaan utamanya ditentukan oleh flag pipeline dan parameter `pipeline_credit=1` ke API.

#### Flow Yang Berjalan Saat Ini

1. Prospek dibuat melalui `prospect_create` dengan status awal `OPEN`.
2. Jika pembuatnya adalah AO yang sesuai kelompok produk dan kantor, prospek otomatis didelegasikan ke dirinya sendiri. Selain kondisi tersebut, prospek masuk status delegasi `BELUM_DIDELEGASIKAN`.
3. Daftar prospek mengambil data melalui `prospect_list`. Scope data berbeda menurut role: AO melihat prospek yang dibuat atau ditugaskan kepadanya, sedangkan superuser/developer dapat melihat data sesuai cakupan kantor/wilayah.
4. Follow up pertama melalui `prospect_follow_up` menyimpan riwayat follow up dan mengubah status `OPEN` menjadi `FOLLOW_UP`.
5. Untuk produk kredit, aksi `Debitur Mau Lanjut` melalui `prospect_confirm_credit_interest` membuat record `prospect_credit_pipelines` dengan stage awal `FORMULIR` dan status pipeline `PROSPECT_CONFIRMED`.
6. Setelah dokumen minimum `FORMULIR` dan `KTP_SUAMI_ISTRI` tersedia, `prospect_complete_credit_docs` memulai SLA. Status prospek berubah menjadi `SLA`, stage menjadi `PEMBERKASAN`, status pipeline menjadi `SLA_RUNNING`, dan deadline SLA dihitung 14 hari.
7. Tahapan SLA harus maju berurutan melalui `prospect_sla_log`: `PEMBERKASAN` ke `SURVEY`, lalu `ANALISA`, kemudian `KOMITE`. Survey mewajibkan foto, Analisa mewajibkan analis kredit yang valid, sedangkan lampiran Analisa/Komite dapat dilengkapi melalui upload.
8. Pada stage `KOMITE`, prospek kredit dapat ditutup melalui `prospect_close`. Sistem mencari realisasi pada database existing, mencocokkan rekening/nama, mengisi nilai realisasi, lalu mengubah prospek menjadi `CLOSING` dan pipeline menjadi `SELESAI`/`DISBURSED`.
9. Prospek non-kredit tidak melalui pipeline kredit. Prospek tersebut dapat langsung menuju closing dari status `FOLLOW_UP` atau `SLA` sesuai tipe produk.
10. Reject menyimpan alasan dan menutup log/stage pipeline yang masih terbuka.

#### Temuan Penting Untuk Perombakan

- Status prospek dan stage pipeline adalah dua dimensi berbeda, tetapi UI saat ini menampilkannya dalam satu daftar sehingga arti `FOLLOW_UP`, `SLA`, `KOMITE`, dan `CLOSING` mudah tercampur.
- `prospect_credit_pipelines` hanya berlaku untuk `KREDIT` dan `DEBITUR_EXISTING`, sudah didelegasikan, serta memiliki AO pengelola. Inilah sebabnya pipeline kredit bukan sekadar daftar semua prospek berstatus SLA.
- Tombol `Pemberkasan Lengkap` sebenarnya baru memvalidasi dokumen minimum dan memulai SLA, bukan memastikan seluruh berkas telah lengkap. Nama aksi dan pesan UI sebaiknya diselaraskan.
- Schema sudah memiliki stage `CAIR`, tetapi flow API saat ini bergerak langsung dari `KOMITE` ke closing/disbursed. Keputusan apakah perlu tahap pencairan eksplisit harus ditetapkan dalam desain baru.
- `changeStatus` dengan target `SLA` meneruskan proses ke penyelesaian dokumen. Ini membuat perubahan status biasa bercampur dengan command bisnis dan sebaiknya dipisahkan.
- Proses closing menggunakan lookup serta pencocokan nama/rekening pada database existing. Ini bagus sebagai validasi, tetapi UI idealnya menampilkan kandidat realisasi untuk dikonfirmasi agar kesalahan matching dapat diaudit.
- Otorisasi aksi closing dan reject perlu diaudit ulang di backend secara eksplisit, sama seperti pembatasan yang sudah diterapkan pada update SLA, agar setiap aksi hanya dapat dilakukan oleh AO pengelola atau role yang memang diberi wewenang.

#### Flow Target Yang Direkomendasikan

- Pisahkan lifecycle umum prospek dari pipeline kredit:
  - Lifecycle prospek: `OPEN` -> `FOLLOW_UP` -> `CLOSING`, dengan jalur keluar `REJECT`.
  - Delegasi: `BELUM_DIDELEGASIKAN` -> `SUDAH_DIDELEGASIKAN` sebagai dimensi ownership, bukan status bisnis utama.
  - Pipeline kredit: `FORMULIR` -> `PEMBERKASAN` -> `SURVEY` -> `ANALISA` -> `KOMITE` -> `CAIR` -> `SELESAI`.
- Tahap pipeline kredit baru dibuat ketika debitur menyatakan minat melanjutkan, bukan ketika prospek baru diinput.
- Minimum dokumen diverifikasi sebelum SLA dimulai. Kelengkapan seluruh dokumen dan validasi tiap stage ditampilkan terpisah agar tidak membingungkan AO.
- Setelah Komite, gunakan keputusan yang jelas: `DISETUJUI`, `DITOLAK`, atau `PERLU_PERBAIKAN`. Hanya keputusan yang disetujui yang boleh menuju `CAIR` dan kemudian closing.
- Closing menjadi command khusus per produk. Kredit memakai data realisasi existing, sedangkan tabungan/deposito/pembeli aset memakai field validasi yang sesuai. Setelah closing berhasil, data tidak boleh diubah sembarang; koreksi harus melalui mekanisme pembatalan/reopen yang memiliki alasan dan audit trail.
- Reject/cancel dapat dilakukan dari tahap yang diizinkan, selalu membutuhkan alasan, dan menyimpan aktor serta waktu perubahan.

#### Pembagian Peran Halaman Yang Disarankan

- `daftar-prospek.php`: inbox/lifecycle seluruh prospek, fokus pada pencarian, ownership, follow up, status umum, dan report.
- `pipeline-kredit.php`: work queue khusus kredit, fokus pada stage, umur SLA, deadline, overdue, AO pengelola, nominal pengajuan, dan aksi tahap berikutnya.
- `prospek-detail.php`: satu-satunya tempat untuk menjalankan command bisnis detail seperti follow up, konfirmasi minat, verifikasi dokumen, update stage, closing, dan reject.

#### Verifikasi Audit

- File yang ditinjau: `pages/daftar-prospek.php`, `pages/pipeline-kredit.php`, `pages/prospek-detail.php`, `api/routers/prospect.php`, `api/controllers/ProspectController.php`, dan `database/create_tables_prospek.sql`.
- Audit ini tidak mengubah source code maupun schema database.
- Belum dilakukan pengujian runtime/browser pada flow ini. Pengujian tersebut perlu dilakukan setelah rancangan state transition disepakati dan implementasi perombakan dimulai.

#### Pekerjaan Lanjutan

- Tetapkan state transition resmi dan siapa yang berwenang menjalankan setiap transition.
- Tetapkan apakah stage `CAIR` wajib digunakan sebelum closing.
- Audit dan perbaiki authorization closing/reject di backend.
- Pisahkan query dan UI pipeline kredit dari daftar prospek secara bertahap tanpa membuat tabel baru.
- Tambahkan test API untuk transition yang valid, transition melompat, akses lintas AO/cabang, duplicate closing, dan overdue SLA.

### Implementasi Fetch Profil Login Dan Component FE Dasar

- Flow lama halaman profil menggunakan data dummy statis, antara lain nama, jabatan, NIK, kantor, email, dan nomor telepon. Tombol logout hanya mengarah ke halaman login, sementara UI ganti password masih tampil walaupun backend-nya belum disiapkan.
- Flow baru halaman profil memanggil `GET /api/?action=whoami` dengan `credentials: include`, sehingga data diambil dari session/cookie SSO aktif. Jika session/token tidak valid, halaman mengarahkan user kembali ke login.
- Data profil yang ditampilkan sekarang meliputi nama, ID pegawai, unit kerja, jabatan, level, kode kantor, kantor penempatan, email, telepon, role, dan kelompok jabatan.
- Logout sekarang memanggil `POST /api/?action=logout`, membersihkan data token lokal, lalu mengarahkan user ke login.
- UI ganti password sengaja tidak dimasukkan ke rancangan baru sesuai kesepakatan pekerjaan.

#### Component FE

- Ditambahkan `assets/css/components.css` sebagai dasar komponen reusable: surface, section, info grid, action button, status, avatar, dan aturan responsive.
- Ditambahkan `assets/js/components.js` dengan namespace `VisitinUI` untuk helper frontend seperti initials dan escaping text.
- Asset component dimuat global melalui `views/header.php` agar halaman lain dapat menggunakannya tanpa menyalin CSS/JavaScript.
- `pages/profile.php` dibangun ulang menggunakan komponen tersebut dengan layout dua kolom di desktop dan satu kolom di mobile.
- Upload foto masih berupa preview lokal karena endpoint penyimpanan foto profil belum menjadi bagian pekerjaan ini.

#### Backend Auth

- `AuthController::buildSessionUser` membawa `kode_unit_kerja` jika tersedia dari SSO atau database SIMPEG existing.
- Response `whoami` meneruskan field profil non-sensitif yang diperlukan frontend. Tidak ada tabel baru dan tidak ada perubahan schema database.

#### Verifikasi

- `php -l pages/profile.php`: lulus.
- `php -l api/controllers/AuthController.php`: lulus.
- `php -l views/header.php`: lulus.
- Tidak ada runtime login SSO yang dijalankan karena membutuhkan kredensial dan koneksi SSO aktif. Warning `openssl already loaded` berasal dari konfigurasi PHP lokal.

#### Pekerjaan Lanjutan

- Uji browser pada mobile, tablet, dan desktop menggunakan akun demo/SSO yang tersedia.
- Pastikan response `whoami` dari SSO production mengirim field kode unit dengan nama key yang sesuai.
- Gunakan component FE pada halaman lain secara bertahap setelah pola visual profil disetujui.
- Siapkan endpoint upload foto profil dan fitur ganti password pada fase terpisah.

### Privasi Data Profil

- NIK/NIP dihapus dari tampilan `pages/profile.php` karena termasuk data sensitif.
- Field NIK/NIP juga tidak lagi diteruskan melalui session aplikasi dan response `whoami` yang dibentuk `AuthController` karena tidak dibutuhkan oleh fitur profil saat ini.
- Query database existing tetap boleh memiliki alias `nik` untuk kebutuhan modul lain; perubahan ini hanya membatasi exposure pada flow autentikasi/profil.
- Verifikasi: pencarian referensi NIK pada `pages/profile.php` dan field response `whoami` di `AuthController` sudah dibersihkan. Tidak ada perubahan schema database.

### Penyederhanaan Navigasi Responsif

- Flow navigasi lama menampilkan Home, Prospek, Mapping, History, dan Profile pada bottom navigation.
- Flow navigasi baru untuk fase awal hanya menampilkan Home, Create Prospek, Profile, dan Logout.
- Mapping dan History tidak dihapus dari source code atau route; keduanya hanya disembunyikan dari navigasi sampai modulnya siap digunakan.
- Navigasi desktop sekarang berupa sidebar tetap di sisi kiri dengan label dan icon.
- Navigasi mobile sekarang berupa rail vertikal tetap di sisi kanan agar sesuai pola penggunaan layar sempit. Wrapper konten diberi ruang tambahan supaya tidak tertutup rail.
- `Create Prospek` mengarah langsung ke `pages/input-prospek.php`.
- Logout pada navbar memanggil `POST /api/?action=logout`, membersihkan token lokal, lalu mengarahkan ke halaman login.
- File yang diubah: `views/header.php` dan `views/navbar.php`.
- Tidak ada perubahan database dan tidak ada perubahan pada flow API prospek.

#### Verifikasi

- Struktur navigasi diperiksa ulang agar hanya empat menu fase awal yang dirender.
- `php -l views/header.php`: lulus.
- `php -l views/navbar.php`: lulus.
- Visual browser desktop/mobile belum tersedia pada sesi ini; breakpoint yang dipakai adalah desktop mulai `768px`, mobile di bawah `768px`.

### Revisi Posisi Navbar Dan Konfirmasi Logout

- Flow navbar disesuaikan: mobile kembali menggunakan bottom navigation horizontal, sedangkan tablet/desktop menggunakan rail icon-only di sisi kiri dan posisi vertikal di tengah layar.
- Konten mobile diberi ruang bawah sebesar tinggi navbar; konten desktop diberi ruang kiri agar rail tidak menutup isi halaman.
- Konfirmasi logout sekarang memakai component modal reusable dari `assets/js/components.js` dan style dari `assets/css/components.css`.
- Modal dipakai bersama oleh logout navbar dan logout pada halaman profile, mendukung tombol Batal, konfirmasi logout, klik backdrop, dan tombol Escape.
- Mapping dan History tetap tidak ditampilkan di navbar sesuai scope fase awal.
- Tidak ada perubahan database maupun flow API bisnis.

### Penyempurnaan Navbar Floating Dan Preferensi Tampilan

- Padding kiri/kanan layout yang sebelumnya membuat konten terdorong menjauh dari tepi dihapus. Navbar sekarang melayang di atas layout dan tidak mengambil ruang konten.
- Mobile menggunakan bottom navigation floating dengan bentuk pill, blur, shadow, dan item aktif berwarna primary.
- Tablet/desktop menggunakan rail icon-only floating di kiri tengah, tanpa label teks agar tampak ringkas dan modern.
- Ditambahkan component preferensi tampilan di profile untuk mengatur jenis font dan ukuran text.
- Preferensi disimpan per `employee_id` menggunakan `localStorage`, lalu dibaca oleh `assets/js/components.js` pada semua halaman melalui CSS variable global.
- Ukuran text tersedia dari 90% sampai 120%, sedangkan font tersedia Default Sistem, Arial, Verdana, dan Georgia.
- Tidak ada tabel baru. Penyimpanan lintas perangkat memerlukan endpoint ke database existing pada tahap lanjutan.

### Penyesuaian Akhir Posisi Dan Label Navbar

- Navbar web/tablet dipindahkan menjadi rail icon-only floating di pojok kanan bawah.
- Navbar mobile tetap berada di bawah, tetapi ukurannya dibuat compact dan seluruh label teks disembunyikan sehingga hanya icon yang tampil.
- Layout konten tetap tanpa padding tambahan karena navbar melayang di atas halaman.

### Navbar Mobile Full Width Dan Logout Terpusat

- Navbar mobile diubah menjadi full width di bagian bawah layar dengan sudut atas membulat dan tetap hanya menampilkan icon.
- Navbar web/tablet tetap compact icon-only di pojok kanan bawah.
- Tombol Logout pada `pages/profile.php` dihapus agar logout hanya tersedia dari navbar utama.
- Modal konfirmasi logout tetap berjalan melalui component reusable pada navbar.

### Penyempurnaan Scrollbar Global

- Scrollbar vertical dan horizontal dibuat lebih tipis dengan ukuran 6px pada browser berbasis WebKit/Chromium.
- Warna scrollbar dibuat netral, rounded, transparan pada track, dan sedikit lebih gelap saat hover.
- Firefox juga memakai mode `scrollbar-width: thin` dan warna yang disesuaikan.
- Style diterapkan global melalui `views/header.php` agar semua halaman dan component memiliki tampilan scroll yang konsisten.

### Penambahan Pilihan Font Modern

- Pilihan font pada preferensi profile diperluas menjadi Inter, Roboto, Plus Jakarta Sans, Nunito Sans, Default Sistem, dan Arial.
- Font modern dimuat satu kali melalui `views/header.php`, sedangkan pemilihan font tetap dikelola oleh component `VisitinUI.preferences`.
- Pengaturan font tetap tersimpan per `employee_id` di browser dan otomatis diterapkan pada semua halaman.

### Navbar Icon-Only Final

- Navbar dikembalikan menjadi rail icon-only permanen seperti referensi desain.
- Label teks dan atribut tooltip `title` dihapus dari item navbar; `aria-label` tetap dipertahankan untuk aksesibilitas.
- Klik icon langsung menjalankan navigasi atau membuka konfirmasi logout tanpa tahap membuka label.
- Tombol FAB Create Prospek pada daftar/pipeline tetap dihapus karena akses Create Prospek tersedia dari navbar.

### Navbar Desktop Satu Icon Setelah Idle

- Ukuran icon dan button desktop diperkecil agar rail lebih ringan dan tidak mengganggu area report.
- Setelah 5 detik tanpa interaksi, navbar desktop otomatis collapse menjadi satu icon toggle.
- Navbar terbuka kembali saat icon toggle diklik atau pointer/focus masuk ke area navbar.
- Saat terbuka, menu tetap icon-only; tidak ada judul atau label tambahan.
- Navbar mobile tetap full-width di bawah dan tidak ikut collapse.

### Penyempurnaan Visual Navbar Mobile

- Bottom navbar mobile dipoles menjadi glass bar full-width dengan sudut atas membulat dan shadow yang lebih ringan.
- Setiap menu memakai ukuran tile icon yang konsisten, bukan lagi membagi lebar secara penuh.
- Menu aktif menggunakan tile primary dengan shadow lembut agar lebih jelas dan modern.
- Jarak antar icon disesuaikan untuk layar normal dan layar sempit hingga 420px.

### Pemadatan Dock Navbar Mobile

- Tinggi bottom dock dan padding vertikal diperkecil agar tidak menyisakan ruang putih berlebihan.
- Group icon dibuat lebih rapat dan terpusat dengan ukuran tile yang konsisten.
- Item aktif sedikit terangkat dengan transform dan shadow lembut untuk memberi kesan dock modern.
- Sudut dan garis atas dock diperhalus tanpa mengubah mode full-width mobile.

### Centering Group Icon Navbar

- Group icon mobile dikunci berada di tengah dock dengan `justify-content: center`.
- Ukuran setiap tile tetap seragam dan gap kiri/kanan dibuat konsisten agar komposisi seimbang.
- Spacing layar sempit hingga 420px dibuat sedikit lebih rapat agar tidak terdesak.

### Pemerataan Jarak Icon Mobile

- Distribusi icon mobile diubah menjadi `space-evenly` agar jarak antar icon serta ruang kiri dan kanan dock sama rata.
- Tile active tetap memakai ukuran tetap sehingga tidak melebar dan tetap terlihat seperti dock modern.

### Pemadatan Rail Desktop Dan Icon Create Prospek

- Rail navbar non-mobile diperkecil lagi pada lebar, padding, gap, tinggi item, dan ukuran icon agar lebih hemat ruang di sisi kanan.
- Mode collapsed desktop juga dipadatkan menjadi toggle yang lebih kecil.
- Icon Create Prospek diganti dari `fa-user-plus` menjadi `fa-file-circle-plus` agar lebih merepresentasikan proses input prospek.

### Komponen Filter Daftar Prospek Dan Pipeline

- Filter lanjutan pada `pages/daftar-prospek.php` diubah dari elemen `<details>` menjadi panel filter reusable dengan tombol `Filter` di kanan atas header.
- Panel filter dapat dibuka/tutup melalui tombol utama, tombol close, klik di luar panel, atau tombol `Escape`. Setelah `Terapkan Filter` dijalankan, panel otomatis menutup agar area report/list kembali lapang.
- Komponen reusable ditambahkan ke `assets/css/components.css` dan `assets/js/components.js` melalui `VisitinUI.bindFilterPanel`, sehingga halaman lain cukup memakai atribut `data-filter-component`, `data-filter-trigger`, dan `data-filter-panel`.
- `pages/pipeline-kredit.php` tetap memakai ulang `daftar-prospek.php`, sehingga pola filter baru otomatis berlaku untuk Daftar Prospek dan Pipeline Kredit tanpa duplikasi markup.
- Parameter filter dan flow request API tetap dipertahankan; tidak ada perubahan database atau pembuatan tabel baru.

### Metric Empat Status Dan Tampilan Data Responsif

- Ringkasan status pada `pages/daftar-prospek.php` disederhanakan menjadi empat metric reusable: `Open`, `Follow Up`, `Pipeline`, dan `Closing`, masing-masing memakai icon compact. Metric `Reject` tidak lagi ditampilkan sebagai kartu ringkasan.
- Label `SLA` pada ringkasan diganti menjadi `Pipeline`; sumber hitungan API tetap memakai data SLA yang sudah tersedia, sedangkan pada halaman Pipeline Kredit pemetaan tahap existing tetap dipakai.
- Daftar prospek sekarang memakai component `ui-responsive-data`: mobile menampilkan kartu prospek, sedangkan layar mulai `768px` menampilkan tabel dengan kolom yang menyesuaikan mode Daftar Prospek atau Pipeline Kredit.
- Aksi detail, status/delegasi, checkbox bulk, pagination, dan request API tetap dipertahankan pada kedua bentuk tampilan.
- Component responsive ditambahkan ke `assets/css/components.css` dan `assets/js/components.js` agar halaman lain dapat menggunakan struktur slot `data-responsive-cards` dan `data-responsive-table` tanpa membuat komponen baru.

### Keputusan Pemisahan Page Daftar Prospek Dan Pipeline Kredit

- Disepakati secara arsitektur bahwa `pages/daftar-prospek.php` dan `pages/pipeline-kredit.php` sebaiknya menjadi dua entry point dengan flow masing-masing.
- `daftar-prospek.php` akan difokuskan sebagai inbox lifecycle umum: pencarian, sumber prospek, ownership, delegasi, follow up, closing umum, dan report.
- `pipeline-kredit.php` akan difokuskan sebagai work queue kredit: stage pipeline, umur/SLA, deadline, nominal pengajuan, AO pengelola, dokumen, dan aksi tahap berikutnya sampai closing.
- Yang dipakai bersama hanya component UI dan helper API seperti filter panel, metric, responsive card/table, formatter, dan request client. Business rule pipeline tidak akan ditambahkan sebagai cabang baru ke halaman daftar prospek.
- Pemisahan akan dilakukan bertahap: entry point pipeline dibuat mandiri terlebih dahulu, komponen bersama diekstrak, lalu request API dan hak akses diuji ulang. Tidak ada pembuatan tabel database baru.

### Perbaikan Checklist Delegasi Prospek Open

- Checkbox delegasi pada tampilan card dan tabel tidak lagi bergantung secara kaku pada nilai `delegation_status = BELUM_DIDELEGASIKAN`.
- Prospek berstatus `OPEN` sekarang dapat dichecklist selama user memiliki hak delegasi dan prospek belum berstatus `SUDAH_DIDELEGASIKAN`, termasuk data existing yang nilai delegation status-nya kosong/null.
- Validasi endpoint delegasi tidak diubah; backend tetap menolak prospek yang sudah didelegasikan dan memvalidasi akses, cabang, serta group AO tujuan.
- Badge `Pending` pada card dan tabel disamakan dengan aturan checkbox agar data existing yang delegation status-nya kosong tetap terbaca siap didelegasikan.

### Pemisahan Page Dan Segmentasi Lifecycle Prospek

- `pages/pipeline-kredit.php` sekarang menjadi entry point mandiri dan tidak lagi meng-include `pages/daftar-prospek.php`.
- `pages/daftar-prospek.php` difokuskan sebagai inbox lifecycle umum. Data yang tampil dibatasi ke status `OPEN` dan `FOLLOW_UP`.
- Inbox prospek memiliki tiga segmentasi reusable:
  - `Aktif 3 Bulan`: status `OPEN` atau `FOLLOW_UP` dengan input maksimal tiga bulan terakhir.
  - `Hot Prospek`: status `OPEN` atau `FOLLOW_UP` dengan input maksimal satu bulan terakhir.
  - `Terbengkalai`: status `FOLLOW_UP`, belum memiliki record `prospect_credit_pipelines`, dan input berada pada rentang satu sampai tiga bulan terakhir.
- Ringkasan inbox prospek mengikuti segmentasi baru: `Open`, `Follow Up`, `Hot Prospek`, dan `Terbengkalai`.
- Pipeline Kredit hanya mengambil prospek jenis `KREDIT` atau `DEBITUR_EXISTING` yang sudah didelegasikan dan sudah memiliki AO. Status `REJECT` dikeluarkan dari antrean; status `CLOSING` tetap dapat dilihat sebagai hasil akhir proses.
- Pipeline memiliki filter tahap `FORMULIR`, `PEMBERKASAN`, `SURVEY`, `ANALISA`, `KOMITE`, `CAIR`, dan `SELESAI`, serta filter jenis, korwil, cabang, AO, pencarian, dan periode input.
- Desktop memakai tabel dan mobile memakai card melalui component `ui-responsive-data`. Filter panel dan metric tetap memakai component reusable yang sudah ada.
- API `prospect_list` dan `prospect_report` ditambah parameter `lifecycle`, `status_in`, `pipeline_stage`, dan rentang `date_from/date_to` untuk mendukung dua page tanpa membuat tabel database baru.
- Flow bisnis yang berubah: prospek baru tetap dikelola di inbox sampai `OPEN`/`FOLLOW_UP`; setelah dikonfirmasi lanjut kredit dan didelegasikan, pekerjaan berpindah ke page Pipeline Kredit hingga closing.

### Audit Repository Dan Fondasi FE Components (2026-09-07)

- Seluruh struktur repository sudah dipetakan: router halaman pada `index.php`, halaman pada `pages/`, layout global pada `views/`, endpoint pada `api/`, asset lintas halaman pada `assets/`, schema pada `database/`, serta dokumentasi pada `docs/`.
- `assets/` sebelumnya baru memiliki `components.css`, `components.js`, dan PDF.js. Component dasar yang sudah ada mencakup layout surface, metric, modal konfirmasi, filter panel, responsive card/table, preferensi font/ukuran teks per user, dan helper navbar/profile.
- Fondasi component FE diperluas tanpa mengubah business rule page: primitive toolbar, button, icon button, field input/select/textarea, tabs, badge/status, alert, empty/loading state, skeleton, dan pagination.
- `VisitinUI` sekarang memiliki helper reusable untuk API GET/POST, formatter currency/number/date/text, debounce pencarian, tabs, state loading/empty/error/content, dan wrapper toast.
- `views/header.php` menambahkan meta `app-base` sebagai sumber base URL untuk API client reusable. Asset component tetap dimuat global agar semua page bisa melakukan refactor bertahap.
- Dokumentasi penggunaan component dibuat pada `assets/README.md`, termasuk contoh markup, kontrak `data-*`, aturan prefix class `ui-*`, dan aturan komentar penjelasan:
  - HTML: `<!-- # COMPONENT: ... -->`
  - CSS: `/* COMPONENT: ... */`
  - JavaScript: `// COMPONENT: ...`
  - PHP: `# COMPONENT: ...`
- Belum ada refactor massal page pada pekerjaan ini. Component dibuat sebagai fondasi terlebih dahulu agar perubahan tampilan page berikutnya konsisten dan mudah dirawat.
- Branch `dev-responsif` menjadi branch kerja yang dituju sesuai arahan. Status aktual branch belum dapat diverifikasi dari environment ini karena executable Git tidak tersedia.

### Daftar Pekerjaan Yang Belum Dikerjakan Setelah Audit (2026-09-07)

- **P0 - Integrasi data kunjungan:** mengganti dummy/legacy pada `get_mapping` dan `create_kunjungan` dengan query database existing, termasuk upload foto, validasi lokasi, dan response detail.
- **P0 - Page kunjungan:** menghubungkan `kunjungan-create.php`, `kunjungan-detail.php`, `kunjungan-history-debitur.php`, dan `history.php` ke API yang disepakati.
- **P0 - Page mapping dan nominatif:** menghapus simulasi login/data hardcode dan menyiapkan endpoint untuk mapping, nominatif, filter wilayah, bucket, serta hak akses.
- **P0 - Kepemilikan database:** meninjau `AoCreditPortfolioController::ensureTables()` dan memastikan aplikasi memakai schema database existing; pembuatan tabel runtime tidak boleh berjalan tanpa keputusan deployment yang jelas.
- **P1 - Refactor component FE:** migrasikan page secara bertahap ke `assets/css/components.css` dan `assets/js/components.js`, dimulai dari input prospek, detail prospek, daftar prospek, pipeline, profile, mapping, nominatif, history, dan kunjungan.
- **P1 - Kontrak API:** lengkapi dokumentasi endpoint aktual, parameter lifecycle/pipeline terbaru, response pagination, error, upload, dan role authorization pada `docs/API_POSTMAN.md`.
- **P1 - Autentikasi produksi:** finalisasi konfigurasi SSO SIMPEG, cookie/session production, CORS origin spesifik, dan hapus ketergantungan dummy setelah environment siap.
- **P1 - Keamanan upload:** validasi MIME/ukuran/ekstensi, nama file, storage, dan akses file private untuk seluruh upload prospek/kunjungan.
- **P2 - Halaman pendukung:** selesaikan API untuk janji bayar, hapus buku, kelolaan AO kredit, dan kalkulator sesuai kebutuhan bisnis yang sudah dikonfirmasi.
- **P2 - Quality assurance:** uji API dengan database existing, uji role/permission, uji responsive pada mobile/tablet/desktop, serta uji alur prospek dari input sampai closing.
- **P2 - Dokumentasi:** rapikan README lama yang masih menyebut branch/status/schema/dummy endpoint yang tidak lagi akurat setelah kontrak API final ditetapkan.

### Penyempurnaan Component FE (2026-09-07)

- API client `VisitinUI.api` sekarang menerima response numerik standar (`200`-`299`) dan response legacy `status: "success"`, sehingga migrasi endpoint dapat dilakukan bertahap.
- Kelompok component lama pada `components.css` diberi komentar `COMPONENT` berdasarkan tanggung jawabnya: surface/typography, action button, avatar/preferences, metric, responsive data, filter, dan confirmation modal.
- Hasil verifikasi: `components.js` lulus `node --check`, file PHP terkait lulus `php -l`, dan jumlah kurung CSS seimbang (`169` pembuka serta `169` penutup).

### Status Branch (2026-09-07)

- Branch `dev-responsif` sudah tersedia di repository.
- Worktree saat ini masih berada di branch `prospek` dengan perubahan lokal pada beberapa page, controller, view, dan asset component.
- Perpindahan branch ditolak Git karena perubahan lokal berisiko tertimpa oleh isi `dev-responsif`. Tidak dilakukan `stash`, reset, checkout paksa, atau penghapusan perubahan. Setelah perubahan lokal diamankan melalui commit terpisah, branch dapat dipindahkan dengan aman.

### Detail Pipeline Dan Konfirmasi Debitur Lanjut (2026-09-07)

- Entry point baru `pages/detail-pipeline.php` dibuat dengan judul `Detail Pipeline Kredit` dan mendelegasikan render ke implementasi existing. Route `pages/prospek-detail.php` tetap tersedia sebagai kompatibilitas agar URL lama tidak langsung rusak.
- Tautan dari `daftar-prospek.php` dan `pipeline-kredit.php` sekarang mengarah ke `/detail-pipeline/{id}`. Navbar juga mengenali page baru untuk status menu aktif.
- Modal `Debitur Mau Lanjut` diperbarui menggunakan component `ui-field`, `ui-input`, `ui-select`, `ui-textarea`, dan `ui-button` agar pola form konsisten serta tetap responsif pada layar kecil.
- Field yang wajib dikonfirmasi saat debitur lanjut:
  - status SLIK: `Sudah dilakukan` atau `Belum dilakukan`;
  - rencana tanggal realisasi;
  - jumlah yang akan diajukan;
  - catatan tambahan bersifat opsional.
- Data baru disimpan pada tabel existing `prospect_credit_pipelines` melalui kolom `slik_checked` dan `planned_realization_date`. Schema awal dan `database/run_migration_dpk.php` sudah diperbarui; tidak ada tabel baru.
- Dokumen pipeline sekarang bersifat opsional. Dokumen tetap dapat diupload setelah pipeline dibuat, tetapi upload tidak lagi menjadi syarat untuk memulai SLA.
- Tombol lanjutan diubah menjadi `Mulai Proses SLA`. Sistem mencegah SLA dimulai dua kali dan menampilkan status dokumen sebagai opsional.
- Dokumen API diperbarui pada `docs/API_POSTMAN.md` dengan payload konfirmasi baru dan aturan upload opsional.
- Flow terbaru: `FOLLOW_UP` -> `Debitur Mau Lanjut` -> pipeline `FORMULIR` + metadata SLIK/tanggal/plafon -> `Mulai Proses SLA` -> `PEMBERKASAN` -> tahap pipeline berikutnya -> closing.

### Verifikasi Detail Pipeline (2026-09-07)

- `node --check` untuk JavaScript inline `prospek-detail.php` berhasil setelah ekspresi PHP dihilangkan khusus untuk proses pemeriksaan.
- `php -l` berhasil untuk controller, migration, alias page baru, dan navbar.
- Smoke test route `/detail-pipeline/21` mengembalikan `302` ke login karena belum ada session, sesuai guard autentikasi.
- Asset `assets/js/components.js` berhasil diakses dengan HTTP `200`.
- Server development lokal dihentikan setelah smoke test selesai.

### Syarat Pipeline: SLIK Bagus Dan Data IDEP (2026-09-07)

- Hasil SLIK pada modal konfirmasi menjadi gate utama: pilihan `Bagus dan dapat diproses` membuka field lanjutan, sedangkan `Tidak bagus / langsung Reject` langsung mengubah status prospek menjadi `REJECT`.
- Nomor IDEP menjadi input wajib pada konfirmasi Debitur Mau Lanjut.
- Lampiran file IDEP bersifat opsional, hanya menerima TXT dengan MIME `text/plain`, dan dibatasi 2 MB pada frontend serta backend.
- Setelah SLIK bagus, data yang wajib dilengkapi hanya `no_idep`, `jumlah_pengajuan_debitur`, dan `rencana_tgl_realisasi`; file `idep.txt` tetap opsional.
- Kolom existing `prospect_credit_pipelines` digunakan melalui schema/migration: `slik_checked`, `idep_number`, `idep_file_url`, dan `planned_realization_date`. Tidak ada tabel baru.
- File TXT IDEP disimpan pada folder upload `uploads/idep/` dan path-nya dikembalikan pada detail pipeline.
- Pipeline yang tampil pada antrean `pipeline-kredit` hanya yang memiliki `slik_checked = 1`; SLIK buruk tidak dapat lanjut ke SLA.

### Gate SLIK Dan Reject Otomatis (2026-09-07)

- Backend memvalidasi hasil SLIK setelah pengecekan akses prospek, sehingga request langsung ke API tidak dapat melewati gate frontend.
- SLIK buruk menjalankan mekanisme reject terpusat dengan alasan `SLIK tidak bagus`, mencatat history, dan tidak membuat pipeline kredit.
- SLIK bagus baru memvalidasi empat kebutuhan flow: nomor IDEP, file TXT IDEP opsional, jumlah pengajuan debitur, dan rencana tanggal realisasi.
- Kolom legacy `realization_probability` tetap nullable untuk kompatibilitas data lama, tetapi sudah tidak ditampilkan, diminta, atau divalidasi dalam konfirmasi pipeline baru.
- SLA tetap hanya dapat dimulai jika pipeline memiliki SLIK bagus dan nomor IDEP.

### Penyelarasan Tampilan Pipeline Dengan Flow Baru (2026-09-07)

- Indikator `Peluang` yang bergantung pada `realization_probability` dihapus dari kartu dan tabel `pipeline-kredit` agar tidak menampilkan nilai kosong atau meminta data di luar flow yang disepakati.
- Kartu dan tabel pipeline sekarang fokus pada nasabah, tahap, AO, jumlah pengajuan, realisasi, cabang, status, dan umur pipeline.
- Query legacy `realization_probability` tetap dipertahankan di backend hanya untuk kompatibilitas data existing; tidak ada penghapusan kolom database.
- Verifikasi akhir: lint PHP untuk controller, page detail, page pipeline, alias detail, dan migration lulus; JavaScript component serta inline JavaScript detail/pipeline lulus `node --check`; route detail tanpa session mengembalikan `302` ke login.

### Perbaikan List Prospek Kosong (2026-09-07)

- Penyebab ditemukan pada query `COUNT` endpoint `prospect_list`: pembentukan query menggunakan regex umum `FROM` dan salah mengambil `FROM` di dalam subquery `kode_kantor`.
- Query count sekarang dibuat sebelum `ORDER BY` dan `LIMIT`, lalu hanya mengganti bagian utama `SELECT ... FROM prospects p` menjadi `SELECT COUNT(*)`; seluruh join dan filter tetap dipertahankan.
- Dampak yang diperbaiki mencakup data list, total badge, dan pagination pada `daftar-prospek.php` serta page lain yang memakai endpoint `prospect_list`.
- Pengujian database menemukan query list juga masih memilih kolom legacy `pc.realization_probability` yang belum tersedia pada database existing. Kolom tersebut dihapus dari query list karena sudah tidak digunakan pada tampilan maupun flow baru.
- Pengujian berikutnya menemukan database existing belum memiliki kolom `slik_checked`, sehingga query `pipeline_credit` gagal sebelum mengembalikan data. Filter pipeline sekarang mendeteksi ketersediaan kolom: pipeline baru tetap memakai gate SLIK bagus, sedangkan record legacy tetap ditampilkan sampai migration metadata dilakukan.
- Verifikasi dengan session developer simulasi berhasil: endpoint `prospect_list?pipeline_credit=1` mengembalikan `200` dengan 11 pipeline dan endpoint `prospect_report?pipeline_credit=1` juga mengembalikan `200` dengan ringkasan 11 pipeline.

### Foto Survey Opsional Dan Kamera Pada SLA (2026-09-07)

- Tahap `SURVEY` sekarang boleh disimpan tanpa foto, sehingga upload 0 foto tetap valid.
- Modal SLA menerima maksimal 4 foto Survey sekaligus dari file picker. Validasi jumlah dan tipe file dilakukan di frontend serta backend.
- Ditambahkan tombol `Jepret Foto` yang memakai kamera belakang perangkat melalui `capture="environment"`; hasil jepretan ikut diproses seperti foto upload biasa.
- Kontrol input Survey dipisah menjadi tombol `Pilih Foto` dan `Jepret Foto`. Kamera browser memakai `getUserMedia` dengan fallback ke input kamera perangkat jika permission atau API kamera tidak tersedia.
- Foto yang dipilih atau dijepret langsung ditampilkan sebagai thumbnail sebelum submit agar user dapat memastikan gambar yang akan dikirim.
- Foto dikompres menjadi JPEG sebelum dikirim agar ukuran request lebih ringan.
- Kompresi foto Survey dibuat bertahap berdasarkan ukuran byte dan ditargetkan maksimal 1 MB per foto; backend menolak file yang masih melewati batas tersebut.
- Foto Survey disimpan sebagai JSON array path pada kolom existing `prospect_credit_pipeline_stages.attachment_url`; tidak ada tabel baru atau migration tambahan.
- Detail pipeline menampilkan tombol preview dan download untuk setiap foto, termasuk lampiran legacy yang masih berupa satu URL.
- Deretan ikon foto Survey pada baris SLA diringkas menjadi satu ikon galeri; modal galeri menampilkan seluruh foto dan tombol download per foto.
- Jika tahap Survey sebelumnya disimpan tanpa foto, tombol upload susulan tersedia selama window upload masih terbuka; total foto existing dan baru tetap tidak boleh lebih dari 4.
- Tahap `ANALISA` sekarang hanya membutuhkan pemilihan analis cabang; input upload/foto dan tombol upload lampiran dihilangkan.
- Backend menolak upload lampiran baru untuk `ANALISA`; tahap `KOMITE` tetap menggunakan lampiran PDF tunggal opsional.
- Riwayat SLA juga tidak menampilkan preview/download lampiran pada baris `ANALISA`, sehingga UI tahap tersebut benar-benar fokus pada analis dan status proses.
- Komponen dokumen pipeline dipindahkan secara dinamis tepat setelah baris `PEMBERKASAN`, sehingga dokumen berada di bawah tahap yang menjadi konteksnya pada desktop maupun mobile.
- Panel `Dokumen Pipeline` sekarang default tertutup saat detail dibuka agar area detail tidak penuh.
- Upload PDF tahap `KOMITE` bersifat opsional dengan batas maksimal 1 MB. PDF yang lebih besar dirender ulang menjadi PDF berbasis gambar dengan beberapa tingkat kualitas sebelum dikirim; backend tetap menolak hasil akhir yang masih melebihi 1 MB.

### Countdown Realisasi Dan Status Pending Pipeline (2026-09-07)

- Kartu mobile dan tabel desktop `pipeline-kredit` sekarang menampilkan rencana tanggal realisasi serta indikator `Kurang N hari`, `Hari ini`, atau `Lewat N hari`.
- Tanggal memakai kolom existing `planned_realization_date`; query list memakai fallback `NULL` jika kolom metadata belum tersedia pada database lama.
- Pipeline dengan `pipeline_status = PROSPECT_CONFIRMED` ditampilkan sebagai `Pending` tanpa mengubah status lifecycle prospek menjadi `FOLLOW_UP` atau memindahkannya kembali ke inbox prospek.
- Keputusan flow: `Pending` tetap berada di Pipeline karena debitur sudah menyatakan lanjut. Pemindahan kembali ke Prospek hanya diperlukan jika bisnis nanti membuat aksi khusus `Batalkan Pipeline` atau `Kembali Ke Prospek`.
- Header `detail-pipeline` juga menampilkan rencana realisasi beserta countdown yang sama dan memakai badge `Pending` untuk pipeline yang belum memulai SLA.
- Verifikasi: `php -l` untuk controller dan halaman detail lulus, JavaScript inline detail serta `assets/js/components.js` lulus `node --check`, dan `git diff --check` untuk file perubahan utama tidak menemukan whitespace error baru.

### Perapian Aksi Lampiran Pada Baris SLA (2026-09-07)

- Baris SLA pada `detail-pipeline` memakai layout grid yang memisahkan informasi tahap, durasi, dan grup aksi.
- Tombol preview, download, dan upload Komite sekarang berada dalam satu grup sehingga tombol upload tidak turun sendirian pada layar sempit.
- Ikon galeri Survey tetap ditempatkan di grup aksi yang sama; fungsi preview galeri dan download per foto tetap dipertahankan.
- Tampilan mobile dibuat fleksibel agar grup aksi tetap sejajar dan dapat membungkus bersama jika ruang layar benar-benar terbatas.
- Ukuran tombol aksi pada baris SLA dipadatkan menjadi 30px dengan jarak konsisten agar tidak menutupi area report.

### Default Informasi Proses Terbuka (2026-09-07)

- Panel `Informasi Proses` pada detail pipeline sekarang terbuka secara default agar data proses langsung terlihat saat halaman dibuka.
- Fungsi accordion tetap dipertahankan, sehingga panel masih dapat ditutup atau dibuka dengan menekan judulnya.

### Parser Dan Screening Awal File IDEP (2026-09-07)

- File IDEP berekstensi `.txt` diperlakukan sebagai JSON IDEP sesuai format laporan yang diberikan, lalu divalidasi saat upload.
- Backend membaca ringkasan kualitas terburuk, jumlah fasilitas, fasilitas aktif, hari tunggakan, nominal tunggakan, plafon efektif, dan baki debet.
- Parser menormalisasi karakter encoding rusak yang kadang ikut terbawa dari export IDEP agar JSON tetap dapat dibaca.
- Sistem menyimpan ringkasan analisis pada kolom metadata `idep_analysis_json`; isi mentah IDEP tidak disalin ke database.
- Screening otomatis memberi label `SLIK baik` hanya jika kualitas terburuk `1`, tidak ada hari tunggakan, dan tidak ada nominal tunggakan. Kondisi lain diberi label review atau risiko untuk diverifikasi petugas.
- Ringkasan juga menghitung utilisasi plafon serta eksposur setelah jumlah pengajuan, tetapi tidak menyimpulkan overvalue karena data IDEP tidak memuat penghasilan dan nilai agunan.
- Detail pipeline menampilkan hasil screening IDEP dalam panel responsif di bagian `Informasi Proses`.
- Detail pipeline sekarang menyediakan tombol upload/ganti file IDEP langsung pada baris `File IDEP`; upload memanggil endpoint khusus tanpa membuat ulang pipeline.
- Setelah upload berhasil, link file dan panel screening SLIK diperbarui langsung tanpa reload halaman.
- Hasil screening sekarang ditampilkan dalam modal `Hasil Analisis SLIK` setelah upload; jika sudah pernah dianalisis, modal dapat dibuka kembali melalui ikon analisis di baris `File IDEP`.
- Link untuk membuka isi mentah TXT IDEP dihilangkan dari detail; yang ditampilkan hanya status file, tombol upload/ganti, dan tombol hasil analisis.
- Modal analisis diperluas dengan posisi data terakhir, sebaran kualitas, jumlah fasilitas aktif/lunas, jumlah lembaga, plafon efektif, rincian tunggakan, serta utilisasi dan eksposur.
- Ditambahkan kalkulator kelayakan plafon di modal dengan input penghasilan bersih, angsuran berjalan, jumlah pengajuan, tenor, bunga, dan batas DSR.
- Kalkulator menghitung estimasi angsuran baru, total DSR, sisa kemampuan angsuran, plafon rekomendasi, dan status `Layak secara simulasi` atau `Melebihi simulasi`.
- Metadata pekerjaan dari IDEP tetap tersedia untuk pengembangan lanjutan, tetapi tidak ditampilkan pada modal agar fokus review tetap pada SLIK dan kalkulator plafon.
- Kalkulator bersifat simulasi internal dan tidak menggantikan analisa kredit, verifikasi lapangan, appraisal agunan, maupun keputusan komite.

### Format Angka Kalkulator IDEP (2026-09-07)

- Bagian pekerjaan dan usaha dihapus dari tampilan modal sesuai kebutuhan review.
- Input nominal penghasilan, angsuran berjalan, dan jumlah pengajuan sekarang memakai format ribuan otomatis, sementara perhitungan tetap menggunakan nilai numerik bersih.

### Akses Analisis IDEP Setelah Closing (2026-09-08)

- Hasil analisis IDEP tetap dapat dibuka pada detail pipeline berstatus `CLOSING` atau `REJECT` selama file dan ringkasan analisis tersedia.
- Upload atau ganti file IDEP tetap dikunci setelah prospek berstatus selesai agar data historis tidak berubah.
- Pipeline lama yang sudah memiliki file IDEP tetapi belum memiliki ringkasan analisis akan dianalisis ulang secara aman saat detail dibuka.
- Isi mentah file TXT tetap tidak ditampilkan; pengguna hanya melihat status file dan modal hasil analisis SLIK.

### Pemulihan Login SSO Di Branch Responsif (2026-09-08)

- `pages/login.php` dikembalikan ke versi login lengkap dengan layout responsif 2-panel pada desktop dan single-panel pada mobile.
- Daftar kredensial hardcoded dihapus dari UI karena user pada login harus diverifikasi oleh SSO SIMPEG.
- Flow login tetap menggunakan autentikasi SSO/API dan menyimpan session aplikasi setelah token berhasil diverifikasi.

### Perbaikan Routing Login SSO Localhost (2026-09-08)

- Login browser sekarang selalu memanggil endpoint API aplikasi sendiri, yang meneruskan kredensial ke SSO SIMPEG sesuai `SSO_BASE_URL`.
- Fallback dummy tidak aktif otomatis pada localhost; hanya dapat dipakai jika `LOCAL_LOGIN_FALLBACK=true` disetel eksplisit untuk development.
- Setelah SSO berhasil, backend menyimpan session aplikasi dan halaman diarahkan ke `/home`.

### Shortcut Pergantian User Login (2026-09-08)

- Daftar user yang sering dipakai ditampilkan kembali dalam panel collapsible untuk memudahkan pergantian akun saat development.
- Daftar shortcut dikembalikan menjadi 8 user dengan variasi Developer, AO, Staff, dan Superuser.
- Tombol user hanya mengisi ID Pegawai dan password pada form; proses autentikasi tetap melewati SSO SIMPEG.
- Daftar shortcut tidak menjadi sumber data user dan tidak mengubah mode login production.

### Detail Pipeline Dan Status IDEP (2026-09-08)

- Tombol kembali pada `detail-pipeline.php` sekarang mengarah ke `pipeline-kredit`.
- Detail pipeline menggunakan modal analisis SLIK yang sama dengan detail prospek, sehingga file IDEP tidak dibuka sebagai teks mentah.
- File IDEP lama tanpa ringkasan tetap memunculkan tombol status; bila file valid, ringkasan otomatis dibangun saat detail dibuka.
- Jika file lama tidak ditemukan atau bukan JSON IDEP yang valid, modal menampilkan alasan kegagalannya secara jelas.

### Upload IDEP Sementara Setelah Closing (2026-09-08)

- Status `CLOSING` sementara tetap diperbolehkan upload atau mengganti file IDEP dari detail pipeline.
- Status `REJECT` tetap tidak dapat mengubah file IDEP.
- Hak upload tetap divalidasi di backend berdasarkan akses AO pengelola; perubahan tombol frontend bukan satu-satunya pengaman.

### Penamaan Angsuran Kredit Lain (2026-09-08)

- Label input kalkulator `Angsuran berjalan / bulan` diubah menjadi `Angsuran Kredit Lain / Bulan` agar maksud kewajiban kredit existing lebih jelas.

### Tampilan Rencana Realisasi Setelah Closing (2026-09-08)

- Indikator tanggal pada detail pipeline tidak lagi menampilkan `Lewat ... hari` untuk prospek berstatus `CLOSING`.
- Status `CLOSING` sekarang menampilkan tanggal realisasi aktual bila tersedia dengan indikator hijau `Selesai`.

### Pemulihan Halaman Input Prospek (2026-09-08)

- `pages/input-prospek.php` dipulihkan dari branch `prospek` karena file belum ikut tersedia di branch `dev-responsif`.
- Form input prospek, upload foto, geolocation, pilihan jenis prospek, dan request ke endpoint `prospect_create` kembali tersedia.
- Route `/input-prospek` sudah tidak 404; tanpa session login route mengarahkan user ke halaman login sesuai guard aplikasi.

### Pemisahan Detail Prospek Non-Kredit (2026-09-08)

- Link detail pada daftar prospek sekarang memilih `detail-pipeline` hanya untuk `KREDIT` dan `DEBITUR_EXISTING`.
- `TABUNGAN`, `DEPOSITO`, dan `PEMBELI_ASET` diarahkan ke halaman baru `detail-prospek`.
- Detail non-kredit memakai renderer bersama dalam mode sederhana dan hanya menyediakan alur `Follow Up`, `Closing`, serta `Reject`.
- Informasi SLIK/IDEP, dokumen pipeline, dan tahapan SLA kredit disembunyikan pada mode detail non-kredit.

### Daftar Prospek Menampilkan Semua Status (2026-09-08)

- Filter default daftar prospek diubah menjadi `Semua Status`, sehingga `CLOSING`, `SLA`, dan `REJECT` tidak hilang dari menu Prospek.
- Filter cepat `Aktif 3 Bulan`, `Hot Prospek`, dan `Terbengkalai` tetap tersedia untuk kebutuhan penyaringan operasional.
- Pembatas request `status_in=OPEN,FOLLOW_UP` dihapus dari daftar dan statistik agar status selesai tetap dapat dimuat.
- Tampilan report dan filter lanjutan juga sekarang mendukung `SLA`, `CLOSING`, dan `REJECT`.

### Ringkasan Status Daftar Prospek (2026-09-08)

- Tab lifecycle `Aktif 3 Bulan`, `Hot Prospek`, dan `Terbengkalai` dihapus dari tampilan utama.
- Ringkasan daftar sekarang hanya terdiri dari `Open`, `Follow Up`, `Closing`, dan `Reject`.
- Status `SLA` tetap disimpan sebagai status asli, tetapi jumlahnya digabung ke ringkasan `Follow Up`.
- Daftar dan report tetap memuat semua status; penyaringan status dilakukan melalui filter lanjutan bila diperlukan.

### Toolbar Filter Dan Toggle Tampilan (2026-09-08)

- Filter sumber dan pencarian cepat digabung menjadi satu toolbar yang sejajar pada desktop dan turun responsif pada mobile.
- Tombol `List` dan `Rekap` digabung menjadi satu tombol icon swap agar area kontrol lebih ringkas.
- Komponen toggle tampilan ditambahkan pada `assets/css/components.css` untuk dipakai ulang oleh halaman lain.
- Tombol swap daftar/rekap dipindahkan ke samping tombol pencarian dan memakai warna primary yang sama.
- Label teks `Hasil` dihapus dari area hasil agar ruang tampilan lebih ringkas.

### Konsistensi Component Pipeline Kredit (2026-09-08)

- Toolbar filter dan pencarian `pipeline-kredit.php` disusun sejajar pada desktop dan responsif pada mobile.
- Tahap `Formulir` dan `Cair` dihapus dari tab serta dropdown filter pipeline.
- Stage legacy `FORMULIR` dinormalisasi sebagai `Pemberkasan`, sedangkan `CAIR` ditampilkan sebagai `Selesai` agar data lama tetap terlihat.
- Pipeline berstatus `CLOSING` menampilkan indikator realisasi `Selesai`, bukan hitungan keterlambatan rencana.
- Indikator loading pipeline dipertahankan di toolbar agar request data tetap berjalan setelah judul antrean diringkas.
- Field filter pipeline sekarang memakai component reusable `ui-filter-grid`, `ui-filter-field`, `ui-filter-label`, dan `ui-filter-control` dari `assets/css/components.css`.

### Konsistensi Dropdown Dan Search Pipeline (2026-09-08)

- Grid filter reusable diubah menjadi dua kolom pada desktop dan satu kolom pada mobile agar mengikuti pola `daftar-prospek.php`.
- Search pipeline dan search daftar prospek sekarang memakai component `ui-filter-toolbar`, `ui-filter-search`, dan `ui-filter-search-button`.
- Ukuran input, tombol pencarian, jarak antar kontrol, dan breakpoint mobile diseragamkan.

### Filter Periode Dan AO Pipeline (2026-09-09)

- Tab tahap pipeline diganti menjadi dropdown `Tahap SLA` agar toolbar lebih ringkas.
- Filter `AO Kredit` dipindahkan ke toolbar utama di sisi kanan; filter lanjutan tetap menyimpan jenis, korwil, dan cabang.
- Periode default pipeline memakai `closing_date` akhir bulan lalu dan `harian_date` hari ini.
- Batas periode diterapkan eksklusif pada `closing_date` dan inklusif pada `harian_date`.
- Pipeline kredit sekarang memakai aktivitas tahap SLA terakhir sebagai tanggal filter, sehingga tahap yang maju bulan ini tetap terlihat meski prospek dibuat bulan lalu.

### Filter Pipeline Collapsible (2026-09-09)

- Filter `Closing (M-1)` dan `Harian (Actual)` dipindahkan ke panel filter buka/tutup agar toolbar utama lebih ringkas.
- Dropdown `Jenis` di pipeline dihapus dari tampilan sementara, sehingga opsi Debitur Existing/baru tidak tampil sebagai filter.
- Data pipeline kredit tetap mengambil seluruh prospek kredit yang memenuhi akses, status, delegasi, dan SLIK.
- Tombol `Terapkan Filter` dihapus agar panel lebih ringkas; Korwil, Cabang, AO, tahap, dan periode sekarang menerapkan perubahan otomatis.

### Penyederhanaan Filter Pipeline Dan Prospek (2026-09-09)

- Tombol `Terapkan Filter` pada panel `daftar-prospek.php` juga dihapus.
- Dropdown AO Kredit dipindahkan dari toolbar pipeline ke panel filter lanjutan.
- Sisi kanan toolbar pipeline sekarang memakai dropdown `Status SLA`.
- Status SLA yang tersedia: `Pending`, `SLA Berjalan`, `Approved`, dan `Selesai`.
- Filter `pipeline_status` diterapkan pada endpoint list dan report pipeline kredit.
- Filter lanjutan daftar prospek sekarang auto-apply saat kontrol berubah setelah tombol apply dihapus.

### Toolbar Pipeline AO Dan Search (2026-09-09)

- Dropdown `Status SLA` dihapus dari toolbar pipeline.
- Dropdown `AO Kredit` dipindahkan ke sisi kiri toolbar.
- Urutan toolbar pipeline sekarang `AO Kredit`, `Tahap SLA`, dan pencarian debitur.
- AO Kredit tidak lagi ditampilkan dua kali pada panel filter lanjutan.

### Urutan Filter Periode Dan Kantor (2026-09-09)

- Filter `daftar-prospek.php` dan `pipeline-kredit.php` memakai susunan component yang sama.
- Baris pertama panel berisi `Closing (M-1)` dan `Harian (Actual)`.
- Baris kedua menyatukan filter `Korwil` dan `Cabang` dalam satu grid kantor.
- Filter Jenis dan Status daftar prospek tetap tersedia pada baris berikutnya.

### Ringkasan Dan Kolom Pipeline (2026-09-09)

- Tabel pipeline dimulai dari kolom `Cabang`, lalu `Nasabah`.
- Ringkasan `Analisa` dan `Komite` digabung menjadi `Analisa + Komite`.
- Ringkasan `Total Proses SLA` ditambahkan di posisi paling kanan.
- Tanggal aktif menampilkan `Rencana`, sedangkan data closing menampilkan `Realisasi`.
- Hitungan keterlambatan diganti menjadi durasi `Proses SLA X hari`.
- AO ditampilkan kecil di bawah nama menggunakan ID pegawai dan disembunyikan bila prospek diinput langsung oleh AO.
- Kolom realisasi menampilkan nominal sekaligus persentase dari total pengajuan.
- Durasi proses dari API berhenti di `closed_at` untuk data yang sudah closing.

### Kolom AO Dan Tampilan Durasi SLA (2026-09-09)

- Durasi `Proses SLA` hanya ditampilkan satu kali di bawah nama tahap; kolom rencana/realisasi hanya menampilkan tanggal.
- Kolom `AO` ditambahkan pada tabel pipeline untuk menampilkan ID AO delegasi dan ID penginput/referensi (`referral_by`, dengan fallback `created_by`).
- Kartu responsif ikut menampilkan kedua informasi AO dan satu durasi SLA di bawah tahap.

### Format Nama Detail Dikembalikan (2026-09-09)

- Format detail dikembalikan seperti semula: `Diinput Oleh` dan `AO Pengelola` tampil pada baris terpisah.
- Detail kembali menampilkan nama beserta ID pegawai jika data nama tersedia.
- Tampilan nama pendek dan penggabungan AO hanya berlaku pada list `pipeline-kredit.php`.

### Nama AO Di Pipeline Kredit (2026-09-09)

- Response daftar pipeline kredit menggunakan nama `assigned_to` dari SIMPEG.
- Kolom AO di `pipeline-kredit.php` menampilkan nama pendek, bukan ID pegawai.
- Informasi input/referensi tidak ditampilkan pada kolom pipeline.
- Judul kolom diperjelas menjadi `AO Pengelola`.

### Penyesuaian Ringkasan Pipeline (2026-09-09)

- Kartu `Total Proses SLA` dihapus sehingga ringkasan tahap kembali berisi `Pemberkasan`, `Survey`, dan `Analisa + Komite`.
- Total nominal pengajuan sekarang hanya menjumlahkan prospek berstatus `SLA`; data `CLOSING` tidak ikut.
- Ringkasan nominal menampilkan jumlah `NOA` untuk pengajuan SLA dan realisasi closing.

### Analisis IDEP Saat Konfirmasi Pipeline (2026-09-09)

- Modal `Debitur Mau Lanjut` sekarang menampilkan panel analisis IDEP lengkap segera setelah file IDEP dipilih.
- Panel menggunakan renderer yang sama seperti detail pipeline, termasuk seluruh metrik `Screening IDEP` dan `Kalkulator kelayakan plafon`.
- Endpoint preview hanya membaca file tanpa menyimpan atau mengubah status; file dan analisis baru disimpan setelah konfirmasi pipeline.
- Modal analisis lengkap tetap tersedia pada detail pipeline untuk file IDEP yang sudah tersimpan.

### Ringkasan Tahap Analisa Dan Komite Dipisah (2026-09-09)

- Kartu ringkasan `pipeline-kredit.php` kembali menampilkan `Analisa` dan `Komite` sebagai dua tahap terpisah.
- Jumlah masing-masing kartu memakai `total_pipeline_analisa` dan `total_pipeline_komite` dari API.
- Grid ringkasan desktop disusun menjadi empat kartu: `Pemberkasan`, `Survey`, `Analisa`, dan `Komite`.

### Pemulihan Router Kelolaan AO Kredit (2026-09-09)

- Memulihkan `api/routers/ao_credit_portfolio.php` yang dibutuhkan oleh `api/index.php`.
- Action daftar, detail, simpan target pipeline, dan simpan aktivitas kembali diarahkan ke `AoCreditPortfolioController`.

### Penyederhanaan Menu Home (2026-09-08)

- Kartu `Delegasi Prospek` dihapus dari halaman `home.php` agar menu utama lebih ringkas.
- Akses delegasi tetap tersedia melalui daftar prospek untuk user yang memiliki hak delegasi.
