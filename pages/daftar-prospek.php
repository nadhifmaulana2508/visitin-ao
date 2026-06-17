<?php
$is_ao = in_array($user_role, ['ao_kredit', 'ao_dana', 'ao_remedial', 'developer']);
$is_superuser = in_array($user_role, ['superuser', 'developer']);
?>

<style>
    .header-compact {
        background: linear-gradient(135deg, var(--color-primary), var(--color-secondary));
        color: white; padding: 25px 20px 50px 20px;
        border-bottom-left-radius: 25px; border-bottom-right-radius: 25px;
    }

    /* Stats overlap */
    .stats-row {
        display: flex; gap: 10px; margin: -30px 15px 15px 15px;
        position: relative; z-index: 10;
    }
    .stat-card {
        flex: 1; background: #ffffff; border-radius: 14px; padding: 14px 12px;
        box-shadow: 0 4px 15px rgba(0,0,0,0.04); text-align: center;
    }
    .stat-card .stat-num { font-size: 1.3rem; font-weight: 800; color: var(--color-primary); display: block; }
    .stat-card .stat-lbl { font-size: 0.6rem; font-weight: 700; color: #64748B; text-transform: uppercase; }

    /* Filter tabs */
    .filter-tabs {
        display: flex; background: #ffffff; border-radius: 12px; padding: 4px;
        margin: 0 15px 15px 15px; box-shadow: 0 2px 10px rgba(0,0,0,0.03);
    }
    .filter-tab-btn {
        flex: 1; text-align: center; padding: 9px 5px; background: transparent; border: none;
        border-radius: 10px; color: #94A3B8; font-weight: 700; font-size: 0.7rem;
        transition: all 0.25s ease; cursor: pointer;
    }
    .filter-tab-btn.active {
        background-color: var(--color-primary); color: white;
        box-shadow: 0 3px 8px rgba(10,25,49,0.2);
    }

    /* Filter collapsible */
    .filter-wrapper {
        background-color: #ffffff; border-radius: 14px; margin: 0 15px 15px 15px;
        padding: 12px; box-shadow: 0 2px 10px rgba(0,0,0,0.02);
    }
    .search-input {
        background-color: #F4F7F6; border: 1px solid #E0E0E0; border-radius: 10px;
        padding: 10px 14px; font-size: 0.85rem; width: 100%;
    }
    .search-input:focus { border-color: var(--color-primary); box-shadow: none; outline: none; }
    .btn-toggle-filter {
        background-color: #E6EDF5; color: var(--color-primary); border: none;
        border-radius: 10px; padding: 0 14px; min-height: 44px; transition: 0.2s;
    }
    .filter-label {
        font-size: 0.65rem; font-weight: 700; color: var(--color-primary);
        margin-bottom: 4px; display: block; text-transform: uppercase;
    }

    /* Prospek Cards */
    .prospek-card {
        border: none; border-radius: 14px; margin-bottom: 12px; background-color: #ffffff;
        box-shadow: 0 3px 10px rgba(0,0,0,0.025); transition: transform 0.15s;
        border-left: 4px solid transparent; overflow: hidden;
    }
    .prospek-card:active { transform: scale(0.98); }
    .prospek-card.type-kredit { border-left-color: #1976D2; }
    .prospek-card.type-tabungan { border-left-color: #388E3C; }
    .prospek-card.type-deposito { border-left-color: #7B1FA2; }
    .prospek-card.type-aset { border-left-color: #F57C00; }
    .prospek-card.type-existing { border-left-color: #00838F; }

    .badge-type {
        font-size: 0.6rem; padding: 4px 8px; border-radius: 6px;
        font-weight: 700; letter-spacing: 0.3px;
    }
    .badge-kredit { background: #E3F2FD; color: #1565C0; }
    .badge-tabungan { background: #E8F5E9; color: #2E7D32; }
    .badge-deposito { background: #F3E5F5; color: #6A1B9A; }
    .badge-aset { background: #FFF3E0; color: #E65100; }
    .badge-existing { background: #E0F7FA; color: #006064; }

    .badge-status {
        font-size: 0.6rem; padding: 4px 8px; border-radius: 6px; font-weight: 700;
    }
    .status-open { background: #FFF9C4; color: #F57F17; }
    .status-follow_up { background: #E3F2FD; color: #1565C0; }
    .status-sla { background: #E8F5E9; color: #2E7D32; }
    .status-closing { background: #E8F5E9; color: #1B5E20; }
    .status-reject { background: #FFEBEE; color: #C62828; }

    .badge-delegasi {
        font-size: 0.55rem; padding: 3px 6px; border-radius: 4px; font-weight: 700;
    }
    .delegasi-belum { background: #FFE0B2; color: #E65100; }
    .delegasi-sudah { background: #C8E6C9; color: #2E7D32; }

    .prospek-name { font-size: 0.9rem; font-weight: 700; color: #1E293B; margin-bottom: 2px; }
    .prospek-meta { font-size: 0.7rem; color: #64748B; display: flex; align-items: center; gap: 4px; margin-bottom: 2px; }
    .prospek-meta i { width: 14px; color: #A0AEC0; }

    .btn-fab {
        position: fixed; bottom: calc(var(--nav-height) + var(--safe-area-bottom, 0px) + 20px);
        right: calc(50% - 280px); /* Aligned to wrapper */
        width: 56px; height: 56px; border-radius: 50%;
        background: var(--color-accent); color: white; border: none;
        box-shadow: 0 6px 20px rgba(255,123,84,0.4);
        display: flex; align-items: center; justify-content: center;
        font-size: 1.3rem; z-index: 999; transition: 0.2s;
    }
    .btn-fab:active { transform: scale(0.9); }
    @media (max-width: 600px) {
        .btn-fab { right: 20px; }
    }

    /* Empty state */
    .empty-state {
        text-align: center; padding: 40px 20px;
    }
    .empty-state i { font-size: 3rem; color: #CBD5E1; margin-bottom: 15px; }
    .empty-state h6 { font-weight: 700; color: #64748B; }
    .empty-state p { font-size: 0.8rem; color: #94A3B8; }
</style>

<div class="header-compact">
    <div class="d-flex justify-content-between align-items-center mb-2">
        <div>
            <h5 class="fw-bold mb-0">Daftar Prospek</h5>
            <p class="small text-white-50 mb-0" style="font-size: 0.7rem;">
                <?php if ($is_superuser): ?>
                    Seluruh prospek cabang Anda
                <?php elseif ($is_ao): ?>
                    Pipeline prospek Anda
                <?php else: ?>
                    Prospek yang Anda input
                <?php endif; ?>
            </p>
        </div>
        <span class="badge bg-white text-primary rounded-pill px-3 py-2 fw-bold" id="total-badge">0</span>
    </div>
</div>

<!-- Stats -->
<div class="stats-row">
    <div class="stat-card">
        <span class="stat-num" id="stat-open">0</span>
        <span class="stat-lbl">Open</span>
    </div>
    <div class="stat-card">
        <span class="stat-num" id="stat-followup" style="color: #1976D2;">0</span>
        <span class="stat-lbl">Follow Up</span>
    </div>
    <div class="stat-card">
        <span class="stat-num" id="stat-sla" style="color: #388E3C;">0</span>
        <span class="stat-lbl">SLA</span>
    </div>
    <div class="stat-card">
        <span class="stat-num" id="stat-closing" style="color: var(--color-accent);">0</span>
        <span class="stat-lbl">Closing</span>
    </div>
</div>

<!-- Filter Tabs: AO vs Non-AO -->
<?php if ($is_superuser): ?>
<div class="filter-tabs">
    <button class="filter-tab-btn active" onclick="filterBySource('all', this)">Semua</button>
    <button class="filter-tab-btn" onclick="filterBySource('ao', this)">Dari AO</button>
    <button class="filter-tab-btn" onclick="filterBySource('non_ao', this)">Dari Non-AO</button>
    <button class="filter-tab-btn" onclick="filterBySource('pending', this)">Belum Delegasi</button>
</div>
<?php endif; ?>

<!-- Search & Filter -->
<div class="filter-wrapper">
    <div class="d-flex gap-2">
        <div class="input-group flex-grow-1">
            <span class="input-group-text bg-light border-0" style="border-radius: 10px 0 0 10px;">
                <i class="fa-solid fa-magnifying-glass text-muted"></i>
            </span>
            <input type="text" class="form-control bg-light border-0" id="search-input"
                   placeholder="Cari nama nasabah..." style="font-size: 0.85rem; border-radius: 0 10px 10px 0;">
        </div>
        <button class="btn btn-toggle-filter" type="button" data-bs-toggle="collapse" data-bs-target="#collapseFilter">
            <i class="fa-solid fa-sliders"></i>
        </button>
    </div>

    <div class="collapse" id="collapseFilter">
        <div class="row g-2 pt-3 mt-1 border-top">
            <div class="col-6">
                <label class="filter-label">Jenis Prospek</label>
                <select class="form-select search-input" id="filter-type">
                    <option value="">Semua Jenis</option>
                    <option value="KREDIT">Kredit</option>
                    <option value="TABUNGAN">Tabungan</option>
                    <option value="DEPOSITO">Deposito</option>
                    <option value="PEMBELI_ASET">Pembeli Aset</option>
                    <option value="DEBITUR_EXISTING">Debitur Existing</option>
                </select>
            </div>
            <div class="col-6">
                <label class="filter-label">Status</label>
                <select class="form-select search-input" id="filter-status">
                    <option value="">Semua Status</option>
                    <option value="OPEN">Open</option>
                    <option value="FOLLOW_UP">Follow Up</option>
                    <option value="SLA">SLA</option>
                    <option value="CLOSING">Closing</option>
                    <option value="REJECT">Reject</option>
                </select>
            </div>
            <div class="col-12 mt-2">
                <button class="btn w-100 py-2 fw-bold text-white" style="background-color: var(--color-primary); border-radius: 10px; border:none;"
                        onclick="applyFilters()">
                    Terapkan Filter
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Prospect List -->
<div class="container px-3 mb-5 pb-5" id="prospect-list">
    <!-- Diisi oleh JS -->
    <div class="empty-state" id="empty-state">
        <i class="fa-solid fa-clipboard-list"></i>
        <h6>Belum Ada Prospek</h6>
        <p>Klik tombol <b>+</b> untuk input prospek baru</p>
    </div>
</div>

<!-- FAB Button -->
<a href="<?= BASE_APP ?>/input-prospek" class="btn-fab" title="Input Prospek Baru">
    <i class="fa-solid fa-plus"></i>
</a>

<script>
(function() {
    const BASE_APP = <?= json_encode(BASE_APP) ?>;
    const userRole = '<?= $user_role ?>';
    const isSuperuser = <?= $is_superuser ? 'true' : 'false' ?>;

    let allProspects = [];
    let currentSourceFilter = 'all';

    // =========================================
    // DUMMY DATA (Karena belum ada DB, inject sample data)
    // =========================================
    const DUMMY_PROSPECTS = [
        {
            id: 1, prospect_type: 'KREDIT', customer_name: 'Bapak Ahmad Sudirman',
            phone_number: '081234567890', product_interest: 'Kredit Modal Kerja',
            estimated_amount: 150000000, kecamatan: 'Semarang Tengah', desa: 'Pendrikan',
            description: 'Pemilik toko material, butuh modal untuk ekspansi',
            is_ao_input: true, delegation_status: 'SUDAH_DIDELEGASIKAN', status: 'SLA',
            created_by: '201-001', created_by_name: 'BUDI SANTOSO',
            created_at: '2026-06-10 09:15:00', assigned_to: '201-001'
        },
        {
            id: 2, prospect_type: 'TABUNGAN', customer_name: 'Ibu Rina Wati',
            phone_number: '081298765432', product_interest: 'Tabungan Rencana',
            estimated_amount: 5000000, kecamatan: 'Semarang Barat', desa: 'Krobokan',
            description: 'Ibu rumah tangga, tertarik menabung rutin',
            is_ao_input: false, delegation_status: 'BELUM_DIDELEGASIKAN', status: 'OPEN',
            created_by: '201-005', created_by_name: 'DEWI KUSUMA',
            created_at: '2026-06-12 14:30:00', assigned_to: null
        },
        {
            id: 3, prospect_type: 'KREDIT', customer_name: 'PT Maju Jaya Sentosa',
            phone_number: '081345678901', product_interest: 'Kredit Investasi',
            estimated_amount: 500000000, kecamatan: 'Semarang Utara', desa: 'Bandarharjo',
            description: 'Pengembangan pabrik garmen, butuh mesin baru',
            is_ao_input: false, delegation_status: 'SUDAH_DIDELEGASIKAN', status: 'FOLLOW_UP',
            created_by: '201-006', created_by_name: 'RATNA SARI',
            created_at: '2026-06-08 10:00:00', assigned_to: '201-001'
        },
        {
            id: 4, prospect_type: 'DEPOSITO', customer_name: 'H. Mochtar Abdullah',
            phone_number: '081456789012', product_interest: 'Deposito 12 Bulan',
            estimated_amount: 200000000, kecamatan: 'Rembang', desa: 'Leteh',
            description: 'Dana pensiunan, cari yang aman',
            is_ao_input: true, delegation_status: 'SUDAH_DIDELEGASIKAN', status: 'CLOSING',
            created_by: '201-002', created_by_name: 'SITI RAHAYU',
            created_at: '2026-06-05 08:00:00', assigned_to: '201-002'
        },
        {
            id: 5, prospect_type: 'PEMBELI_ASET', customer_name: 'CV Berkah Abadi',
            phone_number: '081567890123', product_interest: 'Tanah Jaminan',
            estimated_amount: 350000000, kecamatan: 'Kaliori', desa: 'Babadan',
            description: 'Tertarik beli tanah eks jaminan kredit macet',
            is_ao_input: false, delegation_status: 'BELUM_DIDELEGASIKAN', status: 'OPEN',
            created_by: '201-005', created_by_name: 'DEWI KUSUMA',
            created_at: '2026-06-14 11:20:00', assigned_to: null
        },
        {
            id: 6, prospect_type: 'DEBITUR_EXISTING', customer_name: 'Bapak Supriyadi',
            phone_number: '081678901234', product_interest: 'Top-Up KMK',
            estimated_amount: 75000000, kecamatan: 'Semarang Tengah', desa: 'Pandansari',
            description: 'Debitur lancar 2 tahun, mau tambah plafon',
            is_ao_input: true, delegation_status: 'SUDAH_DIDELEGASIKAN', status: 'FOLLOW_UP',
            created_by: '201-001', created_by_name: 'BUDI SANTOSO',
            created_at: '2026-06-13 16:00:00', assigned_to: '201-001'
        },
        {
            id: 7, prospect_type: 'KREDIT', customer_name: 'Ibu Siti Aminah',
            phone_number: '081789012345', product_interest: 'Kredit Multiguna',
            estimated_amount: 50000000, kecamatan: 'Sumber', desa: 'Krikilan',
            description: 'Untuk renovasi rumah dan biaya sekolah anak',
            is_ao_input: false, delegation_status: 'SUDAH_DIDELEGASIKAN', status: 'REJECT',
            created_by: '201-006', created_by_name: 'RATNA SARI',
            created_at: '2026-06-02 09:00:00', assigned_to: '201-001'
        },
    ];

    // =========================================
    // LOAD & RENDER
    // =========================================
    function loadProspects() {
        // Gabungkan dummy + session data (dari API)
        allProspects = [...DUMMY_PROSPECTS];
        
        // Fetch dari API juga (session storage)
        fetch(BASE_APP + '/api/?action=get_prospects', { credentials: 'include' })
            .then(r => r.json())
            .then(body => {
                if (body.status === 200 && Array.isArray(body.data)) {
                    // Tambahkan data dari session, offset ID
                    body.data.forEach(p => {
                        p.id = allProspects.length + p.id;
                        allProspects.push(p);
                    });
                }
                renderList();
            })
            .catch(() => renderList());
    }

    function renderList() {
        let filtered = [...allProspects];

        // Source filter (AO / Non-AO / Pending)
        if (currentSourceFilter === 'ao') {
            filtered = filtered.filter(p => p.is_ao_input === true);
        } else if (currentSourceFilter === 'non_ao') {
            filtered = filtered.filter(p => p.is_ao_input === false);
        } else if (currentSourceFilter === 'pending') {
            filtered = filtered.filter(p => p.delegation_status === 'BELUM_DIDELEGASIKAN');
        }

        // Search
        const search = document.getElementById('search-input').value.toLowerCase().trim();
        if (search) {
            filtered = filtered.filter(p => 
                p.customer_name.toLowerCase().includes(search) ||
                (p.phone_number || '').includes(search)
            );
        }

        // Type filter
        const typeFilter = document.getElementById('filter-type').value;
        if (typeFilter) {
            filtered = filtered.filter(p => p.prospect_type === typeFilter);
        }

        // Status filter
        const statusFilter = document.getElementById('filter-status').value;
        if (statusFilter) {
            filtered = filtered.filter(p => p.status === statusFilter);
        }

        // Update stats
        updateStats(filtered);

        // Render
        const container = document.getElementById('prospect-list');
        const emptyState = document.getElementById('empty-state');

        if (filtered.length === 0) {
            container.innerHTML = '';
            container.appendChild(emptyState);
            emptyState.style.display = 'block';
            return;
        }

        emptyState.style.display = 'none';

        // Sort by created_at desc
        filtered.sort((a, b) => new Date(b.created_at) - new Date(a.created_at));

        let html = `<div class="d-flex justify-content-between align-items-center mb-3">
            <h6 class="fw-bold mb-0 text-dark" style="font-size:0.85rem;">Hasil</h6>
            <span class="text-muted" style="font-size:0.7rem;">${filtered.length} prospek</span>
        </div>`;

        filtered.forEach(p => {
            const typeClass = getTypeClass(p.prospect_type);
            const typeBadge = getTypeBadge(p.prospect_type);
            const statusBadge = getStatusBadge(p.status);
            const delegasiBadge = getDelegasiBadge(p.delegation_status);
            const timeAgo = formatTimeAgo(p.created_at);
            const nominal = p.estimated_amount ? formatRupiah(p.estimated_amount) : '-';

            html += `
            <a href="${BASE_APP}/prospek-detail/${p.id}" class="text-decoration-none">
                <div class="prospek-card ${typeClass}">
                    <div class="card-body p-3">
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <span class="badge-type ${typeBadge.cls}">${typeBadge.label}</span>
                            <div class="d-flex gap-1 align-items-center">
                                ${delegasiBadge}
                                ${statusBadge}
                            </div>
                        </div>
                        <div class="prospek-name">${p.customer_name}</div>
                        <div class="prospek-meta"><i class="fa-solid fa-box-open"></i> ${p.product_interest || '-'}</div>
                        <div class="prospek-meta"><i class="fa-solid fa-money-bill"></i> ${nominal}</div>
                        <div class="d-flex justify-content-between align-items-center mt-2 pt-2" style="border-top:1px solid #F1F5F9;">
                            <span class="prospek-meta mb-0"><i class="fa-solid fa-user-pen"></i> ${p.created_by_name || '-'}</span>
                            <span style="font-size:0.6rem; color:#94A3B8;">${timeAgo}</span>
                        </div>
                    </div>
                </div>
            </a>`;
        });

        container.innerHTML = html;
    }

    function updateStats(data) {
        const open = data.filter(p => p.status === 'OPEN').length;
        const fu = data.filter(p => p.status === 'FOLLOW_UP').length;
        const sla = data.filter(p => p.status === 'SLA').length;
        const closing = data.filter(p => p.status === 'CLOSING').length;

        document.getElementById('stat-open').textContent = open;
        document.getElementById('stat-followup').textContent = fu;
        document.getElementById('stat-sla').textContent = sla;
        document.getElementById('stat-closing').textContent = closing;
        document.getElementById('total-badge').textContent = data.length;
    }

    // =========================================
    // HELPERS
    // =========================================
    function getTypeClass(type) {
        const map = { KREDIT: 'type-kredit', TABUNGAN: 'type-tabungan', DEPOSITO: 'type-deposito', PEMBELI_ASET: 'type-aset', DEBITUR_EXISTING: 'type-existing' };
        return map[type] || '';
    }

    function getTypeBadge(type) {
        const map = {
            KREDIT: { cls: 'badge-kredit', label: '<i class="fa-solid fa-money-bill-transfer me-1"></i>Kredit' },
            TABUNGAN: { cls: 'badge-tabungan', label: '<i class="fa-solid fa-piggy-bank me-1"></i>Tabungan' },
            DEPOSITO: { cls: 'badge-deposito', label: '<i class="fa-solid fa-vault me-1"></i>Deposito' },
            PEMBELI_ASET: { cls: 'badge-aset', label: '<i class="fa-solid fa-building me-1"></i>Pembeli Aset' },
            DEBITUR_EXISTING: { cls: 'badge-existing', label: '<i class="fa-solid fa-user-check me-1"></i>Existing' },
        };
        return map[type] || { cls: '', label: type };
    }

    function getStatusBadge(status) {
        const map = {
            OPEN: { cls: 'status-open', label: 'Open' },
            FOLLOW_UP: { cls: 'status-follow_up', label: 'Follow Up' },
            SLA: { cls: 'status-sla', label: 'SLA' },
            CLOSING: { cls: 'status-closing', label: 'Closing' },
            REJECT: { cls: 'status-reject', label: 'Reject' },
        };
        const s = map[status] || { cls: '', label: status };
        return `<span class="badge-status ${s.cls}">${s.label}</span>`;
    }

    function getDelegasiBadge(status) {
        if (status === 'BELUM_DIDELEGASIKAN') {
            return `<span class="badge-delegasi delegasi-belum">Pending</span>`;
        }
        return '';
    }

    function formatTimeAgo(dateStr) {
        const now = new Date();
        const date = new Date(dateStr);
        const diff = Math.floor((now - date) / 1000);
        if (diff < 60) return 'Baru saja';
        if (diff < 3600) return Math.floor(diff/60) + ' mnt lalu';
        if (diff < 86400) return Math.floor(diff/3600) + ' jam lalu';
        if (diff < 604800) return Math.floor(diff/86400) + ' hari lalu';
        return date.toLocaleDateString('id-ID', { day: 'numeric', month: 'short' });
    }

    // =========================================
    // EVENT HANDLERS
    // =========================================
    window.filterBySource = function(source, btn) {
        currentSourceFilter = source;
        document.querySelectorAll('.filter-tab-btn').forEach(b => b.classList.remove('active'));
        btn.classList.add('active');
        renderList();
    };

    window.applyFilters = function() {
        renderList();
        // Close collapse
        const collapse = bootstrap.Collapse.getInstance(document.getElementById('collapseFilter'));
        if (collapse) collapse.hide();
    };

    // Live search
    document.getElementById('search-input').addEventListener('input', function() {
        renderList();
    });

    // Init
    loadProspects();
})();
</script>
