-- ============================================
-- DPK DATABASE: Prospek + menu access
-- Jalankan: mysql -u root -p < database/create_tables_prospek.sql
-- ============================================

CREATE DATABASE IF NOT EXISTS `dpk`
  DEFAULT CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE `dpk`;

CREATE TABLE IF NOT EXISTS `kode_kantor` (
    `kode_kantor` VARCHAR(5) PRIMARY KEY,
    `nama_kantor` VARCHAR(120) NOT NULL,
    `korwil` ENUM('pusat','semarang','solo','banyumas','pekalongan') NOT NULL,
    `is_active` TINYINT(1) NOT NULL DEFAULT 1,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `kode_kantor` (`kode_kantor`, `nama_kantor`, `korwil`) VALUES
('000','Kantor Pusat','pusat'),
('001','Kc. Utama','semarang'),
('002','Kc. Rembang','semarang'),
('003','Kc. Pati','semarang'),
('004','Kc. Demak','semarang'),
('005','Kc. Kendal','semarang'),
('006','Kc. Salatiga','semarang'),
('007','Kc. Kab. Semarang','semarang'),
('008','Kc. Wonogiri','solo'),
('009','Kc. Kota Surakarta','solo'),
('010','Kc. Karanganyar','solo'),
('011','Kc. Sukoharjo','solo'),
('012','Kc. Sragen','solo'),
('013','Kc. Boyolali','solo'),
('014','Kc. Magelang','solo'),
('015','Kc. Wonosobo','banyumas'),
('016','Kc. Purworejo','banyumas'),
('017','Kc. Kebumen','banyumas'),
('018','Kc. Banjarnegara','banyumas'),
('019','Kc. Purbalingga','banyumas'),
('020','Kc. Banyumas','banyumas'),
('021','Kc. Cilacap','banyumas'),
('022','Kc. Kab. Tegal','pekalongan'),
('023','Kc. Brebes','pekalongan'),
('024','Kc. Kota Tegal','pekalongan'),
('025','Kc. Pemalang','pekalongan'),
('026','Kc. Kota Pekalongan','pekalongan'),
('027','Kc. Kab. Pekalongan','pekalongan'),
('028','Kc. Batang','pekalongan')
ON DUPLICATE KEY UPDATE
    `nama_kantor` = VALUES(`nama_kantor`),
    `korwil` = VALUES(`korwil`),
    `is_active` = 1;

CREATE TABLE IF NOT EXISTS `menu_access_by_jabatan` (
    `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `kode_kantor` VARCHAR(5) NOT NULL DEFAULT 'all',
    `job_position` VARCHAR(150) NOT NULL,
    `group_jabatan` VARCHAR(80) DEFAULT NULL,
    `can_access_prospek` TINYINT(1) NOT NULL DEFAULT 0,
    `can_input_prospek` TINYINT(1) NOT NULL DEFAULT 0,
    `can_delegate_prospek` TINYINT(1) NOT NULL DEFAULT 0,
    `can_view_report_prospek` TINYINT(1) NOT NULL DEFAULT 0,
    `can_access_mapping` TINYINT(1) NOT NULL DEFAULT 0,
    `can_access_nominatif` TINYINT(1) NOT NULL DEFAULT 0,
    `can_access_history` TINYINT(1) NOT NULL DEFAULT 1,
    `can_access_profile` TINYINT(1) NOT NULL DEFAULT 1,
    `is_active` TINYINT(1) NOT NULL DEFAULT 1,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY `uq_menu_access_job_branch` (`kode_kantor`, `job_position`),
    INDEX `idx_menu_group` (`group_jabatan`),
    INDEX `idx_menu_active` (`is_active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `menu_access_by_jabatan`
(`kode_kantor`, `job_position`, `group_jabatan`, `can_access_prospek`, `can_input_prospek`, `can_delegate_prospek`, `can_view_report_prospek`, `can_access_mapping`, `can_access_nominatif`, `can_access_history`, `can_access_profile`)
VALUES
('all','Staf Sistem dan Jaringan TI','Staf',1,1,1,1,1,1,1,1),
('all','Account Officer Kredit','AO Kredit',1,1,0,0,1,1,1,1),
('all','Account Officer Dana','AO Dana',1,1,0,0,0,0,1,1),
('all','Account Officer Remedial','AO Remedial',1,1,0,0,1,1,1,1),
('all','Kepala Bidang Pemasaran','Pejabat',1,1,1,1,1,1,1,1),
('all','Teller','Staf',1,1,0,0,0,0,1,1),
('all','Customer Service','Staf',1,1,0,0,0,0,1,1)
ON DUPLICATE KEY UPDATE
    `group_jabatan` = VALUES(`group_jabatan`),
    `can_access_prospek` = VALUES(`can_access_prospek`),
    `can_input_prospek` = VALUES(`can_input_prospek`),
    `can_delegate_prospek` = VALUES(`can_delegate_prospek`),
    `can_view_report_prospek` = VALUES(`can_view_report_prospek`),
    `can_access_mapping` = VALUES(`can_access_mapping`),
    `can_access_nominatif` = VALUES(`can_access_nominatif`),
    `can_access_history` = VALUES(`can_access_history`),
    `can_access_profile` = VALUES(`can_access_profile`),
    `is_active` = 1;

CREATE TABLE IF NOT EXISTS `prospects` (
    `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `prospect_type` ENUM('KREDIT','TABUNGAN','DEPOSITO','PEMBELI_ASET','DEBITUR_EXISTING') NOT NULL,
    `customer_name` VARCHAR(200) NOT NULL,
    `identity_number` VARCHAR(20) DEFAULT NULL,
    `phone_number` VARCHAR(20) NOT NULL,
    `jenis_usaha` VARCHAR(50) DEFAULT NULL,
    `rekomendasi_produk` ENUM('Tabungan','Deposito','Kredit','Aset') DEFAULT NULL,
    `keterangan_usaha` TEXT DEFAULT NULL,
    `provinsi` VARCHAR(100) DEFAULT NULL,
    `kab_kota` VARCHAR(100) DEFAULT NULL,
    `kecamatan` VARCHAR(100) DEFAULT NULL,
    `desa` VARCHAR(100) DEFAULT NULL,
    `address` TEXT DEFAULT NULL,
    `latitude` DECIMAL(10,7) DEFAULT NULL,
    `longitude` DECIMAL(10,7) DEFAULT NULL,
    `geo_address` TEXT DEFAULT NULL,
    `foto_url` VARCHAR(500) DEFAULT NULL,
    `kode_kantor` VARCHAR(5) NOT NULL,
    `description` TEXT DEFAULT NULL,
    `created_by` VARCHAR(20) NOT NULL,
    `created_by_kode_kantor` VARCHAR(5) NOT NULL,
    `referral_by` VARCHAR(20) DEFAULT NULL,
    `is_ao_input` TINYINT(1) NOT NULL DEFAULT 0,
    `delegation_status` ENUM('BELUM_DIDELEGASIKAN','SUDAH_DIDELEGASIKAN') NOT NULL DEFAULT 'BELUM_DIDELEGASIKAN',
    `assigned_to` VARCHAR(20) DEFAULT NULL,
    `assigned_by` VARCHAR(20) DEFAULT NULL,
    `assigned_at` DATETIME DEFAULT NULL,
    `status` ENUM('OPEN','FOLLOW_UP','SLA','REJECT','CLOSING') NOT NULL DEFAULT 'OPEN',
    `sla_started_at` DATETIME DEFAULT NULL,
    `sla_started_by` VARCHAR(20) DEFAULT NULL,
    `rejected_at` DATETIME DEFAULT NULL,
    `reject_reason` VARCHAR(255) DEFAULT NULL,
    `reject_note` TEXT DEFAULT NULL,
    `closed_at` DATETIME DEFAULT NULL,
    `closing_account_number` VARCHAR(30) DEFAULT NULL,
    `closing_realization_amount` BIGINT UNSIGNED DEFAULT NULL,
    `closing_tenor` INT UNSIGNED DEFAULT NULL,
    `closing_note` TEXT DEFAULT NULL,
    `closing_asset_name` VARCHAR(200) DEFAULT NULL,
    `closing_buyer_name` VARCHAR(200) DEFAULT NULL,
    `closing_asset_purchase_method` ENUM('LELANG','CESSIE','LAINNYA') DEFAULT NULL,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_type` (`prospect_type`),
    INDEX `idx_status` (`status`),
    INDEX `idx_delegation` (`delegation_status`),
    INDEX `idx_kode_kantor` (`kode_kantor`),
    INDEX `idx_created_by` (`created_by`),
    INDEX `idx_assigned_to` (`assigned_to`),
    INDEX `idx_created_at` (`created_at`),
    INDEX `idx_is_ao` (`is_ao_input`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

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
    INDEX `idx_fu_prospect` (`prospect_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `prospect_histories` (
    `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `prospect_id` BIGINT UNSIGNED NOT NULL,
    `action` VARCHAR(50) NOT NULL,
    `old_status` VARCHAR(30) DEFAULT NULL,
    `new_status` VARCHAR(30) DEFAULT NULL,
    `old_assigned_to` VARCHAR(20) DEFAULT NULL,
    `new_assigned_to` VARCHAR(20) DEFAULT NULL,
    `note` TEXT DEFAULT NULL,
    `metadata` JSON DEFAULT NULL,
    `created_by` VARCHAR(20) NOT NULL,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_hist_prospect` (`prospect_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `prospect_sla_logs` (
    `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `prospect_id` BIGINT UNSIGNED NOT NULL,
    `stage` ENUM('VERIFIKASI_DATA','SURVEI_JAMINAN','ANALISA_KREDIT','KOMITE_KREDIT','PERSETUJUAN','AKAD_KREDIT','PENCAIRAN') NOT NULL,
    `stage_started_at` DATETIME NOT NULL,
    `stage_ended_at` DATETIME DEFAULT NULL,
    `duration_days` INT UNSIGNED DEFAULT NULL,
    `note` TEXT DEFAULT NULL,
    `created_by` VARCHAR(20) NOT NULL,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_sla_prospect` (`prospect_id`),
    INDEX `idx_sla_stage` (`stage`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `prospect_credit_pipelines` (
    `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `prospect_id` BIGINT UNSIGNED NOT NULL,
    `assigned_to` VARCHAR(20) DEFAULT NULL,
    `requested_loan_amount` BIGINT UNSIGNED DEFAULT NULL,
    `confirmation_at` DATETIME NOT NULL,
    `documents_completed_at` DATETIME DEFAULT NULL,
    `sla_started_at` DATETIME DEFAULT NULL,
    `sla_deadline_at` DATETIME DEFAULT NULL,
    `current_stage` ENUM('FORMULIR','PEMBERKASAN','SURVEY','ANALISA','KOMITE','CAIR','SELESAI','BATAL') NOT NULL DEFAULT 'FORMULIR',
    `pipeline_status` ENUM('PROSPECT_CONFIRMED','SLA_RUNNING','APPROVED','DISBURSED','REJECTED','CANCELLED') NOT NULL DEFAULT 'PROSPECT_CONFIRMED',
    `created_by` VARCHAR(20) NOT NULL,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY `uq_credit_pipeline_prospect` (`prospect_id`),
    INDEX `idx_credit_pipeline_assigned` (`assigned_to`),
    INDEX `idx_credit_pipeline_stage` (`current_stage`),
    INDEX `idx_credit_pipeline_status` (`pipeline_status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `prospect_credit_pipeline_documents` (
    `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `pipeline_id` BIGINT UNSIGNED NOT NULL,
    `doc_code` VARCHAR(50) NOT NULL,
    `doc_name` VARCHAR(150) NOT NULL,
    `is_required` TINYINT(1) NOT NULL DEFAULT 1,
    `is_completed` TINYINT(1) NOT NULL DEFAULT 0,
    `completed_at` DATETIME DEFAULT NULL,
    `file_url` VARCHAR(500) DEFAULT NULL,
    `file_type` VARCHAR(30) DEFAULT NULL,
    `note` TEXT DEFAULT NULL,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY `uq_credit_doc` (`pipeline_id`, `doc_code`),
    INDEX `idx_credit_doc_pipeline` (`pipeline_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `prospect_credit_pipeline_stages` (
    `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `pipeline_id` BIGINT UNSIGNED NOT NULL,
    `prospect_id` BIGINT UNSIGNED NOT NULL,
    `stage` ENUM('FORMULIR','PEMBERKASAN','SURVEY','ANALISA','KOMITE','CAIR') NOT NULL,
    `stage_started_at` DATETIME NOT NULL,
    `stage_ended_at` DATETIME DEFAULT NULL,
    `duration_days` INT UNSIGNED DEFAULT NULL,
    `sla_counted` TINYINT(1) NOT NULL DEFAULT 1,
    `attachment_url` VARCHAR(500) DEFAULT NULL,
    `attachment_type` VARCHAR(30) DEFAULT NULL,
    `attachment_uploaded_at` DATETIME DEFAULT NULL,
    `note` TEXT DEFAULT NULL,
    `created_by` VARCHAR(20) NOT NULL,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_credit_stage_pipeline` (`pipeline_id`),
    INDEX `idx_credit_stage_prospect` (`prospect_id`),
    INDEX `idx_credit_stage_stage` (`stage`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
