-- ============================================
-- CREATE TABLES: E-Prospek Module
-- Database: dpk
-- 
-- Jalankan:
--   mysql -u root -p dpk < database/create_tables_prospek.sql
--
-- Atau copy-paste ke phpMyAdmin / HeidiSQL
-- ============================================

CREATE DATABASE IF NOT EXISTS `dpk` 
  DEFAULT CHARACTER SET utf8mb4 
  COLLATE utf8mb4_unicode_ci;

USE `dpk`;

-- ============================================
-- 1. TABEL: kode_kantor (Master Cabang 000-028)
-- ============================================
CREATE TABLE IF NOT EXISTS `kode_kantor` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `kode_kantor` VARCHAR(5) NOT NULL,
    `nama_kantor` VARCHAR(100) NOT NULL,
    `korwil` VARCHAR(20) NOT NULL DEFAULT 'semarang' COMMENT 'semarang|solo|banyumas|pekalongan|pusat',
    `is_active` TINYINT(1) NOT NULL DEFAULT 1,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY `uk_kode_kantor` (`kode_kantor`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert data cabang (skip jika sudah ada)
INSERT IGNORE INTO `kode_kantor` (`kode_kantor`, `nama_kantor`, `korwil`) VALUES
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
-- 2. TABEL: prospects (Data Prospek Utama)
-- ============================================
CREATE TABLE IF NOT EXISTS `prospects` (
    `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    
    -- Jenis Prospek
    `prospect_type` ENUM('KREDIT','TABUNGAN','DEPOSITO','PEMBELI_ASET','DEBITUR_EXISTING') NOT NULL,
    
    -- Data Nasabah
    `customer_name` VARCHAR(200) NOT NULL,
    `identity_number` VARCHAR(20) DEFAULT NULL,
    `phone_number` VARCHAR(20) NOT NULL,
    
    -- Data Usaha & Produk
    `jenis_usaha` VARCHAR(50) DEFAULT NULL,
    `rekomendasi_produk` ENUM('Tabungan','Deposito','Kredit','Aset') DEFAULT NULL,
    `keterangan_usaha` TEXT DEFAULT NULL,
    
    -- Alamat & Lokasi
    `provinsi` VARCHAR(100) DEFAULT NULL,
    `kab_kota` VARCHAR(100) DEFAULT NULL,
    `kecamatan` VARCHAR(100) DEFAULT NULL,
    `desa` VARCHAR(100) DEFAULT NULL,
    `address` TEXT DEFAULT NULL,
    `latitude` DECIMAL(10,7) DEFAULT NULL,
    `longitude` DECIMAL(10,7) DEFAULT NULL,
    `geo_address` TEXT DEFAULT NULL,
    
    -- Foto
    `foto_url` VARCHAR(500) DEFAULT NULL,
    
    -- Kantor Tujuan
    `kode_kantor` VARCHAR(5) NOT NULL,
    
    -- Keterangan
    `description` TEXT DEFAULT NULL,
    
    -- Tracking
    `created_by` VARCHAR(20) NOT NULL COMMENT 'id_peg penginput',
    `created_by_kode_kantor` VARCHAR(5) NOT NULL,
    `referral_by` VARCHAR(20) DEFAULT NULL COMMENT 'id_peg referral (=created_by)',
    `is_ao_input` TINYINT(1) NOT NULL DEFAULT 0,
    
    -- Delegasi
    `delegation_status` ENUM('BELUM_DIDELEGASIKAN','SUDAH_DIDELEGASIKAN') NOT NULL DEFAULT 'BELUM_DIDELEGASIKAN',
    `assigned_to` VARCHAR(20) DEFAULT NULL,
    `assigned_by` VARCHAR(20) DEFAULT NULL,
    `assigned_at` DATETIME DEFAULT NULL,
    
    -- Status
    `status` ENUM('OPEN','FOLLOW_UP','SLA','REJECT','CLOSING') NOT NULL DEFAULT 'OPEN',
    
    -- SLA (Kredit)
    `sla_started_at` DATETIME DEFAULT NULL,
    `sla_started_by` VARCHAR(20) DEFAULT NULL,
    
    -- Reject
    `rejected_at` DATETIME DEFAULT NULL,
    `reject_reason` VARCHAR(255) DEFAULT NULL,
    `reject_note` TEXT DEFAULT NULL,
    
    -- Closing
    `closed_at` DATETIME DEFAULT NULL,
    `closing_account_number` VARCHAR(30) DEFAULT NULL,
    `closing_realization_amount` BIGINT UNSIGNED DEFAULT NULL,
    `closing_tenor` INT UNSIGNED DEFAULT NULL,
    `closing_note` TEXT DEFAULT NULL,
    `closing_asset_name` VARCHAR(200) DEFAULT NULL,
    `closing_buyer_name` VARCHAR(200) DEFAULT NULL,
    
    -- Timestamps
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
    
    -- Indexes
    INDEX `idx_type` (`prospect_type`),
    INDEX `idx_status` (`status`),
    INDEX `idx_delegation` (`delegation_status`),
    INDEX `idx_kode_kantor` (`kode_kantor`),
    INDEX `idx_created_by` (`created_by`),
    INDEX `idx_assigned_to` (`assigned_to`),
    INDEX `idx_created_at` (`created_at`),
    INDEX `idx_is_ao` (`is_ao_input`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- 3. TABEL: prospect_pipeline (History Status / Pipeline)
--    Setiap kali status berubah, tercatat di sini
-- ============================================
CREATE TABLE IF NOT EXISTS `prospect_pipeline` (
    `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `prospect_id` BIGINT UNSIGNED NOT NULL,
    `action` VARCHAR(50) NOT NULL COMMENT 'CREATED|DELEGATED|STATUS_CHANGED|FOLLOW_UP|CLOSED|REJECTED|SLA_STAGE',
    `old_status` VARCHAR(20) DEFAULT NULL,
    `new_status` VARCHAR(20) DEFAULT NULL,
    `old_assigned_to` VARCHAR(20) DEFAULT NULL,
    `new_assigned_to` VARCHAR(20) DEFAULT NULL,
    `note` TEXT DEFAULT NULL,
    `metadata` JSON DEFAULT NULL,
    `created_by` VARCHAR(20) NOT NULL,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    
    INDEX `idx_prospect_id` (`prospect_id`),
    INDEX `idx_action` (`action`),
    INDEX `idx_created_at` (`created_at`),
    CONSTRAINT `fk_pipeline_prospect` FOREIGN KEY (`prospect_id`) 
        REFERENCES `prospects`(`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- 4. TABEL: prospect_follow_ups (Catatan Follow Up)
-- ============================================
CREATE TABLE IF NOT EXISTS `prospect_follow_ups` (
    `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `prospect_id` BIGINT UNSIGNED NOT NULL,
    `follow_up_date` DATE NOT NULL,
    `method` ENUM('TELEPON','WHATSAPP','KUNJUNGAN','BERTEMU_DI_KANTOR','LAINNYA') NOT NULL,
    `result` TEXT NOT NULL,
    `note` TEXT DEFAULT NULL,
    `next_plan` VARCHAR(255) DEFAULT NULL,
    `next_follow_up_date` DATE DEFAULT NULL,
    `created_by` VARCHAR(20) NOT NULL,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    
    INDEX `idx_prospect_id` (`prospect_id`),
    INDEX `idx_date` (`follow_up_date`),
    CONSTRAINT `fk_followup_prospect` FOREIGN KEY (`prospect_id`) 
        REFERENCES `prospects`(`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- 5. TABEL: prospect_sla_logs (Tracking SLA Kredit per Stage)
-- ============================================
CREATE TABLE IF NOT EXISTS `prospect_sla_logs` (
    `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `prospect_id` BIGINT UNSIGNED NOT NULL,
    `stage` VARCHAR(50) NOT NULL COMMENT 'VERIFIKASI_DATA|SURVEI_JAMINAN|ANALISA_KREDIT|KOMITE_KREDIT|PERSETUJUAN|AKAD_KREDIT|PENCAIRAN',
    `stage_started_at` DATETIME NOT NULL,
    `stage_ended_at` DATETIME DEFAULT NULL,
    `duration_days` INT DEFAULT NULL,
    `note` TEXT DEFAULT NULL,
    `created_by` VARCHAR(20) NOT NULL,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    
    INDEX `idx_prospect_id` (`prospect_id`),
    INDEX `idx_stage` (`stage`),
    CONSTRAINT `fk_sla_prospect` FOREIGN KEY (`prospect_id`) 
        REFERENCES `prospects`(`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- SELESAI! Jalankan dummy_data_dpk.sql untuk sample data.
-- ============================================
