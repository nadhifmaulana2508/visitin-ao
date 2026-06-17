-- ============================================
-- MIGRATION: Database DPK (Data Prospek & Kunjungan)
-- Jalankan di database: dpk
-- ============================================

-- Buat database jika belum ada
CREATE DATABASE IF NOT EXISTS `dpk` 
  DEFAULT CHARACTER SET utf8mb4 
  COLLATE utf8mb4_unicode_ci;

USE `dpk`;

-- ============================================
-- TABEL: kode_kantor
-- Daftar cabang 000-028 + korwil grouping
-- ============================================
DROP TABLE IF EXISTS `kode_kantor`;
CREATE TABLE `kode_kantor` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `kode_kantor` VARCHAR(5) NOT NULL,
    `nama_kantor` VARCHAR(100) NOT NULL,
    `korwil` VARCHAR(20) NOT NULL DEFAULT 'semarang' COMMENT 'semarang|solo|banyumas|pekalongan|pusat',
    `is_active` TINYINT(1) NOT NULL DEFAULT 1,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY `uk_kode_kantor` (`kode_kantor`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Data cabang 000-028
INSERT INTO `kode_kantor` (`kode_kantor`, `nama_kantor`, `korwil`) VALUES
('000', 'Pusat', 'pusat'),
('001', 'Kc. Utama', 'semarang'),
('002', 'Kc. Rembang', 'semarang'),
('003', 'Kc. Pati', 'semarang'),
('004', 'Kc. Demak', 'semarang'),
('005', 'Kc. Kendal', 'semarang'),
('006', 'Kc. Salatiga', 'semarang'),
('007', 'Kc. Kab. Semarang', 'semarang'),
('008', 'Kc. Wonogiri', 'solo'),
('009', 'Kc. Kota Surakarta', 'solo'),
('010', 'Kc. Karanganyar', 'solo'),
('011', 'Kc. Sukoharjo', 'solo'),
('012', 'Kc. Sragen', 'solo'),
('013', 'Kc. Boyolali', 'solo'),
('014', 'Kc. Magelang', 'solo'),
('015', 'Kc. Wonosobo', 'banyumas'),
('016', 'Kc. Purworejo', 'banyumas'),
('017', 'Kc. Kebumen', 'banyumas'),
('018', 'Kc. Banjarnegara', 'banyumas'),
('019', 'Kc. Purbalingga', 'banyumas'),
('020', 'Kc. Banyumas', 'banyumas'),
('021', 'Kc. Cilacap', 'banyumas'),
('022', 'Kc. Kab. Tegal', 'pekalongan'),
('023', 'Kc. Brebes', 'pekalongan'),
('024', 'Kc. Kota Tegal', 'pekalongan'),
('025', 'Kc. Pemalang', 'pekalongan'),
('026', 'Kc. Kota Pekalongan', 'pekalongan'),
('027', 'Kc. Kab. Pekalongan', 'pekalongan'),
('028', 'Kc. Batang', 'pekalongan');

-- ============================================
-- TABEL: prospects
-- Data prospek bisnis (kredit, tabungan, deposito, pembeli aset, debitur existing)
-- ============================================
DROP TABLE IF EXISTS `prospects`;
CREATE TABLE `prospects` (
    `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    
    -- Jenis & Data Nasabah
    `prospect_type` ENUM('KREDIT','TABUNGAN','DEPOSITO','PEMBELI_ASET','DEBITUR_EXISTING') NOT NULL,
    `customer_name` VARCHAR(200) NOT NULL,
    `identity_number` VARCHAR(20) DEFAULT NULL COMMENT 'No KTP/identitas',
    `phone_number` VARCHAR(20) NOT NULL,
    
    -- Data Usaha & Produk
    `jenis_usaha` VARCHAR(50) DEFAULT NULL COMMENT 'Pertanian|Perikanan|Peternakan|Perdagangan|Jasa|Industri Rumahan|Karyawan|Wiraswasta|Lainnya',
    `rekomendasi_produk` ENUM('Tabungan','Deposito','Kredit','Aset') DEFAULT NULL COMMENT 'Dropdown produk rekomendasi',
    `keterangan_usaha` TEXT DEFAULT NULL COMMENT 'Deskripsi usaha calon nasabah',
    
    -- Alamat
    `provinsi` VARCHAR(100) DEFAULT NULL,
    `kab_kota` VARCHAR(100) DEFAULT NULL,
    `kecamatan` VARCHAR(100) DEFAULT NULL,
    `desa` VARCHAR(100) DEFAULT NULL,
    `address` TEXT DEFAULT NULL COMMENT 'Alamat lengkap (jalan, RT/RW, dll)',
    
    -- Geotagging
    `latitude` DECIMAL(10,7) DEFAULT NULL,
    `longitude` DECIMAL(10,7) DEFAULT NULL,
    `geo_address` TEXT DEFAULT NULL COMMENT 'Alamat hasil reverse geocoding',
    
    -- Foto
    `foto_url` VARCHAR(500) DEFAULT NULL COMMENT 'Path file foto prospek',
    
    -- Kantor tujuan (cabang 001-028)
    `kode_kantor` VARCHAR(5) NOT NULL COMMENT 'Cabang tujuan prospek',
    
    -- Keterangan
    `description` TEXT DEFAULT NULL,
    
    -- Tracking Input & Referral
    `created_by` VARCHAR(20) NOT NULL COMMENT 'id_peg yang input (referral)',
    `created_by_kode_kantor` VARCHAR(5) NOT NULL COMMENT 'Kantor penginput saat input',
    `referral_by` VARCHAR(20) DEFAULT NULL COMMENT 'id_peg referal (sama dgn created_by, eksplisit untuk reporting)',
    `is_ao_input` TINYINT(1) NOT NULL DEFAULT 0 COMMENT '1=input oleh AO, 0=non-AO',
    
    -- Delegasi
    `delegation_status` ENUM('BELUM_DIDELEGASIKAN','SUDAH_DIDELEGASIKAN') NOT NULL DEFAULT 'BELUM_DIDELEGASIKAN',
    `assigned_to` VARCHAR(20) DEFAULT NULL COMMENT 'id_peg AO yang menerima delegasi',
    `assigned_by` VARCHAR(20) DEFAULT NULL COMMENT 'id_peg superuser yang mendelegasikan',
    `assigned_at` DATETIME DEFAULT NULL,
    
    -- Status Prospek
    `status` ENUM('OPEN','FOLLOW_UP','SLA','REJECT','CLOSING') NOT NULL DEFAULT 'OPEN',
    
    -- SLA Tracking (khusus KREDIT)
    `sla_started_at` DATETIME DEFAULT NULL COMMENT 'Waktu masuk status SLA',
    `sla_started_by` VARCHAR(20) DEFAULT NULL COMMENT 'id_peg yang ubah ke SLA',
    
    -- Reject
    `rejected_at` DATETIME DEFAULT NULL,
    `reject_reason` VARCHAR(255) DEFAULT NULL,
    `reject_note` TEXT DEFAULT NULL,
    
    -- Closing
    `closed_at` DATETIME DEFAULT NULL,
    `closing_account_number` VARCHAR(30) DEFAULT NULL COMMENT 'Nomor rekening realisasi',
    `closing_realization_amount` BIGINT UNSIGNED DEFAULT NULL COMMENT 'Nominal realisasi (Rp)',
    `closing_tenor` INT UNSIGNED DEFAULT NULL COMMENT 'Jangka waktu (deposito/kredit)',
    `closing_note` TEXT DEFAULT NULL,
    `closing_asset_name` VARCHAR(200) DEFAULT NULL COMMENT 'Objek aset (pembeli aset)',
    `closing_buyer_name` VARCHAR(200) DEFAULT NULL COMMENT 'Nama pembeli (pembeli aset)',
    
    -- Timestamp
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
    
    -- Indexes
    INDEX `idx_prospect_type` (`prospect_type`),
    INDEX `idx_status` (`status`),
    INDEX `idx_delegation` (`delegation_status`),
    INDEX `idx_kode_kantor` (`kode_kantor`),
    INDEX `idx_created_by` (`created_by`),
    INDEX `idx_assigned_to` (`assigned_to`),
    INDEX `idx_created_at` (`created_at`),
    INDEX `idx_closed_at` (`closed_at`),
    INDEX `idx_is_ao_input` (`is_ao_input`),
    INDEX `idx_sla_started_at` (`sla_started_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- TABEL: prospect_follow_ups
-- Catatan follow up per prospek
-- ============================================
DROP TABLE IF EXISTS `prospect_follow_ups`;
CREATE TABLE `prospect_follow_ups` (
    `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `prospect_id` BIGINT UNSIGNED NOT NULL,
    `follow_up_date` DATE NOT NULL,
    `method` ENUM('TELEPON','WHATSAPP','KUNJUNGAN','BERTEMU_DI_KANTOR','LAINNYA') NOT NULL,
    `result` TEXT NOT NULL COMMENT 'Hasil follow up',
    `note` TEXT DEFAULT NULL,
    `next_plan` VARCHAR(255) DEFAULT NULL COMMENT 'Rencana tindak lanjut',
    `next_follow_up_date` DATE DEFAULT NULL,
    `created_by` VARCHAR(20) NOT NULL,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    
    INDEX `idx_prospect_id` (`prospect_id`),
    INDEX `idx_follow_up_date` (`follow_up_date`),
    CONSTRAINT `fk_followup_prospect` FOREIGN KEY (`prospect_id`) 
        REFERENCES `prospects`(`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- TABEL: prospect_histories
-- Audit trail setiap perubahan status prospek
-- ============================================
DROP TABLE IF EXISTS `prospect_histories`;
CREATE TABLE `prospect_histories` (
    `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `prospect_id` BIGINT UNSIGNED NOT NULL,
    `action` VARCHAR(50) NOT NULL COMMENT 'CREATED|DELEGATED|STATUS_CHANGED|FOLLOW_UP|CLOSED|REJECTED',
    `old_status` VARCHAR(20) DEFAULT NULL,
    `new_status` VARCHAR(20) DEFAULT NULL,
    `old_assigned_to` VARCHAR(20) DEFAULT NULL,
    `new_assigned_to` VARCHAR(20) DEFAULT NULL,
    `note` TEXT DEFAULT NULL,
    `metadata` JSON DEFAULT NULL COMMENT 'Data tambahan (nominal closing, alasan reject, dll)',
    `created_by` VARCHAR(20) NOT NULL,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    
    INDEX `idx_prospect_id` (`prospect_id`),
    INDEX `idx_action` (`action`),
    INDEX `idx_created_at` (`created_at`),
    CONSTRAINT `fk_history_prospect` FOREIGN KEY (`prospect_id`) 
        REFERENCES `prospects`(`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- TABEL: prospect_sla_logs
-- Tracking detail proses SLA kredit (durasi tiap tahap)
-- ============================================
DROP TABLE IF EXISTS `prospect_sla_logs`;
CREATE TABLE `prospect_sla_logs` (
    `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `prospect_id` BIGINT UNSIGNED NOT NULL,
    `stage` VARCHAR(50) NOT NULL COMMENT 'VERIFIKASI|ANALISA|KOMITE|PENCAIRAN|dll',
    `stage_started_at` DATETIME NOT NULL,
    `stage_ended_at` DATETIME DEFAULT NULL,
    `duration_days` INT DEFAULT NULL COMMENT 'Durasi dalam hari (auto-calculated)',
    `note` TEXT DEFAULT NULL,
    `created_by` VARCHAR(20) NOT NULL,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    
    INDEX `idx_prospect_id` (`prospect_id`),
    INDEX `idx_stage` (`stage`),
    CONSTRAINT `fk_sla_prospect` FOREIGN KEY (`prospect_id`) 
        REFERENCES `prospects`(`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
