<?php
$page_title = 'Patient EHR Dossier';
require_once 'includes/header.php';

$pdo = get_db_pdo();
$patient_id = $_GET['id'] ?? 0;

$stmt = $pdo->prepare("SELECT * FROM patients WHERE id = ?");
$stmt->execute([$patient_id]);
$patient = $stmt->fetch();

if (!$patient) {
    echo '<div class="alert alert-danger mt-3">Patient record not found.</div>';
    require_once 'includes/footer.php';
    exit();
}

// Handle Patient Insurance details update
if (isset($_POST['save_insurance'])) {
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        set_flash('Invalid security token.', 'danger');
    } else {
        $company_id = (int)$_POST['company_id'];
        $policy_number = trim($_POST['policy_number'] ?? '');
        
        if ($company_id > 0 && !empty($policy_number)) {
            $up_stmt = $pdo->prepare("UPDATE patient_insurance SET status = 'expired' WHERE patient_id = ?");
            $up_stmt->execute([$patient_id]);
            
            $ins_policy = $pdo->prepare("INSERT INTO patient_insurance (patient_id, company_id, policy_number, status) VALUES (?, ?, ?, 'active')");
            $ins_policy->execute([$patient_id, $company_id, $policy_number]);
            
            set_flash('Insurance policy updated successfully!');
            header("Location: patient_history.php?id=$patient_id");
            exit();
        } else {
            set_flash('Please provide valid insurance parameters.', 'danger');
        }
    }
}

$stmt = $pdo->prepare("
    SELECT pr.*, d.name as doctor_name, d.specialization, a.app_date, a.app_time
    FROM prescriptions pr
    JOIN doctors d ON pr.doctor_id = d.id
    JOIN appointments a ON pr.appointment_id = a.id
    WHERE pr.patient_id = ?
    ORDER BY a.app_date DESC, a.app_time DESC
");
$stmt->execute([$patient_id]);
$encounters = $stmt->fetchAll();

$stmt = $pdo->prepare("
    SELECT i.*, a.app_date 
    FROM invoices i
    JOIN appointments a ON i.appointment_id = a.id
    WHERE i.patient_id = ?
    ORDER BY i.created_at DESC
");
$stmt->execute([$patient_id]);
$invoices = $stmt->fetchAll();

// Fetch insurance details
$ins_stmt = $pdo->prepare("
    SELECT pi.*, ic.name as company_name, ic.coverage_percentage
    FROM patient_insurance pi
    JOIN insurance_companies ic ON pi.company_id = ic.id
    WHERE pi.patient_id = ? AND pi.status = 'active'
    LIMIT 1
");
$ins_stmt->execute([$patient_id]);
$insurance = $ins_stmt->fetch();

$companies = $pdo->query("SELECT * FROM insurance_companies ORDER BY name ASC")->fetchAll();

$chart_encounters = array_reverse($encounters);
$chart_dates = [];
$bp_systolic = [];
$bp_diastolic = [];
$heart_rates = [];
$temperatures = [];
$weights = [];

foreach ($chart_encounters as $enc) {
    $date_label = date('M j, Y', strtotime($enc['app_date']));
    $chart_dates[] = $date_label;
    
    // BP parsing (e.g. "120/80")
    $bp = $enc['blood_pressure'];
    $sys = 0; $dia = 0;
    if ($bp && strpos($bp, '/') !== false) {
        $parts = explode('/', $bp);
        $sys = (int)trim($parts[0]);
        $dia = (int)trim($parts[1]);
    }
    $bp_systolic[] = $sys;
    $bp_diastolic[] = $dia;
    
    $heart_rates[] = $enc['heart_rate'] ? (int)$enc['heart_rate'] : null;
    $temperatures[] = $enc['temperature'] ? (float)$enc['temperature'] : null;
    $weights[] = $enc['weight'] ? (float)$enc['weight'] : null;
}
?>

<!-- Load Chart.js for Vitals Charting -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<div class="row mb-4">
    <div class="col-12 d-flex justify-content-between align-items-center">
        <div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-1">
                    <li class="breadcrumb-item"><a href="index.php" style="color:var(--muted); text-decoration:none;">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="patients.php" style="color:var(--muted); text-decoration:none;">Patients</a></li>
                    <li class="breadcrumb-item active" aria-current="page">EHR Profile</li>
                </ol>
            </nav>
            <h4 class="fw-bold mb-0" style="color:var(--text); letter-spacing:-0.5px;">Patient Medical Record (EHR)</h4>
        </div>
        <div>
            <a href="patients.php" class="btn btn-light" style="border: 1.5px solid #e2e8f0; border-radius: 12px;">
                <i class="fas fa-arrow-left me-2"></i>Back to Directory
            </a>
        </div>
    </div>
</div>

<div class="row g-4">
    <!-- Patient Info Panel -->
    <div class="col-md-4">
        <div class="card border-0 shadow-sm mb-4" style="border-radius: 16px;">
            <div class="card-body p-4">
                <div class="text-center pb-4 border-bottom">
                    <div style="width: 80px; height: 80px; background: rgba(79, 70, 229, 0.1); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 16px; color: var(--accent);">
                        <i class="fas fa-user-injured fa-3x"></i>
                    </div>
                    <h5 class="fw-bold mb-1"><?= esc($patient['name']) ?></h5>
                    <span class="badge bg-primary-subtle text-primary px-3 py-2 rounded-pill" style="font-size:12px;">
                        Patient ID: #PAT-<?= $patient['id'] ?>
                    </span>
                </div>
                
                <div class="pt-4 space-y-3">
                    <div class="d-flex justify-content-between mb-3">
                        <span class="text-muted small fw-medium">Gender:</span>
                        <span class="fw-semibold small"><?= esc($patient['gender']) ?></span>
                    </div>
                    <div class="d-flex justify-content-between mb-3">
                        <span class="text-muted small fw-medium">Date of Birth:</span>
                        <span class="fw-semibold small"><?= $patient['date_of_birth'] ? date('M j, Y', strtotime($patient['date_of_birth'])) : 'N/A' ?></span>
                    </div>
                    <div class="d-flex justify-content-between mb-3">
                        <span class="text-muted small fw-medium">Phone:</span>
                        <span class="fw-semibold small"><?= esc($patient['phone'] ?: 'N/A') ?></span>
                    </div>
                    <div class="d-flex justify-content-between mb-3">
                        <span class="text-muted small fw-medium">Email:</span>
                        <span class="fw-semibold small"><?= esc($patient['email'] ?: 'N/A') ?></span>
                    </div>
                    <div class="mb-3">
                        <span class="text-muted small fw-medium d-block mb-1">Residential Address:</span>
                        <span class="fw-semibold small d-block bg-light p-2 rounded-3 text-secondary"><?= esc($patient['address'] ?: 'No address recorded') ?></span>
                    </div>
                    <?php if ($patient['emergency_contact']): ?>
                    <div class="p-3 bg-danger-subtle rounded-3 border border-danger-subtle mt-3">
                        <span class="text-danger small fw-bold d-block"><i class="fas fa-exclamation-triangle me-1"></i> Emergency Contact</span>
                        <span class="fw-bold small text-dark mt-1 d-block"><?= esc($patient['emergency_contact']) ?></span>
                    </div>
                    <?php endif; ?>

                    <!-- Insurance policy details box -->
                    <div class="pt-4 border-top mt-4">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <span class="text-secondary small fw-bold text-uppercase" style="font-size:11px;"><i class="fas fa-id-card me-1"></i> Insurance Policy</span>
                            <?php if (in_array($_SESSION['role'], ['admin', 'receptionist'])): ?>
                                <button class="btn btn-xs btn-outline-primary py-0 px-2 rounded-pill" data-bs-toggle="modal" data-bs-target="#insuranceModal" style="font-size:10px;">Edit</button>
                            <?php endif; ?>
                        </div>
                        <?php if ($insurance): ?>
                            <div class="p-3 bg-primary-subtle rounded-3 border border-primary-subtle text-primary">
                                <span class="fw-bold d-block text-dark" style="font-size:13px;"><?= esc($insurance['company_name']) ?></span>
                                <span class="small d-block text-secondary mt-1">Policy: <strong><?= esc($insurance['policy_number']) ?></strong></span>
                                <span class="small d-block text-success fw-bold mt-1"><i class="fas fa-shield-alt me-1"></i> Covers <?= number_format($insurance['coverage_percentage'], 0) ?>% of Bills</span>
                            </div>
                        <?php else: ?>
                            <div class="p-3 rounded-3 border border-dashed text-center bg-light">
                                <span class="text-muted small d-block mb-2">No active health insurance policy.</span>
                                <?php if (in_array($_SESSION['role'], ['admin', 'receptionist'])): ?>
                                    <button class="btn btn-xs btn-outline-secondary py-1 px-3 rounded-pill" data-bs-toggle="modal" data-bs-target="#insuranceModal" style="font-size:11px;">Attach Policy</button>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- EHR Record Tabs Panel -->
    <div class="col-md-8">
        <div class="card border-0 shadow-sm" style="border-radius: 16px;">
            <div class="card-header bg-white border-0 pt-4 px-4 pb-0">
                <ul class="nav nav-tabs border-bottom-0" id="ehrTab" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active fw-bold px-4 pb-3" id="timeline-tab" data-bs-toggle="tab" data-bs-target="#timeline" type="button" role="tab" aria-controls="timeline" aria-selected="true" style="border: none; border-bottom: 3px solid transparent;">
                            <i class="fas fa-history me-2"></i>History Timeline
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link fw-bold px-4 pb-3" id="vitals-tab" data-bs-toggle="tab" data-bs-target="#vitals" type="button" role="tab" aria-controls="vitals" aria-selected="false" style="border: none; border-bottom: 3px solid transparent;">
                            <i class="fas fa-heartbeat me-2"></i>Vital Trends
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link fw-bold px-4 pb-3" id="billing-tab" data-bs-toggle="tab" data-bs-target="#billing" type="button" role="tab" aria-controls="billing" aria-selected="false" style="border: none; border-bottom: 3px solid transparent;">
                            <i class="fas fa-file-invoice-dollar me-2"></i>Invoices
                        </button>
                    </li>
                </ul>
            </div>
            
            <div class="card-body p-4">
                <div class="tab-content" id="ehrTabContent">

                    <div class="tab-pane fade show active" id="timeline" role="tabpanel" aria-labelledby="timeline-tab">
                        <?php if (empty($encounters)): ?>
                            <div class="text-center py-5">
                                <div style="background: #f8fafc; width: 64px; height: 64px; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 16px;">
                                    <i class="fas fa-folder-open text-muted fa-2x"></i>
                                </div>
                                <h6 class="fw-bold text-muted">No Clinical Encounters Found</h6>
                                <p class="text-muted small mb-0">This patient has no registered prescriptions or diagnoses in the system.</p>
                            </div>
                        <?php else: ?>
                            <div class="timeline-container ps-3" style="border-left: 2px solid #e2e8f0; position: relative;">
                                <?php foreach ($encounters as $index => $enc): ?>
                                    <div class="timeline-item mb-5" style="position: relative;">
                                        <!-- Bullet indicator -->
                                        <div style="position: absolute; left: -25px; top: 0; width: 14px; height: 14px; border-radius: 50%; background: var(--accent); border: 3px solid #fff; box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.15);"></div>
                                        
                                        <div class="p-4 bg-light rounded-4 border border-white" style="box-shadow: 0 4px 12px rgba(0,0,0,0.01);">
                                            <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
                                                <div>
                                                    <span class="badge bg-primary mb-1" style="font-size: 11px;">Prescription #<?= $enc['id'] ?></span>
                                                    <h6 class="fw-bold mb-0 text-dark">Consultation with <?= esc($enc['doctor_name']) ?></h6>
                                                    <small class="text-muted"><?= esc($enc['specialization']) ?></small>
                                                </div>
                                                <div class="text-end">
                                                    <span class="badge bg-light text-dark border p-2 small">
                                                        <i class="far fa-calendar-alt me-1 text-primary"></i> <?= date('M j, Y', strtotime($enc['app_date'])) ?>
                                                    </span>
                                                </div>
                                            </div>
                                            
                                            <div class="row g-2 mb-3 bg-white p-2 rounded-3 border border-light">
                                                <div class="col-6 col-md-3 text-center border-end">
                                                    <small class="text-muted d-block" style="font-size: 10px;">BP</small>
                                                    <span class="fw-bold text-dark small"><?= esc($enc['blood_pressure'] ?: 'N/A') ?></span>
                                                </div>
                                                <div class="col-6 col-md-3 text-center border-end">
                                                    <small class="text-muted d-block" style="font-size: 10px;">Heart Rate</small>
                                                    <span class="fw-bold text-dark small"><?= $enc['heart_rate'] ? esc($enc['heart_rate']).' bpm' : 'N/A' ?></span>
                                                </div>
                                                <div class="col-6 col-md-3 text-center border-end">
                                                    <small class="text-muted d-block" style="font-size: 10px;">Temp</small>
                                                    <span class="fw-bold text-dark small"><?= $enc['temperature'] ? esc($enc['temperature']).' °C' : 'N/A' ?></span>
                                                </div>
                                                <div class="col-6 col-md-3 text-center">
                                                    <small class="text-muted d-block" style="font-size: 10px;">Weight</small>
                                                    <span class="fw-bold text-dark small"><?= $enc['weight'] ? esc($enc['weight']).' kg' : 'N/A' ?></span>
                                                </div>
                                            </div>
                                            
                                            <div class="mb-3">
                                                <div class="fw-bold text-secondary mb-1" style="font-size: 12px; text-transform: uppercase; letter-spacing: 0.5px;">Symptoms</div>
                                                <p class="mb-0 text-dark small bg-white p-3 rounded-3" style="border: 1px solid #f1f5f9;"><?= nl2br(esc($enc['symptoms'])) ?></p>
                                            </div>
                                            
                                            <div class="mb-3">
                                                <div class="fw-bold text-secondary mb-1" style="font-size: 12px; text-transform: uppercase; letter-spacing: 0.5px;">Diagnosis</div>
                                                <p class="mb-0 text-dark small bg-white p-3 rounded-3" style="border: 1px solid #f1f5f9;"><?= nl2br(esc($enc['diagnosis'])) ?></p>
                                            </div>

                                            <div class="mb-3">
                                                <div class="fw-bold text-secondary mb-1" style="font-size: 12px; text-transform: uppercase; letter-spacing: 0.5px;">Medications</div>
                                                <div class="text-dark small bg-white p-3 rounded-3" style="font-family: monospace; white-space: pre-wrap; border: 1px solid #f1f5f9;"><?= esc($enc['medications']) ?></div>
                                            </div>
                                            
                                            <?php if ($enc['instructions']): ?>
                                            <div class="mb-3">
                                                <div class="fw-bold text-secondary mb-1" style="font-size: 12px; text-transform: uppercase; letter-spacing: 0.5px;">Instructions</div>
                                                <p class="mb-0 text-muted small bg-white p-3 rounded-3" style="border: 1px solid #f1f5f9;"><?= nl2br(esc($enc['instructions'])) ?></p>
                                            </div>
                                            <?php endif; ?>

                                            <!-- Lab tests ordered during this encounter -->
                                            <?php
                                            $stmt_lab = $pdo->prepare("
                                                SELECT lr.*, lt.name as test_name 
                                                FROM lab_requests lr 
                                                JOIN lab_tests lt ON lr.test_id = lt.id 
                                                WHERE lr.appointment_id = ?
                                            ");
                                            $stmt_lab->execute([$enc['appointment_id']]);
                                            $labs = $stmt_lab->fetchAll();
                                            
                                            if (!empty($labs)):
                                            ?>
                                            <div class="mt-3 pt-2 border-top">
                                                <div class="fw-bold text-secondary mb-1" style="font-size: 11px; text-transform: uppercase; letter-spacing: 0.5px;">Diagnostic Investigations Ordered</div>
                                                <div class="row g-2 mt-1">
                                                    <?php foreach ($labs as $l): ?>
                                                        <div class="col-md-6 col-12">
                                                            <div class="p-2 rounded bg-white border small">
                                                                <span class="fw-bold text-dark d-block"><?= esc($l['test_name']) ?></span>
                                                                <?php if ($l['status'] === 'completed'): ?>
                                                                    <span class="text-success fw-medium d-block mt-1" style="font-size:11px;"><i class="fas fa-check-circle me-1"></i> Result: <?= esc($l['result_details']) ?></span>
                                                                <?php else: ?>
                                                                    <span class="text-warning fw-medium d-block mt-1" style="font-size:11px;"><i class="fas fa-spinner fa-spin me-1"></i> Status: Processing in lab</span>
                                                                <?php endif; ?>
                                                            </div>
                                                        </div>
                                                    <?php endforeach; ?>
                                                </div>
                                            </div>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>

                    <div class="tab-pane fade" id="vitals" role="tabpanel" aria-labelledby="vitals-tab">
                        <?php if (empty($encounters)): ?>
                            <div class="text-center py-5">
                                <div style="background: #f8fafc; width: 64px; height: 64px; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 16px;">
                                    <i class="fas fa-heartbeat text-muted fa-2x"></i>
                                </div>
                                <h6 class="fw-bold text-muted">No Vitals Logs Found</h6>
                                <p class="text-muted small mb-0">Record a prescription for this patient first to track vital stats.</p>
                            </div>
                        <?php else: ?>
                            <div class="mb-4">
                                <h6 class="fw-bold mb-3">Vitals History Trends (Chronological)</h6>
                                <div class="bg-white p-3 rounded-4 border" style="position: relative; height: 350px;">
                                    <canvas id="vitalsChart"></canvas>
                                </div>
                            </div>
                            
                            <script>
                            document.addEventListener("DOMContentLoaded", function() {
                                const ctx = document.getElementById('vitalsChart').getContext('2d');
                                new Chart(ctx, {
                                    type: 'line',
                                    data: {
                                        labels: <?= json_encode($chart_dates) ?>,
                                        datasets: [
                                            {
                                                label: 'Systolic BP (mmHg)',
                                                data: <?= json_encode($bp_systolic) ?>,
                                                borderColor: '#ef4444',
                                                backgroundColor: 'rgba(239, 68, 68, 0.1)',
                                                borderWidth: 2.5,
                                                tension: 0.2,
                                                fill: false
                                            },
                                            {
                                                label: 'Diastolic BP (mmHg)',
                                                data: <?= json_encode($bp_diastolic) ?>,
                                                borderColor: '#3b82f6',
                                                backgroundColor: 'rgba(59, 130, 246, 0.1)',
                                                borderWidth: 2,
                                                tension: 0.2,
                                                fill: false
                                            },
                                            {
                                                label: 'Heart Rate (bpm)',
                                                data: <?= json_encode($heart_rates) ?>,
                                                borderColor: '#10b981',
                                                backgroundColor: 'rgba(16, 185, 129, 0.1)',
                                                borderWidth: 2,
                                                tension: 0.2,
                                                fill: false
                                            },
                                            {
                                                label: 'Temp (°C)',
                                                data: <?= json_encode($temperatures) ?>,
                                                borderColor: '#f59e0b',
                                                backgroundColor: 'rgba(245, 158, 11, 0.1)',
                                                borderWidth: 2,
                                                tension: 0.2,
                                                fill: false,
                                                yAxisID: 'y1'
                                            }
                                        ]
                                    },
                                    options: {
                                        responsive: true,
                                        maintainAspectRatio: false,
                                        scales: {
                                            y: {
                                                type: 'linear',
                                                display: true,
                                                position: 'left',
                                                title: {
                                                    display: true,
                                                    text: 'BP / HR'
                                                }
                                            },
                                            y1: {
                                                type: 'linear',
                                                display: true,
                                                position: 'right',
                                                grid: {
                                                    drawOnChartArea: false
                                                },
                                                title: {
                                                    display: true,
                                                    text: 'Temp (°C)'
                                                }
                                            }
                                        }
                                    }
                                });
                            });
                            </script>
                        <?php endif; ?>
                    </div>
                    
                    <!-- BILLING TAB -->
                    <div class="tab-pane fade" id="billing" role="tabpanel" aria-labelledby="billing-tab">
                        <?php if (empty($invoices)): ?>
                            <div class="text-center py-5">
                                <div style="background: #f8fafc; width: 64px; height: 64px; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 16px;">
                                    <i class="fas fa-file-invoice text-muted fa-2x"></i>
                                </div>
                                <h6 class="fw-bold text-muted">No Invoices Issued</h6>
                                <p class="text-muted small mb-0">No bills have been registered for this patient yet.</p>
                            </div>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table align-middle">
                                    <thead>
                                        <tr>
                                            <th>Invoice ID</th>
                                            <th>Date</th>
                                            <th>Gross Amount</th>
                                            <th>Net Amount</th>
                                            <th>Status</th>
                                            <th class="text-end">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($invoices as $inv): ?>
                                            <tr>
                                                <td class="fw-bold">#INV-<?= $inv['id'] ?></td>
                                                <td><?= date('M j, Y', strtotime($inv['created_at'])) ?></td>
                                                <td>$<?= number_format($inv['total_amount'], 2) ?></td>
                                                <td class="fw-bold text-primary">$<?= number_format($inv['net_amount'], 2) ?></td>
                                                <td>
                                                    <?php
                                                    $badge = match ($inv['status']) {
                                                        'paid' => 'success',
                                                        'unpaid' => 'danger',
                                                        'partially_paid' => 'warning',
                                                    };
                                                    ?>
                                                    <span class="badge bg-<?= $badge ?>-subtle text-<?= $badge ?> px-3 py-2 rounded-pill" style="font-size: 11px;">
                                                        <?= ucfirst(str_replace('_', ' ', $inv['status'])) ?>
                                                    </span>
                                                </td>
                                                <td class="text-end">
                                                    <a href="invoice_details.php?id=<?= $inv['id'] ?>" class="btn btn-sm btn-outline-primary px-3 rounded-pill" style="font-size: 12px; font-weight:600;">
                                                        <i class="fas fa-eye me-1"></i> View Bill
                                                    </a>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php endif; ?>
                    </div>
                    
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Insurance Modal -->
<div class="modal fade" id="insuranceModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow rounded-4">
            <div class="modal-header border-bottom-0 p-4 pb-0">
                <h5 class="modal-title fw-bold">Attach Health Insurance Policy</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
                <div class="modal-body p-4">
                    <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">
                    
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Insurance Provider / Carrier</label>
                        <select name="company_id" class="form-select rounded-3" required>
                            <option value="" disabled selected>Choose provider...</option>
                            <?php foreach ($companies as $c): ?>
                                <option value="<?= $c['id'] ?>" <?= $insurance && $insurance['company_id'] == $c['id'] ? 'selected' : '' ?>>
                                    <?= esc($c['name']) ?> (Covers <?= number_format($c['coverage_percentage'], 0) ?>%)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="mb-0">
                        <label class="form-label small fw-bold">Policy / Membership Number</label>
                        <input type="text" name="policy_number" class="form-control rounded-3" value="<?= $insurance ? esc($insurance['policy_number']) : '' ?>" placeholder="e.g. POL-89739281-BC" required>
                    </div>
                </div>
                <div class="modal-footer border-top-0 p-4 pt-0">
                    <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" name="save_insurance" class="btn btn-primary rounded-pill px-4">Save Policy Details</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Extra styles for active tab layout matching our custom themes -->
<style>
#ehrTab .nav-link.active {
    color: var(--accent) !important;
    border-bottom-color: var(--accent) !important;
    background: transparent !important;
}
#ehrTab .nav-link {
    color: var(--muted);
    border-bottom: 3px solid transparent;
}
#ehrTab .nav-link:hover {
    color: var(--text);
    border-bottom-color: #cbd5e1;
}
</style>

<?php require_once 'includes/footer.php'; ?>
