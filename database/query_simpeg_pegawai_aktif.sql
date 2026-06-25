-- ============================================
-- QUERY: Data Pegawai Aktif dari DB SIMPEG
-- Database: masq2971_simpeg_dummy
-- ============================================
-- Query ini digunakan oleh aplikasi untuk:
-- 1. Validasi login (dummy mode: cek apakah id_peg ada)
-- 2. Ambil data profil pegawai (nama, jabatan, cabang, dll)
-- 3. Filter pegawai berdasarkan kode_kantor (untuk delegasi, report)
-- 4. Menentukan level akses berdasarkan unit_kerja / group_jabatan
-- ============================================

-- Query utama: ambil semua pegawai aktif
SELECT
    k.kode_cabang AS kode_kantor,
    j.id_peg AS employee_id,
    p.nama AS full_name,
    p.nip AS nik,
    p.email,
    p.telp,
    j.unit_kerja AS kode_unit_kerja,
    k.nama_kantor AS branch_name,
    mj.nama_unit_kerja AS unit_kerja,
    mj.nama_jabatan AS job_position,
    mj.level,
    mj.group_jabatan
FROM 
    tb_jabatan j
INNER JOIN 
    tb_pegawai p ON j.id_peg = p.id_peg
INNER JOIN 
    tb_master_jabatan mj ON CAST(j.kode_jabatan AS CHAR) = CAST(mj.kode_jabatan AS CHAR)
LEFT JOIN 
    tb_kantor k ON j.unit_kerja = k.kode_kantor_detail
WHERE 
    j.status_jab = 'Aktif';

-- ============================================
-- Query: Pegawai per cabang tertentu
-- Digunakan untuk filter list AO saat delegasi
-- ============================================
-- SELECT ... WHERE j.status_jab = 'Aktif' AND k.kode_cabang = '001';

-- ============================================
-- Query: Pegawai dengan level tertentu (AO, Kabid, dll)
-- ============================================
-- SELECT ... WHERE j.status_jab = 'Aktif' AND mj.group_jabatan IN ('AO Kredit','AO Dana','AO Remedial');

-- ============================================
-- CATATAN MAPPING ROLE DI APLIKASI:
-- ============================================
-- group_jabatan dari SIMPEG -> role di aplikasi:
--   'Staf'              -> staff (non-AO, hanya bisa input prospek)
--   'AO Kredit'         -> ao_kredit
--   'AO Dana'           -> ao_dana  
--   'AO Remedial'       -> ao_remedial (cek FE/BE dari unit_kerja atau permission tambahan)
--   'Pejabat'           -> superuser (jika level = 'Kabid' atau 'Kacab')
--   'Direksi'           -> superuser (report only)
--   'Komisaris'         -> superuser (report only)
--
-- Untuk user Pusat (kode_kantor = '000'):
--   - Divisi Pemasaran       -> bisa lihat semua cabang (report)
--   - Divisi Operasional     -> bisa lihat semua cabang (report)  
--   - Divisi Remedial        -> hanya report remedial
--   - Dewan Komisaris/Direksi -> semua report
--
-- Untuk atasan di cabang (PE = Kabid Pemasaran, PS = Kabid Operasional):
--   - Hanya akses data cabangnya sendiri
-- ============================================
