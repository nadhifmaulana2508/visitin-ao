<?php
$role = $user_role ?? 'staff';
$perms = $user_permissions ?? [];
$is_developer = ($role === 'developer');
$is_superuser = in_array($role, ['superuser', 'developer'], true);
$is_ao_kredit = in_array('AO_KREDIT', $perms, true) || $is_developer;
$can_access_kelolaan = $is_superuser || $is_ao_kredit;
?>

<style>
    .kelolaan-header {
        background: linear-gradient(135deg, var(--color-primary), var(--color-secondary));
        color: #fff;
        padding: 26px 20px 54px;
        border-bottom-left-radius: 26px;
        border-bottom-right-radius: 26px;
    }
    .kelolaan-summary {
        margin: -34px 16px 14px;
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 10px;
        position: relative;
        z-index: 10;
    }
    .kelolaan-summary-card,
    .kelolaan-empty-card,
    .kelolaan-desktop-table-wrap {
        background: #fff;
        border-radius: 14px;
        box-shadow: 0 8px 22px rgba(15, 23, 42, .06);
    }
    .kelolaan-summary-card {
        padding: 14px;
    }
    .kelolaan-summary-label {
        font-size: .62rem;
        text-transform: uppercase;
        color: #64748B;
        font-weight: 800;
        margin-bottom: 3px;
    }
    .kelolaan-summary-value {
        font-size: 1rem;
        color: #102A43;
        font-weight: 900;
    }
    .kelolaan-summary-note {
        margin-top: 4px;
        font-size: .68rem;
        color: #64748B;
        font-weight: 700;
    }
    .kelolaan-filters,
    .kelolaan-list,
    .kelolaan-empty {
        margin: 0 16px 16px;
    }
    .kelolaan-filters {
        background: #fff;
        border-radius: 14px;
        padding: 14px;
        box-shadow: 0 4px 14px rgba(15,23,42,.04);
    }
    .kelolaan-search-row {
        display: grid;
        grid-template-columns: 1fr;
        gap: 10px;
    }
    .kelolaan-filter-toggle {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 10px;
        margin-top: 12px;
        padding-top: 12px;
        border-top: 1px solid #E2E8F0;
        background: transparent;
        border-left: 0;
        border-right: 0;
        border-bottom: 0;
        width: 100%;
        color: #102A43;
        font-size: .84rem;
        font-weight: 800;
        text-align: left;
    }
    .kelolaan-filter-toggle .right {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        color: #64748B;
        font-size: .72rem;
        font-weight: 700;
    }
    .kelolaan-filter-grid {
        display: none;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 10px;
        margin-top: 12px;
    }
    .kelolaan-filter-grid.is-open {
        display: grid;
    }
    .kelolaan-label {
        font-size: .62rem;
        font-weight: 800;
        color: #64748B;
        text-transform: uppercase;
        margin-bottom: 5px;
        display: block;
    }
    .kelolaan-input {
        width: 100%;
        border: 1px solid #CBD5E1;
        border-radius: 10px;
        background: #fff;
        padding: 10px 12px;
        font-size: .82rem;
        font-weight: 600;
        color: #1E293B;
    }
    .kelolaan-card {
        display: block;
        text-decoration: none;
        color: inherit;
        background: #fff;
        border-radius: 16px;
        padding: 14px;
        margin-bottom: 12px;
        box-shadow: 0 4px 14px rgba(15,23,42,.04);
        border-left: 4px solid #1D4ED8;
    }
    .kelolaan-top {
        display: flex;
        justify-content: space-between;
        gap: 10px;
        align-items: flex-start;
        margin-bottom: 10px;
    }
    .kelolaan-name {
        font-size: .9rem;
        font-weight: 900;
        color: #102A43;
        line-height: 1.25;
        margin-bottom: 4px;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }
    .kelolaan-meta {
        font-size: .7rem;
        color: #64748B;
        display: flex;
        align-items: center;
        gap: 6px;
        margin-bottom: 2px;
    }
    .kelolaan-chip {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        border-radius: 999px;
        padding: 4px 8px;
        font-size: .6rem;
        font-weight: 800;
        white-space: nowrap;
    }
    .chip-flow { background: #FEE2E2; color: #B91C1C; }
    .chip-stay { background: #FEF3C7; color: #B45309; }
    .chip-improved { background: #DCFCE7; color: #15803D; }
    .chip-btc { background: #DBEAFE; color: #1D4ED8; }
    .kelolaan-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 10px;
        margin-bottom: 10px;
    }
    .kelolaan-stat {
        background: #F8FAFC;
        border: 1px solid #E2E8F0;
        border-radius: 12px;
        padding: 10px 12px;
    }
    .kelolaan-stat-label {
        font-size: .58rem;
        color: #94A3B8;
        font-weight: 800;
        text-transform: uppercase;
    }
    .kelolaan-stat-value {
        margin-top: 3px;
        font-size: .78rem;
        font-weight: 900;
        color: #0F172A;
    }
    .kelolaan-stat-value.money { color: #0F766E; }
    .kelolaan-card-runoff {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 10px;
        margin-bottom: 10px;
        padding: 10px 12px;
        border-radius: 12px;
        background: #EFF6FF;
        border: 1px solid #DBEAFE;
    }
    .kelolaan-card-runoff .label {
        font-size: .62rem;
        text-transform: uppercase;
        color: #64748B;
        font-weight: 800;
    }
    .kelolaan-card-runoff .value {
        font-size: .85rem;
        color: #1D4ED8;
        font-weight: 900;
        text-align: right;
    }
    .kelolaan-footer {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 8px;
        font-size: .68rem;
        color: #64748B;
    }
    .kelolaan-empty {
        text-align: center;
        color: #94A3B8;
        margin-bottom: 90px;
    }
    .kelolaan-empty-card {
        padding: 26px 18px;
    }
    .kelolaan-loading {
        padding: 18px;
        text-align: center;
        color: #64748B;
        font-weight: 700;
    }
    .kelolaan-desktop-table-wrap {
        display: none;
        overflow: hidden;
    }
    .kelolaan-table {
        width: 100%;
        border-collapse: collapse;
        font-size: .8rem;
    }
    .kelolaan-table th,
    .kelolaan-table td {
        padding: 12px 14px;
        border-bottom: 1px solid #EEF2F7;
        vertical-align: top;
    }
    .kelolaan-table thead th {
        background: #F8FAFC;
        color: #475569;
        font-size: .68rem;
        text-transform: uppercase;
        font-weight: 800;
        white-space: nowrap;
    }
    .kelolaan-table tbody tr:hover {
        background: #FAFCFF;
    }
    .kelolaan-table .name {
        font-weight: 900;
        color: #102A43;
        line-height: 1.25;
    }
    .kelolaan-table .sub {
        margin-top: 3px;
        color: #64748B;
        font-size: .72rem;
    }
    .kelolaan-table .num {
        text-align: right;
        white-space: nowrap;
        font-weight: 800;
    }
    .kelolaan-table .money {
        color: #0F766E;
    }
    .kelolaan-table .action {
        text-align: center;
        white-space: nowrap;
    }
    .kelolaan-table-link {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 6px;
        padding: 8px 12px;
        border-radius: 10px;
        background: #E6EDF5;
        color: #102A43;
        font-weight: 800;
        text-decoration: none;
        font-size: .74rem;
    }
    @media (min-width: 768px) {
        .kelolaan-summary,
        .kelolaan-filters,
        .kelolaan-list,
        .kelolaan-empty {
            margin-left: 24px;
            margin-right: 24px;
        }
        .kelolaan-summary {
            grid-template-columns: repeat(4, minmax(0, 1fr));
        }
        .kelolaan-search-row {
            grid-template-columns: 1.3fr auto;
            align-items: end;
        }
        .kelolaan-filter-grid.is-open {
            grid-template-columns: repeat(4, minmax(0, 1fr));
        }
    }
    @media (min-width: 1100px) {
        .kelolaan-mobile-cards {
            display: none;
        }
        .kelolaan-desktop-table-wrap {
            display: block;
        }
    }
</style>

<div class="kelolaan-header">
    <div class="d-flex align-items-center mb-1">
        <a href="<?= BASE_APP ?>/home" class="text-white me-3"><i class="fa-solid fa-arrow-left fs-5"></i></a>
        <div>
            <h5 class="fw-bold mb-0">Kelolaan AO Kredit</h5>
            <p class="small text-white-50 mb-0" style="font-size:.72rem;">
                <?= $is_ao_kredit && !$is_superuser ? 'Kelolaan sesuai AO login' : 'Kelolaan AO Kredit sesuai cabang dan AO' ?>
            </p>
        </div>
    </div>
</div>

<?php if (!$can_access_kelolaan): ?>
<div class="kelolaan-empty">
    <div class="kelolaan-empty-card">
        <i class="fa-solid fa-lock d-block fs-1 mb-3"></i>
        <h6 class="fw-bold text-dark">Akses tidak tersedia</h6>
        <p class="mb-0">Menu ini khusus AO Kredit, Superuser, atau Developer.</p>
    </div>
</div>
<?php return; endif; ?>

<div class="kelolaan-summary">
    <div class="kelolaan-summary-card">
        <div class="kelolaan-summary-label">Total NOA</div>
        <div class="kelolaan-summary-value" id="sum-noa">0</div>
    </div>
    <div class="kelolaan-summary-card">
        <div class="kelolaan-summary-label">Baki Debet</div>
        <div class="kelolaan-summary-value" id="sum-bd">Rp0</div>
    </div>
    <div class="kelolaan-summary-card">
        <div class="kelolaan-summary-label">Pipeline Pokok</div>
        <div class="kelolaan-summary-value" id="sum-target-pokok">Rp0</div>
    </div>
    <div class="kelolaan-summary-card">
        <div class="kelolaan-summary-label">Run Off Pokok</div>
        <div class="kelolaan-summary-value" id="sum-runoff">Rp0</div>
        <div class="kelolaan-summary-note" id="sum-runoff-ratio">0%</div>
    </div>
</div>

<div class="kelolaan-filters">
    <div class="kelolaan-search-row">
        <div>
            <label class="kelolaan-label">Cari Kelolaan</label>
            <input class="kelolaan-input" id="flt-search" placeholder="Nama nasabah / rekening / AO">
        </div>
        <div class="text-end">
            <div class="small text-muted fw-semibold" id="filter-status-text">Filter otomatis aktif</div>
        </div>
    </div>

    <button class="kelolaan-filter-toggle" type="button" id="toggle-advanced-filter" aria-expanded="false">
        <span><i class="fa-solid fa-sliders me-2"></i>Filter Lanjutan</span>
        <span class="right"><span id="filter-active-text">Tertutup</span><i class="fa-solid fa-chevron-down" id="filter-toggle-icon"></i></span>
    </button>

    <div class="kelolaan-filter-grid" id="advanced-filter-panel">
        <div>
            <label class="kelolaan-label">Closing</label>
            <input class="kelolaan-input" id="flt-closing-date" type="date" value="<?= date('Y-m-t', strtotime('last month')) ?>">
        </div>
        <div>
            <label class="kelolaan-label">Harian</label>
            <input class="kelolaan-input" id="flt-harian-date" type="date" value="<?= date('Y-m-d') ?>">
        </div>
        <div>
            <label class="kelolaan-label">Cabang</label>
            <select class="kelolaan-input" id="flt-kode-kantor">
                <option value="">Semua cabang</option>
            </select>
        </div>
        <div id="flt-ao-wrap">
            <label class="kelolaan-label">AO Kredit</label>
            <select class="kelolaan-input" id="flt-ao-employee-id">
                <option value="">Semua AO Kredit</option>
            </select>
        </div>
    </div>
</div>

<div class="kelolaan-list">
    <div class="kelolaan-mobile-cards" id="kelolaan-mobile-cards"></div>
    <div class="kelolaan-desktop-table-wrap" id="kelolaan-desktop-table-wrap"></div>
</div>

<div class="kelolaan-empty" id="kelolaan-empty" style="display:none;">
    <div class="kelolaan-empty-card">
        <i class="fa-solid fa-folder-open d-block fs-1 mb-3"></i>
        <h6 class="fw-bold text-dark">Belum ada data kelolaan</h6>
        <p class="mb-0">Coba ubah filter, tanggal closing, tanggal harian, cabang, atau AO kreditnya.</p>
    </div>
</div>

<script>
(function() {
    const BASE_APP = <?= json_encode(BASE_APP) ?>;
    const userKodeKantor = <?= json_encode($user_kode_kantor ?? '000') ?>;
    const isSuperuser = <?= $is_superuser ? 'true' : 'false' ?>;
    const isAoKredit = <?= $is_ao_kredit ? 'true' : 'false' ?>;
    const urlParams = new URLSearchParams(window.location.search);

    const searchInput = document.getElementById('flt-search');
    const closingInput = document.getElementById('flt-closing-date');
    const harianInput = document.getElementById('flt-harian-date');
    const branchSelect = document.getElementById('flt-kode-kantor');
    const aoSelect = document.getElementById('flt-ao-employee-id');
    const aoWrap = document.getElementById('flt-ao-wrap');
    const advancedPanel = document.getElementById('advanced-filter-panel');
    const toggleButton = document.getElementById('toggle-advanced-filter');
    const toggleIcon = document.getElementById('filter-toggle-icon');
    const filterActiveText = document.getElementById('filter-active-text');
    const filterStatusText = document.getElementById('filter-status-text');
    const mobileCards = document.getElementById('kelolaan-mobile-cards');
    const desktopTableWrap = document.getElementById('kelolaan-desktop-table-wrap');
    const emptyState = document.getElementById('kelolaan-empty');

    let autoFilterTimer = null;
    let isAdvancedOpen = false;

    function formatRupiah(value) {
        return new Intl.NumberFormat('id-ID', {
            style: 'currency',
            currency: 'IDR',
            maximumFractionDigits: 0
        }).format(Number(value || 0));
    }

    function formatPercent(value) {
        return new Intl.NumberFormat('id-ID', {
            minimumFractionDigits: 0,
            maximumFractionDigits: 1
        }).format(Number(value || 0)) + '%';
    }

    function escapeHtml(value) {
        return String(value ?? '').replace(/[&<>\"']/g, function(ch) {
            return {'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;', "'":'&#039;'}[ch];
        });
    }

    function chipClass(status) {
        const classes = {
            'FLOW (Memburuk)': 'chip-flow',
            'STAY': 'chip-stay',
            'IMPROVED (Membaik)': 'chip-improved',
            'BTC (Back to Current)': 'chip-btc',
            'LUNAS': 'chip-btc'
        };
        return classes[status] || 'chip-stay';
    }

    function buildDetailQuery(params) {
        return new URLSearchParams({
            closing_date: params.get('closing_date') || '',
            harian_date: params.get('harian_date') || '',
            kode_kantor: params.get('kode_kantor') || '',
            ao_employee_id: params.get('ao_employee_id') || '',
            search: params.get('search') || ''
        }).toString();
    }

    function updateFilterPanelState() {
        advancedPanel.classList.toggle('is-open', isAdvancedOpen);
        toggleButton.setAttribute('aria-expanded', isAdvancedOpen ? 'true' : 'false');
        toggleIcon.className = isAdvancedOpen ? 'fa-solid fa-chevron-up' : 'fa-solid fa-chevron-down';
        filterActiveText.textContent = isAdvancedOpen ? 'Terbuka' : 'Tertutup';
    }

    function updateFilterStatusText(text) {
        filterStatusText.textContent = text;
    }

    function setLoadingState() {
        const loadingHtml = '<div class="kelolaan-loading"><i class="fa-solid fa-spinner fa-spin me-2"></i>Memuat data kelolaan...</div>';
        mobileCards.innerHTML = loadingHtml;
        desktopTableWrap.innerHTML = loadingHtml;
        emptyState.style.display = 'none';
        updateFilterStatusText('Memuat data...');
    }

    async function loadCabangOptions() {
        try {
            const res = await fetch(BASE_APP + '/api/?action=master_kode_kantor', {credentials: 'include'});
            const body = await res.json();
            const rows = body.status === 200 ? (body.data?.all || []) : [];

            rows
                .filter(function(row) { return row.kode_kantor !== '000'; })
                .forEach(function(row) {
                    const option = document.createElement('option');
                    option.value = row.kode_kantor;
                    option.textContent = row.kode_kantor + ' - ' + row.nama_kantor;
                    branchSelect.appendChild(option);
                });
        } catch (e) {}

        if (urlParams.get('kode_kantor')) {
            branchSelect.value = urlParams.get('kode_kantor');
        } else if (userKodeKantor && userKodeKantor !== '000') {
            branchSelect.value = userKodeKantor;
        }
    }

    async function loadAoOptions() {
        const branchCode = branchSelect.value || (userKodeKantor !== '000' ? userKodeKantor : '');

        if (isAoKredit && !isSuperuser) {
            aoWrap.style.display = 'none';
            return;
        }

        aoWrap.style.display = '';
        aoSelect.innerHTML = '<option value="">Semua AO Kredit</option>';

        if (!branchCode) {
            return;
        }

        try {
            const params = new URLSearchParams({
                action: 'master_pegawai_ao',
                kode_kantor: branchCode,
                group_jabatan: 'AO Kredit'
            });
            const res = await fetch(BASE_APP + '/api/?' + params.toString(), {credentials: 'include'});
            const body = await res.json();
            const rows = body.status === 200 ? (body.data || []) : [];

            rows.forEach(function(row) {
                const option = document.createElement('option');
                option.value = row.employee_id || '';
                option.textContent = (row.full_name || '-') + ' (' + (row.employee_id || '-') + ')';
                aoSelect.appendChild(option);
            });

            if (urlParams.get('ao_employee_id')) {
                aoSelect.value = urlParams.get('ao_employee_id');
            }
        } catch (e) {}
    }

    function renderCards(rows, params) {
        const detailQuery = buildDetailQuery(params);
        mobileCards.innerHTML = rows.map(function(row) {
            return `
                <a href="${BASE_APP}/kelolaan-ao-kredit-detail/${encodeURIComponent(row.no_rekening)}?${detailQuery}" class="kelolaan-card">
                    <div class="kelolaan-top">
                        <div style="min-width:0;">
                            <div class="kelolaan-name">${escapeHtml(row.nama_nasabah || '-')}</div>
                            <div class="kelolaan-meta"><i class="fa-solid fa-credit-card"></i><span>${escapeHtml(row.no_rekening || '-')}</span></div>
                            <div class="kelolaan-meta"><i class="fa-solid fa-user-tie"></i><span>${escapeHtml(row.nama_ao || '-')}</span></div>
                        </div>
                        <span class="kelolaan-chip ${chipClass(row.pergerakan_status)}">${escapeHtml(row.pergerakan_status || '-')}</span>
                    </div>
                    <div class="kelolaan-grid">
                        <div class="kelolaan-stat">
                            <div class="kelolaan-stat-label">Baki Debet</div>
                            <div class="kelolaan-stat-value money">${formatRupiah(row.bd_closing || 0)}</div>
                        </div>
                        <div class="kelolaan-stat">
                            <div class="kelolaan-stat-label">Totung</div>
                            <div class="kelolaan-stat-value money">${formatRupiah(row.totung_skrg || 0)}</div>
                        </div>
                        <div class="kelolaan-stat">
                            <div class="kelolaan-stat-label">DPD</div>
                            <div class="kelolaan-stat-value">${escapeHtml(String(row.dpd_closing ?? 0))} hari</div>
                        </div>
                        <div class="kelolaan-stat">
                            <div class="kelolaan-stat-label">Pipeline Pokok</div>
                            <div class="kelolaan-stat-value money">${formatRupiah(row.target_pokok_awal_bulan || 0)}</div>
                        </div>
                    </div>
                    <div class="kelolaan-card-runoff">
                        <div>
                            <div class="label">Run Off Pokok</div>
                            <div class="kelolaan-summary-note">${escapeHtml(row.status_bayar_jt || '-')}</div>
                        </div>
                        <div class="value">${formatRupiah(row.pokok_skrg || 0)}<br><span style="font-size:.72rem;color:#64748B;">${formatPercent(row.run_off_percent || 0)}</span></div>
                    </div>
                    <div class="kelolaan-footer">
                        <span>${escapeHtml((row.kode_kantor || '') + (row.branch_name ? ' - ' + row.branch_name : ''))}</span>
                        <span>${escapeHtml(row.bucket_sekarang || '-')}</span>
                    </div>
                </a>
            `;
        }).join('');
    }

    function renderDesktopTable(rows, params) {
        const detailQuery = buildDetailQuery(params);
        desktopTableWrap.innerHTML = `
            <table class="kelolaan-table">
                <thead>
                    <tr>
                        <th>Nasabah</th>
                        <th>AO</th>
                        <th>Cabang</th>
                        <th>DPD</th>
                        <th>Pergerakan</th>
                        <th class="num">Baki Debet</th>
                        <th class="num">Totung</th>
                        <th class="num">Pipeline Pokok</th>
                        <th class="num">Run Off Pokok</th>
                        <th class="num">Rasio</th>
                        <th class="action">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    ${rows.map(function(row) {
                        return `
                            <tr>
                                <td>
                                    <div class="name">${escapeHtml(row.nama_nasabah || '-')}</div>
                                    <div class="sub">${escapeHtml(row.no_rekening || '-')}</div>
                                </td>
                                <td>${escapeHtml(row.nama_ao || '-')}</td>
                                <td>${escapeHtml((row.kode_kantor || '') + (row.branch_name ? ' - ' + row.branch_name : ''))}</td>
                                <td>${escapeHtml(String(row.dpd_closing ?? 0))} hari</td>
                                <td><span class="kelolaan-chip ${chipClass(row.pergerakan_status)}">${escapeHtml(row.pergerakan_status || '-')}</span></td>
                                <td class="num money">${formatRupiah(row.bd_closing || 0)}</td>
                                <td class="num money">${formatRupiah(row.totung_skrg || 0)}</td>
                                <td class="num money">${formatRupiah(row.target_pokok_awal_bulan || 0)}</td>
                                <td class="num money">${formatRupiah(row.pokok_skrg || 0)}</td>
                                <td class="num">${formatPercent(row.run_off_percent || 0)}</td>
                                <td class="action">
                                    <a class="kelolaan-table-link" href="${BASE_APP}/kelolaan-ao-kredit-detail/${encodeURIComponent(row.no_rekening)}?${detailQuery}">
                                        <i class="fa-solid fa-eye"></i> Detail
                                    </a>
                                </td>
                            </tr>
                        `;
                    }).join('')}
                </tbody>
            </table>
        `;
    }

    function renderSummary(summary) {
        document.getElementById('sum-noa').textContent = summary.total_noa || 0;
        document.getElementById('sum-bd').textContent = formatRupiah(summary.total_bd_closing || 0);
        document.getElementById('sum-target-pokok').textContent = formatRupiah(summary.total_target_pokok || 0);
        document.getElementById('sum-runoff').textContent = formatRupiah(summary.total_run_off_pokok || 0);
        document.getElementById('sum-runoff-ratio').textContent = formatPercent(summary.run_off_percent || 0);
    }

    function syncQueryToUrl(params) {
        const clean = new URLSearchParams();
        ['search', 'closing_date', 'harian_date', 'kode_kantor', 'ao_employee_id'].forEach(function(key) {
            const value = params.get(key);
            if (value) {
                clean.set(key, value);
            }
        });
        const nextUrl = BASE_APP + '/kelolaan-ao-kredit' + (clean.toString() ? '?' + clean.toString() : '');
        window.history.replaceState({}, '', nextUrl);
    }

    async function loadKelolaanAoKredit() {
        const params = new URLSearchParams({
            search: searchInput.value.trim(),
            closing_date: closingInput.value,
            harian_date: harianInput.value,
            kode_kantor: branchSelect.value,
            ao_employee_id: aoSelect.value,
            limit: '50'
        });

        setLoadingState();
        syncQueryToUrl(params);

        try {
            const res = await fetch(BASE_APP + '/api/?action=ao_credit_portfolio_list&' + params.toString(), {credentials: 'include'});
            const body = await res.json();
            if (body.status !== 200) {
                throw new Error(body.message || 'Gagal memuat data');
            }

            const rows = body.data?.items || [];
            const summary = body.data?.summary || {};
            renderSummary(summary);

            if (!rows.length) {
                mobileCards.innerHTML = '';
                desktopTableWrap.innerHTML = '';
                emptyState.style.display = 'block';
                updateFilterStatusText('Tidak ada data untuk filter ini');
                return;
            }

            emptyState.style.display = 'none';
            renderCards(rows, params);
            renderDesktopTable(rows, params);
            updateFilterStatusText('Filter otomatis aktif');
        } catch (e) {
            mobileCards.innerHTML = '';
            desktopTableWrap.innerHTML = '';
            emptyState.style.display = 'block';
            updateFilterStatusText('Gagal memuat data');
        }
    }

    function triggerAutoFilter() {
        clearTimeout(autoFilterTimer);
        updateFilterStatusText('Menyiapkan filter...');
        autoFilterTimer = setTimeout(function() {
            loadKelolaanAoKredit();
        }, 350);
    }

    function initAutoBindings() {
        searchInput.addEventListener('input', triggerAutoFilter);
        closingInput.addEventListener('change', triggerAutoFilter);
        harianInput.addEventListener('change', triggerAutoFilter);
        branchSelect.addEventListener('change', async function() {
            await loadAoOptions();
            triggerAutoFilter();
        });
        aoSelect.addEventListener('change', triggerAutoFilter);
    }

    toggleButton.addEventListener('click', function() {
        isAdvancedOpen = !isAdvancedOpen;
        updateFilterPanelState();
    });

    searchInput.value = urlParams.get('search') || '';
    closingInput.value = urlParams.get('closing_date') || closingInput.value;
    harianInput.value = urlParams.get('harian_date') || harianInput.value;
    isAdvancedOpen = !!(urlParams.get('closing_date') || urlParams.get('harian_date') || urlParams.get('kode_kantor') || urlParams.get('ao_employee_id'));
    updateFilterPanelState();

    initAutoBindings();

    loadCabangOptions()
        .then(loadAoOptions)
        .then(loadKelolaanAoKredit);
})();
</script>
