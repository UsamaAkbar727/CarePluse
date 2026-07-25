<?php
$page_title = 'Executive Command Dashboard';
require_once 'includes/header.php';
require_once 'config.php';
require_once 'includes/ai_copilot.php';

$pdo = get_conn();

// Analytics computation
$total_patients = $pdo->query("SELECT COUNT(*) FROM patients")->fetchColumn() ?: 0;
$total_doctors = $pdo->query("SELECT COUNT(*) FROM doctors")->fetchColumn() ?: 0;
$today_appointments = $pdo->query("SELECT COUNT(*) FROM appointments WHERE app_date = CURDATE()")->fetchColumn() ?: 0;
$pending_appointments = $pdo->query("SELECT COUNT(*) FROM appointments WHERE status = 'pending'")->fetchColumn() ?: 0;
$occupied_beds = $pdo->query("SELECT COUNT(*) FROM beds WHERE status = 'occupied'")->fetchColumn() ?: 0;
$total_beds = $pdo->query("SELECT COUNT(*) FROM beds")->fetchColumn() ?: 1;

$predictive = CarePulseAI::getPredictiveAnalytics();
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h2 class="h3 mb-1 font-weight-bold text-gray-800">📊 Enterprise Hospital Command Center</h2>
        <p class="text-muted mb-0">Real-time Clinical Operations, Bed Utilization & AI Predictive Intelligence</p>
    </div>
    <div class="d-flex gap-2">
        <a href="telehealth.php" class="btn btn-info text-white font-weight-bold shadow-sm">
            <i class="fas fa-video mr-1"></i> Launch Telehealth Portal
        </a>
        <a href="shift_handoff.php" class="btn btn-warning text-dark font-weight-bold shadow-sm">
            <i class="fas fa-exchange-alt mr-1"></i> Shift Handoff
        </a>
    </div>
</div>

<!-- AI Predictive Alert Banner -->
<div class="alert alert-primary border-0 shadow-sm rounded-lg mb-4 p-3 bg-gradient-primary text-white d-flex align-items-center justify-content-between">
    <div class="d-flex align-items-center">
        <div class="rounded-circle bg-white text-primary d-flex align-items-center justify-content-center mr-3" style="width: 45px; height: 45px; flex-shrink:0;">
            <i class="fas fa-brain fa-lg"></i>
        </div>
        <div>
            <h6 class="font-weight-bold mb-0">AI Clinical Intelligence Insight</h6>
            <small class="text-white-50"><?= htmlspecialchars($predictive['ai_insight']) ?></small>
        </div>
    </div>
    <span class="badge badge-light text-primary px-3 py-2 font-weight-bold">Occupancy Forecast: <?= $predictive['projected_occupancy_peak'] ?>%</span>
</div>

<!-- KPI Cards -->
<div class="row mb-4">
    <div class="col-xl-3 col-md-6 mb-3">
        <div class="card border-left-primary shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Today's Appointments</div>
                        <div class="h4 mb-0 font-weight-bold text-gray-800"><?= $today_appointments ?></div>
                    </div>
                    <div class="col-auto"><i class="fas fa-calendar-check fa-2x text-gray-300"></i></div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6 mb-3">
        <div class="card border-left-success shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Bed Occupancy Rate</div>
                        <div class="h4 mb-0 font-weight-bold text-gray-800"><?= $predictive['current_occupancy'] ?>% (<?= $occupied_beds ?>/<?= $total_beds ?>)</div>
                    </div>
                    <div class="col-auto"><i class="fas fa-procedures fa-2x text-gray-300"></i></div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6 mb-3">
        <div class="card border-left-info shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-info text-uppercase mb-1">Active Patient Dossiers</div>
                        <div class="h4 mb-0 font-weight-bold text-gray-800"><?= $total_patients ?></div>
                    </div>
                    <div class="col-auto"><i class="fas fa-user-injured fa-2x text-gray-300"></i></div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6 mb-3">
        <div class="card border-left-warning shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">Available Doctors</div>
                        <div class="h4 mb-0 font-weight-bold text-gray-800"><?= $total_doctors ?></div>
                    </div>
                    <div class="col-auto"><i class="fas fa-user-md fa-2x text-gray-300"></i></div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Quick Shortcuts Bar -->
<div class="card shadow border-0 mb-4">
    <div class="card-header bg-white font-weight-bold text-primary">
        <i class="fas fa-rocket mr-2"></i> Fast Enterprise Actions
    </div>
    <div class="card-body">
        <div class="row text-center">
            <?php if (in_array($user_role, ['admin', 'doctor', 'receptionist'])): ?>
            <div class="col-md-3 mb-2">
                <a href="patients.php" class="btn btn-outline-primary btn-block p-3">
                    <i class="fas fa-user-plus fa-2x d-block mb-2"></i> Patient Directory
                </a>
            </div>
            <div class="col-md-3 mb-2">
                <a href="add_appointment.php" class="btn btn-outline-success btn-block p-3">
                    <i class="fas fa-calendar-plus fa-2x d-block mb-2"></i> Book Appointment
                </a>
            </div>
            <?php endif; ?>

            <?php if (in_array($user_role, ['admin', 'pharmacist'])): ?>
            <div class="col-md-3 mb-2">
                <a href="dispenser.php" class="btn btn-outline-warning btn-block p-3">
                    <i class="fas fa-pills fa-2x d-block mb-2"></i> Dispense Pharmacy
                </a>
            </div>
            <?php endif; ?>

            <?php if (in_array($user_role, ['admin', 'doctor', 'lab_tech'])): ?>
            <div class="col-md-3 mb-2">
                <a href="dicom_viewer.php" class="btn btn-outline-danger btn-block p-3">
                    <i class="fas fa-microscope fa-2x d-block mb-2"></i> DICOM Radiology
                </a>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>