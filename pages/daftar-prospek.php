<?php
$is_ao = in_array($user_role, ['ao_kredit', 'ao_dana', 'ao_remedial', 'developer']);
$is_superuser = in_array($user_role, ['superuser', 'developer']);
$is_pusat = ($user_kode_kantor === '000');

// Default dates for report
$default_closing_start = date('Y-m-01', strtotime('last month'));
$default_closing_end = date('Y-m-t', strtotime('last month'));
$default_harian = date('Y-m-d');

// URL params (pre-fill filters)
$url_filter = $_GET['filter'] ?? '';
$url_type = $_GET['type'] ?? '';
$url_source = $_GET['source'] ?? '';
$url_view = $_GET['view'] ?? 'list'; // list | report
?>

<style>
    .filter-section {
        background: #ffffff; border-radius: 14px; margin: 0 16px 16px 16px;
        padding: 14px; box-shadow: 0 2px 10px rgba(0,0,0,0.025);
    }
    @media (min-width: 768px) { .filter-section { margin: 0 24px 16px 24px; padding: 18px; } }
    @media (min-width: 1024px) { .filter-section { margin: 0 32px 20px 32px; padding: 20px; } }

    .filter-tabs {
        display: flex; background: #F8FAFC; border-radius: 10px; padding: 3px;
        margin-bottom: 12px; overflow-x: auto; -webkit-overflow-scrolling: touch;
    }
    .filter-tabs::-webkit-scrollbar { display: none; }
    .ftab {
        flex: none; text-align: center; padding: 8px 14px; background: transparent; border: none;
        border-radius: 8px; color: #64748B; font-weight: 700; font-size: 0.7rem;
        transition: all 0.2s; cursor: pointer; white-space: nowrap;
    }
    .ftab.active { background: var(--color-primary); color: white; box-shadow: 0 2px 8px rgba(10,25,49,0.15); }
    @media (min-width: 768px) { .ftab { padding: 9px 18px; font-size: 0.75rem; } }

    .filter-row { display: flex; gap: 8px; flex-wrap: wrap; }
    .filter-row .filter-item { flex: 1; min-width: 120px; }
    @media (max-width: 576px) { .filter-row .filter-item { min-width: 100px; } }
    .filter-select {
        background: #F4F7F6; border: 1px solid #E2E8F0; border-radius: 8px;
        padding: 8px 10px; font-size: 0.75rem; font-weight: 600; width: 100%;
        color: #475569; min-height: 38px;
    }
    .filter-select:focus { border-color: var(--color-primary); outline: none; }

    .btn-filter-apply {
        background: var(--color-primary); color: white; border: none; border-radius: 8px;
        padding: 8px 16px; font-size: 0.75rem; font-weight: 700; min-height: 38px; cursor: pointer;
    }

    /* List area */
    .list-area { padding: 0 16px 80px 16px; }
    @media (min-width: 768px) { .list-area { padding: 0 24px 80px 24px; } }
    @media (min-width: 1024px) { .list-area { padding: 0 32px 80px 32px; } }

    /* Prospek card */
    .prospek-card {
        background: #ffffff; border-radius: 14px; padding: 14px; margin-bottom: 12px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.025); border-left: 4px solid transparent;
        transition: transform 0.15s; text-decoration: none; color: inherit; display: block;
    }
    .prospek-card:hover { box-shadow: 0 4px 16px rgba(0,0,0,0.05); }
    .prospek-card:active { transform: scale(0.99); }
    .prospek-card.t-kredit { border-left-color: #1976D2; }
    .prospek-card.t-tabungan { border-left-color: #388E3C; }
    .prospek-card.t-deposito { border-left-color: #7B1FA2; }
    .prospek-card.t-pembeli_aset { border-left-color: #F57C00; }
    .prospek-card.t-debitur_existing { border-left-color: #00838F; }
    @media (min-width: 768px) { .prospek-card { padding: 18px; } }

    .p-name { font-size: 0.88rem; font-weight: 700; color: #1E293B; margin-bottom: 3px; }
    .p-meta { font-size: 0.7rem; color: #64748B; display: flex; align-items: center; gap: 4px; margin-bottom: 2px; }
    .p-meta i { width: 14px; color: #A0AEC0; font-size: 0.65rem; }

    .badge-delegasi { font-size: 0.55rem; padding: 3px 6px; border-radius: 4px; font-weight: 700; }
    .del-pending { background: #FFE0B2; color: #E65100; }
    .del-done { background: #C8E6C9; color: #2E7D32; }

    /* Report section */
    .report-card {
        background: #ffffff; border-radius: 14px; padding: 16px; margin-bottom: 14px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.025);
    }
    @media (min-width: 768px) { .report-card { padding: 20px; } }
    .report-title { font-size: 0.75rem; font-weight: 800; color: var(--color-primary); text-transform: uppercase; margin-bottom: 10px; }
    .report-value { font-size: 1.4rem; font-weight: 800; color: #1E293B; }
    .report-label { font-size: 0.65rem; color: #64748B; font-weight: 600; }

    /* Empty state */
    .empty-state { text-align: center; padding: 50px 20px; }
    .empty-state i { font-size: 3rem; color: #CBD5E1; margin-bottom: 15px; }

    /* Pagination */
    .pagination-bar {
        display: flex; justify-content: center; align-items: center; gap: 8px;
        padding: 16px 0;
    }
    .pg-btn {
        width: 36px; height: 36px; border-radius: 8px; border: 1px solid #E2E8F0;
        background: #fff; color: #475569; font-weight: 700; font-size: 0.8rem;
        display: flex; align-items: center; justify-content: center; cursor: pointer;
    }
    .pg-btn.active { background: var(--color-primary); color: white; border-color: var(--color-primary); }
    .pg-btn:disabled { opacity: 0.4; cursor: default; }
</style>


<div class="header-compact">
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h5 class="fw-bold mb-0">Daftar Prospek</h5>
            <p class="small text-white-50 mb-0" style="font-size:0.7rem;">
                <?php if ($is_superuser): ?>Seluruh prospek<?= $is_pusat ? '' : ' cabang Anda' ?>
                <?php elseif ($is_ao): ?>Pipeline prospek Anda
                <?php else: ?>Prospek yang Anda input<?php endif; ?>
            </p>
        </div>
        <span class="badge bg-white text-primary rounded-pill px-3 py-2 fw-bold" id="total-badge" style="font-size:0.8rem;">0</span>
    </div>
</div>

<!-- Stats -->
<div class="stats-row" style="margin:-30px 16px 16px 16px; position:relative; z-index:10;">
    <div class="stat-card"><span class="stat-num" id="s-open" style="font-size:1.2rem;font-weight:800;color:var(--color-warning);">0</span><span class="stat-lbl" style="font-size:0.6rem;color:#64748B;font-weight:600;">Open</span></div>
    <div class="stat-card"><span class="stat-num" id="s-fu" style="font-size:1.2rem;font-weight:800;color:#1976D2;">0</span><span class="stat-lbl" style="font-size:0.6rem;color:#64748B;font-weight:600;">Follow Up</span></div>
    <div class="stat-card"><span class="stat-num" id="s-sla" style="font-size:1.2rem;font-weight:800;color:#388E3C;">0</span><span class="stat-lbl" style="font-size:0.6rem;color:#64748B;font-weight:600;">SLA</span></div>
    <div class="stat-card"><span class="stat-num" id="s-closing" style="font-size:1.2rem;font-weight:800;color:var(--color-accent);">0</span><span class="stat-lbl" style="font-size:0.6rem;color:#64748B;font-weight:600;">Closing</span></div>
    <div class="stat-card"><span class="stat-num" id="s-reject" style="font-size:1.2rem;font-weight:800;color:#D32F2F;">0</span><span class="stat-lbl" style="font-size:0.6rem;color:#64748B;font-weight:600;">Reject</span></div>
</div>

<!-- Filters -->
<div class="filter-section">
    <!-- Source tabs (AO / Non-AO / Pending) -->
    <div class="filter-tabs" id="source-tabs">
        <button class="ftab active" data-source="all">Semua</button>
        <button class="ftab" data-source="ao">Dari AO</button>
        <button class="ftab" data-source="non_ao">Dari Non-AO</button>
        <?php if ($is_superuser): ?>
        <button class="ftab" data-source="pending">Belum Delegasi</button>
        <?php endif; ?>
    </div>

    <!-- Search -->
    <div class="mb-2">
        <div class="d-flex gap-2">
            <input type="text" class="filter-select flex-grow-1" id="f-search" placeholder="Cari nama nasabah...">
            <button class="btn-filter-apply" onclick="loadData()" title="Cari"><i class="fa-solid fa-magnifying-glass"></i></button>
        </div>
    </div>

    <!-- Collapsible advanced filter -->
    <details id="advanced-filter">
        <summary style="font-size:0.7rem; font-weight:700; color:var(--color-primary); cursor:pointer; margin-bottom:10px;">
            <i class="fa-solid fa-sliders me-1"></i> Filter Lanjutan
        </summary>
        <div class="filter-row mb-2">
            <div class="filter-item">
                <label style="font-size:0.6rem; font-weight:700; color:#64748B;">Jenis</label>
                <select class="filter-select" id="f-type">
                    <option value="">Semua Jenis</option>
                    <option value="KREDIT">Kredit</option>
                    <option value="TABUNGAN">Tabungan</option>
                    <option value="DEPOSITO">Deposito</option>
                    <option value="PEMBELI_ASET">Pembeli Aset</option>
                    <option value="DEBITUR_EXISTING">Debitur Existing</option>
                </select>
            </div>
            <div class="filter-item">
                <label style="font-size:0.6rem; font-weight:700; color:#64748B;">Status</label>
                <select class="filter-select" id="f-status">
                    <option value="">Semua Status</option>
                    <option value="OPEN">Open</option>
                    <option value="FOLLOW_UP">Follow Up</option>
                    <option value="SLA">SLA</option>
                    <option value="CLOSING">Closing</option>
                    <option value="REJECT">Reject</option>
                </select>
            </div>
        </div>
        <div class="filter-row mb-2">
            <div class="filter-item">
                <label style="font-size:0.6rem; font-weight:700; color:#64748B;">Korwil</label>
                <select class="filter-select" id="f-korwil">
                    <option value="">Konsolidasi (Semua)</option>
                    <option value="semarang">Semarang (001-007)</option>
                    <option value="solo">Solo (008-014)</option>
                    <option value="banyumas">Banyumas (015-021)</option>
                    <option value="pekalongan">Pekalongan (022-028)</option>
                </select>
            </div>
            <div class="filter-item">
                <label style="font-size:0.6rem; font-weight:700; color:#64748B;">Cabang</label>
                <select class="filter-select" id="f-cabang">
                    <option value="">Semua Cabang</option>
                </select>
            </div>
        </div>
        <div class="filter-row mb-2">
            <div class="filter-item">
                <label style="font-size:0.6rem; font-weight:700; color:#64748B;">Dari Tanggal</label>
                <input type="date" class="filter-select" id="f-date-from">
            </div>
            <div class="filter-item">
                <label style="font-size:0.6rem; font-weight:700; color:#64748B;">Sampai Tanggal</label>
                <input type="date" class="filter-select" id="f-date-to" value="<?= $default_harian ?>">
            </div>
        </div>
        <div class="filter-row">
            <div class="filter-item">
                <label style="font-size:0.6rem; font-weight:700; color:#64748B;">Closing Dari</label>
                <input type="date" class="filter-select" id="f-cl-from" value="<?= $default_closing_start ?>">
            </div>
            <div class="filter-item">
                <label style="font-size:0.6rem; font-weight:700; color:#64748B;">Closing Sampai</label>
                <input type="date" class="filter-select" id="f-cl-to" value="<?= $default_closing_end ?>">
            </div>
        </div>
        <button class="btn-filter-apply w-100 mt-3" onclick="loadData()">
            <i class="fa-solid fa-filter me-1"></i> Terapkan Filter
        </button>
    </details>
</div>

<!-- View toggle: List vs Report -->
<div class="d-flex justify-content-between align-items-center" style="padding:0 16px; margin-bottom:12px;">
    <h6 class="fw-bold mb-0" style="font-size:0.82rem; color:#1E293B;" id="list-title">Hasil</h6>
    <div class="d-flex gap-1">
        <button class="pg-btn" id="btn-view-list" title="List View" onclick="switchView('list')"><i class="fa-solid fa-list"></i></button>
        <button class="pg-btn" id="btn-view-report" title="Report View" onclick="switchView('report')"><i class="fa-solid fa-chart-bar"></i></button>
    </div>
</div>

<!-- Content: List view -->
<div class="list-area" id="view-list">
    <div class="grid-cards" id="prospect-container"></div>
    <div class="empty-state" id="empty-state" style="display:none;">
        <i class="fa-solid fa-clipboard-list d-block"></i>
        <h6 class="fw-bold" style="color:#64748B;">Tidak Ada Data</h6>
        <p style="font-size:0.8rem; color:#94A3B8;">Ubah filter atau input prospek baru</p>
    </div>
    <div class="pagination-bar" id="pagination"></div>
</div>

<!-- Content: Report view -->
<div class="list-area" id="view-report" style="display:none;">
    <div class="grid-cards" id="report-container"></div>
</div>

<!-- FAB -->
<a href="<?= BASE_APP ?>/input-prospek" class="btn-fab" title="Input Prospek Baru"><i class="fa-solid fa-plus"></i></a>


<script>
(function() {
    const BASE_APP = <?= json_encode(BASE_APP) ?>;
    const urlFilter = '<?= $url_filter ?>';
    const urlType = '<?= $url_type ?>';
    const urlSource = '<?= $url_source ?>';
    const urlView = '<?= $url_view ?>';

    let currentPage = 1;
    let currentSource = urlSource || 'all';

    // Pre-fill from URL params
    if (urlFilter === 'pending') currentSource = 'pending';
    if (urlFilter === 'sla') document.getElementById('f-status').value = 'SLA';
    if (urlType) document.getElementById('f-type').value = urlType;

    // =========================================
    // SOURCE TABS
    // =========================================
    document.querySelectorAll('#source-tabs .ftab').forEach(btn => {
        if (btn.dataset.source === currentSource) { btn.classList.add('active'); } else { btn.classList.remove('active'); }
        btn.addEventListener('click', function() {
            document.querySelectorAll('#source-tabs .ftab').forEach(b => b.classList.remove('active'));
            this.classList.add('active');
            currentSource = this.dataset.source;
            currentPage = 1;
            loadData();
        });
    });

    // =========================================
    // CABANG DROPDOWN (mirror from korwil)
    // =========================================
    const fKorwil = document.getElementById('f-korwil');
    const fCabang = document.getElementById('f-cabang');
    let allCabang = [];

    async function loadCabangFilter() {
        try {
            const r = await fetch(BASE_APP + '/api/?action=master_kode_kantor', {credentials:'include'});
            const b = await r.json();
            if (b.status === 200 && b.data) allCabang = b.data.all || [];
        } catch(e) { /* fallback empty */ }
        renderCabangFilter();
    }

    function renderCabangFilter() {
        const korwil = fKorwil.value;
        const filtered = korwil ? allCabang.filter(c => c.korwil === korwil) : allCabang.filter(c => c.kode_kantor !== '000');
        fCabang.innerHTML = '<option value="">Semua Cabang</option>';
        filtered.forEach(c => { fCabang.innerHTML += `<option value="${c.kode_kantor}">${c.kode_kantor} - ${c.nama_kantor}</option>`; });
    }
    fKorwil.addEventListener('change', renderCabangFilter);
    loadCabangFilter();

    // =========================================
    // VIEW SWITCH
    // =========================================
    window.switchView = function(view) {
        document.getElementById('view-list').style.display = view === 'list' ? 'block' : 'none';
        document.getElementById('view-report').style.display = view === 'report' ? 'block' : 'none';
        document.getElementById('btn-view-list').classList.toggle('active', view === 'list');
        document.getElementById('btn-view-report').classList.toggle('active', view === 'report');
        if (view === 'report') loadReport();
    };
    if (urlView === 'report') switchView('report'); else switchView('list');

    // =========================================
    // LOAD DATA (List)
    // =========================================
    window.loadData = async function() {
        const params = new URLSearchParams({
            source: currentSource === 'pending' ? 'all' : currentSource,
            search: document.getElementById('f-search').value,
            prospect_type: document.getElementById('f-type').value,
            status: document.getElementById('f-status').value,
            korwil: fKorwil.value,
            kode_kantor: fCabang.value,
            date_from: document.getElementById('f-date-from').value,
            date_to: document.getElementById('f-date-to').value,
            page: currentPage,
            limit: 20,
        });
        if (currentSource === 'pending') params.set('delegation', 'BELUM_DIDELEGASIKAN');

        try {
            const res = await fetch(BASE_APP + '/api/?action=prospect_list&' + params.toString(), {credentials:'include'});
            const body = await res.json();
            if (body.status === 200 && body.data) {
                renderList(body.data.items || [], body.data.pagination || {});
            } else {
                renderList([], {});
            }
        } catch(e) {
            renderList([], {});
        }
    };

    function renderList(items, pagination) {
        const container = document.getElementById('prospect-container');
        const empty = document.getElementById('empty-state');
        const total = pagination.total || items.length;

        // Update stats
        document.getElementById('total-badge').textContent = total;
        updateStats(items);

        if (items.length === 0) { container.innerHTML = ''; empty.style.display = 'block'; return; }
        empty.style.display = 'none';

        container.innerHTML = items.map(p => {
            const typeClass = 't-' + (p.prospect_type || '').toLowerCase();
            const typeBadge = getTypeBadge(p.prospect_type);
            const statusBadge = getStatusBadge(p.status);
            const delBadge = p.delegation_status === 'BELUM_DIDELEGASIKAN' ? '<span class="badge-delegasi del-pending">Pending</span>' : '';
            const nominal = p.estimated_amount ? formatRupiah(p.estimated_amount) : '-';
            const cabang = p.nama_kantor ? `${p.kode_kantor} - ${p.nama_kantor}` : p.kode_kantor;
            return `<a href="${BASE_APP}/prospek-detail/${p.id}" class="prospek-card ${typeClass}">
                <div class="d-flex justify-content-between align-items-start mb-2">
                    <span class="badge-type ${typeBadge.cls}">${typeBadge.lbl}</span>
                    <div class="d-flex gap-1">${delBadge}${statusBadge}</div>
                </div>
                <div class="p-name">${p.customer_name}</div>
                <div class="p-meta"><i class="fa-solid fa-box-open"></i>${p.product_interest || '-'}</div>
                <div class="p-meta"><i class="fa-solid fa-money-bill"></i>${nominal}</div>
                <div class="d-flex justify-content-between align-items-center mt-2 pt-2" style="border-top:1px solid #F4F7F6;">
                    <span class="p-meta mb-0"><i class="fa-solid fa-building"></i>${cabang}</span>
                    <span style="font-size:0.6rem;color:#94A3B8;">${formatDate(p.created_at)}</span>
                </div>
            </a>`;
        }).join('');

        // Pagination
        renderPagination(pagination);
    }

    function updateStats(items) {
        const counts = {OPEN:0, FOLLOW_UP:0, SLA:0, CLOSING:0, REJECT:0};
        items.forEach(p => { if (counts[p.status] !== undefined) counts[p.status]++; });
        document.getElementById('s-open').textContent = counts.OPEN;
        document.getElementById('s-fu').textContent = counts.FOLLOW_UP;
        document.getElementById('s-sla').textContent = counts.SLA;
        document.getElementById('s-closing').textContent = counts.CLOSING;
        document.getElementById('s-reject').textContent = counts.REJECT;
    }

    function renderPagination(pg) {
        const el = document.getElementById('pagination');
        if (!pg || !pg.total_pages || pg.total_pages <= 1) { el.innerHTML = ''; return; }
        let html = `<button class="pg-btn" ${pg.page<=1?'disabled':''} onclick="goPage(${pg.page-1})"><i class="fa-solid fa-chevron-left"></i></button>`;
        for (let i = 1; i <= Math.min(pg.total_pages, 5); i++) {
            html += `<button class="pg-btn ${i===pg.page?'active':''}" onclick="goPage(${i})">${i}</button>`;
        }
        html += `<button class="pg-btn" ${pg.page>=pg.total_pages?'disabled':''} onclick="goPage(${pg.page+1})"><i class="fa-solid fa-chevron-right"></i></button>`;
        el.innerHTML = html;
    }
    window.goPage = function(p) { currentPage = p; loadData(); };

    // =========================================
    // LOAD REPORT
    // =========================================
    async function loadReport() {
        const params = new URLSearchParams({
            source: currentSource === 'pending' ? 'all' : currentSource,
            korwil: fKorwil.value,
            kode_kantor: fCabang.value,
            closing_from: document.getElementById('f-cl-from').value,
            closing_to: document.getElementById('f-cl-to').value,
            harian_date: document.getElementById('f-date-to').value || '<?= $default_harian ?>',
        });
        try {
            const res = await fetch(BASE_APP + '/api/?action=prospect_report&' + params.toString(), {credentials:'include'});
            const body = await res.json();
            if (body.status === 200 && body.data) renderReport(body.data);
        } catch(e) { document.getElementById('report-container').innerHTML = '<p class="text-muted text-center">Gagal memuat report</p>'; }
    }

    function renderReport(data) {
        const s = data.summary || {};
        const cl = data.closing_period || {};
        const hr = data.harian || {};
        const pt = data.per_type || [];

        let html = `
        <div class="report-card" style="background:linear-gradient(135deg,var(--color-primary),var(--color-secondary));color:white;">
            <div class="report-title" style="color:rgba(255,255,255,0.7);border:none;">Total Realisasi Closing</div>
            <div class="report-value" style="color:white;">${formatRupiah(s.total_realisasi||0)}</div>
            <div class="report-label" style="color:rgba(255,255,255,0.6);">${s.total_closing||0} prospek closing</div>
        </div>
        <div class="report-card">
            <div class="report-title">Closing Periode (${cl.from||'-'} s/d ${cl.to||'-'})</div>
            <div class="d-flex justify-content-between">
                <div><div class="report-value" style="font-size:1.1rem;">${cl.jumlah||0}</div><div class="report-label">Jumlah</div></div>
                <div class="text-end"><div class="report-value" style="font-size:1.1rem;">${formatRupiah(cl.nominal||0)}</div><div class="report-label">Nominal</div></div>
            </div>
        </div>
        <div class="report-card">
            <div class="report-title">Input Hari Ini (${hr.date||'-'})</div>
            <div class="report-value">${hr.jumlah_input||0} <span style="font-size:0.8rem;font-weight:600;color:#64748B;">prospek baru</span></div>
        </div>
        <div class="report-card">
            <div class="report-title">Summary Keseluruhan</div>
            <div class="row g-2 text-center">
                <div class="col-4"><div style="background:#FFF9C4;border-radius:10px;padding:10px;"><div style="font-size:1.1rem;font-weight:800;">${s.total_open||0}</div><div class="report-label">Open</div></div></div>
                <div class="col-4"><div style="background:#E3F2FD;border-radius:10px;padding:10px;"><div style="font-size:1.1rem;font-weight:800;">${s.total_follow_up||0}</div><div class="report-label">Follow Up</div></div></div>
                <div class="col-4"><div style="background:#E8F5E9;border-radius:10px;padding:10px;"><div style="font-size:1.1rem;font-weight:800;">${s.total_sla||0}</div><div class="report-label">SLA</div></div></div>
            </div>
            <div class="row g-2 text-center mt-1">
                <div class="col-6"><div style="background:#FFF0EB;border-radius:10px;padding:10px;"><div style="font-size:1.1rem;font-weight:800;">${s.total_from_ao||0}</div><div class="report-label">Dari AO</div></div></div>
                <div class="col-6"><div style="background:#F1F5F9;border-radius:10px;padding:10px;"><div style="font-size:1.1rem;font-weight:800;">${s.total_from_non_ao||0}</div><div class="report-label">Dari Non-AO</div></div></div>
            </div>
            <div class="mt-2 p-2 rounded-3" style="background:#FFEBEE;"><span class="report-label text-danger">Pending Delegasi: <b>${s.total_pending_delegasi||0}</b></span></div>
        </div>`;

        if (pt.length > 0) {
            html += `<div class="report-card"><div class="report-title">Per Jenis Prospek</div>`;
            pt.forEach(t => {
                html += `<div class="d-flex justify-content-between align-items-center py-2" style="border-bottom:1px solid #F4F7F6;">
                    <span style="font-size:0.8rem;font-weight:700;">${t.prospect_type}</span>
                    <div class="text-end"><span style="font-size:0.85rem;font-weight:800;">${t.jumlah}</span> <span class="report-label ms-2">${formatRupiah(t.realisasi||0)}</span></div>
                </div>`;
            });
            html += `</div>`;
        }

        document.getElementById('report-container').innerHTML = html;
    }

    // =========================================
    // HELPERS
    // =========================================
    function getTypeBadge(t) {
        const m = {KREDIT:{cls:'badge-kredit',lbl:'Kredit'},TABUNGAN:{cls:'badge-tabungan',lbl:'Tabungan'},DEPOSITO:{cls:'badge-deposito',lbl:'Deposito'},PEMBELI_ASET:{cls:'badge-aset',lbl:'P. Aset'},DEBITUR_EXISTING:{cls:'badge-existing',lbl:'Existing'}};
        return m[t] || {cls:'',lbl:t};
    }
    function getStatusBadge(s) {
        const m = {OPEN:'status-open',FOLLOW_UP:'status-follow_up',SLA:'status-sla',CLOSING:'status-closing',REJECT:'status-reject'};
        return `<span class="badge-status ${m[s]||''}">${(s||'').replace('_',' ')}</span>`;
    }
    function formatDate(d) { if(!d) return '-'; return new Date(d).toLocaleDateString('id-ID',{day:'numeric',month:'short'}); }

    // =========================================
    // INIT
    // =========================================
    loadData();
})();
</script>
