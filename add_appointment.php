<?php
session_start();
if (!isset($_SESSION['admin_user'])) {
    header("Location: login.php");
    exit();
}
include('config.php');
if (isset($_POST['save_appt'])) {
    $p_name = mysqli_real_escape_string($conn, $_POST['patient_name']);
    $d_name = mysqli_real_escape_string($conn, $_POST['doctor_name']);
    $date = mysqli_real_escape_string($conn, $_POST['app_date']);
    $status = mysqli_real_escape_string($conn, $_POST['status']);

    $sql = "INSERT INTO appointments (patient_name, doctor_name, app_date, status) 
            VALUES ('$p_name', '$d_name', '$date', '$status')";

    if (mysqli_query($conn, $sql)) {
        header("Location: index.php?msg=success");
        exit();
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CarePulse | New Appointment</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <style>
   :root {
    --sidebar-bg: #111c44;
    --accent: #4318FF;
    --accent-light: #7551FF;
    --text-main: #2b3674;
    --text-muted: #a3aed0;
    --success: #05cd99;
    --warning: #ffb547;
    --danger: #ff5a5f;
    --card-shadow: 0 20px 27px rgba(0, 0, 0, 0.05);
    --hover-shadow: 0 8px 16px rgba(67, 24, 255, 0.1);
}

body {
    background: linear-gradient(135deg, #f4f7fe 0%, #f0f3ff 100%);
    font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
    color: var(--text-main);
    min-height: 100vh;
    overflow-x: hidden;
}

/* Sidebar Enhancement */
.sidebar {
    width: 280px;
    height: 100vh;
    background: linear-gradient(180deg, var(--sidebar-bg) 0%, #0d1533 100%);
    color: white;
    position: fixed;
    padding: 35px 25px;
    box-shadow: 8px 0px 30px rgba(0, 0, 0, 0.15);
    z-index: 1000;
    border-right: 1px solid rgba(255, 255, 255, 0.05);
    backdrop-filter: blur(10px);
}

.sidebar::before {
    content: '';
    position: absolute;
    top: 0;
    right: 0;
    width: 4px;
    height: 100%;
    background: linear-gradient(to bottom, var(--accent), #7551FF);
    opacity: 0.8;
}

.sidebar h3 {
    font-size: 26px;
    font-weight: 800;
    letter-spacing: -0.5px;
    margin-bottom: 50px;
    display: flex;
    align-items: center;
    gap: 14px;
    padding-bottom: 20px;
    border-bottom: 1px solid rgba(255, 255, 255, 0.1);
    position: relative;
}

.sidebar h3::after {
    content: '';
    position: absolute;
    bottom: -1px;
    left: 0;
    width: 60px;
    height: 3px;
    background: var(--accent);
    border-radius: 3px;
}

.sidebar h3 i {
    color: #70adf1;
    background: linear-gradient(135deg, #70adf1, #8671f7);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    font-size: 28px;
}

.nav-link {
    color: #b5bdd8;
    padding: 18px 22px;
    border-radius: 14px;
    margin-bottom: 8px;
    transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
    font-weight: 500;
    display: flex;
    align-items: center;
    position: relative;
    overflow: hidden;
    border: 1px solid transparent;
}

.nav-link::before {
    content: '';
    position: absolute;
    left: 0;
    top: 0;
    height: 100%;
    width: 4px;
    background: var(--accent);
    transform: translateX(-10px);
    transition: transform 0.3s ease;
    border-radius: 0 4px 4px 0;
}

.nav-link i {
    width: 32px;
    font-size: 20px;
    transition: all 0.3s ease;
}

.nav-link:hover {
    background: rgba(255, 255, 255, 0.08);
    color: #fff;
    transform: translateX(5px);
    border-color: rgba(255, 255, 255, 0.1);
}

.nav-link:hover::before {
    transform: translateX(0);
}

.nav-link.active {
    background: linear-gradient(135deg, var(--accent), var(--accent-light)) !important;
    color: white !important;
    box-shadow: var(--hover-shadow);
    border: none;
    transform: translateX(0);
}

.nav-link.active::before {
    transform: translateX(0);
    background: white;
    opacity: 0.3;
}

.nav-link.active i {
    transform: scale(1.1);
}

/* Main Content Enhancement */
.main-content {
    margin-left: 280px;
    padding: 60px;
    min-height: 100vh;
    animation: fadeIn 0.6s ease-out;
}

@keyframes fadeIn {
    from { opacity: 0; transform: translateY(20px); }
    to { opacity: 1; transform: translateY(0); }
}

.form-card {
    background: white;
    padding: 50px;
    border-radius: 24px;
    box-shadow: var(--card-shadow);
    border: none;
    position: relative;
    overflow: hidden;
    transition: transform 0.3s ease, box-shadow 0.3s ease;
}

.form-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 25px 50px rgba(0, 0, 0, 0.08);
}

.form-card::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 4px;
    background: linear-gradient(to right, var(--accent), var(--accent-light));
}

.form-label {
    font-weight: 600;
    color: var(--text-main);
    font-size: 14px;
    margin-bottom: 10px;
    display: flex;
    align-items: center;
    gap: 8px;
    letter-spacing: 0.2px;
}

.form-label::after {
    content: '*';
    color: var(--danger);
    font-size: 12px;
    margin-left: 2px;
    opacity: 0.8;
}

.form-control,
.form-select {
    border: 2px solid #e8edf9;
    border-radius: 14px;
    padding: 14px 18px;
    transition: all 0.3s ease;
    font-size: 15px;
    background: #fcfdff;
    box-shadow: inset 0 2px 4px rgba(0, 0, 0, 0.02);
}

.form-control:hover,
.form-select:hover {
    border-color: #d0d9f0;
    background: white;
}

.form-control:focus,
.form-select:focus {
    border-color: var(--accent);
    background: white;
    box-shadow: 0 0 0 4px rgba(67, 24, 255, 0.15), inset 0 2px 4px rgba(0, 0, 0, 0.02);
    transform: translateY(-1px);
}

/* Custom select arrow */
.form-select {
    background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='16' height='16' fill='%234318FF' viewBox='0 0 16 16'%3E%3Cpath d='M7.247 11.14L2.451 5.658C1.885 5.013 2.345 4 3.204 4h9.592a1 1 0 0 1 .753 1.659l-4.796 5.48a1 1 0 0 1-1.506 0z'/%3E%3C/svg%3E");
    background-repeat: no-repeat;
    background-position: right 18px center;
    background-size: 14px;
    padding-right: 45px;
    appearance: none;
    -webkit-appearance: none;
    -moz-appearance: none;
}

.btn-save {
    background: linear-gradient(135deg, var(--accent), var(--accent-light));
    border: none;
    border-radius: 14px;
    padding: 16px 32px;
    font-weight: 700;
    transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
    box-shadow: 0px 10px 25px rgba(67, 24, 255, 0.25);
    position: relative;
    overflow: hidden;
    font-size: 15px;
    letter-spacing: 0.3px;
}

.btn-save::before {
    content: '';
    position: absolute;
    top: 0;
    left: -100%;
    width: 100%;
    height: 100%;
    background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
    transition: left 0.6s;
}

.btn-save:hover {
    background: linear-gradient(135deg, #3311cc, #5a3dfd);
    transform: translateY(-3px) scale(1.02);
    box-shadow: 0px 15px 30px rgba(67, 24, 255, 0.35);
}

.btn-save:hover::before {
    left: 100%;
}

.btn-save:active {
    transform: translateY(-1px) scale(1.01);
}

.page-title {
    font-weight: 800;
    font-size: 34px;
    margin-bottom: 40px;
    background: linear-gradient(135deg, var(--text-main) 0%, #4318FF 100%);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    letter-spacing: -0.5px;
    position: relative;
    display: inline-block;
}

.page-title::after {
    content: '';
    position: absolute;
    bottom: -12px;
    left: 0;
    width: 60px;
    height: 4px;
    background: linear-gradient(to right, var(--accent), var(--accent-light));
    border-radius: 2px;
}

/* Responsive adjustments */
@media (max-width: 1200px) {
    .sidebar {
        width: 260px;
        padding: 30px 20px;
    }
    
    .main-content {
        margin-left: 260px;
        padding: 40px;
    }
}

@media (max-width: 768px) {
    .sidebar {
        transform: translateX(-100%);
        transition: transform 0.3s ease;
    }
    
    .sidebar.active {
        transform: translateX(0);
    }
    
    .main-content {
        margin-left: 0;
        padding: 30px;
    }
}

/* Scrollbar Styling */
::-webkit-scrollbar {
    width: 8px;
    height: 8px;
}

::-webkit-scrollbar-track {
    background: #f1f1f1;
    border-radius: 4px;
}

::-webkit-scrollbar-thumb {
    background: linear-gradient(to bottom, var(--accent), var(--accent-light));
    border-radius: 4px;
}

::-webkit-scrollbar-thumb:hover {
    background: linear-gradient(to bottom, #3311cc, #4a27ff);
}
    </style>
</head>

<body>

    <div class="sidebar d-flex flex-column">
        <h3><i class="fa-solid fa-heart-pulse"></i> CarePulse</h3>
        <div class="nav flex-column mb-auto">
            <a href="index.php" class="nav-link"><i class="fa fa-home"></i> Dashboard</a>
            <a href="add_appointment.php" class="nav-link active"><i class="fa fa-calendar-plus"></i> New Appt</a>
        </div>
        <hr style="border-color: rgba(255,255,255,0.1)">
        <a href="logout.php" class="btn btn-outline-danger w-100" style="border-radius: 10px;">
            <i class="fa fa-sign-out-alt me-2"></i> Logout
        </a>
    </div>

    <div class="main-content">
        <div class="container" style="max-width: 800px;">
            <h2 class="page-title">Book New Appointment</h2>

            <div class="form-card">
                <form method="POST">
                    <div class="mb-4">
                        <label class="form-label">Patient Full Name</label>
                        <input type="text" name="patient_name" class="form-control" placeholder="e.g. Usama jUtT " required>
                    </div>

                    <div class="row mb-4">
                        <div class="col-md-6">
                            <label class="form-label">Select Doctor</label>
                            <select name="doctor_name" class="form-select" required>
                                <option value="" selected disabled>Choose a physician...</option>
                                <option value="Sarah Ahmed">Dr. Sarah Ahmed (General)</option>
                                <option value="Ali Raza">Dr. Ali Raza (Specialist)</option>
                                <option value="Usman Jatt">Dr. Usman Jatt (Surgeon)</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Appointment Date</label>
                            <input type="date" name="app_date" class="form-control" required>
                        </div>
                    </div>

                    <div class="mb-4">
                        <label class="form-label">Initial Status</label>
                        <select name="status" class="form-select">
                            <option value="Pending">Pending Review</option>
                            <option value="Confirmed">Confirmed</option>
                        </select>
                    </div>

                    <div class="pt-2">
                        <button type="submit" name="save_appt" class="btn btn-primary btn-save w-100">
                            Confirm Appointment Details
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

</body>

</html>