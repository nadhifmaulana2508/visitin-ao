<?php
$is_ao = in_array($user_role, ['ao_kredit', 'ao_dana', 'ao_remedial', 'developer']);
$is_superuser = in_array($user_role, ['superuser', 'developer']);
$can_delegate_prospek = (bool)($menu_access['can_delegate_prospek'] ?? false);
$user_employee_id = $_SESSION['user_data']['employee_id'] ?? '';
$prospect_id = $_GET['id'] ?? null;
$detail_page_title = $detail_page_title ?? 'Detail Prospek';
$detail_back_url = $detail_back_url ?? (BASE_APP . '/daftar-prospek');
$detail_simple_mode = !empty($detail_simple_mode);
?>

<style>
    .detail-container { margin: -30px 16px 20px 16px; position: relative; z-index: 10; }
    @media (min-width: 768px) { .detail-container { margin: -35px 24px 24px 24px; } }
    @media (min-width: 1024px) { .detail-container { margin: -40px 32px 28px 32px; } }

    .detail-card { background:#fff; border-radius:16px; padding:18px; margin-bottom:14px; box-shadow:0 3px 12px rgba(0,0,0,0.03); }
    @media (min-width: 768px) { .detail-card { padding:22px; } }
    .simple-prospect-detail .credit-only-detail { display:none !important; }

    .info-row { display:flex; justify-content:space-between; align-items:flex-start; padding:9px 0; border-bottom:1px solid #F4F7F6; gap:10px; }
    .info-row:last-child { border-bottom:none; }
    .info-label { font-size:0.68rem; font-weight:700; color:#64748B; text-transform:uppercase; min-width:90px; flex-shrink:0; }
    .info-value { font-size:0.82rem; font-weight:700; color:#1E293B; text-align:right; word-break:break-word; }
    @media (min-width: 768px) { .info-label { font-size:0.72rem; } .info-value { font-size:0.85rem; } }

    /* # COMPONENT: Ringkasan screening IDEP tanpa menyimpan atau menampilkan isi mentah laporan. */
    .idep-analysis-panel { margin-top:12px; padding:12px; border:1px solid #DBEAFE; border-radius:12px; background:#F8FBFF; }
    #modalIdepAnalysis .modal-body, #modalCreditInterest .modal-body { max-height:calc(100vh - 130px); overflow-y:auto; }
    #credit-idep-analysis-slot .idep-analysis-panel { margin-top:12px; }
    .idep-analysis-head { display:flex; align-items:center; justify-content:space-between; gap:8px; margin-bottom:10px; }
    .idep-analysis-title { color:#1E3A8A; font-size:0.72rem; font-weight:800; }
    .idep-analysis-badge { display:inline-flex; align-items:center; gap:5px; padding:5px 8px; border-radius:999px; font-size:0.62rem; font-weight:800; white-space:nowrap; }
    .idep-analysis-badge.good { background:#DCFCE7; color:#166534; }
    .idep-analysis-badge.review { background:#FEF3C7; color:#92400E; }
    .idep-analysis-badge.risk { background:#FEE2E2; color:#991B1B; }
    .idep-analysis-badge.missing { background:#E2E8F0; color:#475569; }
    .idep-analysis-grid { display:grid; grid-template-columns:repeat(2, minmax(0,1fr)); gap:8px; }
    .idep-analysis-metric { min-width:0; padding:8px; border-radius:9px; background:#fff; border:1px solid #E0ECFA; }
    .idep-analysis-metric-label { display:block; color:#64748B; font-size:0.58rem; font-weight:700; line-height:1.25; }
    .idep-analysis-metric-value { display:block; margin-top:3px; color:#1E293B; font-size:0.72rem; font-weight:900; overflow-wrap:anywhere; }
    .idep-analysis-note { margin:10px 0 0; color:#64748B; font-size:0.62rem; line-height:1.45; }
    .idep-analysis-note strong { color:#334155; }
    .idep-analysis-section { margin-top:14px; padding-top:12px; border-top:1px solid #E0ECFA; }
    .idep-analysis-section-title { margin-bottom:8px; color:#1E3A8A; font-size:0.72rem; font-weight:800; }
    .idep-calculator-grid { display:grid; grid-template-columns:repeat(2, minmax(0,1fr)); gap:8px; }
    .idep-calculator-field { display:grid; gap:4px; min-width:0; color:#64748B; font-size:0.6rem; font-weight:800; }
    .idep-calculator-field .ui-input { width:100%; min-height:36px; padding:8px; border:1px solid #CBD5E1; border-radius:8px; color:#1E293B; font-size:0.72rem; font-weight:700; }
    .idep-calculator-result { margin-top:10px; padding:10px; border:1px solid #DBEAFE; border-radius:10px; background:#fff; }
    .idep-calculator-result .idep-analysis-grid { gap:6px; }
    .idep-upload-button { width:28px; height:28px; padding:0; border:1px solid #BFDBFE; border-radius:8px; background:#EFF6FF; color:#1D4ED8; display:inline-flex; align-items:center; justify-content:center; cursor:pointer; }
    .idep-upload-button:hover { background:#DBEAFE; }
    .idep-upload-button:disabled { cursor:wait; opacity:.65; }
    @media (max-width: 420px) { .idep-analysis-grid, .idep-calculator-grid { grid-template-columns:1fr; } }

    .badge-lg { font-size:0.7rem; padding:6px 14px; border-radius:8px; font-weight:700; }

    /* Timeline */
    .timeline { position:relative; padding-left:22px; list-style:none; margin:0; padding-top:5px; }
    .timeline::before { content:''; position:absolute; left:7px; top:12px; bottom:12px; width:2px; background:#E2E8F0; }
    .timeline-item { position:relative; margin-bottom:18px; }
    .timeline-item:last-child { margin-bottom:0; }
    .timeline-dot { position:absolute; left:-19px; top:5px; width:10px; height:10px; border-radius:50%; background:var(--color-accent); border:2px solid white; box-shadow:0 0 0 2px #FFE5E5; }
    .timeline-date { font-size:0.62rem; color:#94A3B8; font-weight:600; margin-bottom:2px; }
    .timeline-text { font-size:0.78rem; color:#475569; line-height:1.4; }

    /* SLA Pipeline */
    .sla-stage { display:grid; grid-template-columns:10px minmax(0,1fr) auto; align-items:center; column-gap:8px; row-gap:6px; padding:10px 0; border-bottom:1px dashed #E2E8F0; }
    .sla-stage:last-child { border-bottom:none; }
    .sla-dot { width:10px; height:10px; border-radius:50%; flex-shrink:0; }
    .sla-dot.done { background:#1976D2; }
    .sla-dot.active { background:var(--color-accent); animation:pulse 1.5s infinite; }
    @keyframes pulse { 0%,100%{box-shadow:0 0 0 0 rgba(255,123,84,0.4)} 50%{box-shadow:0 0 0 6px rgba(255,123,84,0)} }
    .sla-stage-name { font-size:0.78rem; font-weight:700; color:#1E293B; }
    .sla-stage-dur { font-size:0.65rem; color:#64748B; margin-left:auto; }
    .sla-stage-info { min-width:0; }
    .sla-stage-actions { grid-column:2 / -1; display:flex; align-items:center; justify-content:flex-end; gap:4px; min-width:0; }
    .sla-stage-actions .icon-mini-btn { width:30px; height:30px; border-radius:9px; font-size:0.72rem; }
    .sla-stage-actions .icon-mini-btn.upload { margin-left:0 !important; }
    .sla-stage-actions .stage-attachment-list { margin-left:0; }
    .stage-attachment-list { display:inline-flex; align-items:center; justify-content:flex-end; flex-wrap:wrap; gap:4px; max-width:100%; }
    .stage-attachment-item { display:inline-flex; align-items:center; gap:2px; }
    .stage-photo-grid { display:grid; grid-template-columns:repeat(4, minmax(0,1fr)); gap:6px; margin-top:8px; }
    .stage-photo-preview { position:relative; aspect-ratio:1; overflow:hidden; border:1px solid #E2E8F0; border-radius:10px; background:#F8FAFC; }
    .stage-photo-preview img { width:100%; height:100%; object-fit:cover; display:block; }
    .stage-photo-index { position:absolute; right:4px; bottom:4px; min-width:20px; padding:2px 5px; border-radius:8px; background:rgba(10,25,49,.78); color:#fff; font-size:.62rem; font-weight:800; text-align:center; }
    .survey-gallery-grid { display:grid; grid-template-columns:repeat(2, minmax(0,1fr)); gap:10px; }
    .survey-gallery-item { min-width:0; padding:8px; border:1px solid #E2E8F0; border-radius:12px; background:#F8FAFC; }
    .survey-gallery-item img { width:100%; aspect-ratio:4/3; object-fit:cover; display:block; border-radius:8px; background:#E2E8F0; }
    .survey-gallery-item .action-btn { padding:9px; margin:8px 0 0; font-size:.72rem; }
    .sla-camera-video { width:100%; aspect-ratio:4/3; display:block; object-fit:cover; border-radius:12px; background:#0A1931; }

    /* Action buttons */
    .action-btn {
        width:100%; border:none; border-radius:12px; font-weight:700; font-size:0.85rem;
        padding:13px; margin-bottom:10px; transition:0.15s; cursor:pointer;
    }
    .action-btn:active { transform:scale(0.98); }
    .btn-follow-up { background:#E3F2FD; color:#1565C0; }
    .btn-sla { background:#E3F2FD; color:#1565C0; }
    .btn-closing { background:var(--color-accent); color:white; box-shadow:0 4px 12px rgba(255,123,84,0.3); }
    .btn-reject { background:#FFEBEE; color:#C62828; }
    .btn-delegasi { background:var(--color-primary); color:white; box-shadow:0 4px 12px rgba(10,25,49,0.2); }
    .btn-wa { background:#F1F5F9; color:#334155; }
    .action-grid { display:grid; grid-template-columns:repeat(2, minmax(0,1fr)); gap:8px; }
    .action-grid .action-btn { margin-bottom:0; padding:10px 8px; font-size:0.72rem; min-height:44px; display:flex !important; align-items:center; justify-content:center; gap:6px; }
    .action-grid .action-btn i { font-size:0.82rem; margin:0 !important; }
    @media (min-width: 768px) { .action-grid { grid-template-columns:repeat(4, minmax(0,1fr)); } }

    .header-main { display:flex; align-items:flex-start; justify-content:space-between; gap:12px; min-height:46px; }
    .header-title-block { min-width:0; flex:1; }
    .header-title-block #d-name {
        max-width:100%; overflow:hidden; text-overflow:ellipsis; display:-webkit-box;
        -webkit-line-clamp:2; -webkit-box-orient:vertical; line-height:1.18;
    }
    .sla-summary {
        display:none; grid-template-columns:repeat(2, minmax(0,1fr)); gap:4px 10px;
        margin-top:8px; padding:0; border:0; max-width:360px;
    }
    .sla-metric {
        background:transparent; border:0; border-radius:0; padding:0; min-height:0;
        display:flex; align-items:baseline; justify-content:flex-start; gap:6px; text-align:left;
    }
    .sla-summary .sla-label { font-size:0.56rem; color:#94A3B8; font-weight:800; text-transform:uppercase; line-height:1.1; white-space:nowrap; }
    .sla-metric-value { margin-top:0; font-size:0.74rem; font-weight:900; color:#0A1931; line-height:1.15; word-break:break-word; }
    .sla-metric-value.sla-days { color:#1565C0; }
    .sla-metric-value.money { color:#00796B; }
    .sla-metric-value.percent { color:#1565C0; }
    .sla-realization-date { display:inline-block; margin-left:4px; color:#94A3B8; font-size:0.56rem; font-weight:800; white-space:nowrap; }
    .sla-realization-plan { display:flex; align-items:center; gap:8px; width:fit-content; max-width:100%; margin-top:9px; padding:7px 10px; border:1px solid #DBEAFE; border-radius:9px; background:#EFF6FF; color:#1D4ED8; font-size:0.66rem; font-weight:800; }
    .sla-realization-plan i { flex:0 0 auto; font-size:0.72rem; }
    .sla-realization-plan .plan-date { min-width:0; overflow:hidden; text-overflow:ellipsis; white-space:nowrap; }
    .sla-realization-plan strong { flex:0 0 auto; }
    .sla-realization-plan.today { border-color:#FDE68A; background:#FFFBEB; color:#B45309; }
    .sla-realization-plan.overdue { border-color:#FECACA; background:#FEF2F2; color:#B91C1C; }
    .sla-realization-plan.completed { border-color:#BBF7D0; background:#F0FDF4; color:#15803D; }
    .sla-realization-plan.missing { border-color:#E2E8F0; background:#F8FAFC; color:#64748B; }
    .status-pending { background:#FEF3C7; color:#92400E; }
    @media (max-width: 420px) {
        .header-main { display:block; }
        .sla-summary { max-width:100%; }
        .sla-summary .sla-label { font-size:0.5rem; }
        .sla-metric-value { font-size:0.68rem; }
        .sla-realization-date { font-size:0.5rem; margin-left:2px; }
    }
    .doc-item { display:flex; gap:8px; align-items:center; padding:8px 0; border-bottom:1px dashed #E2E8F0; }
    .doc-item:last-child { border-bottom:none; }
    .doc-check { width:18px; height:18px; border-radius:50%; display:flex; align-items:center; justify-content:center; flex-shrink:0; font-size:0.62rem; }
    .doc-check.done { background:#E3F2FD; color:#1565C0; }
    .doc-check.wait { background:#F1F5F9; color:#94A3B8; }
    .doc-name { font-size:0.74rem; font-weight:700; color:#1E293B; line-height:1.35; }
    .doc-note { font-size:0.62rem; color:#64748B; }
    .icon-mini-btn { width:34px; height:34px; border:none; border-radius:10px; display:inline-flex; align-items:center; justify-content:center; flex-shrink:0; }
    .icon-mini-btn.upload { background:#E3F2FD; color:#1565C0; }
    .icon-mini-btn.view { background:#F1F5F9; color:#334155; }
    .icon-mini-btn.download { background:#ECFDF5; color:#047857; text-decoration:none; }
    #sla-stage-file-actions > .action-btn, #sla-stage-survey-tools .action-btn { flex:1; width:auto; }
    #sla-stage-survey-tools { flex:1; display:none; }
    #sla-stage-survey-tools.is-visible { display:flex; }
    @media (max-width: 575px) {
        .sla-stage-actions { justify-content:flex-start; flex-wrap:wrap; }
        .sla-stage-actions .stage-attachment-list { width:auto; justify-content:flex-start; }
    }
    .photo-chip { display:inline-flex; align-items:center; gap:6px; border:none; border-radius:10px; padding:7px 10px; background:#F1F5F9; color:#475569; font-size:0.68rem; font-weight:800; }
    .header-quick-actions { display:flex; justify-content:flex-end; gap:8px; margin-top:14px; }
    .header-icon-action { width:36px; height:36px; padding:0; border:none; border-radius:10px; display:none; align-items:center; justify-content:center; background:#F1F5F9; color:#475569; text-decoration:none; }
    .header-icon-action:hover { color:#1E293B; background:#E2E8F0; }
    .preview-frame { width:100%; min-height:420px; border:1px solid #E2E8F0; border-radius:12px; }
    .preview-img { width:100%; max-height:70vh; object-fit:contain; border-radius:12px; background:#F8FAFC; }
    .pdf-mobile-preview { background:#F8FAFC; border:1px solid #E2E8F0; border-radius:12px; padding:14px; color:#475569; max-height:70vh; overflow:auto; }
    .pdf-mobile-state { padding:34px 12px; text-align:center; }
    .pdf-mobile-state i { font-size:2rem; color:#1565C0; margin-bottom:12px; }
    .pdf-mobile-state .title { font-size:0.85rem; font-weight:800; color:#1E293B; margin-bottom:4px; }
    .pdf-mobile-state .hint { font-size:0.68rem; margin-bottom:14px; }
    .pdf-page-canvas { width:100%; height:auto; display:block; background:#fff; border-radius:8px; box-shadow:0 1px 5px rgba(15,23,42,.12); margin-bottom:12px; }
    .pdf-page-canvas:last-child { margin-bottom:0; }
    .pdf-action-row { display:grid; grid-template-columns:1fr; gap:8px; }
    @media (min-width: 420px) { .pdf-action-row { grid-template-columns:repeat(2, minmax(0,1fr)); } }
    .input-evidence-map { width:100%; height:260px; border:1px solid #E2E8F0; border-radius:12px; overflow:hidden; background:#F1F5F9; }
    .input-evidence-map iframe { width:100%; height:100%; border:0; display:block; }
    .input-evidence-meta { margin-top:8px; font-size:0.68rem; color:#64748B; line-height:1.45; }
    .collapse-card .section-title { display:flex; align-items:center; justify-content:space-between; gap:10px; cursor:pointer; margin-bottom:0; }
    .collapse-card .collapse-body { margin-top:12px; }
    .collapse-card.collapsed .collapse-body { display:none; }
    .collapse-card .chev { color:#94A3B8; transition:transform .15s; }
    .collapse-card.collapsed .chev { transform:rotate(-90deg); }
    .sla-subdocs { margin-top:8px; padding:10px; border-radius:12px; background:#F8FAFC; }
    .sla-subdocs .section-title { margin-bottom:0; }
    .sla-subdocs .collapse-body { margin-top:8px; }
    .stage-action-row { display:flex; align-items:center; gap:8px; margin-top:8px; }
    .next-stage-pill { flex:1; background:#F1F5F9; color:#1E293B; border-radius:12px; padding:10px 12px; font-size:0.72rem; font-weight:800; }
</style>


<div class="header-compact">
    <div class="d-flex align-items-center">
        <a href="<?= htmlspecialchars($detail_back_url, ENT_QUOTES, 'UTF-8') ?>" class="text-white me-3"><i class="fa-solid fa-arrow-left fs-5"></i></a>
        <div>
                <h5 class="fw-bold mb-0"><?= htmlspecialchars($detail_page_title, ENT_QUOTES, 'UTF-8') ?></h5>
            <p class="small text-white-50 mb-0" style="font-size:0.7rem;">ID #<?= htmlspecialchars($prospect_id ?? '-') ?></p>
        </div>
    </div>
</div>

<div class="detail-container<?= $detail_simple_mode ? ' simple-prospect-detail' : '' ?>">
    <!-- Loading state -->
    <div id="detail-loading" class="detail-card text-center py-5">
        <i class="fa-solid fa-spinner fa-spin fs-3 text-muted"></i>
        <p class="text-muted mt-2 small">Memuat data...</p>
    </div>

    <!-- Content (filled by JS) -->
    <div id="detail-content" style="display:none;">
        <!-- Header card -->
        <div class="detail-card">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <span class="badge-lg" id="d-type-badge">-</span>
                <span class="badge-lg" id="d-status-badge">-</span>
            </div>
            <div class="header-main">
                <div class="header-title-block">
                    <h5 class="fw-bold text-dark mb-1" id="d-name">-</h5>
                    <div class="sla-summary" id="sla-summary">
                        <div class="sla-metric">
                            <span class="sla-label">Pipeline</span>
                            <span class="sla-metric-value money" id="sla-pipeline-amount">-</span>
                        </div>
                        <div class="sla-metric">
                            <span class="sla-label">Real</span>
                            <span class="sla-metric-value money"><span id="sla-realization-amount">-</span><span class="sla-realization-date" id="sla-realization-date"></span></span>
                        </div>
                        <div class="sla-metric">
                            <span class="sla-label">SLA</span>
                            <span class="sla-metric-value sla-days" id="sla-days">0</span>
                        </div>
                        <div class="sla-metric">
                            <span class="sla-label">Rasio</span>
                            <span class="sla-metric-value percent" id="sla-realization-percent">-</span>
                        </div>
                    </div>
                    <div class="sla-realization-plan missing" id="sla-realization-plan" style="display:none;"></div>
                </div>
            </div>
            <div class="header-quick-actions" id="header-quick-actions">
                <button type="button" class="header-icon-action" id="btn-header-photo" title="Lihat Foto Prospek"><i class="fa-solid fa-camera"></i></button>
                <button type="button" class="header-icon-action" id="btn-header-map" title="Lihat Titik Lokasi"><i class="fa-solid fa-location-dot"></i></button>
                <a class="header-icon-action" id="btn-header-wa" title="Hubungi via WhatsApp" target="_blank" rel="noopener"><i class="fa-brands fa-whatsapp"></i></a>
            </div>
        </div>

        <!-- Info Nasabah -->
        <div class="detail-card collapse-card">
            <h6 class="section-title" onclick="toggleCollapse(this)"><span><i class="fa-solid fa-user me-2 text-accent"></i>Informasi Nasabah</span><i class="fa-solid fa-chevron-down chev"></i></h6>
            <div class="collapse-body">
                <div class="info-row"><span class="info-label">Nama</span><span class="info-value" id="d-cust-name">-</span></div>
                <div class="info-row"><span class="info-label">No. HP</span><span class="info-value" id="d-phone">-</span></div>
                <div class="info-row"><span class="info-label">No. Identitas</span><span class="info-value" id="d-identity">-</span></div>
                <div class="info-row"><span class="info-label">Jenis Usaha</span><span class="info-value text-accent" id="d-jenis-usaha">-</span></div>
                <div class="info-row"><span class="info-label">Produk</span><span class="info-value" id="d-product-detail">-</span></div>
                <div class="info-row"><span class="info-label">Cabang</span><span class="info-value" id="d-cabang">-</span></div>
                <div class="info-row"><span class="info-label">Alamat</span><span class="info-value" id="d-alamat" style="font-size:0.75rem;">-</span></div>
            </div>
        </div>

        <!-- SLA Summary (muncul sejak pipeline kredit dibuat) -->
        <div id="sla-section" style="display:none;">
            <div class="detail-card">
                <h6 class="section-title"><i class="fa-solid fa-chart-gantt me-2 text-accent"></i>Pipeline SLA Kredit</h6>
                <div id="sla-stages"></div>
                <div class="sla-subdocs collapse-card collapsed" id="credit-docs-section" style="display:none;">
                    <h6 class="section-title" onclick="toggleCreditDocs(this)">
                            <span style="font-size:0.72rem;font-weight:800;color:#1E293B;"><i class="fa-solid fa-folder-open me-1 text-accent"></i>Dokumen Pipeline (Opsional)</span>
                        <span class="d-inline-flex align-items-center gap-2">
                            <span style="font-size:0.62rem;color:#64748B;font-weight:700;" id="credit-docs-summary">-</span>
                            <i class="fa-solid fa-chevron-down chev"></i>
                        </span>
                    </h6>
                    <div class="collapse-body" id="credit-docs-list"></div>
                </div>
            </div>
        </div>

        <!-- Info Proses -->
        <div class="detail-card collapse-card">
            <h6 class="section-title" onclick="toggleCollapse(this)"><span><i class="fa-solid fa-gears me-2 text-accent"></i>Informasi Proses</span><i class="fa-solid fa-chevron-down chev"></i></h6>
            <div class="collapse-body">
                <div class="info-row"><span class="info-label">Diinput Oleh</span><span class="info-value" id="d-created-by">-</span></div>
                <div class="info-row"><span class="info-label">Tanggal Input</span><span class="info-value" id="d-created-at">-</span></div>
                <div class="info-row"><span class="info-label">Sumber</span><span class="info-value" id="d-source">-</span></div>
                <div class="info-row"><span class="info-label">Delegasi</span><span class="info-value" id="d-delegasi">-</span></div>
                <div class="info-row"><span class="info-label">AO Pengelola</span><span class="info-value" id="d-ao">-</span></div>
                <div class="info-row credit-only-detail"><span class="info-label">Status SLIK</span><span class="info-value" id="d-slik-status">-</span></div>
                <div class="info-row credit-only-detail"><span class="info-label">Nomor IDEP</span><span class="info-value" id="d-idep-number">-</span></div>
                <div class="info-row credit-only-detail"><span class="info-label">File IDEP</span><span class="info-value"><span class="d-inline-flex align-items-center justify-content-end gap-2"><span id="d-idep-file">-</span><button type="button" class="idep-upload-button" id="btn-view-idep-analysis" title="Lihat hasil analisis SLIK" style="display:none;"><i class="fa-solid fa-chart-line"></i></button><button type="button" class="idep-upload-button" id="btn-upload-idep" title="Upload atau ganti file IDEP" style="display:none;"><i class="fa-solid fa-upload"></i></button></span></span></div>
                <div class="info-row credit-only-detail"><span class="info-label">Rencana Realisasi</span><span class="info-value" id="d-planned-realization">-</span></div>
            </div>
        </div>
        <input type="file" id="detail-idep-file" class="d-none" accept=".txt,text/plain">

        <!-- Keterangan -->
        <div class="detail-card">
            <h6 class="section-title"><i class="fa-solid fa-note-sticky me-2 text-accent"></i>Keterangan</h6>
            <p class="small text-muted mb-0" id="d-desc" style="font-style:italic; line-height:1.5;">-</p>
        </div>

        <!-- Follow Ups -->
        <div class="detail-card collapse-card collapsed" id="followup-section">
            <h6 class="section-title" onclick="toggleCollapse(this)"><span><i class="fa-solid fa-phone-volume me-2 text-accent"></i>Riwayat Follow Up</span><i class="fa-solid fa-chevron-down chev"></i></h6>
            <div class="collapse-body" id="followup-list"><p class="small text-muted">Belum ada follow up</p></div>
        </div>

        <!-- Timeline History -->
        <div class="detail-card collapse-card collapsed">
            <h6 class="section-title" onclick="toggleCollapse(this)"><span><i class="fa-solid fa-timeline me-2 text-accent"></i>Riwayat Aktivitas</span><i class="fa-solid fa-chevron-down chev"></i></h6>
            <div class="collapse-body"><ul class="timeline" id="timeline-list"></ul></div>
        </div>

        <!-- Actions -->
        <div id="action-section"></div>

    </div>
</div>


<!-- Modal Follow Up -->
<div class="modal fade" id="modalFollowUp" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered" style="margin:15px; max-width:500px;">
        <div class="modal-content" style="border-radius:16px;">
            <div class="modal-header border-0 pb-0">
                <h6 class="modal-title fw-bold"><i class="fa-solid fa-phone-volume text-primary me-2"></i>Input Follow Up</h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="form-followup">
                    <div class="mb-3">
                        <label class="form-label-custom">Tanggal</label>
                        <input type="date" class="input-custom" name="follow_up_date" value="<?= date('Y-m-d') ?>" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label-custom">Metode</label>
                        <select class="input-custom" name="method" required>
                            <option value="TELEPON">Telepon</option>
                            <option value="WHATSAPP">WhatsApp</option>
                            <option value="KUNJUNGAN">Kunjungan</option>
                            <option value="BERTEMU_DI_KANTOR">Bertemu di Kantor</option>
                            <option value="LAINNYA">Lainnya</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label-custom">Hasil <span class="text-danger">*</span></label>
                        <textarea class="input-custom" name="result" rows="2" placeholder="Hasil follow up..." required></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label-custom">Rencana Selanjutnya</label>
                        <input type="text" class="input-custom" name="next_plan" placeholder="Rencana tindak lanjut">
                    </div>
                    <button type="submit" class="action-btn btn-closing">Simpan Follow Up</button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Modal Debitur Mau Lanjut -->
<div class="modal fade" id="modalCreditInterest" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered" style="margin:15px; max-width:500px;">
        <div class="modal-content" style="border-radius:16px;">
            <div class="modal-header border-0 pb-0">
                <h6 class="modal-title fw-bold"><i class="fa-solid fa-handshake text-primary me-2"></i>Debitur Mau Lanjut</h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="form-credit-interest">
                    <!-- # COMPONENT: Keputusan SLIK menjadi gate sebelum data pipeline dapat dilengkapi. -->
                    <div class="ui-field mb-3">
                        <label class="ui-field-label form-label-custom" for="credit-slik-status">Hasil SLIK <span class="text-danger">*</span></label>
                        <select class="ui-select input-custom" id="credit-slik-status" name="slik_checked" required>
                            <option value="">-- Pilih hasil SLIK --</option>
                            <option value="1">Bagus dan dapat diproses</option>
                            <option value="0">Tidak bagus / langsung Reject</option>
                        </select>
                        <small class="ui-field-help text-muted">SLIK tidak bagus akan langsung mengubah status prospek menjadi Reject.</small>
                    </div>
                    <div id="credit-good-fields" hidden>
                        <!-- # COMPONENT: Field lanjutan hanya aktif setelah hasil SLIK dinyatakan bagus. -->
                        <div class="ui-field mb-3">
                            <label class="ui-field-label form-label-custom" for="credit-requested-amount">Jumlah Pengajuan Debitur <span class="text-danger">*</span></label>
                            <input class="ui-input input-custom" id="credit-requested-amount" type="text" name="requested_loan_amount" inputmode="numeric" placeholder="Contoh: 5.000.000" data-credit-required>
                            <small class="ui-field-help text-muted">Nominal otomatis diformat saat diketik.</small>
                        </div>
                        <div class="ui-field mb-3">
                            <label class="ui-field-label form-label-custom" for="credit-idep-number">Nomor IDEP <span class="text-danger">*</span></label>
                            <input class="ui-input input-custom" id="credit-idep-number" type="text" name="idep_number" maxlength="50" placeholder="Masukkan nomor IDEP" data-credit-required>
                            <small class="ui-field-help text-muted">Nomor IDEP wajib diisi. File TXT hanya sebagai lampiran opsional.</small>
                        </div>
                        <div class="ui-field mb-3">
                            <label class="ui-field-label form-label-custom" for="credit-idep-file">Lampiran IDEP <span class="text-muted">(opsional)</span></label>
                            <input class="ui-input input-custom" id="credit-idep-file" type="file" name="idep_file" accept=".txt,text/plain">
                            <small class="ui-field-help text-muted">Format JSON IDEP berekstensi TXT, maksimal 2 MB. Ringkasan SLIK akan dibaca otomatis.</small>
                        </div>
                        <!-- # COMPONENT: Panel analisis lengkap dipasang di sini setelah file IDEP dipilih. -->
                        <div id="credit-idep-analysis-slot" hidden></div>
                        <div class="ui-field mb-3">
                            <label class="ui-field-label form-label-custom" for="credit-realization-date">Rencana Tanggal Realisasi <span class="text-danger">*</span></label>
                            <input class="ui-input input-custom" id="credit-realization-date" type="date" name="planned_realization_date" value="<?= date('Y-m-d') ?>" data-credit-required>
                        </div>
                    </div>
                    <button type="submit" class="ui-button ui-button--primary action-btn btn-sla">Pilih Hasil SLIK</button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Modal Konfirmasi Pemberkasan -->
<div class="modal fade" id="modalCompleteDocs" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered" style="margin:15px; max-width:500px;">
        <div class="modal-content" style="border-radius:16px;">
            <div class="modal-header border-0 pb-0">
                <h6 class="modal-title fw-bold"><i class="fa-solid fa-play text-primary me-2"></i>Mulai Proses SLA</h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p class="small text-muted mb-3">Mulai proses dan hitung SLA kredit sekarang? Upload dokumen dapat dilakukan kapan saja setelah pipeline dibuat.</p>
                <div class="d-grid gap-2">
                    <button type="button" class="ui-button ui-button--primary action-btn btn-sla mb-0" id="btn-confirm-complete-docs">Mulai Proses SLA</button>
                    <button type="button" class="action-btn btn-wa mb-0" data-bs-dismiss="modal">Batal</button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Closing -->
<div class="modal fade" id="modalClosing" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered" style="margin:15px; max-width:500px;">
        <div class="modal-content" style="border-radius:16px;">
            <div class="modal-header border-0 pb-0">
                <h6 class="modal-title fw-bold"><i class="fa-solid fa-check-double text-success me-2"></i>Closing Prospek</h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="form-closing">
                    <div class="mb-3">
                        <label class="form-label-custom">Nomor Rekening / Nama Debitur <span class="text-danger">*</span></label>
                        <input type="text" class="input-custom" name="closing_account_number" placeholder="Nomor rekening atau nama debitur" required>
                        <div id="closing-account-hint" class="small mt-2" style="font-size:0.7rem;color:#64748B;">Ketik nomor rekening atau nama debitur untuk mengambil realisasi 3 bulan terakhir.</div>
                    </div>
                    <div id="closing-realization-preview" class="mb-3" style="display:none;background:#F8FAFC;border:1px solid #E2E8F0;border-radius:12px;padding:12px;">
                        <div class="d-flex justify-content-between gap-2 mb-2">
                            <span style="font-size:0.65rem;font-weight:800;color:#64748B;text-transform:uppercase;">Nasabah</span>
                            <span id="closing-lookup-name" style="font-size:0.78rem;font-weight:800;color:#0A1931;text-align:right;">-</span>
                        </div>
                        <div class="d-flex justify-content-between gap-2 mb-2">
                            <span style="font-size:0.65rem;font-weight:800;color:#64748B;text-transform:uppercase;">Produk</span>
                            <span id="closing-lookup-product" style="font-size:0.74rem;font-weight:700;color:#334155;text-align:right;">-</span>
                        </div>
                        <div class="d-flex justify-content-between gap-2 mb-2">
                            <span style="font-size:0.65rem;font-weight:800;color:#64748B;text-transform:uppercase;">Realisasi</span>
                            <span id="closing-lookup-amount" style="font-size:0.82rem;font-weight:900;color:#00796B;text-align:right;">-</span>
                        </div>
                        <div class="d-flex justify-content-between gap-2 mb-2">
                            <span style="font-size:0.65rem;font-weight:800;color:#64748B;text-transform:uppercase;">Tanggal Realisasi</span>
                            <span id="closing-lookup-date" style="font-size:0.74rem;font-weight:700;color:#334155;text-align:right;">-</span>
                        </div>
                        <div style="font-size:0.68rem;color:#64748B;line-height:1.35;" id="closing-lookup-address">-</div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label-custom">Nominal Realisasi (Rp) <span class="text-danger">*</span></label>
                        <input type="number" class="input-custom" name="closing_realization_amount" placeholder="0" required min="1">
                    </div>
                    <div class="mb-3" id="closing-tenor-field">
                        <label class="form-label-custom">Jangka Waktu (bulan)</label>
                        <input type="number" class="input-custom" name="closing_tenor" placeholder="12">
                    </div>
                    <div class="mb-3" id="closing-asset-name-field" style="display:none;">
                        <label class="form-label-custom">Nama Aset</label>
                        <input type="text" class="input-custom" name="closing_asset_name" placeholder="Contoh: Rumah / Tanah / Kendaraan">
                    </div>
                    <div class="mb-3" id="closing-buyer-field" style="display:none;">
                        <label class="form-label-custom">Nama Pembeli <span class="text-danger">*</span></label>
                        <input type="text" class="input-custom" name="closing_buyer_name" placeholder="Nama calon pembeli">
                    </div>
                    <div class="mb-3" id="closing-asset-method-field" style="display:none;">
                        <label class="form-label-custom">Metode Pembelian <span class="text-danger">*</span></label>
                        <select class="input-custom" name="closing_asset_purchase_method">
                            <option value="">-- Pilih Metode --</option>
                            <option value="LELANG">Lelang</option>
                            <option value="CESSIE">Cessie</option>
                            <option value="LAINNYA">Lainnya</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label-custom">Catatan</label>
                        <textarea class="input-custom" name="closing_note" rows="2" placeholder="Catatan closing..."></textarea>
                    </div>
                    <button type="submit" class="action-btn btn-closing">Konfirmasi Closing</button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Modal Reject -->
<div class="modal fade" id="modalReject" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered" style="margin:15px; max-width:500px;">
        <div class="modal-content" style="border-radius:16px;">
            <div class="modal-header border-0 pb-0">
                <h6 class="modal-title fw-bold"><i class="fa-solid fa-xmark text-danger me-2"></i>Reject Prospek</h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="form-reject">
                    <div class="mb-3">
                        <label class="form-label-custom">Alasan Reject <span class="text-danger">*</span></label>
                        <select class="input-custom" name="reject_reason" required>
                            <option value="">-- Pilih Alasan --</option>
                            <option value="Tidak berminat">Tidak berminat</option>
                            <option value="Tidak dapat dihubungi">Tidak dapat dihubungi</option>
                            <option value="Data tidak memenuhi syarat">Data tidak memenuhi syarat</option>
                            <option value="Pengajuan tidak disetujui">Pengajuan tidak disetujui</option>
                            <option value="Alasan lain">Alasan lain</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label-custom">Catatan</label>
                        <textarea class="input-custom" name="reject_note" rows="2" placeholder="Detail alasan..."></textarea>
                    </div>
                    <button type="submit" class="action-btn btn-reject">Konfirmasi Reject</button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Modal Delegasi -->
<div class="modal fade" id="modalDelegasi" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered" style="margin:15px; max-width:500px;">
        <div class="modal-content" style="border-radius:16px;">
            <div class="modal-header border-0 pb-0">
                <h6 class="modal-title fw-bold"><i class="fa-solid fa-user-gear text-primary me-2"></i>Delegasi ke AO</h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="form-delegasi">
                    <div class="mb-3">
                        <label class="form-label-custom">Pilih AO <span class="text-danger">*</span></label>
                        <select class="input-custom" name="assigned_to" id="sel-ao-delegasi" required>
                            <option value="">-- Memuat AO... --</option>
                        </select>
                        <small class="text-muted d-block mt-1" id="delegasi-hint" style="font-size:0.6rem;"></small>
                    </div>
                    <button type="submit" class="action-btn btn-delegasi">Delegasikan</button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Modal SLA Stage -->
<div class="modal fade" id="modalSlaStage" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered" style="margin:15px; max-width:500px;">
        <div class="modal-content" style="border-radius:16px;">
            <div class="modal-header border-0 pb-0">
                <h6 class="modal-title fw-bold"><i class="fa-solid fa-chart-gantt text-success me-2"></i>Tambah Tahap SLA</h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="form-sla-stage">
                    <input type="hidden" name="stage" id="sla-next-stage-value">
                    <div class="mb-3">
                        <label class="form-label-custom">Tahap Berikutnya</label>
                        <div class="next-stage-pill" id="sla-next-stage-label">-</div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label-custom">Catatan</label>
                        <input type="text" class="input-custom" name="note" placeholder="Catatan tahap...">
                    </div>
                    <div class="mb-3" id="sla-stage-analyst-wrap" style="display:none;">
                        <label class="form-label-custom">Analis Cabang <span class="text-danger">*</span></label>
                        <select class="input-custom" name="analyst_employee_id" id="sla-stage-analyst">
                            <option value="">-- Pilih analis cabang --</option>
                        </select>
                        <small class="text-muted d-block mt-1" style="font-size:0.65rem;">Staf Analis Kredit dan Appraisal sesuai cabang prospek.</small>
                    </div>
                    <div class="mb-3" id="sla-stage-file-wrap" style="display:none;">
                        <label class="form-label-custom" id="sla-stage-file-label">Lampiran</label>
                        <input type="file" class="d-none" name="attachment_file" id="sla-stage-file">
                        <div id="sla-stage-file-actions" class="d-flex gap-2 mt-2">
                            <button type="button" class="action-btn btn-follow-up mb-0" id="sla-stage-file-button"><i class="fa-solid fa-images me-2"></i>Pilih Foto</button>
                            <div id="sla-stage-survey-tools" class="gap-2">
                                <button type="button" class="action-btn btn-sla mb-0" id="sla-stage-camera-button"><i class="fa-solid fa-camera me-2"></i>Jepret Foto</button>
                                <input type="file" id="sla-stage-camera" class="d-none" accept="image/*" capture="environment">
                            </div>
                        </div>
                        <div id="sla-stage-file-list" aria-live="polite"></div>
                        <small class="text-muted d-block mt-1" id="sla-stage-file-hint" style="font-size:0.65rem;"></small>
                    </div>
                    <button type="submit" class="action-btn btn-sla">Simpan Tahap</button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Modal Kamera Survey -->
<div class="modal fade" id="modalSlaCamera" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered" style="margin:15px; max-width:500px;">
        <div class="modal-content" style="border-radius:16px;">
            <div class="modal-header border-0 pb-0">
                <h6 class="modal-title fw-bold"><i class="fa-solid fa-camera text-primary me-2"></i>Jepret Foto Survey</h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <video id="sla-camera-video" class="sla-camera-video" autoplay playsinline></video>
                <canvas id="sla-camera-canvas" class="d-none"></canvas>
                <small id="sla-camera-hint" class="text-muted d-block mt-2">Pastikan objek terlihat jelas.</small>
                <button type="button" class="action-btn btn-sla mt-3 mb-0" id="sla-camera-capture"><i class="fa-solid fa-camera me-2"></i>Ambil Foto</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Galeri Foto Survey -->
<div class="modal fade" id="modalSurveyGallery" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-lg" style="margin:15px;">
        <div class="modal-content" style="border-radius:16px;">
            <div class="modal-header border-0 pb-0">
                <h6 class="modal-title fw-bold"><i class="fa-solid fa-images text-primary me-2"></i>Foto Survey</h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="survey-gallery-grid" id="survey-gallery-body"></div>
            </div>
        </div>
    </div>
</div>

<!-- # COMPONENT: Modal hasil analisis SLIK dari file IDEP. -->
<div class="modal fade" id="modalIdepAnalysis" tabindex="-1" aria-labelledby="idep-analysis-modal-title" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" style="margin:15px; max-width:520px;">
        <div class="modal-content" style="border-radius:16px;">
            <div class="modal-header border-0 pb-0">
                <h6 class="modal-title fw-bold" id="idep-analysis-modal-title"><i class="fa-solid fa-shield-halved text-primary me-2"></i>Hasil Analisis SLIK</h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
            </div>
            <div class="modal-body">
                <div class="idep-analysis-panel" id="idep-analysis-panel">
                    <div class="idep-analysis-head">
                        <span class="idep-analysis-title"><i class="fa-solid fa-file-lines me-1"></i>Screening IDEP</span>
                        <span class="idep-analysis-badge missing" id="idep-analysis-badge">Belum dianalisis</span>
                    </div>
                    <div class="idep-analysis-grid">
                        <div class="idep-analysis-metric"><span class="idep-analysis-metric-label">Posisi data terakhir</span><strong class="idep-analysis-metric-value" id="idep-analysis-position">-</strong></div>
                        <div class="idep-analysis-metric"><span class="idep-analysis-metric-label">Kualitas terburuk</span><strong class="idep-analysis-metric-value" id="idep-analysis-quality">-</strong></div>
                        <div class="idep-analysis-metric"><span class="idep-analysis-metric-label">Sebaran kualitas</span><strong class="idep-analysis-metric-value" id="idep-analysis-distribution">-</strong></div>
                        <div class="idep-analysis-metric"><span class="idep-analysis-metric-label">Total fasilitas</span><strong class="idep-analysis-metric-value" id="idep-analysis-facilities">-</strong></div>
                        <div class="idep-analysis-metric"><span class="idep-analysis-metric-label">Fasilitas aktif / lunas</span><strong class="idep-analysis-metric-value" id="idep-analysis-active-facilities">-</strong></div>
                        <div class="idep-analysis-metric"><span class="idep-analysis-metric-label">Jumlah lembaga</span><strong class="idep-analysis-metric-value" id="idep-analysis-lenders">-</strong></div>
                        <div class="idep-analysis-metric"><span class="idep-analysis-metric-label">Plafon efektif</span><strong class="idep-analysis-metric-value" id="idep-analysis-limit">-</strong></div>
                        <div class="idep-analysis-metric"><span class="idep-analysis-metric-label">Baki debet</span><strong class="idep-analysis-metric-value" id="idep-analysis-outstanding">-</strong></div>
                        <div class="idep-analysis-metric"><span class="idep-analysis-metric-label">Utilisasi plafon</span><strong class="idep-analysis-metric-value" id="idep-analysis-utilization">-</strong></div>
                        <div class="idep-analysis-metric"><span class="idep-analysis-metric-label">Jumlah pengajuan</span><strong class="idep-analysis-metric-value" id="idep-analysis-requested">-</strong></div>
                        <div class="idep-analysis-metric"><span class="idep-analysis-metric-label">Eksposur setelah pengajuan</span><strong class="idep-analysis-metric-value" id="idep-analysis-exposure">-</strong></div>
                        <div class="idep-analysis-metric"><span class="idep-analysis-metric-label">Hari tunggakan</span><strong class="idep-analysis-metric-value" id="idep-analysis-overdue-days">-</strong></div>
                        <div class="idep-analysis-metric"><span class="idep-analysis-metric-label">Tunggakan pokok</span><strong class="idep-analysis-metric-value" id="idep-analysis-principal-arrears">-</strong></div>
                        <div class="idep-analysis-metric"><span class="idep-analysis-metric-label">Tunggakan bunga</span><strong class="idep-analysis-metric-value" id="idep-analysis-interest-arrears">-</strong></div>
                        <div class="idep-analysis-metric"><span class="idep-analysis-metric-label">Denda</span><strong class="idep-analysis-metric-value" id="idep-analysis-penalty">-</strong></div>
                    </div>
                    <p class="idep-analysis-note" id="idep-analysis-note"></p>
                    <p class="idep-analysis-note"><strong>Catatan overvalue:</strong> <span id="idep-analysis-overvalue">-</span></p>
                    <div class="idep-analysis-section">
                        <div class="idep-analysis-section-title"><i class="fa-solid fa-calculator me-1"></i>Kalkulator kelayakan plafon</div>
                        <p class="idep-analysis-note">Isi data aktual debitur. Angka ini adalah simulasi internal dan batas DSR dapat disesuaikan dengan kebijakan produk.</p>
                        <div class="idep-calculator-grid">
                            <label class="idep-calculator-field"><span>Penghasilan bersih / bulan</span><input class="ui-input" id="calc-net-income" type="text" inputmode="numeric" placeholder="Contoh: 7.000.000" data-number-format></label>
                            <label class="idep-calculator-field"><span>Angsuran Kredit Lain / Bulan</span><input class="ui-input" id="calc-existing-installment" type="text" inputmode="numeric" placeholder="Contoh: 1.000.000" data-number-format></label>
                            <label class="idep-calculator-field"><span>Jumlah pengajuan</span><input class="ui-input" id="calc-requested-amount" type="text" inputmode="numeric" placeholder="Nominal pengajuan" data-number-format></label>
                            <label class="idep-calculator-field"><span>Tenor (bulan)</span><input class="ui-input" id="calc-tenor" type="number" min="1" max="240" value="36"></label>
                            <label class="idep-calculator-field"><span>Bunga per tahun (%)</span><input class="ui-input" id="calc-annual-rate" type="number" min="0" max="100" step="0.01" value="12"></label>
                            <label class="idep-calculator-field"><span>Batas DSR (%)</span><input class="ui-input" id="calc-dsr-limit" type="number" min="1" max="100" step="1" value="35"></label>
                        </div>
                        <div class="idep-calculator-result" id="idep-calculator-result">
                            <div class="idep-analysis-head"><span class="idep-analysis-title"><i class="fa-solid fa-scale-balanced me-1"></i>Hasil simulasi</span><span class="idep-analysis-badge missing" id="calc-eligibility">Lengkapi data</span></div>
                            <div class="idep-analysis-grid">
                                <div class="idep-analysis-metric"><span class="idep-analysis-metric-label">Angsuran baru</span><strong class="idep-analysis-metric-value" id="calc-new-installment">-</strong></div>
                                <div class="idep-analysis-metric"><span class="idep-analysis-metric-label">Total DSR</span><strong class="idep-analysis-metric-value" id="calc-total-dsr">-</strong></div>
                                <div class="idep-analysis-metric"><span class="idep-analysis-metric-label">Plafon rekomendasi</span><strong class="idep-analysis-metric-value" id="calc-recommended-plafond">-</strong></div>
                                <div class="idep-analysis-metric"><span class="idep-analysis-metric-label">Sisa kemampuan angsuran</span><strong class="idep-analysis-metric-value" id="calc-available-installment">-</strong></div>
                            </div>
                            <p class="idep-analysis-note" id="calc-eligibility-note">Masukkan penghasilan bersih dan data kewajiban bulanan untuk menghitung.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<input type="file" id="credit-doc-file" class="d-none">

<!-- Modal Preview File -->
<div class="modal fade" id="modalFilePreview" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-lg" style="margin:15px;">
        <div class="modal-content" style="border-radius:16px;">
            <div class="modal-header border-0 pb-0">
                <h6 class="modal-title fw-bold" id="file-preview-title"><i class="fa-solid fa-eye text-primary me-2"></i>Preview</h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="file-preview-body"></div>
        </div>
    </div>
</div>

<!-- Modal Preview Map -->
<div class="modal fade" id="modalMapPreview" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-lg" style="margin:15px;">
        <div class="modal-content" style="border-radius:16px;">
            <div class="modal-header border-0 pb-0">
                <h6 class="modal-title fw-bold"><i class="fa-solid fa-location-dot text-primary me-2"></i>Titik Lokasi</h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div id="map-preview-body"></div>
            </div>
        </div>
    </div>
</div>


<script>
(function() {
    const BASE_APP = <?= json_encode(BASE_APP) ?>;
    const prospectId = <?= json_encode($prospect_id) ?>;
    const userRole = '<?= $user_role ?>';
    const userEmployeeId = <?= json_encode($user_employee_id) ?>;
    const isAO = <?= $is_ao ? 'true' : 'false' ?>;
    const isSuperuser = <?= $is_superuser ? 'true' : 'false' ?>;
    const canDelegateProspek = <?= $can_delegate_prospek ? 'true' : 'false' ?>;
    let prospectData = null;
    let pdfJsLoadPromise = null;

    if (!prospectId) { showError('ID prospek tidak valid'); return; }

    // =========================================
    // LOAD DETAIL FROM API
    // =========================================
    async function loadDetail() {
        try {
            const res = await fetch(BASE_APP + '/api/?action=prospect_detail&id=' + prospectId, {credentials:'include'});
            const body = await res.json();
            if (body.status === 200 && body.data) {
                prospectData = body.data;
                render(prospectData);
            } else {
                showError(body.message || 'Prospek tidak ditemukan');
            }
        } catch(e) {
            showError('Gagal memuat data dari server');
        }
    }

    function showError(msg) {
        document.getElementById('detail-loading').innerHTML = `<i class="fa-solid fa-circle-xmark text-danger fs-2 d-block mb-2"></i><p class="text-muted small">${msg}</p><a href="${BASE_APP}/daftar-prospek" class="btn btn-sm btn-outline-primary mt-2">Kembali</a>`;
    }

    function canUpdateSlaUi(p) {
        return userRole === 'developer' || (isAO && p?.assigned_to && p.assigned_to === userEmployeeId);
    }

    function isWithinUploadWindow(uploadedAt) {
        if (!uploadedAt) return true;
        const limit = new Date(uploadedAt.replace(' ', 'T'));
        limit.setDate(limit.getDate() + 7);
        return new Date() <= limit;
    }

    function digitsOnly(value) {
        return String(value || '').replace(/\D/g, '');
    }

    function formatPlainNumber(value) {
        const digits = digitsOnly(value);
        return digits ? digits.replace(/\B(?=(\d{3})+(?!\d))/g, '.') : '';
    }

    function formatRupiah(value) {
        const amount = Number(value || 0);
        return amount ? 'Rp ' + amount.toLocaleString('id-ID') : '-';
    }

    function formatEmployee(id, name) {
        if (name && id) return `${name} (${id})`;
        return name || id || '-';
    }

    function getProductLabel(p) {
        const typeLabels = {
            KREDIT: 'Kredit',
            TABUNGAN: 'Tabungan',
            DEPOSITO: 'Deposito',
            PEMBELI_ASET: 'Pembeli Aset',
            DEBITUR_EXISTING: 'Debitur Existing'
        };
        const type = typeLabels[p.prospect_type] || p.prospect_type || '';
        const product = p.rekomendasi_produk || '';
        if (product && type && product.toLowerCase() !== type.toLowerCase()) return `${product} - ${type}`;
        return product || type || '-';
    }

    window.toggleCollapse = function(titleEl) {
        titleEl.closest('.collapse-card')?.classList.toggle('collapsed');
    };

    window.toggleCreditDocs = function(titleEl) {
        titleEl.closest('.collapse-card')?.classList.toggle('collapsed');
    };

    // =========================================
    // RENDER
    // =========================================
    function render(p) {
        document.getElementById('detail-loading').style.display = 'none';
        document.getElementById('detail-content').style.display = 'block';

        // Type badge
        const typeMap = {KREDIT:{cls:'badge-kredit',l:'Kredit'},TABUNGAN:{cls:'badge-tabungan',l:'Tabungan'},DEPOSITO:{cls:'badge-deposito',l:'Deposito'},PEMBELI_ASET:{cls:'badge-aset',l:'Pembeli Aset'},DEBITUR_EXISTING:{cls:'badge-existing',l:'Existing'}};
        const ti = typeMap[p.prospect_type] || {cls:'',l:p.prospect_type};
        document.getElementById('d-type-badge').className = 'badge-lg badge-type ' + ti.cls;
        document.getElementById('d-type-badge').textContent = ti.l;

        // Status badge
        const pipelineStatus = String(p.credit_pipeline?.pipeline_status || '').toUpperCase();
        const displayStatus = !['CLOSING', 'REJECT'].includes(String(p.status || '').toUpperCase()) && ['PROSPECT_CONFIRMED', 'PENDING'].includes(pipelineStatus)
            ? 'PENDING'
            : p.status;
        const statusMap = {OPEN:{cls:'status-open',l:'Open'},FOLLOW_UP:{cls:'status-follow_up',l:'Follow Up'},SLA:{cls:'status-sla',l:'SLA'},PENDING:{cls:'status-pending',l:'Pending'},CLOSING:{cls:'status-closing',l:'Closing'},REJECT:{cls:'status-reject',l:'Reject'}};
        const si = statusMap[displayStatus] || {cls:'',l:displayStatus};
        document.getElementById('d-status-badge').className = 'badge-lg badge-status ' + si.cls;
        document.getElementById('d-status-badge').textContent = si.l;

        document.getElementById('d-name').textContent = p.customer_name;
        renderHeaderQuickActions(p);

        // Info
        document.getElementById('d-cust-name').textContent = p.customer_name;
        document.getElementById('d-phone').innerHTML = p.phone_number ? `<a href="tel:${p.phone_number}" style="color:inherit;text-decoration:none;">${p.phone_number}</a>` : '-';
        document.getElementById('d-identity').textContent = p.identity_number || '-';
        document.getElementById('d-jenis-usaha').textContent = p.jenis_usaha || '-';
        document.getElementById('d-product-detail').textContent = getProductLabel(p);
        document.getElementById('d-cabang').textContent = (p.kode_kantor||'') + (p.nama_kantor ? ' - '+p.nama_kantor : '');
        document.getElementById('d-alamat').textContent = [p.address, p.desa, p.kecamatan, p.kab_kota].filter(Boolean).join(', ') || '-';
        document.getElementById('d-created-by').textContent = formatEmployee(p.created_by, p.created_by_name);
        document.getElementById('d-created-at').textContent = p.created_at ? fmtDateTime(p.created_at) : '-';
        document.getElementById('d-source').textContent = p.is_ao_input == 1 ? 'Input AO (Auto-delegasi)' : 'Input Non-AO';
        document.getElementById('d-delegasi').innerHTML = p.delegation_status === 'SUDAH_DIDELEGASIKAN' ? '<span style="color:#2E7D32;">Sudah</span>' : '<span style="color:#E65100;">Belum</span>';
        document.getElementById('d-ao').textContent = p.assigned_to ? formatEmployee(p.assigned_to, p.assigned_to_name) : 'Belum ditentukan';
        document.getElementById('d-slik-status').textContent = p.credit_pipeline?.slik_checked === null || p.credit_pipeline?.slik_checked === undefined
            ? '-'
            : Number(p.credit_pipeline.slik_checked) === 1 ? 'Bagus' : 'Tidak bagus';
        document.getElementById('d-idep-number').textContent = p.credit_pipeline?.idep_number || '-';
        const idepFile = p.credit_pipeline?.idep_file_url ? uploadFileUrl(p.credit_pipeline.idep_file_url) : '';
        document.getElementById('d-idep-file').textContent = idepFile ? 'Sudah diupload' : 'Belum diupload';
        const idepUploadButton = document.getElementById('btn-upload-idep');
        const idepViewButton = document.getElementById('btn-view-idep-analysis');
        const idepAnalysisJson = p.credit_pipeline?.idep_analysis_json || null;
        const idepAnalysisError = p.credit_pipeline?.idep_analysis_error || '';
        if (idepUploadButton) {
            // # COMPONENT: Penggantian IDEP sementara tetap tersedia saat closing, tetapi tidak untuk reject.
            idepUploadButton.style.display = p.credit_pipeline && canUpdateSlaUi(p) && String(p.status || '').toUpperCase() !== 'REJECT'
                ? 'inline-flex'
                : 'none';
        }
        if (idepViewButton) {
            // # COMPONENT: File IDEP tetap dapat dilihat statusnya setelah closing, termasuk file lama tanpa ringkasan.
            idepViewButton.style.display = idepFile ? 'inline-flex' : 'none';
            idepViewButton.title = idepAnalysisJson ? 'Lihat hasil analisis SLIK' : (idepAnalysisError || 'Lihat status file IDEP');
        }
        document.getElementById('d-planned-realization').textContent = p.credit_pipeline?.planned_realization_date
            ? fmtDate(p.credit_pipeline.planned_realization_date)
            : '-';
        renderIdepAnalysis(idepAnalysisJson, idepAnalysisError);
        document.getElementById('d-desc').textContent = p.description || p.keterangan_usaha || 'Tidak ada keterangan';

        renderCreditDocs(p.credit_pipeline || null);

        // Pipeline kredit tampil sejak debitur mau lanjut, supaya upload pemberkasan bisa dilakukan sebelum SLA mulai.
        if (p.credit_pipeline) {
            renderHeaderCreditSummary(p);
            renderRealizationPlan(p);
            document.getElementById('sla-section').style.display = 'block';
            renderSlaStages(p.credit_pipeline?.stages || p.sla_logs || []);
        } else {
            document.getElementById('sla-summary').style.display = 'none';
            document.getElementById('sla-realization-plan').style.display = 'none';
            document.getElementById('sla-section').style.display = 'none';
        }

        // Follow ups
        renderFollowUps(p.follow_ups || []);

        // Timeline
        renderTimeline(p.histories || []);

        // Actions
        configureClosingForm(p);
        renderActions(p);
    }

    // # COMPONENT: Menampilkan screening awal dari ringkasan IDEP yang disimpan backend.
    function renderIdepAnalysis(rawAnalysis, analysisError = '') {
        const panel = document.getElementById('idep-analysis-panel');
        if (!panel) return;

        let analysis = rawAnalysis;
        if (typeof analysis === 'string') {
            try { analysis = JSON.parse(analysis); } catch (error) { analysis = null; }
        }
        if (!analysis || typeof analysis !== 'object') {
            window.currentIdepAnalysis = null;
            const badge = document.getElementById('idep-analysis-badge');
            badge.className = 'idep-analysis-badge missing';
            badge.innerHTML = '<i class="fa-solid fa-circle-info"></i>Belum tersedia';
            const setResult = (id, value) => { const el = document.getElementById(id); if (el) el.textContent = value; };
            ['idep-analysis-position', 'idep-analysis-quality', 'idep-analysis-distribution', 'idep-analysis-facilities', 'idep-analysis-active-facilities', 'idep-analysis-lenders', 'idep-analysis-limit', 'idep-analysis-outstanding', 'idep-analysis-utilization', 'idep-analysis-requested', 'idep-analysis-exposure', 'idep-analysis-overdue-days', 'idep-analysis-principal-arrears', 'idep-analysis-interest-arrears', 'idep-analysis-penalty'].forEach(id => setResult(id, '-'));
            setResult('idep-analysis-note', analysisError || 'File IDEP tersedia, tetapi ringkasan analisis SLIK belum tersedia.');
            setResult('idep-analysis-overvalue', 'Belum dapat dinilai sebelum file IDEP berhasil dianalisis.');
            panel.style.display = 'block';
            return;
        }
        window.currentIdepAnalysis = analysis;

        const code = String(analysis.recommendation_code || 'DATA_TIDAK_LENGKAP');
        const badgeClass = code === 'BAGUS' ? 'good' : (code === 'TIDAK_BAGUS' ? 'risk' : (code === 'PERLU_REVIEW' ? 'review' : 'missing'));
        const badge = document.getElementById('idep-analysis-badge');
        badge.className = 'idep-analysis-badge ' + badgeClass;
        badge.innerHTML = '<i class="fa-solid ' + (code === 'BAGUS' ? 'fa-circle-check' : 'fa-circle-exclamation') + '"></i>' + escapeHtml(analysis.recommendation_label || 'Belum dianalisis');

        const quality = analysis.worst_quality_label || '-';
        const qualityCode = analysis.worst_quality ? ' (' + analysis.worst_quality + ')' : '';
        const qualityDistribution = analysis.quality_distribution || {};
        const qualityShortLabels = {1:'Lancar', 2:'DPK', 3:'Kurang lancar', 4:'Diragukan', 5:'Macet'};
        const distributionText = Object.keys(qualityShortLabels)
            .map((key) => Number(qualityDistribution[key] || 0) > 0 ? qualityShortLabels[key] + ' ' + qualityDistribution[key] : '')
            .filter(Boolean)
            .join(' · ') || '-';
        const formatMoneyOrDash = (value) => Number(value || 0) > 0 ? fmtRupiah(value) : '-';
        document.getElementById('idep-analysis-quality').textContent = quality + qualityCode;
        document.getElementById('idep-analysis-position').textContent = analysis.report_position || '-';
        document.getElementById('idep-analysis-distribution').textContent = distributionText;
        document.getElementById('idep-analysis-facilities').textContent = analysis.facility_count ?? '-';
        document.getElementById('idep-analysis-active-facilities').textContent = (analysis.active_facility_count ?? 0) + ' aktif / ' + (analysis.paid_off_facility_count ?? 0) + ' lunas';
        document.getElementById('idep-analysis-lenders').textContent = analysis.lender_count ?? '-';
        document.getElementById('idep-analysis-limit').textContent = formatMoneyOrDash(analysis.effective_limit);
        const arrears = Number(analysis.total_arrears || 0);
        const overdueDays = Number(analysis.max_overdue_days || 0);
        document.getElementById('idep-analysis-outstanding').textContent = fmtRupiah(analysis.total_outstanding || 0);
        document.getElementById('idep-analysis-utilization').textContent = analysis.utilization_percent === null || analysis.utilization_percent === undefined
            ? 'Tidak tersedia'
            : Number(analysis.utilization_percent).toLocaleString('id-ID') + '%';
        document.getElementById('idep-analysis-requested').textContent = formatMoneyOrDash(analysis.requested_loan_amount);
        document.getElementById('idep-analysis-exposure').textContent = formatMoneyOrDash(analysis.exposure_after_request || analysis.total_outstanding);
        document.getElementById('idep-analysis-overdue-days').textContent = overdueDays + ' hari';
        document.getElementById('idep-analysis-principal-arrears').textContent = formatMoneyOrDash(analysis.total_principal_arrears);
        document.getElementById('idep-analysis-interest-arrears').textContent = formatMoneyOrDash(analysis.total_interest_arrears);
        document.getElementById('idep-analysis-penalty').textContent = formatMoneyOrDash(analysis.total_penalty);
        document.getElementById('idep-analysis-note').textContent = analysis.recommendation_note || 'Screening awal tersedia.';
        document.getElementById('idep-analysis-overvalue').textContent = analysis.overvalue_note || analysis.overvalue_label || 'Belum dapat dinilai dari data IDEP.';
        const requestedInput = document.getElementById('calc-requested-amount');
        if (requestedInput && !requestedInput.dataset.userEdited && Number(analysis.requested_loan_amount || 0) > 0) {
            requestedInput.value = formatPlainNumber(analysis.requested_loan_amount);
        }
        calculateIdepAffordability();
        panel.style.display = 'block';
    }

    // # COMPONENT: Simulasi kemampuan angsuran; parameter dapat disesuaikan dengan kebijakan produk.
    function estimateInstallment(principal, annualRate, tenor) {
        if (principal <= 0 || tenor <= 0) return 0;
        const monthlyRate = annualRate / 100 / 12;
        if (monthlyRate === 0) return principal / tenor;
        return principal * monthlyRate / (1 - Math.pow(1 + monthlyRate, -tenor));
    }

    function estimatePrincipal(installment, annualRate, tenor) {
        if (installment <= 0 || tenor <= 0) return 0;
        const monthlyRate = annualRate / 100 / 12;
        if (monthlyRate === 0) return installment * tenor;
        return installment * (1 - Math.pow(1 + monthlyRate, -tenor)) / monthlyRate;
    }

    function calculateIdepAffordability() {
        const income = Number(digitsOnly(document.getElementById('calc-net-income')?.value || 0));
        const existingInstallment = Number(digitsOnly(document.getElementById('calc-existing-installment')?.value || 0));
        const requested = Number(digitsOnly(document.getElementById('calc-requested-amount')?.value || 0));
        const tenor = Number(document.getElementById('calc-tenor')?.value || 0);
        const annualRate = Number(document.getElementById('calc-annual-rate')?.value || 0);
        const dsrLimit = Number(document.getElementById('calc-dsr-limit')?.value || 0);
        const badge = document.getElementById('calc-eligibility');
        const setResult = (id, value) => { const el = document.getElementById(id); if (el) el.textContent = value; };

        if (income <= 0 || tenor <= 0 || dsrLimit <= 0) {
            badge.className = 'idep-analysis-badge missing';
            badge.innerHTML = '<i class="fa-solid fa-circle-info"></i>Lengkapi data';
            setResult('calc-new-installment', '-');
            setResult('calc-total-dsr', '-');
            setResult('calc-recommended-plafond', '-');
            setResult('calc-available-installment', '-');
            setResult('calc-eligibility-note', 'Masukkan penghasilan bersih dan data kewajiban bulanan untuk menghitung.');
            return;
        }

        const maximumTotalInstallment = income * (dsrLimit / 100);
        const availableInstallment = Math.max(0, maximumTotalInstallment - existingInstallment);
        const recommendedPlafond = estimatePrincipal(availableInstallment, annualRate, tenor);
        const newInstallment = estimateInstallment(requested, annualRate, tenor);
        const totalDsr = income > 0 ? ((existingInstallment + newInstallment) / income) * 100 : 0;
        const withinPaymentCapacity = requested > 0 && requested <= recommendedPlafond && totalDsr <= dsrLimit;
        const slikCode = String(window.currentIdepAnalysis?.recommendation_code || '');
        const slikGood = slikCode === 'BAGUS';
        const simulationEligible = withinPaymentCapacity && slikGood;

        setResult('calc-new-installment', requested > 0 ? fmtRupiah(newInstallment) : '-');
        setResult('calc-total-dsr', requested > 0 ? totalDsr.toLocaleString('id-ID', {maximumFractionDigits:2}) + '%' : '-');
        setResult('calc-recommended-plafond', fmtRupiah(recommendedPlafond));
        setResult('calc-available-installment', fmtRupiah(availableInstallment));

        if (!requested) {
            badge.className = 'idep-analysis-badge review';
            badge.innerHTML = '<i class="fa-solid fa-calculator"></i>Simulasi siap';
            setResult('calc-eligibility-note', 'Plafon rekomendasi dihitung dari penghasilan, kewajiban berjalan, tenor, bunga, dan batas DSR.');
        } else if (!slikGood) {
            badge.className = 'idep-analysis-badge review';
            badge.innerHTML = '<i class="fa-solid fa-triangle-exclamation"></i>Perlu review SLIK';
            setResult('calc-eligibility-note', 'Kemampuan angsuran dapat dihitung, tetapi hasil akhir belum layak dinyatakan karena screening SLIK belum berstatus baik.');
        } else if (simulationEligible) {
            badge.className = 'idep-analysis-badge good';
            badge.innerHTML = '<i class="fa-solid fa-circle-check"></i>Layak secara simulasi';
            setResult('calc-eligibility-note', 'Jumlah pengajuan masih berada di bawah plafon rekomendasi dan batas DSR simulasi. Tetap lakukan verifikasi usaha dan agunan.');
        } else {
            badge.className = 'idep-analysis-badge risk';
            badge.innerHTML = '<i class="fa-solid fa-circle-xmark"></i>Melebihi simulasi';
            setResult('calc-eligibility-note', 'Jumlah pengajuan melebihi plafon rekomendasi atau membuat DSR melewati batas simulasi.');
        }
    }

    function renderHeaderCreditSummary(p) {
        const summary = document.getElementById('sla-summary');
        const pipelineAmount = Number(p.credit_pipeline?.requested_loan_amount || 0);
        const realizationAmount = Number(p.closing_realization_amount || 0);
        const realizationPercent = pipelineAmount > 0 && realizationAmount > 0
            ? Math.round((realizationAmount / pipelineAmount) * 100)
            : null;
        const realizationDate = p.closing_realization_date || p.closed_at || '';

        summary.style.display = 'grid';
        document.getElementById('sla-days').textContent = p.sla_duration_days ?? 0;
        document.getElementById('sla-pipeline-amount').textContent = pipelineAmount > 0 ? fmtRupiah(pipelineAmount) : '-';
        document.getElementById('sla-realization-amount').textContent = realizationAmount > 0 ? fmtRupiah(realizationAmount) : '-';
        document.getElementById('sla-realization-date').textContent = realizationAmount > 0 && realizationDate ? fmtDate(realizationDate) : '';
        document.getElementById('sla-realization-percent').textContent = realizationPercent !== null ? `${realizationPercent}%` : '-';
    }

    // # COMPONENT: Menampilkan deadline realisasi atau hasil realisasi pada header detail pipeline.
    function renderRealizationPlan(p) {
        const el = document.getElementById('sla-realization-plan');
        const status = String(p.status || '').toUpperCase();
        const value = p.credit_pipeline?.planned_realization_date || '';
        el.className = 'sla-realization-plan';
        if (status === 'CLOSING') {
            const actualDate = p.closing_realization_date || p.closed_at || '';
            el.classList.add('completed');
            el.innerHTML = `<i class="fa-solid fa-circle-check"></i><span class="plan-date">${actualDate ? 'Realisasi ' + fmtDate(actualDate) : 'Prospek sudah closing'}</span><strong>Selesai</strong>`;
            el.style.display = 'flex';
            return;
        }
        if (!value) {
            el.classList.add('missing');
            el.innerHTML = '<i class="fa-solid fa-calendar-xmark"></i><span class="plan-date">Rencana realisasi belum diisi</span>';
            el.style.display = 'flex';
            return;
        }
        const parts = String(value).slice(0, 10).split('-').map(Number);
        const target = Date.UTC(parts[0], parts[1] - 1, parts[2]);
        const today = new Date();
        const todayUtc = Date.UTC(today.getFullYear(), today.getMonth(), today.getDate());
        const days = Math.round((target - todayUtc) / 86400000);
        const countdown = days > 0 ? `Kurang ${days} hari` : days === 0 ? 'Hari ini' : `Lewat ${Math.abs(days)} hari`;
        el.classList.add(days > 0 ? 'on-time' : days === 0 ? 'today' : 'overdue');
        el.innerHTML = `<i class="fa-solid fa-calendar-check"></i><span class="plan-date">Rencana realisasi ${fmtDate(value)}</span><strong>${escapeHtml(countdown)}</strong>`;
        el.style.display = 'flex';
    }

    function configureClosingForm(p) {
        const isCredit = ['KREDIT', 'DEBITUR_EXISTING'].includes(p.prospect_type);
        const isDana = ['TABUNGAN', 'DEPOSITO'].includes(p.prospect_type);
        const isAsset = p.prospect_type === 'PEMBELI_ASET';
        const account = document.querySelector('#form-closing [name="closing_account_number"]');
        const amount = document.querySelector('#form-closing [name="closing_realization_amount"]');
        const tenorField = document.getElementById('closing-tenor-field');
        const tenor = document.querySelector('#form-closing [name="closing_tenor"]');
        const accountHint = document.getElementById('closing-account-hint');
        const realizationPreview = document.getElementById('closing-realization-preview');
        const buyer = document.querySelector('#form-closing [name="closing_buyer_name"]');
        const method = document.querySelector('#form-closing [name="closing_asset_purchase_method"]');
        if (account) {
            account.required = isCredit || isDana;
            account.closest('.mb-3').style.display = isAsset ? 'none' : 'block';
            account.placeholder = isDana ? 'Nomor rekening tabungan/deposito' : 'Nomor rekening atau nama debitur';
            account.dataset.lookupEnabled = isCredit ? '1' : '0';
            if (accountHint) {
                accountHint.style.display = isCredit ? 'block' : 'none';
                accountHint.textContent = isCredit ? 'Ketik nomor rekening atau nama debitur untuk mengambil realisasi 3 bulan terakhir.' : '';
                accountHint.style.color = '#64748B';
            }
        }
        if (amount) {
            amount.required = isDana;
            amount.closest('.mb-3').style.display = (isCredit || isAsset) ? 'none' : 'block';
            amount.min = isCredit || isDana ? '1' : '0';
            amount.placeholder = isDana ? 'Nominal setoran/deposito' : 'Nominal realisasi wajib';
            amount.readOnly = isCredit;
        }
        if (tenorField) tenorField.style.display = p.prospect_type === 'DEPOSITO' ? 'block' : 'none';
        if (tenor) tenor.readOnly = isCredit;
        if (!isCredit && realizationPreview) realizationPreview.style.display = 'none';
        document.getElementById('closing-asset-name-field').style.display = isAsset ? 'block' : 'none';
        document.getElementById('closing-buyer-field').style.display = isAsset ? 'block' : 'none';
        document.getElementById('closing-asset-method-field').style.display = isAsset ? 'block' : 'none';
        if (buyer) buyer.required = isAsset;
        if (method) method.required = isAsset;
    }

    function uploadFileUrl(path) {
        const normalized = String(path || '').replace(/^\/+/, '');
        if (!normalized) return '';
        if (/^https?:\/\//i.test(normalized)) return normalized;
        return BASE_APP + '/api/?action=file_upload&path=' + encodeURIComponent(normalized);
    }

    function renderCreditDocs(pipeline) {
        const section = document.getElementById('credit-docs-section');
        const list = document.getElementById('credit-docs-list');
        if (!pipeline || !Array.isArray(pipeline.documents)) {
            section.style.display = 'none';
            return;
        }
        section.style.display = 'block';
        const completed = pipeline.documents.filter(d => Number(d.is_completed) === 1).length;
        document.getElementById('credit-docs-summary').textContent = `${completed}/${pipeline.documents.length} berkas`;
        list.innerHTML = pipeline.documents.map(d => {
            const done = Number(d.is_completed) === 1;
            const isForm = d.doc_code === 'FORMULIR';
            const accept = isForm ? 'image/*' : 'application/pdf';
            const hint = isForm ? 'Foto/scan' : 'PDF';
            const safeName = String(d.doc_name || '').replace(/'/g, "\\'");
            const fileType = d.file_type || (isForm ? 'IMAGE' : 'PDF');
            const viewUrl = d.file_url ? uploadFileUrl(d.file_url) : '';
            const viewButton = viewUrl ? `<button type="button" class="icon-mini-btn view" title="Lihat ${hint}" onclick="openFilePreview('${escapeAttr(viewUrl)}', '${fileType}', '${safeName}')"><i class="fa-solid ${isForm ? 'fa-image' : 'fa-file-pdf'}"></i></button>` : '';
            const uploadButton = canUpdateSlaUi(prospectData) && isWithinUploadWindow(d.completed_at) ? `<button type="button" class="icon-mini-btn upload" title="Upload ${hint}" onclick="pickCreditDoc('${d.doc_code}', '${accept}')"><i class="fa-solid fa-upload"></i></button>` : '';
            return `<div class="doc-item">
                <div class="doc-check ${done ? 'done' : 'wait'}"><i class="fa-solid ${done ? 'fa-check' : 'fa-clock'}"></i></div>
                <div class="flex-grow-1">
                    <div class="doc-name">${escapeHtml(d.doc_name || '-')}</div>
                    <div class="doc-note">${done ? 'Lengkap' : 'Opsional - upload ' + hint}</div>
                </div>
                ${viewButton}
                ${uploadButton}
            </div>`;
        }).join('');
    }

    // COMPONENT: Normalisasi lampiran stage lama (satu URL) dan Survey baru (JSON array).
    function getStageAttachmentUrls(value) {
        if (Array.isArray(value)) return value.filter(Boolean).map(uploadFileUrl);
        const raw = String(value || '').trim();
        if (!raw) return [];
        try {
            const parsed = JSON.parse(raw);
            if (Array.isArray(parsed)) return parsed.filter(Boolean).map(uploadFileUrl);
        } catch (error) {
            // Format lama berupa satu path biasa.
        }
        return [uploadFileUrl(raw)];
    }

    window.surveyGalleryData = {};

    // COMPONENT: Satu tombol galeri menjaga baris SLA tetap ringkas, detail foto dibuka dalam modal.
    window.openSurveyGallery = function(galleryKey, title) {
        const urls = window.surveyGalleryData[galleryKey] || [];
        const body = document.getElementById('survey-gallery-body');
        body.innerHTML = urls.map((url, index) => `<div class="survey-gallery-item"><img src="${escapeAttr(url)}" alt="${escapeAttr(title || 'Foto Survey')} ${index + 1}"><a href="${escapeAttr(url)}" download class="action-btn btn-follow-up d-block text-center text-decoration-none"><i class="fa-solid fa-download me-2"></i>Unduh Foto ${index + 1}</a></div>`).join('');
        bootstrap.Modal.getOrCreateInstance(document.getElementById('modalSurveyGallery')).show();
    };

    function renderSlaStages(logs) {
        const el = document.getElementById('sla-stages');
        const docsSection = document.getElementById('credit-docs-section');
        const stageLabels = {PEMBERKASAN:'Pemberkasan',SURVEY:'Survey',ANALISA:'Analisa',KOMITE:'Komite'};
        // COMPONENT: Dokumen ditempatkan setelah baris Pemberkasan agar mengikuti konteks tahapnya.
        if (docsSection) docsSection.remove();
        if (!logs || logs.length === 0) {
            el.innerHTML = '<p class="small text-muted">Belum ada tahap SLA</p>';
            if (docsSection) el.appendChild(docsSection);
            return;
        }
        window.surveyGalleryData = {};
        const visibleStages = logs.filter(s => ['PEMBERKASAN','SURVEY','ANALISA','KOMITE'].includes(s.stage));
        el.innerHTML = visibleStages.map(s => {
            const isDone = !!s.stage_ended_at;
            const isActive = !s.stage_ended_at;
            const dur = s.duration_days ? s.duration_days + ' hari' : (isActive ? 'Berjalan...' : '-');
            const attachmentUrls = getStageAttachmentUrls(s.attachment_url);
            const attachment = s.stage === 'ANALISA' ? '' : (attachmentUrls.length ? `<span class="stage-attachment-list">${attachmentUrls.map((url, index) => `<span class="stage-attachment-item"><button type="button" class="icon-mini-btn view" title="Lihat foto ${index + 1}" onclick="openFilePreview('${escapeAttr(url)}', '${s.attachment_type === 'PDF' ? 'PDF' : 'IMAGE'}', '${stageLabels[s.stage] || s.stage} ${index + 1}')"><i class="fa-solid ${s.attachment_type === 'PDF' ? 'fa-file-pdf' : 'fa-image'}"></i></button><a class="icon-mini-btn download" href="${escapeAttr(url)}" download title="Unduh foto ${index + 1}"><i class="fa-solid fa-download"></i></a></span>`).join('')}</span>` : '');
            const canUploadAttachment = canUpdateSlaUi(prospectData) && (s.stage === 'KOMITE' || (s.stage === 'SURVEY' && attachmentUrls.length < 4)) && isWithinUploadWindow(s.attachment_uploaded_at);
            const uploadTitle = s.stage === 'SURVEY' ? 'Upload foto Survey' : 'Upload PDF Komite';
            const uploadAttachment = canUploadAttachment ? `<button type="button" class="icon-mini-btn upload ms-2" title="${uploadTitle}" onclick="pickStageAttachment('${s.stage}')"><i class="fa-solid fa-upload"></i></button>` : '';
            const analyst = s.stage === 'ANALISA' && s.analyst_employee_id ? `<div style="font-size:0.6rem;color:#64748B;"><i class="fa-solid fa-user-check me-1"></i>${escapeHtml(s.analyst_name || s.analyst_employee_id)} (${escapeHtml(s.analyst_employee_id)})</div>` : '';
            return `<div class="sla-stage">
                <div class="sla-dot ${isDone ? 'done' : 'active'}"></div>
                <div class="sla-stage-info"><div class="sla-stage-name">${stageLabels[s.stage]||s.stage}</div><div style="font-size:0.6rem;color:#94A3B8;">${fmtDate(s.stage_started_at)}${isDone?' → '+fmtDate(s.stage_ended_at):''}</div>${analyst}</div>
                <div class="sla-stage-dur">${dur}</div>
                <div class="sla-stage-actions">${attachment}${uploadAttachment}</div>
            </div>${s.stage === 'PEMBERKASAN' ? '<div id="credit-docs-slot"></div>' : ''}`;
        }).join('');
        visibleStages.forEach((stage, index) => {
            if (stage.stage !== 'SURVEY') return;
            const urls = getStageAttachmentUrls(stage.attachment_url);
            if (!urls.length) return;
            const row = el.querySelectorAll('.sla-stage')[index];
            if (!row) return;
            row.querySelector('.stage-attachment-list')?.remove();
            const galleryKey = 'survey-' + String(stage.id || index);
            window.surveyGalleryData[galleryKey] = urls;
            const galleryButton = document.createElement('button');
            galleryButton.type = 'button';
            galleryButton.className = 'icon-mini-btn view';
            galleryButton.title = 'Lihat ' + urls.length + ' foto Survey';
            galleryButton.innerHTML = '<i class="fa-solid fa-images"></i>';
            galleryButton.addEventListener('click', event => {
                event.preventDefault();
                event.stopPropagation();
                window.openSurveyGallery(galleryKey, 'Survey');
            });
            const actionGroup = row.querySelector('.sla-stage-actions');
            const uploadButton = actionGroup?.querySelector('.icon-mini-btn.upload');
            if (uploadButton) actionGroup.insertBefore(galleryButton, uploadButton);
            else actionGroup?.appendChild(galleryButton);
        });
        const docsSlot = document.getElementById('credit-docs-slot');
        if (docsSection) (docsSlot || el).appendChild(docsSection);
    }

    function getNextStage(p) {
        const order = ['PEMBERKASAN', 'SURVEY', 'ANALISA', 'KOMITE'];
        const current = p.credit_pipeline?.current_stage || 'PEMBERKASAN';
        const idx = order.indexOf(current);
        return idx >= 0 ? order[idx + 1] || null : null;
    }

    function renderFollowUps(fups) {
        const el = document.getElementById('followup-list');
        if (!fups || fups.length === 0) { el.innerHTML = '<p class="small text-muted" style="font-style:italic;">Belum ada follow up</p>'; return; }
        el.innerHTML = fups.map(f => `
            <div style="padding:10px 0; border-bottom:1px solid #F4F7F6;">
                <div class="d-flex justify-content-between align-items-start">
                    <span style="font-size:0.75rem; font-weight:700; color:#1E293B;">${f.method || '-'}</span>
                    <span style="font-size:0.6rem; color:#94A3B8;">${fmtDate(f.follow_up_date)}</span>
                </div>
                <p style="font-size:0.78rem; color:#475569; margin:4px 0 0 0; line-height:1.4;">${f.result || '-'}</p>
                ${f.next_plan ? '<small style="font-size:0.65rem; color:#64748B;"><i class="fa-solid fa-arrow-right me-1"></i>'+f.next_plan+'</small>' : ''}
            </div>
        `).join('');
    }

    function renderTimeline(histories) {
        const el = document.getElementById('timeline-list');
        if (!histories || histories.length === 0) { el.innerHTML = '<li class="timeline-item"><div class="timeline-dot"></div><div class="timeline-text text-muted">Belum ada riwayat</div></li>'; return; }
        el.innerHTML = histories.map(h => `
            <li class="timeline-item">
                <div class="timeline-dot"></div>
                <div class="timeline-date">${fmtDateTime(h.created_at)}</div>
                <div class="timeline-text">${h.note || h.action || '-'}</div>
            </li>
        `).join('');
    }

    function renderHeaderQuickActions(p) {
        const actionsWrap = document.getElementById('header-quick-actions');
        const photoButton = document.getElementById('btn-header-photo');
        const mapButton = document.getElementById('btn-header-map');
        const waButton = document.getElementById('btn-header-wa');
        const lat = parseFloat(p.latitude);
        const lng = parseFloat(p.longitude);
        const hasLocation = Number.isFinite(lat) && Number.isFinite(lng);
        const hasPhoto = !!p.foto_url;

        photoButton.style.display = hasPhoto ? 'inline-flex' : 'none';
        if (hasPhoto) {
            const photoUrl = `${BASE_APP}/${p.foto_url}`;
            photoButton.onclick = () => openFilePreview(photoUrl, 'IMAGE', 'Foto Prospek');
        }

        mapButton.style.display = hasLocation ? 'inline-flex' : 'none';
        if (hasLocation) {
            mapButton.onclick = () => openMapPreview(p, lat, lng);
        }

        if (p.phone_number) {
            const wa = String(p.phone_number || '').replace(/\D/g, '').replace(/^0/, '62');
            waButton.href = `https://wa.me/${wa}`;
            waButton.style.display = 'inline-flex';
        } else {
            waButton.removeAttribute('href');
            waButton.style.display = 'none';
        }

        actionsWrap.style.display = hasPhoto || hasLocation || !!p.phone_number ? 'flex' : 'none';
    }

    function openMapPreview(p, lat, lng) {
        const mapUrl = buildOsmEmbedUrl(lat, lng);
        const coordinateText = `${lat.toFixed(6)}, ${lng.toFixed(6)}`;
        const addressText = p.geo_address || [p.address, p.desa, p.kecamatan, p.kab_kota].filter(Boolean).join(', ');
        document.getElementById('map-preview-body').innerHTML = `
            <div class="input-evidence-map">
                <iframe loading="lazy" src="${escapeAttr(mapUrl)}"></iframe>
            </div>
            <div class="input-evidence-meta">
                <div><i class="fa-solid fa-location-crosshairs me-1"></i>${escapeHtml(coordinateText)}</div>
                ${addressText ? `<div>${escapeHtml(addressText)}</div>` : ''}
            </div>
        `;
        bootstrap.Modal.getOrCreateInstance(document.getElementById('modalMapPreview')).show();
    }

    function buildOsmEmbedUrl(lat, lng) {
        const delta = 0.01;
        const bbox = [
            (lng - delta).toFixed(6),
            (lat - delta).toFixed(6),
            (lng + delta).toFixed(6),
            (lat + delta).toFixed(6)
        ].join(',');
        return `https://www.openstreetmap.org/export/embed.html?bbox=${bbox}&layer=mapnik&marker=${lat.toFixed(6)},${lng.toFixed(6)}`;
    }

    function renderActions(p) {
        const el = document.getElementById('action-section');
        let html = '';

        // Superuser: delegasi jika belum
        if (canDelegateProspek && p.delegation_status === 'BELUM_DIDELEGASIKAN') {
            html += `<button class="action-btn btn-delegasi" data-bs-toggle="modal" data-bs-target="#modalDelegasi"><i class="fa-solid fa-user-gear me-2"></i>Delegasikan ke AO</button>`;
            loadAOOptions(p.prospect_type);
        }

        // AO actions
        if (canUpdateSlaUi(p) && p.delegation_status === 'SUDAH_DIDELEGASIKAN' && !['CLOSING','REJECT'].includes(p.status)) {
            const isCredit = p.prospect_type === 'KREDIT' || p.prospect_type === 'DEBITUR_EXISTING';
            const hasCreditPipeline = !!p.credit_pipeline;
            const slaStarted = !!p.credit_pipeline?.sla_started_at;
            const isCreditAtKomite = isCredit && p.status === 'SLA' && p.credit_pipeline?.current_stage === 'KOMITE';
            html += `<button class="action-btn btn-follow-up" data-bs-toggle="modal" data-bs-target="#modalFollowUp"><i class="fa-solid fa-phone-volume me-2"></i>Input Follow Up</button>`;

            if (isCredit && !hasCreditPipeline) {
                html += `<button class="action-btn btn-sla" data-bs-toggle="modal" data-bs-target="#modalCreditInterest"><i class="fa-solid fa-handshake me-2"></i>Debitur Mau Lanjut</button>`;
            }

            if (isCredit && hasCreditPipeline && !slaStarted) {
                html += `<button class="action-btn btn-sla" data-bs-toggle="modal" data-bs-target="#modalCompleteDocs"><i class="fa-solid fa-play me-2"></i>Mulai Proses SLA</button>`;
            }

            if (p.status === 'SLA') {
                const nextStage = getNextStage(p);
                if (nextStage) {
                    const lbl = {SURVEY:'Survey',ANALISA:'Analisa',KOMITE:'Komite'}[nextStage] || nextStage;
                    html += `<button class="action-btn btn-sla" data-bs-toggle="modal" data-bs-target="#modalSlaStage"><i class="fa-solid fa-arrow-right me-2"></i>${lbl}</button>`;
                }
            }

            if ((!isCredit && ['FOLLOW_UP','SLA'].includes(p.status)) || isCreditAtKomite) {
                html += `<button class="action-btn btn-closing" data-bs-toggle="modal" data-bs-target="#modalClosing"><i class="fa-solid fa-check-double me-2"></i>Closing</button>`;
            }

            html += `<button class="action-btn btn-reject" data-bs-toggle="modal" data-bs-target="#modalReject"><i class="fa-solid fa-xmark me-2"></i>Reject</button>`;
        }

        el.innerHTML = html ? `<div class="action-grid">${html}</div>` : '';
    }

    // =========================================
    // API ACTIONS
    // =========================================
    window.doChangeStatus = async function(status) {
        if (!confirm('Ubah status menjadi ' + status + '?')) return;
        try {
            const res = await fetch(BASE_APP+'/api/?action=prospect_change_status', {method:'POST',credentials:'include',headers:{'Content-Type':'application/json'},body:JSON.stringify({prospect_id:prospectId,new_status:status})});
            const b = await res.json();
            if (b.status===200) { showToast('<i class="fa-solid fa-check me-2"></i>'+b.message,'success'); setTimeout(()=>location.reload(),800); }
            else showToast(b.message||'Gagal','danger');
        } catch(e) { showToast('Error koneksi','danger'); }
    };

    const creditInterestForm = document.getElementById('form-credit-interest');
    const creditSlikSelect = document.getElementById('credit-slik-status');
    const creditGoodFields = document.getElementById('credit-good-fields');
    const creditInterestSubmit = creditInterestForm.querySelector('button[type="submit"]');

    // COMPONENT: Sinkronkan field wajib dan CTA berdasarkan keputusan SLIK.
    function syncCreditInterestForm() {
        const isGood = creditSlikSelect.value === '1';
        const isBad = creditSlikSelect.value === '0';
        creditGoodFields.hidden = !isGood;
        creditGoodFields.querySelectorAll('[data-credit-required]').forEach((field) => {
            field.required = isGood;
        });
        if (!isGood) clearCreditIdepAnalysisInline();
        creditInterestSubmit.textContent = isGood ? 'Konfirmasi Masuk Pipeline' : isBad ? 'Tolak Prospek' : 'Pilih Hasil SLIK';
        creditInterestSubmit.classList.toggle('btn-reject', isBad);
        creditInterestSubmit.classList.toggle('btn-sla', !isBad);
    }

    // # COMPONENT: Satu panel analisis lengkap dipakai ulang di modal konfirmasi dan detail.
    const idepAnalysisPanel = document.getElementById('idep-analysis-panel');
    const idepAnalysisModalBody = document.querySelector('#modalIdepAnalysis .modal-body');
    const creditIdepAnalysisSlot = document.getElementById('credit-idep-analysis-slot');

    function mountIdepAnalysisInline() {
        if (!idepAnalysisPanel || !creditIdepAnalysisSlot) return;
        creditIdepAnalysisSlot.hidden = false;
        creditIdepAnalysisSlot.appendChild(idepAnalysisPanel);
    }

    function mountIdepAnalysisModal() {
        if (!idepAnalysisPanel || !idepAnalysisModalBody) return;
        idepAnalysisModalBody.appendChild(idepAnalysisPanel);
        if (creditIdepAnalysisSlot) creditIdepAnalysisSlot.hidden = true;
    }

    function clearCreditIdepAnalysisInline() {
        creditIdepPreviewSequence++;
        mountIdepAnalysisModal();
    }

    const creditIdepFileInput = document.getElementById('credit-idep-file');
    const creditRequestedInput = document.getElementById('credit-requested-amount');
    let creditIdepPreviewFile = null;
    let creditIdepPreviewTimer = null;
    let creditIdepPreviewSequence = 0;

    async function analyzeCreditIdepPreview(file) {
        if (!file) return;
        const sequence = ++creditIdepPreviewSequence;
        mountIdepAnalysisInline();
        renderIdepAnalysis(null, 'File IDEP sedang dianalisis...');
        try {
            const response = await fetch(BASE_APP + '/api/?action=prospect_idep_analyze', {
                method: 'POST',
                credentials: 'include',
                headers: {'Content-Type': 'application/json'},
                body: JSON.stringify({
                    prospect_id: prospectId,
                    requested_loan_amount: parseInt(digitsOnly(creditRequestedInput?.value) || '0'),
                    file_base64: await fileToBase64(file),
                    mime_type: 'text/plain'
                })
            });
            const body = await response.json();
            if (sequence !== creditIdepPreviewSequence) return;
            if (body.status === 200) renderIdepAnalysis(body.data?.analysis || null);
            else renderIdepAnalysis(null, body.message || 'Analisis IDEP gagal diproses.');
        } catch (error) {
            if (sequence === creditIdepPreviewSequence) renderIdepAnalysis(null, 'Analisis IDEP gagal diproses.');
        }
    }

    creditIdepFileInput?.addEventListener('change', function() {
        const file = this.files?.[0];
        if (!file) {
            creditIdepPreviewFile = null;
            clearCreditIdepAnalysisInline();
            return;
        }
        if (!/\.txt$/i.test(file.name)) {
            showToast('Lampiran IDEP harus berformat TXT', 'danger');
            this.value = '';
            creditIdepPreviewFile = null;
            clearCreditIdepAnalysisInline();
            return;
        }
        if (file.size > 2 * 1024 * 1024) {
            showToast('File IDEP maksimal 2 MB', 'danger');
            this.value = '';
            creditIdepPreviewFile = null;
            clearCreditIdepAnalysisInline();
            return;
        }
        creditIdepPreviewFile = file;
        analyzeCreditIdepPreview(file);
    });

    creditRequestedInput?.addEventListener('input', function() {
        this.value = formatPlainNumber(this.value);
        if (!creditIdepPreviewFile) return;
        clearTimeout(creditIdepPreviewTimer);
        creditIdepPreviewTimer = setTimeout(() => analyzeCreditIdepPreview(creditIdepPreviewFile), 450);
    });

    creditSlikSelect.addEventListener('change', syncCreditInterestForm);
    syncCreditInterestForm();

    creditInterestForm.addEventListener('submit', async function(e) {
        e.preventDefault();
        const fd = new FormData(this);
        const slikValue = String(fd.get('slik_checked') || '');
        // COMPONENT: SLIK buruk dikirim sebagai keputusan reject tanpa meminta field pipeline.
        const payload = {
            prospect_id: prospectId,
            slik_checked: slikValue
        };

        if (!slikValue) {
            showToast('Hasil SLIK wajib dipilih', 'danger');
            return;
        }
        if (slikValue === '0' && !window.confirm('Hasil SLIK tidak bagus. Prospek akan langsung berstatus Reject. Lanjutkan?')) {
            return;
        }
        if (slikValue === '1') {
            payload.requested_loan_amount = parseInt(digitsOnly(fd.get('requested_loan_amount')) || '0');
            payload.idep_number = String(fd.get('idep_number') || '').trim();
            payload.planned_realization_date = fd.get('planned_realization_date');
            if (!payload.requested_loan_amount || payload.requested_loan_amount <= 0) {
                showToast('Jumlah pengajuan debitur wajib diisi', 'danger');
                return;
            }
            if (!payload.idep_number) {
                showToast('Nomor IDEP wajib diisi', 'danger');
                return;
            }
            if (!payload.planned_realization_date) {
                showToast('Rencana tanggal realisasi wajib diisi', 'danger');
                return;
            }
            const idepFile = fd.get('idep_file');
            if (idepFile && idepFile.size > 2 * 1024 * 1024) {
                showToast('File IDEP maksimal 2 MB', 'danger');
                return;
            }
            if (idepFile && idepFile.name && !/\.txt$/i.test(idepFile.name)) {
                showToast('Lampiran IDEP harus berformat TXT', 'danger');
                return;
            }
            // COMPONENT: File TXT IDEP bersifat opsional; hanya dikirim jika dipilih user.
            if (idepFile && idepFile.size > 0) {
                payload.idep_file_base64 = await fileToBase64(idepFile);
                payload.idep_file_mime_type = 'text/plain';
            }
        }

        try {
            const res = await fetch(BASE_APP+'/api/?action=prospect_confirm_credit_interest', {method:'POST',credentials:'include',headers:{'Content-Type':'application/json'},body:JSON.stringify(payload)});
            const b = await res.json();
            if (b.status===200) {
                showToast('<i class="fa-solid fa-check me-2"></i>'+b.message,'success');
                const modalEl = document.getElementById('modalCreditInterest');
                bootstrap.Modal.getOrCreateInstance(modalEl).hide();
                this.reset();
                syncCreditInterestForm();
                setTimeout(()=>location.reload(),800);
            }
            else showToast(b.message||'Gagal','danger');
        } catch(e) { showToast('Error koneksi','danger'); }
    });

    // # COMPONENT: Upload IDEP dari detail pipeline tanpa mengulang konfirmasi pipeline.
    const detailIdepButton = document.getElementById('btn-upload-idep');
    const detailIdepViewButton = document.getElementById('btn-view-idep-analysis');
    const detailIdepInput = document.getElementById('detail-idep-file');
    detailIdepButton?.addEventListener('click', () => detailIdepInput?.click());
    detailIdepViewButton?.addEventListener('click', () => {
        mountIdepAnalysisModal();
        renderIdepAnalysis(
            prospectData?.credit_pipeline?.idep_analysis_json || null,
            prospectData?.credit_pipeline?.idep_analysis_error || ''
        );
        bootstrap.Modal.getOrCreateInstance(document.getElementById('modalIdepAnalysis')).show();
    });
    detailIdepInput?.addEventListener('change', async function() {
        const file = this.files?.[0];
        if (!file) return;
        if (!/\.txt$/i.test(file.name)) {
            showToast('File IDEP harus berformat TXT', 'danger');
            this.value = '';
            return;
        }
        if (file.size > 2 * 1024 * 1024) {
            showToast('File IDEP maksimal 2 MB', 'danger');
            this.value = '';
            return;
        }

        const originalIcon = detailIdepButton.innerHTML;
        detailIdepButton.disabled = true;
        detailIdepButton.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i>';
        try {
            const res = await fetch(BASE_APP + '/api/?action=prospect_idep_upload', {
                method: 'POST',
                credentials: 'include',
                headers: {'Content-Type': 'application/json'},
                body: JSON.stringify({
                    prospect_id: prospectId,
                    file_base64: await fileToBase64(file),
                    mime_type: 'text/plain'
                })
            });
            const body = await res.json();
            if (body.status !== 200) {
                showToast(body.message || 'Upload IDEP gagal', 'danger');
                return;
            }

            const savedFile = body.data?.file;
            const analysis = body.data?.analysis;
            if (prospectData?.credit_pipeline) {
                prospectData.credit_pipeline.idep_file_url = savedFile?.path || prospectData.credit_pipeline.idep_file_url;
                prospectData.credit_pipeline.idep_analysis_json = JSON.stringify(analysis || {});
            }
            document.getElementById('d-idep-file').textContent = savedFile?.path ? 'Sudah diupload' : 'Upload berhasil';
            if (detailIdepViewButton) detailIdepViewButton.style.display = 'inline-flex';
            mountIdepAnalysisModal();
            renderIdepAnalysis(analysis);
            showToast('<i class="fa-solid fa-check me-2"></i>File IDEP berhasil dianalisis', 'success');
            bootstrap.Modal.getOrCreateInstance(document.getElementById('modalIdepAnalysis')).show();
        } catch (error) {
            showToast('Upload IDEP gagal', 'danger');
        } finally {
            this.value = '';
            detailIdepButton.disabled = false;
            detailIdepButton.innerHTML = originalIcon;
        }
    });

    ['calc-net-income', 'calc-existing-installment', 'calc-requested-amount', 'calc-tenor', 'calc-annual-rate', 'calc-dsr-limit'].forEach((id) => {
        document.getElementById(id)?.addEventListener('input', function() {
            if (this.hasAttribute('data-number-format')) this.value = formatPlainNumber(this.value);
            if (id === 'calc-requested-amount') this.dataset.userEdited = '1';
            calculateIdepAffordability();
        });
    });

    async function completeCreditDocs() {
        const button = document.getElementById('btn-confirm-complete-docs');
        const originalText = button?.innerHTML;
        if (button) {
            button.disabled = true;
            button.innerHTML = '<i class="fa-solid fa-spinner fa-spin me-2"></i>Memproses...';
        }
        try {
            const res = await fetch(BASE_APP+'/api/?action=prospect_complete_credit_docs', {method:'POST',credentials:'include',headers:{'Content-Type':'application/json'},body:JSON.stringify({prospect_id:prospectId})});
            const b = await res.json();
            if (b.status===200) {
                showToast('<i class="fa-solid fa-check me-2"></i>'+b.message,'success');
                bootstrap.Modal.getOrCreateInstance(document.getElementById('modalCompleteDocs')).hide();
                setTimeout(()=>location.reload(),800);
            }
            else showToast(b.message||'Gagal','danger');
        } catch(e) { showToast('Error koneksi','danger'); }
        finally {
            if (button) {
                button.disabled = false;
                button.innerHTML = originalText;
            }
        }
    }

    document.getElementById('btn-confirm-complete-docs').addEventListener('click', completeCreditDocs);

    document.getElementById('form-followup').addEventListener('submit', async function(e) {
        e.preventDefault();
        const fd = new FormData(this);
        const payload = {prospect_id:prospectId, follow_up_date:fd.get('follow_up_date'), method:fd.get('method'), result:fd.get('result'), next_plan:fd.get('next_plan')};
        try {
            const res = await fetch(BASE_APP+'/api/?action=prospect_follow_up', {method:'POST',credentials:'include',headers:{'Content-Type':'application/json'},body:JSON.stringify(payload)});
            const b = await res.json();
            if(b.status===200){ showToast('<i class="fa-solid fa-check me-2"></i>Follow up disimpan','success'); bootstrap.Modal.getInstance(document.getElementById('modalFollowUp')).hide(); setTimeout(()=>location.reload(),800); }
            else showToast(b.message||'Gagal','danger');
        } catch(e) { showToast('Error','danger'); }
    });

    let closingLookupTimer = null;
    let closingLookupAccount = '';

    function setClosingLookupState(type, message) {
        const hint = document.getElementById('closing-account-hint');
        if (!hint) return;
        hint.textContent = message || '';
        hint.style.color = type === 'error' ? '#D32F2F' : (type === 'success' ? '#388E3C' : '#64748B');
    }

    function clearClosingLookupFields() {
        const form = document.getElementById('form-closing');
        form.querySelector('[name="closing_realization_amount"]').value = '';
        form.querySelector('[name="closing_tenor"]').value = '';
        document.getElementById('closing-realization-preview').style.display = 'none';
        closingLookupAccount = '';
    }

    function renderClosingLookup(data) {
        document.getElementById('closing-lookup-name').textContent = data.nama_nasabah || '-';
        document.getElementById('closing-lookup-product').textContent = [data.kode_produk, data.nama_produk].filter(Boolean).join(' - ') || '-';
        document.getElementById('closing-lookup-amount').textContent = data.realisasi_pokok ? formatRupiah(data.realisasi_pokok) : '-';
        document.getElementById('closing-lookup-date').textContent = data.tanggal_realisasi || '-';
        document.getElementById('closing-lookup-address').textContent = data.alamat || '-';
        document.getElementById('closing-realization-preview').style.display = 'block';

        const form = document.getElementById('form-closing');
        form.querySelector('[name="closing_realization_amount"]').value = data.realisasi_pokok || '';
        form.querySelector('[name="closing_tenor"]').value = data.jml_angsuran || '';
        closingLookupAccount = data.no_rekening || '';
    }

    async function lookupClosingAccount(searchText) {
        const query = String(searchText || '').trim();
        if (!query) {
            clearClosingLookupFields();
            setClosingLookupState('idle', 'Ketik nomor rekening atau nama debitur untuk mengambil realisasi 3 bulan terakhir.');
            return;
        }

        setClosingLookupState('idle', 'Mencari data realisasi...');
        try {
            const params = new URLSearchParams({ prospect_id: prospectId, query });
            const res = await fetch(BASE_APP + '/api/?action=prospect_closing_lookup&' + params.toString(), {credentials:'include'});
            const body = await res.json();
            if (body.status === 200 && body.data) {
                renderClosingLookup(body.data);
                setClosingLookupState('success', 'Data realisasi ditemukan dan nominal otomatis terisi.');
            } else {
                clearClosingLookupFields();
                setClosingLookupState('error', body.message || 'Data realisasi tidak ditemukan.');
            }
        } catch (e) {
            clearClosingLookupFields();
            setClosingLookupState('error', 'Gagal mengecek data realisasi.');
        }
    }

    document.querySelector('#form-closing [name="closing_account_number"]').addEventListener('input', function() {
        if (this.dataset.lookupEnabled !== '1') return;
        clearClosingLookupFields();
        clearTimeout(closingLookupTimer);
        closingLookupTimer = setTimeout(() => lookupClosingAccount(this.value), 450);
    });

    document.getElementById('modalClosing').addEventListener('shown.bs.modal', function() {
        const account = document.querySelector('#form-closing [name="closing_account_number"]');
        if (account?.dataset.lookupEnabled === '1' && account.value) lookupClosingAccount(account.value);
    });

    document.getElementById('form-closing').addEventListener('submit', async function(e) {
        e.preventDefault();
        const fd = new FormData(this);
        const isCredit = ['KREDIT', 'DEBITUR_EXISTING'].includes(prospectData?.prospect_type);
        const account = String(fd.get('closing_account_number') || '').trim();
        if (isCredit && !closingLookupAccount) {
            showToast('Realisasi wajib dicek dan cocok dengan nama prospek', 'danger');
            return;
        }
        const payload = {
            prospect_id: prospectId,
            closing_account_number: isCredit ? closingLookupAccount : account,
            closing_realization_amount: parseInt(fd.get('closing_realization_amount') || '0'),
            closing_tenor: parseInt(fd.get('closing_tenor') || '0'),
            closing_asset_name: fd.get('closing_asset_name'),
            closing_buyer_name: fd.get('closing_buyer_name'),
            closing_asset_purchase_method: fd.get('closing_asset_purchase_method'),
            closing_note: fd.get('closing_note')
        };
        try {
            const res = await fetch(BASE_APP+'/api/?action=prospect_close', {method:'POST',credentials:'include',headers:{'Content-Type':'application/json'},body:JSON.stringify(payload)});
            const b = await res.json();
            if(b.status===200){ showToast('<i class="fa-solid fa-check me-2"></i>Closing berhasil!','success'); bootstrap.Modal.getInstance(document.getElementById('modalClosing')).hide(); setTimeout(()=>location.reload(),800); }
            else showToast(b.message||'Gagal','danger');
        } catch(e) { showToast('Error','danger'); }
    });

    window.openFilePreview = function(url, type, title) {
        document.getElementById('file-preview-title').innerHTML = `<i class="fa-solid fa-eye text-primary me-2"></i>${escapeHtml(title || 'Preview')}`;
        const body = document.getElementById('file-preview-body');
        if (type === 'PDF') {
            const safeUrl = escapeAttr(url);
            const safeTitle = escapeHtml(title || 'Berkas PDF');
            if (isMobilePdfViewer()) {
                body.innerHTML = `<div class="pdf-mobile-preview">
                    <div class="pdf-mobile-state">
                        <i class="fa-solid fa-spinner fa-spin"></i>
                        <div class="title">${safeTitle}</div>
                        <div class="hint">Memuat PDF...</div>
                    </div>
                </div>`;
                renderPdfInModal(url, body.querySelector('.pdf-mobile-preview'), safeUrl, safeTitle);
            } else {
                body.innerHTML = `<iframe class="preview-frame" src="${safeUrl}"></iframe><a href="${safeUrl}" target="_blank" rel="noopener" class="action-btn btn-follow-up d-block text-center text-decoration-none mt-3"><i class="fa-solid fa-up-right-from-square me-2"></i>Buka PDF</a>`;
            }
        } else {
            const safeUrl = escapeAttr(url);
            body.innerHTML = `<img class="preview-img" src="${safeUrl}" alt="${escapeAttr(title || 'Foto')}"><a href="${safeUrl}" download class="action-btn btn-follow-up d-block text-center text-decoration-none mt-3"><i class="fa-solid fa-download me-2"></i>Unduh Foto</a>`;
        }
        bootstrap.Modal.getOrCreateInstance(document.getElementById('modalFilePreview')).show();
    };

    function isMobilePdfViewer() {
        return window.matchMedia('(max-width: 767px)').matches
            || /Android|iPhone|iPad|iPod|Mobile/i.test(navigator.userAgent);
    }

    async function loadPdfJs() {
        if (window.pdfjsLib) {
            return window.pdfjsLib;
        }

        if (!pdfJsLoadPromise) {
            pdfJsLoadPromise = new Promise((resolve, reject) => {
                const script = document.createElement('script');
                script.src = BASE_APP + '/assets/vendor/pdfjs/pdf.min.js';
                script.onload = () => {
                    if (!window.pdfjsLib) {
                        reject(new Error('PDF.js tidak tersedia'));
                        return;
                    }
                    window.pdfjsLib.GlobalWorkerOptions.workerSrc = BASE_APP + '/assets/vendor/pdfjs/pdf.worker.min.js';
                    resolve(window.pdfjsLib);
                };
                script.onerror = () => reject(new Error('Gagal memuat PDF.js'));
                document.head.appendChild(script);
            });
        }

        return pdfJsLoadPromise;
    }

    async function renderPdfInModal(url, container, safeUrl, safeTitle) {
        try {
            const pdfjsLib = await loadPdfJs();
            const pdf = await pdfjsLib.getDocument({ url }).promise;
            container.innerHTML = '';

            for (let pageNumber = 1; pageNumber <= pdf.numPages; pageNumber++) {
                const page = await pdf.getPage(pageNumber);
                const baseViewport = page.getViewport({ scale: 1 });
                const width = Math.max(260, container.clientWidth - 4);
                const scale = width / baseViewport.width;
                const viewport = page.getViewport({ scale });
                const canvas = document.createElement('canvas');
                const context = canvas.getContext('2d');
                canvas.className = 'pdf-page-canvas';
                canvas.width = Math.floor(viewport.width);
                canvas.height = Math.floor(viewport.height);
                container.appendChild(canvas);
                await page.render({ canvasContext: context, viewport }).promise;
            }
        } catch (error) {
            container.innerHTML = `<div class="pdf-mobile-state">
                <i class="fa-solid fa-file-circle-exclamation"></i>
                <div class="title">${safeTitle}</div>
                <div class="hint">PDF belum bisa dirender di modal. Silakan buka file langsung.</div>
                <div class="pdf-action-row">
                    <a href="${safeUrl}" target="_blank" rel="noopener" class="action-btn btn-follow-up d-block text-center text-decoration-none mb-0"><i class="fa-solid fa-up-right-from-square me-2"></i>Buka PDF</a>
                    <a href="${safeUrl}" download class="action-btn btn-wa d-block text-center text-decoration-none mb-0"><i class="fa-solid fa-download me-2"></i>Unduh PDF</a>
                </div>
            </div>`;
        }
    }

    function escapeHtml(value) {
        return String(value ?? '').replace(/[&<>"']/g, char => ({
            '&': '&amp;',
            '<': '&lt;',
            '>': '&gt;',
            '"': '&quot;',
            "'": '&#039;'
        }[char]));
    }

    function escapeAttr(value) {
        return escapeHtml(value).replace(/`/g, '&#096;');
    }

    async function fileToBase64(file) {
        return new Promise((resolve, reject) => {
            const reader = new FileReader();
            reader.onload = () => resolve(reader.result);
            reader.onerror = reject;
            reader.readAsDataURL(file);
        });
    }

    async function compressImageFile(file, maxWidth = 1600, quality = 0.78) {
        const dataUrl = await fileToBase64(file);
        return new Promise((resolve, reject) => {
            const img = new Image();
            img.onload = () => {
                const scale = Math.min(1, maxWidth / img.width);
                const canvas = document.createElement('canvas');
                canvas.width = Math.round(img.width * scale);
                canvas.height = Math.round(img.height * scale);
                const ctx = canvas.getContext('2d');
                ctx.drawImage(img, 0, 0, canvas.width, canvas.height);
                resolve({base64: canvas.toDataURL('image/jpeg', quality), mime: 'image/jpeg'});
            };
            img.onerror = reject;
            img.src = dataUrl;
        });
    }

    function dataUrlByteLength(dataUrl) {
        const base64 = String(dataUrl).split(',')[1] || '';
        const padding = (base64.match(/=*$/) || [''])[0].length;
        return Math.max(0, Math.floor(base64.length * 0.75) - padding);
    }

    // COMPONENT: Kompresi foto Survey bertahap agar setiap file tidak melewati 1 MB.
    async function compressImageForUpload(file, maxBytes = 1024 * 1024) {
        const attempts = [
            {width:1600, quality:0.78},
            {width:1400, quality:0.68},
            {width:1200, quality:0.58},
            {width:960, quality:0.48},
            {width:720, quality:0.38}
        ];
        for (const attempt of attempts) {
            const encoded = await compressImageFile(file, attempt.width, attempt.quality);
            if (dataUrlByteLength(encoded.base64) <= maxBytes) return encoded;
        }
        throw new Error('Foto masih lebih besar dari 1 MB setelah dikompres');
    }

    function dataUrlToBinary(dataUrl) {
        const base64 = String(dataUrl).split(',')[1] || '';
        return atob(base64);
    }

    // COMPONENT: Bentuk PDF baru dari thumbnail halaman untuk mengecilkan file besar di browser.
    function buildImagePdf(pages) {
        const objects = [];
        const pageIds = [];
        let nextId = 3;
        pages.forEach(page => {
            page.imageId = nextId++;
            page.contentId = nextId++;
            page.pageId = nextId++;
            pageIds.push(page.pageId);
        });
        objects[1] = '<< /Type /Catalog /Pages 2 0 R >>';
        objects[2] = `<< /Type /Pages /Kids [${pageIds.map(id => `${id} 0 R`).join(' ')}] /Count ${pageIds.length} >>`;
        pages.forEach(page => {
            const imageData = page.jpeg;
            const content = `q ${page.width} 0 0 ${page.height} 0 0 cm /Im1 Do Q`;
            objects[page.imageId] = `<< /Type /XObject /Subtype /Image /Width ${page.width} /Height ${page.height} /ColorSpace /DeviceRGB /BitsPerComponent 8 /Filter /DCTDecode /Length ${imageData.length} >>\nstream\n${imageData}\nendstream`;
            objects[page.contentId] = `<< /Length ${content.length} >>\nstream\n${content}\nendstream`;
            objects[page.pageId] = `<< /Type /Page /Parent 2 0 R /MediaBox [0 0 ${page.width} ${page.height}] /Resources << /XObject << /Im1 ${page.imageId} 0 R >> >> /Contents ${page.contentId} 0 R >>`;
        });

        let output = '%PDF-1.4\n%\xFF\xFF\xFF\xFF\n';
        const offsets = [0];
        for (let id = 1; id < nextId; id++) {
            offsets[id] = output.length;
            output += `${id} 0 obj\n${objects[id]}\nendobj\n`;
        }
        const xrefOffset = output.length;
        output += `xref\n0 ${nextId}\n0000000000 65535 f \n`;
        for (let id = 1; id < nextId; id++) {
            output += `${String(offsets[id]).padStart(10, '0')} 00000 n \n`;
        }
        output += `trailer\n<< /Size ${nextId} /Root 1 0 R >>\nstartxref\n${xrefOffset}\n%%EOF`;
        return new Blob([Uint8Array.from(output, char => char.charCodeAt(0) & 0xff)], {type:'application/pdf'});
    }

    async function compressPdfForUpload(file, maxBytes = 1024 * 1024) {
        if (file.size <= maxBytes) return file;
        const pdfjsLib = await loadPdfJs();
        const source = await file.arrayBuffer();
        const pdf = await pdfjsLib.getDocument({data: source}).promise;
        const attempts = [
            {scale:0.9, quality:0.72},
            {scale:0.75, quality:0.58},
            {scale:0.6, quality:0.45},
            {scale:0.5, quality:0.32}
        ];
        for (const attempt of attempts) {
            const pages = [];
            for (let pageNumber = 1; pageNumber <= pdf.numPages; pageNumber++) {
                const page = await pdf.getPage(pageNumber);
                const viewport = page.getViewport({scale: attempt.scale});
                const canvas = document.createElement('canvas');
                canvas.width = Math.max(1, Math.round(viewport.width));
                canvas.height = Math.max(1, Math.round(viewport.height));
                await page.render({canvasContext: canvas.getContext('2d'), viewport}).promise;
                pages.push({
                    width: canvas.width,
                    height: canvas.height,
                    jpeg: dataUrlToBinary(canvas.toDataURL('image/jpeg', attempt.quality))
                });
            }
            const compressed = buildImagePdf(pages);
            if (compressed.size <= maxBytes) {
                return new File([compressed], file.name, {type:'application/pdf'});
            }
        }
        throw new Error('PDF masih lebih besar dari 1 MB setelah dikompres');
    }

    window.pickCreditDoc = function(docCode, accept) {
        const input = document.getElementById('credit-doc-file');
        input.value = '';
        input.multiple = false;
        input.accept = accept;
        input.onchange = async () => {
            const file = input.files?.[0];
            if (!file) return;
            try {
                let encoded;
                if (file.type.startsWith('image/')) {
                    encoded = await compressImageFile(file);
                } else {
                    encoded = {base64: await fileToBase64(file), mime: file.type || 'application/pdf'};
                }
                const res = await fetch(BASE_APP+'/api/?action=prospect_credit_upload', {
                    method:'POST',
                    credentials:'include',
                    headers:{'Content-Type':'application/json'},
                    body:JSON.stringify({prospect_id:prospectId, target:'DOCUMENT', doc_code:docCode, file_base64:encoded.base64, mime_type:encoded.mime})
                });
                const b = await res.json();
                if(b.status===200){ showToast('<i class="fa-solid fa-check me-2"></i>Berkas diupload','success'); setTimeout(()=>location.reload(),700); }
                else showToast(b.message||'Upload gagal','danger');
            } catch(e) { showToast('Upload gagal','danger'); }
        };
        input.click();
    };

    window.pickStageAttachment = function(stage) {
        const input = document.getElementById('credit-doc-file');
        input.value = '';
        input.multiple = stage === 'SURVEY';
        input.accept = stage === 'SURVEY' ? 'image/*' : 'application/pdf';
        input.onchange = async () => {
            const files = [...(input.files || [])];
            if (!files.length) return;
            if (stage === 'SURVEY' && files.length > 4) {
                showToast('Foto Survey maksimal 4 file per upload', 'danger');
                return;
            }
            try {
                const encodedFiles = [];
                for (const file of files) {
                    if (stage === 'SURVEY') {
                        if (!file.type.startsWith('image/')) {
                            showToast('Lampiran Survey harus berupa gambar', 'danger');
                            return;
                        }
                        encodedFiles.push(await compressImageForUpload(file));
                    } else {
                        if (file.type !== 'application/pdf') {
                            showToast('File Komite harus berformat PDF', 'danger');
                            return;
                        }
                        const preparedPdf = await compressPdfForUpload(file);
                        encodedFiles.push({base64: await fileToBase64(preparedPdf), mime:'application/pdf'});
                    }
                }
                const res = await fetch(BASE_APP+'/api/?action=prospect_credit_upload', {
                    method:'POST',
                    credentials:'include',
                    headers:{'Content-Type':'application/json'},
                    body:JSON.stringify({
                        prospect_id:prospectId,
                        target:'STAGE_ATTACHMENT',
                        stage,
                        file_base64: stage === 'SURVEY' ? encodedFiles.map(file => file.base64) : encodedFiles[0].base64,
                        mime_type: stage === 'SURVEY' ? encodedFiles.map(file => file.mime) : encodedFiles[0].mime
                    })
                });
                const b = await res.json();
                if(b.status===200){ showToast('<i class="fa-solid fa-check me-2"></i>Lampiran diupload','success'); setTimeout(()=>location.reload(),700); }
                else showToast(b.message||'Upload gagal','danger');
            } catch(e) { showToast(e.message || 'Upload gagal','danger'); }
        };
        input.click();
    };

    document.getElementById('form-reject').addEventListener('submit', async function(e) {
        e.preventDefault();
        const fd = new FormData(this);
        const payload = {prospect_id:prospectId, reject_reason:fd.get('reject_reason'), reject_note:fd.get('reject_note')};
        try {
            const res = await fetch(BASE_APP+'/api/?action=prospect_reject', {method:'POST',credentials:'include',headers:{'Content-Type':'application/json'},body:JSON.stringify(payload)});
            const b = await res.json();
            if(b.status===200){ showToast('<i class="fa-solid fa-check me-2"></i>Reject berhasil','success'); bootstrap.Modal.getInstance(document.getElementById('modalReject')).hide(); setTimeout(()=>location.reload(),800); }
            else showToast(b.message||'Gagal','danger');
        } catch(e) { showToast('Error','danger'); }
    });

    document.getElementById('form-delegasi').addEventListener('submit', async function(e) {
        e.preventDefault();
        const fd = new FormData(this);
        const payload = {prospect_id:prospectId, assigned_to:fd.get('assigned_to')};
        try {
            const res = await fetch(BASE_APP+'/api/?action=prospect_delegate', {method:'POST',credentials:'include',headers:{'Content-Type':'application/json'},body:JSON.stringify(payload)});
            const b = await res.json();
            if(b.status===200){ showToast('<i class="fa-solid fa-check me-2"></i>Didelegasikan!','success'); bootstrap.Modal.getInstance(document.getElementById('modalDelegasi')).hide(); setTimeout(()=>location.reload(),800); }
            else showToast(b.message||'Gagal','danger');
        } catch(e) { showToast('Error','danger'); }
    });

    let surveyCameraFiles = [];
    let surveyCameraStream = null;
    let surveyPreviewUrls = [];

    function getSelectedStageFiles() {
        const input = document.getElementById('sla-stage-file');
        return [...(input.files || []), ...surveyCameraFiles];
    }

    // COMPONENT: Preview thumbnail membantu user memastikan foto yang dipilih sebelum submit.
    function renderStageFileList() {
        const list = document.getElementById('sla-stage-file-list');
        surveyPreviewUrls.forEach(url => URL.revokeObjectURL(url));
        surveyPreviewUrls = [];
        const files = getSelectedStageFiles();
        if (!files.length) {
            list.innerHTML = '';
            return;
        }
        const isSurvey = getNextStage(prospectData || {}) === 'SURVEY';
        if (!isSurvey) {
            list.innerHTML = `<div class="small text-muted mt-2">${files.map(file => escapeHtml(file.name)).join(', ')}</div>`;
            return;
        }
        list.innerHTML = `<div class="stage-photo-grid">${files.map((file, index) => {
            const previewUrl = URL.createObjectURL(file);
            surveyPreviewUrls.push(previewUrl);
            return `<div class="stage-photo-preview"><img src="${escapeAttr(previewUrl)}" alt="Foto Survey ${index + 1}"><span class="stage-photo-index">${index + 1}</span></div>`;
        }).join('')}</div><div class="small text-muted mt-2">${files.length} foto dipilih</div>`;
    }

    function stopSurveyCamera() {
        if (surveyCameraStream) {
            surveyCameraStream.getTracks().forEach(track => track.stop());
            surveyCameraStream = null;
        }
        const video = document.getElementById('sla-camera-video');
        if (video) video.srcObject = null;
    }

    async function openSurveyCamera() {
        const cameraInput = document.getElementById('sla-stage-camera');
        if (!navigator.mediaDevices?.getUserMedia) {
            cameraInput.click();
            return;
        }
        try {
            surveyCameraStream = await navigator.mediaDevices.getUserMedia({
                video: {facingMode: {ideal: 'environment'}},
                audio: false
            });
            const video = document.getElementById('sla-camera-video');
            video.srcObject = surveyCameraStream;
            bootstrap.Modal.getOrCreateInstance(document.getElementById('modalSlaCamera')).show();
        } catch (error) {
            // Fallback memakai input capture jika kamera browser ditolak/tidak tersedia.
            cameraInput.click();
        }
    }

    document.getElementById('sla-stage-file').addEventListener('change', renderStageFileList);
    document.getElementById('sla-stage-file-button').addEventListener('click', function() {
        document.getElementById('sla-stage-file').click();
    });
    document.getElementById('sla-stage-camera').addEventListener('change', function() {
        if (this.files?.[0]) surveyCameraFiles.push(this.files[0]);
        this.value = '';
        renderStageFileList();
    });
    document.getElementById('sla-stage-camera-button').addEventListener('click', openSurveyCamera);
    document.getElementById('modalSlaCamera').addEventListener('hidden.bs.modal', stopSurveyCamera);
    document.getElementById('sla-camera-capture').addEventListener('click', function() {
        const video = document.getElementById('sla-camera-video');
        const canvas = document.getElementById('sla-camera-canvas');
        if (!video.videoWidth || !video.videoHeight) {
            showToast('Kamera belum siap, coba lagi', 'danger');
            return;
        }
        canvas.width = video.videoWidth;
        canvas.height = video.videoHeight;
        canvas.getContext('2d').drawImage(video, 0, 0, canvas.width, canvas.height);
        canvas.toBlob(blob => {
            if (!blob) {
                showToast('Foto gagal diambil', 'danger');
                return;
            }
            surveyCameraFiles.push(new File([blob], `foto-survey-${Date.now()}.jpg`, {type:'image/jpeg'}));
            renderStageFileList();
            bootstrap.Modal.getInstance(document.getElementById('modalSlaCamera')).hide();
        }, 'image/jpeg', 0.9);
    });

    function syncStageFileInput() {
        const stage = getNextStage(prospectData || {});
        const stageLabelMap = {SURVEY:'Survey', ANALISA:'Analisa', KOMITE:'Komite'};
        document.getElementById('sla-next-stage-value').value = stage || '';
        document.getElementById('sla-next-stage-label').textContent = stage ? stageLabelMap[stage] || stage : 'Tahap selesai';
        const wrap = document.getElementById('sla-stage-file-wrap');
        const analystWrap = document.getElementById('sla-stage-analyst-wrap');
        const analystSelect = document.getElementById('sla-stage-analyst');
        const input = document.getElementById('sla-stage-file');
        const cameraTools = document.getElementById('sla-stage-survey-tools');
        const cameraInput = document.getElementById('sla-stage-camera');
        const fileButton = document.getElementById('sla-stage-file-button');
        const label = document.getElementById('sla-stage-file-label');
        const hint = document.getElementById('sla-stage-file-hint');
        input.value = '';
        input.multiple = false;
        input.removeAttribute('capture');
        cameraInput.value = '';
        surveyCameraFiles = [];
        cameraTools.classList.remove('is-visible');
        fileButton.innerHTML = '<i class="fa-solid fa-file-arrow-up me-2"></i>Pilih File';
        renderStageFileList();
        analystSelect.value = '';
        analystWrap.style.display = stage === 'ANALISA' ? 'block' : 'none';
        if (stage === 'ANALISA') loadAnalisCabangOptions();
        if (stage === 'SURVEY') {
            wrap.style.display = 'block';
            input.accept = 'image/*';
            input.multiple = true;
            cameraInput.setAttribute('capture', 'environment');
            cameraTools.classList.add('is-visible');
            fileButton.innerHTML = '<i class="fa-solid fa-images me-2"></i>Pilih Foto';
            label.textContent = 'Foto Survey';
            hint.textContent = 'Opsional, maksimal 4 foto. Bisa pilih file atau jepret langsung dari kamera.';
        } else if (stage === 'ANALISA') {
            // COMPONENT: Analisa hanya memilih analis; tahap ini tidak menerima upload/foto.
            wrap.style.display = 'none';
            input.accept = '';
            label.textContent = 'Lampiran';
            hint.textContent = '';
        } else if (stage === 'KOMITE') {
            wrap.style.display = 'block';
            input.accept = 'application/pdf';
            fileButton.innerHTML = '<i class="fa-solid fa-file-pdf me-2"></i>Pilih PDF';
            label.textContent = 'File Komite';
            hint.textContent = 'Opsional. PDF maksimal 1 MB. File lebih besar akan dicoba dikompres otomatis.';
        } else {
            wrap.style.display = 'none';
            input.accept = '';
            label.textContent = 'Lampiran';
            hint.textContent = '';
        }
    }
    document.getElementById('modalSlaStage').addEventListener('show.bs.modal', syncStageFileInput);

    async function loadAnalisCabangOptions() {
        const sel = document.getElementById('sla-stage-analyst');
        const kode = prospectData?.kode_kantor || '';
        sel.innerHTML = '<option value="">Memuat analis...</option>';
        if (!kode) {
            sel.innerHTML = '<option value="">Kode cabang tidak ditemukan</option>';
            return;
        }

        try {
            const params = new URLSearchParams({ kode_kantor: kode });
            const res = await fetch(BASE_APP + '/api/?action=master_analis_kredit&' + params.toString(), {credentials:'include'});
            const body = await res.json();
            const rows = body.status === 200 ? (body.data || []) : [];
            if (!rows.length) {
                sel.innerHTML = '<option value="">Analis cabang tidak ditemukan</option>';
                return;
            }
            sel.innerHTML = '<option value="">-- Pilih analis cabang --</option>' + rows.map(a =>
                `<option value="${escapeAttr(a.employee_id)}">${escapeHtml(a.full_name || a.employee_id)} (${escapeHtml(a.employee_id)})</option>`
            ).join('');
        } catch(e) {
            sel.innerHTML = '<option value="">Gagal memuat analis</option>';
        }
    }

    document.getElementById('form-sla-stage').addEventListener('submit', async function(e) {
        e.preventDefault();
        const fd = new FormData(this);
        const payload = {prospect_id:prospectId, stage:fd.get('stage'), note:fd.get('note')};
        const input = document.getElementById('sla-stage-file');
        const selectedFiles = payload.stage === 'SURVEY'
            ? [...(input.files || []), ...surveyCameraFiles]
            : [input.files?.[0]].filter(Boolean);
        if (!payload.stage) {
            showToast('Tahap SLA sudah selesai. Silakan closing jika sudah cair.', 'danger');
            return;
        }
        if (payload.stage === 'SURVEY' && selectedFiles.length > 4) {
            showToast('Foto Survey maksimal 4 file', 'danger');
            return;
        }
        if (payload.stage === 'ANALISA') {
            payload.analyst_employee_id = fd.get('analyst_employee_id');
            if (!payload.analyst_employee_id) {
                showToast('Pilih analis cabang dulu', 'danger');
                return;
            }
        }
        try {
            if (payload.stage === 'SURVEY') {
                const encodedFiles = [];
                for (const file of selectedFiles) {
                    if (!file.type.startsWith('image/')) {
                        showToast('Semua foto Survey harus berupa gambar', 'danger');
                        return;
                    }
                    encodedFiles.push(await compressImageForUpload(file));
                }
                // Survey bersifat opsional, jadi array kosong tetap dikirim secara eksplisit.
                payload.attachment_base64 = encodedFiles.map(file => file.base64);
                payload.attachment_mime = encodedFiles.map(file => file.mime);
            } else if (selectedFiles[0]) {
                let attachment = selectedFiles[0];
                if (payload.stage === 'KOMITE') {
                    if (attachment.type !== 'application/pdf') {
                        showToast('File Komite harus berformat PDF', 'danger');
                        return;
                    }
                    attachment = await compressPdfForUpload(attachment);
                }
                if (attachment.type.startsWith('image/')) {
                    const encoded = await compressImageFile(attachment);
                    payload.attachment_base64 = encoded.base64;
                    payload.attachment_mime = encoded.mime;
                } else {
                    payload.attachment_base64 = await fileToBase64(attachment);
                    payload.attachment_mime = attachment.type || 'application/pdf';
                }
            }
        } catch(e) {
            showToast(e.message || 'Lampiran gagal diproses','danger');
            return;
        }
        try {
            const res = await fetch(BASE_APP+'/api/?action=prospect_sla_log', {method:'POST',credentials:'include',headers:{'Content-Type':'application/json'},body:JSON.stringify(payload)});
            const b = await res.json();
            if(b.status===200){ showToast('<i class="fa-solid fa-check me-2"></i>Tahap SLA ditambahkan','success'); bootstrap.Modal.getInstance(document.getElementById('modalSlaStage')).hide(); this.reset(); surveyCameraFiles = []; renderStageFileList(); setTimeout(()=>location.reload(),800); }
            else showToast(b.message||'Gagal','danger');
        } catch(e) { showToast('Error','danger'); }
    });

    async function loadAOOptions(type) {
        const sel = document.getElementById('sel-ao-delegasi');
        const hint = document.getElementById('delegasi-hint');
        let group = '';
        let tipe = '';
        if (type==='KREDIT'||type==='DEBITUR_EXISTING') { group='AO Kredit'; tipe='kredit'; hint.textContent='Hanya AO Kredit'; }
        else if (type==='TABUNGAN'||type==='DEPOSITO') { group='AO Dana'; tipe='dana'; hint.textContent='Hanya AO Dana'; }
        else if (type==='PEMBELI_ASET') { group='AO Remedial'; tipe='remedial'; hint.textContent='Hanya AO Remedial'; }
        try {
            const params = new URLSearchParams();
            if (group) params.set('group_jabatan', group);
            if (tipe) params.set('tipe', tipe);
            if (prospectData && prospectData.kode_kantor) params.set('kode_kantor', prospectData.kode_kantor);
            const res = await fetch(BASE_APP+'/api/?action=master_pegawai_ao&'+params.toString(), {credentials:'include'});
            const b = await res.json();
            if (b.status===200 && Array.isArray(b.data)) {
                sel.innerHTML = '<option value="">-- Pilih AO --</option>';
                b.data.forEach(ao => { sel.innerHTML += `<option value="${ao.employee_id}">${ao.full_name} (${ao.job_position||ao.group_jabatan})</option>`; });
                if (b.data.length === 0) {
                    sel.innerHTML = '<option value="">-- AO tidak ditemukan di cabang ini --</option>';
                }
            }
        } catch(e) {}
    }

    // Helpers
    function fmtDate(d) { if(!d) return '-'; return new Date(d).toLocaleDateString('id-ID',{day:'numeric',month:'short',year:'numeric'}); }
    function fmtDateTime(d) { if(!d) return '-'; return new Date(d).toLocaleDateString('id-ID',{day:'numeric',month:'short',year:'numeric',hour:'2-digit',minute:'2-digit'}); }
    function fmtRupiah(n) { return new Intl.NumberFormat('id-ID', {style:'currency', currency:'IDR', maximumFractionDigits:0}).format(Number(n || 0)); }

    // Init
    loadDetail();
})();
</script>
