<?php
// Navbar role-based: tampilkan menu sesuai role user
$role = $user_role ?? 'staff';
$perms = $user_permissions ?? [];

// Helper: cek apakah user punya permission tertentu
function hasPermission($perms, $code) {
    return in_array($code, $perms);
}

// Determine which nav items to show based on role
$showMapping = in_array($role, ['developer', 'ao_remedial', 'superuser']) 
    || hasPermission($perms, 'AO_REMEDIAL_FE') 
    || hasPermission($perms, 'AO_REMEDIAL_BE')
    || hasPermission($perms, 'AO_KREDIT');

$showProspek = true; // Semua bisa akses prospek (input prospek)
?>

<nav class="bottom-nav">
    <a href="<?= BASE_APP ?>/home" class="nav-item <?= ($current_page == 'home') ? 'active' : '' ?>">
        <i class="fa-solid fa-house"></i>
        <span>Home</span>
    </a>

    <?php if ($showProspek): ?>
    <a href="<?= BASE_APP ?>/daftar-prospek" class="nav-item <?= in_array($current_page, ['daftar-prospek', 'input-prospek', 'prospek-detail']) ? 'active' : '' ?>">
        <i class="fa-solid fa-bullseye"></i>
        <span>Prospek</span>
    </a>
    <?php endif; ?>

    <?php if ($showMapping): ?>
    <a href="<?= BASE_APP ?>/mapping" class="nav-item <?= ($current_page == 'mapping') ? 'active' : '' ?>">
        <i class="fa-solid fa-users"></i>
        <span>Mapping</span>
    </a>
    <?php endif; ?>

    <a href="<?= BASE_APP ?>/history" class="nav-item <?= ($current_page == 'history') ? 'active' : '' ?>">
        <i class="fa-solid fa-clock-rotate-left"></i>
        <span>History</span>
    </a>

    <a href="<?= BASE_APP ?>/profile" class="nav-item <?= ($current_page == 'profile') ? 'active' : '' ?>">
        <i class="fa-solid fa-user"></i>
        <span>Profile</span>
    </a>
</nav>
