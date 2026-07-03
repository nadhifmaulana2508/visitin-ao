<?php
$is_ao = in_array($user_role, ['ao_kredit', 'ao_dana', 'ao_remedial', 'developer']);
$is_superuser = in_array($user_role, ['superuser', 'developer']);
$can_delegate_prospek = (bool)($menu_access['can_delegate_prospek'] ?? false);
$user_employee_id = $_SESSION['user_data']['employee_id'] ?? '';
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
    .sla-dot.done { background:#1976D2; }
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
    .btn-sla { background:#E3F2FD; color:#1565C0; }
    .btn-closing { background:var(--color-accent); color:white; box-shadow:0 4px 12px rgba(255,123,84,0.3); }
    .btn-reject { background:#FFEBEE; color:#C62828; }
    .btn-delegasi { background:var(--color-primary); color:white; box-shadow:0 4px 12px rgba(10,25,49,0.2); }
    .btn-wa { background:#F1F5F9; color:#334155; }
    .action-grid { display:grid; grid-template-columns:repeat(2, minmax(0,1fr)); gap:8px; }
    .action-grid .action-btn { margin-bottom:0; padding:10px 8px; font-size:0.72rem; min-height:44px; display:flex !important; align-items:center; justify-content:center; gap:6px; }
    .action-grid .action-btn i { font-size:0.82rem; margin:0 !important; }
    @media (min-width: 768px) { .action-grid { grid-template-columns:repeat(4, minmax(0,1fr)); } }

    .sla-summary {
        background:#ffffff; border-radius:12px;
        padding:14px; margin-bottom:14px; border:1px solid #E2E8F0;
    }
    .sla-summary .sla-total { font-size:1.8rem; font-weight:800; color:#1E293B; }
    .sla-summary .sla-label { font-size:0.7rem; color:#64748B; font-weight:600; }
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
            <div class="sla-summary d-flex justify-content-between align-items-center">
                <div>
                    <div class="sla-label">Durasi SLA (hari berjalan)</div>
                    <div class="sla-total" id="sla-days">0</div>
                </div>
                <div class="text-end">
                    <div class="sla-label">Tahap Saat Ini</div>
                    <div style="font-size:0.85rem; font-weight:800; color:#1E293B;" id="sla-current-stage">-</div>
                </div>
            </div>
            <div class="detail-card">
                <h6 class="section-title"><i class="fa-solid fa-chart-gantt me-2 text-accent"></i>Pipeline SLA Kredit</h6>
                <div id="sla-stages"></div>
                <div class="sla-subdocs collapse-card" id="credit-docs-section" style="display:none;">
                    <h6 class="section-title" onclick="toggleCreditDocs(this)">
                        <span style="font-size:0.72rem;font-weight:800;color:#1E293B;"><i class="fa-solid fa-folder-open me-1 text-accent"></i>Pemberkasan</span>
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
        <div class="detail-card collapse-card collapsed">
            <h6 class="section-title" onclick="toggleCollapse(this)"><span><i class="fa-solid fa-gears me-2 text-accent"></i>Informasi Proses</span><i class="fa-solid fa-chevron-down chev"></i></h6>
            <div class="collapse-body">
                <div class="info-row"><span class="info-label">Diinput Oleh</span><span class="info-value" id="d-created-by">-</span></div>
                <div class="info-row"><span class="info-label">Tanggal Input</span><span class="info-value" id="d-created-at">-</span></div>
                <div class="info-row"><span class="info-label">Sumber</span><span class="info-value" id="d-source">-</span></div>
                <div class="info-row"><span class="info-label">Delegasi</span><span class="info-value" id="d-delegasi">-</span></div>
                <div class="info-row"><span class="info-label">AO Pengelola</span><span class="info-value" id="d-ao">-</span></div>
            </div>
        </div>

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
                    <div class="mb-3">
                        <label class="form-label-custom">Plafon yang Akan Dipinjam <span class="text-danger">*</span></label>
                        <input type="text" class="input-custom" name="requested_loan_amount" inputmode="numeric" placeholder="Contoh: 5.000.000" required>
                        <small class="text-muted d-block mt-1" style="font-size:0.65rem;">Nominal otomatis diformat saat diketik.</small>
                    </div>
                    <div class="mb-3">
                        <label class="form-label-custom">Catatan</label>
                        <textarea class="input-custom" name="note" rows="2" placeholder="Catatan awal pengajuan kredit..."></textarea>
                    </div>
                    <button type="submit" class="action-btn btn-sla">Konfirmasi Lanjut</button>
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
                <h6 class="modal-title fw-bold"><i class="fa-solid fa-folder-open text-primary me-2"></i>Pemberkasan Lengkap</h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p class="small text-muted mb-3">Tandai pemberkasan sudah lengkap dan mulai hitung SLA kredit?</p>
                <div class="d-grid gap-2">
                    <button type="button" class="action-btn btn-sla mb-0" id="btn-confirm-complete-docs">Konfirmasi Pemberkasan</button>
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
                        <label class="form-label-custom">Nomor Rekening <span class="text-danger">*</span></label>
                        <input type="text" class="input-custom" name="closing_account_number" placeholder="Nomor rekening realisasi" required>
                        <div id="closing-account-hint" class="small mt-2" style="font-size:0.7rem;color:#64748B;">Ketik nomor rekening untuk mengambil data realisasi.</div>
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
                        <input type="file" class="input-custom" name="attachment_file" id="sla-stage-file">
                        <small class="text-muted d-block mt-1" id="sla-stage-file-hint" style="font-size:0.65rem;"></small>
                    </div>
                    <button type="submit" class="action-btn btn-sla">Simpan Tahap</button>
                </form>
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
        const statusMap = {OPEN:{cls:'status-open',l:'Open'},FOLLOW_UP:{cls:'status-follow_up',l:'Follow Up'},SLA:{cls:'status-sla',l:'SLA'},CLOSING:{cls:'status-closing',l:'Closing'},REJECT:{cls:'status-reject',l:'Reject'}};
        const si = statusMap[p.status] || {cls:'',l:p.status};
        document.getElementById('d-status-badge').className = 'badge-lg badge-status ' + si.cls;
        document.getElementById('d-status-badge').textContent = si.l;

        document.getElementById('d-name').textContent = p.customer_name;
        document.getElementById('d-product').innerHTML = '<i class="fa-solid fa-box-open me-1"></i>' + getProductLabel(p);
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
        document.getElementById('d-desc').textContent = p.description || p.keterangan_usaha || 'Tidak ada keterangan';

        renderCreditDocs(p.credit_pipeline || null);

        // Pipeline kredit tampil sejak debitur mau lanjut, supaya upload pemberkasan bisa dilakukan sebelum SLA mulai.
        if (p.credit_pipeline) {
            document.getElementById('sla-section').style.display = 'block';
            document.getElementById('sla-days').textContent = p.sla_duration_days ?? 0;
            renderSlaStages(p.credit_pipeline?.stages || p.sla_logs || []);
            if (!(p.sla_started_at || p.credit_pipeline?.sla_started_at)) {
                const plafon = p.credit_pipeline?.requested_loan_amount ? ' - Plafon ' + fmtRupiah(p.credit_pipeline.requested_loan_amount) : '';
                document.getElementById('sla-current-stage').textContent = 'Menunggu pemberkasan' + plafon;
            }
        } else {
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
            account.placeholder = isDana ? 'Nomor rekening tabungan/deposito' : 'Nomor rekening realisasi';
            account.dataset.lookupEnabled = isCredit ? '1' : '0';
            if (accountHint) {
                accountHint.style.display = isCredit ? 'block' : 'none';
                accountHint.textContent = isCredit ? 'Ketik nomor rekening untuk mengambil data realisasi.' : '';
                accountHint.style.color = '#64748B';
            }
        }
        if (amount) {
            amount.required = isCredit || isDana;
            amount.closest('.mb-3').style.display = isAsset ? 'none' : 'block';
            amount.min = isCredit || isDana ? '1' : '0';
            amount.placeholder = isDana ? 'Nominal setoran/deposito' : 'Nominal realisasi wajib';
            amount.readOnly = isCredit;
        }
        if (tenorField) tenorField.style.display = isCredit || p.prospect_type === 'DEPOSITO' ? 'block' : 'none';
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
                    <div class="doc-name">${d.doc_name}</div>
                    <div class="doc-note">${done ? 'Lengkap' : 'Upload ' + hint}</div>
                </div>
                ${viewButton}
                ${uploadButton}
            </div>`;
        }).join('');
    }

    function renderSlaStages(logs) {
        const el = document.getElementById('sla-stages');
        if (!logs || logs.length === 0) { el.innerHTML = '<p class="small text-muted">Belum ada tahap SLA</p>'; return; }
        const stageLabels = {PEMBERKASAN:'Pemberkasan',SURVEY:'Survey',ANALISA:'Analisa',KOMITE:'Komite'};
        const visibleStages = logs.filter(s => ['PEMBERKASAN','SURVEY','ANALISA','KOMITE'].includes(s.stage));
        let currentStage = '-';
        el.innerHTML = visibleStages.map(s => {
            const isDone = !!s.stage_ended_at;
            const isActive = !s.stage_ended_at;
            if (isActive) currentStage = stageLabels[s.stage] || s.stage;
            const dur = s.duration_days ? s.duration_days + ' hari' : (isActive ? 'Berjalan...' : '-');
            const attachmentUrl = s.attachment_url ? uploadFileUrl(s.attachment_url) : '';
            const attachment = attachmentUrl ? `<button type="button" class="icon-mini-btn view ms-2" title="Lihat detail" onclick="openFilePreview('${escapeAttr(attachmentUrl)}', '${s.attachment_type || 'IMAGE'}', 'Detail ${stageLabels[s.stage]||s.stage}')"><i class="fa-solid ${s.attachment_type === 'PDF' ? 'fa-file-pdf' : 'fa-image'}"></i></button>` : '';
            const canUploadAttachment = canUpdateSlaUi(prospectData) && ['ANALISA','KOMITE'].includes(s.stage) && isWithinUploadWindow(s.attachment_uploaded_at);
            const uploadAttachment = canUploadAttachment ? `<button type="button" class="icon-mini-btn upload ms-2" title="Upload PDF" onclick="pickStageAttachment('${s.stage}')"><i class="fa-solid fa-upload"></i></button>` : '';
            const analyst = s.stage === 'ANALISA' && s.analyst_employee_id ? `<div style="font-size:0.6rem;color:#64748B;"><i class="fa-solid fa-user-check me-1"></i>${escapeHtml(s.analyst_name || s.analyst_employee_id)} (${escapeHtml(s.analyst_employee_id)})</div>` : '';
            return `<div class="sla-stage">
                <div class="sla-dot ${isDone ? 'done' : 'active'}"></div>
                <div><div class="sla-stage-name">${stageLabels[s.stage]||s.stage}</div><div style="font-size:0.6rem;color:#94A3B8;">${fmtDate(s.stage_started_at)}${isDone?' → '+fmtDate(s.stage_ended_at):''}</div></div>
                ${analyst}<div class="sla-stage-dur">${dur}</div>${attachment}${uploadAttachment}
            </div>`;
        }).join('');
        document.getElementById('sla-current-stage').textContent = currentStage;
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
            const docsComplete = !!p.credit_pipeline?.documents_completed_at;
            const isCreditAtKomite = isCredit && p.status === 'SLA' && p.credit_pipeline?.current_stage === 'KOMITE';
            html += `<button class="action-btn btn-follow-up" data-bs-toggle="modal" data-bs-target="#modalFollowUp"><i class="fa-solid fa-phone-volume me-2"></i>Input Follow Up</button>`;

            if (isCredit && !hasCreditPipeline) {
                html += `<button class="action-btn btn-sla" data-bs-toggle="modal" data-bs-target="#modalCreditInterest"><i class="fa-solid fa-handshake me-2"></i>Debitur Mau Lanjut</button>`;
            }

            if (isCredit && hasCreditPipeline && !docsComplete) {
                html += `<button class="action-btn btn-sla" data-bs-toggle="modal" data-bs-target="#modalCompleteDocs"><i class="fa-solid fa-folder-open me-2"></i>Pemberkasan Lengkap</button>`;
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

    document.getElementById('form-credit-interest').addEventListener('submit', async function(e) {
        e.preventDefault();
        const fd = new FormData(this);
        const payload = {
            prospect_id: prospectId,
            requested_loan_amount: parseInt(digitsOnly(fd.get('requested_loan_amount')) || '0'),
            note: fd.get('note')
        };
        if (!payload.requested_loan_amount || payload.requested_loan_amount <= 0) {
            showToast('Plafon pinjaman wajib diisi', 'danger');
            return;
        }
        try {
            const res = await fetch(BASE_APP+'/api/?action=prospect_confirm_credit_interest', {method:'POST',credentials:'include',headers:{'Content-Type':'application/json'},body:JSON.stringify(payload)});
            const b = await res.json();
            if (b.status===200) {
                showToast('<i class="fa-solid fa-check me-2"></i>'+b.message,'success');
                const modalEl = document.getElementById('modalCreditInterest');
                bootstrap.Modal.getOrCreateInstance(modalEl).hide();
                this.reset();
                setTimeout(()=>location.reload(),800);
            }
            else showToast(b.message||'Gagal','danger');
        } catch(e) { showToast('Error koneksi','danger'); }
    });

    document.querySelector('#form-credit-interest [name="requested_loan_amount"]').addEventListener('input', function() {
        this.value = formatPlainNumber(this.value);
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

    async function lookupClosingAccount(accountNumber) {
        const account = String(accountNumber || '').replace(/\s+/g, '');
        if (!account) {
            clearClosingLookupFields();
            setClosingLookupState('idle', 'Ketik nomor rekening untuk mengambil data realisasi.');
            return;
        }

        setClosingLookupState('idle', 'Mencari data realisasi...');
        try {
            const params = new URLSearchParams({ prospect_id: prospectId, no_rekening: account });
            const res = await fetch(BASE_APP + '/api/?action=prospect_closing_lookup&' + params.toString(), {credentials:'include'});
            const body = await res.json();
            if (body.status === 200 && body.data) {
                renderClosingLookup(body.data);
                setClosingLookupState('success', 'Data realisasi ditemukan dan nominal otomatis terisi.');
            } else {
                clearClosingLookupFields();
                setClosingLookupState('error', body.message || 'Nomor rekening tidak ditemukan.');
            }
        } catch (e) {
            clearClosingLookupFields();
            setClosingLookupState('error', 'Gagal mengecek nomor rekening.');
        }
    }

    document.querySelector('#form-closing [name="closing_account_number"]').addEventListener('input', function() {
        if (this.dataset.lookupEnabled !== '1') return;
        this.value = this.value.replace(/\s+/g, '');
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
        const account = String(fd.get('closing_account_number') || '').replace(/\s+/g, '');
        if (isCredit && (!closingLookupAccount || closingLookupAccount !== account)) {
            showToast('Nomor rekening wajib dicek dan valid dari data realisasi', 'danger');
            return;
        }
        const payload = {
            prospect_id: prospectId,
            closing_account_number: account,
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
            body.innerHTML = `<img class="preview-img" src="${escapeAttr(url)}" alt="${escapeAttr(title || 'Foto')}">`;
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

    window.pickCreditDoc = function(docCode, accept) {
        const input = document.getElementById('credit-doc-file');
        input.value = '';
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
        input.accept = 'application/pdf';
        input.onchange = async () => {
            const file = input.files?.[0];
            if (!file) return;
            try {
                const encoded = {base64: await fileToBase64(file), mime: file.type || 'application/pdf'};
                const res = await fetch(BASE_APP+'/api/?action=prospect_credit_upload', {
                    method:'POST',
                    credentials:'include',
                    headers:{'Content-Type':'application/json'},
                    body:JSON.stringify({prospect_id:prospectId, target:'STAGE_ATTACHMENT', stage, file_base64:encoded.base64, mime_type:encoded.mime})
                });
                const b = await res.json();
                if(b.status===200){ showToast('<i class="fa-solid fa-check me-2"></i>Lampiran diupload','success'); setTimeout(()=>location.reload(),700); }
                else showToast(b.message||'Upload gagal','danger');
            } catch(e) { showToast('Upload gagal','danger'); }
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

    function syncStageFileInput() {
        const stage = getNextStage(prospectData || {});
        const stageLabelMap = {SURVEY:'Survey', ANALISA:'Analisa', KOMITE:'Komite'};
        document.getElementById('sla-next-stage-value').value = stage || '';
        document.getElementById('sla-next-stage-label').textContent = stage ? stageLabelMap[stage] || stage : 'Tahap selesai';
        const wrap = document.getElementById('sla-stage-file-wrap');
        const analystWrap = document.getElementById('sla-stage-analyst-wrap');
        const analystSelect = document.getElementById('sla-stage-analyst');
        const input = document.getElementById('sla-stage-file');
        const label = document.getElementById('sla-stage-file-label');
        const hint = document.getElementById('sla-stage-file-hint');
        input.value = '';
        analystSelect.value = '';
        analystWrap.style.display = stage === 'ANALISA' ? 'block' : 'none';
        if (stage === 'ANALISA') loadAnalisCabangOptions();
        if (stage === 'SURVEY') {
            wrap.style.display = 'block';
            input.accept = 'image/*';
            label.textContent = 'Foto Survey';
            hint.textContent = 'Upload foto kunjungan usaha atau jaminan. Foto akan dikompres otomatis.';
        } else if (stage === 'ANALISA') {
            wrap.style.display = 'block';
            input.accept = 'application/pdf';
            label.textContent = 'File Analisa';
            hint.textContent = 'Opsional. Upload hasil analisa dari SIAK dalam format PDF.';
        } else if (stage === 'KOMITE') {
            wrap.style.display = 'block';
            input.accept = 'application/pdf';
            label.textContent = 'File Komite';
            hint.textContent = 'Opsional. Upload hasil komite dalam format PDF.';
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
        const attachment = document.getElementById('sla-stage-file').files?.[0];
        if (!payload.stage) {
            showToast('Tahap SLA sudah selesai. Silakan closing jika sudah cair.', 'danger');
            return;
        }
        if (payload.stage === 'SURVEY' && !attachment) {
            showToast('Foto survey wajib diupload', 'danger');
            return;
        }
        if (payload.stage === 'ANALISA') {
            payload.analyst_employee_id = fd.get('analyst_employee_id');
            if (!payload.analyst_employee_id) {
                showToast('Pilih analis cabang dulu', 'danger');
                return;
            }
        }
        if (attachment) {
            try {
                if (attachment.type.startsWith('image/')) {
                    const encoded = await compressImageFile(attachment);
                    payload.attachment_base64 = encoded.base64;
                    payload.attachment_mime = encoded.mime;
                } else {
                    payload.attachment_base64 = await fileToBase64(attachment);
                    payload.attachment_mime = attachment.type || 'application/pdf';
                }
            } catch(e) {
                showToast('Lampiran gagal diproses','danger');
                return;
            }
        }
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
