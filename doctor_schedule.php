<?php
$page_title = 'Doctor Availability Roster';
require_once 'includes/header.php';
require_role(['admin', 'doctor', 'receptionist']);

$pdo = get_db_pdo();
$user_role = $_SESSION['role'];

// Determine which doctor schedule we are editing
$doctor_id = 0;
if ($user_role === 'doctor') {
    $doctor_id = get_doctor_id($pdo);
    if (!$doctor_id) {
        echo '<div class="alert alert-danger mt-3">Your user account is not linked to any doctor record. Please contact the administrator.</div>';
        require_once 'includes/footer.php';
        exit();
    }
} else { // Admin or receptionist
    $doctor_id = isset($_GET['doctor_id']) ? (int)$_GET['doctor_id'] : 0;
    if ($doctor_id === 0) {
        // Fetch first doctor as default
        $doctor_id = (int)$pdo->query("SELECT id FROM doctors LIMIT 1")->fetchColumn();
    }
}

// Fetch all doctors for admin selection dropdown
$doctors_list = [];
if (in_array($user_role, ['admin', 'receptionist'])) {
    $doctors_list = $pdo->query("SELECT id, name, specialization FROM doctors ORDER BY name ASC")->fetchAll();
}

// Handle Add / Edit Availability
if (isset($_POST['save_availability']) && in_array($user_role, ['admin', 'doctor'])) {
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        set_flash('Invalid security token.', 'danger');
    } else {
        $day = $_POST['day_of_week'] ?? '';
        $start = $_POST['start_time'] ?? '';
        $end = $_POST['end_time'] ?? '';
        
        $valid_days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];
        
        if (!in_array($day, $valid_days) || empty($start) || empty($end)) {
            set_flash('Please fill in all availability parameters.', 'danger');
        } elseif ($start >= $end) {
            set_flash('Start time must be before end time.', 'danger');
        } else {
            // Save or update
            $stmt = $pdo->prepare("
                INSERT INTO doctor_availability (doctor_id, day_of_week, start_time, end_time)
                VALUES (?, ?, ?, ?)
                ON DUPLICATE KEY UPDATE start_time = VALUES(start_time), end_time = VALUES(end_time)
            ");
            $stmt->execute([$doctor_id, $day, $start, $end]);
            
            // Audit log
            audit_log($pdo, 'SAVE_SCHEDULE', 'doctor_availability', $doctor_id, null, ['day' => $day, 'hours' => "$start - $end"]);
            
            set_flash('Availability schedule updated successfully!');
            header("Location: doctor_schedule.php?doctor_id=$doctor_id");
            exit();
        }
    }
}

// Handle Delete shift
if (isset($_GET['delete_shift']) && in_array($user_role, ['admin', 'doctor'])) {
    if (!verify_csrf_token($_GET['token'] ?? '')) {
        set_flash('Invalid security token.', 'danger');
    } else {
        $shift_id = (int)$_GET['delete_shift'];
        
        // Security check
        $stmt = $pdo->prepare("SELECT doctor_id FROM doctor_availability WHERE id = ?");
        $stmt->execute([$shift_id]);
        $owner_id = $stmt->fetchColumn();
        
        if ($user_role === 'doctor' && $owner_id != $doctor_id) {
            set_flash('Access denied.', 'danger');
        } else {
            $stmt = $pdo->prepare("DELETE FROM doctor_availability WHERE id = ?");
            $stmt->execute([$shift_id]);
            set_flash('Shift timing removed.');
            header("Location: doctor_schedule.php?doctor_id=$doctor_id");
            exit();
        }
    }
}

// Fetch current doctor details
$stmt = $pdo->prepare("SELECT * FROM doctors WHERE id = ?");
$stmt->execute([$doctor_id]);
$current_doctor = $stmt->fetch();

// Fetch schedules
$stmt = $pdo->prepare("
    SELECT * FROM doctor_availability
    WHERE doctor_id = ?
    ORDER BY FIELD(day_of_week, 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday')
");
$stmt->execute([$doctor_id]);
$schedules = $stmt->fetchAll();
?>

<div class="row mb-4 mt-3">
    <div class="col-12 d-flex justify-content-between align-items-center">
        <div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-1">
                    <li class="breadcrumb-item"><a href="index.php" style="color:var(--muted); text-decoration:none;">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="doctors.php" style="color:var(--muted); text-decoration:none;">Doctors</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Shift Schedules</li>
                </ol>
            </nav>
            <h4 class="fw-bold mb-0" style="color:var(--text); letter-spacing:-0.5px;">Physician Attending Schedules</h4>
        </div>
        <div>
            <a href="doctors.php" class="btn btn-light" style="border: 1.5px solid #e2e8f0; border-radius: 12px;">
                <i class="fas fa-arrow-left me-2"></i>Back to Directory
            </a>
        </div>
    </div>
</div>

<div class="row g-4">
    <!-- Selection Panel for Admin -->
    <?php if (in_array($user_role, ['admin', 'receptionist'])): ?>
    <div class="col-12">
        <div class="card border-0 shadow-sm p-4 bg-white" style="border-radius: 16px;">
            <form method="GET" class="row g-3 align-items-center">
                <div class="col-md-8">
                    <label class="form-label fw-bold small text-uppercase" style="letter-spacing: 0.5px;">Select Medical Provider</label>
                    <select name="doctor_id" class="form-select rounded-3 py-2" onchange="this.form.submit()">
                        <?php foreach ($doctors_list as $doc): ?>
                            <option value="<?= $doc['id'] ?>" <?= $doc['id'] == $doctor_id ? 'selected' : '' ?>>
                                <?= esc(format_doctor_name($doc['name'])) ?> (<?= esc($doc['specialization']) ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-4 d-flex align-items-end pt-md-4">
                    <button type="submit" class="btn btn-primary w-100 py-2"><i class="fas fa-search me-2"></i>View Schedule Roster</button>
                </div>
            </form>
        </div>
    </div>
    <?php endif; ?>

    <!-- Roster Overview -->
    <div class="col-md-7">
        <div class="card border-0 shadow-sm rounded-4 h-100">
            <div class="card-header bg-white border-0 pt-4 px-4 pb-0">
                <h5 class="fw-bold mb-0 text-dark">Shift Roster for <?= $current_doctor ? esc(format_doctor_name($current_doctor['name'])) : 'Physician' ?></h5>
                <p class="text-muted small mb-0">List of scheduled working hours by day of the week.</p>
            </div>
            <div class="card-body p-4">
                <div class="table-responsive">
                    <table class="table align-middle">
                        <thead class="bg-light">
                            <tr>
                                <th class="ps-3">Day of Week</th>
                                <th>Duty Shift Hours</th>
                                <?php if (in_array($user_role, ['admin', 'doctor'])): ?>
                                <th class="text-end pe-3">Actions</th>
                                <?php endif; ?>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($schedules)): ?>
                                <tr>
                                    <td colspan="3" class="text-center py-5 text-muted small">No scheduled working hours recorded for this physician.</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($schedules as $sched): ?>
                                    <tr>
                                        <td class="ps-3 fw-bold text-dark"><i class="far fa-calendar-alt text-primary me-2"></i><?= esc($sched['day_of_week']) ?></td>
                                        <td>
                                            <span class="badge bg-success-subtle text-success py-2 px-3" style="font-size:12px; font-weight: 600;">
                                                <i class="far fa-clock me-1"></i>
                                                <?= date('h:i A', strtotime($sched['start_time'])) ?> - <?= date('h:i A', strtotime($sched['end_time'])) ?>
                                            </span>
                                        </td>
                                        <?php if (in_array($user_role, ['admin', 'doctor'])): ?>
                                        <td class="text-end pe-3">
                                            <a href="doctor_schedule.php?doctor_id=<?= $doctor_id ?>&delete_shift=<?= $sched['id'] ?>&token=<?= generate_csrf_token() ?>" class="btn btn-sm btn-icon btn-light rounded-circle text-danger" title="Remove shift">
                                                <i class="fas fa-trash-alt"></i>
                                            </a>
                                        </td>
                                        <?php endif; ?>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Configure / Add Availability Panel -->
    <?php if (in_array($user_role, ['admin', 'doctor'])): ?>
    <div class="col-md-5">
        <div class="card border-0 shadow-sm rounded-4">
            <div class="card-header bg-white border-0 pt-4 px-4 pb-0">
                <h5 class="fw-bold mb-0 text-dark">Define Duty Shift</h5>
                <p class="text-muted small mb-0">Set or update shift timings. Adding a day that already exists will overwrite the hours.</p>
            </div>
            <div class="card-body p-4">
                <form method="POST">
                    <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">
                    
                    <div class="mb-3">
                        <label class="form-label fw-bold small">Day of Week</label>
                        <select name="day_of_week" class="form-select rounded-3 py-2" required>
                            <option value="" disabled selected>Choose a weekday...</option>
                            <option value="Monday">Monday</option>
                            <option value="Tuesday">Tuesday</option>
                            <option value="Wednesday">Wednesday</option>
                            <option value="Thursday">Thursday</option>
                            <option value="Friday">Friday</option>
                            <option value="Saturday">Saturday</option>
                            <option value="Sunday">Sunday</option>
                        </select>
                    </div>

                    <div class="row">
                        <div class="col-6 mb-4">
                            <label class="form-label fw-bold small">Start Time</label>
                            <input type="time" name="start_time" class="form-control rounded-3 py-2" required>
                        </div>
                        <div class="col-6 mb-4">
                            <label class="form-label fw-bold small">End Time</label>
                            <input type="time" name="end_time" class="form-control rounded-3 py-2" required>
                        </div>
                    </div>

                    <button type="submit" name="save_availability" class="btn btn-success w-100 py-3 fw-bold" style="border-radius:12px;">
                        Configure Duty Shift
                    </button>
                </form>
            </div>
        </div>
    </div>
    <?php endif; ?>
</div>

<?php require_once 'includes/footer.php'; ?>
