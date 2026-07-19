<?php
$page_title = 'Audit Logs';
require_once 'includes/header.php';
require_role(['admin']);

$pdo = get_db_pdo();

// Handle Clear Logs (Admin only)
if (isset($_POST['clear_logs']) && $_SESSION['role'] === 'admin') {
    if (verify_csrf_token($_POST['csrf_token'] ?? '')) {
        $pdo->exec("DELETE FROM audit_logs");
        set_flash('Audit logs cleared.');
        header('Location: audit_logs.php');
        exit();
    }
}

// Fetch Logs with Pagination
$page = (int) ($_GET['page'] ?? 1);
$per_page = 50;
$offset = ($page - 1) * $per_page;

$stmt = $pdo->prepare("SELECT a.*, u.username FROM audit_logs a LEFT JOIN users u ON a.user_id = u.id ORDER BY a.created_at DESC LIMIT $per_page OFFSET $offset");
$stmt->execute();
$logs = $stmt->fetchAll();

$total_logs = $pdo->query("SELECT COUNT(*) FROM audit_logs")->fetchColumn();
$total_pages = ceil($total_logs / $per_page);
?>

<div class="row mb-4">
    <div class="col-md-6">
        <h3 class="fw-bold"><i class="fas fa-shield-alt text-dark me-2"></i>Security Audit Logs</h3>
    </div>
    <div class="col-md-6 text-end">
        <form method="POST" class="d-inline" onsubmit="return confirm ">
            <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">
            <button type="submit" name="clear_logs" class="btn btn-outline-danger rounded-pill px-4">
                <i class="fas fa-trash-alt me-2"></i>Clear All Logs
            </button>
        </form>
    </div>
</div>

<div class="card border-0 shadow-sm rounded-4">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="bg-light">
                    <tr>
                        <th class="ps-4">Timestamp</th>
                        <th>User</th>
                        <th>Action</th>
                        <th>Entity</th>
                        <th>Changes</th>
                        <th class="pe-4">IP Address</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($logs)): ?>
                        <tr>
                            <td colspan="6" class="text-center py-5 text-muted">No activity logs found.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($logs as $l): ?>
                            <tr>
                                <td class="ps-4 small text-muted">
                                    <?= date('M j, Y H:i:s', strtotime($l['created_at'])) ?>
                                </td>
                                <td>
                                    <span class="badge bg-light text-dark fw-bold"><?= esc($l['username'] ?? 'System') ?></span>
                                </td>
                                <td>
                                    <?php
                                    $action_class = match ($l['action']) {
                                        'CREATE' => 'success',
                                        'UPDATE' => 'primary',
                                        'DELETE' => 'danger',
                                        'LOGIN' => 'info',
                                        'LOGOUT' => 'secondary',
                                        default => 'dark'
                                    };
                                    ?>
                                    <span class="badge bg-<?= $action_class ?>"><?= $l['action'] ?></span>
                                </td>
                                <td>
                                    <span class="text-muted"><?= esc($l['table_name']) ?></span>
                                    <small class="d-block text-muted">ID: <?= $l['record_id'] ?></small>
                                </td>
                                <td style="max-width: 300px;">
                                     <?php 
                                     $old_val = $l['old_values'] ?? null;
                                     $new_val = $l['new_values'] ?? null; 
                                     
                                     if ($old_val) {
                                         $old_arr = json_decode($old_val, true);
                                         if (json_last_error() === JSON_ERROR_NONE && is_array($old_arr)) {
                                             $old_val = json_encode($old_arr, JSON_PRETTY_PRINT);
                                         }
                                     }
                                     if ($new_val) {
                                         $new_arr = json_decode($new_val, true);
                                         if (json_last_error() === JSON_ERROR_NONE && is_array($new_arr)) {
                                             $new_val = json_encode($new_arr, JSON_PRETTY_PRINT);
                                         }
                                     }
                                     ?>
                                     <?php if ($old_val || $new_val): ?>
                                         <button class="btn btn-sm btn-link text-decoration-none p-0" type="button"
                                             data-bs-toggle="collapse" data-bs-target="#data-<?= $l['id'] ?>">
                                             View Details
                                         </button>
                                         <div class="collapse mt-2" id="data-<?= $l['id'] ?>">
                                             <div class="bg-light p-2 rounded small border">
                                                 <?php if ($old_val): ?>
                                                     <strong>Before:</strong>
                                                     <pre class="mb-1" style="font-size: 10px;"><?= esc($old_val) ?></pre>
                                                 <?php endif; ?>
                                                 <?php if ($new_val): ?>
                                                     <strong>After:</strong>
                                                     <pre class="mb-0" style="font-size: 10px;"><?= esc($new_val) ?></pre>
                                                 <?php endif; ?>
                                             </div>
                                         </div>
                                     <?php else: ?>
                                         <span class="text-muted small">N/A</span>
                                     <?php endif; ?>
                                </td>
                                <td class="pe-4 small text-muted"><?= esc($l['ip_address']) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php if ($total_pages > 1): ?>
    <nav class="mt-4">
        <ul class="pagination justify-content-center">
            <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                <li class="page-item <?= $i == $page ? 'active' : '' ?>">
                    <a class="page-link" href="?page=<?= $i ?>"><?= $i ?></a>
                </li>
            <?php endfor; ?>
        </ul>
    </nav>
<?php endif; ?>

<?php require_once 'includes/footer.php'; ?>