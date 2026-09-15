<?php
$title = isset($page_title) ? $page_title : 'Visitin AO';

// Ambil data user dari session untuk navbar & role check
$user_role = $_SESSION['user_data']['role'] ?? 'staff';
$user_name = $_SESSION['user_data']['full_name'] ?? 'User';
$user_permissions = $_SESSION['user_data']['permissions'] ?? [];
$user_kode_kantor = $_SESSION['user_data']['kode_kantor'] ?? ($_SESSION['user_data']['kode'] ?? '000');
$user_access_korwil = $_SESSION['user_data']['access_korwil'] ?? null;
$user_job_position = $_SESSION['user_data']['job_position'] ?? '';
$user_group_jabatan = $_SESSION['user_data']['group_jabatan'] ?? '';

$is_developer_menu = ($user_role === 'developer') || in_array('DEV', $user_permissions, true);
$is_superuser_menu = ($user_role === 'superuser') || in_array('SUPERUSER_PROSPEK', $user_permissions, true) || $is_developer_menu;
$is_ao_kredit_menu = in_array('AO_KREDIT', $user_permissions, true) || $is_developer_menu;
$is_ao_dana_menu = in_array('AO_DANA', $user_permissions, true) || $is_developer_menu;
$is_ao_remedial_menu = in_array('AO_REMEDIAL_FE', $user_permissions, true) || in_array('AO_REMEDIAL_BE', $user_permissions, true) || $is_developer_menu;
$is_branch_delegator = preg_match('/^(00[1-9]|0[1-2][0-9]|028)$/', (string)$user_kode_kantor) === 1;
$is_known_prospek_job = in_array($user_job_position, [
    'Staf Sistem dan Jaringan TI',
    'Account Officer Kredit',
    'Account Officer Dana',
    'Account Officer Remedial',
    'Kepala Bidang Pemasaran',
    'Teller',
    'Customer Service',
], true);

$menu_access = [
    'can_access_prospek' => true,
    'can_input_prospek' => true,
    'can_delegate_prospek' => $is_developer_menu || ($user_role === 'superuser' && $is_branch_delegator),
    'can_view_report_prospek' => true,
    'can_access_mapping' => $is_superuser_menu || $is_ao_kredit_menu || $is_ao_remedial_menu,
    'can_access_nominatif' => $is_superuser_menu || $is_ao_kredit_menu || $is_ao_remedial_menu,
    'can_access_history' => $user_role !== 'staff',
    'can_access_profile' => true,
];
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#0A1931">
    <!-- # COMPONENT: Meta ini menjadi sumber BASE_APP untuk API client reusable. -->
    <meta name="app-base" content="<?= htmlspecialchars(BASE_APP, ENT_QUOTES, 'UTF-8') ?>">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <title><?= $title; ?></title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=Nunito+Sans:wght@400;600;700;800&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Roboto:wght@400;500;700;900&display=swap" rel="stylesheet">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/js/all.min.js" crossorigin="anonymous"></script>
    <link href="<?= BASE_APP ?>/assets/css/components.css" rel="stylesheet">
    <script src="<?= BASE_APP ?>/assets/js/components.js" defer></script>


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
            --app-font-scale: 1;
            --app-font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
        }

        * { -webkit-tap-highlight-color: transparent; box-sizing: border-box; }

        html {
            scrollbar-width: thin;
            scrollbar-color: #94A3B8 transparent;
        }

        *::-webkit-scrollbar {
            width: 6px;
            height: 6px;
        }

        *::-webkit-scrollbar-track {
            background: transparent;
        }

        *::-webkit-scrollbar-thumb {
            border: 1px solid transparent;
            border-radius: 999px;
            background: #94A3B8;
            background-clip: padding-box;
        }

        *::-webkit-scrollbar-thumb:hover {
            background: #64748B;
            background-clip: padding-box;
        }

        body {
            background-color: var(--color-bg);
            font-family: var(--app-font-family);
            font-size: calc(1rem * var(--app-font-scale));
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
            padding: 0;
        }

        /* =========================================
           APP NAV - MOBILE BOTTOM / DESKTOP RIGHT FLOATING RAIL
        ========================================= */
        .app-nav {
            position: fixed;
            right: 0;
            bottom: 0;
            left: 0;
            display: flex;
            justify-content: space-evenly;
            align-items: center;
            gap: 0;
            min-height: 58px;
            width: 100%;
            margin: 0;
            padding: 5px 18px calc(5px + var(--safe-bottom));
            border-top: 1px solid rgba(226, 232, 240, 0.95);
            border-radius: 16px 16px 0 0;
            background: rgba(255, 255, 255, 0.98);
            box-shadow: 0 -8px 24px rgba(15, 23, 42, 0.11);
            backdrop-filter: blur(14px);
            z-index: 1000;
        }

        .nav-item {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            flex: 0 0 46px;
            min-width: 0;
            min-height: 44px;
            padding: 6px;
            border: 0;
            border-radius: 13px;
            background: transparent;
            color: #94A3B8;
            text-decoration: none;
            transition: background-color 0.2s, color 0.2s, transform 0.2s;
        }
        .nav-item i { font-size: 1.08rem; }
        .nav-item span { display: none; }
        .nav-toggle { display: none; }

        .nav-item:hover,
        .nav-item.active {
            background: #EEF2F7;
            color: var(--color-primary);
        }

        .nav-item.active {
            background: var(--color-primary);
            color: #FFFFFF;
            font-weight: 700;
            box-shadow: 0 6px 14px rgba(10, 25, 49, 0.22);
            transform: translateY(-2px);
        }

        @media (max-width: 420px) {
            .app-nav {
                gap: 12px;
                padding-right: 10px;
                padding-left: 10px;
            }

            .nav-item {
                flex-basis: 42px;
            }
        }

        @media (min-width: 768px) {
            .mobile-wrapper {
                padding: 0;
            }

            .app-nav {
                top: auto;
                right: 8px;
                bottom: 10px;
                left: auto;
                width: 48px;
                min-height: auto;
                transform: none;
                flex-direction: column;
                justify-content: flex-start;
                align-items: stretch;
                gap: 3px;
                margin: 0;
                padding: 5px 4px;
                border: 1px solid rgba(255, 255, 255, 0.82);
                border-radius: 16px;
                background: rgba(255, 255, 255, 0.9);
                box-shadow: 0 12px 30px rgba(15, 23, 42, 0.15);
                backdrop-filter: blur(14px);
            }

            .nav-item {
                flex: 0 0 auto;
                flex-direction: row;
                justify-content: flex-start;
                gap: 5px;
                min-height: 36px;
                padding: 5px 5px;
            }

            .nav-item i {
                flex: 0 0 16px;
                font-size: 0.82rem;
                text-align: center;
            }

            .nav-item span {
                display: none;
            }

            .nav-item.active {
                box-shadow: 0 4px 10px rgba(10, 25, 49, 0.2);
            }

            .app-nav.is-collapsed {
                width: 42px;
                min-height: 42px;
                padding: 4px;
                border-radius: 14px;
            }

            .app-nav.is-collapsed .nav-item:not(.nav-toggle) {
                display: none;
            }

            .app-nav.is-collapsed .nav-toggle {
                display: flex;
                min-height: 32px;
                padding: 4px;
                border-radius: 10px;
                background: var(--color-primary);
                color: #FFFFFF;
            }

        }

        .mobile-wrapper.page-login {
            padding-right: 0;
            padding-left: 0;
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

        .modal-dialog.modal-dialog-centered {
            margin-left: auto !important;
            margin-right: auto !important;
            width: calc(100% - 30px);
        }
        .modal-dialog.modal-dialog-centered.modal-lg {
            max-width: min(900px, calc(100% - 30px));
        }
        .modal-dialog {
            max-height: calc(100dvh - 32px);
        }
        .modal-content {
            max-height: calc(100dvh - 32px);
            overflow: hidden;
            display: flex;
            flex-direction: column;
        }
        .modal-header,
        .modal-footer {
            flex: 0 0 auto;
        }
        .modal-body {
            overflow-y: auto;
            -webkit-overflow-scrolling: touch;
        }
        .modal-body::-webkit-scrollbar {
            width: 6px;
        }
        .modal-body::-webkit-scrollbar-thumb {
            background: #CBD5E1;
            border-radius: 999px;
        }
        @media (max-width: 767px) {
            .modal-dialog {
                margin-top: 12px;
                margin-bottom: calc(var(--nav-height) + var(--safe-bottom) + 12px);
                max-height: calc(100dvh - var(--nav-height) - var(--safe-bottom) - 24px);
            }
            .modal-content {
                max-height: calc(100dvh - var(--nav-height) - var(--safe-bottom) - 24px);
            }
        }

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
    <div class="mobile-wrapper page-<?= htmlspecialchars($current_page ?? 'unknown', ENT_QUOTES, 'UTF-8') ?>" data-preference-user="<?= htmlspecialchars($_SESSION['user_data']['employee_id'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
