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
    .kelolaan-empty-card {
        background: #fff;
        border-radius: 14px;
        padding: 14px;
        box-shadow: 0 8px 22px rgba(15, 23, 42, .06);
    }
    .kelolaan-summary-label {
        font-size: .62rem;
        text-transform: uppercase;
        color: #64748B;
        font-weight: 800;
    }
    .kelolaan-summary-value {
        font-size: 1rem;
        color: #102A43;
        font-weight: 900;
        margin-top: 4px;
    }
    .kelolaan-filters,
    .kelolaan-list {
        margin: 0 16px 16px;
    }
    .kelolaan-filters {
        background: #fff;
        border-radius: 14px;
        padding: 14px;
        box-shadow: 0 4px 14px rgba(15,23,42,.04);
    }
    .kelolaan-filter-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 10px;
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
        line-height: 1.2;
        margin-bottom: 4px;
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
    .kelolaan-footer {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 8px;
        font-size: .68rem;
        color: #64748B;
    }
    .kelolaan-empty {
        margin: 0 16px 90px;
        text-align: center;
        color: #94A3B8;
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
        .kelolaan-filter-grid {
            grid-template-columns: 1.4fr repeat(5, minmax(0, 1fr));
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
        <div class="kelolaan-summary-label">Totung Sekarang</div>
        <div class="kelolaan-summary-value" id="sum-totung">Rp0</div>
    </div>
    <div class="kelolaan-summary-card">
        <div class="kelolaan-summary-label">Target Awal Bulan</div>
        <div class="kelolaan-summary-value" id="sum-target">Rp0</div>
    </div>
</div>

<div class="kelolaan-filters">
    <div class="kelolaan-filter-grid">
        <div>
            <label class="kelolaan-label">Cari Kelolaan</label>
            <input class="kelolaan-input" id="flt-search" placeholder="Nama nasabah / rekening / AO">
        </div>
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
        <div class="d-flex align-items-end">
            <button class="btn btn-primary w-100 fw-bold" style="border-radius:10px;padding:10px 12px;" onclick="loadKelolaanAoKredit()">
                <i class="fa-solid fa-magnifying-glass me-1"></i> Tampilkan
            </button>
        </div>
    </div>
</div>

<div class="kelolaan-list" id="kelolaan-list"></div>
<div class="kelolaan-empty" id="kelolaan-empty" style="display:none;">
    <div class="kelolaan-empty-card">
        <i class="fa-solid fa-folder-open d-block fs-1 mb-3"></i>
        <h6 class="fw-bold text-dark">Belum ada data kelolaan</h6>
        <p class="mb-0">Coba ubah tanggal closing, tanggal harian, cabang, atau AO kreditnya.</p>
    </div>
</div>

<script>
(function() {
    const BASE_APP = <?= json_encode(BASE_APP) ?>;
    const userKodeKantor = <?= json_encode($user_kode_kantor ?? '000') ?>;
    const isSuperuser = <?= $is_superuser ? 'true' : 'false' ?>;
    const isAoKredit = <?= $is_ao_kredit ? 'true' : 'false' ?>;
    const urlParams = new URLSearchParams(window.location.search);

    function formatRupiah(value) {
        return new Intl.NumberFormat('id-ID', {
            style: 'currency',
            currency: 'IDR',
            maximumFractionDigits: 0
        }).format(Number(value || 0));
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

    async function loadCabangOptions() {
        const select = document.getElementById('flt-kode-kantor');
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
                    select.appendChild(option);
                });
        } catch (e) {}

        if (urlParams.get('kode_kantor')) {
            select.value = urlParams.get('kode_kantor');
        } else if (userKodeKantor && userKodeKantor !== '000') {
            select.value = userKodeKantor;
        }

        if (!isSuperuser && !isAoKredit && userKodeKantor && userKodeKantor !== '000') {
            select.value = userKodeKantor;
            select.disabled = true;
        }
    }

    async function loadAoOptions() {
        const wrapper = document.getElementById('flt-ao-wrap');
        const select = document.getElementById('flt-ao-employee-id');
        const branchCode = document.getElementById('flt-kode-kantor').value || (userKodeKantor !== '000' ? userKodeKantor : '');

        if (isAoKredit && !isSuperuser) {
            wrapper.style.display = 'none';
            return;
        }

        wrapper.style.display = '';
        select.innerHTML = '<option value="">Semua AO Kredit</option>';

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
                select.appendChild(option);
            });

            if (urlParams.get('ao_employee_id')) {
                select.value = urlParams.get('ao_employee_id');
            }
        } catch (e) {}
    }

    window.loadKelolaanAoKredit = async function() {
        const params = new URLSearchParams({
            search: document.getElementById('flt-search').value.trim(),
            closing_date: document.getElementById('flt-closing-date').value,
            harian_date: document.getElementById('flt-harian-date').value,
            kode_kantor: document.getElementById('flt-kode-kantor').value,
            ao_employee_id: document.getElementById('flt-ao-employee-id').value,
            limit: '50'
        });

        const list = document.getElementById('kelolaan-list');
        const empty = document.getElementById('kelolaan-empty');
        list.innerHTML = '<div class="kelolaan-summary-card text-center text-muted">Memuat data kelolaan...</div>';
        empty.style.display = 'none';

        try {
            const res = await fetch(BASE_APP + '/api/?action=ao_credit_portfolio_list&' + params.toString(), {credentials: 'include'});
            const body = await res.json();
            if (body.status !== 200) {
                throw new Error(body.message || 'Gagal memuat data');
            }

            const rows = body.data?.items || [];
            const summary = body.data?.summary || {};
            document.getElementById('sum-noa').textContent = summary.total_noa || 0;
            document.getElementById('sum-bd').textContent = formatRupiah(summary.total_bd_closing || 0);
            document.getElementById('sum-totung').textContent = formatRupiah(summary.total_totung_skrg || 0);
            document.getElementById('sum-target').textContent = formatRupiah(Number(summary.total_target_pokok || 0) + Number(summary.total_target_bunga || 0));

            if (!rows.length) {
                list.innerHTML = '';
                empty.style.display = 'block';
                return;
            }

            list.innerHTML = rows.map(function(row) {
                const detailQuery = new URLSearchParams({
                    closing_date: params.get('closing_date') || '',
                    harian_date: params.get('harian_date') || '',
                    kode_kantor: params.get('kode_kantor') || '',
                    ao_employee_id: params.get('ao_employee_id') || '',
                    search: params.get('search') || ''
                }).toString();

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
                                <div class="kelolaan-stat-label">Totung Sekarang</div>
                                <div class="kelolaan-stat-value money">${formatRupiah(row.totung_skrg || 0)}</div>
                            </div>
                            <div class="kelolaan-stat">
                                <div class="kelolaan-stat-label">DPD</div>
                                <div class="kelolaan-stat-value">${escapeHtml(String(row.dpd_closing ?? 0))} hari</div>
                            </div>
                            <div class="kelolaan-stat">
                                <div class="kelolaan-stat-label">Target Awal Bulan</div>
                                <div class="kelolaan-stat-value money">${formatRupiah(Number(row.target_pokok_awal_bulan || 0) + Number(row.target_bunga_awal_bulan || 0))}</div>
                            </div>
                        </div>
                        <div class="kelolaan-footer">
                            <span>${escapeHtml((row.kode_kantor || '') + (row.branch_name ? ' - ' + row.branch_name : ''))}</span>
                            <span>${escapeHtml(row.status_bayar_jt || '-')}</span>
                        </div>
                    </a>
                `;
            }).join('');
        } catch (e) {
            list.innerHTML = '';
            empty.style.display = 'block';
        }
    };

    document.getElementById('flt-search').addEventListener('keydown', function(e) {
        if (e.key === 'Enter') {
            e.preventDefault();
            loadKelolaanAoKredit();
        }
    });

    document.getElementById('flt-kode-kantor').addEventListener('change', function() {
        loadAoOptions();
    });

    document.getElementById('flt-search').value = urlParams.get('search') || '';
    document.getElementById('flt-closing-date').value = urlParams.get('closing_date') || document.getElementById('flt-closing-date').value;
    document.getElementById('flt-harian-date').value = urlParams.get('harian_date') || document.getElementById('flt-harian-date').value;

    loadCabangOptions()
        .then(loadAoOptions)
        .then(loadKelolaanAoKredit);
})();
</script>
