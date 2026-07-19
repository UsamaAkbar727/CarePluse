<?php
$page_title = 'Patient Management';
require_once 'includes/header.php';

$pdo = get_db_pdo();
$user_role = $_SESSION['role'];

// Handle Search
$search = $_GET['search'] ?? '';
$where = "WHERE 1=1";
$params = [];

if ($search) {
    $where .= " AND (name LIKE ? OR phone LIKE ? OR email LIKE ?)";
    $params = ["%$search%", "%$search%", "%$search%"];
}

// Handle Delete
if (isset($_GET['delete']) && $user_role === 'admin') {
    if (!verify_csrf_token($_GET['token'] ?? '')) {
        set_flash('Invalid security token.', 'danger');
    } else {
        $id = (int)$_GET['delete'];
        
        // Get old data for audit
        $stmt = $pdo->prepare("SELECT * FROM patients WHERE id = ?");
        $stmt->execute([$id]);
        $old_data = $stmt->fetch();

        if ($old_data) {
            $stmt = $pdo->prepare("DELETE FROM patients WHERE id = ?");
            if ($stmt->execute([$id])) {
                audit_log($pdo, 'DELETE', 'patients', $id, $old_data, null);
                set_flash('Patient record deleted successfully.');
            }
        }
    }
    header('Location: patients.php');
    exit();
}

// Handle Add/Edit
if (isset($_POST['save_patient'])) {
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        set_flash('Invalid security token.', 'danger');
    } else {
        $id = $_POST['id'] ?? null;
        $name = trim($_POST['name']);
        $email = trim($_POST['email']);
        $phone = trim($_POST['phone']);
        $dob = $_POST['dob'];
        $gender = $_POST['gender'];
        $address = trim($_POST['address']);

        if (empty($name) || empty($phone)) {
            set_flash('Name and Phone are required.', 'danger');
        } else {
            if ($id) {
                // Update
                $stmt = $pdo->prepare("SELECT * FROM patients WHERE id = ?");
                $stmt->execute([$id]);
                $old_data = $stmt->fetch();

                $stmt = $pdo->prepare("UPDATE patients SET name=?, email=?, phone=?, date_of_birth=?, gender=?, address=? WHERE id=?");
                $stmt->execute([$name, $email, $phone, $dob, $gender, $address, $id]);
                
                audit_log($pdo, 'UPDATE', 'patients', $id, $old_data, ['name'=>$name, 'email'=>$email, 'phone'=>$phone]);
                set_flash('Patient record updated.');
            } else {
                // Create
                $stmt = $pdo->prepare("INSERT INTO patients (name, email, phone, date_of_birth, gender, address) VALUES (?, ?, ?, ?, ?, ?)");
                $stmt->execute([$name, $email, $phone, $dob, $gender, $address]);
                $new_id = $pdo->lastInsertId();
                
                audit_log($pdo, 'CREATE', 'patients', $new_id, null, ['name'=>$name, 'phone'=>$phone]);
                set_flash('New patient added successfully.');
            }
            header('Location: patients.php');
            exit();
        }
    }
}

// Fetch Patients
$stmt = $pdo->prepare("SELECT * FROM patients $where ORDER BY created_at DESC");
$stmt->execute($params);
$patients = $stmt->fetchAll();
?>

<div class="row mb-4">
    <div class="col-md-6">
        <h3 class="fw-bold"><i class="fas fa-user-injured text-primary me-2"></i>Patient Directory</h3>
    </div>
    <div class="col-md-6 text-end">
        <button class="btn btn-primary rounded-pill px-4" data-bs-toggle="modal" data-bs-target="#patientModal">
            <i class="fas fa-plus me-2"></i>Add New Patient
        </button>
    </div>
</div>

<div class="card border-0 shadow-sm rounded-4">
    <div class="card-body p-4">
        <div class="row g-3 mb-4">
            <div class="col-md-4">
                <form method="GET" class="input-group">
                    <input type="text" name="search" class="form-control border-end-0 rounded-start-pill" placeholder="Search by name, phone..." value="<?= esc($search) ?>">
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
                        <th class="ps-4">ID</th>
                        <th>Name</th>
                        <th>Contact</th>
                        <th>DOB / Gender</th>
                        <th>Joined</th>
                        <th class="text-end pe-4">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($patients)): ?>
                        <tr>
                            <td colspan="6" class="text-center py-5 text-muted">No patients found.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($patients as $p): ?>
                            <tr>
                                <td class="ps-4 text-muted">#<?= $p['id'] ?></td>
                                <td>
                                    <div class="fw-bold"><?= esc($p['name']) ?></div>
                                    <small class="text-muted"><?= esc($p['email']) ?></small>
                                </td>
                                <td>
                                    <div><i class="fas fa-phone-alt fa-xs me-1 text-muted"></i><?= esc($p['phone']) ?></div>
                                    <small class="text-truncate d-inline-block" style="max-width: 150px;"><?= esc($p['address']) ?></small>
                                </td>
                                <td>
                                    <div><?= $p['date_of_birth'] ? date('M j, Y', strtotime($p['date_of_birth'])) : 'N/A' ?></div>
                                    <span class="badge bg-light text-dark fw-normal"><?= $p['gender'] ?></span>
                                </td>
                                <td><?= date('M j, Y', strtotime($p['created_at'])) ?></td>
                                <td class="text-end pe-4">
                                    <a href="patient_history.php?id=<?= $p['id'] ?>" class="btn btn-sm btn-icon btn-light rounded-circle me-1" title="View Patient Medical Dossier">
                                        <i class="fas fa-file-medical text-success"></i>
                                    </a>
                                    <button class="btn btn-sm btn-icon btn-light rounded-circle me-1 edit-patient" 
                                            data-id="<?= $p['id'] ?>"
                                            data-name="<?= esc($p['name']) ?>"
                                            data-email="<?= esc($p['email']) ?>"
                                            data-phone="<?= esc($p['phone']) ?>"
                                            data-dob="<?= $p['date_of_birth'] ?>"
                                            data-gender="<?= $p['gender'] ?>"
                                            data-address="<?= esc($p['address']) ?>"
                                            data-bs-toggle="modal" data-bs-target="#patientModal">
                                        <i class="fas fa-edit text-primary"></i>
                                    </button>
                                    <button onclick="confirmDelete('patients.php?delete=<?= $p['id'] ?>&token=<?= generate_csrf_token() ?>')" class="btn btn-sm btn-icon btn-light rounded-circle">
                                        <i class="fas fa-trash text-danger"></i>
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

<!-- Add/Edit Modal -->
<div class="modal fade" id="patientModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow rounded-4">
            <div class="modal-header border-bottom-0 p-4">
                <h5 class="modal-title fw-bold" id="modalTitle">Register New Patient</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
                <div class="modal-body p-4 pt-0">
                    <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">
                    <input type="hidden" name="id" id="patient_id">
                    
                    <div class="mb-3">
                        <label class="form-label fw-medium">Full Name <span class="text-danger">*</span></label>
                        <input type="text" name="name" id="patient_name" class="form-control rounded-3" required>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-medium">Phone Number <span class="text-danger">*</span></label>
                            <input type="tel" name="phone" id="patient_phone" class="form-control rounded-3" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-medium">Email Address</label>
                            <input type="email" name="email" id="patient_email" class="form-control rounded-3">
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-medium">Date of Birth</label>
                            <input type="date" name="dob" id="patient_dob" class="form-control rounded-3">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-medium">Gender</label>
                            <select name="gender" id="patient_gender" class="form-select rounded-3">
                                <option value="Male">Male</option>
                                <option value="Female">Female</option>
                                <option value="Other">Other</option>
                            </select>
                        </div>
                    </div>

                    <div class="mb-0">
                        <label class="form-label fw-medium">Residential Address</label>
                        <textarea name="address" id="patient_address" class="form-control rounded-3" rows="3"></textarea>
                    </div>
                </div>
                <div class="modal-footer border-top-0 p-4">
                    <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" name="save_patient" class="btn btn-primary rounded-pill px-4">Save Patient</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const editBtns = document.querySelectorAll('.edit-patient');
    const modal = document.getElementById('patientModal');
    const modalTitle = document.getElementById('modalTitle');
    
    editBtns.forEach(btn => {
        btn.addEventListener('click', function() {
            modalTitle.innerText = 'Edit Patient Record';
            document.getElementById('patient_id').value = this.dataset.id;
            document.getElementById('patient_name').value = this.dataset.name;
            document.getElementById('patient_email').value = this.dataset.email;
            document.getElementById('patient_phone').value = this.dataset.phone;
            document.getElementById('patient_dob').value = this.dataset.dob;
            document.getElementById('patient_gender').value = this.dataset.gender;
            document.getElementById('patient_address').value = this.dataset.address;
        });
    });

    modal.addEventListener('hidden.bs.modal', function () {
        modalTitle.innerText = 'Register New Patient';
        document.getElementById('patient_id').value = '';
        document.querySelector('form').reset();
    });
});
</script>

<?php require_once 'includes/footer.php'; ?>