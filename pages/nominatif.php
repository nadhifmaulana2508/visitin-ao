<style>
    /* COMPACT HEADER STYLING */
    .header-compact {
        background: linear-gradient(135deg, var(--color-primary), var(--color-secondary));
        color: white; padding: 25px 20px 45px 20px; 
        border-bottom-left-radius: 25px; border-bottom-right-radius: 25px;
    }
    
    .overlap-card {
        background-color: #ffffff; border-radius: 16px; margin: -25px 20px 15px 20px; 
        padding: 20px; box-shadow: 0 8px 20px rgba(0, 0, 0, 0.05); position: relative; z-index: 10;
    }

    /* STYLING FILTER COLLAPSIBLE */
    .filter-wrapper {
        background-color: #ffffff; border-radius: 16px; margin: 0 20px 15px 20px; 
        padding: 15px; box-shadow: 0 4px 15px rgba(0, 0, 0, 0.03);
    }
    .search-input { background-color: #F4F7F6; border: 1px solid #E0E0E0; border-radius: 10px; padding: 10px 15px; font-size: 0.85rem; }
    .search-input:focus { border-color: var(--color-primary); box-shadow: none; }
    .btn-toggle-filter { background-color: #E6EDF5; color: var(--color-primary); border: none; border-radius: 10px; padding: 0 15px; transition: 0.2s; }
    .filter-label { font-size: 0.7rem; font-weight: 700; color: var(--color-primary); margin-bottom: 5px; display: block; text-transform: uppercase; }

    /* CARD STYLING & STATUS */
    .mapping-card { border: none; border-radius: 16px; margin-bottom: 12px; background-color: #ffffff; box-shadow: 0 4px 12px rgba(0,0,0,0.03); transition: transform 0.2s; }
    .mapping-card:active { transform: scale(0.98); }
    
    /* Warna Bucket */
    .bucket-memburuk { background-color: #D32F2F; color: white; }
    .bucket-stay { background-color: #F57C00; color: white; }
    .bucket-perbaikan { background-color: #388E3C; color: white; }

    /* Warna Kolektibilitas */
    .kolek-1 { background-color: #E2E8F0; color: #475569; }
    .kolek-2 { background-color: #E3F2FD; color: #1976D2; }
    .kolek-3 { background-color: #FFF9C4; color: #F57F17; }
    .kolek-4 { background-color: #FFE0B2; color: #E65100; }
    .kolek-5 { background-color: #FFCDD2; color: #C62828; }

    .badge-role { font-size: 0.7rem; padding: 6px 10px; border-radius: 6px; font-weight: 700; }
    .badge-bucket { font-size: 0.65rem; padding: 4px 8px; border-radius: 20px; font-weight: 700; letter-spacing: 0.5px; }
    .info-text { font-size: 0.75rem; color: #64748B; margin-bottom: 4px; display: flex; align-items: flex-start;}
    .info-text i { width: 16px; margin-top: 3px; color: #A0AEC0; }

    /* DUAL BUTTONS STYLING */
    .btn-action-main { background-color: #F8F9FA; color: var(--color-primary); border: 1px solid #E0E0E0; border-radius: 10px; font-weight: 700; transition: all 0.2s; font-size: 0.85rem; }
    .btn-action-main:hover { background-color: var(--color-primary); color: white; border-color: var(--color-primary); }
    .btn-action-history { background-color: #ffffff; color: var(--color-accent); border: 1px solid #E0E0E0; border-radius: 10px; transition: all 0.2s; display: flex; align-items: center; justify-content: center; }
    .btn-action-history:hover { background-color: #FFF0EB; border-color: var(--color-accent); color: var(--color-accent); }
    
    .pagination-custom .page-link { color: var(--color-primary); border: none; margin: 0 3px; border-radius: 8px; font-weight: 600; }
    .pagination-custom .page-item.active .page-link { background-color: var(--color-primary); color: white; }
</style>

<?php
// SIMULASI LOGIN (Ganti 'pusat' atau 'cabang' untuk lihat beda filternya)
$level_user = 'pusat'; 
// $level_user = 'cabang';
?>

<div class="header-compact">
    <div class="d-flex align-items-center mb-1">
        <a href="<?= BASE_APP ?>/home" class="text-white me-3"><i class="fa-solid fa-arrow-left fs-5"></i></a>
        <h5 class="fw-bold mb-0">Data Nominatif Kredit</h5>
    </div>
</div>

<div class="overlap-card d-flex justify-content-between align-items-center">
    <div>
        <p class="mb-1 small fw-bold text-muted">Total Baki Debet Filter</p>
        <h3 class="mb-0 fw-bold text-dark">
            <span class="text-accent" style="font-size: 1.2rem;">Rp</span> 15.4<span style="font-size: 1.2rem;">M</span>
        </h3>
        <span class="badge bg-light text-primary border mt-2">Dari 120 NOA</span>
    </div>
    <div class="bg-light p-3 rounded-circle d-flex align-items-center justify-content-center" style="width: 50px; height: 50px;">
        <i class="fa-solid fa-database fs-4" style="color: var(--color-accent);"></i>
    </div>
</div>

<div class="filter-wrapper">
    <form action="" method="GET">
        <div class="d-flex gap-2">
            <div class="input-group flex-grow-1">
                <span class="input-group-text bg-light border-0" style="border-radius: 10px 0 0 10px;"><i class="fa-solid fa-magnifying-glass text-muted"></i></span>
                <input type="text" class="form-control bg-light border-0" placeholder="Cari nama nasabah..." style="font-size: 0.9rem; border-radius: 0 10px 10px 0;">
            </div>
            <button class="btn btn-toggle-filter" type="button" data-bs-toggle="collapse" data-bs-target="#collapseFilterNominatif">
                <i class="fa-solid fa-sliders"></i>
            </button>
        </div>

        <div class="collapse" id="collapseFilterNominatif">
            <div class="row g-2 pt-3 mt-1 border-top">
                
                <?php if($level_user == 'pusat'): ?>
                <div class="col-12">
                    <label class="filter-label">Konsolidasi Cabang</label>
                    <select class="form-select search-input fw-bold text-primary">
                        <option value="all" selected>Seluruh Cabang (Pusat)</option>
                        <option value="cabang_utama">Cabang Utama</option>
                        <option value="cabang_barat">Cabang Barat</option>
                    </select>
                </div>
                <?php endif; ?>

                <div class="col-6 mt-2">
                    <label class="filter-label">Kantor Kas</label>
                    <select class="form-select search-input">
                        <option value="" selected>Semua Kankas</option>
                        <option value="kas_pasar">Kas Pasar Induk</option>
                        <option value="kas_pelabuhan">Kas Pelabuhan</option>
                    </select>
                </div>
                
                <div class="col-6 mt-2">
                    <label class="filter-label">Kolektibilitas</label>
                    <select class="form-select search-input">
                        <option value="" selected>Semua Kolek</option>
                        <option value="1">Kol 1 - Lancar</option>
                        <option value="2">Kol 2 - DPK</option>
                        <option value="3">Kol 3 - Kurang Lancar</option>
                        <option value="4">Kol 4 - Diragukan</option>
                        <option value="5">Kol 5 - Macet</option>
                    </select>
                </div>

                <div class="col-6 mt-2">
                    <label class="filter-label">Kecamatan</label>
                    <select class="form-select search-input">
                        <option value="" selected>Semua Kec...</option>
                        <option value="Semarang Tengah">Semarang Tengah</option>
                    </select>
                </div>
                
                <div class="col-6 mt-2">
                    <label class="filter-label">Desa / Kelurahan</label>
                    <select class="form-select search-input">
                        <option value="" selected>Semua Desa...</option>
                        <option value="Pendrikan">Pendrikan</option>
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

<div class="container px-3 mt-2 mb-5 pb-5">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h6 class="fw-bold mb-0 text-dark">Data Nominatif</h6>
        <span class="text-muted small">Menampilkan <b>5</b> dari 120 NOA</span>
    </div>

    <div class="card mapping-card border-left" style="border-left: 5px solid #F57F17;">
        <div class="card-body p-3">
            <div class="d-flex justify-content-between align-items-start mb-2">
                <span class="badge kolek-3 badge-role"><i class="fa-solid fa-triangle-exclamation me-1"></i> Kol 3 (Kurang Lancar)</span>
                <span class="badge bucket-memburuk badge-bucket"><i class="fa-solid fa-arrow-trend-down me-1"></i> Bucket Memburuk</span>
            </div>
            <h6 class="fw-bold mb-1 text-dark">Bapak Supriyadi (Toko Makmur)</h6>
            
            <div class="d-flex align-items-center mb-3">
                <span class="badge bg-light text-primary border px-2 py-1" style="font-size: 0.65rem;">
                    <i class="fa-solid fa-user-tie me-1"></i> AO: Budi Santoso
                </span>
                <span class="badge bg-light text-secondary border px-2 py-1 ms-2" style="font-size: 0.65rem;">
                    <i class="fa-solid fa-building me-1"></i> Kas Pasar Induk
                </span>
            </div>

            <div class="bg-light p-2 rounded-3 mb-3">
                <span class="info-text"><i class="fa-solid fa-wallet"></i> <span>Baki Debet: Rp 45.000.000</span></span>
                <span class="info-text"><i class="fa-solid fa-file-invoice-dollar"></i> <span class="text-danger fw-bold">Totung: Rp 5.000.000</span></span>
                <span class="info-text"><i class="fa-solid fa-map-location-dot"></i> <span>Kec. Semarang Tengah, Kel. Pendrikan</span></span>
            </div>
            
            <div class="d-flex gap-2">
                <a href="<?= BASE_APP ?>/kunjungan-create" class="btn btn-action-main flex-grow-1 py-2">
                    <i class="fa-solid fa-person-walking-arrow-right me-1"></i> Buat Kunjungan
                </a>
                <a href="<?= BASE_APP ?>/kunjungan-history-debitur" class="btn btn-action-history px-3 py-2" title="Lihat Riwayat">
                    <i class="fa-solid fa-clock-rotate-left fs-5"></i>
                </a>
            </div>
        </div>
    </div>

    <div class="card mapping-card border-left" style="border-left: 5px solid #1976D2;">
        <div class="card-body p-3">
            <div class="d-flex justify-content-between align-items-start mb-2">
                <span class="badge kolek-2 badge-role"><i class="fa-solid fa-circle-exclamation me-1"></i> Kol 2 (DPK)</span>
                <span class="badge bucket-stay badge-bucket"><i class="fa-solid fa-minus me-1"></i> Bucket Stay</span>
            </div>
            <h6 class="fw-bold mb-1 text-dark">PT. Maju Bersama</h6>
            
            <div class="d-flex align-items-center mb-3">
                <span class="badge bg-light text-primary border px-2 py-1" style="font-size: 0.65rem;">
                    <i class="fa-solid fa-user-tie me-1"></i> AO: Budi Santoso
                </span>
                <span class="badge bg-light text-secondary border px-2 py-1 ms-2" style="font-size: 0.65rem;">
                    <i class="fa-solid fa-building me-1"></i> Cabang Utama
                </span>
            </div>

            <div class="bg-light p-2 rounded-3 mb-3">
                <span class="info-text"><i class="fa-solid fa-wallet"></i> <span>Baki Debet: Rp 120.000.000</span></span>
                <span class="info-text"><i class="fa-solid fa-file-invoice-dollar"></i> <span class="text-warning fw-bold" style="color: #F57C00!important;">Totung: Rp 12.000.000</span></span>
                <span class="info-text"><i class="fa-solid fa-map-location-dot"></i> <span>Kec. Semarang Utara, Kel. Pindrikan Lor</span></span>
            </div>
            
            <div class="d-flex gap-2">
                <a href="<?= BASE_APP ?>/kunjungan-create" class="btn btn-action-main flex-grow-1 py-2">
                    <i class="fa-solid fa-person-walking-arrow-right me-1"></i> Buat Kunjungan
                </a>
                <a href="<?= BASE_APP ?>/kunjungan-history-debitur" class="btn btn-action-history px-3 py-2" title="Lihat Riwayat">
                    <i class="fa-solid fa-clock-rotate-left fs-5"></i>
                </a>
            </div>
        </div>
    </div>

    <div class="card mapping-card border-left" style="border-left: 5px solid #C62828;">
        <div class="card-body p-3">
            <div class="d-flex justify-content-between align-items-start mb-2">
                <span class="badge kolek-5 badge-role"><i class="fa-solid fa-skull-crossbones me-1"></i> Kol 5 (Macet)</span>
                <span class="badge bucket-perbaikan badge-bucket"><i class="fa-solid fa-arrow-trend-up me-1"></i> Bucket Perbaikan</span>
            </div>
            <h6 class="fw-bold mb-1 text-dark">Ibu Siti Aminah (Katering)</h6>
            
            <div class="d-flex align-items-center mb-3">
                <span class="badge bg-light border px-2 py-1" style="font-size: 0.65rem; color: #E65100;">
                    <i class="fa-solid fa-user-tie me-1"></i> AO: Andi Setiawan
                </span>
                <span class="badge bg-light text-secondary border px-2 py-1 ms-2" style="font-size: 0.65rem;">
                    <i class="fa-solid fa-building me-1"></i> Cabang Barat
                </span>
            </div>

            <div class="bg-light p-2 rounded-3 mb-3">
                <span class="info-text"><i class="fa-solid fa-wallet"></i> <span>Baki Debet: Rp 15.000.000</span></span>
                <span class="info-text"><i class="fa-solid fa-file-invoice-dollar"></i> <span class="text-danger fw-bold">Totung: Rp 8.000.000</span></span>
                <span class="info-text"><i class="fa-solid fa-map-location-dot"></i> <span>Kec. Semarang Barat, Kel. Krobokan</span></span>
            </div>
            
            <div class="d-flex gap-2">
                <a href="<?= BASE_APP ?>/kunjungan-create" class="btn btn-action-main flex-grow-1 py-2">
                    <i class="fa-solid fa-person-walking-arrow-right me-1"></i> Buat Kunjungan
                </a>
                <a href="<?= BASE_APP ?>/kunjungan-history-debitur" class="btn btn-action-history px-3 py-2" title="Lihat Riwayat">
                    <i class="fa-solid fa-clock-rotate-left fs-5"></i>
                </a>
            </div>
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