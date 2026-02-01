<?php
session_start();
if (!isset($_SESSION['admin_user'])) {
    header("Location: login.php");
    exit();
}
include('config.php');
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>CarePulse | Patients List</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root {
            --primary: #2c3e50;
            --accent: #3498db;
            --accent-light: #5dade2;
            --sidebar-bg: linear-gradient(180deg, #2c3e50 0%, #1a2530 100%);
            --sidebar-hover: rgba(52, 152, 219, 0.15);
            --text-light: #ecf0f1;
            --text-muted: #95a5a6;
            --card-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
            --transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        body {
            background: linear-gradient(135deg, #f8f9fa 0%, #f1f8ff 100%);
            font-family: 'Segoe UI', -apple-system, BlinkMacSystemFont, sans-serif;
            margin: 0;
            min-height: 100vh;
        }

        /* Modern Sidebar */
        .sidebar {
            width: 250px;
            height: 100vh;
            background: var(--sidebar-bg);
            color: white;
            position: fixed;
            padding: 25px 0;
            box-shadow: 3px 0 15px rgba(0, 0, 0, 0.1);
            z-index: 1000;
            border-right: 1px solid rgba(255, 255, 255, 0.05);
            transition: var(--transition);
        }

        .sidebar::before {
            content: '';
            position: absolute;
            top: 0;
            right: 0;
            width: 3px;
            height: 100%;
            background: linear-gradient(to bottom, var(--accent), var(--accent-light));
            opacity: 0.7;
        }

        /* Sidebar Brand */
        .sidebar-brand {
            padding: 0 25px 25px;
            margin-bottom: 20px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }

        .brand-logo {
            display: flex;
            align-items: center;
            gap: 12px;
            color: var(--text-light);
            text-decoration: none;
        }

        .brand-logo i {
            font-size: 26px;
            color: var(--accent);
            background: rgba(52, 152, 219, 0.15);
            padding: 10px;
            border-radius: 10px;
            transition: var(--transition);
        }

        .brand-logo:hover i {
            transform: rotate(15deg);
            background: rgba(52, 152, 219, 0.25);
        }

        .brand-logo span {
            font-size: 20px;
            font-weight: 700;
            letter-spacing: -0.5px;
        }

        /* Sidebar Menu */
        .sidebar-menu {
            padding: 0 15px;
        }

        .nav-item {
            margin-bottom: 5px;
            list-style: none;
        }

        .nav-link {
            color: var(--text-muted);
            padding: 14px 20px;
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 15px;
            border-radius: 10px;
            transition: var(--transition);
            position: relative;
            overflow: hidden;
            font-weight: 500;
            letter-spacing: 0.3px;
        }

        .nav-link::before {
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

        .nav-link i {
            width: 22px;
            font-size: 18px;
            text-align: center;
            transition: transform 0.3s ease;
        }

        .nav-link:hover {
            color: var(--text-light);
            background: var(--sidebar-hover);
            transform: translateX(5px);
            padding-left: 25px;
        }

        .nav-link:hover::before {
            transform: translateX(0);
        }

        .nav-link:hover i {
            transform: scale(1.1);
        }

        .nav-link.active {
            color: white;
            background: linear-gradient(135deg, var(--accent), var(--accent-light));
            box-shadow: 0 5px 15px rgba(52, 152, 219, 0.25);
            padding-left: 25px;
        }

        .nav-link.active::before {
            transform: translateX(0);
            background: rgba(255, 255, 255, 0.3);
        }

        .nav-link.active i {
            transform: scale(1.1);
        }

        /* Submenu Indicators */
        .nav-item.has-submenu .nav-link::after {
            content: '›';
            margin-left: auto;
            font-size: 18px;
            transition: transform 0.3s ease;
        }

        .nav-item.has-submenu.active .nav-link::after {
            transform: rotate(90deg);
        }

        /* Main Content */
        .main-content {
            margin-left: 250px;
            padding: 40px;
            min-height: 100vh;
            animation: fadeIn 0.5s ease-out;
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

        /* Dashboard Header */
        .dashboard-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 40px;
            padding-bottom: 25px;
            border-bottom: 2px solid rgba(0, 0, 0, 0.05);
        }

        .page-title {
            font-size: 28px;
            font-weight: 700;
            color: var(--primary);
            position: relative;
            display: inline-block;
        }

        .page-title::after {
            content: '';
            position: absolute;
            bottom: -10px;
            left: 0;
            width: 50px;
            height: 3px;
            background: var(--accent);
            border-radius: 2px;
        }

        /* Cards Grid */
        .cards-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 25px;
            margin-bottom: 40px;
        }

        .card {
            background: white;
            border-radius: 15px;
            padding: 25px;
            box-shadow: var(--card-shadow);
            transition: var(--transition);
            border: 1px solid rgba(0, 0, 0, 0.03);
            position: relative;
            overflow: hidden;
        }

        .card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(to right, var(--accent), var(--accent-light));
        }

        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
        }

        .card-icon {
            width: 50px;
            height: 50px;
            background: linear-gradient(135deg, var(--accent), var(--accent-light));
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 20px;
        }

        .card-icon i {
            color: white;
            font-size: 22px;
        }

        .card-title {
            font-size: 16px;
            font-weight: 600;
            color: var(--primary);
            margin-bottom: 10px;
        }

        .card-value {
            font-size: 28px;
            font-weight: 700;
            color: var(--primary);
            margin-bottom: 15px;
        }

        .card-trend {
            font-size: 13px;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 5px;
        }

        .trend-up {
            color: #2ecc71;
        }

        .trend-down {
            color: #e74c3c;
        }

        /* Quick Stats */
        .quick-stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 40px;
        }

        .stat-item {
            background: white;
            padding: 20px;
            border-radius: 12px;
            text-align: center;
            box-shadow: var(--card-shadow);
            transition: var(--transition);
            border: 1px solid rgba(0, 0, 0, 0.03);
        }

        .stat-item:hover {
            transform: translateY(-3px);
        }

        .stat-number {
            font-size: 32px;
            font-weight: 700;
            color: var(--accent);
            margin-bottom: 5px;
        }

        .stat-label {
            font-size: 14px;
            color: var(--text-muted);
            font-weight: 600;
        }

        /* Scrollbar Styling */
        ::-webkit-scrollbar {
            width: 6px;
        }

        ::-webkit-scrollbar-track {
            background: #f1f1f1;
            border-radius: 10px;
        }

        ::-webkit-scrollbar-thumb {
            background: linear-gradient(to bottom, var(--accent), var(--accent-light));
            border-radius: 10px;
        }

        /* Mobile Sidebar Toggle */
        .sidebar-toggle {
            display: none;
            position: fixed;
            top: 20px;
            left: 20px;
            z-index: 1001;
            background: var(--accent);
            color: white;
            border: none;
            width: 40px;
            height: 40px;
            border-radius: 10px;
            cursor: pointer;
            font-size: 20px;
            box-shadow: 0 4px 12px rgba(52, 152, 219, 0.25);
        }

        /* Responsive Design */
        @media (max-width: 992px) {
            .sidebar {
                transform: translateX(-100%);
            }

            .sidebar.active {
                transform: translateX(0);
            }

            .main-content {
                margin-left: 0;
                padding: 30px 20px;
            }

            .sidebar-toggle {
                display: flex;
                align-items: center;
                justify-content: center;
            }

            .cards-grid {
                grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            }
        }

        @media (max-width: 768px) {
            .cards-grid {
                grid-template-columns: 1fr;
            }

            .quick-stats {
                grid-template-columns: repeat(2, 1fr);
            }

            .dashboard-header {
                flex-direction: column;
                gap: 15px;
                align-items: flex-start;
            }
        }

        /* Loading Animation */
        @keyframes shimmer {
            0% {
                background-position: -200px 0;
            }

            100% {
                background-position: 200px 0;
            }
        }

        .loading {
            background: linear-gradient(90deg, #f0f0f0 25%, #e0e0e0 50%, #f0f0f0 75%);
            background-size: 200px 100%;
            animation: shimmer 1.5s infinite;
        }
    </style>
</head>

<body>

    <div class="sidebar d-flex flex-column p-3">
        <h3 class="mb-4 text-info"><i class="fa-solid fa-heart-pulse"></i> CarePulse</h3>
        <ul class="nav nav-pills flex-column mb-auto">
            <li class="nav-item mb-2"><a href="index.php" class="nav-link"><i class="fa fa-home me-2"></i> Dashboard</a></li>
            <li class="nav-item mb-2"><a href="add_appointment.php" class="nav-link"><i class="fa fa-calendar-plus me-2"></i> New Appt</a></li>
            <li class="nav-item mb-2"><a href="patients.php" class="nav-link active"><i class="fa fa-user-injured me-2"></i> Patients</a></li>
            <li class="nav-item mb-2"><a href="doctors.php" class="nav-link"><i class="fa fa-user-md me-2"></i> Doctors</a></li>
        </ul>
        <hr>
        <a href="logout.php" class="btn btn-outline-danger btn-sm"><i class="fa fa-sign-out-alt me-2"></i>Logout</a>
    </div>

    <div class="main-content">
        <h2 class="fw-bold mb-4 text-dark">Patient Records</h2>
        <div class="card border-0 shadow-sm rounded-3 overflow-hidden">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Patient Name</th>
                        <th>Last Appointment</th>
                        <th>Status</th>
                        <th>Records</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $res = mysqli_query($conn, "SELECT patient_name, MAX(app_date) as last_date, status FROM appointments GROUP BY patient_name ORDER BY last_date DESC");
                    if (mysqli_num_rows($res) > 0) {
                        while ($row = mysqli_fetch_assoc($res)) {
                            echo "<tr>
                            <td class='fw-bold text-primary'><i class='fa fa-user-circle me-2'></i>{$row['patient_name']}</td>
                            <td>{$row['last_date']}</td>
                            <td><span class='badge bg-info text-white rounded-pill'>Active Patient</span></td>
                            <td><button class='btn btn-sm btn-light border'>View File</button></td>
                        </tr>";
                        }
                    } else {
                        echo "<tr><td colspan='4' class='text-center py-4'>No patients found in database.</td></tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>
</body>

</html>