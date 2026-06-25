# Tabel DPK Untuk Modul Prospek

Jalankan tabel-tabel ini di database `dpk` saja. Database SIMPEG tidak perlu dibuat dari repo ini karena data pegawai sudah tersedia di sistem SIMPEG.

## Tabel Yang Perlu Dibuat

1. `kode_kantor`
   - Master cabang/kantor tujuan prospek.
   - Dipakai untuk dropdown cabang, filter cabang, dan grouping korwil.

2. `menu_access_by_jabatan`
   - Pengaturan akses menu berdasarkan jabatan/cabang.
   - Kolom boolean seperti `can_access_prospek`, `can_input_prospek`, `can_delegate_prospek`, dan lainnya.

3. `prospects`
   - Tabel utama data prospek.
   - Menyimpan produk, data calon nasabah, wilayah, penginput, status delegasi, AO assigned, status prospek, SLA, reject, dan closing.

4. `prospect_follow_ups`
   - Riwayat follow up prospek.
   - Relasi ke `prospects.id`.

5. `prospect_histories`
   - Audit trail aktivitas prospek.
   - Relasi ke `prospects.id`.

6. `prospect_sla_logs`
   - Riwayat tahapan SLA/pipeline kredit.
   - Relasi ke `prospects.id`.

## File SQL

SQL lengkap ada di:

`database/create_tables_prospek.sql`

Untuk menjalankan dari PHP lokal tanpa `mysql` CLI:

```bash
php database/run_migration_dpk.php
```

## SIMPEG

SIMPEG hanya perlu menyediakan data pegawai aktif, jabatan, unit kerja, dan kantor. Query referensi ada di:

`database/query_simpeg_pegawai_aktif.sql`
