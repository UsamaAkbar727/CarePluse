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

<!-- AI Predictive Alert Banner (Premium Styling & Contrast Fix) -->
<style>
    .ai-insight-banner {
        background: linear-gradient(135deg, #4f46e5 0%, #3b82f6 100%);
        border-radius: 16px;
        padding: 20px 24px;
        box-shadow: 0 10px 25px -5px rgba(79, 70, 229, 0.3), 0 8px 10px -6px rgba(79, 70, 229, 0.2);
        border: none;
        color: #ffffff;
        position: relative;
        overflow: hidden;
        margin-bottom: 28px;
    }
    .ai-insight-banner::before {
        content: '';
        position: absolute;
        top: -50%;
        right: -10%;
        width: 300px;
        height: 300px;
        background: radial-gradient(circle, rgba(255,255,255,0.15) 0%, rgba(255,255,255,0) 70%);
        border-radius: 50%;
        pointer-events: none;
    }
    .ai-insight-icon-container {
        width: 52px;
        height: 52px;
        background: rgba(255, 255, 255, 0.15);
        backdrop-filter: blur(8px);
        border: 1px solid rgba(255, 255, 255, 0.25);
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-right: 18px;
        color: #ffffff;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
        transition: transform 0.3s ease;
    }
    .ai-insight-banner:hover .ai-insight-icon-container {
        transform: rotate(15deg) scale(1.05);
    }
    .ai-insight-badge {
        background: rgba(255, 255, 255, 0.2);
        backdrop-filter: blur(8px);
        border: 1px solid rgba(255, 255, 255, 0.3);
        color: #ffffff;
        padding: 8px 16px;
        border-radius: 99px;
        font-weight: 700;
        font-size: 13.5px;
        letter-spacing: 0.2px;
        box-shadow: 0 4px 10px rgba(0, 0, 0, 0.05);
    }
    
    /* Dark mode adjustments to maintain premium look */
    body.dark-mode .ai-insight-banner {
        background: linear-gradient(135deg, #3730a3 0%, #1d4ed8 100%);
        box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.3);
    }
</style>
<div class="ai-insight-banner d-flex align-items-center justify-content-between flex-wrap gap-3">
    <div class="d-flex align-items-center">
        <div class="ai-insight-icon-container">
            <i class="fas fa-brain fa-lg"></i>
        </div>
        <div>
            <h6 class="font-weight-bold mb-1" style="font-size: 16px; letter-spacing: -0.2px;">AI Clinical Intelligence Insight</h6>
            <div style="font-size: 14px; color: rgba(255, 255, 255, 0.85); font-weight: 500;"><?= htmlspecialchars($predictive['ai_insight']) ?></div>
        </div>
    </div>
    <div>
        <span class="ai-insight-badge">
            <i class="fas fa-chart-line mr-1"></i> Occupancy Forecast: <?= $predictive['projected_occupancy_peak'] ?>%
        </span>
    </div>
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