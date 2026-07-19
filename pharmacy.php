<?php
$page_title = 'Pharmacy Store Inventory';
require_once 'includes/header.php';
require_role(['admin', 'pharmacist']);

$pdo = get_db_pdo();

if (isset($_POST['add_medicine'])) {
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        set_flash('Invalid security token.', 'danger');
    } else {
        $name = trim($_POST['med_name'] ?? '');
        $type = $_POST['med_type'] ?? 'Tablet';
        $stock = (int)($_POST['stock_qty'] ?? 0);
        $price = (float)($_POST['price_per_unit'] ?? 0.00);
        $expiry = $_POST['expiry_date'] ?? '';

        if (empty($name) || $stock < 0 || $price < 0 || empty($expiry)) {
            set_flash('Please fill in valid medicine parameters.', 'danger');
        } else {
            $stmt = $pdo->prepare("INSERT INTO medicines (name, type, stock_qty, price_per_unit, expiry_date) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$name, $type, $stock, $price, $expiry]);
            set_flash("Medicine '$name' added to stock successfully!");
            header('Location: pharmacy.php');
            exit();
        }
    }
}

if (isset($_POST['adjust_stock'])) {
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        set_flash('Invalid security token.', 'danger');
    } else {
        $med_id = (int)$_POST['medicine_id'];
        $qty = (int)$_POST['qty_change'];
        
        $stmt = $pdo->prepare("UPDATE medicines SET stock_qty = stock_qty + ? WHERE id = ?");
        $stmt->execute([$qty, $med_id]);
        set_flash("Stock quantity updated successfully.");
        header('Location: pharmacy.php');
        exit();
    }
}

$medicines = $pdo->query("SELECT * FROM medicines ORDER BY name ASC")->fetchAll();
?>

<div class="row mb-4 mt-3">
    <div class="col-md-6">
        <h3 class="fw-bold"><i class="fas fa-pills text-primary me-2"></i>Pharmacy Store Inventory</h3>
    </div>
    <div class="col-md-6 text-end d-flex gap-2 justify-content-end">
        <a href="dispenser.php" class="btn btn-outline-primary rounded-pill px-4">
            <i class="fas fa-file-prescription me-2"></i>Prescription Dispenser
        </a>
        <button class="btn btn-primary rounded-pill px-4" data-bs-toggle="modal" data-bs-target="#medicineModal">
            <i class="fas fa-plus me-2"></i>Add Medicine Stock
        </button>
    </div>
</div>

<div class="card border-0 shadow-sm rounded-4">
    <div class="card-body p-4">
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead>
                    <tr>
                        <th class="ps-4">Medicine Name</th>
                        <th>Type</th>
                        <th>Stock Available</th>
                        <th>Unit Price ($)</th>
                        <th>Expiry Date</th>
                        <th>Status Status</th>
                        <th class="text-end pe-4">Stock Restock</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($medicines)): ?>
                        <tr>
                            <td colspan="7" class="text-center py-5 text-muted small">No medicines registered in inventory. Click "Add Medicine Stock" to build your database.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($medicines as $med): ?>
                            <?php
                            $is_low = $med['stock_qty'] < 50;
                            $today = new DateTime();
                            $exp = new DateTime($med['expiry_date']);
                            $diff = $today->diff($exp);
                            $is_expired = $today >= $exp;
                            $is_near_expiry = !$is_expired && $diff->days < 180; // less than 6 months
                            ?>
                            <tr style="border-color: <?= $is_expired ? '#fee2e2' : '#f1f5f9' ?>;">
                                <td class="ps-4">
                                    <div class="fw-bold <?= $is_expired ? 'text-danger' : 'text-dark' ?>"><?= esc($med['name']) ?></div>
                                    <small class="text-muted">#MED-<?= $med['id'] ?></small>
                                </td>
                                <td>
                                    <span class="badge bg-secondary-subtle text-secondary rounded-pill px-3 py-1"><?= esc($med['type']) ?></span>
                                </td>
                                <td>
                                    <span class="fw-bold <?= $is_low ? 'text-warning fs-6' : 'text-dark' ?>"><?= $med['stock_qty'] ?> units</span>
                                    <?php if ($is_low): ?>
                                        <i class="fas fa-exclamation-triangle text-warning ms-1" title="Low stock warning!"></i>
                                    <?php endif; ?>
                                </td>
                                <td>$<?= number_format($med['price_per_unit'], 2) ?></td>
                                <td>
                                    <span class="fw-semibold <?= $is_expired ? 'text-danger' : ($is_near_expiry ? 'text-warning' : 'text-muted') ?>">
                                        <?= date('M j, Y', strtotime($med['expiry_date'])) ?>
                                    </span>
                                </td>
                                <td>
                                    <?php if ($is_expired): ?>
                                        <span class="badge bg-danger px-3 py-2 rounded-pill" style="font-size:10px;">EXPIRED</span>
                                    <?php elseif ($is_near_expiry): ?>
                                        <span class="badge bg-warning px-3 py-2 rounded-pill" style="font-size:10px;">Expiring Soon</span>
                                    <?php else: ?>
                                        <span class="badge bg-success-subtle text-success px-3 py-2 rounded-pill" style="font-size:10px;">Good Stock</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-end pe-4">
                                    <button class="btn btn-sm btn-light border rounded-pill px-3 adjust-stock-btn"
                                            data-id="<?= $med['id'] ?>"
                                            data-name="<?= esc($med['name']) ?>"
                                            data-bs-toggle="modal" data-bs-target="#stockAdjustModal">
                                        <i class="fas fa-plus-minus me-1"></i> Quick Edit
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="modal fade" id="medicineModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow rounded-4">
            <div class="modal-header border-bottom-0 p-4 pb-0">
                <h5 class="modal-title fw-bold">Register Medicine Stock</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
                <div class="modal-body p-4">
                    <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">
                    
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Medicine / Generic Name</label>
                        <input type="text" name="med_name" class="form-control rounded-3" placeholder="e.g. Paracetamol 500mg" required>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label small fw-bold">Medicine Type</label>
                            <select name="med_type" class="form-select rounded-3">
                                <option value="Tablet">Tablet</option>
                                <option value="Capsule">Capsule</option>
                                <option value="Syrup">Syrup</option>
                                <option value="Injection">Injection</option>
                                <option value="Suspension">Suspension</option>
                                <option value="Ointment">Ointment</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label small fw-bold">Expiry Date</label>
                            <input type="date" name="expiry_date" class="form-control rounded-3" required>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-0">
                            <label class="form-label small fw-bold">Initial Stock Qty</label>
                            <input type="number" name="stock_qty" class="form-control rounded-3" value="100" min="0" required>
                        </div>
                        <div class="col-md-6 mb-0">
                            <label class="form-label small fw-bold">Unit Sale Price ($)</label>
                            <input type="number" step="0.01" name="price_per_unit" class="form-control rounded-3" value="1.00" min="0" required>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-top-0 p-4 pt-0">
                    <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" name="add_medicine" class="btn btn-primary rounded-pill px-4">Register Drug</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="stockAdjustModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow rounded-4">
            <div class="modal-header border-bottom-0 p-4 pb-0">
                <h5 class="modal-title fw-bold">Quick Stock Correction</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
                <div class="modal-body p-4">
                    <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">
                    <input type="hidden" name="medicine_id" id="modal_med_id">
                    
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Selected Drug</label>
                        <input type="text" id="modal_med_name" class="form-control bg-light" disabled>
                    </div>

                    <div class="mb-0">
                        <label class="form-label small fw-bold">Adjust Quantity (use negative value to subtract)</label>
                        <input type="number" name="qty_change" class="form-control rounded-3" placeholder="e.g. +50 or -20" required>
                    </div>
                </div>
                <div class="modal-footer border-top-0 p-4 pt-0">
                    <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" name="adjust_stock" class="btn btn-primary rounded-pill px-4">Confirm Adjustment</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const btn = document.querySelectorAll('.adjust-stock-btn');
    btn.forEach(el => {
        el.addEventListener('click', function() {
            document.getElementById('modal_med_id').value = this.dataset.id;
            document.getElementById('modal_med_name').value = this.dataset.name;
        });
    });
});
</script>

<?php require_once 'includes/footer.php'; ?>
