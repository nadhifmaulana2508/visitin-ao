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

    case 'prospect_delegate_bulk':
        if ($request_method !== 'POST') sendResponse(405, 'Method harus POST', null);
        $token = AuthMiddleware::require();
        (new ProspectController())->delegateBulk(readJsonBody());
        break;

    case 'prospect_ao_workload':
        if ($request_method !== 'GET') sendResponse(405, 'Method harus GET', null);
        $token = AuthMiddleware::require();
        (new ProspectController())->aoWorkload($_GET);
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

    case 'prospect_confirm_credit_interest':
        if ($request_method !== 'POST') sendResponse(405, 'Method harus POST', null);
        $token = AuthMiddleware::require();
        (new ProspectController())->confirmCreditInterest(readJsonBody());
        break;

    case 'prospect_complete_credit_docs':
        if ($request_method !== 'POST') sendResponse(405, 'Method harus POST', null);
        $token = AuthMiddleware::require();
        (new ProspectController())->completeCreditDocumentation(readJsonBody());
        break;

    case 'prospect_credit_upload':
        if ($request_method !== 'POST') sendResponse(405, 'Method harus POST', null);
        $token = AuthMiddleware::require();
        (new ProspectController())->uploadCreditPipelineFile(readJsonBody());
        break;

    case 'prospect_closing_lookup':
        if ($request_method !== 'GET') sendResponse(405, 'Method harus GET', null);
        $token = AuthMiddleware::require();
        (new ProspectController())->closingLookup($_GET);
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

    case 'master_analis_kredit':
        if ($request_method !== 'GET') sendResponse(405, 'Method harus GET', null);
        $token = AuthMiddleware::require();
        (new ProspectController())->masterAnalisKredit($_GET);
        break;

    default:
        // Tidak match, kembalikan false agar index.php lanjut ke default
        return false;
}

return true;
