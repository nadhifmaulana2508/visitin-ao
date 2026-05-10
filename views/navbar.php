<nav class="bottom-nav">
    <a href="<?= BASE_APP ?>/home" class="nav-item <?= ($current_page == 'home') ? 'active' : '' ?>">
        <i class="fa-solid fa-house nav-icon"></i>
        <span>Home</span>
    </a>
    <a href="<?= BASE_APP ?>/mapping" class="nav-item <?= ($current_page == 'mapping') ? 'active' : '' ?>">
        <i class="fa-solid fa-users nav-icon"></i>
        <span>Mapping</span>
    </a>
    <a href="<?= BASE_APP ?>/history" class="nav-item <?= ($current_page == 'history') ? 'active' : '' ?>">
        <i class="fa-solid fa-clock-rotate-left nav-icon"></i>
        <span>History</span>
    </a>
    <a href="<?= BASE_APP ?>/profile" class="nav-item <?= ($current_page == 'profile') ? 'active' : '' ?>">
        <i class="fa-solid fa-user nav-icon"></i>
        <span>Profile</span>
    </a>
</nav>