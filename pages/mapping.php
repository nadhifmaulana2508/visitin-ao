<style>
    /* COMPACT HEADER STYLING */
    .header-compact {
        background: linear-gradient(135deg, var(--color-primary), var(--color-secondary));
        color: white; padding: 25px 20px 45px 20px; 
        border-bottom-left-radius: 25px; border-bottom-right-radius: 25px;
    }
    .progress-header { height: 8px; border-radius: 10px; background-color: rgba(255,255,255,0.2); margin-bottom: 5px; }
    .progress-bar-header { background-color: var(--color-accent); border-radius: 10px; }

    /* STYLING TAB CUSTOM (ICON BASED) */
    .icon-tabs {
        display: flex; background: #ffffff; border-radius: 15px; padding: 5px;
        margin: -25px 20px 20px 20px; box-shadow: 0 4px 15px rgba(0,0,0,0.05); position: relative; z-index: 10;
    }
    .icon-tab-btn {
        flex: 1; text-align: center; padding: 10px 0; background: transparent; border: none; 
        border-radius: 12px; color: #94A3B8; transition: all 0.3s ease; cursor: pointer;
    }
    .icon-tab-btn.active { background-color: var(--color-primary); color: white; box-shadow: 0 4px 10px rgba(10, 25, 49, 0.2); }
    .icon-tab-btn i { font-size: 1.2rem; }
    .icon-tab-btn span { display: block; font-size: 0.65rem; font-weight: 700; margin-top: 3px; }
    
    .tab-content { display: none; animation: fadeIn 0.3s ease; }
    .tab-content.active { display: block; }
    @keyframes fadeIn { from { opacity: 0; transform: translateY(5px); } to { opacity: 1; transform: translateY(0); } }

    /* STYLING FILTER COLLAPSIBLE */
    .filter-wrapper {
        background-color: #ffffff; border-radius: 16px; margin: 0 0 15px 0; 
        padding: 15px; box-shadow: 0 4px 15px rgba(0, 0, 0, 0.03);
    }
    .search-input { background-color: #F4F7F6; border: 1px solid #E0E0E0; border-radius: 10px; padding: 10px 15px; font-size: 0.85rem; }
    .search-input:focus { border-color: var(--color-primary); box-shadow: none; }
    .btn-toggle-filter { background-color: #E6EDF5; color: var(--color-primary); border: none; border-radius: 10px; padding: 0 15px; transition: 0.2s; }
    .filter-label { font-size: 0.7rem; font-weight: 700; color: var(--color-primary); margin-bottom: 5px; display: block; text-transform: uppercase; }

    /* CARD STYLING & STATUS */
    .mapping-card { border: none; border-radius: 16px; margin-bottom: 12px; background-color: #ffffff; box-shadow: 0 4px 12px rgba(0,0,0,0.03); transition: transform 0.2s; }
    .mapping-card:active { transform: scale(0.98); }
    .badge-soft-danger { background-color: #FFE5E5; color: #D32F2F; }
    .badge-soft-success { background-color: #E8F5E9; color: #388E3C; }
    .bucket-memburuk { background-color: #D32F2F; color: white; }
    .bucket-stay { background-color: #F57C00; color: white; }
    .bucket-perbaikan { background-color: #388E3C; color: white; }
    .badge-role { font-size: 0.7rem; padding: 6px 10px; border-radius: 6px; font-weight: 700; }
    .badge-bucket { font-size: 0.65rem; padding: 4px 8px; border-radius: 20px; font-weight: 700; letter-spacing: 0.5px; }
    .info-text { font-size: 0.75rem; color: #64748B; margin-bottom: 4px; display: flex; align-items: flex-start;}
    .info-text i { width: 16px; margin-top: 3px; color: #A0AEC0; }
    .pipeline-badge { font-size: 0.6rem; padding: 3px 6px; border-radius: 4px; font-weight: 700; letter-spacing: 0.5px;}

    /* DUAL BUTTONS STYLING */
    .btn-action-main { background-color: #F8F9FA; color: var(--color-primary); border: 1px solid #E0E0E0; border-radius: 10px; font-weight: 700; transition: all 0.2s; font-size: 0.85rem; }
    .btn-action-main:hover { background-color: var(--color-primary); color: white; border-color: var(--color-primary); }
    .btn-action-history { background-color: #ffffff; color: var(--color-accent); border: 1px solid #E0E0E0; border-radius: 10px; transition: all 0.2s; display: flex; align-items: center; justify-content: center; }
    .btn-action-history:hover { background-color: #FFF0EB; border-color: var(--color-accent); color: var(--color-accent); }
    
    /* SUMMARY TAB STYLING */
    .summary-card { background: white; border-radius: 16px; padding: 15px; margin-bottom: 15px; box-shadow: 0 4px 10px rgba(0,0,0,0.02); }
    .summary-title { font-size: 0.8rem; font-weight: 800; color: var(--color-primary); text-transform: uppercase; margin-bottom: 12px; border-bottom: 2px solid #F4F7F6; padding-bottom: 8px; }
    .stat-value { font-size: 1.1rem; font-weight: 800; color: #1E293B; }
    .stat-label { font-size: 0.7rem; color: #64748B; font-weight: 600; }
    .kolek-row { display: flex; justify-content: space-between; align-items: center; padding: 8px 0; border-bottom: 1px dashed #E2E8F0; }
    .kolek-row:last-child { border-bottom: none; }
    
    /* PAGINATION STYLING */
    .pagination-custom .page-link { color: var(--color-primary); border: none; margin: 0 3px; border-radius: 8px; font-weight: 600; }
    .pagination-custom .page-item.active .page-link { background-color: var(--color-primary); color: white; }
</style>

<div class="header-compact">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h5 class="fw-bold mb-0">Mapping Awal Bulan</h5>
        <span class="badge bg-white text-primary rounded-pill px-3 py-2 fw-bold">85 NOA</span>
    </div>
    <div class="d-flex justify-content-between mb-1">
        <span class="small text-white-50 fw-bold" style="font-size: 0.75rem;">Coverage: 60/85 (70%)</span>
        <span class="small text-white-50 fw-bold" style="font-size: 0.75rem;"><i class="fa-solid fa-person-walking-arrow-right me-1"></i> Frek: 124x</span>
    </div>
    <div class="progress progress-header">
        <div class="progress-bar progress-bar-header" role="progressbar" style="width: 70%;"></div>
    </div>
</div>

<div class="icon-tabs">
    <button class="icon-tab-btn active" onclick="switchMappingTab('list', this)">
        <i class="fa-solid fa-list-check"></i>
        <span>List Mapping</span>
    </button>
    <button class="icon-tab-btn" onclick="switchMappingTab('summary', this)">
        <i class="fa-solid fa-chart-column"></i>
        <span>Ringkasan</span>
    </button>
</div>

<div class="container px-3 mt-2 mb-5 pb-5">
    
    <div id="tab-list" class="tab-content active">
        <div class="filter-wrapper">
            <form action="" method="GET">
                <div class="d-flex gap-2">
                    <div class="input-group flex-grow-1">
                        <span class="input-group-text bg-light border-0" style="border-radius: 10px 0 0 10px;"><i class="fa-solid fa-magnifying-glass text-muted"></i></span>
                        <input type="text" class="form-control bg-light border-0" placeholder="Cari nama nasabah..." style="font-size: 0.9rem; border-radius: 0 10px 10px 0;">
                    </div>
                    <button class="btn btn-toggle-filter" type="button" data-bs-toggle="collapse" data-bs-target="#collapseFilterMapping" aria-expanded="false">
                        <i class="fa-solid fa-sliders"></i>
                    </button>
                </div>

                <div class="collapse" id="collapseFilterMapping">
                    <div class="row g-2 pt-3 mt-1 border-top">
                        <div class="col-6">
                            <label class="filter-label">Kecamatan</label>
                            <select class="form-select search-input">
                                <option value="" selected>Semua Kec...</option>
                                <option value="Semarang Tengah">Semarang Tengah</option>
                            </select>
                        </div>
                        <div class="col-6">
                            <label class="filter-label">Desa / Kelurahan</label>
                            <select class="form-select search-input">
                                <option value="" selected>Semua Desa...</option>
                                <option value="Pendrikan">Pendrikan</option>
                            </select>
                        </div>
                        <div class="col-6 mt-2">
                            <label class="filter-label">Jatuh Tempo</label>
                            <input type="date" class="form-control search-input">
                        </div>
                        <div class="col-6 mt-2">
                            <label class="filter-label">Status Bayar</label>
                            <select class="form-select search-input">
                                <option value="" selected>Semua Status</option>
                                <option value="belum">Belum Bayar</option>
                                <option value="sudah">Sudah Bayar</option>
                            </select>
                        </div>
                        <div class="col-6 mt-2">
                            <label class="filter-label">Kondisi Bucket</label>
                            <select class="form-select search-input">
                                <option value="" selected>Semua Bucket</option>
                                <option value="memburuk">Memburuk</option>
                                <option value="stay">Stay</option>
                                <option value="perbaikan">Perbaikan</option>
                            </select>
                        </div>
                        <div class="col-6 mt-2">
                            <label class="filter-label">Totung (Rp)</label>
                            <input type="number" class="form-control search-input" placeholder="Min. Totung...">
                        </div>
                        <div class="col-12 mt-3">
                            <button type="submit" class="btn w-100 py-2 fw-bold text-white shadow-sm" style="background-color: var(--color-primary); border-radius: 10px;">
                                Terapkan Filter
                            </button>
                        </div>
                    </div>
                </div>
            </form>
        </div>

        <div class="d-flex justify-content-between align-items-center mb-3">
            <h6 class="fw-bold mb-0 text-dark" style="opacity: 0.8;">Daftar Kunjungan <span class="badge bg-secondary ms-1">Baru</span></h6>
            <span class="text-muted small">Menampilkan <b>5</b> dari 85 NOA</span>
        </div>

        <div class="card mapping-card border-left" style="border-left: 5px solid #D32F2F;">
            <div class="card-body p-3">
                <div class="d-flex justify-content-between align-items-start mb-2">
                    <span class="badge badge-soft-danger badge-role">Belum Bayar</span>
                    <span class="badge bucket-memburuk badge-bucket"><i class="fa-solid fa-arrow-trend-down me-1"></i> Bucket Memburuk</span>
                </div>
                <h6 class="fw-bold mb-1 text-dark">Bapak Supriyadi (Toko Makmur)</h6>
                
                <div class="d-flex align-items-center mb-3" style="font-size: 0.65rem;">
                    <span class="badge bg-light text-secondary border px-2 pipeline-badge" title="Closing Bulan Lalu">Cls: 31-60</span>
                    <i class="fa-solid fa-caret-right mx-1 text-muted"></i>
                    <span class="badge bg-light text-primary border px-2 pipeline-badge" title="Pipeline Target">Pipe: 1-30</span>
                    <i class="fa-solid fa-caret-right mx-1 text-muted"></i>
                    <span class="badge bg-danger text-white px-2 shadow-sm pipeline-badge" title="Kondisi Aktual Hari Ini">Act: 61-90</span>
                </div>

                <div class="bg-light p-2 rounded-3 mb-3">
                    <span class="info-text"><i class="fa-solid fa-wallet"></i> <span>Baki Debet: Rp 45.000.000</span></span>
                    <span class="info-text"><i class="fa-solid fa-file-invoice-dollar"></i> <span class="text-danger fw-bold">Totung: Rp 5.000.000</span></span>
                    <span class="info-text"><i class="fa-solid fa-calendar-xmark"></i> <span>Jatuh Tempo: 05 Apr 2026</span></span>
                    <span class="info-text"><i class="fa-solid fa-clock-rotate-left"></i> <span class="text-primary fw-bold">Trkh Kunjungan: 02 Apr (PTP)</span></span>
                </div>
                
                <div class="d-flex gap-2">
                    <a href="<?= BASE_APP ?>/kunjungan-create" class="btn btn-action-main flex-grow-1 py-2">
                        <i class="fa-solid fa-person-walking-arrow-right me-1"></i> Mulai Kunjungan
                    </a>
                    <a href="<?= BASE_APP ?>/kunjungan-history-debitur" class="btn btn-action-history px-3 py-2" title="Lihat Riwayat">
                        <i class="fa-solid fa-clock-rotate-left fs-5"></i>
                    </a>
                </div>
            </div>
        </div>

        <div class="card mapping-card border-left" style="border-left: 5px solid #F57C00;">
            <div class="card-body p-3">
                <div class="d-flex justify-content-between align-items-start mb-2">
                    <span class="badge badge-soft-danger badge-role">Belum Bayar</span>
                    <span class="badge bucket-stay badge-bucket"><i class="fa-solid fa-minus me-1"></i> Bucket Stay</span>
                </div>
                <h6 class="fw-bold mb-1 text-dark">PT. Maju Bersama</h6>
                
                <div class="d-flex align-items-center mb-3" style="font-size: 0.65rem;">
                    <span class="badge bg-light text-secondary border px-2 pipeline-badge">Cls: 31-60</span>
                    <i class="fa-solid fa-caret-right mx-1 text-muted"></i>
                    <span class="badge bg-light text-primary border px-2 pipeline-badge">Pipe: 1-30</span>
                    <i class="fa-solid fa-caret-right mx-1 text-muted"></i>
                    <span class="badge bg-warning text-white px-2 shadow-sm pipeline-badge" style="background-color: #F57C00!important;">Act: 31-60</span>
                </div>

                <div class="bg-light p-2 rounded-3 mb-3">
                    <span class="info-text"><i class="fa-solid fa-wallet"></i> <span>Baki Debet: Rp 120.000.000</span></span>
                    <span class="info-text"><i class="fa-solid fa-file-invoice-dollar"></i> <span class="text-warning fw-bold" style="color: #F57C00!important;">Totung: Rp 12.000.000</span></span>
                    <span class="info-text"><i class="fa-solid fa-calendar-day"></i> <span>Jatuh Tempo: 15 Apr 2026</span></span>
                    <span class="info-text"><i class="fa-solid fa-user-xmark"></i> <span class="text-muted fw-bold">Trkh Kunjungan: Blm ada (Bln ini)</span></span>
                </div>
                <div class="d-flex gap-2">
                    <a href="<?= BASE_APP ?>/kunjungan-create" class="btn btn-action-main flex-grow-1 py-2">
                        <i class="fa-solid fa-person-walking-arrow-right me-1"></i> Mulai Kunjungan
                    </a>
                    <a href="<?= BASE_APP ?>/kunjungan-history-debitur" class="btn btn-action-history px-3 py-2" title="Lihat Riwayat">
                        <i class="fa-solid fa-clock-rotate-left fs-5"></i>
                    </a>
                </div>
            </div>
        </div>

        <div class="card mapping-card border-left" style="border-left: 5px solid #388E3C;">
            <div class="card-body p-3">
                <div class="d-flex justify-content-between align-items-start mb-2">
                    <span class="badge badge-soft-success badge-role">Sudah Bayar</span>
                    <span class="badge bucket-perbaikan badge-bucket"><i class="fa-solid fa-arrow-trend-up me-1"></i> Bucket Perbaikan</span>
                </div>
                <h6 class="fw-bold mb-1 text-dark">Ibu Siti Aminah (Katering)</h6>
                
                <div class="d-flex align-items-center mb-3" style="font-size: 0.65rem;">
                    <span class="badge bg-light text-secondary border px-2 pipeline-badge">Cls: 31-60</span>
                    <i class="fa-solid fa-caret-right mx-1 text-muted"></i>
                    <span class="badge bg-light text-primary border px-2 pipeline-badge">Pipe: 1-30</span>
                    <i class="fa-solid fa-caret-right mx-1 text-muted"></i>
                    <span class="badge bg-success text-white px-2 shadow-sm pipeline-badge">Act: 1-30</span>
                </div>

                <div class="bg-light p-2 rounded-3 mb-3">
                    <span class="info-text"><i class="fa-solid fa-wallet"></i> <span>Baki Debet: Rp 15.000.000</span></span>
                    <span class="info-text"><i class="fa-solid fa-file-invoice-dollar"></i> <span class="text-success fw-bold">Totung: Rp 0 (Lancar)</span></span>
                    <span class="info-text"><i class="fa-solid fa-check-circle"></i> <span class="text-success fw-bold">Dibayar: 02 Apr 2026</span></span>
                    <span class="info-text"><i class="fa-solid fa-clock-rotate-left"></i> <span class="text-success fw-bold">Trkh Kunjungan: 02 Apr (LNS)</span></span>
                </div>
                <a href="<?= BASE_APP ?>/kunjungan-history-debitur" class="btn btn-action-history w-100 py-2">
                    <i class="fa-solid fa-clock-rotate-left me-2"></i> Lihat Riwayat Nasabah
                </a>
            </div>
        </div>

        <nav aria-label="Page navigation" class="mt-4">
            <ul class="pagination pagination-custom justify-content-center">
                <li class="page-item disabled"><a class="page-link" href="#"><i class="fa-solid fa-angle-left"></i></a></li>
                <li class="page-item active"><a class="page-link" href="#">1</a></li>
                <li class="page-item"><a class="page-link" href="#">2</a></li>
                <li class="page-item"><a class="page-link" href="#">3</a></li>
                <li class="page-item"><a class="page-link" href="#"><i class="fa-solid fa-angle-right"></i></a></li>
            </ul>
        </nav>
    </div>

    <div id="tab-summary" class="tab-content pb-3">
        
        <div class="summary-card">
            <h6 class="summary-title"><i class="fa-solid fa-phone-volume me-2 text-accent"></i>Status Kontak Kunjungan</h6>
            <a href="#" class="text-decoration-none text-dark">
                <div class="d-flex justify-content-between align-items-center p-2 rounded-3 mb-2" style="background-color: #E8F5E9; border-left: 4px solid #388E3C;">
                    <div>
                        <span class="fw-bold d-block text-success">Contacted</span>
                        <span class="small text-muted" style="font-size: 0.65rem;">Bertemu langsung / Tersambung</span>
                    </div>
                    <div class="text-end d-flex align-items-center">
                        <span class="fw-bold fs-5 text-success">50</span>
                        <i class="fa-solid fa-chevron-right ms-3 text-muted opacity-50 small"></i>
                    </div>
                </div>
            </a>
            <a href="#" class="text-decoration-none text-dark">
                <div class="d-flex justify-content-between align-items-center p-2 rounded-3" style="background-color: #FFE5E5; border-left: 4px solid #D32F2F;">
                    <div>
                        <span class="fw-bold d-block text-danger">Not Contacted</span>
                        <span class="small text-muted" style="font-size: 0.65rem;">Rumah kosong / Tidak diangkat</span>
                    </div>
                    <div class="text-end d-flex align-items-center">
                        <span class="fw-bold fs-5 text-danger">10</span>
                        <i class="fa-solid fa-chevron-right ms-3 text-muted opacity-50 small"></i>
                    </div>
                </div>
            </a>
        </div>

        <div class="summary-card">
            <h6 class="summary-title"><i class="fa-solid fa-bullseye me-2 text-accent"></i>Performa Pipeline</h6>
            <div class="d-flex justify-content-between align-items-center p-2 rounded-3 mb-2" style="background-color: #F8F9FA; border: 1px solid #E2E8F0;">
                <div>
                    <span class="fw-bold d-block text-dark" style="font-size: 0.8rem;">Sesuai / Tercapai</span>
                    <span class="small text-muted" style="font-size: 0.65rem;">Actual &le; Target Pipeline</span>
                </div>
                <div class="text-end">
                    <span class="fw-bold fs-5 text-primary">55</span>
                    <span class="small text-muted d-block" style="font-size: 0.6rem;">Dari 85 NOA</span>
                </div>
            </div>
            <div class="d-flex justify-content-between align-items-center p-2 rounded-3" style="background-color: #FFE5E5; border: 1px solid #FFCDD2;">
                <div>
                    <span class="fw-bold d-block text-danger" style="font-size: 0.8rem;">Meleset (Memburuk)</span>
                    <span class="small text-muted" style="font-size: 0.65rem;">Actual &gt; Target Pipeline</span>
                </div>
                <div class="text-end">
                    <span class="fw-bold fs-5 text-danger">30</span>
                    <span class="small text-muted d-block" style="font-size: 0.6rem;">Dari 85 NOA</span>
                </div>
            </div>
        </div>

        <div class="summary-card">
            <h6 class="summary-title"><i class="fa-solid fa-handshake me-2 text-accent"></i>Monitoring Janji Bayar (PTP)</h6>
            <div class="row g-2">
                <div class="col-6">
                    <div class="p-2 rounded-3" style="background-color: #E8F5E9; border-left: 4px solid #388E3C;">
                        <span class="stat-label text-success d-block mb-1">Menepati Janji</span>
                        <span class="stat-value d-block" style="font-size: 1rem;">15 NOA</span>
                        <span class="stat-label" style="font-size: 0.6rem;">Sudah setor (PPK/LNS)</span>
                    </div>
                </div>
                <div class="col-6">
                    <div class="p-2 rounded-3" style="background-color: #FFF0EB; border-left: 4px solid #F57C00;">
                        <span class="stat-label text-warning d-block mb-1" style="color:#F57C00!important;">Belum / Ingkar</span>
                        <span class="stat-value d-block" style="font-size: 1rem;">9 NOA</span>
                        <span class="stat-label" style="font-size: 0.6rem;">Melewati Tgl Janji</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="summary-card">
            <h6 class="summary-title"><i class="fa-solid fa-layer-group me-2 text-accent"></i>Coverage per Bucket</h6>
            <div class="row g-2 text-center">
                <div class="col-4">
                    <div class="bg-light p-2 rounded-3 border-bottom h-100" style="border-bottom-color: #D32F2F!important; border-bottom-width: 3px!important;">
                        <span class="stat-label d-block text-danger mb-1" style="font-size: 0.65rem;">Memburuk</span>
                        <span class="stat-value" style="font-size: 0.9rem;">10 <small class="text-muted fw-normal" style="font-size: 0.55rem;">/ 15 NOA</small></span>
                        <span class="d-block fw-bold text-dark mt-1" style="font-size: 0.65rem;">Rp 250 Jt</span>
                    </div>
                </div>
                <div class="col-4">
                    <div class="bg-light p-2 rounded-3 border-bottom h-100" style="border-bottom-color: #F57C00!important; border-bottom-width: 3px!important;">
                        <span class="stat-label d-block text-warning mb-1" style="color:#F57C00!important; font-size: 0.65rem;">Stay</span>
                        <span class="stat-value" style="font-size: 0.9rem;">20 <small class="text-muted fw-normal" style="font-size: 0.55rem;">/ 40 NOA</small></span>
                        <span class="d-block fw-bold text-dark mt-1" style="font-size: 0.65rem;">Rp 1.2 M</span>
                    </div>
                </div>
                <div class="col-4">
                    <div class="bg-light p-2 rounded-3 border-bottom h-100" style="border-bottom-color: #388E3C!important; border-bottom-width: 3px!important;">
                        <span class="stat-label d-block text-success mb-1" style="font-size: 0.65rem;">Perbaikan</span>
                        <span class="stat-value" style="font-size: 0.9rem;">30 <small class="text-muted fw-normal" style="font-size: 0.55rem;">/ 30 NOA</small></span>
                        <span class="d-block fw-bold text-dark mt-1" style="font-size: 0.65rem;">Rp 475 Jt</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="summary-card">
            <h6 class="summary-title"><i class="fa-solid fa-money-bill-transfer me-2 text-accent"></i>Status Bayar (Amount Call)</h6>
            <div class="row g-2">
                <div class="col-6">
                    <div class="p-2 rounded-3" style="background-color: #E8F5E9;">
                        <span class="stat-label text-success d-block mb-1"><i class="fa-solid fa-check-circle me-1"></i>Sudah Bayar</span>
                        <span class="stat-value d-block" style="font-size: 0.85rem;">Rp 125.500.000</span>
                        <span class="stat-label" style="font-size: 0.65rem;">Dari 25 NOA</span>
                    </div>
                </div>
                <div class="col-6">
                    <div class="p-2 rounded-3" style="background-color: #FFE5E5;">
                        <span class="stat-label text-danger d-block mb-1"><i class="fa-solid fa-triangle-exclamation me-1"></i>Belum Bayar</span>
                        <span class="stat-value d-block" style="font-size: 0.85rem;">Rp 450.000.000</span>
                        <span class="stat-label" style="font-size: 0.65rem;">Sisa 60 NOA</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="summary-card mb-0">
            <h6 class="summary-title"><i class="fa-solid fa-chart-pie me-2 text-accent"></i>Baki Debet per Kolektibilitas</h6>
            <div class="kolek-row">
                <span class="stat-label"><span class="badge bg-secondary me-2">Kol 1</span>Lancar <small class="text-muted fw-normal">(15 NOA)</small></span>
                <span class="stat-value text-dark" style="font-size: 0.85rem;">Rp 0</span>
            </div>
            <div class="kolek-row">
                <span class="stat-label"><span class="badge bg-primary me-2">Kol 2</span>DPK <small class="text-muted fw-normal">(40 NOA)</small></span>
                <span class="stat-value text-dark" style="font-size: 0.85rem;">Rp 1.250.000.000</span>
            </div>
            <div class="kolek-row">
                <span class="stat-label"><span class="badge bg-warning text-dark me-2">Kol 3</span>Kurang Lancar <small class="text-muted fw-normal">(15 NOA)</small></span>
                <span class="stat-value text-dark" style="font-size: 0.85rem;">Rp 450.000.000</span>
            </div>
            <div class="kolek-row">
                <span class="stat-label"><span class="badge bg-orange text-white me-2" style="background-color: #F57C00;">Kol 4</span>Diragukan <small class="text-muted fw-normal">(10 NOA)</small></span>
                <span class="stat-value text-dark" style="font-size: 0.85rem;">Rp 150.000.000</span>
            </div>
            <div class="kolek-row">
                <span class="stat-label"><span class="badge bg-danger me-2">Kol 5</span>Macet <small class="text-muted fw-normal">(5 NOA)</small></span>
                <span class="stat-value text-danger" style="font-size: 0.85rem;">Rp 75.000.000</span>
            </div>
            <div class="d-flex justify-content-between align-items-center mt-3 p-2 rounded-3" style="background-color: #F8F9FA; border: 1px solid #E2E8F0;">
                <span class="fw-bold text-dark" style="font-size: 0.8rem;">TOTAL (85 NOA)</span>
                <span class="fw-bold text-primary" style="font-size: 0.95rem;">Rp 1.925.000.000</span>
            </div>
        </div>

    </div>

</div>

<script>
function switchMappingTab(tabId, btnElement) {
    const buttons = document.querySelectorAll('.icon-tab-btn');
    buttons.forEach(btn => btn.classList.remove('active'));
    
    btnElement.classList.add('active');
    
    const contents = document.querySelectorAll('.tab-content');
    contents.forEach(content => content.classList.remove('active'));
    
    document.getElementById('tab-' + tabId).classList.add('active');
}
</script>