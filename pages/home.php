<?php
$role = $user_role ?? 'staff';
$nama_user = $user_name ?? 'User';
$branch = $_SESSION['user_data']['branch'] ?? 'Cabang';
$perms = $user_permissions ?? [];
?>

<style>
    .header-premium {
        background: linear-gradient(135deg, var(--color-primary), var(--color-secondary));
        color: white; padding: 30px 20px 60px 20px;
        border-bottom-left-radius: 30px; border-bottom-right-radius: 30px;
    }
    .overlap-card {
        background-color: #ffffff; border-radius: 16px; margin: -40px 20px 20px 20px;
        padding: 20px; box-shadow: 0 8px 20px rgba(0,0,0,0.05);
        position: relative; z-index: 10;
    }
    .menu-card {
        background-color: #ffffff; border-radius: 16px; padding: 18px 10px;
        text-align: center; box-shadow: 0 4px 12px rgba(0,0,0,0.03);
        border: none; transition: transform 0.2s; height: 100%;
        display: flex; flex-direction: column; justify-content: center;
        align-items: center; text-decoration: none; color: inherit;
    }
    .menu-card:active { transform: scale(0.95); }
    .menu-icon-wrapper {
        width: 48px; height: 48px; border-radius: 12px;
        display: flex; align-items: center; justify-content: center;
        margin-bottom: 10px; font-size: 1.4rem;
    }
    .icon-orange { background-color: #FFF0EB; color: var(--color-accent); }
    .icon-blue { background-color: #E6EDF5; color: var(--color-primary); }
    .icon-red { background-color: #FFE5E5; color: #D32F2F; }
    .icon-green { background-color: #E8F5E9; color: #388E3C; }
    .icon-purple { background-color: #F3E5F5; color: #7B1FA2; }
    .icon-cyan { background-color: #E0F7FA; color: #00838F; }
    .menu-title { font-size: 0.75rem; font-weight: 700; color: #2B2B2B; line-height: 1.3; }

    .section-heading {
        font-size: 0.8rem; font-weight: 800; color: #475569;
        text-transform: uppercase; letter-spacing: 0.5px;
        margin-bottom: 12px; padding-left: 5px;
    }

    .role-badge {
        font-size: 0.65rem; padding: 4px 10px; border-radius: 8px;
        font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px;
    }
    .role-developer { background: #FFF0EB; color: var(--color-accent); }
    .role-ao_kredit { background: #E3F2FD; color: #1565C0; }
    .role-ao_dana { background: #E8F5E9; color: #2E7D32; }
    .role-ao_remedial { background: #FFEBEE; color: #C62828; }
    .role-superuser { background: #F3E5F5; color: #6A1B9A; }
    .role-staff { background: #F1F5F9; color: #475569; }
</style>

<div class="header-premium">
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <p class="mb-0 small text-white-50">Selamat Datang,</p>
            <h4 class="mb-0 fw-bold" style="font-size:1.2rem;"><?= htmlspecialchars($nama_user) ?></h4>
        </div>
        <a href="<?= BASE_APP ?>/profile">
            <img src="https://ui-avatars.com/api/?name=<?= urlencode($nama_user) ?>&background=FF7B54&color=fff&rounded=true&size=90"
                 alt="Profile" width="45" height="45" class="border border-2 border-white shadow-sm" style="border-radius:50%;">
        </a>
    </div>
</div>

<div class="overlap-card d-flex justify-content-between align-items-center">
    <div>
        <p class="mb-1 small fw-bold text-muted" style="font-size:0.7rem;">Posisi / Role</p>
        <h6 class="mb-0 fw-bold text-dark"><?= ucwords(str_replace('_', ' ', $role)) ?></h6>
        <small class="text-muted" style="font-size:0.7rem;"><i class="fa-solid fa-building-columns me-1"></i><?= htmlspecialchars($branch) ?></small>
    </div>
    <span class="role-badge role-<?= $role ?>"><?= strtoupper(str_replace('_', ' ', $role)) ?></span>
</div>

<div class="container px-3 mt-3 mb-5 pb-5">

    <!-- MENU PROSPEK (Semua role bisa akses) -->
    <h6 class="section-heading"><i class="fa-solid fa-bullseye me-2 text-accent"></i>Prospek</h6>
    <div class="row g-3 mb-4">
        <div class="col-4">
            <a href="<?= BASE_APP ?>/input-prospek" class="menu-card">
                <div class="menu-icon-wrapper icon-green">
                    <i class="fa-solid fa-plus"></i>
                </div>
                <span class="menu-title">Input<br>Prospek</span>
            </a>
        </div>
        <div class="col-4">
            <a href="<?= BASE_APP ?>/daftar-prospek" class="menu-card">
                <div class="menu-icon-wrapper icon-blue">
                    <i class="fa-solid fa-list-check"></i>
                </div>
                <span class="menu-title">Daftar<br>Prospek</span>
            </a>
        </div>

        <?php if ($is_superuser = in_array($role, ['superuser', 'developer'])): ?>
        <div class="col-4">
            <a href="<?= BASE_APP ?>/daftar-prospek" class="menu-card">
                <div class="menu-icon-wrapper icon-purple">
                    <i class="fa-solid fa-user-gear"></i>
                </div>
                <span class="menu-title">Delegasi<br>Prospek</span>
            </a>
        </div>
        <?php endif; ?>

        <?php if (in_array($role, ['ao_kredit', 'developer'])): ?>
        <div class="col-4">
            <a href="<?= BASE_APP ?>/daftar-prospek" class="menu-card">
                <div class="menu-icon-wrapper icon-orange">
                    <i class="fa-solid fa-chart-line"></i>
                </div>
                <span class="menu-title">Pipeline<br>Kredit</span>
            </a>
        </div>
        <?php endif; ?>

        <?php if (in_array($role, ['ao_dana', 'developer'])): ?>
        <div class="col-4">
            <a href="<?= BASE_APP ?>/daftar-prospek" class="menu-card">
                <div class="menu-icon-wrapper icon-cyan">
                    <i class="fa-solid fa-piggy-bank"></i>
                </div>
                <span class="menu-title">Pipeline<br>Dana</span>
            </a>
        </div>
        <?php endif; ?>
    </div>

    <!-- MENU KUNJUNGAN / MAPPING (AO & Superuser) -->
    <?php if (in_array($role, ['ao_kredit', 'ao_remedial', 'superuser', 'developer'])): ?>
    <h6 class="section-heading"><i class="fa-solid fa-person-walking-arrow-right me-2 text-accent"></i>Kunjungan & Mapping</h6>
    <div class="row g-3 mb-4">
        <?php if (in_array($role, ['ao_remedial', 'developer'])): ?>
        <div class="col-4">
            <a href="<?= BASE_APP ?>/mapping" class="menu-card">
                <div class="menu-icon-wrapper icon-orange">
                    <i class="fa-solid fa-calendar-check"></i>
                </div>
                <span class="menu-title">Mapping<br>Bulan Ini</span>
            </a>
        </div>
        <div class="col-4">
            <a href="<?= BASE_APP ?>/janji-bayar" class="menu-card">
                <div class="menu-icon-wrapper icon-green">
                    <i class="fa-solid fa-handshake"></i>
                </div>
                <span class="menu-title">Janji<br>Bayar</span>
            </a>
        </div>
        <div class="col-4">
            <a href="<?= BASE_APP ?>/hapus-buku" class="menu-card">
                <div class="menu-icon-wrapper icon-red">
                    <i class="fa-solid fa-file-circle-xmark"></i>
                </div>
                <span class="menu-title">Hapus<br>Buku</span>
            </a>
        </div>
        <?php endif; ?>

        <?php if (in_array($role, ['ao_kredit', 'developer'])): ?>
        <div class="col-4">
            <a href="<?= BASE_APP ?>/mapping" class="menu-card">
                <div class="menu-icon-wrapper icon-orange">
                    <i class="fa-solid fa-users-gear"></i>
                </div>
                <span class="menu-title">Mapping<br>Existing</span>
            </a>
        </div>
        <?php endif; ?>

        <div class="col-4">
            <a href="<?= BASE_APP ?>/nominatif" class="menu-card">
                <div class="menu-icon-wrapper icon-blue">
                    <i class="fa-solid fa-file-invoice-dollar"></i>
                </div>
                <span class="menu-title">Nominatif<br>Kredit</span>
            </a>
        </div>

        <?php if (in_array($role, ['superuser', 'developer'])): ?>
        <div class="col-4">
            <a href="<?= BASE_APP ?>/mapping" class="menu-card">
                <div class="menu-icon-wrapper icon-purple">
                    <i class="fa-solid fa-chart-column"></i>
                </div>
                <span class="menu-title">Monitoring<br>Mapping</span>
            </a>
        </div>
        <?php endif; ?>
    </div>
    <?php endif; ?>

    <!-- MENU LAPORAN (Superuser & Developer) -->
    <?php if (in_array($role, ['superuser', 'developer'])): ?>
    <h6 class="section-heading"><i class="fa-solid fa-chart-pie me-2 text-accent"></i>Laporan & Monitoring</h6>
    <div class="row g-3 mb-4">
        <div class="col-4">
            <a href="<?= BASE_APP ?>/history" class="menu-card">
                <div class="menu-icon-wrapper icon-blue">
                    <i class="fa-solid fa-clock-rotate-left"></i>
                </div>
                <span class="menu-title">Riwayat<br>Aktivitas</span>
            </a>
        </div>
        <div class="col-4">
            <a href="<?= BASE_APP ?>/daftar-prospek" class="menu-card">
                <div class="menu-icon-wrapper icon-green">
                    <i class="fa-solid fa-file-lines"></i>
                </div>
                <span class="menu-title">Laporan<br>Prospek</span>
            </a>
        </div>
        <div class="col-4">
            <a href="<?= BASE_APP ?>/mapping" class="menu-card">
                <div class="menu-icon-wrapper icon-orange">
                    <i class="fa-solid fa-ranking-star"></i>
                </div>
                <span class="menu-title">Performa<br>AO</span>
            </a>
        </div>
    </div>
    <?php endif; ?>

</div>
