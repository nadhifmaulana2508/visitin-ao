<?php
// Set default title kalau variabel $page_title belum didefinisikan di halaman
$title = isset($page_title) ? $page_title : 'Visitin AO';

// Ambil data user dari session untuk navbar & role check
$user_role = $_SESSION['user_data']['role'] ?? 'staff';
$user_name = $_SESSION['user_data']['full_name'] ?? 'User';
$user_permissions = $_SESSION['user_data']['permissions'] ?? [];
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta name="theme-color" content="#0A1931">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <title><?= $title; ?></title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/js/all.min.js" crossorigin="anonymous" referrerpolicy="no-referrer"></script>

    <style>
        /* =========================================
           TEMA GLOBAL - RESPONSIVE
        ========================================= */
        :root {
            --color-primary: #0A1931; 
            --color-secondary: #150E56; 
            --color-accent: #FF7B54; 
            --color-bg: #F4F7F6;
            --color-success: #388E3C;
            --color-warning: #F57C00;
            --color-danger: #D32F2F;
            --nav-height: 70px;
            --safe-area-bottom: env(safe-area-inset-bottom, 0px);
        }

        * {
            -webkit-tap-highlight-color: transparent;
        }

        body {
            background-color: var(--color-bg);
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            margin: 0;
            padding: 0;
            overflow-x: hidden;
        }
        
        /* =========================================
           MOBILE WRAPPER - RESPONSIVE
        ========================================= */
        .mobile-wrapper {
            max-width: 600px;
            margin: 0 auto;
            background-color: var(--color-bg);
            min-height: 100vh;
            min-height: 100dvh;
            position: relative;
            padding-bottom: calc(var(--nav-height) + var(--safe-area-bottom) + 10px);
        }

        /* Desktop: tambah shadow untuk efek card */
        @media (min-width: 601px) {
            .mobile-wrapper {
                box-shadow: 0 0 30px rgba(0,0,0,0.08);
            }
        }

        /* =========================================
           BOTTOM NAV - RESPONSIVE
        ========================================= */
        .bottom-nav {
            position: fixed;
            bottom: 0;
            left: 50%;
            transform: translateX(-50%);
            max-width: 600px;
            width: 100%;
            background: #ffffff;
            display: flex;
            justify-content: space-around;
            align-items: center;
            padding: 10px 5px calc(10px + var(--safe-area-bottom)) 5px;
            box-shadow: 0 -4px 20px rgba(0,0,0,0.06);
            z-index: 1000;
            border-top-left-radius: 20px;
            border-top-right-radius: 20px;
            height: var(--nav-height);
        }

        .nav-item {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            text-decoration: none;
            color: #A0AEC0;
            flex: 1;
            transition: all 0.3s ease;
            padding: 5px 0;
            position: relative;
        }

        .nav-item svg {
            font-size: 1.2rem;
            margin-bottom: 4px;
            transition: all 0.3s ease;
        }

        .nav-item span {
            font-size: 0.65rem;
            font-weight: 500;
            white-space: nowrap;
        }

        /* Active State */
        .nav-item.active {
            color: var(--color-primary);
        }

        .nav-item.active svg {
            transform: translateY(-2px);
        }

        .nav-item.active span {
            font-weight: 700;
        }

        /* Nav badge untuk notifikasi */
        .nav-badge {
            position: absolute;
            top: 2px;
            right: calc(50% - 18px);
            background: var(--color-accent);
            color: white;
            font-size: 0.5rem;
            font-weight: 700;
            width: 16px;
            height: 16px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        /* =========================================
           UTILITY CLASSES - RESPONSIVE
        ========================================= */
        .text-accent { color: var(--color-accent) !important; }
        .bg-accent { background-color: var(--color-accent) !important; }
        
        /* Smooth scrolling */
        html { scroll-behavior: smooth; }

        /* Better touch targets */
        button, .btn, a.btn, select, input[type="submit"] {
            min-height: 44px;
        }

        /* Fix iOS input zoom */
        input, select, textarea {
            font-size: 16px !important;
        }

        @media (min-width: 400px) {
            input, select, textarea {
                font-size: inherit !important;
            }
        }

        /* Toast notification */
        .toast-notification {
            position: fixed;
            top: 20px;
            left: 50%;
            transform: translateX(-50%);
            max-width: 90%;
            background: var(--color-primary);
            color: white;
            padding: 12px 20px;
            border-radius: 12px;
            font-size: 0.85rem;
            font-weight: 600;
            z-index: 9999;
            box-shadow: 0 8px 20px rgba(0,0,0,0.2);
            animation: slideDown 0.3s ease;
        }

        @keyframes slideDown {
            from { transform: translateX(-50%) translateY(-20px); opacity: 0; }
            to { transform: translateX(-50%) translateY(0); opacity: 1; }
        }

        /* Loading spinner */
        .spinner-overlay {
            position: fixed;
            top: 0; left: 0; right: 0; bottom: 0;
            background: rgba(255,255,255,0.8);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 9999;
        }

        /* Responsive font sizing */
        @media (max-width: 360px) {
            .mobile-wrapper { font-size: 14px; }
        }
    </style>
</head>
<body>
    <div class="mobile-wrapper">
