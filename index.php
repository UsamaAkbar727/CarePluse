<?php
$page_title = 'Dashboard';
require 'includes/header.php';

$pdo = get_db_pdo();
$user_role = $_SESSION['role'];
$user_id = $_SESSION['user_id'];

// Role-based stats
$stats = [
    'total_appts' => 0,
    'pending_appts' => 0,
    'total_patients' => 0,
    'total_doctors' => 0,
    'today_appts' => 0
];

$financials = [
    'revenue' => 0.00,
    'pending' => 0.00
];

$bed_stats = [
    'total' => 0,
    'occupied' => 0
];

if ($user_role === 'admin' || $user_role === 'receptionist') {
    $stats['total_appts'] = $pdo->query('SELECT COUNT(*) FROM appointments')->fetchColumn();
    $stats['pending_appts'] = $pdo->query("SELECT COUNT(*) FROM appointments WHERE status = 'pending'")->fetchColumn();
    $stats['total_patients'] = $pdo->query('SELECT COUNT(*) FROM patients')->fetchColumn();
    $stats['total_doctors'] = $pdo->query('SELECT COUNT(*) FROM doctors WHERE status = "available"')->fetchColumn();
    $stats['today_appts'] = $pdo->query('SELECT COUNT(*) FROM appointments WHERE DATE(app_date) = CURDATE()')->fetchColumn();
    
    $financials['revenue'] = $pdo->query("SELECT SUM(net_amount) FROM invoices WHERE status = 'paid'")->fetchColumn() ?: 0.00;
    $financials['pending'] = $pdo->query("SELECT SUM(net_amount) FROM invoices WHERE status = 'unpaid'")->fetchColumn() ?: 0.00;
    
    $bed_stats['total'] = $pdo->query("SELECT COUNT(*) FROM beds")->fetchColumn() ?: 0;
    $bed_stats['occupied'] = $pdo->query("SELECT COUNT(*) FROM beds WHERE status = 'occupied'")->fetchColumn() ?: 0;
} elseif ($user_role === 'doctor') {
    $doctor_id = get_doctor_id($pdo);

    $stmt = $pdo->prepare('SELECT COUNT(*) FROM appointments WHERE doctor_id = ?');
    $stmt->execute([$doctor_id]);
    $stats['total_appts'] = $stmt->fetchColumn();

    $stmt = $pdo->prepare('SELECT COUNT(*) FROM appointments WHERE doctor_id = ? AND status = "pending"');
    $stmt->execute([$doctor_id]);
    $stats['pending_appts'] = $stmt->fetchColumn();

    $stmt = $pdo->prepare('SELECT COUNT(*) FROM appointments WHERE doctor_id = ? AND DATE(app_date) = CURDATE()');
    $stmt->execute([$doctor_id]);
    $stats['today_appts'] = $stmt->fetchColumn();
}

// --- PREPARE DATA FOR CHARTS ---
$safe_doctor_id = isset($doctor_id) ? ($doctor_id ?: 0) : 0;

// Past 7 Days Trend
$trend_data = [];
for ($i = 6; $i >= 0; $i--) {
    $date = date('Y-m-d', strtotime("-$i days"));
    $label = date('D (M j)', strtotime("-$i days"));
    $trend_data[$date] = [
        'label' => $label,
        'count' => 0
    ];
}

$startDate = date('Y-m-d', strtotime('-6 days'));
$endDate = date('Y-m-d');

if ($user_role === 'admin' || $user_role === 'receptionist') {
    $trend_stmt = $pdo->prepare('SELECT DATE(app_date) as date, COUNT(*) as count FROM appointments WHERE app_date BETWEEN ? AND ? GROUP BY DATE(app_date)');
    $trend_stmt->execute([$startDate, $endDate]);
} else { // Doctor role
    $trend_stmt = $pdo->prepare('SELECT DATE(app_date) as date, COUNT(*) as count FROM appointments WHERE doctor_id = ? AND app_date BETWEEN ? AND ? GROUP BY DATE(app_date)');
    $trend_stmt->execute([$safe_doctor_id, $startDate, $endDate]);
}

foreach ($trend_stmt->fetchAll() as $row) {
    if (isset($trend_data[$row['date']])) {
        $trend_data[$row['date']]['count'] = (int)$row['count'];
    }
}

$chart_trend_labels = [];
$chart_trend_values = [];
foreach ($trend_data as $date => $info) {
    $chart_trend_labels[] = $info['label'];
    $chart_trend_values[] = $info['count'];
}

// Specialization Distribution / Status Breakdown
if ($user_role === 'admin' || $user_role === 'receptionist') {
    $spec_stmt = $pdo->query('SELECT d.specialization, COUNT(*) as count FROM appointments a JOIN doctors d ON a.doctor_id = d.id GROUP BY d.specialization');
    $spec_data = $spec_stmt->fetchAll();
} else { // Doctor role
    $spec_stmt = $pdo->prepare('SELECT status as specialization, COUNT(*) as count FROM appointments WHERE doctor_id = ? GROUP BY status');
    $spec_stmt->execute([$safe_doctor_id]);
    $spec_data = $spec_stmt->fetchAll();
}

$chart_spec_labels = [];
$chart_spec_values = [];
foreach ($spec_data as $row) {
    $chart_spec_labels[] = ucfirst($row['specialization']);
    $chart_spec_values[] = (int)$row['count'];
}
?>

<div class="row g-4 mb-4 mt-2">
    <div class="col-12 d-flex justify-content-between align-items-center">
        <div>
            <h4 class="fw-bold mb-1" style="color:var(--text); letter-spacing:-0.5px;">Welcome back,
                <?= esc($_SESSION['full_name'] ?? $_SESSION['username']) ?>!</h4>
            <p class="text-muted mb-0" style="font-size: 14px;">Here's what's happening today at CarePulse.</p>
        </div>
        <?php if (in_array($user_role, ['admin', 'receptionist'])): ?>
            <a href="appointments.php" class="btn btn-primary px-4 py-2"
                style="border-radius: 12px; font-size: 14px; box-shadow: 0 4px 12px var(--accent-glow);">
                <i class="fas fa-plus me-2"></i>New Appointment
            </a>
        <?php endif; ?>
    </div>
</div>

<?php if ($user_role === 'doctor' && !$doctor_id): ?>
    <div class="alert alert-warning border-0 shadow-sm mb-4 p-4" style="border-radius: 20px;">
        <div class="d-flex align-items-center gap-3">
            <div style="width: 48px; height: 48px; background: rgba(245, 158, 11, 0.1); border-radius: 12px; display: flex; align-items: center; justify-content: center; color: #d97706;">
                <i class="fas fa-user-md-slash fs-4"></i>
            </div>
            <div>
                <h6 class="fw-bold mb-1" style="color: #92400e;">Medical Profile Not Found</h6>
                <p class="mb-0 text-muted small">Your account is registered as a doctor, but it's not linked to a specific doctor profile. Please ensure your <strong>Full Name</strong> or <strong>Email</strong> matches exactly in the <i>Medical Directory</i>.</p>
            </div>
        </div>
    </div>
<?php endif; ?>

<div class="row g-4 mb-4">
    <!-- Stat 1 -->
    <div class="col-lg-3 col-md-6">
        <div class="card h-100 border-0"
            style="background: linear-gradient(145deg, #ffffff, #f8fafc); box-shadow: 0 4px 20px rgba(0,0,0,0.03); border-radius: 20px !important;">
            <div class="card-body p-4 d-flex align-items-center justify-content-between">
                <div>
                    <h6 class="text-muted fw-bold mb-2 text-uppercase" style="font-size: 11px; letter-spacing: 0.5px;">
                        <?= $user_role === 'doctor' ? 'My Appointments' : 'Total Appointments' ?></h6>
                    <h2 class="fw-bolder mb-0" style="font-size: 32px; letter-spacing: -1px;">
                        <?= $stats['total_appts'] ?></h2>
                </div>
                <div
                    style="width: 54px; height: 54px; background: rgba(79, 70, 229, 0.1); border-radius: 16px; display: flex; align-items: center; justify-content: center; color: var(--accent);">
                    <i class="fas fa-calendar-check fs-4"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Stat 2 -->
    <div class="col-lg-3 col-md-6">
        <div class="card h-100 border-0"
            style="background: linear-gradient(145deg, #ffffff, #f8fafc); box-shadow: 0 4px 20px rgba(0,0,0,0.03); border-radius: 20px !important;">
            <div class="card-body p-4 d-flex align-items-center justify-content-between">
                <div>
                    <h6 class="fw-bold mb-2 text-uppercase"
                        style="color: #d97706; font-size: 11px; letter-spacing: 0.5px;">Pending Approvals</h6>
                    <h2 class="fw-bolder mb-0" style="color: #d97706; font-size: 32px; letter-spacing: -1px;">
                        <?= $stats['pending_appts'] ?></h2>
                </div>
                <div
                    style="width: 54px; height: 54px; background: rgba(245, 158, 11, 0.1); border-radius: 16px; display: flex; align-items: center; justify-content: center; color: #d97706;">
                    <i class="fas fa-clock fs-4"></i>
                </div>
            </div>
        </div>
    </div>

    <?php if (in_array($user_role, ['admin', 'receptionist'])): ?>
        <!-- Stat 3 -->
        <div class="col-lg-3 col-md-6">
            <div class="card h-100 border-0"
                style="background: linear-gradient(145deg, #ffffff, #f8fafc); box-shadow: 0 4px 20px rgba(0,0,0,0.03); border-radius: 20px !important;">
                <div class="card-body p-4 d-flex align-items-center justify-content-between">
                    <div>
                        <h6 class="text-muted fw-bold mb-2 text-uppercase" style="font-size: 11px; letter-spacing: 0.5px;">
                            Total Patients</h6>
                        <h2 class="fw-bolder mb-0" style="font-size: 32px; letter-spacing: -1px;">
                            <?= $stats['total_patients'] ?></h2>
                    </div>
                    <div
                        style="width: 54px; height: 54px; background: rgba(6, 182, 212, 0.1); border-radius: 16px; display: flex; align-items: center; justify-content: center; color: #06b6d4;">
                        <i class="fas fa-user-injured fs-4"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Stat 4 -->
        <div class="col-lg-3 col-md-6">
            <div class="card h-100 border-0"
                style="background: linear-gradient(145deg, #ffffff, #f8fafc); box-shadow: 0 4px 20px rgba(0,0,0,0.03); border-radius: 20px !important;">
                <div class="card-body p-4 d-flex align-items-center justify-content-between">
                    <div>
                        <h6 class="fw-bold mb-2 text-uppercase"
                            style="color: #059669; font-size: 11px; letter-spacing: 0.5px;">Available Doctors</h6>
                        <h2 class="fw-bolder mb-0" style="color: #059669; font-size: 32px; letter-spacing: -1px;">
                            <?= $stats['total_doctors'] ?></h2>
                    </div>
                    <div
                        style="width: 54px; height: 54px; background: rgba(16, 185, 129, 0.1); border-radius: 16px; display: flex; align-items: center; justify-content: center; color: #10b981;">
                        <i class="fas fa-user-md fs-4"></i>
                    </div>
                </div>
            </div>
        </div>
    <?php else: ?>
        <!-- Doctor specific Stat 3 -->
        <div class="col-lg-3 col-md-6">
            <div class="card h-100 border-0"
                style="background: linear-gradient(145deg, #ffffff, #f8fafc); box-shadow: 0 4px 20px rgba(0,0,0,0.03); border-radius: 20px !important;">
                <div class="card-body p-4 d-flex align-items-center justify-content-between">
                    <div>
                        <h6 class="fw-bold mb-2 text-uppercase"
                            style="color: #0284c7; font-size: 11px; letter-spacing: 0.5px;">Scheduled Today</h6>
                        <h2 class="fw-bolder mb-0" style="color: #0284c7; font-size: 32px; letter-spacing: -1px;">
                            <?= $stats['today_appts'] ?></h2>
                    </div>
                    <div
                        style="width: 54px; height: 54px; background: rgba(14, 165, 233, 0.1); border-radius: 16px; display: flex; align-items: center; justify-content: center; color: #0ea5e9;">
                        <i class="fas fa-calendar-day fs-4"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6"></div>
    <?php endif; ?>
</div>

<?php if (in_array($user_role, ['admin', 'receptionist'])): ?>
<!-- Financial & IPD Occupancy Dashboard Cards -->
<div class="row g-4 mb-4">
    <div class="col-md-4">
        <div class="card h-100 border-0"
            style="background: linear-gradient(145deg, #ffffff, #f8fafc); box-shadow: 0 4px 20px rgba(0,0,0,0.03); border-radius: 20px !important;">
            <div class="card-body p-4 d-flex align-items-center justify-content-between">
                <div>
                    <h6 class="text-muted fw-bold mb-2 text-uppercase" style="font-size: 11px; letter-spacing: 0.5px;">Revenue Collected (Paid Bills)</h6>
                    <h2 class="fw-bolder mb-0 text-success" style="font-size: 32px; letter-spacing: -1px;">$<?= number_format($financials['revenue'], 2) ?></h2>
                </div>
                <div style="width: 54px; height: 54px; background: rgba(16, 185, 129, 0.1); border-radius: 16px; display: flex; align-items: center; justify-content: center; color: #10b981;">
                    <i class="fas fa-hand-holding-usd fs-4"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card h-100 border-0"
            style="background: linear-gradient(145deg, #ffffff, #f8fafc); box-shadow: 0 4px 20px rgba(0,0,0,0.03); border-radius: 20px !important;">
            <div class="card-body p-4 d-flex align-items-center justify-content-between">
                <div>
                    <h6 class="fw-bold mb-2 text-uppercase" style="color: #ef4444; font-size: 11px; letter-spacing: 0.5px;">Outstanding Receivables</h6>
                    <h2 class="fw-bolder mb-0" style="color: #ef4444; font-size: 32px; letter-spacing: -1px;">$<?= number_format($financials['pending'], 2) ?></h2>
                </div>
                <div style="width: 54px; height: 54px; background: rgba(239, 68, 68, 0.1); border-radius: 16px; display: flex; align-items: center; justify-content: center; color: #ef4444;">
                    <i class="fas fa-file-invoice-dollar fs-4"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card h-100 border-0"
            style="background: linear-gradient(145deg, #ffffff, #f8fafc); box-shadow: 0 4px 20px rgba(0,0,0,0.03); border-radius: 20px !important;">
            <div class="card-body p-4 d-flex align-items-center justify-content-between">
                <div>
                    <h6 class="text-muted fw-bold mb-2 text-uppercase" style="font-size: 11px; letter-spacing: 0.5px;">Inpatient Bed Occupancy</h6>
                    <h2 class="fw-bolder mb-0 text-primary" style="font-size: 32px; letter-spacing: -1px;"><?= $bed_stats['occupied'] ?> / <?= $bed_stats['total'] ?></h2>
                </div>
                <div style="width: 54px; height: 54px; background: rgba(79, 70, 229, 0.1); border-radius: 16px; display: flex; align-items: center; justify-content: center; color: var(--accent);">
                    <i class="fas fa-procedures fs-4"></i>
                </div>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- Charts Section -->
<div class="row g-4 mb-4">
    <!-- Appointments Trend Chart -->
    <div class="col-lg-8 col-12">
        <div class="card border-0 shadow-sm h-100" style="border-radius: 20px !important;">
            <div class="card-header bg-white border-0 p-4 pb-0 rounded-top-4">
                <h5 class="fw-bold mb-1 text-dark" style="letter-spacing: -0.3px;">
                    <?= $user_role === 'doctor' ? 'My Appointment Activity' : 'Weekly Appointment Volume' ?>
                </h5>
                <p class="text-muted small mb-0">Total volume over the last 7 days</p>
            </div>
            <div class="card-body p-4" style="position: relative; min-height: 320px;">
                <?php if (array_sum($chart_trend_values) === 0): ?>
                    <div class="h-100 d-flex flex-column align-items-center justify-content-center py-4">
                        <div style="background: #f8fafc; width: 64px; height: 64px; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 12px auto;">
                            <i class="fas fa-chart-line" style="font-size: 24px; color: #cbd5e1;"></i>
                        </div>
                        <p class="text-muted small fw-medium mb-0">No appointment activity in the last 7 days</p>
                    </div>
                <?php else: ?>
                    <canvas id="trendChart"></canvas>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Category Distribution Chart -->
    <div class="col-lg-4 col-12">
        <div class="card border-0 shadow-sm h-100" style="border-radius: 20px !important;">
            <div class="card-header bg-white border-0 p-4 pb-0 rounded-top-4">
                <h5 class="fw-bold mb-1 text-dark" style="letter-spacing: -0.3px;">
                    <?= $user_role === 'doctor' ? 'Status Breakdown' : 'Bookings by Speciality' ?>
                </h5>
                <p class="text-muted small mb-0">Overall ratio and distribution</p>
            </div>
            <div class="card-body p-4 d-flex align-items-center justify-content-center" style="position: relative; min-height: 320px;">
                <?php if (empty($chart_spec_values)): ?>
                    <div class="text-center py-4">
                        <div style="background: #f8fafc; width: 64px; height: 64px; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 12px auto;">
                            <i class="fas fa-chart-pie" style="font-size: 24px; color: #cbd5e1;"></i>
                        </div>
                        <p class="text-muted small fw-medium mb-0">No booking data available</p>
                    </div>
                <?php else: ?>
                    <canvas id="distributionChart"></canvas>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Recent Activity -->
<div class="card border-0 shadow-sm" style="border-radius: 20px !important;">
    <div class="card-header bg-white border-0 p-4 pb-3 d-flex justify-content-between align-items-center rounded-top-4">
        <h5 class="fw-bold mb-0 text-dark" style="letter-spacing: -0.3px;">Recent Activity</h5>
        <a href="appointments.php" class="btn btn-sm btn-light px-3 fw-bold"
            style="border-radius: 10px; color: var(--muted); background: #f1f5f9;">View All</a>
    </div>
    <div class="table-responsive px-2 pb-2">
        <table class="table table-hover align-middle mb-0">
            <thead class="border-0">
                <tr>
                    <th class="ps-4" style="background: transparent;">Patient</th>
                    <th style="background: transparent;">Doctor</th>
                    <th style="background: transparent;">Date & Time</th>
                    <th style="background: transparent;">Status</th>
                    <th class="text-end pe-4" style="background: transparent;">Action</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $query = 'SELECT a.*, p.name as p_name, d.name as d_name FROM appointments a LEFT JOIN patients p ON a.patient_id = p.id LEFT JOIN doctors d ON a.doctor_id = d.id';
                if ($user_role === 'doctor') {
                    $query .= ' WHERE a.doctor_id = ?';
                }
                $query .= ' ORDER BY a.created_at DESC LIMIT 5';

                $stmt = $pdo->prepare($query);
                if ($user_role === 'doctor') {
                    $stmt->execute([$doctor_id]);
                } else {
                    $stmt->execute();
                }

                $appts = $stmt->fetchAll();
                if (empty($appts)): ?>
                    <tr>
                        <td colspan="5" class="text-center py-5">
                            <div
                                style="background: #f8fafc; width: 80px; height: 80px; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 16px;">
                                <i class="fas fa-calendar-times" style="font-size: 32px; color: #cbd5e1;"></i>
                            </div>
                            <p class="text-muted fw-medium mb-0">No recent activity found.</p>
                        </td>
                    </tr>
                <?php else:
                    foreach ($appts as $appt): ?>
                        <tr>
                            <td class="ps-4">
                                <div class="d-flex align-items-center gap-3">
                                    <div
                                        style="width: 40px; height: 40px; background: #f1f5f9; border-radius: 10px; display: flex; align-items: center; justify-content: center; color: var(--muted); font-weight: bold; font-size: 14px;">
                                        <?= strtoupper(substr($appt['p_name'] ?? 'U', 0, 1)) ?>
                                    </div>
                                    <div>
                                        <div class="fw-bold" style="color: #1e293b; font-size: 14px;">
                                            <?= esc($appt['p_name'] ?? 'Unknown Patient') ?></div>
                                        <div style="font-size: 12px; color: var(--muted);">#PAT-<?= $appt['patient_id'] ?></div>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div class="fw-medium text-dark" style="font-size: 14px;"><i
                                        class="fas fa-user-md me-2 text-muted"
                                        style="font-size: 12px;"></i><?= esc($appt['d_name'] ?? 'Unassigned') ?></div>
                            </td>
                            <td>
                                <div class="fw-medium text-dark" style="font-size: 14px;">
                                    <?= date('M j, Y', strtotime($appt['app_date'])) ?></div>
                                <div style="font-size: 12px; color: var(--muted);"><i
                                        class="far fa-clock me-1"></i><?= date('h:i A', strtotime($appt['app_time'])) ?></div>
                            </td>
                            <td>
                                <?php
                                $badge_class = match ($appt['status']) {
                                    'pending' => 'warning',
                                    'confirmed' => 'success',
                                    'completed' => 'info',
                                    'cancelled' => 'danger',
                                    default => 'secondary'
                                };
                                ?>
                                <span class="badge bg-<?= $badge_class ?>"
                                    style="background: rgba(var(--bs-<?= $badge_class ?>-rgb), 0.1) !important; color: var(--bs-<?= $badge_class ?>) !important; font-size: 12px; padding: 6px 12px; border-radius: 8px;">
                                    <?= ucfirst($appt['status']) ?>
                                </span>
                            </td>
                            <td class="text-end pe-4">
                                <a href="appointment_details.php?id=<?= $appt['id'] ?>" class="btn btn-sm btn-light"
                                    style="border-radius: 8px; width: 32px; height: 32px; padding: 0; display: inline-flex; align-items: center; justify-content: center; color: var(--muted); transition: all 0.2s;">
                                    <i class="fas fa-arrow-right"></i>
                                </a>
                            </td>
                        </tr>
                    <?php endforeach;
                endif; ?>
            </tbody>
        </table>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    document.addEventListener("DOMContentLoaded", function() {
        <?php if (array_sum($chart_trend_values) > 0): ?>
        // Trend Chart (Line Chart)
        const trendCtx = document.getElementById('trendChart').getContext('2d');

        const primaryGradient = trendCtx.createLinearGradient(0, 0, 0, 300);
        primaryGradient.addColorStop(0, 'rgba(79, 70, 229, 0.3)');
        primaryGradient.addColorStop(1, 'rgba(79, 70, 229, 0)');

        new Chart(trendCtx, {
            type: 'line',
            data: {
                labels: <?= json_encode($chart_trend_labels) ?>,
                datasets: [{
                    label: 'Appointments',
                    data: <?= json_encode($chart_trend_values) ?>,
                    borderColor: '#4f46e5',
                    borderWidth: 3,
                    backgroundColor: primaryGradient,
                    fill: true,
                    tension: 0.35,
                    pointBackgroundColor: '#4f46e5',
                    pointBorderColor: '#ffffff',
                    pointBorderWidth: 2,
                    pointRadius: 5,
                    pointHoverRadius: 7
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        padding: 12,
                        cornerRadius: 10,
                        backgroundColor: '#0f172a',
                        titleFont: { family: 'Inter', weight: 'bold' },
                        bodyFont: { family: 'Inter' }
                    }
                },
                scales: {
                    y: {
                        grid: { color: '#f1f5f9' },
                        ticks: {
                            stepSize: 1,
                            font: { family: 'Inter', size: 11 },
                            color: '#64748b'
                        },
                        border: { dash: [5, 5] }
                    },
                    x: {
                        grid: { display: false },
                        ticks: {
                            font: { family: 'Inter', size: 11 },
                            color: '#64748b'
                        }
                    }
                }
            }
        });
        <?php endif; ?>

        <?php if (!empty($chart_spec_values)): ?>
        // Distribution Chart (Doughnut Chart)
        const distCtx = document.getElementById('distributionChart').getContext('2d');
        
        const colors = [
            '#4f46e5', // Primary Accent
            '#10b981', // Success
            '#f59e0b', // Warning
            '#06b6d4', // Info
            '#ec4899', // Pink
            '#8b5cf6', // Purple
            '#ef4444'  // Danger
        ];

        new Chart(distCtx, {
            type: 'doughnut',
            data: {
                labels: <?= json_encode($chart_spec_labels) ?>,
                datasets: [{
                    data: <?= json_encode($chart_spec_values) ?>,
                    backgroundColor: colors,
                    borderWidth: 2,
                    borderColor: '#ffffff'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '70%',
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            font: { family: 'Inter', size: 11 },
                            boxWidth: 10,
                            padding: 15,
                            color: '#475569'
                        }
                    },
                    tooltip: {
                        padding: 12,
                        cornerRadius: 10,
                        backgroundColor: '#0f172a',
                        titleFont: { family: 'Inter', weight: 'bold' },
                        bodyFont: { family: 'Inter' }
                    }
                }
            }
        });
        <?php endif; ?>
    });
</script>

<?php require 'includes/footer.php'; ?>