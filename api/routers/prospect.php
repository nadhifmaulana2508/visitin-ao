<?php
/**
 * Router: Prospect Endpoints
 * 
 * Semua action yang berhubungan dengan modul prospek.
 * Di-include dari api/index.php saat action match prefix 'prospect_*'
 */

switch ($action) {

    // ===================== CRUD PROSPEK =====================

    case 'prospect_create':
        if ($request_method !== 'POST') sendResponse(405, 'Method harus POST', null);
        $token = AuthMiddleware::require();
        (new ProspectController())->create(readJsonBody());
        break;

    case 'prospect_list':
        if ($request_method !== 'GET') sendResponse(405, 'Method harus GET', null);
        $token = AuthMiddleware::require();
        (new ProspectController())->list($_GET);
        break;

    case 'prospect_detail':
        if ($request_method !== 'GET') sendResponse(405, 'Method harus GET', null);
        $token = AuthMiddleware::require();
        $id = $_GET['id'] ?? null;
        if (!$id) sendResponse(400, 'Parameter id wajib diisi', null);
        (new ProspectController())->detail((int) $id);
        break;

    // ===================== STATUS CHANGES =====================

    case 'prospect_delegate':
        if ($request_method !== 'POST') sendResponse(405, 'Method harus POST', null);
        $token = AuthMiddleware::require();
        (new ProspectController())->delegate(readJsonBody());
        break;

    case 'prospect_follow_up':
        if ($request_method !== 'POST') sendResponse(405, 'Method harus POST', null);
        $token = AuthMiddleware::require();
        (new ProspectController())->followUp(readJsonBody());
        break;

    case 'prospect_change_status':
        if ($request_method !== 'POST') sendResponse(405, 'Method harus POST', null);
        $token = AuthMiddleware::require();
        (new ProspectController())->changeStatus(readJsonBody());
        break;

    case 'prospect_close':
        if ($request_method !== 'POST') sendResponse(405, 'Method harus POST', null);
        $token = AuthMiddleware::require();
        (new ProspectController())->close(readJsonBody());
        break;

    case 'prospect_reject':
        if ($request_method !== 'POST') sendResponse(405, 'Method harus POST', null);
        $token = AuthMiddleware::require();
        (new ProspectController())->reject(readJsonBody());
        break;

    // ===================== SLA PIPELINE =====================

    case 'prospect_sla_log':
        if ($request_method !== 'POST') sendResponse(405, 'Method harus POST', null);
        $token = AuthMiddleware::require();
        (new ProspectController())->addSlaLog(readJsonBody());
        break;

    case 'prospect_sla_pipeline':
        if ($request_method !== 'GET') sendResponse(405, 'Method harus GET', null);
        $token = AuthMiddleware::require();
        (new ProspectController())->slaPipeline($_GET);
        break;

    // ===================== REPORT =====================

    case 'prospect_report':
        if ($request_method !== 'GET') sendResponse(405, 'Method harus GET', null);
        $token = AuthMiddleware::require();
        (new ProspectController())->report($_GET);
        break;

    // ===================== MASTER DATA =====================

    case 'master_kode_kantor':
        if ($request_method !== 'GET') sendResponse(405, 'Method harus GET', null);
        (new ProspectController())->masterKodeKantor($_GET);
        break;

    case 'master_pegawai_ao':
        if ($request_method !== 'GET') sendResponse(405, 'Method harus GET', null);
        $token = AuthMiddleware::require();
        (new ProspectController())->masterPegawaiAO($_GET);
        break;

    default:
        // Tidak match, kembalikan false agar index.php lanjut ke default
        return false;
}

return true;
