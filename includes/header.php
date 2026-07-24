<?php
/**
 * CarePulse - Global Header
 * ob_start() prevents "headers already sent" errors from any page
 */
ob_start();

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/functions.php';

require_login();
$user_role    = $_SESSION['role'] ?? 'guest';
$notifications = get_pending_count(get_db_pdo());
$page_title   = $page_title ?? 'CarePulse';
$current_page = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="CarePulse - Modern Hospital Management System">
    <title><?= esc($page_title) ?> | CarePulse</title>
    <link rel="icon" type="image/png" href="favicon.png">

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    <!-- Bootstrap 5 + FA -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.all.min.js"></script>

    <style>
        /* ==================== DESIGN TOKENS ==================== */
        :root {
            --bg:          #f0f2f8;
            --sidebar-bg:  #0f172a;
            --sidebar-w:   250px;
            --accent:      #4f46e5;
            --accent-light:#6366f1;
            --accent-glow: rgba(79,70,229,.18);
            --success:     #10b981;
            --warning:     #f59e0b;
            --danger:      #ef4444;
            --info:        #06b6d4;
            --text:        #1e293b;
            --muted:       #64748b;
            --card-bg:     #ffffff;
            --card-radius: 16px;
            --card-shadow: 0 1px 3px rgba(0,0,0,.06), 0 4px 20px rgba(0,0,0,.06);
            --transition:  all .22s cubic-bezier(.4,0,.2,1);
        }

        /* ==================== BASE ==================== */
        *, *::before, *::after { box-sizing: border-box; }

        body {
            font-family: 'Inter', system-ui, sans-serif;
            background: var(--bg);
            color: var(--text);
            margin: 0;
            min-height: 100vh;
            overflow-x: hidden;
        }

        /* ==================== SIDEBAR ==================== */
        #sidebar {
            position: fixed;
            top: 0; left: 0;
            width: var(--sidebar-w);
            height: 100vh;
            background: var(--sidebar-bg);
            display: flex;
            flex-direction: column;
            z-index: 1040;
            overflow-y: auto;
            scrollbar-width: none;
            transition: transform .3s ease;
        }
        #sidebar::-webkit-scrollbar { display: none; }

        .sidebar-brand {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 24px 20px 20px;
            border-bottom: 1px solid rgba(255,255,255,.07);
            text-decoration: none;
        }
        .sidebar-brand .brand-icon {
            width: 38px; height: 38px;
            background: linear-gradient(135deg, var(--accent), var(--accent-light));
            border-radius: 10px;
            display: flex; align-items: center; justify-content: center;
            font-size: 17px; color: #fff;
            box-shadow: 0 4px 12px rgba(79,70,229,.4);
            flex-shrink: 0;
        }
        .sidebar-brand .brand-name {
            font-size: 17px;
            font-weight: 800;
            color: #fff;
            letter-spacing: -.4px;
        }
        .sidebar-brand .brand-sub {
            font-size: 10px;
            color: rgba(255,255,255,.4);
            letter-spacing: .5px;
            text-transform: uppercase;
        }

        .sidebar-section-title {
            font-size: 10px;
            font-weight: 700;
            letter-spacing: 1.2px;
            text-transform: uppercase;
            color: rgba(255,255,255,.3);
            padding: 18px 20px 6px;
        }

        .sidebar-nav { padding: 8px 12px; flex: 1; }

        .sidebar-nav .nav-link {
            display: flex;
            align-items: center;
            gap: 11px;
            padding: 10px 14px;
            border-radius: 10px;
            color: rgba(255,255,255,.55);
            font-size: 13.5px;
            font-weight: 500;
            text-decoration: none;
            margin-bottom: 2px;
            transition: var(--transition);
            border: none;
            position: relative;
        }
        .sidebar-nav .nav-link i {
            width: 18px;
            text-align: center;
            font-size: 14px;
            flex-shrink: 0;
        }
        .sidebar-nav .nav-link:hover {
            background: rgba(255,255,255,.07);
            color: #fff;
        }
        .sidebar-nav .nav-link.active {
            background: var(--accent-glow);
            color: #a5b4fc;
            font-weight: 600;
        }
        .sidebar-nav .nav-link.active i { color: #818cf8; }

        .sidebar-footer {
            padding: 16px 12px;
            border-top: 1px solid rgba(255,255,255,.07);
        }
        .sidebar-footer .logout-btn {
            display: flex;
            align-items: center;
            gap: 10px;
            width: 100%;
            padding: 10px 14px;
            border-radius: 10px;
            background: rgba(239,68,68,.1);
            color: #fca5a5;
            font-size: 13.5px;
            font-weight: 500;
            border: none;
            text-decoration: none;
            transition: var(--transition);
        }
        .sidebar-footer .logout-btn:hover {
            background: rgba(239,68,68,.2);
            color: #fecaca;
        }

        /* ==================== TOPBAR ==================== */
        #topbar {
            position: fixed;
            top: 0;
            left: var(--sidebar-w);
            right: 0;
            height: 64px;
            background: rgba(255,255,255,.9);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            border-bottom: 1px solid rgba(0,0,0,.07);
            display: flex;
            align-items: center;
            justify-content: flex-end;
            padding: 0 28px;
            z-index: 1030;
            gap: 8px;
        }

        .topbar-btn {
            width: 38px; height: 38px;
            border-radius: 10px;
            border: none;
            background: transparent;
            color: var(--muted);
            display: flex; align-items: center; justify-content: center;
            font-size: 15px;
            cursor: pointer;
            transition: var(--transition);
            text-decoration: none;
            position: relative;
        }
        .topbar-btn:hover { background: var(--bg); color: var(--text); }

        .notif-badge {
            position: absolute;
            top: 4px; right: 4px;
            width: 16px; height: 16px;
            background: var(--danger);
            border-radius: 50%;
            font-size: 9px;
            color: #fff;
            font-weight: 700;
            display: flex; align-items: center; justify-content: center;
            border: 2px solid #fff;
        }

        .topbar-user {
            display: flex;
            align-items: center;
            gap: 9px;
            padding: 5px 10px;
            border-radius: 12px;
            cursor: pointer;
            transition: var(--transition);
            text-decoration: none;
            color: var(--text);
            border: none;
            background: transparent;
        }
        .topbar-user:hover { background: var(--bg); }
        .topbar-user img {
            width: 34px; height: 34px;
            border-radius: 10px;
            object-fit: cover;
        }
        .topbar-user .user-info { text-align: left; }
        .topbar-user .user-name {
            font-size: 13px;
            font-weight: 600;
            color: var(--text);
            line-height: 1.2;
            white-space: nowrap;
        }
        .topbar-user .user-role {
            font-size: 11px;
            color: var(--muted);
            line-height: 1;
        }

        /* ==================== MAIN CONTENT ==================== */
        #main-content {
            margin-left: var(--sidebar-w);
            padding-top: 64px;
            min-height: 100vh;
        }
        .page-content {
            padding: 28px 28px;
        }

        /* ==================== CARDS ==================== */
        .card {
            background: var(--card-bg);
            border-radius: var(--card-radius) !important;
            border: 1px solid rgba(0,0,0,.06) !important;
            box-shadow: var(--card-shadow) !important;
        }

        /* ==================== TABLES ==================== */
        .table thead th {
            background: #f8fafc;
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .6px;
            color: var(--muted);
            border-bottom: 1px solid #e2e8f0;
            padding: 12px 16px;
        }
        .table tbody td {
            padding: 14px 16px;
            vertical-align: middle;
            border-bottom: 1px solid #f1f5f9;
            font-size: 14px;
        }
        .table tbody tr:last-child td { border-bottom: none; }
        .table tbody tr:hover td { background: #fafbff; }

        /* ==================== FORM CONTROLS ==================== */
        .form-control, .form-select {
            border: 1.5px solid #e2e8f0;
            border-radius: 10px !important;
            font-size: 14px;
            padding: 10px 14px;
            transition: var(--transition);
            background: #fff;
        }
        .form-control:focus, .form-select:focus {
            border-color: var(--accent);
            box-shadow: 0 0 0 3px rgba(79,70,229,.12);
            background: #fff;
        }
        .form-label {
            font-size: 13px;
            font-weight: 600;
            color: var(--muted);
            margin-bottom: 6px;
            text-transform: uppercase;
            letter-spacing: .4px;
        }

        /* ==================== BUTTONS ==================== */
        .btn {
            font-size: 13.5px;
            font-weight: 600;
            border-radius: 10px !important;
            padding: 9px 18px;
            transition: var(--transition);
        }
        .btn-primary {
            background: var(--accent) !important;
            border-color: var(--accent) !important;
            box-shadow: 0 4px 12px rgba(79,70,229,.3);
        }
        .btn-primary:hover {
            background: #4338ca !important;
            border-color: #4338ca !important;
            transform: translateY(-1px);
            box-shadow: 0 6px 18px rgba(79,70,229,.38);
        }
        .btn-sm { padding: 6px 12px; font-size: 12.5px; }

        /* ==================== BADGES ==================== */
        .badge {
            font-size: 11px;
            font-weight: 600;
            padding: 4px 10px;
            border-radius: 6px;
        }

        /* ==================== ALERTS / FLASH ==================== */
        .alert {
            border: none !important;
            border-radius: 12px !important;
            font-size: 14px;
            font-weight: 500;
            padding: 14px 18px;
        }
        .alert-success { background: #ecfdf5; color: #065f46; }
        .alert-danger  { background: #fef2f2; color: #991b1b; }
        .alert-warning { background: #fffbeb; color: #92400e; }
        .alert-info    { background: #ecfeff; color: #155e75; }

        /* ==================== DROPDOWN (topbar) ==================== */
        .dropdown-menu {
            border: 1px solid rgba(0,0,0,.08);
            border-radius: 14px;
            box-shadow: 0 8px 30px rgba(0,0,0,.12);
            padding: 8px;
            min-width: 200px;
        }
        .dropdown-item {
            border-radius: 8px;
            font-size: 13.5px;
            padding: 9px 12px;
            transition: var(--transition);
        }
        .dropdown-item:hover { background: #f1f5f9; }
        .dropdown-divider { margin: 6px 0; border-color: #f1f5f9; }

        /* ==================== MODAL ==================== */
        .modal-content {
            border: none !important;
            border-radius: 18px !important;
            box-shadow: 0 20px 60px rgba(0,0,0,.15) !important;
        }
        .modal-header { padding: 22px 24px 16px; border-bottom: 1px solid #f1f5f9; }
        .modal-body   { padding: 16px 24px; }
        .modal-footer { padding: 16px 24px 22px; border-top: 1px solid #f1f5f9; }

        /* ==================== MOBILE ==================== */
        #sidebar-overlay {
            display: none;
            position: fixed; inset: 0;
            background: rgba(0,0,0,.5);
            z-index: 1039;
        }
        @media (max-width: 768px) {
            #sidebar { transform: translateX(-100%); }
            #sidebar.open { transform: translateX(0); }
            #sidebar-overlay.show { display: block; }
            #topbar { left: 0; }
            #main-content { margin-left: 0; }
        }

        /* ==================== SCROLLBAR ==================== */
        ::-webkit-scrollbar { width: 6px; }
        ::-webkit-scrollbar-track { background: transparent; }
        ::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 99px; }
        ::-webkit-scrollbar-thumb:hover { background: #94a3b8; }
        /* ==================== PASSWORD TOGGLE ==================== */
        .password-field-container {
            position: relative;
        }
        .password-toggle-icon {
            position: absolute;
            right: 12px;
            top: 50%;
            transform: translateY(-50%);
            border: none;
            background: transparent;
            color: var(--muted);
            cursor: pointer;
            padding: 4px;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: var(--transition);
            z-index: 10;
        }
        .password-toggle-icon:hover {
            color: var(--accent);
        }

        /* ==================== DARK MODE OVERRIDES ==================== */
        body.dark-mode {
            --bg:          #0f172a;
            --card-bg:     #1e293b;
            --text:        #f8fafc;
            --muted:       #94a3b8;
            --border-color:#334155;
        }
        body.dark-mode .text-muted {
            color: var(--muted) !important;
        }
        body.dark-mode .card,
        body.dark-mode .modal-content,
        body.dark-mode .bg-white {
            background: var(--card-bg) !important;
            color: var(--text) !important;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2) !important;
        }
        body.dark-mode .card-header,
        body.dark-mode .modal-header,
        body.dark-mode .modal-footer {
            background-color: var(--card-bg) !important;
            border-color: #334155 !important;
            color: var(--text) !important;
        }
        body.dark-mode .table,
        body.dark-mode th,
        body.dark-mode td,
        body.dark-mode tr {
            background-color: var(--card-bg) !important;
            color: var(--text) !important;
            border-color: #334155 !important;
        }
        body.dark-mode .table tbody tr:hover td {
            background-color: #334155 !important;
        }
        body.dark-mode .bg-light,
        body.dark-mode .bg-light-subtle,
        body.dark-mode .table thead,
        body.dark-mode .table thead th {
            background-color: #0f172a !important;
            color: var(--text) !important;
        }
        body.dark-mode .btn-light {
            background-color: #334155 !important;
            color: var(--text) !important;
            border-color: #475569 !important;
        }
        body.dark-mode .btn-light:hover {
            background-color: #475569 !important;
        }
        body.dark-mode .badge.bg-light {
            background-color: #334155 !important;
            color: var(--text) !important;
        }
        body.dark-mode .form-control,
        body.dark-mode .form-select,
        body.dark-mode textarea {
            background-color: #0f172a !important;
            color: var(--text) !important;
            border-color: #334155 !important;
        }
        body.dark-mode .form-control:focus,
        body.dark-mode .form-select:focus {
            border-color: var(--accent) !important;
            box-shadow: 0 0 0 3px rgba(79,70,229,.2) !important;
        }
        body.dark-mode .dropdown-menu {
            background-color: #1e293b !important;
            border-color: #334155 !important;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.3) !important;
        }
        body.dark-mode .dropdown-item {
            color: var(--text) !important;
        }
        body.dark-mode .dropdown-item:hover {
            background-color: #334155 !important;
        }
        body.dark-mode .dropdown-divider {
            border-color: #334155 !important;
        }
        body.dark-mode .breadcrumb-item.active {
            color: var(--text) !important;
        }
        body.dark-mode .bg-light,
        body.dark-mode .p-3.bg-light.rounded-4 {
            background-color: #0f172a !important;
            border-color: #334155 !important;
        }
        body.dark-mode #topbar {
            background-color: rgba(30, 41, 59, 0.9) !important;
            border-bottom-color: #334155 !important;
        }
        body.dark-mode .text-dark {
            color: var(--text) !important;
        }
        body.dark-mode .demo-cred-btn {
            background-color: #0f172a !important;
            border-color: #334155 !important;
            color: var(--text) !important;
        }
        body.dark-mode .demo-cred-btn:hover {
            background-color: var(--accent-glow) !important;
            border-color: var(--accent) !important;
            color: var(--accent) !important;
        }
    </style>
</head>
<body>

<?php if ($user_role !== 'guest'): 
    $global_avatar_url = !empty($_SESSION['avatar']) && file_exists(__DIR__ . '/../' . $_SESSION['avatar'])
        ? $_SESSION['avatar']
        : 'https://ui-avatars.com/api/?name=' . urlencode($_SESSION['username'] ?? 'U') . '&background=4f46e5&color=fff&size=64';
?>
<!-- ===================== SIDEBAR ===================== -->
<div id="sidebar-overlay" onclick="closeSidebar()"></div>
<aside id="sidebar">

    <a href="index.php" class="sidebar-brand">
        <div class="brand-icon">
            <i class="fas fa-heart-pulse"></i>
        </div>
        <div>
            <div class="brand-name">CarePulse</div>
            <div class="brand-sub">Health System</div>
        </div>
    </a>

    <nav class="sidebar-nav">
        <div class="sidebar-section-title">Main</div>

        <a href="admin_dashboard.php" class="nav-link <?= in_array($current_page, ['admin_dashboard.php', 'index.php']) ? 'active' : '' ?>">
            <i class="fas fa-chart-pie"></i> Dashboard
        </a>

        <?php if (in_array($user_role, ['admin', 'doctor', 'receptionist'])): ?>
        <a href="patients.php" class="nav-link <?= $current_page === 'patients.php' ? 'active' : '' ?>">
            <i class="fas fa-user-injured"></i> Patients
        </a>
        <?php endif; ?>

        <?php if (in_array($user_role, ['admin', 'receptionist'])): ?>
        <a href="doctors.php" class="nav-link <?= $current_page === 'doctors.php' ? 'active' : '' ?>">
            <i class="fas fa-user-md"></i> Doctors
        </a>
        <a href="doctor_schedule.php" class="nav-link <?= $current_page === 'doctor_schedule.php' ? 'active' : '' ?>">
            <i class="fas fa-calendar-alt"></i> Doctor Schedules
        </a>
        <a href="wards_beds.php" class="nav-link <?= in_array($current_page, ['wards_beds.php', 'admissions.php']) ? 'active' : '' ?>">
            <i class="fas fa-procedures"></i> IPD Wards & Beds
        </a>
        <?php endif; ?>

        <?php if (in_array($user_role, ['admin', 'doctor', 'receptionist'])): ?>
        <a href="appointments.php" class="nav-link <?= in_array($current_page, ['appointments.php','add_appointment.php']) ? 'active' : '' ?>">
            <i class="fas fa-calendar-check"></i> Appointments
        </a>
        <?php endif; ?>

        <?php if (in_array($user_role, ['admin', 'doctor', 'lab_tech'])): ?>
        <a href="lab_portal.php" class="nav-link <?= $current_page === 'lab_portal.php' ? 'active' : '' ?>">
            <i class="fas fa-flask"></i> Diagnostics Lab
        </a>
        <?php endif; ?>

        <?php if (in_array($user_role, ['admin', 'pharmacist'])): ?>
        <a href="pharmacy.php" class="nav-link <?= in_array($current_page, ['pharmacy.php', 'dispenser.php']) ? 'active' : '' ?>">
            <i class="fas fa-pills"></i> Pharmacy & Inventory
        </a>
        <?php endif; ?>

        <!-- Enterprise Clinical Extensions -->
        <?php if (in_array($user_role, ['admin', 'doctor', 'receptionist', 'lab_tech'])): ?>
        <div class="sidebar-section-title mt-2">Enterprise Clinical</div>
        <?php if (in_array($user_role, ['admin', 'doctor'])): ?>
        <a href="telehealth.php" class="nav-link <?= $current_page === 'telehealth.php' ? 'active' : '' ?>">
            <i class="fas fa-video text-info"></i> Telehealth & Vitals
        </a>
        <?php endif; ?>

        <?php if (in_array($user_role, ['admin', 'doctor', 'receptionist'])): ?>
        <a href="shift_handoff.php" class="nav-link <?= $current_page === 'shift_handoff.php' ? 'active' : '' ?>">
            <i class="fas fa-exchange-alt text-warning"></i> Shift Handoff
        </a>
        <?php endif; ?>

        <?php if (in_array($user_role, ['admin', 'doctor', 'lab_tech'])): ?>
        <a href="dicom_viewer.php" class="nav-link <?= $current_page === 'dicom_viewer.php' ? 'active' : '' ?>">
            <i class="fas fa-microscope text-success"></i> Radiology DICOM
        </a>
        <?php endif; ?>
        <?php endif; ?>

        <?php if (in_array($user_role, ['admin', 'receptionist'])): ?>
        <a href="billing.php" class="nav-link <?= in_array($current_page, ['billing.php', 'invoice_details.php']) ? 'active' : '' ?>">
            <i class="fas fa-file-invoice-dollar"></i> Billing & Ledgers
        </a>
        <?php endif; ?>

        <?php if ($user_role === 'admin'): ?>
        <div class="sidebar-section-title mt-2">Admin</div>
        <a href="users.php" class="nav-link <?= $current_page === 'users.php' ? 'active' : '' ?>">
            <i class="fas fa-users-cog"></i> User Management
        </a>
        <a href="insurance_claims.php" class="nav-link <?= $current_page === 'insurance_claims.php' ? 'active' : '' ?>">
            <i class="fas fa-file-invoice"></i> Insurance Claims
        </a>
        <a href="audit_logs.php" class="nav-link <?= $current_page === 'audit_logs.php' ? 'active' : '' ?>">
            <i class="fas fa-shield-alt"></i> Audit Logs
        </a>
        <?php endif; ?>
    </nav>

    <!-- User mini card at bottom of sidebar -->
    <div style="padding: 12px; border-top: 1px solid rgba(255,255,255,.07); margin-top: auto;">
        <a href="profile.php" style="display:flex;align-items:center;gap:10px;text-decoration:none;padding:10px 12px;border-radius:12px;background:rgba(255,255,255,.05);transition:var(--transition);" onmouseover="this.style.background='rgba(255,255,255,.1)'" onmouseout="this.style.background='rgba(255,255,255,.05)'">
            <img src="<?= $global_avatar_url ?>" width="36" height="36" style="border-radius:9px;object-fit:cover;" alt="">
            <div style="flex:1;min-width:0;">
                <div style="font-size:13px;font-weight:600;color:#fff;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;"><?= esc($_SESSION['full_name'] ?? $_SESSION['username'] ?? 'User') ?></div>
                <div style="font-size:11px;color:rgba(255,255,255,.4);"><?= ucfirst($user_role) ?></div>
            </div>
        </a>
    </div>

    <div class="sidebar-footer">
        <a href="logout.php" class="logout-btn">
            <i class="fas fa-sign-out-alt"></i> Sign Out
        </a>
    </div>

</aside>

<!-- ===================== TOPBAR ===================== -->
<header id="topbar">
    <!-- Mobile menu toggle -->
    <button class="topbar-btn d-md-none me-auto" onclick="openSidebar()" style="margin-left:-8px;">
        <i class="fas fa-bars"></i>
    </button>

    <!-- Global Command Palette Trigger -->
    <button class="btn btn-light btn-sm me-auto d-none d-md-flex align-items-center gap-2 border shadow-sm px-3" onclick="openCommandPalette()" style="border-radius: 20px !important;">
        <i class="fas fa-search text-primary"></i>
        <span class="text-muted small">Search patients, modules...</span>
        <kbd class="bg-dark text-white px-2 py-0 small rounded" style="font-size:10px;">Ctrl + K</kbd>
    </button>

    <!-- Notifications -->
    <div class="dropdown">
        <button class="topbar-btn" data-bs-toggle="dropdown" aria-expanded="false">
            <i class="fas fa-bell"></i>
            <?php if ($notifications > 0): ?>
                <span class="notif-badge"><?= $notifications ?></span>
            <?php endif; ?>
        </button>
        <ul class="dropdown-menu dropdown-menu-end">
            <li>
                <div style="padding:10px 14px 6px;">
                    <div style="font-size:13px;font-weight:700;color:var(--text);">Notifications</div>
                    <div style="font-size:12px;color:var(--muted);"><?= $notifications ?> pending appointments</div>
                </div>
            </li>
            <li><hr class="dropdown-divider"></li>
            <li><a class="dropdown-item" href="appointments.php"><i class="fas fa-calendar-alt me-2 text-primary"></i>View All Appointments</a></li>
        </ul>
    </div>

    <!-- Theme Switcher -->
    <button class="topbar-btn me-2" id="darkModeToggle" title="Toggle Theme" style="border: none; background: transparent; cursor: pointer;">
        <i class="fas fa-moon" id="themeIcon"></i>
    </button>

    <!-- User Menu -->
    <div class="dropdown">
        <button class="topbar-user" data-bs-toggle="dropdown" aria-expanded="false">
            <img src="<?= $global_avatar_url ?>" alt="" style="object-fit: cover;">
            <div class="user-info d-none d-sm-block">
                <div class="user-name"><?= esc($_SESSION['full_name'] ?? $_SESSION['username'] ?? 'User') ?></div>
                <div class="user-role"><?= ucfirst($user_role) ?></div>
            </div>
            <i class="fas fa-chevron-down" style="font-size:11px;color:var(--muted);margin-left:2px;"></i>
        </button>
        <ul class="dropdown-menu dropdown-menu-end">
            <li>
                <div style="padding:10px 14px 8px;">
                    <div style="font-size:13px;font-weight:700;"><?= esc($_SESSION['full_name'] ?? $_SESSION['username']) ?></div>
                    <div style="font-size:12px;color:var(--muted);"><?= esc($_SESSION['email'] ?? '') ?></div>
                </div>
            </li>
            <li><hr class="dropdown-divider"></li>
            <li><a class="dropdown-item" href="profile.php"><i class="fas fa-user me-2 text-muted"></i>My Profile</a></li>
            <?php if ($user_role === 'admin'): ?>
            <li><a class="dropdown-item" href="users.php"><i class="fas fa-users-cog me-2 text-muted"></i>User Management</a></li>
            <?php endif; ?>
            <li><hr class="dropdown-divider"></li>
            <li><a class="dropdown-item text-danger" href="logout.php"><i class="fas fa-sign-out-alt me-2"></i>Sign Out</a></li>
        </ul>
    </div>
</header>

<!-- Command Palette Modal -->
<div class="modal fade" id="commandPaletteModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 16px !important;">
            <div class="modal-body p-0">
                <div class="p-3 border-bottom d-flex align-items-center bg-light" style="border-top-left-radius: 16px; border-top-right-radius: 16px;">
                    <i class="fas fa-search text-muted me-2 fa-lg"></i>
                    <input type="text" id="cmdInput" class="form-control border-0 bg-transparent shadow-none" placeholder="Type a command or search (e.g. Patient, Telehealth, Shift, Pharmacy)..." style="font-size: 16px;" onkeyup="filterCommandPalette()">
                    <button type="button" class="btn-close ms-2" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="p-2" id="cmdList" style="max-height: 380px; overflow-y: auto;">
                    <?php if (in_array($user_role, ['admin', 'doctor', 'receptionist'])): ?>
                    <a href="patients.php" class="cmd-item d-flex align-items-center p-3 text-decoration-none text-dark rounded-3 mb-1 hover-bg-light">
                        <i class="fas fa-user-injured text-primary fa-lg me-3"></i>
                        <div><strong class="d-block">Patients Directory</strong><small class="text-muted">Browse patient records & EHR files</small></div>
                    </a>
                    <a href="add_appointment.php" class="cmd-item d-flex align-items-center p-3 text-decoration-none text-dark rounded-3 mb-1 hover-bg-light">
                        <i class="fas fa-calendar-plus text-success fa-lg me-3"></i>
                        <div><strong class="d-block">Schedule New Appointment</strong><small class="text-muted">Book consultation with specialist doctor</small></div>
                    </a>
                    <?php endif; ?>

                    <?php if (in_array($user_role, ['admin', 'doctor'])): ?>
                    <a href="telehealth.php" class="cmd-item d-flex align-items-center p-3 text-decoration-none text-dark rounded-3 mb-1 hover-bg-light">
                        <i class="fas fa-video text-info fa-lg me-3"></i>
                        <div><strong class="d-block">Virtual Telehealth Room</strong><small class="text-muted">Launch live video consultation & telemetry HUD</small></div>
                    </a>
                    <?php endif; ?>

                    <?php if (in_array($user_role, ['admin', 'doctor', 'receptionist'])): ?>
                    <a href="shift_handoff.php" class="cmd-item d-flex align-items-center p-3 text-decoration-none text-dark rounded-3 mb-1 hover-bg-light">
                        <i class="fas fa-exchange-alt text-warning fa-lg me-3"></i>
                        <div><strong class="d-block">Shift Handoff Management</strong><small class="text-muted">Duty handover logs & task board</small></div>
                    </a>
                    <?php endif; ?>

                    <?php if (in_array($user_role, ['admin', 'doctor', 'lab_tech'])): ?>
                    <a href="dicom_viewer.php" class="cmd-item d-flex align-items-center p-3 text-decoration-none text-dark rounded-3 mb-1 hover-bg-light">
                        <i class="fas fa-microscope text-danger fa-lg me-3"></i>
                        <div><strong class="d-block">Radiology DICOM Viewer</strong><small class="text-muted">Inspect X-Ray, CT Scan, and MRI medical images</small></div>
                    </a>
                    <a href="lab_portal.php" class="cmd-item d-flex align-items-center p-3 text-decoration-none text-dark rounded-3 mb-1 hover-bg-light">
                        <i class="fas fa-flask text-info fa-lg me-3"></i>
                        <div><strong class="d-block">Diagnostics Lab Portal</strong><small class="text-muted">Manage lab orders and blood tests</small></div>
                    </a>
                    <?php endif; ?>

                    <?php if (in_array($user_role, ['admin', 'pharmacist'])): ?>
                    <a href="pharmacy.php" class="cmd-item d-flex align-items-center p-3 text-decoration-none text-dark rounded-3 mb-1 hover-bg-light">
                        <i class="fas fa-pills text-purple fa-lg me-3"></i>
                        <div><strong class="d-block">Pharmacy Inventory</strong><small class="text-muted">Manage drug stock, pricing, and safety checks</small></div>
                    </a>
                    <?php endif; ?>

                    <?php if ($user_role === 'admin'): ?>
                    <a href="users.php" class="cmd-item d-flex align-items-center p-3 text-decoration-none text-dark rounded-3 mb-1 hover-bg-light">
                        <i class="fas fa-users-cog text-secondary fa-lg me-3"></i>
                        <div><strong class="d-block">User Management</strong><small class="text-muted">Manage staff accounts and security credentials</small></div>
                    </a>
                    <?php endif; ?>
                </div>
            </div>
            <div class="px-3 py-2 bg-light text-muted small border-top d-flex justify-content-between" style="border-bottom-left-radius: 16px; border-bottom-right-radius: 16px;">
                <span>Use <kbd>↑</kbd> <kbd>↓</kbd> to navigate, <kbd>ESC</kbd> to close</span>
                <span>CarePulse Command Center</span>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('keydown', function(e) {
    if ((e.ctrlKey || e.metaKey) && e.key.toLowerCase() === 'k') {
        e.preventDefault();
        openCommandPalette();
    }
});

function openCommandPalette() {
    const modalEl = document.getElementById('commandPaletteModal');
    if (modalEl) {
        const modal = new bootstrap.Modal(modalEl);
        modal.show();
        setTimeout(() => document.getElementById('cmdInput').focus(), 200);
    }
}

function filterCommandPalette() {
    const val = document.getElementById('cmdInput').value.toLowerCase();
    const items = document.querySelectorAll('#cmdList .cmd-item');
    items.forEach(item => {
        const text = item.innerText.toLowerCase();
        item.style.display = text.includes(val) ? 'flex' : 'none';
    });
}
</script>


<!-- ===================== MAIN AREA ===================== -->
<div id="main-content">
<div class="page-content">
<?php display_flash(); ?>

<?php else: ?>
<div class="container py-4">
<?php endif; ?>