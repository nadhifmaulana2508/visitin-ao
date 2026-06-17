# Issue: Pengembangan Modul Prospek, Delegasi AO, SLA Kredit, dan Mapping Debitur

## 1. Latar Belakang

Aplikasi ini digunakan untuk mengelola proses prospek bisnis dan penanganan debitur. Seluruh karyawan wajib dapat menginput data prospek. Prospek tersebut kemudian akan diproses oleh AO sesuai jenis prospeknya.

Jenis prospek yang harus didukung aplikasi adalah:

1. Prospek Kredit
2. Prospek Tabungan
3. Prospek Deposito
4. Prospek Pembeli Aset
5. Prospek dari Debitur Existing

Selain modul prospek, aplikasi juga harus memiliki modul **Mapping Debitur**. Modul ini digunakan untuk membagi daftar debitur kepada AO yang bertugas melakukan pengelolaan, kunjungan, penagihan, atau tindak lanjut sesuai kategori hari menunggak.

Aplikasi harus mendukung alur kerja berdasarkan role pengguna agar proses input, delegasi, follow up, SLA, closing, reject, dan mapping debitur berjalan terstruktur.

---

## 2. Tujuan Pengembangan

Tujuan dari pengembangan fitur ini adalah:

1. Memastikan seluruh karyawan dapat menginput prospek.
2. Memastikan prospek yang diinput dapat didelegasikan kepada AO yang tepat.
3. Memastikan AO dapat melihat pipeline prospek sesuai tugasnya.
4. Memastikan proses prospek kredit memiliki pencatatan SLA dan history proses.
5. Memastikan proses closing kredit wajib mencatat rekening dan nominal realisasi pencairan.
6. Memastikan prospek tabungan dan deposito dapat dicatat sampai closing atau reject.
7. Memastikan prospek pembeli aset dapat dikelola oleh AO Remedial.
8. Memastikan debitur dapat dimapping oleh superuser setiap awal bulan.
9. Memastikan debitur dengan kategori tunggakan tertentu masuk ke AO yang tepat.
10. Memastikan manajemen dapat memonitor progres prospek, pipeline AO, dan mapping debitur.

---


## 3. Role Pengguna

### 3.1 Developer

Developer adalah user dengan akses penuh.

Hak akses Developer:

1. Dapat mengakses seluruh menu.
2. Dapat menjalankan seluruh fitur.
3. Dapat melihat seluruh data.
4. Dapat melakukan testing seluruh alur.
5. Dapat membantu melakukan perbaikan data jika diperlukan.

---

### 3.2 Superuser Prospek dan Mapping

Superuser adalah user yang memiliki kewenangan untuk mengelola dan mendelegasikan data.

User yang termasuk superuser:

1. Kepala Cabang
2. Kepala Bidang Pemasaran

Hak akses superuser:

1. Dapat melihat seluruh prospek di cabangnya.
2. Dapat melihat prospek yang diinput oleh semua karyawan di cabangnya.
3. Dapat mendelegasikan prospek kepada AO.
4. Dapat mendelegasikan prospek kredit kepada AO Kredit.
5. Dapat mendelegasikan prospek tabungan dan deposito kepada AO Dana.
6. Dapat mendelegasikan prospek pembeli aset kepada AO Remedial.
7. Dapat mendelegasikan prospek dari debitur existing kepada AO Kredit.
8. Dapat melakukan mapping debitur di awal bulan.
9. Dapat menentukan AO pengelola debitur berdasarkan kategori hari menunggak.
10. Dapat melihat status prospek dan status mapping debitur.

---


### 3.3 Karyawan Umum / Non-AO

Karyawan umum adalah seluruh karyawan yang bukan AO.

Hak akses karyawan umum:

1. Dapat menginput prospek.
2. Dapat melihat prospek yang pernah diinput oleh dirinya sendiri.
3. Tidak dapat langsung memproses prospek.
4. Prospek yang diinput harus menunggu delegasi dari superuser.
5. Setelah prospek didelegasikan, proses selanjutnya dilakukan oleh AO terkait.

Contoh:

* Teller menginput prospek tabungan.
* Customer Service menginput prospek deposito.
* Staff umum menginput prospek kredit.
* Prospek tersebut tidak langsung masuk ke AO.
* Kepala Cabang atau Kepala Bidang Pemasaran harus mendelegasikan prospek tersebut terlebih dahulu.

---

### 3.4 AO Kredit

AO Kredit adalah user yang bertugas mengelola prospek kredit dan debitur dengan hari menunggak 0 sampai 30 hari.

Hak akses AO Kredit:

1. Dapat menginput prospek kredit.
2. Jika AO Kredit menginput prospek kredit sendiri, maka prospek otomatis terdelegasi ke AO tersebut.
3. Dapat menerima delegasi prospek kredit dari superuser.
4. Dapat memproses prospek kredit dari tahap Open sampai Closing atau Reject.
5. Dapat mengubah status prospek kredit menjadi Follow Up.
6. Dapat mengubah status prospek kredit menjadi SLA jika debitur berminat dan proses kredit dilanjutkan.
7. Dapat melihat pipeline kredit yang sedang berada pada tahap SLA.
8. Dapat mencatat history follow up.
9. Dapat melakukan closing dengan input rekening dan nominal realisasi pencairan.
10. Dapat mengelola debitur dengan kategori hari menunggak 0 sampai 30 hari.

---


### 3.5 AO Dana

AO Dana adalah user yang bertugas mengelola prospek tabungan dan deposito.

Hak akses AO Dana:

1. Dapat menginput prospek tabungan.
2. Dapat menginput prospek deposito.
3. Jika AO Dana menginput prospek tabungan atau deposito sendiri, maka prospek otomatis terdelegasi ke AO tersebut.
4. Dapat menerima delegasi prospek tabungan dan deposito dari superuser.
5. Dapat memproses prospek tabungan dan deposito dari Open sampai Closing atau Reject.
6. Dapat mencatat hasil follow up.
7. Dapat melakukan closing prospek tabungan atau deposito.

---

### 3.6 AO Remedial FE

AO Remedial FE adalah AO Remedial yang bertugas menangani debitur dengan hari menunggak 31 sampai 180 hari.

FE dapat dipahami sebagai Front End Remedial.

Hak akses AO Remedial FE:

1. Dapat menerima mapping debitur dari superuser.
2. Dapat melihat daftar debitur yang wajib dikunjungi.
3. Dapat melihat daftar debitur yang wajib ditagih.
4. Dapat melihat frekuensi kunjungan atau penagihan per bulan.
5. Dapat menginput hasil kunjungan.
6. Dapat menginput hasil penagihan.
7. Dapat mengelola pipeline debitur kategori 31 sampai 180 hari menunggak.

---

### 3.7 AO Remedial BE

AO Remedial BE adalah AO Remedial yang bertugas menangani debitur dengan hari menunggak lebih dari 180 hari dan debitur PH.

BE dapat dipahami sebagai Back End Remedial.

Hak akses AO Remedial BE:

1. Dapat menerima mapping debitur dari superuser.
2. Dapat melihat debitur dengan hari menunggak lebih dari 180 hari.
3. Dapat melihat debitur dengan status PH.
4. Dapat melihat daftar debitur yang wajib dikunjungi.
5. Dapat melihat daftar debitur yang wajib ditagih.
6. Dapat menginput hasil kunjungan.
7. Dapat menginput hasil penagihan.
8. Dapat mengelola pipeline debitur kategori 180 hari ke atas dan PH.

---


### 3.8 AO Remedial FE dan BE

Terdapat kemungkinan satu AO Remedial dapat mengelola dua kategori sekaligus, yaitu FE dan BE.

Artinya, sistem harus mendukung AO yang memiliki akses ganda:

1. Sebagai AO Remedial FE.
2. Sebagai AO Remedial BE.

Jika AO memiliki akses FE dan BE, maka AO tersebut dapat menerima mapping untuk:

1. Debitur menunggak 31 sampai 180 hari.
2. Debitur menunggak lebih dari 180 hari.
3. Debitur dengan status PH.

Sistem tidak boleh membatasi satu AO hanya pada satu kategori jika user tersebut memang diberikan akses FE dan BE.

---

## 4. Modul Prospek

### 4.1 Jenis Prospek

Aplikasi harus mendukung beberapa jenis prospek berikut:

1. Kredit
2. Tabungan
3. Deposito
4. Pembeli Aset
5. Debitur Existing

Setiap prospek minimal memiliki data berikut:

1. Nama calon nasabah / debitur
2. Nomor identitas jika ada
3. Nomor handphone
4. Alamat
5. Jenis prospek
6. Produk yang diminati
7. Nominal estimasi
8. Keterangan prospek
9. Cabang
10. User penginput
11. Tanggal input
12. Status prospek
13. AO yang menerima delegasi
14. Tanggal delegasi
15. User yang melakukan delegasi

---


## 5. Alur Delegasi Prospek

### 5.1 Prospek Diinput oleh AO

Jika prospek diinput oleh AO, maka sistem harus otomatis mendelegasikan prospek tersebut kepada AO yang menginput.

Contoh:

AO Kredit menginput prospek kredit atas nama Bapak Ahmad.

Maka sistem otomatis:

1. Membuat data prospek.
2. Mengisi user penginput = AO Kredit tersebut.
3. Mengisi AO penerima delegasi = AO Kredit tersebut.
4. Mengisi status delegasi = sudah terdelegasi.
5. Mengisi tanggal delegasi = tanggal input prospek.
6. Prospek langsung muncul di pipeline AO Kredit tersebut.

---

### 5.2 Prospek Diinput oleh Non-AO

Jika prospek diinput oleh karyawan non-AO, maka prospek tidak boleh otomatis masuk ke AO.

Prospek harus masuk ke daftar prospek yang menunggu delegasi superuser.

Alurnya:

1. Karyawan non-AO menginput prospek.
2. Sistem menyimpan prospek dengan status delegasi "Belum Didelegasikan".
3. Prospek muncul di menu superuser.
4. Superuser memilih prospek tersebut.
5. Superuser memilih AO yang sesuai.
6. Sistem menyimpan data AO penerima delegasi.
7. Sistem mengubah status delegasi menjadi "Sudah Didelegasikan".
8. Prospek muncul di menu AO terkait.

---

### 5.3 Aturan Delegasi Berdasarkan Jenis Prospek

Aturan delegasi adalah sebagai berikut:

| Jenis Prospek    | Didelegasikan Kepada |
| ---------------- | -------------------- |
| Kredit           | AO Kredit            |
| Tabungan         | AO Dana              |
| Deposito         | AO Dana              |
| Pembeli Aset     | AO Remedial          |
| Debitur Existing | AO Kredit            |

Sistem harus mencegah kesalahan delegasi.

Contoh validasi:

1. Prospek kredit tidak boleh didelegasikan ke AO Dana.
2. Prospek tabungan tidak boleh didelegasikan ke AO Kredit.
3. Prospek deposito tidak boleh didelegasikan ke AO Remedial.
4. Prospek pembeli aset tidak boleh didelegasikan ke AO Dana.
5. Prospek debitur existing hanya boleh didelegasikan ke AO Kredit.

---


## 6. Alur Prospek Kredit

### 6.1 Status Prospek Kredit

Prospek kredit memiliki status:

1. Open
2. Follow Up
3. SLA
4. Reject
5. Closing

---

### 6.2 Penjelasan Status Prospek Kredit

#### Open

Status awal saat prospek kredit baru dibuat atau baru didelegasikan.

Kondisi:

1. Prospek sudah ada di sistem.
2. Belum dilakukan tindak lanjut oleh AO.
3. AO perlu melakukan follow up kepada calon debitur.

---

#### Follow Up

Status ketika AO sudah mulai menghubungi atau menindaklanjuti calon debitur.

Pada tahap ini AO dapat menginput:

1. Tanggal follow up.
2. Metode follow up.
3. Hasil follow up.
4. Catatan follow up.
5. Rencana tindak lanjut berikutnya.

Contoh metode follow up:

1. Telepon
2. WhatsApp
3. Kunjungan
4. Bertemu di kantor
5. Lainnya

---

#### SLA

Status ketika calon debitur berminat dan proses kredit mulai berjalan.

Pada tahap SLA, prospek kredit harus masuk menjadi pipeline AO Kredit.

Sistem harus mulai mencatat history waktu proses kredit sejak masuk SLA.

Tujuan pencatatan SLA:

1. Mengetahui kapan prospek mulai diproses sebagai kredit.
2. Mengetahui berapa lama proses kredit berjalan.
3. Mengetahui berapa lama waktu dari SLA sampai closing.
4. Mengetahui berapa lama waktu dari SLA sampai reject.
5. Membantu monitoring produktivitas AO dan kecepatan proses kredit.

Ketika status berubah menjadi SLA, sistem harus menyimpan:

1. Tanggal masuk SLA.
2. Jam masuk SLA jika diperlukan.
3. User yang mengubah status ke SLA.
4. AO pengelola.
5. Status SLA aktif.


---

#### Reject

Status ketika prospek kredit tidak dilanjutkan.

Reject dapat terjadi karena:

1. Calon debitur tidak berminat.
2. Calon debitur tidak dapat dihubungi.
3. Data calon debitur tidak memenuhi syarat.
4. Pengajuan kredit tidak disetujui.
5. Alasan lain.

Saat reject, user wajib menginput:

1. Tanggal reject.
2. Alasan reject.
3. Catatan reject.

Jika prospek sudah pernah masuk SLA, maka sistem harus menghitung lama proses dari tanggal masuk SLA sampai tanggal reject.

---

#### Closing

Status ketika prospek kredit berhasil cair.

Saat closing kredit, user wajib menginput:

1. Tanggal closing.
2. Nomor rekening.
3. Nominal realisasi kredit yang cair.
4. Catatan closing jika ada.

Sistem tidak boleh mengizinkan closing kredit jika nomor rekening belum diinput.

Setelah closing, sistem harus menghitung lama proses dari tanggal masuk SLA sampai tanggal closing.

---

### 6.3 Alur Lengkap Prospek Kredit

Alur utama:

1. Prospek kredit dibuat.
2. Sistem memberi status awal Open.
3. Jika penginput adalah AO Kredit, prospek otomatis masuk ke AO tersebut.
4. Jika penginput bukan AO Kredit, prospek menunggu delegasi superuser.
5. Superuser mendelegasikan prospek ke AO Kredit.
6. AO Kredit melakukan follow up.
7. Jika calon debitur tidak berminat, AO Kredit mengubah status menjadi Reject.
8. Jika calon debitur berminat, AO Kredit mengubah status menjadi SLA.
9. Saat masuk SLA, prospek masuk ke pipeline kredit AO.
10. AO Kredit memproses kredit.
11. Jika proses gagal, status menjadi Reject.
12. Jika proses berhasil cair, status menjadi Closing.
13. Saat closing, AO wajib menginput rekening dan nominal realisasi pencairan.
14. Sistem menyimpan history proses dan menghitung durasi SLA.

---


## 7. Prospek Tabungan dan Deposito

### 7.1 Status Prospek Tabungan dan Deposito

Prospek tabungan dan deposito memiliki status:

1. Open
2. Follow Up
3. Reject
4. Closing

Prospek tabungan dan deposito tidak menggunakan status SLA.

---

### 7.2 Alur Prospek Tabungan dan Deposito

Alur proses:

1. Prospek tabungan atau deposito dibuat.
2. Sistem memberi status awal Open.
3. Jika penginput adalah AO Dana, prospek otomatis masuk ke AO Dana tersebut.
4. Jika penginput bukan AO Dana, prospek menunggu delegasi superuser.
5. Superuser mendelegasikan prospek ke AO Dana.
6. AO Dana melakukan follow up.
7. Jika calon nasabah tidak berminat, status menjadi Reject.
8. Jika calon nasabah berhasil membuka tabungan atau deposito, status menjadi Closing.
9. Saat closing, AO Dana menginput hasil realisasi.
10. Sistem menyimpan history proses.

Data closing tabungan minimal:

1. Tanggal closing.
2. Nomor rekening tabungan.
3. Nominal setoran awal.
4. Catatan closing.

Data closing deposito minimal:

1. Tanggal closing.
2. Nomor rekening deposito.
3. Nominal deposito.
4. Jangka waktu deposito.
5. Catatan closing.

---

## 8. Prospek Pembeli Aset

Prospek pembeli aset adalah prospek orang atau pihak yang berminat membeli aset.

Alur:

1. Prospek pembeli aset diinput oleh karyawan.
2. Jika penginput adalah AO Remedial, prospek otomatis terdelegasi kepada AO Remedial tersebut.
3. Jika penginput bukan AO Remedial, prospek menunggu delegasi superuser.
4. Superuser mendelegasikan prospek kepada AO Remedial.
5. AO Remedial melakukan follow up.
6. Jika calon pembeli tidak berminat, status menjadi Reject.
7. Jika calon pembeli berhasil melakukan pembelian, status menjadi Closing.

Status prospek pembeli aset:

1. Open
2. Follow Up
3. Reject
4. Closing

Data closing pembeli aset minimal:

1. Tanggal closing.
2. Nama pembeli.
3. Objek aset yang dibeli.
4. Nominal transaksi.
5. Catatan closing.

---


## 9. Prospek dari Debitur Existing

Pada menu AO Kredit, harus terdapat fitur prospek dari debitur existing.

Debitur existing adalah debitur yang sudah pernah atau sedang memiliki hubungan kredit dengan bank, kemudian berpotensi ditawarkan kredit kembali atau fasilitas tambahan.

Alur:

1. Data debitur existing tersedia di sistem.
2. Superuser memilih debitur existing yang akan dijadikan prospek.
3. Superuser mendelegasikan debitur existing tersebut kepada AO Kredit.
4. Prospek muncul di menu AO Kredit.
5. AO Kredit melakukan follow up kepada debitur.
6. Jika debitur berminat, status prospek masuk ke SLA.
7. Jika debitur tidak berminat, status langsung menjadi Reject.
8. Jika proses kredit berhasil, status menjadi Closing.
9. Jika proses kredit gagal, status menjadi Reject.

Validasi:

1. Prospek debitur existing hanya boleh didelegasikan oleh superuser.
2. Prospek debitur existing hanya boleh didelegasikan kepada AO Kredit.
3. Jika debitur berminat, sistem harus mengubah status menjadi SLA.
4. Jika debitur tidak berminat, sistem harus mengubah status menjadi Reject.

---

## 10. Modul Mapping Debitur

### 10.1 Pengertian Mapping Debitur

Mapping Debitur adalah proses pembagian daftar debitur kepada AO yang bertanggung jawab untuk mengelola, mengunjungi, menagih, atau melakukan tindak lanjut kepada debitur tersebut.

Mapping dilakukan oleh superuser pada awal bulan.

Superuser yang dapat melakukan mapping:

1. Kepala Cabang
2. Kepala Bidang Pemasaran

Mapping debitur dilakukan untuk menentukan:

1. Debitur mana yang harus dikelola.
2. Debitur mana yang wajib dikunjungi.
3. Debitur mana yang wajib ditagih.
4. AO mana yang bertanggung jawab.
5. Berapa frekuensi kunjungan atau penagihan dalam bulan tersebut.
6. Pipeline penanganan debitur.

---


### 10.2 Waktu Mapping Debitur

Mapping debitur dilakukan oleh superuser pada awal bulan.

Batas waktu mapping adalah:

**Tanggal 1 sampai tanggal 5 setiap bulan.**

Artinya:

1. Pada tanggal 1, superuser sudah bisa mulai melakukan mapping debitur.
2. Superuser masih bisa melakukan mapping sampai tanggal 5.
3. Setelah lewat tanggal 5, sistem dapat membatasi proses mapping atau memberikan tanda bahwa mapping sudah melewati batas waktu.
4. Jika tetap ingin mengizinkan mapping setelah tanggal 5, sistem harus memberikan status khusus, misalnya "Mapping Terlambat".

Rekomendasi aturan sistem:

1. Tanggal 1 sampai 5: mapping dapat dilakukan secara normal.
2. Tanggal 6 dan seterusnya: mapping tidak dapat dilakukan, kecuali oleh role tertentu seperti Developer atau superuser dengan alasan khusus.
3. Jika mapping setelah tanggal 5 tetap diperbolehkan, maka alasan keterlambatan wajib diinput.

---

### 10.3 Kategori Debitur Berdasarkan Hari Menunggak

Debitur dibagi berdasarkan jumlah hari menunggak.

Aturan kategori:

| Hari Menunggak   | Kategori Pengelolaan            | Pengelola      |
| ---------------- | ------------------------------- | -------------- |
| 0 - 30 hari      | Kredit Aktif / Early Collection | AO Kredit      |
| 31 - 180 hari    | Remedial FE                     | AO Remedial FE |
| 180 hari ke atas | Remedial BE                     | AO Remedial BE |
| PH               | Remedial BE                     | AO Remedial BE |

Keterangan:

1. Debitur dengan hari menunggak 0 sampai 30 hari dikelola oleh AO Kredit.
2. Debitur dengan hari menunggak 31 sampai 180 hari dikelola oleh AO Remedial FE.
3. Debitur dengan hari menunggak lebih dari 180 hari dikelola oleh AO Remedial BE.
4. Debitur dengan status PH dikelola oleh AO Remedial BE.
5. Jika terdapat AO Remedial yang memiliki akses FE dan BE, maka AO tersebut dapat menerima mapping untuk kategori 31 sampai 180 hari, 180 hari ke atas, dan PH.

---


### 10.4 Alur Mapping Debitur

Alur mapping debitur:

1. Sistem menampilkan daftar debitur pada awal bulan.
2. Sistem menampilkan informasi hari menunggak untuk masing-masing debitur.
3. Sistem menampilkan status PH jika debitur termasuk PH.
4. Sistem menentukan kategori pengelolaan berdasarkan hari menunggak.
5. Superuser membuka menu Mapping Debitur.
6. Superuser memilih bulan dan tahun mapping.
7. Superuser melihat daftar debitur yang perlu dimapping.
8. Superuser memilih debitur.
9. Superuser memilih AO pengelola sesuai kategori.
10. Superuser menginput frekuensi kunjungan atau penagihan dalam bulan tersebut.
11. Superuser menyimpan mapping.
12. Sistem membuat pipeline debitur untuk AO terkait.
13. AO dapat melihat daftar debitur yang sudah dimapping kepadanya.
14. AO melakukan kunjungan atau penagihan.
15. AO menginput hasil kunjungan atau penagihan.
16. Superuser dapat memonitor progres mapping.

---

### 10.5 Validasi Mapping Berdasarkan Kategori

Sistem harus melakukan validasi agar mapping tidak salah sasaran.

Aturan validasi:

#### Debitur Menunggak 0 - 30 Hari

1. Hanya dapat dimapping ke AO Kredit.
2. Tidak boleh dimapping ke AO Dana.
3. Tidak boleh dimapping ke AO Remedial FE.
4. Tidak boleh dimapping ke AO Remedial BE.

Contoh:

Jika debitur menunggak 15 hari, maka pilihan AO yang muncul hanya AO Kredit.

---

#### Debitur Menunggak 31 - 180 Hari

1. Hanya dapat dimapping ke AO Remedial FE.
2. Dapat dimapping ke AO yang memiliki akses FE dan BE.
3. Tidak boleh dimapping ke AO Kredit.
4. Tidak boleh dimapping ke AO Dana.
5. Tidak boleh dimapping ke AO Remedial BE murni jika AO tersebut tidak memiliki akses FE.

Contoh:

Jika debitur menunggak 90 hari, maka pilihan AO yang muncul adalah AO Remedial FE atau AO Remedial yang memiliki akses FE dan BE.

---


#### Debitur Menunggak 180 Hari ke Atas

1. Hanya dapat dimapping ke AO Remedial BE.
2. Dapat dimapping ke AO yang memiliki akses FE dan BE.
3. Tidak boleh dimapping ke AO Kredit.
4. Tidak boleh dimapping ke AO Dana.
5. Tidak boleh dimapping ke AO Remedial FE murni jika AO tersebut tidak memiliki akses BE.

Contoh:

Jika debitur menunggak 220 hari, maka pilihan AO yang muncul adalah AO Remedial BE atau AO Remedial yang memiliki akses FE dan BE.

---

#### Debitur PH

1. Selalu masuk kategori BE.
2. Hanya dapat dimapping ke AO Remedial BE.
3. Dapat dimapping ke AO yang memiliki akses FE dan BE.
4. Tidak boleh dimapping ke AO Kredit.
5. Tidak boleh dimapping ke AO Dana.
6. Tidak boleh dimapping ke AO Remedial FE murni jika AO tersebut tidak memiliki akses BE.

Contoh:

Jika debitur memiliki status PH, walaupun hari menunggaknya tidak terbaca, maka sistem tetap menganggap debitur tersebut sebagai kategori BE.

---

### 10.6 Data yang Perlu Dicatat Saat Mapping

Saat superuser melakukan mapping debitur, sistem minimal harus menyimpan data berikut:

1. ID debitur
2. Nama debitur
3. Nomor rekening kredit
4. Cabang
5. Hari menunggak
6. Status PH
7. Kategori mapping
8. Bulan mapping
9. Tahun mapping
10. AO pengelola
11. Tipe AO pengelola: AO Kredit, AO Remedial FE, AO Remedial BE, atau AO Remedial FE dan BE
12. Frekuensi wajib kunjungan dalam bulan tersebut
13. Frekuensi wajib penagihan dalam bulan tersebut
14. Catatan mapping
15. User superuser yang melakukan mapping
16. Tanggal mapping
17. Status mapping

---


### 10.7 Status Mapping Debitur

Status mapping debitur dapat terdiri dari:

1. Belum Dimapping
2. Sudah Dimapping
3. Dalam Proses Kunjungan
4. Dalam Proses Penagihan
5. Selesai
6. Tidak Berhasil Dikunjungi
7. Tidak Berhasil Ditagih
8. Mapping Terlambat

Penjelasan:

#### Belum Dimapping

Debitur belum ditentukan AO pengelolanya.

#### Sudah Dimapping

Debitur sudah diberikan kepada AO tertentu.

#### Dalam Proses Kunjungan

AO sudah mulai melakukan kunjungan.

#### Dalam Proses Penagihan

AO sudah mulai melakukan penagihan.

#### Selesai

Target kunjungan atau penagihan bulan tersebut sudah selesai.

#### Tidak Berhasil Dikunjungi

AO sudah mencoba melakukan kunjungan, tetapi debitur tidak dapat ditemui.

#### Tidak Berhasil Ditagih

AO sudah mencoba melakukan penagihan, tetapi pembayaran belum berhasil didapatkan.

#### Mapping Terlambat

Mapping dilakukan setelah tanggal 5 bulan berjalan.

---

### 10.8 Pipeline Debitur untuk AO

Setelah superuser melakukan mapping, sistem harus otomatis membuat pipeline debitur untuk AO.

Pipeline ini menjadi daftar kerja AO selama bulan berjalan.

Pipeline AO harus menampilkan:

1. Nama debitur
2. Nomor rekening kredit
3. Hari menunggak
4. Kategori debitur
5. Status PH jika ada
6. Nominal kewajiban jika tersedia
7. Tanggal mapping
8. Target frekuensi kunjungan
9. Target frekuensi penagihan
10. Jumlah kunjungan yang sudah dilakukan
11. Jumlah penagihan yang sudah dilakukan
12. Sisa kunjungan yang harus dilakukan
13. Sisa penagihan yang harus dilakukan
14. Status terakhir
15. Catatan terakhir

---


### 10.9 Input Hasil Kunjungan atau Penagihan oleh AO

AO yang menerima mapping harus dapat menginput hasil aktivitas.

Data aktivitas minimal:

1. Tanggal aktivitas
2. Jenis aktivitas
3. Metode aktivitas
4. Hasil aktivitas
5. Catatan aktivitas
6. Bukti aktivitas jika diperlukan
7. Lokasi kunjungan jika diperlukan
8. Tanggal rencana follow up berikutnya

Jenis aktivitas:

1. Kunjungan
2. Penagihan
3. Telepon
4. WhatsApp
5. Surat
6. Lainnya

Hasil aktivitas:

1. Debitur ditemui
2. Debitur tidak ditemui
3. Janji bayar
4. Sudah bayar
5. Belum bisa bayar
6. Menolak bayar
7. Nomor tidak aktif
8. Alamat tidak ditemukan
9. Lainnya

Jika hasil aktivitas adalah "Janji Bayar", maka sistem sebaiknya meminta input:

1. Tanggal janji bayar
2. Nominal janji bayar
3. Catatan janji bayar

Jika hasil aktivitas adalah "Sudah Bayar", maka sistem sebaiknya meminta input:

1. Tanggal pembayaran
2. Nominal pembayaran
3. Bukti pembayaran jika diperlukan

---

## 11. Aturan Khusus Mapping Awal Bulan

### 11.1 Periode Mapping

Mapping dilakukan setiap bulan.

Contoh:

Untuk mapping bulan Januari:

1. Superuser dapat melakukan mapping pada tanggal 1 Januari sampai 5 Januari.
2. Mapping tersebut berlaku untuk pipeline kerja bulan Januari.
3. AO melihat daftar debitur mapping Januari.
4. Setelah masuk bulan Februari, sistem membuat periode mapping baru.

---


### 11.2 Mapping Tidak Boleh Double pada Bulan yang Sama

Satu debitur tidak boleh dimapping dua kali kepada AO berbeda untuk bulan yang sama, kecuali ada fitur reassignment.

Aturan:

1. Jika debitur sudah dimapping pada bulan berjalan, maka statusnya "Sudah Dimapping".
2. Superuser tidak boleh membuat mapping baru untuk debitur yang sama di bulan yang sama.
3. Jika ingin mengganti AO, gunakan fitur ubah mapping atau reassignment.
4. Sistem harus menyimpan history perubahan AO.

---

### 11.3 Reassignment Mapping

Jika diperlukan, superuser dapat mengganti AO pengelola.

Data yang harus dicatat saat reassignment:

1. AO lama
2. AO baru
3. Tanggal perubahan
4. User yang mengubah
5. Alasan perubahan

Contoh alasan:

1. AO pindah tugas.
2. AO cuti.
3. Salah mapping.
4. Beban kerja AO terlalu banyak.
5. Instruksi pimpinan.

---

## 12. History dan Audit Trail

Sistem wajib menyimpan history untuk setiap perubahan penting.

### 12.1 History Prospek

History prospek mencatat:

1. Tanggal input prospek
2. User penginput
3. Tanggal delegasi
4. User pendelegasi
5. AO penerima delegasi
6. Perubahan status prospek
7. Catatan follow up
8. Tanggal masuk SLA khusus kredit
9. Tanggal reject
10. Alasan reject
11. Tanggal closing
12. Rekening closing
13. Nominal realisasi

---

### 12.2 History Mapping Debitur

History mapping debitur mencatat:

1. Tanggal mapping
2. User superuser yang melakukan mapping
3. AO penerima mapping
4. Kategori mapping
5. Frekuensi kunjungan
6. Frekuensi penagihan
7. Perubahan AO jika ada
8. Alasan perubahan AO jika ada
9. Aktivitas kunjungan
10. Aktivitas penagihan
11. Hasil aktivitas
12. Catatan aktivitas

---


## 13. Menu yang Dibutuhkan

### 13.1 Menu Prospek

Submenu:

1. Input Prospek
2. Daftar Prospek Saya
3. Prospek Menunggu Delegasi
4. Delegasi Prospek
5. Pipeline AO Kredit
6. Pipeline AO Dana
7. Pipeline AO Remedial
8. Prospek Debitur Existing
9. Laporan Prospek

---

### 13.2 Menu Mapping Debitur

Submenu:

1. Daftar Debitur Awal Bulan
2. Mapping Debitur
3. Pipeline Debitur AO Kredit
4. Pipeline Debitur AO Remedial FE
5. Pipeline Debitur AO Remedial BE
6. Reassignment Mapping
7. Monitoring Mapping
8. Laporan Mapping Debitur

---

## 14. Contoh Flow Sederhana untuk Programmer

### 14.1 Flow Prospek Kredit dari Karyawan Non-AO

1. User non-AO login.
2. User input prospek kredit.
3. Sistem simpan prospek dengan status Open.
4. Sistem simpan status delegasi Belum Didelegasikan.
5. Superuser login.
6. Superuser buka menu Prospek Menunggu Delegasi.
7. Superuser pilih prospek kredit.
8. Superuser pilih AO Kredit.
9. Sistem simpan AO penerima.
10. Sistem ubah status delegasi menjadi Sudah Didelegasikan.
11. AO Kredit login.
12. AO Kredit melihat prospek di pipeline.
13. AO Kredit follow up.
14. Jika nasabah berminat, AO ubah status ke SLA.
15. Sistem catat tanggal SLA.
16. Jika kredit cair, AO ubah status ke Closing.
17. Sistem wajib meminta nomor rekening dan nominal cair.
18. Sistem hitung lama proses dari SLA sampai Closing.

---


### 14.2 Flow Prospek Kredit dari AO Kredit

1. AO Kredit login.
2. AO Kredit input prospek kredit.
3. Sistem otomatis assign prospek ke AO tersebut.
4. Status awal Open.
5. AO melakukan follow up.
6. Jika berminat, status menjadi SLA.
7. Sistem mencatat tanggal SLA.
8. Prospek masuk pipeline kredit.
9. Jika cair, status menjadi Closing.
10. AO wajib input rekening dan nominal realisasi.
11. Sistem menyimpan history.

---

### 14.3 Flow Mapping Debitur Awal Bulan

1. Tanggal masuk periode awal bulan, yaitu tanggal 1 sampai 5.
2. Superuser login.
3. Superuser buka menu Mapping Debitur.
4. Sistem menampilkan daftar debitur.
5. Sistem menampilkan hari menunggak masing-masing debitur.
6. Sistem mengelompokkan debitur berdasarkan hari menunggak:

   * 0 sampai 30 hari: AO Kredit
   * 31 sampai 180 hari: AO Remedial FE
   * 180 hari ke atas: AO Remedial BE
   * PH: AO Remedial BE
7. Superuser memilih debitur.
8. Superuser memilih AO sesuai kategori.
9. Superuser menginput frekuensi kunjungan dan penagihan.
10. Sistem menyimpan mapping.
11. Sistem membuat pipeline untuk AO.
12. AO login.
13. AO melihat daftar debitur yang harus dikelola.
14. AO melakukan kunjungan atau penagihan.
15. AO menginput hasil aktivitas.
16. Superuser memonitor progres.

---

## 15. Validasi Utama yang Wajib Ada

### 15.1 Validasi Prospek

1. Semua prospek wajib memiliki jenis prospek.
2. Semua prospek wajib memiliki nama calon nasabah atau debitur.
3. Prospek dari non-AO wajib menunggu delegasi superuser.
4. Prospek dari AO otomatis terdelegasi ke AO tersebut.
5. Prospek kredit hanya boleh dikelola AO Kredit.
6. Prospek tabungan dan deposito hanya boleh dikelola AO Dana.
7. Prospek pembeli aset hanya boleh dikelola AO Remedial.
8. Prospek debitur existing hanya boleh dikelola AO Kredit.
9. Closing kredit wajib input nomor rekening.
10. Closing kredit wajib input nominal realisasi pencairan.
11. Reject wajib input alasan reject.
12. SLA hanya berlaku untuk prospek kredit.
13. Tabungan dan deposito tidak memiliki status SLA.

---


### 15.2 Validasi Mapping Debitur

1. Mapping hanya dapat dilakukan oleh superuser.
2. Mapping normal hanya dapat dilakukan tanggal 1 sampai 5 setiap bulan.
3. Debitur 0 sampai 30 hari menunggak hanya dapat dimapping ke AO Kredit.
4. Debitur 31 sampai 180 hari menunggak hanya dapat dimapping ke AO Remedial FE.
5. Debitur 180 hari ke atas hanya dapat dimapping ke AO Remedial BE.
6. Debitur PH selalu masuk kategori BE.
7. AO yang memiliki akses FE dan BE dapat menerima mapping FE dan BE.
8. Satu debitur tidak boleh dimapping ganda pada bulan yang sama.
9. Jika mapping diubah, sistem wajib menyimpan history perubahan.
10. Frekuensi kunjungan atau penagihan wajib diinput saat mapping.

---

## 16. Rekomendasi Struktur Data Sederhana

### 16.1 Tabel users

Field yang dibutuhkan:

1. id
2. nama
3. username
4. role
5. cabang_id
6. is_active

---

### 16.2 Tabel user_roles atau user_permissions

Dibutuhkan karena satu AO bisa memiliki lebih dari satu fungsi, misalnya FE dan BE.

Field yang dibutuhkan:

1. id
2. user_id
3. permission_code

Contoh permission_code:

1. DEV
2. SUPERUSER_PROSPEK
3. AO_KREDIT
4. AO_DANA
5. AO_REMEDIAL_FE
6. AO_REMEDIAL_BE

Jika satu AO bisa FE dan BE, maka user tersebut memiliki dua permission:

1. AO_REMEDIAL_FE
2. AO_REMEDIAL_BE

---


### 16.3 Tabel prospects

Field yang dibutuhkan:

1. id
2. prospect_type
3. customer_name
4. identity_number
5. phone_number
6. address
7. product_interest
8. estimated_amount
9. description
10. branch_id
11. created_by
12. assigned_to
13. assigned_by
14. assigned_at
15. delegation_status
16. status
17. sla_started_at
18. rejected_at
19. reject_reason
20. reject_note
21. closed_at
22. closing_account_number
23. closing_realization_amount
24. closing_note
25. created_at
26. updated_at

Contoh prospect_type:

1. KREDIT
2. TABUNGAN
3. DEPOSITO
4. PEMBELI_ASET
5. DEBITUR_EXISTING

Contoh delegation_status:

1. BELUM_DIDELEGASIKAN
2. SUDAH_DIDELEGASIKAN

Contoh status:

1. OPEN
2. FOLLOW_UP
3. SLA
4. REJECT
5. CLOSING

---

### 16.4 Tabel prospect_follow_ups

Field yang dibutuhkan:

1. id
2. prospect_id
3. follow_up_date
4. method
5. result
6. note
7. next_plan
8. created_by
9. created_at

Contoh method:

1. TELEPON
2. WHATSAPP
3. KUNJUNGAN
4. BERTEMU_DI_KANTOR
5. LAINNYA

---


### 16.5 Tabel prospect_histories

Field yang dibutuhkan:

1. id
2. prospect_id
3. old_status
4. new_status
5. action
6. note
7. created_by
8. created_at

---

### 16.6 Tabel debtors

Field yang dibutuhkan:

1. id
2. debtor_name
3. credit_account_number
4. branch_id
5. overdue_days
6. is_ph
7. outstanding_amount
8. collectability_status
9. address
10. phone_number
11. created_at
12. updated_at

---

### 16.7 Tabel debtor_mappings

Field yang dibutuhkan:

1. id
2. debtor_id
3. branch_id
4. mapping_month
5. mapping_year
6. overdue_days_at_mapping
7. is_ph_at_mapping
8. mapping_category
9. assigned_to
10. assigned_by
11. visit_frequency_target
12. collection_frequency_target
13. mapping_status
14. mapping_note
15. mapped_at
16. is_late_mapping
17. late_mapping_reason
18. created_at
19. updated_at

Contoh mapping_category:

1. AO_KREDIT
2. REMEDIAL_FE
3. REMEDIAL_BE

Contoh mapping_status:

1. BELUM_DIMAPPING
2. SUDAH_DIMAPPING
3. DALAM_PROSES_KUNJUNGAN
4. DALAM_PROSES_PENAGIHAN
5. SELESAI
6. TIDAK_BERHASIL_DIKUNJUNGI
7. TIDAK_BERHASIL_DITAGIH
8. MAPPING_TERLAMBAT

---


### 16.8 Tabel debtor_mapping_activities

Field yang dibutuhkan:

1. id
2. debtor_mapping_id
3. activity_date
4. activity_type
5. activity_method
6. activity_result
7. payment_promise_date
8. payment_promise_amount
9. payment_date
10. payment_amount
11. payment_proof
12. visit_location
13. latitude
14. longitude
15. note
16. next_follow_up_date
17. created_by
18. created_at

Contoh activity_type:

1. KUNJUNGAN
2. PENAGIHAN
3. TELEPON
4. WHATSAPP
5. SURAT
6. LAINNYA

Contoh activity_result:

1. DEBITUR_DITEMUI
2. DEBITUR_TIDAK_DITEMUI
3. JANJI_BAYAR
4. SUDAH_BAYAR
5. BELUM_BISA_BAYAR
6. MENOLAK_BAYAR
7. NOMOR_TIDAK_AKTIF
8. ALAMAT_TIDAK_DITEMUKAN
9. LAINNYA

---

### 16.9 Tabel debtor_mapping_histories

Field yang dibutuhkan:

1. id
2. debtor_mapping_id
3. action
4. old_assigned_to
5. new_assigned_to
6. old_status
7. new_status
8. reason
9. created_by
10. created_at

---


## 17. Acceptance Criteria

Fitur dianggap selesai jika memenuhi kriteria berikut:

### 17.1 Prospek

1. Semua karyawan dapat menginput prospek.
2. Prospek dari AO otomatis terdelegasi ke AO tersebut.
3. Prospek dari non-AO masuk ke daftar tunggu delegasi.
4. Superuser dapat mendelegasikan prospek sesuai jenis prospek.
5. AO Kredit dapat memproses prospek kredit dari Open, Follow Up, SLA, Reject, sampai Closing.
6. Sistem mencatat tanggal masuk SLA untuk prospek kredit.
7. Sistem menghitung durasi dari SLA sampai Closing atau Reject.
8. Closing kredit wajib menginput nomor rekening.
9. Closing kredit wajib menginput nominal realisasi pencairan.
10. AO Dana dapat memproses prospek tabungan dan deposito tanpa SLA.
11. AO Remedial dapat memproses prospek pembeli aset.
12. Prospek debitur existing dapat didelegasikan superuser kepada AO Kredit.
13. Jika debitur existing berminat, status bisa masuk SLA.
14. Jika debitur existing tidak berminat, status bisa langsung Reject.
15. Semua perubahan status tersimpan di history.

---

### 17.2 Mapping Debitur

1. Superuser dapat melakukan mapping debitur.
2. Mapping normal hanya dilakukan tanggal 1 sampai 5 setiap bulan.
3. Sistem dapat mengelompokkan debitur berdasarkan hari menunggak.
4. Debitur 0 sampai 30 hari hanya dapat dimapping ke AO Kredit.
5. Debitur 31 sampai 180 hari hanya dapat dimapping ke AO Remedial FE.
6. Debitur 180 hari ke atas hanya dapat dimapping ke AO Remedial BE.
7. Debitur PH selalu masuk kategori BE.
8. AO yang memiliki akses FE dan BE dapat menerima mapping kategori FE dan BE.
9. Superuser dapat menginput target frekuensi kunjungan dan penagihan.
10. Setelah mapping, pipeline debitur muncul di AO terkait.
11. AO dapat menginput hasil kunjungan atau penagihan.
12. Sistem menghitung jumlah aktivitas yang sudah dilakukan.
13. Sistem menampilkan sisa target kunjungan atau penagihan.
14. Mapping tidak boleh double untuk debitur yang sama pada bulan yang sama.
15. Jika ada perubahan AO, sistem menyimpan history reassignment.

---


## 18. Catatan untuk Programmer

Hal paling penting yang harus dipahami:

1. Prospek adalah data peluang bisnis.
2. Mapping debitur adalah pembagian daftar debitur untuk dikelola AO.
3. Semua karyawan bisa input prospek, tetapi tidak semua bisa memproses prospek.
4. Superuser bertugas mendelegasikan prospek dan melakukan mapping debitur.
5. Jika AO input prospek sendiri, sistem otomatis assign ke AO tersebut.
6. Jika non-AO input prospek, harus menunggu delegasi superuser.
7. SLA hanya berlaku untuk prospek kredit.
8. Saat kredit masuk SLA, data tersebut menjadi pipeline AO Kredit.
9. Closing kredit wajib memiliki rekening dan nominal realisasi.
10. Mapping debitur dilakukan setiap awal bulan, maksimal tanggal 5.
11. Hari menunggak menentukan siapa AO pengelola debitur.
12. Debitur PH selalu masuk ke AO Remedial BE.
13. Satu AO Remedial bisa memiliki akses FE dan BE sekaligus.
14. Semua perubahan penting harus disimpan di history agar dapat diaudit.

---

## 19. Ringkasan Alur Utama

### Prospek

```
Input Prospek → Delegasi → Follow Up → SLA (khusus kredit) → Reject / Closing → History
```

### Mapping Debitur

```
Awal Bulan → Superuser Mapping → Tentukan AO Berdasarkan Hari Menunggak → Masuk Pipeline AO → AO Kunjungan / Penagihan → Input Hasil → Monitoring
```

---

## 20. Konteks Teknis Aplikasi Existing

### 20.1 Arsitektur Saat Ini

Aplikasi sudah memiliki fondasi berikut yang harus dipertahankan:

- **Front Controller**: `index.php` (root) sebagai router halaman
- **API Router**: `api/index.php` dengan pattern `?action=xxx`
- **Autentikasi**: SSO via SIMPEG (`sso_token` cookie)
- **Session Bridge**: Cookie SSO di-bridge ke PHP session
- **UI Framework**: Bootstrap 5.3.2 + FontAwesome 6.4.2
- **Layout**: Mobile-first (max-width 480px) dengan bottom navigation
- **Database**: MySQL via PDO (`api/config/database.php`)
- **Environment**: Custom `.env` loader tanpa Composer

### 20.2 Halaman yang Sudah Ada

Halaman berikut sudah memiliki UI dan perlu diintegrasikan:

| Halaman | Status | Keterangan |
|---------|--------|------------|
| `login.php` | Selesai | Terintegrasi SSO |
| `home.php` | UI Selesai | Dashboard role-based (remedial, kredit, dana) |
| `mapping.php` | UI Selesai | Mapping debitur + ringkasan, perlu integrasi API |
| `nominatif.php` | UI Selesai | Data nominatif kredit |
| `history.php` | UI Selesai | Riwayat kunjungan + raport |
| `profile.php` | UI Selesai | ID Card + pengaturan |
| `janji-bayar.php` | UI Selesai | Follow-up PTP |
| `hapus-buku.php` | UI Selesai | Data debitur PH |
| `kunjungan-create-kal.php` | UI Selesai | Form kunjungan lengkap |
| `kunjungan-detail.php` | UI Selesai | Detail bukti kunjungan |
| `kunjungan-create.php` | Kosong | Belum diimplementasi |

### 20.3 API yang Sudah Diimplementasi

| Action | Status | Keterangan |
|--------|--------|------------|
| `login` | Selesai | Proxy ke SIMPEG SSO |
| `whoami` | Selesai | Validasi token |
| `logout` | Selesai | Clear cookie |
| `get_mapping` | Placeholder | Return dummy data |
| `create_kunjungan` | Placeholder | Belum simpan ke DB |

### 20.4 Yang Perlu Dibangun untuk Issue Ini

1. **API Endpoints Baru**: CRUD prospek, delegasi, mapping debitur, aktivitas
2. **Database Migration**: Tabel baru sesuai Section 16
3. **Halaman Baru**: Input prospek, delegasi, pipeline, monitoring
4. **Modifikasi Halaman Existing**: Integrasi API ke halaman mapping, home
5. **Middleware Permission**: Validasi role/permission per endpoint
6. **Business Logic**: Auto-delegasi, validasi kategori, SLA tracking

---

## 21. Urutan Implementasi yang Disarankan

### Phase 1: Foundation (Database & Permission)
1. Buat migration/init_db untuk tabel `user_permissions`
2. Buat migration untuk tabel `prospects` dan `prospect_histories`
3. Buat migration untuk tabel `prospect_follow_ups`
4. Buat migration untuk tabel `debtors`
5. Buat migration untuk tabel `debtor_mappings`
6. Buat migration untuk tabel `debtor_mapping_activities`
7. Buat migration untuk tabel `debtor_mapping_histories`
8. Implementasi middleware permission check

### Phase 2: Modul Prospek (Backend)
1. API `create_prospect` — Input prospek + auto-delegasi jika AO
2. API `get_prospects` — List prospek (filter by role, status, cabang)
3. API `delegate_prospect` — Superuser delegasi prospek ke AO
4. API `update_prospect_status` — Ubah status (follow up, SLA, reject, closing)
5. API `create_follow_up` — Catat follow up prospek
6. API `close_prospect` — Closing dengan validasi rekening/nominal

### Phase 3: Modul Mapping Debitur (Backend)
1. API `get_debtors_for_mapping` — List debitur + kategori otomatis
2. API `create_mapping` — Mapping debitur ke AO + validasi kategori
3. API `get_ao_pipeline` — Pipeline debitur per AO
4. API `create_activity` — Input hasil kunjungan/penagihan
5. API `reassign_mapping` — Ganti AO + history
6. API `get_mapping_monitoring` — Dashboard superuser

### Phase 4: Frontend Integration
1. Halaman Input Prospek
2. Halaman Delegasi Prospek (Superuser)
3. Halaman Pipeline Kredit (AO Kredit)
4. Halaman Pipeline Dana (AO Dana)
5. Halaman Mapping Debitur (Superuser)
6. Integrasi API ke `mapping.php` existing
7. Halaman Monitoring & Laporan

---

*Dokumen ini adalah panduan utama untuk pengembangan fitur Prospek dan Mapping Debitur pada aplikasi Visitin AO.*
