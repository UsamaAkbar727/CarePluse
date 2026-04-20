<?php
$page_title = 'User Accounts';
require_once 'includes/header.php';
require_role('admin');

$pdo = get_db_pdo();

// Handle Delete
if (isset($_GET['delete'])) {
    if (!verify_csrf_token($_GET['token'] ?? '')) {
        set_flash('Invalid security token.', 'danger');
    } else {
        $id = (int)$_GET['delete'];
        if ($id === (int)$_SESSION['user_id']) {
            set_flash('You cannot delete your own account.', 'danger');
        } else {
            $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
            $stmt->execute([$id]);
            $old_data = $stmt->fetch();

            if ($old_data) {
                $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
                if ($stmt->execute([$id])) {
                    audit_log($pdo, 'DELETE', 'users', $id, $old_data, null);
                    set_flash('User account deleted.');
                }
            }
        }
    }
    header('Location: users.php');
    exit();
}

// Handle Add/Edit
if (isset($_POST['save_user'])) {
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        set_flash('Invalid security token.', 'danger');
    } else {
        $id = $_POST['id'] ?? null;
        $username = trim($_POST['username']);
        $email = trim($_POST['email']);
        $full_name = trim($_POST['full_name']);
        $role = $_POST['role'];
        $is_active = isset($_POST['is_active']) ? 1 : 0;
        $password = trim($_POST['password']);

        if (empty($username) || empty($role)) {
            set_flash('Username and Role are required.', 'danger');
        } else {
            if ($id) {
                // Update
                $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
                $stmt->execute([$id]);
                $old_data = $stmt->fetch();

                $sql = "UPDATE users SET username=?, email=?, full_name=?, role=?, is_active=?";
                $params = [$username, $email, $full_name, $role, $is_active];
                
                if (!empty($password)) {
                    $sql .= ", password=?";
                    $params[] = password_hash($password, PASSWORD_DEFAULT);
                }
                
                $sql .= " WHERE id=?";
                $params[] = $id;

                $stmt = $pdo->prepare($sql);
                $stmt->execute($params);
                
                audit_log($pdo, 'UPDATE', 'users', $id, ['username'=>$old_data['username']], ['username'=>$username, 'role'=>$role]);
                set_flash('User account updated.');
            } else {
                // Create
                if (empty($password)) {
                    set_flash('Password is required for new users.', 'danger');
                } else {
                    $stmt = $pdo->prepare("INSERT INTO users (username, password, email, full_name, role, is_active) VALUES (?, ?, ?, ?, ?, ?)");
                    $stmt->execute([$username, password_hash($password, PASSWORD_DEFAULT), $email, $full_name, $role, $is_active]);
                    $new_id = $pdo->lastInsertId();
                    
                    audit_log($pdo, 'CREATE', 'users', $new_id, null, ['username'=>$username, 'role'=>$role]);
                    set_flash('New user created successfully.');
                }
            }
            if (!flash_has_errors()) {
                header('Location: users.php');
                exit();
            }
        }
    }
}

// Fetch Users
$search = $_GET['search'] ?? '';
$where = "WHERE 1=1";
$params = [];
if ($search) {
    $where .= " AND (username LIKE ? OR full_name LIKE ? OR email LIKE ?)";
    $params = ["%$search%", "%$search%", "%$search%"];
}

$stmt = $pdo->prepare("SELECT * FROM users $where ORDER BY created_at DESC");
$stmt->execute($params);
$users = $stmt->fetchAll();
?>

<div class="row mb-4">
    <div class="col-md-6">
        <h3 class="fw-bold"><i class="fas fa-users-cog text-primary me-2"></i>Access Control</h3>
    </div>
    <div class="col-md-6 text-end">
        <button class="btn btn-primary rounded-pill px-4" data-bs-toggle="modal" data-bs-target="#userModal">
            <i class="fas fa-plus me-2"></i>Create New User
        </button>
    </div>
</div>

<div class="card border-0 shadow-sm rounded-4">
    <div class="card-body p-4">
        <div class="row g-3 mb-4">
            <div class="col-md-4">
                <form method="GET" class="input-group">
                    <input type="text" name="search" class="form-control border-end-0 rounded-start-pill" placeholder="Search users..." value="<?= esc($search) ?>">
                    <button class="btn btn-primary rounded-end-pill px-3" type="submit">
                        <i class="fas fa-search"></i>
                    </button>
                </form>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead class="bg-light">
                    <tr>
                        <th class="ps-4">User</th>
                        <th>Role</th>
                        <th>Status</th>
                        <th>Last Login</th>
                        <th class="text-end pe-4">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($users as $u): ?>
                        <tr>
                            <td class="ps-4">
                                <div class="d-flex align-items-center">
                                    <img src="https://ui-avatars.com/api/?name=<?= urlencode($u['username']) ?>&background=random" class="rounded-circle me-3" width="40" height="40">
                                    <div>
                                        <div class="fw-bold"><?= esc($u['username']) ?></div>
                                        <small class="text-muted"><?= esc($u['email']) ?></small>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <span class="badge bg-light text-dark border fw-normal px-3">
                                    <?= ucfirst($u['role']) ?>
                                </span>
                            </td>
                            <td>
                                <?php if ($u['is_active']): ?>
                                    <span class="text-success"><i class="fas fa-check-circle me-1"></i>Active</span>
                                <?php else: ?>
                                    <span class="text-danger"><i class="fas fa-times-circle me-1"></i>Suspended</span>
                                <?php endif; ?>
                            </td>
                            <td class="small text-muted">
                                <?= $u['last_login'] ? date('M j, Y H:i', strtotime($u['last_login'])) : 'Never' ?>
                            </td>
                            <td class="text-end pe-4">
                                <button class="btn btn-sm btn-icon btn-light rounded-circle me-1 edit-user" 
                                        data-id="<?= $u['id'] ?>"
                                        data-username="<?= esc($u['username']) ?>"
                                        data-name="<?= esc($u['full_name']) ?>"
                                        data-email="<?= esc($u['email']) ?>"
                                        data-role="<?= $u['role'] ?>"
                                        data-active="<?= $u['is_active'] ?>"
                                        data-bs-toggle="modal" data-bs-target="#userModal">
                                    <i class="fas fa-edit text-primary"></i>
                                </button>
                                <?php if ($u['id'] != $_SESSION['user_id']): ?>
                                    <button onclick="confirmDelete('users.php?delete=<?= $u['id'] ?>&token=<?= generate_csrf_token() ?>')" class="btn btn-sm btn-icon btn-light rounded-circle">
                                        <i class="fas fa-trash text-danger"></i>
                                    </button>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- User Modal -->
<div class="modal fade" id="userModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow rounded-4">
            <div class="modal-header border-bottom-0 p-4">
                <h5 class="modal-title fw-bold" id="modalTitle">Create User Account</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
                <div class="modal-body p-4 pt-0">
                    <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">
                    <input type="hidden" name="id" id="user_id">
                    
                    <div class="mb-3">
                        <label class="form-label fw-medium">Username <span class="text-danger">*</span></label>
                        <input type="text" name="username" id="user_username" class="form-control rounded-3" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-medium">Full Name</label>
                        <input type="text" name="full_name" id="user_name" class="form-control rounded-3">
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-medium">Email Address</label>
                        <input type="email" name="email" id="user_email" class="form-control rounded-3">
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-medium">Role <span class="text-danger">*</span></label>
                            <select name="role" id="user_role" class="form-select rounded-3" required>
                                <option value="receptionist">Receptionist</option>
                                <option value="doctor">Doctor</option>
                                <option value="admin">Administrator</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-medium">Account Status</label>
                            <div class="form-check form-switch mt-2">
                                <input class="form-check-input" type="checkbox" name="is_active" id="user_active" checked>
                                <label class="form-check-label" for="user_active">Active</label>
                            </div>
                        </div>
                    </div>

                    <div class="mb-0">
                        <label class="form-label fw-medium">Password <span class="text-muted small" id="pwLabel"></span></label>
                        <div class="password-field-container">
                            <input type="password" name="password" id="user_password" class="form-control rounded-3" minlength="8">
                            <button type="button" class="password-toggle-icon" tabindex="-1">
                                <i class="far fa-eye"></i>
                            </button>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-top-0 p-4">
                    <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" name="save_user" class="btn btn-primary rounded-pill px-4">Save Account</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const editBtns = document.querySelectorAll('.edit-user');
    const modal = document.getElementById('userModal');
    const modalTitle = document.getElementById('modalTitle');
    const pwLabel = document.getElementById('pwLabel');
    const pwInput = document.getElementById('user_password');
    
    editBtns.forEach(btn => {
        btn.addEventListener('click', function() {
            modalTitle.innerText = 'Edit User Account';
            pwLabel.innerText = '(Leave blank to keep current)';
            pwInput.required = false;
            
            document.getElementById('user_id').value = this.dataset.id;
            document.getElementById('user_username').value = this.dataset.username;
            document.getElementById('user_name').value = this.dataset.name;
            document.getElementById('user_email').value = this.dataset.email;
            document.getElementById('user_role').value = this.dataset.role;
            document.getElementById('user_active').checked = this.dataset.active == '1';
        });
    });

    modal.addEventListener('hidden.bs.modal', function () {
        modalTitle.innerText = 'Create User Account';
        pwLabel.innerText = '';
        pwInput.required = true;
        document.getElementById('user_id').value = '';
        document.querySelector('form').reset();
    });
});
</script>

<?php require_once 'includes/footer.php'; ?>