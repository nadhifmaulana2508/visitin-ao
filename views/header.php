<?php
$title = isset($page_title) ? $page_title : 'Visitin AO';

// Ambil data user dari session untuk navbar & role check
$user_role = $_SESSION['user_data']['role'] ?? 'staff';
$user_name = $_SESSION['user_data']['full_name'] ?? 'User';
$user_permissions = $_SESSION['user_data']['permissions'] ?? [];
$user_kode_kantor = $_SESSION['user_data']['kode_kantor'] ?? ($_SESSION['user_data']['kode'] ?? '000');
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#0A1931">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <title><?= $title; ?></title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/js/all.min.js" crossorigin="anonymous"></script>


    <style>
        /* =========================================
           CSS VARIABLES
        ========================================= */
        :root {
            --color-primary: #0A1931;
            --color-secondary: #150E56;
            --color-accent: #FF7B54;
            --color-bg: #F4F7F6;
            --color-success: #388E3C;
            --color-warning: #F57C00;
            --color-danger: #D32F2F;
            --nav-height: 68px;
            --safe-bottom: env(safe-area-inset-bottom, 0px);
            --content-max: 1200px;
            --sidebar-width: 240px;
        }

        * { -webkit-tap-highlight-color: transparent; box-sizing: border-box; }

        body {
            background-color: var(--color-bg);
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            margin: 0; padding: 0; overflow-x: hidden;
        }

        /* =========================================
           LAYOUT WRAPPER - FULL WIDTH (no side spaces)
           Semua device: full-width, no max-width constraint
        ========================================= */
        .mobile-wrapper {
            width: 100%;
            max-width: 100%;
            margin: 0;
            background-color: var(--color-bg);
            min-height: 100vh;
            min-height: 100dvh;
            position: relative;
            padding-bottom: calc(var(--nav-height) + var(--safe-bottom) + 15px);
        }


        /* =========================================
           BOTTOM NAV - RESPONSIVE
        ========================================= */
        .bottom-nav {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            width: 100%;
            background: #ffffff;
            display: flex;
            justify-content: space-around;
            align-items: center;
            padding: 8px 5px calc(8px + var(--safe-bottom)) 5px;
            box-shadow: 0 -4px 20px rgba(0,0,0,0.06);
            z-index: 1000;
            height: var(--nav-height);
        }

        .nav-item {
            display: flex; flex-direction: column; align-items: center;
            justify-content: center; text-decoration: none;
            color: #A0AEC0; flex: 1; transition: all 0.25s ease;
            padding: 4px 0; position: relative; gap: 3px;
        }
        .nav-item svg { font-size: 1.15rem; transition: all 0.25s ease; }
        .nav-item span { font-size: 0.6rem; font-weight: 500; white-space: nowrap; }

        .nav-item.active { color: var(--color-primary); }
        .nav-item.active svg { transform: translateY(-2px); }
        .nav-item.active span { font-weight: 700; }

        /* Desktop: nav items wider */
        @media (min-width: 768px) {
            .nav-item span { font-size: 0.7rem; }
            .nav-item svg { font-size: 1.25rem; }
            .bottom-nav { padding: 10px 20px calc(10px + var(--safe-bottom)) 20px; }
        }

        /* =========================================
           RESPONSIVE GRID HELPERS
        ========================================= */
        .grid-responsive {
            display: grid;
            gap: 12px;
            grid-template-columns: repeat(2, 1fr);
        }
        @media (min-width: 576px) { .grid-responsive { grid-template-columns: repeat(3, 1fr); } }
        @media (min-width: 768px) { .grid-responsive { grid-template-columns: repeat(4, 1fr); gap: 16px; } }
        @media (min-width: 1024px) { .grid-responsive { grid-template-columns: repeat(5, 1fr); gap: 18px; } }

        /* Card grid for lists */
        .grid-cards {
            display: grid;
            gap: 12px;
            grid-template-columns: 1fr;
        }
        @media (min-width: 768px) { .grid-cards { grid-template-columns: repeat(2, 1fr); gap: 16px; } }
        @media (min-width: 1024px) { .grid-cards { grid-template-columns: repeat(3, 1fr); gap: 18px; } }

        /* =========================================
           RESPONSIVE CONTENT PADDING
        ========================================= */
        .content-padding {
            padding-left: 16px;
            padding-right: 16px;
        }
        @media (min-width: 768px) {
            .content-padding { padding-left: 24px; padding-right: 24px; }
        }
        @media (min-width: 1024px) {
            .content-padding { padding-left: 32px; padding-right: 32px; }
        }


        /* =========================================
           RESPONSIVE HEADER (gradient headers)
        ========================================= */
        .header-compact {
            background: linear-gradient(135deg, var(--color-primary), var(--color-secondary));
            color: white; padding: 25px 20px 45px 20px;
            border-bottom-left-radius: 25px; border-bottom-right-radius: 25px;
        }
        @media (min-width: 768px) {
            .header-compact { padding: 30px 32px 50px 32px; border-bottom-left-radius: 30px; border-bottom-right-radius: 30px; }
        }
        @media (min-width: 1024px) {
            .header-compact { padding: 35px 40px 55px 40px; }
        }

        /* =========================================
           RESPONSIVE CARDS
        ========================================= */
        .card-responsive {
            background: #ffffff; border-radius: 16px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.03);
            border: 1px solid #F1F5F9; padding: 16px;
            transition: transform 0.15s, box-shadow 0.15s;
        }
        .card-responsive:hover {
            box-shadow: 0 6px 20px rgba(0,0,0,0.06);
        }
        @media (min-width: 768px) {
            .card-responsive { padding: 20px; border-radius: 18px; }
        }

        /* =========================================
           FORM ELEMENTS - RESPONSIVE
        ========================================= */
        .input-custom {
            background-color: #ffffff; border: 1px solid #CBD5E1; border-radius: 10px;
            padding: 11px 14px; font-size: 0.85rem; font-weight: 600;
            color: #1E293B; width: 100%; transition: border-color 0.2s, box-shadow 0.2s;
            min-height: 44px;
        }
        .input-custom:focus {
            border-color: var(--color-primary); box-shadow: 0 0 0 3px rgba(10,25,49,0.08); outline: none;
        }
        .input-custom:disabled, .input-custom[readonly] {
            background-color: #F8FAFC; color: #64748B; cursor: not-allowed;
        }
        .input-custom::placeholder { color: #94A3B8; font-weight: 400; }

        .form-label-custom {
            font-size: 0.7rem; font-weight: 700; color: #64748B;
            margin-bottom: 5px; display: block; text-transform: uppercase;
        }

        @media (min-width: 768px) {
            .input-custom { font-size: 0.9rem; padding: 12px 16px; }
            .form-label-custom { font-size: 0.75rem; }
        }

        /* =========================================
           UTILITY CLASSES
        ========================================= */
        .text-accent { color: var(--color-accent) !important; }
        .bg-accent { background-color: var(--color-accent) !important; }
        html { scroll-behavior: smooth; }

        /* Toast notification */
        .toast-notification {
            position: fixed; top: 20px; left: 50%; transform: translateX(-50%);
            max-width: 90%; background: var(--color-primary); color: white;
            padding: 12px 20px; border-radius: 12px; font-size: 0.85rem;
            font-weight: 600; z-index: 9999;
            box-shadow: 0 8px 20px rgba(0,0,0,0.2); animation: slideDown 0.3s ease;
        }
        @media (min-width: 768px) { .toast-notification { max-width: 400px; } }
        @keyframes slideDown {
            from { transform: translateX(-50%) translateY(-20px); opacity: 0; }
            to { transform: translateX(-50%) translateY(0); opacity: 1; }
        }

        /* Responsive text */
        .text-truncate-2 {
            display: -webkit-box; -webkit-line-clamp: 2;
            -webkit-box-orient: vertical; overflow: hidden;
        }

        /* Stats row - responsive */
        .stats-row {
            display: flex; gap: 10px; overflow-x: auto;
            -webkit-overflow-scrolling: touch; scroll-snap-type: x mandatory;
            padding-bottom: 5px;
        }
        .stats-row::-webkit-scrollbar { display: none; }
        .stat-card {
            flex: 1; min-width: 80px; background: #ffffff; border-radius: 14px;
            padding: 14px 10px; box-shadow: 0 4px 15px rgba(0,0,0,0.04);
            text-align: center; scroll-snap-align: start;
        }
        @media (min-width: 768px) {
            .stats-row { overflow: visible; }
            .stat-card { min-width: unset; padding: 18px 14px; }
        }

        /* FAB button */
        .btn-fab {
            position: fixed;
            bottom: calc(var(--nav-height) + var(--safe-bottom) + 20px);
            right: 24px; width: 56px; height: 56px; border-radius: 50%;
            background: var(--color-accent); color: white; border: none;
            box-shadow: 0 6px 20px rgba(255,123,84,0.4);
            display: flex; align-items: center; justify-content: center;
            font-size: 1.3rem; z-index: 999; transition: 0.2s;
            text-decoration: none;
        }
        .btn-fab:active { transform: scale(0.9); }

        /* Section title */
        .section-title {
            font-size: 0.8rem; font-weight: 800; color: var(--color-primary);
            text-transform: uppercase; margin-bottom: 15px;
            border-bottom: 2px solid #F4F7F6; padding-bottom: 10px;
        }
        @media (min-width: 768px) { .section-title { font-size: 0.85rem; } }

        /* Badge system */
        .badge-type { font-size: 0.6rem; padding: 4px 8px; border-radius: 6px; font-weight: 700; }
        .badge-kredit { background: #E3F2FD; color: #1565C0; }
        .badge-tabungan { background: #E8F5E9; color: #2E7D32; }
        .badge-deposito { background: #F3E5F5; color: #6A1B9A; }
        .badge-aset { background: #FFF3E0; color: #E65100; }
        .badge-existing { background: #E0F7FA; color: #006064; }

        .badge-status { font-size: 0.6rem; padding: 4px 8px; border-radius: 6px; font-weight: 700; }
        .status-open { background: #FFF9C4; color: #F57F17; }
        .status-follow_up { background: #E3F2FD; color: #1565C0; }
        .status-sla { background: #E8F5E9; color: #2E7D32; }
        .status-closing { background: #C8E6C9; color: #1B5E20; }
        .status-reject { background: #FFEBEE; color: #C62828; }
    </style>
</head>
<body>
    <div class="mobile-wrapper">
