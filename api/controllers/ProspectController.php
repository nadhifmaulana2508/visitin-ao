<?php
/**
 * ProspectController
 * 
 * Full CRUD + filter untuk modul E-Prospek.
 * Filter: range tanggal, kode_kantor (konsolidasi/korwil/cabang), ao/non-ao/all, status
 * SLA Pipeline: otomatis jadi pipeline AO Kredit saat status = SLA
 */
class ProspectController
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getDpk();
    }

    private function ensureColumnExists(string $table, string $column, string $definition): void
    {
        static $checked = [];
        $key = "{$table}.{$column}";
        if (isset($checked[$key])) {
            return;
        }

        if (!preg_match('/^[a-zA-Z0-9_]+$/', $table) || !preg_match('/^[a-zA-Z0-9_]+$/', $column)) {
            sendResponse(500, 'Konfigurasi kolom database tidak valid', null);
        }

        $stmt = $this->db->prepare("
            SELECT COUNT(*)
            FROM INFORMATION_SCHEMA.COLUMNS
            WHERE TABLE_SCHEMA = DATABASE()
              AND TABLE_NAME = :table
              AND COLUMN_NAME = :column
        ");
        $stmt->execute([
            ':table' => $table,
            ':column' => $column,
        ]);

        if ((int) $stmt->fetchColumn() === 0) {
            try {
                $this->db->exec("ALTER TABLE `{$table}` ADD COLUMN `{$column}` {$definition}");
            } catch (\PDOException $e) {
                sendResponse(500, 'Kolom plafon belum tersedia dan gagal dibuat otomatis', [
                    'error' => $e->getMessage(),
                ]);
            }
        }

        $checked[$key] = true;
    }

    private function kodeKantorJoinSql(string $alias = 'kk'): string
    {
        $korwilSelect = $this->hasKodeKantorColumn('korwil') ? 'MIN(korwil) AS korwil' : 'NULL AS korwil';

        return "LEFT JOIN (
                    SELECT kode_kantor, MIN(nama_kantor) AS nama_kantor, {$korwilSelect}
                    FROM kode_kantor
                    GROUP BY kode_kantor
                ) {$alias} ON CONVERT(p.kode_kantor USING utf8mb4) COLLATE utf8mb4_unicode_ci = CONVERT({$alias}.kode_kantor USING utf8mb4) COLLATE utf8mb4_unicode_ci";
    }

    private function hasKodeKantorColumn(string $column): bool
    {
        static $columns = null;

        if ($columns === null) {
            try {
                $stmt = $this->db->query("SHOW COLUMNS FROM `kode_kantor`");
                $columns = array_column($stmt->fetchAll(), 'Field');
            } catch (\Throwable $e) {
                $columns = [];
            }
        }

        return in_array($column, $columns, true);
    }

    /**
     * Helper: get current user data from token/session
     */
    private function getCurrentUser(): array
    {
        if (session_status() === PHP_SESSION_NONE) session_start();
        return $_SESSION['user_data'] ?? [];
    }

    private function resolveProspectType(array $input): string
    {
        $type = strtoupper(trim($input['prospect_type'] ?? ''));
        if ($type !== '') {
            return $type;
        }

        $product = strtolower(trim($input['rekomendasi_produk'] ?? ''));
        return match ($product) {
            'kredit' => 'KREDIT',
            'tabungan' => 'TABUNGAN',
            'deposito' => 'DEPOSITO',
            'aset' => 'PEMBELI_ASET',
            default => '',
        };
    }

    private function getDelegationTargetLabel(string $kodeKantor): string
    {
        return "Kepala Bidang Pemasaran cabang {$kodeKantor}";
    }

    private function getAoGroupForProspectType(string $type): ?string
    {
        return match ($type) {
            'KREDIT', 'DEBITUR_EXISTING' => 'AO Kredit',
            'TABUNGAN', 'DEPOSITO' => 'AO Dana',
            'PEMBELI_ASET' => 'AO Remedial',
            default => null,
        };
    }

    private function getSsoAoTypeForGroup(?string $groupJabatan): ?string
    {
        return match ($groupJabatan) {
            'AO Kredit' => 'kredit',
            'AO Dana' => 'dana',
            'AO Remedial' => 'remedial',
            default => null,
        };
    }

    private function getAoGroupForUser(array $user): ?string
    {
        return match ($user['role'] ?? '') {
            'ao_kredit' => 'AO Kredit',
            'ao_dana' => 'AO Dana',
            'ao_remedial' => 'AO Remedial',
            default => null,
        };
    }

    private function isCreditProspect(array $prospect): bool
    {
        return in_array($prospect['prospect_type'] ?? '', ['KREDIT', 'DEBITUR_EXISTING'], true);
    }

    private function getEmployeeDisplayName(?string $employeeId): ?string
    {
        $employeeId = trim((string) $employeeId);
        if ($employeeId === '') {
            return null;
        }

        try {
            $employee = Database::getPegawaiById($employeeId);
            return $employee['full_name'] ?? null;
        } catch (\Exception $e) {
            return null;
        }
    }

    private function getDefaultCreditDocuments(): array
    {
        return [
            ['FORMULIR', 'Formulir pengajuan'],
            ['KTP_SUAMI_ISTRI', 'KTP suami/istri'],
            ['SURAT_KETERANGAN_USAHA', 'SKU / Surat Keterangan Usaha'],
            ['REKENING_LISTRIK_PAJAK', 'Rekening listrik / air / pajak bangunan / jaminan sertifikat / NPWP'],
            ['STNK_BPKB', 'Jaminan STNK atau BPKB'],
            ['SERTIFIKAT', 'Jaminan sertifikat tanah dan collateral'],
        ];
    }

    private function saveBase64Upload(string $base64, string $mimeType, string $prefix, array $allowedMime, string $folder = 'credit_pipeline'): array
    {
        if (!in_array($mimeType, $allowedMime, true)) {
            sendResponse(400, 'Tipe file tidak sesuai untuk proses ini', null);
        }

        $payload = preg_replace('#^data:[^;]+;base64,#i', '', $base64);
        $binary = base64_decode($payload, true);
        if ($binary === false || strlen($binary) === 0) {
            sendResponse(400, 'File upload tidak valid', null);
        }

        $extension = match ($mimeType) {
            'image/jpeg' => 'jpg',
            'image/png' => 'png',
            'application/pdf' => 'pdf',
            default => 'bin',
        };

        $safePrefix = preg_replace('/[^a-zA-Z0-9_-]/', '_', $prefix);
        $safeFolder = preg_replace('/[^a-zA-Z0-9_-]/', '_', $folder);
        $uploadDir = __DIR__ . "/../../uploads/{$safeFolder}/";
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        $filename = $safePrefix . '_' . date('Ymd_His') . '_' . uniqid() . '.' . $extension;
        $path = $uploadDir . $filename;
        if (!file_put_contents($path, $binary)) {
            sendResponse(500, 'Gagal menyimpan file upload', null);
        }

        return [
            'path' => "uploads/{$safeFolder}/" . $filename,
            'type' => str_starts_with($mimeType, 'image/') ? 'IMAGE' : 'PDF',
        ];
    }

    private function ensureCreditPipeline(array $prospect, array $user, ?string $note = null, ?int $requestedLoanAmount = null): int
    {
        $this->ensureColumnExists('prospect_credit_pipelines', 'requested_loan_amount', 'BIGINT UNSIGNED DEFAULT NULL AFTER `assigned_to`');

        $stmt = $this->db->prepare("SELECT id FROM prospect_credit_pipelines WHERE prospect_id = :pid");
        $stmt->execute([':pid' => $prospect['id']]);
        $existing = $stmt->fetch();
        if ($existing) {
            if ($requestedLoanAmount !== null && $requestedLoanAmount > 0) {
                $this->db->prepare("UPDATE prospect_credit_pipelines SET requested_loan_amount = :amount, updated_at = NOW() WHERE id = :id")
                    ->execute([':amount' => $requestedLoanAmount, ':id' => $existing['id']]);
            }
            return (int) $existing['id'];
        }

        $now = date('Y-m-d H:i:s');
        $ins = $this->db->prepare("INSERT INTO prospect_credit_pipelines
            (prospect_id, assigned_to, requested_loan_amount, confirmation_at, current_stage, pipeline_status, created_by)
            VALUES (:pid, :assigned_to, :requested_loan_amount, :confirmation_at, 'FORMULIR', 'PROSPECT_CONFIRMED', :created_by)");
        $ins->execute([
            ':pid' => $prospect['id'],
            ':assigned_to' => $prospect['assigned_to'] ?? null,
            ':requested_loan_amount' => $requestedLoanAmount && $requestedLoanAmount > 0 ? $requestedLoanAmount : null,
            ':confirmation_at' => $now,
            ':created_by' => $user['employee_id'] ?? 'system',
        ]);
        $pipelineId = (int) $this->db->lastInsertId();

        $docStmt = $this->db->prepare("INSERT INTO prospect_credit_pipeline_documents
            (pipeline_id, doc_code, doc_name, is_required)
            VALUES (:pipeline_id, :doc_code, :doc_name, 1)");
        foreach ($this->getDefaultCreditDocuments() as [$code, $name]) {
            $docStmt->execute([
                ':pipeline_id' => $pipelineId,
                ':doc_code' => $code,
                ':doc_name' => $name,
            ]);
        }

        $stageStmt = $this->db->prepare("INSERT INTO prospect_credit_pipeline_stages
            (pipeline_id, prospect_id, stage, stage_started_at, sla_counted, note, created_by)
            VALUES (:pipeline_id, :prospect_id, 'FORMULIR', :started_at, 0, :note, :created_by)");
        $stageStmt->execute([
            ':pipeline_id' => $pipelineId,
            ':prospect_id' => $prospect['id'],
            ':started_at' => $now,
            ':note' => $note ?: 'Debitur konfirmasi berminat lanjut pengajuan kredit',
            ':created_by' => $user['employee_id'] ?? 'system',
        ]);

        return $pipelineId;
    }

    private function canAccessProspect(array $prospect, array $user): bool
    {
        $role = $user['role'] ?? 'staff';
        $employeeId = $user['employee_id'] ?? '';
        $kodeKantor = $user['kode_kantor'] ?? '000';

        if ($role === 'developer') {
            return true;
        }

        if ($role === 'superuser') {
            if (!empty($user['access_korwil'])) {
                if (!empty($prospect['korwil'])) {
                    return ($prospect['korwil'] ?? '') === $user['access_korwil'];
                }

                return in_array((string)($prospect['kode_kantor'] ?? ''), Database::getKodeKantorCodesByKorwilName($user['access_korwil']), true);
            }
            return $kodeKantor === '000' || ($prospect['kode_kantor'] ?? '') === $kodeKantor;
        }

        if (in_array($role, ['ao_kredit', 'ao_dana', 'ao_remedial'], true)) {
            return ($prospect['assigned_to'] ?? '') === $employeeId || ($prospect['created_by'] ?? '') === $employeeId;
        }

        return ($prospect['created_by'] ?? '') === $employeeId;
    }

    private function canDelegateProspect(array $user): bool
    {
        $userRole = $user['role'] ?? 'staff';
        $userPerms = $user['permissions'] ?? [];
        $userKodeKantor = $user['kode_kantor'] ?? '000';
        $userAccessKorwil = $user['access_korwil'] ?? null;
        $isBranchDelegator = preg_match('/^(00[1-9]|0[1-2][0-9]|028)$/', (string)$userKodeKantor) === 1;

        return $userRole === 'developer'
            || ($userRole === 'superuser'
                && !$userAccessKorwil
                && $isBranchDelegator
                && in_array('SUPERUSER_PROSPEK', $userPerms, true));
    }

    private function getAoActiveWorkloads(array $employeeIds): array
    {
        $employeeIds = array_values(array_unique(array_filter($employeeIds)));
        if (empty($employeeIds)) {
            return [];
        }

        $placeholders = [];
        $binds = [];
        foreach ($employeeIds as $i => $employeeId) {
            $key = ":ao_{$i}";
            $placeholders[] = $key;
            $binds[$key] = $employeeId;
        }

        $stmt = $this->db->prepare("SELECT assigned_to, COUNT(*) AS active_count
            FROM prospects
            WHERE delegation_status = 'SUDAH_DIDELEGASIKAN'
              AND assigned_to IN (" . implode(',', $placeholders) . ")
              AND status NOT IN ('CLOSING','REJECT')
            GROUP BY assigned_to");
        $stmt->execute($binds);

        $counts = [];
        foreach ($stmt->fetchAll() as $row) {
            $counts[$row['assigned_to']] = (int) $row['active_count'];
        }

        return $counts;
    }

    private function attachAoWorkloads(array $candidates): array
    {
        $limit = max(1, (int) env('AO_WORKLOAD_LIMIT', 10));
        $counts = $this->getAoActiveWorkloads(array_map(fn($ao) => $ao['employee_id'] ?? '', $candidates));

        foreach ($candidates as &$candidate) {
            $activeCount = $counts[$candidate['employee_id'] ?? ''] ?? 0;
            $candidate['active_count'] = $activeCount;
            $candidate['workload_limit'] = $limit;
            $candidate['is_overload'] = $activeCount >= $limit;
        }
        unset($candidate);

        return $candidates;
    }

    private function getProspectsForDelegation(array $ids, array $user): array
    {
        $ids = array_values(array_unique(array_filter(array_map('intval', $ids), fn($id) => $id > 0)));
        if (empty($ids)) {
            sendResponse(400, 'Pilih minimal 1 prospek', null);
        }
        if (count($ids) > 50) {
            sendResponse(400, 'Maksimal 50 prospek per sekali delegasi', null);
        }

        $placeholders = [];
        $binds = [];
        foreach ($ids as $i => $id) {
            $key = ":id_{$i}";
            $placeholders[] = $key;
            $binds[$key] = $id;
        }

        $stmt = $this->db->prepare("SELECT * FROM prospects WHERE id IN (" . implode(',', $placeholders) . ")");
        $stmt->execute($binds);
        $prospects = $stmt->fetchAll();

        if (count($prospects) !== count($ids)) {
            sendResponse(404, 'Sebagian prospek tidak ditemukan', null);
        }

        $kodeKantor = null;
        $requiredGroup = null;
        foreach ($prospects as $prospect) {
            if (!$this->canAccessProspect($prospect, $user)) {
                sendResponse(403, 'Ada prospek yang tidak bisa Anda akses', null);
            }
            if (($prospect['delegation_status'] ?? '') === 'SUDAH_DIDELEGASIKAN') {
                sendResponse(400, 'Pilih hanya prospek yang belum didelegasikan', null);
            }

            $prospectGroup = $this->getAoGroupForProspectType($prospect['prospect_type']);
            if ($prospectGroup === null) {
                sendResponse(400, 'Ada jenis prospek yang belum punya group AO tujuan', null);
            }

            $kodeKantor ??= $prospect['kode_kantor'];
            $requiredGroup ??= $prospectGroup;

            if ($kodeKantor !== $prospect['kode_kantor']) {
                sendResponse(400, 'Bulk delegasi hanya untuk prospek di cabang yang sama', null);
            }
            if ($requiredGroup !== $prospectGroup) {
                sendResponse(400, 'Bulk delegasi hanya untuk jenis prospek dengan AO tujuan yang sama', null);
            }
        }

        return [
            'prospects' => $prospects,
            'kode_kantor' => $kodeKantor,
            'group_jabatan' => $requiredGroup,
        ];
    }

    private function canUpdateSla(array $prospect, array $user): bool
    {
        if (($user['role'] ?? '') === 'developer') {
            return true;
        }

        if (!in_array($user['role'] ?? '', ['ao_kredit', 'ao_dana', 'ao_remedial'], true)) {
            return false;
        }

        return ($prospect['assigned_to'] ?? '') !== '' && ($prospect['assigned_to'] ?? '') === ($user['employee_id'] ?? '');
    }

    private function isUploadLocked(?string $uploadedAt): bool
    {
        if (!$uploadedAt) {
            return false;
        }

        return strtotime($uploadedAt . ' +7 days') < time();
    }

    /**
     * Helper: insert history record
     */
    private function addHistory(int $prospectId, string $action, ?string $oldStatus, ?string $newStatus, ?string $oldAssigned, ?string $newAssigned, ?string $note, ?array $metadata = null): void
    {
        $user = $this->getCurrentUser();
        $stmt = $this->db->prepare("INSERT INTO prospect_histories 
            (prospect_id, action, old_status, new_status, old_assigned_to, new_assigned_to, note, metadata, created_by) 
            VALUES (:pid, :action, :old_s, :new_s, :old_a, :new_a, :note, :meta, :by)");
        $stmt->execute([
            ':pid' => $prospectId,
            ':action' => $action,
            ':old_s' => $oldStatus,
            ':new_s' => $newStatus,
            ':old_a' => $oldAssigned,
            ':new_a' => $newAssigned,
            ':note' => $note,
            ':meta' => $metadata ? json_encode($metadata) : null,
            ':by' => $user['employee_id'] ?? 'system',
        ]);
    }


    // =========================================================
    // CREATE PROSPECT
    // =========================================================
    public function create(array $input): void
    {
        $user = $this->getCurrentUser();
        $employeeId = $user['employee_id'] ?? '';
        $userRole = $user['role'] ?? 'staff';
        $userPerms = $user['permissions'] ?? [];
        $userKodeKantor = $user['kode_kantor'] ?? '000';

        // Validasi wajib
        $type = $this->resolveProspectType($input);
        $name = trim($input['customer_name'] ?? '');
        $phone = trim($input['phone_number'] ?? '');
        $kodeKantor = trim($input['kode_kantor'] ?? $userKodeKantor);

        if ($type === '' || $name === '' || $phone === '') {
            sendResponse(400, 'prospect_type, customer_name, dan phone_number wajib diisi', null);
        }

        $validTypes = ['KREDIT','TABUNGAN','DEPOSITO','PEMBELI_ASET','DEBITUR_EXISTING'];
        if (!in_array($type, $validTypes)) {
            sendResponse(400, 'prospect_type tidak valid', null);
        }

        // Sumber input AO/non-AO mengikuti role user. Auto-delegasi hanya bila:
        // AO input prospek sesuai group AO-nya dan kode kantor prospek sama dengan kode kantor user.
        $userAoGroup = $this->getAoGroupForUser($user);
        $requiredGroup = $this->getAoGroupForProspectType($type) ?? 'AO';
        $isAoInput = $userAoGroup !== null;
        $canAutoDelegateToSelf = $isAoInput && $userAoGroup === $requiredGroup && $kodeKantor === $userKodeKantor;

        $delegationStatus = $canAutoDelegateToSelf ? 'SUDAH_DIDELEGASIKAN' : 'BELUM_DIDELEGASIKAN';
        $assignedTo = $canAutoDelegateToSelf ? $employeeId : null;
        $assignedBy = $canAutoDelegateToSelf ? $employeeId : null;
        $assignedAt = $canAutoDelegateToSelf ? date('Y-m-d H:i:s') : null;
        $fotoUrl = $input['foto_url'] ?? null;
        if (!$fotoUrl && !empty($input['foto_base64'])) {
            $mimeType = 'image/jpeg';
            if (preg_match('#^data:(image/(?:jpeg|png));base64,#i', (string)$input['foto_base64'], $matches)) {
                $mimeType = strtolower($matches[1]);
            }
            $savedFoto = $this->saveBase64Upload((string)$input['foto_base64'], $mimeType, "prospect_{$employeeId}", ['image/jpeg', 'image/png'], 'prospects');
            $fotoUrl = $savedFoto['path'];
        }

        $stmt = $this->db->prepare("INSERT INTO prospects (
            prospect_type, customer_name, identity_number, phone_number,
            jenis_usaha, rekomendasi_produk, keterangan_usaha, provinsi, kab_kota, kecamatan, desa,
            address, latitude, longitude, geo_address, foto_url,
            kode_kantor, description, created_by, created_by_kode_kantor, referral_by, is_ao_input,
            delegation_status, assigned_to, assigned_by, assigned_at, status, created_at
        ) VALUES (
            :type, :name, :identity, :phone,
            :jenis_usaha, :rekomendasi_produk, :keterangan_usaha, :prov, :kab, :kec, :desa,
            :address, :lat, :lng, :geo_addr, :foto,
            :kode_kantor, :desc, :created_by, :created_by_kk, :referral_by, :is_ao,
            :deleg_status, :assigned_to, :assigned_by, :assigned_at, 'OPEN', NOW()
        )");

        $stmt->execute([
            ':type' => $type,
            ':name' => $name,
            ':identity' => $input['identity_number'] ?? null,
            ':phone' => $phone,
            ':jenis_usaha' => $input['jenis_usaha'] ?? null,
            ':rekomendasi_produk' => $input['rekomendasi_produk'] ?? null,
            ':keterangan_usaha' => $input['keterangan_usaha'] ?? null,
            ':prov' => $input['provinsi'] ?? null,
            ':kab' => $input['kab_kota'] ?? null,
            ':kec' => $input['kecamatan'] ?? null,
            ':desa' => $input['desa'] ?? null,
            ':address' => $input['address'] ?? null,
            ':lat' => $input['latitude'] ?? null,
            ':lng' => $input['longitude'] ?? null,
            ':geo_addr' => $input['geo_address'] ?? null,
            ':foto' => $fotoUrl,
            ':kode_kantor' => $kodeKantor,
            ':desc' => $input['description'] ?? null,
            ':created_by' => $employeeId,
            ':created_by_kk' => $userKodeKantor,
            ':referral_by' => $employeeId,
            ':is_ao' => $isAoInput ? 1 : 0,
            ':deleg_status' => $delegationStatus,
            ':assigned_to' => $assignedTo,
            ':assigned_by' => $assignedBy,
            ':assigned_at' => $assignedAt,
        ]);

        $newId = (int) $this->db->lastInsertId();

        // History
        if ($canAutoDelegateToSelf) {
            $note = "Prospek {$type} dibuat oleh {$requiredGroup} cabang {$kodeKantor} dan otomatis didelegasikan ke pembuat";
            $this->addHistory($newId, 'CREATED', null, 'OPEN', null, $employeeId, $note, ['auto_delegated' => true]);
        } else {
            $note = "Prospek {$type} dibuat dan menunggu delegasi ke {$requiredGroup} cabang {$kodeKantor}";
            $this->addHistory($newId, 'CREATED', null, 'OPEN', null, null, $note, ['auto_delegated' => false]);
        }

        sendResponse(201, 'Prospek berhasil disimpan', [
            'id' => $newId,
            'delegation_status' => $delegationStatus,
            'assigned_to' => $assignedTo,
        ]);
    }


    // =========================================================
    // LIST PROSPECTS (with full filter)
    // =========================================================
    public function list(array $params): void
    {
        $user = $this->getCurrentUser();
        $userRole = $user['role'] ?? 'staff';
        $userPerms = $user['permissions'] ?? [];
        $userKodeKantor = $user['kode_kantor'] ?? '000';
        $userAccessKorwil = $user['access_korwil'] ?? null;
        $filterSource = $params['source'] ?? null; // 'ao', 'non_ao', 'mine', 'all'
        $isMineSource = $filterSource === 'mine';
        $isPipelineCredit = (($params['pipeline_credit'] ?? '') === '1');

        // Base query
        $sql = "SELECT p.*, kk.nama_kantor, kk.korwil,
                    pc.id AS credit_pipeline_id,
                    pc.requested_loan_amount,
                    pc.current_stage AS credit_pipeline_stage,
                    pc.pipeline_status AS credit_pipeline_status,
                    DATEDIFF(NOW(), COALESCE(pc.sla_started_at, pc.confirmation_at, p.sla_started_at, p.assigned_at, p.created_at)) AS pipeline_days
                FROM prospects p
                " . $this->kodeKantorJoinSql('kk') . "
                LEFT JOIN prospect_credit_pipelines pc ON pc.prospect_id = p.id
                WHERE 1=1";
        $binds = [];

        // --- FILTER: Access control berdasarkan role ---
        // Pusat (000): Divisi Pemasaran, Operasional, Direksi, Komisaris = semua cabang
        // Pusat Divisi Remedial = hanya report remedial (tapi untuk prospek bisa semua)
        // Atasan cabang (PE, PS): hanya cabangnya
        // AO: hanya yang assigned ke dia
        // Staff: hanya yang dia input

        if ($isMineSource) {
            // Prospek Saya adalah tracking pribadi lintas cabang/kode kantor.
        } elseif ($userRole === 'developer') {
            // Full access, no filter
        } elseif ($userRole === 'superuser') {
            if ($userAccessKorwil) {
                $accessCodes = Database::getKodeKantorByKorwil($userAccessKorwil);
                if (!empty($accessCodes)) {
                    $placeholders = [];
                    foreach ($accessCodes as $i => $code) {
                        $key = ":access_korwil_{$i}";
                        $placeholders[] = $key;
                        $binds[$key] = $code;
                    }
                    $sql .= " AND p.kode_kantor IN (" . implode(',', $placeholders) . ")";
                } else {
                    $sql .= " AND 1=0";
                }
            } elseif ($userKodeKantor === '000') {
                // Pusat superuser: bisa lihat semua
            } else {
                // Cabang superuser: hanya cabangnya
                $sql .= " AND p.kode_kantor = :user_kk";
                $binds[':user_kk'] = $userKodeKantor;
            }
        } elseif (in_array($userRole, ['ao_kredit', 'ao_dana', 'ao_remedial'])) {
            // AO: lihat yang di-assign ke dia + yang dia input
            $sql .= " AND (p.assigned_to = :ao_id OR p.created_by = :ao_id2)";
            $binds[':ao_id'] = $user['employee_id'];
            $binds[':ao_id2'] = $user['employee_id'];
        } else {
            // Staff: hanya yang dia input sendiri
            $sql .= " AND p.created_by = :staff_id";
            $binds[':staff_id'] = $user['employee_id'];
        }

        // --- FILTER: Kode Kantor (konsolidasi / korwil / cabang) ---
        $filterKorwil = $params['korwil'] ?? null;
        $filterKodeKantor = $params['kode_kantor'] ?? null;

        if (!$isMineSource && $filterKodeKantor && $filterKodeKantor !== 'all') {
            $sql .= " AND p.kode_kantor = :flt_kk";
            $binds[':flt_kk'] = $filterKodeKantor;
        } elseif (!$isMineSource && $filterKorwil && $filterKorwil !== 'all') {
            $korwilCodes = Database::getKodeKantorByKorwil($filterKorwil);
            if (!empty($korwilCodes)) {
                $placeholders = [];
                foreach ($korwilCodes as $i => $code) {
                    $key = ":korwil_{$i}";
                    $placeholders[] = $key;
                    $binds[$key] = $code;
                }
                $sql .= " AND p.kode_kantor IN (" . implode(',', $placeholders) . ")";
            }
        }

        // --- FILTER: prospect_type ---
        $filterType = $params['prospect_type'] ?? null;
        if ($filterType && $filterType !== 'all') {
            $sql .= " AND p.prospect_type = :flt_type";
            $binds[':flt_type'] = $filterType;
        }

        // --- FILTER: status ---
        $filterStatus = $params['status'] ?? null;
        if ($filterStatus && $filterStatus !== 'all') {
            $sql .= " AND p.status = :flt_status";
            $binds[':flt_status'] = $filterStatus;
        }

        // --- FILTER: delegation_status ---
        $filterDelegasi = $params['delegation'] ?? null;
        if ($filterDelegasi && $filterDelegasi !== 'all') {
            $sql .= " AND p.delegation_status = :flt_deleg";
            $binds[':flt_deleg'] = $filterDelegasi;
        }

        if ($isPipelineCredit) {
            $sql .= " AND p.prospect_type IN ('KREDIT','DEBITUR_EXISTING')
                AND p.delegation_status = 'SUDAH_DIDELEGASIKAN'
                AND COALESCE(p.assigned_to, '') <> ''";
        }

        $filterAssignedTo = trim((string)($params['assigned_to'] ?? ''));
        if ($filterAssignedTo !== '' && $filterAssignedTo !== 'all') {
            $sql .= " AND p.assigned_to = :flt_assigned_to";
            $binds[':flt_assigned_to'] = $filterAssignedTo;
        }

        // --- FILTER: AO vs Non-AO (is_ao_input) ---
        if ($filterSource === 'ao') {
            $sql .= " AND p.is_ao_input = 1";
        } elseif ($filterSource === 'non_ao') {
            $sql .= " AND p.is_ao_input = 0";
        } elseif ($filterSource === 'mine') {
            $sql .= " AND p.created_by = :source_created_by";
            $binds[':source_created_by'] = $user['employee_id'] ?? '';
        }

        // --- FILTER: Periode berjalan (created_at > closing_date dan <= harian_date) ---
        $closingDate = $params['closing_date'] ?? null;
        $harianDate = $params['harian_date'] ?? null;
        if (!$isMineSource && ($closingDate || $harianDate)) {
            if ($closingDate) {
                $sql .= " AND p.created_at > :closing_date_limit";
                $binds[':closing_date_limit'] = $closingDate . ' 23:59:59';
            }
            if ($harianDate) {
                $sql .= " AND p.created_at <= :harian_date_limit";
                $binds[':harian_date_limit'] = $harianDate . ' 23:59:59';
            }
        } elseif (!$isMineSource) {
            // Backward compatible untuk query lama.
            $dateFrom = $params['date_from'] ?? null;
            $dateTo = $params['date_to'] ?? null;
            if ($dateFrom) {
                $sql .= " AND p.created_at >= :date_from";
                $binds[':date_from'] = $dateFrom . ' 00:00:00';
            }
            if ($dateTo) {
                $sql .= " AND p.created_at <= :date_to";
                $binds[':date_to'] = $dateTo . ' 23:59:59';
            }

            $closingFrom = $params['closing_from'] ?? null;
            $closingTo = $params['closing_to'] ?? null;
            if ($closingFrom) {
                $sql .= " AND p.closed_at >= :cl_from";
                $binds[':cl_from'] = $closingFrom . ' 00:00:00';
            }
            if ($closingTo) {
                $sql .= " AND p.closed_at <= :cl_to";
                $binds[':cl_to'] = $closingTo . ' 23:59:59';
            }
        }

        // --- FILTER: Search (nama nasabah) ---
        $search = $params['search'] ?? null;
        if ($search && trim($search) !== '') {
            $sql .= " AND (p.customer_name LIKE :search OR p.phone_number LIKE :search2)";
            $binds[':search'] = '%' . trim($search) . '%';
            $binds[':search2'] = '%' . trim($search) . '%';
        }

        // --- ORDER & PAGINATION ---
        $orderBy = $params['order_by'] ?? 'created_at';
        $orderDir = strtoupper($params['order_dir'] ?? 'DESC') === 'ASC' ? 'ASC' : 'DESC';
        $allowedOrders = ['created_at', 'closed_at', 'customer_name', 'status'];
        if (!in_array($orderBy, $allowedOrders)) $orderBy = 'created_at';
        $sql .= " ORDER BY p.{$orderBy} {$orderDir}";

        $page = max(1, (int)($params['page'] ?? 1));
        $limit = min(100, max(1, (int)($params['limit'] ?? 25)));
        $offset = ($page - 1) * $limit;
        $sql .= " LIMIT {$limit} OFFSET {$offset}";

        // Execute
        $stmt = $this->db->prepare($sql);
        $stmt->execute($binds);
        $data = $stmt->fetchAll();
        foreach ($data as &$row) {
            $row['assigned_to_name'] = $this->getEmployeeDisplayName($row['assigned_to'] ?? null);
        }
        unset($row);

        // Count total (tanpa LIMIT)
        $countSql = preg_replace('/ORDER BY.*$/s', '', preg_replace('/^SELECT.*?FROM/s', 'SELECT COUNT(*) as total FROM', $sql));
        $countSql = preg_replace('/LIMIT.*$/s', '', $countSql);
        $countStmt = $this->db->prepare($countSql);
        $countStmt->execute($binds);
        $total = (int)($countStmt->fetch()['total'] ?? 0);

        sendResponse(200, 'OK', [
            'items' => $data,
            'pagination' => [
                'page' => $page,
                'limit' => $limit,
                'total' => $total,
                'total_pages' => ceil($total / $limit),
            ]
        ]);
    }


    // =========================================================
    // DETAIL PROSPECT (with follow_ups, histories, sla_logs)
    // =========================================================
    public function detail(int $id): void
    {
        $user = $this->getCurrentUser();
        $stmt = $this->db->prepare("SELECT p.*, kk.nama_kantor, kk.korwil 
            FROM prospects p 
            " . $this->kodeKantorJoinSql('kk') . " 
            WHERE p.id = :id");
        $stmt->execute([':id' => $id]);
        $prospect = $stmt->fetch();

        if (!$prospect) {
            sendResponse(404, 'Prospek tidak ditemukan', null);
        }

        if (!$this->canAccessProspect($prospect, $user)) {
            sendResponse(403, 'Anda tidak punya akses ke prospek ini', null);
        }

        $prospect['created_by_name'] = $this->getEmployeeDisplayName($prospect['created_by'] ?? null);
        $prospect['assigned_to_name'] = $this->getEmployeeDisplayName($prospect['assigned_to'] ?? null);

        // Get follow ups
        $fuStmt = $this->db->prepare("SELECT * FROM prospect_follow_ups WHERE prospect_id = :id ORDER BY follow_up_date DESC");
        $fuStmt->execute([':id' => $id]);
        $prospect['follow_ups'] = $fuStmt->fetchAll();

        // Get histories
        $hStmt = $this->db->prepare("SELECT * FROM prospect_histories WHERE prospect_id = :id ORDER BY created_at ASC");
        $hStmt->execute([':id' => $id]);
        $prospect['histories'] = $hStmt->fetchAll();

        // Get SLA logs (khusus kredit yang sudah SLA)
        if (in_array($prospect['prospect_type'], ['KREDIT', 'DEBITUR_EXISTING']) && $prospect['sla_started_at']) {
            $slaStmt = $this->db->prepare("SELECT * FROM prospect_sla_logs WHERE prospect_id = :id ORDER BY stage_started_at ASC");
            $slaStmt->execute([':id' => $id]);
            $prospect['sla_logs'] = $slaStmt->fetchAll();

            // Hitung total durasi SLA
            if ($prospect['closed_at']) {
                $slaStart = new DateTime($prospect['sla_started_at']);
                $closed = new DateTime($prospect['closed_at']);
                $prospect['sla_duration_days'] = $slaStart->diff($closed)->days;
            } elseif ($prospect['rejected_at']) {
                $slaStart = new DateTime($prospect['sla_started_at']);
                $rejected = new DateTime($prospect['rejected_at']);
                $prospect['sla_duration_days'] = $slaStart->diff($rejected)->days;
            } else {
                $slaStart = new DateTime($prospect['sla_started_at']);
                $now = new DateTime();
                $prospect['sla_duration_days'] = $slaStart->diff($now)->days;
                $prospect['sla_status'] = 'ACTIVE';
            }
        }

        if ($this->isCreditProspect($prospect)) {
            $pipelineStmt = $this->db->prepare("SELECT * FROM prospect_credit_pipelines WHERE prospect_id = :id");
            $pipelineStmt->execute([':id' => $id]);
            $pipeline = $pipelineStmt->fetch();
            if ($pipeline) {
                $docStmt = $this->db->prepare("SELECT * FROM prospect_credit_pipeline_documents WHERE pipeline_id = :id ORDER BY id ASC");
                $docStmt->execute([':id' => $pipeline['id']]);
                $stageStmt = $this->db->prepare("SELECT * FROM prospect_credit_pipeline_stages WHERE pipeline_id = :id ORDER BY stage_started_at ASC, id ASC");
                $stageStmt->execute([':id' => $pipeline['id']]);
                $pipeline['documents'] = $docStmt->fetchAll();
                $pipeline['stages'] = $stageStmt->fetchAll();

                if (!empty($pipeline['sla_started_at'])) {
                    $slaStart = new DateTime($pipeline['sla_started_at']);
                    $slaEndValue = $prospect['closed_at'] ?: ($prospect['rejected_at'] ?: date('Y-m-d H:i:s'));
                    $slaEnd = new DateTime($slaEndValue);
                    $prospect['sla_duration_days'] = $slaStart->diff($slaEnd)->days;
                }
                $prospect['credit_pipeline'] = $pipeline;
            }
        }

        sendResponse(200, 'OK', $prospect);
    }

    // =========================================================
    // DELEGATE PROSPECT (Superuser)
    // =========================================================
    public function delegate(array $input): void
    {
        $user = $this->getCurrentUser();
        $prospectId = (int)($input['prospect_id'] ?? 0);
        $assignedTo = trim($input['assigned_to'] ?? '');

        if (!$this->canDelegateProspect($user)) {
            sendResponse(403, 'Delegasi hanya bisa dilakukan Developer atau Superuser cabang non-000', null);
        }

        if ($prospectId <= 0 || $assignedTo === '') {
            sendResponse(400, 'prospect_id dan assigned_to wajib diisi', null);
        }

        // Get prospect
        $stmt = $this->db->prepare("SELECT * FROM prospects WHERE id = :id");
        $stmt->execute([':id' => $prospectId]);
        $prospect = $stmt->fetch();

        if (!$prospect) sendResponse(404, 'Prospek tidak ditemukan', null);
        if (!$this->canAccessProspect($prospect, $user)) {
            sendResponse(403, 'Anda tidak punya akses delegasi prospek ini', null);
        }
        if ($prospect['delegation_status'] === 'SUDAH_DIDELEGASIKAN') {
            sendResponse(400, 'Prospek sudah didelegasikan sebelumnya', null);
        }

        $requiredGroup = $this->getAoGroupForProspectType($prospect['prospect_type']);
        if ($requiredGroup !== null) {
            $candidates = $this->getAoCandidates($prospect['kode_kantor'], $requiredGroup);
            $isValidAo = false;
            foreach ($candidates as $candidate) {
                if (($candidate['employee_id'] ?? '') === $assignedTo) {
                    $isValidAo = true;
                    break;
                }
            }

            if (!$isValidAo) {
                sendResponse(400, "AO tujuan harus {$requiredGroup} di cabang {$prospect['kode_kantor']}", null);
            }
        }

        // Update
        $now = date('Y-m-d H:i:s');
        $upd = $this->db->prepare("UPDATE prospects SET 
            delegation_status = 'SUDAH_DIDELEGASIKAN', 
            assigned_to = :to, assigned_by = :by, assigned_at = :at, updated_at = NOW() 
            WHERE id = :id");
        $upd->execute([
            ':to' => $assignedTo,
            ':by' => $user['employee_id'] ?? '',
            ':at' => $now,
            ':id' => $prospectId,
        ]);

        $this->addHistory($prospectId, 'DELEGATED', null, null, null, $assignedTo, 
            "Didelegasikan oleh " . ($user['full_name'] ?? 'Superuser'));

        sendResponse(200, 'Prospek berhasil didelegasikan', null);
    }

    public function delegateBulk(array $input): void
    {
        $user = $this->getCurrentUser();
        if (!$this->canDelegateProspect($user)) {
            sendResponse(403, 'Delegasi hanya bisa dilakukan Developer atau Superuser cabang non-000', null);
        }

        $ids = $input['prospect_ids'] ?? [];
        $assignedTo = trim((string)($input['assigned_to'] ?? ''));
        if (!is_array($ids) || $assignedTo === '') {
            sendResponse(400, 'prospect_ids dan assigned_to wajib diisi', null);
        }

        $bundle = $this->getProspectsForDelegation($ids, $user);
        $prospects = $bundle['prospects'];
        $kodeKantor = $bundle['kode_kantor'];
        $requiredGroup = $bundle['group_jabatan'];

        $candidates = $this->getAoCandidates($kodeKantor, $requiredGroup, $this->getSsoAoTypeForGroup($requiredGroup));
        $isValidAo = false;
        foreach ($candidates as $candidate) {
            if (($candidate['employee_id'] ?? '') === $assignedTo) {
                $isValidAo = true;
                break;
            }
        }

        if (!$isValidAo) {
            sendResponse(400, "AO tujuan harus {$requiredGroup} di cabang {$kodeKantor}", null);
        }

        $now = date('Y-m-d H:i:s');
        $this->db->beginTransaction();
        try {
            $upd = $this->db->prepare("UPDATE prospects SET
                delegation_status = 'SUDAH_DIDELEGASIKAN',
                assigned_to = :to,
                assigned_by = :by,
                assigned_at = :at,
                updated_at = NOW()
                WHERE id = :id");

            foreach ($prospects as $prospect) {
                $upd->execute([
                    ':to' => $assignedTo,
                    ':by' => $user['employee_id'] ?? '',
                    ':at' => $now,
                    ':id' => $prospect['id'],
                ]);

                $this->addHistory((int)$prospect['id'], 'DELEGATED', null, null, null, $assignedTo,
                    "Didelegasikan massal oleh " . ($user['full_name'] ?? 'Superuser'),
                    ['bulk_delegation' => true, 'total_selected' => count($prospects)]);
            }

            $this->db->commit();
        } catch (\Exception $e) {
            $this->db->rollBack();
            sendResponse(500, 'Gagal memproses bulk delegasi', null);
        }

        sendResponse(200, count($prospects) . ' prospek berhasil didelegasikan', [
            'assigned_to' => $assignedTo,
            'total' => count($prospects),
        ]);
    }

    public function aoWorkload(array $params): void
    {
        $user = $this->getCurrentUser();
        if (!$this->canDelegateProspect($user)) {
            sendResponse(403, 'Hanya delegator yang bisa melihat beban AO', null);
        }

        $ids = [];
        if (!empty($params['prospect_ids'])) {
            $ids = array_map('intval', explode(',', (string)$params['prospect_ids']));
        }

        if (!empty($ids)) {
            $bundle = $this->getProspectsForDelegation($ids, $user);
            $kodeKantor = $bundle['kode_kantor'];
            $groupJabatan = $bundle['group_jabatan'];
        } else {
            $kodeKantor = $params['kode_kantor'] ?? null;
            $groupJabatan = $params['group_jabatan'] ?? null;
        }

        if (!$kodeKantor || !$groupJabatan) {
            sendResponse(400, 'kode_kantor/group_jabatan atau prospect_ids wajib diisi', null);
        }

        $candidates = $this->getAoCandidates($kodeKantor, $groupJabatan, $this->getSsoAoTypeForGroup($groupJabatan));
        sendResponse(200, 'OK', [
            'kode_kantor' => $kodeKantor,
            'group_jabatan' => $groupJabatan,
            'selected_count' => count($ids),
            'workload_limit' => max(1, (int) env('AO_WORKLOAD_LIMIT', 10)),
            'ao' => $this->attachAoWorkloads($candidates),
        ]);
    }


    // =========================================================
    // FOLLOW UP
    // =========================================================
    public function followUp(array $input): void
    {
        $user = $this->getCurrentUser();
        $prospectId = (int)($input['prospect_id'] ?? 0);
        $method = $input['method'] ?? '';
        $result = trim($input['result'] ?? '');

        if ($prospectId <= 0 || $method === '' || $result === '') {
            sendResponse(400, 'prospect_id, method, dan result wajib diisi', null);
        }

        // Get prospect & validate
        $stmt = $this->db->prepare("SELECT * FROM prospects WHERE id = :id");
        $stmt->execute([':id' => $prospectId]);
        $prospect = $stmt->fetch();
        if (!$prospect) sendResponse(404, 'Prospek tidak ditemukan', null);

        // Insert follow up
        $fuStmt = $this->db->prepare("INSERT INTO prospect_follow_ups 
            (prospect_id, follow_up_date, method, result, note, next_plan, next_follow_up_date, created_by)
            VALUES (:pid, :date, :method, :result, :note, :plan, :next_date, :by)");
        $fuStmt->execute([
            ':pid' => $prospectId,
            ':date' => $input['follow_up_date'] ?? date('Y-m-d'),
            ':method' => $method,
            ':result' => $result,
            ':note' => $input['note'] ?? null,
            ':plan' => $input['next_plan'] ?? null,
            ':next_date' => $input['next_follow_up_date'] ?? null,
            ':by' => $user['employee_id'] ?? '',
        ]);

        // Update status to FOLLOW_UP if still OPEN
        if ($prospect['status'] === 'OPEN') {
            $this->db->prepare("UPDATE prospects SET status = 'FOLLOW_UP', updated_at = NOW() WHERE id = :id")
                ->execute([':id' => $prospectId]);
            $this->addHistory($prospectId, 'STATUS_CHANGED', 'OPEN', 'FOLLOW_UP', null, null, 'Status otomatis berubah setelah follow up pertama');
        }

        $this->addHistory($prospectId, 'FOLLOW_UP', null, null, null, null, 
            "Follow up via {$method}: {$result}");

        sendResponse(200, 'Follow up berhasil disimpan', null);
    }

    // =========================================================
    // CHANGE STATUS (to SLA)
    // =========================================================
    public function changeStatus(array $input): void
    {
        $user = $this->getCurrentUser();
        $prospectId = (int)($input['prospect_id'] ?? 0);
        $newStatus = strtoupper(trim($input['new_status'] ?? ''));

        if ($prospectId <= 0 || $newStatus === '') {
            sendResponse(400, 'prospect_id dan new_status wajib diisi', null);
        }

        $stmt = $this->db->prepare("SELECT * FROM prospects WHERE id = :id");
        $stmt->execute([':id' => $prospectId]);
        $prospect = $stmt->fetch();
        if (!$prospect) sendResponse(404, 'Prospek tidak ditemukan', null);

        $oldStatus = $prospect['status'];

        // Validasi SLA hanya untuk KREDIT dan DEBITUR_EXISTING
        if ($newStatus === 'SLA') {
            $this->completeCreditDocumentation($input);
            return;
        }

        // Generic status change (FOLLOW_UP)
        if ($newStatus === 'FOLLOW_UP') {
            $this->db->prepare("UPDATE prospects SET status = 'FOLLOW_UP', updated_at = NOW() WHERE id = :id")
                ->execute([':id' => $prospectId]);
            $this->addHistory($prospectId, 'STATUS_CHANGED', $oldStatus, 'FOLLOW_UP', null, null, 'Status diubah ke Follow Up');
            sendResponse(200, 'Status berhasil diubah ke Follow Up', null);
        }

        sendResponse(400, 'new_status tidak valid. Gunakan: FOLLOW_UP, SLA', null);
    }

    public function confirmCreditInterest(array $input): void
    {
        $user = $this->getCurrentUser();
        $prospectId = (int)($input['prospect_id'] ?? 0);
        $requestedLoanAmount = (int)($input['requested_loan_amount'] ?? 0);
        if ($prospectId <= 0) sendResponse(400, 'prospect_id wajib diisi', null);
        if ($requestedLoanAmount <= 0) sendResponse(400, 'Plafon pinjaman wajib diisi', null);

        $stmt = $this->db->prepare("SELECT * FROM prospects WHERE id = :id");
        $stmt->execute([':id' => $prospectId]);
        $prospect = $stmt->fetch();
        if (!$prospect) sendResponse(404, 'Prospek tidak ditemukan', null);
        if (!$this->canAccessProspect($prospect, $user)) sendResponse(403, 'Anda tidak punya akses ke prospek ini', null);
        if (!$this->canUpdateSla($prospect, $user)) sendResponse(403, 'Hanya AO pengelola yang bisa update SLA', null);
        if (!$this->isCreditProspect($prospect)) sendResponse(400, 'Pipeline kredit hanya untuk prospek kredit', null);
        if ($prospect['delegation_status'] !== 'SUDAH_DIDELEGASIKAN') sendResponse(400, 'Prospek harus didelegasikan ke AO lebih dulu', null);
        if (in_array($prospect['status'], ['CLOSING', 'REJECT'], true)) sendResponse(400, 'Prospek sudah selesai', null);

        $pipelineId = $this->ensureCreditPipeline($prospect, $user, $input['note'] ?? null, $requestedLoanAmount);
        if ($prospect['status'] === 'OPEN') {
            $this->db->prepare("UPDATE prospects SET status = 'FOLLOW_UP', updated_at = NOW() WHERE id = :id")
                ->execute([':id' => $prospectId]);
        }

        $this->addHistory($prospectId, 'CREDIT_PIPELINE_CREATED', $prospect['status'], 'FOLLOW_UP', null, null,
            'Debitur konfirmasi berminat, pipeline kredit dibuat', [
                'pipeline_id' => $pipelineId,
                'requested_loan_amount' => $requestedLoanAmount,
            ]);

        sendResponse(200, 'Pipeline kredit berhasil dibuat', [
            'pipeline_id' => $pipelineId,
            'requested_loan_amount' => $requestedLoanAmount,
        ]);
    }

    public function completeCreditDocumentation(array $input): void
    {
        $user = $this->getCurrentUser();
        $prospectId = (int)($input['prospect_id'] ?? 0);
        if ($prospectId <= 0) sendResponse(400, 'prospect_id wajib diisi', null);

        $stmt = $this->db->prepare("SELECT * FROM prospects WHERE id = :id");
        $stmt->execute([':id' => $prospectId]);
        $prospect = $stmt->fetch();
        if (!$prospect) sendResponse(404, 'Prospek tidak ditemukan', null);
        if (!$this->canAccessProspect($prospect, $user)) sendResponse(403, 'Anda tidak punya akses ke prospek ini', null);
        if (!$this->canUpdateSla($prospect, $user)) sendResponse(403, 'Hanya AO pengelola yang bisa update SLA', null);
        if (!$this->isCreditProspect($prospect)) sendResponse(400, 'SLA hanya untuk prospek kredit', null);

        $pipelineId = $this->ensureCreditPipeline($prospect, $user, 'Pipeline dibuat saat pemberkasan dinyatakan lengkap');
        $now = date('Y-m-d H:i:s');
        $deadline = date('Y-m-d H:i:s', strtotime($now . ' +14 days'));

        $missingStmt = $this->db->prepare("SELECT COUNT(*) FROM prospect_credit_pipeline_documents
            WHERE pipeline_id = :pipeline_id
              AND doc_code IN ('FORMULIR', 'KTP_SUAMI_ISTRI')
              AND is_completed = 0");
        $missingStmt->execute([':pipeline_id' => $pipelineId]);
        if ((int)$missingStmt->fetchColumn() > 0) {
            sendResponse(400, 'Minimal formulir dan KTP suami/istri wajib diupload sebelum SLA dimulai.', null);
        }

        $this->db->prepare("UPDATE prospect_credit_pipeline_stages SET stage_ended_at = :ended,
            duration_days = DATEDIFF(:ended2, stage_started_at)
            WHERE pipeline_id = :pipeline_id AND stage_ended_at IS NULL")
            ->execute([':ended' => $now, ':ended2' => $now, ':pipeline_id' => $pipelineId]);

        $this->db->prepare("UPDATE prospect_credit_pipelines SET
            documents_completed_at = COALESCE(documents_completed_at, :docs_at),
            sla_started_at = COALESCE(sla_started_at, :sla_at),
            sla_deadline_at = COALESCE(sla_deadline_at, :deadline_at),
            current_stage = 'PEMBERKASAN',
            pipeline_status = 'SLA_RUNNING',
            updated_at = NOW()
            WHERE id = :id")
            ->execute([
                ':docs_at' => $now,
                ':sla_at' => $now,
                ':deadline_at' => $deadline,
                ':id' => $pipelineId,
            ]);

        $this->db->prepare("UPDATE prospects SET status = 'SLA',
            sla_started_at = COALESCE(sla_started_at, :sla),
            sla_started_by = COALESCE(sla_started_by, :by),
            updated_at = NOW()
            WHERE id = :id")
            ->execute([':sla' => $now, ':by' => $user['employee_id'] ?? '', ':id' => $prospectId]);

        $this->db->prepare("INSERT INTO prospect_credit_pipeline_stages
            (pipeline_id, prospect_id, stage, stage_started_at, sla_counted, note, created_by)
            VALUES (:pipeline_id, :prospect_id, 'PEMBERKASAN', :started_at, 1, :note, :created_by)")
            ->execute([
                ':pipeline_id' => $pipelineId,
                ':prospect_id' => $prospectId,
                ':started_at' => $now,
                ':note' => $input['note'] ?? 'Pemberkasan lengkap, SLA mulai dihitung',
                ':created_by' => $user['employee_id'] ?? '',
            ]);

        $legacyCount = $this->db->prepare("SELECT COUNT(*) FROM prospect_sla_logs WHERE prospect_id = :pid");
        $legacyCount->execute([':pid' => $prospectId]);
        if ((int)$legacyCount->fetchColumn() === 0) {
            $this->db->prepare("INSERT INTO prospect_sla_logs
                (prospect_id, stage, stage_started_at, note, created_by)
                VALUES (:pid, 'VERIFIKASI_DATA', :started, 'Pemberkasan lengkap, SLA mulai dihitung', :by)")
                ->execute([':pid' => $prospectId, ':started' => $now, ':by' => $user['employee_id'] ?? '']);
        }

        $this->addHistory($prospectId, 'SLA_STARTED', $prospect['status'], 'SLA', null, null,
            'Pemberkasan lengkap, SLA kredit mulai dihitung', ['pipeline_id' => $pipelineId, 'sla_started_at' => $now]);

        sendResponse(200, 'Pemberkasan lengkap, SLA kredit mulai dihitung', ['pipeline_id' => $pipelineId, 'sla_started_at' => $now]);
    }

    public function uploadCreditPipelineFile(array $input): void
    {
        $user = $this->getCurrentUser();
        $prospectId = (int)($input['prospect_id'] ?? 0);
        $target = strtoupper(trim($input['target'] ?? 'DOCUMENT'));
        $fileBase64 = $input['file_base64'] ?? '';
        $mimeType = $input['mime_type'] ?? '';

        if ($prospectId <= 0 || $fileBase64 === '' || $mimeType === '') {
            sendResponse(400, 'prospect_id, file_base64, dan mime_type wajib diisi', null);
        }

        $stmt = $this->db->prepare("SELECT * FROM prospects WHERE id = :id");
        $stmt->execute([':id' => $prospectId]);
        $prospect = $stmt->fetch();
        if (!$prospect) sendResponse(404, 'Prospek tidak ditemukan', null);
        if (!$this->canAccessProspect($prospect, $user)) sendResponse(403, 'Anda tidak punya akses ke prospek ini', null);
        if (!$this->canUpdateSla($prospect, $user)) sendResponse(403, 'Hanya AO pengelola yang bisa update SLA', null);
        if (!$this->isCreditProspect($prospect)) sendResponse(400, 'Upload pipeline hanya untuk prospek kredit', null);

        $pipelineStmt = $this->db->prepare("SELECT * FROM prospect_credit_pipelines WHERE prospect_id = :pid");
        $pipelineStmt->execute([':pid' => $prospectId]);
        $pipeline = $pipelineStmt->fetch();
        if (!$pipeline) sendResponse(400, 'Pipeline kredit belum dibuat', null);

        if ($target === 'DOCUMENT') {
            $docCode = strtoupper(trim($input['doc_code'] ?? ''));
            if ($docCode === '') sendResponse(400, 'doc_code wajib diisi', null);

            $docStmt = $this->db->prepare("SELECT * FROM prospect_credit_pipeline_documents WHERE pipeline_id = :pipeline_id AND doc_code = :doc_code");
            $docStmt->execute([':pipeline_id' => $pipeline['id'], ':doc_code' => $docCode]);
            $doc = $docStmt->fetch();
            if (!$doc) sendResponse(404, 'Dokumen pipeline tidak ditemukan', null);
            if (!empty($doc['file_url']) && $this->isUploadLocked($doc['completed_at'] ?? null)) {
                sendResponse(400, 'Berkas sudah melewati 7 hari dan tidak bisa diupload ulang', null);
            }

            $allowed = $docCode === 'FORMULIR' ? ['image/jpeg', 'image/png'] : ['application/pdf'];
            $saved = $this->saveBase64Upload($fileBase64, $mimeType, "doc_{$prospectId}_{$docCode}", $allowed);

            $this->db->prepare("UPDATE prospect_credit_pipeline_documents SET
                is_completed = 1, completed_at = NOW(), file_url = :file_url, file_type = :file_type, note = :note, updated_at = NOW()
                WHERE id = :id")
                ->execute([
                    ':file_url' => $saved['path'],
                    ':file_type' => $saved['type'],
                    ':note' => $input['note'] ?? null,
                    ':id' => $doc['id'],
                ]);

            if (in_array($docCode, ['FORMULIR', 'KTP_SUAMI_ISTRI'], true)) {
                $this->addHistory($prospectId, 'CREDIT_DOC_UPLOADED', null, null, null, null,
                    "Upload berkas kredit: {$doc['doc_name']}", ['doc_code' => $docCode, 'file_type' => $saved['type']]);
            }

            sendResponse(200, 'Berkas kredit berhasil diupload', $saved);
        }

        if ($target === 'STAGE_ATTACHMENT') {
            $stage = strtoupper(trim($input['stage'] ?? ''));
            if (!in_array($stage, ['SURVEY', 'ANALISA', 'KOMITE'], true)) {
                sendResponse(400, 'Tahap lampiran tidak valid', null);
            }

            $stageStmt = $this->db->prepare("SELECT * FROM prospect_credit_pipeline_stages
                WHERE pipeline_id = :pipeline_id AND stage = :stage
                ORDER BY stage_started_at DESC, id DESC LIMIT 1");
            $stageStmt->execute([':pipeline_id' => $pipeline['id'], ':stage' => $stage]);
            $stageRow = $stageStmt->fetch();
            if (!$stageRow) sendResponse(404, 'Tahap SLA belum tersedia', null);
            if (!empty($stageRow['attachment_url']) && $this->isUploadLocked($stageRow['attachment_uploaded_at'] ?? null)) {
                sendResponse(400, 'Lampiran tahap sudah melewati 7 hari dan tidak bisa diupload ulang', null);
            }

            $allowed = $stage === 'SURVEY' ? ['image/jpeg', 'image/png'] : ['application/pdf'];
            $saved = $this->saveBase64Upload($fileBase64, $mimeType, "stage_{$prospectId}_" . strtolower($stage), $allowed);

            $this->db->prepare("UPDATE prospect_credit_pipeline_stages SET
                attachment_url = :url, attachment_type = :type, attachment_uploaded_at = NOW(), note = COALESCE(:note, note)
                WHERE id = :id")
                ->execute([
                    ':url' => $saved['path'],
                    ':type' => $saved['type'],
                    ':note' => $input['note'] ?? null,
                    ':id' => $stageRow['id'],
                ]);

            sendResponse(200, 'Lampiran tahap SLA berhasil diupload', $saved);
        }

        sendResponse(400, 'Target upload tidak valid', null);
    }


    // =========================================================
    // CLOSE PROSPECT
    // =========================================================
    public function close(array $input): void
    {
        $user = $this->getCurrentUser();
        $prospectId = (int)($input['prospect_id'] ?? 0);

        if ($prospectId <= 0) sendResponse(400, 'prospect_id wajib diisi', null);

        $stmt = $this->db->prepare("SELECT * FROM prospects WHERE id = :id");
        $stmt->execute([':id' => $prospectId]);
        $prospect = $stmt->fetch();
        if (!$prospect) sendResponse(404, 'Prospek tidak ditemukan', null);

        $type = $prospect['prospect_type'];
        $oldStatus = $prospect['status'];

        // Validasi closing sesuai produk
        if ($type === 'KREDIT' || $type === 'DEBITUR_EXISTING') {
            $accountNum = trim($input['closing_account_number'] ?? '');
            $amount = (int)($input['closing_realization_amount'] ?? 0);
            if ($accountNum === '') sendResponse(400, 'Closing kredit wajib input nomor rekening', null);
            if ($amount <= 0) sendResponse(400, 'Closing kredit wajib input nominal realisasi pencairan', null);
            if ($prospect['status'] !== 'SLA') sendResponse(400, 'Closing kredit dilakukan setelah masuk SLA/pipeline kredit', null);
        } elseif ($type === 'TABUNGAN' || $type === 'DEPOSITO') {
            $accountNum = trim($input['closing_account_number'] ?? '');
            $amount = (int)($input['closing_realization_amount'] ?? 0);
            if ($accountNum === '') sendResponse(400, 'Closing tabungan/deposito wajib input nomor rekening', null);
            if ($amount <= 0) sendResponse(400, 'Closing tabungan/deposito wajib input nominal setoran/deposito', null);
        } elseif ($type === 'PEMBELI_ASET') {
            $buyer = trim($input['closing_buyer_name'] ?? '');
            $method = strtoupper(trim($input['closing_asset_purchase_method'] ?? ''));
            if ($buyer === '') sendResponse(400, 'Closing aset wajib input nama pembeli', null);
            if (!in_array($method, ['LELANG', 'CESSIE', 'LAINNYA'], true)) {
                sendResponse(400, 'Metode pembelian aset wajib dipilih: LELANG, CESSIE, atau LAINNYA', null);
            }
        }

        $now = date('Y-m-d H:i:s');

        $upd = $this->db->prepare("UPDATE prospects SET 
            status = 'CLOSING', closed_at = :closed, 
            closing_account_number = :acc, closing_realization_amount = :amount,
            closing_tenor = :tenor, closing_note = :note,
            closing_asset_name = :asset, closing_buyer_name = :buyer,
            closing_asset_purchase_method = :asset_method,
            updated_at = NOW() WHERE id = :id");
        $upd->execute([
            ':closed' => $now,
            ':acc' => $input['closing_account_number'] ?? null,
            ':amount' => (int)($input['closing_realization_amount'] ?? 0) ?: null,
            ':tenor' => (int)($input['closing_tenor'] ?? 0) ?: null,
            ':note' => $input['closing_note'] ?? null,
            ':asset' => $input['closing_asset_name'] ?? null,
            ':buyer' => $input['closing_buyer_name'] ?? null,
            ':asset_method' => strtoupper(trim($input['closing_asset_purchase_method'] ?? '')) ?: null,
            ':id' => $prospectId,
        ]);

        // Close any open SLA log
        if ($prospect['sla_started_at']) {
            $this->db->prepare("UPDATE prospect_sla_logs SET stage_ended_at = :ended, 
                duration_days = DATEDIFF(:ended2, stage_started_at) 
                WHERE prospect_id = :pid AND stage_ended_at IS NULL")
                ->execute([':ended' => $now, ':ended2' => $now, ':pid' => $prospectId]);
            $this->db->prepare("UPDATE prospect_credit_pipeline_stages SET stage_ended_at = :ended,
                duration_days = DATEDIFF(:ended2, stage_started_at)
                WHERE prospect_id = :pid AND stage_ended_at IS NULL")
                ->execute([':ended' => $now, ':ended2' => $now, ':pid' => $prospectId]);
            $this->db->prepare("UPDATE prospect_credit_pipelines SET current_stage = 'SELESAI',
                pipeline_status = 'DISBURSED', updated_at = NOW() WHERE prospect_id = :pid")
                ->execute([':pid' => $prospectId]);
        }

        $metadata = [
            'account_number' => $input['closing_account_number'] ?? null,
            'amount' => (int)($input['closing_realization_amount'] ?? 0),
            'tenor' => (int)($input['closing_tenor'] ?? 0),
            'asset_purchase_method' => strtoupper(trim($input['closing_asset_purchase_method'] ?? '')) ?: null,
        ];
        $this->addHistory($prospectId, 'CLOSED', $oldStatus, 'CLOSING', null, null, 
            'Prospek berhasil closing', $metadata);

        sendResponse(200, 'Prospek berhasil di-closing', null);
    }

    // =========================================================
    // REJECT PROSPECT
    // =========================================================
    public function reject(array $input): void
    {
        $user = $this->getCurrentUser();
        $prospectId = (int)($input['prospect_id'] ?? 0);
        $reason = trim($input['reject_reason'] ?? '');

        if ($prospectId <= 0) sendResponse(400, 'prospect_id wajib diisi', null);
        if ($reason === '') sendResponse(400, 'reject_reason wajib diisi', null);

        $stmt = $this->db->prepare("SELECT * FROM prospects WHERE id = :id");
        $stmt->execute([':id' => $prospectId]);
        $prospect = $stmt->fetch();
        if (!$prospect) sendResponse(404, 'Prospek tidak ditemukan', null);

        $oldStatus = $prospect['status'];
        $now = date('Y-m-d H:i:s');

        $upd = $this->db->prepare("UPDATE prospects SET 
            status = 'REJECT', rejected_at = :rej, reject_reason = :reason, reject_note = :note, updated_at = NOW() 
            WHERE id = :id");
        $upd->execute([
            ':rej' => $now,
            ':reason' => $reason,
            ':note' => $input['reject_note'] ?? null,
            ':id' => $prospectId,
        ]);

        // Close any open SLA log
        if ($prospect['sla_started_at']) {
            $this->db->prepare("UPDATE prospect_sla_logs SET stage_ended_at = :ended, 
                duration_days = DATEDIFF(:ended2, stage_started_at) 
                WHERE prospect_id = :pid AND stage_ended_at IS NULL")
                ->execute([':ended' => $now, ':ended2' => $now, ':pid' => $prospectId]);
            $this->db->prepare("UPDATE prospect_credit_pipeline_stages SET stage_ended_at = :ended,
                duration_days = DATEDIFF(:ended2, stage_started_at)
                WHERE prospect_id = :pid AND stage_ended_at IS NULL")
                ->execute([':ended' => $now, ':ended2' => $now, ':pid' => $prospectId]);
            $this->db->prepare("UPDATE prospect_credit_pipelines SET current_stage = 'BATAL',
                pipeline_status = 'REJECTED', updated_at = NOW() WHERE prospect_id = :pid")
                ->execute([':pid' => $prospectId]);
        }

        $this->addHistory($prospectId, 'REJECTED', $oldStatus, 'REJECT', null, null, 
            "Reject: {$reason}", ['reason' => $reason]);

        sendResponse(200, 'Prospek berhasil di-reject', null);
    }


    // =========================================================
    // SLA LOG (add stage to pipeline)
    // =========================================================
    public function addSlaLog(array $input): void
    {
        $user = $this->getCurrentUser();
        $prospectId = (int)($input['prospect_id'] ?? 0);
        $stage = strtoupper(trim($input['stage'] ?? ''));

        if ($prospectId <= 0 || $stage === '') {
            sendResponse(400, 'prospect_id dan stage wajib diisi', null);
        }

        $stageOrder = ['PEMBERKASAN', 'SURVEY', 'ANALISA', 'KOMITE'];
        if (!in_array($stage, $stageOrder, true)) {
            sendResponse(400, 'Tahap SLA kredit tidak valid', null);
        }

        $stmt = $this->db->prepare("SELECT * FROM prospects WHERE id = :id");
        $stmt->execute([':id' => $prospectId]);
        $prospect = $stmt->fetch();
        if (!$prospect) sendResponse(404, 'Prospek tidak ditemukan', null);
        if (!$this->canUpdateSla($prospect, $user)) sendResponse(403, 'Hanya AO pengelola yang bisa update SLA', null);
        if (!$this->isCreditProspect($prospect)) sendResponse(400, 'Tahap SLA hanya untuk prospek kredit', null);
        if ($prospect['status'] !== 'SLA') sendResponse(400, 'SLA dimulai setelah pemberkasan lengkap', null);

        $pipelineStmt = $this->db->prepare("SELECT * FROM prospect_credit_pipelines WHERE prospect_id = :pid");
        $pipelineStmt->execute([':pid' => $prospectId]);
        $pipeline = $pipelineStmt->fetch();
        if (!$pipeline) sendResponse(400, 'Pipeline kredit belum dibuat', null);

        $currentStage = $pipeline['current_stage'] ?? 'PEMBERKASAN';
        $currentIndex = array_search($currentStage, $stageOrder, true);
        if ($currentIndex === false) {
            sendResponse(400, 'Tahap pipeline saat ini tidak valid untuk proses SLA', null);
        }
        $nextStage = $stageOrder[$currentIndex + 1] ?? null;
        if ($nextStage === null) {
            sendResponse(400, 'Tahap SLA sudah sampai komite. Lanjutkan dengan closing jika sudah cair.', null);
        }
        if ($stage !== $nextStage) {
            sendResponse(400, "Tahap berikutnya harus {$nextStage}", null);
        }

        // Close previous open stage
        $now = date('Y-m-d H:i:s');
        $this->db->prepare("UPDATE prospect_credit_pipeline_stages SET stage_ended_at = :ended,
            duration_days = DATEDIFF(:ended2, stage_started_at) 
            WHERE pipeline_id = :pipeline_id AND stage_ended_at IS NULL")
            ->execute([':ended' => $now, ':ended2' => $now, ':pipeline_id' => $pipeline['id']]);

        // Insert new stage
        $attachment = null;
        if ($stage === 'SURVEY' && (empty($input['attachment_base64']) || empty($input['attachment_mime']))) {
            sendResponse(400, 'Tahap Survey wajib upload foto kunjungan/jaminan', null);
        }

        if (!empty($input['attachment_base64']) && !empty($input['attachment_mime'])) {
            if ($stage === 'SURVEY') {
                $attachment = $this->saveBase64Upload(
                    $input['attachment_base64'],
                    $input['attachment_mime'],
                    "stage_{$prospectId}_survey",
                    ['image/jpeg', 'image/png']
                );
            } elseif ($stage === 'ANALISA') {
                $attachment = $this->saveBase64Upload(
                    $input['attachment_base64'],
                    $input['attachment_mime'],
                    "stage_{$prospectId}_analisa",
                    ['application/pdf']
                );
            } elseif ($stage === 'KOMITE') {
                $attachment = $this->saveBase64Upload(
                    $input['attachment_base64'],
                    $input['attachment_mime'],
                    "stage_{$prospectId}_komite",
                    ['application/pdf']
                );
            } else {
                sendResponse(400, 'Upload file tidak tersedia untuk tahap ini', null);
            }
        }

        $ins = $this->db->prepare("INSERT INTO prospect_credit_pipeline_stages
            (pipeline_id, prospect_id, stage, stage_started_at, sla_counted, attachment_url, attachment_type, attachment_uploaded_at, note, created_by)
            VALUES (:pipeline_id, :pid, :stage, :started, 1, :attachment_url, :attachment_type, :attachment_uploaded_at, :note, :by)");
        $ins->execute([
            ':pipeline_id' => $pipeline['id'],
            ':pid' => $prospectId,
            ':stage' => $stage,
            ':started' => $now,
            ':attachment_url' => $attachment['path'] ?? null,
            ':attachment_type' => $attachment['type'] ?? null,
            ':attachment_uploaded_at' => $attachment ? $now : null,
            ':note' => $input['note'] ?? null,
            ':by' => $user['employee_id'] ?? '',
        ]);

        $this->db->prepare("UPDATE prospect_credit_pipelines SET current_stage = :stage,
            pipeline_status = :status, updated_at = NOW() WHERE id = :id")
            ->execute([':stage' => $stage, ':status' => 'SLA_RUNNING', ':id' => $pipeline['id']]);

        $legacyStage = match ($stage) {
            'PEMBERKASAN' => 'VERIFIKASI_DATA',
            'SURVEY' => 'SURVEI_JAMINAN',
            'ANALISA' => 'ANALISA_KREDIT',
            'KOMITE' => 'KOMITE_KREDIT',
            default => 'VERIFIKASI_DATA',
        };
        $this->db->prepare("UPDATE prospect_sla_logs SET stage_ended_at = :ended,
            duration_days = DATEDIFF(:ended2, stage_started_at)
            WHERE prospect_id = :pid AND stage_ended_at IS NULL")
            ->execute([':ended' => $now, ':ended2' => $now, ':pid' => $prospectId]);
        $this->db->prepare("INSERT INTO prospect_sla_logs
            (prospect_id, stage, stage_started_at, note, created_by)
            VALUES (:pid, :stage, :started, :note, :by)")
            ->execute([
                ':pid' => $prospectId,
                ':stage' => $legacyStage,
                ':started' => $now,
                ':note' => $input['note'] ?? null,
                ':by' => $user['employee_id'] ?? '',
            ]);

        $this->addHistory($prospectId, 'SLA_STAGE_CHANGED', 'SLA', 'SLA', null, null,
            "Tahap SLA kredit: {$stage}", ['pipeline_id' => $pipeline['id'], 'stage' => $stage]);

        sendResponse(200, 'Tahap SLA kredit berhasil diperbarui', null);
    }

    // =========================================================
    // SLA PIPELINE (list semua prospek yang sedang SLA)
    // =========================================================
    public function slaPipeline(array $params): void
    {
        $user = $this->getCurrentUser();
        $assignedTo = $params['assigned_to'] ?? $user['employee_id'] ?? '';

        $sql = "SELECT p.*, kk.nama_kantor,
                DATEDIFF(NOW(), p.sla_started_at) AS sla_days,
                (SELECT sl.stage FROM prospect_sla_logs sl WHERE sl.prospect_id = p.id AND sl.stage_ended_at IS NULL ORDER BY sl.stage_started_at DESC LIMIT 1) AS current_stage
                FROM prospects p 
                " . $this->kodeKantorJoinSql('kk') . "
                WHERE p.status = 'SLA'";
        $binds = [];

        // Filter by AO (kalau bukan superuser/developer)
        $userRole = $user['role'] ?? 'staff';
        if (!in_array($userRole, ['developer', 'superuser'])) {
            $sql .= " AND p.assigned_to = :ao";
            $binds[':ao'] = $assignedTo;
        } elseif (isset($params['assigned_to']) && $params['assigned_to'] !== 'all') {
            $sql .= " AND p.assigned_to = :ao";
            $binds[':ao'] = $params['assigned_to'];
        }

        $sql .= " ORDER BY p.sla_started_at ASC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($binds);
        $data = $stmt->fetchAll();

        sendResponse(200, 'OK', $data);
    }

    // =========================================================
    // REPORT (summary prospek: filter range, kode_kantor, ao/non-ao)
    // =========================================================
    public function report(array $params): void
    {
        $user = $this->getCurrentUser();
        $userRole = $user['role'] ?? 'staff';
        $userKodeKantor = $user['kode_kantor'] ?? '000';
        $userAccessKorwil = $user['access_korwil'] ?? null;
        $source = $params['source'] ?? 'all';
        $isMineSource = $source === 'mine';
        $isPipelineCredit = (($params['pipeline_credit'] ?? '') === '1');

        // Default range: closing = akhir bulan kemarin, harian = hari ini
        $defaultClosingEnd = date('Y-m-t', strtotime('last month'));
        $defaultClosingStart = date('Y-m-01', strtotime('last month'));
        $today = date('Y-m-d');

        $closingTo = $params['closing_date'] ?? ($params['closing_to'] ?? $defaultClosingEnd);
        $closingFrom = $params['closing_from'] ?? date('Y-m-01', strtotime($closingTo));
        $harianDate = $params['harian_date'] ?? $today;

        // Base WHERE for access control:
        // developer/superuser pusat = konsolidasi; superuser cabang = cabangnya;
        // AO = assigned + input sendiri; staff = input sendiri.
        $accessWhere = "";
        $binds = [];
        if ($isMineSource) {
            // Prospek Saya lintas cabang/kode kantor, dibatasi oleh created_by di source filter.
        } elseif ($userRole === 'developer') {
            // Full access
        } elseif ($userRole === 'superuser') {
            if ($userAccessKorwil) {
                $codes = Database::getKodeKantorByKorwil($userAccessKorwil);
                if (!empty($codes)) {
                    $ph = [];
                    foreach ($codes as $i => $c) {
                        $k = ":access_kw_{$i}";
                        $ph[] = $k;
                        $binds[$k] = $c;
                    }
                    $accessWhere = " AND p.kode_kantor IN (" . implode(',', $ph) . ")";
                } else {
                    $accessWhere = " AND 1=0";
                }
            } elseif ($userKodeKantor !== '000') {
                $accessWhere = " AND p.kode_kantor = :ukk";
                $binds[':ukk'] = $userKodeKantor;
            }
        } elseif (in_array($userRole, ['ao_kredit', 'ao_dana', 'ao_remedial'])) {
            $accessWhere = " AND (p.assigned_to = :uid OR p.created_by = :uid2)";
            $binds[':uid'] = $user['employee_id'];
            $binds[':uid2'] = $user['employee_id'];
        } else {
            $accessWhere = " AND p.created_by = :staff_id";
            $binds[':staff_id'] = $user['employee_id'];
        }

        // Filter korwil/kantor
        $filterKorwil = $params['korwil'] ?? null;
        $filterKode = $params['kode_kantor'] ?? null;
        $kantorWhere = "";
        if (!$isMineSource && $filterKode && $filterKode !== 'all') {
            $kantorWhere = " AND p.kode_kantor = :fkk";
            $binds[':fkk'] = $filterKode;
        } elseif (!$isMineSource && $filterKorwil && $filterKorwil !== 'all') {
            $codes = Database::getKodeKantorByKorwil($filterKorwil);
            if (!empty($codes)) {
                $ph = [];
                foreach ($codes as $i => $c) { $k = ":kr{$i}"; $ph[] = $k; $binds[$k] = $c; }
                $kantorWhere = " AND p.kode_kantor IN (" . implode(',', $ph) . ")";
            }
        }

        // Source filter
        $sourceWhere = "";
        if ($source === 'ao') $sourceWhere = " AND p.is_ao_input = 1";
        elseif ($source === 'non_ao') $sourceWhere = " AND p.is_ao_input = 0";
        elseif ($source === 'mine') {
            $sourceWhere = " AND p.created_by = :source_created_by";
            $binds[':source_created_by'] = $user['employee_id'] ?? '';
        }

        $pipelineWhere = "";
        if ($isPipelineCredit) {
            $pipelineWhere .= " AND p.prospect_type IN ('KREDIT','DEBITUR_EXISTING')
                AND p.delegation_status = 'SUDAH_DIDELEGASIKAN'
                AND COALESCE(p.assigned_to, '') <> ''";
        }

        $assignedTo = trim((string)($params['assigned_to'] ?? ''));
        if ($assignedTo !== '' && $assignedTo !== 'all') {
            $pipelineWhere .= " AND p.assigned_to = :flt_assigned_to";
            $binds[':flt_assigned_to'] = $assignedTo;
        }

        $where = "WHERE 1=1" . $accessWhere . $kantorWhere . $sourceWhere . $pipelineWhere;
        $pipelineJoin = "LEFT JOIN prospect_credit_pipelines pc ON pc.prospect_id = p.id";

        // Summary stats
        $summaryStmt = $this->db->prepare("SELECT 
            COUNT(*) AS total_prospek,
            SUM(CASE WHEN p.status = 'OPEN' THEN 1 ELSE 0 END) AS total_open,
            SUM(CASE WHEN p.status = 'FOLLOW_UP' THEN 1 ELSE 0 END) AS total_follow_up,
            SUM(CASE WHEN p.status = 'SLA' THEN 1 ELSE 0 END) AS total_sla,
            SUM(CASE WHEN p.status = 'CLOSING' THEN 1 ELSE 0 END) AS total_closing,
            SUM(CASE WHEN p.status = 'REJECT' THEN 1 ELSE 0 END) AS total_reject,
            SUM(CASE WHEN p.is_ao_input = 1 THEN 1 ELSE 0 END) AS total_from_ao,
            SUM(CASE WHEN p.is_ao_input = 0 THEN 1 ELSE 0 END) AS total_from_non_ao,
            SUM(CASE WHEN p.delegation_status = 'BELUM_DIDELEGASIKAN' THEN 1 ELSE 0 END) AS total_pending_delegasi,
            SUM(CASE WHEN p.status = 'CLOSING' THEN COALESCE(p.closing_realization_amount, 0) ELSE 0 END) AS total_realisasi,
            SUM(COALESCE(pc.requested_loan_amount, 0)) AS total_pipeline_pengajuan,
            SUM(CASE WHEN p.status = 'CLOSING' THEN COALESCE(p.closing_realization_amount, 0) ELSE 0 END) AS total_pipeline_realisasi
            FROM prospects p {$pipelineJoin} {$where}");
        $summaryStmt->execute($binds);
        $summary = $summaryStmt->fetch();

        // Closing bulan kemarin
        $closingBinds = array_merge($binds, [':clf' => $closingFrom . ' 00:00:00', ':clt' => $closingTo . ' 23:59:59']);
        $closingStmt = $this->db->prepare("SELECT COUNT(*) AS jumlah, 
            SUM(COALESCE(p.closing_realization_amount, 0)) AS nominal
            FROM prospects p {$pipelineJoin} {$where} AND p.status = 'CLOSING' AND p.closed_at BETWEEN :clf AND :clt");
        $closingStmt->execute($closingBinds);
        $closingReport = $closingStmt->fetch();

        // Input periode berjalan: data > closing_to dan data <= harian_date
        $harianFrom = date('Y-m-d', strtotime($closingTo . ' +1 day'));
        $harianBinds = array_merge($binds, [
            ':hd' => $closingTo . ' 23:59:59',
            ':hd2' => $harianDate . ' 23:59:59'
        ]);
        $harianStmt = $this->db->prepare("SELECT COUNT(*) AS jumlah 
            FROM prospects p {$pipelineJoin} {$where} AND p.created_at > :hd AND p.created_at <= :hd2");
        $harianStmt->execute($harianBinds);
        $harianReport = $harianStmt->fetch();

        // Per type breakdown
        $typeStmt = $this->db->prepare("SELECT p.prospect_type, COUNT(*) AS jumlah,
            SUM(CASE WHEN p.status = 'CLOSING' THEN COALESCE(p.closing_realization_amount,0) ELSE 0 END) AS realisasi
            FROM prospects p {$pipelineJoin} {$where} GROUP BY p.prospect_type");
        $typeStmt->execute($binds);
        $perType = $typeStmt->fetchAll();

        sendResponse(200, 'OK', [
            'summary' => $summary,
            'closing_period' => [
                'from' => $closingFrom,
                'to' => $closingTo,
                'jumlah' => (int)($closingReport['jumlah'] ?? 0),
                'nominal' => (int)($closingReport['nominal'] ?? 0),
            ],
            'harian' => [
                'from' => $harianFrom,
                'date' => $harianDate,
                'jumlah_input' => (int)($harianReport['jumlah'] ?? 0),
            ],
            'per_type' => $perType,
        ]);
    }


    // =========================================================
    // MASTER: Kode Kantor (dropdown cabang + korwil filter)
    // =========================================================
    public function masterKodeKantor(array $params): void
    {
        $korwil = $params['korwil'] ?? null;
        $data = Database::getKodeKantor($korwil);

        // Group by korwil untuk frontend
        $grouped = [
            'all' => $data,
            'korwil' => [
                'pusat' => ['label' => 'Pusat', 'range' => '000'],
                'semarang' => ['label' => 'Korwil Semarang', 'range' => '001 - 007'],
                'solo' => ['label' => 'Korwil Solo', 'range' => '008 - 014'],
                'banyumas' => ['label' => 'Korwil Banyumas', 'range' => '015 - 021'],
                'pekalongan' => ['label' => 'Korwil Pekalongan', 'range' => '022 - 028'],
            ],
        ];

        sendResponse(200, 'OK', $grouped);
    }

    // =========================================================
    // MASTER: Pegawai AO (untuk delegasi dropdown)
    // =========================================================
    public function masterPegawaiAO(array $params): void
    {
        $kodeKantor = $params['kode_kantor'] ?? null;
        $groupJabatan = $params['group_jabatan'] ?? null;
        $tipe = $params['tipe'] ?? null;

        $data = $this->attachAoWorkloads($this->getAoCandidates($kodeKantor, $groupJabatan, $tipe));
        sendResponse(200, 'OK', $data);
    }

    private function getAoCandidates(?string $kodeKantor = null, ?string $groupJabatan = null, ?string $tipe = null): array
    {
        $token = AuthMiddleware::getToken();
        if ($token && $kodeKantor) {
            $ssoData = $this->getAoCandidatesFromSso($token, $kodeKantor, $groupJabatan, $tipe);
            if (!empty($ssoData)) {
                return $ssoData;
            }
        }

        try {
            return Database::getPegawaiAktif($kodeKantor, $groupJabatan);
        } catch (\Exception $e) {
            // Fallback: return dummy AO list jika SIMPEG DB belum tersedia
            $dummyAO = [
                ['employee_id' => '201-001', 'full_name' => 'BUDI SANTOSO', 'job_position' => 'AO Kredit', 'group_jabatan' => 'AO Kredit', 'kode_kantor' => '001', 'branch_name' => 'Kc. Utama'],
                ['employee_id' => '201-002', 'full_name' => 'SITI RAHAYU', 'job_position' => 'AO Dana', 'group_jabatan' => 'AO Dana', 'kode_kantor' => '001', 'branch_name' => 'Kc. Utama'],
                ['employee_id' => '201-003', 'full_name' => 'ANDI SETIAWAN', 'job_position' => 'AO Remedial', 'group_jabatan' => 'AO Remedial', 'kode_kantor' => '001', 'branch_name' => 'Kc. Utama'],
            ];

            // Filter by kode_kantor if specified
            if ($kodeKantor) {
                $dummyAO = array_filter($dummyAO, fn($ao) => $ao['kode_kantor'] === $kodeKantor);
            }
            if ($groupJabatan) {
                $dummyAO = array_filter($dummyAO, fn($ao) => $ao['group_jabatan'] === $groupJabatan);
            }

            return array_values($dummyAO);
        }
    }

    private function getAoCandidatesFromSso(string $token, string $kodeKantor, ?string $groupJabatan = null, ?string $tipe = null): array
    {
        if ($token === 'session-authenticated') {
            return [];
        }

        $tipeCandidates = $this->buildSsoAoTypeCandidates($tipe, $groupJabatan);
        if (empty($tipeCandidates)) {
            return [];
        }

        $baseUrl = rtrim((string) env('SSO_BASE_URL', env('SIMPEG_BASE_URL', 'https://apisso.bkkjateng.co.id')), '/');
        $configuredEndpoint = trim((string) env('SSO_AO_ENDPOINT', ''));
        $endpoints = $configuredEndpoint !== ''
            ? [$configuredEndpoint]
            : [
                '/api/ao/list',
            ];

        foreach ($endpoints as $endpoint) {
            foreach ($tipeCandidates as $tipeValue) {
                $url = $baseUrl . '/' . ltrim($endpoint, '/') . '?' . http_build_query([
                    'tipe' => $tipeValue,
                    'kode_cabang' => $kodeKantor,
                ]);

                $response = httpRequest('GET', $url, null, [
                    'Authorization: Bearer ' . $token,
                ]);

                if (($response['status'] ?? 0) !== 200) {
                    continue;
                }

                $normalized = $this->normalizeSsoAoResponse($response['body'] ?? [], $groupJabatan, $kodeKantor);
                if (!empty($normalized)) {
                    return $normalized;
                }
            }
        }

        return [];
    }

    private function buildSsoAoTypeCandidates(?string $tipe = null, ?string $groupJabatan = null): array
    {
        $values = [];
        if ($tipe !== null && trim($tipe) !== '') {
            return [trim($tipe)];
        }

        $group = strtolower(trim((string) $groupJabatan));
        if ($group !== '') {
            if (str_contains($group, 'kredit')) {
                $values = array_merge($values, ['kredit', 'KREDIT', 'AO Kredit']);
            } elseif (str_contains($group, 'dana')) {
                $values = array_merge($values, ['dana', 'DANA', 'AO Dana']);
            } elseif (str_contains($group, 'remedial')) {
                $values = array_merge($values, ['remedial', 'REMEDIAL', 'AO Remedial']);
            }
        }

        return array_values(array_unique(array_filter($values, fn($value) => trim((string) $value) !== '')));
    }

    private function normalizeSsoAoResponse(array $body, ?string $groupJabatan, ?string $kodeKantor): array
    {
        $rows = $body['data'] ?? $body['result'] ?? $body['rows'] ?? [];
        if (isset($rows['data']) && is_array($rows['data'])) {
            $rows = $rows['data'];
        }

        if (!is_array($rows)) {
            return [];
        }

        $normalized = [];
        foreach ($rows as $row) {
            if (!is_array($row)) {
                continue;
            }

            $employeeId = $row['employee_id'] ?? $row['id_peg'] ?? $row['kode_pegawai'] ?? $row['nik'] ?? null;
            $fullName = $row['full_name'] ?? $row['nama'] ?? $row['name'] ?? null;
            if (!$employeeId || !$fullName) {
                continue;
            }

            $normalized[] = [
                'employee_id' => (string) $employeeId,
                'full_name' => (string) $fullName,
                'kode_kantor' => (string) ($row['kode_kantor'] ?? $row['kode_cabang'] ?? $kodeKantor ?? ''),
                'branch_name' => (string) ($row['branch_name'] ?? $row['nama_cabang'] ?? $row['nama_kantor'] ?? ''),
                'job_position' => (string) ($row['job_position'] ?? $row['jabatan'] ?? $row['nama_jabatan'] ?? $groupJabatan ?? ''),
                'group_jabatan' => (string) ($row['group_jabatan'] ?? $groupJabatan ?? ''),
            ];
        }

        usort($normalized, fn($a, $b) => strcmp($a['full_name'], $b['full_name']));
        return $normalized;
    }
}
