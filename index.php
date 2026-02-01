<?php
session_start();
if (!isset($_SESSION['admin_user'])) {
    header("Location: login.php");
    exit();
}
include('config.php');

if (isset($_GET['delete_id'])) {
    $id = $_GET['delete_id'];
    mysqli_query($conn, "DELETE FROM appointments WHERE id = $id");
    header("Location: index.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CarePulse | Dashboard</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Inter', 'Segoe UI', Roboto, sans-serif;
        }

        body {
            display: flex;
            background: #f4f7fe;
            color: #2b3674;
        }

        /* Sidebar - Enhanced */
        .sidebar {
            width: 280px;
            height: 100vh;
            background: #111c44;
            color: #fff;
            position: fixed;
            padding: 30px 25px;
            z-index: 1000;
            box-shadow: 6px 0px 25px rgba(0, 0, 0, 0.15);
            border-right: 1px solid rgba(255, 255, 255, 0.08);
        }

        .sidebar-brand {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 40px;
            padding-bottom: 20px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }

        .sidebar-brand i {
            font-size: 32px;
            color: #70adf1;
            background: rgba(255, 255, 255, 0.05);
            padding: 10px;
            border-radius: 12px;
        }

        .sidebar h2 {
            font-size: 24px;
            font-weight: 800;
            letter-spacing: -0.5px;
            color: #fff;
        }

        .sidebar a {
            display: flex;
            align-items: center;
            color: #a3aed0;
            padding: 16px 20px;
            text-decoration: none;
            border-radius: 12px;
            margin-bottom: 8px;
            transition: all 0.3s ease;
            font-weight: 500;
            position: relative;
            overflow: hidden;
        }

        .sidebar a i {
            width: 30px;
            font-size: 20px;
            transition: transform 0.3s ease;
        }

        .sidebar a:hover {
            background: rgba(255, 255, 255, 0.1);
            color: #fff;
            transform: translateX(5px);
        }

        .sidebar a:hover i {
            transform: scale(1.1);
        }

        .sidebar a.active {
            background: #4318FF;
            color: #fff;
            box-shadow: 0px 8px 20px rgba(67, 24, 255, 0.25);
        }

        .sidebar a.active::after {
            content: '';
            position: absolute;
            right: 15px;
            width: 6px;
            height: 6px;
            background: #fff;
            border-radius: 50%;
        }

        /* Main Content */
        .main-content {
            margin-left: 280px;
            width: calc(100% - 280px);
            padding: 40px;
        }

        .header-flex {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
        }

        /* Stats Cards - Optimized Size */
        .card-container {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 20px;
            margin-bottom: 40px;
        }

        .stat-card {
            background: #fff;
            padding: 25px;
            border-radius: 20px;
            display: flex;
            align-items: center;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.03);
            transition: all 0.3s ease;
            border: 1px solid rgba(234, 236, 247, 0.8);
        }

        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.08);
        }

        .icon-box {
            width: 52px;
            height: 52px;
            border-radius: 14px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 18px;
            font-size: 22px;
            color: #fff;
            flex-shrink: 0;
            box-shadow: 0 6px 12px rgba(0, 0, 0, 0.1);
        }

        .bg-blue {
            background: linear-gradient(135deg, #4318ff 0%, #5e3aff 100%);
        }

        .bg-green {
            background: linear-gradient(135deg, #05cd99 0%, #11deab 100%);
        }

        .bg-yellow {
            background: linear-gradient(135deg, #ffb547 0%, #ffc107 100%);
        }

        .stat-info p {
            color: #a3aed0;
            font-size: 14px;
            font-weight: 600;
            margin-bottom: 6px;
            letter-spacing: 0.3px;
        }

        .stat-info h3 {
            font-size: 28px;
            color: #2b3674;
            font-weight: 700;
            margin-bottom: 4px;
        }

        .stat-info .sub-text {
            font-size: 12px;
            font-weight: 500;
            color: #8f9bba;
        }

        /* Table Design - Enhanced */
        .table-wrapper {
            background: #fff;
            padding: 25px;
            border-radius: 20px;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.03);
            border: 1px solid rgba(234, 236, 247, 0.8);
        }

        .table-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 2px solid #f1f4f9;
        }

        .table-header h3 {
            font-size: 18px;
            color: #2b3674;
            font-weight: 700;
        }

        .table-search {
            padding: 10px 15px;
            border: 1px solid #e0e5f2;
            border-radius: 10px;
            font-size: 14px;
            width: 250px;
            transition: all 0.3s ease;
        }

        .table-search:focus {
            border-color: #4318FF;
            box-shadow: 0 0 0 3px rgba(67, 24, 255, 0.1);
            outline: none;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th {
            text-align: left;
            padding: 15px;
            color: #a3aed0;
            font-size: 12px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            border-bottom: 2px solid #f1f4f9;
            background: #f8faff;
        }

        td {
            padding: 16px 15px;
            color: #2b3674;
            font-size: 14px;
            font-weight: 500;
            border-bottom: 1px solid #f1f4f9;
            transition: background 0.2s ease;
        }

        tr:hover td {
            background-color: #f8faff;
        }

        tr:last-child td {
            border-bottom: none;
        }

        /* Enhanced Status Pills */
        .status {
            padding: 6px 15px;
            border-radius: 10px;
            font-size: 12px;
            font-weight: 700;
            display: inline-block;
            text-transform: uppercase;
            letter-spacing: 0.3px;
        }

        .status.confirmed {
            background: rgba(5, 205, 153, 0.1);
            color: #05cd99;
            border: 1px solid rgba(5, 205, 153, 0.2);
        }

        .status.pending {
            background: rgba(255, 181, 71, 0.1);
            color: #ffb547;
            border: 1px solid rgba(255, 181, 71, 0.2);
        }

        .status.cancelled {
            background: rgba(238, 93, 80, 0.1);
            color: #ee5d50;
            border: 1px solid rgba(238, 93, 80, 0.2);
        }

        /* Action Buttons */
        .action-buttons {
            display: flex;
            gap: 10px;
        }

        .btn-delete {
            height: 36px;
            width: 36px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 8px;
            background: rgba(238, 93, 80, 0.1);
            color: #ee5d50;
            text-decoration: none;
            transition: all 0.3s ease;
        }

        .btn-delete:hover {
            background: #ee5d50;
            color: #fff;
            transform: scale(1.05);
        }

        .btn-edit {
            height: 36px;
            width: 36px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 8px;
            background: rgba(67, 24, 255, 0.1);
            color: #4318FF;
            text-decoration: none;
            transition: all 0.3s ease;
        }

        .btn-edit:hover {
            background: #4318FF;
            color: #fff;
            transform: scale(1.05);
        }

        /* Pagination */
        .pagination {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: 20px;
            padding-top: 20px;
            border-top: 1px solid #f1f4f9;
        }

        .pagination-info {
            color: #a3aed0;
            font-size: 13px;
            font-weight: 500;
        }

        .pagination-buttons {
            display: flex;
            gap: 8px;
        }

        .pagination-btn {
            padding: 8px 15px;
            border: 1px solid #e0e5f2;
            border-radius: 8px;
            background: #fff;
            color: #2b3674;
            font-size: 13px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .pagination-btn:hover {
            border-color: #4318FF;
            color: #4318FF;
        }

        .pagination-btn.active {
            background: #4318FF;
            color: #fff;
            border-color: #4318FF;
        }

        /* Scrollbar Styling */
        ::-webkit-scrollbar {
            width: 6px;
            height: 6px;
        }

        ::-webkit-scrollbar-track {
            background: #f1f1f1;
            border-radius: 10px;
        }

        ::-webkit-scrollbar-thumb {
            background: #c1c1c1;
            border-radius: 10px;
        }

        ::-webkit-scrollbar-thumb:hover {
            background: #a3aed0;
        }

        /* Responsive Design */
        @media (max-width: 1200px) {
            .card-container {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        @media (max-width: 768px) {
            .sidebar {
                width: 240px;
            }

            .main-content {
                margin-left: 240px;
                width: calc(100% - 240px);
                padding: 20px;
            }

            .card-container {
                grid-template-columns: 1fr;
                gap: 15px;
            }

            .header-flex {
                flex-direction: column;
                align-items: flex-start;
                gap: 15px;
            }

            .table-search {
                width: 100%;
            }
        }
    </style>
</head>

<body>

    <div class="sidebar">
        <div class="sidebar-brand">
            <i class="fa-solid fa-heart-pulse"></i>
            <h2>CarePulse</h2>
        </div>

        <a href="index.php" class="active"><i class="fa-solid fa-house"></i> Dashboard</a>
        <a href="add_appointment.php"><i class="fa-solid fa-calendar-plus"></i> New Appt</a>
        <a href="patients.php"><i class="fa-solid fa-hospital-user"></i> Patients</a>
        <a href="doctors.php"><i class="fa-solid fa-user-doctor"></i> Doctors</a>

        <div style="margin-top: auto; padding-top: 50px;">
            <a href="logout.php" style="color: #ee5d50; background: rgba(238, 93, 80, 0.05);">
                <i class="fa-solid fa-right-from-bracket"></i> Logout
            </a>
        </div>
    </div>

    <div class="main-content">
        <div class="header-flex">
            <h1 style="font-weight: 700; font-size: 34px;">Well Come Doctor</h1>
        </div>

        <div class="card-container">
            <div class="stat-card">
                <div class="icon-box bg-blue"><i class="fa-solid fa-calendar-check"></i></div>
                <div class="stat-info">
                    <p>Total Appointments</p>
                    <h3><?php
                        $res = mysqli_query($conn, "SELECT COUNT(*) as total FROM appointments");
                        echo mysqli_fetch_assoc($res)['total'];
                        ?></h3>
                </div>
            </div>

            <div class="stat-card">
                <div class="icon-box bg-green"><i class="fa-solid fa-user-md"></i></div>
                <div class="stat-info">
                    <p>Active Doctors</p>
                    <h3>4</h3>
                </div>
            </div>

            <div class="stat-card">
                <div class="icon-box bg-yellow"><i class="fa-solid fa-clock-rotate-left"></i></div>
                <div class="stat-info">
                    <p>Pending Requests</p>
                    <h3>05</h3>
                </div>
            </div>
        </div>

        <h3 style="margin-bottom: 20px; font-weight: 700; color: #2b3674;">Recent Appointments</h3>

        <div class="table-wrapper">
            <table>
                <thead>
                    <tr>
                        <th>Patient Name</th>
                        <th>Assigned Doctor</th>
                        <th>Appointment Date</th>
                        <th>Status</th>
                        <th style="text-align: center;">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $query = "SELECT * FROM appointments ORDER BY id DESC";
                    $result = mysqli_query($conn, $query);
                    while ($row = mysqli_fetch_assoc($result)) {
                        $status_class = strtolower($row['status']);
                        echo "<tr>
                            <td><strong style='color:#1b2559;'>{$row['patient_name']}</strong></td>
                            <td>Dr. {$row['doctor_name']}</td>
                            <td>" . date('M d, Y', strtotime($row['app_date'])) . "</td>
                            <td><span class='status $status_class'>{$row['status']}</span></td>
                            <td style='display:flex; justify-content:center;'>
                                <a href='index.php?delete_id={$row['id']}' class='btn-delete' onclick='return confirm(\"Are you sure you want to delete this record?\")'>
                                    <i class='fa-solid fa-trash-can'></i>
                                </a>
                            </td>
                        </tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>
</body>

</html>