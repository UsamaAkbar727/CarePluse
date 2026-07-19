<?php
$page_title = 'Wards & Beds Occupancy';
require_once 'includes/header.php';
require_role(['admin', 'receptionist']);

$pdo = get_db_pdo();

// Handle Add Ward
if (isset($_POST['add_ward'])) {
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        set_flash('Invalid security token.', 'danger');
    } else {
        $name = trim($_POST['ward_name'] ?? '');
        $type = $_POST['ward_type'] ?? 'General';
        $capacity = (int)($_POST['capacity'] ?? 4);

        if (empty($name) || $capacity <= 0) {
            set_flash('Please fill in valid ward parameters.', 'danger');
        } else {
            $stmt = $pdo->prepare("INSERT INTO wards (name, type, capacity) VALUES (?, ?, ?)");
            $stmt->execute([$name, $type, $capacity]);
            $ward_id = $pdo->lastInsertId();
            
            // Auto create beds for the ward
            $stmt_bed = $pdo->prepare("INSERT INTO beds (ward_id, bed_number, status) VALUES (?, ?, 'available')");
            for ($i = 1; $i <= $capacity; $i++) {
                $bed_num = strtoupper(substr($type, 0, 3)) . '-' . str_pad($i, 2, '0', STR_PAD_LEFT);
                $stmt_bed->execute([$ward_id, $bed_num]);
            }
            
            set_flash("Ward and $capacity beds created successfully!");
            header('Location: wards_beds.php');
            exit();
        }
    }
}

// Fetch all Wards
$wards = $pdo->query("SELECT * FROM wards ORDER BY name ASC")->fetchAll();

// Fetch beds with active patient admissions
$beds_data = $pdo->query("
    SELECT b.*, w.name as ward_name, w.type as ward_type, 
           ad.id as admission_id, p.name as patient_name, p.id as patient_id
    FROM beds b
    JOIN wards w ON b.ward_id = w.id
    LEFT JOIN admissions ad ON b.id = ad.bed_id AND ad.status = 'admitted'
    LEFT JOIN patients p ON ad.patient_id = p.id
    ORDER BY w.name ASC, b.bed_number ASC
")->fetchAll();

// Group beds by Ward ID
$ward_beds = [];
foreach ($beds_data as $bed) {
    $ward_beds[$bed['ward_id']][] = $bed;
}
?>

<div class="row mb-4 mt-3">
    <div class="col-md-6">
        <h3 class="fw-bold"><i class="fas fa-procedures text-primary me-2"></i>Wards & Beds Directory</h3>
    </div>
    <div class="col-md-6 text-end d-flex gap-2 justify-content-end">
        <button class="btn btn-outline-primary rounded-pill px-4" data-bs-toggle="modal" data-bs-target="#wardModal">
            <i class="fas fa-plus me-2"></i>Add New Ward
        </button>
        <a href="admissions.php" class="btn btn-primary rounded-pill px-4">
            <i class="fas fa-user-plus me-2"></i>Admit New Patient
        </a>
    </div>
</div>

<!-- Grid of Wards -->
<div class="row g-4">
    <?php if (empty($wards)): ?>
        <div class="col-12 text-center py-5 text-muted bg-white rounded-4 border">
            <i class="fas fa-procedures fa-3x mb-3 text-muted"></i>
            <h5>No wards created yet</h5>
            <p class="small">Click "Add New Ward" to set up rooms and bed allocations.</p>
        </div>
    <?php else: ?>
        <?php foreach ($wards as $w): ?>
            <?php
            $current_beds = $ward_beds[$w['id']] ?? [];
            $occupied_count = 0;
            foreach ($current_beds as $b) {
                if ($b['status'] === 'occupied') $occupied_count++;
            }
            $capacity = count($current_beds);
            $occupancy_rate = $capacity > 0 ? round(($occupied_count / $capacity) * 100) : 0;
            ?>
            <div class="col-lg-6 col-12">
                <div class="card border-0 shadow-sm rounded-4 h-100 bg-white">
                    <div class="card-header bg-white border-0 pt-4 px-4 pb-0 d-flex justify-content-between align-items-center">
                        <div>
                            <span class="badge bg-primary-subtle text-primary px-3 py-1 rounded-pill mb-1" style="font-size:11px;">
                                <?= esc($w['type']) ?> Ward
                            </span>
                            <h5 class="fw-bold mb-0 text-dark"><?= esc($w['name']) ?></h5>
                        </div>
                        <div class="text-end">
                            <span class="fw-bold text-dark" style="font-size: 14px;"><?= $occupied_count ?> / <?= $capacity ?> Occupied</span>
                            <div class="progress mt-1" style="height: 4px; width: 100px; border-radius: 2px;">
                                <div class="progress-bar bg-<?= $occupancy_rate > 75 ? 'danger' : ($occupancy_rate > 40 ? 'warning' : 'success') ?>" role="progressbar" style="width: <?= $occupancy_rate ?>%;"></div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="card-body p-4">
                        <div class="row g-3">
                            <?php foreach ($current_beds as $bed): ?>
                                <div class="col-6 col-md-4">
                                    <div class="p-3 rounded-4 border text-center relative bg-light" style="box-shadow: 0 4px 10px rgba(0,0,0,0.01); border-color: <?= $bed['status'] === 'occupied' ? '#fee2e2' : '#e2e8f0' ?> !important;">
                                        <div class="fs-4 mb-1 text-<?= $bed['status'] === 'occupied' ? 'danger' : 'success' ?>">
                                            <i class="fas fa-bed"></i>
                                        </div>
                                        <div class="fw-bold text-dark" style="font-size: 14px;"><?= esc($bed['bed_number']) ?></div>
                                        
                                        <?php if ($bed['status'] === 'occupied'): ?>
                                            <div class="text-truncate mt-2 text-danger small fw-semibold" title="<?= esc($bed['patient_name']) ?>">
                                                <?= esc($bed['patient_name']) ?>
                                            </div>
                                            <a href="admissions.php?discharge=<?= $bed['admission_id'] ?>&token=<?= generate_csrf_token() ?>" class="btn btn-xs btn-danger mt-2 py-1 px-3 w-100 rounded-pill small-btn" style="font-size:10px;">
                                                Discharge
                                            </a>
                                        <?php else: ?>
                                            <div class="text-muted mt-2 small" style="font-size: 11px;">Available</div>
                                            <a href="admissions.php?bed_id=<?= $bed['id'] ?>" class="btn btn-xs btn-outline-success mt-2 py-1 px-3 w-100 rounded-pill small-btn" style="font-size:10px;">
                                                Admit
                                            </a>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

<!-- Add Ward Modal -->
<div class="modal fade" id="wardModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow rounded-4">
            <div class="modal-header border-bottom-0 p-4 pb-0">
                <h5 class="modal-title fw-bold">Create Ward & Beds</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
                <div class="modal-body p-4">
                    <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">
                    
                    <div class="mb-3">
                        <label class="form-label fw-bold small">Ward / Room Name</label>
                        <input type="text" name="ward_name" class="form-control rounded-3" placeholder="e.g. Intensive Care Unit (ICU) A" required>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold small">Ward Type</label>
                            <select name="ward_type" class="form-select rounded-3">
                                <option value="General">General Ward</option>
                                <option value="ICU">ICU</option>
                                <option value="Private">Private Room</option>
                                <option value="Deluxe">Deluxe Room</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold small">Bed Capacity</label>
                            <input type="number" name="capacity" class="form-control rounded-3" value="4" min="1" max="12" required>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-top-0 p-4 pt-0">
                    <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" name="add_ward" class="btn btn-primary rounded-pill px-4">Create Ward</button>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
.small-btn {
    font-weight: 600;
    letter-spacing: 0.3px;
    border-width: 1.5px;
}
</style>

<?php require_once 'includes/footer.php'; ?>
