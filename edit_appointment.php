<?php
$page_title = 'Edit Appointment';
require_once 'includes/header.php';
require_role(['admin', 'receptionist']);

$pdo = get_db_pdo();
$id = $_GET['id'] ?? 0;

// Fetch current appointment data
$stmt = $pdo->prepare("SELECT * FROM appointments WHERE id = ?");
$stmt->execute([$id]);
$appt = $stmt->fetch();

if (!$appt) {
    set_flash('Appointment not found.', 'danger');
    header('Location: appointments.php');
    exit();
}

// Handle Form Submission
if (isset($_POST['update_appt'])) {
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        set_flash('Invalid security token.', 'danger');
    } else {
        $patient_id = (int)$_POST['patient_id'];
        $doctor_id = (int)$_POST['doctor_id'];
        $app_date = $_POST['app_date'];
        $app_time = $_POST['app_time'];
        $status = $_POST['status'];
        $notes = trim($_POST['notes']);

        // Validations
        if (!validate_date($app_date)) {
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
                    // Check for duplicate appointment (excluding self)
                    $stmt = $pdo->prepare("SELECT id FROM appointments WHERE doctor_id = ? AND app_date = ? AND app_time = ? AND id != ? AND status != 'cancelled'");
                    $stmt->execute([$doctor_id, $app_date, $app_time, $id]);
                    
                    if ($stmt->fetch()) {
                        set_flash('Selected slot is already reserved for this doctor by another patient.', 'warning');
                    } else {
                        // Check if status is updated to completed - auto generate invoice
                        if ($status === 'completed' && $appt['status'] !== 'completed') {
                            $chk_stmt = $pdo->prepare('SELECT id FROM invoices WHERE appointment_id = ?');
                            $chk_stmt->execute([$id]);
                            if (!$chk_stmt->fetchColumn()) {
                                // Default consultation fee: $50.00, tax 5%: $2.50, net: $52.50
                                $inv_stmt = $pdo->prepare('INSERT INTO invoices (appointment_id, patient_id, total_amount, tax, net_amount, status) VALUES (?, ?, 50.00, 2.50, 52.50, "unpaid")');
                                $inv_stmt->execute([$id, $patient_id]);
                                $invoice_id = $pdo->lastInsertId();

                                $item_stmt = $pdo->prepare('INSERT INTO invoice_items (invoice_id, item_description, quantity, unit_price, total_price) VALUES (?, "Doctor Consultation Fee", 1, 50.00, 50.00)');
                                $item_stmt->execute([$invoice_id]);
                            }
                        }

                        $stmt = $pdo->prepare("UPDATE appointments SET patient_id = ?, doctor_id = ?, app_date = ?, app_time = ?, status = ?, notes = ?, updated_by = ?, updated_at = NOW() WHERE id = ?");
                        if ($stmt->execute([$patient_id, $doctor_id, $app_date, $app_time, $status, $notes, $_SESSION['user_id'], $id])) {
                            
                            audit_log($pdo, 'UPDATE', 'appointments', $id, $appt, ['patient_id' => $patient_id, 'date' => $app_date, 'status' => $status]);
                            
                            set_flash('Appointment updated successfully!');
                            header("Location: appointment_details.php?id=$id");
                            exit();
                        }
                    }
                }
            }
        }
    }
}

// Fetch Data for selects
$doctors = $pdo->query("SELECT id, name, specialization FROM doctors ORDER BY name")->fetchAll();
$patients = $pdo->query("SELECT id, name, phone FROM patients ORDER BY name")->fetchAll();
?>

<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm rounded-4 overflow-hidden mb-4">
            <div class="card-header bg-primary text-white p-4">
                <h4 class="mb-0 fw-bold">Edit Appointment #<?= $id ?></h4>
                <p class="mb-0 opacity-75">Modify the appointment schedule or status below.</p>
            </div>
            <div class="card-body p-4 p-md-5">
                <form method="POST">
                    <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">

                    <div class="row g-4">
                        <div class="col-md-12">
                            <label class="form-label fw-bold">Select Patient</label>
                            <select name="patient_id" class="form-select rounded-3 py-2" required>
                                <?php foreach ($patients as $p): ?>
                                    <option value="<?= $p['id'] ?>" <?= $p['id'] == $appt['patient_id'] ? 'selected' : '' ?>>
                                        <?= esc($p['name']) ?> (<?= esc($p['phone']) ?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="col-md-12">
                            <label class="form-label fw-bold">Assigned Doctor</label>
                            <select name="doctor_id" class="form-select rounded-3 py-2" required>
                                <?php foreach ($doctors as $d): ?>
                                    <option value="<?= $d['id'] ?>" <?= $d['id'] == $appt['doctor_id'] ? 'selected' : '' ?>>
                                        <?= esc(format_doctor_name($d['name'])) ?> - <?= esc($d['specialization']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-bold">Appointment Date</label>
                            <input type="date" name="app_date" class="form-control rounded-3 py-2" value="<?= $appt['app_date'] ?>" required>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-bold">Appointment Time</label>
                            <input type="time" name="app_time" class="form-control rounded-3 py-2" value="<?= date('H:i', strtotime($appt['app_time'])) ?>" required>
                        </div>

                        <div class="col-md-12">
                            <label class="form-label fw-bold">Appointment Status</label>
                            <select name="status" class="form-select rounded-3 py-2" required>
                                <option value="pending" <?= $appt['status'] === 'pending' ? 'selected' : '' ?>>Pending Approval</option>
                                <option value="confirmed" <?= $appt['status'] === 'confirmed' ? 'selected' : '' ?>>Confirmed</option>
                                <option value="completed" <?= $appt['status'] === 'completed' ? 'selected' : '' ?>>Completed</option>
                                <option value="cancelled" <?= $appt['status'] === 'cancelled' ? 'selected' : '' ?>>Cancelled</option>
                            </select>
                        </div>

                        <div class="col-12">
                            <label class="form-label fw-bold">Consultation Notes (Optional)</label>
                            <textarea name="notes" class="form-control rounded-3" rows="3"><?= esc($appt['notes']) ?></textarea>
                        </div>

                        <div class="col-12 pt-3">
                            <button type="submit" name="update_appt" class="btn btn-primary btn-lg w-100 rounded-pill py-3 fw-bold">
                                Save Changes
                            </button>
                            <a href="appointment_details.php?id=<?= $id ?>" class="btn btn-link w-100 mt-2">Cancel and go back</a>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
