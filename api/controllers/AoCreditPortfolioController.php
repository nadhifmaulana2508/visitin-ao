<?php

class AoCreditPortfolioController
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getDpk();
        $this->ensureTables();
    }

    private function getCurrentUser(): array
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        return $_SESSION['user_data'] ?? [];
    }

    private function canAccessModule(array $user): bool
    {
        $role = (string)($user['role'] ?? 'staff');
        $perms = $user['permissions'] ?? [];
        return $role === 'developer' || $role === 'superuser' || in_array('AO_KREDIT', $perms, true);
    }

    private function isAoKreditLogin(array $user): bool
    {
        return in_array('AO_KREDIT', $user['permissions'] ?? [], true) || ($user['role'] ?? '') === 'ao_kredit';
    }

    private function ensureTables(): void
    {
        static $booted = false;
        if ($booted) {
            return;
        }

        $this->db->exec("
            CREATE TABLE IF NOT EXISTS `ao_credit_pipeline_targets` (
                `id` INT NOT NULL AUTO_INCREMENT,
                `periode_bulan` CHAR(7) NOT NULL,
                `closing_date` DATE DEFAULT NULL,
                `harian_date` DATE DEFAULT NULL,
                `no_rekening` VARCHAR(30) NOT NULL,
                `kode_kantor` VARCHAR(3) NOT NULL,
                `kode_ao` VARCHAR(30) DEFAULT NULL,
                `ao_employee_id` VARCHAR(30) DEFAULT NULL,
                `target_pokok_awal_bulan` DECIMAL(18,2) NOT NULL DEFAULT 0,
                `target_bunga_awal_bulan` DECIMAL(18,2) NOT NULL DEFAULT 0,
                `catatan` VARCHAR(255) DEFAULT NULL,
                `input_by` VARCHAR(30) DEFAULT NULL,
                `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                PRIMARY KEY (`id`),
                UNIQUE KEY `uniq_periode_rekening` (`periode_bulan`, `no_rekening`),
                KEY `idx_ao_credit_pipeline_targets_ao` (`ao_employee_id`),
                KEY `idx_ao_credit_pipeline_targets_cabang` (`kode_kantor`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci
        ");

        $this->db->exec("
            CREATE TABLE IF NOT EXISTS `ao_credit_portfolio_activities` (
                `id` INT NOT NULL AUTO_INCREMENT,
                `no_rekening` VARCHAR(30) NOT NULL,
                `kode_kantor` VARCHAR(3) NOT NULL,
                `kode_ao` VARCHAR(30) DEFAULT NULL,
                `ao_employee_id` VARCHAR(30) DEFAULT NULL,
                `jenis_tindakan` VARCHAR(20) NOT NULL,
                `kode_tindakan` VARCHAR(20) DEFAULT NULL,
                `lokasi_tindakan` VARCHAR(50) DEFAULT NULL,
                `orang_ditemui` VARCHAR(50) DEFAULT NULL,
                `nominal_janji` DECIMAL(18,2) NOT NULL DEFAULT 0,
                `tanggal_janji` DATE DEFAULT NULL,
                `keterangan` TEXT DEFAULT NULL,
                `latitude` DECIMAL(12,8) DEFAULT NULL,
                `longitude` DECIMAL(12,8) DEFAULT NULL,
                `geo_address` VARCHAR(255) DEFAULT NULL,
                `foto_path` VARCHAR(255) DEFAULT NULL,
                `created_by` VARCHAR(30) DEFAULT NULL,
                `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                PRIMARY KEY (`id`),
                KEY `idx_ao_credit_portfolio_activities_rekening` (`no_rekening`),
                KEY `idx_ao_credit_portfolio_activities_ao` (`ao_employee_id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci
        ");

        $booted = true;
    }

    private function saveBase64Upload(string $base64, string $mimeType, string $prefix): ?string
    {
        if ($base64 === '' || $mimeType === '') {
            return null;
        }

        if (!in_array($mimeType, ['image/jpeg', 'image/png'], true)) {
            sendResponse(400, 'Foto kunjungan hanya boleh JPG atau PNG', null);
        }

        $payload = preg_replace('#^data:[^;]+;base64,#i', '', $base64);
        $binary = base64_decode($payload, true);
        if ($binary === false || strlen($binary) === 0) {
            sendResponse(400, 'Foto kunjungan tidak valid', null);
        }

        $extension = $mimeType === 'image/png' ? 'png' : 'jpg';
        $folder = __DIR__ . '/../../uploads/ao_credit_portfolio/';
        if (!is_dir($folder)) {
            mkdir($folder, 0755, true);
        }

        $safePrefix = preg_replace('/[^a-zA-Z0-9_-]/', '_', $prefix);
        $filename = $safePrefix . '_' . date('Ymd_His') . '_' . uniqid() . '.' . $extension;
        $fullPath = $folder . $filename;

        if (!file_put_contents($fullPath, $binary)) {
            sendResponse(500, 'Gagal menyimpan foto kunjungan', null);
        }

        return 'uploads/ao_credit_portfolio/' . $filename;
    }

    private function getFilterDates(array $params): array
    {
        $closingDate = trim((string)($params['closing_date'] ?? date('Y-m-t', strtotime('last month'))));
        $harianDate = trim((string)($params['harian_date'] ?? date('Y-m-d')));
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $closingDate) || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $harianDate)) {
            sendResponse(400, 'Format tanggal tidak valid', null);
        }

        return [
            'closing_date' => $closingDate,
            'harian_date' => $harianDate,
            'periode_bulan' => date('Y-m', strtotime($harianDate)),
        ];
    }

    private function buildPortfolioSql(array $params, array $user): array
    {
        if (!$this->canAccessModule($user)) {
            sendResponse(403, 'Anda tidak punya akses ke kelolaan AO Kredit', null);
        }

        $dates = $this->getFilterDates($params);
        $search = trim((string)($params['search'] ?? ''));
        $filterAo = trim((string)($params['ao_employee_id'] ?? ''));
        $filterKodeKantor = trim((string)($params['kode_kantor'] ?? ''));
        $noRekening = trim((string)($params['no_rekening'] ?? ''));

        $binds = [
            ':closing_date' => $dates['closing_date'],
            ':harian_date_join' => $dates['harian_date'],
            ':day_cutoff_1' => $dates['harian_date'],
            ':day_cutoff_2' => $dates['harian_date'],
            ':day_cutoff_3' => $dates['harian_date'],
            ':day_cutoff_4a' => $dates['harian_date'],
            ':day_cutoff_4b' => $dates['harian_date'],
            ':trx_month_current_1' => $dates['harian_date'],
            ':trx_month_current_2' => $dates['harian_date'],
            ':trx_month_prev_1' => $dates['harian_date'],
            ':trx_month_prev_2a' => $dates['harian_date'],
            ':trx_month_prev_2b' => $dates['harian_date'],
            ':trx_from' => date('Y-m-01', strtotime($dates['harian_date'] . ' -1 month')),
            ':trx_to' => $dates['harian_date'],
            ':va_created' => $dates['harian_date'],
            ':periode_bulan' => $dates['periode_bulan'],
        ];

        $where = [
            "c.created = :closing_date",
            "c.hari_menunggak <= 30",
            "c.baki_debet > 0",
        ];

        if ($this->isAoKreditLogin($user)) {
            $where[] = "ao.id_peg = :login_ao_id";
            $binds[':login_ao_id'] = (string)($user['employee_id'] ?? '');
        } else {
            $userKode = (string)($user['kode_kantor'] ?? '000');
            if ($filterKodeKantor !== '') {
                $where[] = "c.kode_cabang = :kode_kantor";
                $binds[':kode_kantor'] = $filterKodeKantor;
            } elseif ($userKode !== '' && $userKode !== '000') {
                $where[] = "c.kode_cabang = :user_kode_kantor";
                $binds[':user_kode_kantor'] = $userKode;
            }

            if ($filterAo !== '') {
                $where[] = "ao.id_peg = :filter_ao_id";
                $binds[':filter_ao_id'] = $filterAo;
            }
        }

        if ($noRekening !== '') {
            $where[] = "c.no_rekening = :no_rekening";
            $binds[':no_rekening'] = $noRekening;
        }

        if ($search !== '') {
            $where[] = "(c.no_rekening LIKE :search_rek OR c.nama_nasabah LIKE :search_name OR COALESCE(ao.nama_ao, '') LIKE :search_ao)";
            $binds[':search_rek'] = '%' . $search . '%';
            $binds[':search_name'] = '%' . $search . '%';
            $binds[':search_ao'] = '%' . $search . '%';
        }

        $whereSql = implode(' AND ', $where);
        $sql = "SELECT
                c.no_rekening,
                c.nama_nasabah,
                c.kode_cabang AS kode_kantor,
                kk.nama_kantor AS branch_name,
                c.alamat,
                c.kode_group2 AS kode_ao,
                ao.id_peg AS ao_employee_id,
                COALESCE(ao.nama_ao, c.kode_group2) AS nama_ao,
                c.tgl_jatuh_tempo,
                c.baki_debet AS bd_closing,
                c.hari_menunggak AS dpd_closing,
                CASE
                    WHEN c.hari_menunggak = 0 THEN 'A_DPD 0'
                    WHEN c.hari_menunggak BETWEEN 1 AND 30 THEN 'B_DPD 1-30'
                    ELSE 'C_DPD > 30'
                END AS bucket_awal,
                COALESCE(n.baki_debet, 0) AS bd_sekarang,
                COALESCE(n.hari_menunggak, 0) AS dpd_sekarang,
                CASE
                    WHEN DAY(:day_cutoff_1) <= DAY(c.tgl_jatuh_tempo) AND COALESCE(trx.total_bayar_sekarang, 0) <= 0
                    THEN COALESCE(va.proyeksi_pokok, 0)
                    ELSE COALESCE(n.tunggakan_pokok, 0)
                END AS tunggakan_pokok_skrg,
                CASE
                    WHEN DAY(:day_cutoff_2) <= DAY(c.tgl_jatuh_tempo) AND COALESCE(trx.total_bayar_sekarang, 0) <= 0
                    THEN COALESCE(va.proyeksi_bunga, 0)
                    ELSE COALESCE(n.tunggakan_bunga, 0)
                END AS tunggakan_bunga_skrg,
                (
                    CASE
                        WHEN DAY(:day_cutoff_3) <= DAY(c.tgl_jatuh_tempo) AND COALESCE(trx.total_bayar_sekarang, 0) <= 0
                        THEN COALESCE(va.proyeksi_pokok, 0) + COALESCE(va.proyeksi_bunga, 0)
                        ELSE COALESCE(n.tunggakan_pokok, 0) + COALESCE(n.tunggakan_bunga, 0)
                    END
                ) AS totung_skrg,
                CASE
                    WHEN n.no_rekening IS NULL THEN 'LUNAS'
                    WHEN n.hari_menunggak = 0 THEN 'A_DPD 0'
                    WHEN n.hari_menunggak BETWEEN 1 AND 30 THEN 'B_DPD 1-30'
                    WHEN n.hari_menunggak > 30 THEN 'C_DPD > 30'
                END AS bucket_sekarang,
                trx.tgl_trx_lalu,
                COALESCE(trx.pokok_lalu, 0) AS pokok_lalu,
                COALESCE(trx.bunga_lalu, 0) AS bunga_lalu,
                trx.tgl_trx_skrg,
                COALESCE(trx.total_bayar_sekarang, 0) AS bayar_skrg,
                CASE
                    WHEN n.no_rekening IS NULL OR n.baki_debet <= 0 THEN 'lunas'
                    WHEN COALESCE(trx.total_bayar_sekarang, 0) > 0 AND (COALESCE(n.tunggakan_pokok, 0) + COALESCE(n.tunggakan_bunga, 0)) > 0 THEN 'byr, ada tunggakan'
                    WHEN COALESCE(trx.total_bayar_sekarang, 0) > 0 AND DAY(trx.tgl_trx_skrg) <= DAY(c.tgl_jatuh_tempo) THEN 'byr dan tepat di jt'
                    WHEN COALESCE(trx.total_bayar_sekarang, 0) > 0 AND DAY(trx.tgl_trx_skrg) > DAY(c.tgl_jatuh_tempo) THEN 'byr lewat jt'
                    WHEN COALESCE(trx.total_bayar_sekarang, 0) <= 0 AND DAY(:day_cutoff_4a) <= DAY(c.tgl_jatuh_tempo) THEN 'blm byr belum jt'
                    WHEN COALESCE(trx.total_bayar_sekarang, 0) <= 0 AND DAY(:day_cutoff_4b) > DAY(c.tgl_jatuh_tempo) THEN 'blm byr lewat jt'
                    ELSE 'lainnya'
                END AS status_bayar_jt,
                CASE
                    WHEN n.no_rekening IS NULL OR n.baki_debet <= 0 THEN 'LUNAS'
                    WHEN c.hari_menunggak > 0 AND n.hari_menunggak = 0 THEN 'BTC (Back to Current)'
                    WHEN n.hari_menunggak > c.hari_menunggak THEN 'FLOW (Memburuk)'
                    WHEN n.hari_menunggak < c.hari_menunggak AND n.hari_menunggak > 0 THEN 'IMPROVED (Membaik)'
                    WHEN n.hari_menunggak = c.hari_menunggak THEN 'STAY'
                    ELSE 'LAINNYA'
                END AS pergerakan_status,
                COALESCE(pt.target_pokok_awal_bulan, 0) AS target_pokok_awal_bulan,
                COALESCE(pt.target_bunga_awal_bulan, 0) AS target_bunga_awal_bulan,
                pt.catatan AS target_catatan,
                pt.updated_at AS target_updated_at
            FROM nominatif c
            INNER JOIN ao_kredit ao ON c.kode_group2 = ao.kode_group2
            LEFT JOIN (
                SELECT kode_kantor, MIN(nama_kantor) AS nama_kantor
                FROM kode_kantor
                GROUP BY kode_kantor
            ) kk ON kk.kode_kantor = c.kode_cabang
            LEFT JOIN nominatif n
                ON c.no_rekening = n.no_rekening
               AND n.created = :harian_date_join
            LEFT JOIN (
                SELECT
                    no_rekening,
                    MAX(tunggakan_pokok) AS proyeksi_pokok,
                    MAX(tunggakan_bunga) AS proyeksi_bunga
                FROM nominatif_proyeksi_va
                WHERE created > :va_created
                GROUP BY no_rekening
            ) va ON c.no_rekening = va.no_rekening
            LEFT JOIN (
                SELECT
                    no_rekening,
                    MAX(CASE WHEN DATE_FORMAT(tgl_trans, '%Y-%m') = DATE_FORMAT(:trx_month_current_1, '%Y-%m') THEN tgl_trans END) AS tgl_trx_skrg,
                    SUM(CASE WHEN DATE_FORMAT(tgl_trans, '%Y-%m') = DATE_FORMAT(:trx_month_current_2, '%Y-%m')
                        THEN (COALESCE(angsuran_pokok, 0) + COALESCE(angsuran_bunga, 0) - COALESCE(diskon_bunga, 0))
                        ELSE 0 END) AS total_bayar_sekarang,
                    MAX(CASE WHEN DATE_FORMAT(tgl_trans, '%Y-%m') = DATE_FORMAT(DATE_SUB(:trx_month_prev_1, INTERVAL 1 MONTH), '%Y-%m') THEN tgl_trans END) AS tgl_trx_lalu,
                    SUM(CASE WHEN DATE_FORMAT(tgl_trans, '%Y-%m') = DATE_FORMAT(DATE_SUB(:trx_month_prev_2a, INTERVAL 1 MONTH), '%Y-%m')
                        THEN COALESCE(angsuran_pokok, 0) ELSE 0 END) AS pokok_lalu,
                    SUM(CASE WHEN DATE_FORMAT(tgl_trans, '%Y-%m') = DATE_FORMAT(DATE_SUB(:trx_month_prev_2b, INTERVAL 1 MONTH), '%Y-%m')
                        THEN (COALESCE(angsuran_bunga, 0) - COALESCE(diskon_bunga, 0)) ELSE 0 END) AS bunga_lalu
                FROM transaksi_kredit
                WHERE tgl_trans >= :trx_from
                  AND tgl_trans <= :trx_to
                GROUP BY no_rekening
            ) trx ON c.no_rekening = trx.no_rekening
            LEFT JOIN ao_credit_pipeline_targets pt
                ON pt.no_rekening = c.no_rekening
               AND pt.periode_bulan = :periode_bulan
            WHERE {$whereSql}";

        return [$sql, $binds, $dates];
    }

    public function list(array $params): void
    {
        $user = $this->getCurrentUser();
        [$baseSql, $binds, $dates] = $this->buildPortfolioSql($params, $user);

        $page = max(1, (int)($params['page'] ?? 1));
        $limit = min(50, max(10, (int)($params['limit'] ?? 20)));
        $offset = ($page - 1) * $limit;

        $summaryStmt = $this->db->prepare("SELECT
                COUNT(*) AS total_noa,
                COALESCE(SUM(bd_closing), 0) AS total_bd_closing,
                COALESCE(SUM(totung_skrg), 0) AS total_totung_skrg,
                COALESCE(SUM(target_pokok_awal_bulan), 0) AS total_target_pokok,
                COALESCE(SUM(target_bunga_awal_bulan), 0) AS total_target_bunga
            FROM ({$baseSql}) x");
        $summaryStmt->execute($binds);
        $summary = $summaryStmt->fetch() ?: [];

        $countStmt = $this->db->prepare("SELECT COUNT(*) FROM ({$baseSql}) x");
        $countStmt->execute($binds);
        $total = (int)$countStmt->fetchColumn();

        $itemsSql = $baseSql . " ORDER BY
            CASE pergerakan_status
                WHEN 'FLOW (Memburuk)' THEN 1
                WHEN 'STAY' THEN 2
                WHEN 'IMPROVED (Membaik)' THEN 3
                WHEN 'BTC (Back to Current)' THEN 4
                WHEN 'LUNAS' THEN 5
                ELSE 6
            END,
            dpd_closing ASC,
            bd_closing DESC
            LIMIT :limit OFFSET :offset";
        $itemsStmt = $this->db->prepare($itemsSql);
        foreach ($binds as $key => $value) {
            $itemsStmt->bindValue($key, $value);
        }
        $itemsStmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $itemsStmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $itemsStmt->execute();

        sendResponse(200, 'OK', [
            'items' => $itemsStmt->fetchAll(),
            'summary' => [
                'total_noa' => (int)($summary['total_noa'] ?? 0),
                'total_bd_closing' => (float)($summary['total_bd_closing'] ?? 0),
                'total_totung_skrg' => (float)($summary['total_totung_skrg'] ?? 0),
                'total_target_pokok' => (float)($summary['total_target_pokok'] ?? 0),
                'total_target_bunga' => (float)($summary['total_target_bunga'] ?? 0),
            ],
            'filters' => $dates,
            'pagination' => [
                'page' => $page,
                'limit' => $limit,
                'total' => $total,
                'total_pages' => (int)ceil($total / $limit),
            ],
        ]);
    }

    public function detail(array $params): void
    {
        $user = $this->getCurrentUser();
        $noRekening = trim((string)($params['no_rekening'] ?? ''));
        if ($noRekening === '') {
            sendResponse(400, 'no_rekening wajib diisi', null);
        }

        $params['no_rekening'] = $noRekening;
        [$baseSql, $binds, $dates] = $this->buildPortfolioSql($params, $user);
        $stmt = $this->db->prepare($baseSql . " LIMIT 1");
        $stmt->execute($binds);
        $row = $stmt->fetch();
        if (!$row) {
            sendResponse(404, 'Data kelolaan tidak ditemukan', null);
        }

        $activityStmt = $this->db->prepare("SELECT *
            FROM ao_credit_portfolio_activities
            WHERE no_rekening = :no_rekening
            ORDER BY created_at DESC, id DESC");
        $activityStmt->execute([':no_rekening' => $noRekening]);

        sendResponse(200, 'OK', [
            'portfolio' => $row,
            'activities' => $activityStmt->fetchAll(),
            'filters' => $dates,
        ]);
    }

    public function savePipelineTarget(array $input): void
    {
        $user = $this->getCurrentUser();
        if (!$this->canAccessModule($user)) {
            sendResponse(403, 'Anda tidak punya akses', null);
        }

        $noRekening = trim((string)($input['no_rekening'] ?? ''));
        $kodeKantor = trim((string)($input['kode_kantor'] ?? ''));
        $aoEmployeeId = trim((string)($input['ao_employee_id'] ?? ''));
        $kodeAo = trim((string)($input['kode_ao'] ?? ''));
        $periodeBulan = trim((string)($input['periode_bulan'] ?? date('Y-m')));
        $closingDate = trim((string)($input['closing_date'] ?? date('Y-m-t', strtotime('last month'))));
        $harianDate = trim((string)($input['harian_date'] ?? date('Y-m-d')));
        $targetPokok = (float)($input['target_pokok_awal_bulan'] ?? 0);
        $targetBunga = (float)($input['target_bunga_awal_bulan'] ?? 0);
        $catatan = trim((string)($input['catatan'] ?? ''));

        if ($noRekening === '' || $kodeKantor === '' || $periodeBulan === '') {
            sendResponse(400, 'Data rekening, cabang, dan periode wajib diisi', null);
        }

        $stmt = $this->db->prepare("INSERT INTO ao_credit_pipeline_targets
            (periode_bulan, closing_date, harian_date, no_rekening, kode_kantor, kode_ao, ao_employee_id, target_pokok_awal_bulan, target_bunga_awal_bulan, catatan, input_by)
            VALUES (:periode_bulan, :closing_date, :harian_date, :no_rekening, :kode_kantor, :kode_ao, :ao_employee_id, :target_pokok, :target_bunga, :catatan, :input_by)
            ON DUPLICATE KEY UPDATE
                closing_date = VALUES(closing_date),
                harian_date = VALUES(harian_date),
                kode_kantor = VALUES(kode_kantor),
                kode_ao = VALUES(kode_ao),
                ao_employee_id = VALUES(ao_employee_id),
                target_pokok_awal_bulan = VALUES(target_pokok_awal_bulan),
                target_bunga_awal_bulan = VALUES(target_bunga_awal_bulan),
                catatan = VALUES(catatan),
                input_by = VALUES(input_by),
                updated_at = CURRENT_TIMESTAMP");
        $stmt->execute([
            ':periode_bulan' => $periodeBulan,
            ':closing_date' => $closingDate,
            ':harian_date' => $harianDate,
            ':no_rekening' => $noRekening,
            ':kode_kantor' => $kodeKantor,
            ':kode_ao' => $kodeAo ?: null,
            ':ao_employee_id' => $aoEmployeeId ?: null,
            ':target_pokok' => $targetPokok,
            ':target_bunga' => $targetBunga,
            ':catatan' => $catatan !== '' ? $catatan : null,
            ':input_by' => (string)($user['employee_id'] ?? ''),
        ]);

        sendResponse(200, 'Pipeline awal bulan berhasil disimpan', null);
    }

    public function saveActivity(array $input): void
    {
        $user = $this->getCurrentUser();
        if (!$this->canAccessModule($user)) {
            sendResponse(403, 'Anda tidak punya akses', null);
        }

        $noRekening = trim((string)($input['no_rekening'] ?? ''));
        $jenisTindakan = strtoupper(trim((string)($input['jenis_tindakan'] ?? '')));
        $keterangan = trim((string)($input['keterangan'] ?? ''));
        if ($noRekening === '' || $jenisTindakan === '' || $keterangan === '') {
            sendResponse(400, 'Rekening, jenis tindakan, dan keterangan wajib diisi', null);
        }

        $fotoPath = null;
        $fotoBase64 = trim((string)($input['foto_base64'] ?? ''));
        $fotoMime = trim((string)($input['foto_mime_type'] ?? ''));
        if ($fotoBase64 !== '' && $fotoMime !== '') {
            $fotoPath = $this->saveBase64Upload($fotoBase64, $fotoMime, 'visit_' . $noRekening);
        }

        $stmt = $this->db->prepare("INSERT INTO ao_credit_portfolio_activities
            (no_rekening, kode_kantor, kode_ao, ao_employee_id, jenis_tindakan, kode_tindakan, lokasi_tindakan, orang_ditemui, nominal_janji, tanggal_janji, keterangan, latitude, longitude, geo_address, foto_path, created_by)
            VALUES
            (:no_rekening, :kode_kantor, :kode_ao, :ao_employee_id, :jenis_tindakan, :kode_tindakan, :lokasi_tindakan, :orang_ditemui, :nominal_janji, :tanggal_janji, :keterangan, :latitude, :longitude, :geo_address, :foto_path, :created_by)");
        $stmt->execute([
            ':no_rekening' => $noRekening,
            ':kode_kantor' => trim((string)($input['kode_kantor'] ?? '')),
            ':kode_ao' => trim((string)($input['kode_ao'] ?? '')) ?: null,
            ':ao_employee_id' => trim((string)($input['ao_employee_id'] ?? '')) ?: null,
            ':jenis_tindakan' => $jenisTindakan,
            ':kode_tindakan' => trim((string)($input['kode_tindakan'] ?? '')) ?: null,
            ':lokasi_tindakan' => trim((string)($input['lokasi_tindakan'] ?? '')) ?: null,
            ':orang_ditemui' => trim((string)($input['orang_ditemui'] ?? '')) ?: null,
            ':nominal_janji' => (float)($input['nominal_janji'] ?? 0),
            ':tanggal_janji' => trim((string)($input['tanggal_janji'] ?? '')) ?: null,
            ':keterangan' => $keterangan,
            ':latitude' => $input['latitude'] !== '' ? $input['latitude'] : null,
            ':longitude' => $input['longitude'] !== '' ? $input['longitude'] : null,
            ':geo_address' => trim((string)($input['geo_address'] ?? '')) ?: null,
            ':foto_path' => $fotoPath,
            ':created_by' => (string)($user['employee_id'] ?? ''),
        ]);

        sendResponse(201, 'Aktivitas kelolaan berhasil disimpan', [
            'foto_path' => $fotoPath,
        ]);
    }
}
