<?php include('config.php'); ?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>CarePulse | Admin Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root {
            --primary: #2c3e50;
            --accent: #3498db;
        }

        body {
            background: #f8f9fa;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        .sidebar {
            width: 250px;
            height: 100vh;
            background: var(--primary);
            color: white;
            position: fixed;
        }

        .main-content {
            margin-left: 250px;
            padding: 30px;
        }

        .card-stats {
            border-left: 5px solid var(--accent);
            transition: 0.3s;
        }

        .card-stats:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
        }

        .nav-link {
            color: #bdc3c7;
            transition: 0.2s;
        }

        .nav-link:hover {
            color: white;
            background: rgba(255, 255, 255, 0.1);
        }
    </style>
</head>

<body>

    <div class="sidebar d-flex flex-column p-3">
        <h3><i class="fa-solid fa-heart-pulse text-info"></i> CarePulse</h3>
        <hr>
        <ul class="nav nav-pills flex-column mb-auto">
            <li class="nav-item"><a href="#" class="nav-link active"><i class="fa fa-home me-2"></i> Dashboard</a></li>
            <li><a href="#" class="nav-link"><i class="fa fa-calendar-check me-2"></i> Appointments</a></li>
            <li><a href="#" class="nav-link"><i class="fa fa-user-injured me-2"></i> Patients</a></li>
            <li><a href="#" class="nav-link"><i class="fa fa-user-md me-2"></i> Doctors</a></li>
        </ul>
        <hr>
        <a href="logout.php" class="btn btn-danger btn-sm w-100">Logout</a>
    </div>

    <div class="main-content">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>Hospital Overview</h2>
            <span class="badge bg-light text-dark p-2 border"><?php echo date('d M, Y'); ?></span>
        </div>

        <div class="row g-4 mb-5">
            <div class="col-md-4">
                <div class="card card-stats p-3 border-0 shadow-sm">
                    <div class="d-flex align-items-center">
                        <div class="bg-info text-white p-3 rounded-circle me-3"><i class="fa fa-clock fa-2x"></i></div>
                        <div>
                            <h6 class="text-muted mb-0">Total Appointments</h6>
                            <h3>48</h3>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card card-stats p-3 border-0 shadow-sm" style="border-left-color: #2ecc71;">
                    <div class="d-flex align-items-center">
                        <div class="bg-success text-white p-3 rounded-circle me-3"><i class="fa fa-check-circle fa-2x"></i></div>
                        <div>
                            <h6 class="text-muted mb-0">Completed</h6>
                            <h3>32</h3>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card card-stats p-3 border-0 shadow-sm" style="border-left-color: #e74c3c;">
                    <div class="d-flex align-items-center">
                        <div class="bg-danger text-white p-3 rounded-circle me-3"><i class="fa fa-user-md fa-2x"></i></div>
                        <div>
                            <h6 class="text-muted mb-0">Available Doctors</h6>
                            <h3>12</h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white py-3">
                <h5 class="mb-0 text-primary">Recent Appointments</h5>
            </div>
            <div class="card-body">
                <table class="table table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>Patient Name</th>
                            <th>Doctor</th>
                            <th>Date</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>Ali Khan</td>
                            <td>Dr. Sarah Ahmed</td>
                            <td>2026-02-01</td>
                            <td><span class="badge bg-warning">Pending</span></td>
                            <td><button class="btn btn-sm btn-outline-primary">Manage</button></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</body>

</html>