<?php

require __DIR__ . '/../api/config/env.php';

$host = env('DB_HOST', 'localhost');
$port = env('DB_PORT', '3306');
$user = env('DB_USER', 'root');
$pass = env('DB_PASS', '');
$name = env('DB_NAME', 'dpk');

$server = new PDO(
    "mysql:host={$host};port={$port};charset=utf8mb4",
    $user,
    $pass,
    [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC]
);

$server->exec("CREATE DATABASE IF NOT EXISTS `{$name}` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");

$db = new PDO(
    "mysql:host={$host};port={$port};dbname={$name};charset=utf8mb4",
    $user,
    $pass,
    [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC]
);

function tableExists(PDO $db, string $table): bool
{
    $stmt = $db->prepare("SHOW TABLES LIKE :table");
    $stmt->execute([':table' => $table]);
    return (bool) $stmt->fetchColumn();
}

function columnExists(PDO $db, string $table, string $column): bool
{
    if (!tableExists($db, $table)) {
        return false;
    }

    $stmt = $db->prepare("SHOW COLUMNS FROM `{$table}` LIKE :column");
    $stmt->execute([':column' => $column]);
    return (bool) $stmt->fetchColumn();
}

if (tableExists($db, 'kode_kantor')) {
    if (!columnExists($db, 'kode_kantor', 'korwil')) {
        $db->exec("ALTER TABLE `kode_kantor` ADD COLUMN `korwil` VARCHAR(20) DEFAULT NULL AFTER `nama_kantor`");
    }
    if (!columnExists($db, 'kode_kantor', 'is_active')) {
        $db->exec("ALTER TABLE `kode_kantor` ADD COLUMN `is_active` TINYINT(1) NOT NULL DEFAULT 1");
    }
}

if (tableExists($db, 'prospects') && !columnExists($db, 'prospects', 'closing_asset_purchase_method')) {
    $db->exec("ALTER TABLE `prospects` ADD COLUMN `closing_asset_purchase_method` ENUM('LELANG','CESSIE','LAINNYA') DEFAULT NULL AFTER `closing_buyer_name`");
}

if (tableExists($db, 'prospect_credit_pipeline_documents') && !columnExists($db, 'prospect_credit_pipeline_documents', 'file_type')) {
    $db->exec("ALTER TABLE `prospect_credit_pipeline_documents` ADD COLUMN `file_type` VARCHAR(30) DEFAULT NULL AFTER `file_url`");
}

if (tableExists($db, 'prospect_credit_pipeline_stages')) {
    if (!columnExists($db, 'prospect_credit_pipeline_stages', 'attachment_url')) {
        $db->exec("ALTER TABLE `prospect_credit_pipeline_stages` ADD COLUMN `attachment_url` VARCHAR(500) DEFAULT NULL AFTER `sla_counted`");
    }
    if (!columnExists($db, 'prospect_credit_pipeline_stages', 'attachment_type')) {
        $db->exec("ALTER TABLE `prospect_credit_pipeline_stages` ADD COLUMN `attachment_type` VARCHAR(30) DEFAULT NULL AFTER `attachment_url`");
    }
    if (!columnExists($db, 'prospect_credit_pipeline_stages', 'attachment_uploaded_at')) {
        $db->exec("ALTER TABLE `prospect_credit_pipeline_stages` ADD COLUMN `attachment_uploaded_at` DATETIME DEFAULT NULL AFTER `attachment_type`");
    }
}

$sql = file_get_contents(__DIR__ . '/create_tables_prospek.sql');
$db->exec($sql);

echo "Migration DPK OK\n";
