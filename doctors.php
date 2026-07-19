<?php
$page_title = 'Doctor Management';
require_once 'includes/header.php';
require_role(['admin', 'receptionist']);

$pdo = get_db_pdo();
$user_role = $_SESSION['role'];

// Handle Search
$search = $_GET['search'] ?? '';
$status_filter = $_GET['status'] ?? '';
$where = "WHERE 1=1";
$params = [];

if ($search) {
    $where .= " AND (name LIKE ? OR specialization LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

if ($status_filter) {
    $where .= " AND status = ?";
    $params[] = $status_filter;
}

// Handle Delete
if (isset($_GET['delete']) && $user_role === 'admin') {
    if (!verify_csrf_token($_GET['token'] ?? '')) {
        set_flash('Invalid security token.', 'danger');
    } else {
        $id = (int)$_GET['delete'];
        $stmt = $pdo->prepare("SELECT * FROM doctors WHERE id = ?");
        $stmt->execute([$id]);
        $old_data = $stmt->fetch();

        if ($old_data) {
            $stmt = $pdo->prepare("DELETE FROM doctors WHERE id = ?");
            if ($stmt->execute([$id])) {
                audit_log($pdo, 'DELETE', 'doctors', $id, $old_data, null);
                set_flash('Doctor record deleted.');
            }
        }
    }
    header('Location: doctors.php');
    exit();
}

// Handle Add/Edit
if (isset($_POST['save_doctor'])) {
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        set_flash('Invalid security token.', 'danger');
    } else {
        $id = $_POST['id'] ?? null;
        $name = trim($_POST['name']);
        $spec = trim($_POST['specialization']);
        $email = trim($_POST['email']);
        $phone = trim($_POST['phone']);
        $status = $_POST['status'];

        if (empty($name) || empty($spec)) {
            set_flash('Name and Specialization are required.', 'danger');
        } else {
            if ($id) {
                $stmt = $pdo->prepare("SELECT * FROM doctors WHERE id = ?");
                $stmt->execute([$id]);
                $old_data = $stmt->fetch();

                $stmt = $pdo->prepare("UPDATE doctors SET name=?, specialization=?, email=?, phone=?, status=? WHERE id=?");
                $stmt->execute([$name, $spec, $email, $phone, $status, $id]);
                audit_log($pdo, 'UPDATE', 'doctors', $id, $old_data, ['name'=>$name, 'status'=>$status]);
                set_flash('Doctor profile updated.');
            } else {
                $stmt = $pdo->prepare("INSERT INTO doctors (name, specialization, email, phone, status) VALUES (?, ?, ?, ?, ?)");
                $stmt->execute([$name, $spec, $email, $phone, $status]);
                $new_id = $pdo->lastInsertId();
                audit_log($pdo, 'CREATE', 'doctors', $new_id, null, ['name'=>$name, 'spec'=>$spec]);
                set_flash('New doctor added successfully.');
            }
            header('Location: doctors.php');
            exit();
        }
    }
}

// Fetch Doctors with Appointment Count
$stmt = $pdo->prepare("SELECT d.*, (SELECT COUNT(*) FROM appointments WHERE doctor_id = d.id) as appt_count FROM doctors d $where ORDER BY name ASC");
$stmt->execute($params);
$doctors = $stmt->fetchAll();
?>

<div class="row mb-4">
    <div class="col-md-6">
        <h3 class="fw-bold"><i class="fas fa-user-md text-success me-2"></i>Medical Directory</h3>
    </div>
    <?php if ($user_role === 'admin'): ?>
    <div class="col-md-6 text-end">
        <button class="btn btn-success rounded-pill px-4" data-bs-toggle="modal" data-bs-target="#doctorModal">
            <i class="fas fa-plus me-2"></i>Add New Doctor
        </button>
    </div>
    <?php endif; ?>
</div>

<div class="card border-0 shadow-sm rounded-4">
    <div class="card-body p-4">
        <div class="row g-3 mb-4">
            <div class="col-md-6">
                <form method="GET" class="d-flex gap-2">
                    <input type="text" name="search" class="form-control rounded-pill px-3" placeholder="Search name or specialization..." value="<?= esc($search) ?>">
                    <select name="status" class="form-select rounded-pill w-auto" onchange="this.form.submit()">
                        <option value="">All Status</option>
                        <option value="available" <?= $status_filter === 'available' ? 'selected' : '' ?>>Available</option>
                        <option value="busy" <?= $status_filter === 'busy' ? 'selected' : '' ?>>Busy</option>
                        <option value="offline" <?= $status_filter === 'offline' ? 'selected' : '' ?>>Offline</option>
                    </select>
                    <button class="btn btn-light rounded-pill px-3" type="submit">
                        <i class="fas fa-search"></i>
                    </button>
                </form>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead class="bg-light">
                    <tr>
                        <th class="ps-4">Doctor</th>
                        <th>Specialization</th>
                        <th>Contact</th>
                        <th>Status</th>
                        <th>Appts</th>
                        <?php if ($user_role === 'admin'): ?>
                        <th class="text-end pe-4">Actions</th>
                        <?php endif; ?>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($doctors)): ?>
                        <tr>
                            <td colspan="6" class="text-center py-5 text-muted">No doctors found.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($doctors as $d): ?>
                            <tr>
                                <td class="ps-4">
                                    <div class="d-flex align-items-center">
                                        <div class="avatar-sm bg-success bg-opacity-10 text-success rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 40px; height: 40px;">
                                            <i class="fas fa-user-md"></i>
                                        </div>
                                        <div>
                                            <div class="fw-bold"><?= esc($d['name']) ?></div>
                                            <small class="text-muted">ID: #DOR-<?= $d['id'] ?></small>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <span class="badge bg-primary bg-opacity-10 text-primary fw-normal px-3 py-2">
                                        <?= esc($d['specialization']) ?>
                                    </span>
                                </td>
                                <td>
                                    <div class="small"><i class="fas fa-phone-alt me-1 opacity-50"></i><?= esc($d['phone']) ?></div>
                                    <div class="small text-muted"><i class="fas fa-envelope me-1 opacity-50"></i><?= esc($d['email']) ?></div>
                                </td>
                                <td>
                                    <?php
                                    $s_class = match ($d['status']) {
                                        'available' => 'success',
                                        'busy' => 'warning',
                                        'offline' => 'secondary',
                                        default => 'light'
                                    };
                                    ?>
                                    <span class="badge bg-<?= $s_class ?> p-2 w-100"><?= ucfirst($d['status']) ?></span>
                                </td>
                                <td>
                                    <span class="fw-bold"><?= $d['appt_count'] ?></span>
                                </td>
                                <td class="text-end pe-4">
                                    <a href="doctor_schedule.php?doctor_id=<?= $d['id'] ?>" class="btn btn-sm btn-icon btn-light rounded-circle me-1" title="View Shift Schedule">
                                        <i class="fas fa-calendar-alt text-success"></i>
                                    </a>
                                    <?php if ($user_role === 'admin'): ?>
                                    <button class="btn btn-sm btn-icon btn-light rounded-circle me-1 edit-doctor" 
                                            data-id="<?= $d['id'] ?>"
                                            data-name="<?= esc($d['name']) ?>"
                                            data-spec="<?= esc($d['specialization']) ?>"
                                            data-email="<?= esc($d['email']) ?>"
                                            data-phone="<?= esc($d['phone']) ?>"
                                            data-status="<?= $d['status'] ?>"
                                            data-bs-toggle="modal" data-bs-target="#doctorModal" title="Edit Profile">
                                        <i class="fas fa-edit text-primary"></i>
                                    </button>
                                    <button onclick="confirmDelete('doctors.php?delete=<?= $d['id'] ?>&token=<?= generate_csrf_token() ?>')" class="btn btn-sm btn-icon btn-light rounded-circle" title="Delete Profile">
                                        <i class="fas fa-trash text-danger"></i>
                                    </button>
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

<!-- Add/Edit Modal -->
<div class="modal fade" id="doctorModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow rounded-4">
            <div class="modal-header border-bottom-0 p-4">
                <h5 class="modal-title fw-bold" id="modalTitle">Add Medical Professional</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
                <div class="modal-body p-4 pt-0">
                    <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">
                    <input type="hidden" name="id" id="doctor_id">
                    
                    <div class="mb-3">
                        <label class="form-label fw-medium">Doctor's Full Name <span class="text-danger">*</span></label>
                        <input type="text" name="name" id="doctor_name" class="form-control rounded-3" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-medium">Specialization <span class="text-danger">*</span></label>
                        <input type="text" name="specialization" id="doctor_spec" class="form-control rounded-3" placeholder="e.g. Cardiologist" required>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-medium">Phone Number</label>
                            <input type="tel" name="phone" id="doctor_phone" class="form-control rounded-3">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-medium">Email Address</label>
                            <input type="email" name="email" id="doctor_email" class="form-control rounded-3">
                        </div>
                    </div>

                    <div class="mb-0">
                        <label class="form-label fw-medium">Availability Status</label>
                        <select name="status" id="doctor_status" class="form-select rounded-3">
                            <option value="available">Available</option>
                            <option value="busy">Busy</option>
                            <option value="offline">Offline</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer border-top-0 p-4">
                    <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" name="save_doctor" class="btn btn-success rounded-pill px-4">Save Profile</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const editBtns = document.querySelectorAll('.edit-doctor');
    const modal = document.getElementById('doctorModal');
    const modalTitle = document.getElementById('modalTitle');
    
    editBtns.forEach(btn => {
        btn.addEventListener('click', function() {
            modalTitle.innerText = 'Edit Doctor Profile';
            document.getElementById('doctor_id').value = this.dataset.id;
            document.getElementById('doctor_name').value = this.dataset.name;
            document.getElementById('doctor_spec').value = this.dataset.spec;
            document.getElementById('doctor_email').value = this.dataset.email;
            document.getElementById('doctor_phone').value = this.dataset.phone;
            document.getElementById('doctor_status').value = this.dataset.status;
        });
    });

    modal.addEventListener('hidden.bs.modal', function () {
        modalTitle.innerText = 'Add Medical Professional';
        document.getElementById('doctor_id').value = '';
        document.querySelector('form').reset();
    });
});
</script>

<?php require_once 'includes/footer.php'; ?>