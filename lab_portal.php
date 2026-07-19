<?php
$page_title = 'Laboratory Diagnostics Portal';
require_once 'includes/header.php';
require_role(['admin', 'lab_tech']);

$pdo = get_db_pdo();

// Handle Report Results Submission
if (isset($_POST['submit_results'])) {
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        set_flash('Invalid security token.', 'danger');
    } else {
        $request_id = (int)$_POST['request_id'];
        $results = trim($_POST['result_details'] ?? '');
        
        if ($request_id <= 0 || empty($results)) {
            set_flash('Please fill in test results description.', 'danger');
        } else {
            // Fetch request details
            $stmt = $pdo->prepare("
                SELECT lr.*, lt.name as test_name, lt.cost, a.patient_id
                FROM lab_requests lr
                JOIN lab_tests lt ON lr.test_id = lt.id
                JOIN appointments a ON lr.appointment_id = a.id
                WHERE lr.id = ? AND lr.status = 'pending'
            ");
            $stmt->execute([$request_id]);
            $request = $stmt->fetch();
            
            if ($request) {
                $appt_id = $request['appointment_id'];
                $patient_id = $request['patient_id'];
                $test_name = $request['test_name'];
                $cost = $request['cost'];
                
                // 1. Update lab request status & results
                $up_stmt = $pdo->prepare("UPDATE lab_requests SET result_details = ?, status = 'completed' WHERE id = ?");
                $up_stmt->execute([$results, $request_id]);
                
                // 2. Fetch or create invoice for this appointment
                $inv_stmt = $pdo->prepare("SELECT id FROM invoices WHERE appointment_id = ?");
                $inv_stmt->execute([$appt_id]);
                $invoice_id = $inv_stmt->fetchColumn();
                
                if (!$invoice_id) {
                    // Create invoice
                    $tax = $cost * 0.05;
                    $net = $cost + $tax;
                    $ins_inv = $pdo->prepare("INSERT INTO invoices (appointment_id, patient_id, total_amount, tax, net_amount, status) VALUES (?, ?, ?, ?, ?, 'unpaid')");
                    $ins_inv->execute([$appt_id, $patient_id, $cost, $tax, $net]);
                    $invoice_id = $pdo->lastInsertId();
                } else {
                    // Update invoice totals by adding this test item
                    $inv_items_stmt = $pdo->prepare("INSERT INTO invoice_items (invoice_id, item_description, quantity, unit_price, total_price) VALUES (?, ?, 1, ?, ?)");
                    $inv_items_stmt->execute([$invoice_id, $test_name . " (Lab Diagnostics)", $cost, $cost]);
                    
                    // Recalculate totals
                    // Calculate gross sum
                    $sum_stmt = $pdo->prepare("SELECT SUM(total_price) FROM invoice_items WHERE invoice_id = ?");
                    $sum_stmt->execute([$invoice_id]);
                    $gross = $sum_stmt->fetchColumn() ?: 0.00;
                    
                    // Fetch existing settings
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
                }
                
                // If it was newly created invoice, insert the invoice line item
                $chk_items = $pdo->prepare("SELECT COUNT(*) FROM invoice_items WHERE invoice_id = ?");
                $chk_items->execute([$invoice_id]);
                if ($chk_items->fetchColumn() == 0) {
                    $inv_items_stmt = $pdo->prepare("INSERT INTO invoice_items (invoice_id, item_description, quantity, unit_price, total_price) VALUES (?, ?, 1, ?, ?)");
                    $inv_items_stmt->execute([$invoice_id, $test_name . " (Lab Diagnostics)", $cost, $cost]);
                }
                
                // Audit log
                audit_log($pdo, 'COMPLETE_LAB_TEST', 'lab_requests', $request_id, ['status' => 'pending'], ['status' => 'completed', 'results' => $results]);
                
                set_flash("Lab results submitted! Diagnostics charge of $" . number_format($cost, 2) . " has been posted to patient's bill.");
                header('Location: lab_portal.php');
                exit();
            } else {
                set_flash('Lab request not found.', 'danger');
            }
        }
    }
}

// Fetch pending and completed lab requests
$status_filter = $_GET['status'] ?? 'pending';

$stmt = $pdo->prepare("
    SELECT lr.*, lt.name as test_name, lt.cost, p.name as patient_name, p.phone as patient_phone, d.name as doctor_name, a.app_date
    FROM lab_requests lr
    JOIN lab_tests lt ON lr.test_id = lt.id
    JOIN appointments a ON lr.appointment_id = a.id
    JOIN patients p ON a.patient_id = p.id
    JOIN doctors d ON a.doctor_id = d.id
    WHERE lr.status = ?
    ORDER BY lr.created_at DESC
");
$stmt->execute([$status_filter]);
$requests = $stmt->fetchAll();
?>

<div class="row mb-4 mt-3">
    <div class="col-md-6">
        <h3 class="fw-bold"><i class="fas fa-flask text-primary me-2"></i>Diagnostics Laboratory Portal</h3>
    </div>
    <div class="col-md-6 text-end">
        <div class="btn-group rounded-pill overflow-hidden border">
            <a href="lab_portal.php?status=pending" class="btn btn-sm <?= $status_filter === 'pending' ? 'btn-primary' : 'btn-light' ?> px-4">Pending Requests</a>
            <a href="lab_portal.php?status=completed" class="btn btn-sm <?= $status_filter === 'completed' ? 'btn-primary' : 'btn-light' ?> px-4">Completed Reports</a>
        </div>
    </div>
</div>

<!-- Requests Directory -->
<div class="card border-0 shadow-sm rounded-4">
    <div class="card-body p-4">
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead>
                    <tr>
                        <th class="ps-4">Request ID</th>
                        <th>Patient Details</th>
                        <th>Ordered Test</th>
                        <th>Ordering Physician</th>
                        <th>Order Date</th>
                        <?php if ($status_filter === 'completed'): ?>
                            <th>Findings / Details</th>
                        <?php endif; ?>
                        <th class="text-end pe-4">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($requests)): ?>
                        <tr>
                            <td colspan="7" class="text-center py-5 text-muted small">No diagnostic requests found in this queue.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($requests as $req): ?>
                            <tr>
                                <td class="ps-4 fw-bold">#LAB-<?= $req['id'] ?></td>
                                <td>
                                    <div class="fw-bold"><?= esc($req['patient_name']) ?></div>
                                    <small class="text-muted"><i class="fas fa-phone-alt fa-xs me-1"></i><?= esc($req['patient_phone']) ?></small>
                                </td>
                                <td>
                                    <div class="fw-semibold text-dark"><?= esc($req['test_name']) ?></div>
                                    <small class="text-muted">Chargeable: $<?= number_format($req['cost'], 2) ?></small>
                                </td>
                                <td><?= esc(format_doctor_name($req['doctor_name'])) ?></td>
                                <td><?= date('M j, Y - h:i A', strtotime($req['created_at'])) ?></td>
                                <?php if ($status_filter === 'completed'): ?>
                                    <td>
                                        <div class="small bg-light p-2 rounded border" style="font-family: monospace; white-space: pre-wrap; font-size:12px;"><?= esc($req['result_details']) ?></div>
                                    </td>
                                <?php endif; ?>
                                <td class="text-end pe-4">
                                    <?php if ($status_filter === 'pending'): ?>
                                        <button class="btn btn-sm btn-primary rounded-pill px-3 submit-results-btn" 
                                                data-id="<?= $req['id'] ?>"
                                                data-test="<?= esc($req['test_name']) ?>"
                                                data-patient="<?= esc($req['patient_name']) ?>"
                                                data-bs-toggle="modal" data-bs-target="#resultModal">
                                            <i class="fas fa-check-circle me-1"></i> Submit Report
                                        </button>
                                    <?php else: ?>
                                        <span class="badge bg-success-subtle text-success px-3 py-2 rounded-pill" style="font-size:11px;">Completed</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Submit Result Modal -->
<div class="modal fade" id="resultModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow rounded-4">
            <div class="modal-header border-bottom-0 p-4 pb-0">
                <h5 class="modal-title fw-bold">Enter Lab Results</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
                <div class="modal-body p-4">
                    <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">
                    <input type="hidden" name="request_id" id="modal_request_id">
                    
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Patient Name</label>
                        <input type="text" id="modal_patient_name" class="form-control bg-light" disabled>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-bold">Diagnostic Investigation</label>
                        <input type="text" id="modal_test_name" class="form-control bg-light" disabled>
                    </div>

                    <div class="mb-0">
                        <label class="form-label small fw-bold">Findings & Measurements (Results Details)</label>
                        <textarea name="result_details" class="form-control rounded-3" rows="4" placeholder="Enter measurements, findings, or notes (e.g. Hemoglobin: 14.5 g/dL, WBC: 8000/mcL)" required></textarea>
                    </div>
                </div>
                <div class="modal-footer border-top-0 p-4 pt-0">
                    <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" name="submit_results" class="btn btn-primary rounded-pill px-4">Submit Diagnostics</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const btn = document.querySelectorAll('.submit-results-btn');
    btn.forEach(el => {
        el.addEventListener('click', function() {
            document.getElementById('modal_request_id').value = this.dataset.id;
            document.getElementById('modal_patient_name').value = this.dataset.patient;
            document.getElementById('modal_test_name').value = this.dataset.test;
        });
    });
});
</script>

<?php require_once 'includes/footer.php'; ?>
