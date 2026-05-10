<style>
    /* COMPACT HEADER STYLING */
    .header-compact {
        background: linear-gradient(135deg, var(--color-primary), var(--color-secondary));
        color: white; padding: 25px 20px 45px 20px; 
        border-bottom-left-radius: 25px; border-bottom-right-radius: 25px;
    }
    
    /* STYLING TAB CUSTOM */
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
        background-color: #ffffff; border-radius: 16px; margin: 0 20px 20px 20px; 
        padding: 15px; box-shadow: 0 4px 15px rgba(0, 0, 0, 0.03);
    }
    .search-input { background-color: #F4F7F6; border: 1px solid #E0E0E0; border-radius: 10px; padding: 10px 15px; font-size: 0.85rem; }
    .search-input:focus { border-color: var(--color-primary); box-shadow: none; }
    .btn-toggle-filter { background-color: #E6EDF5; color: var(--color-primary); border: none; border-radius: 10px; padding: 0 15px; transition: 0.2s; }
    .filter-label { font-size: 0.7rem; font-weight: 700; color: var(--color-primary); margin-bottom: 5px; display: block; text-transform: uppercase; }

    /* CARD HISTORY LIST */
    .history-card { border: none; border-radius: 16px; margin-bottom: 12px; background-color: #ffffff; box-shadow: 0 4px 10px rgba(0,0,0,0.02); transition: transform 0.2s; border-left: 4px solid transparent; }
    .history-card:active { transform: scale(0.98); }
    .status-success { border-left-color: #388E3C; } .status-warning { border-left-color: #F57C00; } .status-danger { border-left-color: #D32F2F; }  
    .badge-status { font-size: 0.65rem; padding: 5px 8px; border-radius: 6px; font-weight: 700; letter-spacing: 0.5px; }
    .text-date { font-size: 0.75rem; color: #A0AEC0; }
    .link-nasabah { text-decoration: none; color: inherit; transition: color 0.2s; }

    /* SUMMARY / RAPORT STYLING */
    .summary-card { background: white; border-radius: 16px; padding: 15px; margin-bottom: 15px; box-shadow: 0 4px 10px rgba(0,0,0,0.02); }
    .summary-title { font-size: 0.8rem; font-weight: 800; color: var(--color-primary); text-transform: uppercase; margin-bottom: 12px; border-bottom: 2px solid #F4F7F6; padding-bottom: 8px; }
    .progress-custom { height: 10px; border-radius: 10px; background-color: #E2E8F0; margin-bottom: 5px; }
    .progress-bar-custom { background-color: var(--color-accent); border-radius: 10px; }
    .stat-value { font-size: 1.1rem; font-weight: 800; color: #1E293B; }
    .stat-label { font-size: 0.7rem; color: #64748B; font-weight: 600; }
    
    .pagination-custom .page-link { color: var(--color-primary); border: none; margin: 0 3px; border-radius: 8px; font-weight: 600; }
    .pagination-custom .page-item.active .page-link { background-color: var(--color-primary); color: white; }
</style>

<?php
// Default Range: 1 Bulan Terakhir
$date_start_default = date('Y-m-d', strtotime('-1 month'));
$date_end_default = date('Y-m-d');
$role = 'remedial'; 
?>

<div class="header-compact">
    <div class="d-flex justify-content-between align-items-center mb-1">
        <h5 class="fw-bold mb-0"><i class="fa-solid fa-clock-rotate-left me-2"></i>Aktivitas Kunjungan</h5>
        <span class="badge bg-white text-primary rounded-pill px-3 py-2 fw-bold">Raport AO</span>
    </div>
    <p class="small text-white-50 mb-0" style="font-size: 0.75rem;">Periode: <?= date('d M Y', strtotime($date_start_default)) ?> - <?= date('d M Y', strtotime($date_end_default)) ?></p>
</div>

<div class="icon-tabs">
    <button class="icon-tab-btn active" onclick="switchHistoryTab('list', this)">
        <i class="fa-solid fa-list-ul"></i>
        <span>Riwayat Data</span>
    </button>
    <button class="icon-tab-btn" onclick="switchHistoryTab('raport', this)">
        <i class="fa-solid fa-award"></i>
        <span>Raport Kinerja</span>
    </button>
</div>

<div class="filter-wrapper">
    <form action="" method="GET">
        <div class="d-flex gap-2">
            <div class="input-group flex-grow-1">
                <span class="input-group-text bg-light border-0" style="border-radius: 10px 0 0 10px;"><i class="fa-solid fa-magnifying-glass text-muted"></i></span>
                <input type="text" class="form-control bg-light border-0" placeholder="Cari nasabah..." style="font-size: 0.9rem; border-radius: 0 10px 10px 0;">
            </div>
            <button class="btn btn-toggle-filter" type="button" data-bs-toggle="collapse" data-bs-target="#collapseFilterHistory">
                <i class="fa-solid fa-sliders"></i>
            </button>
        </div>

        <div class="collapse" id="collapseFilterHistory">
            <div class="row g-2 pt-3 mt-1 border-top">
                <div class="col-6">
                    <label class="filter-label">Mulai</label>
                    <input type="date" class="form-control search-input" value="<?= $date_start_default ?>">
                </div>
                <div class="col-6">
                    <label class="filter-label">Sampai</label>
                    <input type="date" class="form-control search-input" value="<?= $date_end_default ?>">
                </div>

                <?php if($role == 'remedial'): ?>
                <div class="col-12 mt-2">
                    <label class="filter-label">Kode Tindakan</label>
                    <select class="form-select search-input">
                        <option value="" selected>Semua Tindakan</option>
                        <option value="PTP">PTP - Promise to Pay</option>
                        <option value="PPK">PPK - Pick up Payment Collected</option>
                        <option value="LNS">LNS - Pelunasan</option>
                        <option value="RES">RES - Restruktur</option>
                    </select>
                </div>
                <?php endif; ?>

                <div class="col-12 mt-3">
                    <button type="submit" class="btn w-100 py-2 fw-bold text-white shadow-sm" style="background-color: var(--color-primary); border-radius: 10px;">
                        Tarik Data Sesuai Filter
                    </button>
                </div>
            </div>
        </div>
    </form>
</div>

<div class="container px-3 mb-5 pb-5">
    
    <div id="tab-list" class="tab-content active">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h6 class="fw-bold mb-0 text-dark">Daftar Aktivitas</h6>
            <span class="text-muted small">Ditemukan <b>124</b> Kunjungan</span>
        </div>

        <div class="card history-card status-warning">
            <div class="card-body p-3">
                <div class="d-flex justify-content-between align-items-start mb-2">
                    <div>
                        <a href="<?= BASE_APP ?>/kunjungan-history-debitur" class="link-nasabah">
                            <h6 class="fw-bold mb-0 text-dark">Bapak Haryanto <i class="fa-solid fa-up-right-from-square ms-1" style="font-size: 0.6rem;"></i></h6>
                        </a>
                        <span class="text-date">07 Apr 2026 • 10:30 WIB</span>
                    </div>
                    <span class="badge badge-status" style="background-color: #FFF0EB; color: #F57C00;">PTP</span>
                </div>
                <div class="bg-light p-2 rounded-3 mb-2" style="font-size: 0.8rem;">
                    <i class="fa-solid fa-quote-left me-1 text-muted"></i>
                    <b>Janji Bayar: Rp 2.500.000 (15/04/2026).</b>
                </div>
                <div class="d-flex justify-content-between align-items-center">
                    <small class="text-muted fw-bold" style="font-size: 0.7rem;">
                        <i class="fa-solid fa-location-dot me-1"></i> Rumah Nasabah
                    </small>
                    <a href="<?= BASE_APP ?>/kunjungan-detail" class="text-decoration-none small fw-bold" style="color: var(--color-accent);">Detail <i class="fa-solid fa-chevron-right ms-1"></i></a>
                </div>
            </div>
        </div>
        
        <div class="card history-card status-success">
            <div class="card-body p-3">
                <div class="d-flex justify-content-between align-items-start mb-2">
                    <div>
                        <a href="<?= BASE_APP ?>/kunjungan-history-debitur" class="link-nasabah">
                            <h6 class="fw-bold mb-0 text-dark">Ibu Ratna Sari <i class="fa-solid fa-up-right-from-square ms-1" style="font-size: 0.6rem;"></i></h6>
                        </a>
                        <span class="text-date">04 Apr 2026 • 14:15 WIB</span>
                    </div>
                    <span class="badge badge-status" style="background-color: #E8F5E9; color: #388E3C;">LNS</span>
                </div>
                <div class="bg-light p-2 rounded-3 mb-2" style="font-size: 0.8rem;">
                    <i class="fa-solid fa-quote-left me-1 text-muted"></i>
                    Pelunasan dipercepat.
                </div>
                <div class="d-flex justify-content-between align-items-center">
                    <small class="text-muted fw-bold" style="font-size: 0.7rem;">
                        <i class="fa-solid fa-location-dot me-1"></i> Tempat Usaha
                    </small>
                    <a href="<?= BASE_APP ?>/kunjungan-detail" class="text-decoration-none small fw-bold" style="color: var(--color-accent);">Detail <i class="fa-solid fa-chevron-right ms-1"></i></a>
                </div>
            </div>
        </div>

        <nav aria-label="Page navigation" class="mt-4">
            <ul class="pagination pagination-custom justify-content-center">
                <li class="page-item disabled"><a class="page-link" href="#"><i class="fa-solid fa-angle-left"></i></a></li>
                <li class="page-item active"><a class="page-link" href="#">1</a></li>
                <li class="page-item"><a class="page-link" href="#">2</a></li>
                <li class="page-item"><a class="page-link" href="#"><i class="fa-solid fa-angle-right"></i></a></li>
            </ul>
        </nav>
    </div>

    <div id="tab-raport" class="tab-content pb-3">
        <h6 class="fw-bold mb-3 text-dark">Raport Kinerja (Berdasarkan Filter)</h6>
        
        <div class="summary-card" style="background: linear-gradient(135deg, #388E3C, #2E7D32); color: white; border: none;">
            <h6 class="summary-title text-white border-white border-opacity-25"><i class="fa-solid fa-sack-dollar me-2"></i>Amount Collect</h6>
            <span class="d-block text-white-50" style="font-size: 0.7rem; font-weight: 600;">Total Setoran/Pelunasan yang ditagih:</span>
            <h2 class="fw-bold mb-0 mt-1">Rp 125.500.000</h2>
            <div class="mt-2 pt-2 border-top border-white border-opacity-25 d-flex justify-content-between">
                <span style="font-size: 0.7rem; font-weight: 600;"><i class="fa-solid fa-file-invoice-dollar me-1"></i> PPK: Rp 25.500.000</span>
                <span style="font-size: 0.7rem; font-weight: 600;"><i class="fa-solid fa-check-double me-1"></i> LNS: Rp 100.000.000</span>
            </div>
        </div>

        <div class="summary-card">
            <h6 class="summary-title"><i class="fa-solid fa-person-walking-arrow-right me-2 text-accent"></i>Cakupan Kunjungan (Coverage)</h6>
            <div class="d-flex justify-content-between mb-1">
                <span class="stat-label">Ter-kunjungi vs Target</span>
                <span class="stat-label fw-bold text-dark">85 / 85 NOA (100%)</span>
            </div>
            <div class="progress progress-custom">
                <div class="progress-bar progress-bar-custom bg-success" role="progressbar" style="width: 100%;"></div>
            </div>
            <div class="d-flex justify-content-between mt-3 pt-2 border-top">
                <span class="stat-label">Frekuensi Kunjungan Total</span>
                <span class="stat-value text-accent" style="font-size: 0.95rem;"><i class="fa-solid fa-stopwatch me-1"></i> 200 Kali</span>
            </div>
        </div>

        <div class="summary-card">
            <h6 class="summary-title"><i class="fa-solid fa-bullseye me-2 text-accent"></i>Kepatuhan Pipeline</h6>
            <div class="row g-2">
                <div class="col-6">
                    <div class="bg-light p-2 rounded-3 text-center h-100" style="border-bottom: 3px solid #388E3C;">
                        <span class="stat-label d-block text-success mb-1">Sesuai / Membaik</span>
                        <span class="stat-value fs-4 d-block">80</span>
                        <span class="small text-muted" style="font-size: 0.6rem;">NOA Target Terjaga</span>
                    </div>
                </div>
                <div class="col-6">
                    <div class="bg-light p-2 rounded-3 text-center h-100" style="border-bottom: 3px solid #D32F2F;">
                        <span class="stat-label d-block text-danger mb-1">Meleset / Memburuk</span>
                        <span class="stat-value fs-4 d-block">5</span>
                        <span class="small text-muted" style="font-size: 0.6rem;">NOA Lewat Target</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="summary-card mb-0">
            <h6 class="summary-title"><i class="fa-solid fa-chart-pie me-2 text-accent"></i>Rincian Tindakan Kunjungan</h6>
            <div class="row g-2 text-center">
                <div class="col-4">
                    <div class="card border-0 shadow-sm rounded-3" style="background-color: #FFF0EB;">
                        <div class="card-body p-2">
                            <h5 class="fw-bold mb-0 text-orange" style="color: #F57C00;">52</h5>
                            <small class="fw-bold text-muted" style="font-size: 0.65rem;">Janji Bayar (PTP)</small>
                        </div>
                    </div>
                </div>
                <div class="col-4">
                    <div class="card border-0 shadow-sm rounded-3" style="background-color: #E8F5E9;">
                        <div class="card-body p-2">
                            <h5 class="fw-bold text-success mb-0">28</h5>
                            <small class="fw-bold text-muted" style="font-size: 0.65rem;">Lunas (LNS/PPK)</small>
                        </div>
                    </div>
                </div>
                <div class="col-4">
                    <div class="card border-0 shadow-sm rounded-3" style="background-color: #FFE5E5;">
                        <div class="card-body p-2">
                            <h5 class="fw-bold text-danger mb-0">12</h5>
                            <small class="fw-bold text-muted" style="font-size: 0.65rem;">Gagal Bertemu</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>

<script>
function switchHistoryTab(tabId, btnElement) {
    const buttons = document.querySelectorAll('.icon-tab-btn');
    buttons.forEach(btn => btn.classList.remove('active'));
    
    btnElement.classList.add('active');
    
    const contents = document.querySelectorAll('.tab-content');
    contents.forEach(content => content.classList.remove('active'));
    
    document.getElementById('tab-' + tabId).classList.add('active');
}
</script>