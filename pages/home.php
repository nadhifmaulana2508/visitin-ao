<?php
$role = $user_role ?? 'staff';
$nama_user = $user_name ?? 'User';
$branch = $_SESSION['user_data']['branch'] ?? 'Cabang';
$perms = $user_permissions ?? [];
$kodeKantor = $user_kode_kantor ?? '000';
$menu = $menu_access ?? [];

$is_developer = ($role === 'developer');
$is_superuser = in_array($role, ['superuser', 'developer']);
$is_ao_kredit = in_array('AO_KREDIT', $perms) || $is_developer;
$is_ao_dana = in_array('AO_DANA', $perms) || $is_developer;
$is_ao_remedial = (in_array('AO_REMEDIAL_FE', $perms) || in_array('AO_REMEDIAL_BE', $perms)) || $is_developer;
$is_ao = $is_ao_kredit || $is_ao_dana || $is_ao_remedial;
$is_pusat = ($kodeKantor === '000');

$can_access_prospek = (bool)($menu['can_access_prospek'] ?? true);
$can_input_prospek = (bool)($menu['can_input_prospek'] ?? true);
$can_delegate_prospek = (bool)($menu['can_delegate_prospek'] ?? $is_superuser);
$can_view_report_prospek = (bool)($menu['can_view_report_prospek'] ?? ($is_superuser || $is_pusat));
$can_access_mapping = (bool)($menu['can_access_mapping'] ?? ($is_ao || $is_superuser));
$can_access_nominatif = (bool)($menu['can_access_nominatif'] ?? ($is_ao || $is_superuser));
$can_access_history = (bool)($menu['can_access_history'] ?? true);
?>

<style>
    .header-home {
        background: linear-gradient(135deg, var(--color-primary), var(--color-secondary));
        color: white; padding: 28px 20px 55px 20px;
        border-bottom-left-radius: 28px; border-bottom-right-radius: 28px;
    }
    @media (min-width: 768px) { .header-home { padding: 32px 32px 60px 32px; } }
    @media (min-width: 1024px) { .header-home { padding: 36px 40px 65px 40px; border-radius: 0 0 32px 32px; } }

    .overlap-card {
        background: #ffffff; border-radius: 16px;
        margin: -35px 16px 20px 16px; padding: 18px;
        box-shadow: 0 8px 24px rgba(0,0,0,0.06); position: relative; z-index: 10;
    }
    @media (min-width: 768px) { .overlap-card { margin: -40px 24px 24px 24px; padding: 22px; } }
    @media (min-width: 1024px) { .overlap-card { margin: -45px 32px 28px 32px; padding: 24px; } }

    .menu-card {
        background: #ffffff; border-radius: 14px; padding: 16px 10px;
        text-align: center; box-shadow: 0 3px 12px rgba(0,0,0,0.03);
        border: 1px solid #F1F5F9; transition: transform 0.15s, box-shadow 0.15s;
        height: 100%; display: flex; flex-direction: column;
        justify-content: center; align-items: center;
        text-decoration: none; color: inherit; cursor: pointer;
    }
    .menu-card:hover { box-shadow: 0 6px 20px rgba(0,0,0,0.07); transform: translateY(-2px); }
    .menu-card:active { transform: scale(0.96); }

    .menu-icon-wrapper {
        width: 46px; height: 46px; border-radius: 12px;
        display: flex; align-items: center; justify-content: center;
        margin-bottom: 10px; font-size: 1.3rem;
    }
    @media (min-width: 768px) { .menu-icon-wrapper { width: 52px; height: 52px; font-size: 1.4rem; } }

    .icon-orange { background: #FFF0EB; color: var(--color-accent); }
    .icon-blue { background: #E6EDF5; color: var(--color-primary); }
    .icon-red { background: #FFE5E5; color: #D32F2F; }
    .icon-green { background: #E8F5E9; color: #388E3C; }
    .icon-purple { background: #F3E5F5; color: #7B1FA2; }
    .icon-cyan { background: #E0F7FA; color: #00838F; }

    .menu-title { font-size: 0.72rem; font-weight: 700; color: #2B2B2B; line-height: 1.3; }
    @media (min-width: 768px) { .menu-title { font-size: 0.8rem; } }

    .section-heading {
        font-size: 0.75rem; font-weight: 800; color: #475569;
        text-transform: uppercase; letter-spacing: 0.5px;
        margin-bottom: 12px; padding-left: 4px;
    }
    @media (min-width: 768px) { .section-heading { font-size: 0.8rem; margin-bottom: 16px; } }

    .role-badge {
        font-size: 0.6rem; padding: 4px 10px; border-radius: 8px;
        font-weight: 700; text-transform: uppercase; letter-spacing: 0.3px;
    }
    .role-developer { background: #FFF0EB; color: var(--color-accent); }
    .role-ao_kredit { background: #E3F2FD; color: #1565C0; }
    .role-ao_dana { background: #E8F5E9; color: #2E7D32; }
    .role-ao_remedial { background: #FFEBEE; color: #C62828; }
    .role-superuser { background: #F3E5F5; color: #6A1B9A; }
    .role-staff { background: #F1F5F9; color: #475569; }

    .avatar-home {
        width: 44px; height: 44px; border-radius: 50%;
        border: 2px solid rgba(255,255,255,0.6); box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    }
    @media (min-width: 768px) { .avatar-home { width: 50px; height: 50px; } }

</style>


<div class="header-home">
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <p class="mb-0 small text-white-50" style="font-size:0.75rem;">Selamat Datang,</p>
            <h4 class="mb-0 fw-bold" style="font-size:1.15rem;"><?= htmlspecialchars($nama_user) ?></h4>
        </div>
        <a href="<?= BASE_APP ?>/profile">
            <img src="https://ui-avatars.com/api/?name=<?= urlencode($nama_user) ?>&background=FF7B54&color=fff&rounded=true&size=100"
                 alt="Profile" class="avatar-home">
        </a>
    </div>
</div>

<div class="overlap-card d-flex justify-content-between align-items-center">
    <div>
        <p class="mb-1 small fw-bold text-muted" style="font-size:0.68rem;">Posisi / Role</p>
        <h6 class="mb-0 fw-bold text-dark" style="font-size:0.95rem;"><?= ucwords(str_replace('_', ' ', $role)) ?></h6>
        <small class="text-muted" style="font-size:0.7rem;"><i class="fa-solid fa-building-columns me-1"></i><?= htmlspecialchars($branch) ?> (<?= $kodeKantor ?>)</small>
    </div>
    <span class="role-badge role-<?= $role ?>"><?= strtoupper(str_replace('_', ' ', $role)) ?></span>
</div>

<div class="content-padding pb-5 mb-4">

    <?php if ($can_access_prospek): ?>
    <h6 class="section-heading"><i class="fa-solid fa-bullseye me-2 text-accent"></i>Prospek</h6>
    <div class="grid-responsive mb-4">
        <?php if ($can_input_prospek): ?>
        <a href="<?= BASE_APP ?>/input-prospek" class="menu-card">
            <div class="menu-icon-wrapper icon-green"><i class="fa-solid fa-plus"></i></div>
            <span class="menu-title">Input Prospek</span>
        </a>
        <?php endif; ?>
        <a href="<?= BASE_APP ?>/daftar-prospek" class="menu-card">
            <div class="menu-icon-wrapper icon-blue"><i class="fa-solid fa-list-check"></i></div>
            <span class="menu-title">Daftar Prospek</span>
        </a>

        <?php if ($can_delegate_prospek): ?>
        <a href="<?= BASE_APP ?>/daftar-prospek?filter=pending" class="menu-card">
            <div class="menu-icon-wrapper icon-purple"><i class="fa-solid fa-user-gear"></i></div>
            <span class="menu-title">Delegasi Prospek</span>
        </a>
        <?php endif; ?>

        <?php if ($is_ao_kredit): ?>
        <a href="<?= BASE_APP ?>/daftar-prospek?filter=sla" class="menu-card">
            <div class="menu-icon-wrapper icon-orange"><i class="fa-solid fa-chart-line"></i></div>
            <span class="menu-title">Pipeline Kredit</span>
        </a>
        <?php endif; ?>

        <?php if ($can_view_report_prospek && $role !== 'staff'): ?>
        <a href="<?= BASE_APP ?>/daftar-prospek?view=report" class="menu-card">
            <div class="menu-icon-wrapper icon-blue"><i class="fa-solid fa-file-lines"></i></div>
            <span class="menu-title">Laporan Prospek</span>
        </a>
        <?php endif; ?>
    </div>
    <?php endif; ?>


    <!-- ============ MENU KUNJUNGAN & MAPPING (AO + Superuser) ============ -->
    <?php if ($can_access_mapping || $can_access_nominatif): ?>
    <h6 class="section-heading"><i class="fa-solid fa-person-walking-arrow-right me-2 text-accent"></i>Kunjungan & Mapping</h6>
    <div class="grid-responsive mb-4">

        <?php if ($is_ao_remedial && $can_access_mapping): ?>
        <a href="<?= BASE_APP ?>/mapping" class="menu-card">
            <div class="menu-icon-wrapper icon-orange"><i class="fa-solid fa-calendar-check"></i></div>
            <span class="menu-title">Mapping Bulan Ini</span>
        </a>
        <a href="<?= BASE_APP ?>/janji-bayar" class="menu-card">
            <div class="menu-icon-wrapper icon-green"><i class="fa-solid fa-handshake"></i></div>
            <span class="menu-title">Janji Bayar</span>
        </a>
        <a href="<?= BASE_APP ?>/hapus-buku" class="menu-card">
            <div class="menu-icon-wrapper icon-red"><i class="fa-solid fa-file-circle-xmark"></i></div>
            <span class="menu-title">Hapus Buku</span>
        </a>
        <?php endif; ?>

        <?php if ($is_ao_kredit && $can_access_mapping): ?>
        <a href="<?= BASE_APP ?>/mapping" class="menu-card">
            <div class="menu-icon-wrapper icon-orange"><i class="fa-solid fa-users-gear"></i></div>
            <span class="menu-title">Mapping Existing</span>
        </a>
        <?php endif; ?>

        <?php if ($can_access_nominatif): ?>
        <a href="<?= BASE_APP ?>/nominatif" class="menu-card">
            <div class="menu-icon-wrapper icon-blue"><i class="fa-solid fa-file-invoice-dollar"></i></div>
            <span class="menu-title">Nominatif Kredit</span>
        </a>
        <?php endif; ?>

        <?php if ($is_superuser && $can_access_mapping): ?>
        <a href="<?= BASE_APP ?>/mapping" class="menu-card">
            <div class="menu-icon-wrapper icon-purple"><i class="fa-solid fa-chart-column"></i></div>
            <span class="menu-title">Monitoring Mapping</span>
        </a>
        <?php endif; ?>
    </div>
    <?php endif; ?>

    <!-- ============ MENU LAPORAN (Superuser + Developer + Pusat) ============ -->
    <?php if ($can_access_history || $can_view_report_prospek || $can_access_mapping): ?>
    <h6 class="section-heading"><i class="fa-solid fa-chart-pie me-2 text-accent"></i>Laporan & Monitoring</h6>
    <div class="grid-responsive mb-4">
        <?php if ($can_access_history): ?>
        <a href="<?= BASE_APP ?>/history" class="menu-card">
            <div class="menu-icon-wrapper icon-blue"><i class="fa-solid fa-clock-rotate-left"></i></div>
            <span class="menu-title">Riwayat Aktivitas</span>
        </a>
        <?php endif; ?>
        <?php if ($can_view_report_prospek): ?>
        <a href="<?= BASE_APP ?>/daftar-prospek?view=report" class="menu-card">
            <div class="menu-icon-wrapper icon-green"><i class="fa-solid fa-file-lines"></i></div>
            <span class="menu-title">Report Prospek</span>
        </a>
        <?php endif; ?>
        <!-- <a href="<?= BASE_APP ?>/daftar-prospek?source=non_ao" class="menu-card">
            <div class="menu-icon-wrapper icon-orange"><i class="fa-solid fa-user-clock"></i></div>
            <span class="menu-title">Input Non-AO</span>
        </a> -->
        <?php if ($can_access_mapping): ?>
        <a href="<?= BASE_APP ?>/mapping" class="menu-card">
            <div class="menu-icon-wrapper icon-purple"><i class="fa-solid fa-ranking-star"></i></div>
            <span class="menu-title">Performa AO</span>
        </a>
        <?php endif; ?>
    </div>
    <?php endif; ?>

</div>
