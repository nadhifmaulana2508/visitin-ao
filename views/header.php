<?php
// Set default title kalau variabel $page_title belum didefinisikan di halaman
$title = isset($page_title) ? $page_title : 'visited-ao';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title; ?></title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/js/all.min.js" crossorigin="anonymous" referrerpolicy="no-referrer"></script>

    <style>
        /* TEMA GLOBAL ELEGAN */
        :root {
            --color-primary: #0A1931; 
            --color-secondary: #150E56; 
            --color-accent: #FF7B54; 
            --color-bg: #F4F7F6; 
        }

        * {
            box-sizing: border-box;
        }

        html {
            min-width: 320px;
        }

        body {
            background-color: var(--color-bg);
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            margin: 0;
        }
        
        .mobile-wrapper {
            width: 100%;
            margin: 0 auto;
            background-color: var(--color-bg);
            min-height: 100vh;
            position: relative;
            padding-bottom: 96px; /* Space buat navbar mobile */
            overflow-x: hidden;
        }
        
/* =========================================
   BOTTOM NAV STYLING (PREMIUM NATIVE FEEL)
========================================= */
.bottom-nav {
    position: fixed;
    bottom: 0;
    max-width: 480px;
    width: 100%;
    background: #ffffff;
    display: flex;
    justify-content: space-around; /* Membagi jarak sama rata */
    align-items: center;
    /* Tambahan padding bawah 20px (Safe Area) biar gak nabrak garis iPhone */
    padding: 12px 10px 25px 10px; 
    box-shadow: 0 -4px 20px rgba(0,0,0,0.06);
    z-index: 1000;
    left: 50%;
    transform: translateX(-50%);
    border-top-left-radius: 20px;
    border-top-right-radius: 20px;
}

.nav-item {
    display: flex;
    flex-direction: column; /* Bikin icon di atas, teks di bawah */
    align-items: center;
    justify-content: center;
    text-decoration: none;
    color: #A0AEC0; /* Warna abu-abu elegan untuk yang tidak aktif */
    flex: 1;
    transition: all 0.3s ease;
}

/* Target langsung ke SVG karena FontAwesome sekarang pakai SVG */
.nav-item svg {
    font-size: 1.35rem;
    margin-bottom: 5px;
    transition: all 0.3s ease;
}

.nav-item span {
    font-size: 0.7rem;
    font-weight: 500;
}

/* =========================================
   ACTIVE STATE (MENU SAAT DIKLIK)
========================================= */
.nav-item.active {
    color: var(--color-primary); /* Berubah jadi biru tua/sesuai tema */
}

/* Efek icon sedikit melompat ke atas saat aktif */
.nav-item.active svg {
    transform: translateY(-3px); 
}

.nav-item.active span {
    font-weight: 700;
}

/* =========================================
   RESPONSIVE APP SHELL
========================================= */
@media (max-width: 767.98px) {
    .mobile-wrapper {
        max-width: 480px;
        box-shadow: 0 0 20px rgba(0,0,0,0.1);
    }
}

@media (min-width: 768px) {
    body {
        background: #E9EEF3;
    }

    .mobile-wrapper {
        max-width: none;
        padding-left: 92px;
        padding-bottom: 0;
    }

    .bottom-nav {
        top: 0;
        bottom: 0;
        left: 0;
        width: 92px;
        max-width: none;
        height: 100vh;
        transform: none;
        flex-direction: column;
        justify-content: center;
        gap: 8px;
        padding: 20px 10px;
        border-radius: 0;
        box-shadow: 4px 0 20px rgba(10,25,49,0.08);
    }

    .nav-item {
        flex: 0 0 auto;
        width: 100%;
        min-height: 72px;
        border-radius: 14px;
    }

    .nav-item.active {
        background-color: #E6EDF5;
    }

    .nav-item svg {
        margin-bottom: 6px;
    }

    .nav-item.active svg {
        transform: none;
    }

    .container,
    .form-container,
    .detail-container {
        max-width: 1120px;
    }

    .filter-wrapper,
    .overlap-card,
    .icon-tabs,
    .filter-tabs,
    .profile-card,
    .section-card,
    .form-container,
    .detail-container {
        max-width: 1120px;
        margin-left: auto !important;
        margin-right: auto !important;
    }

    .header-premium,
    .header-compact,
    .header-profile,
    .debitur-header {
        border-bottom-left-radius: 0 !important;
        border-bottom-right-radius: 0 !important;
        padding-left: clamp(28px, 4vw, 56px) !important;
        padding-right: clamp(28px, 4vw, 56px) !important;
    }

    .login-wrapper {
        max-width: 520px;
        margin: 0 auto;
        justify-content: center;
    }

    .mapping-card,
    .history-card,
    .task-card,
    .summary-card,
    .menu-card {
        border-radius: 12px !important;
    }
}

@media (min-width: 992px) {
    .mobile-wrapper {
        padding-left: 108px;
    }

    .bottom-nav {
        width: 108px;
    }

    .container,
    .form-container,
    .detail-container,
    .filter-wrapper,
    .overlap-card,
    .icon-tabs,
    .filter-tabs,
    .profile-card,
    .section-card {
        max-width: 1180px;
    }
}
        
        
        
        
    </style>
</head>
<body>
    <div class="mobile-wrapper">
