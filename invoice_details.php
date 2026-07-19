<?php
$page_title = 'Invoice Summary';
require_once 'includes/header.php';
require_role(['admin', 'receptionist']);

$pdo = get_db_pdo();
$invoice_id = $_GET['id'] ?? 0;

// Fetch Invoice details
$stmt = $pdo->prepare("
    SELECT i.*, p.name as patient_name, p.phone as patient_phone, p.email as patient_email, p.address as patient_address,
           d.name as doctor_name, a.app_date, a.app_time
    FROM invoices i
    JOIN patients p ON i.patient_id = p.id
    JOIN appointments a ON i.appointment_id = a.id
    JOIN doctors d ON a.doctor_id = d.id
    WHERE i.id = ?
");
$stmt->execute([$invoice_id]);
$invoice = $stmt->fetch();

if (!$invoice) {
    echo '<div class="alert alert-danger mt-3">Invoice record not found.</div>';
    require_once 'includes/footer.php';
    exit();
}

// Handle Add Invoice Item
if (isset($_POST['add_item'])) {
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        set_flash('Invalid security token.', 'danger');
    } else {
        $desc = trim($_POST['item_description'] ?? '');
        $qty = (int)($_POST['quantity'] ?? 1);
        $price = (float)($_POST['unit_price'] ?? 0);
        $total = $qty * $price;
        
        if (empty($desc) || $price <= 0 || $qty <= 0) {
            set_flash('Please provide a valid description, price, and quantity.', 'danger');
        } else {
            // Insert item
            $stmt = $pdo->prepare("INSERT INTO invoice_items (invoice_id, item_description, quantity, unit_price, total_price) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$invoice_id, $desc, $qty, $price, $total]);
            
            // Recalculate invoice totals
            recalculate_invoice_totals($pdo, $invoice_id);
            set_flash('Billing item added successfully.');
            header("Location: invoice_details.php?id=$invoice_id");
            exit();
        }
    }
}

// Handle Delete Invoice Item
if (isset($_GET['delete_item'])) {
    if (!verify_csrf_token($_GET['token'] ?? '')) {
        set_flash('Invalid security token.', 'danger');
    } else {
        $item_id = (int)$_GET['delete_item'];
        $stmt = $pdo->prepare("DELETE FROM invoice_items WHERE id = ? AND invoice_id = ?");
        $stmt->execute([$item_id, $invoice_id]);
        
        recalculate_invoice_totals($pdo, $invoice_id);
        set_flash('Billing item deleted.');
        header("Location: invoice_details.php?id=$invoice_id");
        exit();
    }
}

// Handle Invoice Summary Updates (Tax, Discount, Status, Payment Method)
if (isset($_POST['update_invoice'])) {
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        set_flash('Invalid security token.', 'danger');
    } else {
        $discount = (float)($_POST['discount'] ?? 0);
        $tax_rate = (float)($_POST['tax_rate'] ?? 5); // Default 5%
        $status = $_POST['status'] ?? 'unpaid';
        $pay_method = $_POST['payment_method'] ?? 'Cash';
        
        // Fetch gross total from current items
        $gross_stmt = $pdo->prepare("SELECT SUM(total_price) FROM invoice_items WHERE invoice_id = ?");
        $gross_stmt->execute([$invoice_id]);
        $gross = $gross_stmt->fetchColumn() ?: 0.00;
        
        $tax = $gross * ($tax_rate / 100);
        $net = ($gross + $tax) - $discount;
        if ($net < 0) $net = 0;
        
        // Update database
        $stmt = $pdo->prepare("UPDATE invoices SET total_amount = ?, discount = ?, tax = ?, net_amount = ?, status = ?, payment_method = ? WHERE id = ?");
        $stmt->execute([$gross, $discount, $tax, $net, $status, $pay_method, $invoice_id]);
        
        // Audit log
        audit_log($pdo, 'UPDATE_BILL', 'invoices', $invoice_id, ['net_amount' => $invoice['net_amount'], 'status' => $invoice['status']], ['net_amount' => $net, 'status' => $status]);
        
        set_flash('Invoice financial configuration updated.');
        header("Location: invoice_details.php?id=$invoice_id");
        exit();
    }
}

// Helper to update gross and net amounts based on invoice items
function recalculate_invoice_totals($pdo, $invoice_id) {
    // Get invoice settings
    $inv_stmt = $pdo->prepare("SELECT discount, tax, total_amount FROM invoices WHERE id = ?");
    $inv_stmt->execute([$invoice_id]);
    $inv = $inv_stmt->fetch();
    
    // Calculate new items sum
    $items_stmt = $pdo->prepare("SELECT SUM(total_price) FROM invoice_items WHERE invoice_id = ?");
    $items_stmt->execute([$invoice_id]);
    $gross = $items_stmt->fetchColumn() ?: 0.00;
    
    // Recalculate tax (retain tax percentage by matching old tax vs old gross)
    $tax_rate = 5; // Default fallback
    if ($inv && $inv['total_amount'] > 0) {
        $tax_rate = ($inv['tax'] / $inv['total_amount']) * 100;
    }
    
    $tax = $gross * ($tax_rate / 100);
    $discount = $inv ? (float)$inv['discount'] : 0;
    $net = ($gross + $tax) - $discount;
    if ($net < 0) $net = 0;
    
    // Update invoice
    $up_stmt = $pdo->prepare("UPDATE invoices SET total_amount = ?, tax = ?, net_amount = ? WHERE id = ?");
    $up_stmt->execute([$gross, $tax, $net, $invoice_id]);
}

// Fetch invoice items
$stmt = $pdo->prepare("SELECT * FROM invoice_items WHERE invoice_id = ? ORDER BY id ASC");
$stmt->execute([$invoice_id]);
$invoice_items = $stmt->fetchAll();
?>

<!-- Print Stylesheets -->
<style>
@media print {
    /* Hide navigation sidebar, headers, and quick-action buttons on print */
    #sidebar, nav, .btn, .no-print, .modal, .alert, header {
        display: none !important;
    }
    body {
        background: #ffffff !important;
        color: #000000 !important;
        padding: 0 !important;
        margin: 0 !important;
    }
    .main-content {
        margin-left: 0 !important;
        width: 100% !important;
        padding: 0 !important;
    }
    .invoice-card {
        border: none !important;
        box-shadow: none !important;
        padding: 0 !important;
    }
    .print-title {
        display: block !important;
    }
}
</style>

<div class="row mb-4 no-print mt-3">
    <div class="col-12 d-flex justify-content-between align-items-center">
        <div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-1">
                    <li class="breadcrumb-item"><a href="billing.php" style="color:var(--muted); text-decoration:none;">Invoices</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Invoice Details</li>
                </ol>
            </nav>
            <h4 class="fw-bold mb-0" style="color:var(--text); letter-spacing:-0.5px;">Invoice Summary</h4>
        </div>
        <div class="d-flex gap-2">
            <button onclick="window.print()" class="btn btn-light" style="border: 1.5px solid #e2e8f0; border-radius: 12px;">
                <i class="fas fa-print me-2 text-primary"></i>Print Invoice
            </button>
            <a href="billing.php" class="btn btn-primary px-4" style="border-radius: 12px;">
                <i class="fas fa-arrow-left me-2"></i>Back to Billing
            </a>
        </div>
    </div>
</div>

<div class="row g-4">
    <!-- Invoice Content Sheet -->
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm invoice-card p-4 p-md-5" style="border-radius: 20px;">
            <!-- Brand & Invoice Metadata -->
            <div class="d-flex justify-content-between align-items-start border-bottom pb-4 mb-4 flex-wrap gap-3">
                <div class="d-flex align-items-center gap-3">
                    <div style="width: 50px; height: 50px; background: var(--accent); color: white; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 24px;">
                        <i class="fas fa-file-invoice-dollar"></i>
                    </div>
                    <div>
                        <h4 class="fw-bold mb-0 text-dark" style="letter-spacing: -0.5px;">CarePulse HMS</h4>
                        <small class="text-muted">Clinical Invoicing & Ledger Services</small>
                    </div>
                </div>
                <div class="text-md-end">
                    <h5 class="fw-bold text-primary mb-1">INVOICE #INV-<?= $invoice['id'] ?></h5>
                    <div class="small text-muted mb-1">Issued Date: <?= date('M j, Y h:i A', strtotime($invoice['created_at'])) ?></div>
                    <div class="small text-muted">Appointment Visit: <?= date('M j, Y', strtotime($invoice['app_date'])) ?></div>
                </div>
            </div>

            <!-- Addresses grid -->
            <div class="row g-4 mb-4">
                <div class="col-md-6">
                    <h6 class="text-muted fw-bold small text-uppercase" style="font-size: 11px; letter-spacing: 0.5px;">Patient Billing Info</h6>
                    <div class="fw-bold text-dark mt-2" style="font-size:16px;"><?= esc($invoice['patient_name']) ?></div>
                    <div class="small text-secondary mt-1"><i class="fas fa-phone-alt fa-xs me-2"></i><?= esc($invoice['patient_phone']) ?></div>
                    <div class="small text-secondary mt-1"><i class="fas fa-envelope fa-xs me-2"></i><?= esc($invoice['patient_email'] ?: 'No email registered') ?></div>
                    <div class="small text-secondary mt-1"><i class="fas fa-map-marker-alt fa-xs me-2"></i><?= esc($invoice['patient_address'] ?: 'No residential address registered') ?></div>
                </div>
                <div class="col-md-6 text-md-end">
                    <h6 class="text-muted fw-bold small text-uppercase" style="font-size: 11px; letter-spacing: 0.5px;">Attending Doctor</h6>
                    <div class="fw-bold text-dark mt-2" style="font-size:16px;"><?= format_doctor_name($invoice['doctor_name']) ?></div>
                    <small class="text-muted">General Practitioner / Hospital Consultant</small>
                </div>
            </div>

            <!-- Items Table -->
            <h6 class="text-muted fw-bold small text-uppercase mb-3" style="font-size: 11px; letter-spacing: 0.5px;">Line Itemized Ledger</h6>
            <div class="table-responsive">
                <table class="table align-middle">
                    <thead class="bg-light">
                        <tr>
                            <th class="ps-3" style="font-size: 12px; font-weight: 700; color:var(--muted);">Service / Treatment Description</th>
                            <th style="font-size: 12px; font-weight: 700; color:var(--muted); width: 80px;">Qty</th>
                            <th style="font-size: 12px; font-weight: 700; color:var(--muted); width: 120px;">Unit Price</th>
                            <th style="font-size: 12px; font-weight: 700; color:var(--muted); width: 120px;">Total Price</th>
                            <th class="text-end pe-3 no-print" style="width: 50px;"></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($invoice_items)): ?>
                            <tr>
                                <td colspan="5" class="text-center py-4 text-muted small">No items added to invoice yet.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($invoice_items as $item): ?>
                                <tr>
                                    <td class="ps-3 fw-semibold text-dark" style="font-size: 14px;"><?= esc($item['item_description']) ?></td>
                                    <td><?= $item['quantity'] ?></td>
                                    <td>$<?= number_format($item['unit_price'], 2) ?></td>
                                    <td class="fw-bold text-dark">$<?= number_format($item['total_price'], 2) ?></td>
                                    <td class="text-end pe-3 no-print">
                                        <a href="invoice_details.php?id=<?= $invoice_id ?>&delete_item=<?= $item['id'] ?>&token=<?= generate_csrf_token() ?>" class="btn btn-sm btn-icon btn-light rounded-circle text-danger" title="Remove item">
                                            <i class="fas fa-times-circle"></i>
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <!-- Invoices Total math panel -->
            <div class="row g-4 mt-3">
                <div class="col-md-6">
                    <div class="p-3 bg-light rounded-4 border border-white no-print">
                        <span class="badge bg-<?= $invoice['status'] === 'paid' ? 'success' : 'danger' ?> mb-2">
                            <?= ucfirst($invoice['status']) ?>
                        </span>
                        <h6 class="fw-bold mb-1" style="font-size: 13px;">Payment Terms</h6>
                        <p class="text-muted small mb-0">Payments are processed instantly in billing. Receipts are auto-updated in patient medical timelines.</p>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="space-y-2 text-md-end p-2">
                        <div class="d-flex justify-content-between justify-content-md-end gap-5 py-1">
                            <span class="text-muted small">Subtotal Gross:</span>
                            <span class="fw-medium text-dark">$<?= number_format($invoice['total_amount'], 2) ?></span>
                        </div>
                        <div class="d-flex justify-content-between justify-content-md-end gap-5 py-1">
                            <span class="text-muted small">Assessed Tax (IVA/GST):</span>
                            <span class="fw-medium text-dark">$<?= number_format($invoice['tax'], 2) ?></span>
                        </div>
                        <div class="d-flex justify-content-between justify-content-md-end gap-5 py-1">
                            <span class="text-muted small text-danger">Discount / Co-pay deductions:</span>
                            <span class="fw-medium text-danger">-$<?= number_format($invoice['discount'], 2) ?></span>
                        </div>
                        <hr class="my-2" style="border-color: #cbd5e1; opacity: 1;">
                        <div class="d-flex justify-content-between justify-content-md-end gap-5 py-1 align-items-center">
                            <span class="fw-bold text-dark" style="font-size: 15px;">NET GRAND TOTAL:</span>
                            <span class="fw-bold text-primary" style="font-size: 22px;">$<?= number_format($invoice['net_amount'], 2) ?></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Right sidebar (Invoice Manager & Quick Item Add) -->
    <div class="col-lg-4 no-print">
        <!-- Add Item Panel -->
        <div class="card border-0 shadow-sm rounded-4 mb-4">
            <div class="card-header bg-white border-0 pt-4 px-4 pb-0">
                <h6 class="fw-bold mb-0"><i class="fas fa-plus-circle text-primary me-2"></i>Add Line Item</h6>
            </div>
            <div class="card-body p-4">
                <form method="POST">
                    <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Service / Treatment Description</label>
                        <input type="text" name="item_description" class="form-control form-control-sm" placeholder="e.g. Lab workup, room charges" required>
                    </div>
                    <div class="row">
                        <div class="col-6 mb-3">
                            <label class="form-label small fw-bold">Qty</label>
                            <input type="number" name="quantity" class="form-control form-control-sm" value="1" min="1" required>
                        </div>
                        <div class="col-6 mb-3">
                            <label class="form-label small fw-bold">Price ($)</label>
                            <input type="number" step="0.01" name="unit_price" class="form-control form-control-sm" placeholder="15.00" required>
                        </div>
                    </div>
                    <button type="submit" name="add_item" class="btn btn-primary btn-sm w-100 py-2" style="border-radius: 8px;">
                        Insert Billing Line
                    </button>
                </form>
            </div>
        </div>

        <!-- Financial Updates Panel -->
        <div class="card border-0 shadow-sm rounded-4">
            <div class="card-header bg-white border-0 pt-4 px-4 pb-0">
                <h6 class="fw-bold mb-0"><i class="fas fa-cogs text-primary me-2"></i>Manage Financials</h6>
            </div>
            <div class="card-body p-4">
                <form method="POST">
                    <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">
                    <input type="hidden" name="update_invoice" value="1">
                    
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Discount / Deduction ($)</label>
                        <input type="number" step="0.01" name="discount" class="form-control form-control-sm" value="<?= $invoice['discount'] ?>" min="0">
                    </div>

                    <?php
                    // Estimate tax rate from amounts
                    $tax_percent = 5.0; // Default
                    if ($invoice['total_amount'] > 0) {
                        $tax_percent = round(($invoice['tax'] / $invoice['total_amount']) * 100, 1);
                    }
                    ?>
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Tax Assessed (%)</label>
                        <input type="number" step="0.1" name="tax_rate" class="form-control form-control-sm" value="<?= $tax_percent ?>" min="0">
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-bold">Billing Status</label>
                        <select name="status" class="form-select form-select-sm">
                            <option value="unpaid" <?= $invoice['status'] === 'unpaid' ? 'selected' : '' ?>>Unpaid</option>
                            <option value="paid" <?= $invoice['status'] === 'paid' ? 'selected' : '' ?>>Paid</option>
                            <option value="partially_paid" <?= $invoice['status'] === 'partially_paid' ? 'selected' : '' ?>>Partially Paid</option>
                        </select>
                    </div>

                    <div class="mb-4">
                        <label class="form-label small fw-bold">Payment Method</label>
                        <select name="payment_method" class="form-select form-select-sm">
                            <option value="Cash" <?= $invoice['payment_method'] === 'Cash' ? 'selected' : '' ?>>Cash</option>
                            <option value="Card" <?= $invoice['payment_method'] === 'Card' ? 'selected' : '' ?>>Credit / Debit Card</option>
                            <option value="Insurance" <?= $invoice['payment_method'] === 'Insurance' ? 'selected' : '' ?>>Medical Insurance Coverage</option>
                            <option value="Other" <?= $invoice['payment_method'] === 'Other' ? 'selected' : '' ?>>Other Method</option>
                        </select>
                    </div>

                    <button type="submit" class="btn btn-success btn-sm w-100 py-2" style="border-radius: 8px;">
                        Save Financial Configurations
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
