<?php
$page_title = 'Patient Admissions (IPD)';
require_once 'includes/header.php';
require_role(['admin', 'receptionist']);

$pdo = get_db_pdo();

if (isset($_GET['discharge'])) {
    if (!verify_csrf_token($_GET['token'] ?? '')) {
        set_flash('Invalid security token.', 'danger');
    } else {
        $admission_id = (int)$_GET['discharge'];

        $stmt = $pdo->prepare("
            SELECT ad.*, p.name as patient_name, b.bed_number, b.id as bed_id
            FROM admissions ad
            JOIN patients p ON ad.patient_id = p.id
            JOIN beds b ON ad.bed_id = b.id
            WHERE ad.id = ? AND ad.status = 'admitted'
        ");
        $stmt->execute([$admission_id]);
        $admission = $stmt->fetch();
        
        if ($admission) {
            $patient_id = $admission['patient_id'];
            $bed_id = $admission['bed_id'];
            $admit_date = $admission['admission_date'];
            $daily_charges = $admission['room_charges'];
            
            // Calculate total days (minimum 1 day)
            $today = date('Y-m-d');
            $diff = date_diff(date_create($admit_date), date_create($today));
            $days = $diff->format("%a");
            if ($days <= 0) $days = 1;
            
            $total_room_cost = $days * $daily_charges;

            $stmt = $pdo->prepare("UPDATE beds SET status = 'available' WHERE id = ?");
            $stmt->execute([$bed_id]);

            $stmt = $pdo->prepare("UPDATE admissions SET discharge_date = ?, status = 'discharged' WHERE id = ?");
            $stmt->execute([$today, $admission_id]);

            // Find latest appointment to link invoice, or create a stub if none exists
            $appt_stmt = $pdo->prepare("SELECT id FROM appointments WHERE patient_id = ? ORDER BY app_date DESC LIMIT 1");
            $appt_stmt->execute([$patient_id]);
            $appt_id = $appt_stmt->fetchColumn() ?: 0;
            
            if ($appt_id === 0) {
                $doc_id = $pdo->query("SELECT id FROM doctors LIMIT 1")->fetchColumn();
                $ins_appt = $pdo->prepare("INSERT INTO appointments (patient_id, doctor_id, app_date, app_time, status, notes) VALUES (?, ?, CURDATE(), '00:00:00', 'completed', 'Room Admission Discharge Placeholder')");
                $ins_appt->execute([$patient_id, $doc_id]);
                $appt_id = $pdo->lastInsertId();
            }

            $tax = $total_room_cost * 0.05;
            $net = $total_room_cost + $tax;
            
            $inv_stmt = $pdo->prepare("INSERT INTO invoices (appointment_id, patient_id, total_amount, tax, net_amount, status) VALUES (?, ?, ?, ?, ?, 'unpaid')");
            $inv_stmt->execute([$appt_id, $patient_id, $total_room_cost, $tax, $net]);
            $invoice_id = $pdo->lastInsertId();

            $item_stmt = $pdo->prepare("INSERT INTO invoice_items (invoice_id, item_description, quantity, unit_price, total_price) VALUES (?, ?, ?, ?, ?)");
            $desc = "Room Admission Charges: Bed " . $admission['bed_number'] . " ($days Days @ $" . number_format($daily_charges, 2) . "/day)";
            $item_stmt->execute([$invoice_id, $desc, 1, $total_room_cost, $total_room_cost]);
            
            // Log audit
            audit_log($pdo, 'DISCHARGE_PATIENT', 'admissions', $admission_id, ['status' => 'admitted'], ['status' => 'discharged', 'bill_amount' => $net]);
            
            set_flash("Patient discharged successfully! Billing invoice #INV-$invoice_id has been generated for room charges.");
        } else {
            set_flash("Admission record not found or patient already discharged.", "danger");
        }
    }
    header('Location: wards_beds.php');
    exit();
}

if (isset($_POST['admit_patient'])) {
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        set_flash('Invalid security token.', 'danger');
    } else {
        $patient_id = (int)$_POST['patient_id'];
        $bed_id = (int)$_POST['bed_id'];
        $doctor_id = (int)$_POST['doctor_id'];
        $charges = (float)($_POST['room_charges'] ?? 50.00);
        $duration = (int)($_POST['expected_duration_days'] ?? 5);
        $admit_date = $_POST['admission_date'] ?? date('Y-m-d');
        
        if ($patient_id <= 0 || $bed_id <= 0 || $doctor_id <= 0 || $charges <= 0 || $duration < 1 || $duration > 30) {
            set_flash('Please fill in all details correctly. Stay duration must be between 1 and 30 days.', 'danger');
        } else {
            $chk_stmt = $pdo->prepare("SELECT id FROM admissions WHERE patient_id = ? AND status = 'admitted'");
            $chk_stmt->execute([$patient_id]);
            if ($chk_stmt->fetch()) {
                set_flash('This patient is already admitted in the hospital.', 'danger');
            } else {
                // Check if the bed is available
                $bed_chk = $pdo->prepare("SELECT status FROM beds WHERE id = ?");
                $bed_chk->execute([$bed_id]);
                if ($bed_chk->fetchColumn() !== 'available') {
                    set_flash('The selected bed is already occupied or unavailable.', 'danger');
                } else {
                    // Check doctor weekday shift schedule
                    $day_of_week = date('l', strtotime($admit_date));
                    $avail_chk = $pdo->prepare("SELECT COUNT(*) FROM doctor_availability WHERE doctor_id = ? AND day_of_week = ?");
                    $avail_chk->execute([$doctor_id, $day_of_week]);
                    if ($avail_chk->fetchColumn() == 0) {
                        set_flash("The selected doctor is not scheduled to work on $day_of_week.", 'danger');
                    } else {
                        // Check doctor active inpatient load (max 3)
                        $load_chk = $pdo->prepare("SELECT COUNT(*) FROM admissions WHERE doctor_id = ? AND status = 'admitted'");
                        $load_chk->execute([$doctor_id]);
                        if ($load_chk->fetchColumn() >= 3) {
                            set_flash("Selected doctor is currently managing the maximum limit of active inpatients (Max 3).", 'danger');
                        } else {
                            $stmt = $pdo->prepare("INSERT INTO admissions (patient_id, bed_id, doctor_id, admission_date, room_charges, expected_duration_days, status) VALUES (?, ?, ?, ?, ?, ?, 'admitted')");
                            $stmt->execute([$patient_id, $bed_id, $doctor_id, $admit_date, $charges, $duration]);
                            $admission_id = $pdo->lastInsertId();

                            $stmt = $pdo->prepare("UPDATE beds SET status = 'occupied' WHERE id = ?");
                            $stmt->execute([$bed_id]);
                            
                            audit_log($pdo, 'ADMIT_PATIENT', 'admissions', $admission_id, null, ['patient_id' => $patient_id, 'bed_id' => $bed_id]);
                            
                            set_flash('Patient admitted successfully and bed status updated!');
                            header('Location: wards_beds.php');
                            exit();
                        }
                    }
                }
            }
        }
    }
}

$patients = $pdo->query("
    SELECT p.id, p.name, p.phone,
           (SELECT COUNT(*) FROM admissions a WHERE a.patient_id = p.id AND a.status = 'admitted') as is_admitted
    FROM patients p 
    ORDER BY p.name ASC
")->fetchAll();

$doctors = $pdo->query("
    SELECT d.id, d.name, d.specialization,
           (SELECT COUNT(*) FROM admissions a WHERE a.doctor_id = d.id AND a.status = 'admitted') as active_load
    FROM doctors d 
    WHERE d.status = 'available' 
    ORDER BY d.name ASC
")->fetchAll();

$beds = $pdo->query("
    SELECT b.*, w.name as ward_name, w.type as ward_type 
    FROM beds b 
    JOIN wards w ON b.ward_id = w.id 
    ORDER BY w.name ASC, b.bed_number ASC
")->fetchAll();

$preselected_bed_id = isset($_GET['bed_id']) ? (int)$_GET['bed_id'] : 0;
?>

<div class="row justify-content-center mt-3">
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm rounded-4 overflow-hidden mb-4">
            <div class="card-header bg-primary text-white p-4">
                <h4 class="mb-0 fw-bold">Inpatient Admission Registry (IPD)</h4>
                <p class="mb-0 opacity-75">Assign beds and configure billing for patient check-in.</p>
            </div>
            <div class="card-body p-4 p-md-5">
                <form method="POST">
                    <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">

                    <div class="row g-4">
                        <div class="col-md-12">
                            <label class="form-label fw-bold">Select Inpatient</label>
                            <select name="patient_id" class="form-select rounded-3 py-2" required>
                                <option value="" disabled selected>Choose a patient...</option>
                                <?php foreach ($patients as $p): ?>
                                    <option value="<?= $p['id'] ?>" <?= $p['is_admitted'] > 0 ? 'disabled' : '' ?>>
                                        <?= esc($p['name']) ?> (<?= esc($p['phone']) ?>) <?= $p['is_admitted'] > 0 ? '— [ALREADY ADMITTED]' : '' ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="col-md-12">
                            <label class="form-label fw-bold">Assign Room Bed</label>
                            <select name="bed_id" class="form-select rounded-3 py-2" required>
                                <option value="" disabled>Choose an available bed...</option>
                                <?php foreach ($beds as $b): ?>
                                    <option value="<?= $b['id'] ?>" <?= $b['status'] !== 'available' ? 'disabled' : '' ?> <?= $b['id'] == $preselected_bed_id ? 'selected' : '' ?>>
                                        <?= esc($b['ward_name']) ?> - Bed <?= esc($b['bed_number']) ?> (<?= esc($b['ward_type']) ?>) <?= $b['status'] !== 'available' ? '— [OCCUPIED]' : '' ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="col-md-12">
                            <label class="form-label fw-bold">Attending / Admitting Doctor</label>
                            <select name="doctor_id" class="form-select rounded-3 py-2" required>
                                <option value="" disabled selected>Assign a physician...</option>
                                <?php foreach ($doctors as $d): ?>
                                    <option value="<?= $d['id'] ?>" <?= $d['active_load'] >= 3 ? 'disabled' : '' ?>>
                                        <?= esc(format_doctor_name($d['name'])) ?> - <?= esc($d['specialization']) ?> <?= $d['active_load'] >= 3 ? '— [MAX ADMISSIONS REACHED]' : '' ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label fw-bold">Daily Room Charges ($)</label>
                            <input type="number" step="0.01" name="room_charges" class="form-control rounded-3 py-2" value="50.00" min="0" required>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label fw-bold">Admission Date</label>
                            <input type="date" name="admission_date" class="form-control rounded-3 py-2" value="<?= date('Y-m-d') ?>" required>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label fw-bold">Stay Duration (Days)</label>
                            <input type="number" name="expected_duration_days" class="form-control rounded-3 py-2" value="5" min="1" max="30" required>
                        </div>

                        <div class="col-12 pt-3">
                            <button type="submit" name="admit_patient" class="btn btn-primary btn-lg w-100 rounded-pill py-3 fw-bold">
                                Confirm Admission & Check-in
                            </button>
                            <a href="wards_beds.php" class="btn btn-link w-100 mt-2">Cancel and go back</a>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
