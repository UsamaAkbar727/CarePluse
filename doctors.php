<?php
session_start();
if (!isset($_SESSION['admin_user'])) {
    header("Location: login.php");
    exit();
}
include('config.php');
?>
<!DOCTYPE html>
<html>

<head>
    <title>CarePulse | Doctors List</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <style>
        :root {
            --primary: #2c3e50;
            --accent: #3498db;
            --sidebar-bg: #1a2a3a;
            --sidebar-hover: rgba(52, 152, 219, 0.1);
            --text-light: #bdc3c7;
            --card-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
            --card-hover-shadow: 0 15px 30px rgba(0, 0, 0, 0.1);
        }

        body {
            background: linear-gradient(135deg, #f8f9fa 0%, #f0f3ff 100%);
            font-family: 'Segoe UI', -apple-system, BlinkMacSystemFont, sans-serif;
            display: flex;
            min-height: 100vh;
            margin: 0;
        }

        /* Enhanced Sidebar */
        .sidebar {
            width: 260px;
            height: 100vh;
            background: linear-gradient(180deg, var(--sidebar-bg) 0%, #15202b 100%);
            color: #fff;
            position: fixed;
            padding: 30px 20px;
            z-index: 1000;
            box-shadow: 5px 0 20px rgba(0, 0, 0, 0.15);
            border-right: 1px solid rgba(255, 255, 255, 0.05);
            transition: all 0.3s ease;
        }

        .sidebar::before {
            content: '';
            position: absolute;
            top: 0;
            right: 0;
            width: 3px;
            height: 100%;
            background: linear-gradient(to bottom, var(--accent), #5dade2);
            opacity: 0.7;
        }

        .sidebar-brand {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 50px;
            padding-bottom: 25px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            position: relative;
        }

        .sidebar-brand::after {
            content: '';
            position: absolute;
            bottom: -1px;
            left: 0;
            width: 50px;
            height: 2px;
            background: var(--accent);
            border-radius: 1px;
        }

        .sidebar-brand i {
            font-size: 28px;
            color: var(--accent);
            background: rgba(52, 152, 219, 0.1);
            padding: 10px;
            border-radius: 10px;
            box-shadow: 0 4px 10px rgba(52, 152, 219, 0.2);
            transition: transform 0.3s ease;
        }

        .sidebar-brand:hover i {
            transform: scale(1.1) rotate(5deg);
        }

        .sidebar-brand span {
            font-size: 22px;
            font-weight: 700;
            letter-spacing: -0.5px;
            color: #fff;
            text-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .sidebar a {
            display: flex;
            align-items: center;
            color: var(--text-light);
            padding: 16px 18px;
            text-decoration: none;
            border-radius: 10px;
            margin-bottom: 8px;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            font-weight: 500;
            position: relative;
            overflow: hidden;
            border: 1px solid transparent;
        }

        .sidebar a::before {
            content: '';
            position: absolute;
            left: 0;
            top: 0;
            height: 100%;
            width: 3px;
            background: var(--accent);
            transform: translateX(-10px);
            transition: transform 0.3s ease;
            border-radius: 0 2px 2px 0;
        }

        .sidebar i {
            margin-right: 15px;
            width: 20px;
            font-size: 18px;
            transition: transform 0.3s ease;
        }

        .sidebar a:hover {
            background: var(--sidebar-hover);
            color: #fff;
            transform: translateX(5px);
            border-color: rgba(52, 152, 219, 0.2);
        }

        .sidebar a:hover::before {
            transform: translateX(0);
        }

        .sidebar a:hover i {
            transform: scale(1.1);
        }

        .sidebar a.active {
            background: linear-gradient(135deg, var(--accent), #5dade2);
            color: #fff;
            box-shadow: 0 8px 20px rgba(52, 152, 219, 0.25);
            border: none;
            transform: translateX(0);
        }

        .sidebar a.active::before {
            transform: translateX(0);
            background: rgba(255, 255, 255, 0.3);
        }

        .sidebar a.active i {
            transform: scale(1.1);
        }

        /* Main Content */
        .main-content {
            margin-left: 260px;
            width: calc(100% - 260px);
            padding: 50px;
            animation: fadeIn 0.6s ease-out;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(20px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* Enhanced Doc Cards */
        .doc-card {
            border: none;
            border-radius: 18px;
            background: white;
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            position: relative;
            overflow: hidden;
            box-shadow: var(--card-shadow);
            border: 1px solid rgba(0, 0, 0, 0.03);
        }

        .doc-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(to right, var(--accent), #5dade2);
        }

        .doc-card:hover {
            transform: translateY(-8px);
            box-shadow: var(--card-hover-shadow);
        }

        .doc-card:hover::after {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: linear-gradient(45deg, transparent, rgba(52, 152, 219, 0.05), transparent);
            transform: rotate(45deg);
            animation: shine 1.5s ease-out;
        }

        @keyframes shine {
            0% {
                transform: rotate(45deg) translateX(-100%);
            }

            100% {
                transform: rotate(45deg) translateX(100%);
            }
        }

        /* Card Content Enhancement */
        .card-body {
            padding: 25px;
            position: relative;
            z-index: 1;
        }

        .card-title {
            color: var(--primary);
            font-weight: 700;
            font-size: 18px;
            margin-bottom: 12px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .card-title i {
            color: var(--accent);
            font-size: 20px;
        }

        .card-text {
            color: #6c757d;
            font-size: 14px;
            line-height: 1.6;
            margin-bottom: 15px;
        }

        .card-meta {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding-top: 15px;
            border-top: 1px solid rgba(0, 0, 0, 0.05);
            font-size: 12px;
            color: #95a5a6;
        }

        .card-badge {
            background: rgba(52, 152, 219, 0.1);
            color: var(--accent);
            padding: 4px 12px;
            border-radius: 20px;
            font-weight: 600;
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .card-actions {
            display: flex;
            gap: 10px;
            margin-top: 20px;
        }

        .btn-card {
            flex: 1;
            padding: 10px;
            border-radius: 10px;
            border: none;
            font-weight: 600;
            font-size: 13px;
            transition: all 0.3s ease;
            cursor: pointer;
        }

        .btn-view {
            background: rgba(52, 152, 219, 0.1);
            color: var(--accent);
        }

        .btn-view:hover {
            background: var(--accent);
            color: white;
            transform: translateY(-2px);
        }

        .btn-download {
            background: rgba(46, 204, 113, 0.1);
            color: #2ecc71;
        }

        .btn-download:hover {
            background: #2ecc71;
            color: white;
            transform: translateY(-2px);
        }

        /* Grid Layout for Cards */
        .doc-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 30px;
            margin-top: 30px;
        }

        /* Header Enhancement */
        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 40px;
            padding-bottom: 25px;
            border-bottom: 2px solid rgba(0, 0, 0, 0.05);
        }

        .page-title {
            font-size: 32px;
            font-weight: 700;
            color: var(--primary);
            position: relative;
            display: inline-block;
        }

        .page-title::after {
            content: '';
            position: absolute;
            bottom: -12px;
            left: 0;
            width: 50px;
            height: 3px;
            background: var(--accent);
            border-radius: 2px;
        }

        /* Scrollbar Styling */
        ::-webkit-scrollbar {
            width: 8px;
            height: 8px;
        }

        ::-webkit-scrollbar-track {
            background: #f1f1f1;
            border-radius: 10px;
        }

        ::-webkit-scrollbar-thumb {
            background: linear-gradient(to bottom, var(--accent), #5dade2);
            border-radius: 10px;
        }

        ::-webkit-scrollbar-thumb:hover {
            background: linear-gradient(to bottom, #2980b9, #3498db);
        }

        /* Responsive Design */
        @media (max-width: 1200px) {
            .doc-grid {
                grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
                gap: 25px;
            }
        }

        @media (max-width: 992px) {
            .doc-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        @media (max-width: 768px) {
            .sidebar {
                width: 240px;
                transform: translateX(-100%);
            }

            .sidebar.active {
                transform: translateX(0);
            }

            .main-content {
                margin-left: 0;
                width: 100%;
                padding: 30px 20px;
            }

            .doc-grid {
                grid-template-columns: 1fr;
            }

            .page-header {
                flex-direction: column;
                gap: 20px;
                align-items: flex-start;
            }
        }

        /* Quick Stats */
        .quick-stats {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 20px;
            margin-bottom: 40px;
        }

        .stat-item {
            background: white;
            padding: 20px;
            border-radius: 15px;
            text-align: center;
            box-shadow: var(--card-shadow);
            transition: transform 0.3s ease;
        }

        .stat-item:hover {
            transform: translateY(-5px);
        }

        .stat-number {
            font-size: 32px;
            font-weight: 700;
            color: var(--accent);
            margin-bottom: 5px;
        }

        .stat-label {
            font-size: 14px;
            color: #7f8c8d;
            font-weight: 600;
        }
    </style>
</head>

<body>

    <div class="sidebar">
        <div class="sidebar-brand">
            <i class="fa-solid fa-heart-pulse"></i>
            <h2>CarePulse</h2>
        </div>
        <a href="index.php"><i class="fa-solid fa-house"></i> Dashboard</a>
        <a href="add_appointment.php"><i class="fa-solid fa-calendar-plus"></i> New Appt</a>
        <a href="patients.php"><i class="fa-solid fa-hospital-user"></i> Patients</a>
        <a href="doctors.php" class="active"><i class="fa-solid fa-user-doctor"></i> Doctors</a>
        <div style="margin-top: 50px;">
            <a href="logout.php" style="color: #ff4d4d;"><i class="fa-solid fa-right-from-bracket"></i> Logout</a>
        </div>
    </div>

    <div class="main-content">
        <h1 class="mb-4">Specialist Doctors</h1>
        <div class="row">
            <div class="col-md-4 mb-4">
                <div class="card doc-card p-4 text-center shadow-sm">
                    <i class="fa fa-user-md fa-3x text-primary mb-3"></i>
                    <h5 class="fw-bold">Dr. Mussa </h5>
                    <p class="text-muted small">General Physician</p>
                    <span class="badge bg-success">Available</span>
                </div>
            </div>

            <div class="col-md-4 mb-4">
                <div class="card doc-card p-4 text-center shadow-sm">
                    <i class="fa fa-stethoscope fa-3x text-info mb-3"></i>
                    <h5 class="fw-bold">Dr. Usama Akbar</h5>
                    <p class="text-muted small">Neurologist</p>
                    <span class="badge bg-success">Available</span>
                </div>
            </div>

            <div class="col-md-4 mb-4">
                <div class="card doc-card p-4 text-center shadow-sm">
                    <i class="fa fa-user-doctor fa-3x text-warning mb-3"></i>
                    <h5 class="fw-bold">Dr. Sultan JutT </h5>
                    <p class="text-muted small">Orthopedic Surgeon</p>
                    <span class="badge bg-success">Available</span>
                </div>
            </div>

            <div class="col-md-4 mb-4">
                <div class="card doc-card p-4 text-center shadow-sm">
                    <i class="fa fa-heart-pulse fa-3x text-danger mb-3"></i>
                    <h5 class="fw-bold">Dr. Mahii Khan</h5>
                    <p class="text-muted small">Cardiologist</p>
                    <span class="badge bg-danger">In Surgery</span>
                </div>
            </div>
        </div>
    </div>

</body>

</html>