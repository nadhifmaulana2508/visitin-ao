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

        body {
            background-color: var(--color-bg);
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        .mobile-wrapper {
            max-width: 480px;
            margin: 0 auto;
            background-color: var(--color-bg);
            min-height: 100vh;
            position: relative;
            box-shadow: 0 0 20px rgba(0,0,0,0.1);
            padding-bottom: 80px; /* Space buat navbar */
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
        
        
        
        
    </style>
</head>
<body>
    <div class="mobile-wrapper">