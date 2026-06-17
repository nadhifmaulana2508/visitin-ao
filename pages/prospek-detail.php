<?php
$is_ao = in_array($user_role, ['ao_kredit', 'ao_dana', 'ao_remedial', 'developer']);
$is_superuser = in_array($user_role, ['superuser', 'developer']);
$prospect_id = $_GET['id'] ?? null;
?>

<style>
    .header-compact {
        background: linear-gradient(135deg, var(--color-primary), var(--color-secondary));
        color: white; padding: 25px 20px 50px 20px;
        border-bottom-left-radius: 25px; border-bottom-right-radius: 25px;
    }
    .detail-container { margin: -30px 15px 20px 15px; position: relative; z-index: 10; }
    .detail-card {
        background: #ffffff; border-radius: 16px; padding: 20px;
        box-shadow: 0 4px 15px rgba(0,0,0,0.04); margin-bottom: 15px;
    }
    .section-title {
        font-size: 0.75rem; font-weight: 800; color: var(--color-primary);
        text-transform: uppercase; margin-bottom: 12px;
        border-bottom: 2px solid #F4F7F6; padding-bottom: 8px;
    }
</style>

<style>
    .info-row { display: flex; justify-content: space-between; padding: 8px 0; border-bottom: 1px solid #F4F7F6; }
    .info-row:last-child { border-bottom: none; }
    .info-label { font-size: 0.7rem; font-weight: 700; color: #64748B; text-transform: uppercase; }
    .info-value { font-size: 0.85rem; font-weight: 700; color: #1E293B; text-align: right; max-width: 60%; }

    .badge-type-lg { font-size: 0.7rem; padding: 6px 12px; border-radius: 8px; font-weight: 700; }
    .badge-status-lg { font-size: 0.7rem; padding: 6px 12px; border-radius: 8px; font-weight: 700; }

    .badge-kredit { background: #E3F2FD; color: #1565C0; }
    .badge-tabungan { background: #E8F5E9; color: #2E7D32; }
    .badge-deposito { background: #F3E5F5; color: #6A1B9A; }
    .badge-aset { background: #FFF3E0; color: #E65100; }
    .badge-existing { background: #E0F7FA; color: #006064; }

    .status-open { background: #FFF9C4; color: #F57F17; }
    .status-follow_up { background: #E3F2FD; color: #1565C0; }
    .status-sla { background: #E8F5E9; color: #2E7D32; }
    .status-closing { background: #E8F5E9; color: #1B5E20; }
    .status-reject { background: #FFEBEE; color: #C62828; }

    .timeline { position: relative; padding-left: 20px; list-style: none; margin: 0; padding-top: 5px; }
    .timeline::before { content:''; position:absolute; left:6px; top:10px; bottom:10px; width:2px; background:#E2E8F0; }
    .timeline-item { position: relative; margin-bottom: 18px; }
    .timeline-item:last-child { margin-bottom: 0; }
    .timeline-dot {
        position: absolute; left: -18px; top: 4px; width: 10px; height: 10px;
        border-radius: 50%; background: var(--color-accent); border: 2px solid white;
        box-shadow: 0 0 0 2px #FFE5E5;
    }
    .timeline-content { font-size: 0.8rem; color: #475569; }
    .timeline-date { font-size: 0.65rem; color: #94A3B8; font-weight: 600; }

    .btn-action-detail {
        border-radius: 12px; font-weight: 700; font-size: 0.85rem; padding: 12px;
        width: 100%; border: none; transition: 0.2s; margin-bottom: 10px;
    }
    .btn-action-detail:active { transform: scale(0.98); }
    .btn-follow-up { background: #E3F2FD; color: #1565C0; }
    .btn-sla { background: #E8F5E9; color: #2E7D32; }
    .btn-closing { background: var(--color-accent); color: white; box-shadow: 0 4px 12px rgba(255,123,84,0.3); }
    .btn-reject { background: #FFEBEE; color: #C62828; }
    .btn-delegasi { background: var(--color-primary); color: white; box-shadow: 0 4px 12px rgba(10,25,49,0.2); }
</style>


<div class="header-compact">
    <div class="d-flex align-items-center mb-1">
        <a href="<?= BASE_APP ?>/daftar-prospek" class="text-white me-3"><i class="fa-solid fa-arrow-left fs-5"></i></a>
        <div>
            <h5 class="fw-bold mb-0">Detail Prospek</h5>
            <p class="small text-white-50 mb-0" style="font-size:0.7rem;">ID: #<span id="detail-id"><?= $prospect_id ?></span></p>
        </div>
    </div>
</div>

<div class="detail-container">
    <!-- Status & Type Header -->
    <div class="detail-card" id="card-header">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <span class="badge-type-lg" id="badge-type">Loading...</span>
            <span class="badge-status-lg" id="badge-status">...</span>
        </div>
        <h5 class="fw-bold text-dark mb-1" id="detail-name">-</h5>
        <p class="small text-muted mb-0" id="detail-product"><i class="fa-solid fa-box-open me-1"></i> -</p>
    </div>

    <!-- Info Nasabah -->
    <div class="detail-card">
        <h6 class="section-title"><i class="fa-solid fa-user me-2 text-accent"></i>Informasi Nasabah</h6>
        <div class="info-row"><span class="info-label">Nama</span><span class="info-value" id="info-name">-</span></div>
        <div class="info-row"><span class="info-label">No. HP</span><span class="info-value" id="info-phone">-</span></div>
        <div class="info-row"><span class="info-label">No. Identitas</span><span class="info-value" id="info-identity">-</span></div>
        <div class="info-row"><span class="info-label">Estimasi Nominal</span><span class="info-value text-accent" id="info-nominal">-</span></div>
        <div class="info-row"><span class="info-label">Alamat</span><span class="info-value" id="info-address" style="font-size:0.75rem;">-</span></div>
    </div>

    <!-- Info Proses -->
    <div class="detail-card">
        <h6 class="section-title"><i class="fa-solid fa-gears me-2 text-accent"></i>Informasi Proses</h6>
        <div class="info-row"><span class="info-label">Diinput Oleh</span><span class="info-value" id="info-created-by">-</span></div>
        <div class="info-row"><span class="info-label">Tanggal Input</span><span class="info-value" id="info-created-at">-</span></div>
        <div class="info-row"><span class="info-label">Status Delegasi</span><span class="info-value" id="info-delegasi">-</span></div>
        <div class="info-row"><span class="info-label">AO Pengelola</span><span class="info-value" id="info-ao">-</span></div>
        <div class="info-row"><span class="info-label">Sumber</span><span class="info-value" id="info-source">-</span></div>
    </div>


    <!-- Keterangan -->
    <div class="detail-card">
        <h6 class="section-title"><i class="fa-solid fa-note-sticky me-2 text-accent"></i>Keterangan</h6>
        <p class="small text-muted mb-0" id="info-description" style="font-style:italic;">-</p>
    </div>

    <!-- History / Timeline -->
    <div class="detail-card">
        <h6 class="section-title"><i class="fa-solid fa-timeline me-2 text-accent"></i>Riwayat Aktivitas</h6>
        <ul class="timeline" id="timeline-list">
            <li class="timeline-item">
                <div class="timeline-dot"></div>
                <div class="timeline-date">Memuat...</div>
            </li>
        </ul>
    </div>

    <!-- Action Buttons (role-based) -->
    <div id="action-buttons">
        <!-- Diisi oleh JS berdasarkan role & status -->
    </div>
</div>


<!-- Modal Follow Up -->
<div class="modal fade" id="modalFollowUp" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered" style="margin:15px;">
        <div class="modal-content" style="border-radius:16px;">
            <div class="modal-header border-0 pb-0">
                <h6 class="modal-title fw-bold"><i class="fa-solid fa-phone-volume text-primary me-2"></i>Input Follow Up</h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="form-followup">
                    <div class="mb-3">
                        <label class="form-label small fw-bold text-muted">Metode</label>
                        <select class="form-select" name="method" required style="border-radius:10px;">
                            <option value="TELEPON">Telepon</option>
                            <option value="WHATSAPP">WhatsApp</option>
                            <option value="KUNJUNGAN">Kunjungan</option>
                            <option value="BERTEMU_DI_KANTOR">Bertemu di Kantor</option>
                            <option value="LAINNYA">Lainnya</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold text-muted">Hasil</label>
                        <textarea class="form-control" name="result" rows="2" placeholder="Hasil follow up..." required style="border-radius:10px;"></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold text-muted">Rencana Selanjutnya</label>
                        <input type="text" class="form-control" name="next_plan" placeholder="Rencana tindak lanjut" style="border-radius:10px;">
                    </div>
                    <button type="submit" class="btn w-100 py-2 fw-bold text-white" style="background:var(--color-primary);border-radius:10px;border:none;">
                        Simpan Follow Up
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Modal Delegasi (Superuser) -->
<div class="modal fade" id="modalDelegasi" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered" style="margin:15px;">
        <div class="modal-content" style="border-radius:16px;">
            <div class="modal-header border-0 pb-0">
                <h6 class="modal-title fw-bold"><i class="fa-solid fa-user-gear text-primary me-2"></i>Delegasi ke AO</h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="form-delegasi">
                    <div class="mb-3">
                        <label class="form-label small fw-bold text-muted">Pilih AO</label>
                        <select class="form-select" name="assigned_to" id="sel-ao-delegasi" required style="border-radius:10px;">
                            <option value="">-- Pilih AO --</option>
                        </select>
                        <small class="text-muted" id="delegasi-hint" style="font-size:0.65rem;"></small>
                    </div>
                    <button type="submit" class="btn w-100 py-2 fw-bold text-white" style="background:var(--color-primary);border-radius:10px;border:none;">
                        Delegasikan
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>


<script>
(function() {
    const BASE_APP = <?= json_encode(BASE_APP) ?>;
    const prospectId = <?= json_encode($prospect_id) ?>;
    const userRole = '<?= $user_role ?>';
    const isAO = <?= $is_ao ? 'true' : 'false' ?>;
    const isSuperuser = <?= $is_superuser ? 'true' : 'false' ?>;

    // Dummy prospect data (same as daftar-prospek)
    const PROSPECTS = [
        { id:1, prospect_type:'KREDIT', customer_name:'Bapak Ahmad Sudirman', phone_number:'081234567890', identity_number:'3374012345670001', product_interest:'Kredit Modal Kerja', estimated_amount:150000000, kecamatan:'Semarang Tengah', desa:'Pendrikan', address:'Jl. Pandanaran No. 45 RT 03/RW 02', description:'Pemilik toko material, butuh modal untuk ekspansi usaha baru di cabang kedua.', is_ao_input:true, delegation_status:'SUDAH_DIDELEGASIKAN', status:'SLA', created_by:'201-001', created_by_name:'BUDI SANTOSO', created_at:'2026-06-10 09:15:00', assigned_to:'201-001' },
        { id:2, prospect_type:'TABUNGAN', customer_name:'Ibu Rina Wati', phone_number:'081298765432', identity_number:'', product_interest:'Tabungan Rencana', estimated_amount:5000000, kecamatan:'Semarang Barat', desa:'Krobokan', address:'Perumahan Griya Asri Blok D-12', description:'Ibu rumah tangga, tertarik menabung rutin untuk dana pendidikan anak.', is_ao_input:false, delegation_status:'BELUM_DIDELEGASIKAN', status:'OPEN', created_by:'201-005', created_by_name:'DEWI KUSUMA', created_at:'2026-06-12 14:30:00', assigned_to:null },
        { id:3, prospect_type:'KREDIT', customer_name:'PT Maju Jaya Sentosa', phone_number:'081345678901', identity_number:'', product_interest:'Kredit Investasi', estimated_amount:500000000, kecamatan:'Semarang Utara', desa:'Bandarharjo', address:'Kawasan Industri Terboyo Blok A-5', description:'Pengembangan pabrik garmen, butuh mesin baru untuk ekspansi produksi.', is_ao_input:false, delegation_status:'SUDAH_DIDELEGASIKAN', status:'FOLLOW_UP', created_by:'201-006', created_by_name:'RATNA SARI', created_at:'2026-06-08 10:00:00', assigned_to:'201-001' },
        { id:4, prospect_type:'DEPOSITO', customer_name:'H. Mochtar Abdullah', phone_number:'081456789012', identity_number:'3374056789010002', product_interest:'Deposito 12 Bulan', estimated_amount:200000000, kecamatan:'Rembang', desa:'Leteh', address:'Jl. Diponegoro No. 88 Rembang', description:'Dana pensiunan, cari yang aman dan stabil.', is_ao_input:true, delegation_status:'SUDAH_DIDELEGASIKAN', status:'CLOSING', created_by:'201-002', created_by_name:'SITI RAHAYU', created_at:'2026-06-05 08:00:00', assigned_to:'201-002' },
        { id:5, prospect_type:'PEMBELI_ASET', customer_name:'CV Berkah Abadi', phone_number:'081567890123', identity_number:'', product_interest:'Tanah Jaminan', estimated_amount:350000000, kecamatan:'Kaliori', desa:'Babadan', address:'Jl. Raya Kaliori KM 5', description:'Tertarik beli tanah eks jaminan kredit macet di area Kaliori.', is_ao_input:false, delegation_status:'BELUM_DIDELEGASIKAN', status:'OPEN', created_by:'201-005', created_by_name:'DEWI KUSUMA', created_at:'2026-06-14 11:20:00', assigned_to:null },
        { id:6, prospect_type:'DEBITUR_EXISTING', customer_name:'Bapak Supriyadi', phone_number:'081678901234', identity_number:'3374019876540003', product_interest:'Top-Up KMK', estimated_amount:75000000, kecamatan:'Semarang Tengah', desa:'Pandansari', address:'Jl. Pemuda No. 12', description:'Debitur lancar 2 tahun, track record bagus, mau tambah plafon untuk stok barang.', is_ao_input:true, delegation_status:'SUDAH_DIDELEGASIKAN', status:'FOLLOW_UP', created_by:'201-001', created_by_name:'BUDI SANTOSO', created_at:'2026-06-13 16:00:00', assigned_to:'201-001' },
        { id:7, prospect_type:'KREDIT', customer_name:'Ibu Siti Aminah', phone_number:'081789012345', identity_number:'3374023456780004', product_interest:'Kredit Multiguna', estimated_amount:50000000, kecamatan:'Sumber', desa:'Krikilan', address:'Desa Krikilan RT 01/RW 03', description:'Untuk renovasi rumah dan biaya sekolah anak.', is_ao_input:false, delegation_status:'SUDAH_DIDELEGASIKAN', status:'REJECT', created_by:'201-006', created_by_name:'RATNA SARI', created_at:'2026-06-02 09:00:00', assigned_to:'201-001' },
    ];

    // Find prospect
    const prospect = PROSPECTS.find(p => p.id == prospectId);
    if (!prospect) {
        document.querySelector('.detail-container').innerHTML = '<div class="detail-card text-center"><i class="fa-solid fa-circle-xmark text-danger fs-1 mb-3"></i><h6 class="fw-bold">Prospek tidak ditemukan</h6><a href="'+BASE_APP+'/daftar-prospek" class="btn btn-sm btn-outline-primary mt-2">Kembali</a></div>';
        return;
    }

    // Render detail
    renderDetail(prospect);
    renderTimeline(prospect);
    renderActions(prospect);

    function renderDetail(p) {
        // Type badge
        const typeMap = { KREDIT:{cls:'badge-kredit',lbl:'Kredit'}, TABUNGAN:{cls:'badge-tabungan',lbl:'Tabungan'}, DEPOSITO:{cls:'badge-deposito',lbl:'Deposito'}, PEMBELI_ASET:{cls:'badge-aset',lbl:'Pembeli Aset'}, DEBITUR_EXISTING:{cls:'badge-existing',lbl:'Debitur Existing'} };
        const typeInfo = typeMap[p.prospect_type] || {cls:'',lbl:p.prospect_type};
        document.getElementById('badge-type').className = 'badge-type-lg ' + typeInfo.cls;
        document.getElementById('badge-type').textContent = typeInfo.lbl;

        // Status badge
        const statusMap = { OPEN:{cls:'status-open',lbl:'Open'}, FOLLOW_UP:{cls:'status-follow_up',lbl:'Follow Up'}, SLA:{cls:'status-sla',lbl:'SLA'}, CLOSING:{cls:'status-closing',lbl:'Closing'}, REJECT:{cls:'status-reject',lbl:'Reject'} };
        const statusInfo = statusMap[p.status] || {cls:'',lbl:p.status};
        document.getElementById('badge-status').className = 'badge-status-lg ' + statusInfo.cls;
        document.getElementById('badge-status').textContent = statusInfo.lbl;

        document.getElementById('detail-name').textContent = p.customer_name;
        document.getElementById('detail-product').innerHTML = '<i class="fa-solid fa-box-open me-1"></i> ' + (p.product_interest || '-');

        document.getElementById('info-name').textContent = p.customer_name;
        document.getElementById('info-phone').innerHTML = '<a href="tel:'+p.phone_number+'" class="text-decoration-none">'+p.phone_number+'</a>';
        document.getElementById('info-identity').textContent = p.identity_number || '-';
        document.getElementById('info-nominal').textContent = p.estimated_amount ? formatRupiah(p.estimated_amount) : '-';
        document.getElementById('info-address').textContent = [p.address, p.desa, p.kecamatan].filter(Boolean).join(', ') || '-';

        document.getElementById('info-created-by').textContent = p.created_by_name;
        document.getElementById('info-created-at').textContent = new Date(p.created_at).toLocaleDateString('id-ID', {day:'numeric',month:'long',year:'numeric',hour:'2-digit',minute:'2-digit'});
        document.getElementById('info-delegasi').innerHTML = p.delegation_status === 'SUDAH_DIDELEGASIKAN' ? '<span style="color:#2E7D32;">Sudah</span>' : '<span style="color:#E65100;">Belum</span>';
        document.getElementById('info-ao').textContent = p.assigned_to ? getAOName(p.assigned_to) : 'Belum ditentukan';
        document.getElementById('info-source').textContent = p.is_ao_input ? 'Input AO (Auto-delegasi)' : 'Input Non-AO';
        document.getElementById('info-description').textContent = p.description || 'Tidak ada keterangan';
    }

    function getAOName(id) {
        const map = { '201-001':'Budi Santoso (AO Kredit)', '201-002':'Siti Rahayu (AO Dana)', '201-003':'Andi Setiawan (AO Remedial)', '201-004':'Wahyu Hidayat (Kabid)' };
        return map[id] || id;
    }

    function renderTimeline(p) {
        const timeline = document.getElementById('timeline-list');
        let items = [];
        items.push({ date: p.created_at, text: 'Prospek dibuat oleh ' + p.created_by_name });
        if (p.delegation_status === 'SUDAH_DIDELEGASIKAN' && !p.is_ao_input) {
            items.push({ date: p.created_at, text: 'Didelegasikan ke ' + getAOName(p.assigned_to) });
        }
        if (p.status === 'FOLLOW_UP' || p.status === 'SLA' || p.status === 'CLOSING' || p.status === 'REJECT') {
            items.push({ date: p.created_at, text: 'Status diubah ke ' + p.status.replace('_',' ') });
        }
        if (p.status === 'SLA') {
            items.push({ date: p.created_at, text: 'Masuk pipeline SLA - proses kredit berjalan' });
        }

        timeline.innerHTML = items.map(i => `
            <li class="timeline-item">
                <div class="timeline-dot"></div>
                <div class="timeline-date">${new Date(i.date).toLocaleDateString('id-ID',{day:'numeric',month:'short',year:'numeric'})}</div>
                <div class="timeline-content">${i.text}</div>
            </li>
        `).join('');
    }

    function renderActions(p) {
        const container = document.getElementById('action-buttons');
        let html = '';

        // Superuser: bisa delegasi jika belum didelegasikan
        if (isSuperuser && p.delegation_status === 'BELUM_DIDELEGASIKAN') {
            html += `<button class="btn-action-detail btn-delegasi" data-bs-toggle="modal" data-bs-target="#modalDelegasi">
                <i class="fa-solid fa-user-gear me-2"></i>Delegasikan ke AO
            </button>`;
            populateDelegasiOptions(p.prospect_type);
        }

        // AO: actions berdasarkan status
        if (isAO && p.delegation_status === 'SUDAH_DIDELEGASIKAN') {
            if (p.status === 'OPEN' || p.status === 'FOLLOW_UP') {
                html += `<button class="btn-action-detail btn-follow-up" data-bs-toggle="modal" data-bs-target="#modalFollowUp">
                    <i class="fa-solid fa-phone-volume me-2"></i>Input Follow Up
                </button>`;
            }
            if (p.status === 'FOLLOW_UP' && p.prospect_type === 'KREDIT') {
                html += `<button class="btn-action-detail btn-sla" onclick="changeStatus('SLA')">
                    <i class="fa-solid fa-arrow-right me-2"></i>Ubah ke SLA (Proses Kredit)
                </button>`;
            }
            if (['FOLLOW_UP','SLA'].includes(p.status)) {
                html += `<button class="btn-action-detail btn-closing" onclick="changeStatus('CLOSING')">
                    <i class="fa-solid fa-check-double me-2"></i>Closing
                </button>`;
            }
            if (!['CLOSING','REJECT'].includes(p.status)) {
                html += `<button class="btn-action-detail btn-reject" onclick="changeStatus('REJECT')">
                    <i class="fa-solid fa-xmark me-2"></i>Reject
                </button>`;
            }
        }

        // WhatsApp shortcut
        if (p.phone_number) {
            const waNum = p.phone_number.replace(/^0/,'62');
            html += `<a href="https://wa.me/${waNum}" target="_blank" class="btn-action-detail d-block text-center text-decoration-none" style="background:#E8F5E9;color:#25D366;">
                <i class="fa-brands fa-whatsapp me-2"></i>Hubungi via WhatsApp
            </a>`;
        }

        container.innerHTML = html;
    }

    function populateDelegasiOptions(type) {
        const sel = document.getElementById('sel-ao-delegasi');
        const hint = document.getElementById('delegasi-hint');
        let options = [];
        if (type === 'KREDIT' || type === 'DEBITUR_EXISTING') {
            options = [{id:'201-001', name:'Budi Santoso (AO Kredit)'}];
            hint.textContent = 'Prospek '+type.toLowerCase()+' hanya bisa didelegasikan ke AO Kredit';
        } else if (type === 'TABUNGAN' || type === 'DEPOSITO') {
            options = [{id:'201-002', name:'Siti Rahayu (AO Dana)'}];
            hint.textContent = 'Prospek '+type.toLowerCase()+' hanya bisa didelegasikan ke AO Dana';
        } else if (type === 'PEMBELI_ASET') {
            options = [{id:'201-003', name:'Andi Setiawan (AO Remedial)'}];
            hint.textContent = 'Prospek pembeli aset hanya bisa didelegasikan ke AO Remedial';
        }
        sel.innerHTML = '<option value="">-- Pilih AO --</option>';
        options.forEach(o => { sel.innerHTML += `<option value="${o.id}">${o.name}</option>`; });
    }

    // Action handlers
    window.changeStatus = function(newStatus) {
        if (!confirm('Ubah status menjadi ' + newStatus + '?')) return;
        showToast('<i class="fa-solid fa-check me-2"></i>Status berhasil diubah ke ' + newStatus, 'success');
        setTimeout(() => location.reload(), 1000);
    };

    // Follow up form
    document.getElementById('form-followup').addEventListener('submit', function(e) {
        e.preventDefault();
        showToast('<i class="fa-solid fa-check me-2"></i>Follow up berhasil disimpan', 'success');
        bootstrap.Modal.getInstance(document.getElementById('modalFollowUp')).hide();
    });

    // Delegasi form
    document.getElementById('form-delegasi').addEventListener('submit', function(e) {
        e.preventDefault();
        const ao = document.getElementById('sel-ao-delegasi').value;
        if (!ao) { showToast('Pilih AO terlebih dahulu', 'warning'); return; }
        showToast('<i class="fa-solid fa-check me-2"></i>Prospek berhasil didelegasikan', 'success');
        bootstrap.Modal.getInstance(document.getElementById('modalDelegasi')).hide();
        setTimeout(() => location.reload(), 1000);
    });
})();
</script>
