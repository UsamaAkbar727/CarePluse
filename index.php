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

if ($user_role === 'admin' || $user_role === 'receptionist') {
    $stats['total_appts'] = $pdo->query('SELECT COUNT(*) FROM appointments')->fetchColumn();
    $stats['pending_appts'] = $pdo->query("SELECT COUNT(*) FROM appointments WHERE status = 'pending'")->fetchColumn();
    $stats['total_patients'] = $pdo->query('SELECT COUNT(*) FROM patients')->fetchColumn();
    $stats['total_doctors'] = $pdo->query('SELECT COUNT(*) FROM doctors WHERE status = "available"')->fetchColumn();
    $stats['today_appts'] = $pdo->query('SELECT COUNT(*) FROM appointments WHERE DATE(app_date) = CURDATE()')->fetchColumn();
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
                    <h2 class="fw-bolder mb-0" style="color: #1e293b; font-size: 32px; letter-spacing: -1px;">
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
                        <h2 class="fw-bolder mb-0" style="color: #1e293b; font-size: 32px; letter-spacing: -1px;">
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

<?php require 'includes/footer.php'; ?>