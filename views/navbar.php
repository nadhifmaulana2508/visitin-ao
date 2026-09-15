<?php
// Navigasi fase awal: Home, Create Prospek, Profile, dan Logout.
$role = $user_role ?? 'staff';
$perms = $user_permissions ?? [];
$menu = $menu_access ?? [];

$showInputProspek = (bool)($menu['can_input_prospek'] ?? false);
$showProfile = (bool)($menu['can_access_profile'] ?? true);
?>

<nav class="app-nav" aria-label="Navigasi utama">
    <button type="button" class="nav-item nav-toggle" id="nav-toggle" aria-label="Buka navigasi">
        <i class="fa-solid fa-bars"></i>
    </button>

    <a href="<?= BASE_APP ?>/home" class="nav-item <?= ($current_page == 'home') ? 'active' : '' ?>" aria-label="Home">
        <i class="fa-solid fa-house"></i>
        <span>Home</span>
    </a>

    <?php if ($showInputProspek): ?>
    <a href="<?= BASE_APP ?>/input-prospek" class="nav-item <?= in_array($current_page, ['input-prospek', 'prospek-detail', 'detail-pipeline', 'detail-prospek']) ? 'active' : '' ?>" aria-label="Create Prospek">
        <i class="fa-solid fa-file-circle-plus"></i>
        <span>Create Prospek</span>
    </a>
    <?php endif; ?>

    <?php if ($showProfile): ?>
    <a href="<?= BASE_APP ?>/profile" class="nav-item <?= ($current_page == 'profile') ? 'active' : '' ?>" aria-label="Profile">
        <i class="fa-solid fa-user"></i>
        <span>Profile</span>
    </a>
    <?php endif; ?>

    <button type="button" class="nav-item nav-logout" id="nav-logout" aria-label="Logout">
        <i class="fa-solid fa-arrow-right-from-bracket"></i>
        <span>Logout</span>
    </button>
</nav>

<script>
(function () {
    const nav = document.querySelector('.app-nav');
    const button = document.getElementById('nav-logout');
    const toggle = document.getElementById('nav-toggle');
    if (!button || !nav || !toggle) return;

    const desktopQuery = window.matchMedia('(min-width: 768px)');
    let collapseTimer = null;

    function isDesktop() {
        return desktopQuery.matches;
    }

    function clearCollapseTimer() {
        if (collapseTimer) window.clearTimeout(collapseTimer);
        collapseTimer = null;
    }

    function setCollapsed(collapsed) {
        if (!isDesktop()) {
            nav.classList.remove('is-collapsed');
            return;
        }
        nav.classList.toggle('is-collapsed', collapsed);
        toggle.setAttribute('aria-label', collapsed ? 'Buka navigasi' : 'Tutup navigasi');
        toggle.setAttribute('aria-expanded', collapsed ? 'false' : 'true');
    }

    function scheduleCollapse() {
        clearCollapseTimer();
        if (!isDesktop()) return;
        collapseTimer = window.setTimeout(function () {
            if (!nav.matches(':hover') && !nav.contains(document.activeElement)) setCollapsed(true);
        }, 5000);
    }

    function openNav() {
        if (!isDesktop()) return;
        clearCollapseTimer();
        setCollapsed(false);
        scheduleCollapse();
    }

    toggle.addEventListener('click', function (event) {
        event.stopPropagation();
        if (nav.classList.contains('is-collapsed')) openNav();
        else scheduleCollapse();
    });

    nav.addEventListener('pointerenter', openNav);
    nav.addEventListener('pointerleave', scheduleCollapse);
    nav.addEventListener('focusin', openNav);
    nav.addEventListener('focusout', scheduleCollapse);
    desktopQuery.addEventListener?.('change', function () {
        clearCollapseTimer();
        setCollapsed(false);
        scheduleCollapse();
    });
    openNav();

    button.addEventListener('click', async function () {
        button.disabled = true;
        const confirmed = window.VisitinUI && typeof window.VisitinUI.confirm === 'function'
            ? await window.VisitinUI.confirm({
                title: 'Logout dari aplikasi?',
                message: 'Session kamu akan diakhiri pada perangkat ini.',
                confirmLabel: 'Ya, logout',
                cancelLabel: 'Batal'
            })
            : window.confirm('Logout dari aplikasi?');

        if (!confirmed) {
            button.disabled = false;
            return;
        }

        try {
            await fetch('<?= BASE_APP ?>/api/?action=logout', {
                method: 'POST',
                credentials: 'include',
                headers: { Accept: 'application/json' }
            });
        } finally {
            localStorage.removeItem('visitin_token');
            localStorage.removeItem('visitin_user');
            window.location.href = '<?= BASE_APP ?>/login';
        }
    });
})();
</script>
