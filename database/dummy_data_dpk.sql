-- ============================================
-- DUMMY DATA: Database DPK
-- Jalankan SETELAH migration_dpk.sql
-- ============================================

USE `dpk`;

-- ============================================
-- DUMMY: prospects (7 data sample)
-- ============================================
INSERT INTO `prospects` (
    `prospect_type`, `customer_name`, `identity_number`, `phone_number`,
    `product_interest`, `estimated_amount`, `provinsi`, `kab_kota`, `kecamatan`, `desa`,
    `address`, `latitude`, `longitude`, `kode_kantor`,
    `description`, `created_by`, `created_by_kode_kantor`, `is_ao_input`,
    `delegation_status`, `assigned_to`, `assigned_by`, `assigned_at`,
    `status`, `sla_started_at`, `sla_started_by`,
    `closed_at`, `closing_account_number`, `closing_realization_amount`,
    `rejected_at`, `reject_reason`, `reject_note`,
    `created_at`
) VALUES

-- 1. Kredit - AO input - sudah SLA (pipeline kredit)
('KREDIT', 'Bapak Ahmad Sudirman', '3374012345670001', '081234567890',
 'Kredit Modal Kerja', 150000000, 'JAWA TENGAH', 'KOTA SEMARANG', 'Semarang Tengah', 'Pendrikan Kidul',
 'Jl. Pandanaran No. 45 RT 03/RW 02', -6.9830000, 110.4190000, '001',
 'Pemilik toko material, butuh modal untuk ekspansi usaha baru di cabang kedua.',
 '201-001', '001', 1,
 'SUDAH_DIDELEGASIKAN', '201-001', NULL, '2026-06-10 09:15:00',
 'SLA', '2026-06-12 10:00:00', '201-001',
 NULL, NULL, NULL,
 NULL, NULL, NULL,
 '2026-06-10 09:15:00'),

-- 2. Tabungan - Non-AO input - belum didelegasikan
('TABUNGAN', 'Ibu Rina Wati', NULL, '081298765432',
 'Tabungan Rencana', 5000000, 'JAWA TENGAH', 'KOTA SEMARANG', 'Semarang Barat', 'Krobokan',
 'Perumahan Griya Asri Blok D-12', -6.9750000, 110.3950000, '001',
 'Ibu rumah tangga, tertarik menabung rutin untuk dana pendidikan anak.',
 '201-005', '001', 0,
 'BELUM_DIDELEGASIKAN', NULL, NULL, NULL,
 'OPEN', NULL, NULL,
 NULL, NULL, NULL,
 NULL, NULL, NULL,
 '2026-06-12 14:30:00'),

-- 3. Kredit - Non-AO input - sudah didelegasikan, status Follow Up
('KREDIT', 'PT Maju Jaya Sentosa', NULL, '081345678901',
 'Kredit Investasi', 500000000, 'JAWA TENGAH', 'KOTA SEMARANG', 'Semarang Utara', 'Bandarharjo',
 'Kawasan Industri Terboyo Blok A-5', -6.9550000, 110.4450000, '001',
 'Pengembangan pabrik garmen, butuh mesin baru untuk ekspansi produksi.',
 '201-006', '001', 0,
 'SUDAH_DIDELEGASIKAN', '201-001', '201-004', '2026-06-09 08:30:00',
 'FOLLOW_UP', NULL, NULL,
 NULL, NULL, NULL,
 NULL, NULL, NULL,
 '2026-06-08 10:00:00'),

-- 4. Deposito - AO Dana input - sudah closing
('DEPOSITO', 'H. Mochtar Abdullah', '3374056789010002', '081456789012',
 'Deposito 12 Bulan', 200000000, 'JAWA TENGAH', 'KAB. REMBANG', 'Rembang', 'Leteh',
 'Jl. Diponegoro No. 88 Rembang', -6.7070000, 111.3460000, '002',
 'Dana pensiunan, cari yang aman dan stabil.',
 '201-002', '001', 1,
 'SUDAH_DIDELEGASIKAN', '201-002', NULL, '2026-06-05 08:00:00',
 'CLOSING', NULL, NULL,
 '2026-06-15 09:00:00', '0012345678', 200000000,
 NULL, NULL, NULL,
 '2026-06-05 08:00:00'),

-- 5. Pembeli Aset - Non-AO input - belum didelegasikan
('PEMBELI_ASET', 'CV Berkah Abadi', NULL, '081567890123',
 'Tanah Jaminan Eks Kredit Macet', 350000000, 'JAWA TENGAH', 'KAB. REMBANG', 'Kaliori', 'Babadan',
 'Jl. Raya Kaliori KM 5', -6.7200000, 111.3000000, '002',
 'Tertarik beli tanah eks jaminan kredit macet di area Kaliori.',
 '201-005', '001', 0,
 'BELUM_DIDELEGASIKAN', NULL, NULL, NULL,
 'OPEN', NULL, NULL,
 NULL, NULL, NULL,
 NULL, NULL, NULL,
 '2026-06-14 11:20:00'),

-- 6. Debitur Existing - AO Kredit input - Follow Up
('DEBITUR_EXISTING', 'Bapak Supriyadi', '3374019876540003', '081678901234',
 'Top-Up Kredit Modal Kerja', 75000000, 'JAWA TENGAH', 'KOTA SEMARANG', 'Semarang Tengah', 'Pandansari',
 'Jl. Pemuda No. 12', -6.9850000, 110.4200000, '001',
 'Debitur lancar 2 tahun, track record bagus, mau tambah plafon untuk stok barang.',
 '201-001', '001', 1,
 'SUDAH_DIDELEGASIKAN', '201-001', NULL, '2026-06-13 16:00:00',
 'FOLLOW_UP', NULL, NULL,
 NULL, NULL, NULL,
 NULL, NULL, NULL,
 '2026-06-13 16:00:00'),

-- 7. Kredit - Non-AO input - sudah Reject
('KREDIT', 'Ibu Siti Aminah', '3374023456780004', '081789012345',
 'Kredit Multiguna', 50000000, 'JAWA TENGAH', 'KAB. REMBANG', 'Sumber', 'Krikilan',
 'Desa Krikilan RT 01/RW 03', -6.7400000, 111.3800000, '002',
 'Untuk renovasi rumah dan biaya sekolah anak.',
 '201-006', '001', 0,
 'SUDAH_DIDELEGASIKAN', '201-001', '201-004', '2026-06-03 09:00:00',
 'REJECT', NULL, NULL,
 NULL, NULL, NULL,
 '2026-06-10 14:00:00', 'Data tidak memenuhi syarat', 'BI Checking buruk, scoring tidak lolos minimal.',
 '2026-06-02 09:00:00'),

-- 8. Kredit dari cabang lain (Pusat input ke cabang 003)
('KREDIT', 'Bapak Haryanto', NULL, '081890123456',
 'Kredit Multiguna', 100000000, 'JAWA TENGAH', 'KAB. PATI', 'Pati', 'Pati Kidul',
 'Jl. Sudirman No. 55 Pati', -6.7500000, 111.0400000, '003',
 'Kerabat pegawai pusat, butuh kredit untuk usaha warung makan.',
 '102-119', '000', 0,
 'BELUM_DIDELEGASIKAN', NULL, NULL, NULL,
 'OPEN', NULL, NULL,
 NULL, NULL, NULL,
 NULL, NULL, NULL,
 '2026-06-15 10:30:00');

-- ============================================
-- DUMMY: prospect_follow_ups
-- ============================================
INSERT INTO `prospect_follow_ups` (
    `prospect_id`, `follow_up_date`, `method`, `result`, `note`, `next_plan`, `created_by`, `created_at`
) VALUES
(1, '2026-06-11', 'TELEPON', 'Nasabah tertarik, minta detail syarat pengajuan KMK.', 'Sudah kirim brosur via WA.', 'Jadwalkan kunjungan minggu depan', '201-001', '2026-06-11 10:30:00'),
(1, '2026-06-12', 'KUNJUNGAN', 'Bertemu langsung, nasabah siap ajukan. Dokumen sudah dikumpulkan.', 'Berkas lengkap, siap proses.', 'Input ke SLA', '201-001', '2026-06-12 09:45:00'),
(3, '2026-06-10', 'TELEPON', 'Direktur PT belum bisa ditemui, dijadwalkan ulang.', NULL, 'Hubungi lagi besok', '201-001', '2026-06-10 14:00:00'),
(3, '2026-06-11', 'WHATSAPP', 'Chat dg sekretaris, dijadwalkan meeting Jumat.', 'Sudah kirim company profile bank.', 'Meeting Jumat siang', '201-001', '2026-06-11 08:30:00'),
(6, '2026-06-14', 'KUNJUNGAN', 'Ketemu di toko, nasabah mau top-up. Tanya nominal aman berapa.', 'Skor BI Checking bersih.', 'Buat simulasi plafon baru', '201-001', '2026-06-14 11:00:00'),
(7, '2026-06-05', 'TELEPON', 'Nasabah minta proses dipercepat karena butuh dana.', NULL, 'Proses BI Checking', '201-001', '2026-06-05 09:00:00');

-- ============================================
-- DUMMY: prospect_histories
-- ============================================
INSERT INTO `prospect_histories` (
    `prospect_id`, `action`, `old_status`, `new_status`, 
    `old_assigned_to`, `new_assigned_to`, `note`, `metadata`, `created_by`, `created_at`
) VALUES
-- Prospek 1: Created -> Follow Up -> SLA
(1, 'CREATED', NULL, 'OPEN', NULL, '201-001', 'Prospek kredit dibuat oleh AO Kredit (auto-delegasi)', NULL, '201-001', '2026-06-10 09:15:00'),
(1, 'STATUS_CHANGED', 'OPEN', 'FOLLOW_UP', NULL, NULL, 'Mulai follow up nasabah', NULL, '201-001', '2026-06-11 10:30:00'),
(1, 'STATUS_CHANGED', 'FOLLOW_UP', 'SLA', NULL, NULL, 'Nasabah berminat, masuk proses kredit', '{"sla_started_at":"2026-06-12 10:00:00"}', '201-001', '2026-06-12 10:00:00'),

-- Prospek 2: Created (belum ada action lagi)
(2, 'CREATED', NULL, 'OPEN', NULL, NULL, 'Prospek tabungan dibuat oleh Teller (menunggu delegasi)', NULL, '201-005', '2026-06-12 14:30:00'),

-- Prospek 3: Created -> Delegated -> Follow Up
(3, 'CREATED', NULL, 'OPEN', NULL, NULL, 'Prospek kredit dibuat oleh CS', NULL, '201-006', '2026-06-08 10:00:00'),
(3, 'DELEGATED', NULL, NULL, NULL, '201-001', 'Didelegasikan ke AO Kredit oleh Kabid Pemasaran', NULL, '201-004', '2026-06-09 08:30:00'),
(3, 'STATUS_CHANGED', 'OPEN', 'FOLLOW_UP', NULL, NULL, 'Mulai follow up', NULL, '201-001', '2026-06-10 14:00:00'),

-- Prospek 4: Created -> Closing
(4, 'CREATED', NULL, 'OPEN', NULL, '201-002', 'Prospek deposito dibuat oleh AO Dana (auto-delegasi)', NULL, '201-002', '2026-06-05 08:00:00'),
(4, 'STATUS_CHANGED', 'OPEN', 'FOLLOW_UP', NULL, NULL, 'Follow up via telepon', NULL, '201-002', '2026-06-07 10:00:00'),
(4, 'CLOSED', 'FOLLOW_UP', 'CLOSING', NULL, NULL, 'Closing deposito berhasil', '{"account_number":"0012345678","amount":200000000,"tenor":12}', '201-002', '2026-06-15 09:00:00'),

-- Prospek 7: Created -> Delegated -> Follow Up -> Reject
(7, 'CREATED', NULL, 'OPEN', NULL, NULL, 'Prospek kredit dibuat oleh CS', NULL, '201-006', '2026-06-02 09:00:00'),
(7, 'DELEGATED', NULL, NULL, NULL, '201-001', 'Didelegasikan ke AO Kredit', NULL, '201-004', '2026-06-03 09:00:00'),
(7, 'STATUS_CHANGED', 'OPEN', 'FOLLOW_UP', NULL, NULL, 'Mulai follow up', NULL, '201-001', '2026-06-05 09:00:00'),
(7, 'REJECTED', 'FOLLOW_UP', 'REJECT', NULL, NULL, 'Data tidak memenuhi syarat', '{"reason":"BI Checking buruk, scoring tidak lolos minimal."}', '201-001', '2026-06-10 14:00:00');

-- ============================================
-- DUMMY: prospect_sla_logs (untuk prospek #1 yang sudah SLA)
-- ============================================
INSERT INTO `prospect_sla_logs` (
    `prospect_id`, `stage`, `stage_started_at`, `stage_ended_at`, `duration_days`, `note`, `created_by`, `created_at`
) VALUES
(1, 'VERIFIKASI_DATA', '2026-06-12 10:00:00', '2026-06-13 15:00:00', 1, 'Verifikasi kelengkapan berkas nasabah', '201-001', '2026-06-12 10:00:00'),
(1, 'SURVEI_JAMINAN', '2026-06-13 15:00:00', '2026-06-15 10:00:00', 2, 'Survei lokasi jaminan di Pendrikan', '201-001', '2026-06-13 15:00:00'),
(1, 'ANALISA_KREDIT', '2026-06-15 10:00:00', NULL, NULL, 'Sedang dalam proses analisa', '201-001', '2026-06-15 10:00:00');
