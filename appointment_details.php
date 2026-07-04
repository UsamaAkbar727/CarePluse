<?php
$page_title = 'Appointment Details';
require 'includes/header.php';

$pdo = get_db_pdo();
$id = $_GET['id'] ?? 0;

// Fetch appointment with patient and doctor details
$stmt = $pdo->prepare('
    SELECT 
        a.*, 
        p.name as p_name, p.email as p_email, p.phone as p_phone, p.gender as p_gender, p.date_of_birth,
        d.name as d_name, d.specialization as d_specialization, d.email as d_email,
        u1.full_name as creator_name,
        u2.full_name as updater_name
    FROM appointments a
    JOIN patients p ON a.patient_id = p.id
    JOIN doctors d ON a.doctor_id = d.id
    LEFT JOIN users u1 ON a.created_by = u1.id
    LEFT JOIN users u2 ON a.updated_by = u2.id
    WHERE a.id = ?
');
$stmt->execute([$id]);
$appt = $stmt->fetch();

if (!$appt) {
    echo '<div class="alert alert-danger">Appointment not found.</div>';
    require 'includes/footer.php';
    exit();
}

// Security: Doctors can only see their own appointments
if ($_SESSION['role'] === 'doctor') {
    $doctor_id = get_doctor_id($pdo);
    if ($appt['doctor_id'] != $doctor_id) {
        header('Location: 403.php');
        exit();
    }
}

// Auto-create prescriptions table if not exists
$pdo->exec("
    CREATE TABLE IF NOT EXISTS prescriptions (
        id INT AUTO_INCREMENT PRIMARY KEY,
        appointment_id INT UNIQUE NOT NULL,
        patient_id INT NOT NULL,
        doctor_id INT NOT NULL,
        symptoms TEXT,
        diagnosis TEXT,
        medications TEXT,
        instructions TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (appointment_id) REFERENCES appointments(id) ON DELETE CASCADE
    )
");

// Fetch prescription if exists
$pres_stmt = $pdo->prepare('SELECT * FROM prescriptions WHERE appointment_id = ?');
$pres_stmt->execute([$id]);
$prescription = $pres_stmt->fetch();

// Save/Update Prescription
if (isset($_POST['save_prescription']) && $_SESSION['role'] === 'doctor') {
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        $_SESSION['flash_message'] = ['text' => 'Invalid security token.', 'type' => 'danger'];
    } else {
        $symptoms = trim($_POST['symptoms'] ?? '');
        $diagnosis = trim($_POST['diagnosis'] ?? '');
        $medications = trim($_POST['medications'] ?? '');
        $instructions = trim($_POST['instructions'] ?? '');
        
        if ($prescription) {
            $stmt = $pdo->prepare('UPDATE prescriptions SET symptoms = ?, diagnosis = ?, medications = ?, instructions = ? WHERE appointment_id = ?');
            $stmt->execute([$symptoms, $diagnosis, $medications, $instructions, $id]);
            set_flash('Prescription updated successfully.');
        } else {
            $stmt = $pdo->prepare('INSERT INTO prescriptions (appointment_id, patient_id, doctor_id, symptoms, diagnosis, medications, instructions) VALUES (?, ?, ?, ?, ?, ?, ?)');
            $stmt->execute([$id, $appt['patient_id'], $appt['doctor_id'], $symptoms, $diagnosis, $medications, $instructions]);
            set_flash('Prescription created successfully.');
        }
        header("Location: appointment_details.php?id=$id");
        exit();
    }
}

$status_class = match ($appt['status']) {
    'pending' => 'warning',
    'confirmed' => 'success',
    'completed' => 'info',
    'cancelled' => 'danger',
    default => 'secondary'
};
?>

<div class="row mb-4">
    <div class="col-12 d-flex justify-content-between align-items-center">
        <div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-1">
                    <li class="breadcrumb-item"><a href="index.php" style="color:var(--muted); text-decoration:none;">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="appointments.php" style="color:var(--muted); text-decoration:none;">Appointments</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Details</li>
                </ol>
            </nav>
            <h4 class="fw-bold mb-0" style="color:var(--text); letter-spacing:-0.5px;">Appointment #<?= $appt['id'] ?></h4>
        </div>
        <div class="d-flex gap-2">
            <a href="appointments.php" class="btn btn-light" style="border: 1.5px solid #e2e8f0; border-radius: 12px;">
                <i class="fas fa-arrow-left me-2"></i>Back
            </a>
            <?php if (in_array($_SESSION['role'], ['admin', 'receptionist'])): ?>
                <a href="edit_appointment.php?id=<?= $appt['id'] ?>" class="btn btn-primary px-4" style="border-radius: 12px;">
                    <i class="fas fa-edit me-2"></i>Edit Details
                </a>
            <?php endif; ?>
        </div>
    </div>
</div>

<div class="row g-4">
    <!-- Main Info -->
    <div class="col-lg-8">
        <div class="card border-0 mb-4 h-100">
            <div class="card-body p-4">
                <div class="d-flex justify-content-between align-items-start mb-4">
                    <div>
                        <span class="badge bg-<?= $status_class ?> mb-2" style="background: rgba(var(--bs-<?= $status_class ?>-rgb), 0.1) !important; color: var(--bs-<?= $status_class ?>) !important; font-size: 13px; padding: 8px 16px; border-radius: 10px;">
                            <i class="fas fa-circle me-2" style="font-size: 8px;"></i><?= ucfirst($appt['status']) ?>
                        </span>
                        <h2 class="fw-bold mb-0" style="color: var(--text);"><?= date('F j, Y', strtotime($appt['app_date'])) ?></h2>
                        <p class="text-muted mb-0"><i class="far fa-clock me-2"></i><?= date('h:i A', strtotime($appt['app_time'])) ?> (Local Time)</p>
                    </div>
                    <div class="text-end">
                        <p class="text-muted small mb-1">Created At</p>
                        <p class="fw-medium mb-0 small"><?= date('M j, Y h:i A', strtotime($appt['created_at'])) ?></p>
                    </div>
                </div>

                <div class="row g-4 mt-2">
                    <div class="col-md-6">
                        <div class="p-3 bg-light rounded-4 border border-white h-100">
                            <h6 class="text-muted fw-bold small text-uppercase mb-3" style="letter-spacing: 0.5px;">Patient Information</h6>
                            <div class="d-flex align-items-center gap-3 mb-3">
                                <div style="width: 48px; height: 48px; background: #fff; border-radius: 14px; display: flex; align-items: center; justify-content: center; color: var(--accent); box-shadow: 0 4px 10px rgba(0,0,0,0.03);">
                                    <i class="fas fa-user-injured fs-5"></i>
                                </div>
                                <div>
                                    <div class="fw-bold" style="color: #1e293b;"><?= esc($appt['p_name']) ?></div>
                                    <div style="font-size: 12px; color: var(--muted);">Patient ID: #PAT-<?= $appt['patient_id'] ?></div>
                                </div>
                            </div>
                            <div class="space-y-2">
                                <div class="d-flex justify-content-between py-1">
                                    <span class="text-muted small">Gender</span>
                                    <span class="fw-medium small"><?= $appt['p_gender'] ?></span>
                                </div>
                                <div class="d-flex justify-content-between py-1">
                                    <span class="text-muted small">Phone</span>
                                    <span class="fw-medium small"><?= esc($appt['p_phone'] ?: 'N/A') ?></span>
                                </div>
                                <div class="d-flex justify-content-between py-1">
                                    <span class="text-muted small">Email</span>
                                    <span class="fw-medium small text-truncate ms-2"><?= esc($appt['p_email'] ?: 'N/A') ?></span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="p-3 bg-light rounded-4 border border-white h-100">
                            <h6 class="text-muted fw-bold small text-uppercase mb-3" style="letter-spacing: 0.5px;">Doctor Information</h6>
                            <div class="d-flex align-items-center gap-3 mb-3">
                                <div style="width: 48px; height: 48px; background: #fff; border-radius: 14px; display: flex; align-items: center; justify-content: center; color: #10b981; box-shadow: 0 4px 10px rgba(0,0,0,0.03);">
                                    <i class="fas fa-user-md fs-5"></i>
                                </div>
                                <div>
                                    <div class="fw-bold" style="color: #1e293b;"><?= esc($appt['d_name']) ?></div>
                                    <div style="font-size: 12px; color: var(--muted);"><?= esc($appt['d_specialization']) ?></div>
                                </div>
                            </div>
                            <div class="space-y-2">
                                <div class="d-flex justify-content-between py-1">
                                    <span class="text-muted small">Specialization</span>
                                    <span class="fw-medium small"><?= esc($appt['d_specialization']) ?></span>
                                </div>
                                <div class="d-flex justify-content-between py-1">
                                    <span class="text-muted small">Availability</span>
                                    <span class="badge bg-success-subtle text-success small" style="font-size: 10px;">Available</span>
                                </div>
                                <div class="d-flex justify-content-between py-1">
                                    <span class="text-muted small">Doctor Email</span>
                                    <span class="fw-medium small text-truncate ms-2"><?= esc($appt['d_email'] ?: 'N/A') ?></span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="mt-4 p-3 rounded-4" style="background: rgba(79, 70, 229, 0.03); border: 1px dashed rgba(79, 70, 229, 0.2);">
                    <h6 class="text-muted fw-bold small text-uppercase mb-2" style="letter-spacing: 0.5px;"><i class="fas fa-notes-medical me-2"></i>Consultation Notes</h6>
                    <p class="mb-0 text-dark" style="font-size: 14px; line-height: 1.6;">
                        <?= nl2br(esc($appt['notes'] ?: 'No notes provided for this appointment.')) ?>
                    </p>
                </div>

                <!-- Prescription & Diagnostics Card -->
                <div class="card border-0 mt-4 shadow-sm" style="border-radius: 16px !important;">
                    <div class="card-header bg-white border-0 pt-4 px-4 pb-0 d-flex justify-content-between align-items-center">
                        <h5 class="fw-bold mb-0 text-dark" style="letter-spacing: -0.3px;"><i class="fas fa-file-prescription me-2 text-indigo"></i>Digital Prescription & Diagnostics</h5>
                        <?php if ($prescription): ?>
                            <a href="print_prescription.php?id=<?= $prescription['id'] ?>" target="_blank" class="btn btn-sm btn-outline-primary px-3 rounded-pill" style="font-size: 12px; font-weight: 600;">
                                <i class="fas fa-print me-1"></i> Print / Download
                            </a>
                        <?php endif; ?>
                    </div>
                    <div class="card-body p-4">
                        <?php if ($prescription): ?>
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <h6 class="text-muted fw-bold small text-uppercase" style="font-size: 11px; letter-spacing: 0.5px;">Symptoms</h6>
                                    <p class="text-dark bg-light p-3 rounded-3 mb-0" style="font-size: 14px; min-height: 80px;"><?= nl2br(esc($prescription['symptoms'])) ?></p>
                                </div>
                                <div class="col-md-6">
                                    <h6 class="text-muted fw-bold small text-uppercase" style="font-size: 11px; letter-spacing: 0.5px;">Diagnosis</h6>
                                    <p class="text-dark bg-light p-3 rounded-3 mb-0" style="font-size: 14px; min-height: 80px;"><?= nl2br(esc($prescription['diagnosis'])) ?></p>
                                </div>
                                <div class="col-12 mt-3">
                                    <h6 class="text-muted fw-bold small text-uppercase" style="font-size: 11px; letter-spacing: 0.5px;">Prescribed Medications</h6>
                                    <div class="bg-light p-3 rounded-3 mb-0" style="font-size: 14px; font-family: monospace; white-space: pre-wrap; min-height: 100px;"><?= esc($prescription['medications']) ?></div>
                                </div>
                                <div class="col-12 mt-3">
                                    <h6 class="text-muted fw-bold small text-uppercase" style="font-size: 11px; letter-spacing: 0.5px;">Dosage Instructions / Remarks</h6>
                                    <p class="text-dark bg-light p-3 rounded-3 mb-0" style="font-size: 14px; min-height: 80px;"><?= nl2br(esc($prescription['instructions'] ?: 'None')) ?></p>
                                </div>
                            </div>
                            
                            <?php if ($_SESSION['role'] === 'doctor'): ?>
                                <div class="mt-4 pt-3 border-top text-end">
                                    <button type="button" class="btn btn-sm btn-light px-3 border" data-bs-toggle="collapse" data-bs-target="#editPrescriptionCollapse" style="border-radius: 8px;">
                                        <i class="fas fa-edit me-1"></i> Edit Prescription
                                    </button>
                                </div>
                                
                                <div class="collapse mt-3" id="editPrescriptionCollapse">
                                    <form method="POST" class="p-3 border rounded-3 bg-light">
                                        <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">
                                        <h6 class="fw-bold mb-3" style="font-size: 14px;">Update Prescription Details</h6>
                                        <div class="row g-3">
                                            <div class="col-md-6">
                                                <label class="form-label small fw-bold">Symptoms</label>
                                                <textarea name="symptoms" class="form-control form-control-sm" rows="3" required><?= esc($prescription['symptoms']) ?></textarea>
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label small fw-bold">Diagnosis</label>
                                                <textarea name="diagnosis" class="form-control form-control-sm" rows="3" required><?= esc($prescription['diagnosis']) ?></textarea>
                                            </div>
                                            <div class="col-12">
                                                <label class="form-label small fw-bold">Medications (One per line: e.g. Paracetamol 500mg - 1 Tab - Twice Daily)</label>
                                                <textarea name="medications" class="form-control form-control-sm" rows="4" required><?= esc($prescription['medications']) ?></textarea>
                                            </div>
                                            <div class="col-12">
                                                <label class="form-label small fw-bold">Instructions / Remarks</label>
                                                <textarea name="instructions" class="form-control form-control-sm" rows="3"><?= esc($prescription['instructions']) ?></textarea>
                                            </div>
                                            <div class="col-12 text-end">
                                                <button type="submit" name="save_prescription" class="btn btn-primary btn-sm px-4" style="border-radius: 8px;">
                                                    Update Changes
                                                </button>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                            <?php endif; ?>

                        <?php else: ?>
                            <?php if ($_SESSION['role'] === 'doctor'): ?>
                                <p class="text-muted small mb-3">No digital prescription generated yet for this appointment. As the attending doctor, you can draft one below:</p>
                                <form method="POST" class="p-3 border rounded-3 bg-light">
                                    <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">
                                    <div class="row g-3">
                                        <div class="col-md-6">
                                            <label class="form-label small fw-bold">Symptoms</label>
                                            <textarea name="symptoms" class="form-control form-control-sm" rows="3" placeholder="Enter patient symptoms (e.g. Cough, high grade fever)" required></textarea>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label small fw-bold">Diagnosis</label>
                                            <textarea name="diagnosis" class="form-control form-control-sm" rows="3" placeholder="Enter primary diagnosis (e.g. Acute bronchitis)" required></textarea>
                                        </div>
                                        <div class="col-12">
                                            <label class="form-label small fw-bold">Medications (One per line: e.g. Paracetamol 500mg - 1 Tab - Twice Daily)</label>
                                            <textarea name="medications" class="form-control form-control-sm" rows="4" placeholder="1. Amoxicillin 500mg - 1 Cap - Three times daily&#10;2. Panadol 500mg - 2 Tabs - SOS" required></textarea>
                                        </div>
                                        <div class="col-12">
                                            <label class="form-label small fw-bold">Instructions / Remarks</label>
                                            <textarea name="instructions" class="form-control form-control-sm" rows="3" placeholder="Take after meals. Complete the antibiotic course."></textarea>
                                        </div>
                                        <div class="col-12 text-end">
                                            <button type="submit" name="save_prescription" class="btn btn-success btn-sm px-4" style="border-radius: 8px;">
                                                Create & Save Prescription
                                            </button>
                                        </div>
                                    </div>
                                </form>
                            <?php else: ?>
                                <div class="text-center py-4">
                                    <div style="background: #f8fafc; width: 64px; height: 64px; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 12px;">
                                        <i class="fas fa-receipt" style="font-size: 24px; color: #cbd5e1;"></i>
                                    </div>
                                    <p class="text-muted small fw-medium mb-0">No prescription generated yet by the attending doctor.</p>
                                </div>
                            <?php endif; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Sidebar Info -->
    <div class="col-lg-4">
        <div class="card border-0 mb-4 shadow-sm">
            <div class="card-header bg-white border-0 pt-4 px-4 pb-0">
                <h6 class="fw-bold mb-0">Manage Status</h6>
            </div>
            <div class="card-body p-4">
                <form action="update_appointment_status.php" method="POST">
                    <input type="hidden" name="id" value="<?= $id ?>">
                    <div class="mb-3">
                        <label class="form-label">Quick Update</label>
                        <select name="status" class="form-select">
                            <option value="pending" <?= $appt['status'] === 'pending' ? 'selected' : '' ?>>Pending Approval</option>
                            <option value="confirmed" <?= $appt['status'] === 'confirmed' ? 'selected' : '' ?>>Confirm Appointment</option>
                            <option value="completed" <?= $appt['status'] === 'completed' ? 'selected' : '' ?>>Mark as Completed</option>
                            <option value="cancelled" <?= $appt['status'] === 'cancelled' ? 'selected' : '' ?>>Cancel Appointment</option>
                        </select>
                    </div>
                    <button type="submit" class="btn btn-primary w-100 py-3" style="border-radius: 12px; font-weight: 700;">
                        Apply Changes
                    </button>
                </form>
            </div>
        </div>

        <div class="card border-0 shadow-sm" style="background: linear-gradient(135deg, #1e293b, #0f172a); color: white;">
            <div class="card-body p-4">
                <h6 class="fw-bold mb-3 text-white-50 small text-uppercase" style="letter-spacing: 1px;">Audit Trail</h6>
                <div class="d-flex gap-3 mb-4">
                    <div style="width: 2px; background: rgba(255,255,255,0.1); position: relative;">
                        <div style="position: absolute; top: 0; left: -4px; width: 10px; height: 10px; border-radius: 50%; background: var(--accent);"></div>
                    </div>
                    <div>
                        <div class="fw-bold small">Entry Created</div>
                        <div class="text-white-50" style="font-size: 11px;">By <?= esc($appt['creator_name'] ?: 'System') ?></div>
                        <div class="text-white-50" style="font-size: 11px;"><?= date('M j, Y - h:i A', strtotime($appt['created_at'])) ?></div>
                    </div>
                </div>
                <?php if ($appt['updated_at'] != $appt['created_at']): ?>
                <div class="d-flex gap-3">
                    <div style="width: 2px; background: rgba(255,255,255,0.1); position: relative;">
                        <div style="position: absolute; top: 0; left: -4px; width: 10px; height: 10px; border-radius: 50%; background: #10b981;"></div>
                    </div>
                    <div>
                        <div class="fw-bold small">Last Modified</div>
                        <div class="text-white-50" style="font-size: 11px;">By <?= esc($appt['updater_name'] ?: 'System') ?></div>
                        <div class="text-white-50" style="font-size: 11px;"><?= date('M j, Y - h:i A', strtotime($appt['updated_at'])) ?></div>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php require 'includes/footer.php'; ?>
