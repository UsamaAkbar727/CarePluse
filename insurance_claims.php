<?php
$page_title = 'Insurance Claims Registry';
require_once 'includes/header.php';
require_role(['admin']);

$pdo = get_db_pdo();

// Handle Approve Claim
if (isset($_POST['approve_claim'])) {
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        set_flash('Invalid security token.', 'danger');
    } else {
        $claim_id = (int)$_POST['claim_id'];
        
        $stmt = $pdo->prepare("SELECT * FROM insurance_claims WHERE id = ? AND status = 'pending'");
        $stmt->execute([$claim_id]);
        $claim = $stmt->fetch();
        
        if ($claim) {
            $invoice_id = $claim['invoice_id'];
            $amount = $claim['amount_claimed'];
            
            // 1. Approve Claim
            $up_claim = $pdo->prepare("UPDATE insurance_claims SET amount_approved = ?, status = 'approved' WHERE id = ?");
            $up_claim->execute([$amount, $claim_id]);
            
            // 2. Set invoice to paid
            $up_invoice = $pdo->prepare("UPDATE invoices SET status = 'paid' WHERE id = ?");
            $up_invoice->execute([$invoice_id]);
            
            audit_log($pdo, 'APPROVE_CLAIM', 'insurance_claims', $claim_id, ['status' => 'pending'], ['status' => 'approved']);
            set_flash("Insurance claim approved and invoice marked as settled.");
            header('Location: insurance_claims.php');
            exit();
        } else {
            set_flash('Claim not found or already processed.', 'danger');
        }
    }
}

// Handle Reject Claim
if (isset($_POST['reject_claim'])) {
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        set_flash('Invalid security token.', 'danger');
    } else {
        $claim_id = (int)$_POST['claim_id'];
        $remarks = trim($_POST['remarks'] ?? 'Claim rejected by insurer review.');
        
        $stmt = $pdo->prepare("SELECT * FROM insurance_claims WHERE id = ? AND status = 'pending'");
        $stmt->execute([$claim_id]);
        $claim = $stmt->fetch();
        
        if ($claim) {
            $up_claim = $pdo->prepare("UPDATE insurance_claims SET status = 'rejected', remarks = ? WHERE id = ?");
            $up_claim->execute([$remarks, $claim_id]);
            
            audit_log($pdo, 'REJECT_CLAIM', 'insurance_claims', $claim_id, ['status' => 'pending'], ['status' => 'rejected', 'remarks' => $remarks]);
            set_flash("Insurance claim rejected.");
            header('Location: insurance_claims.php');
            exit();
        } else {
            set_flash('Claim not found or already processed.', 'danger');
        }
    }
}

// Fetch claims list
$status_filter = $_GET['status'] ?? 'pending';

$stmt = $pdo->prepare("
    SELECT c.*, ic.name as company_name, p.name as patient_name, i.net_amount
    FROM insurance_claims c
    JOIN insurance_companies ic ON c.company_id = ic.id
    JOIN invoices i ON c.invoice_id = i.id
    JOIN patients p ON i.patient_id = p.id
    WHERE c.status = ?
    ORDER BY c.created_at DESC
");
$stmt->execute([$status_filter]);
$claims = $stmt->fetchAll();
?>

<div class="row mb-4 mt-3">
    <div class="col-md-6">
        <h3 class="fw-bold"><i class="fas fa-shield-alt text-primary me-2"></i>Insurance Claims Registry</h3>
    </div>
    <div class="col-md-6 text-end">
        <div class="btn-group rounded-pill overflow-hidden border">
            <a href="insurance_claims.php?status=pending" class="btn btn-sm <?= $status_filter === 'pending' ? 'btn-primary' : 'btn-light' ?> px-4">Pending Claims</a>
            <a href="insurance_claims.php?status=approved" class="btn btn-sm <?= $status_filter === 'approved' ? 'btn-primary' : 'btn-light' ?> px-4">Approved Payments</a>
            <a href="insurance_claims.php?status=rejected" class="btn btn-sm <?= $status_filter === 'rejected' ? 'btn-primary' : 'btn-light' ?> px-4">Rejections</a>
        </div>
    </div>
</div>

<div class="card border-0 shadow-sm rounded-4">
    <div class="card-body p-4">
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead>
                    <tr>
                        <th class="ps-4">Claim Identifier</th>
                        <th>Patient Name</th>
                        <th>Insurance Provider</th>
                        <th>Claim Share ($)</th>
                        <th>Invoice Value</th>
                        <th>Created Date</th>
                        <?php if ($status_filter === 'rejected'): ?>
                            <th>Reason for Rejection</th>
                        <?php endif; ?>
                        <th class="text-end pe-4">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($claims)): ?>
                        <tr>
                            <td colspan="8" class="text-center py-5 text-muted small">No insurance claims in this queue.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($claims as $c): ?>
                            <tr>
                                <td class="ps-4 fw-bold">#<?= esc($c['claim_number']) ?></td>
                                <td><?= esc($c['patient_name']) ?></td>
                                <td><?= esc($c['company_name']) ?></td>
                                <td class="text-primary fw-bold">$<?= number_format($c['amount_claimed'], 2) ?></td>
                                <td>$<?= number_format($c['net_amount'], 2) ?></td>
                                <td><?= date('M j, Y - H:i', strtotime($c['created_at'])) ?></td>
                                <?php if ($status_filter === 'rejected'): ?>
                                    <td>
                                        <div class="small bg-light p-2 rounded border" style="font-family: monospace; white-space: pre-wrap; font-size:12px;"><?= esc($c['remarks']) ?></div>
                                    </td>
                                <?php endif; ?>
                                <td class="text-end pe-4">
                                    <?php if ($status_filter === 'pending'): ?>
                                        <div class="d-inline-flex gap-2">
                                            <form method="POST">
                                                <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">
                                                <input type="hidden" name="claim_id" value="<?= $c['id'] ?>">
                                                <button type="submit" name="approve_claim" class="btn btn-sm btn-success rounded-pill px-3" onclick="return confirm('Confirm insurer claim approval and settlement?')">
                                                    Approve
                                                </button>
                                            </form>
                                            <button class="btn btn-sm btn-outline-danger rounded-pill px-3 reject-claim-btn"
                                                    data-id="<?= $c['id'] ?>"
                                                    data-code="<?= esc($c['claim_number']) ?>"
                                                    data-bs-toggle="modal" data-bs-target="#rejectModal">
                                                Reject
                                            </button>
                                        </div>
                                    <?php else: ?>
                                        <span class="badge bg-<?= $c['status'] === 'approved' ? 'success' : 'danger' ?>-subtle text-<?= $c['status'] === 'approved' ? 'success' : 'danger' ?> px-3 py-2 rounded-pill" style="font-size:11px;">
                                            <?= ucfirst($c['status']) ?>
                                        </span>
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

<!-- Reject Remarks Modal -->
<div class="modal fade" id="rejectModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow rounded-4">
            <div class="modal-header border-bottom-0 p-4 pb-0">
                <h5 class="modal-title fw-bold">Reject Insurance Claim</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
                <div class="modal-body p-4">
                    <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">
                    <input type="hidden" name="claim_id" id="modal_claim_id">
                    
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Claim Code</label>
                        <input type="text" id="modal_claim_code" class="form-control bg-light" disabled>
                    </div>

                    <div class="mb-0">
                        <label class="form-label small fw-bold">Reason for Rejection / Review Remarks</label>
                        <textarea name="remarks" class="form-control rounded-3" rows="4" placeholder="Enter reason (e.g. Policy coverage expired, missing diagnosis code)" required></textarea>
                    </div>
                </div>
                <div class="modal-footer border-top-0 p-4 pt-0">
                    <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" name="reject_claim" class="btn btn-danger rounded-pill px-4">Confirm Rejection</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const btn = document.querySelectorAll('.reject-claim-btn');
    btn.forEach(el => {
        el.addEventListener('click', function() {
            document.getElementById('modal_claim_id').value = this.dataset.id;
            document.getElementById('modal_claim_code').value = this.dataset.code;
        });
    });
});
</script>

<?php require_once 'includes/footer.php'; ?>
