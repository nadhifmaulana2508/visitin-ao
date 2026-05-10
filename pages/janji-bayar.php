<style>
    .header-premium {
        background: linear-gradient(135deg, var(--color-primary), var(--color-secondary));
        color: white; padding: 30px 20px 80px 20px; 
        border-bottom-left-radius: 30px; border-bottom-right-radius: 30px;
    }
    
    /* STYLING TAB CUSTOM */
    .filter-tabs {
        display: flex; background: #ffffff; border-radius: 15px; padding: 5px;
        margin: -40px 20px 20px 20px; box-shadow: 0 8px 20px rgba(0,0,0,0.05);
        position: relative; z-index: 10;
    }
    .filter-tab-btn {
        flex: 1; text-align: center; padding: 12px 0; background: transparent; border: none; 
        border-radius: 12px; color: #94A3B8; font-weight: 700; font-size: 0.85rem; transition: all 0.3s ease; cursor: pointer;
    }
    .filter-tab-btn.active {
        background-color: var(--color-primary); color: white;
        box-shadow: 0 4px 10px rgba(10, 25, 49, 0.2);
    }
    .filter-tab-btn i { margin-right: 5px; }
    
    /* Logic Tampilan Konten */
    .tab-content { display: none; animation: fadeIn 0.3s ease; }
    .tab-content.active { display: block; }
    @keyframes fadeIn { from { opacity: 0; transform: translateY(5px); } to { opacity: 1; transform: translateY(0); } }

    /* CARD STYLING */
    .task-card {
        border: none; border-radius: 16px; margin-bottom: 15px; background-color: #ffffff;
        box-shadow: 0 4px 12px rgba(0,0,0,0.03); border-left: 5px solid transparent;
    }
    .task-card-warning { border-left-color: #F57C00; } /* Untuk Belum Bayar */
    .task-card-success { border-left-color: #388E3C; } /* Untuk Sudah Bayar */
    
    .nominal-text { font-size: 1.1rem; font-weight: 800; color: var(--color-primary); display: block; margin-bottom: 5px; }
    
    .btn-action {
        background-color: #F8F9FA; color: var(--color-primary); border: 1px solid #E0E0E0;
        border-radius: 10px; font-weight: 700; transition: all 0.2s; font-size: 0.8rem;
    }
    .btn-action-primary {
        background-color: var(--color-accent); color: white; border: none;
    }
    .btn-action:hover, .btn-action-primary:hover { transform: translateY(-2px); box-shadow: 0 4px 10px rgba(0,0,0,0.1); color: white;}
</style>

<div class="header-premium">
    <div class="d-flex align-items-center mb-2">
        <a href="<?= BASE_APP ?>/home" class="text-white me-3"><i class="fa-solid fa-arrow-left fs-5"></i></a>
        <h5 class="fw-bold mb-0">Daftar Janji Bayar</h5>
    </div>
    <p class="small text-white-50 ms-4 ps-2 mb-0">Follow up nasabah PTP / Janji Bayar bulan ini.</p>
</div>

<div class="filter-tabs">
    <button class="filter-tab-btn active" onclick="switchJanjiTab('belum', this)">
        <i class="fa-solid fa-clock"></i> Belum Bayar
    </button>
    <button class="filter-tab-btn" onclick="switchJanjiTab('sudah', this)">
        <i class="fa-solid fa-check-circle"></i> Sudah Bayar
    </button>
</div>

<div class="container px-3 mb-5 pb-5">
    
    <div id="tab-belum" class="tab-content active">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h6 class="fw-bold mb-0 text-dark">Perlu Dikunjungi <span class="badge bg-danger rounded-pill ms-1">2</span></h6>
        </div>

        <div class="card task-card task-card-warning">
            <div class="card-body p-3">
                <div class="d-flex justify-content-between align-items-start mb-2">
                    <h6 class="fw-bold mb-0 text-dark">Bapak Supriyadi (Toko Makmur)</h6>
                    <span class="badge bg-danger text-white" style="font-size: 0.6rem;"><i class="fa-solid fa-triangle-exclamation me-1"></i> HARI INI</span>
                </div>
                
                <div class="bg-light p-2 rounded-3 mb-3">
                    <span class="small text-muted fw-bold text-uppercase" style="font-size: 0.65rem;">Nominal Janji Bayar</span>
                    <span class="nominal-text">Rp 2.500.000</span>
                    <span class="small text-muted"><i class="fa-solid fa-location-dot me-1"></i> Jl. Pemuda No. 12</span>
                </div>

                <div class="row g-2">
                    <div class="col-12">
                        <a href="<?= BASE_APP ?>/kunjungan-create" class="btn btn-action-primary w-100 py-2">
                            <i class="fa-solid fa-person-walking-arrow-right me-2"></i> Kunjungi Sekarang
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <div class="card task-card task-card-warning">
            <div class="card-body p-3">
                <div class="d-flex justify-content-between align-items-start mb-2">
                    <h6 class="fw-bold mb-0 text-dark">Ibu Siti Aminah</h6>
                    <span class="badge" style="background-color: #FFF0EB; color: #F57C00; font-size: 0.6rem;">BESOK, 09 APR</span>
                </div>
                
                <div class="bg-light p-2 rounded-3 mb-3">
                    <span class="small text-muted fw-bold text-uppercase" style="font-size: 0.65rem;">Nominal Janji Bayar</span>
                    <span class="nominal-text">Rp 1.000.000</span>
                    <span class="small text-muted"><i class="fa-solid fa-location-dot me-1"></i> Perumahan Asri Blok B</span>
                </div>

                <div class="row g-2">
                    <div class="col-12">
                        <a href="<?= BASE_APP ?>/kunjungan-create" class="btn btn-action w-100 py-2">
                            <i class="fa-solid fa-calendar-plus me-2"></i> Jadwalkan Kunjungan
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div id="tab-sudah" class="tab-content">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h6 class="fw-bold mb-0 text-dark">Diselesaikan Bulan Ini</h6>
        </div>

        <div class="card task-card task-card-success">
            <div class="card-body p-3">
                <div class="d-flex justify-content-between align-items-start mb-2">
                    <h6 class="fw-bold mb-0 text-dark">PT. Maju Bersama</h6>
                    <span class="badge bg-success text-white" style="font-size: 0.6rem;"><i class="fa-solid fa-check me-1"></i> LUNAS</span>
                </div>
                
                <div class="bg-light p-2 rounded-3 mb-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <span class="small text-muted fw-bold text-uppercase" style="font-size: 0.65rem;">Realisasi Bayar</span>
                            <span class="nominal-text mb-0" style="color: #388E3C;">Rp 15.000.000</span>
                        </div>
                        <div class="text-end">
                            <span class="small text-muted fw-bold text-uppercase" style="font-size: 0.65rem;">Tanggal</span>
                            <span class="d-block fw-bold text-dark" style="font-size: 0.8rem;">05 Apr 2026</span>
                        </div>
                    </div>
                </div>

                <div class="row g-2">
                    <div class="col-12">
                        <a href="<?= BASE_APP ?>/kunjungan-history-debitur" class="btn btn-action w-100 py-2">
                            <i class="fa-solid fa-clock-rotate-left me-2"></i> Lihat Riwayat
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

</div>

<script>
function switchJanjiTab(tabId, btnElement) {
    // 1. Hilangkan class active dari semua tombol tab
    const buttons = document.querySelectorAll('.filter-tab-btn');
    buttons.forEach(btn => btn.classList.remove('active'));
    
    // 2. Tambahkan class active ke tombol yang diklik
    btnElement.classList.add('active');
    
    // 3. Sembunyikan semua konten tab
    const contents = document.querySelectorAll('.tab-content');
    contents.forEach(content => content.classList.remove('active'));
    
    // 4. Munculkan konten yang sesuai dengan id
    document.getElementById('tab-' + tabId).classList.add('active');
}
</script>