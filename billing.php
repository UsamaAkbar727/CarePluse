<?php
$page_title = 'Billing & Ledgers';
require_once 'includes/header.php';
require_role(['admin', 'receptionist']);

$pdo = get_db_pdo();

// Filters
$search = $_GET['search'] ?? '';
$status = $_GET['status'] ?? '';
$date_from = $_GET['date_from'] ?? '';
$date_to = $_GET['date_to'] ?? '';

$where = "WHERE 1=1";
$params = [];

if ($search) {
    $where .= " AND p.name LIKE ?";
    $params[] = "%$search%";
}

if ($status) {
    $where .= " AND i.status = ?";
    $params[] = $status;
}

if ($date_from) {
    $where .= " AND DATE(i.created_at) >= ?";
    $params[] = $date_from;
}

if ($date_to) {
    $where .= " AND DATE(i.created_at) <= ?";
    $params[] = $date_to;
}

// Fetch invoices
$stmt = $pdo->prepare("
    SELECT i.*, p.name as patient_name, p.phone as patient_phone, a.app_date
    FROM invoices i
    JOIN patients p ON i.patient_id = p.id
    JOIN appointments a ON i.appointment_id = a.id
    $where
    ORDER BY i.created_at DESC
");
$stmt->execute($params);
$invoices = $stmt->fetchAll();

// Total Summary stats
$total_revenue = $pdo->query("SELECT SUM(net_amount) FROM invoices WHERE status = 'paid'")->fetchColumn() ?: 0;
$pending_revenue = $pdo->query("SELECT SUM(net_amount) FROM invoices WHERE status = 'unpaid'")->fetchColumn() ?: 0;
$total_bills = $pdo->query("SELECT COUNT(*) FROM invoices")->fetchColumn() ?: 0;
?>

<div class="row mb-4">
    <div class="col-md-6">
        <h3 class="fw-bold"><i class="fas fa-file-invoice-dollar text-primary me-2"></i>Financial Invoicing Ledger</h3>
    </div>
    <div class="col-md-6 text-end">
        <!-- Quick Stats Badge -->
        <span class="badge bg-success-subtle text-success p-2 px-3 me-2" style="font-size: 13px;">
            <i class="fas fa-check-circle me-1"></i> Paid: $<?= number_format($total_revenue, 2) ?>
        </span>
        <span class="badge bg-danger-subtle text-danger p-2 px-3" style="font-size: 13px;">
            <i class="fas fa-exclamation-circle me-1"></i> Pending: $<?= number_format($pending_revenue, 2) ?>
        </span>
    </div>
</div>

<!-- Stats widgets -->
<div class="row g-4 mb-4">
    <div class="col-md-4">
        <div class="card border-0 shadow-sm p-4 bg-white" style="border-radius: 16px;">
            <div class="d-flex align-items-center">
                <div class="bg-success text-white p-3 rounded-circle me-3" style="width: 54px; height: 54px; display:flex; align-items:center; justify-content:center;">
                    <i class="fas fa-hand-holding-usd fa-lg"></i>
                </div>
                <div>
                    <h6 class="text-muted mb-0 small text-uppercase fw-bold" style="letter-spacing: 0.5px;">Revenue Collected</h6>
                    <h3 class="fw-bold mb-0 text-success mt-1">$<?= number_format($total_revenue, 2) ?></h3>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card border-0 shadow-sm p-4 bg-white" style="border-radius: 16px;">
            <div class="d-flex align-items-center">
                <div class="bg-danger text-white p-3 rounded-circle me-3" style="width: 54px; height: 54px; display:flex; align-items:center; justify-content:center;">
                    <i class="fas fa-money-bill-wave fa-lg"></i>
                </div>
                <div>
                    <h6 class="text-muted mb-0 small text-uppercase fw-bold" style="letter-spacing: 0.5px;">Unpaid / Outstanding</h6>
                    <h3 class="fw-bold mb-0 text-danger mt-1">$<?= number_format($pending_revenue, 2) ?></h3>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card border-0 shadow-sm p-4 bg-white" style="border-radius: 16px;">
            <div class="d-flex align-items-center">
                <div class="bg-primary text-white p-3 rounded-circle me-3" style="width: 54px; height: 54px; display:flex; align-items:center; justify-content:center;">
                    <i class="fas fa-receipt fa-lg"></i>
                </div>
                <div>
                    <h6 class="text-muted mb-0 small text-uppercase fw-bold" style="letter-spacing: 0.5px;">Total Invoices</h6>
                    <h3 class="fw-bold mb-0 mt-1"><?= $total_bills ?> Bills Issued</h3>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Filters Panel -->
<div class="card border-0 shadow-sm mb-4 rounded-4">
    <div class="card-body p-4">
        <form method="GET" class="row g-3">
            <div class="col-md-4">
                <label class="form-label small fw-bold">Patient Name</label>
                <input type="text" name="search" class="form-control" placeholder="Search patient name..." value="<?= esc($search) ?>">
            </div>
            <div class="col-md-2">
                <label class="form-label small fw-bold">Payment Status</label>
                <select name="status" class="form-select">
                    <option value="">All Statuses</option>
                    <option value="paid" <?= $status === 'paid' ? 'selected' : '' ?>>Paid</option>
                    <option value="unpaid" <?= $status === 'unpaid' ? 'selected' : '' ?>>Unpaid</option>
                    <option value="partially_paid" <?= $status === 'partially_paid' ? 'selected' : '' ?>>Partially Paid</option>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label small fw-bold">Date From</label>
                <input type="date" name="date_from" class="form-control" value="<?= esc($date_from) ?>">
            </div>
            <div class="col-md-2">
                <label class="form-label small fw-bold">Date To</label>
                <input type="date" name="date_to" class="form-control" value="<?= esc($date_to) ?>">
            </div>
            <div class="col-md-2 d-flex align-items-end">
                <button type="submit" class="btn btn-primary w-100 py-2 rounded-3"><i class="fas fa-filter me-2"></i>Filter</button>
            </div>
        </form>
    </div>
</div>

<!-- Invoices Directory -->
<div class="card border-0 shadow-sm rounded-4">
    <div class="card-body p-4">
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead>
                    <tr>
                        <th class="ps-4">Invoice ID</th>
                        <th>Patient Name</th>
                        <th>Visit Date</th>
                        <th>Created At</th>
                        <th>Gross Amount</th>
                        <th>Net Total</th>
                        <th>Status</th>
                        <th class="text-end pe-4">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($invoices)): ?>
                        <tr>
                            <td colspan="8" class="text-center py-5 text-muted">No invoices found.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($invoices as $inv): ?>
                            <tr>
                                <td class="ps-4 fw-bold">#INV-<?= $inv['id'] ?></td>
                                <td>
                                    <div class="fw-bold"><?= esc($inv['patient_name']) ?></div>
                                    <small class="text-muted"><i class="fas fa-phone-alt fa-xs me-1"></i><?= esc($inv['patient_phone']) ?></small>
                                </td>
                                <td><?= date('M j, Y', strtotime($inv['app_date'])) ?></td>
                                <td><?= date('M j, Y h:i A', strtotime($inv['created_at'])) ?></td>
                                <td>$<?= number_format($inv['total_amount'], 2) ?></td>
                                <td class="fw-bold text-primary">$<?= number_format($inv['net_amount'], 2) ?></td>
                                <td>
                                    <?php
                                    $badge = match ($inv['status']) {
                                        'paid' => 'success',
                                        'unpaid' => 'danger',
                                        'partially_paid' => 'warning',
                                    };
                                    ?>
                                    <span class="badge bg-<?= $badge ?>-subtle text-<?= $badge ?> px-3 py-2 rounded-pill" style="font-size: 11px;">
                                        <?= ucfirst(str_replace('_', ' ', $inv['status'])) ?>
                                    </span>
                                </td>
                                <td class="text-end pe-4">
                                    <a href="invoice_details.php?id=<?= $inv['id'] ?>" class="btn btn-sm btn-light border px-3" style="border-radius: 8px;">
                                        <i class="fas fa-eye me-1 text-primary"></i> View Details
                                    </a>
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
