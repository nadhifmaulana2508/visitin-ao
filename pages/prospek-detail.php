<?php
$is_ao = in_array($user_role, ['ao_kredit', 'ao_dana', 'ao_remedial', 'developer']);
$is_superuser = in_array($user_role, ['superuser', 'developer']);
$prospect_id = $_GET['id'] ?? null;
?>

<style>
    .detail-container { margin: -30px 16px 20px 16px; position: relative; z-index: 10; }
    @media (min-width: 768px) { .detail-container { margin: -35px 24px 24px 24px; } }
    @media (min-width: 1024px) { .detail-container { margin: -40px 32px 28px 32px; } }

    .detail-card { background:#fff; border-radius:16px; padding:18px; margin-bottom:14px; box-shadow:0 3px 12px rgba(0,0,0,0.03); }
    @media (min-width: 768px) { .detail-card { padding:22px; } }

    .info-row { display:flex; justify-content:space-between; align-items:flex-start; padding:9px 0; border-bottom:1px solid #F4F7F6; gap:10px; }
    .info-row:last-child { border-bottom:none; }
    .info-label { font-size:0.68rem; font-weight:700; color:#64748B; text-transform:uppercase; min-width:90px; flex-shrink:0; }
    .info-value { font-size:0.82rem; font-weight:700; color:#1E293B; text-align:right; word-break:break-word; }
    @media (min-width: 768px) { .info-label { font-size:0.72rem; } .info-value { font-size:0.85rem; } }

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
    .sla-stage { display:flex; align-items:center; gap:8px; padding:10px 0; border-bottom:1px dashed #E2E8F0; }
    .sla-stage:last-child { border-bottom:none; }
    .sla-dot { width:10px; height:10px; border-radius:50%; flex-shrink:0; }
    .sla-dot.done { background:#388E3C; }
    .sla-dot.active { background:var(--color-accent); animation:pulse 1.5s infinite; }
    @keyframes pulse { 0%,100%{box-shadow:0 0 0 0 rgba(255,123,84,0.4)} 50%{box-shadow:0 0 0 6px rgba(255,123,84,0)} }
    .sla-stage-name { font-size:0.78rem; font-weight:700; color:#1E293B; }
    .sla-stage-dur { font-size:0.65rem; color:#64748B; margin-left:auto; }

    /* Action buttons */
    .action-btn {
        width:100%; border:none; border-radius:12px; font-weight:700; font-size:0.85rem;
        padding:13px; margin-bottom:10px; transition:0.15s; cursor:pointer;
    }
    .action-btn:active { transform:scale(0.98); }
    .btn-follow-up { background:#E3F2FD; color:#1565C0; }
    .btn-sla { background:#E8F5E9; color:#2E7D32; }
    .btn-closing { background:var(--color-accent); color:white; box-shadow:0 4px 12px rgba(255,123,84,0.3); }
    .btn-reject { background:#FFEBEE; color:#C62828; }
    .btn-delegasi { background:var(--color-primary); color:white; box-shadow:0 4px 12px rgba(10,25,49,0.2); }
    .btn-wa { background:#E8F5E9; color:#25D366; }

    .sla-summary {
        background: linear-gradient(135deg, #E8F5E9, #C8E6C9); border-radius:12px;
        padding:14px; margin-bottom:14px; border:1px solid #A5D6A7;
    }
    .sla-summary .sla-total { font-size:1.8rem; font-weight:800; color:#2E7D32; }
    .sla-summary .sla-label { font-size:0.7rem; color:#388E3C; font-weight:600; }
</style>


<div class="header-compact">
    <div class="d-flex align-items-center">
        <a href="<?= BASE_APP ?>/daftar-prospek" class="text-white me-3"><i class="fa-solid fa-arrow-left fs-5"></i></a>
        <div>
            <h5 class="fw-bold mb-0">Detail Prospek</h5>
            <p class="small text-white-50 mb-0" style="font-size:0.7rem;">ID #<?= htmlspecialchars($prospect_id ?? '-') ?></p>
        </div>
    </div>
</div>

<div class="detail-container">
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
            <h5 class="fw-bold text-dark mb-1" id="d-name">-</h5>
            <p class="small text-muted mb-0" id="d-product"><i class="fa-solid fa-box-open me-1"></i>-</p>
        </div>

        <!-- SLA Summary (hanya muncul jika status SLA) -->
        <div id="sla-section" style="display:none;">
            <div class="sla-summary d-flex justify-content-between align-items-center">
                <div>
                    <div class="sla-label">Durasi SLA (hari berjalan)</div>
                    <div class="sla-total" id="sla-days">0</div>
                </div>
                <div class="text-end">
                    <div class="sla-label">Tahap Saat Ini</div>
                    <div style="font-size:0.85rem; font-weight:800; color:#1B5E20;" id="sla-current-stage">-</div>
                </div>
            </div>
            <div class="detail-card">
                <h6 class="section-title"><i class="fa-solid fa-chart-gantt me-2 text-accent"></i>Pipeline SLA Kredit</h6>
                <div id="sla-stages"></div>
            </div>
        </div>

        <!-- Info Nasabah -->
        <div class="detail-card">
            <h6 class="section-title"><i class="fa-solid fa-user me-2 text-accent"></i>Informasi Nasabah</h6>
            <div class="info-row"><span class="info-label">Nama</span><span class="info-value" id="d-cust-name">-</span></div>
            <div class="info-row"><span class="info-label">No. HP</span><span class="info-value" id="d-phone">-</span></div>
            <div class="info-row"><span class="info-label">No. Identitas</span><span class="info-value" id="d-identity">-</span></div>
            <div class="info-row"><span class="info-label">Estimasi</span><span class="info-value text-accent" id="d-nominal">-</span></div>
            <div class="info-row"><span class="info-label">Cabang</span><span class="info-value" id="d-cabang">-</span></div>
            <div class="info-row"><span class="info-label">Alamat</span><span class="info-value" id="d-alamat" style="font-size:0.75rem;">-</span></div>
        </div>

        <!-- Info Proses -->
        <div class="detail-card">
            <h6 class="section-title"><i class="fa-solid fa-gears me-2 text-accent"></i>Informasi Proses</h6>
            <div class="info-row"><span class="info-label">Diinput Oleh</span><span class="info-value" id="d-created-by">-</span></div>
            <div class="info-row"><span class="info-label">Tanggal Input</span><span class="info-value" id="d-created-at">-</span></div>
            <div class="info-row"><span class="info-label">Sumber</span><span class="info-value" id="d-source">-</span></div>
            <div class="info-row"><span class="info-label">Delegasi</span><span class="info-value" id="d-delegasi">-</span></div>
            <div class="info-row"><span class="info-label">AO Pengelola</span><span class="info-value" id="d-ao">-</span></div>
        </div>

        <!-- Keterangan -->
        <div class="detail-card">
            <h6 class="section-title"><i class="fa-solid fa-note-sticky me-2 text-accent"></i>Keterangan</h6>
            <p class="small text-muted mb-0" id="d-desc" style="font-style:italic; line-height:1.5;">-</p>
        </div>

        <!-- Follow Ups -->
        <div class="detail-card" id="followup-section">
            <h6 class="section-title"><i class="fa-solid fa-phone-volume me-2 text-accent"></i>Riwayat Follow Up</h6>
            <div id="followup-list"><p class="small text-muted">Belum ada follow up</p></div>
        </div>

        <!-- Timeline History -->
        <div class="detail-card">
            <h6 class="section-title"><i class="fa-solid fa-timeline me-2 text-accent"></i>Riwayat Aktivitas</h6>
            <ul class="timeline" id="timeline-list"></ul>
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
                        <label class="form-label-custom">Nomor Rekening <span class="text-danger">*</span></label>
                        <input type="text" class="input-custom" name="closing_account_number" placeholder="Nomor rekening realisasi" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label-custom">Nominal Realisasi (Rp) <span class="text-danger">*</span></label>
                        <input type="number" class="input-custom" name="closing_realization_amount" placeholder="0" required min="1">
                    </div>
                    <div class="mb-3" id="closing-tenor-field">
                        <label class="form-label-custom">Jangka Waktu (bulan)</label>
                        <input type="number" class="input-custom" name="closing_tenor" placeholder="12">
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
                    <div class="mb-3">
                        <label class="form-label-custom">Tahap <span class="text-danger">*</span></label>
                        <select class="input-custom" name="stage" required>
                            <option value="VERIFIKASI_DATA">Verifikasi Data</option>
                            <option value="SURVEI_JAMINAN">Survei Jaminan</option>
                            <option value="ANALISA_KREDIT">Analisa Kredit</option>
                            <option value="KOMITE_KREDIT">Komite Kredit</option>
                            <option value="PERSETUJUAN">Persetujuan</option>
                            <option value="AKAD_KREDIT">Akad Kredit</option>
                            <option value="PENCAIRAN">Pencairan</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label-custom">Catatan</label>
                        <input type="text" class="input-custom" name="note" placeholder="Catatan tahap...">
                    </div>
                    <button type="submit" class="action-btn btn-sla">Simpan Tahap</button>
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
    let prospectData = null;

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
        const statusMap = {OPEN:{cls:'status-open',l:'Open'},FOLLOW_UP:{cls:'status-follow_up',l:'Follow Up'},SLA:{cls:'status-sla',l:'SLA'},CLOSING:{cls:'status-closing',l:'Closing'},REJECT:{cls:'status-reject',l:'Reject'}};
        const si = statusMap[p.status] || {cls:'',l:p.status};
        document.getElementById('d-status-badge').className = 'badge-lg badge-status ' + si.cls;
        document.getElementById('d-status-badge').textContent = si.l;

        document.getElementById('d-name').textContent = p.customer_name;
        document.getElementById('d-product').innerHTML = '<i class="fa-solid fa-box-open me-1"></i>' + (p.product_interest || '-');

        // Info
        document.getElementById('d-cust-name').textContent = p.customer_name;
        document.getElementById('d-phone').innerHTML = p.phone_number ? `<a href="tel:${p.phone_number}" style="color:inherit;text-decoration:none;">${p.phone_number}</a>` : '-';
        document.getElementById('d-identity').textContent = p.identity_number || '-';
        document.getElementById('d-nominal').textContent = p.estimated_amount ? formatRupiah(p.estimated_amount) : '-';
        document.getElementById('d-cabang').textContent = (p.kode_kantor||'') + (p.nama_kantor ? ' - '+p.nama_kantor : '');
        document.getElementById('d-alamat').textContent = [p.address, p.desa, p.kecamatan, p.kab_kota].filter(Boolean).join(', ') || '-';
        document.getElementById('d-created-by').textContent = p.created_by || '-';
        document.getElementById('d-created-at').textContent = p.created_at ? fmtDateTime(p.created_at) : '-';
        document.getElementById('d-source').textContent = p.is_ao_input == 1 ? 'Input AO (Auto-delegasi)' : 'Input Non-AO';
        document.getElementById('d-delegasi').innerHTML = p.delegation_status === 'SUDAH_DIDELEGASIKAN' ? '<span style="color:#2E7D32;">Sudah</span>' : '<span style="color:#E65100;">Belum</span>';
        document.getElementById('d-ao').textContent = p.assigned_to || 'Belum ditentukan';
        document.getElementById('d-desc').textContent = p.description || 'Tidak ada keterangan';

        // SLA Section
        if (p.status === 'SLA' && p.sla_started_at) {
            document.getElementById('sla-section').style.display = 'block';
            document.getElementById('sla-days').textContent = p.sla_duration_days ?? 0;
            renderSlaStages(p.sla_logs || []);
        }

        // Follow ups
        renderFollowUps(p.follow_ups || []);

        // Timeline
        renderTimeline(p.histories || []);

        // Actions
        renderActions(p);
    }


    function renderSlaStages(logs) {
        const el = document.getElementById('sla-stages');
        if (!logs || logs.length === 0) { el.innerHTML = '<p class="small text-muted">Belum ada tahap SLA</p>'; return; }
        const stageLabels = {VERIFIKASI_DATA:'Verifikasi Data',SURVEI_JAMINAN:'Survei Jaminan',ANALISA_KREDIT:'Analisa Kredit',KOMITE_KREDIT:'Komite Kredit',PERSETUJUAN:'Persetujuan',AKAD_KREDIT:'Akad Kredit',PENCAIRAN:'Pencairan'};
        let currentStage = '-';
        el.innerHTML = logs.map(s => {
            const isDone = !!s.stage_ended_at;
            const isActive = !s.stage_ended_at;
            if (isActive) currentStage = stageLabels[s.stage] || s.stage;
            const dur = s.duration_days ? s.duration_days + ' hari' : (isActive ? 'Berjalan...' : '-');
            return `<div class="sla-stage">
                <div class="sla-dot ${isDone ? 'done' : 'active'}"></div>
                <div><div class="sla-stage-name">${stageLabels[s.stage]||s.stage}</div><div style="font-size:0.6rem;color:#94A3B8;">${fmtDate(s.stage_started_at)}${isDone?' → '+fmtDate(s.stage_ended_at):''}</div></div>
                <div class="sla-stage-dur">${dur}</div>
            </div>`;
        }).join('');
        document.getElementById('sla-current-stage').textContent = currentStage;
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

    function renderActions(p) {
        const el = document.getElementById('action-section');
        let html = '';

        // Superuser: delegasi jika belum
        if (isSuperuser && p.delegation_status === 'BELUM_DIDELEGASIKAN') {
            html += `<button class="action-btn btn-delegasi" data-bs-toggle="modal" data-bs-target="#modalDelegasi"><i class="fa-solid fa-user-gear me-2"></i>Delegasikan ke AO</button>`;
            loadAOOptions(p.prospect_type);
        }

        // AO actions
        if (isAO && p.delegation_status === 'SUDAH_DIDELEGASIKAN' && !['CLOSING','REJECT'].includes(p.status)) {
            html += `<button class="action-btn btn-follow-up" data-bs-toggle="modal" data-bs-target="#modalFollowUp"><i class="fa-solid fa-phone-volume me-2"></i>Input Follow Up</button>`;

            if (p.status === 'FOLLOW_UP' && (p.prospect_type === 'KREDIT' || p.prospect_type === 'DEBITUR_EXISTING')) {
                html += `<button class="action-btn btn-sla" onclick="doChangeStatus('SLA')"><i class="fa-solid fa-arrow-right me-2"></i>Ubah ke SLA (Proses Kredit)</button>`;
            }

            if (p.status === 'SLA') {
                html += `<button class="action-btn btn-sla" data-bs-toggle="modal" data-bs-target="#modalSlaStage"><i class="fa-solid fa-chart-gantt me-2"></i>Tambah Tahap SLA</button>`;
            }

            if (['FOLLOW_UP','SLA'].includes(p.status)) {
                html += `<button class="action-btn btn-closing" data-bs-toggle="modal" data-bs-target="#modalClosing"><i class="fa-solid fa-check-double me-2"></i>Closing</button>`;
            }

            html += `<button class="action-btn btn-reject" data-bs-toggle="modal" data-bs-target="#modalReject"><i class="fa-solid fa-xmark me-2"></i>Reject</button>`;
        }

        // WhatsApp
        if (p.phone_number) {
            const wa = (p.phone_number||'').replace(/^0/,'62');
            html += `<a href="https://wa.me/${wa}" target="_blank" class="action-btn btn-wa d-block text-center text-decoration-none"><i class="fa-brands fa-whatsapp me-2"></i>Hubungi via WhatsApp</a>`;
        }

        el.innerHTML = html;
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

    document.getElementById('form-closing').addEventListener('submit', async function(e) {
        e.preventDefault();
        const fd = new FormData(this);
        const payload = {prospect_id:prospectId, closing_account_number:fd.get('closing_account_number'), closing_realization_amount:parseInt(fd.get('closing_realization_amount')), closing_tenor:parseInt(fd.get('closing_tenor')||'0'), closing_note:fd.get('closing_note')};
        try {
            const res = await fetch(BASE_APP+'/api/?action=prospect_close', {method:'POST',credentials:'include',headers:{'Content-Type':'application/json'},body:JSON.stringify(payload)});
            const b = await res.json();
            if(b.status===200){ showToast('<i class="fa-solid fa-check me-2"></i>Closing berhasil!','success'); bootstrap.Modal.getInstance(document.getElementById('modalClosing')).hide(); setTimeout(()=>location.reload(),800); }
            else showToast(b.message||'Gagal','danger');
        } catch(e) { showToast('Error','danger'); }
    });

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

    document.getElementById('form-sla-stage').addEventListener('submit', async function(e) {
        e.preventDefault();
        const fd = new FormData(this);
        const payload = {prospect_id:prospectId, stage:fd.get('stage'), note:fd.get('note')};
        try {
            const res = await fetch(BASE_APP+'/api/?action=prospect_sla_log', {method:'POST',credentials:'include',headers:{'Content-Type':'application/json'},body:JSON.stringify(payload)});
            const b = await res.json();
            if(b.status===200){ showToast('<i class="fa-solid fa-check me-2"></i>Tahap SLA ditambahkan','success'); bootstrap.Modal.getInstance(document.getElementById('modalSlaStage')).hide(); setTimeout(()=>location.reload(),800); }
            else showToast(b.message||'Gagal','danger');
        } catch(e) { showToast('Error','danger'); }
    });

    async function loadAOOptions(type) {
        const sel = document.getElementById('sel-ao-delegasi');
        const hint = document.getElementById('delegasi-hint');
        let group = '';
        if (type==='KREDIT'||type==='DEBITUR_EXISTING') { group='AO Kredit'; hint.textContent='Hanya AO Kredit'; }
        else if (type==='TABUNGAN'||type==='DEPOSITO') { group='AO Dana'; hint.textContent='Hanya AO Dana'; }
        else if (type==='PEMBELI_ASET') { group='AO Remedial'; hint.textContent='Hanya AO Remedial'; }
        try {
            const res = await fetch(BASE_APP+'/api/?action=master_pegawai_ao'+(group?'&group_jabatan='+encodeURIComponent(group):''), {credentials:'include'});
            const b = await res.json();
            if (b.status===200 && Array.isArray(b.data)) {
                sel.innerHTML = '<option value="">-- Pilih AO --</option>';
                b.data.forEach(ao => { sel.innerHTML += `<option value="${ao.employee_id}">${ao.full_name} (${ao.job_position||ao.group_jabatan})</option>`; });
            }
        } catch(e) {}
    }

    // Helpers
    function fmtDate(d) { if(!d) return '-'; return new Date(d).toLocaleDateString('id-ID',{day:'numeric',month:'short',year:'numeric'}); }
    function fmtDateTime(d) { if(!d) return '-'; return new Date(d).toLocaleDateString('id-ID',{day:'numeric',month:'short',year:'numeric',hour:'2-digit',minute:'2-digit'}); }

    // Init
    loadDetail();
})();
</script>
