<?php
$page_title = 'Prescription Drug Dispenser';
require_once 'includes/header.php';
require_role(['admin', 'pharmacist']);

$pdo = get_db_pdo();

if (isset($_POST['dispense_meds'])) {
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        set_flash('Invalid security token.', 'danger');
    } else {
        $prescription_id = (int)$_POST['prescription_id'];
        $med_ids = $_POST['med_ids'] ?? [];
        $quantities = $_POST['quantities'] ?? [];
        
        if ($prescription_id <= 0 || empty($med_ids)) {
            set_flash('Please select drugs to dispense.', 'danger');
        } else {

            $stmt = $pdo->prepare("
                SELECT pr.*, a.patient_id
                FROM prescriptions pr
                JOIN appointments a ON pr.appointment_id = a.id
                WHERE pr.id = ?
            ");
            $stmt->execute([$prescription_id]);
            $pres = $stmt->fetch();
            
            if ($pres) {
                $appt_id = $pres['appointment_id'];
                $patient_id = $pres['patient_id'];

                $inv_stmt = $pdo->prepare("SELECT id FROM invoices WHERE appointment_id = ?");
                $inv_stmt->execute([$appt_id]);
                $invoice_id = $inv_stmt->fetchColumn();
                
                if (!$invoice_id) {

                    $ins_inv = $pdo->prepare("INSERT INTO invoices (appointment_id, patient_id, total_amount, tax, net_amount, status) VALUES (?, ?, 0, 0, 0, 'unpaid')");
                    $ins_inv->execute([$appt_id, $patient_id]);
                    $invoice_id = $pdo->lastInsertId();
                }
                
                $dispensed_count = 0;
                $dispensed_total = 0.00;
                
                // Process each drug row
                for ($i = 0; $i < count($med_ids); $i++) {
                    $med_id = (int)$med_ids[$i];
                    $qty = (int)$quantities[$i];
                    
                    if ($med_id > 0 && $qty > 0) {

                        $med_stmt = $pdo->prepare("SELECT name, stock_qty, price_per_unit FROM medicines WHERE id = ?");
                        $med_stmt->execute([$med_id]);
                        $medicine = $med_stmt->fetch();
                        
                        if ($medicine && $medicine['stock_qty'] >= $qty) {
                            $price_charged = $qty * $medicine['price_per_unit'];
                            $dispensed_total += $price_charged;

                            $up_stock = $pdo->prepare("UPDATE medicines SET stock_qty = stock_qty - ? WHERE id = ?");
                            $up_stock->execute([$qty, $med_id]);

                            $ins_item = $pdo->prepare("INSERT INTO prescription_items (prescription_id, medicine_id, quantity, price_charged, status) VALUES (?, ?, ?, ?, 'dispensed')");
                            $ins_item->execute([$prescription_id, $med_id, $qty, $medicine['price_per_unit']]);

                            $inv_item = $pdo->prepare("INSERT INTO invoice_items (invoice_id, item_description, quantity, unit_price, total_price) VALUES (?, ?, ?, ?, ?)");
                            $desc = $medicine['name'] . " (Pharmacy Dispensation)";
                            $inv_item->execute([$invoice_id, $desc, $qty, $medicine['price_per_unit'], $price_charged]);
                            
                            $dispensed_count++;
                        }
                    }
                }
                
                if ($dispensed_count > 0) {

                    $sum_stmt = $pdo->prepare("SELECT SUM(total_price) FROM invoice_items WHERE invoice_id = ?");
                    $sum_stmt->execute([$invoice_id]);
                    $gross = $sum_stmt->fetchColumn() ?: 0.00;

                    $inv_set = $pdo->prepare("SELECT discount, tax, total_amount FROM invoices WHERE id = ?");
                    $inv_set->execute([$invoice_id]);
                    $inv_data = $inv_set->fetch();
                    $tax_rate = 5;
                    if ($inv_data && $inv_data['total_amount'] > 0) {
                        $tax_rate = ($inv_data['tax'] / $inv_data['total_amount']) * 100;
                    }
                    $tax = $gross * ($tax_rate / 100);
                    $discount = $inv_data ? (float)$inv_data['discount'] : 0;
                    $net = ($gross + $tax) - $discount;
                    if ($net < 0) $net = 0;
                    
                    $up_inv = $pdo->prepare("UPDATE invoices SET total_amount = ?, tax = ?, net_amount = ? WHERE id = ?");
                    $up_inv->execute([$gross, $tax, $net, $invoice_id]);

                    audit_log($pdo, 'DISPENSE_PRESCRIPTION', 'prescription_items', $prescription_id, null, ['drugs_count' => $dispensed_count, 'total_billed' => $dispensed_total]);
                    
                    set_flash("Dispensed $dispensed_count drugs! Billing total of $" . number_format($dispensed_total, 2) . " has been posted to invoice #INV-$invoice_id.");
                } else {
                    set_flash("No drugs dispensed. Check stock availability.", "warning");
                }
                
                header('Location: dispenser.php');
                exit();
            }
        }
    }
}

// (A prescription is pending dispensing if it has no rows in prescription_items or we want to display prescriptions)
$prescriptions = $pdo->query("
    SELECT pr.*, p.name as patient_name, p.phone as patient_phone, d.name as doctor_name, a.app_date
    FROM prescriptions pr
    JOIN patients p ON pr.patient_id = p.id
    JOIN doctors d ON pr.doctor_id = d.id
    JOIN appointments a ON pr.appointment_id = a.id
    ORDER BY pr.created_at DESC
")->fetchAll();

$medicines_list = $pdo->query("SELECT id, name, type, stock_qty, price_per_unit FROM medicines WHERE stock_qty > 0 ORDER BY name ASC")->fetchAll();
?>

<div class="row mb-4 mt-3">
    <div class="col-md-6">
        <h3 class="fw-bold"><i class="fas fa-file-prescription text-primary me-2"></i>Prescription Drug Dispenser</h3>
    </div>
    <div class="col-md-6 text-end">
        <a href="pharmacy.php" class="btn btn-outline-secondary rounded-pill px-4">
            <i class="fas fa-arrow-left me-2"></i>Back to Inventory
        </a>
    </div>
</div>

<div class="row g-4">

    <div class="col-lg-6 col-12">
        <div class="card border-0 shadow-sm rounded-4 h-100 bg-white">
            <div class="card-header bg-white border-0 pt-4 px-4 pb-0">
                <h5 class="fw-bold text-dark mb-0">Incoming Prescription Queue</h5>
                <p class="text-muted small">Select a clinical dossier below to prepare checkout dispensing.</p>
            </div>
            
            <div class="card-body p-4" style="max-height: 550px; overflow-y: auto;">
                <?php if (empty($prescriptions)): ?>
                    <div class="text-center py-5 text-muted small">
                        <i class="fas fa-prescription-bottle fa-2x mb-3 text-muted"></i>
                        <p class="mb-0">No active prescriptions available.</p>
                    </div>
                <?php else: ?>
                    <div class="list-group list-group-flush gap-3">
                        <?php foreach ($prescriptions as $pres): ?>
                            <?php

                            $disp_stmt = $pdo->prepare("SELECT COUNT(*) FROM prescription_items WHERE prescription_id = ?");
                            $disp_stmt->execute([$pres['id']]);
                            $is_dispensed = $disp_stmt->fetchColumn() > 0;
                            ?>
                            <div class="list-group-item p-3 rounded-4 border bg-light position-relative select-prescription-btn" 
                                 style="cursor: pointer; transition: all 0.2s;"
                                 data-id="<?= $pres['id'] ?>"
                                 data-patient="<?= esc($pres['patient_name']) ?>"
                                 data-doctor="<?= esc(format_doctor_name($pres['doctor_name'])) ?>"
                                 data-meds="<?= esc($pres['medications']) ?>"
                                 data-instructions="<?= esc($pres['instructions']) ?>">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <span class="badge bg-primary-subtle text-primary rounded-pill px-3" style="font-size:10px;">Prescription #<?= $pres['id'] ?></span>
                                    <span class="badge bg-<?= $is_dispensed ? 'success' : 'warning' ?>-subtle text-<?= $is_dispensed ? 'success' : 'warning' ?> rounded-pill px-3" style="font-size:10px;"><?= $is_dispensed ? 'Dispensed' : 'Pending' ?></span>
                                </div>
                                <h6 class="fw-bold mb-1 text-dark"><?= esc($pres['patient_name']) ?></h6>
                                <p class="text-muted small mb-2"><i class="fas fa-user-md me-1"></i>Dr. <?= esc(format_doctor_name($pres['doctor_name'])) ?> | <i class="far fa-calendar-alt me-1"></i><?= date('M j, Y', strtotime($pres['app_date'])) ?></p>
                                <div class="bg-white p-2 rounded border small text-muted text-truncate" style="font-family: monospace; font-size:11px;">
                                    <?= esc($pres['medications']) ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="col-lg-6 col-12">
        <div class="card border-0 shadow-sm rounded-4 h-100 bg-white" id="dispenser_panel" style="display: none;">
            <div class="card-header bg-primary text-white p-4">
                <h5 class="fw-bold mb-0 text-white">Drug Checkout Allocation</h5>
                <p class="mb-0 text-white-50 small">Match generic drug rows to matching store items.</p>
            </div>
            
            <form method="POST">
                <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">
                <input type="hidden" name="prescription_id" id="checkout_pres_id">
                
                <div class="card-body p-4 p-md-5">
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Attending Doctor Note</label>
                        <div class="bg-light p-3 rounded border small text-dark" style="font-family: monospace; white-space: pre-wrap; font-size:12px;" id="doctor_prescription_text"></div>
                    </div>
                    
                    <div class="mb-4">
                        <label class="form-label small fw-bold">Physician Dosage Remarks</label>
                        <p class="text-muted small mb-0" id="doctor_instruction_text"></p>
                    </div>

                    <h6 class="fw-bold text-dark border-bottom pb-2 mb-3"><i class="fas fa-capsules me-2"></i>Map Store Stock</h6>
                    <div id="dispensation_rows_container">
                        <!-- Dispensation drug selector row -->
                        <div class="row g-2 mb-3 dispensation-row align-items-end">
                            <div class="col-7">
                                <label class="form-label small text-muted mb-1">Select Stock Medicine</label>
                                <select name="med_ids[]" class="form-select rounded-3">
                                    <option value="" disabled selected>Choose medicine...</option>
                                    <?php foreach ($medicines_list as $m): ?>
                                        <option value="<?= $m['id'] ?>">
                                            <?= esc($m['name']) ?> (<?= esc($m['type']) ?>) - $<?= number_format($m['price_per_unit'], 2) ?> [Stock: <?= $m['stock_qty'] ?>]
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-3">
                                <label class="form-label small text-muted mb-1">Qty</label>
                                <input type="number" name="quantities[]" class="form-control rounded-3" value="1" min="1">
                            </div>
                            <div class="col-2">
                                <button type="button" class="btn btn-outline-danger w-100 rounded-3 remove-row-btn"><i class="fas fa-trash-alt"></i></button>
                            </div>
                        </div>
                    </div>

                    <button type="button" class="btn btn-xs btn-outline-primary rounded-pill px-3 mt-1 small" id="add_drug_row_btn">
                        <i class="fas fa-plus me-1"></i> Add Another Drug
                    </button>
                </div>

                <div class="card-footer bg-white border-0 p-4 pt-0">
                    <button type="submit" name="dispense_meds" class="btn btn-success btn-lg w-100 rounded-pill py-3 fw-bold">
                        <i class="fas fa-check-double me-2"></i>Dispense Drugs & Auto-Bill Charges
                    </button>
                </div>
            </form>
        </div>

        <div class="card border-0 shadow-sm rounded-4 h-100 bg-white d-flex align-items-center justify-content-center text-center py-5 px-4" id="dispenser_empty_panel">
            <div>
                <div style="background: #f8fafc; width: 80px; height: 80px; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 20px;">
                    <i class="fas fa-file-prescription text-muted fa-2x"></i>
                </div>
                <h5 class="fw-bold text-dark">No Prescription Selected</h5>
                <p class="text-muted small">Select any patient prescription from the incoming list on the left to start mapping pharmacy inventory allocations.</p>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const list = document.querySelectorAll('.select-prescription-btn');
    const panel = document.getElementById('dispenser_panel');
    const emptyPanel = document.getElementById('dispenser_empty_panel');
    
    list.forEach(el => {
        el.addEventListener('click', function() {
            // Remove active style from others
            list.forEach(item => item.classList.remove('border-primary'));
            this.classList.add('border-primary');
            
            // Populate fields
            document.getElementById('checkout_pres_id').value = this.dataset.id;
            document.getElementById('doctor_prescription_text').textContent = this.dataset.meds;
            document.getElementById('doctor_instruction_text').textContent = this.dataset.instructions || 'None';
            
            // Toggle panels
            emptyPanel.style.display = 'none';
            panel.style.display = 'block';
        });
    });

    const container = document.getElementById('dispensation_rows_container');
    const addBtn = document.getElementById('add_drug_row_btn');
    
    addBtn.addEventListener('click', function() {
        const row = container.querySelector('.dispensation-row').cloneNode(true);
        // Clear value of cloned inputs
        row.querySelector('select').selectedIndex = 0;
        row.querySelector('input').value = 1;
        
        // Setup remove trigger
        row.querySelector('.remove-row-btn').addEventListener('click', function() {
            row.remove();
        });
        
        container.appendChild(row);
    });

    // Attach remove event for default row
    container.querySelector('.remove-row-btn').addEventListener('click', function() {
        if (container.querySelectorAll('.dispensation-row').length > 1) {
            this.closest('.dispensation-row').remove();
        } else {
            alert("At least one mapping row must remain active.");
        }
    });
});
</script>

<?php require_once 'includes/footer.php'; ?>
