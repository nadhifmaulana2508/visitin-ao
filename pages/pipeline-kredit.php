<?php
$is_ao = in_array($user_role, ['ao_kredit', 'ao_dana', 'ao_remedial', 'developer'], true);
$is_superuser = in_array($user_role, ['superuser', 'developer'], true);
$is_pusat = ($user_kode_kantor === '000');
$user_access_korwil = $_SESSION['user_data']['access_korwil'] ?? '';
$url_type = $_GET['type'] ?? '';
$url_stage = strtoupper(trim((string)($_GET['stage'] ?? '')));
$default_closing_date = date('Y-m-t', strtotime('last month'));
$default_harian_date = date('Y-m-d');
?>

<style>
    .pipeline-page { padding-bottom: 88px; }
    .pipeline-page .header-compact { margin-bottom: 0; }
    .pipeline-page .ui-metric-grid { position: relative; z-index: 2; margin: -30px 16px 16px; }
    .pipeline-page .ui-metric-grid { grid-template-columns: repeat(4, minmax(0, 1fr)); }
    .pipeline-filter-section { margin: 0 16px 16px; padding: 14px; border-radius: 14px; background: #FFFFFF; box-shadow: 0 2px 10px rgba(15, 23, 42, 0.04); }
    .pipeline-filter-row { grid-template-columns: repeat(2, minmax(0, 1fr)); }
    /* # COMPONENT: Toolbar pipeline berisi AO, tahap, dan pencarian debitur. */
    .pipeline-filter-toolbar { align-items: flex-end; }
    .pipeline-filter-toolbar .ui-filter-field { flex: 1 1 180px; }
    .pipeline-filter-toolbar .ui-filter-search { flex: 1 1 320px; }
    .pipeline-summary { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 10px; margin: 0 16px 16px; }
    .pipeline-summary-card { min-width: 0; padding: 12px 14px; border: 1px solid #E2E8F0; border-radius: 10px; background: #FFFFFF; box-shadow: 0 2px 10px rgba(15, 23, 42, 0.04); }
    .pipeline-summary-label { color: #64748B; font-size: 0.62rem; font-weight: 800; text-transform: uppercase; }
    .pipeline-summary-value { margin-top: 2px; color: #1E293B; font-size: 1rem; font-weight: 900; overflow-wrap: anywhere; }
    .pipeline-summary-meta { margin-top: 2px; color: #94A3B8; font-size: 0.64rem; font-weight: 700; }
    .pipeline-list-area { padding: 0 16px; }
    .pipeline-card { display: block; min-width: 0; padding: 14px; border-left: 4px solid #1976D2; border-radius: 14px; background: #FFFFFF; box-shadow: 0 2px 10px rgba(15, 23, 42, 0.04); color: inherit; text-decoration: none; }
    .pipeline-card.t-debitur_existing { border-left-color: #00838F; }
    .pipeline-card-head { display: flex; align-items: flex-start; justify-content: space-between; gap: 8px; margin-bottom: 10px; }
    .pipeline-card-main { min-width: 0; }
    .pipeline-card-ao { display: flex; align-items: flex-start; gap: 4px; margin: 3px 0 7px; color: #64748B; font-size: 0.62rem; font-weight: 700; }
    .pipeline-card-ao > i { margin-top: 2px; color: #94A3B8; font-size: 0.58rem; }
    .pipeline-ao-lines { display: grid; gap: 2px; min-width: 0; }
    .pipeline-ao-line { display: flex; align-items: baseline; gap: 4px; min-width: 0; }
    .pipeline-ao-label { flex: 0 0 auto; color: #94A3B8; font-size: 0.56rem; font-weight: 800; text-transform: uppercase; }
    .pipeline-ao-value { min-width: 0; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
    .pipeline-page .p-meta { display: flex; align-items: center; gap: 4px; margin-bottom: 3px; color: #64748B; font-size: 0.7rem; }
    .pipeline-page .p-meta i { width: 14px; flex: 0 0 14px; color: #A0AEC0; font-size: 0.65rem; text-align: center; }
    .pipeline-page .meta-text { min-width: 0; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
    .pipeline-card .p-name { overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
    .pipeline-card-grid { display: grid; grid-template-columns: minmax(0, 1fr) minmax(105px, 0.7fr); gap: 12px; align-items: start; }
    .pipeline-card-side { min-width: 0; text-align: right; }
    .pipeline-card-side .p-meta { justify-content: flex-end; }
    .pipeline-card-side .meta-text { white-space: normal; }
    .pipeline-amount { color: #0F766E; font-size: 0.8rem; font-weight: 900; }
    .pipeline-deadline { display:flex; align-items:center; justify-content:flex-start; gap:8px; margin:-2px 0 10px; padding:7px 9px; border:1px solid #DBEAFE; border-radius:9px; background:#EFF6FF; color:#1D4ED8; font-size:0.66rem; font-weight:800; }
    .pipeline-deadline i { flex:0 0 auto; font-size:0.72rem; }
    .pipeline-deadline span { min-width:0; overflow:hidden; text-overflow:ellipsis; white-space:nowrap; }
    .pipeline-deadline.today { border-color:#FDE68A; background:#FFFBEB; color:#B45309; }
    .pipeline-deadline.overdue { border-color:#FECACA; background:#FEF2F2; color:#B91C1C; }
    .pipeline-deadline.completed { border-color:#BBF7D0; background:#F0FDF4; color:#15803D; }
    .pipeline-deadline.missing { border-color:#E2E8F0; background:#F8FAFC; color:#64748B; }
    .pipeline-table-deadline { color:#1D4ED8; font-weight:800; }
    .pipeline-table-deadline.completed { color:#15803D; }
    .pipeline-table-deadline.missing { color:#64748B; }
    .pipeline-page .status-pending { background:#FEF3C7; color:#92400E; }
    .pipeline-age { display: inline-flex; align-items: center; gap: 5px; margin-top: 6px; padding: 4px 7px; border-radius: 6px; background: #F1F5F9; color: #475569; font-size: 0.62rem; font-weight: 800; }
    .pipeline-percent { display: inline-flex; align-items: flex-end; flex-direction: column; gap: 1px; min-width: 42px; margin-top: 6px; padding: 4px 7px; border-radius: 9px; background: #ECFDF5; color: #047857; font-size: 0.62rem; font-weight: 900; line-height: 1.2; text-align: right; white-space: normal; }
    .pipeline-percent strong { font-size: 0.68rem; font-weight: 900; }
    .pipeline-percent small { font-size: 0.6rem; font-weight: 800; }
    .pipeline-page .pagination-bar { padding-bottom: 20px; }
    .pipeline-page .ui-responsive-data-table table { min-width: 1080px; }
    .pipeline-table-stage { color: var(--color-primary); font-weight: 800; }
    .pipeline-table-stage + small { display: block; margin-top: 3px; color: #64748B; font-size: 0.62rem; font-weight: 700; }
    .pipeline-table-ao { display: flex; align-items: flex-start; gap: 4px; min-width: 128px; color: #475569; font-size: 0.62rem; font-weight: 700; }
    .pipeline-table-ao > i { margin-top: 2px; color: #94A3B8; font-size: 0.58rem; }
    .pipeline-table-ao .pipeline-ao-lines { gap: 3px; }
    .pipeline-card-stage-age { display: block; margin: 0 0 4px 18px; color: #64748B; font-size: 0.62rem; font-weight: 700; }
    @media (min-width: 768px) {
        .pipeline-page .ui-metric-grid { margin-right: 24px; margin-left: 24px; }
        .pipeline-filter-section, .pipeline-summary, .pipeline-list-area { margin-right: 24px; margin-left: 24px; }
        .pipeline-filter-section { padding: 18px; }
        .pipeline-list-area { padding-right: 0; padding-left: 0; }
    }
    @media (min-width: 1024px) {
        .pipeline-page .ui-metric-grid { margin-right: 32px; margin-left: 32px; }
        .pipeline-filter-section, .pipeline-summary { margin-right: 32px; margin-left: 32px; }
    }
    @media (max-width: 767px) {
        .pipeline-page .ui-metric-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); }
    }
    @media (max-width: 575px) {
        .pipeline-page .ui-metric-grid { gap: 8px; }
        .pipeline-filter-section { margin-right: 12px; margin-left: 12px; padding: 12px; }
        .pipeline-summary, .pipeline-list-area { margin-right: 12px; margin-left: 12px; }
        .pipeline-card-grid { grid-template-columns: minmax(0, 1fr) minmax(88px, 0.68fr); gap: 8px; }
    }
</style>

<main class="ui-filter-component pipeline-page" data-filter-component>
    <header class="header-compact">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h5 class="fw-bold mb-0">Pipeline Kredit</h5>
                <p class="small text-white-50 mb-0" style="font-size:0.7rem;">Antrean proses kredit sampai closing</p>
            </div>
            <div class="ui-filter-header-actions">
                <button type="button" class="ui-filter-trigger" data-filter-trigger aria-controls="pipeline-filter" aria-expanded="false"><i class="fa-solid fa-sliders" aria-hidden="true"></i><span>Filter</span></button>
                <span class="badge bg-white text-primary rounded-pill px-3 py-2 fw-bold" id="pipeline-total" style="font-size:0.8rem;">0</span>
            </div>
        </div>
    </header>

    <section class="ui-metric-grid" aria-label="Ringkasan tahap pipeline">
        <div class="ui-metric-card ui-metric-card--open"><span class="ui-metric-icon"><i class="fa-solid fa-folder-open"></i></span><span class="ui-metric-content"><span class="ui-metric-value" id="metric-pemberkasan">0</span><span class="ui-metric-label">Pemberkasan</span></span></div>
        <div class="ui-metric-card ui-metric-card--follow-up"><span class="ui-metric-icon"><i class="fa-solid fa-magnifying-glass-location"></i></span><span class="ui-metric-content"><span class="ui-metric-value" id="metric-survey">0</span><span class="ui-metric-label">Survey</span></span></div>
        <div class="ui-metric-card ui-metric-card--pipeline"><span class="ui-metric-icon"><i class="fa-solid fa-chart-line"></i></span><span class="ui-metric-content"><span class="ui-metric-value" id="metric-analisa">0</span><span class="ui-metric-label">Analisa</span></span></div>
        <div class="ui-metric-card ui-metric-card--pipeline"><span class="ui-metric-icon"><i class="fa-solid fa-clipboard-check"></i></span><span class="ui-metric-content"><span class="ui-metric-value" id="metric-komite">0</span><span class="ui-metric-label">Komite</span></span></div>
    </section>

    <section class="pipeline-summary" aria-label="Nilai pipeline kredit">
        <div class="pipeline-summary-card"><div class="pipeline-summary-label">Total pengajuan SLA</div><div class="pipeline-summary-value" id="summary-pengajuan">Rp0</div><div class="pipeline-summary-meta" id="summary-pengajuan-meta">0 NOA • Status SLA</div></div>
        <div class="pipeline-summary-card"><div class="pipeline-summary-label">Realisasi closing</div><div class="pipeline-summary-value" id="summary-realisasi">Rp0</div><div class="pipeline-summary-meta" id="summary-percent">0 NOA • 0% dari pengajuan SLA</div></div>
    </section>

    <section class="pipeline-filter-section">
        <div class="ui-filter-toolbar pipeline-filter-toolbar">
            <!-- # COMPONENT: Filter AO Kredit berada di sisi kiri toolbar. -->
            <div class="ui-filter-field pipeline-ao-field" id="pipeline-ao-wrap">
                <label class="ui-filter-label" for="pipeline-ao">AO Kredit</label>
                <select class="ui-filter-control" id="pipeline-ao"><option value="">Semua AO</option></select>
            </div>
            <!-- # COMPONENT: Dropdown tahap SLA pipeline. -->
            <div class="ui-filter-field pipeline-stage-field">
                <label class="ui-filter-label" for="pipeline-stage">Tahap SLA</label>
                <select class="ui-filter-control" id="pipeline-stage">
                    <option value="">Semua Tahap</option>
                    <option value="PEMBERKASAN">Pemberkasan</option>
                    <option value="SURVEY">Survey</option>
                    <option value="ANALISA">Analisa</option>
                    <option value="KOMITE">Komite</option>
                    <option value="SELESAI">Selesai</option>
                </select>
            </div>
            <!-- # COMPONENT: Pencarian cepat pipeline. -->
            <div class="ui-filter-search pipeline-search">
                <input type="search" class="ui-filter-control pipeline-filter-control flex-grow-1" id="pipeline-search" placeholder="Cari debitur..." aria-label="Cari debitur">
                <button type="button" class="ui-filter-search-button btn-filter-apply" id="pipeline-search-button" title="Cari"><i class="fa-solid fa-magnifying-glass"></i></button>
                <span id="pipeline-loading" class="text-muted" style="display:none;font-size:0.68rem;white-space:nowrap;">Memuat...</span>
            </div>
        </div>
        <div id="pipeline-filter" class="ui-filter-panel" data-filter-panel hidden>
            <div class="ui-filter-panel-head"><h6 class="ui-filter-panel-title"><i class="fa-solid fa-sliders" aria-hidden="true"></i> Filter Pipeline</h6><button type="button" class="ui-filter-close" data-filter-close aria-label="Tutup filter"><i class="fa-solid fa-xmark"></i></button></div>
            <div class="ui-filter-grid pipeline-filter-row">
                <!-- # COMPONENT: Periode pipeline, batas bawah eksklusif dan batas atas inklusif. -->
                <div class="ui-filter-field"><label class="ui-filter-label" for="pipeline-from">Closing (M-1)</label><input type="date" class="ui-filter-control" id="pipeline-from" value="<?= htmlspecialchars($default_closing_date, ENT_QUOTES, 'UTF-8') ?>"></div>
                <div class="ui-filter-field"><label class="ui-filter-label" for="pipeline-to">Harian (Actual)</label><input type="date" class="ui-filter-control" id="pipeline-to" value="<?= htmlspecialchars($default_harian_date, ENT_QUOTES, 'UTF-8') ?>"></div>
                <!-- # COMPONENT: Filter kantor bersama untuk Korwil dan Cabang. -->
                <div class="ui-filter-field"><label class="ui-filter-label" for="pipeline-korwil">Korwil</label><select class="ui-filter-control" id="pipeline-korwil"><option value="">Konsolidasi (Semua)</option><option value="semarang">Semarang</option><option value="solo">Solo</option><option value="banyumas">Banyumas</option><option value="pekalongan">Pekalongan</option></select></div>
                <div class="ui-filter-field"><label class="ui-filter-label" for="pipeline-cabang">Cabang</label><select class="ui-filter-control" id="pipeline-cabang"><option value="">Semua Cabang</option></select></div>
            </div>
        </div>
    </section>

    <section class="pipeline-list-area">
        <div class="ui-responsive-data" id="pipeline-container" data-responsive-data><div class="ui-responsive-data-cards" data-responsive-cards></div><div class="ui-responsive-data-table" data-responsive-table></div></div>
        <div class="empty-state" id="pipeline-empty" style="display:none;text-align:center;padding:50px 20px;"><i class="fa-solid fa-diagram-project d-block" style="font-size:3rem;color:#CBD5E1;margin-bottom:15px;"></i><h6 class="fw-bold" style="color:#64748B;">Pipeline belum memiliki data</h6><p style="font-size:0.8rem;color:#94A3B8;">Prospek akan muncul setelah dikonfirmasi lanjut ke proses kredit.</p></div>
        <div class="pagination-bar" id="pipeline-pagination"></div>
    </section>
</main>

<script>
(function () {
    const BASE_APP = <?= json_encode(BASE_APP) ?>;
    const initialType = <?= json_encode($url_type) ?>;
    const initialStage = <?= json_encode($url_stage) ?>;
    const userRole = <?= json_encode($user_role) ?>;
    const userKodeKantor = <?= json_encode($user_kode_kantor) ?>;
    const userAccessKorwil = <?= json_encode($user_access_korwil) ?>;
    const isPusat = <?= $is_pusat ? 'true' : 'false' ?>;
    const isLoginAo = ['ao_kredit', 'ao_dana', 'ao_remedial'].includes(userRole);
    const allowedStages = ['PEMBERKASAN', 'SURVEY', 'ANALISA', 'KOMITE', 'SELESAI'];
    const elements = {
        type: document.getElementById('pipeline-type'), stage: document.getElementById('pipeline-stage'), korwil: document.getElementById('pipeline-korwil'), cabang: document.getElementById('pipeline-cabang'), ao: document.getElementById('pipeline-ao'), aoWrap: document.getElementById('pipeline-ao-wrap'), from: document.getElementById('pipeline-from'), to: document.getElementById('pipeline-to'), search: document.getElementById('pipeline-search'), loading: document.getElementById('pipeline-loading'), cards: document.querySelector('#pipeline-container [data-responsive-cards]'), table: document.querySelector('#pipeline-container [data-responsive-table]'), empty: document.getElementById('pipeline-empty'), pagination: document.getElementById('pipeline-pagination')
    };
    let currentPage = 1;
    let currentStage = allowedStages.includes(initialStage) ? initialStage : '';
    let allCabang = [];

    function escapeHtml(value) { return String(value ?? '').replace(/[&<>"']/g, ch => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#039;'}[ch])); }
    function formatDate(value) { if (!value) return '-'; return new Date(value).toLocaleDateString('id-ID', {day:'numeric', month:'short', year:'numeric'}); }
    function formatRupiah(value) { return new Intl.NumberFormat('id-ID', {style:'currency', currency:'IDR', maximumFractionDigits:0}).format(Number(value || 0)); }
    function percent(realization, requested) { const base = Number(requested || 0); return base > 0 ? Math.round((Number(realization || 0) / base) * 100) : 0; }
    // # COMPONENT: Stage legacy dinormalisasi agar tidak menampilkan Formulir/Cair sebagai menu terpisah.
    function stageLabel(stage) { return ({FORMULIR:'Pemberkasan', PEMBERKASAN:'Pemberkasan', SURVEY:'Survey', ANALISA:'Analisa', KOMITE:'Komite', CAIR:'Selesai', SELESAI:'Selesai'}[stage] || stage || 'Menunggu tahap'); }
    function typeLabel(type) { return type === 'DEBITUR_EXISTING' ? 'Debitur Existing' : 'Kredit'; }
    function statusBadge(status) { const cls = ({OPEN:'status-open', FOLLOW_UP:'status-follow_up', SLA:'status-sla', PENDING:'status-pending', CLOSING:'status-closing', REJECT:'status-reject'}[status] || ''); return `<span class="badge-status ${cls}">${escapeHtml(String(status || '-').replace('_', ' '))}</span>`; }
    function typeBadge(type) { return `<span class="badge-type ${type === 'DEBITUR_EXISTING' ? 'badge-existing' : 'badge-kredit'}">${escapeHtml(typeLabel(type))}</span>`; }
    function ageLabel(days) { return `Proses SLA ${Math.max(0, Number(days || 0))} hari`; }
    function shortEmployeeName(name) {
        const label = String(name || '').trim();
        if (!label) return '';
        const chars = Array.from(label);
        return chars.length > 8 ? chars.slice(0, 8).join('').trimEnd() + '...' : label;
    }
    function aoMeta(pipeline) {
        return aoMarkup(pipeline, 'pipeline-card-ao');
    }
    function aoMarkup(pipeline, className) {
        const delegatedId = String(pipeline.assigned_to || '').trim();
        const delegatedName = shortEmployeeName(pipeline.assigned_to_name) || (delegatedId ? 'Nama belum tersedia' : '');
        const lines = delegatedName ? `<div class="pipeline-ao-line"><span class="pipeline-ao-label">AO</span><span class="pipeline-ao-value">${escapeHtml(delegatedName)}</span></div>` : '';
        return lines ? `<div class="${className}"><i class="fa-solid fa-user-tie"></i><div class="pipeline-ao-lines">${lines}</div></div>` : '';
    }
    function realizationMarkup(pipeline) {
        const amount = Number(pipeline.closing_realization_amount || 0);
        const ratio = percent(amount, pipeline.requested_loan_amount);
        return `<span class="pipeline-percent"><strong>${escapeHtml(formatRupiah(amount))}</strong><small>(${ratio}%)</small></span>`;
    }
    function pipelineDisplayStatus(pipeline) {
        const pipelineStatus = String(pipeline.credit_pipeline_status || '').toUpperCase();
        if (['CLOSING', 'REJECT'].includes(String(pipeline.status || '').toUpperCase())) return String(pipeline.status).toUpperCase();
        return ['PROSPECT_CONFIRMED', 'PENDING'].includes(pipelineStatus) ? 'PENDING' : String(pipeline.status || '-').toUpperCase();
    }
    function realizationDate(pipeline) {
        const status = String(pipeline.status || '').toUpperCase();
        if (status === 'CLOSING') return pipeline.closing_realization_date || pipeline.closed_at || '';
        return pipeline.planned_realization_date || '';
    }
    function realizationPlan(pipeline) {
        const status = String(pipeline.status || '').toUpperCase();
        const date = realizationDate(pipeline);
        const dateLabel = date ? formatDate(date) : 'Belum diisi';
        const title = status === 'CLOSING' ? 'Realisasi ' : 'Rencana ';
        const className = status === 'CLOSING' ? 'completed' : (date ? 'on-time' : 'missing');
        return `<div class="pipeline-deadline ${className}"><i class="fa-solid ${status === 'CLOSING' ? 'fa-circle-check' : 'fa-calendar-check'}"></i><span>${title}${escapeHtml(dateLabel)}</span></div>`;
    }
    function decoratePipelineViews(items) {
        elements.cards.querySelectorAll('.pipeline-card').forEach((card, index) => {
            const grid = card.querySelector('.pipeline-card-grid');
            if (grid && items[index]) grid.insertAdjacentHTML('beforebegin', realizationPlan(items[index]));
        });
        const table = elements.table.querySelector('table');
        if (!table) return;
        const header = table.querySelector('thead tr');
        const headerCell = document.createElement('th');
        headerCell.textContent = 'Rencana / Realisasi';
        header.insertBefore(headerCell, header.children[4] || null);
        table.querySelectorAll('tbody tr').forEach((row, index) => {
            const pipeline = items[index];
            if (!pipeline) return;
            const date = realizationDate(pipeline);
            const dateLabel = date ? formatDate(date) : '-';
            const cell = document.createElement('td');
            const isClosing = String(pipeline.status || '').toUpperCase() === 'CLOSING';
            cell.innerHTML = `<span class="pipeline-table-deadline ${isClosing ? 'completed' : (date ? 'on-time' : 'missing')}">${escapeHtml(dateLabel)}</span>`;
            row.insertBefore(cell, row.children[4] || null);
        });
    }
    function setText(id, value) { const node = document.getElementById(id); if (node) node.textContent = value; }

    function setActiveStage() { elements.stage.value = currentStage; }
    async function loadCabangFilter() {
        try { const response = await fetch(BASE_APP + '/api/?action=master_kode_kantor', {credentials:'include'}); const body = await response.json(); if (body.status === 200 && body.data) allCabang = body.data.all || []; } catch (error) { allCabang = []; }
        renderCabangFilter();
    }
    function renderCabangFilter() {
        if (userAccessKorwil) elements.korwil.value = userAccessKorwil;
        const korwil = elements.korwil.value;
        const filtered = korwil ? allCabang.filter(row => row.korwil === korwil) : allCabang.filter(row => row.kode_kantor !== '000');
        elements.cabang.innerHTML = '<option value="">Semua Cabang</option>' + filtered.map(row => `<option value="${escapeHtml(row.kode_kantor)}">${escapeHtml(row.kode_kantor)} - ${escapeHtml(row.nama_kantor)}</option>`).join('');
        if (userAccessKorwil) elements.korwil.disabled = true;
        if (!isPusat && userKodeKantor !== '000') { elements.cabang.value = userKodeKantor; elements.cabang.disabled = true; elements.korwil.disabled = true; }
        loadAoFilter();
    }
    async function loadAoFilter() {
        if (isLoginAo) { elements.aoWrap.style.display = 'none'; return; }
        elements.aoWrap.style.display = 'block';
        const kode = elements.cabang.value || (!isPusat && userKodeKantor !== '000' ? userKodeKantor : '');
        elements.ao.innerHTML = kode ? '<option value="">Semua AO</option>' : '<option value="">Pilih cabang dulu</option>';
        if (!kode) return;
        try { const query = new URLSearchParams({kode_kantor:kode, group_jabatan:'AO Kredit', tipe:'kredit'}); const response = await fetch(BASE_APP + '/api/?action=master_pegawai_ao&' + query.toString(), {credentials:'include'}); const body = await response.json(); const rows = body.status === 200 ? (body.data || []) : []; elements.ao.innerHTML = '<option value="">Semua AO</option>' + rows.map(row => `<option value="${escapeHtml(row.employee_id)}">${escapeHtml(row.full_name || row.employee_id)}</option>`).join(''); } catch (error) { elements.ao.innerHTML = '<option value="">AO gagal dimuat</option>'; }
    }
    function buildParams() {
        const query = new URLSearchParams({pipeline_credit:'1', page:String(currentPage), limit:'20', search:elements.search.value.trim()});
        if (elements.type?.value) query.set('prospect_type', elements.type.value);
        if (currentStage) query.set('pipeline_stage', currentStage);
        if (elements.korwil.value) query.set('korwil', elements.korwil.value);
        if (elements.cabang.value) query.set('kode_kantor', elements.cabang.value);
        if (elements.ao.value && !isLoginAo) query.set('assigned_to', elements.ao.value);
        if (elements.from.value) query.set('closing_date', elements.from.value);
        if (elements.to.value) query.set('harian_date', elements.to.value);
        return query;
    }
    function renderCard(pipeline) {
        const type = String(pipeline.prospect_type || '').toUpperCase(); const stage = String(pipeline.credit_pipeline_stage || '').toUpperCase(); const branch = pipeline.nama_kantor ? `${pipeline.kode_kantor} - ${pipeline.nama_kantor}` : (pipeline.kode_kantor || '-');
        return `<a href="${BASE_APP}/detail-pipeline/${encodeURIComponent(pipeline.id)}" class="pipeline-card t-${type.toLowerCase()}"><div class="pipeline-card-head"><span>${typeBadge(type)}</span><span>${statusBadge(pipeline.status)}</span></div><div class="pipeline-card-grid"><div class="pipeline-card-main"><div class="p-meta"><i class="fa-solid fa-building"></i><span class="meta-text">${escapeHtml(branch)}</span></div><div class="p-name" title="${escapeHtml(pipeline.customer_name || '-')}" style="font-size:0.88rem;font-weight:800;color:#1E293B;">${escapeHtml(pipeline.customer_name || '-')}</div>${aoMeta(pipeline)}<div class="p-meta"><i class="fa-solid fa-diagram-project"></i><span class="meta-text">${escapeHtml(stageLabel(stage))}</span></div><small class="pipeline-card-stage-age">${escapeHtml(ageLabel(pipeline.pipeline_days))}</small><div class="p-meta"><i class="fa-solid fa-calendar"></i><span class="meta-text">Input ${escapeHtml(formatDate(pipeline.created_at))}</span></div></div><div class="pipeline-card-side"><div class="pipeline-amount">${escapeHtml(formatRupiah(pipeline.requested_loan_amount))}</div>${realizationMarkup(pipeline)}</div></div></a>`;
    }
    function renderTable(items) {
        const rows = items.map(pipeline => { const type = String(pipeline.prospect_type || '').toUpperCase(); const stage = String(pipeline.credit_pipeline_stage || '').toUpperCase(); const branch = pipeline.nama_kantor ? `${pipeline.kode_kantor} - ${pipeline.nama_kantor}` : (pipeline.kode_kantor || '-'); return `<tr class="ui-responsive-data-row t-${type.toLowerCase()}"><td class="ui-table-muted">${escapeHtml(branch)}</td><td><a class="ui-table-primary" href="${BASE_APP}/detail-pipeline/${encodeURIComponent(pipeline.id)}">${escapeHtml(pipeline.customer_name || '-')}</a><span class="ui-table-secondary">${typeBadge(type)}</span></td><td>${aoMarkup(pipeline, 'pipeline-table-ao')}</td><td><span class="pipeline-table-stage">${escapeHtml(stageLabel(stage))}</span><small>${escapeHtml(ageLabel(pipeline.pipeline_days))}</small></td><td><strong>${escapeHtml(formatRupiah(pipeline.requested_loan_amount))}</strong></td><td>${realizationMarkup(pipeline)}</td><td>${statusBadge(pipeline.status)}</td></tr>`; }).join('');
        return `<table aria-label="Tabel pipeline kredit"><thead><tr><th>Cabang</th><th>Nasabah</th><th>AO Pengelola</th><th>Tahap</th><th>Pengajuan</th><th>Realisasi</th><th>Status</th></tr></thead><tbody>${rows}</tbody></table>`;
    }
    function renderList(items, pagination) { items.forEach(pipeline => { pipeline.status = pipelineDisplayStatus(pipeline); }); setText('pipeline-total', pagination.total || items.length); elements.cards.innerHTML = items.map(renderCard).join(''); elements.table.innerHTML = renderTable(items); decoratePipelineViews(items); elements.empty.style.display = items.length ? 'none' : 'block'; renderPagination(pagination); }
    function renderPagination(pagination) { if (!pagination || Number(pagination.total_pages || 0) <= 1) { elements.pagination.innerHTML = ''; return; } let html = `<button class="pg-btn" ${pagination.page <= 1 ? 'disabled' : ''} data-page="${pagination.page - 1}"><i class="fa-solid fa-chevron-left"></i></button>`; for (let page = 1; page <= Math.min(Number(pagination.total_pages), 5); page++) html += `<button class="pg-btn ${page === Number(pagination.page) ? 'active' : ''}" data-page="${page}">${page}</button>`; html += `<button class="pg-btn" ${pagination.page >= pagination.total_pages ? 'disabled' : ''} data-page="${pagination.page + 1}"><i class="fa-solid fa-chevron-right"></i></button>`; elements.pagination.innerHTML = html; elements.pagination.querySelectorAll('[data-page]').forEach(button => button.addEventListener('click', () => { if (!button.disabled) { currentPage = Number(button.dataset.page); loadData(); } })); }
    async function loadSummary() { const query = buildParams(); query.delete('page'); query.delete('limit'); query.delete('search'); try { const response = await fetch(BASE_APP + '/api/?action=prospect_report&' + query.toString(), {credentials:'include'}); const body = await response.json(); const summary = body.data?.summary || {}; const requestedNoa = Number(summary.total_pipeline_pengajuan_noa || 0); const realizedNoa = Number(summary.total_pipeline_realisasi_noa || 0); setText('metric-pemberkasan', summary.total_pipeline_pemberkasan || 0); setText('metric-survey', summary.total_pipeline_survey || 0); setText('metric-analisa', summary.total_pipeline_analisa || 0); setText('metric-komite', summary.total_pipeline_komite || 0); setText('summary-pengajuan', formatRupiah(summary.total_pipeline_pengajuan || 0)); setText('summary-pengajuan-meta', requestedNoa + ' NOA • Status SLA'); setText('summary-realisasi', formatRupiah(summary.total_pipeline_realisasi || 0)); setText('summary-percent', realizedNoa + ' NOA • ' + percent(summary.total_pipeline_realisasi, summary.total_pipeline_pengajuan) + '% dari pengajuan SLA'); } catch (error) { /* List tetap dapat dipakai jika summary gagal. */ } }
    async function loadData() { elements.loading.style.display = 'inline'; try { const response = await fetch(BASE_APP + '/api/?action=prospect_list&' + buildParams().toString(), {credentials:'include'}); const body = await response.json(); if (body.status === 200 && body.data) { renderList(body.data.items || [], body.data.pagination || {}); loadSummary(); } else renderList([], {}); } catch (error) { renderList([], {}); } finally { elements.loading.style.display = 'none'; } }
    elements.stage.addEventListener('change', () => { currentStage = allowedStages.includes(elements.stage.value) ? elements.stage.value : ''; setActiveStage(); currentPage = 1; loadData(); });
    elements.korwil.addEventListener('change', () => { renderCabangFilter(); currentPage = 1; loadData(); }); elements.cabang.addEventListener('change', () => { loadAoFilter(); currentPage = 1; loadData(); });
    elements.ao.addEventListener('change', () => { currentPage = 1; loadData(); });
    elements.from.addEventListener('change', () => { currentPage = 1; loadData(); }); elements.to.addEventListener('change', () => { currentPage = 1; loadData(); });
    document.getElementById('pipeline-search-button').addEventListener('click', () => { currentPage = 1; loadData(); }); elements.search.addEventListener('keydown', event => { if (event.key === 'Enter') { currentPage = 1; loadData(); } }); if (elements.type) elements.type.addEventListener('change', () => { currentPage = 1; loadData(); });
    if (initialType && elements.type) elements.type.value = initialType; setActiveStage(); loadCabangFilter().then(loadData);
})();
</script>
