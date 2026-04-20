<?php
$page_title = 'Appointment Management';
require_once 'includes/header.php';
require_role(['admin', 'receptionist', 'doctor']);

$pdo = get_db_pdo();
$user_role = $_SESSION['role'];
$user_id = $_SESSION['user_id'];

// Handle Status Updates
if (isset($_POST['update_status'])) {
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        set_flash('Invalid security token.', 'danger');
    } else {
        $id = (int)$_POST['id'];
        $new_status = $_POST['status'];
        
        // Get old status for audit
        $stmt = $pdo->prepare("SELECT status FROM appointments WHERE id = ?");
        $stmt->execute([$id]);
        $old_status = $stmt->fetchColumn();

        $stmt = $pdo->prepare("UPDATE appointments SET status = ?, updated_by = ? WHERE id = ?");
        if ($stmt->execute([$new_status, $user_id, $id])) {
            audit_log($pdo, 'UPDATE_STATUS', 'appointments', $id, ['status' => $old_status], ['status' => $new_status]);
            set_flash('Appointment status updated to ' . ucfirst($new_status));
        }
    }
    header('Location: ' . $_SERVER['PHP_SELF']);
    exit();
}

// Fetch Appointments
$query = "SELECT a.*, p.name as p_name, p.phone as p_phone, d.name as d_name 
          FROM appointments a 
          JOIN patients p ON a.patient_id = p.id 
          JOIN doctors d ON a.doctor_id = d.id";

$params = [];
if ($user_role === 'doctor') {
    $doctor_id = get_doctor_id($pdo);
    $query .= " WHERE a.doctor_id = ?";
    $params[] = $doctor_id;
}

$query .= " ORDER BY a.app_date DESC, a.app_time DESC";
$stmt = $pdo->prepare($query);
$stmt->execute($params);
$appointments = $stmt->fetchAll();
?>

<div class="row mb-4">
    <div class="col-md-6">
        <h3 class="fw-bold"><i class="fas fa-calendar-alt text-primary me-2"></i>Appointments</h3>
    </div>
    <?php if ($user_role !== 'doctor'): ?>
    <div class="col-md-6 text-end">
        <a href="add_appointment.php" class="btn btn-primary rounded-pill px-4">
            <i class="fas fa-plus me-2"></i>Book New
        </a>
    </div>
    <?php endif; ?>
</div>

<div class="card border-0 shadow-sm rounded-4">
    <div class="card-body p-4">
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead class="bg-light">
                    <tr>
                        <th class="ps-4">Patient</th>
                        <th>Doctor</th>
                        <th>Date & Time</th>
                        <th>Status</th>
                        <th class="text-end pe-4">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($appointments)): ?>
                        <tr>
                            <td colspan="5" class="text-center py-5 text-muted">No appointments scheduled.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($appointments as $a): ?>
                            <tr>
                                <td class="ps-4">
                                    <div class="fw-bold"><?= esc($a['p_name']) ?></div>
                                    <small class="text-muted"><?= esc($a['p_phone']) ?></small>
                                </td>
                                <td>
                                    <div class="fw-medium">Dr. <?= esc($a['d_name']) ?></div>
                                </td>
                                <td>
                                    <div><?= date('M j, Y', strtotime($a['app_date'])) ?></div>
                                    <small class="text-muted"><?= date('h:i A', strtotime($a['app_time'])) ?></small>
                                </td>
                                <td>
                                    <?php
                                    $badge = match ($a['status']) {
                                        'pending' => 'warning',
                                        'confirmed' => 'success',
                                        'completed' => 'info',
                                        'cancelled' => 'danger',
                                        default => 'secondary'
                                    };
                                    ?>
                                    <span class="badge bg-<?= $badge ?> bg-opacity-10 text-<?= $badge ?> px-3 py-2">
                                        <?= ucfirst($a['status']) ?>
                                    </span>
                                </td>
                                <td class="text-end pe-4">
                                    <div class="dropdown d-inline-block">
                                        <button class="btn btn-sm btn-light dropdown-toggle rounded-pill px-3" type="button" data-bs-toggle="dropdown">
                                            Status
                                        </button>
                                        <ul class="dropdown-menu dropdown-menu-end shadow border-0">
                                            <li><form method="POST"><input type="hidden" name="id" value="<?= $a['id'] ?>"><input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>"><button type="submit" name="update_status" value="1" class="dropdown-item"><input type="hidden" name="status" value="pending">Pending</button></form></li>
                                            <li><form method="POST"><input type="hidden" name="id" value="<?= $a['id'] ?>"><input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>"><button type="submit" name="update_status" value="1" class="dropdown-item text-success"><input type="hidden" name="status" value="confirmed">Confirm</button></form></li>
                                            <li><form method="POST"><input type="hidden" name="id" value="<?= $a['id'] ?>"><input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>"><button type="submit" name="update_status" value="1" class="dropdown-item text-info"><input type="hidden" name="status" value="completed">Mark Completed</button></form></li>
                                            <li><hr class="dropdown-divider"></li>
                                            <li><form method="POST"><input type="hidden" name="id" value="<?= $a['id'] ?>"><input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>"><button type="submit" name="update_status" value="1" class="dropdown-item text-danger"><input type="hidden" name="status" value="cancelled">Cancel</button></form></li>
                                        </ul>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
