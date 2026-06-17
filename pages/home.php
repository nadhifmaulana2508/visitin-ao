<style>
    /* Styling khusus Home */
    .header-premium {
        background: linear-gradient(135deg, var(--color-primary), var(--color-secondary));
        color: white;
        padding: 30px 20px 60px 20px; 
        border-bottom-left-radius: 30px;
        border-bottom-right-radius: 30px;
    }
    .overlap-card {
        background-color: #ffffff;
        border-radius: 16px;
        margin: -40px 20px 20px 20px; 
        padding: 20px;
        box-shadow: 0 8px 20px rgba(0, 0, 0, 0.05);
        position: relative;
        z-index: 10;
    }
    .menu-card {
        background-color: #ffffff;
        border-radius: 16px;
        padding: 20px 10px;
        text-align: center;
        box-shadow: 0 4px 12px rgba(0,0,0,0.03);
        border: none;
        transition: transform 0.2s;
        height: 100%;
        display: flex;
        flex-direction: column;
        justify-content: center;
        align-items: center;
        text-decoration: none;
        color: inherit;
    }
    .menu-card:active { transform: scale(0.95); }
    .menu-icon-wrapper {
        width: 50px;
        height: 50px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-bottom: 12px;
        font-size: 1.5rem;
    }
    .icon-orange { background-color: #FFF0EB; color: var(--color-accent); }
    .icon-blue { background-color: #E6EDF5; color: var(--color-primary); }
    .icon-red { background-color: #FFE5E5; color: #D32F2F; }
    .icon-green { background-color: #E8F5E9; color: #388E3C; }
    .menu-title { font-size: 0.8rem; font-weight: 700; color: #2B2B2B; line-height: 1.2; }
</style>

<?php
// SIMULASI SESSION LOGIN
$role = 'remedial'; 
$nama_user = 'Budi Santoso';
$cabang = 'Cabang Utama';
?>

<div class="header-premium">
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <p class="mb-0 small text-white-50">Selamat Datang,</p>
            <h4 class="mb-0 fw-bold"><?= $nama_user; ?></h4>
        </div>
        <img src="https://ui-avatars.com/api/?name=<?= urlencode($nama_user); ?>&background=FF7B54&color=fff&rounded=true" alt="Profile" width="45" height="45" class="border border-2 border-white shadow-sm">
    </div>
</div>

<div class="overlap-card d-flex justify-content-between align-items-center">
    <div>
        <p class="mb-1 small fw-bold text-muted">Posisi / Divisi</p>
        <h5 class="mb-0 fw-bold text-dark text-uppercase">AO <?= $role; ?></h5>
        <small class="text-muted"><i class="fa-solid fa-building-columns me-1"></i> <?= $cabang; ?></small>
    </div>
    <div class="bg-light p-3 rounded-circle d-flex align-items-center justify-content-center" style="width: 50px; height: 50px;">
        <i class="fa-solid fa-id-badge fs-4 text-primary" style="color: var(--color-primary)!important;"></i>
    </div>
</div>

<div class="container px-3 mt-4 mb-4">
    <h6 class="fw-bold mb-3 text-dark" style="opacity: 0.8;">Menu Utama</h6>
    
    <div class="row g-3">
        <?php if ($role == 'remedial'): ?>
            <div class="col-6 col-md-4 col-xl-3">
                <a href="<?= BASE_APP ?>/mapping" class="menu-card">
                    <div class="menu-icon-wrapper icon-orange">
                        <i class="fa-solid fa-calendar-check"></i>
                    </div>
                    <span class="menu-title">Mapping<br>Bulan Ini</span>
                </a>
            </div>
            <div class="col-6 col-md-4 col-xl-3">
                <a href="<?= BASE_APP ?>/nominatif" class="menu-card">
                    <div class="menu-icon-wrapper icon-blue">
                        <i class="fa-solid fa-file-invoice-dollar"></i>
                    </div>
                    <span class="menu-title">Nominatif<br>Kredit</span>
                </a>
            </div>
            <div class="col-6 col-md-4 col-xl-3">
                <a href="<?= BASE_APP ?>/janji-bayar" class="menu-card">
                    <div class="menu-icon-wrapper icon-green">
                        <i class="fa-solid fa-handshake"></i>
                    </div>
                    <span class="menu-title">Janji Bayar<br>(Kunjungan)</span>
                </a>
            </div>
            <div class="col-6 col-md-4 col-xl-3">
                <a href="<?= BASE_APP ?>/hapus-buku" class="menu-card">
                    <div class="menu-icon-wrapper icon-red">
                        <i class="fa-solid fa-file-circle-xmark"></i>
                    </div>
                    <span class="menu-title">Data<br>Hapus Buku</span>
                </a>
            </div>

        <?php elseif ($role == 'kredit'): ?>
            <div class="col-6 col-md-4 col-xl-3">
                <a href="<?= BASE_APP ?>/prospek" class="menu-card">
                    <div class="menu-icon-wrapper icon-green">
                        <i class="fa-solid fa-user-check"></i>
                    </div>
                    <span class="menu-title">Prospek<br>(Dikunjungi)</span>
                </a>
            </div>
            <div class="col-6 col-md-4 col-xl-3">
                <a href="<?= BASE_APP ?>/mapping" class="menu-card">
                    <div class="menu-icon-wrapper icon-orange">
                        <i class="fa-solid fa-users-gear"></i>
                    </div>
                    <span class="menu-title">Mapping<br>Existing</span>
                </a>
            </div>
            <div class="col-6 col-md-4 col-xl-3">
                <a href="<?= BASE_APP ?>/potensi" class="menu-card">
                    <div class="menu-icon-wrapper icon-blue">
                        <i class="fa-solid fa-money-bill-trend-up"></i>
                    </div>
                    <span class="menu-title">Potensi<br>Top-Up</span>
                </a>
            </div>

        <?php elseif ($role == 'dana'): ?>
            <div class="col-6 col-md-4 col-xl-3">
                <a href="<?= BASE_APP ?>/kelola-dana" class="menu-card">
                    <div class="menu-icon-wrapper icon-blue">
                        <i class="fa-solid fa-vault"></i>
                    </div>
                    <span class="menu-title">Kelola<br>Dana</span>
                </a>
            </div>
            <div class="col-6 col-md-4 col-xl-3">
                <a href="<?= BASE_APP ?>/mapping" class="menu-card">
                    <div class="menu-icon-wrapper icon-orange">
                        <i class="fa-solid fa-calendar-day"></i>
                    </div>
                    <span class="menu-title">Jadwal Menabung<br>Hari Ini</span>
                </a>
            </div>
        <?php endif; ?>
    </div>
</div>
