<?php
$page_title = 'Patient EHR Dossier';
require_once 'includes/header.php';

$pdo = get_db_pdo();
$patient_id = $_GET['id'] ?? 0;

// Fetch patient
$stmt = $pdo->prepare("SELECT * FROM patients WHERE id = ?");
$stmt->execute([$patient_id]);
$patient = $stmt->fetch();

if (!$patient) {
    echo '<div class="alert alert-danger mt-3">Patient record not found.</div>';
    require_once 'includes/footer.php';
    exit();
}

// Fetch encounters (prescriptions / clinical records)
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

// Fetch invoices
$stmt = $pdo->prepare("
    SELECT i.*, a.app_date 
    FROM invoices i
    JOIN appointments a ON i.appointment_id = a.id
    WHERE i.patient_id = ?
    ORDER BY i.created_at DESC
");
$stmt->execute([$patient_id]);
$invoices = $stmt->fetchAll();

// Prepare charts data (chronological order)
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
                    
                    <!-- TIMELINE TAB -->
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
                                            <div>
                                                <div class="fw-bold text-secondary mb-1" style="font-size: 12px; text-transform: uppercase; letter-spacing: 0.5px;">Instructions</div>
                                                <p class="mb-0 text-muted small bg-white p-3 rounded-3" style="border: 1px solid #f1f5f9;"><?= nl2br(esc($enc['instructions'])) ?></p>
                                            </div>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                    
                    <!-- VITALS TRENDS TAB -->
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
