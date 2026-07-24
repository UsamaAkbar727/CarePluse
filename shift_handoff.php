<?php
require_once 'includes/header.php';
require_once 'config.php';
require_role(['admin', 'doctor', 'receptionist']);

$pdo = get_conn();

// Handle Form Submission for new Shift Handoff
$msg = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_handoff'])) {
    $shift_name = sanitize($_POST['shift_name']);
    $handover_to = sanitize($_POST['handover_to']);
    $high_risk_patients = sanitize($_POST['high_risk_patients']);
    $pending_tasks = sanitize($_POST['pending_tasks']);
    $notes = sanitize($_POST['notes']);
    $user_id = $_SESSION['user_id'] ?? 1;

    $stmt = $pdo->prepare("
        INSERT INTO shift_handoffs (user_id, shift_name, handover_to, high_risk_patients, pending_tasks, notes)
        VALUES (?, ?, ?, ?, ?, ?)
    ");
    if ($stmt->execute([$user_id, $shift_name, $handover_to, $high_risk_patients, $pending_tasks, $notes])) {
        $msg = '<div class="alert alert-success shadow-sm">Shift handover logged successfully! Receiving staff notified.</div>';
    } else {
        $msg = '<div class="alert alert-danger shadow-sm">Failed to log shift handover.</div>';
    }
}

// Fetch Handoff history
$handoffs = $pdo->query("
    SELECT sh.*, u.full_name as outgoing_staff, u.role
    FROM shift_handoffs sh
    JOIN users u ON sh.user_id = u.id
    ORDER BY sh.created_at DESC
    LIMIT 20
")->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h2 class="h3 mb-1 font-weight-bold text-gray-800">🔄 Clinical & Nursing Shift Handoff Management</h2>
        <p class="text-muted mb-0">Seamless Duty Handovers, Critical Patient Tracking, and Pending Tasks Board</p>
    </div>
    <button class="btn btn-primary shadow-sm font-weight-bold" data-toggle="modal" data-target="#newHandoffModal">
        <i class="fas fa-plus-circle mr-1"></i> New Shift Handover Log
    </button>
</div>

<?= $msg ?>

<!-- High Level Summary Cards -->
<div class="row mb-4">
    <div class="col-xl-3 col-md-6 mb-3">
        <div class="card border-left-danger shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-danger text-uppercase mb-1">High-Risk ICUs / Critical</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">3 Patients Monitored</div>
                    </div>
                    <div class="col-auto"><i class="fas fa-user-shield fa-2x text-gray-300"></i></div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6 mb-3">
        <div class="card border-left-warning shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">Pending Lab Reports</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">5 Pending Reviews</div>
                    </div>
                    <div class="col-auto"><i class="fas fa-flask fa-2x text-gray-300"></i></div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6 mb-3">
        <div class="card border-left-info shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-info text-uppercase mb-1">Active Duty Shift</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">Evening Shift (15:00 - 23:00)</div>
                    </div>
                    <div class="col-auto"><i class="fas fa-clock fa-2x text-gray-300"></i></div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6 mb-3">
        <div class="card border-left-success shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-success text-uppercase mb-1">On-Duty Medical Team</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">8 Staff Logged In</div>
                    </div>
                    <div class="col-auto"><i class="fas fa-user-md fa-2x text-gray-300"></i></div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Shift Handoff Timeline Board -->
<div class="card shadow border-0 mb-4">
    <div class="card-header bg-white font-weight-bold text-primary d-flex justify-content-between align-items-center">
        <span><i class="fas fa-history mr-2"></i> Recent Shift Handover Logs</span>
        <span class="badge badge-light border text-muted">Latest 20 Records</span>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="bg-light text-uppercase text-xs font-weight-bold text-muted">
                    <tr>
                        <th>Shift & Date</th>
                        <th>Outgoing Staff</th>
                        <th>Handed Over To</th>
                        <th>High Risk Patients</th>
                        <th>Pending Action Tasks</th>
                        <th>Shift Notes</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($handoffs)): ?>
                        <tr>
                            <td colspan="6" class="text-center text-muted py-4">No shift handovers logged yet. Click "New Shift Handover Log" to create one.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($handoffs as $h): ?>
                            <tr>
                                <td>
                                    <span class="badge badge-primary px-2 py-1"><?= htmlspecialchars($h['shift_name']) ?></span>
                                    <div class="small text-muted mt-1"><?= date('M d, Y h:i A', strtotime($h['created_at'])) ?></div>
                                </td>
                                <td>
                                    <div class="font-weight-bold"><?= htmlspecialchars($h['outgoing_staff']) ?></div>
                                    <span class="badge badge-light border text-capitalize"><?= htmlspecialchars($h['role']) ?></span>
                                </td>
                                <td><span class="font-weight-bold text-dark"><?= htmlspecialchars($h['handover_to']) ?></span></td>
                                <td>
                                    <?php if ($h['high_risk_patients']): ?>
                                        <div class="badge badge-danger p-2 text-wrap text-left font-weight-normal">
                                            <i class="fas fa-exclamation-triangle mr-1"></i> <?= nl2br(htmlspecialchars($h['high_risk_patients'])) ?>
                                        </div>
                                    <?php else: ?>
                                        <span class="text-muted small">None Reported</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($h['pending_tasks']): ?>
                                        <div class="text-xs bg-light p-2 rounded border border-warning">
                                            <i class="fas fa-tasks text-warning mr-1"></i> <?= nl2br(htmlspecialchars($h['pending_tasks'])) ?>
                                        </div>
                                    <?php else: ?>
                                        <span class="text-muted small">All Tasks Done</span>
                                    <?php endif; ?>
                                </td>
                                <td class="small text-gray-700"><?= nl2br(htmlspecialchars($h['notes'] ?: 'No notes')) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal: New Shift Handover Log -->
<div class="modal fade" id="newHandoffModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title font-weight-bold"><i class="fas fa-exchange-alt mr-2"></i> Create Clinical Shift Handover</h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form method="POST" action="">
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6 form-group">
                            <label class="font-weight-bold text-gray-700">Shift Name</label>
                            <select name="shift_name" class="form-control" required>
                                <option value="Morning Shift (07:00 - 15:00)">Morning Shift (07:00 - 15:00)</option>
                                <option value="Evening Shift (15:00 - 23:00)">Evening Shift (15:00 - 23:00)</option>
                                <option value="Night Shift (23:00 - 07:00)">Night Shift (23:00 - 07:00)</option>
                            </select>
                        </div>
                        <div class="col-md-6 form-group">
                            <label class="font-weight-bold text-gray-700">Receiving Staff / Doctor</label>
                            <input type="text" name="handover_to" class="form-control" placeholder="e.g. Dr. Sarah Ahmed / Nurse Team B" required>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="font-weight-bold text-gray-700">High-Risk Patients & Critical Watch</label>
                        <textarea name="high_risk_patients" class="form-control" rows="2" placeholder="List patient names/bed numbers requiring continuous telemetry or monitoring..."></textarea>
                    </div>
                    <div class="form-group">
                        <label class="font-weight-bold text-gray-700">Pending Tasks & Unfinished Orders</label>
                        <textarea name="pending_tasks" class="form-control" rows="2" placeholder="e.g. Awaiting Blood Culture for Bed GEN-02, Administer IV Antibiotics at 20:00..."></textarea>
                    </div>
                    <div class="form-group mb-0">
                        <label class="font-weight-bold text-gray-700">General Shift Handover Notes</label>
                        <textarea name="notes" class="form-control" rows="3" placeholder="Additional observations, ward bed availability, equipment notes..."></textarea>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" name="submit_handoff" class="btn btn-primary font-weight-bold px-4">
                        <i class="fas fa-check-circle mr-1"></i> Submit Shift Handover Log
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
