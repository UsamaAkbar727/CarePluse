<?php
$page_title = 'Book Appointment';
require_once 'includes/header.php';
require_role(['admin', 'receptionist']);

$pdo = get_db_pdo();

// Handle Form Submission
if (isset($_POST['book_appt'])) {
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        set_flash('Invalid security token.', 'danger');
    } else {
        $patient_id = (int)$_POST['patient_id'];
        $doctor_id = (int)$_POST['doctor_id'];
        $app_date = $_POST['app_date'];
        $app_time = $_POST['app_time'];
        $notes = trim($_POST['notes']);

        // Validations
        if ($app_date < date('Y-m-d')) {
            set_flash('Cannot book appointments in the past.', 'danger');
        } elseif (!validate_date($app_date)) {
            set_flash('Invalid date. Please choose a date within 2024-2030.', 'danger');
        } else {
            // Check Doctor Availability Shift Roster
            $weekday = date('l', strtotime($app_date));
            $sched_stmt = $pdo->prepare("SELECT start_time, end_time FROM doctor_availability WHERE doctor_id = ? AND day_of_week = ?");
            $sched_stmt->execute([$doctor_id, $weekday]);
            $sched = $sched_stmt->fetch();

            if (!$sched) {
                set_flash('Selected physician is not available on ' . $weekday . 's.', 'danger');
            } else {
                $formatted_time = date('H:i:s', strtotime($app_time));
                if ($formatted_time < $sched['start_time'] || $formatted_time > $sched['end_time']) {
                    set_flash('Attending hours for this physician on ' . $weekday . 's are between ' . date('h:i A', strtotime($sched['start_time'])) . ' and ' . date('h:i A', strtotime($sched['end_time'])) . '.', 'danger');
                } else {
                    // Check for duplicate appointment (same doctor, same date, same time)
                    $stmt = $pdo->prepare("SELECT id FROM appointments WHERE doctor_id = ? AND app_date = ? AND app_time = ? AND status != 'cancelled'");
                    $stmt->execute([$doctor_id, $app_date, $app_time]);
                    
                    if ($stmt->fetch()) {
                        set_flash('Selected slot is already reserved for this doctor.', 'warning');
                    } else {
                        $stmt = $pdo->prepare("INSERT INTO appointments (patient_id, doctor_id, app_date, app_time, notes, created_by) VALUES (?, ?, ?, ?, ?, ?)");
                        if ($stmt->execute([$patient_id, $doctor_id, $app_date, $app_time, $notes, $_SESSION['user_id']])) {
                            $new_id = $pdo->lastInsertId();
                            audit_log($pdo, 'BOOK', 'appointments', $new_id, null, ['patient_id' => $patient_id, 'date' => $app_date]);
                            set_flash('Appointment booked successfully!');
                            header('Location: appointments.php');
                            exit();
                        }
                    }
                }
            }
        }
    }
}

// Fetch Data for selects
$doctors = $pdo->query("SELECT id, name, specialization FROM doctors WHERE status = 'available' ORDER BY name")->fetchAll();
$patients = $pdo->query("SELECT id, name, phone FROM patients ORDER BY name")->fetchAll();
?>

<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm rounded-4 overflow-hidden mb-4">
            <div class="card-header bg-primary text-white p-4">
                <h4 class="mb-0 fw-bold">Schedule New Appointment</h4>
                <p class="mb-0 opacity-75">Fill in the details below to book a consultation.</p>
            </div>
            <div class="card-body p-4 p-md-5">
                <form method="POST">
                    <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">

                    <div class="row g-4">
                        <div class="col-md-12">
                            <label class="form-label fw-bold">Select Patient</label>
                            <select name="patient_id" class="form-select rounded-3 py-2" required>
                                <option value="" disabled selected>Search and select patient...</option>
                                <?php foreach ($patients as $p): ?>
                                    <option value="<?= $p['id'] ?>"><?= esc($p['name']) ?> (<?= esc($p['phone']) ?>)</option>
                                <?php endforeach; ?>
                            </select>
                            <div class="form-text">Don't see the patient? <a href="patients.php">Register them first</a>.</div>
                        </div>

                        <div class="col-md-12">
                            <label class="form-label fw-bold">Assigned Doctor</label>
                            <select name="doctor_id" class="form-select rounded-3 py-2" required>
                                <option value="" disabled selected>Choose a physician...</option>
                                <?php foreach ($doctors as $d): ?>
                                    <option value="<?= $d['id'] ?>"><?= esc(format_doctor_name($d['name'])) ?> - <?= esc($d['specialization']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-bold">Preferred Date</label>
                            <input type="date" name="app_date" class="form-control rounded-3 py-2" min="<?= date('Y-m-d') ?>" required>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-bold">Preferred Time</label>
                            <input type="time" name="app_time" class="form-control rounded-3 py-2" required>
                        </div>

                        <div class="col-12">
                            <label class="form-label fw-bold">Consultation Notes (Optional)</label>
                            <textarea name="notes" class="form-control rounded-3" rows="3" placeholder="Symptoms, reason for visit..."></textarea>
                        </div>

                        <div class="col-12 pt-3">
                            <button type="submit" name="book_appt" class="btn btn-primary btn-lg w-100 rounded-pill py-3 fw-bold">
                                Confirm & Book Appointment
                            </button>
                            <a href="appointments.php" class="btn btn-link w-100 mt-2">Cancel and go back</a>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <div class="alert alert-info rounded-4 border-0 d-flex align-items-center">
            <i class="fas fa-info-circle fa-2x me-3"></i>
            <div>
                <strong>Quick Tip:</strong> Ensure the doctor is available at the selected time. System will notify you if there's a conflict.
            </div>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>