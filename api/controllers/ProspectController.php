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

    /**
     * Helper: get current user data from token/session
     */
    private function getCurrentUser(): array
    {
        if (session_status() === PHP_SESSION_NONE) session_start();
        return $_SESSION['user_data'] ?? [];
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
        $type = $input['prospect_type'] ?? '';
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

        // Determine apakah user adalah AO yang sesuai
        $isAoInput = false;
        $autoAssign = null;

        if ($type === 'KREDIT' || $type === 'DEBITUR_EXISTING') {
            if (in_array('AO_KREDIT', $userPerms) || $userRole === 'developer') {
                $isAoInput = true;
                $autoAssign = $employeeId;
            }
        } elseif ($type === 'TABUNGAN' || $type === 'DEPOSITO') {
            if (in_array('AO_DANA', $userPerms) || $userRole === 'developer') {
                $isAoInput = true;
                $autoAssign = $employeeId;
            }
        } elseif ($type === 'PEMBELI_ASET') {
            if (in_array('AO_REMEDIAL_FE', $userPerms) || in_array('AO_REMEDIAL_BE', $userPerms) || $userRole === 'developer') {
                $isAoInput = true;
                $autoAssign = $employeeId;
            }
        }

        $delegationStatus = $isAoInput ? 'SUDAH_DIDELEGASIKAN' : 'BELUM_DIDELEGASIKAN';
        $assignedAt = $isAoInput ? date('Y-m-d H:i:s') : null;

        $stmt = $this->db->prepare("INSERT INTO prospects (
            prospect_type, customer_name, identity_number, phone_number,
            product_interest, estimated_amount, provinsi, kab_kota, kecamatan, desa,
            address, latitude, longitude, geo_address, foto_url,
            kode_kantor, description, created_by, created_by_kode_kantor, is_ao_input,
            delegation_status, assigned_to, assigned_at, status, created_at
        ) VALUES (
            :type, :name, :identity, :phone,
            :product, :amount, :prov, :kab, :kec, :desa,
            :address, :lat, :lng, :geo_addr, :foto,
            :kode_kantor, :desc, :created_by, :created_by_kk, :is_ao,
            :deleg_status, :assigned_to, :assigned_at, 'OPEN', NOW()
        )");

        $stmt->execute([
            ':type' => $type,
            ':name' => $name,
            ':identity' => $input['identity_number'] ?? null,
            ':phone' => $phone,
            ':product' => $input['product_interest'] ?? null,
            ':amount' => (int)($input['estimated_amount'] ?? 0),
            ':prov' => $input['provinsi'] ?? null,
            ':kab' => $input['kab_kota'] ?? null,
            ':kec' => $input['kecamatan'] ?? null,
            ':desa' => $input['desa'] ?? null,
            ':address' => $input['address'] ?? null,
            ':lat' => $input['latitude'] ?? null,
            ':lng' => $input['longitude'] ?? null,
            ':geo_addr' => $input['geo_address'] ?? null,
            ':foto' => $input['foto_url'] ?? null,
            ':kode_kantor' => $kodeKantor,
            ':desc' => $input['description'] ?? null,
            ':created_by' => $employeeId,
            ':created_by_kk' => $userKodeKantor,
            ':is_ao' => $isAoInput ? 1 : 0,
            ':deleg_status' => $delegationStatus,
            ':assigned_to' => $autoAssign,
            ':assigned_at' => $assignedAt,
        ]);

        $newId = (int) $this->db->lastInsertId();

        // History
        $note = $isAoInput
            ? "Prospek {$type} dibuat oleh AO (auto-delegasi)"
            : "Prospek {$type} dibuat (menunggu delegasi superuser)";
        $this->addHistory($newId, 'CREATED', null, 'OPEN', null, $autoAssign, $note);

        sendResponse(201, 'Prospek berhasil disimpan', ['id' => $newId, 'delegation_status' => $delegationStatus]);
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

        // Base query
        $sql = "SELECT p.*, kk.nama_kantor, kk.korwil FROM prospects p 
                LEFT JOIN kode_kantor kk ON p.kode_kantor = kk.kode_kantor 
                WHERE 1=1";
        $binds = [];

        // --- FILTER: Access control berdasarkan role ---
        // Pusat (000): Divisi Pemasaran, Operasional, Direksi, Komisaris = semua cabang
        // Pusat Divisi Remedial = hanya report remedial (tapi untuk prospek bisa semua)
        // Atasan cabang (PE, PS): hanya cabangnya
        // AO: hanya yang assigned ke dia
        // Staff: hanya yang dia input

        if ($userRole === 'developer') {
            // Full access, no filter
        } elseif ($userRole === 'superuser') {
            if ($userKodeKantor === '000') {
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

        if ($filterKodeKantor && $filterKodeKantor !== 'all') {
            $sql .= " AND p.kode_kantor = :flt_kk";
            $binds[':flt_kk'] = $filterKodeKantor;
        } elseif ($filterKorwil && $filterKorwil !== 'all') {
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

        // --- FILTER: AO vs Non-AO (is_ao_input) ---
        $filterSource = $params['source'] ?? null; // 'ao', 'non_ao', 'all'
        if ($filterSource === 'ao') {
            $sql .= " AND p.is_ao_input = 1";
        } elseif ($filterSource === 'non_ao') {
            $sql .= " AND p.is_ao_input = 0";
        }

        // --- FILTER: Range tanggal (created_at) ---
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

        // --- FILTER: Range closing_date ---
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
        $allowedOrders = ['created_at', 'closed_at', 'customer_name', 'estimated_amount', 'status'];
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
        $stmt = $this->db->prepare("SELECT p.*, kk.nama_kantor, kk.korwil 
            FROM prospects p 
            LEFT JOIN kode_kantor kk ON p.kode_kantor = kk.kode_kantor 
            WHERE p.id = :id");
        $stmt->execute([':id' => $id]);
        $prospect = $stmt->fetch();

        if (!$prospect) {
            sendResponse(404, 'Prospek tidak ditemukan', null);
        }

        // Get follow ups
        $fuStmt = $this->db->prepare("SELECT * FROM prospect_follow_ups WHERE prospect_id = :id ORDER BY follow_up_date DESC");
        $fuStmt->execute([':id' => $id]);
        $prospect['follow_ups'] = $fuStmt->fetchAll();

        // Get histories
        $hStmt = $this->db->prepare("SELECT * FROM prospect_histories WHERE prospect_id = :id ORDER BY created_at ASC");
        $hStmt->execute([':id' => $id]);
        $prospect['histories'] = $hStmt->fetchAll();

        // Get SLA logs (khusus kredit yang sudah SLA)
        if ($prospect['prospect_type'] === 'KREDIT' && $prospect['sla_started_at']) {
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

        if ($prospectId <= 0 || $assignedTo === '') {
            sendResponse(400, 'prospect_id dan assigned_to wajib diisi', null);
        }

        // Get prospect
        $stmt = $this->db->prepare("SELECT * FROM prospects WHERE id = :id");
        $stmt->execute([':id' => $prospectId]);
        $prospect = $stmt->fetch();

        if (!$prospect) sendResponse(404, 'Prospek tidak ditemukan', null);
        if ($prospect['delegation_status'] === 'SUDAH_DIDELEGASIKAN') {
            sendResponse(400, 'Prospek sudah didelegasikan sebelumnya', null);
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
            if (!in_array($prospect['prospect_type'], ['KREDIT', 'DEBITUR_EXISTING'])) {
                sendResponse(400, 'Status SLA hanya berlaku untuk prospek Kredit dan Debitur Existing', null);
            }
            $now = date('Y-m-d H:i:s');
            $this->db->prepare("UPDATE prospects SET status = 'SLA', sla_started_at = :sla, sla_started_by = :by, updated_at = NOW() WHERE id = :id")
                ->execute([':sla' => $now, ':by' => $user['employee_id'] ?? '', ':id' => $prospectId]);

            // Auto-create first SLA log stage
            $this->db->prepare("INSERT INTO prospect_sla_logs (prospect_id, stage, stage_started_at, note, created_by) VALUES (:pid, 'VERIFIKASI_DATA', :started, 'Tahap awal SLA', :by)")
                ->execute([':pid' => $prospectId, ':started' => $now, ':by' => $user['employee_id'] ?? '']);

            $this->addHistory($prospectId, 'STATUS_CHANGED', $oldStatus, 'SLA', null, null, 
                'Masuk proses SLA (pipeline kredit)', ['sla_started_at' => $now]);
            sendResponse(200, 'Status berhasil diubah ke SLA - masuk pipeline kredit', null);
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

        // Validasi: Kredit wajib punya rekening & nominal
        if ($type === 'KREDIT' || $type === 'DEBITUR_EXISTING') {
            $accountNum = trim($input['closing_account_number'] ?? '');
            $amount = (int)($input['closing_realization_amount'] ?? 0);
            if ($accountNum === '') sendResponse(400, 'Closing kredit wajib input nomor rekening', null);
            if ($amount <= 0) sendResponse(400, 'Closing kredit wajib input nominal realisasi pencairan', null);
        }

        $now = date('Y-m-d H:i:s');

        $upd = $this->db->prepare("UPDATE prospects SET 
            status = 'CLOSING', closed_at = :closed, 
            closing_account_number = :acc, closing_realization_amount = :amount,
            closing_tenor = :tenor, closing_note = :note,
            closing_asset_name = :asset, closing_buyer_name = :buyer,
            updated_at = NOW() WHERE id = :id");
        $upd->execute([
            ':closed' => $now,
            ':acc' => $input['closing_account_number'] ?? null,
            ':amount' => (int)($input['closing_realization_amount'] ?? 0) ?: null,
            ':tenor' => (int)($input['closing_tenor'] ?? 0) ?: null,
            ':note' => $input['closing_note'] ?? null,
            ':asset' => $input['closing_asset_name'] ?? null,
            ':buyer' => $input['closing_buyer_name'] ?? null,
            ':id' => $prospectId,
        ]);

        // Close any open SLA log
        if ($prospect['sla_started_at']) {
            $this->db->prepare("UPDATE prospect_sla_logs SET stage_ended_at = :ended, 
                duration_days = DATEDIFF(:ended2, stage_started_at) 
                WHERE prospect_id = :pid AND stage_ended_at IS NULL")
                ->execute([':ended' => $now, ':ended2' => $now, ':pid' => $prospectId]);
        }

        $metadata = [
            'account_number' => $input['closing_account_number'] ?? null,
            'amount' => (int)($input['closing_realization_amount'] ?? 0),
            'tenor' => (int)($input['closing_tenor'] ?? 0),
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
        $stage = trim($input['stage'] ?? '');

        if ($prospectId <= 0 || $stage === '') {
            sendResponse(400, 'prospect_id dan stage wajib diisi', null);
        }

        // Close previous open stage
        $now = date('Y-m-d H:i:s');
        $this->db->prepare("UPDATE prospect_sla_logs SET stage_ended_at = :ended, 
            duration_days = DATEDIFF(:ended2, stage_started_at) 
            WHERE prospect_id = :pid AND stage_ended_at IS NULL")
            ->execute([':ended' => $now, ':ended2' => $now, ':pid' => $prospectId]);

        // Insert new stage
        $ins = $this->db->prepare("INSERT INTO prospect_sla_logs 
            (prospect_id, stage, stage_started_at, note, created_by) 
            VALUES (:pid, :stage, :started, :note, :by)");
        $ins->execute([
            ':pid' => $prospectId,
            ':stage' => strtoupper($stage),
            ':started' => $now,
            ':note' => $input['note'] ?? null,
            ':by' => $user['employee_id'] ?? '',
        ]);

        sendResponse(200, 'SLA stage berhasil ditambahkan', null);
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
                LEFT JOIN kode_kantor kk ON p.kode_kantor = kk.kode_kantor
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

        // Default range: closing = akhir bulan kemarin, harian = hari ini
        $defaultClosingEnd = date('Y-m-t', strtotime('last month'));
        $defaultClosingStart = date('Y-m-01', strtotime('last month'));
        $today = date('Y-m-d');

        $closingFrom = $params['closing_from'] ?? $defaultClosingStart;
        $closingTo = $params['closing_to'] ?? $defaultClosingEnd;
        $harianDate = $params['harian_date'] ?? $today;

        // Base WHERE for access control
        $accessWhere = "";
        $binds = [];
        if (!in_array($userRole, ['developer', 'superuser']) || ($userRole === 'superuser' && $userKodeKantor !== '000')) {
            if (in_array($userRole, ['ao_kredit', 'ao_dana', 'ao_remedial'])) {
                $accessWhere = " AND (p.assigned_to = :uid OR p.created_by = :uid2)";
                $binds[':uid'] = $user['employee_id'];
                $binds[':uid2'] = $user['employee_id'];
            } elseif ($userKodeKantor !== '000') {
                $accessWhere = " AND p.kode_kantor = :ukk";
                $binds[':ukk'] = $userKodeKantor;
            }
        }

        // Filter korwil/kantor
        $filterKorwil = $params['korwil'] ?? null;
        $filterKode = $params['kode_kantor'] ?? null;
        $kantorWhere = "";
        if ($filterKode && $filterKode !== 'all') {
            $kantorWhere = " AND p.kode_kantor = :fkk";
            $binds[':fkk'] = $filterKode;
        } elseif ($filterKorwil && $filterKorwil !== 'all') {
            $codes = Database::getKodeKantorByKorwil($filterKorwil);
            if (!empty($codes)) {
                $ph = [];
                foreach ($codes as $i => $c) { $k = ":kr{$i}"; $ph[] = $k; $binds[$k] = $c; }
                $kantorWhere = " AND p.kode_kantor IN (" . implode(',', $ph) . ")";
            }
        }

        // Source filter
        $sourceWhere = "";
        $source = $params['source'] ?? 'all';
        if ($source === 'ao') $sourceWhere = " AND p.is_ao_input = 1";
        elseif ($source === 'non_ao') $sourceWhere = " AND p.is_ao_input = 0";

        $where = "WHERE 1=1" . $accessWhere . $kantorWhere . $sourceWhere;

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
            SUM(CASE WHEN p.status = 'CLOSING' THEN COALESCE(p.closing_realization_amount, 0) ELSE 0 END) AS total_realisasi
            FROM prospects p {$where}");
        $summaryStmt->execute($binds);
        $summary = $summaryStmt->fetch();

        // Closing bulan kemarin
        $closingBinds = array_merge($binds, [':clf' => $closingFrom . ' 00:00:00', ':clt' => $closingTo . ' 23:59:59']);
        $closingStmt = $this->db->prepare("SELECT COUNT(*) AS jumlah, 
            SUM(COALESCE(p.closing_realization_amount, 0)) AS nominal
            FROM prospects p {$where} AND p.status = 'CLOSING' AND p.closed_at BETWEEN :clf AND :clt");
        $closingStmt->execute($closingBinds);
        $closingReport = $closingStmt->fetch();

        // Input hari ini
        $harianBinds = array_merge($binds, [':hd' => $harianDate . ' 00:00:00', ':hd2' => $harianDate . ' 23:59:59']);
        $harianStmt = $this->db->prepare("SELECT COUNT(*) AS jumlah 
            FROM prospects p {$where} AND p.created_at BETWEEN :hd AND :hd2");
        $harianStmt->execute($harianBinds);
        $harianReport = $harianStmt->fetch();

        // Per type breakdown
        $typeStmt = $this->db->prepare("SELECT p.prospect_type, COUNT(*) AS jumlah,
            SUM(CASE WHEN p.status = 'CLOSING' THEN COALESCE(p.closing_realization_amount,0) ELSE 0 END) AS realisasi
            FROM prospects p {$where} GROUP BY p.prospect_type");
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

        try {
            $data = Database::getPegawaiAktif($kodeKantor, $groupJabatan);
            sendResponse(200, 'OK', $data);
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

            sendResponse(200, 'OK (fallback dummy)', array_values($dummyAO));
        }
    }
}
